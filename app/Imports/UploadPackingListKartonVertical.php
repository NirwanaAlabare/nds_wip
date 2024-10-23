<?php

namespace App\Imports;

use App\Models\Packing_list_upload_karton_vertical;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Auth;
use DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithStartRow;


class UploadPackingListKartonVertical implements ToModel, WithStartRow
{
    public function startRow(): int
    {
        return 2;
    }
    public function model(array $row)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        return new Packing_list_upload_karton_vertical([
            'po' => $row[0],
            'dest' => $row[1],
            'no_carton_awal' => $row[2],
            'no_carton_akhir' => $row[4],
            'color' => $row[5],
            'tipe_pack' => $row[6],
            'size' => $row[7] ?? null,
            'qty' => $row[8] ?? null,
            'created_by' => $user,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }
}
