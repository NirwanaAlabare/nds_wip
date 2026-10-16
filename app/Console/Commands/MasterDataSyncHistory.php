<?php

namespace App\Console\Commands;

use App\Services\MasterDataHistoryService;
use Illuminate\Console\Command;

class MasterDataSyncHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'masterdata:sync-history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Salin riwayat master customer / supplier dari act_hist (sekali sesudah tabel mastersupplier_log dibuat; aman diulang)';

    public function handle(MasterDataHistoryService $riwayat)
    {
        if (! $riwayat->siap()) {
            $this->error('Tabel mastersupplier_log belum ada. Jalankan database/sql/2026_10_14_mastersupplier_log.sql dulu.');
            return 1;
        }

        $mulai = microtime(true);
        $jumlah = $riwayat->sinkron(true);
        $this->info($jumlah . ' catatan riwayat disalin (' . round(microtime(true) - $mulai, 1) . ' detik).');

        return 0;
    }
}
