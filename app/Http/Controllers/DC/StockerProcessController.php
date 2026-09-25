<?php

namespace App\Http\Controllers\DC;

use App\Models\Stocker\Stocker;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class StockerProcessController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view("dc.stocker-process.stocker-process", ["page" => "dashboard-dc", "subPageGroup" => "stok-dc"]);
    }

    public function getData(Request $request)
    {
        $idStockerInput = trim($request->input('id_qr_stocker'));

        // 1. Validasi input
        if (empty($idStockerInput)) {
            return response()->json([
                'status' => 'success',
                'data'   => []
            ]);
        }

        // 2. Cari data stocker acuan
        $stockerData = Stocker::where("id", $idStockerInput)
            ->orWhere("id_qr_stocker", $idStockerInput)
            ->first();

        if (!$stockerData) {
            return response()->json([
                'status' => 'success',
                'data'   => []
            ]);
        }

        // 3. Tentukan kolom dan nilai form yang digunakan
        $formColumn =$stockerData->form_piece_id > 0
            ? "form_piece_id"
            : ($stockerData->form_reject_id > 0 ? "form_reject_id" : "form_cut_id");

        $formValue =$stockerData->form_piece_id > 0
            ? $stockerData->form_piece_id
            : ($stockerData->form_reject_id > 0 ? $stockerData->form_reject_id :$stockerData->form_cut_id);

        // 4. Ambil daftar ID QR Stocker yang similar
        $similarStockers = DB::table('stocker_input')
            ->leftJoin('part_detail', 'part_detail.id', '=', 'stocker_input.part_detail_id')
            ->where("stocker_input.{$formColumn}", $formValue)
            ->where("stocker_input.so_det_id", $stockerData->so_det_id)
            ->where("stocker_input.size", $stockerData->size)
            ->where("stocker_input.group_stocker", $stockerData->group_stocker)
            ->where("stocker_input.ratio", $stockerData->ratio)
            ->where("stocker_input.stocker_reject", $stockerData->stocker_reject)
            ->whereRaw("(part_detail.status IS NULL OR part_detail.status = 'active')")
            ->pluck('stocker_input.id_qr_stocker')
            ->toArray();

        if (empty($similarStockers)) {
            return response()->json([
                'status' => 'success',
                'data'   => []
            ]);
        }

        // 5. Format Quoted String untuk WHERE IN secara aman via PDO Quote
        $pdo = DB::getPdo();$quotedList = array_map(function ($item) use ($pdo) {
            return $pdo->quote($item);
        }, $similarStockers);

        $inClause = implode(',',$quotedList);

        // Buat klausa WHERE eksplisit dengan prefix nama tabel masing-masing
        $whereDcIn         = " WHERE dc_in_input.id_qr_stocker IN ({$inClause}) ";
        $whereSecInhouseIn = " WHERE secondary_inhouse_in_input.id_qr_stocker IN ({$inClause}) ";
        $whereSecInhouse   = " WHERE secondary_inhouse_input.id_qr_stocker IN ({$inClause}) ";
        $whereSecIn        = " WHERE secondary_in_input.id_qr_stocker IN ({$inClause}) ";
        $whereSecUpdate    = " WHERE secondary_in_update.id_qr_stocker IN ({$inClause}) ";
        $mainWhere         = " WHERE stocker_input.id_qr_stocker IN ({$inClause}) ";

        // 6. Query Utama
        $query = "
            SELECT
                stocker_input.id_qr_stocker,
                master_sb_ws.ws,
                master_sb_ws.color,
                master_sb_ws.size,
                (CASE WHEN COALESCE(pcust.set_part_status, part_detail.part_status) = 'complement' THEN COALESCE(p_com.panel, part.panel) ELSE part.panel END) AS panel,
                master_part.nama_part,
                part_detail.part_status,
                COALESCE(GROUP_CONCAT(DISTINCT multi_master_secondary.tujuan), master_secondary.tujuan) AS tujuan,
                COALESCE(GROUP_CONCAT(DISTINCT multi_master_secondary.proses), master_secondary.proses) AS proses,
                COALESCE(stocker_input.qty_ply_mod, stocker_input.qty_ply) AS qty_stocker,

                -- TOTAL AKUMULASI / LAST QTY
                COALESCE(dc.dc_qty_awal, 0) AS dc_qty_awal,
                COALESCE(dc.dc_qty_reject, 0) AS dc_qty_reject,
                COALESCE(dc.dc_qty_replace, 0) AS dc_qty_replace,
                (COALESCE(dc.dc_qty_awal, 0) - COALESCE(dc.dc_qty_reject, 0) + COALESCE(dc.dc_qty_replace, 0)) AS dc_qty_hasil,

                COALESCE(sih_in.qty_sec_inhouse_in, 0) AS qty_sec_inhouse_in,

                COALESCE(sih.sec_inhouse_qty_awal, 0) AS sec_inhouse_qty_awal,
                COALESCE(sih.sec_inhouse_qty_reject, 0) AS sec_inhouse_qty_reject,
                COALESCE(sih.sec_inhouse_qty_replace, 0) AS sec_inhouse_qty_replace,
                (COALESCE(sih.sec_inhouse_qty_awal, 0) - COALESCE(sih.sec_inhouse_qty_reject, 0) + COALESCE(sih.sec_inhouse_qty_replace, 0)) AS sec_inhouse_qty_hasil,

                COALESCE(sin.sec_in_qty_awal, 0) AS sec_in_qty_awal,
                COALESCE(sin.sec_in_qty_reject, 0) AS sec_in_qty_reject,
                COALESCE(sin.sec_in_qty_replace, 0) AS sec_in_qty_replace,
                (COALESCE(sin.sec_in_qty_awal, 0) - COALESCE(sin.sec_in_qty_reject, 0) + COALESCE(sin.sec_in_qty_replace, 0)) AS sec_in_qty_hasil,

                COALESCE(upd.update_reject, 0) AS update_reject,
                COALESCE(upd.update_replace, 0) AS update_replace,
                (COALESCE(upd.update_replace, 0) - COALESCE(upd.update_reject, 0)) AS update_qty_hasil,

                -- JSON HISTORY RIWAYAT
                dc.history_dc,
                sih_in.history_sec_inhouse_in,
                sih.history_sec_inhouse,
                sin.history_sec_in,
                upd.history_sec_update,

                DATE(trolley_stocker.created_at) tanggal_trolley,
                trolley.nama_trolley,

                loading_line.tanggal_loading,
                loading_line.nama_line,
                loading_line.qty loading_qty,

                stocker_input.stocker_reject,
                stocker_source.id_qr_stocker stocker_source_qr

            FROM stocker_input

            -- SUBQUERY 1: DC IN
            LEFT JOIN (
                SELECT
                    dc_in_input.id_qr_stocker,
                    CAST(SUBSTRING_INDEX(GROUP_CONCAT(dc_in_input.qty_awal ORDER BY dc_in_input.created_at ASC), ',', 1) AS UNSIGNED) AS dc_qty_awal,
                    SUM(dc_in_input.qty_reject) AS dc_qty_reject,
                    SUM(dc_in_input.qty_replace) AS dc_qty_replace,
                    JSON_ARRAYAGG(JSON_OBJECT(
                        'created_at', DATE_FORMAT(dc_in_input.created_at, '%Y-%m-%d %H:%i:%s'),
                        'tgl_trans', DATE_FORMAT(dc_in_input.tgl_trans, '%Y-%m-%d'),
                        'qty_awal', dc_in_input.qty_awal,
                        'qty_reject', dc_in_input.qty_reject,
                        'qty_replace', dc_in_input.qty_replace,
                        'hasil', (dc_in_input.qty_awal - dc_in_input.qty_reject + dc_in_input.qty_replace)
                    )) AS history_dc
                FROM dc_in_input
                {$whereDcIn}
                GROUP BY dc_in_input.id_qr_stocker
            ) dc ON dc.id_qr_stocker = stocker_input.id_qr_stocker

            -- SUBQUERY 2: SEC INHOUSE IN
            LEFT JOIN (
                SELECT
                    secondary_inhouse_in_input.id_qr_stocker,
                    CAST(SUBSTRING_INDEX(GROUP_CONCAT(secondary_inhouse_in_input.qty_in ORDER BY secondary_inhouse_in_input.created_at ASC), ',', 1) AS UNSIGNED) AS qty_sec_inhouse_in,
                    JSON_ARRAYAGG(JSON_OBJECT(
                        'created_at', DATE_FORMAT(secondary_inhouse_in_input.created_at, '%Y-%m-%d %H:%i:%s'),
                        'tgl_trans', DATE_FORMAT(secondary_inhouse_in_input.tgl_trans, '%Y-%m-%d'),
                        'qty_in', secondary_inhouse_in_input.qty_in,
                        'tujuan', COALESCE(master_secondary.tujuan, old_master_secondary.tujuan),
                        'proses', COALESCE(master_secondary.proses, old_master_secondary.proses)
                    )) AS history_sec_inhouse_in
                FROM secondary_inhouse_in_input
                LEFT JOIN stocker_input st ON st.id_qr_stocker = secondary_inhouse_in_input.id_qr_stocker
                LEFT JOIN part_detail pd ON pd.id = st.part_detail_id
                LEFT JOIN part_detail_secondary pds ON pds.part_detail_id = st.part_detail_id AND pds.urutan = secondary_inhouse_in_input.urutan
                LEFT JOIN master_secondary ON master_secondary.id = pds.master_secondary_id
                LEFT JOIN master_secondary old_master_secondary ON old_master_secondary.id = pd.master_secondary_id
                {$whereSecInhouseIn}
                GROUP BY secondary_inhouse_in_input.id_qr_stocker
            ) sih_in ON sih_in.id_qr_stocker = stocker_input.id_qr_stocker

            -- SUBQUERY 3: SEC INHOUSE (Ambil Qty Awal Pertama)
            LEFT JOIN (
                SELECT
                    secondary_inhouse_input.id_qr_stocker,
                    CAST(SUBSTRING_INDEX(GROUP_CONCAT(secondary_inhouse_input.qty_awal ORDER BY secondary_inhouse_input.created_at ASC), ',', 1) AS UNSIGNED) AS sec_inhouse_qty_awal,
                    SUM(secondary_inhouse_input.qty_reject) AS sec_inhouse_qty_reject,
                    SUM(secondary_inhouse_input.qty_replace) AS sec_inhouse_qty_replace,
                    JSON_ARRAYAGG(JSON_OBJECT(
                        'created_at', DATE_FORMAT(secondary_inhouse_input.created_at, '%Y-%m-%d %H:%i:%s'),
                        'tgl_trans', DATE_FORMAT(secondary_inhouse_input.tgl_trans, '%Y-%m-%d'),
                        'tujuan', COALESCE(master_secondary.tujuan, old_master_secondary.tujuan),
                        'proses', COALESCE(master_secondary.proses, old_master_secondary.proses),
                        'qty_awal', secondary_inhouse_input.qty_awal,
                        'qty_reject', secondary_inhouse_input.qty_reject,
                        'qty_replace', secondary_inhouse_input.qty_replace,
                        'hasil', (secondary_inhouse_input.qty_awal - secondary_inhouse_input.qty_reject + secondary_inhouse_input.qty_replace)
                    )) AS history_sec_inhouse
                FROM secondary_inhouse_input
                LEFT JOIN stocker_input st ON st.id_qr_stocker = secondary_inhouse_input.id_qr_stocker
                LEFT JOIN part_detail pd ON pd.id = st.part_detail_id
                LEFT JOIN part_detail_secondary pds ON pds.part_detail_id = st.part_detail_id AND pds.urutan = secondary_inhouse_input.urutan
                LEFT JOIN master_secondary ON master_secondary.id = pds.master_secondary_id
                LEFT JOIN master_secondary old_master_secondary ON old_master_secondary.id = pd.master_secondary_id
                {$whereSecInhouse}
                GROUP BY secondary_inhouse_input.id_qr_stocker
            ) sih ON sih.id_qr_stocker = stocker_input.id_qr_stocker

            -- SUBQUERY 4: SEC IN (Ambil Qty Awal Pertama)
            LEFT JOIN (
                SELECT
                    secondary_in_input.id_qr_stocker,
                    CAST(SUBSTRING_INDEX(GROUP_CONCAT(secondary_in_input.qty_awal ORDER BY secondary_in_input.created_at ASC), ',', 1) AS UNSIGNED) AS sec_in_qty_awal,
                    SUM(secondary_in_input.qty_reject) AS sec_in_qty_reject,
                    SUM(secondary_in_input.qty_replace) AS sec_in_qty_replace,
                    JSON_ARRAYAGG(JSON_OBJECT(
                        'created_at', DATE_FORMAT(secondary_in_input.created_at, '%Y-%m-%d %H:%i:%s'),
                        'tgl_trans', DATE_FORMAT(secondary_in_input.tgl_trans, '%Y-%m-%d'),
                        'tujuan', COALESCE(master_secondary.tujuan, old_master_secondary.tujuan),
                        'proses', COALESCE(master_secondary.proses, old_master_secondary.proses),
                        'qty_awal', secondary_in_input.qty_awal,
                        'qty_reject', secondary_in_input.qty_reject,
                        'qty_replace', secondary_in_input.qty_replace,
                        'hasil', (secondary_in_input.qty_awal - secondary_in_input.qty_reject + secondary_in_input.qty_replace)
                    )) AS history_sec_in
                FROM secondary_in_input
                LEFT JOIN stocker_input st ON st.id_qr_stocker = secondary_in_input.id_qr_stocker
                LEFT JOIN part_detail pd ON pd.id = st.part_detail_id
                LEFT JOIN part_detail_secondary pds ON pds.part_detail_id = st.part_detail_id AND pds.urutan = secondary_in_input.urutan
                LEFT JOIN master_secondary ON master_secondary.id = pds.master_secondary_id
                LEFT JOIN master_secondary old_master_secondary ON old_master_secondary.id = pd.master_secondary_id
                {$whereSecIn}
                GROUP BY secondary_in_input.id_qr_stocker
            ) sin ON sin.id_qr_stocker = stocker_input.id_qr_stocker

            -- SUBQUERY 5: SEC UPDATE
            LEFT JOIN (
                SELECT
                    secondary_in_update.id_qr_stocker,
                    SUM(secondary_in_update.`reject`) AS update_reject,
                    SUM(secondary_in_update.`replace`) AS update_replace,
                    JSON_ARRAYAGG(JSON_OBJECT(
                        'created_at', DATE_FORMAT(secondary_in_update.created_at, '%Y-%m-%d %H:%i:%s'),
                        'tgl_trans', DATE_FORMAT(secondary_in_update.tgl_trans, '%Y-%m-%d'),
                        'tujuan', COALESCE(master_secondary.tujuan, old_master_secondary.tujuan),
                        'proses', COALESCE(master_secondary.proses, old_master_secondary.proses),
                        'reject', secondary_in_update.`reject`,
                        'replace', secondary_in_update.`replace`,
                        'hasil', (secondary_in_update.`replace` - secondary_in_update.`reject`)
                    )) AS history_sec_update
                FROM secondary_in_update
                LEFT JOIN secondary_in_input ON secondary_in_input.id_qr_stocker = secondary_in_update.id_qr_stocker
                LEFT JOIN stocker_input st ON st.id_qr_stocker = secondary_in_input.id_qr_stocker
                LEFT JOIN part_detail pd ON pd.id = st.part_detail_id
                LEFT JOIN part_detail_secondary pds ON pds.part_detail_id = st.part_detail_id AND pds.urutan = secondary_in_input.urutan
                LEFT JOIN master_secondary ON master_secondary.id = pds.master_secondary_id
                LEFT JOIN master_secondary old_master_secondary ON old_master_secondary.id = pd.master_secondary_id
                {$whereSecUpdate}
                GROUP BY secondary_in_update.id_qr_stocker
            ) upd ON upd.id_qr_stocker = stocker_input.id_qr_stocker

            LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
            LEFT JOIN part_detail ON part_detail.id = stocker_input.part_detail_id
            LEFT JOIN part ON part.id = part_detail.part_id
            LEFT JOIN part_custom pcust ON pcust.part_id = part.id AND pcust.part_detail_id = part_detail.id AND pcust.color = master_sb_ws.color
            LEFT JOIN part_detail pd_com ON pd_com.id = part_detail.from_part_detail
            LEFT JOIN part p_com ON p_com.id = pd_com.part_id
            LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
            LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = part_detail.id
            LEFT JOIN master_secondary multi_master_secondary ON multi_master_secondary.id = part_detail_secondary.master_secondary_id
            LEFT JOIN master_secondary ON master_secondary.id = part_detail.master_secondary_id
            LEFT JOIN trolley_stocker ON trolley_stocker.stocker_id = stocker_input.id
            LEFT JOIN trolley ON trolley.id = trolley_stocker.trolley_id
            LEFT JOIN loading_line ON loading_line.stocker_id = stocker_input.id
            LEFT JOIN stocker_reject ON stocker_reject.id = stocker_input.stocker_reject
            LEFT JOIN stocker_input stocker_source ON
                stocker_source.form_cut_id = stocker_input.form_cut_id and
                stocker_source.part_detail_id = stocker_input.part_detail_id and
                stocker_source.so_det_id = stocker_input.so_det_id and
                stocker_source.group_stocker = stocker_input.group_stocker and
                stocker_source.ratio = stocker_input.ratio and
                stocker_source.stocker_reject is null and
                stocker_input.stocker_reject is not null

            {$mainWhere}

            GROUP BY
                stocker_input.id_qr_stocker,
                master_sb_ws.ws, master_sb_ws.color, master_sb_ws.size,
                pcust.set_part_status, part_detail.part_status, p_com.panel, part.panel, master_part.nama_part,
                stocker_input.qty_ply_mod, stocker_input.qty_ply,
                master_secondary.tujuan, master_secondary.proses,
                dc.dc_qty_awal, dc.dc_qty_reject, dc.dc_qty_replace, dc.history_dc,
                sih_in.qty_sec_inhouse_in, sih_in.history_sec_inhouse_in,
                sih.sec_inhouse_qty_awal, sih.sec_inhouse_qty_reject, sih.sec_inhouse_qty_replace, sih.history_sec_inhouse,
                sin.sec_in_qty_awal, sin.sec_in_qty_reject, sin.sec_in_qty_replace, sin.history_sec_in,
                upd.update_reject, upd.update_replace, upd.history_sec_update
        ";

        $data = DB::select($query);

        // Decode JSON String dari MySQL menjadi Native Array PHP
        foreach ($data as$row) {
            $row->history_dc = json_decode($row->history_dc ?? '[]');
            $row->history_sec_inhouse_in = json_decode($row->history_sec_inhouse_in ?? '[]');
            $row->history_sec_inhouse = json_decode($row->history_sec_inhouse ?? '[]');
            $row->history_sec_in = json_decode($row->history_sec_in ?? '[]');
            $row->history_sec_update = json_decode($row->history_sec_update ?? '[]');
        }

        return response()->json([
            'status' => 'success',
            'data'   => $data
        ]);
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
        //
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
