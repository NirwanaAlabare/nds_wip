<?php

namespace App\Http\Livewire\Sewing;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\UserPassword;
use App\Models\SignalBit\UserSbWip;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\Reject;
use DB;

class ReportLineUser extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = "";
    public $date;
    public $dateFrom;
    public $dateTo;
    public $period;

    public function mount()
    {
        $this->date = date('Y-m-d');
        $this->dateFrom = date('Y-m-d');
        $this->dateTo = date('Y-m-d');
        $this->group = 'line';
        $this->period = 'daily';
    }

    public function filter($group, $period)
    {
        $this->period = $period;
    }

    public function render()
    {
        $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
        $years = [2024, 2023, 2022, 2021, 2020, 2019, 2018, 2017, 2016, 2015, 2014, 2013, 2012, 2011, 2010, 2009, 2008, 2007, 2006, 2005, 2004, 2003, 2002, 2001, 2000, 1999];

        $masterPlans = MasterPlan::all();

        $lines = UserSbWip::selectRaw("
                userpassword.username,
                user_sb_wip.name,
                user_sb_wip.line_id,
                SUM((IFNULL(rfts.rft, 0)+IFNULL(reworks.rework, 0))) total_actual,
                GREATEST(IFNULL(MAX(rfts.last_rft), '".$this->date." 00:00:00'), IFNULL(MAX(defects.last_defect), '".$this->date." 00:00:00'), IFNULL(MAX(reworks.last_rework), '".$this->date." 00:00:00'), IFNULL(MAX(rejects.last_reject), '".$this->date." 00:00:00')) latest_output
            ")->
            leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
            leftJoin(DB::raw("(SELECT max(rfts.updated_at) last_rft, count(rfts.id) rft, rfts.created_by from output_rfts rfts inner join master_plan on master_plan.id = rfts.master_plan_id where DATE(rfts.updated_at) = '".$this->date."' and master_plan.tgl_plan = '".$this->date."' and status = 'NORMAL' GROUP BY rfts.created_by) as rfts"), "user_sb_wip.id", "=", "rfts.created_by")->
            leftJoin(DB::raw("(SELECT max(defects.updated_at) last_defect, defects.created_by from output_defects defects inner join master_plan on master_plan.id = defects.master_plan_id where defects.defect_status = 'defect' and DATE(defects.updated_at) = '".$this->date."' and master_plan.tgl_plan = '".$this->date."' GROUP BY defects.created_by) as defects"), "user_sb_wip.id", "=", "defects.created_by")->
            leftJoin(DB::raw("(SELECT max(defrew.updated_at) last_rework, count(defrew.id) rework, reworks.created_by from output_defects defrew inner join master_plan on master_plan.id = defrew.master_plan_id inner join output_reworks reworks on reworks.defect_id = defrew.id where defrew.defect_status = 'reworked' and DATE(defrew.updated_at) = '".$this->date."' and master_plan.tgl_plan = '".$this->date."' GROUP BY reworks.created_by) as reworks"), "user_sb_wip.id", "=", "reworks.created_by")->
            leftJoin(DB::raw("(SELECT max(rejects.updated_at) last_reject, rejects.created_by from output_rejects rejects inner join master_plan on master_plan.id = rejects.master_plan_id where DATE(rejects.updated_at) = '".$this->date."' and master_plan.tgl_plan = '".$this->date."' GROUP BY rejects.created_by) as rejects"), "user_sb_wip.id", "=", "rejects.created_by")->
            where("userpassword.Groupp", 'SEWING')->
            whereRaw("(userpassword.Locked != 1 OR userpassword.Locked IS NULL)")->
            whereRaw("(user_sb_wip.locked != 'locked' OR user_sb_wip.locked IS NULL)")->
            whereRaw("(
                userpassword.username LIKE '%".$this->search."%' OR
                userpassword.FullName LIKE '%".$this->search."%' OR
                user_sb_wip.name LIKE '%".$this->search."%'
            )")->
            groupBy("user_sb_wip.id")->get();

        return view('livewire.sewing.report-line-user', [
            'masterPlans' => $masterPlans,
            'lines' => $lines,
            'months' => $months,
            'years' => $years,
        ]);
    }
}
