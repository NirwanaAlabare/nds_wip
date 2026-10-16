<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Approval + lock/unlock Costing & BOM (Approval Center).
 * - Status approval : act_costing.app1 / bom_marketing.approval
 *                     (W/NULL = Awaiting, A/Y = Approved, R = Return to Staff, C = Cancel/ditolak)
 * - Unlock          : tabel unlock ERP (Costing -> unlock_cost, BOM -> unlock_jo per JO dari SO yang memakai BOM)
 *                     dengan kolom tambahan status (costing_legacy_columns.sql):
 *                     W = request (unlock_date masih NULL, jadi tidak dianggap unlock oleh native),
 *                     A = di-unlock manager (default, termasuk baris lama/native). Berlaku selama
 *                     DATE_ADD(unlock_date, INTERVAL 2 DAY) > hari ini dan di-unlock setelah approval terakhir,
 *                     R = ditolak manager, C = dibatalkan peminta, D = selesai (Save and lock / Cancel editing).
 * - Histori (request, unlock, catatan Return/Cancel): mkt_hist.
 * Approved = terkunci. Staff menyimpan perubahan saat return / cancel -> kembali Awaiting.
 * Costing unlocked hanya bisa diedit peminta; Save and lock menutup unlock dan Costing tetap Approved.
 */
class MarketingApprovalService
{
    public const TYPES = ['costing', 'bom'];
    public const ACTIONS = ['approve', 'return', 'cancel'];
    public const UNLOCK_WAITING = 'W';
    public const UNLOCK_APPROVED = 'A';
    public const UNLOCK_REJECTED = 'R';
    public const UNLOCK_CANCELLED = 'C';
    public const UNLOCK_DONE = 'D';

    private const CONFIG = [
        'costing' => [
            'label' => 'Costing',
            'table' => 'act_costing',
            'status_column' => 'app1',
            'number_column' => 'cost_no',
            'costing_column' => 'id',
            'awaiting_value' => 'W',
            'approve_columns' => ['app1_by', 'app1_date'],
        ],
        'bom' => [
            'label' => 'BOM',
            'table' => 'bom_marketing',
            'status_column' => 'approval',
            'number_column' => 'no_katalog_bom',
            'costing_column' => 'id_costing',
            'awaiting_value' => null,
            'approve_columns' => ['approved_by', 'approved_at'],
        ],
    ];

    private const ACTION_VALUES = ['approve' => 'Y', 'return' => 'R', 'cancel' => 'C'];
    private const ACTION_LABELS = ['approve' => 'disetujui', 'return' => 'dikembalikan ke staff', 'cancel' => 'ditolak (cancel)'];
    private const UNLOCK_REQUEST_PREFIX = 'Request unlock: ';

    /** Ekspresi SQL status: awaiting | return | cancel | approved | unlocked. */
    public static function statusSql(string $type, string $alias): string
    {
        if ($type === 'costing') {
            return "(CASE
                WHEN UPPER(TRIM(COALESCE({$alias}.status, ''))) = 'CANCEL' OR {$alias}.app1 = 'C' THEN 'cancel'
                WHEN {$alias}.app1 = 'R' THEN 'return'
                WHEN UPPER(TRIM(COALESCE({$alias}.app1, ''))) IN ('A', 'Y') THEN IF(" . self::costingUnlockedSql($alias) . ", 'unlocked', 'approved')
                ELSE 'awaiting'
            END)";
        }

        return "(CASE
            WHEN {$alias}.approval = 'C' THEN 'cancel'
            WHEN {$alias}.approval = 'R' THEN 'return'
            WHEN {$alias}.approval = 'Y' THEN IF(" . self::bomUnlockedSql($alias) . ", 'unlocked', 'approved')
            ELSE 'awaiting'
        END)";
    }

    /** Baris unlock yang aktif: sudah di-unlock (status A) dan masih dalam masa 2 hari (aturan native). */
    private static function activeUnlockWhere(string $alias = ''): string
    {
        $p = $alias !== '' ? "{$alias}." : '';

        return "{$p}status = '" . self::UNLOCK_APPROVED . "' AND DATE_ADD({$p}unlock_date, INTERVAL 2 DAY) > CURDATE()";
    }

    /**
     * Costing ter-unlock: ada unlock_cost aktif yang di-unlock setelah approval terakhir.
     * unlock_cost tidak ber-index; IN (tidak berkorelasi, dihitung sekali dan isinya sedikit) menyaring dulu
     * supaya EXISTS yang berkorelasi hanya jalan untuk costing yang memang baru di-unlock.
     * Baris native tidak punya approved_at, jadi memakai dateinput.
     */
    public static function costingUnlockedSql(string $alias): string
    {
        return "({$alias}.id IN (SELECT id_cost FROM unlock_cost WHERE " . self::activeUnlockWhere() . ")
            AND EXISTS(SELECT 1 FROM unlock_cost u
                WHERE u.id_cost = {$alias}.id AND " . self::activeUnlockWhere('u') . "
                  AND COALESCE(u.approved_at, u.dateinput) >= COALESCE({$alias}.app1_date, '1970-01-01')))";
    }

    /** JO aktif dari SO yang memakai BOM ini. */
    private static function bomJoIdsSql(string $bomIdExpression): string
    {
        return "SELECT jd.id_jo FROM so JOIN jo_det jd ON jd.id_so = so.id AND jd.cancel = 'N'
            WHERE so.id_bom = {$bomIdExpression} AND so.cancel_h = 'N'";
    }

    /** BOM ter-unlock: ada unlock_jo aktif (untuk salah satu JO-nya) yang di-unlock setelah BOM di-approve. */
    public static function bomUnlockedSql(string $alias): string
    {
        return "EXISTS(SELECT 1 FROM unlock_jo u
            WHERE u.id_jo IN (SELECT id_jo FROM unlock_jo WHERE " . self::activeUnlockWhere() . ")
              AND u.id_jo IN (" . self::bomJoIdsSql("{$alias}.id") . ")
              AND " . self::activeUnlockWhere('u') . "
              AND COALESCE(u.approved_at, u.dateinput) >= COALESCE({$alias}.approved_at, '1970-01-01'))";
    }

    /** Ada request unlock (status W) yang belum diproses manager. */
    public static function pendingUnlockSql(string $type, string $idExpression): string
    {
        $waiting = "status = '" . self::UNLOCK_WAITING . "'";
        if ($type === 'costing') {
            return "({$idExpression} IN (SELECT id_cost FROM unlock_cost WHERE {$waiting}))";
        }

        return "EXISTS(SELECT 1 FROM unlock_jo u
            WHERE u.{$waiting} AND u.id_jo IN (" . self::bomJoIdsSql($idExpression) . "))";
    }

    /** Catatan Return to Staff / Cancel terakhir dari manager. */
    public static function latestNoteSql(string $type, string $idExpression): string
    {
        return "(SELECT h.`desc` FROM mkt_hist h
            WHERE h.type = '{$type}' AND h.type_id = {$idExpression} AND h.action IN ('return', 'cancel')
            ORDER BY h.id DESC LIMIT 1)";
    }

    public function status(string $type, int $id): ?string
    {
        $this->assertType($type);

        return $this->db()->table(self::CONFIG[$type]['table'] . ' as x')
            ->where('x.id', $id)
            ->value(DB::raw(self::statusSql($type, 'x')));
    }

    /** Approved & tidak dalam masa unlock = tidak boleh diubah. */
    public function isLocked(string $type, int $id): bool
    {
        return $this->status($type, $id) === 'approved';
    }

    /**
     * Dipanggil sebelum staff menyimpan perubahan: dokumen return / cancel / unlocked
     * kembali ke awaiting supaya dicek ulang manager.
     */
    public function markEdited(string $type, int $id): void
    {
        $status = $this->status($type, $id);
        if (!in_array($status, ['return', 'cancel', 'unlocked'], true)) {
            return;
        }
        // Costing unlocked tetap Approved; dikunci lagi lewat Save and lock / Cancel editing.
        if ($type === 'costing' && $status === 'unlocked') {
            return;
        }

        // Costing yang dokumennya dibatalkan (status CANCEL) bukan ditolak approval, biarkan.
        if ($type === 'costing' && $status === 'cancel'
            && strtoupper(trim((string) $this->db()->table('act_costing')->where('id', $id)->value('status'))) === 'CANCEL') {
            return;
        }

        $config = self::CONFIG[$type];
        $this->db()->table($config['table'])->where('id', $id)->update([
            $config['status_column'] => $config['awaiting_value'],
        ]);
        $this->history($type, $id, 'resubmitted', "{$config['label']} {$this->number($type, $id)} diubah dan menunggu approval lagi.");
    }

    /**
     * Approve / Return to Staff / Cancel untuk beberapa dokumen sekaligus.
     * Hanya dokumen berstatus awaiting yang diproses; sisanya dilewati.
     */
    public function decide(string $type, array $ids, string $action, ?string $note = null): array
    {
        $this->assertType($type);
        if (!in_array($action, self::ACTIONS, true)) {
            throw new \InvalidArgumentException('Aksi approval tidak valid.');
        }
        $note = trim((string) $note);
        if ($action !== 'approve' && $note === '') {
            throw new \RuntimeException('Catatan wajib diisi untuk Return to Staff / Cancel.');
        }

        $config = self::CONFIG[$type];
        $db = $this->db();

        return $db->transaction(function () use ($db, $type, $ids, $action, $note, $config) {
            $eligible = $db->table($config['table'] . ' as x')
                ->whereIn('x.id', array_map('intval', $ids))
                ->whereRaw(self::statusSql($type, 'x') . " = 'awaiting'")
                ->lockForUpdate()
                ->pluck('x.id');
            if ($type === 'bom') {
                // BOM tanpa item belum diajukan, jadi tidak bisa diputuskan.
                $eligible = $db->table('bom_marketing_detail')
                    ->whereIn('id_bom_marketing', $eligible)
                    ->where('cancel', 'N')
                    ->distinct()
                    ->pluck('id_bom_marketing');
            }

            $update = [$config['status_column'] => self::ACTION_VALUES[$action]];
            if ($action === 'approve') {
                // app1_by hanya varchar(20).
                $update[$config['approve_columns'][0]] = mb_substr((string) $this->userName(), 0, 20);
                $update[$config['approve_columns'][1]] = now();
            }
            if ($eligible->isNotEmpty()) {
                $db->table($config['table'])->whereIn('id', $eligible)->update($update);
            }

            foreach ($eligible as $id) {
                $description = $action === 'approve'
                    ? "{$config['label']} {$this->number($type, $id)} " . self::ACTION_LABELS[$action] . '.'
                    : $note;
                $this->history($type, $id, $action === 'approve' ? 'approved' : $action, $description);
            }

            return ['processed' => $eligible->count(), 'skipped' => count(array_unique($ids)) - $eligible->count()];
        });
    }

    /**
     * Staff mengajukan unlock: baris status W dengan unlock_date NULL di unlock_cost (Costing)
     * atau unlock_jo untuk setiap JO dari SO yang memakai BOM (BOM).
     */
    public function requestUnlock(string $type, int $id, string $reason): void
    {
        $this->assertType($type);
        $reason = mb_substr(trim($reason), 0, 100);
        $db = $this->db();
        $db->transaction(function () use ($db, $type, $id, $reason) {
            $db->table(self::CONFIG[$type]['table'])->where('id', $id)->lockForUpdate()->first();
            if (!$this->isLocked($type, $id)) {
                throw new \RuntimeException(self::CONFIG[$type]['label'] . ' tidak sedang terkunci.');
            }
            $pending = (bool) $db->selectOne('SELECT ' . self::pendingUnlockSql($type, '?') . ' as pending', [$id])->pending;
            if ($pending) {
                throw new \RuntimeException('Request unlock untuk ' . self::CONFIG[$type]['label'] . ' ini masih menunggu.');
            }

            $request = [
                'unlock_date' => null,
                'reason' => $reason,
                'username' => mb_substr((string) $this->loginName(), 0, 10),
                'dateinput' => now(),
                'status' => self::UNLOCK_WAITING,
            ];
            if ($type === 'costing') {
                $db->table('unlock_cost')->insert($request + [
                    'id_cost' => $id,
                    'cost_no' => $this->number('costing', $id),
                ]);
            } else {
                $jos = $this->bomJobOrders($id);
                if ($jos->isEmpty()) {
                    throw new \RuntimeException('BOM belum terhubung ke JO, unlock belum bisa diajukan.');
                }
                $db->table('unlock_jo')->insert($jos->map(fn ($jo) => $request + [
                    'id_jo' => $jo->id,
                    'jo_no' => $jo->jo_no,
                ])->all());
            }

            $this->history($type, $id, 'unlock_requested', self::UNLOCK_REQUEST_PREFIX . $reason);
        });
    }

    /**
     * Tombol Unlock di Approval Center ($ids = id Costing / id BOM yang punya request).
     * Baris request diubah jadi status A dengan unlock_date hari ini (berlaku 2 hari, aturan native).
     */
    public function unlock(string $type, array $ids): int
    {
        $this->assertType($type);
        $db = $this->db();

        return $db->transaction(function () use ($db, $type, $ids) {
            $approved = [
                'status' => self::UNLOCK_APPROVED,
                'unlock_date' => now()->toDateString(),
                'approved_by' => mb_substr((string) $this->userName(), 0, 50),
                'approved_at' => now(),
            ];
            $processed = 0;
            foreach (array_unique(array_map('intval', $ids)) as $id) {
                $query = $type === 'costing'
                    ? $db->table('unlock_cost')->where('id_cost', $id)
                    : $db->table('unlock_jo')->whereIn('id_jo', $this->bomJobOrders($id)->pluck('id'));
                if ($query->where('status', self::UNLOCK_WAITING)->update($approved) === 0) {
                    continue;
                }
                $processed++;
                $label = self::CONFIG[$type]['label'];
                $this->history($type, $id, 'unlocked', "{$label} {$this->number($type, $id)} di-unlock (berlaku sampai " . now()->addDay()->format('d M Y') . ').');
            }

            return $processed;
        });
    }

    /**
     * Kondisi lock Costing untuk halaman edit:
     * editable (belum approved) | locked | requested (W menunggu) | unlocked (A aktif).
     */
    public function costingUnlockState(int $id): array
    {
        $db = $this->db();
        $status = $this->status('costing', $id);
        $state = [
            'state' => 'editable',
            'status' => $status,
            'can_edit' => true,
            'request' => null,
            'unlock' => null,
            'rev' => $db->table('unlock_cost')->where('id_cost', $id)->where('status', self::UNLOCK_DONE)->count() + 1,
        ];

        if ($status === 'unlocked') {
            $unlock = $db->table('unlock_cost as u')
                ->join('act_costing as a', 'a.id', '=', 'u.id_cost')
                ->where('u.id_cost', $id)
                ->whereRaw(self::activeUnlockWhere('u'))
                ->whereRaw("COALESCE(u.approved_at, u.dateinput) >= COALESCE(a.app1_date, '1970-01-01')")
                ->orderByRaw('COALESCE(u.approved_at, u.dateinput) DESC')
                ->first(['u.username', 'u.approved_by', 'u.approved_at', 'u.dateinput', 'u.reason']);

            return ['state' => 'unlocked', 'unlock' => $unlock, 'can_edit' => $this->isRequester($unlock->username ?? null)] + $state;
        }
        if ($status !== 'approved') {
            return $state;
        }

        $request = $db->table('unlock_cost')
            ->where('id_cost', $id)
            ->where('status', self::UNLOCK_WAITING)
            ->orderByDesc('dateinput')
            ->first(['username', 'dateinput', 'reason']);

        return ['state' => $request ? 'requested' : 'locked', 'request' => $request, 'can_edit' => false] + $state;
    }

    /** Costing yang sedang unlocked hanya boleh diubah oleh user yang me-request unlock. */
    public function canEditCosting(int $id): bool
    {
        return $this->costingUnlockState($id)['can_edit'];
    }

    /** Peminta membatalkan request unlock yang masih menunggu (W -> C). */
    public function cancelUnlockRequest(int $costingId): void
    {
        $db = $this->db();
        $db->transaction(function () use ($db, $costingId) {
            $request = $db->table('unlock_cost')
                ->where('id_cost', $costingId)
                ->where('status', self::UNLOCK_WAITING)
                ->lockForUpdate()
                ->get(['username']);
            if ($request->isEmpty()) {
                throw new \RuntimeException('Tidak ada request unlock yang menunggu.');
            }
            if (!$request->contains(fn ($row) => $this->isRequester($row->username))) {
                throw new \RuntimeException('Hanya peminta yang bisa membatalkan request unlock.');
            }

            $db->table('unlock_cost')->where('id_cost', $costingId)->where('status', self::UNLOCK_WAITING)->update(['status' => self::UNLOCK_CANCELLED]);
            $this->history('costing', $costingId, 'unlock_cancelled', "Request unlock Costing {$this->number('costing', $costingId)} dibatalkan.");
        });
    }

    /**
     * Mengunci Costing lagi (A -> D). $saved = Save and lock (Costing tetap Approved),
     * tanpa $saved = Cancel editing.
     */
    public function relockCosting(int $costingId, bool $saved): void
    {
        $state = $this->costingUnlockState($costingId);
        if ($state['state'] !== 'unlocked') {
            throw new \RuntimeException('Costing tidak sedang di-unlock.');
        }
        if (!$state['can_edit']) {
            throw new \RuntimeException('Hanya peminta unlock yang bisa mengunci Costing ini.');
        }

        $db = $this->db();
        $db->transaction(function () use ($db, $costingId, $saved, $state) {
            $db->table('unlock_cost')
                ->where('id_cost', $costingId)
                ->where('status', self::UNLOCK_APPROVED)
                ->whereRaw(self::activeUnlockWhere())
                ->update(['status' => self::UNLOCK_DONE]);

            $number = $this->number('costing', $costingId);
            if (!$saved) {
                $this->history('costing', $costingId, 'relocked', "Edit Costing {$number} dibatalkan, Costing dikunci lagi.");
                return;
            }

            $this->history('costing', $costingId, 'relocked', "Costing {$number} Rev {$state['rev']} disimpan dan dikunci lagi (Approved).");
        });
    }

    private function isRequester(?string $username): bool
    {
        return $username !== null && strcasecmp(trim($username), (string) mb_substr((string) $this->loginName(), 0, 10)) === 0;
    }

    /** JO aktif (id, jo_no) dari SO yang memakai BOM. */
    private function bomJobOrders(int $bomId)
    {
        return $this->db()->table('jo')
            ->whereIn('id', fn ($q) => $q->select('jd.id_jo')
                ->from('so')
                ->join('jo_det as jd', fn ($join) => $join->on('jd.id_so', '=', 'so.id')->where('jd.cancel', 'N'))
                ->where('so.id_bom', $bomId)
                ->where('so.cancel_h', 'N'))
            ->get(['id', 'jo_no']);
    }

    private function costingId(string $type, int $id): int
    {
        $config = self::CONFIG[$type];
        $costingId = $this->db()->table($config['table'])->where('id', $id)->value($config['costing_column']);
        if (!$costingId) {
            throw new \RuntimeException($config['label'] . ' tidak ditemukan.');
        }

        return (int) $costingId;
    }

    private function number(string $type, int $id): string
    {
        $config = self::CONFIG[$type];

        return (string) $this->db()->table($config['table'])->where('id', $id)->value($config['number_column']);
    }

    private function history(string $type, int $id, string $action, string $description): void
    {
        app(MarketingHistoryService::class)->record($this->costingId($type, $id), $type, $action, $description, $id);
    }

    private function assertType(string $type): void
    {
        if (!in_array($type, self::TYPES, true)) {
            throw new \InvalidArgumentException('Tipe dokumen tidak valid.');
        }
    }

    private function userName(): ?string
    {
        $user = Auth::user();

        return $user ? ($user->name ?: $user->username) : null;
    }

    /** unlock_cost / unlock_jo.username memakai login seperti ERP native (varchar 10). */
    private function loginName(): ?string
    {
        $user = Auth::user();

        return $user ? ($user->username ?: $user->name) : null;
    }

    private function db()
    {
        return DB::connection('mysql_sb');
    }
}
