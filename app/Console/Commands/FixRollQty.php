<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Cutting\FormCutInput;
use App\Models\Cutting\FormCutInputDetail;
use App\Models\Cutting\ScannedItem;
use App\Services\CuttingService;
use DB;

class FixRollQty extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'production:fixrollqty';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix Roll Qty ';

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
    public function handle(CuttingService $cuttingService)
    {
        $idRoll = null;
        $qty = null;

        $rollId = $idRoll;
        $rollQty = $qty;
        $rollUse = null;

        // Get Roll Use wit null parameter (update all mismatched roll qty)
        $fixRollQty = $cuttingService->fixRollQty($rollId, $rollQty);

        // Logging
        Log::channel('fixRollQty')->info([
            "Fix Roll Qty",
            "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
            "Roll ID: ".($rollId ?? "All"),
            "Roll Qty: ".($rollQty ?? "All"),
            $fixRollQty
        ]);
    }
}
