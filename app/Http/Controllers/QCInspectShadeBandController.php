<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use PDF;

class QCInspectShadeBandController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $tgl_skrg = date('Y-m-d');
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-30 days'));
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data_input = DB::connection('mysql_sb')->select("SELECT
c.tgl_dok,
qc.tgl_trans,
DATE_FORMAT(qc.tgl_trans, '%d-%M-%Y') AS tgl_trans_fix,
DATE_FORMAT(c.tgl_dok, '%d-%M-%Y') AS tgl_dok_fix,
c.no_dok,
no_barcode,
no_invoice,
c.supplier,
ms.Supplier buyer,
ac.kpno,
ac.styleno,
mi.color,
mi.id_item,
mi.itemdesc,
no_roll_buyer,
no_lot,
qty_aktual,
satuan,
qc.group,
qc.created_by
from qc_inspect_shade_band qc
left join whs_lokasi_inmaterial a on qc.barcode = a.no_barcode
LEFT JOIN whs_inmaterial_fabric_det b ON a.no_dok = b.no_dok AND a.id_item = b.id_item AND a.id_jo = b.id_jo
LEFT JOIN whs_inmaterial_fabric c ON a.no_dok = c.no_dok
INNER JOIN jo_det jd ON a.id_jo = jd.id_jo
INNER JOIN so ON jd.id_so = so.id
INNER JOIN act_costing ac ON so.id_cost = ac.id
INNER JOIN mastersupplier ms ON ac.id_buyer = ms.Id_Supplier
INNER JOIN masteritem mi ON a.id_item = mi.id_item
where qc.tgl_trans >= '$tgl_awal' and  qc.tgl_trans <= '$tgl_akhir'
order by qc.tgl_trans desc, qc.created_at desc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view(
            'qc_inspect.proses_shade_band',
            [
                'page' => 'dashboard-qc-inspect',
                "subPageGroup" => "qc-inspect-proses",
                "subPage" => "qc-inspect-proses-shade-band",
                'tgl_skrg_min_sebulan' => $tgl_skrg_min_sebulan,
                'tgl_skrg' => $tgl_skrg,
                "containerFluid" => true,
                "user" => $user
            ]
        );
    }

    public function input_shade_band(Request $request)
    {
        $user = Auth::user()->name;

        return view(
            'qc_inspect.proses_input_shade_band',
            [
                'page' => 'dashboard-qc-inspect',
                "subPageGroup" => "qc-inspect-proses",
                "subPage" => "qc-inspect-proses-shade-band",
                "containerFluid" => true,
                "user" => $user
            ]
        );
    }


    public function get_barcode_info_shade_band(Request $request)
    {
        $barcode = $request->input('txtbarcode_scan');

        // Check if barcode exists
        $barcodeData = DB::connection('mysql_sb')->selectOne("
        SELECT
            ms.supplier AS buyer,
            ac.kpno,
            ac.styleno,
            mi.color,
            no_roll_buyer,
            no_lot,
            no_barcode,
            a.id_item,
            mi.itemdesc,
            c.no_invoice,
            c.tgl_dok,
            mi.itemdesc
        FROM whs_lokasi_inmaterial a
        LEFT JOIN whs_inmaterial_fabric_det b ON a.no_dok = b.no_dok AND a.id_item = b.id_item AND a.id_jo = b.id_jo
        LEFT JOIN whs_inmaterial_fabric c ON a.no_dok = c.no_dok
        INNER JOIN jo_det jd ON a.id_jo = jd.id_jo
        INNER JOIN so ON jd.id_so = so.id
        INNER JOIN act_costing ac ON so.id_cost = ac.id
        INNER JOIN mastersupplier ms ON ac.id_buyer = ms.Id_Supplier
        INNER JOIN masteritem mi ON a.id_item = mi.id_item
        WHERE a.status = 'Y' AND no_barcode = ?
        LIMIT 1
    ", [$barcode]);

        // If barcode not found at all
        if (!$barcodeData) {
            return response()->json(['status' => 'not_found']);
        }

        // Check for duplication
        $isDuplicate1 = DB::connection('mysql_sb')->selectOne("
        SELECT barcode
        FROM qc_inspect_shade_band_tmp
        WHERE barcode = ?
        LIMIT 1
    ", [$barcode]);

        if ($isDuplicate1) {
            return response()->json([
                'status' => 'duplicate',
            ]);
        }

        // Check for duplication
        $isDuplicate2 = DB::connection('mysql_sb')->selectOne("
        SELECT barcode
        FROM qc_inspect_shade_band
        WHERE barcode = ?
        LIMIT 1
    ", [$barcode]);

        if ($isDuplicate2) {
            return response()->json([
                'status' => 'duplicate',
            ]);
        }

        // If barcode is valid and not duplicate
        return response()->json([
            'status' => 'success',
            'data' => [
                'buyer'   => $barcodeData->buyer,
                'kpno'    => $barcodeData->kpno,
                'styleno' => $barcodeData->styleno,
                'color' => $barcodeData->color,
                'no_roll_buyer' => $barcodeData->no_roll_buyer,
                'no_lot' => $barcodeData->no_lot,
                'no_barcode' => $barcodeData->no_barcode,
                'id_item' => $barcodeData->id_item,
                'itemdesc' => $barcodeData->itemdesc,
                'tgl_dok' => $barcodeData->tgl_dok,
                'no_invoice' => $barcodeData->no_invoice,
                'itemdesc' => $barcodeData->itemdesc,
                // You can add more fields here if needed
            ]
        ]);
    }


    public function insert_tmp_shade_band(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $currentDate = $timestamp->format('Y-m-d');
        $barcode = $request->barcode;

        DB::connection('mysql_sb')->table('qc_inspect_shade_band_tmp')->insert([
            'barcode'               => $barcode,
            'tgl_trans'              => $currentDate,
            'created_by'            => $user,
            'created_at'            => $timestamp,
            'updated_at'            => $timestamp,
        ]);

        // Return detailed response
        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.',
            'data' => [
                'barcode' => $barcode
            ]
        ]);
    }

    public function get_list_shade_band_tmp(Request $request)
    {
        $user = Auth::user()->name;

        $data_input = DB::connection('mysql_sb')->select("select
c.tgl_dok,
DATE_FORMAT(c.tgl_dok, '%d-%M-%Y') AS tgl_dok_fix,
c.no_dok,
no_barcode,
no_invoice,
c.supplier,
ms.Supplier buyer,
ac.kpno,
ac.styleno,
mi.color,
mi.id_item,
mi.itemdesc,
no_roll_buyer,
no_lot,
qty_aktual,
satuan
from qc_inspect_shade_band_tmp tmp
left join whs_lokasi_inmaterial a on tmp.barcode = a.no_barcode
LEFT JOIN whs_inmaterial_fabric_det b ON a.no_dok = b.no_dok AND a.id_item = b.id_item AND a.id_jo = b.id_jo
LEFT JOIN whs_inmaterial_fabric c ON a.no_dok = c.no_dok
INNER JOIN jo_det jd ON a.id_jo = jd.id_jo
INNER JOIN so ON jd.id_so = so.id
INNER JOIN act_costing ac ON so.id_cost = ac.id
INNER JOIN mastersupplier ms ON ac.id_buyer = ms.Id_Supplier
INNER JOIN masteritem mi ON a.id_item = mi.id_item
where tmp.created_by = '$user'
order by tmp.id asc
            ");
        return DataTables::of($data_input)->toJson();
    }

    public function delete_barcode_tmp_shade_band(Request $request)
    {
        $user = Auth::user()->name;
        $barcode = $request->barcode;

        $deleted =  DB::connection('mysql_sb')->delete(
            "DELETE FROM qc_inspect_shade_band_tmp where barcode = '$barcode' and created_by = '$user'"
        );

        return response()->json([
            'status' => $deleted ? 'success' : 'not_found',
            'message' => $deleted ? 'Data berhasil dihapus.' : 'Data tidak ditemukan atau tidak dapat dihapus.',
            'data' => [
                'barcode' => $barcode
            ]
        ]);
    }

    public function save_proses_shade_band(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $group = $request->group;
        $currentDate = $timestamp->format('Y-m-d');

        DB::connection('mysql_sb')->insert(
            "INSERT INTO qc_inspect_shade_band (barcode, tgl_trans,qc_inspect_shade_band.group, created_by,created_at,updated_at)
            SELECT barcode,'$currentDate','$group',created_by,created_at,'$timestamp'
            from qc_inspect_shade_band_tmp WHERE created_by = '$user'"
        );
        // Delete from temporary table
        DB::connection('mysql_sb')->delete(
            "DELETE FROM qc_inspect_shade_band_tmp WHERE created_by = ?",
            [$user]
        );
        // Return detailed response
        return response()->json([
            'status' => 'success',
            'message' => 'Data Berhasil disimpan.',
            'data' => [
                'group' => $group
            ]
        ]);
    }


    public function print_sticker_group_shade_band(Request $request)
    {
        // Fetch header data using raw SQL query

        $ids = $request->input('ids');

        // Wrap each ID in quotes
        $quotedIds = array_map(function ($id) {
            return "'$id'";
        }, $ids);

        // Implode into a string for SQL
        $inClause = implode(',', $quotedIds);

        // Now use the string in the raw SQL query
        $sql = "SELECT
            a.group,
            a.barcode
        FROM qc_inspect_shade_band a
        WHERE barcode IN ($inClause)"; // Removed the extra closing parenthesis

        $data_header = DB::connection('mysql_sb')->select($sql);

        // Generate PDF from the view
        $pdf = PDF::loadView('qc_inspect.pdf_print_group_shade_band', [
            'data_header' => $data_header,
        ])->setPaper([0, 0, 113.39, 85.04]);

        // Set filename and return download
        $fileName = 'pdf.pdf';
        return $pdf->download(str_replace("/", "_", $fileName));
    }
}
