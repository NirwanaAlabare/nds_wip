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
        // Merekap 2 bulan sebelumnya, misalnya dijalankan 30 November maka periodenya September
        $period = now()->subMonthsNoOverflow(2);

        $dcService = new DcService();
        $dcService->runRekap($period->copy()->endOfMonth()->toDateString(), $period->copy()->startOfMonth()->toDateString());
    }
}
