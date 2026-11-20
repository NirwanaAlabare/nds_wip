<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Hanya membaca file (header: ws, color, size, sub_size).
 * Validasi & penyimpanan dilakukan di MarkerToolsController@importSubSize.
 */
class ImportSubSize implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        //
    }
}
