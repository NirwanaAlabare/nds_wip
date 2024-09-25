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
            'ws' => $row[0],
            'style' => $row[1],
            'desc' => $row[2],
            'po' => $row[3],
            'color' => $row[4],
            'size' => $row[5],
            'dest' => $row[6],
            'barcode' => $row[7],
            'qty_po' => $row[8],
            'buyer' => $row[9],
            'tgl_shipment' => $row[10],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
            'created_by' => $user,
        ]);
    }
}
