@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@include('export-import._laporan_ceisa_skin')
@endsection

@section('content')
{{-- ===========================================================================
     Laporan Rekonsiliasi Ceisa - Signalbit.
     Tampilannya mengikuti daftar Invoice EXIM (lihat ../_laporan_ceisa_skin).
     =========================================================================== --}}
<div class="nag-skin dn-kontrol-sm">

    {{-- ===== Kepala halaman ===== --}}
    <div class="dn-kepala-halaman">
        <div class="dn-kepala-kiri">
            <span class="dn-kepala-ikon"><i class="fas fa-scale-balanced"></i></span>
            <div>
                <h1 class="dn-kepala-judul">Laporan Rekonsiliasi Ceisa - Signalbit</h1>
                <p class="dn-kepala-sub">Dokumen CEISA dibandingkan dengan Signalbit per nomor aju &amp; nomor daftar</p>
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
                <div class="form-group dn-f-sedang">
                    <label for="status">Status</label>
                    <select class="form-control select2bs4" id="status" name="status">
                        <option selected value="ALL">ALL</option>
                        @foreach ($statusdok as $sdok)
                            <option value="{{ $sdok->isi }}">{{ $sdok->tampil }}</option>
                        @endforeach
                    </select>
                </div>
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
                            <th>Jenis Dok</th>
                            <th>No Daftar</th>
                            <th>Tgl Daftar</th>
                            <th>No Aju</th>
                            <th>Tgl Aju</th>
                            <th>Supplier Ceisa</th>
                            <th>Supplier SB</th>
                            <th>Satuan Ceisa</th>
                            <th>Satuan SB</th>
                            <th>Qty Ceisa</th>
                            <th>Qty SB</th>
                            <th>Diff Qty</th>
                            <th>Total Ceisa</th>
                            <th>Total IDR</th>
                            <th>Total SB</th>
                            <th>Total SB IDR</th>
                            <th>Diff Total</th>
                            <th>Status</th>
                            <th>Issue List</th>
                            <th>Description Update</th>
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

    // Satuan bisa lebih dari satu ("PCE, KGM") - satu satuan satu baris.
    function rSatuan(v, t) {
        if (t !== 'display') { return v == null ? '' : v; }
        return v ? teks(v).replace(/,\s*/g, '<br>') : teks(v);
    }

    // Status: sudah / belum ada di Signalbit.
    function rStatus(v, t) {
        if (t !== 'display') { return v == null ? '' : v; }
        return pil(v, v === 'Updated' ? 'is-second' : 'is-batal');
    }

    // Issue List: hijau = sesuai, biru = cuma selisih pembulatan, kuning = satuan
    // beda / total CEISA kosong, merah = qty atau total beda, atau belum ada di
    // Signalbit.
    function rIssue(v, t) {
        if (t !== 'display') { return v == null ? '' : v; }
        var s = String(v || '').toUpperCase();
        var kelas = 'is-first';
        if (s === 'SESUAI') { kelas = 'is-second'; }
        else if (s === 'SELISIH PEMBULATAN') { kelas = 'is-post'; }
        else if (s === 'TIDAK ADA DI SIGNALBIT' || s === 'TIDAK SESUAI' || /QTY|TOTAL TIDAK/.test(s)) { kelas = 'is-batal'; }
        return pil(v, kelas);
    }

    pasangLoader('#datatable', '#lap-area');

    var datatable = $('#datatable').DataTable(opsiTabel({
        ajax: {
            url: @json(route('report-rekonsiliasi-ceisa')),
            data: function (d) {
                d.dateFrom = tglIso('#from');
                d.dateTo = tglIso('#to');
                d.jenis_dok = $('#jenis_dok').val();
                d.status = $('#status').val();
            }
        },
        // Jenis Dok s/d Tgl Aju dibekukan di kiri (lihat pasangBeku di bawah).
        columns: [
            { data: 'kode_dokumen', className: 'dn-lap-beku', render: rTeks },
            { data: 'no_daftar', className: 'dn-lap-beku', render: rKunci },
            { data: 'tgl_daftar', className: 'dn-lap-beku', render: rTgl },
            { data: 'no_aju', className: 'dn-lap-beku', render: rTeks },
            { data: 'tgl_aju', className: 'dn-lap-beku dn-lap-beku-akhir', render: rTgl },
            { data: 'nama_entitas', render: rLipat },
            { data: 'supplier', render: rLipat },
            { data: 'satuan_ciesa_tampil', render: rSatuan },
            { data: 'satuan_sb', render: rSatuan },
            { data: 'qty', className: 'dn-angka', render: rAngka(0, 2) },
            { data: 'qty_sb', className: 'dn-angka', render: rAngka(0, 2) },
            { data: 'diff_qty', className: 'dn-angka', render: rSelisih(0, 2) },
            { data: 'total', className: 'dn-angka', render: rAngka(2, 2) },
            { data: 'total_idr', className: 'dn-angka dn-nilai', render: rAngka(2, 2) },
            { data: 'total_sb', className: 'dn-angka', render: rAngka(2, 2) },
            { data: 'total_sb_idr', className: 'dn-angka dn-nilai', render: rAngka(2, 2) },
            { data: 'diff_total', className: 'dn-angka', render: rSelisih(2, 2) },
            { data: 'status', className: 'dn-tengah', render: rStatus },
            { data: 'status_kesesuaian', render: rIssue },
            { data: 'keterangan_update', render: rLipat }
        ]
    }));

    pasangBeku(datatable, 5);

    $('#lap-btn-cari').on('click', function () { datatable.ajax.reload(); });

    $('#lap-btn-export').on('click', function () {
        var from = tglIso('#from');
        var to = tglIso('#to');
        unduhExcel(@json(route('export-rekonsiliasi-ceisa')), {
            from: from,
            to: to,
            jenis_dok: $('#jenis_dok').val(),
            status: $('#status').val()
        }, 'Laporan Rekonsiliasi Data Ceisa Dari  ' + from + ' sampai ' + to + '.xlsx');
    });
});
</script>
@endsection
