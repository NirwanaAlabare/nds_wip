<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\Dc\Rack;
use App\Models\Stocker\Stocker;
use App\Models\Marker\Marker;
use App\Models\Dc\DCIn;
use App\Models\Cutting\FormCutInput;
use App\Models\SignalBit\UserLine;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\ActCosting;
use App\Models\SignalBit\SoDet;
use App\Models\SignalBit\Rft;
use App\Exports\Sewing\ChiefSewingRangeExport;
use App\Exports\Sewing\LeaderSewingRangeExport;
use Yajra\DataTables\Facades\DataTables;
use App\Events\TriggerWipLine;
use DB;
use Excel;

class DashboardWipLineController extends Controller
{
    public function track(Request $request)
    {
        return Redirect::to('/home');
    }

    public function index(Request $request)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2048M');

        $months = [['angka' => 1, 'nama' => 'Januari'], ['angka' => 2, 'nama' => 'Februari'], ['angka' => 3, 'nama' => 'Maret'], ['angka' => 4, 'nama' => 'April'], ['angka' => 5, 'nama' => 'Mei'], ['angka' => 6, 'nama' => 'Juni'], ['angka' => 7, 'nama' => 'Juli'], ['angka' => 8, 'nama' => 'Agustus'], ['angka' => 9, 'nama' => 'September'], ['angka' => 10, 'nama' => 'Oktober'], ['angka' => 11, 'nama' => 'November'], ['angka' => 12, 'nama' => 'Desember']];
        $years = array_reverse(range(1999, date('Y')));

        $query = DB::connection('mysql_dsb')->select("SELECT COALESCE(sum(jam_kerja),0) as jam_kerja from master_plan where tgl_plan = CURRENT_DATE() and sewing_line = 'line_01'");
        $lines = DB::connection('mysql_dsb')->select("SELECT username as id, FullName as name from userpassword where Groupp = 'SEWING' and (Locked is NULL OR Locked != 1) ORDER BY line_id");

        return view('wip/dashboard-wip', ['page' => 'dashboard-wip', 'lines' => $lines, 'months' => $months, 'years' => $years]);
    }

    public function show_wip_line($id)
    {
        return view('wip.wip-line', [
            'id' => $id,
        ]);
    }

    // tampilan smart factory, data dari endpoint trigger_wip_line yang sama dengan wip-line
    public function show_wip_line_smart($id)
    {
        return view('wip.wip-line-smart', [
            'id' => $id,
        ]);
    }

    public function trigger_wip_line(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'line_id' => 'required|string',
            'jam' => 'nullable|date_format:H:i', // TEST JAM: hapus setelah selesai cek
        ]);
        $tanggal = $validated['tanggal'];
        $lineId = $validated['line_id'];

        // TEST JAM: ?jam=HH:MM di URL dashboard menggantikan jam server untuk menit sekarang
        $nowM = null;
        if (!empty($validated['jam'])) {
            [$h, $m] = explode(':', $validated['jam']);
            $nowM = (int) $h * 60 + (int) $m;
        }

        $data['user'] = "$lineId - End Line";
        $data['summary'] = $this->cari_summary_line($tanggal, $lineId, $nowM);
        $data['buyer'] = $data['summary']->buyer ?? '';
        $data['no_ws'] = $data['summary']->ws ?? '';
        $data['hourly'] = $this->cari_hourly_line($tanggal, $lineId);

        $data['list_defect'] = $this->listdefect($tanggal, $lineId);
        $data['link_gambar1'] = $this->cari_link_gambar1($tanggal, $lineId);
        $data['positiondefect'] = $this->cari_positiondefect($tanggal, $lineId);

        // broadcast(new TriggerWipLine($data, $lineId));
        return response()->json(
            [
                'message' => 'Data diterima',
                'data' => [
                    'tanggal' => $tanggal,
                    'line_id' => $lineId,
                    'data' => $data,
                ],
            ],
            200,
        );
    }

    function cari_summary_line($tanggal, $line, $nowM = null) // TEST JAM: $nowM
    {
        // Perhitungan per jam sama dengan cari_hourly_line, jadi req_hour_target & output_jam_ini
        // selalu cocok dengan baris jam berjalan di tabel per jam
        $query = DB::connection('mysql_dsb')->select(
            "WITH RECURSIVE x AS (
                SELECT DATE(?) AS tgl, ? AS line,
                       COALESCE(?, TIME_TO_SEC(CURTIME()) DIV 60) AS now_m   -- TEST JAM: aslinya TIME_TO_SEC(CURTIME()) DIV 60
            ),
            p AS (
                SELECT
                    x.tgl, x.line, x.now_m,
                    IF(DAYOFWEEK(x.tgl) = 6, 690, 720)          AS i1s,   -- istirahat siang mulai
                    IF(DAYOFWEEK(x.tgl) = 6, 750, 780)          AS i1e,   -- istirahat siang selesai
                    1080 AS i2s, 1110 AS i2e,                             -- istirahat malam
                    GROUP_CONCAT(DISTINCT ac.kpno ORDER BY ac.kpno SEPARATOR ', ')         AS ws,
                    GROUP_CONCAT(DISTINCT ms.supplier ORDER BY ms.supplier SEPARATOR ', ') AS buyer,
                    SUM(m.set_target)                           AS target,
                    ROUND(SUM(m.jam_kerja), 2)                  AS jam_kerja,
                    ROUND(SUM(m.jam_kerja) * 60)                AS total_m,
                    MIN(m.jam_kerja_awal)                       AS jam_kerja_awal,
                    TIME_TO_SEC(MIN(m.jam_kerja_awal)) DIV 60   AS start_m
                FROM x
                JOIN master_plan m     ON m.tgl_plan = x.tgl AND m.sewing_line = x.line AND m.cancel = 'N'
                JOIN act_costing ac    ON ac.id = m.id_ws
                JOIN mastersupplier ms ON ms.Id_Supplier = ac.id_buyer
            ),
            -- Slot per jam: menit kerja (dipotong istirahat, dibatasi jam kerja plan) & sisa menit
            slot AS (
                SELECT -1 AS n, start_m - 60 AS mulai, 0 AS menit, 0 AS terpakai, total_m AS sisa_m FROM p
                UNION ALL
                SELECT
                    s.n + 1,
                    s.mulai + 60,
                    LEAST(p.total_m - s.terpakai - s.menit,
                          60 - GREATEST(0, LEAST(s.mulai + 120, p.i1e) - GREATEST(s.mulai + 60, p.i1s))
                             - GREATEST(0, LEAST(s.mulai + 120, p.i2e) - GREATEST(s.mulai + 60, p.i2s))),
                    s.terpakai + s.menit,
                    p.total_m - s.terpakai - s.menit
                FROM slot s CROSS JOIN p
                WHERE s.mulai + 120 < 1440
            ),
            -- Target per jam = ROUND(sisa target * menit / sisa menit)
            tj AS (
                SELECT -1 AS n, CAST(target AS SIGNED) AS sisa, 0 AS tgt FROM p
                UNION ALL
                SELECT
                    s.n,
                    t.sisa - t.tgt,
                    IF(s.menit = 0, 0, ROUND((t.sisa - t.tgt) * s.menit / s.sisa_m))
                FROM tj t JOIN slot s ON s.n = t.n + 1
            ),
            -- Output & defect per slot (jam pertama ditarik dari 00:00:00)
            -- p di-join di luar union: kalau di dalam, optimizer membaca semua master_plan line ini dulu (lambat)
            od AS (
                SELECT GREATEST(0, (TIME_TO_SEC(z.created_at) - p.start_m * 60) DIV 3600) AS n,
                       SUM(z.o) AS output, SUM(1 - z.o) AS defect
                FROM (
                    SELECT a.created_at, 1 AS o
                    FROM x
                    JOIN output_rfts a ON a.created_at >= x.tgl
                                      AND a.created_at <  x.tgl + INTERVAL 1 DAY
                    JOIN master_plan m ON m.id = a.master_plan_id AND m.sewing_line = x.line
                    UNION ALL
                    SELECT d.created_at, 0
                    FROM x
                    JOIN output_defects d ON d.created_at >= x.tgl
                                         AND d.created_at <  x.tgl + INTERVAL 1 DAY
                    JOIN master_plan m    ON m.id = d.master_plan_id AND m.sewing_line = x.line
                                         AND m.tgl_plan = x.tgl AND m.cancel = 'N'
                ) z
                CROSS JOIN p
                GROUP BY n
            ),
            -- Jam istirahat penuh dipindah ke jam kerja berikutnya,
            -- jam setelah shift dikumpulkan ke 1 jam setelah akhir shift (sama dengan cari_hourly_line)
            peta AS (
                SELECT
                    n,
                    LEAST(COALESCE(MIN(IF(menit > 0, n, NULL)) OVER (ORDER BY n DESC ROWS UNBOUNDED PRECEDING), n),
                          COALESCE(MAX(IF(menit > 0, n, NULL)) OVER () + 1, n)) AS tujuan
                FROM slot
                WHERE n >= 0
            ),
            od2 AS (
                SELECT COALESCE(pt.tujuan, od.n) AS n, SUM(od.output) AS output, SUM(od.defect) AS defect
                FROM od
                LEFT JOIN peta pt ON pt.n = od.n
                GROUP BY 1
            ),
            -- Slot jam berjalan (kalau sedang istirahat, ikut ke jam kerja berikutnya)
            cur AS (
                SELECT pt.tujuan AS n
                FROM p
                JOIN peta pt ON pt.n = GREATEST(0, (p.now_m - p.start_m) DIV 60)
            ),
            -- Total harian
            o AS (
                SELECT
                    COUNT(*)                 AS tot_output,
                    SUM(a.status = 'NORMAL') AS rft
                FROM x
                JOIN output_rfts a  ON a.created_at >= x.tgl
                                   AND a.created_at <  x.tgl + INTERVAL 1 DAY
                JOIN master_plan m  ON m.id = a.master_plan_id AND m.sewing_line = x.line
            ),
            def AS (
                SELECT
                    COALESCE(SUM(d.defect_status = 'defect'), 0)   AS tot_defect,
                    COALESCE(SUM(d.defect_status = 'reworked'), 0) AS tot_reworked
                FROM x
                JOIN output_defects d ON d.created_at >= x.tgl
                                     AND d.created_at <  x.tgl + INTERVAL 1 DAY
                JOIN master_plan m    ON m.id = d.master_plan_id AND m.sewing_line = x.line
                                     AND m.tgl_plan = x.tgl AND m.cancel = 'N'
            ),
            calc AS (
                SELECT
                    p.*,
                    o.tot_output,
                    o.rft,
                    d.tot_defect,
                    d.tot_reworked,
                    CONCAT(TIME_FORMAT(SEC_TO_TIME(s.mulai * 60), '%H:%i'), ' - ',
                           TIME_FORMAT(SEC_TO_TIME(s.mulai * 60 + 3540), '%H:%i'))  AS periode_jam,
                    COALESCE(t.tgt, 0)       AS req_hour_target,
                    COALESCE(od2.output, 0)  AS output_jam_ini,
                    p.target / (p.jam_kerja * 60) AS target_per_menit,
                    -- dibatasi jam kerja plan, jadi target_skrg tidak melebihi target & eff tidak turun setelah shift
                    LEAST(
                        GREATEST(0,
                              GREATEST(0, p.now_m - p.start_m)
                            - GREATEST(0, LEAST(p.now_m, p.i1e) - GREATEST(p.start_m, p.i1s))
                            - GREATEST(0, LEAST(p.now_m, p.i2e) - GREATEST(p.start_m, p.i2s))
                        ),
                        p.jam_kerja * 60
                    ) AS menit_kerja
                FROM p
                CROSS JOIN o
                CROSS JOIN def d
                LEFT JOIN cur        ON TRUE
                LEFT JOIN slot s     ON s.n   = cur.n
                LEFT JOIN tj t       ON t.n   = cur.n
                LEFT JOIN od2        ON od2.n = cur.n
            )
            SELECT
                line                                                                   AS sewing_line,
                ws,
                buyer,
                target,
                jam_kerja,
                jam_kerja_awal,
                tot_output,
                periode_jam,
                req_hour_target,
                output_jam_ini,
                rft,
                tot_defect,
                tot_reworked,
                menit_kerja,
                ROUND(target_per_menit * menit_kerja)                                  AS target_skrg,
                ROUND(tot_output / NULLIF(target_per_menit * menit_kerja, 0) * 100, 2) AS eff,
                target - tot_output                                                    AS variation,
                -- dibagi output + defect
                ROUND(rft / NULLIF(tot_output + tot_defect, 0) * 100, 2)               AS rft_pct,
                ROUND(100 - rft / NULLIF(tot_output + tot_defect, 0) * 100, 2)         AS def_rate
            FROM calc",
            [$tanggal, $line, $nowM], // TEST JAM: aslinya [$tanggal, $line]
        );
        return $query[0] ?? null;
    }

    function cari_hourly_line($tanggal, $line)
    {
        $query = DB::connection('mysql_dsb')->select(
            "WITH RECURSIVE x AS (
                SELECT DATE(?) AS tgl, ? AS line
            ),
            p AS (
                SELECT
                    x.tgl, x.line,
                    IF(DAYOFWEEK(x.tgl) = 6, 690, 720)          AS i1s,   -- istirahat siang mulai
                    IF(DAYOFWEEK(x.tgl) = 6, 750, 780)          AS i1e,   -- istirahat siang selesai
                    1080 AS i2s, 1110 AS i2e,                             -- istirahat malam
                    SUM(m.set_target)                           AS target,
                    ROUND(SUM(m.jam_kerja) * 60)                AS total_m,
                    TIME_TO_SEC(MIN(m.jam_kerja_awal)) DIV 60   AS start_m
                FROM x
                JOIN master_plan m ON m.tgl_plan = x.tgl AND m.sewing_line = x.line AND m.cancel = 'N'
            ),
            -- Slot per jam: menit kerja (dipotong istirahat, dibatasi jam kerja plan) & sisa menit
            slot AS (
                SELECT -1 AS n, start_m - 60 AS mulai, 0 AS menit, 0 AS terpakai, total_m AS sisa_m FROM p
                UNION ALL
                SELECT
                    s.n + 1,
                    s.mulai + 60,
                    LEAST(p.total_m - s.terpakai - s.menit,
                          60 - GREATEST(0, LEAST(s.mulai + 120, p.i1e) - GREATEST(s.mulai + 60, p.i1s))
                             - GREATEST(0, LEAST(s.mulai + 120, p.i2e) - GREATEST(s.mulai + 60, p.i2s))),
                    s.terpakai + s.menit,
                    p.total_m - s.terpakai - s.menit
                FROM slot s CROSS JOIN p
                WHERE s.mulai + 120 < 1440
            ),
            -- Target per jam = ROUND(sisa target * menit / sisa menit)
            tj AS (
                SELECT -1 AS n, CAST(target AS SIGNED) AS sisa, 0 AS tgt FROM p
                UNION ALL
                SELECT
                    s.n,
                    t.sisa - t.tgt,
                    IF(s.menit = 0, 0, ROUND((t.sisa - t.tgt) * s.menit / s.sisa_m))
                FROM tj t JOIN slot s ON s.n = t.n + 1
            ),
            -- Output & defect per slot: tiap jam HH:00:00 s/d HH:59:59, jam pertama ditarik dari 00:00:00
            -- p di-join di luar union: kalau di dalam, optimizer membaca semua master_plan line ini dulu (lambat)
            od AS (
                SELECT GREATEST(0, (TIME_TO_SEC(z.created_at) - p.start_m * 60) DIV 3600) AS n,
                       SUM(z.o) AS output, SUM(1 - z.o) AS defect
                FROM (
                    SELECT a.created_at, 1 AS o
                    FROM x
                    JOIN output_rfts a ON a.created_at >= x.tgl
                                      AND a.created_at <  x.tgl + INTERVAL 1 DAY
                    JOIN master_plan m ON m.id = a.master_plan_id AND m.sewing_line = x.line
                    UNION ALL
                    SELECT d.created_at, 0
                    FROM x
                    JOIN output_defects d ON d.created_at >= x.tgl
                                         AND d.created_at <  x.tgl + INTERVAL 1 DAY
                    JOIN master_plan m    ON m.id = d.master_plan_id AND m.sewing_line = x.line
                                         AND m.tgl_plan = x.tgl AND m.cancel = 'N'
                ) z
                CROSS JOIN p
                GROUP BY n
            ),
            -- Jam istirahat penuh (menit kerja 0) dipindah ke jam kerja berikutnya,
            -- contoh output 12:00-12:59 masuk ke baris 13:00.
            -- Jam setelah shift dikumpulkan ke 1 jam setelah akhir shift, contoh output 17:xx, 18:xx masuk ke baris 16:00 - 17:00.
            peta AS (
                SELECT
                    n,
                    LEAST(COALESCE(MIN(IF(menit > 0, n, NULL)) OVER (ORDER BY n DESC ROWS UNBOUNDED PRECEDING), n),
                          COALESCE(MAX(IF(menit > 0, n, NULL)) OVER () + 1, n)) AS tujuan
                FROM slot
                WHERE n >= 0
            ),
            od2 AS (
                SELECT COALESCE(pt.tujuan, od.n) AS n, SUM(od.output) AS output, SUM(od.defect) AS defect
                FROM od
                LEFT JOIN peta pt ON pt.n = od.n
                GROUP BY 1
            )
            SELECT
                CONCAT(TIME_FORMAT(SEC_TO_TIME(mulai * 60), '%H:%i'), ' - ',
                       TIME_FORMAT(SEC_TO_TIME(mulai * 60 + 3600), '%H:%i'))   AS hours,
                menit                                                          AS menit_kerja,
                tgt                                                            AS target,
                output,
                output - tgt                                                   AS variation,
                IF(tgt = 0, 0, ROUND(output / tgt * 100, 2))                   AS efficiency,
                defect,
                IF(output = 0, 0, ROUND(defect / output * 100, 2))             AS defect_rate
            FROM (
                SELECT
                    s.n, s.mulai, s.menit, t.tgt,
                    COALESCE(od2.output, 0) AS output,
                    COALESCE(od2.defect, 0) AS defect,
                    MAX(IF(s.menit > 0, s.n, NULL)) OVER () AS n_kerja
                FROM slot s
                JOIN tj t     ON t.n = s.n
                LEFT JOIN od2 ON od2.n = s.n
                WHERE s.n >= 0
            ) r
            -- jam kerja selalu tampil, 1 jam setelah akhir shift hanya kalau ada output
            WHERE n <= n_kerja
               OR (n = n_kerja + 1 AND output > 0)
            ORDER BY n",
            [$tanggal, $line],
        );
        return $query;
    }

    function listdefect($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT defect_type_id, defect_type, jml
                       FROM (
                         SELECT a.defect_type_id, c.defect_type, COUNT(c.defect_type) AS jml
                         FROM output_defects a
                         INNER JOIN master_plan b ON b.id = a.master_plan_id
                         INNER JOIN output_defect_types c ON c.id = a.defect_type_id
                         WHERE b.sewing_line = ?
                         AND a.created_at >= ? AND a.created_at < ? + INTERVAL 1 DAY
                         GROUP BY c.defect_type
                       ) a
                       ORDER BY a.jml DESC
                       LIMIT 5",
            [$lineId, $tanggal, $tanggal],
        );

        return $query; // This is an array already
    }

    function cari_link_gambar1($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT gambar AS image
                   FROM master_plan
                   WHERE sewing_line = ?
                   AND tgl_plan = ?
                   AND cancel = ?
                   GROUP BY gambar
                   ORDER BY id ASC",
            [$lineId, $tanggal, 'N'],
        );
        return $query;
    }

    function cari_positiondefect($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT
                a.defect_area_x,
                a.defect_area_y,
                a.defect_type_id,
                b.gambar AS image
            FROM
                output_defects a
            INNER JOIN
                master_plan b
            ON
                b.id = a.master_plan_id
            WHERE
                b.sewing_line = ? AND
                b.cancel = ? AND
                a.created_at >= ? AND a.created_at < ? + INTERVAL 1 DAY",
            [$lineId, "N", $tanggal, $tanggal],
        );

        return $query;
    }

    function preChiefSewing() {
        $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
        $years = array_reverse(range(1999, date('Y')));

        return view('wip.pre-dashboard-chief-sewing', ['page' => 'dashboard-wip', "years" => $years, "months" => $months]);
    }

    function factoryPerformance($year = 0, $month = 0) {
        $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];

        $yearVar = $year ? $year : date("Y");
        $monthVar = $month ? $month : date("m");

        // return view('wip.dashboard-chief-sewing', ['page' => 'dashboard-wip', "year" => $yearVar, "month" => $monthVar, "monthName" => $months[num($monthVar)-1]["nama"], "months" => $months]);
        return view('wip.dashboard-factory-performance-sewing', ['page' => 'dashboard-wip', "year" => $yearVar, "month" => $monthVar, "monthName" => $months[num($monthVar)-1]["nama"], "months" => $months]);
    }

    function chiefSewing($year = 0, $month = 0) {
        $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];

        $yearVar = $year ? $year : date("Y");
        $monthVar = $month ? $month : date("m");

        // return view('wip.dashboard-chief-sewing', ['page' => 'dashboard-wip', "year" => $yearVar, "month" => $monthVar, "monthName" => $months[num($monthVar)-1]["nama"], "months" => $months]);
        return view('wip.dashboard-chief-support-sewing', ['page' => 'dashboard-wip', "year" => $yearVar, "month" => $monthVar, "monthName" => $months[num($monthVar)-1]["nama"], "months" => $months]);
    }

    function chiefSewingData(Request $request) {
        $month = $request->month ? $request->month : date("m");
        $year = $request->year ? $request->year : date("Y");

        $efficiencyLine = DB::connection("mysql_sb")->select("
            select
                output_employee_line.*,
                output_employee_line.line_name as sewing_line,
                SUM(rft) rft,
                SUM(output) output,
                SUM(mins_prod) mins_prod,
                SUM(mins_avail) mins_avail,
                SUM(cumulative_mins_avail) cumulative_mins_avail
            from
                output_employee_line
                left join userpassword on userpassword.line_id = output_employee_line.line_id
                left join (
                    SELECT
                        output.tgl_output,
                        output.tgl_plan,
                        output.sewing_line,
                        SUM( rft ) rft,
                        SUM( output ) output,
                        GROUP_CONCAT( CONCAT(output, '*', output.smv) ) outputsmv,
                        SUM( output * output.smv ) mins_prod,
                        SUM( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END ) * 60 mins_avail,
                        MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END ) man_power,
                        MAX( output.last_update ) last_update,
                        (
                            IF
                                (
                                    cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                    ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                    (
                                        IF (
                                            cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                            (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                            (
                                                IF (
                                                    cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                    (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                    TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                )
                                            )
                                        )
                                    )
                                )
                        ) / 60 jam_kerja,
                        (
                            IF
                                (
                                    cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                    ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                    (
                                        IF (
                                            cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                            (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                            (
                                                IF (
                                                    cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                    (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                    TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                )
                                            )
                                        )
                                    )
                                )
                        ) mins_kerja,
                        MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END )*
                        (
                            IF
                                (
                                    cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                    ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                    (
                                        IF (
                                            cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                            (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                            (
                                                IF (
                                                    cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                    (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                    TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                )
                                            )
                                        )
                                    )
                                )
                        ) cumulative_mins_avail,
                        FLOOR(
                            MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END )*
                            (
                                IF
                                    (
                                        cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                        ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                        (
                                            IF (
                                                cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                                (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                                (
                                                    IF (
                                                        cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                        (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                        TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                    )
                                                )
                                            )
                                        )
                                    )
                            ) / AVG( output.smv )
                        ) cumulative_target
                    FROM
                        (
                            SELECT
                                DATE( rfts.updated_at ) tgl_output,
                                COUNT( rfts.id ) output,
                                SUM( CASE WHEN rfts.status = 'NORMAL' THEN 1 ELSE 0 END ) rft,
                                MAX(rfts.updated_at) last_update,
                                master_plan.id master_plan_id,
                                master_plan.tgl_plan,
                                master_plan.sewing_line,
                                master_plan.man_power,
                                master_plan.jam_kerja,
                                master_plan.smv
                            FROM
                                output_rfts rfts
                                inner join master_plan on master_plan.id = rfts.master_plan_id
                            where
                                rfts.updated_at >= '".$year."-".$month."-01 00:00:00' AND rfts.updated_at <= '".$year."-".$month."-31 23:59:59'
                                AND master_plan.tgl_plan >= DATE_SUB('".$year."-".$month."-01', INTERVAL 20 DAY) AND master_plan.tgl_plan <= '".$year."-".$month."-31'
                                AND master_plan.cancel = 'N'
                            GROUP BY
                                master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)
                            order by
                                sewing_line
                        ) output
                    GROUP BY
                        output.sewing_line,
                        output.tgl_output
                ) output on output.sewing_line = userpassword.username and output.tgl_output = output_employee_line.tanggal
            WHERE
                tanggal between '".$year."-".$month."-01' AND '".$year."-".$month."-31'
                AND tanggal <= CURRENT_DATE()
            group by
                tanggal,
                line_id
            order by
                line_id asc,
                tanggal asc
        ");

        return $efficiencyLine;
    }

    function preChiefSewingRange() {
        return view('wip.pre-dashboard-chief-sewing-range', ['page' => 'dashboard-wip']);
    }

    function chiefSewingRange($from = 0, $to = 0) {
        $from = $from ? $from : date("Y-m-d", strtotime(date("Y-m-d")." -7 days"));
        $to = $to ? $to : date("Y-m-d");

        return view('wip.dashboard-chief-sewing-range', ['page' => 'dashboard-sewing-eff', 'subPageGroup' => 'sewing-report', 'subPage' => 'chief-sewing-range', "from" => $from, "to" => $to]);
    }

    function chiefSewingRangeData(Request $request) {
        $from = $request->from ? $request->from : date("Y-m-d", strtotime(date("Y-m-d")." -7 days"));
        $to = $request->to ? $request->to : date("Y-m-d");

        $efficiencyLine = DB::connection("mysql_sb")->select("
            select
                output_employee_line.*,
                output.sewing_line,
                SUM(rft) rft,
                SUM(output) output,
                SUM(mins_prod) mins_prod,
                SUM(mins_avail) mins_avail,
                SUM(cumulative_mins_avail) cumulative_mins_avail
            from
                output_employee_line
                left join userpassword on userpassword.line_id = output_employee_line.line_id
                inner join (
                    SELECT
                        output.tgl_output,
                        output.tgl_plan,
                        output.sewing_line,
                        SUM(rft) rft,
                        SUM(output) output,
                        SUM(output * output.smv) mins_prod,
                        SUM(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END) * 60 mins_avail,
                        MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END) man_power,
                        MAX(output.last_update) last_update,
                        (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)))/60 jam_kerja,
                        (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) mins_kerja,
                        MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) cumulative_mins_avail,
                        FLOOR(MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)/AVG(output.smv), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)/AVG(output.smv) ))) cumulative_target
                    FROM
                        (
                            SELECT
                                DATE( rfts.updated_at ) tgl_output,
                                COUNT( rfts.id ) output,
                                SUM( CASE WHEN rfts.status = 'NORMAL' THEN 1 ELSE 0 END ) rft,
                                MAX(rfts.updated_at) last_update,
                                master_plan.id master_plan_id,
                                master_plan.tgl_plan,
                                master_plan.sewing_line,
                                master_plan.man_power,
                                master_plan.jam_kerja,
                                master_plan.smv
                            FROM
                                output_rfts rfts
                                inner join master_plan on master_plan.id = rfts.master_plan_id
                            where
                                rfts.updated_at >= '".$from." 00:00:00' AND rfts.updated_at <= '".$to." 23:59:59'
                                AND master_plan.tgl_plan >= DATE_SUB('".$from."', INTERVAL 20 DAY) AND master_plan.tgl_plan <= '".$to."'
                                AND master_plan.cancel = 'N'
                            GROUP BY
                                master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)
                            order by
                                sewing_line
                        ) output
                    GROUP BY
                        output.sewing_line,
                        output.tgl_output
                ) output on output.sewing_line = userpassword.username and output.tgl_output = output_employee_line.tanggal
            group by
                tanggal,
                leader_id,
                chief_id,
                line_id
            order by
                chief_id asc,
                tanggal asc
        ");

        return $efficiencyLine;
    }

    function chiefSewingRangeDataExport(Request $request) {
        $from = $request->from ? $request->from : date("Y-m-d", strtotime(date("Y-m-d")." -7 days"));
        $to = $request->to ? $request->to : date("Y-m-d");

        return Excel::download(new ChiefSewingRangeExport($from, $to), 'chief_sewing_range.xlsx');
    }

    function leaderSewing(Request $request, $from = 0, $to = 0) {
        $from = $from ? $from : date("Y-m-d", strtotime(date("Y-m-d")." -7 days"));
        $to = $to ? $to : date("Y-m-d");
        $buyerId = $request->buyer_id;

        $orders = ActCosting::where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->pluck('kpno');
        $styles = ActCosting::where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('cost_date', 'desc')->orderBy('styleno', 'asc')->groupBy('styleno')->pluck('styleno');
        $styleProds = SoDet::leftJoin('so', 'so.id', '=', 'so_det.id_so')->leftJoin('act_costing', 'act_costing.id', '=', 'so.id_cost')->where('act_costing.cost_date', '>=', '2023-01-01')->orderBy('styleno_prod', 'desc')->orderBy('styleno_prod', 'asc')->groupBy('styleno_prod')->pluck('styleno_prod');

        $buyers = DB::connection('mysql_sb')->table('mastersupplier')->
            selectRaw('Id_Supplier as id, Supplier as name')->
            leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->
            leftJoin('master_plan', 'master_plan.id_ws', '=', 'act_costing.id')->
            where('mastersupplier.tipe_sup', 'C')->
            where('master_plan.cancel', 'N')->
            whereRaw('tgl_plan between cast((now() - interval 1 year) as date) AND cast(CURRENT_DATE() as date)')->
            orderBy('Supplier', 'ASC')->
            groupBy('Id_Supplier', 'Supplier')->
            get();

        return view('wip.dashboard-leader-sewing', ['page' => 'dashboard-sewing-eff', 'subPageGroup' => 'sewing-report', 'subPage' => 'leader-sewing', "from" => $from, "to" => $to, "buyers" => $buyers, "orders" => $orders, "styles" => $styles, "styleProds" => $styleProds]);
    }

    function leaderSewingData(Request $request) {
        ini_set("max_execution_time", 3600);
        $from = $request->from ? $request->from : date("Y-m-d", strtotime(date("Y-m-d")." -7 days"));
        $to = $request->to ? $request->to : date("Y-m-d");
        $buyerId = $request->buyer_id ? $request->buyer_id : null;
        $ws = $request->ws_filter ? addQuotesAround(implode("\r\n", $request->ws_filter)) : null;
        $style = $request->style_filter ? addQuotesAround(implode("\r\n", $request->style_filter)) : null;
        $styleProd = $request->style_prod_filter ? addQuotesAround(implode("\r\n", $request->style_prod_filter)) : null;
        $color = $request->color_filter ? addQuotesAround(implode("\r\n", $request->color_filter)) : null;
        $size = $request->size_filter ? addQuotesAround(implode("\r\n", $request->size_filter)) : null;
        $sewingLine = $request->sewing_line_filter ? addQuotesAround(implode("\r\n", $request->sewing_line_filter)) : null;
        $lineLeader = $request->line_leader_filter ? addQuotesAround(implode("\r\n", $request->line_leader_filter)) : null;

        $buyerFilter = $buyerId ? "AND mastersupplier.Id_Supplier in (".$buyerId.")" : "";
        $wsFilter = $ws ? "AND act_costing.kpno in (".$ws.")" : "";
        $styleFilter = $style ? "AND act_costing.styleno in (".$style.")" : "";
        $styleProdFilter = $styleProd ? "AND so_det.styleno_prod in (".$styleProd.")" : "";
        $colorFilter = $color ? "AND so_det.color in (".$color.")" : "";
        $sizeFilter = $size ? "AND so_det.size in (".$size.")" : "";
        $sewingLineFilter = $sewingLine ? "AND output.sewing_line in (".$sewingLine.")" : "";
        $lineLeaderFilter = $lineLeader ? "AND output_employee_line.leader_name in (".$lineLeader.")" : "";

        $tanggalQuery = "";
        $lineQuery = "";
        $tanggal = Rft::selectRaw("DATE(output_rfts.updated_at) as tgl_plan")->leftJoin("so_det", "so_det.id", "=", "output_rfts.so_det_id")->leftJoin("master_plan", "master_plan.id", "=", "output_rfts.master_plan_id")->leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws");
        $line = Rft::selectRaw("output_rfts.created_by")->leftJoin("so_det", "so_det.id", "=", "output_rfts.so_det_id")->leftJoin("master_plan", "master_plan.id", "=", "output_rfts.master_plan_id")->leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws");
        if ($wsFilter || $styleFilter || $styleProdFilter) {
            $tanggal->whereRaw("".
                (
                    $wsFilter || $styleFilter || $styleProdFilter ? "output_rfts.id is not null" :
                    "output_rfts.updated_at >= '".$from." 00:00:00' AND output_rfts.updated_at <= '".$to." 23:59:59' AND master_plan.tgl_plan >= DATE_SUB('".$from."', INTERVAL 20 DAY) AND master_plan.tgl_plan <= '".$to."'"
                ).
                "
                ".$wsFilter."
                ".$styleFilter."
                ".$styleProdFilter."
            ");

            $line->whereRaw("".
                (
                    $wsFilter || $styleFilter || $styleProdFilter ? "output_rfts.id is not null" :
                    "output_rfts.updated_at >= '".$from." 00:00:00' AND output_rfts.updated_at <= '".$to." 23:59:59' AND master_plan.tgl_plan >= DATE_SUB('".$from."', INTERVAL 20 DAY) AND master_plan.tgl_plan <= '".$to."'"
                ).
                "
                ".$wsFilter."
                ".$styleFilter."
                ".$styleProdFilter."
            ");

            $tanggalData = $tanggal->orderByRaw("DATE(output_rfts.updated_at) asc")->groupByRaw("DATE(output_rfts.updated_at)")->get();
            $lineData = $line->orderByRaw("output_rfts.created_by asc")->groupByRaw("output_rfts.created_by")->get();

            $tanggalQuery = addQuotesAround($tanggalData->pluck("tgl_plan")->implode("\r\n"));
            $lineQuery = addQuotesAround($lineData->pluck("created_by")->implode("\r\n"));
        }

        $efficiencyLine = DB::connection("mysql_sb")->select("
            SELECT
                output_employee_line.*,
                output.sewing_line,
                SUM( rft ) rft,
                SUM( output ) output,
                SUM( mins_prod ) mins_prod,
                SUM( mins_avail ) mins_avail,
                SUM( cumulative_mins_avail ) cumulative_mins_avail
            FROM
                output_employee_line
                LEFT JOIN userpassword ON userpassword.line_id = output_employee_line.line_id
                INNER JOIN (
                SELECT
                    output.tgl_output,
					output.tgl_plan,
					output.sewing_line,
					SUM( rft ) rft,
					SUM( output ) output,
					GROUP_CONCAT( CONCAT(output, '*', output.smv) ) outputsmv,
					SUM( output * output.smv ) mins_prod,
					SUM( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END ) * 60 mins_avail,
					MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END ) man_power,
					MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) last_update,
					".($wsFilter || $styleFilter || $styleProdFilter ? "(SUM(output)/total_output) * " : "")." (
						IF
							(
								cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ) <= '12:00:00',
								( TIME_TO_SEC( TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE ( output.jam_kerja_awal, '07:00:00' ) ))/ 60 ),
								(
									IF (
										cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ) >= '12:00:00' AND cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ) < '18:45:00',
										(TIME_TO_SEC(TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE ( output.jam_kerja_awal, '07:00:00' ) ))/ 60 ) - 60,
										(
											IF (
												cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ) >= '18:45:00',
												(TIME_TO_SEC(TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE ( output.jam_kerja_awal, '07:00:00' ) ))/ 60 ) - 90,
												TIME_TO_SEC( TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE ( output.jam_kerja_awal, '07:00:00' ) ))/ 60
											)
										)
									)
								)
							)
					) / 60 jam_kerja,
					".($wsFilter || $styleFilter || $styleProdFilter ? "(SUM(output)/total_output) * " : "")." (
						IF
							(
								cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ) <= '12:00:00',
								( TIME_TO_SEC( TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE ( output.jam_kerja_awal, '07:00:00' ) ))/ 60 ),
								(
									IF (
										cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ) >= '12:00:00' AND cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ) < '18:45:00',
										(TIME_TO_SEC(TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE ( output.jam_kerja_awal, '07:00:00' ) ))/ 60 ) - 60,
										(
											IF (
												cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ) >= '18:45:00',
												(TIME_TO_SEC(TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE ( output.jam_kerja_awal, '07:00:00' ) ))/ 60 ) - 90,
												TIME_TO_SEC( TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE ( output.jam_kerja_awal, '07:00:00' ) ))/ 60
											)
										)
									)
								)
							)
					) mins_kerja,
					MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END )*
					".($wsFilter || $styleFilter || $styleProdFilter ? "(SUM(output)/total_output) * " : "")."(
						IF
							(
								cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ) <= '12:00:00',
								( TIME_TO_SEC( TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE ( output.jam_kerja_awal, '07:00:00' ) ))/ 60 ),
								(
									IF (
										cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ) >= '12:00:00' AND cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ) < '18:45:00',
										(TIME_TO_SEC(TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE ( output.jam_kerja_awal, '07:00:00' ) ))/ 60 ) - 60,
										(
											IF (
												cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ) >= '18:45:00',
												(TIME_TO_SEC(TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE ( output.jam_kerja_awal, '07:00:00' ) ))/ 60 ) - 90,
												TIME_TO_SEC( TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE ( output.jam_kerja_awal, '07:00:00' ) ))/ 60
											)
										)
									)
								)
							)
					) cumulative_mins_avail,
					FLOOR(
						MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END )*
						".($wsFilter || $styleFilter || $styleProdFilter ? "(SUM(output)/total_output) * " : "")." (
							IF
								(
									cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ) <= '12:00:00',
									( TIME_TO_SEC( TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE ( output.jam_kerja_awal, '07:00:00' ) ))/ 60 ),
									(
										IF (
											cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ) >= '12:00:00' AND cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ) < '18:45:00',
											(TIME_TO_SEC(TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE ( output.jam_kerja_awal, '07:00:00' ) ))/ 60 ) - 60,
											(
												IF (
													cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ) >= '18:45:00',
													(TIME_TO_SEC(TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE ( output.jam_kerja_awal, '07:00:00' ) ))/ 60 ) - 90,
													TIME_TO_SEC( TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE ( output.jam_kerja_awal, '07:00:00' ) ))/ 60
												)
											)
										)
									)
								)
						) / AVG( output.smv )
					) cumulative_target,
					SUM(output)
                FROM
                    (
                        SELECT
                            DATE( rfts.updated_at ) tgl_output,
                            COUNT( rfts.id ) output,
                            SUM( CASE WHEN rfts.STATUS = 'NORMAL' THEN 1 ELSE 0 END ) rft,
                            MIN( rfts.created_at ) first_update,
                            MAX( rfts.updated_at ) last_update,
                            master_plan.id master_plan_id,
                            master_plan.tgl_plan,
                            master_plan.sewing_line,
                            master_plan.man_power,
                            master_plan.jam_kerja,
                            master_plan.jam_kerja_awal,
                            master_plan.smv,
                            rfts.created_by as sewing_line_id
                        FROM
                            output_rfts rfts
                            INNER JOIN master_plan ON master_plan.id = rfts.master_plan_id
                            INNER JOIN act_costing ON act_costing.id = master_plan.id_ws
                            INNER JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                            INNER JOIN so_det ON so_det.id = rfts.so_det_id
                        WHERE
                            ".(
                                $wsFilter || $styleFilter || $styleProdFilter ? "rfts.id is not null" :
                                "rfts.updated_at >= '".$from." 00:00:00' AND rfts.updated_at <= '".$to." 23:59:59' AND master_plan.tgl_plan >= DATE_SUB('".$from."', INTERVAL 20 DAY) AND master_plan.tgl_plan <= '".$to."'"
                            )."
                            AND master_plan.cancel = 'N'
                            ".$buyerFilter."
                            ".$wsFilter."
                            ".$styleFilter."
                            ".$styleProdFilter."
                            ".$colorFilter."
                        GROUP BY
                            master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)
                        ORDER BY
                            sewing_line
                    ) output
                    ".
                        (
                            ($wsFilter || $styleFilter || $styleProdFilter) ?
                            "
                                LEFT JOIN (
                                    SELECT
                                        DATE( updated_at ) tgl_output,
                                        created_by AS sewing_line_id,
                                        master_plan.sewing_line,
                                        max(TIME ( updated_at )) last_update,
                                        count( so_det_id ) total_output
                                    FROM
                                        output_rfts
                                        LEFT JOIN master_plan on master_plan.id = output_rfts.master_plan_id
                                    WHERE
                                        output_rfts.id is not null
                                        ".($tanggalQuery ? "AND DATE ( output_rfts.updated_at ) IN ( ".$tanggalQuery." )" : "")."
                                        ".($lineQuery ? "AND output_rfts.created_by IN ( ".$lineQuery." )" : "")."
                                    GROUP BY
                                        created_by,
                                        DATE ( updated_at )
                                ) alloutput ON alloutput.tgl_output = output.tgl_output AND alloutput.sewing_line = output.sewing_line
                            "
                            :
                            ""
                        )
                    ."
                GROUP BY
                    output.sewing_line,
                    output.tgl_output
                ) output ON output.sewing_line = userpassword.username
                AND output.tgl_output = output_employee_line.tanggal
            WHERE
                output.sewing_line is not null
                ".$sewingLineFilter."
                ".$lineLeaderFilter."
            GROUP BY
                line_id,
                tanggal
            ORDER BY
                line_id ASC,
                tanggal ASC
        ");

        // dd("
        //     SELECT
        //         output_employee_line.*,
        //         output.sewing_line,
        //         SUM( rft ) rft,
        //         SUM( output ) output,
        //         SUM( mins_prod ) mins_prod,
        //         SUM( mins_avail ) mins_avail,
        //         SUM( cumulative_mins_avail ) cumulative_mins_avail
        //     FROM
        //         output_employee_line
        //         LEFT JOIN userpassword ON userpassword.line_id = output_employee_line.line_id
        //         INNER JOIN (
        //         SELECT
        //             output.tgl_output,
        //             output.tgl_plan,
        //             output.sewing_line,
        //             SUM( rft ) rft,
        //             SUM( output ) output,
        //             SUM( output * output.smv ) mins_prod,
        //             SUM( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END ) * 60 mins_avail,
        //             MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END ) man_power,
        //             MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) last_update,
        //             (".($wsFilter || $styleFilter || $styleProdFilter ? "(SUM(output)/total_output) * " : "")." IF ( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ) <= '13:00:00', ( TIME_TO_SEC( TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE(output.jam_kerja_awal, '07:00:00') ))/ 60 ), (( TIME_TO_SEC( TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE(output.jam_kerja_awal, '07:00:00') ))/ 60 )- 60 )))/ 60 jam_kerja,
        //             (".($wsFilter || $styleFilter || $styleProdFilter ? "(SUM(output)/total_output) * " : "")." IF ( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ) <= '13:00:00', ( TIME_TO_SEC( TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE(output.jam_kerja_awal, '07:00:00') ))/ 60 ), (( TIME_TO_SEC( TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE(output.jam_kerja_awal, '07:00:00') ))/ 60 )- 60 ))) mins_kerja,
        //             MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END )*".($wsFilter || $styleFilter || $styleProdFilter ? "(SUM(output)/total_output)*" : "")."( IF ( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ) <= '13:00:00', ( TIME_TO_SEC( TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE(output.jam_kerja_awal, '07:00:00') ))/ 60 ), (( TIME_TO_SEC( TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE(output.jam_kerja_awal, '07:00:00') ))/ 60 )- 60 ))) cumulative_mins_avail,
        //             FLOOR( MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END )*".($wsFilter || $styleFilter || $styleProdFilter ? "(SUM(output)/total_output)*" : "")."( IF (  cast( MAX( output.last_update ) AS TIME ) <= '13:00:00', ( TIME_TO_SEC( TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE(output.jam_kerja_awal, '07:00:00') ))/ 60 )/ AVG( output.smv ), (( TIME_TO_SEC( TIMEDIFF( cast( MAX( ".($wsFilter || $styleFilter || $styleProdFilter ? "alloutput.last_update" : "output.last_update")." ) AS TIME ), COALESCE(output.jam_kerja_awal, '07:00:00') ))/ 60 )- 60 )/ AVG( output.smv )  ))) cumulative_target,
        //             SUM(output)
        //         FROM
        //             (
        //                 SELECT
        //                     DATE( rfts.updated_at ) tgl_output,
        //                     COUNT( rfts.id ) output,
        //                     SUM( CASE WHEN rfts.STATUS = 'NORMAL' THEN 1 ELSE 0 END ) rft,
        //                     MIN( rfts.created_at ) first_update,
        //                     MAX( rfts.updated_at ) last_update,
        //                     master_plan.id master_plan_id,
        //                     master_plan.tgl_plan,
        //                     master_plan.sewing_line,
        //                     master_plan.man_power,
        //                     master_plan.jam_kerja,
        //                     master_plan.jam_kerja_awal,
        //                     master_plan.smv,
        //                     rfts.created_by as sewing_line_id
        //                 FROM
        //                     output_rfts rfts
        //                     INNER JOIN master_plan ON master_plan.id = rfts.master_plan_id
        //                     INNER JOIN act_costing ON act_costing.id = master_plan.id_ws
        //                     INNER JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
        //                     INNER JOIN so_det ON so_det.id = rfts.so_det_id
        //                 WHERE
        //                     ".(
        //                         $wsFilter || $styleFilter || $styleProdFilter ? "rfts.id is not null" :
        //                         "rfts.updated_at >= '".$from." 00:00:00' AND rfts.updated_at <= '".$to." 23:59:59' AND master_plan.tgl_plan >= DATE_SUB('".$from."', INTERVAL 20 DAY) AND master_plan.tgl_plan <= '".$to."'"
        //                     )."
        //                     AND master_plan.cancel = 'N'
        //                     ".$buyerFilter."
        //                     ".$wsFilter."
        //                     ".$styleFilter."
        //                     ".$styleProdFilter."
        //                     ".$colorFilter."
        //                 GROUP BY
        //                     master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)
        //                 ORDER BY
        //                     sewing_line
        //             ) output
        //             ".
        //                 (
        //                     ($wsFilter || $styleFilter || $styleProdFilter) ?
        //                     "
        //                         LEFT JOIN (
        //                             SELECT
        //                                 DATE( updated_at ) tgl_output,
        //                                 created_by AS sewing_line_id,
        //                                 master_plan.sewing_line,
        //                                 max(TIME ( updated_at )) last_update,
        //                                 count( so_det_id ) total_output
        //                             FROM
        //                                 output_rfts
        //                                 LEFT JOIN master_plan on master_plan.id = output_rfts.master_plan_id
        //                             WHERE
        //                                 output_rfts.id is not null
        //                                 ".($tanggalQuery ? "AND DATE ( output_rfts.updated_at ) IN ( ".$tanggalQuery." )" : "")."
        //                                 ".($lineQuery ? "AND output_rfts.created_by IN ( ".$lineQuery." )" : "")."
        //                             GROUP BY
        //                                 created_by,
        //                                 DATE ( updated_at )
        //                         ) alloutput ON alloutput.tgl_output = output.tgl_output AND alloutput.sewing_line = output.sewing_line
        //                     "
        //                     :
        //                     ""
        //                 )
        //             ."
        //         GROUP BY
        //             output.sewing_line,
        //             output.tgl_output
        //         ) output ON output.sewing_line = userpassword.username
        //         AND output.tgl_output = output_employee_line.tanggal
        //     WHERE
        //         output.sewing_line is not null
        //         ".$sewingLineFilter."
        //         ".$lineLeaderFilter."
        //     GROUP BY
        //         line_id,
        //         tanggal
        //     ORDER BY
        //         line_id ASC,
        //         tanggal ASC
        // ");

        return $efficiencyLine;
    }

    function leaderSewingFilter(Request $request) {
        ini_set("max_execution_time", 3600);
        $from = $request->from ? $request->from : date("Y-m-d", strtotime(date("Y-m-d")." -7 days"));
        $to = $request->to ? $request->to : date("Y-m-d");
        $buyerId = $request->buyer_id ? $request->buyer_id : null;

        $buyerFilter = $buyerId ? "AND mastersupplier.Id_Supplier = '".$buyerId."'" : "";

        $efficiencyLine = collect(DB::connection("mysql_sb")->select("
            select
                output_employee_line.*,
                userpassword.username as sewing_line
            from
                output_employee_line
                left join userpassword on userpassword.line_id = output_employee_line.line_id
            where
                output_employee_line.tanggal between '".$from."' and '".$to."'
            group by
                line_id,
                tanggal
            order by
                line_id asc,
                tanggal asc
        "));

        $detail = collect(DB::connection("mysql_sb")->select("
            SELECT
                DATE( rfts.updated_at ) tgl_output,
                COUNT( rfts.id ) output,
                SUM( CASE WHEN rfts.status = 'NORMAL' THEN 1 ELSE 0 END ) rft,
                MAX(rfts.updated_at) last_update,
                master_plan.id master_plan_id,
                master_plan.tgl_plan,
                master_plan.sewing_line,
                master_plan.man_power,
                master_plan.jam_kerja,
                master_plan.smv,
                act_costing.kpno ws,
                act_costing.styleno style,
                so_det.color,
                so_det.size,
                so_det.styleno_prod style_prod,
                mastersupplier.Supplier buyer
            FROM
                output_rfts rfts
                inner join master_plan on master_plan.id = rfts.master_plan_id
                inner join so_det on so_det.id = rfts.so_det_id
                inner join act_costing on act_costing.id = master_plan.id_ws
                inner join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
            where
                rfts.updated_at >= '".$from." 00:00:00' AND rfts.updated_at <= '".$to." 23:59:59'
                AND master_plan.tgl_plan >= DATE_SUB('".$from."', INTERVAL 20 DAY) AND master_plan.tgl_plan <= '".$to."'
                AND master_plan.cancel = 'N'
                ".$buyerFilter."
            GROUP BY
                act_costing.kpno, act_costing.styleno, so_det.color, so_det.size
        "));

        $lines = $efficiencyLine->groupBy('sewing_line')->keys();
        $suppliers = $detail->groupBy('buyer')->keys();
        $colors = $detail->groupBy('color')->keys();
        $sizes = $detail->groupBy('size')->keys();
        $lineLeaders = $efficiencyLine->groupBy('leader_name')->keys();

        return array(
            'lines' => $lines,
            'suppliers' => $suppliers,
            'colors' => $colors,
            'sizes' => $sizes,
            'lineLeaders' => $lineLeaders,
        );
    }

    function leaderSewingRangeDataExport(Request $request) {
        $from = $request->from ? $request->from : date("Y-m-d", strtotime(date("Y-m-d")." -7 days"));
        $to = $request->to ? $request->to : date("Y-m-d");
        $buyer = $request->buyer ? $request->buyer : "";
        $ws = $request->ws_filter;
        $style = $request->style_filter;
        $styleProd = $request->style_prod_filter;
        $color = $request->color_filter;
        $size = $request->size_filter;
        $sewingLine = $request->sewing_line_filter;
        $lineLeader = $request->line_leader_filter;

        return Excel::download(new LeaderSewingRangeExport($from, $to, $buyer, $ws ,$style ,$styleProd ,$color ,$size ,$sewingLine ,$lineLeader), 'chief_sewing_range.xlsx');
    }

    function supportLineSewing($year = 0, $month = 0) {
        $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];

        $yearVar = $year ? $year : date("Y");
        $monthVar = $month ? $month : date("m");

        return view('wip.dashboard-support-line-sewing', ['page' => 'dashboard-wip', "year" => $yearVar, "month" => $monthVar, "monthName" => $months[num($monthVar)-1]["nama"], "months" => $months]);
    }

    function supportLineSewingData(Request $request) {
        $month = $request->month ? $request->month : date("m");
        $year = $request->year ? $request->year : date("Y");

        $efficiencyLine = DB::connection("mysql_sb")->select("
            select
                output_employee_line.*,
                output.sewing_line,
                SUM(coalesce(rft, 0)) rft,
                SUM(coalesce(output, 0)) output,
                SUM(coalesce(mins_prod, 0)) mins_prod,
                SUM(coalesce(mins_avail, 0)) mins_avail,
                SUM(coalesce(cumulative_mins_avail, 0)) cumulative_mins_avail
            from
                output_employee_line
                left join userpassword on userpassword.line_id = output_employee_line.line_id
                inner join (
                    SELECT
                        output.tgl_output,
                        output.tgl_plan,
                        output.sewing_line,
                        SUM( rft ) rft,
                        SUM( output ) output,
                        GROUP_CONCAT( CONCAT(output, '*', output.smv) ) outputsmv,
                        SUM( output * output.smv ) mins_prod,
                        SUM( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END ) * 60 mins_avail,
                        MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END ) man_power,
                        MAX( output.last_update ) last_update,
                        (
                            IF
                                (
                                    cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                    ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                    (
                                        IF (
                                            cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                            (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                            (
                                                IF (
                                                    cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                    (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                    TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                )
                                            )
                                        )
                                    )
                                )
                        ) / 60 jam_kerja,
                        (
                            IF
                                (
                                    cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                    ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                    (
                                        IF (
                                            cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                            (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                            (
                                                IF (
                                                    cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                    (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                    TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                )
                                            )
                                        )
                                    )
                                )
                        ) mins_kerja,
                        MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END )*
                        (
                            IF
                                (
                                    cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                    ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                    (
                                        IF (
                                            cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                            (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                            (
                                                IF (
                                                    cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                    (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                    TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                )
                                            )
                                        )
                                    )
                                )
                        ) cumulative_mins_avail,
                        FLOOR(
                            MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END )*
                            (
                                IF
                                    (
                                        cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                        ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                        (
                                            IF (
                                                cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                                (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                                (
                                                    IF (
                                                        cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                        (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                        TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                    )
                                                )
                                            )
                                        )
                                    )
                            ) / AVG( output.smv )
                        ) cumulative_target
                    FROM
                        (
                            SELECT
                                DATE( rfts.updated_at ) tgl_output,
                                COUNT( rfts.id ) output,
                                SUM( CASE WHEN rfts.status = 'NORMAL' THEN 1 ELSE 0 END ) rft,
                                MAX(rfts.updated_at) last_update,
                                master_plan.id master_plan_id,
                                master_plan.tgl_plan,
                                master_plan.sewing_line,
                                master_plan.man_power,
                                master_plan.jam_kerja,
                                master_plan.smv
                            FROM
                                output_rfts rfts
                                inner join master_plan on master_plan.id = rfts.master_plan_id
                            where
                                rfts.updated_at >= '".$year."-".$month."-01 00:00:00' AND rfts.updated_at <= '".$year."-".$month."-31 23:59:59'
                                AND master_plan.tgl_plan >= DATE_SUB('".$year."-".$month."-01', INTERVAL 20 DAY) AND master_plan.tgl_plan <= '".$year."-".$month."-31'
                                AND master_plan.cancel = 'N'
                            GROUP BY
                                master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)
                            order by
                                sewing_line
                        ) output
                    GROUP BY
                        output.sewing_line,
                        output.tgl_output
                ) output on output.sewing_line = userpassword.username and output.tgl_output = output_employee_line.tanggal
            group by
                tanggal,
                technical_id,
                mechanic_id,
                leaderqc_id,
                ie_id,
                chief_id
            order by
                chief_id asc,
                tanggal asc
        ");

        return $efficiencyLine;
    }

    function factoryDailyPerformance($year = 0, $month = 0) {
        $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];

        $yearVar = $year ? $year : date("Y");
        $monthVar = $month ? $month : date("m");

        return view('wip.dashboard-factory-daily-sewing', ['page' => 'dashboard-sewing-eff', 'subPageGroup' => 'sewing-report', 'subPage' => 'factory-daily-sewing', "year" => $yearVar, "month" => $monthVar, "monthName" => $months[num($monthVar)-1]["nama"], "months" => $months]);
    }

    function factoryDailyPerformanceData(Request $request) {
        $month = $request->month ? $request->month : date("m");
        $year = $request->year ? $request->year : date("Y");

        $factoryDailyEfficiency = DB::connection("mysql_sb")->select("
            select
                output.tgl_output as tanggal,
                output.sewing_line,
                SUM(rft) rft,
                SUM(output) output,
                SUM(mins_prod) mins_prod,
                SUM(mins_avail) mins_avail,
                SUM(cumulative_mins_avail) cumulative_mins_avail,
                AVG(target_eff) as target_eff,
                97 as target_rft
            from
                (
                    SELECT
                        output.tgl_output,
                        output.tgl_plan,
                        output.sewing_line,
                        SUM( rft ) rft,
                        SUM( output ) output,
                        GROUP_CONCAT( CONCAT(output, '*', output.smv) ) outputsmv,
                        SUM( output * output.smv ) mins_prod,
                        SUM( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END ) * 60 mins_avail,
                        MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END ) man_power,
                        MAX( output.last_update ) last_update,
                        (
                            IF
                                (
                                    cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                    ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                    (
                                        IF (
                                            cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                            (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                            (
                                                IF (
                                                    cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                    (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                    TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                )
                                            )
                                        )
                                    )
                                )
                        ) / 60 jam_kerja,
                        (
                            IF
                                (
                                    cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                    ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                    (
                                        IF (
                                            cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                            (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                            (
                                                IF (
                                                    cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                    (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                    TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                )
                                            )
                                        )
                                    )
                                )
                        ) mins_kerja,
                        MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END )*
                        (
                            IF
                                (
                                    cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                    ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                    (
                                        IF (
                                            cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                            (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                            (
                                                IF (
                                                    cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                    (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                    TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                )
                                            )
                                        )
                                    )
                                )
                        ) cumulative_mins_avail,
                        FLOOR(
                            MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END )*
                            (
                                IF
                                    (
                                        cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                        ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                        (
                                            IF (
                                                cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                                (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                                (
                                                    IF (
                                                        cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                        (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                        TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                    )
                                                )
                                            )
                                        )
                                    )
                            ) / AVG( output.smv )
                        ) cumulative_target,
                        output.target_effy as target_eff,
                        97 as target_rft
                    FROM
                        (
                            SELECT
                                DATE( rfts.updated_at ) tgl_output,
                                COUNT( rfts.id ) output,
                                SUM( CASE WHEN rfts.status = 'NORMAL' THEN 1 ELSE 0 END ) rft,
                                MAX(rfts.updated_at) last_update,
                                master_plan.id master_plan_id,
                                master_plan.tgl_plan,
                                master_plan.sewing_line,
                                master_plan.man_power,
                                master_plan.jam_kerja,
                                master_plan.smv,
                                master_plan.set_target,
                                master_plan.target_effy,
                                97 as target_rft
                            FROM
                                output_rfts rfts
                                inner join master_plan on master_plan.id = rfts.master_plan_id
                                inner join act_costing on act_costing.id = master_plan.id_ws
                                inner join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                            where
                                rfts.updated_at >= '".$year."-".$month."-01 00:00:00' AND rfts.updated_at <= '".$year."-".$month."-31 23:59:59'
                                AND master_plan.tgl_plan >= DATE_SUB('".$year."-".$month."-01', INTERVAL 20 DAY) AND master_plan.tgl_plan <= '".$year."-".$month."-31'
                                AND master_plan.cancel = 'N'
                            GROUP BY
                                master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)
                            order by
                                sewing_line
                        ) output
                    GROUP BY
                        output.sewing_line,
                        output.tgl_output
                ) output
            group by
                tgl_output
            order by
                tgl_output asc
        ");

        return $factoryDailyEfficiency;
    }

    function chiefLeaderSewing(Request $request, $from = 0, $to = 0) {
        $from = $from ? $from : date("Y-m-d", strtotime(date("Y-m-d")." -7 days"));
        $to = $to ? $to : date("Y-m-d");
        $buyerId = $request->buyer_id;

        $buyers = DB::connection('mysql_sb')->table('mastersupplier')->
            selectRaw('Id_Supplier as id, Supplier as name')->
            leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->
            leftJoin('master_plan', 'master_plan.id_ws', '=', 'act_costing.id')->
            where('mastersupplier.tipe_sup', 'C')->
            where('master_plan.cancel', 'N')->
            whereRaw('tgl_plan between cast((now() - interval 1 year) as date) AND cast(CURRENT_DATE() as date)')->
            orderBy('Supplier', 'ASC')->
            groupBy('Id_Supplier', 'Supplier')->
            get();

        return view('wip.dashboard-chief-leader-sewing', ['page' => 'dashboard-sewing-eff', 'subPageGroup' => 'sewing-report', 'subPage' => 'leader-sewing', "from" => $from, "to" => $to, "buyers" => $buyers]);
    }

    function chiefLeaderSewingData(Request $request) {
        $from = $request->from ? $request->from : date("Y-m-d", strtotime(date("Y-m-d")." -7 days"));
        $to = $request->to ? $request->to : date("Y-m-d");
        $buyerId = $request->buyer_id ? $request->buyer_id : null;

        $buyerFilter = $buyerId ? "AND mastersupplier.Id_Supplier = '".$buyerId."'" : "";

        $efficiencyLine = DB::connection("mysql_sb")->select("
            select
                output_employee_line.*,
                output.sewing_line,
                SUM(rft) rft,
                SUM(output) output,
                SUM(mins_prod) mins_prod,
                SUM(mins_avail) mins_avail,
                SUM(cumulative_mins_avail) cumulative_mins_avail
            from
                output_employee_line
                left join userpassword on userpassword.line_id = output_employee_line.line_id
                inner join (
                    SELECT
                        tgl_output,
                        output.tgl_plan,
                        output.sewing_line,
                        SUM( rft ) rft,
                        SUM( output ) output,
                        GROUP_CONCAT( CONCAT(output, '*', output.smv) ) outputsmv,
                        SUM( output * output.smv ) mins_prod,
                        SUM( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END ) * 60 mins_avail,
                        MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END ) man_power,
                        MAX( output.last_update ) last_update,
                        (
                            IF
                                (
                                    cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                    ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                    (
                                        IF (
                                            cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                            (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                            (
                                                IF (
                                                    cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                    (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                    TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                )
                                            )
                                        )
                                    )
                                )
                        ) / 60 jam_kerja,
                        (
                            IF
                                (
                                    cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                    ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                    (
                                        IF (
                                            cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                            (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                            (
                                                IF (
                                                    cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                    (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                    TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                )
                                            )
                                        )
                                    )
                                )
                        ) mins_kerja,
                        MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END )*
                        (
                            IF
                                (
                                    cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                    ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                    (
                                        IF (
                                            cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                            (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                            (
                                                IF (
                                                    cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                    (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                    TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                )
                                            )
                                        )
                                    )
                                )
                        ) cumulative_mins_avail,
                        FLOOR(
                            MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END )*
                            (
                                IF
                                    (
                                        cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                        ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                        (
                                            IF (
                                                cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                                (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                                (
                                                    IF (
                                                        cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                        (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                        TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                    )
                                                )
                                            )
                                        )
                                    )
                            ) / AVG( output.smv )
                        ) cumulative_target
                    FROM
                        (
                            SELECT
                                DATE( rfts.updated_at ) tgl_output,
                                COUNT( rfts.id ) output,
                                SUM( CASE WHEN rfts.status = 'NORMAL' THEN 1 ELSE 0 END ) rft,
                                MAX(rfts.updated_at) last_update,
                                master_plan.id master_plan_id,
                                master_plan.tgl_plan,
                                master_plan.sewing_line,
                                master_plan.man_power,
                                master_plan.jam_kerja,
                                master_plan.smv
                            FROM
                                output_rfts rfts
                                inner join master_plan on master_plan.id = rfts.master_plan_id
                                inner join act_costing on act_costing.id = master_plan.id_ws
                                inner join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                            where
                                rfts.updated_at >= '".$from." 00:00:00' AND rfts.updated_at <= '".$to." 23:59:59'
                                AND master_plan.cancel = 'N'
                                ".$buyerFilter."
                            GROUP BY
                                master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)
                            order by
                                sewing_line
                        ) output
                    GROUP BY
                        output.sewing_line,
                        output.tgl_output
                ) output on output.sewing_line = userpassword.username and output.tgl_output = output_employee_line.tanggal
            group by
                tanggal,
                leader_nik,
                chief_nik,
                line_id
            order by
                chief_nik asc,
                tanggal asc
        ");

        return $efficiencyLine;
    }

    function chiefLeaderSewingRangeDataExport(Request $request) {
        $from = $request->from ? $request->from : date("Y-m-d", strtotime(date("Y-m-d")." -7 days"));
        $to = $request->to ? $request->to : date("Y-m-d");
        $buyer = $request->buyer ? $request->buyer : "";

        return Excel::download(new LeaderSewingRangeExport($from, $to, $buyer), 'chief_sewing_range.xlsx');
    }

    function topChiefSewing($year = 0, $month = 0) {
        $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];

        $yearVar = $year ? $year : date("Y");
        $monthVar = $month ? $month : date("m");

        // return view('wip.dashboard-chief-sewing', ['page' => 'dashboard-wip', "year" => $yearVar, "month" => $monthVar, "monthName" => $months[num($monthVar)-1]["nama"], "months" => $months]);
        return view('wip.dashboard-top-chief-sewing', ['page' => 'dashboard-wip', "year" => $yearVar, "month" => $monthVar, "monthName" => $months[num($monthVar)-1]["nama"], "months" => $months]);
    }

    function topChiefSewingData(Request $request) {
        $month = $request->month ? $request->month : date("m");
        $year = $request->year ? $request->year : date("Y");

        $efficiencyChief = DB::connection("mysql_sb")->select("
            select
                output_employee_line.*,
                output.sewing_line,
                SUM(rft) rft,
                SUM(output) output,
                SUM(mins_prod) mins_prod,
                SUM(mins_avail) mins_avail,
                SUM(cumulative_mins_avail) cumulative_mins_avail,
                (SUM(mins_prod)/SUM(cumulative_mins_avail)*100) efficiency,
                (SUM(rft)/SUM(output)*100) rft,
                (SUM(mins_prod)/SUM(cumulative_mins_avail)*100) + (SUM(rft)/SUM(output)*100) rft_efficiency
            from
                output_employee_line
                left join userpassword on userpassword.line_id = output_employee_line.line_id
                inner join (
                    SELECT
                        tgl_output,
                        output.tgl_plan,
                        output.sewing_line,
                        SUM( rft ) rft,
                        SUM( output ) output,
                        GROUP_CONCAT( CONCAT(output, '*', output.smv) ) outputsmv,
                        SUM( output * output.smv ) mins_prod,
                        SUM( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END ) * 60 mins_avail,
                        MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END ) man_power,
                        MAX( output.last_update ) last_update,
                        (
                            IF
                                (
                                    cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                    ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                    (
                                        IF (
                                            cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                            (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                            (
                                                IF (
                                                    cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                    (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                    TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                )
                                            )
                                        )
                                    )
                                )
                        ) / 60 jam_kerja,
                        (
                            IF
                                (
                                    cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                    ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                    (
                                        IF (
                                            cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                            (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                            (
                                                IF (
                                                    cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                    (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                    TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                )
                                            )
                                        )
                                    )
                                )
                        ) mins_kerja,
                        MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END )*
                        (
                            IF
                                (
                                    cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                    ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                    (
                                        IF (
                                            cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                            (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                            (
                                                IF (
                                                    cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                    (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                    TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                )
                                            )
                                        )
                                    )
                                )
                        ) cumulative_mins_avail,
                        FLOOR(
                            MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END )*
                            (
                                IF
                                    (
                                        cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                        ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                        (
                                            IF (
                                                cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                                (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                                (
                                                    IF (
                                                        cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                        (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                        TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                    )
                                                )
                                            )
                                        )
                                    )
                            ) / AVG( output.smv )
                        ) cumulative_target
                    FROM
                        (
                            SELECT
                                DATE( rfts.updated_at ) tgl_output,
                                COUNT( rfts.id ) output,
                                SUM( CASE WHEN rfts.status = 'NORMAL' THEN 1 ELSE 0 END ) rft,
                                MAX(rfts.updated_at) last_update,
                                master_plan.id master_plan_id,
                                master_plan.tgl_plan,
                                master_plan.sewing_line,
                                master_plan.man_power,
                                master_plan.jam_kerja,
                                master_plan.smv
                            FROM
                                output_rfts rfts
                                inner join master_plan on master_plan.id = rfts.master_plan_id
                                inner join act_costing on act_costing.id = master_plan.id_ws
                                inner join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                            where
                                rfts.updated_at >= '".$year."-".$month."-01 00:00:00' AND rfts.updated_at <= '".$year."-".$month."-31 23:59:59'
                                AND master_plan.tgl_plan >= DATE_SUB('".$year."-".$month."-01', INTERVAL 20 DAY) AND master_plan.tgl_plan <= '".$year."-".$month."-31'
                                AND master_plan.cancel = 'N'
                            GROUP BY
                                master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)
                            order by
                                sewing_line
                        ) output
                    GROUP BY
                        output.sewing_line,
                        output.tgl_output
                ) output on output.sewing_line = userpassword.username and output.tgl_output = output_employee_line.tanggal
            group by
                chief_nik
            order by
                rft_efficiency desc
        ");

        return $efficiencyChief;
    }

    function topLeaderSewing($year = 0, $month = 0) {
        $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];

        $yearVar = $year ? $year : date("Y");
        $monthVar = $month ? $month : date("m");

        // return view('wip.dashboard-chief-sewing', ['page' => 'dashboard-wip', "year" => $yearVar, "month" => $monthVar, "monthName" => $months[num($monthVar)-1]["nama"], "months" => $months]);
        return view('wip.dashboard-top-leader-sewing', ['page' => 'dashboard-wip', "year" => $yearVar, "month" => $monthVar, "monthName" => $months[num($monthVar)-1]["nama"], "months" => $months]);
    }

    function topLeaderSewingData(Request $request) {
        $month = $request->month ? $request->month : date("m");
        $year = $request->year ? $request->year : date("Y");

        $efficiencyLeader = DB::connection("mysql_sb")->select("
            select
                output_employee_line.*,
                output.sewing_line,
                SUM(rft) rft,
                SUM(output) output,
                SUM(mins_prod) mins_prod,
                SUM(mins_avail) mins_avail,
                SUM(cumulative_mins_avail) cumulative_mins_avail,
                (SUM(mins_prod)/SUM(cumulative_mins_avail)*100) efficiency,
                (SUM(rft)/SUM(output)*100) rft,
                (SUM(mins_prod)/SUM(cumulative_mins_avail)*100) + (SUM(rft)/SUM(output)*100) rft_efficiency
            from
                output_employee_line
                left join userpassword on userpassword.line_id = output_employee_line.line_id
                inner join (
                    SELECT
                        tgl_output,
                        output.tgl_plan,
                        output.sewing_line,
                        SUM( rft ) rft,
                        SUM( output ) output,
                        GROUP_CONCAT( CONCAT(output, '*', output.smv) ) outputsmv,
                        SUM( output * output.smv ) mins_prod,
                        SUM( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END ) * 60 mins_avail,
                        MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END ) man_power,
                        MAX( output.last_update ) last_update,
                        (
                            IF
                                (
                                    cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                    ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                    (
                                        IF (
                                            cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                            (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                            (
                                                IF (
                                                    cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                    (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                    TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                )
                                            )
                                        )
                                    )
                                )
                        ) / 60 jam_kerja,
                        (
                            IF
                                (
                                    cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                    ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                    (
                                        IF (
                                            cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                            (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                            (
                                                IF (
                                                    cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                    (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                    TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                )
                                            )
                                        )
                                    )
                                )
                        ) mins_kerja,
                        MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END )*
                        (
                            IF
                                (
                                    cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                    ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                    (
                                        IF (
                                            cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                            (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                            (
                                                IF (
                                                    cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                    (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                    TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                )
                                            )
                                        )
                                    )
                                )
                        ) cumulative_mins_avail,
                        FLOOR(
                            MAX( CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END )*
                            (
                                IF
                                    (
                                        cast( MAX( output.last_update ) AS TIME ) <= '12:00:00',
                                        ( TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ),
                                        (
                                            IF (
                                                cast( MAX( output.last_update ) AS TIME ) >= '12:00:00' AND cast( MAX( output.last_update ) AS TIME ) < '18:45:00',
                                                (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 60,
                                                (
                                                    IF (
                                                        cast( MAX( output.last_update ) AS TIME ) >= '18:45:00',
                                                        (TIME_TO_SEC(TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60 ) - 90,
                                                        TIME_TO_SEC( TIMEDIFF( cast( MAX( output.last_update ) AS TIME ), '07:00:00' ))/ 60
                                                    )
                                                )
                                            )
                                        )
                                    )
                            ) / AVG( output.smv )
                        ) cumulative_target
                    FROM
                        (
                            SELECT
                                DATE( rfts.updated_at ) tgl_output,
                                COUNT( rfts.id ) output,
                                SUM( CASE WHEN rfts.status = 'NORMAL' THEN 1 ELSE 0 END ) rft,
                                MAX(rfts.updated_at) last_update,
                                master_plan.id master_plan_id,
                                master_plan.tgl_plan,
                                master_plan.sewing_line,
                                master_plan.man_power,
                                master_plan.jam_kerja,
                                master_plan.smv
                            FROM
                                output_rfts rfts
                                inner join master_plan on master_plan.id = rfts.master_plan_id
                                inner join act_costing on act_costing.id = master_plan.id_ws
                                inner join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                            where
                                rfts.updated_at >= '".$year."-".$month."-01 00:00:00' AND rfts.updated_at <= '".$year."-".$month."-31 23:59:59'
                                AND master_plan.tgl_plan >= DATE_SUB('".$year."-".$month."-01', INTERVAL 20 DAY) AND master_plan.tgl_plan <= '".$year."-".$month."-31'
                                AND master_plan.cancel = 'N'
                            GROUP BY
                                master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)
                            order by
                                sewing_line
                        ) output
                    GROUP BY
                        output.sewing_line,
                        output.tgl_output
                ) output on output.sewing_line = userpassword.username and output.tgl_output = output_employee_line.tanggal
            group by
                leader_nik
            order by
                rft_efficiency desc
        ");

        return $efficiencyLeader;
    }
}
