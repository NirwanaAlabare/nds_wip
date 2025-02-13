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
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithTitle;
use DB;

class ProductionExport implements FromView, ShouldAutoSize, ShouldQueue, WithTitle
{
    protected $date;
    protected $selectedLine;

    function __construct($date, $selectedLine) {
        $this->date = $date;
        $this->selectedLine = $selectedLine;
    }

    public function view(): View
    {
        ini_set('max_execution_time', 300);

        $lines = UserLine::with('masterPlans')->where('username', $this->selectedLine)->first();

        $hours = array(
            "08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00"
        );

        $defectTypes = Defect::selectRaw('defect_type_id, count(defect_type_id) as defect_type_count')->
            leftJoin("master_plan", "master_plan.id", "=","output_defects.master_plan_id")->
            where("master_plan.cancel", 'N')->
            where("master_plan.tgl_plan", $this->date)->
            where("master_plan.sewing_line", $this->selectedLine)->
            groupBy("defect_type_id")->
            orderByRaw("defect_type_count desc")->limit(5)->get();

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

        return view('sewing.export.production-export', [
            'lines' => $lines,
            'hours' => $hours,
            'defectTypes' => $defectTypes,
            'defectAreas' => $defectAreas,
            'date' => $this->date,
            'selectedLine' => $this->selectedLine
        ]);
    }

    public function title(): string
    {
        return $this->selectedLine;
    }
}
