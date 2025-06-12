<?php

namespace App\Exports\Sewing;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\SignalBit\EmployeeLine;
use DB;

class MasterLineExport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $from;
    protected $to;

    public function __construct($from, $to)
    {
        $this->from = $from ? $from : date('Y-m-d');
        $this->to = $to ? $to : date('Y-m-d');
    }

    public function view(): View
    {
        $masterLines = EmployeeLine::where("tanggal", ">=", $this->from)->where("tanggal", "<=", $this->to)->get();

        return view("sewing.export.master-line-export", [
            "from" => $this->from,
            "to" => $this->to,
            "masterLines" => $masterLines,
        ]);
    }
}
