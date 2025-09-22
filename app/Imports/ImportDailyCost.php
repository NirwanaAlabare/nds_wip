<?php

namespace App\Imports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Models\Daily_cost_tmp;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithStartRow;


class ImportDailyCost implements ToModel, WithStartRow
{
    public function startRow(): int
    {
        return 2;
    }
    public function model(array $row)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        // ✅ Insert directly into mysql_sb
        DB::connection('mysql_sb')->table('mgt_rep_daily_cost_tmp')->insert([
            'no_coa'      => $row[0],
            'projection'  => $row[1],
            'created_at'  => $timestamp,
            'updated_at'  => $timestamp,
            'created_by'  => $user,
        ]);
        return null;
    }
}
