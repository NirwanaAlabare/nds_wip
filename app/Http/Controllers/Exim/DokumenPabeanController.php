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
                    $editUrl = route('dokumen-pabean-edit', ['id' => $row->trx_no_par, 'trx' => $jenis]);

                    $noAju = $row->nomor_aju ?? '';
                    $tglAju = ($row->tanggal_aju && $row->tanggal_aju != '0000-00-00') ? $row->tanggal_aju : '';

                    $btn = '<div class="d-flex justify-content-center">';
                    $btn .= '<a href="' . $editUrl . '" class="btn btn-sm btn-info mr-1" title="Edit Dokumen"><i class="fas fa-edit"></i></a>';

                    $btn .= '<button type="button" class="btn btn-sm btn-success mr-1 btn-kirim"
                                data-id="' . $row->trx_no_par . '"
                                data-noaju="' . $noAju . '"
                                data-tglaju="' . $tglAju . '"
                                title="Kirim ke CEISA"><i class="fas fa-paper-plane"></i></button>';
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

    public function sendCeisa($id, Request $request)
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

            $tanggalAju = date('Y-m-d');

            $nomorAju = $header->nomor_aju;

            if (empty($nomorAju) || strlen($nomorAju) != 26) {
                $kodeKantor   = '0000';
                $kodeDokumen  = '40';
                $idPerusahaan = 'NIW345';
                $tglFormat    = date('Ymd', strtotime($tanggalAju));
                $tahunAju     = date('Y', strtotime($tanggalAju));

                $prefixSearch = $kodeKantor . $kodeDokumen . $idPerusahaan . $tahunAju;

                $lastData = $db->table('bpb')
                    ->where('nomor_aju', 'like', $prefixSearch . '%')
                    ->orderBy('nomor_aju', 'desc')
                    ->first();

                if ($lastData && !empty($lastData->nomor_aju)) {
                    $lastSequence = (int) substr($lastData->nomor_aju, -6);
                    $nextSequence = $lastSequence + 1;
                } else {
                    $nextSequence = 1;
                }

                $nomorAju = $kodeKantor . $kodeDokumen . $idPerusahaan . $tglFormat . str_pad($nextSequence, 6, '0', STR_PAD_LEFT);
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
                    "kodeBarang" => $item->goods_code ?? '',
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
                "jabatanTtd" => "EXIM",
                "namaTtd" => "USER",
                "nik" => "123456789012345",
                "kodeKantor" => "050100",
                "kotaTtd" => "BANDUNG",
                "jumlahKontainer" => 0,
                "kodeDokumen" => "40",
                "kodeTujuanPengiriman" => "1",
                "netto" => (float) $totalNetto,
                "nomorAju" => $nomorAju,
                "tanggalAju" => $tanggalAju,
                "seri" => 0,
                "tanggalTtd" => date('Y-m-d'),
                "volume" => 0.00,
                "biayaTambahan" => 0.00,
                "biayaPengurang" => 0.00,
                "vd" => 0.00,
                "uangMuka" => 0.00,
                "nilaiJasa" => 0.00,
                "entitas" => [
                    [
                        "alamatEntitas" => "JL. RAYA RANCAEKEK MAJALAYA NO. 289 RT. 001 RW. 007",
                        "kodeEntitas" => "3",
                        "kodeJenisIdentitas" => "5",
                        "namaEntitas" => "NIRWANA ALABARE GARMENT",
                        "nibEntitas" => "0220103231143",
                        "nomorIdentitas" => "0745406926444000000000",
                        "nomorIjinEntitas" => "16/MK/WBC.09/2026",
                        "seriEntitas" => 1,
                        "tanggalIjinEntitas" => "2026-01-20"
                    ],
                    [
                        "alamatEntitas" => "",
                        "kodeEntitas" => "7",
                        "kodeJenisApi" => "2",
                        "kodeJenisIdentitas" => "5",
                        "kodeStatus" => "5",
                        "namaEntitas" => 'Tes',
                        "nibEntitas" => "",
                        "nomorIdentitas" => "",
                        "seriEntitas" => 2
                    ],
                    [
                        "alamatEntitas" => $header->alamat ?? "",
                        "kodeEntitas" => "9",
                        "kodeJenisApi" => "2",
                        "kodeJenisIdentitas" => "5",
                        "kodeStatus" => "5",
                        "namaEntitas" => $header->Supplier ?? "",
                        "nibEntitas" => "",
                        "nomorIdentitas" => $header->no_npwp ?? "000000000000000000",
                        "seriEntitas" => 3
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
                        "nomorPengangkut" => "D 6661 XX",
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

            $responseCeisa = $this->ceisaService->kirimDokumen($payload, 'false');

            if ($responseCeisa['successful']) {
                $db->table('bpb')
                    ->where(function($query) use ($id) {
                        $query->where('bpb.bpbno', $id)->orWhere('bpb.bpbno_int', $id);
                    })
                    ->update([
                        'nomor_aju' => $nomorAju,
                        'tanggal_aju' => $tanggalAju
                    ]);

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

    public function edit($id, Request $request)
    {
        $db = DB::connection('mysql_sb');

        $header = $db->table('bpb as a')
            ->select(
                'a.*',
                'ms.supplier',
                'ms.alamat as alamat_supplier',
                'ms.npwp as npwp_supplier',
                DB::raw("IF(a.bpbno_int != '', a.bpbno_int, a.bpbno) as trx_no_par")
            )
            ->leftJoin('mastersupplier as ms', 'a.id_supplier', '=', 'ms.id_supplier')
            ->where(function($query) use ($id) {
                $query->where('a.bpbno', $id)->orWhere('a.bpbno_int', $id);
            })
            ->first();

        if (!$header) {
            abort(404, 'Data Transaksi Tidak Ditemukan');
        }

        $ceisaInfo = $db->table('bpb_ceisa')->where('bpbno', $id)->first();

        $dataDetail = [];
        if ($ceisaInfo && $ceisaInfo->payload_json) {
            $dataDetail = json_decode($ceisaInfo->payload_json, true);
        }

        return view('export-import.dokumen-pabean.edit', [
            "page"           => "dashboard-export-import",
            "subPageGroup"   => "export-import",
            "subPage"        => "dokumen-pabean-list",
            "containerFluid" => true,
            "header"         => $header,
            "ceisaInfo"      => $ceisaInfo,
            "dataDetail"     => $dataDetail
        ]);
    }

    public function updateDraft($id, Request $request)
    {

        DB::connection('mysql_sb')->beginTransaction();

        try {

            $payloadJson = [
                'kodeKantor'           => $request->input('kodeKantor', '050100'),
                'kodeTujuanPengiriman' => $request->input('kodeTujuanPengiriman', '1'),
                'bruto'                => (float) $request->input('bruto', 0),
                'netto'                => (float) $request->input('netto', 0),
                'volume'               => (float) $request->input('volume', 0),
                'hargaPenyerahan'      => (float) $request->input('hargaPenyerahan', 0),
                'asuransi'             => (float) $request->input('asuransi', 0),
                'freight'              => (float) $request->input('freight', 0),
                'biayaTambahan'        => (float) $request->input('biayaTambahan', 0),
                'biayaPengurang'       => (float) $request->input('biayaPengurang', 0),
                'uangMuka'             => (float) $request->input('uangMuka', 0),
                'nilaiJasa'            => (float) $request->input('nilaiJasa', 0),
                'namaTtd'              => $request->input('namaTtd'),
                'jabatanTtd'           => $request->input('jabatanTtd'),
                'kotaTtd'              => $request->input('kotaTtd'),
                'tanggalTtd'           => $request->input('tanggalTtd', date('Y-m-d')),
                'entitas'              => $request->input('entitas', []),
                'pengangkut'           => $request->input('pengangkut', []),
                'kemasan'              => $request->input('kemasan', []),
                'pungutan'             => $request->input('pungutan', []),
                'dok'                  => $request->input('dok', []),
            ];


            DB::connection('mysql_sb')->table('bpb_ceisa')->updateOrInsert(
                ['bpbno' => $id],
                [
                    'tanggal_aju'  => $request->input('tanggalAju', date('Y-m-d')),
                    'payload_json' => json_encode($payloadJson),
                    'updated_at'   => Carbon::now()
                ]
            );


            DB::connection('mysql_sb')->commit();

            return redirect()->route('dokumen-pabean-index')
                             ->with('success', 'Data draft dokumen CEISA berhasil disimpan!');

        } catch (Exception $e) {

            DB::connection('mysql_sb')->rollBack();


            \Illuminate\Support\Facades\Log::error('Error Update Draft CEISA: ' . $e->getMessage());


            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
        }
    }
}
