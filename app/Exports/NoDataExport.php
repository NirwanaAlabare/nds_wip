<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Illuminate\Support\Collection;

class NoDataExport implements FromArray
{
    public function array(): array
    {
        return [
            ['nodata']
        ];
    }
}
