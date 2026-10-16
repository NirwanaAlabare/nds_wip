<?php

namespace App\Http\Controllers;

use App\Services\MarketingApprovalService;
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
    ];

    public function __construct(private MarketingApprovalService $approvals)
    {
    }

    public function index()
    {
        return view('marketing.approval_center', [
            'page'           => 'dashboard-marketing',
            'subPageGroup'   => 'marketing-approval',
            'subPage'        => 'marketing-approval-center',
            'containerFluid' => true,
            'summary'        => [
                'costing' => $this->documentQuery('costing')->whereRaw(MarketingApprovalService::statusSql('costing', 'a') . " = 'awaiting'")->count(),
                'bom' => $this->documentQuery('bom')->whereRaw(MarketingApprovalService::statusSql('bom', 'bm') . " = 'awaiting'")->count(),
                'unlock_costing' => $this->unlockRequestQuery('costing')->count(),
                'unlock_bom' => $this->unlockRequestQuery('bom')->count(),
            ],
        ]);
    }

    /** Tabel Approval Costing / Approval BOM (DataTables) + jumlah per status. */
    public function documents(Request $request, string $type)
    {
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
                DB::raw(MarketingApprovalService::latestNoteSql($type, $type === 'costing' ? 'a.id' : 'bm.id') . ' as approval_note'),
            ]);
        // Urutan default hanya saat user belum klik sort di header tabel.
        if (! $request->filled('order')) {
            $data->orderByDesc($type === 'costing' ? 'a.id' : 'bm.id');
        }

        return DataTables::of($data)
            ->with('status_counts', $counts->select(DB::raw($statusSql . ' as approval_status'), DB::raw('COUNT(*) as total'))
                ->groupBy('approval_status')
                ->pluck('total', 'approval_status'))
            ->addIndexColumn()
            ->make(true);
    }

    /** Tabel Unlock Costing / Unlock BOM: dokumen yang punya request unlock (status W) di unlock_cost / unlock_jo. */
    public function unlockRequests(Request $request, string $type)
    {
        $data = $this->unlockRequestQuery($type);
        if (! $request->filled('order')) {
            $data->orderBy($type === 'costing' ? 'a.id' : 'bm.id');
        }
        $this->applyFilters($data, $request, $type, DB::raw($this->requestColumnSql($type, 'dateinput')));

        return DataTables::of($data)
            ->addIndexColumn()
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
        } else {
            $query = $db->table('bom_marketing as bm')
                ->join('act_costing as a', 'a.id', '=', 'bm.id_costing')
                ->whereRaw(MarketingApprovalService::pendingUnlockSql('bom', 'bm.id'))
                ->select('bm.id', 'bm.id as ref_id', 'a.id as id_costing', 'bm.no_katalog_bom as doc_no')
                ->selectRaw(MarketingApprovalService::statusSql('bom', 'bm') . ' as doc_status');
        }

        return $query->leftJoin('mastersupplier as b', 'a.id_buyer', '=', 'b.Id_Supplier')
            ->leftJoin('masterproduct as p', 'a.id_product', '=', 'p.id')
            ->addSelect('b.Supplier as buyer', 'a.brand', 'a.styleno', 'a.kpno', 'p.product_item')
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
            $result = $this->approvals->decide($type, $validated['ids'], $validated['action'], $validated['note'] ?? null);
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
            'type' => ['required', 'in:' . implode(',', MarketingApprovalService::TYPES)],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        return $this->respond(fn () => $this->approvals->unlock($validated['type'], $validated['ids']) . ' data berhasil di-unlock.');
    }

    public function requestUnlock(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:' . implode(',', MarketingApprovalService::TYPES)],
            'id' => ['required', 'integer'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        return $this->respond(function () use ($validated) {
            $this->approvals->requestUnlock($validated['type'], (int) $validated['id'], trim($validated['reason']));
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
