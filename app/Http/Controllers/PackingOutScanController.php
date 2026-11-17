<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Marker\Marker;
use App\Models\Part\Part;
use App\Models\Stocker\Stocker;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use Excel;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use Yajra\DataTables\Facades\DataTables;

class PackingOutScanController extends Controller
{
    public function index() {
        return view("packing-out-scan.index", [
            'containerFluid' => true, 
            'page' => 'dashboard-packing',
            "subPageGroup" => "packing-packing-out",
            "subPage" => "packing-out-scan"
        ]);
    }

    public function create() {
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-120 days'));

        $data_po = DB::select("
            SELECT
                MIN(ppic_master_so.id) isi,
                ppic_master_so.po,
                ppic_master_so.dest
            FROM ppic_master_so
            WHERE ppic_master_so.tgl_shipment >= ?
                AND ppic_master_so.po IS NOT NULL AND ppic_master_so.po != ''
            GROUP BY ppic_master_so.po, ppic_master_so.dest
            ORDER BY ppic_master_so.po, ppic_master_so.dest
        ", [$tgl_skrg_min_sebulan]);

        return view("packing-out-scan.create", [
            'containerFluid' => true,
            'page' => 'dashboard-packing',
            "subPageGroup" => "packing-packing-out",
            "subPage" => "packing-out-scan",
            "data_po" => $data_po,
        ]);
    }

    // Semua karton 1 PO (packing list + qty yang sudah di-scan) untuk karton aktif & peta karton
    public function getCartonData(Request $request)
    {
        $cek_po = DB::table('ppic_master_so')
            ->select('po', 'dest')
            ->where('id', $request->cbopo)
            ->first();

        if (!$cek_po) {
            return response()->json(['message' => 'PO tidak ditemukan'], 404);
        }

        $data_carton = DB::select("
            SELECT
                a.no_carton,
                a.id_so_det,
                CASE WHEN a.barcode = '-' THEN COALESCE(pp.barcode, a.barcode) ELSE a.barcode END barcode,
                m.size,
                msn.urutan size_order,
                m.color,
                m.styleno,
                m.ws,
                a.qty,
                a.keterangan,
                a.tipe_pack,
                COALESCE(b.qty_scan, 0) qty_scan
            FROM (
                SELECT po, dest, no_carton, id_so_det, MAX(barcode) barcode, SUM(qty) qty, MAX(keterangan) keterangan, MAX(tipe_pack) tipe_pack
                FROM packing_master_packing_list
                WHERE po = ? AND dest = ?
                GROUP BY po, dest, no_carton, id_so_det
            ) a
            LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
            -- Barcode packing list '-' diganti barcode dari master PPIC
            LEFT JOIN (
                SELECT id_so_det, MAX(barcode) barcode
                FROM ppic_master_so
                WHERE po = ? AND dest = ?
                GROUP BY id_so_det
            ) pp ON a.id_so_det = pp.id_so_det
            -- Scan masuk ke baris karton lewat id_so_det; scan lama yang id_so_det-nya kosong/tidak cocok lewat barcode
            LEFT JOIN (
                SELECT s.no_carton, COALESCE(k1.id_so_det, k2.id_so_det) id_so_det, COUNT(*) qty_scan
                FROM packing_packing_out_scan s
                LEFT JOIN (
                    SELECT DISTINCT no_carton, id_so_det
                    FROM packing_master_packing_list
                    WHERE po = ? AND dest = ?
                ) k1 ON s.no_carton = k1.no_carton AND s.id_so_det = k1.id_so_det
                LEFT JOIN (
                    SELECT no_carton, barcode, MIN(id_so_det) id_so_det
                    FROM packing_master_packing_list
                    WHERE po = ? AND dest = ?
                    GROUP BY no_carton, barcode
                ) k2 ON s.no_carton = k2.no_carton AND s.barcode = k2.barcode
                WHERE s.po = ? AND s.dest = ?
                GROUP BY s.no_carton, COALESCE(k1.id_so_det, k2.id_so_det)
            ) b ON a.no_carton = b.no_carton AND a.id_so_det = b.id_so_det
            LEFT JOIN master_size_new msn ON m.size = msn.size
            ORDER BY a.no_carton ASC, m.color ASC, msn.urutan ASC
        ", [
            $cek_po->po, $cek_po->dest,
            $cek_po->po, $cek_po->dest,
            $cek_po->po, $cek_po->dest,
            $cek_po->po, $cek_po->dest,
            $cek_po->po, $cek_po->dest,
        ]);

        return response()->json([
            'po' => $cek_po->po,
            'dest' => $cek_po->dest,
            'data' => $data_carton,
        ]);
    }

    // Simpan 1x scan barcode ke karton (alur sama dengan PackingPackingOutController@store, tanpa FGS)
    public function store(Request $request)
    {
        $timestamp = Carbon::now();
        $user      = Auth::user()->name;
        $barcode   = $request->barcode;
        $cbopo     = $request->cbopo;
        $no_carton = $request->no_carton;
        // Barcode sama di satu karton (mis. beda color): layar kirim semua id_so_det-nya, 1 scan = +1 ke tiap baris
        $req_so_dets = array_filter((array) $request->id_so_det);

        try {
            $result = DB::transaction(function () use ($cbopo, $barcode, $no_carton, $req_so_dets, $user, $timestamp) {

                // =========================================================
                // CEK PO PPIC
                // =========================================================
                $cek_po_by_ppic = DB::select("
                    SELECT *
                    FROM ppic_master_so
                    WHERE id = ?
                    FOR UPDATE
                ", [$cbopo]);

                if (!$cek_po_by_ppic) {
                    return [
                        'icon' => 'salah',
                        'msg' => 'Data PO PPIC tidak ditemukan',
                    ];
                }

                $po   = $cek_po_by_ppic[0]->po;
                $dest = $cek_po_by_ppic[0]->dest;

                $tgl_trans = date('Y-m-d');

                // =========================================================
                // CEK BARCODE DI MASTER PPIC
                // =========================================================
                $cek_po = DB::select("
                    SELECT *
                    FROM ppic_master_so
                    WHERE barcode = ?
                    AND dest = ?
                    AND po = ?
                ", [$barcode, $dest, $po]);

                if (!$cek_po) {
                    return [
                        'icon' => 'salah',
                        'msg' => 'Data tidak ada di Master PPIC',
                    ];
                }

                // Satu barcode bisa punya beberapa id_so_det; kalau layar sudah kirim id_so_det, pakai itu
                $so_dets = collect($cek_po)->pluck('id_so_det');
                $targets = $req_so_dets
                    ? $so_dets->filter(fn ($s) => in_array($s, $req_so_dets))->values()
                    : collect([null]);

                if ($targets->isEmpty()) {
                    return [
                        'icon' => 'salah',
                        'msg' => 'Data tidak ada di packing list',
                    ];
                }

                // Kalau salah satu baris gagal, lempar exception supaya insert baris sebelumnya ikut dibatalkan.
                // Pengecualian: baris yang stoknya habis cukup dilewati (dicatat di $skipped).
                $ids = [];
                $skipped = [];

                foreach ($targets as $target) {

                    // =========================================================
                    // LOCK PACKING LIST
                    // =========================================================
                    // Dicocokkan per carton + id_so_det; barcode packing list
                    // boleh sama dengan hasil scan atau '-'.
                    // Request ke baris karton yang sama akan menunggu sampai
                    // request sebelumnya selesai.
                    // =========================================================
                    $packing_list = DB::table('packing_master_packing_list')
                        ->where('po', $po)
                        ->where('no_carton', $no_carton)
                        ->whereIn('id_so_det', $target === null ? $so_dets : [$target])
                        ->whereIn('barcode', [$barcode, '-'])
                        ->where('dest', $dest)
                        ->lockForUpdate()
                        ->first();

                    if (!$packing_list) {
                        throw new \DomainException('Data tidak ada di packing list');
                    }

                    $id_so_det = $packing_list->id_so_det;
                    $id_ppic   = collect($cek_po)->firstWhere('id_so_det', $id_so_det)->id;

                    // =========================================================
                    // CEK STOK (setelah lock, jadi tot_out sudah ter-update)
                    // =========================================================
                    // Stok = Saldo Akhir Detail Transaksi WIP PO (snapshot saldo_wip_packing_central + transaksi)
                    // untuk PO ini dan item (ws/style/color/size) dari id_so_det yang di-scan
                    $cek_stok_fix = app(PackingCentralSwitchingController::class)
                        ->saldoAkhirDetailTransaksi($po, [$id_so_det]);

                    // Stok dicek per id_so_det; baris yang stoknya habis dilewati, baris lain tetap masuk
                    if ($cek_stok_fix < 1) {
                        $skipped[] = $id_so_det;
                        continue;
                    }

                    // =========================================================
                    // HITUNG TOTAL SUDAH OUT DI KARTON INI
                    // =========================================================
                    $cek_qty_isi_karton = DB::select("
                        SELECT
                            qty,
                            COALESCE(tot_input, 0) AS tot_input
                        FROM
                        (
                            SELECT po, no_carton, id_so_det, dest, SUM(qty) qty
                            FROM packing_master_packing_list
                            WHERE po = ?
                            AND no_carton = ?
                            AND id_so_det = ?
                            AND dest = ?
                            GROUP BY po, no_carton, id_so_det, dest
                        ) a
                        LEFT JOIN
                        (
                            -- Scan lama yang id_so_det-nya kosong/tidak ada di karton ini dihitung lewat barcode
                            SELECT po, no_carton, dest, COUNT(barcode) AS tot_input
                            FROM packing_packing_out_scan s
                            WHERE po = ?
                            AND no_carton = ?
                            AND dest = ?
                            AND (
                                id_so_det = ?
                                OR (
                                    barcode = ?
                                    AND NOT EXISTS (
                                        SELECT 1
                                        FROM packing_master_packing_list k
                                        WHERE k.po = s.po
                                        AND k.dest = s.dest
                                        AND k.no_carton = s.no_carton
                                        AND k.id_so_det = s.id_so_det
                                    )
                                )
                            )
                            GROUP BY po, no_carton, dest
                        ) b
                            ON a.po = b.po
                            AND a.dest = b.dest
                            AND a.no_carton = b.no_carton
                    ", [$po, $no_carton, $id_so_det, $dest, $po, $no_carton, $dest, $id_so_det, $packing_list->barcode]);

                    if (!$cek_qty_isi_karton) {
                        throw new \DomainException('Tidak Ada Data 2');
                    }

                    $cek_qty_isi = $cek_qty_isi_karton[0]->qty;
                    $tot_out     = $cek_qty_isi_karton[0]->tot_input;

                    // =========================================================
                    // CEK QTY KARTON & INSERT
                    // =========================================================
                    if ($cek_qty_isi > $tot_out) {
                        $id_scan = DB::table('packing_packing_out_scan')->insertGetId([
                            'tgl_trans'  => $tgl_trans,
                            'barcode'    => $barcode,
                            'po'         => $po,
                            'dest'       => $dest,
                            'no_carton'  => $no_carton,
                            'notes'      => '-',
                            'created_by' => $user,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                            'id_so_det'  => $id_so_det,
                            'id_ppic'    => $id_ppic,
                        ]);

                        $ids[$id_so_det] = $id_scan;
                        continue;
                    }

                    if ($cek_qty_isi == $tot_out) {
                        throw new \DomainException('Data sudah melebihi qty karton');
                    }

                    throw new \DomainException('Tidak Ada Data 1');
                }

                if (!$ids) {
                    throw new \DomainException('Tidak Ada Stok');
                }

                return [
                    'icon' => 'benar',
                    'msg' => 'Data berhasil Disimpan',
                    'ids' => $ids,
                    'skipped' => $skipped,
                ];
            });
        } catch (\DomainException $e) {
            $result = [
                'icon' => 'salah',
                'msg' => $e->getMessage(),
            ];
        }

        return response()->json($result);
    }

    // Batalkan 1 scan: simpan ke log lalu hapus (alur sama dengan PackingPackingOutController@packing_out_hapus_history)
    public function destroy(Request $request)
    {
        $id = $request->id;

        $deleted = DB::transaction(function () use ($id) {
            DB::insert("
                INSERT INTO packing_packing_out_scan_log (id_packing_Packing_out_scan, tgl_trans, barcode, po, no_carton, created_at, updated_at, created_by)
                SELECT id, tgl_trans, barcode, po, no_carton, created_at, updated_at, created_by
                FROM packing_packing_out_scan
                WHERE id = ?
            ", [$id]);

            return DB::delete("DELETE FROM packing_packing_out_scan WHERE id = ?", [$id]);
        });

        if (!$deleted) {
            return response()->json(['message' => 'Data scan tidak ditemukan'], 404);
        }

        return response()->json(['message' => 'Scan berhasil dibatalkan']);
    }

}
