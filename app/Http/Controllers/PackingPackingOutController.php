<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanPackingOut;
use App\Models\PackingOutGudangStok;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class PackingPackingOutController extends Controller
{
    public function index(Request $request)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $po = $request->txtpo;
        if ($po == null or $po == '') {
            $po_text = '';
        } else {
            $po = $request->txtpo;
            $po_text = "where po = '$po'";
        }
        $user = Auth::user()->name;
        $tujuan = $request->tujuan;
        if ($request->ajax()) {

            // Tujuan Gudang Stok punya sumber data sendiri (packing_out_gudang_stok),
            // selain itu (default) tetap pakai data scan ke ekspedisi.
            if ($tujuan == 'Gudang Stok') {
                return $this->getDataPackingOutGudangStok($tgl_awal, $tgl_akhir, $po);
            }

            $additionalQuery = '';
            $data_input = DB::select("WITH
                o as (
                    select
                    count(*) as tot,
                    tgl_trans,
                    id_ppic,
                    no_carton,
                    max(created_by) as created_by,
                    max(updated_at) as tgl_akt_input
                    from packing_packing_out_scan
                    where tgl_trans >= '$tgl_awal' and tgl_trans <=  '$tgl_akhir' and id_ppic is not null
                    group by tgl_trans, no_carton, id_ppic
                ),

                sb as (
                    select sd.id id_so_det, supplier as buyer, kpno, styleno, color, size,dest, reff_no
                    from signalbit_erp.so_det sd
                    inner join signalbit_erp.so on sd.id_so = so.id
                    inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
                    inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
                    where sd.cancel = 'N' and so.cancel_h = 'N'
                )

                SELECT
                    tot,
                    tgl_trans_fix,
                    tgl_akt_input,
                    po,
                    barcode,
                    color,
                    size,
                    no_carton,
                    ws,
                    styleno,
                    reff_no,
                    dest,
                    tgl_shipment,
                    created_by,
                    'PACKING CENTRAL' AS sumber,
                    'EKSPEDISI' AS tujuan
                FROM (
                    select
                        o.tot ,
                        DATE_FORMAT(o.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
                        DATE_FORMAT(o.tgl_akt_input, '%d-%m-%Y %H:%i:%s') AS tgl_akt_input,
                        p.po,
                        p.barcode,
                        sb.color,
                        sb.size,
                        no_carton,
                        sb.kpno as ws,
                        sb.styleno,
                        sb.reff_no,
                        sb.dest,
                        DATE_FORMAT(p.tgl_shipment, '%d-%m-%Y') AS tgl_shipment,
                        o.created_by
                    from o
                    left join ppic_master_so p on o.id_ppic = p.id
                    left join sb on p.id_so_det = sb.id_so_det
                    $po_text

                    UNION ALL

                    select
                        SUM(COALESCE(pc_packing_scan, 0)) tot,
                        DATE_FORMAT(tgl_saldo, '%d-%m-%Y') tgl_trans_fix,
                        DATE_FORMAT(tgl_saldo, '%d-%m-%Y %H:%i:%s') AS tgl_akt_input,
                        '-' po,
                        '-' barcode,
                        color,
                        size,
                        '-' no_carton,
                        ws,
                        styleno,
                        styleno reff_no,
                        '-' dest,
                        DATE_FORMAT(tgl_saldo, '%d-%m-%Y') AS tgl_shipment,
                        'INJECT' created_by
                    from
                        signalbit_erp.inject_mutasi_sewing
                    where
                        tgl_saldo >= '$tgl_awal' and tgl_saldo <= '$tgl_akhir'
                        and pc_packing_scan > 0
                    GROUP BY
                        ws,
                        color,
                        size
                ) packing_out
                $po_text
                order by
                    tgl_trans_fix desc,
                    po asc,
                    no_carton asc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view(
            'packing.packing_out',
            [
                'page' => 'dashboard-packing',
                "subPageGroup" => "packing-packing-out",
                "subPage" => "packing-out"
            ]
        );
    }

    private function getDataPackingOutGudangStok($tgl_awal, $tgl_akhir, $po)
    {
        return DataTables::of($this->queryPackingOutGudangStok($tgl_awal, $tgl_akhir, $po))->toJson();
    }

    private function queryPackingOutGudangStok($tgl_awal, $tgl_akhir, $po)
    {
        $binding = [$tgl_awal, $tgl_akhir];

        $po_text = '';
        if ($po !== null && $po !== '') {
            $po_text = 'AND a.po = ?';
            $binding[] = $po;
        }

        $data = DB::select("
            SELECT
                a.no_trans,
                DATE_FORMAT(a.created_at, '%d-%m-%Y') AS tanggal,
                a.no_karton,
                a.po,
                w.ws,
                w.styleno,
                w.color,
                w.size,
                a.qty,
                a.grade,
                UPPER(a.lokasi_asal) AS sumber,
                UPPER(a.tujuan) AS tujuan,
                a.created_by_username AS created_by,
                DATE_FORMAT(a.created_at, '%d-%m-%Y %H:%i:%s') AS tgl_akt_input,
                CASE
                    WHEN COALESCE(fsp.qty_terima, 0) >= a.qty
                        THEN 'TERIMA'
                    ELSE 'PENDING'
                END AS status
            FROM packing_out_gudang_stok a
            LEFT JOIN master_sb_ws w ON w.id_so_det = a.so_det_id
            LEFT JOIN (
                SELECT
                    packing_out_gudang_stok_id,
                    SUM(qty) AS qty_terima
                FROM fg_stok_penerimaan_packing
                GROUP BY packing_out_gudang_stok_id
            ) fsp ON fsp.packing_out_gudang_stok_id = a.id
            WHERE DATE(a.created_at) >= ? AND DATE(a.created_at) <= ?
            $po_text
            ORDER BY
                a.created_at DESC,
                a.po ASC,
                a.no_karton ASC
        ", $binding);

        return $data;
    }

    public function getno_carton(Request $request)
    {
        $search = $request->input('search');
        $cbopo = $request->input('cbopo');

        // STOK GUDANG (FGS)
        if ($cbopo !== null && $cbopo !== '' && (int) $cbopo === 0) {
            return response()->json($this->getNoCartonGudangStok($search));
        }

        // Get PO and destination from master table
        $cek_po = DB::table('ppic_master_so')
            ->select('po', 'dest')
            ->where('id', $cbopo)
            ->first();

        if (!$cek_po) {
            return response()->json([]);
        }

        $po = $cek_po->po;
        $dest = $cek_po->dest;

        // Main query for carton data with qty balance
        $subQuery = DB::table('packing_master_packing_list as a')
            ->select('a.no_carton', DB::raw('SUM(a.qty) as total_pl'), DB::raw('SUM(COALESCE(b.qty_scan, 0)) as total_scan'))
            ->leftJoin(DB::raw('(
            SELECT po, no_carton, dest, barcode, COUNT(barcode) as qty_scan
            FROM packing_packing_out_scan
            WHERE po = "' . $po . '" AND dest = "' . $dest . '"
            GROUP BY po, no_carton, dest, barcode
        ) b'), function ($join) {
                $join->on('a.po', '=', 'b.po')
                    ->on('a.no_carton', '=', 'b.no_carton')
                    ->on('a.dest', '=', 'b.dest')
                    ->on('a.barcode', '=', 'b.barcode');
            })
            ->where('a.po', $po)
            ->where('a.dest', $dest)
            ->groupBy('a.no_carton')
            ->havingRaw('SUM(a.qty) - SUM(COALESCE(b.qty_scan, 0)) != 0');

        // Apply search filter if exists
        if (!empty($search)) {
            $subQuery->where('a.no_carton', 'like', '%' . $search . '%');
        }

        $data_carton = $subQuery->limit(50)->get();

        // Format response for Select2
        $results = $data_carton->map(function ($row) {
            return [
                'id' => $row->no_carton,
                'text' => $row->no_carton
            ];
        });

        return response()->json($results);
    }

    private function getNoCartonGudangStok($search)
    {
        // Stok gudang selalu tercatat dengan po ini di packing_packing_in
        $po = 'GUDANG STOK';

        // Qty stok gudang per carton per so_det. No. carton tidak ada di
        // packing list, dia menempel di fg_stok_bppb lewat fg_stok_bppb_id.
        $stok = '(
            SELECT a.po, b.no_carton, a.id_so_det, SUM(a.qty) AS qty
            FROM packing_packing_in a
            INNER JOIN fg_stok_bppb b ON b.id = a.fg_stok_bppb_id
            WHERE a.id_ppic_master_so IS NULL
            GROUP BY a.po, b.no_carton, a.id_so_det
        ) a';

        // Dest diambil dari ppic_master_so lewat id_so_det. Satu so_det bisa
        // punya lebih dari satu baris PO, dirapikan dulu biar tidak dobel.
        $master = '(
            SELECT id_so_det, MAX(dest) AS dest
            FROM ppic_master_so
            GROUP BY id_so_det
        ) p';

        // Main query for carton data with qty balance
        $subQuery = DB::table(DB::raw($stok))
            ->select('a.no_carton', DB::raw('SUM(a.qty) as total_pl'), DB::raw('SUM(COALESCE(b.qty_scan, 0)) as total_scan'))
            ->join(DB::raw($master), 'p.id_so_det', '=', 'a.id_so_det')
            ->leftJoin(DB::raw('(
            SELECT po, dest, no_carton, id_so_det, COUNT(barcode) as qty_scan
            FROM packing_packing_out_scan
            WHERE po = "' . $po . '"
            GROUP BY po, dest, no_carton, id_so_det
        ) b'), function ($join) {
                $join->on('a.po', '=', 'b.po')
                    ->on('p.dest', '=', 'b.dest')
                    ->on('a.no_carton', '=', 'b.no_carton')
                    ->on('a.id_so_det', '=', 'b.id_so_det');
            })
            ->where('a.po', $po)
            ->groupBy('a.no_carton')
            ->havingRaw('SUM(a.qty) - SUM(COALESCE(b.qty_scan, 0)) != 0');

        // Apply search filter if exists
        if (!empty($search)) {
            $subQuery->where('a.no_carton', 'like', '%' . $search . '%');
        }

        $data_carton = $subQuery->limit(50)->get();

        // Format response for Select2
        return $data_carton->map(function ($row) {
            return [
                'id' => $row->no_carton,
                'text' => $row->no_carton
            ];
        });
    }


    //     public function getno_carton(Request $request)
    // {
    //     $cek_po = DB::select("
    //     select po, dest from ppic_master_so where id = '" . $request->cbopo . "'
    //     ");

    //     $po = $cek_po ? $cek_po[0]->po : null;
    //     $dest = $cek_po ? $cek_po[0]->dest : null;


    //     $data_carton = DB::select("SELECT
    //     a.no_carton isi, a.no_carton tampil
    //     from
    //     (
    //     select po, no_carton, dest, barcode, qty qty_pl
    //     from packing_master_packing_list where po = '$po' and dest = '$dest'
    //     ) a
    //     left join
    //     (
    //     select po, no_carton, dest, barcode, count(barcode) qty_scan
    //     from packing_packing_out_scan where po = '$po' and dest = '$dest'
    //     group by po, no_carton, dest, barcode
    //     ) b on a.po = b.po and a.no_carton = b.no_carton and a.dest = b.dest and a.barcode = b.barcode
    // 	where a.qty_pl -  coalesce(qty_scan,0) != '0'
    //     group by a.no_carton
    //     ");

    //     $html = "<option value=''>Pilih No Carton</option>";

    //     foreach ($data_carton as $datacarton) {
    //         $html .= " <option value='" . $datacarton->isi . "'>" . $datacarton->tampil . "</option> ";
    //     }

    //     return $html;
    // }



    public function getpo(Request $request)
    {
        $tgl_skrg = date('Y-m-d');
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-120 days'));
        // $cek_po = DB::select("
        // select * from ppic_master_so where id = '" . $request->cbopo . "' and tgl_shipment >= '$tgl_skrg_min_sebulan'
        // ");

        // STOK GUDANG (FGS)
        if ($request->cbopo !== null && $request->cbopo !== '' && (int) $request->cbopo === 0) {
            $cek_po_gudang_stok = DB::select("
            select po, dest from packing_packing_in
            where id_ppic_master_so is null and po is not null
            group by po, dest
            limit 1
            ");

            return json_encode($cek_po_gudang_stok ? $cek_po_gudang_stok[0] : '-');
        }

        $cek_po = DB::select("
        select * from ppic_master_so where id = '" . $request->cbopo . "'
        ");

        // return json_encode($cek_po[0]);
        return json_encode($cek_po ? $cek_po[0] : '-');
    }



    public function packing_out_show_summary(Request $request)
    {
        if (!$request->ajax()) {
            abort(403);
        }

        $po = $request->cbopo;
        $cbono_carton = $request->cbono_carton;
        $dest = $request->txtdest;

        // STOK GUDANG (FGS)
        if ($po === 'GUDANG STOK') {
            return $this->showSummaryGudangStok($po, $dest, $cbono_carton);
        }

        // Main data query
        $data_summary = DB::select("
            SELECT a.*, COALESCE(tot_scan, 0) AS tot_scan
            FROM (
                SELECT
                    a.no_carton,
                    a.po,
                    a.dest,
                    a.id_ppic_master_so,
                    a.id_so_det,
                    m.size,
                    m.color,
                    a.barcode,
                    a.qty
                FROM packing_master_packing_list a
                INNER JOIN ppic_master_so p ON a.id_ppic_master_so = p.id
                INNER JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                WHERE a.po = ?
                AND a.dest = ?
                AND a.no_carton = ?
            ) a
            LEFT JOIN (
                SELECT
                    COUNT(barcode) AS tot_scan,
                    barcode,
                    po,
                    no_carton,
                    dest
                FROM packing_packing_out_scan
                WHERE po = ?
                AND dest = ?
                AND no_carton = ?
                GROUP BY barcode, no_carton, dest, po
            ) b ON a.po = b.po
            AND a.dest = b.dest
            AND a.no_carton = b.no_carton
            AND a.barcode = b.barcode
            LEFT JOIN master_size_new msn ON a.size = msn.size
            ORDER BY color ASC, urutan ASC
        ", [$po, $dest, $cbono_carton, $po, $dest, $cbono_carton]);

        // Compute totals (done server-side)
        $total_qty = collect($data_summary)->sum('qty');
        $total_scan = collect($data_summary)->sum('tot_scan');

        // Return DataTables-compatible JSON
        return response()->json([
            'data' => $data_summary,
            'totals' => [
                'qty' => $total_qty,
                'tot_scan' => $total_scan,
            ],
        ]);
    }

    private function showSummaryGudangStok($po, $dest, $cbono_carton)
    {
        $data_summary = DB::select("
            SELECT a.*, COALESCE(tot_scan, 0) AS tot_scan
            FROM (
                SELECT
                    b.no_carton,
                    a.po,
                    p.dest,
                    a.id_ppic_master_so,
                    a.id_so_det,
                    m.size,
                    m.color,
                    p.barcode,
                    SUM(a.qty) AS qty
                FROM packing_packing_in a
                INNER JOIN fg_stok_bppb b ON b.id = a.fg_stok_bppb_id
                INNER JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                INNER JOIN (
                    SELECT
                        id_so_det,
                        MAX(dest) AS dest,
                        MAX(barcode) AS barcode
                    FROM ppic_master_so
                    GROUP BY id_so_det
                ) p ON p.id_so_det = a.id_so_det
                WHERE a.id_ppic_master_so IS NULL
                AND a.po = ?
                AND p.dest = ?
                AND b.no_carton = ?
                GROUP BY
                    b.no_carton,
                    a.po,
                    p.dest,
                    a.id_ppic_master_so,
                    a.id_so_det,
                    m.size,
                    m.color,
                    p.barcode
            ) a
            LEFT JOIN (
                SELECT
                    COUNT(*) AS tot_scan,
                    id_so_det,
                    po,
                    no_carton,
                    dest
                FROM packing_packing_out_scan
                WHERE po = ?
                AND dest = ?
                AND no_carton = ?
                AND id_ppic IS NULL
                GROUP BY id_so_det, no_carton, dest, po
            ) b ON a.po = b.po
            AND a.dest = b.dest
            AND a.no_carton = b.no_carton
            AND a.id_so_det = b.id_so_det
            LEFT JOIN master_size_new msn ON a.size = msn.size
            ORDER BY color ASC, urutan ASC
        ", [$po, $dest, $cbono_carton, $po, $dest, $cbono_carton]);

        $total_qty = collect($data_summary)->sum('qty');
        $total_scan = collect($data_summary)->sum('tot_scan');

        return response()->json([
            'data' => $data_summary,
            'totals' => [
                'qty' => $total_qty,
                'tot_scan' => $total_scan,
            ],
        ]);
    }


    public function packing_out_show_history(Request $request)
    {
        $user = Auth::user()->name;
        $tgl_trans = date('Y-m-d');

        $cbono_carton = $request->cbono_carton ? $request->cbono_carton : null;

        // if ($cbono_carton == null) {
        //     $no_carton = '-';
        //     $notes = '-';
        // } else {
        //     $cekArray = explode('_', $cbono_carton);
        //     $no_carton = $cekArray[0];
        //     $notes = $cekArray[1];
        // }



        if ($request->ajax()) {

            $data_history = DB::select("
                select
                    o.id,
                    tgl_trans,
                    if (o.tgl_trans = '" . $tgl_trans . "'  and c.po is null,'ok','no') cek_stat,
                    DATE_FORMAT(o.created_at, '%d-%m-%Y %H:%i:%s') created_at,
                    o.po,
                    o.barcode,
                    CASE
                        WHEN o.id_ppic IS NULL THEN mgs.color
                        ELSE m.color
                    END AS color,
                    CASE
                        WHEN o.id_ppic IS NULL THEN mgs.size
                        ELSE m.size
                    END AS size
                from packing_packing_out_scan o
                left join ppic_master_so p on o.barcode = p.barcode and o.po = p.po and o.po = p.po and o.dest = p.dest
                left join master_sb_ws m on p.id_so_det = m.id_so_det
                left join master_sb_ws mgs on mgs.id_so_det = o.id_so_det and o.id_ppic is null
                left join
                (
                select po, barcode, dest, no_carton, sum(qty)tot_fg from fg_fg_in where po = '" . $request->cbopo . "'
                and dest = '" . $request->txtdest . "' and no_carton = '" . $request->cbono_carton . "' and status = 'NORMAL') c
                on o.barcode = c.barcode and o.po = c.po and o.po = c.po and o.dest = c.dest
                where o.no_carton = '" . $request->cbono_carton . "' and o.po = '" . $request->cbopo . "' and o.dest = '" . $request->txtdest . "'
                order by o.created_at desc
            ");
            return DataTables::of($data_history)->toJson();
        }
    }

    public function packing_out_hapus_history(Request $request)
    {
        $id_history = $request->id_history;

        $ins_history =  DB::insert("
insert into packing_packing_out_scan_log (id_packing_Packing_out_scan, tgl_trans, barcode, po, no_carton, created_at, updated_at, created_by)
SELECT id, tgl_trans, barcode, po, no_carton,created_at, updated_at, created_by  FROM `packing_packing_out_scan` where id = '$id_history'");

        $del_history =  DB::delete("
        delete from packing_packing_out_scan where id = '$id_history'");
    }

    public function create(Request $request)
    {
        $user = Auth::user()->name;

        $tgl_skrg_4_bln = date('Y-m-d', strtotime('-90 days'));

        $data_po = DB::select("SELECT
            a.id_ppic_master_so isi,
            concat(a.po, ' - ', a.dest, ' ( ', count(distinct(a.no_carton)), ' ) ') tampil,
            act.close_order
            from packing_master_packing_list a
            inner join ppic_master_so p on a.id_ppic_master_so = p.id
            LEFT JOIN master_sb_ws m on p.id_so_det = m.id_so_det
            LEFT JOIN signalbit_erp.act_costing act on m.id_act_cost = act.id
            where p.tgl_shipment >= '$tgl_skrg_4_bln'
            group by a.po, a.dest

            -- union all

            -- STOK GUDANG (FGS)
            -- select
            -- 0 isi,
            -- concat(a.po, ' - ', a.dest, ' ( ', count(distinct(b.no_carton)), ' ) ') tampil
            -- from packing_packing_in a
            -- inner join fg_stok_bppb b ON b.id = a.fg_stok_bppb_id
            -- where a.id_ppic_master_so is null
            -- and a.po is not null
            -- and a.tgl_penerimaan >= '$tgl_skrg_4_bln'
            -- group by a.po, a.dest
        ");

        // $data_po = DB::select("SELECT p.po isi, concat(p.po, ' - ( ', coalesce(max(m.no_carton),0) , ' ) ') tampil
        // from ppic_master_so p
        // left join packing_master_carton m on p.po = m.po
        // where barcode is not null and barcode != '' and barcode != '-'
        // group by p.po");

        return view('packing.create_packing_out', [
            'page' => 'dashboard-packing',
            "subPageGroup" => "packing-packing-out",
            "subPage" => "packing-out",
            "data_po" => $data_po,
            "user" => $user
        ]);
    }


    // DEPRECATED
    // public function store(Request $request)
    // {
    //     $timestamp  = Carbon::now();
    //     $user       = Auth::user()->name;
    //     $barcode    = $request->barcode;
    //     $cbopo    = $request->cbopo;
    //     $no_carton    = $request->cbono_carton;
    //     $dest    = $request->txtdest;
    //     // $no_carton_cek    = $request->cbono_carton;
    //     // $cekArray = explode('_', $no_carton_cek);
    //     // $no_carton = $cekArray[0];
    //     // $notes = $cekArray[1];



    //     $cek_po_by_ppic = DB::select("
    //             select * from ppic_master_so where id = '$cbopo'
    //         ");
    //     $cek_dest_po = $cek_po_by_ppic[0]->po;

    //     $tgl_trans = date('Y-m-d');

    //     $cek_po = DB::select("
    //             select * from ppic_master_so where barcode = '$barcode' and  dest = '$dest' and po = '$cek_dest_po'
    //         ");

    //     if(!$cek_po){
    //             return array(
    //             'icon' => 'salah',
    //             'msg' => 'Data tidak ada di Master PPIC',
    //         );
    //     }


    //     $id_so_det =  $cek_po[0]->id_so_det;
    //     $id_ppic =  $cek_po[0]->id;


    //     $cek_data = DB::select("
    //     select count(barcode) cek from ppic_master_so p
    //     where barcode = '$barcode' and po = '$cek_dest_po' and dest = '$dest'
    //     ");


    //     $cek_data_fix = $cek_data[0]->cek;
    //     // dd("select count(barcode) cek from ppic_master_so p
    //     // where barcode = '$barcode' and po = '$cek_dest_po' and dest = '$dest'");

    //     if ($cek_data_fix >= '1') {

    //         $cek_stok = DB::select("
    //         select coalesce(pack_in.tot_in,0) - coalesce(pack_out.tot_out,0) - coalesce(pack_switch.qty_switch,0) tot_s
    //         from ppic_master_so p
    //         left join
    //         (
    //             SELECT sum( packing_packing_in.qty ) tot_in, packing_packing_in.id_ppic_master_so FROM packing_packing_in inner join ppic_master_so on ppic_master_so.id = packing_packing_in.id_ppic_master_so WHERE packing_packing_in.barcode = '$barcode' AND ppic_master_so.po = '$cek_dest_po' AND ppic_master_so.dest = '$dest' GROUP BY id_ppic_master_so
    //         ) pack_in on p.id = pack_in.id_ppic_master_so
    //         left join
    //         (
    //             select count(p.barcode) tot_out, p.id
    //             from packing_packing_out_scan a
    //             inner join ppic_master_so p on a.barcode = p.barcode and a.po = p.po and a.dest = p.dest
    //             where p.barcode = '$barcode' and p.po = '$cek_dest_po' and p.dest = '$dest'
    //             group by a.barcode, a.po
    //         ) pack_out on p.id = pack_out.id
    //         LEFT JOIN
    //         (
    //             SELECT
    //                 asal_ppic_master_so_id,
    //                 SUM(qty_switch) AS qty_switch
    //             FROM packing_central_switching
    //             GROUP BY asal_ppic_master_so_id
    //         ) pack_switch
    //             ON p.id = pack_switch.asal_ppic_master_so_id
    //         where p.barcode = '$barcode' and p.po = '$cek_dest_po' and dest = '$dest'
    //         ");

    //         $cek_stok_fix = $cek_stok[0]->tot_s;

    //         $cek_qty_isi_karton = DB::select("SELECT qty, coalesce(tot_input,0) tot_input from
    //         (select po, no_carton, barcode, dest ,qty from packing_master_packing_list
    //         where po = '$cek_dest_po' and no_carton = '$no_carton' and barcode = '$barcode' and dest = '$dest'
    //         )a
    //         left join
    //         (
    //         select po, no_carton, barcode, dest,count(barcode) tot_input
    //         from packing_packing_out_scan
    //         where po = '$cek_dest_po' and no_carton = '$no_carton' and barcode = '$barcode' and dest = '$dest'
    //         ) b on a.po = b.po and a.dest = b.dest and a.no_carton = b.no_carton and a.barcode = b.barcode");

    //         if ($cek_qty_isi_karton) {
    //             $cek_qty_isi = $cek_qty_isi_karton[0]->qty;
    //             $tot_out = $cek_qty_isi_karton[0]->tot_input;
    //             if ($cek_stok_fix >= '1') {

    //                 if ($cek_qty_isi > $tot_out) {
    //                     $insert = DB::insert("
    //                         insert into packing_packing_out_scan
    //                         (tgl_trans,barcode,po,dest,no_carton,notes,created_by,created_at,updated_at, id_so_det, id_ppic)
    //                         values
    //                         (
    //                             '$tgl_trans',
    //                             '$barcode',
    //                             '$cek_dest_po',
    //                             '$dest',
    //                             '$no_carton',
    //                             '-',
    //                             '$user',
    //                             '$timestamp',
    //                             '$timestamp',
    //                             '$id_so_det',
    //                             '$id_ppic'
    //                         )
    //                     ");
    //                     return array(
    //                         'icon' => 'benar',
    //                         'msg' => 'Data berhasil Disimpan',
    //                     );
    //                 } else if ($cek_qty_isi == $tot_out) {
    //                     return array(
    //                         'icon' => 'lebih',
    //                         'msg' => 'Data sudah melebihi qty karton',
    //                     );
    //                 } else {
    //                     return array(
    //                         'icon' => 'salah',
    //                         'msg' => 'Tidak Ada Data 1',
    //                     );
    //                 }
    //             } else
    //                 return array(
    //                     'icon' => 'salah',
    //                     'msg' => 'Tidak Ada Stok',
    //                 );
    //         } else
    //             return array(
    //                 'icon' => 'salah',
    //                 'msg' => 'Tidak Ada Data 2',
    //             );
    //     } else {
    //         return array(
    //             'icon' => 'salah',
    //             'msg' => 'Data tidak ada di packing list',
    //         );
    //     }
    // }

    public function store(Request $request)
    {
        $timestamp  = Carbon::now();
        $user       = Auth::user()->name;
        $barcode    = $request->barcode;
        $cbopo    = $request->cbopo;
        $no_carton    = $request->cbono_carton;
        $dest    = $request->txtdest;

        return DB::transaction(function () use (
            $cbopo,
            $barcode,
            $dest,
            $no_carton,
            $user,
            $timestamp
        ) {

            // STOK GUDANG (FGS)
            if ($cbopo !== null && $cbopo !== '' && (int) $cbopo === 0) {
                return $this->storeGudangStok($barcode, $no_carton, $user, $timestamp);
            }

            // =========================================================
            // CEK PO PPIC
            // =========================================================
            $cek_po_by_ppic = DB::select("
                SELECT *
                FROM ppic_master_so
                WHERE id = '$cbopo'
                FOR UPDATE
            ");

            if (!$cek_po_by_ppic) {
                return [
                    'icon' => 'salah',
                    'msg' => 'Data PO PPIC tidak ditemukan',
                ];
            }

            $cek_dest_po = $cek_po_by_ppic[0]->po;

            $tgl_trans = date('Y-m-d');


            // =========================================================
            // CEK BARCODE DI MASTER PPIC
            // =========================================================
            $cek_po = DB::select("
                SELECT *
                FROM ppic_master_so
                WHERE barcode = '$barcode'
                AND dest = '$dest'
                AND po = '$cek_dest_po'
            ");

            if (!$cek_po) {
                return [
                    'icon' => 'salah',
                    'msg' => 'Data tidak ada di Master PPIC',
                ];
            }

            $id_so_det = $cek_po[0]->id_so_det;
            $id_ppic   = $cek_po[0]->id;


            // =========================================================
            // CEK DATA BARCODE
            // =========================================================
            $cek_data = DB::select("
                SELECT COUNT(barcode) AS cek
                FROM ppic_master_so p
                WHERE barcode = '$barcode'
                AND po = '$cek_dest_po'
                AND dest = '$dest'
            ");

            $cek_data_fix = $cek_data[0]->cek;

            if ($cek_data_fix < 1) {
                return [
                    'icon' => 'salah',
                    'msg' => 'Data tidak ada di packing list',
                ];
            }

            // =========================================================
            // LOCK PACKING LIST
            // =========================================================
            // INI BAGIAN PALING PENTING
            //
            // Request pertama akan mendapatkan lock.
            // Request kedua dengan barcode + PO + dest + carton yang sama
            // akan menunggu sampai request pertama selesai.
            // =========================================================

            $packing_list = DB::table('packing_master_packing_list')
                ->where('po', $cek_dest_po)
                ->where('no_carton', $no_carton)
                ->where('barcode', $barcode)
                ->where('dest', $dest)
                ->lockForUpdate()
                ->first();


            // =========================================================
            // CEK PACKING LIST
            // =========================================================
            if (!$packing_list) {
                return [
                    'icon' => 'salah',
                    'msg' => 'Data tidak ada di packing list',
                ];
            }

            $cek_qty_isi = $packing_list->qty;


            // =========================================================
            // CEK STOK
            // =========================================================
            //
            // Query ini dilakukan SETELAH lock.
            // Jadi kalau ada request lain yang sudah insert,
            // hasil tot_out akan ikut ter-update.
            // =========================================================

            $cek_stok = DB::select("
                SELECT
                    SUM(
                        COALESCE(pack_in.tot_in, 0)
                        + COALESCE(pack_switch_in.qty_switch_masuk, 0)
                        - COALESCE(pack_out.tot_out, 0)
                        - COALESCE(pack_switch.qty_switch, 0)
                    ) AS tot_s

                FROM ppic_master_so p

                LEFT JOIN
                (
                    SELECT
                        SUM(packing_packing_in.qty) AS tot_in,
                        packing_packing_in.id_ppic_master_so,
                        packing_packing_in.id_so_det

                    FROM packing_packing_in

                    INNER JOIN ppic_master_so
                        ON ppic_master_so.id = packing_packing_in.id_ppic_master_so

                    WHERE packing_packing_in.barcode IN ('$barcode', '0')
                    AND ppic_master_so.po = '$cek_dest_po'
                    AND ppic_master_so.dest = '$dest'
                    AND packing_packing_in.sumber = 'Sewing'

                    GROUP BY
                        packing_packing_in.id_ppic_master_so,
                        packing_packing_in.id_so_det
                ) pack_in
                    ON p.id = pack_in.id_ppic_master_so

                LEFT JOIN
                (
                    SELECT
                        COUNT(*) AS tot_out,
                        a.id_ppic,
                        a.id_so_det

                    FROM packing_packing_out_scan a

                    INNER JOIN ppic_master_so p
                        ON a.barcode = p.barcode
                        AND a.po = p.po
                        AND a.dest = p.dest

                    WHERE p.barcode = '$barcode'
                    AND p.po = '$cek_dest_po'
                    AND p.dest = '$dest'

                    GROUP BY
                        a.id_ppic,
                        a.id_so_det
                ) pack_out
                    ON p.id = pack_out.id_ppic
                    AND p.id_so_det = pack_out.id_so_det

                LEFT JOIN
                (
                    SELECT
                        asal_ppic_master_so_id,
                        asal_so_det_id,
                        SUM(qty_switch) AS qty_switch

                    FROM packing_central_switching

                    GROUP BY
                        asal_ppic_master_so_id,
                        asal_so_det_id
                ) pack_switch
                    ON p.id = pack_switch.asal_ppic_master_so_id
                    AND p.id_so_det = pack_switch.asal_so_det_id

                LEFT JOIN
                (
                    SELECT
                        tujuan_ppic_master_so_id,
                        tujuan_so_det_id,
                        SUM(qty_switch) AS qty_switch_masuk

                    FROM packing_central_switching

                    GROUP BY
                        tujuan_ppic_master_so_id,
                        tujuan_so_det_id
                ) pack_switch_in
                    ON p.id = pack_switch_in.tujuan_ppic_master_so_id
                    AND p.id_so_det = pack_switch_in.tujuan_so_det_id

                WHERE p.barcode = '$barcode'
                AND p.po = '$cek_dest_po'
                AND p.dest = '$dest'
            ");

            $cek_stok_fix = $cek_stok[0]->tot_s ?? 0;

            // =========================================================
            // CEK STOK
            // =========================================================

            if ($cek_stok_fix < 1) {
                return [
                    'icon' => 'salah',
                    'msg' => 'Tidak Ada Stok',
                ];
            }


            // =========================================================
            // HITUNG TOTAL SUDAH OUT
            // =========================================================
            //
            // Karena packing_master_packing_list sudah di-lock,
            // request yang sama akan masuk secara bergantian.
            // =========================================================

            $cek_qty_isi_karton = DB::select("
                SELECT
                    qty,
                    COALESCE(tot_input, 0) AS tot_input

                FROM
                (
                    SELECT
                        po,
                        no_carton,
                        barcode,
                        dest,
                        qty

                    FROM packing_master_packing_list

                    WHERE po = '$cek_dest_po'
                    AND no_carton = '$no_carton'
                    AND barcode = '$barcode'
                    AND dest = '$dest'
                ) a

                LEFT JOIN
                (
                    SELECT
                        po,
                        no_carton,
                        barcode,
                        dest,
                        COUNT(barcode) AS tot_input

                    FROM packing_packing_out_scan

                    WHERE po = '$cek_dest_po'
                    AND no_carton = '$no_carton'
                    AND barcode = '$barcode'
                    AND dest = '$dest'

                    GROUP BY
                        po,
                        no_carton,
                        barcode,
                        dest

                ) b
                    ON a.po = b.po
                    AND a.dest = b.dest
                    AND a.no_carton = b.no_carton
                    AND a.barcode = b.barcode
            ");


            // =========================================================
            // CEK DATA KARTON
            // =========================================================

            if (!$cek_qty_isi_karton) {
                return [
                    'icon' => 'salah',
                    'msg' => 'Tidak Ada Data 2',
                ];
            }


            $cek_qty_isi = $cek_qty_isi_karton[0]->qty;
            $tot_out      = $cek_qty_isi_karton[0]->tot_input;


            // =========================================================
            // CEK QTY KARTON
            // =========================================================

            if ($cek_qty_isi > $tot_out) {

                // =====================================================
                // INSERT
                // =====================================================

                DB::insert("
                    INSERT INTO packing_packing_out_scan
                    (
                        tgl_trans,
                        barcode,
                        po,
                        dest,
                        no_carton,
                        notes,
                        created_by,
                        created_at,
                        updated_at,
                        id_so_det,
                        id_ppic
                    )
                    VALUES
                    (
                        '$tgl_trans',
                        '$barcode',
                        '$cek_dest_po',
                        '$dest',
                        '$no_carton',
                        '-',
                        '$user',
                        '$timestamp',
                        '$timestamp',
                        '$id_so_det',
                        '$id_ppic'
                    )
                ");


                return [
                    'icon' => 'benar',
                    'msg' => 'Data berhasil Disimpan',
                ];
            }


            // =========================================================
            // QTY SUDAH PENUH
            // =========================================================

            if ($cek_qty_isi == $tot_out) {
                return [
                    'icon' => 'lebih',
                    'msg' => 'Data sudah melebihi qty karton',
                ];
            }


            // =========================================================
            // KONDISI LAIN
            // =========================================================

            return [
                'icon' => 'salah',
                'msg' => 'Tidak Ada Data 1',
            ];

        });
    }

    private function storeGudangStok($barcode, $no_carton, $user, $timestamp)
    {
        $po = 'GUDANG STOK';
        $tgl_trans = date('Y-m-d');


        // =========================================================
        // CEK BARCODE DI MASTER PPIC
        // =========================================================
        //
        // Stok gudang tidak punya PO sendiri di ppic_master_so, jadi
        // barcode tidak bisa dicocokkan lewat po. Barcode juga tidak
        // unik lintas PO, jadi pembatasnya so_det yang memang ada di
        // carton ini.
        // =========================================================

        $cek_po = DB::select("
            SELECT
                p.id,
                p.id_so_det,
                p.dest

            FROM ppic_master_so p

            INNER JOIN
            (
                SELECT a.id_so_det

                FROM packing_packing_in a

                INNER JOIN fg_stok_bppb b
                    ON b.id = a.fg_stok_bppb_id

                WHERE a.id_ppic_master_so IS NULL
                AND a.po = '$po'
                AND b.no_carton = '$no_carton'

                GROUP BY a.id_so_det
            ) c
                ON c.id_so_det = p.id_so_det

            WHERE p.barcode = '$barcode'
        ");

        if (!$cek_po) {
            return [
                'icon' => 'salah',
                'msg' => 'Barcode tidak ada di carton stok gudang ini',
            ];
        }

        $id_so_det = $cek_po[0]->id_so_det;
        $dest      = $cek_po[0]->dest;


        // =========================================================
        // LOCK STOK GUDANG
        // =========================================================
        //
        // Padanan lock packing list di alur normal. Stok gudang tidak
        // punya baris packing list, yang dikunci baris packing in-nya.
        // =========================================================

        $stok_gudang = DB::select("
            SELECT a.id

            FROM packing_packing_in a

            INNER JOIN fg_stok_bppb b
                ON b.id = a.fg_stok_bppb_id

            WHERE a.id_ppic_master_so IS NULL
            AND a.po = '$po'
            AND a.id_so_det = '$id_so_det'
            AND b.no_carton = '$no_carton'

            FOR UPDATE
        ");

        if (!$stok_gudang) {
            return [
                'icon' => 'salah',
                'msg' => 'Data tidak ada di stok gudang',
            ];
        }


        // =========================================================
        // CEK STOK
        // =========================================================
        //
        // Query ini dilakukan SETELAH lock. Semua bucket dipilih lewat
        // kolom NULL-nya, karena stok gudang tidak punya id_ppic.
        // =========================================================

        $cek_stok = DB::select("
            SELECT
                COALESCE((
                    SELECT SUM(qty)
                    FROM packing_packing_in
                    WHERE id_ppic_master_so IS NULL
                    AND id_so_det = '$id_so_det'
                ), 0)
                + COALESCE((
                    SELECT SUM(qty_switch)
                    FROM packing_central_switching
                    WHERE tujuan_ppic_master_so_id IS NULL
                    AND tujuan_so_det_id = '$id_so_det'
                ), 0)
                - COALESCE((
                    SELECT COUNT(*)
                    FROM packing_packing_out_scan
                    WHERE id_ppic IS NULL
                    AND id_so_det = '$id_so_det'
                ), 0)
                - COALESCE((
                    SELECT SUM(qty_switch)
                    FROM packing_central_switching
                    WHERE asal_ppic_master_so_id IS NULL
                    AND asal_so_det_id = '$id_so_det'
                ), 0)
                AS tot_s
        ");

        $cek_stok_fix = $cek_stok[0]->tot_s ?? 0;

        if ($cek_stok_fix < 1) {
            return [
                'icon' => 'salah',
                'msg' => 'Tidak Ada Stok',
            ];
        }

        // =========================================================
        // HITUNG TOTAL SUDAH OUT
        // =========================================================
        //
        // Qty carton diambil dari packing in yang menempel di carton
        // fg_stok_bppb, bukan dari packing list.
        // =========================================================

        $cek_qty_isi_karton = DB::select("
            SELECT
                a.qty,
                COALESCE(b.tot_input, 0) AS tot_input

            FROM
            (
                SELECT SUM(a.qty) AS qty

                FROM packing_packing_in a

                INNER JOIN fg_stok_bppb b
                    ON b.id = a.fg_stok_bppb_id

                WHERE a.id_ppic_master_so IS NULL
                AND a.po = '$po'
                AND a.id_so_det = '$id_so_det'
                AND b.no_carton = '$no_carton'
            ) a

            LEFT JOIN
            (
                SELECT COUNT(*) AS tot_input

                FROM packing_packing_out_scan

                WHERE po = '$po'
                AND dest = '$dest'
                AND no_carton = '$no_carton'
                AND id_so_det = '$id_so_det'
                AND id_ppic IS NULL
            ) b
                ON 1 = 1
        ");

        if (!$cek_qty_isi_karton || $cek_qty_isi_karton[0]->qty === null) {
            return [
                'icon' => 'salah',
                'msg' => 'Tidak Ada Data 2',
            ];
        }

        $cek_qty_isi = $cek_qty_isi_karton[0]->qty;
        $tot_out     = $cek_qty_isi_karton[0]->tot_input;

        // =========================================================
        // CEK QTY KARTON
        // =========================================================

        if ($cek_qty_isi > $tot_out) {

            // =====================================================
            // INSERT
            // =====================================================
            //
            // id_ppic sengaja NULL. Kalau diisi id PO asal, stok yang
            // berkurang jadi stok PO itu, padahal barangnya dari FGS.
            // =====================================================

            DB::insert("
                INSERT INTO packing_packing_out_scan
                (
                    tgl_trans,
                    barcode,
                    po,
                    dest,
                    no_carton,
                    notes,
                    created_by,
                    created_at,
                    updated_at,
                    id_so_det,
                    id_ppic
                )
                VALUES
                (
                    '$tgl_trans',
                    '$barcode',
                    '$po',
                    '$dest',
                    '$no_carton',
                    '-',
                    '$user',
                    '$timestamp',
                    '$timestamp',
                    '$id_so_det',
                    NULL
                )
            ");


            return [
                'icon' => 'benar',
                'msg' => 'Data berhasil Disimpan',
            ];
        }


        // =========================================================
        // QTY SUDAH PENUH
        // =========================================================

        if ($cek_qty_isi == $tot_out) {
            return [
                'icon' => 'lebih',
                'msg' => 'Data sudah melebihi qty karton',
            ];
        }


        return [
            'icon' => 'salah',
            'msg' => 'Tidak Ada Data 1',
        ];
    }

    public function packing_out_show_tot_input(Request $request)
    {
        $user       = Auth::user()->name;
        $tgl_trans = date('Y-m-d');
        $data_header = DB::select("
        SELECT count(barcode)tot_input
        from packing_packing_out_scan
        where created_by = '$user' and tgl_trans = '$tgl_trans'
        ");

        return json_encode($data_header ? $data_header[0] : '-');
    }

    public function packing_out_tot_barcode(Request $request)
    {
        $user = Auth::user()->name;
        $po    = $request->cbopo;
        $dest    = $request->dest;
        if ($request->ajax()) {


            $data_summary = DB::select("
            SELECT
            a.id,
            a.id_so_det,
            m.buyer,
            concat((DATE_FORMAT(a.tgl_shipment,  '%d')), '-', left(DATE_FORMAT(a.tgl_shipment,  '%M'),3),'-',DATE_FORMAT(a.tgl_shipment,  '%Y')
            ) tgl_shipment_fix,
            a.barcode,
            m.reff_no,
            a.po,
            a.dest,
            a.desc,
            m.ws,
            m.styleno,
            m.color,
            m.size,
            a.qty_po,
            coalesce(trf.qty_trf,0) qty_trf,
            coalesce(pck.qty_packing_in,0) qty_packing_in,
            coalesce(pck_out.qty_packing_out,0) qty_packing_out,
            coalesce(pck.qty_packing_in,0) - coalesce(pck_out.qty_packing_out,0) sisa,
            m.ws,
            a.created_by,
            a.created_at
            FROM ppic_master_so a
            inner join master_sb_ws m on a.id_so_det = m.id_so_det
            left join master_size_new msn on m.size = msn.size
            left join
            (
                select id_ppic_master_so, coalesce(sum(qty),0) qty_trf from packing_trf_garment group by id_ppic_master_so
            ) trf on trf.id_ppic_master_so = a.id
            left join
            (
                select id_ppic_master_so, coalesce(sum(qty),0) qty_packing_in from packing_packing_in group by id_ppic_master_so
            ) pck on pck.id_ppic_master_so = a.id
            left join
            (
            select p.id, qty_packing_out from
                (
                select count(barcode) qty_packing_out,po, barcode, dest from packing_packing_out_scan
                group by barcode, po, dest
                ) a
            inner join ppic_master_so p on a.barcode = p.barcode and a.po = p.po and a.dest = p.dest
            group by p.id
            ) pck_out on pck_out.id = a.id
            where a.po = '$po' and a.dest = '$dest'
            order by tgl_shipment desc, buyer asc, ws asc , msn.urutan asc
            ");

            return DataTables::of($data_summary)->toJson();
        }
    }



    public function export_excel_packing_out(Request $request)
    {

        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $po = $request->txtpo;
        $tujuan = $request->tujuan;

        if ($tujuan == 'Gudang Stok') {
            return response()->json($this->queryPackingOutGudangStok($tgl_awal, $tgl_akhir, $po));
        }

        if ($po == null or $po == '') {
            $po_text = '';
        } else {
            $po = $request->txtpo;
            $po_text = "where po = '$po'";
        }
        // return Excel::download(new ExportLaporanPackingOut($request->from, $request->to), 'Laporan_Hasil_Scan.xlsx');
        $data = DB::select("WITH
            o as (
                select
                count(*) as tot,
                tgl_trans,
                id_ppic,
                no_carton,
                max(created_by) as created_by,
                max(updated_at) as tgl_akt_input
                from packing_packing_out_scan
                where tgl_trans >= '$tgl_awal' and tgl_trans <=  '$tgl_akhir' and id_ppic is not null
                group by tgl_trans, no_carton, id_ppic
            ),

            sb as (
                select sd.id id_so_det, supplier as buyer, kpno, styleno, color, size,dest, reff_no
                from signalbit_erp.so_det sd
                inner join signalbit_erp.so on sd.id_so = so.id
                inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
                inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
                where sd.cancel = 'N' and so.cancel_h = 'N'
            )

            SELECT
                tot,
                tgl_trans_fix,
                tgl_akt_input,
                po,
                barcode,
                color,
                size,
                no_carton,
                ws,
                styleno,
                reff_no,
                dest,
                tgl_shipment,
                created_by
            FROM (
                select
                    o.tot ,
                    DATE_FORMAT(o.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
                    DATE_FORMAT(o.tgl_akt_input, '%d-%m-%Y %H:%i:%s') AS tgl_akt_input,
                    p.po,
                    p.barcode,
                    sb.color,
                    sb.size,
                    no_carton,
                    sb.kpno as ws,
                    sb.styleno,
                    sb.reff_no,
                    sb.dest,
                    DATE_FORMAT(p.tgl_shipment, '%d-%m-%Y') AS tgl_shipment,
                    o.created_by
                from o
                left join ppic_master_so p on o.id_ppic = p.id
                left join sb on p.id_so_det = sb.id_so_det
                $po_text

                UNION ALL

                select
                    SUM(COALESCE(pc_packing_scan, 0)) tot,
                    DATE_FORMAT(tgl_saldo, '%d-%m-%Y') tgl_trans_fix,
                    DATE_FORMAT(tgl_saldo, '%d-%m-%Y %H:%i:%s') AS tgl_akt_input,
                    '-' po,
                    '-' barcode,
                    color,
                    size,
                    '-' no_carton,
                    ws,
                    styleno,
                    styleno reff_no,
                    '-' dest,
                    DATE_FORMAT(tgl_saldo, '%d-%m-%Y') AS tgl_shipment,
                    'INJECT' created_by
                from
                    signalbit_erp.inject_mutasi_sewing
                where
                    tgl_saldo >= '$tgl_awal' and tgl_saldo <= '$tgl_akhir'
                    and pc_packing_scan > 0
                GROUP BY
                    ws,
                    color,
                    size
            ) packing_out
            $po_text
            order by
                tgl_trans_fix desc,
                po asc,
                no_carton asc
        ");

        return response()->json($data);
    }


    public function show_sum_max_carton(Request $request)
    {
        $po = $request->po_data ? $request->po_data : null;
        $no_carton_data_arr = $request->no_carton_data ? $request->no_carton_data : null;
        $cekArray = explode('_', $no_carton_data_arr);
        $no_carton = $cekArray[0];
        $notes = $cekArray[1];

        $data_kapasitas_karton = DB::select("SELECT a.*, coalesce(b.tot_out,0)tot_out from
(
select * from packing_master_carton
where po = '$po' and no_carton = '$no_carton' and notes = '$notes') a
left join
(
select count(barcode) tot_out, po, no_carton, notes from packing_packing_out_scan where po = '$po' and no_carton = '$no_carton ' and notes = '$notes'
group by po, no_carton, notes
) b on a.po = b.po and a.no_carton = b.no_carton and a.notes = b.notes
        ");

        return json_encode($data_kapasitas_karton ? $data_kapasitas_karton[0] : null);
    }

    public function create_packing_out_kirim_gudang_stok(){

        return view("packing.packing_out_kirim_gudang_stok", [
            "page" => "dashboard-packing",
            "subPageGroup" => "packing-packing-out",
            "subPage" => "packing-out"
        ]);
    }

    public function store_packing_out_kirim_gudang_stok(Request $request)
    {
        DB::beginTransaction();

        try {

            $no_trans = DB::selectOne("
                SELECT
                    CONCAT('PCK/OUT/', DATE_FORMAT(CURRENT_DATE(), '%Y')) AS Mattype,
                    IF(
                        MAX(no_trans) IS NULL,
                        '00001',
                        LPAD(MAX(RIGHT(no_trans, 5)) + 1, 5, 0)
                    ) AS nomor,
                    CONCAT(
                        'PCK/OUT/',
                        DATE_FORMAT(CURRENT_DATE(), '%m'),
                        DATE_FORMAT(CURRENT_DATE(), '%y'),
                        '/',
                        IF(
                            MAX(no_trans) IS NULL,
                            '00001',
                            LPAD(MAX(RIGHT(no_trans, 5)) + 1, 5, 0)
                        )
                    ) AS kode
                FROM packing_out_gudang_stok
                WHERE
                    MONTH(created_at) = MONTH(CURRENT_DATE())
                    AND YEAR(created_at) = YEAR(CURRENT_DATE())
                    AND LEFT(no_trans, 3) = 'PCK'
            ");

            $items = json_decode($request->items, true);

            foreach ($items as $item) {

                PackingOutGudangStok::create([
                    'no_trans'                => $no_trans->kode,
                    'no_karton'               => $item['no_karton'],
                    'lokasi_asal'             => strtoupper($item['lokasi_asal']),
                    'po'                      => $item['po'],
                    'ppic_master_so_id'       => $item['ppic_master_so_id'] ?: null,
                    'so_det_id'               => $item['so_det_id'],
                    'tujuan'                  => $request->tujuan,
                    'grade'                   => $request->grade,
                    'qty'                     => $item['qty'],
                    'created_by'              => auth()->user()->id,
                    'created_by_username'     => auth()->user()->username,
                    'created_at'              => date('Y-m-d H:i:s'),
                ]);

            }

            DB::commit();

            return array(
                "status" => 200,
                "message" => "Data Pengeluaran Packing ke Gudang Stok berhasil disimpan.",
                "additional" => [],
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return array(
                "status" => 500,
                "message" => "Terjadi kesalahan saat menyimpan data: " . $e->getMessage(),
                "additional" => [],
            );
        }
    }

    public function getpo_packing_out_kirim_gudang_stok(Request $request)
    {
        if ($request->lokasi_asal == 'Packing Central') {
            $data = DB::select("
                WITH a AS (

                SELECT
                    a.id_ppic_master_so,
                    a.id_so_det AS so_det_id,
                    SUM(a.qty) AS qty_pck_in
                from packing_packing_in a
                    LEFT JOIN laravel_nds.ppic_master_so p ON a.id_ppic_master_so = p.id
                    WHERE a.sumber IN ('Sewing','FGS', 'TEMPORARY PACKING')
                        AND (
                            a.id_ppic_master_so IS NULL
                            OR YEAR(p.tgl_shipment) >= 2026
                            OR p.po = 'HGL.CMT/X/2025/039/SGT/1025/165/BLACK'
                            OR p.po = '61297671'
                            OR p.po = '61297673'
                        )
                    group by id_ppic_master_so,a.id_so_det
                ),

                p AS (
                    SELECT
                        id_ppic,
                        id_so_det,
                        COUNT(*) AS qty_scan
                    FROM packing_packing_out_scan
                    WHERE id_so_det IS NOT NULL
                    GROUP BY
                        id_ppic,
                        id_so_det
                ),

                s AS (
                    SELECT
                        asal_ppic_master_so_id,
                        asal_so_det_id,
                        SUM(qty_switch) AS qty_switch
                    FROM packing_central_switching
                    GROUP BY
                        asal_ppic_master_so_id,
                        asal_so_det_id
                ),

                t AS (
                    SELECT
                        tujuan_ppic_master_so_id AS id_ppic_master_so,
                        tujuan_so_det_id AS so_det_id,
                        SUM(qty_switch) AS qty_switch_masuk
                    FROM packing_central_switching
                    GROUP BY
                        tujuan_ppic_master_so_id,
                        tujuan_so_det_id
                ),

                r AS (
                    SELECT
                        id_ppic_master_so,
                        id_so_det,
                        qty
                    FROM fg_fg_out
                    WHERE status = 'RETUR'
                    GROUP BY
                        id_ppic_master_so,
                        id_so_det
                ),

                g AS (
                    SELECT
                        ppic_master_so_id AS id_ppic_master_so,
                        so_det_id,
                        SUM(qty) AS qty_out_gudang
                    FROM packing_out_gudang_stok
                    WHERE lokasi_asal = 'PACKING CENTRAL'
                    GROUP BY
                        ppic_master_so_id,
                        so_det_id
                ),

                combined AS (
                    SELECT
                        id_ppic_master_so,
                        so_det_id,
                        qty_pck_in AS qty,
                        0 AS qty_scan,
                        0 AS qty_switch,
                        0 AS qty_switch_masuk,
                        0 AS qty_retur,
                        0 AS qty_out_gudang
                    FROM a

                    UNION ALL

                    SELECT
                        id_ppic AS id_ppic_master_so,
                        id_so_det AS so_det_id,
                        0 AS qty,
                        qty_scan,
                        0 AS qty_switch,
                        0 AS qty_switch_masuk,
                        0 AS qty_retur,
                        0 AS qty_out_gudang
                    FROM p

                    UNION ALL

                    SELECT
                        asal_ppic_master_so_id AS id_ppic_master_so,
                        asal_so_det_id AS so_det_id,
                        0 AS qty,
                        0 AS qty_scan,
                        qty_switch,
                        0 AS qty_switch_masuk,
                        0 AS qty_retur,
                        0 AS qty_out_gudang
                    FROM s

                    UNION ALL

                    SELECT
                        id_ppic_master_so,
                        so_det_id,
                        0 AS qty,
                        0 AS qty_scan,
                        0 AS qty_switch,
                        qty_switch_masuk,
                        0 AS qty_retur,
                        0 AS qty_out_gudang
                    FROM t

                    UNION ALL

                    SELECT
                        id_ppic_master_so,
                        id_so_det AS so_det_id,
                        0 AS qty,
                        0 AS qty_scan,
                        0 AS qty_switch,
                        0 AS qty_switch_masuk,
                        qty AS qty_retur,
                        0 AS qty_out_gudang
                    FROM r

                    UNION ALL

                    SELECT
                        id_ppic_master_so,
                        so_det_id,
                        0 AS qty,
                        0 AS qty_scan,
                        0 AS qty_switch,
                        0 AS qty_switch_masuk,
                        0 AS qty_retur,
                        qty_out_gudang
                    FROM g
                ),

                result AS (
                    SELECT
                        combined.id_ppic_master_so,
                        COALESCE(ppic_master_so.po, packing_packing_in.po) AS po,
                        combined.so_det_id,
                        SUM(combined.qty)
                            + SUM(combined.qty_retur)
                            + SUM(combined.qty_switch_masuk)
                            - SUM(combined.qty_scan)
                            - SUM(combined.qty_switch)
                            - SUM(combined.qty_out_gudang) AS qty_sisa
                    FROM combined
                    LEFT JOIN (
                        SELECT
                            id_ppic_master_so,
                            id_so_det,
                            MIN(id) AS id,
                            MAX(po) AS po,
                            MAX(tgl_penerimaan) AS tgl_penerimaan
                        FROM packing_packing_in
                        GROUP BY
                            id_ppic_master_so,
                            id_so_det
                    ) packing_packing_in
                        ON packing_packing_in.id_ppic_master_so <=> combined.id_ppic_master_so
                        AND packing_packing_in.id_so_det <=> combined.so_det_id
                    LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = combined.so_det_id
                    LEFT JOIN ppic_master_so ON ppic_master_so.id = combined.id_ppic_master_so
                    WHERE 1=1
                        AND (
                            combined.id_ppic_master_so IS NULL
                            OR YEAR(ppic_master_so.tgl_shipment) >= 2026
                            OR ppic_master_so.po = 'HGL.CMT/X/2025/039/SGT/1025/165/BLACK'
                            OR ppic_master_so.po = '61297671'
                            OR ppic_master_so.po = '61297673'
                        )
                    GROUP BY
                        combined.id_ppic_master_so,
                        combined.so_det_id,
                        COALESCE(ppic_master_so.po, packing_packing_in.po)
                )

                SELECT DISTINCT po
                FROM result
                WHERE qty_sisa >= 1 AND po LIKE '%$request->search%'
                ORDER BY po
            ");

        }else{

            $data = DB::select("
                SELECT
                    stok.po
                FROM (" . $this->sqlStokTemporaryPacking(false) . ") stok
                GROUP BY stok.po
                ORDER BY stok.po
            ");

        }

        return response()->json($data);
    }

    private function sqlStokPackingCentralPerPo(): string
    {
        return "
            SELECT
                stok.id_ppic_master_so,
                stok.id_so_det,
                stok.qty_sisa
            FROM (
                SELECT
                    pin.id_ppic_master_so,
                    pin.id_so_det,
                    (
                        pin.qty_in +
                        COALESCE(retur.qty, 0) +
                        COALESCE(switch_in.qty, 0) -
                        COALESCE(switch_out.qty, 0) -
                        COALESCE(scan.qty_scan, 0) -
                        COALESCE(out_gudang_stok.qty, 0)
                    ) AS qty_sisa
                FROM (
                    SELECT
                        id_ppic_master_so,
                        id_so_det,
                        SUM(qty) AS qty_in
                    FROM packing_packing_in
                    WHERE sumber IN ('Sewing', 'FGS', 'TEMPORARY PACKING')
                        AND po = ?
                    GROUP BY id_ppic_master_so, id_so_det
                ) pin
                LEFT JOIN (
                    SELECT
                        id_ppic_master_so,
                        id_so_det,
                        qty
                    FROM fg_fg_out
                    WHERE status = 'RETUR'
                    GROUP BY id_ppic_master_so, id_so_det
                ) retur
                    ON retur.id_so_det = pin.id_so_det
                    AND retur.id_ppic_master_so = pin.id_ppic_master_so
                LEFT JOIN (
                    SELECT
                        asal_ppic_master_so_id,
                        asal_so_det_id,
                        SUM(qty_switch) AS qty
                    FROM packing_central_switching
                    GROUP BY asal_ppic_master_so_id, asal_so_det_id
                ) switch_out
                    ON switch_out.asal_so_det_id = pin.id_so_det
                    AND switch_out.asal_ppic_master_so_id = pin.id_ppic_master_so
                LEFT JOIN (
                    SELECT
                        tujuan_ppic_master_so_id,
                        tujuan_so_det_id,
                        SUM(qty_switch) AS qty
                    FROM packing_central_switching
                    GROUP BY tujuan_ppic_master_so_id, tujuan_so_det_id
                ) switch_in
                    ON switch_in.tujuan_so_det_id = pin.id_so_det
                    AND switch_in.tujuan_ppic_master_so_id = pin.id_ppic_master_so
                LEFT JOIN (
                    SELECT
                        id_ppic,
                        id_so_det,
                        COUNT(*) AS qty_scan
                    FROM packing_packing_out_scan
                    WHERE id_so_det IS NOT NULL
                    GROUP BY id_ppic, id_so_det
                ) scan
                    ON scan.id_so_det = pin.id_so_det
                    AND scan.id_ppic = pin.id_ppic_master_so
                LEFT JOIN (
                    SELECT
                        ppic_master_so_id,
                        so_det_id,
                        SUM(qty) AS qty
                    FROM packing_out_gudang_stok
                    WHERE lokasi_asal = 'PACKING CENTRAL'
                    GROUP BY ppic_master_so_id, so_det_id
                ) out_gudang_stok
                    ON out_gudang_stok.so_det_id = pin.id_so_det
                    AND out_gudang_stok.ppic_master_so_id = pin.id_ppic_master_so
            ) stok
            WHERE stok.qty_sisa >= 1
        ";
    }

    private function sqlStokTemporaryPacking(bool $perPo = true): string
    {
        $filterPo = $perPo ? 'AND stok.po = ?' : '';

        return "
            SELECT
                stok.po,
                stok.id_ppic_master_so,
                stok.id_so_det,
                stok.qty
                    - COALESCE(out_gudang_stok.qty, 0)
                    - COALESCE(out_temporary.qty, 0) AS qty_sisa
            FROM packing_trf_garment stok
            LEFT JOIN (
                SELECT
                    po,
                    so_det_id,
                    SUM(qty) AS qty
                FROM packing_out_gudang_stok
                WHERE lokasi_asal = 'TEMPORARY PACKING'
                GROUP BY po, so_det_id
            ) out_gudang_stok
                ON out_gudang_stok.so_det_id = stok.id_so_det
                AND out_gudang_stok.po = stok.po
            LEFT JOIN (
                SELECT
                    po,
                    id_so_det,
                    SUM(qty) AS qty
                FROM packing_trf_garment_out_temporary
                GROUP BY po, id_so_det
            ) out_temporary
                ON out_temporary.id_so_det = stok.id_so_det
                AND out_temporary.po = stok.po
            WHERE stok.tujuan = 'TEMPORARY PACKING'
            {$filterPo}
            GROUP BY
                stok.po,
                stok.id_so_det
            HAVING qty_sisa >= 1
        ";
    }

    public function getws_packing_out_kirim_gudang_stok(Request $request)
    {
        $lokasi_asal = $request->lokasi_asal;
        $po = $request->po;

        if ($lokasi_asal == 'Packing Central') {

            $data = DB::select("
                SELECT
                    master_sb_ws.ws,
                    SUM(stok.qty_sisa) AS qty
                FROM (" . $this->sqlStokPackingCentralPerPo() . ") stok
                INNER JOIN master_sb_ws ON master_sb_ws.id_so_det = stok.id_so_det
                GROUP BY master_sb_ws.ws
                ORDER BY master_sb_ws.ws
            ", [$po]);

        }else{

            $data = DB::select("
                SELECT
                    master_sb_ws.ws,
                    SUM(stok.qty_sisa) AS qty
                FROM (" . $this->sqlStokTemporaryPacking() . ") stok
                INNER JOIN master_sb_ws ON master_sb_ws.id_so_det = stok.id_so_det
                GROUP BY master_sb_ws.ws
                ORDER BY master_sb_ws.ws
            ", [$po]);

        }

        return response()->json($data);
    }

    public function getstyle_packing_out_kirim_gudang_stok(Request $request)
    {
        $lokasi_asal = $request->lokasi_asal;
        $po = $request->po;
        $ws = $request->ws;

        if ($lokasi_asal == 'Packing Central') {

            $data = DB::select("
                SELECT
                    master_sb_ws.styleno AS style,
                    SUM(stok.qty_sisa) AS qty
                FROM (" . $this->sqlStokPackingCentralPerPo() . ") stok
                INNER JOIN master_sb_ws ON master_sb_ws.id_so_det = stok.id_so_det
                WHERE master_sb_ws.ws = ?
                GROUP BY master_sb_ws.styleno
                ORDER BY master_sb_ws.styleno
            ", [$po, $ws]);

        }else {

            $data = DB::select("
                SELECT
                    master_sb_ws.styleno AS style,
                    SUM(stok.qty_sisa) AS qty
                FROM (" . $this->sqlStokTemporaryPacking() . ") stok
                INNER JOIN master_sb_ws ON master_sb_ws.id_so_det = stok.id_so_det
                WHERE master_sb_ws.ws = ?
                GROUP BY master_sb_ws.styleno
                ORDER BY master_sb_ws.styleno
            ", [$po, $ws]);

        }

        return response()->json($data);
    }

    public function getcolor_packing_out_kirim_gudang_stok(Request $request)
    {
        $lokasi_asal = $request->lokasi_asal;
        $po = $request->po;
        $ws = $request->ws;
        $style = $request->style;

        if ($lokasi_asal == 'Packing Central') {

            $data = DB::select("
                SELECT
                    master_sb_ws.color,
                    SUM(stok.qty_sisa) AS qty
                FROM (" . $this->sqlStokPackingCentralPerPo() . ") stok
                INNER JOIN master_sb_ws ON master_sb_ws.id_so_det = stok.id_so_det
                WHERE master_sb_ws.ws = ?
                    AND master_sb_ws.styleno = ?
                GROUP BY master_sb_ws.color
                ORDER BY master_sb_ws.color
            ", [$po, $ws, $style]);

        }else{

            $data = DB::select("
                SELECT
                    master_sb_ws.color,
                    SUM(stok.qty_sisa) AS qty
                FROM (" . $this->sqlStokTemporaryPacking() . ") stok
                INNER JOIN master_sb_ws ON master_sb_ws.id_so_det = stok.id_so_det
                WHERE master_sb_ws.ws = ?
                    AND master_sb_ws.styleno = ?
                GROUP BY master_sb_ws.color
                ORDER BY master_sb_ws.color
            ", [$po, $ws, $style]);

        }

        return response()->json($data);
    }

    public function getsize_packing_out_kirim_gudang_stok(Request $request)
    {
        $lokasi_asal = $request->lokasi_asal;
        $po = $request->po;
        $ws = $request->ws;
        $style = $request->style;
        $color = $request->color;

        if ($lokasi_asal == 'Packing Central') {

            $data = DB::select("
                SELECT
                    master_sb_ws.size,
                    master_sb_ws.id_so_det,
                    stok.id_ppic_master_so,
                    SUM(stok.qty_sisa) AS qty
                FROM (" . $this->sqlStokPackingCentralPerPo() . ") stok
                INNER JOIN master_sb_ws ON master_sb_ws.id_so_det = stok.id_so_det
                WHERE master_sb_ws.ws = ?
                    AND master_sb_ws.styleno = ?
                    AND master_sb_ws.color = ?
                GROUP BY
                    master_sb_ws.size,
                    master_sb_ws.id_so_det,
                    stok.id_ppic_master_so
                ORDER BY master_sb_ws.size
            ", [$po, $ws, $style, $color]);

        }else{

            $data = DB::select("
                SELECT
                    master_sb_ws.size,
                    master_sb_ws.id_so_det,
                    stok.id_ppic_master_so,
                    SUM(stok.qty_sisa) AS qty
                FROM (" . $this->sqlStokTemporaryPacking() . ") stok
                INNER JOIN master_sb_ws ON master_sb_ws.id_so_det = stok.id_so_det
                WHERE master_sb_ws.ws = ?
                    AND master_sb_ws.styleno = ?
                    AND master_sb_ws.color = ?
                GROUP BY
                    master_sb_ws.size,
                    master_sb_ws.id_so_det,
                    stok.id_ppic_master_so
                ORDER BY master_sb_ws.size
            ", [$po, $ws, $style, $color]);

        }

        return response()->json($data);
    }

    public function getqty_packing_out_kirim_gudang_stok(Request $request)
    {
        $lokasi_asal = $request->lokasi_asal;
        $po = $request->po;
        $ws = $request->ws;
        $style = $request->style;
        $color = $request->color;
        $size = $request->size;
        $so_det_id = $request->so_det_id;

        if ($lokasi_asal == 'Packing Central') {

            $data = DB::select("
                SELECT
                    COALESCE(SUM(stok.qty_sisa), 0) AS qty
                FROM (" . $this->sqlStokPackingCentralPerPo() . ") stok
                INNER JOIN master_sb_ws ON master_sb_ws.id_so_det = stok.id_so_det
                WHERE master_sb_ws.ws = ?
                    AND master_sb_ws.styleno = ?
                    AND master_sb_ws.color = ?
                    AND master_sb_ws.size = ?
                    AND (? IS NULL OR stok.id_so_det = ?)
            ", [$po, $ws, $style, $color, $size, $so_det_id, $so_det_id]);

        }else{

            $data = DB::select("
                SELECT
                    COALESCE(SUM(stok.qty_sisa), 0) AS qty
                FROM (" . $this->sqlStokTemporaryPacking() . ") stok
                INNER JOIN master_sb_ws ON master_sb_ws.id_so_det = stok.id_so_det
                WHERE master_sb_ws.ws = ?
                    AND master_sb_ws.styleno = ?
                    AND master_sb_ws.color = ?
                    AND master_sb_ws.size = ?
                    AND (? IS NULL OR stok.id_so_det = ?)
            ", [$po, $ws, $style, $color, $size, $so_det_id, $so_det_id]);

        }

        return response()->json([
            'qty' => $data[0]->qty ?? 0
        ]);
    }
}
