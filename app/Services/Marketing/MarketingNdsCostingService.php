<?php

namespace App\Services\Marketing;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

/**
 * Percobaan DB baru marketing (koneksi mysql_mkt). Saat aktif, Costing dan Sales Order hanya memakai DB baru;
 * tidak ada yang ditulis atau dibaca dari Signalbit. Sinkronisasi ke Signalbit dikerjakan terpisah nanti.
 */
class MarketingNdsCostingService
{
    private const CONNECTION = 'mysql_mkt';

    // Urutan set di form dan di costing_set.
    private const SET_ORDER = ['TOP' => 1, 'BOTTOM' => 2];

    // Nama kolom DataTables di view => kolom di DB baru. Kolom lain tidak bisa dicari atau diurutkan.
    private const LIST_COLUMNS = [
        'a.cost_no' => 'c.costing_no',
        'a.dateinput' => 'c.created_at',
        's.so_no' => 's.so_no',
        'b.Supplier' => 'b.name',
        'a.brand' => 'br.brand_name',
        'a.styleno' => 'c.style',
        'a.qty' => 'c.planned_qty',
        'a.cfm_price' => 'c.fob_price',
        'a.mkt_order' => 'o.office_name',
        'p.product_group' => 'g.product_group_name',
        'p.product_item' => 'si.item_names',
        'a.kpno' => 'ws.ws_list',
    ];

    /** @var array<int, array> BOM aktif per SO (cache per request listing). */
    private array $bomLists = [];

    /** BOM aktif per WS di SO ini: id, bom_no, no_ws, status approval, unlock_pending. */
    private function bomList($soId): array
    {
        if (!$soId) {
            return [];
        }
        if (!isset($this->bomLists[$soId])) {
            $approval = MarketingNdsApprovalService::ready('bom');
            $this->bomLists[$soId] = DB::connection(self::CONNECTION)->table('bom as b')
                ->join('so_line as l', 'l.id', '=', 'b.so_line_id')->where('l.sales_order_id', $soId)
                ->where(fn ($q) => $q->whereNull('b.cancel')->orWhere('b.cancel', '!=', 'Y'))
                ->orderBy('l.id')
                ->get([
                    'b.id', 'b.bom_no', 'l.no_ws',
                    DB::raw(($approval ? MarketingNdsApprovalService::statusSql('bom', 'b') : "'awaiting'") . ' as status'),
                    DB::raw(($approval ? 'IF(' . MarketingNdsApprovalService::pendingUnlockSql('bom', 'b.id') . ', 1, 0)' : '0') . ' as unlock_pending'),
                ])
                ->map(fn ($b) => (array) $b)->all();
        }

        return $this->bomLists[$soId];
    }

    /**
     * Alasan SO perlu disesuaikan karena Costing berubah (titik merah di tombol SO pada listing):
     * set Costing yang belum / tidak lagi punya WS di SO. Hilang setelah SO disimpan ulang dan set-nya cocok.
     */
    private function soStale($soId, $costingId): array
    {
        if (!$soId) {
            return [];
        }
        $db = DB::connection(self::CONNECTION);
        $active = fn ($q, $alias) => $q->where(fn ($w) => $w->whereNull("{$alias}.cancel")->orWhere("{$alias}.cancel", '!=', 'Y'));
        $sets = $active($db->table('costing_set as s'), 's')->where('s.costing_id', $costingId)->pluck('s.updated_at', 's.id');
        $lineSets = $active($db->table('so_line as l'), 'l')->where('l.sales_order_id', $soId)->pluck('l.costing_set_id')->filter()->map(fn ($id) => (int) $id);
        $reasons = [];
        $added = $sets->keys()->map(fn ($id) => (int) $id)->diff($lineSets);
        if ($added->isNotEmpty()) {
            $reasons[] = $added->count() . ' set baru di Costing belum punya WS di SO ini';
        }
        $removed = $lineSets->diff($sets->keys()->map(fn ($id) => (int) $id));
        if ($removed->isNotEmpty()) {
            $reasons[] = $removed->count() . ' WS di SO ini set-nya sudah dihapus dari Costing';
        }
        return $reasons;
    }

    public static function enabled(): bool
    {
        return (bool) config('marketing.nds_trial');
    }

    /** Kolom created_by yang sudah ada di DB baru, di-cache per tabel. */
    private static array $creatorColumns = [];

    /**
     * ['created_by' => username login] untuk ditambahkan ke baris insert, VARCHAR(50) berisi teks (bukan user id).
     * Kosong bila tabel belum punya kolom created_by, supaya insert tetap jalan sebelum SQL-nya dijalankan.
     */
    public static function creator(string $table): array
    {
        if (!array_key_exists($table, self::$creatorColumns)) {
            self::$creatorColumns[$table] = Schema::connection(self::CONNECTION)->hasColumn($table, 'created_by');
        }
        if (!self::$creatorColumns[$table]) {
            return [];
        }
        $user = Auth::user();
        $name = $user ? ($user->username ?? $user->name ?? ('user-' . $user->id)) : null;

        return ['created_by' => $name !== null ? mb_substr((string) $name, 0, 50) : null];
    }

    /**
     * Saring opsi dropdown halaman lama: hanya yang ada di master DB baru.
     * $keep = nilai yang sedang terpilih. $prefix = awalan kode di DB baru (mis. SSN-): sisanya adalah id Signalbit.
     */
    public function limitOptions($rows, string $rowKey, string $table, string $column, $keep = null, ?\Closure $scope = null, ?string $prefix = null)
    {
        if (!self::enabled()) {
            return $rows;
        }
        $normalize = fn ($value) => mb_strtoupper(trim((string) $value));

        try {
            $query = DB::connection(self::CONNECTION)->table($table)->whereNotNull($column)
                ->where(fn ($q) => $q->whereNull('cancel')->orWhere('cancel', 'N'));
            if ($prefix) {
                $query->where($column, 'like', $prefix . '%');
            }
            if ($scope) {
                $scope($query);
            }
            $allowed = $query->pluck($column)->map(fn ($value) => $normalize($prefix ? Str::after($value, $prefix) : $value))->flip();
        } catch (\Throwable $exception) {
            logger()->error('Gagal membaca master DB baru.', ['table' => $table, 'exception' => $exception->getMessage()]);

            return $rows;
        }

        return collect($rows)->filter(function ($row) use ($rowKey, $allowed, $keep, $normalize) {
            $value = $normalize($row->{$rowKey});

            return $allowed->has($value) || ($value !== '' && $value === $normalize($keep));
        })->values();
    }

    /** Pilihan Product Item form Costing: hanya item yang sudah punya product_set (TOP/BOTTOM). $keep = item yang sedang terpilih. */
    public function productItemOptions(array $keep = [])
    {
        return DB::connection(self::CONNECTION)->table('master_product_items')
            ->where(function ($q) use ($keep) {
                $q->where(fn ($active) => $active
                    ->where(fn ($cancel) => $cancel->whereNull('cancel')->orWhere('cancel', 'N'))
                    ->whereNotNull('product_set')
                    ->where('product_set', '!=', ''))
                    ->orWhereIn('id', $keep);
            })
            ->orderBy('product_item_name')
            ->get(['id', 'product_item_name as product_item', 'product_set'])
            ->each(fn ($item) => $item->product_set = $item->product_set === null ? null : strtoupper(trim($item->product_set)));
    }

    /**
     * Mengurutkan Product Item terpilih, TOP lebih dulu dari BOTTOM.
     *
     * @return array{ids: array<int, int>, missing: array<int, string>}
     */
    public function sortProductItems(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        $items = DB::connection(self::CONNECTION)->table('master_product_items')->whereIn('id', $ids)->get(['id', 'product_item_name', 'product_set'])->keyBy('id');
        $order = fn ($id) => self::SET_ORDER[strtoupper(trim((string) ($items[$id]->product_set ?? '')))] ?? PHP_INT_MAX;
        usort($ids, fn ($a, $b) => $order($a) <=> $order($b));

        return [
            'ids' => $ids,
            'missing' => array_values(array_map(
                fn ($id) => $items[$id]->product_item_name ?? (string) $id,
                array_filter($ids, fn ($id) => trim((string) ($items[$id]->product_set ?? '')) === '')
            )),
        ];
    }

    /**
     * Set Costing beserta namanya. Nama = product_set item (TOP, BOTTOM); kalau dua item bertipe sama, atau belum punya tipe, memakai nama item.
     *
     * @return array{sets: \Illuminate\Support\Collection, shared: array<string, array<int, string>>}
     */
    public function costingSets(int $costingId): array
    {
        $sets = DB::connection(self::CONNECTION)->table('costing_set as s')
            ->leftJoin('master_product_items as p', 'p.id', '=', 's.product_items_id')
            ->leftJoin('master_product_group as g', 'g.id', '=', 'p.product_group_id')
            ->where('s.costing_id', $costingId)
            ->where(fn ($q) => $q->whereNull('s.cancel')->orWhere('s.cancel', '!=', 'Y'))
            ->orderBy('s.id')
            ->get(['s.id', 's.product_items_id', 'p.product_item_name', 'p.product_set', 'g.product_group_name'])
            // TOP lebih dulu, walaupun item BOTTOM ditambahkan lebih awal ke Costing.
            ->sortBy(fn ($set) => self::SET_ORDER[strtoupper(trim((string) $set->product_set))] ?? PHP_INT_MAX)
            ->values();
        $labelOf = fn ($set) => strtoupper(trim((string) $set->product_set));
        $counts = $sets->map($labelOf)->countBy();

        return [
            'sets' => $sets->map(function ($set) use ($labelOf, $counts) {
                $label = $labelOf($set);
                $set->set_type = $label !== '' && $counts[$label] === 1 ? $label : $set->product_item_name;

                return $set;
            }),
            'shared' => $sets->filter(fn ($set) => $labelOf($set) !== '' && $counts[$labelOf($set)] > 1)
                ->groupBy($labelOf)
                ->map(fn ($group) => $group->pluck('product_item_name')->all())
                ->all(),
        ];
    }

    /** Content CMT pada grup Manufacturing. */
    public function cmtContentId(): ?int
    {
        $id = DB::connection(self::CONNECTION)->table('master_contents as m')
            ->join('master_group as g', 'g.id', '=', 'm.group_id')
            ->where('g.group_code', 'M')
            ->where('m.content_code', 'CMT')
            ->where(fn ($q) => $q->whereNull('m.cancel')->orWhere('m.cancel', 'N'))
            ->value('m.id');

        return $id ? (int) $id : null;
    }

    /**
     * Syarat Costing bisa dipakai lanjut: tiap set punya baris CMT dengan harga.
     *
     * @return array<int, string>
     */
    public function cmtIssues(int $costingId): array
    {
        $contentId = $this->cmtContentId();
        if (!$contentId) {
            return ['Master CMT belum ada. Tambahkan dulu content CMT di grup Manufacturing.'];
        }
        $priced = DB::connection(self::CONNECTION)->table('costing_charges')
            ->where(fn ($q) => $q->whereNull('cancel')->orWhere('cancel', '!=', 'Y'))
            ->where('costings_id', $costingId)
            ->where('contents_id', $contentId)
            ->where('price', '>', 0)
            ->pluck('costing_set_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $missing = $this->costingSets($costingId)['sets']
            ->reject(fn ($set) => in_array((int) $set->id, $priced, true))
            ->pluck('set_type')
            ->unique()
            ->values();
        if ($missing->isEmpty()) {
            return [];
        }
        $names = $missing->count() > 1 ? $missing->slice(0, -1)->implode(', ') . ' dan ' . $missing->last() : $missing->first();

        return ["Harga CMT untuk product item {$names} pada tab Manufacturing belum diisi."];
    }

    /** Ringkasan Costing untuk modal konfirmasi sebelum Create SO. */
    public function soPreview(int $costingId): ?array
    {
        $db = DB::connection(self::CONNECTION);
        $active = fn ($q, $col) => $q->where(fn ($w) => $w->whereNull($col)->orWhere($col, 'N'));
        $costing = $db->table('costings as c')
            ->leftJoin('master_partners as b', 'b.id', '=', 'c.buyer_id')
            ->leftJoin('master_brands as br', 'br.id', '=', 'c.brand_id')
            ->leftJoin('master_currencies as cu', 'cu.id', '=', 'c.currency_id')
            ->where('c.id', $costingId)
            ->first(['c.id', 'c.costing_no', 'c.costing_date', 'c.style', 'c.planned_qty', 'c.fob_price', 'b.name as buyer', 'br.brand_name as brand', 'cu.currency_code']);
        if (!$costing) {
            return null;
        }
        $setNames = $this->costingSets($costingId)['sets']->pluck('set_type', 'id');
        $setName = fn ($id) => $id ? ($setNames[$id] ?? '-') : 'ALL';

        $parts = $active($db->table('costing_material_parts as mp'), 'mp.cancel')
            ->join('master_parts as p', 'p.id', '=', 'mp.parts_id')
            ->select('mp.costing_materials_id', DB::raw("GROUP_CONCAT(p.part_name ORDER BY mp.id SEPARATOR ', ') as part_name"))
            ->groupBy('mp.costing_materials_id');
        $materials = $active($db->table('costing_materials as d'), 'd.cancel')
            ->leftJoin('master_contents as m', 'm.id', '=', 'd.contents_id')
            ->leftJoin('master_shells as sh', 'sh.id', '=', 'd.shells_id')
            ->leftJoinSub($parts, 'pt', 'pt.costing_materials_id', '=', 'd.id')
            ->leftJoin('master_units as u', 'u.id', '=', 'd.units_id')
            ->where('d.costings_id', $costingId)
            ->orderBy('d.contents_id')->orderBy('d.costing_set_id')->orderBy('d.id')
            ->get(['m.content_name', 'd.costing_set_id', 'sh.shell_name', 'pt.part_name', 'd.cons', 'u.unit_code', 'd.allowance_percent'])
            ->map(fn ($r) => [
                'content' => mb_strtoupper((string) $r->content_name),
                'set' => $setName($r->costing_set_id),
                'shell' => $r->shell_name,
                'part' => $r->part_name,
                'cons' => (float) $r->cons,
                'unit' => $r->unit_code,
                'loss' => (float) $r->allowance_percent,
            ]);
        $charges = $active($db->table('costing_charges as d'), 'd.cancel')
            ->leftJoin('master_contents as m', 'm.id', '=', 'd.contents_id')
            ->where('d.costings_id', $costingId)
            ->orderBy('d.id')
            ->get(['m.content_name', 'd.costing_set_id', 'd.price'])
            ->map(fn ($r) => [
                'content' => mb_strtoupper((string) $r->content_name),
                'set' => $setName($r->costing_set_id),
                'price' => (float) $r->price,
            ]);
        $orders = $active($db->table('sales_orders'), 'cancel')
            ->where('costing_id', $costingId)
            ->orderBy('id')
            ->get(['so_no', 'so_date']);

        return [
            'costing' => $costing,
            'sets' => $setNames->values(),
            'materials' => $materials->values(),
            'charges' => $charges->values(),
            'orders' => $orders,
            'cmt_issues' => $this->cmtIssues($costingId),
        ];
    }

    /** Harga CMT tiap set ke costing_set.cm_price. Dilewati selama kolomnya belum ada. */
    public function syncCmPrice(int $costingId): void
    {
        $contentId = $this->cmtContentId();
        if (!$contentId || !Schema::connection(self::CONNECTION)->hasColumn('costing_set', 'cm_price')) {
            return;
        }
        $db = DB::connection(self::CONNECTION);
        $active = fn ($q) => $q->where(fn ($w) => $w->whereNull('cancel')->orWhere('cancel', '!=', 'Y'));
        foreach ($active($db->table('costing_set')->where('costing_id', $costingId))->pluck('id') as $setId) {
            $db->table('costing_set')->where('id', $setId)->update([
                'cm_price' => $active($db->table('costing_charges')->where('costing_set_id', $setId)->where('contents_id', $contentId))->value('price'),
            ]);
        }
    }

    /**
     * Nomor berikutnya untuk format PREFIX/myy/NNN: urut per tahun. Dipanggil di dalam transaksi supaya baris yang dibaca terkunci.
     */
    public function nextNumber(string $table, string $column, string $prefix, int $digits): string
    {
        $last = DB::connection(self::CONNECTION)->table($table)
            ->where($column, 'like', $prefix . '/__' . now()->format('y') . '/%')
            ->lockForUpdate()
            ->pluck($column)
            ->map(fn ($number) => (int) Str::afterLast($number, '/'))
            ->max();

        return $prefix . '/' . now()->format('my') . '/' . str_pad(($last ?? 0) + 1, $digits, '0', STR_PAD_LEFT);
    }

    /** Baris master yang sudah ada dipakai lagi; yang belum ada dibuat. */
    public function master(string $table, array $key, array $values = []): int
    {
        $db = DB::connection(self::CONNECTION);
        $id = $db->table($table)->where($key)->value('id');
        if ($id) {
            return (int) $id;
        }

        return (int) $db->table($table)->insertGetId($key + $values + ['created_at' => now(), 'updated_at' => now()] + self::creator($table));
    }

    /** Listing Costing. Approval, SO lama, dan BOM belum ada di DB baru, jadi hanya status dan SO baru yang terisi. */
    public function listing(Request $request)
    {
        $db = DB::connection(self::CONNECTION);
        $this->mapRequestColumns($request);
        // Approval Costing & BOM (kolom approval + unlock_cost / unlock_bom); sebelum SQL-nya dijalankan semua dihitung Awaiting.
        $approval = MarketingNdsApprovalService::ready();
        if ($approval) {
            app(MarketingNdsApprovalService::class)->closeExpiredUnlocks('costing');
            app(MarketingNdsApprovalService::class)->closeExpiredUnlocks('bom');
        }
        $costingStatus = $approval ? MarketingNdsApprovalService::statusSql('costing', 'c') : "'awaiting'";

        // Satu Costing bisa punya beberapa set: nama item digabung, group dari set pertama.
        $setItems = $db->table('costing_set as s')
            ->leftJoin('master_product_items as sp', 'sp.id', '=', 's.product_items_id')
            ->where(fn ($q) => $q->whereNull('s.cancel')->orWhere('s.cancel', '!=', 'Y'))
            ->select('s.costing_id', DB::raw('MIN(s.id) as first_set_id'), DB::raw("GROUP_CONCAT(DISTINCT sp.product_item_name ORDER BY s.id SEPARATOR ', ') as item_names"))
            ->groupBy('s.costing_id');
        // Satu baris listing per SO (Add SO = baris baru); Costing tanpa SO tetap satu baris. so_count untuk keterangan Add SO.
        $activeSo = fn ($q, $alias) => $q->where(fn ($w) => $w->whereNull("{$alias}.cancel")->orWhere("{$alias}.cancel", '!=', 'Y'));
        $soCounts = $activeSo($db->table('sales_orders as sc'), 'sc')->select('sc.costing_id', DB::raw('COUNT(*) as so_count'))->groupBy('sc.costing_id');
        // Satu SO punya satu WS per set; ditampilkan per baris, TOP lebih dulu.
        $worksheets = $db->table('so_line as l')
            ->leftJoin('costing_set as ls', 'ls.id', '=', 'l.costing_set_id')
            ->leftJoin('master_product_items as lp', 'lp.id', '=', 'ls.product_items_id')
            ->select('l.sales_order_id', DB::raw("GROUP_CONCAT(l.no_ws ORDER BY FIELD(UPPER(TRIM(lp.product_set)), 'BOTTOM', 'TOP') DESC, l.id SEPARATOR '\\n') as ws_list"), DB::raw('COUNT(*) as ws_count'))
            ->groupBy('l.sales_order_id');

        $data = $db->table('costings as c')
            ->leftJoin('master_partners as b', 'b.id', '=', 'c.buyer_id')
            ->leftJoin('master_brands as br', 'br.id', '=', 'c.brand_id')
            ->leftJoin('master_marketing_office as o', 'o.id', '=', 'c.marketing_office_id')
            ->leftJoinSub($setItems, 'si', 'si.costing_id', '=', 'c.id')
            ->leftJoin('costing_set as cs', 'cs.id', '=', 'si.first_set_id')
            ->leftJoin('master_product_items as p', 'p.id', '=', 'cs.product_items_id')
            ->leftJoin('master_product_group as g', 'g.id', '=', 'p.product_group_id')
            ->leftJoinSub($soCounts, 'so_latest', 'so_latest.costing_id', '=', 'c.id')
            ->leftJoin('sales_orders as s', fn ($j) => $j->on('s.costing_id', '=', 'c.id')
                ->where(fn ($w) => $w->whereNull('s.cancel')->orWhere('s.cancel', '!=', 'Y')))
            ->leftJoinSub($worksheets, 'ws', 'ws.sales_order_id', '=', 's.id')
            ->select(
                'c.id',
                'c.costing_no as cost_no',
                'c.costing_date as cost_date',
                'b.name as nama_buyer',
                'br.brand_name as brand',
                'c.style as styleno',
                'c.planned_qty as qty',
                'c.fob_price as cfm_price',
                'o.office_name as mkt_order',
                'g.product_group_name as product_group_text',
                'si.item_names as product_item_text',
                's.id as so_id',
                's.so_no',
                'so_latest.so_count',
                's.created_at as so_created_at',
                // Approval: menu Costing menampilkan Request Unlock saat Approved (terkunci).
                DB::raw("{$costingStatus} as approval_status"),
                DB::raw(($approval ? 'IF(' . MarketingNdsApprovalService::pendingUnlockSql('costing', 'c.id') . ', 1, 0)' : '0') . ' as costing_unlock_pending'),
                // Add SO hanya untuk Costing Approved (terkunci); Unlocked sedang bisa diubah.
                DB::raw(($approval ? "{$costingStatus} = 'approved'" : '1') . ' as so_allowed'),
                'ws.ws_list as kpno',
                'ws.ws_count',
                'c.created_at as dateinput'
            )
            ->orderBy('c.id', 'desc')
            ->orderBy('s.id', 'desc');

        // Filter Costing Date = tanggal Costing dibuat (created_at).
        if ($request->filled('tgl_awal') && $request->filled('tgl_akhir')) {
            $data->whereDate('c.created_at', '>=', $request->tgl_awal)
                ->whereDate('c.created_at', '<=', $request->tgl_akhir);
        }
        if ($request->filled('buyers')) {
            $data->whereIn('c.buyer_id', (array) $request->buyers);
        }

        // Jumlah per status untuk tab status di atas tabel.
        // Dihitung per Costing (bukan per baris SO).
        $counts = (clone $data)->reorder()->select(DB::raw("{$costingStatus} as st"), DB::raw('COUNT(DISTINCT c.id) as total'))->groupBy('st')->pluck('total', 'st');
        if (in_array($request->approval_status, ['awaiting', 'return', 'cancel', 'approved', 'unlocked'], true)) {
            $data->whereRaw("{$costingStatus} = ?", [$request->approval_status]);
        }

        return DataTables::of($data)
            ->with('status_counts', $counts)
            ->addIndexColumn()
            ->addColumn('tgl_costing', fn ($row) => $row->cost_date ? date('d-m-Y', strtotime($row->cost_date)) : '-')
            ->addColumn('due_date', fn () => '-')
            ->addColumn('costing_date', fn ($row) => $row->dateinput ? date('d M Y', strtotime($row->dateinput)) : '-')
            ->addColumn('market', fn () => null)
            ->addColumn('bom_id', fn () => null)
            ->addColumn('bom_item_count', fn () => 0)
            // Jumlah WS yang BOM-nya perlu diperbarui karena SO / Costing berubah (hanya baris halaman ini yang dihitung).
            // BOM belum sesuai Costing / SO (content ditambah/dihapus di Costing, color/size/destinasi SO berubah): titik merah sampai BOM disesuaikan.
            ->addColumn('bom_stale_detail', fn ($row) => $row->so_id ? app(MarketingNdsBomService::class)->staleDetails((int) $row->so_id) : [])
            // Jumlah WS di SO ini yang sudah punya BOM (header BOM dibuat saat Save pertama di Input BOM): 0 = menu "Create".
            ->addColumn('bom_count', fn ($row) => count($this->bomList($row->so_id)))
            ->addColumn('so_stale', fn ($row) => $this->soStale($row->so_id, $row->id))
            // BOM per WS + status approval: menu BOM menampilkan Request Unlock untuk BOM yang Approved (terkunci), sama seperti Costing.
            ->addColumn('bom_list', fn ($row) => $this->bomList($row->so_id))
            // SO tanpa approval: Edit SO memunculkan pop-up kalau WS-nya sudah dipakai di Marker (hanya baris halaman ini yang dicek).
            ->addColumn('so_marker_message', fn ($row) => $row->so_id ? app(MarketingNdsApprovalService::class)->soMarkerMessage((int) $row->so_id) : null)
            ->editColumn('product_group_text', fn ($row) => $this->titleCase($row->product_group_text))
            ->editColumn('product_item_text', fn ($row) => $this->titleCase($row->product_item_text))
            ->make(true);
    }

    private function mapRequestColumns(Request $request): void
    {
        $columns = $request->input('columns', []);
        foreach ($columns as $index => $column) {
            $name = $column['name'] ?? '';
            if (isset(self::LIST_COLUMNS[$name])) {
                $columns[$index]['name'] = self::LIST_COLUMNS[$name];
            } elseif (preg_match('/^[abp]\./', $name) === 1) {
                $columns[$index]['searchable'] = 'false';
                $columns[$index]['orderable'] = 'false';
            }
        }
        $request->merge(['columns' => $columns]);
    }

    private function titleCase(?string $value): string
    {
        return $value ? ucwords(strtolower($value), " \t\r\n\f\v-") : '-';
    }

    /** Id baris users DB baru untuk user yang login; dibuat kalau belum ada. */
    public function userId(): ?int
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }
        $db = DB::connection(self::CONNECTION);
        if (!$db->table('users')->where('id', $user->id)->exists()) {
            $db->table('users')->insert([
                'id' => $user->id,
                'username' => $user->username ?? ('user-' . $user->id),
                'cancel' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return (int) $user->id;
    }
}
