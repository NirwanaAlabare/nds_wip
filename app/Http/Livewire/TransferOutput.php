<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\SignalBit\UserLine;
use App\Models\SignalBit\MasterPlan;
use DB;

class TransferOutput extends Component
{
    public $lines;
    public $orders;

    public $fromDate;
    public $fromLine;
    public $fromMasterPlans;
    public $fromSoDet;

    public $toDate;
    public $toLine;
    public $toMasterPlans;
    public $toSoDet;

    public $fromMasterPlanOutput;
    public $toMasterPlanOutput;

    public $fromSelectedMasterPlan;
    public $toSelectedMasterPlan;

    public $loadingMasterPlan;

    public $baseUrl;

    public function mount()
    {
        $this->lines = null;
        $this->orders = null;

        // From init value
        $this->fromDate = date('Y-m-d');
        $this->fromLine = null;
        $this->fromMasterPlan = null;
        $this->fromSelectedMasterPlan = null;
        $this->fromSoDet = null;

        // To init value
        $this->toDate = date('Y-m-d');
        $this->toLine = null;
        $this->toMasterPlan = null;
        $this->toSelectedMasterPlan = null;
        $this->toSoDet = null;

        $this->kodeNumbering = null;

        $this->loadingMasterPlan = false;
        $this->baseUrl = url('/');
    }

    public function transferNumbering()
    {
        $newKodeNumbering = addQuotesAround($this->kodeNumbering);

        if ($this->fromLine && $this->fromSelectedMasterPlan && $this->toLine && $this->toSelectedMasterPlan) {
            if ($this->kodeNumbering) {
                $toUser = DB::connection("mysql_sb")->table("userpassword")->selectRaw("
                    user_sb_wip.id
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.line_id", "=", "userpassword.line_id")->
                where("userpassword.username", $this->toLine)->
                orderBy("user_sb_wip.id", "desc")->
                first();

                $messageSuccess = "";
                $messageNotFound = "";
                if ($toUser) {
                    // Transfer Output
                    $transferOutput = DB::connection("mysql_sb")->statement("
                        update output_rfts
                        left join master_plan on master_plan.id = output_rfts.master_plan_id
                        set output_rfts.master_plan_id = '".$this->toSelectedMasterPlan."', output_rfts.created_by = '".$toUser->id."'
                        where output_rfts.kode_numbering in (".$newKodeNumbering.")
                    ");

                    // Transfer Defect
                    $transferDefect = DB::connection("mysql_sb")->statement("
                        update output_defects
                        left join master_plan on master_plan.id = output_defects.master_plan_id
                        set output_defects.master_plan_id = '".$this->toSelectedMasterPlan."', output_defects.created_by = '".$toUser->id."'
                        where output_defects.kode_numbering in (".$newKodeNumbering.")
                    ");

                    // Transfer Reject
                    $transferReject = DB::connection("mysql_sb")->statement("
                        update output_rejects
                        left join master_plan on master_plan.id = output_rejects.master_plan_id
                        set output_rejects.master_plan_id = '".$this->toSelectedMasterPlan."', output_rejects.created_by = '".$toUser->id."'
                        where output_rejects.kode_numbering in (".$newKodeNumbering.")
                    ");

                    $messageSuccess .= $newKodeNumbering." <br> berhasil <br>";
                } else {
                    $messageNotFound .= "User Line '".$this->toLine."' tidak ditemukan.";
                }

                if ($messageSuccess != "") {
                    $this->emit('alert', 'success', $messageSuccess);
                }

                if ($messageNotFound != "") {
                    $this->emit('alert', 'error', $messageNotFound);
                }
            } else {
                $this->emit("alert", "warning", "Harap cantumkan kode numbering seperti contoh");
            }
        } else {
            $this->emit("alert", "warning", "Harap pilih line dan master plan dengan lengkap");
        }
    }

    public function transferAll()
    {
        if ($this->fromLine && $this->fromSelectedMasterPlan && $this->toLine && $this->toSelectedMasterPlan) {
            // From SoDet List
            $this->fromSoDet = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.sewing_line = '".$this->fromLine."'")->
            whereRaw("master_plan.id = '".$this->fromSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.color", "so_det.size")->
            get();

            // To SoDet List
            $this->toSoDet = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.sewing_line = '".$this->toLine."'")->
            whereRaw("master_plan.id = '".$this->toSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.color", "so_det.size")->
            get();

            $messageSuccess = "";
            $messageNotFound = "";
            foreach ($this->fromSoDet as $fromSoDet) {
                $toSoDet = $this->toSoDet->where("size", $fromSoDet->size)->first();

                if ($toSoDet) {
                    // Transfer Output
                    $transferOutput = DB::connection("mysql_sb")->statement("
                        update output_rfts
                        left join master_plan on master_plan.id = output_rfts.master_plan_id
                        set output_rfts.master_plan_id = '".$this->toSelectedMasterPlan."'
                        where output_rfts.master_plan_id = '".$this->fromSelectedMasterPlan."' and output_rfts.so_det_id = '".$fromSoDet->id."' and output_rfts.kode_numbering is null
                    ");
                    if ($transferOutput) {
                        $soDetOutput = DB::connection("mysql_sb")->statement("
                            update output_rfts
                            left join master_plan on master_plan.id = output_rfts.master_plan_id
                            set output_rfts.so_det_id = '".$toSoDet->id."'
                            where output_rfts.so_det_id = '".$fromSoDet->id."' and master_plan.id = '".$this->toSelectedMasterPlan."' and master_plan.sewing_line = '".$this->toLine."' and output_rfts.kode_numbering is null
                        ");
                    }

                    // Transfer Defect
                    $transferDefect = DB::connection("mysql_sb")->statement("
                        update output_defects
                        left join master_plan on master_plan.id = output_defects.master_plan_id
                        set output_defects.master_plan_id = '".$this->toSelectedMasterPlan."'
                        where output_defects.master_plan_id = '".$this->fromSelectedMasterPlan."' and output_defects.so_det_id = '".$fromSoDet->id."' and output_defects.kode_numbering is null
                    ");
                    if ($transferDefect) {
                        $soDetDefect = DB::connection("mysql_sb")->statement("
                            update output_defects
                            left join master_plan on master_plan.id = output_defects.master_plan_id
                            set output_defects.so_det_id = '".$toSoDet->id."'
                            where output_defects.so_det_id = '".$fromSoDet->id."' and master_plan.id = '".$this->toSelectedMasterPlan."' and master_plan.sewing_line = '".$this->toLine."' and output_defects.kode_numbering is null
                        ");
                    }

                    // Transfer Reject
                    $transferReject = DB::connection("mysql_sb")->statement("
                        update output_rejects
                        left join master_plan on master_plan.id = output_rejects.master_plan_id
                        set output_rejects.master_plan_id = '".$this->toSelectedMasterPlan."'
                        where output_rejects.master_plan_id = '".$this->fromSelectedMasterPlan."' and output_rejects.so_det_id = '".$fromSoDet->id."' and output_rejects.kode_numbering is null
                    ");
                    if ($transferReject) {
                        $soDetReject = DB::connection("mysql_sb")->statement("
                            update output_rejects
                            left join master_plan on master_plan.id = output_rejects.master_plan_id
                            set output_rejects.so_det_id = '".$toSoDet->id."'
                            where output_rejects.so_det_id = '".$fromSoDet->id."' and master_plan.id = '".$this->toSelectedMasterPlan."' and master_plan.sewing_line = '".$this->toLine."' and output_rejects.kode_numbering is null
                        ");
                    }

                    $messageSuccess .= $fromSoDet->size." success <br>";
                } else {
                    $messageNotFound .= $fromSoDet->size." not found <br>";
                }
            }

            if ($messageSuccess != "") {
                $this->emit('alert', 'success', $messageSuccess);
            }

            if ($messageNotFound != "") {
                $this->emit('alert', 'warning', $messageNotFound);
            }
        } else {
            $this->emit("alert", "warning", "Harap pilih line dan master plan dengan lengkap");
        }
    }

    public function transferRft()
    {
        if ($this->toSelectedMasterPlan && $this->fromLine && $this->fromSelectedMasterPlan && $this->toLine && $this->toSelectedMasterPlan) {
            // From SoDet List
            $this->fromSoDet = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.sewing_line = '".$this->fromLine."'")->
            whereRaw("master_plan.id = '".$this->fromSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.color", "so_det.size")->
            get();

            // To SoDet List
            $this->toSoDet = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.sewing_line = '".$this->toLine."'")->
            whereRaw("master_plan.id = '".$this->toSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.color", "so_det.size")->
            get();

            $messageSuccess = "";
            $messageNotFound = "";
            foreach ($this->fromSoDet as $fromSoDet) {
                $toSoDet = $this->toSoDet->where("size", $fromSoDet->size)->first();

                if ($toSoDet) {
                    // Transfer Output
                    $transferOutput = DB::connection("mysql_sb")->statement("
                        update output_rfts
                        left join master_plan on master_plan.id = output_rfts.master_plan_id
                        set output_rfts.master_plan_id = '".$this->toSelectedMasterPlan."'
                        where output_rfts.master_plan_id = '".$this->fromSelectedMasterPlan."' and output_rfts.so_det_id = '".$fromSoDet->id."' and output_rfts.status != 'REWORK' and output_rfts.rework_id is null and output_rfts.kode_numbering is null
                    ");
                    if ($transferOutput) {
                        $soDetOutput = DB::connection("mysql_sb")->statement("
                            update output_rfts
                            left join master_plan on master_plan.id = output_rfts.master_plan_id
                            set output_rfts.so_det_id = '".$toSoDet->id."'
                            where output_rfts.so_det_id = '".$fromSoDet->id."' and master_plan.id = '".$this->toSelectedMasterPlan."' and master_plan.sewing_line = '".$this->toLine."' and output_rfts.status != 'REWORK' and output_rfts.rework_id is null and output_rfts.kode_numbering is null
                        ");
                    }

                    $messageSuccess .= $fromSoDet->size." success <br>";
                } else {
                    $messageNotFound .= $fromSoDet->size." not found <br>";
                }
            }

            if ($messageSuccess != "") {
                $this->emit('alert', 'success', $messageSuccess);
            }

            if ($messageNotFound != "") {
                $this->emit('alert', 'warning', $messageNotFound);
            }
        } else {
            $this->emit("alert", "warning", "Harap pilih line dan master plan dengan lengkap");
        }
    }

    public function transferDefect()
    {
        if ($this->toSelectedMasterPlan && $this->fromLine && $this->fromSelectedMasterPlan && $this->toLine && $this->toSelectedMasterPlan) {
            // From SoDet List
            $this->fromSoDet = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.sewing_line = '".$this->fromLine."'")->
            whereRaw("master_plan.id = '".$this->fromSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.color", "so_det.size")->
            get();

            // To SoDet List
            $this->toSoDet = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.sewing_line = '".$this->toLine."'")->
            whereRaw("master_plan.id = '".$this->toSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.color", "so_det.size")->
            get();

            $messageSuccess = "";
            $messageNotFound = "";
            foreach ($this->fromSoDet as $fromSoDet) {
                $toSoDet = $this->toSoDet->where("size", $fromSoDet->size)->first();

                if ($toSoDet) {
                    // Transfer Defect
                    $transferDefect = DB::connection("mysql_sb")->statement("
                        update output_defects
                        left join master_plan on master_plan.id = output_defects.master_plan_id
                        set output_defects.master_plan_id = '".$this->toSelectedMasterPlan."'
                        where output_defects.master_plan_id = '".$this->fromSelectedMasterPlan."' and output_defects.so_det_id = '".$fromSoDet->id."' and output_defects.kode_numbering is null
                    ");
                    if ($transferDefect) {
                        $soDetDefect = DB::connection("mysql_sb")->statement("
                            update output_defects
                            left join master_plan on master_plan.id = output_defects.master_plan_id
                            set output_defects.so_det_id = '".$toSoDet->id."'
                            where output_defects.so_det_id = '".$fromSoDet->id."' and master_plan.id = '".$this->toSelectedMasterPlan."' and master_plan.sewing_line = '".$this->toLine."' and output_defects.kode_numbering is null
                        ");
                    }

                    // Transfer Rft/Rework
                    $transferRftRework = DB::connection("mysql_sb")->statement("
                        update output_rfts
                        left join master_plan on master_plan.id = output_rfts.master_plan_id
                        set output_rfts.master_plan_id = '".$this->toSelectedMasterPlan."'
                        where output_rfts.master_plan_id = '".$this->fromSelectedMasterPlan."' and output_rfts.so_det_id = '".$fromSoDet->id."' and master_plan.sewing_line = '".$this->toLine."' and output_rfts.status = 'REWORK' and output_rfts.rework_id is not null and output_rfts.kode_numbering is null
                    ");
                    if ($transferRftRework) {
                        $soDetRftRework = DB::connection("mysql_sb")->statement("
                            update output_rfts
                            left join master_plan on master_plan.id = output_rfts.master_plan_id
                            set output_rfts.so_det_id = '".$toSoDet->id."'
                            where output_rfts.so_det_id = '".$fromSoDet->id."' and master_plan.id = '".$this->toSelectedMasterPlan."' and master_plan.sewing_line = '".$this->toLine."' and output_rfts.status = 'REWORK' and output_rfts.rework_id is not null and output_rfts.kode_numbering is null
                        ");
                    }

                    $messageSuccess .= $fromSoDet->size." success <br>";
                } else {
                    $messageNotFound .= $fromSoDet->size." not found <br>";
                }
            }

            if ($messageSuccess != "") {
                $this->emit('alert', 'success', $messageSuccess);
            }

            if ($messageNotFound != "") {
                $this->emit('alert', 'warning', $messageNotFound);
            }
        } else {
            $this->emit("alert", "warning", "Harap pilih line dan master plan dengan lengkap");
        }
    }

    public function transferReject()
    {
        if ($this->toSelectedMasterPlan && $this->fromLine && $this->fromSelectedMasterPlan && $this->toLine && $this->toSelectedMasterPlan) {
            // From SoDet List
            $this->fromSoDet = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.sewing_line = '".$this->fromLine."'")->
            whereRaw("master_plan.id = '".$this->fromSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.color", "so_det.size")->
            get();

            // To SoDet List
            $this->toSoDet = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.sewing_line = '".$this->toLine."'")->
            whereRaw("master_plan.id = '".$this->toSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.color", "so_det.size")->
            get();

            $messageSuccess = "";
            $messageNotFound = "";
            foreach ($this->fromSoDet as $fromSoDet) {
                $toSoDet = $this->toSoDet->where("size", $fromSoDet->size)->first();

                if ($toSoDet) {
                    // Transfer Reject
                    $transferReject = DB::connection("mysql_sb")->statement("
                        update output_rejects
                        left join master_plan on master_plan.id = output_rejects.master_plan_id
                        set output_rejects.master_plan_id = '".$this->toSelectedMasterPlan."'
                        where output_rejects.master_plan_id = '".$this->fromSelectedMasterPlan."' and output_rejects.so_det_id = '".$fromSoDet->id."' and master_plan.sewing_line = '".$this->toLine."' and output_rejects.kode_numbering is null
                    ");
                    if ($transferReject) {
                        $soDetReject = DB::connection("mysql_sb")->statement("
                            update output_rejects
                            left join master_plan on master_plan.id = output_rejects.master_plan_id
                            set output_rejects.so_det_id = '".$toSoDet->id."'
                            where output_rejects.so_det_id = '".$fromSoDet->id."' and master_plan.id = '".$this->toSelectedMasterPlan."' and master_plan.sewing_line = '".$this->toLine."' and output_rejects.kode_numbering is null
                        ");
                    }

                    $messageSuccess .= $fromSoDet->size." success <br>";
                } else {
                    $messageNotFound .= $fromSoDet->size." not found <br>";
                }
            }

            if ($messageSuccess != "") {
                $this->emit('alert', 'success', $messageSuccess);
            }

            if ($messageNotFound != "") {
                $this->emit('alert', 'warning', $messageNotFound);
            }
        } else {
            $this->emit("alert", "warning", "Harap pilih line dan master plan dengan lengkap");
        }
    }

    public function render()
    {
        $this->loadingMasterPlan = false;

        $this->lines = UserLine::where("Groupp", "SEWING")->orderBy("line_id", "asc")->get();

        // Master Plan for From
        $fromMasterPlanSql = MasterPlan::selectRaw('
                master_plan.id,
                master_plan.tgl_plan as tanggal,
                master_plan.id_ws as id_ws,
                act_costing.kpno as no_ws,
                act_costing.styleno as style,
                master_plan.color as color
            ')->
            leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')->
            where('master_plan.cancel', '!=', 'Y');

        // Date Filter
        if ($this->fromDate) {
            $fromMasterPlanSql->whereRaw('master_plan.tgl_plan = "'.$this->fromDate.'"');
        } else {
            $fromMasterPlanSql->whereRaw('YEAR(master_plan.tgl_plan) = "'.date('Y').'"');
        }
        // Line Filter
        if ($this->fromLine) {
            $fromMasterPlanSql->where('master_plan.sewing_line', $this->fromLine);
        }
        $this->fromMasterPlans = $fromMasterPlanSql->
            orderBy('master_plan.tgl_plan', 'desc')->
            orderBy('act_costing.kpno', 'asc')->
            get();

        // Master Plan for To
        $toMasterPlanSql = MasterPlan::selectRaw('
                master_plan.id,
                master_plan.tgl_plan as tanggal,
                master_plan.id_ws as id_ws,
                act_costing.kpno as no_ws,
                act_costing.styleno as style,
                master_plan.color as color
            ')->
            leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')->
            where('master_plan.cancel', '!=', 'Y');

        // Date Filter
        if ($this->toDate) {
            $toMasterPlanSql->whereRaw('master_plan.tgl_plan = "'.$this->toDate.'"');
        } else {
            $toMasterPlanSql->whereRaw('YEAR(master_plan.tgl_plan) = "'.date('Y').'"');
        }
        // Line Filter
        if ($this->toLine) {
            $toMasterPlanSql->where('master_plan.sewing_line', $this->toLine);
        }
        $this->toMasterPlans = $toMasterPlanSql->
            orderBy('master_plan.tgl_plan', 'desc')->
            orderBy('act_costing.kpno', 'asc')->
            get();

        // From Master Plan Output
        if ($this->fromSelectedMasterPlan) {
            $this->fromMasterPlanOutput = MasterPlan::selectRaw("
                master_plan.id,
                master_plan.tgl_plan tanggal,
                SUM((IFNULL(rfts.rft, 0))) rft,
                SUM((IFNULL(defects.defect, 0))) defect,
                SUM((IFNULL(reworks.rework, 0))) rework,
                SUM((IFNULL(rejects.reject, 0))) reject,
                act_costing.kpno ws,
                act_costing.styleno style,
                master_plan.color,
                master_plan.sewing_line,
                master_plan.smv smv,
                master_plan.jam_kerja jam_kerja,
                master_plan.man_power man_power,
                master_plan.plan_target plan_target
            ")->
            leftJoin(DB::raw("(SELECT max(rfts.updated_at) last_rft, count(rfts.id) rft, master_plan.id master_plan_id from output_rfts rfts inner join master_plan on master_plan.id = rfts.master_plan_id where status = 'NORMAL' and master_plan.id = ".$this->fromSelectedMasterPlan." GROUP BY master_plan.id, master_plan.tgl_plan) as rfts"), "master_plan.id", "=", "rfts.master_plan_id")->
            leftJoin(DB::raw("(SELECT max(defects.updated_at) last_defect, count(defects.id) defect, master_plan.id master_plan_id from output_defects defects inner join master_plan on master_plan.id = defects.master_plan_id where defects.defect_status = 'defect' and master_plan.id = ".$this->fromSelectedMasterPlan." GROUP BY master_plan.id, master_plan.tgl_plan) as defects"), "master_plan.id", "=", "defects.master_plan_id")->
            leftJoin(DB::raw("(SELECT max(defrew.updated_at) last_rework, count(defrew.id) rework, master_plan.id master_plan_id from output_defects defrew inner join master_plan on master_plan.id = defrew.master_plan_id where defrew.defect_status = 'reworked' and master_plan.id = ".$this->fromSelectedMasterPlan." GROUP BY master_plan.id, master_plan.tgl_plan) as reworks"), "master_plan.id", "=", "reworks.master_plan_id")->
            leftJoin(DB::raw("(SELECT max(rejects.updated_at) last_reject, count(rejects.id) reject, master_plan.id master_plan_id from output_rejects rejects inner join master_plan on master_plan.id = rejects.master_plan_id where master_plan.id = ".$this->fromSelectedMasterPlan." GROUP BY master_plan.id, master_plan.tgl_plan) as rejects"), "master_plan.id", "=", "rejects.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            where("master_plan.id", $this->fromSelectedMasterPlan)->
            groupBy("master_plan.id")->
            orderBy("master_plan.id_ws", "asc")->
            orderBy("act_costing.styleno", "asc")->
            orderBy("master_plan.color", "asc")->
            orderBy("master_plan.sewing_line", "asc")->
            first();

            // From SoDet List
            $this->fromSoDet = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.sewing_line = '".$this->fromLine."'")->
            whereRaw("master_plan.id = '".$this->fromSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.color", "so_det.size")->
            get();
        }

        // To Master Plan Output
        if ($this->toSelectedMasterPlan) {
            $this->toMasterPlanOutput = MasterPlan::selectRaw("
                master_plan.id,
                master_plan.tgl_plan tanggal,
                SUM((IFNULL(rfts.rft, 0))) rft,
                SUM((IFNULL(defects.defect, 0))) defect,
                SUM((IFNULL(reworks.rework, 0))) rework,
                SUM((IFNULL(rejects.reject, 0))) reject,
                act_costing.kpno ws,
                act_costing.styleno style,
                master_plan.color,
                master_plan.sewing_line,
                master_plan.smv smv,
                master_plan.jam_kerja jam_kerja,
                master_plan.man_power man_power,
                master_plan.plan_target plan_target
            ")->
            leftJoin(DB::raw("(SELECT max(rfts.updated_at) last_rft, count(rfts.id) rft, master_plan.id master_plan_id from output_rfts rfts inner join master_plan on master_plan.id = rfts.master_plan_id where status = 'NORMAL' and master_plan.id = ".$this->toSelectedMasterPlan." GROUP BY master_plan.id, master_plan.tgl_plan) as rfts"), "master_plan.id", "=", "rfts.master_plan_id")->
            leftJoin(DB::raw("(SELECT max(defects.updated_at) last_defect, count(defects.id) defect, master_plan.id master_plan_id from output_defects defects inner join master_plan on master_plan.id = defects.master_plan_id where defects.defect_status = 'defect' and master_plan.id = ".$this->toSelectedMasterPlan." GROUP BY master_plan.id, master_plan.tgl_plan) as defects"), "master_plan.id", "=", "defects.master_plan_id")->
            leftJoin(DB::raw("(SELECT max(defrew.updated_at) last_rework, count(defrew.id) rework, master_plan.id master_plan_id from output_defects defrew inner join master_plan on master_plan.id = defrew.master_plan_id where defrew.defect_status = 'reworked' and master_plan.id = ".$this->toSelectedMasterPlan." GROUP BY master_plan.id, master_plan.tgl_plan) as reworks"), "master_plan.id", "=", "reworks.master_plan_id")->
            leftJoin(DB::raw("(SELECT max(rejects.updated_at) last_reject, count(rejects.id) reject, master_plan.id master_plan_id from output_rejects rejects inner join master_plan on master_plan.id = rejects.master_plan_id where master_plan.id = ".$this->toSelectedMasterPlan." GROUP BY master_plan.id, master_plan.tgl_plan) as rejects"), "master_plan.id", "=", "rejects.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            where("master_plan.id", $this->toSelectedMasterPlan)->
            groupBy("master_plan.id")->
            orderBy("master_plan.id_ws", "asc")->
            orderBy("act_costing.styleno", "asc")->
            orderBy("master_plan.color", "asc")->
            orderBy("master_plan.sewing_line", "asc")->
            first();

            // To SoDet List
            $this->toSoDet = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.sewing_line = '".$this->toLine."'")->
            whereRaw("master_plan.id = '".$this->fromSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.color", "so_det.size")->
            get();
        }

        return view('livewire.transfer-output');
    }
}
