<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\ExportLaporanRekonsiliasi;
use App\Exports\ExportLaporanCeisaDetail;
use Maatwebsite\Excel\Facades\Excel;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use DB;
use QrCode;
use PDF;

class AccountingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        return view("stock_opname.homepage", ["page" => "accounting"]);
    }

    public function UpdateData(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = "";
            $keywordQuery = "";

            if ($request->jenis_dok != 'ALL') {
                $where = " and jenis_dok = '" . $request->jenis_dok . "' ";
            }else{
                $where = "";
            }


            $dataMutlokas = DB::connection('mysql_sb')->select("select id, no_dok, tgl_update, jenis_dok, no_aju, tgl_aju, no_daftar, tgl_daftar, keterangan, status, created_by from exim_update_keterangan WHERE tgl_update BETWEEN '".$request->tgl_awal."' and '".$request->tgl_akhir."' ".$where."  order by id asc");


            return DataTables::of($dataMutlokas)->toJson();
        }

        $nows = DB::connection('mysql_sb')->select("select DISTINCT no_ws from whs_mut_lokasi_h");
        $jenisdok = DB::connection('mysql_sb')->select("select DISTINCT nama_pilihan from masterpilihan where kode_pilihan in ('JENIS_DOK_IN','JENIS_DOK_OUT') and nama_pilihan like '%BC%' order by nama_pilihan asc");

        return view("accounting.update-ceisa", ['nows' => $nows, 'jenisdok' => $jenisdok, "page" => "accounting"]);
    }

    public function create()
    {

        $no_ws = DB::connection('mysql_sb')->select("select DISTINCT no_ws kpno from whs_lokasi_inmaterial where LEFT(no_dok,2) = 'GK'
            UNION
            select DISTINCT no_ws kpno from whs_sa_fabric where qty > 0");
        $kode_gr = DB::connection('mysql_sb')->select("SELECT CONCAT(kode, '/', bulan, tahun, '/', nomor) AS kode
            FROM (
                SELECT
                'UPC/NAG' AS kode,
                DATE_FORMAT(CURRENT_DATE(), '%m') AS bulan,
                DATE_FORMAT(CURRENT_DATE(), '%y') AS tahun,
                IF(
                    MAX(no_dok) IS NULL,
                    '00001',
                    LPAD(SUBSTR(MAX(no_dok), 14, 5) + 1, 5, '0')
                    ) AS nomor
                FROM exim_update_keterangan
                WHERE MONTH(tgl_update) = MONTH(CURRENT_DATE())
                AND YEAR(tgl_update) = YEAR(CURRENT_DATE())
            ) AS a");

        $jenisdok = DB::connection('mysql_sb')->select("select DISTINCT nama_pilihan from masterpilihan where kode_pilihan in ('JENIS_DOK_IN','JENIS_DOK_OUT') and nama_pilihan like '%BC%' order by nama_pilihan asc");

        return view('accounting.create-update-ceisa', ['kode_gr' => $kode_gr,'no_ws' => $no_ws, 'jenisdok' => $jenisdok, 'page' => 'accounting']);
    }

    public function getData(Request $request)
    {
        $tgl_dari = $request->tgl_dari;
        $tgl_sampai = $request->tgl_sampai;
        $jenis_dok = $request->jenis_dok;

    // Query tanpa NOT EXISTS
        $query = "
        SELECT * FROM (
            SELECT
            CONCAT('BC ',
                TRIM(BOTH '.' FROM
                    CONCAT_WS('.',
                        SUBSTRING(kode_dokumen, 1, 1),
                        IF(CHAR_LENGTH(kode_dokumen) >= 2, SUBSTRING(kode_dokumen, 2, 1), NULL),
                        IF(CHAR_LENGTH(kode_dokumen) >= 3, SUBSTRING(kode_dokumen, 3, 1), NULL)
                        )
                    )
                ) AS jenis_dok,
            kode_dokumen,
            nomor_aju,
            SUBSTRING(nomor_aju, -6) AS no_aju,
            DATE_FORMAT(STR_TO_DATE(SUBSTRING(nomor_aju, 13, 8), '%Y%m%d'), '%Y-%m-%d') AS tanggal_aju,
            LPAD(nomor_daftar, 6, 0) AS nomor_daftar,
            tanggal_daftar
            FROM exim_header
            ) a
        WHERE a.jenis_dok = ?
        AND a.tanggal_aju BETWEEN ? AND ?
        GROUP BY no_aju

        UNION

        SELECT
        jenis_dok,
        REPLACE(REPLACE(REPLACE(jenis_dok, '.', ''), ' ', ''),'BC', '') AS kode_dokumen,
        nomor_aju,
        nomor_aju AS no_aju,
        tanggal_aju,
        nomor_daftar,
        tanggal_daftar
        FROM exim_ceisa_manual
        WHERE status != 'CANCEL'
        AND jenis_dok = ?
        AND tanggal_aju BETWEEN ? AND ?
        ";

        $data = DB::connection('mysql_sb')->select($query, [
            $jenis_dok, $tgl_dari, $tgl_sampai,
            $jenis_dok, $tgl_dari, $tgl_sampai
        ]);

    // Ambil data yang sudah ada di exim_update_keterangan
        $existing = DB::connection('mysql_sb')->table('exim_update_keterangan')
        ->select('no_aju', 'no_daftar')
        ->where('status', '!=', 'CANCEL')
        ->get()
        ->map(fn($row) => $row->no_aju . '|' . $row->no_daftar)
        ->toArray();

    // Filter di PHP agar hanya data yang belum tersimpan yang dikirim
        $filtered = collect($data)->filter(function ($row) use ($existing) {
            $key = $row->no_aju . '|' . $row->nomor_daftar;
            return !in_array($key, $existing);
        })->values();
    // dd(collect($data));

        return response()->json($filtered);
    }


    public function store(Request $request)
    {
    // Simpan input atas
        $noDok = $request->input('txt_no_dok');
        $tglUpdate = $request->input('txt_tgl_update');
        $jenisDok = $request->input('jenis_dok');

        $kode_gr = DB::connection('mysql_sb')->selectOne("
            SELECT CONCAT(kode, '/', bulan, tahun, '/', nomor) AS kode
            FROM (
                SELECT
                'UPC/NAG' AS kode,
                DATE_FORMAT(CURRENT_DATE(), '%m') AS bulan,
                DATE_FORMAT(CURRENT_DATE(), '%y') AS tahun,
                IF(
                    MAX(no_dok) IS NULL,
                    '00001',
                    LPAD(SUBSTR(MAX(no_dok), 14, 5) + 1, 5, '0')
                    ) AS nomor
                FROM exim_update_keterangan
                WHERE MONTH(tgl_update) = MONTH(CURRENT_DATE())
                AND YEAR(tgl_update) = YEAR(CURRENT_DATE())
                ) AS a
            ");

        $kodeDokumen = $kode_gr->kode;

    $rows = $request->input('rows', []); // 'rows' dikirim dari JS nanti
        // dd($rows);

    foreach ($rows as $row) {
        // Simpan hanya jika diceklis dan ada keterangan
        if (!empty($row['checked']) && !empty($row['keterangan'])) {
            DB::connection('mysql_sb')->table('exim_update_keterangan')->insert([
                'no_dok'        => $kodeDokumen,
                'tgl_update'    => $tglUpdate,
                'jenis_dok'     => $jenisDok,
                'no_aju'        => $row['no_aju'],
                'tgl_aju'       => $row['tanggal_aju'],
                'no_daftar'     => $row['no_daftar'],
                'tgl_daftar'    => $row['tanggal_daftar'],
                'keterangan'    => $row['keterangan'],
                'status'        => 'POST',
                'created_by'    => Auth::user()->name,
                'created_date'    => now(),
            ]);
        }
    }

    return array(
        "status" => 200,
        "message" => '',
        "additional" => [],
    );
}

public function CancelDataCeisa(Request $request)
{
    $timestamp = Carbon::now();
    $updateCeisa = DB::connection('mysql_sb')->table('exim_update_keterangan')->where('id', $request['txt_nodok'])->update([
        'status' => 'CANCEL',
        'cancel_by' => Auth::user()->name,
        'cancel_date' => $timestamp,
    ]);

    $massage = 'Cancel Data Successfully';

    return array(
        "status" => 200,
        "message" => $massage,
        "additional" => [],
        "redirect" => url('accounting/update-data-ceisa')
    );

}


public function EditDataCeisa(Request $request)
{
    $timestamp = Carbon::now();
    $updateCeisa = DB::connection('mysql_sb')->table('exim_update_keterangan')->where('id', $request['edit_nodok'])->update([
        'keterangan' => $request['edit_keterangan'],
    ]);

    $massage = 'Edit Data Successfully';

    return array(
        "status" => 200,
        "message" => $massage,
        "additional" => [],
        "redirect" => url('accounting/update-data-ceisa')
    );

}


public function ReportRekonsiliasi(Request $request)
{
    if ($request->ajax()) {
        $additionalQuery = "";

        if ($request->jenis_dok != 'ALL') {
            $additionalQuery .= " and kode_dokumen = '" . $request->jenis_dok . "' ";
        }

        if ($request->status != 'ALL') {
            $additionalQuery .= " and status = '" . $request->status . "' ";
        }


        $data_ceisa = DB::connection('mysql_sb')->select("select a.*, COALESCE(b.keterangan,'-') keterangan_update from (select *, CASE
        -- 1. Semua sesuai
        WHEN ROUND(qty, 2) = ROUND(COALESCE(qty_sb, 0), 2)
        AND ROUND(total_idr, 2) = ROUND(COALESCE(total_sb_idr, 0), 2)
        AND (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'SESUAI'

        -- 2. TOTAL CEISA kosong tapi data lain sesuai
        WHEN ROUND(qty, 2) = ROUND(COALESCE(qty_sb, 0), 2)
        AND ROUND(total_idr, 2) = 0 AND ROUND(COALESCE(total_sb_idr, 0), 2) > 0
        AND (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'TOTAL CEISA KOSONG'

        -- 3. Total tidak sesuai (lebih dari ±1000)
        WHEN ROUND(qty, 2) = ROUND(COALESCE(qty_sb, 0), 2)
        AND (diff_total > 1000 OR diff_total < -1000)
        AND diff_total != 0
        AND (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'TOTAL TIDAK SESUAI'

        -- 4. Total selisih pembulatan (kurang dari ±1000)
        WHEN ROUND(qty, 2) = ROUND(COALESCE(qty_sb, 0), 2)
        AND ABS(diff_total) < 1000
        AND diff_total != 0
        AND (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'SELISIH PEMBULATAN'

        -- 5. Satuan tidak sesuai tapi total & qty sama
        WHEN ROUND(qty, 2) = ROUND(COALESCE(qty_sb, 0), 2)
        AND ROUND(total_idr, 2) = ROUND(COALESCE(total_sb_idr, 0), 2)
        AND NOT (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'SATUAN TIDAK SESUAI'

        -- 6. Satuan tidak sesuai + TOTAL CEISA kosong
        WHEN ROUND(qty, 2) = ROUND(COALESCE(qty_sb, 0), 2)
        AND ROUND(total_idr, 2) = 0 AND ROUND(COALESCE(total_sb_idr, 0), 2) > 0
        AND NOT (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'SATUAN TIDAK SESUAI, TOTAL CEISA KOSONG'

        -- 7. Satuan tidak sesuai + total selisih pembulatan
        WHEN ROUND(qty, 2) = ROUND(COALESCE(qty_sb, 0), 2)
        AND ABS(diff_total) < 1000 AND diff_total != 0
        AND NOT (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'SATUAN TIDAK SESUAI, SELISIH PEMBULATAN'

        -- 8. Satuan dan total tidak sesuai (selisih besar)
        WHEN ROUND(qty, 2) = ROUND(COALESCE(qty_sb, 0), 2)
        AND ABS(diff_total) > 1000 AND diff_total != 0
        AND NOT (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'SATUAN DAN TOTAL TIDAK SESUAI'

        -- 9. QTY selisih kecil + total sama + satuan sama
        WHEN ABS(diff_qty) < 1 AND diff_qty != 0
        AND ROUND(total_idr, 2) = ROUND(COALESCE(total_sb_idr, 0), 2)
        AND (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'SELISIH PEMBULATAN'

        -- 10. QTY selisih kecil + total sama + satuan sama
        WHEN ABS(diff_qty) < 1 AND diff_qty != 0
        AND ABS(diff_total) < 1000
        AND diff_total != 0
        AND (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'SELISIH PEMBULATAN'

        -- 11. QTY tidak sesuai + total sama
        WHEN ABS(diff_qty) >= 1
        AND ROUND(total_idr, 2) = ROUND(COALESCE(total_sb_idr, 0), 2)
        AND (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'QTY TIDAK SESUAI'

        -- 11. QTY tidak sesuai + total sama
        WHEN ABS(diff_qty) >= 1
        AND ABS(diff_total) < 1000
        AND diff_total != 0
        AND (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'QTY TIDAK SESUAI'

        -- 12. QTY tidak sesuai + TOTAL CEISA kosong
        WHEN ABS(diff_qty) >= 1
        AND ROUND(total_idr, 2) = 0 AND ROUND(COALESCE(total_sb_idr, 0), 2) > 0
        AND (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'QTY TIDAK SESUAI, TOTAL CEISA KOSONG'

        -- 13. QTY tidak sesuai + total tidak sesuai
        WHEN ROUND(qty, 2) != ROUND(COALESCE(qty_sb, 0), 2)
        AND ROUND(total_idr, 2) != ROUND(COALESCE(total_sb_idr, 0), 2)
        AND (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'QTY DAN TOTAL TIDAK SESUAI'

        -- 14. QTY dan satuan tidak sesuai, total sama
        WHEN ABS(diff_qty) >= 1
        AND ROUND(total_idr, 2) = ROUND(COALESCE(total_sb_idr, 0), 2)
        AND NOT (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'QTY DAN SATUAN TIDAK SESUAI'

        -- 15. QTY, satuan, dan total tidak sesuai
        WHEN ROUND(qty, 2) != ROUND(COALESCE(qty_sb, 0), 2)
        AND NOT (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        AND ROUND(total_idr, 2) != ROUND(COALESCE(total_sb_idr, 0), 2)
        THEN 'QTY, SATUAN DAN TOTAL TIDAK SESUAI'
        END AS status_kesesuaian from  (
            select kode_dokumen, no_aju, tgl_aju, a.no_daftar, a.tgl_daftar, ROUND(qty,2) qty, ROUND(total,2) total, ROUND(total_idr,2) total_idr, ROUND(COALESCE(qty_sb,0),2) qty_sb, ROUND(COALESCE(total_sb,0),2) total_sb, ROUND(COALESCE(total_sb_idr,0),2) total_sb_idr, ROUND(ROUND(qty,2) - ROUND(COALESCE(qty_sb,0),2),2) diff_qty, ROUND(ROUND(total_idr,2) - ROUND(COALESCE(total_sb_idr,0),2),2) diff_total, if(no_bpb is null,'-',no_bpb) no_bpb, IF(jenis_dok is null,'Not Updated','Updated') status, satuan_sb, satuan_sb_total, satuan_ciesa, satuan_ciesa_tampil, satuan_ciesa_total, nama_entitas, supplier from (select * from (SELECT CONCAT('BC ',GROUP_CONCAT(SUBSTRING(kode_dokumen,n,1) ORDER BY n SEPARATOR '.')) AS kode_dokumen,nomor_aju,no_aju,tgl_aju,no_daftar,tgl_daftar,qty,satuan_ciesa, satuan_ciesa_tampil, satuan_ciesa_total,total,total_idr, nama_entitas FROM (
            select kode_dokumen,nomor_aju,no_aju,tgl_aju,no_daftar,tgl_daftar,ROUND(SUM(qty),2) AS qty,satuan_sb kode_satuan,ROUND(SUM(total),2) AS total,ROUND(SUM(total_idr),2) AS total_idr, GROUP_CONCAT(DISTINCT satuan_sb SEPARATOR ', ') AS satuan_ciesa, GROUP_CONCAT(DISTINCT kode_satuan SEPARATOR ', ') AS satuan_ciesa_tampil, GROUP_CONCAT(CONCAT(kode_satuan, ' (', round(qty,2), ')') SEPARATOR ', ') satuan_ciesa_total, nama_entitas from (select a.*, b.satuan_sb from (select a.*, b.nama_entitas from (SELECT kode_dokumen,nomor_aju,no_aju,tgl_aju,no_daftar,tgl_daftar,ROUND(SUM(jumlah_satuan),2) AS qty,kode_satuan,ROUND(SUM(cif),2) AS total,ROUND(SUM(cif_rupiah),2) AS total_idr FROM (
            SELECT a.*,kode_barang,uraian,jumlah_satuan,kode_satuan,cif,cif_rupiah FROM (
            SELECT kode_dokumen,nomor_aju,SUBSTRING(nomor_aju,-6) AS no_aju,DATE_FORMAT(STR_TO_DATE(SUBSTRING(nomor_aju,13,8),'%Y%m%d'),'%Y-%m-%d') AS tgl_aju,LPAD(nomor_daftar,6,0) AS no_daftar,tanggal_daftar AS tgl_daftar FROM exim_header
            ) a LEFT JOIN (
            select nomor_aju, kode_barang, uraian, jumlah_satuan, CASE
            WHEN fil_aju IN (25, 40, 41) THEN harga_penyerahan
            WHEN fil_aju IN (23, 27, 261, 262) THEN cif
            WHEN fil_aju IN (30) THEN (harga_satuan * jumlah_satuan)
            ELSE '0'
            END AS cif, CASE
            WHEN fil_aju IN (25, 40, 41) THEN harga_penyerahan
            WHEN fil_aju IN (23, 27, 261, 262) THEN cif_rupiah
            WHEN fil_aju IN (30) THEN ((harga_satuan * jumlah_satuan) * ndpbm)
            ELSE '0'
            END AS cif_rupiah, kode_satuan
            from (SELECT nomor_aju, LEFT(nomor_aju,6) + 0 fil_aju, kode_barang,uraian,jumlah_satuan,kode_satuan, cif,cif_rupiah,harga_satuan,ndpbm, fob, harga_penyerahan FROM exim_barang) a
            ) b ON b.nomor_aju=a.nomor_aju
            ) a GROUP BY a.no_daftar, a.nomor_aju, kode_satuan) a LEFT JOIN
            (select * from (select nomor_aju, nomor_identitas, nama_entitas, alamat_entitas from exim_entitas where seri = '8' and kode_entitas = '8' and kode_jenis_identitas = '6' and (LEFT(nomor_aju,6) + 0) IN (25,27,41,261) GROUP BY nomor_aju
            UNION
            select nomor_aju, nomor_identitas, nama_entitas, alamat_entitas from exim_entitas where seri = '9' and kode_entitas = '9' and kode_jenis_identitas = '6' and (LEFT(nomor_aju,6) + 0) IN (40,262) GROUP BY nomor_aju
            UNION
            select nomor_aju, nomor_identitas, nama_entitas, alamat_entitas from exim_entitas where (seri != '4' and kode_entitas != '4' and (kode_jenis_identitas != '6')) and (LEFT(nomor_aju,6) + 0) IN (23) and (nama_entitas != 'PT NIRWANA ALABARE GARMENT' and nama_entitas != 'NIRWANA ALABARE GARMENT') GROUP BY nomor_aju
            UNION
            select nomor_aju, nomor_identitas, nama_entitas, alamat_entitas from exim_entitas where seri = '8' and kode_entitas = '8' and kode_jenis_identitas = '' and (LEFT(nomor_aju,6) + 0) IN (30) GROUP BY nomor_aju) a) b on b.nomor_aju = a.nomor_aju) a LEFT JOIN (select satuan_ceisa, GROUP_CONCAT(satuan_sb) satuan_sb from mapping_satuan_ceisa GROUP BY satuan_ceisa) b on b.satuan_ceisa = a.kode_satuan) a GROUP BY a.no_daftar, a.nomor_aju) a JOIN ( SELECT 1 AS n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) numbers ON n<=LENGTH(a.kode_dokumen) GROUP BY no_daftar, nomor_aju ORDER BY nomor_aju asc) a
            UNION
            select jenis_dok, nomor_aju, no_aju, tanggal_aju, nomor_daftar, tanggal_daftar, sum(qty) qty, GROUP_CONCAT(DISTINCT satuan SEPARATOR ', ') AS satuan_ciesa, GROUP_CONCAT(DISTINCT satuan SEPARATOR ', ') AS satuan_ciesa_tampil, GROUP_CONCAT(CONCAT(satuan, ' (', round(qty,2), ')') SEPARATOR ', ') satuan_ciesa_total, sum(total) total, sum(total_idr) total_idr, supplier from ( select jenis_dok, nomor_aju, nomor_aju no_aju, tanggal_aju, nomor_daftar, tanggal_daftar, sum(qty) qty, satuan, sum(price) total, sum(IF(rate is null,price,price * rate)) total_idr, supplier from exim_ceisa_manual a left join (select tanggal, curr, rate from masterrate where v_codecurr = 'PAJAK' GROUP BY tanggal, curr ) cr on cr.tanggal = a.tanggal_daftar and cr.curr = a.curr INNER JOIN mastersupplier ms on ms.id_supplier = a.id_supplier where a.status != 'CANCEL' GROUP BY nomor_aju, nomor_daftar, satuan) a GROUP BY nomor_aju, nomor_daftar) a
            left join (
            select * from (select jenis_dok, nomor_aju, tanggal_aju, bcno, bcdate, GROUP_CONCAT(DISTINCT bpbno_int SEPARATOR ', ') no_bpb, GROUP_CONCAT(DISTINCT unit SEPARATOR ', ') AS satuan_sb, GROUP_CONCAT(CONCAT(unit, ' (', round(qty,2), ')') SEPARATOR ', ') satuan_sb_total, SUM(qty) qty_sb, sum(total) total_sb, sum(total * rate) total_sb_idr, supplier from (select jenis_dok, nomor_aju, tanggal_aju, bcno, bcdate, bpbno_int, IF(satuan_bc IS NULL OR satuan_bc = '',unit,satuan_bc) unit, SUM(IF(qty_bc IS NULL OR qty_bc = '' OR qty_bc = 0, qty, qty_bc)) qty, sum(IF(qty_bc IS NULL OR qty_bc = '' OR qty_bc = 0, qty, qty_bc) * coalesce(ifnull(price_bc,price),0)) total, IF(a.rate_bc is null,IF(rate is null,'1',rate),a.rate_bc) rate, supplier from bpb a INNER JOIN mastersupplier ms on ms.id_supplier = a.id_supplier left join (select tanggal, curr, rate from masterrate where v_codecurr = 'PAJAK' GROUP BY tanggal, curr ) cr on cr.tanggal = a.bcdate and cr.curr = IF(a.curr_bc IS NULL OR a.curr_bc = '',a.curr,a.curr_bc) where bcdate >= '".$request->dateFrom."' and bcdate <= '".$request->dateTo."' and (bcno is not null and bcno not in ('','-'))  GROUP BY bcno, jenis_dok, nomor_aju, IF(satuan_bc IS NULL OR satuan_bc = '',unit,satuan_bc)) a GROUP BY bcno, jenis_dok, nomor_aju
        UNION
        select jenis_dok, nomor_aju, tanggal_aju, bcno, bcdate, GROUP_CONCAT(DISTINCT bppbno_int SEPARATOR ', ') no_bpb, GROUP_CONCAT(DISTINCT unit SEPARATOR ', ') AS satuan_sb, GROUP_CONCAT(CONCAT(unit, ' (', round(qty,2), ')') SEPARATOR ', ') satuan_sb_total, SUM(qty) qty_sb, sum(total) total_sb, sum(total * rate) total_sb_idr, supplier from (select jenis_dok, nomor_aju, tanggal_aju, bcno, bcdate, bppbno_int, IF(satuan_bc IS NULL OR satuan_bc = '',unit,satuan_bc) unit, SUM(IF(qty_bc IS NULL OR qty_bc = '' OR qty_bc = 0, qty, qty_bc)) qty, sum(IF(qty_bc IS NULL OR qty_bc = '' OR qty_bc = 0, qty, qty_bc) * coalesce(ifnull(price_bc,price),0)) total, IF(a.rate_bc is null,IF(rate is null,'1',rate),a.rate_bc) rate, supplier from bppb a INNER JOIN mastersupplier ms on ms.id_supplier = a.id_supplier left join (select tanggal, curr, rate from masterrate where v_codecurr = 'PAJAK' GROUP BY tanggal, curr ) cr on cr.tanggal = a.bcdate and cr.curr = IF(a.curr_bc IS NULL OR a.curr_bc = '',a.curr,a.curr_bc) where bcdate >= '".$request->dateFrom."' and bcdate <= '".$request->dateTo."' and (bcno is not null and bcno not in ('','-'))  GROUP BY bcno, jenis_dok, nomor_aju, IF(satuan_bc IS NULL OR satuan_bc = '',unit,satuan_bc)) a GROUP BY bcno, jenis_dok, nomor_aju
        ) a GROUP BY bcno, jenis_dok, nomor_aju) b on b.nomor_aju = a.no_aju and b.bcno = a.no_daftar and b.jenis_dok = a.kode_dokumen) a where a.tgl_daftar >= '".$request->dateFrom."' and a.tgl_daftar <= '".$request->dateTo."' ".$additionalQuery.") a LEFT JOIN
            (select jenis_dok, no_aju, no_daftar, UPPER(keterangan) keterangan from exim_update_keterangan where tgl_daftar >= '".$request->dateFrom."' and tgl_daftar <= '".$request->dateTo."' and status != 'CANCEL' GROUP BY jenis_dok, no_aju, no_daftar) b on b.jenis_dok = a.kode_dokumen and b.no_aju = a.no_aju and b.no_daftar = a.no_daftar");


return DataTables::of($data_ceisa)->toJson();
}

$jenisdok = DB::connection('mysql_sb')->select("select DISTINCT nama_pilihan from masterpilihan where kode_pilihan in ('JENIS_DOK_IN','JENIS_DOK_OUT') and nama_pilihan like '%BC%' order by nama_pilihan asc");
$statusdok = DB::connection('mysql_sb')->select("select 'Updated' isi, 'UPDATED' tampil UNION select 'Not Updated' isi, 'NOT UPDATED' tampil");

return view("accounting.laporan-rekonsiliasi", ['jenisdok' => $jenisdok, 'statusdok' => $statusdok, "page" => "accounting"]);
}


public function ExportReportRekonsiliasi(Request $request)
{
    return Excel::download(new ExportLaporanRekonsiliasi($request->from, $request->to, $request->jenis_dok, $request->status), 'Laporan.xlsx');
}


public function ReportCeisaDetail(Request $request)
{
    if ($request->ajax()) {
        $additionalQuery = "";

        if ($request->jenis_dok != 'ALL') {
            $additionalQuery .= " and kode_dokumen_format = '" . $request->jenis_dok . "' ";
        }


        $data_ceisa = DB::connection('mysql_sb')->select("select * from (
            select * from (SELECT *, CASE
            WHEN LENGTH(kode_dokumen) = 3 THEN
            CONCAT('BC ',
            SUBSTRING(kode_dokumen, 1, 1), '.',
            SUBSTRING(kode_dokumen, 2, 1), '.',
            SUBSTRING(kode_dokumen, 3, 1))
            WHEN LENGTH(kode_dokumen) = 2 THEN
            CONCAT('BC ',
            SUBSTRING(kode_dokumen, 1, 1), '.',
            SUBSTRING(kode_dokumen, 2, 1))
            ELSE kode_dokumen
            END AS kode_dokumen_format FROM ( SELECT a.*,c.nama_entitas,kode_barang, uraian, qty, unit, (cif/qty) price, rates, cif, cif_rupiah FROM (SELECT no_dokumen, kode_dokumen ,nomor_aju,SUBSTRING(nomor_aju,-6) no_aju,DATE_FORMAT(STR_TO_DATE(SUBSTRING(nomor_aju,13,8),'%Y%m%d'),'%Y-%m-%d') tgl_aju,LPAD(nomor_daftar,6,0) no_daftar,tanggal_daftar tgl_daftar, created_by, created_date, IF(kode_valuta = '' OR kode_valuta is null, 'IDR', kode_valuta) curr FROM exim_header) a LEFT JOIN ( SELECT nomor_aju,kode_barang,uraian,jumlah_satuan qty,kode_satuan unit,(cif/jumlah_satuan) price, ndpbm rates, CASE
            WHEN (LEFT(nomor_aju,6) + 0) IN (25, 40, 41) THEN harga_penyerahan
            WHEN (LEFT(nomor_aju,6) + 0) IN (23, 27, 261, 262) THEN cif
            WHEN (LEFT(nomor_aju,6) + 0) IN (30) THEN (harga_satuan * jumlah_satuan)
            ELSE '0'
            END AS cif, CASE
            WHEN (LEFT(nomor_aju,6) + 0) IN (25, 40, 41) THEN harga_penyerahan
            WHEN (LEFT(nomor_aju,6) + 0) IN (23, 27, 261, 262) THEN cif_rupiah
            WHEN (LEFT(nomor_aju,6) + 0) IN (30) THEN ((harga_satuan * jumlah_satuan) * ndpbm)
            ELSE '0'
            END AS cif_rupiah FROM exim_barang) b ON b.nomor_aju=a.nomor_aju left join (select * from (select nomor_aju, nomor_identitas, nama_entitas, alamat_entitas from exim_entitas where seri = '8' and kode_entitas = '8' and kode_jenis_identitas = '6' and (LEFT(nomor_aju,6) + 0) IN (25,27,41,261) GROUP BY nomor_aju
            UNION
            select nomor_aju, nomor_identitas, nama_entitas, alamat_entitas from exim_entitas where seri = '9' and kode_entitas = '9' and kode_jenis_identitas = '6' and (LEFT(nomor_aju,6) + 0) IN (40,262) GROUP BY nomor_aju
            UNION
                    select nomor_aju, nomor_identitas, nama_entitas, alamat_entitas from exim_entitas where kode_entitas = '5' and (LEFT(nomor_aju,6) + 0) IN (23) and (nama_entitas != 'PT NIRWANA ALABARE GARMENT' and nama_entitas != 'NIRWANA ALABARE GARMENT') GROUP BY nomor_aju
            UNION
            select nomor_aju, nomor_identitas, nama_entitas, alamat_entitas from exim_entitas where seri = '8' and kode_entitas = '8' and kode_jenis_identitas = '' and (LEFT(nomor_aju,6) + 0) IN (30) GROUP BY nomor_aju) a) c on c.nomor_aju=a.nomor_aju) a) a where tgl_daftar >= '".$request->dateFrom."' and tgl_daftar <= '".$request->dateTo."'
            UNION
            select no_dok, jenis_dok, nomor_aju, nomor_aju no_aju, tanggal_aju, nomor_daftar, tanggal_daftar, created_by, created_date, supplier, '-' kode_barang, nama_item, qty, satuan, a.curr, (price / qty) price, IF(rate is null,1,rate) rate, price cif, IF(rate is null,price,price * rate) cif_rupiah, jenis_dok dok_format from exim_ceisa_manual a left join (select tanggal, curr, rate from masterrate where v_codecurr = 'PAJAK' GROUP BY tanggal, curr ) cr on cr.tanggal = a.tanggal_daftar and cr.curr = a.curr INNER JOIN mastersupplier ms on ms.id_supplier = a.id_supplier where tanggal_daftar >= '".$request->dateFrom."' and tanggal_daftar <= '".$request->dateTo."' and status != 'CANCEL') a where 1=1 ".$additionalQuery."");


        return DataTables::of($data_ceisa)->toJson();
    }

    $jenisdok = DB::connection('mysql_sb')->select("select DISTINCT nama_pilihan from masterpilihan where kode_pilihan in ('JENIS_DOK_IN','JENIS_DOK_OUT') and nama_pilihan like '%BC%' order by nama_pilihan asc");
    $statusdok = DB::connection('mysql_sb')->select("select 'Updated' isi, 'UPDATED' tampil UNION select 'Not Updated' isi, 'NOT UPDATED' tampil");

    return view("accounting.laporan-ceisa-detail", ['jenisdok' => $jenisdok, 'statusdok' => $statusdok, "page" => "accounting"]);
}


public function ExportReportCeisaDetail(Request $request)
{
    return Excel::download(new ExportLaporanCeisaDetail($request->from, $request->to, $request->jenis_dok, $request->status), 'Laporan.xlsx');
}


    public function ReportSignalbitBC(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = "";

            $data = DB::connection('mysql_sb')->select("select a.bpbno_int, a.bpbdate, jenis_dok, bcno no_daftar, bcdate, nomor_aju, tanggal_aju , a.id_item, b.itemdesc, IFNULL(NULLIF(satuan_bc, ''), unit) AS satuan, IFNULL(NULLIF(qty_bc, ''), qty) AS qty, IFNULL(NULLIF(price_bc, ''), price) AS price, (IFNULL(NULLIF(qty_bc, ''), qty) * IFNULL(NULLIF(price_bc, ''), price)) total from bpb a INNER JOIN masteritem b on b.id_item = a.id_item where bcdate BETWEEN '".$request->dateFrom."' and '".$request->dateTo."' and jenis_dok != 'INHOUSE' and a.bpbno_int not like '%FG%'
        UNION
        select a.bpbno_int, a.bpbdate, jenis_dok, bcno no_daftar, bcdate, nomor_aju, tanggal_aju , a.id_item, CONCAT(b.itemname, ' ', IFNULL(b.color,''), ' ', IFNULL(b.size,'')) itemdesc, IFNULL(NULLIF(satuan_bc, ''), a.unit) AS satuan, IFNULL(NULLIF(qty_bc, ''), a.qty) AS qty, IFNULL(NULLIF(price_bc, ''), a.price) AS price, (IFNULL(NULLIF(qty_bc, ''), a.qty) * IFNULL(NULLIF(price_bc, ''), a.price)) total from bpb a INNER JOIN masterstyle b on b.id_item = a.id_item where bcdate BETWEEN '".$request->dateFrom."' and '".$request->dateTo."' and jenis_dok != 'INHOUSE' and a.bpbno_int like '%FG%'
        UNION
        select a.bppbno_int, a.bppbdate, jenis_dok, bcno no_daftar, bcdate, nomor_aju, tanggal_aju , a.id_item, b.itemdesc, IFNULL(NULLIF(satuan_bc, ''), unit) AS satuan, IFNULL(NULLIF(qty_bc, ''), qty) AS qty, IFNULL(NULLIF(price_bc, ''), price) AS price, (IFNULL(NULLIF(qty_bc, ''), qty) * IFNULL(NULLIF(price_bc, ''), price)) total from bppb a INNER JOIN masteritem b on b.id_item = a.id_item where bcdate BETWEEN '".$request->dateFrom."' and '".$request->dateTo."' and jenis_dok != 'INHOUSE' and a.bppbno_int not like '%FG%'
        UNION
        select a.bppbno_int, a.bppbdate, jenis_dok, bcno no_daftar, bcdate, nomor_aju, tanggal_aju , a.id_item, CONCAT(b.itemname, ' ', IFNULL(b.color,''), ' ', IFNULL(b.size,'')) itemdesc, IFNULL(NULLIF(satuan_bc, ''), a.unit) AS satuan, IFNULL(NULLIF(qty_bc, ''), a.qty) AS qty, IFNULL(NULLIF(price_bc, ''), a.price) AS price, (IFNULL(NULLIF(qty_bc, ''), a.qty) * IFNULL(NULLIF(price_bc, ''), a.price)) total from bppb a INNER JOIN masterstyle b on b.id_item = a.id_item where bcdate BETWEEN '".$request->dateFrom."' and '".$request->dateTo."' and jenis_dok != 'INHOUSE' and a.bppbno_int like '%FG%'");


            return DataTables::of($data)->toJson();
        }

        return view("accounting.report-signalbit-bc", ["page" => "accounting"]);
    }


    public function ExportReportSignalbitBC(Request $request)
{
    $from = $request->from;
    $to   = $request->to;

    // ==============================
    // SQL
    // ==============================
    $sql = "select a.bpbno_int, a.bpbdate, jenis_dok, bcno no_daftar, bcdate, nomor_aju, tanggal_aju , a.id_item, b.itemdesc, IFNULL(NULLIF(satuan_bc, ''), unit) AS satuan, IFNULL(NULLIF(qty_bc, ''), qty) AS qty, IFNULL(NULLIF(price_bc, ''), price) AS price, (IFNULL(NULLIF(qty_bc, ''), qty) * IFNULL(NULLIF(price_bc, ''), price)) total from bpb a INNER JOIN masteritem b on b.id_item = a.id_item where bcdate BETWEEN '".$request->from."' and '".$request->to."' and jenis_dok != 'INHOUSE' and a.bpbno_int not like '%FG%'
        UNION
        select a.bpbno_int, a.bpbdate, jenis_dok, bcno no_daftar, bcdate, nomor_aju, tanggal_aju , a.id_item, CONCAT(b.itemname, ' ', IFNULL(b.color,''), ' ', IFNULL(b.size,'')) itemdesc, IFNULL(NULLIF(satuan_bc, ''), a.unit) AS satuan, IFNULL(NULLIF(qty_bc, ''), a.qty) AS qty, IFNULL(NULLIF(price_bc, ''), a.price) AS price, (IFNULL(NULLIF(qty_bc, ''), a.qty) * IFNULL(NULLIF(price_bc, ''), a.price)) total from bpb a INNER JOIN masterstyle b on b.id_item = a.id_item where bcdate BETWEEN '".$request->from."' and '".$request->to."' and jenis_dok != 'INHOUSE' and a.bpbno_int like '%FG%'
        UNION
        select a.bppbno_int, a.bppbdate, jenis_dok, bcno no_daftar, bcdate, nomor_aju, tanggal_aju , a.id_item, b.itemdesc, IFNULL(NULLIF(satuan_bc, ''), unit) AS satuan, IFNULL(NULLIF(qty_bc, ''), qty) AS qty, IFNULL(NULLIF(price_bc, ''), price) AS price, (IFNULL(NULLIF(qty_bc, ''), qty) * IFNULL(NULLIF(price_bc, ''), price)) total from bppb a INNER JOIN masteritem b on b.id_item = a.id_item where bcdate BETWEEN '".$request->from."' and '".$request->to."' and jenis_dok != 'INHOUSE' and a.bppbno_int not like '%FG%'
        UNION
        select a.bppbno_int, a.bppbdate, jenis_dok, bcno no_daftar, bcdate, nomor_aju, tanggal_aju , a.id_item, CONCAT(b.itemname, ' ', IFNULL(b.color,''), ' ', IFNULL(b.size,'')) itemdesc, IFNULL(NULLIF(satuan_bc, ''), a.unit) AS satuan, IFNULL(NULLIF(qty_bc, ''), a.qty) AS qty, IFNULL(NULLIF(price_bc, ''), a.price) AS price, (IFNULL(NULLIF(qty_bc, ''), a.qty) * IFNULL(NULLIF(price_bc, ''), a.price)) total from bppb a INNER JOIN masterstyle b on b.id_item = a.id_item where bcdate BETWEEN '".$request->from."' and '".$request->to."' and jenis_dok != 'INHOUSE' and a.bppbno_int like '%FG%'";

    $data = DB::connection('mysql_sb')->select($sql);

    // convert object → array
    $rows = array_map(fn($r) => (array)$r, $data);


    // ==============================
    // FastExcel – Hanya Data (NO Style)
    // ==============================
    $excel = FastExcel::create('Data');
    $sheet = $excel->getSheet();


    $sheet->writeRow(['Data BC Signalbit'])
      ->applyFontStyleBold()
      ->applyFontSize(16); 
    $sheet->writeRow(["Tanggal Daftar: {$from} s/d {$to}"])->applyFontStyleBold(); 

    $sheet->mergeCells('A1:M1');

    $sheet->writeRow(['']);

    $headers = [
    'No BPB', 'BPB Date', 'Jenis Dokumen', 'No Daftar', 'Tgl Daftar', 'No Aju', 'Tgl Aju', 'Id Item', 'Item Descriptions', 'Unit', 'Qty', 'Price', 'Total'
    ];

$sheet->writeRow($headers)
      ->applyFontStyleBold()
      ->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

// hitung panjang header
$maxLen = [];
foreach ($headers as $i => $h) {
    $maxLen[$i] = strlen($h);
}

foreach ($rows as $r) {
    $rowData = [
        $r['bpbno_int'] ?? '',
        $r['bpbdate'] ?? '',
        $r['jenis_dok'] ?? '',
        $r['no_daftar'] ?? '',
        $r['bcdate'] ?? '',
        $r['nomor_aju'] ?? '',
        $r['tanggal_aju'] ?? '',
        $r['id_item'] ?? '',
        $r['itemdesc'] ?? '',
        $r['satuan'] ?? 0,
        $r['qty'] ?? 0,
        $r['price'] ?? 0,
        $r['total'] ?? 0,
    ];

foreach ($rowData as $i => $v) {
        $len = strlen((string)$v);
        $maxLen[$i] = max($maxLen[$i] ?? 0, $len);
    }

    $sheet->writeRow($rowData)
          ->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
}

// Setelah semua row ditulis → atur width sesuai panjang isi
foreach ($maxLen as $i => $len) {
    $sheet->setColWidth($i + 1, $len + 3); // padding
}


    // DOWNLOAD
    $filename = "Data_BC_Signalbit_dari_{$from}_sd_{$to}.xlsx";
    return $excel->download($filename);
}



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

}
