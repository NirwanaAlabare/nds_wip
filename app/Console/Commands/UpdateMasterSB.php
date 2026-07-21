<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\MasterSbWs;
use App\Services\GeneralService;
use DB;

class UpdateMasterSB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'general:updatemastersb';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Master SB WS ';

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
    public function handle(GeneralService $generalService)
    {
        // Update Master SB
        $updateMasterSbWs = $generalService->updateMasterSbWs();

        // Logging
        Log::channel('updateMasterSb')->info([
            "Replace New Master SB WS",
            "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
            $updateMasterSbWs
        ]);
    }
}
