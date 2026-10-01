@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@include('export-import.invoice._skin')
@endsection

@section('content')
{{-- ===========================================================================
     Daftar Invoice Export.
     Kolom & filternya sama dengan daftar Invoice Local (../local/index.blade.php),
     yang beda:
       - PDF & Excel punya dua versi: CM (penagihan AR) dan FOB (perizinan BC)
       - modal rinciannya mengikuti bentuk dokumen export: pihak-pihak, blok
         pengiriman, Invoice Summary per warna, rekap CM & FOB berdampingan
     =========================================================================== --}}
{{-- dn-kontrol-sm: tombol, isian & select2 ukuran kecil - sama di semua halaman invoice (lihat _skin). --}}
<div class="nag-skin dn-kontrol-sm dn-daftar-export">

    {{-- ===== Kepala halaman =====
         Judul layar saja; tombol-tombolnya ikut di baris filter supaya semua
         aksi ada di satu tempat. --}}
    <div class="dn-kepala-halaman">
        <div class="dn-kepala-kiri">
            <span class="dn-kepala-ikon"><i class="fas fa-file-export"></i></span>
            <div>
                <h1 class="dn-kepala-judul">Invoice Export</h1>
                <p class="dn-kepala-sub">Booking invoice export EXIM</p>
            </div>
        </div>
    </div>

    {{-- ===== Filter ===== --}}
    <div class="card dn-kartu-filter">
        <div class="card-body">
            {{-- Urutannya: yang paling sering dipakai menyaring dulu (Seller,
                 Status), rentang tanggalnya di kanan. Lebarnya ikut isinya -
                 nama seller panjang, tanggal cukup selebar dd/mm/yyyy. --}}
            <div class="dn-filter-petak">
                <div class="form-group dn-f-lebar">
                    <label for="inv-customer">Seller</label>
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
                {{-- Search menyaring daftar; Export & Create mengurus dokumennya.
                     Dipisah garis tipis supaya kelihatan dua kelompok kerja yang beda,
                     tapi tetap satu deret jadi tangannya tidak pindah jauh. --}}
                <div class="dn-filter-tombol">
                    <button type="button" class="btn btn-dn-utama" id="inv-btn-cari">
                        <i class="fa fa-search"></i> Search
                    </button>
                    <span class="dn-filter-pisah"></span>
                    <button type="button" class="btn btn-dn-excel" id="inv-btn-export">
                        <i class="fas fa-file-excel"></i> Export
                    </button>
                    {{-- Warnanya sengaja beda dari Search (navy): Create itu membuat
                         dokumen baru, bukan menyaring daftar. --}}
                    <button type="button" class="btn btn-dn-create" id="inv-btn-buat">
                        <i class="fas fa-plus"></i> Create
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- ===== Tabel ===== --}}
    <div class="card dn-kartu-tabel">
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
                            {{-- Billed To & Shipped To untuk invoice export selalu Seller
                                 yang sama, dan Shipp selalu "Export" - jadi cukup satu
                                 kolom Seller. --}}
                            <tr>
                                <th>Inv Number</th>
                                <th>Invoice Date</th>
                                <th>Seller</th>
                                <th>Type</th>
                                <th>Doc Type</th>
                                <th>Doc Number</th>
                                <th class="dn-angka">Value (CM)</th>
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
         Modal rincian Invoice Export - terbuka saat nomor invoice diklik.
         Urutannya sama dengan dokumennya: keterangan, pihak-pihak, blok
         pengiriman, Invoice Summary & rekap, lalu baris FG/OUT asalnya.
         =================================================================== --}}
    <div class="modal fade" id="modal-inv-detail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable dn-modal-rincian">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-export"></i>
                        <span id="det-judul">Invoice</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- Pita ringkasan: di luar badan modal, jadi tetap terlihat waktu
                     isinya digulir. Angka yang paling sering dicari (status, tanggal,
                     jumlah shipment, grand total) tidak perlu digulir dulu. --}}
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
                        <span class="dn-pita-label">Shipments</span>
                        <b id="det-pita-kirim">0</b>
                    </div>
                    <div class="dn-pita-butir dn-pita-total">
                        <span class="dn-pita-label">Grand Total CM</span>
                        <b id="det-pita-cm">0.00</b>
                    </div>
                    <div class="dn-pita-butir dn-pita-total">
                        <span class="dn-pita-label">Grand Total FOB</span>
                        <b id="det-pita-fob">0.00</b>
                    </div>
                </div>

                <div class="modal-body">
                    <div class="dn-det-muat" id="det-muat">
                        <span class="dn-loader-putar"></span> Loading...
                    </div>

                    {{-- Isinya dibagi tiga tab supaya tiap bagian muat sekali lihat:
                         keterangan & pihak, blok pengiriman, lalu ringkasan & rekap. --}}
                    <div id="det-isi" hidden>
                        <ul class="nav dn-det-tab" id="det-tab" role="tablist">
                            <li role="presentation">
                                <button class="nav-link active" id="det-tab-umum-btn" data-bs-toggle="tab"
                                    data-bs-target="#det-tab-umum" type="button" role="tab"
                                    aria-controls="det-tab-umum" aria-selected="true">
                                    <i class="fas fa-file-invoice"></i> Overview
                                </button>
                            </li>
                            <li role="presentation">
                                <button class="nav-link" id="det-tab-ringkas-btn" data-bs-toggle="tab"
                                    data-bs-target="#det-tab-ringkas" type="button" role="tab"
                                    aria-controls="det-tab-ringkas" aria-selected="false">
                                    <i class="fas fa-layer-group"></i> Summary
                                </button>
                            </li>
                            {{-- Riwayat jadi tab sendiri: daftarnya bisa panjang dan
                                 tidak ada hubungannya dengan angka invoice-nya. --}}
                            <li role="presentation">
                                <button class="nav-link" id="det-tab-riw-btn" data-bs-toggle="tab"
                                    data-bs-target="#det-tab-riw" type="button" role="tab"
                                    aria-controls="det-tab-riw" aria-selected="false">
                                    <i class="fas fa-clock-rotate-left"></i> History
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">
                        <div class="tab-pane fade show active" id="det-tab-umum" role="tabpanel" aria-labelledby="det-tab-umum-btn">
                        <dl class="dn-det-info dn-det-info-4" id="det-info"></dl>

                        <div class="dn-det-judul"><i class="fas fa-people-arrows"></i> Parties</div>
                        {{-- Urutannya alur dokumen: barang dikirim Shipper, ditagihkan lewat
                             Seller ke Purchaser, dan dikirim ke Receiver. --}}
                        <div class="dn-det-pihak" id="det-pihak"></div>

                        {{-- Blok pengiriman ditampilkan sebagai label-isi seperti di cetakan.
                             Sebagai tabel isinya 18 kolom: entah digeser ke samping, entah
                             kata-katanya terpenggal - dua-duanya tidak enak dibaca.
                             Ikut di tab Overview supaya tinggi kedua tab hampir sama -
                             modal tidak berubah ukuran waktu tabnya diganti. --}}
                        <div class="dn-det-judul">
                            <i class="fas fa-ship"></i> Shipment Details
                            <span class="dn-tab-angka" id="det-tab-kirim-n">0</span>
                        </div>
                        <div id="det-kirim"></div>
                        </div>

                        {{-- Invoice Summary & Detail SJ satu kartu, sama seperti di form:
                             baris summary memang diturunkan dari warna SJ-nya. --}}
                        <div class="tab-pane fade" id="det-tab-ringkas" role="tabpanel" aria-labelledby="det-tab-ringkas-btn">
                        <div class="dn-det-kartu">
                            <div class="dn-sj-pita">
                                <div class="dn-lipat-strip">
                                    <span class="dn-sj-label"><i class="fas fa-truck"></i> Detail SJ</span>
                                    <div class="dn-lipat-ringkas" id="det-sj-ringkas">
                                        <span class="dn-lipat-kosong">No SJ row.</span>
                                    </div>
                                    <button type="button" class="btn btn-dn-lipat" id="det-btn-lipat-sj"
                                        aria-expanded="false" aria-controls="det-sj-isi" hidden>
                                        <span id="det-btn-lipat-sj-teks">Show details</span>
                                        <i class="fas fa-chevron-down dn-lipat-panah"></i>
                                    </button>
                                </div>
                                <div class="dn-table-scroll dn-table-tinggi" id="det-sj-isi" hidden>
                                    <table class="dn-table text-nowrap" id="det-tabel" style="width:100%">
                                        <thead>
                                            <tr>
                                                {{-- Source & SJ Number tidak ditampilkan: isinya sama untuk semua
                                                     baris (satu profit center, satu SJ). --}}
                                                <th>SO Number</th>
                                                <th>SJ Date</th>
                                                <th>Shipping Number</th>
                                                <th>WS#</th>
                                                <th>Style No</th>
                                                <th>Product Item</th>
                                                <th>Color</th>
                                                <th>Size</th>
                                                <th>UOM</th>
                                                <th class="dn-angka">Qty</th>
                                                <th class="dn-angka">Unit Price</th>
                                                <th class="dn-angka">Total Price</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- ----- Invoice Summary per warna ----- --}}
                            <div class="dn-table-scroll">
                                <table class="dn-table text-nowrap" id="det-summary" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Color Code</th>
                                            <th>Color Name</th>
                                            <th class="dn-angka">Total Pieces</th>
                                            <th class="dn-angka">Qty Invoiced</th>
                                            <th class="dn-angka">Unit Cost (CM)</th>
                                            <th class="dn-angka">Unit Cost (FOB)</th>
                                            <th class="dn-angka">Disc (%)</th>
                                            <th class="dn-angka">Total (CM)</th>
                                            <th class="dn-angka">Total (FOB)</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Rekap CM & FOB berdampingan - sama dengan kartu Summary di form.
                             Pakai pembungkus biasa, bukan row/col: margin negatif row
                             membuat isinya melebihi lebar modal. --}}
                        <div class="dn-det-rekap-bungkus">
                            <div>
                                <div class="dn-det-rekap">
                                    <table class="dn-rekap">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>CM <span class="dn-rekap-curr" id="det-curr-cm"></span></th>
                                                <th>FOB <span class="dn-rekap-curr" id="det-curr-fob"></span></th>
                                            </tr>
                                        </thead>
                                        <tbody id="det-rekap"></tbody>
                                        <tfoot>
                                            <tr class="dn-rekap-grand">
                                                <th>Grand Total</th>
                                                <td id="det-grand-cm">0.00</td>
                                                <td id="det-grand-fob">0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        </div>{{-- /tab Summary --}}

                        <div class="tab-pane fade" id="det-tab-riw" role="tabpanel"
                            aria-labelledby="det-tab-riw-btn">
                            @include('export-import.invoice._riwayat')
                        </div>{{-- /tab History --}}
                        </div>{{-- /tab-content --}}

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-dn-kembali" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i> Close
                    </button>
                    {{-- Empat tombol cetak dijadikan dua dropdown CM/FOB - bentuknya
                         sama dengan tombol cetak di kolom Action daftar. --}}
                    <div class="dropdown dn-pilih-versi dn-det-cetak">
                        <button type="button" class="btn btn-dn-cetak dropdown-toggle" data-bs-toggle="dropdown"
                            data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dn-menu-versi">
                            <li><a class="dropdown-item" href="#" id="det-btn-pdf-cm" target="_blank" rel="noopener">
                                <b>CM</b><span>AR billing</span></a></li>
                            <li><a class="dropdown-item" href="#" id="det-btn-pdf-fob" target="_blank" rel="noopener">
                                <b>FOB</b><span>Customs (BC)</span></a></li>
                        </ul>
                    </div>
                    {{-- PDF Classic: tampilan tanpa CARING, untuk pembanding. --}}
                    <div class="dropdown dn-pilih-versi dn-det-cetak">
                        <button type="button" class="btn btn-dn-cetak-lama dropdown-toggle" data-bs-toggle="dropdown"
                            data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false"
                            title="PDF without CARING styling">
                            <i class="fas fa-file-pdf"></i> PDF Classic
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dn-menu-versi">
                            <li><a class="dropdown-item" href="#" id="det-btn-pdfl-cm" target="_blank" rel="noopener">
                                <b>CM</b><span>AR billing</span></a></li>
                            <li><a class="dropdown-item" href="#" id="det-btn-pdfl-fob" target="_blank" rel="noopener">
                                <b>FOB</b><span>Customs (BC)</span></a></li>
                        </ul>
                    </div>
                    <div class="dropdown dn-pilih-versi dn-det-cetak">
                        <button type="button" class="btn btn-dn-excel dropdown-toggle" data-bs-toggle="dropdown"
                            data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dn-menu-versi">
                            <li><a class="dropdown-item" href="#" id="det-btn-xls-cm"><b>CM</b><span>AR billing</span></a></li>
                            <li><a class="dropdown-item" href="#" id="det-btn-xls-fob"><b>FOB</b><span>Customs (BC)</span></a></li>
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
    var RUT_DATA   = @json(route('invoice-exim-export-data'));
    var RUT_DETAIL = @json(route('invoice-exim-export-detail'));
    var RUT_PDF    = @json(route('invoice-exim-export-pdf'));
    var RUT_XLS    = @json(route('invoice-exim-export-excel'));
    var RUT_XLS_DAFTAR = @json(route('invoice-exim-export-excel-daftar'));
    var POLA_EDIT  = @json(route('invoice-exim-export-edit', ['id' => '__ID__']));
    var RUT_BATAL  = @json(route('invoice-exim-export-batal'));
    var RUT_BUAT   = @json(route('invoice-exim-export-create'));

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

    function teks(nilai) {
        if (nilai == null || nilai === '') { return '-'; }
        return $('<div>').text(nilai).html();
    }

    function uang(nilai) {
        var n = parseFloat(nilai);
        if (isNaN(n)) { return teks(nilai); }
        return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Qty, berat & unit cost: desimal secukupnya, tidak dipaksa dua angka.
    function angka(nilai, maks) {
        var n = parseFloat(nilai);
        if (isNaN(n)) { return teks(nilai); }
        return n.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: maks });
    }

    // Tanggal dari server berbentuk yyyy-mm-dd; di layar ditulis '12 Sep 2026' -
    // nama bulan tidak bisa salah baca antara tanggal & bulan. Nilai aslinya tetap
    // dipakai untuk mengurutkan, jadi urutannya tidak ikut berubah.
    var BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    function tglTampil(v, t) {
        if (t !== 'display') { return v || ''; }
        var p = /^(\d{4})-(\d{2})-(\d{2})/.exec(v || '');
        return p ? p[3] + ' ' + BULAN[parseInt(p[2], 10) - 1] + ' ' + p[1] : teks(v);
    }

    /**
     * Tombol cetak dengan pilihan versi. Popper-nya memakai strategi fixed
     * supaya menunya tidak terpotong oleh wadah tabel yang bisa digulir.
     */
    // jenis: 'pdf' = PDF gaya CARING, 'pdf-lama' = PDF Classic (tanpa CARING),
    // 'xls' = Excel.
    function menuCetak(jenis, id) {
        var lama   = jenis === 'pdf-lama';
        var pdf    = jenis === 'pdf' || lama;
        var rute   = pdf ? RUT_PDF : RUT_XLS;
        var target = pdf ? ' target="_blank" rel="noopener"' : '';
        var ekor   = lama ? '&gaya=lama' : '';
        var judul  = lama ? 'Print PDF Classic - without CARING (CM / FOB)'
            : (pdf ? 'Print PDF' : 'Download Excel') + ' (CM / FOB)';
        return '<div class="dropdown dn-pilih-versi">'
            + '<button type="button" class="btn dn-ikon ' + (pdf ? 'dn-ikon-pdf' : 'dn-ikon-excel')
            + (lama ? ' dn-ikon-pdf-lama' : '') + '"'
            + ' data-bs-toggle="dropdown" data-bs-popper-config=\'{"strategy":"fixed"}\' aria-expanded="false"'
            + ' title="' + judul + '">'
            + '<i class="fas ' + (pdf ? 'fa-file-pdf' : 'fa-file-excel') + '"></i>'
            + '<i class="fas fa-angle-down dn-ikon-panah"></i>'
            + '</button>'
            + '<ul class="dropdown-menu dn-menu-versi">'
            + '<li><a class="dropdown-item" href="' + rute + '?id=' + id + '&versi=cm' + ekor + '"' + target + '>'
            + '<b>CM</b><span>AR billing</span></a></li>'
            + '<li><a class="dropdown-item" href="' + rute + '?id=' + id + '&versi=fob' + ekor + '"' + target + '>'
            + '<b>FOB</b><span>Customs (BC)</span></a></li>'
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
                    if (t !== 'display') { return v || ''; }
                    return '<button type="button" class="dn-no-link" data-id="' + teks(row.id) + '">'
                        + teks(v) + '</button>';
                }
            },
            { data: 'tanggal',  render: tglTampil },
            { data: 'customer', render: function (v, t, row) { return teks(v || row.customer_ship); } },
            { data: 'type',     render: teks },
            { data: 'doc_type',   render: teks },
            { data: 'doc_number', render: teks },
            { data: 'value', className: 'dn-angka dn-nilai', render: uang },
            {
                data: 'status',
                className: 'dn-tengah',
                render: function (v) {
                    // Pil kecil bertitik - status kebaca sekilas tanpa jadi blok warna besar.
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

                    // Yang dibatalkan tidak boleh punya cetakan yang beredar.
                    if (status === 'CANCEL') {
                        return '<div class="dn-aksi"><span class="dn-diproses">Cancelled</span></div>';
                    }

                    var isi = menuCetak('pdf', id) + menuCetak('pdf-lama', id) + menuCetak('xls', id);
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
    $('#inv-table').on('click', '.btn-inv-batal', function () {
        var $tb = $(this);
        var id  = $tb.data('id');
        var no  = $tb.data('no') || '';

        Swal.fire({
            icon: 'warning',
            title: 'Cancel this invoice?',
            html: '<div class="dn-swal-ringkas">'
                + '<div><span>Invoice</span><b>' + teks(no) + '</b></div>'
                + '<div><span>Grand Total (CM)</span><b>' + teks($tb.data('nilai')) + '</b></div>'
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

    // Detail SJ dilipat: isinya cuma sumber angka, dan tidak ikut tercetak.
    $('#det-btn-lipat-sj').on('click', function () {
        var buka = $('#det-sj-isi').prop('hidden');
        $('#det-sj-isi').prop('hidden', !buka);
        $(this).attr('aria-expanded', buka ? 'true' : 'false');
        $('#det-btn-lipat-sj-teks').text(buka ? 'Hide details' : 'Show details');
    });

    // yyyy-mm-dd hh:mm:ss -> 12 Sep 2026 09:12 (detiknya tidak dipakai di layar).
    function tglJam(v) {
        var p = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/.exec(v || '');
        return p ? p[3] + ' ' + BULAN[parseInt(p[2], 10) - 1] + ' ' + p[1] + ' ' + p[4] + ':' + p[5] : teks(v);
    }

    function barisInfo(label, isi, mentah) {
        return '<div><dt>' + label + '</dt><dd>' + (mentah ? isi : teks(isi)) + '</dd></div>';
    }

    /**
     * Blok pengiriman: SATU kotak untuk semua baris shipment, digeser ke samping
     * kalau lebih dari satu - bukan kotak bertumpuk. Label & isinya berpasangan,
     * urutannya sama dengan cetakan; keterangan barang memakai satu baris penuh.
     */
    function kotakKirim(kirim) {
        if (!kirim.length) {
            return '<div class="dn-det-kosong">No shipment row.</div>';
        }
        function isi(label, nilai, penuh) {
            return '<div' + (penuh ? ' class="dn-det-penuh"' : '') + '>'
                + '<dt>' + label + '</dt><dd>' + nilai + '</dd></div>';
        }
        var banyak = kirim.length > 1;
        var lembar = kirim.map(function (r, i) {
            return '<div class="dn-det-kirim-lembar"' + (i ? ' hidden' : '') + '>'
                + '<dl class="dn-det-kirim-grid">'
                + isi('Dest Purchase', teks(r.dest_purchase))
                + isi('Style NO', teks(r.style_no))
                + isi('Brand', teks(r.brand))
                + isi('Chanel Description', teks(r.chanel_description))
                + isi('Currency', teks(r.currency))
                + isi('Payment Term', teks(r.payment_term))
                + isi('Final Destination', teks(r.final_destination))
                + isi('Country of Origin', teks(r.country_origin))
                + isi('Ship Mode', teks(r.ship_mode))
                + isi('Term of Sale', teks(r.term_of_sale))
                + isi('Transfer Point', teks(r.transfer_point))
                + isi('Port of Loading', teks(r.port_of_loading))
                + isi('Total Gross Weight (KGS)', angka(r.total_gross_weight, 3))
                + isi('Total Net Weight (KGS)', angka(r.total_net_weight, 3))
                + isi('Total Net Net Weight (KGS)', angka(r.total_net_net_weight, 3))
                + isi('Total Carton', angka(r.total_carton, 2))
                + isi('Product Description', teks(r.product_description), true)
                + '</dl></div>';
        }).join('');

        return '<div class="dn-det-kirim-kotak">'
            + '<div class="dn-det-kirim-kepala">'
            + '<div class="dn-det-kirim-judul" id="det-kirim-judul">Shipment 1'
            + (banyak ? ' <span class="dn-det-kirim-dari">of ' + kirim.length + '</span>' : '') + '</div>'
            + (banyak
                ? '<div class="dn-det-kirim-navigasi">'
                    + '<button type="button" class="btn btn-dn-lipat" data-arah="-1" aria-label="Previous shipment">'
                    + '<i class="fas fa-chevron-left"></i></button>'
                    + '<button type="button" class="btn btn-dn-lipat" data-arah="1" aria-label="Next shipment">'
                    + '<i class="fas fa-chevron-right"></i></button>'
                    + '</div>'
                : '')
            + '</div>'
            + '<div class="dn-det-kirim-isi">' + lembar + '</div>'
            + '</div>';
    }

    // Pindah antar shipment: tombol panah mengganti lembar yang tampil.
    // Sengaja BUKAN jalur yang digeser - geseran ke samping memunculkan scrollbar.
    var kirimLembar = 0;
    function tampilkanKirim(i) {
        var $lembar = $('#det-kirim .dn-det-kirim-lembar');
        if (!$lembar.length) { return; }
        kirimLembar = Math.min($lembar.length - 1, Math.max(0, i));
        $lembar.prop('hidden', true).eq(kirimLembar).prop('hidden', false);
        $('#det-kirim-judul').html('Shipment ' + (kirimLembar + 1)
            + ($lembar.length > 1 ? ' <span class="dn-det-kirim-dari">of ' + $lembar.length + '</span>' : ''));
        // Di ujung daftar panahnya dimatikan, jadi jelas sudah mentok.
        $('#det-kirim [data-arah="-1"]').prop('disabled', kirimLembar === 0);
        $('#det-kirim [data-arah="1"]').prop('disabled', kirimLembar === $lembar.length - 1);
    }
    $('#det-kirim').on('click', '.dn-det-kirim-navigasi .btn', function () {
        tampilkanKirim(kirimLembar + parseInt($(this).data('arah'), 10));
    });

    /** Satu kotak pihak: label, nama tebal, alamat apa adanya (baris dipertahankan). */
    function kotakPihak(label, nama, alamat) {
        return '<div class="dn-det-pihak-kotak">'
            + '<span class="dn-det-pihak-label">' + label + '</span>'
            + '<b>' + teks(nama) + '</b>'
            + (alamat ? '<p>' + teks(alamat) + '</p>' : '')
            + '</div>';
    }

    /**
     * Tinggi isi tab dikunci ke tab yang paling tinggi. Tanpa ini modal ikut
     * mengecil/membesar tiap kali tabnya diganti - terasa seperti layar lain.
     * Tab yang tidak aktif dibuat tampak sebentar (tak terlihat) supaya lebarnya
     * benar waktu diukur, lalu dikembalikan sebelum layar digambar.
     */
    function samakanTinggiTab() {
        var $isi = $('#det-isi .tab-content').css('min-height', '');
        var tinggi = 0;
        // Tab History dilewati: isinya baru dimuat sesudah tabnya dibuka, dan
        // panjangnya tidak terbatas - kalau ikut diukur, dua tab lainnya jadi
        // kosong melompong.
        $('#det-isi .tab-pane').not('#det-tab-riw').each(function () {
            var aktif = $(this).hasClass('active');
            if (!aktif) { $(this).css({ display: 'block', visibility: 'hidden' }); }
            tinggi = Math.max(tinggi, this.scrollHeight);
            if (!aktif) { $(this).css({ display: '', visibility: '' }); }
        });
        $isi.css('min-height', tinggi + 'px');
    }

    // Modal yang baru dibuka: ukur lagi setelah animasinya selesai.
    $('#modal-inv-detail').on('shown.bs.modal', function () {
        if (!$('#det-isi').prop('hidden')) { samakanTinggiTab(); }
    });

    $('#inv-table').on('click', '.dn-no-link', function () {
        var id = encodeURIComponent($(this).data('id'));
        $('#det-judul').text($(this).text());
        $('#det-muat').prop('hidden', false);
        $('#det-isi').prop('hidden', true);
        $('#det-pita').prop('hidden', true);
        // Selalu mulai dari tab Overview - bukan tab terakhir yang dibuka invoice lain.
        $('#det-tab .nav-link').removeClass('active').attr('aria-selected', 'false');
        $('#det-tab-umum-btn').addClass('active').attr('aria-selected', 'true');
        $('#det-isi .tab-pane').removeClass('show active');
        $('#det-tab-umum').addClass('show active');
        // Riwayat invoice sebelumnya dikosongkan - tabnya dimuat lagi kalau dibuka.
        riwSetel();
        riwInv = $(this).data('id');
        $('#det-btn-pdf-cm').attr('href', RUT_PDF + '?id=' + id + '&versi=cm');
        $('#det-btn-pdf-fob').attr('href', RUT_PDF + '?id=' + id + '&versi=fob');
        $('#det-btn-pdfl-cm').attr('href', RUT_PDF + '?id=' + id + '&versi=cm&gaya=lama');
        $('#det-btn-pdfl-fob').attr('href', RUT_PDF + '?id=' + id + '&versi=fob&gaya=lama');
        $('#det-btn-xls-cm').attr('href', RUT_XLS + '?id=' + id + '&versi=cm');
        $('#det-btn-xls-fob').attr('href', RUT_XLS + '?id=' + id + '&versi=fob');
        // Sembunyikan dulu; ditentukan lagi setelah statusnya diketahui.
        $('.dn-det-cetak').addClass('d-none');
        modalDetail.show();

        $.getJSON(RUT_DETAIL, { id: $(this).data('id') }).done(function (res) {
            var h = res.header || {};
            $('#det-judul').text(h.no_invoice || 'Invoice');

            // Invoice yang dibatalkan boleh dilihat, tapi tidak boleh dicetak.
            var batal = String(h.status || '').toUpperCase() === 'CANCEL';
            $('.dn-det-cetak').toggleClass('d-none', batal);

            {{-- Invoice Date & Status tidak diulang di sini: keduanya sudah ada di
                 pita ringkasan yang selalu terlihat di atas. --}}
            $('#det-info').html(''
                + barisInfo('Invoice Number #2', h.no_invoice_2)
                + barisInfo('Profit Center', h.profit_center)
                + barisInfo('Invoice Type', h.type)
                + barisInfo('Document', (h.doc_type || '-') + (h.doc_number ? ' / ' + h.doc_number : ''))
                + barisInfo('Currency', h.curr)
                + barisInfo('Refference', h.reference)
                {{-- Siapa & kapan dibuat digabung: dua kotak terpisah cuma menambah
                     tinggi modal tanpa menambah keterangan. --}}
                + barisInfo('Created', (h.booking_by || '-') + (h.booking_date ? '  ·  ' + tglJam(h.booking_date) : ''))
                + barisInfo('Invoice Notes', h.invoice_notes));

            // Panah kecil di antara kotak: alur dokumennya jadi kebaca, bukan cuma
            // empat kotak berjajar.
            var panah = '<span class="dn-pihak-panah"><i class="fas fa-chevron-right"></i></span>';
            $('#det-pihak').html([
                kotakPihak('Shipper', h.shipper_nama, h.shipper_alamat),
                kotakPihak('Seller', h.seller_nama, h.seller_alamat),
                kotakPihak('Purchaser', h.purchaser_nama, h.purchaser_alamat),
                kotakPihak('Receiver / Ship To', h.receiver_nama, h.receiver_alamat)
            ].join(panah));

            var kirim = res.kirim || [];
            $('#det-kirim').html(kotakKirim(kirim));
            tampilkanKirim(0);

            var summary = res.summary || [];
            $('#det-summary tbody').html(summary.length
                ? summary.map(function (r, i) {
                    return '<tr>'
                        + '<td class="dn-tengah"><b>' + (i + 1) + '</b></td>'
                        + '<td>' + teks(r.color_code) + '</td>'
                        + '<td>' + teks(r.color_name) + '</td>'
                        + '<td class="dn-angka">' + (parseFloat(r.total_pieces) ? angka(r.total_pieces, 2) : '-') + '</td>'
                        + '<td class="dn-angka">' + angka(r.qty_invoiced, 2) + '</td>'
                        + '<td class="dn-angka">' + angka(r.unit_cost_cm, 4) + '</td>'
                        + '<td class="dn-angka">' + angka(r.unit_cost_fob, 4) + '</td>'
                        + '<td class="dn-angka">' + angka(r.disc, 2) + '</td>'
                        + '<td class="dn-angka"><b>' + uang(r.total_cm) + '</b></td>'
                        + '<td class="dn-angka"><b>' + uang(r.total_fob) + '</b></td>'
                        + '</tr>';
                }).join('')
                : '<tr><td class="dn-kosong" colspan="10">No summary row.</td></tr>');

            // Rekap CM & FOB - baris & tandanya sama dengan kartu Summary di form.
            var cm = (res.rekap && res.rekap.cm) || {};
            var fob = (res.rekap && res.rekap.fob) || {};
            var vat = parseFloat(res.vat_persen) || 0;
            function sel(n) {
                var v = parseFloat(n) || 0;
                return '<td' + (Math.abs(v) < 0.005 ? ' class="dn-nol"' : '') + '>' + uang(v) + '</td>';
            }
            function baris(label, kunci, kelas) {
                return '<tr' + (kelas ? ' class="' + kelas + '"' : '') + '><th>' + label + '</th>'
                    + sel(cm[kunci]) + sel(fob[kunci]) + '</tr>';
            }
            $('#det-rekap').html(''
                + baris('Total', 'total')
                + baris('Discount', 'discount', 'dn-rekap-kurang')
                + baris('Down Payment', 'dp', 'dn-rekap-kurang')
                + baris('DP/CBD from Invoice', 'dp_cbd', 'dn-rekap-kurang')
                + baris('Return', 'retur', 'dn-rekap-kurang')
                + baris('Total Without Tax', 'twot', 'dn-rekap-sub')
                + baris(vat ? 'VAT (' + vat + '%)' : 'VAT', 'vat', 'dn-rekap-tambah'));
            $('#det-grand-cm').text(uang(cm.grand));
            $('#det-grand-fob').text(uang(fob.grand));
            // Pita ringkasan di kepala modal memakai angka yang sama.
            $('#det-pita-status').html('<span class="dn-pil ' + kelasStatus(h.status) + '">'
                + '<span class="dn-pil-titik"></span>' + teks(h.status) + '</span>');
            $('#det-pita-tgl').text(tglTampil(h.tgl_invoice, 'display'));
            $('#det-pita-kirim').text(kirim.length);
            $('#det-pita-cm').text(uang(cm.grand));
            $('#det-pita-fob').text(uang(fob.grand));
            $('#det-tab-kirim-n').text(kirim.length);
            $('#det-pita').prop('hidden', false);
            // Isian yang kosong ("-") diredupkan supaya mata langsung ke yang terisi.
            $('#det-info dd, #det-kirim dd').each(function () {
                $(this).toggleClass('dn-det-kosong-nilai', $(this).text().trim() === '-');
            });
            $('#det-curr-cm, #det-curr-fob').text(String(h.curr || '').toUpperCase());

            var sj = res.baris || [];
            // Satu baris ringkasan Detail SJ, sama seperti di form.
            var pcs = sj.reduce(function (a, r) { return a + (parseFloat(r.qty) || 0); }, 0);
            $('#det-sj-isi').prop('hidden', true);
            $('#det-btn-lipat-sj-teks').text('Show details');
            $('#det-btn-lipat-sj').attr('aria-expanded', 'false').prop('hidden', !sj.length);
            $('#det-sj-ringkas').html(sj.length
                ? '<b>' + sj.length + '</b> SJ row' + (sj.length > 1 ? 's' : '')
                    + ' <span class="dn-lipat-titik">&middot;</span> <b>' + summary.length + '</b> colour'
                    + (summary.length > 1 ? 's' : '')
                    + ' <span class="dn-lipat-titik">&middot;</span> <b>' + angka(pcs, 2) + '</b> pcs'
                : '<span class="dn-lipat-kosong">No SJ row.</span>');
            $('#det-tabel tbody').html(sj.length
                ? sj.map(function (r) {
                    return '<tr>'
                        + '<td>' + teks(r.so_number) + '</td>'
                        + '<td>' + tglTampil(r.sj_date, 'display') + '</td>'
                        + '<td>' + teks(r.shipp_number) + '</td>'
                        + '<td>' + teks(r.ws) + '</td>'
                        + '<td>' + teks(r.styleno) + '</td>'
                        + '<td>' + teks(r.product_item) + '</td>'
                        + '<td>' + teks(r.color) + '</td>'
                        + '<td>' + teks(r.size) + '</td>'
                        + '<td>' + teks(r.uom) + '</td>'
                        + '<td class="dn-angka">' + angka(r.qty, 2) + '</td>'
                        + '<td class="dn-angka">' + angka(r.unit_price, 4) + '</td>'
                        + '<td class="dn-angka">' + uang(r.total_price) + '</td>'
                        + '</tr>';
                }).join('')
                : '<tr><td class="dn-kosong" colspan="12">No SJ row.</td></tr>');

            $('#det-muat').prop('hidden', true);
            $('#det-isi').prop('hidden', false);
            // Diukur setelah gambarnya jadi: kalau modalnya masih beranimasi buka,
            // semua tinggi masih 0.
            setTimeout(samakanTinggiTab, 0);
        }).fail(function (x) {
            modalDetail.hide();
            var pesan = (x.responseJSON && x.responseJSON.pesan) ? x.responseJSON.pesan : 'Please try again.';
            Swal.fire({ icon: 'error', title: 'Could not load the invoice', text: pesan });
        });
    });

    // Riwayat baru diminta ke server waktu tabnya dibuka - daftarnya bisa
    // panjang dan tidak selalu dilihat.
    var riwInv = 0;
    $('#det-tab-riw-btn').on('shown.bs.tab', function () {
        riwMuat(riwInv);
    });

@include('export-import.invoice._riwayat_js')
});
</script>
@endpush
@endsection
