<?php

namespace App\Exports;

use App\Exports\MgtReportDashboard\SheetDailyEarnBuyer;
use App\Exports\MgtReportDashboard\SheetEarning;
use App\Exports\MgtReportDashboard\SheetProfitLine;
use App\Exports\MgtReportDashboard\SheetSumBuyer;
use App\Exports\MgtReportDashboard\SheetSumFullEarn;
use App\Exports\MgtReportDashboard\SheetSumProdEarn;
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
        return [
            new SheetSumProdEarn($this->start_date, $this->end_date),
            new SheetSumFullEarn($this->start_date, $this->end_date),
            new SheetSumBuyer($this->start_date, $this->end_date, null),
            new SheetEarning($this->earningRows),
            new SheetDailyEarnBuyer($this->start_date, $this->end_date),
            new SheetProfitLine($this->start_date, $this->end_date),
        ];
    }
}
