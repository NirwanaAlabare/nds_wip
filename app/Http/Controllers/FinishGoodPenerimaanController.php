<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanFGINList;
use App\Exports\ExportLaporanFGINSummary;

class FinishGoodPenerimaanController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $tgl_skrg = date('Y-m-d');
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-90 days'));
        $user = Auth::user()->name;
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_input = DB::select("SELECT
no_sb,
tgl_penerimaan,
concat((DATE_FORMAT(a.tgl_penerimaan,  '%d')), '-', left(DATE_FORMAT(a.tgl_penerimaan,  '%M'),3),'-',DATE_FORMAT(a.tgl_penerimaan,  '%Y')
            ) tgl_penerimaan_fix,
a.po,
a.barcode,
buyer,
ws,
color,
size,
a.qty,
m.dest,
a.no_carton,
a.notes,
a.created_at,
a.created_by
from fg_fg_in a
inner join ppic_master_so p on a.id_ppic_master_so = p.id
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where tgl_penerimaan >= '$tgl_awal' and tgl_penerimaan <= '$tgl_akhir' and a.status = 'NORMAL'
order by a.created_at desc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view(
            'finish_good.finish_good_penerimaan',
            [
                'page' => 'dashboard_finish_good',
                "subPageGroup" => "finish_good_penerimaan",
                "subPage" => "finish_good_penerimaan",
            ]
        );
    }

    public function search_po(Request $request)
    {
        $term = $request->q ?? '';
        $data = DB::select("
            SELECT
                concat(a.po,'_',a.dest) AS id,
                concat(a.po, ' - ', a.dest, ' - ', m.buyer) AS text,
                act_costing.close_order
            FROM ppic_master_so a
            INNER JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
            LEFT JOIN signalbit_erp.act_costing ON m.id_act_cost = signalbit_erp.act_costing.id
            WHERE a.po IS NOT NULL
                AND a.tgl_shipment >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
                AND (a.po LIKE ? OR m.buyer LIKE ?)
            GROUP BY a.po, a.dest, m.buyer
            ORDER BY a.po ASC
            LIMIT 50
        ", ["%{$term}%", "%{$term}%"]);

        $results = collect($data)->map(function ($row) {
            $row->disabled = $row->close_order === 'Y';
            return $row;
        });

        return response()->json(['results' => $data]);
    }

    public function fg_in_getno_carton(Request $request)
    // SELECT
    // concat(a.no_carton,'_',a.notes)  isi,
    // concat(a.no_carton, ' ( ', coalesce(sum(b.total),0) - coalesce(sum(c.qty_fg),0), ' ) ', a.notes) tampil
    // from
    // (select id,po, no_carton, notes, qty_isi from packing_master_carton where po = '" . $request->cbopo . "') a
    // left join (
    // select count(barcode) total, po, barcode, dest, no_carton, notes from packing_packing_out_scan
    // where po = '" . $request->cbopo . "'
    // group by no_carton, po, barcode, dest
    // ) b on a.po = b.po and a.no_carton = b.no_carton and a.notes = b.notes
    // left join (
    // select sum(qty) qty_fg,po, barcode, no_carton, notes from fg_fg_in where po = '" . $request->cbopo . "' and status = 'NORMAL' group by barcode, po, no_carton, notes ) c
    // on a.po = c.po and a.no_carton = c.no_carton and a.notes = c.notes and b.barcode = c.barcode
    // where
    // (
    // case
    // when a.qty_isi is null then coalesce(b.total,0) - coalesce(c.qty_fg,0) >= '1'
    // when a.qty_isi = b.total then a.qty_isi - coalesce(c.qty_fg,0) != '0'
    // end
    // )
    // group by a.no_carton
    // order by a.no_carton asc


    // NEW
    // SELECT
    // concat(a.no_carton,'_',a.notes)  isi,
    // concat(a.no_carton, ' ( ', coalesce(sum(b.total),0) - coalesce(sum(c.qty_fg),0), ' ) ', a.notes) tampil
    //  from
    // (select id,po, no_carton, notes, qty_isi from packing_master_carton where po = '" . $request->cbopo . "') a
    // left join (
    // select count(barcode) total, po, barcode, dest, no_carton, notes from packing_packing_out_scan
    // where po = '" . $request->cbopo . "'
    // group by no_carton, po
    // ) b on a.po = b.po and a.no_carton = b.no_carton and a.notes = b.notes
    // left join (
    // select sum(qty) qty_fg,po, barcode, no_carton, notes from fg_fg_in where po = '" . $request->cbopo . "' and status = 'NORMAL' group by barcode, po, no_carton, notes ) c
    // on a.po = c.po and a.no_carton = c.no_carton and a.notes = c.notes and b.barcode = c.barcode
    // where
    // (
    //      case
    //      when a.qty_isi is null then coalesce(b.total,0) - coalesce(c.qty_fg,0) >= '1'
    //      when a.qty_isi = b.total then coalesce(b.total,0) - coalesce(c.qty_fg,0) >= '1'
    //      end
    //     )
    //      group by a.no_carton
    //      order by a.no_carton asc

    //     SELECT
    //     concat(no_carton,'_',notes)  isi,
    //     concat(no_carton, ' ( ', coalesce(sum(tot_scan),0) - coalesce(sum(qty_fg),0), ' ) ', notes) tampil
    //     from (
    //     select
    //     a.barcode, a.po, a.notes, a.no_carton,
    //     m.qty_isi,
    //     e.tot_isi,
    //     sum(tot_scan) tot_scan,
    //     sum(qty_fg) qty_fg
    //     from (
    //     select barcode, po, notes, no_carton, count(barcode)tot_scan, '0' qty_fg
    //     from packing_packing_out_scan where po = '" . $request->cbopo . "'
    //     group by barcode, po, notes, no_carton
    //     union
    //     select barcode, po, notes, no_carton,'0' tot_scan,sum(qty)qty_fg from fg_fg_in
    //     where po = '" . $request->cbopo . "' and status = 'NORMAL'
    //     group by barcode, po, notes, no_carton
    //     ) a
    //     left join (select * from packing_master_carton where po = '" . $request->cbopo . "') m
    //     on a.po = m.po and a.no_carton = m.no_carton and a.notes = m.notes
    //     left join (
    //     select count(barcode)tot_isi, po, notes, no_carton
    //     from packing_packing_out_scan where po = '" . $request->cbopo . "'
    //     group by po, notes, no_carton
    //     ) e
    //     on m.po = e.po and m.no_carton = e.no_carton and m.notes = e.notes
    //     group by barcode, po, notes, no_carton
    //     ) d
    //     where
    //     (
    //          case
    //          when d.qty_isi is null then coalesce(d.tot_scan,0) - coalesce(d.qty_fg,0) >= '1'
    //          when d.qty_isi = d.tot_isi then coalesce(d.tot_scan,0) - coalesce(d.qty_fg,0) >= '1'
    //          end
    //         )
    //             group by po, no_carton
    //  order by no_carton asc


    {
        $po_data_arr = $request->cbopo ? $request->cbopo : null;
        if ($po_data_arr) {
            $cekArray = explode('_', $po_data_arr);
            // Use null coalescing operator to safely assign values
            $po = isset($cekArray[0]) ? $cekArray[0] : null;
            $dest = isset($cekArray[1]) ? $cekArray[1] : null;
        } else {
            // Handle the case where $po_data_arr is null
            $po = null; // or set a default value
            $dest = null; // or set a default value
        }

        $data_no_carton = DB::select("WITH
pl as (
select * from packing_master_packing_list where  po = '$po' and dest = '$dest'
),
a as (
select id_ppic, no_carton, count(*)tot_scan from packing_packing_out_scan a
where po = '$po' and dest = '$dest'
group by id_ppic, no_carton
),
b as (
select id_ppic_master_so, sum(qty)tot_fg, no_carton from fg_fg_in
where po = '$po' and dest = '$dest' and status = 'NORMAL'
GROUP BY id_ppic_master_so, no_carton
)

select
pl.no_carton isi,
concat(pl.no_carton,' (', sum(pl.qty), ') ') tampil
from pl
left join a on pl.id_ppic_master_so = a.id_ppic and pl.no_carton = a.no_carton
left join b on pl.id_ppic_master_so = b.id_ppic_master_so and pl.no_carton = b.no_carton
group by pl.no_carton
having coalesce(sum(qty),0) = coalesce(sum(tot_scan),0) and coalesce(sum(tot_scan),0) - coalesce(sum(tot_fg),0) != '0'
        ");


        //         SELECT
        // a.no_carton isi,
        // concat(a.no_carton,' (', sum(a.qty), ') ') tampil
        // FROM
        // (
        // select * from packing_master_packing_list where  po = '$po' and dest = '$dest'
        // ) a
        // left join
        // (
        // select po, barcode, dest, no_carton, count(barcode)tot_scan from packing_packing_out_scan a
        // where po = '$po' and dest = '$dest'
        // group by po , barcode, dest, no_carton
        // ) b on a.po = b.po and a.barcode = b.barcode and a.dest = b.dest and a.no_carton = b.no_carton
        // left join
        // (
        // select po, barcode, dest, no_carton, sum(qty)tot_fg from fg_fg_in
        // where po = '$po' and dest = '$dest' and status = 'NORMAL'
        // GROUP BY po, barcode, dest, no_carton
        // ) c on a.po = c.po and a.barcode = c.barcode and a.dest = c.dest and a.no_carton = c.no_carton
        // group by a.no_carton
        // having coalesce(sum(qty),0) = coalesce(sum(tot_scan),0) and coalesce(sum(tot_scan),0) - coalesce(sum(tot_fg),0) != '0'


        // where coalesce(b.total,0) - coalesce(c.qty_fg,0) >= '1'

        $html = "<option value=''>Pilih No. Carton</option>";

        foreach ($data_no_carton as $datanocarton) {
            $html .= " <option value='" . $datanocarton->isi . "'>" . $datanocarton->tampil . "</option> ";
        }

        return $html;
    }

    public function show_preview_fg_in(Request $request)
    {
        $user = Auth::user()->name;

        $po_data_arr = $request->cbopo ? $request->cbopo : null;

        if ($po_data_arr) {
            $cekArray = explode('_', $po_data_arr);
            // Use null coalescing operator to safely assign values
            $po = isset($cekArray[0]) ? $cekArray[0] : null;
            $dest = isset($cekArray[1]) ? $cekArray[1] : null;
        } else {
            // Handle the case where $po_data_arr is null
            $po = null; // or set a default value
            $dest = null; // or set a default value
        }


        if ($request->ajax()) {

            $data_preview = $this->getPreviewData($po, $dest);

            // Paging & filter dikerjakan di browser (client-side), jadi kirim semua data sekali saja
            return response()->json(['data' => $data_preview]);
        }
    }

    // Data karton yang sudah full scan tapi belum full FG IN.
    // Dipakai untuk preview dan dipanggil ulang saat simpan, supaya data yang disimpan selalu data terbaru.
    // Patokan scan sama dengan halaman Packing List: dihitung per barcode + no_carton.
    private function getPreviewData($po, $dest)
    {
        return DB::select("WITH pl AS (
    SELECT * FROM packing_master_packing_list
    WHERE po = ? AND dest = ?
),
a AS (
    SELECT barcode, no_carton, COUNT(*) AS tot_scan
    FROM packing_packing_out_scan
    WHERE po = ? AND dest = ?
    GROUP BY barcode, no_carton
),
b AS (
    SELECT barcode, no_carton, SUM(qty) AS tot_fg
    FROM fg_fg_in
    WHERE po = ? AND dest = ? AND status = 'NORMAL'
    GROUP BY barcode, no_carton
),
-- Karton full = semua baris packing list-nya qty = qty scan (status 'Pass' di halaman Packing List)
c AS (
    SELECT STRAIGHT_JOIN
        pl.no_carton,
        MIN(pl.qty = COALESCE(a.tot_scan, 0))  AS carton_full,
        SUM(pl.qty)                            AS carton_qty,
        SUM(COALESCE(a.tot_scan, 0))           AS carton_scan
    FROM pl
    LEFT JOIN a ON pl.barcode = a.barcode AND pl.no_carton = a.no_carton
    GROUP BY pl.no_carton
)
-- STRAIGHT_JOIN: paksa mulai dari pl (index po,dest). Tanpa ini optimizer mulai dari a lalu cari
-- packing list lewat index no_carton saja (semua PO), query jadi belasan detik.
SELECT STRAIGHT_JOIN
    pl.id_so_det,
    pl.no_carton,
    pl.barcode,
    pl.po,
    pl.dest,
    m.color,
    m.size,
    m.ws,
    pl.qty - COALESCE(b.tot_fg, 0)              AS qty,      -- qty sisa, supaya yang sudah FG IN sebagian tidak masuk dobel
    a.tot_scan,
    COALESCE(b.tot_fg, 0)                       AS tot_fg,
    a.tot_scan - COALESCE(b.tot_fg, 0)          AS selisih,
    'PCS'                                        AS unit,
    m.dest,
    m.price,
    m.curr,
    pl.id_ppic_master_so,
    c.carton_full,
    c.carton_qty,
    c.carton_scan
FROM pl
LEFT JOIN a  ON pl.barcode = a.barcode
             AND pl.no_carton = a.no_carton
LEFT JOIN b  ON pl.barcode = b.barcode
             AND pl.no_carton = b.no_carton
INNER JOIN c ON pl.no_carton = c.no_carton
INNER JOIN ppic_master_so p  ON pl.id_ppic_master_so = p.id
INNER JOIN master_sb_ws m    ON pl.id_so_det = m.id_so_det
WHERE pl.qty = a.tot_scan                        -- baris sudah full scan (karton belum tentu, lihat carton_full)
  AND COALESCE(b.tot_fg, 0) < a.tot_scan        -- tapi fg belum full
GROUP BY pl.no_carton, m.id_so_det
ORDER BY pl.no_carton
        ", [$po, $dest, $po, $dest, $po, $dest]);

            // SELECT
            // a.id_so_det,
            // a.no_carton,
            // a.barcode,
            // a.po,
            // a.dest,
            // m.color,
            // m.size,
            // m.ws,
            // coalesce(b.tot_scan,0) - coalesce(tot_fg,0) qty,
            // 'PCS' unit,
            // m.dest,
            // price,
            // m.curr,
            // a.id_ppic_master_so
            // FROM
            // (
            // select * from packing_master_packing_list where  po = '$po' and dest = '$dest' and no_carton = '$no_carton'
            // ) a
            // left join
            // (
            // select po, barcode, dest, no_carton, count(barcode)tot_scan from packing_packing_out_scan a
            // where po = '$po' and dest = '$dest' and no_carton = '$no_carton'
            // group by po , barcode, dest, no_carton
            // ) b on a.po = b.po and a.barcode = b.barcode and a.dest = b.dest and a.no_carton = b.no_carton
            // left join
            // (
            // select po, barcode, dest, no_carton, sum(qty)tot_fg from fg_fg_in where po = '$po' and dest = '$dest' and no_carton = '$no_carton' and status = 'NORMAL'
            // group by po , barcode, dest, no_carton
            // ) c on a.po = c.po and a.barcode = c.barcode and a.dest = c.dest and a.no_carton = c.no_carton
            // inner join master_sb_ws m on a.id_so_det = m.id_so_det
            // group by a.id_so_det
            // having coalesce(sum(a.qty),0) = coalesce(sum(tot_scan),0) and coalesce(sum(tot_scan),0) - coalesce(sum(tot_fg),0) != '0'
    }

    // Dipanggil sebelum swal konfirmasi: bandingkan karton yang dicentang dengan data FG IN terbaru
    public function check_fg_in(Request $request)
    {
        $poArray = explode('_', $request->cbopo ?? '');
        $po = $poArray[0];
        $dest = $poArray[1] ?? null;

        [$rows, $skipped] = $this->resolveSelected($po, $dest, $this->selectedKeys($request));

        return response()->json($this->summarizeRows($rows, $skipped));
    }

    public function store(Request $request)
    {
        $poArray = explode('_', $_POST['cbopo']);
        $po = $poArray[0];
        $dest = $poArray[1];

        // Kunci per PO: simpan bersamaan (user lain / klik ganda) harus antri,
        // jadi pengecekan FG IN di bawah selalu melihat data yang sudah tersimpan sebelumnya
        $lockName = 'fg_in_' . md5($po);
        $lock = DB::selectOne("SELECT GET_LOCK(?, 30) AS acquired", [$lockName]);

        if (! $lock || $lock->acquired != 1) {
            return array(
                "status" => 400,
                "message" => 'PO ini sedang disimpan oleh proses lain, silakan coba lagi.',
            );
        }

        try {
            // Browser hanya mengirim key baris yang dicentang (no_carton__id_so_det) dari semua halaman,
            // qty/harga diambil ulang dari database supaya karton yang sudah FG IN tidak tersimpan dobel
            [$rows, $skipped] = $this->resolveSelected($po, $dest, $this->selectedKeys($request));

            if ($rows->isEmpty()) {
                return array_merge([
                    "status" => 200,
                    "message" => 'Tidak ada data yang disimpan',
                ], $this->summarizeRows($rows, $skipped));
            }

            $bpbno_int = $this->saveFgIn($po, $dest, $rows);

            return array_merge([
                "status" => 201,
                "message" => 'No Transaksi : ' . $bpbno_int . ' Sudah Terbuat',
                "no_transaksi" => $bpbno_int,
            ], $this->summarizeRows($rows, $skipped));
        } finally {
            DB::select("SELECT RELEASE_LOCK(?)", [$lockName]);
        }
    }

    private function selectedKeys(Request $request)
    {
        $keys = json_decode($request->input('selected_keys', '[]'), true);

        return is_array($keys) ? array_map('strval', $keys) : [];
    }

    // Cocokkan key yang dicentang dengan data terbaru. Yang lolos akan disimpan,
    // sisanya dilewati per karton beserta alasannya (sudah FG IN / karton belum full / data berubah)
    private function resolveSelected($po, $dest, array $selectedKeys)
    {
        $selected = array_flip($selectedKeys);
        $preview  = collect($this->getPreviewData($po, $dest));

        // Karton yang belum full (patokan Packing List) tidak boleh disimpan walau ikut terkirim
        $rows = $preview->filter(function ($row) use ($selected) {
            return $row->qty > 0
                && $row->carton_full == 1
                && isset($selected[$row->no_carton . '__' . $row->id_so_det]);
        })->values();

        $validKeys = $rows->map(fn ($row) => $row->no_carton . '__' . $row->id_so_det)->flip();
        $skippedCartons = collect($selectedKeys)
            ->reject(fn ($key) => isset($validKeys[$key]))
            ->map(fn ($key) => substr($key, 0, strrpos($key, '__')))
            ->unique()
            ->values();

        if ($skippedCartons->isEmpty()) {
            return [$rows, []];
        }

        $fgIn = DB::table('fg_fg_in')
            ->selectRaw('no_carton, GROUP_CONCAT(DISTINCT no_sb) AS no_sb, MAX(tgl_penerimaan) AS tgl_penerimaan, GROUP_CONCAT(DISTINCT created_by) AS created_by, SUM(qty) AS qty')
            ->where('po', $po)
            ->where('dest', $dest)
            ->where('status', 'NORMAL')
            ->whereIn('no_carton', $skippedCartons->all())
            ->groupBy('no_carton')
            ->get()
            ->keyBy('no_carton');
        $previewCartons = $preview->keyBy('no_carton');
        $validCartons   = $rows->keyBy('no_carton');

        $skipped = $skippedCartons->map(function ($noCarton) use ($fgIn, $previewCartons, $validCartons) {
            $fg = $fgIn->get($noCarton);
            $previewRow = $previewCartons->get($noCarton);

            if ($fg) {
                return [
                    'no_carton' => $noCarton,
                    'reason'    => $validCartons->has($noCarton) ? 'Sebagian sudah FG IN' : 'Sudah FG IN',
                    'no_sb'     => $fg->no_sb,
                    'tgl'       => $fg->tgl_penerimaan,
                    'user'      => $fg->created_by,
                    'qty'       => (int) $fg->qty,
                ];
            }

            return [
                'no_carton' => $noCarton,
                'reason'    => $previewRow && $previewRow->carton_full != 1 ? 'Karton belum full' : 'Data karton berubah',
            ];
        })->values()->all();

        return [$rows, $skipped];
    }

    // Ringkasan untuk swal konfirmasi & hasil simpan
    private function summarizeRows($rows, $skipped)
    {
        // Urutan size sama dengan halaman Packing List (master_size_new.urutan), size yang tidak terdaftar di akhir
        $urutan = DB::table('master_size_new')->pluck('urutan', 'size');
        $sizeOrder = fn ($size) => $urutan[$size] ?? PHP_INT_MAX;

        return [
            'total_carton' => $rows->pluck('no_carton')->unique()->count(),
            'total_qty'    => (int) $rows->sum('qty'),
            'sizes'        => $rows->groupBy('size')->map(fn ($items, $size) => [
                'size' => $size,
                'qty'  => (int) $items->sum('qty'),
            ])->sortBy(fn ($item) => $sizeOrder($item['size']))->values(),
            'cartons'      => $rows->groupBy('no_carton')->map(fn ($items, $noCarton) => [
                'no_carton' => $noCarton,
                'qty'       => (int) $items->sum('qty'),
                'sizes'     => $items->sortBy(fn ($row) => $sizeOrder($row->size))
                    ->map(fn ($row) => ['size' => $row->size, 'qty' => (int) $row->qty])->values(),
            ])->values(),
            'keys'         => $rows->map(fn ($row) => $row->no_carton . '__' . $row->id_so_det)->values(),
            'skipped'      => $skipped,
        ];
    }

    // Simpan ke bpb (SB), packing_master_carton & fg_fg_in. Mengembalikan no transaksi (bpbno_int).
    private function saveFgIn($po, $dest, $rows)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        $tgl_skrg = date('Y-m-d');

        $cek_sb = DB::connection('mysql_sb')->select("select count(id) tot from bpb where bpbdate = '$tgl_skrg'
        and po_fg = '$po' and status_input = 'nds'");
        $data_cek_sb = $cek_sb[0]->tot;

        if ($data_cek_sb == '0') {

            $update_data_bpbno = DB::connection('mysql_sb')->update("update tempbpb set bpbno = bpbno + 1  where mattype = 'fg'");
            $data_bpbno = DB::connection('mysql_sb')->select("select * from tempbpb where mattype = 'fg'");
            $bpbno = $data_bpbno[0]->BPBNo;

            $tahun = date('Y', strtotime($timestamp));
            $kode = 'FG-IN-' . $tahun;
            $update_data_bpbno_int = DB::connection('mysql_sb')->update("update tempbpb set bpbno = bpbno + 1  where mattype = '$kode'");
            $data_bpbno_int = DB::connection('mysql_sb')->select("select * from tempbpb where mattype = '$kode '");
            $bpbno_int_no_tr = $data_bpbno_int[0]->BPBNo;
            $bpbno_int_no_tr_fix = sprintf("%05s", $bpbno_int_no_tr);
            $thn_bln_bpbno_int = date('my', strtotime($timestamp));
            $bpbno_int = 'FG/IN/' . $thn_bln_bpbno_int . '/' . $bpbno_int_no_tr_fix;
        } else {
            $cek_no_sb = DB::connection('mysql_sb')->select("select substring(bpbno,3)bpbno,bpbno_int from bpb where bpbdate = '$tgl_skrg'
            and po_fg = '$po' and status_input = 'nds' limit 1");
            $bpbno = $cek_no_sb[0]->bpbno;
            $bpbno_int = $cek_no_sb[0]->bpbno_int;
        }

        $tgl_penerimaan         = date('Y-m-d');

        // bpb SB cuma 1 baris per id_so_det, jadi qty dijumlah dulu per id_so_det (bukan query per karton)
        foreach ($rows->groupBy('id_so_det') as $id_so_det => $rowsSoDet) {
            $txtqty = $rowsSoDet->sum('qty');
            $price  = $rowsSoDet->first()->price;
            $curr   = $rowsSoDet->first()->curr;

            $cek = DB::connection('mysql_sb')->select("select count(id_so_det) cek from masterstyle where id_so_det = '$id_so_det'");
            $cek_data = $cek[0]->cek;
            if ($cek_data == '0') {
                $ins_m_style = DB::connection('mysql_sb')->insert("insert into masterstyle
				(Styleno,Buyerno,DelDate,unit,itemname,Color,Size,id_so_det,KPNo,country,goods_code)
				select Styleno,so.Buyerno,DelDate_det,sod.unit,product_item,Color,Size,sod.id,KPNo,sod.dest,product_group from
				so_det sod inner join so on sod.id_so=so.id
				inner join act_costing ac on ac.id=so.id_cost
				inner join masterproduct mp on ac.id_product=mp.id
				where sod.cancel='N' and sod.id='$id_so_det'");
                $cek_id_item = DB::connection('mysql_sb')->select("select * from masterstyle where id_so_det = '$id_so_det'");
                $id_item = $cek_id_item[0]->id_item;
            } else {
                $cek_id_item = DB::connection('mysql_sb')->select("select * from masterstyle where id_so_det = '$id_so_det'");
                $id_item = $cek_id_item[0]->id_item;
            }

            $cek_id_sb = DB::connection('mysql_sb')->select("select id from bpb where bpbdate = '$tgl_skrg'
            and po_fg = '$po' and status_input = 'nds' and id_so_det = '$id_so_det' and id_item = '$id_item' ");
            $id_sb = $cek_id_sb ? $cek_id_sb[0]->id : 0;

            if ($id_sb == '0') {
                DB::connection('mysql_sb')->insert("insert into bpb(bpbno,bpbno_int,bpbdate,id_supplier,grade,invno,jenis_dok,id_item,id_so_det,qty,unit,price,curr,username,status_input,po_fg,jenis_trans)
                values('FG$bpbno','$bpbno_int','$tgl_penerimaan','435','GRADE A','-','INHOUSE','$id_item','$id_so_det','$txtqty','PCS','$price','$curr','$user','nds','$po','Hasil Produksi') ");
            } else {
                DB::connection('mysql_sb')->update("update bpb set qty = qty + $txtqty where id = '$id_sb' ");
            }
        }

        // Update status karton & insert fg_fg_in secara batch
        foreach ($rows->pluck('no_carton')->unique()->chunk(500) as $cartonChunk) {
            DB::table('packing_master_carton')
                ->where('po', $po)
                ->whereIn('no_carton', $cartonChunk->values()->all())
                ->update(['status' => 'transfer']);
        }

        $fg_in = $rows->map(function ($row) use ($bpbno_int, $tgl_skrg, $po, $dest, $user, $timestamp) {
            return [
                'no_sb'             => $bpbno_int,
                'tgl_penerimaan'    => $tgl_skrg,
                'id_ppic_master_so' => $row->id_ppic_master_so,
                'id_so_det'         => $row->id_so_det,
                'barcode'           => $row->barcode,
                'qty'               => $row->qty,
                'po'                => $po,
                'no_carton'         => $row->no_carton,
                'lokasi'            => '-',
                'notes'             => '-',
                'dest'              => $dest,
                'status'            => 'NORMAL',
                'created_by'        => $user,
                'updated_at'        => $timestamp,
                'created_at'        => $timestamp,
            ];
        });

        foreach ($fg_in->chunk(500) as $fgInChunk) {
            DB::table('fg_fg_in')->insert($fgInChunk->values()->all());
        }

        return $bpbno_int;
    }

    public function export_excel_fg_in_list(Request $request)
    {
        return Excel::download(new ExportLaporanFGINList($request->from, $request->to), 'Laporan_Penerimaan FG_Stok.xlsx');
    }
    public function export_excel_fg_in_summary(Request $request)
    {
        return Excel::download(new ExportLaporanFGINSummary($request->from, $request->to), 'Laporan_Penerimaan FG_Stok.xlsx');
    }
}
