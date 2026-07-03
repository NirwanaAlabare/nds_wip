<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Bc25Service
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
                'a.*',
                DB::raw("IF(a.id_so_det IS NOT NULL AND a.id_so_det != '' AND a.id_so_det != '0', ms.goods_code, mi.goods_code) as goods_code"),
                DB::raw("IF(a.id_so_det IS NOT NULL AND a.id_so_det != '' AND a.id_so_det != '0', CONCAT(ms.itemname, ' ', IFNULL(ms.color,''), ' ', IFNULL(ms.size,'')), mi.itemdesc) as itemdesc")
            )
            ->where(function ($query) use ($id) {
                $query->where('a.bppbno', $id)->orWhere('a.bppbno_int', $id);
            })
            ->get();

        $nomorAju = $ceisaInfo->nomor_aju ?? $this->generateNomorAju($db);

        $dokumens = !empty($dataDetail['dokumen']) ? $dataDetail['dokumen'] : [
            ['kodeDokumen' => '', 'nomorDokumen' => '', 'tanggalDokumen' => '', 'fasilitas' => '', 'izin' => '', 'kantor' => '']
        ];

        $jaminans = !empty($dataDetail['jaminan']) ? $dataDetail['jaminan'] : [
            ['kodeJenisJaminan' => '', 'nomorJaminan' => '', 'tanggalJaminan' => '', 'nilaiJaminan' => '', 'tanggalJatuhTempo' => '', 'penjamin' => '', 'nomorBpj' => '', 'tanggalBpj' => '']
        ];

        $pengangkuts = !empty($dataDetail['pengangkut']) ? $dataDetail['pengangkut'] : [
            ['kodeCaraAngkut' => '3']
        ];

        $kontainers = !empty($dataDetail['kontainer']) ? $dataDetail['kontainer'] : [
            ['nomor' => '', 'ukuran' => '', 'jenis' => '', 'tipe' => '']
        ];

        $kemasans = !empty($dataDetail['kemasan']) ? $dataDetail['kemasan'] : [
            ['jumlah' => '', 'jenis' => '', 'merk' => '']
        ];

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

        $referensiDokumen = [
            '10' => 'RKSP',
            '11' => 'MANIFES',
            '20' => 'BC 2.0 - PEMBERITAHUAN IMPOR BARANG',
            '23' => 'BC 2.3 - PEMBERITAHUAN IMPOR BARANG UNTUK DITIMBUN DI TEMPAT PENIMBUNAN BERIKAT',
            '25' => 'BC 2.5 - PEMBERITAHUAN IMPOR BARANG DARI TEMPAT PENIMBUNAN BERIKAT',
            '262' => 'BC 2.6.2 - PEMBERITAHUAN PENGELUARAN BARANG DARI TEMPAT PENIMBUNAN BERIKAT DENGAN JAMINAN',
            '27' => 'BC 2.7 - PEMBERITAHUAN PENGELUARAN UNTUK DIANGKUT DARI TEMPAT PENIMBUNAN BERIKAT KE TEMPAT PENIMBUNAN BERIKAT LAINNYA',
            '30' => 'BC 3.0 - PEMBERITAHUAN EKSPOR BARANG',
            '33' => 'BC 3.3 - PEMBERITAHUAN EKSPOR BARANG MELALUI/DARI PUSAT LOGISTIK BERIKAT',
            '40' => 'BC 4.0 - PEMBERITAHUAN PEMASUKAN BARANG ASAL TEMPAT LAIN DALAM DAERAH PABEAN KE TEMPAT PENIMBUNAN BERIKAT',
            '41' => 'BC 4.1 - PEMBERITAHUAN PENGELUARAN KEMBALI BARANG ASAL TEMPAT LAIN DALAM DAERAH PABEAN DARI TEMPAT PENIMBUNAN BERIKAT',
            '50' => 'KITE',
            '51' => 'FTZ 01',
            '52' => 'FTZ 02',
            '53' => 'FTZ 03',
            '65' => 'BC 1.1 KONSOLIDASI PJT',
            '111' => 'Bank Devisa Hasil Ekspor (DHE)',
            '161' => 'PPB - PEMBERITAHUAN PERPINDAHAN BARANG ANTAR TEMPAT PENIMBUNAN DALAM SATU PUSAT LOGISTIK BERIKAT',
            '202' => 'PENGELUARAN BAHAN BAKU DAN/ ATAU SISA BAHAN BAKU',
            '203' => 'PENGELUARAN SEMENTARA - SUBKONTRAK',
            '204' => 'PENGELUARAN SEMENTARA - PERBAIKAN/ REPARASI',
            '205' => 'PENGELUARAN SEMENTARA - PEMINJAMAN BARANG MODAL UNTUK KEPERLUAN PRODUKSI',
            '206' => 'PENGELUARAN SEMENTARA - PENGETESAN ATAU PENGEMBANGAN KUALITAS PRODUKSI',
            '207' => 'PENGELUARAN SEMENTARA - PENGGUNAAN KEMASAN YANG DIPAKAI BERULANG (RETURNABLE PACKAGE)',
            '208' => 'PENGELUARAN SEMENTARA - DIPAMERKAN',
            '209' => 'PENGELUARAN SEMENTARA - TUJUAN LAIN DENGAN PERSETUJUAN KEPALA KANTOR PABEAN',
            '210' => 'PENERIMAAN PEKERJAAN - SUBKONTRAK',
            '211' => 'PENERIMAAN PEKERJAAN - PERBAIKAN/ REPARASI',
            '212' => 'PENERIMAAN PEKERJAAN - PEKERJAAN LAIN',
            '213' => 'PEMUSNAHAN BARANG DI KAWASAN BERIKAT',
            '217' => 'PACKING LIST',
            '246' => 'L/C',
            '261' => 'BC 2.6.1 - PEMBERITAHUAN PENGELUARAN BARANG DARI TEMPAT PENIMBUNAN BERIKAT DENGAN JAMINAN',
            '262' => 'BC 2.6.2 - PEMBERITAHUAN PEMASUKAN KEMBALI BARANG YANG DI KELUARKAN DARI TEMPAT PENIMBUNAN BERIKAT DENGAN JAMINAN',
            '281' => 'PPK - PEMBERITAHUAN PEMASUKAN KEMBALI BARANG ASAL PLB DARI LOKASI PENERIMA FASILITAS DI TEMPAT LAIN DALAM DAERAH PABEAN KE PLB',
            '282' => 'DOKAP PLB - PEMBERITAHUAN PENGELUARAN DENGAN DOKUMEN PELENGKAP',
            '302' => 'CN Ekspor',
            '315' => 'KONTRAK',
            '331' => 'P3BET - PEMBERITAHUAN PENGGABUNGAN DAN PEMECAHAN BARANG EKSPOR DAN TRANSHIPMENT',
            '343' => 'SHIPING ORDER',
            '380' => 'INVOICE',
            '383' => 'SSTB',
            '388' => 'FAKTUR PAJAK',
            '410' => 'SURAT SANGGUP BAYAR / SSB',
            '430' => 'BANK GARANSI',
            '440' => 'SURAT TANDA BUKTI SETOR / STBS',
            '454' => 'SSPCP / SSBC',
            '455' => 'SURAT SETORAN PAJAK (SSP)',
            '456' => 'SKB',
            '457' => 'Surat Keterangan Bebas (SKB) PPh',
            '458' => 'SURAT KETERANGAN TIDAK DIPUNGUT (SKTD) PPN',
            '459' => 'Non SKB / SKTD',
            '500' => 'MOU PDE (Eksportir)',
            '511' => 'FTZ-01 PEMASUKAN DARI LUAR DAERAH PABEAN (IMPOR)',
            '512' => 'FTZ-01 PENGELUARAN KE LUAR DAERAH PABEAN (EKSPOR)',
            '513' => 'FTZ-01 PENGELUARAN KE TEMPAT LAIN DALAM DAERAH PABEAN',
            '521' => 'FTZ-02 PEMASUKAN ANTAR FREE TRADE ZONE DAN KAWASAN BERIKAT',
            '522' => 'FTZ-02 PENGELUARAN ANTAR FREE TRADE ZONE DAN KAWASAN BERIKAT',
            '531' => 'FTZ-03 PEMASUKAN DARI TEMPAT LAIN DALAM DAERAH PABEAN',
            '640' => 'DELIVERY ORDER',
            '666' => 'Pengecualian Dengan Surat Keputusan',
            '704' => 'MASTER B/L',
            '705' => 'B/L',
            '740' => 'AWB',
            '741' => 'MASTER AWB',
            '800' => 'SERTIFIKAT ALAT PERANGKAT TELEKOM/POSTEL',
            '803' => 'SATS LN / DEPHUT',
            '805' => 'REGISTRASI B3 / KLH',
            '808' => 'IJIN IMPOR / POLRI',
            '809' => 'SIE',
            '810' => 'SM/SPM',
            '811' => 'Sertifikat Legalitas Kayu (Dok.V-Legal)',
            '812' => 'Dok. Impor (PIB)',
            '813' => 'DOK. CUKAI (CK)',
            '814' => 'SKEP IJIN EKSPOR BERKALA',
            '815' => 'SKEP IJIN TATA NIAGA EKSPOR',
            '816' => 'DOK. EKSPOR (PEB)',
            '817' => 'Eksportir Terdaftar (ET) Depdag',
            '818' => 'Endorsement BRIK',
            '819' => 'Sertifikat Intan Kasar',
            '820' => 'Surat Persetujuan Ekspor (SPE)',
            '821' => 'Surat Tanda Registrasi UPPB',
            '822' => 'Srt Tanda Pendaftaran Pedagang Bokor SIR',
            '834' => 'SNI GULA KRISTAL MENTAH / DEPTAN',
            '835' => 'IZIN DAN/ATAU PENDAFT PESTISIDA / DEPTAN',
            '836' => 'IZIN IMPOR / DEPTAN',
            '842' => 'SNI / ESDM',
            '843' => 'NOMOR PELUMAS TERDAFTAR / ESDM',
            '844' => 'IJIN USAHA NIAGA/IU NIAGA TERBATAS/ESDM',
            '845' => 'REKOMENDASI IMPOR PELUMAS',
            '846' => 'SKEM',
            '851' => 'SURAT IJIN KARANTINA TANAMAN',
            '853' => 'SURAT IJIN KARANTINA HEWAN / IKAN',
            '854' => 'SURAT PERSETUJUAN MUAT BPOM',
            '856' => 'LAP. PEMERIKSAAN SURVEYOR (LPS-E)',
            '857' => 'FUMIGATION CERTIFICATE',
            '858' => 'CITES CERTIFICATE',
            '860' => 'Electronic Certificate Of Origin (E-CO)',
            '861' => 'CERTIFICATE OF ORIGIN (CO)',
            '862' => 'SKEP USDFS',
            '871' => 'Nomor Pendaftaran Alat Kesehatan/Depkes',
            '872' => 'LAPORAN SURVEYOR DEPKES',
            '873' => 'IP (NARKTK, PREKURSOR & PSIKOTR)/DEPKES',
            '874' => 'IT (PREKURSOR & PSIKOTR)/DEPKES',
            '875' => 'SPI (NARKTK, PREKURSOR & PSIKOTR)/DEPKES',
            '876' => 'Ijin Pembawaan UKA',
            '877' => 'Ijin Persetujuan Pembawaan UKA',
            '878' => 'Ijin Pelaporan Pembawaan UKA',
            '888' => 'PENGECUALIAN PERIJINAN',
            '902' => 'IJIN BAPETEN',
            '911' => 'SURAT KEPUTUSAN',
            '912' => 'SKEP FASILITAS BKPM',
            '913' => 'SKEP FASILITAS PERTAMBANGAN',
            '914' => 'KITE IKM',
            '915' => 'Skep Fasilitas Impor Sementara',
            '917' => 'BPBC / BPPAI',
            '918' => 'SK LABEL BAHASA INDONESIA',
            '919' => 'SK Bermotor',
            '920' => 'SKEP TPB',
            '936' => 'KH-9a/Izin Impor Karantina Hewan',
            '937' => 'KH-14/Izin Impor Karantina Hewan',
            '938' => 'KH-17/Izin Impor Karantina Hewan',
            '939' => 'KT-5/Izin Impor Karantina Pertanian',
            '940' => 'KT-9/Izin Impor Karantina Pertanian',
            '941' => 'KT-13/Izin Impor Karantina Pertanian',
            '942' => 'IZIN IMPOR KARANTINA TUMBUHAN',
            '943' => 'KH-5 / IZIN IMPOR KARANTINA HEWAN',
            '944' => 'KH-7 / IZIN IMPOR KARANTINA HEWAN',
            '945' => 'KH-12 / IZIN IMPOR KARANTINA HEWAN',
            '946' => 'KID-3 / IZIN IMPOR KARANTINA IKAN',
            '947' => 'KID-15 / IZIN IMPOR KARANTINA IKAN',
            '948' => 'NPIK',
            '949' => 'PENGAKUAN SBG IMPORTIR PRODUSEN',
            '950' => 'KID-4/IZIN KARANTINA IKAN',
            '951' => 'HC (HEALTH CERTIFICATE)',
            '956' => 'PENGAKUAN SBG IMPORTIR TERDAFTAR',
            '957' => 'SNI/SPB/DEPDAG',
            '958' => 'LAPORAN SURVEYOR / DEPDAG',
            '959' => 'SURAT PERSETUJUAN IMPOR DEP.DAG',
            '960' => '3D/PC dan/atau PFP',
            '961' => 'Hasil Lab',
            '993' => 'SURAT IJIN MENTERI PERTANIAN',
            '994' => 'BUKTI PENERIMAAN JAMINAN (BPJ)',
            '995' => 'STBS / SSP-E (PAJAK EKSPOR)',
            '996' => 'SRT SANGGUP BAYAR (SSB)',
            '997' => 'COSTOMS BOND / STTJ',
            '998' => 'SKEP FASILITAS KEMUDAHAN EKSPOR',
            '999' => 'LAINNYA'
        ];

        $listValuta = [
            'IDR' => 'Rupiah',
            'USD' => 'US Dollar',
            'SGD' => 'Singapore Dollar',
            'EUR' => 'Euro',
            'JPY' => 'Yen',
            'CNY' => 'Yuan',
        ];

        $listCaraAngkut = [
            '1' => 'Laut',
            '2' => 'Kereta Api',
            '3' => 'Darat',
            '4' => 'Udara',
            '5' => 'Pos',
            '6' => 'Multimoda',
            '7' => 'Instalasi/Pipa',
            '8' => 'Perairan',
            '9' => 'Lainnya'
        ];

        $listJenisPungutan = [
            'BM' => 'Bea Masuk',
            'BMT' => 'Bea Masuk Tambahan',
            'CUKAI' => 'Cukai',
            'PPN' => 'PPN',
            'PPNBM' => 'PPnBM',
            'PPH' => 'PPh',
        ];

        $listJenisJaminan = [
            '1' => 'Tunai',
            '2' => 'Bank Garansi',
            '3' => 'Customs Bond',
            '4' => 'Tertulis/CG',
            '5' => 'STTJ',
            '6' => 'Pusat',
            '7' => 'Indonesia EximBank',
            '8' => 'Perusahaan Penjaminan'
        ];

        $listKategoriBarang = [
            '1' => 'Bahan Baku',
            '2' => 'Bahan Penolong',
            '3' => 'Barang Modal',
            '4' => 'Peralatan Pabrik',
            '5' => 'Barang Hasil Produksi',
            '6' => 'Barang Lain-lain',
            '7' => 'Barang Jadi',
            '8' => 'Barang Contoh'
        ];

        $listUkuranKontainer = [
            '20' => '20 Feet',
            '40' => '40 Feet',
            '45' => '45 Feet',
            '60' => '60 Feet'
        ];

        $listTipeKontainer = [
            '1' => 'General / Dry Cargo',
            '2' => 'Thermal',
            '3' => 'Tank',
            '4' => 'Dry Bulk',
            '5' => 'Platform',
            '99' => 'Lain-lain'
        ];

        $listJenisKontainer = [
            '4' => 'Empty',
            '7' => 'LCL',
            '8' => 'FCL'
        ];

        // Format CEISA status
        if (!$ceisaInfo) {
            $statusCeisa = '<span class="badge badge-secondary">Draft Kosong</span>';
        } else {
            if ($ceisaInfo->status == 0) {
                $statusCeisa = '<span class="badge badge-warning">Draft</span>';
            } elseif ($ceisaInfo->status == 1) {
                $statusCeisa = '<span class="badge badge-success">Sudah Kirim</span>';
            } else {
                $statusCeisa = '<span class="badge badge-danger">Gagal</span>';
            }
        }

        $kantorList = $this->getKantorList();

        return view('export-import.dokumen-pabean.edit-bc25', [
            'header'              => $header,
            'items'               => $items,
            'ceisaInfo'           => $ceisaInfo,
            'dataDetail'          => $dataDetail,
            'nomorAju'            => $nomorAju,
            'dokumens'            => $dokumens,
            'jaminans'            => $jaminans,
            'statusCeisa'         => $statusCeisa,
            'kantorList'          => $kantorList,
            'pengangkuts'         => $pengangkuts,
            'kontainers'          => $kontainers,
            'kemasans'            => $kemasans,
            'listSatuanBarang'    => $listSatuanBarang,
            'listJenisKemasan'    => $listJenisKemasan,
            'listValuta'          => $listValuta,
            'listKategoriBarang'  => $listKategoriBarang,
            'listUkuranKontainer' => $listUkuranKontainer,
            'listTipeKontainer'   => $listTipeKontainer,
            'listJenisKontainer'  => $listJenisKontainer,
            'referensiDokumen'    => $referensiDokumen,
            'listCaraAngkut'      => $listCaraAngkut,
            'listJenisPungutan'   => $listJenisPungutan,
            'listJenisJaminan'    => $listJenisJaminan,
            'containerFluid'      => true,
            "page"           => "dashboard-export-import",
            "subPageGroup"   => "export-import",
            "subPage"        => "dokumen-pabean-list",
        ]);
    }

    public function updateDraft($id, Request $request)
    {
        DB::connection('mysql_sb')->beginTransaction();

        try {
            // --- 1. Dokumen Pendukung & Upload File ---
            $dokumenInput = $request->input('dokumen', []);
            $dokumenFiles = $request->file('dokumen', []);
            $dokumenList  = [];
            $seriDok = 1;

            foreach ($dokumenInput as $index => $d) {
                if (!empty($d['kodeDokumen']) || !empty($d['nomorDokumen']) || !empty($d['kode']) || !empty($d['nomor'])) {
                    $dokData = [
                        'seriDokumen'    => $seriDok++,
                        'kodeDokumen'    => $d['kodeDokumen'] ?? $d['kode'] ?? '',
                        'nomorDokumen'   => $d['nomorDokumen'] ?? $d['nomor'] ?? '',
                        'tanggalDokumen' => $d['tanggalDokumen'] ?? $d['tgl'] ?? date('Y-m-d'),
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
                        $dokData['urlDokumen'] = url('/uploads/ceisa/' . $fileName);
                    } else {
                        // Pertahankan URL/File lama jika ada
                        if (!empty($d['old_file'])) {
                            $dokData['fileName'] = $d['old_file'];
                            $dokData['urlDokumen'] = url('/uploads/ceisa/' . $d['old_file']);
                        }
                    }

                    $dokumenList[] = $dokData;
                }
            }

            // --- 2. Kontainer ---
            $kontainerInput = $request->input('kontainer', []);
            $kontainerList  = [];
            $seriKontainer = 1;
            foreach ($kontainerInput as $k) {
                $nomor = $k['nomorKontainer'] ?? $k['nomor'] ?? '';
                if (!empty($nomor)) {
                    $kontainerList[] = [
                        'seriKontainer'       => $seriKontainer++,
                        'kodeJenisKontainer'  => $k['kodeJenisKontainer'] ?? $k['jenis'] ?? '',
                        'kodeTipeKontainer'   => $k['kodeTipeKontainer'] ?? $k['tipe'] ?? '',
                        'kodeUkuranKontainer' => $k['kodeUkuranKontainer'] ?? $k['ukuran'] ?? '',
                        'nomorKontainer'      => $nomor,
                    ];
                }
            }

            // --- 3. Kemasan ---
            $kemasanInput = $request->input('kemasan', []);
            $kemasanList  = [];
            $seriKemasan = 1;
            foreach ($kemasanInput as $k) {
                $jumlah = $k['jumlahKemasan'] ?? $k['jumlah'] ?? 0;
                $jenis = $k['kodeJenisKemasan'] ?? $k['jenis'] ?? '';
                if (!empty($jumlah) || !empty($jenis)) {
                    $kemasanList[] = [
                        'seriKemasan'      => $seriKemasan++,
                        'jumlahKemasan'    => (float) $jumlah,
                        'kodeJenisKemasan' => $jenis,
                        'merkKemasan'      => $k['merkKemasan'] ?? $k['merk'] ?? '-',
                    ];
                }
            }

            // --- 4. Pengangkut ---
            $pengangkutInput = $request->input('pengangkut', []);
            $pengangkutList  = [];
            $seriPengangkut = 1;
            foreach ($pengangkutInput as $p) {
                if (!empty($p['kodeCaraAngkut'])) {
                    $pengangkutList[] = [
                        'seriPengangkut' => $seriPengangkut++,
                        'kodeCaraAngkut' => $p['kodeCaraAngkut'] ?? '',
                    ];
                }
            }

            // --- 5. Jaminan ---
            $jaminanInput = $request->input('jaminan', []);
            $jaminanList  = [];
            foreach ($jaminanInput as $j) {
                if (!empty($j['kodeJenisJaminan'])) {
                    $jaminanList[] = [
                        'kodeJenisJaminan'  => $j['kodeJenisJaminan'] ?? '',
                        'nomorJaminan'      => $j['nomorJaminan'] ?? '',
                        'tanggalJaminan'    => $j['tanggalJaminan'] ?? date('Y-m-d'),
                        'nilaiJaminan'      => (float) ($j['nilaiJaminan'] ?? 0),
                        'tanggalJatuhTempo' => $j['tanggalJatuhTempo'] ?? date('Y-m-d'),
                        'penjamin'          => $j['penjamin'] ?? '',
                        'nomorBpj'          => $j['nomorBpj'] ?? '',
                        'tanggalBpj'        => $j['tanggalBpj'] ?? date('Y-m-d'),
                    ];
                }
            }

            // --- 6. Barang & Bahan Baku ---
            $barangInput = $request->input('barang', []);
            $barangList  = [];
            foreach ($barangInput as $index => $b) {

                // Bahan Baku (gabungan lokal dan impor)
                $bahanBakuInput = $b['bahanBaku'] ?? [];
                $bahanBakuList = [];
                $seriBahanBaku = 1;

                foreach ($bahanBakuInput as $bb) {
                    if (!empty($bb['kodeBarang'])) {

                        // Bahan Baku Tarif (dalam Bahan Baku)
                        $bahanBakuTarifInput = $bb['bahanBakuTarif'] ?? [];
                        $bahanBakuTarifList = [];
                        foreach ($bahanBakuTarifInput as $bbt) {
                            if (!empty($bbt['kodeJenisPungutan'])) {
                                $bahanBakuTarifList[] = [
                                    'seriBahanBaku'       => $seriBahanBaku,
                                    'kodeJenisPungutan'   => $bbt['kodeJenisPungutan'] ?? '',
                                    'kodeAsalBahanBaku'   => $bbt['kodeAsalBahanBaku'] ?? '',
                                    'kodeFasilitasTarif'  => $bbt['kodeFasilitasTarif'] ?? '',
                                    'kodeSatuanBarang'    => $bbt['kodeSatuanBarang'] ?? '',
                                    'kodeJenisTarif'      => $bbt['kodeJenisTarif'] ?? '1',
                                    'nilaiBayar'          => (float) ($bbt['nilaiBayar'] ?? 0),
                                    'nilaiSudahDilunasi'  => (float) ($bbt['nilaiSudahDilunasi'] ?? 0),
                                    'nilaiFasilitas'      => (float) ($bbt['nilaiFasilitas'] ?? 0),
                                    'jumlahSatuan'        => (float) ($bbt['jumlahSatuan'] ?? 0),
                                    'jumlahKemasan'       => (float) ($bbt['jumlahKemasan'] ?? 0),
                                    'tarif'               => (float) ($bbt['tarif'] ?? 0),
                                    'tarifFasilitas'      => (float) ($bbt['tarifFasilitas'] ?? 0),
                                ];
                            }
                        }

                        $bahanBakuList[] = [
                            'cif'                   => (float) ($bb['cif'] ?? 0),
                            'cifRupiah'             => (float) ($bb['cifRupiah'] ?? 0),
                            'hargaPenyerahan'       => (float) ($bb['hargaPenyerahan'] ?? 0),
                            'hargaPerolehan'        => (float) ($bb['hargaPerolehan'] ?? 0),
                            'jumlahSatuan'          => (float) ($bb['jumlahSatuan'] ?? 0),
                            'kodeSatuanBarang'      => $bb['kodeSatuanBarang'] ?? '',
                            'kodeAsalBahanBaku'     => $bb['kodeAsalBahanBaku'] ?? '0', // 0 Impor, 1 Lokal
                            'kodeBarang'            => $bb['kodeBarang'] ?? '',
                            'kodeDokAsal'           => $bb['kodeDokAsal'] ?? '',
                            'kodeDokumen'           => $bb['kodeDokumen'] ?? '',
                            'kodeKantor'            => $bb['kodeKantor'] ?? '',
                            'merkBarang'            => $bb['merkBarang'] ?? '',
                            'ndpbm'                 => (float) ($bb['ndpbm'] ?? 0),
                            'netto'                 => (float) ($bb['netto'] ?? 0),
                            'nomorDaftarDokAsal'    => $bb['nomorDaftarDokAsal'] ?? '',
                            'posTarif'              => $bb['posTarif'] ?? '',
                            'seriBahanBaku'         => $seriBahanBaku,
                            'seriBarang'            => (int) ($bb['seriBarang'] ?? 1),
                            'seriBarangDokAsal'     => (int) ($bb['seriBarangDokAsal'] ?? 1),
                            'seriIjin'              => (int) ($bb['seriIjin'] ?? 1),
                            'spesifikasiLainBarang' => $bb['spesifikasiLainBarang'] ?? '-',
                            'tanggalDaftarDokAsal'  => $bb['tanggalDaftarDokAsal'] ?? date('Y-m-d'),
                            'tipeBarang'            => $bb['tipeBarang'] ?? '',
                            'ukuranBarang'          => $bb['ukuranBarang'] ?? '',
                            'uraianBarang'          => $bb['uraianBarang'] ?? '',
                            'nilaiJasa'             => (float) ($bb['nilaiJasa'] ?? 0),
                            'flagTis'               => $bb['flagTis'] ?? '0',
                            'bahanBakuTarif'        => $bahanBakuTarifList
                        ];

                        $seriBahanBaku++;
                    }
                }

                $barangList[] = [
                    'seriBarang'        => (int) ($b['seriBarang'] ?? ($index + 1)),
                    'kodeBarang'        => $b['kodeBarang'] ?? '',
                    'uraian'            => $b['uraian'] ?? '',
                    'merk'              => $b['merk'] ?? '',
                    'tipe'              => $b['tipe'] ?? '',
                    'ukuran'            => $b['ukuran'] ?? '',
                    'spesifikasiLain'   => $b['spesifikasiLain'] ?? '-',
                    'posTarif'          => $b['posTarif'] ?? '',
                    'kodeNegaraAsal'    => $b['kodeNegaraAsal'] ?? '',
                    'kodeAsalBarang'    => $b['kodeAsalBarang'] ?? '',
                    'jumlahSatuan'      => (float) ($b['jumlahSatuan'] ?? 0),
                    'kodeSatuanBarang'  => $b['kodeSatuanBarang'] ?? '',
                    'jumlahKemasan'     => (float) ($b['jumlahKemasan'] ?? 0),
                    'kodeJenisKemasan'  => $b['kodeJenisKemasan'] ?? '',
                    'netto'             => (float) ($b['netto'] ?? 0),
                    'cif'               => (float) ($b['cif'] ?? 0),
                    'nilaiBarang'       => (float) ($b['nilaiBarang'] ?? 0),

                    'cifRupiah'         => (float) ($b['cifRupiah'] ?? 0),
                    'hargaEkspor'       => (float) ($b['hargaEkspor'] ?? 0),
                    'hargaPenyerahan'   => (float) ($b['hargaPenyerahan'] ?? 0),
                    'hargaPerolehan'    => (float) ($b['hargaPerolehan'] ?? 0),
                    'isiPerKemasan'     => (float) ($b['isiPerKemasan'] ?? 0),
                    'kodeAsalBahanBaku' => $b['kodeAsalBahanBaku'] ?? '0',
                    'kodeDokumen'       => $b['kodeDokumen'] ?? '23',
                    'ndpbm'             => (float) ($b['ndpbm'] ?? 0),
                    'uangMuka'          => (float) ($b['uangMuka'] ?? 0),
                    'nilaiJasa'         => (float) ($b['nilaiJasa'] ?? 0),

                    // Additional fields from BC 2.5
                    'dokumenAsal'       => $b['dokumenAsal'] ?? [],
                    'kodePenggunaan'    => $b['kodePenggunaan'] ?? '',
                    'kodeKategoriBarang'=> $b['kodeKategoriBarang'] ?? '',
                    'kodeKondisiBarang' => $b['kodeKondisiBarang'] ?? '',
                    'pungutan'          => $b['pungutan'] ?? [],

                    'bahanBaku'         => $bahanBakuList,
                ];
            }

            $draft = [
                // Header & Pengajuan
                'nomorAju'             => $request->input('nomorAju'),
                'kodeKantor'           => $request->input('kodeKantor'),
                'kodeJenisTpb'         => $request->input('kodeJenisTpb'),
                'kodeTujuanPengiriman' => $request->input('kodeTujuanPengiriman'),
                'kodeCaraBayar'        => $request->input('kodeCaraBayar'),

                // Entitas
                'entitas'              => $request->input('entitas', []),

                // Harga & Nilai Fisik
                'bruto'                => (float) $request->input('bruto', 0),
                'netto'                => (float) $request->input('netto', 0),
                'volume'               => (float) $request->input('volume', 0),
                'hargaPenyerahan'      => (float) $request->input('hargaPenyerahan', 0),
                'cif'                  => (float) $request->input('cif', 0),
                'biayaPengurang'       => (float) $request->input('biayaPengurang', 0),
                'uangMuka'             => (float) $request->input('uangMuka', 0),
                'nilaiJasa'            => (float) $request->input('nilaiJasa', 0),

                // Lists
                'dokumen'              => $dokumenList,
                'kontainer'            => $kontainerList,
                'kemasan'              => $kemasanList,
                'pengangkut'           => $pengangkutList,
                'barang'               => $barangList,
                'jaminan'              => $jaminanList,

                // Tanda Tangan
                'namaTtd'              => $request->input('namaTtd'),
                'jabatanTtd'           => $request->input('jabatanTtd'),
                'kotaTtd'              => $request->input('kotaTtd'),
                'tanggalTtd'           => $request->input('tanggalTtd'),
            ];

            // Update ke DB
            $headerBppb = DB::connection('mysql_sb')->table('bppb')->where(function($query) use ($id) {
                $query->where('bppbno', $id)->orWhere('bppbno_int', $id);
            })->first();

            $realBppbno    = $headerBppb ? $headerBppb->bppbno : $id;
            $realBppbnoInt = $headerBppb ? $headerBppb->bppbno_int : '';

            $ceisaRec = DB::connection('mysql_sb')->table('bpb_ceisa')
                ->where('bpbno', $id)->orWhere('bpbno_int', $id)->first();

            // Ambil nomor aju dari input, jika kosong gunakan dari record sebelumnya (bila ada)
            $inputNomorAju = $request->input('nomorAju', '');

            $payloadJson = json_encode($draft);

            if ($ceisaRec) {
                DB::connection('mysql_sb')->table('bpb_ceisa')
                    ->where('id', $ceisaRec->id)
                    ->update([
                        'bpbno'        => $realBppbno,
                        'bpbno_int'    => $realBppbnoInt,
                        'nomor_aju'    => $inputNomorAju ?: $ceisaRec->nomor_aju,
                        'payload_json' => $payloadJson,
                        'jenis_bc'     => '2.5',
                        'updated_at'   => Carbon::now()
                    ]);
            } else {
                DB::connection('mysql_sb')->table('bpb_ceisa')->insert([
                    'bpbno'        => $realBppbno,
                    'bpbno_int'    => $realBppbnoInt,
                    'nomor_aju'    => $inputNomorAju,
                    'jenis_bc'     => '2.5',
                    'payload_json' => $payloadJson,
                    'status'       => 0,
                    'created_at'   => Carbon::now(),
                    'updated_at'   => Carbon::now()
                ]);
            }

            DB::connection('mysql_sb')->commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Draft BC 2.5 berhasil disimpan.'
            ]);

        } catch (\Exception $e) {
            DB::connection('mysql_sb')->rollBack();
            Log::error('Error Update Draft BC 2.5: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status'  => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function sendCeisa($id, Request $request)
    {
        $db = DB::connection('mysql_sb');

        try {
            $header = $db->table('bppb as a')
                ->where(function ($query) use ($id) {
                    $query->where('a.bppbno', $id)->orWhere('a.bppbno_int', $id);
                })
                ->first();

            if (!$header) {
                throw new \Exception("Data transaksi bppb tidak ditemukan!");
            }

            $draftRow = $db->table('bpb_ceisa')
                ->where('bpbno', $header->bppbno)
                ->orWhere('bpbno_int', $header->bppbno_int)
                ->orWhere('bpbno', $id)
                ->orWhere('bpbno_int', $id)
                ->first();

            if (!$draftRow || empty($draftRow->payload_json)) {
                throw new \Exception('Draft kosong. Simpan draft terlebih dahulu.');
            }

            $draft = json_decode($draftRow->payload_json, true);
            $nomorAju = $draftRow->nomor_aju ?? $this->generateNomorAju($db);

            // --- Dokumen ---
            $payloadDokumen = [];
            foreach (($draft['dokumen'] ?? $draft['dok'] ?? []) as $index => $d) {
                $docItem = [
                    'idDokumen'      => "DOK" . str_pad($index + 1, 4, "0", STR_PAD_LEFT),
                    'kodeDokumen'    => $d['kodeDokumen'] ?? $d['kode'] ?? '',
                    'nomorDokumen'   => $d['nomorDokumen'] ?? $d['nomor'] ?? '',
                    'seriDokumen'    => (int) ($d['seriDokumen'] ?? ($index + 1)),
                    'tanggalDokumen' => $d['tanggalDokumen'] ?? $d['tgl'] ?? date('Y-m-d')
                ];

                if (!empty($d['fasilitas'])) {
                    // Pastikan panjangnya tidak lebih dari 2 karakter
                    $docItem['kodeFasilitas'] = substr($d['fasilitas'], 0, 2);
                }

                $payloadDokumen[] = $docItem;
            }

            // --- Entitas ---
            $entitasDraft = $draft['entitas'] ?? [];
            $payloadEntitas = [];

            // 1. Pengusaha TPB (Kode 3)
            if (!empty($entitasDraft[3])) {
                $e = $entitasDraft[3];
                $payloadEntitas[] = [
                    'seriEntitas'        => 1,
                    'kodeEntitas'        => '3',
                    'kodeJenisApi'       => $e['jenisApi'] ?? '02',
                    'kodeJenisIdentitas' => !empty($e['nomorIdentitas']) && strlen(str_replace(['.', '-'], '', $e['nomorIdentitas'])) == 16 ? '6' : '5',
                    'kodeStatus'         => $e['status'] ?? '5',
                    'namaEntitas'        => $e['namaEntitas'] ?? $e['nama'] ?? '',
                    'nibEntitas'         => $e['nibEntitas'] ?? $e['nib'] ?? '',
                    'nomorIdentitas'     => str_replace(['.', '-'], '', $e['nomorIdentitas'] ?? ''),
                    'niperEntitas'       => $e['niperEntitas'] ?? $e['niper'] ?? '',
                    'nomorIjinEntitas'   => $e['nomorIjinEntitas'] ?? $e['nomorIjin'] ?? '',
                    'alamatEntitas'      => $e['alamatEntitas'] ?? $e['alamat'] ?? '',
                    'tanggalIjinEntitas' => $e['tanggalIjinEntitas'] ?? $e['tanggalIjin'] ?? date('Y-m-d')
                ];
            }

            // 2. Pemilik Barang (Kode 7)
            if (!empty($entitasDraft[7])) {
                $e = $entitasDraft[7];
                $payloadEntitas[] = [
                    'seriEntitas'        => 2,
                    'kodeEntitas'        => '7',
                    'kodeJenisApi'       => $e['jenisApi'] ?? '02',
                    'kodeJenisIdentitas' => !empty($e['nomorIdentitas']) && strlen(str_replace(['.', '-'], '', $e['nomorIdentitas'])) == 16 ? '6' : '5',
                    'kodeStatus'         => $e['status'] ?? '5',
                    'namaEntitas'        => $e['namaEntitas'] ?? $e['nama'] ?? '',
                    'nibEntitas'         => $e['nibEntitas'] ?? $e['nib'] ?? '',
                    'nomorIdentitas'     => str_replace(['.', '-'], '', $e['nomorIdentitas'] ?? ''),
                    'niperEntitas'       => $e['niperEntitas'] ?? $e['niper'] ?? '',
                    'nomorIjinEntitas'   => $e['nomorIjinEntitas'] ?? $e['nomorIjin'] ?? '',
                    'alamatEntitas'      => $e['alamatEntitas'] ?? $e['alamat'] ?? '',
                    'tanggalIjinEntitas' => $e['tanggalIjinEntitas'] ?? $e['tanggalIjin'] ?? date('Y-m-d')
                ];
            }

            // 3. Pembeli / Penerima Barang (Kode 8)
            if (!empty($entitasDraft[8])) {
                $e = $entitasDraft[8];
                $payloadEntitas[] = [
                    'seriEntitas'        => 3,
                    'kodeEntitas'        => '8',
                    'kodeJenisApi'       => $e['jenisApi'] ?? '02',
                    'kodeJenisIdentitas' => !empty($e['nomorIdentitas']) && strlen(str_replace(['.', '-'], '', $e['nomorIdentitas'])) == 16 ? '6' : '5',
                    'kodeStatus'         => $e['status'] ?? '5',
                    'namaEntitas'        => $e['namaEntitas'] ?? $e['nama'] ?? '',
                    'nibEntitas'         => $e['nibEntitas'] ?? $e['nib'] ?? '',
                    'nomorIdentitas'     => str_replace(['.', '-'], '', $e['nomorIdentitas'] ?? ''),
                    'niperEntitas'       => $e['niperEntitas'] ?? $e['niper'] ?? '-',
                    'nomorIjinEntitas'   => $e['nomorIjinEntitas'] ?? $e['nomorIjin'] ?? '',
                    'alamatEntitas'      => $e['alamatEntitas'] ?? $e['alamat'] ?? '',
                    'tanggalIjinEntitas' => $e['tanggalIjinEntitas'] ?? $e['tanggalIjin'] ?? date('Y-m-d')
                ];
            }

            // --- Kemasan ---
            $payloadKemasan = [];
            foreach (($draft['kemasan'] ?? []) as $index => $k) {
                $payloadKemasan[] = [
                    'jumlahKemasan'    => (float) ($k['jumlahKemasan'] ?? 0),
                    'kodeJenisKemasan' => $k['kodeJenisKemasan'] ?? '',
                    'merkKemasan'      => $k['merkKemasan'] ?? '-',
                    'seriKemasan'      => (int) ($k['seriKemasan'] ?? ($index + 1)),
                ];
            }

            // --- Kontainer ---
            $payloadKontainer = [];
            foreach (($draft['kontainer'] ?? []) as $index => $k) {
                $payloadKontainer[] = [
                    'kodeJenisKontainer'  => $k['kodeJenisKontainer'] ?? '',
                    'kodeTipeKontainer'   => $k['kodeTipeKontainer'] ?? '',
                    'kodeUkuranKontainer' => $k['kodeUkuranKontainer'] ?? '',
                    'nomorKontainer'      => $k['nomorKontainer'] ?? '',
                    'seriKontainer'       => (int) ($k['seriKontainer'] ?? ($index + 1)),
                ];
            }

            // --- Pengangkut ---
            $payloadPengangkut = [];
            foreach (($draft['pengangkut'] ?? []) as $index => $p) {
                $payloadPengangkut[] = [
                    'idPengangkut'    => "ANG" . str_pad($index + 1, 4, "0", STR_PAD_LEFT),
                    'kodeCaraAngkut'  => $p['kodeCaraAngkut'] ?? '',
                    'namaPengangkut'  => $p['namaPengangkut'] ?? 'LAINNYA',
                    'nomorPengangkut' => $p['nomorPengangkut'] ?? '-',
                    'seriPengangkut'  => (int) ($p['seriPengangkut'] ?? ($index + 1)),
                ];
            }

            // --- Jaminan ---
            $payloadJaminan = [];
            foreach (($draft['jaminan'] ?? []) as $index => $j) {
                $payloadJaminan[] = [
                    'idJaminan'         => "JAM" . str_pad($index + 1, 4, "0", STR_PAD_LEFT),
                    'nomorBpj'          => $j['nomorBpj'] ?? '',
                    'tanggalBpj'        => $j['tanggalBpj'] ?? date('Y-m-d'),
                    'kodeJenisJaminan'  => $j['kodeJenisJaminan'] ?? '',
                    'nomorJaminan'      => $j['nomorJaminan'] ?? '',
                    'tanggalJaminan'    => $j['tanggalJaminan'] ?? date('Y-m-d'),
                    'tanggalJatuhTempo' => $j['tanggalJatuhTempo'] ?? date('Y-m-d'),
                    'penjamin'          => $j['penjamin'] ?? '',
                    'nilaiJaminan'      => (float) ($j['nilaiJaminan'] ?? 0),
                ];
            }

            // --- Barang ---
            $payloadBarang = [];
            foreach (($draft['barang'] ?? []) as $index => $b) {

                $payloadBahanBaku = [];
                foreach (($b['bahanBaku'] ?? []) as $bb) {

                    $payloadBahanBakuTarif = [];
                    foreach (($bb['bahanBakuTarif'] ?? []) as $bbt) {
                        $payloadBahanBakuTarif[] = [
                            'seriBahanBaku'       => (int) ($bbt['seriBahanBaku'] ?? 1),
                            'kodeJenisPungutan'   => $bbt['kodeJenisPungutan'] ?? '',
                            'kodeAsalBahanBaku'   => $bbt['kodeAsalBahanBaku'] ?? '0',
                            'kodeFasilitasTarif'  => $bbt['kodeFasilitasTarif'] ?? '',
                            'kodeSatuanBarang'    => $bbt['kodeSatuanBarang'] ?? '',
                            'kodeJenisTarif'      => $bbt['kodeJenisTarif'] ?? '1',
                            'nilaiBayar'          => (float) ($bbt['nilaiBayar'] ?? 0),
                            'nilaiSudahDilunasi'  => (float) ($bbt['nilaiSudahDilunasi'] ?? 0),
                            'nilaiFasilitas'      => (float) ($bbt['nilaiFasilitas'] ?? 0),
                            'jumlahSatuan'        => (float) ($bbt['jumlahSatuan'] ?? 0),
                            'jumlahKemasan'       => (float) ($bbt['jumlahKemasan'] ?? 0),
                            'tarif'               => (float) ($bbt['tarif'] ?? 0),
                            'tarifFasilitas'      => (float) ($bbt['tarifFasilitas'] ?? 0)
                        ];
                    }

                    $payloadBahanBaku[] = [
                        'cif'                   => (float) ($bb['cif'] ?? 0),
                        'cifRupiah'             => (float) ($bb['cifRupiah'] ?? 0),
                        'hargaPenyerahan'       => (float) ($bb['hargaPenyerahan'] ?? 0),
                        'hargaPerolehan'        => (float) ($bb['hargaPerolehan'] ?? 0),
                        'jumlahSatuan'          => (float) ($bb['jumlahSatuan'] ?? 0),
                        'kodeSatuanBarang'      => $bb['kodeSatuanBarang'] ?? '',
                        'kodeAsalBahanBaku'     => $bb['kodeAsalBahanBaku'] ?? '0',
                        'kodeBarang'            => $bb['kodeBarang'] ?? '',
                        'kodeDokAsal'           => $bb['kodeDokAsal'] ?? '',
                        'kodeDokumen'           => $bb['kodeDokumen'] ?? '',
                        'kodeKantor'            => $bb['kodeKantor'] ?? '',
                        'merkBarang'            => $bb['merkBarang'] ?? '',
                        'ndpbm'                 => (float) ($bb['ndpbm'] ?? 0),
                        'netto'                 => (float) ($bb['netto'] ?? 0),
                        'nomorAjuDokAsal'       => $bb['nomorAjuDokAsal'] ?? '',
                        'nomorDaftarDokAsal'    => $bb['nomorDaftarDokAsal'] ?? '',
                        'posTarif'              => $bb['posTarif'] ?? '',
                        'seriBahanBaku'         => (int) ($bb['seriBahanBaku'] ?? 1),
                        'seriBarang'            => (int) ($bb['seriBarang'] ?? 1),
                        'seriBarangDokAsal'     => (int) ($bb['seriBarangDokAsal'] ?? 1),
                        'seriIjin'              => (int) ($bb['seriIjin'] ?? 1),
                        'spesifikasiLainBarang' => $bb['spesifikasiLainBarang'] ?? '-',
                        'tanggalDaftarDokAsal'  => $bb['tanggalDaftarDokAsal'] ?? date('Y-m-d'),
                        'tipeBarang'            => $bb['tipeBarang'] ?? '-',
                        'ukuranBarang'          => $bb['ukuranBarang'] ?? '-',
                        'uraianBarang'          => $bb['uraianBarang'] ?? '',
                        'nilaiJasa'             => (float) ($bb['nilaiJasa'] ?? 0),
                        'flagTis'               => $bb['flagTis'] ?? '0',
                        'bahanBakuTarif'        => $payloadBahanBakuTarif
                    ];
                }

                $payloadBarangTarif = [];
                foreach (($b['pungutan'] ?? []) as $jns => $pt) {
                    if ((float)($pt['tarif'] ?? 0) > 0 || (float)($pt['nilaiBayar'] ?? 0) > 0) {
                        $payloadBarangTarif[] = [
                            'kodeJenisPungutan'  => strtoupper((string) $jns),
                            'kodeFasilitasTarif' => $pt['kodeFasilitasTarif'] ?? '1',
                            'kodeJenisTarif'     => $pt['kodeJenisTarif'] ?? '1',
                            'tarif'              => (float) ($pt['tarif'] ?? 0),
                            'tarifFasilitas'     => (float) ($pt['tarifFasilitas'] ?? 0),
                            'nilaiBayar'         => (float) ($pt['nilaiBayar'] ?? 0),
                            'nilaiFasilitas'     => (float) ($pt['nilaiFasilitas'] ?? 0),
                            'nilaiSudahDilunasi' => (float) ($pt['nilaiSudahDilunasi'] ?? 0),
                            'jumlahSatuan'       => (float) ($b['jumlahSatuan'] ?? 0),
                            'kodeSatuanBarang'   => $b['kodeSatuanBarang'] ?? '',
                        ];
                    }
                }

                if (empty($payloadBarangTarif)) {
                    $payloadBarangTarif[] = [
                        'kodeJenisPungutan'  => 'BM',
                        'kodeFasilitasTarif' => '1',
                        'kodeJenisTarif'     => '1',
                        'tarif'              => 0,
                        'tarifFasilitas'     => 0,
                        'nilaiBayar'         => 0,
                        'nilaiFasilitas'     => 0,
                        'nilaiSudahDilunasi' => 0,
                        'jumlahSatuan'       => (float) ($b['jumlahSatuan'] ?? 0),
                        'kodeSatuanBarang'   => $b['kodeSatuanBarang'] ?? '',
                    ];
                }

                $payloadBarang[] = [
                    'cif'               => (float) ($b['cif'] ?? 0),
                    'cifRupiah'         => (float) ($b['cifRupiah'] ?? 0),
                    'hargaEkspor'       => (float) ($b['hargaEkspor'] ?? 0),
                    'hargaPenyerahan'   => (float) ($b['hargaPenyerahan'] ?? 0),
                    'hargaPerolehan'    => (float) ($b['hargaPerolehan'] ?? 0),
                    'isiPerKemasan'     => (float) ($b['isiPerKemasan'] ?? 0),
                    'jumlahKemasan'     => (float) ($b['jumlahKemasan'] ?? 0),
                    'jumlahSatuan'      => (float) ($b['jumlahSatuan'] ?? 0),
                    'kodeAsalBahanBaku' => $b['kodeAsalBahanBaku'] ?? '0',
                    'kodeAsalBarang'    => $b['kodeAsalBarang'] ?? '0',
                    'kodeBarang'        => $b['kodeBarang'] ?? '',
                    'kodeDokumen'       => $b['kodeDokumen'] ?? '25',
                    'kodeJenisKemasan'  => $b['kodeJenisKemasan'] ?? '',
                    'kodeNegaraAsal'    => !empty($b['kodeNegaraAsal']) ? $b['kodeNegaraAsal'] : 'ID',
                    'kodeSatuanBarang'  => $b['kodeSatuanBarang'] ?? '',
                    'merk'              => $b['merk'] ?? '-',
                    'ndpbm'             => (float) ($b['ndpbm'] ?? 0),
                    'netto'             => (float) ($b['netto'] ?? 0),
                    'nilaiBarang'       => (float) ($b['nilaiBarang'] ?? 0),
                    'nilaiJasa'         => (float) ($b['nilaiJasa'] ?? 0),
                    'posTarif'          => $b['posTarif'] ?? '',
                    'seriBarang'        => (int) ($b['seriBarang'] ?? ($index + 1)),
                    'spesifikasiLain'   => $b['spesifikasiLain'] ?? '-',
                    'tipe'              => $b['tipe'] ?? '-',
                    'uangMuka'          => (float) ($b['uangMuka'] ?? 0),
                    'ukuran'            => $b['ukuran'] ?? '-',
                    'uraian'            => $b['uraian'] ?? '',

                    'diskon'             => (float) ($b['diskon'] ?? 0),
                    'fob'                => (float) ($b['fob'] ?? 0),
                    'freight'            => (float) ($b['freight'] ?? 0),
                    'kodeDokAsal'        => $b['dokumenAsal']['jenisDokumen'] ?? '',
                    'kodeGunaBarang'     => $b['kodePenggunaan'] ?? '',
                    'kodeKategoriBarang' => $b['kodeKategoriBarang'] ?? '',
                    'kodeKondisiBarang'  => $b['kodeKondisiBarang'] ?? '',
                    'kodePerhitungan'    => '0',
                    'barangDokumen'      => [ [ 'seriDokumen' => 1, 'seriIjin' => 1 ] ],
                    'barangTarif'        => $payloadBarangTarif,

                    'bahanBaku'         => $payloadBahanBaku
                ];
            }

            // Pungutan
            $payloadPungutan = [];
            $seriPungutan = 1;
            foreach (($draft['pungutan'] ?? []) as $index => $p) {
                if (!empty($p['kodeJenisPungutan']) && (float)($p['nilaiPungutan'] ?? 0) > 0) {
                    $payloadPungutan[] = [
                        'idPungutan'          => (string) $seriPungutan,
                        'kodeFasilitasTarif'  => $p['kodeFasilitasTarif'] ?? '1',
                        'kodeJenisPungutan'   => (string) ($p['kodeJenisPungutan'] ?? ''),
                        'nilaiPungutan'       => (float) ($p['nilaiPungutan'] ?? 0),
                    ];
                    $seriPungutan++;
                }
            }


            $finalPayload = [
                'asalData'             => 'S',
                'asuransi'             => 0,
                'biayaTambahan'        => 0,
                'biayaPengurang'       => 0,
                'bruto'                => (float) ($draft['bruto'] ?? 0),
                'cif'                  => (float) ($draft['nilaiCif'] ?? 0),
                'disclaimer'           => "1",
                'freight'              => 0,
                'hargaPenyerahan'      => 0,
                'jabatanTtd'           => $draft['jabatanTtd'] ?? '-',
                'jumlahKontainer'      => count($payloadKontainer),
                'kodeDokumen'          => '25',
                'kodeKantor'           => $draft['kantorPabean'] ?? $draft['kodeKantor'] ?? '',
                'kodeValuta'           => $draft['valuta'] ?? 'IDR',
                'kotaTtd'              => $draft['kotaTtd'] ?? '-',
                'namaTtd'              => $draft['namaTtd'] ?? '-',
                'ndpbm'                => (float) ($draft['ndpbm'] ?? 0),
                'netto'                => (float) ($draft['netto'] ?? 0),
                'nik'                  => str_replace(['.', '-'], '', $entitasDraft[3]['nomorIdentitas'] ?? ''),
                'nilaiBarang'          => (float) ($draft['nilaiPabean'] ?? 0),
                'nomorAju'             => $nomorAju,
                'seri'                 => 0,
                'tanggalAju'           => date('Y-m-d'),
                'tanggalTtd'           => $draft['tanggalTtd'] ?? date('Y-m-d'),
                'tempatStuffing'       => '',
                'tglAkhirBerlaku'      => date('Y-m-d'),
                'tglAwalBerlaku'       => date('Y-m-d'),
                'totalDanaSawit'       => 0,
                'uangMuka'             => 0,
                'vd'                   => 0,

                'idPengguna'           => $draft['idPengguna'] ?? 'admin',
                'kodeCaraBayar'        => $draft['kodeCaraBayar'] ?? '1',
                'kodeJenisTpb'         => $draft['kodeJenisTpb'] ?? '1',
                'kodeLokasiBayar'      => !empty($draft['kodeLokasiBayar']) ? $draft['kodeLokasiBayar'] : '1',
                'kodeTujuanPengiriman' => $draft['kodeTujuanPengiriman'] ?? $draft['tujuanPengiriman'] ?? '',
                'volume'               => (float) ($draft['volume'] ?? 0),


                'barang'              => $payloadBarang,
                'dokumen'             => $payloadDokumen,
                'entitas'             => $payloadEntitas,
                'jaminan'             => $payloadJaminan,
                'kemasan'             => $payloadKemasan,
                'kontainer'           => $payloadKontainer,
                'pengangkut'          => $payloadPengangkut,
                'pungutan'            => $payloadPungutan,
            ];

            foreach (['tanggalTtd', 'tglAkhirBerlaku', 'tglAwalBerlaku'] as $dateField) {
                if (isset($finalPayload[$dateField]) && empty($finalPayload[$dateField])) {
                    unset($finalPayload[$dateField]);
                }
            }

            Log::info('Kirim BC 2.5 CEISA Payload: ', $finalPayload);

            $responseCeisa = $this->ceisaService->kirimDokumenBc25($finalPayload);

            if ($responseCeisa['successful']) {
                $db->table('bpb_ceisa')->where('bpbno', $id)->orWhere('bpbno_int', $id)->update([
                    'nomor_aju'   => $nomorAju,
                    'tanggal_aju' => Carbon::now()->format('Y-m-d'),
                    'status'      => 1,
                    'updated_at'  => Carbon::now()
                ]);

                return response()->json([
                    'status'         => 200,
                    'message'        => 'Dokumen BC 2.5 berhasil dikirim ke CEISA!',
                    'data_payload'   => $finalPayload,
                    'ceisa_response' => $responseCeisa['body']
                ]);
            } else {
                return response()->json([
                    'status'      => $responseCeisa['status_code'],
                    'message'     => 'Gagal mengirim BC 2.5 ke CEISA.',
                    'ceisa_error' => $responseCeisa['body']
                ], $responseCeisa['status_code']);
            }

        } catch (\Exception $e) {
            Log::error('Error Send CEISA BC 2.5: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status'  => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function generateNomorAju($db)
    {
        $currentYear = date('Y');
        $today       = date('Ymd');
        $prefix      = '000025NIW3452';

        $lastCeisa = $db->table('bpb_ceisa')
            ->where('nomor_aju', 'like', $prefix . $currentYear . '%')
            ->where('jenis_bc', '2.5')
            ->orderBy('nomor_aju', 'desc')
            ->first();

        $localSeq = 0;
        if ($lastCeisa && $lastCeisa->nomor_aju && strlen($lastCeisa->nomor_aju) === 26) {
            $localSeq = (int) substr($lastCeisa->nomor_aju, -5);
        }

        $ceisaSeq = $this->ceisaService->getLastSequenceFromCeisa($prefix . $currentYear, '25');

        $maxSeq  = max($localSeq, $ceisaSeq);
        $nextSeq = str_pad($maxSeq + 1, 5, '0', STR_PAD_LEFT);

        return $prefix . $today . $nextSeq;
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
