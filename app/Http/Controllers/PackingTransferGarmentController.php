<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
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
                m.styleno,
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
                m.styleno,
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
        $line = $request->cbo_line;
        $tgl_shipment_min_setahun = date('Y-m-d', strtotime('-360 days'));

        if ($line) {
            // Filter by line: only POs that have output from this line
            $data_po = DB::select("
                SELECT p.po AS isi, m.styleno, m.styleno_prod
                FROM (
                    SELECT DISTINCT po_id
                    FROM signalbit_erp.output_rfts_packing_po
                    WHERE created_by_line = '$line'
                ) o
                INNER JOIN ppic_master_so p ON o.po_id = p.id
                INNER JOIN master_sb_ws m ON p.id_so_det = m.id_so_det
                WHERE tgl_shipment >= '$tgl_shipment_min_setahun'
                GROUP BY p.po
                ORDER BY p.po ASC
            ");
        } else {
            // No line filter: show all POs
            $data_po = DB::select("
                SELECT p.po isi, m.styleno, m.styleno_prod
                FROM ppic_master_so p
                INNER JOIN master_sb_ws m ON p.id_so_det = m.id_so_det
                WHERE tgl_shipment >= '$tgl_shipment_min_setahun'
                GROUP BY p.po
                ORDER BY p.po ASC
            ");
        }

        $html = "<option value=''>-- Pilih PO --</option>";
        foreach ($data_po as $datapo) {
            $styleno     = htmlspecialchars($datapo->styleno      ?? '');
            $stylenoProd = htmlspecialchars($datapo->styleno_prod ?? '');
            $html .= "<option value='{$datapo->isi}'"
                   . " data-styleno='{$styleno}'"
                   . " data-stylenoprod='{$stylenoProd}'>"
                   . "{$datapo->isi}"
                   . "</option>";
        }

        return $html;
    }

    public function get_line_by_po(Request $request)
    {
        $po = $request->cbo_po;

        // Cari lines yang punya output untuk PO ini via output_rfts_packing_po
        $data_line = DB::select("
            SELECT DISTINCT o.created_by_line isi, o.created_by_line tampil
            FROM signalbit_erp.output_rfts_packing_po o
            INNER JOIN ppic_master_so p ON o.po_id = p.id
            WHERE p.po = '$po'
            AND o.created_by_line IS NOT NULL
            ORDER BY o.created_by_line ASC
        ");

        $html = '<option value="">-- Pilih Line --</option>';
        foreach ($data_line as $d) {
            $html .= "<option value='{$d->isi}'>{$d->tampil}</option>";
        }

        return $html;
    }

    public function get_garment(Request $request)
    {
        $po = $request->cbo_po;
        $line = $request->cbo_line;
        $user = Auth::user()->name;

        $data_garment = DB::select("WITH m as (
SELECT a.po_id, a.created_by_line AS line, COUNT(*) AS qty_packing_line
FROM signalbit_erp.output_rfts_packing_po a
INNER JOIN laravel_nds.ppic_master_so p ON a.po_id = p.id
WHERE po = '$po' and a.created_by_line = '$line'
GROUP BY a.po_id
),
g AS (
    SELECT id_ppic_master_so, line, SUM(qty) AS qty_trf_gmt
    FROM packing_trf_garment a
		INNER JOIN ppic_master_so p on a.id_ppic_master_so = p.id
		WHERE a.po = '$po' and line = '$line'
    GROUP BY id_ppic_master_so, line
),
t AS (
    SELECT id_ppic_master_so, line, SUM(qty_tmp_trf_garment) AS qty_trf_gmt
    FROM packing_trf_garment_tmp
    WHERE created_by = '$user' and line = '$line'
    GROUP BY id_ppic_master_so, line
),
c AS (
    SELECT po_id as id_ppic_master_so, line, qty_packing_line AS qty_packing, 0 AS qty_trf_gmt FROM m
    UNION ALL
    SELECT id_ppic_master_so, line, 0, qty_trf_gmt FROM g
    UNION ALL
    SELECT id_ppic_master_so, line, 0, qty_trf_gmt FROM t
)

SELECT
id_ppic_master_so isi,
m.ws,
m.color,
m.size,
p.dest,
coalesce(SUM(qty_packing) - SUM(qty_trf_gmt),0) AS qty_sisa,
line,
SUM(qty_packing)            AS qty_packing,
SUM(qty_trf_gmt)            AS qty_trf_gmt,
SUM(qty_packing) - SUM(qty_trf_gmt) AS selisih
FROM c
left join ppic_master_so p on c.id_ppic_master_so = p.id
left join master_sb_ws m on p.id_so_det = m.id_so_det
left join master_size_new msn on m.size = msn.size
group by id_ppic_master_so
order by ws asc, color asc, urutan asc

");

        $html = "<option value=''>Pilih Garment</option>";

        foreach ($data_garment as $datagarment) {
            $selisih = $datagarment->selisih ?? 0;
            $ws      = htmlspecialchars($datagarment->ws    ?? '');
            $color   = htmlspecialchars($datagarment->color ?? '');
            $size    = htmlspecialchars($datagarment->size  ?? '');
            $dest    = htmlspecialchars($datagarment->dest  ?? '');
            $qtySisa = $datagarment->qty_sisa ?? 0;
            $html .= "<option value='{$datagarment->isi}'"
                   . " data-selisih='{$selisih}'"
                   . " data-ws='{$ws}'"
                   . " data-color='{$color}'"
                   . " data-size='{$size}'"
                   . " data-dest='{$dest}'"
                   . " data-qty='{$qtySisa}'>"
                   . "{$ws} / {$color} / {$size}"   // tampil ringkas, detail via template
                   . "</option>";
        }

        return $html;
    }

    public function store_tmp_trf_garment(Request $request)
    {
        
        // Check Closing
        $dataCheckClosing = DB::connection('mysql_sb')->table('output_rfts_packing_po')
            ->where('po_id', $request->cbogarment)
            ->orderBy('updated_at', 'desc')
            ->first();

        if (checkClosingDate(date('Y-m-d', strtotime($dataCheckClosing->updated_at)))) {
            return array(
                "status" => 400,
                "icon" => "salah",
                "msg" => "Data tidak dapat disimpan karena periode sudah ditutup.",
                "additional" => "Closing",
            );
        }

        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $validatedRequest = $request->validate([
            "cboline"   => "required",
            "cbopo"     => "required",
            "cbogarment"=> "required",
            "txtqty"    => "required|numeric|min:1",
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
