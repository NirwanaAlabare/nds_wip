<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\Summary\MasterKursBi;
use App\Models\Summary\MasterKursBiSB;
use App\Models\Summary\DataDetailProduksiDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Goutte\Client;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class MasterKursBiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $masterKursBi = MasterKursBiSB::query();

            return
            DataTables::eloquent($masterKursBi)->
                order(
                    function ($query) {
                        $query->orderBy('kode_kurs_bi', 'desc');
                    }
                )->toJson();
        }

        return view('sewing.master.master-kurs-bi', ['parentPage' => 'master', 'page' => 'dashboard-sewing-effy']);
    }

    /**
     * Fetch data.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData()
    {
        return DataTables::of(MasterKursBi::orderBy('kode_kurs_bi', 'desc')->get())->toJson();
    }

    // Legacy 1
    // public function scrapData(Request $request)
    // {
    //     // set operator
    //     $operator = Auth::user()->username;

    //     // tanggal kurs request
    //     $tanggalRequest = $request->date;

    //     // latest tanggal kurs from db
    //     $latestTanggalKurs = MasterKursBiSB::max("tanggal_kurs_bi");

    //     // scrapping configuration
    //     $goutteClient = new Client();
    //     $crawler = $goutteClient->request('GET', 'https://datacenter.ortax.org/ortax/kursbi/show/USD');
    //     $tmpData = $crawler->filter('.table')->each(function ($node) {
    //         return $node->text();
    //     });

    //     // variables
    //     $countTanggalKurs = 0;
    //     $tanggalKursSuccess = [];
    //     $tanggalKursUnavailable = [];

    //     // data
    //     $data = $tmpData[0];

    //     // explode data to array
    //     $explodeData = explode(" ", $data);

    //     // loop data per date range
    //     for($i=8;$i<count($explodeData);$i+=7) {
    //         // set data position
    //         $i_date = $i;
    //         $i_month = $i+1;
    //         $i_year = $i+2;
    //         $i_kursjual = $i+3;
    //         $i_kursbeli = $i+4;
    //         $i_kurstengah = $i+5;

    //         $tahunKurs = $explodeData[$i_year];
    //         $namaBulanKurs = $explodeData[$i_month];
    //         $tanggalKurs = $explodeData[$i_date];

    //         // set nomor bulan kurs
    //         switch($namaBulanKurs) {
    //             case "Januari":
    //                 $bulanKurs = "01";
    //                 break;
    //             case "Februari":
    //                 $bulanKurs = "02";
    //                 break;
    //             case "Maret":
    //                 $bulanKurs = "03";
    //                 break;
    //             case "April":
    //                 $bulanKurs = "04";
    //                 break;
    //             case "Mei":
    //                 $bulanKurs = "05";
    //                 break;
    //             case "Juni":
    //                 $bulanKurs = "06";
    //                 break;
    //             case "Juli":
    //                 $bulanKurs = "07";
    //                 break;
    //             case "Agustus":
    //                 $bulanKurs = "08";
    //                 break;
    //             case "September":
    //                 $bulanKurs = "09";
    //                 break;
    //             case "Oktober":
    //                 $bulanKurs = "10";
    //                 break;
    //             case "November":
    //                 $bulanKurs = "11";
    //                 break;
    //             case "Desember":
    //                 $bulanKurs = "12";
    //                 break;
    //             default:
    //                 $bulanKurs = "00";
    //                 $namaBulanKurs = "TIDAK DIKENAL";
    //         }

    //         // build full tanggal kurs
    //         $fullTanggalKurs =  $tahunKurs."-".$bulanKurs."-".$tanggalKurs;

    //         // when tanggal kurs is below tanggal request
    //         $isNow = strtotime($tanggalRequest) == strtotime($latestTanggalKurs);
    //         $isNowCondition = $isNow ? strtotime($fullTanggalKurs) >= strtotime($latestTanggalKurs) : strtotime($fullTanggalKurs) > strtotime($latestTanggalKurs);
    //         if (strtotime($fullTanggalKurs) <= strtotime($tanggalRequest) && $isNowCondition) {
    //             $countTanggalKurs++;

    //             // set kurs
    //             $kursJual = str_replace(",",".",str_replace(".","",$explodeData[$i_kursjual]));
    //             $kursBeli = str_replace(",",".",str_replace(".","",$explodeData[$i_kursbeli]));
    //             $kursTengah = str_replace(",",".",str_replace(".","",$explodeData[$i_kurstengah]));

    //             // set kode kurs
    //             $mataUang = 'USD';
    //             $kodeKursBi = $mataUang.str_replace("-","",$fullTanggalKurs);

    //             // already exist condition
    //             $exist = MasterKursBiSB::where("kode_kurs_bi", $kodeKursBi)->count();
    //             if($exist > 0) {
    //                 $updateKursBi = MasterKursBiSB::where("kode_kurs_bi", $kodeKursBi)
    //                 ->update([
    //                     'tanggal_kurs_bi' => $fullTanggalKurs,
    //                     'mata_uang' => $mataUang,
    //                     'kurs_jual' => $kursJual,
    //                     'kurs_beli' => $kursBeli,
    //                     'kurs_tengah' => $kursTengah,
    //                     'operator' => $operator
    //                 ]);

    //                 if ($updateKursBi) {
    //                     array_push($tanggalKursSuccess, $fullTanggalKurs);
    //                 } else {
    //                     array_push($tanggalKursUnavailable, $fullTanggalKurs);
    //                 }
    //             } else {
    //                 $createKursBi = MasterKursBiSB::create([
    //                     'kode_kurs_bi' => $kodeKursBi,
    //                     'tanggal_kurs_bi' => $fullTanggalKurs,
    //                     'mata_uang' => $mataUang,
    //                     'kurs_jual' => $kursJual,
    //                     'kurs_beli' => $kursBeli,
    //                     'kurs_tengah' => $kursTengah,
    //                     'operator' => $operator
    //                 ]);

    //                 if ($createKursBi) {
    //                     array_push($tanggalKursSuccess, $fullTanggalKurs);
    //                 } else {
    //                     array_push($tanggalKursUnavailable, $fullTanggalKurs);
    //                 }
    //             }

    //             if (isset($createKursBi)) {
    //                 DataDetailProduksiDay::where('tgl_produksi', $fullTanggalKurs)->
    //                     update([
    //                         'kurs_bi_id' => $createKursBi->id
    //                     ]);
    //             }
    //         }
    //     }

    //     // return scrap result
    //     if(count($tanggalKursSuccess) < 1) {
    //         $result = array(
    //             "status" => "error",
    //             "message" => "<b>ERROR:</b> Data kurs terbaru hingga tanggal ".$tanggalRequest." tidak ditemukan"
    //         );
    //     } else {
    //         $detailProduksiDaySuccess = DataDetailProduksiDay::whereIn('tgl_produksi', $tanggalKursSuccess)->
    //             orderBy('tgl_produksi', 'desc')->get();

    //         foreach ($detailProduksiDaySuccess as $day) {
    //             $kursBi = MasterKursBiSB::where('tanggal_kurs_bi', $day->tgl_produksi)->first();
    //             $earning = $day->earning;
    //             $kursEarning = $day->dataDetailProduksi->dataProduksi->kode_mata_uang != 'IDR' ? $earning*$kursBi->kurs_tengah : 0;

    //             DataDetailProduksiDay::where('id', $day->id)->
    //                 update([
    //                     'kurs_earning' => $kursEarning
    //                 ]);
    //         }

    //         $result = array(
    //             "status" => "success",
    //             "tanggalSuccess" => $tanggalKursSuccess,
    //             "tanggalUnavailable" => $tanggalKursUnavailable
    //         );
    //     }

    //     return $result;
    // }
    // Legacy 2
    // public function scrapData(Request $request)
    // {
    //     // set operator
    //     $operator = Auth::user()->username;

    //     // tanggal kurs request
    //     $tanggalRequest = $request->date;

    //     // latest tanggal kurs from db
    //     $latestTanggalKurs = MasterKursBiSB::max("tanggal_kurs_bi");

    //     // scrapping configuration
    //     $goutteClient = new Client();
    //     $crawler = $goutteClient->request('GET', 'https://datacenter.ortax.org/ortax/kursbi/show/USD');
    //     $tmpData = $crawler->filter('table tr td')->each(function ($node) {
    //         return $node->text();
    //     });

    //     // variables
    //     $countTanggalKurs = 0;

    //     // data
    //     $dataTanggal = $tmpData[0];

    //     // explode data to array
    //     $explodeData = explode(" ", $data);

    //     // loop data per date range
    //     for($i=8;$i<count($explodeData);$i+=7) {
    //         // set data position
    //         $i_date = $i;
    //         $i_month = $i+1;
    //         $i_year = $i+2;
    //         $i_kursjual = $i+3;
    //         $i_kursbeli = $i+4;
    //         $i_kurstengah = $i+5;

    //         $tahunKurs = $explodeData[$i_year];
    //         $namaBulanKurs = $explodeData[$i_month];
    //         $tanggalKurs = $explodeData[$i_date];

    //         // set nomor bulan kurs
    //         switch($namaBulanKurs) {
    //             case "Januari":
    //                 $bulanKurs = "01";
    //                 break;
    //             case "Februari":
    //                 $bulanKurs = "02";
    //                 break;
    //             case "Maret":
    //                 $bulanKurs = "03";
    //                 break;
    //             case "April":
    //                 $bulanKurs = "04";
    //                 break;
    //             case "Mei":
    //                 $bulanKurs = "05";
    //                 break;
    //             case "Juni":
    //                 $bulanKurs = "06";
    //                 break;
    //             case "Juli":
    //                 $bulanKurs = "07";
    //                 break;
    //             case "Agustus":
    //                 $bulanKurs = "08";
    //                 break;
    //             case "September":
    //                 $bulanKurs = "09";
    //                 break;
    //             case "Oktober":
    //                 $bulanKurs = "10";
    //                 break;
    //             case "November":
    //                 $bulanKurs = "11";
    //                 break;
    //             case "Desember":
    //                 $bulanKurs = "12";
    //                 break;
    //             default:
    //                 $bulanKurs = "00";
    //                 $namaBulanKurs = "TIDAK DIKENAL";
    //         }

    //         // build full tanggal kurs
    //         $fullTanggalKurs =  $tahunKurs."-".$bulanKurs."-".$tanggalKurs;

    //         // when tanggal kurs is below tanggal request
    //         $isNow = strtotime($tanggalRequest) == strtotime($latestTanggalKurs);
    //         $isNowCondition = $isNow ? strtotime($fullTanggalKurs) >= strtotime($latestTanggalKurs) : strtotime($fullTanggalKurs) > strtotime($latestTanggalKurs);
    //         if (strtotime($fullTanggalKurs) <= strtotime($tanggalRequest) && $isNowCondition) {
    //             $countTanggalKurs++;

    //             // set kurs
    //             $kursJual = str_replace(",",".",str_replace(".","",$explodeData[$i_kursjual]));
    //             $kursBeli = str_replace(",",".",str_replace(".","",$explodeData[$i_kursbeli]));
    //             $kursTengah = str_replace(",",".",str_replace(".","",$explodeData[$i_kurstengah]));

    //             // set kode kurs
    //             $mataUang = 'USD';
    //             $kodeKursBi = $mataUang.str_replace("-","",$fullTanggalKurs);

    //             // already exist condition
    //             $exist = MasterKursBiSB::where("kode_kurs_bi", $kodeKursBi)->count();
    //             if($exist > 0) {
    //                 $updateKursBi = MasterKursBiSB::where("kode_kurs_bi", $kodeKursBi)
    //                 ->update([
    //                     'tanggal_kurs_bi' => $fullTanggalKurs,
    //                     'mata_uang' => $mataUang,
    //                     'kurs_jual' => $kursJual,
    //                     'kurs_beli' => $kursBeli,
    //                     'kurs_tengah' => $kursTengah,
    //                     'operator' => $operator
    //                 ]);

    //                 if ($updateKursBi) {
    //                     array_push($tanggalKursSuccess, $fullTanggalKurs);
    //                 } else {
    //                     array_push($tanggalKursUnavailable, $fullTanggalKurs);
    //                 }
    //             } else {
    //                 $createKursBi = MasterKursBiSB::create([
    //                     'kode_kurs_bi' => $kodeKursBi,
    //                     'tanggal_kurs_bi' => $fullTanggalKurs,
    //                     'mata_uang' => $mataUang,
    //                     'kurs_jual' => $kursJual,
    //                     'kurs_beli' => $kursBeli,
    //                     'kurs_tengah' => $kursTengah,
    //                     'operator' => $operator
    //                 ]);

    //                 if ($createKursBi) {
    //                     array_push($tanggalKursSuccess, $fullTanggalKurs);
    //                 } else {
    //                     array_push($tanggalKursUnavailable, $fullTanggalKurs);
    //                 }
    //             }

    //             // if (isset($createKursBi)) {
    //             //     DataDetailProduksiDay::where('tgl_produksi', $fullTanggalKurs)->
    //             //         update([
    //             //             'kurs_bi_id' => $createKursBi->id
    //             //         ]);
    //             // }
    //         }
    //     }

    //     // return scrap result
    //     if(count($tanggalKursSuccess) < 1) {
    //         $result = array(
    //             "status" => "error",
    //             "message" => "<b>ERROR:</b> Data kurs terbaru hingga tanggal ".$tanggalRequest." tidak ditemukan"
    //         );
    //     } else {
    //         // $detailProduksiDaySuccess = DataDetailProduksiDay::whereIn('tgl_produksi', $tanggalKursSuccess)->
    //         //     orderBy('tgl_produksi', 'desc')->get();

    //         // foreach ($detailProduksiDaySuccess as $day) {
    //         //     $kursBi = MasterKursBiSB::where('tanggal_kurs_bi', $day->tgl_produksi)->first();
    //         //     $earning = $day->earning;
    //         //     $kursEarning = $day->dataDetailProduksi->dataProduksi->kode_mata_uang != 'IDR' ? $earning*$kursBi->kurs_tengah : 0;

    //         //     DataDetailProduksiDay::where('id', $day->id)->
    //         //         update([
    //         //             'kurs_earning' => $kursEarning
    //         //         ]);
    //         // }

    //         $result = array(
    //             "status" => "success",
    //             "tanggalSuccess" => $tanggalKursSuccess,
    //             "tanggalUnavailable" => $tanggalKursUnavailable
    //         );
    //     }

    //     return $result;
    // }

    // Legacy 3
    // New Vesion of Ortax
    // public function scrapData(Request $request)
    // {
    //     // set operator
    //     $operator = Auth::user()->username;

    //     // tanggal kurs request
    //     $tanggalRequest = $request->date;

    //     // latest tanggal kurs from db
    //     $latestTanggalKurs = MasterKursBiSB::max("tanggal_kurs_bi");

    //     // scrapping configuration
    //     $goutteClient = new Client();
    //     $crawler = $goutteClient->request('GET', 'https://datacenter.ortax.org/ortax/kursbi/show/USD');
    //     $tmpData = $crawler->filter('table tbody tr')->each(function ($node) {
    //         return $node->text();
    //     });

    //     // variables
    //     $countTanggalKurs = 0;
    //     $tanggalKursSuccess = [];
    //     $tanggalKursUnavailable = [];

    //     // loop
    //     foreach ($tmpData as $index => $data) {
    //         if (!str_contains($data, "Kurs")) {
    //             // explode data to array
    //             $explodeData = explode("Rp", $data);

    //             // set tanggal
    //             $tanggalRange = explode(" - ", $explodeData[0]);
    //             $tanggalAwal = explode(" ", $tanggalRange[0]);

    //             $tanggalKurs = $tanggalAwal[0];
    //             $namaBulanKurs = $tanggalAwal[1];
    //             $tahunKurs = $tanggalAwal[2];

    //             // set nomor bulan kurs
    //             switch($namaBulanKurs) {
    //                 case "January":
    //                     $bulanKurs = "01";
    //                     break;
    //                 case "February":
    //                     $bulanKurs = "02";
    //                     break;
    //                 case "March":
    //                     $bulanKurs = "03";
    //                     break;
    //                 case "April":
    //                     $bulanKurs = "04";
    //                     break;
    //                 case "May":
    //                     $bulanKurs = "05";
    //                     break;
    //                 case "June":
    //                     $bulanKurs = "06";
    //                     break;
    //                 case "July":
    //                     $bulanKurs = "07";
    //                     break;
    //                 case "August":
    //                     $bulanKurs = "08";
    //                     break;
    //                 case "September":
    //                     $bulanKurs = "09";
    //                     break;
    //                 case "October":
    //                     $bulanKurs = "10";
    //                     break;
    //                 case "November":
    //                     $bulanKurs = "11";
    //                     break;
    //                 case "December":
    //                     $bulanKurs = "12";
    //                     break;
    //                 default:
    //                     $bulanKurs = "00";
    //                     $namaBulanKurs = "TIDAK DIKENAL";
    //             }

    //             // build full tanggal kurs
    //             $fullTanggalKurs =  $tahunKurs."-".$bulanKurs."-".$tanggalKurs;

    //             $kursJualRaw = $explodeData[1];
    //             $kursBeliRaw = $explodeData[2];
    //             $kursTengahRaw = $explodeData[3];

    //             // when tanggal kurs is below tanggal request
    //             $isNow = strtotime($tanggalRequest) == strtotime($latestTanggalKurs);
    //             $isNowCondition = $isNow ? strtotime($fullTanggalKurs) >= strtotime($latestTanggalKurs) : strtotime($fullTanggalKurs) > strtotime($latestTanggalKurs);
    //             if (strtotime($fullTanggalKurs) <= strtotime($tanggalRequest) && $isNowCondition) {
    //                 $countTanggalKurs++;

    //                 // set kurs
    //                 $kursJual = str_replace(",",".",str_replace(".","",$kursJualRaw));
    //                 $kursBeli = str_replace(",",".",str_replace(".","",$kursBeliRaw));
    //                 $kursTengah = str_replace(",",".",str_replace(".","",$kursTengahRaw));

    //                 // set kode kurs
    //                 $mataUang = 'USD';
    //                 $kodeKursBi = $mataUang.str_replace("-","",$fullTanggalKurs);

    //                 // already exist condition
    //                 $exist = MasterKursBiSB::where("kode_kurs_bi", $kodeKursBi)->count();
    //                 if($exist > 0) {
    //                     $updateKursBi = MasterKursBiSB::where("kode_kurs_bi", $kodeKursBi)
    //                     ->update([
    //                         'tanggal_kurs_bi' => $fullTanggalKurs,
    //                         'mata_uang' => $mataUang,
    //                         'kurs_jual' => $kursJual,
    //                         'kurs_beli' => $kursBeli,
    //                         'kurs_tengah' => $kursTengah,
    //                         'operator' => $operator
    //                     ]);

    //                     if ($updateKursBi) {
    //                         array_push($tanggalKursSuccess, $fullTanggalKurs);
    //                     } else {
    //                         array_push($tanggalKursUnavailable, $fullTanggalKurs);
    //                     }
    //                 } else {
    //                     $createKursBi = MasterKursBiSB::create([
    //                         'kode_kurs_bi' => $kodeKursBi,
    //                         'tanggal_kurs_bi' => $fullTanggalKurs,
    //                         'mata_uang' => $mataUang,
    //                         'kurs_jual' => $kursJual,
    //                         'kurs_beli' => $kursBeli,
    //                         'kurs_tengah' => $kursTengah,
    //                         'operator' => $operator
    //                     ]);

    //                     if ($createKursBi) {
    //                         array_push($tanggalKursSuccess, $fullTanggalKurs);
    //                     } else {
    //                         array_push($tanggalKursUnavailable, $fullTanggalKurs);
    //                     }
    //                 }

    //                 // if (isset($createKursBi)) {
    //                 //     DataDetailProduksiDay::where('tgl_produksi', $fullTanggalKurs)->
    //                 //         update([
    //                 //             'kurs_bi_id' => $createKursBi->id
    //                 //         ]);
    //                 // }
    //             }
    //         }
    //     }

    //     // return scrap result
    //     if(count($tanggalKursSuccess) < 1) {
    //         $result = array(
    //             "status" => "error",
    //             "message" => "<b>ERROR:</b> Data kurs terbaru hingga tanggal ".$tanggalRequest." tidak ditemukan"
    //         );
    //     } else {
    //         // $detailProduksiDaySuccess = DataDetailProduksiDay::whereIn('tgl_produksi', $tanggalKursSuccess)->
    //         //     orderBy('tgl_produksi', 'desc')->get();

    //         // foreach ($detailProduksiDaySuccess as $day) {
    //         //     $kursBi = MasterKursBiSB::where('tanggal_kurs_bi', $day->tgl_produksi)->first();
    //         //     $earning = $day->earning;
    //         //     $kursEarning = $day->dataDetailProduksi->dataProduksi->kode_mata_uang != 'IDR' ? $earning*$kursBi->kurs_tengah : 0;

    //         //     DataDetailProduksiDay::where('id', $day->id)->
    //         //         update([
    //         //             'kurs_earning' => $kursEarning
    //         //         ]);
    //         // }

    //         $result = array(
    //             "status" => "success",
    //             "tanggalSuccess" => $tanggalKursSuccess,
    //             "tanggalUnavailable" => $tanggalKursUnavailable
    //         );
    //     }

    //     return $result;
    // }

    // New Conditional
    public function scrapData(Request $request)
    {
        // set operator
        $operator = Auth::user()->username;

        // tanggal kurs request
        $tanggalRequest = Carbon::parse($request->date);

        // latest tanggal kurs from db
        $latestTanggalKurs = Carbon::parse(MasterKursBiSB::max("tanggal_kurs_bi"));

        // scrapping configuration
        $goutteClient = new Client();
        $crawler = $goutteClient->request('GET', 'https://datacenter.ortax.org/ortax/kursbi/show/USD');
        $tmpData = $crawler->filter('table tbody tr')->each(function ($node) {
            return $node->text();
        });

        // variables
        $countTanggalKurs = 0;
        $tanggalKursSuccess = [];
        $tanggalKursUnavailable = [];

        // loop
        foreach ($tmpData as $index => $data) {
            if (!str_contains($data, "Kurs")) {
                // explode data to array
                $explodeData = explode("Rp", $data);

                // set tanggal
                $tanggalRange = explode(" - ", $explodeData[0]);
                $tanggalAwal = explode(" ", $tanggalRange[0]);

                $tanggalKurs = $tanggalAwal[0];
                $namaBulanKurs = $tanggalAwal[1];
                $tahunKurs = $tanggalAwal[2];

                // set nomor bulan kurs
                switch($namaBulanKurs) {
                    case "January":
                        $bulanKurs = "01";
                        break;
                    case "February":
                        $bulanKurs = "02";
                        break;
                    case "March":
                        $bulanKurs = "03";
                        break;
                    case "April":
                        $bulanKurs = "04";
                        break;
                    case "May":
                        $bulanKurs = "05";
                        break;
                    case "June":
                        $bulanKurs = "06";
                        break;
                    case "July":
                        $bulanKurs = "07";
                        break;
                    case "August":
                        $bulanKurs = "08";
                        break;
                    case "September":
                        $bulanKurs = "09";
                        break;
                    case "October":
                        $bulanKurs = "10";
                        break;
                    case "November":
                        $bulanKurs = "11";
                        break;
                    case "December":
                        $bulanKurs = "12";
                        break;
                    default:
                        $bulanKurs = "00";
                        $namaBulanKurs = "TIDAK DIKENAL";
                }

                // build full tanggal kurs
                $fullTanggalKurs =  $tahunKurs."-".$bulanKurs."-".$tanggalKurs;

                $kursJualRaw = $explodeData[1];
                $kursBeliRaw = $explodeData[2];
                $kursTengahRaw = $explodeData[3];

                // when tanggal kurs is below tanggal request
                $isNow = strtotime($tanggalRequest) == strtotime($latestTanggalKurs);
                $isNowCondition = $isNow ? strtotime($fullTanggalKurs) >= strtotime($latestTanggalKurs) : strtotime($fullTanggalKurs) > strtotime($latestTanggalKurs);
                if ((strtotime($fullTanggalKurs) <= strtotime($tanggalRequest) && $isNowCondition) || strtotime($fullTanggalKurs) == strtotime($tanggalRequest)) {
                    $countTanggalKurs++;

                    // set kurs
                    $kursJual = str_replace(",",".",str_replace(".","",$kursJualRaw));
                    $kursBeli = str_replace(",",".",str_replace(".","",$kursBeliRaw));
                    $kursTengah = str_replace(",",".",str_replace(".","",$kursTengahRaw));

                    // set kode kurs
                    $mataUang = 'USD';
                    $kodeKursBi = $mataUang.str_replace("-","",$fullTanggalKurs);

                    // already exist condition
                    $exist = MasterKursBiSB::where("kode_kurs_bi", $kodeKursBi)->count();
                    if($exist > 0) {
                        $updateKursBi = MasterKursBiSB::where("kode_kurs_bi", $kodeKursBi)
                        ->update([
                            'tanggal_kurs_bi' => $fullTanggalKurs,
                            'mata_uang' => $mataUang,
                            'kurs_jual' => $kursJual,
                            'kurs_beli' => $kursBeli,
                            'kurs_tengah' => $kursTengah,
                            'operator' => $operator
                        ]);

                        if ($updateKursBi) {
                            array_push($tanggalKursSuccess, $fullTanggalKurs);
                        } else {
                            array_push($tanggalKursUnavailable, $fullTanggalKurs);
                        }
                    } else {
                        $createKursBi = MasterKursBiSB::create([
                            'kode_kurs_bi' => $kodeKursBi,
                            'tanggal_kurs_bi' => $fullTanggalKurs,
                            'mata_uang' => $mataUang,
                            'kurs_jual' => $kursJual,
                            'kurs_beli' => $kursBeli,
                            'kurs_tengah' => $kursTengah,
                            'operator' => $operator
                        ]);

                        if ($createKursBi) {
                            array_push($tanggalKursSuccess, $fullTanggalKurs);
                        } else {
                            array_push($tanggalKursUnavailable, $fullTanggalKurs);
                        }
                    }

                    // if (isset($createKursBi)) {
                    //     DataDetailProduksiDay::where('tgl_produksi', $fullTanggalKurs)->
                    //         update([
                    //             'kurs_bi_id' => $createKursBi->id
                    //         ]);
                    // }
                }
            }
        }

        // get period between tanggal kurs
        $period = CarbonPeriod::create($latestTanggalKurs, $tanggalRequest);

        // get all kurs
        $latestKurs = null;
        foreach ($period as $p) {
            $date = $p->format("Y-m-d");

            if ($date) {
                $currentKurs = MasterKursBiSB::where("tanggal_kurs_bi", $date)->first();

                if ($currentKurs) {
                    $latestKurs = $currentKurs;
                } else {
                    if ($latestKurs) {
                        // set kode kurs
                        $mataUang = 'USD';
                        $kodeKursBi = $mataUang.str_replace("-","",$date);

                        $createKursBi = MasterKursBiSB::create([
                            'kode_kurs_bi' => $kodeKursBi,
                            'tanggal_kurs_bi' => $date,
                            'mata_uang' => $latestKurs->mata_uang,
                            'kurs_jual' => $latestKurs->kurs_jual,
                            'kurs_beli' => $latestKurs->kurs_beli,
                            'kurs_tengah' => $latestKurs->kurs_tengah,
                            'operator' => $operator
                        ]);

                        if ($createKursBi) {
                            array_push($tanggalKursSuccess, $date);

                            $latestKurs = $createKursBi;
                        }
                    } else {
                        array_push($tanggalKursUnavailable, $date);
                    }
                }
            }
        }

        // return scrap result
        if(count($tanggalKursSuccess) < 1) {
            $result = array(
                "status" => "error",
                "message" => "<b>ERROR:</b> Data kurs terbaru hingga tanggal ".$tanggalRequest." tidak ditemukan"
            );
        } else {
            // $detailProduksiDaySuccess = DataDetailProduksiDay::whereIn('tgl_produksi', $tanggalKursSuccess)->
            //     orderBy('tgl_produksi', 'desc')->get();

            // foreach ($detailProduksiDaySuccess as $day) {
            //     $kursBi = MasterKursBiSB::where('tanggal_kurs_bi', $day->tgl_produksi)->first();
            //     $earning = $day->earning;
            //     $kursEarning = $day->dataDetailProduksi->dataProduksi->kode_mata_uang != 'IDR' ? $earning*$kursBi->kurs_tengah : 0;

            //     DataDetailProduksiDay::where('id', $day->id)->
            //         update([
            //             'kurs_earning' => $kursEarning
            //         ]);
            // }

            $result = array(
                "status" => "success",
                "tanggalSuccess" => $tanggalKursSuccess,
                "tanggalUnavailable" => $tanggalKursUnavailable
            );
        }

        return $result;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MasterKursBi  $masterKursBi
     * @return \Illuminate\Http\Response
     */
    public function show(MasterKursBi $masterKursBi)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MasterKursBi  $masterKursBi
     * @return \Illuminate\Http\Response
     */
    public function edit(MasterKursBi $masterKursBi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MasterKursBi  $masterKursBi
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MasterKursBi $masterKursBi)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MasterKursBi  $masterKursBi
     * @return \Illuminate\Http\Response
     */
    public function destroy(MasterKursBi $masterKursBi)
    {
        //
    }
}
