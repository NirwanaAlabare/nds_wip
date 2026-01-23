<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\Cutting\ScannedItem;
use App\Models\Cutting\FormCutInputDetail;
use App\Exports\ExportLaporanRoll;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use DNS1D;
use PDF;
use DB;
//
use Illuminate\Support\Facades\Auth;

class GantiRejectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /// form GR Panel
    public function form_gr_panel(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $tgl_skrg = date('Y-m-d');
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-30 days'));
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data_input = DB::select("SELECT
tgl_form,
DATE_FORMAT(a.tgl_form, '%d-%M-%Y') AS tgl_form_fix,
no_form,
tujuan,
id_item,
barcode,
panel,
mp.nama_panel,
k.kpno,
k.styleno,
a.color,
a.qty_pakai,
sum(b.qty) as qty_part,
s.unit
from form_cut_gr_panel_barcode a
inner join form_cut_gr_panel_barcode_det b on a.id = b.id_form
left join scanned_item s on a.barcode  = s.id_roll
left join signalbit_erp.masterpanel mp on a.panel = mp.id
left join (
				SELECT
						jd.id_jo,
						ac.kpno,
                        supplier as buyer,
                        styleno
				FROM signalbit_erp.jo_det jd
				INNER JOIN signalbit_erp.so ON jd.id_so = so.id
				INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
				WHERE jd.cancel = 'N'
				GROUP BY jd.id_jo
) k on a.id_jo = k.id_jo
 where a.tgl_form >= '$tgl_awal' and a.tgl_form <= '$tgl_akhir'
group by no_form
Order by a.tgl_form desc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view(
            'cutting.ganti-reject.form_ganti_reject_panel',
            [
                'page' => 'dashboard-cutting',
                "subPageGroup" => "cutting-reject",
                "subPage" => "cutting-reject",
                'tgl_skrg_min_sebulan' => $tgl_skrg_min_sebulan,
                'tgl_skrg' => $tgl_skrg,
                "user" => $user
            ]
        );
    }

    public function create_form_gr_panel(Request $request)
    {
        $user = Auth::user()->name;

        $data_dept = DB::connection('mysql_hris')->select("SELECT sub_dept_name isi, sub_dept_name tampil
        from department_all where site_nirwana_id = 'NAG' and group_1 = 'PRODUCTION'
        ORDER BY department_name asc, sub_dept_name asc");

        $data_ws = DB::connection('mysql_sb')->select("SELECT id_jo as isi, ac.kpno tampil from jo_det jd
         inner join so on jd.id_so = so.id
         inner join act_costing ac on so.id_cost = ac.id
         where jd.cancel = 'N' and ac.cost_date >= '2025-01-01'
         group by id_cost order by ac.kpno asc");

        return view(
            'cutting.ganti-reject.create_form_ganti_reject_panel',
            [
                'page' => 'dashboard-cutting',
                "subPageGroup" => "cutting-reject",
                "subPage" => "cutting-reject",
                "user" => $user,
                "data_dept" => $data_dept,
                "data_ws" => $data_ws
            ]
        );
    }

    public function get_barcode_form_gr_panel($id = 0, Request $request)
    {

        // 1️⃣ cek data barcode
        $cek_barcode = DB::selectOne("WITH db as
(
SELECT
barcode,
id_item,
id_jo,
detail_item,
roll_buyer,
lot,
a.qty_roll,
qty_in,
a.qty_pakai,
unit,
color

        FROM form_cut_alokasi_gr_panel_barcode a
		left join scanned_item b on a.barcode = b.id_roll
        WHERE barcode = ?
        ORDER BY a.id DESC
        LIMIT 1
),
lr as (
		SELECT
				br.id,
				br.id_item,
				a.id_jo,
				IFNULL(br.idws_act, a.kpno) AS ws_act,
                buyer,
                a.styleno
		FROM signalbit_erp.bppb_req br
		INNER JOIN (
				SELECT
						id_item,
						id_jo,
						MAX(id) AS max_id
				FROM signalbit_erp.bppb_req
				GROUP BY id_item, id_jo
		) x ON br.id = x.max_id
		LEFT JOIN (
				SELECT
						jd.id_jo,
						ac.kpno,
                        supplier as buyer,
                        styleno
				FROM signalbit_erp.jo_det jd
				INNER JOIN signalbit_erp.so ON jd.id_so = so.id
				INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
				WHERE jd.cancel = 'N'
				GROUP BY jd.id_jo
		) a ON br.id_jo = a.id_jo
)


SELECT db.*, lr.ws_act, lr.buyer, lr.styleno FROM db
left join lr on db.id_item = lr.id_item and  db.id_jo = lr.id_jo", [$id]);

        if (!$cek_barcode) {
            return response()->json([
                'status' => 'N',
                'message' => 'Barcode tidak terdaftar'
            ]);
        }


        return response()->json([
            'status' => 'success',
            'data' => [
                'barcode'   => $cek_barcode->barcode,
                'id_item' => $cek_barcode->id_item,
                'detail_item' => $cek_barcode->detail_item,
                'roll_buyer' => $cek_barcode->roll_buyer,
                'lot' => $cek_barcode->lot,
                'qty_in' => $cek_barcode->qty_in,
                'qty_roll' => $cek_barcode->qty_roll,
                'qty_pakai' => $cek_barcode->qty_pakai,
                'unit' => $cek_barcode->unit,
                'color' => $cek_barcode->color,
                'ws_act' => $cek_barcode->ws_act,
                'buyer' => $cek_barcode->buyer,
                'styleno' => $cek_barcode->styleno,
                'color' => $cek_barcode->color,
                // You can add more fields here if needed
            ]
        ]);
    }

    public function get_ws_all_form_gr_panel(Request $request)
    {
        $cboalo_ws = $request->cboalo_ws;

        if (!$cboalo_ws) {
            return response()->json([
                'status' => 'N',
                'message' => 'WS tidak valid'
            ]);
        }

        // ===============================
        // 1️⃣ HEADER WS (buyer & style)
        // ===============================
        $ws = DB::connection('mysql_sb')->selectOne("SELECT
            ms.supplier AS buyer,
            ac.styleno
        FROM jo_det jd
        INNER JOIN so ON jd.id_so = so.id
        INNER JOIN act_costing ac ON so.id_cost = ac.id
        INNER JOIN mastersupplier ms ON ac.id_buyer = ms.Id_Supplier
        WHERE jd.cancel = 'N'
          AND jd.id_jo = ?
        GROUP BY ac.id
        ORDER BY ac.kpno ASC
    ", [$cboalo_ws]);

        if (!$ws) {
            return response()->json([
                'status' => 'N',
                'message' => 'Data WS tidak ditemukan'
            ]);
        }

        // ===============================
        // 2️⃣ COLOR
        // ===============================
        $colors = DB::connection('mysql_sb')->select("SELECT color AS isi, color AS tampil
        FROM so_det sd
        INNER JOIN so ON sd.id_so = so.id
        INNER JOIN act_costing ac ON so.id_cost = ac.id
        INNER JOIN jo_det jd ON so.id = jd.id_so
        WHERE jd.cancel = 'N'
          AND jd.id_jo = ?
        GROUP BY color
    ", [$cboalo_ws]);

        $colorHtml = "<option value=''>Pilih Color</option>";
        foreach ($colors as $c) {
            $colorHtml .= "<option value='{$c->isi}'>{$c->tampil}</option>";
        }

        // ===============================
        // 3️⃣ PANEL
        // ===============================
        $panels = DB::connection('mysql_sb')->select("SELECT id_panel AS isi, mp.nama_panel AS tampil
        FROM bom_jo_item k
        INNER JOIN masterpanel mp ON k.id_panel = mp.id
        WHERE k.id_jo = ?
          AND k.cancel = 'N'
        GROUP BY id_panel
    ", [$cboalo_ws]);

        $panelHtml = "<option value=''>Pilih Panel</option>";
        foreach ($panels as $p) {
            $panelHtml .= "<option value='{$p->isi}'>{$p->tampil}</option>";
        }

        // ===============================
        // 4 SIZE
        // ===============================
        $size = DB::connection('mysql_sb')->select("SELECT sd.size AS isi, sd.size AS tampil
        FROM so_det sd
        INNER JOIN so ON sd.id_so = so.id
        INNER JOIN act_costing ac ON so.id_cost = ac.id
        INNER JOIN jo_det jd ON so.id = jd.id_so
        LEFT JOIN master_size_new msn on sd.size = msn.size
        WHERE jd.cancel = 'N'
          AND jd.id_jo = ?
        GROUP BY sd.size
        ORDER BY urutan asc
    ", [$cboalo_ws]);

        $sizeHtml = "<option value=''>Pilih Size</option>";
        foreach ($size as $s) {
            $sizeHtml .= "<option value='{$s->isi}'>{$s->tampil}</option>";
        }

        // ===============================
        // RESPONSE FINAL
        // ===============================
        return response()->json([
            'status' => 'success',
            'data' => [
                'buyer'  => $ws->buyer,
                'styleno' => $ws->styleno,
                'colors' => $colorHtml,
                'panels' => $panelHtml,
                'sizes' => $sizeHtml
            ]
        ]);
    }



    public function save_form_gr_panel(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $today = date('Y-m-d');

        $barcode = $request->txtbarcode;
        $txtalo_qty_pakai = $request->txtalo_qty_pakai;
        $cboalo_tujuan = $request->cboalo_tujuan;
        $cbocolor = $request->cbocolor;
        $cbopanel = $request->cbopanel;
        $cbows = $request->cbows;
        $panels = $request->input('tempPanels', []);

        if (empty($panels)) {
            return response()->json(['status' => 'error', 'message' => 'Tidak ada data panel'], 422);
        }

        // Prepare date values
        $datePrefix = date('my');
        $month = date('m');
        $year = date('Y');

        $get_last_number = DB::select("
        SELECT MAX(CAST(SUBSTRING_INDEX(no_form, '/', -1) AS UNSIGNED)) AS last_number
        FROM form_cut_gr_panel_barcode
        WHERE MONTH(tgl_form) = ? AND YEAR(tgl_form) = ?
    ", [$month, $year]);

        $last_number = $get_last_number[0]->last_number ?? 0;
        $formCounter = $last_number + 1;

        $no_form = 'GRP/OUT/' . $datePrefix . '/' . $formCounter++;

        $id_header = DB::table('form_cut_gr_panel_barcode')->insertGetId([
            'no_form' => $no_form,
            'tgl_form' => $today,
            'barcode' => $barcode,
            'qty_pakai' => $txtalo_qty_pakai,
            'tujuan' => $cboalo_tujuan,
            'color' => $cbocolor,
            'panel' => $cbopanel,
            'id_jo' => $cbows,
            'created_by' => $user,
            'created_at' => $timestamp,
            'updated_at' => $timestamp
        ]);

        foreach ($panels as $p) {
            DB::table('form_cut_gr_panel_barcode_det')->insert([
                'id_form' => $id_header,
                'part' => strtoupper($p['part']),
                'size' => $p['size'],
                'qty' => $p['qty'],
                'created_by' => $user,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Form Berhasil dibuat',
            'no_form' => $no_form
        ]);
    }
}
