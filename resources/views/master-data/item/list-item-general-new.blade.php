@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@include('export-import.invoice._skin')
<style>
/* Lebar kolom daftar item New (10 kolom, terakhir Action) */
@media (min-width: 992px) {
  .nag-skin.dn-daftar-item-new .dn-tabel-daftar th:nth-child(1)  { width: 11%; }
  .nag-skin.dn-daftar-item-new .dn-tabel-daftar th:nth-child(2)  { width: 20%; }
  .nag-skin.dn-daftar-item-new .dn-tabel-daftar th:nth-child(3)  { width: 7%; }
  .nag-skin.dn-daftar-item-new .dn-tabel-daftar th:nth-child(4)  { width: 7%; }
  .nag-skin.dn-daftar-item-new .dn-tabel-daftar th:nth-child(5)  { width: 12%; }
  .nag-skin.dn-daftar-item-new .dn-tabel-daftar th:nth-child(6)  { width: 12%; }
  .nag-skin.dn-daftar-item-new .dn-tabel-daftar th:nth-child(7)  { width: 6%; }
  .nag-skin.dn-daftar-item-new .dn-tabel-daftar th:nth-child(8)  { width: 10%; }
  .nag-skin.dn-daftar-item-new .dn-tabel-daftar th:nth-child(9)  { width: 8%; }
  .nag-skin.dn-daftar-item-new .dn-tabel-daftar th:nth-child(10) { width: 7%; }
}
@include("master-data._gaya")
.nag-skin .md-ada-gambar { margin-left: 5px; color: #94a3b8; font-size: 10px; }

/* Pil Facility / Non Facility di tabel (kolom facility Y / N), tanpa ikon:
   Facility = latar hijau penuh; Non Facility = latar abu bergaris miring supaya beda sekilas */
.nag-skin .mn-fas {
  display: inline-block;
  padding: 3px 11px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .2px;
  white-space: nowrap;
}
.nag-skin .mn-fas.is-y {
  background: #16a34a;
  color: #fff;
  box-shadow: 0 2px 6px -2px rgba(22, 163, 74, .55);
}
.nag-skin .mn-fas.is-n {
  background-color: #f1f5f9;
  background-image: repeating-linear-gradient(135deg, rgba(148, 163, 184, .28) 0 4px, transparent 4px 8px);
  border: 1px solid #cbd5e1;
  color: #475569;
}

/* ===== Modal Create ===== */
#md-modal-form .md-det-ket { color: #cbd5e1; font-size: 12px; }
#md-modal-form .card:last-child { margin-bottom: 0; }
.nag-skin .md-ket { display: block; margin-top: 4px; font-size: 11px; color: #64748b; }
.nag-skin .md-ket i { margin-right: 4px; color: #94a3b8; }
.nag-skin .md-besar { text-transform: uppercase; }
/* Item Code otomatis: kotak terkunci bergaya kode */
.nag-skin .mn-auto {
  margin-left: 4px;
  padding: 1px 6px;
  border-radius: 5px;
  background: #1e3a5f;
  color: #fff;
  font-size: 9.5px;
  font-weight: 800;
  letter-spacing: .5px;
  vertical-align: 1px;
}
.nag-skin .mn-kode-auto[readonly] {
  background: #eef3f9;
  border-style: dashed;
  color: #0f2942;
  font-weight: 700;
  letter-spacing: .6px;
  cursor: default;
}
.nag-skin .mn-kode-auto::placeholder { font-weight: 400; letter-spacing: 0; color: #94a3b8; font-style: italic; }
/* Sub Group belum dipilih: kotak Item Code abu-abu putus-putus */
.nag-skin .mn-kode-auto[readonly]:placeholder-shown { background: #f1f5f9; border-style: dashed; border-color: #cbd5e1; }
.nag-skin .md-besar::placeholder { text-transform: none; }

/* Kotak info di bawah isian (Sub Group: accounting / customs; Facility: arti pilihannya) */
.nag-skin .mn-info {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  margin-top: 6px;
  padding: 7px 11px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #f1f5f9;
  color: #475569;
  font-size: 12px;
  line-height: 1.4;
}
.nag-skin .mn-info i { margin-top: 2px; }
.nag-skin .mn-info:empty { display: none; }
.nag-skin .mn-info b { color: #1e293b; }
.nag-skin .mn-info.is-y { background: #dcfce7; border-color: #86efac; color: #166534; }
.nag-skin .mn-info.is-y b { color: #14532d; }
.nag-skin .mn-info.is-n {
  background-color: #f8fafc;
  background-image: repeating-linear-gradient(135deg, rgba(148, 163, 184, .16) 0 4px, transparent 4px 8px);
  border-color: #cbd5e1;
  color: #475569;
}

/* Gambar: kotak pratinjau + tombol pilih / hapus */
.nag-skin .md-pratinjau {
  width: 100%;
  height: 158px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border: 1px dashed #cbd5e1;
  border-radius: 12px;
  background: #f8fafc;
  color: #94a3b8;
  font-size: 12px;
  text-align: center;
}
.nag-skin .md-pratinjau i { display: block; margin-bottom: 6px; font-size: 28px; opacity: .6; }
.nag-skin .md-pratinjau img { width: 100%; height: 100%; object-fit: contain; }
.nag-skin .md-unggah-aksi { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
.nag-skin .md-nama-file { display: block; margin-top: 5px; font-size: 11.5px; color: #334155; word-break: break-all; }
.nag-skin .md-nama-file:empty { display: none; }
.nag-skin .btn-md-pilih { border: 1px solid #cbd5e1; background: #fff; color: #1e3a5f; font-size: 12.5px; font-weight: 600; }
.nag-skin .btn-md-pilih:hover { background: #f1f5f9; }
.nag-skin .btn-md-hapus { border: 1px solid #fecaca; background: #fff; color: #b91c1c; font-size: 12.5px; font-weight: 600; }
.nag-skin .btn-md-hapus:hover { background: #fef2f2; }

/* Edit: Group & Sub Group dikunci (Item Code ikut kode sub group) */
.nag-skin .mn-kunci {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  margin-left: 4px;
  padding: 1px 6px;
  border-radius: 5px;
  background: #fef3c7;
  color: #92400e;
  font-size: 9.5px;
  font-weight: 800;
  letter-spacing: .4px;
  vertical-align: 1px;
}
.nag-skin .mn-kunci[hidden] { display: none; }

/* ===== Modal Show ===== */
/* Kepala modal: kalau ada gambar, avatarnya jadi gambar kecil */
#md-modal-detail .md-det-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 13px; }
/* Pil Facility di dalam kotak info */
#md-modal-detail .dn-det-info .mn-fas { margin-top: 2px; }
/* Tab Image */
#md-modal-detail .mn-gambar-wadah {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  padding: 14px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
}
#md-modal-detail .mn-gambar-wadah img { max-width: 100%; max-height: 60vh; object-fit: contain; border-radius: 8px; }
#md-modal-detail .mn-gambar-kosong { color: #94a3b8; font-size: 13px; text-align: center; }
#md-modal-detail .mn-gambar-kosong i { display: block; margin-bottom: 8px; font-size: 30px; opacity: .6; }
#md-modal-detail .mn-gambar-buka { display: inline-flex; align-items: center; gap: 6px; margin-top: 10px; font-size: 12px; }
</style>
@endsection

@section('content')
{{-- ===========================================================================
     Tab NEW - Daftar Master Item General struktur baru (database nds, master_item_generals).
     Server-side. Create / Edit lewat modal yang sama (Edit: Group & Sub Group dikunci),
     Show lewat modal detail, Active / Inactive lewat kolom cancel.
     =========================================================================== --}}
<div class="nag-skin dn-kontrol-sm dn-daftar-item-new">

    @include('master-data.item._tab-item', ['tabAktif' => 'new'])

    {{-- ===== Filter ===== --}}
    <div class="card dn-kartu-filter">
        <div class="card-body">
            <div class="dn-filter-petak">
                <div class="form-group dn-f-sedang">
                    <label for="mn-grup">Group</label>
                    <select class="form-control select2bs4" id="mn-grup">
                        <option value="">ALL</option>
                        @foreach ($grup as $g)
                            <option value="{{ $g->id }}">{{ $g->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group dn-f-sedang">
                    <label for="mn-sub">Sub Group</label>
                    <select class="form-control select2bs4" id="mn-sub">
                        <option value="">ALL</option>
                    </select>
                </div>
                <div class="form-group dn-f-sedang">
                    <label for="mn-fas">Facility</label>
                    <select class="form-control select2bs4" id="mn-fas">
                        <option value="">ALL</option>
                        <option value="Y">FACILITY</option>
                        <option value="N">NON FACILITY</option>
                    </select>
                </div>
                <div class="form-group dn-f-sedang">
                    <label for="mn-status">Status</label>
                    <select class="form-control select2bs4" id="mn-status">
                        <option value="">ALL</option>
                        <option value="aktif">ACTIVE</option>
                        <option value="nonaktif">INACTIVE</option>
                    </select>
                </div>
                <div class="dn-filter-tombol">
                    <button type="button" class="btn btn-dn-utama" id="mn-btn-cari">
                        <i class="fa fa-search"></i> Search
                    </button>
                    <span class="dn-filter-pisah"></span>
                    <button type="button" class="btn btn-dn-excel" id="mn-btn-export">
                        <i class="fas fa-file-excel"></i> Export
                    </button>
                    <a href="{{ route('master-data-item-new-create') }}" class="btn btn-dn-create" id="mn-btn-create">
                        <i class="fas fa-plus"></i> Create
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Tabel ===== --}}
    <div class="card">
        <div class="card-body">
            <div class="dn-list-area" id="mn-list-area">
                <div class="dn-loader-overlay">
                    <div class="dn-loader-kartu">
                        <span class="dn-loader-putar"></span> Loading...
                    </div>
                </div>
                <div class="dn-table-muat-wrap md-responsif">
                    <table id="mn-table" class="dn-table dn-table-muat dn-tabel-daftar" style="width:100%">
                        <thead>
                            <tr>
                                <th>Item Code</th>
                                <th>Description</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Group</th>
                                <th>Sub Group</th>
                                <th>Unit</th>
                                <th class="dn-tengah">Facility</th>
                                <th class="dn-tengah">Status</th>
                                <th class="dn-tengah md-th-aksi"><span>Action</span></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================================
         Modal Create - isian master_item_generals.
         Group General hanya penyaring Sub Group (yang disimpan general_subgroup_id).
         group_id diisi server: master_group ber-group_code 'N' (ITEM GENERAL).
         Tidak tertutup kalau klik di luar modal, supaya isian tidak hilang.
         =================================================================== --}}
    <div class="modal fade" id="md-modal-form" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header md-det-kepala">
                    <div class="md-det-avatar"><i class="fas fa-layer-group" id="mn-f-ikon"></i></div>
                    <div class="md-det-identitas">
                        <h5 class="md-det-nama" id="mn-f-judul">Create Master Item General</h5>
                        <div class="md-det-meta" id="mn-f-meta">
                            <span class="md-det-ket">Input form to add new general (non-production) item - new structure</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    {{-- ===== Klasifikasi dulu: Item Code ikut kode Sub Group ===== --}}
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-sitemap"></i> Classification</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="mn-f-grup">Group <span class="dn-wajib" title="Required">*</span> <span class="mn-kunci mn-tanda-kunci" hidden title="Cannot be changed - Item Code follows the sub group code"><i class="fas fa-lock"></i>LOCKED</span></label>
                                        <select class="form-control select2bs4" id="mn-f-grup">
                                            <option value="">-- Select group --</option>
                                            @foreach ($grup as $g)
                                                <option value="{{ $g->id }}">{{ $g->nama }}</option>
                                            @endforeach
                                        </select>
                                        <small class="md-ket" id="mn-ket-grup"><i class="fas fa-circle-info"></i>Choose group first to list its sub groups.</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="mn-f-sub">Sub Group <span class="dn-wajib" title="Required">*</span> <span class="mn-kunci mn-tanda-kunci" hidden title="Cannot be changed - Item Code follows the sub group code"><i class="fas fa-lock"></i>LOCKED</span></label>
                                        <select class="form-control select2bs4" id="mn-f-sub" name="general_subgroup_id">
                                            <option value="">-- Select sub group --</option>
                                        </select>
                                        <div class="mn-info" id="mn-info-sub"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="mn-fasilitas">Facility <span class="dn-wajib" title="Required">*</span></label>
                                        <select class="form-control select2bs4" id="mn-fasilitas" name="facility">
                                            <option value="">-- Select facility --</option>
                                            <option value="Y">Facility</option>
                                            <option value="N">Non Facility</option>
                                        </select>
                                        <div class="mn-info" id="mn-info-fasilitas"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== Data item (setelah klasifikasi): gambar di kiri, isian di kanan ===== --}}
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-box"></i> Item Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-3 col-md-4">
                                    <div class="form-group mb-0">
                                        <label>Image</label>
                                        <div class="md-pratinjau" id="mn-pratinjau"></div>
                                        <input type="file" id="mn-file" accept=".jpg,.jpeg,.png,.webp" hidden>
                                        <div class="md-unggah-aksi">
                                            <button type="button" class="btn btn-sm btn-md-pilih" id="mn-btn-pilih">
                                                <i class="fas fa-upload"></i> Choose image
                                            </button>
                                            <button type="button" class="btn btn-sm btn-md-hapus" id="mn-btn-hapus-gambar" hidden>
                                                <i class="fas fa-trash"></i> Remove
                                            </button>
                                        </div>
                                        <span class="md-nama-file" id="mn-nama-file"></span>
                                        <small class="md-ket"><i class="fas fa-circle-info"></i>JPG, PNG or WEBP. Optional.</small>
                                    </div>
                                </div>

                                <div class="col-lg-9 col-md-8">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                {{-- Tidak diketik: dibuat server dari kode sub group saat simpan (ATK-1, ATK-2, ...) --}}
                                                <label for="mn-kode">Item Code <span class="mn-auto">AUTO</span></label>
                                                <input type="text" class="form-control mn-kode-auto" id="mn-kode" readonly tabindex="-1"
                                                    placeholder="Choose Sub Group first">
                                                <small class="md-ket" id="mn-kode-ket"><i class="fas fa-circle-info"></i>Filled after Sub Group is chosen in Classification.</small>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="mn-nama">Description <span class="dn-wajib" title="Required">*</span></label>
                                                <input type="text" class="form-control md-besar" id="mn-nama" name="general_name"
                                                    maxlength="{{ $panjang['general_name'] }}" placeholder="Item description" autocomplete="off">
                                                <small class="md-ket"><i class="fas fa-circle-info"></i>Max {{ $panjang['general_name'] }} characters.</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="mn-warna">Color <span class="dn-wajib" title="Required">*</span></label>
                                                <input type="text" class="form-control md-besar" id="mn-warna" name="color"
                                                    maxlength="{{ $panjang['color'] }}" placeholder="Use - if none" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="mn-ukuran">Size <span class="dn-wajib" title="Required">*</span></label>
                                                <input type="text" class="form-control md-besar" id="mn-ukuran" name="size"
                                                    maxlength="{{ $panjang['size'] }}" placeholder="Use - if none" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="mn-unit">Unit <span class="dn-wajib" title="Required">*</span></label>
                                                <select class="form-control select2bs4" id="mn-unit" name="unit_id">
                                                    <option value="">-- Select unit --</option>
                                                    @foreach ($satuan as $u)
                                                        <option value="{{ $u->id }}">{{ $u->kode }} - {{ $u->nama }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="dn-kaki">
                                <button type="button" class="btn btn-dn-kembali" data-bs-dismiss="modal">
                                    <i class="fa fa-arrow-left"></i> Back
                                </button>
                                <button type="button" class="btn btn-dn-simpan" id="mn-btn-simpan">
                                    <i class="fa fa-save"></i> <span id="mn-kata-simpan">Save</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================================
         Modal Show - dari tombol mata di kolom Action.
         =================================================================== --}}
    <div class="modal fade" id="md-modal-detail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header md-det-kepala">
                    <div class="md-det-avatar" id="mn-det-avatar"><i class="fas fa-box"></i></div>
                    <div class="md-det-identitas">
                        <h5 class="md-det-nama" id="mn-det-judul">Item</h5>
                        <div class="md-det-meta" id="mn-det-meta"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="md-det-tab">
                    <ul class="nav" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link active" id="mn-tab-btn-basic" data-bs-toggle="tab" data-bs-target="#mn-tab-basic" role="tab">
                                <i class="fas fa-id-card"></i> Basic
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#mn-tab-gambar" role="tab">
                                <i class="fas fa-image"></i> Image
                                <span class="md-tab-cacah" id="mn-cacah-gambar" hidden>1</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="modal-body">
                    <div class="dn-det-muat" id="mn-det-muat">
                        <span class="dn-loader-putar"></span> Loading...
                    </div>
                    <div class="tab-content" id="mn-det-isi" hidden>
                        <div class="tab-pane fade show active" id="mn-tab-basic" role="tabpanel">
                            <div class="dn-det-judul"><i class="fas fa-fingerprint"></i> Identity</div>
                            <dl class="dn-det-info dn-det-info-4" id="mn-det-identitas"></dl>
                            <div class="dn-det-judul"><i class="fas fa-sitemap"></i> Classification</div>
                            <dl class="dn-det-info dn-det-info-4" id="mn-det-klasifikasi"></dl>
                            <div class="dn-det-judul"><i class="fas fa-clock-rotate-left"></i> Record</div>
                            <dl class="dn-det-info dn-det-info-4 mb-0" id="mn-det-catatan"></dl>
                        </div>
                        <div class="tab-pane fade" id="mn-tab-gambar" role="tabpanel">
                            <div class="mn-gambar-wadah" id="mn-det-gambar"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-dn-utama md-ke-form" id="mn-det-ubah" hidden>
                        <i class="fas fa-pen"></i> Edit
                    </a>
                    <button type="button" class="btn btn-dn-kembali" data-bs-dismiss="modal">
                        <i class="fas fa-xmark"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script>
$(function () {
    var RUT_DATA     = @json(route('master-data-item-new-list'));
    var RUT_SIMPAN   = @json(route('master-data-item-new-simpan'));
    var RUT_EXCEL    = @json(route('master-data-item-new-excel'));
    var RUT_EDIT     = @json(route('master-data-item-new-edit'));
    var RUT_PERBARUI = @json(route('master-data-item-new-perbarui'));
    var RUT_DETAIL   = @json(route('master-data-item-new-detail'));
    var RUT_STATUS   = @json(route('master-data-item-new-status'));
    var SUB_GRUP     = @json($subGrup);
    var EKSTENSI     = @json($ekstensi);
    var BATAS_SERVER = @json($batasUpload);
    var BUKA_FORM    = @json($bukaForm);
    var AWAL         = @json($awal);   // dari dashboard: status, cari

    $('#mn-status').val(AWAL.status || '');
    $('.dn-kartu-filter .select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

    function teks(nilai) {
        if (nilai == null || String(nilai).trim() === '') { return '-'; }
        return $('<div>').text(String(nilai).trim()).html();
    }
    function ada(nilai) {
        var s = String(nilai == null ? '' : nilai).trim();
        return s !== '' && s !== '-';
    }
    function biasa(v, t) { return t === 'display' ? teks(v) : (v || ''); }

    // Isi pilihan Sub Group menurut Group. hanyaAktif: form Create tidak menawarkan sub group Inactive.
    function isiSubGrup($s, grup, kosong, hanyaAktif) {
        grup = String(grup || '');
        $s.empty().append($('<option value="">').text(kosong));
        SUB_GRUP.forEach(function (s) {
            if (hanyaAktif && String(s.aktif) !== '1') { return; }
            if (grup === '' ? !hanyaAktif : String(s.grup) === grup) {
                $s.append($('<option>').val(s.id).text((s.kode ? s.kode + ' - ' : '') + s.nama + (String(s.aktif) === '1' ? '' : ' (inactive)')));
            }
        });
        $s.val('').trigger('change');
    }

    // ---------- Filter ----------
    $('#mn-grup').on('change', function () { isiSubGrup($('#mn-sub'), this.value, 'ALL', false); });
    isiSubGrup($('#mn-sub'), '', 'ALL', false);

    function filterSaatIni() {
        return {
            grup:   $('#mn-grup').val() || '',
            sub:    $('#mn-sub').val() || '',
            fas:    $('#mn-fas').val() || '',
            status: $('#mn-status').val() || ''
        };
    }

    // ---------- Tabel ----------
    var kolom = [
        {
            data: 'kode',
            render: function (v, t, row) {
                if (t !== 'display') { return v || ''; }
                return '<span class="md-kode">' + teks(v) + '</span>'
                    + (String(row.gambar) === '1' ? '<i class="fas fa-image md-ada-gambar" title="Has image"></i>' : '')
                    + '<span class="md-sub">ID ' + teks(row.id) + '</span>';
            }
        },
        { data: 'nama', render: biasa },
        { data: 'color', render: biasa },
        { data: 'size', render: biasa },
        { data: 'grup', render: biasa },
        { data: 'sub_grup', render: biasa },
        { data: 'satuan', render: biasa },
        {
            data: 'facility',
            className: 'dn-tengah',
            render: function (v, t) {
                var y = String(v).toUpperCase() === 'Y';
                if (t !== 'display') { return y ? 'Facility' : 'Non Facility'; }
                return '<span class="mn-fas ' + (y ? 'is-y' : 'is-n') + '">' + (y ? 'Facility' : 'Non Facility') + '</span>';
            }
        },
        {
            data: 'cancel',
            className: 'dn-tengah',
            render: function (v, t) {
                var aktif = String(v) !== 'Y';
                if (t !== 'display') { return aktif ? 'ACTIVE' : 'INACTIVE'; }
                return '<span class="dn-pil ' + (aktif ? 'is-second' : 'is-batal') + '">'
                    + '<span class="dn-pil-titik"></span>' + (aktif ? 'Active' : 'Inactive') + '</span>';
            }
        },
        // Show / Active-Inactive / Edit - layar lebar tiga tombol, HP satu tombol menu (mdTombolAksi)
        {
            data: null,
            orderable: false,
            className: 'dn-tengah',
            render: function (v, t, row) {
                if (t !== 'display') { return ''; }
                return mdTombolAksi({ id: row.id, aktif: String(row.cancel) !== 'Y', urlEdit: RUT_EDIT });
            }
        }
    ];

    var PRIORITAS = { nama: 1, kode: 2, cancel: 4, facility: 5, grup: 6, sub_grup: 7, satuan: 8, color: 9, size: 10 };
    kolom.forEach(function (k, i) {
        k.responsivePriority = PRIORITAS[k.data] || (i === kolom.length - 1 ? 3 : 10);
    });

@include('master-data._responsif_js')

    // Filter atas yang terakhir dipakai memuat tabel (bukan pilihan yang belum di-Search)
    var filterTerpakai = filterSaatIni();

    var tabel = $('#mn-table').DataTable({
        serverSide: true,
        searchDelay: 400,
        search: { search: AWAL.cari || '' },
        lengthMenu: [10, 25, 50, 100],
        order: [],
        autoWidth: false,
        responsive: mdResponsif(),
        language: {
            emptyTable: '<div class="dn-kosong-isi"><i class="fas fa-layer-group"></i><span>No item in the new structure yet.</span></div>',
            zeroRecords: '<div class="dn-kosong-isi"><i class="fas fa-magnifying-glass"></i><span>No item matches your search.</span></div>'
        },
        dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: RUT_DATA,
            data: function (d) {
                delete d.columns;
                filterTerpakai = filterSaatIni();
                $.extend(d, filterTerpakai);
            },
            error: function () { $('#mn-list-area').removeClass('is-memuat'); }
        },
        columns: kolom,
        createdRow: function (tr, row) {
            $(tr).toggleClass('md-nonaktif', String(row.cancel) === 'Y');
        }
    });

    tabel.on('preXhr.dt', function () { $('#mn-list-area').addClass('is-memuat'); });
    tabel.on('xhr.dt draw.dt', function () { $('#mn-list-area').removeClass('is-memuat'); });

    $('#mn-btn-cari').on('click', function () { tabel.ajax.reload(); });

    // Export memakai filter yang terakhir memuat tabel + teks Search, jadi isinya sama dengan yang tampil
    $('#mn-btn-export').on('click', function () {
        mdExportExcel(RUT_EXCEL + '?' + $.param($.extend({ cari: tabel.search() }, filterTerpakai)));
    });

    // ===================================================================
    // Show: modal detail
    // ===================================================================
    var modalDetail = new bootstrap.Modal(document.getElementById('md-modal-detail'));

    function barisInfo(label, isi, kelas) {
        return '<div' + (kelas ? ' class="' + kelas + '"' : '') + '><dt>' + label + '</dt><dd>' + teks(isi) + '</dd></div>';
    }
    function pilFasilitas(v) {
        var y = String(v).toUpperCase() === 'Y';
        return '<span class="mn-fas ' + (y ? 'is-y' : 'is-n') + '">' + (y ? 'Facility' : 'Non Facility') + '</span>';
    }

    $('#mn-table').on('click', '.md-btn-lihat', function () {
        var id = $(this).data('id');

        bootstrap.Tab.getOrCreateInstance(document.getElementById('mn-tab-btn-basic')).show();
        $('#mn-det-judul').text('Item').removeAttr('title');
        $('#mn-det-avatar').html('<i class="fas fa-box"></i>');
        $('#mn-det-meta').empty();
        $('#mn-cacah-gambar').prop('hidden', true);
        $('#mn-det-ubah').prop('hidden', true);
        $('#mn-det-muat').prop('hidden', false);
        $('#mn-det-isi').prop('hidden', true);
        modalDetail.show();

        $.getJSON(RUT_DETAIL, { id: id }).done(function (res) {
            var d = res.data || {};
            var aktif = String(d.cancel) !== 'Y';

            // ----- Kepala -----
            $('#mn-det-judul').text(d.general_name || 'Item').attr('title', d.general_name || '');
            if (res.gambar_url) {
                $('#mn-det-avatar').html($('<img alt="">').attr('src', res.gambar_url)
                    .on('error', function () { $('#mn-det-avatar').html('<i class="fas fa-box"></i>'); }));
            }
            $('#mn-det-meta').html(''
                + '<span class="md-det-chip is-kode">' + teks(d.general_code) + '</span>'
                + '<span class="md-det-chip"><i class="fas fa-hashtag"></i>ID ' + teks(d.id) + '</span>'
                + '<span class="md-det-chip"><i class="fas fa-sitemap"></i>' + teks(d.general_subgroup_name) + '</span>'
                + '<span class="dn-pil ' + (aktif ? 'is-second' : 'is-batal') + '"><span class="dn-pil-titik"></span>'
                + (aktif ? 'Active' : 'Inactive') + '</span>');

            // ----- Basic -----
            $('#mn-det-identitas').html(''
                + barisInfo('Item Code', d.general_code)
                + barisInfo('Description', d.general_name, 'md-penuh')
                + barisInfo('ID', d.id)
                + barisInfo('Color', d.color)
                + barisInfo('Size', d.size)
                + barisInfo('Unit', ada(d.unit_code) ? d.unit_code + (ada(d.unit_name) ? ' - ' + d.unit_name : '') : ''));
            $('#mn-det-klasifikasi').html(''
                + barisInfo('Group', d.general_group_name)
                + barisInfo('Sub Group', (ada(d.general_subgroup_code) ? d.general_subgroup_code + ' - ' : '') + (d.general_subgroup_name || ''))
                + barisInfo('Accounting', d.accounting_type)
                + barisInfo('Customs', d.customs_type)
                + '<div><dt>Facility</dt><dd>' + pilFasilitas(d.facility) + '</dd></div>');
            $('#mn-det-catatan').html(''
                + barisInfo('Created By', d.created_by)
                + barisInfo('Created At', d.created_at)
                + barisInfo('Updated At', d.updated_at));

            // ----- Image -----
            var $gambar = $('#mn-det-gambar');
            if (res.gambar_url) {
                $('#mn-cacah-gambar').prop('hidden', false);
                $gambar.html('<div class="text-center"></div>');
                $gambar.children().append($('<img alt="">').attr('src', res.gambar_url).on('error', function () {
                    $gambar.html('<div class="mn-gambar-kosong"><i class="fas fa-image"></i>Image file not found.</div>');
                })).append($('<div>').append($('<a class="mn-gambar-buka" target="_blank" rel="noopener">').attr('href', res.gambar_url)
                    .html('<i class="fas fa-up-right-from-square"></i> Open full size')));
            } else {
                $gambar.html('<div class="mn-gambar-kosong"><i class="fas fa-image"></i>'
                    + (d.picture ? 'Image file not found.' : 'No image for this item.') + '</div>');
            }

            $('#mn-det-ubah').attr('href', RUT_EDIT + '?id=' + encodeURIComponent(d.id))
                .data({ dasar: RUT_EDIT, id: d.id }).prop('hidden', false);
            $('#mn-det-muat').prop('hidden', true);
            $('#mn-det-isi').prop('hidden', false);
        }).fail(function (x) {
            modalDetail.hide();
            Swal.fire({
                icon: 'error',
                title: 'Failed to load detail',
                text: (x.responseJSON && x.responseJSON.message) || 'Please try again.',
                customClass: { popup: 'dn-swal' }
            });
        });
    });

    // ===================================================================
    // Active / Inactive
    // ===================================================================
    $('#mn-table').on('click', '.md-btn-status', function () {
        var row = mdBarisDari(tabel, this);
        var keAktif = String($(this).data('aktif')) !== '1';

        Swal.fire({
            icon: 'question',
            title: keAktif ? 'Set this item Active?' : 'Set this item Inactive?',
            html: '<div class="dn-swal-ringkas">'
                + '<div><span>Item Code</span><b>' + teks(row.kode) + '</b></div>'
                + '<div><span>Description</span><b>' + teks(row.nama) + '</b></div>'
                + '<div><span>New status</span><b>' + (keAktif ? 'Active' : 'Inactive') + '</b></div>'
                + '</div>',
            showCancelButton: true,
            confirmButtonText: keAktif ? '<i class="fas fa-toggle-on"></i> Yes, set Active' : '<i class="fas fa-toggle-off"></i> Yes, set Inactive',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            focusCancel: true,
            customClass: { popup: 'dn-swal' }
        }).then(function (pilih) {
            if (!pilih.isConfirmed) { return; }

            $.ajax({
                url: RUT_STATUS,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json' },
                data: { id: row.id, aktif: keAktif ? 1 : 0 }
            }).done(function (res) {
                Swal.fire({
                    icon: 'success',
                    title: res.aktif ? 'Set to Active' : 'Set to Inactive',
                    html: '<b>' + teks(res.kode) + '</b> is now ' + (res.aktif ? 'Active' : 'Inactive') + '.',
                    timer: 1800,
                    showConfirmButton: false,
                    customClass: { popup: 'dn-swal' }
                });
                tabel.ajax.reload(null, false);   // tetap di halaman yang sama
            }).fail(function (x) {
                Swal.fire({
                    icon: 'error',
                    title: 'Status not changed',
                    text: (x.responseJSON && x.responseJSON.message) || 'Please try again.',
                    customClass: { popup: 'dn-swal' }
                });
            });
        });
    });

    // ===================================================================
    // Modal Create / Edit
    // ===================================================================
    var $form = $('#md-modal-form');
    // Tanpa focus trap: swal di atas modal tetap bisa difokus
    var modalForm = new bootstrap.Modal($form[0], { focus: false });
    $form.find('.select2bs4').select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $form });

    // Gambar: file = baru dipilih, url = pratinjau file itu, lama = url gambar item (Edit), hapus = gambar lama dibuang
    var G = { file: null, url: null, lama: null, hapus: false };
    var ubah = null;   // data item yang di-Edit (null = Create)

    function nilai(id) { return $.trim($(id).val() || ''); }

    // ---------- Group -> Sub Group + info accounting / customs ----------
    $('#mn-f-grup').on('change', function () {
        isiSubGrup($('#mn-f-sub'), this.value, this.value ? '-- Select sub group --' : '-- Select group first --', true);
    });

    $('#mn-f-sub').on('change', function () {
        var id = String(this.value || '');
        var s = SUB_GRUP.filter(function (x) { return String(x.id) === id; })[0];
        $('#mn-info-sub').html(!s ? '' : '<i class="fas fa-circle-info"></i><span>'
            + 'Code: <b>' + teks(s.kode) + '</b> &middot; Accounting: <b>' + teks(s.accounting) + '</b> &middot; Customs: <b>' + teks(s.customs) + '</b></span>');

        // Perkiraan Item Code (nomor pastinya ditentukan saat simpan)
        $('#mn-kode').val(s && s.berikut ? s.berikut : '');
        $('#mn-kode-ket').html(!s
            ? '<i class="fas fa-circle-info"></i>Filled after Sub Group is chosen in Classification.'
            : (s.berikut
                ? '<i class="fas fa-circle-info"></i>Next number for ' + teks(s.kode) + '. Final number is set when saved.'
                : '<i class="fas fa-triangle-exclamation"></i>This sub group has no valid code. Set it in Master Group General.'));
    });

    // ---------- Facility: arti pilihan ditampilkan waktu memilih (disimpan Y / N) ----------
    function infoFasilitas() {
        var v = $('#mn-fasilitas').val();
        var $i = $('#mn-info-fasilitas').removeClass('is-y is-n');
        if (v === 'Y') {
            $i.addClass('is-y').html('<i class="fas fa-circle-check"></i><span><b>Facility.</b> This item uses facility.</span>');
        } else if (v === 'N') {
            $i.addClass('is-n').html('<i class="fas fa-circle-minus"></i><span><b>Non Facility.</b> This item does not use facility.</span>');
        } else {
            $i.html('<i class="fas fa-circle-info"></i><span>Choose <b>Facility</b> if this item uses facility, <b>Non Facility</b> if it does not.</span>');
        }
    }
    $('#mn-fasilitas').on('change', infoFasilitas);

    // ---------- Gambar ----------
    function tampilPratinjau(src) {
        var $p = $('#mn-pratinjau').empty();
        if (src) { $p.append($('<img alt="">').attr('src', src)); }
        else { $p.html('<span><i class="fas fa-image"></i>No image</span>'); }
    }
    // Kosongkan kotak gambar. Saat Edit, gambar lama ikut ditandai dibuang (baru terhapus waktu Update).
    function lepasGambar() {
        if (G.url) { URL.revokeObjectURL(G.url); }
        G = { file: null, url: null, lama: G.lama, hapus: !!G.lama };
        tampilPratinjau(null);
        $('#mn-nama-file').text(G.hapus ? 'Current image will be removed when updated.' : '');
        $('#mn-btn-hapus-gambar').prop('hidden', true);
        $('#mn-btn-pilih').html('<i class="fas fa-upload"></i> Choose image');
        $('#mn-file').val('');
    }

    // Keadaan awal gambar saat modal dibuka: Create kosong, Edit menampilkan gambar item
    function awalGambar(urlLama) {
        if (G.url) { URL.revokeObjectURL(G.url); }
        G = { file: null, url: null, lama: urlLama || null, hapus: false };
        $('#mn-file').val('');
        $('#mn-nama-file').text('');
        if (G.lama) {
            var $p = $('#mn-pratinjau').empty();
            $p.append($('<img alt="">').attr('src', G.lama).on('error', function () {
                $p.html('<span><i class="fas fa-image"></i>Image file not found</span>');
            }));
            $('#mn-btn-hapus-gambar').prop('hidden', false);
            $('#mn-btn-pilih').html('<i class="fas fa-upload"></i> Replace');
        } else {
            tampilPratinjau(null);
            $('#mn-btn-hapus-gambar').prop('hidden', true);
            $('#mn-btn-pilih').html('<i class="fas fa-upload"></i> Choose image');
        }
    }

    $('#mn-btn-pilih').on('click', function () { $('#mn-file').val('').trigger('click'); });

    $('#mn-file').on('change', function () {
        var f = this.files && this.files[0];
        if (!f) { return; }
        var ext = (f.name.split('.').pop() || '').toLowerCase();
        if (EKSTENSI.indexOf(ext) === -1) {
            Swal.fire({ icon: 'warning', title: 'Image not accepted', text: 'Image must be JPG, PNG or WEBP.', customClass: { popup: 'dn-swal' } });
            return;
        }
        if (G.url) { URL.revokeObjectURL(G.url); }
        G = { file: f, url: URL.createObjectURL(f), lama: G.lama, hapus: false };
        tampilPratinjau(G.url);
        $('#mn-nama-file').text(f.name);
        $('#mn-btn-hapus-gambar').prop('hidden', false);
        $('#mn-btn-pilih').html('<i class="fas fa-upload"></i> Replace');
    });

    $('#mn-btn-hapus-gambar').on('click', lepasGambar);

    // ---------- Buka modal: id kosong = Create (form kosong), id = Edit (data diambil dari detail) ----------
    function bukaForm(id) {
        if (!id) { isiForm(null, null); return; }

        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false, allowEscapeKey: false,
            showConfirmButton: false, customClass: { popup: 'dn-swal' },
            didOpen: function () { Swal.showLoading(); }
        });
        $.getJSON(RUT_DETAIL, { id: id }).done(function (res) {
            Swal.close();
            isiForm(res.data || {}, res.gambar_url);
        }).fail(function (x) {
            Swal.fire({
                icon: 'error',
                title: 'Failed to open item',
                text: (x.responseJSON && x.responseJSON.message) || 'Please try again.',
                customClass: { popup: 'dn-swal' }
            });
        });
    }

    function isiForm(d, urlGambar) {
        ubah = d;
        $('#mn-f-ikon').attr('class', 'fas ' + (d ? 'fa-pen-to-square' : 'fa-layer-group'));
        $('#mn-f-judul').text((d ? 'Edit' : 'Create') + ' Master Item General');
        $('#mn-f-meta').html(!d
            ? '<span class="md-det-ket">Input form to add new general (non-production) item - new structure</span>'
            : '<span class="md-det-ket">Update item data</span>'
                + '<span class="md-det-chip is-kode">' + teks(d.general_code) + '</span>'
                + (String(d.cancel) === 'Y' ? '<span class="dn-pil is-batal"><span class="dn-pil-titik"></span>Inactive</span>' : ''));
        $('#mn-kata-simpan').text(d ? 'Update' : 'Save');

        d = d || {};
        $('#mn-nama').val(d.general_name || '');
        $('#mn-warna').val(d.color || '');
        $('#mn-ukuran').val(d.size || '');

        // Satuan lama yang sudah Inactive tetap ditampilkan supaya tidak kosong
        var $unit = $('#mn-unit');
        $unit.find('option.mn-opsi-lama').remove();
        if (d.unit_id && !$unit.find('option[value="' + d.unit_id + '"]').length) {
            $unit.append($('<option class="mn-opsi-lama">').val(d.unit_id).text((d.unit_code || '') + ' - ' + (d.unit_name || '') + ' (inactive)'));
        }
        $unit.val(d.unit_id ? String(d.unit_id) : '').trigger('change');

        // Group & Sub Group: Edit dikunci di sub group item (juga kalau sub group itu sudah Inactive)
        $('#mn-f-grup').val(d.general_group_id ? String(d.general_group_id) : '').trigger('change');
        if (ubah) {
            var $sub = $('#mn-f-sub');
            if (!$sub.find('option[value="' + d.general_subgroup_id + '"]').length) {
                $sub.append($('<option>').val(d.general_subgroup_id)
                    .text((d.general_subgroup_code ? d.general_subgroup_code + ' - ' : '') + d.general_subgroup_name + ' (inactive)'));
            }
            $sub.val(String(d.general_subgroup_id)).trigger('change');
            $('#mn-kode').val(d.general_code || '');
            $('#mn-kode-ket').html('<i class="fas fa-lock"></i>Item Code cannot be changed.');
        }
        $('#mn-f-grup, #mn-f-sub').prop('disabled', !!ubah);
        $('.mn-tanda-kunci').prop('hidden', !ubah);
        $('#mn-ket-grup').html(ubah
            ? '<i class="fas fa-lock"></i>Group & Sub Group cannot be changed because Item Code follows the sub group code.'
            : '<i class="fas fa-circle-info"></i>Choose group first to list its sub groups.');

        $('#mn-fasilitas').val(d.facility ? String(d.facility).toUpperCase() : '').trigger('change');
        awalGambar(urlGambar);
        infoFasilitas();
        $('#mn-btn-simpan').prop('disabled', false);
        modalForm.show();
    }

    // href tetap ada supaya Ctrl+klik / tab baru juga jalan (server membuka halaman ini dengan modal terbuka)
    $('#mn-btn-create').on('click', function (e) {
        if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) { return; }
        e.preventDefault();
        bukaForm(null);
    });

    // Edit dari tombol pensil (tabel / menu HP) dan dari modal Show
    $(document).on('click', 'a.md-ke-form', function (e) {
        if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) { return; }
        e.preventDefault();
        var id = $(this).data('id');
        if (!id) { return; }
        if ($(this).closest('#md-modal-detail').length) {
            // Dari modal Show: tutup dulu, baru buka form (dua modal tidak ditumpuk)
            $('#md-modal-detail').one('hidden.bs.modal', function () { bukaForm(id); });
            modalDetail.hide();
        } else {
            bukaForm(id);
        }
    });

    $form.on('hidden.bs.modal', function () {
        if (G.url) { URL.revokeObjectURL(G.url); G.url = null; }
    });

    // ---------- Simpan ----------
    $('#mn-btn-simpan').on('click', function () {
        var kurang = [];
        if (!nilai('#mn-nama'))       { kurang.push('Description'); }
        if (!nilai('#mn-warna'))      { kurang.push('Color (use - if none)'); }
        if (!nilai('#mn-ukuran'))     { kurang.push('Size (use - if none)'); }
        if (!nilai('#mn-unit'))       { kurang.push('Unit'); }
        if (!nilai('#mn-f-grup'))     { kurang.push('Group'); }
        if (!nilai('#mn-f-sub'))      { kurang.push('Sub Group'); }
        if (!nilai('#mn-fasilitas'))  { kurang.push('Facility / Non Facility'); }

        if (kurang.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Please complete the form',
                html: 'The following must be filled in before saving:<ul class="dn-swal-kurang"><li>' + kurang.join('</li><li>') + '</li></ul>',
                confirmButtonText: 'OK',
                customClass: { popup: 'dn-swal' }
            });
            return;
        }

        var $tb = $(this);
        var fas = nilai('#mn-fasilitas');
        var teksGambar = G.file ? 'New image' : (G.hapus ? 'Removed' : (G.lama ? 'Unchanged' : 'None'));
        var ringkas = '<div class="dn-swal-ringkas">'
            + '<div><span>Item Code</span><b>' + teks(nilai('#mn-kode')) + (ubah ? '' : ' (auto)') + '</b></div>'
            + '<div><span>Description</span><b>' + teks(nilai('#mn-nama').toUpperCase()) + '</b></div>'
            + '<div><span>Sub Group</span><b>' + teks($('#mn-f-sub').find(':selected').text()) + '</b></div>'
            + '<div><span>Unit</span><b>' + teks($('#mn-unit').find(':selected').text()) + '</b></div>'
            + '<div><span>Facility</span><b>' + (fas === 'Y' ? 'Facility' : 'Non Facility') + '</b></div>'
            + '<div><span>Image</span><b>' + teksGambar + '</b></div>'
            + '</div>';

        Swal.fire({
            icon: 'question',
            title: ubah ? 'Update this item?' : 'Save this item?',
            html: ringkas,
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-save"></i> ' + (ubah ? 'Yes, update it' : 'Yes, save it'),
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            focusCancel: true,
            customClass: { popup: 'dn-swal' }
        }).then(function (p) {
            if (p.isConfirmed) { kirimSimpan($tb); }
        });
    });

    function kirimSimpan($tb) {
        var fd = new FormData();
        $form.find('input[name], select[name]').each(function () {
            fd.append(this.name, $.trim($(this).val() || ''));
        });
        if (G.file) { fd.append('gambar', G.file); }
        if (ubah) {
            fd.append('id', ubah.id);
            if (G.hapus && !G.file) { fd.append('hapus_gambar', '1'); }
        }

        $tb.prop('disabled', true);
        Swal.fire({
            title: ubah ? 'Updating...' : 'Saving...',
            allowOutsideClick: false, allowEscapeKey: false,
            showConfirmButton: false, customClass: { popup: 'dn-swal' },
            didOpen: function () { Swal.showLoading(); }
        });

        $.ajax({
            url: ubah ? RUT_PERBARUI : RUT_SIMPAN,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json' },
            data: fd,
            processData: false,
            contentType: false
        }).done(function (res) {
            // Create: perkiraan nomor berikutnya sub group ini ikut naik (tanpa muat ulang halaman)
            if (!ubah) {
                var subId = String(nilai('#mn-f-sub'));
                var m = /^(.*)-(\d+)$/.exec(String(res.kode || ''));
                SUB_GRUP.forEach(function (s) {
                    if (String(s.id) === subId && m) { s.berikut = m[1] + '-' + (parseInt(m[2], 10) + 1); }
                });
            }
            Swal.fire({
                icon: 'success',
                title: ubah ? 'Item updated' : 'Item saved',
                html: '<b>' + teks(res.kode) + ' - ' + teks(res.nama) + '</b> has been ' + (ubah ? 'updated' : 'saved') + '.',
                confirmButtonText: 'OK',
                allowOutsideClick: false,
                customClass: { popup: 'dn-swal' }
            }).then(function () {
                modalForm.hide();
                tabel.ajax.reload(null, false);
            });
        }).fail(function (x) {
            $tb.prop('disabled', false);
            var res = x.responseJSON || {};
            var pesan = res.message || (x.status === 413 ? 'The image is larger than the server upload limit (' + BATAS_SERVER + ').' : 'Failed to save, please try again.');
            var html = teks(pesan);
            if (res.kurang && res.kurang.length) {
                html += '<ul class="dn-swal-kurang"><li>' + res.kurang.map(teks).join('</li><li>') + '</li></ul>';
            }
            Swal.fire({
                icon: x.status === 409 ? 'warning' : 'error',
                title: x.status === 409 ? 'Already exists' : (ubah ? 'Not updated' : 'Not saved'),
                html: html,
                confirmButtonText: 'OK',
                customClass: { popup: 'dn-swal' }
            });
        });
    }

    // Dari alamat create (true) / edit (id) langsung
    if (BUKA_FORM) { bukaForm(BUKA_FORM === true ? null : BUKA_FORM); }
});
</script>
@endpush
@endsection
