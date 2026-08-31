<?php

namespace App\Http\Controllers\ReportBc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ReportBc\PemasukanService;
use App\Services\ReportBc\PengeluaranService;
use App\Services\ReportBc\MutasiService;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ReportBcController extends Controller
{
    protected $pemasukanService;
    protected $pengeluaranService;
    protected $mutasiService;


    public function __construct(PemasukanService $pemasukanService, PengeluaranService $pengeluaranService,MutasiService $mutasiService) {
        $this->pemasukanService = $pemasukanService;
        $this->pengeluaranService = $pengeluaranService;
        $this->mutasiService = $mutasiService;
    }

    public function index(Request $request)
    {
        return view('report-bc.report_bc.index', ['page' => 'dashboard-report-bc']);
    }

    public function showReport(Request $request, $jenis, $kategori, $kategoriBarang)
    {
        $fromDate = $request->input('from');
        $toDate = $request->input('to');
        $filterBy = $request->input('filter_by', 'dokumen');
        $export = $request->input('export');

        if (!$fromDate || !$toDate) {
            return redirect()->route('dashboard-report-bc')->with('error', 'Silakan tentukan range tanggal terlebih dahulu.');
        }


        if ($jenis === 'mutasi_bahan_baku') {
            $dataLaporan = $this->mutasiService->getDataMutasiBahanBaku($fromDate, $toDate, $kategoriBarang);
            $cleanKategori = 'bahanbaku';
        } elseif ($jenis === 'mutasi_barang_jadi') {
            $dataLaporan = $this->mutasiService->getDataMutasiBarangJadi($fromDate, $toDate, $kategoriBarang);
            $cleanKategori = 'barangjadi';
        } elseif ($jenis === 'mutasi_barang_jadi_gudang') {
            $dataLaporan = $this->mutasiService->getDataMutasiBarangJadiGudang($fromDate, $toDate, $kategoriBarang);
            $cleanKategori = 'barangjadi_gudang';
        } elseif ($jenis === 'mutasi_wip') {
            $dataLaporan = $this->mutasiService->getDataMutasiWip($fromDate, $toDate);
            $cleanKategori = 'mutasiwip';
        } elseif ($jenis === 'mutasi_mesin_sparepart') {
            $dataLaporan = $this->mutasiService->getDataMutasiMesinSparepart($fromDate, $toDate, $kategoriBarang);
            $cleanKategori = $kategoriBarang;
        }  elseif ($jenis === 'mutasi_barang_sisa') {
            $dataLaporan = $this->mutasiService->getDataMutasiBarangSisa($fromDate, $toDate, $kategoriBarang);
            $cleanKategori = 'barangsisa';
        }else {
            $service = ($jenis === 'pemasukan') ? $this->pemasukanService : $this->pengeluaranService;
            $cleanKategori = preg_replace('/[^a-zA-Z0-9]/', '', $kategori);
            $methodName = 'getData' . ucfirst($cleanKategori);

            if (method_exists($service, $methodName)) {
                $dataLaporan = $service->$methodName($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang);
            } else {
                $dataLaporan = collect();
            }
        }

        if ($export == 'excel') {


            if($jenis == 'pemasukan'){
                $this->pemasukanService->exportExcel($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
            }

            if($jenis == 'pengeluaran'){
                $this->pengeluaranService->exportExcel($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
            }

            if($jenis == 'mutasi_bahan_baku'){
                $this->mutasiService->exportExcelBahanBaku($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
            }

            if($jenis == 'mutasi_barang_jadi'){
                $this->mutasiService->exportExcelBarangJadi($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
            }

            if($jenis == 'mutasi_barang_jadi_gudang'){
                $this->mutasiService->exportExcelBarangJadiGudang($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
            }

            if($jenis == 'mutasi_mesin_sparepart'){
                $this->mutasiService->exportExcelMesinSparepart($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
            }

            if($jenis == 'mutasi_barang_sisa'){
                $this->mutasiService->exportExcelBarangSisa($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
            }
            // $fileName = "Laporan_" . ucfirst($jenis) . "_" . strtoupper($cleanKategori) . "_" . date('Ymd') . ".xls";

            // return response(view('report-bc.report_bc.excel-' . $jenis, [
            //     'data' => $dataLaporan,
            //     'jenis' => $jenis,
            //     'kategori' => $kategori,
            //     'fromDate' => $fromDate,
            //     'toDate' => $toDate,
            //     'kategoriBarang' => $kategoriBarang,
            //     'filterBy' => $filterBy,
            //     'fileName' => $fileName
            // ]))
            // ->header('Content-Type', 'application/vnd.ms-excel')
            // ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        }

        $viewName = 'report-bc.report_bc.report-' . $jenis;

        if (!view()->exists($viewName)) {
            return redirect()->route('dashboard-report-bc')->with('error', 'Halaman view ' . $viewName . ' belum dibuat.');
        }

        return view($viewName, [
            'page'           => 'dashboard-report-bc',
            'jenis'          => $jenis,
            'kategori'       => $kategori,
            'fromDate'       => $fromDate,
            'toDate'         => $toDate,
            'filterBy'       => $filterBy,
            'kategoriBarang' => $kategoriBarang,
            'data'           => $dataLaporan,
        ]);
    }

    private function getFieldTanggal($filterBy)
    {
        return ($filterBy == 'transaksi') ? 'tanggal_bpb' : 'tanggal_daftar';
    }

    // --- TAB PENGELUARAN ---

    public function getDataBc33($fromDate, $toDate, $filterBy, $jenis)
    {
        $dateField = $this->getFieldTanggal($filterBy);

        dd('a');

        return collect();
    }

    public function getDataBc30($fromDate, $toDate, $filterBy, $jenis)
    {
        $dateField = $this->getFieldTanggal($filterBy);

        return collect();
    }

    public function getDataBc261($fromDate, $toDate, $filterBy, $jenis)
    {
        $dateField = $this->getFieldTanggal($filterBy);

        return collect();
    }

    public function getDataBc25($fromDate, $toDate, $filterBy, $jenis)
    {
        $dateField = $this->getFieldTanggal($filterBy);

        return collect();
    }

    public function getDataBc41($fromDate, $toDate, $filterBy, $jenis)
    {
        $dateField = $this->getFieldTanggal($filterBy);

        return collect();
    }

    public function getMutasiGudangJadi(Request $request)
    {
        $fromDate = $request->input('from');
        $toDate   = $request->input('to');
        $kategoriBarang = 'all';

        if (!$fromDate || !$toDate) {
            return response()->json(['error' => 'Tanggal from/to wajib diisi'], 400);
        }

        $draw   = intval($request->input('draw'));
        $start  = intval($request->input('start', 0));
        $length = intval($request->input('length', 25));
        $searchValue = strtolower($request->input('search.value', ''));

        $cacheKey = "mutasi_gudang_{$fromDate}_{$toDate}_{$kategoriBarang}";

        // $allData = Cache::remember($cacheKey, 300, function () use ($fromDate, $toDate, $kategoriBarang) {
        //     return $this->mutasiService->getDataMutasiBarangJadiGudang($fromDate, $toDate, $kategoriBarang);
        // });

        $allData =  $this->mutasiService->getDataMutasiBarangJadiGudang($fromDate, $toDate, $kategoriBarang);

        $totalRecords = $allData->count();

        $filteredData = $allData;
        if (!empty($searchValue)) {
            $filteredData = $allData->filter(function ($row) use ($searchValue) {
                return str_contains(strtolower($row->styleno ?? ''), $searchValue)
                    || str_contains(strtolower($row->ws ?? ''), $searchValue)
                    || str_contains(strtolower($row->id_so_det ?? ''), $searchValue)
                    || str_contains(strtolower($row->color ?? ''), $searchValue)
                    || str_contains(strtolower($row->lokasi ?? ''), $searchValue)
                    || str_contains(strtolower($row->no_carton ?? ''), $searchValue);
            })->values();
        }

        $totalFiltered = $filteredData->count();

        $pageData = $length == -1
            ? $filteredData
            : $filteredData->slice($start, $length)->values();

            return response()->json([
            "draw"            => $draw,
            "recordsTotal"    => $totalRecords,
            "recordsFiltered" => $totalFiltered,
            "data"            => $pageData,
        ]);
    }

    public function export_excel_mutasi_barang_jadi_gudang(Request $request){
        $fromDate = $request->from;
        $toDate = $request->to;

        $this->mutasiService->exportExcelBarangJadiGudang($fromDate, $toDate);
    }

    public function export_excel_mutasi_barang_jadi(Request $request){
        $fromDate = $request->from;
        $toDate = $request->to;

        $this->mutasiService->exportExcelBarangJadi($fromDate, $toDate);
    }

    public function export_excel_pemasukan_bc(Request $request){
        $fromDate = $request->from;
        $toDate = $request->to;
        $filterBy = $request->filter_by;
        $jenis = $request->jenis;
        $kategori = $request->kategori;
        $kategoriBarang = $request->kategoriBarang;

        $this->pemasukanService->exportExcel($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
    }

    public function export_excel_pengeluaran_bc(Request $request){
        $fromDate = $request->from;
        $toDate = $request->to;
        $filterBy = $request->filter_by;
        $jenis = $request->jenis;
        $kategori = $request->kategori;
        $kategoriBarang = $request->kategoriBarang;

        $this->pengeluaranService->exportExcel($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
    }

    public function export_excel_mutasi_bahan_baku(Request $request){
        $fromDate = $request->from;
        $toDate = $request->to;
        $filterBy = $request->filter_by;
        $jenis = $request->jenis;
        $kategori = $request->kategori;
        $kategoriBarang = $request->kategoriBarang;

        $this->mutasiService->exportExcelBahanBaku($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
    }

    public function export_excel_mutasi_mesin(Request $request){
        $fromDate = $request->from;
        $toDate = $request->to;
        $filterBy = $request->filter_by;
        $jenis = $request->jenis;
        $kategori = $request->kategori;
        $kategoriBarang = $request->kategoriBarang;

        $this->mutasiService->exportExcelMesinSparepart($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
    }

    public function export_excel_mutasi_barang_sisa(Request $request){
        $fromDate = $request->from;
        $toDate = $request->to;
        $filterBy = $request->filter_by;
        $jenis = $request->jenis;
        $kategori = $request->kategori;
        $kategoriBarang = $request->kategoriBarang;

        $this->mutasiService->exportExcelBarangSisa($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
    }
}
