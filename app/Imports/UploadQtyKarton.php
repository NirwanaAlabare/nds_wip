<?php

namespace App\Imports;

use App\Models\Packing_master_carton_upload_qty;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Auth;
use DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithStartRow;


class UploadQtyKarton implements ToModel, WithStartRow
{
    public function startRow(): int
    {
        return 2;
    }
    public function model(array $row)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        return new Packing_master_carton_upload_qty([
            'id' => $row[0],
            'po' => $row[1],
            'no_carton' => $row[2],
            'notes' => $row[3],
            'qty_isi' => $row[4],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
            'created_by' => $user,
        ]);
    }
}
