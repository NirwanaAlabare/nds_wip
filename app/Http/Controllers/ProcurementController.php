<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\ExportDetailReturSB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\BppbSB;
use Carbon\Carbon;
use DB;
use QrCode;
use PDF;

class ProcurementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        return view("procurement.homepage", ["page" => "procurement"]);
    }

    public function detailreturnsb(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = "";

            if ($request->dateFrom) {
                $additionalQuery .= " and a.tgl_filter >= '" . $request->dateFrom . "' ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and a.tgl_filter <= '" . $request->dateTo . "' ";
            }

            if ($request->itemSO != '') {
                $data_opname = DB::connection('mysql_sb')->select("select kode,bppbno_int, bppbdate, status_return, stylenya, wsno, supplier, invno,jenis_dok, nomor_aju, IF(nomor_aju = '' OR nomor_aju is null,'-',tanggal_aju) tanggal_aju, CASE
                    WHEN a.confirm = 'Y' THEN 'Confirm'
                    WHEN a.cancel = 'Y' THEN 'Cancel'
                    ELSE '-'
                    END AS status, username created_by from (SELECT mid(bppbno,4,1) kode,jo_no,ac.kpno wsno,ac.styleno stylenya,a.*,s.goods_code,s.itemdesc itemdesc,supplier FROM bppb a inner join masteritem s on a.id_item=s.id_item inner join mastersupplier ms on a.id_supplier=ms.id_supplier left join jo_det jod on a.id_jo=jod.id_jo left join jo on jod.id_jo=jo.id left join so on jod.id_so=so.id left join act_costing ac on so.id_cost=ac.id where mid(bppbno,4,1) in ('A','F','B','N') and mid(bppbno,4,2)!='FG' and right(bppbno,1)='R' and a.bppbdate BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' GROUP BY a.bppbno ASC order by bppbdate desc) a where kode = '" . $request->itemSO . "' order by bppbdate desc");
            }else{
                $data_opname = DB::connection('mysql_sb')->select("select kode,bppbno_int, bppbdate, status_return, stylenya, wsno, supplier, invno,jenis_dok, nomor_aju, IF(nomor_aju = '' OR nomor_aju is null,'-',tanggal_aju) tanggal_aju, CASE
                    WHEN a.confirm = 'Y' THEN 'Confirm'
                    WHEN a.cancel = 'Y' THEN 'Cancel'
                    ELSE '-'
                    END AS status, username created_by from (SELECT mid(bppbno,4,1) kode,jo_no,ac.kpno wsno,ac.styleno stylenya,a.*,s.goods_code,s.itemdesc itemdesc,supplier FROM bppb a inner join masteritem s on a.id_item=s.id_item inner join mastersupplier ms on a.id_supplier=ms.id_supplier left join jo_det jod on a.id_jo=jod.id_jo left join jo on jod.id_jo=jo.id left join so on jod.id_so=so.id left join act_costing ac on so.id_cost=ac.id where mid(bppbno,4,1) in ('A','F','B','N') and mid(bppbno,4,2)!='FG' and right(bppbno,1)='R' and a.bppbdate BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' GROUP BY a.bppbno ASC order by bppbdate desc) a order by bppbdate desc");

            }


            return DataTables::of($data_opname)->toJson();
        }
        $item_so = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('kode_pilihan', 'nama_pilihan')->where('type_pilihan', '=', 'whs_return_sb')->where('status', '=', 'Active')->get();
        $arealok = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'status_replacement')->where('status', '=', 'Active')->get();

        return view("procurement.detail-return-sb", ['arealok' => $arealok, 'item_so' => $item_so, "page" => "stock_opname"]);
    }

    
    public function export_excel_detailreturn_sb(Request $request)
    {
        return Excel::download(new ExportDetailReturSB($request->itemso, $request->from, $request->to), 'list_detail_return.xlsx');
    }

    public function simpaneditreturnsb(Request $request)
    {
        // $markerCount = Marker::selectRaw("MAX(kode) latest_kode")->whereRaw("kode LIKE 'MRK/" . date('ym') . "/%'")->first();
        // $markerNumber = intval(substr($markerCount->latest_kode, -5)) + 1;
        // $markerCode = 'MRK/' . date('ym') . '/' . sprintf('%05s', $markerNumber);
        // $totalQty = 0;

        $validatedRequest = $request->validate([
            "txt_id" => "required",
            "txt_area" => "required",
        ]);


            $updatebppb = BppbSB::where('bppbno_int', $validatedRequest['txt_id'])->update([
                'status_return' => $validatedRequest['txt_area'],
            ]);

            $massage = 'Edit Succesfully';

            return array(
                "status" => 200,
                "message" => $massage,
                "additional" => [],
                "redirect" => url('/procurement/detail-return-sb')
            );

        return array(
                "status" => 400,
                "message" => "Error Edit",
                "additional" => [],
            );
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

}
