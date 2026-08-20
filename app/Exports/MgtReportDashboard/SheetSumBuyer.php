<?php

namespace App\Exports\MgtReportDashboard;

use App\Exports\export_excel_laporan_sum_buyer;
use Maatwebsite\Excel\Concerns\WithTitle;

class SheetSumBuyer extends export_excel_laporan_sum_buyer implements WithTitle
{
    use ForcesSheetTitle;

    public function title(): string
    {
        return 'mgt_report_sum_buyer';
    }
}
