<?php

namespace App\Http\Controllers\ReportBc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ReportBc\PemasukanService;
use App\Services\ReportBc\PengeluaranService;
use App\Services\ReportBc\MutasiService;

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
            $fileName = "Laporan_" . ucfirst($jenis) . "_" . strtoupper($cleanKategori) . "_" . date('Ymd') . ".xls";

            return response(view('report-bc.report_bc.excel-' . $jenis, [
                'data' => $dataLaporan,
                'jenis' => $jenis,
                'kategori' => $kategori,
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'kategoriBarang' => $kategoriBarang,
                'filterBy' => $filterBy,
                'fileName' => $fileName
            ]))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
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
            'data'           => $dataLaporan
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
}
