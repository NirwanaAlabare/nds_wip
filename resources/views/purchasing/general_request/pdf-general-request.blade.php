@php
    // Angka: pemisah ribuan titik, desimal koma; ",00" dibuang
    $angka = function ($n) {
        $t = number_format((float) $n, 2, ',', '.');
        return str_ends_with($t, ',00') ? substr($t, 0, -3) : $t;
    };
    $tgl = fn($d) => $d ? date('d M Y', strtotime($d)) : '-';
    // [latar, teks, titik]
    $warnaStatus = [
        'Draft' => ['#f1f2f4', '#5b6472', '#9aa3b0'],
        'Menunggu Approval' => ['#fdf3d8', '#9a6b00', '#e0a100'],
        'Proses PO' => ['#e7f0fb', '#2f5d99', '#4a7fc4'],
        'Diterima Sebagian' => ['#fcefe3', '#a8622a', '#d4873f'],
        'Selesai' => ['#e5f4ec', '#2f7a52', '#3f9a68'],
        'Ditolak' => ['#fbe7ea', '#b3263b', '#d23c52'],
    ][$status] ?? ['#f1f2f4', '#5b6472', '#9aa3b0'];
    // Belum final: diberi tanda air
    $tandaAir = ['Draft' => 'DRAFT', 'Menunggu Approval' => 'MENUNGGU APPROVAL', 'Ditolak' => 'DITOLAK'][$status] ?? null;
    $tglButuhAwal = collect($items)->pluck('tgl_butuh')->filter()->min();
    $adaTanpaHarga = collect($items)->contains(fn($it) => $it->harga === null);
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>{{ $g->request_no }}</title>
    <style>
        @page {
            margin: 26px 30px 40px 30px;
        }

        * {
            font-family: 'DejaVu Sans', sans-serif;
        }

        body {
            margin: 0;
            font-size: 9px;
            color: #26262b;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .r {
            text-align: right;
        }

        .c {
            text-align: center;
        }

        .mono {
            font-family: 'DejaVu Sans Mono', monospace;
        }

        .redup {
            color: #b5b9c1;
        }

        /* Kop */
        .kop td {
            vertical-align: top;
        }

        .kop .logo {
            width: 76px;
        }

        .kop .logo img {
            width: 66px;
        }

        .kop .pt {
            font-size: 13.5px;
            font-weight: bold;
            color: #26262b;
        }

        .kop .alamat {
            margin-top: 4px;
            font-size: 7.8px;
            color: #6b7079;
            line-height: 1.55;
        }

        .kop .judul {
            width: 200px;
            text-align: right;
        }

        .judul .t1 {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1.2px;
            color: #6b7079;
        }

        .judul .no {
            margin-top: 3px;
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 12px;
            font-weight: bold;
            color: #26262b;
        }

        .garis {
            margin: 10px 0 8px;
            border-top: 1px solid #e2e4e8;
        }

        /* Status & kota */
        .status {
            display: inline-block;
            padding: 4px 11px;
            border-radius: 12px;
            font-size: 8.5px;
            font-weight: bold;
        }

        .status .dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 3px;
            margin-right: 4px;
        }

        .kota {
            font-size: 9px;
            color: #6b7079;
        }

        /* Info */
        .info {
            margin-top: 7px;
            border: 1px solid #e2e4e8;
            border-radius: 8px;
        }

        .info td {
            width: 25%;
            padding: 6px 10px;
            border-right: 1px solid #e2e4e8;
            vertical-align: top;
        }

        .info td.akhir {
            border-right: 0;
            background: #fbe9ec;
        }

        .info .k {
            font-size: 6.8px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #6b7079;
            text-transform: uppercase;
        }

        .info .akhir .k {
            color: #c8102e;
        }

        .info .v {
            margin-top: 3px;
            font-size: 10.5px;
            font-weight: bold;
            color: #26262b;
        }

        .info .v small {
            font-weight: normal;
            font-size: 8px;
            color: #6b7079;
        }

        /* Tabel item */
        .item {
            margin-top: 10px;
        }

        .item th {
            background: #26262b;
            color: #fff;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: .6px;
            text-transform: uppercase;
            padding: 6px 5px;
            text-align: left;
        }

        .item th.r {
            text-align: right;
        }

        .item th.c {
            text-align: center;
        }

        .item td {
            padding: 5px 5px;
            border-bottom: 1px solid #eceef1;
            vertical-align: top;
            font-size: 8.8px;
        }

        .item tr.genap td {
            background: #fafafb;
        }

        .item .kode {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 7.8px;
            color: #4b5058;
        }

        .item .nm {
            font-weight: bold;
        }

        .item .ket {
            margin-top: 2px;
            font-size: 7.5px;
            font-style: italic;
            color: #6b7079;
        }

        .item tfoot td {
            padding: 6px 5px;
            border-bottom: 0;
            background: #f2f3f5;
            font-weight: bold;
            font-size: 9.5px;
        }

        /* Ringkasan */
        .ringkas {
            margin-top: 10px;
        }

        .ringkas td {
            vertical-align: top;
        }

        .catatan {
            padding: 2px 0 2px 12px;
            border-left: 2px solid #c8102e;
            font-size: 8.3px;
            color: #4b5058;
            line-height: 1.75;
        }

        .total {
            padding: 7px 12px;
            border: 1px solid #e2e4e8;
            border-left: 4px solid #26262b;
            border-radius: 0 8px 8px 0;
            background: #f4f4f6;
        }

        .total .k {
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 1.4px;
            color: #4b5058;
        }

        .total .v {
            margin-top: 4px;
            font-size: 14px;
            font-weight: bold;
            color: #26262b;
            line-height: 1.15;
        }

        .total .v small {
            font-size: 9px;
            font-weight: normal;
            color: #6b7079;
            margin-right: 2px;
        }

        /* Notes header */
        .notes {
            margin-top: 10px;
            padding: 6px 10px;
            border: 1px solid #e2e4e8;
            border-left: 4px solid #c8102e;
            border-radius: 6px;
            background: #fff;
        }

        .notes .k {
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 1.4px;
            color: #c8102e;
        }

        .notes .v {
            margin-top: 3px;
            font-size: 9px;
            line-height: 1.55;
            color: #26262b;
        }

        /* Persetujuan */
        .cap {
            margin-top: 14px;
            margin-bottom: 5px;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #4b5058;
        }

        .ttd td {
            width: 25%;
            padding: 0 5px;
            vertical-align: top;
        }

        .ttd td.pertama {
            padding-left: 0;
        }

        .ttd td.akhir {
            padding-right: 0;
        }

        .ttd .kotak {
            border: 1px solid #e2e4e8;
            border-radius: 8px;
            padding: 6px 8px 6px;
            text-align: center;
        }

        .ttd .peran {
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #4b5058;
        }

        .ttd .ruang {
            height: 38px;
        }

        .ttd .nama {
            border-top: 1px solid #9aa0a8;
            padding-top: 5px;
            font-weight: bold;
            font-size: 9px;
            min-height: 11px;
        }

        .ttd .tg {
            font-size: 7.3px;
            color: #6b7079;
            margin-top: 1px;
            min-height: 9px;
        }

        /* Tanda air & kaki */
        .tanda-air {
            position: fixed;
            top: 360px;
            left: -40px;
            right: -40px;
            text-align: center;
            white-space: nowrap;
            font-size: 52px;
            font-weight: bold;
            letter-spacing: 5px;
            color: #c8102e;
            opacity: .06;
            transform: rotate(-30deg);
        }

        .kaki {
            position: fixed;
            bottom: -26px;
            left: 0;
            right: 0;
            padding-top: 6px;
            border-top: 1px solid #e2e4e8;
            font-size: 7px;
            color: #8b9099;
        }

        /* Ruang untuk "Hal. x / y" yang ditulis lewat canvas (controller) */
        .kaki td.r {
            padding-right: 52px;
        }
    </style>
</head>

<body>
    @if ($tandaAir)
        <div class="tanda-air">{{ $tandaAir }}</div>
    @endif

    <div class="kaki">
        <table>
            <tr>
                <td>{{ $g->request_no }} &middot; Purchase Request General</td>
                <td class="r">Dicetak {{ date('d M Y H:i') }}{{ Auth::user() ? ' oleh ' . Auth::user()->username : '' }}
                    &middot;</td>
            </tr>
        </table>
    </div>

    {{-- Kop + judul --}}
    <table class="kop">
        <tr>
            @if ($kop['logo'])
                <td class="logo"><img src="{{ $kop['logo'] }}" alt="Logo"></td>
            @endif
            <td>
                <div class="pt">{{ $kop['nama'] }}</div>
                @if ($kop['alamat'] || $kop['telp'])
                    <div class="alamat">
                        @foreach ($kop['alamat'] as $baris)
                            {{ $baris }}<br>
                        @endforeach
                        {{ $kop['telp'] }}
                    </div>
                @endif
            </td>
            <td class="judul">
                <div class="t1">PURCHASE REQUEST GENERAL</div>
                <div class="no">{{ $g->request_no }}</div>
            </td>
        </tr>
    </table>

    <div class="garis"></div>

    {{-- Status & kota --}}
    <table>
        <tr>
            <td>
                <span class="status" style="background: {{ $warnaStatus[0] }}; color: {{ $warnaStatus[1] }}"><span
                        class="dot" style="background: {{ $warnaStatus[2] }}"></span>{{ $status }}</span>
            </td>
            <td class="r kota">Bandung, {{ $tgl($g->request_date) }}</td>
        </tr>
    </table>

    {{-- Info --}}
    <table class="info">
        <tr>
            <td>
                <div class="k">Tgl Request</div>
                <div class="v">{{ $tgl($g->request_date) }}</div>
            </td>
            <td>
                <div class="k">Departemen</div>
                <div class="v">{{ $departemen }}</div>
            </td>
            <td>
                <div class="k">Pemohon</div>
                <div class="v">{{ $namaPemohon }} <small>({{ $pemohon }})</small></div>
            </td>
            <td class="akhir">
                <div class="k">Dibutuhkan Mulai</div>
                <div class="v">{{ $tgl($tglButuhAwal) }}</div>
            </td>
        </tr>
    </table>

    {{-- Item --}}
    <table class="item">
        <thead>
            <tr>
                <th width="4%" class="c">No</th>
                <th width="11%">Item Code</th>
                <th>Description</th>
                <th width="7%" class="c">Color</th>
                <th width="6%" class="c">Size</th>
                <th width="11%" class="c">Tgl Butuh</th>
                <th width="6%" class="r">Qty</th>
                <th width="6%">Unit</th>
                <th width="5%">Curr</th>
                <th width="10%" class="r">Price</th>
                <th width="11%" class="r">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $i => $it)
                <tr class="{{ $i % 2 ? 'genap' : '' }}">
                    <td class="c">{{ $i + 1 }}</td>
                    <td class="kode">{{ $it->kode }}</td>
                    <td>
                        <div class="nm">{{ $it->nama }}</div>
                        @if ($it->ket)
                            <div class="ket">{{ implode('; ', $it->ket) }}</div>
                        @endif
                    </td>
                    <td class="c {{ $it->warna === '-' ? 'redup' : '' }}">{{ $it->warna === '-' ? '–' : $it->warna }}</td>
                    <td class="c {{ $it->ukuran === '-' ? 'redup' : '' }}">{{ $it->ukuran === '-' ? '–' : $it->ukuran }}</td>
                    <td class="c mono">{{ $it->tgl_butuh ? date('d-m-Y', strtotime($it->tgl_butuh)) : '-' }}</td>
                    <td class="r"><b>{{ $angka($it->qty) }}</b></td>
                    <td>{{ $it->satuan }}</td>
                    <td>{{ $it->mata_uang }}</td>
                    <td class="r {{ $it->harga === null ? 'redup' : '' }}">
                        {{ $it->harga === null ? '–' : $angka($it->harga) }}</td>
                    <td class="r {{ $it->harga === null ? 'redup' : '' }}">
                        {{ $it->harga === null ? '–' : $angka($it->qty * $it->harga) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="9" class="r">Total</td>
                <td colspan="2" class="r">
                    @forelse ($totalUang as $uang => $n)
                        {{ count($totalUang) > 1 ? $uang . ' ' : '' }}{{ $angka($n) }}@if (!$loop->last)<br>@endif
                    @empty
                        –
                    @endforelse
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- Ringkasan --}}
    <table class="ringkas">
        <tr>
            <td style="padding-right: 24px">
                <div class="catatan">
                    <b>{{ count($items) }}</b> item barang &middot;
                    {{ collect($totalQty)->map(fn($n, $s) => $angka($n) . ' ' . $s)->implode(', ') }}<br>
                    @if ($adaTanpaHarga)
                        Sebagian item belum diisi harga perkiraan.<br>
                    @endif
                    Harga adalah perkiraan pemohon, bukan harga final PO.
                </div>
            </td>
            <td style="width: 255px">
                <div class="total">
                    <div class="k">TOTAL PERKIRAAN</div>
                    @forelse ($totalUang as $uang => $n)
                        <div class="v"><small>{{ $uang }}</small> {{ $angka($n) }}</div>
                    @empty
                        <div class="v">–</div>
                    @endforelse
                </div>
            </td>
        </tr>
    </table>

    {{-- Catatan keseluruhan request (header) --}}
    @if ($g->notes)
        <div class="notes">
            <div class="k">NOTES</div>
            <div class="v">{!! nl2br(e($g->notes)) !!}</div>
        </div>
    @endif

    {{-- Persetujuan: format lama (Created By, Waiting, Waiting 2, Received By) --}}
    <div class="cap">PERSETUJUAN</div>
    <table class="ttd">
        <tr>
            <td class="pertama">
                <div class="kotak">
                    <div class="peran">CREATED BY</div>
                    <div class="ruang"></div>
                    <div class="nama">{{ $namaPemohon }}</div>
                    <div class="tg">{{ $tgl($g->created_at) }}</div>
                </div>
            </td>
            {{-- Waiting = approver 1, Waiting 2 = approver 2 (kosong kalau tidak dipilih) --}}
            @foreach ($ttdApprover as $i => $ap)
                <td>
                    <div class="kotak">
                        <div class="peran">{{ $i ? 'WAITING 2' : 'WAITING' }}</div>
                        <div class="ruang"></div>
                        <div class="nama">{{ $ap['nama'] ?? '' }}&nbsp;</div>
                        <div class="tg">
                            @if ($ap['hasil'] && $ap['waktu'])
                                {{ $ap['hasil'] }} {{ $tgl($ap['waktu']) }}
                            @else
                                &nbsp;
                            @endif
                        </div>
                    </div>
                </td>
            @endforeach
            <td class="akhir">
                <div class="kotak">
                    <div class="peran">RECEIVED BY</div>
                    <div class="ruang"></div>
                    <div class="nama">&nbsp;</div>
                    <div class="tg">&nbsp;</div>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
