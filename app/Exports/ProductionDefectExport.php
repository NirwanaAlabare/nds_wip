<?php

namespace App\Exports;

use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\UserLine;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\Reject;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithCharts;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use DB;

class ProductionDefectExport implements FromView, ShouldAutoSize, WithCharts
{
    protected $date;
    protected $selectedLine;

    function __construct($date, $selectedLine) {
        $this->date = $date;
        $this->selectedLine = $selectedLine;
        $this->defectRowCount = 0;
        $this->topDefectRowCount = 0;
    }

    public function view(): View
    {
        ini_set('max_execution_time', 300);

        $defects = Defect::selectRaw('COALESCE(output_defects.updated_at, output_defects.created_at) waktu, kode_numbering, defect_type, defect_area, UPPER(defect_status) defect_status')->
            leftJoin("master_plan", "master_plan.id", "=","output_defects.master_plan_id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=","output_defects.defect_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=","output_defects.defect_area_id")->
            where("master_plan.cancel", 'N')->
            where("master_plan.tgl_plan", $this->date)->
            where("master_plan.sewing_line", $this->selectedLine)->
            groupBy("output_defects.id")->
            orderByRaw("COALESCE(updated_at, created_at) asc")->get();

        $defectTypes = Defect::selectRaw('defect_type_id, count(defect_type_id) as defect_type_count')->
            leftJoin("master_plan", "master_plan.id", "=","output_defects.master_plan_id")->
            where("master_plan.cancel", 'N')->
            where("master_plan.tgl_plan", $this->date)->
            where("master_plan.sewing_line", $this->selectedLine)->
            groupBy("defect_type_id")->
            orderByRaw("defect_type_count desc")->get();

        $defectTypeIds = [];
        foreach ($defectTypes as $type) {
            array_push($defectTypeIds, $type->defect_type_id);
        }

        $defectAreas = Defect::selectRaw('defect_type_id, defect_area_id, count(defect_area_id) as defect_area_count')->
            leftJoin("master_plan", "master_plan.id", "=","output_defects.master_plan_id")->
            where("master_plan.cancel", 'N')->
            where("master_plan.tgl_plan", $this->date)->
            where("master_plan.sewing_line", $this->selectedLine)->
            whereIn("defect_type_id", $defectTypeIds)->
            groupBy("defect_type_id", "defect_area_id")->
            orderByRaw("defect_area_count desc")->get();

        $this->defectRowCount = $defects->count();
        foreach ($defectTypes as $type) {
            $defectAreasFiltered = $defectAreas->where("defect_type_id", $type->defect_type_id)->take(5);
            $firstDefectAreasFiltered = $defectAreasFiltered->first();

            $this->topDefectRowCount = $defectAreasFiltered->count();
        }


        return view('sewing.export.production-export-defect', [
            'defects' => $defects,
            'defectTypes' => $defectTypes,
            'defectAreas' => $defectAreas,
            'date' => $this->date,
            'selectedLine' => $this->selectedLine
        ]);
    }

    public function charts()
    {

        $label      = [new DataSeriesValues('String', 'Worksheet!$B$'.($event->getConcernable()->defectRowCount+6), null, 1)];
        $categories = [new DataSeriesValues('String', 'Worksheet!$B$'.($event->getConcernable()->defectRowCount+7).':$B$'.($event->getConcernable()->defectRowCount+7+$event->getConcernable()->topDefectRowCount), null, 4)];
        $values     = [new DataSeriesValues('Number', 'Worksheet!$C$'.($event->getConcernable()->defectRowCount+7).':$C$'.($event->getConcernable()->defectRowCount+7+$event->getConcernable()->topDefectRowCount), null, 4)];

        $series = new DataSeries(DataSeries::TYPE_PIECHART, DataSeries::GROUPING_STANDARD, range(0, \count($values) - 1), $label, $categories, $values);
        $plot   = new PlotArea(null, [$series]);

        $legend = new Legend();
        $chart  = new Chart('Defect Chart', new Title('Defect Chart'), $legend, $plot);

        return $chart;
    }
}
