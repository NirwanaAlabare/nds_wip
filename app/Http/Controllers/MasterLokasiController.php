<?php

namespace App\Http\Controllers;

use App\Models\Stocker\Stocker;
use App\Models\Stocker\StockerDetail;
use App\Models\Cutting\FormCutInput;
use App\Models\Cutting\FormCutInputDetail;
use App\Models\Cutting\FormCutInputDetailLap;
use App\Models\Marker\Marker;
use App\Models\MasterLokasi;
use App\Models\UnitLokasi;
use Illuminate\Support\Facades\Auth;
use App\Models\Marker\MarkerDetail;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use QrCode;
use DNS1D;
use PDF;

class MasterLokasiController extends Controller
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
            $keywordQuery = "";
            if ($request->area != 'ALL') {
                $additionalQuery .= " where area_lok = '" . $request->area . "' ";
            if ($request->search["value"]) {
                $keywordQuery = "
                    and (
                        a.kode_lok like '%" . $request->search["value"] . "%' OR
                        a.area_lok like '%" . $request->search["value"] . "%' OR
                        a.inisial_lok like '%" . $request->search["value"] . "%' OR
                        a.baris_lok like '%" . $request->search["value"] . "%' OR
                        a.level_lok like '%" . $request->search["value"] . "%' OR
                        a.no_lok like '%" . $request->search["value"] . "%' OR
                        f.unit like '%" . $request->search["value"] . "%' OR
                        a.kapasitas like '%" . $request->search["value"] . "%' OR
                        a.status like '%" . $request->search["value"] . "%'
                    )
                ";
            }
            }else{
                $additionalQuery = " ";
                if ($request->search["value"]) {
                    $keywordQuery = "
                    where (
                            a.kode_lok like '%" . $request->search["value"] . "%' OR
                            a.area_lok like '%" . $request->search["value"] . "%' OR
                            a.inisial_lok like '%" . $request->search["value"] . "%' OR
                            a.baris_lok like '%" . $request->search["value"] . "%' OR
                            a.level_lok like '%" . $request->search["value"] . "%' OR
                            a.no_lok like '%" . $request->search["value"] . "%' OR
                            f.unit like '%" . $request->search["value"] . "%' OR
                            a.kapasitas like '%" . $request->search["value"] . "%' OR
                            a.status like '%" . $request->search["value"] . "%'
                        )
                    ";
                }
            }


            $data_m_lokasi = DB::connection('mysql_sb')->select("
            select a.*,b.unit unit_roll,c.unit unit_bundle,d.unit unit_box,e.unit unit_pack,f.unit from (select  id,kode_lok,area_lok,inisial_lok,baris_lok,level_lok,no_lok,kapasitas,CONCAT(create_by, ' (',create_date,')') create_user,concat (IF(status = 'Active','Y','N'),'-' ,kode_lok,'-',id) kode_id, status, subbaris_lok, sublevel_lok from whs_master_lokasi) a left join
                (select kode_lok, unit from whs_unit_lokasi where unit = 'ROLL' and status = 'Y' GROUP BY kode_lok) b on b.kode_lok = a.kode_lok left join
                (select kode_lok, unit from whs_unit_lokasi where unit = 'BUNDLE' and status = 'Y' GROUP BY kode_lok) c on c.kode_lok = a.kode_lok left join
                (select kode_lok, unit from whs_unit_lokasi where unit = 'BOX' and status = 'Y' GROUP BY kode_lok) d on d.kode_lok = a.kode_lok left join
                (select kode_lok, unit from whs_unit_lokasi where unit = 'PACK' and status = 'Y' GROUP BY kode_lok) e on e.kode_lok = a.kode_lok left join
                (select kode_lok, GROUP_CONCAT(unit) unit from whs_unit_lokasi where status = 'Y' GROUP BY kode_lok) f on f.kode_lok = a.kode_lok
                " . $additionalQuery . "
                " . $keywordQuery . "
            ");


             return DataTables::of($data_m_lokasi)->toJson();
        }

        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();

        return view("master.master-lokasi", ['arealok' => $arealok,'unit' => $unit,"page" => "dashboard-warehouse"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();

        return view('master.create-lokasi', ['arealok' => $arealok,'unit' => $unit, 'page' => 'dashboard-warehouse']);
    }


    public function updatestatus(Request $request)
    {

        $id = $request['id_lok'];
        $status = $request['status_lok'];
        if ($status == 'Active') {
            $updateLokasi = MasterLokasi::where('id', $request['id_lok'])->update([
                'status' => 'Deactive'
            ]);
        }else{
            $updateLokasi = MasterLokasi::where('id', $request['id_lok'])->update([
                'status' => 'Active'
            ]);
        }

        $massage = 'Change Status Successfully';

            return array(
                "status" => 200,
                "message" => $massage,
                "additional" => [],
                "redirect" => url('/master-lokasi')
            );

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
            "kode_lok" => "required",
            "txt_area_new" => "required",
            "txt_inisial_new" => "required",
            "txt_capacity_new" => "required",
        ]);

        $lokCode = $validatedRequest['kode_lok'];

        $datanomor = DB::connection('mysql_sb')->select("
        select kode_lok from whs_master_lokasi where kode_lok = '".$lokCode."'");
        $nomor_lokasi = $datanomor ? $datanomor[0] : null;
      if($nomor_lokasi == null){

        // if ($request['ROLL'] == 'on') {
        //      $unitStore1 = UnitLokasi::create([
        //         'kode_lok' => $lokCode,
        //         'unit' => 'ROLL',
        //         'status' => 'Y',
        //     ]);

        // }
        // if ($request['BUNDLE'] == 'on') {
        //      $unitStore2 = UnitLokasi::create([
        //         'kode_lok' => $lokCode,
        //         'unit' => 'BUNDLE',
        //         'status' => 'Y',
        //     ]);

        // }
        // if ($request['BOX'] == 'on') {
        //      $unitStore3 = UnitLokasi::create([
        //         'kode_lok' => $lokCode,
        //         'unit' => 'BOX',
        //         'status' => 'Y',
        //     ]);

        // }
        // if ($request['PACK'] == 'on') {
        //      $unitStore4 = UnitLokasi::create([
        //         'kode_lok' => $lokCode,
        //         'unit' => 'PACK',
        //         'status' => 'Y',
        //     ]);

        // }
        $timestamp = Carbon::now();

            $lokasiStore = MasterLokasi::create([
                'type_lok' => 'FABRIC WAREHOUSE RACK',
                'kode_lok' => $lokCode,
                'area_lok' => $validatedRequest['txt_area_new'],
                'inisial_lok' => $validatedRequest['txt_inisial_new'],
                'baris_lok' => $request['txt_baris_new'],
                'level_lok' => $request['txt_level_new'],
                'subbaris_lok' => $request['txt_subbaris_new'],
                'sublevel_lok' => $request['txt_sublevel_new'],
                'unit' => '-',
                'kapasitas' => $validatedRequest['txt_capacity_new'],
                'status' => 'Active',
                'create_by' => Auth::user()->name,
                'create_date' => $timestamp,
            ]);

            $massage = 'Location ' . $lokCode . ' Saved Succesfully';

            return array(
                "status" => 200,
                "message" => $massage,
                "additional" => [],
                "redirect" => url('/master-lokasi')
            );
    }else{
        return array(
                "status" => 400,
                "message" => "Data Duplicate",
                "additional" => [],
            );
    }

    }

    public function simpanedit(Request $request)
    {

        $validatedRequest = $request->validate([
            "kode_lok_edit" => "required",
            "txt_id" => "required",
            "txt_area" => "required",
            "txt_inisial" => "required",
            "txt_capacity" => "required",
        ]);

        $lokCode = $validatedRequest['kode_lok_edit'];

      if($lokCode != null){

        // $delete_unit = UnitLokasi::where('kode_lok', $lokCode)
        //       ->delete();

        // if ($request['ROLL_edit'] == 'on') {
        //      $unitStore1 = UnitLokasi::create([
        //         'kode_lok' => $lokCode,
        //         'unit' => 'ROLL',
        //         'status' => 'Y',
        //     ]);

        // }
        // if ($request['BUNDLE_edit'] == 'on') {
        //      $unitStore2 = UnitLokasi::create([
        //         'kode_lok' => $lokCode,
        //         'unit' => 'BUNDLE',
        //         'status' => 'Y',
        //     ]);

        // }
        // if ($request['BOX_edit'] == 'on') {
        //      $unitStore3 = UnitLokasi::create([
        //         'kode_lok' => $lokCode,
        //         'unit' => 'BOX',
        //         'status' => 'Y',
        //     ]);

        // }
        // if ($request['PACK_edit'] == 'on') {
        //      $unitStore4 = UnitLokasi::create([
        //         'kode_lok' => $lokCode,
        //         'unit' => 'PACK',
        //         'status' => 'Y',
        //     ]);

        // }

        $timestamp = Carbon::now();

            $updateLokasi = MasterLokasi::where('id', $validatedRequest['txt_id'])->update([
                'kode_lok' => $lokCode,
                'area_lok' => $validatedRequest['txt_area'],
                'inisial_lok' => $validatedRequest['txt_inisial'],
                'baris_lok' => $request['txt_baris'],
                'level_lok' => $request['txt_level'],
                'subbaris_lok' => $request['subtxt_baris'],
                'sublevel_lok' => $request['subtxt_level'],
                'kapasitas' => $validatedRequest['txt_capacity'],
                'status' => 'Active',
                'create_by' => Auth::user()->name,
                'create_date' => $timestamp,

            ]);

            $massage = 'Location ' . $lokCode . ' Edit Succesfully';

            return array(
                "status" => 200,
                "message" => $massage,
                "additional" => [],
                "redirect" => url('/master-lokasi')
            );
        }else{
        return array(
                "status" => 400,
                "message" => "Data Duplicate",
                "additional" => [],
            );
    }

    }


    public function printlokasi(Request $request, $id)
    {
        $dataLokasi = MasterLokasi::selectRaw("
                CONCAT(inisial_lok,baris_lok,level_lok,no_lok) kode_lok,
                kode_lok kode,
                id
            ")->
            where("id", $id)->
            first();

        // PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('master.pdf.print-lokasi', ["dataLokasi" => $dataLokasi])->setPaper('a7', 'landscape');

        // $pdf = PDF::loadView('master.pdf.print-lokasi', ["dataLokasi" => $dataLokasi]);

        $fileName = 'Lokasi-'.$dataLokasi->kode_lok.'.pdf';

        return $pdf->stream(str_replace("/", "_", $fileName));
    }


    public function printLokasiAll()
{
    $allLokasi = MasterLokasi::selectRaw("
        CONCAT(inisial_lok,baris_lok,level_lok,no_lok) kode_lok,
        kode_lok kode,
        id
    ")->get();

//     $allLokasi = MasterLokasi::selectRaw("
//     CONCAT(inisial_lok, baris_lok, level_lok, no_lok) AS kode_lok,
//     kode_lok AS kode,
//     id
// ")
// ->orderBy('id', 'asc')
// ->limit(10)
// ->get();

    $pdf = PDF::loadView('master.pdf.print-lokasi-all', [
        "listLokasi" => $allLokasi
    ])->setPaper('a7', 'landscape');

    $fileName = 'Lokasi-All.pdf';
    return $pdf->stream($fileName);
}


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Stocker\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Stocker\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */
    public function edit(Stocker $stocker)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Stocker\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $dataLokasi = DB::connection('mysql_sb')->select("
        select  id,
                kode_lok,
                area_lok,
                inisial_lok,
                baris_lok,
                level_lok,
                no_lok,
                unit,
                kapasitas,
                CONCAT(create_by, ' ',create_date) create_user,
                status from whs_master_lokasi where id = '$id'");
        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();

        return view('master.update-lokasi', ["dataLokasi" => $dataLokasi,'arealok' => $arealok,'unit' => $unit, 'page' => 'dashboard-warehouse']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Stocker\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */
    public function destroy(Stocker $stocker)
    {
        //
    }




}
