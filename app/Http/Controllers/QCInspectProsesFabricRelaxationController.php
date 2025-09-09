<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use PDF;

class QCInspectProsesFabricRelaxationController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $tgl_skrg = date('Y-m-d');
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-30 days'));
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data_input = DB::connection('mysql_sb')->select("select
rl.id,
rl.tgl_form,
DATE_FORMAT(rl.tgl_form, '%d-%M-%Y') AS tgl_form_fix,
rl.operator,
rl.no_mesin,
rl.no_form,
rl.enroll_id,
rl.operator,
rl.nik,
DATE_FORMAT(c.tgl_dok, '%d-%m-%Y')tgl_dok,
c.no_invoice,
ms.Supplier buyer,
ac.kpno,
ac.styleno,
mi.color,
a.no_lot,
mi.id_item,
mi.itemdesc,
barcode,
no_roll_buyer,
rl.durasi_relax,
DATE_FORMAT(start_form, '%d-%m-%Y %H:%i:%s') AS start_form_fix,
DATE_FORMAT(start_form, '%d-%m-%Y')start_date,
time(start_form) start_time,
DATE_FORMAT(finish_form, '%d-%m-%Y %H:%i:%s') AS finish_form_fix,
DATE_FORMAT(finish_form, '%d-%m-%Y')finish_date,
time(finish_form) finish_time,
TIMESTAMPDIFF(DAY, start_form, finish_form) AS days_diff,
case
		when finish_form is null then 'Ongoing'
		else 'Done'
		end as status_fix
from qc_inspect_fabric_relaxation rl
inner join whs_lokasi_inmaterial a on rl.barcode = a.no_barcode
LEFT JOIN whs_inmaterial_fabric_det b ON a.no_dok = b.no_dok AND a.id_item = b.id_item AND a.id_jo = b.id_jo
LEFT JOIN whs_inmaterial_fabric c ON a.no_dok = c.no_dok
INNER JOIN jo_det jd ON a.id_jo = jd.id_jo
INNER JOIN so ON jd.id_so = so.id
INNER JOIN act_costing ac ON so.id_cost = ac.id
INNER JOIN mastersupplier ms ON ac.id_buyer = ms.Id_Supplier
INNER JOIN masteritem mi ON a.id_item = mi.id_item
where rl.tgl_form >= '$tgl_awal' and rl.tgl_form <= '$tgl_akhir'
order by rl.tgl_form desc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view(
            'qc_inspect.proses_fabric_relaxation',
            [
                'page' => 'dashboard-qc-inspect',
                "subPageGroup" => "qc-inspect-proses",
                "subPage" => "qc-inspect-proses-fabric-relaxation",
                'tgl_skrg_min_sebulan' => $tgl_skrg_min_sebulan,
                'tgl_skrg' => $tgl_skrg,
                "containerFluid" => true,
                "user" => $user
            ]
        );
    }


    public function input_fabric_relaxation(Request $request)
    {
        $user = Auth::user()->name;

        return view(
            'qc_inspect.proses_input_fabric_relaxation',
            [
                'page' => 'dashboard-qc-inspect',
                "subPageGroup" => "qc-inspect-proses",
                "subPage" => "qc-inspect-proses-fabric-relaxation",
                "containerFluid" => true,
                "user" => $user
            ]
        );
    }

    public function get_barcode_info_fabric_relaxation(Request $request)
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
        $isDuplicate = DB::connection('mysql_sb')->selectOne("
        SELECT barcode
        FROM qc_inspect_fabric_relaxation
        WHERE barcode = ?
        LIMIT 1
    ", [$barcode]);

        if ($isDuplicate) {
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


    public function save_form_fabric_relaxation(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $barcode = $request->barcode;
        $enroll_id = $request->enroll_id;
        $durasi_relax = $request->durasi_relax;

        $cek_operator = DB::connection('mysql_hris')->select("
        SELECT employee_name, enroll_id, nik
        FROM employee_atribut
        WHERE enroll_id = ?", [$enroll_id]);

        $nm_operator = $cek_operator[0]->employee_name;
        $nik = $cek_operator[0]->nik;

        // Prepare date values
        $datePrefix = $timestamp->format('dmy'); // DDMMYY format
        $month = $timestamp->format('m');
        $year = $timestamp->format('Y');
        $currentDate = $timestamp->format('Y-m-d');

        $get_last_number = DB::connection('mysql_sb')->select("
        SELECT MAX(CAST(SUBSTRING_INDEX(no_form, '/', -1) AS UNSIGNED)) AS last_number
        FROM qc_inspect_fabric_relaxation
        WHERE MONTH(tgl_form) = ? AND YEAR(tgl_form) = ?
    ", [$month, $year]);

        $last_number = $get_last_number[0]->last_number ?? 0;
        $formCounter = $last_number + 1;

        $no_form = 'FRL/' . $datePrefix . '/' . $formCounter++;

        $id = DB::connection('mysql_sb')->table('qc_inspect_fabric_relaxation')->insertGetId([
            'no_form'               => $no_form,
            'tgl_form'              => $currentDate,
            'no_mesin'              => $user,
            'start_form'            => $timestamp,
            'enroll_id'             => $enroll_id,
            'operator'              => $nm_operator,
            'nik'                   => $nik,
            'barcode'               => $barcode,
            'durasi_relax'          => $durasi_relax,
            'created_by'            => $user,
            'created_at'            => $timestamp,
            'updated_at'            => $timestamp,
        ]);

        // Return detailed response
        return response()->json([
            'status' => 'success',
            'message' => 'Form berhasil disimpan.',
            'data' => [
                'id'    => $id,
                'no_form' => $no_form,
                'barcode' => $barcode,
                'durasi_relax' => $durasi_relax
            ]
        ]);
    }

    public function input_fabric_relaxation_det($id)
    {
        $user = Auth::user()->name;

        $get_header = DB::connection('mysql_sb')->select("select
rl.id,
rl.tgl_form,
DATE_FORMAT(rl.tgl_form, '%d-%M-%Y') AS tgl_form_fix,
rl.operator,
rl.no_mesin,
rl.no_form,
rl.enroll_id,
rl.operator,
rl.nik,
DATE_FORMAT(c.tgl_dok, '%d-%m-%Y')tgl_dok,
c.no_invoice,
ms.Supplier buyer,
ac.kpno,
ac.styleno,
mi.color,
a.no_lot,
mi.id_item,
mi.itemdesc,
barcode,
no_roll_buyer,
rl.durasi_relax,
DATE_FORMAT(start_form, '%d-%m-%Y %H:%i:%s') AS start_form_fix,
DATE_FORMAT(start_form, '%d-%m-%Y')start_date,
time(start_form) start_time,
DATE_FORMAT(finish_form, '%d-%m-%Y %H:%i:%s') AS finish_form_fix,
DATE_FORMAT(finish_form, '%d-%m-%Y')finish_date,
time(finish_form) finish_time,
TIMESTAMPDIFF(DAY, start_form, finish_form) AS days_diff,
case
		when finish_form is null then 'Ongoing'
		else 'Done'
		end as status_fix
from qc_inspect_fabric_relaxation rl
inner join whs_lokasi_inmaterial a on rl.barcode = a.no_barcode
LEFT JOIN whs_inmaterial_fabric_det b ON a.no_dok = b.no_dok AND a.id_item = b.id_item AND a.id_jo = b.id_jo
LEFT JOIN whs_inmaterial_fabric c ON a.no_dok = c.no_dok
INNER JOIN jo_det jd ON a.id_jo = jd.id_jo
INNER JOIN so ON jd.id_so = so.id
INNER JOIN act_costing ac ON so.id_cost = ac.id
INNER JOIN mastersupplier ms ON ac.id_buyer = ms.Id_Supplier
INNER JOIN masteritem mi ON a.id_item = mi.id_item
where rl.id = ?", [$id]);


        $start_form                 = $get_header[0]->start_form_fix;
        $finish_form                = $get_header[0]->finish_form_fix;
        $enroll_id                  = $get_header[0]->enroll_id;
        $nik                        = $get_header[0]->nik;
        $operator                   = $get_header[0]->operator;
        $no_mesin                   = $get_header[0]->no_mesin;
        $no_form                    = $get_header[0]->no_form;
        $tgl_dok                    = $get_header[0]->tgl_dok;
        $no_invoice                 = $get_header[0]->no_invoice;
        $buyer                      = $get_header[0]->buyer;
        $kpno                       = $get_header[0]->kpno;
        $styleno                    = $get_header[0]->styleno;
        $color                      = $get_header[0]->color;
        $no_roll_buyer              = $get_header[0]->no_roll_buyer;
        $no_lot                     = $get_header[0]->no_lot;
        $barcode                    = $get_header[0]->barcode;
        $color                      = $get_header[0]->color;
        $itemdesc                   = $get_header[0]->itemdesc;
        $durasi_relax               = $get_header[0]->durasi_relax;

        return view(
            'qc_inspect.proses_input_fabric_relaxation_det',
            [
                'page' => 'dashboard-qc-inspect',
                "subPageGroup" => "qc-inspect-proses",
                "subPage" => "qc-inspect-proses-fabric-relaxation",
                "containerFluid" => true,
                "user" => $user,
                "id"                => $id,
                "start_form"        => $start_form,
                "finish_form"       => $finish_form,
                "enroll_id"         => $enroll_id,
                "nik"               => $nik,
                "operator"          => $operator,
                "no_mesin"          => $no_mesin,
                "no_form"           => $no_form,
                "tgl_dok"           => $tgl_dok,
                "no_invoice"        => $no_invoice,
                "buyer"             => $buyer,
                "kpno"              => $kpno,
                "styleno"           => $styleno,
                "color"             => $color,
                "no_roll_buyer"     => $no_roll_buyer,
                "no_lot"            => $no_lot,
                "barcode"           => $barcode,
                "itemdesc"          => $itemdesc,
                "durasi_relax"      => $durasi_relax
            ]
        );
    }

    public function finish_form_fabric_relaxation(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $id = $request->id;

        $update = DB::connection('mysql_sb')->update("UPDATE qc_inspect_fabric_relaxation set
        finish_no_mesin = '$user',
        finish_form = '$timestamp'
        WHERE id = '$id'
    ");

        // Return detailed response
        return response()->json([
            'status' => 'success',
            'message' => 'Form berhasil disimpan.',
            'data' => [
                'id'    => $id
            ]
        ]);
    }

    public function print_sticker_fabric_relaxation(Request $request)
    {
        // Fetch header data using raw SQL query

        $ids = $request->input('id');

        // Make sure $ids is an array and numeric (optional validation)
        if (!is_array($ids)) {
            return response()->json(['error' => 'Invalid ID format'], 400);
        }

        // Build placeholders for each ID — e.g. ?, ?, ?
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Raw SQL
        $sql = "SELECT
        barcode,
        DATE_FORMAT(start_form, '%d-%m-%Y')start_date,
        time(start_form) start_time,
        DATE_FORMAT(finish_form, '%d-%m-%Y')finish_date,
        time(finish_form) finish_time
        FROM qc_inspect_fabric_relaxation WHERE id IN ($placeholders)";

        // Execute query with bindings
        $data_header = DB::connection('mysql_sb')->select($sql, $ids);

        // Generate PDF from the view
        $pdf = PDF::loadView('qc_inspect.pdf_qc_inspect_print_fabric_relaxation', [
            'data_header' => $data_header,
        ])->setPaper([0, 0, 113.39, 566.93]);

        // Set filename and return download
        $fileName = 'pdf.pdf';
        return $pdf->download(str_replace("/", "_", $fileName));
    }
}
