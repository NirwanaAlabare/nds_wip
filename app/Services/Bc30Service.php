<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Bc30Service
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

        $ceisaInfo = $db->table('bpb_ceisa')
            ->where(function ($query) use ($id, $header) {
                $query->where('bpbno', $id)
                      ->orWhere('bpbno', $header->bppbno)
                      ->orWhere('bpbno_int', $id)
                      ->orWhere('bpbno_int', $header->bppbno_int);
            })
            ->first();
        $dataDetail = json_decode($ceisaInfo->payload_json ?? '{}', true);

        $items = $db->table('bppb as a')
            ->leftJoin('masteritem as mi', 'a.id_item', '=', 'mi.id_item')
            ->leftJoin('masterstyle as ms', 'a.id_item', '=', 'ms.id_item')
            ->select(
                'a.id_item',
                // DB::raw("IF(a.id_so_det IS NOT NULL AND a.id_so_det != '' AND a.id_so_det != '0' AND a.bppbno_int NOT LIKE '%OFC%' AND a.bppbno_int NOT LIKE '%FG%', ms.goods_code, mi.goods_code) as goods_code"),
                // DB::raw("IF(a.id_so_det IS NOT NULL AND a.id_so_det != '' AND a.id_so_det != '0' AND a.bppbno_int NOT LIKE '%OFC%' AND a.bppbno_int NOT LIKE '%FG%', CONCAT(ms.itemname, ' ', IFNULL(ms.color,''), ' ', IFNULL(ms.size,'')), mi.itemdesc) as itemdesc"),
                DB::raw("ms.goods_code as goods_code"),
                DB::raw("CONCAT(ms.itemname, ' ', IFNULL(ms.color,'')) as itemdesc"),
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
            ['kode' => '', 'nomor' => '', 'tgl' => '']
        ];


        return view('export-import.dokumen-pabean.edit-bc30', [
            'page'          => 'dashboard-export-import',
            'subPageGroup'  => 'export-import',
            'subPage'       => 'dokumen-pabean-list',
            'containerFluid'=> true,
            'header'        => $header,
            'ceisaInfo'     => $ceisaInfo,
            'dataDetail'    => $dataDetail,
            'items'         => $items,
            'nomorAju'      => $nomorAju,
            'dokumens'      => $dokumens,
            'kantorList'    => $this->getKantorList(),
            'listIncoterm'       => \App\Services\BcReferenceService::getIncoterm(),
            'listSatuanBarang'   => \App\Services\BcReferenceService::getSatuanBarang(),
            'listJenisKemasan'   => \App\Services\BcReferenceService::getJenisKemasan(),
            'referensiDokumen'   => \App\Services\BcReferenceService::getReferensiDokumen(),
            'listValuta'         => \App\Services\BcReferenceService::getValuta(),
            'listKategoriBarang' => \App\Services\BcReferenceService::getKategoriBarang(),
            'listJenisKontainer' => \App\Services\BcReferenceService::getJenisKontainer(),
            'listTipeKontainer'  => \App\Services\BcReferenceService::getTipeKontainer(),
            'listUkuranKontainer'=> \App\Services\BcReferenceService::getUkuranKontainer(),
            'listCaraAngkut' => \App\Services\BcReferenceService::getCaraAngkut(),
        ]);
    }

    // GENERATE NOMOR AJU BC 3.0
    private function generateNomorAju($db)
    {
        $currentYear = date('Y');
        $today       = date('Ymd');
        $prefix      = '000030NIW779';

        $lastCeisa = $db->table('bpb_ceisa')
            ->where('nomor_aju', 'like', $prefix . $currentYear . '%')
            ->where('jenis_bc', '3.0')
            ->orderBy('nomor_aju', 'desc')
            ->first();

        $localSeq = 0;
        if ($lastCeisa && $lastCeisa->nomor_aju && strlen($lastCeisa->nomor_aju) === 26) {
            $localSeq = (int) substr($lastCeisa->nomor_aju, -6);
        }

        $ceisaSeq = $this->ceisaService->getLastSequenceFromCeisa($prefix . $currentYear, '30');

        $maxSeq  = max($localSeq, $ceisaSeq);
        $nextSeq = str_pad($maxSeq + 1, 6, '0', STR_PAD_LEFT);

        return $prefix . $today . $nextSeq;
    }

    // UPDATE DRAFT BC 3.0
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
                        'kodeJenisKemasan' => $k['kodeJenisKemasan'] ?? '',
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
                        'kodeBendera'     => $p['kodeBendera'] ?? '',
                        'namaPengangkut'  => $p['namaPengangkut'] ?? '',
                        'nomorPengangkut' => $p['nomorPengangkut'] ?? '',
                        'kodeCaraAngkut'  => $p['kodeCaraAngkut'] ?? '',
                    ];
                }
            }

            // --- 5. Bank Devisa ---
            $bankDevisaInput = $request->input('bankDevisa', []);
            $bankDevisaList  = [];
            $seriBank = 1;
            foreach ($bankDevisaInput as $b) {
                if (!empty($b['kodeBank']) || !empty($b['namaBank'])) {
                    $bankDevisaList[] = [
                        'seriBank' => $seriBank++,
                        'kodeBank' => $b['kodeBank'] ?? '',
                        'namaBank' => $b['namaBank'] ?? '',
                    ];
                }
            }

            // --- 6. Entitas & Pemilik Barang ---
            $entitasInput = $request->input('entitas', []);
            $entitasList  = [];
            foreach ($entitasInput as $kodeEntitas => $e) {
                $entitasList[] = [
                    'seriEntitas'        => (int) ($e['seriEntitas'] ?? 0),
                    'kodeEntitas'        => (string) $kodeEntitas,
                    'kodeJenisIdentitas' => $e['kodeJenisIdentitas'] ?? '',
                    'nomorIdentitas'     => $e['nomorIdentitas'] ?? '',
                    'namaEntitas'        => $e['namaEntitas'] ?? '',
                    'alamatEntitas'      => $e['alamatEntitas'] ?? '',
                    'kodeNegara'         => $e['kodeNegara'] ?? '',
                    'nibEntitas'         => $e['nibEntitas'] ?? '',
                    'statusEntitas'      => $e['statusEntitas'] ?? '',
                    'nitku'              => $e['nitku'] ?? '',
                    'kodeKategoriKonsolidator' => $e['kodeKategoriKonsolidator'] ?? '',
                ];
            }

            // Menggabungkan Pemilik Barang ke Entitas Utama (Kode: 7) untuk API CEISA
            $pemilikInput = $request->input('pemilik', []);
            $seriPemilikMulai = count($entitasList) + 1;
            foreach ($pemilikInput as $pem) {
                if (!empty($pem['noId']) || !empty($pem['nama'])) {
                    $entitasList[] = [
                        'seriEntitas'        => $seriPemilikMulai++,
                        'kodeEntitas'        => '7',
                        'kodeJenisIdentitas' => $pem['jenisId'] ?? '',
                        'nomorIdentitas'     => $pem['noId'] ?? '',
                        'namaEntitas'        => $pem['nama'] ?? '',
                        'alamatEntitas'      => $pem['alamat'] ?? '',
                    ];
                }
            }

            // --- 7. Pungutan ---
            $pungutan = $request->input('pungutan', []);
            if (is_array($pungutan)) {
                foreach ($pungutan as &$p) {
                    if (isset($p['nilaiPungutan'])) {
                        $p['nilaiPungutan'] = (float) $p['nilaiPungutan'];
                    }
                }
                unset($p);
            }

            // --- 8. Kesiapan Barang (PKB) ---
            $kesiapanBarangList = array_values($request->input('kesiapanBarang', []));

            // --- 9. Barang ---
            $barangList = array_map(function ($brg) {
                $brg['fob']               = (float) ($brg['fob'] ?? 0);
                $brg['hargaSatuan']       = (float) ($brg['hargaSatuan'] ?? 0);
                $brg['hargaEkspor']       = (float) ($brg['hargaEkspor'] ?? 0);
                $brg['hargaPatokan']      = (float) ($brg['hargaPatokan'] ?? 0);
                $brg['netto']             = (float) ($brg['netto'] ?? 0);
                $brg['jumlahSatuan']      = (float) ($brg['jumlahSatuan'] ?? 0);
                $brg['jumlahKemasan']     = (float) ($brg['jumlahKemasan'] ?? 0);
                $brg['seriBarang']        = (int) ($brg['seriBarang'] ?? 0);
                $brg['kodeDokumen']       = '30';

                // Format Tarif Barang (Jika Ada)
                if (isset($brg['barangTarif']) && is_array($brg['barangTarif'])) {
                    foreach ($brg['barangTarif'] as &$tarif) {
                        $tarif['jumlahSatuan'] = (float) ($brg['jumlahSatuan'] ?? 0);
                        $tarif['tarif']        = (float) ($tarif['tarif'] ?? 0);
                    }
                }

                // Bersihkan data array kosong
                if (isset($brg['dokFasilitas'])) $brg['dokFasilitas'] = array_values($brg['dokFasilitas']);
                if (isset($brg['barangPemilik'])) $brg['barangPemilik'] = array_values($brg['barangPemilik']);

                return $brg;
            }, array_values($request->input('barang', [])));

            $payloadJson = [
                'asalData'              => 'S',
                'disclaimer'            => '1',
                'kodeDokumen'           => '30',

                // Header Dokumen
                'nomorAju'              => $request->input('nomorAju', ''),
                'tanggalAju'            => $request->input('tanggalAju', date('Y-m-d')),
                'kodeKantor'            => $request->input('kodeKantor', '050500'),
                'kodeKantorEkspor'      => $request->input('kodeKantorEkspor', ''),
                'kodeKantorMuat'        => $request->input('kodeKantorMuat', ''),
                'kodeKantorPeriksa'     => $request->input('kodeKantorPeriksa', ''),

                // Pelabuhan & Pengangkutan
                'kodePelEkspor'         => $request->input('kodePelEkspor', ''),
                'kodePelMuat'           => $request->input('kodePelMuat', ''),
                'kodePelTujuan'         => $request->input('kodePelTujuan', ''),
                'kodeLokasi'            => $request->input('kodeLokasi', ''),
                'kodeTps'               => $request->input('kodeTps', ''),
                'kodeLokasiTps'         => $request->input('kodeLokasiTps', ''),
                'kodeNegaraTujuan'      => $request->input('kodeNegaraTujuan', ''),
                'kodeJenisPengangkutan' => $request->input('kodeJenisPengangkutan', ''),

                // Nilai, Keuangan & Perdagangan
                'kodeCaraDagang'        => $request->input('kodeCaraDagang', ''),
                'kodeCaraBayar'         => $request->input('kodeCaraBayar', ''),
                'kodeIncoterm'          => $request->input('kodeIncoterm', ''),
                'kodeJenisEkspor'       => $request->input('kodeJenisEkspor', ''),
                'kodeKategoriEkspor'    => $request->input('kodeKategoriEkspor', ''),
                'kodeValuta'            => $request->input('kodeValuta', 'IDR'),
                'kodePembayar'          => $request->input('kodePembayar', ''), // Bila ada
                'ndpbm'                 => (float) $request->input('ndpbm', 0),
                'cif'                   => (float) $request->input('cif', 0),
                'fob'                   => (float) $request->input('fob', 0),
                'asuransi'              => (float) $request->input('asuransi', 0),
                'kodeAsuransi'          => $request->input('kodeAsuransi', 'LN'),
                'freight'               => (float) $request->input('freight', 0),
                'bruto'                 => (float) $request->input('bruto', 0),
                'netto'                 => (float) $request->input('netto', 0),
                'nilaiMaklon'           => (float) $request->input('nilaiMaklon', 0),
                'nilaiPph'              => (float) $request->input('nilaiPph', 0), // UPDATE: Dimasukkan!
                'totalDanaSawit'        => (float) $request->input('totalDanaSawit', 0),
                'jumlahKontainer'       => count($kontainerList), // Wajib sesuai skema

                // Flag Khusus Ekspor
                'flagBarkir'            => $request->input('flagBarkir', 'T'),
                'flagCurah'             => $request->input('flagCurah', '2'),
                'flagMigas'             => $request->input('flagMigas', '2'),

                // Waktu
                'tanggalEkspor'         => $request->input('tanggalEkspor', date('Y-m-d')),
                'tanggalPeriksa'        => $request->input('tanggalPeriksa', date('Y-m-d')),
                'tanggalTtd'            => $request->input('tanggalTtd', date('Y-m-d')),

                // Penandatangan
                'namaTtd'               => $request->input('namaTtd', ''),
                'jabatanTtd'            => $request->input('jabatanTtd', ''),
                'kotaTtd'               => $request->input('kotaTtd', ''),

                'entitas'               => $entitasList,
                'dokumen'               => $dokumenList,
                'kemasan'               => $kemasanList,
                'kontainer'             => $kontainerList,
                'pengangkut'            => $pengangkutList,
                'bankDevisa'            => $bankDevisaList,
                'pungutan'              => $pungutan,
                'kesiapanBarang'        => $kesiapanBarangList,
                'barang'                => $barangList,

                'entitasUi'             => $entitasInput,
                'pemilik'               => array_values($pemilikInput),
                'dok'                   => $dokumenList,
            ];

            // --- Update Database ---
            DB::connection('mysql_sb')->table('bpb_ceisa')->updateOrInsert(
                ['bpbno' => $id],
                [
                    'tanggal_aju'  => $request->input('tanggalAju', date('Y-m-d')),
                    'nomor_aju'    => $request->input('nomorAju'),
                    'payload_json' => json_encode($payloadJson),
                    'jenis_bc'     => '3.0',
                    'updated_at'   => date('Y-m-d H:i:s'),
                    'bpbno_int'    => $request->input('bppbno_int') ?? null,
                ]
            );

            DB::connection('mysql_sb')->commit();

            return back()->with('success', 'Data draft BC 3.0 berhasil disimpan!');

        } catch (\Exception $e) {
            DB::connection('mysql_sb')->rollBack();
            Log::error('Error Update Draft BC 3.0: ' . $e->getMessage() . ' di baris ' . $e->getLine());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
        }
    }

    // SEND CEISA BC 3.0
    public function sendCeisa($id, Request $request)
    {
        $db = DB::connection('mysql_sb');

        try {
            $header = $db->table('bppb as a')
                ->where(function ($query) use ($id) {
                    $query->where('a.bppbno', $id)->orWhere('a.bppbno_int', $id);
                })
                ->first();

            if (!$header) throw new \Exception('Data transaksi tidak ditemukan!');

            $ceisaInfo = $db->table('bpb_ceisa')->where('bpbno', $id)->first();
            if (!$ceisaInfo || empty($ceisaInfo->payload_json)) {
                throw new \Exception('Data CEISA belum disiapkan. Simpan draft terlebih dahulu.');
            }

            $draft = json_decode($ceisaInfo->payload_json, true);

            $dokumenDraft = $draft['dokumen'] ?? ($draft['dok'] ?? []);
            $invoice = null;
            $packingList = null;
            $otherDocs = [];

            foreach ($dokumenDraft as $d) {
                $kode = trim(explode(' - ', $d['kodeDokumen'] ?? $d['kode'] ?? '')[0]);
                $nomor = $d['nomorDokumen'] ?? $d['nomor'] ?? '';
                $tgl = $d['tanggalDokumen'] ?? $d['tgl'] ?? date('Y-m-d');

                if (empty($kode) || empty($nomor)) continue;

                $docObj = [
                    'idDokumen'      => strval($d['idDokumen'] ?? ''),
                    'kodeDokumen'    => $kode,
                    'nomorDokumen'   => $nomor,
                    'tanggalDokumen' => $tgl,
                ];

                if ($kode === '380' && !$invoice) {
                    $invoice = $docObj;
                } elseif ($kode === '217' && !$packingList) {
                    $packingList = $docObj;
                } else {
                    $otherDocs[] = $docObj;
                }
            }

            // if (!$invoice) {
            //     throw new \Exception('Validasi Gagal: Dokumen BC 3.0 wajib melampirkan INVOICE (Kode 380). Silakan tambahkan terlebih dahulu di Tab Dokumen Pendukung.');
            // }
            // if (!$packingList) {
            //     throw new \Exception('Validasi Gagal: Dokumen BC 3.0 wajib melampirkan PACKING LIST (Kode 217). Silakan tambahkan terlebih dahulu di Tab Dokumen Pendukung.');
            // }

            $payloadDokumen = [];
            $seriDok = 1;

            if ($invoice) {
                $invoice['seriDokumen'] = $seriDok++;
                $payloadDokumen[] = $invoice;
            }

            if ($packingList) {
                $packingList['seriDokumen'] = $seriDok++;
                $payloadDokumen[] = $packingList;
            }

            foreach ($otherDocs as $od) {
                $od['seriDokumen'] = $seriDok++;
                $payloadDokumen[] = $od;
            }

            $payloadKemasan = [];
            $seriKem = 1;
            foreach (($draft['kemasan'] ?? []) as $k) {
                $payloadKemasan[] = [
                    'seriKemasan'      => $seriKem++,
                    'jumlahKemasan'    => (int) ($k['jumlahKemasan'] ?? 0),
                    'kodeJenisKemasan' => !empty($k['kodeJenisKemasan']) ? strval($k['kodeJenisKemasan']) : 'CT',
                    'merkKemasan'      => !empty($k['merkKemasan']) ? strval($k['merkKemasan']) : '-',
                ];
            }
            if (empty($payloadKemasan)) {
                $payloadKemasan[] = [
                    'seriKemasan'      => 1,
                    'jumlahKemasan'    => 0,
                    'kodeJenisKemasan' => 'CT',
                    'merkKemasan'      => '-',
                ];
            }

            $payloadKontainer = [];
            foreach (($draft['kontainer'] ?? []) as $k) {
                $jenisKont = strval($k['kodeJenisKontainer'] ?? '');
                if (!in_array($jenisKont, ["4", "7", "8"])) $jenisKont = "4";

                $tipeKont = strval($k['kodeTipeKontainer'] ?? '');
                if (!in_array($tipeKont, ["1", "2", "3", "4", "5", "6", "7", "8", "99"])) $tipeKont = "1";

                $ukuranKont = strval($k['kodeUkuranKontainer'] ?? '');
                if (!in_array($ukuranKont, ["20", "40", "45", "60"])) $ukuranKont = "20";

                $payloadKontainer[] = [
                    'seriKontainer'       => (int) ($k['seriKontainer'] ?? 1),
                    'nomorKontainer'      => strtoupper($k['nomorKontainer'] ?? ''),
                    'kodeJenisKontainer'  => $jenisKont,
                    'kodeTipeKontainer'   => $tipeKont,
                    'kodeUkuranKontainer' => $ukuranKont,
                ];
            }

            $payloadPengangkut = [];
            $seriPeng = 1;
            foreach (($draft['pengangkut'] ?? []) as $p) {
                $payloadPengangkut[] = [
                    'seriPengangkut'  => $seriPeng++,
                    'kodeBendera'     => !empty($p['kodeBendera']) ? strval($p['kodeBendera']) : 'ID',
                    'namaPengangkut'  => !empty($p['namaPengangkut']) ? strval($p['namaPengangkut']) : '-',
                    'nomorPengangkut' => !empty($p['nomorPengangkut']) ? strval($p['nomorPengangkut']) : '-',
                    'kodeCaraAngkut'  => !empty($p['kodeCaraAngkut']) ? strval($p['kodeCaraAngkut']) : '3',
                ];
            }
            if (empty($payloadPengangkut)) {
                $payloadPengangkut[] = [
                    'seriPengangkut'  => 1,
                    'kodeBendera'     => 'ID',
                    'namaPengangkut'  => '-',
                    'nomorPengangkut' => '-',
                    'kodeCaraAngkut'  => '3',
                ];
            }

            $payloadBankDevisa = [];
            $seriBank = 1;
            foreach (($draft['bankDevisa'] ?? []) as $b) {
                $payloadBankDevisa[] = [
                    'seriBank' => $seriBank++,
                    'kodeBank' => strval($b['kodeBank'] ?? ''),
                    'namaBank' => strval($b['namaBank'] ?? ''),
                ];
            }
            if (empty($payloadBankDevisa)) {
                $payloadBankDevisa = [];
            }

            $entitasDraft = $draft['entitas'] ?? [];
            $eksportir    = array_filter($entitasDraft, function($e) { return ($e['kodeEntitas'] ?? '') == '2'; });
            $pemilik      = array_filter($entitasDraft, function($e) { return ($e['kodeEntitas'] ?? '') == '7'; });
            $penerima     = array_filter($entitasDraft, function($e) { return ($e['kodeEntitas'] ?? '') == '8'; });
            $pembeli      = array_filter($entitasDraft, function($e) { return ($e['kodeEntitas'] ?? '') == '6'; });
            $ppjk         = array_filter($entitasDraft, function($e) { return ($e['kodeEntitas'] ?? '') == '4'; });
            $konsolidator = array_filter($entitasDraft, function($e) { return ($e['kodeEntitas'] ?? '') == '23'; });

            $payloadEntitas = [];
            $seriEnt = 1;

            // 1. Eksportir (2)
            $eks = reset($eksportir);
            if (!$eks) $eks = ['kodeEntitas' => '2'];
            $jenisIdEks = strval($eks['kodeJenisIdentitas'] ?? '5');
            if (!in_array($jenisIdEks, ["2", "3", "4", "5", "6"])) $jenisIdEks = '5';
            $payloadEntitas[] = [
                'alamatEntitas'      => !empty($eks['alamatEntitas']) ? strval($eks['alamatEntitas']) : '-',
                'kodeEntitas'        => '2',
                'kodeJenisIdentitas' => $jenisIdEks,
                'namaEntitas'        => !empty($eks['namaEntitas']) ? strval($eks['namaEntitas']) : '-',
                'nibEntitas'         => strval($eks['nibEntitas'] ?? ''),
                'nomorIdentitas'     => !empty($eks['nomorIdentitas']) && $eks['nomorIdentitas'] !== '-' ? strval($eks['nomorIdentitas']) : config('ceisa.id_perusahaan_dev', '0745406926444000'),
                'seriEntitas'        => $seriEnt++,
            ];

            // 2. Pemilik (7)
            $pem = reset($pemilik);
            if (!$pem) {
                $pem = $eks;
                $pem['kodeEntitas'] = '7';
            }
            $jenisIdPem = strval($pem['kodeJenisIdentitas'] ?? '5');
            if (!in_array($jenisIdPem, ["2", "3", "4", "5", "6"])) $jenisIdPem = '5';
            $payloadEntitas[] = [
                'alamatEntitas'      => !empty($pem['alamatEntitas']) ? strval($pem['alamatEntitas']) : '-',
                'kodeEntitas'        => '7',
                'kodeJenisIdentitas' => $jenisIdPem,
                'namaEntitas'        => !empty($pem['namaEntitas']) ? strval($pem['namaEntitas']) : '-',
                'nibEntitas'         => strval($pem['nibEntitas'] ?? ''),
                'nomorIdentitas'     => !empty($pem['nomorIdentitas']) && $pem['nomorIdentitas'] !== '-' ? strval($pem['nomorIdentitas']) : config('ceisa.id_perusahaan_dev', '0745406926444000'),
                'seriEntitas'        => $seriEnt++,
            ];

            // 3. Penerima (8)
            $pen = reset($penerima);
            if (!$pen) $pen = ['kodeEntitas' => '8'];
            $payloadEntitas[] = [
                'alamatEntitas' => !empty($pen['alamatEntitas']) ? strval($pen['alamatEntitas']) : '-',
                'kodeEntitas'   => '8',
                'kodeNegara'    => strval($pen['kodeNegara'] ?? ''),
                'namaEntitas'   => !empty($pen['namaEntitas']) ? strval($pen['namaEntitas']) : '-',
                'seriEntitas'   => $seriEnt++,
            ];

            // 4. Pembeli (6)
            $bel = reset($pembeli);
            if (!$bel) {
                $bel = $pen;
                $bel['kodeEntitas'] = '6';
            }
            $payloadEntitas[] = [
                'alamatEntitas' => !empty($bel['alamatEntitas']) ? strval($bel['alamatEntitas']) : '-',
                'kodeEntitas'   => '6',
                'kodeNegara'    => strval($bel['kodeNegara'] ?? ''),
                'namaEntitas'   => !empty($bel['namaEntitas']) ? strval($bel['namaEntitas']) : '-',
                'seriEntitas'   => $seriEnt++,
            ];

            // 5. PPJK (4)
            $pjk = reset($ppjk);
            if ($pjk && !empty($pjk['nomorIdentitas']) && $pjk['nomorIdentitas'] !== '-') {
                $jenisIdPjk = strval($pjk['kodeJenisIdentitas'] ?? '5');
                if (!in_array($jenisIdPjk, ["2", "3", "4", "5", "6"])) $jenisIdPjk = '5';
                $payloadEntitas[] = [
                    'alamatEntitas'      => !empty($pjk['alamatEntitas']) ? strval($pjk['alamatEntitas']) : '-',
                    'kodeEntitas'        => '4',
                    'kodeJenisIdentitas' => $jenisIdPjk,
                    'namaEntitas'        => !empty($pjk['namaEntitas']) ? strval($pjk['namaEntitas']) : '-',
                    'nibEntitas'         => strval($pjk['nibEntitas'] ?? ''),
                    'nomorIdentitas'     => strval($pjk['nomorIdentitas']),
                    'seriEntitas'        => $seriEnt++,
                ];
            }

            // 6. Konsolidator (23) - Optional
            $kon = reset($konsolidator);
            if ($kon && !empty($kon['nomorIdentitas']) && $kon['nomorIdentitas'] !== '-') {
                $jenisIdKon = strval($kon['kodeJenisIdentitas'] ?? '5');
                if (!in_array($jenisIdKon, ["2", "3", "4", "5", "6"])) $jenisIdKon = '5';
                $payloadEntitas[] = [
                    'alamatEntitas'      => !empty($kon['alamatEntitas']) ? strval($kon['alamatEntitas']) : '-',
                    'kodeEntitas'        => '23',
                    'kodeJenisIdentitas' => $jenisIdKon,
                    'namaEntitas'        => !empty($kon['namaEntitas']) ? strval($kon['namaEntitas']) : '-',
                    'nibEntitas'         => strval($kon['nibEntitas'] ?? ''),
                    'nomorIdentitas'     => strval($kon['nomorIdentitas']),
                    'seriEntitas'        => $seriEnt++,
                    'kodeKategoriKonsolidator' => strval($kon['kodeKategoriKonsolidator'] ?? ''),
                ];
            }

            $kesiapanBarangList = [];
            foreach (($draft['kesiapanBarang'] ?? []) as $kb) {
                $waktuStr = $kb['waktuSiapPeriksa'] ?? date('Y-m-d\TH:i');
                $waktu = \Carbon\Carbon::parse($waktuStr)->format('Y-m-d\TH:i:s');

                $caraStuffing = strval($kb['kodeCaraStuffing'] ?? '');
                if (!in_array($caraStuffing, ["4", "7", "8"])) {
                    $caraStuffing = "4"; // Default 4 (FCL)
                }

                $jenisGudang = strval($kb['kodeJenisGudang'] ?? '');
                if (!in_array($jenisGudang, ["1", "2", "3", "4"])) {
                    $jenisGudang = "2"; // Default 2 (Gudang Pabrik)
                }

                $jenisBarang = strval($kb['kodeJenisBarang'] ?? '');
                if (!in_array($jenisBarang, ["1", "2"])) {
                    $jenisBarang = "1";
                }

                $jenisPartOf = strval($kb['kodeJenisPartOf'] ?? '');
                if (!in_array($jenisPartOf, ["1", "2", "", "NULL"])) {
                    $jenisPartOf = "1";
                }

                $kesiapanBarangList[] = [
                    'alamat'            => !empty($kb['alamat']) ? strval($kb['alamat']) : '-',
                    'kodeJenisBarang'   => $jenisBarang,
                    'kodeJenisGudang'   => $jenisGudang,
                    'lokasiSiapPeriksa' => !empty($kb['lokasiSiapPeriksa']) ? strval($kb['lokasiSiapPeriksa']) : '-',
                    'namaPic'           => !empty($kb['namaPic']) ? strval($kb['namaPic']) : '-',
                    'nomorTelpPic'      => !empty($kb['nomorTelpPic']) ? strval($kb['nomorTelpPic']) : '-',
                    'tanggalPkb'        => !empty($kb['tanggalPkb']) ? strval($kb['tanggalPkb']) : date('Y-m-d'),
                    'waktuSiapPeriksa'  => $waktu,
                    'kodeCaraStuffing'  => $caraStuffing,
                    'kodeJenisPartOf'   => $jenisPartOf,
                    'jumlahContainer20' => (int) ($kb['jumlahContainer20'] ?? 0),
                    'jumlahContainer40' => (int) ($kb['jumlahContainer40'] ?? 0),
                ];
            }
            if (empty($kesiapanBarangList)) {
                $kesiapanBarangList[] = [
                    'alamat'            => '-',
                    'kodeJenisBarang'   => '1',
                    'kodeJenisGudang'   => '2',
                    'lokasiSiapPeriksa' => '-',
                    'namaPic'           => '-',
                    'nomorTelpPic'      => '-',
                    'tanggalPkb'        => date('Y-m-d'),
                    'waktuSiapPeriksa'  => date('Y-m-d\TH:i:s'),
                    'kodeCaraStuffing'  => '4',
                    'kodeJenisPartOf'   => '1',
                    'jumlahContainer20' => 0,
                    'jumlahContainer40' => 0,
                ];
            }

            $payloadBarang = [];
            foreach (($draft['barang'] ?? []) as $index => $brg) {
                $barangTarif = [];
                foreach (($brg['barangTarif'] ?? []) as $tarif) {
                    $barangTarif[] = [
                        'kodeJenisTarif'     => $tarif['kodeJenisTarif'] ?? '1',
                        'jumlahSatuan'       => (float) ($tarif['jumlahSatuan'] ?? $brg['jumlahSatuan'] ?? 0),
                        'kodeFasilitasTarif' => $tarif['kodeFasilitasTarif'] ?? '3',
                        'kodeSatuanBarang'   => $tarif['kodeSatuanBarang'] ?? '',
                        'kodeJenisPungutan'  => $tarif['kodeJenisPungutan'] ?? 'BM',
                        'nilaiBayar'         => (float) ($tarif['nilaiBayar'] ?? 0),
                        'seriBarang'         => (int) ($brg['seriBarang'] ?? ($index + 1)),
                        'tarif'              => (float) ($tarif['tarif'] ?? 0),
                    ];
                }

                $barangDokumen = [];
                foreach (($brg['dokFasilitas'] ?? []) as $df) {
                    $barangDokumen[] = [
                        'kodeDokumen'    => $df['kodeDokumen'] ?? '',
                        'nomorDokumen'   => $df['nomorDokumen'] ?? '',
                        'tanggalDokumen' => $df['tanggalDokumen'] ?? '',
                        'kodeFasilitas'  => $df['kodeFasilitas'] ?? '',
                        'seriIjin'       => $df['seriIjin'] ?? '',
                    ];
                }

                $entitasBarang = [];
                foreach (($brg['entitasBarang'] ?? []) as $eb) {
                    $entitasBarang[] = [
                        'nomorIdentitas' => $eb['nomorIdentitas'] ?? '',
                        'namaEntitas'    => $eb['namaEntitas'] ?? '',
                        'alamatEntitas'  => $eb['alamatEntitas'] ?? '',
                    ];
                }

                $payloadBarang[] = [
                    'seriBarang'        => (int) ($brg['seriBarang'] ?? ($index + 1)),
                    'posTarif'          => strval($brg['posTarif'] ?? ''),
                    'kodeBarang'        => strval($brg['kodeBarang'] ?? ''),
                    'uraian'            => !empty($brg['uraian']) ? strval($brg['uraian']) : '-',
                    'merk'              => !empty($brg['merk']) ? strval($brg['merk']) : '-',
                    'tipe'              => !empty($brg['tipe']) ? strval($brg['tipe']) : '-',
                    'ukuran'            => !empty($brg['ukuran']) ? strval($brg['ukuran']) : '-',
                    'spesifikasiLain'   => !empty($brg['spesifikasiLain']) ? strval($brg['spesifikasiLain']) : '-',
                    'kodeNegaraAsal'    => !empty($brg['kodeNegaraAsal']) ? strval($brg['kodeNegaraAsal']) : 'ID',
                    'kodeDaerahAsal'    => strval($brg['kodeDaerahAsal'] ?? ''),
                    'jumlahSatuan'      => (float) ($brg['jumlahSatuan'] ?? 0),
                    'kodeSatuanBarang'  => strval($brg['kodeSatuanBarang'] ?? ''),
                    'jumlahKemasan'     => (float) ($brg['jumlahKemasan'] ?? 0),
                    'kodeJenisKemasan'  => !empty($brg['kodeJenisKemasan']) ? strval($brg['kodeJenisKemasan']) : 'CT',
                    'fob'               => (float) ($brg['fob'] ?? 0),
                    'netto'             => (float) ($brg['netto'] ?? 0),
                    'hargaEkspor'       => (float) ($brg['hargaEkspor'] ?? 0),
                    'hargaSatuan'       => (float) ($brg['hargaSatuan'] ?? 0),
                    'kodeJenisEkspor'   => !empty($brg['kodeJenisEkspor']) ? strval($brg['kodeJenisEkspor']) : (!empty($draft['kodeJenisEkspor']) ? strval($draft['kodeJenisEkspor']) : '1'),
                    'hargaPatokan'      => (float) ($brg['hargaPatokan'] ?? 0),
                    'kodeDokumen'       => '30',
                    'barangTarif'       => $barangTarif,
                    'dokFasilitas'      => $barangDokumen,
                    'entitasBarang'     => $entitasBarang,
                ];
            }

            $payloadPungutan = [];
            if (!empty($draft['pungutan']) && is_array($draft['pungutan'])) {
                foreach ($draft['pungutan'] as $p) {
                    if (isset($p['kodeJenisPungutan'])) {
                        $payloadPungutan[] = [
                            'kodeFasilitasTarif' => $p['kodeFasilitasTarif'] ?? '3',
                            'kodeJenisPungutan'  => $p['kodeJenisPungutan'],
                            'nilaiPungutan'      => (float) ($p['nilaiPungutan'] ?? 0),
                        ];
                    }
                }
            }

            $finalPayload = [
                'idPlatform'            => config('ceisa.id_platform_dev', ''),
                'asalData'              => 'S',
                'disclaimer'            => '1',
                'kodeDokumen'           => '30',
                'nomorAju'              => $ceisaInfo->nomor_aju ?? '',
                'tanggalAju'            => $draft['tanggalAju'] ?? date('Y-m-d'),

                // Kode Kantor & Rute
                'kodeKantor'            => !empty($draft['kodeKantor']) ? strval($draft['kodeKantor']) : '050500',
                'kodeKantorEkspor'      => !empty($draft['kodeKantorEkspor']) ? strval($draft['kodeKantorEkspor']) : (!empty($draft['kodeKantor']) ? strval($draft['kodeKantor']) : '050500'),
                'kodeKantorMuat'        => !empty($draft['kodeKantorMuat']) ? strval($draft['kodeKantorMuat']) : (!empty($draft['kodeKantor']) ? strval($draft['kodeKantor']) : '050500'),
                'kodeKantorPeriksa'     => !empty($draft['kodeKantorPeriksa']) ? strval($draft['kodeKantorPeriksa']) : (!empty($draft['kodeKantor']) ? strval($draft['kodeKantor']) : '050500'),
                'kodePelEkspor'         => !empty($draft['kodePelEkspor']) ? strval($draft['kodePelEkspor']) : 'IDTPP',
                'kodePelMuat'           => !empty($draft['kodePelMuat']) ? strval($draft['kodePelMuat']) : 'IDTPP',
                'kodePelTujuan'         => !empty($draft['kodePelTujuan']) ? strval($draft['kodePelTujuan']) : 'MYPKG',
                'kodeLokasi'            => !empty($draft['kodeLokasi']) ? strval($draft['kodeLokasi']) : '2',
                'kodeTps'               => strval($draft['kodeTps'] ?? ''),
                'kodeNegaraTujuan'      => !empty($draft['kodeNegaraTujuan']) ? strval($draft['kodeNegaraTujuan']) : '',
                'kodeJenisPengangkutan' => !empty($draft['kodeJenisPengangkutan']) ? strval($draft['kodeJenisPengangkutan']) : '1',

                // Parameter Bisnis & Transaksi
                'kodeCaraDagang'        => !empty($draft['kodeCaraDagang']) ? strval($draft['kodeCaraDagang']) : '1',
                'kodeCaraBayar'         => !empty($draft['kodeCaraBayar']) ? strval($draft['kodeCaraBayar']) : '1',
                'kodePembayar'          => strval($draft['kodePembayar'] ?? ''),
                'kodeIncoterm'          => !empty($draft['kodeIncoterm']) ? strval($draft['kodeIncoterm']) : 'FOB',
                'kodeJenisEkspor'       => !empty($draft['kodeJenisEkspor']) ? strval($draft['kodeJenisEkspor']) : '1',
                'kodeKategoriEkspor'    => !empty($draft['kodeKategoriEkspor']) ? strval($draft['kodeKategoriEkspor']) : '1',
                'kodeValuta'            => !empty($draft['kodeValuta']) ? strval($draft['kodeValuta']) : 'USD',

                // Indikator Flag
                'flagBarkir'            => !empty($draft['flagBarkir']) ? strval($draft['flagBarkir']) : 'T',
                'flagCurah'             => !empty($draft['flagCurah']) ? strval($draft['flagCurah']) : '2',
                'flagMigas'             => !empty($draft['flagMigas']) ? strval($draft['flagMigas']) : '2',

                // Nilai & Ukuran
                'ndpbm'                 => (float) ($draft['ndpbm'] ?? 0),
                'cif'                   => (float) ($draft['cif'] ?? 0),
                'asuransi'              => (float) ($draft['asuransi'] ?? 0),
                'kodeAsuransi'          => !empty($draft['kodeAsuransi']) ? strval($draft['kodeAsuransi']) : 'LN',
                'freight'               => (float) ($draft['freight'] ?? 0),
                'fob'                   => (float) ($draft['fob'] ?? 0),
                'bruto'                 => (float) ($draft['bruto'] ?? 0),
                'netto'                 => (float) ($draft['netto'] ?? 0),
                'nilaiMaklon'           => (float) ($draft['nilaiMaklon'] ?? 0),
                'nilaiPph'              => (float) ($draft['nilaiPph'] ?? 0),
                'totalDanaSawit'        => (float) ($draft['totalDanaSawit'] ?? 0),
                'jumlahKontainer'       => count($payloadKontainer),

                // Waktu & Penandatangan
                'tanggalEkspor'         => !empty($draft['tanggalEkspor']) ? strval($draft['tanggalEkspor']) : date('Y-m-d'),
                'tanggalPeriksa'        => !empty($draft['tanggalPeriksa']) ? strval($draft['tanggalPeriksa']) : date('Y-m-d'),
                'tanggalTtd'            => !empty($draft['tanggalTtd']) ? strval($draft['tanggalTtd']) : date('Y-m-d'),
                'namaTtd'               => !empty($draft['namaTtd']) ? strval($draft['namaTtd']) : 'nama_ttd',
                'jabatanTtd'            => !empty($draft['jabatanTtd']) ? strval($draft['jabatanTtd']) : 'jabatan_ttd',
                'kotaTtd'               => !empty($draft['kotaTtd']) ? strval($draft['kotaTtd']) : 'kota_ttd',

                // Memasukkan Hasil Pemetaan Array
                'entitas'               => $payloadEntitas,
                'dokumen'               => $payloadDokumen,
                'pengangkut'            => $payloadPengangkut,
                'kontainer'             => $payloadKontainer,
                'kemasan'               => $payloadKemasan,
                'bankDevisa'            => $payloadBankDevisa,
                'pungutan'              => $payloadPungutan,
                'kesiapanBarang'        => $kesiapanBarangList,
                'barang'                => $payloadBarang,
            ];

            $responseCeisa = $this->ceisaService->kirimDokumenBc30($finalPayload);

            if ($responseCeisa['successful']) {
                $db->table('bpb_ceisa')->where('bpbno', $id)->update([
                    'status'       => 1,
                    'updated_at'   => \Carbon\Carbon::now(),
                ]);

                return response()->json([
                    'status'         => 200,
                    'message'        => 'Dokumen BC 3.0 berhasil dikirim ke CEISA!',
                    'data_payload'   => $finalPayload,
                    'ceisa_response' => $responseCeisa['body'],
                ]);
            } else {
                return response()->json([
                    'status'      => $responseCeisa['status_code'],
                    'message'     => 'Gagal mengirim ke CEISA.',
                    'ceisa_error' => $responseCeisa['body'],
                ], $responseCeisa['status_code']);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => $e->getMessage() . ' di baris ' . $e->getLine(),
            ], 500);
        }
    }

    // HELPER: Daftar Kantor BC
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

    public function sendCeisaBatch30(array $bppbs, Request $request)
    {
        $db = DB::connection('mysql_sb');
        $firstBpb = $bppbs[0];
        try {
            $header = $db->table('bppb as a')
                ->where(function ($query) use ($firstBpb) {
                    $query->where('a.bppbno', $firstBpb)->orWhere('a.bppbno_int', $firstBpb);
                })
                ->first();

            if (!$header) throw new \Exception('Data transaksi tidak ditemukan!');

            $ceisaInfo = $db->table('bpb_ceisa')->where('bpbno', $firstBpb)->first();
            if (!$ceisaInfo || empty($ceisaInfo->payload_json)) {
                throw new \Exception('Data CEISA belum disiapkan. Simpan draft terlebih dahulu.');
            }

            $draft = json_decode($ceisaInfo->payload_json, true);

            $mergedBarang = [];

            foreach ($bppbs as $no_bpb) {
                $bppbData = $db->table('bpb_ceisa')->where('bpbno', $no_bpb)->first();
                if ($bppbData) {
                    $bppbPayload = json_decode($bppbData->payload_json ?? '{}', true);

                    if (isset($bppbPayload['barang']) && is_array($bppbPayload['barang'])) {
                        foreach ($bppbPayload['barang'] as $brg) {
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

            $dokumenDraft = $draft['dokumen'] ?? ($draft['dok'] ?? []);
            $invoice = null;
            $packingList = null;
            $otherDocs = [];

            foreach ($dokumenDraft as $d) {
                $kode = trim(explode(' - ', $d['kodeDokumen'] ?? $d['kode'] ?? '')[0]);
                $nomor = $d['nomorDokumen'] ?? $d['nomor'] ?? '';
                $tgl = $d['tanggalDokumen'] ?? $d['tgl'] ?? date('Y-m-d');

                if (empty($kode) || empty($nomor)) continue;

                $docObj = [
                    'idDokumen'      => strval($d['idDokumen'] ?? ''),
                    'kodeDokumen'    => $kode,
                    'nomorDokumen'   => $nomor,
                    'tanggalDokumen' => $tgl,
                ];

                if ($kode === '380' && !$invoice) {
                    $invoice = $docObj;
                } elseif ($kode === '217' && !$packingList) {
                    $packingList = $docObj;
                } else {
                    $otherDocs[] = $docObj;
                }
            }

            $payloadDokumen = [];
            $seriDok = 1;

            if ($invoice) {
                $invoice['seriDokumen'] = $seriDok++;
                $payloadDokumen[] = $invoice;
            }

            if ($packingList) {
                $packingList['seriDokumen'] = $seriDok++;
                $payloadDokumen[] = $packingList;
            }

            foreach ($otherDocs as $od) {
                $od['seriDokumen'] = $seriDok++;
                $payloadDokumen[] = $od;
            }

            $payloadKemasan = [];
            $seriKem = 1;
            foreach (($draft['kemasan'] ?? []) as $k) {
                $payloadKemasan[] = [
                    'seriKemasan'      => $seriKem++,
                    'jumlahKemasan'    => (int) ($k['jumlahKemasan'] ?? 0),
                    'kodeJenisKemasan' => !empty($k['kodeJenisKemasan']) ? strval($k['kodeJenisKemasan']) : 'CT',
                    'merkKemasan'      => !empty($k['merkKemasan']) ? strval($k['merkKemasan']) : '-',
                ];
            }
            if (empty($payloadKemasan)) {
                $payloadKemasan[] = [
                    'seriKemasan'      => 1,
                    'jumlahKemasan'    => 0,
                    'kodeJenisKemasan' => 'CT',
                    'merkKemasan'      => '-',
                ];
            }

            $payloadKontainer = [];
            foreach (($draft['kontainer'] ?? []) as $k) {
                $jenisKont = strval($k['kodeJenisKontainer'] ?? '');
                if (!in_array($jenisKont, ["4", "7", "8"])) $jenisKont = "4";

                $tipeKont = strval($k['kodeTipeKontainer'] ?? '');
                if (!in_array($tipeKont, ["1", "2", "3", "4", "5", "6", "7", "8", "99"])) $tipeKont = "1";

                $ukuranKont = strval($k['kodeUkuranKontainer'] ?? '');
                if (!in_array($ukuranKont, ["20", "40", "45", "60"])) $ukuranKont = "20";

                $payloadKontainer[] = [
                    'seriKontainer'       => (int) ($k['seriKontainer'] ?? 1),
                    'nomorKontainer'      => strtoupper($k['nomorKontainer'] ?? ''),
                    'kodeJenisKontainer'  => $jenisKont,
                    'kodeTipeKontainer'   => $tipeKont,
                    'kodeUkuranKontainer' => $ukuranKont,
                ];
            }

            $payloadPengangkut = [];
            $seriPeng = 1;
            foreach (($draft['pengangkut'] ?? []) as $p) {
                $payloadPengangkut[] = [
                    'seriPengangkut'  => $seriPeng++,
                    'kodeBendera'     => !empty($p['kodeBendera']) ? strval($p['kodeBendera']) : 'ID',
                    'namaPengangkut'  => !empty($p['namaPengangkut']) ? strval($p['namaPengangkut']) : '-',
                    'nomorPengangkut' => !empty($p['nomorPengangkut']) ? strval($p['nomorPengangkut']) : '-',
                    'kodeCaraAngkut'  => !empty($p['kodeCaraAngkut']) ? strval($p['kodeCaraAngkut']) : '3',
                ];
            }
            if (empty($payloadPengangkut)) {
                $payloadPengangkut[] = [
                    'seriPengangkut'  => 1,
                    'kodeBendera'     => 'ID',
                    'namaPengangkut'  => '-',
                    'nomorPengangkut' => '-',
                    'kodeCaraAngkut'  => '3',
                ];
            }

            $payloadBankDevisa = [];
            $seriBank = 1;
            foreach (($draft['bankDevisa'] ?? []) as $b) {
                $payloadBankDevisa[] = [
                    'seriBank' => $seriBank++,
                    'kodeBank' => strval($b['kodeBank'] ?? ''),
                    'namaBank' => strval($b['namaBank'] ?? ''),
                ];
            }
            if (empty($payloadBankDevisa)) {
                $payloadBankDevisa[] = [
                    'seriBank' => 1,
                    'kodeBank' => '014',
                    'namaBank' => 'BANK CENTRAL ASIA',
                ];
            }

            $entitasDraft = $draft['entitas'] ?? [];
            $eksportir    = array_filter($entitasDraft, function($e) { return ($e['kodeEntitas'] ?? '') == '2'; });
            $pemilik      = array_filter($entitasDraft, function($e) { return ($e['kodeEntitas'] ?? '') == '7'; });
            $penerima     = array_filter($entitasDraft, function($e) { return ($e['kodeEntitas'] ?? '') == '8'; });
            $pembeli      = array_filter($entitasDraft, function($e) { return ($e['kodeEntitas'] ?? '') == '6'; });
            $ppjk         = array_filter($entitasDraft, function($e) { return ($e['kodeEntitas'] ?? '') == '4'; });
            $konsolidator = array_filter($entitasDraft, function($e) { return ($e['kodeEntitas'] ?? '') == '23'; });

            $payloadEntitas = [];
            $seriEnt = 1;

            // 1. Eksportir (2)
            $eks = reset($eksportir);
            if (!$eks) $eks = ['kodeEntitas' => '2'];
            $jenisIdEks = strval($eks['kodeJenisIdentitas'] ?? '5');
            if (!in_array($jenisIdEks, ["2", "3", "4", "5", "6"])) $jenisIdEks = '5';
            $payloadEntitas[] = [
                'alamatEntitas'      => !empty($eks['alamatEntitas']) ? strval($eks['alamatEntitas']) : '-',
                'kodeEntitas'        => '2',
                'kodeJenisIdentitas' => $jenisIdEks,
                'namaEntitas'        => !empty($eks['namaEntitas']) ? strval($eks['namaEntitas']) : '-',
                'nibEntitas'         => strval($eks['nibEntitas'] ?? ''),
                'nomorIdentitas'     => !empty($eks['nomorIdentitas']) && $eks['nomorIdentitas'] !== '-' ? strval($eks['nomorIdentitas']) : config('ceisa.id_perusahaan_dev', '0745406926444000'),
                'seriEntitas'        => $seriEnt++,
            ];

            // 2. Pemilik (7)
            $pem = reset($pemilik);
            if (!$pem) {
                $pem = $eks;
                $pem['kodeEntitas'] = '7';
            }
            $jenisIdPem = strval($pem['kodeJenisIdentitas'] ?? '5');
            if (!in_array($jenisIdPem, ["2", "3", "4", "5", "6"])) $jenisIdPem = '5';
            $payloadEntitas[] = [
                'alamatEntitas'      => !empty($pem['alamatEntitas']) ? strval($pem['alamatEntitas']) : '-',
                'kodeEntitas'        => '7',
                'kodeJenisIdentitas' => $jenisIdPem,
                'namaEntitas'        => !empty($pem['namaEntitas']) ? strval($pem['namaEntitas']) : '-',
                'nibEntitas'         => strval($pem['nibEntitas'] ?? ''),
                'nomorIdentitas'     => !empty($pem['nomorIdentitas']) && $pem['nomorIdentitas'] !== '-' ? strval($pem['nomorIdentitas']) : config('ceisa.id_perusahaan_dev', '0745406926444000'),
                'seriEntitas'        => $seriEnt++,
            ];

            // 3. Penerima (8)
            $pen = reset($penerima);
            if (!$pen) $pen = ['kodeEntitas' => '8'];
            $payloadEntitas[] = [
                'alamatEntitas' => !empty($pen['alamatEntitas']) ? strval($pen['alamatEntitas']) : '-',
                'kodeEntitas'   => '8',
                'kodeNegara'    => strval($pen['kodeNegara'] ?? ''),
                'namaEntitas'   => !empty($pen['namaEntitas']) ? strval($pen['namaEntitas']) : '-',
                'seriEntitas'   => $seriEnt++,
            ];

            // 4. Pembeli (6)
            $bel = reset($pembeli);
            if (!$bel) {
                $bel = $pen;
                $bel['kodeEntitas'] = '6';
            }
            $payloadEntitas[] = [
                'alamatEntitas' => !empty($bel['alamatEntitas']) ? strval($bel['alamatEntitas']) : '-',
                'kodeEntitas'   => '6',
                'kodeNegara'    => strval($bel['kodeNegara'] ?? ''),
                'namaEntitas'   => !empty($bel['namaEntitas']) ? strval($bel['namaEntitas']) : '-',
                'seriEntitas'   => $seriEnt++,
            ];

            // 5. PPJK (4)
            $pjk = reset($ppjk);
            if ($pjk && !empty($pjk['nomorIdentitas']) && $pjk['nomorIdentitas'] !== '-') {
                $jenisIdPjk = strval($pjk['kodeJenisIdentitas'] ?? '5');
                if (!in_array($jenisIdPjk, ["2", "3", "4", "5", "6"])) $jenisIdPjk = '5';
                $payloadEntitas[] = [
                    'alamatEntitas'      => !empty($pjk['alamatEntitas']) ? strval($pjk['alamatEntitas']) : '-',
                    'kodeEntitas'        => '4',
                    'kodeJenisIdentitas' => $jenisIdPjk,
                    'namaEntitas'        => !empty($pjk['namaEntitas']) ? strval($pjk['namaEntitas']) : '-',
                    'nibEntitas'         => strval($pjk['nibEntitas'] ?? ''),
                    'nomorIdentitas'     => strval($pjk['nomorIdentitas']),
                    'seriEntitas'        => $seriEnt++,
                ];
            }

            // 6. Konsolidator (23) - Optional
            $kon = reset($konsolidator);
            if ($kon && !empty($kon['nomorIdentitas']) && $kon['nomorIdentitas'] !== '-') {
                $jenisIdKon = strval($kon['kodeJenisIdentitas'] ?? '5');
                if (!in_array($jenisIdKon, ["2", "3", "4", "5", "6"])) $jenisIdKon = '5';
                $payloadEntitas[] = [
                    'alamatEntitas'      => !empty($kon['alamatEntitas']) ? strval($kon['alamatEntitas']) : '-',
                    'kodeEntitas'        => '23',
                    'kodeJenisIdentitas' => $jenisIdKon,
                    'namaEntitas'        => !empty($kon['namaEntitas']) ? strval($kon['namaEntitas']) : '-',
                    'nibEntitas'         => strval($kon['nibEntitas'] ?? ''),
                    'nomorIdentitas'     => strval($kon['nomorIdentitas']),
                    'seriEntitas'        => $seriEnt++,
                    'kodeKategoriKonsolidator' => strval($kon['kodeKategoriKonsolidator'] ?? ''),
                ];
            }

            $kesiapanBarangList = [];
            foreach (($draft['kesiapanBarang'] ?? []) as $kb) {
                $waktuStr = $kb['waktuSiapPeriksa'] ?? date('Y-m-d\TH:i');
                $waktu = \Carbon\Carbon::parse($waktuStr)->format('Y-m-d\TH:i:s');

                $caraStuffing = strval($kb['kodeCaraStuffing'] ?? '');
                if (!in_array($caraStuffing, ["4", "7", "8"])) {
                    $caraStuffing = "4"; // Default 4 (FCL)
                }

                $jenisGudang = strval($kb['kodeJenisGudang'] ?? '');
                if (!in_array($jenisGudang, ["1", "2", "3", "4"])) {
                    $jenisGudang = "2"; // Default 2 (Gudang Pabrik)
                }

                $jenisBarang = strval($kb['kodeJenisBarang'] ?? '');
                if (!in_array($jenisBarang, ["1", "2"])) {
                    $jenisBarang = "1";
                }

                $jenisPartOf = strval($kb['kodeJenisPartOf'] ?? '');
                if (!in_array($jenisPartOf, ["1", "2", "", "NULL"])) {
                    $jenisPartOf = "1";
                }

                $kesiapanBarangList[] = [
                    'alamat'            => !empty($kb['alamat']) ? strval($kb['alamat']) : '-',
                    'kodeJenisBarang'   => $jenisBarang,
                    'kodeJenisGudang'   => $jenisGudang,
                    'lokasiSiapPeriksa' => !empty($kb['lokasiSiapPeriksa']) ? strval($kb['lokasiSiapPeriksa']) : '-',
                    'namaPic'           => !empty($kb['namaPic']) ? strval($kb['namaPic']) : '-',
                    'nomorTelpPic'      => !empty($kb['nomorTelpPic']) ? strval($kb['nomorTelpPic']) : '-',
                    'tanggalPkb'        => !empty($kb['tanggalPkb']) ? strval($kb['tanggalPkb']) : date('Y-m-d'),
                    'waktuSiapPeriksa'  => $waktu,
                    'kodeCaraStuffing'  => $caraStuffing,
                    'kodeJenisPartOf'   => $jenisPartOf,
                    'jumlahContainer20' => (int) ($kb['jumlahContainer20'] ?? 0),
                    'jumlahContainer40' => (int) ($kb['jumlahContainer40'] ?? 0),
                ];
            }
            if (empty($kesiapanBarangList)) {
                $kesiapanBarangList[] = [
                    'alamat'            => '-',
                    'kodeJenisBarang'   => '1',
                    'kodeJenisGudang'   => '2',
                    'lokasiSiapPeriksa' => '-',
                    'namaPic'           => '-',
                    'nomorTelpPic'      => '-',
                    'tanggalPkb'        => date('Y-m-d'),
                    'waktuSiapPeriksa'  => date('Y-m-d\TH:i:s'),
                    'kodeCaraStuffing'  => '4',
                    'kodeJenisPartOf'   => '1',
                    'jumlahContainer20' => 0,
                    'jumlahContainer40' => 0,
                ];
            }

            $payloadBarang = [];
            foreach (($draft['barang'] ?? []) as $index => $brg) {
                $barangTarif = [];
                foreach (($brg['barangTarif'] ?? []) as $tarif) {
                    $barangTarif[] = [
                        'kodeJenisTarif'     => $tarif['kodeJenisTarif'] ?? '1',
                        'jumlahSatuan'       => (float) ($tarif['jumlahSatuan'] ?? $brg['jumlahSatuan'] ?? 0),
                        'kodeFasilitasTarif' => $tarif['kodeFasilitasTarif'] ?? '3',
                        'kodeSatuanBarang'   => $tarif['kodeSatuanBarang'] ?? '',
                        'kodeJenisPungutan'  => $tarif['kodeJenisPungutan'] ?? 'BM',
                        'nilaiBayar'         => (float) ($tarif['nilaiBayar'] ?? 0),
                        'seriBarang'         => (int) ($brg['seriBarang'] ?? ($index + 1)),
                        'tarif'              => (float) ($tarif['tarif'] ?? 0),
                    ];
                }

                $barangDokumen = [];
                foreach (($brg['dokFasilitas'] ?? []) as $df) {
                    $barangDokumen[] = [
                        'kodeDokumen'    => $df['kodeDokumen'] ?? '',
                        'nomorDokumen'   => $df['nomorDokumen'] ?? '',
                        'tanggalDokumen' => $df['tanggalDokumen'] ?? '',
                        'kodeFasilitas'  => $df['kodeFasilitas'] ?? '',
                        'seriIjin'       => $df['seriIjin'] ?? '',
                    ];
                }

                $entitasBarang = [];
                foreach (($brg['entitasBarang'] ?? []) as $eb) {
                    $entitasBarang[] = [
                        'nomorIdentitas' => $eb['nomorIdentitas'] ?? '',
                        'namaEntitas'    => $eb['namaEntitas'] ?? '',
                        'alamatEntitas'  => $eb['alamatEntitas'] ?? '',
                    ];
                }

                $payloadBarang[] = [
                    'seriBarang'        => (int) ($brg['seriBarang'] ?? ($index + 1)),
                    'posTarif'          => strval($brg['posTarif'] ?? ''),
                    'kodeBarang'        => strval($brg['kodeBarang'] ?? ''),
                    'uraian'            => !empty($brg['uraian']) ? strval($brg['uraian']) : '-',
                    'merk'              => !empty($brg['merk']) ? strval($brg['merk']) : '-',
                    'tipe'              => !empty($brg['tipe']) ? strval($brg['tipe']) : '-',
                    'ukuran'            => !empty($brg['ukuran']) ? strval($brg['ukuran']) : '-',
                    'spesifikasiLain'   => !empty($brg['spesifikasiLain']) ? strval($brg['spesifikasiLain']) : '-',
                    'kodeNegaraAsal'    => !empty($brg['kodeNegaraAsal']) ? strval($brg['kodeNegaraAsal']) : 'ID',
                    'kodeDaerahAsal'    => strval($brg['kodeDaerahAsal'] ?? ''),
                    'jumlahSatuan'      => (float) ($brg['jumlahSatuan'] ?? 0),
                    'kodeSatuanBarang'  => strval($brg['kodeSatuanBarang'] ?? ''),
                    'jumlahKemasan'     => (float) ($brg['jumlahKemasan'] ?? 0),
                    'kodeJenisKemasan'  => !empty($brg['kodeJenisKemasan']) ? strval($brg['kodeJenisKemasan']) : 'CT',
                    'fob'               => (float) ($brg['fob'] ?? 0),
                    'netto'             => (float) ($brg['netto'] ?? 0),
                    'hargaEkspor'       => (float) ($brg['hargaEkspor'] ?? 0),
                    'hargaSatuan'       => (float) ($brg['hargaSatuan'] ?? 0),
                    'kodeJenisEkspor'   => !empty($brg['kodeJenisEkspor']) ? strval($brg['kodeJenisEkspor']) : (!empty($draft['kodeJenisEkspor']) ? strval($draft['kodeJenisEkspor']) : '1'),
                    'hargaPatokan'      => (float) ($brg['hargaPatokan'] ?? 0),
                    'kodeDokumen'       => '30',
                    'barangTarif'       => $barangTarif,
                    'dokFasilitas'      => $barangDokumen,
                    'entitasBarang'     => $entitasBarang,
                ];
            }

            $payloadPungutan = [];
            if (!empty($draft['pungutan']) && is_array($draft['pungutan'])) {
                foreach ($draft['pungutan'] as $p) {
                    if (isset($p['kodeJenisPungutan'])) {
                        $payloadPungutan[] = [
                            'kodeFasilitasTarif' => $p['kodeFasilitasTarif'] ?? '3',
                            'kodeJenisPungutan'  => $p['kodeJenisPungutan'],
                            'nilaiPungutan'      => (float) ($p['nilaiPungutan'] ?? 0),
                        ];
                    }
                }
            }

            $finalPayload = [
                'idPlatform'            => config('ceisa.id_platform_dev', ''),
                'asalData'              => 'S',
                'disclaimer'            => '1',
                'kodeDokumen'           => '30',
                'nomorAju'              => $nomorAju ?? '',
                'tanggalAju'            => $draft['tanggalAju'] ?? date('Y-m-d'),

                // Kode Kantor & Rute
                'kodeKantor'            => !empty($draft['kodeKantor']) ? strval($draft['kodeKantor']) : '050500',
                'kodeKantorEkspor'      => !empty($draft['kodeKantorEkspor']) ? strval($draft['kodeKantorEkspor']) : (!empty($draft['kodeKantor']) ? strval($draft['kodeKantor']) : '050500'),
                'kodeKantorMuat'        => !empty($draft['kodeKantorMuat']) ? strval($draft['kodeKantorMuat']) : (!empty($draft['kodeKantor']) ? strval($draft['kodeKantor']) : '050500'),
                'kodeKantorPeriksa'     => !empty($draft['kodeKantorPeriksa']) ? strval($draft['kodeKantorPeriksa']) : (!empty($draft['kodeKantor']) ? strval($draft['kodeKantor']) : '050500'),
                'kodePelEkspor'         => !empty($draft['kodePelEkspor']) ? strval($draft['kodePelEkspor']) : 'IDTPP',
                'kodePelMuat'           => !empty($draft['kodePelMuat']) ? strval($draft['kodePelMuat']) : 'IDTPP',
                'kodePelTujuan'         => !empty($draft['kodePelTujuan']) ? strval($draft['kodePelTujuan']) : 'MYPKG',
                'kodeLokasi'            => !empty($draft['kodeLokasi']) ? strval($draft['kodeLokasi']) : '2',
                'kodeTps'               => strval($draft['kodeTps'] ?? ''),
                'kodeNegaraTujuan'      => !empty($draft['kodeNegaraTujuan']) ? strval($draft['kodeNegaraTujuan']) : '',
                'kodeJenisPengangkutan' => !empty($draft['kodeJenisPengangkutan']) ? strval($draft['kodeJenisPengangkutan']) : '1',

                // Parameter Bisnis & Transaksi
                'kodeCaraDagang'        => !empty($draft['kodeCaraDagang']) ? strval($draft['kodeCaraDagang']) : '1',
                'kodeCaraBayar'         => !empty($draft['kodeCaraBayar']) ? strval($draft['kodeCaraBayar']) : '1',
                'kodePembayar'          => strval($draft['kodePembayar'] ?? ''),
                'kodeIncoterm'          => !empty($draft['kodeIncoterm']) ? strval($draft['kodeIncoterm']) : 'FOB',
                'kodeJenisEkspor'       => !empty($draft['kodeJenisEkspor']) ? strval($draft['kodeJenisEkspor']) : '1',
                'kodeKategoriEkspor'    => !empty($draft['kodeKategoriEkspor']) ? strval($draft['kodeKategoriEkspor']) : '1',
                'kodeValuta'            => !empty($draft['kodeValuta']) ? strval($draft['kodeValuta']) : 'USD',

                // Indikator Flag
                'flagBarkir'            => !empty($draft['flagBarkir']) ? strval($draft['flagBarkir']) : 'T',
                'flagCurah'             => !empty($draft['flagCurah']) ? strval($draft['flagCurah']) : '2',
                'flagMigas'             => !empty($draft['flagMigas']) ? strval($draft['flagMigas']) : '2',

                // Nilai & Ukuran
                'ndpbm'                 => (float) ($draft['ndpbm'] ?? 0),
                'cif'                   => (float) ($draft['cif'] ?? 0),
                'asuransi'              => (float) ($draft['asuransi'] ?? 0),
                'kodeAsuransi'          => !empty($draft['kodeAsuransi']) ? strval($draft['kodeAsuransi']) : 'LN',
                'freight'               => (float) ($draft['freight'] ?? 0),
                'fob'                   => (float) ($draft['fob'] ?? 0),
                'bruto'                 => (float) ($draft['bruto'] ?? 0),
                'netto'                 => (float) ($draft['netto'] ?? 0),
                'nilaiMaklon'           => (float) ($draft['nilaiMaklon'] ?? 0),
                'nilaiPph'              => (float) ($draft['nilaiPph'] ?? 0),
                'totalDanaSawit'        => (float) ($draft['totalDanaSawit'] ?? 0),
                'jumlahKontainer'       => count($payloadKontainer),

                // Waktu & Penandatangan
                'tanggalEkspor'         => !empty($draft['tanggalEkspor']) ? strval($draft['tanggalEkspor']) : date('Y-m-d'),
                'tanggalPeriksa'        => !empty($draft['tanggalPeriksa']) ? strval($draft['tanggalPeriksa']) : date('Y-m-d'),
                'tanggalTtd'            => !empty($draft['tanggalTtd']) ? strval($draft['tanggalTtd']) : date('Y-m-d'),
                'namaTtd'               => !empty($draft['namaTtd']) ? strval($draft['namaTtd']) : 'nama_ttd',
                'jabatanTtd'            => !empty($draft['jabatanTtd']) ? strval($draft['jabatanTtd']) : 'jabatan_ttd',
                'kotaTtd'               => !empty($draft['kotaTtd']) ? strval($draft['kotaTtd']) : 'kota_ttd',

                // Memasukkan Hasil Pemetaan Array
                'entitas'               => $payloadEntitas,
                'dokumen'               => $payloadDokumen,
                'pengangkut'            => $payloadPengangkut,
                'kontainer'             => $payloadKontainer,
                'kemasan'               => $payloadKemasan,
                'bankDevisa'            => $payloadBankDevisa,
                'pungutan'              => $payloadPungutan,
                'kesiapanBarang'        => $kesiapanBarangList,
                'barang'                => $payloadBarang,
            ];

            $responseCeisa = $this->ceisaService->kirimDokumenBc30($finalPayload);

            if ($responseCeisa['successful']) {
                foreach ($bppbs as $no_bppb) {
                    $db->table('bpb_ceisa')->where('bpbno', $no_bppb)->update([
                        'nomor_aju'   => $nomorAju,
                        'tanggal_aju' => $ceisaInfo->tanggal_aju ?? $header->tanggal_aju ?? date('Y-m-d'),
                        'status'      => 1,
                        'jenis_bc'    => '3.0',
                        'updated_at'  => \Carbon\Carbon::now()
                    ]);
                }

                return response()->json([
                    'status'         => 200,
                    'message'        => 'Dokumen BC 3.0 berhasil dikirim ke CEISA!',
                    'data_payload'   => $finalPayload,
                    'ceisa_response' => $responseCeisa['body'],
                ]);
            } else {
                return response()->json([
                    'status'      => $responseCeisa['status_code'],
                    'message'     => 'Gagal mengirim ke CEISA.',
                    'ceisa_error' => $responseCeisa['body'],
                ], $responseCeisa['status_code']);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => $e->getMessage() . ' di baris ' . $e->getLine(),
            ], 500);
        }
    }
}
