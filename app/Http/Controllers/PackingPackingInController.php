<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanPackingIn;
use App\Models\PackingCentralSwitching;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class PackingPackingInController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_input = DB::select("
            select
            a.no_trans,
            concat((DATE_FORMAT(a.tgl_penerimaan,  '%d')), '-', left(DATE_FORMAT(a.tgl_penerimaan,  '%M'),3),'-',DATE_FORMAT(a.tgl_penerimaan,  '%Y')
            ) tgl_penerimaan_fix,
            b.no_trans no_trf_garment,
            b.line,
            p.barcode,
            p.po,
            p.dest,
            a.qty,
            m.ws,
            m.styleno,
            m.color,
            m.size,
            a.created_at,
            a.created_by
            from packing_packing_in a
            left join packing_trf_garment b on a.id_trf_garment = b.id
            left join ppic_master_so p on a.id_ppic_master_so = p.id
            left join master_sb_ws m on p.id_so_det = m.id_so_det
                where a.tgl_penerimaan >= '$tgl_awal' and a.tgl_penerimaan <= '$tgl_akhir' AND sumber IN ('Sewing', 'Switching')
            union
            select
            a.no_trans,
            concat((DATE_FORMAT(a.tgl_penerimaan,  '%d')), '-', left(DATE_FORMAT(a.tgl_penerimaan,  '%M'),3),'-',DATE_FORMAT(a.tgl_penerimaan,  '%Y')
            ) tgl_penerimaan_fix,
            b.no_trans no_trf_garment,
            'Temporary' line,
            p.barcode,
            p.po,
            p.dest,
            a.qty,
            m.ws,
            m.styleno,
            m.color,
            m.size,
            a.created_at,
            a.created_by
            from packing_packing_in a
            inner join packing_trf_garment_out_temporary b on a.id_trf_garment = b.id
            inner join ppic_master_so p on a.id_ppic_master_so = p.id
            inner join master_sb_ws m on p.id_so_det = m.id_so_det
            where a.tgl_penerimaan >= '$tgl_awal' and a.tgl_penerimaan <= '$tgl_akhir' and sumber = 'Temporary' and a.line = 'Temporary'
            union
            select
            a.no_trans,
            concat((DATE_FORMAT(a.tgl_penerimaan,  '%d')), '-', left(DATE_FORMAT(a.tgl_penerimaan,  '%M'),3),'-',DATE_FORMAT(a.tgl_penerimaan,  '%Y')
            ) tgl_penerimaan_fix,
            b.no_trans_out no_trf_garment,
            'FGS' line,
            a.barcode,
            a.po,
            a.dest,
            a.qty,
            m.ws,
            m.styleno,
            m.color,
            m.size,
            a.created_at,
            a.created_by
            from packing_packing_in a
            inner join fg_stok_bppb b on a.fg_stok_bppb_id = b.id
            inner join master_sb_ws m on a.id_so_det = m.id_so_det
            where a.tgl_penerimaan >= '$tgl_awal' and a.tgl_penerimaan <= '$tgl_akhir' and sumber = 'FGS' and a.line = 'FGS'
            order by created_at desc

            ");

            return DataTables::of($data_input)->toJson();
        }

        $data_no_trans = DB::select("
        select data_cek.no_trans isi , data_cek.no_trans tampil
        from
            (
            SELECT
            a.id,
            a.no_trans,
            a.qty,
            b.qty_in
            from packing_trf_garment a
            left join
                (
                select id_trf_garment,sum(qty) qty_in from packing_packing_in
                group by id_trf_garment
                ) b on a.id = b.id_trf_garment
                 where a.tujuan = 'Packing'
            having a.qty - coalesce(b.qty_in,0) > '0'
            union
            SELECT
            a.id,
            a.no_trans,
            a.qty,
            b.qty_in
            from packing_trf_garment_out_temporary a
		    left join
                (
                select id_trf_garment,sum(qty) qty_in from packing_packing_in
                group by id_trf_garment
                ) b on a.id = b.id_trf_garment
            having a.qty - coalesce(b.qty_in,0) > '0'
            union
            SELECT
            a.id,
            a.no_trans_out as no_trans,
            a.qty_out as qty,
            b.qty_in
            from fg_stok_bppb a
		    left join
                (
                select fg_stok_bppb_id,sum(qty) qty_in from packing_packing_in
                group by fg_stok_bppb_id
                ) b on a.id = b.fg_stok_bppb_id
            where tujuan = 'PACKING CENTRAL'
            having a.qty_out - coalesce(b.qty_in,0) > '0'
            ) data_cek
            group by data_cek.no_trans
            order by id desc, no_trans asc
        ");
        return view(
            'packing.packing_in',
            [
                'page' => 'dashboard-packing',
                "subPageGroup" => "packing-packing-in",
                "subPage" => "packing-in",
                "data_no_trans" => $data_no_trans
            ]
        );
    }


    public function show_preview_packing_in(Request $request)
    {
        $user = Auth::user()->name;

        // $tahun = date('Y');
        // $no = date('my');
        // $kode = 'PO/FGS/OUT/';
        // $cek_nomor = DB::select("
        // select max(right(po,5))nomor from packing_packing_in where year(tgl_penerimaan) = '" . $tahun . "' and sumber = 'FGS'
        // ");
        // $nomor_tr = $cek_nomor[0]->nomor;
        // $urutan = (int)($nomor_tr);
        // $urutan++;
        // $kodepay = sprintf("%05s", $urutan);

        // $kode_trans = $kode . $no . '/' . $kodepay;

        if ($request->ajax()) {

            $data_preview = DB::select("
            SELECT
            a.id,
            a.line,
			a.qty,
            b.qty_in,
			m.ws,
			m.color,
			m.size,
			p.barcode,
			p.dest,
		    p.po,
            'PCS' unit
            from packing_trf_garment a
            left join
                (
                select id_trf_garment,sum(qty) qty_in from packing_packing_in where sumber != 'Temporary'
                group by id_trf_garment
                ) b on a.id = b.id_trf_garment
						inner join ppic_master_so  p on a.id_ppic_master_so = p.id
						inner join master_sb_ws m on p.id_so_det = m.id_so_det
						where a.no_trans = '" . $request->cbono . "'
            having a.qty - coalesce(b.qty_in,0) != '0'
			union
            SELECT
            a.id,
            'Temporary' line,
			a.qty,
            b.qty_in,
			m.ws,
			m.color,
			m.size,
			p.barcode,
			p.dest,
			p.po,
            'PCS' unit
            from packing_trf_garment_out_temporary a
            left join
                (
                select id_trf_garment,sum(qty) qty_in from packing_packing_in where sumber = 'Temporary'
                group by id_trf_garment
                ) b on a.id = b.id_trf_garment
						inner join ppic_master_so  p on a.id_ppic_master_so = p.id
						inner join master_sb_ws m on p.id_so_det = m.id_so_det
						where a.no_trans = '" . $request->cbono . "'
            having a.qty - coalesce(b.qty_in,0) != '0'
            union
            SELECT
            a.id,
            'FGS' line,
			a.qty_out - COALESCE(b.qty_in, 0) AS qty,
            b.qty_in,
			m.ws,
			m.color,
			m.size,
			ppic_master_so.barcode,
			'-' dest,
			'GUDANG STOK' po,
            'PCS' unit
            from fg_stok_bppb a
            left join ppic_master_so ON ppic_master_so.id_so_det = a.id_so_det
            left join
                (
                select fg_stok_bppb_id,sum(qty) qty_in from packing_packing_in where sumber = 'FGS'
                group by fg_stok_bppb_id
                ) b on a.id = b.fg_stok_bppb_id
            inner join master_sb_ws m on a.id_so_det = m.id_so_det
            where a.no_trans_out = '" . $request->cbono . "'
            HAVING qty != 0
            ");

            return DataTables::of($data_preview)->toJson();
        }
    }


    public function create(Request $request)
    {
        $user = Auth::user()->name;

        $data_line = DB::connection('mysql_sb')->select("SELECT username isi, username tampil from userpassword where groupp = 'sewing' order by isi asc");

        return view('packing.create_packing_transfer_garment', [
            'page' => 'dashboard-packing',
            "subPageGroup" => "packing-transfer-garment",
            "subPage" => "transfer-garment",
            "data_line" => $data_line,
            "user" => $user
        ]);
    }

    public function get_po(Request $request)
    {
        $data_po = DB::select("
        SELECT p.po isi,p.po tampil FROM output_rfts_packing a
        left join ppic_master_so p on a.so_det_id = p.id_so_det
        left join master_sb_ws m on a.so_det_id = m.id_so_det
        where sewing_line = '" . $request->cbo_line . "'
        group by po
        having po is not null
        order by po asc");

        $html = "<option value=''>Pilih PO</option>";

        foreach ($data_po as $datapo) {
            $html .= " <option value='" . $datapo->isi . "'>" . $datapo->tampil . "</option> ";
        }

        return $html;
    }

    public function get_garment(Request $request)
    {
        $data_garment = DB::select("
        SELECT p.id isi,
        concat(m.ws, ' - ', m.color, ' - ', m.size, ' => ', count(so_det_id) - coalesce(tmp.tot_tmp,0) - coalesce(ptg.tot_in,0) , ' PCS' ) tampil
        FROM output_rfts_packing a
        left join ppic_master_so p on a.so_det_id = p.id_so_det
        left join master_sb_ws m on a.so_det_id = m.id_so_det
        left join
            (
                select sum(qty_tmp_trf_garment) tot_tmp,id_ppic_master_so from packing_trf_garment_tmp
            ) tmp on p.id = tmp.id_ppic_master_so
        left join
            (
                select sum(qty) tot_in,id_ppic_master_so from packing_trf_garment
            ) ptg on p.id = ptg.id_ppic_master_so
        where sewing_line = '" . $request->cbo_line . "' and p.po = '" . $request->cbo_po . "'
        group by a.so_det_id, p.po, p.barcode
        having po is not null");

        $html = "<option value=''>Pilih Garment</option>";

        foreach ($data_garment as $datagarment) {
            $html .= " <option value='" . $datagarment->isi . "'>" . $datagarment->tampil . "</option> ";
        }

        return $html;
    }

    public function store(Request $request)
    {
        $user = Auth::user()->name;

        $lockKey = 'packing_packing_in_store_' . $user;
        if (!Cache::add($lockKey, true, 5)) {
            return array(
                "status" => 300,
                "message" => 'Data sebelumnya masih diproses, mohon tunggu beberapa detik lalu coba lagi.',
                "additional" => [],
            );
        }

        try {
            return $this->storePackingIn($request, $user);
        } finally {
            Cache::forget($lockKey);
        }
    }

    private function storePackingIn(Request $request, string $user)
    {
        $timestamp = Carbon::now();
        $JmlArray               = $_POST['txtqty'];
        $id_trf_garmentArray    = $_POST['id_trf_garment'];
        $po_fgsArray            = $_POST['po_fgs'];
        $status              = implode(',', $_POST['status']);
        $tgl_penerimaan = date('Y-m-d');
        $isTemporary = ($status == 'Temporary');
        $sourceTable = $isTemporary ? 'packing_trf_garment_out_temporary' : 'packing_trf_garment';
        $sumber = $isTemporary ? 'Temporary' : 'Sewing';
        $sumberFilter = $isTemporary ? "sumber = 'Temporary'" : "sumber != 'Temporary'";

        // Acquire a MySQL server-side named lock per id_trf_garment so that
        // two devices/users submitting the same source transaction can never
        // run the "check sisa qty then insert" section concurrently. This
        // does not depend on the app cache driver (CACHE_DRIVER=file here,
        // whose Cache::add() is a check-then-act pattern and not reliably
        // atomic across processes) or on transaction/row-lock timing.
        $lockNames = collect($id_trf_garmentArray)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->unique()
            ->sort()
            ->values()
            ->map(fn ($id) => 'packing_in_trf_' . $id);

        $acquiredLocks = [];
        foreach ($lockNames as $lockName) {
            $locked = DB::selectOne('select GET_LOCK(?, ?) as locked', [$lockName, 10]);
            if ((int) $locked->locked !== 1) {
                foreach ($acquiredLocks as $acquired) {
                    DB::select('select RELEASE_LOCK(?)', [$acquired]);
                }
                return array(
                    "status" => 200,
                    "message" => 'Item ini sedang diproses oleh user lain, silakan coba lagi beberapa saat.',
                    "additional" => [],
                );
            }
            $acquiredLocks[] = $lockName;
        }

        try {
            $kode_trans = DB::transaction(function () use (
                $JmlArray,
                $id_trf_garmentArray,
                $tgl_penerimaan,
                $timestamp,
                $user,
                $isTemporary,
                $sourceTable,
                $sumber,
                $sumberFilter,
                $po_fgsArray
            ) {
                // $tahun = date('Y', strtotime($tgl_penerimaan));
                // $no = date('my', strtotime($tgl_penerimaan));
                // $kode = 'PCK/IN/';
                // $cek_nomor = DB::select("
                // select max(cast(SUBSTR(no_trans,13,5) as int))nomor from packing_packing_in where year(tgl_penerimaan) = '" . $tahun . "'
                // ");
                // $nomor_tr = $cek_nomor[0]->nomor;
                // $urutan = (int)($nomor_tr);
                // $urutan++;
                // $kode_cek = $urutan++;
                // $kodepay = sprintf("%05s", $kode_cek);

                // $kode_trans = $kode . $no . '/' . $kodepay;

                $tahun = date('Y', strtotime($tgl_penerimaan));
                $no = date('my', strtotime($tgl_penerimaan));
                $kode = 'PCK/IN/';

                $cek_nomor = DB::select("
                    SELECT MAX(CAST(SUBSTR(no_trans, 13, 5) AS UNSIGNED)) AS nomor
                    FROM packing_packing_in
                    WHERE YEAR(tgl_penerimaan) = '$tahun'
                ");

                $nomor_tr = $cek_nomor[0]->nomor ?? 0;
                $urutan = (int) $nomor_tr;
                $urutan++;

                $kodepay = sprintf("%05d", $urutan);

                $kode_trans = $kode . $no . '/' . $kodepay;

                $insertedAny = false;

                foreach ($JmlArray as $key => $value) {
                    if ($value == '0' || $value == '') {
                        continue;
                    }

                    $txtqty = (float) $JmlArray[$key];

                    $txtid_trf_garment = $id_trf_garmentArray[$key];
                    // Row-lock the source transfer so concurrent submissions
                    // (different user/device, same no_trans) queue up instead
                    // of both reading a stale "sisa qty".

                    $status = $_POST['status'][$key] ?? '';

                    if($status == 'FGS'){
                        $cek = DB::select("select fg_stok_bppb.*, ppic_master_so.barcode, ppic_master_so.dest from fg_stok_bppb 
                            left join ppic_master_so on ppic_master_so.id_so_det = fg_stok_bppb.id_so_det 
                            where fg_stok_bppb.id = ? for update", [$txtid_trf_garment]);
                        if (empty($cek)) {
                            continue;
                        }
                        $cek = $cek[0];

                        $qtyInRow = DB::selectOne("
                            select coalesce(sum(qty),0) qty_in from packing_packing_in
                            where fg_stok_bppb_id = ? and sumber = 'FGS'
                        ", [$txtid_trf_garment]);
                        $sisa = (float) $cek->qty_out - (float) $qtyInRow->qty_in;
    
                        if ($txtqty > $sisa) {
                            throw new \RuntimeException(
                                'Qty untuk No. Transaksi sumber tidak lagi mencukupi (sisa: ' . $sisa . '). '
                                . 'Kemungkinan data sudah diinput oleh user lain, silakan refresh dan coba lagi.'
                            );
                        }
    
                        $id_ppic_master_so = null;
                        $id_so_det = $cek->id_so_det;
                        $line = 'FGS';
                        $po = array_values($po_fgsArray)[0];
                        $barcode = $cek->barcode;
                        $dest = $cek->dest;

                        DB::insert("
                        insert into packing_packing_in
                        (id_trf_garment,fg_stok_bppb_id,no_trans,tgl_penerimaan,id_ppic_master_so,id_so_det,qty,line,po,barcode,dest,sumber,created_by,created_at,updated_at)
                        values(null,'$txtid_trf_garment','$kode_trans','$tgl_penerimaan',null,'$id_so_det','$txtqty','$line','$po','$barcode','$dest','FGS','$user','$timestamp','$timestamp')");
                    }else{
                        $cek = DB::select("select * from $sourceTable where id = ? for update", [$txtid_trf_garment]);
                        if (empty($cek)) {
                            continue;
                        }
                        $cek = $cek[0];
    
                        $qtyInRow = DB::selectOne("
                            select coalesce(sum(qty),0) qty_in from packing_packing_in
                            where id_trf_garment = ? and $sumberFilter
                        ", [$txtid_trf_garment]);
                        $sisa = (float) $cek->qty - (float) $qtyInRow->qty_in;
    
                        if ($txtqty > $sisa) {
                            throw new \RuntimeException(
                                'Qty untuk No. Transaksi sumber tidak lagi mencukupi (sisa: ' . $sisa . '). '
                                . 'Kemungkinan data sudah diinput oleh user lain, silakan refresh dan coba lagi.'
                            );
                        }
    
                        $id_ppic_master_so = $cek->id_ppic_master_so;
                        $id_so_det = $cek->id_so_det;
                        $line = $isTemporary ? 'Temporary' : $cek->line;
                        $po = $cek->po;
                        $barcode = $cek->barcode;
                        $dest = $cek->dest;

                        DB::insert("
                        insert into packing_packing_in
                        (id_trf_garment,no_trans,tgl_penerimaan,id_ppic_master_so,id_so_det,qty,line,po,barcode,dest,sumber,created_by,created_at,updated_at)
                        values('$txtid_trf_garment','$kode_trans','$tgl_penerimaan','$id_ppic_master_so','$id_so_det','$txtqty','$line','$po','$barcode','$dest','$sumber','$user','$timestamp','$timestamp')");
                    }

                    $insertedAny = true;
                }

                if (!$insertedAny) {
                    throw new \RuntimeException('Tidak ada Data');
                }

                return $kode_trans;
            });
        } catch (\RuntimeException $e) {
            return array(
                "status" => 400,
                "message" => $e->getMessage(),
                "additional" => [],
            );
        } finally {
            foreach ($acquiredLocks as $lockName) {
                DB::select('select RELEASE_LOCK(?)', [$lockName]);
            }
        }

        return array(
            "status" => 900,
            "message" => 'No Transaksi :
             ' . $kode_trans . '
             Sudah Terbuat',
            "additional" => [],
        );
    }

    public function undo(Request $request)
    {
        $user = Auth::user()->name;

        $undo =  DB::delete(
            "DELETE FROM packing_trf_garment_tmp where created_by = '$user'"
        );

        if ($undo) {
            return array(
                'icon' => 'benar',
                'msg' => 'Data berhasil diundo',
            );
        } else {
            return array(
                'icon' => 'salah',
                'msg' => 'Tidak ada yang diundo',
            );
        }
    }

    public function reset(Request $request)
    {
        $user = Auth::user()->name;

        $undo =  DB::delete(
            "DELETE FROM packing_trf_garment_tmp where created_by = '$user'"
        );
    }


    // public function gettipe_garment(Request $request)
    // {
    //     // $data_ws = DB::connection('mysql_sb')->select("
    //     //     select so_det_id isi,
    //     //         concat(ac.kpno,' - ', ac.styleno,' - ', sd.color,' - ', sd.size, ' - > ',count(so_det_id)) tampil
    //     //     from output_rfts_packing a
    //     //         inner join master_plan mp on a.master_plan_id = mp.id
    //     //         inner join act_costing ac on mp.id_ws = ac.id
    //     //         inner join so_det sd on a.so_det_id = sd.id
    //     //         left join master_size_new msn on sd.size = msn.size
    //     //     where sewing_line = '" . $request->cbo_line . "'
    //     //     group by so_det_id
    //     //     having count(so_det_id) != '0'
    //     //     order by ac.kpno asc, sd.color asc, styleno asc, msn.urutan asc
    //     // ");

    //     $data_ws = PPICMasterSo::all();

    //     $html = "<option value=''>Pilih Garment</option>";

    //     foreach ($data_ws as $dataws) {
    //         if ($dataws->outputPacking) {
    //             // $res = $dataws->outputPacking->ppicOutput($request->cbo_line)->get();
    //             $res = $dataws->outputPacking->ppicOutput($request->cbo_line);

    //             foreach ($res as $r) {
    //                 $html .= " <option value='" . $r->isi . "'>" . $dataws->po . " - " . $r->tampil . "</option> ";
    //             }
    //         }
    //     }

    //     return $html;
    // }



    public function export_excel_packing_in(Request $request)
    {
        return Excel::download(new ExportLaporanPackingIn($request->from, $request->to), 'Laporan_Packing_In.xlsx');
    }
}
