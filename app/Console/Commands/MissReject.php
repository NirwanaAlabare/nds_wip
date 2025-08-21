<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SignalBit\Reject;
use App\Models\SignalBit\RejectPacking;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use DB;

class MissReject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'production:missreject';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Missing Reject Output ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get Defects with Missing Reject
        $defects = collect(DB::connection("mysql_sb")->select("select null as id, output_defects.master_plan_id, output_defects.so_det_id, 'NORMAL' as status, output_defects.id as defect_id, output_defects.defect_type_id as reject_type_id, output_defects.defect_area_id as reject_area_id, output_defects.defect_area_x as reject_area_x, output_defects.defect_area_y as reject_area_y, 'defect' as reject_status, output_defects.created_by, output_defects.created_at, output_defects.updated_at from output_defects left join output_rejects on output_rejects.defect_id = output_defects.id where output_rejects.id is null and defect_status = 'rejected'"));

        $defectArr = $defects->map(function ($item, $key) {
            return (array) $item;
        })->toArray();

        $storeToReject = Reject::insert($defectArr);

        // Get Defects with Missing Reject Packing
        $defectsPacking = collect(DB::connection("mysql_sb")->select("select null as id, output_defects.master_plan_id, output_defects.so_det_id, 'NORMAL' as status, output_defects.id as defect_id, output_defects.defect_type_id as reject_type_id, output_defects.defect_area_id as reject_area_id, output_defects.defect_area_x as reject_area_x, output_defects.defect_area_y as reject_area_y, 'defect' as reject_status, output_defects.created_by, output_defects.created_at, output_defects.updated_at from output_defects_packing as output_defects left join output_rejects_packing as output_rejects on output_rejects.defect_id = output_defects.id where output_rejects.id is null and defect_status = 'rejected'"));

        $defectPackingArr = $defectsPacking->map(function ($item, $key) {
            return (array) $item;
        })->toArray();

        $storeToRejectPacking = RejectPacking::insert($defectPackingArr);

        if ($storeToReject && $storeToRejectPacking) {
            Log::channel('missRejectOutput')->info([
                "Repair Defect->Reject Chain Data",
                "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                "Total Data ".count($defects)." - ".$defects,
                "Total Data Packing ".count($defectsPacking)." - ".$defectsPacking
            ]);

            return array(
                'status' => 200,
                'message' => 'Berhasil memperbaiki <br> Data Defect = '.count($defects).' <br>',
                'redirect' => '',
                'table' => '',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Terjadi kesalahan',
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }
}
