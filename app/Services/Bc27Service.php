<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Bc27Service
{
    protected $ceisaService;

    public function __construct(CeisaService $ceisaService)
    {
        $this->ceisaService = $ceisaService;
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

        return view('export-import.dokumen-pabean.edit-bc27', [
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
        $prefix      = '000027NIW345';

        $lastCeisa = $db->table('bpb_ceisa')
                        ->where('nomor_aju', 'like', $prefix . $currentYear . '%')
                        ->where(function($q) {
                            $q->where('jenis_bc', '2.7')->orWhere('jenis_bc', '27');
                        })
                        ->orderBy('nomor_aju', 'desc')
                        ->first();

        if ($lastCeisa && $lastCeisa->nomor_aju && strlen($lastCeisa->nomor_aju) === 26) {
            $lastSeq = (int) substr($lastCeisa->nomor_aju, -6);
            $nextSeq = str_pad($lastSeq + 1, 6, '0', STR_PAD_LEFT);
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
            if (is_array($pungutan)) {
                foreach ($pungutan as &$p) {
                    if (isset($p['nilaiPungutan'])) {
                        $p['nilaiPungutan'] = (float) $p['nilaiPungutan'];
                    }
                }
                unset($p);
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
                    $brg['jumlahSatuan'] = (float) ($brg['jumlahSatuan'] ?? 0);
                    $brg['jumlahKemasan'] = (float) ($brg['jumlahKemasan'] ?? 0);
                    $brg['biayaTambahan'] = (float) ($brg['biayaTambahan'] ?? 0);
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
                    'jenis_bc'     => '27',
                    'updated_at'   => date('Y-m-d H:i:s'),
                    'bpbno_int'    => $request->input('bpbno_int') ?? null
                ]
            );

            DB::connection('mysql_sb')->commit();

            return redirect()->route('dokumen-pabean-index')
                             ->with('success', 'Data draft BC 2.7 berhasil disimpan!');

        } catch (\Exception $e) {
            DB::connection('mysql_sb')->rollBack();
            \Illuminate\Support\Facades\Log::error('Error Update Draft BC 2.7: ' . $e->getMessage());

            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
        }
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

            if (!$header) throw new \Exception("Data transaksi tidak ditemukan!");

            $ceisaInfo = $db->table('bpb_ceisa')->where('bpbno', $id)->first();
            if (!$ceisaInfo) throw new \Exception("Data CEISA belum disiapkan. Simpan draft terlebih dahulu.");

            $draft     = json_decode($ceisaInfo->payload_json ?? '{}', true);
            $nomorAju  = $ceisaInfo->nomor_aju ?? '';
            $tanggalAju = date('Y-m-d');

            $payloadDokumen = [];
            $seriDok = 1;
            foreach (($draft['dok'] ?? []) as $d) {
                if (!empty($d['kode']) && !empty($d['nomor'])) {
                    $payloadDokumen[] = [
                        "kodeDokumen"    => trim(explode(' - ', $d['kode'])[0]),
                        "nomorDokumen"   => $d['nomor'],
                        "seriDokumen"    => $seriDok++,
                        "tanggalDokumen" => !empty($d['tgl']) ? $d['tgl'] : date('Y-m-d')
                    ];
                }
            }

            $hasInvoice = false;
            $hasTransport = false;
            foreach ($payloadDokumen as $dok) {
                $kodeStr = explode(' - ', $dok['kodeDokumen'])[0];
                $kodeStr = trim($kodeStr);

                if ($kodeStr === '380') $hasInvoice = true;
                if (in_array($kodeStr, ['705', '740', '704', '741'])) $hasTransport = true;
            }

            if (!$hasInvoice || !$hasTransport) {
                throw new \Exception("Validasi Gagal: Dokumen BC 2.7 wajib melampirkan INVOICE (380) dan B/L atau AWB (705/740). Silakan tambahkan di tab Dokumen Pendukung.");
            }

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
                    "jumlahKemasan"    => (float) ($k['jumlahKemasan'] ?? 0),
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
                if (!empty($brg['barangTarif']) && is_array($brg['barangTarif'])) {
                    foreach ($brg['barangTarif'] as $tarif) {
                            $kodeJenisPungutan = !empty($tarif['kodeJenisPungutan']) ? $tarif['kodeJenisPungutan'] : "BM";
                            $kodeFasilitasTarif = !empty($tarif['kodeFasilitasTarif']) ? $tarif['kodeFasilitasTarif'] : "3";
                            $tarifPersen = (float) ($tarif['tarif'] ?? 0);
                            $tarifFasilitas = (float) ($tarif['tarifFasilitas'] ?? ($kodeFasilitasTarif == '1' ? 0 : 100));

                            $cifRupiah = (float)($brg['cif'] ?? 0) * (float)($brg['ndpbm'] ?? 0);
                            $bmAmount = $cifRupiah * ($kodeJenisPungutan == 'BM' ? $tarifPersen / 100 : 0);
                            $nilaiDasar = ($kodeJenisPungutan == 'BM') ? $cifRupiah : ($cifRupiah + ($cifRupiah * 0.1));
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

                            $barangTarif[] = [
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
                if (empty($barangTarif)) {
                    $barangTarif = [
                        [
                            "kodeJenisTarif" => "1",
                            "jumlahSatuan" => (float)($brg['jumlahSatuan'] ?? 0),
                            "kodeFasilitasTarif" => "3",
                            "kodeSatuanBarang" => $brg['kodeSatuanBarang'] ?? "",
                            "kodeJenisPungutan" => "BM",
                            "nilaiBayar" => 0,
                            "nilaiFasilitas" => 0,
                            "nilaiSudahDilunasi" => 0,
                            "seriBarang" => (int)($brg['seriBarang'] ?? ($index + 1)),
                            "tarif" => 0,
                            "tarifFasilitas" => 100
                        ],
                        [
                            "kodeJenisTarif" => "1",
                            "jumlahSatuan" => (float)($brg['jumlahSatuan'] ?? 0),
                            "kodeFasilitasTarif" => "3",
                            "kodeSatuanBarang" => $brg['kodeSatuanBarang'] ?? "",
                            "kodeJenisPungutan" => "PPH",
                            "nilaiBayar" => 0,
                            "nilaiFasilitas" => 0,
                            "nilaiSudahDilunasi" => 0,
                            "seriBarang" => (int)($brg['seriBarang'] ?? ($index + 1)),
                            "tarif" => 0,
                            "tarifFasilitas" => 100
                        ],
                        [
                            "kodeJenisTarif" => "1",
                            "jumlahSatuan" => (float)($brg['jumlahSatuan'] ?? 0),
                            "kodeFasilitasTarif" => "3",
                            "kodeSatuanBarang" => $brg['kodeSatuanBarang'] ?? "",
                            "kodeJenisPungutan" => "PPN",
                            "nilaiBayar" => 0,
                            "nilaiFasilitas" => 0,
                            "nilaiSudahDilunasi" => 0,
                            "seriBarang" => (int)($brg['seriBarang'] ?? ($index + 1)),
                            "tarif" => 0,
                            "tarifFasilitas" => 100
                        ]
                    ];
                }

                $barangDokumen = [];
                foreach (($brg['barangDokumen'] ?? []) as $bd) {
                    if (!empty($bd['seriDokumen'])) {
                        $barangDokumen[] = ["seriDokumen" => $bd['seriDokumen']];
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
                    "kodeDokumen"       => "27",
                    "kodeJenisKemasan"  => $brg['kodeJenisKemasan'] ?? "",
                    "kodeKategoriBarang"=> $brg['kodeKategoriBarang'] ?? "",
                    "kodeNegaraAsal"    => !empty($brg['kodeNegaraAsal']) ? $brg['kodeNegaraAsal'] : "ID",
                    "kodePerhitungan"   => $brg['kodePerhitungan'] ?? "0",
                    "kodeSatuanBarang"  => $brg['kodeSatuanBarang'] ?? "",
                    "merk"              => $brg['merk'] ?? "-",
                    "ndpbm"             => (float) ($brg['ndpbm'] ?? 0),
                    "netto"             => (float) ($brg['netto'] ?? 0),
                    "bruto"             => (float) ($brg['bruto'] ?? 0),
                    "volume"            => (float) ($brg['volume'] ?? 0),
                    "nilaiBarang"       => (float) ($brg['nilaiBarang'] ?? 0),
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
                    "alamatEntitas" => $entitasDraft[8]['alamatEntitas'] ?? $header->alamat_supplier ?? "",
                    "kodeEntitas"   => "8",
                    "kodeNegara"    => $entitasDraft[8]['kodeNegara'] ?? "ID",
                    "namaEntitas"   => $entitasDraft[8]['namaEntitas'] ?? $header->supplier ?? "",
                    "nibEntitas"    => $entitasDraft[8]['nibEntitas'] ?? "",
                    "nomorIdentitas"=> $entitasDraft[8]['nomorIdentitas'] ?? "",
                    "seriEntitas"   => 2,
                ],
                [
                    "alamatEntitas"      => $entitasDraft[7]['alamatEntitas'] ?? "",
                    "kodeEntitas"        => "7",
                    "kodeJenisApi"       => $entitasDraft[7]['kodeJenisApi'] ?? "",
                    "kodeJenisIdentitas" => $entitasDraft[7]['kodeJenisIdentitas'] ?? "5",
                    "kodeStatus"         => $entitasDraft[7]['kodeStatus'] ?? "5",
                    "namaEntitas"        => $entitasDraft[7]['namaEntitas'] ?? "",
                    "nomorIdentitas"     => $entitasDraft[7]['nomorIdentitas'] ?? "",
                    "nomorIjinEntitas"   => $entitasDraft[7]['nomorIjinEntitas'] ?? "",
                    "tanggalIjinEntitas" => $entitasDraft[7]['tanggalIjinEntitas'] ?? "",
                    "seriEntitas"        => 3,
                ],
            ];

            $payloadPungutan = [];
            if (!empty($draft['pungutan']) && is_array($draft['pungutan'])) {
                foreach ($draft['pungutan'] as $p) {
                    if (isset($p['kodeJenisPungutan'])) {
                        $payloadPungutan[] = [
                            "kodeFasilitasTarif" => $p['kodeFasilitasTarif'] ?? "3",
                            "kodeJenisPungutan"  => $p['kodeJenisPungutan'],
                            "nilaiPungutan"      => (float) ($p['nilaiPungutan'] ?? 0)
                        ];
                    }
                }
            }

            $payload = [
                "idPlatform"       => config('ceisa.id_platform_dev', ''),
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
                "kodeDokumen"      => "27",
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
                "subsubposBc11"    => $draft['subsubposBc11'] ?? "",
                "tanggalBc11"      => $draft['tanggalBc11'] ?? "",
                "tanggalTiba"      => $draft['tanggalTiba'] ?? "",
                "volume"           => (float) ($draft['volume'] ?? 0),
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
                "pungutan"         => $payloadPungutan,
                "barang"           => $arrayBarang,
            ];

            $dateFields = ['tanggalBc11'];
            foreach ($dateFields as $f) {
                if (empty($payload[$f])) unset($payload[$f]);
            }
            if (empty($payload['kodeTutupPu'])) $payload['kodeTutupPu'] = "11";
            if (empty($payload['tanggalTiba'])) $payload['tanggalTiba'] = date('Y-m-d');

            foreach ($payload['entitas'] as &$ent) {
                if (empty($ent['tanggalIjinEntitas'])) {
                    $ent['tanggalIjinEntitas'] = date('Y-m-d');
                }
            }
            unset($ent);

            $responseCeisa = $this->ceisaService->kirimDokumenBc27($payload);

            if ($responseCeisa['successful']) {
                $db->table('bpb_ceisa')->where('bpbno', $id)->update([
                    'nomor_aju'   => $nomorAju,
                    'tanggal_aju' => $tanggalAju,
                    'jenis_bc'    => '2.7',
                    'status'      => 1,
                    'updated_at'  => Carbon::now()
                ]);

                return response()->json([
                    'status'         => 200,
                    'message'        => 'Dokumen BC 2.7 berhasil dikirim ke CEISA!',
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
}
