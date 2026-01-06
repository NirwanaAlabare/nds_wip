<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;
use App\Imports\ImportIE_MasterProcess;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoadingOutController extends Controller
{
    public function loading_out(Request $request)
    {

        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;

        if ($request->ajax()) {
            $data_input = DB::select("SELECT
no_form,
tgl_form,
DATE_FORMAT(tgl_form, '%d-%b-%Y') AS tgl_form_fix,
ph.pono,
supplier,
a.jns_dok,
a.jns_pengeluaran,
sum(b.qty) tot_qty,
a.berat_panel,
a.berat_karung,
a.ket,
a.created_by,
a.updated_at
from wip_out a
left join wip_out_det b on a.id = b.id_wip_out
left join signalbit_erp.po_header ph on a.id_po = ph.id
left join signalbit_erp.mastersupplier ms on ph.id_supplier = ms.Id_Supplier
left join stocker_input si on b.id_qr_stocker = si.id_qr_stocker
where tgl_form >= '$tgl_awal' and tgl_form <= '$tgl_akhir'
group by no_form
            ");

            return DataTables::of($data_input)->toJson();
        }

        // For non-AJAX (initial page load)
        return view('dc.loading_out.loading_out', [
            'page' => 'dashboard-dc',
            'subPageGroup' => 'loading-dc',
            'subPage' => 'loading_out',
            'containerFluid' => true,
        ]);
    }

    public function loading_out_det(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;

        $data = DB::select("SELECT
no_form,
tgl_form,
DATE_FORMAT(tgl_form, '%d-%b-%Y') AS tgl_form_fix,
ph.pono,
ms.supplier,
a.jns_dok,
a.jns_pengeluaran,
a.berat_panel,
a.berat_karung,
a.ket,
sum(b.qty) as qty,
b.id_qr_stocker,
b.no_karung,
ac.kpno,
ac.styleno,
sd.color,
sd.size,
mb.supplier buyer
from wip_out a
left join wip_out_det b on a.id = b.id_wip_out
left join signalbit_erp.po_header ph on a.id_po = ph.id
left join stocker_input si on b.id_qr_stocker = si.id_qr_stocker
left join signalbit_erp.mastersupplier ms on ph.id_supplier = ms.Id_Supplier
left join signalbit_erp.so_det sd on si.so_det_id = sd.id
left join signalbit_erp.so on sd.id_so = so.id
left join signalbit_erp.act_costing ac on so.id_cost = ac.id
left join signalbit_erp.mastersupplier mb on ac.id_buyer = mb.id_supplier
where tgl_form >= '$tgl_awal' and tgl_form <= '$tgl_akhir'
group by no_form, si.so_det_id
order by no_form asc, tgl_form asc, a.created_at desc");

        return DataTables::of($data)->toJson();
    }


    public function input_loading_out(Request $request)
    {
        $user = Auth::user()->name;

        $data_supplier = DB::connection('mysql_sb')->select("SELECT ms.id_supplier as isi, Supplier as tampil from po_header ph
		inner join mastersupplier ms on ph.id_supplier = ms.id_supplier
		where jenis = 'P'
		group by ph.id_supplier
		order by SUpplier asc	");

        $data_dok = DB::connection('mysql_sb')->select("SELECT nama_pilihan isi,nama_pilihan tampil
            from masterpilihan where kode_pilihan='Status KB Out'");

        $data_jns = DB::connection('mysql_sb')->select("SELECT nama_trans isi,nama_trans tampil from mastertransaksi where
                            jenis_trans='OUT' and jns_gudang = 'FACC' order by id");

        return view(
            'dc.loading_out.input_loading_out',
            [
                'page' => 'dashboard-dc',
                'subPageGroup' => 'loading-dc',
                'subPage' => 'loading_out',
                'data_supplier' => $data_supplier,
                'data_dok' => $data_dok,
                'data_jns' => $data_jns,
                "containerFluid" => true,
                "user" => $user
            ]
        );
    }


    public function getpo_loading_out(Request $request)
    {

        $user = Auth::user()->name;
        $id_supplier = $request->cbo_sup;

        $data_po = DB::connection('mysql_sb')->select("SELECT id as isi, pono as tampil
        from po_header
        where jenis = 'P' and app = 'A' and id_supplier = '$id_supplier'
        order by podate desc
        ");

        $html = "<option value=''>Pilih No PO</option>";

        foreach ($data_po as $datapo) {
            $html .= " <option value='" . $datapo->isi . "'>" . $datapo->tampil . "</option> ";
        }

        return $html;
    }

    public function get_list_po_loading_out(Request $request)
    {
        $id_po = $request->id_po;
        $data_input = DB::select("WITH tmp as (
SELECT
k.id_item,
k.id_jo,
sum(dc.qty_awal - dc.qty_reject + dc.qty_replace) qty_input
from wip_out_tmp tmp
left join stocker_input a on tmp.id_qr_stocker = a.id_qr_stocker
left join part_detail p on a.part_detail_id = p.id
left join part_detail_item pdi on p.id = pdi.part_detail_id
left join signalbit_erp.bom_jo_item k on pdi.bom_jo_item_id = k.id
left join signalbit_erp.masteritem mi on k.id_item = mi.id_item
left join form_cut_input f on a.form_cut_id = f.id
left join signalbit_erp.so_det sd on a.so_det_id = sd.id
left join signalbit_erp.so on sd.id_so = so.id
left join signalbit_erp.act_costing ac on so.id_cost = ac.id
left join dc_in_input dc on tmp.id_qr_stocker = dc.id_qr_stocker
where id_po = '$id_po'
group by k.id_item, k.id_jo
),
po as (
SELECT
            a.id,
            a.id_jo,
            ac.kpno ws,
            jo.jo_no,
            mi.itemdesc,
            mi.id_item,
            a.qty qty_po,
            a.unit
            from signalbit_erp.po_item a
            inner join signalbit_erp.jo_det jd on a.id_jo = jd.id_jo
            inner join signalbit_erp.jo on jd.id_jo = jo.id
            inner join signalbit_erp.so on jd.id_so = so.id
            inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
            inner join signalbit_erp.masteritem mi on a.id_gen = mi.id_item
            where id_po = '$id_po' and a.cancel = 'N'
            order by kpno desc
),
bppb as (
select id_item, id_jo, sum(qty) qty_out
from signalbit_erp.bppb where bppbno like 'SJ-C%' and id_po = '$id_po'
group by id_item , id_jo
),
bpb as (
select id_item, id_jo, sum(qty) qty_in from signalbit_erp.bpb
inner join signalbit_erp.po_header ph on bpb.pono = ph.pono
where bpbno like 'C%' and ph.id = '$id_po'
group by id_item, id_jo
)

SELECT
po.id,
po.id_jo,
po.ws,
po.jo_no,
po.itemdesc,
po.id_item,
po.qty_po,
po.qty_po - bppb.qty_out qty_outstanding,
coalesce(qty_input,0) as qty_input,
po.qty_po - bppb.qty_out - coalesce(qty_input,0) blc,
po.unit
from po
left join tmp on po.id_item = tmp.id_item and po.id_jo = tmp.id_jo
left join bpb on po.id_item = bpb.id_item and po.id_jo = bpb.id_jo
left join bppb on po.id_item = bppb.id_item and po.id_jo = bppb.id_jo
order by po.ws asc
            ");

        return DataTables::of($data_input)->toJson();
    }

    public function get_loading_out_stocker_info(Request $request)
    {
        $no_stocker = $request->no_stocker;
        $id_po      = $request->id_po;

        // 1️⃣ cek stocker ada
        $cek_data_stocker = DB::selectOne("
    SELECT id_qr_stocker
    FROM stocker_input
    WHERE id_qr_stocker = ?
", [$no_stocker]);

        if (!$cek_data_stocker) {
            return response()->json([
                'result' => 'N',
                'message' => 'Stocker tidak terdaftar'
            ]);
        }

        // 2️⃣ cek part stocker
        $cek_part_stocker = DB::selectOne("
    SELECT b.id
    FROM stocker_input si
    LEFT JOIN part_detail a ON si.part_detail_id = a.id
    LEFT JOIN part_detail_item b ON a.id = b.part_detail_id
    WHERE si.id_qr_stocker = ?
", [$no_stocker]);

        if (!$cek_part_stocker || $cek_part_stocker->id === null) {
            return response()->json([
                'result' => 'N',
                'message' => 'Stocker belum diisi part'
            ]);
        }


        // 3️⃣ cek DC IN
        $cek_dc_in = DB::selectOne("
    SELECT id
    FROM dc_in_input
    WHERE id_qr_stocker = ?
", [$no_stocker]);

        if (!$cek_dc_in) {
            return response()->json([
                'result' => 'N',
                'message' => 'Stocker belum DC IN'
            ]);
        }

        $cek_stocker = DB::connection('mysql_sb')->selectOne("SELECT
            si.id_qr_stocker,
            jd.id_jo,
            CASE
                WHEN b.id_jo IS NULL THEN 'N'
                ELSE 'Y'
            END AS result
        FROM laravel_nds.stocker_input si
        LEFT JOIN so_det sd
            ON si.so_det_id = sd.id
        LEFT JOIN so
            ON sd.id_so = so.id
        LEFT JOIN act_costing ac
            ON so.id_cost = ac.id
        LEFT JOIN jo_det jd
            ON sd.id_so = jd.id_so
        LEFT JOIN (
            SELECT DISTINCT id_jo
            FROM po_item
            WHERE id_po = ?
        ) b
            ON jd.id_jo = b.id_jo
        WHERE si.id_qr_stocker = ?
    ", [
            $id_po,
            $no_stocker
        ]);

        if (empty($cek_stocker)) {
            return response()->json([
                'result'  => 'N',
                'message' => 'Stocker tidak ditemukan'
            ]);
        }

        $cek_tmp = DB::selectOne("
        SELECT id
        FROM wip_out_tmp
        WHERE id_qr_stocker = ?
    ", [$no_stocker]);

        if ($cek_tmp) {
            return response()->json([
                'result' => 'N',
                'message' => 'Stocker Sudah diScan'
            ]);
        }

        // 2️⃣ cek duplicate TMP
        $cek_input_stocker = DB::selectOne("
        SELECT id_qr_stocker
        FROM wip_out_det
        WHERE id_qr_stocker = ?
    ", [$no_stocker]);

        if ($cek_input_stocker) {
            return response()->json([
                'result'  => 'N',
                'message' => 'Stocker sudah pernah discan'
            ]);
        }

        $stocker = $cek_stocker;

        return response()->json([
            'result'  => $stocker->result,
            'data'    => $stocker
        ]);
    }


    public function save_tmp_stocker_loading_out(Request $request)
    {
        $no_stocker = $request->no_stocker;
        $id_po      = $request->id_po;
        $no_karung      = $request->no_karung;
        $user = Auth::user()->name;
        $timestamp = Carbon::now();


        DB::insert("INSERT INTO wip_out_tmp (
        id_qr_stocker,
        id_po,
        no_karung,
        created_by,
        created_at,
        updated_at
    ) VALUES (?,?,?, ?, ?, ?)", [
            $no_stocker,
            $id_po,
            $no_karung,
            $user,
            $timestamp,
            $timestamp
        ]);

        // 4️⃣ response sukses
        return response()->json([
            'status'  => 'success',
            'message' => 'Stocker berhasil disimpan'
        ]);
    }


    public function get_list_tmp_scan_loading_out(Request $request)
    {
        $id_po = $request->id_po;
        $data_input = DB::select("SELECT
        tmp.id,
        tmp.no_karung,
        tmp.id_qr_stocker,
        f.no_cut,
        mi.itemdesc,
        f.shell,
        ac.kpno,
        ac.styleno,
        sd.color,
        sd.size,
        dc.qty_awal - dc.qty_reject + dc.qty_replace as qty,
        concat(a.range_awal, ' - ', a.range_akhir) range_stocker
        from wip_out_tmp tmp
        left join stocker_input a on tmp.id_qr_stocker = a.id_qr_stocker
        left join part_detail p on a.part_detail_id = p.id
        left join part_detail_item pdi on p.id = pdi.part_detail_id
        left join signalbit_erp.bom_jo_item k on pdi.bom_jo_item_id = k.id
        left join signalbit_erp.masteritem mi on k.id_item = mi.id_item
        left join form_cut_input f on a.form_cut_id = f.id
        left join signalbit_erp.so_det sd on a.so_det_id = sd.id
        left join signalbit_erp.so on sd.id_so = so.id
        left join signalbit_erp.act_costing ac on so.id_cost = ac.id
        left join dc_in_input dc on a.id_qr_stocker = dc.id_qr_stocker
        where id_po = '$id_po'
        order by tmp.id desc");

        return DataTables::of($data_input)->toJson();
    }

    public function loading_out_delete_tmp(Request $request)
    {
        $id = $request->id;

        // Perform delete
        $deleted = DB::delete("DELETE FROM wip_out_tmp WHERE id = ?", [$id]);

        // Return JSON response
        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil dihapus.',
            'deleted' => $deleted
        ]);
    }

    public function save_loading_out(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $cbo_sup = $request->cbo_sup;
        $id_po = $request->id_po;
        $cbo_dok = $request->cbo_dok;
        $cbo_jns = $request->cbo_jns;
        $tgl_trans = $request->tgl_trans;
        $txt_ket = $request->txt_ket;
        $txt_berat_panel = $request->txt_berat_panel ?? 0;
        $txt_berat_karung = $request->txt_berat_karung ?? 0;


        $cek_detail = DB::table('wip_out_tmp')
            ->where('id_po', $id_po)
            ->exists();

        if (!$cek_detail) {
            return response()->json([
                'status' => 'error',
                'message' => 'Detail masih kosong, tidak bisa menyimpan data'
            ], 422);
        }

        // Prepare date values
        $tgl_trans = Carbon::parse($request->tgl_trans);
        $datePrefix = $tgl_trans->format('my');
        $month = $tgl_trans->format('m');
        $year = $tgl_trans->format('Y');

        $get_last_number = DB::select("
        SELECT MAX(CAST(SUBSTRING_INDEX(no_form, '/', -1) AS UNSIGNED)) AS last_number
        FROM wip_out
        WHERE MONTH(tgl_form) = ? AND YEAR(tgl_form) = ?
    ", [$month, $year]);

        $last_number = $get_last_number[0]->last_number ?? 0;
        $formCounter = $last_number + 1;

        $no_form = 'SCP/OUT/' . $datePrefix . '/' . $formCounter++;

        $id_header = DB::table('wip_out')->insertGetId([
            'no_form' => $no_form,
            'tgl_form' => $tgl_trans,
            'id_po' => $id_po,
            'id_supplier' => $cbo_sup,
            'jns_dok' => $cbo_dok,
            'jns_pengeluaran' => $cbo_jns,
            'ket' => $txt_ket,
            'berat_panel' => $txt_berat_panel,
            'berat_karung' => $txt_berat_karung,
            'created_by' => $user,
            'created_at' => $timestamp,
            'updated_at' => $timestamp
        ]);

        DB::insert("
    INSERT INTO wip_out_det
    (id_wip_out, no_karung, id_qr_stocker, id_item, id_jo, qty, created_by, created_at, updated_at)
    SELECT ?, no_karung, tmp.id_qr_stocker, k.id_item, k.id_jo, dc.qty_awal - dc.qty_reject + dc.qty_replace as qty, tmp.created_by, tmp.created_at, tmp.updated_at
    FROM wip_out_tmp tmp
		left join stocker_input a on tmp.id_qr_stocker = a.id_qr_stocker
		left join part_detail p on a.part_detail_id = p.id
		left join part_detail_item pdi on p.id = pdi.part_detail_id
		left join signalbit_erp.bom_jo_item k on pdi.bom_jo_item_id = k.id
        left join dc_in_input dc on a.id_qr_stocker = dc.id_qr_stocker
        WHERE id_po = ?
", [$id_header, $id_po]);

        DB::table('wip_out_tmp')->where('id_po', $id_po)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Form Berhasil dibuat',
            'no_form' => $no_form
        ]);
    }

    public function get_info_modal_det_loading_out(Request $request)
    {
        $no_form = $request->no_form;
        // Use query builder with parameter binding to avoid SQL injection
        $data_input = DB::selectOne("SELECT
        no_form,
        supplier,
        berat_panel,
        berat_karung
        FROM wip_out a
        left join wip_out_det b on a.id = b.id_wip_out
        left join signalbit_erp.po_header ph on a.id_po = ph.id
        left join signalbit_erp.mastersupplier ms on ph.id_supplier = ms.Id_Supplier
        where no_form = ?
    ", [$no_form]);

        if (!$data_input) {
            return response()->json(['error' => 'Data not found'], 404);
        }

        return response()->json($data_input);
    }

    public function get_table_modal_det_loading_out(Request $request)
    {
        $no_form = $request->no_form;

        $data = DB::select("SELECT
		ac.kpno,
		mi.itemdesc,
		sum(si.qty_ply) tot_qty
        from wip_out_det a
		left join wip_out b on a.id_wip_out = b.id
        left join stocker_input si on a.id_qr_stocker = si.id_qr_stocker
        left join part_detail p on si.part_detail_id = p.id
        left join part_detail_item pdi on p.id = pdi.part_detail_id
        left join signalbit_erp.bom_jo_item k on pdi.bom_jo_item_id = k.id
        left join signalbit_erp.masteritem mi on k.id_item = mi.id_item
        left join signalbit_erp.so_det sd on si.so_det_id = sd.id
        left join signalbit_erp.so on sd.id_so = so.id
        left join signalbit_erp.act_costing ac on so.id_cost = ac.id
		where no_form = '$no_form'
		group by kpno, mi.id_item
		order by kpno asc, itemdesc asc");

        return DataTables::of($data)->toJson();
    }

    public function get_table_modal_stocker_loading_out(Request $request)
    {
        $no_form = $request->no_form;

        $data = DB::select("SELECT
a.id_qr_stocker,
a.no_karung,
ac.kpno,
ac.styleno,
sd.color,
sd.size,
si.qty_ply
from wip_out_det a
left join wip_out b on a.id_wip_out = b.id
left join stocker_input si on a.id_qr_stocker = si.id_qr_stocker
left join signalbit_erp.so_det sd on si.so_det_id = sd.id
left join signalbit_erp.so on sd.id_so = so.id
left join signalbit_erp.act_costing ac on so.id_cost = ac.id
left join signalbit_erp.master_size_new msn on sd.size = msn.size
where no_form = '$no_form'
order by kpno asc, color asc, urutan asc");

        return DataTables::of($data)->toJson();
    }


    public function loading_out_konfirmasi(Request $request)
    {


        $no_form = $request->no_form;

        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $today = date('Y-m-d'); // misal 11 Des 2025 → 111225

        // Ambil ID terakhir hari ini
        $lastId = DB::table('ie_op_breakdown')
            ->where('id_op_breakdown', 'like', 'OB' . $today . '_%')
            ->max('id_op_breakdown');

        if ($lastId) {
            // Ambil angka urutan terakhir
            $parts = explode('_', $lastId);
            $lastNumber = (int) $parts[1];
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1; // belum ada, mulai dari 1
        }

        // Format angka jadi 2 digit
        $numberFormatted = str_pad($newNumber, 2, '0', STR_PAD_LEFT);

        // Buat ID baru dengan prefix PP
        $id_op_breakdown = 'OB' . $today . '_' . $numberFormatted;

        $style = strtoupper($request->style);
        $brand = strtoupper($request->brand);
        $id_product = $request->cbo_prod;
        $cbo_req = strtoupper($request->cbo_req);
        $req_date = strtoupper($request->req_date);
        $due_date = strtoupper($request->due_date);
        $stat = $request->stat;

        // CEK apakah nama sudah ada
        $exists = DB::table('ie_op_breakdown')
            ->where('style', $style)
            ->where('brand', $brand)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Operation Breakdown dengan style "' . $style . '" and brand "' . $brand . '" sudah ada'
            ], 400);
        }
        $filename = null;
        if ($request->hasFile('picture')) {
            $extension = $request->file('picture')->getClientOriginalExtension();
            $filename = $id_op_breakdown . '.' . $extension;
            $request->file('picture')->storeAs('public/gambar_op_breakdown', $filename);
        }


        // Siapkan data untuk batch insert
        $insertData = [];

        foreach ($ids as $processId) {
            $insertData[] = [
                'id_op_breakdown'   => $id_op_breakdown,
                'id_part_process'   => $processId,
                'tgl_trans'         => $today,
                'picture'           => $filename,
                'style'             => $style,
                'brand'             => $brand,
                'id_product'        => $id_product,
                'request_by'        => $cbo_req,
                'request_date'      => $req_date,
                'due_date'          => $due_date,
                'status'            => $stat,
                'created_by'        => $user,
                'created_at'        => $timestamp,
                'updated_at'        => $timestamp,
            ];
        }

        // Insert semua sekaligus
        DB::table('ie_op_breakdown')->insert($insertData);

        return response()->json([
            'status' => 'success',
            'message' => 'Style : ' . $style . ' Dengan Brand : ' . $brand . ' sudah ditambahkan',
        ]);
    }
}
