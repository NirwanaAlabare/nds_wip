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

        $ceisaInfo = $db->table('bpb_ceisa')->where('bpbno', $id)->first();
        $dataDetail = json_decode($ceisaInfo->payload_json ?? '{}', true);

        $items = $db->table('bppb as a')
            ->join('masteritem as mi', 'a.id_item', '=', 'mi.id_item')
            ->select('a.*', 'mi.goods_code', 'mi.itemdesc')
            ->where(function ($query) use ($id) {
                $query->where('a.bppbno', $id)->orWhere('a.bppbno_int', $id);
            })
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
        ]);
    }

    // GENERATE NOMOR AJU BC 3.0
    private function generateNomorAju($db)
    {
        $currentYear = date('Y');
        $today       = date('Ymd');
        $prefix      = '000030NIW77920';

        $lastCeisa = $db->table('bpb_ceisa')
            ->where('nomor_aju', 'like', $prefix . $currentYear . '%')
            ->where('jenis_bc', '3.0')
            ->orderBy('nomor_aju', 'desc')
            ->first();

        if ($lastCeisa && $lastCeisa->nomor_aju && strlen($lastCeisa->nomor_aju) === 26) {
            $lastSeq = (int) substr($lastCeisa->nomor_aju, -6);
            $nextSeq = str_pad($lastSeq + 1, 6, '0', STR_PAD_LEFT);
            return $prefix . $today . $nextSeq;
        }

        return $prefix . $today . '000001';
    }

    // UPDATE DRAFT BC 3.0
    public function updateDraft($id, Request $request)
    {
        DB::connection('mysql_sb')->beginTransaction();

        try {
            // --- 1. Dokumen Pendukung ---
            // $dokumenInput = $request->input('dok', []);
            // $dokumenList  = [];
            // $seriDok = 1;
            // foreach ($dokumenInput as $d) {
            //     if (!empty($d['kode']) || !empty($d['nomor'])) {
            //         $dokumenList[] = [
            //             'seriDokumen'    => $seriDok++,
            //             'kodeDokumen'    => $d['kode'] ?? '',
            //             'nomorDokumen'   => $d['nomor'] ?? '',
            //             'tanggalDokumen' => $d['tgl'] ?? date('Y-m-d'),
            //         ];
            //     }
            // }

            // ... (di dalam try { ) ...

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
                    'jenis_bc'     => '30',
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

            $payloadDokumen = [];
            foreach (($draft['dokumen'] ?? []) as $d) {
                $payloadDokumen[] = [
                    'seriDokumen'    => (int) ($d['seriDokumen'] ?? 1),
                    'kodeDokumen'    => $d['kodeDokumen'] ?? '',
                    'nomorDokumen'   => $d['nomorDokumen'] ?? '',
                    'tanggalDokumen' => $d['tanggalDokumen'] ?? '',
                ];
            }

            $hasInvoice = false;
            foreach ($payloadDokumen as $dok) {
                if ($dok['kodeDokumen'] === '380') {
                    $hasInvoice = true;
                    break;
                }
            }
            if (!$hasInvoice) {
                throw new \Exception('Validasi Gagal: Dokumen BC 3.0 wajib melampirkan INVOICE (Kode 380). Silakan tambahkan terlebih dahulu di Tab Dokumen Pelengkap.');
            }

            $payloadKemasan = [];
            foreach (($draft['kemasan'] ?? []) as $k) {
                $payloadKemasan[] = [
                    'seriKemasan'      => (int) ($k['seriKemasan'] ?? 1),
                    'jumlahKemasan'    => (int) ($k['jumlahKemasan'] ?? 0),
                    'kodeJenisKemasan' => $k['kodeJenisKemasan'] ?? '',
                    'merkKemasan'      => $k['merkKemasan'] ?? '-',
                ];
            }

            $payloadKontainer = [];
            foreach (($draft['kontainer'] ?? []) as $k) {
                $payloadKontainer[] = [
                    'seriKontainer'       => (int) ($k['seriKontainer'] ?? 1),
                    'nomorKontainer'      => strtoupper($k['nomorKontainer'] ?? ''),
                    'kodeJenisKontainer'  => $k['kodeJenisKontainer'] ?? '',
                    'kodeTipeKontainer'   => $k['kodeTipeKontainer'] ?? '',
                    'kodeUkuranKontainer' => $k['kodeUkuranKontainer'] ?? '',
                ];
            }

            $payloadPengangkut = [];
            foreach (($draft['pengangkut'] ?? []) as $p) {
                $payloadPengangkut[] = [
                    'seriPengangkut'  => (int) ($p['seriPengangkut'] ?? 1),
                    'kodeBendera'     => $p['kodeBendera'] ?? '',
                    'namaPengangkut'  => $p['namaPengangkut'] ?? '',
                    'nomorPengangkut' => $p['nomorPengangkut'] ?? '',
                    'kodeCaraAngkut'  => $p['kodeCaraAngkut'] ?? '',
                ];
            }

            $payloadBankDevisa = [];
            foreach (($draft['bankDevisa'] ?? []) as $b) {
                $payloadBankDevisa[] = [
                    'seriBank' => (int) ($b['seriBank'] ?? 1),
                    'kodeBank' => $b['kodeBank'] ?? '',
                    'namaBank' => $b['namaBank'] ?? '',
                ];
            }

            $entitasDraft = $draft['entitas'] ?? [];
            $eksportir    = array_filter($entitasDraft, function($e) { return ($e['kodeEntitas'] ?? '') == '2'; });
            $pemilik      = array_filter($entitasDraft, function($e) { return ($e['kodeEntitas'] ?? '') == '7'; });
            $penerima     = array_filter($entitasDraft, function($e) { return ($e['kodeEntitas'] ?? '') == '8'; });
            $pembeli      = array_filter($entitasDraft, function($e) { return ($e['kodeEntitas'] ?? '') == '6'; });
            $konsolidator = array_filter($entitasDraft, function($e) { return ($e['kodeEntitas'] ?? '') == '23'; });

            $payloadEntitas = [];
            $seriEnt = 1;

            $eks = reset($eksportir);
            if ($eks) {
                $eks['seriEntitas'] = $seriEnt++;
                $payloadEntitas[] = $eks;
            }

            $pem = reset($pemilik);
            if ($pem) {
                if (empty($pem['kodeJenisIdentitas'])) $pem['kodeJenisIdentitas'] = '5'; // Fix default Enum Error
                $pem['seriEntitas'] = $seriEnt++;
                $payloadEntitas[] = $pem;
            } else if ($eks) {
                $pemFallback = $eks;
                $pemFallback['kodeEntitas'] = '7';
                $pemFallback['seriEntitas'] = $seriEnt++;
                $payloadEntitas[] = $pemFallback;
            }

            $pen = reset($penerima);
            if ($pen) {
                $pen['seriEntitas'] = $seriEnt++;
                $payloadEntitas[] = $pen;
            }

            $bel = reset($pembeli);
            if ($bel) {
                $bel['seriEntitas'] = $seriEnt++;
                $payloadEntitas[] = $bel;
            }

            $kon = reset($konsolidator);
            if ($kon && !empty($kon['nomorIdentitas'])) {
                $kon['seriEntitas'] = $seriEnt++;
                $payloadEntitas[] = $kon;
            }

            $kesiapanBarangList = [];
            foreach (($draft['kesiapanBarang'] ?? []) as $kb) {
                $waktuStr = $kb['waktuSiapPeriksa'] ?? date('Y-m-d\TH:i');
                $waktu = \Carbon\Carbon::parse($waktuStr)->format('Y-m-d\TH:i:s');

                $kesiapanBarangList[] = [
                    'alamat'            => $kb['alamat'] ?? '',
                    'kodeJenisBarang'   => $kb['kodeJenisBarang'] ?? '1',
                    'kodeJenisGudang'   => $kb['kodeJenisGudang'] ?? '2',
                    'lokasiSiapPeriksa' => $kb['lokasiSiapPeriksa'] ?? '',
                    'namaPic'           => $kb['namaPic'] ?? '',
                    'nomorTelpPic'      => $kb['nomorTelpPic'] ?? '',
                    'tanggalPkb'        => $kb['tanggalPkb'] ?? date('Y-m-d'),
                    'waktuSiapPeriksa'  => $waktu,
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
                    'posTarif'          => $brg['posTarif'] ?? '',
                    'kodeBarang'        => $brg['kodeBarang'] ?? '',
                    'uraian'            => $brg['uraian'] ?? '',
                    'merk'              => $brg['merk'] ?? '-',
                    'tipe'              => $brg['tipe'] ?? '-',
                    'ukuran'            => $brg['ukuran'] ?? '-',
                    'spesifikasiLain'   => $brg['spesifikasiLain'] ?? '-',
                    'kodeNegaraAsal'    => $brg['kodeNegaraAsal'] ?? 'ID',
                    'kodeDaerahAsal'    => $brg['kodeDaerahAsal'] ?? '',
                    'jumlahSatuan'      => (float) ($brg['jumlahSatuan'] ?? 0),
                    'kodeSatuanBarang'  => $brg['kodeSatuanBarang'] ?? '',
                    'jumlahKemasan'     => (float) ($brg['jumlahKemasan'] ?? 0),
                    'kodeJenisKemasan'  => $brg['kodeJenisKemasan'] ?? '',
                    'fob'               => (float) ($brg['fob'] ?? 0),
                    'netto'             => (float) ($brg['netto'] ?? 0),
                    'hargaEkspor'       => (float) ($brg['hargaEkspor'] ?? 0),
                    'hargaSatuan'       => (float) ($brg['hargaSatuan'] ?? 0),
                    'kodeJenisEkspor'   => $brg['kodeJenisEkspor'] ?? '1',
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
                'kodeKantor'            => $draft['kodeKantor'] ?? '050500',
                'kodeKantorEkspor'      => $draft['kodeKantorEkspor'] ?? '',
                'kodeKantorMuat'        => $draft['kodeKantorMuat'] ?? '',
                'kodeKantorPeriksa'     => $draft['kodeKantorPeriksa'] ?? '',
                'kodePelEkspor'         => $draft['kodePelEkspor'] ?? '',
                'kodePelMuat'           => $draft['kodePelMuat'] ?? '',
                'kodePelTujuan'         => $draft['kodePelTujuan'] ?? '',
                'kodeLokasi'            => $draft['kodeLokasi'] ?? '',
                'kodeTps'               => $draft['kodeTps'] ?? '',
                'kodeNegaraTujuan'      => $draft['kodeNegaraTujuan'] ?? '',
                'kodeJenisPengangkutan' => $draft['kodeJenisPengangkutan'] ?? '',

                // Parameter Bisnis & Transaksi
                'kodeCaraDagang'        => $draft['kodeCaraDagang'] ?? '',
                'kodeCaraBayar'         => $draft['kodeCaraBayar'] ?? '',
                'kodeIncoterm'          => $draft['kodeIncoterm'] ?? '',
                'kodeJenisEkspor'       => $draft['kodeJenisEkspor'] ?? '',
                'kodeKategoriEkspor'    => $draft['kodeKategoriEkspor'] ?? '',
                'kodeValuta'            => $draft['kodeValuta'] ?? 'IDR',

                // Indikator Flag
                'flagBarkir'            => $draft['flagBarkir'] ?? 'T',
                'flagCurah'             => $draft['flagCurah'] ?? '2',
                'flagMigas'             => $draft['flagMigas'] ?? '2',

                //
                'ndpbm'                 => (float) ($draft['ndpbm'] ?? 0),
                'cif'                   => (float) ($draft['cif'] ?? 0),
                'asuransi'              => (float) ($draft['asuransi'] ?? 0),
                'kodeAsuransi'          => $draft['kodeAsuransi'] ?? 'LN',
                'freight'               => (float) ($draft['freight'] ?? 0),
                'fob'                   => (float) ($draft['fob'] ?? 0),
                'bruto'                 => (float) ($draft['bruto'] ?? 0),
                'netto'                 => (float) ($draft['netto'] ?? 0),
                'nilaiMaklon'           => (float) ($draft['nilaiMaklon'] ?? 0),
                'nilaiPph'              => (float) ($draft['nilaiPph'] ?? 0),
                'totalDanaSawit'        => (float) ($draft['totalDanaSawit'] ?? 0),
                'jumlahKontainer'       => count($payloadKontainer),

                // Waktu & Penandatangan
                'tanggalEkspor'         => $draft['tanggalEkspor'] ?? date('Y-m-d'),
                'tanggalPeriksa'        => $draft['tanggalPeriksa'] ?? date('Y-m-d'),
                'tanggalTtd'            => $draft['tanggalTtd'] ?? date('Y-m-d'),
                'namaTtd'               => $draft['namaTtd'] ?? '',
                'jabatanTtd'            => $draft['jabatanTtd'] ?? '',
                'kotaTtd'               => $draft['kotaTtd'] ?? '',

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
}
