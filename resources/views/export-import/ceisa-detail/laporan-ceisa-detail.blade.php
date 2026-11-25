@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@include('export-import._laporan_ceisa_skin')
@endsection

@section('content')
{{-- ===========================================================================
     Laporan Data Ceisa Detail - rincian barang per dokumen CEISA.
     Tampilannya mengikuti daftar Invoice EXIM (lihat ../_laporan_ceisa_skin).
     =========================================================================== --}}
<div class="nag-skin dn-kontrol-sm">

    {{-- ===== Kepala halaman ===== --}}
    <div class="dn-kepala-halaman">
        <div class="dn-kepala-kiri">
            <span class="dn-kepala-ikon"><i class="fas fa-boxes-stacked"></i></span>
            <div>
                <h1 class="dn-kepala-judul">Laporan Data Ceisa Detail</h1>
                <p class="dn-kepala-sub">Rincian barang per dokumen CEISA</p>
            </div>
        </div>
    </div>

    {{-- ===== Filter ===== --}}
    <div class="card dn-kartu-filter">
        <div class="card-body">
            <div class="dn-filter-petak">
                <div class="form-group dn-f-sedang">
                    <label for="jenis_dok">Jenis Dok</label>
                    <select class="form-control select2bs4" id="jenis_dok" name="jenis_dok">
                        <option selected value="ALL">ALL</option>
                        @foreach ($jenisdok as $jdok)
                            <option value="{{ $jdok->nama_pilihan }}">{{ $jdok->nama_pilihan }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Laporan ini tidak disaring per status - nilainya selalu ALL. --}}
                <input type="hidden" id="status" name="status" value="ALL">
                <div class="form-group dn-f-tgl">
                    <label for="from">From</label>
                    {{-- Tampil '24 Nov 2026'; nilainya diambil lewat tglIso() - lihat invoice/_kalender. --}}
                    <input type="text" class="form-control dn-tgl" id="from" name="from"
                        value="{{ now()->format('j M Y') }}" autocomplete="off">
                </div>
                <div class="form-group dn-f-tgl">
                    <label for="to">To</label>
                    <input type="text" class="form-control dn-tgl" id="to" name="to"
                        value="{{ now()->format('j M Y') }}" autocomplete="off">
                </div>
                <div class="dn-filter-tombol">
                    <button type="button" class="btn btn-dn-utama" id="lap-btn-cari">
                        <i class="fa fa-search"></i> Search
                    </button>
                    <span class="dn-filter-pisah"></span>
                    <button type="button" class="btn btn-dn-excel" id="lap-btn-export">
                        <i class="fas fa-file-excel"></i> Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Tabel ===== --}}
    <div class="card dn-kartu-tabel">
        <div class="card-body">
            <div class="dn-list-area" id="lap-area">
                <div class="dn-loader-overlay">
                    <div class="dn-loader-kartu">
                        <span class="dn-loader-putar"></span> Loading...
                    </div>
                </div>
                <table id="datatable" class="dn-table dn-tabel-daftar dn-lap-tabel" style="width:100%">
                    <thead>
                        <tr>
                            <th>No Upload</th>
                            <th>Jenis Dokumen</th>
                            <th>Supplier</th>
                            <th>No Daftar</th>
                            <th>Tgl Daftar</th>
                            <th>No Aju</th>
                            <th>Tgl Aju</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>Curr</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Rate</th>
                            <th>Total IDR</th>
                            <th>Uploaded By</th>
                            <th>Uploaded Date</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
$(function () {
    $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

    // ---------- tanggal & pembantu ----------
    @include('export-import.invoice._kalender')
    @include('export-import._laporan_ceisa_js')

    pasangLoader('#datatable', '#lap-area');

    var datatable = $('#datatable').DataTable(opsiTabel({
        ajax: {
            url: @json(route('report-ceisa-detail')),
            data: function (d) {
                d.dateFrom = tglIso('#from');
                d.dateTo = tglIso('#to');
                d.jenis_dok = $('#jenis_dok').val();
                d.status = $('#status').val();
            }
        },
        columns: [
            { data: 'no_dokumen', render: rTeks },
            { data: 'kode_dokumen_format', render: rTeks },
            { data: 'nama_entitas', render: rLipat },
            { data: 'no_daftar', render: rKunci },
            { data: 'tgl_daftar', render: rTgl },
            { data: 'no_aju', render: rTeks },
            { data: 'tgl_aju', render: rTgl },
            // Kode barang bisa berupa daftar ("SPX/0826/003, SPX/0826/004") - boleh turun baris.
            { data: 'kode_barang', render: rLipat },
            { data: 'uraian', render: rLipatLebar },
            { data: 'qty', className: 'dn-angka', render: rAngka(0, 2) },
            { data: 'unit', render: rTeks },
            { data: 'curr', render: rTeks },
            // Harga satuan bisa sangat kecil (mis. 0.0045 per pcs) - sampai 4 desimal.
            { data: 'price', className: 'dn-angka', render: rAngka(2, 4) },
            { data: 'cif', className: 'dn-angka', render: rAngka(2, 2) },
            { data: 'rates', className: 'dn-angka', render: rAngka(0, 2) },
            { data: 'cif_rupiah', className: 'dn-angka dn-nilai', render: rAngka(2, 2) },
            { data: 'created_by', render: rTeks },
            { data: 'created_date', render: rJam }
        ]
    }));

    $('#lap-btn-cari').on('click', function () { datatable.ajax.reload(); });

    $('#lap-btn-export').on('click', function () {
        var from = tglIso('#from');
        var to = tglIso('#to');
        unduhExcel(@json(route('export-ceisa-detail')), {
            from: from,
            to: to,
            jenis_dok: $('#jenis_dok').val(),
            status: $('#status').val()
        }, 'Laporan Data Ceisa Detail Dari  ' + from + ' sampai ' + to + '.xlsx');
    });
});
</script>
@endsection
