<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use App\Models\Cutting\FormCutInputDetail;
use DB;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

class ExportPemakaianKain implements FromView, WithEvents, ShouldAutoSize /*WithColumnWidths,*/
{
    use Exportable;

    protected $dateFrom;
    protected $dateTo;
    protected $rowCount;

    public function __construct($dateFrom, $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->rowCount = 0;
    }

    public function view(): View
    {
        $dateFrom = $this->dateFrom ? $this->dateFrom : date('Y-m-d');
        $dateTo = $this->dateTo ? $this->dateTo : date('Y-m-d');

        $data = collect();

        $requestRoll = DB::connection("mysql_sb")->select("
            select a.*,b.no_bppb no_out, COALESCE(total_roll,0) roll_out, ROUND(COALESCE(qty_out,0), 2) qty_out, c.no_dok no_retur, COALESCE(total_roll_ri,0) roll_retur, ROUND(COALESCE(qty_out_ri,0), 2) qty_retur from (select bppbno,bppbdate,s.supplier tujuan,ac.kpno no_ws,ac.styleno,ms.supplier buyer,a.id_item,
            REPLACE(mi.itemdesc, '\"', '\\\\\"') itemdesc,a.qty qty_req,a.unit, idws_act no_ws_aktual
            from bppb_req a inner join mastersupplier s on a.id_supplier=s.id_supplier
            inner join jo_det jod on a.id_jo=jod.id_jo
            inner join so on jod.id_so=so.id
            inner join act_costing ac on so.id_cost=ac.id
            inner join mastersupplier ms on ac.id_buyer=ms.id_supplier
            inner join masteritem mi on a.id_item=mi.id_item
            where bppbno like '%RQ-F%' and a.id_supplier = '432' and bppbdate between '".$dateFrom."' and '".$dateTo."'
            group by a.id_item,a.bppbno
            order by bppbdate,bppbno desc) a left join
            (select a.no_bppb,no_req,id_item,COUNT(id_roll) total_roll, sum(qty_out) qty_out,satuan from whs_bppb_h a INNER JOIN (select bppbno,bppbdate from bppb_req where bppbno like '%RQ-F%' and id_supplier = '432' and bppbdate between '".$dateFrom."' and '".$dateTo."' GROUP BY bppbno) b on b.bppbno = a.no_req inner join whs_bppb_det c on c.no_bppb = a.no_bppb where a.status != 'Cancel' and c.status = 'Y' GROUP BY a.no_bppb,no_req,id_item) b on b.no_req = a.bppbno and b.id_item = a.id_item left join
            (select a.no_dok, no_invoice no_req,id_item,COUNT(no_barcode) total_roll_ri, sum(qty_sj) qty_out_ri,satuan from (select * from whs_inmaterial_fabric where no_dok like '%RI%' and supplier = 'Production - Cutting' ) a INNER JOIN (select bppbno,bppbdate from bppb_req where bppbno like '%RQ-F%' and id_supplier = '432' and bppbdate between '".$dateFrom."' and '".$dateTo."' GROUP BY bppbno) b on b.bppbno = a.no_invoice INNER JOIN whs_lokasi_inmaterial c on c.no_dok = a.no_dok GROUP BY a.no_dok,no_invoice,id_item) c on c.no_req = a.bppbno and c.id_item = a.id_item
        ");

        foreach ($requestRoll as $req) {
            $rollIdsArr = collect(DB::connection("mysql_sb")->select("select id_roll from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb WHERE a.no_req = '".$req->bppbno."' and b.id_item = '".$req->id_item."' and b.status = 'Y' GROUP BY id_roll"));

            $rollIds = $rollIdsArr->pluck('id_roll')->map(function ($item) {
                return "'" . $item . "'";
            })->implode(', ');

            // $rolls = FormCutInputDetail::selectRaw("
            //         id_roll,
            //         id_item,
            //         detail_item,
            //         lot,
            //         COALESCE(roll_buyer, roll) roll,
            //         MAX(qty) qty,
            //         ROUND(MIN(CASE WHEN status != 'extension' AND status != 'extension complete' THEN (sisa_kain) ELSE (qty - total_pemakaian_roll) END), 2) sisa_kain,
            //         unit,
            //         ROUND(SUM(total_pemakaian_roll), 2) total_pemakaian_roll,
            //         ROUND(SUM(short_roll), 2) total_short_roll_2,
            //         ROUND((SUM(total_pemakaian_roll) + MIN(CASE WHEN status != 'extension' AND status != 'extension complete' THEN (sisa_kain) ELSE (qty - total_pemakaian_roll) END)) - MAX(qty), 2) total_short_roll
            //     ")->
            //     whereNotNull("id_roll")->
            //     whereIn("id_roll", $rollIds)->
            //     whereIn("status", ['complete', 'need extension', 'extension complete'])->
            //     groupBy("id_item", "id_roll")->
            //     get();

            $rolls = collect(DB::select("
                SELECT
                    req.id_roll,
                    req.id_item,
                    req.item_desc detail_item,
                    req.no_lot lot,
                    req.styleno,
                    req.color,
                    COALESCE(roll.roll, req.no_roll) roll,
                    COALESCE(roll.qty, req.qty_out) qty,
                    (CASE WHEN roll.sisa_kain > 0 THEN COALESCE(roll.sisa_kain, 0) - COALESCE(piping.piping, 0) ELSE COALESCE(roll.qty, req.qty_out) END) as sisa_kain,
                    COALESCE(roll.unit, piping.unit, req.satuan) unit,
                    COALESCE(roll.total_pemakaian_roll, 0) + COALESCE(piping.piping, 0) as total_pemakaian_roll,
                    COALESCE(roll.total_short_roll_2, 0) total_short_roll_2,
                    COALESCE(roll.total_short_roll, 0) total_short_roll
                FROM (
                    select b.*, c.color, tmpjo.styleno from signalbit_erp.whs_bppb_h a INNER JOIN signalbit_erp.whs_bppb_det b on b.no_bppb = a.no_bppb LEFT JOIN signalbit_erp.masteritem c ON c.id_item = b.id_item left join (select id_jo,kpno,styleno from signalbit_erp.act_costing ac inner join signalbit_erp.so on ac.id=so.id_cost inner join signalbit_erp.jo_det jod on signalbit_erp.so.id=jod.id_so group by id_jo) tmpjo on tmpjo.id_jo=b.id_jo WHERE a.no_req = '".$req->bppbno."' and b.id_item = '".$req->id_item."' and b.status = 'Y' GROUP BY id_roll
                ) req
                LEFT JOIN (
                    select
                        id_roll,
                        id_item,
                        detail_item,
                        lot,
                        COALESCE(roll_buyer, roll) roll,
                        MAX(qty) qty,
                        ROUND(MIN(CASE WHEN status != 'extension' AND status != 'extension complete' THEN (sisa_kain) ELSE (qty - total_pemakaian_roll) END), 2) sisa_kain,
                        unit,
                        ROUND(SUM(total_pemakaian_roll), 2) total_pemakaian_roll,
                        ROUND(SUM(short_roll), 2) total_short_roll_2,
                        ROUND((SUM(total_pemakaian_roll) + MIN(CASE WHEN status != 'extension' AND status != 'extension complete' THEN (sisa_kain) ELSE (qty - total_pemakaian_roll) END)) - MAX(qty), 2) total_short_roll
                    from
                        laravel_nds.form_cut_input_detail
                    WHERE
                        `status` in ('complete', 'need extension', 'extension complete')
                        ".($rollIds ? "and id_roll in (".$rollIds.")" : "")."
                    GROUP BY
                        id_item,
                        id_roll
                UNION ALL
                    SELECT
                        id_roll,
                        id_item,
                        detail_item,
                        lot,
                        COALESCE ( roll_buyer, roll ) roll,
                        SUM( form_cut_piece_detail.qty ) qty,
                        MIN( form_cut_piece_detail.qty_sisa ) sisa_kain,
                        qty_unit as unit,
                        ROUND( SUM( form_cut_piece_detail.qty_pemakaian ) ) total_pemakaian_roll,
                        ROUND( SUM( (form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa) - form_cut_piece_detail.qty ) ) total_short_roll_2,
                        ROUND( SUM( (form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa) - form_cut_piece_detail.qty ) ) total_short_roll
                    FROM
                        `form_cut_piece_detail`
                    WHERE
                        `status` = 'complete'
                        ".($rollIds ? "and id_roll in (".$rollIds.")" : "")."
                    GROUP BY
                        `id_item`,
                        `id_roll`
                ) roll ON req.id_roll = roll.id_roll
                left join (
                    select
                        id_roll,
                        form_cut_piping.unit,
                        SUM(form_cut_piping.qty) qty,
                        SUM(form_cut_piping.piping) piping
                    from
                        form_cut_piping
                    where
                        id_roll IS NOT NULL
                        ".($rollIds ? "and id_roll in (".$rollIds.")" : "")."
                    group by
                        id_roll
                ) piping on piping.id_roll = req.id_roll
            "));

            if ($rolls->count() > 0) {
                $rolls->map(function ($roll) use ($req) {
                    $roll->tanggal_req = $req->bppbdate;
                    $roll->no_req = $req->bppbno;
                    $roll->no_out = $req->no_out;
                    $roll->no_ws = $req->no_ws;
                    $roll->no_ws_aktual = $req->no_ws_aktual;
                });

                $data->push($rolls);
            }
        }

        $data = $data->flatten(1);

        $this->rowCount = $data->count();

        return view('cutting.report.export.pemakaian-roll', [
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'data' => $data
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => [self::class, 'afterSheet']
        ];
    }

    public static function afterSheet(AfterSheet $event)
    {
        $currentRow = 1;

        $event->sheet->styleCells(
            'A3:R' . ($event->getConcernable()->rowCount+2+1),
            [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ]
        );
    }

    // public function columnWidths(): array
    // {
    //     return [
    //         'A' => 15,
    //         'C' => 15,
    //         'D' => 15,
    //         'E' => 15,
    //         'G' => 25,
    //     ];
    // }
}
