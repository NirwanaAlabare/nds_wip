<?php

namespace App\Exports\MgtReportDashboard;

use App\Exports\export_excel_laporan_sum_full_earn;
use Maatwebsite\Excel\Concerns\WithTitle;

class SheetSumFullEarn extends export_excel_laporan_sum_full_earn implements WithTitle
{
    use ForcesSheetTitle;

    public function title(): string
    {
        return 'mgt_report_sum_full_earn';
    }
}
