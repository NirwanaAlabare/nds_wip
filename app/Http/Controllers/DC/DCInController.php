<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\SecondaryInhouse;
use App\Models\Stocker;
use App\Models\Trolley;
use App\Models\TrolleyStocker;

use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use DB;

class DCInController extends Controller
{
    public function index(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');

        $data_rak = DB::select("select nama_detail_rak isi, nama_detail_rak tampil from rack_detail");
        // dd($data_rak);
        if ($request->ajax()) {
            $additionalQuery = '';

            if ($request->dateFrom) {
                $additionalQuery .= " and a.tgl_trans >= '" . $request->dateFrom . "' ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and a.tgl_trans <= '" . $request->dateTo . "' ";
            }

            $keywordQuery = '';
            if ($request->search['value']) {
                $keywordQuery = "
                    (
                        line like '%" . $request->search['value'] . "%'
                    )
                ";
            }

            $data_input = DB::select("
                SELECT
                    UPPER(a.id_qr_stocker) id_qr_stocker,
                    DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
                    a.tgl_trans,
                    s.act_costing_ws,
                    s.color,
                    p.buyer,
                    p.style,
                    a.qty_awal,
                    a.qty_reject,
                    a.qty_replace,
                    (a.qty_awal - a.qty_reject + a.qty_replace) qty_in,
                    a.tujuan,
                    a.lokasi,
                    a.tempat,
                    a.created_at,
                    a.user,
                    f.no_cut,
                    msb.size,
                    mp.nama_part
                from
                    dc_in_input a
                    inner join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
                    inner join master_sb_ws msb on msb.so_det_id = s.so_det_id
                    inner join form_cut_input f on f.id = s.form_cut_id
                    inner join part_detail pd on s.part_detail_id = pd.id
                    inner join part p on pd.part_id = p.id
                    inner join master_part mp on mp.id = pd.master_part_id
                    ".$additionalQuery."
                order by
                    a.tgl_trans desc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view('dc.dc-in.dc-in', ['page' => 'dashboard-dc', "subPageGroup" => "dcin-dc", "subPage" => "dc-in", "data_rak" => $data_rak], ['tgl_skrg' => $tgl_skrg]);
    }

    public function show_data_header(Request $request)
    {
        $data_header = DB::select("
            SELECT
                a.act_costing_ws,
                m.buyer,
                m.style styleno,
                a.color,
                a.size,
                a.panel,
                f.no_cut,
                f.id,
                a.shade,
                a.qty_ply,
                a.range_awal,
                a.range_akhir,
                concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) kode,
                ms.tujuan,
                IF(ms.tujuan = 'NON SECONDARY',a.lokasi,ms.proses) lokasi,
                a.tempat
            FROM
                `stocker_input` a
                inner join master_sb_ws msb on msb.so_det_id = a.so_det_id
                inner join form_cut_input f on a.form_cut_id = f.id
                INNER JOIN marker_input m ON m.kode = f.id_marker
                inner join part_detail pd on a.part_detail_id = pd.id
                inner join master_secondary ms on pd.master_secondary_id = ms.id
            WHERE
                a.id_qr_stocker = '$request->txtqrstocker'
        ");

        return json_encode($data_header ? $data_header[0] : null);
    }

    public function get_tempat(Request $request)
    {
        $tujuan = $request->tujuan;
        if ($tujuan == 'NON SECONDARY') {
            $data_tempat = DB::select("select 'RAK' isi, 'RAK' tampil
            union
            select 'TROLLEY', 'TROLLEY'");
            $html = "<option value=''>Pilih Tempat</option>";
            foreach ($data_tempat as $datatempat) {
                $html .= " <option value='" . $datatempat->tampil . "'>" . $datatempat->tampil . "</option> ";
            }
        } else {
            $data_tempat = DB::select("select '-' isi, '-' tampil");
            $html = "<option value = '-' selected> - </option>";
        }

        return $html;
    }


    public function get_lokasi(Request $request)
    {
        $tujuan = $request->tujuan;
        $tempat = $request->tempat;
        if ($tujuan == 'NON SECONDARY' && $tempat == 'RAK') {
            $data_alokasi = DB::select("select kode isi, nama_detail_rak tampil from rack_detail");
            $html = "<option value=''>Pilih Rak</option>";
            foreach ($data_alokasi as $dataalokasi) {
                $html .= " <option value='" . $dataalokasi->tampil . "'>" . $dataalokasi->tampil . "</option> ";
            }
        } else if ($tujuan == 'NON SECONDARY' && $tempat == 'TROLLEY') {
            $data_alokasi = DB::select("select kode isi, nama_trolley tampil from trolley");
            $html = "<option value=''>Pilih Trolley</option>";
            foreach ($data_alokasi as $dataalokasi) {
                $html .= " <option value='" . $dataalokasi->tampil . "'>" . $dataalokasi->tampil . "</option> ";
            }
        } else {
            $data_alokasi = DB::select("select proses isi, proses tampil from master_secondary where tujuan = '$tujuan'");
            $html = "<option value=''>Pilih Lokasi</option>";
            foreach ($data_alokasi as $dataalokasi) {
                $html .= " <option value='" . $dataalokasi->tampil . "'>" . $dataalokasi->tampil . "</option> ";
            }
        }

        return $html;
    }

    public function create(Request $request)
    {
        return view('dc.dc-in.create-dc-in', ['page' => 'dashboard-dc', "subPageGroup" => "dcin-dc", "subPage" => "dc-in"]);
    }

    public function get_tmp_dc_in(Request $request)
    {
        $user = Auth::user()->name;

        // $tmpDcIn = DB::select("
        //     select
        //         ms.id_qr_stocker,
        //         mp.nama_part,
        //         concat(ms.id_qr_stocker,' - ',mp.nama_part) kode_stocker,
        //         ifnull(s.tujuan,'-') tujuan,
        //         ifnull(tmp.tempat,'-') tempat,
        //         ifnull(tmp.lokasi,'-') lokasi,
        //         concat(coalesce(ms.qty_ply_mod, ms.qty_ply) - coalesce(tmp.qty_reject,0) + coalesce(tmp.qty_replace,0), concat(' (', (coalesce(tmp.qty_replace,0) - coalesce(tmp.qty_reject,0)), ')')) qty_in,
        //         ms.act_costing_ws,
        //         ms.size,
        //         ms.color,
        //         ms.panel,
        //         concat(ms.range_awal, '-', ms.range_akhir) rangeAwalAkhir,
        //         ifnull(tmp.id_qr_stocker,'x') cek_stat
        //     from
        //         (
        //             select
        //                 *,
        //                 concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) kode
        //             from
        //                 stocker_input
        //         ) ms
        //         inner join
        //             (
        //                 select
        //                     concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) kode
        //                 from
        //                     tmp_dc_in_input_new x
        //                     inner join stocker_input y on x.id_qr_stocker = y.id_qr_stocker
        //                 where
        //                     user = '$user'
        //                 group by
        //                     concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade)
        //             )
        //         a on ms.kode = a.kode
        //         inner join part_detail pd on ms.part_detail_id = pd.id
        //         inner join master_part mp  on pd.master_part_id = mp.id
        //         left join master_secondary s on pd.master_secondary_id = s.id
        //         left join tmp_dc_in_input_new tmp on ms.id_qr_stocker = tmp.id_qr_stocker
        //     order by
        //         ifnull(tmp.id_qr_stocker,'x') asc
        // ");

        $tmpDcIn = DB::select("
            SELECT
                ms.id_qr_stocker,
                mp.nama_part,
                concat( ms.id_qr_stocker, ' - ', mp.nama_part ) kode_stocker,
                ifnull( s.tujuan, '-' ) tujuan,
                ifnull( tmp.tempat, '-' ) tempat,
                ifnull( tmp.lokasi, '-' ) lokasi,
                concat(
                    COALESCE ( ms.qty_ply_mod, ms.qty_ply ) - COALESCE ( tmp.qty_reject, 0 ) + COALESCE ( tmp.qty_replace, 0 ),
                concat( ' (', ( COALESCE ( tmp.qty_replace, 0 ) - COALESCE ( tmp.qty_reject, 0 )), ')' )) qty_in,
                ms.act_costing_ws,
                msb.size,
                ms.color,
                ms.panel,
                concat( ms.range_awal, '-', ms.range_akhir ) rangeAwalAkhir,
                ifnull( tmp.id_qr_stocker, 'x' ) cek_stat
            FROM
                tmp_dc_in_input_new x
                INNER JOIN stocker_input y ON x.id_qr_stocker = y.id_qr_stocker
                LEFT JOIN stocker_input ms ON ms.form_cut_id = y.form_cut_id AND ms.so_det_id = y.so_det_id AND ms.group_stocker = y.group_stocker AND ms.ratio = y.ratio
                LEFT JOIN master_sb_ws msb ON msb.so_det_id = ms.so_det_id
                LEFT JOIN tmp_dc_in_input_new tmp ON tmp.id_qr_stocker = ms.id_qr_stocker
                INNER JOIN part_detail pd ON ms.part_detail_id = pd.id
                INNER JOIN master_part mp ON pd.master_part_id = mp.id
                LEFT JOIN master_secondary s ON pd.master_secondary_id = s.id
            WHERE
                x.`user` = '".$user."'
            group by ms.id_qr_stocker
            order by ifnull( tmp.id_qr_stocker, 'x' )
        ");

        return DataTables::of($tmpDcIn)->toJson();
    }

    public function insert_tmp_dc_in(Request $request)
    {
        $user = Auth::user()->name;
        if ($request->txttuj_h == 'NON SECONDARY') {
            $tujuan = $request->txttuj_h;
            $lokasi = $request->txtlok_h;
            $tempat = $request->txttempat_h;
        } else {
            $tujuan = $request->txttuj_h;
            $lokasi = $request->txtlok_h;
            $tempat = '-';
        }

        $cekdata =  DB::select("
            select
                *
            from
                tmp_dc_in_input_new
                left join dc_in_input on dc_in_input.id_qr_stocker = tmp_dc_in_input_new.id_qr_stocker
            where
                tmp_dc_in_input_new.id_qr_stocker = '" . $request->txtqrstocker . "'
        ");

        $cekdata_fix = $cekdata ? $cekdata[0] : null;
        if ($cekdata_fix ==  null) {

            $cekdata_fix = $cekdata ? $cekdata[0] : null;
            if ($cekdata_fix ==  null) {

                DB::insert("
                    insert into tmp_dc_in_input_new
                    (
                        id_qr_stocker,
                        qty_reject,
                        qty_replace,
                        tujuan,
                        tempat,
                        lokasi,
                        user
                    )
                    values
                    (
                        '" . $request->txtqrstocker . "',
                        '0',
                        '0',
                        '$tujuan',
                        '$tempat',
                        '$lokasi',
                        '$user'
                    )
                ");

                DB::update(
                    "update stocker_input set status = 'dc' where id_qr_stocker = '" . $request->txtqrstocker . "'"
                );
            }
        }
    }

    public function show_tmp_dc_in(Request $request)
    {
        $data_tmp_dc_in = DB::select("
            SELECT
                s.id_qr_stocker,
                coalesce(s.qty_ply_mod, s.qty_ply) - coalesce(tmp.qty_reject,0) + coalesce(tmp.qty_replace,0) qty_in,
                tmp.qty_reject,
                tmp.qty_replace,
                ms.tujuan,
                ms.proses,
                tmp.tempat,
                tmp.lokasi,
                tmp.ket,
                concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade)kode
            from
                stocker_input s
                inner join part_detail pd on s.part_detail_id = pd.id
                inner join master_part mp  on pd.master_part_id = mp.id
                left join master_secondary ms on pd.master_secondary_id = ms.id
                left join tmp_dc_in_input_new tmp on s.id_qr_stocker = tmp.id_qr_stocker
            where
                s.id_qr_stocker= '$request->id_c'
        ");

        return json_encode($data_tmp_dc_in[0]);
    }

    public function update_tmp_dc_in(Request $request)
    {
        if ($request->txttuj == 'NON SECONDARY') {
            $update_stocker_input = DB::update("
                update
                    stocker_input
                set
                    tempat = '" . $request->cbotempat . "',
                    tujuan = '" . $request->txttuj . "',
                    lokasi = '" . $request->cbolokasi . "'
                where
                    concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) = '" . $request->id_kode . "'
            ");

            // Trolley Things
            if ($request->cbotempat == "TROLLEY") {
                $lastTrolleyStock = TrolleyStocker::select('kode')->orderBy('id', 'desc')->first();
                $trolleyStockNumber = $lastTrolleyStock ? intval(substr($lastTrolleyStock->kode, -5)) + 1 : 1;

                $stockerData = Stocker::whereRaw("concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) = '" . $request->id_kode . "'")->get();

                $trolleyStockArr = [];

                $thisTrolley = Trolley::where("nama_trolley", $request->cbolokasi)->first();
                if ($thisTrolley) {

                    $i = 0;
                    foreach ($stockerData as $stocker) {
                        $trolleyCheck = TrolleyStocker::where('stocker_id', $stocker->id)->first();
                        if (!$trolleyCheck) {
                            array_push($trolleyStockArr, [
                                "kode" => "TLS".sprintf('%05s', ($trolleyStockNumber+$i)),
                                "trolley_id" => $thisTrolley->id,
                                "stocker_id" => $stocker->id,
                                "status" => "active",
                                "tanggal_alokasi" => date('Y-m-d'),
                                "created_at" => Carbon::now(),
                                "updated_at" => Carbon::now(),
                            ]);

                            $i++;
                        }
                    }

                    $storeTrolleyStock = TrolleyStocker::insert($trolleyStockArr);

                    if (count($trolleyStockArr) > 0) {
                        $updateStocker = Stocker::whereRaw("concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) = '" . $request->id_kode . "'")->
                            update([
                                "status" => "trolley",
                                "latest_alokasi" => Carbon::now()
                            ]);
                    }
                }
            }
        }

        $update_tmp_dc_in = DB::table("tmp_dc_in_input_new")->
            where("id_qr_stocker", $request->id_c )->
            update([
                "qty_reject" => $request->txtqtyreject,
                "qty_replace" => $request->txtqtyreplace,
                "tujuan" => $request->txttuj,
                "tempat" => $request->cbotempat,
                "lokasi" => $request->cbolokasi,
                "ket" => $request->txtket
            ]);

        if (!(is_nan($update_tmp_dc_in))) {
            return array(
                'status' => 300,
                'message' => 'Data Stocker "' . $request->id_c . '" berhasil diubah',
                'redirect' => '',
                'table' => 'datatable-scan',
                'additional' => [],
                'callback' => 'resetCheckedStocker()'
            );
        }
    }

    public function update_mass_tmp_dc_in(Request $request)
    {
        ini_set('max_execution_time', 36000);

        $massStockerIds = explode(",", $request->mass_id_c);

        if (count($massStockerIds) > 0) {
            $stockerCodes = Stocker::selectRaw("concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) kode")->whereIn("id_qr_stocker", $massStockerIds)->pluck('kode')->toArray();
            $stockerCodeRaw = "(";
            for ($i = 0; $i < count($stockerCodes); $i++) {
                if ($i > 0) {
                    $stockerCodeRaw .= ", '".$stockerCodes[$i]."'";
                } else {
                    $stockerCodeRaw .= "'".$stockerCodes[$i]."'";
                }
            }
            $stockerCodeRaw .= ")";

            if ($request->mass_txttuj == 'NON SECONDARY') {
                $update_stocker_input = DB::update("
                    update
                        stocker_input
                    set
                        tempat = '" . $request->mass_cbotempat . "',
                        tujuan = '" . $request->mass_txttuj . "',
                        lokasi = '" . $request->mass_cbolokasi . "'
                    where
                        concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) in " . $stockerCodeRaw . "
                ");

                if ($request->mass_cbotempat == "TROLLEY") {
                    $lastTrolleyStock = TrolleyStocker::select('kode')->orderBy('id', 'desc')->first();
                    $trolleyStockNumber = $lastTrolleyStock ? intval(substr($lastTrolleyStock->kode, -5)) + 1 : 1;

                    $stockerData = Stocker::whereRaw("concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) in " . $stockerCodeRaw)->get();

                    $trolleyStockArr = [];

                    $thisTrolley = Trolley::where("nama_trolley", $request->mass_cbolokasi)->first();
                    if ($thisTrolley) {

                        $i = 0;
                        foreach ($stockerData as $stocker) {
                            $trolleyCheck = TrolleyStocker::where('stocker_id', $stocker->id)->first();
                            if (!$trolleyCheck) {
                                array_push($trolleyStockArr, [
                                    "kode" => "TLS".sprintf('%05s', ($trolleyStockNumber+$i)),
                                    "trolley_id" => $thisTrolley->id,
                                    "stocker_id" => $stocker->id,
                                    "status" => "active",
                                    "tanggal_alokasi" => date('Y-m-d'),
                                    "created_at" => Carbon::now(),
                                    "updated_at" => Carbon::now(),
                                ]);

                                $i++;
                            }
                        }

                        $storeTrolleyStock = TrolleyStocker::insert($trolleyStockArr);

                        if (count($trolleyStockArr) > 0) {
                            $updateStocker = Stocker::whereRaw("concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) in " . $stockerCodeRaw)->
                                update([
                                    "status" => "trolley",
                                    "latest_alokasi" => Carbon::now()
                                ]);
                        }
                    }
                }
            }

            $update_tmp_dc_in = DB::table("tmp_dc_in_input_new")->
                whereIn("id_qr_stocker", $massStockerIds)->
                update([
                    "tujuan" => $request->mass_txttuj,
                    "tempat" => $request->mass_cbotempat,
                    "lokasi" => $request->mass_cbolokasi,
                ]);

            if (!(is_nan($update_tmp_dc_in))) {
                return array(
                    'status' => 300,
                    'message' => 'Data Stocker "' . $request->mass_id_c . '" berhasil diubah',
                    'redirect' => '',
                    'table' => 'datatable-scan',
                    'additional' => [],
                    'callback' => 'resetCheckedStocker()'
                );
            }
        }

        return array(
            'status' => 400,
            'message' => 'Data Stocker "' . $request->mass_id_c . '" gagal diubah',
            'redirect' => '',
            'table' => 'datatable-scan',
            'additional' => [],
            'callback' => 'resetCheckedStocker()'
        );
    }

    public function store(Request $request)
    {
        $tgltrans = date('Y-m-d');
        $timestamp = Carbon::now();
        $user = Auth::user()->name;

        DB::insert("
            REPLACE INTO dc_in_input
            (
                id_qr_stocker,
                tgl_trans,
                tujuan,
                lokasi,
                tempat,
                qty_awal,
                qty_reject,
                qty_replace,
                user,
                status,
                created_at,
                updated_at
            )
            select
                tmp.id_qr_stocker,
                '$tgltrans',
                tmp.tujuan,
                tmp.lokasi,
                tmp.tempat,
                coalesce(ms.qty_ply_mod, ms.qty_ply),
                qty_reject,
                qty_replace,
                user,
                'N',
                '$timestamp',
                '$timestamp'
            from
                tmp_dc_in_input_new tmp
                inner join stocker_input ms on tmp.id_qr_stocker = ms.id_qr_stocker
            where
                tmp.tujuan > '' and
                tmp.lokasi > '' and
                tmp.tempat > '' and
                user = '$user'
        ");

        DB::insert("
            INSERT INTO rack_detail_stocker
            (
                detail_rack_id,
                nm_rak,
                stocker_id,
                qty_in,
                created_at,
                updated_at
            )
            select
                r.id,nama_detail_rak,
                tmp.id_qr_stocker,
                coalesce(s.qty_ply_mod, s.qty_ply) - qty_reject + qty_replace qty_in,
                '$timestamp',
                '$timestamp'
            from
                tmp_dc_in_input_new tmp
                inner join rack_detail r on tmp.lokasi = r.nama_detail_rak
                inner join stocker_input s on tmp.id_qr_stocker = s.id_qr_stocker
            where
                tmp.tujuan = 'NON SECONDARY' and
                tmp.tujuan > '' and
                tmp.lokasi > '' and
                tmp.tempat > '' and
                user = '$user'
            "
        );

        return array(
            'status' => 999,
            'message' => 'Data Sudah Disimpan',
            'redirect' => 'reload',
            'table' => '',
            'additional' => [],
            'callback' => 'cleard()',
        );
    }

    public function destroy(Request $request)
    {
        $user = Auth::user()->name;

        DB::delete(
            "DELETE FROM tmp_dc_in_input_new where tujuan > '' and lokasi > '' and tempat > '' and user = '$user'"
        );
    }

    // public function export_excel_mut_karyawan(Request $request)
    // {
    //     return Excel::download(new ExportLaporanMutasiKaryawan($request->from, $request->to), 'Laporan_Mutasi_Karyawan.xlsx');
    // }
}
