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
            '10' => 'RKSP',
            '11' => 'MANIFES',
            '16' => 'BC 1.6 - PEMBERITAHUAN PABEAN PENGELUARAN BARANG DARI KAWASAN PABEAN UNTUK DITIMBUN DI PUSAT LOGISTIK BERIKAT',
            '20' => 'BC 2.0 - PEMBERITAHUAN IMPOR BARANG',
            '21' => 'PIBK/IMPOR KHUSUS',
            '23' => 'BC 2.3 - PEMBERITAHUAN IMPOR BARANG UNTUK DITIMBUN DI TEMPAT PENIMBUNAN BERIKAT',
            '25' => 'BC 2.5 - PEMBERITAHUAN IMPOR BARANG DARI TEMPAT PENIMBUNAN BERIKAT',
            '27' => 'BC 2.7 - PEMBERITAHUAN PENGELUARAN UNTUK DIANGKUT DARI TEMPAT PENIMBUNAN BERIKAT KE TEMPAT PENIMBUNAN BERIKAT LAINNYA',
            '28' => 'BC 2.8 - PEMBERITAHUAN IMPOR BARANG DARI PUSAT LOGISTIK BERIKAT',
            '30' => 'BC 3.0 - PEMBERITAHUAN EKSPOR NARAMG',
            '33' => 'BC 3.3 - PEMBERITAHUAN EKSPOR BARANG MELALUI/DARI PUSAT LOGISTIK BERIKAT',
            '40' => 'BC 4.0 - PEMBERITAHUAN PEMASUKAN BARANG ASAL TEMPAT LAIN DALAM DAERAH PABEAN KE TEMPAT PENIMBUNAN BERIKAT',
            '41' => 'BC 4.1 - PEMBERITAHUAN PENGELUARAN KEMBALI BARANG ASAL TEMPAT LAIN DALAM DAERAH PABEAN DARI TEMPAT PENIMBUNAN BERIKAT',
            '50' => 'KITE',
            '51' => 'FTZ 01',
            '52' => 'FTZ 02',
            '53' => 'FTZ 03',
            '65' => 'BC 1.1 KONSOLIDASI PJT',
            '001' => 'COA',
            '003' => 'Sertifikat Uji Tipe, Data Tingkat Konsumsi BBM, dan Data Uji Emisi CO2 dari pabrikan atau instansi terkait',
            '012' => 'MILL Certificate',
            '053' => 'MSDS',
            '111' => 'Bank Devisa Hasil Ekspor (DHE)',
            '161' => 'PPB - PEMBERITAHUAN PERPINDAHAN BARANG ANTAR TEMPAT PENIMBUNAN DALAM SATU PUSAT LOGISTIK BERIKAT',
            '167' => 'Spesifikasi Barang',
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
            '299' => 'Foto Barang/Lainnya',
            '302' => 'CN Ekspor',
            '315' => 'KONTRAK',
            '331' => 'P3BET - PEMBERITAHUAN PENGGABUNGAN DAN PEMECAHAN BARANG EKSPOR DAN TRANSHIPMENT',
            '343' => 'SHIPING ORDER',
            '370' => 'PURCHASE ORDER',
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
            '493' => 'Rekening Koran',
            '500' => 'MOU PDE (Eksportir)',
            '511' => 'FTZ-01 PEMASUKAN DARI LUAR DAERAH PABEAN (IMPOR)',
            '512' => 'FTZ-01 PENGELUARAN KE LUAR DAERAH PABEAN (EKSPOR)',
            '513' => 'FTZ-01 PENGELUARAN KE TEMPAT LAIN DALAM DAERAH PABEAN',
            '521' => 'FTZ-02 PEMASUKAN ANTAR FREE TRADE ZONE DAN KAWASAN BERIKAT',
            '522' => 'FTZ-02 PENGELUARAN ANTAR FREE TRADE ZONE DAN KAWASAN BERIKAT',
            '530' => 'Polis',
            '531' => 'FTZ-03 PEMASUKAN DARI TEMPAT LAIN DALAM DAERAH PABEAN',
            '575' => 'Bukti Bayar/Tagihan Insurance',
            '640' => 'DELIVERY ORDER',
            '666' => 'Pengecualian Dengan Surat Keputusan',
            '704' => 'MASTER B/L',
            '705' => 'B/L',
            '727' => 'Brosur/Katalog',
            '740' => 'AWB',
            '741' => 'MASTER AWB',
            '780' => 'Bukti Bayar/Tagihan Freight',
            '800' => 'SERTIFIKAT ALAT PERANGKAT TELEKOM/POSTEL',
            '803' => 'SATS LN / DEPHUT',
            '805' => 'REGISTRASI B3 / KLH',
            '808' => 'IJIN IMPOR / POLRI',
            '809' => 'SIE',
            '810' => 'SM/SPM',
            '811' => 'Sertifikat Legalitas Kayu (Dok.V-Legal)',
            '812' => 'Dok. Impor (PIB)',
            '813' => 'DOK. CUKAI (CK)',
            '814' => 'SPEK IJIN EKSPOR BERKALA',
            '815' => 'SPEK IJIN TATA NIAGA EKSPOR',
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
            '862' => 'SPEK USDFS',
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
            '912' => 'SPEK FASILITAS BKPM',
            '913' => 'SPEK FASILITAS PERTAMBANGAN',
            '914' => 'KITE IKM',
            '915' => 'SPEK Fasilitas Impor Sementara',
            '917' => 'BPBC / BPPAI',
            '918' => 'SK LABEL BAHASA INDONESIA',
            '919' => 'SK Bermotor',
            '920' => 'SPEK TPB',
            '922' => 'SPEK Fasilitas Impor Sementara Returnable Package',
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
            '964' => 'SURAT PERINTAH MASUK INSTALASI KARANTINA ATAU TEMPAT LAIN',
            '965' => 'SURAT PERINTAH PEMINDAHAN MEDIA PEMBAWA (SP2MP)',
            '966' => 'SURAT KETERANGAN MEDIA PEMBAWA LAIN',
            '967' => 'SERTIFIKAT PELEPASAN',
            '968' => 'SURAT KETERANGAN KARANTINA',
            '969' => 'SURAT KETERANGAN HASIL PENGAWASAN',
            '970' => 'Sertifikat Kesehatan Hewan',
            '971' => 'Sertifikat Sanitasi Produk Hewan',
            '972' => 'Sertifikat Kesehatan Ikan dan Produk Ikan (Ekspor)',
            '973' => 'SERTIFIKAT KESEHATAN TUMBUHAN UNTUK EKSPOR',
            '974' => 'SERTIFIKAT KESEHATAN TUMBUHAN UNTUK RE-EKSPOR',
            '975' => 'SERTIFIKAT EKSPOR UNTUK PRODUK TUMBUHAN',
            '981' => 'DNP dan Dokumen Pendukungnya/ INP/ Informasi Komponen Biaya',
            '982' => 'Surat Keterangan',
            '983' => 'Surat Pernyataan',
            '985' => 'Bukti Transfer',
            '993' => 'SURAT IJIN MENTERI PERTANIAN',
            '994' => 'BUKTI PENERIMAAN JAMINAN (BPJ)',
            '995' => 'STBS / SSP-E (PAJAK EKSPOR)',
            '996' => 'SRT SANGGUP BAYAR (SSB)',
            '997' => 'COSTOMS BOND / STTJ',
            '998' => 'SPEK FASILITAS KEMUDAHAN EKSPOR',
            '999' => 'LAINNYA',
            '03001' => 'Izin Prinsip Pendirian Kawasan Berikat Sebelum Fisik Bangunan Berdiri',
            '03002' => 'Keputusan Penetapan Tempat Sebagai Kawasan Berikat Dan Pemberian Izin Penyelenggara Kawasan Berikat',
            '03003' => 'Persetujuan Penetapan Tempat Sebagai Kawasan Berikat Dan Pemberian Izin Penyelenggara Kawasan Berikat Sekaligus Izin Pengusaha Kawasan Berikat',
            '03004' => 'Izin PDKB',
            '03005' => 'Perpanjangan Penetapan Tempat Sebagai Kawasan Berikat Dan Izin Penyelenggara Kawasan Berikat, Izin Pengusaha Kawasan Berikat, Atau Izin PDKB Sebelum Jangka Waktu Izin Tersebut Berakhir',
            '03006' => 'Perubahan Izin Penyelenggara Kawasan Berikat, Izin Pengusaha Kawasan Berikat, Atau Izin PDKB (Terdapat Perubahan Nama Perusahaan Yang Bukan Dikarenakan Merger Atau Diakuisisi, Jenis Hasil Produksi, Atau Luas Kawasan Berikat)',
            '03007' => 'Perubahan Keputusan Izin Penyelenggara Kawasan Berikat, Izin Pengusaha Kawasan Berikat, Atau Izin PDKB',
            '03008' => 'Pemberian Izin Penambahan Pintu Khusus Pemasukan Dan Pengeluaran Barang Di Kawasan Berikat',
            '03009' => 'Pemberian Izin Penambahan Pintu Khusus Orang Di Kawasan Berikat',
            '03010' => 'Persetujuan Pemasukan Barang Dari Kawasan Bebas Ke Kawasan Berikat',
            '03011' => 'Persetujuan Pemasukan Barang Modal Dari Luar Daerah Pabean',
            '03012' => 'Persetujuan Pemasukan Barang Modal Dari Kawasan Berikat Lain',
            '03013' => 'Persetujuan Pemasukan Barang Jadi Asal Luar Daerah Pabean Untuk Digabungkan Dengan Hasil Produksi Utama Kawasan Berikat',
            '03014' => 'Persetujuan Pemasukan Peralatan Perkantoran Asal Luar Daerah Pabean Ke Kawasan Berikat',
            '03015' => 'Persetujuan Pemasukan Barang Contoh Asal Luar Daerah Pabean',
            '03016' => 'Persetujuan Pembebasan Bea Masuk Untuk Barang Contoh Yang Akan Dikeluarkan Ke Tempat Lain Dalam Daerah Pabean',
            '03017' => 'Persetujuan Mengeluarkan Hasil Produksi Kawasan Berikat Ke Tempat Penyelenggaraan Pameran Berikat (TPPB)',
            '03018' => 'Persetujuan Untuk Mengeluarkan Bahan Baku Dan/Atau Bahan Rusak Dan/Atau Apkir (Reject) Yang Sama Sekali Tidak Diproses Ke Gudang Berikat Asal Barang',
            '03019' => 'Persetujuan Untuk Mengeluarkan Barang Dan/Atau Bahan Rusak Dan/Atau Apkir (Reject) Asal Tlddp Ke TLDDP',
            '03020' => 'Persetujuan Pengeluaran Bahan Baku/Sisa Bahan Baku Asal Impor Untuk Direekspor',
            '03021' => 'Persetujuan Pengeluaran Bahan Baku Dan/Atau Sisa Bahan Baku Asal Luar Daerah Pabean Ke Kawasan Berikat Lain',
            '03022' => 'Persetujuan Pengeluaran Bahan Baku Dan/Atau Sisa Bahan Baku Asal Luar Daerah Pabean Ke Perusahaan Industri Di TLDDP',
            '03023' => 'Persetujuan Pemindahtanganan Barang Selain Hasil Produksi Dalam Rangka Saling Melengkapi Kebutuhan Dalam Proses Produksi Atau Peningkatan Produksi Ke Kawasan Berikat Lain Dalam Satu Manajemen',
            '03024' => 'Persetujuan Pemindahtanganan Barang Selain Hasil Produksi Dalam Rangka Saling Melengkapi Kebutuhan Dalam Proses Produksi Atau Peningkatan Produksi Ke Kawasan Berikat Lain Dalam Satu PKB',
            '03025' => 'Persetujuan Pemindahtanganan Barang Selain Hasil Produksi Dalam Rangka Saling Melengkapi Kebutuhan Dalam Proses Produksi Atau Peningkatan Produksi Ke Kawasan Berikat Lainnya',
            '03026' => 'Persetujuan Pengeluaran Barang Modal Asal Impor Yang Belum Dibayar BM-nya Untuk Direekspor',
            '03027' => 'Persetujuan Pengeluaran Barang Modal Asal Impor Yang Belum Diselesaikan Kewajiban BM-nya Ke Kawasan Berikat Lain Setelah Jangka Waktu 2 (Dua) Tahun Sejak Diimpor Dan Telahdipergunakan Di Kawasan Berikat',
            '03028' => 'Persetujuan Pengeluaran Barang Modal Asal Impor Yang Belum Diselesaikan Kewajiban BM Ke Tempat Lain Dalam Daerah Pabean Sebelum Jangka Waktu 4 (Empat) Tahun Sejak Diimpor, Dan Telah Dipergunakan Di Kawasan Berikat',
            '03029' => 'Keputusan Pembebasan BM Atas Pengeluaran Barang Modal Asal Impor Yang Belum Diselesaikan Kewajiban Pembayaran Bm Ke TLDDP Setelah Jangka Waktu 4 (Empat) Tahun Sejak Diimpor, Dan Telah Dipergunakan Di Kawasan Berikat',
            '03030' => 'Persetujuan Pengeluaran Peralatan Perkantoran Asal Impor Yang Belum Lunas BM Untuk Direekspor',
            '03031' => 'Persetujuan Pengeluaran Peralatan Perkantoran Asal Impor Yang Belum Diselesaikan Kewajiban Pembayaran Bm Ke Kawasan Berikat Lain Setelah Dipergunakan Di Kawasan Berikat',
            '03032' => 'Persetujuan Pengeluaran Peralatan Perkantoran Asal Impor Yang Belum Diselesaikan Kewajiban Pembayaran Bm Ke TLDDP Sebelum Jangka Waktu 4 (Empat) Tahun Sejak Diimpor, Dan Telah Dipergunakan Di Kawasan Berikat Yang Bersangkutan',
            '03033' => 'Persetujuan Pengeluaran Peralatan Perkantoran Asal Impor Yang Belum Diselesaikan Kewajiban Pembayaran Bm Ke TLDDP Setelah Jangka Waktu 4 (Empat) Tahun Sejak Diimpor, Dan Telah Dipergunakan Di Kawasan Berikat',
            '03034' => 'Persetujuan Untuk Memindahtangankan Barang Modal Dan/Atau Peralatan Perkantoran Yang Telah Dilunasi BM Dan PDRI Pada Saat Pemasukan Ke Kawasan Berikat',
            '03035' => 'Persetujuan Untuk Memindahtangankan Barang Modal Asal Tempat Lain Dalam Daerah Pabean',
            '03036' => 'Persetujuan Pengeluaran Barang Modal Untuk Perbaikan/Reparasi Ke Luar Daerah Pabean',
            '03037' => 'Persetujuan Pengeluaran Barang Modal Untuk Perbaikan/Reparasi Ke TLDDP',
            '03038' => 'Persetujuan Pengeluaran Barang Modal Untuk Perbaikan/Reparasi Ke KB Lain',
            '03039' => 'Persetujuan Subkontrak Kurang Dari 60 (Enam Puluh) Hari Ke TLDDP',
            '03040' => 'Persetujuan Subkontrak Kurang Dari 60 (Enam Puluh) Hari Ke KB Lain',
            '03041' => 'Persetujuan Subkontrak Lebih Dari 60 (Enam Puluh) Hari Ke TLDDP',
            '03042' => 'Persetujuan Subkontrak Lebih Dari 60 (Enam Puluh) Hari Ke PDKB Lain',
            '03043' => 'Persetujuan Meminjamkan Mesin/Cetakan (Moulding) Ke KB Lain Dalam Rangka Subkontrak',
            '03044' => 'Persetujuan Meminjamkan Mesin/Cetakan (Moulding) Ke KB Lain Bukan Dalam Rangka Subkontrak',
            '03045' => 'Persetujuan Meminjamkan Mesin/Cetakan (Moulding) Ke TLDDP Dalam Rangka Subkontrak',
            '03046' => 'Persetujuan Meminjamkan Mesin/Cetakan (Moulding) Ke TLDDP Bukan Dalam Rangka Subkontrak',
            '03047' => 'Persetujuan Perpanjangan Meminjamkan Mesin Dan/Atau Cetakan (Moulding) Ke PDKB Lain Dalam Rangka Subkontrak',
            '03048' => 'Persetujuan Perpanjangan Meminjamkan Mesin Dan/Atau Cetakan (Moulding) Ke PDKB Lain Bukan Dalam Rangka Subkontrak',
            '03049' => 'Persetujuan Perpanjangan Meminjamkan Mesin Dan/Atau Cetakan (Moulding) Ke TLDDP Dalam Rangka Subkontrak',
            '03050' => 'Persetujuan Perpanjangan Meminjamkan Mesin Dan/Atau Cetakan (Moulding) Ke TLDDP Selain Dalam Rangka Subkontrak',
            '03051' => 'Persetujuan Peminjaman Mesin Atau Cetakan (Moulding) Yang Melebihi Jangka Waktu',
            '03052' => 'Persetujuan Pemusnahan Atas Barangbarang Yang Busuk Dan/Atau Yang Karena Sifat Dan Bentuknya Dapat Dimusnahkan',
            '03053' => 'Persetujuan Perusakan Atas Barang Asal Luar Daerah Pabean Yang Karena Sifat Dan Bentuknya Tidak Dapat Dimusnahkan',
            '03054' => 'Persetujuan Menerima Subkontrak Dari TLDDP',
            '03055' => 'Persetujuan Peminjaman Mesin/Cetakan (Moulding) Dari TLDDP Dalam Rangka Subkontrak',
            '03056' => 'Persetujuan Peminjaman Mesin/Cetakan (Moulding) Dari TLDDP Bukan Dalam Rangka Subkontrak',
            '03057' => 'Persetujuan Peminjaman Mesin/Peralatan Pabrik Dari TLDDP',
            '03060' => 'Persetujuan Pemasukan Barang Modal Berupa Peralatan Pabrik Dari Luar Daerah Pabean',
            '03061' => 'Persetujuan Pemasukan Barang Modal Berupa Suku Cadang Dari Luar Daerah Pabean Yang Dimasukkan Tidak Bersamaan Dengan Barang Modal',
            '03062' => 'Persetujuan Pemasukan Kembali (Reimpor) Barang Hasil Produksi Asal TPB',
            '03063' => 'Persetujuan Pemasukan Kembali (Reimpor) Barang Modal Setelah Perbaikan/Reparasi Dari Luar Daerah Pabean',
            '03064' => 'Persetujuan Perpanjangan Jangka Waktu Pengeluaran Barang Modal Keperluan Perbaikan/Reparasi Tujuan TLDDP',
            '03065' => 'Persetujuan Pengeluaran Barang Contoh/Sampel KB Dengan Tujuan TLDDP',
            '03066' => 'Rekomendasi Meminjamkan Barang Modal Ke TLDDP Dalam Rangka Subkontrak Atau Bukan Lebih Dari 6 Bulan',
        ];

        $pengangkuts = !empty($dataDetail['pengangkut']) ? $dataDetail['pengangkut'] : [
            ['namaPengangkut' => '', 'nomorPengangkut' => '', 'kodeCaraAngkut' => '3', 'kodeBendera' => 'ID']
        ];
        $kontainers  = $dataDetail['kontainer']  ?? [];
        $kemasans    = !empty($dataDetail['kemasan']) ? $dataDetail['kemasan'] : [
            ['jumlahKemasan' => '', 'kodeJenisKemasan' => 'CT', 'merkKemasan' => '-']
        ];

        $listKategoriBarang = [
            '23' => [
                '01' => 'BARANG UNTUK DITIMBUN',
                '02' => 'BARANG UNTUK KEPERLUAN PENGUSAHAAN',
                '11' => 'UNTUK BAHAN BAKU/BAHAN PENOLONG',
                '12' => 'UNTUK PENGEMAS/ALAT BANTU PENGEMAS',
                '13' => 'UNTUK PERALATAN UNTUK PEMBANGUNAN, PERLUASAN, ATAU KONSTRUKSI KB',
                '14' => 'UNTUK BARANG MODAL DAN/ATAU SPAREPARTS BARANG MODAL',
                '15' => 'UNTUK BARANG CONTOH',
                '16' => 'UNTUK BARANG JADI GUNA DIGABUNG DENGAN HASIL PRODUKSI',
                '17' => 'UNTUK BARANG REIMPOR',
                '18' => 'UNTUK PERALATAN PERKANTORAN',
                '19' => 'BARANG UNTUK KEPERLUAN PENANGANAN COVID19',
                '21' => 'UNTUK BARANG YANG DITIMBUN DI GB',
                '22' => 'UNTUK BARANG REIMPOR',
                '31' => 'UNTUK BARANG UNTUK DIPAMERKAN',
                '32' => 'UNTUK BARANG UNTUK MENDUKUNG KEPERLUAN PAMERAN',
                '33' => 'UNTUK BARANG REIMPOR',
                '41' => 'UNTUK BARANG YANG DITIMBUN DI TBB',
                '42' => 'UNTUK BARANG REIMPOR',
                '51' => 'UNTUK BARANG LELANG',
                '52' => 'UNTUK SPAREPARTS',
                '53' => 'UNTUK BARANG REIMPOR',
                '61' => 'UNTUK BARANG YANG DITIMBUN DI KDUB',
                '62' => 'UNTUK BARANG REIMPOR'
            ],
            '25' => [
                '1' => 'HASIL PRODUKSI',
                '2' => 'BAHAN BAKU',
                '3' => 'BARANG MODAL',
                '4' => 'PERALATAN KANTOR',
                '5' => 'SISA DARI PROSES PRODUKSI/LIMBAH (WASTE/SCRAP) DAN/ATAU SISA ATAU BEKAS PENGEMAS',
                '6' => 'BARANG YANG DITIMBUN UNTUK DIJUAL',
                '7' => 'BARANG YANG DIPAMERKAN UNTUK DIJUAL',
                '8' => 'BARANG LAINNYA',
                '9' => 'BARANG UNTUK KEPERLUAN PENANGANAN COVID19',
                '10' => 'BARANG CONTOH'
            ]
        ];

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
            'listJenisKemasan'      => $listJenisKemasan,
            'listSatuanBarang'      => $listSatuanBarang,
            'listKategoriBarang'    => $listKategoriBarang,
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
