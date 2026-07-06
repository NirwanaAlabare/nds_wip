<?php

namespace App\Http\Controllers;

use App\Imports\ImportIE_MasterProcess;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use DB;
use QrCode;
use PDF;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AssetMesinTambahController extends Controller
{
    public function asset_mesin_tambah(Request $request)
    {
        $tgl_trans = '2024-01-01';
        $supplierList = DB::connection('mysql_sb')->table('mastersupplier')
            ->select('id_supplier', 'Supplier')
            ->where('tipe_sup', '=', 'S')
            ->orderBy('Supplier', 'ASC')
            ->get();

        $bpbList = DB::connection('mysql_sb')->select("
        WITH bpb AS (
            SELECT
                b.id_item,
                b.bpbdate,
                b.bpbno,
                b.bpbno_int,
                ms.supplier,
                SUM(b.qty) AS qty
            FROM bpb b
            INNER JOIN mastersupplier ms ON ms.Id_Supplier = b.id_supplier
            INNER JOIN masteritem mi ON mi.id_item = b.id_item
            WHERE b.bpbdate >= '$tgl_trans'
                AND b.bpbno LIKE 'N%'
                AND mi.n_code_category = '4'
                AND mi.goods_code NOT LIKE 'SEW%'
                AND b.cancel = 'N'
            GROUP BY b.id_item, b.bpbno
        ),
        p AS (
            SELECT
                bpbno,
                id_item,
                COUNT(*) AS tot
            FROM laravel_nds.asset_penerimaan_mesin
            GROUP BY bpbno, id_item
        )

        SELECT
            bpb.*,
            COALESCE(p.tot, 0) AS tot
        FROM bpb
        LEFT JOIN p
            ON bpb.bpbno = p.bpbno
            AND bpb.id_item = p.id_item
        WHERE bpb.qty > COALESCE(p.tot, 0)
        GROUP BY bpbno
        ORDER BY bpbdate ASC;
        ");

        $jenisMasterList = DB::table('asset_master_kd_jenis')->select('kd_jenis', 'nm_jenis')->get();
        $merkMasterList = DB::table('asset_master_kd_merk')->select('kd_merk', 'nm_merk')->get();
        $jenisMap = $jenisMasterList->keyBy('kd_jenis');
        $merkMap = $merkMasterList->keyBy('kd_merk');

        $jenisList = DB::table('asset_master_jenis_mesin')
            ->select('id_jenis', 'kd_jenis', 'kd_merk', 'tipe', 'id_supplier')
            ->orderBy('kd_jenis', 'ASC')
            ->get();
        foreach ($jenisList as $row) {
            $row->jenis = $jenisMap[$row->kd_jenis]->nm_jenis ?? $row->kd_jenis;
            $row->merk = $merkMap[$row->kd_merk]->nm_merk ?? $row->kd_merk;
        }

        if ($request->ajax()) {
            $data_input = DB::select("SELECT * FROM asset_master_jenis_mesin ORDER BY id_jenis DESC");

            $supplierMap = $supplierList->keyBy('id_supplier');
            foreach ($data_input as $row) {
                $row->supplier = $supplierMap[$row->id_supplier]->Supplier ?? '-';
                $row->jenis = $jenisMap[$row->kd_jenis]->nm_jenis ?? $row->kd_jenis;
                $row->merk = $merkMap[$row->kd_merk]->nm_merk ?? $row->kd_merk;
            }

            return DataTables::of($data_input)->toJson();
        }

        // For non-AJAX (initial page load)
        return view('asset_management.mesin_tambah', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-mesin',
            'subPage' => 'asset_mesin_tambah',
            'containerFluid' => true,
            'supplierList' => $supplierList,
            'bpbList' => $bpbList,
            'jenisList' => $jenisList,
        ]);
    }

    public function get_penerimaan_mesin(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;
        $data = DB::select("SELECT id_bpb, tgl_trans, DATE_FORMAT(tgl_trans, '%d-%m-%Y') as tgl_trans_fix, a.id_item, a.id_jenis, count(*) as tot_qty, bcno, jenis_dok,
        SUM(CASE WHEN a.serial_number IS NOT NULL AND a.serial_number <> '' THEN 1 ELSE 0 END) as tot_filled,
        SUM(CASE WHEN a.foto IS NOT NULL AND a.foto <> '' THEN 1 ELSE 0 END) as tot_foto,
        SUM(CASE WHEN a.serial_number IS NOT NULL AND a.serial_number <> '' AND a.foto IS NOT NULL AND a.foto <> '' THEN 1 ELSE 0 END) as tot_complete,
        a.bpbno_int, bpb.unit, bpb.id_supplier, supplier, nm_jenis, nm_merk, a.id_item, mi.itemdesc, c.tipe
FROM asset_penerimaan_mesin a
left join asset_master_jenis_mesin c on a.id_jenis = c.id_jenis
left join signalbit_erp.bpb on a.id_bpb = bpb.id
left join signalbit_erp.mastersupplier ms on bpb.id_supplier = ms.id_supplier
left join asset_master_kd_jenis d on c.kd_jenis = d.kd_jenis
left join asset_master_kd_merk e on c.kd_merk = e.kd_merk
left join signalbit_erp.masteritem mi on a.id_item = mi.id_item
where a.tgl_trans >= ? and a.tgl_trans <= ?
group by id_bpb, id_item, a.bpbno_int
", [$tgl_awal, $tgl_akhir]);

        return DataTables::of($data)->toJson();
    }

    public function export_excel_penerimaan_mesin(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $data = DB::select("SELECT id_bpb, tgl_trans, a.id_item, a.id_jenis, count(*) as tot_qty,
        SUM(CASE WHEN a.serial_number IS NOT NULL AND a.serial_number <> '' THEN 1 ELSE 0 END) as tot_filled,
        SUM(CASE WHEN a.foto IS NOT NULL AND a.foto <> '' THEN 1 ELSE 0 END) as tot_foto,
        SUM(CASE WHEN a.serial_number IS NOT NULL AND a.serial_number <> '' AND a.foto IS NOT NULL AND a.foto <> '' THEN 1 ELSE 0 END) as tot_complete,
        a.bpbno_int, bpb.unit, bpb.id_supplier, supplier, nm_jenis, nm_merk, a.id_item, mi.itemdesc, c.tipe
        FROM asset_penerimaan_mesin a
        left join asset_master_jenis_mesin c on a.id_jenis = c.id_jenis
        left join signalbit_erp.bpb on a.id_bpb = bpb.id
        left join signalbit_erp.mastersupplier ms on bpb.id_supplier = ms.id_supplier
        left join asset_master_kd_jenis d on c.kd_jenis = d.kd_jenis
        left join asset_master_kd_merk e on c.kd_merk = e.kd_merk
        left join signalbit_erp.masteritem mi on a.id_item = mi.id_item
        where a.tgl_trans >= ? and a.tgl_trans <= ?
        group by id_bpb, id_item, a.bpbno_int
        order by tgl_trans ASC
        ", [$tgl_awal, $tgl_akhir]);

        $rows = array_map(fn($r) => (array) $r, $data);

        $excel = FastExcel::create('Penerimaan Mesin');
        $sheet = $excel->getSheet();

        $sheet->writeRow(['Laporan Penerimaan Mesin'])->applyFontStyleBold()->applyFontSize(16);
        $sheet->writeRow(["Periode {$tgl_awal} s/d {$tgl_akhir}"])->applyFontStyleBold();
        $sheet->writeRow([]);

        $sheet->writeRow([
            'No',
            'Tgl Transaksi',
            'BPB',
            'Supplier',
            'Jenis',
            'Merk',
            'Nama Mesin',
            'Tipe',
            'Total Unit',
            'Sudah Lengkap',
        ])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $no = 1;
        foreach ($rows as $r) {
            $sheet->writeRow([
                $no++,
                $r['tgl_trans_fix'] ?? '',
                $r['bpbno_int'] ?? '',
                $r['supplier'] ?? '',
                $r['nm_jenis'] ?? '',
                $r['nm_merk'] ?? '',
                $r['itemdesc'] ?? '',
                $r['tipe'] ?? '',
                $r['tot_qty'] ?? 0,
                $r['tot_complete'] ?? 0,
            ])->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        $filename = "Laporan Penerimaan Mesin {$tgl_awal} sd {$tgl_akhir}.xlsx";

        // FastExcel::download() echo file langsung via header()+readfile() tanpa mengembalikan Response,
        // sehingga Laravel ikut mengirim response kosong di belakangnya & merusak isi file xlsx.
        // Simpan ke temp file lalu kirim lewat response()->download() bawaan Laravel supaya bersih.
        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('xlsx_export_') . '.xlsx';
        $excel->save($tmpFile);

        return response()->download($tmpFile, $filename)->deleteFileAfterSend(true);
    }

    public function get_penerimaan_mesin_unit(Request $request)
    {
        $request->validate([
            'id_bpb' => 'nullable',
            'id_item' => 'nullable',
            'bpbno_int' => 'nullable',
        ]);

        $query = DB::table('asset_penerimaan_mesin')
            ->select('id', 'serial_number', 'foto', 'kode_qr');

        if ($request->id_bpb) {
            // Unit hasil penerimaan dari BPB: id_item bisa sama di beberapa dokumen, jadi dikunci id_bpb + id_item
            $query->where('id_bpb', $request->id_bpb)->where('id_item', $request->id_item);
        } else {
            // Unit hasil Inject (tanpa BPB): id_bpb & id_item kosong, jadi dikunci nomor bpbno_int (unik per batch inject)
            $query->whereNull('id_bpb')->where('bpbno_int', $request->bpbno_int);
        }

        $units = $query->orderBy('id', 'ASC')->get();

        foreach ($units as $unit) {
            $unit->qr = $unit->kode_qr
                ? base64_encode(QrCode::format('svg')->size(80)->generate($unit->kode_qr))
                : null;
        }

        return response()->json($units);
    }

    private function get_penerimaan_mesin_qr_units(
        ?string $tgl_awal,
        ?string $tgl_akhir,
        ?string $bpbno = null,
        ?string $id_item = null,
        ?string $id_supplier = null,
        ?string $ids = null,
        ?string $bgColorHex = null
    ) {
        $query = DB::table('asset_penerimaan_mesin as a')
            ->select('a.id', 'a.serial_number', 'a.kode_qr')
            ->leftJoin('signalbit_erp.bpb as bpb', 'a.id_bpb', '=', 'bpb.id');

        if ($ids) {
            $query->whereIn('a.id', array_filter(explode(',', $ids)));
        } else {
            $query->whereBetween('a.tgl_trans', [$tgl_awal, $tgl_akhir])
                ->when($bpbno, function ($query) use ($bpbno) {
                    $query->where('a.bpbno_int', $bpbno);
                })
                ->when($id_item, function ($query) use ($id_item) {
                    $query->where('a.id_item', $id_item);
                })
                ->when($id_supplier, function ($query) use ($id_supplier) {
                    $query->where('bpb.id_supplier', $id_supplier);
                });
        }

        $units = $query->whereNotNull('a.serial_number')
            ->where('a.serial_number', '<>', '')
            ->whereNotNull('a.foto')
            ->where('a.foto', '<>', '')
            ->whereNotNull('a.kode_qr')
            ->where('a.kode_qr', '<>', '')
            ->orderBy('a.id', 'ASC')
            ->get();

        foreach ($units as $unit) {
            $qr = QrCode::format('svg')->size(80);
            if ($bgColorHex && preg_match('/^[0-9a-fA-F]{6}$/', $bgColorHex)) {
                $qr->backgroundColor(...array_map('hexdec', str_split($bgColorHex, 2)));
            }
            $unit->qr = base64_encode($qr->generate($unit->kode_qr));
        }

        return $units;
    }

    public function get_penerimaan_mesin_qr_list(Request $request)
    {
        $units = $this->get_penerimaan_mesin_qr_units(
            $request->tgl_awal,
            $request->tgl_akhir,
            $request->bpbno,
            $request->id_item,
            $request->id_supplier
        );

        return response()->json($units);
    }

    public function get_penerimaan_mesin_qr_filter_options(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $data = DB::select("
            SELECT DISTINCT a.bpbno_int, a.id_item, mi.itemdesc, bpb.id_supplier, ms.supplier
            FROM asset_penerimaan_mesin a
            LEFT JOIN signalbit_erp.bpb ON a.id_bpb = bpb.id
            LEFT JOIN signalbit_erp.mastersupplier ms ON bpb.id_supplier = ms.id_supplier
            LEFT JOIN signalbit_erp.masteritem mi ON a.id_item = mi.id_item
            WHERE a.tgl_trans >= ? AND a.tgl_trans <= ?
            ORDER BY mi.itemdesc ASC
        ", [$tgl_awal, $tgl_akhir]);

        return response()->json($data);
    }

    public function print_qr_list_mesin(Request $request)
    {
        $color = ltrim((string) $request->color, '#');
        $color = preg_match('/^[0-9a-fA-F]{6}$/', $color) ? strtolower($color) : 'f8bbd0';

        $units = $this->get_penerimaan_mesin_qr_units(
            $request->tgl_awal,
            $request->tgl_akhir,
            $request->bpbno,
            $request->id_item,
            $request->id_supplier,
            $request->ids,
            $color
        );

        return view('asset_management.print_qr_mesin_list', [
            'units' => $units,
            'color' => $color,
        ]);
    }

    public function print_qr_mesin(Request $request, $id)
    {
        $unit = DB::table('asset_penerimaan_mesin')->where('id', $id)->first();

        if (!$unit || !$unit->kode_qr) {
            abort(404);
        }

        $pdf = PDF::loadView('asset_management.print_qr_mesin', [
            'kode_qr' => $unit->kode_qr,
            'serial_number' => $unit->serial_number,
        ])->setPaper([0, 0, 200, 200]);

        return $pdf->stream('QR-' . $unit->kode_qr . '.pdf');
    }

    public function store_penerimaan_mesin_unit(Request $request)
    {
        $request->validate([
            'units' => 'required|array',
            'units.*.id' => 'required|integer',
            'units.*.serial_number' => 'nullable|string|max:255',
            'units.*.foto' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
        ]);

        $timestamp = Carbon::now();

        foreach ($request->units as $unit) {
            $serialNumber = $unit['serial_number'] ?? null;

            if ($serialNumber) {
                $idJenis = DB::table('asset_penerimaan_mesin')->where('id', $unit['id'])->value('id_jenis');

                $duplicate = DB::table('asset_penerimaan_mesin')
                    ->where('id_jenis', $idJenis)
                    ->where('serial_number', $serialNumber)
                    ->where('id', '!=', $unit['id'])
                    ->exists();

                if ($duplicate) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Serial Number "' . $serialNumber . '" sudah digunakan pada jenis mesin yang sama',
                    ], 422);
                }
            }

            $update = [
                'serial_number' => $serialNumber,
                'status' => 'IDLE',
                'updated_at' => $timestamp,
            ];

            if (isset($unit['foto']) && $unit['foto'] instanceof \Illuminate\Http\UploadedFile) {
                $filename = $unit['id'] . '.' . $unit['foto']->getClientOriginalExtension();
                $unit['foto']->storeAs('public/gambar_penerimaan_mesin', $filename);
                $update['foto'] = $filename;
            }

            DB::table('asset_penerimaan_mesin')->where('id', $unit['id'])->update($update);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data unit mesin berhasil disimpan.',
        ]);
    }

    public function get_bpb_detail(Request $request)
    {
        $data = DB::connection('mysql_sb')->select("SELECT id, b.id_item, b.bpbno, b.bpbno_int, mi.itemdesc, qty, unit, b.id_supplier FROM bpb b
                INNER JOIN mastersupplier ms ON ms.Id_Supplier = b.id_supplier
                INNER JOIN masteritem mi on b.id_item = mi.id_item
                WHERE bpbno = ? AND mi.n_code_category = '4' AND b.cancel = 'N'", [$request->bpbno]);

        return DataTables::of($data)->toJson();
    }

    public function store_penerimaan_mesin(Request $request)
    {
        $request->validate([
            'id_item' => 'required',
            'id_bpb' => 'required',
            'bpbno' => 'required',
            'bpbno_int' => 'required',
            'id_jenis' => 'required|exists:asset_master_jenis_mesin,id_jenis',
            'qty' => 'required|integer|min:1',
        ]);

        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $jenis = DB::table('asset_master_jenis_mesin')
            ->select('kd_jenis', 'kd_merk')
            ->where('id_jenis', $request->id_jenis)
            ->first();

        $prefix = $jenis->kd_jenis . '_' . $jenis->kd_merk . '_';

        $lastNumber = DB::table('asset_penerimaan_mesin')
            ->where('kode_qr', 'LIKE', $prefix . '%')
            ->selectRaw('MAX(CAST(SUBSTRING(kode_qr, ?) AS UNSIGNED)) as last_number', [strlen($prefix) + 1])
            ->value('last_number');

        $nextNumber = ($lastNumber ?? 0) + 1;

        for ($i = 0; $i < $request->qty; $i++) {
            DB::insert("INSERT INTO asset_penerimaan_mesin (
                tgl_trans,
                id_item,
                id_bpb,
                bpbno,
                bpbno_int,
                id_jenis,
                kode_qr,
                created_by,
                created_at,
                updated_at
            ) VALUES (?,?,?,?,?,?,?,?,?,?)", [
                $timestamp->format('Y-m-d'),
                $request->id_item,
                $request->id_bpb,
                $request->bpbno,
                $request->bpbno_int,
                $request->id_jenis,
                $prefix . $nextNumber,
                $user,
                $timestamp,
                $timestamp
            ]);

            $nextNumber++;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Mesin berhasil diterima sebanyak ' . $request->qty . ' unit',
        ]);
    }

    public function store_inject(Request $request)
    {
        $request->validate([
            'tgl_trans' => 'required|date',
            'id_jenis' => 'required|exists:asset_master_jenis_mesin,id_jenis',
            'qty' => 'required|integer|min:1',
        ]);

        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $tglTrans = Carbon::parse($request->tgl_trans);

        $jenis = DB::table('asset_master_jenis_mesin')
            ->select('kd_jenis', 'kd_merk')
            ->where('id_jenis', $request->id_jenis)
            ->first();

        // Nomor Inject: INJ/MSN/{bulan}{tahun}/{5 digit increment}, reset tiap bulan (berdasarkan tanggal transaksi)
        $periode = $tglTrans->format('mY');
        $bpbPrefix = 'INJ/MSN/' . $periode . '/';

        $lastBpbNumber = DB::table('asset_penerimaan_mesin')
            ->where('bpbno_int', 'LIKE', $bpbPrefix . '%')
            ->selectRaw('MAX(CAST(SUBSTRING(bpbno_int, ?) AS UNSIGNED)) as last_number', [strlen($bpbPrefix) + 1])
            ->value('last_number');

        $bpbnoInt = $bpbPrefix . str_pad(($lastBpbNumber ?? 0) + 1, 5, '0', STR_PAD_LEFT);

        $qrPrefix = $jenis->kd_jenis . '_' . $jenis->kd_merk . '_';

        $lastQrNumber = DB::table('asset_penerimaan_mesin')
            ->where('kode_qr', 'LIKE', $qrPrefix . '%')
            ->selectRaw('MAX(CAST(SUBSTRING(kode_qr, ?) AS UNSIGNED)) as last_number', [strlen($qrPrefix) + 1])
            ->value('last_number');

        $nextQrNumber = ($lastQrNumber ?? 0) + 1;

        for ($i = 0; $i < $request->qty; $i++) {
            DB::insert("INSERT INTO asset_penerimaan_mesin (
                tgl_trans,
                id_item,
                id_bpb,
                bpbno,
                bpbno_int,
                id_jenis,
                kode_qr,
                created_by,
                created_at,
                updated_at
            ) VALUES (?,?,?,?,?,?,?,?,?,?)", [
                $tglTrans->format('Y-m-d'),
                null,
                null,
                null,
                $bpbnoInt,
                $request->id_jenis,
                $qrPrefix . $nextQrNumber,
                $user,
                $timestamp,
                $timestamp
            ]);

            $nextQrNumber++;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Mesin berhasil di-inject sebanyak ' . $request->qty . ' unit (No: ' . $bpbnoInt . ')',
        ]);
    }
}
