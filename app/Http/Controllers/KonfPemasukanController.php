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

        foreach ($request->id_bpb as $i => $id_bpb) {

            $check = $request->chek_id[$i] ?? 0;
            if ($check <= 0) continue;

        // Update status di InMaterialFabric
            InMaterialFabric::where('no_dok', $id_bpb)->update([
                'status' => 'Approved',
                'approved_by' => Auth::user()->name,
                'approved_date' => $timestamp,
            ]);

        // Update status di BpbSB
            BpbSB::where('bpbno_int', $id_bpb)->update([
                'confirm' => 'Y',
                'confirm_by' => Auth::user()->name,
                'confirm_date' => $timestamp,
            ]);

        // Update qty di DB mysql_sb jika bpbno_int ada
            $sqlbpb = DB::connection('mysql_sb')->select(
                "select bpbno_int from bpb where bpbno_int = ? and bpbno_int like '%RI%' limit 1",
                [$id_bpb]
            );

            $bpbnya = $sqlbpb ? $sqlbpb[0]->bpbno_int : '-';
            if ($bpbnya != '-') {
                DB::connection('mysql_sb')->update(
                    "update bpb set qty = qty_temp where bpbno_int = ?",
                    [$id_bpb]
                );
            }

        // Ambil data BPB
            $cekdata = DB::connection('mysql_sb')->select("
                select 
                SUBSTR(bpbno_int,1,3) fil_wip, phd.tipe_com, mi.itemdesc, bpb.confirm, bpbno, bpbno_int, bpb.bpbdate, 
                bpb.id_supplier, supplier, mattype, n_code_category,
                if(matclass like '%ACCESORIES%','ACCESORIES',mi.matclass) matclass,
                bpb.curr, COALESCE(ph.tax,0) tax, bpb.username, bpb.dateinput,
                round(SUM(((qty - COALESCE(qty_reject,0)) * price) + (((qty - COALESCE(qty_reject,0)) * price) * (COALESCE(ph.tax,0) /100))),2) as total,
                round(SUM(((qty - COALESCE(qty_reject,0)) * price)),2) as dpp,
                round(SUM((((qty - COALESCE(qty_reject,0)) * price) * (COALESCE(ph.tax,0) /100))),2) as ppn
                from bpb
                inner join masteritem mi on bpb.id_item = mi.id_item
                inner join mastersupplier ms on bpb.id_supplier = ms.id_supplier
                left join po_header ph on bpb.pono = ph.pono
                left join po_header_draft phd on phd.id = ph.id_draft
                where bpbno_int = ?
                group by bpbno, mattype, n_code_category
                order by supplier
                ", [$id_bpb]);

            if (!$cekdata) continue;

            $data = $cekdata[0];

        // Variabel
            $no_bpb = $data->bpbno_int;
            $supp = $data->supplier;
            $id_supplier = $data->id_supplier;
            $mattype = $data->mattype;
            $matclass1 = $data->matclass;
            $n_code_category = $data->n_code_category;
            $tax = $data->tax;
            $curr = $data->curr;
            $username = $data->username;
            $total = $data->total;
            $dpp = $data->dpp;
            $ppn = $data->ppn;
            $tgl_bpb = $data->bpbdate;
            $dateinput_ = $data->dateinput;
            $tipe_com = $data->tipe_com;

        // Tentukan matclass
            if ($mattype == 'C') {
                if (in_array($matclass1, ['CMT','PRINTING','EMBRODEIRY','WASHING','PAINTING','HEATSEAL'])) {
                    $matclass = $matclass1;
                } else {
                    $matclass = 'OTHER';
                }
            } else {
                $matclass = $matclass1;
            }

        // Rate
            $rate = 1;
            if ($curr != 'IDR') {
                $sqlrate = DB::connection('mysql_sb')->select(
                    "select ROUND(rate,2) as rate from masterrate where tanggal = ? and v_codecurr = 'PAJAK'", [$tgl_bpb]
                );
                $rate = $sqlrate ? $sqlrate[0]->rate : 1;
            }

            $idr_dpp = $dpp * $rate;
            $idr_ppn = $ppn * $rate;
            $idr_total = $total * $rate;

        // Category
            $cust_ctg = in_array($id_supplier, ['342','20','19','692','17','18']) ? 'Related' : 'Third';

        // Keterangan
            $kata1 = '';
            if ($mattype != 'N') {
                switch ($matclass) {
                    case 'FABRIC': $kata1 = "PEMBELIAN KAIN"; break;
                    case 'ACCESORIES': $kata1 = "PEMBELIAN AKSESORIS"; break;
                    case 'CMT': $kata1 = "BIAYA MAKLOON PAKAIAN JADI"; break;
                    case 'PRINTING': $kata1 = "BIAYA MAKLOON PRINTING"; break;
                    case 'EMBRODEIRY': $kata1 = "BIAYA MAKLOON EMBRODEIRY"; break;
                    case 'WASHING': $kata1 = "BIAYA MAKLOON WASHING"; break;
                    case 'PAINTING': $kata1 = "BIAYA MAKLOON PAINTING"; break;
                    case 'HEATSEAL': $kata1 = "BIAYA MAKLOON HEATSEAL"; break;
                    default: $kata1 = "BIAYA MAKLOON LAINNYA";
                }
            } else {
                switch ($n_code_category) {
                    case '1': $kata1 = "PEMBELIAN PERSEDIAAN ATK"; break;
                    case '2': $kata1 = "PEMBELIAN PERSEDIAAN UMUM"; break;
                    case '3': $kata1 = "BIAYA PERSEDIAAN SPAREPARTS"; break;
                    case '4': $kata1 = "BIAYA MESIN"; break;
                    default: $kata1 = "";
                }
            }
            $description = $kata1 . " " . $no_bpb . " DARI " . $supp;

        // Ambil COA dan buat jurnal
            $sqlcoa_cre = DB::connection('mysql_sb')->select("
                select no_coa, nama_coa from mastercoa_v2
                where cus_ctg like ? and mattype like ? and matclass like ? and n_code_category like ? and inv_type like '%bpb_credit%' limit 1
                ", ["%$cust_ctg%", "%$mattype%", "%$matclass%", "%$n_code_category%"]);
            $no_coa_cre = $sqlcoa_cre ? $sqlcoa_cre[0]->no_coa : '-';
            $nama_coa_cre = $sqlcoa_cre ? $sqlcoa_cre[0]->nama_coa : '-';

            $sqlcoa_deb = DB::connection('mysql_sb')->select("
                select no_coa, nama_coa from mastercoa_v2
                where cus_ctg like ? and mattype like ? and matclass like ? and n_code_category like ? and inv_type like '%bpb_debit%' limit 1
                ", ["%$cust_ctg%", "%$mattype%", "%$matclass%", "%$n_code_category%"]);
            $no_coa_deb = $sqlcoa_deb ? $sqlcoa_deb[0]->no_coa : '-';
            $nama_coa_deb = $sqlcoa_deb ? $sqlcoa_deb[0]->nama_coa : '-';


            Journal::create([
                'no_journal' => $no_bpb,
                'tgl_journal' => $tgl_bpb,
                'type_journal' => 'AP - BPB',
                'no_coa' => $no_coa_cre,
                'nama_coa' => $nama_coa_cre,
                'curr' => $curr,
                'rate' => $rate,
                'debit' => 0,
                'credit' => $total,
                'debit_idr' => 0,
                'credit_idr' => $idr_total,
                'status' => 'Approved',
                'keterangan' => $description,
                'create_by' => $username,
                'create_date' => $dateinput_,
                'approve_by' => Auth::user()->name,
                'approve_date' => $timestamp,
                'profit_center' => 'NAG',
            ]);

            Journal::create([
                'no_journal' => $no_bpb,
                'tgl_journal' => $tgl_bpb,
                'type_journal' => 'AP - BPB',
                'no_coa' => $no_coa_deb,
                'nama_coa' => $nama_coa_deb,
                'curr' => $curr,
                'rate' => $rate,
                'debit' => $dpp,
                'credit' => 0,
                'debit_idr' => $idr_dpp,
                'credit_idr' => 0,
                'status' => 'Approved',
                'keterangan' => $description,
                'create_by' => $username,
                'create_date' => $dateinput_,
                'approve_by' => Auth::user()->name,
                'approve_date' => $timestamp,
                'profit_center' => 'NAG',
            ]);

            if ($tax >= 1) {
                $sqlcoa_ppn = DB::connection('mysql_sb')->select("select no_coa, nama_coa from mastercoa_v2 where inv_type like '%PPN MASUKAN%' limit 1");
                $no_coa_ppn = $sqlcoa_ppn ? $sqlcoa_ppn[0]->no_coa : '-';
                $nama_coa_ppn = $sqlcoa_ppn ? $sqlcoa_ppn[0]->nama_coa : '-';

                Journal::create([
                    'no_journal' => $no_bpb,
                    'tgl_journal' => $tgl_bpb,
                    'type_journal' => 'AP - BPB',
                    'no_coa' => $no_coa_ppn,
                    'nama_coa' => $nama_coa_ppn,
                    'curr' => $curr,
                    'rate' => $rate,
                    'debit' => $ppn,
                    'credit' => 0,
                    'debit_idr' => $idr_ppn,
                    'credit_idr' => 0,
                    'status' => 'Approved',
                    'keterangan' => $description,
                    'create_by' => $username,
                    'create_date' => $dateinput_,
                    'approve_by' => Auth::user()->name,
                    'approve_date' => $timestamp,
                    'profit_center' => 'NAG',
                ]);
            }
        }

        return response()->json([
            "status" => 200,
            "message" => "Approved Data Successfully",
            "additional" => [],
        ]);
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
