<?php

namespace App\Exports\MgtReportDashboard;

use App\Exports\export_excel_laporan_sum_prod_earn;
use Maatwebsite\Excel\Concerns\WithTitle;

class SheetSumProdEarn extends export_excel_laporan_sum_prod_earn implements WithTitle
{
    use ForcesSheetTitle;

    public function title(): string
    {
        return 'mgt_report_sum_prod_earn';
    }
}
