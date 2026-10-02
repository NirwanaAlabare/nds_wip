<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Marker\Marker;
use App\Models\Part\Part;
use App\Models\Stocker\Stocker;
use DB;
use Illuminate\Support\Facades\Auth;
use Excel;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Yajra\DataTables\Facades\DataTables;

class WipAdjustmentController extends Controller
{

    public function index() {
        return view("wip-adjustment.index", [
            'containerFluid' => true, 
            'page' => 'dashboard-ppic',
            "subPageGroup" => "ppic_tools",
            "subPage" => "wip-adjustment",
            // Tanggal closing terakhir (Y-m-d), tombol cancel di periode ini disabled
            'lastClosing' => DB::table('data_locks')
                ->where('is_locked', true)
                ->orderBy('end_date', 'desc')
                ->value('end_date'),
        ]);
    }

    public function contohUploadImport()
    {
        $path = public_path('assets/example/contoh-import-wip-adjustment.xlsx');
        return response()->download($path);
    }

    // Struktur template per jenis report: header di file => field
    private $templateFields = [
        'PACKING' => [
            'text' => [
                'ws'    => 'no_ws',
                'buyer' => 'buyer',
                'style' => 'style',
                'color' => 'color',
                'size'  => 'size',
            ],
            'qty' => [
                'transit terima packing line' => 'transit_terima_packing_line',
                'packing line'                => 'packing_line',
                'packing temporary'           => 'packing_temporary',
                'packing central'             => 'packing_central',
            ],
            'required' => ['no_ws', 'buyer', 'style', 'color', 'size'],
            'type_map' => [
                'transit_terima_packing_line' => 'TRANSIT_PACKING',
                'packing_line'                => 'PACKING',
                'packing_temporary'           => 'PACKING_TEMPORARY',
                'packing_central'             => 'PACKING_CENTRAL',
            ],
        ],
        'SEWING' => [
            'text' => [
                'ws'    => 'no_ws',
                'buyer' => 'buyer',
                'style' => 'style',
                'color' => 'color',
                'size'  => 'size',
            ],
            'qty' => [
                'sewing'                   => 'sewing',
                'qc finishing'             => 'qc_finishing',
                'finishing pasang kancing' => 'finishing_pasang_kancing',
                'finishing bartack'        => 'finishing_bartack',
                'finishing heatseal'       => 'finishing_heatseal',
                'finishing snap'           => 'finishing_snap',
                'finishing embro'          => 'finishing_embro',
                'defect sewing'            => 'defect_sewing',
                'defect spotcleaning'      => 'defect_spotcleaning',
                'defect mending'           => 'defect_mending',
                'transit terima qc reject' => 'transit_terima_qc_reject',
                'qc reject'                => 'qc_reject',
            ],
            'required' => ['no_ws', 'buyer', 'style', 'color', 'size'],
            'type_map' => [
                'sewing'                   => 'SEWING',
                'qc_finishing'             => 'QC FINISHING',
                'finishing_pasang_kancing' => ['FINISHING', 'Pasang Kancing'],
                'finishing_bartack'        => ['FINISHING', 'Bartack'],
                'finishing_heatseal'       => ['FINISHING', 'Heatseal'],
                'finishing_snap'           => ['FINISHING', 'Snap'],
                'finishing_embro'          => ['FINISHING', 'Embro'],
                'defect_sewing'            => 'DEFECT SEWING',
                'defect_spotcleaning'      => 'DEFECT SPOTCLEANING',
                'defect_mending'           => 'DEFECT MENDING',
                'transit_terima_qc_reject' => 'TRANSIT TERIMA QC REJECT',
                'qc_reject'                => 'QC REJECT',
            ],
        ],
        'DC' => [
            'text' => [
                'ws'    => 'no_ws',
                'buyer' => 'buyer',
                'style' => 'style',
                'color' => 'color',
                'size'  => 'size',
                'panel' => 'panel',
                'part'  => 'part',
            ],
            'qty' => [
                'mutasi dc'                     => 'mutasi_dc',
                'mutasi secondary dalam'        => 'mutasi_secondary_dalam',
                'mutasi secondary luar'         => 'mutasi_secondary_luar',
                'terima transit secondary luar' => 'terima_transit_secondary_luar',
            ],
            'required' => ['no_ws', 'buyer', 'style', 'color', 'size', 'panel', 'part'],
            'type_map' => [
                'mutasi_dc'                     => 'DC',
                'mutasi_secondary_dalam'        => 'DC_SECONDARY_DALAM',
                'mutasi_secondary_luar'         => 'DC_SECONDARY_LUAR',
                'terima_transit_secondary_luar' => 'TERIMA_TRANSIT_SECONDARY_LUAR',
            ],
        ],
        'CUTTING_PCS' => [
            'text' => [
                'ws'    => 'no_ws',
                'buyer' => 'buyer',
                'style' => 'style',
                'color' => 'color',
                'size'  => 'size',
                'panel' => 'panel',
                'part'  => 'part',
            ],
            'qty' => [
                'cutting' => 'cutting',
            ],
            'required' => ['no_ws', 'buyer', 'style', 'color', 'size', 'panel', 'part'],
            'type_map' => [
                'cutting' => 'CUTTING',
            ],
        ],
        'CUTTING_FABRIC' => [
            'text' => [
                'ws'      => 'ws',
                'id_roll' => 'id_roll',
                'id_item' => 'id_item',
                'satuan'  => 'satuan',
            ],
            'qty' => [
                'fabric' => 'fabric',
            ],
            'required' => ['ws', 'id_roll', 'id_item', 'satuan'],
        ],
    ];

    // Pecah nilai type_map jadi [type_report, proses]
    private function typeProses($value)
    {
        return is_array($value) ? $value : [$value, null];
    }

    // Report yang menyimpan kolom proses
    private function hasProses($typeMap)
    {
        foreach ($typeMap as $value) {
            if (is_array($value)) {
                return true;
            }
        }

        return false;
    }

    // Daftar type_report unik dari type_map
    private function typeReports($typeMap)
    {
        return array_values(array_unique(array_map(
            fn ($value) => $this->typeProses($value)[0],
            $typeMap
        )));
    }

    // Koneksi database sesuai jenis report
    private function connection($typeReport)
    {
        if ($typeReport == 'SEWING') {
            return 'mysql_sb';
        } else {
            return 'mysql';
        }
    }

    // Baca & validasi file import (belum menyimpan ke database)
    public function importData(Request $request)
    {
        $request->validate([
            'file'        => 'required|file|mimes:xlsx,xls,csv',
            'type_report' => 'required',
        ]);

        $typeReport = $request->type_report;

        if (!isset($this->templateFields[$typeReport])) {
            return response()->json([
                'status'  => 422,
                'message' => 'Import untuk jenis report ini belum tersedia'
            ], 422);
        }

        $template = $this->templateFields[$typeReport];
        $rows = Excel::toArray([], $request->file('file'))[0] ?? [];

        if (count($rows) < 2) {
            return response()->json([
                'status'  => 422,
                'message' => 'File kosong atau tidak memiliki data'
            ], 422);
        }

        // Petakan posisi kolom berdasarkan nama header, bukan urutan
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $rows[0]);
        $expected = array_merge(['tgl_saldo' => 'tgl_saldo'], $template['text'], $template['qty']);

        $index = [];
        $missing = [];

        foreach ($expected as $headerName => $field) {
            $pos = array_search($headerName, $header, true);

            if ($pos === false) {
                $missing[] = $headerName;
            } else {
                $index[$field] = $pos;
            }
        }

        if ($missing) {
            return response()->json([
                'status'  => 422,
                'message' => 'Kolom tidak sesuai template: ' . implode(', ', $missing)
            ], 422);
        }

        $data = [];

        foreach (array_slice($rows, 1) as $i => $row) {
            // Lewati baris kosong
            if (!array_filter($row, fn ($v) => trim((string) $v) !== '')) {
                continue;
            }

            $errors = [];
            $item = [
                'row'         => $i + 2,
                'type_report' => $typeReport,
            ];

            $tglSaldo = $this->parseTanggal($row[$index['tgl_saldo']] ?? null);

            if (!$tglSaldo) {
                $errors[] = 'Tanggal saldo tidak valid';
            }

            $item['tgl_saldo'] = $tglSaldo;

            foreach ($template['text'] as $field) {
                $item[$field] = trim((string) ($row[$index[$field]] ?? ''));
            }

            foreach ($template['required'] as $field) {
                if ($item[$field] === '') {
                    $errors[] = strtoupper(str_replace('no_', '', $field)) . ' wajib diisi';
                }
            }

            foreach ($template['qty'] as $headerName => $field) {
                $value = $row[$index[$field]] ?? null;

                if ($value === null || trim((string) $value) === '') {
                    $item[$field] = 0;
                } elseif (is_numeric($value)) {
                    $item[$field] = $value + 0;
                } else {
                    $item[$field] = $value;
                    $errors[] = ucwords($headerName) . ' harus angka';
                }
            }

            $item['errors'] = $errors;
            $data[] = $item;
        }

        if (!$data) {
            return response()->json([
                'status'  => 422,
                'message' => 'File tidak memiliki data'
            ], 422);
        }

        // Kumpulkan error per baris, simpan hanya jika semua baris valid
        $errors = [];

        foreach ($data as $item) {
            if ($item['errors']) {
                $errors[] = 'Baris ' . $item['row'] . ': ' . implode(', ', $item['errors']);
            }
        }

        if ($errors) {
            return response()->json([
                'status'  => 422,
                'message' => 'Data tidak valid',
                'errors'  => $errors,
            ], 422);
        }

        $inserted = $this->simpanImport($typeReport, $data);

        if ($inserted === 0) {
            return response()->json([
                'status'  => 422,
                'message' => 'Semua qty bernilai 0, tidak ada data yang disimpan'
            ], 422);
        }

        return response()->json([
            'status'  => 200,
            'message' => count($data) . ' baris berhasil diimport',
        ]);
    }

    // Simpan Cutting Fabric: 1 baris Excel = 1 baris wip_adjustment_fabric
    private function simpanImportFabric($data)
    {
        // id_roll F2 yang ada di mut_cut_fab_saldo_tmp => REKAP
        $rollF2 = array_values(array_unique(array_filter(
            array_column($data, 'id_roll'),
            fn ($idRoll) => strtoupper(substr($idRoll, 0, 2)) === 'F2'
        )));

        $rollRekap = [];

        foreach (array_chunk($rollF2, 500) as $chunk) {
            $found = DB::connection('mysql')
                ->table('mut_cut_fab_saldo_tmp')
                ->whereIn('id_roll', $chunk)
                ->distinct()
                ->pluck('id_roll')
                ->all();

            $rollRekap = array_merge($rollRekap, $found);
        }

        $rollRekap = array_flip($rollRekap);
        $username = Auth::user()->username;
        $now = now();
        $insert = [];

        foreach ($data as $item) {
            if (empty($item['fabric'])) {
                continue;
            }

            $insert[] = [
                'tgl_saldo'           => $item['tgl_saldo'],
                'ws'                  => $item['ws'],
                'id_roll'             => $item['id_roll'],
                'id_item'             => $item['id_item'],
                'satuan'              => $item['satuan'],
                'qty'                 => $item['fabric'],
                'type'                => isset($rollRekap[$item['id_roll']]) ? 'REKAP' : 'NON REKAP',
                'status'              => 'Y',
                'created_by_username' => $username,
                'created_at'          => $now,
            ];
        }

        DB::connection('mysql')->transaction(function () use ($insert) {
            foreach (array_chunk($insert, 500) as $chunk) {
                DB::connection('mysql')->table('wip_adjustment_fabric')->insert($chunk);
            }
        });

        return count($insert);
    }

    // Simpan hasil import: 1 baris per kolom qty yang tidak 0
    private function simpanImport($typeReport, $data)
    {
        if ($typeReport == 'CUTTING_FABRIC') {
            return $this->simpanImportFabric($data);
        }

        $typeMap = $this->templateFields[$typeReport]['type_map'];
        $insert = [];
        $username = Auth::user()->username;
        $now = now();
        $hasProses = $this->hasProses($typeMap);
        $keyColumns = array_values($this->templateFields[$typeReport]['text']);

        foreach ($data as $item) {
            foreach ($typeMap as $field => $value) {
                if (empty($item[$field])) {
                    continue;
                }

                [$type, $proses] = $this->typeProses($value);

                $row = [
                    'tgl_saldo'   => $item['tgl_saldo'],
                    'type_report' => $type,
                ];

                // Kolom teks sesuai template (DC ikut panel & part)
                foreach ($keyColumns as $column) {
                    $row[$column] = $item[$column];
                }

                $row += [
                    'qty'         => $item[$field],
                    'status'      => 'Y',
                    'created_by_username' => $username,
                    'created_at'  => $now,
                ];

                // Semua baris dalam 1 insert harus punya kolom yang sama
                if ($hasProses) {
                    $row['proses'] = $proses;
                }

                $insert[] = $row;
            }
        }

        $connection = $this->connection($typeReport);

        DB::connection($connection)->transaction(function () use ($insert, $connection) {
            foreach (array_chunk($insert, 500) as $chunk) {
                DB::connection($connection)->table('wip_adjustment')->insert($chunk);
            }
        });

        return count($insert);
    }

    // Tanggal dari Excel bisa berupa serial number atau teks
    private function parseTanggal($value)
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value)->format('Y-m-d');
        }

        foreach (['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d'] as $format) {
            $date = \DateTime::createFromFormat('!' . $format, trim($value));

            if ($date && $date->format($format) === trim($value)) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    // Data index: baris per type_report digabung jadi 1 baris per tanggal + ws + buyer + style + color + size
    public function getData(Request $request)
    {
        $typeReport = $request->type_report;

        if (!isset($this->templateFields[$typeReport])) {
            return DataTables::of([])->toJson();
        }

        if ($typeReport == 'CUTTING_FABRIC') {
            $data = DB::connection('mysql')->select("
                SELECT
                    a.id,
                    a.tgl_saldo AS tgl_saldo_raw,
                    DATE_FORMAT(a.tgl_saldo, '%d-%m-%Y') AS tgl_saldo,
                    'CUTTING FABRIC' AS type_report,
                    a.ws,
                    a.id_roll,
                    a.id_item,
                    a.satuan,
                    a.qty AS fabric,
                    a.type,
                    a.created_at,
                    a.created_by_username,
                    a.status
                FROM
                    wip_adjustment_fabric a
                WHERE
                    a.tgl_saldo BETWEEN ? AND ?
                ORDER BY
                    a.tgl_saldo DESC,
                    a.ws,
                    a.id_roll
            ", [$request->dateFrom, $request->dateTo]);

            return DataTables::of($data)->toJson();
        }

        $typeMap = $this->templateFields[$typeReport]['type_map'];

        $pivot = [];
        $bindings = [];

        foreach ($typeMap as $field => $value) {
            [$type, $proses] = $this->typeProses($value);

            if ($proses === null) {
                $pivot[] = "SUM(CASE WHEN a.type_report = ? THEN a.qty ELSE 0 END) AS $field";
                $bindings[] = $type;
            } else {
                $pivot[] = "SUM(CASE WHEN a.type_report = ? AND a.proses = ? THEN a.qty ELSE 0 END) AS $field";
                $bindings[] = $type;
                $bindings[] = $proses;
            }
        }

        $types = $this->typeReports($typeMap);
        $placeholders = implode(', ', array_fill(0, count($types), '?'));

        // Kolom pengelompokan sesuai template (DC ikut panel & part)
        $keyColumns = implode(', ', array_map(
            fn ($column) => "a.$column",
            array_values($this->templateFields[$typeReport]['text'])
        ));
        $bindings = array_merge(
            [str_replace('_', ' ', $typeReport)],
            $bindings,
            [$request->dateFrom, $request->dateTo],
            $types
        );

        $data = DB::connection($this->connection($typeReport))->select("
            SELECT
                a.tgl_saldo AS tgl_saldo_raw,
                DATE_FORMAT(a.tgl_saldo, '%d-%m-%Y') AS tgl_saldo,
                ? AS type_report,
                $keyColumns,
                " . implode(",\n                ", $pivot) . ",
                MAX(a.created_at) AS created_at,
                MAX(a.created_by_username) AS created_by_username,
                a.status
            FROM
                wip_adjustment a
            WHERE
                a.tgl_saldo BETWEEN ? AND ?
                AND a.type_report IN ($placeholders)
            GROUP BY
                a.tgl_saldo,
                $keyColumns,
                a.status
            ORDER BY
                a.tgl_saldo DESC,
                a.no_ws,
                a.color,
                a.size
        ", $bindings);

        return DataTables::of($data)->toJson();
    }

    // Cancel 1 baris gabungan: semua type_report di grup itu jadi status N
    public function cancel(Request $request)
    {
        $request->validate([
            'type_report' => 'required',
            'tgl_saldo'   => 'required|date',
        ]);

        if (!isset($this->templateFields[$request->type_report])) {
            return response()->json([
                'status'  => 422,
                'message' => 'Jenis report tidak dikenali'
            ], 422);
        }

        if (checkClosingDate($request->tgl_saldo)) {
            return response()->json([
                'status'  => 422,
                'message' => 'Periode sudah closing, adjustment tidak bisa di-cancel'
            ], 422);
        }

        if ($request->type_report == 'CUTTING_FABRIC') {
            $query = DB::connection('mysql')
                ->table('wip_adjustment_fabric')
                ->where('id', $request->id)
                ->where('status', 'Y');
        } else {
            $typeMap = $this->templateFields[$request->type_report]['type_map'];

            $query = DB::connection($this->connection($request->type_report))
                ->table('wip_adjustment')
                ->where('tgl_saldo', $request->tgl_saldo)
                ->whereIn('type_report', $this->typeReports($typeMap))
                ->where('status', 'Y');

            // Kolom kunci bisa null atau '' (request '' otomatis jadi null), samakan keduanya
            foreach ($this->templateFields[$request->type_report]['text'] as $column) {
                $query->whereRaw("IFNULL($column, '') = ?", [$request->input($column) ?? '']);
            }
        }

        $updated = $query->update(['status' => 'N']);

        if ($updated === 0) {
            return response()->json([
                'status'  => 422,
                'message' => 'Data tidak ditemukan atau sudah dicancel'
            ], 422);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Data berhasil dicancel'
        ]);
    }
}