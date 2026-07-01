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

            $currentUser = auth()->user();
            $isAllowedRollback = $currentUser && (
                in_array(strtolower($currentUser->username ?? ''), ['deti', 'admin']) ||
                in_array(strtolower($currentUser->name ?? ''), ['deti', 'admin'])
            );

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
                ->addColumn('action', function($row) use ($jenis, $isAllowedRollback) {
                    $noAju = $row->nomor_aju_ceisa ?? '';
                    $tglAju = ($row->tanggal_aju && $row->tanggal_aju != '0000-00-00') ? $row->tanggal_aju : '';

                    $btn = '<div class="d-flex justify-content-center">';
                    $editUrl = '#';
                    if ($row->jenis_dok == 'BC 2.3') {
                        $editUrl = route('dokumen-pabean-edit-bc23', ['id' => $row->trx_no_par]);
                    }
                    if ($row->jenis_dok == 'BC 4.0' && $jenis == 'Pemasukan') {
                        $editUrl = route('dokumen-pabean-edit', ['id' => $row->trx_no_par, 'trx' => $jenis]);
                    }

                    if($row->jenis_dok == 'BC 2.7') {
                        $editUrl = route('dokumen-pabean-edit-bc27', ['id' => $row->trx_no_par]);
                    }

                    if($row->jenis_dok == 'BC 3.0') {
                        $editUrl = route('dokumen-pabean-edit-bc30', ['id' => $row->trx_no_par]);
                    }

                    if($row->jenis_dok == 'BC 3.3' && $jenis == 'Pengeluaran') {
                        $editUrl = route('dokumen-pabean-edit-bc33', ['id' => $row->trx_no_par]);
                    }

                    if($row->jenis_dok == 'BC 4.1' && $jenis == 'Pengeluaran') {
                        $editUrl = route('dokumen-pabean-edit-bc41', ['id' => $row->trx_no_par]);
                    }

                    $btn .= '<a href="' . $editUrl . '" class="btn btn-sm btn-info mr-1" title="Edit Dokumen"><i class="fas fa-edit"></i></a>';

                    if($row->ceisa_status == 1) {
                        $btn .= '<button type="button" class="btn btn-sm btn-secondary mr-1 btn-status" title="Status CEISA" data-noaju="' . $noAju . '" data-jenis_bc="' . $row->jenis_dok . '"><i class="fas fa-check"></i></button>';
                        if ($isAllowedRollback) {
                            $btn .= '<button type="button" class="btn btn-sm btn-warning mr-1 btn-rollback" title="Rollback Status CEISA" data-id="' . $row->trx_no_par . '" data-noaju="' . ($noAju ?: 'BELUM ADA') . '"><i class="fas fa-undo"></i></button>';
                        }
                    } else {
                        $btn .= '<button type="button" class="btn btn-sm btn-success mr-1 btn-kirim"
                            data-id="' . $row->trx_no_par . '"
                            data-noaju="' . $noAju . '"
                            data-tglaju="' . $tglAju . '"
                            data-jenis_bc="' . $row->jenis_dok . '"
                            title="Kirim ke CEISA"><i class="fas fa-paper-plane"></i></button>';
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

            if (empty($nomorAju) || strlen($nomorAju) !== 26) {
                $nomorAju = $this->generateNomorAju($db);
            }

            // if (empty($nomorAju) || strlen($nomorAju) != 26) {
            //     $currentYear  = date('Y');
            //     $today        = date('Ymd');
            //     $kodeKantor   = !empty($draft['kodeKantor']) ? $draft['kodeKantor'] : '050500';
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
                        "kodeDokumen" => trim(explode(' - ', $d['kode'])[0]),
                        "nomorDokumen" => $d['nomor'],
                        "seriDokumen" => $seriDok++,
                        "tanggalDokumen" => !empty($d['tgl']) ? $d['tgl'] : date('Y-m-d')
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
            $headerPungutan = [];
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
                        "kodeNegaraAsal"   => !empty($brg['kodeNegaraAsal']) ? $brg['kodeNegaraAsal'] : "ID",
                        "ndpbm"            => 0.00,
                        "uangMuka"         => 0.00,
                        "nilaiJasa"        => (float) ($brg['nilaiJasa'] ?? 0.00),
                        "barangTarif"      => array_map(function($t) use ($brg, $index, $hargaPenyerahanItem, &$headerPungutan) {
                            $kodeJenisPungutan = $t['kodeJenisPungutan'] ?? "PPN";
                            $kodeFasilitasTarif = $t['kodeFasilitasTarif'] ?? "3";
                            $tarifPersen = (float) ($t['tarif'] ?? 11);
                            $tarifFasilitas = (float) ($t['tarifFasilitas'] ?? ($kodeFasilitasTarif == '1' ? 0 : 100));

                            $taxAmount = $hargaPenyerahanItem * ($tarifPersen / 100);

                            $nilaiFasilitas = 0;
                            $nilaiBayar = 0;
                            if ($kodeFasilitasTarif == '1') {
                                $nilaiBayar = $taxAmount;
                            } else {
                                $nilaiFasilitas = $taxAmount * ($tarifFasilitas / 100);
                                $nilaiBayar = $taxAmount - $nilaiFasilitas;
                            }

                            $finalBayar = (float) ($t['nilaiBayar'] ?? 0) > 0 ? (float) ($t['nilaiBayar'] ?? 0) : round($nilaiBayar);
                            $finalFasilitas = (float) ($t['nilaiFasilitas'] ?? 0) > 0 ? (float) ($t['nilaiFasilitas'] ?? 0) : round($nilaiFasilitas);

                            $key = $kodeJenisPungutan . '_' . $kodeFasilitasTarif;
                            if (!isset($headerPungutan[$key])) {
                                $headerPungutan[$key] = [
                                    "kodeFasilitasTarif" => $kodeFasilitasTarif,
                                    "kodeJenisPungutan"  => $kodeJenisPungutan,
                                    "nilaiPungutan"      => 0
                                ];
                            }
                            $headerPungutan[$key]["nilaiPungutan"] += ($finalBayar + $finalFasilitas);

                            return [
                                "kodeJenisTarif"     => "1",
                                "jumlahSatuan"       => (float) ($brg['jumlahSatuan'] ?? 0),
                                "kodeFasilitasTarif" => $kodeFasilitasTarif,
                                "kodeSatuanBarang"   => $brg['kodeSatuanBarang'] ?? "",
                                "nilaiBayar"         => $finalBayar,
                                "nilaiFasilitas"     => $finalFasilitas,
                                "nilaiSudahDilunasi" => (float) ($t['nilaiSudahDilunasi'] ?? 0),
                                "seriBarang"         => (int) ($brg['seriBarang'] ?? ($index + 1)),
                                "tarif"              => $tarifPersen,
                                "tarifFasilitas"     => $tarifFasilitas,
                                "kodeJenisPungutan"  => $kodeJenisPungutan
                            ];
                        }, (function() use ($brg) {
                            $list = isset($brg['barangTarif']) && is_array($brg['barangTarif']) ? $brg['barangTarif'] : [];
                            if (!empty($list) && !isset($list[0])) {
                                return [$list];
                            }
                            return array_values($list);
                        })())
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
                            (function() use ($hargaPenyerahanItem, $index, $item, &$headerPungutan) {
                                $taxAmount = $hargaPenyerahanItem * 0.11; // 11% default PPN
                                $nilaiFas = round($taxAmount);

                                $key = 'PPN_3';
                                if (!isset($headerPungutan[$key])) {
                                    $headerPungutan[$key] = [
                                        "kodeFasilitasTarif" => "3",
                                        "kodeJenisPungutan"  => "PPN",
                                        "nilaiPungutan"      => 0
                                    ];
                                }
                                $headerPungutan[$key]["nilaiPungutan"] += $nilaiFas;

                                return [
                                    "kodeJenisTarif"     => "1",
                                    "jumlahSatuan"       => (float) $item->qty,
                                    "kodeFasilitasTarif" => "3",
                                    "kodeSatuanBarang"   => $item->unit,
                                    "nilaiBayar"         => 0.00,
                                    "nilaiFasilitas"     => $nilaiFas,
                                    "nilaiSudahDilunasi" => 0.00,
                                    "seriBarang"         => ($index + 1),
                                    "tarif"              => 11.00,
                                    "tarifFasilitas"     => 100.00,
                                    "kodeJenisPungutan"  => "PPN"
                                ];
                            })()
                        ]
                    ];
                }
            }

            $payload = [
                "idPlatform"           => config('ceisa.id_platform_live', ''),
                "asalData"             => "S",
                "asuransi"             => (float) ($draft['asuransi'] ?? 0.00),
                "bruto"                => max((float) ($draft['bruto'] ?? $header->berat_kotor ?? 0.00), (float) ($draft['netto'] ?? $header->berat_bersih ?? 0.00)),
                "cif"                  => (float) ($draft['cif'] ?? 0.00),
                "kodeJenisTpb"         => $draft['jenisTpb'] ?? "1",
                "freight"              => (float) ($draft['freight'] ?? 0.00),
                "hargaPenyerahan"      => (float) ($draft['hargaPenyerahan'] ?? $totalHargaPenyerahan),
                "idPengguna"           => "",
                "jabatanTtd"           => $draft['jabatanTtd'] ?? "EXIM STAFF",
                "namaTtd"              => $draft['namaTtd'] ?? "USER EXIM",
                "nik"                  => "",
                "kodeKantor"           => $draft['kodeKantor'] ?? "050500",
                "kotaTtd"              => $draft['kotaTtd'] ?? "BANDUNG",
                "jumlahKontainer"      => (int) ($draft['jumlahKontainer'] ?? 0),
                "kodeDokumen"          => "40",
                "kodeTujuanPengiriman" => $draft['kodeTujuanPengiriman'] ?? "1",
                "kodeTutupPu"          => $draft['kodeTutupPu'] ?? "11",
                "tanggalTiba"          => $draft['tanggalTiba'] ?? date('Y-m-d'),
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
                        "tanggalIjinEntitas" => $draft['entitas'][3]['tanggalIjinEntitas'] ?? "2026-01-20"
                    ],
                    [
                        "alamatEntitas"      => $draft['entitas'][7]['alamatEntitas'] ?? $header->alamat_supplier ?? "",
                        "kodeEntitas"        => "7",
                        "kodeJenisApi"       => "2",
                        "kodeJenisIdentitas" => "5",
                        "kodeStatus"         => $draft['entitas'][7]['kodeStatus'] ?? "5",
                        "namaEntitas"        => $draft['entitas'][7]['namaEntitas'] ?? $header->supplier ?? "PEMILIK BARANG",
                        "nibEntitas"         => !empty($draft['entitas'][7]['nibEntitas']) ? $draft['entitas'][7]['nibEntitas'] : "0220103231143",
                        "nomorIdentitas"     => $draft['entitas'][7]['nomorIdentitas'] ?? $header->npwp_supplier ?? "",
                        "tanggalIjinEntitas" => $draft['entitas'][7]['tanggalIjinEntitas'] ?? "",
                        "seriEntitas"        => 2
                    ],
                    [
                        "alamatEntitas"      => $draft['entitas'][9]['alamatEntitas'] ?? $header->alamat_supplier ?? "",
                        "kodeEntitas"        => "9",
                        "kodeJenisApi"       => "2",
                        "kodeJenisIdentitas" => "5",
                        "kodeStatus"         => $draft['entitas'][9]['kodeStatus'] ?? "5",
                        "namaEntitas"        => $draft['entitas'][9]['namaEntitas'] ?? $header->supplier ?? "PENGIRIM BARANG",
                        "nibEntitas"         => !empty($draft['entitas'][9]['nibEntitas']) ? $draft['entitas'][9]['nibEntitas'] : "0220103231143",
                        "nomorIdentitas"     => $draft['entitas'][9]['nomorIdentitas'] ?? $header->npwp_supplier ?? "",
                        "seriEntitas"        => 3
                    ]
                ],

                "dokumen"    => $payloadDokumen,
                "pengangkut" => [
                    [
                        "namaPengangkut"  => $draft['pengangkut']['nama'] ?? "",
                        "nomorPengangkut" => $draft['pengangkut']['nomor'] ?? $header->nomor_mobil ?? "",
                        "kodeBendera"     => !empty($draft['pengangkut']['kodeBendera']) ? $draft['pengangkut']['kodeBendera'] : "ID",
                        "kodeCaraAngkut"  => !empty($draft['pengangkut']['kodeCaraAngkut']) ? (string)$draft['pengangkut']['kodeCaraAngkut'] : "1",
                        "seriPengangkut"  => 1
                    ]
                ],
                "kontainer"  => $payloadKontainer,
                "kemasan"    => $payloadKemasan,
                "pungutan"   => !empty($headerPungutan) ? array_values($headerPungutan) : [
                    [
                        "kodeFasilitasTarif" => "3",
                        "kodeJenisPungutan"  => "PPN",
                        "nilaiPungutan"      => 0.00
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
                    'jenis_bc'   => '4.0',
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

    private function getKantorList()
    {
        return [
            '000000' => 'DJBC',
            '001000' => 'SEKRETARIAT',
            '001100' => 'TP BIDANG I',
            '001200' => 'TP BIDANG II',
            '001300' => 'TP BIDANG III',
            '001400' => 'DIREKTORAT KI',
            '001500' => 'DIREKTORAT KBP',
            '002000' => 'DIREKTORAT TEKNIS',
            '003000' => 'DIREKTORAT FASILITAS',
            '004000' => 'DIREKTORAT CUKAI',
            '005000' => 'DIREKTORAT P2',
            '006000' => 'DIREKTORAT AUDIT',
            '007000' => 'DIREKTORAT KIAL',
            '008000' => 'DIREKTORAT PPS',
            '009000' => 'DIREKTORAT IKC',
            '010000' => 'KANWIL SUMUT',
            '010100' => 'KPPBC KUALANAMU',
            '010700' => 'KPPBC ELAWAN',
            '010800' => 'KPPBC MEDAN',
            '010900' => 'KPPBC PANGKALAN SUSU',
            '011000' => 'KPPBC PEMATANGSIANTAR',
            '011100' => 'KPPBC TELUK NIBUNG',
            '011200' => 'KPPBC KUALA TANJUNG',
            '011300' => 'KPPBC SIBOLGA',
            '011500' => 'KPPBC TELUK BAYUR',
            '011600' => 'BLBC KELAS II MEDAN',
            '020000' => 'KANWIL KHUSUS KEPRI',
            '020100' => 'KPPBC TBK',
            '020200' => 'KPPBC SAMBU BELAKANG PADANG',
            '020300' => 'KPPBC SELAT PANJANG',
            '020400' => 'KPU BATAM',
            '020500' => 'KPPBC TANJUNG PINANG',
            '020800' => 'KPPBC DABO SINGKEP',
            '020900' => 'KPPBC DUMAI',
            '021000' => 'KPPBC BAGAN SIAPIAPI',
            '021100' => 'KPPBC BENGKALIS',
            '021200' => 'KPPBC PEKANBARU',
            '021300' => 'KPPBC SIAK SRI INDRAPURA',
            '021500' => 'KPPBC TEMBILAHAN',
            '021700' => 'KPPBC TAREMPA',
            '021800' => 'PANGSAROP BATAM',
            '021900' => 'PANGSAROP TANJUNG BALAI KARIMUN',
            '030000' => 'KANWIL SUMBAGTIM',
            '030100' => 'KPPBC PALEMBANG',
            '030200' => 'KPPBC BENGKULU',
            '030300' => 'KPPBC PANGKALPINANG',
            '030500' => 'KPPBC TANJUNGPANDAN',
            '030600' => 'KPPBC JAMBI',
            '030700' => 'KPPBC BANDAR LAMPUNG',
            '040000' => 'KANWIL JAKARTA',
            '040300' => 'KPU TANJUNG PRIOK',
            '040400' => 'KPPBC JAKARTA',
            '040500' => 'BLBC KELAS I JAKARTA',
            '040600' => 'KPPBC KANTOR POS PASAR BARU',
            '040700' => 'PANGSAROP TANJUNG PRIOK',
            '050000' => 'KANWIL JABAR',
            '050100' => 'KPU SOEKARNO-HATTA',
            '050300' => 'KPPBC BOGOR',
            '050400' => 'KPPBC TMP MERAK',
            '050500' => 'KPPBC BANDUNG',
            '050600' => 'KPPBC TASIKMALAYA',
            '050700' => 'KPPBC CIREBON',
            '050800' => 'KPPBC PURWAKARTA',
            '050900' => 'KPPBC BEKASI',
            '051000' => 'KPPBC CIKARANG',
            '060000' => 'KANWIL JATENG DIY',
            '060100' => 'KPPBC TMP TANJUNG EMAS',
            '060200' => 'KPPBC PEKALONGAN',
            '060300' => 'KPPBC TMC KUDUS',
            '060400' => 'KPPBC CILACAP',
            '060600' => 'KPPBC SURAKARTA',
            '060700' => 'KPPBC YOGYAKARTA',
            '060800' => 'KPPBC SEMARANG',
            '061000' => 'KPPBC TEGAL',
            '061100' => 'KPPBC MAGELANG',
            '062000' => 'KPPBC PURWOKERTO',
            '070000' => 'KANWIL JATIM I',
            '070100' => 'KPPBC TMP TANJUNG PERAK',
            '070200' => 'KPPBC MADURA',
            '070300' => 'KPPBC GRESIK',
            '070400' => 'KPPBC BOJONEGORO',
            '070500' => 'KPPBC TMP JUANDA',
            '070600' => 'KPPBC TMC MALANG',
            '070700' => 'KPPBC BLITAR',
            '070800' => 'KPPBC TMC KEDIRI',
            '070900' => 'KPPBC TULUNG AGUNG',
            '071000' => 'KPPBC MADIUN',
            '071100' => 'KPPBC JEMBER',
            '071200' => 'KPPBC PROBOLINGGO',
            '071300' => 'KPPBC PASURUAN',
            '071400' => 'BLBC KELAS II SURABAYA',
            '071500' => 'KPPBC SIDOARJO',
            '080000' => 'KANWIL BALI,NTB DAN NTT',
            '080100' => 'KPPBC TMP NGURAH RAI',
            '080200' => 'KPPBC DENPASAR',
            '080300' => 'KPPBC MATARAM',
            '080400' => 'KPPBC SUMBAWA',
            '080500' => 'KPPBC KUPANG',
            '080700' => 'KPPBC MAUMERE',
            '081200' => 'KPPBC BENOA',
            '081300' => 'KPPBC ATAPUPU',
            '081400' => 'KPPBC ATAMBUA',
            '090000' => 'KANWIL KALBAGBAR',
            '090100' => 'KPPBC PONTIANAK',
            '090200' => 'KPPBC ENTIKONG',
            '090400' => 'KPPBC KETAPANG',
            '090500' => 'KPPBC SINTETE',
            '090700' => 'KPPBC SAMPIT',
            '090800' => 'KPPBC PANGKALAN BUN',
            '090900' => 'KPPBC PULANG PISAU',
            '091000' => 'KPPBC NANGA BADAU',
            '092000' => 'KPPBC JAGOI BABANG',
            '100000' => 'KANWIL KALBAGTIM',
            '100100' => 'KPPBC BANJARMASIN',
            '100200' => 'KPPBC KOTABARU',
            '100300' => 'KPPBC BALIKPAPAN',
            '100500' => 'KPPBC SAMARINDA',
            '100600' => 'KPPBC BONTANG',
            '100800' => 'KPPBC TARAKAN',
            '100900' => 'KPPBC NUNUKAN',
            '101000' => 'KPPBC SANGATTA',
            '110000' => 'KANWIL SULBAGSEL',
            '110100' => 'KPPBC MAKASSAR',
            '110300' => 'KPPBC PAREPARE',
            '110400' => 'KPPBC MALILI',
            '110500' => 'KPPBC BAJOE',
            '110600' => 'KPPBC KENDARI',
            '110700' => 'KPPBC POMALAA',
            '110800' => 'KPPBC PANTOLOAN',
            '110900' => 'KPPBC MOROWALI',
            '111000' => 'KPPBC LUWUK',
            '111100' => 'KPPBC BITUNG',
            '111200' => 'KPPBC MANADO',
            '111300' => 'KPPBC GORONTALO',
            '111400' => 'PANGSAROP PANTOLOAN',
            '120000' => 'KANWIL MALUKU',
            '120100' => 'KPPBC AMBON',
            '120200' => 'KPPBC TERNATE',
            '120300' => 'KPPBC SORONG',
            '120400' => 'KPPBC MANOKWARI',
            '120500' => 'KPPBC FAK-FAK',
            '120600' => 'KPPBC JAYAPURA',
            '120700' => 'KPPBC MERAUKE',
            '120800' => 'KPPBC AMAMAPARE',
            '120900' => 'KPPBC BIAK',
            '121000' => 'KPPBC TUAL',
            '121100' => 'PANGSAROP SORONG',
            '122000' => 'KPPBC BINTUNI',
            '122100' => 'KPPBC KAIMANA',
            '122200' => 'KPPBC NABIRE',
            '122300' => 'KPPBC BABO',
            '130000' => 'KANWIL ACEH',
            '130100' => 'KPPBC BANDA ACEH',
            '130300' => 'KPPBC SABANG',
            '130400' => 'KPPBC MEULABOH',
            '130500' => 'KPPBC LHOKSEUMAWE',
            '130600' => 'KPPBC KUALA LANGSA',
            '140000' => 'KANWIL RIAU',
            '150000' => 'KANWIL BANTEN',
            '150300' => 'KPPBC TANGERANG',
            '160000' => 'KANWIL JATIM II',
            '160200' => 'KPPBC MARUNDA',
            '160700' => 'KPPBC BANYUWANGI',
            '170000' => 'KANWIL SUMBAGBAR',
            '180000' => 'KANWIL KALBAGSEL',
            '180100' => 'KPPBC Nashta',
            '180200' => 'KPPBC Nashta',
            '190000' => 'KANWIL SULBAGTARA',
            '200000' => 'KANWIL KHUSUS PAPUA',
            '760000' => 'PUSDIKLAT BEA DAN CUKAI',
            '999999' => 'UNIT LAIN DI LUAR DJBC'
        ];
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

        // Redirect ke form spesifik berdasarkan jenis_bc
        if ($ceisaInfo && $ceisaInfo->jenis_bc == '23') {
            return $this->editBc23($id, $request);
        }
        if ($ceisaInfo && in_array($ceisaInfo->jenis_bc, ['27', '2.7'])) {
            return $this->editBc27($id, $request);
        }
        if ($ceisaInfo && in_array($ceisaInfo->jenis_bc, ['30', '3.0'])) {
            return $this->editBc30($id, $request);
        }

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
            "nomorAju"       => $nomorAju,
            "kantorList"     => $this->getKantorList()
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

        $localSeq = 0;
        if ($lastCeisa && $lastCeisa->nomor_aju && strlen($lastCeisa->nomor_aju) === 26) {
            $localSeq = (int) substr($lastCeisa->nomor_aju, -6);
        }

        $ceisaSeq = $this->ceisaService->getLastSequenceFromCeisa($prefix . $currentYear, '40');

        $maxSeq  = max($localSeq, $ceisaSeq);
        $nextSeq = str_pad($maxSeq + 1, 6, '0', STR_PAD_LEFT);

        return $prefix . $today . $nextSeq;
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
                'kodeKantor'           => $request->input('kodeKantor', '050500'),
                'jenisTpb'              => $request->input('jenisTPB', '1'),
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
                'kodeTutupPu'          => $request->input('kodeTutupPu'),
                'tanggalTiba'          => $request->input('tanggalTiba'),
                'namaTtd'              => $request->input('namaTtd'),
                'jabatanTtd'           => $request->input('jabatanTtd'),
                'kotaTtd'              => $request->input('kotaTtd'),
                'tanggalTtd'           => $request->input('tanggalTtd', date('Y-m-d')),
                'jumlahKontainer'      => (int) $request->input('jumlahKontainer', 0),
                'kodeKantorBongkar'    => $request->input('kodeKantorBongkar', ''),
                'kodePelBongkar'       => $request->input('kodePelBongkar', ''),
                'kodePelMuat'          => $request->input('kodePelMuat', ''),
                'kodePelTransit'       => $request->input('kodePelTransit', ''),
                'kodeTps'              => $request->input('kodeTps', ''),
                'kodeTujuanTpb'        => $request->input('kodeTujuanTpb', ''),
                'kodeIncoterm'         => $request->input('kodeIncoterm', ''),
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
                    'jenis_bc'     => '4.0',
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

            $responseCeisa = $this->ceisaService->getStatus($noAju);

            if ($responseCeisa['successful'] && isset($responseCeisa['body']['status']) && in_array(strtolower($responseCeisa['body']['status']), ['ok', 'success'])) {
                return response()->json([
                    'status'         => 200,
                    'message'        => 'Status draft berhasil ditarik dari CEISA!',
                    'ceisa_response' => $responseCeisa['body']
                ]);
            } else {
                return response()->json([
                    'status'         => 404,
                    'message'        => 'Draft/Respon tidak ditemukan di server CEISA.',
                    'ceisa_error'    => $responseCeisa['body'] ?? 'Tidak ada respon dari CEISA'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getResponData($noAju, Request $request)
    {
        try {
            $jenisBcInput = $request->input('jenis_bc', 'BC 4.0');
            $currentYear  = date('Y');
            $today        = date('Ymd');
            $prefix       = '000040NIW345';
            $jenisBcCeisa = '40';

            if ($jenisBcInput == 'BC 2.3') {
                $prefix = '000023NIW345';
                $jenisBcCeisa = '23';
            } elseif ($jenisBcInput == 'BC 2.7') {
                $prefix = '000027NIW345';
                $jenisBcCeisa = '27';
            } elseif ($jenisBcInput == 'BC 3.0') {
                $prefix = '000030NIW779';
                $jenisBcCeisa = '30';
            } elseif ($jenisBcInput == 'BC 3.3') {
                $prefix = '000033NIW779';
                $jenisBcCeisa = '33';
            } elseif ($jenisBcInput == 'BC 4.0') {
                $prefix = '000040NIW345';
                $jenisBcCeisa = '40';
            }

            $db = DB::connection('mysql_sb');
            $query = $db->table('bpb_ceisa')->where('nomor_aju', 'like', $prefix . $currentYear . '%');
            if ($jenisBcInput == 'BC 2.3') {
                $query->where(function($q) { $q->where('jenis_bc', '2.3')->orWhere('jenis_bc', '23'); });
            } elseif ($jenisBcInput == 'BC 2.7') {
                $query->where(function($q) { $q->where('jenis_bc', '2.7')->orWhere('jenis_bc', '27'); });
            } elseif ($jenisBcInput == 'BC 3.0') {
                $query->where('jenis_bc', '3.0');
            } elseif ($jenisBcInput == 'BC 3.3') {
                $query->where('jenis_bc', '3.3');
            }
            $lastCeisa = $query->orderBy('nomor_aju', 'desc')->first();

            $localSeq = 0;
            $localLastNoAju = '-';
            if ($lastCeisa && $lastCeisa->nomor_aju && strlen($lastCeisa->nomor_aju) === 26) {
                $localLastNoAju = $lastCeisa->nomor_aju;
                $localSeq = (int) substr($localLastNoAju, -6);
            }

            $ceisaSeq = $this->ceisaService->getLastSequenceFromCeisa($prefix . $currentYear, $jenisBcCeisa);

            $maxSeq  = max($localSeq, $ceisaSeq);
            $nextSeq = str_pad($maxSeq + 1, 6, '0', STR_PAD_LEFT);
            $nextNoAju = $prefix . $today . $nextSeq;

            $responseCeisa = $this->ceisaService->cekStatus();

            return response()->json([
                'status'         => 200,
                'message'        => 'Data Last Sequence dan Respon berhasil ditarik!',
                'sequence_info'  => [
                    'jenis_bc'         => $jenisBcInput,
                    'prefix'           => $prefix . $currentYear,
                    'local_last_noaju' => $localLastNoAju,
                    'local_seq'        => $localSeq,
                    'ceisa_seq'        => $ceisaSeq,
                    'max_seq'          => $maxSeq,
                    'next_seq'         => $nextSeq,
                    'next_noaju'       => $nextNoAju
                ],
                'ceisa_response' => $responseCeisa
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rollbackStatus($id, Request $request)
    {
        try {
            $currentUser = auth()->user();
            $isAllowedRollback = $currentUser && (
                in_array(strtolower($currentUser->username ?? ''), ['deti', 'admin']) ||
                in_array(strtolower($currentUser->name ?? ''), ['deti', 'admin'])
            );

            if (!$isAllowedRollback) {
                return response()->json([
                    'status' => 403,
                    'message' => 'Anda tidak memiliki akses untuk melakukan rollback status.'
                ], 403);
            }

            $db = DB::connection('mysql_sb');
            $db->table('bpb_ceisa')
               ->where('bpbno', $id)
               ->orWhere('bpbno_int', $id)
               ->update([
                   'status' => 0,
                   'updated_at' => \Carbon\Carbon::now()
               ]);

            return response()->json([
                'status' => 200,
                'message' => 'Status CEISA berhasil di-rollback. Anda sekarang dapat mengirim ulang dokumen ini.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteDraft($noAju)
    {
        try {
            $result = $this->ceisaService->deleteDraft($noAju);

            if ($result['successful'] || $result['status_code'] == 200) {
                return response()->json([
                    'success' => true,
                    'message' => 'Draft dokumen CEISA berhasil dihapus'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus draft dari CEISA',
                'details' => $result['body'] ?? ''
            ], 400);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error Delete CEISA Draft: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menghapus draft',
                'error' => $e->getMessage()
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

    public function editBc23($id, Request $request)
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

        $nomorAju = $ceisaInfo->nomor_aju ?? $this->generateNomorAjuBc23($db);

        return view('export-import.dokumen-pabean.edit-bc23', [
            "page"           => "dashboard-export-import",
            "subPageGroup"   => "export-import",
            "subPage"        => "dokumen-pabean-list",
            "containerFluid" => true,
            "header"         => $header,
            "ceisaInfo"      => $ceisaInfo,
            "dataDetail"     => $dataDetail,
            "items"          => $items,
            "nomorAju"       => $nomorAju,
            "kantorList"     => $this->getKantorList()
        ]);
    }

    private function generateNomorAjuBc23($db)
    {
        $currentYear = date('Y');
        $today       = date('Ymd');
        $prefix      = '000023NIW345';

        $lastCeisa = $db->table('bpb_ceisa')
                        ->where('nomor_aju', 'like', $prefix . $currentYear . '%')
                        ->where(function($q) {
                            $q->where('jenis_bc', '2.3')->orWhere('jenis_bc', '23');
                        })
                        ->orderBy('nomor_aju', 'desc')
                        ->first();

        $localSeq = 0;
        if ($lastCeisa && $lastCeisa->nomor_aju && strlen($lastCeisa->nomor_aju) === 26) {
            $localSeq = (int) substr($lastCeisa->nomor_aju, -6);
        }

        $ceisaSeq = $this->ceisaService->getLastSequenceFromCeisa($prefix . $currentYear, '23');

        $maxSeq  = max($localSeq, $ceisaSeq);
        $nextSeq = str_pad($maxSeq + 1, 6, '0', STR_PAD_LEFT);

        return $prefix . $today . $nextSeq;
    }

    public function updateDraftBc23($id, Request $request)
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
            if (isset($pungutan['nilai'])) {
                $pungutan['nilai'] = (float) $pungutan['nilai'];
            }

            $payloadJson = [
                'kodeKantor'         => $request->input('kodeKantor', '050500'),
                'jenisTpb'          => $request->input('jenisTPB', '1'),
                'kodeKantorBongkar'  => $request->input('kodeKantorBongkar', ''),
                'kodeTujuanTpb'      => $request->input('kodeTujuanTpb', ''),
                'kodeTutupPu'        => $request->input('kodeTutupPu', ''),
                'bruto'              => (float) $request->input('bruto', 0),
                'netto'              => (float) $request->input('netto', 0),
                'hargaPenyerahan'    => (float) $request->input('hargaPenyerahan', 0),
                'cif'                => (float) $request->input('cif', 0),
                'fob'                => (float) $request->input('fob', 0),
                'asuransi'           => (float) $request->input('asuransi', 0),
                'kodeAsuransi'       => $request->input('kodeAsuransi', 'LN'),
                'freight'            => (float) $request->input('freight', 0),
                'biayaTambahan'      => (float) $request->input('biayaTambahan', 0),
                'biayaPengurang'     => (float) $request->input('biayaPengurang', 0),
                'kodeKenaPajak'      => $request->input('kodeKenaPajak', '1'),
                'ndpbm'              => (float) $request->input('ndpbm', 0) <= 0 && $request->input('kodeValuta', 'IDR') === 'IDR' ? 1 : (float) $request->input('ndpbm', 0),
                'nilaiBarang'        => (float) $request->input('nilaiBarang', 0),
                'kodeIncoterm'       => $request->input('kodeIncoterm', ''),
                'kodeValuta'         => $request->input('kodeValuta', 'IDR'),
                'kodePelMuat'        => $request->input('kodePelMuat', ''),
                'kodePelBongkar'     => $request->input('kodePelBongkar', ''),
                'kodePelTransit'     => $request->input('kodePelTransit', ''),
                'kodeTps'            => $request->input('kodeTps', ''),
                'jumlahKontainer'    => (int) $request->input('jumlahKontainer', 0),
                'nomorBc11'          => $request->input('nomorBc11', ''),
                'posBc11'            => $request->input('posBc11', ''),
                'subposBc11'         => $request->input('subposBc11', ''),
                'subsubposBc11'      => $request->input('subsubposBc11', ''),
                'tanggalBc11'        => $request->input('tanggalBc11', ''),
                'kodeBc11'           => $request->input('kodeBc11', ''),
                'nik'                => $request->input('nik', ''),
                'seri'               => (int) $request->input('seri', 0),
                'namaTtd'            => $request->input('namaTtd', ''),
                'jabatanTtd'         => $request->input('jabatanTtd', ''),
                'kotaTtd'            => $request->input('kotaTtd', ''),
                'tanggalTtd'         => $request->input('tanggalTtd', date('Y-m-d')),
                'tanggalTiba'        => $request->input('tanggalTiba', ''),
                'entitas'            => $request->input('entitas', []),
                'pengangkut'         => $request->input('pengangkut', []),
                'pungutan'           => $pungutan,
                'dok'                => $dokumenList,
                'kontainer'          => $kontainerList,
                'kemasan'            => $kemasanList,
                'barang'             => array_map(function($brg) use ($request) {
                    $ndpbm = (float) $request->input('ndpbm', 0) <= 0 && $request->input('kodeValuta', 'IDR') === 'IDR' ? 1 : (float) $request->input('ndpbm', 0);
                    $brg['cif'] = (float) ($brg['cif'] ?? 0);
                    $brg['cifRupiah'] = (float) ($brg['cifRupiah'] ?? 0);
                    if ($brg['cifRupiah'] <= 0 && $ndpbm > 0) {
                        $brg['cifRupiah'] = $brg['cif'] * $ndpbm;
                    }
                    $brg['fob'] = (float) ($brg['fob'] ?? 0);
                    $brg['asuransi'] = (float) ($brg['asuransi'] ?? 0);
                    $brg['freight'] = (float) ($brg['freight'] ?? 0);
                    $brg['hargaSatuan'] = (float) ($brg['hargaSatuan'] ?? 0);
                    $brg['netto'] = (float) ($brg['netto'] ?? 0);
                    $brg['bruto'] = (float) ($brg['bruto'] ?? $brg['netto'] ?? 0);
                    $brg['jumlahSatuan'] = (float) ($brg['jumlahSatuan'] ?? 0);
                    $brg['jumlahKemasan'] = (float) ($brg['jumlahKemasan'] ?? 0);
                    $brg['biayaTambahan'] = (float) ($brg['biayaTambahan'] ?? 0);
                    $brg['nilaiBarang'] = (float) ($brg['nilaiBarang'] ?? $brg['cif'] ?? 0);
                    return $brg;
                }, $request->input('barang', [])),
                'bc11Nomor'         => $request->input('nomorBc11', ''),
                'bc11Tanggal'       => $request->input('tanggalBc11', ''),
                'bc11Pos'          => $request->input('posBc11', ''),
                'bc11Subpos'       => $request->input('subposBc11', ''),
                'bc11Subsubpos'    => $request->input('subsubposBc11', ''),
                'bc11KodeBc'       => $request->input('kodeBc11', ''),
            ];

            DB::connection('mysql_sb')->table('bpb_ceisa')->updateOrInsert(
                ['bpbno' => $id],
                [
                    'tanggal_aju'  => $request->input('tanggalAju', date('Y-m-d')),
                    'nomor_aju'    => $request->input('nomorAju'),
                    'payload_json' => json_encode($payloadJson),
                    'jenis_bc'     => '2.3',
                    'updated_at'   => date('Y-m-d H:i:s'),
                    'bpbno_int'    => $request->input('bpbno_int') ?? null
                ]
            );

            DB::connection('mysql_sb')->commit();

            return redirect()->route('dokumen-pabean-index')
                             ->with('success', 'Data draft BC 2.3 berhasil disimpan!');

        } catch (\Exception $e) {
            DB::connection('mysql_sb')->rollBack();
            \Illuminate\Support\Facades\Log::error('Error Update Draft BC 2.3: ' . $e->getMessage());

            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
        }
    }

    public function sendCeisaBc23($id, Request $request)
    {
        $db = DB::connection('mysql_sb');

        try {
            $header = $db->table('bpb as a')
                ->join('mastersupplier as ms', 'a.id_supplier', '=', 'ms.id_supplier')
                ->where(function($query) use ($id) {
                    $query->where('a.bpbno', $id)->orWhere('a.bpbno_int', $id);
                })
                ->first();

            if (!$header) throw new \Exception("Data transaksi tidak ditemukan!");

            $ceisaInfo = $db->table('bpb_ceisa')->where('bpbno', $id)->first();
            if (!$ceisaInfo) throw new \Exception("Data CEISA belum disiapkan. Simpan draft terlebih dahulu.");

            $draft     = json_decode($ceisaInfo->payload_json ?? '{}', true);
            $nomorAju  = $ceisaInfo->nomor_aju ?? '';
            $tanggalAju = date('Y-m-d');

            $payloadDokumen = [];
            $invoiceDok = null;
            $transportDok = null;
            $otherDoks = [];
            foreach (($draft['dok'] ?? []) as $d) {
                if (!empty($d['kode']) && !empty($d['nomor'])) {
                    $kode = trim(explode(' - ', $d['kode'])[0]);
                    $doc = [
                        "kodeDokumen"    => $kode,
                        "nomorDokumen"   => $d['nomor'],
                        "tanggalDokumen" => !empty($d['tgl']) ? $d['tgl'] : date('Y-m-d')
                    ];
                    if ($kode === '380' && !$invoiceDok) {
                        $invoiceDok = $doc;
                    } elseif (in_array($kode, ['705', '740']) && !$transportDok) {
                        $transportDok = $doc;
                    } else {
                        $otherDoks[] = $doc;
                    }
                }
            }

            if ($transportDok) {
                array_unshift($otherDoks, $transportDok);
            } else {
                // Default fallback if B/L is missing in the frontend
                array_unshift($otherDoks, [
                    "kodeDokumen"    => "705",
                    "nomorDokumen"   => "-",
                    "tanggalDokumen" => $header->bpbdate ?? date('Y-m-d')
                ]);
            }

            if ($invoiceDok) {
                array_unshift($otherDoks, $invoiceDok);
            } else {
                // Default fallback if Invoice is missing in the frontend
                array_unshift($otherDoks, [
                    "kodeDokumen"    => "380",
                    "nomorDokumen"   => $header->invno ?? "-",
                    "tanggalDokumen" => $header->bpbdate ?? date('Y-m-d')
                ]);
            }

            $seriDok = 1;
            $payloadDokumen = array_map(function($d) use (&$seriDok) {
                $d['seriDokumen'] = $seriDok++;
                return $d;
            }, $otherDoks);

            $hasInvoice = false;
            $hasTransport = false;
            foreach ($payloadDokumen as $dok) {
                $kodeStr = explode(' - ', $dok['kodeDokumen'])[0];
                $kodeStr = trim($kodeStr);

                if ($kodeStr === '380') $hasInvoice = true;
                if (in_array($kodeStr, ['705', '740', '704', '741'])) $hasTransport = true;
            }

            // if (!$hasInvoice || !$hasTransport) {
            //     throw new \Exception("Validasi Gagal: Dokumen BC 2.3 wajib melampirkan INVOICE (380) dan B/L atau AWB (705/740). Silakan tambahkan di tab Dokumen Pendukung.");
            // }

            $payloadKontainer = [];
            $seriKont = 1;
            foreach (($draft['kontainer'] ?? []) as $k) {
                if (!empty($k['nomorKontainer'])) {
                    $payloadKontainer[] = [
                        "kodeJenisKontainer"  => $k['kodeJenisKontainer'],
                        "kodeTipeKontainer"   => $k['kodeTipeKontainer'],
                        "kodeUkuranKontainer" => $k['kodeUkuranKontainer'],
                        "nomorKontainer"      => strtoupper(trim($k['nomorKontainer'])),
                        "seriKontainer"       => $seriKont++
                    ];
                }
            }

            $payloadKemasan = [];
            $seriKem = 1;
            foreach (($draft['kemasan'] ?? []) as $k) {
                $payloadKemasan[] = [
                    "jumlahKemasan"    => (int) ($k['jumlahKemasan'] ?? 0),
                    "kodeJenisKemasan" => $k['kodeJenisKemasan'] ?? "CT",
                    "merkKemasan"      => $k['merkKemasan'] ?? "-",
                    "seriKemasan"      => $seriKem++
                ];
            }
            if (empty($payloadKemasan)) {
                $payloadKemasan[] = ["jumlahKemasan" => 0, "kodeJenisKemasan" => "CT", "merkKemasan" => "-", "seriKemasan" => 1];
            }

            $totalHargaPenyerahan = 0;
            $totalCif = 0;
            $totalFob = 0;
            $totalFreight = 0;
            $totalAsuransi = 0;
            $totalDiskon = 0;
            $arrayBarang = [];

            if (count($draft['barang'] ?? []) === 1) {
                if (empty($draft['barang'][0]['cif']) && !empty($draft['cif'])) $draft['barang'][0]['cif'] = $draft['cif'];
                if (empty($draft['barang'][0]['fob']) && !empty($draft['fob'])) $draft['barang'][0]['fob'] = $draft['fob'];
                if (empty($draft['barang'][0]['freight']) && !empty($draft['freight'])) $draft['barang'][0]['freight'] = $draft['freight'];
                if (empty($draft['barang'][0]['asuransi']) && !empty($draft['asuransi'])) $draft['barang'][0]['asuransi'] = $draft['asuransi'];
                if (empty($draft['barang'][0]['diskon']) && !empty($draft['diskon'])) $draft['barang'][0]['diskon'] = $draft['diskon'];
            }

            foreach (($draft['barang'] ?? []) as $index => $brg) {
                $cifItem = (float) ($brg['cif'] ?? 0);
                $nettoItem = (float) ($brg['netto'] ?? 0);

                if ($cifItem <= 0 || $nettoItem <= 0) {
                    $itemNum = $index + 1;
                    throw new \Exception("Validasi Gagal: Harga CIF dan Berat Bersih (Netto) pada Barang ke-{$itemNum} harus lebih besar dari 0.");
                }

                $hargaPenyerahanItem = (float) ($brg['hargaPenyerahan'] ?? 0);
                $totalHargaPenyerahan += $hargaPenyerahanItem;
                $totalCif += (float) ($brg['cif'] ?? 0);
                $totalFob += (float) ($brg['fob'] ?? 0);
                $totalFreight += (float) ($brg['freight'] ?? 0);
                $totalAsuransi += (float) ($brg['asuransi'] ?? 0);
                $totalDiskon += (float) ($brg['diskon'] ?? 0);

                $barangTarif = [];
                $pungutanMap = [];
                if (!empty($brg['barangTarif']) && is_array($brg['barangTarif'])) {
                    foreach ($brg['barangTarif'] as $tarif) {
                            $kodeJenisPungutan = !empty($tarif['kodeJenisPungutan']) ? strtoupper(trim($tarif['kodeJenisPungutan'])) : "BM";
                            $kodeFasilitasTarif = !empty($tarif['kodeFasilitasTarif']) ? $tarif['kodeFasilitasTarif'] : "3";
                            $tarifPersen = (float) ($tarif['tarif'] ?? 0);
                            $tarifFasilitas = (float) ($tarif['tarifFasilitas'] ?? ($kodeFasilitasTarif == '1' ? 0 : 100));

                            $cifRupiah = (float)($brg['cif'] ?? 0) * (float)($brg['ndpbm'] ?? 0);
                            $bmAmount = $cifRupiah * ($kodeJenisPungutan == 'BM' ? $tarifPersen / 100 : 0);
                            $nilaiDasar = ($kodeJenisPungutan == 'BM') ? $cifRupiah : ($cifRupiah + ($cifRupiah * 0.1)); // simplified
                            $taxAmount = $nilaiDasar * ($tarifPersen / 100);

                            $nilaiFasilitas = 0;
                            $nilaiBayar = 0;
                            if ($kodeFasilitasTarif == '1') {
                                $nilaiBayar = $taxAmount;
                            } else {
                                $nilaiFasilitas = $taxAmount * ($tarifFasilitas / 100);
                                $nilaiBayar = $taxAmount - $nilaiFasilitas;
                            }

                            $kodeJenisTarif = !empty($tarif['kodeJenisTarif']) ? $tarif['kodeJenisTarif'] : "1";

                            $finalNilaiBayar = (float) ($tarif['nilaiBayar'] ?? 0) > 0 ? (float) ($tarif['nilaiBayar'] ?? 0) : round($nilaiBayar);
                            $finalNilaiFasilitas = (float) ($tarif['nilaiFasilitas'] ?? 0) > 0 ? (float) ($tarif['nilaiFasilitas'] ?? 0) : round($nilaiFasilitas);

                            $pungutanMap[$kodeJenisPungutan] = [
                                "kodeJenisTarif"     => $kodeJenisTarif,
                                "jumlahSatuan"       => (float) ($tarif['jumlahSatuan'] ?? $brg['jumlahSatuan'] ?? 0),
                                "kodeFasilitasTarif" => $kodeFasilitasTarif,
                                "kodeSatuanBarang"   => !empty($tarif['kodeSatuanBarang']) ? $tarif['kodeSatuanBarang'] : (!empty($brg['kodeSatuanBarang']) ? $brg['kodeSatuanBarang'] : ""),
                                "kodeJenisPungutan"  => $kodeJenisPungutan,
                                "nilaiBayar"         => $finalNilaiBayar,
                                "nilaiFasilitas"     => $finalNilaiFasilitas,
                                "nilaiSudahDilunasi" => (float) ($tarif['nilaiSudahDilunasi'] ?? 0),
                                "seriBarang"         => (int) ($brg['seriBarang'] ?? ($index + 1)),
                                "tarif"              => $tarifPersen,
                                "tarifFasilitas"     => $tarifFasilitas,
                            ];
                    }
                }

                // Force the order: BM, PPH, PPN (as required by CEISA BC 2.3 schema)
                $orderedKeys = ['BM', 'PPH', 'PPN'];
                foreach ($orderedKeys as $pkey) {
                    if (isset($pungutanMap[$pkey])) {
                        $barangTarif[] = $pungutanMap[$pkey];
                    } else {
                        // Default empty entry if missing
                        $barangTarif[] = [
                            "kodeJenisTarif" => "1",
                            "jumlahSatuan" => (float)($brg['jumlahSatuan'] ?? 0),
                            "kodeFasilitasTarif" => "3",
                            "kodeSatuanBarang" => $brg['kodeSatuanBarang'] ?? "",
                            "kodeJenisPungutan" => $pkey,
                            "nilaiBayar" => 0,
                            "nilaiFasilitas" => 0,
                            "nilaiSudahDilunasi" => 0,
                            "seriBarang" => (int)($brg['seriBarang'] ?? ($index + 1)),
                            "tarif" => 0,
                            "tarifFasilitas" => 100
                        ];
                    }
                }

                // Add any other pungutan (CUKAI, PPNBM) if user inputted them, after the mandatory 3
                foreach ($pungutanMap as $k => $v) {
                    if (!in_array($k, $orderedKeys)) {
                        $barangTarif[] = $v;
                    }
                }

                $barangDokumen = [];
                foreach (($brg['barangDokumen'] ?? []) as $bd) {
                    if (!empty($bd['seriDokumen'])) {
                        $barangDokumen[] = [
                            "seriDokumen" => (string) $bd['seriDokumen']
                        ];
                    }
                }

                $arrayBarang[] = [
                    "asuransi"          => (float) ($brg['asuransi'] ?? 0),
                    "cif"               => (float) ($brg['cif'] ?? 0),
                    "cifRupiah"         => (float) ($brg['cifRupiah'] ?? 0),
                    "diskon"            => (float) ($brg['diskon'] ?? 0),
                    "fob"               => (float) ($brg['fob'] ?? 0),
                    "freight"           => (float) ($brg['freight'] ?? 0),
                    "hargaEkspor"       => (float) ($brg['hargaEkspor'] ?? 0),
                    "hargaPenyerahan"   => $hargaPenyerahanItem,
                    "hargaPerolehan"    => (float) ($brg['hargaPerolehan'] ?? 0),
                    "hargaSatuan"       => (float) ($brg['hargaSatuan'] ?? 0),
                    "isiPerKemasan"     => (float) ($brg['isiPerKemasan'] ?? 0),
                    "jumlahKemasan"     => (float) ($brg['jumlahKemasan'] ?? 0),
                    "jumlahSatuan"      => (float) ($brg['jumlahSatuan'] ?? 0),
                    "kodeAsalBahanBaku" => $brg['kodeAsalBahanBaku'] ?? "0",
                    "kodeBarang"        => strval($brg['kodeBarang'] ?? ''),
                    "kodeDokumen"       => "23",
                    "kodeJenisKemasan"  => $brg['kodeJenisKemasan'] ?? "",
                    "kodeKategoriBarang"=> $brg['kodeKategoriBarang'] ?? "",
                    "kodeNegaraAsal"    => !empty($brg['kodeNegaraAsal']) ? $brg['kodeNegaraAsal'] : "",
                    "kodePerhitungan"   => $brg['kodePerhitungan'] ?? "0",
                    "kodeSatuanBarang"  => $brg['kodeSatuanBarang'] ?? "",
                    "merk"              => $brg['merk'] ?? "-",
                    "ndpbm"             => (float) ($brg['ndpbm'] ?? $draft['ndpbm'] ?? 0),
                    "netto"             => (float) ($brg['netto'] ?? 0),
                    "nilaiBarang"       => (float) ($brg['nilaiBarang'] ?? $brg['cif'] ?? 0),
                    "nilaiTambah"       => (float) ($brg['nilaiTambah'] ?? 0),
                    "posTarif"          => $brg['posTarif'] ?? "",
                    "seriBarang"        => (int) ($brg['seriBarang'] ?? ($index + 1)),
                    "spesifikasiLain"   => $brg['spesifikasiLain'] ?? "-",
                    "tipe"              => $brg['tipe'] ?? "",
                    "ukuran"            => $brg['ukuran'] ?? "",
                    "uraian"            => $brg['uraian'] ?? "",
                    "idBarang"          => $brg['idBarang'] ?? "",
                    "barangTarif"       => $barangTarif,
                    "barangDokumen"     => $barangDokumen,
                ];
            }

            $entitasDraft = $draft['entitas'] ?? [];
            $payloadEntitas = [
                [
                    "alamatEntitas"      => $entitasDraft[3]['alamatEntitas'] ?? "",
                    "kodeEntitas"        => "3",
                    "kodeJenisIdentitas" => $entitasDraft[3]['kodeJenisIdentitas'] ?? "5",
                    "namaEntitas"        => $entitasDraft[3]['namaEntitas'] ?? "",
                    "nibEntitas"         => $entitasDraft[3]['nibEntitas'] ?? "",
                    "nomorIdentitas"     => $entitasDraft[3]['nomorIdentitas'] ?? "",
                    "nomorIjinEntitas"   => $entitasDraft[3]['nomorIjinEntitas'] ?? "",
                    "tanggalIjinEntitas" => $entitasDraft[3]['tanggalIjinEntitas'] ?? "",
                    "seriEntitas"        => 1,
                ],
                [
                    "alamatEntitas"      => $entitasDraft[5]['alamatEntitas'] ?? $entitasDraft[9]['alamatEntitas'] ?? $header->alamat_supplier ?? "",
                    "kodeEntitas"        => "5",
                    "kodeNegara"         => $entitasDraft[5]['kodeNegara'] ?? $entitasDraft[9]['kodeNegara'] ?? "",
                    "namaEntitas"        => $entitasDraft[5]['namaEntitas'] ?? $entitasDraft[9]['namaEntitas'] ?? $header->supplier ?? "",
                    "seriEntitas"        => 3,
                ],
                [
                    "alamatEntitas"      => $entitasDraft[7]['alamatEntitas'] ?? "",
                    "kodeEntitas"        => "7",
                    "kodeJenisApi"       => "",
                    "kodeJenisIdentitas" => $entitasDraft[7]['kodeJenisIdentitas'] ?? "5",
                    "kodeStatus"         => $entitasDraft[7]['kodeStatus'] ?? "5",
                    "namaEntitas"        => $entitasDraft[7]['namaEntitas'] ?? "",
                    "nomorIdentitas"     => $entitasDraft[7]['nomorIdentitas'] ?? "",
                    "nomorIjinEntitas"   => $entitasDraft[7]['nomorIjinEntitas'] ?? "",
                    "tanggalIjinEntitas" => $entitasDraft[7]['tanggalIjinEntitas'] ?? "",
                    "seriEntitas"        => 7,
                ],
            ];

            $payload = [
                "idPlatform"       => config('ceisa.id_platform_live', ''),
                "asalData"         => "S",
                "asuransi"         => $totalAsuransi > 0 ? $totalAsuransi : (float) ($draft['asuransi'] ?? 0),
                "biayaPengurang"   => (float) ($draft['biayaPengurang'] ?? 0),
                "biayaTambahan"    => (float) ($draft['biayaTambahan'] ?? 0),
                "bruto"            => (float) ($draft['bruto'] ?? 0),
                "cif"              => $totalCif > 0 ? $totalCif : (float) ($draft['cif'] ?? 0),
                "fob"              => $totalFob > 0 ? $totalFob : (float) ($draft['fob'] ?? 0),
                "freight"          => $totalFreight > 0 ? $totalFreight : (float) ($draft['freight'] ?? 0),
                "hargaPenyerahan"  => (float) ($draft['hargaPenyerahan'] ?? $totalHargaPenyerahan),
                "jabatanTtd"       => $draft['jabatanTtd'] ?? "",
                "jumlahKontainer"  => (int) ($draft['jumlahKontainer'] ?? 0),
                "kodeAsuransi"     => $draft['kodeAsuransi'] ?? "LN",
                "kodeDokumen"      => "23",
                "kodeIncoterm"     => $draft['kodeIncoterm'] ?? "",
                "kodeKantor"       => $draft['kodeKantor'] ?? "050500",
                "kodeKantorBongkar"=> $draft['kodeKantorBongkar'] ?? "",
                "kodeKenaPajak"    => $draft['kodeKenaPajak'] ?? "1",
                "kodePelBongkar"   => $draft['kodePelBongkar'] ?? "",
                "kodePelMuat"      => $draft['kodePelMuat'] ?? "",
                "kodePelTransit"   => $draft['kodePelTransit'] ?? "",
                "kodeTps"          => $draft['kodeTps'] ?? "",
                "kodeTujuanTpb"    => $draft['kodeTujuanTpb'] ?? "",
                "kodeTutupPu"      => $draft['kodeTutupPu'] ?? "",
                "kodeValuta"       => $draft['kodeValuta'] ?? "IDR",
                "kotaTtd"          => $draft['kotaTtd'] ?? "",
                "namaTtd"          => $draft['namaTtd'] ?? "",
                "ndpbm"            => (float) ($draft['ndpbm'] ?? 0),
                "netto"            => (float) ($draft['netto'] ?? 0),
                "nik"              => $draft['nik'] ?? "",
                "nilaiBarang"      => (float) ($draft['nilaiBarang'] ?? 0),
                "nomorAju"         => $nomorAju,
                "nomorBc11"        => $draft['nomorBc11'] ?? "",
                "posBc11"          => $draft['posBc11'] ?? "",
                "seri"             => (int) ($draft['seri'] ?? 0),
                "subposBc11"       => $draft['subposBc11'] ?? "",
                "tanggalBc11"      => $draft['tanggalBc11'] ?? "",
                "tanggalTiba"      => $draft['tanggalTiba'] ?? "",
                "tanggalTtd"       => $draft['tanggalTtd'] ?? date('Y-m-d'),
                "entitas"          => $payloadEntitas,
                "dokumen"          => $payloadDokumen,
                "pengangkut"       => [[
                    "namaPengangkut"  => $draft['pengangkut']['nama'] ?? "",
                    "nomorPengangkut" => $draft['pengangkut']['nomor'] ?? "",
                    "kodeBendera"     => !empty($draft['pengangkut']['kodeBendera']) ? $draft['pengangkut']['kodeBendera'] : "ID",
                    "kodeCaraAngkut"  => !empty($draft['pengangkut']['kodeCaraAngkut']) ? (string)$draft['pengangkut']['kodeCaraAngkut'] : "1",
                    "seriPengangkut"  => 1
                ]],
                "kontainer"        => $payloadKontainer,
                "kemasan"          => $payloadKemasan,
                "barang"           => $arrayBarang,
            ];

            $dateFields = ['tanggalBc11'];
            foreach ($dateFields as $f) {
                if (empty($payload[$f])) unset($payload[$f]);
            }
            if (empty($payload['kodeTutupPu'])) $payload['kodeTutupPu'] = "11";
            if (empty($payload['tanggalTiba'])) $payload['tanggalTiba'] = date('Y-m-d');

            foreach ($payload['entitas'] as &$ent) {
                if ($ent['kodeEntitas'] === '3' && empty($ent['tanggalIjinEntitas'])) {
                    $ent['tanggalIjinEntitas'] = date('Y-m-d');
                }
                if ($ent['kodeEntitas'] === '7' && (empty($ent['nomorIjinEntitas']) || $ent['nomorIjinEntitas'] === 'nomor_ijin_entitas')) {
                    unset($ent['nomorIjinEntitas'], $ent['tanggalIjinEntitas']);
                }
            }
            unset($ent);

            $responseCeisa = $this->ceisaService->kirimDokumenBc23($payload);

            if ($responseCeisa['successful']) {
                $db->table('bpb_ceisa')->where('bpbno', $id)->update([
                    'nomor_aju'   => $nomorAju,
                    'tanggal_aju' => $tanggalAju,
                    'jenis_bc'    => '2.3',
                    'status'      => 1,
                    'updated_at'  => Carbon::now()
                ]);

                return response()->json([
                    'status'         => 200,
                    'message'        => 'Dokumen BC 2.3 berhasil dikirim ke CEISA!',
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

    public function editBc27($id, Request $request)
    {
        return app(\App\Services\Bc27Service::class)->edit($id, $request);
    }

    public function updateDraftBc27($id, Request $request)
    {
        return app(\App\Services\Bc27Service::class)->updateDraft($id, $request);
    }

    public function sendCeisaBc27($id, Request $request)
    {
        return app(\App\Services\Bc27Service::class)->sendCeisa($id, $request);
    }

    public function editBc30($id, Request $request)
    {
        return app(\App\Services\Bc30Service::class)->edit($id, $request);
    }

    public function updateDraftBc30($id, Request $request)
    {
        return app(\App\Services\Bc30Service::class)->updateDraft($id, $request);
    }

    public function sendCeisaBc30($id, Request $request)
    {
        return app(\App\Services\Bc30Service::class)->sendCeisa($id, $request);
    }

    public function editBc33($id, Request $request)
    {
        return app(\App\Services\Bc33Service::class)->edit($id, $request);
    }

    public function updateDraftBc33($id, Request $request)
    {
        return app(\App\Services\Bc33Service::class)->updateDraft($id, $request);
    }

    public function sendCeisaBc33($id, Request $request)
    {
        return app(\App\Services\Bc33Service::class)->sendCeisa($id, $request);
    }

    public function editBc41($id, Request $request)
    {
        return app(\App\Services\Bc41Service::class)->edit($id, $request);
    }

    public function updateDraftBc41($id, Request $request)
    {
        return app(\App\Services\Bc41Service::class)->updateDraft($id, $request);
    }

    public function sendCeisaBc41($id, Request $request)
    {
        return app(\App\Services\Bc41Service::class)->sendCeisa($id, $request);
    }
}

