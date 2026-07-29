<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;

class ImportAssetMasterTab implements ToCollection
{
    const EXPECTED_HEADERS = ['RFID Code', 'Line Code', 'Tab Code'];

    public int $inserted = 0;
    public array $duplicates = [];
    public bool $invalidFormat = false;

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty() || !$this->isValidHeader($rows->first())) {
            $this->invalidFormat = true;
            return;
        }

        $dataRows = $rows->slice(1);

        $validRows = [];
        $seen = [];
        $duplicates = [];

        foreach ($dataRows as $row) {
            $rfid_code = trim($row[0] ?? '');
            $line_code = trim($row[1] ?? '');
            $tab_code = trim($row[2] ?? '');

            if (!$rfid_code) {
                continue;
            }

            if (isset($seen[$rfid_code])) {
                $duplicates[$rfid_code] = true;
            }
            $seen[$rfid_code] = true;

            $validRows[] = [$rfid_code, $line_code, $tab_code];
        }

        if (!empty($duplicates)) {
            $this->duplicates = array_keys($duplicates);
            return;
        }

        if (empty($validRows)) {
            return;
        }

        $existing = DB::table('asset_master_tab')
            ->whereIn('rfid_code', array_column($validRows, 0))
            ->pluck('rfid_code')
            ->all();

        if (!empty($existing)) {
            $this->duplicates = $existing;
            return;
        }

        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        foreach ($validRows as [$rfid_code, $line_code, $tab_code]) {
            DB::insert("INSERT INTO asset_master_tab (
                rfid_code,
                line_code,
                tab_code,
                lokasi,
                status,
                created_by,
                created_at,
                updated_at
            ) VALUES (?,?,?,?,?,?,?,?)", [
                $rfid_code,
                $line_code,
                $tab_code,
                'IT',
                'IDLE',
                $user,
                $timestamp,
                $timestamp
            ]);

            $this->inserted++;
        }
    }

    private function isValidHeader($headerRow)
    {
        if (!$headerRow) {
            return false;
        }

        foreach (self::EXPECTED_HEADERS as $i => $expected) {
            $actual = trim($headerRow[$i] ?? '');

            if (strcasecmp($actual, $expected) !== 0) {
                return false;
            }
        }

        return true;
    }
}
