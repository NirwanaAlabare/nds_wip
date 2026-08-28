<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Bc23Service
{
    protected $ceisaService;

    public function __construct(CeisaService $ceisaService)
    {
        $this->ceisaService = $ceisaService;
    }

    public function updateDraftBatchBc23($ids, Request $request)
    {
        DB::connection('mysql_sb')->beginTransaction();
        try {
            $bpbs   = explode(',', $ids);
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

            // dd($request->input('entitas', []));

            $payloadJson = [
                'kodeKantor'         => $request->input('kodeKantor', '050500'),
                'jenisTpb'          => $request->input('jenisTPB', '1'),
                'kodeKantorBongkar'  => $request->input('kodeKantorBongkar', ''),
                'kodeTujuanTpb'      => $request->input('kodeTujuanTpb', ''),
                'kodeTutupPu'        => $request->input('kodeTutupPu', ''),
                'bruto'              => (float) $request->input('bruto', 0),
                'netto'              => (float) $request->input('netto', 0),
                // 'hargaPenyerahan'    => (float) $request->input('hargaPenyerahan', 0),
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

            // DB::connection('mysql_sb')->table('bpb_ceisa')->updateOrInsert(
            //     ['bpbno' => $id],
            //     [
            //         'tanggal_aju'  => $request->input('tanggalAju', date('Y-m-d')),
            //         'nomor_aju'    => $request->input('nomorAju'),
            //         'payload_json' => json_encode($payloadJson),
            //         'jenis_bc'     => '2.3',
            //         'updated_at'   => date('Y-m-d H:i:s'),
            //         'bpbno_int'    => $request->input('bpbno_int') ?? null
            //     ]
            // );

            foreach ($bpbs as $id) {
                $headerBpb = DB::connection('mysql_sb')->table('bpb')->where(function($query) use ($id) {
                    $query->where('bpbno', $id)->orWhere('bpbno_int', $id);
                })->first();

                $realBpbno    = $headerBpb ? $headerBpb->bpbno : $id;
                $realBpbnoInt = $headerBpb ? $headerBpb->bpbno_int : '';

                $ceisaRec = DB::connection('mysql_sb')->table('bpb_ceisa')
                    ->where('bpbno', $id)->orWhere('bpbno_int', $id)->first();

                $inputNomorAju = $request->input('nomorAju', '');

                if ($ceisaRec) {
                    DB::connection('mysql_sb')->table('bpb_ceisa')
                        ->where('id', $ceisaRec->id)
                        ->update([
                            'bpbno'            => $realBpbno,
                            'bpbno_int'        => $realBpbnoInt,
                            'nomor_aju'        => $inputNomorAju ?: $ceisaRec->nomor_aju,
                            'payload_json'     => json_encode($payloadJson),
                            'jenis_bc'         => '2.3',
                            'is_batch'         => 1,
                            'no_dokumen_merge' => $request->input('no_dokumen_merge', ''),
                            'updated_at'       => \Carbon\Carbon::now()
                        ]);
                } else {
                    DB::connection('mysql_sb')->table('bpb_ceisa')->insert([
                        'bpbno'            => $realBpbno,
                        'bpbno_int'        => $realBpbnoInt,
                        'nomor_aju'        => $inputNomorAju,
                        'jenis_bc'         => '2.3',
                        'payload_json'     => json_encode($payloadJson),
                        'status'           => 0,
                        'is_batch'         => 1,
                        'no_dokumen_merge' => $request->input('no_dokumen_merge', ''),
                        'created_at'       => \Carbon\Carbon::now(),
                        'updated_at'       => \Carbon\Carbon::now()
                    ]);
                }
            }

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


    // bikin fungsi sendCeisaBatch23 untuk mengirim data ke CEISA
    public function sendCeisaBatch23(array $bpbs, Request $request)
    {
        $db = DB::connection('mysql_sb');

        $firstBpb = $bpbs[0];

        try {
            $header = $db->table('bpb as a')
                ->join('mastersupplier as ms', 'a.id_supplier', '=', 'ms.id_supplier')
                ->where(function($query) use ($firstBpb) {
                    $query->where('a.bpbno', $firstBpb)->orWhere('a.bpbno_int', $firstBpb);
                })
                ->first();

            if (!$header) {
                throw new \Exception("Data transaksi tidak ditemukan!");
            }

            $ceisaInfo = $db->table('bpb_ceisa')->where('bpbno', $firstBpb)->first();


            if(!$ceisaInfo) {
                throw new \Exception("Data CEISA untuk transaksi ini tidak ditemukan. Pastikan data sudah disiapkan sebelum mengirim ke CEISA.");
            }

            $draft = json_decode($ceisaInfo->payload_json ?? '{}', true);


            unset($draft['barang']);


            $mergedBarang = [];

            foreach ($bpbs as $no_bpb) {
                $bpbData = $db->table('bpb_ceisa')->where('bpbno', $no_bpb)->first();
                if ($bpbData) {
                    $bpbPayload = json_decode($bpbData->payload_json ?? '{}', true);

                    if (isset($bpbPayload['barang']) && is_array($bpbPayload['barang'])) {
                        foreach ($bpbPayload['barang'] as $brg) {
                            $id_item = $brg['kodeBarang'];

                            if (isset($mergedBarang[$id_item])) {
                                $mergedBarang[$id_item]['jumlahSatuan'] += (float)($brg['jumlahSatuan'] ?? 0);
                                // $mergedBarang[$id_item]['hargaPenyerahan'] += (float)($brg['hargaPenyerahan'] ?? 0);
                                $mergedBarang[$id_item]['netto'] += (float)($brg['netto'] ?? 0);
                            } else {
                                $mergedBarang[$id_item] = $brg;
                            }
                        }
                    }
                }
            }

            $draft['barang'] = [];
            $key = 1;
            foreach ($mergedBarang as $brg) {
                $brg['seriBarang'] = $key++;
                $draft['barang'][] = $brg;
            }


            if (empty($draft['barang'])) {
                return response()->json(['message' => 'Tidak ada barang untuk dikirim.'], 400);
            }

            $nomorAju = $ceisaInfo->nomor_aju ?? '';

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
            }

            if ($invoiceDok) {
                array_unshift($otherDoks, $invoiceDok);
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

                // if ($cifItem <= 0 || $nettoItem <= 0) {
                //     $itemNum = $index + 1;
                //     throw new \Exception("Validasi Gagal: Harga CIF dan Berat Bersih (Netto) pada Barang ke-{$itemNum} harus lebih besar dari 0.");
                // }

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

            $responseCeisa = $this->ceisaService->kirimDokumenBatch23($payload);

            if ($responseCeisa['successful']) {

                foreach ($bpbs as $no_bpb) {
                    $updated = $db->table('bpb')
                    ->where(function($q) use ($no_bpb) {
                        $q->where('bpbno', $no_bpb)->orWhere('bpbno_int', $no_bpb);
                    })
                    ->update([
                        'nomor_aju'   => $nomorAju,
                        'tanggal_aju' => date('Y-m-d'),
                        'bcdate'      => date('Y-m-d'),
                    ]);

                    $db->table('bpb_ceisa')->where('bpbno', $no_bpb)->update([
                        'nomor_aju'   => $nomorAju,
                        'tanggal_aju' => $ceisaInfo->tanggal_aju ?? $header->tanggal_aju ?? date('Y-m-d'),
                        'status'      => 1,
                        'jenis_bc'    => '2.3',
                        'updated_at'  => \Carbon\Carbon::now()
                    ]);
                }

                return response()->json([
                    'status'         => 200,
                    'message'        => 'Dokumen berhasil dikirim ke CEISA sebagai Draft!',
                    'data_payload'   => $payload,
                    'ceisa_response' => $responseCeisa['body'],
                    'nomor_aju'      => $nomorAju
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

    public function editBatch($ids, Request $request)
    {
        $db = DB::connection('mysql_sb');

        $bpbs = explode(',', $ids);
        $firstBpb = $bpbs[0];

        $header = $db->table('bpb as a')
            ->select('a.*', 'ms.supplier', 'ms.alamat as alamat_supplier', 'ms.npwp as npwp_supplier',
                     DB::raw("IF(a.bpbno_int != '', a.bpbno_int, a.bpbno) as trx_no_par"))
            ->leftJoin('mastersupplier as ms', 'a.id_supplier', '=', 'ms.id_supplier')
            ->where(function($query) use ($firstBpb) {
                $query->where('a.bpbno', $firstBpb)->orWhere('a.bpbno_int', $firstBpb);
            })
            ->first();

        if (!$header) abort(404, 'Data Transaksi Tidak Ditemukan');

        $ceisaInfo = $db->table('bpb_ceisa')->where('bpbno', $firstBpb)->first();

        $dataDetail = json_decode($ceisaInfo->payload_json ?? '{}', true);

        if(isset($dataDetail['barang']) && count($dataDetail['barang']) > 0){
            $items = collect($dataDetail['barang'])->map(function($b) {
                return (object)[
                    'id_item'         => $b['idItem'] ?? '',
                    'goods_code'      => $b['kodeBarang'] ?? '',
                    'itemdesc'        => $b['uraian'] ?? '',
                    'unit'            => $b['kodeSatuanBarang'] ?? '',
                    'qty'             => $b['jumlahSatuan'] ?? 0,
                ];
            });
        } else {
            $items = $db->table('bpb as a')
                ->join('masteritem as mi', 'a.id_item', '=', 'mi.id_item')
                ->select(
                    'a.id_item', 'mi.goods_code', 'mi.itemdesc',
                    DB::raw("MAX(a.unit) as unit"),
                    DB::raw('SUM(a.qty) as qty'),
                    DB::raw('AVG(a.price) as price'),
                    DB::raw('SUM(a.qty * a.price) as total_harga')
                )
                ->where(function($query) use ($bpbs) {
                    $query->whereIn('a.bpbno', $bpbs)->orWhereIn('a.bpbno_int', $bpbs);
                })
                ->groupBy('a.id_item', 'mi.goods_code', 'mi.itemdesc')
                ->get();
        }

        $nomorAju = $ceisaInfo->nomor_aju ?? $this->generateNomorAjuBc23($db);

        $listJenisKemasan = \App\Services\BcReferenceService::getJenisKemasan();
        $listSatuanBarang = \App\Services\BcReferenceService::getSatuanBarang();

        return view('export-import.dokumen-pabean.edit-batch-bc23', [
            "page"           => "dashboard-export-import",
            "subPageGroup"   => "export-import",
            "subPage"        => "dokumen-pabean-list",
            "containerFluid" => true,
            "header"         => $header,
            "ceisaInfo"      => $ceisaInfo,
            "dataDetail"     => $dataDetail,
            "items"          => $items,
            "nomorAju"       => $nomorAju,
            "batch_id"       => $ids,
            'listSatuanBarang'    => $listSatuanBarang,
            'listJenisKemasan'    => $listJenisKemasan,
            "kantorList"     => $this->getKantorList()
        ]);
    }

    private function getKantorList()
    {
        return [
            '000000' => 'DJBC',
            '040300' => 'KPU TANJUNG PRIOK',
            '040400' => 'KPPBC JAKARTA',
            '050100' => 'KPU SOEKARNO-HATTA',
            '050500' => 'KPPBC BANDUNG',
            '050600' => 'KPPBC TASIKMALAYA',
            '050700' => 'KPPBC CIREBON',
            '050800' => 'KPPBC PURWAKARTA',
            '050900' => 'KPPBC BEKASI',
            '051000' => 'KPPBC CIKARANG',
            '060100' => 'KPPBC TMP TANJUNG EMAS',
            '060200' => 'KPPBC PEKALONGAN',
            '060300' => 'KPPBC TMC KUDUS',
            '060400' => 'KPPBC CILACAP',
            '060600' => 'KPPBC SURAKARTA',
            '060700' => 'KPPBC YOGYAKARTA',
            '060800' => 'KPPBC SEMARANG',
            '070100' => 'KPPBC TMP TANJUNG PERAK',
            '070300' => 'KPPBC GRESIK',
            '070500' => 'KPPBC TMP JUANDA',
            '070600' => 'KPPBC TMC MALANG',
            '071500' => 'KPPBC SIDOARJO',
            '010100' => 'KPPBC KUALANAMU',
            '010800' => 'KPPBC MEDAN',
            '020400' => 'KPU BATAM',
            '080100' => 'KPPBC TMP NGURAH RAI',
            '100300' => 'KPPBC BALIKPAPAN',
            '110100' => 'KPPBC MAKASSAR',
            '150300' => 'KPPBC TANGERANG',
            '999999' => 'UNIT LAIN DI LUAR DJBC',
        ];
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
}
