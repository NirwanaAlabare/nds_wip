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

@endphp

<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">
            <i class="fas fa-edit"></i> BC 3.0 - PEMBERITAHUAN EKSPOR BARANG
        </h5>
    </div>

    <form action="{{ route('dokumen-pabean-update_draft_batch_bc30', $batch_id) }}" method="POST" id="form-edit-ceisa">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="alert alert-warning py-2 mb-4">
                <strong>Mode Batch (BC 3.0)</strong><br>
                <strong>No. Transaksi Gabungan:</strong> {{ $batch_id }} <br>
                {{ $header->supplier ?? '-' }}
                <input type="hidden" name="bpbno_int" value="{{ $header->bppbno_int }}">
                <input type="hidden" name="no_dokumen_merge" value="{{ $batch_id }}">
            </div>

            <ul class="nav nav-tabs" id="ceisaTab" role="tablist">
                <li class="nav-item"><a class="nav-link active" id="header-tab" data-toggle="tab" href="#tab-header" role="tab"><i class="fas fa-info-circle"></i> Header & PKB</a></li>
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
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold px-3 py-2 d-flex align-items-center" style="font-size:13px; background-color: #001f3f;">
                            <i class="fas fa-file-invoice mr-2"></i> Data Utama
                            <small class="ml-auto font-weight-normal opacity-75">Isi identitas dokumen ekspor</small>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Nomor Aju</label>
                                        <div class="col-sm-8"><input type="text" name="nomorAju" class="form-control form-control-sm fw-bold" value="{{ $nomorAju }}"></div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Tanggal Aju</label>
                                        <div class="col-sm-8"><input type="date" name="tanggalAju" class="form-control form-control-sm" value="{{ $ceisaInfo->tanggal_aju ?? date('Y-m-d') }}"></div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Kantor Pabean Pemuatan</label>
                                        <div class="col-sm-8">
                                            <select name="kodeKantorMuat" class="form-control form-control-sm select2bs4">
                                                <option value="">Pilih Kantor Pabean Pemuatan</option>
                                                @foreach($kantorList as $val => $label)
                                                    <option value="{{ $val }}" {{ ($dataDetail['kodeKantorMuat'] ?? '050500') == $val ? 'selected' : '' }}>{{ $val }} - {{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Pelabuhan Muat Ekspor </label>
                                        <div class="col-sm-8">
                                            <select name="kodePelEkspor" class="form-control form-control-sm select2-pelabuhan">
                                                <option value="">Pilih Pelabuhan Muat Ekspor</option>
                                                @if(!empty($dataDetail['kodePelEkspor']))
                                                    <option value="{{ $dataDetail['kodePelEkspor'] }}" selected>{{ $dataDetail['kodePelEkspor'] }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Kantor Pabean Ekspor</label>
                                        <div class="col-sm-8">
                                            <select name="kodeKantorEkspor" class="form-control form-control-sm select2bs4 ">
                                                <option value="">Pilih Kantor Pabean Ekspor</option>
                                                @foreach($kantorList as $val => $label)
                                                    <option value="{{ $val }}" {{ ($dataDetail['kodeKantorEkspor'] ?? '') == $val ? 'selected' : '' }}>{{ $val }} - {{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Jenis Ekspor</label>
                                        <div class="col-sm-8">
                                            <select name="kodeJenisEkspor" class="form-control form-control-sm select2bs4">
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
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Kategori Ekspor</label>
                                        <div class="col-sm-8">
                                            <select name="kodeKategoriEkspor" class="form-control form-control-sm select2bs4">
                                                @php $katEkspor = $dataDetail['kodeKategoriEkspor'] ?? '41' @endphp
                                                <option value="">Pilih Kategori Ekspor</option>
                                                <option {{ ($katEkspor ?? '') == '61' ? 'selected' : '' }} value="61">61 - BKC Yang Belum Dilunasi Cukainya</option>
                                                <option {{ ($katEkspor ?? '') == '51' ? 'selected' : '' }} value="51">51 - PLB</option>
                                                <option {{ ($katEkspor ?? '') == '46' ? 'selected' : '' }} value="46">46 - TPB Dari Kawasan Daur Ulang Berikat</option>
                                                <option {{ ($katEkspor ?? '') == '45' ? 'selected' : '' }} value="45">45 - TPB Dari Tempat Lelang Berikat</option>
                                                <option {{ ($katEkspor ?? '') == '44' ? 'selected' : '' }} value="44">44 - TPB Dari Toko Bebas Bea</option>
                                                <option {{ ($katEkspor ?? '') == '43' ? 'selected' : '' }} value="43">43 - TPB Dari Tempat Pameran Berikat</option>
                                                <option {{ ($katEkspor ?? '') == '42' ? 'selected' : '' }} value="42">42 - TPB Dari Gudang Berikat</option>
                                                <option {{ ($katEkspor ?? '') == '41' ? 'selected' : '' }} value="41">41 - TPB Dari Kawasan Berikat</option>
                                                <option {{ ($katEkspor ?? '') == '38' ? 'selected' : '' }} value="38">38 - Khusus Brg Keperluan Penelitian</option>
                                                <option {{ ($katEkspor ?? '') == '37' ? 'selected' : '' }} value="37">37 - Khusus Brg Contoh</option>
                                                <option {{ ($katEkspor ?? '') == '36' ? 'selected' : '' }} value="36">36 - Khusus Brg Cinderamata</option>
                                                <option {{ ($katEkspor ?? '') == '35' ? 'selected' : '' }} value="35">35 - Khusus Brg Keperluan Ibadah Utk Umum Sosial Pendidikan Budaya/Olahraga dan Bencana Alam</option>
                                                <option {{ ($katEkspor ?? '') == '34' ? 'selected' : '' }} value="34">34 - Khusus Brg Pindahan</option>
                                                <option {{ ($katEkspor ?? '') == '33' ? 'selected' : '' }} value="33">33 - Khusus Brg Kiriman</option>
                                                <option {{ ($katEkspor ?? '') == '32' ? 'selected' : '' }} value="32">32 - Khusus Brg Perwakilan Badan Internasional</option>
                                                <option {{ ($katEkspor ?? '') == '31' ? 'selected' : '' }} value="31">31 - Khusus Brg Perwakilan Negara Asing</option>
                                                <option {{ ($katEkspor ?? '') == '23' ? 'selected' : '' }} value="23">23 - KITE dengan pembebasan dan pengembalian</option>
                                                <option {{ ($katEkspor ?? '') == '22' ? 'selected' : '' }} value="22">22 - Yg pd saat imp mndpt fas pengembalian BM(NIPER dgn pengembalian)</option>
                                                <option {{ ($katEkspor ?? '') == '21' ? 'selected' : '' }} value="21">21 - Yg pd saat imp mndpt fas pembebasan BM(NIPER dgn pembebasan)</option>
                                                <option {{ ($katEkspor ?? '') == '10' ? 'selected' : '' }} value="10">10 - Umum</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Lokasi TPS</label>
                                        <div class="col-sm-8">
                                            <select name="kodeTps" class="form-control form-control-sm select2-tps">
                                                <option value="">Pilih Lokasi TPS</option>
                                                @foreach($mapNamaTps as $code => $label)
                                                    <option value="{{ $code }}" {{ $tpsCode == $code ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                                @if(!empty($tpsCode) && !isset($mapNamaTps[$tpsCode]))
                                                    <option value="{{ $tpsCode }}" selected>{{ $tpsLabel }}</option>
                                                @endif
                                            </select>
                                            <small class="text-muted">Ketik nama atau kode TPS (Contoh: KOJA, JICT, dll)</small>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Cara Perdagangan</label>
                                        <div class="col-sm-8">
                                            <select name="kodeCaraDagang" class="form-control form-control-sm select2bs4">
                                                @php $caraDagang = $dataDetail['kodeCaraDagang'] ?? '15' @endphp
                                                <option value="">Pilih Cara Perdagangan</option>
                                                <option value="1" {{ $caraDagang == '1' ? 'selected' : '' }}>1 - Biasa</option>
                                                <option value="2" {{ $caraDagang == '2' ? 'selected' : '' }}>2 - IMB - Imbal Dagang</option>
                                                <option value="3" {{ $caraDagang == '3' ? 'selected' : '' }}>3 - PMK - Pembayaran dimuka / Advance Payment</option>
                                                <option value="4" {{ $caraDagang == '4' ? 'selected' : '' }}>4 - KMD Bertahap - Pembayaran Kemudian / Open Account Tunai</option>
                                                <option value="5" {{ $caraDagang == '5' ? 'selected' : '' }}>5 - KMD Tunai - Pembayaran Kemudian / Open Account Tunai</option>
                                                <option value="6" {{ $caraDagang == '6' ? 'selected' : '' }}>6 - SLC - Sight Letter of Credit</option>
                                                <option value="7" {{ $caraDagang == '7' ? 'selected' : '' }}>7 - ULC - Usance Letter of Credit</option>
                                                <option value="8" {{ $caraDagang == '8' ? 'selected' : '' }}>8 - RLC - Red Clause Letter of Credit</option>
                                                <option value="9" {{ $caraDagang == '9' ? 'selected' : '' }}>9 - WSI - Wessel Inkaso / Collection Draft</option>
                                                <option value="10" {{ $caraDagang == '10' ? 'selected' : '' }}>10 - KON - Konsinyasi / Consignment</option>
                                                <option value="11" {{ $caraDagang == '11' ? 'selected' : '' }}>11 - ICA - Inter Company Account</option>
                                                <option value="12" {{ $caraDagang == '12' ? 'selected' : '' }}>12 - PDN Tunai - Pembayaran di Dalam Negeri Tunai</option>
                                                <option value="13" {{ $caraDagang == '13' ? 'selected' : '' }}>13 - TT - Pembayaran di Dalam Negeri melalui Telegraph Transfer</option>
                                                <option value="14" {{ $caraDagang == '14' ? 'selected' : '' }}>14 - NCV - Dilakukan tanpa pembayaran</option>
                                                <option value="15" {{ $caraDagang == '15' ? 'selected' : '' }}>15 - Lainnya</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Cara Pembayaran</label>
                                        <div class="col-sm-8">
                                            <select name="kodeCaraBayar" class="form-control form-control-sm select2bs4">
                                                @php $caraBayar = $dataDetail['kodeCaraBayar'] ?? '9' @endphp
                                                <option value="">Pilih Cara Pembayaran</option>
                                                <option value="1" {{ $caraBayar == '1' ? 'selected' : '' }}>1 - BIASA/TUNAI</option>
                                                <option value="2" {{ $caraBayar == '2' ? 'selected' : '' }}>2 - BERKALA</option>
                                                <option value="3" {{ $caraBayar == '3' ? 'selected' : '' }}>3 - DENGAN JAMINAN</option>
                                                <option value="4" {{ $caraBayar == '4' ? 'selected' : '' }}>4 - PERHITUNGAN KEMUDIAN</option>
                                                <option value="5" {{ $caraBayar == '5' ? 'selected' : '' }}>5 - KONSINYASI (CONSIGNMENT)</option>
                                                <option value="6" {{ $caraBayar == '6' ? 'selected' : '' }}>6 - USANCE LETTER OF CREDIT</option>
                                                <option value="7" {{ $caraBayar == '7' ? 'selected' : '' }}>7 - RED CLAUSE LETTER OF CREDIT</option>
                                                <option value="8" {{ $caraBayar == '8' ? 'selected' : '' }}>8 - INTER-COMPANY ACCOUNT</option>
                                                <option value="9" {{ $caraBayar == '9' ? 'selected' : '' }}>9 - GABUNGAN/LAINNYA</option>
                                                <option value="10" {{ $caraBayar == '10' ? 'selected' : '' }}>10 - PEMBAYARAN KEMUDIAN (OPEN ACCOUNT) SECARA BERTAHAP</option>
                                                <option value="11" {{ $caraBayar == '11' ? 'selected' : '' }}>11 - PEMBAYARAN KEMUDIAN (OPEN ACCOUNT) SECARA TUNAI</option>
                                                <option value="12" {{ $caraBayar == '12' ? 'selected' : '' }}>12 - DILAKUKAN DI DN DENGAN PEMBAYARAN UANG TUNAI</option>
                                                <option value="13" {{ $caraBayar == '13' ? 'selected' : '' }}>13 - DILAKUKAN DI DN DENGAN PEMBAYARAN MELALUI TELEGRAPH</option>
                                                <option value="14" {{ $caraBayar == '14' ? 'selected' : '' }}>14 - DILAKUKAN TANPA PEMBAYARAN</option>
                                                <option value="15" {{ $caraBayar == '15' ? 'selected' : '' }}>15 - PEMBAYARAN DIMUKA (ADVANCE PAYMENT)</option>
                                                <option value="16" {{ $caraBayar == '16' ? 'selected' : '' }}>16 - SIGHT LETTER OF CREDIT</option>
                                                <option value="17" {{ $caraBayar == '17' ? 'selected' : '' }}>17 - INKASO (COLLECTION DRAFT)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Komoditas</label>
                                        <div class="col-sm-8">
                                            <select name="flagMigas" class="form-control form-control-sm select2bs4">
                                                <option value="">Pilih Komoditas</option>
                                                <option value="2" {{ ($dataDetail['flagMigas'] ?? '2') == '2' ? 'selected' : '' }}>2 - NON MIGAS</option>
                                                <option value="1" {{ ($dataDetail['flagMigas'] ?? '') == '1' ? 'selected' : '' }}>1 - MIGAS</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Barang Kiriman & Curah</label>
                                        <div class="col-sm-4 pr-1">
                                            <select name="flagBarkir" class="form-control form-control-sm select2bs4">
                                                <option value="">Pilih Barang Kiriman</option>
                                                <option value="T" {{ ($dataDetail['flagBarkir'] ?? 'T') == 'T' ? 'selected' : '' }}>T - Non Kiriman</option>
                                                <option value="Y" {{ ($dataDetail['flagBarkir'] ?? '') == 'Y' ? 'selected' : '' }}>Y - Kiriman</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-4 pl-1">
                                            <select name="flagCurah" class="form-control form-control-sm select2bs4">
                                                <option value="">Pilih Barang Curah</option>
                                                <option value="2" {{ ($dataDetail['flagCurah'] ?? '2') == '2' ? 'selected' : '' }}>2 - Non Curah</option>
                                                <option value="1" {{ ($dataDetail['flagCurah'] ?? '') == '1' ? 'selected' : '' }}>1 - Curah</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-3 mt-4 border">
                        <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #0080ff;">Data PKB (Pemberitahuan Kesiapan Barang)</div>
                        <div class="card-body bg-light">
                            <div class="row">
                                <div class="col-md-6">
                                    <fieldset class="border rounded px-3 pb-3 mb-3 bg-white">
                                        <h3 class="w-auto mt-4 mb-2 text-dark font-weight-bold" style="font-size: 16px;">Permintaan Pemeriksaan</h3>
                                        <hr>
                                        <div class="form-group row mb-2 mt-2">
                                            <label class="col-sm-4 col-form-label text-sm">Tanggal PKB</label>
                                            <div class="col-sm-8"><input type="date" class="form-control form-control-sm" name="kesiapanBarang[0][tanggalPkb]" value="{{ $dataDetail['kesiapanBarang'][0]['tanggalPkb'] ?? date('Y-m-d') }}"></div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label text-sm">Alamat Barang</label>
                                            <div class="col-sm-8">
                                                <textarea class="form-control form-control-sm " name="kesiapanBarang[0][alamat]" rows="2">{{ $dataDetail['kesiapanBarang'][0]['alamat'] ?? 'JL RAYA RANCAEKEK MAJALAYA NO 289 SOLOKAN JERUK  BANDUNG' }}</textarea>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label text-sm">Contact Person (PIC)</label>
                                            <div class="col-sm-8"><input type="text" class="form-control form-control-sm " name="kesiapanBarang[0][namaPic]" value="{{ $dataDetail['kesiapanBarang'][0]['namaPic'] ?? 'NENG K' }}"></div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label text-sm">No. Telp PIC</label>
                                            <div class="col-sm-8"><input type="text" class="form-control form-control-sm " name="kesiapanBarang[0][nomorTelpPic]" value="{{ $dataDetail['kesiapanBarang'][0]['nomorTelpPic'] ?? '02275568956' }}"></div>
                                        </div>
                                    </fieldset>
                                </div>
                                <div class="col-md-6">
                                    <fieldset class="border rounded px-3 pb-3 mb-3 bg-white">
                                        <h3 class="w-auto mt-4 mb-2 text-dark font-weight-bold" style="font-size: 16px;">Kondisi & Tempat Siap Periksa</h3>
                                        <hr>
                                        <div class="form-group row mb-2 mt-2">
                                            <label class="col-sm-4 col-form-label text-sm">Waktu Siap Periksa</label>
                                            <div class="col-sm-8"><input type="datetime-local" class="form-control form-control-sm" name="kesiapanBarang[0][waktuSiapPeriksa]" value="{{ isset($dataDetail['kesiapanBarang'][0]['waktuSiapPeriksa']) ? date('Y-m-d H:i', strtotime($dataDetail['kesiapanBarang'][0]['waktuSiapPeriksa'])) : date('Y-m-d H:i') }}"></div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label text-sm">Lokasi Siap Periksa</label>
                                            <div class="col-sm-8"><input type="text" class="form-control form-control-sm " name="kesiapanBarang[0][lokasiSiapPeriksa]" value="{{ $dataDetail['kesiapanBarang'][0]['lokasiSiapPeriksa'] ?? 'Factory' }}"></div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label text-sm">Jenis Gudang Simpan</label>
                                            <div class="col-sm-8">
                                                <select class="form-control form-control-sm select2bs4" name="kesiapanBarang[0][kodeJenisGudang]">
                                                    <option value="">Pilih Tempat Simpan</option>
                                                    <option value="2" {{ ($dataDetail['kesiapanBarang'][0]['kodeJenisGudang'] ?? '2') == '2' ? 'selected' : '' }}>2 - GUDANG PABRIK</option>
                                                    <option value="1" {{ ($dataDetail['kesiapanBarang'][0]['kodeJenisGudang'] ?? '') == '1' ? 'selected' : '' }}>1 - GUDANG VEEM</option>
                                                    <option value="3" {{ ($dataDetail['kesiapanBarang'][0]['kodeJenisGudang'] ?? '') == '3' ? 'selected' : '' }}>3 - GUDANG KONSOLIDASI</option>
                                                    <option value="4" {{ ($dataDetail['kesiapanBarang'][0]['kodeJenisGudang'] ?? '') == '4' ? 'selected' : '' }}>4 - LAINNYA</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-0">
                                            <label class="col-sm-4 col-form-label text-sm">Jenis Barang</label>
                                            <div class="col-sm-8">
                                                <select class="form-control form-control-sm select2bs4" name="kesiapanBarang[0][kodeJenisBarang]">
                                                    <option value="">Pilih Jenis Barang</option>
                                                    <option value="1" {{ ($dataDetail['kesiapanBarang'][0]['kodeJenisBarang'] ?? '1') == '1' ? 'selected' : '' }}>1 - BARANG EKSPOR GABUNGAN</option>
                                                    <option value="2" {{ ($dataDetail['kesiapanBarang'][0]['kodeJenisBarang'] ?? '') == '2' ? 'selected' : '' }}>2 - BAHAN/BARANG ASAL IMP FASILITAS</option>
                                                </select>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-entitas" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #001f3f;">Eksportir</div>
                                <div class="card-body">
                                    <input type="hidden" name="entitas[2][kodeEntitas]" value="2">
                                    <input type="hidden" name="entitas[2][seriEntitas]" value="1">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nomor Identitas</label>
                                        <div class="row">
                                            <div class="col-4 pr-1">
                                                <select name="entitas[2][kodeJenisIdentitas]" class="form-control form-control-sm">
                                                    <option value="6" {{ ($dataDetail['entitas'][2]['kodeJenisIdentitas'] ?? '6') == '6' ? 'selected' : '' }}>6 - NPWP 16 DIGIT</option>
                                                    <option value="5" {{ ($dataDetail['entitas'][2]['kodeJenisIdentitas'] ?? '') == '5' ? 'selected' : '' }}>5 - NPWP 15 DIGIT</option>
                                                </select>
                                            </div>
                                            <div class="col-8 pl-1">
                                                <input type="text" id="nomorIdentitas_2" name="entitas[2][nomorIdentitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][2]['nomorIdentitas'] ?? '' }}">
                                            </div>
                                        </div>
                                        <small class="text-muted font-italic" style="font-size: 11px;">* Masukkan 22 digit lengkap (NPWP + NITKU). CEISA akan otomatis memotong NPWP menjadi 16 digit.</small>
                                    </div>

                                    <div class="form-group mb-2">
                                        <label class="small mb-0">NITKU</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="nitku_2" name="entitas[2][nitku]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][2]['nitku'] ?? '' }}" placeholder="NITKU 22 Digit">
                                        </div>
                                        <small class="text-muted font-italic" style="font-size: 11px;">* Otomatis terisi 22 digit dari nomor identitas atau dapat disesuaikan.</small>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nama</label>
                                        <input type="text" name="entitas[2][namaEntitas]" class="form-control form-control-sm " value="{{ $dataDetail['entitas'][2]['namaEntitas'] ?? '' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Alamat</label>
                                        <textarea name="entitas[2][alamatEntitas]" class="form-control form-control-sm " rows="2">{{ $dataDetail['entitas'][2]['alamatEntitas'] ?? '' }}</textarea>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small mb-0">Status</label>
                                        <select name="entitas[2][kodeStatus]" class="form-control form-control-sm select2bs4 ">
                                            <option value="">Pilih Status</option>
                                            <option value="1" {{ ($dataDetail['entitas'][2]['kodeStatus'] ?? '') == '1' ? 'selected' : '' }}>KOPERASI</option>
                                            <option value="2" {{ ($dataDetail['entitas'][2]['kodeStatus'] ?? '') == '2' ? 'selected' : '' }}>PMDN (MIGAS)</option>
                                            <option value="3" {{ ($dataDetail['entitas'][2]['kodeStatus'] ?? '') == '3' ? 'selected' : '' }}>PMDN (NON MIGAS)</option>
                                            <option value="4" {{ ($dataDetail['entitas'][2]['kodeStatus'] ?? '') == '4' ? 'selected' : '' }}>PMA (MIGAS)</option>
                                            <option value="5" {{ ($dataDetail['entitas'][2]['kodeStatus'] ?? '') == '5' ? 'selected' : '' }}>PMA (NON MIGAS)</option>
                                            <option value="6" {{ ($dataDetail['entitas'][2]['kodeStatus'] ?? '') == '6' ? 'selected' : '' }}>BUMN</option>
                                            <option value="7" {{ ($dataDetail['entitas'][2]['kodeStatus'] ?? '') == '7' ? 'selected' : '' }}>BUMD</option>
                                            <option value="8" {{ ($dataDetail['entitas'][2]['kodeStatus'] ?? '') == '8' ? 'selected' : '' }}>PERORANGAN</option>
                                            <option value="9" {{ ($dataDetail['entitas'][2]['kodeStatus'] ?? '') == '9' ? 'selected' : '' }}>USAHA KECIL MIKRO DAN MENENGAH</option>
                                            <option value="10" {{ ($dataDetail['entitas'][2]['kodeStatus'] ?? '') == '10' ? 'selected' : '' }}>LAINNYA</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header text-white fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color: #001f3f;">
                                    <span>Pembeli <i class="fas fa-question-circle text-light"></i></span>
                                    <button type="button" class="btn btn-sm btn-light border py-0 px-2 ml-auto" id="btn-salin-penerima" title="Salin Data Penerima"><i class="fas fa-copy text-primary"></i> Salin Penerima</button>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" name="entitas[6][kodeEntitas]" value="6">
                                    <input type="hidden" name="entitas[6][seriEntitas]" value="2">
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

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #001f3f;">Penerima</div>
                                <div class="card-body">
                                    <input type="hidden" name="entitas[8][kodeEntitas]" value="8">
                                    <input type="hidden" name="entitas[8][seriEntitas]" value="3">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nama</label>
                                        <input type="text" name="entitas[8][namaEntitas]" class="form-control form-control-sm " value="{{ $dataDetail['entitas'][8]['namaEntitas'] ?? $header->supplier ?? '' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Alamat</label>
                                        <textarea name="entitas[8][alamatEntitas]" class="form-control form-control-sm " rows="2">{{ $dataDetail['entitas'][8]['alamatEntitas'] ?? $header->alamat_supplier ?? '' }}</textarea>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small mb-0">Negara</label>
                                        <select name="entitas[8][kodeNegara]" class="form-control form-control-sm select2bs4 ">
                                            <option value="">Pilih Negara</option>
                                            @include('export-import.dokumen-pabean.options_negara', ['selected' => $dataDetail['entitas'][8]['kodeNegara'] ?? ''])
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #001f3f;">Pihak Yang Melakukan Konsolidasi</div>
                                <div class="card-body">
                                    <input type="hidden" name="entitas[23][kodeEntitas]" value="23">
                                    <input type="hidden" name="entitas[23][seriEntitas]" value="4">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Kategori</label>
                                        <select name="entitas[23][kodeKategoriKonsolidator]" class="form-control form-control-sm select2bs4 ">
                                            <option value="">Pilih Kategori</option>
                                            <option value="1" {{ ($dataDetail['entitas'][23]['kodeKategoriKonsolidator'] ?? '') == '1' ? 'selected' : '' }}>KONSOLIDATOR</option>
                                            <option value="2" {{ ($dataDetail['entitas'][23]['kodeKategoriKonsolidator'] ?? '') == '2' ? 'selected' : '' }}>EKSPORTIR MANDIRI</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nomor Identitas & NITKU</label>
                                        <div class="row">
                                            <div class="col-4 pr-1">
                                                <select name="entitas[23][kodeJenisIdentitas]" class="form-control form-control-sm">
                                                    <option value="6">NPWP 16</option>
                                                    <option value="5">NPWP 15</option>
                                                </select>
                                            </div>
                                            <div class="col-8 pl-1"><input type="text" name="entitas[23][nomorIdentitas]" class="form-control form-control-sm " placeholder="No. Identitas" value="{{ $dataDetail['entitas'][23]['nomorIdentitas'] ?? '' }}"></div>
                                        </div>
                                        <input type="text" name="entitas[23][nitku]" class="form-control form-control-sm  mt-1" placeholder="NITKU 22 Digit" value="{{ $dataDetail['entitas'][23]['nitku'] ?? '' }}">
                                    </div>
                                    <div class="form-group mb-2"><label class="small mb-0">Nama</label><input type="text" name="entitas[23][namaEntitas]" class="form-control form-control-sm " value="{{ $dataDetail['entitas'][23]['namaEntitas'] ?? '' }}"></div>
                                    <div class="form-group mb-0"><label class="small mb-0">Alamat Konsolidasi</label><textarea name="entitas[23][alamatEntitas]" class="form-control form-control-sm " rows="2">{{ $dataDetail['entitas'][23]['alamatEntitas'] ?? '' }}</textarea></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PEMILIK BARANG (APPEND INLINE) -->
                    <div class="card shadow-sm mt-2 border">
                        <div class="card-header text-dark fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color: #f8f9fa;">
                            <span>Pemilik Barang </span>
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
                                    @php $pemiliks = $dataDetail['pemilik'] ?? []; @endphp
                                    @forelse($pemiliks as $pIndex => $pem)
                                    <tr>
                                        <td class="text-center p-2 align-middle"><input type="text" class="form-control form-control-sm text-center bg-light" value="{{ $pIndex + 1 }}" readonly></td>
                                        <td class="p-2">
                                            <select name="pemilik[{{ $pIndex }}][jenisId]" class="form-control form-control-sm mb-1 ">
                                                <option value="6" {{ ($pem['jenisId'] ?? '') == '6' ? 'selected' : '' }}>NPWP 16 DIGIT</option>
                                                <option value="5" {{ ($pem['jenisId'] ?? '') == '5' ? 'selected' : '' }}>NPWP 15 DIGIT</option>
                                                <option value="2" {{ ($pem['jenisId'] ?? '') == '2' ? 'selected' : '' }}>Paspor</option>
                                                <option value="3" {{ ($pem['jenisId'] ?? '') == '3' ? 'selected' : '' }}>KTP</option>
                                            </select>
                                            <input type="text" name="pemilik[{{ $pIndex }}][noId]" class="form-control form-control-sm " value="{{ $pem['noId'] ?? '' }}" placeholder="No. Identitas">
                                        </td>
                                        <td class="p-2"><textarea name="pemilik[{{ $pIndex }}][alamat]" class="form-control form-control-sm " rows="2" placeholder="Alamat">{{ $pem['alamat'] ?? '' }}</textarea></td>
                                        <td class="p-2 align-middle">
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="pemilik[{{ $pIndex }}][nama]" class="form-control form-control-sm " value="{{ $pem['nama'] ?? '' }}" placeholder="Nama Pemilik">
                                                <div class="input-group-append"><button type="button" class="btn btn-danger btn-hapus-pemilik"><i class="fas fa-trash-alt"></i></button></div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr class="no-data-row"><td colspan="4" class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-2 text-light"></i><br>No Data</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-dokumen" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color: #001f3f;">
                            <span>Dokumen Pendukung</span>
                            <button type="button" class="btn btn-sm btn-light btn-add-action py-0 px-2 ml-auto" id="btn-add-dok" title="Tambah Dokumen"><i class="fas fa-plus text-primary"></i></button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0" id="table-dokumen">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="25%">Kode Dokumen</th>
                                    <th width="25%">Nomor Dokumen</th>
                                    <th width="15%">Tgl Dokumen</th>
                                    <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-dokumen">
                                    @php
                                        $dokumens = $dataDetail['dok'] ?? [];
                                    @endphp
                                    @foreach($dokumens as $index => $dok)
                                        <tr>
                                            <td>
                                                <select name="dok[{{ $index }}][kode]" class="form-control form-control-sm select2bs4">
                                                    <option value="">-- Pilih Kode --</option>
                                                    @foreach($referensiDokumen as $val => $text)
                                                        {{-- Gunakan kodeDokumen (baru) atau kode (lama) --}}
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
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #001f3f;">Rincian Rute Pengangkutan</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Tempat Penimbunan </label>
                                        <div class="col-sm-8">
                                            <input type="text" name="kodeTps" class="form-control form-control-sm " placeholder="Contoh: G001" value="{{ $dataDetail['kodeTps'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Pelabuhan Muat Asal</label>
                                        <div class="col-sm-8">
                                            <select name="kodePelMuat" class="form-control form-control-sm select2-pelabuhan">
                                                @if(!empty($dataDetail['kodePelMuat']))
                                                    <option value="{{ $dataDetail['kodePelMuat'] }}" selected>{{ $dataDetail['kodePelMuat'] }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Jenis Pengangkutan</label>
                                        <div class="col-sm-8">
                                            <select name="kodeJenisPengangkutan" class="form-control form-control-sm select2bs4 ">
                                                <option value="">-- Pilih Jenis Pengangkutan --</option>
                                                <option value="1" {{ ($dataDetail['kodeJenisPengangkutan'] ?? '') == '1' ? 'selected' : '' }}>1 - SATU SARANA ANGKUT</option>
                                                <option value="2" {{ ($dataDetail['kodeJenisPengangkutan'] ?? '') == '2' ? 'selected' : '' }}>2 - INSTALASI/PIPA/TRANSMISI</option>
                                                <option value="3" {{ ($dataDetail['kodeJenisPengangkutan'] ?? '') == '3' ? 'selected' : '' }}>3 - ANGKUT LANJUT</option>
                                                <option value="4" {{ ($dataDetail['kodeJenisPengangkutan'] ?? '') == '4' ? 'selected' : '' }}>4 - ANGKUT LANJUT MULTIMODA</option>
                                                <option value="5" {{ ($dataDetail['kodeJenisPengangkutan'] ?? '') == '5' ? 'selected' : '' }}>5 - BARANG BAWAAN PENUMPANG / AWAK SARKUT</option>
                                                <option value="6" {{ ($dataDetail['kodeJenisPengangkutan'] ?? '') == '6' ? 'selected' : '' }}>6 - SARANA ANGKUT LAINNYA</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Pelabuhan Tujuan</label>
                                        <div class="col-sm-8">
                                            <select name="kodePelTujuan" class="form-control form-control-sm select2-pelabuhan">
                                                @if(!empty($dataDetail['kodePelTujuan']))
                                                    <option value="{{ $dataDetail['kodePelTujuan'] }}" selected>{{ $dataDetail['kodePelTujuan'] }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Negara Tujuan Ekspor</label>
                                        <div class="col-sm-8">
                                            <select name="kodeNegaraTujuan" class="form-control form-control-sm select2bs4 ">
                                                <option value="">Pilih Negara</option>
                                                @include('export-import.dokumen-pabean.options_negara', ['selected' => $dataDetail['kodeNegaraTujuan'] ?? ''])
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Tgl Perkiraan Ekspor</label>
                                        <div class="col-sm-8"><input type="date" name="tanggalEkspor" class="form-control form-control-sm " value="{{ $dataDetail['tanggalEkspor'] ?? '' }}"></div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Lokasi Pemeriksaan</label>
                                        <div class="col-sm-8">
                                            <select name="kodeLokasi" class="form-control form-control-sm select2bs4 ">
                                                <option value="">Pilih Lokasi Pemeriksaan</option>
                                                <option value="1" {{ (old('kodeLokasi') == '1' || ($dataDetail['kodeLokasi'] ?? '') == '1') ? 'selected' : '' }}>1 - KAWASAN PABEAN TEMPAT PEMUATAN</option>
                                                <option value="2" {{ (old('kodeLokasi') == '2' || ($dataDetail['kodeLokasi'] ?? '') == '2') ? 'selected' : '' }}>2 - GUDANG EKSPORTIR</option>
                                                <option value="3" {{ (old('kodeLokasi') == '3' || ($dataDetail['kodeLokasi'] ?? '') == '3') ? 'selected' : '' }}>3 - TEMPAT LAIN YANG DIIZINKAN</option>
                                                <option value="4" {{ (old('kodeLokasi') == '4' || ($dataDetail['kodeLokasi'] ?? '') == '4') ? 'selected' : '' }}>4 - TEMPAT PENIMBUNAN SEMENTARA</option>
                                                <option value="5" {{ (old('kodeLokasi') == '5' || ($dataDetail['kodeLokasi'] ?? '') == '5') ? 'selected' : '' }}>5 - TEMPAT PENIMBUNAN PABEAN</option>
                                                <option value="6" {{ (old('kodeLokasi') == '6' || ($dataDetail['kodeLokasi'] ?? '') == '6') ? 'selected' : '' }}>6 - TEMPAT PENIMBUNAN BERIKAT</option>
                                                <option value="7" {{ (old('kodeLokasi') == '7' || ($dataDetail['kodeLokasi'] ?? '') == '7') ? 'selected' : '' }}>7 - TEMPAT PENIMBUTAN LAINNYA</option>
                                                <option value="8" {{ (old('kodeLokasi') == '8' || ($dataDetail['kodeLokasi'] ?? '') == '8') ? 'selected' : '' }}>8 - GUDANG KONSOLIDATOR</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Tanggal Periksa</label>
                                        <div class="col-sm-8"><input type="date" name="tanggalPeriksa" class="form-control form-control-sm " value="{{ $dataDetail['tanggalPeriksa'] ?? '' }}"></div>
                                    </div>
                                    <div class="form-group row mb-0">
                                        <label class="col-sm-4 col-form-label text-sm">Kantor BC Pemeriksa</label>
                                        <div class="col-sm-8">
                                            <select name="kodeKantorPeriksa" class="form-control form-control-sm select2bs4 ">
                                                <option value="">Pilih Kantor</option>
                                                @foreach($kantorList as $val => $label)
                                                    <option value="{{ $val }}" {{ ($dataDetail['kodeKantorPeriksa'] ?? '050500') == $val ? 'selected' : '' }}>{{ $val }} - {{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color: #001f3f;">
                            <span>Daftar Sarana Pengangkut</span>
                            <button type="button" id="btn-add-sarkut" class="btn btn-sm btn-light btn-add-action py-0 px-2 ml-auto" title="Tambah Sarana Angkut"><i class="fas fa-plus text-primary"></i></button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="bg-light text-center" style="font-size: 12px;">
                                    <tr>
                                        <th width="10%">Seri</th>
                                        <th width="30%">Nama Sarana Angkut</th>
                                        <th width="25%">No. Pengangkut (Voy/Flight)</th>
                                        <th width="20%">Cara Angkut</th>
                                        <th width="15%">Kode Bendera</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-sarkut">
                                    @php $pengangkuts = $dataDetail['pengangkut'] ?? []; @endphp
                                    @forelse($pengangkuts as $sIndex => $sarkut)
                                    <tr>
                                        <td class="text-center align-middle"><input type="text" class="form-control form-control-sm text-center bg-light" value="{{ $sIndex + 1 }}" readonly></td>
                                        <td><input type="text" name="pengangkut[{{ $sIndex }}][namaPengangkut]" class="form-control form-control-sm " value="{{ $sarkut['namaPengangkut'] ?? '' }}"></td>
                                        <td><input type="text" name="pengangkut[{{ $sIndex }}][nomorPengangkut]" class="form-control form-control-sm " value="{{ $sarkut['nomorPengangkut'] ?? '' }}"></td>
                                        <td>
                                            <select name="pengangkut[{{ $sIndex }}][kodeCaraAngkut]" class="form-control form-control-sm ">
                                                <option value="1" {{ ($sarkut['kodeCaraAngkut'] ?? '') == '1' ? 'selected' : '' }}>1 - LAUT</option>
                                                <option value="4" {{ ($sarkut['kodeCaraAngkut'] ?? '') == '4' ? 'selected' : '' }}>4 - UDARA</option>
                                                <option value="3" {{ ($sarkut['kodeCaraAngkut'] ?? '') == '3' ? 'selected' : '' }}>3 - DARAT</option>
                                            </select>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="pengangkut[{{ $sIndex }}][kodeBendera]" class="form-control form-control-sm  text-uppercase" value="{{ $sarkut['kodeBendera'] ?? '' }}">
                                                <div class="input-group-append"><button type="button" class="btn btn-danger btn-hapus-sarkut"><i class="fas fa-trash-alt"></i></button></div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty

                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-kemasan" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color: #001f3f;">
                            <span>Data Kemasan Ekspor</span>
                            <button type="button" class="btn btn-sm btn-light btn-add-action py-0 px-2 ml-auto" id="btn-add-kemasan" title="Tambah Kemasan"><i class="fas fa-plus text-primary"></i></button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0" id="table-kemasan">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="20%">Jumlah Kemasan</th>
                                        <th width="40%">Jenis Kemasan</th>
                                        <th width="30%">Merek Kemasan</th>
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
                                        <td><input type="number" step="any" name="kemasan[{{ $index }}][jumlahKemasan]" class="form-control form-control-sm  input-decimal" value="{{ $kemasan['jumlahKemasan'] ?? $kemasan['jumlah'] ?? 0 }}"></td>
                                        <td>
                                            <select name="kemasan[{{ $index }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4 ">
                                                <option value="">-- Pilih --</option>
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
                        <div class="card-header text-white fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color: #001f3f;">
                            <span>Data Peti Kemas / Kontainer</span>
                            <button type="button" class="btn btn-sm btn-light btn-add-action py-0 px-2 ml-auto" id="btn-add-kontainer" title="Tambah Kontainer"><i class="fas fa-plus text-primary"></i></button>
                        </div>
                        <div class="card-body p-0">
                            @php
                                $kontainers = $dataDetail['kontainer'] ?? [];
                            @endphp
                            <table class="table table-sm table-bordered mb-0" id="table-kontainer">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="30%">Nomor Kontainer</th>
                                        <th width="20%">Jenis</th>
                                        <th width="25%">Tipe</th>
                                        <th width="15%">Ukuran</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-kontainer">
                                    @foreach($kontainers as $kIndex => $kont)
                                    <tr>
                                        <td><input type="text" name="kontainer[{{ $kIndex }}][nomorKontainer]" class="form-control form-control-sm text-uppercase" value="{{ $kont['nomorKontainer'] ?? '' }}"></td>
                                        <td>
                                            <select name="kontainer[{{ $kIndex }}][kodeJenisKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih --</option>
                                                @foreach($listJenisKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kont['kodeJenisKontainer'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="kontainer[{{ $kIndex }}][kodeTipeKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih --</option>
                                                @foreach($listTipeKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kont['kodeTipeKontainer'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="kontainer[{{ $kIndex }}][kodeUkuranKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih --</option>
                                                @foreach($listUkuranKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kont['kodeUkuranKontainer'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
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
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #001f3f;">Rincian Keuangan & Nilai Ekspor</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-2"><label>Jenis Valuta</label>
                                        <select name="kodeValuta" class="form-control form-control-sm select2bs4" id="kode_valuta">
                                            <option value="">Pilih Valuta</option>
                                            @foreach($listValuta as $k => $v)
                                                <option value="{{ $k }}" {{ ($dataDetail['kodeValuta'] ?? 'USD') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">NDPBM</label>
                                        <input type="number" step="any" name="ndpbm" class="form-control form-control-sm" value="{{ $dataDetail['ndpbm'] ?? 0 }}" id="ndpbm">
                                        <div class="d-flex justify-content-end">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-info btn-sm" id="btn-get-kurs">
                                                    <i class="fas fa-sync-alt"></i> Tarik Kurs CEISA
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-2"><label>Cara Penyerahan (Incoterm)</label>
                                        <select name="kodeIncoterm" class="form-control form-control-sm select2bs4">
                                            <option value="">-- Pilih --</option>
                                            @foreach($listIncoterm as $k => $v)
                                                <option value="{{ $k }}" {{ ($dataDetail['kodeIncoterm'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 border-left">
                                    <div class="form-group mb-2"><label>Nilai FOB Pengajuan</label><input type="number" step="any" name="fob" class="form-control form-control-sm " value="{{ $dataDetail['fob'] ?? '0.00' }}"></div>
                                    <div class="form-group mb-2"><label>Freight</label><input type="number" step="any" name="freight" class="form-control form-control-sm " value="{{ $dataDetail['freight'] ?? '0.00' }}"></div>
                                    <div class="row">
                                        <div class="col-5 form-group mb-2 pr-1"><label>Tempat Asuransi</label>
                                            <select name="kodeAsuransi" class="form-control form-control-sm ">
                                                <option value="LN" {{ ($dataDetail['kodeAsuransi'] ?? 'DN') == 'LN' ? 'selected' : '' }}>LUAR NEGERI</option>
                                                <option value="DN" {{ ($dataDetail['kodeAsuransi'] ?? 'DN') == 'DN' ? 'selected' : '' }}>DALAM NEGERI</option>
                                            </select>
                                        </div>
                                        <div class="col-7 form-group mb-2 pl-1"><label>Nilai Asuransi</label><input type="number" step="any" name="asuransi" class="form-control form-control-sm " value="{{ $dataDetail['asuransi'] ?? '0.00' }}"></div>
                                    </div>
                                </div>
                                <div class="col-md-4 border-left">
                                    <div class="form-group mb-2"><label>Berat Kotor / Bruto (Kg)</label><input type="number" step="any" name="bruto" class="form-control form-control-sm " value="{{ $dataDetail['bruto'] ?? '0.00' }}"></div>
                                    <div class="form-group mb-2"><label>Berat Bersih / Netto (Kg)</label><input type="number" step="any" name="netto" class="form-control form-control-sm " value="{{ $dataDetail['netto'] ?? '0.00' }}"></div>
                                    <div class="form-group mb-2"><label>Nilai Jasa Maklon</label><input type="number" step="any" name="nilaiMaklon" class="form-control form-control-sm" value="{{ $dataDetail['nilaiMaklon'] ?? '0.00' }}"></div>

                                    <div class="form-group mb-3 mt-3">
                                        <div class="row align-items-center">
                                            <div class="col-sm-6">
                                                <label class="text-sm font-weight-bold mb-0">
                                                    <input type="checkbox" id="check-pph" name="isNilaiPph" class="mr-1" {{ !empty($dataDetail['nilaiPph']) ? 'checked' : '' }}>
                                                    PPh Ps.22 Ekspor
                                                </label>
                                            </div>
                                            <div class="col-sm-6 text-right">
                                                <div class="fw-bold text-success mb-0" style="font-size: 14px;" id="text-nilai-pph">Rp 0,00</div>
                                                <input type="hidden" name="nilaiPph" id="val-nilai-pph" value="{{ $dataDetail['nilaiPph'] ?? '0.00' }}">
                                            </div>
                                            <div class="col-12 mt-1">
                                                <small class="text-muted font-italic" style="font-size: 10px;">* PPh = 1,5% x FOB x Kurs (NDPBM)</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0"><label>Total Pungutan Sawit</label><input type="number" step="any" name="totalDanaSawit" class="form-control form-control-sm" value="{{ $dataDetail['totalDanaSawit'] ?? '0.00' }}"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color: #001f3f;">
                            <span>Bank Devisa Hasil Ekspor</span>
                            <button type="button" id="btn-add-bank" class="btn btn-sm btn-light btn-add-action py-0 px-2 ml-auto" title="Tambah Bank"><i class="fas fa-plus text-primary"></i></button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="bg-light text-center" style="font-size: 12px;">
                                    <tr>
                                        <th width="10%">Seri</th>
                                        <th width="30%">Kode Bank</th>
                                        <th width="60%">Nama Bank Devisa</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-bank">
                                    @php
                                        $banks = $dataDetail['bankDevisa'] ?? [];
                                        if (empty($banks)) {
                                            $banks[] = ['kodeBank' => '14', 'namaBank' => 'BANK CENTRAL ASIA'];
                                        }
                                    @endphp
                                    @forelse($banks as $bIndex => $bank)
                                    <tr>
                                        <td class="text-center align-middle"><input type="text" class="form-control form-control-sm text-center bg-light" value="{{ $bIndex + 1 }}" readonly></td>
                                        <td>
                                            <select name="bankDevisa[{{ $bIndex }}][kodeBank]" class="form-control form-control-sm select2bs4 select-bank">
                                                <option value="">Pilih Bank</option>
                                                @include('export-import.dokumen-pabean.options_bank', ['selected' => $bank['kodeBank'] ?? '', 'selectedName' => $bank['namaBank'] ?? ''])
                                            </select>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="bankDevisa[{{ $bIndex }}][namaBank]" class="form-control form-control-sm input-nama-bank" value="{{ $bank['namaBank'] ?? '' }}" placeholder="Nama Bank">
                                                <div class="input-group-append"><button type="button" class="btn btn-danger btn-hapus-bank"><i class="fas fa-trash-alt"></i></button></div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
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
                                @php $draftItem = $dataDetail['barang'][$index] ?? []; @endphp

                                <div class="card mb-2 border">
                                    <div class="card-header bg-light py-2 btn-collapse-barang" data-target="#collapseBarang{{ $index }}" style="cursor: pointer;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-bold" style="font-size: 13px; color: white;">
                                                {{ $item->goods_code ?? $item->id_item }} - {{ $item->itemdesc }}
                                            </div>
                                            <i class="fas fa-chevron-down text-muted icon-collapse"></i>
                                        </div>
                                    </div>

                                    <div id="collapseBarang{{ $index }}" class="collapse" data-parent="#accordionBarang">
                                        <div class="card-body py-3 px-3 bg-white">

                                            <!-- Hidden inputs wajib untuk API -->
                                            <input type="hidden" name="barang[{{ $index }}][seriBarang]" value="{{ $index + 1 }}">
                                            <input type="hidden" name="barang[{{ $index }}][kodeDokumen]" value="30">

                                            <!-- Layout 2 Kolom Sesuai Portal CEISA 4.0 -->
                                            <div class="row">
                                                <!-- KOLOM KIRI -->
                                                <div class="col-md-6 pr-md-4">
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Seri</label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control form-control-sm bg-light" value="{{ $index + 1 }}" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Pos Tarif/HS </label>
                                                        <div class="col-sm-8">
                                                            <input type="text" name="barang[{{ $index }}][posTarif]" class="form-control form-control-sm" value="{{ $draftItem['posTarif'] ?? '48191000' }}" placeholder="Masukkan Pos Tarif/HS">
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Kode Barang </label>
                                                        <div class="col-sm-8">
                                                            <input type="text" name="barang[{{ $index }}][kodeBarang]" class="form-control form-control-sm" value="{{ $draftItem['kodeBarang'] ?? $item->goods_code ?? $item->id_item ?? '-' }}">
                                                            <input type="text" name="barang[{{ $index }}][idItem]" class="form-control form-control-sm hidden" value="{{ $item->id_item ?? '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Uraian Jenis Barang </label>
                                                        <div class="col-sm-8">
                                                            <textarea name="barang[{{ $index }}][uraian]" class="form-control form-control-sm" rows="3">{{ $draftItem['uraian'] ?? $item->itemdesc ?? '' }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Merek </label>
                                                        <div class="col-sm-8">
                                                            <input type="text" name="barang[{{ $index }}][merk]" class="form-control form-control-sm" value="{{ $draftItem['merk'] ?? '-' }}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Tipe </label>
                                                        <div class="col-sm-8">
                                                            <input type="text" name="barang[{{ $index }}][tipe]" class="form-control form-control-sm" value="{{ $draftItem['tipe'] ?? '-' }}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Ukuran </label>
                                                        <div class="col-sm-8">
                                                            <input type="text" name="barang[{{ $index }}][ukuran]" class="form-control form-control-sm" value="{{ $draftItem['ukuran'] ?? '-' }}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Negara Asal Barang </label>
                                                        <div class="col-sm-8">
                                                            <select name="barang[{{ $index }}][kodeNegaraAsal]" class="form-control form-control-sm select2bs4">
                                                                <option value="">Pilih Negara Asal Barang</option>
                                                                @include('export-import.dokumen-pabean.options_negara', ['selected' => $draftItem['kodeNegaraAsal'] ?? 'ID'])
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Daerah Asal Barang </label>
                                                        <div class="col-sm-8">
                                                            <select name="barang[{{ $index }}][kodeDaerahAsal]" class="form-control form-control-sm select2bs4">
                                                                <option value="">Pilih Daerah Asal Barang</option>
                                                                @include('export-import.dokumen-pabean.options_daerah', ['selected' => $draftItem['kodeDaerahAsal'] ?? '3204' ?? ''])
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- KOLOM KANAN -->
                                                <div class="col-md-6 pl-md-4">
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Jumlah dan Satuan Barang </label>
                                                        <div class="col-sm-4 pr-1">
                                                            <input type="number" step="any" name="barang[{{ $index }}][jumlahSatuan]" class="form-control form-control-sm" value="{{ $draftItem['jumlahSatuan'] ?? (float) $item->qty }}">
                                                        </div>
                                                        <div class="col-sm-4 pl-1">
                                                            <select name="barang[{{ $index }}][kodeSatuanBarang]" class="form-control form-control-sm select2bs4 kode-satuan-barang">
                                                                <option value="">Pilih Kode Satuan</option>
                                                                @foreach($listSatuanBarang as $k => $v)
                                                                    <option value="{{ $k }}" {{ ($draftItem['kodeSatuanBarang'] ?? $item->unit) == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Kemasan </label>
                                                        <div class="col-sm-4 pr-1">
                                                            <input type="number" step="any" name="barang[{{ $index }}][jumlahKemasan]" class="form-control form-control-sm" value="{{ $draftItem['jumlahKemasan'] ?? 0 }}">
                                                        </div>
                                                        <div class="col-sm-4 pl-1">
                                                            <select name="barang[{{ $index }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4">
                                                                <option value="">Pilih Kode Jenis Kemasan</option>
                                                                @foreach($listJenisKemasan as $k => $v)
                                                                    <option value="{{ $k }}" {{ ($draftItem['kodeJenisKemasan'] ?? 'CT') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Nilai Ekspor (Nilai FOB) </label>
                                                        <div class="col-sm-8">
                                                            <input type="number" step="any" name="barang[{{ $index }}][fob]" class="form-control form-control-sm" value="{{ $draftItem['fob'] ?? (float)($item->qty * $item->price) }}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Berat Bersih (Kg) </label>
                                                        <div class="col-sm-8">
                                                            <input type="number" step="any" name="barang[{{ $index }}][netto]" class="form-control form-control-sm" value="{{ $draftItem['netto'] ?? (float) ($item->nw ?? $item->netto ?? 0) }}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Nilai Ekspor per satuan barang </label>
                                                        <div class="col-sm-8">
                                                            <input type="number" step="any" name="barang[{{ $index }}][hargaEkspor]" class="form-control form-control-sm" value="{{ $draftItem['hargaEkspor'] ?? (float)$item->price }}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Jenis Ekspor</label>
                                                        <div class="col-sm-8">
                                                            <select name="barang[{{ $index }}][kodeJenisEkspor]" class="form-control form-control-sm select2bs4">
                                                                <option value="">Pilih Jenis Ekspor</option>
                                                                @php $kJe = $draftItem['kodeJenisEkspor'] ?? '1'; @endphp
                                                                <option value="1" {{ $kJe == '1' ? 'selected' : '' }}>1 - Ekspor Biasa</option>
                                                                <option value="2" {{ $kJe == '2' ? 'selected' : '' }}>2 - Berkala</option>
                                                                <option value="3" {{ $kJe == '3' ? 'selected' : '' }}>3 - Fasilitas</option>
                                                                <option value="4" {{ $kJe == '4' ? 'selected' : '' }}>4 - Re-Import</option>
                                                                <option value="5" {{ $kJe == '5' ? 'selected' : '' }}>5 - Re-Ekspor</option>
                                                                <option value="6" {{ $kJe == '6' ? 'selected' : '' }}>6 - Ekspor Sementara</option>
                                                                <option value="7" {{ $kJe == '7' ? 'selected' : '' }}>7 - Ekspor Gabungan</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr class="mt-4 mb-4">

                                            <!-- TABEL DOKUMEN FASILITAS/LARTAS (DALAM BARANG) -->
                                            <div class="card shadow-sm mb-4 border">
                                                <div class="card-header text-dark fw-bold d-flex justify-content-between align-items-center px-3 py-2" style="font-size:13px; background-color: #f8f9fa;">
                                                    <span>Dokumen Fasilitas/Lartas</span>
                                                    <button type="button" class="btn btn-sm btn-primary py-0 px-2 btn-add-dok-fasilitas" data-itemidx="{{ $index }}"><i class="fas fa-plus"></i> Tambah</button>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-borderless mb-0">
                                                            <thead class="bg-light text-center border-bottom">
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

                                            <!-- TABEL ENTITAS BARANG (DALAM BARANG) -->
                                            <div class="card shadow-sm mb-0 border">
                                                <div class="card-header text-dark fw-bold d-flex justify-content-between align-items-center px-3 py-2" style="font-size:13px; background-color: #f8f9fa;">
                                                    <span>Entitas Barang</span>
                                                    <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-add-entitas-barang" data-itemidx="{{ $index }}"><i class="fas fa-plus text-primary"></i> Tambah</button>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-borderless mb-0">
                                                            <thead class="bg-light text-center border-bottom">
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
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-pungutan" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color: #001f3f;">
                            <span>Pungutan</span>
                            <button type="button" class="btn btn-sm btn-light btn-add-action py-0 px-2 ml-auto"><i class="fas fa-sync-alt text-primary"></i> Generate</button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless mb-0">
                                    <thead class="bg-light text-center border-bottom">
                                        <tr>
                                            <th class="align-middle">Pungutan</th>
                                            <th class="align-middle">Dibayar</th>
                                            <th class="align-middle">Ditanggung Pemerintah</th>
                                            <th class="align-middle">Ditunda</th>
                                            <th class="align-middle">Tidak Dipungut</th>
                                            <th class="align-middle">Dibebaskan</th>
                                            <th class="align-middle">Sudah Dilunasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center p-2"><input type="text" class="form-control form-control-sm" disabled></td>
                                            <td class="text-center p-2"><input type="text" class="form-control form-control-sm" disabled></td>
                                            <td class="text-center p-2"><input type="text" class="form-control form-control-sm" disabled></td>
                                            <td class="text-center p-2"><input type="text" class="form-control form-control-sm" disabled></td>
                                            <td class="text-center p-2"><input type="text" class="form-control form-control-sm" disabled></td>
                                            <td class="text-center p-2"><input type="text" class="form-control form-control-sm" disabled></td>
                                            <td class="text-center p-2"><input type="text" class="form-control form-control-sm" disabled></td>
                                        </tr>
                                        <tr><td colspan="7" class="text-center py-4 text-muted">No Data</td></tr>
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
                                <div class="col-md-3 form-group"><label>Nama TTD</label><input type="text" name="namaTtd" class="form-control form-control-sm " value="{{ $dataDetail['namaTtd'] ?? 'YUS YULIUS' }}"></div>
                                <div class="col-md-3 form-group"><label>Jabatan</label><input type="text" name="jabatanTtd" class="form-control form-control-sm " value="{{ $dataDetail['jabatanTtd'] ?? 'MANAGER EXIM' }}"></div>
                                <div class="col-md-3 form-group"><label>Kota TTD</label><input type="text" name="kotaTtd" class="form-control form-control-sm " value="{{ $dataDetail['kotaTtd'] ?? 'BANDUNG' }}"></div>
                                <div class="col-md-3 form-group"><label>Tanggal TTD</label><input type="date" name="tanggalTtd" class="form-control form-control-sm " value="{{ $dataDetail['tanggalTtd'] ?? date('Y-m-d') }}"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="card-footer text-right bg-white border-top sticky-action">
            <a href="{{ route('dokumen-pabean-index') }}" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button type="submit" class="btn btn-success btn-sm">
                <i class="fas fa-save"></i> Simpan Draft
            </button>
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
                        <select id="modal-pemilik-jenis-identitas" class="form-control" style="max-width:160px;">
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
        $('.kode-satuan-barang').val('PCE').trigger('change');

        // ================= DOKUMEN HANDLER =================
        const optDokumenHtml = `
            <option value="">-- Pilih Kode --</option>
            @foreach($referensiDokumen as $val => $text) <option value="{{ $val }}">{{ $val }} - {{ $text }}</option> @endforeach
        `;
        let dokIndex = {{ count($dokumens ?? []) }};
        $('#btn-add-dok').on('click', function() {
            let htmlTr = `
                <tr>
                    <td><select name="dok[${dokIndex}][kode]" class="form-control form-control-sm select2bs4-dynamic ">${optDokumenHtml}</select></td>
                    <td><input type="text" name="dok[${dokIndex}][nomor]" class="form-control form-control-sm "></td>
                    <td><input type="date" name="dok[${dokIndex}][tgl]" class="form-control form-control-sm "></td>
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
        const optJenisKontainer = `<option value="">-- Pilih --</option>` + `@foreach($listJenisKontainer as $k => $v)<option value="{{ $k }}">{{ $k }} - {{ $v }}</option>@endforeach`;
        const optTipeKontainer = `<option value="">-- Pilih --</option>` + `@foreach($listTipeKontainer as $k => $v)<option value="{{ $k }}">{{ $k }} - {{ $v }}</option>@endforeach`;
        const optUkuranKontainer = `<option value="">-- Pilih --</option>` + `@foreach($listUkuranKontainer as $k => $v)<option value="{{ $k }}">{{ $k }} - {{ $v }}</option>@endforeach`;
        let kontainerIndex = {{ count($kontainers ?? []) }};
        $('#btn-add-kontainer').on('click', function() {
            let htmlTr = `
                <tr>
                    <td><input type="text" name="kontainer[${kontainerIndex}][nomorKontainer]" class="form-control form-control-sm  text-uppercase"></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeJenisKontainer]" class="form-control form-control-sm select2bs4">${optJenisKontainer}</select></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeTipeKontainer]" class="form-control form-control-sm select2bs4">${optTipeKontainer}</select></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeUkuranKontainer]" class="form-control form-control-sm select2bs4">${optUkuranKontainer}</select></td>
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
                <td><select name="pengangkut[${sarkutIndex}][kodeCaraAngkut]" class="form-control form-control-sm select2bs4"><option value="">Pilih Cara Angkut</option>` + `@foreach($listCaraAngkut as $k => $v)<option value="{{ $k }}">{{ $k }} - {{ $v }}</option>@endforeach` + `</select></td>
                <td><div class="input-group input-group-sm"><input type="text" name="pengangkut[${sarkutIndex}][kodeBendera]" class="form-control form-control-sm  text-uppercase"><div class="input-group-append"><button type="button" class="btn btn-danger btn-hapus-sarkut"><i class="fas fa-trash-alt"></i></button></div></div></td>
            </tr>`;
            $('#tbody-sarkut').append(tr);
            sarkutIndex++;
        });
        $(document).on('click', '.btn-hapus-sarkut', function() { $(this).closest('tr').remove(); });

        // ================= BANK HANDLER =================
        const optBankHtml = `
            <option value="">Pilih Bank</option>
            @include('export-import.dokumen-pabean.options_bank')
        `;
        let bankIndex = {{ count($banks ?? []) }};
        $('#btn-add-bank').on('click', function() {
            let tr = `<tr>
                <td class="text-center align-middle"><input type="text" class="form-control form-control-sm text-center bg-light" value="${bankIndex + 1}" readonly></td>
                <td><select name="bankDevisa[${bankIndex}][kodeBank]" class="form-control form-control-sm select2bs4-dynamic select-bank">${optBankHtml}</select></td>
                <td><div class="input-group input-group-sm"><input type="text" name="bankDevisa[${bankIndex}][namaBank]" class="form-control form-control-sm input-nama-bank" placeholder="Nama Bank"><div class="input-group-append"><button type="button" class="btn btn-danger btn-hapus-bank"><i class="fas fa-trash-alt"></i></button></div></div></td>
            </tr>`;
            $('#tbody-bank').append(tr);
            $(`select[name="bankDevisa[${bankIndex}][kodeBank]"]`).select2({ theme: 'bootstrap4', width: '100%' });
            bankIndex++;
        });
        $(document).on('click', '.btn-hapus-bank', function() { $(this).closest('tr').remove(); });
        $(document).on('change', '.select-bank', function() {
            let selectedOption = $(this).find('option:selected');
            let bankName = selectedOption.data('name') || '';
            if (bankName) {
                $(this).closest('tr').find('.input-nama-bank').val(bankName);
            }
        });

        // ================= JS PEMILIK BARANG =================
        let pemilikIndex = {{ count($pemiliks ?? []) }};
        $('#btn-add-pemilik').on('click', function(e) {
            e.preventDefault();
            $('#tbody-pemilik .no-data-row').remove();

            let tr = `<tr>
                <td class="text-center p-2 align-middle"><input type="text" class="form-control form-control-sm text-center bg-light" value="${pemilikIndex + 1}" readonly></td>
                <td class="p-2">
                    <select name="pemilik[${pemilikIndex}][jenisId]" class="form-control form-control-sm mb-1 ">
                        <option value="6">NPWP 16 DIGIT</option>
                        <option value="5">NPWP 15 DIGIT</option>
                        <option value="2">Paspor</option>
                        <option value="3">KTP</option>
                    </select>
                    <input type="text" name="pemilik[${pemilikIndex}][noId]" class="form-control form-control-sm " placeholder="No. Identitas">
                </td>
                <td class="p-2"><textarea name="pemilik[${pemilikIndex}][alamat]" class="form-control form-control-sm " rows="2" placeholder="Alamat"></textarea></td>
                <td class="p-2 align-middle">
                    <div class="input-group input-group-sm">
                        <input type="text" name="pemilik[${pemilikIndex}][nama]" class="form-control form-control-sm " placeholder="Nama Pemilik">
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
    });
</script>
<script>
$(document).ready(function() {
    $('#nomorIdentitas_2').on('input', function() {
        let valNomor = $(this).val();

        $('#nitku_2').val(valNomor);
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

    function kalkulasiNilaiPabean() {
        let ndpbm = parseFloat($('#ndpbm').val()) || 0;
        let totalCif = parseFloat($('#total_cif').val()) || 0;
        let nilaiPabeanRupiah = ndpbm * totalCif;

        $('#nilai_pabean').val(nilaiPabeanRupiah.toFixed(2));
    }
});
</script>
@endsection
