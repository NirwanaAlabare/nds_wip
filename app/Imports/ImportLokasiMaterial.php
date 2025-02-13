<?php

namespace App\Imports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Models\InMaterialLokTemp;


class ImportLokasiMaterial implements ToModel
{
    

    public function model(array $row)
    {
        return new InMaterialLokTemp([
            'no_lot' => $row[1],
            'no_roll' => $row[2], 
            'no_roll_buyer' => $row[3], 
            'qty_bpb' => $row[4], 
            'qty_aktual' => $row[5], 
            'kode_lok' => $row[6], 
            'created_by' => Auth::user()->name, 
        ]);
    }

    
}
