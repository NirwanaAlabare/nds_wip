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

            // dd($ceisaInfo);

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
                                $mergedBarang[$id_item]['hargaPenyerahan'] += (float)($brg['hargaPenyerahan'] ?? 0);
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

            $responseCeisa = $this->ceisaService->kirimDokumenBatch23($payload);

            if ($responseCeisa['successful']) {

                foreach ($bpbs as $no_bpb) {
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
}
