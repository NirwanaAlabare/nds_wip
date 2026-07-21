<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\MasterSbWs;
use App\Services\SewingService;
use DB;

class UpdateMgtRepTmpEarn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'general:updatemgtreptmpearn';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Mgr Rep Tmp Earn ';

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
    public function handle(SewingService $sewingService)
    {
        // Update Mgt Rep Tmp Earn
        $sewingService->updateMgtRepTmpEarn();

        // Logging
        Log::channel('updateMgtRepTmpEarn')->info([
            "Update MgtRepTmpEarn",
            "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System")
        ]);
    }
}
