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

<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">
            <i class="fas fa-edit"></i> BC 2.3 - PEMBERITAHUAN IMPOR BARANG UNTUK DITIMBUN DI TEMPAT PENIMBUNAN BERIKAT

        </h5>
    </div>

    <form action="{{ route('dokumen-pabean-update_draft_batch_bc23', $batch_id) }}" method="POST" id="form-edit-ceisa">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="alert alert-warning py-2 mb-4">
                <strong>Mode Batch (BC 2.3)</strong><br>
                <strong>No. Transaksi Gabungan:</strong> {{ $batch_id }} <br>
                {{ $header->supplier ?? '-' }}
                <input type="hidden" name="bpbno_int" value="{{ $header->bpbno_int }}">
                <input type="hidden" name="no_dokumen_merge" value="{{ $batch_id }}">
                <input type="hidden" name="kodeDokumen" value="261">
                <input type="hidden" name="asalData" value="S">
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
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Pengajuan</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label>Nomor Aju</label>
                                        <input type="text" name="nomorAju" class="form-control form-control-sm fw-bold" value="{{ $nomorAju }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Kode Kantor Pabean</label>
                                        <select name="kodeKantor" class="form-control form-control-sm select2bs4">
                                            <option value="">Pilih Kantor</option>
                                            @foreach($kantorList as $val => $label)
                                                <option value="{{ $val }}" {{ (isset($dataDetail['kodeKantor']) && $dataDetail['kodeKantor'] == $val) || (!isset($dataDetail['kodeKantor']) && $val == '050500') ? 'selected' : '' }}>
                                                    {{ $val }} - {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Kantor Pabean Bongkar</label>
                                        <select name="kodeKantorBongkar" class="form-control form-control-sm select2bs4">
                                            <option value="">Pilih Kantor Bongkar</option>
                                            @foreach($kantorList as $val => $label)
                                                <option value="{{ $val }}" {{ (isset($dataDetail['kodeKantorBongkar']) && $dataDetail['kodeKantorBongkar'] == $val) ? 'selected' : '' }}>
                                                    {{ $val }} - {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>Pelabuhan Bongkar</label>
                                        <select name="kodePelBongkar" class="form-control form-control-sm select2-pelabuhan select2bs4">
                                            @if(!empty($dataDetail['kodePelBongkar']))
                                                <option value="{{ $dataDetail['kodePelBongkar'] }}" selected>{{ $dataDetail['kodePelBongkar'] }}</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Jenis & Tujuan TPB</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label>Jenis TPB</label>
                                        <select name="jenisTPB" class="form-control form-control-sm select2bs4">
                                            <option value="">Pilih Jenis TPB</option>
                                            @php $selectedJenisTPB = $dataDetail['kodeJenisTpb'] ?? $dataDetail['jenisTpb'] ?? '' @endphp
                                            <option value="1" {{ $selectedJenisTPB == '1' ? 'selected' : '' }}>KAWASAN BERIKAT</option>
                                            <option value="2" {{ $selectedJenisTPB == '2' ? 'selected' : '' }}>GUDANG BERIKAT</option>
                                            <option value="3" {{ $selectedJenisTPB == '3' ? 'selected' : '' }}>TPPB</option>
                                            <option value="4" {{ $selectedJenisTPB == '4' ? 'selected' : '' }}>TBB</option>
                                            <option value="5" {{ $selectedJenisTPB == '5' ? 'selected' : '' }}>TLB</option>
                                            <option value="6" {{ $selectedJenisTPB == '6' ? 'selected' : '' }}>KDUB</option>
                                            <option value="7" {{ $selectedJenisTPB == '7' ? 'selected' : '' }}>LAINNYA</option>
                                            <option value="8" {{ $selectedJenisTPB == '8' ? 'selected' : '' }}>KAWASAN BEBAS</option>
                                            <option value="9" {{ $selectedJenisTPB == '9' ? 'selected' : '' }}>KAWASAN EKONOMI KHUSUS</option>
                                            <option value="10" {{ $selectedJenisTPB == '10' ? 'selected' : '' }}>KAWASAN EKONOMI LAINNYA</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>Tujuan TPB</label>
                                        <select name="kodeTujuanTpb" class="form-control form-control-sm select2bs4">
                                            <option value="">Pilih Tujuan TPB</option>
                                            @php $tujuanTpb = $dataDetail['kodeTujuanTpb'] ?? '' @endphp
                                            <option value="1" {{ $tujuanTpb == '1' ? 'selected' : '' }}>KAWASAN BERIKAT</option>
                                            <option value="2" {{ $tujuanTpb == '2' ? 'selected' : '' }}>GUDANG BERIKAT</option>
                                            <option value="3" {{ $tujuanTpb == '3' ? 'selected' : '' }}>TPPB</option>
                                            <option value="4" {{ $tujuanTpb == '4' ? 'selected' : '' }}>TBB</option>
                                            <option value="5" {{ $tujuanTpb == '5' ? 'selected' : '' }}>TLB</option>
                                            <option value="6" {{ $tujuanTpb == '6' ? 'selected' : '' }}>KDUB</option>
                                            <option value="7" {{ $tujuanTpb == '7' ? 'selected' : '' }}>LAINNYA</option>
                                            <option value="8" {{ $tujuanTpb == '8' ? 'selected' : '' }}>KAWASAN BEBAS</option>
                                            <option value="9" {{ $tujuanTpb == '9' ? 'selected' : '' }}>KAWASAN EKONOMI KHUSUS</option>
                                            <option value="10" {{ $tujuanTpb == '10' ? 'selected' : '' }}>KAWASAN EKONOMI LAINNYA</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Lainnya</div>
                                <div class="card-body">
                                    <div class="form-group mb-0">
                                        <label>Kode Tutup PU</label>
                                        <select name="kodeTutupPu" class="form-control form-control-sm select2bs4">
                                            @php $tutupPu = $dataDetail['kodeTutupPu'] ?? '11' @endphp
                                            <option value="11" {{ $tutupPu == '11' ? 'selected' : '' }}>11 - BC 1.1</option>
                                            <option value="12" {{ $tutupPu == '12' ? 'selected' : '' }}>12 - BC 1.2</option>
                                            <option value="13" {{ $tutupPu == '13' ? 'selected' : '' }}>13 - BC 1.3</option>
                                            <option value="14" {{ $tutupPu == '14' ? 'selected' : '' }}>14 - BC 1.4</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-barang" role="tabpanel">
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
                                        {{ $item->goods_code ?? $item->id_item }} - {{ $item->itemdesc }}
                                    </div>
                                    <i class="fas fa-chevron-down icon-collapse"></i>
                                </div>
                            </div>

                            <div id="collapseBarang{{ $index }}" class="collapse" data-parent="#accordionBarang">
                                <div class="card-body py-3 px-3 bg-white">

                                    <input type="hidden" name="barang[{{ $index }}][kodeBarang]" value="{{ $draftItem['kodeBarang'] ?? $item->goods_code ?? $item->id_item }}">
                                    <input type="hidden" name="barang[{{ $index }}][seriBarang]" value="{{ $index + 1 }}">


                                    <div class="row">
                                        <!-- KOLOM JENIS -->
                                        <div class="col-md-4">
                                            <div class="card shadow-sm mb-3">
                                                <div class="card-header bg-light fw-bold" style="font-size: 13px;">Jenis</div>
                                                <div class="card-body">
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Seri</label>
                                                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $index + 1 }}" readonly>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Pos Tarif/HS</label>
                                                        <input type="text" name="barang[{{ $index }}][posTarif]" class="form-control form-control-sm" value="{{ $draftItem['posTarif'] ?? '' }}" placeholder="Masukkan Pos Tarif/HS">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Kode Barang</label>
                                                        <input type="text" name="barang[{{ $index }}][kodeBarang]" class="form-control form-control-sm" value="{{ $draftItem['kodeBarang'] ?? $item->id_item ?? '' }}">
                                                        <input type="text" name="barang[{{ $index }}][idItem]" class="form-control form-control-sm hidden" value="{{ $item->id_item ?? '' }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Uraian Jenis Barang</label>
                                                        <textarea name="barang[{{ $index }}][uraian]" class="form-control form-control-sm" rows="2">{{ $draftItem['uraian'] ?? $item->itemdesc }}</textarea>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Merek</label>
                                                        <input type="text" name="barang[{{ $index }}][merk]" class="form-control form-control-sm" value="{{ $draftItem['merk'] ?? '-' }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Tipe</label>
                                                        <input type="text" name="barang[{{ $index }}][tipe]" class="form-control form-control-sm" value="{{ $draftItem['tipe'] ?? 'TIPE BARANG' }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Ukuran</label>
                                                        <input type="text" name="barang[{{ $index }}][ukuran]" class="form-control form-control-sm" value="{{ $draftItem['ukuran'] ?? '-' }}">
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="small text-muted mb-0">Spesifikasi Lain</label>
                                                        <input type="text" name="barang[{{ $index }}][spesifikasiLain]" class="form-control form-control-sm" value="{{ $draftItem['spesifikasiLain'] ?? $item->remark ?? '-' }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- KOLOM KETERANGAN LAINNYA & HARGA -->
                                        <div class="col-md-4">
                                            <div class="card shadow-sm mb-3">
                                                <div class="card-header bg-light fw-bold" style="font-size: 13px;">Keterangan Lainnya</div>
                                                <div class="card-body">
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Kategori Barang</label>
                                                        <select name="barang[{{ $index }}][kodeKategoriBarang]" class="form-control form-control-sm select2bs4">
                                                            <option value="">Pilih Kategori</option>
                                                            <option value="01" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '01' ? 'selected' : '' }}>01 - BARANG UNTUK DITIMBUN</option>
                                                            <option value="02" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '02' ? 'selected' : '' }}>02 - BARANG UNTUK KEPERLUAN PENGUSAHAAN</option>
                                                            <option value="11" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '11' ? 'selected' : '' }}>11 - UNTUK BAHAN BAKU/BAHAN PENOLONG</option>
                                                            <option value="12" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '12' ? 'selected' : '' }}>12 - UNTUK PENGEMAS/ALAT BANTU PENGEMAS</option>
                                                            <option value="13" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '13' ? 'selected' : '' }}>13 - UNTUK PERALATAN UNTUK PEMBANGUNAN, PERLUASAN, ATAU KONSTRUKSI KB</option>
                                                            <option value="14" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '14' ? 'selected' : '' }}>14 - UNTUK BARANG MODAL DAN/ATAU SPAREPARTS BARANG MODAL</option>
                                                            <option value="15" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '15' ? 'selected' : '' }}>15 - UNTUK BARANG CONTOH</option>
                                                            <option value="16" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '16' ? 'selected' : '' }}>16 - UNTUK BARANG JADI GUNA DIGABUNG DENGAN HASIL PRODUKSI</option>
                                                            <option value="17" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '17' ? 'selected' : '' }}>17 - UNTUK BARANG REIMPOR</option>
                                                            <option value="18" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '18' ? 'selected' : '' }}>18 - UNTUK PERALATAN PERKANTORAN</option>
                                                            <option value="19" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '19' ? 'selected' : '' }}>19 - BARANG UNTUK KEPERLUAN PENANGANAN COVID19</option>
                                                            <option value="21" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '21' ? 'selected' : '' }}>21 - UNTUK BARANG YANG DITIMBUN DI GB</option>
                                                            <option value="22" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '22' ? 'selected' : '' }}>22 - UNTUK BARANG REIMPOR</option>
                                                            <option value="31" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '31' ? 'selected' : '' }}>31 - UNTUK BARANG UNTUK DIPAMERKAN</option>
                                                            <option value="32" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '32' ? 'selected' : '' }}>32 - UNTUK BARANG UNTUK MENDUKUNG KEPERLUAN PAMERAN</option>
                                                            <option value="33" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '33' ? 'selected' : '' }}>33 - UNTUK BARANG REIMPOR</option>
                                                            <option value="41" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '41' ? 'selected' : '' }}>41 - UNTUK BARANG YANG DITIMBUN DI TBB</option>
                                                            <option value="42" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '42' ? 'selected' : '' }}>42 - UNTUK BARANG REIMPOR</option>
                                                            <option value="51" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '51' ? 'selected' : '' }}>51 - UNTUK BARANG LELANG</option>
                                                            <option value="52" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '52' ? 'selected' : '' }}>52 - UNTUK SPAREPARTS</option>
                                                            <option value="53" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '53' ? 'selected' : '' }}>53 - UNTUK BARANG REIMPOR</option>
                                                            <option value="61" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '61' ? 'selected' : '' }}>61 - UNTUK BARANG YANG DITIMBUN DI KDUB</option>
                                                            <option value="62" {{ ($draftItem['kodeKategoriBarang'] ?? '') == '62' ? 'selected' : '' }}>62 - UNTUK BARANG REIMPOR</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="small text-muted mb-0">Negara</label>
                                                        <select name="barang[{{ $index }}][kodeNegaraAsal]" class="form-control form-control-sm select2bs4">
                                                            <option value="">Pilih Negara</option>
                                                            @include('export-import.dokumen-pabean.options_negara', ['selected' => $draftItem['kodeNegaraAsal'] ?? ''])
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card shadow-sm mb-3">
                                                <div class="card-header bg-light fw-bold" style="font-size: 13px;">Harga</div>
                                                <div class="card-body">
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Harga CIF (Nilai Barang)</label>
                                                        <input type="number" step="any" name="barang[{{ $index }}][nilaiBarang]" class="form-control form-control-sm input-cif-barang" value="{{ $draftItem['nilaiBarang'] ?? $draftItem['cif'] ?? 0 }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">FOB</label>
                                                        <input type="number" step="any" name="barang[{{ $index }}][fob]" class="form-control form-control-sm" value="{{ $draftItem['fob'] ?? 0 }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Harga Satuan</label>
                                                        <input type="number" step="any" name="barang[{{ $index }}][hargaSatuan]" class="form-control form-control-sm" value="{{ $draftItem['hargaSatuan'] ?? 0 }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Freight</label>
                                                        <input type="number" step="any" name="barang[{{ $index }}][freight]" class="form-control form-control-sm" value="{{ $draftItem['freight'] ?? 0 }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Asuransi</label>
                                                        <input type="number" step="any" name="barang[{{ $index }}][asuransi]" class="form-control form-control-sm" value="{{ $draftItem['asuransi'] ?? 0 }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Nilai CIF</label>
                                                        <input type="number" step="any" name="barang[{{ $index }}][cif]" class="form-control form-control-sm" value="{{ $draftItem['cif'] ?? 0 }}">
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="small text-muted mb-0">Nilai Pabean (Rp)</label>
                                                        <input type="number" step="any" name="barang[{{ $index }}][cifRupiah]" class="form-control form-control-sm" value="{{ $draftItem['cifRupiah'] ?? 0 }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- KOLOM JUMLAH & BERAT & PUNGUTAN -->
                                        <div class="col-md-4">
                                            <div class="card shadow-sm mb-3">
                                                <div class="card-header bg-light fw-bold" style="font-size: 13px;">Jumlah & Berat</div>
                                                <div class="card-body">
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Jumlah dan Satuan Barang</label>
                                                        <div class="row">
                                                            <div class="col-7 pr-1">
                                                                <input type="number" step="any" name="barang[{{ $index }}][jumlahSatuan]" class="form-control form-control-sm" value="{{ $draftItem['jumlahSatuan'] ?? (float) $item->qty }}">
                                                            </div>
                                                            <div class="col-5 pl-1">
                                                                <select name="barang[{{ $index }}][kodeSatuanBarang]" class="form-control form-control-sm select2bs4">
                                                                    @php $savedSatuan = $draftItem['kodeSatuanBarang'] ?? 'PCS'; @endphp
                                                                    @foreach($listSatuanBarang as $kode => $label)
                                                                        <option value="{{ $kode }}" {{ $savedSatuan == $kode ? 'selected' : '' }}>{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Kemasan</label>
                                                        <div class="row">
                                                            <div class="col-7 pr-1">
                                                                <input type="number" step="any" name="barang[{{ $index }}][jumlahKemasan]" class="form-control form-control-sm" value="{{ $draftItem['jumlahKemasan'] ?? 0 }}">
                                                            </div>
                                                            <div class="col-5 pl-1">
                                                                <select name="barang[{{ $index }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4">
                                                                    @php $savedKemasan = $draftItem['kodeJenisKemasan'] ?? 'CT'; @endphp
                                                                    <option value="">-- Pilih --</option>
                                                                    @foreach($listJenisKemasan as $k => $v)
                                                                        <option value="{{ $k }}" {{ $savedKemasan == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="small text-muted mb-0">Berat Bersih (kg)</label>
                                                        <input type="number" step="any" name="barang[{{ $index }}][netto]" class="form-control form-control-sm" value="{{ $draftItem['netto'] ?? (float) ($item->nw ?? $item->netto ?? 0) }}">
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- <div class="card shadow-sm mb-3">
                                                <div class="card-header bg-light fw-bold" style="font-size: 13px;">
                                                    Dokumen Fasilitas/Lartas
                                                    <button type="button" class="btn btn-primary btn-sm float-right py-0" style="font-size: 11px;"><i class="fas fa-plus"></i> Tambah</button>
                                                </div>
                                                <div class="card-body p-2">
                                                    <table class="table table-bordered table-sm text-center mb-0" style="font-size: 11px;">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th>Seri</th>
                                                                <th>Jenis</th>
                                                                <th>Nomor</th>
                                                                <th>Tanggal</th>
                                                                <th>Fasilitas</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td colspan="5" class="text-muted py-3">No Data</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div> --}}

                                            <div class="card shadow-sm mb-3">
                                                <div class="card-header bg-light fw-bold" style="font-size: 13px;">Pungutan (Tarif)</div>
                                                <div class="card-body p-2">
                                                    @php
                                                        $tarifList = $draftItem['barangTarif'] ?? [
                                                            ['kodeJenisPungutan' => 'BM',  'tarif' => 0, 'kodeFasilitasTarif' => '3', 'tarifFasilitas' => 100],
                                                            ['kodeJenisPungutan' => 'PPN', 'tarif' => 0, 'kodeFasilitasTarif' => '3', 'tarifFasilitas' => 100],
                                                            ['kodeJenisPungutan' => 'PPNBM', 'tarif' => 0, 'kodeFasilitasTarif' => '3', 'tarifFasilitas' => 100],
                                                            ['kodeJenisPungutan' => 'PPH', 'tarif' => 0, 'kodeFasilitasTarif' => '3', 'tarifFasilitas' => 100],
                                                        ];
                                                        $tarifBM = $tarifPPN = $tarifPPNBM = $tarifPPH = null;
                                                        foreach($tarifList as $t) {
                                                            if(($t['kodeJenisPungutan']??'') == 'BM')  $tarifBM  = $t;
                                                            if(($t['kodeJenisPungutan']??'') == 'PPN') $tarifPPN = $t;
                                                            if(($t['kodeJenisPungutan']??'') == 'PPNBM') $tarifPPNBM = $t;
                                                            if(($t['kodeJenisPungutan']??'') == 'PPH') $tarifPPH = $t;
                                                        }
                                                        if(!$tarifBM)  $tarifBM  = ['kodeJenisPungutan' => 'BM',  'tarif' => 0, 'kodeFasilitasTarif' => '3', 'tarifFasilitas' => 100];
                                                        if(!$tarifPPN) $tarifPPN = ['kodeJenisPungutan' => 'PPN', 'tarif' => 0, 'kodeFasilitasTarif' => '3', 'tarifFasilitas' => 100];
                                                        if(!$tarifPPNBM) $tarifPPNBM = ['kodeJenisPungutan' => 'PPNBM', 'tarif' => 0, 'kodeFasilitasTarif' => '3', 'tarifFasilitas' => 100];
                                                        if(!$tarifPPH) $tarifPPH = ['kodeJenisPungutan' => 'PPH', 'tarif' => 0, 'kodeFasilitasTarif' => '3', 'tarifFasilitas' => 100];
                                                        $tarifList = [$tarifBM, $tarifPPN, $tarifPPNBM, $tarifPPH];
                                                    @endphp
                                                    <table class="table table-bordered table-sm mb-0" style="font-size:11px;">
                                                        <thead class="bg-light text-center">
                                                            <tr>
                                                                <th>Jenis</th>
                                                                <th>Tarif (%)</th>
                                                                <th>Fas. (%)</th>
                                                                <th>Fasilitas</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach($tarifList as $tIdx => $tarif)
                                                        <tr>
                                                            <td class="text-center align-middle fw-bold">{{ $tarif['kodeJenisPungutan'] }}
                                                                <input type="hidden" name="barang[{{ $index }}][barangTarif][{{$tIdx}}][kodeJenisPungutan]" value="{{ $tarif['kodeJenisPungutan'] }}">
                                                            </td>
                                                            <td><input type="text" inputmode="decimal" name="barang[{{ $index }}][barangTarif][{{$tIdx}}][tarif]" class="form-control form-control-sm input-decimal text-center" value="{{ $tarif['tarif'] }}" style="font-size:11px;"></td>
                                                            <td><input type="text" inputmode="decimal" name="barang[{{ $index }}][barangTarif][{{$tIdx}}][tarifFasilitas]" class="form-control form-control-sm input-decimal text-center" value="{{ $tarif['tarifFasilitas'] }}" style="font-size:11px;"></td>
                                                            <td>
                                                                <select name="barang[{{ $index }}][barangTarif][{{$tIdx}}][kodeFasilitasTarif]" class="form-control form-control-sm select2bs4" style="font-size:11px;">
                                                                    <option value="3" {{ ($tarif['kodeFasilitasTarif'] ?? '') == '3' ? 'selected' : '' }}>3 - Ditangguhkan</option>
                                                                    <option value="5" {{ ($tarif['kodeFasilitasTarif'] ?? '') == '5' ? 'selected' : '' }}>5 - Dibebaskan</option>
                                                                    <option value="6" {{ ($tarif['kodeFasilitasTarif'] ?? '') == '6' ? 'selected' : '' }}>6 - Tdk Dipungut</option>
                                                                    <option value="7" {{ ($tarif['kodeFasilitasTarif'] ?? '') == '7' ? 'selected' : '' }}>7 - Dilunasi</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                        </tbody>
                                                        <tfoot class="bg-light">
                                                            <tr>
                                                                <td colspan="4" class="text-muted text-center" style="font-size:10px;">Total: BM + PPH + PPN dihitung di tab Pungutan</td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                                <div class="mt-3 w-100 px-3">
                                                    <div class="custom-control custom-checkbox mb-2">
                                                        <input type="checkbox" class="custom-control-input bmt-toggle" id="bmtToggle{{$index}}">
                                                        <label class="custom-control-label fw-bold" for="bmtToggle{{$index}}" style="font-size:12px;">BMT (Bea Masuk Tambahan)</label>
                                                    </div>
                                                    <div class="bmt-container p-3 bg-light border rounded" style="display: none;">
                                                        @php
                                                            $bmtTypes = ['BMAD', 'BMTP', 'BMI', 'BMP'];
                                                            $bmtStartIndex = 3; // After BM, PPH, PPN
                                                        @endphp
                                                        @foreach($bmtTypes as $bIdx => $bmtType)
                                                        @php
                                                            $tIdx = $bmtStartIndex + $bIdx;
                                                            $bmtData = null;
                                                            foreach(($draftItem['barangTarif'] ?? []) as $t) {
                                                                if(($t['kodeJenisPungutan']??'') == $bmtType) $bmtData = $t;
                                                            }
                                                            $isSementara = !empty($bmtData['sementara']) ? 'checked' : '';
                                                            $jenisTarif = $bmtData['kodeJenisTarif'] ?? '1'; // 1 = Advalorum (%), 2 = Spesifik
                                                        @endphp
                                                        <div class="mb-3 bmt-row border-bottom pb-2">
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2" style="font-size: 11px;">
                                                                    <div class="fw-bold">{{ $bmtType }}</div>
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input" name="barang[{{ $index }}][barangTarif][{{$tIdx}}][sementara]" id="sementara{{$bmtType}}{{$index}}" value="1" {{ $isSementara }}>
                                                                        <label class="custom-control-label text-muted" for="sementara{{$bmtType}}{{$index}}">Sementara</label>
                                                                    </div>
                                                                </div>
                                                                <input type="hidden" name="barang[{{ $index }}][barangTarif][{{$tIdx}}][kodeJenisPungutan]" value="{{ $bmtType }}">
                                                                <div class="col-md-3">
                                                                    <select name="barang[{{ $index }}][barangTarif][{{$tIdx}}][kodeJenisTarif]" class="form-control form-control-sm bmt-jenis-tarif select2bs4" style="font-size: 11px;">
                                                                        <option value="1" {{ $jenisTarif == '1' ? 'selected' : '' }}>Advalorum (%)</option>
                                                                        <option value="2" {{ $jenisTarif == '2' ? 'selected' : '' }}>Spesifik</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-7 bmt-inputs-container">
                                                                    <!-- Advalorum -->
                                                                    <div class="bmt-advalorum-inputs" style="{{ $jenisTarif == '1' ? '' : 'display:none;' }}">
                                                                        <input type="number" step="any" name="barang[{{ $index }}][barangTarif][{{$tIdx}}][tarif]" class="form-control form-control-sm" placeholder="%" value="{{ $jenisTarif == '1' ? ($bmtData['tarif'] ?? '') : '' }}" style="font-size: 11px;">
                                                                    </div>
                                                                    <!-- Spesifik -->
                                                                    <div class="bmt-spesifik-inputs row m-0" style="{{ $jenisTarif == '2' ? '' : 'display:none;' }}">
                                                                        <div class="col-6 pl-0 pr-1">
                                                                            <input type="number" step="any" name="barang[{{ $index }}][barangTarif][{{$tIdx}}][jumlahSatuan]" class="form-control form-control-sm" placeholder="Jml Satuan" value="{{ $bmtData['jumlahSatuan'] ?? '' }}" style="font-size: 11px;">
                                                                        </div>
                                                                        <div class="col-6 pr-0 pl-1">
                                                                            <input type="number" step="any" name="barang[{{ $index }}][barangTarif][{{$tIdx}}][tarifSpesifik]" class="form-control form-control-sm" placeholder="Tarif" value="{{ $jenisTarif == '2' ? ($bmtData['tarif'] ?? '') : '' }}" style="font-size: 11px;">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row align-items-center">
                                                                <div class="col-md-5"></div>
                                                                <div class="col-md-4">
                                                                    <select name="barang[{{ $index }}][barangTarif][{{$tIdx}}][kodeFasilitasTarif]" class="form-control form-control-sm select2bs4" style="font-size: 11px;">
                                                                        <option value="3" {{ ($bmtData['kodeFasilitasTarif'] ?? '') == '3' ? 'selected' : '' }}>3 - DTG - DITANGGUHKAN</option>
                                                                        <option value="5" {{ ($bmtData['kodeFasilitasTarif'] ?? '') == '5' ? 'selected' : '' }}>5 - BBS - DIBEBASKAN</option>
                                                                        <option value="6" {{ ($bmtData['kodeFasilitasTarif'] ?? '') == '6' ? 'selected' : '' }}>6 - TIDAK DIPUNGUT</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-3 pl-0">
                                                                    <div class="input-group input-group-sm">
                                                                        <input type="number" step="any" name="barang[{{ $index }}][barangTarif][{{$tIdx}}][tarifFasilitas]" class="form-control form-control-sm" placeholder="0" value="{{ $bmtData['tarifFasilitas'] ?? '0' }}" style="font-size: 11px;">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text px-2" style="font-size: 11px;">%</span>
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
                                    </div>

                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-entitas" role="tabpanel">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Entitas Pengusaha TPB (Kode: 3)</div>
                                <div class="card-body">
                                    <div class="form-group mb-2"><label class="small mb-0">Nama Entitas</label><input type="text" name="entitas[3][namaEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['namaEntitas'] ?? 'NIRWANA ALABARE GARMENT' }}"></div>
                                    <div class="form-group mb-2"><label class="small mb-0">NPWP</label><input type="text" name="entitas[3][nomorIdentitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['nomorIdentitas'] ?? '0745406926444000000000' }}"></div>
                                    <div class="form-group mb-2"><label class="small mb-0">NIB</label><input type="text" name="entitas[3][nibEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['nibEntitas'] ?? '0220103231143' }}"></div>
                                    <div class="form-group mb-2"><label class="small mb-0">Alamat</label><input type="text" name="entitas[3][alamatEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['alamatEntitas'] ?? 'JL. RAYA RANCAEKEK MAJALAYA NO. 289 RT. 001 RW. 007' }}"></div>
                                    <div class="form-group mb-2"><label class="small mb-0">No. Izin TPB</label><input type="text" name="entitas[3][nomorIjinEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['nomorIjinEntitas'] ?? '16/MK/WBC.09/2026' }}"></div>
                                    <div class="form-group mb-0"><label class="small mb-0">Tanggal Izin TPB</label><input type="date" name="entitas[3][tanggalIjinEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['tanggalIjinEntitas'] ?? '2026-01-20' }}"></div>
                                    <input type="hidden" name="entitas[3][kodeNegara]" value="{{ $dataDetail['entitas'][3]['kodeNegara'] ?? 'ID' }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Entitas Pemasok (Kode: 5)</div>
                                <div class="card-body">
                                    <div class="form-group mb-2"><label class="small mb-0">Nama Entitas</label><input type="text" name="entitas[5][namaEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][5]['namaEntitas'] ?? $dataDetail['entitas'][9]['namaEntitas'] ?? $header->supplier ?? '' }}"></div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Negara</label>
                                        <select name="entitas[5][kodeNegara]" class="form-control form-control-sm select2bs4">
                                            @include('export-import.dokumen-pabean.options_negara', ['selected' => $dataDetail['entitas'][5]['kodeNegara'] ?? $dataDetail['entitas'][9]['kodeNegara'] ?? ''])
                                        </select>
                                    </div>
                                    <div class="form-group mb-0"><label class="small mb-0">Alamat</label><input type="text" name="entitas[5][alamatEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][5]['alamatEntitas'] ?? $dataDetail['entitas'][9]['alamatEntitas'] ?? $header->alamat_supplier ?? '' }}"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Entitas Pemilik Barang (Kode: 7)</div>
                                <div class="card-body">
                                    <div class="form-group mb-2"><label class="small mb-0">Nama Entitas</label><input type="text" name="entitas[7][namaEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][7]['namaEntitas'] ?? $header->supplier ?? '' }}"></div>
                                    <div class="form-group mb-2"><label class="small mb-0">NPWP</label><input type="text" name="entitas[7][nomorIdentitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][7]['nomorIdentitas'] ?? $header->npwp_supplier ?? '' }}"></div>
                                    <div class="form-group mb-0"><label class="small mb-0">Alamat</label><input type="text" name="entitas[7][alamatEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][7]['alamatEntitas'] ?? $header->alamat_supplier ?? '' }}"></div>
                                    <input type="hidden" name="entitas[7][nomorIjinEntitas]" value="{{ $dataDetail['entitas'][7]['nomorIjinEntitas'] ?? '' }}">
                                    <input type="hidden" name="entitas[7][tanggalIjinEntitas]" value="{{ $dataDetail['entitas'][7]['tanggalIjinEntitas'] ?? '' }}">
                                    <input type="hidden" name="entitas[7][kodeNegara]" value="{{ $dataDetail['entitas'][7]['kodeNegara'] ?? 'ID' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-dokumen" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color:#001f3f;">
                            <span>Dokumen Pendukung</span>
                            <button type="button" class="btn btn-sm btn-light py-0 px-2" style="margin-left:auto !important;" id="btn-add-dok" title="Tambah Dokumen"><i class="fas fa-plus text-primary"></i> Tambah Dokumen</button>
                        </div>
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

                                $dokumens = [];
                                if (!empty($dataDetail['dok']) && count($dataDetail['dok']) > 0) {
                                    $dokumens = $dataDetail['dok'];
                                }
                            @endphp

                        <div class="card-body p-0" style="overflow-x:auto;">
                            <table class="table table-sm table-bordered mb-0" id="table-dokumen" style="min-width:700px;">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="40%">Kode Dokumen</th>
                                        <th width="30%">Nomor Dokumen</th>
                                        <th width="20%">Tgl Dokumen</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-dokumen">
                                    @foreach($dokumens as $index => $dok)
                                    <tr>
                                        <td>
                                            <select name="dok[{{ $index }}][kode]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih Kode --</option>
                                                @foreach($referensiDokumen as $val => $text)
                                                    <option value="{{ $val }}" {{ ($dok['kode'] ?? '') == $val ? 'selected' : '' }}>{{ $val }} - {{ $text }}</option>
                                                @endforeach
                                                @if(!empty($dok['kode']) && !array_key_exists($dok['kode'], $referensiDokumen))
                                                    <option value="{{ $dok['kode'] }}" selected>{{ $dok['kode'] }} - Custom</option>
                                                @endif
                                            </select>
                                        </td>
                                        <td><input type="text" name="dok[{{ $index }}][nomor]" class="form-control form-control-sm" value="{{ $dok['nomor'] ?? '' }}"></td>
                                        <td><input type="date" name="dok[{{ $index }}][tgl]" class="form-control form-control-sm" value="{{ $dok['tgl'] ?? '' }}"></td>
                                        <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-dok" title="Hapus Baris"><i class="fas fa-trash-alt"></i></button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-pengangkut" role="tabpanel">
                    <div class="d-flex justify-content-end mb-2">
                    </div>
                    <div class="row">
                        <!-- BC 1.1 -->
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light fw-bold" style="font-size:13px;">BC 1.1</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Kode BC 1.1</label>
                                        <div class="d-flex">
                                            <div style="flex: 1; padding-right: 2px;">
                                                <select name="kodeBc11" class="form-control form-control-sm select2bs4" style="width: 100%;">
                                                    <option value="10" {{ ($dataDetail['kodeBc11'] ?? $dataDetail['bc11KodeBc'] ?? '') == '10' ? 'selected' : '' }}>BC 1.0</option>
                                                    <option value="11" {{ ($dataDetail['kodeBc11'] ?? $dataDetail['bc11KodeBc'] ?? '11') == '11' ? 'selected' : '' }}>BC 1.1</option>
                                                </select>
                                            </div>
                                            <div style="flex: 1; padding-left: 2px; padding-right: 2px;">
                                                <input type="text" name="nomorBc11" class="form-control form-control-sm" value="{{ $dataDetail['nomorBc11'] ?? $dataDetail['bc11Nomor'] ?? '' }}" placeholder="No BC 1.1">
                                            </div>
                                            <div style="flex: 1; padding-left: 2px;">
                                                <input type="date" name="tanggalBc11" class="form-control form-control-sm" value="{{ $dataDetail['tanggalBc11'] ?? $dataDetail['bc11Tanggal'] ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-0">Nomor Pos</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" name="posBc11" class="form-control" value="{{ $dataDetail['posBc11'] ?? $dataDetail['bc11Pos'] ?? '' }}" placeholder="Pos">
                                            <input type="text" name="subposBc11" class="form-control" value="{{ $dataDetail['subposBc11'] ?? $dataDetail['bc11SubPos'] ?? '' }}" placeholder="Sub Pos">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pengangkutan -->
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light fw-bold" style="font-size:13px;">Pengangkutan</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Cara Pengangkutan</label>
                                        <select name="pengangkut[caraPengangkutan]" class="form-control form-control-sm select2bs4">
                                            <option value=""> -- Pilih Cara Pengangkutan -- </option>
                                            <option value="1" {{ ($dataDetail['pengangkut']['caraPengangkutan'] ?? '') == '1' ? 'selected' : '' }}>1 - LAUT</option>
                                            <option value="2" {{ ($dataDetail['pengangkut']['caraPengangkutan'] ?? '') == '2' ? 'selected' : '' }}>2 - UDARA</option>
                                            <option value="3" {{ ($dataDetail['pengangkut']['caraPengangkutan'] ?? '') == '3' ? 'selected' : '' }}>3 - DARAT</option>
                                            <option value="4" {{ ($dataDetail['pengangkut']['caraPengangkutan'] ?? '') == '4' ? 'selected' : '' }}>4 - KERETA API</option>
                                            <option value="5" {{ ($dataDetail['pengangkut']['caraPengangkutan'] ?? '') == '5' ? 'selected' : '' }}>5 - POS</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Nama Sarana Angkut</label>
                                        <input type="text" name="pengangkut[nama]" class="form-control form-control-sm" value="{{ $dataDetail['pengangkut']['nama'] ?? '' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Nomor Voy/flight/kepali/lainnya</label>
                                        <input type="text" name="pengangkut[nomor]" class="form-control form-control-sm" value="{{ $dataDetail['pengangkut']['nomor'] ?? $header->nomor_mobil ?? '-' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Bendera</label>
                                        <select name="pengangkut[kodeBendera]" class="form-control form-control-sm select2bs4">
                                            @include('export-import.dokumen-pabean.options_negara', ['selected' => $dataDetail['pengangkut']['kodeBendera'] ?? 'ID'])
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-0">Tanggal Tiba</label>
                                        <input type="date" name="tanggalTiba" class="form-control form-control-sm" value="{{ $dataDetail['tanggalTiba'] ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pelabuhan & Tempat Penimbunan -->
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light fw-bold" style="font-size:13px;">Pelabuhan & Tempat Penimbunan</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Pelabuhan Muat</label>
                                        <select name="kodePelMuat" class="form-control form-control-sm select2-pelabuhan select2bs4">
                                            @if(!empty($dataDetail['kodePelMuat']))
                                                <option value="{{ $dataDetail['kodePelMuat'] }}" selected>{{ $dataDetail['kodePelMuat'] }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Pelabuhan Transit</label>
                                        <select name="kodePelTransit" class="form-control form-control-sm select2-pelabuhan select2bs4">
                                            @if(!empty($dataDetail['kodePelTransit']))
                                                <option value="{{ $dataDetail['kodePelTransit'] }}" selected>{{ $dataDetail['kodePelTransit'] }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Pelabuhan Bongkar</label>
                                        <select name="kodePelBongkar" class="form-control form-control-sm select2-pelabuhan select2bs4">
                                            @if(!empty($dataDetail['kodePelBongkar']))
                                                <option value="{{ $dataDetail['kodePelBongkar'] }}" selected>{{ $dataDetail['kodePelBongkar'] }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-0">Tempat Penimbunan</label>
                                        <input type="text" name="kodeTps" class="form-control form-control-sm" value="{{ $dataDetail['kodeTps'] ?? $dataDetail['kodeTempPenimbunan'] ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-pungutan" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Pungutan</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center mb-0">
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
                                        @php $pungutanList = ['BM', 'PPN', 'PPNBM', 'PPH']; @endphp
                                        @foreach($pungutanList as $pung)
                                        <tr>
                                            <td class="text-left font-weight-bold" style="color:#666; font-size:12px; padding-left:15px;">{{ $pung }}</td>
                                            <td class="font-weight-bold" style="font-size:12px; color:#0000FF;" id="text-{{ strtolower($pung) }}-ditangguhkan">Rp 0,00</td>
                                            <td class="font-weight-bold" style="font-size:12px; color:#0000FF;" id="text-{{ strtolower($pung) }}-sudah-dilunasi">Rp 0,00</td>
                                            <td class="font-weight-bold" style="font-size:12px; color:#0000FF;" id="text-{{ strtolower($pung) }}-dibebaskan">Rp 0,00</td>
                                            <td class="font-weight-bold" style="font-size:12px; color:#0000FF;" id="text-{{ strtolower($pung) }}-tidak-dipungut">Rp 0,00</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <td class="text-right font-weight-bold" style="color:#333; font-size:13px; padding-right:15px;">TOTAL</td>
                                            <td class="font-weight-bold" style="font-size:13px; color:#cc0000;" id="text-total-ditangguhkan">Rp 0,00</td>
                                            <td class="font-weight-bold" style="font-size:13px; color:#cc0000;" id="text-total-sudah-dilunasi">Rp 0,00</td>
                                            <td class="font-weight-bold" style="font-size:13px; color:#cc0000;" id="text-total-dibebaskan">Rp 0,00</td>
                                            <td class="font-weight-bold" style="font-size:13px; color:#cc0000;" id="text-total-tidak-dipungut">Rp 0,00</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div id="hidden-pungutan-container">
                                <!-- Digenerate via JS -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-transaksi" role="tabpanel">
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-primary btn-sm" onclick="$('#ceisaTab a[href=\'#tab-barang\']').tab('show');">Detail Barang</button>
                    </div>
                    <div class="row">
                        <!-- Harga -->
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light fw-bold" style="font-size:13px;">Harga</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Jenis Valuta</label>
                                        <select name="kodeValuta" class="form-control form-control-sm select2bs4" id="kode_valuta">
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
                                        <label class="small text-muted mb-0">Harga Barang</label>
                                        <div class="d-flex">
                                            <div style="flex: 1; padding-right: 2px;">
                                                <select name="kodeIncoterm" class="form-control form-control-sm select2bs4" style="width: 100%;">
                                                    <option value="CIF" {{ ($dataDetail['kodeIncoterm'] ?? 'CIF') == 'CIF' ? 'selected' : '' }}>CIF - COST, INSURANCE AND FREIGHT</option>
                                                    <option value="FOB" {{ ($dataDetail['kodeIncoterm'] ?? '') == 'FOB' ? 'selected' : '' }}>FOB - FREE ON BOARD</option>
                                                </select>
                                            </div>
                                            <div style="flex: 1; padding-left: 2px;">
                                                <input type="number" step="any" name="nilaiBarang" class="form-control form-control-sm" value="{{ $dataDetail['nilaiBarang'] ?? 0 }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Nilai CIF</label>
                                        <input type="number" step="any" name="cif" class="form-control form-control-sm" value="{{ $dataDetail['cif'] ?? 0 }}" id="total_cif">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-0">Nilai Pabean dalam Rupiah</label>
                                        <input type="number" step="any" name="cifRupiah" class="form-control form-control-sm" value="{{ $dataDetail['cifRupiah'] ?? 0 }}" id="nilai_pabean">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Harga Lainnya -->
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light fw-bold" style="font-size:13px;">Harga Lainnya</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Biaya Penambah</label>
                                        <input type="number" step="any" name="biayaTambahan" class="form-control form-control-sm" value="{{ $dataDetail['biayaTambahan'] ?? 0 }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Biaya Pengurang</label>
                                        <input type="number" step="any" name="biayaPengurang" class="form-control form-control-sm" value="{{ $dataDetail['biayaPengurang'] ?? 0 }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">FOB</label>
                                        <input type="number" step="any" name="fob" class="form-control form-control-sm" value="{{ $dataDetail['fob'] ?? 0 }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Freight</label>
                                        <input type="number" step="any" name="freight" class="form-control form-control-sm" value="{{ $dataDetail['freight'] ?? 0 }}">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-0">Asuransi</label>
                                        <div class="d-flex">
                                            <div style="flex: 1; padding-right: 2px;">
                                                <select name="kodeAsuransi" class="form-control form-control-sm select2bs4" style="width: 100%;">
                                                    <option value="LN" {{ ($dataDetail['kodeAsuransi'] ?? 'LN') == 'LN' ? 'selected' : '' }}>LUAR NEGERI</option>
                                                    <option value="DN" {{ ($dataDetail['kodeAsuransi'] ?? '') == 'DN' ? 'selected' : '' }}>DALAM NEGERI</option>
                                                </select>
                                            </div>
                                            <div style="flex: 1; padding-left: 2px;">
                                                <input type="number" step="any" name="asuransi" class="form-control form-control-sm" value="{{ $dataDetail['asuransi'] ?? 0 }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Berat & Keterangan Pajak -->
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light fw-bold" style="font-size:13px;">Berat</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Berat Kotor (KGM)</label>
                                        <input type="number" step="any" name="bruto" class="form-control form-control-sm" value="{{ $dataDetail['bruto'] ?? $header->berat_kotor ?? 0 }}">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="small text-muted mb-0">Berat Bersih (KGM)</label>
                                        <input type="number" step="any" id="totalNetto" name="netto" class="form-control form-control-sm" value="{{ $dataDetail['netto'] ?? $header->berat_bersih ?? 0 }}">
                                    </div>
                                </div>
                            </div>
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light fw-bold" style="font-size:13px;">Keterangan Pajak</div>
                                <div class="card-body">
                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-0">Jasa Kena Pajak</label>
                                        <select name="kodeKenaPajak" class="form-control form-control-sm select2bs4">
                                            <option value="">Pilih Jasa Kena Pajak</option>
                                            <option value="1" {{ ($dataDetail['kodeKenaPajak'] ?? '') == '1' ? 'selected' : '' }}>1 - PEMBELIAN BKP</option>
                                            <option value="2" {{ ($dataDetail['kodeKenaPajak'] ?? '') == '2' ? 'selected' : '' }}>2 - PENERIMA JASA JKP</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-pernyataan" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color:#001f3f;">Pernyataan & Penandatangan</div>
                        <div class="card-body">
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
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header fw-bold d-flex align-items-center px-3 py-2 bg-light text-dark" style="font-size:13px;">
                            <span>Data Kemasan</span>
                            <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" style="margin-left:auto !important;" id="btn-add-kemasan" title="Tambah Kemasan"><i class="fas fa-plus"></i> Tambah Kemasan</button>
                        </div>
                        <div class="card-body p-0">
                            @php
                                $kemasans = $dataDetail['kemasan'] ?? [];
                                if (empty($kemasans)) {
                                    $kemasans[] = ['jumlahKemasan' => $header->qty_karton ?? "", 'kodeJenisKemasan' => 'CT', 'merkKemasan' => '-'];
                                }
                            @endphp
                            <table class="table table-sm table-bordered mb-0" id="table-kemasan">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="20%">Jumlah Kemasan</th>
                                        <th width="45%">Jenis Kemasan</th>
                                        <th width="25%">Merek</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-kemasan">
                                    @foreach($kemasans as $index => $kemasan)
                                    <tr>
                                        <td><input type="text" inputmode="decimal" name="kemasan[{{ $index }}][jumlahKemasan]" class="form-control form-control-sm input-decimal" value="{{ $kemasan['jumlahKemasan'] ?? $kemasan['jumlah'] ?? "" }}" placeholder="contoh: 10"></td>
                                        <td>
                                            <select name="kemasan[{{ $index }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih --</option>
                                                @foreach($listJenisKemasan as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kemasan['kodeJenisKemasan'] ?? $kemasan['kode'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" name="kemasan[{{ $index }}][merkKemasan]" class="form-control form-control-sm" value="{{ $kemasan['merkKemasan'] ?? $kemasan['merk'] ?? '-' }}"></td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kemasan" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header fw-bold d-flex align-items-center px-3 py-2 bg-light text-dark" style="font-size:13px;">
                            <span>Data Kontainer / Peti Kemas</span>
                            <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" style="margin-left:auto !important;" id="btn-add-kontainer" title="Tambah Kontainer"><i class="fas fa-plus"></i> Tambah Kontainer</button>
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
                                        <th width="20%">Jenis</th>
                                        <th width="25%">Tipe</th>
                                        <th width="15%">Ukuran</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-kontainer">
                                    @foreach($kontainers as $kIndex => $kont)
                                    <tr>
                                        <td><input type="text" name="kontainer[{{ $kIndex }}][nomorKontainer]" class="form-control form-control-sm text-uppercase" value="{{ $kont['nomorKontainer'] ?? '' }}" placeholder="Contoh: TGHU1234567"></td>
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

        // BMT Interactions
        $(document).on('change', '.bmt-toggle', function() {
            if($(this).is(':checked')) {
                $(this).closest('.mt-3').find('.bmt-container').slideDown();
            } else {
                $(this).closest('.mt-3').find('.bmt-container').slideUp();
            }
        });

        // Initialize BMT toggles on load
        $('.bmt-container').each(function() {
            let hasData = false;
            $(this).find('input[type="number"]').each(function() {
                if($(this).val() && parseFloat($(this).val()) > 0) hasData = true;
            });
            if(hasData) {
                $(this).show();
                $(this).closest('.mt-3').find('.bmt-toggle').prop('checked', true);
            }
        });

        $(document).on('change', '.bmt-jenis-tarif', function() {
            let val = $(this).val();
            let container = $(this).closest('.bmt-row').find('.bmt-inputs-container');
            if (val === '1') {
                container.find('.bmt-advalorum-inputs').show();
                container.find('.bmt-spesifik-inputs').hide();
                container.find('.bmt-spesifik-inputs input').val('');
            } else {
                container.find('.bmt-advalorum-inputs').hide();
                container.find('.bmt-spesifik-inputs').show();
                container.find('.bmt-advalorum-inputs input').val('');
            }
        });

        // ── Filter input angka & desimal ──────────────────────────────────
        // Hanya izinkan angka (0-9) dan satu titik desimal
        $(document).on('input', '.input-decimal', function () {
            let val = $(this).val();
            // Hapus semua karakter selain angka dan titik
            val = val.replace(/[^0-9.]/g, '');
            // Cegah lebih dari satu titik
            const parts = val.split('.');
            if (parts.length > 2) val = parts[0] + '.' + parts.slice(1).join('');
            $(this).val(val);
        });
        // Saat keluar field: trim titik di awal/akhir, kosong = biarkan kosong
        $(document).on('blur', '.input-decimal', function () {
            let val = $(this).val().replace(/^\./, '').replace(/\.$/, '');
            $(this).val(val);
        });
        // Blokir karakter non-numerik saat keypress (e, E, +, -)
        $(document).on('keypress', '.input-decimal', function (e) {
            const allowed = /[0-9.]/;
            const char = String.fromCharCode(e.which);
            if (!allowed.test(char)) e.preventDefault();
            // Cegah titik ke-2
            if (char === '.' && $(this).val().includes('.')) e.preventDefault();
        });
        // ─────────────────────────────────────────────────────────────────

        $('#ceisaTab a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });

        // Kalkulasi pungutan sudah digantikan oleh calculateTotals() di bawah

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
                    <td><select name="dok[${dokIndex}][kode]" class="form-control form-control-sm select2bs4-dynamic">${optDokumenHtml}</select></td>
                    <td><input type="text" name="dok[${dokIndex}][nomor]" class="form-control form-control-sm" value=""></td>
                    <td><input type="date" name="dok[${dokIndex}][tgl]" class="form-control form-control-sm" value=""></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-dok"><i class="fas fa-trash-alt"></i></button></td>
                </tr>
            `;
            $('#tbody-dokumen').append(htmlTr);
            $(`select[name="dok[${dokIndex}][kode]"]`).select2({ theme: 'bootstrap4', width: '100%', tags: true });
            dokIndex++;
        });
        $(document).on('click', '.btn-hapus-dok', function() {
            if ($('#tbody-dokumen tr').length > 1) { $(this).closest('tr').remove(); }
            else { Swal.fire('Info', 'Minimal sisakan 1 baris.', 'info'); }
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


        function validasiBC23() {
            let errors = [];
            let firstTab = null;

            $('#form-edit-ceisa').find('input, select, textarea').each(function() {
                let el = $(this);

                if (el.is(':disabled') || el.is('[readonly]') || el.attr('type') === 'hidden' || el.attr('type') === 'button' || el.attr('type') === 'submit') {
                    return;
                }

                // Skip BMT inputs as they are optional
                if (el.closest('.bmt-container').length) {
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

            // let hargaOk = false;
            // $('input[name$="[hargaPenyerahan]"]').each(function() {
            //     if ($(this).val() && parseFloat($(this).val()) > 0) hargaOk = true;
            // });
            // if (!hargaOk) {
            //     errors.push('Harga Penyerahan/Jual (minimal 1 barang > 0)');
            //     if (!firstTab) firstTab = '#tab-header';
            // }

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

             if ($('#ndpbm').val() === '' || $('#ndpbm').val() === '0') {
                Swal.fire({
                    title: 'Gagal!',
                    text: 'NDPBM tidak boleh kosong.',
                    icon: 'error'
                });
                return;
            }


            // if (!validasiBC23()) return;

            let invalidBarang = false;
            let invalidItemNames = [];

            // Validasi CIF dan Netto harus > 0
            $('input[name$="[cif]"], input[name$="[netto]"]').each(function() {
                let name = $(this).attr('name');
                if (name && name.indexOf('barang[') !== -1) {
                    let val = parseFloat($(this).val()) || 0;
                    if (val <= 0) {
                        invalidBarang = true;
                        let label = $(this).closest('.form-group').find('label').text() || name;
                        invalidItemNames.push(label);
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                }
            });

            // if (invalidBarang) {
            //     $('#barang-tab').tab('show');
            //     Swal.fire({
            //         title: 'Validasi Gagal!',
            //         html: 'Terdapat item barang yang nilai <b>Harga CIF</b> atau <b>Berat Bersih (Netto)</b>-nya masih 0. <br><br>Harus lebih dari 0.',
            //         icon: 'warning',
            //         confirmButtonColor: '#003366'
            //     });
            //     return false;
            // }


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


        function calculateTotals() {
            let totalHarga = 0;
            let totalNetto = 0;
            let totalVolume = 0;

            let dataPungutan = {
                'BM':  { '3': 0, '5': 0, '6': 0, '7': 0 },
                'PPN': { '3': 0, '5': 0, '6': 0, '7': 0 },
                'PPH': { '3': 0, '5': 0, '6': 0, '7': 0 },
                'PPNBM': { '3': 0, '5': 0, '6': 0, '7': 0 },
                'BMAD': { '3': 0, '5': 0, '6': 0, '7': 0 },
                'BMTP': { '3': 0, '5': 0, '6': 0, '7': 0 },
                'BMI': { '3': 0, '5': 0, '6': 0, '7': 0 },
                'BMP': { '3': 0, '5': 0, '6': 0, '7': 0 },
                'CUKAI': { '3': 0, '5': 0, '6': 0, '7': 0 }
            };

            $('#accordionBarang .card').each(function() {
                let row = $(this);

                let valPabean = 0;
                let inputCifRupiah = row.find('input[name$="[cifRupiah]"]');
                let inputCif = row.find('input[name$="[cif]"]');
                if (inputCifRupiah.length > 0) {
                    valPabean = parseFloat(inputCifRupiah.val().replace(/,/g, '')) || 0;
                }
                if (valPabean === 0 && inputCif.length > 0) {
                    valPabean = parseFloat(inputCif.val().replace(/,/g, '')) || 0;
                }

                // Sum Netto
                let inputNetto = row.find('input[name$="[netto]"]');
                if (inputNetto.length > 0) {
                    totalNetto += parseFloat(inputNetto.val().replace(/,/g, '')) || 0;
                }

                // Sum Volume
                let inputVolume = row.find('input[name$="[volume]"]');
                if (inputVolume.length > 0) {
                    totalVolume += parseFloat(inputVolume.val().replace(/,/g, '')) || 0;
                }

                // --- Kalkulasi pungutan per baris tarif ---
                let totalBeaAmount = 0;

                // Pass 1: Hitung BM, BMT, Cukai
                row.find('input[name*="[kodeJenisPungutan]"]').each(function() {
                    let jenisInput = $(this);
                    let jenis = jenisInput.val();
                    if (['BM', 'BMAD', 'BMTP', 'BMI', 'BMP', 'CUKAI'].includes(jenis)) {
                        let container = jenisInput.closest('tr');
                        if (container.length === 0) container = jenisInput.closest('.bmt-row');
                        if (container.length === 0) return;

                        let tarif = parseFloat(container.find('input[name*="[tarif]"]').first().val()) || 0;
                        let fas = container.find('select[name*="[kodeFasilitasTarif]"]').val() || '3';
                        let amount = 0;

                        // Check specific for BMT
                        let jenisTarifSelect = container.find('select[name*="[kodeJenisTarif]"]');
                        if (jenisTarifSelect.length > 0 && jenisTarifSelect.val() === '2') {
                            let satuan = parseFloat(container.find('input[name*="[jumlahSatuan]"]').val()) || 0;
                            let tarifSpesifik = parseFloat(container.find('input[name*="[tarifSpesifik]"]').val()) || 0;
                            amount = satuan * tarifSpesifik;
                        } else {
                            amount = valPabean * (tarif / 100);
                        }

                        if (dataPungutan[jenis] && dataPungutan[jenis][fas] !== undefined) {
                            dataPungutan[jenis][fas] += amount;
                        }
                        totalBeaAmount += amount;
                    }
                });

                // Pass 2: Hitung PDRI (PPN, PPNBM, PPH)
                row.find('input[name*="[kodeJenisPungutan]"]').each(function() {
                    let jenisInput = $(this);
                    let jenis = jenisInput.val();
                    if (['PPN', 'PPNBM', 'PPH'].includes(jenis)) {
                        let container = jenisInput.closest('tr');
                        if (container.length === 0) return;
                        let tarif = parseFloat(container.find('input[name*="[tarif]"]').val()) || 0;
                        let fas = container.find('select[name*="[kodeFasilitasTarif]"]').val() || '3';
                        let amount = (valPabean + totalBeaAmount) * (tarif / 100);

                        if (dataPungutan[jenis] && dataPungutan[jenis][fas] !== undefined) {
                            dataPungutan[jenis][fas] += amount;
                        }
                    }
                });
            });

            let formatDecimal = function(num) {
                if (num % 1 === 0) return num.toString() + '.0000';
                return num.toFixed(4);
            };

            let formatIdr = function(num) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(num);
            };

            $('#totalNetto').val(formatDecimal(totalNetto));
            $('#totalVolume').val(formatDecimal(totalVolume));

            // Auto-correct bruto agar tidak < netto
            // let inputBruto = $('input[name="bruto"]');
            // let currentBruto = parseFloat(inputBruto.val().replace(/,/g, '')) || 0;
            // if (currentBruto < totalNetto) {
            //     inputBruto.val(formatDecimal(totalNetto));
            // }

            // Update tabel UI pungutan
            let hiddenContainer = $('#hidden-pungutan-container');
            hiddenContainer.empty();
            let arrayIndex = 0;

            let total3 = 0, total5 = 0, total6 = 0, total7 = 0;

            ['BM', 'PPN', 'PPNBM', 'PPH'].forEach(function(jenis) {
                let idPrefix = '#text-' + jenis.toLowerCase() + '-';
                if ($(idPrefix + 'ditangguhkan').length) {
                    $(idPrefix + 'ditangguhkan').text(formatIdr(dataPungutan[jenis]['3']));
                    $(idPrefix + 'dibebaskan').text(formatIdr(dataPungutan[jenis]['5']));
                    $(idPrefix + 'tidak-dipungut').text(formatIdr(dataPungutan[jenis]['6']));
                    $(idPrefix + 'sudah-dilunasi').text(formatIdr(dataPungutan[jenis]['7']));
                }

                total3 += dataPungutan[jenis]['3'] || 0;
                total5 += dataPungutan[jenis]['5'] || 0;
                total6 += dataPungutan[jenis]['6'] || 0;
                total7 += dataPungutan[jenis]['7'] || 0;

                for (let fas in dataPungutan[jenis]) {
                    if (dataPungutan[jenis][fas] > 0) {
                        hiddenContainer.append(`<input type="hidden" name="pungutan[${arrayIndex}][kodeFasilitasTarif]" value="${fas}">`);
                        hiddenContainer.append(`<input type="hidden" name="pungutan[${arrayIndex}][kodeJenisPungutan]" value="${jenis}">`);
                        hiddenContainer.append(`<input type="hidden" name="pungutan[${arrayIndex}][nilaiPungutan]" value="${formatDecimal(dataPungutan[jenis][fas])}">`);
                        arrayIndex++;
                    }
                }
            });

            $('#text-total-ditangguhkan').text(formatIdr(total3));
            $('#text-total-dibebaskan').text(formatIdr(total5));
            $('#text-total-tidak-dipungut').text(formatIdr(total6));
            $('#text-total-sudah-dilunasi').text(formatIdr(total7));
        }

        $(document).on('input change',
            'input[name$="[cif]"], input[name$="[cifRupiah]"], input[name$="[netto]"], input[name$="[volume]"], ' +
            'select[name*="[kodeFasilitasTarif]"], select[name*="[kodeJenisTarif]"], ' +
            'input[name*="[barangTarif]"][name$="[tarif]"], input[name*="[barangTarif]"][name$="[jumlahSatuan]"], input[name*="[barangTarif]"][name$="[tarifSpesifik]"]',
            function() {
                calculateTotals();
            }
        );

        // Trigger kalkulasi awal untuk sinkronisasi nilai
        calculateTotals();



        // Filter tabel pungutan
        $('.column-search').on('keyup', function() {
            $('#tab-pungutan tbody tr').show(); // Reset semua baris

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

    function kalkulasiNilaiPabean() {
        let ndpbm = parseFloat($('#ndpbm').val()) || 0;
        let totalCif = parseFloat($('#total_cif').val()) || 0;
        let nilaiPabeanRupiah = ndpbm * totalCif;

        $('#nilai_pabean').val(nilaiPabeanRupiah.toFixed(2));
    }
</script>
@endsection
