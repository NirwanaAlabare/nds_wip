<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard roll kain yang sudah diterima cutting (penerimaan_cutting)
 * tetapi belum dipakai sama sekali (spreading, form piece, piping, ganti reject).
 */
class DashboardRollBelumDipakaiController extends Controller
{
    /**
     * Penerimaan yang dihitung hanya mulai tanggal ini (sama dengan batas modul penerimaan cutting).
     */
    private const MIN_TGL_TERIMA = '2026-10-01';

    /**
     * Batas umur (hari) roll dianggap terlalu lama menunggu.
     */
    public const BATAS_HARI = 3;

    public function index()
    {
        return view('cutting.dashboard.roll-belum-dipakai', [
            'batasHari' => self::BATAS_HARI,
        ]);
    }

    public function data(Request $request)
    {
        $today = Carbon::today();

        return response()->json([
            'status'     => true,
            'today'      => $today->format('Y-m-d'),
            'batas_hari' => self::BATAS_HARI,
            'unused'     => $this->rollBelumDipakai($today),
            'in_use'     => $this->rollSedangDipakai(),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Penerimaan terakhir per roll (sebelum hari ini) yang belum punya pemakaian.
     *
     * Batas waktu pemakaian:
     * - penerimaan pertama roll -> pemakaian kapan pun dianggap sudah dipakai
     *   (data backfill mencatat created_at = tgl_bppb 00:00, bisa tidak presisi),
     * - roll yang diterima ulang -> hanya pemakaian sejak penerimaan terakhir.
     */
    private function rollBelumDipakai(Carbon $today)
    {
        $usageTables = [
            ['form_cut_input_detail', 'id_roll'],
            ['form_cut_piece_detail', 'id_roll'],
            ['form_cut_piping', 'id_roll'],
            ['piping_process_detail', 'id_roll'],
            ['form_cut_alokasi_gr_panel_barcode', 'barcode'],
        ];

        $notUsed = collect($usageTables)->map(fn ($t) => "
            AND NOT EXISTS (
                SELECT 1 FROM {$t[0]} u
                WHERE u.{$t[1]} = pc.id_roll
                AND COALESCE(u.created_at, u.updated_at) >= pc.batas
            )")->implode("\n");

        $rows = DB::select("
            SELECT
                pc.id,
                pc.id_roll,
                DATE(pc.tanggal_terima) AS tgl_terima,
                pc.created_at AS terima_at,
                pc.qty_konv,
                pc.unit_konv,
                d.no_bppb,
                d.id_jo,
                d.qty_out,
                d.satuan AS unit,
                d.no_lot,
                d.no_roll,
                d.item_desc,
                h.no_req,
                h.no_ws,
                h.no_ws_aktual,
                mi.color AS warna
            FROM (
                SELECT
                    p.*,
                    IF(
                        EXISTS (
                            SELECT 1 FROM penerimaan_cutting p0
                            WHERE p0.id_roll = p.id_roll AND p0.id <> p.id AND p0.created_at < p.created_at
                        ),
                        p.created_at,
                        '2000-01-01 00:00:00'
                    ) AS batas
                FROM penerimaan_cutting p
                WHERE p.tanggal_terima >= ?
                AND p.tanggal_terima < ?
                AND p.id_roll IS NOT NULL AND p.id_roll <> ''
                AND NOT EXISTS (
                    SELECT 1 FROM penerimaan_cutting p2
                    WHERE p2.id_roll = p.id_roll
                    AND (p2.created_at > p.created_at OR (p2.created_at = p.created_at AND p2.id > p.id))
                )
            ) pc
            LEFT JOIN signalbit_erp.whs_bppb_det d ON d.id = pc.whs_bppb_det_id
            LEFT JOIN signalbit_erp.whs_bppb_h h ON h.no_bppb = d.no_bppb
            LEFT JOIN signalbit_erp.masteritem mi ON mi.id_item = d.id_item
            WHERE 1 = 1
            {$notUsed}
            ORDER BY pc.tanggal_terima ASC, pc.id ASC
        ", [self::MIN_TGL_TERIMA, $today->format('Y-m-d')]);

        $styles = $this->stylePerJo(collect($rows)->pluck('id_jo'));

        return collect($rows)->map(function ($r) use ($styles, $today) {
            $tgl = Carbon::parse($r->tgl_terima);

            return [
                'tgl'   => $r->tgl_terima,
                'age'   => (int) $tgl->diffInDays($today),
                'id'    => $r->id_roll,
                'req'   => $r->no_req,
                'bppb'  => $r->no_bppb,
                'ws'    => $r->no_ws,
                'wsAct' => $r->no_ws_aktual,
                'style' => $styles[$r->id_jo] ?? '-',
                'item'  => $r->item_desc,
                'color' => $r->warna ?? '-',
                'lot'   => $r->no_lot,
                'no'    => $r->no_roll,
                'qty'   => (float) ($r->qty_out ?? 0),
                'unit'  => $r->unit ?? '-',
                'm'     => strtoupper((string) $r->unit_konv) === 'METER' ? (float) $r->qty_konv : null,
                'qtyK'  => (float) $r->qty_konv,
                'unitK' => $r->unit_konv,
            ];
        })->values();
    }

    /**
     * Roll yang sedang di meja: detail spreading / form piece yang belum selesai (aktivitas 24 jam terakhir).
     */
    private function rollSedangDipakai()
    {
        $since = Carbon::now()->subDay()->format('Y-m-d H:i:s');

        $rows = DB::select("
            SELECT * FROM (
                SELECT
                    fd.id_roll,
                    'Form Gelar' AS keg,
                    fi.no_form,
                    meja.name AS meja,
                    fd.color_act AS warna,
                    fd.qty,
                    fd.unit,
                    COALESCE(fd.updated_at, fd.created_at) AS ts
                FROM form_cut_input_detail fd
                JOIN form_cut_input fi ON fi.id = fd.form_cut_id
                LEFT JOIN users meja ON meja.id = fi.no_meja
                WHERE fd.status IN ('not complete', 'extension')
                AND fi.status <> 'SELESAI PENGERJAAN'
                AND fd.id_roll IS NOT NULL AND fd.id_roll <> ''
                AND COALESCE(fd.updated_at, fd.created_at) >= ?

                UNION ALL

                SELECT
                    pd.id_roll,
                    'Form Piece' AS keg,
                    fp.no_form,
                    NULL AS meja,
                    fp.color AS warna,
                    pd.qty,
                    pd.qty_unit AS unit,
                    COALESCE(pd.updated_at, pd.created_at) AS ts
                FROM form_cut_piece_detail pd
                JOIN form_cut_piece fp ON fp.id = pd.form_id
                WHERE pd.status = 'incomplete'
                AND pd.id_roll IS NOT NULL AND pd.id_roll <> ''
                AND COALESCE(pd.updated_at, pd.created_at) >= ?
            ) x
            ORDER BY ts DESC
        ", [$since, $since]);

        return collect($rows)->map(fn ($r) => [
            'id'    => $r->id_roll,
            'keg'   => $r->keg,
            'form'  => $r->no_form,
            'meja'  => $r->meja ? strtoupper($r->meja) : null,
            'color' => $r->warna ?? '-',
            'qty'   => (float) ($r->qty ?? 0),
            'unit'  => $r->unit ?? '',
        ])->unique('id')->values();
    }

    private function stylePerJo($idJos)
    {
        $idJos = collect($idJos)->filter()->unique()->values();

        if ($idJos->isEmpty()) {
            return collect();
        }

        return DB::connection('mysql_sb')
            ->table('act_costing as ac')
            ->selectRaw('jod.id_jo, MAX(ac.styleno) AS styleno')
            ->join('so', 'ac.id', '=', 'so.id_cost')
            ->join('jo_det as jod', 'so.id', '=', 'jod.id_so')
            ->whereIn('jod.id_jo', $idJos)
            ->groupBy('jod.id_jo')
            ->pluck('styleno', 'id_jo');
    }
}
