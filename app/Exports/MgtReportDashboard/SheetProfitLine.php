<?php

namespace App\Exports\MgtReportDashboard;

use App\Exports\export_excel_laporan_profit_line;
use Maatwebsite\Excel\Concerns\WithTitle;

class SheetProfitLine extends export_excel_laporan_profit_line implements WithTitle
{
    use ForcesSheetTitle;

    public function title(): string
    {
        return 'Profit Line';
    }
}
