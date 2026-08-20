<?php

namespace App\Exports;

use App\Exports\MgtReportDashboard\SheetCosting;
use App\Exports\MgtReportDashboard\SheetDailyCost;
use App\Exports\MgtReportDashboard\SheetDailyEarnBuyer;
use App\Exports\MgtReportDashboard\SheetEarning;
use App\Exports\MgtReportDashboard\SheetLabor;
use App\Exports\MgtReportDashboard\SheetProfitLine;
use App\Exports\MgtReportDashboard\SheetSumBuyer;
use App\Exports\MgtReportDashboard\SheetSumFullEarn;
use App\Exports\MgtReportDashboard\SheetSumProdEarn;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Satu file berisi seluruh laporan management report untuk 1 periode.
 * Tiap sheet memakai export yang sudah ada di menu laporan masing-masing.
 */
class ExportMgtReportDashboardWorkbook implements WithMultipleSheets
{
    use Exportable;

    protected $start_date;
    protected $end_date;
    protected $earningRows;

    public function __construct($start_date, $end_date, array $earningRows)
    {
        $this->start_date  = $start_date;
        $this->end_date    = $end_date;
        $this->earningRows = $earningRows;
    }

    public function sheets(): array
    {
        $sheets = [
            new SheetSumProdEarn($this->start_date, $this->end_date),
            new SheetSumFullEarn($this->start_date, $this->end_date),
            new SheetSumBuyer($this->start_date, $this->end_date, null),
            new SheetEarning($this->earningRows),
            new SheetDailyEarnBuyer($this->start_date, $this->end_date),
            new SheetProfitLine($this->start_date, $this->end_date),
            new SheetCosting(),
        ];

        // daily cost disimpan per bulan, jadi periode dipecah jadi 1 sheet tiap bulan
        foreach ($this->monthsInPeriod() as $month) {
            $sheets[] = new SheetDailyCost($month['bulan'], $month['tahun']);
        }

        $sheets[] = new SheetLabor($this->start_date, $this->end_date, 'STAFF');
        $sheets[] = new SheetLabor($this->start_date, $this->end_date, 'NON STAFF');

        return $sheets;
    }

    /** daftar bulan/tahun yang tersentuh oleh periode, urut dari yang paling awal */
    private function monthsInPeriod(): array
    {
        $cursor = Carbon::parse($this->start_date)->startOfMonth();
        $last   = Carbon::parse($this->end_date)->startOfMonth();
        $months = [];

        while ($cursor->lessThanOrEqualTo($last)) {
            $months[] = [
                'bulan' => (int) $cursor->format('n'),
                'tahun' => (int) $cursor->format('Y'),
            ];

            $cursor->addMonth();
        }

        return $months;
    }
}
