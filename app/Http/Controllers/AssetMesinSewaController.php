<?php

namespace App\Http\Controllers;

use App\Imports\ImportIE_MasterProcess;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use DB;
use PDF;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AssetMesinSewaController extends Controller
{
    public function asset_mesin_sewa(Request $request)
    {
        $tgl_trans = '2026-05-01';
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
                AND mi.goods_code LIKE 'SEW%'
                AND b.cancel = 'N'
            GROUP BY b.id_item, b.bpbno
        ),
        p AS (
            SELECT
                bpbno,
                id_item,
                COUNT(*) AS tot
            FROM laravel_nds.asset_penerimaan_mesin_sewa
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

        $qrList = DB::table('asset_master_mesin_sewa_qr')
            ->select('kode_qr')
            ->orderBy('kode_qr', 'ASC')
            ->get();

        // For non-AJAX (initial page load)
        return view('asset_management.mesin_sewa', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-mesin',
            'subPage' => 'asset_mesin_sewa',
            'containerFluid' => true,
            'supplierList' => $supplierList,
            'bpbList' => $bpbList,
            'jenisList' => $jenisList,
            'jenisMasterList' => $jenisMasterList,
            'merkMasterList' => $merkMasterList,
            'qrList' => $qrList,
        ]);
    }

    public function get_penerimaan_mesin_sewa(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $data = DB::select("SELECT id_bpb, tgl_trans, a.id_item, count(*) as tot_qty, bcno, jenis_dok,
        SUM(CASE WHEN a.serial_number IS NOT NULL AND a.serial_number <> '' THEN 1 ELSE 0 END) as tot_filled,
        SUM(CASE WHEN a.foto IS NOT NULL AND a.foto <> '' THEN 1 ELSE 0 END) as tot_foto,
        SUM(CASE WHEN a.serial_number IS NOT NULL AND a.serial_number <> '' AND a.foto IS NOT NULL AND a.foto <> '' THEN 1 ELSE 0 END) as tot_complete,
        a.bpbno_int, bpb.unit, bpb.id_supplier, supplier, nm_jenis, nm_merk, a.id_item, mi.itemdesc, tipe
        FROM asset_penerimaan_mesin_sewa a
        left join signalbit_erp.bpb on a.id_bpb = bpb.id
        left join signalbit_erp.mastersupplier ms on bpb.id_supplier = ms.id_supplier
        left join signalbit_erp.masteritem mi on a.id_item = mi.id_item
        where a.tgl_trans >= '$tgl_awal' and a.tgl_trans <= '$tgl_akhir'
        group by id_bpb, id_item
        ");

        return DataTables::of($data)->toJson();
    }

    public function get_bpb_detail_sewa(Request $request)
    {
        $data = DB::connection('mysql_sb')->select("SELECT id, b.id_item, b.bpbno, b.bpbno_int, mi.itemdesc, qty, unit, b.id_supplier FROM bpb b
                INNER JOIN mastersupplier ms ON ms.Id_Supplier = b.id_supplier
                INNER JOIN masteritem mi on b.id_item = mi.id_item
                WHERE bpbno = ? AND mi.n_code_category = '4' AND mi.goods_code LIKE 'SEW%' AND b.cancel = 'N'", [$request->bpbno]);

        return DataTables::of($data)->toJson();
    }

    public function store_penerimaan_mesin_sewa(Request $request)
    {
        $request->validate([
            'id_item' => 'required',
            'id_bpb' => 'required',
            'bpbno' => 'required',
            'bpbno_int' => 'required',
            'qty' => 'required|integer|min:1',
        ]);

        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        for ($i = 0; $i < $request->qty; $i++) {
            DB::insert("INSERT INTO asset_penerimaan_mesin_sewa (
                tgl_trans,
                id_item,
                id_bpb,
                bpbno,
                bpbno_int,
                masa_kontrak,
                created_by,
                created_at,
                updated_at
            ) VALUES (?,?,?,?,?,?,?,?,?)", [
                $timestamp->format('Y-m-d'),
                $request->id_item,
                $request->id_bpb,
                $request->bpbno,
                $request->bpbno_int,
                30,
                $user,
                $timestamp,
                $timestamp
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Mesin sewa berhasil disimpan sebanyak ' . $request->qty . ' unit',
        ]);
    }

    public function get_penerimaan_mesin_sewa_unit(Request $request)
    {
        $request->validate([
            'id_bpb' => 'required',
            'id_item' => 'required',
        ]);

        $units = DB::table('asset_penerimaan_mesin_sewa')
            ->select('id', 'serial_number', 'foto', 'kode_qr', 'nm_jenis', 'nm_merk', 'tipe', 'tgl_awal_kontrak', 'masa_kontrak', 'tgl_akhir_kontrak')
            ->where('id_bpb', $request->id_bpb)
            ->where('id_item', $request->id_item)
            ->orderBy('id', 'ASC')
            ->get();

        return response()->json($units);
    }

    public function store_penerimaan_mesin_sewa_unit(Request $request)
    {
        $request->validate([
            'units' => 'required|array',
            'units.*.id' => 'required|integer',
            'units.*.serial_number' => 'nullable|string|max:255',
            'units.*.foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'units.*.kode_qr' => 'nullable|exists:asset_master_mesin_sewa_qr,kode_qr',
            'units.*.nm_jenis' => 'nullable|string|max:255',
            'units.*.nm_merk' => 'nullable|string|max:255',
            'units.*.tipe' => 'nullable|string|max:255',
            'units.*.tgl_awal_kontrak' => 'nullable|date',
            'units.*.masa_kontrak' => 'nullable|integer|min:1',
        ]);

        $timestamp = Carbon::now();

        // Tiap request hanya kirim field yang berubah (serial_number / foto / kode_qr / nm_jenis / nm_merk / tipe / tgl_awal_kontrak / masa_kontrak),
        // jadi field lain tidak boleh ikut ditimpa kalau memang tidak dikirim.
        foreach ($request->units as $unit) {
            $update = ['updated_at' => $timestamp];

            if (array_key_exists('serial_number', $unit)) {
                $update['serial_number'] = $unit['serial_number'] ?: null;
            }

            if (array_key_exists('kode_qr', $unit)) {
                $update['kode_qr'] = $unit['kode_qr'] ?: null;
            }

            if (array_key_exists('nm_jenis', $unit)) {
                $update['nm_jenis'] = $unit['nm_jenis'] ?: null;
            }

            if (array_key_exists('nm_merk', $unit)) {
                $update['nm_merk'] = $unit['nm_merk'] ?: null;
            }

            if (array_key_exists('tipe', $unit)) {
                $update['tipe'] = $unit['tipe'] ?: null;
            }

            if (array_key_exists('tgl_awal_kontrak', $unit)) {
                $update['tgl_awal_kontrak'] = $unit['tgl_awal_kontrak'] ?: null;
            }

            if (array_key_exists('masa_kontrak', $unit)) {
                $update['masa_kontrak'] = $unit['masa_kontrak'] ?: null;
            }

            // Tanggal Akhir Kontrak dihitung otomatis dari Tanggal Terima + Masa Kontrak (hari),
            // selalu dihitung ulang dari nilai efektif (gabungan data lama & yang baru dikirim) supaya tetap akurat
            // walau Tanggal Terima dan Masa Kontrak disimpan lewat request yang berbeda.
            if (array_key_exists('tgl_awal_kontrak', $update) || array_key_exists('masa_kontrak', $update)) {
                $current = DB::table('asset_penerimaan_mesin_sewa')->where('id', $unit['id'])->first();
                $tglAwalKontrak = array_key_exists('tgl_awal_kontrak', $update) ? $update['tgl_awal_kontrak'] : $current->tgl_awal_kontrak;
                $masaKontrak = array_key_exists('masa_kontrak', $update) ? $update['masa_kontrak'] : $current->masa_kontrak;

                // Default Masa Kontrak ke 30 hari kalau Tanggal Terima sudah diisi tapi Masa Kontrak belum pernah diisi
                if ($tglAwalKontrak && !$masaKontrak) {
                    $masaKontrak = 30;
                    $update['masa_kontrak'] = 30;
                }

                $update['tgl_akhir_kontrak'] = ($tglAwalKontrak && $masaKontrak)
                    ? Carbon::parse($tglAwalKontrak)->addDays((int) $masaKontrak)->format('Y-m-d')
                    : null;
            }

            if (isset($unit['foto']) && $unit['foto'] instanceof \Illuminate\Http\UploadedFile) {
                $filename = $unit['id'] . '.' . $unit['foto']->getClientOriginalExtension();
                $unit['foto']->storeAs('public/gambar_penerimaan_mesin_sewa', $filename);
                $update['foto'] = $filename;
            }

            DB::table('asset_penerimaan_mesin_sewa')->where('id', $unit['id'])->update($update);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data unit mesin sewa berhasil disimpan.',
        ]);
    }
}
