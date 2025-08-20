<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\SignalBit\MasterPlan;

class LineMasterplan extends Component
{
    public $date;
    public $masterPlan;
    public $lineRow;
    public $baseUrl;

    public function mount()
    {
        $this->date = date('Y-m-d');
        $this->baseUrl = url('/');
    }

    public function render()
    {
        $this->masterPlan = MasterPlan::where("tgl_plan", $this->date)->orderBy("sewing_line", "asc")->where("cancel", "N")->get();
        $this->lineRow = MasterPlan::selectRaw("sewing_line, COUNT(id) total_row, GROUP_CONCAT(jam_kerja), SUM(jam_kerja) total_jam, SUM(plan_target) total_target")->where("tgl_plan", $this->date)->where("cancel", "N")->groupBy("sewing_line", "tgl_plan")->get();

        return view('livewire.line-masterplan');
    }
}
