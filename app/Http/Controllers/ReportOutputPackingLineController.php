<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use \avadim\FastExcelLaravel\Excel as FastExcel;

class ReportOutputPackingLineController extends Controller
{
    // Output packing line per buyer/ws/style/color/size/tipe/tanggal (dipakai datatable & export)
    private function outputQuery($tglAwal, $tglAkhir, $tipe, $buyer)
    {
        $tipe = strtolower((string) $tipe);

        if ($tipe == '') {
            return DB::table(DB::raw("(SELECT 1 as dummy) as results"))->whereRaw('1 = 0');
        }

        $start = $tglAwal . ' 00:00:00';
        $end = $tglAkhir . ' 23:59:59';

        return DB::query()
            ->fromRaw("
                (
                    select so_det_id, buyer, ws, styleno, color, size, type, tgl, SUM(jumlah) jumlah from (SELECT
                        so_det_id,
                        mb.buyer,
                        mb.ws,
                        mb.styleno,
                        mb.color,
                        mb.size,
                        a.type,
                        DATE(updated_at) AS tgl,
                        COUNT(*) AS jumlah
                    FROM signalbit_erp.output_rfts_packing_po a
                    INNER JOIN signalbit_erp.master_plan mp ON a.master_plan_id = mp.id
                    LEFT JOIN (
                        SELECT
                        sd.id as id_so_det,
                        ac.kpno as ws,
                        supplier as buyer,
                        styleno,
                        color,
                        size,
                        dest
                        FROM signalbit_erp.so_det sd
                        INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                        INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                        INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                        INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                        WHERE jd.cancel = 'N'
                    ) mb on a.so_det_id = mb.id_so_det
                    WHERE
                        updated_at >= ?
                        AND updated_at <= ?
                    GROUP BY so_det_id, a.type, DATE(updated_at)

                    UNION ALL

                    select '-' so_det_id, buyer, ws, styleno, color, size, 'rft' type, tgl_saldo tgl, COALESCE(packing_rft,0) jumlah from signalbit_erp.inject_mutasi_sewing where type_saldo = 'PACKING' and tgl_saldo >= ? AND tgl_saldo <= ?) a GROUP BY buyer, ws, styleno, color, size, type, tgl
                ) as results
            ", [$start, $end, $start, $end])
            ->where('results.type', $tipe)
            ->when($buyer, function ($query) use ($buyer) {
                return $query->where('results.buyer', $buyer);
            });
    }

    public function index(Request $request){

        if ($request->ajax()) {
            $data = $this->outputQuery($request->dateFrom, $request->dateTo, $request->tipe, $request->buyer);

            return DataTables::queryBuilder($data)->make(true);
        }

        $buyer = DB::connection('mysql_sb')
            ->table('mastersupplier')
            ->select('supplier')
            ->orderBy('supplier', 'ASC')
            ->get();

        return view("packing.report_output_packing_line", [
            'page' => 'dashboard-packing',
            "subPageGroup" => "packing-report",
            "subPage" => "report-output-packing-line",
            'containerFluid' => true,
            "buyer" => $buyer
        ]);
    }

    public function export(Request $request) {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 3600);

        $from = $request->from;
        $to = $request->to;
        $tipe = $request->tipe;
        $buyer = $request->buyer;

        $data = $this->outputQuery($from, $to, $tipe, $buyer)->get();

        $excel = FastExcel::create('report-output-packing-line');
        $sheet = $excel->sheet();

        // Judul & keterangan filter
        $sheet->writeRow(['Report Output Packing Line'], ['font-style' => 'bold', 'font-size' => 14]);
        $sheet->writeRow(['Periode : ' . date('d-m-Y', strtotime($from)) . ' s/d ' . date('d-m-Y', strtotime($to))], ['font-style' => 'bold']);
        $sheet->writeRow(['Tipe : ' . $tipe], ['font-style' => 'bold']);
        $sheet->writeRow(['Buyer : ' . (empty($buyer) ? 'Semua Buyer' : $buyer)], ['font-style' => 'bold']);
        $sheet->writeRow(['']);

        $sheet->writeRow(
            ['Buyer', 'WS', 'Style', 'Color', 'Size', 'Jumlah'],
            [
                'font-style' => 'bold',
                'border'     => 'thin',
                'text-align'     => 'center',
                'vertical-align' => 'center',
                'fill'           => '#D9EDF7',
            ]
        );

        foreach ($data as $row) {
            $sheet->writeRow(
                [
                    $row->buyer,
                    $row->ws,
                    $row->styleno,
                    $row->color,
                    // Size disimpan sebagai teks (contoh: "08" tidak berubah jadi 8)
                    (string) $row->size,
                    (float) $row->jumlah,
                ],
                ['border' => 'thin']
            );
        }

        foreach (range('A', 'F') as $col) {
            $sheet->setColWidth($col, 20);
        }

        return $excel->download('report-output-packing-line.xlsx');
    }
}
