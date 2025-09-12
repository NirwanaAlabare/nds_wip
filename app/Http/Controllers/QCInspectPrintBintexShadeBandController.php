<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use PDF;
use Milon\Barcode\DNS1D;

class QCInspectPrintBintexShadeBandController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $tgl_skrg = date('Y-m-d');
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-30 days'));
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data_input = DB::connection('mysql_sb')->select("select
c.tgl_dok,
DATE_FORMAT(c.tgl_dok, '%d-%M-%Y') AS tgl_dok_fix,
c.no_dok,
no_barcode,
no_invoice,
c.supplier,
ms.Supplier buyer,
ac.kpno,
ac.styleno,
mi.color,
mi.id_item,
mi.itemdesc,
no_roll_buyer,
no_lot,
qty_aktual,
satuan
from whs_lokasi_inmaterial a
LEFT JOIN whs_inmaterial_fabric_det b ON a.no_dok = b.no_dok AND a.id_item = b.id_item AND a.id_jo = b.id_jo
LEFT JOIN whs_inmaterial_fabric c ON a.no_dok = c.no_dok
INNER JOIN jo_det jd ON a.id_jo = jd.id_jo
INNER JOIN so ON jd.id_so = so.id
INNER JOIN act_costing ac ON so.id_cost = ac.id
INNER JOIN mastersupplier ms ON ac.id_buyer = ms.Id_Supplier
INNER JOIN masteritem mi ON a.id_item = mi.id_item
where c.tgl_dok >= '$tgl_awal' and  c.tgl_dok <= '$tgl_akhir'
order by c.no_dok asc,c.tgl_dok asc, a.id_item asc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view(
            'qc_inspect.proses_print_bintex_shade_band',
            [
                'page' => 'dashboard-qc-inspect',
                "subPageGroup" => "qc-inspect-proses",
                "subPage" => "qc-inspect-proses-print-bintex-shade-band",
                'tgl_skrg_min_sebulan' => $tgl_skrg_min_sebulan,
                'tgl_skrg' => $tgl_skrg,
                "containerFluid" => true,
                "user" => $user
            ]
        );
    }


    public function print_sticker_bintex_shade_band(Request $request)
    {
        // Fetch header data using raw SQL query

        $ids = $request->input('ids');

        // Wrap each ID in quotes
        $quotedIds = array_map(function ($id) {
            return "'$id'";
        }, $ids);

        // Implode into a string for SQL
        $inClause = implode(',', $quotedIds);

        // Now use the string in the raw SQL query
        $sql = "SELECT
            no_lot,
            no_roll_buyer,
            qty_aktual,
            satuan,
            id_item,
            no_barcode
        FROM whs_lokasi_inmaterial
        WHERE no_barcode IN ($inClause)"; // Removed the extra closing parenthesis

        $data_header = DB::connection('mysql_sb')->select($sql);


        $d = new DNS1D();

        foreach ($data_header as &$dh) {
            if (!empty($dh->no_barcode)) {
                $dh->barcode_base64 = $d->getBarcodePNG($dh->no_barcode, 'C128'); // Code128 barcode
            } else {
                $dh->barcode_base64 = null;
            }
        }

        // Generate PDF from the view
        $pdf = PDF::loadView('qc_inspect.pdf_print_bintex_shade_band', [
            'data_header' => $data_header,
        ])->setPaper([0, 0, 85.04, 56.69]);

        // Set filename and return download
        $fileName = 'pdf.pdf';
        return $pdf->download(str_replace("/", "_", $fileName));
    }
}
