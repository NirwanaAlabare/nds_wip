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
    private $po;

    public function __construct($po)
    {

        $this->po = $po;
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
            'po' => $this->po,
            'field_1' => $row[7] ?? null,
            'field_2' => $row[8] ?? null,
            'field_3' => $row[9] ?? null,
            'field_4' => $row[10] ?? null,
            'field_5' => $row[11] ?? null,
            'field_6' => $row[12] ?? null,
            'field_7' => $row[13] ?? null,
            'field_8' => $row[14] ?? null,
            'field_9' => $row[15] ?? null,
            'field_10' => $row[16] ?? null,
            'created_by' => $user,

        ]);

        // Limit the collection to only one record

        $header->save();
    }
}
