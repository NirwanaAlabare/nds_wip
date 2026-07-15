<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class HelpdeskDashboardController extends Controller
{
    public function dashboard_helpdesk(Request $request)
    {
        return view('helpdesk.dashboard_helpdesk', [
            'page' => 'dashboard-helpdesk',
            'containerFluid' => true,
        ]);
    }

    public function summaryBap(Request $request)
    {
        $tahun = $request->tahun ?: date('Y');

        $query = DB::table('bap_form')->whereYear('tgl_form', $tahun);

        $total = (clone $query)->count();
        $selesai = (clone $query)->where('is_cancel', false)->where('is_selesai', true)->count();
        $cancel = (clone $query)->where('is_cancel', true)->count();
        $proses = (clone $query)->where('is_cancel', false)->where('is_selesai', false)->count();

        return response()->json([
            'total' => $total,
            'proses' => $proses,
            'selesai' => $selesai,
            'cancel' => $cancel,
        ]);
    }

    public function recentActivityBap(Request $request)
    {
        $tahun = $request->tahun ?: date('Y');

        $data = DB::table('bap_form')
            ->select('no_form', 'department', 'tgl_form', 'is_selesai', 'is_cancel', 'updated_at')
            ->whereYear('tgl_form', $tahun)
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $status = $row->is_cancel ? 'cancel' : ($row->is_selesai ? 'selesai' : 'proses');

                return [
                    'no_form' => $row->no_form,
                    'department' => $row->department,
                    'status' => $status,
                    'bulan' => $row->tgl_form ? date('M \'y', strtotime($row->tgl_form)) : '-',
                    'updated_at' => $row->updated_at ? date('d M Y H:i', strtotime($row->updated_at)) : '-',
                ];
            });

        return response()->json($data);
    }

    public function chartBapDepartment(Request $request)
    {
        $tahun = $request->tahun ?: date('Y');

        $data = DB::table('bap_form')
            ->selectRaw('department, COUNT(*) as total')
            ->whereYear('tgl_form', $tahun)
            ->where('is_cancel', false)
            ->groupBy('department')
            ->orderByDesc('total')
            ->get();

        return response()->json($data);
    }

    public function chartBapMonthly(Request $request)
    {
        $tahun = $request->tahun ?: date('Y');

        $data = DB::table('bap_form')
            ->selectRaw('MONTH(tgl_form) as bulan, COUNT(*) as total')
            ->whereYear('tgl_form', $tahun)
            ->where('is_cancel', false)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy('bulan');

        $namaBulan = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];

        $result = [];
        foreach ($namaBulan as $bulanKe => $label) {
            $result[] = [
                'bulan' => $label,
                'total' => $data->get($bulanKe)->total ?? 0,
            ];
        }

        return response()->json($result);
    }
}
