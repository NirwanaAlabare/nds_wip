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

<div class="container-fluid">
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">
            <i class="fas fa-edit"></i> BC 2.6.1 - PEMBERITAHUAN PENGELUARAN BARANG DARI TEMPAT BERIKAT DENGAN JAMINAN
        </h5>
    </div>

    <form action="{{ route('dokumen-pabean-update_draft_batch_bc261', $batch_id) }}" method="POST" id="form-edit-ceisa">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="alert alert-warning py-2 mb-4">
                <strong>Mode Batch (BC 2.6.1)</strong><br>
                <strong>No. Transaksi Gabungan:</strong> {{ $batch_id }} <br>
                {{ $header->supplier ?? '-' }}
                <input type="hidden" name="bpbno_int" value="{{ $header->bppbno_int }}">
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
                <li class="nav-item"><a class="nav-link" id="jaminan-tab" data-toggle="tab" href="#tab-jaminan" role="tab"><i class="fas fa-shield-alt"></i> Jaminan</a></li>
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
                                        <label class="text-sm">Nomor Aju</label>
                                        <input type="text" name="nomorAju" class="form-control form-control-sm fw-bold" value="{{ $nomorAju }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Kantor Pabean</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="text-sm">Kantor Pabean Pengawasan</label>
                                        <select name="kantorPabean" class="form-control form-control-sm select2bs4">
                                            <option value="">Pilih Kantor Pabean</option>
                                             @foreach($kantorList as $kode => $nama)
                                                <option value="{{ $kode }}" {{ ($dataDetail['kantorPabean'] ?? '') == $kode ? 'selected' : '' }}>
                                                    {{ $nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Keterangan Lain</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="text-sm">Tujuan Pengiriman</label>
                                        <select name="tujuanPengiriman" class="form-control form-control-sm select2bs4">
                                            <option value="">-- Tujuan Pengiriman --</option>
                                            @foreach($listTujuanPengiriman as $k => $v)
                                                <option value="{{ $k }}" {{ ($dataDetail['tujuanPengiriman'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ strtoupper($v) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="tab-pane fade" id="tab-entitas" role="tabpanel">
                    <div class="row">

                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Pengusaha TPB (Asal)</div>
                                <div class="card-body">
                                    @php $entTpb = $dataDetail['entitas']['tpb'] ?? []; @endphp
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nomor Identitas (NPWP)</label>
                                        <input type="text" name="entitas[tpb][nomorIdentitas]" class="form-control form-control-sm" value="{{ $entTpb['nomorIdentitas'] ?? '0745406926444000000000' }}" placeholder="NPWP 15/16 Digit">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">NITKU</label>
                                        <input type="text" name="entitas[tpb][nitku]" class="form-control form-control-sm" value="{{ $entTpb['nitku'] ?? '' }}" placeholder="NITKU">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nama Pengusaha TPB</label>
                                        <input type="text" name="entitas[tpb][namaEntitas]" class="form-control form-control-sm" value="{{ $entTpb['namaEntitas'] ?? 'NIRWANA ALABARE GARMENT' }}" placeholder="Nama Perusahaan">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Alamat</label>
                                        <textarea name="entitas[tpb][alamatEntitas]" class="form-control form-control-sm" rows="2" placeholder="Alamat Perusahaan">{{ $entTpb['alamatEntitas'] ?? 'JL. RAYA RANCAEKEK MAJALAYA NO. 289 RT. 001 RW. 007' }}</textarea>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nomor Ijin TPB</label>
                                        <input type="text" name="entitas[tpb][nomorIjinEntitas]" class="form-control form-control-sm" value="{{ $entTpb['nomorIjinEntitas'] ?? '16/MK/WBC.09/2026' }}" placeholder="Nomor Ijin">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Tanggal Ijin TPB</label>
                                        <input type="date" name="entitas[tpb][tanggalIjinEntitas]" class="form-control form-control-sm" value="{{ $entTpb['tanggalIjinEntitas'] ?? '2026-01-20' }}">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small mb-0">NIB</label>
                                        <input type="text" name="entitas[tpb][nibEntitas]" class="form-control form-control-sm" value="{{ $entTpb['nibEntitas'] ?? '0220103231143' }}" placeholder="NIB">
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Penerima Barang / Pembeli</div>
                                <div class="card-body">
                                    @php $entPenerima = $dataDetail['entitas']['penerima'] ?? []; @endphp
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nomor Identitas (NPWP/KTP)</label>
                                        <input type="text" name="entitas[penerima][nomorIdentitas]" class="form-control form-control-sm" value="{{ $entPenerima['nomorIdentitas'] ?? ($header->npwp_supplier ?? '') }}" placeholder="NPWP / KTP">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">NITKU</label>
                                        <input type="text" name="entitas[penerima][nitku]" class="form-control form-control-sm" value="{{ $entPenerima['nitku'] ?? '' }}" placeholder="NITKU">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nama Penerima</label>
                                        <input type="text" name="entitas[penerima][namaEntitas]" class="form-control form-control-sm" value="{{ $entPenerima['namaEntitas'] ?? ($header->supplier ?? '') }}" placeholder="Nama Penerima">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Alamat Penerima</label>
                                        <textarea name="entitas[penerima][alamatEntitas]" class="form-control form-control-sm" rows="3" placeholder="Alamat Penerima">{{ $entPenerima['alamatEntitas'] ?? ($header->alamat_supplier ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Pemilik Barang</div>
                                <div class="card-body">
                                    @php $entPemilik = $dataDetail['entitas']['pemilik'] ?? []; @endphp
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nomor Identitas (NPWP/KTP)</label>
                                        <input type="text" name="entitas[pemilik][nomorIdentitas]" class="form-control form-control-sm" value="{{ $entPemilik['nomorIdentitas'] ?? ($header->npwp_supplier ?? '') }}" placeholder="NPWP / KTP">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">NITKU</label>
                                        <input type="text" name="entitas[pemilik][nitku]" class="form-control form-control-sm" value="{{ $entPemilik['nitku'] ?? '' }}" placeholder="NITKU">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nama Pemilik</label>
                                        <input type="text" name="entitas[pemilik][namaEntitas]" class="form-control form-control-sm" value="{{ $entPemilik['namaEntitas'] ?? ($header->supplier ?? '') }}" placeholder="Nama Pemilik">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Alamat Pemilik</label>
                                        <textarea name="entitas[pemilik][alamatEntitas]" class="form-control form-control-sm" rows="3" placeholder="Alamat Pemilik">{{ $entPemilik['alamatEntitas'] ?? ($header->alamat_supplier ?? '') }}</textarea>
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
                            <button type="button" class="btn btn-sm btn-light py-0 px-2" style="margin-left: auto !important;" id="btn-add-dok" title="Tambah Dokumen"><i class="fas fa-plus text-primary"></i> Tambah Dokumen</button>
                        </div>
                        <div class="card-body p-0" style="overflow-x: auto;">
                            <table class="table table-sm table-bordered mb-0" id="table-dokumen" style="min-width: 800px;">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="5%">Seri</th>
                                        <th width="25%">Jenis Dokumen</th>
                                        <th width="22%">Nomor</th>
                                        <th width="15%">Tanggal</th>
                                        <th width="12%">Fasilitas</th>
                                        <th width="13%">Kode Izin</th>
                                        <th width="8%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-dokumen">
                                    @foreach($dokumens as $index => $dok)
                                        <tr>
                                            <td class="text-center align-middle">
                                                {{ $index + 1 }}
                                                <input type="hidden" name="dok[{{ $index }}][seriDokumen]" value="{{ $index + 1 }}">
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
                                                <input type="text" name="dok[{{ $index }}][nomor]" class="form-control form-control-sm" value="{{ $dok['nomorDokumen'] ?? $dok['nomor'] ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="date" name="dok[{{ $index }}][tgl]" class="form-control form-control-sm" value="{{ $dok['tanggalDokumen'] ?? $dok['tgl'] ?? '' }}">
                                            </td>
                                            <td><input type="text" name="dok[{{ $index }}][fasilitas]" class="form-control form-control-sm" value="{{ $dok['fasilitas'] ?? '' }}" placeholder="Kode Fasilitas"></td>
                                            <td><input type="text" name="dok[{{ $index }}][izin]" class="form-control form-control-sm" value="{{ $dok['izin'] ?? '' }}" placeholder="Kode Izin"></td>
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
                        <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Pengangkutan</div>
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <label class="small mb-1">Cara Pengangkutan</label>
                                @php $sarkutFirst = $pengangkuts[0] ?? []; @endphp
                                <input type="hidden" name="pengangkut[0][seriPengangkut]" value="1">
                                <input type="hidden" name="pengangkut[0][kodeBendera]" value="ID">
                                <select name="pengangkut[0][kodeCaraAngkut]" class="form-control form-control-sm select2bs4">
                                    <option value="">Pilih Cara Angkut</option>
                                    @foreach($listCaraAngkut as $k => $v)
                                        <option value="{{ $k }}" {{ ($pengangkuts[0]['kodeCaraAngkut'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ strtoupper($v) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="tab-pane fade" id="tab-kemasan" role="tabpanel">

                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header fw-bold d-flex align-items-center px-3 py-2 bg-light text-dark" style="font-size:13px;">
                            <span>Data Kemasan</span>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" style="margin-left: auto !important;" id="btn-add-kemasan" title="Tambah Kemasan"><i class="fas fa-plus"></i> Tambah Kemasan</button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0" id="table-kemasan">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="5%">Seri</th>
                                        <th width="18%">Jumlah</th>
                                        <th width="38%">Jenis Kemasan</th>
                                        <th width="29%">Merek</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-kemasan">
                                    @foreach($kemasans as $kIndex => $kemasan)
                                    <tr>
                                        <td class="text-center align-middle">
                                            {{ $kIndex + 1 }}
                                            <input type="hidden" name="kemasan[{{ $kIndex }}][seriKemasan]" value="{{ $kIndex + 1 }}">
                                        </td>
                                        <td><input type="number" step="any" name="kemasan[{{ $kIndex }}][jumlahKemasan]" class="form-control form-control-sm" value="{{ $kemasan['jumlahKemasan'] ?? 0 }}"></td>
                                        <td>
                                            <select name="kemasan[{{ $kIndex }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih Jenis Kemasan --</option>
                                                @foreach($listJenisKemasan as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kemasan['kodeJenisKemasan'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" name="kemasan[{{ $kIndex }}][merkKemasan]" class="form-control form-control-sm" value="{{ $kemasan['merkKemasan'] ?? '-' }}"></td>
                                        <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kemasan"><i class="fas fa-trash-alt"></i></button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>


                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header fw-bold d-flex align-items-center px-3 py-2 bg-light text-dark" style="font-size:13px;">
                            <span>Data Petikemas (Kontainer)</span>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" style="margin-left: auto !important;" id="btn-add-kontainer" title="Tambah Kontainer"><i class="fas fa-plus"></i> Tambah Petikemas</button>
                        </div>
                        <div class="card-body p-0">
                            @php
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
                                        <th width="25%">Nomor Kontainer</th>
                                        <th width="15%">Ukuran</th>
                                        <th width="20%">Jenis</th>
                                        <th width="20%">Tipe</th>
                                        <th width="12%">Jenis Muatan</th>
                                        <th width="8%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-kontainer">
                                    @foreach($kontainers as $cIndex => $kont)
                                    <tr>
                                        <input type="hidden" name="kontainer[{{ $cIndex }}][seriKontainer]" value="{{ $cIndex + 1 }}">
                                        <td><input type="text" name="kontainer[{{ $cIndex }}][nomorKontainer]" class="form-control form-control-sm text-uppercase" value="{{ $kont['nomorKontainer'] ?? '' }}"></td>
                                        <td>
                                            <select name="kontainer[{{ $cIndex }}][kodeUkuranKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih Ukuran --</option>
                                                @foreach($listUkuranKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kont['kodeUkuranKontainer'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="kontainer[{{ $cIndex }}][kodeJenisKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih Jenis --</option>
                                                @foreach($listJenisKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kont['kodeJenisKontainer'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="kontainer[{{ $cIndex }}][kodeTipeKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih Tipe --</option>
                                                @foreach($listTipeKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kont['kodeTipeKontainer'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" name="kontainer[{{ $cIndex }}][jenisMuatan]" class="form-control form-control-sm" value="{{ $kont['jenisMuatan'] ?? '' }}"></td>
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

                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Harga</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label>Jenis Valuta</label>
                                        <select name="valuta" class="form-control form-control-sm select2bs4" id="kode_valuta">
                                            <option value="">-- Pilih Valuta --</option>
                                            @foreach($listValuta as $kVal => $nVal)
                                                <option value="{{ $kVal }}" {{ ($dataDetail['valuta'] ?? 'IDR') == $kVal ? 'selected' : '' }}>{{ $kVal }} - {{ $nVal }}</option>
                                            @endforeach
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
                                        <label>Nilai CIF</label>
                                        <input type="number" step="any" name="nilaiCif" class="form-control form-control-sm" value="{{ $dataDetail['nilaiCif'] ?? '0.00' }}" id="total_cif">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>Nilai Pabean</label>
                                        <input type="number" step="any" name="nilaiPabean" class="form-control form-control-sm" value="{{ $dataDetail['nilaiPabean'] ?? '0.00' }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Berat</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label>Berat Kotor (KGM)</label>
                                        <input type="number" step="any" name="bruto" class="form-control form-control-sm" value="{{ $dataDetail['bruto'] ?? '0.00' }}">
                                        <small class="text-muted" style="font-size: 10px;">Berat Kotor harus lebih besar dari 0</small>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Berat Bersih (KGM)</label>
                                        <input type="number" step="any" name="netto" class="form-control form-control-sm" value="{{ $dataDetail['netto'] ?? '0.00' }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>


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
                                @endphp

                                <div class="card mb-2 border">
                                    <div class="card-header py-2 btn-collapse-barang" data-target="#collapseBarang{{ $index }}" style="cursor: pointer; background-color: #001f3f;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-bold text-white" style="font-size: 13px;">
                                                {{ $item->goods_code ?? $item->id_item }} - {{ $item->itemdesc }}
                                            </div>
                                            <i class="fas fa-chevron-down text-white icon-collapse"></i>
                                        </div>
                                    </div>

                                    <div id="collapseBarang{{ $index }}" class="collapse {{ $index == 0 ? 'show' : '' }}" data-parent="#accordionBarang">
                                        <div class="card-body py-3 px-3 bg-white">


                                            <input type="hidden" name="barang[{{ $index }}][seriBarang]" value="{{ $index + 1 }}">

                                            <div class="row">
                                                <!-- KOLOM KIRI: JENIS -->
                                                <div class="col-md-4">
                                                    <div class="card shadow-sm mb-3 border">
                                                        <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Jenis</div>
                                                        <div class="card-body">
                                                            <div class="form-group mb-2">
                                                                <label>Seri</label>
                                                                <input type="text" class="form-control form-control-sm" value="{{ $index + 1 }}" readonly>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Pos Tarif/HS <i class="far fa-question-circle text-primary"></i></label>
                                                                <input type="text" name="barang[{{ $index }}][posTarif]" class="form-control form-control-sm" value="{{ $draftItem['posTarif'] ?? '' }}" placeholder="Pos Tarif/HS">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Kode Barang</label>
                                                                <input type="text" name="barang[{{ $index }}][kodeBarang]" class="form-control form-control-sm" value="{{ $draftItem['kodeBarang'] ?? $item->goods_code ?? $item->id_item ?? '-' }}">
                                                                <input type="text" name="barang[{{ $index }}][idItem]" class="form-control form-control-sm hidden" value="{{ $item->id_item ?? '' }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label class="d-flex justify-content-between align-items-center mb-1">
                                                                    Uraian Jenis Barang
                                                                    <button type="button" class="btn btn-primary btn-sm py-0" style="font-size: 10px;">Sesuai Hs</button>
                                                                </label>
                                                                <textarea name="barang[{{ $index }}][uraian]" class="form-control form-control-sm " rows="3">{{ $draftItem['uraian'] ?? $item->itemdesc ?? '' }}</textarea>
                                                                <small style="font-size: 10px;">Uraian kosong</small>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Merek</label>
                                                                <input type="text" name="barang[{{ $index }}][merk]" class="form-control form-control-sm" value="{{ $draftItem['merk'] ?? '-' }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Tipe</label>
                                                                <input type="text" name="barang[{{ $index }}][tipe]" class="form-control form-control-sm" value="{{ $draftItem['tipe'] ?? '-' }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Ukuran</label>
                                                                <input type="text" name="barang[{{ $index }}][ukuran]" class="form-control form-control-sm" value="{{ $draftItem['ukuran'] ?? '-' }}">
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label>Spesifikasi Lain</label>
                                                                <input type="text" name="barang[{{ $index }}][spesifikasiLain]" class="form-control form-control-sm" value="{{ $draftItem['spesifikasiLain'] ?? '-' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- KOLOM TENGAH: KETERANGAN LAINNYA & HARGA -->
                                                <div class="col-md-4">
                                                    <div class="card shadow-sm mb-3 border">
                                                        <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Keterangan Lainnya</div>
                                                        <div class="card-body">
                                                            <div class="form-group mb-2">
                                                                <label>Negara Asal Barang</label>
                                                                <select name="barang[{{ $index }}][kodeNegaraAsal]" class="form-control form-control-sm text-danger border-danger select2bs4">
                                                                    <option value="">Pilih Negara Asal Barang</option>
                                                                    @include('export-import.dokumen-pabean.options_negara', ['selected' => $draftItem['kodeNegaraAsal'] ?? ''])
                                                                </select>
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label>Daerah Asal Barang</label>
                                                                <select name="barang[{{ $index }}][kodeAsalBarang]" class="form-control form-control-sm text-danger border-danger select2bs4">
                                                                    <option value="">Pilih Daerah Asal Barang</option>
                                                                    @include('export-import.dokumen-pabean.options_daerah', ['selected' => $draftItem['kodeAsalBarang'] ?? ''])
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="card shadow-sm mb-3 border">
                                                        <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Harga</div>
                                                        <div class="card-body">
                                                            <div class="form-group mb-2">
                                                                <label>Nilai CIF</label>
                                                                <input type="number" step="any" name="barang[{{ $index }}][cif]" class="form-control form-control-sm input-cif-barang" value="{{ $draftItem['cif'] ?? '0.00' }}">
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label>Nilai CIF</label>
                                                                <input type="number" step="any" name="barang[{{ $index }}][hargaPenyerahan]" class="form-control form-control-sm" value="{{ $draftItem['hargaPenyerahan'] ?? (float)($item->qty * $item->price) }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- KOLOM KANAN: JUMLAH & BERAT & DOKUMEN IZIN -->
                                                <div class="col-md-4">
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
                                                                        <select name="barang[{{ $index }}][kodeSatuanBarang]" class="form-control form-control-sm select2bs4">
                                                                            <option value="">-- Satuan --</option>
                                                                            @foreach($listSatuanBarang as $k => $v)
                                                                                <option value="{{ $k }}" {{ ($draftItem['kodeSatuanBarang'] ?? $item->unit ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
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
                                                                        <select name="barang[{{ $index }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4">
                                                                            <option value="">-- Kemasan --</option>
                                                                            @foreach($listJenisKemasan as $k => $v)
                                                                                <option value="{{ $k }}" {{ ($draftItem['kodeJenisKemasan'] ?? 'CT') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                            @endforeach
                                                                        </select>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label>Berat Bersih (Kg)</label>
                                                                <input type="number" step="any" name="barang[{{ $index }}][netto]" class="form-control form-control-sm " value="{{ $draftItem['netto'] ?? (float) ($item->nw ?? $item->netto ?? 0) }}">

                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="card shadow-sm mb-3 border d-none">
                                                        <div class="card-header fw-bold d-flex justify-content-between align-items-center bg-light text-dark px-3 py-2" style="font-size:13px;">
                                                            <span>Dokumen Izin</span>
                                                            <button type="button" class="btn btn-sm btn-primary py-0 px-2 btn-add-dokumen-barang" data-itemidx="{{ $index }}"><i class="fas fa-plus"></i> Tambah</button>
                                                        </div>
                                                        <div class="card-body p-2 text-center" style="min-height: 120px;">
                                                            <div class="text-muted mt-4">
                                                                <i class="fas fa-inbox fa-2x mb-2 text-light"></i><br>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- BOTTOM ROW: BAHAN BAKU IMPOR & LOKAL -->
                                            <div class="row d-none">
                                                <div class="col-md-6">
                                                    <div class="card shadow-sm mb-3 border">
                                                        <div class="card-header fw-bold d-flex justify-content-between align-items-center bg-light text-dark px-3 py-2" style="font-size:13px;">
                                                            <span>Bahan Baku Impor</span>
                                                            <div>
                                                                <button type="button" class="btn btn-sm btn-outline-secondary py-0">Urutkan</button>
                                                                <button type="button" class="btn btn-sm btn-primary py-0 btn-add-bahan-baku" data-itemidx="{{ $index }}">Aksi</button>
                                                            </div>
                                                        </div>
                                                        <div class="card-body p-0">
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-bordered mb-0">
                                                                    <thead class="bg-light text-center" style="font-size: 12px;">
                                                                        <tr>
                                                                            <th>Seri</th>
                                                                            <th>HS</th>
                                                                            <th>Uraian</th>
                                                                            <th>Nilai Barang</th>
                                                                            <th>Kode Satuan</th>
                                                                            <th></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="tbody-bahan-baku-{{ $index }}">
                                                                        @php $bahanBaku = $draftItem['bahanBaku'] ?? []; @endphp
                                                                        @if(count($bahanBaku) == 0)
                                                                        <tr>
                                                                            <td colspan="6" class="text-center text-muted py-4">
                                                                                <i class="fas fa-inbox fa-2x mb-2 text-light"></i><br>
                                                                                <small>No Data</small>
                                                                            </td>
                                                                        </tr>
                                                                        @else
                                                                            @foreach($bahanBaku as $bbIndex => $bb)
                                                                            <tr>
                                                                                <input type="hidden" name="barang[{{ $index }}][bahanBaku][{{ $bbIndex }}][seriBahanBaku]" value="{{ $bbIndex + 1 }}">
                                                                                <td class="p-1 text-center align-middle">{{ $bbIndex + 1 }}</td>
                                                                                <td class="p-1"><input type="text" name="barang[{{ $index }}][bahanBaku][{{ $bbIndex }}][hs]" class="form-control form-control-sm" value="{{ $bb['hs'] ?? '' }}" placeholder="HS"></td>
                                                                                <td class="p-1"><input type="text" name="barang[{{ $index }}][bahanBaku][{{ $bbIndex }}][uraian]" class="form-control form-control-sm" value="{{ $bb['uraian'] ?? '' }}" placeholder="Uraian"></td>
                                                                                <td class="p-1"><input type="number" step="any" name="barang[{{ $index }}][bahanBaku][{{ $bbIndex }}][nilaiBarang]" class="form-control form-control-sm" value="{{ $bb['nilaiBarang'] ?? 0 }}"></td>
                                                                                <td class="p-1">
                                                                                    <select name="barang[{{ $index }}][bahanBaku][{{ $bbIndex }}][kodeSatuan]" class="form-control form-control-sm select2bs4">
                                                                                        <option value="">Satuan</option>
                                                                                        @foreach($listSatuanBarang as $k => $v)
                                                                                            <option value="{{ $k }}" {{ ($bb['kodeSatuan'] ?? '') == $k ? 'selected' : '' }}>{{ $k }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </td>
                                                                                <td class="text-center p-1 align-middle"><button type="button" class="btn btn-sm btn-outline-danger btn-hapus-bb"><i class="fas fa-trash-alt"></i></button></td>
                                                                            </tr>
                                                                            @endforeach
                                                                        @endif
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="card shadow-sm mb-3 border">
                                                        <div class="card-header fw-bold d-flex justify-content-between align-items-center bg-light text-dark px-3 py-2" style="font-size:13px;">
                                                            <span>Bahan Baku Lokal</span>
                                                            <div>
                                                                <button type="button" class="btn btn-sm btn-outline-secondary py-0">Urutkan</button>
                                                                <button type="button" class="btn btn-sm btn-primary py-0">Aksi</button>
                                                            </div>
                                                        </div>
                                                        <div class="card-body p-0 text-center" style="min-height: 120px;">
                                                            <div class="text-muted mt-4">
                                                                <i class="fas fa-inbox fa-2x mb-2 text-light"></i><br>
                                                                <small>No Data</small>
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


                <div class="tab-pane fade" id="tab-jaminan" role="tabpanel">
                    <div class="card mb-3">
                        <div class="card-header fw-bold d-flex align-items-center px-3 py-2 text-white" style="font-size:13px; background-color: #001f3f;">
                            <span>Data Jaminan</span>
                            <button type="button" class="btn btn-sm btn-primary py-0 px-2" style="margin-left: auto !important; background-color: #0d6efd; border-color: #0d6efd; color: white;" onclick="addJaminanRow()">
                                <i class="fas fa-plus"></i> Tambah Jaminan
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center" id="table-jaminan">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Jenis Jaminan</th>
                                            <th>Nomor Jaminan</th>
                                            <th>Tgl Jaminan</th>
                                            <th>Nilai Jaminan</th>
                                            <th>Jatuh Tempo</th>
                                            <th>Penjamin</th>
                                            <th>Nomor BPJ</th>
                                            <th>Tgl BPJ</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-jaminan">
                                        @foreach($jaminans as $idx => $j)
                                        <tr>
                                            <td>
                                                <select class="form-control form-control-sm select2bs4" name="jaminan[{{ $idx }}][kodeJenisJaminan]">
                                                    <option value="">Pilih</option>
                                                    @foreach($listJenisJaminan as $kode => $nama)
                                                        <option value="{{ $kode }}" {{ ($j['kodeJenisJaminan'] ?? '') == $kode ? 'selected' : '' }}>{{ $kode }} - {{ $nama }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="text" class="form-control form-control-sm" name="jaminan[{{ $idx }}][nomorJaminan]" value="{{ $j['nomorJaminan'] ?? '' }}"></td>
                                            <td><input type="date" class="form-control form-control-sm" name="jaminan[{{ $idx }}][tanggalJaminan]" value="{{ $j['tanggalJaminan'] ?? '' }}"></td>
                                            <td><input type="number" step="0.01" class="form-control form-control-sm" name="jaminan[{{ $idx }}][nilaiJaminan]" value="{{ $j['nilaiJaminan'] ?? '' }}"></td>
                                            <td><input type="date" class="form-control form-control-sm" name="jaminan[{{ $idx }}][tanggalJatuhTempo]" value="{{ $j['tanggalJatuhTempo'] ?? '' }}"></td>
                                            <td><input type="text" class="form-control form-control-sm" name="jaminan[{{ $idx }}][penjamin]" value="{{ $j['penjamin'] ?? '' }}"></td>
                                            <td><input type="text" class="form-control form-control-sm" name="jaminan[{{ $idx }}][nomorBpj]" value="{{ $j['nomorBpj'] ?? '' }}"></td>
                                            <td><input type="date" class="form-control form-control-sm" name="jaminan[{{ $idx }}][tanggalBpj]" value="{{ $j['tanggalBpj'] ?? '' }}"></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-pungutan" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header fw-bold d-flex align-items-center px-3 py-2 bg-light text-dark" style="font-size:13px;">
                            <span>Pungutan</span>
                            {{-- <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" style="margin-left: auto !important;" id="btn-generate-pungutan">
                                <i class="fas fa-sync-alt"></i> Generate Pungutan
                            </button> --}}
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0" id="table-pungutan">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="50%" class="border-bottom-0">Pungutan</th>
                                            <th width="50%" class="border-bottom-0">Dijaminkan</th>
                                        </tr>
                                        <tr>
                                            <th class="pt-0"><input type="text" class="form-control form-control-sm" disabled></th>
                                            <th class="pt-0"><input type="text" class="form-control form-control-sm" disabled></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-pungutan">
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-5">
                                                <i class="fas fa-inbox fa-3x mb-2" style="color: #f1f1f1;"></i><br>
                                                <small style="color: #ccc;">No Data</small>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="tab-pane fade" id="tab-pernyataan" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #001f3f;">Pernyataan & Penandatangan</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 form-group"><label>Nama TTD</label><input type="text" name="namaTtd" class="form-control form-control-sm" value="{{ $dataDetail['namaTtd'] ?? '' }}" placeholder="Nama Lengkap"></div>
                                <div class="col-md-3 form-group"><label>Jabatan</label><input type="text" name="jabatanTtd" class="form-control form-control-sm" value="{{ $dataDetail['jabatanTtd'] ?? '' }}" placeholder="Jabatan"></div>
                                <div class="col-md-3 form-group"><label>Tempat / Kota TTD</label><input type="text" name="tempatTtd" class="form-control form-control-sm" value="{{ $dataDetail['tempatTtd'] ?? '' }}" placeholder="Kota"></div>
                                <div class="col-md-3 form-group"><label>Tanggal TTD</label><input type="date" name="tanggalTtd" class="form-control form-control-sm" value="{{ $dataDetail['tanggalTtd'] ?? date('Y-m-d') }}"></div>
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
                    <td class="text-center align-middle">${dokIndex + 1}<input type="hidden" name="dok[${dokIndex}][seriDokumen]" value="${dokIndex + 1}"></td>
                    <td><select name="dok[${dokIndex}][kode]" class="form-control form-control-sm select2bs4-dynamic">${optDokumenHtml}</select></td>
                    <td><input type="text" name="dok[${dokIndex}][nomor]" class="form-control form-control-sm"></td>
                    <td><input type="date" name="dok[${dokIndex}][tgl]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="dok[${dokIndex}][fasilitas]" class="form-control form-control-sm" placeholder="Kode Fasilitas"></td>
                    <td><input type="text" name="dok[${dokIndex}][izin]" class="form-control form-control-sm" placeholder="Kode Izin"></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-dok"><i class="fas fa-trash-alt"></i></button></td>
                </tr>`;
            $('#tbody-dokumen').append(htmlTr);
            $(`select[name="dok[${dokIndex}][kode]"]`).select2({ theme: 'bootstrap4', width: '100%' });
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
                    <td><input type="number" step="any" name="kemasan[${kemasanIndex}][jumlahKemasan]" class="form-control form-control-sm" value="0"></td>
                    <td><select name="kemasan[${kemasanIndex}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4-dynamic">${optJenisKemasan}</select></td>
                    <td><input type="text" name="kemasan[${kemasanIndex}][merkKemasan]" class="form-control form-control-sm" value="-"></td>
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
                    <input type="hidden" name="kontainer[${kontainerIndex}][seriKontainer]" value="${kontainerIndex + 1}">
                    <td><input type="text" name="kontainer[${kontainerIndex}][nomorKontainer]" class="form-control form-control-sm text-uppercase"></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeUkuranKontainer]" class="form-control form-control-sm select2bs4-dynamic">${optUkuranKontainer}</select></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeJenisKontainer]" class="form-control form-control-sm select2bs4-dynamic">${optJenisKontainer}</select></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeTipeKontainer]" class="form-control form-control-sm select2bs4-dynamic">${optTipeKontainer}</select></td>
                    <td><input type="text" name="kontainer[${kontainerIndex}][jenisMuatan]" class="form-control form-control-sm"></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kontainer"><i class="fas fa-trash-alt"></i></button></td>
                </tr>`;
            $('#tbody-kontainer').append(htmlTr);
            $(`select[name="kontainer[${kontainerIndex}][kodeUkuranKontainer]"]`).select2({ theme: 'bootstrap4', width: '100%' });
            $(`select[name="kontainer[${kontainerIndex}][kodeJenisKontainer]"]`).select2({ theme: 'bootstrap4', width: '100%' });
            $(`select[name="kontainer[${kontainerIndex}][kodeTipeKontainer]"]`).select2({ theme: 'bootstrap4', width: '100%' });
            kontainerIndex++;
        });
        $(document).on('click', '.btn-hapus-kontainer', function() { $(this).closest('tr').remove(); });

        // ================= BAHAN BAKU LOKAL HANDLER =================
        const optSatuanHtml = `
            <option value="">Satuan</option>
            @foreach($listSatuanBarang as $k => $v) <option value="{{ $k }}">{{ $k }}</option> @endforeach
        `;
        $(document).on('click', '.btn-add-bahan-baku', function() {
            let itemIdx = $(this).data('itemidx');
            let tbody = $(`#tbody-bahan-baku-${itemIdx}`);
            let rowIdx = tbody.find('tr').length;

            let tr = `<tr>
                <input type="hidden" name="barang[${itemIdx}][bahanBaku][${rowIdx}][seriBahanBaku]" value="${rowIdx + 1}">
                <td class="p-1"><input type="text" name="barang[${itemIdx}][bahanBaku][${rowIdx}][hs]" class="form-control form-control-sm" placeholder="HS"></td>
                <td class="p-1">
                    <input type="text" name="barang[${itemIdx}][bahanBaku][${rowIdx}][uraian]" class="form-control form-control-sm mb-1" placeholder="Uraian">
                    <select name="barang[${itemIdx}][bahanBaku][${rowIdx}][kodeSatuan]" class="form-control form-control-sm select2bs4-dynamic">${optSatuanHtml}</select>
                </td>
                <td class="p-1"><input type="number" step="any" name="barang[${itemIdx}][bahanBaku][${rowIdx}][nilaiBarang]" class="form-control form-control-sm" value="0"></td>
                <td class="text-center p-1 align-middle"><button type="button" class="btn btn-sm btn-outline-danger btn-hapus-bb"><i class="fas fa-trash-alt"></i></button></td>
            </tr>`;
            tbody.append(tr);
            $(`select[name="barang[${itemIdx}][bahanBaku][${rowIdx}][kodeSatuan]"]`).select2({ theme: 'bootstrap4', width: '100%' });
        });
        $(document).on('click', '.btn-hapus-bb', function() { $(this).closest('tr').remove(); });

        // ================= PUNGUTAN HANDLER =================
        let pungutanIndex = {{ count($dataDetail['pungutan'] ?? []) }};
        // ================= SYNC NETTO =================
        $('#btn-sync-netto').on('click', function() {
            let totalNetto = 0;
            $('[name$="[netto]"]').each(function() {
                let val = parseFloat($(this).val()) || 0;
                totalNetto += val;
            });
            $('[name="netto"]').val(totalNetto.toFixed(4));
        });

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
            let form = $(this);
            Swal.fire({
                title: 'Simpan Perubahan?',
                text: "Data draft BC 2.6.1 akan diperbarui.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Ya, Simpan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
                    $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method') || 'POST',
                        data: form.serialize(),
                        success: function(response) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message || 'Draft BC 2.6.1 berhasil disimpan.',
                                icon: 'success'
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data.',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        });

        // Add Jaminan Row
        window.addJaminanRow = function() {
            let idx = $('#tbody-jaminan tr').length;
            let options = '<option value="">Pilih</option>';
            @foreach($listJenisJaminan as $kode => $nama)
                options += '<option value="{{ $kode }}">{{ $kode }} - {{ $nama }}</option>';
            @endforeach
            let html = `
                <tr>
                    <td><select class="form-control form-control-sm select2bs4-dynamic" name="jaminan[${idx}][kodeJenisJaminan]">${options}</select></td>
                    <td><input type="text" class="form-control form-control-sm" name="jaminan[${idx}][nomorJaminan]" value=""></td>
                    <td><input type="date" class="form-control form-control-sm" name="jaminan[${idx}][tanggalJaminan]" value=""></td>
                    <td><input type="number" step="0.01" class="form-control form-control-sm" name="jaminan[${idx}][nilaiJaminan]" value=""></td>
                    <td><input type="date" class="form-control form-control-sm" name="jaminan[${idx}][tanggalJatuhTempo]" value=""></td>
                    <td><input type="text" class="form-control form-control-sm" name="jaminan[${idx}][penjamin]" value=""></td>
                    <td><input type="text" class="form-control form-control-sm" name="jaminan[${idx}][nomorBpj]" value=""></td>
                    <td><input type="date" class="form-control form-control-sm" name="jaminan[${idx}][tanggalBpj]" value=""></td>
                    <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
                </tr>
            `;
            $('#tbody-jaminan').append(html);
            $(`select[name="jaminan[${idx}][kodeJenisJaminan]"]`).select2({ theme: 'bootstrap4', width: '100%' });
        }

        window.removeRow = function(btn) {
            $(btn).closest('tr').remove();
        }

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
