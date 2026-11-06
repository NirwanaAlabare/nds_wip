<?php

namespace App\Services;

use App\Http\Controllers\Cutting\ReportCuttingController;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use DB;

class CuttingFabricRekapService
{
    /**
     * Saldo pembuka fabric_rekap (sama dengan $balance_date di query mutasi).
     * Baris tanggal ini tidak boleh diubah, rekap hanya untuk tanggal sesudahnya.
     */
    protected $balanceDate = '2026-09-30';

    /**
     * Awal periode rekap kalau fabric_rekap masih kosong.
     */
    protected $rekapStartDate = '2026-10-01';

    /**
     * Batas selisih saldo antar periode, untuk menampung pembulatan per roll.
     */
    protected $saldoTolerance = 1;

    public function runRekap($dryRun = false): array
    {
        try {
            // Sama dengan dc:rekap, yang direkap sampai 2 bulan sebelumnya
            // (30 Nov merekap sampai 30 Sep). Bulan yang tertinggal ikut direkap.
            $dateLimit = now()->subMonthsNoOverflow(2)->endOfMonth();

            $latestRekap = DB::selectOne("SELECT MAX(tanggal) tanggal FROM fabric_rekap");
            $periodStart = $latestRekap->tanggal
                ? Carbon::parse($latestRekap->tanggal)->addDay()
                : Carbon::parse($this->rekapStartDate);

            $notClosed = null;
            $periods = DB::transaction(function () use ($periodStart, $dateLimit, $dryRun, &$notClosed) {
                $periods = [];
                while ($periodStart->lte($dateLimit)) {
                    $periodEnd = $periodStart->copy()->endOfMonth()->toDateString();

                    // Berhenti di bulan yang belum closing
                    if (!checkClosingDate($periodEnd)) {
                        $notClosed = $periodEnd;
                        break;
                    }

                    // Berurutan, karena saldo awal periode dibaca dari rekap periode sebelumnya
                    $periods[] = $this->rekapPeriod($periodStart->toDateString(), $periodEnd, $dryRun);

                    $periodStart = $periodStart->copy()->addMonthNoOverflow()->startOfMonth();
                }

                return $periods;
            });

            if (count($periods) < 1) {
                $message = $notClosed
                    ? 'Rekap Fabric tidak dijalankan karena periode s/d '.$notClosed.' belum closing. Closing terakhir: '.periodeClosing().'.'
                    : 'Tidak ada periode baru untuk direkap.';
                Log::channel('rekapFabric')->info($message);

                return [
                    'status' => 200,
                    'message' => $message,
                ];
            }

            $message = ($dryRun ? '[Dry Run] ' : '').'Rekap Fabric periode '.$periods[0]['date_from'].' s/d '.end($periods)['date_to'].' berhasil.';
            if ($notClosed) {
                $message .= ' Periode s/d '.$notClosed.' belum closing, belum direkap.';
            }
            Log::channel('rekapFabric')->info($message, $periods);

            return [
                'status' => 200,
                'message' => $message,
                'additional' => $periods,
            ];
        } catch (\Throwable $e) {
            Log::channel('rekapFabric')->error("Error saat mengupdate Rekap Fabric: " . $e->getMessage());

            return [
                'status' => 400,
                'message' => 'Gagal mengupdate Rekap Fabric: ' . $e->getMessage(),
            ];
        }
    }

    public function runRekapCustom($dateFrom, $dateTo): array
    {
        try {
            if ($dateFrom <= $this->balanceDate) {
                return [
                    'status' => 400,
                    'message' => 'Rekap Fabric hanya tersedia untuk tanggal setelah '.$this->balanceDate.'. Rekap '.$this->balanceDate.' tidak bisa diubah.',
                ];
            }

            // Hanya periode yang sudah closing yang boleh direkap. Rekap sesudahnya yang
            // ikut diperbarui juga harus sudah closing, jadi yang dicek tanggal terakhirnya.
            $lastRekapDateInChain = DB::table('fabric_rekap')
                ->where('tanggal', '>', $dateTo)
                ->max('tanggal') ?: $dateTo;

            if (!checkClosingDate($lastRekapDateInChain)) {
                return [
                    'status' => 400,
                    'message' => 'Rekap Fabric tidak dijalankan karena periode s/d '.$lastRekapDateInChain.' belum closing. Closing terakhir: '.periodeClosing().'.',
                ];
            }

            // Rekap sesudah periode ini yang sudah ada menyimpan saldo awal dari angka
            // lama, jadi ikut direkap ulang berurutan. Periodenya dimulai H+1 dari rekap
            // sebelumnya. Rekap baru tidak dibuat di sini. Semua dalam satu transaksi,
            // jadi kalau cek saldo salah satu periode gagal, semuanya batal.
            $periods = DB::transaction(function () use ($dateFrom, $dateTo) {
                $periods = [$this->rekapPeriod($dateFrom, $dateTo, false)];

                $nextRekapDates = DB::table('fabric_rekap')
                    ->where('tanggal', '>', $dateTo)
                    ->distinct()
                    ->orderBy('tanggal')
                    ->pluck('tanggal');

                $previousDate = $dateTo;
                foreach ($nextRekapDates as $nextRekapDate) {
                    $periods[] = $this->rekapPeriod(Carbon::parse($previousDate)->addDay()->toDateString(), $nextRekapDate, false);

                    $previousDate = $nextRekapDate;
                }

                return $periods;
            });

            $message = 'Rekap Fabric periode '.$dateFrom.' s/d '.$dateTo.' berhasil. Total '.$periods[0]['total_row'].' baris.';
            if (count($periods) > 1) {
                $message .= '<br>Rekap sesudahnya ikut diperbarui: '.implode(', ', array_map(function ($period) {
                    return $period['date_from'].' s/d '.$period['date_to'];
                }, array_slice($periods, 1))).'.';
            }
            Log::channel('rekapFabric')->info($message, $periods);

            return [
                'status' => 200,
                'message' => $message,
                'additional' => $periods,
            ];
        } catch (\Throwable $e) {
            Log::channel('rekapFabric')->error("Error saat mengupdate Rekap Fabric: " . $e->getMessage());

            return [
                'status' => 400,
                'message' => 'Gagal mengupdate Rekap Fabric: ' . $e->getMessage(),
            ];
        }
    }

    protected function rekapPeriod($dateFrom, $dateTo, $dryRun)
    {
        // Pengaman terakhir supaya saldo pembuka tidak pernah ikut terhapus
        if ($dateFrom <= $this->balanceDate) {
            throw new \Exception("Rekap ".$this->balanceDate." tidak bisa diubah, periode harus dimulai setelah tanggal tersebut");
        }

        $mutasiQuery = app(ReportCuttingController::class)->buildMutasiFabricBarcodeQueryNew($dateFrom, $dateTo, 'Barcode');

        $summary = DB::selectOne("
            SELECT
                COUNT(*) total_row,
                ROUND(SUM(saldo_awal), 2) saldo_awal,
                ROUND(SUM(saldo_akhir), 2) saldo_akhir
            FROM (
                ".$mutasiQuery."
            ) mutasi
        ");

        // Saldo akhir rekap sebelumnya (rumus sama dengan fabric_rekap di query mutasi)
        $previousRekap = DB::selectOne("
            SELECT
                ROUND(SUM(saldo_awal + (penerimaan - (pemakaian - short_roll + gr_panel + gr_set) - retur) + adjustment), 2) saldo_akhir
            FROM
                fabric_rekap
            WHERE
                tanggal = DATE_SUB(?, INTERVAL 1 DAY)
        ", [$dateFrom]);

        if (
            $previousRekap->saldo_akhir !== null &&
            abs($previousRekap->saldo_akhir - $summary->saldo_awal) > $this->saldoTolerance
        ) {
            throw new \Exception("Saldo awal ".$dateFrom." (".$summary->saldo_awal.") tidak sama dengan saldo akhir rekap sebelumnya (".$previousRekap->saldo_akhir.")");
        }

        $deleted = 0;
        if (!$dryRun) {
            // Hapus rekap periode ini dulu, karena INSERT biasa (tanpa unique key)
            // akan membuat baris dobel kalau periodenya direkap ulang
            $deleted = DB::table('fabric_rekap')
                ->whereBetween('tanggal', [$dateFrom, $dateTo])
                ->delete();

            DB::insert("
                INSERT INTO fabric_rekap (
                    tanggal, id_roll, id_item, item_desc, unit,
                    saldo_awal, penerimaan, pemakaian, short_roll, gr_panel, gr_set, retur, adjustment, saldo_akhir,
                    created_by, created_by_username, created_at, updated_at
                )
                SELECT
                    '".$dateTo."', barcode, id_item, itemdesc, satuan,
                    saldo_awal, penerimaan, pemakaian, short_roll, gr_panel, gr_set, retur, adjustment, saldo_akhir,
                    0, 'SYSTEM', NOW(), NOW()
                FROM (
                    ".$mutasiQuery."
                ) mutasi
            ");
        }

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'deleted' => $deleted,
            'total_row' => $summary->total_row,
            'saldo_awal' => $summary->saldo_awal,
            'saldo_akhir' => $summary->saldo_akhir,
        ];
    }
}
