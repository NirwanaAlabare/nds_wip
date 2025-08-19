<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportPPIC_Master_so_sb;
use App\Exports\ExportPPIC_Master_so_ppic;
use App\Imports\ImportPPIC_SO;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use PhpOffice\PhpSpreadsheet\Style\Style;

class PPIC_MasterSOController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $tgl_skrg = date('Y-m-d');
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-30 days'));
        $ws = $request->ws;
        $style = $request->style;
        $user = Auth::user()->name;
        $filter = $request->filter;

        if ($filter == 'all') {
            $condition_tgl = " where tgl_shipment >= '$tgl_awal' and tgl_shipment <= '$tgl_akhir'";
            $condition = "";
            if (empty($ws) && empty($style)) {
                $condition = "";
            } else if (!empty($ws) && empty($style)) {
                $condition = "where kpno = '$ws'";
            } else if (empty($ws) && !empty($style)) {
                $condition = "where styleno = '$style'";
            } else if (!empty($ws) && !empty($style)) {
                $condition = "where kpno = '$ws' and styleno = '$style'";
            }
        } else if ($filter == 'date') {
            $condition_tgl = " where tgl_shipment >= '$tgl_awal' and tgl_shipment <= '$tgl_akhir'";
            $condition = "";
        } else if ($filter == 'ws-style') {
            $condition_tgl = "";
            $condition = "";
            if (!empty($ws) && empty($style)) {
                $condition = "where kpno = '$ws'";
            } else if (empty($ws) && !empty($style)) {
                $condition = "where styleno = '$style'";
            } else if (!empty($ws) && !empty($style)) {
                $condition = "where kpno = '$ws' and styleno = '$style'";
            }
        }

        if ($request->ajax()) {
            $data_input = DB::select("WITH ppic as (
                select * from laravel_nds.ppic_master_so p
                $condition_tgl
                ),
                gmt as (
                select sd.id as id_so_det,id_jo,kpno,styleno, sd.*, Supplier buyer, product_group from signalbit_erp.so_det sd
                inner join signalbit_erp.so on sd.id_so = so.id
                inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
                inner join signalbit_erp.jo_det jd on so.id = jd.id_so
                inner join signalbit_erp.masterproduct mp on ac.id_product = mp.id
                inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.id_supplier
                where sd.cancel = 'N' and so.cancel_h = 'N'
                ),
                pck_trf_gmt as (
                select id_ppic_master_so, sum(qty) qty_trf from packing_trf_garment
                group by id_ppic_master_so
                ),
                pck_in as (
                select id_ppic_master_so, sum(qty) qty_pck_in from packing_packing_in
                group by id_ppic_master_so
                ),
                pck_out as (
                select barcode,po,dest,count(barcode) qty_pck_out from packing_packing_out_scan
                group by barcode,po,dest
                )

                select
                p.id,
                p.id_so_det,
                gmt.buyer,
                CONCAT(DATE_FORMAT(p.tgl_shipment, '%d'), '-', LEFT(DATE_FORMAT(p.tgl_shipment, '%M'), 3), '-', DATE_FORMAT(p.tgl_shipment, '%Y')) AS tgl_shipment_fix,
                kpno ws,
                styleno,
                p.barcode,
                reff_no,
                p.desc,
                p.po,
                gmt.dest,
                product_group,
                gmt.color,
                gmt.size,
                p.qty_po,
                coalesce(pck_trf_gmt.qty_trf,0) qty_trf,
                coalesce(pck_in.qty_pck_in,0) qty_packing_in,
                coalesce(pck_out.qty_pck_out,0) qty_packing_out,
                p.created_by,
                p.created_at
                from ppic p
                inner join gmt on p.id_so_det = gmt.id_so_det
                left join pck_trf_gmt on p.id = pck_trf_gmt.id_ppic_master_so
                left join pck_in on p.id = pck_in.id_ppic_master_so
                left join pck_out on p.barcode = pck_out.barcode and p.po = pck_out.po and p.dest = pck_out.dest
                LEFT JOIN signalbit_erp.master_size_new msn ON gmt.size = msn.size
                $condition
                ORDER BY
                p.tgl_shipment DESC,
                gmt.buyer ASC,
                kpno ASC,
                gmt.dest ASC,
                gmt.color ASC,
                msn.urutan ASC,
                gmt.dest ASC;

            ");

            return DataTables::of($data_input)->toJson();
        }

        $data_ws = DB::select("select ws isi, ws tampil from
(select * from ppic_master_so p
where created_by = '" . $user . "' and tgl_shipment >= '" . $tgl_skrg_min_sebulan . "' ) p
inner join master_sb_ws m on p.id_so_det = m.id_so_det
group by ws
order by ws asc");

        return view(
            'ppic.master_so',
            [
                'page' => 'dashboard-ppic',
                "subPageGroup" => "ppic-master",
                "subPage" => "ppic-master-master-so",
                'data_ws' => $data_ws,
                "containerFluid" => true,
                "user" => $user
            ]
        );
    }


    public function show_tmp_ppic_so(Request $request)
    {
        $user = Auth::user()->name;
        if ($request->ajax()) {

            $data_tmp = DB::select("
            select
            tmp.id_tmp,
            m.id_so_det,
            tmp.ws,
            tmp.style,
            tmp.desc,
            tmp.color,
            tmp.size,
            tmp.buyer,
            tmp.barcode,
            tmp.po,
            tmp.dest,
            tmp.qty_po,
            tmp.tgl_shipment,
            tmp.created_at,
            tmp.updated_at,
            tmp.created_by,
            if(
            m.id_so_det is not null and tmp.tgl_shipment != '0000-00-00' and p.id_so_det is null,'Ok','Check') status
            from ppic_master_so_tmp tmp
            left join master_sb_ws m on tmp.ws = m.ws
            and tmp.color = m.color
            and tmp.size = m.size
            and tmp.style = m.styleno
            and tmp.dest = m.dest
            left join ppic_master_so p on m.id_so_det = p.id_so_det
                                and tmp.po = p.po
								and tmp.barcode = p.barcode
            left join master_size_new msn on tmp.size = msn.size
            where tmp.created_by = '$user'
            order by ws asc, urutan asc
            ");

            return DataTables::of($data_tmp)->toJson();
        }
    }

    public function import_excel_so(Request $request)
    {
        // validasi
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('file');

        $nama_file = rand() . $file->getClientOriginalName();

        $file->move('file_upload', $nama_file);

        Excel::import(new ImportPPIC_SO, public_path('/file_upload/' . $nama_file));

        return array(
            "status" => 200,
            "message" => 'Data Berhasil Di Upload',
            "additional" => [],
            // "redirect" => url('in-material/upload-lokasi')
        );

        // return array(
        //     "status" => 201,
        //     "message" => 'Data  Berhasil Di Upload',
        //     "additional" => [],
        //     "redirect" => '',
        //     "table" => 'datatable_preview',
        //     "callback" => "data_cek_tmp()"
        // );
    }

    public function contoh_upload_ppic_so()
    {
        $path = public_path('storage/contoh-upload.xlsx');
        return response()->download($path);
    }

    public function undo_tmp_ppic_so(Request $request)
    {
        $user = Auth::user()->name;

        $undo =  DB::delete(
            "DELETE FROM ppic_master_so_tmp where created_by = '$user'"
        );

        if ($undo) {
            return array(
                'icon' => 'benar',
                'msg' => 'Data berhasil diundo',
            );
        } else {
            return array(
                'icon' => 'salah',
                'msg' => 'Tidak ada yang diundo',
            );
        }
    }

    public function store_tmp_ppic_so(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $cek = DB::select("select count(m.id_so_det)tot_avail from ppic_master_so_tmp tmp
        left join master_sb_ws m on tmp.ws = m.ws
        and tmp.color = m.color
        and tmp.size = m.size
        and tmp.style = m.styleno
        and tmp.dest = m.dest
        left join ppic_master_so p on m.id_so_det = p.id_so_det
        and tmp.po = p.po
where tmp.created_by = '$user' and if(
m.id_so_det is not null and tmp.tgl_shipment != '0000-00-00' and p.id_so_det is null,'Ok','Check') = 'Check'");

        $cekinput = $cek[0]->tot_avail;

        if ($cekinput > '1') {
            return array(
                'icon' => 'salah',
                'msg' => 'Tidak ada yang disimpan, Periksa Data Lagi',
            );
        } else {
            $insert = DB::insert(
                "
                insert into ppic_master_so
                (id_so_det,barcode,po,dest,ppic_master_so.desc,tgl_shipment,qty_po,created_at,updated_at,created_by)
                select
                m.id_so_det,
                tmp.barcode,
                tmp.po,
                tmp.dest,
                tmp.desc,
                tmp.tgl_shipment,
                tmp.qty_po,
                '$timestamp',
                '$timestamp',
                tmp.created_by
                from ppic_master_so_tmp tmp
                left join master_sb_ws m on tmp.ws = m.ws
                                    and tmp.color = m.color
                                    and tmp.size = m.size
                                    and tmp.style = m.styleno
                                    and tmp.dest = m.dest
                left join ppic_master_so p on m.id_so_det = p.id_so_det
                                    and tmp.tgl_shipment = p.tgl_shipment
                                    and tmp.po = p.po
                where tmp.created_by = '$user' and if(
                m.id_so_det is not null and tmp.tgl_shipment != '0000-00-00' and p.id_so_det is null,'Ok','Check') = 'Ok'
                "
            );

            if ($insert) {
                $delete =  DB::delete(
                    "DELETE a.* FROM ppic_master_so_tmp a
                    where a.created_by = '$user' "
                );
                return array(
                    'icon' => 'benar',
                    'msg' => 'Transaksi Sudah Terbuat',
                );
            }
        }
    }

    public function hapus_data_temp_ppic_so(Request $request)
    {
        $id_tmp = $request->id_tmp;

        $del_tmp =  DB::delete("
        delete from ppic_master_so_tmp where id_tmp = '$id_tmp'");
    }


    public function master_so_tracking_output(Request $request)
    {
        $user = Auth::user()->name;
        $tgl_skrg = date('Y-m-d');


        $data_tracking = DB::select("
select
a.so_det_id,
concat((DATE_FORMAT(a.created_at,  '%d')), '-', left(DATE_FORMAT(a.created_at,  '%M'),3),'-',DATE_FORMAT(a.created_at,  '%Y')
) tgl_trans,
created_by sewing_line,
tot_p_line tot,
'PCS' unit,
m.ws,
p.list_po,
m.color,
m.size,
m.styleno,
m.dest,
m.buyer
from
(
select
so_det_id,count(so_det_id) tot_p_line , created_by, created_at
from output_rfts_packing a
where created_at >= '$tgl_skrg'
group by so_det_id, created_by ) a
left join master_sb_ws m on a.so_det_id = m.id_so_det
left join
	(
	select group_concat(DISTINCT(po)) list_po, id_so_det from ppic_master_so
	group by id_so_det
	) p on a.so_det_id = p.id_so_det
order by created_by asc
            ");

        return DataTables::of($data_tracking)->toJson();
    }

    public function show_data_ppic_master_so(Request $request)
    {
        $data_ppic_master_so = DB::select("
        SELECT
        a.id,
        a.id_so_det,
        m.buyer,
        concat((DATE_FORMAT(a.tgl_shipment,  '%d')), '-', left(DATE_FORMAT(a.tgl_shipment,  '%M'),3),'-',DATE_FORMAT(a.tgl_shipment,  '%Y')
        ) tgl_shipment_fix,
        a.tgl_shipment,
        a.barcode,
        m.reff_no,
        a.po,
        a.dest,
        a.desc,
        m.ws,
        m.styleno,
        m.color,
        m.size,
        a.qty_po,
        coalesce(trf.qty_trf,0) qty_trf,
        coalesce(pck.qty_packing_in,0) qty_packing_in,
        m.ws,
        a.created_by,
        a.created_at
        FROM ppic_master_so a
        inner join master_sb_ws m on a.id_so_det = m.id_so_det
        left join master_size_new msn on m.size = msn.size
        left join
            (
            select id_ppic_master_so, coalesce(sum(qty),0) qty_trf from packing_trf_garment group by id_ppic_master_so
            ) trf on trf.id_ppic_master_so = a.id
        left join
            (
            select id_ppic_master_so, coalesce(sum(qty),0) qty_packing_in from packing_packing_in group by id_ppic_master_so
            ) pck on pck.id_ppic_master_so = a.id
                            where a.id = '$request->id_c'");
        return json_encode($data_ppic_master_so[0]);
    }

    public function update_data_ppic_master_so(Request $request)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        $tgl_shipment = date('Y-m-d', strtotime($request->txted_tgl_shipment));
        // dd($tgl_shipment);
        DB::update(
            "update ppic_master_so
            set
            qty_po = '" . $request->txted_qty_po_skrg . "',
            old_qty_po = '" . $request->txted_qty_po . "',
            tgl_shipment = '" . $request->txted_tgl_shipment_skrg . "',
            old_tgl_shipment = '" . $tgl_shipment . "',
            tgl_update = '$timestamp',
            user_update = '$user'
            where id = '" . $request->txtid_c . "'
            "
        );

        return array(
            'status' => 200,
            'message' => 'Data  Berhasil Diupdate',
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }



    public function export_excel_master_sb_so(Request $request)
    {
        ini_set("max_execution_time", 3600000);
        ini_set('memory_limit', '5120000M');

        $data = DB::select("
        select *,date_format(tgl_kirim, '%Y-%m-%d') tgl_kirim_fix
        from master_sb_ws
        where tgl_kirim >= '2023-01-01'
        ");

        $excel = FastExcel::create('data');
        $sheet = $excel->getSheet();

        $area = $sheet->beginArea();

        $sheet->writeTo('A1', 'Laporan Master SO SB', ['font-size' => 16]);
        $sheet->mergeCells('A1:S1');

        $sheet->writeTo('A2', 'No', ['background-color' => '#D6EEEE'])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('B2', 'ID SO Det', ['background-color' => '#D6EEEE'])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('C2', 'WS', ['background-color' => '#FFFF00'])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('D2', 'No. Costing', ['background-color' => '#FFFF00'])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('E2', 'Tgl. Kirim', ['background-color' => '#FFFF00'])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('F2', 'Product Group', ['background-color' => '#FFFF00'])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('G2', 'Product Item', ['background-color' => '#FFFF00'])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('H2', 'Style', ['background-color' => '#FFFF00'])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('I2', 'Main Dest', ['background-color' => '#FFFF00'])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('J2', 'Dest', ['background-color' => '#FFFF00'])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('K2', 'Brand', ['background-color' => '#FFFF00'])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('L2', 'No. SO', ['background-color' => '#FFFF00'])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('M2', 'Buyer', ['background-color' => '#FFFF00'])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('N2', 'Color', ['background-color' => '#FFFF00'])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('O2', 'Size', ['background-color' => '#FFFF00'])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('P2', 'Qty SO', ['background-color' => '#FFFF00'])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('Q2', 'Price', ['background-color' => '#FFFF00'])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('R2', 'Reff No', ['background-color' => '#FFFF00'])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('S2', 'Style No Prod', ['background-color' => '#FFFF00'])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $sheet->writeAreas();

        $no = 1;
        foreach ($data as $d) {
            $dArr = [
                $no++,
                $d->id_so_det ? $d->id_so_det : '-',
                $d->ws ? $d->ws : '-',
                $d->cost_no ? $d->cost_no : '-',
                $d->tgl_kirim_fix ? $d->tgl_kirim_fix : '-',
                $d->product_group ? $d->product_group : '-',
                $d->product_item ? $d->product_item : '-',
                $d->styleno ? $d->styleno : '-',
                $d->main_dest ? $d->main_dest : '-',
                $d->dest ? $d->dest : '-',
                $d->brand ? $d->brand : '-',
                $d->so_no ? $d->so_no : '-',
                $d->buyer ? $d->buyer : '-',
                $d->color ? $d->color : '-',
                $d->size ? $d->size : '-',
                $d->qty ? $d->qty : '-',
                $d->price ? $d->price : '-',
                $d->reff_no ? $d->reff_no : '-',
                $d->styleno_prod ? $d->styleno_prod : '-'
            ];

            $sheet->writeRow($dArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        $filename = date('Y-m-d') . ' PPIC Master SO.xlsx';

        ob_end_clean();
        $excel->download($filename);

        // return Excel::download(new exportPPIC_Master_so_sb, 'Laporan_Master_SB_SO.xlsx');
    }


    public function getpo_ppic_edit_tgl(Request $request)
    {
        $tgl_skrg = date('Y-m-d');
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-30 days'));
        $user = Auth::user()->name;
        $data_po = DB::select("
select p.po isi, p.po tampil from
(select * from ppic_master_so p
where created_by = '$user' and tgl_shipment >= '$tgl_skrg_min_sebulan' ) p
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where m.ws = '" . $request->cbows_edit_tgl . "'
group by po
order by po asc
        ");

        $html = "<option value=''>Pilih No PO</option>";

        foreach ($data_po as $datapo) {
            $html .= " <option value='" . $datapo->isi . "'>" . $datapo->tampil . "</option> ";
        }

        return $html;
    }

    public function getpo_ppic_hapus(Request $request)
    {
        $tgl_skrg = date('Y-m-d');
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-30 days'));
        $user = Auth::user()->name;
        $data_po = DB::select("
select p.po isi, p.po tampil from
(select * from ppic_master_so p
where created_by = '$user' and tgl_shipment >= '$tgl_skrg_min_sebulan' ) p
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where m.ws = '" . $request->cbows_hapus . "'
group by po
order by po asc
        ");

        $html = "<option value=''>Pilih No PO</option>";

        foreach ($data_po as $datapo) {
            $html .= " <option value='" . $datapo->isi . "'>" . $datapo->tampil . "</option> ";
        }

        return $html;
    }


    public function list_master_ppic_edit(Request $request)
    {
        $user = Auth::user()->name;
        $tgl_skrg = date('Y-m-d');
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-30 days'));
        $ws = $request->ws;
        $po = $request->po;
        $data_list = DB::select("SELECT
a.id,
a.id_so_det,
m.buyer,
tgl_shipment,
concat((DATE_FORMAT(a.tgl_shipment,  '%d')), '-', left(DATE_FORMAT(a.tgl_shipment,  '%M'),3),'-',DATE_FORMAT(a.tgl_shipment,  '%Y')
) tgl_shipment_fix,
a.barcode,
m.reff_no,
a.qty_po,
a.po,
a.dest,
a.desc,
m.ws,
m.styleno,
m.color,
m.size,
sum(qty_trf) qty_trf,
sum(qty_packing_in) qty_packing_in,
sum(qty_packing_out) qty_packing_out,
m.ws,
a.created_by,
a.created_at,
pl.id id_pl
from (
select id id_ppic_master_so, qty_po , '0' qty_trf, '0' qty_packing_in, '0' qty_packing_out from ppic_master_so a
where po = '$po'
group by id, po
union
select id_ppic_master_so, '0' qty_po , sum(qty) qty_trf, '0' qty_packing_in, '0' qty_packing_out from packing_trf_garment
where po = '$po'
group by id_ppic_master_so, po
union
select id_ppic_master_so, '0' qty_po , '0' qty_trf, sum(qty) qty_packing_in, '0' qty_packing_out from packing_packing_in
where po = '$po'
group by id_ppic_master_so, po
union
select p.id id_ppic_master_so, '0' qty_po , '0' qty_trf, '0' qty_packing_in, qty_packing_out from
(
select count(barcode) qty_packing_out,po, barcode, dest from packing_packing_out_scan
where po = '$po'
group by barcode, po, dest
) a
inner join ppic_master_so p on a.barcode = p.barcode and a.po = p.po and a.dest = p.dest
group by p.id
) mut
inner join ppic_master_so a on mut.id_ppic_master_so = a.id
inner join master_sb_ws m on a.id_so_det = m.id_so_det
left join master_size_new msn on m.size = msn.size
left join (select id,barcode, po, dest from packing_master_packing_list where po = '$po' group by barcode, po, dest) pl on  a.barcode = pl.barcode and a.po = pl.po and a.dest = pl.dest
group by id_ppic_master_so
order by tgl_shipment desc, buyer asc, ws asc, dest asc, color asc, msn.urutan asc, dest asc
            ");

        return DataTables::of($data_list)->toJson();
    }

    public function edit_multiple_ppic_master_so(Request $request)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        $JmlArray                                   = $_POST['id'];
        $barcodeArray                               = $_POST['barcode'];
        $tgl_shipmentArray                          = $_POST['tgl_shipment'];
        $qty_poArray                                = $_POST['qty_po'];
        $poArray                                    = $_POST['po'];

        foreach ($JmlArray as $key => $value) {
            if ($value != '') {
                $txtid                          = $JmlArray[$key];
                $txtbarcode                     = $barcodeArray[$key];
                $txttgl_shipment                = $tgl_shipmentArray[$key];
                $qty_po                         = $qty_poArray[$key];
                $po                             = $poArray[$key]; {

                    $update =  DB::update("
            update ppic_master_so
            set
            barcode = '$txtbarcode',
            tgl_shipment = '$txttgl_shipment',
            qty_po = '$qty_po',
            po = '$po'
            where id = '$txtid'");
                }
            }

            // "callback" => "getdetail(`$no_form_modal`,`$txtket_modal`)"
        }

        $po = array_shift($poArray);
        // dd($po);
        $update_packing_trf =  DB::update("
        update packing_trf_garment a
        INNER JOIN ppic_master_so p ON a.id_ppic_master_so = p.id
        SET a.barcode = p.barcode
        where a.po = '$po'");

        $update_packing_in =  DB::update("
        update packing_packing_in a
        INNER JOIN ppic_master_so p ON a.id_ppic_master_so = p.id
        SET a.barcode = p.barcode
        where a.po = '$po'");

        $update_packing_list =  DB::update("
        update packing_master_packing_list a
        INNER JOIN ppic_master_so p ON a.id_ppic_master_so = p.id
        SET a.barcode = p.barcode
        where a.po = '$po'");

        return array(
            'status' => 201,
            'message' => 'Data  Berhasil Diupdate',
            'table' => 'datatable_edit',
            'additional' => [],
        );

        // return array(
        //     "status" => 202,
        //     "message" => 'No Form Berhasil Di Update',
        //     "additional" => [],
        //     "redirect" => '',
        //     "callback" => "getdetail(`$no_form_modal`,`$txtket_modal_input`)"

        // );
    }

    public function hapus_multiple_ppic_master_so(Request $request)
    {
        $timestamp = Carbon::now();
        $user               = Auth::user()->name;

        $JmlArray           = $_POST['cek_data'];

        if ($JmlArray != '') {
            foreach ($JmlArray as $key => $value) {
                if ($value != '') {
                    $id         = $JmlArray[$key]; {
                        $insert_log =  DB::insert("
                        INSERT INTO ppic_master_so_log (id_ppic_master_so, id_so_det, barcode, po, dest, ppic_master_so_log.desc, tgl_shipment, qty_po, created_at, updated_at, created_by, tgl_update, user_update, old_qty_po, old_tgl_shipment)
                        SELECT id, id_so_det, barcode, po, dest, ppic_master_so.desc, tgl_shipment, qty_po, created_at, updated_at, created_by, tgl_update, user_update, old_qty_po, old_tgl_shipment
                        FROM ppic_master_so where id = '$id'
                        ");
                        $del =  DB::delete("delete from ppic_master_so where id = '$id'");
                    }
                }
            }

            return array(
                "status" => 201,
                "message" => 'Data Sudah di Hapus',
                "additional" => [],
                "redirect" => '',
                "table" => 'datatable_hapus',
            );
        } else {
            return array(
                "status" => 400,
                "message" => 'Tidak ada Data',
                "additional" => [],
            );
        }
    }


    public function update_tgl_ppic_master_so(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $tgl_skrg = date('Y-m-d');
        $ws = $request->cbows_edit_tgl;
        $po = $request->cbopo_edit_tgl;
        $tgl_ubah = $request->tgl_ubah;


        $update_tgl = DB::update("
            update ppic_master_so p
            inner join master_sb_ws m on p.id_so_det = m.id_so_det
            set p.tgl_shipment = '$tgl_ubah'
            where p.created_by = '$user' and tgl_shipment >= '$tgl_skrg' and m.ws = '$ws' and po = '$po'
            ");

        if ($update_tgl) {
            return array(
                'icon' => 'benar',
                'msg' => 'Data Berhasil Ditambahkan',
            );
        } else {
            return array(
                'icon' => 'salah',
                'msg' => 'Tidak ada yang ditambahkan',
            );
        }
    }

    public function data_cek_double_tmp_ppic_so(Request $request)
    {
        $user = Auth::user()->name;
        $data_cek = DB::select("select coalesce(count(m.id_so_det),0) tot_cek from ppic_master_so_tmp tmp
        left join master_sb_ws m on tmp.ws = m.ws
        and tmp.color = m.color
        and tmp.size = m.size
        and tmp.style = m.styleno
        and tmp.dest = m.dest
				left join ppic_master_so p on m.id_so_det = p.id_so_det
       and tmp.po = p.po
			 where tmp.created_by = '" . $user . "'
			 group by tmp.po, m.id_so_det
			 having count(m.id_so_det) > '1'");
        $data_cek_fix = $data_cek ? $data_cek[0]->tot_cek : 0;
        if ($data_cek_fix == null or $data_cek_fix == '') {
            $data_cek_fix == '0';
        } else {
            $data_cek_fix = $data_cek_fix;
        }
        // dd($data_cek_fix);

        return json_encode($data_cek ? $data_cek[0] : null);
    }


    public function data_cek_avail_tmp_ppic_so(Request $request)
    {
        $user = Auth::user()->name;
        $data_cek = DB::select("select coalesce(count(m.id_so_det),0) tot_cek from ppic_master_so_tmp tmp
        left join master_sb_ws m on tmp.ws = m.ws
        and tmp.color = m.color
        and tmp.size = m.size
        and tmp.style = m.styleno
        and tmp.dest = m.dest
				left join ppic_master_so p on m.id_so_det = p.id_so_det
       and tmp.po = p.po
			 where tmp.created_by = '" . $user . "'
			 group by tmp.po, m.id_so_det
			 having count(m.id_so_det) > '1'");
        $data_cek_fix = $data_cek ? $data_cek[0]->tot_cek : 0;
        if ($data_cek_fix == null or $data_cek_fix == '') {
            $data_cek_fix == '0';
        } else {
            $data_cek_fix = $data_cek_fix;
        }
        // dd($data_cek_fix);

        return json_encode($data_cek ? $data_cek[0] : null);
    }


    public function get_ws_header_ppic(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $filter = $request->filter;
        $style = $request->style;

        $conditions = [];

        if ($filter === 'all' || $filter === 'date') {
            // If filtering by date or all, restrict WS by shipment date range
            $conditions[] = "tgl_shipment >= '$tgl_awal' AND tgl_shipment <= '$tgl_akhir'";
        }

        if ($filter === 'ws-style' && !empty($style)) {
            // If filtering by style, get WS only for that style (ignore date here or combine)
            $conditions[] = "styleno = '$style'";
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        // Get WS based on constructed where clause
        $all_ws = DB::select(
            "SELECT DISTINCT kpno isi, kpno tampil
        FROM laravel_nds.ppic_master_so p
        INNER JOIN signalbit_erp.so_det sd ON p.id_so_det = sd.id
        INNER JOIN signalbit_erp.so ON sd.id_so = so.id
        INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
        $whereClause
        ORDER BY kpno ASC"
        );

        // Selected WS is all for this filter
        $selected_ws = array_map(fn($item) => $item->isi, $all_ws);

        return response()->json([
            'all_ws' => $all_ws,
            'selected_ws' => $selected_ws
        ]);
    }



    public function get_style_header_ppic(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $filter = $request->filter;
        $ws = $request->ws;

        $conditions = [];

        if ($filter === 'all') {
            $conditions[] = "tgl_shipment >= '$tgl_awal' AND tgl_shipment <= '$tgl_akhir'";
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        // All styles
        $all_styles = DB::select(
            "SELECT DISTINCT styleno isi, styleno tampil
        FROM laravel_nds.ppic_master_so p
        INNER JOIN signalbit_erp.so_det sd ON p.id_so_det = sd.id
        INNER JOIN signalbit_erp.so ON sd.id_so = so.id
        INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
        $whereClause
        ORDER BY styleno ASC"
        );

        // Related style(s) based on selected WS
        $related_styles = [];
        if (!empty($ws)) {
            $related_styles = DB::select(
                "SELECT DISTINCT styleno
            FROM laravel_nds.ppic_master_so p
            INNER JOIN signalbit_erp.so_det sd ON p.id_so_det = sd.id
            INNER JOIN signalbit_erp.so ON sd.id_so = so.id
            INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
            WHERE kpno = '$ws'"
            );
        }

        return response()->json([
            'all_styles' => $all_styles,
            'selected_styles' => array_map(fn($item) => $item->styleno, $related_styles)
        ]);
    }

    public function get_ws_style_ppic(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $filter = $request->filter;

        $condition = "";
        if ($filter == 'all') {
            $condition = "where tgl_shipment >= '$tgl_awal' and tgl_shipment <= '$tgl_akhir'";
        } else if ($filter == 'ws-style') {
            $condition = "";
        } else if ($filter == 'date') {
            $condition = "";
        }


        $data_ws = DB::select(
            "SELECT DISTINCT ac.id isi, kpno tampil
        FROM laravel_nds.ppic_master_so p
        INNER JOIN signalbit_erp.so_det sd ON p.id_so_det = sd.id
        INNER JOIN signalbit_erp.so ON sd.id_so = so.id
        INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
        $condition
        ORDER BY kpno ASC"
        );

        $data_style = DB::select(
            "SELECT DISTINCT ac.id isi, styleno tampil
        FROM laravel_nds.ppic_master_so p
        INNER JOIN signalbit_erp.so_det sd ON p.id_so_det = sd.id
        INNER JOIN signalbit_erp.so ON sd.id_so = so.id
        INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
        $condition
        ORDER BY styleno ASC"
        );



        return response()->json([
            'data_ws' => $data_ws,
            'data_style' => $data_style
        ]);
    }



    public function export_excel_master_so_ppic(Request $request)
    {
        return Excel::download(new exportPPIC_Master_so_ppic($request->from, $request->to, $request->ws, $request->style, $request->filter), 'Laporan_Master_SB_SO.xlsx');
    }
}
