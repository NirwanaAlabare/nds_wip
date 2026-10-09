<?php

namespace App\Http\Controllers\Part;

use App\Http\Controllers\Controller;
use App\Models\Part\MasterPart;
use App\Models\Part\PartDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class MasterPartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $masterPartsQuery = MasterPart::query();

            return DataTables::eloquent($masterPartsQuery)->
                filterColumn('kode_master_part', function ($query, $keyword) {
                    $query->whereRaw("LOWER(kode_master_part) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('nama_part', function ($query, $keyword) {
                    $query->whereRaw("LOWER(nama_part) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('bag', function ($query, $keyword) {
                    $query->whereRaw("LOWER(bag) LIKE LOWER('%" . $keyword . "%')");
                })->order(function ($query) {
                    $query->orderBy('cancel', 'asc')->orderBy('updated_at', 'desc')->orderBy('kode_master_part', 'desc');
                })->toJson();
        }

        return view("marker.master-part.master-part", ["page" => "dashboard-marker",  "subPageGroup" => "master-marker", "subPage" => "master-part"]);
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
        $validatedRequest = $request->validate([
            "nama_part" => "required|string",
            "bag"       => "required|string",
        ]);

        // Sanitasi multiple space
        $namaPartClean = preg_replace('/\s+/', ' ', trim($validatedRequest["nama_part"]));
        $bagClean      = preg_replace('/\s+/', ' ', trim($validatedRequest["bag"]));

        // Generate kode berdasarkan ID / Kode terakhir
        $lastMasterPart = MasterPart::select('kode_master_part')->orderBy('id', 'desc')->first();
        $lastNumber = $lastMasterPart ? intval(substr($lastMasterPart->kode_master_part, -5)) : 0;
        $masterPartCode = 'MP' . sprintf('%05d', $lastNumber + 1);

        $masterPartStore = MasterPart::create([
            "kode_master_part"    => $masterPartCode,
            "nama_part"           => $namaPartClean,
            "bag"                 => $bagClean,
            "cancel"              => "N",
            "created_by"          => Auth::user()->id,
            "created_by_username" => Auth::user()->username,
        ]);

        if ($masterPartStore) {
            return [
                "status"     => 200,
                "message"    => $masterPartCode,
                "additional" => [],
            ];
        }

        return [
            "status"     => 400,
            "message"    => "Terjadi Kesalahan",
            "additional" => [],
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Part\MasterPart  $masterPart
     * @return \Illuminate\Http\Response
     */
    public function show(MasterPart $masterPart)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Part\MasterPart  $masterPart
     * @return \Illuminate\Http\Response
     */
    public function edit(MasterPart $masterPart)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Part\MasterPart  $masterPart
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validatedRequest = $request->validate([
            "edit_id"        => "required|exists:master_part,id",
            "edit_nama_part" => "required|string",
            "edit_bag"       => "required|string",
        ]);

        // Sanitasi multiple space dan trim
        $namaPartClean = preg_replace('/\s+/', ' ', trim($validatedRequest["edit_nama_part"]));
        $bagClean      = preg_replace('/\s+/', ' ', trim($validatedRequest["edit_bag"]));

        // Cari model terlebih dahulu
        $masterPart = MasterPart::find($validatedRequest['edit_id']);

        if (!$masterPart) {
            return [
                'status'     => 404,
                'message'    => 'Data master part tidak ditemukan',
                'redirect'   => '',
                'table'      => 'datatable-master-part',
                'additional' => [],
            ];
        }

        // Assign nilai baru yang sudah dibersihkan
        $masterPart->nama_part = $namaPartClean;
        $masterPart->bag       = $bagClean;

        // Cek apakah ada perubahan data (isDirty/save)
        // save() mengembalikan true meskipun tidak ada kolom yang berubah
        if ($masterPart->save()) {
            return [
                'status'     => 200,
                'message'    => 'Data master part berhasil diubah',
                'redirect'   => '',
                'table'      => 'datatable-master-part',
                'additional' => [],
            ];
        }

        return [
            'status'     => 400,
            'message'    => 'Data master part gagal diubah',
            'redirect'   => '',
            'table'      => 'datatable-master-part',
            'additional' => [],
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Part\MasterPart  $masterPart
     * @return \Illuminate\Http\Response
     */
    public function destroy(MasterPart $masterPart, $id = 0)
    {
        $checkPartDetail = PartDetail::where("master_part_id", $id)->first();

        if (!$checkPartDetail) {
            $destroyMasterPart = MasterPart::find($id)->delete();

            if ($destroyMasterPart) {
                return array(
                    'status' => 200,
                    'message' => 'Master Part berhasil dihapus',
                    'redirect' => '',
                    'table' => 'datatable-master-part',
                    'additional' => [],
                );
            }
        } else {
            return array(
                'status' => 400,
                'message' => 'Master Part sudah digunakan',
                'redirect' => '',
                'table' => 'datatable-master-part',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Master Part gagal dihapus',
            'redirect' => '',
            'table' => 'datatable-master-part',
            'additional' => [],
        );
    }
}
