<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\ReworkPacking;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\RftPacking;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use DB;

class MissRework extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'production:missrework';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Missing Rework Output ';

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
        // Get Defects with Missing Rework
        $defects = collect(DB::connection("mysql_sb")->select("select null as id, output_defects.id as defect_id, 'NORMAL' as status, output_defects.created_by, output_defects.created_at, output_defects.updated_at from output_defects left join output_reworks on output_reworks.defect_id = output_defects.id where output_reworks.id is null and defect_status = 'reworked'"));

        $defectArr = $defects->map(function ($item, $key) {
            return (array) $item;
        })->toArray();

        $storeToRework = Rework::insert($defectArr);

        // Get Reworks Data with Of Course Missing RFT
        $reworks = collect(DB::connection("mysql_sb")->select("select null as id, output_defects.master_plan_id, output_defects.so_det_id, 'REWORK' as status, output_reworks.id as rework_id, output_defects.created_by, output_reworks.created_at, output_reworks.updated_at, output_defects.kode_numbering, output_defects.kode_numbering no_cut_size from output_reworks left join output_defects on output_defects.id = output_reworks.defect_id left join output_rfts on output_rfts.rework_id = output_reworks.id where output_rfts.id is null"));

        $reworkArr = $reworks->map(function ($item, $key) {
            return (array) $item;
        })->toArray();

        $storeToRft = Rft::insert($reworkArr);

        // Get Defects with Missing Rework Packing
        $defectsPacking = collect(DB::connection("mysql_sb")->select("select null as id, output_defects_packing.id as defect_id, 'NORMAL' as status, output_defects_packing.created_by, output_defects_packing.created_at, output_defects_packing.updated_at from output_defects_packing left join output_reworks_packing on output_reworks_packing.defect_id = output_defects_packing.id where output_reworks_packing.id is null and defect_status = 'reworked'"));

        $defectPackingArr = $defectsPacking->map(function ($item, $key) {
            return (array) $item;
        })->toArray();

        $storeToReworkPacking = ReworkPacking::insert($defectPackingArr);

        // Get Reworks Data with Of Course Missing RFT Packing
        $reworksPacking = collect(DB::connection("mysql_sb")->select("select null as id, output_defects_packing.master_plan_id, output_defects_packing.so_det_id, 'REWORK' as status, output_reworks_packing.id as rework_id, output_defects_packing.created_by, output_reworks_packing.created_at, output_reworks_packing.updated_at, output_defects_packing.kode_numbering, output_defects_packing.kode_numbering no_cut_size from output_reworks_packing left join output_defects_packing on output_defects_packing.id = output_reworks_packing.defect_id left join output_rfts_packing on output_rfts_packing.rework_id = output_reworks_packing.id where output_rfts_packing.id is null"));

        $reworkPackingArr = $reworksPacking->map(function ($item, $key) {
            return (array) $item;
        })->toArray();

        $storeToRftPacking = RftPacking::insert($reworkPackingArr);

        if ($storeToRework && $storeToRft && $storeToReworkPacking && $storeToRftPacking) {
            Log::channel('missReworkOutput')->info([
                "Repair Defect->Rework->RFT Chain Data",
                "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                "Total Data ".count($defects),
                "Total Data Packing ".count($defectsPacking),
                $reworks
            ]);

            return array(
                'status' => 200,
                'message' => 'Berhasil memperbaiki <br> Data Defect = '.count($defects).' <br> Data Rework = '.count($reworks).' <br> Data Defect Packing = '.count($defectsPacking).' <br> Data Rework Packing = '.count($reworksPacking),
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
