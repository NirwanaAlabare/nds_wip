<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

// Halaman baca-saja: scan QR No. Carton -> tampilkan isi & saldonya pada opname terakhir
class FGStokCekQrCartonController extends Controller
{
    public function index(Request $request)
    {
        return view('fg-stock.cek_qr_carton_fg_stock', [
            'page' => 'dashboard-fg-stock',
            "subPageGroup" => "fgstock-opname",
            "subPage" => "cek-qr-carton-fg-stock",
            'header' => $this->lastOpnameHeader(),
            'containerFluid' => true,
        ]);
    }

    // Pengecekan carton selalu memakai opname terakhir: operator tinggal scan,
    // tidak perlu memilih periode dulu.
    private function lastOpnameHeader()
    {
        $header = DB::select("
            SELECT no_opname, tgl_opname, periode, ket, status
            FROM fg_stok_opname_header
            WHERE cancel = 'N'
            ORDER BY tgl_opname DESC, no_opname DESC
            LIMIT 1
        ");

        return count($header) > 0 ? $header[0] : null;
    }

    public function getData(Request $request)
    {
        $request->validate([
            'no_carton' => 'required|string',
        ]);

        $no_carton = trim($request->no_carton);
        $header = $this->lastOpnameHeader();

        if (!$header) {
            return response()->json(['message' => 'Belum ada data opname sama sekali.'], 404);
        }

        $rows = DB::select("
            SELECT
                d.id_so_det,
                d.no_carton,
                d.no_pallet,
                d.status,
                d.grade,
                d.qty,
                m.buyer,
                m.ws,
                m.styleno,
                m.dest,
                m.color,
                m.size,
                m.product_item
            FROM fg_stok_opname_detail d
            LEFT JOIN master_sb_ws m ON m.id_so_det = d.id_so_det
            LEFT JOIN master_size_new ms ON ms.size = m.size
            WHERE d.no_opname = ? AND d.no_carton = ? AND d.cancel = 'N'
            ORDER BY ms.urutan ASC, d.id ASC
        ", [$header->no_opname, $no_carton]);

        if (count($rows) === 0) {
            return response()->json([
                'message' => 'No. Carton ' . $no_carton . ' tidak ada di opname ' . $header->no_opname . '.',
            ], 404);
        }

        // Baris dengan id_so_det NULL hanya penanda bahwa carton sudah dibuat tapi belum diisi item
        $items = array_values(array_filter($rows, fn($row) => $row->id_so_det !== null));

        return response()->json([
            'no_opname' => $header->no_opname,
            'no_carton' => $rows[0]->no_carton,
            'no_pallet' => $rows[0]->no_pallet,
            'status' => $rows[0]->status,
            'total_qty' => array_sum(array_map(fn($row) => (float) $row->qty, $items)),
            'items' => $items,
        ]);
    }

}
