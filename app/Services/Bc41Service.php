<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Bc41Service
{
    protected $ceisaService;

    public function __construct(CeisaService $ceisaService)
    {
        $this->ceisaService = $ceisaService;
    }

    public function edit($id, Request $request)
    {
        $db = DB::connection('mysql_sb');

        $header = $db->table('bppb as a')
            ->select(
                'a.*',
                'ms.supplier',
                'ms.alamat as alamat_supplier',
                'ms.npwp as npwp_supplier',
                DB::raw("IF(a.bppbno_int != '', a.bppbno_int, a.bppbno) as trx_no_par")
            )
            ->leftJoin('mastersupplier as ms', 'a.id_supplier', '=', 'ms.id_supplier')
            ->where(function ($query) use ($id) {
                $query->where('a.bppbno', $id)->orWhere('a.bppbno_int', $id);
            })
            ->first();

        if (!$header) abort(404, 'Data Transaksi Tidak Ditemukan');

        $ceisaInfo = $db->table('bpb_ceisa')->where('bpbno', $id)->first();
        $dataDetail = json_decode($ceisaInfo->payload_json ?? '{}', true);

        $items = $db->table('bppb as a')
            ->leftJoin('masteritem as mi', 'a.id_item', '=', 'mi.id_item')
            ->leftJoin('masterstyle as ms', 'a.id_item', '=', 'ms.id_item')
            ->select(
                'a.id_item',
                DB::raw("IF(a.id_so_det IS NOT NULL AND a.id_so_det != '' AND a.id_so_det != '0' AND a.bppbno_int NOT LIKE '%OFC%' AND a.bppbno_int NOT LIKE '%FG%', ms.goods_code, mi.goods_code) as goods_code"),
                DB::raw("IF(a.id_so_det IS NOT NULL AND a.id_so_det != '' AND a.id_so_det != '0' AND a.bppbno_int NOT LIKE '%OFC%' AND a.bppbno_int NOT LIKE '%FG%', CONCAT(ms.itemname, ' ', IFNULL(ms.color,''), ' ', IFNULL(ms.size,'')), mi.itemdesc) as itemdesc"),
                DB::raw("MAX(a.unit) as unit"),
                DB::raw('SUM(a.qty) as qty'),
                DB::raw('AVG(a.price) as price'),
                DB::raw('SUM(a.qty * a.price) as total_harga')
            )
            ->where(function ($query) use ($id) {
                $query->where('a.bppbno', $id)->orWhere('a.bppbno_int', $id);
            })
            ->groupBy('a.id_item')
            ->get();

        $nomorAju = $ceisaInfo->nomor_aju ?? $this->generateNomorAju($db);
        $dokumens = !empty($dataDetail['dok']) ? $dataDetail['dok'] : [
            ['kode' => '', 'nomor' => '', 'tgl' => '', 'fasilitas' => '', 'izin' => '', 'kantor' => '']
        ];



$listJenisKemasan = \App\Services\BcReferenceService::getJenisKemasan();
$listSatuanBarang = \App\Services\BcReferenceService::getSatuanBarang();
        // Referensi dokumen lampiran
$referensiDokumen = \App\Services\BcReferenceService::getReferensiDokumen();

        $pengangkuts = !empty($dataDetail['pengangkut']) ? $dataDetail['pengangkut'] : [
            ['namaPengangkut' => '', 'nomorPengangkut' => '', 'kodeCaraAngkut' => '3', 'kodeBendera' => 'ID']
        ];
        $kontainers  = $dataDetail['kontainer']  ?? [];
        $kemasans    = !empty($dataDetail['kemasan']) ? $dataDetail['kemasan'] : [
            ['jumlahKemasan' => '', 'kodeJenisKemasan' => 'CT', 'merkKemasan' => '-']
        ];

$listKategoriBarang = \App\Services\BcReferenceService::getKategoriBarang();

        return view('export-import.dokumen-pabean.edit-bc41', [
            'page'                  => 'dashboard-export-import',
            'subPageGroup'          => 'export-import',
            'subPage'               => 'dokumen-pabean-list',
            'containerFluid'        => true,
            'header'                => $header,
            'ceisaInfo'             => $ceisaInfo,
            'dataDetail'            => $dataDetail,
            'items'                 => $items,
            'nomorAju'              => $nomorAju,
            'dokumens'              => $dokumens,
            'kantorList'            => $this->getKantorList(),
            'listJenisTpb'          => \App\Services\BcReferenceService::getJenisTpb(),
            'listTujuanPengiriman'  => \App\Services\BcReferenceService::getTujuanPengiriman('41'),
            'listCaraAngkut'        => \App\Services\BcReferenceService::getCaraAngkut(),
            'listJenisKemasan'      => $listJenisKemasan,
            'listSatuanBarang'      => $listSatuanBarang,
            'listKategoriBarang'    => $listKategoriBarang,
            
            'listJenisKontainer'    => \App\Services\BcReferenceService::getJenisKontainer(),
            'listTipeKontainer'     => \App\Services\BcReferenceService::getTipeKontainer(),
            'listUkuranKontainer'   => \App\Services\BcReferenceService::getUkuranKontainer(),
            'referensiDokumen'      => $referensiDokumen,
            'pengangkuts'           => $pengangkuts,
            'kontainers'            => $kontainers,
            'kemasans'              => $kemasans,
        ]);
    }

    // GENERATE NOMOR AJU BC 4.1
    private function generateNomorAju($db)
    {
        $currentYear = date('Y');
        $today       = date('Ymd');
        $prefix      = '000041NIW779';

        $lastCeisa = $db->table('bpb_ceisa')
            ->where('nomor_aju', 'like', $prefix . $currentYear . '%')
            ->where('jenis_bc', '4.1')
            ->orderBy('nomor_aju', 'desc')
            ->first();

        $localSeq = 0;
        if ($lastCeisa && $lastCeisa->nomor_aju && strlen($lastCeisa->nomor_aju) === 26) {
            $localSeq = (int) substr($lastCeisa->nomor_aju, -6);
        }

        $ceisaSeq = $this->ceisaService->getLastSequenceFromCeisa($prefix . $currentYear, '41');

        $maxSeq  = max($localSeq, $ceisaSeq);
        $nextSeq = str_pad($maxSeq + 1, 6, '0', STR_PAD_LEFT);

        return $prefix . $today . $nextSeq;
    }

    // UPDATE DRAFT BC 4.1
    public function updateDraft($id, Request $request)
    {
        DB::connection('mysql_sb')->beginTransaction();

        try {
            // --- 1. Dokumen Pendukung & Upload File ---
            $dokumenInput = $request->input('dok', []);
            $dokumenFiles = $request->file('dok', []);
            $dokumenList  = [];
            $seriDok = 1;

            foreach ($dokumenInput as $index => $d) {
                if (!empty($d['kode']) || !empty($d['nomor'])) {
                    $dokData = [
                        'seriDokumen'    => $seriDok++,
                        'kodeDokumen'    => $d['kode'] ?? '',
                        'nomorDokumen'   => $d['nomor'] ?? '',
                        'tanggalDokumen' => $d['tgl'] ?? date('Y-m-d'),
                        'fasilitas'      => $d['fasilitas'] ?? '',
                        'izin'           => $d['izin'] ?? '',
                        'kantor'         => $d['kantor'] ?? '',
                        'fileName'       => $d['fileName'] ?? null,
                    ];

                    if (isset($dokumenFiles[$index]['file_lampiran'])) {
                        $file = $dokumenFiles[$index]['file_lampiran'];
                        $fileName = 'CEISA_' . str_replace('/', '-', $id) . '_' . ($d['kode'] ?? 'DOC') . '_' . time() . '.' . $file->getClientOriginalExtension();
                        $destinationPath = public_path('uploads/ceisa');
                        if (!file_exists($destinationPath)) {
                            mkdir($destinationPath, 0755, true);
                        }
                        $file->move($destinationPath, $fileName);
                        $dokData['fileName'] = $fileName;
                    }

                    $dokumenList[] = $dokData;
                }
            }

            // --- 2. Kemasan ---
            $kemasanInput = $request->input('kemasan', []);
            $kemasanList  = [];
            $seriKemasan = 1;
            foreach ($kemasanInput as $k) {
                if (isset($k['jumlahKemasan']) && $k['jumlahKemasan'] !== '') {
                    $kemasanList[] = [
                        'seriKemasan'      => $seriKemasan++,
                        'jumlahKemasan'    => (int) $k['jumlahKemasan'],
                        'kodeJenisKemasan' => $k['kodeJenisKemasan'] ?? 'CT',
                        'merkKemasan'      => $k['merkKemasan'] ?? '-',
                    ];
                }
            }

            // --- 3. Kontainer / Peti Kemas ---
            $kontainerInput = $request->input('kontainer', []);
            $kontainerList  = [];
            $seriKontainer = 1;
            foreach ($kontainerInput as $k) {
                if (!empty($k['nomorKontainer'])) {
                    $kontainerList[] = [
                        'seriKontainer'       => $seriKontainer++,
                        'nomorKontainer'      => $k['nomorKontainer'],
                        'kodeJenisKontainer'  => $k['kodeJenisKontainer'] ?? '',
                        'kodeTipeKontainer'   => $k['kodeTipeKontainer'] ?? '',
                        'kodeUkuranKontainer' => $k['kodeUkuranKontainer'] ?? '',
                        'jenisMuatan'         => $k['jenisMuatan'] ?? '',
                    ];
                }
            }

            // --- 4. Pengangkut ---
            $pengangkutInput = $request->input('pengangkut', []);
            $pengangkutList  = [];
            $seriPengangkut = 1;
            foreach ($pengangkutInput as $p) {
                if (!empty($p['namaPengangkut']) || !empty($p['nomorPengangkut'])) {
                    $pengangkutList[] = [
                        'seriPengangkut'  => $seriPengangkut++,
                        'kodeBendera'     => $p['kodeBendera'] ?? 'ID',
                        'namaPengangkut'  => $p['namaPengangkut'] ?? '',
                        'nomorPengangkut' => $p['nomorPengangkut'] ?? '',
                        'kodeCaraAngkut'  => $p['kodeCaraAngkut'] ?? '3',
                    ];
                }
            }

            // --- 5. Entitas ---
            $entitasInput = $request->input('entitas', []);

            // --- 6. Barang ---
            $barangInput = $request->input('barang', []);
            $barangList  = [];
            foreach ($barangInput as $index => $b) {
                $bahanBakuInput = $b['bahanBakuLokal'] ?? [];
                $bahanBakuList  = [];
                $seriBahan = 1;
                foreach ($bahanBakuInput as $bb) {
                    if (!empty($bb['uraian'])) {
                        $bahanBakuList[] = [
                            'seriBahanBaku' => $seriBahan++,
                            'hs'            => $bb['hs'] ?? '',
                            'uraian'        => $bb['uraian'] ?? '',
                            'nilaiBarang'   => (float) ($bb['nilaiBarang'] ?? 0),
                            'kodeSatuan'    => $bb['kodeSatuan'] ?? '',
                        ];
                    }
                }

                $barangList[] = [
                    'seriBarang'         => (int) ($b['seriBarang'] ?? ($index + 1)),
                    'posTarif'           => $b['posTarif'] ?? '',
                    'kodeBarang'         => $b['kodeBarang'] ?? '-',
                    'uraian'             => $b['uraian'] ?? '',
                    'merk'               => $b['merk'] ?? '-',
                    'tipe'               => $b['tipe'] ?? '-',
                    'ukuran'             => $b['ukuran'] ?? '-',
                    'spesifikasiLain'    => $b['spesifikasiLain'] ?? '-',
                    'jumlahSatuan'       => (float) ($b['jumlahSatuan'] ?? 0),
                    'kodeSatuanBarang'   => $b['kodeSatuanBarang'] ?? '',
                    'jumlahKemasan'      => (float) ($b['jumlahKemasan'] ?? 0),
                    'kodeJenisKemasan'   => $b['kodeJenisKemasan'] ?? 'CT',
                    'volume'             => (float) ($b['volume'] ?? 0),
                    'netto'              => (float) ($b['netto'] ?? 0),
                    'kodeKategoriBarang' => $b['kodeKategoriBarang'] ?? '',
                    'hargaPenyerahan'    => (float) ($b['hargaPenyerahan'] ?? 0),
                    'nilaiJasa'          => (float) ($b['nilaiJasa'] ?? 0),
                    'bahanBakuLokal'     => $bahanBakuList,
                ];
            }

            $draftPayload = [
                'nomorAju'            => $request->input('nomorAju', ''),
                'kodeKantor'          => $request->input('kodeKantor', ''),
                'kodeJenisTpb'        => $request->input('kodeJenisTpb', ''),
                'kodeTujuanPengiriman'=> $request->input('kodeTujuanPengiriman', ''),

                // Transaksi
                'hargaPenyerahan'     => (float) ($request->input('hargaPenyerahan', 0)),
                'nilaiJasa'           => (float) ($request->input('nilaiJasa', 0)),
                'uangMuka'            => (float) ($request->input('uangMuka', 0)),
                'diskon'              => (float) ($request->input('diskon', 0)),
                'hargaPerolehan'      => (float) ($request->input('hargaPerolehan', 0)),
                'dasarPengenaanPajak' => (float) ($request->input('dasarPengenaanPajak', 0)),
                'ppnTarif'            => (float) ($request->input('ppnTarif', 11)),
                'ppnNilai'            => (float) ($request->input('ppnNilai', 0)),
                'ppnbmTarif'          => (float) ($request->input('ppnbmTarif', 0)),
                'ppnbmNilai'          => (float) ($request->input('ppnbmNilai', 0)),
                'volume'              => (float) ($request->input('volume', 0)),
                'bruto'               => (float) ($request->input('bruto', 0)),
                'netto'               => (float) ($request->input('netto', 0)),

                // Pungutan
                'caraBayar'           => $request->input('caraBayar', 'BANK'),
                'wajibBayar'          => $request->input('wajibBayar', ''),
                'nomorBuktiBayar'     => $request->input('nomorBuktiBayar', ''),
                'tanggalBuktiBayar'   => $request->input('tanggalBuktiBayar', ''),

                // Pernyataan
                'tempatTtd'           => $request->input('tempatTtd', ''),
                'tanggalTtd'          => $request->input('tanggalTtd', date('Y-m-d')),
                'namaTtd'             => $request->input('namaTtd', ''),
                'jabatanTtd'          => $request->input('jabatanTtd', ''),

                'dok'                 => $dokumenList,
                'kemasan'             => $kemasanList,
                'kontainer'           => $kontainerList,
                'pengangkut'          => $pengangkutList,
                'entitas'             => $entitasInput,
                'barang'              => $barangList,
                'pungutan'            => $request->input('pungutan', []),
            ];

            // Update ke tabel bpb_ceisa
            $db = DB::connection('mysql_sb');
            $ceisaRow = $db->table('bpb_ceisa')->where('bpbno', $id)->first();

            $header = $db->table('bppb')->select('bppbno_int')->where(function ($query) use ($id) {
                $query->where('bppbno', $id)->orWhere('bppbno_int', $id);
            })->first();
            $bpbno_int = $header ? $header->bppbno_int : null;

            if ($ceisaRow) {
                $db->table('bpb_ceisa')->where('bpbno', $id)->update([
                    'nomor_aju'    => $request->input('nomorAju', $ceisaRow->nomor_aju),
                    'bpbno_int'    => $bpbno_int,
                    'payload_json' => json_encode($draftPayload),
                    'updated_at'   => Carbon::now()
                ]);
            } else {
                $db->table('bpb_ceisa')->insert([
                    'bpbno'        => $id,
                    'bpbno_int'    => $bpbno_int,
                    'nomor_aju'    => $request->input('nomorAju', ''),
                    'jenis_bc'     => '4.1',
                    'payload_json' => json_encode($draftPayload),
                    'created_at'   => Carbon::now(),
                    'updated_at'   => Carbon::now()
                ]);
            }

            DB::connection('mysql_sb')->commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Draft BC 4.1 berhasil disimpan!',
                'data'    => $draftPayload
            ]);

        } catch (\Exception $e) {
            DB::connection('mysql_sb')->rollBack();
            Log::error('Error Update Draft BC 4.1: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status'  => 500,
                'message' => 'Gagal menyimpan draft: ' . $e->getMessage()
            ], 500);
        }
    }

    // SEND CEISA BC 4.1
    public function sendCeisa($id, Request $request)
    {
        $db = DB::connection('mysql_sb');

        try {
            $ceisaInfo = $db->table('bpb_ceisa')->where('bpbno', $id)->first();
            if (!$ceisaInfo || empty($ceisaInfo->payload_json)) {
                throw new \Exception('Draft dokumen belum disimpan. Silakan lengkapi dan Simpan Draft terlebih dahulu.');
            }

            $draft = json_decode($ceisaInfo->payload_json, true);
            $nomorAju = $ceisaInfo->nomor_aju ?? $draft['nomorAju'] ?? '';
            if (empty($nomorAju)) {
                throw new \Exception('Nomor Aju tidak ditemukan. Silakan Simpan Draft ulang.');
            }

            // --- Pemetaan Entitas ---
            $entitasDraft = $draft['entitas'] ?? [];
            $payloadEntitas = [];

            // 1. Pengusaha TPB / Pengusaha Kena Pajak (Kode 3)
            if (!empty($entitasDraft['tpb'])) {
                $e = $entitasDraft['tpb'];
                $payloadEntitas[] = [
                    'seriEntitas'        => 1,
                    'kodeEntitas'        => '3',
                    'kodeJenisIdentitas' => !empty($e['nomorIdentitas']) && strlen(str_replace(['.', '-'], '', $e['nomorIdentitas'])) == 16 ? '6' : '5',
                    'nomorIdentitas'     => str_replace(['.', '-'], '', $e['nomorIdentitas'] ?? ''),
                    'nitku'              => $e['nitku'] ?? '',
                    'namaEntitas'        => $e['namaEntitas'] ?? '',
                    'alamatEntitas'      => $e['alamatEntitas'] ?? '',
                    'nomorIjinEntitas'   => $e['nomorIjinEntitas'] ?? '',
                    'tanggalIjinEntitas' => $e['tanggalIjinEntitas'] ?? '',
                    'nibEntitas'         => $e['nibEntitas'] ?? '',
                ];
            }

            // 2. Pemilik Barang (Kode 7)
            if (!empty($entitasDraft['pemilik'])) {
                $e = $entitasDraft['pemilik'];
                $payloadEntitas[] = [
                    'seriEntitas'        => 2,
                    'kodeEntitas'        => '7',
                    'kodeJenisApi'       => '02',
                    'kodeStatus'         => '5',
                    'kodeJenisIdentitas' => !empty($e['nomorIdentitas']) && strlen(str_replace(['.', '-'], '', $e['nomorIdentitas'])) == 16 ? '6' : '5',
                    'nomorIdentitas'     => str_replace(['.', '-'], '', $e['nomorIdentitas'] ?? ''),
                    'nitku'              => $e['nitku'] ?? '',
                    'namaEntitas'        => $e['namaEntitas'] ?? '',
                    'alamatEntitas'      => $e['alamatEntitas'] ?? '',
                    'nomorIjinEntitas'   => $e['nomorIjinEntitas'] ?? '',
                    'tanggalIjinEntitas' => $e['tanggalIjinEntitas'] ?? date('Y-m-d'),
                ];
            }

            // 3. Penerima Barang / Pembeli BKP (Kode 8)
            if (!empty($entitasDraft['penerima'])) {
                $e = $entitasDraft['penerima'];
                $payloadEntitas[] = [
                    'seriEntitas'        => 3,
                    'kodeEntitas'        => '8',
                    'kodeJenisApi'       => '02',
                    'kodeStatus'         => '5',
                    'kodeJenisIdentitas' => !empty($e['nomorIdentitas']) && strlen(str_replace(['.', '-'], '', $e['nomorIdentitas'])) == 16 ? '6' : '5',
                    'nomorIdentitas'     => str_replace(['.', '-'], '', $e['nomorIdentitas'] ?? ''),
                    'nitku'              => $e['nitku'] ?? '',
                    'namaEntitas'        => $e['namaEntitas'] ?? '',
                    'alamatEntitas'      => $e['alamatEntitas'] ?? '',
                ];
            }

            // --- Pemetaan Dokumen Lampiran ---
            $payloadDokumen = [];
            foreach (($draft['dok'] ?? []) as $d) {
                if (!empty($d['kodeDokumen']) && !empty($d['nomorDokumen'])) {
                    $payloadDokumen[] = [
                        'seriDokumen'    => (int) ($d['seriDokumen'] ?? 1),
                        'kodeDokumen'    => $d['kodeDokumen'],
                        'nomorDokumen'   => $d['nomorDokumen'],
                        'tanggalDokumen' => $d['tanggalDokumen'] ?? date('Y-m-d'),
                        'fasilitas'      => $d['fasilitas'] ?? '',
                        'izin'           => $d['izin'] ?? '',
                        'kantor'         => $d['kantor'] ?? '',
                    ];
                }
            }

            // --- Pemetaan Pengangkut ---
            $payloadPengangkut = [];
            foreach (($draft['pengangkut'] ?? []) as $p) {
                $payloadPengangkut[] = [
                    'seriPengangkut'  => (string) ($p['seriPengangkut'] ?? '1'),
                    'kodeBendera'     => $p['kodeBendera'] ?? 'ID',
                    'namaPengangkut'  => $p['namaPengangkut'] ?? '',
                    'nomorPengangkut' => $p['nomorPengangkut'] ?? '',
                    'kodeCaraAngkut'  => $p['kodeCaraAngkut'] ?? '3',
                ];
            }

            // --- Pemetaan Kemasan ---
            $payloadKemasan = [];
            foreach (($draft['kemasan'] ?? []) as $k) {
                $payloadKemasan[] = [
                    'seriKemasan'      => (int) ($k['seriKemasan'] ?? 1),
                    'jumlahKemasan'    => (int) ($k['jumlahKemasan'] ?? 0),
                    'kodeJenisKemasan' => $k['kodeJenisKemasan'] ?? 'CT',
                    'merkKemasan'      => $k['merkKemasan'] ?? '-',
                ];
            }

            // --- Pemetaan Kontainer ---
            $payloadKontainer = [];
            foreach (($draft['kontainer'] ?? []) as $k) {
                $payloadKontainer[] = [
                    'seriKontainer'       => (int) ($k['seriKontainer'] ?? 1),
                    'nomorKontainer'      => strtoupper($k['nomorKontainer'] ?? ''),
                    'kodeJenisKontainer'  => $k['kodeJenisKontainer'] ?? '',
                    'kodeTipeKontainer'   => $k['kodeTipeKontainer'] ?? '',
                    'kodeUkuranKontainer' => $k['kodeUkuranKontainer'] ?? '',
                    'jenisMuatan'         => $k['jenisMuatan'] ?? '',
                ];
            }

            // --- Pemetaan Barang ---
            $payloadBarang = [];
            foreach (($draft['barang'] ?? []) as $index => $b) {
                $bahanBakuLokal = [];
                foreach (($b['bahanBakuLokal'] ?? []) as $bb) {
                    $bahanBakuLokal[] = [
                        'seriBahanBaku' => (int) ($bb['seriBahanBaku'] ?? 1),
                        'hs'            => $bb['hs'] ?? '',
                        'uraian'        => $bb['uraian'] ?? '',
                        'nilaiBarang'   => (float) ($bb['nilaiBarang'] ?? 0),
                        'kodeSatuan'    => $bb['kodeSatuan'] ?? '',
                    ];
                }

                $payloadBarang[] = [
                    'seriBarang'         => (int) ($b['seriBarang'] ?? ($index + 1)),
                    'kodeDokumen'        => '41',
                    'posTarif'           => $b['posTarif'] ?? '',
                    'kodeBarang'         => $b['kodeBarang'] ?? '-',
                    'uraian'             => $b['uraian'] ?? '',
                    'merk'               => $b['merk'] ?? '-',
                    'tipe'               => $b['tipe'] ?? '-',
                    'ukuran'             => $b['ukuran'] ?? '-',
                    'spesifikasiLain'    => $b['spesifikasiLain'] ?? '-',
                    'jumlahSatuan'       => (float) ($b['jumlahSatuan'] ?? 0),
                    'kodeSatuanBarang'   => $b['kodeSatuanBarang'] ?? '',
                    'jumlahKemasan'      => (float) ($b['jumlahKemasan'] ?? 0),
                    'kodeJenisKemasan'   => $b['kodeJenisKemasan'] ?? 'CT',
                    'volume'             => (float) ($b['volume'] ?? 0),
                    'netto'              => (float) ($b['netto'] ?? 0),
                    'kodeKategoriBarang' => $b['kodeKategoriBarang'] ?? '',
                    'hargaPenyerahan'    => (float) ($b['hargaPenyerahan'] ?? 0),
                    'nilaiJasa'          => (float) ($b['nilaiJasa'] ?? 0),
                    'bahanBaku'          => $bahanBakuLokal,
                    'cif'                => 0,
                    'hargaEkspor'        => 0,
                    'isiPerKemasan'      => 0,
                    'nilaiBarang'        => 0,
                    'cifRupiah'          => 0,
                    'hargaPerolehan'     => 0,
                    'kodeAsalBahanBaku'  => '0',
                    'ndpbm'              => 0,
                ];
            }

            $finalPayload = [
                'nomorAju'            => $nomorAju,
                'kodeDokumen'         => '41',
                'asalData'            => 'S',
                'idPlatform'          => config('ceisa.id_platform_live', ''),
                'kodeKantor'          => $draft['kodeKantor'] ?? '',
                'kodeJenisTpb'        => $draft['kodeJenisTpb'] ?? '',
                'kodeTujuanPengiriman'=> $draft['kodeTujuanPengiriman'] ?? '',

                // Transaksi
                'hargaPenyerahan'     => (float) ($draft['hargaPenyerahan'] ?? 0),
                'nilaiJasa'           => (float) ($draft['nilaiJasa'] ?? 0),
                'uangMuka'            => (float) ($draft['uangMuka'] ?? 0),
                'diskon'              => (float) ($draft['diskon'] ?? 0),
                'hargaPerolehan'      => (float) ($draft['hargaPerolehan'] ?? 0),
                'dasarPengenaanPajak' => (float) ($draft['dasarPengenaanPajak'] ?? 0),
                'ppnTarif'            => (float) ($draft['ppnTarif'] ?? 11),
                'ppnNilai'            => (float) ($draft['ppnNilai'] ?? 0),
                'ppnbmTarif'          => (float) ($draft['ppnbmTarif'] ?? 0),
                'ppnbmNilai'          => (float) ($draft['ppnbmNilai'] ?? 0),
                'volume'              => (float) ($draft['volume'] ?? 0),
                'bruto'               => (float) ($draft['bruto'] ?? 0),
                'netto'               => (float) ($draft['netto'] ?? 0),

                'kodeCaraAngkut'      => !empty($payloadPengangkut[0]['kodeCaraAngkut']) ? $payloadPengangkut[0]['kodeCaraAngkut'] : '3',
                'namaPengangkut'      => !empty($payloadPengangkut[0]['namaPengangkut']) ? $payloadPengangkut[0]['namaPengangkut'] : '',
                'nomorPengangkut'     => !empty($payloadPengangkut[0]['nomorPengangkut']) ? $payloadPengangkut[0]['nomorPengangkut'] : '',
                'kodeBendera'         => !empty($payloadPengangkut[0]['kodeBendera']) ? $payloadPengangkut[0]['kodeBendera'] : 'ID',

                // Pungutan
                'caraBayar'           => $draft['caraBayar'] ?? 'BANK',
                'wajibBayar'          => $draft['wajibBayar'] ?? '',
                'nomorBuktiBayar'     => $draft['nomorBuktiBayar'] ?? '',
                'tanggalBuktiBayar'   => $draft['tanggalBuktiBayar'] ?? '',

                // Pernyataan & Tanda Tangan
                'tempatTtd'           => $draft['tempatTtd'] ?? '',
                'tanggalTtd'          => $draft['tanggalTtd'] ?? date('Y-m-d'),
                'namaTtd'             => $draft['namaTtd'] ?? '',
                'jabatanTtd'          => $draft['jabatanTtd'] ?? '',
                'kotaTtd'             => $draft['tempatTtd'] ?? 'kota_ttd',

                'asuransi'            => 0,
                'cif'                 => 0,
                'freight'             => 0,
                'jumlahKontainer'     => count($payloadKontainer),
                'kodeLokasiBayar'     => '',
                'kodePembayar'        => explode(' - ', $draft['wajibBayar'] ?? '')[0] ?? '',
                'nilaiBarang'         => 0,
                'seri'                => 0,
                'tanggalAju'          => date('Y-m-d'),
                'userPortal'          => 'admin',
                'biayaTambahan'       => 0,
                'biayaPengurang'      => 0,

                'entitas'             => $payloadEntitas,
                'dokumen'             => $payloadDokumen,
                'pengangkut'          => $payloadPengangkut,
                'kemasan'             => $payloadKemasan,
                'kontainer'           => $payloadKontainer,
                'barang'              => $payloadBarang,
                'pungutan'            => array_map(function($p, $idx) {
                    return [
                        'seriPungutan'  => $idx + 1,
                        'kodePungutan'  => $p['kodePungutan'] ?? '',
                        'dibayar'       => (float) ($p['dibayar'] ?? 0),
                    ];
                }, $draft['pungutan'] ?? [], array_keys($draft['pungutan'] ?? [])),
            ];

            foreach (['tanggalBuktiBayar', 'tanggalTtd'] as $dateField) {
                if (isset($finalPayload[$dateField]) && empty($finalPayload[$dateField])) {
                    unset($finalPayload[$dateField]);
                }
            }

            Log::info('Kirim BC 4.1 CEISA Payload: ', $finalPayload);

            $responseCeisa = $this->ceisaService->kirimDokumenBc41($finalPayload);

            if ($responseCeisa['successful']) {
                $db->table('bpb_ceisa')->where('bpbno', $id)->update([
                    'nomor_aju'   => $nomorAju,
                    'tanggal_aju' => Carbon::now()->format('Y-m-d'),
                    'jenis_bc'    => '4.1',
                    'status'      => 1,
                    'updated_at'  => Carbon::now()
                ]);

                return response()->json([
                    'status'         => 200,
                    'message'        => 'Dokumen BC 4.1 berhasil dikirim ke CEISA!',
                    'data_payload'   => $finalPayload,
                    'ceisa_response' => $responseCeisa['body']
                ]);
            } else {
                return response()->json([
                    'status'      => $responseCeisa['status_code'],
                    'message'     => 'Gagal mengirim BC 4.1 ke CEISA.',
                    'ceisa_error' => $responseCeisa['body']
                ], $responseCeisa['status_code']);
            }

        } catch (\Exception $e) {
            Log::error('Error Send CEISA BC 4.1: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status'  => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getKantorList()
    {
        return [
            ['kode' => '050500', 'nama' => '050500 - KPPBC TMP A BANDUNG'],
            ['kode' => '010700', 'nama' => '010700 - KPPBC TMP B MEDAN'],
            ['kode' => '040300', 'nama' => '040300 - KPPBC TMP A JAKARTA'],
            ['kode' => '050100', 'nama' => '050100 - KPPBC TMP A BOGOR'],
            ['kode' => '050200', 'nama' => '050200 - KPPBC TMP A PURWAKARTA'],
            ['kode' => '050300', 'nama' => '050300 - KPPBC TMP A BEKASI'],
            ['kode' => '060100', 'nama' => '060100 - KPPBC TMP TANJUNG EMAS'],
            ['kode' => '070100', 'nama' => '070100 - KPPBC TMP TANJUNG PERAK'],
            ['kode' => '070300', 'nama' => '070300 - KPPBC TMP PASURUAN'],
            ['kode' => '070600', 'nama' => '070600 - KPPBC TMP C GRESIK'],
        ];
    }
}
