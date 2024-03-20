<?php

namespace App\Http\Controllers;

use App\Models\InMaterialFabric;
use App\Models\InMaterialFabricDet;
use App\Models\BppbDet;
use App\Models\BppbHeader;
use App\Models\BppbSB;
use Illuminate\Support\Facades\Auth;
use App\Models\MarkerDetail;
use App\Models\InMaterialLokasi;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportLokasiMaterial;
use App\Models\Journal;
use App\Models\BpbSB;
use DB;
use QrCode;
use DNS1D;
use PDF;

class KonfPengeluaranController extends Controller
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

            $data_inmaterial = DB::connection('mysql_sb')->select("select a.no_bppb,tgl_bppb,no_req,no_jo,buyer,tujuan,dok_bc,jenis_pengeluaran,no_invoice,no_daftar,tgl_daftar,CONCAT(a.created_by,' (',a.created_at, ') ') user_create,a.status,a.id, SUM(qty_out) qty, b.satuan from whs_bppb_h a inner join whs_bppb_det b on b.no_bppb = a.no_bppb where tgl_bppb BETWEEN '".$request->tgl_awal."' and '".$request->tgl_akhir."' and a.status = 'Pending' GROUP BY a.no_bppb order by no_bppb asc");


            return DataTables::of($data_inmaterial)->toJson();
        }

        $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('tipe_sup', '=', 'S')->get();
        $mtypebc = DB::connection('mysql_sb')->table('masterpilihan')->select('id', 'nama_pilihan')->where('kode_pilihan', '=', 'JENIS_DOK_IN')->get();
        $pch_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Purchasing_type')->where('status', '=', 'Active')->get();
        $status = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Status_material')->where('status', '=', 'Active')->get();
        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();

        return view("konfirmasi.konfirmasi-pengeluaran", ['status' => $status,'pch_type' => $pch_type,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit,"page" => "dashboard-warehouse"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getdatapengeluaran(Request $request)
    {

    $det_item = DB::connection('mysql_sb')->select("select no_bppb,kpno,styleno,a.id_jo,a.id_item,goods_code,itemdesc, sum(qty_out) qty,satuan from whs_bppb_det a 
inner join masteritem b on b.id_item = a.id_item 
left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) tmpjo on tmpjo.id_jo=a.id_jo where no_bppb = '" . $request->no_bppb . "' GROUP BY no_bppb,a.id_jo,a.id_item");

        $html = '<div class="table-responsive">
            <table id="tableshow" class="table table-head-fixed table-bordered table-striped table-sm w-100">
                <thead>
                    <tr>
                        <th class="text-center" style="font-size: 0.6rem;">No WS</th>
                        <th class="text-center" style="font-size: 0.6rem;">Style</th>
                        <th class="text-center" style="font-size: 0.6rem;">ID JO</th>
                        <th class="text-center" style="font-size: 0.6rem;">ID Item</th>
                        <th class="text-center" style="font-size: 0.6rem;">Nama Item</th>
                        <th class="text-center" style="font-size: 0.6rem;">Qty</th>
                        <th class="text-center" style="font-size: 0.6rem;">Satuan</th>
                    </tr>
                </thead>
                <tbody>';
            $jml_qty_sj = 0;
            $jml_qty_ak = 0;
            $x = 1;
        foreach ($det_item as $detitem) {
            $html .= ' <tr>
                        <td> '.$detitem->kpno.'</td>
                        <td> '.$detitem->styleno.'</td>
                        <td> '.$detitem->id_jo.'</td>
                        <td> '.$detitem->id_item.'</td>
                        <td> '.$detitem->itemdesc.'</td>
                        <td> '.$detitem->qty.'</td>
                        <td> '.$detitem->satuan.'</td>
                       </tr>';
                       $x++;
        }

        $html .= '</tbody>
            </table>
        </div>';

        return $html;
    }


    public function approvepengeluaranall(Request $request)
    {
            $timestamp = Carbon::now();

            for ($i = 0; $i < $request['jumlah_data']; $i++) {
            $check = isset($request['chek_id'][$i]) ? $request['chek_id'][$i] : 0;
            if ($check > 0) {
                // dd($request['id_bpb'][$i]);
                $updateBppbnew = BppbHeader::where('no_bppb', $request['id_bpb'][$i])->update([
                'status' => 'Approved',
                'approved_by' => Auth::user()->name,
                'approved_date' => $timestamp,
            ]);

            $updateBppbSB = BppbSB::where('bppbno_int', $request['id_bpb'][$i])->update([
                'confirm' => 'Y',
                'confirm_by' => Auth::user()->name,
                'confirm_date' => $timestamp,
            ]);

            $cekdata = DB::connection('mysql_sb')->select("select bppbno,bppbno_int,bppbdate,curr,id_supplier,supplier,mattype,n_code_category,matclass,curr,COALESCE(tax,0) tax,username,dateinput,(dpp + (dpp * (COALESCE(tax,0)/100))) total,dpp,(dpp * (COALESCE(tax,0)/100)) ppn from (select bppbno, bppbno_int, bppb.bppbdate, bppb.id_supplier, supplier, mattype, n_code_category, 
        if(matclass like '%ACCESORIES%','ACCESORIES',mi.matclass) matclass, bppb.curr,bppb.username, bppb.dateinput, 
        SUM(((qty) * price)) as dpp,bpbno_ro
        from bppb 
        inner join masteritem mi on bppb.id_item = mi.id_item
        inner join mastersupplier ms on bppb.id_supplier = ms.id_supplier
        where bppbno_int IN ('".$request['id_bpb'][$i]."') group by bppbno_int) a left join
        (select bpbno,pono from bpb GROUP BY bpbno) b on b.bpbno = a.bpbno_ro
        left JOIN
        (select pono,tax from po_header GROUP BY pono) c on c.pono = b.pono");


            $no_bpb         = $cekdata[0]->bppbno_int;
            $supp           = $cekdata[0]->supplier;
            $id_supplier    = $cekdata[0]->id_supplier;
            $mattype        = $cekdata[0]->mattype;
            $matclass1      = $cekdata[0]->matclass;
            $n_code_category = $cekdata[0]->n_code_category;
            $tax            = $cekdata[0]->tax;
            $curr           = $cekdata[0]->curr;
            $username       = $cekdata[0]->username;
            $curr           = $cekdata[0]->curr;
            $total          = $cekdata[0]->total;
            $dpp            = $cekdata[0]->dpp;
            $ppn            = $cekdata[0]->ppn;
            $tgl_bpb        = $cekdata[0]->bppbdate;
            $dateinput      = $cekdata[0]->dateinput;
            $matclass       = '';
            $rate           = 0;
            $idr_dpp        = 0;
            $idr_ppn        = 0;
            $idr_total      = 0;
            $cust_ctg       = '';
            $kata1          = '';

            if ($mattype == 'C') {
                if ($matclass1 == 'CMT' || $matclass1 == 'PRINTING' || $matclass1 == 'EMBRODEIRY' || $matclass1 == 'WASHING' || $matclass1 == 'PAINTING' || $matclass1 == 'HEATSEAL') {
                            $matclass = $matclass1;
                } else {
                            $matclass = 'OTHER';
                }
            } else {
                        $matclass = $matclass1;
            }

            if ($curr != 'IDR') {
                $sqlrate = DB::connection('mysql_sb')->select("select ROUND(rate,2) as rate , tanggal  FROM masterrate where tanggal = '".$tgl_bpb."' and v_codecurr = 'PAJAK'");

                $rate   = $sqlrate[0]->rate ? $sqlrate[0]->rate : 1;

            } else {
                $rate = 1;
            }

                $idr_dpp = $dpp * $rate;
                $idr_ppn = $ppn * $rate;
                $idr_total = $total * $rate;

            if ($id_supplier == '342' || $id_supplier == '20' || $id_supplier == '19' || $id_supplier == '692' || $id_supplier == '17' || $id_supplier == '18') {
                $cust_ctg = 'Related';
            } else {
                $cust_ctg = 'Third';
            }


            if ($mattype != 'N') {
                if ($matclass == 'FABRIC') {
                    $kata1 = "RETURN PEMBELIAN KAIN";
                } elseif ($matclass == 'ACCESORIES') {
                    $kata1 = "RETURN PEMBELIAN AKSESORIS";
                } elseif ($matclass == 'CMT') {
                    $kata1 = "RETURN BIAYA MAKLOON PAKAIAN JADI";
                } elseif ($matclass == 'PRINTING') {
                    $kata1 = "RETURN BIAYA MAKLOON PRINTING";
                } elseif ($matclass == 'EMBRODEIRY') {
                    $kata1 = "RETURN BIAYA MAKLOON EMBRODEIRY";
                } elseif ($matclass == 'WASHING') {
                    $kata1 = "RETURN BIAYA MAKLOON WASHING";
                } elseif ($matclass == 'PAINTING') {
                    $kata1 = "RETURN BIAYA MAKLOON PAINTING";
                } elseif ($matclass == 'HEATSEAL') {
                    $kata1 = "RETURN BIAYA MAKLOON HEATSEAL";
                } else {
                    $kata1 = "RETURN BIAYA MAKLOON LAINNYA";
                }
        } else {
                if ($n_code_category == '1') {
                    $kata1 = "RETURN PEMBELIAN PERSEDIAAN ATK";
                } elseif ($n_code_category == '2') {
                    $kata1 = "RETURN PEMBELIAN PERSEDIAAN UMUM";
                } elseif ($n_code_category == '3') {
                    $kata1 = "RETURN BIAYA PERSEDIAAN SPAREPARTS";
                } elseif ($n_code_category == '4') {
                    $kata1 = "RETURN BIAYA MESIN";
                } else {
                    $kata1 = "";
                }
        }   

            $kata2 = "DARI";

            $description = $kata1 . " " . $no_bpb . " " . $kata2 . " " . $supp;

            $sqlcoa = DB::connection('mysql_sb')->select("select no_coa, nama_coa from mastercoa_v2 where cus_ctg like '%".$cust_ctg."%' and mattype like '%".$mattype."%' and matclass like '%".$matclass."%' and n_code_category like '%".$n_code_category."%' and inv_type like '%bpb_credit%' Limit 1");

            $no_coa_cre   = $sqlcoa[0]->no_coa ? $sqlcoa[0]->no_coa : '-';
            $nama_coa_cre   = $sqlcoa[0]->nama_coa ? $sqlcoa[0]->nama_coa : '-';

            $jurnalcredit = Journal::create([
                'no_journal' => $no_bpb,
                'tgl_journal' => $tgl_bpb,
                'type_journal' => 'AP - BPB RETURN',
                'no_coa' => $no_coa_cre,
                'nama_coa' => $nama_coa_cre,
                'no_costcenter' => '-',
                'nama_costcenter' => '-',
                'reff_doc' => '-',
                'reff_date' => '',
                'buyer' => '-',
                'no_ws' => '-',
                'curr' => $curr,
                'rate' => $rate,
                'debit' => $total,
                'credit' => '0',
                'debit_idr' => $idr_total,
                'credit_idr' => '0',
                'status' => 'Approved',
                'keterangan' => $description,
                'create_by' => $username,
                'create_date' => $dateinput,
                'approve_by' => Auth::user()->name,
                'approve_date' => $timestamp,
                'cancel_by' => '',
                'cancel_date' => '',
            ]);

            $sqlcoa2 = DB::connection('mysql_sb')->select("select no_coa, nama_coa from mastercoa_v2 where cus_ctg like '%".$cust_ctg."%' and mattype like '%".$mattype."%' and matclass like '%".$matclass."%' and n_code_category like '%".$n_code_category."%' and inv_type like '%bpb_debit%' Limit 1");

            $no_coa_deb   = $sqlcoa2[0]->no_coa ? $sqlcoa2[0]->no_coa : '-';
            $nama_coa_deb   = $sqlcoa2[0]->nama_coa ? $sqlcoa2[0]->nama_coa : '-';

            $jurnaldebit = Journal::create([
                'no_journal' => $no_bpb,
                'tgl_journal' => $tgl_bpb,
                'type_journal' => 'AP - BPB RETURN',
                'no_coa' => $no_coa_deb,
                'nama_coa' => $nama_coa_deb,
                'no_costcenter' => '-',
                'nama_costcenter' => '-',
                'reff_doc' => '-',
                'reff_date' => '',
                'buyer' => '-',
                'no_ws' => '-',
                'curr' => $curr,
                'rate' => $rate,
                'debit' => '0',
                'credit' => $dpp,
                'debit_idr' => '0',
                'credit_idr' => $idr_dpp,
                'status' => 'Approved',
                'keterangan' => $description,
                'create_by' => $username,
                'create_date' => $dateinput,
                'approve_by' => Auth::user()->name,
                'approve_date' => $timestamp,
                'cancel_by' => '',
                'cancel_date' => '',
            ]);

            if ($tax >= 1) {

                $sqlcoa2 = DB::connection('mysql_sb')->select("select no_coa, nama_coa from mastercoa_v2 where inv_type like '%PPN MASUKAN%' Limit 1");

                $no_coa_ppn   = $sqlcoa2[0]->no_coa ? $sqlcoa2[0]->no_coa : '-';
                $nama_coa_ppn   = $sqlcoa2[0]->nama_coa ? $sqlcoa2[0]->nama_coa : '-';

                $jurnalppn = Journal::create([
                    'no_journal' => $no_bpb,
                    'tgl_journal' => $tgl_bpb,
                    'type_journal' => 'AP - BPB RETURN',
                    'no_coa' => $no_coa_ppn,
                    'nama_coa' => $nama_coa_ppn,
                    'no_costcenter' => '-',
                    'nama_costcenter' => '-',
                    'reff_doc' => '-',
                    'reff_date' => '',
                    'buyer' => '-',
                    'no_ws' => '-',
                    'curr' => $curr,
                    'rate' => $rate,
                    'debit' => '0',
                    'credit' => $ppn,
                    'debit_idr' => '0',
                    'credit_idr' => $idr_ppn,
                    'status' => 'Approved',
                    'keterangan' => $description,
                    'create_by' => $username,
                    'create_date' => $dateinput,
                    'approve_by' => Auth::user()->name,
                    'approve_date' => $timestamp,
                    'cancel_by' => '',
                    'cancel_date' => '',
                ]);
            }


            }
            }
        
        $massage = 'Approved Data Successfully';

            return array(
                "status" => 200,
                "message" => $massage,
                "additional" => [],
                // "redirect" => url('/konfirmasi-pemasukan')
            );
        
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
 

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */
    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Stocker  $stocker
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
     * @param  \App\Models\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */
    public function destroy(Stocker $stocker)
    {
        //
    }

  

    
}
