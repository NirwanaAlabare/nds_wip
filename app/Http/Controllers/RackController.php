<?php

namespace App\Http\Controllers;

use App\Models\Rack;
use App\Models\RackDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use QrCode;
use PDF;

class RackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $rackQuery = Rack::selectRaw("
                    rack.id,
                    rack.kode,
                    rack.nama_rak,
                    COUNT(DISTINCT rack_detail.id) total_ruang
                ")->
                leftJoin("rack_detail", "rack_detail.rack_id", "=", "rack.id")->
                groupBy("rack.id");

            return DataTables::eloquent($rackQuery)->
                filterColumn('kode', function ($query, $keyword) {
                    $query->whereRaw("LOWER(kode) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('nama_rak', function ($query, $keyword) {
                    $query->whereRaw("LOWER(nama_rak) LIKE LOWER('%" . $keyword . "%')");
                })->order(function ($query) {
                    $query->
                        orderBy('rack.kode', 'desc')->
                        orderBy('rack.updated_at', 'desc');
                })->toJson();
        }

        return view("rack.rack", ["page" => "dashboard-dc", "subPageGroup" => "rak-dc", "subPage" => "rack"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('rack.create-rack', ["page" => "dashboard-dc", "subPageGroup" => "rak-dc", "subPage" => "rack"]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedRequest = $request->validate([
            "nama_rak" => "required|unique:rack,nama_rak,except,id",
            "jumlah_baris" => "required|numeric|min:1",
            "jumlah_ruang" => "required|numeric|min:1",
        ]);

        $lastRack = Rack::select('kode')->orderBy('id', 'desc')->first();
        $rackNumber = $lastRack ? intval(substr($lastRack->kode, -5)) + 1 : 1;

        if ($validatedRequest['jumlah_baris'] > 0 && $validatedRequest['jumlah_ruang'] > 0) {
            for ($n = 0; $n < $validatedRequest['jumlah_baris']; $n++) {
                $rackCode = 'RAK' . sprintf('%05s', $rackNumber + $n);

                $storeRack = Rack::create([
                    "kode" => $rackCode,
                    "nama_rak" => $validatedRequest['nama_rak'].".".($n+1),
                ]);

                $lastRackDetail = RackDetail::select('kode')->orderBy('id', 'desc')->first();
                $rackDetailNumber = $lastRackDetail ? intval(substr($lastRackDetail->kode, -5)) + 1 : 1;

                $rackDetailData = [];
                for ($i = 0; $i < $validatedRequest['jumlah_ruang']; $i++) {
                    array_push($rackDetailData, [
                        "kode" => 'DRK' . sprintf('%05s', $rackDetailNumber + $i),
                        "rack_id" => $storeRack->id,
                        "nama_detail_rak" => $validatedRequest['nama_rak'].".".($n+1).".".($i+1),
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now(),
                    ]);
                }

                $storeRackDetail = RackDetail::insert($rackDetailData);
            }

            return array(
                "status" => 200,
                "message" => $rackCode,
                "additional" => [],
                "redirect" => ""
            );
        } else {
            return array(
                "status" => 400,
                "message" => "Jumlah ruang dan baris tidak bisa 0",
                "additional" => [],
                "redirect" => ""
            );
        }

        return array(
            "status" => 400,
            "message" => "Terjadi kesalahan",
            "additional" => [],
            "redirect" => ""
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Rack  $rack
     * @return \Illuminate\Http\Response
     */
    public function show(Rack $rack)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Rack  $rack
     * @return \Illuminate\Http\Response
     */
    public function edit(Rack $rack)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Rack  $rack
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validatedRequest = $request->validate([
            "edit_id" => "required",
            "edit_nama_rak" => "required|unique:rack,nama_rak,".$request['edit_id'],
            "edit_total_ruang" => "required|min:1",

        ],[
            'edit_nama_rak.required' => 'Harap tentukan nama rak',
            'edit_nama_rak.unique' => 'Nama rak sudah ada',
            'edit_total_ruang' => "Jumlah ruang tidak bisa nol",
        ]);

        $updateRack = Rack::where('id', $validatedRequest['edit_id'])->update([
            "nama_rak" => $validatedRequest['edit_nama_rak'],
        ]);

        if ($updateRack) {
            // Rack Detail
            $rackData = Rack::where('id', $validatedRequest['edit_id'])->first();
            $rackDetailCount = $rackData->rackDetails->count();

            if ($validatedRequest['edit_total_ruang'] > $rackDetailCount) {
                // Rack Detail if fewer
                $lastRackDetail = RackDetail::select('kode')->orderBy('updated_at', 'desc')->first();
                $rackDetailNumber = $lastRackDetail ? intval(substr($lastRackDetail->kode, -5)) + 1 : 1;

                $rackDetailData = [];
                for ($i = 0; $i < $validatedRequest['edit_total_ruang'] - $rackDetailCount; $i++) {
                    array_push($rackDetailData, [
                        "kode" => 'DRK' . sprintf('%05s', $rackDetailNumber + $i),
                        "rack_id" => $validatedRequest['edit_id'],
                        "nama_detail_rak" => $validatedRequest['edit_nama_rak'].".".($i+($rackDetailCount+1)),
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now(),
                    ]);
                }

                $storeRackDetail = RackDetail::insert($rackDetailData);

            } else if ($validatedRequest['edit_total_ruang'] < $rackDetailCount) {
                // Rack Detail if more
                $rackDetails = $rackData->rackDetails->sortByDesc('id')->take(($rackDetailCount - $validatedRequest['edit_total_ruang']));

                $rackDetailIds = [];
                foreach ($rackDetails as $rackDetail) {
                    array_push($rackDetailIds, $rackDetail->id);
                }

                $deleteRackDetail = RackDetail::whereIn('id', $rackDetailIds)->delete();
            }

            // Rack Detail Name
            $i = 0;
            $rackDetailData = RackDetail::where('rack_id', $validatedRequest['edit_id'])->orderBy('id', 'asc')->get();
            foreach ($rackDetailData as $rackDetail) {
                RackDetail::where('id', $rackDetail->id)->update([
                    "nama_detail_rak" => $validatedRequest['edit_nama_rak'].".".($i+1),
                    "updated_at" => Carbon::now(),
                ]);

                $i++;
            }

            return array(
                "status" => 200,
                "message" => "Rak '".$rackData->kode."' Berhasil Di Ubah",
                "additional" => [],
                "table" => "datatable-rack",
                "redirect" => ""
            );
        }

        return array(
            "status" => 400,
            "message" => "Terjadi kesalahan",
            "additional" => [],
            "redirect" => ""
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Rack  $rack
     * @return \Illuminate\Http\Response
     */
    public function destroy(Rack $rack, $id = 0)
    {
        $thisRack = Rack::selectRaw("
                rack.nama_rak,
                COUNT(rack_detail_stocker.id) stocker_rack
            ")->
            leftJoin("rack_detail", "rack_detail.rack_id", "rack.id")->
            leftJoin("rack_detail_stocker", "rack_detail_stocker.detail_rack_id", "rack_detail.id")->
            where('rack.id', $id)->
            groupBy('rack.id')->
            first();

        if ($thisRack->stocker_rack < 1) {
            $deleteRack = Rack::where('id', $id)->delete();

            if ($deleteRack) {
                $deleteRackDetail = RackDetail::where('rack_id', $id)->delete();
            }

            return array(
                "status" => 200,
                "message" => "Rak '".$thisRack->nama_rak."' Berhasil Di Hapus",
                "additional" => [],
                "table" => "datatable-rack",
                "redirect" => ""
            );
        }

        return array(
            "status" => 400,
            "message" => "Rak '".$thisRack->nama_rak."' sudah terisi",
            "additional" => [],
            "redirect" => ""
        );
    }

    public function printRack(Request $request, $id = 0) {
        $dataRack = Rack::where('id', $id)->first();

        if ($dataRack) {
            PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
            $pdf = PDF::loadView('rack.pdf.print-rack', ["dataRack" => $dataRack])->setPaper('a4', 'landscape');

            $path = public_path('pdf/');
            $fileName = 'rack-'.$dataRack->nama_rak.'.pdf';
            $pdf->save($path . '/' . $fileName);
            $generatedFilePath = public_path('pdf/'.$fileName);

            return response()->download($generatedFilePath);
        }
    }
}
