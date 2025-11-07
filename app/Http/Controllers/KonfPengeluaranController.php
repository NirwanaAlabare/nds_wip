<?php

namespace App\Http\Controllers;

use App\Models\InMaterialFabric;
use App\Models\InMaterialFabricDet;
use App\Models\BppbDet;
use App\Models\BppbHeader;
use App\Models\BppbSB;
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


    public function approvepengeluaranall(Request $request)
{
    $timestamp = Carbon::now();

    DB::beginTransaction();
    try {

        foreach ($request->id_bpb as $i => $id_bpb) {
            $check = $request->chek_id[$i] ?? 0;
            if ($check <= 0) continue;

            // Update status BPPB
            BppbHeader::where('no_bppb', $id_bpb)->update([
                'status' => 'Approved',
                'approved_by' => Auth::user()->name,
                'approved_date' => $timestamp,
            ]);

            BppbSB::where('bppbno_int', $id_bpb)->update([
                'confirm' => 'Y',
                'confirm_by' => Auth::user()->name,
                'confirm_date' => $timestamp,
            ]);

            // Ambil data BPPB
            $cekdata = DB::connection('mysql_sb')->select("
                SELECT a.*, COALESCE(c.tax,0) as tax, (a.dpp + (a.dpp * (COALESCE(c.tax,0)/100))) as total, 
                       (a.dpp * (COALESCE(c.tax,0)/100)) as ppn
                FROM (
                    SELECT bppbno, bppbno_int, bppb.bppbdate, bppb.id_supplier, supplier, mattype, n_code_category,
                           IF(matclass LIKE '%ACCESORIES%', 'ACCESORIES', mi.matclass) as matclass, 
                           bppb.curr, bppb.username, bppb.dateinput, SUM(qty * price) as dpp, bpbno_ro
                    FROM bppb
                    INNER JOIN masteritem mi ON bppb.id_item = mi.id_item
                    INNER JOIN mastersupplier ms ON bppb.id_supplier = ms.id_supplier
                    WHERE bppbno_int = ?
                    GROUP BY bppbno_int
                ) a
                LEFT JOIN (
                    SELECT bpbno, pono FROM bpb GROUP BY bpbno
                ) b ON b.bpbno = a.bpbno_ro
                LEFT JOIN (
                    SELECT pono, tax FROM po_header GROUP BY pono
                ) c ON c.pono = b.pono
            ", [$id_bpb]);

            if (!$cekdata) continue; // skip jika tidak ada data

            $data = $cekdata[0];

            $no_bpb = $data->bppbno_int;
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
            $tgl_bpb = $data->bppbdate;
            $dateinput = $data->dateinput;

            // Tentukan matclass
            $matclass = ($mattype == 'C') ? 
                        (in_array($matclass1, ['CMT','PRINTING','EMBRODEIRY','WASHING','PAINTING','HEATSEAL']) ? $matclass1 : 'OTHER') 
                        : $matclass1;

            // Rate kurs
            $rate = 1;
            if ($curr != 'IDR') {
                $sqlrate = DB::connection('mysql_sb')->select("
                    SELECT ROUND(rate,2) as rate FROM masterrate 
                    WHERE tanggal = ? AND v_codecurr = 'PAJAK' 
                    LIMIT 1
                ", [$tgl_bpb]);
                $rate = $sqlrate ? $sqlrate[0]->rate : 1;
            }

            $idr_dpp = $dpp * $rate;
            $idr_ppn = $ppn * $rate;
            $idr_total = $total * $rate;

            // Customer category
            $cust_ctg = in_array($id_supplier, ['342','20','19','692','17','18']) ? 'Related' : 'Third';

            // Tentukan kata1
            if ($mattype != 'N') {
                switch ($matclass) {
                    case 'FABRIC': $kata1 = "RETURN PEMBELIAN KAIN"; break;
                    case 'ACCESORIES': $kata1 = "RETURN PEMBELIAN AKSESORIS"; break;
                    case 'CMT': $kata1 = "RETURN BIAYA MAKLOON PAKAIAN JADI"; break;
                    case 'PRINTING': $kata1 = "RETURN BIAYA MAKLOON PRINTING"; break;
                    case 'EMBRODEIRY': $kata1 = "RETURN BIAYA MAKLOON EMBRODEIRY"; break;
                    case 'WASHING': $kata1 = "RETURN BIAYA MAKLOON WASHING"; break;
                    case 'PAINTING': $kata1 = "RETURN BIAYA MAKLOON PAINTING"; break;
                    case 'HEATSEAL': $kata1 = "RETURN BIAYA MAKLOON HEATSEAL"; break;
                    default: $kata1 = "RETURN BIAYA MAKLOON LAINNYA"; break;
                }
            } else {
                switch ($n_code_category) {
                    case '1': $kata1 = "RETURN PEMBELIAN PERSEDIAAN ATK"; break;
                    case '2': $kata1 = "RETURN PEMBELIAN PERSEDIAAN UMUM"; break;
                    case '3': $kata1 = "RETURN BIAYA PERSEDIAAN SPAREPARTS"; break;
                    case '4': $kata1 = "RETURN BIAYA MESIN"; break;
                    default: $kata1 = ""; break;
                }
            }

            $description = $kata1 . " " . $no_bpb . " DARI " . $supp;

            // Ambil COA credit
            $sqlcoa = DB::connection('mysql_sb')->select("
                SELECT no_coa, nama_coa FROM mastercoa_v2 
                WHERE cus_ctg LIKE ? AND mattype LIKE ? AND matclass LIKE ? AND n_code_category LIKE ? AND inv_type LIKE '%bpb_credit%' 
                LIMIT 1
            ", ["%$cust_ctg%", "%$mattype%", "%$matclass%", "%$n_code_category%"]);

            $no_coa_cre = $sqlcoa ? $sqlcoa[0]->no_coa : '-';
            $nama_coa_cre = $sqlcoa ? $sqlcoa[0]->nama_coa : '-';

            Journal::create([
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
                'credit' => 0,
                'debit_idr' => $idr_total,
                'credit_idr' => 0,
                'status' => 'Approved',
                'keterangan' => $description,
                'create_by' => $username,
                'create_date' => $dateinput,
                'approve_by' => Auth::user()->name,
                'approve_date' => $timestamp,
                'cancel_by' => '',
                'cancel_date' => '',
                'profit_center' => 'NAG',
            ]);

            // Ambil COA debit
            $sqlcoa2 = DB::connection('mysql_sb')->select("
                SELECT no_coa, nama_coa FROM mastercoa_v2 
                WHERE cus_ctg LIKE ? AND mattype LIKE ? AND matclass LIKE ? AND n_code_category LIKE ? AND inv_type LIKE '%bpb_debit%' 
                LIMIT 1
            ", ["%$cust_ctg%", "%$mattype%", "%$matclass%", "%$n_code_category%"]);

            $no_coa_deb = $sqlcoa2 ? $sqlcoa2[0]->no_coa : '-';
            $nama_coa_deb = $sqlcoa2 ? $sqlcoa2[0]->nama_coa : '-';

            Journal::create([
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
                'debit' => 0,
                'credit' => $dpp,
                'debit_idr' => 0,
                'credit_idr' => $idr_dpp,
                'status' => 'Approved',
                'keterangan' => $description,
                'create_by' => $username,
                'create_date' => $dateinput,
                'approve_by' => Auth::user()->name,
                'approve_date' => $timestamp,
                'cancel_by' => '',
                'cancel_date' => '',
                'profit_center' => 'NAG',
            ]);

            // Jurnal PPN
            if ($tax >= 1) {
                $sqlcoa_ppn = DB::connection('mysql_sb')->select("
                    SELECT no_coa, nama_coa FROM mastercoa_v2 WHERE inv_type LIKE '%PPN MASUKAN%' LIMIT 1
                ");
                $no_coa_ppn = $sqlcoa_ppn ? $sqlcoa_ppn[0]->no_coa : '-';
                $nama_coa_ppn = $sqlcoa_ppn ? $sqlcoa_ppn[0]->nama_coa : '-';

                Journal::create([
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
                    'debit' => 0,
                    'credit' => $ppn,
                    'debit_idr' => 0,
                    'credit_idr' => $idr_ppn,
                    'status' => 'Approved',
                    'keterangan' => $description,
                    'create_by' => $username,
                    'create_date' => $dateinput,
                    'approve_by' => Auth::user()->name,
                    'approve_date' => $timestamp,
                    'cancel_by' => '',
                    'cancel_date' => '',
                    'profit_center' => 'NAG',
                ]);
            }
        }

        DB::commit();

        return response()->json([
            "status" => 200,
            "message" => "Approved Data Successfully",
            "additional" => [],
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            "status" => 500,
            "message" => "Failed to approve: " . $e->getMessage(),
            "additional" => [],
        ]);
    }
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
