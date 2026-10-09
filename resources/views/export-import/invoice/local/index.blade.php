@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@include('export-import.invoice._skin')
@endsection

@section('content')
{{-- ===========================================================================
     Daftar Invoice Local.
     Kolomnya mengikuti menu Booking Invoice di aplikasi AR, tampilannya
     mengikuti skin Debit Note (lihat ../_skin.blade.php).
     Daftar Invoice Export punya berkasnya sendiri: ../export/index.blade.php.
     =========================================================================== --}}
{{-- dn-kontrol-sm: tombol, isian & select2 ukuran kecil - sama di semua halaman invoice (lihat _skin). --}}
<div class="nag-skin dn-kontrol-sm dn-daftar-local">

    {{-- ===== Kepala halaman =====
         Judul layar saja; tombolnya ikut di baris filter - bentuknya sama dengan
         daftar Invoice Export. --}}
    <div class="dn-kepala-halaman">
        <div class="dn-kepala-kiri">
            <span class="dn-kepala-ikon"><i class="fas fa-file-invoice"></i></span>
            <div>
                <h1 class="dn-kepala-judul">Invoice Local</h1>
                <p class="dn-kepala-sub">Booking invoice local EXIM</p>
            </div>
        </div>
    </div>

    {{-- ===== Filter ===== --}}
    <div class="card dn-kartu-filter">
        <div class="card-body">
            {{-- Urutannya: yang paling sering dipakai menyaring dulu (Shipped To,
                 Status), rentang tanggalnya di kanan, tombol di ujung. --}}
            <div class="dn-filter-petak">
                <div class="form-group dn-f-lebar">
                    <label for="inv-customer">Shipped To</label>
                    <select class="form-control select2bs4" id="inv-customer" name="inv-customer">
                        <option value="">ALL</option>
                        @foreach ($customer as $c)
                            <option value="{{ $c->Id_Supplier }}">{{ $c->Supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group dn-f-sedang">
                    <label for="inv-status">Status</label>
                    <select class="form-control select2bs4" id="inv-status" name="inv-status">
                        <option value="">ALL</option>
                        <option value="CANCEL">CANCEL</option>
                        <option value="DRAFT">DRAFT</option>
                        <option value="POST">POST</option>
                        <option value="FIRST APPROVED">FIRST APPROVED</option>
                        <option value="SECOND APPROVED">SECOND APPROVED</option>
                    </select>
                </div>
                <div class="form-group dn-f-tgl">
                    <label for="inv-tgl-awal">Invoice Date From</label>
                    {{-- Tampil dd/mm/yyyy; nilainya diambil lewat tglIso() - lihat _kalender. --}}
                    <input type="text" class="form-control dn-tgl" id="inv-tgl-awal" name="inv-tgl-awal"
                        value="{{ now()->format('j M Y') }}" autocomplete="off">
                </div>
                <div class="form-group dn-f-tgl">
                    <label for="inv-tgl-akhir">Invoice Date To</label>
                    <input type="text" class="form-control dn-tgl" id="inv-tgl-akhir" name="inv-tgl-akhir"
                        value="{{ now()->format('j M Y') }}" autocomplete="off">
                </div>
                <div class="dn-filter-tombol">
                    <button type="button" class="btn btn-dn-utama" id="inv-btn-cari">
                        <i class="fa fa-search"></i> Search
                    </button>
                    <span class="dn-filter-pisah"></span>
                    <button type="button" class="btn btn-dn-excel" id="inv-btn-export">
                        <i class="fas fa-file-excel"></i> Export
                    </button>
                    <button type="button" class="btn btn-dn-create" id="inv-btn-buat">
                        <i class="fas fa-plus"></i> Create
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- ===== Tabel ===== --}}
    <div class="card">
        <div class="card-body">
            <div class="dn-list-area" id="inv-list-area">
                <div class="dn-loader-overlay">
                    <div class="dn-loader-kartu">
                        <span class="dn-loader-putar"></span> Loading...
                    </div>
                </div>
                <div class="dn-table-muat-wrap">
                    <table id="inv-table" class="dn-table dn-table-muat dn-tabel-daftar" style="width:100%">
                        <thead>
                            {{-- Kolom Shipp tidak ditampilkan: di daftar ini isinya
                                 selalu "Local". Urutannya sama dengan daftar Export -
                                 tanggal di depan, Status menempel ke Action. --}}
                            <tr>
                                <th>Inv Number</th>
                                <th>Invoice Date</th>
                                <th>Billed To</th>
                                <th>Shipped To</th>
                                <th>Type</th>
                                <th>Doc Type</th>
                                <th>Doc Number</th>
                                <th class="dn-angka">Value</th>
                                <th class="dn-tengah">Status</th>
                                <th class="dn-tengah">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    {{-- ===================================================================
         Modal rincian invoice - terbuka saat nomor invoice di tabel diklik.
         =================================================================== --}}
    <div class="modal fade" id="modal-inv-detail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable dn-modal-rincian">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-invoice"></i>
                        <span id="det-judul">Invoice</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- Pita ringkasan: di luar badan yang digulir, jadi status, tanggal &
                     grand total tetap terlihat sampai bawah. --}}
                <div class="dn-det-pita-ringkas" id="det-pita" hidden>
                    <div class="dn-pita-butir">
                        <span class="dn-pita-label">Status</span>
                        <span id="det-pita-status"></span>
                    </div>
                    <div class="dn-pita-butir">
                        <span class="dn-pita-label">Invoice Date</span>
                        <b id="det-pita-tgl">-</b>
                    </div>
                    <div class="dn-pita-butir">
                        <span class="dn-pita-label">SJ Rows</span>
                        <b id="det-pita-baris">0</b>
                    </div>
                    <div class="dn-pita-butir dn-pita-total">
                        <span class="dn-pita-label">Grand Total</span>
                        <b id="det-pita-grand">0.00</b>
                    </div>
                </div>

                <div class="modal-body">
                    <div class="dn-det-muat" id="det-muat">
                        <span class="dn-loader-putar"></span> Loading...
                    </div>

                    <div id="det-isi" hidden>
                        <dl class="dn-det-info" id="det-info"></dl>

                        <div class="dn-det-judul"><i class="fas fa-table"></i> Detail SJ</div>
                        <div class="dn-table-scroll dn-table-tinggi">
                            <table class="dn-table" id="det-tabel" style="width:100%">
                                <thead>
                                    {{-- Source dibuang: satu invoice satu profit center.
                                         SJ Number diisi nomor FG/OUT-nya, jadi kolom Shipping
                                         Number tidak perlu lagi. --}}
                                    <tr>
                                        <th>SO Number</th>
                                        <th>SJ Number</th>
                                        <th>SJ Date</th>
                                        <th>WS#</th>
                                        <th>Style No</th>
                                        <th>Product Item</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>UOM</th>
                                        <th class="dn-angka">Qty</th>
                                        <th class="dn-angka">Unit Price</th>
                                        <th class="dn-angka">Discount (%)</th>
                                        <th class="dn-angka">Total Price</th>
                                        {{-- Nilai TAGIH knitting - lihat _skin. --}}
                                        <th class="det-sel-tagih">UOM Billing</th>
                                        <th class="dn-angka det-sel-tagih">Qty Billing</th>
                                        <th class="dn-angka det-sel-tagih">Unit Price Billing</th>
                                        <th class="dn-angka det-sel-tagih">Total Price Billing</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        {{-- Rekap: yang selalu terlihat cuma Grand Total; rinciannya
                             dibuka kalau perlu - sama pola dengan Detail SJ di modal Export. --}}
                        <div class="row justify-content-end">
                            <div class="col-lg-6 col-md-9">
                                <div class="dn-det-rekap-lipat">
                                    <div class="dn-lipat-strip">
                                        <span class="dn-sj-label"><i class="fas fa-calculator"></i> Summary</span>
                                        <button type="button" class="btn btn-dn-lipat" id="det-btn-lipat-rekap"
                                            aria-expanded="false" aria-controls="det-ringkas">
                                            <span id="det-btn-lipat-rekap-teks">Show details</span>
                                            <i class="fas fa-chevron-down dn-lipat-panah"></i>
                                        </button>
                                    </div>
                                    <div class="dn-det-ringkas" id="det-ringkas" hidden></div>
                                    <div class="dn-det-ringkas dn-det-ringkas-grand" id="det-ringkas-grand"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Riwayat: tertutup dulu. Yang dicari biasanya isi
                             invoice-nya; riwayatnya dibuka kalau perlu, dan isinya
                             baru diminta ke server saat itu. --}}
                        <div class="dn-det-rekap-lipat dn-riw-wadah-lipat">
                            <div class="dn-lipat-strip">
                                <span class="dn-sj-label">
                                    <i class="fas fa-clock-rotate-left"></i> History
                                </span>
                                <button type="button" class="btn btn-dn-lipat" id="det-btn-lipat-riw"
                                    aria-expanded="false" aria-controls="det-riw">
                                    <span id="det-btn-lipat-riw-teks">Show details</span>
                                    <i class="fas fa-chevron-down dn-lipat-panah"></i>
                                </button>
                            </div>
                            <div id="det-riw" hidden>
                                @include('export-import.invoice._riwayat')
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-dn-kembali" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i> Close
                    </button>
                    {{-- Tiga bentuk cetakan, sama untuk PDF & Excel: Detail (per style &
                         warna, seperti di AR), Summary (per produk) dan Knitting (khusus
                         NAK - barisnya disembunyikan untuk profit center lain). --}}
                    <div class="dropdown dn-pilih-versi dn-det-cetak" id="det-btn-pdf">
                        <button type="button" class="btn btn-dn-cetak dropdown-toggle" data-bs-toggle="dropdown"
                            data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dn-menu-versi">
                            <li><a class="dropdown-item" href="#" id="det-btn-pdf-rinci" target="_blank" rel="noopener">
                                <b>Detail</b><span>Per style &amp; colour</span></a></li>
                            <li><a class="dropdown-item" href="#" id="det-btn-pdf-ringkas" target="_blank" rel="noopener">
                                <b>Summary</b><span>Per product</span></a></li>
                            <li id="det-pdf-knit-baris"><a class="dropdown-item" href="#" id="det-btn-pdf-knit"
                                target="_blank" rel="noopener">
                                <b>Knitting &ndash; Shipment</b><span>Qty &amp; price as shipped</span></a></li>
                            <li id="det-pdf-knitb-baris"><a class="dropdown-item" href="#" id="det-btn-pdf-knitb"
                                target="_blank" rel="noopener">
                                <b>Knitting &ndash; Billing</b><span>Qty &amp; price as billed</span></a></li>
                        </ul>
                    </div>
                    {{-- PDF Classic: tampilan tanpa CARING, untuk pembanding. --}}
                    <div class="dropdown dn-pilih-versi dn-det-cetak" id="det-btn-pdfl">
                        <button type="button" class="btn btn-dn-cetak-lama dropdown-toggle" data-bs-toggle="dropdown"
                            data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false"
                            title="PDF without CARING styling">
                            <i class="fas fa-file-pdf"></i> PDF Classic
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dn-menu-versi">
                            <li><a class="dropdown-item" href="#" id="det-btn-pdfl-rinci" target="_blank" rel="noopener">
                                <b>Detail</b><span>Per style &amp; colour</span></a></li>
                            <li><a class="dropdown-item" href="#" id="det-btn-pdfl-ringkas" target="_blank" rel="noopener">
                                <b>Summary</b><span>Per product</span></a></li>
                            <li id="det-pdfl-knit-baris"><a class="dropdown-item" href="#" id="det-btn-pdfl-knit"
                                target="_blank" rel="noopener">
                                <b>Knitting</b><span>Consignor / consignee form</span></a></li>
                        </ul>
                    </div>
                    <div class="dropdown dn-pilih-versi dn-det-cetak" id="det-btn-xls">
                        <button type="button" class="btn btn-dn-excel dropdown-toggle" data-bs-toggle="dropdown"
                            data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dn-menu-versi">
                            <li><a class="dropdown-item" href="#" id="det-btn-xls-rinci">
                                <b>Detail</b><span>Per style &amp; colour</span></a></li>
                            <li><a class="dropdown-item" href="#" id="det-btn-xls-ringkas">
                                <b>Summary</b><span>Per product</span></a></li>
                            <li id="det-xls-knit-baris"><a class="dropdown-item" href="#" id="det-btn-xls-knit">
                                <b>Knitting</b><span>Consignor / consignee form</span></a></li>
                        </ul>
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
<script>
$(function () {
    var RUT_DATA   = @json(route('invoice-exim-local-data'));
    var RUT_DETAIL = @json(route('invoice-exim-local-detail'));
    var RUT_PDF    = @json(route('invoice-exim-local-pdf'));
    var RUT_PDF_R  = @json(route('invoice-exim-local-pdf-ringkas'));
    var RUT_PDF_K  = @json(route('invoice-exim-local-pdf-knitting'));
    var RUT_XLS    = @json(route('invoice-exim-local-excel'));
    var RUT_XLS_R  = @json(route('invoice-exim-local-excel-ringkas'));
    var RUT_XLS_K  = @json(route('invoice-exim-local-excel-knitting'));
    var RUT_XLS_DAFTAR = @json(route('invoice-exim-local-excel-daftar'));
    var POLA_EDIT  = @json(route('invoice-exim-local-edit', ['id' => '__ID__']));
    var RUT_BATAL  = @json(route('invoice-exim-local-batal'));
    var RUT_BUAT  = @json(route('invoice-exim-local-create'));

    $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

    // ---------- tanggal ----------
    @include('export-import.invoice._kalender')

    // Status dipetakan ke warna pil yang sama dengan Debit Note.
    function kelasStatus(status) {
        var s = String(status == null ? '' : status).trim().toUpperCase();
        if (s === 'DRAFT') { return 'is-draft'; }
        if (s === 'POST') { return 'is-post'; }
        if (s === 'FIRST APPROVED') { return 'is-first'; }
        if (s === 'SECOND APPROVED') { return 'is-second'; }
        if (s === 'CANCEL' || s === 'CANCELED' || s === 'CANCELLED') { return 'is-batal'; }
        return 'is-lain';
    }

    // Tanggal dari server yyyy-mm-dd; di layar ditulis '12 Sep 2026' - sama
    // dengan daftar Invoice Export. Pengurutan tetap memakai nilai aslinya.
    var BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    function tglTampil(v, t) {
        if (t !== 'display') { return v || ''; }
        var p = /^(\d{4})-(\d{2})-(\d{2})/.exec(v || '');
        return p ? p[3] + ' ' + BULAN[parseInt(p[2], 10) - 1] + ' ' + p[1] : teks(v);
    }

    function teks(nilai) {
        if (nilai == null || nilai === '') { return '-'; }
        return $('<div>').text(nilai).html();
    }

    function uang(nilai) {
        var n = parseFloat(nilai);
        if (isNaN(n)) { return teks(nilai); }
        return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    /**
     * Tombol cetak (PDF atau Excel) dengan pilihan bentuknya: Detail, Summary,
     * dan Knitting khusus invoice NAK. Pilihan PDF & Excel sengaja dibuat dari
     * satu fungsi supaya selalu sama. Popper-nya memakai strategi fixed supaya
     * menunya tidak terpotong oleh wadah tabel yang bisa digulir.
     */
    // jenis: 'pdf' = PDF gaya CARING, 'pdf-lama' = PDF Classic (tanpa CARING),
    // 'xls' = Excel.
    function menuCetak(jenis, id, pc) {
        var lama = jenis === 'pdf-lama';
        var pdf = jenis === 'pdf' || lama;
        var rute = pdf ? [RUT_PDF, RUT_PDF_R, RUT_PDF_K] : [RUT_XLS, RUT_XLS_R, RUT_XLS_K];
        var buka = pdf ? ' target="_blank" rel="noopener"' : '';
        var ekor = lama ? '&gaya=lama' : '';
        // tambahan: ruas alamat ekstra (dipakai versi cetakan knitting).
        function item(url, judul, ket, tambahan) {
            return '<li><a class="dropdown-item" href="' + url + '?id=' + id + ekor
                + (tambahan || '') + '"' + buka + '>'
                + '<b>' + judul + '</b><span>' + ket + '</span></a></li>';
        }
        var judulTombol = lama ? 'Print PDF Classic - without CARING'
            : (pdf ? 'Print PDF' : 'Download Excel') + ' (detail / summary)';
        return '<div class="dropdown dn-pilih-versi">'
            + '<button type="button" class="btn dn-ikon ' + (pdf ? 'dn-ikon-pdf' : 'dn-ikon-excel')
            + (lama ? ' dn-ikon-pdf-lama' : '') + '"'
            + ' data-bs-toggle="dropdown" data-bs-popper-config=\'{"strategy":"fixed"}\' aria-expanded="false"'
            + ' title="' + judulTombol + '">'
            + '<i class="fas ' + (pdf ? 'fa-file-pdf' : 'fa-file-excel') + '"></i>'
            + '<i class="fas fa-angle-down dn-ikon-panah"></i>'
            + '</button>'
            + '<ul class="dropdown-menu dn-menu-versi">'
            + item(rute[0], 'Detail', 'Per style &amp; colour')
            + item(rute[1], 'Summary', 'Per product')
            // Bentuk knitting cuma untuk invoice NAK, dan ada DUA: knitting
            // memang punya dua angka (nilai kirim & nilai tagih). Bentuk
            // dokumennya sama persis - yang berbeda cuma angkanya.
            + (String(pc || '').trim().toUpperCase() === 'NAK'
                ? item(rute[2], 'Knitting &ndash; Shipment', 'Qty &amp; price as shipped')
                  + item(rute[2], 'Knitting &ndash; Billing', 'Qty &amp; price as billed', '&versi=tagih')
                : '')
            + '</ul></div>';
    }

    var tabel = $('#inv-table').DataTable({
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [[1, 'desc']],
        autoWidth: false,
        {{-- Keterangan daftar kosong dibuat jelas, tanpa tombol: Create sudah
             ada di baris filter di atas, tidak perlu diulang di tengah tabel. --}}
        language: {
            emptyTable: '<div class="dn-kosong-isi">'
                + '<i class="fas fa-file-invoice"></i>'
                + '<span>No invoice in this date range.</span></div>',
            zeroRecords: '<div class="dn-kosong-isi">'
                + '<i class="fas fa-magnifying-glass"></i>'
                + '<span>No invoice matches your search.</span></div>'
        },
        dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: RUT_DATA,
            data: function (d) {
                d.tgl_awal  = tglIso('#inv-tgl-awal');
                d.tgl_akhir = tglIso('#inv-tgl-akhir');
                d.customer  = $('#inv-customer').val();
                d.status    = $('#inv-status').val();
            },
            dataSrc: function (res) {
                return (res && res.data) ? res.data : [];
            },
            error: function () {
                $('#inv-list-area').removeClass('is-memuat');
            }
        },
        columns: [
            {
                data: 'no_invoice',
                render: function (v, t, row) {
                    // Pengurutan & pencarian tetap memakai nilai aslinya.
                    if (t !== 'display') { return v || ''; }
                    return '<button type="button" class="dn-no-link" data-id="' + teks(row.id) + '">'
                        + teks(v) + '</button>';
                }
            },
            { data: 'tanggal',       render: tglTampil },
            { data: 'customer',      render: teks },
            { data: 'customer_ship', render: function (v, t, row) { return teks(v || row.customer); } },
            { data: 'type',          render: teks },
            { data: 'doc_type',      render: teks },
            { data: 'doc_number',    render: teks },
            { data: 'value', className: 'dn-angka dn-nilai', render: uang },
            {
                data: 'status',
                className: 'dn-tengah',
                render: function (v) {
                    // Pil kecil bertitik - sama dengan daftar Invoice Export.
                    return '<span class="dn-pil ' + kelasStatus(v) + '">'
                        + '<span class="dn-pil-titik"></span>' + teks(v) + '</span>';
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (row) {
                    var id     = encodeURIComponent(row.id);
                    var status = String(row.status || '').toUpperCase();

                    // Yang dibatalkan tidak boleh punya cetakan yang beredar,
                    // jadi PDF & Excel disembunyikan. Rinciannya tetap bisa
                    // dilihat lewat nomor invoice di kolom pertama.
                    if (status === 'CANCEL') {
                        return '<div class="dn-aksi"><span class="dn-diproses">Cancelled</span></div>';
                    }

                    var isi = menuCetak('pdf', id, row.profit_center) + menuCetak('pdf-lama', id, row.profit_center)
                        + menuCetak('xls', id, row.profit_center);
                    // Ubah & batal hanya untuk yang masih draft, sama seperti
                    // aturan di Booking Invoice aplikasi AR.
                    if (status === 'DRAFT') {
                        isi += '<a class="btn dn-ikon dn-ikon-ubah btn-inv-ubah" title="Edit invoice" href="' +
                            POLA_EDIT.replace('__ID__', id) + '"><i class="fas fa-pen"></i></a>' +
                            '<button type="button" class="btn dn-ikon dn-ikon-batal btn-inv-batal"' +
                            ' title="Cancel invoice"' +
                            ' data-id="' + teks(row.id) + '"' +
                            ' data-no="' + teks(row.no_invoice) + '"' +
                            ' data-nilai="' + teks(uang(row.value)) + '">' +
                            '<i class="fas fa-times"></i></button>';
                    }
                    return '<div class="dn-aksi">' + isi + '</div>';
                }
            }
        ]
    });

    tabel.on('preXhr.dt', function () { $('#inv-list-area').addClass('is-memuat'); });
    tabel.on('xhr.dt draw.dt', function () { $('#inv-list-area').removeClass('is-memuat'); });

    $('#inv-btn-cari').on('click', function () { tabel.ajax.reload(); });

    // Export Excel dibuat lewat DataTables Buttons, tapi tombolnya tombol kita
    // sendiri supaya warnanya tetap sama dengan Debit Note.
    // Excel dibuat di server: ada judul, periode, tabel bergaris & kolom nilai
    // sebagai angka - bentuknya sama dengan List Invoice di AR. Yang dikirim
    // filter yang sedang dipakai, jadi isinya persis yang terlihat di layar.
    $('#inv-btn-export').on('click', function () {
        var p = $.param({
            tgl_awal:  tglIso('#inv-tgl-awal'),
            tgl_akhir: tglIso('#inv-tgl-akhir'),
            customer:  $('#inv-customer').val() || '',
            status:    $('#inv-status').val() || ''
        });
        window.location.href = RUT_XLS_DAFTAR + '?' + p;
    });

    $('#inv-btn-buat').on('click', function () {
        window.location.href = RUT_BUAT;
    });

    // ================= Batalkan invoice =================
    // Sekali dibatalkan, SJ-nya lepas dan bisa dipakai invoice lain - jadi
    // ditanya dulu, dan yang ditampilkan nomor & nilainya, bukan cuma "yakin?".
    $('#inv-table').on('click', '.btn-inv-batal', function () {
        var $tb = $(this);
        var id  = $tb.data('id');
        var no  = $tb.data('no') || '';

        Swal.fire({
            icon: 'warning',
            title: 'Cancel this invoice?',
            html: '<div class="dn-swal-ringkas">'
                + '<div><span>Invoice</span><b>' + teks(no) + '</b></div>'
                + '<div><span>Grand Total</span><b>' + teks($tb.data('nilai')) + '</b></div>'
                + '</div>'
                + '<p class="dn-swal-catatan">Its status becomes <b>CANCEL</b> and the SJ rows'
                + ' it used can be picked again by another invoice. This cannot be undone here.</p>',
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-times"></i> Yes, cancel it',
            cancelButtonText: 'No, keep it',
            reverseButtons: true,
            focusCancel: true,
            customClass: { popup: 'dn-swal' }
        }).then(function (pilih) {
            if (!pilih.isConfirmed) { return; }

            $tb.prop('disabled', true);
            Swal.fire({
                title: 'Cancelling...', allowOutsideClick: false, allowEscapeKey: false,
                showConfirmButton: false, customClass: { popup: 'dn-swal' },
                didOpen: function () { Swal.showLoading(); }
            });

            $.ajax({
                url: RUT_BATAL,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { id: id }
            }).done(function (res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Invoice cancelled',
                    html: 'Invoice <b>' + teks(res.no_invoice || no) + '</b> is now <b>CANCEL</b>.'
                        + ' Its SJ rows are available again.',
                    confirmButtonText: 'OK',
                    customClass: { popup: 'dn-swal' }
                }).then(function () { tabel.ajax.reload(null, false); });
            }).fail(function (x) {
                $tb.prop('disabled', false);
                var p = (x.responseJSON && x.responseJSON.pesan) ? x.responseJSON.pesan : 'Cancel failed, please try again.';
                Swal.fire({ icon: 'error', title: 'Cancel failed', text: p, customClass: { popup: 'dn-swal' } });
            });
        });
    });

    // ================= Modal rincian invoice =================
    var modalDetail = new bootstrap.Modal(document.getElementById('modal-inv-detail'));

    // yyyy-mm-dd hh:mm:ss -> 12 Sep 2026 09:12 (detiknya tidak dipakai di layar).
    function tglJam(v) {
        var p = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/.exec(v || '');
        return p ? p[3] + ' ' + BULAN[parseInt(p[2], 10) - 1] + ' ' + p[1] + ' ' + p[4] + ':' + p[5] : teks(v);
    }

    function barisInfo(label, isi, mentah) {
        return '<div><dt>' + label + '</dt><dd>' + (mentah ? isi : teks(isi)) + '</dd></div>';
    }

    // Buka/tutup rincian rekap di modal.
    $('#det-btn-lipat-rekap').on('click', function () {
        var buka = $('#det-ringkas').prop('hidden');
        $('#det-ringkas').prop('hidden', !buka);
        $(this).attr('aria-expanded', buka ? 'true' : 'false');
        $('#det-btn-lipat-rekap-teks').text(buka ? 'Hide details' : 'Show details');
    });

    $('#inv-table').on('click', '.dn-no-link', function () {
        var id = $(this).data('id');
        $('#det-judul').text($(this).text());
        $('#det-muat').prop('hidden', false);
        $('#det-isi').prop('hidden', true);
        $('#det-pita').prop('hidden', true);
        $('#det-btn-pdf-rinci').attr('href', RUT_PDF + '?id=' + encodeURIComponent(id));
        $('#det-btn-pdf-ringkas').attr('href', RUT_PDF_R + '?id=' + encodeURIComponent(id));
        $('#det-btn-pdf-knit').attr('href', RUT_PDF_K + '?id=' + encodeURIComponent(id));
        $('#det-btn-pdf-knitb').attr('href', RUT_PDF_K + '?id=' + encodeURIComponent(id) + '&versi=tagih');
        // Ditentukan lagi setelah profit center-nya diketahui.
        $('#det-btn-pdfl-rinci').attr('href', RUT_PDF + '?id=' + encodeURIComponent(id) + '&gaya=lama');
        $('#det-btn-pdfl-ringkas').attr('href', RUT_PDF_R + '?id=' + encodeURIComponent(id) + '&gaya=lama');
        $('#det-btn-pdfl-knit').attr('href', RUT_PDF_K + '?id=' + encodeURIComponent(id) + '&gaya=lama');
        $('#det-btn-xls-rinci').attr('href', RUT_XLS + '?id=' + encodeURIComponent(id));
        $('#det-btn-xls-ringkas').attr('href', RUT_XLS_R + '?id=' + encodeURIComponent(id));
        $('#det-btn-xls-knit').attr('href', RUT_XLS_K + '?id=' + encodeURIComponent(id));
        $('#det-pdf-knit-baris, #det-pdf-knitb-baris, #det-pdfl-knit-baris, #det-xls-knit-baris').prop('hidden', true);
        // Panel History ditutup lagi & dikosongkan - isinya milik invoice tadi.
        $('#det-riw').prop('hidden', true);
        $('#det-btn-lipat-riw').attr('aria-expanded', 'false').data('inv', id);
        $('#det-btn-lipat-riw-teks').text('Show details');
        riwSetel();
        // Sembunyikan dulu; ditentukan lagi setelah statusnya diketahui.
        $('#det-btn-pdf, #det-btn-pdfl, #det-btn-xls').addClass('d-none');
        modalDetail.show();

        $.getJSON(RUT_DETAIL, { id: id }).done(function (res) {
            var h = res.header || {};
            $('#det-judul').text(h.no_invoice || 'Invoice');

            // Invoice yang dibatalkan boleh dilihat, tapi tidak boleh dicetak.
            var batal = String(h.status || '').toUpperCase() === 'CANCEL';
            $('#det-btn-pdf, #det-btn-pdfl, #det-btn-xls').toggleClass('d-none', batal);
            $('#det-pdf-knit-baris, #det-pdf-knitb-baris, #det-pdfl-knit-baris, #det-xls-knit-baris').prop('hidden',
                String(h.profit_center || '').trim().toUpperCase() !== 'NAK');

            // Invoice Date yang diisi di form; kalau invoice lama belum punya,
            // jatuh ke tanggal booking.
            var tglInv = (res.pot && res.pot.tgl_invoice) ? res.pot.tgl_invoice : h.tanggal;
            {{-- Invoice Date & Status tidak diulang di sini: keduanya sudah ada di
                 pita ringkasan yang selalu terlihat di atas. --}}
            $('#det-info').html(''
                + barisInfo('Profit Center', h.profit_center)
                + barisInfo('Billed To', h.customer)
                + barisInfo('Shipped To', h.customer_ship)
                + barisInfo('Address', h.alamat_ship || h.alamat)
                + barisInfo('Type', h.type)
                + barisInfo('Document', (h.doc_type || '-') + (h.doc_number ? ' / ' + h.doc_number : ''))
                + barisInfo('Currency', h.curr)
                {{-- Siapa & kapan dibuat digabung, sama seperti modal Export. --}}
                + barisInfo('Created', (h.booking_by || '-') + (h.booking_date ? '  ·  ' + tglJam(h.booking_date) : '')));

            var baris = res.baris || [];
            // Ditentukan dari datanya: invoice garment tidak punya nilai tagih,
            // dan tabelnya tidak perlu melebar tanpa guna.
            $('#det-tabel').toggleClass('is-knit', baris.some(function (r) {
                return $.trim(String(r.uom_tagih == null ? '' : r.uom_tagih)) !== ''
                    || (parseFloat(r.total_price_tagih) || 0) > 0;
            }));
            $('#det-tabel tbody').html(baris.length
                ? baris.map(function (r) {
                    return '<tr>'
                        + '<td>' + teks(r.so_number) + '</td>'
                        + '<td>' + teks(r.shipp_number) + '</td>'
                        + '<td>' + tglTampil(r.sj_date, 'display') + '</td>'
                        + '<td>' + teks(r.ws) + '</td>'
                        + '<td>' + teks(r.styleno) + '</td>'
                        + '<td>' + teks(r.product_item) + '</td>'
                        + '<td>' + teks(r.color) + '</td>'
                        + '<td>' + teks(r.size) + '</td>'
                        + '<td>' + teks(r.uom) + '</td>'
                        + '<td class="dn-angka">' + uang(r.qty) + '</td>'
                        + '<td class="dn-angka">' + uang(r.unit_price) + '</td>'
                        + '<td class="dn-angka">' + uang(r.disc) + '</td>'
                        + '<td class="dn-angka">' + uang(r.total_price) + '</td>'
                        + '<td class="det-sel-tagih">' + teks(r.uom_tagih) + '</td>'
                        + '<td class="dn-angka det-sel-tagih">' + uang(r.qty_tagih) + '</td>'
                        + '<td class="dn-angka det-sel-tagih">' + uang(r.unit_price_tagih) + '</td>'
                        + '<td class="dn-angka det-sel-tagih">' + uang(r.total_price_tagih) + '</td>'
                        + '</tr>';
                }).join('')
                : '<tr><td class="dn-kosong" colspan="17">No detail row.</td></tr>');

            var p = res.pot || {};
            // Ringkasan nilai tagih - cuma ada untuk invoice knitting.
            var pt = res.pot_tagih || null;
            function baut(label, nilai, kelas, nilai2) {
                return '<div' + (kelas ? ' class="' + kelas + '"' : '') + '>'
                    + '<span>' + label + '</span><b>' + uang(nilai || 0) + '</b>'
                    + (pt ? '<b class="det-nilai-tagih">' + uang(nilai2 || 0) + '</b>' : '')
                    + '</div>';
            }
            var pt2 = pt || {};
            $('#det-ringkas').html(''
                + (pt ? '<div class="det-judul-nilai"><span></span>'
                        + '<b>Shipment</b><b class="det-nilai-tagih">Billing</b></div>' : '')
                + baut('Total', p.total, '', pt2.total)
                + baut('Discount', p.discount, '', pt2.discount)
                + baut('Down Payment', p.dp, '', pt2.dp)
                + baut('DP/CBD from Invoice', p.dp_cbd, '', pt2.dp_cbd)
                + baut('Return', p.retur, '', pt2.retur)
                + baut('Total Without Tax', p.twot, '', pt2.twot)
                + baut('VAT' + (parseFloat(p.vat_persen) ? ' (' + parseFloat(p.vat_persen) + '%)' : ''),
                       p.vat, '', pt2.vat));
            $('#det-ringkas-grand').html(baut('Grand Total', p.grand_total, 'dn-det-grand',
                pt2.grand_total));
            // Tiap invoice dibuka dalam keadaan terlipat lagi.
            $('#det-ringkas').prop('hidden', true);
            $('#det-btn-lipat-rekap').attr('aria-expanded', 'false');
            $('#det-btn-lipat-rekap-teks').text('Show details');

            // Pita ringkasan memakai angka yang sama dengan isi modal.
            $('#det-pita-status').html('<span class="dn-pil ' + kelasStatus(h.status) + '">'
                + '<span class="dn-pil-titik"></span>' + teks(h.status) + '</span>');
            $('#det-pita-tgl').text(tglTampil(tglInv, 'display'));
            $('#det-pita-baris').text(baris.length);
            $('#det-pita-grand').text(uang(p.grand_total || 0));
            $('#det-pita').prop('hidden', false);
            // Isian yang kosong ("-") diredupkan supaya mata langsung ke yang terisi.
            $('#det-info dd').each(function () {
                $(this).toggleClass('dn-det-kosong-nilai', $(this).text().trim() === '-');
            });

            $('#det-muat').prop('hidden', true);
            $('#det-isi').prop('hidden', false);
        }).fail(function (x) {
            modalDetail.hide();
            var pesan = (x.responseJSON && x.responseJSON.pesan) ? x.responseJSON.pesan : 'Please try again.';
            Swal.fire({ icon: 'error', title: 'Could not load the invoice', text: pesan });
        });
    });

    // Riwayat baru diminta ke server waktu panelnya dibuka - daftarnya bisa
    // panjang dan tidak selalu dilihat.
    $('#det-btn-lipat-riw').on('click', function () {
        var buka = $('#det-riw').prop('hidden');
        $('#det-riw').prop('hidden', !buka);
        $(this).attr('aria-expanded', buka ? 'true' : 'false');
        $('#det-btn-lipat-riw-teks').text(buka ? 'Hide details' : 'Show details');
        if (buka) { riwMuat($(this).data('inv')); }
    });

@include('export-import.invoice._riwayat_js')
});
</script>
@endpush
@endsection
