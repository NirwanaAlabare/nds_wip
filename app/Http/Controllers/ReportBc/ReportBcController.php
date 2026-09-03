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

    // public function index(Request $request)
    // {
    //     return view('report-bc.report_bc.index', ['page' => 'dashboard-report-bc']);
    // }

    // public function showReport(Request $request, $jenis, $kategori, $kategoriBarang)
    // {
    //     $fromDate = $request->input('from');
    //     $toDate = $request->input('to');
    //     $filterBy = $request->input('filter_by', 'dokumen');
    //     $export = $request->input('export');

    //     if (!$fromDate || !$toDate) {
    //         return redirect()->route('dashboard-report-bc')->with('error', 'Silakan tentukan range tanggal terlebih dahulu.');
    //     }


    //     if ($jenis === 'mutasi_bahan_baku') {
    //         $dataLaporan = $this->mutasiService->getDataMutasiBahanBaku($fromDate, $toDate, $kategoriBarang);
    //         $cleanKategori = 'bahanbaku';
    //     } elseif ($jenis === 'mutasi_barang_jadi') {
    //         $dataLaporan = $this->mutasiService->getDataMutasiBarangJadi($fromDate, $toDate, $kategoriBarang);
    //         $cleanKategori = 'barangjadi';
    //     } elseif ($jenis === 'mutasi_barang_jadi_gudang') {
    //         $dataLaporan = $this->mutasiService->getDataMutasiBarangJadiGudang($fromDate, $toDate, $kategoriBarang);
    //         $cleanKategori = 'barangjadi_gudang';
    //     } elseif ($jenis === 'mutasi_wip') {
    //         $dataLaporan = $this->mutasiService->getDataMutasiWip($fromDate, $toDate);
    //         $cleanKategori = 'mutasiwip';
    //     } elseif ($jenis === 'mutasi_mesin_sparepart') {
    //         $dataLaporan = $this->mutasiService->getDataMutasiMesinSparepart($fromDate, $toDate, $kategoriBarang);
    //         $cleanKategori = $kategoriBarang;
    //     }  elseif ($jenis === 'mutasi_barang_sisa') {
    //         $dataLaporan = $this->mutasiService->getDataMutasiBarangSisa($fromDate, $toDate, $kategoriBarang);
    //         $cleanKategori = 'barangsisa';
    //     }else {
    //         $service = ($jenis === 'pemasukan') ? $this->pemasukanService : $this->pengeluaranService;
    //         $cleanKategori = preg_replace('/[^a-zA-Z0-9]/', '', $kategori);
    //         $methodName = 'getData' . ucfirst($cleanKategori);

    //         if (method_exists($service, $methodName)) {
    //             $dataLaporan = $service->$methodName($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang);
    //         } else {
    //             $dataLaporan = collect();
    //         }
    //     }

    //     if ($export == 'excel') {


    //         if($jenis == 'pemasukan'){
    //             $this->pemasukanService->exportExcel($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
    //         }

    //         if($jenis == 'pengeluaran'){
    //             $this->pengeluaranService->exportExcel($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
    //         }

    //         if($jenis == 'mutasi_bahan_baku'){
    //             $this->mutasiService->exportExcelBahanBaku($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
    //         }

    //         if($jenis == 'mutasi_barang_jadi'){
    //             $this->mutasiService->exportExcelBarangJadi($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
    //         }

    //         if($jenis == 'mutasi_barang_jadi_gudang'){
    //             $this->mutasiService->exportExcelBarangJadiGudang($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
    //         }

    //         if($jenis == 'mutasi_mesin_sparepart'){
    //             $this->mutasiService->exportExcelMesinSparepart($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
    //         }

    //         if($jenis == 'mutasi_barang_sisa'){
    //             $this->mutasiService->exportExcelBarangSisa($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
    //         }
    //         // $fileName = "Laporan_" . ucfirst($jenis) . "_" . strtoupper($cleanKategori) . "_" . date('Ymd') . ".xls";

    //         // return response(view('report-bc.report_bc.excel-' . $jenis, [
    //         //     'data' => $dataLaporan,
    //         //     'jenis' => $jenis,
    //         //     'kategori' => $kategori,
    //         //     'fromDate' => $fromDate,
    //         //     'toDate' => $toDate,
    //         //     'kategoriBarang' => $kategoriBarang,
    //         //     'filterBy' => $filterBy,
    //         //     'fileName' => $fileName
    //         // ]))
    //         // ->header('Content-Type', 'application/vnd.ms-excel')
    //         // ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    //     }

    //     $viewName = 'report-bc.report_bc.report-' . $jenis;

    //     if (!view()->exists($viewName)) {
    //         return redirect()->route('dashboard-report-bc')->with('error', 'Halaman view ' . $viewName . ' belum dibuat.');
    //     }

    //     return view($viewName, [
    //         'page'           => 'dashboard-report-bc',
    //         'jenis'          => $jenis,
    //         'kategori'       => $kategori,
    //         'fromDate'       => $fromDate,
    //         'toDate'         => $toDate,
    //         'filterBy'       => $filterBy,
    //         'kategoriBarang' => $kategoriBarang,
    //         'data'           => $dataLaporan,
    //     ]);
    // }


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
            if ($request->ajax()) {
                return response()->json(['error' => 'Silakan tentukan range tanggal terlebih dahulu.'], 422);
            }
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
        } elseif ($jenis === 'mutasi_barang_sisa') {
            $dataLaporan = $this->mutasiService->getDataMutasiBarangSisa($fromDate, $toDate, $kategoriBarang);
            $cleanKategori = 'barangsisa';
        } else {
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
            if ($jenis == 'pemasukan') {
                return $this->pemasukanService->exportExcel($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
            }
            if ($jenis == 'pengeluaran') {
                return $this->pengeluaranService->exportExcel($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
            }
            if ($jenis == 'mutasi_bahan_baku') {
                return $this->mutasiService->exportExcelBahanBaku($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
            }
            if ($jenis == 'mutasi_barang_jadi') {
                return $this->mutasiService->exportExcelBarangJadi($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
            }
            if ($jenis == 'mutasi_barang_jadi_gudang') {
                return $this->mutasiService->exportExcelBarangJadiGudang($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
            }
            if ($jenis == 'mutasi_mesin_sparepart') {
                return $this->mutasiService->exportExcelMesinSparepart($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
            }
            if ($jenis == 'mutasi_barang_sisa') {
                return $this->mutasiService->exportExcelBarangSisa($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori);
            }
        }

        // AJAX request dari dashboard → balikin partial HTML tabel, bukan full page
        if ($request->ajax()) {
            $partialView = 'report-bc.report_bc.report-' . $jenis;

            if (!view()->exists($partialView)) {
                return response()->json(['error' => 'Partial view ' . $partialView . ' belum dibuat.'], 404);
            }

            $html = view($partialView, [
                'jenis'          => $jenis,
                'kategori'       => $kategori,
                'fromDate'       => $fromDate,
                'toDate'         => $toDate,
                'filterBy'       => $filterBy,
                'kategoriBarang' => $kategoriBarang,
                'data'           => $dataLaporan,
            ])->render();

            return response()->json(['html' => $html]);
        }

        // fallback non-AJAX (misal orang buka URL langsung / bookmark)
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

    public function pemasukan(Request $request, $kategori)
    {
        $fromDate = $request->input('from');
        $toDate = $request->input('to');
        $filterBy = $request->input('filter_by', 'dokumen');
        $kategoriBarang = $request->input('kategoriBarang', 'all');

        if (!$fromDate || !$toDate) {
            return response()->json(['error' => 'Silakan tentukan range tanggal terlebih dahulu.'], 422);
        }

        $html = view('report-bc.report_bc.report-pemasukan', [
            'jenis' => 'pemasukan',
            'kategori' => $kategori,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'filterBy' => $filterBy,
            'kategoriBarang' => $kategoriBarang,
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function getPemasukanData(Request $request)
    {
        $fromDate = $request->input('from');
        $toDate = $request->input('to');
        $filterBy = $request->input('filter_by', 'dokumen');
        $kategoriBarang = $request->input('kategoriBarang', 'all');
        $kategori = $request->input('kategori');


        if (!$fromDate || !$toDate) {
            return response()->json(['data' => []]);
        }

        $cleanKategori = preg_replace('/[^a-zA-Z0-9]/', '', $kategori);
        $methodName = 'getData' . ucfirst($cleanKategori);

        $dataLaporan = method_exists($this->pemasukanService, $methodName)
            ? $this->pemasukanService->$methodName($fromDate, $toDate, $filterBy, 'pemasukan', $kategoriBarang)
            : collect();


        $rows = collect($dataLaporan)->map(function ($row, $i) {
            return [
                'no' => $i + 1,
                'kode_kantor' => $row->kode_kantor ?? '-',
                'jenis_dokumen' => $row->jenis_dokumen ?? '-',
                'kategori_barang' => $row->kategori_barang ?? '-',
                'nomor_daftar' => $row->nomor_daftar ?? '-',
                'tanggal_daftar' => ($row->tanggal_daftar && $row->tanggal_daftar != '0000-00-00') ? date('d-m-Y', strtotime($row->tanggal_daftar)) : '00-00-0000',
                'nama_pengirim' => $row->nama_pengirim ?? '-',
                'nomor_bpb' => $row->nomor_bpb ?? '-',
                'tanggal_bpb' => ($row->tanggal_bpb && $row->tanggal_bpb != '0000-00-00') ? date('d-m-Y', strtotime($row->tanggal_bpb)) : '-', // TERLEWAT SEBELUMNYA
                'id_item' => $row->id_item ?? '-',
                'uraian_barang' => $row->uraian_barang ?? '-',
                'jenis_satuan' => $row->jenis_satuan ?? '-',
                'jumlah_satuan' => number_format($row->jumlah_satuan ?? 0, 2),
                'kode_valuta' => $row->kode_valuta ?? '-',
                'nilai_barang' => number_format($row->nilai_barang ?? 0, 2),
                'kurs' => number_format($row->kurs ?? 0, 2),
                'nilai_barang_idr' => number_format($row->nilai_barang_idr ?? 0, 2),
            ];
        });

        return response()->json(['data' => $rows]);
    }

    public function pengeluaran(Request $request, $kategori)
    {
        $fromDate = $request->input('from');
        $toDate = $request->input('to');
        $filterBy = $request->input('filter_by', 'dokumen');
        $kategoriBarang = $request->input('kategoriBarang', 'all');

        if (!$fromDate || !$toDate) {
            return response()->json(['error' => 'Silakan tentukan range tanggal terlebih dahulu.'], 422);
        }

        $html = view('report-bc.report_bc.report-pengeluaran', [
            'jenis' => 'pengeluaran',
            'kategori' => $kategori,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'filterBy' => $filterBy,
            'kategoriBarang' => $kategoriBarang,
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function getPengeluaranData(Request $request)
    {
        $fromDate = $request->input('from');
        $toDate = $request->input('to');
        $filterBy = $request->input('filter_by', 'dokumen');
        $kategoriBarang = $request->input('kategoriBarang', 'all');
        $kategori = $request->input('kategori');


        if (!$fromDate || !$toDate) {
            return response()->json(['data' => []]);
        }

        $cleanKategori = preg_replace('/[^a-zA-Z0-9]/', '', $kategori);
        $methodName = 'getData' . ucfirst($cleanKategori);

        $dataLaporan = method_exists($this->pengeluaranService, $methodName)
            ? $this->pengeluaranService->$methodName($fromDate, $toDate, $filterBy, 'pengeluaran', $kategoriBarang)
            : collect();


        $rows = collect($dataLaporan)->map(function ($row, $i) {
            return [
                'no' => $i + 1,
                'kode_kantor' => $row->kode_kantor ?? '-',
                'jenis_dokumen' => $row->jenis_dokumen ?? '-',
                'kategori_barang' => $row->kategori_barang ?? '-',
                'nomor_daftar' => $row->nomor_daftar ?? '-',
                'tanggal_daftar' => ($row->tanggal_daftar && $row->tanggal_daftar != '0000-00-00') ? date('d-m-Y', strtotime($row->tanggal_daftar)) : '00-00-0000',
                'nama_pengirim' => $row->nama_pengirim ?? '-',
                'nomor_bpb' => $row->nomor_bpb ?? '-',
                'ws' => $row->ws ?? '-',
                'tanggal_bpb' => ($row->tanggal_bpb && $row->tanggal_bpb != '0000-00-00') ? date('d-m-Y', strtotime($row->tanggal_bpb)) : '-', // TERLEWAT SEBELUMNYA
                'id_item' => $row->id_item ?? '-',
                'uraian_barang' => $row->uraian_barang ?? '-',
                'jenis_satuan' => $row->jenis_satuan ?? '-',
                'jumlah_satuan' => number_format($row->jumlah_satuan ?? 0, 2),
                'kode_valuta' => $row->kode_valuta ?? '-',
                'nilai_barang' => number_format($row->nilai_barang ?? 0, 2),
                'kurs' => number_format($row->kurs ?? 0, 2),
                'nilai_barang_idr' => number_format($row->nilai_barang_idr ?? 0, 2),
            ];
        });

        return response()->json(['data' => $rows]);
    }

    public function mutasiBahanBaku(Request $request, $kategori)
    {
        $fromDate = $request->input('from');
        $toDate = $request->input('to');
        $filterBy = $request->input('filter_by', 'dokumen');
        $kategoriBarang = $request->input('kategoriBarang', 'all');

        if (!$fromDate || !$toDate) {
            return response()->json(['error' => 'Silakan tentukan range tanggal terlebih dahulu.'], 422);
        }

        $html = view('report-bc.report_bc.report-report-mutasi-bahan-baku', [
            'jenis' => 'pengeluaran',
            'kategori' => $kategori,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'filterBy' => $filterBy,
            'kategoriBarang' => $kategoriBarang,
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function getMutasiBahanBaku(Request $request)
    {
        $fromDate = $request->input('from');
        $toDate = $request->input('to');
        $filterBy = $request->input('filter_by', 'dokumen');
        $kategoriBarang = $request->input('kategoriBarang', 'all');
        $kategori = $request->input('kategori');


        if (!$fromDate || !$toDate) {
            return response()->json(['data' => []]);
        }

        $dataLaporan = $this->mutasiService->getDataMutasiBarangJadi($fromDate, $toDate, 'all');

        $rows = collect($dataLaporan)->map(function ($row, $i) {
            return [
                'no' => $i + 1,
                'kode_kantor' => $row->kode_kantor ?? '-',
                'jenis_dokumen' => $row->jenis_dokumen ?? '-',
                'kategori_barang' => $row->kategori_barang ?? '-',
                'nomor_daftar' => $row->nomor_daftar ?? '-',
                'tanggal_daftar' => ($row->tanggal_daftar && $row->tanggal_daftar != '0000-00-00') ? date('d-m-Y', strtotime($row->tanggal_daftar)) : '00-00-0000',
                'nama_pengirim' => $row->nama_pengirim ?? '-',
                'nomor_bpb' => $row->nomor_bpb ?? '-',
                'ws' => $row->ws ?? '-',
                'tanggal_bpb' => ($row->tanggal_bpb && $row->tanggal_bpb != '0000-00-00') ? date('d-m-Y', strtotime($row->tanggal_bpb)) : '-', // TERLEWAT SEBELUMNYA
                'id_item' => $row->id_item ?? '-',
                'uraian_barang' => $row->uraian_barang ?? '-',
                'jenis_satuan' => $row->jenis_satuan ?? '-',
                'jumlah_satuan' => number_format($row->jumlah_satuan ?? 0, 2),
                'kode_valuta' => $row->kode_valuta ?? '-',
                'nilai_barang' => number_format($row->nilai_barang ?? 0, 2),
                'kurs' => number_format($row->kurs ?? 0, 2),
                'nilai_barang_idr' => number_format($row->nilai_barang_idr ?? 0, 2),
            ];
        });

        return response()->json(['data' => $rows]);
    }


    
}
