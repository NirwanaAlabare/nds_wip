<?php

namespace App\Imports;

use App\Models\Packing_list_upload_header;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Auth;
use DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithLimit;


class UploadPackingListHeader implements ToModel, WithStartRow, WithLimit
{
    private $txtpo, $dest;

    public function __construct($txtpo, $dest)
    {

        $this->txtpo = $txtpo;
        $this->dest = $dest;
    }

    public function startRow(): int
    {
        return 2;
    }
    public function limit(): int
    {
        return 1; // only take 100 rows
    }
    public function model(array $row)
    {

        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $header = new Packing_list_upload_header([
            'po' => $this->txtpo,
            'dest' => $this->dest,
            'field_1' => $row[8] ?? null,
            'field_2' => $row[9] ?? null,
            'field_3' => $row[10] ?? null,
            'field_4' => $row[11] ?? null,
            'field_5' => $row[12] ?? null,
            'field_6' => $row[13] ?? null,
            'field_7' => $row[14] ?? null,
            'field_8' => $row[15] ?? null,
            'field_9' => $row[16] ?? null,
            'field_10' => $row[17] ?? null,
            'field_11' => $row[18] ?? null,
            'field_12' => $row[19] ?? null,
            'field_13' => $row[20] ?? null,
            'field_14' => $row[21] ?? null,
            'field_15' => $row[22] ?? null,
            'field_16' => $row[23] ?? null,
            'field_17' => $row[24] ?? null,
            'field_18' => $row[25] ?? null,
            'field_19' => $row[26] ?? null,
            'field_20' => $row[27] ?? null,
            'field_21' => $row[28] ?? null,
            'field_22' => $row[29] ?? null,
            'field_23' => $row[30] ?? null,
            'field_24' => $row[31] ?? null,
            'field_25' => $row[32] ?? null,
            'created_by' => $user,

        ]);

        // Limit the collection to only one record

        $header->save();
    }
}
