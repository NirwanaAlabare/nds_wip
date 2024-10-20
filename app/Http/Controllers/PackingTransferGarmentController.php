<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanTrfGarment;

class PackingTransferGarmentController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_input = DB::select("
                SELECT
                a.no_trans,
                concat((DATE_FORMAT(tgl_trans,  '%d')), '-', left(DATE_FORMAT(tgl_trans,  '%M'),3),'-',DATE_FORMAT(tgl_trans,  '%Y')
                ) tgl_trans_fix,
                a.line,
                a.po,
                m.ws,
                m.color,
                m.size,
                a.qty,
                if(a.qty - c.qty_in = '0','Full','-') status,
                a.id,
                a.tujuan,
                a.created_at,
                a.created_by
                from packing_trf_garment a
                inner join ppic_master_so p on a.id_ppic_master_so = p.id
                inner join master_sb_ws m on a.id_so_det = m.id_so_det
                left join
                    (
                    select id_trf_garment, sum(qty) qty_in from packing_packing_in
                    where sumber = 'Sewing'
                    group by id_trf_garment
                    ) c on a.id = c.id_trf_garment
                where tgl_trans >= '$tgl_awal' and tgl_trans <= '$tgl_akhir'
            union
                SELECT
                a.no_trans,
                concat((DATE_FORMAT(tgl_trans,  '%d')), '-', left(DATE_FORMAT(tgl_trans,  '%M'),3),'-',DATE_FORMAT(tgl_trans,  '%Y')
                ) tgl_trans_fix,
                'Temporary' line,
                a.po,
                m.ws,
                m.color,
                m.size,
                a.qty,
                if(a.qty - c.qty_in = '0','Full','-') status,
                a.id,
                'Packing' tujuan,
                a.created_at,
                a.created_by
                from packing_trf_garment_out_temporary a
                inner join ppic_master_so p on a.id_ppic_master_so = p.id
                inner join master_sb_ws m on a.id_so_det = m.id_so_det
                left join
                    (
                    select id_trf_garment, sum(qty) qty_in from packing_packing_in
                    where sumber = 'Temporary'
                    group by id_trf_garment
                    ) c on a.id = c.id_trf_garment
                where tgl_trans >= '$tgl_awal' and tgl_trans <= '$tgl_akhir'
								order by created_at desc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view('packing.packing_transfer_garment', ['page' => 'dashboard-packing', "subPageGroup" => "packing-transfer-garment", "subPage" => "transfer-garment"]);
    }

    public function create(Request $request)
    {
        $user = Auth::user()->name;

        $data_tujuan = DB::select("SELECT 'Packing' isi, 'Packing' tampil
union
SELECT 'Temporary' isi, 'Temporary' tampil");

        $data_line = DB::connection('mysql_sb')->select("SELECT username isi, username tampil from userpassword
        where groupp = 'sewing' and locked != '1' or groupp = 'sewing' and locked is null
order by isi asc");

        return view('packing.create_packing_transfer_garment', [
            'page' => 'dashboard-packing',
            "subPageGroup" => "packing-transfer-garment",
            "subPage" => "transfer-garment",
            "data_tujuan" => $data_tujuan,
            "data_line" => $data_line,
            "user" => $user
        ]);
    }

    public function get_po(Request $request)
    {
        $tgl_skrg = date('Y-m-d');
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-30 days'));
        $data_po = DB::select("SELECT
        po isi,
        CONCAT(po, ' ( ', styleno, ' ) ', '( ', a.styleno_prod , ' )') tampil
        from
        (
        select po,ws,color,size, styleno,styleno_prod from ppic_master_so p
        inner join master_sb_ws m on p.id_so_det = m.id_so_det
        group by ws, color, size, po
        ) a
        inner join
        (
                select ws,color,size,so_det_id from (
                        select so_det_id from
                        output_rfts_packing a
                        where a.created_at >= '$tgl_skrg_min_sebulan' and a.created_by = '" . $request->cbo_line . "'
                        group by so_det_id
                ) a
                        inner join master_sb_ws m on a.so_det_id = m.id_so_det
                group by so_det_id
        
        ) b on a.ws = b.ws and a.color = b.color and a.size = b.size
        group by po, styleno
        ");

        // $data_po = DB::select("SELECT
        // p.po isi,
        // CONCAT(p.po, ' ( ', p.styleno, ' ) ', '( ', p.styleno_prod , ' )') tampil
        // from
        // (
        // select ws,color,size,so_det_id from output_rfts_packing a
        // inner join master_sb_ws m on a.so_det_id = m.id_so_det
        // where a.created_by = '" . $request->cbo_line . "' and a.updated_at >= '$tgl_skrg_min_sebulan' AND a.updated_at <= '$tgl_skrg'
        // group by so_det_id
        // ) a
        // inner join (select p.*, m.styleno, m.styleno_prod, m.ws, m.color, m.size from ppic_master_so p
        // inner join master_sb_ws m on p.id_so_det = m.id_so_det where tgl_shipment >= '$tgl_skrg_min_sebulan')
        // p on a.ws = p.ws and a.color = p.color and a.size = p.size
        // group by po, p.styleno
        // ");



        $html = "<option value=''>Pilih PO</option>";

        foreach ($data_po as $datapo) {
            $html .= " <option value='" . $datapo->isi . "'>" . $datapo->tampil . "</option> ";
        }

        return $html;
    }

    public function get_garment(Request $request)
    {
        $data_garment = DB::select("SELECT
p.id isi,
concat (p.ws, ' - ',p.id_so_det,' - ', p.product_item, ' - ', p.color, ' - ', p.size, ' - ',p.dest, ' => ', coalesce(qty_sisa,0), ' PCS' ) tampil
 from
(
select p.*, m.color, m.size, m.ws, m.product_item from ppic_master_so p
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where p.po = '" . $request->cbo_po . "'
) p
left join (
select
m_trans.id_so_det,
m.color,
m.size,
m.ws,
m.dest,
coalesce(sum(qty_p_line) - sum(qty_trf_garment) - sum(qty_tmp),0) qty_sisa
from
(
select a.so_det_id id_so_det,count(so_det_id) qty_p_line, '0' qty_trf_garment, '0' qty_tmp
FROM output_rfts_packing a
where created_by = '" . $request->cbo_line . "'
group by so_det_id
union
select id_so_det, '0' qty_p_line, sum(qty) qty_trf_garment , '0' qty_tmp
from packing_trf_garment
where line = '" . $request->cbo_line . "'
group by id_so_det
union
select id_so_det, '0' qty_p_line, '0' qty_trf_garment, sum(qty_tmp_trf_garment) qty_tmp from packing_trf_garment_tmp tmp
inner join ppic_master_so p on tmp.id_ppic_master_so = p.id
where line = '" . $request->cbo_line . "'
) m_trans
inner join master_sb_ws m on m_trans.id_so_det = m.id_so_det
group by color, ws, size
) c on p.ws = c.ws and p.color = c.color and p.size = c.size
left join master_size_new msn on p.size = msn.size
order by p.ws asc, p.color asc, urutan asc
");

        // SELECT
        // p.id isi,
        // concat (p.ws, ' - ', p.color, ' - ', p.size, ' - ',p.dest, ' => ', coalesce(qty_sisa,0), ' PCS' ) tampil
        // from
        // (
        // select p.*, m.color, m.size, m.ws from ppic_master_so p
        // inner join master_sb_ws m on p.id_so_det = m.id_so_det
        // where p.po = '" . $request->cbo_po . "'
        // ) p
        // left join
        // (
        // select
        // m_trans.id_so_det,
        // m.color,
        // m.size,
        // m.ws,
        // coalesce(sum(qty_p_line) - sum(qty_trf_garment) - sum(qty_tmp),0) qty_sisa
        // from
        // (
        // select a.so_det_id id_so_det,count(so_det_id) qty_p_line, '0' qty_trf_garment, '0' qty_tmp
        // FROM output_rfts_packing a
        // where created_by = '" . $request->cbo_line . "'
        // group by so_det_id
        // union
        // select id_so_det, '0' qty_p_line, sum(qty) qty_trf_garment , '0' qty_tmp
        // from packing_trf_garment
        // where line = '" . $request->cbo_line . "'
        // group by id_so_det
        // union
        // select id_so_det, '0' qty_p_line, '0' qty_trf_garment, sum(qty_tmp_trf_garment) qty_tmp from packing_trf_garment_tmp tmp
        // inner join ppic_master_so p on tmp.id_ppic_master_so = p.id
        // where line = '" . $request->cbo_line . "'
        // ) m_trans
        // left join master_sb_ws m on m_trans.id_so_det = m.id_so_det
        // group by id_so_det
        // ) c on p.ws = c.ws and p.color = c.color and p.size = c.size
        // left join master_size_new msn on p.size = msn.size
        // order by p.ws asc, p.color asc, urutan asc


        // SELECT p.id isi,
        // concat (p.ws, ' - ', p.color, ' - ', p.size, ' - ', p.barcode, ' - ', p.dest, ' => ', qty_sisa ) tampil
        //  from
        // (
        // select p.*, m.color, m.size, m.ws from ppic_master_so p
        // inner join master_sb_ws m on p.id_so_det = m.id_so_det
        // where p.po = '" . $request->cbo_po . "'
        // ) p
        // left join (
        // select
        // m_trans.id_so_det,
        // m.color,
        // m.size,
        // p.po,
        // sum(qty_p_line) - sum(qty_trf_garment) - sum(qty_tmp) qty_sisa
        // from
        // (
        // select a.so_det_id id_so_det,count(so_det_id) qty_p_line, '0' qty_trf_garment, '0' qty_tmp
        // FROM output_rfts_packing a
        // where created_by = '" . $request->cbo_line . "'
        // group by so_det_id
        // union
        // select id_so_det, '0' qty_p_line, sum(qty) qty_trf_garment , '0' qty_tmp
        // from packing_trf_garment
        // where line = '" . $request->cbo_line . "'
        // group by id_so_det
        // union
        // select id_so_det, '0' qty_p_line, '0' qty_trf_garment, sum(qty_tmp_trf_garment) qty_tmp from packing_trf_garment_tmp tmp
        // inner join ppic_master_so p on tmp.id_ppic_master_so = p.id
        // where line = '" . $request->cbo_line . "') m_trans
        // left join master_sb_ws m on m_trans.id_so_det = m.id_so_det
        // left join ppic_master_so p on m_trans.id_so_det = p.id_so_det
        // where m_trans.id_so_det != '' and p.po = '" . $request->cbo_po . "'
        // group by m_trans.id_so_det
        // ) tg on p.color = tg.color and p.size = tg.size and p.po = tg.po
        // inner join master_size_new msn on p.size = msn.size
        // where qty_sisa != 'null'
        // group by p.id
        // order by p.color asc, msn.urutan asc


        // backup
        // SELECT
        //             p.id isi,
        //             concat (m.ws, ' - ', m.color, ' - ', m.size, ' => ', sum(qty_p_line) - sum(qty_trf_garment) -  sum(qty_tmp), ' PCS' ) tampil
        //             from
        //     (
        // select a.so_det_id id_so_det,count(so_det_id) qty_p_line, '0' qty_trf_garment, '0' qty_tmp
        // FROM output_rfts_packing a
        // where created_by = '" . $request->cbo_line . "'
        // group by so_det_id
        // union
        // select id_so_det, '0' qty_p_line, sum(qty) qty_trf_garment , '0' qty_tmp
        // from packing_trf_garment
        // where line = '" . $request->cbo_line . "'
        // group by id_so_det
        // union
        // select id_so_det, '0' qty_p_line, '0' qty_trf_garment, sum(qty_tmp_trf_garment) qty_tmp from packing_trf_garment_tmp tmp
        // inner join ppic_master_so p on tmp.id_ppic_master_so = p.id
        // where line = '" . $request->cbo_line . "'
        // group by id_so_det
        // ) a
        // left join ppic_master_so p on a.id_so_det = p.id_so_det
        // left join master_sb_ws m on p.id_so_det = m.id_so_det
        // where p.po = '" . $request->cbo_po . "'
        // group by a.id_so_det


        // SELECT p.id isi,
        // concat(m.ws, ' - ', m.color, ' - ', m.size, ' => ', count(so_det_id) - coalesce(tmp.tot_tmp,0) - coalesce(ptg.tot_in,0) , ' PCS' ) tampil
        // FROM output_rfts_packing a
        // left join ppic_master_so p on a.so_det_id = p.id_so_det
        // left join master_sb_ws m on a.so_det_id = m.id_so_det
        // left join
        //     (
        //         select sum(qty_tmp_trf_garment) tot_tmp,id_ppic_master_so from packing_trf_garment_tmp group by id_ppic_master_so
        //     ) tmp on p.id = tmp.id_ppic_master_so
        // left join
        //     (
        //         select sum(qty) tot_in,id_ppic_master_so from packing_trf_garment group by id_ppic_master_so
        //     ) ptg on p.id = ptg.id_ppic_master_so
        // where sewing_line = '" . $request->cbo_line . "' and p.po = '" . $request->cbo_po . "'
        // group by a.so_det_id, p.po, p.barcode
        // having po is not null


        $html = "<option value=''>Pilih Garment</option>";

        foreach ($data_garment as $datagarment) {
            $html .= " <option value='" . $datagarment->isi . "'>" . $datagarment->tampil . "</option> ";
        }

        return $html;
    }

    public function store_tmp_trf_garment(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $validatedRequest = $request->validate([
            "cboline" => "required",
            "cbopo" => "required",
            "cbogarment" => "required",
            "txtqty" => "required",
        ]);

        // $cek_data = DB::select("
        // select * from ppic_master_so p
        // where id = '" . $validatedRequest['cbogarment'] . "'
        // ");

        // $barcode = $cek_data[0]->barcode;
        // $id_so_det = $cek_data[0]->id_so_det;

        $insert_tmp = DB::insert("
            insert into packing_trf_garment_tmp
            (id_ppic_master_so,qty_tmp_trf_garment,line,created_by,created_at,updated_at)
            values
            (
                '" . $validatedRequest['cbogarment'] . "',
                '" . $validatedRequest['txtqty'] . "',
                '" . $validatedRequest['cboline'] . "',
                '$user',
                '$timestamp',
                '$timestamp'
            )
            ");

        if ($insert_tmp) {
            return array(
                'icon' => 'benar',
                'msg' => 'Data Produk Berhasil Ditambahkan',
            );
        } else {
            return array(
                'icon' => 'salah',
                'msg' => 'Tidak ada yang ditambahkan',
            );
        }
    }

    public function show_tmp_trf_garment(Request $request)
    {
        $user = Auth::user()->name;
        if ($request->ajax()) {

            $data_list = DB::select("
            select
            a.id_tmp_trf_garment,
            line,
            po,
            qty_tmp_trf_garment,
            m.ws,
            m.color,
            m.size
            from packing_trf_garment_tmp a
            inner join ppic_master_so b on a.id_ppic_master_so = b.id
            inner join master_sb_ws m on b.id_so_det = m.id_so_det
            where a.created_by = '$user'
            ");

            return DataTables::of($data_list)->toJson();
        }
    }

    public function hapus_tmp_trf_garment(Request $request)
    {
        $id = $request->id;

        $del_tmp =  DB::delete("
        delete from packing_trf_garment_tmp where id_tmp_trf_garment = '$id'");
    }

    public function store(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $tgltrans = date('Y-m-d');
        $cbotuj = $request->cbotuj;
        $tahun = date('Y', strtotime($tgltrans));
        $bulan = date('m', strtotime($tgltrans));
        $tgl = date('d', strtotime($tgltrans));
        $no = date('dmy', strtotime($tgltrans));
        $kode = 'SEW/OUT/';
        $cek_nomor = DB::select("
        select max(cast(SUBSTR(no_trans,16,3) as int)) nomor from packing_trf_garment where year(tgl_trans) = '" . $tahun . "'
        and month(tgl_trans) = '" . $bulan . "'
        and day(tgl_trans) = '" . $tgl . "'
        ");
        $nomor_tr = $cek_nomor[0]->nomor;
        $urutan = (int)($nomor_tr);
        $urutan++;
        $kodepay = sprintf("%01s", $urutan);

        $kode_trans = $kode . $no . '/' . $kodepay;

        $cek = DB::select("select * from packing_trf_garment_tmp where created_by = '$user'");

        $cekinput = $cek[0]->id_tmp_trf_garment;

        if ($cekinput == '') {
            return array(
                'icon' => 'salah',
                'msg' => 'Tidak ada yang disimpan',
            );
        } else {
            $insert = DB::insert(
                "
                insert into packing_trf_garment
                (no_trans,tgl_trans,id_ppic_master_so,id_so_det,qty,line,po,barcode,dest,tujuan,created_by,created_at,updated_at)
                SELECT '$kode_trans','$tgltrans',
                a.id_ppic_master_so,
                p.id_so_det,
                a.qty_tmp_trf_garment,
                a.line,
                p.po,
                p.barcode,
                p.dest,
                '$cbotuj',
                '$user',
                '$timestamp',
                '$timestamp'
                from packing_trf_garment_tmp a
                inner join ppic_master_so p on a.id_ppic_master_so = p.id
                where a.created_by = '$user'
                "
            );
            if ($insert) {
                $delete =  DB::delete(
                    "DELETE FROM packing_trf_garment_tmp where created_by = '$user'"
                );
                return array(
                    'icon' => 'benar',
                    'title' => $kode_trans,
                    'msg' => 'No Transaksi Sudah Terbuat',
                );
            }
        }
    }

    public function undo(Request $request)
    {
        $user = Auth::user()->name;

        $undo =  DB::delete(
            "DELETE FROM packing_trf_garment_tmp where created_by = '$user'"
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

    public function reset(Request $request)
    {
        $user = Auth::user()->name;

        $undo =  DB::delete(
            "DELETE FROM packing_trf_garment_tmp where created_by = '$user'"
        );
    }

    public function create_transfer_garment_temporary(Request $request)
    {
        $user = Auth::user()->name;

        $data_po = DB::select("SELECT
p.po isi,
p.po tampil
from
(
select id_ppic_master_so, sum(qty) qty_in, '0' qty_out from packing_trf_garment
where tujuan = 'Temporary'
group by id_ppic_master_so
UNION
select id_ppic_master_so,'0' qty_in,sum(qty) qty_out from packing_trf_garment_out_temporary
group by id_ppic_master_so
) data_mut
inner join ppic_master_so p on data_mut.id_ppic_master_so = p.id
group by p.po
having sum(data_mut.qty_in) - sum(data_mut.qty_out) >='1'
order by p.po asc");

        //data_po filter qty 0
        // select
        // p.po isi,
        // CONCAT(p.po, ' ( ', m.styleno, ' ) ', '( ', m.styleno_prod , ' )') tampil
        // from
        // (
        // select id_ppic_master_so, sum(qty) qty_in, '0' qty_out from packing_trf_garment
        // where tujuan = 'Temporary'
        // group by id_ppic_master_so
        // union
        // select id_ppic_master_so, '0' qty_in, sum(qty) qty_out from packing_trf_garment_out_temporary
        // group by id_ppic_master_so
        // ) a
        // inner join ppic_master_so p on a.id_ppic_master_so = p.id
        // inner join master_sb_ws m on p.id_so_det = m.id_so_det
        // group by po
        // having sum(qty_in) - sum(qty_out) >= '0'


        $data_line = DB::connection('mysql_sb')->select("SELECT username isi, username tampil from userpassword
        where groupp = 'sewing' and locked != '1' or groupp = 'sewing' and locked is null
order by isi asc");

        return view('packing.create_packing_transfer_garment_temporary', [
            'page' => 'dashboard-packing',
            "subPageGroup" => "packing-transfer-garment",
            "subPage" => "transfer-garment",
            "data_po" => $data_po,
            "data_line" => $data_line,
            "user" => $user
        ]);
    }

    public function get_garment_temporary(Request $request)
    {
        $data_garment_tmp = DB::select("SELECT
p.id isi,
concat (m.ws, ' - ', m.color, ' - ', m.size, ' => ', sum(a.qty_in) - sum(a.qty_tmp) - sum(a.qty_out), ' PCS' ) tampil
from
(
select a.id_ppic_master_so,m.id_so_det, sum(a.qty) qty_in, '0' qty_tmp, '0' qty_out
from packing_trf_garment a
inner join ppic_master_so p on a.id_ppic_master_so = p.id
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where tujuan = 'Temporary' and p.po = '" . $request->cbo_po . "'
group by a.id_ppic_master_so
union
select tmp.id_ppic_master_so,p.id_so_det, '0' qty_in, sum(qty_tmp_trf_garment) qty_tmp, '0' qty_out from packing_trf_garment_tmp_out_temporary tmp
inner join ppic_master_so p on tmp.id_ppic_master_so = p.id
where p.po = '" . $request->cbo_po . "'
group by tmp.id_ppic_master_so
union
select o.id_ppic_master_so,p.id_so_det, '0' qty_in, '0' qty_tmp, sum(qty) qty_out from packing_trf_garment_out_temporary o
inner join ppic_master_so p on o.id_ppic_master_so = p.id
where p.po = '" . $request->cbo_po . "'
group by o.id_ppic_master_so
) a
left join ppic_master_so p on a.id_ppic_master_so = p.id
left join master_sb_ws m on p.id_so_det = m.id_so_det
left join master_size_new msn on m.size = msn.size
group by a.id_ppic_master_so
having sum(a.qty_in) - sum(a.qty_tmp) - sum(a.qty_out) >= '1'
order by msn.urutan asc
        ");

        $html = "<option value=''>Pilih Garment</option>";

        foreach ($data_garment_tmp as $datagarmenttmp) {
            $html .= " <option value='" . $datagarmenttmp->isi . "'>" . $datagarmenttmp->tampil . "</option> ";
        }

        return $html;
    }

    public function store_tmp_trf_garment_temporary(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $validatedRequest = $request->validate([
            "cbopo" => "required",
            "cbogarment" => "required",
            "txtqty" => "required",
        ]);

        $insert_tmp = DB::insert("
            insert into packing_trf_garment_tmp_out_temporary
            (id_ppic_master_so,qty_tmp_trf_garment,created_by,created_at,updated_at)
            values
            (
                '" . $validatedRequest['cbogarment'] . "',
                '" . $validatedRequest['txtqty'] . "',
                '$user',
                '$timestamp',
                '$timestamp'
            )
            ");

        if ($insert_tmp) {
            return array(
                'icon' => 'benar',
                'msg' => 'Data Produk Berhasil Ditambahkan',
            );
        } else {
            return array(
                'icon' => 'salah',
                'msg' => 'Tidak ada yang ditambahkan',
            );
        }
    }

    public function show_tmp_trf_garment_temporary(Request $request)
    {
        $user = Auth::user()->name;
        if ($request->ajax()) {

            $data_list = DB::select("
            select
            a.id_tmp_trf_garment,
            po,
            qty_tmp_trf_garment,
            m.ws,
            m.color,
            m.size
            from packing_trf_garment_tmp_out_temporary a
            inner join ppic_master_so b on a.id_ppic_master_so = b.id
            inner join master_sb_ws m on b.id_so_det = m.id_so_det
            where a.created_by = '$user'
            ");

            return DataTables::of($data_list)->toJson();
        }
    }

    public function hapus_tmp_trf_garment_temporary(Request $request)
    {
        $id = $request->id;

        $del_tmp =  DB::delete("
        delete from packing_trf_garment_tmp_out_temporary where id_tmp_trf_garment = '$id'");
    }

    public function store_trf_garment_temporary(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $tgltrans = date('Y-m-d');
        $tahun = date('Y', strtotime($tgltrans));
        $bulan = date('m', strtotime($tgltrans));
        $tgl = date('d', strtotime($tgltrans));
        $no = date('dmy', strtotime($tgltrans));
        $kode = 'TMP/OUT/';
        $cek_nomor = DB::select("
        select max(cast(SUBSTR(no_trans,15,3) as int)) nomor from packing_trf_garment_out_temporary where year(tgl_trans) = '" . $tahun . "'
        and month(tgl_trans) = '" . $bulan . "'
        and day(tgl_trans) = '" . $tgl . "'
        ");
        $nomor_tr = $cek_nomor[0]->nomor;
        $urutan = (int)($nomor_tr);
        $urutan++;
        $kodepay = sprintf("%01s", $urutan);

        $kode_trans = $kode . $no . '/' . $kodepay;

        $cek = DB::select("select * from packing_trf_garment_tmp_out_temporary where created_by = '$user'");

        $cekinput = $cek[0]->id_tmp_trf_garment;

        if ($cekinput == '') {
            return array(
                'icon' => 'salah',
                'msg' => 'Tidak ada yang disimpan',
            );
        } else {
            $insert = DB::insert(
                "
                insert into packing_trf_garment_out_temporary
                (no_trans,tgl_trans,id_ppic_master_so,id_so_det,qty,po,barcode,dest,created_by,created_at,updated_at)
                SELECT '$kode_trans','$tgltrans',
                a.id_ppic_master_so,
                p.id_so_det,
                a.qty_tmp_trf_garment,
                p.po,
                p.barcode,
                p.dest,
                '$user',
                '$timestamp',
                '$timestamp'
                from packing_trf_garment_tmp_out_temporary a
                inner join ppic_master_so p on a.id_ppic_master_so = p.id
                where a.created_by = '$user'
                "
            );
            if ($insert) {
                $delete =  DB::delete(
                    "DELETE FROM packing_trf_garment_tmp_out_temporary where created_by = '$user'"
                );
                return array(
                    'icon' => 'benar',
                    'title' => $kode_trans,
                    'msg' => 'No Transaksi Sudah Terbuat',
                );
            }
        }
    }

    public function undo_trf_garment_temporary(Request $request)
    {
        $user = Auth::user()->name;

        $undo =  DB::delete(
            "DELETE FROM packing_trf_garment_tmp_out_temporary where created_by = '$user'"
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

    public function reset_trf_garment_temporary(Request $request)
    {
        $user = Auth::user()->name;

        $undo =  DB::delete(
            "DELETE FROM packing_trf_garment_tmp_out_temporary where created_by = '$user'"
        );
    }

    public function stok_temporary_transfer_garment(Request $request)
    {
        $user = Auth::user()->name;
        $data_stok = DB::select("SELECT
m.buyer,
p.po,
m.ws,
m.styleno,
m.color,
m.size,
p.dest,
a.stok
from (
select
id_ppic_master_so,
sum(qty_in) - sum(qty_out) stok
from
(
select id_ppic_master_so, sum(qty) qty_in, '0' qty_out from packing_trf_garment
where tujuan = 'Temporary'
group by id_ppic_master_so
UNION
select id_ppic_master_so,'0' qty_in,sum(qty) qty_out from packing_trf_garment_out_temporary
group by id_ppic_master_so
) data_mut
group by id_ppic_master_so
) a
inner join ppic_master_so p on a.id_ppic_master_so = p.id
inner join master_sb_ws m on p.id_so_det = m.id_so_det
left join master_size_new msn on m.size = msn.size
order by m.buyer asc, m.ws asc, p.po asc, m.color asc, msn.urutan asc

            ");

        return DataTables::of($data_stok)->toJson();
    }


    public function export_excel_trf_garment(Request $request)
    {
        return Excel::download(new ExportLaporanTrfGarment($request->from, $request->to), 'Laporan_Trf_Garment.xlsx');
    }
}
