<?php

namespace App\Services\Marketing;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Approval + lock/unlock Costing & BOM di DB baru (koneksi mysql_mkt), kolom & aturannya disamakan dengan Signalbit
 * (MarketingApprovalService untuk act_costing / unlock_cost). Sales Order tidak punya approval (bebas edit selama WS-nya
 * belum dipakai Marker, lihat soMarkerMessage).
 * - Status   : costings.approval / bom.approval CHAR(1) (W/NULL = Awaiting, A/Y = Approved, R = Return to Staff, C = Cancel),
 *              approved_by BIGINT (users.id, kolom ERD yang sudah ada), approved_at DATETIME. costings.status = CANCEL juga Cancel.
 * - Unlock   : unlock_cost (id_cost, cost_no) / unlock_bom (id_bom, bom_no) dengan unlock_date, reason, username, dateinput,
 *              status (W request, A di-unlock, R ditolak, C dibatalkan peminta, D selesai), approved_by, approved_at.
 *              Ter-unlock = status A, DATE_ADD(unlock_date, 2 DAY) > hari ini, dan di-unlock setelah approval terakhir.
 * - Approved = terkunci; Costing baru boleh dibuat SO setelah Approved. Return / Cancel yang disimpan staff -> Awaiting.
 *   Costing unlocked: hanya peminta yang bisa edit, Save and lock -> Awaiting. BOM unlocked: Save -> Awaiting.
 * - Histori tetap di process_logs (lihat log()). SQL kolom/tabel: database/sql/nds_erd_penyesuaian.sql.
 */
class MarketingNdsApprovalService
{
    private const CONNECTION = 'mysql_mkt';
    public const TYPES = ['costing', 'bom'];
    public const ACTIONS = ['approve', 'return', 'cancel'];
    public const UNLOCK_WAITING = 'W';
    public const UNLOCK_APPROVED = 'A';
    public const UNLOCK_CANCELLED = 'C';
    public const UNLOCK_DONE = 'D';
    private const ACTION_VALUES = ['approve' => 'Y', 'return' => 'R', 'cancel' => 'C'];
    private const STATUS_OF_ACTION = ['approve' => 'approved', 'return' => 'return', 'cancel' => 'cancel'];

    private const CONFIG = [
        'costing' => [
            'label' => 'Costing', 'table' => 'costings', 'number' => 'costing_no',
            'unlock_table' => 'unlock_cost', 'unlock_id' => 'id_cost', 'unlock_no' => 'cost_no', 'log_column' => 'costings_id',
        ],
        'bom' => [
            'label' => 'BOM', 'table' => 'bom', 'number' => 'bom_no',
            'unlock_table' => 'unlock_bom', 'unlock_id' => 'id_bom', 'unlock_no' => 'bom_no', 'log_column' => 'bom_id',
        ],
    ];


    private function db()
    {
        return DB::connection(self::CONNECTION);
    }

    private static array $readyCache = [];

    /**
     * Kolom approval & approved_at dokumen ini sudah dibuat: approval aktif (Approve / Return / Cancel, lock, dan untuk Costing
     * Add SO hanya saat Approved). Per jenis dokumen, jadi Costing tetap jalan walau kolom BOM belum ada.
     */
    public static function ready(string $type = 'costing'): bool
    {
        $c = self::config($type);

        return self::$readyCache["r:{$type}"] ??= Schema::connection(self::CONNECTION)->hasColumn($c['table'], 'approval')
            && Schema::connection(self::CONNECTION)->hasColumn($c['table'], 'approved_at');
    }

    /** Request Unlock / Unlock aktif setelah tabel unlock_cost / unlock_bom juga ada. */
    public static function unlockReady(string $type = 'costing'): bool
    {
        return self::$readyCache["u:{$type}"] ??= self::ready($type) && Schema::connection(self::CONNECTION)->hasTable(self::config($type)['unlock_table']);
    }

    private static function assertUnlockReady(string $type): void
    {
        if (!self::unlockReady($type)) {
            throw new \RuntimeException('Fitur unlock belum aktif: tabel ' . self::config($type)['unlock_table'] . ' belum dibuat di DB nds.');
        }
    }

    private static function config(string $type): array
    {
        if (!isset(self::CONFIG[$type])) {
            throw new \InvalidArgumentException('Tipe dokumen tidak valid.');
        }

        return self::CONFIG[$type];
    }

    private static function activeUnlockWhere(string $alias = ''): string
    {
        $p = $alias !== '' ? "{$alias}." : '';

        return "{$p}status = '" . self::UNLOCK_APPROVED . "' AND DATE_ADD({$p}unlock_date, INTERVAL 2 DAY) > CURDATE()";
    }

    /** Ekspresi SQL status: awaiting | return | cancel | approved | unlocked (sama dengan statusSql Signalbit). */
    public static function statusSql(string $type, string $alias): string
    {
        $c = self::config($type);
        $cancel = $type === 'costing' ? "UPPER(TRIM(COALESCE({$alias}.status, ''))) = 'CANCEL' OR " : '';
        $unlocked = !self::unlockReady($type) ? '0' : "EXISTS(SELECT 1 FROM {$c['unlock_table']} u WHERE u.{$c['unlock_id']} = {$alias}.id AND " . self::activeUnlockWhere('u') . "
            AND COALESCE(u.approved_at, u.dateinput) >= COALESCE({$alias}.approved_at, '1970-01-01'))";

        return "(CASE
            WHEN {$cancel}{$alias}.approval = 'C' THEN 'cancel'
            WHEN {$alias}.approval = 'R' THEN 'return'
            WHEN UPPER(TRIM(COALESCE({$alias}.approval, ''))) IN ('A', 'Y') THEN IF({$unlocked}, 'unlocked', 'approved')
            ELSE 'awaiting'
        END)";
    }

    /**
     * Awaiting hasil revisi setelah unlock (Save and lock / BOM disimpan saat Unlocked / masa unlock habis dengan perubahan):
     * perubahan status terakhir di process_logs = resubmitted dari unlocked. Masih bisa diedit (seperti Awaiting biasa);
     * dipakai untuk penanda "revisi menunggu approval" di halaman edit. Add SO / BOM tetap menunggu Approved.
     */
    public static function submittedSql(string $type, string $idExpression): string
    {
        $c = self::config($type);

        return "COALESCE((SELECT l.action = 'resubmitted' AND l.from_status = 'unlocked' FROM process_logs l
            WHERE l.{$c['log_column']} = {$idExpression} AND l.doc_type = '{$type}' AND l.to_status IS NOT NULL ORDER BY l.id DESC LIMIT 1), 0)";
    }

    /** Ada request unlock (status W) yang belum diproses. */
    public static function pendingUnlockSql(string $type, string $idExpression): string
    {
        $c = self::config($type);
        if (!self::unlockReady($type)) {
            return '(0 = 1)';
        }

        return "({$idExpression} IN (SELECT {$c['unlock_id']} FROM {$c['unlock_table']} WHERE status = '" . self::UNLOCK_WAITING . "'))";
    }

    /** Kolom request unlock (status W) terbaru. */
    public static function requestColumnSql(string $type, string $idExpression, string $column): string
    {
        $c = self::config($type);
        if (!self::unlockReady($type)) {
            return 'NULL';
        }

        return "(SELECT u.{$column} FROM {$c['unlock_table']} u WHERE u.{$c['unlock_id']} = {$idExpression} AND u.status = '" . self::UNLOCK_WAITING . "' ORDER BY u.dateinput DESC LIMIT 1)";
    }

    public function status(string $type, int $id): ?string
    {
        if (!self::ready($type)) {
            return 'awaiting';
        }
        $this->closeExpiredUnlocks($type, $id);

        return $this->db()->table(self::config($type)['table'] . ' as x')->where('x.id', $id)->value(DB::raw(self::statusSql($type, 'x')));
    }

    public function hasPendingUnlock(string $type, int $id): bool
    {
        return self::ready($type) && (bool) $this->db()->selectOne('SELECT ' . self::pendingUnlockSql($type, (string) (int) $id) . ' as p')->p;
    }

    /**
     * Kondisi lock (format sama dengan MarketingApprovalService::costingUnlockState):
     * editable | locked | requested | unlocked | submitted, plus request / unlock (baris unlock_cost / unlock_bom), can_edit, rev.
     */
    public function state(string $type, int $id): array
    {
        $c = self::config($type);
        $status = $this->status($type, $id);
        $state = ['state' => 'editable', 'status' => $status, 'can_edit' => true, 'request' => null, 'unlock' => null, 'rev' => 1];
        if (!self::ready($type)) {
            return $state;
        }
        if (!self::unlockReady($type)) {
            // Tabel unlock belum ada: Approved tetap terkunci, hanya belum bisa Request Unlock.
            return $status === 'approved' ? ['state' => 'locked', 'can_edit' => false] + $state : $state;
        }
        $rows = fn () => $this->db()->table($c['unlock_table'])->where($c['unlock_id'], $id);
        $state['rev'] = $rows()->where('status', self::UNLOCK_DONE)->count() + 1;

        if ($status === 'unlocked') {
            $appDate = $this->db()->table($c['table'])->where('id', $id)->value('approved_at');
            $unlock = $rows()->whereRaw(self::activeUnlockWhere())
                ->whereRaw('COALESCE(approved_at, dateinput) >= ?', [$appDate ?: '1970-01-01'])
                ->orderByRaw('COALESCE(approved_at, dateinput) DESC')
                ->first(['username', 'approved_by', 'approved_at', 'dateinput', 'reason']);
            // Selama unlock hanya peminta yang boleh mengubah (Costing dan BOM).
            $canEdit = $this->isRequester($unlock->username ?? null);

            return ['state' => 'unlocked', 'unlock' => $unlock, 'can_edit' => $canEdit] + $state;
        }
        if ($status === 'awaiting' && $this->isSubmitted($type, $id)) {
            // Revisi setelah unlock sudah diserahkan: masih bisa diedit oleh peminta unlock terakhir, Add SO / BOM menunggu manager Approve lagi.
            $last = $rows()->where('status', self::UNLOCK_DONE)->orderByRaw('COALESCE(approved_at, dateinput) DESC')->value('username');

            return ['state' => 'submitted', 'can_edit' => $last === null || $this->isRequester($last)] + $state;
        }
        if ($status !== 'approved') {
            return $state;
        }
        $request = $rows()->where('status', self::UNLOCK_WAITING)->orderByDesc('dateinput')->first(['username', 'dateinput', 'reason']);

        return ['state' => $request ? 'requested' : 'locked', 'request' => $request, 'can_edit' => false] + $state;
    }

    public function isSubmitted(string $type, int $id): bool
    {
        return self::ready($type) && (bool) $this->db()->selectOne('SELECT ' . self::submittedSql($type, (string) (int) $id) . ' as s')->s;
    }

    /** Pesan bila dokumen tidak boleh diubah user ini; null = boleh. */
    public function lockedMessage(string $type, int $id): ?string
    {
        $state = $this->state($type, $id);
        if ($state['can_edit']) {
            return null;
        }
        $label = self::config($type)['label'];

        return match ($state['state']) {
            'unlocked' => "{$label} sedang di-unlock untuk user lain. Hanya peminta unlock yang bisa mengubahnya.",
            'submitted' => "Revisi {$label} ini sedang menunggu approval. Hanya peminta unlock yang bisa mengubahnya.",
            'requested' => "{$label} sudah di-approve dan terkunci. Request unlock sedang menunggu keputusan Marketing Manager.",
            default => "{$label} sudah di-approve dan terkunci. Ajukan Request Unlock terlebih dahulu.",
        };
    }

    /**
     * Dipanggil saat staff menyimpan: Return / Cancel kembali Awaiting. BOM unlocked juga kembali Awaiting (unlock ditutup);
     * Costing unlocked menunggu Save and lock (relockCosting).
     */
    public function markEdited(string $type, int $id): void
    {
        if (!self::ready($type)) {
            return;
        }
        $c = self::config($type);
        $status = $this->status($type, $id);
        if ($type === 'costing' && $status === 'cancel'
            && strtoupper(trim((string) $this->db()->table('costings')->where('id', $id)->value('status'))) === 'CANCEL') {
            return; // dibatalkan lewat status bisnis Costing, bukan ditolak approval
        }
        if (!in_array($status, ['return', 'cancel', 'unlocked'], true) || ($type === 'costing' && $status === 'unlocked')) {
            return;
        }
        $this->db()->transaction(function ($db) use ($c, $type, $id, $status) {
            if ($status === 'unlocked') {
                $db->table($c['unlock_table'])->where($c['unlock_id'], $id)->where('status', self::UNLOCK_APPROVED)
                    ->whereRaw(self::activeUnlockWhere())->update(['status' => self::UNLOCK_DONE]);
                $this->bumpRevision($type, $id);
            }
            $db->table($c['table'])->where('id', $id)->update(['approval' => 'W']);
            $this->log($type, $id, 'resubmitted', $status, 'awaiting', "{$c['label']} {$this->number($type, $id)} diubah dan menunggu approval lagi.");
        });
    }

    /** Approve / Return to Staff / Cancel; hanya dokumen Awaiting yang diproses. */
    public function decide(string $type, array $ids, string $action, ?string $note = null, ?string $seenAt = null): array
    {
        $this->assertManager();
        $c = self::config($type);
        if (!in_array($action, self::ACTIONS, true)) {
            throw new \InvalidArgumentException('Aksi approval tidak valid.');
        }
        $note = trim((string) $note);
        if ($action !== 'approve' && $note === '') {
            throw new \RuntimeException('Catatan wajib diisi untuk Return to Staff / Cancel.');
        }
        $ids = array_values(array_unique(array_map('intval', $ids)));
        $db = $this->db();

        return $db->transaction(function () use ($db, $c, $type, $ids, $action, $note, $seenAt) {
            $eligible = $db->table($c['table'] . ' as x')->whereIn('x.id', $ids)
                ->whereRaw(self::statusSql($type, 'x') . " = 'awaiting'")->lockForUpdate()->pluck('x.id');
            $reasons = [];
            $eligible = $eligible->filter(function ($id) use ($type, $action, $seenAt, &$reasons) {
                $reason = $this->decideBlock($type, (int) $id, $action, $seenAt);
                if ($reason !== null) {
                    $reasons[] = $this->number($type, (int) $id) . ': ' . $reason;
                }

                return $reason === null;
            })->values();
            $update = ['approval' => self::ACTION_VALUES[$action]];
            if ($action === 'approve') {
                $update['approved_by'] = app(MarketingNdsCostingService::class)->userId();
                $update['approved_at'] = now();
            }
            if ($eligible->isNotEmpty()) {
                $db->table($c['table'])->whereIn('id', $eligible)->update($update);
            }
            foreach ($eligible as $id) {
                $id = (int) $id;
                if ($action !== 'approve') {
                    $this->log($type, $id, $action, 'awaiting', self::STATUS_OF_ACTION[$action], $note);
                    continue;
                }
                $stamp = MarketingHistoryService::stamp();
                $this->log($type, $id, 'approved', 'awaiting', 'approved', MarketingHistoryService::actor() . " changed status from Awaiting Approval to Approved on {$stamp}. This request has been approved.");
                // Costing siap dibuat SO; BOM cukup dicatat Approved (dulu salah berlabel "Sales Order BOM/...").
                $this->log($type, $id, 'ready', null, null, $type === 'costing'
                    ? "Costing {$this->number($type, $id)} is Approved and ready to be created as a Sales Order on {$stamp}."
                    : "BOM {$this->number($type, $id)} is Approved on {$stamp}.", true);
            }

            return ['processed' => $eligible->count(), 'skipped' => count($ids) - $eligible->count(), 'reasons' => $reasons];
        });
    }

    /**
     * Marketing Manager = role superadmin, atau role yang diberi akses khusus "marketing_manager" (tabel access, lihat
     * database/sql/costing_legacy_columns.sql). Akses "all" / role admin TIDAK otomatis jadi manager, karena approval harus tegas.
     */
    public function isManager(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        foreach ($user->roles as $role) {
            if ($role->nama_role === 'superadmin' || $role->accesses->contains('access', 'marketing_manager')) {
                return true;
            }
        }

        return false;
    }

    private function assertManager(): void
    {
        if (!$this->isManager()) {
            throw new \RuntimeException('Hanya Marketing Manager yang bisa memproses approval dan unlock.');
        }
    }

    /**
     * Alasan dokumen Awaiting tidak boleh diproses manager ini; null = boleh.
     * - dokumen buatan sendiri tidak boleh di-approve sendiri;
     * - isi berubah setelah manager membuka daftar (seen_at) -> buka ulang dulu;
     * - BOM: Costing-nya harus Approved, dan semua material punya item + cons.
     */
    private function decideBlock(string $type, int $id, string $action, ?string $seenAt): ?string
    {
        $db = $this->db();
        $c = self::config($type);
        $doc = $db->table($c['table'])->where('id', $id)->first();
        if ($action === 'approve' && $this->isRequester($doc->created_by ?? null)) {
            return 'tidak bisa meng-approve dokumen buatan sendiri.';
        }
        if ($type === 'costing' && $action === 'cancel' && $this->hasActiveSo($id)) {
            return 'sudah punya Sales Order aktif. Batalkan / ubah SO-nya dulu.';
        }
        if ($seenAt && $this->changedSince($type, $id, $seenAt)) {
            return 'isinya berubah setelah daftar dibuka. Muat ulang daftar lalu cek lagi.';
        }
        if ($type === 'bom' && $action === 'approve') {
            if ($this->status('costing', (int) $doc->costings_id) !== 'approved') {
                return 'Costing-nya belum Approved.';
            }
            if ($missing = $this->bomIncomplete($id)) {
                return "{$missing} material belum punya item / cons.";
            }
        }

        return null;
    }

    /** Costing punya Sales Order aktif (SO tidak di-cancel). */
    public function hasActiveSo(int $costingId): bool
    {
        return $this->db()->table('sales_orders')->where('costing_id', $costingId)
            ->where(fn ($q) => $q->whereNull('cancel')->orWhere('cancel', '!=', 'Y'))->exists();
    }

    /** Jumlah material BOM aktif yang item atau cons-nya kosong (baris induk tanpa rincian, atau rincian yang tidak mewarisi isian). */
    public function bomIncomplete(int $bomId): int
    {
        $active = fn ($q, $col) => $q->where(fn ($w) => $w->whereNull($col)->orWhere($col, '!=', 'Y'));
        $materials = $active($this->db()->table('bom_materials'), 'cancel')->where('bom_id', $bomId)->get(['id', 'items_id', 'cons']);
        $details = $active($this->db()->table('bom_material_details'), 'cancel')->whereIn('bom_materials_id', $materials->pluck('id'))->get(['bom_materials_id', 'master_items_id', 'cons'])->groupBy('bom_materials_id');
        $bad = 0;
        foreach ($materials as $m) {
            $rows = $details[$m->id] ?? collect([(object) ['master_items_id' => null, 'cons' => null]]);
            foreach ($rows as $d) {
                if (!($d->master_items_id ?: $m->items_id) || (float) ($d->cons ?? $m->cons) <= 0) {
                    $bad++;
                    break;
                }
            }
        }

        return $bad;
    }

    /** Dokumen (header atau rinciannya) diubah setelah $since: dari updated_at, tidak hanya dari log. */
    private function changedSince(string $type, int $id, $since): bool
    {
        $db = $this->db();
        if ($type === 'costing') {
            return $db->table('costings')->where('id', $id)->where('updated_at', '>', $since)->exists()
                || $db->table('costing_materials')->where('costings_id', $id)->where('updated_at', '>', $since)->exists()
                || $db->table('costing_charges')->where('costings_id', $id)->where('updated_at', '>', $since)->exists();
        }

        return $db->table('bom')->where('id', $id)->where('updated_at', '>', $since)->exists()
            || $db->table('bom_materials')->where('bom_id', $id)->where('updated_at', '>', $since)->exists()
            || $db->table('bom_material_details as d')->join('bom_materials as m', 'm.id', '=', 'd.bom_materials_id')
                ->where('m.bom_id', $id)->where('d.updated_at', '>', $since)->exists();
    }

    /** Request unlock: baris status W dengan unlock_date NULL (sama dengan Signalbit). */
    public function requestUnlock(string $type, int $id, string $reason): void
    {
        self::assertUnlockReady($type);
        $c = self::config($type);
        $reason = mb_substr(trim($reason), 0, 100);
        $this->db()->transaction(function ($db) use ($c, $type, $id, $reason) {
            $db->table($c['table'])->where('id', $id)->lockForUpdate()->first();
            if ($this->status($type, $id) !== 'approved') {
                throw new \RuntimeException("{$c['label']} tidak sedang terkunci.");
            }
            if ($this->hasPendingUnlock($type, $id)) {
                throw new \RuntimeException("Request unlock untuk {$c['label']} ini masih menunggu.");
            }
            $db->table($c['unlock_table'])->insert([
                $c['unlock_id'] => $id,
                $c['unlock_no'] => mb_substr($this->number($type, $id), 0, 20),
                'unlock_date' => null,
                'reason' => $reason,
                'username' => mb_substr((string) $this->loginName(), 0, 10),
                'dateinput' => now(),
                'status' => self::UNLOCK_WAITING,
            ]);
            $this->log($type, $id, 'unlock_requested', null, null, 'Request unlock: ' . $reason);
        });
    }

    /** Peminta membatalkan request yang masih menunggu (W -> C). */
    public function cancelUnlockRequest(string $type, int $id): void
    {
        self::assertUnlockReady($type);
        $c = self::config($type);
        $this->db()->transaction(function ($db) use ($c, $type, $id) {
            $requests = $db->table($c['unlock_table'])->where($c['unlock_id'], $id)->where('status', self::UNLOCK_WAITING)->lockForUpdate()->get(['username']);
            if ($requests->isEmpty()) {
                throw new \RuntimeException('Tidak ada request unlock yang menunggu.');
            }
            if (!$requests->contains(fn ($row) => $this->isRequester($row->username))) {
                throw new \RuntimeException('Hanya peminta yang bisa membatalkan request unlock.');
            }
            $db->table($c['unlock_table'])->where($c['unlock_id'], $id)->where('status', self::UNLOCK_WAITING)->update(['status' => self::UNLOCK_CANCELLED]);
            $this->log($type, $id, 'unlock_cancelled', null, null, "Request unlock {$c['label']} {$this->number($type, $id)} dibatalkan.");
        });
    }

    /** Tombol Unlock di Approval Center: baris request jadi status A, unlock_date hari ini (berlaku 2 hari). */
    public function unlock(string $type, array $ids): int
    {
        self::assertUnlockReady($type);
        $this->assertManager();
        $c = self::config($type);

        return $this->db()->transaction(function ($db) use ($c, $type, $ids) {
            $processed = 0;
            foreach (array_unique(array_map('intval', $ids)) as $id) {
                $requester = $db->table($c['unlock_table'])->where($c['unlock_id'], $id)->where('status', self::UNLOCK_WAITING)->value('username');
                if ($requester !== null && $this->isRequester($requester)) {
                    continue; // request unlock sendiri harus dibuka manager lain
                }
                $done = $db->table($c['unlock_table'])->where($c['unlock_id'], $id)->where('status', self::UNLOCK_WAITING)->update([
                    'status' => self::UNLOCK_APPROVED,
                    'unlock_date' => now()->toDateString(),
                    'approved_by' => mb_substr((string) $this->userName(), 0, 50),
                    'approved_at' => now(),
                ]);
                if (!$done) {
                    continue;
                }
                $processed++;
                $this->log($type, $id, 'unlocked', 'approved', 'unlocked', "{$c['label']} {$this->number($type, $id)} di-unlock (berlaku sampai " . now()->addDay()->format('d M Y') . ').');
            }

            return $processed;
        });
    }

    /** Save and lock ($saved) / Cancel editing Costing yang di-unlock (A -> D). Diubah -> Awaiting, tidak diubah -> tetap Approved. */
    public function relockCosting(int $costingId, bool $saved): void
    {
        $state = $this->state('costing', $costingId);
        if ($state['state'] !== 'unlocked') {
            throw new \RuntimeException('Costing tidak sedang di-unlock.');
        }
        if (!$state['can_edit']) {
            throw new \RuntimeException('Hanya peminta unlock yang bisa mengunci Costing ini.');
        }
        $this->db()->transaction(function ($db) use ($costingId, $saved, $state) {
            $db->table('unlock_cost')->where('id_cost', $costingId)->where('status', self::UNLOCK_APPROVED)
                ->whereRaw(self::activeUnlockWhere())->update(['status' => self::UNLOCK_DONE]);
            $number = $this->number('costing', $costingId);
            $unlockedAt = $state['unlock']->approved_at ?? $state['unlock']->dateinput ?? null;
            if (!$saved && !$this->costingChangedSince($costingId, $unlockedAt)) {
                $this->log('costing', $costingId, 'relocked', 'unlocked', 'approved', "Edit Costing {$number} dibatalkan, Costing dikunci lagi.");

                return;
            }
            $db->table('costings')->where('id', $costingId)->update(['approval' => 'W']);
            $this->bumpRevision('costing', $costingId);
            $this->log('costing', $costingId, 'resubmitted', 'unlocked', 'awaiting', "Costing {$number} Rev {$state['rev']} diubah dan menunggu approval manager lagi.");
        });
    }

    /** Masa unlock habis: Costing yang sudah diedit kembali Awaiting; selain itu tetap Approved (baris unlock ditutup D). */
    public function closeExpiredUnlocks(string $type, ?int $id = null): int
    {
        if (!self::unlockReady($type)) {
            return 0;
        }
        $c = self::config($type);
        $db = $this->db();
        $expired = $db->table($c['unlock_table'] . ' as u')
            ->join($c['table'] . ' as x', 'x.id', '=', 'u.' . $c['unlock_id'])
            ->where('u.status', self::UNLOCK_APPROVED)->whereNotNull('u.approved_at')
            ->whereRaw('DATE_ADD(u.unlock_date, INTERVAL 2 DAY) <= CURDATE()')
            ->when($id, fn ($q) => $q->where('u.' . $c['unlock_id'], $id))
            ->get(['u.' . $c['unlock_id'] . ' as doc_id', 'u.approved_at', 'x.approval']);
        foreach ($expired as $row) {
            $docId = (int) $row->doc_id;
            $db->transaction(function ($db) use ($c, $type, $docId, $row) {
                $db->table($c['unlock_table'])->where($c['unlock_id'], $docId)->where('status', self::UNLOCK_APPROVED)
                    ->where('approved_at', $row->approved_at)->update(['status' => self::UNLOCK_DONE]);
                if ($type === 'costing' && in_array(strtoupper((string) $row->approval), ['A', 'Y'], true) && $this->costingChangedSince($docId, $row->approved_at)) {
                    $db->table('costings')->where('id', $docId)->update(['approval' => 'W']);
                    $this->bumpRevision($type, $docId);
                    $this->log($type, $docId, 'resubmitted', 'unlocked', 'awaiting', "Masa unlock Costing {$this->number($type, $docId)} habis, perubahannya menunggu approval manager lagi.", true);

                    return;
                }
                $this->log($type, $docId, 'relocked', 'unlocked', 'approved', "Masa unlock {$c['label']} {$this->number($type, $docId)} habis, dokumen dikunci lagi.", true);
            });
        }

        return $expired->count();
    }

    /** Costing belum boleh dibuat SO: pesan alasannya; null = boleh (Approved / Unlocked). */
    public function soBlockMessage(int $costingId): ?string
    {
        if (!self::ready('costing')) {
            return null;
        }

        // Hanya Costing Approved yang terkunci; Unlocked sedang bisa diubah, jadi SO menunggu Costing dikunci lagi.
        return match ($this->status('costing', $costingId)) {
            'approved' => null,
            'unlocked' => 'Costing sedang di-unlock dan bisa berubah. SO baru bisa dibuat setelah Costing dikunci lagi (Save and lock) dan Approved.',
            'return' => 'Costing dikembalikan ke staff (Return to Staff). Perbaiki dan simpan Costing, lalu tunggu approval sebelum membuat SO.',
            'cancel' => 'Costing ditolak (Cancel), jadi belum bisa dibuat SO.',
            default => 'Costing belum di-approve. SO baru bisa dibuat setelah Costing disetujui Marketing Manager di Approval Center.',
        };
    }

    /**
     * Sales Order tidak punya approval: bebas diedit, kecuali salah satu WS-nya sudah dipakai di Marker (marker_input di DB
     * produksi, koneksi default; act_costing_ws = No WS). Pesan untuk pop-up tombol Edit; null = boleh diedit.
     */
    public function soMarkerMessage(int $soId): ?string
    {
        $ws = $this->soMarkerWorksheets($soId);

        return $ws ? 'Sales Order ini sudah ada di Marker (WS ' . implode(', ', $ws) . '). Hubungi IT kalau mau mengubahnya.' : null;
    }

    /** No WS milik SO yang sudah dipakai di Marker (marker yang tidak di-cancel). */
    public function soMarkerWorksheets(int $soId): array
    {
        $worksheets = $this->db()->table('so_line')->where('sales_order_id', $soId)
            ->where(fn ($q) => $q->whereNull('cancel')->orWhere('cancel', '!=', 'Y'))
            ->pluck('no_ws')->filter()->values()->all();
        if (!$worksheets) {
            return [];
        }

        return DB::connection()->table('marker_input')->whereIn('act_costing_ws', $worksheets)
            ->where(fn ($q) => $q->whereNull('cancel')->orWhere('cancel', '!=', 'Y'))
            ->distinct()->pluck('act_costing_ws')->all();
    }

    /** Approver untuk dokumen (approved_by -> users.username, approved_at) bila sedang Approved / Unlocked. */
    public function approver(string $type, int $id): ?array
    {
        if (!in_array($this->status($type, $id), ['approved', 'unlocked'], true)) {
            return null;
        }
        $row = $this->db()->table(self::config($type)['table'] . ' as x')->leftJoin('users as u', 'u.id', '=', 'x.approved_by')
            ->where('x.id', $id)->first(['x.approved_by', 'x.approved_at', 'u.username']);

        return $row && $row->approved_by ? ['name' => $row->username ?: ('user-' . $row->approved_by), 'at' => $row->approved_at] : null;
    }

    /** Catatan Return / Cancel terakhir per dokumen (dari process_logs). */
    public function latestNotes(string $type, array $ids): array
    {
        if (!$ids) {
            return [];
        }
        $c = self::config($type);

        return $this->db()->table('process_logs')->whereIn($c['log_column'], $ids)->where('doc_type', $type)
            ->whereIn('action', ['return', 'cancel'])->orderBy('id')->pluck('note', $c['log_column'])->all();
    }

    /**
     * Revisi = dokumen Approved yang diubah lewat unlock lalu diserahkan lagi (sama dengan hitungan Rev di Signalbit:
     * unlock yang selesai dengan perubahan). revision_no dipakai No Rev di PDF / Excel.
     */
    private function bumpRevision(string $type, int $id): void
    {
        $this->db()->table(self::config($type)['table'])->where('id', $id)
            ->update(['revision_no' => DB::raw('COALESCE(revision_no, 0) + 1')]);
    }

    private function costingChangedSince(int $costingId, $since): bool
    {
        if ($since && $this->changedSince('costing', $costingId, $since)) {
            return true;
        }

        return $this->db()->table('process_logs')->where('costings_id', $costingId)
            ->whereIn('doc_type', ['costing', 'costing_item'])
            ->whereIn('action', ['updated', 'items_added', 'items_updated', 'items_deleted'])
            ->when($since, fn ($q) => $q->where('action_at', '>=', $since))
            ->exists();
    }

    private function number(string $type, int $id): string
    {
        $c = self::config($type);

        return (string) $this->db()->table($c['table'])->where('id', $id)->value($c['number']);
    }

    /** Histori (timeline) di process_logs beserta status sebelum/sesudah. */
    private function log(string $type, int $id, string $action, ?string $from, ?string $to, string $note, bool $bySystem = false): void
    {
        $bom = $type === 'bom' ? $this->db()->table('bom as b')->leftJoin('so_line as l', 'l.id', '=', 'b.so_line_id')
            ->where('b.id', $id)->first(['b.costings_id', 'l.sales_order_id']) : null;
        $this->db()->table('process_logs')->insert([
            'costings_id' => $type === 'costing' ? $id : ($bom->costings_id ?? null),
            'sales_orders_id' => $bom->sales_order_id ?? null,
            'bom_id' => $type === 'bom' ? $id : null,
            'doc_type' => $type,
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to,
            'note' => $note,
            'action_by' => $bySystem ? null : app(MarketingNdsCostingService::class)->userId(),
            'action_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function isRequester(?string $username): bool
    {
        if ($username === null || trim($username) === '') {
            return false;
        }
        // Username lengkap harus sama persis; tabel unlock lama hanya menyimpan 10 huruf, jadi nilai 10 huruf dicocokkan dengan awalan login.
        $name = trim($username);
        $login = (string) $this->loginName();

        return strcasecmp($name, $login) === 0 || (mb_strlen($name) === 10 && strcasecmp($name, (string) mb_substr($login, 0, 10)) === 0);
    }

    private function userName(): ?string
    {
        $user = Auth::user();

        return $user ? ($user->name ?: $user->username) : null;
    }

    private function loginName(): ?string
    {
        $user = Auth::user();

        return $user ? ($user->username ?: $user->name) : null;
    }
}
