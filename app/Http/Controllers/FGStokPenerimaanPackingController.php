<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FGStokPenerimaanPacking;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use \avadim\FastExcelLaravel\Excel as FastExcel;

class FGStokPenerimaanPackingController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;

        if ($request->ajax()) {
            $data_input = DB::select("
                SELECT
                    fg.id,
                    fg.no_trans,
                    DATE_FORMAT(fg.created_at, '%d-%m-%Y') AS tgl_trans,
                    packing_out.no_trans AS no_trans_packing,
                    packing_out.no_karton AS no_karton_packing,
                    fg.no_karton_gd,
                    fg.lokasi_palet,
                    UPPER(fg.po) AS po,
                    msb.ws,
                    msb.styleno AS style,
                    msb.color,
                    msb.size,
                    fg.qty,
                    fg.created_by_username,
                    fg.created_at
                FROM
                    fg_stok_penerimaan_packing fg
                LEFT JOIN packing_out_gudang_stok packing_out ON packing_out.id = fg.packing_out_gudang_stok_id
                LEFT JOIN master_sb_ws msb ON msb.id_so_det = fg.so_det_id
                WHERE fg.created_at >= '$tgl_awal 00:00:00' AND fg.created_at <= '$tgl_akhir 23:59:59' AND fg.mutasi = 'N'
                ORDER BY fg.id DESC
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view('fg-stock.bpb_fg_stok_penerimaan_packing', ['page' => 'dashboard-fg-stock', "subPageGroup" => "fgstock-bpb", "subPage" => "bpb-fg-stok-penerimaan-packing", "containerFluid" => true]);
    }

    public function create(){

        $lokasi = DB::select("select kode_lok_fg_stok isi , kode_lok_fg_stok tampil from fg_stok_master_lok where cancel = 'N' AND TRIM(kode_lok_fg_stok) <> '' ORDER BY lokasi ASC, CAST(tingkat AS UNSIGNED) ASC, CAST(baris AS UNSIGNED) ASC");

        return view("fg-stock.create_bpb_fg_stok_penerimaan_packing", [
            "page" => "dashboard-fg-stock",
            "subPageGroup" => "fgstock-bpb",
            "subPage" => "bpb-fg-stok-penerimaan-packing",
            "containerFluid" => true,
            "lokasi" => $lokasi
        ]);
    }

    public function getNoTransaksi()
    {
        $data = DB::select("
            SELECT
                a.no_trans AS id,
                a.no_trans AS text,
                a.no_trans,
                SUM(a.qty) - COALESCE(SUM(b.qty_fgs_packing), 0) AS qty
            FROM packing_out_gudang_stok a
            LEFT JOIN (
                SELECT
                    packing_out_gudang_stok_id,
                    SUM(qty) AS qty_fgs_packing
                FROM fg_stok_penerimaan_packing
                GROUP BY packing_out_gudang_stok_id
            ) b ON b.packing_out_gudang_stok_id = a.id
            WHERE a.tujuan = 'GUDANG STOK'
            GROUP BY a.no_trans
            HAVING qty > 0
            ORDER BY a.no_trans DESC
        ");

        return response()->json($data);
    }

    public function getNoKarton(Request $request)
    {
        $no_trans = $request->no_trans;

        if ($no_trans === null || $no_trans === '') {
            return response()->json([]);
        }

        $data = DB::select("
            SELECT
                a.no_karton AS id,
                a.no_karton AS text,
                a.no_karton,
                SUM(a.qty) - COALESCE(SUM(b.qty_fgs_packing), 0) AS qty
            FROM packing_out_gudang_stok a
            LEFT JOIN (
                SELECT
                    packing_out_gudang_stok_id,
                    SUM(qty) AS qty_fgs_packing
                FROM fg_stok_penerimaan_packing
                GROUP BY packing_out_gudang_stok_id
            ) b ON b.packing_out_gudang_stok_id = a.id
            WHERE a.no_trans = ?
                AND a.tujuan = 'GUDANG STOK'
            GROUP BY a.no_karton
            HAVING qty > 0
            ORDER BY CAST(a.no_karton AS UNSIGNED) ASC, a.no_karton ASC
        ", [$no_trans]);

        return response()->json($data);
    }

    public function getDetailBarang(Request $request)
    {
        $no_trans  = $request->no_trans;
        $no_karton = $request->no_karton;

        if ($no_trans === null || $no_trans === '' || $no_karton === null || $no_karton === '') {
            return response()->json([]);
        }

        $data = DB::select("
            SELECT
                CONCAT_WS('|', a.so_det_id, a.lokasi_asal, COALESCE(a.grade, '')) AS id,
                CONCAT(
                    UPPER(COALESCE(a.lokasi_asal, '-')), ' | ',
                    COALESCE(w.ws, '-'), ' / ',
                    COALESCE(w.styleno, '-'), ' / ',
                    COALESCE(w.color, '-'), ' / ',
                    COALESCE(w.size, '-'),
                    ' (Grade ', COALESCE(a.grade, '-'),
                    ' - ', SUM(a.qty) - COALESCE(SUM(b.qty_fgs_packing), 0), ' PCS)'
                ) AS text,
                MIN(a.id) AS packing_out_gudang_stok_id,
                a.so_det_id,
                a.ppic_master_so_id,
                a.po,
                a.lokasi_asal,
                a.grade,
                w.ws,
                w.styleno,
                w.color,
                w.size,
                w.buyer,
                SUM(a.qty) - COALESCE(SUM(b.qty_fgs_packing), 0) AS qty
            FROM packing_out_gudang_stok a
            LEFT JOIN master_sb_ws w ON w.id_so_det = a.so_det_id
            LEFT JOIN (
                SELECT
                    packing_out_gudang_stok_id,
                    SUM(qty) AS qty_fgs_packing
                FROM fg_stok_penerimaan_packing
                GROUP BY packing_out_gudang_stok_id
            ) b ON b.packing_out_gudang_stok_id = a.id
            WHERE a.no_trans = ?
                AND a.no_karton = ?
                AND a.tujuan = 'GUDANG STOK'
            GROUP BY
                a.so_det_id,
                a.ppic_master_so_id,
                a.po,
                a.lokasi_asal,
                a.grade,
                w.ws,
                w.styleno,
                w.color,
                w.size,
                w.buyer
            HAVING qty > 0
            ORDER BY w.ws ASC, w.styleno ASC, w.color ASC, w.size ASC
        ", [$no_trans, $no_karton]);

        return response()->json($data);
    }

    public function store(Request $request){

        DB::beginTransaction();

        try {

            $user = Auth::user();
            $now = Carbon::now();

            $items = json_decode($request->items, true);

            $no_trans = DB::selectOne("
                SELECT
                    CONCAT('FGS/IN/', DATE_FORMAT(CURRENT_DATE(), '%Y')) AS Mattype,
                    IF(
                        MAX(no_trans) IS NULL,
                        '00001',
                        LPAD(MAX(RIGHT(no_trans, 5)) + 1, 5, 0)
                    ) AS nomor,
                    CONCAT(
                        'FGS/IN/',
                        DATE_FORMAT(CURRENT_DATE(), '%m'),
                        DATE_FORMAT(CURRENT_DATE(), '%y'),
                        '/',
                        IF(
                            MAX(no_trans) IS NULL,
                            '00001',
                            LPAD(MAX(RIGHT(no_trans, 5)) + 1, 5, 0)
                        )
                    ) AS kode
                FROM fg_stok_penerimaan_packing
                WHERE
                    MONTH(created_at) = MONTH(CURRENT_DATE())
                    AND YEAR(created_at) = YEAR(CURRENT_DATE())
                    AND LEFT(no_trans, 3) = 'FGS'
            ");

            $items = json_decode($request->items, true);

            foreach ($items as $item) {

                FGStokPenerimaanPacking::create([
                    'no_trans'                   => $no_trans->kode,
                    'packing_out_gudang_stok_id' => $item['packing_out_gudang_stok_id'],
                    'ppic_master_so_id'          => $item['ppic_master_so_id'] ?: null,
                    'so_det_id'                  => $item['so_det_id'],
                    'po'                         => $item['po'],
                    'no_karton_asal'             => $item['no_karton_asal'],
                    'no_karton_gd'               => $item['no_karton_gd_target'],
                    'lokasi_palet'               => $item['lokasi_palet_gudang'],
                    'qty'                        => $item['qty'],
                    'created_by'                 => auth()->user()->id,
                    'created_by_username'        => auth()->user()->username,
                    'created_at'                 => date('Y-m-d H:i:s'),
                ]);

            }

            DB::commit();

            return array(
                "status" => 200,
                "message" => "Data berhasil disimpan.<br>No Transaksi: " . $no_trans->kode,
            );

        } catch (Exception $e) {
            DB::rollBack();

            return array(
                "status" => 400,
                "message" => "Terjadi Kesalahan",
                "messages" => $e->getMessage(),
                "additional" => [],
            );
        }
    }

    public function export_excel_bpb_fg_stok_penerimaan_packing(Request $request)
    {
        $tgl_awal = $request->from;
        $tgl_akhir = $request->to;

        $data = DB::select("
            SELECT
                fg.id,
                fg.no_trans,
                DATE_FORMAT(fg.created_at, '%d-%m-%Y') AS tgl_trans,
                packing_out.no_trans AS no_trans_packing,
                packing_out.no_karton AS no_karton_packing,
                fg.no_karton_gd,
                fg.lokasi_palet,
                UPPER(fg.po) AS po,
                msb.ws,
                msb.styleno AS style,
                msb.color,
                msb.size,
                fg.qty,
                fg.created_by_username,
                fg.created_at
            FROM
                fg_stok_penerimaan_packing fg
            LEFT JOIN packing_out_gudang_stok packing_out ON packing_out.id = fg.packing_out_gudang_stok_id
            LEFT JOIN master_sb_ws msb ON msb.id_so_det = fg.so_det_id
            WHERE fg.created_at >= '$tgl_awal 00:00:00' AND fg.created_at <= '$tgl_akhir 23:59:59' AND fg.mutasi = 'N'
            ORDER BY fg.id DESC
        ");

        $fileName = 'report-penerimaan-packing';

        $excel = FastExcel::create($fileName);
        $sheet = $excel->sheet();

        $sheet->writeRow(
            ['Laporan Penerimaan Barang Jadi Packing'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
            ]
        );

        $sheet->writeRow(
            ['Periode ' . $tgl_awal . ' s/d ' . $tgl_akhir],
            [
                'font-size' => 12,
            ]
        );

        $sheet->writeRow(['']);

        $header = [
            'No. Trans',
            'Tgl. Trans',
            'No. Trans Packing',
            'No. Karton Packing',
            'No. Karton GD',
            'Lokasi Palet',
            'PO',
            'WS',
            'Style',
            'Color',
            'Size',
            'Qty',
            'User',
            'Tgl Input',
        ];

        $sheet->writeRow(
            $header,
            [
                'font-style' => 'bold',
                'border'     => 'thin',
                'halign'     => 'center',
                'valign'     => 'center',
            ]
        );

        foreach ($data as $row) {
            $rows = [
                $row->no_trans ?? '',
                $row->tgl_trans ?? '',
                $row->no_trans_packing ?? '',
                $row->no_karton_packing ?? '',
                $row->no_karton_gd ?? '',
                $row->lokasi_palet ?? '',
                $row->po ?? '',
                $row->ws ?? '',
                $row->style ?? '',
                $row->color ?? '',
                $row->size ?? '',
                (float) ($row->qty ?? 0),
                $row->created_by_username ?? '',
                $row->created_at ?? '',
            ];

            $sheet->writeRow(
                $rows,
                [
                    'border' => 'thin',
                ]
            );
        }

        $columns = [
            'A', 'B', 'C', 'D', 'E', 'F',
            'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N'
        ];

        foreach ($columns as $col) {
            $sheet->setColWidth($col, 20);
        }

        return $excel->download();
    }

}
