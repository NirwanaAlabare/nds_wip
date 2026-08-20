<?php

namespace App\Exports\MgtReportDashboard;

use App\Exports\export_excel_laporan_daily_cost;
use Maatwebsite\Excel\Concerns\WithTitle;

class SheetDailyCost extends export_excel_laporan_daily_cost implements WithTitle
{
    use ForcesSheetTitle;

    protected $sheetTitle;

    public function __construct($bulan, $tahun)
    {
        parent::__construct($bulan, $tahun);

        $this->sheetTitle = 'Opt Cost ' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . $tahun;
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }
}
