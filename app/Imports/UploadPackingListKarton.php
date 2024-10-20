<?php

namespace App\Imports;

use App\Models\Packing_list_upload_karton;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Auth;
use DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithStartRow;


class UploadPackingListKarton implements ToModel, WithStartRow
{
    public function startRow(): int
    {
        return 3;
    }
    public function model(array $row)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        return new Packing_list_upload_karton([
            'po' => $row[0],
            'no_carton_awal' => $row[1],
            'no_carton_akhir' => $row[3],
            'tot_ctn' => $row[4],
            'color' => $row[5],
            'tipe_pack' => $row[6],
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
    }
}
