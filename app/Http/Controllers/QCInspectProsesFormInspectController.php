<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class QCInspectProsesFormInspectController extends Controller
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
qc.id,
qc.tgl_form,
DATE_FORMAT(qc.tgl_form, '%d-%M-%Y') AS tgl_form_fix,
qc.no_mesin,
qc.no_form,
qc.id_item,
a.itemdesc,
a.supplier,
qc.no_invoice,
a.buyer,
a.kpno,
a.styleno,
a.color,
qc.group_inspect,
qc.no_lot,
a.type_pch,
qc.proses,
qc.barcode,
b.no_roll,
CONCAT(
    ROUND(IFNULL(d.act_point, 0)),
    '/',
    IFNULL(c.individu, 0)
) AS point_max_point,
CASE
    WHEN qc.result = 'REJECT' and pass_with_condition = 'N' THEN 'REJECT'
    WHEN qc.result = 'REJECT' and pass_with_condition = 'Y' THEN 'PASS WITH CONDITION'
    ELSE
        qc.result
    END as result,
qc.status_proses_form
from signalbit_erp.qc_inspect_form  qc
inner join
(
select a.no_invoice, c.id_item, mi.itemdesc,c.id_jo, mi.color,a.supplier, ms.supplier buyer, ac.kpno, ac.styleno, a.type_pch
from signalbit_erp.whs_inmaterial_fabric a
inner join signalbit_erp.whs_inmaterial_fabric_det b on a.no_dok = b.no_dok
inner join signalbit_erp.whs_lokasi_inmaterial c on a.no_dok = c.no_dok and b.id_item = c.id_item and b.id_jo = c.id_jo
inner join signalbit_erp.masteritem mi on b.id_item = mi.id_item
inner join signalbit_erp.jo_det jd on b.id_jo = jd.id_jo
inner join signalbit_erp.so so on jd.id_so = so.id
inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
group by c.id_item, c.id_jo, a.no_invoice
) a on qc.id_item = a.id_item and qc.id_jo = a.id_jo and qc.no_invoice = a.no_invoice
left join signalbit_erp.whs_lokasi_inmaterial b on qc.barcode = b.no_barcode
left join signalbit_erp.qc_inspect_master_group_inspect c on qc.group_inspect = c.id
left join
(
SELECT
a.no_form,
ROUND(
    (
        (
            SUM(up_to_3) * 1 +
            SUM(`3_6`) * 2 +
            SUM(`6_9`) * 3 +
            SUM(over_9) * 4
        ) * 36 * 100
    ) / (
        AVG(a.cuttable_width_act) *
        AVG(
            CASE
                WHEN b.unit_act_length = 'meter' THEN b.act_length / 0.9144
                ELSE b.act_length
            END
        )
    )
) AS act_point
FROM qc_inspect_form_det a
INNER JOIN qc_inspect_form b ON a.no_form = b.no_form
INNER JOIN qc_inspect_master_group_inspect c ON b.group_inspect = c.id
where tgl_form >= '$tgl_awal' and tgl_form <= '$tgl_akhir'
group by no_form
)
d on qc.no_form = d.no_form
where qc.tgl_form >= '$tgl_awal' and qc.tgl_form <= '$tgl_akhir'
order by no_form asc, tgl_form desc, color asc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view(
            'qc_inspect.proses_form_inspect',
            [
                'page' => 'dashboard-qc-inspect',
                "subPageGroup" => "qc-inspect-proses",
                "subPage" => "qc-inspect-proses-form-inspect",
                'tgl_skrg_min_sebulan' => $tgl_skrg_min_sebulan,
                'tgl_skrg' => $tgl_skrg,
                "containerFluid" => true,
                "user" => $user
            ]
        );
    }

    public function qc_inspect_proses_form_inspect_det($id)
    {
        $user = Auth::user()->name;
        $get_header = DB::connection('mysql_sb')->select("SELECT
qc.id,
qc.tgl_form,
DATE_FORMAT(qc.tgl_form, '%d-%M-%Y') AS tgl_form_fix,
qc.no_mesin,
qc.no_form,
qc.no_invoice,
a.buyer,
a.kpno,
a.styleno,
a.color,
qc.group_inspect,
qc.cek_inspect,
qc.id_item,
qc.id_jo,
qc.no_lot,
a.type_pch,
qc.proses,
qc.start_form,
qc.status_proses_form,
qc.enroll_id,
qc.operator,
qc.nik,
qc.barcode,
b.no_roll,
mi.itemdesc,
a.supplier,
DATE_FORMAT(start_form, '%d-%m-%Y %H:%i:%s') AS start_form_fix,
DATE_FORMAT(finish_form, '%d-%m-%Y %H:%i:%s') AS finish_form_fix,
qc.weight,
qc.lbs,
qc.width,
qc.unit_width,
qc.act_weight,
qc.act_unit_weight,
qc.gramage,
qc.bintex_length,
qc.unit_bintex,
qc.act_length,
qc.unit_act_length
from signalbit_erp.qc_inspect_form  qc
inner join
(
select a.no_invoice, c.id_item, c.id_jo, mi.color,a.supplier, ms.supplier buyer, ac.kpno, ac.styleno, a.type_pch
from signalbit_erp.whs_inmaterial_fabric a
inner join signalbit_erp.whs_inmaterial_fabric_det b on a.no_dok = b.no_dok
inner join signalbit_erp.whs_lokasi_inmaterial c on a.no_dok = c.no_dok and b.id_item = c.id_item and b.id_jo = c.id_jo
inner join signalbit_erp.masteritem mi on b.id_item = mi.id_item
inner join signalbit_erp.jo_det jd on b.id_jo = jd.id_jo
inner join signalbit_erp.so so on jd.id_so = so.id
inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
group by c.id_item, c.id_jo, a.no_invoice
) a on qc.id_item = a.id_item and qc.id_jo = a.id_jo and qc.no_invoice = a.no_invoice
left join signalbit_erp.whs_lokasi_inmaterial b on qc.barcode = b.no_barcode
left join signalbit_erp.whs_inmaterial_fabric c on b.no_dok = c.no_dok
inner join signalbit_erp.masteritem mi on qc.id_item = mi.id_item
where qc.id = ?
order by no_form desc, tgl_form desc, color asc", [$id]);

        $no_form                = $get_header[0]->no_form;
        $no_invoice             = $get_header[0]->no_invoice;
        $id                     = $get_header[0]->id;
        $status_proses_form     = $get_header[0]->status_proses_form;
        $start_form_fix         = $get_header[0]->start_form_fix;
        $finish_form_fix         = $get_header[0]->finish_form_fix;

        $buyer                  = $get_header[0]->buyer;
        $ws                     = $get_header[0]->kpno;
        $style                  = $get_header[0]->styleno;
        $color                  = $get_header[0]->color;
        $no_lot                 = $get_header[0]->no_lot;
        $group_inspect          = $get_header[0]->group_inspect;
        $cek_inspect            = $get_header[0]->cek_inspect;
        $notes                  = $get_header[0]->type_pch;
        $id_item                = $get_header[0]->id_item;
        $id_jo                  = $get_header[0]->id_jo;

        $operator               = $get_header[0]->operator   ?? '';
        $enroll_id              = $get_header[0]->enroll_id  ?? '';
        $nik                    = $get_header[0]->nik        ?? '';

        $barcode                = $get_header[0]->barcode    ?? '';
        $no_roll                = $get_header[0]->no_roll    ?? '';
        $itemdesc               = $get_header[0]->itemdesc   ?? '';
        $supplier               = $get_header[0]->supplier   ?? '';

        $weight                 = $get_header[0]->weight;
        $lbs                    = $get_header[0]->lbs;
        $width                  = $get_header[0]->width;
        $unit_width             = $get_header[0]->unit_width   ?? '';
        $act_weight             = $get_header[0]->act_weight;
        $act_unit_weight        = $get_header[0]->act_unit_weight   ?? '';
        $gramage                = $get_header[0]->gramage;

        $bintex_length          = $get_header[0]->bintex_length;
        $unit_bintex            = $get_header[0]->unit_bintex   ?? '';
        $act_length             = $get_header[0]->act_length;
        $unit_act_length        = $get_header[0]->unit_act_length   ?? '';

        $data_length = DB::connection('mysql_sb')->select("SELECT
        id isi,
        concat(a.from, ' - ', a.to) tampil
        from qc_inspect_master_lenght a");

        $data_defect = DB::connection('mysql_sb')->select("select
        a.id isi,
        critical_defect tampil
        from qc_inspect_master_defect a");


        return view(
            'qc_inspect.proses_det_form_inspect',
            [
                'page' => 'dashboard-qc-inspect',
                "subPageGroup" => "qc-inspect-proses",
                "subPage" => "qc-inspect-proses-form-inspect",
                "containerFluid" => true,
                "user" => $user,
                "no_form" => $no_form,
                "no_invoice" => $no_invoice,
                "id" => $id,
                "status_proses_form" => $status_proses_form,
                "start_form_fix" => $start_form_fix,
                "finish_form_fix" => $finish_form_fix,
                "buyer" => $buyer,
                "ws" => $ws,
                "style" => $style,
                "color" => $color,
                "no_lot" => $no_lot,
                "group_inspect" => $group_inspect,
                "cek_inspect" => $cek_inspect,
                "notes" => $notes,
                "id_item" => $id_item,
                "id_jo" => $id_jo,
                "operator" => $operator,
                "enroll_id" => $enroll_id,
                "nik" => $nik,
                "barcode" => $barcode,
                "no_roll" => $no_roll,
                "itemdesc" => $itemdesc,
                "supplier" => $supplier,
                "weight" => $weight,
                "lbs" => $lbs,
                "width" => $width,
                "unit_width" => $unit_width,
                "act_weight" => $act_weight,
                "act_unit_weight" => $act_unit_weight,
                "gramage" => $gramage,
                "data_length" => $data_length,
                "data_defect" => $data_defect,
                "bintex_length" => $bintex_length,
                "unit_bintex" => $unit_bintex,
                "act_length" => $act_length,
                "unit_act_length" => $unit_act_length,
            ]
        );
    }

    public function get_operator_info(Request $request)
    {
        $txtqr_operator = $request->txtqr_operator;

        $cek_operator = DB::connection('mysql_hris')->select("
        SELECT employee_name, enroll_id, nik
        FROM employee_atribut
        WHERE enroll_id = ?
    ", [$txtqr_operator]);

        if (empty($cek_operator)) {
            return response()->json([
                'status' => 'not_found'
            ]);
        }

        $operator = $cek_operator[0];

        return response()->json([
            'status' => 'success',
            'data' => [
                'operator'   => $operator->employee_name,
                'nik'        => $operator->nik,
                'enroll_id'  => $operator->enroll_id,
            ]
        ]);
    }

    public function save_start_form_inspect(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $txtqr_operator = $request->txtqr_operator;
        $id = $request->id;

        $cek_operator = DB::connection('mysql_hris')->select("
        SELECT employee_name, enroll_id, nik
        FROM employee_atribut
        WHERE enroll_id = ?", [$txtqr_operator]);

        $nm_operator = $cek_operator[0]->employee_name;
        $nik = $cek_operator[0]->nik;
        $enroll_id = $cek_operator[0]->enroll_id;

        $update = DB::connection('mysql_sb')->update("UPDATE qc_inspect_form set
        no_mesin = '$user',
        enroll_id = '$enroll_id',
        nik = '$nik',
        operator = '$nm_operator',
        start_form = '$timestamp',
        status_proses_form = 'new'
        WHERE id = '$id'
    ");

        // Return detailed response
        return response()->json([
            'status' => 'success',
            'message' => 'Form Berhasil di Buat',
            'data' => [
                'enroll_id' => $enroll_id,
                'nik' => $nik,
                'operator' => $nm_operator,
            ]
        ]);
    }

    public function get_barcode_info(Request $request)
    {
        $barcode     = $request->barcode;
        $id_item     = $request->id_item;
        $id_jo       = $request->id_jo;
        $no_lot      = $request->no_lot;
        $no_invoice  = $request->no_invoice;
        $color       = $request->color;

        // Check for duplicate barcode
        $cek_duplicate = DB::connection('mysql_sb')->select("
        SELECT barcode
        FROM qc_inspect_form
        WHERE barcode = ?
    ", [$barcode]);

        if (!empty($cek_duplicate)) {
            return response()->json([
                'status' => 'duplicate',
                'message' => 'Barcode sudah dipakai'
            ]);
        }

        // Fetch barcode information
        $cek_barcode = DB::connection('mysql_sb')->select("
        SELECT
            no_barcode,
            supplier,
            no_roll,
            mi.color,
            mi.itemdesc
        FROM whs_lokasi_inmaterial a
        INNER JOIN whs_inmaterial_fabric_det b ON a.id_item = b.id_item AND a.id_jo = b.id_jo
        INNER JOIN whs_inmaterial_fabric c ON b.no_dok = c.no_dok
        INNER JOIN masteritem mi ON a.id_item = mi.id_item
        WHERE a.id_item = ? AND a.id_jo = ? AND no_invoice = ? AND no_lot = ? AND color = ? AND no_barcode = ?
    ", [$id_item, $id_jo, $no_invoice, $no_lot, $color, $barcode]);

        if (empty($cek_barcode)) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Barcode tidak ditemukan'
            ]);
        }

        $row = $cek_barcode[0];

        return response()->json([
            'status' => 'success',
            'data' => [
                'barcode'   => $row->no_barcode,
                'supplier'  => $row->supplier,
                'no_roll'   => $row->no_roll,
                'color'     => $row->color,
                'itemdesc'  => $row->itemdesc,
            ]
        ]);
    }


    public function save_fabric_form_inspect(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $barcode = $request->barcode;
        $id = $request->id;
        $update = DB::connection('mysql_sb')->update("UPDATE qc_inspect_form set
        barcode = '$barcode'
        WHERE id = '$id'
    ");

        // Return detailed response
        return response()->json([
            'status' => 'success',
            'message' => 'Form Inspect Berhasil di Update dengan Barcode',
        ]);
    }


    public function save_detail_fabric(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $id = $request->id;
        $weight     = $request->weight     ?? 0;
        $width      = $request->width      ?? 0;
        $unitWidth  = $request->unitWidth;
        $lbs        = $request->lbs        ?? 0;
        $act_weight = $request->act_weight ?? 0;
        $gramage    = $request->gramage    ?? 0;

        $update = DB::connection('mysql_sb')->update("UPDATE qc_inspect_form set
        weight = '$weight',
        unit_weight = 'KG',
        lbs = '$lbs',
        width = '$width',
        unit_width = '$unitWidth',
        act_weight = '$act_weight',
        act_unit_weight = 'KG',
        gramage ='$gramage',
        status_proses_form = 'ongoing'
        WHERE id = '$id'
    ");

        // Return detailed response
        return response()->json([
            'status' => 'success',
            'message' => 'Form Inspect Berhasil di Update dengan Detail Fabric',
        ]);
    }

    public function save_visual_inspection(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $id = $request->id;
        $txtno_form = $request->txtno_form;
        $cbo_length = $request->cbo_length ?? 0;
        $cbo_defect = $request->cbo_defect ?? 0;
        $txtup_to_3 = $request->txtup_to_3 ?: 0;
        $txt3_6 = $request->txt3_6 ?: 0;
        $txt6_9 = $request->txt6_9 ?: 0;
        $txtovr_9 = $request->txtovr_9 ?: 0;
        $full_width_act = $request->txtfull_width_act ?: 0;
        $cuttable_width_act = $request->txtcuttable_width_act ?: 0;

        DB::connection('mysql_sb')->insert("INSERT INTO qc_inspect_form_det (
        no_form,
        id_length,
        id_defect,
        up_to_3,
        `3_6`,
        `6_9`,
        over_9,
        full_width_act,
        cuttable_width_act,
        created_by,
        created_at,
        updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $txtno_form,
            $cbo_length,
            $cbo_defect,
            $txtup_to_3,
            $txt3_6,
            $txt6_9,
            $txtovr_9,
            $full_width_act,
            $cuttable_width_act,
            $user,
            $timestamp,
            $timestamp
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Form Inspect Berhasil di Update dengan Detail Fabric',
        ]);
    }

    public function qc_inspect_show_visual_inspect(Request $request)
    {
        $id = $request->id;
        $txtno_form = $request->txtno_form;

        $data_visual_inspect = DB::connection('mysql_sb')->select("SELECT
        a.id,
concat(c.from, ' - ', c.to) nm_length,
b.critical_defect,
up_to_3,
3_6,
6_9,
over_9,
concat(full_width_act, ' -> ' , cuttable_width_act) hasil,
full_width_act,
cuttable_width_act,
qc.status_proses_form
FROM qc_inspect_form_det a
left join qc_inspect_master_defect b on a.id_defect = b.id
inner join qc_inspect_master_lenght c on a.id_length = c.id
inner join qc_inspect_form qc on a.no_form = qc.no_form
where a.no_form = '$txtno_form'
            ");

        return DataTables::of($data_visual_inspect)->toJson();
    }

    public function qc_inspect_delete_visual(Request $request)
    {
        $id = $request->id;

        // Perform delete
        $deleted = DB::connection('mysql_sb')->delete("DELETE FROM qc_inspect_form_det WHERE id = ?", [$id]);

        // Return JSON response
        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil dihapus.',
            'deleted' => $deleted
        ]);
    }


    public function calculate_act_point(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $id = $request->id;
        $txtno_form = $request->txtno_form;
        $txtbintex_length = $request->txtbintex_length ?? 0;
        $unitBintex = $request->unitBintex;
        $txtbintex_act = $request->txtbintex_act;
        $txtact_length = $request->txtact_length ?? 0;
        $unitActLength = $request->unitActLength;
        $txtact_length_fix = $request->txtact_length_fix;

        $update = DB::connection('mysql_sb')->update("UPDATE qc_inspect_form set
        bintex_length = '$txtbintex_length',
        unit_bintex = '$unitBintex',
        bintex_length_act = '$txtbintex_act',
        act_length = '$txtact_length',
        unit_act_length = '$unitActLength',
        act_length_fix = '$txtact_length_fix'
        WHERE id = '$id'
    ");

        // Return detailed response
        return response()->json([
            'status' => 'success',
            'message' => 'Form Inspect Berhasil di Calculate',
        ]);
    }

    public function qc_inspect_show_act_point(Request $request)
    {
        $id = $request->id;
        $txtno_form = $request->txtno_form;

        $data_act_point = DB::connection('mysql_sb')->select("WITH a AS (
    SELECT
        a.no_form,
        SUM(up_to_3) * 1 AS sum_up_to_3,
        SUM(`3_6`) * 2 AS sum_3_6,
        SUM(`6_9`) * 3 AS sum_6_9,
        SUM(over_9) * 4 AS sum_over_9,
				c.individu
    FROM qc_inspect_form_det a
    INNER JOIN qc_inspect_form b ON a.no_form = b.no_form
    LEFT JOIN qc_inspect_master_group_inspect c ON b.group_inspect = c.id
    WHERE a.no_form = '$txtno_form'
),
b AS (
    SELECT
        a.no_form,
        AVG(cuttable_width_act) AS avg_width,
        b.act_length_fix
    FROM qc_inspect_form_det a
    INNER JOIN qc_inspect_form b ON a.no_form = b.no_form
    LEFT JOIN qc_inspect_master_group_inspect c ON b.group_inspect = c.id
    WHERE a.no_form = '$txtno_form'
      AND cuttable_width_act > 0
    GROUP BY a.no_form, b.act_length_fix
),
c AS (
    SELECT
        a.no_form,
				sum_up_to_3,
				sum_3_6,
				sum_6_9,
				sum_over_9,
        (sum_up_to_3 + sum_3_6 + sum_6_9 + sum_over_9) AS tot_point,
				individu
    FROM a
)

SELECT
sum_up_to_3,
sum_3_6,
sum_6_9,
sum_over_9,
c.tot_point,
round((((c.tot_point * 36) * 100) / (b.avg_width * b.act_length_fix))) AS act_point,
individu,
if(round((((c.tot_point * 36) * 100) / (b.avg_width * b.act_length_fix))) <= individu,'PASS','REJECT') result
FROM c
INNER JOIN b ON c.no_form = b.no_form;
            ");

        return DataTables::of($data_act_point)->toJson();
    }

    public function finish_form_inspect(Request $request)
    {
        $id = $request->id;
        $txtno_form = $request->txtno_form;
        $timestamp = Carbon::now();

        $get_result = DB::connection('mysql_sb')->select("SELECT
                    IF(
                ROUND(
                    (
                        (
                            SUM(up_to_3) * 1 +
                            SUM(`3_6`) * 2 +
                            SUM(`6_9`) * 3 +
                            SUM(over_9) * 4
                        ) * 36 * 100
                    ) / (
                        AVG(a.cuttable_width_act) *
                        AVG(
                            CASE
                                WHEN b.unit_act_length = 'meter' THEN b.act_length / 0.9144
                                ELSE b.act_length
                            END
                        )
                    )
                ) <= c.individu,
                        'pass',
                        'reject'
                    ) AS result
                FROM qc_inspect_form_det a
                INNER JOIN qc_inspect_form b ON a.no_form = b.no_form
                INNER JOIN qc_inspect_master_group_inspect c ON b.group_inspect = c.id
                WHERE a.no_form = '$txtno_form';
            ");

        $result                = $get_result[0]->result;

        $finish_form = DB::connection('mysql_sb')->select("UPDATE qc_inspect_form SET
        status_proses_form = 'done',
        result = '$result',
        finish_form = '$timestamp'
        where no_form = '$txtno_form'
            ");

        return response()->json([
            'status' => 'success',
            'message' => 'Form inspection berhasil diselesaikan.'
        ]);
    }
}
