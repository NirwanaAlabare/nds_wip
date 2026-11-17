<?php

namespace App\Http\Controllers;

use App\Services\MarketingApprovalService;
use App\Services\MarketingHistoryService;
use App\Services\MarketingNdsApprovalService;
use App\Services\MarketingNdsCostingService;
use Illuminate\Database\Query\Expression;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class Marketing_ApprovalCenterController extends Controller
{
    private const STATUSES = ['awaiting', 'return', 'cancel', 'approved', 'unlocked'];

    // Kolom yang bisa dipilih di "Search By"; kunci dipakai front-end.
    private const SEARCH_COLUMNS = [
        'costing' => [
            'number' => 'a.cost_no',
            'worksheet' => 'a.kpno',
            'buyer' => 'b.Supplier',
            'brand' => 'a.brand',
            'style' => 'a.styleno',
        ],
        'bom' => [
            'number' => 'bm.no_katalog_bom',
            'worksheet' => 'a.kpno',
            'buyer' => 'b.Supplier',
            'brand' => 'a.brand',
            'style' => 'a.styleno',
        ],
        'so' => [
            'number' => 'so.so_no',
            'worksheet' => 'a.kpno',
            'buyer' => 'b.Supplier',
            'brand' => 'a.brand',
            'style' => 'a.styleno',
        ],
    ];

    // Costing & SO DB baru: kolom dari subquery ndsQuery() beralias "a".
    private const NDS_SEARCH_COLUMNS = [
        'number' => 'a.doc_no',
        'worksheet' => 'a.kpno',
        'buyer' => 'a.buyer',
        'brand' => 'a.brand',
        'style' => 'a.styleno',
    ];

    public function __construct(private MarketingApprovalService $approvals)
    {
    }

    /** Approval Costing & SO dibaca dari DB baru saat percobaan DB baru aktif dan kolom approval sudah dibuat. */
    private static function nds(?string $type = null): bool
    {
        if (!MarketingNdsCostingService::enabled()) {
            return false;
        }
        if ($type === null) {
            return MarketingNdsApprovalService::ready('costing');
        }

        return in_array($type, MarketingNdsApprovalService::TYPES, true) && MarketingNdsApprovalService::ready($type);
    }

    private function ndsApprovals(): MarketingNdsApprovalService
    {
        return app(MarketingNdsApprovalService::class);
    }

    public function index()
    {
        $nds = self::nds();
        if ($nds) {
            foreach (MarketingNdsApprovalService::TYPES as $type) {
                $this->ndsApprovals()->closeExpiredUnlocks($type);
            }
        }
        $awaiting = fn ($type) => self::nds($type)
            // Costing bisa tampil beberapa baris (per SO): kartu menghitung dokumennya, bukan barisnya.
            ? $this->ndsQuery($type)->where('a.approval_status', 'awaiting')->distinct()->count('a.id')
            : $this->documentQuery($type)->whereRaw(MarketingApprovalService::statusSql($type, $type === 'costing' ? 'a' : 'bm') . " = 'awaiting'")->count();
        $unlocks = fn ($type) => self::nds($type)
            ? $this->ndsQuery($type)->where('a.unlock_pending', 1)->distinct()->count('a.id')
            : $this->unlockRequestQuery($type)->count();

        return view('marketing.approval_center', [
            'page'           => 'dashboard-marketing',
            'subPageGroup'   => 'marketing-approval',
            'subPage'        => 'marketing-approval-center',
            'containerFluid' => true,
            'ndsApproval'    => $nds,
            // Tipe yang dibaca dari DB nds (kolom alias "a"): costing, dan bom setelah kolom approval bom dibuat.
            'ndsTypes'       => array_values(array_filter(MarketingNdsApprovalService::TYPES, fn ($t) => self::nds($t))),
            'summary'        => [
                'costing' => $awaiting('costing'),
                'bom' => $awaiting('bom'),
                'unlock_costing' => $unlocks('costing'),
                'unlock_bom' => $unlocks('bom'),
                // SO DB nds tidak punya approval / unlock; Unlock SO hanya untuk alur Signalbit.
                'unlock_so' => $nds ? 0 : $unlocks('so'),
            ],
        ]);
    }

    /**
     * Costing / BOM DB baru beserta kolom tabel Approval (nama kolom sama dengan tabel Signalbit supaya satu template dipakai).
     * Dibungkus subquery "a" supaya filter, sort, dan status bisa memakai alias. BOM = satu baris per WS (so_line).
     */
    private function ndsQuery(string $type)
    {
        $db = DB::connection('mysql_mkt');
        $items = $db->table('costing_set as cs')
            ->leftJoin('master_product_items as pi', 'pi.id', '=', 'cs.product_items_id')
            ->leftJoin('master_product_group as g', 'g.id', '=', 'pi.product_group_id')
            ->where(fn ($q) => $q->whereNull('cs.cancel')->orWhere('cs.cancel', '!=', 'Y'))
            ->select('cs.costing_id', DB::raw("GROUP_CONCAT(DISTINCT pi.product_item_name ORDER BY cs.id SEPARATOR ', ') as product_item"),
                DB::raw('MIN(g.product_group_name) as product_group'))
            ->groupBy('cs.costing_id');
        $poLines = fn ($key) => $db->table('so_line as l')
            ->join('so_production as sp', 'sp.so_line_id', '=', 'l.id')
            ->join('so_po as p', 'p.so_production_id', '=', 'sp.id')
            ->leftJoin('master_destinations as d', 'd.id', '=', 'p.destination_id')
            ->join('sales_orders as so', 'so.id', '=', 'l.sales_order_id')
            ->where(fn ($q) => $q->whereNull('p.cancel')->orWhere('p.cancel', '!=', 'Y'))
            ->where(fn ($q) => $q->whereNull('sp.cancel')->orWhere('sp.cancel', '!=', 'Y'))
            ->select($key, DB::raw('MIN(p.delivery_date) as deldate'),
                DB::raw("GROUP_CONCAT(DISTINCT l.no_ws ORDER BY l.no_ws SEPARATOR ', ') as kpno"),
                DB::raw("GROUP_CONCAT(DISTINCT d.destination_name ORDER BY d.destination_name SEPARATOR ', ') as market"))
            ->groupBy($key);
        $status = MarketingNdsApprovalService::statusSql($type, $type === 'bom' ? 'bm' : 'c');
        $id = $type === 'bom' ? 'bm.id' : 'c.id';

        $inner = $type === 'bom'
            ? $db->table('bom as bm')
                ->join('costings as c', 'c.id', '=', 'bm.costings_id')
                ->join('so_line as wl', 'wl.id', '=', 'bm.so_line_id')
                ->leftJoinSub($poLines('l.id'), 'ln', 'ln.id', '=', 'wl.id')
                ->leftJoinSub($db->table('so_production')->select('so_line_id', DB::raw('SUM(qty) as qty'))
                    ->where(fn ($q) => $q->whereNull('cancel')->orWhere('cancel', '!=', 'Y'))->groupBy('so_line_id'), 'q', 'q.so_line_id', '=', 'wl.id')
                ->where(fn ($q) => $q->whereNull('bm.cancel')->orWhere('bm.cancel', '!=', 'Y'))
                ->select(['bm.id', 'bm.id as ref_id', 'c.id as id_costing', 'wl.sales_order_id as so_id', 'wl.id as line_id', 'bm.bom_no as doc_no',
                    'bm.bom_date as doc_date', 'ln.deldate', 'wl.no_ws as kpno', 'ln.market', 'q.qty'])
            // Costing: satu baris per SO (sama dengan Marketing List); Costing tanpa SO tetap satu baris. Aksi tetap per id Costing.
            : $db->table('costings as c')
                ->leftJoin('sales_orders as cso', fn ($j) => $j->on('cso.costing_id', '=', 'c.id')
                    ->where(fn ($w) => $w->whereNull('cso.cancel')->orWhere('cso.cancel', '!=', 'Y')))
                ->leftJoinSub($poLines('so.id'), 'ln', 'ln.id', '=', 'cso.id')
                ->leftJoin('master_destinations as md', 'md.id', '=', 'c.main_destination_id')
                ->where(fn ($q) => $q->whereNull('c.cancel')->orWhere('c.cancel', '!=', 'Y'))
                ->select(['c.id', 'c.id as ref_id', 'c.id as id_costing', 'cso.id as so_id', 'cso.so_no', 'c.costing_no as doc_no', 'c.costing_date as doc_date', 'ln.deldate', 'ln.kpno',
                    DB::raw('COALESCE(ln.market, md.destination_name) as market'), 'c.planned_qty as qty']);
        $inner->leftJoin('master_partners as b', 'b.id', '=', 'c.buyer_id')
            ->leftJoin('master_brands as br', 'br.id', '=', 'c.brand_id')
            ->leftJoinSub($items, 'it', 'it.costing_id', '=', 'c.id')
            ->addSelect([
                'b.name as buyer', 'br.brand_name as brand', 'c.style as styleno', 'c.fob_price as cfm_price', 'it.product_group', 'it.product_item',
                DB::raw("{$status} as approval_status"),
                DB::raw("{$status} as doc_status"),
                DB::raw('IF(' . MarketingNdsApprovalService::pendingUnlockSql($type, $id) . ', 1, 0) as unlock_pending'),
                DB::raw(MarketingNdsApprovalService::requestColumnSql($type, $id, 'reason') . ' as reason'),
                DB::raw(MarketingNdsApprovalService::requestColumnSql($type, $id, 'username') . ' as requested_by'),
                DB::raw(MarketingNdsApprovalService::requestColumnSql($type, $id, 'dateinput') . ' as requested_at'),
            ]);

        return $db->query()->fromSub($inner, 'a');
    }

    private function applyNdsFilters($query, Request $request, string $dateColumn): void
    {
        if ($request->filled('tgl_awal') && $request->filled('tgl_akhir')) {
            $query->whereDate($dateColumn, '>=', $request->tgl_awal)->whereDate($dateColumn, '<=', $request->tgl_akhir);
        }
        $keyword = trim((string) $request->keyword);
        if ($keyword === '') {
            return;
        }
        $selected = array_values(array_intersect(array_keys(self::NDS_SEARCH_COLUMNS), (array) $request->search_by)) ?: array_keys(self::NDS_SEARCH_COLUMNS);
        $query->where(function ($q) use ($selected, $keyword) {
            foreach ($selected as $key) {
                $q->orWhere(self::NDS_SEARCH_COLUMNS[$key], 'like', "%{$keyword}%");
            }
        });
    }

    /** Tabel Approval Costing / SO DB baru. */
    private function ndsDocuments(Request $request, string $type)
    {
        $this->ndsApprovals()->closeExpiredUnlocks($type);
        $data = $this->ndsQuery($type);
        $counts = $this->ndsQuery($type);
        foreach ([$data, $counts] as $query) {
            $this->applyNdsFilters($query, $request, 'a.doc_date');
        }
        if (in_array($request->status, self::STATUSES, true)) {
            $data->where('a.approval_status', $request->status);
        }
        if (!$request->filled('order')) {
            $data->orderByDesc('a.id')->orderByDesc('a.so_id');
        }

        $payload = DataTables::of($data)
            ->with('status_counts', $counts->select('a.approval_status', DB::raw('COUNT(DISTINCT a.id) as total'))->groupBy('a.approval_status')->pluck('total', 'approval_status'))
            ->addIndexColumn()
            ->escapeColumns([])
            ->make(true)
            ->getData(true);
        $notes = $this->ndsApprovals()->latestNotes($type, array_column($payload['data'] ?? [], 'id'));
        foreach ($payload['data'] ?? [] as $index => $row) {
            $payload['data'][$index]['approval_note'] = $notes[$row['id']] ?? null;
        }

        return response()->json($payload);
    }

    /** Tabel Approval Costing / Approval BOM / Approval SO (DataTables) + jumlah per status. */
    public function documents(Request $request, string $type)
    {
        if (self::nds($type)) {
            return $this->ndsDocuments($request, $type);
        }
        abort_if($type === 'so', 404);
        if ($type === 'costing') {
            $this->approvals->closeExpiredCostingUnlocks();
        }
        $statusSql = MarketingApprovalService::statusSql($type, $type === 'costing' ? 'a' : 'bm');
        $data = $this->documentQuery($type);
        $counts = $this->documentQuery($type);
        foreach ([$data, $counts] as $query) {
            $this->applyFilters($query, $request, $type);
        }

        $status = in_array($request->status, self::STATUSES, true) ? $request->status : null;
        if ($status) {
            $data->whereRaw($statusSql . ' = ?', [$status]);
        }

        $data->select($type === 'costing'
            ? ['a.id', 'a.id as id_costing', 'a.cost_no as doc_no']
            : ['bm.id', 'a.id as id_costing', 'bm.no_katalog_bom as doc_no'])
            ->addSelect([
                'a.deldate',
                'a.kpno',
                'b.Supplier as buyer',
                'a.brand',
                'a.styleno',
                'a.market',
                'a.qty',
                'a.cfm_price',
                'p.product_group',
                'p.product_item',
                DB::raw($statusSql . ' as approval_status'),
            ]);
        // Urutan default hanya saat user belum klik sort di header tabel.
        if (! $request->filled('order')) {
            $data->orderByDesc($type === 'costing' ? 'a.id' : 'bm.id');
        }

        $response = DataTables::of($data)
            ->with('status_counts', $counts->select(DB::raw($statusSql . ' as approval_status'), DB::raw('COUNT(*) as total'))
                ->groupBy('approval_status')
                ->pluck('total', 'approval_status'))
            ->addIndexColumn()
            // Teks di-escape di browser (escapeHtml); tanpa ini "H&M" tampil "H&amp;amp;M".
            ->escapeColumns([])
            ->make(true);

        // Catatan ada di DB baru, jadi dilengkapi per halaman.
        $payload = $response->getData(true);
        $notes = app(MarketingHistoryService::class)->latestNotes($type, array_column($payload['data'] ?? [], 'id_costing'));
        foreach ($payload['data'] ?? [] as $index => $row) {
            $payload['data'][$index]['approval_note'] = $notes[$row['id_costing']] ?? null;
        }

        return response()->json($payload);
    }

    /** Tabel Unlock Costing / BOM / SO: dokumen yang punya request unlock (status W) di unlock_cost / unlock_jo / unlock_so. */
    public function unlockRequests(Request $request, string $type)
    {
        if (self::nds($type)) {
            $data = $this->ndsQuery($type)->where('a.unlock_pending', 1);
            if (!$request->filled('order')) {
                $data->orderBy('a.id')->orderBy('a.so_id');
            }
            $this->applyNdsFilters($data, $request, 'a.requested_at');

            return DataTables::of($data)->addIndexColumn()->escapeColumns([])->make(true);
        }
        $data = $this->unlockRequestQuery($type);
        if (! $request->filled('order')) {
            $data->orderBy(match ($type) { 'costing' => 'a.id', 'so' => 'so.id', default => 'bm.id' });
        }
        $this->applyFilters($data, $request, $type, DB::raw($this->requestColumnSql($type, 'dateinput')));

        return DataTables::of($data)
            ->addIndexColumn()
            // Teks di-escape di browser (escapeHtml); tanpa ini "H&M" tampil "H&amp;amp;M".
            ->escapeColumns([])
            ->make(true);
    }

    private function unlockRequestQuery(string $type)
    {
        $db = DB::connection('mysql_sb');
        if ($type === 'costing') {
            $query = $db->table('act_costing as a')
                ->whereRaw(MarketingApprovalService::pendingUnlockSql('costing', 'a.id'))
                ->select('a.id', 'a.id as ref_id', 'a.id as id_costing', 'a.cost_no as doc_no')
                ->selectRaw(MarketingApprovalService::statusSql('costing', 'a') . ' as doc_status');
        } elseif ($type === 'so') {
            $query = $db->table('so')
                ->join('act_costing as a', 'a.id', '=', 'so.id_cost')
                ->whereRaw(MarketingApprovalService::pendingUnlockSql('so', 'so.id'))
                ->select('so.id', 'so.id as ref_id', 'a.id as id_costing', 'so.so_no as doc_no')
                ->selectRaw(MarketingApprovalService::statusSql('so', 'so') . ' as doc_status');
        } else {
            $query = $db->table('bom_marketing as bm')
                ->join('act_costing as a', 'a.id', '=', 'bm.id_costing')
                ->whereRaw(MarketingApprovalService::pendingUnlockSql('bom', 'bm.id'))
                ->select('bm.id', 'bm.id as ref_id', 'a.id as id_costing', 'bm.no_katalog_bom as doc_no')
                ->selectRaw(MarketingApprovalService::statusSql('bom', 'bm') . ' as doc_status');
        }

        return $query->leftJoin('mastersupplier as b', 'a.id_buyer', '=', 'b.Id_Supplier')
            ->leftJoin('masterproduct as p', 'a.id_product', '=', 'p.id')
            // Kolom sama dengan tabel Approval (documents) supaya listing Unlock seragam.
            ->addSelect('a.deldate', 'a.kpno', 'b.Supplier as buyer', 'a.brand', 'a.styleno', 'a.market', 'a.qty', 'a.cfm_price', 'p.product_group', 'p.product_item')
            ->selectRaw($this->requestColumnSql($type, 'reason') . ' as reason')
            ->selectRaw($this->requestColumnSql($type, 'username') . ' as requested_by')
            ->selectRaw($this->requestColumnSql($type, 'dateinput') . ' as requested_at');
    }

    /** Kolom dari baris request unlock (status W) terbaru untuk dokumen di baris query. */
    private function requestColumnSql(string $type, string $column): string
    {
        $waiting = "u.status = '" . MarketingApprovalService::UNLOCK_WAITING . "'";
        if ($type === 'costing') {
            return "(SELECT u.{$column} FROM unlock_cost u WHERE u.id_cost = a.id AND {$waiting} ORDER BY u.dateinput DESC LIMIT 1)";
        }
        if ($type === 'so') {
            return "(SELECT u.{$column} FROM unlock_so u WHERE u.id_so = so.id AND {$waiting} ORDER BY u.dateinput DESC LIMIT 1)";
        }

        return "(SELECT u.{$column} FROM unlock_jo u
            JOIN jo_det jd ON jd.id_jo = u.id_jo AND jd.cancel = 'N'
            JOIN so ON so.id = jd.id_so AND so.cancel_h = 'N'
            WHERE so.id_bom = bm.id AND {$waiting} ORDER BY u.dateinput DESC LIMIT 1)";
    }

    public function decide(Request $request, string $type)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'action' => ['required', 'in:' . implode(',', MarketingApprovalService::ACTIONS)],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        return $this->respond(function () use ($validated, $type) {
            if (self::nds($type)) {
                $result = $this->ndsApprovals()->decide($type, $validated['ids'], $validated['action'], $validated['note'] ?? null);
            } elseif ($type === 'so') {
                throw new \InvalidArgumentException('Approval SO hanya tersedia di DB baru.');
            } else {
                $result = $this->approvals->decide($type, $validated['ids'], $validated['action'], $validated['note'] ?? null);
            }
            $message = "{$result['processed']} data diproses.";
            if ($result['skipped'] > 0) {
                $message .= " {$result['skipped']} data dilewati karena tidak berstatus Awaiting Approval"
                    . ($type === 'bom' ? ' atau belum punya item.' : '.');
            }
            return $message;
        });
    }

    public function unlock(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:' . implode(',', MarketingApprovalService::UNLOCK_TYPES)],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        return $this->respond(fn () => (self::nds($validated['type'])
            ? $this->ndsApprovals()->unlock($validated['type'], $validated['ids'])
            : $this->approvals->unlock($validated['type'], $validated['ids'])) . ' data berhasil di-unlock.');
    }

    public function requestUnlock(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:' . implode(',', MarketingApprovalService::UNLOCK_TYPES)],
            'id' => ['required', 'integer'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        return $this->respond(function () use ($validated) {
            self::nds($validated['type'])
                ? $this->ndsApprovals()->requestUnlock($validated['type'], (int) $validated['id'], trim($validated['reason']))
                : $this->approvals->requestUnlock($validated['type'], (int) $validated['id'], trim($validated['reason']));

            return 'Request unlock terkirim dan menunggu di Approval Center.';
        });
    }

    private function documentQuery(string $type)
    {
        $db = DB::connection('mysql_sb');
        if ($type === 'costing') {
            $query = $db->table('act_costing as a');
        } else {
            // Header BOM dibuat bersama Costing; BOM baru masuk antrean approval setelah punya item.
            $query = $db->table('bom_marketing as bm')
                ->join('act_costing as a', 'a.id', '=', 'bm.id_costing')
                ->where(fn ($q) => $q->whereNull('bm.cancel')->orWhere('bm.cancel', 'N'))
                ->whereExists(fn ($q) => $q->select(DB::raw(1))
                    ->from('bom_marketing_detail as d')
                    ->whereColumn('d.id_bom_marketing', 'bm.id')
                    ->where('d.cancel', 'N'));
        }

        return $query->leftJoin('mastersupplier as b', 'a.id_buyer', '=', 'b.Id_Supplier')
            ->leftJoin('masterproduct as p', 'a.id_product', '=', 'p.id');
    }

    private function applyFilters($query, Request $request, string $type, string|Expression|null $dateColumn = null): void
    {
        $dateColumn ??= $type === 'costing' ? 'a.cost_date' : 'bm.created_at';
        if ($request->filled('tgl_awal') && $request->filled('tgl_akhir')) {
            $query->whereDate($dateColumn, '>=', $request->tgl_awal)
                ->whereDate($dateColumn, '<=', $request->tgl_akhir);
        }

        $keyword = trim((string) $request->keyword);
        if ($keyword === '') {
            return;
        }
        $columns = self::SEARCH_COLUMNS[$type];
        $selected = array_values(array_intersect(array_keys($columns), (array) $request->search_by));
        $selected = $selected ?: array_keys($columns);
        $query->where(function ($q) use ($columns, $selected, $keyword) {
            foreach ($selected as $key) {
                $q->orWhere($columns[$key], 'like', "%{$keyword}%");
            }
        });
    }

    private function respond(callable $action)
    {
        try {
            return response()->json(['status' => 200, 'message' => $action()]);
        } catch (\RuntimeException | \InvalidArgumentException $e) {
            return response()->json(['status' => 422, 'message' => $e->getMessage()], 422);
        }
    }
}
