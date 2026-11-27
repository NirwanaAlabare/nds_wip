@extends('layouts.index')

@php
    $warna = [
        'Menunggu Approval' => ['#fbf3df', '#b98a2e', 'fa-hourglass-half'],
        'Proses PO' => ['#e6f3eb', '#4a8c68', 'fa-check-circle'],
        'Diterima Sebagian' => ['#e6f3eb', '#4a8c68', 'fa-check-circle'],
        'Selesai' => ['#e6f3eb', '#4a8c68', 'fa-check-circle'],
        'Ditolak' => ['#f9e8ea', '#c0505f', 'fa-times-circle'],
        'Dibatalkan' => ['#e7e9ee', '#525c6b', 'fa-ban'],
    ];
    // Tab: menunggu keputusan saya / sudah disetujui / ditolak
    // Keputusan saya (approver 1 / 2) pada request ini
    $hasilSaya = fn($r) => collect($r['approvers'])->firstWhere('saya', true)['hasil'] ?? null;
    $tabOf = function ($r) use ($hasilSaya) {
        if ($r['status'] === 'Dibatalkan') {
            return 'batal';
        }
        $h = $hasilSaya($r);
        if ($h === 'Disetujui') {
            return 'setuju';
        }
        if ($h === 'Ditolak' || $r['status'] === 'Ditolak') {
            return 'ditolak';
        }
        return $r['status'] === 'Menunggu Approval' ? 'menunggu' : 'setuju';
    };
    $jumlah = collect($requests)->countBy(fn($r) => $tabOf($r));
    $angka = function ($n) {
        $t = number_format((float) $n, 2, ',', '.');
        return str_ends_with($t, ',00') ? substr($t, 0, -3) : $t;
    };
    // Barang sama (satuan, tgl, mata uang & harga sama) dari beberapa sub dept digabung, rincian sub dept disimpan
    $gabung = function ($items) {
        $hasil = [];
        foreach ($items as $it) {
            $k = implode('|', [$it['item_id'], $it['unit_id'], $it['tgl_butuh'], $it['currency_id'], $it['harga']]);
            if (!isset($hasil[$k])) {
                $hasil[$k] = $it + ['rincian' => []];
                $hasil[$k]['qty'] = 0;
            }
            $hasil[$k]['qty'] += $it['qty'];
            $hasil[$k]['rincian'][] = ['nama' => $it['sub_dept'] ?: '-', 'qty' => $it['qty']];
        }
        return array_values($hasil);
    };
    // Label urgensi tgl butuh (hanya untuk yang masih menunggu): sudah lewat / <= 3 hari lagi
    $desak = function ($tgl) {
        if (!$tgl) {
            return null;
        }
        $sisa = (int) round((strtotime($tgl) - strtotime(date('Y-m-d'))) / 86400);
        if ($sisa < 0) {
            return ['lewat', 'fa-exclamation-circle', 'Lewat ' . abs($sisa) . ' hari'];
        }
        if ($sisa === 0) {
            return ['dekat', 'fa-clock', 'Dibutuhkan hari ini'];
        }
        return $sisa <= 3 ? ['dekat', 'fa-clock', $sisa . ' hari lagi'] : null;
    };
    // Yang menunggu saya diurutkan dari tgl butuh paling dekat; tab lain tetap urutan semula
    $requests = collect($requests)->values()
        ->map(fn($r, $i) => $r + ['_urut' => $i])
        ->sortBy(fn($r) => $tabOf($r) === 'menunggu' ? [0, $r['tgl_butuh'] ?: '9999-12-31', $r['_urut']] : [1, '', $r['_urut']])
        ->values()
        ->all();
@endphp

@section('custom-link')
    <style>
        .ap-page {
            --ap-accent: #4a6fa5;
            --ap-soft: rgba(74, 111, 165, .14);
            color: #1f2937;
        }

        .ap-hero {
            position: relative;
            overflow: hidden;
            padding: 20px 24px;
            border-radius: 16px;
            background: linear-gradient(120deg, #3f5f8f, #6a8fc4);
            color: #fff;
            margin-bottom: 16px;
        }

        .ap-hero::after {
            content: '';
            position: absolute;
            right: -60px;
            top: -80px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .08);
        }

        .ap-hero .lbl {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.4px;
            opacity: .85;
            text-transform: uppercase;
        }

        .ap-hero h4 {
            margin: 2px 0 4px;
            font-weight: 800;
        }

        .ap-hero p {
            margin: 0;
            opacity: .9;
            font-size: 13px;
        }

        .ap-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
        }

        .ap-tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border: 1px solid #dde5ef;
            border-radius: 10px;
            background: #fff;
            font-size: 13px;
            font-weight: 700;
            color: #4b5563;
            cursor: pointer;
            transition: all .15s ease;
        }

        .ap-tab .n {
            min-width: 22px;
            padding: 1px 7px;
            border-radius: 10px;
            background: #eef1f5;
            font-size: 11.5px;
            text-align: center;
        }

        .ap-tab:hover {
            border-color: #b9cbe3;
        }

        .ap-tab.aktif {
            background: var(--ap-accent);
            border-color: var(--ap-accent);
            color: #fff;
        }

        .ap-tab.aktif .n {
            background: rgba(255, 255, 255, .25);
        }

        .ap-tab.menunggu .n {
            background: #fbf3df;
            color: #b98a2e;
        }

        .ap-tab.menunggu.aktif .n {
            background: #fff;
            color: #b98a2e;
        }

        .ap-filter {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-left: auto;
            font-size: 12px;
            color: #6b7687;
        }

        .ap-filter input {
            height: 32px;
            padding: 0 8px;
            border: 1px solid #d5dee9;
            border-radius: 8px;
            font-size: 12.5px;
        }

        .ap-cari {
            position: relative;
        }

        .ap-cari i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #a3aebe;
            font-size: 12px;
        }

        .ap-cari input {
            width: 240px;
            padding-left: 28px;
        }

        /* Kartu request */
        .ap-card {
            border: 1px solid #dde5ef;
            border-left: 4px solid var(--c-fg);
            border-radius: 12px;
            background: #fff;
            margin-bottom: 12px;
            box-shadow: 0 1px 2px rgba(31, 41, 55, .04);
            animation: ap-in .35s ease both;
        }

        @keyframes ap-in {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
        }

        .ap-card.sembunyi {
            display: none;
        }

        .ap-card.dipilih {
            background: #f6f9fe;
            border-color: #b9cbe3;
            box-shadow: 0 0 0 2px rgba(74, 111, 165, .15);
        }

        .ap-head {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            cursor: pointer;
        }

        .ap-head:hover {
            background: #fafbfd;
            border-radius: 12px;
        }

        .ap-card.buka .ap-head:hover {
            border-radius: 12px 12px 0 0;
        }

        /* Checkbox pilih (aksi massal) */
        .ap-cek {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            margin: 0;
            flex: none;
            cursor: pointer;
        }

        .ap-cek input {
            width: 17px;
            height: 17px;
            cursor: pointer;
            accent-color: var(--ap-accent);
        }

        .ap-utama {
            flex: 1 1 auto;
            min-width: 0;
        }

        .ap-baris1 {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px 10px;
        }

        .ap-head .no {
            font-family: 'Consolas', monospace;
            font-size: 15px;
            font-weight: 800;
            color: var(--ap-accent);
        }

        .ap-head .dept {
            font-size: 13px;
            font-weight: 700;
            color: #374151;
        }

        .ap-head .meta {
            margin-top: 4px;
            display: flex;
            flex-wrap: wrap;
            gap: 4px 14px;
            font-size: 12px;
            color: #6b7687;
        }

        .ap-head .meta i {
            color: #9aa6b8;
            margin-right: 4px;
        }

        /* Catatan di header: 1 baris, lengkapnya di dalam kartu */
        .ap-head .catatan {
            margin-top: 5px;
            max-width: 720px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            font-size: 12px;
            color: #8a6d2a;
        }

        .ap-head .catatan i {
            margin-right: 4px;
        }

        .ap-card.buka .ap-head .catatan {
            display: none;
        }

        /* Label urgensi tanggal butuh */
        .ap-desak {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .ap-desak.lewat {
            background: #f9e8ea;
            color: #c0505f;
        }

        .ap-desak.dekat {
            background: #fdf0e1;
            color: #c2702a;
        }

        .ap-kanan {
            display: flex;
            align-items: center;
            gap: 14px;
            flex: none;
            margin-left: auto;
        }

        .ap-kanan .ap-total {
            margin-top: 0;
            text-align: right;
            min-width: 120px;
        }

        .ap-total .kosong {
            display: block;
            font-size: 12px;
            font-style: italic;
            color: #a3aebe;
        }

        /* Tombol cepat approve / reject di header */
        .ap-cepat {
            display: flex;
            gap: 6px;
        }

        .ap-cepat button {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            transition: transform .12s ease, background .15s ease, color .15s ease;
        }

        .ap-cepat button:hover {
            transform: translateY(-1px);
        }

        .ap-cepat .setuju {
            background: #e6f3eb;
            color: #4a8c68;
        }

        .ap-cepat .setuju:hover {
            background: #4a8c68;
            color: #fff;
        }

        .ap-cepat .tolak {
            background: #fff;
            border-color: #e3a3ab;
            color: #c0505f;
        }

        .ap-cepat .tolak:hover {
            background: #c0505f;
            color: #fff;
        }

        @media (max-width: 991px) {
            .ap-head {
                flex-wrap: wrap;
            }

            .ap-kanan {
                width: 100%;
                justify-content: flex-end;
            }
        }

        .ap-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            background: var(--c-bg);
            color: var(--c-fg);
            font-size: 12px;
            font-weight: 700;
        }

        .ap-total {
            margin-top: 6px;
            font-size: 11px;
            color: #8a96a8;
        }

        .ap-total b {
            display: block;
            font-family: 'Consolas', monospace;
            font-size: 15px;
            color: #1f2937;
        }

        .ap-caret {
            transition: transform .2s ease;
            color: #9aa6b8;
            flex: none;
        }

        .ap-card.buka .ap-caret {
            transform: rotate(90deg);
        }

        .ap-body {
            display: none;
            border-top: 1px dashed #dde5ef;
            padding: 12px 16px 14px;
        }

        .ap-card.buka .ap-body {
            display: block;
        }

        .ap-tabel {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }

        .ap-tabel th {
            padding: 7px 8px;
            background: #f2f5f9;
            color: #6b7687;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
            border-bottom: 1px solid #dde5ef;
        }

        .ap-tabel td {
            padding: 8px;
            border-bottom: 1px solid #edf1f6;
            vertical-align: top;
        }

        .ap-tabel .nm {
            font-weight: 700;
        }

        .ap-tabel .kd {
            font-family: 'Consolas', monospace;
            font-size: 11px;
            color: #8a96a8;
        }

        .ap-tabel .sub {
            margin-top: 2px;
            font-size: 11px;
            color: #6b7687;
        }

        .ap-tabel .sub i {
            color: var(--ap-accent);
        }

        .ap-tabel .r {
            text-align: right;
            font-family: 'Consolas', monospace;
            white-space: nowrap;
        }

        .ap-tabel tfoot td {
            font-weight: 800;
            background: #eef3f9;
            border-bottom: 0;
        }

        .ap-aksi {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
        }

        .ap-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 16px;
            border: 0;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none !important;
            transition: transform .12s ease, box-shadow .15s ease;
        }

        .ap-btn:hover {
            transform: translateY(-1px);
        }

        .ap-btn.setuju {
            background: #4a8c68;
            color: #fff;
            box-shadow: 0 4px 12px rgba(74, 140, 104, .25);
        }

        .ap-btn.tolak {
            background: #fff;
            border: 1px solid #e3a3ab;
            color: #c0505f;
        }

        .ap-btn.tolak:hover {
            background: #c0505f;
            color: #fff;
        }

        .ap-btn.pdf {
            background: #eef1f5;
            color: #4b5563;
            margin-left: auto;
        }

        .ap-hasil {
            margin-top: 10px;
            padding: 8px 12px;
            border-radius: 8px;
            background: var(--c-bg);
            color: var(--c-fg);
            font-size: 12.5px;
        }

        .ap-approver {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .ap-approver .ap-hasil {
            flex: 1 1 280px;
            margin-top: 0;
        }

        .ap-hasil i.q {
            display: block;
            margin-top: 2px;
            color: #4b5563;
        }

        /* Catatan lengkap di dalam kartu */
        .ap-catatan {
            margin-bottom: 10px;
            padding: 6px 10px;
            border-left: 3px solid #d4a73c;
            border-radius: 4px;
            background: #fdf8ec;
            font-size: 12.5px;
            color: #4b5563;
            white-space: pre-line;
        }

        .ap-tanpa-harga {
            padding: 8px;
            background: #f7f9fc;
            font-size: 12px;
            font-style: italic;
            color: #8a96a8;
            text-align: right;
        }

        /* Footer kartu: status approver (kiri) + tombol (kanan) */
        .ap-foot {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin-top: 12px;
        }

        .ap-foot .ap-approver {
            flex: 1 1 320px;
            margin-top: 0;
        }

        .ap-foot .ap-aksi {
            margin-top: 0;
            margin-left: auto;
        }

        .ap-foot .ap-btn.pdf {
            margin-left: 0;
        }

        /* Pilih semua (tab Menunggu Saya) */
        .ap-pilih-semua {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin: 0 0 10px 2px;
            padding: 6px 12px;
            border: 1px dashed #c9d6e6;
            border-radius: 9px;
            background: #fff;
            font-size: 12.5px;
            font-weight: 700;
            color: #4b5563;
            cursor: pointer;
            user-select: none;
        }

        .ap-pilih-semua input {
            width: 16px;
            height: 16px;
            accent-color: var(--ap-accent);
            cursor: pointer;
        }

        /* Bar aksi massal: muncul di bawah saat ada yang dipilih */
        .ap-massal {
            position: fixed;
            left: 50%;
            bottom: 20px;
            z-index: 1040;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            max-width: calc(100% - 32px);
            padding: 10px 12px 10px 18px;
            border-radius: 14px;
            background: #1f2f47;
            color: #fff;
            box-shadow: 0 12px 32px rgba(15, 23, 42, .3);
            transform: translate(-50%, 140%);
            opacity: 0;
            pointer-events: none;
            transition: transform .25s ease, opacity .25s ease;
        }

        .ap-massal.tampil {
            transform: translate(-50%, 0);
            opacity: 1;
            pointer-events: auto;
        }

        .ap-massal .info {
            font-size: 13px;
            line-height: 1.3;
        }

        .ap-massal .info b {
            font-size: 15px;
        }

        .ap-massal .info small {
            display: block;
            color: #b6c4d8;
            font-family: 'Consolas', monospace;
        }

        .ap-massal .ap-btn {
            height: 34px;
        }

        .ap-massal .ap-btn.tolak {
            background: transparent;
            border-color: #e3a3ab;
            color: #f3c3c9;
        }

        .ap-massal .ap-btn.tolak:hover {
            background: #c0505f;
            color: #fff;
        }

        .ap-massal .lepas {
            border: 0;
            background: transparent;
            color: #b6c4d8;
            font-size: 12.5px;
            font-weight: 700;
            padding: 0 6px;
        }

        .ap-massal .lepas:hover {
            color: #fff;
        }

        .ap-kosong {
            padding: 50px 20px;
            border: 1px dashed #cfd9e6;
            border-radius: 14px;
            background: #f9fbfd;
            text-align: center;
            color: #8a96a8;
        }

        .ap-kosong i {
            display: block;
            margin-bottom: 8px;
            font-size: 34px;
            color: #c3cedc;
        }
    </style>
@endsection

@section('content')
    <div class="ap-page">
        <div class="ap-hero">
            <div class="lbl"><i class="fas fa-user-check"></i> General Request</div>
            <h4>Approval Request</h4>
            <p>Request yang memilih Anda sebagai approver. Periksa item lalu setujui atau tolak.</p>
        </div>

        <div class="ap-bar">
            <button type="button" class="ap-tab menunggu aktif" data-tab="menunggu"><i class="fas fa-hourglass-half"></i>
                Menunggu Saya <span class="n">{{ $jumlah['menunggu'] ?? 0 }}</span></button>
            <button type="button" class="ap-tab" data-tab="setuju"><i class="fas fa-check-circle"></i> Disetujui <span
                    class="n">{{ $jumlah['setuju'] ?? 0 }}</span></button>
            <button type="button" class="ap-tab" data-tab="ditolak"><i class="fas fa-times-circle"></i> Ditolak <span
                    class="n">{{ $jumlah['ditolak'] ?? 0 }}</span></button>
            <button type="button" class="ap-tab" data-tab="batal"><i class="fas fa-ban"></i> Dibatalkan Pemohon <span
                    class="n">{{ $jumlah['batal'] ?? 0 }}</span></button>

            <div class="ap-filter">
                <div class="ap-cari"><i class="fas fa-search"></i><input type="text" id="ap_cari"
                        placeholder="Cari no / departemen / pemohon / barang"></div>
                <span title="Rentang tanggal untuk riwayat (yang menunggu selalu tampil)">Riwayat</span>
                <input type="date" id="ap_awal" value="{{ $tgl_awal }}">
                <span>s/d</span>
                <input type="date" id="ap_akhir" value="{{ $tgl_akhir }}">
            </div>
        </div>

        <label class="ap-pilih-semua" id="ap_pilih_semua_wrap" hidden>
            <input type="checkbox" id="ap_pilih_semua"> Pilih semua yang tampil
        </label>

        <div id="ap_list">
            @foreach ($requests as $req)
                @php
                    [$bg, $fg, $ic] = $warna[$req['status']] ?? ['#eef1f5', '#6b7687', 'fa-question'];
                    $items = $gabung($req['items']);
                    $totalUang = [];
                    foreach ($items as $it) {
                        if ($it['harga'] !== null) {
                            $totalUang[$it['mata_uang']] = ($totalUang[$it['mata_uang']] ?? 0) + $it['qty'] * $it['harga'];
                        }
                    }
                    // Kolom Harga & Subtotal hanya tampil kalau ada item yang berharga
                    $adaHarga = count($totalUang) > 0;
                    $cari = strtolower(implode(' ', [$req['no_request'], $req['departemen'], $req['pemohon'], $req['pemohon_username'], $req['catatan'], ...array_column($items, 'nama')]));
                    // Tombol Approve / Reject (& checkbox massal) hanya kalau request masih menunggu & saya belum memutuskan
                    $menunggu = $req['status'] === 'Menunggu Approval' && $hasilSaya($req) === null;
                    $urgensi = $req['status'] === 'Menunggu Approval' ? $desak($req['tgl_butuh']) : null;
                    $jmlSetuju = collect($req['approvers'])->where('hasil', 'Disetujui')->count();
                @endphp
                {{-- Semua kartu tertutup dulu; klik header untuk membuka --}}
                <div class="ap-card" data-tab="{{ $tabOf($req) }}" data-cari="{{ $cari }}" data-id="{{ $req['id'] }}"
                    data-no="{{ $req['no_request'] }}" data-total='@json((object) $totalUang)'
                    style="--c-bg: {{ $bg }}; --c-fg: {{ $fg }}">
                    <div class="ap-head" onclick="$(this).closest('.ap-card').toggleClass('buka')">
                        @if ($menunggu)
                            <label class="ap-cek" onclick="event.stopPropagation()" title="Pilih untuk approve sekaligus">
                                <input type="checkbox" class="ap-pilih">
                            </label>
                        @endif
                        <i class="fas fa-chevron-right ap-caret"></i>
                        <div class="ap-utama">
                            <div class="ap-baris1">
                                <span class="no">{{ $req['no_request'] }}</span>
                                <span class="dept">{{ $req['departemen'] }}</span>
                                @if ($urgensi)
                                    <span class="ap-desak {{ $urgensi[0] }}"><i class="fas {{ $urgensi[1] }}"></i>
                                        {{ $urgensi[2] }}</span>
                                @endif
                            </div>
                            <div class="meta">
                                <span title="Pemohon"><i class="fas fa-user"></i>{{ $req['pemohon'] }}</span>
                                <span title="Tanggal request"><i class="far fa-calendar"></i>{{ date('d-m-Y', strtotime($req['tgl_request'])) }}</span>
                                @if ($req['tgl_butuh'])
                                    <span title="Tanggal dibutuhkan paling awal"><i class="far fa-calendar-check"></i>Butuh
                                        {{ date('d-m-Y', strtotime($req['tgl_butuh'])) }}</span>
                                @endif
                                <span><i class="fas fa-boxes"></i>{{ count($items) }} item</span>
                                @if (count($req['approvers']))
                                    <span title="Approver yang sudah setuju"><i class="fas fa-user-check"></i>Approval
                                        {{ $jmlSetuju }}/{{ count($req['approvers']) }}</span>
                                @endif
                            </div>
                            @if ($req['catatan'])
                                <div class="catatan" title="{{ $req['catatan'] }}"><i class="fas fa-sticky-note"></i>{{ $req['catatan'] }}</div>
                            @endif
                        </div>
                        <div class="ap-kanan">
                            <div class="ap-total">Total perkiraan
                                @if ($adaHarga)
                                    <b>
                                        @foreach ($totalUang as $u => $n)
                                            {{ $u }} {{ $angka($n) }}@if (!$loop->last)<br>@endif
                                        @endforeach
                                    </b>
                                @else
                                    <span class="kosong">Harga belum diisi</span>
                                @endif
                            </div>
                            <span class="ap-chip"><i class="fas {{ $ic }}"></i>
                                {{ $menunggu ? 'Menunggu Anda' : ($req['status'] === 'Menunggu Approval' ? 'Menunggu approver lain' : $req['status']) }}</span>
                            @if ($menunggu)
                                <div class="ap-cepat" onclick="event.stopPropagation()">
                                    <button type="button" class="setuju" title="Approve" onclick="putuskan('setuju', this)"><i
                                            class="fas fa-check"></i></button>
                                    <button type="button" class="tolak" title="Reject" onclick="putuskan('tolak', this)"><i
                                            class="fas fa-times"></i></button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="ap-body">
                        @if ($req['catatan'])
                            <div class="ap-catatan"><i class="fas fa-sticky-note"></i> {{ $req['catatan'] }}</div>
                        @endif
                        <table class="ap-tabel">
                            <thead>
                                <tr>
                                    <th style="width: 30px">#</th>
                                    <th class="text-left">Barang</th>
                                    <th class="text-left" style="width: 22%">Sub Dept</th>
                                    <th style="width: 110px">Tgl Butuh</th>
                                    <th class="text-right" style="width: 100px">Qty</th>
                                    @if ($adaHarga)
                                        <th class="text-right" style="width: 130px">Harga</th>
                                        <th class="text-right" style="width: 140px">Subtotal</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $i => $it)
                                    <tr>
                                        <td class="text-muted">{{ $i + 1 }}</td>
                                        <td>
                                            <div class="nm">{{ $it['nama'] }}</div>
                                            <div class="kd">{{ $it['kode'] }}</div>
                                            @if ($it['ket'])
                                                <div class="sub"><i class="fas fa-pen"></i> {{ $it['ket'] }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @foreach ($it['rincian'] as $r)
                                                <div class="sub" style="margin-top: 0">{{ $r['nama'] }}@if (count($it['rincian']) > 1)
                                                        <b>&middot; {{ $angka($r['qty']) }}</b>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </td>
                                        <td>{{ $it['tgl_butuh'] ? date('d-m-Y', strtotime($it['tgl_butuh'])) : '-' }}</td>
                                        <td class="r"><b>{{ $angka($it['qty']) }}</b> {{ $it['satuan'] }}</td>
                                        @if ($adaHarga)
                                            <td class="r">{{ $it['harga'] !== null ? $it['mata_uang'] . ' ' . $angka($it['harga']) : '-' }}</td>
                                            <td class="r">
                                                {{ $it['harga'] !== null ? $it['mata_uang'] . ' ' . $angka($it['qty'] * $it['harga']) : '-' }}
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    @if ($adaHarga)
                                        <td colspan="6" class="text-right">Total Perkiraan</td>
                                        <td class="r">
                                            @foreach ($totalUang as $u => $n)
                                                {{ $u }} {{ $angka($n) }}@if (!$loop->last)<br>@endif
                                            @endforeach
                                        </td>
                                    @else
                                        <td colspan="5" class="ap-tanpa-harga"><i class="fas fa-info-circle"></i> Harga
                                            belum diisi pemohon, total perkiraan belum bisa dihitung</td>
                                    @endif
                                </tr>
                            </tfoot>
                        </table>

                        <div class="ap-foot">
                            {{-- Status tiap approver (approve bersamaan; kalau ada 2, keduanya wajib setuju) --}}
                            <div class="ap-approver">
                                @foreach ($req['approvers'] as $ap)
                                    @php
                                        [$abg, $afg, $aic] = $ap['hasil'] === 'Disetujui'
                                            ? ['#e6f3eb', '#4a8c68', 'fa-check-circle']
                                            : ($req['status'] === 'Menunggu Approval' ? ['#fbf3df', '#b98a2e', 'fa-hourglass-half'] : ['#eef1f5', '#8a93a1', 'fa-minus-circle']);
                                    @endphp
                                    <div class="ap-hasil" style="--c-bg: {{ $abg }}; --c-fg: {{ $afg }}">
                                        <i class="fas {{ $aic }}"></i>
                                        <b>Approver {{ $ap['ke'] }}</b> &middot; {{ $ap['nama'] }}{{ $ap['saya'] ? ' (Anda)' : '' }}
                                        &middot; {{ $ap['hasil'] ?: ($req['status'] === 'Menunggu Approval' ? 'Menunggu' : '-') }}
                                        {{ $ap['waktu'] ? '· ' . $ap['waktu'] : '' }}
                                    </div>
                                @endforeach
                            </div>

                            <div class="ap-aksi">
                                @if ($req['status'] !== 'Dibatalkan')
                                    <a class="ap-btn pdf" href="{{ route('general-request-approval-pdf', $req['id']) }}"
                                        target="_blank"><i class="fas fa-file-pdf"></i> Lihat PDF</a>
                                @endif
                                @if ($menunggu)
                                    <button type="button" class="ap-btn tolak" onclick="putuskan('tolak', this)"><i
                                            class="fas fa-times"></i> Reject</button>
                                    <button type="button" class="ap-btn setuju" onclick="putuskan('setuju', this)"><i
                                            class="fas fa-check"></i> Approve</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="ap-kosong" id="ap_kosong" hidden><i class="fas fa-inbox"></i><span></span></div>
        </div>

        {{-- Bar aksi massal: muncul saat ada request yang dipilih --}}
        <div class="ap-massal" id="ap_massal">
            <div class="info"><b id="ap_massal_n">0</b> request dipilih
                <small id="ap_massal_total"></small>
            </div>
            <button type="button" class="lepas" onclick="lepasPilihan()">Batal pilih</button>
            {{-- Massal hanya Approve; Reject tetap satu per satu --}}
            <button type="button" class="ap-btn setuju" onclick="putuskanMassal('setuju')"><i class="fas fa-check"></i>
                Approve</button>
        </div>
    </div>
@endsection

@section('custom-script')
    <script>
        const urlPutus = {
            setuju: "{{ route('general-request-approval-setuju') }}",
            tolak: "{{ route('general-request-approval-tolak') }}",
        };
        const teksKosong = {
            menunggu: 'Tidak ada request yang menunggu approval Anda',
            setuju: 'Belum ada request yang Anda setujui pada rentang tanggal ini',
            ditolak: 'Belum ada request yang Anda tolak pada rentang tanggal ini',
            batal: 'Tidak ada request yang dibatalkan pemohon',
        };
        let tabAktif = 'menunggu';
        const esc = s => $('<div>').text(s ?? '').html();

        function saring() {
            const q = $('#ap_cari').val().trim().toLowerCase();
            let n = 0;
            $('#ap_list .ap-card').each(function() {
                const cocok = $(this).data('tab') === tabAktif && (!q || String($(this).data('cari')).includes(q));
                $(this).toggleClass('sembunyi', !cocok);
                if (cocok) n++;
            });
            $('#ap_kosong').prop('hidden', n > 0).find('span').text(q ? 'Tidak ada yang cocok' : teksKosong[tabAktif]);
            // Yang tersembunyi tidak ikut terpilih
            $('#ap_list .ap-card.sembunyi .ap-pilih:checked').prop('checked', false);
            perbaruiMassal();
        }

        /* ---------- Aksi massal ---------- */
        const kartuDipilih = () => $('#ap_list .ap-card').filter((_, el) => $(el).find('.ap-pilih').is(':checked'));

        function formatAngka(n) {
            return Number(n).toLocaleString('id-ID', { maximumFractionDigits: 2 });
        }

        function perbaruiMassal() {
            const dipilih = kartuDipilih();
            $('#ap_list .ap-card').each(function() {
                $(this).toggleClass('dipilih', $(this).find('.ap-pilih').is(':checked'));
            });

            // Total perkiraan gabungan per mata uang
            const total = {};
            let tanpaHarga = 0;
            dipilih.each(function() {
                const t = $(this).data('total') || {};
                if (!Object.keys(t).length) tanpaHarga++;
                Object.entries(t).forEach(([u, n]) => total[u] = (total[u] || 0) + Number(n));
            });
            const teksTotal = Object.entries(total).map(([u, n]) => `${u} ${formatAngka(n)}`).join(' · ');
            $('#ap_massal_n').text(dipilih.length);
            $('#ap_massal_total').text((teksTotal ? 'Total ' + teksTotal : '') +
                (tanpaHarga ? `${teksTotal ? ' · ' : ''}${tanpaHarga} tanpa harga` : ''));
            $('#ap_massal').toggleClass('tampil', dipilih.length > 0);

            // Pilih semua: hanya di tab Menunggu Saya & kalau ada yang bisa dipilih
            const bisa = $('#ap_list .ap-card:not(.sembunyi) .ap-pilih');
            $('#ap_pilih_semua_wrap').prop('hidden', tabAktif !== 'menunggu' || bisa.length < 2);
            $('#ap_pilih_semua').prop('checked', bisa.length > 0 && bisa.filter(':checked').length === bisa.length);
        }

        function lepasPilihan() {
            $('#ap_list .ap-pilih').prop('checked', false);
            perbaruiMassal();
        }

        $(function() {
            $('.ap-tab').on('click', function() {
                $('.ap-tab').removeClass('aktif');
                $(this).addClass('aktif');
                tabAktif = $(this).data('tab');
                lepasPilihan();
                saring();
            });
            $('#ap_cari').on('input', saring);
            $('#ap_awal, #ap_akhir').on('change', function() {
                const awal = $('#ap_awal').val(), akhir = $('#ap_akhir').val();
                if (awal && akhir) window.location = `{{ route('general-request-approval') }}?tgl_awal=${awal}&tgl_akhir=${akhir}`;
            });
            $('#ap_list').on('change', '.ap-pilih', perbaruiMassal);
            $('#ap_pilih_semua').on('change', function() {
                $('#ap_list .ap-card:not(.sembunyi) .ap-pilih').prop('checked', this.checked);
                perbaruiMassal();
            });
            saring();
        });

        function kirimPutusan(aksi, data) {
            return $.ajax({
                url: urlPutus[aksi],
                type: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json' },
            }).catch(xhr => Swal.showValidationMessage(xhr.responseJSON?.message || 'Terjadi kesalahan, silakan coba lagi.'));
        }

        // aksi: 'setuju' | 'tolak' (cukup konfirmasi, tanpa catatan); el = tombol di dalam kartu
        function putuskan(aksi, el) {
            const kartu = $(el).closest('.ap-card');
            const id = kartu.data('id'), no = String(kartu.data('no'));
            const setuju = aksi === 'setuju';
            Swal.fire({
                icon: setuju ? 'question' : 'warning',
                title: setuju ? 'Approve request?' : 'Reject request?',
                html: `<b>${esc(no)}</b>`,
                showCancelButton: true,
                confirmButtonText: setuju ? '<i class="fas fa-check"></i> Approve' : '<i class="fas fa-times"></i> Reject',
                cancelButtonText: 'Batal',
                confirmButtonColor: setuju ? '#4a8c68' : '#c0505f',
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: () => kirimPutusan(aksi, { id }),
            }).then(r => {
                if (!r.isConfirmed || !r.value) return;
                Swal.fire({ icon: 'success', title: r.value.message, html: `<b>${esc(no)}</b>`, timer: 1500, showConfirmButton: false })
                    .then(() => window.location.reload());
            });
        }

        function putuskanMassal(aksi) {
            const dipilih = kartuDipilih();
            if (!dipilih.length) return;
            if (aksi !== 'setuju') return; // massal hanya Approve
            const setuju = true;
            const ids = dipilih.map((_, el) => $(el).data('id')).get();
            const nos = dipilih.map((_, el) => String($(el).data('no'))).get();
            const tampil = nos.slice(0, 8).map(n => `<li>${esc(n)}</li>`).join('') +
                (nos.length > 8 ? `<li class="text-muted">+ ${nos.length - 8} lainnya</li>` : '');

            Swal.fire({
                icon: setuju ? 'question' : 'warning',
                title: `${setuju ? 'Approve' : 'Reject'} ${ids.length} request?`,
                html: `<ul style="text-align:left; display:inline-block; margin:0; font-family:Consolas,monospace">${tampil}</ul>` +
                    ($('#ap_massal_total').text() ? `<div class="mt-2 text-muted" style="font-size:13px">${esc($('#ap_massal_total').text())}</div>` : ''),
                showCancelButton: true,
                confirmButtonText: setuju ? `<i class="fas fa-check"></i> Approve ${ids.length}` : `<i class="fas fa-times"></i> Reject ${ids.length}`,
                cancelButtonText: 'Batal',
                confirmButtonColor: setuju ? '#4a8c68' : '#c0505f',
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: () => kirimPutusan(aksi, { ids }),
            }).then(r => {
                if (!r.isConfirmed || !r.value) return;
                const gagal = r.value.gagal || [];
                if (!gagal.length) {
                    Swal.fire({ icon: 'success', title: r.value.message, timer: 1600, showConfirmButton: false })
                        .then(() => window.location.reload());
                    return;
                }
                // Sebagian gagal: tampilkan alasannya
                Swal.fire({
                    icon: (r.value.berhasil || []).length ? 'warning' : 'error',
                    title: r.value.message,
                    html: `<ul style="text-align:left; margin:0; font-size:13px">${gagal.map(g => `<li><b>${esc(g.no)}</b>: ${esc(g.pesan)}</li>`).join('')}</ul>`,
                    confirmButtonText: 'OK',
                }).then(() => window.location.reload());
            });
        }
    </script>
@endsection
