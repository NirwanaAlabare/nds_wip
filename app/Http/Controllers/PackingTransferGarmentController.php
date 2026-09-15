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
        $sumber = $request->sumber;
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $additionalQuery = '';
            $data_input = DB::select("
                SELECT *
                    FROM (
                        SELECT
                            a.no_trans,
                            CONCAT(
                                DATE_FORMAT(tgl_trans, '%d'), '-',
                                LEFT(DATE_FORMAT(tgl_trans, '%M'), 3), '-',
                                DATE_FORMAT(tgl_trans, '%Y')
                            ) AS tgl_trans_fix,
                            a.line,
                            UPPER(a.po) AS po,
                            m.ws,
                            m.color,
                            m.size,
                            m.styleno,
                            a.qty,
                            IF(a.qty - c.qty_in = 0, 'Full', '-') AS status,
                            a.id,
                            UPPER(
                                CASE
                                    WHEN a.tujuan = 'Packing' THEN 'Packing Central'
                                    ELSE a.tujuan
                                END
                            ) AS tujuan,
                            'PACKING LINE' AS sumber,
                            a.created_at,
                            a.created_by
                        FROM packing_trf_garment a
                        LEFT JOIN ppic_master_so p ON a.id_ppic_master_so = p.id
                        INNER JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                        LEFT JOIN (
                            SELECT
                                id_trf_garment,
                                SUM(qty) AS qty_in
                            FROM packing_packing_in
                            WHERE sumber = 'Sewing'
                            GROUP BY id_trf_garment
                        ) c ON a.id = c.id_trf_garment
                        WHERE tgl_trans >= '$tgl_awal'
                        AND tgl_trans <= '$tgl_akhir'

                        UNION

                        SELECT
                            a.no_trans,
                            CONCAT(
                                DATE_FORMAT(tgl_trans, '%d'), '-',
                                LEFT(DATE_FORMAT(tgl_trans, '%M'), 3), '-',
                                DATE_FORMAT(tgl_trans, '%Y')
                            ) AS tgl_trans_fix,
                            'TEMPORARY PACKING' AS line,
                            UPPER(a.po) AS po,
                            m.ws,
                            m.color,
                            m.size,
                            m.styleno,
                            a.qty,
                            IF(a.qty - c.qty_in = 0, 'Full', '-') AS status,
                            a.id,
                            'PACKING CENTRAL' AS tujuan,
                            'TEMPORARY PACKING' AS sumber,
                            a.created_at,
                            a.created_by
                        FROM packing_trf_garment_out_temporary a
                        INNER JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                        LEFT JOIN (
                            SELECT
                                packing_trf_garment_out_temporary_id,
                                SUM(qty) AS qty_in
                            FROM packing_packing_in
                            WHERE sumber = 'TEMPORARY PACKING'
                            GROUP BY packing_trf_garment_out_temporary_id
                        ) c ON a.id = c.packing_trf_garment_out_temporary_id
                        WHERE tgl_trans >= '$tgl_awal'
                        AND tgl_trans <= '$tgl_akhir'
                    ) x
                    WHERE x.sumber = '$sumber'
                    ORDER BY x.created_at DESC
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view('packing.packing_transfer_garment', ['page' => 'dashboard-packing', "subPageGroup" => "packing-transfer-garment", "subPage" => "transfer-garment"]);
    }

    public function create(Request $request)
    {
        $user = Auth::user()->name;

        $data_tujuan = DB::select("SELECT 'Packing' isi, 'Packing Central' tampil UNION ALL SELECT 'Temporary Packing' isi, 'Temporary Packing' tampil");

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
            if($request->cbo_tujuan == 'Temporary Packing'){
                $data_po = DB::select("
                    SELECT 
                         'Temporary Packing' AS isi,
                        GROUP_CONCAT(output_rfts_packing_po.so_det_id) AS so_det_id,
                        master_sb_ws.styleno,
                        master_sb_ws.styleno_prod,
                        act.close_order
                    FROM signalbit_erp.output_rfts_packing_po
                    LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = output_rfts_packing_po.so_det_id
                    LEFT JOIN signalbit_erp.act_costing act ON m.id_act_cost = act.id
                    WHERE output_rfts_packing_po.alokasi = 'temporary packing'
                    AND output_rfts_packing_po.created_by_line = '$line'
                    GROUP BY master_sb_ws.styleno, master_sb_ws.styleno_prod
                ");
            }else{
                $data_po = DB::select("
                    SELECT
                        p.po AS isi,
                        m.styleno,
                        m.styleno_prod,
                        act.close_order
                    FROM signalbit_erp.output_rfts_packing_po o
                    INNER JOIN ppic_master_so p ON o.po_id = p.id
                    INNER JOIN master_sb_ws m ON p.id_so_det = m.id_so_det
                    LEFT JOIN signalbit_erp.act_costing act ON m.id_act_cost = act.id
                    WHERE o.created_by_line = '$line'
                    AND p.tgl_shipment >= '$tgl_shipment_min_setahun'
                    GROUP BY p.po, m.styleno, m.styleno_prod
                    ORDER BY p.po ASC
                ");
            }
        } else {
            // No line filter: show all POs
            if($request->cbo_tujuan == 'Temporary Packing'){
                $data_po = DB::select("
                    SELECT 
                         'Temporary Packing' AS isi,
                        GROUP_CONCAT(output_rfts_packing_po.so_det_id) AS so_det_id,
                        master_sb_ws.styleno,
                        master_sb_ws.styleno_prod
                    FROM signalbit_erp.output_rfts_packing_po
                    LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = output_rfts_packing_po.so_det_id
                    WHERE output_rfts_packing_po.alokasi = 'temporary packing'
                    GROUP BY master_sb_ws.styleno, master_sb_ws.styleno_prod
                ");
            }else{
                $data_po = DB::select("
                    SELECT p.po isi, m.styleno, m.styleno_prod
                    FROM ppic_master_so p
                    INNER JOIN master_sb_ws m ON p.id_so_det = m.id_so_det
                    WHERE tgl_shipment >= '$tgl_shipment_min_setahun'
                    GROUP BY p.po
                    ORDER BY p.po ASC
                ");
            }
        }

        $html = "<option value=''>-- Pilih PO --</option>";
        foreach ($data_po as $datapo) {
            $styleno     = htmlspecialchars($datapo->styleno      ?? '');
            $stylenoProd = htmlspecialchars($datapo->styleno_prod ?? '');

            if ($request->cbo_tujuan == 'Temporary Packing') {
                $html .= "<option value='Temporary Packing'"
                    . " data-so-det-id='{$datapo->so_det_id}'"
                    . " data-styleno='{$styleno}'"
                    . " data-stylenoprod='{$stylenoProd}'>"
                    . "Temporary Packing"
                    . "</option>";
            } else {
                if ($datapo->close_order === 'Y') {
                    $html .= "<option value='{$datapo->isi}' disabled style='color: #dc3545; font-weight: bold;'"
                        . " data-styleno='{$styleno}'"
                        . " data-stylenoprod='{$stylenoProd}'>"
                        . "{$datapo->isi} (Close Order)"
                        . "</option>";
                } else {
                    $html .= "<option value='{$datapo->isi}'"
                        . " data-styleno='{$styleno}'"
                        . " data-stylenoprod='{$stylenoProd}'>"
                        . "{$datapo->isi}"
                        . "</option>";
                }
            }
        }

        return $html;
    }

    public function get_line_by_po(Request $request)
    {
        $po = $request->cbo_po;

        // Cari lines yang punya output untuk PO ini via output_rfts_packing_po
        if($request->cbo_tujuan == 'Temporary Packing'){
            $data_line = DB::select("
                SELECT DISTINCT 
                    o.created_by_line AS isi,
                    o.created_by_line AS tampil
                FROM signalbit_erp.output_rfts_packing_po o
                WHERE o.alokasi = 'temporary packing'
                AND o.created_by_line IS NOT NULL
                AND o.so_det_id = '$request->so_det_id'
                ORDER BY o.created_by_line ASC
            ");
        }else{
            $data_line = DB::select("
                SELECT DISTINCT o.created_by_line isi, o.created_by_line tampil
                FROM signalbit_erp.output_rfts_packing_po o
                INNER JOIN ppic_master_so p ON o.po_id = p.id
                WHERE p.po = '$po'
                AND o.created_by_line IS NOT NULL
                ORDER BY o.created_by_line ASC
            ");
        }

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
        $tujuan = $request->cbo_tujuan;
        $user = Auth::user()->name;

        if($tujuan == 'Temporary Packing'){
            $data_garment = DB::select("WITH m as (
                SELECT a.so_det_id AS id_so_det, a.created_by_line AS line, COUNT(*) AS qty_packing_line
                FROM signalbit_erp.output_rfts_packing_po a
                WHERE alokasi = 'temporary packing' and a.so_det_id IN ($request->so_det_id) and a.created_by_line = '$line'
                GROUP BY a.so_det_id, a.created_by_line
                ),
                g AS (
                    SELECT id_so_det, line, SUM(qty) AS qty_trf_gmt
                    FROM packing_trf_garment a
                    WHERE a.tujuan = 'Temporary Packing' and id_so_det IN ($request->so_det_id) and line = '$line'
                    GROUP BY id_so_det, line
                ),
                t AS (
                    SELECT id_so_det, line, SUM(qty_tmp_trf_garment) AS qty_trf_gmt
                    FROM packing_trf_garment_tmp
                    WHERE created_by = '$user' and line = '$line' AND id_ppic_master_so is null 
                    GROUP BY id_so_det, line
                ),
                c AS (
                    SELECT id_so_det, line, qty_packing_line AS qty_packing, 0 AS qty_trf_gmt FROM m
                    UNION ALL
                    SELECT id_so_det, line, 0, qty_trf_gmt FROM g
                    UNION ALL
                    SELECT id_so_det, line, 0, qty_trf_gmt FROM t
                )

                SELECT
                    0 isi,
                    c.id_so_det,
                    m.ws,
                    m.color,
                    m.size,
                    m.dest,
                    coalesce(SUM(qty_packing) - SUM(qty_trf_gmt),0) AS qty_sisa,
                    line,
                    SUM(qty_packing)            AS qty_packing,
                    SUM(qty_trf_gmt)            AS qty_trf_gmt,
                    SUM(qty_packing) - SUM(qty_trf_gmt) AS selisih
                FROM c
                left join master_sb_ws m on c.id_so_det = m.id_so_det
                group by c.id_so_det
                order by ws asc, color asc
            ");

        }else{

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
        }

        $html = "<option value=''>Pilih Garment</option>";

        foreach ($data_garment as $datagarment) {
            $selisih = $datagarment->selisih ?? 0;
            $ws      = htmlspecialchars($datagarment->ws    ?? '');
            $color   = htmlspecialchars($datagarment->color ?? '');
            $size    = htmlspecialchars($datagarment->size  ?? '');
            $dest    = htmlspecialchars($datagarment->dest  ?? '');
            $id_so_det    = htmlspecialchars($datagarment->id_so_det  ?? '');
            $qtySisa = $datagarment->qty_sisa ?? 0;
            $html .= "<option value='{$datagarment->isi}'"
                   . " data-selisih='{$selisih}'"
                   . " data-ws='{$ws}'"
                   . " data-color='{$color}'"
                   . " data-size='{$size}'"
                   . " data-dest='{$dest}'"
                   . " data-id_so_det='{$id_so_det}'"
                   . " data-qty='{$qtySisa}'>"
                   . "{$ws} / {$color} / {$size}"   // tampil ringkas, detail via template
                   . "</option>";
        }

        return $html;
    }

    public function store_tmp_trf_garment(Request $request)
    {

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

        $cbogarment = $validatedRequest['cbogarment'] == 0
            ? 'NULL'
            : "'" . $validatedRequest['cbogarment'] . "'";

        $insert_tmp = DB::insert("
            INSERT INTO packing_trf_garment_tmp
            (
                id_ppic_master_so,
                id_so_det,
                qty_tmp_trf_garment,
                line,
                created_by,
                created_at,
                updated_at
            )
            VALUES
            (
                $cbogarment,
                '" . $request->id_so_det . "',
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

            if($request->cbo_tujuan == 'Temporary Packing'){
                $data_list = DB::select("
                    select
                        a.id_tmp_trf_garment,
                        line,
                        'Temporary Packing' po,
                        qty_tmp_trf_garment,
                        m.ws,
                        m.color,
                        m.size
                    from packing_trf_garment_tmp a
                    inner join master_sb_ws m on a.id_so_det = m.id_so_det
                    where a.created_by = '$user' AND id_ppic_master_so is null
                ");
            }else{
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
            }

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
            SELECT MAX(
                CAST(SUBSTR(no_trans, 16, 3) AS UNSIGNED)
            ) AS nomor
            FROM packing_trf_garment
            WHERE YEAR(tgl_trans) = '$tahun'
            AND MONTH(tgl_trans) = '$bulan'
            AND DAY(tgl_trans) = '$tgl'
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

            if($request->cbotuj == 'Temporary Packing'){
                $insert = DB::insert(
                    "
                    insert into packing_trf_garment
                    (no_trans,tgl_trans,id_ppic_master_so,id_so_det,qty,line,po,barcode,dest,tujuan,created_by,created_at,updated_at)
                    SELECT '$kode_trans','$tgltrans',
                    a.id_ppic_master_so,
                    a.id_so_det,
                    a.qty_tmp_trf_garment,
                    a.line,
                    'Temporary Packing' AS po,
                    p.barcode,
                    p.dest,
                    '$cbotuj',
                    '$user',
                    '$timestamp',
                    '$timestamp'
                    from packing_trf_garment_tmp a
                    inner join (
                        select id_so_det, max(barcode) barcode, max(dest) dest
                        from ppic_master_so
                        group by id_so_det
                    ) p on a.id_so_det = p.id_so_det
                    where a.created_by = '$user' and a.id_ppic_master_so is null
                    "
                );
            }else{
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
            }

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

        $data_style = DB::connection('mysql')->select("
            SELECT DISTINCT
                master_sb_ws.styleno
            FROM packing_trf_garment
            LEFT JOIN master_sb_ws 
                ON master_sb_ws.id_so_det = packing_trf_garment.id_so_det
            LEFT JOIN (
                SELECT
                    po,
                    id_so_det,
                    SUM(qty) AS qty_out
                FROM packing_trf_garment_out_temporary
                GROUP BY po, id_so_det
            ) out_temp
                ON out_temp.po = packing_trf_garment.po
                AND out_temp.id_so_det = packing_trf_garment.id_so_det
            WHERE packing_trf_garment.tujuan = 'TEMPORARY PACKING'
            GROUP BY 
                master_sb_ws.styleno,
                packing_trf_garment.po,
                packing_trf_garment.id_so_det
            HAVING SUM(packing_trf_garment.qty) > COALESCE(MAX(out_temp.qty_out), 0)
            ORDER BY master_sb_ws.styleno ASC
        ");

        return view('packing.create_packing_transfer_garment_temporary', [
            'page' => 'dashboard-packing',
            "subPageGroup" => "packing-transfer-garment",
            "subPage" => "transfer-garment",
            "data_style" => $data_style,
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
            "id_so_det" => "required",
            "qty_transfer" => "required",
        ]);

        $insert_tmp = DB::insert("
            INSERT INTO packing_trf_garment_tmp_out_temporary
            (
                id_ppic_master_so,
                id_so_det,
                qty_tmp_trf_garment,
                created_by,
                created_at,
                updated_at
            )
            VALUES
            (
                NULL,
                '" . $validatedRequest['id_so_det'] . "',
                '" . $validatedRequest['qty_transfer'] . "',
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
                    'TEMPORARY PACKING' tipe,
                    m.ws,
                    m.styleno,
                    m.color,
                    m.size,
                    a.qty_tmp_trf_garment
                from packing_trf_garment_tmp_out_temporary a
                inner join master_sb_ws m on a.id_so_det = m.id_so_det
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
            SELECT MAX(CAST(SUBSTR(no_trans, 15, 3) AS UNSIGNED)) AS nomor
            FROM packing_trf_garment_out_temporary
            WHERE YEAR(tgl_trans) = '$tahun'
            AND MONTH(tgl_trans) = '$bulan'
            AND DAY(tgl_trans) = '$tgl'
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
                'TEMPORARY PACKING' AS po,
                p.barcode,
                p.dest,
                '$user',
                '$timestamp',
                '$timestamp'
                from packing_trf_garment_tmp_out_temporary a
                inner join ppic_master_so p on a.id_so_det = p.id_so_det
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
        $data_stok = DB::select("
            SELECT
                m.buyer,
                'TEMPORARY PACKING' AS po,
                m.ws,
                m.styleno,
                m.color,
                m.size,
                p.dest,
                a.stok
            FROM (
                SELECT
                    id_so_det,
                    SUM(qty_in) - SUM(qty_out) AS stok
                FROM (
                    SELECT
                        id_so_det,
                        SUM(qty) AS qty_in,
                        0 AS qty_out
                    FROM packing_trf_garment
                    WHERE tujuan = 'TEMPORARY PACKING'
                    GROUP BY id_so_det

                    UNION ALL

                    SELECT
                        id_so_det,
                        0 AS qty_in,
                        SUM(qty) AS qty_out
                    FROM packing_trf_garment_out_temporary
                    WHERE po = 'TEMPORARY PACKING'
                    GROUP BY id_so_det
                ) data_mut
                GROUP BY id_so_det
            ) a
            INNER JOIN ppic_master_so p ON a.id_so_det = p.id_so_det
            INNER JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
            LEFT JOIN master_size_new msn ON m.size = msn.size
            WHERE a.stok > 0
            ORDER BY
                m.buyer ASC,
                m.ws ASC,
                m.color ASC,
                msn.urutan ASC
        ");

        return DataTables::of($data_stok)->toJson();
    }

    public function get_stok_temporary_count()
    {
        $data = DB::selectOne("
            SELECT COALESCE(SUM(a.stok), 0) AS total_stok
            FROM (
                SELECT
                    id_so_det,
                    SUM(qty_in) - SUM(qty_out) AS stok
                FROM (
                    SELECT
                        id_so_det,
                        SUM(qty) AS qty_in,
                        0 AS qty_out
                    FROM packing_trf_garment
                    WHERE tujuan = 'TEMPORARY PACKING'
                    GROUP BY id_so_det

                    UNION ALL

                    SELECT
                        id_so_det,
                        0 AS qty_in,
                        SUM(qty) AS qty_out
                    FROM packing_trf_garment_out_temporary
                    WHERE po = 'TEMPORARY PACKING'
                    GROUP BY id_so_det
                ) data_mut
                GROUP BY id_so_det
            ) a
            WHERE a.stok > 0
        ");

        return response()->json([
            'total_stok' => (int) $data->total_stok
        ]);
    }


    public function export_excel_trf_garment(Request $request)
    {
        return Excel::download(new ExportLaporanTrfGarment($request->from, $request->to, $request->sumber), 'Laporan_Trf_Garment.xlsx');
    }

    public function get_ws_trf_garment_temporary(Request $request)
    {
        $data = DB::select("
            SELECT DISTINCT
                master_sb_ws.ws
            FROM packing_trf_garment
            LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = packing_trf_garment.id_so_det
            WHERE packing_trf_garment.tujuan = 'TEMPORARY PACKING' AND master_sb_ws.styleno = ?
        ", [$request->style]);

        return response()->json($data);
    }

    public function get_color_trf_garment_temporary(Request $request)
    {
        $data = DB::select("
            SELECT DISTINCT
                master_sb_ws.color
            FROM packing_trf_garment
            LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = packing_trf_garment.id_so_det
            WHERE packing_trf_garment.tujuan = 'TEMPORARY PACKING' AND master_sb_ws.styleno = ?
            AND master_sb_ws.ws = ?
        ", [$request->style, $request->ws]);

        return response()->json($data);
    }

    public function get_size_trf_garment_temporary(Request $request)
    {
        $data = DB::select("
            SELECT DISTINCT
                master_sb_ws.size,
                packing_trf_garment.id_so_det,
                GREATEST(
                    COALESCE(SUM(packing_trf_garment.qty), 0)

                    - COALESCE((
                        SELECT SUM(out_temp.qty)
                        FROM packing_trf_garment_out_temporary out_temp
                        WHERE out_temp.po = packing_trf_garment.po
                        AND out_temp.id_so_det = packing_trf_garment.id_so_det
                    ), 0)

                    - COALESCE((
                        SELECT SUM(tmp_out.qty_tmp_trf_garment)
                        FROM packing_trf_garment_tmp_out_temporary tmp_out
                        WHERE tmp_out.id_so_det = packing_trf_garment.id_so_det
                    ), 0),

                    0
                ) AS qty
            FROM packing_trf_garment
            LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = packing_trf_garment.id_so_det
            WHERE packing_trf_garment.tujuan = 'TEMPORARY PACKING' AND master_sb_ws.styleno = ?
            AND master_sb_ws.ws = ? AND master_sb_ws.color = ?
            GROUP BY
                master_sb_ws.size,
                packing_trf_garment.id_so_det,
                packing_trf_garment.po
            HAVING qty > 0
        ", [$request->style, $request->ws, $request->color]);

        return response()->json($data);
    }

    public function get_qty_trf_garment_temporary(Request $request)
    {
        $data = DB::selectOne("
            SELECT 
                GREATEST(
                    COALESCE(SUM(packing_trf_garment.qty), 0)

                    - COALESCE((
                        SELECT SUM(out_temp.qty)
                        FROM packing_trf_garment_out_temporary out_temp
                        WHERE out_temp.po = packing_trf_garment.po
                        AND out_temp.id_so_det = packing_trf_garment.id_so_det
                    ), 0)

                    - COALESCE((
                        SELECT SUM(tmp_out.qty_tmp_trf_garment)
                        FROM packing_trf_garment_tmp_out_temporary tmp_out
                        WHERE tmp_out.id_so_det = packing_trf_garment.id_so_det
                    ), 0),

                    0
                ) AS qty
            FROM packing_trf_garment
            LEFT JOIN master_sb_ws  ON master_sb_ws.id_so_det = packing_trf_garment.id_so_det
            WHERE packing_trf_garment.tujuan = 'TEMPORARY PACKING'
            AND master_sb_ws.styleno = ? AND master_sb_ws.ws = ? AND master_sb_ws.color = ? AND master_sb_ws.size = ?
        ", [$request->style, $request->ws, $request->color, $request->size]);

        return response()->json($data);
    }
}
