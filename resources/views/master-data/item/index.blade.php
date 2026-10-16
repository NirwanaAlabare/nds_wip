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
    // Keadaan daftar saat kembali dari Create / Edit (filter, cari, halaman, urutan)
    var AWAL       = @json($awal);

    $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

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
        window.location.href = RUT_EXCEL + '?' + $.param($.extend({ cari: tabel.search() }, filterTerpakai));
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

    $(document).on('mousedown click', 'a.md-ke-form', function () {
        var $a = $(this);
        var q = $.extend($a.data('id') ? { id: $a.data('id') } : {}, keadaanDaftar());
        this.href = $a.data('dasar') + ($.isEmptyObject(q) ? '' : '?' + $.param(q));
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
});
</script>
@endpush
@endsection
