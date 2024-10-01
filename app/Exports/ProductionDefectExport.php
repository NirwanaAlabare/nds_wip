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
use DB;

class ProductionDefectExport implements FromView, ShouldAutoSize, ShouldQueue
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

        return view('sewing.export.production-export-defect', [
            'defects' => $defects,
            'defectTypes' => $defectTypes,
            'defectAreas' => $defectAreas,
            'date' => $this->date,
            'selectedLine' => $this->selectedLine
        ]);
    }
}
