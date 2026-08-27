<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DcService;

class RekapDC extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dc:rekap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rekapitulasi Data DC';

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
        $dcService = new DcService();
        $dcService->runRekap();
    }
}
