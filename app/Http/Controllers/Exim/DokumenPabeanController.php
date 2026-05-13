<?php

namespace App\Http\Controllers\Exim;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Services\CeisaService;
use Exception;

class DokumenPabeanController extends Controller
{
    protected $ceisaService;


    public function __construct(CeisaService $ceisaService)
    {
        $this->ceisaService = $ceisaService;
    }

    public function index(Request $request)
    {

        $db = DB::connection('mysql_sb');

        $tgl_awal = $request->input('tanggal_awal', date('Y-m-d', strtotime('-30 days')));
        $tgl_akhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $jenis = $request->input('jenis', 'Pemasukan');

        if ($request->ajax()) {
            if ($jenis == 'Pemasukan') {
                $tbl = 'bpb';
                $fldtgl = 'bpbdate';
                $fltanggalpar = 'bpbno';
                $fltanggal = DB::raw("IF(a.bpbno_int != '', a.bpbno_int, a.bpbno) as trx_no");
            } else {
                $tbl = 'bppb';
                $fldtgl = 'bppbdate';
                $fltanggalpar = 'bppbno';
                $fltanggal = DB::raw("IF(a.bppbno_int != '', a.bppbno_int, a.bppbno) as trx_no");
            }

            $data = $db->table($tbl . ' as a')
                ->join('mastersupplier as ms', 'a.id_supplier', '=', 'ms.id_supplier')
                ->select(
                    'a.*',
                    'ms.supplier',
                    "a.{$fldtgl} as tanggal",
                    $fltanggal,
                    "a.{$fltanggalpar} as trx_no_par"
                )
                ->whereBetween("a.{$fldtgl}", [$tgl_awal, $tgl_akhir])
                ->where('a.jenis_dok', 'BC 4.0')
                ->groupBy("a.{$fltanggalpar}")
                ->orderBy("a.{$fldtgl}", 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('tanggal', function ($row) {
                    return Carbon::parse($row->tanggal)->format('d M Y');
                })
                ->editColumn('bcdate', function ($row) {
                    return ($row->bcdate && $row->bcdate != '0000-00-00') ? Carbon::parse($row->bcdate)->format('d M Y') : '-';
                })
                ->editColumn('tanggal_aju', function ($row) {
                    return ($row->tanggal_aju && $row->tanggal_aju != '0000-00-00') ? Carbon::parse($row->tanggal_aju)->format('d M Y') : '-';
                })
                ->addColumn('pono', function ($row) use ($jenis) {
                    return $jenis == 'Pemasukan' ? ($row->pono ?? '-') : '-';
                })
                ->addColumn('action', function($row) use ($jenis) {
                    $editUrl = route('dokumen.pabean.edit', ['id' => $row->trx_no_par, 'trx' => $jenis]);

                    $btn = '<div class="d-flex justify-content-center">';
                    $btn .= '<a href="' . $editUrl . '" class="btn btn-sm btn-info mr-1" title="Edit Dokumen"><i class="fas fa-edit"></i></a>';
                    $btn .= '<button type="button" class="btn btn-sm btn-success mr-1 btn-kirim" data-id="' . $row->trx_no_par . '" title="Kirim ke CEISA"><i class="fas fa-paper-plane"></i></button>';
                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('export-import.dokumen-pabean.index', [
            "page"           => "dashboard-export-import",
            "subPageGroup"   => "export-import",
            "subPage"        => "dokumen-pabean-list",
            "containerFluid" => true,
            "jenis"          => $jenis,
            "tgl_awal"       => $tgl_awal,
            "tgl_akhir"      => $tgl_akhir
        ]);
    }

    public function kirimCeisa($id, Request $request)
    {

        $db = DB::connection('mysql_sb');

        try {
            $header = $db->table('bpb as a')
                ->join('mastersupplier as ms', 'a.id_supplier', '=', 'ms.id_supplier')
                ->where(function($query) use ($id) {
                    $query->where('a.bpbno', $id)->orWhere('a.bpbno_int', $id);
                })
                ->first();

            if (!$header) {
                throw new Exception("Data transaksi tidak ditemukan!");
            }

            $items = $db->table('bpb as a')
                ->join('masteritem as mi', 'a.id_item', '=', 'mi.id_item')
                ->where(function($query) use ($id) {
                    $query->where('a.bpbno', $id)->orWhere('a.bpbno_int', $id);
                })
                ->get();

            $totalHargaPenyerahan = 0;
            $totalBruto = $header->berat_kotor > 0 ? $header->berat_kotor : 0;
            $totalNetto = $header->berat_bersih > 0 ? $header->berat_bersih : 0;

            $arrayBarang = [];
            $seriBarang = 1;

            foreach ($items as $item) {
                $hargaPenyerahanItem = $item->qty * $item->price;
                $totalHargaPenyerahan += $hargaPenyerahanItem;

                $arrayBarang[] = [
                    "asuransi" => 0.00,
                    "bruto" => 0.00,
                    "cif" => 0.00,
                    "diskon" => 0.00,
                    "hargaEkspor" => 0.00,
                    "hargaPenyerahan" => (float) $hargaPenyerahanItem,
                    "hargaSatuan" => (float) $item->price,
                    "isiPerKemasan" => 0,
                    "jumlahKemasan" => 0.00,
                    "jumlahRealisasi" => 0.00,
                    "jumlahSatuan" => (float) $item->qty,
                    "kodeBarang" => $item->goods_code ?? 'BRG01',
                    "kodeDokumen" => "40",
                    "kodeJenisKemasan" => "NE",
                    "kodeSatuanBarang" => $item->unit,
                    "merk" => "-",
                    "netto" => 0.00,
                    "nilaiBarang" => 0.00,
                    "posTarif" => "48191000",
                    "seriBarang" => $seriBarang,
                    "spesifikasiLain" => $item->remark ?? "-",
                    "tipe" => "TIPE BARANG",
                    "ukuran" => "",
                    "uraian" => $item->itemdesc ?? "Deskripsi Barang",
                    "volume" => 0.00,
                    "cifRupiah" => 0.00,
                    "hargaPerolehan" => 0.00,
                    "kodeAsalBahanBaku" => "1",
                    "ndpbm" => 0.00,
                    "uangMuka" => 0.00,
                    "nilaiJasa" => 0,
                    "barangTarif" => [
                        [
                            "kodeJenisTarif" => "1",
                            "jumlahSatuan" => (float) $item->qty,
                            "kodeFasilitasTarif" => "3",
                            "kodeSatuanBarang" => $item->unit,
                            "nilaiBayar" => 0.00,
                            "nilaiFasilitas" => 0.00,
                            "nilaiSudahDilunasi" => 0.00,
                            "seriBarang" => $seriBarang,
                            "tarif" => 11.00,
                            "tarifFasilitas" => 100.00,
                            "kodeJenisPungutan" => "PPN"
                        ]
                    ]
                ];
                $seriBarang++;
            }

            $payload = [
                "asalData" => "S",
                "asuransi" => 0.00,
                "bruto" => (float) $totalBruto,
                "cif" => 0.00,
                "kodeJenisTpb" => "1",
                "freight" => 0.00,
                "hargaPenyerahan" => (float) $totalHargaPenyerahan,
                "idPengguna" => "010693232092000 01234567890000",
                "jabatanTtd" => "KUASA DIREKSI",
                "namaTtd" => "ABCD",
                "nik" => "123456789012345",
                "kodeKantor" => "050900",
                "kotaTtd" => "JAKARTA",
                "jumlahKontainer" => 0,
                "kodeDokumen" => "40",
                "kodeTujuanPengiriman" => "1",
                "netto" => (float) $totalNetto,
                "nomorAju" => $header->nomor_aju ?? "-",
                "seri" => 0,
                "tanggalAju" => $header->tanggal_aju ?? date('Y-m-d'),
                "tanggalTtd" => date('Y-m-d'),
                "volume" => 0.00,
                "biayaTambahan" => 0.00,
                "biayaPengurang" => 0.00,
                "vd" => 0.00,
                "uangMuka" => 0.00,
                "nilaiJasa" => 0.00,
                "entitas" => [
                    [
                        "alamatEntitas" => "KAWASAN INDUSTRI GARMEN",
                        "kodeEntitas" => "3",
                        "kodeJenisIdentitas" => "5",
                        "namaEntitas" => "PT PERUSAHAAN GARMEN",
                        "nibEntitas" => "1234567890123",
                        "nomorIdentitas" => "456789012345000",
                        "nomorIjinEntitas" => "1234/KM.4/2021",
                        "seriEntitas" => 1,
                        "tanggalIjinEntitas" => "2021-01-20"
                    ],
                    [
                        "alamatEntitas" => $header->alamat_supplier ?? "ALAMAT SUPPLIER",
                        "kodeEntitas" => "7",
                        "kodeJenisApi" => "2",
                        "kodeJenisIdentitas" => "5",
                        "kodeStatus" => "5",
                        "namaEntitas" => 'Tes',
                        "nibEntitas" => "1234567890123",
                        "nomorIdentitas" => "456789012345000",
                        "seriEntitas" => 2
                    ]
                ],
                "dokumen" => [
                    [
                        "kodeDokumen" => "380",
                        "nomorDokumen" => $header->invno ?? "-",
                        "seriDokumen" => 1,
                        "tanggalDokumen" => $header->bpbdate
                    ],
                    [
                        "kodeDokumen" => "217",
                        "nomorDokumen" => $header->pono ?? "-",
                        "seriDokumen" => 2,
                        "tanggalDokumen" => $header->bpbdate
                    ]
                ],
                "pengangkut" => [
                    [
                        "namaPengangkut" => "TRUK",
                        "nomorPengangkut" => $header->nomor_mobil ?? "-",
                        "seriPengangkut" => 1
                    ]
                ],
                "kontainer" => [],
                "kemasan" => [
                    [
                        "jumlahKemasan" => (float) ($header->qty_karton ?? 0),
                        "kodeJenisKemasan" => "CT",
                        "merkKemasan" => "-",
                        "seriKemasan" => 1
                    ]
                ],
                "pungutan" => [
                    [
                        "kodeFasilitasTarif" => "3",
                        "kodeJenisPungutan" => "PPN",
                        "nilaiPungutan" => 0.00
                    ]
                ],
                "barang" => $arrayBarang
            ];

            dd($payload);

            $responseCeisa = $this->ceisaService->kirimDokumen($payload, 'false');

            if ($responseCeisa['successful']) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Dokumen berhasil dikirim ke CEISA sebagai Draft!',
                    'data_payload' => $payload,
                    'ceisa_response' => $responseCeisa['body']
                ]);
            } else {
                return response()->json([
                    'status' => $responseCeisa['status_code'],
                    'message' => 'Gagal mengirim ke CEISA.',
                    'ceisa_error' => $responseCeisa['body']
                ], $responseCeisa['status_code']);
            }

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
