@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">

    <style type="text/css">
        /* ---- Kartu angka ringkasan ---- */
        .stat-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #fff;
            padding: 10px 12px;
            height: 100%;
        }

        .stat-card .stat-label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #6b7280;
        }

        .stat-card .stat-value {
            font-size: 1.35rem;
            font-weight: 700;
            line-height: 1.2;
            font-variant-numeric: tabular-nums;
        }

        .stat-card .stat-icon {
            font-size: 1.1rem;
            opacity: .35;
        }

        .stat-card .rincian {
            font-size: .72rem;
            color: #6b7280;
        }

        /* ---- Daftar lokasi ----
           Tiap main lokasi jadi satu kartu kecil supaya batas antar area jelas,
           dengan warna mengikuti tema aplikasi: navy #082149 & teal #238380. */
        .lokasi-group {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 10px;
            transition: box-shadow .15s ease;
        }

        .lokasi-group:hover {
            box-shadow: 0 2px 10px rgba(8, 33, 73, .07);
        }

        .lokasi-group-head {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            border: 0;
            text-align: left;
            padding: 10px 12px;
            font-weight: 600;
            color: #082149;
            background: linear-gradient(180deg, #fbfcfe 0%, #f4f6fa 100%);
        }

        .lokasi-group-head:hover {
            background: linear-gradient(180deg, #f4f6fa 0%, #eceff5 100%);
        }

        /* Kotak ikon kecil di kiri nama gedung */
        .ikon-chip {
            flex: 0 0 auto;
            width: 26px;
            height: 26px;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            background: #082149;
            color: #fff;
        }

        .lokasi-group-head .chev {
            font-size: .7rem;
            color: #adb5bd;
            transition: transform .2s ease;
        }

        .lokasi-group-head[aria-expanded="true"] .chev {
            transform: rotate(90deg);
        }

        /* Sub lokasi pakai bar, bukan deretan angka: sekali lihat langsung ketahuan
           area mana yang padat tanpa harus membaca angkanya satu per satu. */
        .sub-row {
            display: block;
            width: 100%;
            border: 0;
            border-top: 1px solid #f4f5f7;
            border-left: 3px solid transparent;
            background: #fff;
            text-align: left;
            padding: 9px 12px 10px 16px;
            color: #495057;
            transition: background .12s ease, border-color .12s ease;
        }

        .sub-row:hover {
            background: #f7fbfb;
            border-left-color: #238380;
        }

        .sub-row .baris-atas {
            display: flex;
            align-items: baseline;
            gap: 12px;
        }

        .sub-row .bar {
            /* wajib block: <span> default-nya inline & height-nya diabaikan */
            display: block;
            height: 5px;
            border-radius: 3px;
            background: #eef1f4;
            margin-top: 6px;
            overflow: hidden;
        }

        .sub-row .bar span {
            display: block;
            height: 100%;
            border-radius: 3px;
            background: linear-gradient(90deg, #238380 0%, #3fb8b0 100%);
            transition: width .3s ease;
        }

        .sub-row.kosong .bar {
            display: none;
        }

        /* Petunjuk halus bahwa barisnya bisa diklik; muncul saat disentuh saja */
        .sub-chev {
            flex: 0 0 auto;
            font-size: .65rem;
            color: #ced4da;
            opacity: 0;
            transition: opacity .12s ease;
        }

        .sub-row:hover .sub-chev {
            opacity: 1;
        }

        .sub-row.kosong .sub-chev {
            display: none;
        }

        .sub-row.kosong {
            color: #adb5bd;
        }

        .nama {
            flex: 1 1 auto;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .nama .alias {
            color: #adb5bd;
            font-weight: 400;
        }

        /* Angka dibungkus pil supaya jadi titik jatuh mata di ujung kanan baris */
        .angka {
            flex: 0 0 auto;
            font-variant-numeric: tabular-nums;
            font-size: .78rem;
            font-weight: 600;
            color: #238380;
            background: #e7f3f2;
            border-radius: 999px;
            padding: 2px 9px;
        }

        .lokasi-group-head .angka {
            color: #fff;
            background: #082149;
        }

        .sub-row.kosong .angka {
            color: #adb5bd;
            background: #f1f3f5;
        }

        /* Mesin tanpa lokasi: satu-satunya warna peringatan di halaman ini */
        .row-tanpa-lokasi {
            border-top: 0;
            border-left-color: #f0b429;
            background: #fffdf5;
            padding: 11px 12px;
        }

        .row-tanpa-lokasi:hover {
            background: #fff8e6;
            border-left-color: #d9a017;
        }

        .row-tanpa-lokasi .nama {
            color: #96601a;
            font-weight: 600;
        }

        .row-tanpa-lokasi .ikon-chip {
            width: 22px;
            height: 22px;
            border-radius: 6px;
            background: #f5c451;
            color: #7a4b08;
            margin-right: 8px;
        }

        .row-tanpa-lokasi .angka {
            color: #7a4b08;
            background: #f7dfa0;
        }

        /* ---- Tabel rekap history & detail lokasi ----
           Dua-duanya pakai gaya yang sama; yang detail lokasi di-scroll dengan
           header nempit di atas (.dataTables_scrollHead memegang header kloningannya). */
        #tabelHistory thead th,
        #tabelDetailLokasi thead th,
        .dataTables_scrollHead thead th {
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef !important;
            border-top: 0;
            font-size: .72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #6b7280;
            white-space: nowrap;
        }

        #tabelHistory tbody td,
        #tabelDetailLokasi tbody td {
            border-top: 1px solid #f4f5f7;
            padding: 9px 12px;
            font-size: .85rem;
            vertical-align: middle;
        }

        #tabelHistory tbody tr {
            cursor: pointer;
        }

        #tabelHistory tbody tr:hover td,
        #tabelDetailLokasi tbody tr:hover td {
            background: #f7fbfb;
        }

        /* Kolom identitas ditebalkan: Kode QR di detail lokasi, kolom kedua di rekap */
        #tabelHistory tbody tr td:nth-child(2),
        #tabelDetailLokasi tbody tr td:first-child {
            font-weight: 600;
            color: #212529;
            letter-spacing: .3px;
        }

        .dataTables_scrollBody {
            border-bottom: 1px solid #f1f3f5;
        }

        /* Jumlah mesin di kiri toolbar, sebaris dengan kotak Search */
        .jumlah-tabel {
            font-size: .85rem;
            color: #6b7280;
            line-height: 1.1;
            padding-top: 2px;
        }

        .jumlah-tabel .angka-besar {
            font-size: 1.6rem;
            font-weight: 700;
            color: #082149;
            margin-right: 4px;
            font-variant-numeric: tabular-nums;
        }

        .pil {
            display: inline-block;
            font-size: .68rem;
            font-weight: 600;
            letter-spacing: .3px;
            padding: 2px 8px;
            border-radius: 999px;
            white-space: nowrap;
        }

        .pil-beli {
            color: #1d4ed8;
            background: #e8eefc;
        }

        .pil-sewa {
            color: #8a5a00;
            background: #fbf0dc;
        }

        .pil-active {
            color: #1b7c50;
            background: #e4f4ec;
        }

        .pil-idle {
            color: #8a5a00;
            background: #fbf0dc;
        }

        .pil-breakdown {
            color: #b42318;
            background: #fce9e7;
        }

        .pil-lain {
            color: #6b7280;
            background: #f1f3f5;
        }

        .lokasi-kosong {
            color: #adb5bd;
            font-style: italic;
        }

        /* ---- Garis waktu di dalam modal riwayat mesin ---- */
        .timeline .history-item {
            position: relative;
            padding: 10px 2px 10px 34px;
        }

        .timeline .history-item::before {
            content: '';
            position: absolute;
            left: 11px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #f1f3f5;
        }

        .timeline .history-item:first-child::before {
            top: 16px;
        }

        .timeline .history-item:last-child::before {
            bottom: calc(100% - 16px);
        }

        .timeline .dot {
            position: absolute;
            left: 0;
            top: 9px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .6rem;
            background: #e7f3f2;
            color: #238380;
            border: 2px solid #fff;
        }

        /* Perpindahan terbaru = posisi mesin sekarang, jadi ditandai lebih tegas */
        .timeline .history-item:first-child .dot {
            background: #238380;
            color: #fff;
        }

        .timeline .waktu {
            font-size: .75rem;
            color: #adb5bd;
            font-variant-numeric: tabular-nums;
        }

        .timeline .jalur {
            font-size: .85rem;
            color: #212529;
            margin-top: 1px;
        }

        .toggle-kosong {
            border: 0;
            background: transparent;
            padding: 6px 2px;
            font-size: .8rem;
            color: #6b7280;
        }

        .toggle-kosong:hover {
            color: #238380;
        }

    </style>
@endsection

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
        <h5 class="fw-bold mb-0">Mutasi Mesin</h5>
        <a href="{{ route('create_asset_mesin_mutasi') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Mutasi Baru
        </a>
    </div>

    <!-- Ringkasan: dipakai untuk cek cepat "totalnya masih masuk akal atau tidak" -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <span class="stat-label">Total Mesin</span>
                    <i class="fa-solid fa-gears stat-icon"></i>
                </div>
                <div class="stat-value">{{ number_format($ringkasan['total_mesin']) }}</div>
                <small class="rincian">
                    Beli {{ number_format($ringkasan['total_beli']) }} &middot;
                    Sewa {{ number_format($ringkasan['total_sewa']) }}
                </small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <span class="stat-label">Sedang Jalan</span>
                    <i class="fa-solid fa-circle-play stat-icon text-success"></i>
                </div>
                <div class="stat-value text-success">{{ number_format($ringkasan['total_active']) }}</div>
                <small class="rincian">Status ACTIVE</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <span class="stat-label">Idle / Breakdown</span>
                    <i class="fa-solid fa-circle-pause stat-icon text-warning"></i>
                </div>
                <div class="stat-value">
                    <span class="text-warning">{{ number_format($ringkasan['total_idle']) }}</span>
                    <span class="text-muted">/</span>
                    <span class="text-danger">{{ number_format($ringkasan['total_breakdown']) }}</span>
                </div>
                <small class="rincian">Tidak sedang dipakai produksi</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <span class="stat-label">Lokasi Terisi</span>
                    <i class="fa-solid fa-location-dot stat-icon"></i>
                </div>
                <div class="stat-value">{{ number_format($ringkasan['lokasi_terisi']) }}</div>
                <small class="rincian">
                    {{ number_format($ringkasan['lokasi_kosong']) }} lokasi masih kosong
                </small>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="input-group input-group-sm mb-2">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="text" class="form-control" id="cariLokasi" placeholder="Cari lokasi / area..."
                    autocomplete="off">
            </div>

            <div id="daftarLokasi">
                {{-- Mesin tanpa lokasi ditaruh paling atas: ini yang paling perlu dibereskan --}}
                @if ($tanpaLokasi && $tanpaLokasi->total_mesin > 0)
                    <div class="lokasi-group" data-cari="belum ada lokasi tanpa lokasi"
                        data-total="{{ $tanpaLokasi->total_mesin }}">
                        <button type="button" class="sub-row btn-detail-lokasi row-tanpa-lokasi" data-tanpa-lokasi="1"
                            data-cari="belum ada lokasi tanpa lokasi" data-total="{{ $tanpaLokasi->total_mesin }}"
                            data-nama="Belum Ada Lokasi">
                            <span class="baris-atas">
                                <span class="ikon-chip"><i class="fas fa-exclamation"></i></span>
                                <span class="nama">Belum ada lokasi</span>
                                <span class="angka">{{ number_format($tanpaLokasi->total_mesin) }}</span>
                            </span>
                        </button>
                    </div>
                @endif

                {{-- Semua grup tertutup saat halaman dibuka: yang dicari orang biasanya satu
                     area tertentu, bukan seluruh isi pabrik sekaligus. --}}
                @forelse ($grupLokasi as $grup)
                    <div class="lokasi-group" data-cari="{{ strtolower($grup['main_lokasi']) }}"
                        data-total="{{ $grup['total_mesin'] }}">
                        <button class="lokasi-group-head" type="button" data-bs-toggle="collapse"
                            data-bs-target="#grup{{ $grup['id_main'] }}" aria-expanded="false">
                            <i class="fas fa-chevron-right chev"></i>
                            <span class="ikon-chip"><i class="fas fa-building"></i></span>
                            <span class="nama">{{ $grup['main_lokasi'] }}</span>
                            <span class="angka">{{ number_format($grup['total_mesin']) }}</span>
                        </button>

                        <div class="collapse" id="grup{{ $grup['id_main'] }}">
                            @foreach ($grup['sub'] as $sub)
                                {{-- Lokasi kosong langsung disembunyikan dari server, tidak menunggu
                                     JS: mayoritas lokasi memang belum terisi & bikin daftar panjang. --}}
                                <button type="button"
                                    class="sub-row btn-detail-lokasi {{ $sub->total_mesin == 0 ? 'kosong d-none' : '' }}"
                                    data-cari="{{ strtolower($sub->nama_lokasi ?? '') }}"
                                    data-total="{{ $sub->total_mesin }}" data-id="{{ $sub->id_lokasi }}"
                                    data-nama="{{ $sub->nama_lokasi }}"
                                    {{ $sub->total_mesin == 0 ? 'disabled' : '' }}>
                                    <span class="baris-atas">
                                        <span class="nama">
                                            {{ $sub->sub_lokasi }}
                                            @if ($sub->alias_lokasi)
                                                <span class="alias">{{ $sub->alias_lokasi }}</span>
                                            @endif
                                        </span>
                                        <span class="angka">{{ $sub->total_mesin ?: '-' }}</span>
                                        <i class="fas fa-chevron-right sub-chev"></i>
                                    </span>
                                    <span class="bar">
                                        <span
                                            style="width: {{ $grup['maks_sub'] > 0 ? round(($sub->total_mesin / $grup['maks_sub']) * 100) : 0 }}%"></span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4"><small>Master lokasi masih kosong.</small></div>
                @endforelse
            </div>

            <div id="kosongHasil" class="text-center text-muted py-4 d-none">
                <small>Tidak ada lokasi yang cocok.</small>
            </div>

            {{-- Mayoritas lokasi memang belum terisi; kalau semua dirender, yang dicari orang
                 malah tenggelam. Jadi disembunyikan dulu & bisa dibuka lewat tombol ini. --}}
            @if ($ringkasan['lokasi_kosong'] > 0)
                <button type="button" class="toggle-kosong" id="btnToggleKosong">
                    Tampilkan {{ $ringkasan['lokasi_kosong'] }} lokasi kosong
                </button>
            @endif
        </div>
    </div>

    {{-- Rekap seluruh transaksi mutasi. Dipaging & dicari di sisi server karena
         isinya terus bertambah tiap scan. --}}
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-baseline justify-content-between mb-2">
                <h6 class="fw-bold mb-0">History Mutasi</h6>
                <small class="text-muted">Klik baris untuk melihat riwayat mesinnya</small>
            </div>

            <div class="table-responsive">
                <table id="tabelHistory" class="table table-hover align-middle text-nowrap w-100">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kode QR</th>
                            <th>Sumber</th>
                            <th>Jenis</th>
                            <th>Merk</th>
                            <th>Serial Number</th>
                            <th>Lokasi Asal</th>
                            <th>Lokasi Tujuan</th>
                            <th>User</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal riwayat satu mesin -->
    <div class="modal fade" id="modalHistoryMesin" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h6 class="modal-title fw-bold mb-0" id="judulHistoryQr">-</h6>
                        <small class="text-muted" id="subJudulHistory">-</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="isiHistoryMesin" class="timeline"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal detail mesin per lokasi -->
    <div class="modal fade" id="modalDetailLokasi" tabindex="-1" aria-hidden="true">
        {{-- modal-dialog-scrollable sengaja tidak dipakai: tabelnya sudah punya area
             gulir sendiri, kalau ditumpuk jadi dua scrollbar bersarang. --}}
        <div class="modal-dialog modal-xl modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold mb-0" id="judulLokasi">Detail Lokasi</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="tabelDetailLokasi" class="table table-hover align-middle text-nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Kode QR</th>
                                    <th>Sumber</th>
                                    <th>Jenis</th>
                                    <th>Merk</th>
                                    <th>Tipe</th>
                                    <th>Serial Number</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        // Modul Asset: senyapkan alert bawaan DataTables saat ajax gagal, cukup dicatat di console
        $.fn.dataTable.ext.errMode = function(settings, techNote, message) {
            console.error('DataTable ajax error:', message);
        };
    </script>

    <script>
        // ---- Pencarian & filter lokasi ----
        // Seluruh sebaran sudah dirender dari server, jadi filternya cukup di sisi browser:
        // tidak ada request tambahan setiap user mengetik.
        let sembunyikanKosong = true;

        function terapkanFilter() {
            let kata = $('#cariLokasi').val().trim().toLowerCase();
            let adaHasil = false;

            $('#daftarLokasi .lokasi-group').each(function() {
                let grup = $(this);
                let cocokGrup = String(grup.data('cari')).indexOf(kata) !== -1;
                let subTampil = 0;

                grup.find('.sub-row').each(function() {
                    let sub = $(this);
                    let kosong = sub.data('total') == 0;
                    // Sub lokasi ikut tampil kalau nama areanya yang cocok, supaya
                    // mencari nama area tidak malah mengosongkan isinya
                    let cocok = cocokGrup || String(sub.data('cari')).indexOf(kata) !== -1;
                    let tampil = cocok && !(sembunyikanKosong && kosong);

                    sub.toggleClass('d-none', !tampil);
                    if (tampil) subTampil++;
                });

                let tampilGrup = subTampil > 0;
                grup.toggleClass('d-none', !tampilGrup);
                if (tampilGrup) adaHasil = true;

                // Saat mencari, isi grup dibuka otomatis supaya hasilnya langsung kelihatan;
                // begitu pencariannya dikosongkan, kembali tertutup seperti keadaan default.
                // aria-expanded ikut diset manual karena kelas .show dipasang langsung,
                // tidak lewat Bootstrap - kalau tidak, panah chevron-nya jadi salah arah.
                if (tampilGrup) {
                    grup.find('.collapse').toggleClass('show', kata !== '');
                    grup.find('.lokasi-group-head').attr('aria-expanded', kata !== '' ? 'true' : 'false');
                }
            });

            $('#kosongHasil').toggleClass('d-none', adaHasil);
        }

        $('#cariLokasi').on('keyup', terapkanFilter);

        $('#btnToggleKosong').on('click', function() {
            sembunyikanKosong = !sembunyikanKosong;
            $(this).text(sembunyikanKosong ?
                'Tampilkan {{ $ringkasan['lokasi_kosong'] }} lokasi kosong' :
                'Sembunyikan lokasi kosong');
            terapkanFilter();
        });

        terapkanFilter();

        // ---- Detail mesin per lokasi ----
        // DataTables-nya dibuat sekali lalu isinya diganti tiap lokasi dibuka,
        // supaya tidak menumpuk instance tiap kali modal dipanggil.
        var idLokasiAktif = null;
        var tanpaLokasiAktif = 0;

        // Elemennya baru dibuat di initComplete, jadi pemanggilan drawCallback pertama
        // (yang jalan sebelum itu) sengaja dibiarkan tidak menemukan apa-apa
        function setJumlahDetail(jumlah) {
            $('#jumlahDetail').html(`<span class="angka-besar">${jumlah}</span> mesin`);
        }

        function pilSumber(sumber) {
            if (!sumber) return '<span class="pil pil-lain">-</span>';
            return `<span class="pil ${sumber === 'SEWA' ? 'pil-sewa' : 'pil-beli'}">${sumber}</span>`;
        }

        function pilStatus(status) {
            let kelas = {
                ACTIVE: 'pil-active',
                IDLE: 'pil-idle',
                BREAKDOWN: 'pil-breakdown',
            } [status] ?? 'pil-lain';
            return `<span class="pil ${kelas}">${status || '-'}</span>`;
        }

        var tabelDetail = $('#tabelDetailLokasi').DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            searching: true,
            // Paging & "Show entries" dimatikan: isi satu lokasi cukup digulir,
            // headernya tetap nempel di atas selama menggulir
            paging: false,
            lengthChange: false,
            info: false,
            scrollY: '50vh',
            scrollX: true,
            scrollCollapse: true,
            // Jangan menembak ajax saat halaman dibuka: datanya baru dibutuhkan
            // setelah salah satu lokasi diklik
            deferLoading: 0,
            ajax: {
                url: '{{ route('getdata_mesin_per_lokasi') }}',
                data: function(d) {
                    d.id_lokasi = idLokasiAktif;
                    d.tanpa_lokasi = tanpaLokasiAktif;
                }
            },
            columns: [
                { data: 'kode_qr', defaultContent: '-' }, // Kode QR
                { data: 'sumber', defaultContent: '-', render: pilSumber }, // Sumber
                { data: 'nm_jenis', defaultContent: '-' }, // Jenis
                { data: 'nm_merk', defaultContent: '-' }, // Merk
                { data: 'tipe', defaultContent: '-' }, // Tipe
                { data: 'serial_number', defaultContent: '-' }, // Serial Number
                { data: 'status', defaultContent: '-', render: pilStatus }, // Status
            ],
            language: {
                emptyTable: 'Tidak ada mesin di lokasi ini.',
                zeroRecords: 'Tidak ada mesin yang cocok dengan pencarian.',
            },
            // Kolom kiri baris toolbar DataTables dibiarkan kosong setelah "Show entries"
            // dimatikan - ruang itu dipakai untuk jumlah mesin, sebaris dengan Search.
            initComplete: function() {
                $('#tabelDetailLokasi_filter').closest('.row').children().first()
                    .html('<div id="jumlahDetail" class="jumlah-tabel"></div>');

                setJumlahDetail(this.api().rows().count());
            },
            drawCallback: function() {
                setJumlahDetail(this.api().rows().count());
            }
        });

        $(document).on('click', '.btn-detail-lokasi', function() {
            idLokasiAktif = $(this).data('id') ?? null;
            tanpaLokasiAktif = $(this).data('tanpa-lokasi') ? 1 : 0;

            $('#judulLokasi').text($(this).data('nama') || 'Detail Lokasi');
            $('#modalDetailLokasi').modal('show');
            tabelDetail.ajax.reload();
        });

        // Kolom DataTables sering salah ukuran kalau tabelnya dirender saat modal masih
        // tersembunyi - dihitung ulang setelah modalnya benar-benar tampil.
        $('#modalDetailLokasi').on('shown.bs.modal', function() {
            tabelDetail.columns.adjust();
        });

        // ---- Rekap history mutasi ----
        function escapeHtml(teks) {
            return $('<div>').text(teks ?? '').html();
        }

        function labelLokasi(lokasi) {
            return lokasi ? escapeHtml(lokasi) : '<span class="lokasi-kosong">Belum ada lokasi</span>';
        }

        // serverSide: jumlah barisnya terus bertambah tiap scan, jadi pencarian &
        // paging diserahkan ke MySQL, bukan ditarik semua ke browser.
        var tabelHistory = $('#tabelHistory').DataTable({
            processing: true,
            serverSide: true,
            searching: true,
            lengthChange: false,
            pageLength: 10,
            scrollX: true,
            // Urutannya selalu terbaru di atas & ditentukan server, jadi header
            // tidak dibuat bisa diklik supaya tidak menjanjikan sesuatu yang tidak ada
            ordering: false,
            ajax: '{{ route('getdata_history_mutasi') }}',
            columns: [
                {
                    data: 'tgl_mutasi',
                    // Tanggal & jam digabung: dua kolom terpisah cuma makan lebar
                    render: function(tgl, tipe, row) {
                        return `${escapeHtml(tgl)} <span class="text-muted">${escapeHtml(row.jam)}</span>`;
                    }
                }, // Tanggal
                { data: 'kode_qr', defaultContent: '-' }, // Kode QR
                {
                    data: 'sumber',
                    defaultContent: '-',
                    render: function(sumber) {
                        if (!sumber) return '<span class="pil">-</span>';
                        return `<span class="pil ${sumber === 'SEWA' ? 'pil-sewa' : 'pil-beli'}">${sumber}</span>`;
                    }
                }, // Sumber
                { data: 'nm_jenis', defaultContent: '-' }, // Jenis
                { data: 'nm_merk', defaultContent: '-' }, // Merk
                { data: 'serial_number', defaultContent: '-' }, // Serial Number
                { data: 'lokasi_asal', render: labelLokasi }, // Lokasi Asal
                { data: 'lokasi_tujuan', defaultContent: '-' }, // Lokasi Tujuan
                { data: 'created_by', defaultContent: '-' }, // User
            ],
            language: {
                emptyTable: 'Belum ada mutasi tercatat.',
                zeroRecords: 'Tidak ada mutasi yang cocok dengan pencarian.',
            },
        });

        // ---- Modal riwayat satu mesin ----
        $('#tabelHistory tbody').on('click', 'tr', function() {
            let baris = tabelHistory.row(this).data();
            if (!baris) return;

            $('#judulHistoryQr').text(baris.kode_qr);
            $('#subJudulHistory').text('Memuat riwayat...');
            $('#isiHistoryMesin').empty();
            $('#modalHistoryMesin').modal('show');

            $.ajax({
                type: 'GET',
                url: '{{ route('getdata_history_mesin') }}',
                data: {
                    kode_qr: baris.kode_qr
                },
                success: function(res) {
                    let u = res.unit;
                    let mesin = u ? [u.nm_jenis, u.nm_merk, u.tipe].filter(Boolean).join(' ') : '';

                    $('#subJudulHistory').html(
                        (mesin ? escapeHtml(mesin) + ' &middot; ' : '') +
                        (u?.serial_number ? 'SN ' + escapeHtml(u.serial_number) + ' &middot; ' : '') +
                        `${res.riwayat.length} perpindahan`
                    );

                    if (!res.riwayat.length) {
                        $('#isiHistoryMesin').html(
                            '<div class="text-center text-muted py-3"><small>Belum ada riwayat.</small></div>');
                        return;
                    }

                    res.riwayat.forEach(function(r, i) {
                        $('#isiHistoryMesin').append(`
                            <div class="history-item">
                                <span class="dot"><i class="fa-solid fa-${i === 0 ? 'location-dot' : 'arrow-right'}"></i></span>
                                <div class="waktu">${escapeHtml(r.tgl_mutasi)} ${escapeHtml(r.jam)} &middot; ${escapeHtml(r.created_by) || '-'}</div>
                                <div class="jalur">${labelLokasi(r.lokasi_asal)} <i class="fas fa-arrow-right mx-1 text-muted" style="font-size:.7rem"></i> ${escapeHtml(r.lokasi_tujuan)}</div>
                            </div>`);
                    });
                },
                error: function(xhr) {
                    console.error('Gagal memuat riwayat:', xhr.responseText);
                    $('#subJudulHistory').text('Gagal memuat riwayat.');
                }
            });
        });
    </script>
@endsection
