<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CuttingFabricRekapService;

class RekapFabric extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cutting:rekap-fabric {--dry-run : Hitung dan cek saldo tanpa menyimpan ke fabric_rekap}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rekapitulasi Saldo Fabric Cutting per Bulan';

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
        $result = (new CuttingFabricRekapService())->runRekap($this->option('dry-run'));

        $this->info($result['message']);

        return $result['status'] == 200 ? 0 : 1;
    }
}
