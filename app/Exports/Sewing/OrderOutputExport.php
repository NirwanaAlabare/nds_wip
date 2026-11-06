<?php

namespace App\Exports\Sewing;

use App\Models\SignalBit\MasterPlan;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Services\SewingOrderOutputService;
use DB;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

class OrderOutputExport extends DefaultValueBinder implements FromView, WithEvents, ShouldAutoSize, WithCustomValueBinder
{
    protected $dateFrom;
    protected $dateTo;
    protected $outputType;
    protected $groupBy;
    protected $order;
    protected $buyer;
    protected $color;
    protected $line;
    protected $size;
    protected $colAlphabet;
    protected $rowCount;

    function __construct($dateFrom, $dateTo, $outputType, $groupBy, $order, $buyer, $color = null, $line = null, $size = null) {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->outputType = SewingOrderOutputService::normalizeOutputType($outputType);
        $this->groupBy = $groupBy;
        $this->order = $order;
        $this->buyer = $buyer;
        $this->color = $color;
        $this->line = $line;
        $this->size = $size;
        $this->colAlphabet = '';
        $this->rowCount = 0;
    }

    protected $data;

    // WS (A), Style (B), Color (C) & Size (E) as text, data rows start after heading row 3
    protected function textColumns()
    {
        return $this->groupBy == 'size' ? ['A', 'B', 'C', 'E'] : ['A', 'B', 'C'];
    }

    public function bindValue(Cell $cell, $value)
    {
        if (in_array($cell->getColumn(), $this->textColumns(), true) && $cell->getRow() > 3 && $cell->getRow() < $this->rowCount && $value !== null) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function view(): View
    {
        $supplier = DB::connection('mysql_sb')->table('mastersupplier')->
            selectRaw('Id_Supplier as id, Supplier as name')->
            leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->
            where('mastersupplier.tipe_sup', 'C')->
            where('status', '!=', 'CANCEL')->
            where('type_ws', 'STD')->
            where('cost_date', '>=', '2023-01-01')->
            where('Id_Supplier', $this->buyer)->
            orderBy('Supplier', 'ASC')->
            groupBy('Id_Supplier', 'Supplier')->
            first();

        // Query sama dengan layar Track Order Output (Livewire TrackOrderOutput)
        $sewingOrderOutputService = new SewingOrderOutputService();
        $filter = [
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'outputType' => $this->outputType,
            'groupBy' => $this->groupBy,
            'order' => $this->order,
            'buyer' => $this->buyer,
            'color' => $this->color,
            'line' => $this->line,
            'size' => $this->size,
        ];

        $orderGroup = $sewingOrderOutputService->orderGroup($filter);
        $orderOutputs = $sewingOrderOutputService->orderOutputs($filter);

        $this->rowCount = $orderGroup->count() + 4;
        $this->data = $orderGroup;
        $alphabets = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
        $colCount = $orderOutputs->groupBy("tanggal")->count() + ($this->groupBy == "size" ? 5 : 4);
        if ($colCount > (count($alphabets)-1)) {
            $colStack = floor($colCount/(count($alphabets)-1));
            $colStackModulo = $colCount%(count($alphabets)-1);
            $this->colAlphabet = $alphabets[$colStack-1].$alphabets[($colStackModulo > 0 ? $colStackModulo - 1 : $colStackModulo)];
        } else {
            $this->colAlphabet = $alphabets[$colCount];
        }

        // Pre-aggregation
        [
            "dates" => $dates,
            "outputMap" => $outputMap,
            "rowTotals" => $rowTotals,
            "dateTotals" => $dateTotals,
            "grandTotal" => $grandTotal,
        ] = $sewingOrderOutputService->aggregate($orderOutputs, $this->groupBy);

        return view('sewing.export.order-output-export', [
            'order' => $this->order,
            'buyer' => $this->buyer,
            'buyerName' => $supplier ? $supplier->name : null,
            'groupBy' => $this->groupBy,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'outputType' => $this->outputType,
            'orderGroup' => $orderGroup,
            'orderOutputs' => $orderOutputs,
            //
            "dates" => $dates,
            "outputMap" => $outputMap,
            "rowTotals" => $rowTotals,
            "dateTotals" => $dateTotals,
            "grandTotal" => $grandTotal,
        ]);
    }

    public function columnFormats(): array
    {
        return [
            //
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => [self::class, 'afterSheet']
        ];
    }

    public static function afterSheet(AfterSheet $event)
    {
        $export = $event->getConcernable();
        $sheet = $event->sheet->getDelegate();

        // HTML reader collapses multiple spaces, rewrite text columns from original data
        $fields = ['A' => 'ws', 'B' => 'style', 'C' => 'color'];
        if ($export->groupBy == 'size') $fields['E'] = 'size';

        $row = 4;
        foreach ($export->data ?? [] as $item) {
            foreach ($fields as $col => $field) {
                if ($item->$field !== null) {
                    $sheet->setCellValueExplicit($col . $row, (string) $item->$field, DataType::TYPE_STRING);
                    $sheet->getStyle($col . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                }
            }
            $row++;
        }

        $event->sheet->styleCells(
            'A3:' . $event->getConcernable()->colAlphabet . $event->getConcernable()->rowCount,
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
}
