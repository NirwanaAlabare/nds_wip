{{-- ===========================================================================
     Stock Opname Fabric - pilih rak untuk di-scan (Proses > Stock Opname).
     Dipakai di tablet & HP: satu kartu per rak (seluruh kartu bisa ditekan),
     kotak status sekaligus filter, pilihan SO / halaman / pencarian diingat
     (sessionStorage) supaya kembali dari halaman scan tetap di posisi yang sama.
     =========================================================================== --}}
@extends('layouts.index', ['containerFluid' => true])

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@include('stock_opname._skin')
<style>
/* Daftar rak: DataTables tetap dipakai untuk data & halaman, tapi tiap baris tampil sebagai kartu */
.so-rak-tabel, .so-rak-tabel tbody { display: block; width: 100% !important; margin: 0 !important; }
.so-rak-tabel thead { display: none; }
.so-rak-tabel tbody { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 12px; }
.so-rak-tabel tbody tr { display: block; background: transparent !important; }
.so-rak-tabel tbody td { display: block; padding: 0; border: 0; height: 100%; }
/* Baris "tidak ada rak" selebar daftar (item grid-nya tr, bukan td) */
.so-rak-tabel tbody tr:has(td.dataTables_empty) { grid-column: 1 / -1; }
.so-rak-tabel tbody td.dataTables_empty { height: auto; padding: 34px 12px; text-align: center; color: var(--muted); background: #fff; border: 1px dashed var(--line-2); border-radius: 14px; }
@media (max-width: 575px) { .so-rak-tabel tbody { grid-template-columns: 1fr; gap: 10px; } }

.so-rak {
  display: flex; flex-direction: column; gap: 11px; height: 100%; padding: 14px 16px; color: var(--ink); text-decoration: none;
  background: #fff; border: 1px solid var(--line); border-left: 5px solid #cbd5e1; border-radius: 14px;
  box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 10px 22px -18px var(--bayang); transition: box-shadow .12s ease, transform .1s ease; touch-action: manipulation;
}
.so-rak:hover { color: var(--ink); border-color: var(--n-300); box-shadow: 0 1px 2px rgba(15, 23, 42, .05), 0 14px 26px -16px var(--bayang); }
.so-rak:active { transform: scale(.985); }
.so-rak:focus-visible { outline: 0; box-shadow: 0 0 0 3px var(--fokus); }
.so-rak.is-partial { border-left-color: #f59e0b; }
.so-rak.is-completed { border-left-color: #22c55e; }
.so-rak-atas { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
.so-rak-label { display: block; font-size: 10.5px; font-weight: 600; letter-spacing: .3px; text-transform: uppercase; color: var(--muted); }
.so-rak-kode { display: block; font-size: 18px; font-weight: 800; letter-spacing: .3px; color: var(--n-950); line-height: 1.2; word-break: break-word; }
.so-rak-angka { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.so-rak-angka small { display: block; font-size: 10.5px; font-weight: 600; letter-spacing: .3px; text-transform: uppercase; color: var(--muted); }
.so-rak-angka b { font-size: 14px; color: var(--n-950); font-variant-numeric: tabular-nums; }
.so-rak-buka { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding-top: 10px; border-top: 1px dashed var(--line); font-size: 13.5px; font-weight: 700; color: var(--n-700); }
.so-rak-buka i.fa-chevron-right { font-size: 12px; opacity: .7; }

.so-total { margin-top: 14px; }
</style>
@endsection

@section('content')
<div class="so-skin">

    <div class="so-kepala">
        <div class="so-kepala-kiri">
            <span class="so-kepala-ikon"><i class="fas fa-clipboard-check"></i></span>
            <div class="so-kepala-teks">
                <h1 class="so-kepala-judul">Stock Opname Fabric</h1>
                <p class="so-kepala-sub">Pilih rak untuk mulai scan barcode</p>
            </div>
        </div>
    </div>

    {{-- ===== SO & pencarian rak ===== --}}
    <div class="so-kartu">
        <div class="so-kartu-isi">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="so-label" for="no_transaksi">No Transaksi</label>
                    <select class="form-control" id="no_transaksi" name="no_transaksi" style="width: 100%;">
                        @foreach ($no_transaksi as $notrans)
                        <option value="{{ $notrans->no_transaksi }}">{{ $notrans->no_transaksi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-7">
                    <label class="so-label" for="so-cari-rak">Cari Rak</label>
                    <div class="so-cari">
                        <i class="fas fa-magnifying-glass"></i>
                        <input type="search" class="form-control" id="so-cari-rak" placeholder="Ketik kode rak, mis. GB.C.15"
                            autocomplete="off" autocorrect="off" autocapitalize="characters" spellcheck="false" enterkeyhint="search">
                    </div>
                </div>
            </div>
            <div class="so-total">
                <div class="so-progres-baris">
                    <span><i class="fas fa-barcode me-1"></i> Roll tersimpan di SO ini</span>
                    <b id="so-total-teks">-</b>
                </div>
                <div class="so-progres" id="so-total-bar"><span style="width: 0%"></span></div>
            </div>
        </div>
    </div>

    {{-- ===== Status rak: sekaligus filter ===== --}}
    <input type="hidden" id="status" name="status" value="ALL">
    @php($ikonStatus = ['Pending' => 'fa-hourglass-start', 'Partial' => 'fa-circle-half-stroke', 'Completed' => 'fa-circle-check'])
    @php($ketStatus = ['Pending' => 'Belum di-scan', 'Partial' => 'Sebagian', 'Completed' => 'Selesai'])
    <div class="so-stat-grid" id="so-status">
        <button type="button" class="so-stat is-aktif" data-status="ALL" aria-pressed="true">
            <span class="so-stat-ikon"><i class="fas fa-layer-group"></i></span>
            <span class="so-stat-isi">
                <span class="so-stat-label">Semua Rak</span>
                <span class="so-stat-angka" data-n="rak">-</span>
                <span class="so-stat-ket">di SO ini</span>
            </span>
        </button>
        @foreach ($status_so as $sts)
        <button type="button" class="so-stat is-{{ strtolower($sts->nama_pilihan) }}" data-status="{{ $sts->nama_pilihan }}" aria-pressed="false">
            <span class="so-stat-ikon"><i class="fas {{ $ikonStatus[$sts->nama_pilihan] ?? 'fa-tag' }}"></i></span>
            <span class="so-stat-isi">
                <span class="so-stat-label">{{ $sts->nama_pilihan }}</span>
                <span class="so-stat-angka" data-n="{{ $sts->nama_pilihan }}">-</span>
                <span class="so-stat-ket">{{ $ketStatus[$sts->nama_pilihan] ?? 'rak' }}</span>
            </span>
        </button>
        @endforeach
    </div>

    {{-- ===== Daftar rak ===== --}}
    <table id="datatable" class="so-rak-tabel">
        <thead>
            <tr>
                <th>Rak</th>
                <th>Item</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    var RUT_SCAN = @json(route('proses-scan-so'));
    var KUNCI_FILTER = 'so_daftar_rak_filter';
    var KELAS_STATUS = { Pending: 'is-pending', Partial: 'is-partial', Completed: 'is-completed' };

    function teks(v) { return $('<div>').text(v == null ? '' : String(v)).html(); }
    // Nilai dari server sudah di-escape Yajra DataTables (escape = '*'): untuk tampilan dipakai apa
    // adanya, untuk URL dikembalikan dulu ke teks aslinya
    function asli(v) { return $('<textarea>').html(v == null ? '' : String(v)).val(); }
    function angka(v, des) {
        return (parseFloat(v) || 0).toLocaleString('id-ID', { minimumFractionDigits: des, maximumFractionDigits: des });
    }

    // Pilihan SO & status diingat selama tab browser terbuka (kembali dari halaman scan / setelah Save)
    function bacaFilter() {
        try { return JSON.parse(sessionStorage.getItem(KUNCI_FILTER) || 'null'); } catch (e) { return null; }
    }
    function simpanFilter() {
        try {
            sessionStorage.setItem(KUNCI_FILTER, JSON.stringify({ no_transaksi: $('#no_transaksi').val(), status: $('#status').val() }));
        } catch (e) { /* mode privat: cukup tidak diingat */ }
    }

    var awal = bacaFilter();
    if (awal) {
        if (awal.no_transaksi && $('#no_transaksi option').filter(function () { return this.value === awal.no_transaksi; }).length) {
            $('#no_transaksi').val(awal.no_transaksi);
        }
        if (awal.status && $('#so-status [data-status="' + awal.status + '"]').length) {
            $('#status').val(awal.status);
        }
    }
    tandaiStatus();

    $('#no_transaksi').select2({ theme: 'bootstrap4', width: '100%', dropdownCssClass: 'so-dropdown' });

    function tandaiStatus() {
        var s = $('#status').val();
        $('#so-status .so-stat').each(function () {
            var aktif = $(this).data('status') === s;
            $(this).toggleClass('is-aktif', aktif).attr('aria-pressed', aktif ? 'true' : 'false');
        });
    }

    // Satu kartu per rak; seluruh kartu adalah link ke halaman scan
    function kartuRak(kode, type, r) {
        if (type !== 'display') { return kode; }
        var roll = parseInt(r.qty_roll, 10) || 0;
        var scan = parseInt(r.qty_roll_scan, 10) || 0;
        var persen = roll > 0 ? Math.min(100, Math.round(scan / roll * 100)) : 0;
        var kelas = KELAS_STATUS[r.status] || 'is-partial';
        var label = r.status || 'Lebih scan';
        var url = RUT_SCAN + '/' + encodeURIComponent(asli(kode)) + '/' + encodeURIComponent(asli(r.id));

        return '<a class="so-rak ' + kelas + '" href="' + teks(url) + '">'
            + '<div class="so-rak-atas">'
            +   '<div><span class="so-rak-label">Rak</span><span class="so-rak-kode">' + kode + '</span></div>'
            +   '<span class="so-pil ' + kelas + '"' + (r.status ? '' : ' title="Roll tersimpan melebihi saldo rak"') + '>'
            +     '<span class="so-pil-titik"></span>' + label + '</span>'
            + '</div>'
            + '<div>'
            +   '<div class="so-progres-baris"><span>Roll</span><b>' + angka(scan, 0) + ' / ' + angka(roll, 0) + '</b></div>'
            +   '<div class="so-progres ' + kelas + '"><span style="width:' + persen + '%"></span></div>'
            + '</div>'
            + '<div class="so-rak-angka">'
            +   '<div><small>Qty Rak</small><b>' + angka(r.qty, 2) + '</b></div>'
            +   '<div><small>Qty Scan</small><b>' + angka(r.qty_scan, 2) + '</b></div>'
            + '</div>'
            + '<div class="so-rak-buka"><span><i class="fas fa-barcode me-1"></i> ' + (r.status === 'Completed' ? 'Lihat / scan lagi' : 'Mulai scan') + '</span>'
            +   '<i class="fas fa-chevron-right"></i></div>'
            + '</a>';
    }

    var hp = window.matchMedia('(max-width: 575px)').matches;

    let datatable = $("#datatable").DataTable({
        serverSide: true,
        processing: true,
        ordering: false,
        autoWidth: false,
        pageLength: 24,
        lengthMenu: [12, 24, 48, 96],
        // Halaman & pencarian diingat (sessionStorage) - kembali dari halaman scan tetap di posisi yang sama
        stateSave: true,
        stateDuration: -1,
        pagingType: hp ? 'simple' : 'simple_numbers',
        dom: "t<'so-dt-bawah'<'so-dt-info'i><'so-dt-hal'p>>",
        language: {
            processing: '<span class="spinner-border spinner-border-sm me-2"></span> Memuat...',
            emptyTable: '<div class="so-kosong"><i class="fas fa-warehouse"></i><span>Tidak ada rak untuk filter ini.</span></div>',
            zeroRecords: '<div class="so-kosong"><i class="fas fa-magnifying-glass"></i><span>Tidak ada rak yang cocok.</span></div>',
            info: '_START_-_END_ dari _TOTAL_ rak',
            infoEmpty: '0 rak',
            infoFiltered: '',
            paginate: { previous: '<i class="fas fa-chevron-left"></i>', next: '<i class="fas fa-chevron-right"></i>' }
        },
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('list-stok-opname') }}',
            dataType: 'json',
            dataSrc: 'data',
            data: function(d) {
                d.no_transaksi = $('#no_transaksi').val();
                d.status = $('#status').val();
            },
        },
        columns: [
            { data: 'kode_lok', render: kartuRak },
            { data: 'itemdesc', visible: false }
        ]
    });

    // Kotak status & progres SO dari ringkasan server (semua rak, sebelum difilter)
    datatable.on('xhr.dt', function (e, settings, json) {
        var r = (json && json.ringkas) || null;
        $('#so-status [data-n]').each(function () {
            var k = $(this).data('n');
            $(this).text(r && r[k] != null ? angka(r[k], 0) : '-');
        });
        var roll = r ? r.roll : 0;
        var scan = r ? r.roll_scan : 0;
        var persen = roll > 0 ? Math.min(100, Math.round(scan / roll * 100)) : 0;
        $('#so-total-teks').text(r ? angka(scan, 0) + ' / ' + angka(roll, 0) + ' roll (' + persen + '%)' : '-');
        $('#so-total-bar').toggleClass('is-completed', roll > 0 && scan >= roll).children('span').css('width', persen + '%');
    });

    function dataTableReload() {
        datatable.ajax.reload();
    }

    $('#so-cari-rak').val(datatable.search());
    var tundaCari;
    $('#so-cari-rak').on('input search', function () {
        var v = $.trim(this.value);
        clearTimeout(tundaCari);
        tundaCari = setTimeout(function () {
            if (v !== datatable.search()) { datatable.search(v).draw(); }
        }, 350);
    });

    $('#no_transaksi').on('change', function () {
        simpanFilter();
        dataTableReload();
    });

    $('#so-status').on('click', '.so-stat', function () {
        $('#status').val($(this).data('status'));
        tandaiStatus();
        simpanFilter();
        dataTableReload();
    });
</script>
@endsection
