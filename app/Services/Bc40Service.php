<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Bc40Service
{
    protected $ceisaService;

    public function __construct(CeisaService $ceisaService)
    {
        $this->ceisaService = $ceisaService;
    }

    public function editBatch($ids, Request $request)
    {
        $db = DB::connection('mysql_sb');

        $bppbs = explode(',', $ids);
        $firstBpb = $bppbs[0];

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
                    'price'           => $b['hargaPenyerahan'] / ($b['jumlahSatuan'] > 0 ? $b['jumlahSatuan'] : 1),
                    'total_harga'     => $b['hargaPenyerahan'] ?? 0,
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
                ->where(function($query) use ($bppbs) {
                    $query->whereIn('a.bpbno', $bppbs)->orWhereIn('a.bpbno_int', $bppbs);
                })
                ->groupBy('a.id_item', 'mi.goods_code', 'mi.itemdesc')
                ->get();
        }

        $nomorAju = $ceisaInfo->nomor_aju ?? $this->generateNomorAju($db);

        return view('export-import.dokumen-pabean.edit-batch-bc40', [
            "page"           => "dashboard-export-import",
            "subPageGroup"   => "export-import",
            "subPage"        => "dokumen-pabean-list",
            "containerFluid" => true,
            "header"         => $header,
            "ceisaInfo"      => $ceisaInfo,
            "dataDetail"     => $dataDetail,
            "items"          => $items,
            "batch_id"       => $ids,
            "nomorAju"       => $nomorAju,
            "kantorList"     => $this->getKantorList()
        ]);
    }

    public function updateDraftBatchBc40($batch_id, Request $request)
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

            $pungutanInput = $request->input('pungutan', []);
            $pungutanList = is_array($pungutanInput) ? array_values($pungutanInput) : [];
            foreach ($pungutanList as &$p) {
                if(isset($p['nilaiPungutan'])) {
                    $p['nilaiPungutan'] = (float) $p['nilaiPungutan'];
                }
            }

            $payloadJson = [
                'kodeKantor'           => $request->input('kodeKantor', '050500'),
                'jenisTpb'             => $request->input('jenisTPB', '1'),
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
                'pungutan'             => $pungutanList,
                'dok'                  => $dokumenList,
                'kontainer'            => $kontainerList,
                'kemasan'              => $kemasanList,
                'barang'               => $request->input('barang', [])
            ];

            $bppbs = explode(',', $batch_id);
            $nomorAju = $request->input('nomorAju');
            $noDokumenMerge = $request->input('no_dokumen_merge', $batch_id);
            $db = DB::connection('mysql_sb');
            $now = date('Y-m-d H:i:s');

            foreach ($bppbs as $id) {
                $id = trim($id);
                if (empty($id)) continue;

                $ceisaRow = $db->table('bpb_ceisa')->where('bpbno', $id)->first();

                $header = $db->table('bpb')->select('bpbno_int')->where(function ($q) use ($id) {
                    $q->where('bpbno', $id)->orWhere('bpbno_int', $id);
                })->first();
                $bpbno_int = $header ? $header->bpbno_int : null;

                if ($ceisaRow) {
                    $db->table('bpb_ceisa')->where('bpbno', $id)->update([
                        'nomor_aju'        => $nomorAju ?: $ceisaRow->nomor_aju,
                        'bpbno_int'        => $bpbno_int,
                        'payload_json'     => json_encode($payloadJson),
                        'is_batch'         => 1,
                        'no_dokumen_merge' => $noDokumenMerge,
                        'updated_at'       => $now
                    ]);
                } else {
                    $db->table('bpb_ceisa')->insert([
                        'bpbno'            => $id,
                        'bpbno_int'        => $bpbno_int,
                        'nomor_aju'        => $nomorAju,
                        'jenis_bc'         => '4.0',
                        'payload_json'     => json_encode($payloadJson),
                        'is_batch'         => 1,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                        'no_dokumen_merge' => $noDokumenMerge,
                    ]);
                }
            }

            DB::connection('mysql_sb')->commit();

            // Mendukung respons dalam bentuk JSON (jika menggunakan AJAX/Fetch API dari frontend)
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data draft batch dokumen CEISA (BC 4.0) berhasil disimpan!'
                ]);
            }

            return redirect()->route('dokumen-pabean-index')
                             ->with('success', 'Data draft batch dokumen CEISA (BC 4.0) berhasil disimpan!');

        } catch (\Exception $e) {
            DB::connection('mysql_sb')->rollBack();
            \Illuminate\Support\Facades\Log::error('Error Update Draft Batch CEISA BC 4.0: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                 return response()->json([
                     'status' => 'error',
                     'message' => 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage()
                 ], 500);
            }

            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
        }
    }

    // bikin fungsi sendCeisaBatch40 untuk mengirim data ke CEISA
    public function sendCeisaBatch40(array $bpbs, Request $request)
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
                            $id_item = $brg['idItem'];

                            if (isset($mergedBarang[$id_item])) {
                                // $mergedBarang[$id_item]['jumlahSatuan'] += (float)($brg['jumlahSatuan'] ?? 0);
                                // $mergedBarang[$id_item]['hargaPenyerahan'] += (float)($brg['hargaPenyerahan'] ?? 0);
                                // $mergedBarang[$id_item]['netto'] += (float)($brg['netto'] ?? 0);
                                $mergedBarang[$id_item]['jumlahSatuan'] = (float)($brg['jumlahSatuan'] ?? 0);
                                $mergedBarang[$id_item]['hargaPenyerahan'] = (float)($brg['hargaPenyerahan'] ?? 0);
                                $mergedBarang[$id_item]['netto'] = (float)($brg['netto'] ?? 0);
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
                    ->where(function($query) use ($bpbs) {
                        $query->where('a.bpbno', $bpbs)->orWhere('a.bpbno_int', $bpbs);
                    })
                    ->select(
                        'a.id_item',
                        'mi.goods_code',
                        'mi.itemdesc',
                        'a.unit',
                        DB::raw('SUM(a.qty) as qty'),
                        DB::raw('AVG(a.price) as price'),
                        DB::raw('SUM(a.qty * a.price) as total_harga')
                    )
                    ->groupBy('a.id_item', 'mi.goods_code', 'mi.itemdesc', 'a.unit')
                    ->get();

                foreach ($items as $index => $item) {
                    $hargaPenyerahanItem = (float) $item->total_harga;
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
                        "kodeBarang"       => strval($item->goods_code ?? ''),
                        "kodeDokumen"      => "40",
                        "kodeJenisKemasan" => "NE",
                        "kodeSatuanBarang" => $item->unit,
                        "merk"             => "-",
                        "netto"            => 0.00,
                        "nilaiBarang"      => 0.00,
                        "posTarif"         => "48191000",
                        "seriBarang"       => ($index + 1),
                        "spesifikasiLain"  => "-",
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
                "tanggalAju"           => date('Y-m-d'),
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


            $responseCeisa = $this->ceisaService->kirimDokumenBatch40($payload, 'false');

            if ($responseCeisa['successful']) {

                foreach ($bpbs as $no_bpb) {
                    $db->table('bpb_ceisa')->where('bpbno', $no_bpb)->update([
                        'nomor_aju'   => $nomorAju,
                        'tanggal_aju' => date('Y-m-d'),
                        'status'      => 1,
                        'jenis_bc'    => '4.0',
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

}
