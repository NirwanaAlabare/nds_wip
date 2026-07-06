<?php

namespace App\Http\Controllers;

use App\Imports\ImportIE_MasterProcess;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MarketingReportController extends Controller
{
    private function get_marketing_cvs_detail_data(?string $from, ?string $to)
    {
        return DB::connection('mysql_sb')->select("
select a.cost_no,kpno,supplier,mkt_order,styleno,product_item,season_desc,curr,so_date,status,qty_so,price_so,cost_date,status_cost,qty_cost,COALESCE(ttl_fabric,0) ttl_fabric,COALESCE(ttl_accsew,0) ttl_accsew,COALESCE(ttl_accpack,0) ttl_accpack,(COALESCE(ttl_fabric,0) + COALESCE(ttl_accsew,0) + COALESCE(ttl_accpack,0)) ttl_material,COALESCE(ttl_cmt,0) ttl_cmt,COALESCE(ttl_embro,0) ttl_embro,COALESCE(ttl_wash,0) ttl_wash,COALESCE(ttl_print,0) ttl_print,COALESCE(ttl_wrapbut,0) ttl_wrapbut,COALESCE(ttl_compbut,0) ttl_compbut,COALESCE(ttl_label,0) ttl_label,COALESCE(ttl_laser,0) ttl_laser,(COALESCE(ttl_cmt,0) + COALESCE(ttl_embro,0) + COALESCE(ttl_wash,0) + COALESCE(ttl_print,0) + COALESCE(ttl_wrapbut,0) + COALESCE(ttl_compbut,0) + COALESCE(ttl_label,0) + COALESCE(ttl_laser,0)) ttl_manufacturing,COALESCE(ttl_develop,0) ttl_develop,COALESCE(ttl_overhead,0) ttl_overhead,COALESCE(ttl_market,0) ttl_market,COALESCE(ttl_shipp,0) ttl_shipp,COALESCE(ttl_import,0) ttl_import,COALESCE(ttl_handl,0) ttl_handl,COALESCE(ttl_test,0) ttl_test,COALESCE(ttl_fabhandl,0) ttl_fabhandl,COALESCE(ttl_service,0) ttl_service, COALESCE(ttl_clearcost,0) ttl_clearcost ,COALESCE(ttl_development,0) ttl_development ,COALESCE(ttl_unexcost,0) ttl_unexcost ,COALESCE(ttl_managementfee,0) ttl_managementfee ,COALESCE(ttl_profit,0) ttl_profit ,(COALESCE(ttl_develop,0) + COALESCE(ttl_overhead,0) + COALESCE(ttl_market,0) + COALESCE(ttl_shipp,0) + COALESCE(ttl_import,0) + COALESCE(ttl_handl,0) + COALESCE(ttl_test,0) + COALESCE(ttl_fabhandl,0) + COALESCE(ttl_service,0) + COALESCE(ttl_clearcost,0) + COALESCE(ttl_development,0) + COALESCE(ttl_unexcost,0) + COALESCE(ttl_managementfee,0) + COALESCE(ttl_profit,0)) ttl_others
           from (select a.cost_no,a.kpno,b.supplier,a.mkt_order,styleno,product_item,season_desc,if(so.curr is null,a.curr,so.curr) curr,so_date,IF(so.cancel_h = 'Y','CANCEL','-') status,so.qty qty_so,so.fob price_so,cost_date,a.status status_cost, a.qty qty_cost  from act_costing a INNER JOIN mastersupplier b ON a.id_buyer=b.Id_Supplier inner join masterproduct mp on a.id_product=mp.id left join so on so.id_cost = a.id left join masterseason ms on ms.id_season = so.id_season where cost_date BETWEEN '$from' and '$to' GROUP BY cost_no) a left join (select cost_no, sum(ttl_fabric) ttl_fabric, sum(ttl_accsew) ttl_accsew, sum(ttl_accpack) ttl_accpack from (select cost_no,case when mattype = 'FABRIC' then total end as ttl_fabric,
           case when mattype = 'ACCESORIES SEWING' then total end as ttl_accsew,
           case when mattype = 'ACCESORIES PACKING' then total end as ttl_accpack from (SELECT cost_no,mattype,IF(curr = 'IDR',val_idr,val_usd) total from act_material where cost_date BETWEEN '$from' and '$to') a) a GROUP BY cost_no) b on b.cost_no = a.cost_no left join (select cost_no, sum(ttl_cmt) ttl_cmt, sum(ttl_embro) ttl_embro, sum(ttl_wash) ttl_wash, sum(ttl_print) ttl_print, sum(ttl_wrapbut) ttl_wrapbut, sum(ttl_compbut) ttl_compbut, sum(ttl_label) ttl_label, sum(ttl_laser) ttl_laser from (select cost_no,case when mattype = 'CMT' then total end as ttl_cmt,
           case when mattype = 'EMBRODEIRY' then total end as ttl_embro,
           case when mattype = 'WASHING' then total end as ttl_wash,
           case when mattype = 'PRINTING' then total end as ttl_print,
           case when mattype = 'WRAPPED BUTTON' then total end as ttl_wrapbut,
           case when mattype = 'COMPLEXITY MAKLOON BUTTON' then total end as ttl_compbut,
           case when mattype = 'LABEL PRINT' then total end as ttl_label,
           case when mattype = 'LASER CUTTING' then total end as ttl_laser from (SELECT cost_no,mattype,IF(curr = 'IDR',val_idr,val_usd) total from act_manufacturing where cost_date BETWEEN '$from' and '$to') a) a GROUP BY cost_no) c on c.cost_no = a.cost_no left join (select cost_no, sum(ttl_develop) ttl_develop, sum(ttl_overhead) ttl_overhead, sum(ttl_market) ttl_market, sum(ttl_shipp) ttl_shipp, sum(ttl_import) ttl_import, sum(ttl_handl) ttl_handl, sum(ttl_test) ttl_test, sum(ttl_fabhandl) ttl_fabhandl, sum(ttl_service) ttl_service, sum(ttl_clearcost) ttl_clearcost , sum(ttl_development) ttl_development, sum(ttl_unexcost) ttl_unexcost, sum(ttl_managementfee) ttl_managementfee, sum(ttl_profit) ttl_profit from (select cost_no,case when mattype = 'DEVELOPMENT' then total end as ttl_develop,
           case when mattype = 'OVERHEAD' then total end as ttl_overhead,
           case when mattype = 'MARKETING' then total end as ttl_market,
           case when mattype = 'SHIPPING' then total end as ttl_shipp,
           case when mattype = 'IMPORT COST' then total end as ttl_import,
           case when mattype = 'HANDLING' then total end as ttl_handl,
           case when mattype = 'TESTING' then total end as ttl_test,
           case when mattype = 'FABRIC HANDLING' then total end as ttl_fabhandl,
           case when mattype = 'SERVICE CHARGE' then total end as ttl_service,
           case when mattype = 'CLEARANCE  COST' then total end as ttl_clearcost,
           case when mattype = 'DEVELOPMENT' then '0' end as ttl_development,
           case when mattype = 'UNEXPECTED COST' then total end as ttl_unexcost,
           case when mattype = 'MANAGEMENT FEE' then total end as ttl_managementfee,
           case when mattype = 'PROFIT' then total end as ttl_profit
            from (SELECT cost_no,mattype,IF(curr = 'IDR',val_idr,val_usd) total from act_others where cost_date BETWEEN '$from' and '$to') a) a GROUP BY cost_no) d on d.cost_no = a.cost_no
            ");
    }

    public function marketing_report_cvs_detail(Request $request)
    {

        if ($request->ajax()) {
            $from = $request->tgl_awal;
            $to = $request->tgl_akhir;

            $data = $this->get_marketing_cvs_detail_data($from, $to);

            return DataTables::of($data)->toJson();
        }


        // For non-AJAX (initial page load)
        return view('marketing.report.marketing_cvs_detail', [
            'page' => 'dashboard-marketing',
            'subPageGroup' => 'marketing-report',
            'subPage' => 'marketing-report-cvs-detail',
            'containerFluid' => true,
        ]);
    }

    public function export_excel_marketing_cvs_detail(Request $request)
    {
        $from = $request->tgl_awal;
        $to = $request->tgl_akhir;

        $data = $this->get_marketing_cvs_detail_data($from, $to);
        $rows = array_map(fn($r) => (array) $r, $data);

        $excel = FastExcel::create('CVS Detail');
        $sheet = $excel->getSheet();
        $sheet->setColFormat('AR', '0.0000'); // kolom "Sales Price - Total Cost": 4 angka di belakang koma

        $fmtDate = fn($d) => $d ? Carbon::parse($d)->format('d-m-Y') : '';

        // Judul & periode laporan, rata tengah
        $sheet->writeRow(['LIST DATA COSTING DETAIL'])->applyFontStyleBold()->applyFontSize(16)->applyTextCenter();
        $sheet->writeRow(['']);
        $sheet->writeRow(["Periode : {$fmtDate($from)} - {$fmtDate($to)}"])->applyFontStyleBold()->applyTextCenter();

        // Baris 4: header grup (No..Curr menyatu vertikal, lalu grup "Sales Order", "Costing Breakdown", dan "Sales Price - Total Cost")
        $sheet->writeRow(array_merge(
            ['No', 'WS', 'Buyer', 'Style', 'Product Type', 'Season', 'Marketing Order', 'Curr'],
            ['Sales Order', '', '', ''],
            array_merge(['Costing Breakdown'], array_fill(0, 30, '')),
            ['Sales Price - Total Cost'],
        ))->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->applyTextCenter();

        // Baris 5: sub header detail per kolom
        $sheet->writeRow(array_merge(
            array_fill(0, 8, ''),
            ['SO Date', 'Status SO', 'Qty SO', 'Sales Price'],
            [
                'CT Date', 'Status', 'Qty', 'Fabric', 'Acc Sewing', 'Acc Packing', 'Total Material',
                'CMT', 'Embrodery', 'Washing', 'Printing', 'Wrapped Button', 'Complexity Makloon Button',
                'Label Print', 'Laser Cutting', 'Total Manufaturing', 'Development', 'Overhead', 'Marketing',
                'Shipping', 'Import', 'Handing', 'Testing', 'Fabric Handling', 'Service Charge',
                'Clearance Cost', 'Unexpected Cost', 'Management Fee', 'Profit', 'Total Others', 'Total Cost',
            ],
            [''],
        ))->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->applyTextCenter();

        for ($i = 1; $i <= 8; $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->mergeCells("{$col}4:{$col}5");
        }
        $sheet->mergeCells('I4:L4');
        $sheet->mergeCells('M4:AQ4');
        $sheet->mergeCells('AR4:AR5');

        // Autowidth kolom mulai dari sini supaya lebar kolom mengikuti isi header/data,
        // bukan ikut melebar karena judul laporan & periode di baris 1 & 3 yang cuma ada di kolom A.
        $sheet->setColWidthAuto('A:AR');

        $no = 1;
        foreach ($rows as $r) {
            $ttlMaterial = $r['ttl_material'] ?? 0;
            $ttlManufacturing = $r['ttl_manufacturing'] ?? 0;
            $ttlOthers = $r['ttl_others'] ?? 0;
            $totalCost = $ttlMaterial + $ttlManufacturing + $ttlOthers;
            $priceSo = $r['price_so'] ?? 0;

            $sheet->writeRow([
                $no++,
                $r['kpno'] ?? '',
                $r['supplier'] ?? '',
                $r['styleno'] ?? '',
                $r['product_item'] ?? '',
                $r['season_desc'] ?? '',
                $r['mkt_order'] ?? '',
                $r['curr'] ?? '',
                $fmtDate($r['so_date'] ?? null),
                $r['status'] ?? '',
                $r['qty_so'] ?? 0,
                $priceSo,
                $fmtDate($r['cost_date'] ?? null),
                $r['status_cost'] ?? '',
                $r['qty_cost'] ?? 0,
                $r['ttl_fabric'] ?? 0,
                $r['ttl_accsew'] ?? 0,
                $r['ttl_accpack'] ?? 0,
                $ttlMaterial,
                $r['ttl_cmt'] ?? 0,
                $r['ttl_embro'] ?? 0,
                $r['ttl_wash'] ?? 0,
                $r['ttl_print'] ?? 0,
                $r['ttl_wrapbut'] ?? 0,
                $r['ttl_compbut'] ?? 0,
                $r['ttl_label'] ?? 0,
                $r['ttl_laser'] ?? 0,
                $ttlManufacturing,
                $r['ttl_develop'] ?? 0,
                $r['ttl_overhead'] ?? 0,
                $r['ttl_market'] ?? 0,
                $r['ttl_shipp'] ?? 0,
                $r['ttl_import'] ?? 0,
                $r['ttl_handl'] ?? 0,
                $r['ttl_test'] ?? 0,
                $r['ttl_fabhandl'] ?? 0,
                $r['ttl_service'] ?? 0,
                $r['ttl_clearcost'] ?? 0,
                $r['ttl_unexcost'] ?? 0,
                $r['ttl_managementfee'] ?? 0,
                $r['ttl_profit'] ?? 0,
                $ttlOthers,
                $totalCost,
                $priceSo - $totalCost,
            ])->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        $filename = "Laporan Costing vs SO Detail {$from} sd {$to}.xlsx";

        // FastExcel::download() echo file langsung via header()+readfile() tanpa mengembalikan Response,
        // sehingga Laravel ikut mengirim response kosong di belakangnya & merusak isi file xlsx.
        // Simpan ke temp file lalu kirim lewat response()->download() bawaan Laravel supaya bersih.
        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('xlsx_export_') . '.xlsx';
        $excel->save($tmpFile);

        return response()->download($tmpFile, $filename)->deleteFileAfterSend(true);
    }
}
