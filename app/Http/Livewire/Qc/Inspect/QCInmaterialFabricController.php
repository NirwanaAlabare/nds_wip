<?php

namespace App\Http\Livewire\QC\Inspect;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class QCInmaterialFabricController extends Controller
{
    public function index()
    {
        return view('livewire.qc.inspect.index', ['page' => 'dashboard-warehouse'])
            ->with('pageTitle', 'Data QC Inspection Fabric');
    }

    public function getDatatables(Request $request)
    {
        // Set default date range if not provided
        $tgl_awal = $request->input('tgl_awal', now()->subDays(30)->format('Y-m-d'));
        $tgl_akhir = $request->input('tgl_akhir', now()->format('Y-m-d'));

        // Validate date inputs
        if (!strtotime($tgl_awal) || !strtotime($tgl_akhir)) {
            return response()->json(['error' => 'Invalid date format'], 400);
        }

        $data = DB::connection('mysql_sb')->select("
                SELECT 
                    a.tgl_dok, 
                    a.no_invoice AS no_pl, 
                    a.supplier, 
                    ms.Supplier AS buyer, 
                    ac.styleno AS style, 
                    d.color,
                    d.id_item,
                    c.no_lot,
                    COUNT(DISTINCT c.no_lot) AS jumlah_no_lot,
                    COUNT(DISTINCT c.id) AS jumlah_roll,
                    a.deskripsi AS catatan,
                    a.no_dok -- Keep this for viewDetails function
                FROM 
                    whs_inmaterial_fabric a
                JOIN 
                    whs_inmaterial_fabric_det b ON b.no_dok = a.no_dok
                JOIN 
                    whs_lokasi_inmaterial c ON c.no_dok = a.no_dok
                JOIN 
                    masteritem d ON d.id_item = c.id_item
                JOIN 
                    jo_det jd ON c.id_jo = jd.id_jo
                JOIN 
                    so ON jd.id_so = so.id
                JOIN 
                    act_costing ac ON so.id_cost = ac.id
                JOIN 
                    mastersupplier ms ON ms.Id_Supplier = ac.id_buyer
                WHERE 
                    a.tgl_dok BETWEEN ? AND ?
                    AND a.supplier NOT IN ('Production - Cutting', 'SAMPLE')
                GROUP BY 
                    a.no_invoice, 
                    d.id_item,
                    c.no_lot
                ORDER BY 
                    a.tgl_dok DESC, 
                    c.id DESC
            ", [$tgl_awal, $tgl_akhir]);

        return datatables()->of($data)
            ->addIndexColumn()
            ->make(true);
    }
}