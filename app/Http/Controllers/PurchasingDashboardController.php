<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class PurchasingDashboardController extends Controller
{
    public function dashboard_purchasing(Request $request)
    {
        $mysql_sb = DB::connection('mysql_sb');

        $tahun = $request->tahun ?? date('Y');

        $count_draft    = $mysql_sb->table('po_header')->whereYear('podate', $tahun)->where('app', 'W')->count();
        $count_approved = $mysql_sb->table('po_header')->whereYear('podate', $tahun)->where('app', 'A')->count();
        $count_canceled = $mysql_sb->table('po_header')->whereYear('podate', $tahun)->where('app', 'C')->count();
        $recent_pos = $mysql_sb->table('po_header as h')
            ->leftJoin('mastersupplier as s', 'h.id_supplier', '=', 's.Id_Supplier')
            ->select('h.pono', 'h.podate', 's.Supplier as nama_supplier', 'h.app', 'h.jenis')
            ->whereYear('h.podate', $tahun)
            ->orderBy('h.podate', 'desc')->orderBy('h.id', 'desc')
            ->limit(5)
            ->get();

        $monthly_spend_data = $mysql_sb->table('po_item as pi')
            ->join('po_header as h', 'pi.id_po', '=', 'h.id')
            ->whereYear('h.podate', $tahun)
            ->where('h.app', '!=', 'C')
            ->selectRaw('MONTH(h.podate) as bulan, SUM(pi.qty * pi.price) as total')
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $chart_months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
        $chart_spend  = [];

        for ($i = 1; $i <= 12; $i++) {
            $total = $monthly_spend_data->get($i, 0);
            $chart_spend[] = round($total / 1000000, 2);
        }

        $chart_jenis_label = ['Material', 'Manufacturing'];
        $chart_jenis_data  = [
            $mysql_sb->table('po_header')->whereYear('podate', $tahun)->where('jenis', 'P')->count(),
            $mysql_sb->table('po_header')->whereYear('podate', $tahun)->where('jenis', 'M')->count()
        ];

        return view('purchasing.dashboard_purchasing', [
            'page'              => 'dashboard-purchasing',
            'subPageGroup'      => 'purchasing',
            'subPage'           => 'dashboard',
            'count_draft'       => $count_draft,
            'count_approved'    => $count_approved,
            'count_canceled'    => $count_canceled,
            'recent_pos'        => $recent_pos,
            'chart_months'      => $chart_months,
            'chart_spend'       => $chart_spend,
            'chart_jenis_label' => $chart_jenis_label,
            'chart_jenis_data'  => $chart_jenis_data,
            'containerFluid'    => true
        ]);
    }

    public function get_list_po_status(Request $request)
    {
        $status = $request->status;
        $tahun  = $request->tahun ?? date('Y');

        $query = DB::connection('mysql_sb')->table('po_header')
            ->leftJoin('mastersupplier as s', 'po_header.id_supplier', '=', 's.Id_Supplier')
            ->select('po_header.id', 'po_header.podate', 'po_header.pono', 's.Supplier as nama_supplier', 'po_header.jenis', 'po_header.app')
            ->whereYear('po_header.podate', $tahun);

        if ($status == 'S') {
            $query->where('po_header.app', 'A')->whereNotNull('po_header.etd');
        } else {
            $query->where('po_header.app', $status);
        }

        return datatables()->of($query)
            ->addColumn('action', function ($row) {
                return '<button type="button" class="btn btn-sm btn-primary btn-view-detail" data-id="'.$row->id.'">
                            <i class="fas fa-eye"></i> View
                        </button>';
            })
            ->make(true);
    }
}
