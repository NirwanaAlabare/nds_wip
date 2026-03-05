<?php

namespace App\Services;

use App\Models\Dc\SecondaryInhouse;
use App\Models\Dc\SecondaryInhouseIn;
use App\Models\Dc\SecondaryIn;
use App\Models\Dc\Stocker;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;
use PDF;

class SecondaryInhouseService
{
    public function checkSecondaryInhouseIn($idQrStocker, $urutan = null) {
        $secondaryInhouseIn = SecondaryInhouseIn::where("id_qr_stocker", $idQrStocker);
        if ($urutan) {
            $secondaryInhouseIn->where("urutan", $urutan);
        }

        $secondaryInhouseInData = $secondaryInhouseIn->first();

        return $secondaryInhouseInData;
    }

    public function checkSecondaryInhouseOut($idQrStocker, $urutan = null) {
        $secondaryInhouseOut = SecondaryInhouse::where("id_qr_stocker", $idQrStocker);
        if ($urutan) {
            $secondaryInhouseOut->where("urutan", $urutan);
        }

        $secondaryInhouseOutData = $secondaryInhouseOut->first();

        return $secondaryInhouseOutData;
    }
}
