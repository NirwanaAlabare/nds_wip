@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@include('export-import.invoice._skin')
<style>
@media (min-width: 992px) {
  .nag-skin.dn-daftar-grup .dn-tabel-daftar th:nth-child(1) { width: 22%; }
  .nag-skin.dn-daftar-grup .dn-tabel-daftar th:nth-child(2) { width: 30%; }
  .nag-skin.dn-daftar-grup .dn-tabel-daftar th:nth-child(3) { width: 12%; }
  .nag-skin.dn-daftar-grup .dn-tabel-daftar th:nth-child(4) { width: 15%; }
  .nag-skin.dn-daftar-grup .dn-tabel-daftar th:nth-child(5) { width: 9%; }
  .nag-skin.dn-daftar-grup .dn-tabel-daftar th:nth-child(6) { width: 12%; }
}
@include("master-data._gaya")
.nag-skin .md-label-lembut {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 6px;
  background: #f1f5f9;
  color: #334155;
  font-size: 12px;
  font-weight: 600;
}
.nag-skin .dn-tabel-daftar tbody tr.md-nonaktif .md-label-lembut { color: #94a3b8; }

/* Modal Create / Edit */
#md-modal-form .md-det-ket { color: #cbd5e1; font-size: 12px; }
#md-modal-form .card:last-child { margin-bottom: 0; }
.nag-skin .md-ket { display: block; margin-top: 4px; font-size: 11px; color: #64748b; }
.nag-skin .md-ket i { margin-right: 4px; color: #94a3b8; }
.nag-skin .md-besar { text-transform: uppercase; }
.nag-skin .md-besar::placeholder { text-transform: none; }
/* Form ringkas: satu kolom, lebar sedang */
#md-modal-form .md-form-ringkas { max-width: 560px; }
#md-modal-form .modal-body { padding: 20px 22px; }
#md-modal-form .modal-footer { padding: 12px 22px; }
/* ===== Kartu sukses simpan (tutup sendiri) =====
   Animasi sengaja tidak dibatasi prefers-reduced-motion (PC kantor mematikan animation effects). */
.md-sukses-popup.swal2-popup { width: 380px; padding: 26px 22px 22px; border-radius: 20px; overflow: hidden; }
.md-sukses-popup .swal2-html-container { margin: 0; }
.md-sukses-popup .swal2-timer-progress-bar { background: linear-gradient(90deg, #16a34a, #22c55e); height: 4px; }
.md-sukses { display: flex; flex-direction: column; align-items: center; gap: 8px; }
.md-sukses-ikon { width: 72px; height: 72px; margin-bottom: 4px; animation: md-sukses-pop .45s cubic-bezier(.34, 1.56, .64, 1) both; }
.md-sukses-ikon svg { width: 100%; height: 100%; }
.md-sukses-lingkar {
  fill: #ecfdf3; stroke: #16a34a; stroke-width: 2.5;
  stroke-dasharray: 151; stroke-dashoffset: 151;
  animation: md-sukses-garis .5s ease-out .1s forwards;
}
.md-sukses-centang {
  fill: none; stroke: #16a34a; stroke-width: 4; stroke-linecap: round; stroke-linejoin: round;
  stroke-dasharray: 36; stroke-dashoffset: 36;
  animation: md-sukses-garis .35s ease-out .5s forwards;
}
@keyframes md-sukses-garis { to { stroke-dashoffset: 0; } }
@keyframes md-sukses-pop { from { transform: scale(.4); opacity: 0; } to { transform: none; opacity: 1; } }
@keyframes md-sukses-naik { from { transform: translateY(8px); opacity: 0; } to { transform: none; opacity: 1; } }
.md-sukses > :not(.md-sukses-ikon) { animation: md-sukses-naik .35s ease both; }
.md-sukses > :nth-child(2) { animation-delay: .25s; }
.md-sukses > :nth-child(3) { animation-delay: .32s; }
.md-sukses > :nth-child(4) { animation-delay: .38s; }
.md-sukses > :nth-child(5) { animation-delay: .44s; }
.md-sukses > :nth-child(6) { animation-delay: .5s; }
.md-sukses-judul { font-size: 18px; font-weight: 700; color: #0f2942; }
.md-sukses-kode {
  padding: 3px 12px; border-radius: 999px;
  background: #1e3a5f; color: #fff; font-size: 12.5px; font-weight: 700; letter-spacing: .6px;
}
.md-sukses-nama { font-size: 15px; font-weight: 700; color: #1e293b; line-height: 1.3; }
.md-sukses-chip { display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; }
.md-sukses-chip span {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 4px 10px; border-radius: 8px;
  background: #eef2f7; color: #334155; font-size: 12px; font-weight: 600;
}
.md-sukses-chip span i { font-size: 10.5px; color: #64748b; }
.md-sukses-chip span.is-baru { background: #dbeafe; color: #1d4ed8; }
.md-sukses-chip span.is-baru i { color: #1d4ed8; }
.md-sukses-chip.is-redup span { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; font-weight: 600; }

/* ===== Combobox (Group & Customs): tampil seperti dropdown, isinya tetap bisa diketik / diedit ===== */
.md-combo { position: relative; }
.md-combo .form-control { padding-right: 52px; cursor: text; }
.md-combo-tombol {
  position: absolute; top: 0; right: 0; bottom: 0; width: 32px;
  display: flex; align-items: center; justify-content: center;
  border: 0; background: transparent; color: #475569; font-size: 11px;
}
.md-combo-tombol i { transition: transform .2s ease; }
.md-combo.is-buka .md-combo-tombol i { transform: rotate(180deg); }
.md-combo-hapus {
  position: absolute; top: 50%; right: 30px; transform: translateY(-50%);
  width: 18px; height: 18px; padding: 0; border: 0; border-radius: 50%;
  background: transparent; color: #94a3b8; font-size: 12px; line-height: 1;
}
.md-combo-hapus:hover { color: #475569; }
.md-combo-hapus[hidden] { display: none; }
.md-combo-daftar {
  position: absolute; left: 0; right: 0; top: calc(100% + 4px); z-index: 1060;
  max-height: 220px; overflow-y: auto; margin: 0; padding: 4px; list-style: none;
  background: #fff; border: 1px solid #cbd5e1; border-radius: 10px;
  box-shadow: 0 12px 28px -12px rgba(15, 41, 66, .35);
  animation: md-combo-muncul .14s ease both;
}
.md-combo-daftar[hidden] { display: none; }
@keyframes md-combo-muncul { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: none; } }
.md-combo-daftar li {
  display: flex; align-items: center; gap: 8px;
  padding: 7px 10px; border-radius: 7px; cursor: pointer;
  font-size: 13px; color: #1e293b;
}
.md-combo-daftar li small { margin-left: auto; color: #94a3b8; font-size: 11px; font-weight: 600; }
.md-combo-daftar li.is-sorot { background: #1e3a5f; color: #fff; }
.md-combo-daftar li.is-sorot small { color: #cbd5e1; }
.md-combo-daftar li.is-pilih:not(.is-sorot) { background: #eef2f7; font-weight: 600; }
.md-combo-daftar li.is-baru { color: #1d4ed8; font-weight: 600; }
.md-combo-daftar li.is-baru.is-sorot { color: #fff; }
.md-combo-daftar li.is-kosong { cursor: default; color: #94a3b8; font-style: italic; }
/* Tombol hapus group kosong di dalam daftar */
.md-combo-buang {
  flex: 0 0 auto; width: 24px; height: 24px; padding: 0; margin-left: 2px;
  border: 0; border-radius: 6px; background: transparent; color: #dc2626; font-size: 11.5px;
}
.md-combo-buang:hover { background: #fee2e2; }
.md-combo-daftar li.is-sorot .md-combo-buang { color: #fecaca; }
.md-combo-daftar li.is-sorot .md-combo-buang:hover { background: rgba(255, 255, 255, .15); color: #fff; }
.dn-swal .md-tombol-hapus { background: #dc2626 !important; }

/* Popup pilihan Rename group / Move to new group */
.dn-swal .md-pilih-grup { margin-top: 12px; text-align: left; font-size: 12.5px; color: #475569; }
.dn-swal .md-pilih-grup p { margin: 0 0 6px; padding: 8px 10px; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0; }
.dn-swal .md-tombol-pindah { background: #0d9488 !important; }

/* Tanda kecil di bawah Group kalau namanya belum ada (akan dibuat baru) */
.nag-skin .md-tanda-baru { display: block; margin-top: 4px; font-size: 11.5px; font-weight: 600; color: #1d4ed8; }
.nag-skin .md-tanda-baru[hidden] { display: none; }
</style>
@endsection

@section('content')
{{-- ===========================================================================
     Master Group General (database nds): master_general_groups + master_general_subgroups.
     Satu baris daftar = satu sub group. Kode otomatis: group GG1, GG2 ... / sub group GSG1, GSG2 ...
     Aksi per baris: Show, Active/Inactive (kolom cancel), Edit.
     =========================================================================== --}}
<div class="nag-skin dn-kontrol-sm dn-daftar-grup">

    <div class="dn-kepala-halaman">
        <div class="dn-kepala-kiri">
            <span class="dn-kepala-ikon"><i class="fas fa-layer-group"></i></span>
            <div>
                <h1 class="dn-kepala-judul">Master Group General</h1>
                <p class="dn-kepala-sub">Group, sub group, accounting &amp; customs of general items</p>
            </div>
        </div>
    </div>

    {{-- ===== Filter ===== --}}
    <div class="card dn-kartu-filter">
        <div class="card-body">
            <div class="dn-filter-petak">
                <div class="form-group dn-f-lebar">
                    <label for="md-grup">Group</label>
                    <select class="form-control select2bs4" id="md-grup">
                        <option value="">ALL</option>
                        @foreach ($pilihan['grup'] as $g)
                            <option value="{{ $g->id }}">{{ $g->nama }}</option>
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
                    <a href="#" class="btn btn-dn-create md-ke-form">
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
                                <th>Group</th>
                                <th>Sub Group</th>
                                <th>Accounting</th>
                                <th>Customs</th>
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
         Modal Create / Edit - satu form: Group, Sub Group, Accounting, Customs.
         Tidak tertutup kalau klik di luar modal, supaya isian tidak hilang.
         =================================================================== --}}
    <div class="modal fade" id="md-modal-form" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        {{-- Tanpa modal-dialog-scrollable: daftar combobox tidak terpotong oleh badan modal --}}
        <div class="modal-dialog md-form-ringkas">
            <div class="modal-content">
                <div class="modal-header md-det-kepala">
                    <div class="md-det-avatar"><i class="fas fa-layer-group" id="mf-ikon"></i></div>
                    <div class="md-det-identitas">
                        <h5 class="md-det-nama" id="mf-judul">Create Group General</h5>
                        <div class="md-det-meta" id="mf-meta"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    {{-- Group & Customs: kotak teks biasa (bisa diedit langsung) dengan saran dari data yang ada.
                         Nama group yang belum ada dibuat jadi group baru saat disimpan. --}}
                    <div class="form-group">
                        <label for="mf-group">Group <span class="dn-wajib" title="Required">*</span></label>
                        <div class="md-combo">
                            <input type="text" class="form-control md-besar" id="mf-group" maxlength="100"
                                placeholder="Select or type a group" autocomplete="off">
                        </div>
                        <small class="md-tanda-baru" id="mf-tanda-grup" hidden><i class="fas fa-circle-plus"></i> New group - will be created when saved</small>
                    </div>
                    <div class="form-group">
                        <label for="mf-sub">Sub Group <span class="dn-wajib" title="Required">*</span></label>
                        <input type="text" class="form-control md-besar" id="mf-sub" maxlength="100" placeholder="Sub group name" autocomplete="off">
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group mb-sm-0">
                                <label for="mf-accounting">Accounting <span class="dn-wajib" title="Required">*</span></label>
                                <select class="form-control" id="mf-accounting"></select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group mb-0">
                                <label for="mf-customs">Customs <span class="dn-wajib" title="Required">*</span></label>
                                <div class="md-combo">
                                    <input type="text" class="form-control md-besar" id="mf-customs" maxlength="100"
                                        placeholder="Select or type" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dn-kembali" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-dn-simpan" id="mf-btn-simpan">
                        <i class="fa fa-save"></i> <span id="mf-kata-simpan">Save</span>
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
    var RUT_DATA     = @json(route('master-data-group-general-list'));
    var RUT_EXCEL    = @json(route('master-data-group-general-excel'));
    var RUT_STATUS   = @json(route('master-data-group-general-status'));
    var RUT_SIMPAN   = @json(route('master-data-group-general-simpan'));
    var RUT_HAPUS_GRUP = @json(route('master-data-group-general-hapus-grup'));
    var RUT_PERBARUI = @json(route('master-data-group-general-perbarui'));
    var PILIHAN      = @json($pilihan);
    var AWAL         = @json($awal);   // dari dashboard: status, cari, bukaForm

    $('.dn-kartu-filter .select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

    function teks(nilai) {
        if (nilai == null || String(nilai).trim() === '') { return '-'; }
        return $('<div>').text(String(nilai).trim()).html();
    }

    function aktif(row) { return String(row.cancel || '').toUpperCase() !== 'Y'; }

    function filterSaatIni() {
        return { grup: $('#md-grup').val() || '', status: $('#md-status').val() || '' };
    }

    function lembut(v, t) {
        if (t !== 'display') { return v || ''; }
        return v ? '<span class="md-label-lembut">' + teks(v) + '</span>' : '-';
    }

    var kolom = [
        {
            data: 'general_group_name',
            render: function (v, t, row) {
                if (t !== 'display') { return (v || '') + ' ' + (row.general_group_code || ''); }
                return '<span class="md-kode">' + teks(v) + '</span><span class="md-sub">' + teks(row.general_group_code) + '</span>';
            }
        },
        {
            data: 'general_subgroup_name',
            render: function (v, t, row) {
                if (t !== 'display') { return (v || '') + ' ' + (row.general_subgroup_code || ''); }
                return '<span class="md-nama">' + teks(v) + '</span><span class="md-sub">' + teks(row.general_subgroup_code) + '</span>';
            }
        },
        { data: 'accounting_type', render: lembut },
        { data: 'customs_type', render: lembut },
        {
            data: 'cancel',
            className: 'dn-tengah',
            render: function (v, t, row) {
                var a = aktif(row);
                if (t !== 'display') { return a ? 'ACTIVE' : 'INACTIVE'; }
                return '<span class="dn-pil ' + (a ? 'is-second' : 'is-batal') + '">'
                    + '<span class="dn-pil-titik"></span>' + (a ? 'Active' : 'Inactive') + '</span>';
            }
        },
        {
            data: null,
            orderable: false,
            searchable: false,
            className: 'dn-tengah',
            render: function (row) {
                return mdTombolAksi({ id: row.id, aktif: aktif(row), urlEdit: '#' });
            }
        }
    ];

    var PRIORITAS = { general_subgroup_name: 1, general_group_name: 2, cancel: 4, accounting_type: 5, customs_type: 6 };
    kolom.forEach(function (k, i) {
        k.responsivePriority = PRIORITAS[k.data] || (i === kolom.length - 1 ? 3 : 10);
    });

@include('master-data._responsif_js')

    var filterTerpakai = filterSaatIni();

    // Data sedikit: diambil semua, pencarian & halaman di browser.
    // Urutan bawaan dari server (group lalu sub group menurut kapan dibuat) - sama dengan Excel.
    var tabel = $('#md-table').DataTable({
        pageLength: 25,
        search: { search: AWAL.cari },   // quick search dashboard (?cari=)
        lengthMenu: [10, 25, 50, 100],
        order: [],
        autoWidth: false,
        responsive: mdResponsif(),
        language: {
            emptyTable: '<div class="dn-kosong-isi"><i class="fas fa-layer-group"></i><span>No group yet. Click Create to add one.</span></div>',
            zeroRecords: '<div class="dn-kosong-isi"><i class="fas fa-magnifying-glass"></i><span>No group matches your search.</span></div>'
        },
        dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: RUT_DATA,
            data: function () {
                filterTerpakai = filterSaatIni();
                return filterTerpakai;
            },
            dataSrc: function (res) {
                if (res.pilihan) { PILIHAN = res.pilihan; segarkanFilterGrup(); }
                return res.data || [];
            },
            error: function () {
                $('#md-list-area').removeClass('is-memuat');
            }
        },
        columns: kolom,
        createdRow: function (tr, row) {
            $(tr).toggleClass('md-nonaktif', !aktif(row));
        }
    });

    tabel.on('preXhr.dt', function () { $('#md-list-area').addClass('is-memuat'); });
    tabel.on('xhr.dt draw.dt', function () { $('#md-list-area').removeClass('is-memuat'); });

    // Group baru yang baru disimpan ikut muncul di filter (pilihan yang sedang dipilih dipertahankan)
    function segarkanFilterGrup() {
        var $s = $('#md-grup');
        var pilih = $s.val() || '';
        $s.empty().append(new Option('ALL', ''));
        (PILIHAN.grup || []).forEach(function (g) { $s.append(new Option(g.nama, g.id)); });
        $s.val(pilih).trigger('change.select2');
    }

    $('#md-btn-cari').on('click', function () { tabel.ajax.reload(); });

    $('#md-btn-export').on('click', function () {
        mdExportExcel(RUT_EXCEL + '?' + $.param($.extend({ cari: tabel.search() }, filterTerpakai)));
    });

    // ---------- Show: ringkasan baris ----------
    $('#md-table').on('click', '.md-btn-lihat', function () {
        var row = mdBarisDari(tabel, this);
        Swal.fire({
            title: teks(row.general_subgroup_name),
            html: '<div class="dn-swal-ringkas">'
                + '<div><span>Group</span><b>' + teks(row.general_group_code) + ' - ' + teks(row.general_group_name) + '</b></div>'
                + '<div><span>Sub Group</span><b>' + teks(row.general_subgroup_code) + ' - ' + teks(row.general_subgroup_name) + '</b></div>'
                + '<div><span>Accounting</span><b>' + teks(row.accounting_type) + '</b></div>'
                + '<div><span>Customs</span><b>' + teks(row.customs_type) + '</b></div>'
                + '<div><span>Status</span><b>' + (aktif(row) ? 'Active' : 'Inactive') + '</b></div>'
                + '</div>',
            confirmButtonText: 'Close',
            customClass: { popup: 'dn-swal' }
        });
    });

    // ---------- Active / Inactive ----------
    $('#md-table').on('click', '.md-btn-status', function () {
        var row = mdBarisDari(tabel, this);
        var keAktif = !aktif(row);

        Swal.fire({
            icon: 'question',
            title: keAktif ? 'Set this sub group Active?' : 'Set this sub group Inactive?',
            html: '<div class="dn-swal-ringkas">'
                + '<div><span>Group</span><b>' + teks(row.general_group_name) + '</b></div>'
                + '<div><span>Sub Group</span><b>' + teks(row.general_subgroup_name) + '</b></div>'
                + '<div><span>New status</span><b>' + (keAktif ? 'Active' : 'Inactive') + '</b></div>'
                + '</div>',
            showCancelButton: true,
            confirmButtonText: keAktif ? '<i class="fas fa-toggle-on"></i> Yes, set Active' : '<i class="fas fa-toggle-off"></i> Yes, set Inactive',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            focusCancel: true,
            customClass: { popup: 'dn-swal' }
        }).then(function (p) {
            if (!p.isConfirmed) { return; }

            $.ajax({
                url: RUT_STATUS,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { id: row.id, aktif: keAktif ? 1 : 0 }
            }).done(function (res) {
                Swal.fire({
                    icon: 'success',
                    title: res.aktif ? 'Set to Active' : 'Set to Inactive',
                    html: '<b>' + teks(res.kode) + ' - ' + teks(res.sub) + '</b> is now ' + (res.aktif ? 'Active' : 'Inactive') + '.',
                    timer: 1800,
                    showConfirmButton: false,
                    customClass: { popup: 'dn-swal' }
                });
                tabel.ajax.reload(null, false);
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
    var ubah = null;

    // Accounting: pilihan tetap EXPENSE / ASSET
    $('#mf-accounting').select2({
        theme: 'bootstrap4',
        width: '100%',
        dropdownParent: $form,
        placeholder: 'Select accounting',
        minimumResultsForSearch: Infinity
    });

    function rapiBesar(v) { return $.trim(String(v || '')).replace(/\s+/g, ' ').toUpperCase(); }

    // ---------- Combobox Group & Customs ----------
    // Tampil seperti dropdown (klik = daftar pilihan yang ada, ketik = menyaring), tapi isinya tetap
    // kotak teks: bisa diedit langsung kalau salah, dan nama baru bisa diketik.
    // o = { daftar: function () -> [{ nilai, ket }], baru: label pilihan nilai baru }
    function pasangCombo($in, o) {
        var $wadah = $in.closest('.md-combo');
        var $hapus = $('<button type="button" class="md-combo-hapus" tabindex="-1" title="Clear" hidden><i class="fas fa-circle-xmark"></i></button>');
        var $tombol = $('<button type="button" class="md-combo-tombol" tabindex="-1" title="Show list"><i class="fas fa-chevron-down"></i></button>');
        var $ul = $('<ul class="md-combo-daftar" role="listbox" hidden></ul>');
        $wadah.append($hapus, $tombol, $ul);
        var item = [];
        var sorot = -1;

        function segarHapus() { $hapus.prop('hidden', $in.val() === ''); }

        // saring = false: semua pilihan (waktu baru dibuka); true: disaring sesuai yang diketik
        function gambar(saring) {
            var sekarang = rapiBesar($in.val());
            var semua = o.daftar();
            item = semua.filter(function (x) { return !saring || !sekarang || rapiBesar(x.nilai).indexOf(sekarang) !== -1; });
            var persis = semua.some(function (x) { return rapiBesar(x.nilai) === sekarang; });
            if (sekarang && !persis) { item.unshift({ nilai: sekarang, baru: true }); }

            $ul.empty();
            if (!item.length) {
                $ul.append($('<li class="is-kosong">').text(semua.length ? 'No match' : 'No data yet - type a new one'));
            }
            item.forEach(function (x, i) {
                var $li = $('<li role="option">').attr('data-i', i);
                if (x.baru) {
                    $li.addClass('is-baru').text('+ ' + o.baru + ' "' + x.nilai + '"');
                } else {
                    $li.text(x.nilai).toggleClass('is-pilih', rapiBesar(x.nilai) === sekarang);
                    if (x.ket) { $li.append($('<small>').text(x.ket)); }
                    // Pilihan yang boleh dihapus (group kosong): tombol tempat sampah
                    if (x.bisaHapus && o.hapus) {
                        $li.append($('<button type="button" class="md-combo-buang" title="Delete this empty group">')
                            .html('<i class="fas fa-trash-can"></i>'));
                    }
                }
                $ul.append($li);
            });
            sorot = -1;
        }

        function buka(saring) { gambar(saring); $ul.prop('hidden', false); $wadah.addClass('is-buka'); }
        function tutup() { $ul.prop('hidden', true); $wadah.removeClass('is-buka'); }
        function terbuka() { return !$ul.prop('hidden'); }

        function setSorot(i) {
            var $li = $ul.children('li[data-i]');
            if (!$li.length) { return; }
            sorot = (i + $li.length) % $li.length;
            $li.removeClass('is-sorot').eq(sorot).addClass('is-sorot')[0].scrollIntoView({ block: 'nearest' });
        }

        function pilih(i) {
            var x = item[i];
            if (!x) { return; }
            $in.val(x.nilai).trigger('change');
            tutup();
        }

        $in.on('focus click', function () { if (!terbuka()) { buka(false); } });
        $in.on('input', function () { buka(true); segarHapus(); });
        $in.on('change', segarHapus);
        $in.on('blur', tutup);
        $in.on('keydown', function (e) {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                if (!terbuka()) { buka(false); }
                setSorot(sorot + (e.key === 'ArrowDown' ? 1 : -1));
            } else if (e.key === 'Enter' && terbuka() && sorot >= 0) {
                e.preventDefault();
                pilih(sorot);
            } else if (e.key === 'Escape' && terbuka()) {
                e.stopPropagation();   // Esc menutup daftar dulu, bukan modal
                tutup();
            } else if (e.key === 'Tab') {
                tutup();
            }
        });
        // mousedown + preventDefault: fokus tidak lepas dari isian (daftar tidak tertutup oleh blur)
        $tombol.on('mousedown', function (e) {
            e.preventDefault();
            if (terbuka()) { tutup(); } else { $in.trigger('focus'); buka(false); }
        });
        $ul.on('mousedown', '.md-combo-buang', function (e) {
            e.preventDefault();
            e.stopPropagation();   // bukan memilih baris
            var x = item[parseInt($(this).closest('li').attr('data-i'), 10)];
            tutup();
            if (x) { o.hapus(x); }
        });
        $ul.on('mousedown', 'li', function (e) {
            e.preventDefault();
            if ($(this).is('[data-i]')) { pilih(parseInt($(this).attr('data-i'), 10)); }
        });
        $hapus.on('mousedown', function (e) {
            e.preventDefault();
            $in.val('').trigger('change').trigger('focus');
            buka(false);
        });
    }

    pasangCombo($('#mf-group'), {
        daftar: function () {
            return (PILIHAN.grup || []).map(function (g) {
                var kosong = (parseInt(g.jumlah, 10) || 0) === 0;
                return { id: g.id, kode: g.kode, nilai: g.nama, ket: g.kode + (kosong ? ' · empty' : ''), bisaHapus: kosong };
            });
        },
        baru: 'Create group',
        hapus: hapusGrupKosong
    });

    // Hapus group kosong (mis. salah ketik). Server menolak kalau ternyata masih punya sub group.
    function hapusGrupKosong(x) {
        Swal.fire({
            icon: 'warning',
            title: 'Delete this group?',
            html: '<div class="dn-swal-ringkas">'
                + '<div><span>Group</span><b>' + teks(x.kode) + ' · ' + teks(x.nilai) + '</b></div>'
                + '<div><span>Sub groups</span><b>None</b></div>'
                + '</div>'
                + '<p class="mt-2 mb-0" style="font-size:12.5px;color:#64748b">This group has no sub group. It will be removed permanently.</p>',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash-can"></i> Yes, delete',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            focusCancel: true,
            showLoaderOnConfirm: true,
            allowOutsideClick: function () { return !Swal.isLoading(); },
            customClass: { popup: 'dn-swal', confirmButton: 'md-tombol-hapus' },
            preConfirm: function () {
                return $.ajax({
                    url: RUT_HAPUS_GRUP,
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json' },
                    data: { id: x.id }
                }).then(function (res) { return res; }, function (r) {
                    Swal.showValidationMessage((r.responseJSON && r.responseJSON.message) || 'Failed to delete, please try again.');
                    return false;
                });
            }
        }).then(function (p) {
            if (!p.isConfirmed || !p.value) { return; }
            PILIHAN = p.value.pilihan || PILIHAN;
            segarkanFilterGrup();
            $('#mf-group').trigger('change');   // tanda "New group" ikut menyesuaikan
            Swal.fire({
                icon: 'success',
                title: 'Group deleted',
                html: '<b>' + teks(p.value.kode_grup) + ' · ' + teks(p.value.group) + '</b> has been deleted.',
                timer: 1800,
                timerProgressBar: true,
                showConfirmButton: false,
                customClass: { popup: 'dn-swal' }
            });
        });
    }
    pasangCombo($('#mf-customs'), {
        daftar: function () { return (PILIHAN.customs || []).map(function (c) { return { nilai: c }; }); },
        baru: 'Add'
    });

    // Nama group yang belum ada = group baru (dibuat saat disimpan)
    function grupBaru() {
        var t = rapiBesar($('#mf-group').val());
        return t !== '' && !(PILIHAN.grup || []).some(function (g) { return rapiBesar(g.nama) === t; });
    }
    $('#mf-group').on('input change', function () {
        // Saat Edit: nama baru bisa jadi ganti nama group lama atau group baru - dipilih waktu Update
        $('#mf-tanda-grup').prop('hidden', !grupBaru()).html(ubah
            ? '<i class="fas fa-circle-info"></i> New name - you can rename this group or move to a new group when updating'
            : '<i class="fas fa-circle-plus"></i> New group - will be created when saved');
    });

    function isiPilihan(id, daftar, v) {
        var $s = $(id).empty().append(new Option('', '', false, false));
        (daftar || []).forEach(function (x) { $s.append(new Option(x, x, false, false)); });
        v = v == null ? '' : String(v);
        if (v !== '' && (daftar || []).indexOf(v) === -1) { $s.append(new Option(v, v, false, false)); }
        $s.val(v).trigger('change');
    }

    function kata() {
        return ubah
            ? { tanya: 'Update', ya: 'Yes, update it', proses: 'Updating...', selesai: 'updated', gagal: 'Not updated' }
            : { tanya: 'Save', ya: 'Yes, save it', proses: 'Saving...', selesai: 'saved', gagal: 'Not saved' };
    }

    // d = data baris (edit) atau null (create)
    function bukaForm(d) {
        ubah = d || null;
        $('#mf-ikon').attr('class', 'fas ' + (d ? 'fa-pen-to-square' : 'fa-layer-group'));
        $('#mf-judul').text((d ? 'Edit' : 'Create') + ' Group General');
        $('#mf-meta').html(!d
            ? '<span class="md-det-ket">Input form to add a new sub group</span>'
            : '<span class="md-det-ket">Update sub group data</span>'
                + '<span class="md-det-chip is-kode">' + teks(d.general_subgroup_code) + '</span>'
                + (aktif(d) ? '' : '<span class="dn-pil is-batal"><span class="dn-pil-titik"></span>Inactive</span>'));
        $('#mf-kata-simpan').text(d ? 'Update' : 'Save');

        d = d || {};
        $('#mf-group').val(d.general_group_name || '').trigger('change');
        $('#mf-tanda-grup').prop('hidden', true);
        $('#mf-sub').val(d.general_subgroup_name || '');
        isiPilihan('#mf-accounting', PILIHAN.accounting, d.accounting_type);
        $('#mf-customs').val(d.customs_type || '').trigger('change');
        $('#mf-btn-simpan').prop('disabled', false);
        modalForm.show();
    }

    $(document).on('click', 'a.md-ke-form', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (!id) { bukaForm(null); return; }
        var row = mdBarisDari(tabel, this);
        if (row && row.id) { bukaForm(row); }
    });

    function nilai(id) { return $.trim($(id).val() || ''); }

    function teksGrup() {
        return rapiBesar($('#mf-group').val()) + (grupBaru() ? ' (new)' : '');
    }

    $('#mf-btn-simpan').on('click', function () {
        var K = kata();
        var kurang = [];
        if (!nilai('#mf-group'))      { kurang.push('Group'); }
        if (!nilai('#mf-sub'))        { kurang.push('Sub Group'); }
        if (!nilai('#mf-accounting')) { kurang.push('Accounting'); }
        if (!nilai('#mf-customs'))    { kurang.push('Customs'); }

        if (kurang.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Please complete the form',
                html: 'The following must be filled in before ' + (ubah ? 'updating' : 'saving') + ':<ul class="dn-swal-kurang"><li>'
                    + kurang.join('</li><li>') + '</li></ul>',
                confirmButtonText: 'OK',
                customClass: { popup: 'dn-swal' }
            });
            return;
        }

        var $tb = $(this);

        // Edit + nama group diganti ke nama yang belum ada: ganti nama group lama, atau pindah ke group baru?
        var grupLama = ubah ? (PILIHAN.grup || []).filter(function (g) { return String(g.id) === String(ubah.group_id); })[0] : null;
        if (ubah && grupLama && grupBaru()) {
            var namaBaru = rapiBesar(nilai('#mf-group'));
            var jumlah = parseInt(grupLama.jumlah, 10) || 1;
            Swal.fire({
                icon: 'question',
                title: 'Group name changed',
                html: '<div class="dn-swal-ringkas">'
                    + '<div><span>Current group</span><b>' + teks(grupLama.kode) + ' · ' + teks(grupLama.nama) + '</b></div>'
                    + '<div><span>New name</span><b>' + teks(namaBaru) + '</b></div>'
                    + '</div>'
                    + '<div class="md-pilih-grup">'
                    + '<p><b>Rename group</b> - fix the name of ' + teks(grupLama.kode) + '. Code stays, '
                    + (jumlah > 1 ? 'all <b>' + jumlah + ' sub groups</b> in it follow.' : 'its sub group follows.') + '</p>'
                    + '<p><b>Move to new group</b> - create new group ' + teks(namaBaru) + ' and move only this sub group.</p>'
                    + '</div>',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: '<i class="fas fa-pen"></i> Rename group',
                denyButtonText: '<i class="fas fa-right-from-bracket"></i> Move to new group',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                focusCancel: true,
                customClass: { popup: 'dn-swal', denyButton: 'md-tombol-pindah' }
            }).then(function (p) {
                if (p.isConfirmed) { kirimSimpan($tb, K, true); }
                else if (p.isDenied) { kirimSimpan($tb, K, false); }
            });
            return;
        }

        Swal.fire({
            icon: 'question',
            title: K.tanya + ' this sub group?',
            html: '<div class="dn-swal-ringkas">'
                + '<div><span>Group</span><b>' + teks(teksGrup()) + '</b></div>'
                + '<div><span>Sub Group</span><b>' + teks(nilai('#mf-sub').toUpperCase()) + '</b></div>'
                + '<div><span>Accounting</span><b>' + teks(nilai('#mf-accounting')) + '</b></div>'
                + '<div><span>Customs</span><b>' + teks(rapiBesar(nilai('#mf-customs'))) + '</b></div>'
                + '</div>',
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

    // Isi kartu sukses: centang beranimasi, kode sub group, nama, group (+ tanda group baru), accounting & customs
    function kartuSukses(res, K) {
        return '<div class="md-sukses">'
            + '<div class="md-sukses-ikon"><svg viewBox="0 0 52 52" aria-hidden="true">'
            + '<circle class="md-sukses-lingkar" cx="26" cy="26" r="24"/>'
            + '<path class="md-sukses-centang" d="M15 27l7 7 15-15"/></svg></div>'
            + '<div class="md-sukses-judul">Sub group ' + K.selesai + '!</div>'
            + '<div class="md-sukses-kode">' + teks(res.kode) + '</div>'
            + '<div class="md-sukses-nama">' + teks(res.sub) + '</div>'
            + '<div class="md-sukses-chip">'
            + '<span><i class="fas fa-layer-group"></i>' + teks(res.kode_grup) + ' · ' + teks(res.group) + '</span>'
            + (res.grup_baru ? '<span class="is-baru"><i class="fas fa-circle-plus"></i>New group</span>' : '')
            + (res.grup_ganti ? '<span class="is-baru"><i class="fas fa-pen"></i>Group renamed</span>' : '')
            + '</div>'
            + '<div class="md-sukses-chip is-redup">'
            + '<span>' + teks(res.accounting) + '</span><span>' + teks(res.customs) + '</span>'
            + '</div>'
            + '</div>';
    }

    // gantiNamaGrup: true = nama group lama yang diganti (Rename group), bukan membuat group baru
    function kirimSimpan($tb, K, gantiNamaGrup) {
        $tb.prop('disabled', true);
        Swal.fire({
            title: K.proses,
            allowOutsideClick: false, allowEscapeKey: false,
            showConfirmButton: false, customClass: { popup: 'dn-swal' },
            didOpen: function () { Swal.showLoading(); }
        });

        var data = {
            group:      nilai('#mf-group'),
            sub_group:  nilai('#mf-sub'),
            accounting: nilai('#mf-accounting'),
            customs:    nilai('#mf-customs')
        };
        if (ubah) { data.id = ubah.id; }
        if (gantiNamaGrup) { data.ganti_nama_grup = 1; }

        $.ajax({
            url: ubah ? RUT_PERBARUI : RUT_SIMPAN,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json' },
            data: data
        }).done(function (res) {
            if (ubah && res.berubah === 0) {
                $tb.prop('disabled', false);
                Swal.fire({
                    icon: 'info',
                    title: 'No changes',
                    html: 'Nothing was changed on <b>' + teks(res.kode) + ' - ' + teks(res.sub) + '</b>.',
                    confirmButtonText: 'OK',
                    customClass: { popup: 'dn-swal' }
                });
                return;
            }
            // Langsung tutup form & muat ulang daftar; kartu sukses tampil sebentar lalu hilang sendiri
            modalForm.hide();
            tabel.ajax.reload(null, false);
            Swal.fire({
                html: kartuSukses(res, K),
                showConfirmButton: false,
                timer: 2600,
                timerProgressBar: true,
                customClass: { popup: 'dn-swal md-sukses-popup' }
            });
        }).fail(function (x) {
            $tb.prop('disabled', false);
            var res = x.responseJSON || {};
            var html = teks(res.message || ('Failed to ' + K.tanya.toLowerCase() + ', please try again.'));
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

    // Tombol New di kartu dashboard (?form=baru): form Create langsung terbuka
    if (AWAL.bukaForm) { bukaForm(null); }
});
</script>
@endpush
@endsection
