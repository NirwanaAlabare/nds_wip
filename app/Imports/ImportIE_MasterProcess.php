<?php

namespace App\Imports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Auth;
use DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithStartRow;


class ImportIE_MasterProcess implements ToModel, WithStartRow
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

        // Uppercase semua data
        $nm_process   = strtoupper(trim($row[0]));

        // 🔍 Cek duplicate
        $exists = DB::table('ie_master_process')
            ->where('nm_process', $nm_process)
            ->exists();

        if ($exists) {
            // ❌ STOP seluruh proses import
            throw new \Exception("Duplicate found: " . $nm_process);
        }

        DB::table('ie_master_process')->insert([
            'nm_process'    => $nm_process,
            'class'         => strtoupper($row[1]),
            'machine_type'  => strtoupper($row[2]),
            'smv'           => strtoupper($row[3]),
            'amv'           => strtoupper($row[4]),
            'remark'        => strtoupper($row[5]),
            'created_at'        => $timestamp,
            'updated_at'        => $timestamp,
            'created_by'        => $user,
        ]);
        return null;
    }
}
