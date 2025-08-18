<?php

namespace App\Http\Controllers;

use App\Models\InMaterialFabric;
use App\Models\InMaterialFabricDet;
use App\Models\Bpb;
use App\Models\Tempbpb;
use App\Models\InMaterialLokTemp;
use Illuminate\Support\Facades\Auth;
use App\Models\Marker\MarkerDetail;
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

class KonfPemasukanController extends Controller
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

            $data_inmaterial = DB::connection('mysql_sb')->select("select a.*,COALESCE(qty_lok,0) qty_lok,round((round(COALESCE(qty,0),4) - round(COALESCE(qty_lok,0),4)),2) qty_balance from (select b.id,b.no_dok,b.tgl_dok,b.tgl_shipp,b.type_dok,b.no_po,b.supplier,b.no_invoice,b.type_bc,b.no_daftar,b.tgl_daftar, b.type_pch,CONCAT(b.created_by,' (',b.created_at, ') ') user_create,b.status,round(sum(COALESCE(qty_good,0)),2) qty, unit from whs_inmaterial_fabric_det a inner join whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and b.tgl_dok BETWEEN '".$request->tgl_awal."' and '".$request->tgl_akhir."' GROUP BY b.id) a left JOIN
            (select no_dok nodok,round(SUM(qty_sj),2) qty_lok from whs_lokasi_inmaterial where status = 'Y' GROUP BY no_dok) b on b.nodok = a.no_dok where a.tgl_dok BETWEEN '".$request->tgl_awal."' and '".$request->tgl_akhir."' and status = 'Pending' order by no_dok asc");


            return DataTables::of($data_inmaterial)->toJson();
        }

        $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('tipe_sup', '=', 'S')->get();
        $mtypebc = DB::connection('mysql_sb')->table('masterpilihan')->select('id', 'nama_pilihan')->where('kode_pilihan', '=', 'JENIS_DOK_IN')->get();
        $pch_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Purchasing_type')->where('status', '=', 'Active')->get();
        $status = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Status_material')->where('status', '=', 'Active')->get();
        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();

        return view("konfirmasi.konfirmasi-penerimaan", ['status' => $status,'pch_type' => $pch_type,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit,"page" => "dashboard-warehouse"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

     public function getdatapenerimaan(Request $request)
    {

    $det_item = DB::connection('mysql_sb')->select("select no_dok,kpno,styleno,a.id_jo,a.id_item,goods_code,itemdesc, sum(qty_sj) qty,satuan from whs_lokasi_inmaterial a
inner join masteritem b on b.id_item = a.id_item
left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) tmpjo on tmpjo.id_jo=a.id_jo where no_dok = '" . $request->no_bpb . "' GROUP BY no_dok,a.id_jo,a.id_item");

        $html = '<div class="table-responsive">
            <table id="tableshow" class="table table-head-fixed table-bordered table-striped w-100">
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


    public function approvematerialall(Request $request)
    {
            $timestamp = Carbon::now();

            for ($i = 0; $i < $request['jumlah_data']; $i++) {
            $check = isset($request['chek_id'][$i]) ? $request['chek_id'][$i] : 0;
            if ($check > 0) {
                // dd(strpos($request['id_bpb'][$i]);
                // dd($request['id_bpb'][$i]);
                $update_whsbpb = InMaterialFabric::where('no_dok', $request['id_bpb'][$i])->update([
                'status' => 'Approved',
                'approved_by' => Auth::user()->name,
                'approved_date' => $timestamp,
                ]);

                $update_bpb = BpbSB::where('bpbno_int', $request['id_bpb'][$i])->update([
                'confirm' => 'Y',
                'confirm_by' => Auth::user()->name,
                'confirm_date' => $timestamp,
                ]);

                $sqlbpb = DB::connection('mysql_sb')->select("select bpbno_int from bpb where bpbno_int = '".$request['id_bpb'][$i]."' and bpbno_int like '%RI%' limit 1");

                $bpbnya   = $sqlbpb ? $sqlbpb[0]->bpbno_int : '-';

                if($bpbnya != '-') {

                    $sqlupdate = DB::connection('mysql_sb')->select("update bpb set qty = qty_temp where bpbno_int ='".$request['id_bpb'][$i]."' ");
                }

            $cekdata = DB::connection('mysql_sb')->select("select SUBSTR(bpbno_int,1,3) fil_wip, phd.tipe_com, mi.itemdesc,bpb.confirm,bpbno, bpbno_int, bpb.bpbdate, bpb.id_supplier, supplier, mattype, n_code_category,
			if(matclass like '%ACCESORIES%','ACCESORIES',mi.matclass) matclass, bpb.curr, COALESCE(ph.tax,0) tax,bpb.username, bpb.dateinput,
			round(SUM(((qty - COALESCE(qty_reject,0)) * price) + (((qty - COALESCE(qty_reject,0)) * price) * (COALESCE(ph.tax,0) /100))),2) as total,round(SUM(((qty - COALESCE(qty_reject,0)) * price)),2) as dpp,round(SUM((((qty - COALESCE(qty_reject,0)) * price) * (COALESCE(ph.tax,0) /100))),2) as ppn
			from bpb
			inner join masteritem mi on bpb.id_item = mi.id_item
			inner join mastersupplier ms on bpb.id_supplier = ms.id_supplier
			left join po_header ph on bpb.pono = ph.pono
			left join po_header_draft phd on phd.id = ph.id_draft
			where bpbno_int = '".$request['id_bpb'][$i]."'  group by bpbno,mattype, n_code_category order by supplier");

            $no_bpb         = $cekdata ? $cekdata[0]->bpbno_int : '-';
            $supp           = $cekdata ? $cekdata[0]->supplier : '-';
            $id_supplier    = $cekdata ? $cekdata[0]->id_supplier : '-';
            $mattype        = $cekdata ? $cekdata[0]->mattype : '-';
            $matclass1      = $cekdata ? $cekdata[0]->matclass : '-';
            $n_code_category = $cekdata ? $cekdata[0]->n_code_category : '-';
            $tax            = $cekdata ? $cekdata[0]->tax : '-';
            $curr           = $cekdata ? $cekdata[0]->curr : '-';
            $username       = $cekdata ? $cekdata[0]->username : '-';
            $curr           = $cekdata ? $cekdata[0]->curr : '-';
            $total          = $cekdata ? $cekdata[0]->total : 0;
            $dpp            = $cekdata ? $cekdata[0]->dpp : 0;
            $ppn            = $cekdata ? $cekdata[0]->ppn : 0;
            $tgl_bpb        = $cekdata ? $cekdata[0]->bpbdate : '-';
            $dateinput_     = $cekdata ? $cekdata[0]->dateinput : '-';
            $tipe_com       = $cekdata ? $cekdata[0]->tipe_com : '-';
            $matclass       = '';
            $rate           = 0;
            $idr_dpp        = 0;
            $idr_ppn        = 0;
            $idr_total      = 0;
            $cust_ctg       = '';
            $kata1       = '';

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

                $rate   = $sqlrate ? $sqlrate[0]->rate : 1;

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
                    $kata1 = "PEMBELIAN KAIN";
                } elseif ($matclass == 'ACCESORIES') {
                    $kata1 = "PEMBELIAN AKSESORIS";
                } elseif ($matclass == 'CMT') {
                    $kata1 = "BIAYA MAKLOON PAKAIAN JADI";
                } elseif ($matclass == 'PRINTING') {
                    $kata1 = "BIAYA MAKLOON PRINTING";
                } elseif ($matclass == 'EMBRODEIRY') {
                    $kata1 = "BIAYA MAKLOON EMBRODEIRY";
                } elseif ($matclass == 'WASHING') {
                    $kata1 = "BIAYA MAKLOON WASHING";
                } elseif ($matclass == 'PAINTING') {
                    $kata1 = "BIAYA MAKLOON PAINTING";
                } elseif ($matclass == 'HEATSEAL') {
                    $kata1 = "BIAYA MAKLOON HEATSEAL";
                } else {
                    $kata1 = "BIAYA MAKLOON LAINNYA";
                }
            } else {
                if ($n_code_category == '1') {
                    $kata1 = "PEMBELIAN PERSEDIAAN ATK";
                } elseif ($n_code_category == '2') {
                    $kata1 = "PEMBELIAN PERSEDIAAN UMUM";
                } elseif ($n_code_category == '3') {
                    $kata1 = "BIAYA PERSEDIAAN SPAREPARTS";
                } elseif ($n_code_category == '4') {
                    $kata1 = "BIAYA MESIN";
                } else {
                    $kata1 = "";
                }
            }

            $kata2 = "DARI";

            $description = $kata1 . " " . $no_bpb . " " . $kata2 . " " . $supp;

            $sqlcoa = DB::connection('mysql_sb')->select("select no_coa, nama_coa from mastercoa_v2 where cus_ctg like '%".$cust_ctg."%' and mattype like '%".$mattype."%' and matclass like '%".$matclass."%' and n_code_category like '%".$n_code_category."%' and inv_type like '%bpb_credit%' Limit 1");

            $no_coa_cre   = $sqlcoa ? $sqlcoa[0]->no_coa : '-';
            $nama_coa_cre   = $sqlcoa ? $sqlcoa[0]->nama_coa : '-';

            $jurnalcredit = Journal::create([
                'no_journal' => $no_bpb,
                'tgl_journal' => $tgl_bpb,
                'type_journal' => 'AP - BPB',
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
                'debit' => '0',
                'credit' => $total,
                'debit_idr' => '0',
                'credit_idr' => $idr_total,
                'status' => 'Approved',
                'keterangan' => $description,
                'create_by' => $username,
                'create_date' => $dateinput_,
                'approve_by' => Auth::user()->name,
                'approve_date' => $timestamp,
                'cancel_by' => '',
                'cancel_date' => '',
            ]);

            $sqlcoa2 = DB::connection('mysql_sb')->select("select no_coa, nama_coa from mastercoa_v2 where cus_ctg like '%".$cust_ctg."%' and mattype like '%".$mattype."%' and matclass like '%".$matclass."%' and n_code_category like '%".$n_code_category."%' and inv_type like '%bpb_debit%' Limit 1");

            $no_coa_deb   = $sqlcoa2 ? $sqlcoa2[0]->no_coa : '-';
            $nama_coa_deb   = $sqlcoa2 ? $sqlcoa2[0]->nama_coa : '-';

            $jurnaldebit = Journal::create([
                'no_journal' => $no_bpb,
                'tgl_journal' => $tgl_bpb,
                'type_journal' => 'AP - BPB',
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
                'debit' => $dpp,
                'credit' => '0',
                'debit_idr' => $idr_dpp,
                'credit_idr' => '0',
                'status' => 'Approved',
                'keterangan' => $description,
                'create_by' => $username,
                'create_date' => $dateinput_,
                'approve_by' => Auth::user()->name,
                'approve_date' => $timestamp,
                'cancel_by' => '',
                'cancel_date' => '',
            ]);

            if ($tax >= 1) {

                $sqlcoa2 = DB::connection('mysql_sb')->select("select no_coa, nama_coa from mastercoa_v2 where inv_type like '%PPN MASUKAN%' Limit 1");

                $no_coa_ppn   = $sqlcoa2 ? $sqlcoa2[0]->no_coa : '-';
                $nama_coa_ppn   = $sqlcoa2 ? $sqlcoa2[0]->nama_coa : '-';

                $jurnalppn = Journal::create([
                    'no_journal' => $no_bpb,
                    'tgl_journal' => $tgl_bpb,
                    'type_journal' => 'AP - BPB',
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
                    'debit' => $ppn,
                    'credit' => '0',
                    'debit_idr' => $idr_ppn,
                    'credit_idr' => '0',
                    'status' => 'Approved',
                    'keterangan' => $description,
                    'create_by' => $username,
                    'create_date' => $dateinput_,
                    'approve_by' => Auth::user()->name,
                    'approve_date' => $timestamp,
                    'cancel_by' => '',
                    'cancel_date' => '',
                ]);
            }

            if($tipe_com == 'Buyer'){
                $sqlcoa = DB::connection('mysql_sb')->select("select no_coa, nama_coa from mastercoa_v2 where cus_ctg like '%".$cust_ctg."%' and mattype like '%".$mattype."%' and matclass like '%".$matclass."%' and n_code_category like '%".$n_code_category."%' and inv_type like '%bpb_debit%' Limit 1");

                $no_coa_cre   = $sqlcoa ? $sqlcoa[0]->no_coa : '-';
                $nama_coa_cre   = $sqlcoa ? $sqlcoa[0]->nama_coa : '-';

                $jurnalcredit = Journal::create([
                    'no_journal' => $no_bpb,
                    'tgl_journal' => $tgl_bpb,
                    'type_journal' => 'AP - BPB',
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
                    'debit' => '0',
                    'credit' => $total,
                    'debit_idr' => '0',
                    'credit_idr' => $idr_total,
                    'status' => 'Approved',
                    'keterangan' => $description,
                    'create_by' => $username,
                    'create_date' => $dateinput_,
                    'approve_by' => Auth::user()->name,
                    'approve_date' => $timestamp,
                    'cancel_by' => '',
                    'cancel_date' => '',
                ]);


                $jurnaldebit = Journal::create([
                    'no_journal' => $no_bpb,
                    'tgl_journal' => $tgl_bpb,
                    'type_journal' => 'AP - BPB',
                    'no_coa' => '1.34.05',
                    'nama_coa' => 'PIUTANG LAIN-LAIN PIHAK KETIGA - BAHAN BAKU / BAHAN PEMBANTU',
                    'no_costcenter' => '-',
                    'nama_costcenter' => '-',
                    'reff_doc' => '-',
                    'reff_date' => '',
                    'buyer' => '-',
                    'no_ws' => '-',
                    'curr' => $curr,
                    'rate' => $rate,
                    'debit' => $dpp,
                    'credit' => '0',
                    'debit_idr' => $idr_dpp,
                    'credit_idr' => '0',
                    'status' => 'Approved',
                    'keterangan' => $description,
                    'create_by' => $username,
                    'create_date' => $dateinput_,
                    'approve_by' => Auth::user()->name,
                    'approve_date' => $timestamp,
                    'cancel_by' => '',
                    'cancel_date' => '',
                ]);

                if ($tax >= 1) {

                    $sqlcoa2 = DB::connection('mysql_sb')->select("select no_coa, nama_coa from mastercoa_v2 where inv_type like '%PPN MASUKAN%' Limit 1");

                    $no_coa_ppn   = $sqlcoa2 ? $sqlcoa2[0]->no_coa : '-';
                    $nama_coa_ppn   = $sqlcoa2 ? $sqlcoa2[0]->nama_coa : '-';

                    $jurnalppn = Journal::create([
                        'no_journal' => $no_bpb,
                        'tgl_journal' => $tgl_bpb,
                        'type_journal' => 'AP - BPB',
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
                        'debit' => $ppn,
                        'credit' => '0',
                        'debit_idr' => $idr_ppn,
                        'credit_idr' => '0',
                        'status' => 'Approved',
                        'keterangan' => $description,
                        'create_by' => $username,
                        'create_date' => $dateinput_,
                        'approve_by' => Auth::user()->name,
                        'approve_date' => $timestamp,
                        'cancel_by' => '',
                        'cancel_date' => '',
                    ]);
                }

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
