<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\SignalBit\UserLine;
use App\Exports\ProductionExport;
use Illuminate\Contracts\Queue\ShouldQueue;
use DB;

class ProductionAllExport implements WithMultipleSheets, ShouldQueue
{
    use Exportable;

    protected $date;

    public function __construct($date)
    {
        $this->date = $date;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        ini_set('max_execution_time', 300);

        $lines = DB::connection('mysql_sb')->table('userpassword')->select('username')->where('Groupp', 'SEWING')->whereRaw("(Locked != '1' OR Locked IS NULL)")->orderBy('FullName', 'asc')->lazy();
        $sheets = [];

        foreach ($lines as $line) {
            $sheets[] = new ProductionExport($this->date, $line->username);
        }

        return $sheets;
    }
}
