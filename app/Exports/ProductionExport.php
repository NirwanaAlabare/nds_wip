<?php

namespace App\Exports;

use App\Models\SignalBit\Defect;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\Reject;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\UserLine;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ProductionExport implements FromView, ShouldAutoSize, ShouldQueue, WithTitle, WithEvents
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

        $timeFrom = $this->date.' 00:00:00';
        $timeTo = $this->date.' 23:59:59';

        // Sama dengan App\Http\Livewire\Sewing\ReportProduction: master plan yang tgl_plan-nya
        // = tanggal terpilih (untuk target / man power) DITAMBAH master plan tanggal berapapun
        // yang punya output di tanggal terpilih, supaya total actual sama dengan Report Output.
        $lines = UserLine::with([
            "masterPlans" => function ($query) use ($timeFrom, $timeTo) {
                $query->where(function ($query) use ($timeFrom, $timeTo) {
                    $query->where('master_plan.tgl_plan', $this->date)->
                        orWhereIn('master_plan.id', function ($sub) use ($timeFrom, $timeTo) {
                            $sub->select('master_plan_id')->from('output_rfts')->whereBetween('updated_at', [$timeFrom, $timeTo]);
                        })->
                        orWhereIn('master_plan.id', function ($sub) use ($timeFrom, $timeTo) {
                            $sub->select('master_plan_id')->from('output_defects')->whereBetween('updated_at', [$timeFrom, $timeTo]);
                        })->
                        orWhereIn('master_plan.id', function ($sub) use ($timeFrom, $timeTo) {
                            $sub->select('master_plan_id')->from('output_rejects')->whereBetween('updated_at', [$timeFrom, $timeTo]);
                        });
                });
            },
            "masterPlans.rfts" => function ($query) use ($timeFrom, $timeTo) {
                $query->whereBetween('output_rfts.updated_at', [$timeFrom, $timeTo]);
            },
            "masterPlans.defects" => function ($query) use ($timeFrom, $timeTo) {
                $query->whereBetween('output_defects.updated_at', [$timeFrom, $timeTo]);
            },
            "masterPlans.rejects" => function ($query) use ($timeFrom, $timeTo) {
                $query->whereBetween('output_rejects.updated_at', [$timeFrom, $timeTo]);
            }
        ])->where('username', $this->selectedLine)->first();

        $hours = array(
            "08:00", "09:00", "10:00", "11:00", "12:00", "14:00", "15:00", "16:00", "17:00"
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // ==========================
                // BORDER TABEL PRODUKSI
                // ==========================
                $sheet->getStyle('A1:H13')
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // ==========================
                // BORDER TABEL TOP 5 DEFECT
                // ==========================
                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A17:E{$lastRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
            },
        ];
    }
}
