<?php

namespace App\Imports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Models\report_output_adj_tmp;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithStartRow;


class UploadPPICAdjOutput implements ToModel, WithStartRow
{
    public function startRow(): int
    {
        return 2;
    }
    public function model(array $row)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        return new report_output_adj_tmp([
            'id' => '',
            'tgl_adj' => $row[0],
            'ws' => $row[1],
            'buyer' => $row[2],
            'style' => $row[3],
            'color' => $row[4],
            'size' => $row[5],
            'sa_sewing' => $row[6],
            'sa_steam' => $row[7],
            'sa_def_sewing' => $row[8],
            'sa_def_spotcleaning' => $row[9],
            'sa_def_mending' => $row[10],
            'sa_def_pck_sewing' => $row[11],
            'sa_def_pck_spotcleaning' => $row[12],
            'sa_def_pck_mending' => $row[13],
            'sa_pck_line' => $row[14],
            'sa_trf_gmt' => $row[15],
            'sa_pck_central' => $row[16],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
            'created_by' => $user,
        ]);
    }
}
