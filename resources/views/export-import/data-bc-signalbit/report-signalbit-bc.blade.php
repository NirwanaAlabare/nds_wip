@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
@include('export-import._laporan_ceisa_skin')
@endsection

@section('content')
{{-- ===========================================================================
     Data BC Signalbit - baris BPB & BPPB yang punya dokumen BC, per tanggal daftar.
     Tampilannya mengikuti daftar Invoice EXIM (lihat ../_laporan_ceisa_skin).
     =========================================================================== --}}
<div class="nag-skin dn-kontrol-sm">

    {{-- ===== Kepala halaman ===== --}}
    <div class="dn-kepala-halaman">
        <div class="dn-kepala-kiri">
            <span class="dn-kepala-ikon"><i class="fas fa-database"></i></span>
            <div>
                <h1 class="dn-kepala-judul">Data BC SignalBit</h1>
                <p class="dn-kepala-sub">Barang masuk (BPB) &amp; keluar (BPPB) Signalbit beserta dokumen BC-nya</p>
            </div>
        </div>
    </div>

    {{-- ===== Filter ===== --}}
    <div class="card dn-kartu-filter">
        <div class="card-body">
            <div class="dn-filter-petak">
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
                            <th>No BPB</th>
                            <th>BPB Date</th>
                            <th>Jenis Dokumen</th>
                            <th>No Daftar</th>
                            <th>Tgl Daftar</th>
                            <th>No Aju</th>
                            <th>Tgl Aju</th>
                            <th>Id Item</th>
                            <th>Item Descriptions</th>
                            <th>Unit</th>
                            <th>Qty</th>
                            <th>Curr</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Rate</th>
                            <th>Nilai IDR</th>
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
<script>
$(function () {
    // ---------- tanggal & pembantu ----------
    @include('export-import.invoice._kalender')
    @include('export-import._laporan_ceisa_js')

    pasangLoader('#datatable', '#lap-area');

    // Barisnya bisa ribuan per bulan - halaman & pencarian dikerjakan server.
    // Tgl Daftar / Tgl Aju sudah '-' dari server kalau nomornya kosong.
    var datatable = $('#datatable').DataTable(opsiTabel({
        serverSide: true,
        ajax: {
            url: @json(route('report-signalbit-bc')),
            data: function (d) {
                d.dateFrom = tglIso('#from');
                d.dateTo = tglIso('#to');
            }
        },
        columns: [
            { data: 'bpbno_int', render: rTeks },
            { data: 'bpbdate', render: rTgl },
            { data: 'jenis_dok', render: rTeks },
            { data: 'no_daftar', render: rKunci },
            { data: 'bcdate', render: rTgl },
            { data: 'nomor_aju', render: rTeks },
            { data: 'tanggal_aju', render: rTgl },
            { data: 'id_item', render: rTeks },
            { data: 'itemdesc', render: rLipatLebar },
            { data: 'satuan', render: rTeks },
            { data: 'qty', className: 'dn-angka', render: rAngka(0, 4) },
            { data: 'curr', render: rTeks },
            { data: 'price', className: 'dn-angka', render: rAngka(2, 4) },
            { data: 'total', className: 'dn-angka', render: rAngka(2, 2) },
            { data: 'rate', className: 'dn-angka', render: rAngka(0, 2) },
            { data: 'nilai_idr', className: 'dn-angka dn-nilai', render: rAngka(2, 2) }
        ]
    }));

    $('#lap-btn-cari').on('click', function () { datatable.ajax.reload(); });

    $('#lap-btn-export').on('click', function () {
        var from = tglIso('#from');
        var to = tglIso('#to');
        unduhExcel(@json(route('export-excel-report-signalbit-bc')), {
            from: from,
            to: to
        }, 'Laporan Data BC Signalbit Dari  ' + from + ' sampai ' + to + '.xlsx');
    });
});
</script>
@endsection
