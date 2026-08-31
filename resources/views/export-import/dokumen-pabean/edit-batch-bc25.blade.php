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

    /* ---------- KARTU PER TAB ---------- */
    .card-tab {
        border: 1px solid var(--border);
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 0;
    }
    .card-tab > .card-header-navy {
        background-color: var(--primary);
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .3px;
        text-transform: uppercase;
        padding: 10px 16px;
        border-bottom: 3px solid var(--accent);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .card-tab > .card-header-navy i { opacity: .9; }
    .card-tab > .card-body { padding: 18px; background: #fff; }
    .card-tab .section-title {
        color: var(--primary);
        border-bottom: 1px solid var(--border);
    }
    .card-tab .section-title:first-child { margin-top: 0; }

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
    // Normalisasi sekali di awal: pastikan variabel yang dipakai di echo bukan array
    $nomorAju    = is_array($nomorAju ?? null) ? '' : ($nomorAju ?? '');
    $dataDetail  = is_array($dataDetail ?? null) ? $dataDetail : [];
    foreach ($dataDetail as $dKey => $dVal) {
        // key yang seharusnya berisi angka/teks, bukan array
        if (is_array($dVal) && !in_array($dKey, ['barang', 'entitas', 'dokumen', 'pengangkut', 'kemasan', 'kontainer', 'pungutan'], true)) {
            $dataDetail[$dKey] = '';
        }
    }
@endphp
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">
            <i class="fas fa-edit"></i> BC 2.5 - PEMBERITAHUAN IMPOR BARANG DARI TEMPAT PENIMBUNAN BERIKAT
        </h5>
    </div>

    <form action="{{ route('dokumen-pabean-update_draft_batch_bc25', $batch_id) }}" method="POST" id="form-edit-ceisa">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="alert alert-warning py-2 mb-4">
                <strong>Mode Batch (BC 3.3)</strong><br>
                <strong>No. Transaksi Gabungan:</strong> {{ $batch_id }} <br>
                {{ $header->supplier ?? '-' }}
                <input type="hidden" name="bpbno_int" value="{{ $header->bpbno_int }}">
                <input type="hidden" name="no_dokumen_merge" value="{{ $batch_id }}">
                <input type="hidden" name="kodeDokumen" value="25">
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
                    <div class="card card-tab shadow-sm">
                        <div class="card-header"><i class="fas fa-info-circle"></i> Header</div>
                        <div class="card-body">
                            <div class="section-title">Data Pengajuan</div>
                            <div class="row">
                                <div class="col-md-3 form-group">
                                    <label>Nomor Aju</label>
                                    <input type="text" name="nomorAju" class="form-control form-control-sm fw-bold" value="{{ $nomorAju ?? '' }}">
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Kode Kantor</label>
                                    <select name="kodeKantor" class="form-control form-control-sm select2bs4 ">
                                            <option value="">Pilih Kantor</option>
                                            @foreach($kantorList as $val => $label)
                                                <option value="{{ $val }}" {{ ($dataDetail['kodeKantor'] ?? '') == $val ? 'selected' : '' }}>{{ $val }} - {{ $label }}</option>
                                            @endforeach
                                        </select>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Jenis TPB</label>
                                    <select name="kodeJenisTpb" class="form-control form-control-sm select2bs4">
                                        <option value="">-- Pilih --</option>
                                        @foreach($listJenisTpb as $k => $v)
                                            <option value="{{ $k }}" {{ ($dataDetail['kodeJenisTpb'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Tujuan Pengiriman</label>
                                    @php
                                        $listTujuan = [
                                            '1' => 'PENYERAHAN BKP', '2' => 'PENYERAHAN JKP', '3' => 'RETUR',
                                            '4' => 'NON PENYERAHAN', '5' => 'LAINNYA'
                                        ];
                                        $tujuanTerpilih = $dataDetail['kodeTujuanPengiriman'] ?? '1';
                                    @endphp
                                    <select name="kodeTujuanPengiriman" class="form-control form-control-sm select2bs4">
                                        <option value="">Pilih Tujuan Pengiriman</option>
                                        @foreach($listTujuan as $key => $text)
                                            <option value="{{ $key }}" {{ $tujuanTerpilih == $key ? 'selected' : '' }}>{{ $key }} - {{ $text }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Cara Pembayaran</label>
                                    @php $caraBayar = $dataDetail['kodeCaraBayar'] ?? ''; @endphp
                                    <select name="kodeCaraBayar" class="form-control form-control-sm select2bs4">
                                        <option value="">-- Pilih --</option>
                                        @foreach($listCaraPembayaran as $k => $v)
                                            <option value="{{ $k }}" {{ ($dataDetail['kodeCaraBayar'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-barang" role="tabpanel">
                    <div class="card card-tab shadow-sm">
                        <div class="card-header"><i class="fas fa-boxes"></i> Data Barang <span class="badge badge-light ml-auto">{{ count($items) }} Item</span></div>
                        <div class="card-body">
                            <div class="section-title"><i class="fas fa-boxes"></i> Rincian Barang ({{ count($items) }} Item)</div>

                            <div class="accordion" id="accordionBarang">
                                @foreach($items as $index => $item)
                                @php
                                    $draftItem = $dataDetail['barang'][$index] ?? [];
                                @endphp

                                <div class="card mb-2 border">
                                    <div class="card-header bg-light py-2 btn-collapse-barang" data-target="#collapseBarang{{ $index }}" style="cursor: pointer;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-bold" style="font-size: 13px;">
                                                {{ $item->goods_code ?? $item->id_item ?? '' }} - {{ $item->itemdesc ?? '' }}
                                            </div>
                                            <i class="fas fa-chevron-down icon-collapse"></i>
                                        </div>
                                    </div>

                                    <div id="collapseBarang{{ $index }}" class="collapse" data-parent="#accordionBarang">
                                        <div class="card-body py-3 px-3 bg-white">

                                            <input type="hidden" name="barang[{{ $index }}][kodeBarang]" value="{{ $draftItem['kodeBarang'] ?? $item->goods_code ?? $item->id_item ?? '' }}">
                                            <input type="hidden" name="barang[{{ $index }}][seriBarang]" value="{{ $index + 1 }}">

                                            <div class="row">

                                                <div class="col-md-3">
                                                    <div class="card shadow-none border mb-3">
                                                        <div class="card-header bg-light p-2" style="font-size: 13px;">
                                                            <h3 class="card-title mb-0" style="font-size: 13px; font-weight: bold;">Dokumen Asal</h3>
                                                        </div>
                                                        <div class="card-body p-2">
                                                            <div class="form-group mb-2">
                                                                <label class="small mb-0">Kode Kantor</label>
                                                                <select name="barang[{{ $index }}][dokumenAsal][kodeKantor]" class="form-control form-control-sm select2bs4">
                                                                    <option value="">-- Pilih Kode Kantor --</option>
                                                                    @foreach($kantorList as $kantor)
                                                                        @php $kKode = is_array($kantor) ? ($kantor['kode'] ?? '') : $kantor; $kNama = is_array($kantor) ? ($kantor['nama'] ?? '') : $kantor; @endphp
                                                                        <option value="{{ $kKode }}" {{ ($draftItem['dokumenAsal']['kodeKantor'] ?? '') == $kKode ? 'selected' : '' }}>{{ $kKode }} - {{ $kNama }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label class="small mb-0">Dokumen Asal</label>
                                                                <select name="barang[{{ $index }}][dokumenAsal][jenisDokumen]" class="form-control form-control-sm select2bs4">
                                                                    <option value="">-- Pilih Dokumen --</option>
                                                                    @foreach($referensiDokumen as $rKode => $rNama)
                                                                        <option value="{{ $rKode }}" {{ ($draftItem['dokumenAsal']['jenisDokumen'] ?? '') == $rKode ? 'selected' : '' }}>{{ $rKode }} - {{ $rNama }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label class="small mb-0">Nomor Pengajuan</label>
                                                                <input type="text" name="barang[{{ $index }}][dokumenAsal][nomorPengajuan]" class="form-control form-control-sm" value="{{ $draftItem['dokumenAsal']['nomorPengajuan'] ?? '' }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label class="small mb-0">Nomor Daftar</label>
                                                                <input type="text" name="barang[{{ $index }}][dokumenAsal][nomorDaftar]" class="form-control form-control-sm" value="{{ $draftItem['dokumenAsal']['nomorDaftar'] ?? '' }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label class="small mb-0">Tanggal Daftar</label>
                                                                <input type="date" name="barang[{{ $index }}][dokumenAsal][tanggalDaftar]" class="form-control form-control-sm" value="{{ $draftItem['dokumenAsal']['tanggalDaftar'] ?? '' }}">
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label class="small mb-0">Seri Barang Asal</label>
                                                                <input type="text" name="barang[{{ $index }}][dokumenAsal][seriBarangAsal]" class="form-control form-control-sm" value="{{ $draftItem['dokumenAsal']['seriBarangAsal'] ?? '' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="col-md-5">
                                                    <div class="card shadow-none border mb-3">
                                                        <div class="card-header bg-light p-2" style="font-size: 13px;">
                                                            <h3 class="card-title mb-0" style="font-size: 13px; font-weight: bold;">Jenis</h3>
                                                        </div>
                                                        <div class="card-body p-2">
                                                            <div class="form-group mb-2">
                                                                <label class="small mb-0">Seri</label>
                                                                <input type="text" class="form-control form-control-sm bg-light" value="{{ $index + 1 }}" readonly>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label class="small mb-0">Pos Tarif/HS <i class="fas fa-info-circle text-primary"></i></label>
                                                                <div class="input-group input-group-sm">
                                                                    <input type="text" name="barang[{{ $index }}][posTarif]" class="form-control form-control-sm" value="{{ $draftItem['posTarif'] ?? '' }}" placeholder="Pos Tarif">
                                                                    <div class="input-group-append">
                                                                        <button class="btn btn-primary" type="button"><i class="fas fa-search"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label class="small mb-0">Kode Barang</label>
                                                                <input type="text" name="barang[{{ $index }}][kodeBarang]" class="form-control form-control-sm" value="{{ $draftItem['kodeBarang'] ?? $item->goods_code ?? $item->id_item ?? '' }}">
                                                                <input type="text" name="barang[{{ $index }}][idItem]" class="form-control form-control-sm hidden" value="{{ $item->id_item ?? '' }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label class="small mb-0 d-flex justify-content-between">Uraian Jenis Barang <span class="badge badge-primary py-1 px-2" style="font-size: 10px;">Sesuai Hs</span></label>
                                                                <textarea name="barang[{{ $index }}][uraian]" class="form-control form-control-sm" rows="2" placeholder="Uraian kosong">{{ $draftItem['uraian'] ?? $item->itemdesc ?? '' }}</textarea>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label class="small mb-0">Merek</label>
                                                                <input type="text" name="barang[{{ $index }}][merk]" class="form-control form-control-sm" value="{{ $draftItem['merk'] ?? '-' }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label class="small mb-0">Tipe</label>
                                                                <input type="text" name="barang[{{ $index }}][tipe]" class="form-control form-control-sm" value="{{ $draftItem['tipe'] ?? '-' }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label class="small mb-0">Ukuran</label>
                                                                <input type="text" name="barang[{{ $index }}][ukuran]" class="form-control form-control-sm" value="{{ $draftItem['ukuran'] ?? '-' }}">
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label class="small mb-0">Spesifikasi Lain</label>
                                                                <input type="text" name="barang[{{ $index }}][spesifikasiLain]" class="form-control form-control-sm" value="{{ $draftItem['spesifikasiLain'] ?? $item->remark ?? null ?? '-' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="col-md-4">
                                                    <div class="card shadow-none border mb-3">
                                                        <div class="card-header bg-light p-2" style="font-size: 13px;">
                                                            <h3 class="card-title mb-0" style="font-size: 13px; font-weight: bold;">Keterangan Lainnya</h3>
                                                        </div>
                                                        <div class="card-body p-2">
                                                            <div class="form-group mb-2">
                                                                <label class="small mb-0">Penggunaan</label>
                                                                <select name="barang[{{ $index }}][kodePenggunaan]" class="form-control form-control-sm select2bs4">
                                                                    <option value="">-- Pilih Penggunaan --</option>
                                                                    <option value="0" {{ ($draftItem['kodePenggunaan'] ?? '') == '0' ? 'selected' : '' }}>0 - BARANG BERHUBUNGAN LANGSUNG</option>
                                                                    <option value="1" {{ ($draftItem['kodePenggunaan'] ?? '') == '1' ? 'selected' : '' }}>1 - TIDAK BERHUBUNGAN LANGSUNG</option>
                                                                    <option value="2" {{ ($draftItem['kodePenggunaan'] ?? '') == '2' ? 'selected' : '' }}>2 - BARANG KONSUMSI</option>
                                                                    <option value="3" {{ ($draftItem['kodePenggunaan'] ?? '') == '3' ? 'selected' : '' }}>3 - BARANG HASIL OLAHAN</option>
                                                                    <option value="4" {{ ($draftItem['kodePenggunaan'] ?? '') == '4' ? 'selected' : '' }}>4 - BARANG LAINNYA</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label class="small mb-0">Kategori Barang</label>
                                                                <select name="barang[{{ $index }}][kodeKategoriBarang]" class="form-control form-control-sm select2bs4">
                                                        <option value="">-- Kategori --</option>
                                                        @foreach(($listKategoriBarang['25'] ?? []) as $k => $v)
                                                            <option value="{{ $k }}" {{ ($draftItem['kodeKategoriBarang'] ?? $item->kodeKategoriBarang ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                        @endforeach
                                                    </select>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label class="small mb-0">Kondisi Barang</label>
                                                                <select name="barang[{{ $index }}][kodeKondisiBarang]" class="form-control form-control-sm select2bs4">
                                                        <option value="">-- Kondisi --</option>
                                                        @foreach($listKondisiBarang as $k => $v)
                                                            <option value="{{ $k }}" {{ ($item->kodeKondisiBarang ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                        @endforeach
                                                    </select>
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label class="small mb-0">Jangka Waktu</label>
                                                                <div class="form-check mt-1">
                                                                    <input class="form-check-input" type="checkbox" name="barang[{{ $index }}][jangkaWaktu]" value="> 4 Tahun" {{ !empty($draftItem['jangkaWaktu']) ? 'checked' : '' }}>
                                                                    <label class="form-check-label small" style="margin-top: 1px;">> 4 Tahun</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="card shadow-none border mb-3">
                                                        <div class="card-header bg-light p-2" style="font-size: 13px;">
                                                            <h3 class="card-title mb-0" style="font-size: 13px; font-weight: bold;">Jumlah & Berat</h3>
                                                        </div>
                                                        <div class="card-body p-2">
                                                            <div class="form-group mb-2">
                                                                <label class="small mb-0">Jumlah dan Satuan Barang</label>
                                                                <div class="row">
                                                                    <div class="col-6 pr-1">
                                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][jumlahSatuan]" class="form-control form-control-sm input-decimal" value="{{ ($draftItem['jumlahSatuan'] ?? (float) $item->qty) ?? '0.0000' }}">
                                                                    </div>
                                                                    <div class="col-6 pl-1">
                                                                        <select name="barang[{{ $index }}][kodeSatuanBarang]" class="form-control form-control-sm select2bs4">
                                                                            <option value="">-- Pilih Satuan --</option>
                                                                            @foreach($listSatuanBarang as $kSat => $vSat)
                                                                                <option value="{{ $kSat }}" {{ ($draftItem['kodeSatuanBarang'] ?? $item->unit) == $kSat ? 'selected' : '' }}>{{ $kSat }} - {{ $vSat }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label class="small mb-0">Kemasan</label>
                                                                <div class="row">
                                                                    <div class="col-4 pr-1">
                                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][jumlahKemasan]" class="form-control form-control-sm input-decimal" value="{{ $draftItem['jumlahKemasan'] ?? 0 }}">
                                                                    </div>
                                                                    <div class="col-8 pl-1">
                                                                        <select name="barang[{{ $index }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4">
                                                                            <option value="">-- Jenis Kemasan --</option>
                                                                            @foreach($listJenisKemasan as $kKem => $vKem)
                                                                                <option value="{{ $kKem }}" {{ ($draftItem['kodeJenisKemasan'] ?? '') == $kKem ? 'selected' : '' }}>{{ $kKem }} - {{ $vKem }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label class="small mb-0">Berat Bersih (Kg)</label>
                                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][netto]" class="form-control form-control-sm input-decimal" value="{{ $draftItem['netto'] ?? '0.0000' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">

                                                <div class="col-md-4">
                                                    <div class="card shadow-none border mb-3">
                                                        <div class="card-header bg-light p-2" style="font-size: 13px;">
                                                            <h3 class="card-title mb-0" style="font-size: 13px; font-weight: bold;">Harga</h3>
                                                        </div>
                                                        <div class="card-body p-2">
                                                            <div class="form-group mb-2">
                                                                <label class="small mb-0">CIF</label>
                                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][cif]" class="form-control form-control-sm input-decimal" value="{{ $draftItem['cif'] ?? '0.00' }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label class="small mb-0">Nilai CIF</label>
                                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][nilaiCif]" class="form-control form-control-sm input-decimal bg-light" value="{{ $draftItem['nilaiCif'] ?? '0.00' }}" readonly>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label class="small mb-0">Nilai Pabean</label>
                                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][nilaiPabean]" class="form-control form-control-sm input-decimal bg-light" value="{{ $draftItem['nilaiPabean'] ?? '0.00' }}" readonly>
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label class="small mb-0">Harga Penyerahan/Harga Jual</label>
                                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][hargaPenyerahan]" class="form-control form-control-sm input-decimal" value="{{ ($draftItem['hargaPenyerahan'] ?? (float) ($item->qty * $item->price)) ?? '0.00' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="col-md-8 hidden">
                                                    <div class="card shadow-none border mb-3">
                                                        <div class="card-header bg-light p-2 d-flex justify-content-between align-items-center" style="font-size: 13px;">
                                                            <h3 class="card-title mb-0" style="font-size: 13px; font-weight: bold;">Dokumen Fasilitas/Lartas</h3>
                                                            <button type="button" class="btn btn-primary btn-sm py-0 px-2" style="font-size: 11px;"><i class="fas fa-plus"></i> Tambah</button>
                                                        </div>
                                                        <div class="card-body p-2 d-flex align-items-center justify-content-center" style="min-height: 120px;">
                                                            <div class="text-center text-muted">
                                                                <i class="fas fa-inbox fa-2x mb-2" style="color: #ddd;"></i>
                                                                <p class="small mb-0" style="color: #ccc;">No Data</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row hidden">

                                                <div class="col-md-12">
                                                    <div class="card shadow-none border mb-3">
                                                        <div class="card-header bg-light p-2 d-flex justify-content-between align-items-center" style="font-size: 13px;">
                                                            <h3 class="card-title mb-0" style="font-size: 13px; font-weight: bold;">Jenis Voluntary Declaration</h3>
                                                            <button type="button" class="btn btn-primary btn-sm py-0 px-2" style="font-size: 11px;"><i class="fas fa-plus"></i> Tambah</button>
                                                        </div>
                                                        <div class="card-body p-2">
                                                            <table class="table table-sm table-borderless mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="small font-weight-bold border-bottom">Jenis Voluntary Declaration</th>
                                                                        <th class="small font-weight-bold border-bottom">Settlement Date</th>
                                                                        <th class="small font-weight-bold border-bottom">Nilai Barang Vd</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td><input type="text" class="form-control form-control-sm bg-light" readonly></td>
                                                                        <td><input type="text" class="form-control form-control-sm bg-light" readonly></td>
                                                                        <td><input type="text" class="form-control form-control-sm bg-light" readonly></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                            <div class="text-center text-muted mt-2 mb-1">
                                                                <i class="fas fa-inbox fa-2x mb-1" style="color: #ddd;"></i>
                                                                <p class="small mb-0" style="color: #ccc;">No Data</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">

                                                <div class="col-md-12">
                                                    <div class="card shadow-none border mb-3">
                                                        <div class="card-header bg-light p-2" style="font-size: 13px;">
                                                            <h3 class="card-title mb-0" style="font-size: 13px; font-weight: bold;">Pungutan</h3>
                                                        </div>
                                                        <div class="card-body p-2 bg-light">
                                                            <div class="row">

                                                                <div class="col-md-6">
                                                                    <div class="card shadow-none border mb-2">
                                                                        <div class="card-body p-2">
                                                                            <div class="row mb-1">
                                                                                <div class="col-4 pr-1">
                                                                                    <select name="barang[{{ $index }}][pungutan][bm][kodeJenis]" class="form-control form-control-sm">
                                                                                        <option value="BM" {{ ($draftItem['pungutan']['bm']['kodeJenis'] ?? 'BM') == 'BM' ? 'selected' : '' }}>BM</option>
                                                                                        <option value="BMKITE" {{ ($draftItem['pungutan']['bm']['kodeJenis'] ?? '') == 'BMKITE' ? 'selected' : '' }}>BMKITE</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-4 px-1">
                                                                                    <select name="barang[{{ $index }}][pungutan][bm][kodeJenisTarif]" class="form-control form-control-sm select2bs4">
                                                                                        <option value="">-- Jenis Tarif --</option>
                                                                                        @foreach($listJenisTarif as $k => $v)
                                                                                            <option value="{{ $k }}" {{ ($pungutanBarang['bm']['kodeJenisTarif'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-4 pl-1" id="bmTarifAdval{{ $index }}" style="{{ ($draftItem['pungutan']['bm']['kodeJenisTarif'] ?? '1') == '1' ? '' : 'display:none;' }}">
                                                                                    <div class="input-group input-group-sm">
                                                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][bm][tarif]" class="form-control form-control-sm" placeholder="Tarif" value="{{ $draftItem['pungutan']['bm']['tarif'] ?? '0' }}">
                                                                                        <div class="input-group-append"><span class="input-group-text bg-white px-1">%</span></div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="row mb-1" id="bmSpesifikRow{{ $index }}" style="{{ ($draftItem['pungutan']['bm']['kodeJenisTarif'] ?? '1') == '2' ? '' : 'display:none;' }}">
                                                                                <div class="col-4 pr-1">
                                                                                    <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][bm][jumlahSatuan]" class="form-control form-control-sm" placeholder="Jml Satuan" value="{{ $draftItem['pungutan']['bm']['jumlahSatuan'] ?? '' }}">
                                                                                </div>
                                                                                <div class="col-4 px-1">
                                                                                    <select name="barang[{{ $index }}][pungutan][bm][kodeSatuanBarang]" class="form-control form-control-sm">
                                                                                        <option value="">-- Pilih Satuan --</option>
                                                                                        @foreach($listSatuanBarang as $kSat => $vSat)
                                                                                            <option value="{{ $kSat }}" {{ ($draftItem['kodeSatuanBarang'] ?? $item->unit) == $kSat ? 'selected' : '' }}>{{ $kSat }} - {{ $vSat }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-4 pl-1">
                                                                                    <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][bm][tarifSpesifik]" class="form-control form-control-sm" placeholder="Tarif" value="{{ $draftItem['pungutan']['bm']['tarifSpesifik'] ?? '' }}">
                                                                                </div>
                                                                            </div>

                                                                            <div class="row">
                                                                                <div class="col-8 pr-1">
                                                                                    <select name="barang[{{ $index }}][pungutan][bm][kodeFasilitas]" class="form-control form-control-sm select2bs4">
                                                                                        <option value="">-- Fasilitas --</option>
                                                                                        @foreach($listFasilitasTarif as $k => $v)
                                                                                            <option value="{{ $k }}" {{ ($pungutanBarang['bm']['kodeFasilitas'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-4 pl-1">
                                                                                    <div class="input-group input-group-sm">
                                                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][bm][tarifFasilitas]" class="form-control form-control-sm" placeholder="Fas %" value="{{ $draftItem['pungutan']['bm']['tarifFasilitas'] ?? '0' }}">
                                                                                        <div class="input-group-append"><span class="input-group-text bg-white px-1">%</span></div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>


                                                                    <div class="card shadow-none border mb-2">
                                                                        <div class="card-body p-2">
                                                                            <div class="form-check mb-2">
                                                                                <input class="form-check-input bmt-toggle-{{ $index }}" type="checkbox" id="checkBmt{{ $index }}" name="barang[{{ $index }}][pungutan][bmt][aktif]" value="1" {{ !empty($draftItem['pungutan']['bmt']['aktif']) ? 'checked' : '' }} onchange="toggleBmt{{ $index }}(this)">
                                                                                <label class="form-check-label small fw-bold" for="checkBmt{{ $index }}" style="margin-top: 1px;">BMT (Bea Masuk Tambahan)</label>
                                                                            </div>
                                                                            <div id="bmtPanel{{ $index }}" style="{{ !empty($draftItem['pungutan']['bmt']['aktif']) ? '' : 'display:none;' }}">
                                                                                @php $bmtTypes = ['BMAD', 'BMTP', 'BMI', 'BMP']; @endphp
                                                                                @foreach($bmtTypes as $bIdx => $bmtType)
                                                                                @php
                                                                                    $bmtData = $draftItem['pungutan']['bmt'][$bmtType] ?? [];
                                                                                @endphp
                                                                                <div class="row align-items-center mb-1 border-top pt-1">
                                                                                    <div class="col-2 small fw-bold" style="font-size:11px;">{{ $bmtType }}<br><span class="text-muted" style="font-size:10px;">Sementara</span></div>
                                                                                    <div class="col-3">
                                                                                        <select name="barang[{{ $index }}][pungutan][bmt][{{ $bmtType }}][kodeJenisTarif]" class="form-control form-control-sm select2bs4">
                                                                                            <option value="">-- Jenis Tarif --</option>
                                                                                            @foreach($listJenisTarif as $k => $v)
                                                                                                <option value="{{ $k }}" {{ ($bmtVal['kodeJenisTarif'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                                            @endforeach
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="col-2">
                                                                                        <div class="input-group input-group-sm">
                                                                                            <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][bmt][{{ $bmtType }}][tarif]" class="form-control form-control-sm" value="{{ $bmtData['tarif'] ?? '0' }}" style="font-size:11px;">
                                                                                            <div class="input-group-append"><span class="input-group-text px-1" style="font-size:10px;">%</span></div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-3">
                                                                                        <select name="barang[{{ $index }}][pungutan][bmt][{{ $bmtType }}][kodeFasilitas]" class="form-control form-control-sm select2bs4">
                                                                                            <option value="">-- Fasilitas --</option>
                                                                                            @foreach($listFasilitasTarif as $k => $v)
                                                                                                <option value="{{ $k }}" {{ ($bmtVal['kodeFasilitas'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                                            @endforeach
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="col-2">
                                                                                        <div class="input-group input-group-sm">
                                                                                            <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][bmt][{{ $bmtType }}][tarifFasilitas]" class="form-control form-control-sm" value="{{ $bmtData['tarifFasilitas'] ?? '0' }}" style="font-size:11px;">
                                                                                            <div class="input-group-append"><span class="input-group-text px-1" style="font-size:10px;">%</span></div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                @endforeach
                                                                            </div>
                                                                        </div>
                                                                    </div>


                                                                    <div class="card shadow-none border mb-2">
                                                                        <div class="card-body p-2">
                                                                            <div class="form-check mb-2">
                                                                                <input class="form-check-input" type="checkbox" id="checkCukai{{ $index }}" name="barang[{{ $index }}][pungutan][cukai][aktif]" value="1" {{ !empty($draftItem['pungutan']['cukai']['aktif']) ? 'checked' : '' }} onchange="toggleCukai{{ $index }}(this)">
                                                                                <label class="form-check-label small fw-bold" for="checkCukai{{ $index }}" style="margin-top: 1px;">Cukai</label>
                                                                            </div>
                                                                            <div id="cukaiPanel{{ $index }}" style="{{ !empty($draftItem['pungutan']['cukai']['aktif']) ? '' : 'display:none;' }}">
                                                                                @php $cukaiData = $draftItem['pungutan']['cukai'] ?? []; @endphp
                                                                                <div class="row">
                                                                                    <div class="col-6">
                                                                                        <div class="form-group mb-2">
                                                                                            <label class="small mb-0" style="font-size:11px;">Komoditi</label>
                                                                                            <div class="input-group input-group-sm">
                                                                                                <select name="barang[{{ $index }}][pungutan][cukai][kodeKomoditi]" class="form-control form-control-sm select2bs4">
                                                                                                    <option value="">-- Komoditi --</option>
                                                                                                    @foreach($listKomoditiCukai as $k => $v)
                                                                                                        <option value="{{ $k }}" {{ ($pungutanBarang['cukai']['kodeKomoditi'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                                                    @endforeach
                                                                                                </select>
                                                                                                <select name="barang[{{ $index }}][pungutan][cukai][kodeGolongan]" class="form-control form-control-sm" style="font-size:11px;">
                                                                                                    <option value="">Gol</option>
                                                                                                    <option value="A" {{ ($cukaiData['kodeGolongan'] ?? '') == 'A' ? 'selected' : '' }}>A</option>
                                                                                                    <option value="B" {{ ($cukaiData['kodeGolongan'] ?? '') == 'B' ? 'selected' : '' }}>B</option>
                                                                                                    <option value="C" {{ ($cukaiData['kodeGolongan'] ?? '') == 'C' ? 'selected' : '' }}>C</option>
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="form-group mb-2">
                                                                                            <label class="small mb-0" style="font-size:11px;">Jenis Tarif</label>
                                                                                            <select name="barang[{{ $index }}][pungutan][cukai][kodeJenisTarif]" class="form-control form-control-sm select2bs4">
                                                                                                <option value="">-- Jenis Tarif --</option>
                                                                                                @foreach($listJenisTarif as $k => $v)
                                                                                                    <option value="{{ $k }}" {{ ($pungutanBarang['cukai']['kodeJenisTarif'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                                                @endforeach
                                                                                            </select>
                                                                                        </div>
                                                                                        <div class="form-group mb-2">
                                                                                            <label class="small mb-0" style="font-size:11px;">Besar Tarif</label>
                                                                                            <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][cukai][besarTarif]" class="form-control form-control-sm" value="{{ $cukaiData['besarTarif'] ?? '0.00' }}" style="font-size:11px;">
                                                                                        </div>
                                                                                        <div class="form-group mb-2">
                                                                                            <label class="small mb-0" style="font-size:11px;">Jumlah</label>
                                                                                            <div class="input-group input-group-sm">
                                                                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][cukai][jumlahSatuan]" class="form-control form-control-sm" value="{{ $cukaiData['jumlahSatuan'] ?? '0.0000' }}" style="font-size:11px;" placeholder="Jml Satuan">
                                                                                                <select name="barang[{{ $index }}][pungutan][cukai][kodeSatuanCukai]" class="form-control form-control-sm" style="font-size:11px;">
                                                                                                    <option value="">-- Pilih Satuan --</option>
                                                                                                    @foreach($listSatuanBarang as $kSat => $vSat)
                                                                                                        <option value="{{ $kSat }}" {{ ($draftItem['kodeSatuanBarang'] ?? $item->unit) == $kSat ? 'selected' : '' }}>{{ $kSat }} - {{ $vSat }}</option>
                                                                                                    @endforeach
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="form-group mb-0">
                                                                                            <label class="small mb-0" style="font-size:11px;">Nilai Cukai</label>
                                                                                            <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][cukai][nilaiCukai]" class="form-control form-control-sm bg-light" value="{{ $cukaiData['nilaiCukai'] ?? '0.00' }}" style="font-size:11px;" readonly>
                                                                                        </div>
                                                                                        <div class="form-group mb-0 mt-2">
                                                                                            <label class="small mb-0" style="font-size:11px;">Jenis Tarif (2)</label>
                                                                                            <select name="barang[{{ $index }}][pungutan][cukai][kodeJenisTarif2]" class="form-control form-control-sm select2bs4">
                                                                                                <option value="">-- Jenis Tarif --</option>
                                                                                                @foreach($listJenisTarif as $k => $v)
                                                                                                    <option value="{{ $k }}" {{ ($pungutanBarang['cukai']['kodeJenisTarif2'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                                                @endforeach
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-6">
                                                                                        <div class="form-group mb-2">
                                                                                            <label class="small mb-0" style="font-size:11px;">HJE RP</label>
                                                                                            <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][cukai][hjeRp]" class="form-control form-control-sm" value="{{ $cukaiData['hjeRp'] ?? '0.00' }}" style="font-size:11px;">
                                                                                        </div>
                                                                                        <div class="form-group mb-2">
                                                                                            <label class="small mb-0" style="font-size:11px;">Satuan Kemasan</label>
                                                                                            <div class="input-group input-group-sm">
                                                                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][cukai][jumlahKemasan]" class="form-control form-control-sm" value="{{ $cukaiData['jumlahKemasan'] ?? 0 }}" style="font-size:11px;" placeholder="Jml">
                                                                                                <select name="barang[{{ $index }}][pungutan][cukai][kodeJenisKemasan]" class="form-control form-control-sm" style="font-size:11px;">
                                                                                                    <option value="">-- Pilih Kemasan --</option>
                                                                                                    @foreach($listJenisKemasan as $kKem => $vKem)
                                                                                                        <option value="{{ $kKem }}" {{ ($cukaiData['kodeJenisKemasan'] ?? '') == $kKem ? 'selected' : '' }}>{{ $kKem }} - {{ $vKem }}</option>
                                                                                                    @endforeach
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="form-group mb-2">
                                                                                            <label class="small mb-0" style="font-size:11px;">Isi Per Kemasan</label>
                                                                                            <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][cukai][isiPerKemasan]" class="form-control form-control-sm" value="{{ $cukaiData['isiPerKemasan'] ?? '0.00' }}" style="font-size:11px;">
                                                                                        </div>
                                                                                        <div class="form-group mb-2">
                                                                                            <label class="small mb-0" style="font-size:11px;">Jumlah Pita Cukai</label>
                                                                                            <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][cukai][jumlahPitaCukai]" class="form-control form-control-sm" value="{{ $cukaiData['jumlahPitaCukai'] ?? '0.00' }}" style="font-size:11px;">
                                                                                        </div>
                                                                                        <div class="form-group mb-2">
                                                                                            <label class="small mb-0" style="font-size:11px;">Saldo Awal</label>
                                                                                            <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][cukai][saldoAwal]" class="form-control form-control-sm" value="{{ $cukaiData['saldoAwal'] ?? '0.00' }}" style="font-size:11px;">
                                                                                        </div>
                                                                                        <div class="form-group mb-0">
                                                                                            <label class="small mb-0" style="font-size:11px;">Saldo Akhir</label>
                                                                                            <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][cukai][saldoAkhir]" class="form-control form-control-sm bg-light" value="{{ $cukaiData['saldoAkhir'] ?? '0.00' }}" style="font-size:11px;" readonly>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>


                                                                <div class="col-md-6">
                                                                    <div class="card shadow-none border mb-0 h-100">
                                                                        <div class="card-body p-2 pt-3">
                                                                            <label class="small fw-bold mb-2">PDRI</label>

                                                                            <div class="row mb-2 align-items-center">
                                                                                <div class="col-3 small">PPN</div>
                                                                                <div class="col-5 pr-1">
                                                                                    <div class="input-group input-group-sm">
                                                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][ppn][tarif]" class="form-control form-control-sm" value="{{ $draftItem['pungutan']['ppn']['tarif'] ?? '11' }}">
                                                                                        <div class="input-group-append"><span class="input-group-text bg-white px-1">%</span></div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-4 pl-1">
                                                                                    <div class="input-group input-group-sm">
                                                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][ppn][tarifFasilitas]" class="form-control form-control-sm" value="{{ $draftItem['pungutan']['ppn']['tarifFasilitas'] ?? '0' }}">
                                                                                        <div class="input-group-append"><span class="input-group-text bg-white px-1">%</span></div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="row mb-2 align-items-center">
                                                                                <div class="col-3 small">PPNBM</div>
                                                                                <div class="col-5 pr-1">
                                                                                    <div class="input-group input-group-sm">
                                                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][ppnbm][tarif]" class="form-control form-control-sm" value="{{ $draftItem['pungutan']['ppnbm']['tarif'] ?? '0' }}">
                                                                                        <div class="input-group-append"><span class="input-group-text bg-white px-1">%</span></div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-4 pl-1">
                                                                                    <div class="input-group input-group-sm">
                                                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][ppnbm][tarifFasilitas]" class="form-control form-control-sm" value="{{ $draftItem['pungutan']['ppnbm']['tarifFasilitas'] ?? '0' }}">
                                                                                        <div class="input-group-append"><span class="input-group-text bg-white px-1">%</span></div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="row mb-1 align-items-center">
                                                                                <div class="col-3 small">PPH</div>
                                                                                <div class="col-3 pr-1">
                                                                                    <div class="input-group input-group-sm">
                                                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][pph][tarif]" class="form-control form-control-sm" value="{{ $draftItem['pungutan']['pph']['tarif'] ?? '2.5' }}">
                                                                                        <div class="input-group-append"><span class="input-group-text bg-white px-1">%</span></div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-3 px-1">
                                                                                    <select name="barang[{{ $index }}][pungutan][pph][caraBayar]" class="form-control form-control-sm select2bs4">
                                                                                        <option value="">-- Cara Bayar --</option>
                                                                                        @foreach($listCaraPembayaran as $k => $v)
                                                                                            <option value="{{ $k }}" {{ ($pungutanBarang['pph']['caraBayar'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-3 pl-1">
                                                                                    <div class="input-group input-group-sm">
                                                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][pph][tarifFasilitas]" class="form-control form-control-sm" value="{{ $draftItem['pungutan']['pph']['tarifFasilitas'] ?? '100' }}">
                                                                                        <div class="input-group-append"><span class="input-group-text bg-white px-1">%</span></div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
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

                <div class="tab-pane fade" id="tab-entitas" role="tabpanel">
                    <div class="card card-tab shadow-sm">
                        <div class="card-header"><i class="fas fa-users"></i> Entitas</div>
                        <div class="card-body">
                            <div class="section-title mt-0"><i class="fas fa-building"></i> Entitas Pengusaha TPB (Kode: 3)</div>
                            <div class="row">
                                <div class="col-md-4 form-group"><label>Nama Entitas</label><input type="text" name="entitas[3][namaEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['namaEntitas'] ?? 'NIRWANA ALABARE GARMENT' }}"></div>
                                <div class="col-md-4 form-group"><label>NPWP</label><input type="text" name="entitas[3][nomorIdentitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['nomorIdentitas'] ?? '0745406926444000000000' }}"></div>
                                <div class="col-md-4 form-group"><label>NIB</label><input type="text" name="entitas[3][nibEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['nibEntitas'] ?? '0220103231143' }}"></div>
                                <div class="col-md-8 form-group"><label>Alamat</label><input type="text" name="entitas[3][alamatEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['alamatEntitas'] ?? 'JL. RAYA RANCAEKEK MAJALAYA NO. 289 RT. 001 RW. 007' }}"></div>
                                <div class="col-md-2 form-group"><label>No. Izin TPB</label><input type="text" name="entitas[3][nomorIjinEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['nomorIjinEntitas'] ?? '16/MK/WBC.09/2026' }}"></div>
                                <div class="col-md-2 form-group"><label>&nbsp;</label><input type="date" name="entitas[3][tanggalIjinEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['tanggalIjinEntitas'] ?? '2026-01-20' }}"></div>
                            </div>

                            <div class="section-title"><i class="fas fa-truck-loading"></i> Entitas Pembeli / Penerima (Kode: 8)</div>
                            <div class="row">
                                <div class="col-md-4 form-group"><label>Nama Entitas</label><input type="text" name="entitas[8][namaEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][8]['namaEntitas'] ?? $header->supplier ?? '' }}"></div>
                                <div class="col-md-4 form-group"><label>NPWP</label><input type="text" name="entitas[8][nomorIdentitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][8]['nomorIdentitas'] ?? $header->npwp_supplier ?? '' }}"></div>
                                <div class="col-md-4 form-group"><label>Alamat</label><input type="text" name="entitas[8][alamatEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][8]['alamatEntitas'] ?? $header->alamat_supplier ?? '' }}"></div>
                            </div>

                            <div class="section-title"><i class="fas fa-user-tag"></i> Entitas Pemilik Barang (Kode: 7)</div>
                            <div class="row">
                                <div class="col-md-4 form-group"><label>Nama Entitas</label><input type="text" name="entitas[7][namaEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][7]['namaEntitas'] ?? '' }}"></div>
                                <div class="col-md-4 form-group"><label>NPWP</label><input type="text" name="entitas[7][nomorIdentitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][7]['nomorIdentitas'] ?? '' }}"></div>
                                <div class="col-md-4 form-group"><label>Alamat</label><input type="text" name="entitas[7][alamatEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][7]['alamatEntitas'] ?? '' }}"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-dokumen" role="tabpanel">
                    <div class="card card-tab shadow-sm">
                        <div class="card-header"><i class="fas fa-file-alt"></i> Dokumen Pelengkap</div>
                        <div class="card-body">

                            <div class="section-title mt-0">Dokumen Pendukung</div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    @php



                                    @endphp
                                    <table class="table table-sm table-bordered" id="table-dokumen">
                                        <thead class="bg-light text-center">
                                            <tr>
                                                <th width="40%">Kode Dokumen</th>
                                                <th width="30%">Nomor Dokumen</th>
                                                <th width="15%">Tgl Dokumen</th>
                                                <th width="10%"><button type="button" class="btn btn-sm btn-primary py-0 px-2" id="btn-add-dok" title="Tambah Dokumen"><i class="fas fa-plus"></i></button></th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-dokumen">
                                            @foreach($dokumens as $index => $dok)
                                            @php
                                                $dok = is_array($dok) ? $dok : [];
                                                $dokKodeTerpilih = $dok['kodeDokumen'] ?? '';
                                            @endphp
                                            <tr>
                                                <td>
                                                    <select name="dokumen[{{ $index }}][kodeDokumen]" class="form-control form-control-sm select2bs4">
                                                        <option value="">-- Pilih Kode --</option>
                                                        @foreach($referensiDokumen as $val => $text)
                                                            <option value="{{ $val }}" {{ $dokKodeTerpilih == $val ? 'selected' : '' }}>{{ $val }} - {{ $text }}</option>
                                                        @endforeach
                                                        @if($dokKodeTerpilih !== '' && !array_key_exists($dokKodeTerpilih, $referensiDokumen))
                                                            <option value="{{ $dokKodeTerpilih }}" selected>{{ $dokKodeTerpilih }} - Custom</option>
                                                        @endif
                                                    </select>
                                                </td>
                                                <td><input type="text" name="dokumen[{{ $index }}][nomorDokumen]" class="form-control form-control-sm" value="{{ $dok['nomorDokumen'] ?? '' }}"></td>
                                                <td><input type="date" name="dokumen[{{ $index }}][tanggalDokumen]" class="form-control form-control-sm" value="{{ $dok['tanggalDokumen'] ?? '' }}"></td>
                                                <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-dok" title="Hapus Baris"><i class="fas fa-trash-alt"></i></button></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-pengangkut" role="tabpanel">
                    <div class="card card-tab shadow-sm">
                        <div class="card-header"><i class="fas fa-truck"></i> Pengangkutan</div>
                        <div class="card-body">
                            <div class="section-title mt-0">Pengangkut</div>
                            @php
                                $pengangkut0 = $dataDetail['pengangkut'][0] ?? [];
                                $pengangkut0 = is_array($pengangkut0) ? $pengangkut0 : [];
                                $pengangkutFlat = $dataDetail['pengangkut'] ?? [];
                                $pengangkutFlat = is_array($pengangkutFlat) ? $pengangkutFlat : [];

                                $caraAngkut = $pengangkut0['kodeCaraAngkut'] ?? '3';
                                $namaPengangkutVal = $pengangkut0['namaPengangkut'] ?? $pengangkutFlat['nama'] ?? null ?? 'TRUK';
                                $nomorPengangkutVal = $pengangkut0['nomorPengangkut'] ?? $pengangkutFlat['nomor'] ?? $header->nomor_mobil ?? '';
                            @endphp
                            <div class="row mb-3">
                                <div class="col-md-3 form-group">
                                    <label>Cara Angkut</label>
                                    <select name="pengangkut[0][kodeCaraAngkut]" class="form-control form-control-sm select2bs4">
                                                        <option value="">Pilih Cara Angkut</option>
                                                        @foreach($listCaraAngkut as $k => $v)
                                                            <option value="{{ $k }}" {{ ($dataDetail['pengangkut'][0]['kodeCaraAngkut'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                        @endforeach
                                                    </select>
                                </div>
                                <div class="col-md-5 form-group hidden">
                                    <label>Keterangan Sarana Angkut Lainnya (Nama)</label>
                                    <input type="text" name="pengangkut[0][namaPengangkut]" class="form-control form-control-sm" value="{{ $namaPengangkutVal }}">
                                </div>
                                <div class="col-md-4 form-group hidden">
                                    <label>Nomor Polisi</label>
                                    <input type="text" name="pengangkut[0][nomorPengangkut]" class="form-control form-control-sm" value="{{ $nomorPengangkutVal }}">
                                    <input type="hidden" name="pengangkut[0][seriPengangkut]" value="1">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-pungutan" role="tabpanel">
                    <div class="card card-tab shadow-sm">
                        <div class="card-header"><i class="fas fa-receipt"></i> Pungutan</div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3 bg-light p-2 rounded">
                                <div class="font-weight-bold" style="font-size: 14px; color: #003366;">Pungutan</div>
                                <button type="button" class="btn btn-sm btn-outline-primary bg-white hidden"><i class="fas fa-sync-alt"></i> Generate Pungutan</button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="align-middle">Pungutan</th>
                                            <th class="align-middle">Ditangguhkan</th>
                                            <th class="align-middle">Sudah Dilunasi</th>
                                            <th class="align-middle">Dibebaskan</th>
                                            <th class="align-middle">Tidak Dipungut</th>
                                        </tr>

                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-left font-weight-bold" style="color: #666; font-size: 12px; padding-left: 15px;">PPN</td>
                                            <td class="font-weight-bold" style="font-size: 12px; color: #0000FF;" id="text-ppn-ditangguhkan">Rp 0,00</td>
                                            <td class="font-weight-bold" style="font-size: 12px; color: #0000FF;" id="text-ppn-sudah-dilunasi">Rp 0,00</td>
                                            <td class="font-weight-bold" style="font-size: 12px; color: #0000FF;" id="text-ppn-dibebaskan">Rp 0,00</td>
                                            <td class="font-weight-bold" style="font-size: 12px; color: #0000FF;" id="text-ppn-tidak-dipungut">Rp 0,00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>


                            <div id="hidden-pungutan-container">

                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-transaksi" role="tabpanel">
                    <div class="card card-tab shadow-sm">
                        <div class="card-header"><i class="fas fa-money-bill-wave"></i> Transaksi & Keuangan</div>
                        <div class="card-body">
                            <div class="section-title mt-0">Data Nilai & Fisik</div>
                            <div class="row">
                                <div class="col-md-2 form-group"><label>Bruto (Kg)</label><input type="text" inputmode="decimal" name="bruto" class="form-control form-control-sm input-decimal" value="{{ $dataDetail['bruto'] ?? $header->berat_kotor ?? '' }}" placeholder="contoh: 125.5000"></div>
                                <div class="col-md-2 form-group"><label>Netto (Kg)</label><input type="text" id="totalNetto" inputmode="decimal" name="netto" class="form-control form-control-sm input-decimal bg-light" value="{{ $dataDetail['netto'] ?? $header->berat_bersih ?? '' }}" readonly></div>
                                <div class="col-md-2 form-group"><label>Volume (M3)</label><input type="text" id="totalVolume" inputmode="decimal" name="volume" class="form-control form-control-sm input-decimal bg-light" value="{{ $dataDetail['volume'] ?? '' }}" readonly></div>
                                <div class="col-md-3 form-group"><label>Harga Penyerahan (Rp)</label><input type="text" id="totalHargaPenyerahan" inputmode="decimal" name="hargaPenyerahan" class="form-control form-control-sm input-decimal bg-light" value="{{ $dataDetail['hargaPenyerahan'] ?? '' }}" readonly></div>
                                <div class="col-md-3 form-group"><label>CIF (Rp)</label><input type="text" inputmode="decimal" name="cif" class="form-control form-control-sm input-decimal" value="{{ $dataDetail['cif'] ?? '' }}" placeholder="contoh: 5000000.00"></div>
                            </div>

                            <div class="row">
                                <div class="col-md-2 form-group"><label>Biaya Pengurang (Rp)</label><input type="text" inputmode="decimal" name="biayaPengurang" class="form-control form-control-sm input-decimal" value="{{ $dataDetail['biayaPengurang'] ?? '' }}" placeholder="contoh: 0.00"></div>
                                <div class="col-md-2 form-group"><label>Uang Muka (Rp)</label><input type="text" inputmode="decimal" name="uangMuka" class="form-control form-control-sm input-decimal" value="{{ $dataDetail['uangMuka'] ?? '' }}" placeholder="contoh: 0.00"></div>
                                <div class="col-md-2 form-group"><label>Nilai Jasa (Rp)</label><input type="text" inputmode="decimal" name="nilaiJasa" class="form-control form-control-sm input-decimal" value="{{ $dataDetail['nilaiJasa'] ?? '' }}" placeholder="contoh: 0.00"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-pernyataan" role="tabpanel">
                    <div class="card card-tab shadow-sm">
                        <div class="card-header"><i class="fas fa-signature"></i> Pernyataan</div>
                        <div class="card-body">
                            <div class="section-title mt-0">Penandatangan</div>
                            <div class="row">
                                <div class="col-md-3 form-group"><label>Nama TTD</label><input type="text" name="namaTtd" class="form-control form-control-sm" value="{{ $dataDetail['namaTtd'] ?? '' }}"></div>
                                <div class="col-md-3 form-group"><label>Jabatan</label><input type="text" name="jabatanTtd" class="form-control form-control-sm" value="{{ $dataDetail['jabatanTtd'] ?? '' }}"></div>
                                <div class="col-md-3 form-group"><label>Kota TTD</label><input type="text" name="kotaTtd" class="form-control form-control-sm" value="{{ $dataDetail['kotaTtd'] ?? '' }}"></div>
                                <div class="col-md-3 form-group"><label>Tanggal TTD</label><input type="date" name="tanggalTtd" class="form-control form-control-sm" value="{{ $dataDetail['tanggalTtd'] ?? date('Y-m-d') }}"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-kemasan" role="tabpanel">
                    <div class="card card-tab shadow-sm">
                        <div class="card-header"><i class="fas fa-box"></i> Kemasan & Peti Kemas</div>
                        <div class="card-body">
                            <div class="section-title mt-0">Data Kemasan</div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    @php
                                        $kemasans = $dataDetail['kemasan'] ?? [];
                                        $kemasans = is_array($kemasans) ? $kemasans : [];
                                        if (empty($kemasans)) {
                                            $kemasans[] = ['jumlahKemasan' => $header->qty_karton ?? '', 'kodeJenisKemasan' => 'CT', 'merkKemasan' => '-'];
                                        }
                                    @endphp
                                    <table class="table table-sm table-bordered" id="table-kemasan">
                                        <thead class="bg-light text-center">
                                            <tr>
                                                <th width="20%">Jumlah Kemasan</th>
                                                <th width="40%">Jenis Kemasan</th>
                                                <th width="30%">Merek</th>
                                                <th width="10%">
                                                    <button type="button" class="btn btn-sm btn-primary py-0 px-2" id="btn-add-kemasan" title="Tambah Kemasan"><i class="fas fa-plus"></i></button>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-kemasan">
                                            @foreach($kemasans as $index => $kemasan)
                                            @php
                                                $kemasan = is_array($kemasan) ? $kemasan : [];
                                                $kemasanKodeTerpilih = $kemasan['kodeJenisKemasan'] ?? $kemasan['kode'] ?? null ?? '';
                                            @endphp
                                            <tr>
                                                <td><input type="text" inputmode="decimal" name="kemasan[{{ $index }}][jumlahKemasan]" class="form-control form-control-sm input-decimal" value="{{ $kemasan['jumlahKemasan'] ?? $kemasan['jumlah'] ?? null ?? '' }}" placeholder="contoh: 10"></td>
                                                <td>
                                                    <select name="kemasan[{{ $index }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4">
                                                        <option value="">-- Pilih --</option>
                                                        @foreach($listJenisKemasan as $k => $v)
                                                            <option value="{{ $k }}" {{ $kemasanKodeTerpilih == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td><input type="text" name="kemasan[{{ $index }}][merkKemasan]" class="form-control form-control-sm" value="{{ $kemasan['merkKemasan'] ?? $kemasan['merk'] ?? null ?? '-' }}"></td>
                                                <td class="text-center align-middle">
                                                    <button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kemasan" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="section-title">Data Kontainer / Peti Kemas</div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    @php
                                        $kontainers = $dataDetail['kontainer'] ?? [];
                                        $kontainers = is_array($kontainers) ? $kontainers : [];
                                        $listJenisKontainer = ['4' => 'Empty', '7' => 'LCL', '8' => 'FCL'];
                                        $listTipeKontainer = [
                                            '1' => 'General/Dry Cargo', '2' => 'Tunnel Type', '3' => 'Open Top Steel',
                                            '4' => 'Flat Rack', '5' => 'Reefer/Refrigerated', '6' => 'Barge Container',
                                            '7' => 'Bulk Container', '8' => 'Isotank', '99' => 'Lain-lain'
                                        ];
                                        $listUkuranKontainer = ['20' => '20 Feet', '40' => '40 Feet', '45' => '45 Feet', '60' => '60 Feet'];
                                    @endphp
                                    <table class="table table-sm table-bordered" id="table-kontainer">
                                        <thead class="bg-light text-center">
                                            <tr>
                                                <th width="30%">Nomor Kontainer</th>
                                                <th width="20%">Jenis</th>
                                                <th width="25%">Tipe</th>
                                                <th width="15%">Ukuran</th>
                                                <th width="10%">
                                                    <button type="button" class="btn btn-sm btn-primary py-0 px-2" id="btn-add-kontainer" title="Tambah Kontainer"><i class="fas fa-plus"></i></button>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-kontainer">
                                            @foreach($kontainers as $kIndex => $kont)
                                            @php
                                                $kont = is_array($kont) ? $kont : [];
                                                $kontJenisTerpilih = $kont['kodeJenisKontainer'] ?? '';
                                                $kontTipeTerpilih = $kont['kodeTipeKontainer'] ?? '';
                                                $kontUkuranTerpilih = $kont['kodeUkuranKontainer'] ?? '';
                                            @endphp
                                            <tr>
                                                <td><input type="text" name="kontainer[{{ $kIndex }}][nomorKontainer]" class="form-control form-control-sm text-uppercase" value="{{ $kont['nomorKontainer'] ?? '' }}" placeholder="Contoh: TGHU1234567"></td>
                                                <td>
                                                    <select name="kontainer[{{ $kIndex }}][kodeJenisKontainer]" class="form-control form-control-sm select2bs4">
                                                        <option value="">-- Pilih --</option>
                                                        @foreach($listJenisKontainer as $k => $v)
                                                            <option value="{{ $k }}" {{ $kontJenisTerpilih == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <select name="kontainer[{{ $kIndex }}][kodeTipeKontainer]" class="form-control form-control-sm select2bs4">
                                                        <option value="">-- Pilih --</option>
                                                        @foreach($listTipeKontainer as $k => $v)
                                                            <option value="{{ $k }}" {{ $kontTipeTerpilih == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <select name="kontainer[{{ $kIndex }}][kodeUkuranKontainer]" class="form-control form-control-sm select2bs4">
                                                        <option value="">-- Pilih --</option>
                                                        @foreach($listUkuranKontainer as $k => $v)
                                                            <option value="{{ $k }}" {{ $kontUkuranTerpilih == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kontainer" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                                                </td>
                                            </tr>
                                            @endforeach
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

        <div class="card-footer text-right bg-white border-top">
            <a href="{{ route('dokumen-pabean-index') }}" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-save"></i> Simpan</button>
        </div>
    </form>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('plugins/sweetalert/dist/sweetalert2.all.min.js') }}"></script>
<script>
    $(document).ready(function() {

        $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%', tags: true });

        $(document).on('input', '.input-decimal', function () {
            let val = $(this).val();
            val = val.replace(/[^0-9.]/g, '');
            const parts = val.split('.');
            if (parts.length > 2) val = parts[0] + '.' + parts.slice(1).join('');
            $(this).val(val);
        });
        $(document).on('blur', '.input-decimal', function () {
            let val = $(this).val().replace(/^\./, '').replace(/\.$/, '');
            $(this).val(val);
        });
        $(document).on('keypress', '.input-decimal', function (e) {
            const allowed = /[0-9.]/;
            const char = String.fromCharCode(e.which);
            if (!allowed.test(char)) e.preventDefault();
            if (char === '.' && $(this).val().includes('.')) e.preventDefault();
        });

        $('#ceisaTab a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });

        const optDokumenHtml = `
            <option value="">-- Pilih Kode --</option>
            @foreach($referensiDokumen as $val => $text)
                <option value="{{ $val }}">{{ $val }} - {{ $text }}</option>
            @endforeach
        `;
        let dokIndex = {{ count($dokumens) }};
        $('#btn-add-dok').on('click', function() {
            let htmlTr = `
                <tr>
                    <td><select name="dokumen[${dokIndex}][kodeDokumen]" class="form-control form-control-sm select2bs4-dynamic">${optDokumenHtml}</select></td>
                    <td><input type="text" name="dokumen[${dokIndex}][nomorDokumen]" class="form-control form-control-sm" value=""></td>
                    <td><input type="date" name="dokumen[${dokIndex}][tanggalDokumen]" class="form-control form-control-sm" value=""></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-dok"><i class="fas fa-trash-alt"></i></button></td>
                </tr>
            `;
            $('#tbody-dokumen').append(htmlTr);
            $(`select[name="dokumen[${dokIndex}][kodeDokumen]"]`).select2({ theme: 'bootstrap4', width: '100%', tags: true });
            dokIndex++;
        });
        $(document).on('click', '.btn-hapus-dok', function() {
            $(this).closest('tr').remove();
        });

        const optJenisKemasan = `
            <option value="">-- Pilih --</option>
            @foreach($listJenisKemasan as $kKem => $vKem)
            <option value="{{ $kKem }}">{{ $kKem }} - {{ $vKem }}</option>
            @endforeach
        `;
        let kemasanIndex = {{ count($kemasans) }};
        $('#btn-add-kemasan').on('click', function() {
            let htmlTr = `
                <tr>
                    <td><input type="text" inputmode="decimal" name="kemasan[${kemasanIndex}][jumlahKemasan]" class="form-control form-control-sm input-decimal" value="0" placeholder="contoh: 10"></td>
                    <td><select name="kemasan[${kemasanIndex}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4-dynamic">${optJenisKemasan}</select></td>
                    <td><input type="text" name="kemasan[${kemasanIndex}][merkKemasan]" class="form-control form-control-sm" value="-" placeholder="contoh: KARTON / -"></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kemasan"><i class="fas fa-trash-alt"></i></button></td>
                </tr>
            `;
            $('#tbody-kemasan').append(htmlTr);
            $(`select[name="kemasan[${kemasanIndex}][kodeJenisKemasan]"]`).select2({ theme: 'bootstrap4', width: '100%', tags: true });
            kemasanIndex++;
        });
        $(document).on('click', '.btn-hapus-kemasan', function() {
            $(this).closest('tr').remove();
        });


        const optJenisKontainer = `<option value="">-- Pilih --</option><option value="4">4 - Empty</option><option value="7">7 - LCL</option><option value="8">8 - FCL</option>`;
        const optTipeKontainer = `<option value="">-- Pilih --</option><option value="1">1 - General/Dry Cargo</option><option value="2">2 - Tunnel Type</option><option value="3">3 - Open Top Steel</option><option value="4">4 - Flat Rack</option><option value="5">5 - Reefer/Refrigerated</option><option value="6">6 - Barge Container</option><option value="7">7 - Bulk Container</option><option value="8">8 - Isotank</option><option value="99">99 - Lain-lain</option>`;
        const optUkuranKontainer = `<option value="">-- Pilih --</option><option value="20">20 Feet</option><option value="40">40 Feet</option><option value="45">45 Feet</option> <option value="60">60 Feet</option>`;

        let kontainerIndex = {{ count($kontainers) }};
        $('#btn-add-kontainer').on('click', function() {
            let htmlTr = `
                <tr>
                    <td><input type="text" name="kontainer[${kontainerIndex}][nomorKontainer]" class="form-control form-control-sm text-uppercase" placeholder="contoh: TGHU1234567"></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeJenisKontainer]" class="form-control form-control-sm select2bs4-dynamic">${optJenisKontainer}</select></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeTipeKontainer]" class="form-control form-control-sm select2bs4-dynamic">${optTipeKontainer}</select></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeUkuranKontainer]" class="form-control form-control-sm select2bs4-dynamic">${optUkuranKontainer}</select></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kontainer"><i class="fas fa-trash-alt"></i></button></td>
                </tr>
            `;
            $('#tbody-kontainer').append(htmlTr);
            $(`select[name^="kontainer[${kontainerIndex}]"]`).select2({ theme: 'bootstrap4', width: '100%' });
            kontainerIndex++;
        });
        $(document).on('click', '.btn-hapus-kontainer', function() {
            $(this).closest('tr').remove();
        });


        function validasiBC25() {
            let errors = [];
            let firstTab = null;

            $('#form-edit-ceisa').find('input, select, textarea').each(function() {
                let el = $(this);

                if (el.is(':disabled') || el.is('[readonly]') || el.attr('type') === 'hidden' || el.attr('type') === 'button' || el.attr('type') === 'submit') {
                    return;
                }

                let val = el.val();
                let isEmpty = !val || val.toString().trim() === '';

                if (isEmpty) {
                    let labelText = el.closest('.form-group').find('label').first().text().trim();
                    if (!labelText) labelText = el.attr('name');

                    errors.push(labelText);
                    el.addClass('border-danger');

                    if (!firstTab) {
                        let tabPane = el.closest('.tab-pane');
                        if (tabPane.length) {
                            firstTab = '#' + tabPane.attr('id');
                        }
                    }
                } else {
                    el.removeClass('border-danger');
                }
            });

            if (errors.length > 0) {
                if (firstTab) {
                    let tabId = firstTab.replace('#tab-', '');
                    $('#' + tabId + '-tab').tab('show');
                }

                let uniqueErrors = [...new Set(errors)];

                Swal.fire({
                    title: 'Field Wajib Belum Diisi!',
                    html: '<div style="text-align:left; font-size:14px; max-height: 250px; overflow-y: auto;">' +
                          'Terdapat inputan yang masih kosong. Silakan isi <b>-</b> untuk teks kosong, <b>0</b> untuk angka, dan jangan biarkan dropdown default:<br><ul style="margin-top:8px">' +
                          uniqueErrors.map(e => '<li><b>' + e + '</b></li>').join('') +
                          '</ul></div>',
                    icon: 'error',
                    confirmButtonColor: '#003366'
                });
                return false;
            }
            return true;
        }

        $('#form-edit-ceisa').on('submit', function(e) {
            e.preventDefault();

            // if (!validasiBC25()) return; // Validasi dimatikan sesuai request user

            Swal.fire({
                title: 'Simpan Perubahan?',
                text: "Data akan dikirim tanpa memuat ulang halaman.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Ya, Simpan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
                    $.ajax({
                        url: $(this).attr('action'),
                        type: $(this).attr('method') || 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Data telah diperbarui.',
                                icon: 'success'
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                title: 'Gagal!',
                                text: 'Terjadi kesalahan saat menyimpan data.',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        });

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

        // Auto Calculation Logic
        function calculateTotals() {
            let totalHarga = 0;
            let totalNetto = 0;
            let totalVolume = 0;

            let dataPPN = {
                '3': 0, // Ditangguhkan
                '5': 0, // Dibebaskan
                '6': 0, // Tidak Dipungut
                '7': 0  // Sudah Dilunasi
            };

            $('#accordionBarang .card').each(function(index) {
                let row = $(this);

                let inputHarga = row.find('input[name$="[hargaPenyerahan]"]');
                if (inputHarga.length > 0) {
                    let valHarga = parseFloat(inputHarga.val().replace(/,/g, '')) || 0;
                    totalHarga += valHarga;

                    let selectFasilitas = row.find('select[name$="[barangTarif][kodeFasilitasTarif]"]');
                    let kodeFasilitas = selectFasilitas.val();

                    let inputTarif = row.find('input[name$="[barangTarif][tarif]"]');
                    let tarif = parseFloat(inputTarif.val()) || 0;

                    let ppnRow = valHarga * (tarif / 100);

                    if (dataPPN[kodeFasilitas] !== undefined) {
                        dataPPN[kodeFasilitas] += ppnRow;
                    }
                }

                let inputNetto = row.find('input[name$="[netto]"]');
                if (inputNetto.length > 0) {
                    let valNetto = parseFloat(inputNetto.val().replace(/,/g, '')) || 0;
                    totalNetto += valNetto;
                }

                let inputVolume = row.find('input[name$="[volume]"]');
                if (inputVolume.length > 0) {
                    let valVolume = parseFloat(inputVolume.val().replace(/,/g, '')) || 0;
                    totalVolume += valVolume;
                }
            });

            let formatDecimal = function(num) {
                if(num % 1 === 0) return num.toString() + '.0000';
                return num.toFixed(4);
            };

            let formatIdr = function(num) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(num);
            };

            $('#totalHargaPenyerahan').val(formatDecimal(totalHarga));
            $('#totalNetto').val(formatDecimal(totalNetto));
            $('#totalVolume').val(formatDecimal(totalVolume));

            // let inputBruto = $('input[name="bruto"]');
            // let currentBruto = parseFloat(inputBruto.val().replace(/,/g, '')) || 0;
            // if (currentBruto < totalNetto) {
            //     inputBruto.val(formatDecimal(totalNetto));
            // }

            $('#text-ppn-ditangguhkan').text(formatIdr(dataPPN['3']));
            $('#text-ppn-dibebaskan').text(formatIdr(dataPPN['5']));
            $('#text-ppn-tidak-dipungut').text(formatIdr(dataPPN['6']));
            $('#text-ppn-sudah-dilunasi').text(formatIdr(dataPPN['7']));

            let hiddenContainer = $('#hidden-pungutan-container');
            hiddenContainer.empty();

            let arrayIndex = 0;
            for (let kode in dataPPN) {
                if (dataPPN[kode] > 0) {
                    hiddenContainer.append(`<input type="hidden" name="pungutan[${arrayIndex}][kodeFasilitasTarif]" value="${kode}">`);
                    hiddenContainer.append(`<input type="hidden" name="pungutan[${arrayIndex}][kodeJenisPungutan]" value="PPN">`);
                    hiddenContainer.append(`<input type="hidden" name="pungutan[${arrayIndex}][nilaiPungutan]" value="${formatDecimal(dataPPN[kode])}">`);
                    arrayIndex++;
                }
            }
        }

        $(document).on('input change', 'input[name$="[hargaPenyerahan]"], input[name$="[netto]"], input[name$="[volume]"], select[name$="[barangTarif][kodeFasilitasTarif]"], input[name$="[barangTarif][tarif]"]', function() {
            if($(this).attr('name').indexOf('barang[') === 0) {
                calculateTotals();
            }
        });

        calculateTotals();

        $('.column-search').on('keyup', function() {
            $('#tab-pungutan tbody tr').show();

            $('.column-search').each(function() {
                let val = $(this).val().toLowerCase();
                let colIdx = $(this).data('column');

                if (val) {
                    $('#tab-pungutan tbody tr').each(function() {
                        let cellText = $(this).find('td').eq(colIdx).text().toLowerCase();
                        if (cellText.indexOf(val) === -1) {
                            $(this).hide();
                        }
                    });
                }
            });
        });

    });
</script>

{{-- Toggle BMT & Cukai per barang item --}}
@foreach($items as $index => $item)
<script>
    function toggleBmSpesifik{{ $index }}(select) {
        let isSpesifik = (select.value === '2');
        document.getElementById('bmTarifAdval{{ $index }}').style.display = isSpesifik ? 'none' : '';
        document.getElementById('bmSpesifikRow{{ $index }}').style.display = isSpesifik ? '' : 'none';
    }
    function toggleBmt{{ $index }}(cb) {
        document.getElementById('bmtPanel{{ $index }}').style.display = cb.checked ? '' : 'none';
    }
    function toggleCukai{{ $index }}(cb) {
        document.getElementById('cukaiPanel{{ $index }}').style.display = cb.checked ? '' : 'none';
    }
</script>
@endforeach

@endsection
