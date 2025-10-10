<?php

namespace App\Http\Livewire\Sewing;

use Livewire\Component;
use App\Models\SignalBit\UserLine;
use App\Models\SignalBit\UserSbWip;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\RftPacking;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\DefectPacking;
use App\Models\SignalBit\Reject;
use App\Models\SignalBit\RejectPacking;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\ReworkPacking;
use App\Models\SignalBit\MasterPlan;
use App\Models\Stocker\YearSequence;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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

    public $rft;
    public $defect;
    public $reject;
    public $rework;

    public $transferRftQty;
    public $transferRftSize;
    public $transferDefectQty;
    public $transferDefectSize;
    public $transferReworkQty;
    public $transferReworkSize;
    public $transferRejectQty;
    public $transferRejectSize;

    public $fromMasterPlanOutput;
    public $toMasterPlanOutput;

    public $fromSelectedMasterPlan;
    public $toSelectedMasterPlan;

    public $loadingMasterPlan;

    public $outputType;

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

        // Qty
        $this->transferRftQty = null;
        $this->transferRftSize = null;

        $this->kodeNumbering = null;
        $this->kodeNumberingList = null;
        $this->kodeNumberingOutput = null;
        $this->kodeNumberingOutputPacking = null;

        $this->loadingMasterPlan = false;
        $this->baseUrl = url('/');

        $this->outputType = "";
    }

    public function checkNumbering()
    {
        $kodeNumbering = addQuotesAround($this->kodeNumbering);

        $this->kodeNumberingList = YearSequence::selectRaw("
            year_sequence.id_year_sequence,
            master_sb_ws.ws,
            master_sb_ws.styleno,
            master_sb_ws.color,
            master_sb_ws.size,
            master_sb_ws.dest
        ")->
        leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "year_sequence.so_det_id")->
        whereRaw("year_sequence.id_year_sequence in (".$kodeNumbering.")")->
        get();

        if ($kodeNumbering) {
            $this->kodeNumberingOutput = collect(
                    DB::connection("mysql_sb")->select("
                        SELECT output.*, userpassword.username as sewing_line FROM (
                            select created_by, kode_numbering, id, created_at, updated_at from output_rfts WHERE kode_numbering in (".$kodeNumbering.")
                            UNION
                            select created_by, kode_numbering, id, created_at, updated_at from output_defects WHERE kode_numbering in (".$kodeNumbering.")
                            UNION
                            select created_by, kode_numbering, id, created_at, updated_at from output_rejects WHERE kode_numbering in (".$kodeNumbering.")
                        ) output
                        left join user_sb_wip on user_sb_wip.id = output.created_by
                        left join userpassword on userpassword.line_id = user_sb_wip.line_id
                    ")
                );
        } else {
            $this->kodeNumberingOutput = collect([]);
        }

        if ($kodeNumbering) {
            $this->kodeNumberingOutputPacking = collect(
                DB::connection("mysql_sb")->select("
                    select created_by sewing_line, kode_numbering, id, created_at, updated_at from output_rfts_packing WHERE kode_numbering in (".$kodeNumbering.")
                ")
            );
        } else {
            $this->kodeNumberingOutputPacking = collect([]);
        }
    }

    public function preTransferNumbering()
    {
        if ($this->toLine && $this->toDate) {
            $this->emit("showModal", "transferNumberingModal");
        } else {
            $this->emit("alert", "warning", "Minimal isi tanggal dan line untuk transfer numbering.");
        }
    }

    public function transferNumbering()
    {
        $newKodeNumbering = addQuotesAround($this->kodeNumbering);

        // if ($this->fromLine && $this->fromSelectedMasterPlan && $this->toLine && $this->toSelectedMasterPlan) {
            if ($this->kodeNumbering) {
                $output = DB::table("year_sequence")->selectRaw("
                    year_sequence.id,
                    year_sequence.id_year_sequence,
                    year_sequence.so_det_id,
                    act_costing.id id_ws,
                    so_det.color,
                    so_det.size,
                    so_det.dest
                ")->
                leftJoin("signalbit_erp.so_det", "so_det.id", "=", "year_sequence.so_det_id")->
                leftJoin("signalbit_erp.so", "so.id", "=", "so_det.id_so")->
                leftJoin("signalbit_erp.act_costing", "act_costing.id", "=","so.id_cost")->
                whereRaw("year_sequence.id_year_sequence in (".$newKodeNumbering.")")->
                groupBy("year_sequence.id")->
                get();

                $this->toSoDet = MasterPlan::selectRaw("
                    so_det.id,
                    so_det.color,
                    so_det.size,
                    so_det.dest
                ")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("so", "so.id_cost", "=", "act_costing.id")->
                leftJoin("so_det", "so_det.id_so", "=", "so.id")->
                whereRaw("master_plan.sewing_line = '".$this->toLine."'")->
                whereRaw("master_plan.id = '".$this->toSelectedMasterPlan."'")->
                whereRaw("so_det.color = master_plan.color")->
                groupBy("so_det.id")->
                get();

                $toUser = DB::connection("mysql_sb")->table("userpassword")->selectRaw("
                    user_sb_wip.id,
                    userpassword.username
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.line_id", "=", "userpassword.line_id")->
                where("userpassword.username", $this->toLine)->
                orderBy("user_sb_wip.id", "desc")->
                first();

                $messageSuccess = "";
                $messageNotFound = "";
                foreach($output as $o) {
                    $toSoDet = $this->toSoDet->where("color", $o->color)->where("size", $o->size)->where("dest", $o->dest)->first();

                    if (!$toSoDet) {
                        $toSoDet = $this->toSoDet->where("size", $o->size)->where("dest", $o->dest)->first();
                    }

                    if (!$toSoDet) {
                        $toSoDet = $this->toSoDet->where("size", $o->size)->first();
                    }

                    if ($this->toSelectedMasterPlan && $toSoDet && $toUser) {
                        // Transfer Output
                        $transferOutput = DB::connection("mysql_sb")->statement("
                            update output_rfts".$this->outputType."
                            left join master_plan on master_plan.id = output_rfts".$this->outputType.".master_plan_id
                            set
                                output_rfts".$this->outputType.".master_plan_id = '".$this->toSelectedMasterPlan."',
                                output_rfts".$this->outputType.".so_det_id = '".$toSoDet->id."',
                                output_rfts".$this->outputType.".created_by = '".($this->outputType == '_packing' ? $toUser->username : $toUser->id)."'
                            where
                                output_rfts".$this->outputType.".kode_numbering = '".$o->id_year_sequence."'
                        ");

                        // Transfer Defect
                        $transferDefect = DB::connection("mysql_sb")->statement("
                            update output_defects".$this->outputType."
                            left join master_plan on master_plan.id = output_defects".$this->outputType.".master_plan_id
                            set
                                output_defects".$this->outputType.".master_plan_id = '".$this->toSelectedMasterPlan."',
                                output_defects".$this->outputType.".so_det_id = '".$toSoDet->id."',
                                output_defects".$this->outputType.".created_by = '".($this->outputType == '_packing' ? $toUser->username : $toUser->id)."'
                            where
                                output_defects".$this->outputType.".kode_numbering = '".$o->id_year_sequence."'
                        ");

                        // Transfer Reject
                        $transferReject = DB::connection("mysql_sb")->statement("
                            update output_rejects".$this->outputType."
                            left join master_plan on master_plan.id = output_rejects".$this->outputType.".master_plan_id
                            set
                                output_rejects".$this->outputType.".master_plan_id = '".$this->toSelectedMasterPlan."',
                                output_rejects".$this->outputType.".so_det_id = '".$toSoDet->id."',
                                output_rejects".$this->outputType.".created_by = '".($this->outputType == '_packing' ? $toUser->username : $toUser->id)."'
                            where
                                output_rejects".$this->outputType.".kode_numbering = '".$o->id_year_sequence."'
                        ");

                        if ($transferOutput || $transferDefect || $transferReject) {
                            $yearSequence = DB::table("year_sequence")->where("id_year_sequence", $o->id_year_sequence)->update(["so_det_id" => $toSoDet->id]);
                        }

                        $messageSuccess .= $o->id_year_sequence." <br> berhasil <br>";
                    } else {
                        // Only Date and Line (Multi Style/Color)
                        if ($this->toDate && $toUser) {
                            // RFT
                            $rft = DB::connection("mysql_sb")
                            ->table("output_rfts".$this->outputType." as rfts")
                            ->select("rfts.id", "master_plan.tgl_plan", "master_plan.id_ws", "master_plan.color")
                            ->leftJoin("master_plan", "master_plan.id", "=", "rfts.master_plan_id")
                            ->where("kode_numbering", $o->id_year_sequence)
                            ->first();

                            if ($rft) {
                                $masterPlanRft = DB::connection("mysql_sb")->table("master_plan")
                                    ->select("master_plan.id")
                                    ->where("master_plan.sewing_line", $toUser->username)
                                    ->where("tgl_plan", $this->toDate)
                                    ->where("id_ws", $rft->id_ws)
                                    ->where("color", $rft->color)
                                    ->first();

                                if ($masterPlanRft) {
                                    DB::connection("mysql_sb")
                                        ->table("output_rfts".$this->outputType)
                                        ->where("id", $rft->id)
                                        ->update([
                                            "master_plan_id" => $masterPlanRft->id,
                                            "created_by" => ($this->outputType == '_packing' ? $toUser->username : $toUser->id),
                                        ]);

                                    $messageSuccess .= $o->id_year_sequence." <br> berhasil <br>";
                                } else {
                                    $messageNotFound .= "RFT ".$o->id_year_sequence." <br> master plan tidak ada <br>";
                                }
                            }

                            // DEFECT
                            $defect = DB::connection("mysql_sb")
                                ->table("output_defects".$this->outputType." as defects")
                                ->select("defects.id", "master_plan.tgl_plan", "master_plan.id_ws", "master_plan.color")
                                ->leftJoin("master_plan", "master_plan.id", "=", "defects.master_plan_id")
                                ->where("kode_numbering", $o->id_year_sequence)
                                ->first();

                            if ($defect) {
                                $masterPlanDefect = DB::connection("mysql_sb")->table("master_plan")
                                    ->select("master_plan.id")
                                    ->where("master_plan.sewing_line", $toUser->username)
                                    ->where("tgl_plan", $this->toDate)
                                    ->where("id_ws", $defect->id_ws)
                                    ->where("color", $defect->color)
                                    ->first();

                                if ($masterPlanDefect) {
                                    DB::connection("mysql_sb")
                                        ->table("output_defects".$this->outputType)
                                        ->where("id", $defect->id)
                                        ->update([
                                            "master_plan_id" => $masterPlanDefect->id,
                                            "created_by" => ($this->outputType == '_packing' ? $toUser->username : $toUser->id),
                                        ]);

                                    $messageSuccess .= $o->id_year_sequence." <br> berhasil <br>";
                                } else {
                                    $messageNotFound .= "Defect ".$o->id_year_sequence." <br> master plan tidak ada <br>";
                                }
                            }

                            // REJECT
                            $reject = DB::connection("mysql_sb")
                                ->table("output_rejects".$this->outputType." as rejects")
                                ->select("rejects.id", "master_plan.tgl_plan", "master_plan.id_ws", "master_plan.color")
                                ->leftJoin("master_plan", "master_plan.id", "=", "rejects.master_plan_id")
                                ->where("kode_numbering", $o->id_year_sequence)
                                ->first();

                            if ($reject) {
                                $masterPlanReject = DB::connection("mysql_sb")->table("master_plan")
                                    ->select("master_plan.id")
                                    ->where("master_plan.sewing_line", $toUser->username)
                                    ->where("tgl_plan", $this->toDate)
                                    ->where("id_ws", $reject->id_ws)
                                    ->where("color", $reject->color)
                                    ->first();

                                if ($masterPlanReject) {
                                    DB::connection("mysql_sb")
                                        ->table("output_rejects".$this->outputType)
                                        ->where("id", $reject->id)
                                        ->update([
                                            "master_plan_id" => $masterPlanReject->id,
                                            "created_by" => ($this->outputType == '_packing' ? $toUser->username : $toUser->id),
                                        ]);

                                    $messageSuccess .= $o->id_year_sequence." <br> berhasil <br>";
                                } else {
                                    $messageNotFound .= "Reject ".$o->id_year_sequence." <br> master plan tidak ada <br>";
                                }
                            }
                        } else {
                            $this->emit("alert", "warning", "Minimal isi tanggal dan line untuk transfer output.");
                        }
                    }
                }

                if ($messageSuccess != "") {
                    Log::channel('transferOutput')->info([
                        "Moving Output Data by QR Number",
                        $this->outputType,
                        "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                        "From Line '".$this->fromLine."'. with Master Plan '".$this->fromSelectedMasterPlan."'",
                        "To Line '".$this->toLine."'. with Master Plan '".$this->toSelectedMasterPlan."'",
                    ]);

                    $this->emit('alert', 'success', $messageSuccess);
                }

                if ($messageNotFound != "") {
                    $this->emit('alert', 'error', $messageNotFound);
                }
            } else {
                $this->emit("alert", "warning", "Harap cantumkan kode numbering seperti contoh");
            }
        // } else {
        //     $this->emit("alert", "warning", "Harap pilih line dan master plan dengan lengkap");
        // }
    }

    public function transferAll()
    {
        if ($this->fromLine && $this->fromSelectedMasterPlan && $this->toLine && $this->toSelectedMasterPlan) {
            // From SoDet List
            $this->fromSoDet = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size,
                so_det.dest
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.sewing_line = '".$this->fromLine."'")->
            whereRaw("master_plan.id = '".$this->fromSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.id")->
            get();

            // To SoDet List
            $this->toSoDet = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size,
                so_det.dest
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.sewing_line = '".$this->toLine."'")->
            whereRaw("master_plan.id = '".$this->toSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.id")->
            get();

            $toUser = DB::connection("mysql_sb")->table("userpassword")->selectRaw("
                    user_sb_wip.id,
                    userpassword.username
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.line_id", "=", "userpassword.line_id")->
                where("userpassword.username", $this->toLine)->
                orderBy("user_sb_wip.id", "desc")->
                first();

            $messageSuccess = "";
            $messageNotFound = "";
            foreach ($this->fromSoDet as $fromSoDet) {
                $toSoDet = $this->toSoDet->where("color", $fromSoDet->color)->where("size", $fromSoDet->size)->where("dest", $fromSoDet->dest)->first();

                if (!$toSoDet) {
                    $toSoDet = $this->toSoDet->where("size", $fromSoDet->size)->where("dest", $fromSoDet->dest)->first();
                }

                if (!$toSoDet) {
                    $toSoDet = $this->toSoDet->where("size", $fromSoDet->size)->first();
                }

                if ($toSoDet && $toUser) {
                    $kodeNumberingNull = "";
                    if ($toSoDet->id != $fromSoDet->id) {
                        $kodeNumberingNull = " and kode_numbering is null";
                    }

                    // Transfer Output
                    $transferOutput = DB::connection("mysql_sb")->statement("
                        update output_rfts".$this->outputType."
                        left join master_plan on master_plan.id = output_rfts".$this->outputType.".master_plan_id
                        set output_rfts".$this->outputType.".master_plan_id = '".$this->toSelectedMasterPlan."', output_rfts".$this->outputType.".created_by = '".($this->outputType == '_packing' ? $toUser->username : $toUser->id)."'
                        where output_rfts".$this->outputType.".master_plan_id = '".$this->fromSelectedMasterPlan."' and output_rfts".$this->outputType.".so_det_id = '".$fromSoDet->id."' ".$kodeNumberingNull."
                    ");
                    if ($transferOutput) {
                        $soDetOutput = DB::connection("mysql_sb")->statement("
                            update output_rfts".$this->outputType."
                            left join master_plan on master_plan.id = output_rfts".$this->outputType.".master_plan_id
                            set output_rfts".$this->outputType.".so_det_id = '".$toSoDet->id."'
                            where output_rfts".$this->outputType.".so_det_id = '".$fromSoDet->id."' and master_plan.id = '".$this->toSelectedMasterPlan."' and master_plan.sewing_line = '".$this->toLine."' ".$kodeNumberingNull."
                        ");
                    }

                    // Transfer Defect
                    $transferDefect = DB::connection("mysql_sb")->statement("
                        update output_defects".$this->outputType."
                        left join master_plan on master_plan.id = output_defects".$this->outputType.".master_plan_id
                        set output_defects".$this->outputType.".master_plan_id = '".$this->toSelectedMasterPlan."', output_defects".$this->outputType.".created_by = '".($this->outputType == '_packing' ? $toUser->username : $toUser->id)."'
                        where output_defects".$this->outputType.".master_plan_id = '".$this->fromSelectedMasterPlan."' and output_defects".$this->outputType.".so_det_id = '".$fromSoDet->id."' ".$kodeNumberingNull."
                    ");
                    if ($transferDefect) {
                        $soDetDefect = DB::connection("mysql_sb")->statement("
                            update output_defects".$this->outputType."
                            left join master_plan on master_plan.id = output_defects".$this->outputType.".master_plan_id
                            set output_defects".$this->outputType.".so_det_id = '".$toSoDet->id."'
                            where output_defects".$this->outputType.".so_det_id = '".$fromSoDet->id."' and master_plan.id = '".$this->toSelectedMasterPlan."' and master_plan.sewing_line = '".$this->toLine."' ".$kodeNumberingNull."
                        ");
                    }

                    // Transfer Reject
                    $transferReject = DB::connection("mysql_sb")->statement("
                        update output_rejects".$this->outputType."
                        left join master_plan on master_plan.id = output_rejects".$this->outputType.".master_plan_id
                        set output_rejects".$this->outputType.".master_plan_id = '".$this->toSelectedMasterPlan."', output_rejects".$this->outputType.".created_by = '".($this->outputType == '_packing' ? $toUser->username : $toUser->id)."'
                        where output_rejects".$this->outputType.".master_plan_id = '".$this->fromSelectedMasterPlan."' and output_rejects".$this->outputType.".so_det_id = '".$fromSoDet->id."' ".$kodeNumberingNull."
                    ");
                    if ($transferReject) {
                        $soDetReject = DB::connection("mysql_sb")->statement("
                            update output_rejects".$this->outputType."
                            left join master_plan on master_plan.id = output_rejects".$this->outputType.".master_plan_id
                            set output_rejects".$this->outputType.".so_det_id = '".$toSoDet->id."'
                            where output_rejects".$this->outputType.".so_det_id = '".$fromSoDet->id."' and master_plan.id = '".$this->toSelectedMasterPlan."' and master_plan.sewing_line = '".$this->toLine."' ".$kodeNumberingNull."
                        ");
                    }

                    $messageSuccess .= $fromSoDet->size." success <br>";
                } else {
                    $messageNotFound .= $fromSoDet->size." not found <br>";
                }
            }

            if ($messageSuccess != "") {
                Log::channel('transferOutput')->info([
                    "Moving All Output Data",
                    $this->outputType,
                    "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                    "From Line '".$this->fromLine."'. with Master Plan '".$this->fromSelectedMasterPlan."' ",
                    "To Line '".$this->toLine."'. with Master Plan '".$this->toSelectedMasterPlan."'",
                ]);

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
            $toUser = DB::connection("mysql_sb")->table("userpassword")->selectRaw("
                    user_sb_wip.id,
                    userpassword.username
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.line_id", "=", "userpassword.line_id")->
                where("userpassword.username", $this->toLine)->
                orderBy("user_sb_wip.id", "desc")->
                first();

            // From SoDet List
            $this->fromSoDet = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size,
                so_det.dest
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.sewing_line = '".$this->fromLine."'")->
            whereRaw("master_plan.id = '".$this->fromSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.id")->
            get();

            // To SoDet List
            $this->toSoDet = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size,
                so_det.dest
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.sewing_line = '".$this->toLine."'")->
            whereRaw("master_plan.id = '".$this->toSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.id")->
            get();

            $messageSuccess = "";
            $messageNotFound = "";
            foreach ($this->fromSoDet as $fromSoDet) {
                $toSoDet = $this->toSoDet->where("color", $fromSoDet->color)->where("size", $fromSoDet->size)->where("dest", $fromSoDet->dest)->first();

                if (!$toSoDet) {
                    $toSoDet = $this->toSoDet->where("size", $fromSoDet->size)->where("dest", $fromSoDet->dest)->first();
                }

                if (!$toSoDet) {
                    $toSoDet = $this->toSoDet->where("size", $fromSoDet->size)->first();
                }

                if ($toSoDet && $toUser) {
                    $kodeNumberingNull = "";
                    if ($fromSoDet->id != $toSoDet->id) {
                        $kodeNumberingNull = " and kode_numbering is null";
                    }

                    // Transfer Output
                    $transferOutput = DB::connection("mysql_sb")->statement("
                        update output_rfts".$this->outputType."
                        left join master_plan on master_plan.id = output_rfts".$this->outputType.".master_plan_id
                        set output_rfts".$this->outputType.".master_plan_id = '".$this->toSelectedMasterPlan."'
                        where output_rfts".$this->outputType.".master_plan_id = '".$this->fromSelectedMasterPlan."' and output_rfts".$this->outputType.".so_det_id = '".$fromSoDet->id."' and output_rfts".$this->outputType.".status != 'REWORK' and output_rfts".$this->outputType.".rework_id is null ".$kodeNumberingNull."
                    ");
                    if ($transferOutput) {
                        $soDetOutput = DB::connection("mysql_sb")->statement("
                            update output_rfts".$this->outputType."
                            left join master_plan on master_plan.id = output_rfts".$this->outputType.".master_plan_id
                            set output_rfts".$this->outputType.".so_det_id = '".$toSoDet->id."', output_rfts".$this->outputType.".created_by = '".($this->outputType == '_packing' ? $toUser->username : $toUser->id)."'
                            where output_rfts".$this->outputType.".so_det_id = '".$fromSoDet->id."' and master_plan.id = '".$this->toSelectedMasterPlan."' and master_plan.sewing_line = '".$this->toLine."' and output_rfts".$this->outputType.".status != 'REWORK' and output_rfts".$this->outputType.".rework_id is null ".$kodeNumberingNull."
                        ");
                    }

                    $messageSuccess .= $fromSoDet->size." success <br>";
                } else {
                    $messageNotFound .= $fromSoDet->size." not found <br>";
                }
            }

            if ($messageSuccess != "") {
                Log::channel('transferOutput')->info([
                    "Moving Output RFT Data",
                    $this->outputType,
                    "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                    "From Line '".$this->fromLine."'. with Master Plan '".$this->fromSelectedMasterPlan."' ",
                    "To Line '".$this->toLine."'. with Master Plan '".$this->toSelectedMasterPlan."'",
                ]);

                $this->emit('alert', 'success', $messageSuccess);
            }

            if ($messageNotFound != "") {
                $this->emit('alert', 'warning', $messageNotFound);
            }
        } else {
            $this->emit("alert", "warning", "Harap pilih line dan master plan dengan lengkap");
        }
    }

    public function transferRftDetail()
    {
        if ($this->fromLine && $this->fromSelectedMasterPlan && $this->toLine && $this->toSelectedMasterPlan) {
            if ($this->transferRftQty && $this->transferRftSize) {
                $userList = UserSbWip::selectRaw("MAX(user_sb_wip.id) id, user_sb_wip.line_id, userpassword.username")->leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->groupBy("user_sb_wip.line_id")->get();
                $fromLine = $userList->where("username", $this->fromLine)->first();
                $toLine = $userList->where("username", $this->toLine)->first();

                // Rft list
                if ($this->outputType == '_packing') {
                    $rfts = RftPacking::selectRaw("output_rfts_packing.*")->
                        leftJoin("so_det", "so_det.id", "=", "output_rfts_packing.so_det_id")->
                        leftJoin("userpassword", "userpassword.username", "=", "output_rfts_packing.created_by")->
                        where("output_rfts_packing.status", "NORMAL")->
                        where("userpassword.username", $this->fromLine)->
                        where("master_plan_id", $this->fromSelectedMasterPlan)->
                        where("so_det.size", $this->transferRftSize)->
                        orderBy("output_rfts_packing.updated_at", "desc")->
                        limit($this->transferRftQty)->
                        get();
                } else {
                    $rfts = Rft::selectRaw("output_rfts.*")->
                        leftJoin("so_det", "so_det.id", "=", "output_rfts.so_det_id")->
                        leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_rfts.created_by")->
                        leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                        where("output_rfts.status", "NORMAL")->
                        where("userpassword.username", $this->fromLine)->
                        where("master_plan_id", $this->fromSelectedMasterPlan)->
                        where("so_det.size", $this->transferRftSize)->
                        orderBy("output_rfts.updated_at", "desc")->
                        limit($this->transferRftQty)->
                        get();
                }

                // To So Det List
                $this->toSoDet = MasterPlan::selectRaw("
                        so_det.id,
                        so_det.color,
                        so_det.size,
                        so_det.dest
                    ")->
                    leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                    leftJoin("so", "so.id_cost", "=", "act_costing.id")->
                    leftJoin("so_det", "so_det.id_so", "=", "so.id")->
                    whereRaw("master_plan.sewing_line = '".$this->toLine."'")->
                    whereRaw("master_plan.id = '".$this->toSelectedMasterPlan."'")->
                    whereRaw("so_det.color = master_plan.color")->
                    groupBy("so_det.id")->
                    orderBy("so_det.id")->
                    get();

                foreach ($rfts as $rft) {
                    $currentToSoDet = $this->toSoDet->where("id", $rft->so_det_idid)->first();

                    if (!$currentToSoDet) {
                        $currentToSoDet = $this->toSoDet->where("size", $this->transferRftSize)->first();
                    }

                    if ($rft->so_det_id == $currentToSoDet->id || $rft->kode_numbering == null) {
                        $rft->timestamps = false;
                        $rft->master_plan_id = $this->toSelectedMasterPlan;
                        $rft->created_by = $this->outputType == "_packing" ? $toLine->username : $toLine->id;
                        $rft->so_det_id = $currentToSoDet->id;
                        $rft->save();
                    }
                }

                Log::channel('transferOutput')->info([
                    "Moving Output RFT Data Size ".$this->transferRftSize." ".$this->transferRftQty." PCS",
                    $this->outputType,
                    "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                    "From Line '".$this->fromLine."'. with Master Plan '".$this->fromSelectedMasterPlan."' ",
                    "To Line '".$this->toLine."'. with Master Plan '".$this->toSelectedMasterPlan."'",
                ]);

                $this->emit('alert', 'success', $rfts->count()." RFT Berhasil di Transfer");
            } else {
                $this->emit('alert', 'error', "Harap pilih size dan tentukan qty yang akan di transfer");
            }
        } else {
            $this->emit('alert', 'error', "Harap pilih line dan masterplan dengan lengkap");
        }
    }

    public function transferDefect()
    {
        if ($this->toSelectedMasterPlan && $this->fromLine && $this->fromSelectedMasterPlan && $this->toLine && $this->toSelectedMasterPlan) {

            $toUser = DB::connection("mysql_sb")->table("userpassword")->selectRaw("
                    user_sb_wip.id,
                    userpassword.username
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.line_id", "=", "userpassword.line_id")->
                where("userpassword.username", $this->toLine)->
                orderBy("user_sb_wip.id", "desc")->
                first();

            // From SoDet List
            $this->fromSoDet = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size,
                so_det.dest
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.sewing_line = '".$this->fromLine."'")->
            whereRaw("master_plan.id = '".$this->fromSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.id")->
            get();

            // To SoDet List
            $this->toSoDet = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size,
                so_det.dest
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.sewing_line = '".$this->toLine."'")->
            whereRaw("master_plan.id = '".$this->toSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.id")->
            get();

            $messageSuccess = "";
            $messageNotFound = "";
            foreach ($this->fromSoDet as $fromSoDet) {
                $toSoDet = $this->toSoDet->where("color", $fromSoDet->color)->where("size", $fromSoDet->size)->where("dest", $fromSoDet->dest)->first();

                if (!$toSoDet) {
                    $toSoDet = $this->toSoDet->where("size", $fromSoDet->size)->where("dest", $fromSoDet->dest)->first();
                }

                if (!$toSoDet) {
                    $toSoDet = $this->toSoDet->where("size", $fromSoDet->size)->first();
                }

                if ($toSoDet && $toUser) {
                    $kodeNumberingNull = "";
                    if ($fromSoDet->id != $toSoDet->id) {
                        $kodeNumberingNull = " and kode_numbering is null";
                    }

                    // Transfer Defect
                    $transferDefect = DB::connection("mysql_sb")->statement("
                        update output_defects".$this->outputType."
                        left join master_plan on master_plan.id = output_defects".$this->outputType.".master_plan_id
                        set output_defects".$this->outputType.".master_plan_id = '".$this->toSelectedMasterPlan."'
                        where output_defects".$this->outputType.".master_plan_id = '".$this->fromSelectedMasterPlan."' and output_defects".$this->outputType.".so_det_id = '".$fromSoDet->id."' and output_defects".$this->outputType.".defect_status = 'defect' ".$kodeNumberingNull."
                    ");
                    if ($transferDefect) {
                        $soDetDefect = DB::connection("mysql_sb")->statement("
                            update output_defects".$this->outputType."
                            left join master_plan on master_plan.id = output_defects".$this->outputType.".master_plan_id
                            set output_defects".$this->outputType.".so_det_id = '".$toSoDet->id."', output_defects".$this->outputType.".created_by = '".($this->outputType == '_packing' ? $toUser->username : $toUser->id)."'
                            where output_defects".$this->outputType.".so_det_id = '".$fromSoDet->id."' and master_plan.id = '".$this->toSelectedMasterPlan."' and master_plan.sewing_line = '".$this->toLine."' and output_defects".$this->outputType.".defect_status = 'defect' ".$kodeNumberingNull."
                        ");
                    }

                    $messageSuccess .= $fromSoDet->size." success <br>";
                } else {
                    $messageNotFound .= $fromSoDet->size." not found <br>";
                }
            }

            if ($messageSuccess != "") {
                Log::channel('transferOutput')->info([
                    "Moving Output Defect Data",
                    $this->outputType,
                    "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                    "From Line '".$this->fromLine."'. with Master Plan '".$this->fromSelectedMasterPlan."' ",
                    "To Line '".$this->toLine."'. with Master Plan '".$this->toSelectedMasterPlan."'",
                ]);

                $this->emit('alert', 'success', $messageSuccess);
            }

            if ($messageNotFound != "") {
                $this->emit('alert', 'warning', $messageNotFound);
            }
        } else {
            $this->emit("alert", "warning", "Harap pilih line dan master plan dengan lengkap");
        }
    }

    public function transferDefectDetail()
    {
        if ($this->fromLine && $this->fromSelectedMasterPlan && $this->toLine && $this->toSelectedMasterPlan) {
            if ($this->transferDefectQty && $this->transferDefectSize) {
                $userList = UserSbWip::selectRaw("MAX(user_sb_wip.id) id, user_sb_wip.line_id, userpassword.username")->leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->groupBy("user_sb_wip.line_id")->get();
                $fromLine = $userList->where("username", $this->fromLine)->first();
                $toLine = $userList->where("username", $this->toLine)->first();

                // Defects list
                if ($this->outputType == "_packing") {
                    $defects = DefectPacking::selectRaw("output_defects_packing.*")->
                        leftJoin("so_det", "so_det.id", "=", "output_defects_packing.so_det_id")->
                        leftJoin("userpassword", "userpassword.username", "=", "output_defects_packing.created_by")->
                        where("userpassword.username", $this->fromLine)->
                        where("master_plan_id", $this->fromSelectedMasterPlan)->
                        where("so_det.size", $this->transferDefectSize)->
                        where("output_defects_packing.defect_status", "defect")->
                        orderBy("output_defects_packing.updated_at", "desc")->
                        limit($this->transferDefectQty)->
                        get();
                } else {
                    $defects = Defect::selectRaw("output_defects.*")->
                        leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
                        leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_defects.created_by")->
                        leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                        where("userpassword.username", $this->fromLine)->
                        where("master_plan_id", $this->fromSelectedMasterPlan)->
                        where("so_det.size", $this->transferDefectSize)->
                        where("output_defects.defect_status", "defect")->
                        orderBy("output_defects.updated_at", "desc")->
                        limit($this->transferDefectQty)->
                        get();
                }

                // To So Det List
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
                    groupBy("so_det.id")->
                    orderBy("so_det.id")->
                    get();

                foreach ($defects as $defect) {
                    $currentToSoDet = $this->toSoDet->where("id", $defect->so_det_id)->first();

                    if (!$currentToSoDet) {
                        $currentToSoDet = $this->toSoDet->where("size", $this->transferDefectSize)->first();
                    }

                    if ($defect->so_det_id == $currentToSoDet->id || $defect->kode_numbering == null) {
                        $defect->timestamps = false;
                        $defect->master_plan_id = $this->toSelectedMasterPlan;
                        $defect->created_by = $this->outputType == "_packing" ? $toLine->username : $toLine->id;
                        $defect->so_det_id = $currentToSoDet->id;
                        $defect->save();
                    }
                }

                Log::channel('transferOutput')->info([
                    "Moving Output Defect Data Size ".$this->transferDefectSize." ".$this->transferDefectQty." PCS",
                    $this->outputType,
                    "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                    "From Line '".$this->fromLine."'. with Master Plan '".$this->fromSelectedMasterPlan."' ",
                    "To Line '".$this->toLine."'. with Master Plan '".$this->toSelectedMasterPlan."'",
                ]);

                $this->emit('alert', 'success', $defects->count()." Defect Berhasil di Transfer");
            } else {
                $this->emit('alert', 'error', "Harap pilih size dan tentukan qty yang akan di transfer");
            }
        } else {
            $this->emit('alert', 'error', "Harap pilih line dan masterplan dengan lengkap");
        }
    }

    public function transferRework()
    {
        if ($this->toSelectedMasterPlan && $this->fromLine && $this->fromSelectedMasterPlan && $this->toLine && $this->toSelectedMasterPlan) {

            $toUser = DB::connection("mysql_sb")->table("userpassword")->selectRaw("
                    user_sb_wip.id,
                    userpassword.username
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.line_id", "=", "userpassword.line_id")->
                where("userpassword.username", $this->toLine)->
                orderBy("user_sb_wip.id", "desc")->
                first();

            // From SoDet List
            $this->fromSoDet = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size,
                so_det.dest
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.sewing_line = '".$this->fromLine."'")->
            whereRaw("master_plan.id = '".$this->fromSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.id")->
            get();

            // To SoDet List
            $this->toSoDet = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size,
                so_det.dest
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.sewing_line = '".$this->toLine."'")->
            whereRaw("master_plan.id = '".$this->toSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.id")->
            get();

            $messageSuccess = "";
            $messageNotFound = "";
            foreach ($this->fromSoDet as $fromSoDet) {
                $toSoDet = $this->toSoDet->where("color", $fromSoDet->color)->where("size", $fromSoDet->size)->where("dest", $fromSoDet->dest)->first();

                if (!$toSoDet) {
                    $toSoDet = $this->toSoDet->where("size", $fromSoDet->size)->where("dest", $fromSoDet->dest)->first();
                }

                if (!$toSoDet) {
                    $toSoDet = $this->toSoDet->where("size", $fromSoDet->size)->first();
                }

                if ($toSoDet && $toUser) {
                    $kodeNumberingNull = "";
                    if ($fromSoDet->id != $toSoDet->id) {
                        $kodeNumberingNull = " and kode_numbering is null";
                    }

                    // Transfer Defect
                    $transferDefect = DB::connection("mysql_sb")->statement("
                        update output_defects".$this->outputType."
                        left join master_plan on master_plan.id = output_defects".$this->outputType.".master_plan_id
                        set output_defects".$this->outputType.".master_plan_id = '".$this->toSelectedMasterPlan."'
                        where output_defects".$this->outputType.".master_plan_id = '".$this->fromSelectedMasterPlan."' and output_defects".$this->outputType.".so_det_id = '".$fromSoDet->id."' and output_defects".$this->outputType.".defect_status = 'reworked' ".$kodeNumberingNull."
                    ");
                    if ($transferDefect) {
                        $soDetDefect = DB::connection("mysql_sb")->statement("
                            update output_defects".$this->outputType."
                            left join master_plan on master_plan.id = output_defects".$this->outputType.".master_plan_id
                            set output_defects".$this->outputType.".so_det_id = '".$toSoDet->id."', output_defects".$this->outputType.".created_by = '".($this->outputType == '_packing' ? $toUser->username : $toUser->id)."'
                            where output_defects".$this->outputType.".so_det_id = '".$fromSoDet->id."' and master_plan.id = '".$this->toSelectedMasterPlan."' and master_plan.sewing_line = '".$this->toLine."' and output_defects".$this->outputType.".defect_status = 'reworked' ".$kodeNumberingNull."
                        ");
                    }

                    // Transfer Rft/Rework
                    $transferRftRework = DB::connection("mysql_sb")->statement("
                        update output_rfts".$this->outputType."
                        left join master_plan on master_plan.id = output_rfts".$this->outputType.".master_plan_id
                        set output_rfts".$this->outputType.".master_plan_id = '".$this->toSelectedMasterPlan."'
                        where output_rfts".$this->outputType.".master_plan_id = '".$this->fromSelectedMasterPlan."' and output_rfts".$this->outputType.".so_det_id = '".$fromSoDet->id."' and master_plan.sewing_line = '".$this->toLine."' and output_rfts".$this->outputType.".status = 'REWORK' and output_rfts".$this->outputType.".rework_id is not null ".$kodeNumberingNull."
                    ");
                    if ($transferRftRework) {
                        $soDetRftRework = DB::connection("mysql_sb")->statement("
                            update output_rfts".$this->outputType."
                            left join master_plan on master_plan.id = output_rfts".$this->outputType.".master_plan_id
                            set output_rfts".$this->outputType.".so_det_id = '".$toSoDet->id."', output_rfts".$this->outputType.".created_by = '".($this->outputType == '_packing' ? $toUser->username : $toUser->id)."'
                            where output_rfts".$this->outputType.".so_det_id = '".$fromSoDet->id."' and master_plan.id = '".$this->toSelectedMasterPlan."' and master_plan.sewing_line = '".$this->toLine."' and output_rfts".$this->outputType.".status = 'REWORK' and output_rfts".$this->outputType.".rework_id is not null ".$kodeNumberingNull."
                        ");
                    }

                    $messageSuccess .= $fromSoDet->size." success <br>";
                } else {
                    $messageNotFound .= $fromSoDet->size." not found <br>";
                }
            }

            if ($messageSuccess != "") {
                Log::channel('transferOutput')->info([
                    "Moving Output Defect Data",
                    $this->outputType,
                    "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                    "From Line '".$this->fromLine."'. with Master Plan '".$this->fromSelectedMasterPlan."' ",
                    "To Line '".$this->toLine."'. with Master Plan '".$this->toSelectedMasterPlan."'",
                ]);

                $this->emit('alert', 'success', $messageSuccess);
            }

            if ($messageNotFound != "") {
                $this->emit('alert', 'warning', $messageNotFound);
            }
        } else {
            $this->emit("alert", "warning", "Harap pilih line dan master plan dengan lengkap");
        }
    }

    public function transferReworkDetail()
    {
        if ($this->fromLine && $this->fromSelectedMasterPlan && $this->toLine && $this->toSelectedMasterPlan) {
            if ($this->transferReworkQty && $this->transferReworkSize) {
                $userList = UserSbWip::selectRaw("MAX(user_sb_wip.id) id, user_sb_wip.line_id, userpassword.username")->leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->groupBy("user_sb_wip.line_id")->get();
                $fromLine = $userList->where("username", $this->fromLine)->first();
                $toLine = $userList->where("username", $this->toLine)->first();

                // Defect Rework list
                if ($this->outputType == "_packing") {
                    $defects = DefectPacking::selectRaw("output_defects_packing.*")->
                        leftJoin("so_det", "so_det.id", "=", "output_defects_packing.so_det_id")->
                        leftJoin("userpassword", "userpassword.username", "=", "output_defects_packing.created_by")->
                        where("userpassword.username", $this->fromLine)->
                        where("master_plan_id", $this->fromSelectedMasterPlan)->
                        where("so_det.size", $this->transferReworkSize)->
                        where("output_defects_packing.defect_status", "reworked")->
                        orderBy("output_defects_packing.updated_at", "desc")->
                        limit($this->transferReworkQty)->
                        get();
                } else {
                    $defects = Defect::selectRaw("output_defects.*")->
                        leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
                        leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_defects.created_by")->
                        leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                        where("userpassword.username", $this->fromLine)->
                        where("master_plan_id", $this->fromSelectedMasterPlan)->
                        where("so_det.size", $this->transferReworkSize)->
                        where("output_defects.defect_status", "reworked")->
                        orderBy("output_defects.updated_at", "desc")->
                        limit($this->transferReworkQty)->
                        get();
                }

                // To So Det List
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
                    groupBy("so_det.id")->
                    orderBy("so_det.id")->
                    get();

                $reworkIds = [];
                foreach ($defects as $defect) {
                    $currentToSoDet = $this->toSoDet->where("id", $defect->so_det_id)->first();

                    if (!$currentToSoDet) {
                        $currentToSoDet = $this->toSoDet->where("size", $this->transferReworkSize)->first();
                    }

                    if ($defect->so_det_id == $currentToSoDet->id || $defect->kode_numbering == null) {
                        $defect->timestamps = false;
                        $defect->master_plan_id = $this->toSelectedMasterPlan;
                        $defect->created_by = $this->outputType == "_packing" ? $toLine->username : $toLine->id;
                        $defect->so_det_id = $currentToSoDet->id;
                        $defect->save();

                        // RFT Rework
                        DB::connection("mysql_sb")->table("output_rfts".$this->outputType)->where("rework_id", $defect->rework->id)->update([
                            "master_plan_id" => $this->toSelectedMasterPlan,
                            "created_by" => $this->outputType == "_packing" ? $toLine->username : $toLine->id,
                            "so_det_id" => $currentToSoDet->id,
                        ]);
                    }
                }

                Log::channel('transferOutput')->info([
                    "Moving Output Rework Data Size ".$this->transferReworkSize." ".$this->transferReworkQty." PCS",
                    $this->outputType,
                    "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                    "From Line '".$this->fromLine."'. with Master Plan '".$this->fromSelectedMasterPlan."' ",
                    "To Line '".$this->toLine."'. with Master Plan '".$this->toSelectedMasterPlan."'",
                ]);

                $this->emit('alert', 'success', $defects->count()." Rework Berhasil di Transfer");
            } else {
                $this->emit('alert', 'error', "Harap pilih size dan tentukan qty yang akan di transfer");
            }
        } else {
            $this->emit('alert', 'error', "Harap pilih line dan masterplan dengan lengkap");
        }
    }

    public function transferReject()
    {
        if ($this->toSelectedMasterPlan && $this->fromLine && $this->fromSelectedMasterPlan && $this->toLine && $this->toSelectedMasterPlan) {
            $toUser = DB::connection("mysql_sb")->table("userpassword")->selectRaw("
                    user_sb_wip.id,
                    userpassword.username
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.line_id", "=", "userpassword.line_id")->
                where("userpassword.username", $this->toLine)->
                orderBy("user_sb_wip.id", "desc")->
                first();

            // From SoDet List
            $this->fromSoDet = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size,
                so_det.dest
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.sewing_line = '".$this->fromLine."'")->
            whereRaw("master_plan.id = '".$this->fromSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.id")->
            get();

            // To SoDet List
            $this->toSoDet = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size,
                so_det.dest
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.sewing_line = '".$this->toLine."'")->
            whereRaw("master_plan.id = '".$this->toSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.id")->
            get();

            $messageSuccess = "";
            $messageNotFound = "";
            foreach ($this->fromSoDet as $fromSoDet) {
                $toSoDet = $this->toSoDet->where("color", $fromSoDet->color)->where("size", $fromSoDet->size)->where("dest", $fromSoDet->dest)->first();

                if (!$toSoDet) {
                    $toSoDet = $this->toSoDet->where("size", $fromSoDet->size)->where("dest", $fromSoDet->dest)->first();
                }

                if (!$toSoDet) {
                    $toSoDet = $this->toSoDet->where("size", $fromSoDet->size)->first();
                }

                if ($toSoDet && $toUser) {
                    $kodeNumberingNull = "";
                    if ($fromSoDet->id != $toSoDet->id) {
                        $kodeNumberingNull = " and kode_numbering is null";
                    }

                    // Transfer Reject
                    $transferReject = DB::connection("mysql_sb")->statement("
                        update output_rejects".$this->outputType."
                        left join master_plan on master_plan.id = output_rejects".$this->outputType.".master_plan_id
                        set output_rejects".$this->outputType.".master_plan_id = '".$this->toSelectedMasterPlan."'
                        where output_rejects".$this->outputType.".master_plan_id = '".$this->fromSelectedMasterPlan."' and output_rejects".$this->outputType.".so_det_id = '".$fromSoDet->id."' and master_plan.sewing_line = '".$this->toLine."' ".$kodeNumberingNull."
                    ");
                    if ($transferReject) {
                        $soDetReject = DB::connection("mysql_sb")->statement("
                            update output_rejects".$this->outputType."
                            left join master_plan on master_plan.id = output_rejects".$this->outputType.".master_plan_id
                            set output_rejects".$this->outputType.".so_det_id = '".$toSoDet->id."', output_rejects".$this->outputType.".created_by = '".($this->outputType == '_packing' ? $toUser->username : $toUser->id)."'
                            where output_rejects".$this->outputType.".so_det_id = '".$fromSoDet->id."' and master_plan.id = '".$this->toSelectedMasterPlan."' and master_plan.sewing_line = '".$this->toLine."' ".$kodeNumberingNull."
                        ");
                    }

                    // Transfer Defect Rejected
                    $transferDefect = DB::connection("mysql_sb")->statement("
                        update output_defects".$this->outputType."
                        left join master_plan on master_plan.id = output_defects".$this->outputType.".master_plan_id
                        set output_defects".$this->outputType.".master_plan_id = '".$this->toSelectedMasterPlan."'
                        where output_defects".$this->outputType.".master_plan_id = '".$this->fromSelectedMasterPlan."' and output_defects".$this->outputType.".so_det_id = '".$fromSoDet->id."' and master_plan.sewing_line = '".$this->toLine."' and defect_status = 'rejected' ".$kodeNumberingNull."
                    ");
                    if ($transferDefect) {
                        $soDetDefect = DB::connection("mysql_sb")->statement("
                            update output_defects".$this->outputType."
                            left join master_plan on master_plan.id = output_defects".$this->outputType.".master_plan_id
                            set output_defects".$this->outputType.".so_det_id = '".$toSoDet->id."', output_defects".$this->outputType.".created_by = '".($this->outputType == '_packing' ? $toUser->username : $toUser->id)."'
                            where output_defects".$this->outputType.".so_det_id = '".$fromSoDet->id."' and master_plan.id = '".$this->toSelectedMasterPlan."' and master_plan.sewing_line = '".$this->toLine."' and defect_status = 'rejected' ".$kodeNumberingNull."
                        ");
                    }

                    $messageSuccess .= $fromSoDet->size." success <br>";
                } else {
                    $messageNotFound .= $fromSoDet->size." not found <br>";
                }
            }

            if ($messageSuccess != "") {
                Log::channel('transferOutput')->info([
                    "Moving Output Reject Data",
                    $this->outputType,
                    "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                    "From Line '".$this->fromLine."'. with Master Plan '".$this->fromSelectedMasterPlan."' ",
                    "To Line '".$this->toLine."'. with Master Plan '".$this->toSelectedMasterPlan."'",
                ]);

                $this->emit('alert', 'success', $messageSuccess);
            }

            if ($messageNotFound != "") {
                $this->emit('alert', 'warning', $messageNotFound);
            }
        } else {
            $this->emit("alert", "warning", "Harap pilih line dan master plan dengan lengkap");
        }
    }

    public function transferRejectDetail()
    {
        if ($this->fromLine && $this->fromSelectedMasterPlan && $this->toLine && $this->toSelectedMasterPlan) {
            if ($this->transferRejectQty && $this->transferRejectSize) {
                $userList = UserSbWip::selectRaw("MAX(user_sb_wip.id) id, user_sb_wip.line_id, userpassword.username")->leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->groupBy("user_sb_wip.line_id")->get();
                $fromLine = $userList->where("username", $this->fromLine)->first();
                $toLine = $userList->where("username", $this->toLine)->first();

                // Reject list
                if ($this->outputType == '_packing') {
                    $rejects = RejectPacking::selectRaw("output_rejects_packing.*")->
                        leftJoin("so_det", "so_det.id", "=", "output_rejects_packing.so_det_id")->
                        leftJoin("userpassword", "userpassword.username", "=", "output_rejects_packing.created_by")->
                        where("userpassword.username", $this->fromLine)->
                        where("master_plan_id", $this->fromSelectedMasterPlan)->
                        where("so_det.size", $this->transferRejectSize)->
                        orderBy("output_rejects_packing.updated_at", "desc")->
                        limit($this->transferRejectQty)->
                        get();
                } else {
                    $rejects = Reject::selectRaw("output_rejects.*")->
                        leftJoin("so_det", "so_det.id", "=", "output_rejects.so_det_id")->
                        leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_rejects.created_by")->
                        leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                        where("userpassword.username", $this->fromLine)->
                        where("master_plan_id", $this->fromSelectedMasterPlan)->
                        where("so_det.size", $this->transferRejectSize)->
                        orderBy("output_rejects.updated_at", "desc")->
                        limit($this->transferRejectQty)->
                        get();
                }

                // To So Det List
                $this->toSoDet = MasterPlan::selectRaw("
                        so_det.id,
                        so_det.color,
                        so_det.size,
                        so_det.dest
                    ")->
                    leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                    leftJoin("so", "so.id_cost", "=", "act_costing.id")->
                    leftJoin("so_det", "so_det.id_so", "=", "so.id")->
                    whereRaw("master_plan.sewing_line = '".$this->toLine."'")->
                    whereRaw("master_plan.id = '".$this->toSelectedMasterPlan."'")->
                    whereRaw("so_det.color = master_plan.color")->
                    groupBy("so_det.id")->
                    orderBy("so_det.id")->
                    get();

                $toSoDet = $this->toSoDet->where("size", $this->transferRejectSize)->first();

                $defectIds = [];
                foreach ($rejects as $reject) {
                    $currentToSoDet = $this->toSoDet->where("id", $reject->so_det_id)->first();

                    if (!$currentToSoDet) {
                        $currentToSoDet = $this->toSoDet->where("size", $this->transferRejectSize)->first();
                    }

                    if ($reject->so_det_id == $currentToSoDet->id || $reject->kode_numbering == null) {
                        $reject->timestamps = false;
                        $reject->master_plan_id = $this->toSelectedMasterPlan;
                        $reject->created_by = $this->outputType == "_packing" ? $toLine->username : $toLine->id;
                        $reject->so_det_id = $currentToSoDet->id;
                        $reject->save();

                        // Defect Reject
                        DB::connection("mysql_sb")->table("output_defects".$this->outputType)->whereIn("id", $reject->defect_id)->update([
                            "master_plan_id" => $this->toSelectedMasterPlan,
                            "created_by" => $this->outputType == "_packing" ? $toLine->username : $toLine->id,
                            "so_det_id" => $currentToSoDet->id,
                        ]);
                    }
                }

                Log::channel('transferOutput')->info([
                    "Moving Output Reject Data Size ".$this->transferRejectSize." ".$this->transferRejectQty." PCS",
                    $this->outputType,
                    "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                    "From Line '".$this->fromLine."'. with Master Plan '".$this->fromSelectedMasterPlan."' ",
                    "To Line '".$this->toLine."'. with Master Plan '".$this->toSelectedMasterPlan."'",
                ]);

                $this->emit('alert', 'success', $rejects->count()." Reject Berhasil di Transfer");
            } else {
                $this->emit('alert', 'error', "Harap pilih size dan tentukan qty yang akan di transfer");
            }
        } else {
            $this->emit('alert', 'error', "Harap pilih line dan masterplan dengan lengkap");
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
                master_plan.color as color,
                master_plan.cancel
            ')->
            leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws');
            // where('master_plan.cancel', '!=', 'Y');

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
            leftJoin(DB::raw("(SELECT max(rfts.updated_at) last_rft, count(rfts.id) rft, master_plan.id master_plan_id from output_rfts".($this->outputType)." rfts inner join master_plan on master_plan.id = rfts.master_plan_id where status = 'NORMAL' and master_plan.id = ".$this->fromSelectedMasterPlan." GROUP BY master_plan.id, master_plan.tgl_plan) as rfts"), "master_plan.id", "=", "rfts.master_plan_id")->
            leftJoin(DB::raw("(SELECT max(defects.updated_at) last_defect, count(defects.id) defect, master_plan.id master_plan_id from output_defects".($this->outputType)." defects inner join master_plan on master_plan.id = defects.master_plan_id where defects.defect_status = 'defect' and master_plan.id = ".$this->fromSelectedMasterPlan." GROUP BY master_plan.id, master_plan.tgl_plan) as defects"), "master_plan.id", "=", "defects.master_plan_id")->
            leftJoin(DB::raw("(SELECT max(defrew.updated_at) last_rework, count(defrew.id) rework, master_plan.id master_plan_id from output_defects".($this->outputType)." defrew inner join master_plan on master_plan.id = defrew.master_plan_id where defrew.defect_status = 'reworked' and master_plan.id = ".$this->fromSelectedMasterPlan." GROUP BY master_plan.id, master_plan.tgl_plan) as reworks"), "master_plan.id", "=", "reworks.master_plan_id")->
            leftJoin(DB::raw("(SELECT max(rejects.updated_at) last_reject, count(rejects.id) reject, master_plan.id master_plan_id from output_rejects".($this->outputType)." rejects inner join master_plan on master_plan.id = rejects.master_plan_id where master_plan.id = ".$this->fromSelectedMasterPlan." GROUP BY master_plan.id, master_plan.tgl_plan) as rejects"), "master_plan.id", "=", "rejects.master_plan_id")->
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
                so_det.size,
                SUM(COALESCE(rft.rft, 0)) rft,
                SUM(COALESCE(defect.defect, 0)) defect,
                SUM(COALESCE(rework.rework, 0)) rework,
                SUM(COALESCE(reject.reject, 0)) reject
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            leftJoin(DB::raw("(select COUNT(id) rft, so_det_id, master_plan_id from output_rfts".$this->outputType." where master_plan_id = '".$this->fromSelectedMasterPlan."' group by so_det_id, master_plan_id) rft"), "so_det.id", "=", "rft.so_det_id")->
            leftJoin(DB::raw("(select COUNT(id) defect, so_det_id, master_plan_id from output_defects".$this->outputType." where master_plan_id = '".$this->fromSelectedMasterPlan."' and defect_status = 'defect' group by so_det_id, master_plan_id) defect"), "so_det.id", "=", "defect.so_det_id")->
            leftJoin(DB::raw("(select COUNT(id) rework, so_det_id, master_plan_id from output_defects".$this->outputType." where master_plan_id = '".$this->fromSelectedMasterPlan."' and defect_status = 'reworked' group by so_det_id, master_plan_id) rework"), "so_det.id", "=", "rework.so_det_id")->
            leftJoin(DB::raw("(select COUNT(id) reject, so_det_id, master_plan_id from output_rejects".$this->outputType." where master_plan_id = '".$this->fromSelectedMasterPlan."' group by so_det_id, master_plan_id) reject"), "so_det.id", "=", "reject.so_det_id")->
            whereRaw("master_plan.sewing_line = '".$this->fromLine."'")->
            whereRaw("master_plan.id = '".$this->fromSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.color", "so_det.size")->
            orderBy("so_det.id")->
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
            leftJoin(DB::raw("(SELECT max(rfts.updated_at) last_rft, count(rfts.id) rft, master_plan.id master_plan_id from output_rfts".$this->outputType." rfts inner join master_plan on master_plan.id = rfts.master_plan_id where status = 'NORMAL' and master_plan.id = ".$this->toSelectedMasterPlan." GROUP BY master_plan.id, master_plan.tgl_plan) as rfts"), "master_plan.id", "=", "rfts.master_plan_id")->
            leftJoin(DB::raw("(SELECT max(defects.updated_at) last_defect, count(defects.id) defect, master_plan.id master_plan_id from output_defects".$this->outputType." defects inner join master_plan on master_plan.id = defects.master_plan_id where defects.defect_status = 'defect' and master_plan.id = ".$this->toSelectedMasterPlan." GROUP BY master_plan.id, master_plan.tgl_plan) as defects"), "master_plan.id", "=", "defects.master_plan_id")->
            leftJoin(DB::raw("(SELECT max(defrew.updated_at) last_rework, count(defrew.id) rework, master_plan.id master_plan_id from output_defects".$this->outputType." defrew inner join master_plan on master_plan.id = defrew.master_plan_id where defrew.defect_status = 'reworked' and master_plan.id = ".$this->toSelectedMasterPlan." GROUP BY master_plan.id, master_plan.tgl_plan) as reworks"), "master_plan.id", "=", "reworks.master_plan_id")->
            leftJoin(DB::raw("(SELECT max(rejects.updated_at) last_reject, count(rejects.id) reject, master_plan.id master_plan_id from output_rejects".$this->outputType." rejects inner join master_plan on master_plan.id = rejects.master_plan_id where master_plan.id = ".$this->toSelectedMasterPlan." GROUP BY master_plan.id, master_plan.tgl_plan) as rejects"), "master_plan.id", "=", "rejects.master_plan_id")->
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
                so_det.size,
                SUM(COALESCE(rft.rft, 0)) rft,
                SUM(COALESCE(defect.defect, 0)) defect,
                SUM(COALESCE(rework.rework, 0)) rework,
                SUM(COALESCE(reject.reject, 0)) reject
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            leftJoin(DB::raw("(select COUNT(id) rft, so_det_id, master_plan_id from output_rfts".$this->outputType." where master_plan_id = '".$this->toSelectedMasterPlan."' group by so_det_id, master_plan_id) rft"), "so_det.id", "=", "rft.so_det_id")->
            leftJoin(DB::raw("(select COUNT(id) defect, so_det_id, master_plan_id from output_defects".$this->outputType." where master_plan_id = '".$this->toSelectedMasterPlan."' and defect_status = 'defect' group by so_det_id, master_plan_id) defect"), "so_det.id", "=", "defect.so_det_id")->
            leftJoin(DB::raw("(select COUNT(id) rework, so_det_id, master_plan_id from output_defects".$this->outputType." where master_plan_id = '".$this->toSelectedMasterPlan."' and defect_status = 'reworked' group by so_det_id, master_plan_id) rework"), "so_det.id", "=", "rework.so_det_id")->
            leftJoin(DB::raw("(select COUNT(id) reject, so_det_id, master_plan_id from output_rejects".$this->outputType." where master_plan_id = '".$this->toSelectedMasterPlan."' group by so_det_id, master_plan_id) reject"), "so_det.id", "=", "reject.so_det_id")->
            whereRaw("master_plan.sewing_line = '".$this->toLine."'")->
            whereRaw("master_plan.id = '".$this->toSelectedMasterPlan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.color", "so_det.size")->
            orderBy("so_det.id")->
            get();
        }

        return view('livewire.transfer-output');
    }
}
