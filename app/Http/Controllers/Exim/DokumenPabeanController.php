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

        $jenis_bc = $request->input('jenis_bc', 'BC 4.0');
        $status_ceisa = $request->input('status_ceisa', '');

        if ($request->ajax()) {
            if ($jenis == 'Pemasukan') {
                $tbl       = 'bpb';
                $fldtgl    = 'bpbdate';
                $fldno     = 'bpbno';
                $fldno_int = 'bpbno_int';
            } else {
                $tbl       = 'bppb';
                $fldtgl    = 'bppbdate';
                $fldno     = 'bppbno';
                $fldno_int = 'bppbno_int';
            }

            $selectTrx = DB::raw("IF({$tbl}.{$fldno_int} != '', {$tbl}.{$fldno_int}, {$tbl}.{$fldno}) as trx_no");

            $data = $db->table($tbl)
                ->join('mastersupplier as ms', "{$tbl}.id_supplier", '=', 'ms.id_supplier')
                ->leftJoin('bpb_ceisa as bc', function($join) use ($tbl, $fldno, $fldno_int) {
                    $join->on("bc.bpbno", '=', "{$tbl}.{$fldno}")
                         ->orOn("bc.bpbno", '=', "{$tbl}.{$fldno_int}");
                })
                ->select(
                    "{$tbl}.*",
                    'ms.supplier',
                    "{$tbl}.{$fldtgl} as tanggal",
                    $selectTrx,
                    "{$tbl}.{$fldno} as trx_no_par",
                    'bc.status as ceisa_status',
                    'bc.nomor_aju as nomor_aju_ceisa',
                    'bc.tanggal_aju as tanggal_aju_ceisa'
                )
                ->whereBetween("{$tbl}.{$fldtgl}", [$tgl_awal, $tgl_akhir]);

            if (!empty($jenis_bc)) {
                $data->where("{$tbl}.jenis_dok", $jenis_bc);
            }

            if (!empty($status_ceisa)) {
                if ($status_ceisa == 'sent') {
                    $data->where('bc.status', 1);
                } elseif ($status_ceisa == 'unsent') {
                    $data->where(function($q) {
                        $q->whereNull('bc.status')
                          ->orWhere('bc.status', '!=', 1);
                    });
                }
            }

            $data->groupBy("{$tbl}.{$fldno}")
                 ->orderBy("{$tbl}.{$fldtgl}", 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('tanggal', function ($row) {
                    return \Carbon\Carbon::parse($row->tanggal)->format('d M Y');
                })
                ->editColumn('bcdate', function ($row) {
                    return ($row->bcdate && $row->bcdate != '0000-00-00') ? \Carbon\Carbon::parse($row->bcdate)->format('d M Y') : '-';
                })
                ->editColumn('tanggal_aju', function ($row) {
                    return ($row->tanggal_aju && $row->tanggal_aju != '0000-00-00') ? \Carbon\Carbon::parse($row->tanggal_aju)->format('d M Y') : '-';
                })
                ->addColumn('pono', function ($row) use ($jenis) {
                    return $jenis == 'Pemasukan' ? ($row->pono ?? '-') : '-';
                })
                ->addColumn('action', function($row) use ($jenis) {
                    $editUrl = route('dokumen-pabean-edit', ['id' => $row->trx_no_par, 'trx' => $jenis]);
                    $noAju = $row->nomor_aju_ceisa ?? '';
                    $tglAju = ($row->tanggal_aju && $row->tanggal_aju != '0000-00-00') ? $row->tanggal_aju : '';

                    $btn = '<div class="d-flex justify-content-center">';
                    $btn .= '<a href="' . $editUrl . '" class="btn btn-sm btn-info mr-1" title="Edit Dokumen"><i class="fas fa-edit"></i></a>';

                    if($row->jenis_dok == 'BC 4.0' && $jenis == 'Pemasukan') {
                        if($row->ceisa_status == 1) {
                            $btn .= '<button type="button" class="btn btn-sm btn-secondary mr-1 btn-status" title="Sudah dikirim ke CEISA" data-noaju="' . $noAju . '"><i class="fas fa-check"></i></button>';
                        } else {
                            $btn .= '<button type="button" class="btn btn-sm btn-success mr-1 btn-kirim"
                                data-id="' . $row->trx_no_par . '"
                                data-noaju="' . $noAju . '"
                                data-tglaju="' . $tglAju . '"
                                title="Kirim ke CEISA"><i class="fas fa-paper-plane"></i></button>';
                        }
                    }

                    $btn .= '</div>';

                    return $btn;
                })
                ->filterColumn('trx_no', function ($query, $keyword) use ($tbl, $fldno, $fldno_int) {
                    $query->where(function ($q) use ($keyword, $tbl, $fldno, $fldno_int) {
                        $q->where("{$tbl}.{$fldno}", 'LIKE', "%{$keyword}%")
                          ->orWhere("{$tbl}.{$fldno_int}", 'LIKE', "%{$keyword}%");
                    });
                })
                ->filterColumn('supplier', function ($query, $keyword) {
                    $query->where('ms.supplier', 'LIKE', "%{$keyword}%");
                })
                ->filterColumn('nomor_aju_ceisa', function ($query, $keyword) {
                    $query->where('bc.nomor_aju', 'LIKE', "%{$keyword}%");
                })
                ->filterColumn('pono', function ($query, $keyword) use ($jenis, $tbl) {
                    if ($jenis == 'Pemasukan') {
                        $query->where("{$tbl}.pono", 'LIKE', "%{$keyword}%");
                    }
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
            "jenis_bc"       => $jenis_bc,
            "status_ceisa"   => $status_ceisa,
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
                throw new \Exception("Data transaksi tidak ditemukan!");
            }

            $ceisaInfo = $db->table('bpb_ceisa')->where('bpbno', $id)->first();

            if(!$ceisaInfo) {
                throw new \Exception("Data CEISA untuk transaksi ini tidak ditemukan. Pastikan data sudah disiapkan sebelum mengirim ke CEISA.");
            }

            $draft = json_decode($ceisaInfo->payload_json ?? '{}', true);


            $tanggalAju = date('Y-m-d');
            $nomorAju = $ceisaInfo->nomor_aju ?? '';

            // if (empty($nomorAju) || strlen($nomorAju) != 26) {
            //     $currentYear  = date('Y');
            //     $today        = date('Ymd');
            //     $kodeKantor   = !empty($draft['kodeKantor']) ? $draft['kodeKantor'] : '050100';
            //     $kodeDokumen  = '40';
            //     $idPerusahaan = 'NIW345';

            //     $prefixSearch = $kodeKantor . $kodeDokumen . $idPerusahaan . $currentYear;

            //     $lastData = $db->table('bpb_ceisa')
            //         ->where('nomor_aju', 'like', $prefixSearch . '%')
            //         ->orderBy('nomor_aju', 'desc')
            //         ->first();

            //     $nextSequence = 1;
            //     if ($lastData && !empty($lastData->nomor_aju)) {
            //         $lastSequence = (int) substr($lastData->nomor_aju, -6);
            //         $nextSequence = $lastSequence + 1;

            //         $lastYearSaved = substr($lastData->nomor_aju, 12, 4);
            //         if ($lastYearSaved !== $currentYear) {
            //             $nextSequence = 1;
            //         }
            //     }

            //     $nomorAju = $kodeKantor . $kodeDokumen . $idPerusahaan . $today . str_pad($nextSequence, 6, '0', STR_PAD_LEFT);
            // }


            $payloadDokumen = [];
            $seriDok = 1;
            foreach (($draft['dok'] ?? []) as $d) {
                if (!empty($d['kode']) && !empty($d['nomor'])) {
                    $payloadDokumen[] = [
                        "kodeDokumen" => $d['kode'], "nomorDokumen" => $d['nomor'],
                        "seriDokumen" => $seriDok++, "tanggalDokumen" => !empty($d['tgl']) ? $d['tgl'] : date('Y-m-d')
                    ];
                }
            }
            if (empty($payloadDokumen)) {
                if(!empty($header->invno)) $payloadDokumen[] = ["kodeDokumen" => "380", "nomorDokumen" => $header->invno, "seriDokumen" => $seriDok++, "tanggalDokumen" => $header->bpbdate];
                if(!empty($header->pono)) $payloadDokumen[] = ["kodeDokumen" => "217", "nomorDokumen" => $header->pono, "seriDokumen" => $seriDok++, "tanggalDokumen" => $header->bpbdate];
            }

            $payloadKontainer = [];
            $seriKont = 1;
            foreach (($draft['kontainer'] ?? []) as $k) {
                if (!empty($k['nomorKontainer'])) {
                    $payloadKontainer[] = [
                        "kodeJenisKontainer"  => $k['kodeJenisKontainer'], "kodeTipeKontainer" => $k['kodeTipeKontainer'],
                        "kodeUkuranKontainer" => $k['kodeUkuranKontainer'], "nomorKontainer" => strtoupper(trim($k['nomorKontainer'])),
                        "seriKontainer"       => $seriKont++
                    ];
                }
            }

            $payloadKemasan = [];
            $seriKem = 1;
            foreach (($draft['kemasan'] ?? []) as $k) {
                $payloadKemasan[] = [
                    "jumlahKemasan"    => (float) ($k['jumlahKemasan'] ?? $k['jumlah'] ?? 0),
                    "kodeJenisKemasan" => $k['kodeJenisKemasan'] ?? $k['kode'] ?? "CT",
                    "merkKemasan"      => $k['merkKemasan'] ?? $k['merk'] ?? "-",
                    "seriKemasan"      => $seriKem++
                ];
            }
            if (empty($payloadKemasan)) {
                $payloadKemasan[] = [
                    "jumlahKemasan" => (float) ($header->qty_karton ?? 0),
                    "kodeJenisKemasan" => "CT", "merkKemasan" => "-", "seriKemasan" => 1
                ];
            }

            $totalHargaPenyerahan = 0;
            $arrayBarang = [];

            if (!empty($draft['barang']) && count($draft['barang']) > 0) {

                foreach ($draft['barang'] as $index => $brg) {
                    $hargaPenyerahanItem = (float) ($brg['hargaPenyerahan'] ?? 0);
                    $totalHargaPenyerahan += $hargaPenyerahanItem;

                    $tarif = $brg['barangTarif'][0] ?? $brg['barangTarif'] ?? [];

                    $arrayBarang[] = [
                        "asuransi"         => (float) ($brg['asuransi'] ?? 0.00),
                        "bruto"            => (float) ($brg['bruto'] ?? 0.00),
                        "cif"              => (float) ($brg['cif'] ?? 0.00),
                        "diskon"           => (float) ($brg['diskon'] ?? 0.00),
                        "hargaEkspor"      => 0.00,
                        "hargaPenyerahan"  => $hargaPenyerahanItem,
                        "hargaSatuan"      => (float) ($brg['hargaSatuan'] ?? 0),
                        "isiPerKemasan"    => 0,
                        "jumlahKemasan"    => (float) ($brg['jumlahKemasan'] ?? 0.00),
                        "jumlahRealisasi"  => 0.00,
                        "jumlahSatuan"     => (float) ($brg['jumlahSatuan'] ?? 0),
                        "kodeBarang"       => strval($brg['kodeBarang'] ?? ''),
                        "kodeDokumen"      => "40",
                        "kodeJenisKemasan" => $brg['kodeJenisKemasan'] ?? "",
                        "kodeSatuanBarang" => $brg['kodeSatuanBarang'] ?? "",
                        "merk"             => $brg['merk'] ?? "-",
                        "netto"            => (float) ($brg['netto'] ?? 0.00),
                        "nilaiBarang"      => 0.00,
                        "posTarif"         => $brg['posTarif'] ?? "",
                        "seriBarang"       => (int) ($brg['seriBarang'] ?? ($index + 1)),
                        "spesifikasiLain"  => $brg['spesifikasiLain'] ?? "-",
                        "tipe"             => $brg['tipe'] ?? "",
                        "ukuran"           => $brg['ukuran'] ?? "",
                        "uraian"           => $brg['uraian'] ?? "Deskripsi Barang",
                        "volume"           => (float) ($brg['volume'] ?? 0.00),
                        "cifRupiah"        => 0.00,
                        "hargaPerolehan"   => 0.00,
                        "kodeAsalBahanBaku"=> "1",
                        "ndpbm"            => 0.00,
                        "uangMuka"         => 0.00,
                        "nilaiJasa"        => (float) ($brg['nilaiJasa'] ?? 0.00),
                        "barangTarif"      => [
                            [
                                "kodeJenisTarif"     => "1",
                                "jumlahSatuan"       => (float) ($brg['jumlahSatuan'] ?? 0),
                                "kodeFasilitasTarif" => $tarif['kodeFasilitasTarif'] ?? "3",
                                "kodeSatuanBarang"   => $brg['kodeSatuanBarang'] ?? "",
                                "nilaiBayar"         => 0.00,
                                "nilaiFasilitas"     => 0.00,
                                "nilaiSudahDilunasi" => 0.00,
                                "seriBarang"         => (int) ($brg['seriBarang'] ?? ($index + 1)),
                                "tarif"              => (float) ($tarif['tarif'] ?? 11.00),
                                "tarifFasilitas"     => (float) ($tarif['tarifFasilitas'] ?? 100.00),
                                "kodeJenisPungutan"  => "PPN"
                            ]
                        ]
                    ];
                }
            } else {

                $items = $db->table('bpb as a')
                    ->join('masteritem as mi', 'a.id_item', '=', 'mi.id_item')
                    ->where(function($query) use ($id) {
                        $query->where('a.bpbno', $id)->orWhere('a.bpbno_int', $id);
                    })->get();

                foreach ($items as $index => $item) {
                    $hargaPenyerahanItem = (float) ($item->qty * $item->price);
                    $totalHargaPenyerahan += $hargaPenyerahanItem;

                    $arrayBarang[] = [
                        "asuransi"         => 0.00,
                        "bruto"            => 0.00,
                        "cif"              => 0.00,
                        "diskon"           => 0.00,
                        "hargaEkspor"      => 0.00,
                        "hargaPenyerahan"  => $hargaPenyerahanItem,
                        "hargaSatuan"      => (float) $item->price,
                        "isiPerKemasan"    => 0,
                        "jumlahKemasan"    => 0.00,
                        "jumlahRealisasi"  => 0.00,
                        "jumlahSatuan"     => (float) $item->qty,
                        "kodeBarang"       => strval($item->goods_code ?? $item->id_item),
                        "kodeDokumen"      => "40",
                        "kodeJenisKemasan" => "NE",
                        "kodeSatuanBarang" => $item->unit,
                        "merk"             => "-",
                        "netto"            => 0.00,
                        "nilaiBarang"      => 0.00,
                        "posTarif"         => "48191000",
                        "seriBarang"       => ($index + 1),
                        "spesifikasiLain"  => $item->remark ?? "-",
                        "tipe"             => "TIPE BARANG",
                        "ukuran"           => "",
                        "uraian"           => $item->itemdesc ?? "Deskripsi Barang",
                        "volume"           => 0.00,
                        "cifRupiah"        => 0.00,
                        "hargaPerolehan"   => 0.00,
                        "kodeAsalBahanBaku"=> "1",
                        "ndpbm"            => 0.00,
                        "uangMuka"         => 0.00,
                        "nilaiJasa"        => 0.00,
                        "barangTarif"      => [
                            [
                                "kodeJenisTarif"     => "1",
                                "jumlahSatuan"       => (float) $item->qty,
                                "kodeFasilitasTarif" => "3",
                                "kodeSatuanBarang"   => $item->unit,
                                "nilaiBayar"         => 0.00,
                                "nilaiFasilitas"     => 0.00,
                                "nilaiSudahDilunasi" => 0.00,
                                "seriBarang"         => ($index + 1),
                                "tarif"              => 11.00,
                                "tarifFasilitas"     => 100.00,
                                "kodeJenisPungutan"  => "PPN"
                            ]
                        ]
                    ];
                }
            }

            $payload = [
                "asalData"             => "S",
                "asuransi"             => (float) ($draft['asuransi'] ?? 0.00),
                "bruto"                => (float) ($draft['bruto'] ?? $header->berat_kotor ?? 0.00),
                "cif"                  => (float) ($draft['cif'] ?? 0.00),
                "kodeJenisTpb"         => "1",
                "freight"              => (float) ($draft['freight'] ?? 0.00),
                "hargaPenyerahan"      => (float) ($draft['hargaPenyerahan'] ?? $totalHargaPenyerahan),
                "idPengguna"           => "",
                "jabatanTtd"           => $draft['jabatanTtd'] ?? "EXIM STAFF",
                "namaTtd"              => $draft['namaTtd'] ?? "USER EXIM",
                "nik"                  => "",
                "kodeKantor"           => $draft['kodeKantor'] ?? "050100",
                "kotaTtd"              => $draft['kotaTtd'] ?? "BANDUNG",
                "jumlahKontainer"      => (int) ($draft['jumlahKontainer'] ?? 0),
                "kodeDokumen"          => "40",
                "kodeTujuanPengiriman" => $draft['kodeTujuanPengiriman'] ?? "1",
                "netto"                => (float) ($draft['netto'] ?? $header->berat_bersih ?? 0.00),
                "nomorAju"             => $nomorAju,
                "tanggalAju"           => $tanggalAju,
                "seri"                 => 0,
                "tanggalTtd"           => $draft['tanggalTtd'] ?? date('Y-m-d'),
                "volume"               => (float) ($draft['volume'] ?? 0.00),
                "biayaTambahan"        => (float) ($draft['biayaTambahan'] ?? 0.00),
                "biayaPengurang"       => (float) ($draft['biayaPengurang'] ?? 0.00),
                "vd"                   => 0.00,
                "uangMuka"             => (float) ($draft['uangMuka'] ?? 0.00),
                "nilaiJasa"            => (float) ($draft['nilaiJasa'] ?? 0.00),

                "entitas" => [
                    [
                        "alamatEntitas"      => $draft['entitas'][3]['alamatEntitas'] ?? "JL. RAYA RANCAEKEK MAJALAYA NO. 289 RT. 001 RW. 007",
                        "kodeEntitas"        => "3",
                        "kodeJenisIdentitas" => "5",
                        "namaEntitas"        => $draft['entitas'][3]['namaEntitas'] ?? "NIRWANA ALABARE GARMENT",
                        "nibEntitas"         => $draft['entitas'][3]['nibEntitas'] ?? "0220103231143",
                        "nomorIdentitas"     => $draft['entitas'][3]['nomorIdentitas'] ?? "0745406926444000000000",
                        "nomorIjinEntitas"   => $draft['entitas'][3]['nomorIjinEntitas'] ?? "16/MK/WBC.09/2026",
                        "seriEntitas"        => 1,
                        "tanggalIjinEntitas" => "2026-01-20"
                    ],
                    [
                        "alamatEntitas"      => $draft['entitas'][7]['alamatEntitas'] ?? $header->alamat_supplier ?? "",
                        "kodeEntitas"        => "7",
                        "kodeJenisApi"       => "2",
                        "kodeJenisIdentitas" => "5",
                        "kodeStatus"         => $draft['entitas'][7]['kodeStatus'] ?? "5",
                        "namaEntitas"        => $draft['entitas'][7]['namaEntitas'] ?? $header->supplier ?? "",
                        "nibEntitas"         => "",
                        "nomorIdentitas"     => $draft['entitas'][7]['nomorIdentitas'] ?? $header->npwp_supplier ?? "",
                        "seriEntitas"        => 2
                    ],
                    [
                        "alamatEntitas"      => $draft['entitas'][9]['alamatEntitas'] ?? $header->alamat_supplier ?? "",
                        "kodeEntitas"        => "9",
                        "kodeJenisApi"       => "2",
                        "kodeJenisIdentitas" => "5",
                        "kodeStatus"         => "5",
                        "namaEntitas"        => $draft['entitas'][9]['namaEntitas'] ?? $header->supplier ?? "",
                        "nibEntitas"         => "",
                        "nomorIdentitas"     => $draft['entitas'][9]['nomorIdentitas'] ?? $header->npwp_supplier ?? "",
                        "seriEntitas"        => 3
                    ]
                ],

                "dokumen"    => $payloadDokumen,
                "pengangkut" => [
                    [
                        "namaPengangkut"  => $draft['pengangkut']['nama'] ?? "",
                        "nomorPengangkut" => $draft['pengangkut']['nomor'] ?? $header->nomor_mobil ?? "",
                        "seriPengangkut"  => 1
                    ]
                ],
                "kontainer"  => $payloadKontainer,
                "kemasan"    => $payloadKemasan,
                "pungutan"   => [
                    [
                        "kodeFasilitasTarif" => "3",
                        "kodeJenisPungutan"  => $draft['pungutan']['jenis'] ?? "",
                        "nilaiPungutan"      => (float) ($draft['pungutan']['nilai'] ?? 0.00)
                    ]
                ],
                "barang"     => $arrayBarang
            ];

            $responseCeisa = $this->ceisaService->kirimDokumen($payload, 'false');

            if ($responseCeisa['successful']) {
                $db->table('bpb')
                    ->where(function($query) use ($id) {
                        $query->where('bpbno', $id)->orWhere('bpbno_int', $id);
                    })
                    ->update([
                        'nomor_aju'   => $nomorAju,
                        'tanggal_aju' => $tanggalAju
                    ]);

                $db->table('bpb_ceisa')->where('bpbno', $id)->update([
                    'nomor_aju'   => $nomorAju,
                    'tanggal_aju' => $tanggalAju,
                    'status'      => 1,
                    'updated_at'  => \Carbon\Carbon::now()
                ]);

                return response()->json([
                    'status'         => 200,
                    'message'        => 'Dokumen berhasil dikirim ke CEISA sebagai Draft!',
                    'data_payload'   => $payload,
                    'ceisa_response' => $responseCeisa['body']
                ]);
            } else {
                return response()->json([
                    'status'      => $responseCeisa['status_code'],
                    'message'     => 'Gagal mengirim ke CEISA.',
                    'ceisa_error' => $responseCeisa['body']
                ], $responseCeisa['status_code']);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id, Request $request)
    {
        $db = DB::connection('mysql_sb');

        $header = $db->table('bpb as a')
            ->select('a.*', 'ms.supplier', 'ms.alamat as alamat_supplier', 'ms.npwp as npwp_supplier',
                     DB::raw("IF(a.bpbno_int != '', a.bpbno_int, a.bpbno) as trx_no_par"))
            ->leftJoin('mastersupplier as ms', 'a.id_supplier', '=', 'ms.id_supplier')
            ->where(function($query) use ($id) {
                $query->where('a.bpbno', $id)->orWhere('a.bpbno_int', $id);
            })
            ->first();

        if (!$header) abort(404, 'Data Transaksi Tidak Ditemukan');

        $ceisaInfo = $db->table('bpb_ceisa')->where('bpbno', $id)->first();

        $dataDetail = json_decode($ceisaInfo->payload_json ?? '{}', true);

        $items = $db->table('bpb as a')
                ->join('masteritem as mi', 'a.id_item', '=', 'mi.id_item')
                ->select('a.*', 'mi.goods_code', 'mi.itemdesc')
                ->where(function($query) use ($id) {
                    $query->where('a.bpbno', $id)->orWhere('a.bpbno_int', $id);
                })
                ->get();

        $nomorAju = $ceisaInfo->nomor_aju ?? $this->generateNomorAju($db);


        return view('export-import.dokumen-pabean.edit', [
            "page"           => "dashboard-export-import",
            "subPageGroup"   => "export-import",
            "subPage"        => "dokumen-pabean-list",
            "containerFluid" => true,
            "header"         => $header,
            "ceisaInfo"      => $ceisaInfo,
            "dataDetail"     => $dataDetail,
            "items"          => $items,
            "nomorAju"       => $nomorAju
        ]);
    }

    private function generateNomorAju($db)
    {
        $currentYear = date('Y');
        $today       = date('Ymd');
        $prefix      = '000040NIW345';

        $lastCeisa = $db->table('bpb_ceisa')
                        ->where('nomor_aju', 'like', $prefix . $currentYear . '%')
                        ->orderBy('nomor_aju', 'desc')
                        ->first();

        if ($lastCeisa && $lastCeisa->nomor_aju && strlen($lastCeisa->nomor_aju) === 26) {
            $lastNoAju = $lastCeisa->nomor_aju;
            $lastSeq   = (int) substr($lastNoAju, -6);
            $nextSeq   = str_pad($lastSeq + 1, 6, '0', STR_PAD_LEFT);
            return $prefix . $today . $nextSeq;
        }

        return $prefix . $today . '000001';
    }

    public function updateDraft($id, Request $request)
    {
        DB::connection('mysql_sb')->beginTransaction();

        try {
            $dokumenInput = $request->input('dok', []);
            $dokumenList = array_values(array_filter($dokumenInput, function($dok) {
                return !empty($dok['kode']) || !empty($dok['nomor']);
            }));

            $kontainerInput = $request->input('kontainer', []);
            $kontainerList = array_values(array_filter($kontainerInput, function($kont) {
                return !empty($kont['nomorKontainer']);
            }));

            $kemasanInput = $request->input('kemasan', []);
            $kemasanList = array_values(array_filter($kemasanInput, function($kem) {
                return isset($kem['jumlahKemasan']) && $kem['jumlahKemasan'] !== '';
            }));
            foreach ($kemasanList as &$k) {
                $k['jumlahKemasan'] = (float) $k['jumlahKemasan'];
            }


            $pungutan = $request->input('pungutan', []);
            if(isset($pungutan['nilai'])) {
                $pungutan['nilai'] = (float) $pungutan['nilai'];
            }

            $payloadJson = [
                'kodeKantor'           => $request->input('kodeKantor', '050100'),
                'kodeTujuanPengiriman' => $request->input('kodeTujuanPengiriman', '1'),
                'bruto'                => (float) $request->input('bruto', 0),
                'netto'                => (float) $request->input('netto', 0),
                'volume'               => (float) $request->input('volume', 0),
                'hargaPenyerahan'      => (float) $request->input('hargaPenyerahan', 0),
                'cif'                  => (float) $request->input('cif', 0),
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
                'jumlahKontainer'      => (int) $request->input('jumlahKontainer', 0),
                'entitas'              => $request->input('entitas', []),
                'pengangkut'           => $request->input('pengangkut', []),
                'pungutan'             => $pungutan,
                'dok'                  => $dokumenList,
                'kontainer'            => $kontainerList,
                'kemasan'              => $kemasanList,
                'barang'               => $request->input('barang', [])
            ];


            DB::connection('mysql_sb')->table('bpb_ceisa')->updateOrInsert(
                ['bpbno' => $id],
                [
                    'tanggal_aju'  => $request->input('tanggalAju', date('Y-m-d')),
                    'nomor_aju'    => $request->input('nomorAju'),
                    'payload_json' => json_encode($payloadJson),
                    'updated_at'   => date('Y-m-d H:i:s'),
                    'bpbno_int'    => $request->input('bpbno_int') ?? null
                ]
            );

            DB::connection('mysql_sb')->commit();

            return redirect()->route('dokumen-pabean-index')
                             ->with('success', 'Data draft dokumen CEISA berhasil disimpan!');

        } catch (\Exception $e) {
            DB::connection('mysql_sb')->rollBack();
            \Illuminate\Support\Facades\Log::error('Error Update Draft CEISA: ' . $e->getMessage());

            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
        }
    }

    public function getDraftData($noAju)
    {
        try {

            $responseCeisa = $this->ceisaService->getStatusDraft($noAju);

            if ($responseCeisa && isset($responseCeisa['status']) && in_array(strtolower($responseCeisa['status']), ['ok', 'success'])) {
                return response()->json([
                    'status'         => 200,
                    'message'        => 'Status draft berhasil ditarik dari CEISA!',
                    'ceisa_response' => $responseCeisa
                ]);
            } else {
                return response()->json([
                    'status'         => 404,
                    'message'        => 'Draft tidak ditemukan di server CEISA.',
                    'ceisa_error'    => $responseCeisa
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStatusPeriode(Request $request)
    {
        try {
            $tgl_awal  = $request->input('tgl_awal',  date('Y-m-d', strtotime('-30 days')));
            $tgl_akhir = $request->input('tgl_akhir', date('Y-m-d'));

            $responseCeisa = $this->ceisaService->cekStatus();
            if (!$responseCeisa || !in_array(strtolower($responseCeisa['status'] ?? ''), ['ok', 'success'])) {
                return response()->json([
                    'status'  => 422,
                    'message' => 'Gagal mengambil data dari server CEISA.',
                    'raw'     => $responseCeisa
                ], 422);
            }

            $dataStatus = $responseCeisa['dataStatus'] ?? [];
            $dataRespon = $responseCeisa['dataRespon']  ?? [];

            $awal  = \Carbon\Carbon::parse($tgl_awal)->startOfDay();
            $akhir = \Carbon\Carbon::parse($tgl_akhir)->endOfDay();


            // Filter dataStatus berdasarkan waktuStatus dalam range tanggal
            $filteredStatus = array_filter($dataStatus, function ($item) use ($awal, $akhir) {
                if (empty($item['waktuStatus'])) return false;
                try {
                    $waktu = \Carbon\Carbon::parse($item['waktuStatus']);
                    return $waktu->between($awal, $akhir);
                } catch (\Exception $e) {
                    return false;
                }
            });

            // Filter dataRespon berdasarkan waktuRespon dalam range tanggal
            $filteredRespon = array_filter($dataRespon, function ($item) use ($awal, $akhir) {
                if (empty($item['waktuRespon'])) return false;
                try {
                    $waktu = \Carbon\Carbon::parse($item['waktuRespon']);
                    return $waktu->between($awal, $akhir);
                } catch (\Exception $e) {
                    return false;
                }
            });

            // Group by nomorAju untuk status
            $grouped = [];
            foreach ($filteredStatus as $item) {
                $noAju = $item['nomorAju'] ?? 'UNKNOWN';
                if (!isset($grouped[$noAju])) {
                    $grouped[$noAju] = [
                        'nomorAju'     => $noAju,
                        'nomorDaftar'  => $item['nomorDaftar']  ?? null,
                        'tanggalDaftar'=> $item['tanggalDaftar'] ?? null,
                        'statusList'   => [],
                        'responList'   => [],
                    ];
                }
                $grouped[$noAju]['statusList'][] = $item;
                // Update nomorDaftar jika ada
                if (!empty($item['nomorDaftar'])) {
                    $grouped[$noAju]['nomorDaftar']   = $item['nomorDaftar'];
                    $grouped[$noAju]['tanggalDaftar'] = $item['tanggalDaftar'] ?? null;
                }
            }

            // Tambahkan dataRespon ke dalam group yang cocok
            foreach ($filteredRespon as $item) {
                $noAju = $item['nomorAju'] ?? 'UNKNOWN';
                if (!isset($grouped[$noAju])) {
                    $grouped[$noAju] = [
                        'nomorAju'      => $noAju,
                        'nomorDaftar'   => $item['nomorDaftar']  ?? null,
                        'tanggalDaftar' => $item['tanggalDaftar'] ?? null,
                        'statusList'    => [],
                        'responList'    => [],
                    ];
                }
                $grouped[$noAju]['responList'][] = $item;
            }

            // Sort each group's statusList descending by waktuStatus
            foreach ($grouped as &$grp) {
                usort($grp['statusList'], function ($a, $b) {
                    return strcmp($b['waktuStatus'] ?? '', $a['waktuStatus'] ?? '');
                });
                usort($grp['responList'], function ($a, $b) {
                    return strcmp($b['waktuRespon'] ?? '', $a['waktuRespon'] ?? '');
                });
            }
            unset($grp);

            // Sort groups by latest waktuStatus descending
            uasort($grouped, function ($a, $b) {
                $aTime = $a['statusList'][0]['waktuStatus'] ?? '0000-00-00';
                $bTime = $b['statusList'][0]['waktuStatus'] ?? '0000-00-00';
                return strcmp($bTime, $aTime);
            });

            return response()->json([
                'status'    => 200,
                'message'   => 'Berhasil mengambil status periode dari CEISA.',
                'tgl_awal'  => $tgl_awal,
                'tgl_akhir' => $tgl_akhir,
                'total'     => count($grouped),
                'data'      => array_values($grouped),
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('CEISA getStatusPeriode Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }
}
