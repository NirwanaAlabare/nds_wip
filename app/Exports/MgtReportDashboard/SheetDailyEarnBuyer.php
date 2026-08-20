<?php

namespace App\Exports\MgtReportDashboard;

use App\Exports\export_excel_laporan_daily_earn_buyer;
use Maatwebsite\Excel\Concerns\WithTitle;

class SheetDailyEarnBuyer extends export_excel_laporan_daily_earn_buyer implements WithTitle
{
    use ForcesSheetTitle;

    public function title(): string
    {
        return 'mgt_report_daily_earn_buyer';
    }
}
