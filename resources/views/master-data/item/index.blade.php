@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@include('export-import.invoice._skin')
<style>
/* Lebar kolom daftar item (8 kolom, terakhir Action) */
@media (min-width: 992px) {
  .nag-skin.dn-daftar-item .dn-tabel-daftar th:nth-child(1) { width: 13%; }
  .nag-skin.dn-daftar-item .dn-tabel-daftar th:nth-child(2) { width: 29%; }
  .nag-skin.dn-daftar-item .dn-tabel-daftar th:nth-child(3) { width: 9%; }
  .nag-skin.dn-daftar-item .dn-tabel-daftar th:nth-child(4) { width: 9%; }
  .nag-skin.dn-daftar-item .dn-tabel-daftar th:nth-child(5) { width: 10%; }
  .nag-skin.dn-daftar-item .dn-tabel-daftar th:nth-child(6) { width: 15%; }
  .nag-skin.dn-daftar-item .dn-tabel-daftar th:nth-child(7) { width: 7%; }
  .nag-skin.dn-daftar-item .dn-tabel-daftar th:nth-child(8) { width: 8%; }
}
@include("master-data._gaya")
/* Penanda item yang punya gambar, di sebelah kode */
.nag-skin .md-ada-gambar { margin-left: 5px; color: #94a3b8; font-size: 10px; }

/* Kepala modal: kalau ada gambar, avatarnya jadi gambar kecil */
#md-modal-detail .md-det-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 13px; }

/* Chart of Accounts: satu kartu per akun */
#md-modal-detail .md-coa-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
#md-modal-detail .md-coa {
  display: flex;
  align-items: flex-start;
  gap: 11px;
  padding: 12px 14px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  min-width: 0;
}
#md-modal-detail .md-coa-ikon {
  flex: 0 0 auto;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 12px;
}
#md-modal-detail .md-coa:nth-child(1) .md-coa-ikon { background: #1e3a5f; }
#md-modal-detail .md-coa:nth-child(2) .md-coa-ikon { background: #15803d; }
#md-modal-detail .md-coa:nth-child(3) .md-coa-ikon { background: #b45309; }
#md-modal-detail .md-coa:nth-child(4) .md-coa-ikon { background: #7c3aed; }
#md-modal-detail .md-coa > div { min-width: 0; }
#md-modal-detail .md-coa > div > span { display: block; font-size: 10.5px; font-weight: 600; color: #64748b; }
#md-modal-detail .md-coa b { display: block; font-size: 13px; color: #1e293b; }
#md-modal-detail .md-coa small { display: block; font-size: 11.5px; color: #475569; line-height: 1.35; }
#md-modal-detail .md-coa.is-kosong b { color: #94a3b8; font-weight: 500; font-style: italic; }
#md-modal-detail .md-coa-ket {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
  padding: 9px 12px;
  border-radius: 10px;
  background: #eef2f7;
  color: #475569;
  font-size: 12px;
}

/* Tab Image */
#md-modal-detail .md-gambar-wadah {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  padding: 14px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
}
#md-modal-detail .md-gambar-wadah img { max-width: 100%; max-height: 60vh; object-fit: contain; border-radius: 8px; }
#md-modal-detail .md-gambar-kosong { color: #94a3b8; font-size: 13px; text-align: center; }
#md-modal-detail .md-gambar-kosong i { display: block; margin-bottom: 8px; font-size: 30px; opacity: .6; }
#md-modal-detail .md-gambar-buka { display: inline-flex; align-items: center; gap: 6px; margin-top: 10px; font-size: 12px; }

@media (max-width: 767px) {
  #md-modal-detail .md-coa-grid { grid-template-columns: 1fr; }
}

/* ===== Modal Create / Edit - isinya sama dengan halaman form lama ===== */
/* Keterangan di bawah judul kepala modal */
#md-modal-form .md-det-ket { color: #cbd5e1; font-size: 12px; }
#md-modal-form .card:last-child { margin-bottom: 0; }

.nag-skin .md-ket { display: block; margin-top: 4px; font-size: 11px; color: #64748b; }
.nag-skin .md-ket i { margin-right: 4px; color: #94a3b8; }
.nag-skin .md-besar { text-transform: uppercase; }
.nag-skin .md-besar::placeholder { text-transform: none; }
/* Di sebelah Inventory Mapping: CoA wajib atau tidak untuk mapping yang dipilih.
   Diberi jarak setinggi label supaya sejajar dengan kotak pilihannya. */
.nag-skin .md-coa-status {
  display: flex;
  align-items: center;
  gap: 8px;
  min-height: 31px;
  margin-top: 23px;
  padding: 5px 11px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #f1f5f9;
  color: #475569;
  font-size: 12px;
  line-height: 1.3;
}
.nag-skin .md-coa-status.is-wajib { background: #fef3c7; border-color: #fde68a; color: #92400e; }
.nag-skin .md-coa-status:empty { display: none; }

/* Gambar: kotak pratinjau selebar kolom + tombol pilih / hapus di bawahnya */
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
.nag-skin .md-nama-file.is-hapus { color: #b91c1c; }
.nag-skin .btn-md-pilih {
  border: 1px solid #cbd5e1;
  background: #fff;
  color: #1e3a5f;
  font-size: 12.5px;
  font-weight: 600;
}
.nag-skin .btn-md-pilih:hover { background: #f1f5f9; }
.nag-skin .btn-md-hapus {
  border: 1px solid #fecaca;
  background: #fff;
  color: #b91c1c;
  font-size: 12.5px;
  font-weight: 600;
}
.nag-skin .btn-md-hapus:hover { background: #fef2f2; }
</style>
@endsection

@section('content')
{{-- ===========================================================================
     Daftar Master Item General (masteritem mattype N / M di SB), server-side.
     Aksi per baris: Show (modal bertab), Active/Inactive, Edit.
     =========================================================================== --}}
<div class="nag-skin dn-kontrol-sm dn-daftar-item">

    <div class="dn-kepala-halaman">
        <div class="dn-kepala-kiri">
            <span class="dn-kepala-ikon"><i class="fas fa-boxes-stacked"></i></span>
            <div>
                <h1 class="dn-kepala-judul">Master Item General</h1>
                <p class="dn-kepala-sub">List of general (non-production) items</p>
            </div>
        </div>
    </div>

    {{-- ===== Filter ===== --}}
    <div class="card dn-kartu-filter">
        <div class="card-body">
            <div class="dn-filter-petak">
                <div class="form-group dn-f-lebar">
                    <label for="md-kategori">Inventory Mapping</label>
                    <select class="form-control select2bs4" id="md-kategori">
                        <option value="">ALL</option>
                        @foreach ($kategori as $id => $nama)
                            <option value="{{ $id }}" {{ $awal['kategori'] === (string) $id ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group dn-f-sedang">
                    <label for="md-tipe">Item Type</label>
                    <select class="form-control select2bs4" id="md-tipe">
                        <option value="">ALL</option>
                        @foreach ($tipe as $t)
                            <option value="{{ $t }}" {{ $awal['tipe'] === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group dn-f-sedang">
                    <label for="md-status">Status</label>
                    <select class="form-control select2bs4" id="md-status">
                        <option value="">ALL</option>
                        <option value="aktif" {{ $awal['status'] === 'aktif' ? 'selected' : '' }}>ACTIVE</option>
                        <option value="nonaktif" {{ $awal['status'] === 'nonaktif' ? 'selected' : '' }}>INACTIVE</option>
                    </select>
                </div>
                <div class="dn-filter-tombol">
                    <button type="button" class="btn btn-dn-utama" id="md-btn-cari">
                        <i class="fa fa-search"></i> Search
                    </button>
                    <span class="dn-filter-pisah"></span>
                    <button type="button" class="btn btn-dn-excel" id="md-btn-export">
                        <i class="fas fa-file-excel"></i> Export
                    </button>
                    <a href="{{ route('master-data-item-create') }}" class="btn btn-dn-create md-ke-form" data-dasar="{{ route('master-data-item-create') }}">
                        <i class="fas fa-plus"></i> Create
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Tabel ===== --}}
    <div class="card">
        <div class="card-body">
            <div class="dn-list-area" id="md-list-area">
                <div class="dn-loader-overlay">
                    <div class="dn-loader-kartu">
                        <span class="dn-loader-putar"></span> Loading...
                    </div>
                </div>
                <div class="dn-table-muat-wrap md-responsif">
                    <table id="md-table" class="dn-table dn-table-muat dn-tabel-daftar" style="width:100%">
                        <thead>
                            <tr>
                                <th>Item Code</th>
                                <th>Description</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Item Type</th>
                                <th>Inventory Mapping</th>
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
         Modal Show - terbuka dari tombol mata di kolom Action.
         =================================================================== --}}
    <div class="modal fade" id="md-modal-detail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header md-det-kepala">
                    <div class="md-det-avatar" id="md-det-avatar"><i class="fas fa-box"></i></div>
                    <div class="md-det-identitas">
                        <h5 class="md-det-nama" id="md-det-judul">Item</h5>
                        <div class="md-det-meta" id="md-det-meta"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="md-det-tab">
                    <ul class="nav" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link active" id="md-tab-btn-basic" data-bs-toggle="tab" data-bs-target="#md-tab-basic" role="tab">
                                <i class="fas fa-id-card"></i> Basic
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#md-tab-coa" role="tab">
                                <i class="fas fa-book"></i> Chart of Accounts
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#md-tab-gambar" role="tab">
                                <i class="fas fa-image"></i> Image
                                <span class="md-tab-cacah" id="md-cacah-gambar" hidden>1</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="modal-body">
                    <div class="dn-det-muat" id="md-det-muat">
                        <span class="dn-loader-putar"></span> Loading...
                    </div>
                    <div class="tab-content" id="md-det-isi" hidden>
                        <div class="tab-pane fade show active" id="md-tab-basic" role="tabpanel">
                            <div class="dn-det-judul"><i class="fas fa-fingerprint"></i> Identity</div>
                            <dl class="dn-det-info dn-det-info-4" id="md-det-identitas"></dl>
                            <div class="dn-det-judul"><i class="fas fa-layer-group"></i> Classification</div>
                            <dl class="dn-det-info dn-det-info-4 mb-0" id="md-det-klasifikasi"></dl>
                        </div>

                        <div class="tab-pane fade" id="md-tab-coa" role="tabpanel">
                            <div class="md-coa-ket" id="md-coa-ket" hidden>
                                <i class="fas fa-circle-info"></i>
                                <span>Chart of accounts is only required for PERSEDIAAN ATK and PERSEDIAAN UMUM.</span>
                            </div>
                            <div class="md-coa-grid" id="md-det-coa"></div>
                        </div>

                        <div class="tab-pane fade" id="md-tab-gambar" role="tabpanel">
                            <div class="md-gambar-wadah" id="md-det-gambar"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-dn-utama md-ke-form" id="md-det-ubah" hidden>
                        <i class="fas fa-pen"></i> Edit
                    </a>
                    <button type="button" class="btn btn-dn-kembali" data-bs-dismiss="modal">
                        <i class="fas fa-xmark"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================================
         Modal Create / Edit - dari tombol Create, tombol pensil di kolom Action
         dan tombol Edit di modal Show. Isian & aturannya mengikuti form Signalbit
         pages/others/master.php (mod=2). CoA wajib untuk mapping ATK & UMUM, selain
         itu dikunci "Not required" ('-'). Gambar baru disimpan di storage NDS.
         Tidak tertutup kalau klik di luar modal, supaya isian tidak hilang.
         =================================================================== --}}
    <div class="modal fade" id="md-modal-form" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header md-det-kepala">
                    <div class="md-det-avatar"><i class="fas fa-boxes-stacked" id="mf-ikon"></i></div>
                    <div class="md-det-identitas">
                        <h5 class="md-det-nama" id="mf-judul">Create Master Item General</h5>
                        <div class="md-det-meta" id="mf-meta"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="dn-det-muat" id="mf-muat" hidden>
                        <span class="dn-loader-putar"></span> Loading...
                    </div>
                    <div id="mf-isi">
                        {{-- ===== Data item: gambar di kiri, isian di kanan ===== --}}
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title"><i class="fas fa-box"></i> Item Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-3 col-md-4">
                                        <div class="form-group mb-0">
                                            <label>Image</label>
                                            <div class="md-pratinjau" id="mf-pratinjau"></div>
                                            <input type="file" id="mf-file" accept=".jpg,.jpeg,.png,.webp" hidden>
                                            <div class="md-unggah-aksi">
                                                <button type="button" class="btn btn-sm btn-md-pilih" id="mf-btn-pilih">
                                                    <i class="fas fa-upload"></i> Choose image
                                                </button>
                                                <button type="button" class="btn btn-sm btn-md-hapus" id="mf-btn-hapus-gambar" hidden>
                                                    <i class="fas fa-trash"></i> Remove
                                                </button>
                                            </div>
                                            <span class="md-nama-file" id="mf-nama-file"></span>
                                            <small class="md-ket"><i class="fas fa-circle-info"></i>JPG, PNG or WEBP.</small>
                                        </div>
                                    </div>

                                    <div class="col-lg-9 col-md-8">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="mf-kode">Item Code <span class="dn-wajib" title="Required">*</span></label>
                                                    <input type="text" class="form-control md-besar" id="mf-kode" name="goods_code"
                                                        maxlength="{{ $panjang['goods_code'] }}" placeholder="Item code" autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label for="mf-nama">Description <span class="dn-wajib" title="Required">*</span></label>
                                                    <input type="text" class="form-control md-besar" id="mf-nama" name="itemdesc"
                                                        maxlength="{{ $panjang['itemdesc'] }}" placeholder="Item description" autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="mf-warna">Color <span class="dn-wajib" title="Required">*</span></label>
                                                    <input type="text" class="form-control md-besar" id="mf-warna" name="color"
                                                        maxlength="{{ $panjang['color'] }}" placeholder="Use - if none" autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="mf-ukuran">Size <span class="dn-wajib" title="Required">*</span></label>
                                                    <input type="text" class="form-control md-besar" id="mf-ukuran" name="size"
                                                        maxlength="{{ $panjang['size'] }}" placeholder="Use - if none" autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="mf-tipe">Item Type</label>
                                                    <select class="form-control select2bs4" id="mf-tipe" name="tipe_item">
                                                        <option value="">-- Select item type --</option>
                                                        @foreach ($tipe as $t)
                                                            <option value="{{ $t }}">{{ $t }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="mf-mutasi">Mutation Type</label>
                                                    <select class="form-control select2bs4" id="mf-mutasi" name="tipe_mut">
                                                        <option value="">-- None --</option>
                                                        @foreach ($mutasi as $nilai => $label)
                                                            <option value="{{ $nilai }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="form-group mb-md-0">
                                                    <label for="mf-kategori">Inventory Mapping <span class="dn-wajib" title="Required">*</span></label>
                                                    <select class="form-control select2bs4" id="mf-kategori" name="n_code_category">
                                                        <option value="">-- Select inventory mapping --</option>
                                                        @foreach ($kategori as $id => $nama)
                                                            <option value="{{ $id }}">{{ $nama }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                {{-- Tanda wajib CoA - mengikuti mapping yang dipilih --}}
                                                <div class="md-coa-status" id="mf-coa-status"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ===== CoA (wajib untuk ATK & UMUM) + tombol ===== --}}
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="card-title"><i class="fas fa-book"></i> Chart of Accounts</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach ($coa as $kolom => $pilihan)
                                        <div class="col-lg-3 col-md-6">
                                            <div class="form-group">
                                                <label for="mf-{{ $kolom }}">{{ $coaLabel[$kolom] }} <span class="dn-wajib md-coa-wajib" title="Required" hidden>*</span></label>
                                                <select class="form-control select2bs4 md-coa" id="mf-{{ $kolom }}" name="{{ $kolom }}">
                                                    <option value="">-- Select account --</option>
                                                    <option value="-">Not required</option>
                                                    {{-- Akun lama yang tidak ada di daftar ditambahkan JS (kelas mf-lama) supaya tidak hilang --}}
                                                    @foreach ($pilihan as $no => $label)
                                                        <option value="{{ $no }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="dn-kaki">
                                    <button type="button" class="btn btn-dn-simpan" id="mf-btn-simpan">
                                        <i class="fa fa-save"></i> <span id="mf-kata-simpan">Save</span>
                                    </button>
                                    <button type="button" class="btn btn-dn-kembali" data-bs-dismiss="modal">
                                        <i class="fa fa-arrow-left"></i> Back
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
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
    var RUT_DATA   = @json(route('master-data-item-list'));
    var RUT_EXCEL  = @json(route('master-data-item-excel'));
    var RUT_DETAIL = @json(route('master-data-item-detail'));
    var RUT_STATUS = @json(route('master-data-item-status'));
    var RUT_EDIT   = @json(route('master-data-item-edit'));
    // Keadaan daftar dari alamat halaman (filter, cari, halaman, urutan)
    var AWAL       = @json($awal);

    // Select2 di modal Create / Edit dipasang terpisah (dropdown-nya harus di dalam modal)
    $('.dn-kartu-filter .select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

    function teks(nilai) {
        if (nilai == null || String(nilai).trim() === '') { return '-'; }
        return $('<div>').text(String(nilai).trim()).html();
    }

    function ada(nilai) {
        var s = String(nilai == null ? '' : nilai).trim();
        return s !== '' && s !== '-';
    }

    function filterSaatIni() {
        return {
            kategori: $('#md-kategori').val() || '',
            tipe:     $('#md-tipe').val() || '',
            status:   $('#md-status').val() || ''
        };
    }

    // Deskripsi bisa panjang - dua baris, selengkapnya lewat tooltip
    function klem(v, t) {
        if (t !== 'display') { return v || ''; }
        if (!ada(v)) { return '-'; }
        return '<span class="md-klem" title="' + teks(v) + '">' + teks(v) + '</span>';
    }

    function biasa(v, t) { return t === 'display' ? teks(v) : (v || ''); }

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
        { data: 'nama', render: klem },
        { data: 'color', render: biasa },
        { data: 'size', render: biasa },
        { data: 'tipe', render: biasa },
        { data: 'kategori', render: biasa },
        {
            data: 'non_aktif',
            className: 'dn-tengah',
            render: function (v, t) {
                var aktif = String(v) !== 'Y';
                if (t !== 'display') { return aktif ? 'ACTIVE' : 'INACTIVE'; }
                return '<span class="dn-pil ' + (aktif ? 'is-second' : 'is-batal') + '">'
                    + '<span class="dn-pil-titik"></span>' + (aktif ? 'Active' : 'Inactive') + '</span>';
            }
        },
        {
            data: null,
            orderable: false,
            className: 'dn-tengah',
            render: function (row) {
                // Layar lebar: tiga tombol; HP: satu tombol menu (lihat mdTombolAksi)
                return mdTombolAksi({ id: row.id, aktif: String(row.non_aktif) !== 'Y', urlEdit: RUT_EDIT });
            }
        }
    ];

    // Kolom yang tetap tampil di layar sempit (angka kecil = dipertahankan lebih lama):
    // deskripsi, kode, aksi, status. Sisanya masuk ke detail yang dibuka dengan tombol +.
    var PRIORITAS = { nama: 1, kode: 2, non_aktif: 4, tipe: 5, color: 6, size: 7, kategori: 8 };
    kolom.forEach(function (k, i) {
        k.responsivePriority = PRIORITAS[k.data] || (i === kolom.length - 1 ? 3 : 10);
    });

@include('master-data._responsif_js')

    // Filter atas yang terakhir dipakai memuat tabel (bukan pilihan yang belum di-Search)
    var filterTerpakai = filterSaatIni();

    var tabel = $('#md-table').DataTable({
        serverSide: true,
        searchDelay: 400,
        pageLength: AWAL.baris,
        lengthMenu: [10, 25, 50, 100],
        // Tanpa urutan pilihan: server mengurutkan item terbaru di atas
        order: AWAL.urut === null ? [] : [[AWAL.urut, AWAL.arah]],
        search: { search: AWAL.cari },
        displayStart: AWAL.hal * AWAL.baris,
        autoWidth: false,
        responsive: mdResponsif(),
        language: {
            emptyTable: '<div class="dn-kosong-isi"><i class="fas fa-boxes-stacked"></i><span>No item for this filter.</span></div>',
            zeroRecords: '<div class="dn-kosong-isi"><i class="fas fa-magnifying-glass"></i><span>No item matches your search.</span></div>'
        },
        dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: RUT_DATA,
            data: function (d) {
                // Hanya yang dipakai server; kolom-kolom bawaan DataTables tidak perlu ikut
                delete d.columns;
                filterTerpakai = filterSaatIni();
                $.extend(d, filterTerpakai);
            },
            error: function () {
                $('#md-list-area').removeClass('is-memuat');
            }
        },
        columns: kolom,
        createdRow: function (tr, row) {
            $(tr).toggleClass('md-nonaktif', String(row.non_aktif) === 'Y');
        }
    });

    tabel.on('preXhr.dt', function () { $('#md-list-area').addClass('is-memuat'); });
    tabel.on('xhr.dt draw.dt', function () { $('#md-list-area').removeClass('is-memuat'); });

    $('#md-btn-cari').on('click', function () {
        tabel.ajax.reload();
    });

    $('#md-btn-export').on('click', function () {
        mdExportExcel(RUT_EXCEL + '?' + $.param($.extend({ cari: tabel.search() }, filterTerpakai)));
    });

    // ---------- Create / Edit membawa keadaan daftar ----------
    function keadaanDaftar() {
        var urut = tabel.order()[0];
        var k = {
            kategori: filterTerpakai.kategori,
            tipe:     filterTerpakai.tipe,
            status:   filterTerpakai.status,
            cari:     tabel.search(),
            hal:      tabel.page(),
            baris:    tabel.page.len(),
            urut:     urut ? urut[0] : '',
            arah:     urut ? urut[1] : ''
        };
        var bawaan = { kategori: '', tipe: '', status: '', cari: '', hal: 0, baris: 10, urut: '', arah: 'asc' };
        Object.keys(bawaan).forEach(function (n) { if (String(k[n]) === String(bawaan[n]) || k[n] === '') { delete k[n]; } });
        return k;
    }

    // Alamat halaman ikut keadaan daftar, jadi tombol Back browser & refresh juga tidak menghapus filter
    tabel.on('draw.dt', function () {
        try {
            var q = $.param(keadaanDaftar());
            history.replaceState(history.state, '', location.pathname + (q ? '?' + q : ''));
        } catch (e) { /* browser lama tanpa history API: cukup tidak diperbarui */ }
    });

    // Create / Edit dibuka di modal. href tetap diisi supaya Ctrl+klik / klik tengah (tab baru)
    // juga jalan: alamat create / edit dialihkan server ke halaman ini dengan modalnya terbuka.
    $(document).on('mousedown click', 'a.md-ke-form', function (e) {
        var $a = $(this);
        var q = $.extend($a.data('id') ? { id: $a.data('id') } : {}, keadaanDaftar());
        this.href = $a.data('dasar') + ($.isEmptyObject(q) ? '' : '?' + $.param(q));
        if (e.type !== 'click' || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) { return; }

        e.preventDefault();
        var id = $a.data('id') || null;
        if ($a.closest('#md-modal-detail').length) {
            // Dari modal Show: tutup dulu, baru buka form (dua modal tidak ditumpuk)
            $('#md-modal-detail').one('hidden.bs.modal', function () { bukaForm(id); });
            modalDetail.hide();
        } else {
            bukaForm(id);
        }
    });

    // ---------- Show: modal detail ----------
    function barisInfo(label, isi, kelas) {
        return '<div' + (kelas ? ' class="' + kelas + '"' : '') + '><dt>' + label + '</dt><dd>' + teks(isi) + '</dd></div>';
    }

    var IKON_COA = ['fa-industry', 'fa-gears', 'fa-tag', 'fa-building'];
    var modalDetail = new bootstrap.Modal(document.getElementById('md-modal-detail'));

    $('#md-table').on('click', '.md-btn-lihat', function () {
        var id = $(this).data('id');

        bootstrap.Tab.getOrCreateInstance(document.getElementById('md-tab-btn-basic')).show();
        $('#md-det-judul').text('Item');
        $('#md-det-avatar').html('<i class="fas fa-box"></i>');
        $('#md-det-meta').empty();
        $('#md-cacah-gambar').prop('hidden', true);
        $('#md-det-ubah').prop('hidden', true);
        $('#md-det-muat').prop('hidden', false);
        $('#md-det-isi').prop('hidden', true);
        modalDetail.show();

        $.getJSON(RUT_DETAIL, { id: id }).done(function (res) {
            var d = res.data || {};
            var aktif = String(d.non_aktif) !== 'Y';

            // ----- Kepala -----
            $('#md-det-judul').text(d.itemdesc || 'Item').attr('title', d.itemdesc || '');
            if (res.gambar_url) {
                $('#md-det-avatar').html($('<img alt="">').attr('src', res.gambar_url)
                    .on('error', function () { $('#md-det-avatar').html('<i class="fas fa-box"></i>'); }));
            }
            $('#md-det-meta').html(''
                + (ada(d.goods_code) ? '<span class="md-det-chip is-kode">' + teks(d.goods_code) + '</span>' : '')
                + '<span class="md-det-chip"><i class="fas fa-hashtag"></i>ID ' + teks(d.id_item) + '</span>'
                + (ada(res.kategori_label) ? '<span class="md-det-chip"><i class="fas fa-warehouse"></i>' + teks(res.kategori_label) + '</span>' : '')
                + (ada(d.tipe_item) ? '<span class="md-det-chip"><i class="fas fa-tag"></i>' + teks(d.tipe_item) + '</span>' : '')
                + '<span class="dn-pil ' + (aktif ? 'is-second' : 'is-batal') + '"><span class="dn-pil-titik"></span>'
                + (aktif ? 'Active' : 'Inactive') + '</span>');

            // ----- Basic -----
            $('#md-det-identitas').html(''
                + barisInfo('Item Code', d.goods_code)
                + barisInfo('Description', d.itemdesc, 'md-penuh')
                + barisInfo('ID', d.id_item)
                + barisInfo('Color', d.color)
                + barisInfo('Size', d.size));
            $('#md-det-klasifikasi').html(''
                + barisInfo('Inventory Mapping', res.kategori_label, 'md-penuh')
                + barisInfo('Item Type', d.tipe_item)
                + barisInfo('Mutation Type', res.mutasi_label));

            // ----- Chart of Accounts -----
            $('#md-coa-ket').prop('hidden', !!res.wajib_coa);
            $('#md-det-coa').html((res.coa || []).map(function (c, i) {
                return '<div class="md-coa' + (c.no ? '' : ' is-kosong') + '">'
                    + '<span class="md-coa-ikon"><i class="fas ' + IKON_COA[i] + '"></i></span>'
                    + '<div><span>' + teks(c.label) + '</span>'
                    + (c.no ? '<b>' + teks(c.no) + '</b><small>' + (c.nama ? teks(c.nama) : '') + '</small>' : '<b>Not set</b>')
                    + '</div></div>';
            }).join(''));

            // ----- Image -----
            var $gambar = $('#md-det-gambar');
            if (res.gambar_url) {
                $('#md-cacah-gambar').prop('hidden', false);
                $gambar.html('<div class="text-center"></div>');
                $gambar.children().append($('<img alt="">').attr('src', res.gambar_url).on('error', function () {
                    $gambar.html('<div class="md-gambar-kosong"><i class="fas fa-image"></i>Image file not found.</div>');
                })).append('<div><a class="md-gambar-buka" target="_blank" rel="noopener" href="' + res.gambar_url + '">'
                    + '<i class="fas fa-up-right-from-square"></i> Open full size</a></div>');
            } else {
                $gambar.html('<div class="md-gambar-kosong"><i class="fas fa-image"></i>'
                    + (d.file_gambar ? 'Image file not found.' : 'No image for this item.') + '</div>');
            }

            $('#md-det-ubah').attr('href', RUT_EDIT + '?id=' + encodeURIComponent(d.id_item))
                .data({ dasar: RUT_EDIT, id: d.id_item }).prop('hidden', false);
            $('#md-det-muat').prop('hidden', true);
            $('#md-det-isi').prop('hidden', false);
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

    // ---------- Active / Inactive ----------
    $('#md-table').on('click', '.md-btn-status', function () {
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
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
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
    var RUT_SIMPAN   = @json(route('master-data-item-simpan'));
    var RUT_PERBARUI = @json(route('master-data-item-perbarui'));
    var WAJIB_COA    = @json($wajibCoa);
    var BATAS_SERVER = @json($batasUpload);   // batas upload PHP server - hanya untuk pesan galat
    var EKSTENSI     = @json($ekstensi);
    var BUKA_FORM    = @json($bukaForm);      // dari alamat lama create / edit?id=

    var $form = $('#md-modal-form');
    // Tanpa focus trap: swal yang tampil di atas modal tetap bisa difokus (Enter / Esc ke swal, bukan ke modal)
    var modalForm = new bootstrap.Modal($form[0], { focus: false });
    $form.find('.select2bs4').select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $form });

    // Item yang sedang diedit (null = create), gambar yang dipilih / dihapus
    var F = { ubah: null, adaGambar: false, fileBaru: null, hapusGambar: false, urlPratinjau: null };
    var muatKe = 0;   // respons edit yang terlambat (modal sudah dibuka lagi untuk item lain) diabaikan

    function kata() {
        return F.ubah
            ? { tanya: 'Update', ya: 'Yes, update it', proses: 'Updating...', selesai: 'updated', gagal: 'Not updated' }
            : { tanya: 'Save', ya: 'Yes, save it', proses: 'Saving...', selesai: 'saved', gagal: 'Not saved' };
    }
    function nilai(id) { return $.trim($(id).val() || ''); }

    // Pilih nilai kalau ada di daftar; kalau tidak ada, kembali ke pilihan kosong (sama dengan form lama)
    function pilih($s, v) {
        v = v == null ? '' : String(v);
        $s.val($s.find('option').filter(function () { return this.value === v; }).length ? v : '');
    }

    // ---------- CoA: wajib untuk ATK & UMUM, selain itu dikunci "Not required" ----------
    function wajibCoa() { return WAJIB_COA.indexOf(String($('#mf-kategori').val())) !== -1; }

    function aturCoa() {
        var wajib = wajibCoa();
        $form.find('.md-coa').each(function () {
            var $s = $(this);
            // Nilai dibaca sebelum opsi '-' dimatikan (opsi terpilih yang dimatikan terbaca null)
            if (wajib) {
                if ($s.val() === '-') { $s.val(''); }
            } else {
                $s.val('-');
            }
            $s.find('option[value="-"]').prop('disabled', wajib);
            $s.prop('disabled', !wajib).trigger('change.select2');
        });
        $form.find('.md-coa-wajib').prop('hidden', !wajib);
        // Keterangan di sebelah Inventory Mapping
        var $status = $('#mf-coa-status').toggleClass('is-wajib', wajib);
        if (!$('#mf-kategori').val()) {
            $status.empty();
        } else if (wajib) {
            $status.html('<i class="fas fa-circle-exclamation"></i><span>Chart of accounts is required for this mapping.</span>');
        } else {
            $status.html('<i class="fas fa-circle-info"></i><span>Chart of accounts is not required for this mapping.</span>');
        }
    }
    $('#mf-kategori').on('change', aturCoa);

    // ---------- Gambar ----------
    function tampilPratinjau(src, pesan) {
        var $p = $('#mf-pratinjau').empty();
        if (src) {
            $p.append($('<img alt="">').attr('src', src));
        } else {
            $p.html('<span><i class="fas fa-image"></i>' + teks(pesan) + '</span>');
        }
    }
    function lepasPratinjau() {
        if (F.urlPratinjau) { URL.revokeObjectURL(F.urlPratinjau); F.urlPratinjau = null; }
    }

    // Keadaan awal gambar: url = gambar tersimpan (null kalau filenya tidak ada), file = nama di masteritem
    function aturGambar(url, file) {
        lepasPratinjau();
        F.adaGambar = file !== '';
        F.fileBaru = null;
        F.hapusGambar = false;
        tampilPratinjau(url, F.adaGambar ? 'Image file not found' : 'No image');
        $('#mf-btn-pilih').html('<i class="fas fa-upload"></i> ' + (F.adaGambar ? 'Replace' : 'Choose image'));
        $('#mf-btn-hapus-gambar').prop('hidden', !F.adaGambar);
        $('#mf-nama-file').text(file).removeClass('is-hapus');
        $('#mf-file').val('');
    }

    $('#mf-btn-pilih').on('click', function () { $('#mf-file').val('').trigger('click'); });

    $('#mf-file').on('change', function () {
        var f = this.files && this.files[0];
        if (!f) { return; }
        var ext = (f.name.split('.').pop() || '').toLowerCase();
        // Ukuran tidak dibatasi; yang dicek hanya jenis filenya
        if (EKSTENSI.indexOf(ext) === -1) {
            Swal.fire({ icon: 'warning', title: 'Image not accepted', text: 'Image must be JPG, PNG or WEBP.', customClass: { popup: 'dn-swal' } });
            return;
        }
        lepasPratinjau();
        F.fileBaru = f;
        F.hapusGambar = false;
        F.urlPratinjau = URL.createObjectURL(f);
        tampilPratinjau(F.urlPratinjau);
        $('#mf-nama-file').text(f.name + ' (new)').removeClass('is-hapus');
        $('#mf-btn-hapus-gambar').prop('hidden', false);
    });

    $('#mf-btn-hapus-gambar').on('click', function () {
        lepasPratinjau();
        F.fileBaru = null;
        F.hapusGambar = F.adaGambar;
        tampilPratinjau(null, 'No image');
        $('#mf-nama-file').text(F.adaGambar ? 'Image will be removed when saved' : '').toggleClass('is-hapus', F.adaGambar);
        $(this).prop('hidden', true);
    });

    function keteranganGambar() {
        if (F.fileBaru) { return 'New image'; }
        if (F.hapusGambar) { return 'Removed'; }
        return F.adaGambar ? (F.ubah ? 'Unchanged' : '-') : 'None';
    }

    // ---------- Buka modal ----------
    // Kepala modal: d = data item (edit) atau null (create)
    function kepalaForm(d) {
        $('#mf-ikon').attr('class', 'fas ' + (d ? 'fa-pen-to-square' : 'fa-boxes-stacked'));
        $('#mf-judul').text((d ? 'Edit' : 'Create') + ' Master Item General');
        $('#mf-meta').html(!d
            ? '<span class="md-det-ket">Input form to add new general (non-production) item</span>'
            : '<span class="md-det-ket">Update general item data</span>'
                + (ada(d.goods_code) ? '<span class="md-det-chip is-kode">' + teks(d.goods_code) + '</span>' : '')
                + '<span class="md-det-chip"><i class="fas fa-hashtag"></i>ID ' + teks(d.id_item) + '</span>'
                + (String(d.non_aktif) === 'Y' ? '<span class="dn-pil is-batal"><span class="dn-pil-titik"></span>Inactive</span>' : ''));
        $('#mf-kata-simpan').text(d ? 'Update' : 'Save');
    }

    // Isi semua isian dari data item ({} untuk create)
    function isiForm(d) {
        $('#mf-kode').val(d.goods_code || '');
        $('#mf-nama').val(d.itemdesc || '');
        $('#mf-warna').val(d.color || '');
        $('#mf-ukuran').val(d.size || '');
        pilih($('#mf-tipe'), d.tipe_item);
        pilih($('#mf-mutasi'), d.tipe_mut);
        pilih($('#mf-kategori'), d.n_code_category);

        $form.find('.md-coa').each(function () {
            var $s = $(this);
            // Kunci dari keadaan sebelumnya dilepas dulu, akun lama sisa item sebelumnya dibuang
            $s.prop('disabled', false).find('option').prop('disabled', false);
            $s.find('option.mf-lama').remove();
            var v = $.trim(String(d[this.name] == null ? '' : d[this.name]));
            // Akun lama yang tidak ada di daftar tetap ditampilkan supaya tidak hilang
            if (v !== '' && !$s.find('option').filter(function () { return this.value === v; }).length) {
                $s.find('option[value="-"]').after($('<option class="mf-lama">').val(v).text(v));
            }
            $s.val(v);
        });

        $form.find('#mf-tipe, #mf-mutasi, #mf-kategori').trigger('change.select2');
        aturCoa();
    }

    function tampilIsi(siap) {
        $('#mf-muat').prop('hidden', siap);
        $('#mf-isi').prop('hidden', !siap);
        $('#mf-btn-simpan').prop('disabled', !siap);
    }

    var sedangTutup = false;   // modal yang masih beranimasi tutup mengabaikan show()

    function bukaForm(id) {
        if (sedangTutup) {
            $form.one('hidden.bs.modal', function () { bukaForm(id); });
            return;
        }
        var ke = ++muatKe;
        F.ubah = null;

        if (!id) {
            kepalaForm(null);
            isiForm({});
            aturGambar(null, '');
            tampilIsi(true);
            modalForm.show();
            return;
        }

        kepalaForm({ id_item: id });
        tampilIsi(false);
        modalForm.show();

        $.getJSON(RUT_DETAIL, { id: id }).done(function (res) {
            if (ke !== muatKe) { return; }
            var d = res.data || {};
            F.ubah = d;
            kepalaForm(d);
            isiForm(d);
            aturGambar(res.gambar_url, d.file_gambar || '');
            tampilIsi(true);
        }).fail(function (x) {
            if (ke !== muatKe) { return; }
            modalForm.hide();
            Swal.fire({
                icon: 'error',
                title: 'Failed to load item',
                text: (x.responseJSON && x.responseJSON.message) || 'Please try again.',
                customClass: { popup: 'dn-swal' }
            });
        });
    }

    // Respons yang masih jalan waktu modal ditutup tidak boleh mengisi form berikutnya
    $form.on('hide.bs.modal', function () { sedangTutup = true; });
    $form.on('hidden.bs.modal', function () { sedangTutup = false; muatKe++; lepasPratinjau(); });

    // ---------- Simpan ----------
    function dataKirim() {
        var fd = new FormData();
        if (F.ubah) { fd.append('id', F.ubah.id_item); }
        // Isian yang dikunci (CoA tidak wajib) tidak ikut terkirim - server mengisinya '-'
        $form.find('input[name], select[name]').each(function () {
            if (!this.disabled) { fd.append(this.name, $.trim($(this).val() || '')); }
        });
        if (F.fileBaru) { fd.append('gambar', F.fileBaru); }
        if (F.hapusGambar) { fd.append('hapus_gambar', 1); }
        return fd;
    }

    $('#mf-btn-simpan').on('click', function () {
        var K = kata();
        var kurang = [];
        if (!nilai('#mf-kode'))     { kurang.push('Item Code'); }
        if (!nilai('#mf-nama'))     { kurang.push('Description'); }
        if (!nilai('#mf-warna'))    { kurang.push('Color (use - if none)'); }
        if (!nilai('#mf-ukuran'))   { kurang.push('Size (use - if none)'); }
        if (!nilai('#mf-kategori')) { kurang.push('Inventory Mapping'); }
        if (wajibCoa()) {
            $form.find('.md-coa').each(function () {
                if (!$(this).val() || $(this).val() === '-') {
                    kurang.push('COA ' + $.trim($('label[for="' + this.id + '"]').clone().children().remove().end().text()));
                }
            });
        }

        if (kurang.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Please complete the form',
                html: 'The following must be filled in before ' + (F.ubah ? 'updating' : 'saving') + ':<ul class="dn-swal-kurang"><li>'
                    + kurang.join('</li><li>') + '</li></ul>',
                confirmButtonText: 'OK',
                customClass: { popup: 'dn-swal' }
            });
            return;
        }

        var $tb = $(this);
        var ringkas = '<div class="dn-swal-ringkas">'
            + '<div><span>Item Code</span><b>' + teks(nilai('#mf-kode').toUpperCase()) + '</b></div>'
            + '<div><span>Description</span><b>' + teks(nilai('#mf-nama').toUpperCase()) + '</b></div>'
            + '<div><span>Inventory Mapping</span><b>' + teks($('#mf-kategori').find(':selected').text()) + '</b></div>'
            + '<div><span>Item Type</span><b>' + teks(nilai('#mf-tipe')) + '</b></div>'
            + '<div><span>Image</span><b>' + teks(keteranganGambar()) + '</b></div>'
            + '</div>';

        Swal.fire({
            icon: 'question',
            title: K.tanya + ' this item?',
            html: ringkas,
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-save"></i> ' + K.ya,
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            focusCancel: true,
            customClass: { popup: 'dn-swal' }
        }).then(function (p) {
            if (p.isConfirmed) { kirimSimpan($tb, K); }
        });
    });

    function kirimSimpan($tb, K) {
        $tb.prop('disabled', true);
        Swal.fire({
            title: K.proses,
            allowOutsideClick: false, allowEscapeKey: false,
            showConfirmButton: false, customClass: { popup: 'dn-swal' },
            didOpen: function () { Swal.showLoading(); }
        });

        $.ajax({
            url: F.ubah ? RUT_PERBARUI : RUT_SIMPAN,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json' },
            data: dataKirim(),
            processData: false,
            contentType: false
        }).done(function (res) {
            if (F.ubah && res.berubah === 0) {
                $tb.prop('disabled', false);
                Swal.fire({
                    icon: 'info',
                    title: 'No changes',
                    html: 'Nothing was changed on <b>' + teks(res.kode) + '</b>.',
                    confirmButtonText: 'OK',
                    customClass: { popup: 'dn-swal' }
                });
                return;
            }
            Swal.fire({
                icon: 'success',
                title: 'Item ' + K.selesai,
                html: '<b>' + teks(res.kode) + ' - ' + teks(res.nama) + '</b> has been ' + K.selesai + '.',
                confirmButtonText: 'OK',
                allowOutsideClick: false,
                customClass: { popup: 'dn-swal' }
            }).then(function () {
                modalForm.hide();
                tabel.ajax.reload(null, false);   // tetap di halaman yang sama, seperti kembali dari form lama
            });
        }).fail(function (x) {
            $tb.prop('disabled', false);
            var res = x.responseJSON || {};
            var pesan = res.message || (x.status === 413 ? 'The image is larger than the server upload limit (' + BATAS_SERVER + ').' : 'Failed to ' + K.tanya.toLowerCase() + ', please try again.');
            var html = teks(pesan);
            if (res.kurang && res.kurang.length) {
                html += '<ul class="dn-swal-kurang"><li>' + res.kurang.map(teks).join('</li><li>') + '</li></ul>';
            }
            Swal.fire({
                icon: x.status === 409 ? 'warning' : 'error',
                title: x.status === 409 ? 'Already exists' : K.gagal,
                html: html,
                confirmButtonText: 'OK',
                customClass: { popup: 'dn-swal' }
            });
        });
    }

    if (BUKA_FORM) { bukaForm(BUKA_FORM === 'baru' ? null : BUKA_FORM); }
});
</script>
@endpush
@endsection
