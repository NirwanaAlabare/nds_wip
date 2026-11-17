<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard Marketing untuk rentang waktu from - to (default 1 tahun terakhir).
 * Costing = act_costing (cost_date), SO = so + so_det aktif (so_date), buyer = act_costing.id_buyer -> mastersupplier.
 * Grafik per hari bila rentang <= 62 hari, selain itu per bulan.
 */
class MarketingDashboardController extends Controller
{
    /**
     * Dashboard Marketing NDS (desain "Dashboard Marketing NDS.html"), masih memakai data contoh yang dibuat di browser.
     * Isinya dimuat lewat iframe (board()) supaya CSS desainnya tidak bentrok dengan AdminLTE / Bootstrap.
     * Dashboard lama (data Signalbit) tetap ada di view marketing.dashboard_marketing + endpoint data().
     */
    public function dashboard_marketing()
    {
        return view('marketing.dashboard_marketing_nds', [
            'page' => 'dashboard-marketing',
            'containerFluid' => true,
        ]);
    }

    /**
     * Isi dashboard (HTML mandiri dengan data contoh). Nama buyer & brand diambil dari master DB nds
     * (window.MASTER_BUYERS); qty, harga, item, dan status order masih data contoh.
     */
    public function board()
    {
        $html = file_get_contents(resource_path('marketing/dashboard_marketing_nds.html'));
        $json = json_encode($this->masterBuyers(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
        $html = preg_replace('/<script>/', '<script>window.MASTER_BUYERS=' . $json . ";</script>\n<script>", $html, 1);

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /** Jumlah buyer teratas yang dipakai data contoh dashboard. */
    private const DASHBOARD_BUYER_LIMIT = 6;

    /**
     * Buyer teratas dari data nyata DB nds (master_partners is_buyer = 1): urut total qty SO aktif, lalu jumlah Costing.
     * Urutan ini dipakai dashboard sebagai bobot data contoh, jadi buyer terbesar juga paling atas di Top 5.
     * Brand dari master_brands.buyer_id. Gagal baca DB = [] (pakai contoh bawaan).
     */
    private function masterBuyers(): array
    {
        try {
            $db = DB::connection('mysql_mkt');
            $active = fn ($q, $col) => $q->where(fn ($w) => $w->whereNull($col)->orWhere($col, '!=', 'Y'));
            $brands = $active($db->table('master_brands as b'), 'b.cancel')->orderBy('b.brand_name')->get(['b.buyer_id', 'b.brand_name'])->groupBy('buyer_id');
            $soQty = $active($active($active($db->table('so_po as p')
                ->join('so_production as sp', 'sp.id', '=', 'p.so_production_id')
                ->join('so_line as l', 'l.id', '=', 'sp.so_line_id')
                ->join('sales_orders as s', 's.id', '=', 'l.sales_order_id')
                ->join('costings as c', 'c.id', '=', 's.costing_id'), 'p.cancel'), 'sp.cancel'), 's.cancel')
                ->groupBy('c.buyer_id')->selectRaw('c.buyer_id, SUM(p.qty) as total')->pluck('total', 'buyer_id');
            $costings = $active($db->table('costings'), 'cancel')->whereNotNull('buyer_id')->groupBy('buyer_id')->selectRaw('buyer_id, COUNT(*) as total')->pluck('total', 'buyer_id');
            $ids = $soQty->keys()->merge($costings->keys())->unique()->values();

            return $active($db->table('master_partners'), 'cancel')->where('is_buyer', 1)->whereIn('id', $ids)
                ->get(['id', 'name', 'code'])
                ->sortBy(fn ($p) => [-(float) ($soQty[$p->id] ?? 0), -(int) ($costings[$p->id] ?? 0), $p->name])
                ->take(self::DASHBOARD_BUYER_LIMIT)
                ->map(function ($p) use ($brands) {
                    $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', explode('|', (string) $p->code)[0]));

                    return [
                        'name' => (string) $p->name,
                        'code' => $code !== '' ? substr($code, 0, 4) : strtoupper(substr(preg_replace('/[^A-Za-z]/', '', (string) $p->name), 0, 3)),
                        'brands' => ($brands[$p->id] ?? collect())->pluck('brand_name')->unique()->values()->all(),
                    ];
                })->values()->all();
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }

    public function data(Request $request)
    {
        $db = DB::connection('mysql_sb');
        $end = $this->parseDate($request->to) ?? Carbon::now();
        $start = $this->parseDate($request->from) ?? $end->copy()->subYear();
        if ($start->greaterThan($end)) {
            [$start, $end] = [$end, $start];
        }
        // so_date / cost_date bertipe DATE, jadi filter memakai tanggalnya saja.
        $range = [$start->toDateString(), $end->toDateString()];

        // Semua grafik selalu per bulan (seperti Grafana), berapa pun panjang rentangnya.
        $daily = false;
        $bucket = $daily ? '%Y-%m-%d' : '%Y-%m';
        $periods = [];
        $cursor = $daily ? $start->copy()->startOfDay() : $start->copy()->startOfMonth();
        while ($cursor <= $end && count($periods) < 400) {
            $periods[] = $cursor->format($daily ? 'Y-m-d' : 'Y-m');
            $daily ? $cursor->addDay() : $cursor->addMonth();
        }

        $soLines = fn () => $db->table('so')
            ->join('so_det as sd', fn ($join) => $join->on('sd.id_so', '=', 'so.id')->where('sd.cancel', 'N'))
            ->where('so.cancel_h', 'N');

        $costingPerPeriod = $db->table('act_costing')
            ->whereBetween('cost_date', $range)
            ->groupBy('m')
            ->pluck(DB::raw('COUNT(*)'), DB::raw("DATE_FORMAT(cost_date, '{$bucket}') as m"));
        $soPerPeriod = $db->table('so')
            ->where('cancel_h', 'N')
            ->whereBetween('so_date', $range)
            ->groupBy('m')
            ->pluck(DB::raw('COUNT(*)'), DB::raw("DATE_FORMAT(so_date, '{$bucket}') as m"));
        $valuePerPeriod = $soLines()
            ->whereBetween('so.so_date', $range)
            ->whereIn('so.curr', ['USD', 'IDR'])
            ->groupBy('m', 'so.curr')
            ->get([DB::raw("DATE_FORMAT(so.so_date, '{$bucket}') as m"), 'so.curr', DB::raw('SUM(sd.qty * sd.price) as total')]);
        $value = fn ($curr, $divider = 1) => array_map(
            fn ($period) => round((float) optional($valuePerPeriod->first(fn ($row) => $row->m === $period && $row->curr === $curr))->total / $divider, 2),
            $periods
        );

        $topBuyers = $soLines()
            ->join('act_costing as ac', 'ac.id', '=', 'so.id_cost')
            ->join('mastersupplier as ms', 'ms.Id_Supplier', '=', 'ac.id_buyer')
            ->whereBetween('so.so_date', $range)
            ->groupBy('ms.Id_Supplier', 'ms.Supplier')
            ->orderByDesc('qty')
            ->limit(10)
            ->get(['ms.Supplier as buyer', DB::raw('SUM(sd.qty) as qty')]);

        $recentSo = $soLines()
            ->leftJoin('act_costing as ac', 'ac.id', '=', 'so.id_cost')
            ->leftJoin('mastersupplier as ms', 'ms.Id_Supplier', '=', 'ac.id_buyer')
            ->whereBetween('so.so_date', $range)
            ->groupBy('so.id', 'so.so_no', 'so.so_date', 'so.curr', 'so.style', 'ac.styleno', 'ac.cost_no', 'ms.Supplier')
            ->orderByDesc('so.so_date')
            ->orderByDesc('so.id')
            ->limit(100)
            ->get([
                'so.so_no', 'so.so_date', 'so.curr', 'ac.cost_no', 'ms.Supplier as buyer',
                DB::raw("COALESCE(NULLIF(so.style, ''), ac.styleno) as style"),
                DB::raw('SUM(sd.qty) as qty'), DB::raw('SUM(sd.qty * sd.price) as value'), DB::raw('MIN(sd.deldate_det) as deldate'),
            ]);

        return response()->json([
            'periods' => $periods,
            'range' => ['from' => $start->toDateTimeString(), 'to' => $end->toDateTimeString(), 'bucket' => $daily ? 'day' : 'month'],
            'kpi' => [
                'total_buyers' => $db->table('act_costing')->whereBetween('cost_date', $range)->whereNotNull('id_buyer')->distinct()->count('id_buyer'),
                'costings' => $costingPerPeriod->sum(),
                'incoming_so' => $soPerPeriod->sum(),
                'due_14_days' => (float) $soLines()->whereBetween('sd.deldate_det', [Carbon::today()->toDateString(), Carbon::today()->addDays(14)->toDateString()])->sum('sd.qty'),
                'total_qty' => (float) $soLines()->whereBetween('so.so_date', $range)->sum('sd.qty'),
            ],
            'costing_vs_so' => [
                'costings' => array_map(fn ($period) => (int) ($costingPerPeriod[$period] ?? 0), $periods),
                'so' => array_map(fn ($period) => (int) ($soPerPeriod[$period] ?? 0), $periods),
            ],
            'order_value' => [
                'usd' => $value('USD'),
                'idr_billion' => $value('IDR', 1000000000),
            ],
            'top_buyers' => $topBuyers,
            'recent_so' => $recentSo,
        ]);
    }

    private function parseDate($value): ?Carbon
    {
        try {
            return $value ? Carbon::parse($value) : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
