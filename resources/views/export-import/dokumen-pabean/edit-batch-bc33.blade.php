@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<style>
    :root {
        --primary: #003366;
        --primary-light: #e8f0fa;
        --accent: #0080ff;
        --border: #dfe3e8;
        --muted: #6c757d;
        --danger: #dc3545;
        --success: #198754;
    }

    body { background-color: #f4f6f9; }

    /* ---------- TAB NAVIGASI ---------- */
    .nav-tabs {
        border-bottom: none;
        gap: 6px;
        flex-wrap: wrap;
        margin-bottom: 4px;
    }
    .nav-tabs .nav-item { margin-bottom: 6px; }
    .nav-tabs .nav-link {
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 8px 14px;
        font-size: 13px;
        color: #444;
        background: #fff;
        transition: all .2s ease;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .nav-tabs .nav-link i { font-size: 12px; opacity: .8; }
    .nav-tabs .nav-link.active {
        font-weight: 600;
        background-color: var(--primary) !important;
        color: #fff !important;
        border-color: var(--primary) !important;
        box-shadow: 0 3px 8px rgba(0,51,102,.25);
    }
    .nav-tabs .nav-link:not(.active):hover {
        background-color: var(--primary-light);
        border-color: var(--primary);
        color: var(--primary) !important;
    }
    /* badge kecil penanda status tab (opsional, isi lewat JS bila mau) */
    .nav-tabs .nav-link .tab-badge {
        font-size: 10px;
        background: var(--danger);
        color: #fff;
        border-radius: 10px;
        padding: 1px 6px;
        margin-left: 2px;
        display: none;
    }
    .nav-tabs .nav-link .tab-badge.show { display: inline-block; }

    /* ---------- KARTU SECTION ---------- */
    .card-sb { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
    .card.shadow-sm { border-radius: 10px; overflow: hidden; }
    .card-header {
        font-size: 13px;
        letter-spacing: .3px;
    }
    .section-title {
        font-size: 14px; font-weight: bold; color: #333;
        border-bottom: 2px solid var(--border);
        padding-bottom: 6px; margin-bottom: 16px; margin-top: 22px;
        display: flex; align-items: center; gap: 8px;
    }

    /* ---------- FORM ELEMENTS ---------- */
    .form-group label {
        font-size: 12.5px; font-weight: 600;
        margin-bottom: 3px; color: #333;
    }
    .form-group label.required::after {
        content: " *";
        color: var(--danger);
        font-weight: 700;
    }
    .form-control-sm {
        font-size: 13px;
        border-radius: 5px;
        border-color: var(--border);
    }
    .form-control-sm:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 .15rem rgba(0,128,255,.15);
    }
    .form-text-hint {
        font-size: 11px;
        color: var(--muted);
        margin-top: 2px;
        display: block;
    }

    /* ---------- FIELDSET / SUB-GROUP ---------- */
    fieldset.border {
        background: #fff;
        border-radius: 8px !important;
    }
    fieldset legend {
        font-size: 12.5px !important;
        background: #fff;
    }

    /* ---------- TABEL DINAMIS (dokumen, kemasan, kontainer, dll) ---------- */
    .table-sm thead th {
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: .3px;
        color: var(--muted);
        background: #f8f9fb;
        white-space: nowrap;
    }
    .table-sm tbody tr:hover { background-color: #f9fbff; }
    .btn-add-action {
        border-radius: 50%;
        width: 26px; height: 26px;
        display: inline-flex; align-items: center; justify-content: center;
    }

    /* ---------- ACCORDION DATA BARANG ---------- */
    .btn-collapse-barang {
        background-color: var(--primary) !important;
        border-radius: 6px;
    }
    .btn-collapse-barang .fw-bold { color: #fff !important; }
    .btn-collapse-barang:hover { filter: brightness(1.08); }
    .icon-collapse { color: #fff !important; }

    /* ---------- FOOTER STICKY ---------- */
    .card-footer.sticky-action {
        position: sticky;
        bottom: 0;
        z-index: 50;
        box-shadow: 0 -4px 10px rgba(0,0,0,.06);
    }

    /* ---------- ALERT MODE BATCH ---------- */
    .alert-warning.py-2 {
        border-radius: 8px;
        border-left: 4px solid #ffc107;
    }

    @media (max-width: 768px) {
        .nav-tabs .nav-link { font-size: 12px; padding: 6px 10px; }
    }

    .card-pkb .card-body { padding: 20px 24px; }

    /* garis pemisah kolom kiri-kanan di desktop */
    .pkb-col-left { border-right: 1px solid var(--border, #e5e7eb); padding-right: 24px; }
    .pkb-col-right { padding-left: 24px; }
    @media (max-width: 767px) {
        .pkb-col-left { border-right: none; padding-right: 15px; border-bottom: 1px solid #e5e7eb; padding-bottom: 16px; margin-bottom: 16px; }
        .pkb-col-right { padding-left: 15px; }
    }

    .section-subtitle {
        font-size: 12px;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: .4px;
        margin: 0 0 16px;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 6px;
    }

    .pkb-label {
        display: block;
        font-size: 12.5px;
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }

    /* fix warna teks select yang kadang jadi cokelat/oranye bawaan browser */
    .card-pkb select.form-control-sm,
    .card-pkb select.form-control-sm option {
        color: #212529 !important;
    }
    .card-pkb select.form-control-sm:invalid,
    .card-pkb select.form-control-sm option[value=""] {
        color: #8a8f98 !important;
    }

    .card-pkb .form-control-sm {
        border-radius: 6px;
        border-color: #d3d6db;
    }
    .card-pkb .form-control-sm:focus {
        border-color: #0080ff;
        box-shadow: 0 0 0 3px rgba(0,128,255,.12);
    }
    .card-pkb textarea.form-control-sm { resize: vertical; }
</style>
@endsection

@section('content')
@php

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
        '630' => 'SURAT JALAN',
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
        '03066' => 'Rekomendasi Meminjamkan Barang Modal Ke TLDDP Dalam Rangka Subkontrak Atau Bukan Lebih Dari 6 Bulan'
    ];

    $entitasAman = [];
    if (isset($dataDetail['entitas']) && is_array($dataDetail['entitas'])) {
        foreach ($dataDetail['entitas'] as $e) {
            if (isset($e['kodeEntitas'])) {
                $entitasAman[$e['kodeEntitas']] = $e;
            }
        }
        $dataDetail['entitas'] = $entitasAman;
    }

    if (isset($dataDetail['dokumen']) && is_array($dataDetail['dokumen'])) {
        $dokMap = [];
        foreach ($dataDetail['dokumen'] as $d) {
            $dokMap[] = [
                'kode'  => $d['kodeDokumen'] ?? '',
                'nomor' => $d['nomorDokumen'] ?? '',
                'tgl'   => $d['tanggalDokumen'] ?? '',
                'fileName'  => $d['fileName'] ?? null
            ];
        }
        $dataDetail['dok'] = $dokMap;
    }

    $mapNamaTps = [
        // Tanjung Priok / Jakarta (040300)
        'KOJA' => 'KOJA - KSO BPK KOJA',
        'JICT' => 'JICT - PT JAKARTA INTERNATIONAL CONTAINER TERMINAL',
        '3T01' => '3T01 - PT MUSTIKA ALAM LESTARI (MAL)',
        '1T01' => '1T01 - PT PELABUHAN INDONESIA II CABANG TANJUNG PRIOK',
        '1T02' => '1T02 - TERMINAL 3 TANJUNG PRIOK',
        'KJT1' => 'KJT1 - TERMINAL PETIKEMAS KOJA',
        'NPCT' => 'NPCT - NEW PRIOK CONTAINER TERMINAL ONE (NPCT1)',
        'DWKA' => 'DWKA - PT DWIPA KHARISMA MITRA TANJUNG PRIOK',
        'AGTP' => 'AGTP - PT AIRIN TANJUNG PRIOK',
        'MIPR' => 'MIPR - PT MULTI INTIPARNA TANJUNG PRIOK',

        // Soekarno-Hatta / Tangerang / Jakarta (050100)
        'JASA' => 'JASA - PT JASA ANGKASA SEMESTA (JAS) CARGO SOEKARNO HATTA',
        'GARU' => 'GARU - PT GARUDA INDONESIA CARGO SOEKARNO HATTA',
        'UNPA' => 'UNPA - PT UNIAIR INDOTAMA CARGO SOEKARNO HATTA',
        'FEDX' => 'FEDX - PT FEDERAL EXPRESS SOEKARNO HATTA',
        'DHLX' => 'DHLX - PT BIROTIKA SEMESTA (DHL EXPRESS) SOEKARNO HATTA',
        'UPSX' => 'UPSX - PT UPS CARDIG INTERNATIONAL SOEKARNO HATTA',
        'TNTX' => 'TNTX - PT SKYLIFT CONSOLIDATOR (TNT EXPRESS) SOEKARNO HATTA',
        'GAPU' => 'GAPU - PT GAPURA ANGKASA CARGO SOEKARNO HATTA',
        'ANGK' => 'ANGK - PT ANGKASA PURA II CARGO SOEKARNO HATTA',

        // Tanjung Perak / Surabaya (070100)
        'TPS1' => 'TPS1 - PT TERMINAL PETIKEMAS SURABAYA (TPS)',
        'BJTI' => 'BJTI - PT BERLIAN JASA TERMINAL INDONESIA',
        'TTL1' => 'TTL1 - PT TERMINAL TELUK LAMONG',
        'MTPS' => 'MTPS - PT MIRAH TERMINAL PETIKEMAS SURABAYA',
        'DWKS' => 'DWKS - PT DWIPA KHARISMA MITRA SURABAYA',
        'ISPS' => 'ISPS - PT INDOLINE SURABAYA',

        // Juanda / Sidoarjo / Surabaya (070200)
        'JASJ' => 'JASJ - PT JASA ANGKASA SEMESTA (JAS) CARGO JUANDA',
        'GAPJ' => 'GAPJ - PT GAPURA ANGKASA CARGO JUANDA',
        'GARJ' => 'GARJ - PT GARUDA INDONESIA CARGO JUANDA',
        'DHLJ' => 'DHLJ - PT BIROTIKA SEMESTA (DHL) JUANDA',

        // Tanjung Emas / Semarang (060100)
        'TPK2' => 'TPK2 - TERMINAL PETIKEMAS SEMARANG (TPKS)',
        'SRIS' => 'SRIS - PT SARI RANA INDAH SEMARANG',
        'DHLS' => 'DHLS - PT BIROTIKA SEMESTA SEMARANG',
        'GAPM' => 'GAPM - PT GAPURA ANGKASA CARGO AHMAD YANI SEMARANG',

        // Belawan / Medan (010700)
        'BICT' => 'BICT - BELAWAN INTERNATIONAL CONTAINER TERMINAL',
        'TPKB' => 'TPKB - TERMINAL PETIKEMAS BELAWAN',
        'BTLP' => 'BTLP - PT BELAWAN TERMINAL LOGISTIK PERSERO',

        // Kualanamu / Medan (010800)
        'JASK' => 'JASK - PT JASA ANGKASA SEMESTA CARGO KUALANAMU',
        'GAPK' => 'GAPK - PT GAPURA ANGKASA CARGO KUALANAMU',
        'GARK' => 'GARK - PT GARUDA INDONESIA CARGO KUALANAMU',

        // Ngurah Rai / Denpasar / Bali (080100)
        'JASD' => 'JASD - PT JASA ANGKASA SEMESTA CARGO NGURAH RAI',
        'GAPD' => 'GAPD - PT GAPURA ANGKASA CARGO NGURAH RAI',
        'GARD' => 'GARD - PT GARUDA INDONESIA CARGO NGURAH RAI',

        // Batam / Kepulauan Riau (020100)
        'BTBP' => 'BTBP - PT BATAM PERSERO BEKAS / BATU AMPAR',
        'BICT2' => 'BICT2 - BATAM INTERNATIONAL CONTAINER TERMINAL',
        'DHLB' => 'DHLB - PT BIROTIKA SEMESTA BATAM',
        'CGKB2' => 'CGKB2 - TPS CARGO BANDARA HANG NADIM BATAM',

        // Makassar / Sulawesi Selatan (100100)
        'TPKM' => 'TPKM - TERMINAL PETIKEMAS MAKASSAR (PELINDO IV)',
        'GAPG' => 'GAPG - PT GAPURA ANGKASA CARGO SULTAN HASANUDDIN MAKASSAR',
        'GARM' => 'GARM - PT GARUDA INDONESIA CARGO MAKASSAR',

        // Balikpapan / Kalimantan Timur (120100)
        'KKT1' => 'KKT1 - PT KALTIM KARIANGAU TERMINAL (KKT) BALIKPAPAN',
        'GAPB' => 'GAPB - PT GAPURA ANGKASA CARGO SEPINGGAN BALIKPAPAN',

        // Cikarang / Bekasi (050300)
        'CDP1' => 'CDP1 - CIKARANG DRY PORT (PT CIKARANG INLAND PORT)',
        'MTB1' => 'MTB1 - PT MITRA TATA BUANA CIKARANG',

        // Bandung (050500)
        'BDRB' => 'BDRB - PT BHANDA GHARA REKSA (BGR) GEDEBAGE BANDUNG',
        'GDBG' => 'GDBG - TPS GEDEBAGE BANDUNG',
        'PTKB' => 'PTKB - TPS PT POS INDONESIA BANDUNG',
        'CGKB' => 'CGKB - TPS CARGO BANDARA HUSEIN SASTRANEGARA BANDUNG',

        // Tangerang / Serpong (050200)
        'BSDT' => 'BSDT - TPS BSD TANGERANG KOTA',
        'IKGT' => 'IKGT - PT INDO KOR GUNA TANGERANG',

        // Merak / Banten (040100)
        'IKPT' => 'IKPT - PT INDAH KIAT PULP & PAPER MERAK BANTEN',
        'CMPT' => 'CMPT - PT CIWANDAN MULTI PURPOSES TERMINAL MERAK',

        // Tanjung Pinang / Kepri (020200)
        'TPTP' => 'TPTP - TERMINAL PETIKEMAS TANJUNG PINANG',
        'KIPT' => 'KIPT - KIJANG PORT TERMINAL',

        // Palembang (030100)
        'BMTP2' => 'BMTP2 - BOOM BARU TERMINAL PETIKEMAS PALEMBANG (PELINDO II)',
        'GAPP' => 'GAPP - PT GAPURA ANGKASA CARGO PALEMBANG',

        // Lampung / Panjang (030400)
        'TPKP' => 'TPKP - TERMINAL PETIKEMAS PANJANG (PELINDO II)',
        'PJPG' => 'PJPG - PELABUHAN PANJANG',

        // Pontianak (130100)
        'TPKN' => 'TPKN - TERMINAL PETIKEMAS PONTIANAK (PELINDO II)',
        'SUPN' => 'SUPN - TPS CARGO BANDARA SUPADIO PONTIANAK',

        // Banjarmasin (130300)
        'TPPB' => 'TPPB - TERMINAL PETIKEMAS TRISAKTI BANJARMASIN (PELINDO III)',
        'BDJB' => 'BDJB - TPS CARGO BANDARA SYAMSUDIN NOOR BANJARMASIN',

        // Samarinda (120200)
        'PSMD' => 'PSMD - PALARAN SAMARINDA CONTAINER TERMINAL (PT PSP)',

        // Bitung / Manado (110100)
        'TPBI' => 'TPBI - TERMINAL PETIKEMAS BITUNG (PELINDO IV)',
        'MDCB' => 'MDCB - TPS CARGO BANDARA SAM RATULANGI MANADO',

        // Ambon (140100)
        'TPKA' => 'TPKA - TERMINAL PETIKEMAS AMBON (PELINDO IV)',
        'AMQB' => 'AMQB - TPS CARGO BANDARA PATTIMURA AMBON',

        // Jayapura / Papua (140200)
        'TPKJ' => 'TPKJ - TERMINAL PETIKEMAS JAYAPURA (PELINDO IV)',
        'DJJB' => 'DJJB - TPS CARGO BANDARA SENTANI JAYAPURA',

        // Sorong (140400)
        'TPKS2' => 'TPKS2 - TERMINAL PETIKEMAS SORONG (PELINDO IV)',

        // Kupang / NTT (080300)
        'TPKK' => 'TPKK - TERMINAL PETIKEMAS TENAU KUPANG (PELINDO III)',

        // Mataram / Lembar / NTB (080200)
        'TPML' => 'TPML - TERMINAL PETIKEMAS LEMBAR MATARAM (PELINDO III)',
    ];
    $tpsCode = $dataDetail['kodeTps'] ?? '';
    $tpsLabel = $mapNamaTps[$tpsCode] ?? ($dataDetail['namaTps'] ?? $tpsCode);
    $kodeLokasiTps = $dataDetail['kodeLokasiTps'] ?? '';
    $kodeLokasiTpsLabel = $mapNamaTps[$kodeLokasiTps] ?? ($dataDetail['namaTps'] ?? $kodeLokasiTps);
@endphp

<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">
            <i class="fas fa-edit"></i> BC 3.3 - PEMBERITAHUAN EKSPOR BARANG MELALUI/DARI PUSAT LOGISTIK BERIKAT
        </h5>
    </div>

    <form action="{{ route('dokumen-pabean-update_draft_batch_bc33', $batch_id) }}" method="POST" id="form-edit-ceisa">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="alert alert-warning py-2 mb-4">
                <strong>Mode Batch (BC 3.3)</strong><br>
                <strong>No. Transaksi Gabungan:</strong> {{ $batch_id }} <br>
                <strong>Supplier:</strong> {{ $header->supplier ?? '-' }}
                <input type="hidden" name="bpbno_int" value="{{ $header->bpbno_int }}">
                <input type="hidden" name="no_dokumen_merge" value="{{ $batch_id }}">
                <input type="hidden" name="kodeDokumen" value="33">
                <input type="hidden" name="asalData" value="S">
                <input type="hidden" name="disclaimer" value="1">
            </div>

            <ul class="nav nav-tabs" id="ceisaTab" role="tablist">
                <li class="nav-item"><a class="nav-link active" id="header-tab" data-toggle="tab" href="#tab-header" role="tab"><i class="fas fa-info-circle"></i> Header</a></li>
                <li class="nav-item"><a class="nav-link" id="entitas-tab" data-toggle="tab" href="#tab-entitas" role="tab"><i class="fas fa-users"></i> Entitas</a></li>
                <li class="nav-item"><a class="nav-link" id="dokumen-tab" data-toggle="tab" href="#tab-dokumen" role="tab"><i class="fas fa-file-alt"></i> Dokumen Pelengkap</a></li>
                <li class="nav-item"><a class="nav-link" id="pengangkut-tab" data-toggle="tab" href="#tab-pengangkut" role="tab"><i class="fas fa-truck"></i> Pengangkutan</a></li>
                <li class="nav-item"><a class="nav-link" id="kemasan-tab" data-toggle="tab" href="#tab-kemasan" role="tab"><i class="fas fa-box"></i> Kemasan & Peti Kemas</a></li>
                <li class="nav-item"><a class="nav-link" id="transaksi-tab" data-toggle="tab" href="#tab-transaksi" role="tab"><i class="fas fa-money-bill-wave"></i> Transaksi & Keuangan</a></li>
                <li class="nav-item"><a class="nav-link" id="barang-tab" data-toggle="tab" href="#tab-barang" role="tab"><i class="fas fa-boxes"></i> Data Barang <span class="badge badge-light ml-1">{{ count($items) }}</span></a></li>
                <li class="nav-item"><a class="nav-link" id="pungutan-tab" data-toggle="tab" href="#tab-pungutan" role="tab"><i class="fas fa-receipt"></i> Pungutan</a></li>
                <li class="nav-item"><a class="nav-link" id="pernyataan-tab" data-toggle="tab" href="#tab-pernyataan" role="tab"><i class="fas fa-signature"></i> Pernyataan</a></li>
            </ul>

            <div class="tab-content mt-3" id="ceisaTabContent">

                <div class="tab-pane fade show active" id="tab-header" role="tabpanel">
                    <div class="row">
                        <!-- Kolom 1 -->
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Pengajuan</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="text-sm">Nomor Aju</label>
                                        <input type="text" name="nomorAju" class="form-control form-control-sm fw-bold" value="{{ $nomorAju }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom 2 -->
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Kantor Pabean</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="text-sm ">Pelabuhan Muat Asal</label>
                                        <select name="kodePelMuatAsal" class="form-control form-control-sm select2-pelabuhan select2bs4">
                                            <option value="">Pilih Pelabuhan Muat Asal</option>
                                            @if(!empty($dataDetail['kodePelMuatAsal'] ?? $dataDetail['kodePelMuat'] ?? ''))
                                                <option value="{{ $dataDetail['kodePelMuatAsal'] ?? $dataDetail['kodePelMuat'] }}" selected>{{ $dataDetail['kodePelMuatAsal'] ?? $dataDetail['kodePelMuat'] }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-sm ">Kantor Pabean Muat</label>
                                        <select name="kodeKantorMuat" class="form-control form-control-sm select2bs4 ">
                                            <option value="">Pilih Kantor Pabean Muat</option>
                                            @foreach($kantorList as $val => $label)
                                                <option value="{{ $val }}" {{ ($dataDetail['kodeKantorMuat'] ?? '') == $val ? 'selected' : '' }}>{{ $val }} - {{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-sm ">Kantor Pabean Pengawasan</label>
                                        <select name="kodeKantor" class="form-control form-control-sm select2bs4 ">
                                            <option value="">Pilih Kantor Pabean Pengawasan</option>
                                            @foreach($kantorList as $val => $label)
                                                <option value="{{ $val }}" {{ ($dataDetail['kodeKantor'] ?? '050500') == $val ? 'selected' : '' }}>{{ $val }} - {{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-sm ">Kantor Asal</label>
                                        <select name="kodeKantorAsal" class="form-control form-control-sm select2bs4 ">
                                            <option value="">Pilih Kantor Asal</option>
                                            @foreach($kantorList as $val => $label)
                                                <option value="{{ $val }}" {{ ($dataDetail['kodeKantorAsal'] ?? '') == $val ? 'selected' : '' }}>{{ $val }} - {{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!-- Hidden legacy & CEISA 4.0 inputs -->
                                    <input type="hidden" name="kodeGudangAsal" value="{{ $dataDetail['kodeGudangAsal'] ?? $dataDetail['kodeGudangPlb'] ?? '' }}">
                                    <input type="hidden" name="tanggalMasuk" value="{{ $dataDetail['tanggalMasuk'] ?? '' }}">
                                    <input type="hidden" name="kodeLokasiTps" value="{{ $kodeLokasiTps ?? '' }}">
                                    <input type="hidden" name="kodeTps" value="{{ $dataDetail['kodeTps'] ?? $kodeLokasiTps ?? '' }}">
                                    <input type="hidden" name="kodeKantorEkspor" value="{{ $dataDetail['kodeKantorEkspor'] ?? $dataDetail['kodeKantorMuat'] ?? $dataDetail['kodeKantor'] ?? '050500' }}">
                                    <input type="hidden" name="_kodeKantorSKEP" value="{{ $dataDetail['_kodeKantorSKEP'] ?? $dataDetail['kodeKantorAsal'] ?? $dataDetail['kodeKantor'] ?? '050500' }}">
                                    <input type="hidden" name="idPkbe" value="{{ $dataDetail['idPkbe'] ?? ($header->bppbno ?? '') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Kolom 3 -->
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Keterangan Lain</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="text-sm ">Jenis Ekspor</label>
                                        <select name="kodeJenisEkspor" class="form-control form-control-sm select2bs4 ">
                                            @php $jenisEkspor = $dataDetail['kodeJenisEkspor'] ?? '' @endphp
                                            <option value="">Pilih Jenis Ekspor</option>
                                            <option value="1" {{ $jenisEkspor == '1' ? 'selected' : '' }}>1 - Ekspor Biasa</option>
                                            <option value="2" {{ $jenisEkspor == '2' ? 'selected' : '' }}>2 - Ekspor Sementara</option>
                                            <option value="4" {{ $jenisEkspor == '4' ? 'selected' : '' }}>4 - Ekspor Barang yang akan Diimpor Kembali</option>
                                            <option value="5" {{ $jenisEkspor == '5' ? 'selected' : '' }}>5 - Re Ekspor Lainnya</option>
                                            <option value="6" {{ $jenisEkspor == '6' ? 'selected' : '' }}>6 - Ekspor Barang Eks Impor Sementara</option>
                                            <option value="7" {{ $jenisEkspor == '7' ? 'selected' : '' }}>7 - Ekspor Gabungan</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-sm ">Kategori Ekspor</label>
                                        <select name="kodeKategoriEkspor" class="form-control form-control-sm select2bs4 ">
                                            @php $katEkspor = $dataDetail['kodeKategoriEkspor'] ?? '' @endphp
                                            <option value="">Pilih Kategori Ekspor</option>
                                            <option value="4" {{ $katEkspor == '4' ? 'selected' : '' }}>4 - Ekspor Dari - Asal KB</option>
                                            <option value="3" {{ $katEkspor == '3' ? 'selected' : '' }}>3 - Ekspor Melalui - Asal KB</option>
                                            <option value="2" {{ $katEkspor == '2' ? 'selected' : '' }}>2 - Ekspor Dari - Umum</option>
                                            <option value="1" {{ $katEkspor == '1' ? 'selected' : '' }}>1 - Ekspor Melalui - Umum</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-sm ">Cara Dagang</label>
                                        <select name="kodeCaraDagang" class="form-control form-control-sm select2bs4 ">
                                            @php $caraDagang = $dataDetail['kodeCaraDagang'] ?? '' @endphp
                                            <option value="">Pilih Cara Dagang</option>
                                            <option value="1" {{ $caraDagang == '1' ? 'selected' : '' }}>1 - Biasa</option>
                                            <option value="2" {{ $caraDagang == '2' ? 'selected' : '' }}>2 - IMB - Imbal Dagang</option>
                                            <option value="3" {{ $caraDagang == '3' ? 'selected' : '' }}>3 - PMK - Pembayaran dimuka</option>
                                            <option value="4" {{ $caraDagang == '4' ? 'selected' : '' }}>4 - KMD Bertahap</option>
                                            <option value="5" {{ $caraDagang == '5' ? 'selected' : '' }}>5 - KMD Tunai</option>
                                            <option value="6" {{ $caraDagang == '6' ? 'selected' : '' }}>6 - SLC - Sight Letter of Credit</option>
                                            <option value="7" {{ $caraDagang == '7' ? 'selected' : '' }}>7 - ULC - Usance Letter of Credit</option>
                                            <option value="8" {{ $caraDagang == '8' ? 'selected' : '' }}>8 - RLC - Red Clause Letter of Credit</option>
                                            <option value="9" {{ $caraDagang == '9' ? 'selected' : '' }}>9 - WSI - Wessel Inkaso</option>
                                            <option value="10" {{ $caraDagang == '10' ? 'selected' : '' }}>10 - KON - Konsinyasi</option>
                                            <option value="11" {{ $caraDagang == '11' ? 'selected' : '' }}>11 - ICA - Inter Company Account</option>
                                            <option value="14" {{ $caraDagang == '14' ? 'selected' : '' }}>14 - NCV - Tanpa Pembayaran</option>
                                            <option value="15" {{ $caraDagang == '15' ? 'selected' : '' }}>15 - Lainnya</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-sm ">Cara Pembayaran</label>
                                        <select name="kodeCaraBayar" class="form-control form-control-sm select2bs4 ">
                                            @php $caraBayar = $dataDetail['kodeCaraBayar'] ?? '' @endphp
                                            <option value="">Pilih Cara Pembayaran</option>
                                            <option value="1" {{ $caraBayar == '1' ? 'selected' : '' }}>1 - BIASA/TUNAI</option>
                                            <option value="2" {{ $caraBayar == '2' ? 'selected' : '' }}>2 - BERKALA</option>
                                            <option value="3" {{ $caraBayar == '3' ? 'selected' : '' }}>3 - DENGAN JAMINAN</option>
                                            <option value="4" {{ $caraBayar == '4' ? 'selected' : '' }}>4 - PERHITUNGAN KEMUDIAN</option>
                                            <option value="5" {{ $caraBayar == '5' ? 'selected' : '' }}>5 - KONSINYASI</option>
                                            <option value="6" {{ $caraBayar == '6' ? 'selected' : '' }}>6 - USANCE LC</option>
                                            <option value="7" {{ $caraBayar == '7' ? 'selected' : '' }}>7 - RED CLAUSE LC</option>
                                            <option value="8" {{ $caraBayar == '8' ? 'selected' : '' }}>8 - INTER-COMPANY ACCOUNT</option>
                                            <option value="9" {{ $caraBayar == '9' ? 'selected' : '' }}>9 - GABUNGAN/LAINNYA</option>
                                            <option value="14" {{ $caraBayar == '14' ? 'selected' : '' }}>14 - TANPA PEMBAYARAN</option>
                                            <option value="15" {{ $caraBayar == '15' ? 'selected' : '' }}>15 - ADVANCE PAYMENT</option>
                                            <option value="16" {{ $caraBayar == '16' ? 'selected' : '' }}>16 - SIGHT LC</option>
                                            <option value="17" {{ $caraBayar == '17' ? 'selected' : '' }}>17 - INKASO</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-sm ">Jenis BC 3.3</label>
                                        <select name="kodeJenisProsedur" class="form-control form-control-sm select2bs4 ">
                                            @php $jenisProsedur = $dataDetail['kodeJenisProsedur'] ?? '' @endphp
                                            <option value="">Pilih Jenis BC 3.3</option>
                                            <option value="1" {{ $jenisProsedur == '1' ? 'selected' : '' }}>1 - EKSPOR BIASA</option>
                                            <option value="2" {{ $jenisProsedur == '2' ? 'selected' : '' }}>2 - EKSPOR BERKALA</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-sm">Negara Tujuan</label>
                                        <select name="kodeNegaraTujuan" class="form-control form-control-sm select2bs4">
                                            <option value="">Pilih Negara Tujuan</option>
                                            @include('export-import.dokumen-pabean.options_negara', ['selected' => $dataDetail['kodeNegaraTujuan'] ?? ''])
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-entitas" role="tabpanel">
                    <div class="row">
                        <!-- Eksportir -->
                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Eksportir</div>
                                <div class="card-body">
                                    <input type="hidden" name="entitas[2][kodeEntitas]" value="2">
                                    <input type="hidden" name="entitas[2][seriEntitas]" value="1">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0 ">Nomor Identitas</label>
                                        <div class="row">
                                            <div class="col-4 pr-1">
                                                <select name="entitas[2][kodeJenisIdentitas]" class="form-control form-control-sm select2bs4">
                                                    <option value="6" {{ ($dataDetail['entitas'][2]['kodeJenisIdentitas'] ?? '6') == '6' ? 'selected' : '' }}>6 - NPWP 16 DIGIT</option>
                                                    <option value="5" {{ ($dataDetail['entitas'][2]['kodeJenisIdentitas'] ?? '') == '5' ? 'selected' : '' }}>5 - NPWP 15 DIGIT</option>
                                                </select>
                                            </div>
                                            <div class="col-8 pl-1">
                                                <input type="text" name="entitas[2][nomorIdentitas]" class="form-control form-control-sm " value="{{ $dataDetail['entitas'][2]['nomorIdentitas'] ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">NITKU</label>
                                        <input type="text" name="entitas[2][nitku]" class="form-control form-control-sm " value="{{ $dataDetail['entitas'][2]['nitku'] ?? '' }}" placeholder="NITKU">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0 ">Nama</label>
                                        <input type="text" name="entitas[2][namaEntitas]" class="form-control form-control-sm " value="{{ $dataDetail['entitas'][2]['namaEntitas'] ?? '' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0 ">Alamat</label>
                                        <textarea name="entitas[2][alamatEntitas]" class="form-control form-control-sm " rows="2">{{ $dataDetail['entitas'][2]['alamatEntitas'] ?? '' }}</textarea>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small mb-0 ">Status</label>
                                        <select name="entitas[2][kodeStatus]" class="form-control form-control-sm select2bs4 ">
                                            <option value="">Pilih Status</option>
                                            <option value="1" {{ ($dataDetail['entitas'][2]['kodeStatus'] ?? $dataDetail['entitas'][2]['statusEntitas'] ?? '') == '1' ? 'selected' : '' }}>KOPERASI</option>
                                            <option value="2" {{ ($dataDetail['entitas'][2]['kodeStatus'] ?? $dataDetail['entitas'][2]['statusEntitas'] ?? '') == '2' ? 'selected' : '' }}>PMDN (MIGAS)</option>
                                            <option value="3" {{ ($dataDetail['entitas'][2]['kodeStatus'] ?? $dataDetail['entitas'][2]['statusEntitas'] ?? '') == '3' ? 'selected' : '' }}>PMDN (NON MIGAS)</option>
                                            <option value="4" {{ ($dataDetail['entitas'][2]['kodeStatus'] ?? $dataDetail['entitas'][2]['statusEntitas'] ?? '') == '4' ? 'selected' : '' }}>PMA (MIGAS)</option>
                                            <option value="5" {{ ($dataDetail['entitas'][2]['kodeStatus'] ?? $dataDetail['entitas'][2]['statusEntitas'] ?? '') == '5' ? 'selected' : '' }}>PMA (NON MIGAS)</option>
                                            <option value="6" {{ ($dataDetail['entitas'][2]['kodeStatus'] ?? $dataDetail['entitas'][2]['statusEntitas'] ?? '') == '6' ? 'selected' : '' }}>BUMN</option>
                                            <option value="7" {{ ($dataDetail['entitas'][2]['kodeStatus'] ?? $dataDetail['entitas'][2]['statusEntitas'] ?? '') == '7' ? 'selected' : '' }}>BUMD</option>
                                            <option value="8" {{ ($dataDetail['entitas'][2]['kodeStatus'] ?? $dataDetail['entitas'][2]['statusEntitas'] ?? '') == '8' ? 'selected' : '' }}>PERORANGAN</option>
                                            <option value="9" {{ ($dataDetail['entitas'][2]['kodeStatus'] ?? $dataDetail['entitas'][2]['statusEntitas'] ?? '') == '9' ? 'selected' : '' }}>USAHA KECIL MIKRO DAN MENENGAH</option>
                                            <option value="10" {{ ($dataDetail['entitas'][2]['kodeStatus'] ?? $dataDetail['entitas'][2]['statusEntitas'] ?? '') == '10' ? 'selected' : '' }}>LAINNYA</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pengusaha PLB / PDPLB (kodeEntitas=3) -->
                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Pengusaha PLB / PDPLB</div>
                                <div class="card-body">
                                    <input type="hidden" name="entitas[3][kodeEntitas]" value="3">
                                    <input type="hidden" name="entitas[3][seriEntitas]" value="2">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nomor Identitas</label>
                                        <div class="row">
                                            <div class="col-4 pr-1">
                                                <select name="entitas[3][kodeJenisIdentitas]" class="form-control form-control-sm select2bs4">
                                                    <option value="6" {{ ($dataDetail['entitas'][3]['kodeJenisIdentitas'] ?? $dataDetail['entitas'][9]['kodeJenisIdentitas'] ?? '6') == '6' ? 'selected' : '' }}>6 - NPWP 16 DIGIT</option>
                                                    <option value="5" {{ ($dataDetail['entitas'][3]['kodeJenisIdentitas'] ?? $dataDetail['entitas'][9]['kodeJenisIdentitas'] ?? '') == '5' ? 'selected' : '' }}>5 - NPWP 15 DIGIT</option>
                                                    <option value="3" {{ ($dataDetail['entitas'][3]['kodeJenisIdentitas'] ?? '') == '3' ? 'selected' : '' }}>3 - KTP</option>
                                                    <option value="4" {{ ($dataDetail['entitas'][3]['kodeJenisIdentitas'] ?? '') == '4' ? 'selected' : '' }}>4 - Lainnya</option>
                                                </select>
                                            </div>
                                            <div class="col-8 pl-1">
                                                <input type="text" name="entitas[3][nomorIdentitas]" class="form-control form-control-sm " value="{{ $dataDetail['entitas'][3]['nomorIdentitas'] ?? $dataDetail['entitas'][9]['nomorIdentitas'] ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">NITKU</label>
                                        <input type="text" name="entitas[3][nitku]" class="form-control form-control-sm " value="{{ $dataDetail['entitas'][3]['nitku'] ?? '' }}" placeholder="NITKU">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nama PLB</label>
                                        <input type="text" name="entitas[3][namaEntitas]" class="form-control form-control-sm " value="{{ $dataDetail['entitas'][3]['namaEntitas'] ?? $dataDetail['entitas'][9]['namaEntitas'] ?? '' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Alamat</label>
                                        <textarea name="entitas[3][alamatEntitas]" class="form-control form-control-sm " rows="2">{{ $dataDetail['entitas'][3]['alamatEntitas'] ?? $dataDetail['entitas'][9]['alamatEntitas'] ?? '' }}</textarea>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Status Perusahaan</label>
                                        <select name="entitas[3][kodeStatus]" class="form-control form-control-sm select2bs4">
                                            <option value="">Pilih Status</option>
                                            <option value="1" {{ ($dataDetail['entitas'][3]['kodeStatus'] ?? '') == '1' ? 'selected' : '' }}>KOPERASI</option>
                                            <option value="2" {{ ($dataDetail['entitas'][3]['kodeStatus'] ?? '') == '2' ? 'selected' : '' }}>PMDN (MIGAS)</option>
                                            <option value="3" {{ ($dataDetail['entitas'][3]['kodeStatus'] ?? '') == '3' ? 'selected' : '' }}>PMDN (NON MIGAS)</option>
                                            <option value="4" {{ ($dataDetail['entitas'][3]['kodeStatus'] ?? '') == '4' ? 'selected' : '' }}>PMA (MIGAS)</option>
                                            <option value="5" {{ ($dataDetail['entitas'][3]['kodeStatus'] ?? '') == '5' ? 'selected' : '' }}>PMA (NON MIGAS)</option>
                                            <option value="6" {{ ($dataDetail['entitas'][3]['kodeStatus'] ?? '') == '6' ? 'selected' : '' }}>BUMN</option>
                                            <option value="8" {{ ($dataDetail['entitas'][3]['kodeStatus'] ?? '') == '8' ? 'selected' : '' }}>PERORANGAN</option>
                                            <option value="10" {{ ($dataDetail['entitas'][3]['kodeStatus'] ?? '') == '10' ? 'selected' : '' }}>LAINNYA</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nomor Izin PLB</label>
                                        <input type="text" name="entitas[3][nomorIjinEntitas]" class="form-control form-control-sm " value="{{ $dataDetail['entitas'][3]['nomorIjinEntitas'] ?? $dataDetail['nomorIzinPlb'] ?? '' }}">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small mb-0">Tanggal Izin PLB</label>
                                        <input type="date" name="entitas[3][tanggalIjinEntitas]" class="form-control form-control-sm " value="{{ $dataDetail['entitas'][3]['tanggalIjinEntitas'] ?? $dataDetail['tanggalIzinPlb'] ?? date('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Penerima -->
                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Penerima</div>
                                <div class="card-body">
                                    <input type="hidden" name="entitas[8][kodeEntitas]" value="8">
                                    <input type="hidden" name="entitas[8][seriEntitas]" value="3">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0 ">Nama</label>
                                        <input type="text" name="entitas[8][namaEntitas]" class="form-control form-control-sm " value="{{ $dataDetail['entitas'][8]['namaEntitas'] ?? $header->supplier ?? '' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0 ">Alamat</label>
                                        <textarea name="entitas[8][alamatEntitas]" class="form-control form-control-sm " rows="2">{{ $dataDetail['entitas'][8]['alamatEntitas'] ?? $header->alamat_supplier ?? '' }}</textarea>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small mb-0 ">Negara</label>
                                        <select name="entitas[8][kodeNegara]" class="form-control form-control-sm select2bs4 ">
                                            <option value="">Pilih Negara</option>
                                            @include('export-import.dokumen-pabean.options_negara', ['selected' => $dataDetail['entitas'][8]['kodeNegara'] ?? ''])
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pembeli -->
                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold d-flex align-items-center px-3 py-2 bg-light text-dark" style="font-size:13px;">
                                    <span>Pembeli</span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 ml-auto" id="btn-salin-penerima" title="Salin Data Penerima"><i class="fas fa-copy"></i> Salin Penerima</button>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" name="entitas[6][kodeEntitas]" value="6">
                                    <input type="hidden" name="entitas[6][seriEntitas]" value="4">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nama</label>
                                        <input type="text" name="entitas[6][namaEntitas]" class="form-control form-control-sm " value="{{ $dataDetail['entitas'][6]['namaEntitas'] ?? '' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Alamat</label>
                                        <textarea name="entitas[6][alamatEntitas]" class="form-control form-control-sm " rows="2">{{ $dataDetail['entitas'][6]['alamatEntitas'] ?? '' }}</textarea>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small mb-0">Negara Tujuan</label>
                                        <select name="entitas[6][kodeNegara]" class="form-control form-control-sm select2bs4 ">
                                            <option value="">Pilih Negara</option>
                                            @include('export-import.dokumen-pabean.options_negara', ['selected' => $dataDetail['entitas'][6]['kodeNegara'] ?? ''])
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PEMILIK BARANG (kodeEntitas=7) -->
                    <div class="card shadow-sm mt-2 border">
                        <div class="card-header text-dark fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color: #f8f9fa;">
                            <span>Pemilik Barang (H.6-10)</span>
                            <button type="button" id="btn-add-pemilik" class="btn btn-sm btn-light btn-add-action py-0 px-2 ml-auto" title="Tambah Pemilik"><i class="fas fa-plus text-primary"></i></button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-borderless mb-0">
                                <thead class="bg-light text-center border-bottom">
                                    <tr>
                                        <th width="10%">Seri</th>
                                        <th width="25%">Nomor Identitas</th>
                                        <th width="35%">Alamat</th>
                                        <th width="30%">Nama & Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-pemilik">
                                    @php $pemiliks = $dataDetail['entitas'][7] ?? $dataDetail['pemilik'] ?? []; @endphp
                                    @php if (isset($pemiliks['kodeEntitas'])) { $pemiliks = [$pemiliks]; } @endphp
                                    @forelse($pemiliks as $pIndex => $pem)
                                    <tr>
                                        <td class="text-center p-2 align-middle"><input type="text" class="form-control form-control-sm text-center bg-light" value="{{ $pIndex + 1 }}" readonly></td>
                                        <td class="p-2">
                                            <select name="entitas[7][{{ $pIndex }}][kodeJenisIdentitas]" class="form-control form-control-sm mb-1 select2bs4">
                                                <option value="6" {{ ($pem['kodeJenisIdentitas'] ?? $pem['jenisId'] ?? '6') == '6' ? 'selected' : '' }}>NPWP 16 DIGIT</option>
                                                <option value="5" {{ ($pem['kodeJenisIdentitas'] ?? $pem['jenisId'] ?? '') == '5' ? 'selected' : '' }}>NPWP 15 DIGIT</option>
                                                <option value="2" {{ ($pem['kodeJenisIdentitas'] ?? $pem['jenisId'] ?? '') == '2' ? 'selected' : '' }}>Paspor</option>
                                                <option value="3" {{ ($pem['kodeJenisIdentitas'] ?? $pem['jenisId'] ?? '') == '3' ? 'selected' : '' }}>KTP</option>
                                                <option value="4" {{ ($pem['kodeJenisIdentitas'] ?? $pem['jenisId'] ?? '') == '4' ? 'selected' : '' }}>Lainnya</option>
                                            </select>
                                            <input type="text" name="entitas[7][{{ $pIndex }}][nomorIdentitas]" class="form-control form-control-sm " value="{{ $pem['nomorIdentitas'] ?? $pem['noId'] ?? '' }}" placeholder="No. Identitas">
                                            <input type="hidden" name="entitas[7][{{ $pIndex }}][kodeEntitas]" value="7">
                                            <input type="hidden" name="entitas[7][{{ $pIndex }}][seriEntitas]" value="{{ $pIndex + 1 }}">
                                        </td>
                                        <td class="p-2"><textarea name="entitas[7][{{ $pIndex }}][alamatEntitas]" class="form-control form-control-sm " rows="2" placeholder="Alamat">{{ $pem['alamatEntitas'] ?? $pem['alamat'] ?? '' }}</textarea></td>
                                        <td class="p-2 align-middle">
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="entitas[7][{{ $pIndex }}][namaEntitas]" class="form-control form-control-sm " value="{{ $pem['namaEntitas'] ?? $pem['nama'] ?? '' }}" placeholder="Nama Pemilik">
                                                <div class="input-group-append"><button type="button" class="btn btn-danger btn-hapus-pemilik"><i class="fas fa-trash-alt"></i></button></div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- PPJK (kodeEntitas=4) -->
                    <div class="card shadow-sm mt-2 border">
                        <div class="card-header text-dark fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color: #f8f9fa;">
                            <span>PPJK</span>
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="entitas[4][kodeEntitas]" value="4">
                            <input type="hidden" name="entitas[4][seriEntitas]" value="7">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">NPWP PPJK</label>
                                        <div class="row">
                                            <div class="col-4 pr-1">
                                                <select name="entitas[4][kodeJenisIdentitas]" class="form-control form-control-sm select2bs4">
                                                    <option value="6" {{ ($dataDetail['entitas'][4]['kodeJenisIdentitas'] ?? '6') == '6' ? 'selected' : '' }}>6 - NPWP 16</option>
                                                    <option value="5" {{ ($dataDetail['entitas'][4]['kodeJenisIdentitas'] ?? '') == '5' ? 'selected' : '' }}>5 - NPWP 15</option>
                                                </select>
                                            </div>
                                            <div class="col-8 pl-1">
                                                <input type="text" name="entitas[4][nomorIdentitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][4]['nomorIdentitas'] ?? '' }}" placeholder="NPWP PPJK">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nama PPJK</label>
                                        <input type="text" name="entitas[4][namaEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][4]['namaEntitas'] ?? '' }}" placeholder="Nama PPJK">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Alamat PPJK</label>
                                        <textarea name="entitas[4][alamatEntitas]" class="form-control form-control-sm" rows="2" placeholder="Alamat PPJK">{{ $dataDetail['entitas'][4]['alamatEntitas'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-dokumen" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color: #001f3f;">
                            <span>Dokumen Lampiran</span>
                            <button type="button" class="btn btn-sm btn-light btn-add-action py-0 px-2 ml-auto" id="btn-add-dok" title="Tambah Dokumen"><i class="fas fa-plus text-primary"></i> Tambah Dokumen</button>
                        </div>
                        <div class="card-body p-0" style="overflow-x: auto;">
                            <table class="table table-sm table-bordered mb-0" id="table-dokumen" style="min-width: 800px;">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="5%">Seri</th>
                                        <th width="20%">Jenis</th>
                                        <th width="20%">Nomor</th>
                                        <th width="12%">Tanggal</th>
                                        <th width="12%">Fasilitas</th>
                                        <th width="12%">Kode Izin</th>
                                        <th width="9%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-dokumen">
                                    @php
                                        $dokumens = $dataDetail['dok'] ?? [];
                                    @endphp
                                    @foreach($dokumens as $index => $dok)
                                        <tr>
                                            <td class="text-center align-middle">
                                                {{ $index + 1 }}
                                                <input type="hidden" name="dok[{{ $index }}][seri]" value="{{ $index + 1 }}">
                                            </td>
                                            <td>
                                                <select name="dok[{{ $index }}][kode]" class="form-control form-control-sm select2bs4">
                                                    <option value="">-- Pilih Kode --</option>
                                                    @foreach($referensiDokumen as $val => $text)
                                                        <option value="{{ $val }}" {{ ($dok['kodeDokumen'] ?? $dok['kode'] ?? '') == $val ? 'selected' : '' }}>
                                                            {{ $val }} - {{ $text }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="dok[{{ $index }}][nomor]" class="form-control form-control-sm"
                                                    value="{{ $dok['nomorDokumen'] ?? $dok['nomor'] ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="date" name="dok[{{ $index }}][tgl]" class="form-control form-control-sm"
                                                    value="{{ $dok['tanggalDokumen'] ?? $dok['tgl'] ?? '' }}">
                                            </td>
                                            <td><input type="text" name="dok[{{ $index }}][kodeFasilitas]" class="form-control form-control-sm" value="{{ $dok['kodeFasilitas'] ?? '' }}" placeholder="Kode Fasilitas"></td>
                                            <td><input type="text" name="dok[{{ $index }}][kodeIjin]" class="form-control form-control-sm" value="{{ $dok['kodeIjin'] ?? '' }}" placeholder="Kode Ijin"></td>
                                            <td class="text-center align-middle">
                                                <button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-dok"><i class="fas fa-trash-alt"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-pengangkut" role="tabpanel">
                    <div class="row">
                        <!-- Pengangkutan PLB -->
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Pengangkutan PLB</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="text-sm">Kode Gudang PLB</label>
                                        <input type="text" name="kodeGudangPlb" class="form-control form-control-sm" value="{{ $dataDetail['kodeGudangPlb'] ?? '' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-sm">Cara Angkut Ke PLB</label>
                                        <select name="kodeCaraAngkutPlb" class="form-control form-control-sm select2bs4">
                                            <option value="">-- Pilih Cara Angkut --</option>
                                            <option value="1" {{ ($dataDetail['kodeCaraAngkutPlb'] ?? $dataDetail['caraAngkutPlb'] ?? '') == '1' ? 'selected' : '' }}>1 - LAUT</option>
                                            <option value="2" {{ ($dataDetail['kodeCaraAngkutPlb'] ?? $dataDetail['caraAngkutPlb'] ?? '') == '2' ? 'selected' : '' }}>2 - KERETA API</option>
                                            <option value="3" {{ ($dataDetail['kodeCaraAngkutPlb'] ?? $dataDetail['caraAngkutPlb'] ?? '') == '3' ? 'selected' : '' }}>3 - DARAT</option>
                                            <option value="4" {{ ($dataDetail['kodeCaraAngkutPlb'] ?? $dataDetail['caraAngkutPlb'] ?? '') == '4' ? 'selected' : '' }}>4 - UDARA</option>
                                            <option value="5" {{ ($dataDetail['kodeCaraAngkutPlb'] ?? $dataDetail['caraAngkutPlb'] ?? '') == '5' ? 'selected' : '' }}>5 - POS</option>
                                            <option value="6" {{ ($dataDetail['kodeCaraAngkutPlb'] ?? $dataDetail['caraAngkutPlb'] ?? '') == '6' ? 'selected' : '' }}>6 - MULTIMODA</option>
                                            <option value="7" {{ ($dataDetail['kodeCaraAngkutPlb'] ?? $dataDetail['caraAngkutPlb'] ?? '') == '7' ? 'selected' : '' }}>7 - INSTALASI / PIPA</option>
                                            <option value="8" {{ ($dataDetail['kodeCaraAngkutPlb'] ?? $dataDetail['caraAngkutPlb'] ?? '') == '8' ? 'selected' : '' }}>8 - PERAIRAN</option>
                                            <option value="9" {{ ($dataDetail['kodeCaraAngkutPlb'] ?? $dataDetail['caraAngkutPlb'] ?? '') == '9' ? 'selected' : '' }}>9 - LAINNYA</option>
                                            <option value="10" {{ ($dataDetail['kodeCaraAngkutPlb'] ?? $dataDetail['caraAngkutPlb'] ?? '') == '10' ? 'selected' : '' }}>10 - INSTALASI</option>
                                            <option value="11" {{ ($dataDetail['kodeCaraAngkutPlb'] ?? $dataDetail['caraAngkutPlb'] ?? '') == '11' ? 'selected' : '' }}>11 - PIPA</option>
                                            <option value="12" {{ ($dataDetail['kodeCaraAngkutPlb'] ?? $dataDetail['caraAngkutPlb'] ?? '') == '12' ? 'selected' : '' }}>12 - TRANSMISI</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="text-sm">Tgl Masuk ke PLB</label>
                                        <input type="date" name="tanggalMasukPlb" class="form-control form-control-sm" value="{{ $dataDetail['tanggalMasukPlb'] ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pengangkutan -->
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Pengangkutan</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="text-sm ">Cara Pengangkutan</label>
                                        <select name="pengangkut[0][kodeCaraAngkut]" class="form-control form-control-sm select2bs4 ">
                                            <option value="">-- Pilih Cara Pengangkutan --</option>
                                            <option value="1" {{ ($dataDetail['pengangkut'][0]['kodeCaraAngkut'] ?? $dataDetail['kodeJenisPengangkutan'] ?? '') == '1' ? 'selected' : '' }}>1 - LAUT</option>
                                            <option value="2" {{ ($dataDetail['pengangkut'][0]['kodeCaraAngkut'] ?? $dataDetail['kodeJenisPengangkutan'] ?? '') == '2' ? 'selected' : '' }}>2 - KERETA API</option>
                                            <option value="3" {{ ($dataDetail['pengangkut'][0]['kodeCaraAngkut'] ?? $dataDetail['kodeJenisPengangkutan'] ?? '') == '3' ? 'selected' : '' }}>3 - DARAT</option>
                                            <option value="4" {{ ($dataDetail['pengangkut'][0]['kodeCaraAngkut'] ?? $dataDetail['kodeJenisPengangkutan'] ?? '') == '4' ? 'selected' : '' }}>4 - UDARA</option>
                                            <option value="5" {{ ($dataDetail['pengangkut'][0]['kodeCaraAngkut'] ?? $dataDetail['kodeJenisPengangkutan'] ?? '') == '5' ? 'selected' : '' }}>5 - POS</option>
                                            <option value="6" {{ ($dataDetail['pengangkut'][0]['kodeCaraAngkut'] ?? $dataDetail['kodeJenisPengangkutan'] ?? '') == '6' ? 'selected' : '' }}>6 - MULTIMODA</option>
                                            <option value="7" {{ ($dataDetail['pengangkut'][0]['kodeCaraAngkut'] ?? $dataDetail['kodeJenisPengangkutan'] ?? '') == '7' ? 'selected' : '' }}>7 - INSTALASI/PIPA</option>
                                            <option value="9" {{ ($dataDetail['pengangkut'][0]['kodeCaraAngkut'] ?? $dataDetail['kodeJenisPengangkutan'] ?? '') == '9' ? 'selected' : '' }}>9 - LAINNYA</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-sm ">Sarana Pengangkut </label>
                                        <input type="text" name="pengangkut[0][namaPengangkut]" class="form-control form-control-sm " value="{{ $dataDetail['pengangkut'][0]['namaPengangkut'] ?? '' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-sm ">No Pengangkut / Voy / Flight </label>
                                        <input type="text" name="pengangkut[0][nomorPengangkut]" class="form-control form-control-sm " value="{{ $dataDetail['pengangkut'][0]['nomorPengangkut'] ?? '' }}">
                                    </div>
                                    <input type="hidden" name="pengangkut[0][seriPengangkut]" value="1">
                                    <div class="form-group mb-0">
                                        <label class="text-sm ">Perkiraan Tanggal Pemuatan </label>
                                        <input type="date" name="tanggalMuat" class="form-control form-control-sm " value="{{ $dataDetail['tanggalMuat'] ?? $dataDetail['tanggalPemuatan'] ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pelabuhan -->
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Pelabuhan</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="text-sm">Tempat Penimbunan</label>
                                        <select name="kodeTps" class="form-control form-control-sm select2-tps-penimbunan select2bs4">
                                            <option value="">Pilih Tempat Penimbunan</option>
                                            @foreach($mapNamaTps as $code => $label)
                                                <option value="{{ $code }}" {{ $tpsCode == $code ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                            @if(!empty($tpsCode) && !isset($mapNamaTps[$tpsCode]))
                                                <option value="{{ $tpsCode }}" selected>{{ $tpsLabel }}</option>
                                            @endif
                                        </select>
                                        <small class="text-muted">Ketik nama atau kode TPS Tempat Penimbunan</small>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-sm ">Pelabuhan Muat</label>
                                        <select name="kodePelMuat" class="form-control form-control-sm select2-pelabuhan select2bs4">
                                            <option value="">-- Pilih Pelabuhan Muat --</option>
                                            @if(!empty($dataDetail['kodePelMuat']))
                                                <option value="{{ $dataDetail['kodePelMuat'] }}" selected>{{ $dataDetail['kodePelMuat'] }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-sm">Pelabuhan Bongkar</label>
                                        <select name="kodePelBongkar" class="form-control form-control-sm select2-pelabuhan select2bs4">
                                            <option value="">-- Pilih Pelabuhan Bongkar --</option>
                                            @if(!empty($dataDetail['kodePelBongkar']))
                                                <option value="{{ $dataDetail['kodePelBongkar'] }}" selected>{{ $dataDetail['kodePelBongkar'] }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="text-sm ">Pelabuhan Tujuan</label>
                                        <select name="kodePelTujuan" class="form-control form-control-sm select2-pelabuhan select2bs4">
                                            <option value="">-- Pilih Pelabuhan Tujuan --</option>
                                            @if(!empty($dataDetail['kodePelTujuan']))
                                                <option value="{{ $dataDetail['kodePelTujuan'] }}" selected>{{ $dataDetail['kodePelTujuan'] }}</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-kemasan" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header fw-bold d-flex align-items-center px-3 py-2 bg-light text-dark" style="font-size:13px;">
                            <span>Data Kemasan</span>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 ml-auto" id="btn-add-kemasan" title="Tambah Kemasan"><i class="fas fa-plus"></i> Tambah Kemasan</button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0" id="table-kemasan">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="5%">Seri</th>
                                        <th width="18%">Jumlah</th>
                                        <th width="38%">Jenis</th>
                                        <th width="29%">Merek</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-kemasan">
                                    @php
                                        $kemasans = $dataDetail['kemasan'] ?? [];
                                        if (empty($kemasans)) {
                                            $kemasans[] = ['jumlahKemasan' => $header->qty_karton ?? "", 'kodeJenisKemasan' => 'CT', 'merkKemasan' => '-'];
                                        }
                                    @endphp
                                    @foreach($kemasans as $index => $kemasan)
                                    <tr>
                                        <td class="text-center align-middle">
                                            {{ $index + 1 }}
                                            <input type="hidden" name="kemasan[{{ $index }}][seriKemasan]" value="{{ $index + 1 }}">
                                        </td>
                                        <td><input type="number" step="any" name="kemasan[{{ $index }}][jumlahKemasan]" class="form-control form-control-sm  input-decimal" value="{{ $kemasan['jumlahKemasan'] ?? $kemasan['jumlah'] ?? 0 }}"></td>
                                        <td>
                                            <select name="kemasan[{{ $index }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4 ">
                                                <option value="">-- Pilih Jenis Kemasan --</option>
                                                @foreach($listJenisKemasan as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kemasan['kodeJenisKemasan'] ?? $kemasan['kode'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" name="kemasan[{{ $index }}][merkKemasan]" class="form-control form-control-sm " value="{{ $kemasan['merkKemasan'] ?? $kemasan['merk'] ?? '-' }}"></td>
                                        <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kemasan"><i class="fas fa-trash-alt"></i></button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header fw-bold d-flex align-items-center px-3 py-2 bg-light text-dark" style="font-size:13px;">
                            <span>Data Petikemas</span>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 ml-auto" id="btn-add-kontainer" title="Tambah Kontainer"><i class="fas fa-plus"></i> Tambah Petikemas</button>
                        </div>
                        <div class="card-body p-0">
                            @php
                                $kontainers = $dataDetail['kontainer'] ?? [];
                                $listJenisKontainer = ['4' => 'Empty', '7' => 'LCL', '8' => 'FCL'];
                                $listTipeKontainer = [
                                    '1' => 'General/Dry Cargo', '2' => 'Tunnel Type', '3' => 'Open Top Steel',
                                    '4' => 'Flat Rack', '5' => 'Reefer/Refrigerated', '6' => 'Barge Container',
                                    '7' => 'Bulk Container', '8' => 'Isotank', '99' => 'Lain-lain'
                                ];
                                $listUkuranKontainer = ['20' => '20 Feet', '40' => '40 Feet', '45' => '45 Feet', '60' => '60 Feet'];
                            @endphp
                            <table class="table table-sm table-bordered mb-0" id="table-kontainer">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="30%">Nomor Kontainer</th>
                                        <th width="15%">Ukuran</th>
                                        <th width="20%">Jenis</th>
                                        <th width="25%">Tipe</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-kontainer">
                                    @foreach($kontainers as $kIndex => $kont)
                                    <tr>
                                        <input type="hidden" name="kontainer[{{ $kIndex }}][seriKontainer]" value="{{ $kIndex + 1 }}">
                                        <td><input type="text" name="kontainer[{{ $kIndex }}][nomorKontainer]" class="form-control form-control-sm text-uppercase" value="{{ $kont['nomorKontainer'] ?? '' }}"></td>
                                        <td>
                                            <select name="kontainer[{{ $kIndex }}][kodeUkuranKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih Ukuran --</option>
                                                @foreach($listUkuranKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kont['kodeUkuranKontainer'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="kontainer[{{ $kIndex }}][kodeJenisKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih Jenis --</option>
                                                @foreach($listJenisKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kont['kodeJenisKontainer'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="kontainer[{{ $kIndex }}][kodeTipeKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih Tipe --</option>
                                                @foreach($listTipeKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kont['kodeTipeKontainer'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kontainer"><i class="fas fa-trash-alt"></i></button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-transaksi" role="tabpanel">
                    <div class="row">
                        <!-- Data Penyerahan -->
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Data Penyerahan</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label>Daerah Asal Barang</label>
                                        @php
                                            $daerahAsal = !empty($dataDetail['kodeDaerahAsal']) ? $dataDetail['kodeDaerahAsal'] : '3204';
                                        @endphp
                                        <select name="kodeDaerahAsal" class="form-control form-control-sm select2bs4">
                                            <option value="">-- Pilih Daerah Asal --</option>
                                            @include('export-import.dokumen-pabean.options_daerah', ['selected' => $daerahAsal])
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Cara Penyerahan Barang (Incoterm)</label>
                                        @php $incoterm = $dataDetail['kodeIncoterm'] ?? '' @endphp
                                        <select name="kodeIncoterm" class="form-control form-control-sm select2bs4 ">
                                            <option value="">-- Pilih Incoterm --</option>
                                            <option value="CFR" {{ $incoterm == 'CFR' ? 'selected' : '' }}>CFR - COST AND FREIGHT</option>
                                            <option value="CIF" {{ $incoterm == 'CIF' ? 'selected' : '' }}>CIF - COST, INSURANCE AND FREIGHT</option>
                                            <option value="CIP" {{ $incoterm == 'CIP' ? 'selected' : '' }}>CIP - CARRIAGE AND INSURANCE PAID TO</option>
                                            <option value="CPT" {{ $incoterm == 'CPT' ? 'selected' : '' }}>CPT - CARRIAGE PAID TO</option>
                                            <option value="DAP" {{ $incoterm == 'DAP' ? 'selected' : '' }}>DAP - DELIVERED AT PLACE</option>
                                            <option value="DAT" {{ $incoterm == 'DAT' ? 'selected' : '' }}>DAT - DELIVERED AT TERMINAL</option>
                                            <option value="DDP" {{ $incoterm == 'DDP' ? 'selected' : '' }}>DDP - DELIVERED DUTY PAID</option>
                                            <option value="DPU" {{ $incoterm == 'DPU' ? 'selected' : '' }}>DPU - DELIVERED AT PLACE UNLOADED</option>
                                            <option value="EXW" {{ $incoterm == 'EXW' ? 'selected' : '' }}>EXW - EX WORKS</option>
                                            <option value="FAS" {{ $incoterm == 'FAS' ? 'selected' : '' }}>FAS - FREE ALONGSIDE SHIP</option>
                                            <option value="FCA" {{ $incoterm == 'FCA' ? 'selected' : '' }}>FCA - FREE CARRIER</option>
                                            <option value="FOB" {{ $incoterm == 'FOB' ? 'selected' : '' }}>FOB - FREE ON BOARD</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Barang Curah</label>
                                        <select name="flagCurah" class="form-control form-control-sm select2bs4">
                                            <option value="">-- Pilih Jenis Barang  --</option>
                                            <option value="1" {{ ($dataDetail['flagCurah'] ?? $dataDetail['isBarangCurah'] ?? '') == '1' ? 'selected' : '' }}>1 - Curah</option>
                                            <option value="2" {{ ($dataDetail['flagCurah'] ?? $dataDetail['isBarangCurah'] ?? '') == '2' ? 'selected' : '' }}>2 - Non-Curah</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Nilai -->
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Nilai</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label>Jenis Valuta</label>
                                        <select name="kodeValuta" class="form-control form-control-sm select2bs4 " id="kode_valuta">
                                            <option value="">-- Valuta --</option>
                                            <option value="">Pilih Valuta</option>
                                            <option value="IDR" {{ ($dataDetail['kodeValuta'] ?? 'IDR') == 'IDR' ? 'selected' : '' }}>IDR - RUPIAH</option>
                                            <option value="USD" {{ ($dataDetail['kodeValuta'] ?? '') == 'USD' ? 'selected' : '' }}>USD - US DOLLAR</option>
                                            <option value="AUD" {{ ($dataDetail['kodeValuta'] ?? '') == 'AUD' ? 'selected' : '' }}>AUD - AUSTRALIAN DOLLAR</option>
                                            <option value="BND" {{ ($dataDetail['kodeValuta'] ?? '') == 'BND' ? 'selected' : '' }}>BND - BRUNEI DOLLAR</option>
                                            <option value="CAD" {{ ($dataDetail['kodeValuta'] ?? '') == 'CAD' ? 'selected' : '' }}>CAD - CANADIAN DOLLAR</option>
                                            <option value="CHF" {{ ($dataDetail['kodeValuta'] ?? '') == 'CHF' ? 'selected' : '' }}>CHF - SWISS FRANC</option>
                                            <option value="CNY" {{ ($dataDetail['kodeValuta'] ?? '') == 'CNY' ? 'selected' : '' }}>CNY - YUAN RENMINBI</option>
                                            <option value="DKK" {{ ($dataDetail['kodeValuta'] ?? '') == 'DKK' ? 'selected' : '' }}>DKK - DANISH KRONE</option>
                                            <option value="EUR" {{ ($dataDetail['kodeValuta'] ?? '') == 'EUR' ? 'selected' : '' }}>EUR - EURO</option>
                                            <option value="GBP" {{ ($dataDetail['kodeValuta'] ?? '') == 'GBP' ? 'selected' : '' }}>GBP - POUND STERLING</option>
                                            <option value="HKD" {{ ($dataDetail['kodeValuta'] ?? '') == 'HKD' ? 'selected' : '' }}>HKD - HONG KONG DOLLAR</option>
                                            <option value="INR" {{ ($dataDetail['kodeValuta'] ?? '') == 'INR' ? 'selected' : '' }}>INR - INDIAN RUPEE</option>
                                            <option value="JPY" {{ ($dataDetail['kodeValuta'] ?? '') == 'JPY' ? 'selected' : '' }}>JPY - JAPANESE YEN</option>
                                            <option value="KRW" {{ ($dataDetail['kodeValuta'] ?? '') == 'KRW' ? 'selected' : '' }}>KRW - SOUTH KOREAN WON</option>
                                            <option value="KWD" {{ ($dataDetail['kodeValuta'] ?? '') == 'KWD' ? 'selected' : '' }}>KWD - KUWAITI DINAR</option>
                                            <option value="MYR" {{ ($dataDetail['kodeValuta'] ?? '') == 'MYR' ? 'selected' : '' }}>MYR - MALAYSIAN RINGGIT</option>
                                            <option value="NZD" {{ ($dataDetail['kodeValuta'] ?? '') == 'NZD' ? 'selected' : '' }}>NZD - NEW ZEALAND DOLLAR</option>
                                            <option value="PGK" {{ ($dataDetail['kodeValuta'] ?? '') == 'PGK' ? 'selected' : '' }}>PGK - PAPUA NEW GUINEA KINA</option>
                                            <option value="PHP" {{ ($dataDetail['kodeValuta'] ?? '') == 'PHP' ? 'selected' : '' }}>PHP - PHILIPPINE PESO</option>
                                            <option value="SAR" {{ ($dataDetail['kodeValuta'] ?? '') == 'SAR' ? 'selected' : '' }}>SAR - SAUDI RIYAL</option>
                                            <option value="SEK" {{ ($dataDetail['kodeValuta'] ?? '') == 'SEK' ? 'selected' : '' }}>SEK - SWEDISH KRONA</option>
                                            <option value="SGD" {{ ($dataDetail['kodeValuta'] ?? '') == 'SGD' ? 'selected' : '' }}>SGD - SINGAPORE DOLLAR</option>
                                            <option value="THB" {{ ($dataDetail['kodeValuta'] ?? '') == 'THB' ? 'selected' : '' }}>THB - THAI BAHT</option>
                                            <option value="TWD" {{ ($dataDetail['kodeValuta'] ?? '') == 'TWD' ? 'selected' : '' }}>TWD - TAIWAN NEW DOLLAR</option>
                                        </select>
                                    </div>
                                   <div class="form-group mb-2">
                                        <label>NDPBM</label>
                                        <input type="number" step="any" name="ndpbm" class="form-control form-control-sm" value="{{ $dataDetail['ndpbm'] ?? '0.00' }}" id="ndpbm">
                                        <div class="d-flex justify-content-end">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-info btn-sm" id="btn-get-kurs">
                                                    <i class="fas fa-sync-alt"></i> Tarik Kurs CEISA
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Nilai Maklon</label>
                                        <input type="number" step="any" name="nilaiMaklon" class="form-control form-control-sm" value="{{ $dataDetail['nilaiMaklon'] ?? '' }}">
                                    </div>
                                    <div class="form-group mb-2 mt-3">
                                        <div class="row align-items-center">
                                            <div class="col-sm-12 mb-1">
                                                <label class="text-sm font-weight-bold mb-0">
                                                    <input type="checkbox" id="check-pph" name="isNilaiPph" class="mr-1" {{ !empty($dataDetail['nilaiPph']) ? 'checked' : '' }}>
                                                    Nilai PPh Pasal 22 Ekspor
                                                </label>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="fw-bold mb-0" style="font-size: 14px;" id="text-nilai-pph">Rp 0,00</div>
                                                <input type="hidden" name="nilaiPph" id="val-nilai-pph" value="{{ $dataDetail['nilaiPph'] ?? '0.00' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Jumlah Nilai Ekspor / FOB (H.33)</label>
                                        <input type="number" step="any" name="fob" class="form-control form-control-sm " value="{{ $dataDetail['fob'] ?? '0.0000' }}" id="total_cif">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Nilai Barang (H.33)</label>
                                        <input type="number" step="any" name="nilaiBarang" class="form-control form-control-sm" value="{{ $dataDetail['nilaiBarang'] ?? '0.00' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Freight</label>
                                        <input type="number" step="any" name="freight" class="form-control form-control-sm" value="{{ $dataDetail['freight'] ?? '0.00' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Asuransi</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="any" name="asuransi" class="form-control form-control-sm" value="{{ $dataDetail['asuransi'] ?? '0.00' }}">
                                            <div class="input-group-append">
                                                <select name="kodeAsuransi" class="form-control form-control-sm select2bs4" style="border-radius:0 4px 4px 0;">
                                                    <option value="DN" {{ ($dataDetail['kodeAsuransi'] ?? 'DN') == 'DN' ? 'selected' : '' }}>DN</option>
                                                    <option value="LN" {{ ($dataDetail['kodeAsuransi'] ?? '') == 'LN' ? 'selected' : '' }}>LN</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>CIF</label>
                                        <input type="number" step="any" name="cif" class="form-control form-control-sm" value="{{ $dataDetail['cif'] ?? '0.00' }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Berat -->
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Berat</div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label>Berat Kotor (KGM)</label>
                                        <input type="number" step="any" name="bruto" class="form-control form-control-sm " value="{{ $dataDetail['bruto'] ?? '0.0000' }}">
                                        <small class="" style="font-size: 10px;">Berat Kotor harus lebih besar dari 0</small>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Berat Bersih (Kg)</label>
                                        <input type="number" step="any" name="netto" class="form-control form-control-sm" value="{{ $dataDetail['netto'] ?? '0.00' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header fw-bold d-flex align-items-center px-3 py-2 bg-light text-dark" style="font-size:14px;">
                            <span>Bank Devisa</span>
                            <button type="button" id="btn-add-bank" class="btn btn-sm btn-primary py-0 px-3 ml-auto">Tambah</button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-borderless mb-0">
                                <thead class="text-muted" style="font-size: 12px; border-bottom: 1px solid #eee;">
                                    <tr>
                                        <th width="10%">Seri</th>
                                        <th width="30%">Kode Bank</th>
                                        <th width="60%">Nama Bank</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-bank">
                                    @php $banks = $dataDetail['bankDevisa'] ?? []; @endphp
                                    @forelse($banks as $bIndex => $bank)
                                    <tr>
                                        <td class="text-center align-middle">
                                            {{ $bIndex + 1 }}
                                            <input type="hidden" name="bankDevisa[{{ $bIndex }}][seriBank]" value="{{ $bIndex + 1 }}">
                                        </td>
                                        <td><input type="text" name="bankDevisa[{{ $bIndex }}][kodeBank]" class="form-control form-control-sm" value="{{ $bank['kodeBank'] ?? '' }}"></td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="bankDevisa[{{ $bIndex }}][namaBank]" class="form-control form-control-sm" value="{{ $bank['namaBank'] ?? '' }}">
                                                <div class="input-group-append"><button type="button" class="btn btn-danger btn-hapus-bank"><i class="fas fa-trash-alt"></i></button></div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>

                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ================= TAB 7: BARANG ================= -->
                <div class="tab-pane fade" id="tab-barang" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #001f3f;">
                            <i class="fas fa-boxes"></i> Rincian Barang ({{ count($items) }} Item)
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="accordionBarang">
                                @foreach($items as $index => $item)
                                @php
                                    $draftItem = $dataDetail['barang'][$index] ?? [];
                                    // Auto-populate dari data transaksi jika draftItem masih kosong
                                    $defaultKodeKantor   = $draftItem['kodeKantor']      ?? $dataDetail['kodeKantor'] ?? '050500';
                                    $defaultNomorDaftar  = $draftItem['nomorDaftar']     ?? ($item->bcno_in ?? ($item->kpno ?? ''));
                                    $defaultTglDaftar    = $draftItem['tanggalDaftar']   ?? ((!empty($item->tgl_bc_in) && $item->tgl_bc_in !== '0000-00-00') ? $item->tgl_bc_in : '');
                                    $defaultSeriBarang   = $draftItem['seriBarangAsal']  ?? ($item->no_urut ?? ($index + 1));
                                    $defaultJenisDok     = $draftItem['kodeJenisDokAsal'] ?? '';
                                @endphp

                                <div class="card mb-2 border">
                                    <div class="card-header bg-light py-2 btn-collapse-barang" data-target="#collapseBarang{{ $index }}" style="cursor: pointer;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-bold" style="font-size: 13px; color: white;">
                                                {{ $item->goods_code ?? $item->id_item }} - {{ $item->itemdesc }}
                                            </div>
                                            <i class="fas fa-chevron-down text-muted icon-collapse"></i>
                                        </div>
                                    </div>

                                    <div id="collapseBarang{{ $index }}" class="collapse {{ $index == 0 ? 'show' : '' }}" data-parent="#accordionBarang">
                                        <div class="card-body py-3 px-3 bg-white">

                                            <!-- Hidden inputs wajib untuk API -->
                                            <input type="hidden" name="barang[{{ $index }}][seriBarang]" value="{{ $index + 1 }}">
                                            <input type="hidden" name="barang[{{ $index }}][kodeDokumen]" value="33">

                                            <!-- Layout Sesuai Portal CEISA 4.0 -->
                                            <div class="row">
                                                <!-- KOLOM KIRI: Dokumen Asal -->
                                                <div class="col-md-4">
                                                    <div class="card shadow-sm mb-3 border">
                                                        <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Dokumen Asal</div>
                                                        <div class="card-body">
                                                            <div class="form-group mb-2">
                                                                <label>Kode Kantor</label>
                                                                <select name="barang[{{ $index }}][kodeKantor]" class="form-control form-control-sm  select2bs4">
                                                                    <option value="">-- Kode Kantor --</option>
                                                                    @foreach($kantorList as $kKtr => $vKtr)
                                                                        <option value="{{ $kKtr }}" {{ $defaultKodeKantor == $kKtr ? 'selected' : '' }}>{{ $kKtr }} - {{ $vKtr }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Dokumen Asal</label>
                                                                <select name="barang[{{ $index }}][kodeJenisDokAsal]" class="form-control form-control-sm  select2bs4">
                                                                    <option value="">-- Jenis Dokumen --</option>
                                                                    @foreach($referensiDokumenAsal as $val => $text)
                                                                        <option value="{{ $val }}" {{ $defaultJenisDok == $val ? 'selected' : '' }}>{{ $val }} - {{ $text }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Nomor Pengajuan</label>
                                                                <input type="text" name="barang[{{ $index }}][nomorPengajuan]" class="form-control form-control-sm" value="{{ $draftItem['nomorPengajuan'] ?? '' }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Nomor Daftar</label>
                                                                <input type="text" name="barang[{{ $index }}][nomorDaftar]" class="form-control form-control-sm " value="{{ $defaultNomorDaftar }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Tanggal Daftar</label>
                                                                <input type="date" name="barang[{{ $index }}][tanggalDaftar]" class="form-control form-control-sm " value="{{ $defaultTglDaftar }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Seri Barang Asal</label>
                                                                <input type="text" name="barang[{{ $index }}][seriBarangAsal]" class="form-control form-control-sm " value="{{ $defaultSeriBarang }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- KOLOM TENGAH: Jenis -->
                                                <div class="col-md-4">
                                                    <div class="card shadow-sm mb-3 border">
                                                        <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Jenis</div>
                                                        <div class="card-body">
                                                            <div class="form-group mb-2">
                                                                <label>Seri</label>
                                                                <input type="text" class="form-control form-control-sm bg-light fw-bold" value="{{ $index + 1 }}" readonly>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <div class="d-flex align-items-center mb-1">
                                                                    <label class="mb-0">Pos Tarif/HS <i class="fas fa-info-circle text-primary"></i></label>
                                                                </div>
                                                                <input type="text" name="barang[{{ $index }}][posTarif]" class="form-control form-control-sm" value="{{ $draftItem['posTarif'] ?? '48191000' }}" placeholder="Masukkan Pos Tarif/HS">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Kode Barang</label>
                                                                <input type="text" name="barang[{{ $index }}][kodeBarang]" class="form-control form-control-sm" value="{{ $draftItem['kodeBarang'] ?? $item->goods_code ?? $item->id_item ?? '-' }}">
                                                                <input type="text" name="barang[{{ $index }}][idItem]" class="form-control form-control-sm hidden" value="{{ $item->id_item ?? '' }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <div class="d-flex justify-content-between align-items-end mb-1">
                                                                    <label class="mb-0">Uraian Jenis Barang</label>
                                                                    <button type="button" class="btn btn-sm btn-primary py-0 px-2" style="font-size:11px;">Sesuai Hs</button>
                                                                </div>
                                                                <textarea name="barang[{{ $index }}][uraian]" class="form-control form-control-sm " rows="3">{{ $draftItem['uraian'] ?? $item->itemdesc ?? '' }}</textarea>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Merek</label>
                                                                <input type="text" name="barang[{{ $index }}][merek]" class="form-control form-control-sm" value="{{ $draftItem['merek'] ?? '-' }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Tipe</label>
                                                                <input type="text" name="barang[{{ $index }}][tipe]" class="form-control form-control-sm" value="{{ $draftItem['tipe'] ?? '-' }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Ukuran</label>
                                                                <input type="text" name="barang[{{ $index }}][ukuran]" class="form-control form-control-sm" value="{{ $draftItem['ukuran'] ?? '-' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- KOLOM KANAN: Multi Cards -->
                                                <div class="col-md-4">
                                                    <!-- Keterangan Lainnya -->
                                                    <div class="card shadow-sm mb-3 border">
                                                        <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Keterangan Lainnya</div>
                                                        <div class="card-body">
                                                            <div class="form-group mb-0">
                                                                <label>Negara</label>
                                                                <select name="barang[{{ $index }}][kodeNegaraAsal]" class="form-control form-control-sm  select2bs4">
                                                                    <option value="">-- Negara --</option>
                                                                    @include('export-import.dokumen-pabean.options_negara', ['selected' => $draftItem['kodeNegaraAsal'] ?? 'ID'])
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Jumlah & Berat -->
                                                    <div class="card shadow-sm mb-3 border">
                                                        <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Jumlah & Berat</div>
                                                        <div class="card-body">
                                                            <div class="form-group mb-2">
                                                                <label>Jumlah dan Satuan Barang</label>
                                                                <div class="row">
                                                                    <div class="col-sm-6 pr-1">
                                                                        <input type="number" step="any" name="barang[{{ $index }}][jumlahSatuan]" class="form-control form-control-sm " value="{{ $draftItem['jumlahSatuan'] ?? (float) $item->qty }}">
                                                                    </div>
                                                                    <div class="col-sm-6 pl-1">
                                                                        <select name="barang[{{ $index }}][kodeSatuanBarang]" class="form-control form-control-sm  select2bs4">
                                                                            <option value="">-- Kode Satuan --</option>
                                                                            @foreach($listSatuanBarang as $k => $v)
                                                                                <option value="{{ $k }}" {{ ($draftItem['kodeSatuanBarang'] ?? $item->unit) == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Kemasan</label>
                                                                <div class="row">
                                                                    <div class="col-sm-6 pr-1">
                                                                        <input type="number" step="any" name="barang[{{ $index }}][jumlahKemasan]" class="form-control form-control-sm" value="{{ $draftItem['jumlahKemasan'] ?? 0 }}">
                                                                    </div>
                                                                    <div class="col-sm-6 pl-1">
                                                                        <select name="barang[{{ $index }}][kodeJenisKemasan]" class="form-control form-control-sm  select2bs4">
                                                                            <option value="">-- Kode Jenis --</option>
                                                                            @foreach($listJenisKemasan as $k => $v)
                                                                                <option value="{{ $k }}" {{ ($draftItem['kodeJenisKemasan'] ?? 'CT') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label>Berat Bersih</label>
                                                                <input type="number" step="any" name="barang[{{ $index }}][netto]" class="form-control form-control-sm " value="{{ $draftItem['netto'] ?? (float) ($item->nw ?? $item->netto ?? 0) }}">
                                                                <small class="" style="font-size: 10px;">Berat Bersih harus lebih besar dari 0</small>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Harga -->
                                                    <div class="card shadow-sm mb-3 border">
                                                        <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Harga</div>
                                                        <div class="card-body">
                                                            <div class="form-group mb-2">
                                                                <label>FOB</label>
                                                                <input type="number" step="any" name="barang[{{ $index }}][fob]" class="form-control form-control-sm input-cif-barang" value="{{ $draftItem['fob'] ?? (float)($item->qty * $item->price) }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Volume</label>
                                                                <input type="number" step="any" name="barang[{{ $index }}][volume]" class="form-control form-control-sm" value="{{ $draftItem['volume'] ?? '0.00' }}">
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label>Nilai Barang</label>
                                                                <input type="number" step="any" name="barang[{{ $index }}][nilaiBarang]" class="form-control form-control-sm" value="{{ $draftItem['nilaiBarang'] ?? $draftItem['hargaEkspor'] ?? (float)$item->price }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mt-2">
                                                <!-- TABEL ENTITAS BARANG (DALAM BARANG) -->
                                                <div class="col-md-6">
                                                    <div class="card shadow-sm mb-0 border">
                                                        <div class="card-header fw-bold d-flex align-items-center px-3 py-2 bg-light text-dark" style="font-size:13px;">
                                                            <span>Pemilik Barang</span>
                                                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 ml-auto btn-add-entitas-barang" data-itemidx="{{ $index }}"><i class="fas fa-plus"></i> Tambah</button>
                                                        </div>
                                                        <div class="card-body p-0">
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-borderless mb-0">
                                                                    <thead class="text-muted text-center border-bottom" style="font-size:12px;">
                                                                        <tr>
                                                                            <th width="10%">Seri</th>
                                                                            <th width="25%">No Identitas</th>
                                                                            <th width="30%">Nama</th>
                                                                            <th width="30%">Alamat</th>
                                                                            <th width="5%">Aksi</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="tbody-entitas-barang-{{ $index }}">
                                                                        @php $entitasBarang = $draftItem['entitasBarang'] ?? []; @endphp
                                                                        @forelse($entitasBarang as $ebIndex => $entBrg)
                                                                        <tr>
                                                                            <td class="text-center p-2 align-middle"><input type="text" class="form-control form-control-sm text-center bg-light" value="{{ $ebIndex + 1 }}" readonly></td>
                                                                            <td class="p-2"><input type="text" name="barang[{{ $index }}][entitasBarang][{{ $ebIndex }}][nomorIdentitas]" class="form-control form-control-sm" value="{{ $entBrg['nomorIdentitas'] ?? '' }}"></td>
                                                                            <td class="p-2"><input type="text" name="barang[{{ $index }}][entitasBarang][{{ $ebIndex }}][namaEntitas]" class="form-control form-control-sm" value="{{ $entBrg['namaEntitas'] ?? '' }}"></td>
                                                                            <td class="p-2"><input type="text" name="barang[{{ $index }}][entitasBarang][{{ $ebIndex }}][alamatEntitas]" class="form-control form-control-sm" value="{{ $entBrg['alamatEntitas'] ?? '' }}"></td>
                                                                            <td class="text-center p-2 align-middle"><button type="button" class="btn btn-sm btn-outline-danger btn-hapus-entitas-brg"><i class="fas fa-trash-alt"></i></button></td>
                                                                        </tr>
                                                                        @empty

                                                                        @endforelse
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- TABEL DOKUMEN FASILITAS/LARTAS (DALAM BARANG) -->
                                                <div class="col-md-6">
                                                    <div class="card shadow-sm mb-0 border">
                                                        <div class="card-header fw-bold d-flex align-items-center px-3 py-2 bg-light text-dark" style="font-size:13px;">
                                                            <span>Dokumen Fasilitas/Lartas</span>
                                                            <button type="button" class="btn btn-sm btn-primary py-0 px-2 ml-auto btn-add-dok-fasilitas" data-itemidx="{{ $index }}"><i class="fas fa-plus"></i> Tambah</button>
                                                        </div>
                                                        <div class="card-body p-0">
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-borderless mb-0">
                                                                    <thead class="text-muted text-center border-bottom" style="font-size:12px;">
                                                                        <tr>
                                                                            <th width="5%">Seri</th>
                                                                            <th width="25%">Jenis</th>
                                                                            <th width="20%">Nomor</th>
                                                                            <th width="15%">Tanggal</th>
                                                                            <th width="15%">Fasilitas</th>
                                                                            <th width="15%">No Urut Izin</th>
                                                                            <th width="5%">Aksi</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="tbody-dok-fasilitas-{{ $index }}">
                                                                        @php $dokFasilitas = $draftItem['dokFasilitas'] ?? []; @endphp
                                                                        @forelse($dokFasilitas as $fIndex => $fas)
                                                                        <tr>
                                                                            <td class="text-center p-2"><input type="text" class="form-control form-control-sm text-center bg-light" value="{{ $fIndex + 1 }}" readonly></td>
                                                                            <td class="p-2">
                                                                                <select name="barang[{{ $index }}][dokFasilitas][{{ $fIndex }}][kodeDokumen]" class="form-control form-control-sm select2bs4">
                                                                                    <option value="">Pilih</option>
                                                                                    @foreach($referensiDokumen as $val => $text)
                                                                                        <option value="{{ $val }}" {{ ($fas['kodeDokumen'] ?? '') == $val ? 'selected' : '' }}>{{ $val }} - {{ $text }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </td>
                                                                            <td class="p-2"><input type="text" name="barang[{{ $index }}][dokFasilitas][{{ $fIndex }}][nomorDokumen]" class="form-control form-control-sm" value="{{ $fas['nomorDokumen'] ?? '' }}"></td>
                                                                            <td class="p-2"><input type="date" name="barang[{{ $index }}][dokFasilitas][{{ $fIndex }}][tanggalDokumen]" class="form-control form-control-sm" value="{{ $fas['tanggalDokumen'] ?? '' }}"></td>
                                                                            <td class="p-2"><input type="text" name="barang[{{ $index }}][dokFasilitas][{{ $fIndex }}][kodeFasilitas]" class="form-control form-control-sm" value="{{ $fas['kodeFasilitas'] ?? '' }}"></td>
                                                                            <td class="p-2"><input type="text" name="barang[{{ $index }}][dokFasilitas][{{ $fIndex }}][seriIjin]" class="form-control form-control-sm" value="{{ $fas['seriIjin'] ?? '' }}"></td>
                                                                            <td class="text-center p-2 align-middle"><button type="button" class="btn btn-sm btn-outline-danger btn-hapus-dok-fas"><i class="fas fa-trash-alt"></i></button></td>
                                                                        </tr>
                                                                        @empty
                                                                        @endforelse
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-pungutan" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header fw-bold d-flex align-items-center px-3 py-2 bg-light text-dark" style="font-size:14px;">
                            <span>Pungutan</span>
                            <button type="button" class="btn btn-sm btn-primary py-0 px-3 ml-auto"><i class="fas fa-sync-alt"></i> Generate Pungutan</button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="bg-light" style="font-size: 12px;">
                                        <tr>
                                            <th width="60%" class="align-middle">Pungutan</th>
                                            <th width="40%" class="align-middle">Dibayar</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-pungutan">
                                        @php $pungutans = $dataDetail['pungutan'] ?? []; @endphp
                                        @forelse($pungutans as $pIdx => $pung)
                                        <tr>
                                            <td>
                                                <input type="text" name="pungutan[{{ $pIdx }}][kodePungutan]" class="form-control form-control-sm" value="{{ $pung['kodePungutan'] ?? '' }}" placeholder="Kode Pungutan">
                                            </td>
                                            <td>
                                                <input type="number" step="any" name="pungutan[{{ $pIdx }}][dibayar]" class="form-control form-control-sm" value="{{ $pung['dibayar'] ?? '0.00' }}" placeholder="0.00">
                                            </td>
                                        </tr>
                                        @empty
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-pernyataan" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #001f3f;">Penandatangan</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 form-group"><label>Nama TTD</label><input type="text" name="namaTtd" class="form-control form-control-sm " value="{{ $dataDetail['namaTtd'] ?? '' }}"></div>
                                <div class="col-md-3 form-group"><label>Jabatan</label><input type="text" name="jabatanTtd" class="form-control form-control-sm " value="{{ $dataDetail['jabatanTtd'] ?? '' }}"></div>
                                <div class="col-md-3 form-group"><label>Kota TTD</label><input type="text" name="kotaTtd" class="form-control form-control-sm " value="{{ $dataDetail['kotaTtd'] ?? '' }}"></div>
                                <div class="col-md-3 form-group"><label>Tanggal TTD</label><input type="date" name="tanggalTtd" class="form-control form-control-sm " value="{{ $dataDetail['tanggalTtd'] ?? date('Y-m-d') }}"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="card-footer text-right bg-white border-top">
            <a href="{{ route('dokumen-pabean-index') }}" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-save"></i> Simpan Draft</button>
        </div>
    </form>
</div>

<div class="modal fade" id="modal-tambah-pemilik" tabindex="-1" role="dialog" aria-labelledby="modalTambahPemilikTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title font-weight-bold" id="modalTambahPemilikTitle">Tambah Pemilik Barang</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-2">
                    <label class="small mb-0">Nomor Identitas:</label>
                    <div class="input-group input-group-sm">
                        <select id="modal-pemilik-jenis-identitas" class="form-control select2bs4" style="max-width:160px;">
                            <option value="6">NPWP 16 DIGIT</option>
                            <option value="5">NPWP 15 DIGIT</option>
                            <option value="2">Paspor</option>
                            <option value="3">KTP</option>
                        </select>
                        <input type="text" id="modal-pemilik-nomor-identitas" class="form-control ">
                    </div>
                </div>
                <div class="form-group mb-2"><label class="small mb-0">Nama</label><input type="text" id="modal-pemilik-nama" class="form-control form-control-sm "></div>
                <div class="form-group mb-0"><label class="small mb-0">Alamat</label><textarea id="modal-pemilik-alamat" class="form-control form-control-sm " rows="3"></textarea></div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="button" id="btn-simpan-pemilik" class="btn btn-sm btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('plugins/sweetalert/dist/sweetalert2.all.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

        $('#ceisaTab a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });

        // ================= DOKUMEN HANDLER =================
        const optDokumenHtml = `
            <option value="">-- Pilih Kode --</option>
            @foreach($referensiDokumen as $val => $text) <option value="{{ $val }}">{{ $val }} - {{ $text }}</option> @endforeach
        `;
        let dokIndex = {{ count($dokumens ?? []) }};
        $('#btn-add-dok').on('click', function() {
            let htmlTr = `
                <tr>
                    <td class="text-center align-middle">${dokIndex + 1}<input type="hidden" name="dok[${dokIndex}][seri]" value="${dokIndex + 1}"></td>
                    <td><select name="dok[${dokIndex}][kode]" class="form-control form-control-sm select2bs4-dynamic ">${optDokumenHtml}</select></td>
                    <td><input type="text" name="dok[${dokIndex}][nomor]" class="form-control form-control-sm "></td>
                    <td><input type="date" name="dok[${dokIndex}][tgl]" class="form-control form-control-sm "></td>
                    <td><input type="text" name="dok[${dokIndex}][kodeFasilitas]" class="form-control form-control-sm" placeholder="Kode Fasilitas"></td>
                    <td><input type="text" name="dok[${dokIndex}][kodeIjin]" class="form-control form-control-sm" placeholder="Kode Ijin"></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-dok"><i class="fas fa-trash-alt"></i></button></td>
                </tr>`;
            $('#tbody-dokumen').append(htmlTr);
            $(`select[name="dok[${dokIndex}][kode]"]`).select2({ theme: 'bootstrap4', width: '100%' });
            dokIndex++;
        });
        $(document).on('click', '.btn-hapus-dok', function() { $(this).closest('tr').remove(); });

        // ================= KEMASAN HANDLER =================
        const optJenisKemasan = `
            <option value="">-- Pilih --</option>
            @foreach($listJenisKemasan as $kKem => $vKem) <option value="{{ $kKem }}">{{ $kKem }} - {{ $vKem }}</option> @endforeach
        `;
        let kemasanIndex = {{ count($kemasans ?? []) }};
        $('#btn-add-kemasan').on('click', function() {
            let htmlTr = `
                <tr>
                    <td class="text-center align-middle">${kemasanIndex + 1}<input type="hidden" name="kemasan[${kemasanIndex}][seriKemasan]" value="${kemasanIndex + 1}"></td>
                    <td><input type="number" step="any" name="kemasan[${kemasanIndex}][jumlahKemasan]" class="form-control form-control-sm  input-decimal" value="0"></td>
                    <td><select name="kemasan[${kemasanIndex}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4-dynamic ">${optJenisKemasan}</select></td>
                    <td><input type="text" name="kemasan[${kemasanIndex}][merkKemasan]" class="form-control form-control-sm " value="-"></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kemasan"><i class="fas fa-trash-alt"></i></button></td>
                </tr>`;
            $('#tbody-kemasan').append(htmlTr);
            $(`select[name="kemasan[${kemasanIndex}][kodeJenisKemasan]"]`).select2({ theme: 'bootstrap4', width: '100%' });
            kemasanIndex++;
        });
        $(document).on('click', '.btn-hapus-kemasan', function() { $(this).closest('tr').remove(); });

        // ================= KONTAINER HANDLER =================
        const optJenisKontainer = `<option value="8">8 - FCL</option><option value="7">7 - LCL</option><option value="4">4 - Empty</option>`;
        const optTipeKontainer = `<option value="1">1 - General/Dry Cargo</option><option value="8">8 - Isotank</option><option value="99">99 - Lain-lain</option>`;
        const optUkuranKontainer = `<option value="20">20 Feet</option><option value="40">40 Feet</option><option value="45">45 Feet</option><option value="60">60 Feet</option>`;
        let kontainerIndex = {{ count($kontainers ?? []) }};
        $('#btn-add-kontainer').on('click', function() {
            let htmlTr = `
                <tr>
                    <input type="hidden" name="kontainer[${kontainerIndex}][seriKontainer]" value="${kontainerIndex + 1}">
                    <td><input type="text" name="kontainer[${kontainerIndex}][nomorKontainer]" class="form-control form-control-sm  text-uppercase"></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeUkuranKontainer]" class="form-control form-control-sm select2bs4">${optUkuranKontainer}</select></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeJenisKontainer]" class="form-control form-control-sm select2bs4">${optJenisKontainer}</select></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeTipeKontainer]" class="form-control form-control-sm select2bs4">${optTipeKontainer}</select></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kontainer"><i class="fas fa-trash-alt"></i></button></td>
                </tr>`;
            $('#tbody-kontainer').append(htmlTr);
            kontainerIndex++;
        });
        $(document).on('click', '.btn-hapus-kontainer', function() { $(this).closest('tr').remove(); });

        // ================= SARKUT HANDLER =================
        let sarkutIndex = {{ count($pengangkuts ?? []) }};
        $('#btn-add-sarkut').on('click', function() {
            let tr = `<tr>
                <td class="text-center align-middle"><input type="text" class="form-control form-control-sm text-center bg-light" value="${sarkutIndex + 1}" readonly></td>
                <td><input type="text" name="pengangkut[${sarkutIndex}][namaPengangkut]" class="form-control form-control-sm "></td>
                <td><input type="text" name="pengangkut[${sarkutIndex}][nomorPengangkut]" class="form-control form-control-sm "></td>
                <td><select name="pengangkut[${sarkutIndex}][kodeCaraAngkut]" class="form-control form-control-sm select2bs4"><option value="1">1 - LAUT</option><option value="4">4 - UDARA</option><option value="3">3 - DARAT</option></select></td>
                <td><div class="input-group input-group-sm"><input type="text" name="pengangkut[${sarkutIndex}][kodeBendera]" class="form-control form-control-sm  text-uppercase"><div class="input-group-append"><button type="button" class="btn btn-danger btn-hapus-sarkut"><i class="fas fa-trash-alt"></i></button></div></div></td>
            </tr>`;
            $('#tbody-sarkut').append(tr);
            sarkutIndex++;
        });
        $(document).on('click', '.btn-hapus-sarkut', function() { $(this).closest('tr').remove(); });

        // ================= BANK HANDLER =================
        let bankIndex = {{ count($banks ?? []) }};
        $('#btn-add-bank').on('click', function() {
            let tr = `<tr>
                <td class="text-center align-middle">${bankIndex + 1}<input type="hidden" name="bankDevisa[${bankIndex}][seriBank]" value="${bankIndex + 1}"></td>
                <td><input type="text" name="bankDevisa[${bankIndex}][kodeBank]" class="form-control form-control-sm "></td>
                <td><div class="input-group input-group-sm"><input type="text" name="bankDevisa[${bankIndex}][namaBank]" class="form-control form-control-sm "><div class="input-group-append"><button type="button" class="btn btn-danger btn-hapus-bank"><i class="fas fa-trash-alt"></i></button></div></div></td>
            </tr>`;
            $('#tbody-bank').append(tr);
            bankIndex++;
        });
        $(document).on('click', '.btn-hapus-bank', function() { $(this).closest('tr').remove(); });

        // ================= JS PEMILIK BARANG =================
        let pemilikIndex = {{ count($pemiliks ?? []) }};
        $('#btn-add-pemilik').on('click', function(e) {
            e.preventDefault();
            $('#tbody-pemilik .no-data-row').remove();

            let tr = `<tr>
                <td class="text-center p-2 align-middle"><input type="text" class="form-control form-control-sm text-center bg-light" value="${pemilikIndex + 1}" readonly></td>
                <td class="p-2">
                    <select name="entitas[7][${pemilikIndex}][kodeJenisIdentitas]" class="form-control form-control-sm mb-1 select2bs4">
                        <option value="6">NPWP 16 DIGIT</option>
                        <option value="5">NPWP 15 DIGIT</option>
                        <option value="2">Paspor</option>
                        <option value="3">KTP</option>
                        <option value="4">Lainnya</option>
                    </select>
                    <input type="text" name="entitas[7][${pemilikIndex}][nomorIdentitas]" class="form-control form-control-sm " placeholder="No. Identitas">
                    <input type="hidden" name="entitas[7][${pemilikIndex}][kodeEntitas]" value="7">
                    <input type="hidden" name="entitas[7][${pemilikIndex}][seriEntitas]" value="${pemilikIndex + 1}">
                </td>
                <td class="p-2"><textarea name="entitas[7][${pemilikIndex}][alamatEntitas]" class="form-control form-control-sm " rows="2" placeholder="Alamat"></textarea></td>
                <td class="p-2 align-middle">
                    <div class="input-group input-group-sm">
                        <input type="text" name="entitas[7][${pemilikIndex}][namaEntitas]" class="form-control form-control-sm " placeholder="Nama Pemilik">
                        <div class="input-group-append"><button type="button" class="btn btn-danger btn-hapus-pemilik"><i class="fas fa-trash-alt"></i></button></div>
                    </div>
                </td>
            </tr>`;
            $('#tbody-pemilik').append(tr);
            pemilikIndex++;
        });
        $(document).on('click', '.btn-hapus-pemilik', function() { $(this).closest('tr').remove(); });

        $('#btn-salin-penerima').on('click', function(e) {
            e.preventDefault();
            let nama = $('input[name="entitas[8][namaEntitas]"]').val();
            let alamat = $('textarea[name="entitas[8][alamatEntitas]"]').val();
            let negara = $('select[name="entitas[8][kodeNegara]"]').val();

            $('input[name="entitas[6][namaEntitas]"]').val(nama);
            $('textarea[name="entitas[6][alamatEntitas]"]').val(alamat);
            $('select[name="entitas[6][kodeNegara]"]').val(negara).trigger('change');

            Swal.fire({toast: true, position: 'top-end', icon: 'success', title: 'Data disalin', showConfirmButton: false, timer: 1500});
        });

        // ================= DOKUMEN FASILITAS & ENTITAS (DALAM BARANG) =================
        $(document).on('click', '.btn-add-dok-fasilitas', function() {
            let itemIdx = $(this).data('itemidx');
            let tbody = $(`#tbody-dok-fasilitas-${itemIdx}`);
            tbody.find('.no-data-row').remove();
            let rowIdx = tbody.find('tr').length;

            let tr = `<tr>
                <td class="text-center p-2"><input type="text" class="form-control form-control-sm text-center bg-light" value="${rowIdx + 1}" readonly></td>
                <td class="p-2"><select name="barang[${itemIdx}][dokFasilitas][${rowIdx}][kodeDokumen]" class="form-control form-control-sm select2bs4-dynamic ">${optDokumenHtml}</select></td>
                <td class="p-2"><input type="text" name="barang[${itemIdx}][dokFasilitas][${rowIdx}][nomorDokumen]" class="form-control form-control-sm "></td>
                <td class="p-2"><input type="date" name="barang[${itemIdx}][dokFasilitas][${rowIdx}][tanggalDokumen]" class="form-control form-control-sm "></td>
                <td class="p-2"><input type="text" name="barang[${itemIdx}][dokFasilitas][${rowIdx}][kodeFasilitas]" class="form-control form-control-sm "></td>
                <td class="p-2"><input type="text" name="barang[${itemIdx}][dokFasilitas][${rowIdx}][seriIjin]" class="form-control form-control-sm"></td>
                <td class="text-center p-2 align-middle"><button type="button" class="btn btn-sm btn-danger btn-hapus-dok-fas"><i class="fas fa-trash-alt"></i></button></td>
            </tr>`;
            tbody.append(tr);
            $(`select[name="barang[${itemIdx}][dokFasilitas][${rowIdx}][kodeDokumen]"]`).select2({ theme: 'bootstrap4', width: '100%' });
        });
        $(document).on('click', '.btn-hapus-dok-fas', function() { $(this).closest('tr').remove(); });


        // ================= PPH EKSPOR KALKULASI =================
        function calculatePphEkspor() {
            let fob = parseFloat($('input[name="fob"]').val()) || 0;
            let ndpbm = parseFloat($('input[name="ndpbm"]').val()) || 0;
            let isChecked = $('#check-pph').is(':checked');
            let pph = isChecked ? (0.015 * fob * ndpbm) : 0;

            let formattedPph = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 2 }).format(pph);
            $('#text-nilai-pph').text(formattedPph);
            $('#val-nilai-pph').val(pph.toFixed(2));
        }

        $('input[name="fob"], input[name="ndpbm"]').on('input', calculatePphEkspor);
        $('#check-pph').on('change', calculatePphEkspor);
        calculatePphEkspor();

        // ================= ACCORDION & AJAX SUBMIT =================
        $('.btn-collapse-barang').on('click', function() {
            let targetId = $(this).data('target');
            let icon = $(this).find('.icon-collapse');
            let isExpanded = $(targetId).hasClass('show');
            $('.collapse').collapse('hide');
            $('.icon-collapse').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            if (!isExpanded) {
                $(targetId).collapse('show');
                icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            }
        });

        $('#form-edit-ceisa').on('submit', function(e) {
            e.preventDefault();
            if ($('#ndpbm').val() === '' || $('#ndpbm').val() === '0') {
                Swal.fire({
                    title: 'Gagal!',
                    text: 'NDPBM tidak boleh kosong.',
                    icon: 'error'
                });
                return;
            }
            Swal.fire({
                title: 'Simpan Perubahan?',
                text: "Data draft akan diperbarui.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Ya, Simpan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
                    this.submit();
                }
            });
        });

        $('.select2-pelabuhan').select2({
            theme: 'bootstrap4',
            placeholder: 'Cari Pelabuhan...',
            allowClear: true,
            language: {
                inputTooShort: function (args) {
                    var remain = args.minimum - args.input.length;
                    return "Masukkan " + remain + " karakter atau lebih";
                }
            },
            ajax: {
                url: '{{ route("ceisa.pelabuhan") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) { return { q: params.term }; },
                processResults: function (data) { return { results: data.results }; },
                cache: true
            },
            minimumInputLength: 2
        });

        $('.select2-tps').select2({
            theme: 'bootstrap4',
            placeholder: 'Cari Lokasi TPS...',
            allowClear: true
        });
        $('.select2-tps-penimbunan').select2({
            theme: 'bootstrap4',
            placeholder: 'Cari Tempat Penimbunan...',
            allowClear: true
        });

        // ==========================================
        // ENTITAS BARANG (DALAM RINCIAN BARANG)
        // ==========================================
        $(document).on('click', '.btn-add-entitas-barang', function() {
            let itemIdx = $(this).data('itemidx');
            let tbody = $(`#tbody-entitas-barang-${itemIdx}`);
            tbody.find('.no-data-row').remove();
            let rowIdx = tbody.find('tr').length;

            let tr = `<tr>
                <td class="text-center p-2 align-middle"><input type="text" class="form-control form-control-sm text-center bg-light" value="${rowIdx + 1}" readonly></td>
                <td class="p-2"><input type="text" name="barang[${itemIdx}][entitasBarang][${rowIdx}][nomorIdentitas]" class="form-control form-control-sm" placeholder="No. Identitas"></td>
                <td class="p-2"><input type="text" name="barang[${itemIdx}][entitasBarang][${rowIdx}][namaEntitas]" class="form-control form-control-sm" placeholder="Nama Entitas"></td>
                <td class="p-2"><input type="text" name="barang[${itemIdx}][entitasBarang][${rowIdx}][alamatEntitas]" class="form-control form-control-sm" placeholder="Alamat"></td>
                <td class="text-center p-2 align-middle"><button type="button" class="btn btn-sm btn-outline-danger btn-hapus-entitas-brg"><i class="fas fa-trash-alt"></i></button></td>
            </tr>`;
            tbody.append(tr);
        });
        $(document).on('click', '.btn-hapus-entitas-brg', function() { $(this).closest('tr').remove(); });

        function kalkulasiTotalCif() {
            let grandTotalCif = 0;
            $('.input-cif-barang').each(function() {
                let cifBarang = parseFloat($(this).val()) || 0;
                grandTotalCif += cifBarang;
            });
            $('#total_cif').val(grandTotalCif.toFixed(2));

            kalkulasiNilaiPabean();
        }

        $(document).on('input', '.input-cif-barang', function() {
            kalkulasiTotalCif();
        });
    });

    $('#btn-get-kurs').click(function() {
        let valuta = $('#kode_valuta').val();
        let $btn = $(this);
        let originalText = $btn.html();

        if (!valuta) {
            alert('Silakan pilih valuta terlebih dahulu!');
            return;
        }



        let baseUrl = '{{ url("/tes-ceisa-kurs") }}';

        $.ajax({
            url: baseUrl + '/' + valuta,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response && response.status === "true" && response.data && response.data.length > 0) {
                    let nilaiKurs = response.data[0].nilaiKurs;

                    $('#ndpbm').val(nilaiKurs);

                    kalkulasiNilaiPabean();
                } else {
                    Swal.fire({
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat mengambil data.',
                        icon: 'error'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan saat mengambil data.',
                    icon: 'error'
                });
                console.error(xhr);
            },
            complete: function() {

                $btn.html(originalText);
                $btn.prop('disabled', false);
            }
        });
    });
</script>
@endsection

