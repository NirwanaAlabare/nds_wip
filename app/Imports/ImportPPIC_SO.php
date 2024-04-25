<?php

namespace App\Imports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Models\PPIC_master_so_tmp;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithStartRow;


class ImportPPIC_SO implements ToModel, WithStartRow
{
    public function startRow(): int
    {
        return 2;
    }
    public function model(array $row)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        return new PPIC_master_so_tmp([
            'id_tmp' => '',
            'id_so_det' => $row[0],
            'barcode' => $row[1],
            'po' => $row[2],
            'dest' => $row[3],
            'qty_po' => $row[4],
            'tgl_shipment' => $row[5],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
            'created_by' => $user,
        ]);
    }
}
