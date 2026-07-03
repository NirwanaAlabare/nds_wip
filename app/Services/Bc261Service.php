<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Bc261Service
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
                'a.*',
                DB::raw("IF(a.id_so_det IS NOT NULL AND a.id_so_det != '' AND a.id_so_det != '0', ms.goods_code, mi.goods_code) as goods_code"),
                DB::raw("IF(a.id_so_det IS NOT NULL AND a.id_so_det != '' AND a.id_so_det != '0', CONCAT(ms.itemname, ' ', IFNULL(ms.color,''), ' ', IFNULL(ms.size,'')), mi.itemdesc) as itemdesc")
            )
            ->where(function ($query) use ($id) {
                $query->where('a.bppbno', $id)->orWhere('a.bppbno_int', $id);
            })
            ->get();

        $nomorAju = $ceisaInfo->nomor_aju ?? $this->generateNomorAju($db);

        $dokumens = !empty($dataDetail['dok']) ? $dataDetail['dok'] : [
            ['kode' => '', 'nomor' => '', 'tgl' => '', 'fasilitas' => '', 'izin' => '', 'kantor' => '']
        ];

        $jaminans = !empty($dataDetail['jaminan']) ? $dataDetail['jaminan'] : [
            ['kodeJenisJaminan' => '', 'nomorJaminan' => '', 'tanggalJaminan' => '', 'nilaiJaminan' => '', 'tanggalJatuhTempo' => '', 'penjamin' => '', 'nomorBpj' => '', 'tanggalBpj' => '']
        ];

        $pengangkuts = !empty($dataDetail['pengangkut']) ? $dataDetail['pengangkut'] : [
            ['kodeCaraAngkut' => '']
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
            '261' => 'BC 2.6.1 - PEMBERITAHUAN PENGELUARAN BARANG DARI TEMPAT PENIMBUNAN BERIKAT DENGAN JAMINAN',
            '262' => 'BC 2.6.2 - PEMBERITAHUAN PEMASUKAN KEMBALI BARANG YANG DI KELUARKAN DARI TEMPAT PENIMBUNAN BERIKAT DENGAN JAMINAN',
            '27' => 'BC 2.7 - PEMBERITAHUAN PENGELUARAN UNTUK DIANGKUT DARI TEMPAT PENIMBUNAN BERIKAT KE TEMPAT PENIMBUNAN BERIKAT LAINNYA',
            '30' => 'BC 3.0 - PEMBERITAHUAN EKSPOR BARANG',
            '33' => 'BC 3.3 - PEMBERITAHUAN EKSPOR BARANG MELALUI/DARI PUSAT LOGISTIK BERIKAT',
            '40' => 'BC 4.0 - PEMBERITAHUAN PEMASUKAN BARANG ASAL TEMPAT LAIN DALAM DAERAH PABEAN KE TEMPAT PENIMBUNAN BERIKAT',
            '41' => 'BC 4.1 - PEMBERITAHUAN PENGELUARAN KEMBALI BARANG ASAL TEMPAT LAIN DALAM DAERAH PABEAN DARI TEMPAT PENIMBUNAN BERIKAT',
            '217' => 'PACKING LIST',
            '380' => 'INVOICE',
            '705' => 'BILL OF LADING',
            '740' => 'AIRWAY BILL',
            '985' => 'Bukti Transfer',
            '994' => 'BUKTI PENERIMAAN JAMINAN (BPJ)',
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

        return view('export-import.dokumen-pabean.edit-bc261', [
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

                    'bahanBaku'         => $bahanBakuList,
                ];
            }

            $draft = [
                // Header & Pengajuan
                'kantorPabean'         => $request->input('kantorPabean'),
                'tujuanPengiriman'     => $request->input('tujuanPengiriman'),

                // Entitas
                'entitas'              => $request->input('entitas', []),

                // Harga
                'valuta'               => $request->input('valuta'),
                'ndpbm'                => (float) $request->input('ndpbm'),
                'nilaiCif'             => (float) $request->input('nilaiCif'),
                'nilaiPabean'          => (float) $request->input('nilaiPabean'),

                // Berat
                'bruto'                => (float) $request->input('bruto', 0),
                'netto'                => (float) $request->input('netto', 0),

                // Tambahan Pungutan
                'pungutan'             => $request->input('pungutan', []),

                // Lists
                'dok'                  => $dokumenList,
                'kontainer'            => $kontainerList,
                'kemasan'              => $kemasanList,
                'pengangkut'           => $pengangkutList,
                'barang'               => $barangList,
                'jaminan'              => $jaminanList,

                // Tanda Tangan
                'tempatTtd'            => $request->input('tempatTtd'),
                'tanggalTtd'           => $request->input('tanggalTtd'),
                'namaTtd'              => $request->input('namaTtd'),
                'jabatanTtd'           => $request->input('jabatanTtd'),
            ];

            // Update ke DB
            $headerBpb = DB::connection('mysql_sb')->table('bpb')->where(function($query) use ($id) {
                $query->where('bpbno', $id)->orWhere('bpbno_int', $id);
            })->first();

            $realBpbno    = $headerBpb ? $headerBpb->bpbno : $id;
            $realBpbnoInt = $headerBpb ? $headerBpb->bpbno_int : '';

            $ceisaRec = DB::connection('mysql_sb')->table('bpb_ceisa')
                ->where('bpbno', $id)->orWhere('bpbno_int', $id)->first();

            // Ambil nomor aju dari input, jika kosong gunakan dari record sebelumnya (bila ada)
            $inputNomorAju = $request->input('nomorAju', '');

            $payloadJson = json_encode($draft);

            if ($ceisaRec) {
                DB::connection('mysql_sb')->table('bpb_ceisa')
                    ->where('id', $ceisaRec->id)
                    ->update([
                        'bpbno'        => $realBpbno,
                        'bpbno_int'    => $realBpbnoInt,
                        'nomor_aju'    => $inputNomorAju ?: $ceisaRec->nomor_aju,
                        'payload_json' => $payloadJson,
                        'jenis_bc'     => '2.6.1',
                        'updated_at'   => Carbon::now()
                    ]);
            } else {
                DB::connection('mysql_sb')->table('bpb_ceisa')->insert([
                    'bpbno'        => $realBpbno,
                    'bpbno_int'    => $realBpbnoInt,
                    'nomor_aju'    => $inputNomorAju,
                    'jenis_bc'     => '2.6.1',
                    'payload_json' => $payloadJson,
                    'status'       => 0,
                    'created_at'   => Carbon::now(),
                    'updated_at'   => Carbon::now()
                ]);
            }

            DB::connection('mysql_sb')->commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Draft BC 2.6.1 berhasil disimpan.'
            ]);

        } catch (\Exception $e) {
            DB::connection('mysql_sb')->rollBack();
            Log::error('Error Update Draft BC 2.6.1: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
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
            $draftRow = $db->table('bpb_ceisa')->where('bpbno', $id)->orWhere('bpbno_int', $id)->first();
            if (!$draftRow || empty($draftRow->payload_json)) {
                throw new \Exception('Draft kosong. Simpan draft terlebih dahulu.');
            }

            $draft = json_decode($draftRow->payload_json, true);
            $nomorAju = $draftRow->nomor_aju ?? $this->generateNomorAju($db);

            // --- Pemetaan Dokumen ---
            $payloadDokumen = [];
            foreach (($draft['dok'] ?? []) as $index => $d) {
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

            // --- Pemetaan Entitas ---
            $entitasDraft = $draft['entitas'] ?? [];
            $payloadEntitas = [];

            // 1. Pengusaha TPB (Kode 3)
            if (!empty($entitasDraft['tpb'])) {
                $e = $entitasDraft['tpb'];
                $payloadEntitas[] = [
                    'seriEntitas'        => 1,
                    'kodeEntitas'        => '3',
                    'kodeJenisApi'       => $e['jenisApi'] ?? '02',
                    'kodeJenisIdentitas' => !empty($e['nomorIdentitas']) && strlen(str_replace(['.', '-'], '', $e['nomorIdentitas'])) == 16 ? '6' : '5',
                    'kodeStatus'         => $e['status'] ?? '5',
                    'namaEntitas'        => $e['namaEntitas'] ?? $e['nama'] ?? '',
                    'nibEntitas'         => $e['nibEntitas'] ?? $e['nib'] ?? '',
                    'nomorIdentitas'     => str_replace(['.', '-'], '', $e['nomorIdentitas'] ?? ''),
                    'nomorIjinEntitas'   => $e['nomorIjinEntitas'] ?? $e['nomorIjin'] ?? '',
                    'alamatEntitas'      => $e['alamatEntitas'] ?? $e['alamat'] ?? '',
                    'tanggalIjinEntitas' => $e['tanggalIjinEntitas'] ?? $e['tanggalIjin'] ?? date('Y-m-d')
                ];
            }

            // 2. Pemilik Barang (Kode 7)
            if (!empty($entitasDraft['pemilik'])) {
                $e = $entitasDraft['pemilik'];
                $payloadEntitas[] = [
                    'seriEntitas'        => 2,
                    'kodeEntitas'        => '7',
                    'kodeJenisApi'       => $e['jenisApi'] ?? '02',
                    'kodeJenisIdentitas' => !empty($e['nomorIdentitas']) && strlen(str_replace(['.', '-'], '', $e['nomorIdentitas'])) == 16 ? '6' : '5',
                    'kodeStatus'         => $e['status'] ?? '5',
                    'namaEntitas'        => $e['namaEntitas'] ?? $e['nama'] ?? '',
                    'nibEntitas'         => $e['nibEntitas'] ?? $e['nib'] ?? '',
                    'nomorIdentitas'     => str_replace(['.', '-'], '', $e['nomorIdentitas'] ?? ''),
                    'nomorIjinEntitas'   => $e['nomorIjinEntitas'] ?? $e['nomorIjin'] ?? '',
                    'alamatEntitas'      => $e['alamatEntitas'] ?? $e['alamat'] ?? '',
                    'tanggalIjinEntitas' => $e['tanggalIjinEntitas'] ?? $e['tanggalIjin'] ?? date('Y-m-d')
                ];
            }

            // 3. Penerima Barang (Kode 8)
            if (!empty($entitasDraft['penerima'])) {
                $e = $entitasDraft['penerima'];
                $payloadEntitas[] = [
                    'seriEntitas'        => 3,
                    'kodeEntitas'        => '8',
                    'kodeJenisApi'       => $e['jenisApi'] ?? '02',
                    'kodeJenisIdentitas' => !empty($e['nomorIdentitas']) && strlen(str_replace(['.', '-'], '', $e['nomorIdentitas'])) == 16 ? '6' : '5',
                    'kodeStatus'         => $e['status'] ?? '5',
                    'namaEntitas'        => $e['namaEntitas'] ?? $e['nama'] ?? '',
                    'nibEntitas'         => $e['nibEntitas'] ?? $e['nib'] ?? '',
                    'nomorIdentitas'     => str_replace(['.', '-'], '', $e['nomorIdentitas'] ?? ''),
                    'nomorIjinEntitas'   => $e['nomorIjinEntitas'] ?? $e['nomorIjin'] ?? '',
                    'alamatEntitas'      => $e['alamatEntitas'] ?? $e['alamat'] ?? '',
                    'tanggalIjinEntitas' => $e['tanggalIjinEntitas'] ?? $e['tanggalIjin'] ?? date('Y-m-d')
                ];
            }

            // --- Pemetaan Kemasan ---
            $payloadKemasan = [];
            foreach (($draft['kemasan'] ?? []) as $index => $k) {
                $payloadKemasan[] = [
                    'jumlahKemasan'    => (float) ($k['jumlahKemasan'] ?? 0),
                    'kodeJenisKemasan' => $k['kodeJenisKemasan'] ?? '',
                    'merkKemasan'      => $k['merkKemasan'] ?? '-',
                    'seriKemasan'      => (int) ($k['seriKemasan'] ?? ($index + 1)),
                ];
            }

            // --- Pemetaan Kontainer ---
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

            // --- Pemetaan Pengangkut ---
            $payloadPengangkut = [];
            foreach (($draft['pengangkut'] ?? []) as $index => $p) {
                $payloadPengangkut[] = [
                    'idPengangkut'   => "ANG" . str_pad($index + 1, 4, "0", STR_PAD_LEFT),
                    'kodeCaraAngkut' => $p['kodeCaraAngkut'] ?? '',
                    'seriPengangkut' => (int) ($p['seriPengangkut'] ?? ($index + 1)),
                ];
            }

            // --- Pemetaan Jaminan ---
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

            // --- Pemetaan Barang ---
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
                    'kodeDokumen'       => $b['kodeDokumen'] ?? '20', // Default BC 2.0 atau BC lainnya
                    'kodeJenisKemasan'  => $b['kodeJenisKemasan'] ?? '',
                    'kodeNegaraAsal'    => $b['kodeNegaraAsal'] ?? '',
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

                    'bahanBaku'         => $payloadBahanBaku
                ];
            }

            // Pungutan
            $payloadPungutan = [];
            foreach (($draft['pungutan'] ?? []) as $index => $p) {
                if (!empty($p['kodeJenisPungutan'])) {
                    $payloadPungutan[] = [
                        'idPungutan'          => "PUN" . str_pad($index + 1, 4, "0", STR_PAD_LEFT),
                        'kodeFasilitasTarif'  => $p['kodeFasilitasTarif'] ?? '1',
                        'kodeJenisPungutan'   => $p['kodeJenisPungutan'] ?? '',
                        'nilaiPungutan'       => (float) ($p['nilaiPungutan'] ?? 0),
                    ];
                }
            }

            $finalPayload = [
                'asalData'            => 'S',
                'asuransi'            => 0,
                'biayaTambahan'       => 0,
                'biayaPengurang'      => 0,
                'bruto'               => (float) ($draft['bruto'] ?? 0),
                'cif'                 => (float) ($draft['nilaiCif'] ?? 0),
                'disclaimer'          => "1",
                'freight'             => 0,
                'hargaPenyerahan'     => 0,
                'jabatanTtd'          => $draft['jabatanTtd'] ?? '-',
                'jumlahKontainer'     => count($payloadKontainer),
                'kodeDokumen'         => '261',
                'kodeKantor'          => $draft['kantorPabean'] ?? '',
                'kodeTujuanPengiriman'=> $draft['tujuanPengiriman'] ?? '',
                'kodeValuta'          => $draft['valuta'] ?? 'IDR',
                'kotaTtd'             => $draft['kotaTtd'] ?? '-',
                'namaTtd'             => $draft['namaTtd'] ?? '-',
                'ndpbm'               => (float) ($draft['ndpbm'] ?? 0),
                'netto'               => (float) ($draft['netto'] ?? 0),
                'nik'                 => '', // Harusnya ditarik dari profile, biarkan string kosong
                'nilaiBarang'         => (float) ($draft['nilaiPabean'] ?? 0),
                'nomorAju'            => $nomorAju,
                'seri'                => 0,
                'tanggalAju'          => date('Y-m-d'),
                'tanggalTtd'          => $draft['tanggalTtd'] ?? date('Y-m-d'),
                'tempatStuffing'      => '',
                'tglAkhirBerlaku'     => date('Y-m-d'),
                'tglAwalBerlaku'      => date('Y-m-d'),
                'totalDanaSawit'      => 0,
                'uangMuka'            => 0,
                'vd'                  => 0,

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

            Log::info('Kirim BC 2.6.1 CEISA Payload: ', $finalPayload);

            $responseCeisa = $this->ceisaService->kirimDokumenBc261($finalPayload);

            if ($responseCeisa['successful']) {
                $db->table('bpb_ceisa')->where('bpbno', $id)->orWhere('bpbno_int', $id)->update([
                    'nomor_aju'   => $nomorAju,
                    'tanggal_aju' => Carbon::now()->format('Y-m-d'),
                    'status'      => 1,
                    'updated_at'  => Carbon::now()
                ]);

                return response()->json([
                    'status'         => 200,
                    'message'        => 'Dokumen BC 2.6.1 berhasil dikirim ke CEISA!',
                    'data_payload'   => $finalPayload,
                    'ceisa_response' => $responseCeisa['body']
                ]);
            } else {
                return response()->json([
                    'status'      => $responseCeisa['status_code'],
                    'message'     => 'Gagal mengirim BC 2.6.1 ke CEISA.',
                    'ceisa_error' => $responseCeisa['body']
                ], $responseCeisa['status_code']);
            }

        } catch (\Exception $e) {
            Log::error('Error Send CEISA BC 2.6.1: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
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
        $prefix      = '000261NIW3452';

        $lastCeisa = $db->table('bpb_ceisa')
            ->where('nomor_aju', 'like', $prefix . $currentYear . '%')
            ->where('jenis_bc', '2.6.1')
            ->orderBy('nomor_aju', 'desc')
            ->first();

        $localSeq = 0;
        if ($lastCeisa && $lastCeisa->nomor_aju && strlen($lastCeisa->nomor_aju) === 26) {
            $localSeq = (int) substr($lastCeisa->nomor_aju, -5);
        }

        $ceisaSeq = $this->ceisaService->getLastSequenceFromCeisa($prefix . $currentYear, '261');

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
