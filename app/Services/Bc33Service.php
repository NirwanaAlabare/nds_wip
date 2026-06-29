<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Bc33Service
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

        // Referensi data untuk view
        $listJenisKemasan = [
            'CT' => 'Carton', 'BG' => 'Bag', 'BX' => 'Box', 'DR' => 'Drum',
            'PK' => 'Package', 'PL' => 'Pallet', 'RO' => 'Roll', 'SA' => 'Sack',
            'CS' => 'Case', 'TK' => 'Tank', 'CN' => 'Container', 'OT' => 'Other',
        ];
        $listSatuanBarang = [
            'PCS' => 'Pieces', 'KGM' => 'Kilogram', 'MTR' => 'Meter',
            'LTR' => 'Liter',  'SET' => 'Set',       'M2'  => 'Square Meter',
            'M3'  => 'Cubic Meter', 'TNE' => 'Tonne', 'UNT' => 'Unit',
            'DZN' => 'Dozen',  'BOX' => 'Box',       'BAG' => 'Bag',
            'ROL' => 'Roll',   'PRS' => 'Pairs',
        ];

        // Referensi dokumen lampiran
        $referensiDokumen = [
            '380' => 'INVOICE', '271' => 'PACKING LIST', '750' => 'SURAT KETERANGAN ASAL',
            '787' => 'PEB',     '856' => 'SURAT JALAN',  '820' => 'BUKTI BAYAR',
            '325' => 'BUKTI PENYERAHAN', '126' => 'BILL OF LADING / AWB',
            '235' => 'CERTIFICATE OF ORIGIN', '703' => 'IJIN EKSPOR',
        ];

        // Referensi dokumen asal barang (BC pemasukan ke PLB) - kode numerik CEISA 4.0
        $referensiDokumenAsal = [
            '24'  => 'BC 2.4',
            '27'  => 'BC 2.7',
            '40'  => 'BC 4.0',
            '522' => 'PPFTZ-02 - PENGELUARAN KE KAWASAN BERIKAT, KEK, FTZ LAIN, ATAU KA...',
            '621' => 'KEK - PENGELUARAN KE KEK/TPB/FTZP',
            '999' => 'LAINNYA',
        ];

        // Kode TPS mapping
        $mapNamaTps = [];
        if (!empty($dataDetail['kodeLokasiTps'])) {
            $mapNamaTps[$dataDetail['kodeLokasiTps']] = $dataDetail['kodeLokasiTps'];
        }
        $kodeLokasiTps      = $dataDetail['kodeLokasiTps'] ?? '';
        $kodeLokasiTpsLabel = $kodeLokasiTps;

        // Pengangkut & kontainer
        $pengangkuts = $dataDetail['pengangkut'] ?? [];
        $kontainers  = $dataDetail['kontainer']  ?? [];

        return view('export-import.dokumen-pabean.edit-bc33', [
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
            'listJenisKemasan'      => $listJenisKemasan,
            'listSatuanBarang'      => $listSatuanBarang,
            'referensiDokumen'      => $referensiDokumen,
            'referensiDokumenAsal'  => $referensiDokumenAsal,
            'mapNamaTps'            => $mapNamaTps,
            'kodeLokasiTps'         => $kodeLokasiTps,
            'kodeLokasiTpsLabel'    => $kodeLokasiTpsLabel,
            'pengangkuts'           => $pengangkuts,
            'kontainers'            => $kontainers,
        ]);
    }

    // GENERATE NOMOR AJU BC 3.3
    private function generateNomorAju($db)
    {
        $currentYear = date('Y');
        $today       = date('Ymd');
        $prefix      = '000033NIW779';

        $lastCeisa = $db->table('bpb_ceisa')
            ->where('nomor_aju', 'like', $prefix . $currentYear . '%')
            ->where('jenis_bc', '3.3')
            ->orderBy('nomor_aju', 'desc')
            ->first();

        $localSeq = 0;
        if ($lastCeisa && $lastCeisa->nomor_aju && strlen($lastCeisa->nomor_aju) === 26) {
            $localSeq = (int) substr($lastCeisa->nomor_aju, -6);
        }

        $ceisaSeq = $this->ceisaService->getLastSequenceFromCeisa($prefix . $currentYear, '33');

        $maxSeq  = max($localSeq, $ceisaSeq);
        $nextSeq = str_pad($maxSeq + 1, 6, '0', STR_PAD_LEFT);

        return $prefix . $today . $nextSeq;
    }

    // UPDATE DRAFT BC 3.3
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

            // --- 6. Entitas ---
            // entitas[2]=Eksportir, entitas[3]=PLB, entitas[4]=PPJK,
            // entitas[6]=Pembeli, entitas[7]=Pemilik(array), entitas[8]=Penerima, entitas[23]=Konsolidator
            $entitasInput = $request->input('entitas', []);
            $entitasList  = [];

            foreach ($entitasInput as $kodeEntitas => $e) {
                // entitas[7] adalah array pemilik barang (multi-row)
                if ($kodeEntitas == '7' && isset($e[0])) {
                    foreach ($e as $idx => $pem) {
                        if (!empty($pem['nomorIdentitas']) || !empty($pem['namaEntitas'])) {
                            $entitasList[] = [
                                'seriEntitas'        => (int) ($pem['seriEntitas'] ?? ($idx + 1)),
                                'kodeEntitas'        => '7',
                                'kodeJenisIdentitas' => $pem['kodeJenisIdentitas'] ?? '6',
                                'nomorIdentitas'     => $pem['nomorIdentitas'] ?? '',
                                'namaEntitas'        => $pem['namaEntitas'] ?? '',
                                'alamatEntitas'      => $pem['alamatEntitas'] ?? '',
                                'kodeNegara'         => $pem['kodeNegara'] ?? '',
                                'kodeStatus'         => $pem['kodeStatus'] ?? '',
                            ];
                        }
                    }
                } else {
                    // Entitas tunggal (2, 3, 4, 6, 8, 23, dll)
                    $entitasList[] = [
                        'seriEntitas'        => (int) ($e['seriEntitas'] ?? 0),
                        'kodeEntitas'        => (string) $kodeEntitas,
                        'kodeJenisIdentitas' => $e['kodeJenisIdentitas'] ?? '',
                        'nomorIdentitas'     => $e['nomorIdentitas'] ?? '',
                        'namaEntitas'        => $e['namaEntitas'] ?? '',
                        'alamatEntitas'      => $e['alamatEntitas'] ?? '',
                        'kodeNegara'         => $e['kodeNegara'] ?? '',
                        'nibEntitas'         => $e['nibEntitas'] ?? '',
                        'kodeStatus'         => $e['kodeStatus'] ?? '',
                        'nitku'              => $e['nitku'] ?? '',
                        'nomorIjinEntitas'   => $e['nomorIjinEntitas'] ?? '',
                        'tanggalIjinEntitas' => $e['tanggalIjinEntitas'] ?? '',
                        'kodeKategoriKonsolidator' => $e['kodeKategoriKonsolidator'] ?? '',
                    ];
                }
            }

            // --- 7. Pungutan ---
            $pungutanInput = $request->input('pungutan', []);
            $pungutan = [];
            foreach ($pungutanInput as $p) {
                if (!empty($p['kodePungutan'])) {
                    $pungutan[] = [
                        'kodePungutan' => $p['kodePungutan'] ?? '',
                        'dibayar'      => (float) ($p['dibayar'] ?? 0),
                    ];
                }
            }

            // --- 8. Kesiapan Barang (PKB) ---
            $kesiapanBarangList = array_values($request->input('kesiapanBarang', []));

            // --- 9. Barang ---
            $barangList = array_map(function ($brg) {
                $brg['fob']           = (float) ($brg['fob'] ?? 0);
                $brg['nilaiBarang']   = (float) ($brg['nilaiBarang'] ?? $brg['hargaEkspor'] ?? 0);
                $brg['hargaSatuan']   = (float) ($brg['hargaSatuan'] ?? 0);
                $brg['hargaPatokan']  = (float) ($brg['hargaPatokan'] ?? 0);
                $brg['netto']         = (float) ($brg['netto'] ?? 0);
                $brg['jumlahSatuan']  = (float) ($brg['jumlahSatuan'] ?? 0);
                $brg['jumlahKemasan'] = (float) ($brg['jumlahKemasan'] ?? 0);
                $brg['seriBarang']    = (int) ($brg['seriBarang'] ?? 0);
                $brg['kodeDokumen']   = '33';

                // Format Tarif Barang (Jika Ada)
                if (isset($brg['barangTarif']) && is_array($brg['barangTarif'])) {
                    foreach ($brg['barangTarif'] as &$tarif) {
                        $tarif['jumlahSatuan'] = (float) ($brg['jumlahSatuan'] ?? 0);
                        $tarif['tarif']        = (float) ($tarif['tarif'] ?? 0);
                    }
                }

                // Bersihkan data array kosong
                if (isset($brg['dokFasilitas'])) $brg['dokFasilitas'] = array_values($brg['dokFasilitas']);
                if (isset($brg['entitasBarang'])) $brg['entitasBarang'] = array_values($brg['entitasBarang']);

                return $brg;
            }, array_values($request->input('barang', [])));

            $payloadJson = [
                'asalData'              => 'S',
                'disclaimer'            => '1',
                'kodeDokumen'           => '33',

                // Header Dokumen
                'nomorAju'              => $request->input('nomorAju', ''),
                'tanggalAju'            => $request->input('tanggalAju', date('Y-m-d')),
                'kodeKantor'            => $request->input('kodeKantor', '050500'),
                'kodeKantorMuat'        => $request->input('kodeKantorMuat', ''),
                'kodeKantorAsal'        => $request->input('kodeKantorAsal', ''),
                'kodeKantorEkspor'      => $request->input('kodeKantorEkspor', $request->input('kodeKantorMuat', $request->input('kodeKantor', '050500'))),
                '_kodeKantorSKEP'       => $request->input('_kodeKantorSKEP', $request->input('kodeKantorAsal', $request->input('kodeKantor', '050500'))),
                'idPkbe'                => $request->input('idPkbe', $id),

                // Jenis & Prosedur
                'kodeJenisProsedur'     => $request->input('kodeJenisProsedur', ''),
                'kodeJenisEkspor'       => $request->input('kodeJenisEkspor', ''),
                'kodeKategoriEkspor'    => $request->input('kodeKategoriEkspor', ''),
                'kodeCaraDagang'        => $request->input('kodeCaraDagang', ''),
                'kodeCaraBayar'         => $request->input('kodeCaraBayar', ''),

                // Pelabuhan & Pengangkutan
                'kodePelMuatAsal'       => $request->input('kodePelMuatAsal', ''),
                'kodePelMuat'           => $request->input('kodePelMuat', ''),
                'kodePelBongkar'        => $request->input('kodePelBongkar', ''),
                'kodePelTujuan'         => $request->input('kodePelTujuan', ''),
                'kodeGudangAsal'        => $request->input('kodeGudangAsal', ''),
                'kodeGudangPlb'         => $request->input('kodeGudangPlb', ''),
                'kodeCaraAngkutPlb'     => $request->input('kodeCaraAngkutPlb', ''),
                'tanggalMasukPlb'       => $request->input('tanggalMasukPlb', ''),
                'tanggalMasuk'          => $request->input('tanggalMasuk', ''),
                'kodeLokasiTps'         => $request->input('kodeLokasiTps', ''),
                'kodeTps'               => $request->input('kodeTps', $request->input('kodeLokasiTps', '')),
                'kodeNegaraTujuan'      => $request->input('kodeNegaraTujuan', ''),
                'tanggalMuat'           => $request->input('tanggalMuat', ''),

                // Nilai, Keuangan
                'kodeDaerahAsal'        => $request->input('kodeDaerahAsal', ''),
                'kodeIncoterm'          => $request->input('kodeIncoterm', ''),
                'kodeValuta'            => $request->input('kodeValuta', 'IDR'),
                'ndpbm'                 => (float) $request->input('ndpbm', 0),
                'fob'                   => (float) $request->input('fob', 0),
                'nilaiBarang'           => (float) $request->input('nilaiBarang', 0),
                'freight'               => (float) $request->input('freight', 0),
                'asuransi'              => (float) $request->input('asuransi', 0),
                'kodeAsuransi'          => $request->input('kodeAsuransi', 'DN'),
                'cif'                   => (float) $request->input('cif', 0),
                'bruto'                 => (float) $request->input('bruto', 0),
                'netto'                 => (float) $request->input('netto', 0),
                'nilaiMaklon'           => (float) $request->input('nilaiMaklon', 0),
                'nilaiPph'              => (float) $request->input('nilaiPph', 0),
                'totalDanaSawit'        => (float) $request->input('totalDanaSawit', 0),
                'jumlahKontainer'       => count($kontainerList),

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
                'dok'                   => $dokumenList,
                'kemasan'               => $kemasanList,
                'kontainer'             => $kontainerList,
                'pengangkut'            => $pengangkutList,
                'bankDevisa'            => $bankDevisaList,
                'pungutan'              => $pungutan,
                'kesiapanBarang'        => $kesiapanBarangList,
                'barang'                => $barangList,
            ];

            // --- Update Database ---
            DB::connection('mysql_sb')->table('bpb_ceisa')->updateOrInsert(
                ['bpbno' => $id],
                [
                    'tanggal_aju'  => $request->input('tanggalAju', date('Y-m-d')),
                    'nomor_aju'    => $request->input('nomorAju'),
                    'payload_json' => json_encode($payloadJson),
                    'jenis_bc'     => '33',
                    'updated_at'   => date('Y-m-d H:i:s'),
                    'bpbno_int'    => $request->input('bppbno_int') ?? null,
                ]
            );

            DB::connection('mysql_sb')->commit();

            return back()->with('success', 'Data draft BC 3.3 berhasil disimpan!');

        } catch (\Exception $e) {
            DB::connection('mysql_sb')->rollBack();
            Log::error('Error Update Draft BC 3.3: ' . $e->getMessage() . ' di baris ' . $e->getLine());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
        }
    }

    // SEND CEISA BC 3.3
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

            // Ambil semua item bppb untuk fallback data Dokumen Asal per barang
            $bppbItems = $db->table('bppb as a')
                ->where(function ($query) use ($id) {
                    $query->where('a.bppbno', $id)->orWhere('a.bppbno_int', $id);
                })
                ->select('a.bcno_in', 'a.tgl_bc_in', 'a.kpno', 'a.no_urut')
                ->get()
                ->toArray();


            $payloadDokumen = [];
            foreach (($draft['dok'] ?? []) as $d) {
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
                throw new \Exception('Validasi Gagal: Dokumen BC 3.3 wajib melampirkan INVOICE (Kode 380). Silakan tambahkan terlebih dahulu di Tab Dokumen Pelengkap.');
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
            // Normalisasi: bisa berupa array flat atau array indexed
            if (!empty($entitasDraft) && isset($entitasDraft[0])) {
                // array flat (sudah list)
                $eksportir    = array_filter($entitasDraft, fn($e) => ($e['kodeEntitas'] ?? '') == '2');
                $plb          = array_filter($entitasDraft, fn($e) => ($e['kodeEntitas'] ?? '') == '3');
                $ppjk         = array_filter($entitasDraft, fn($e) => ($e['kodeEntitas'] ?? '') == '4');
                $pemilik      = array_filter($entitasDraft, fn($e) => ($e['kodeEntitas'] ?? '') == '7');
                $penerima     = array_filter($entitasDraft, fn($e) => ($e['kodeEntitas'] ?? '') == '8');
                $pembeli      = array_filter($entitasDraft, fn($e) => ($e['kodeEntitas'] ?? '') == '6');
                $konsolidator = array_filter($entitasDraft, fn($e) => ($e['kodeEntitas'] ?? '') == '23');
            } else {
                // array indexed by kodeEntitas
                $eksportir    = isset($entitasDraft[2])  ? [$entitasDraft[2]]  : [];
                $plb          = isset($entitasDraft[3])  ? [$entitasDraft[3]]  : [];
                $ppjk         = isset($entitasDraft[4])  ? [$entitasDraft[4]]  : [];
                $pemilik      = isset($entitasDraft[7])  ? (isset($entitasDraft[7][0]) ? $entitasDraft[7] : [$entitasDraft[7]]) : [];
                $penerima     = isset($entitasDraft[8])  ? [$entitasDraft[8]]  : [];
                $pembeli      = isset($entitasDraft[6])  ? [$entitasDraft[6]]  : [];
                $konsolidator = isset($entitasDraft[23]) ? [$entitasDraft[23]] : [];
            }

            $payloadEntitas = [];
            $seriEnt = 1;

            // Aturan Urutan Index Skema CEISA BC 3.3:
            // [0] Eksportir (2), [1] PLB (3), [2] Pembeli (6), [3] Penerima (8), dilanjutkan PPJK (4), Pemilik (7), Konsolidator (23)

            // 1. Eksportir (kodeEntitas=2)
            $eks = reset($eksportir);
            if ($eks) {
                $eks['seriEntitas'] = $seriEnt++;
                $payloadEntitas[] = $eks;
            }

            // 2. PLB (kodeEntitas=3)
            $plbEnt = reset($plb);
            if ($plbEnt && !empty($plbEnt['nomorIdentitas'])) {
                $plbEnt['seriEntitas'] = $seriEnt++;
                $payloadEntitas[] = $plbEnt;
            }

            // 3. Pembeli (kodeEntitas=6)
            $bel = reset($pembeli);
            if ($bel) {
                $bel['seriEntitas'] = $seriEnt++;
                $payloadEntitas[] = $bel;
            }

            // 4. Penerima (kodeEntitas=8)
            $pen = reset($penerima);
            if ($pen) {
                $pen['seriEntitas'] = $seriEnt++;
                $payloadEntitas[] = $pen;
            }

            // 5. PPJK (kodeEntitas=4)
            $ppjkEnt = reset($ppjk);
            if ($ppjkEnt && !empty($ppjkEnt['nomorIdentitas'])) {
                $ppjkEnt['seriEntitas'] = $seriEnt++;
                $payloadEntitas[] = $ppjkEnt;
            }

            // 6. Pemilik barang (kodeEntitas=7)
            foreach ($pemilik as $pem) {
                if (empty($pem['kodeJenisIdentitas'])) $pem['kodeJenisIdentitas'] = '5';
                $pem['seriEntitas']  = $seriEnt++;
                $pem['kodeEntitas']  = '7';
                $payloadEntitas[] = $pem;
            }
            // Fallback: salin eksportir jika tidak ada pemilik
            if (empty($pemilik) && $eks) {
                $pemFallback = $eks;
                $pemFallback['kodeEntitas'] = '7';
                $pemFallback['seriEntitas'] = $seriEnt++;
                $payloadEntitas[] = $pemFallback;
            }

            // 7. Konsolidator (kodeEntitas=23)
            $kon = reset($konsolidator);
            if ($kon && !empty($kon['nomorIdentitas'])) {
                $kon['seriEntitas'] = $seriEnt++;
                $payloadEntitas[] = $kon;
            }

            // Membersihkan tanggal / nomor izin yang kosong agar tidak error validasi format date
            foreach ($payloadEntitas as &$ent) {
                if (empty($ent['tanggalIjinEntitas'])) {
                    if (!empty($ent['nomorIjinEntitas'])) {
                        // Jika ada nomorIjinEntitas tapi tanggalIjinEntitas kosong, berikan fallback tanggalAju agar tidak merah di portal CEISA
                        $ent['tanggalIjinEntitas'] = $draft['tanggalAju'] ?? date('Y-m-d');
                    } else {
                        unset($ent['tanggalIjinEntitas']);
                    }
                }
                if (isset($ent['nomorIjinEntitas']) && empty($ent['nomorIjinEntitas'])) {
                    unset($ent['nomorIjinEntitas']);
                }
            }
            unset($ent);

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
                    'kodeCaraStuffing'  => $kb['kodeCaraStuffing'] ?? '',
                    'kodeJenisPartOf'   => $kb['kodeJenisPartOf'] ?? '',
                    'jumlahContainer20' => (int) ($kb['jumlahContainer20'] ?? 0),
                    'jumlahContainer40' => (int) ($kb['jumlahContainer40'] ?? 0),
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

                $itemBarang = [
                    'seriBarang'           => (int) ($brg['seriBarang'] ?? ($index + 1)),
                    'kodeDokumen'          => '33',

                    // --- Dokumen Asal (nama field sesuai CEISA API) ---
                    'kodeKantorAsal'       => !empty($brg['kodeKantor'])
                                                ? $brg['kodeKantor']
                                                : ($draft['kodeKantor'] ?? '050500'),
                    'kodeDokAsal'          => $brg['kodeJenisDokAsal'] ?? $brg['dokumenAsal'] ?? '',
                    'nomorAjuDokAsal'      => $brg['nomorPengajuan'] ?? '',
                    'nomorDaftarDokAsal'   => !empty($brg['nomorDaftar'])
                                                ? $brg['nomorDaftar']
                                                : ($bppbItems[$index]->bcno_in ?? $bppbItems[$index]->kpno ?? ''),
                    'tanggalDaftarDokAsal' => !empty($brg['tanggalDaftar'])
                                                ? $brg['tanggalDaftar']
                                                : ((!empty($bppbItems[$index]->tgl_bc_in) && $bppbItems[$index]->tgl_bc_in !== '0000-00-00')
                                                    ? $bppbItems[$index]->tgl_bc_in : ''),
                    'seriBarangDokAsal'    => !empty($brg['seriBarangAsal'])
                                                ? $brg['seriBarangAsal']
                                                : ($bppbItems[$index]->no_urut ?? ($index + 1)),

                    // --- Jenis & Uraian Barang ---
                    'posTarif'          => $brg['posTarif'] ?? '',
                    'kodeBarang'        => $brg['kodeBarang'] ?? '',
                    'uraian'            => $brg['uraian'] ?? '',
                    'merk'              => $brg['merek'] ?? $brg['merk'] ?? '-',
                    'tipe'              => $brg['tipe'] ?? '-',
                    'ukuran'            => $brg['ukuran'] ?? '-',
                    'spesifikasiLain'   => $brg['spesifikasiLain'] ?? '-',

                    // --- Asal & Kuantitas ---
                    'kodeNegaraAsal'    => $brg['kodeNegaraAsal'] ?? 'ID',
                    'kodeDaerahAsal'    => $brg['kodeDaerahAsal'] ?? '',
                    'jumlahSatuan'      => (float) ($brg['jumlahSatuan'] ?? 0),
                    'kodeSatuanBarang'  => $brg['kodeSatuanBarang'] ?? '',
                    'jumlahKemasan'     => (float) ($brg['jumlahKemasan'] ?? 0),
                    'kodeJenisKemasan'  => $brg['kodeJenisKemasan'] ?? '',
                    'netto'             => (float) ($brg['netto'] ?? 0),
                    'volume'            => (float) ($brg['volume'] ?? 0),

                    // --- Nilai ---
                    'fob'               => (float) ($brg['fob'] ?? 0),
                    'nilaiBarang'       => (float) ($brg['nilaiBarang'] ?? $brg['hargaEkspor'] ?? 0),
                    'hargaSatuan'       => (float) ($brg['hargaSatuan'] ?? 0),
                    'hargaPatokan'      => (float) ($brg['hargaPatokan'] ?? 0),
                    'kodeJenisEkspor'   => $brg['kodeJenisEkspor'] ?? '1',

                    // --- Sub-array ---
                    'barangTarif'       => $barangTarif,
                    'dokFasilitas'      => $barangDokumen,
                    'entitasBarang'     => $entitasBarang,
                ];

                if (empty($itemBarang['tanggalDaftarDokAsal'])) {
                    unset($itemBarang['tanggalDaftarDokAsal']);
                }
                $payloadBarang[] = $itemBarang;
            }

            // Pungutan dari draft — sudah disimpan dengan format {kodePungutan, dibayar}
            $payloadPungutan = [];
            if (!empty($draft['pungutan']) && is_array($draft['pungutan'])) {
                foreach ($draft['pungutan'] as $idx => $p) {
                    $payloadPungutan[] = [
                        'seriPungutan'  => $idx + 1,
                        'kodePungutan'  => $p['kodePungutan'] ?? '',
                        'dibayar'       => (float) ($p['dibayar'] ?? 0),
                    ];
                }
            }

            $finalPayload = [
                'idPlatform'            => config('ceisa.id_platform_dev', ''),
                'asalData'              => 'S',
                'disclaimer'            => '1',
                'kodeDokumen'           => '33',
                'nomorAju'              => $ceisaInfo->nomor_aju ?? '',
                'tanggalAju'            => $draft['tanggalAju'] ?? date('Y-m-d'),

                // Kode Kantor & Rute
                'kodeKantor'            => $draft['kodeKantor'] ?? '050500',
                'kodeKantorMuat'        => $draft['kodeKantorMuat'] ?? '',
                'kodeKantorAsal'        => $draft['kodeKantorAsal'] ?? '',
                'kodeKantorEkspor'      => $draft['kodeKantorEkspor'] ?? $draft['kodeKantorMuat'] ?? $draft['kodeKantor'] ?? '050500',
                '_kodeKantorSKEP'       => $draft['_kodeKantorSKEP'] ?? $draft['kodeKantorAsal'] ?? $draft['kodeKantor'] ?? '050500',
                'idPkbe'                => $draft['idPkbe'] ?? $id,
                'kodePelMuatAsal'       => $draft['kodePelMuatAsal'] ?? $draft['kodePelMuat'] ?? '',
                'kodePelMuat'           => $draft['kodePelMuat'] ?? $draft['kodePelMuatAsal'] ?? '',
                'kodePelBongkar'        => $draft['kodePelBongkar'] ?? '',
                'kodePelTujuan'         => $draft['kodePelTujuan'] ?? '',
                'kodeGudangAsal'        => $draft['kodeGudangAsal'] ?? '',
                'kodeGudangPlb'         => $draft['kodeGudangPlb'] ?? '',
                'kodeCaraAngkutPlb'     => $draft['kodeCaraAngkutPlb'] ?? '',
                'tanggalMasukPlb'       => $draft['tanggalMasukPlb'] ?? '',
                'tanggalMasuk'          => $draft['tanggalMasuk'] ?? '',
                'kodeTps'               => $draft['kodeTps'] ?? $draft['kodeLokasiTps'] ?? null,
                'kodeNegaraTujuan'      => $draft['kodeNegaraTujuan'] ?? '',
                'tanggalMuat'           => $draft['tanggalMuat'] ?? '',

                // Parameter Bisnis & Transaksi
                'kodeJenisProsedur'     => $draft['kodeJenisProsedur'] ?? '',
                'kodeJenisEkspor'       => $draft['kodeJenisEkspor'] ?? '',
                'kodeKategoriEkspor'    => $draft['kodeKategoriEkspor'] ?? '',
                'kodeCaraDagang'        => $draft['kodeCaraDagang'] ?? '',
                'kodeCaraBayar'         => $draft['kodeCaraBayar'] ?? '',
                'kodeDaerahAsal'        => $draft['kodeDaerahAsal'] ?? '',
                'kodeIncoterm'          => $draft['kodeIncoterm'] ?? '',
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

                // Atribut Pengangkut di tingkat Root (Spesifikasi CEISA 4.0)
                'kodeCaraAngkut'        => !empty($payloadPengangkut[0]['kodeCaraAngkut']) ? $payloadPengangkut[0]['kodeCaraAngkut'] : ($draft['kodeCaraAngkut'] ?? ''),
                'namaPengangkut'        => !empty($payloadPengangkut[0]['namaPengangkut']) ? $payloadPengangkut[0]['namaPengangkut'] : ($draft['namaPengangkut'] ?? ''),
                'nomorPengangkut'       => !empty($payloadPengangkut[0]['nomorPengangkut']) ? $payloadPengangkut[0]['nomorPengangkut'] : ($draft['nomorPengangkut'] ?? ''),
                'kodeBendera'           => !empty($payloadPengangkut[0]['kodeBendera']) ? $payloadPengangkut[0]['kodeBendera'] : ($draft['kodeBendera'] ?? ''),
                'kodeBenderaPengangkut' => !empty($payloadPengangkut[0]['kodeBendera']) ? $payloadPengangkut[0]['kodeBendera'] : ($draft['kodeBendera'] ?? ''),
                'caraPengangkutanLainnya'=> $draft['caraPengangkutanLainnya'] ?? '',

                'kontainer'             => $payloadKontainer,
                'kemasan'               => $payloadKemasan,
                'bankDevisa'            => $payloadBankDevisa,
                'pungutan'              => $payloadPungutan,
                'kesiapanBarang'        => $kesiapanBarangList,
                'barang'                => $payloadBarang,
            ];

            // Membersihkan field tanggal yang string kosong agar tidak memicu invalid date di CEISA
            foreach (['tanggalMasukPlb', 'tanggalMasuk', 'tanggalMuat', 'tanggalEkspor', 'tanggalPeriksa', 'tanggalTtd'] as $dateField) {
                if (isset($finalPayload[$dateField]) && empty($finalPayload[$dateField])) {
                    unset($finalPayload[$dateField]);
                }
            }

            $responseCeisa = $this->ceisaService->kirimDokumenBc33($finalPayload);

            if ($responseCeisa['successful']) {
                $db->table('bpb_ceisa')->where('bpbno', $id)->update([
                    'status'       => 1,
                    'updated_at'   => \Carbon\Carbon::now(),
                ]);

                return response()->json([
                    'status'         => 200,
                    'message'        => 'Dokumen BC 3.3 berhasil dikirim ke CEISA!',
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
