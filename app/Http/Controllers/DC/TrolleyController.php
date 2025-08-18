<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;
use App\Models\Dc\Trolley;
use App\Models\SignalBit\UserLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use DB;
use PDF;

class TrolleyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = "";

            $trolley = Trolley::with('userLine');

            return DataTables::eloquent($trolley)->
                addColumn('line', function ($row) {
                    $line = $row->userLine ? strtoupper(str_replace("_", " ", $row->userLine->username)) : "";

                    return $line;
                })->
                toJson();
        }

        return view("dc.trolley.master-trolley.trolley", ["page" => "dashboard-dc", "subPageGroup" => "trolley-dc", "subPage" => "trolley"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if ($request->ajax()) {
            $latestLineTrolley = Trolley::select('nama_trolley')->where('line_id', $request->id)->orderBy('id', 'desc')->first();

            return $latestLineTrolley;
        }

        $lines = UserLine::where('Groupp', 'SEWING')->whereRaw('(Locked != 1 OR Locked IS NULL)')->orderBy('username', 'asc')->get();

        return view('dc.trolley.master-trolley.create-trolley', ['page' => 'dashboard-dc', 'subPageGroup' => 'trolley-dc', 'subPage' => 'trolley', 'lines' => $lines]);
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
            "line_id" => "required",
            "nama_trolley" => "required",
            "latest_trolley" => "nullable",
            "jumlah" => "required|numeric|min:1",
        ]);

        $lastTrolley = Trolley::select('kode')->orderBy('id', 'desc')->first();
        $trolleyNumber = $lastTrolley ? intval(substr($lastTrolley->kode, -5)) + 1 : 1;
        $trolleyData = [];

        if ($validatedRequest['jumlah'] > 0) {
            $now = Carbon::now();

            for($i = $validatedRequest['latest_trolley']; $i < ($validatedRequest['latest_trolley'] + $validatedRequest['jumlah']); $i++) {
                $trolleyCode = "TRL".sprintf('%05s', $trolleyNumber + $i);

                array_push($trolleyData, [
                    "kode" => $trolleyCode,
                    "nama_trolley" => $validatedRequest['nama_trolley'].".".($i+1),
                    "line_id" => $validatedRequest['line_id'],
                    "created_at" => $now,
                    "updated_at" => $now,
                ]);
            }

            $storeTrolley = Trolley::insert($trolleyData);

            return array(
                'status' => 200,
                'message' => 'Trolley berhasil ditambahkan',
                'redirect' => '',
                'table' => 'datatable-selected',
                'callback' => 'clearTrolleyTable()',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Jumlah tidak bisa 0',
            'redirect' => '',
            'table' => 'datatable-selected',
            'callback' => 'clearTrolleyTable()',
            'additional' => [],
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Dc\Trolley  $trolley
     * @return \Illuminate\Http\Response
     */
    public function show(Trolley $trolley)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Dc\Trolley  $trolley
     * @return \Illuminate\Http\Response
     */
    public function edit(Trolley $trolley)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Dc\Trolley  $trolley
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Trolley $trolley)
    {
        $validatedRequest = $request->validate([
            "edit_id" => "required",
            "edit_nama_trolley" => "required|unique:trolley,nama_trolley,".$request['edit_id'],

        ],[
            'edit_nama_trolley.required' => 'Harap tentukan nama trolley',
            'edit_nama_trolley.unique' => 'Nama trolley sudah ada',
        ]);

        $trolleyData = Trolley::where('id', $validatedRequest['edit_id'])->first();

        $updateTrolley = Trolley::where('id', $validatedRequest['edit_id'])->update([
            "nama_trolley" => $validatedRequest['edit_nama_trolley'],
        ]);

        if ($updateTrolley) {
            return array(
                "status" => 200,
                "message" => "Rak '".$trolleyData->kode."' Berhasil Di Ubah",
                "additional" => [],
                "table" => "datatable-trolley",
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
     * @param  \App\Models\Dc\Trolley  $trolley
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deleteData = Trolley::where('id', $id)->delete();

        if ($deleteData) {
            return array(
                'status' => 200,
                'message' => 'Trolley berhasil disingkirkan',
                'redirect' => '',
                'table' => 'datatable-trolley',
            );
        }

        return array(
            'status' => 400,
            'message' => 'Trolley gagal disingkirkan',
            'redirect' => '',
            'table' => 'datatable-trolley',
        );
    }

    public function printTrolley(Request $request, $id = 0) {
        $dataTrolley = Trolley::where('id', $id)->first();

        if ($dataTrolley) {
            PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
            $pdf = PDF::loadView('dc.trolley.master-trolley.pdf.print-trolley', ["dataTrolley" => $dataTrolley])->setPaper('a4', 'landscape');

            $fileName = 'trolley-'.$dataTrolley->nama_trolley.'.pdf';

            return $pdf->download(str_replace("/", "_", $fileName));
        }
    }
}
