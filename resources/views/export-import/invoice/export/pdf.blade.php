{{-- ===========================================================================
     PDF Invoice Export - gaya CARING. Dua versi dari data yang sama:
       CM  ($versi = 'cm')  untuk penagihan tim AR
       FOB ($versi = 'fob') untuk perizinan barang keluar BC
     Yang berbeda cuma harga di Invoice Summary & rekapnya. Judulnya sama -
     versinya sengaja tidak ditulis di dokumen.

     Isinya sama dengan versi Classic (pdf-lama.blade.php): kop, pihak-pihak,
     Shipment Details per baris, Invoice Summary, Invoice Notes, Manufacturer
     Information, tanda tangan. Tampilannya disamakan dengan PDF Debit Note di
     AR (reportdebitnote_v2): kop dengan blok nilai C-A-R-I-N-G, judul merah,
     judul blok bergaris merah, tabel berkepala abu, Net Invoice Total berlatar
     merah muda, pita merah + "Caring -" di kaki halaman.

     Data disiapkan InvoiceEximController::dataCetakExport(); mPDF-nya dari
     mpdfCaring(). Nomor FG/OUT sengaja tidak dicetak.
     =========================================================================== --}}
@php
    $uang = function ($n) {
        $n = (float) $n;
        return ($n < 0 ? '- ' : '') . number_format(abs($n), 2, '.', ',');
    };
    // Qty & carton: tanpa desimal kalau bulat.
    $bulat = function ($n) {
        $n = (float) $n;
        return floor($n) == $n
            ? number_format($n, 0, '.', ',')
            : rtrim(rtrim(number_format($n, 2, '.', ','), '0'), '.');
    };
    $berat = function ($n) { return number_format((float) $n, 3, '.', ','); };
    // Unit cost: minimal 2, maksimal 4 desimal (0.76 / 1,142.8571).
    $unit = function ($n) {
        $t = rtrim(number_format((float) $n, 4, '.', ','), '0');
        $des = strlen(substr(strrchr($t, '.'), 1));
        return $t . str_repeat('0', max(0, 2 - $des));
    };
    $adaNilai = function ($n) { return abs((float) $n) >= 0.005; };

    // Pihak-pihak: baris pertama = nama (ditebalkan) kalau namanya memang diisi.
    $pihak = array(
        array('SHIP FROM', $baris['shipper'], trim((string) $inv['shipper_nama']) !== ''),
        array('SELLER', $baris['seller'], trim((string) $inv['seller_nama']) !== ''),
        array('PURCHASER', $baris['purchaser'], trim((string) $inv['purchaser_nama']) !== ''),
        array('SHIP TO', $baris['receiver'], trim((string) $inv['receiver_nama']) !== ''),
    );

    // Alamat Manufacturer Information cukup 2 baris: baris pertama tetap, sisanya
    // disambung jadi satu (mis. "KEC. SOLOKAN JERUK, BANDUNG, JAWA BARAT").
    $alamatPabrik = array_values(array_filter(array_map('trim',
        preg_split('/\r\n|\r|\n/', (string) $inv['manufacturer_alamat'])), 'strlen'));
    if (count($alamatPabrik) > 2) {
        $alamatPabrik = array($alamatPabrik[0], implode(' ', array_slice($alamatPabrik, 1)));
    }

    // Rekap Invoice Summary - sama dengan versi Classic.
    $rekapBaris = [
        ['Total Dozens', null, null, false],
        ['Sub Total', $subQty, $subTotal, true],
        ['Hard Tag Cost', null, null, false],
        ['Gross Invoice Sub total', null, $subTotal, true],
        ['Discount', null, $adaNilai($rekap['discount']) ? -$rekap['discount'] : null, false],
    ];
    foreach (['dp' => 'Down Payment', 'dp_cbd' => 'DP/CBD from Invoice', 'retur' => 'Return'] as $k => $label) {
        if ($adaNilai($rekap[$k])) {
            $rekapBaris[] = [$label, null, -$rekap[$k], false];
        }
    }
    if ($adaNilai($rekap['vat'])) {
        $rekapBaris[] = ['VAT (' . (0 + $vatPersen) . '%)', null, $rekap['vat'], false];
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $noCetak }}</title>
    <style>
        @page {
            margin: 7mm 8mm 26mm 8mm;
            odd-footer-name: html_kaki;
            even-footer-name: html_kaki;
        }

        body { font-family: sans-serif; font-size: 11px; color: #222222; }
        table { border-collapse: collapse; width: 100%; }
        td, th { vertical-align: top; }

        /* ---- Kop surat ---- */
        .kepala td { vertical-align: top; padding: 0; }
        .kepala .tengah { text-align: center; }
        /* Kop sedikit lebih kecil dari Debit Note - isi invoice export lebih
           banyak, supaya invoice biasa tetap muat satu halaman. */
        .nama-pt { font-size: 23px; font-weight: bold; }
        .alamat-pt { font-size: 11px; line-height: 1.35; margin-top: 2px; }

        /* ---- Judul ---- */
        .judul { text-align: center; font-size: 24px; font-weight: bold; color: #e2231a; margin-top: 8px; }
        /* Nomor & tanggal satu baris. */
        .nomor { text-align: center; font-size: 13.5px; font-weight: bold; margin-top: 1px; }
        .nomor .tanggal { font-weight: normal; color: #555555; }

        /* ---- Blok berjudul garis merah (pihak, notes, manufacturer) ----
           Garis merah jadi kolom sendiri yang cuma berwarna di baris judul;
           isinya sejajar dengan tulisan judul, tidak menempel ke garis. */
        .blok { font-size: 11.5px; }
        .blok td { padding: 0; }
        .blok td.garis { width: 3px; }
        .blok td.garis.merah { background: #e2231a; }
        .blok td.judul-blok { font-size: 12.5px; font-weight: bold; padding: 1px 0 1px 8px; }
        .blok td.isi { padding: 3px 0 0 8px; line-height: 1.35; }
        .blok td.sekat { width: 14px; border-right: 1px solid #e6e6e6; }
        .blok td.sela { width: 14px; }

        /* ---- Tabel bergaris tipis (shipment & summary) ---- */
        .grid { font-size: 10.5px; }
        .grid th { background: #f5f6f7; border: 1px solid #dcdcdc; padding: 3px 5px; font-weight: bold; text-align: center; vertical-align: middle; line-height: 1.25; }
        .grid td { border: 1px solid #dcdcdc; padding: 3px 5px; text-align: center; vertical-align: middle; }
        .grid .kiri { text-align: left; }
        .grid .ket-produk { background: #f5f6f7; font-weight: bold; }
        .ringkas .simbol { border-right: none; text-align: left; padding-right: 0; }
        .ringkas .nilai { border-left: none; text-align: right; }
        .ringkas .tebal { font-weight: bold; }
        .ringkas .total-nilai { font-weight: bold; background: #fdeef0; }
        .grid td.tanpa-atas { border-top: none; }

        .jarak { height: 10px; }
        .tengah { text-align: center; }

        /* ---- Tanda tangan ---- */
        .ttd td { vertical-align: top; font-size: 11.5px; }
        .ttd .garis-bawah { border-bottom: 1px solid #222222; }

        /* ---- Kaki halaman ---- */
        .kaki { width: 100%; font-size: 8.5px; border-top: 1px solid #f0cbcf; }
        .kaki .kiri { color: #8a8a8a; letter-spacing: 1.6px; padding-top: 4px; }
        .kaki .hal { color: #8a8a8a; text-align: center; font-size: 9px; padding-top: 4px; }
        .kaki .kanan { text-align: right; color: #e2231a; font-family: dancingscript; font-size: 22px; }
    </style>
</head>
<body>

    <htmlpagefooter name="kaki">
        {{-- Pita 12mm (Debit Note 16mm) - isi invoice export lebih banyak. --}}
        <img src="{{ $gbr_pita }}" style="width: 194mm; height: 12mm;">
        <table class="kaki">
            <tr>
                <td class="kiri" width="40%">PT NIRWANA ALABARE GARMENT</td>
                <td class="hal" width="20%">{PAGENO} / {nbpg}</td>
                <td class="kanan" width="40%">Caring &#8212;</td>
            </tr>
        </table>
    </htmlpagefooter>

    {{-- ===== Kop surat ===== --}}
    <table class="kepala">
        <tr>
            <td width="32mm"><img src="{{ $logo }}" width="96"></td>
            <td width="130mm" class="tengah">
                <div class="nama-pt">{{ $kop['nama'] }}</div>
                <div class="alamat-pt">
                    @foreach ($kop['alamat'] as $i => $b)
                        {!! $i ? '<br>' : '' !!}{{ $b }}
                    @endforeach
                    <br>{{ $kop['telp'] }}
                </div>
            </td>
            <td width="32mm" align="right"><img src="{{ $gbr_caring }}" width="92"></td>
        </tr>
    </table>

    {{-- ===== Judul, nomor & tanggal =====
         Satu nomor saja: Invoice Number #2 kalau diisi, selain itu nomor sistem. --}}
    <div class="judul">{{ $judul }}</div>
    <div class="nomor">INVOICE NO : {{ $noCetak }}
        <span class="tanggal">&nbsp;&nbsp;|&nbsp;&nbsp;DATE : {{ $tanggal }}</span></div>

    {{-- ===== Pihak-pihak: SHIP FROM | SELLER, lalu PURCHASER | SHIP TO ===== --}}
    @foreach (array(array($pihak[0], $pihak[1]), array($pihak[2], $pihak[3])) as $pasang)
        <div class="jarak"></div>
        <table class="blok">
            <tr>
                <td class="garis merah"></td>
                <td class="judul-blok" width="46%">{{ $pasang[0][0] }}</td>
                <td class="sekat"></td>
                <td class="sela"></td>
                <td class="garis merah"></td>
                <td class="judul-blok">{{ $pasang[1][0] }}</td>
            </tr>
            <tr>
                @foreach ($pasang as $j => $p)
                    @if ($j === 1)
                        <td class="sekat"></td>
                        <td class="sela"></td>
                    @endif
                    <td class="garis"></td>
                    <td class="isi">
                        @foreach ($p[1] as $i => $b)
                            {!! $i ? '<br>' : '' !!}@if ($i === 0 && $p[2])<b>{{ $b }}</b>@else{{ $b }}@endif
                        @endforeach
                    </td>
                @endforeach
            </tr>
        </table>
    @endforeach

    {{-- ===== Shipment Details - satu blok per baris Shipment ===== --}}
    @foreach ($kirim as $k)
        <div class="jarak"></div>
        <table class="grid">
            <tr>
                <th style="width:11%">Dest Purchase</th>
                <th style="width:13%">Style NO</th>
                <th style="width:11%">Brand</th>
                <th style="width:11%">Chanel Description</th>
                <th style="width:13%">Currency</th>
                <th style="width:13%">Payment Term</th>
                <th style="width:13%">Final Destination</th>
                <th style="width:15%">Country of origin</th>
            </tr>
            <tr>
                <td>{{ $k['dest_purchase'] }}</td>
                <td>{{ $k['style_no'] }}</td>
                <td>{{ $k['brand'] }}</td>
                <td>{{ $k['chanel_description'] }}</td>
                <td>{{ $k['currency'] }}</td>
                <td>{{ $k['payment_term'] }}</td>
                <td>{{ $k['final_destination'] }}</td>
                <td>{{ $k['country_origin'] }}</td>
            </tr>
            <tr>
                <th>Ship Mode</th>
                <th>Term of Sale</th>
                <th>Transfer Point</th>
                <th>Port Of Loading</th>
                <th>Total Gross<br>Weight(KGS)</th>
                <th>Total Net<br>Weight(KGS)</th>
                <th>Total Net Net<br>Weight(KGS)</th>
                <th>Total Carton</th>
            </tr>
            <tr>
                <td>{{ $k['ship_mode'] }}</td>
                <td>{{ $k['term_of_sale'] }}</td>
                <td>{{ $k['transfer_point'] }}</td>
                <td>{{ $k['port_of_loading'] }}</td>
                <td>{{ $berat($k['total_gross_weight']) }}</td>
                <td>{{ $berat($k['total_net_weight']) }}</td>
                <td>{{ $berat($k['total_net_net_weight']) }}</td>
                <td>{{ $bulat($k['total_carton']) }}</td>
            </tr>
            {{-- Judul & isi Product Description satu baris - hemat satu baris
                 per shipment tanpa merapatkan baris lain. --}}
            <tr>
                <td class="ket-produk">Product Description</td>
                <td colspan="7" class="kiri">{{ $k['product_description'] }}</td>
            </tr>
        </table>
    @endforeach

    {{-- ===== Invoice Summary ===== --}}
    <div class="jarak"></div>
    <table class="blok">
        <tr>
            <td class="garis merah"></td>
            <td class="judul-blok">Invoice Summary</td>
        </tr>
    </table>
    <table class="grid ringkas" style="margin-top: 6px">
        {{-- Kolom Total Pieces cuma dicetak kalau ada baris SET: untuk satuan asli angkanya
             sama persis dengan Quantity Invoiced, jadi tidak perlu diulang. Kalau
             kolomnya tidak ada, lebarnya dibagikan ke Color Name & Quantity. --}}
        <tr>
            <th style="width:12%">Color Code</th>
            <th style="width:{{ $adaSet ? 17 : 27 }}%">Color Name</th>
            @if ($adaSet)
                <th style="width:20%">Total Pieces (SET)</th>
            @endif
            <th style="width:{{ $adaSet ? 20 : 30 }}%">Quantity Invoiced (Each)</th>
            <th style="width:10%">Unit Cost</th>
            <th style="width:21%" colspan="2">Extended Line Total</th>
        </tr>
        @foreach ($summary as $r)
            <tr>
                <td class="kiri">{{ $r['color_code'] }}</td>
                <td>{{ $r['color_name'] }}</td>
                @if ($adaSet)
                    {{-- Baris PCS dikosongkan - angkanya sudah ada di Quantity Invoiced. --}}
                    <td>{{ $r['satuan'] === 'SET' && $r['total_pieces'] ? $bulat($r['total_pieces']) : '' }}</td>
                @endif
                <td>{{ $bulat($r['qty']) }}</td>
                <td>{{ $unit($r['unit_cost']) }}</td>
                <td class="simbol" style="width:4%">{{ $simbol }}</td>
                <td class="nilai" style="width:17%">{{ $uang($r['extended']) }}</td>
            </tr>
        @endforeach
    </table>

    {{-- Rekap: tabel sendiri, dijaga utuh (tidak terpotong di tengah halaman)
         dan tanpa kepala kolom - kalau pindah halaman, kepala tidak terulang.
         Lebar kolomnya sama dengan tabel di atas: label = Color Code + Color
         Name. Baris pertama tanpa garis atas supaya garisnya tidak dobel. --}}
    {{-- autosize="1": mPDF tidak boleh mengecilkan tabel ini supaya muat - kalau
         tidak muat, pindah halaman utuh dalam ukuran normal. --}}
    <table class="grid ringkas" style="page-break-inside: avoid" autosize="1">
        @foreach ($rekapBaris as $i => $r)
            @php $atas = $i === 0 ? 'tanpa-atas' : ''; @endphp
            <tr>
                <td class="kiri {{ $atas }} {{ $r[3] ? 'tebal' : '' }}" @if ($i === 0) style="width:{{ $adaSet ? 29 : 39 }}%" @endif>{{ $r[0] }}</td>
                @if ($adaSet)
                    <td class="{{ $atas }}" @if ($i === 0) style="width:20%" @endif></td>
                @endif
                <td class="{{ $atas }} {{ $r[3] ? 'tebal' : '' }}" @if ($i === 0) style="width:{{ $adaSet ? 20 : 30 }}%" @endif>{{ $r[1] === null ? '' : $bulat($r[1]) }}</td>
                <td class="{{ $atas }}" @if ($i === 0) style="width:10%" @endif></td>
                <td class="simbol {{ $atas }} {{ $r[3] ? 'tebal' : '' }}" @if ($i === 0) style="width:4%" @endif>{{ $r[2] === null ? '' : $simbol }}</td>
                <td class="nilai {{ $atas }} {{ $r[3] ? 'tebal' : '' }}" @if ($i === 0) style="width:17%" @endif>{{ $r[2] === null ? '' : $uang($r[2]) }}</td>
            </tr>
        @endforeach
        {{-- Net Invoice Total berlatar merah muda, sama dengan Grand Total Debit Note. --}}
        <tr>
            <td class="kiri tebal" colspan="{{ $adaSet ? 4 : 3 }}">Net Invoice Total</td>
            <td class="simbol total-nilai">{{ $simbol }}</td>
            <td class="nilai total-nilai">{{ $uang($rekap['grand']) }}</td>
        </tr>
    </table>

    {{-- ===== Invoice Notes & Manufacturer Information ===== --}}
    <div class="jarak"></div>
    {{-- autosize="1": jangan dikecilkan mPDF supaya muat - pindah halaman utuh saja. --}}
    <table class="blok" style="page-break-inside: avoid" autosize="1">
        <tr>
            <td class="garis merah"></td>
            <td class="judul-blok" width="46%">Invoice Notes</td>
            <td class="sekat"></td>
            <td class="sela"></td>
            <td class="garis merah"></td>
            <td class="judul-blok">Manufacturer Information</td>
        </tr>
        <tr>
            <td class="garis"></td>
            <td class="isi">{!! nl2br(e((string) $inv['invoice_notes'])) !!}&nbsp;</td>
            <td class="sekat"></td>
            <td class="sela"></td>
            <td class="garis"></td>
            <td class="isi">
                <b>{{ $inv['manufacturer_nama'] }}</b>
                @foreach ($alamatPabrik as $b)
                    <br>{{ $b }}
                @endforeach
            </td>
        </tr>
    </table>

    {{-- ===== Pernyataan & tanda tangan ===== --}}
    <div class="jarak"></div>
    {{-- autosize="1": tanpa ini mPDF mengecilkan huruf blok tanda tangan supaya
         muat di sisa halaman - teksnya jadi kecil sekali. --}}
    <table class="ttd" style="page-break-inside: avoid" autosize="1">
        <tr>
            <td colspan="5">I hereby certify that all information provided is true and correct</td>
        </tr>
        {{-- REFF naik ke baris Prepare's Name - ruang kosongnya cukup satu,
             untuk tanda tangan basah. --}}
        <tr>
            <td colspan="3" style="width:58%">Prepare's Name</td>
            <td style="width:10%">REFF</td>
            <td style="width:32%">{{ $inv['reference'] }}</td>
        </tr>
        <tr>
            <td style="width:3%;height:38px"></td>
            <td style="width:25%"></td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td></td>
            <td class="tengah garis-bawah">{{ $penanda['nama'] }}</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td></td>
            <td class="tengah">{{ $penanda['jabatan'] }}</td>
            <td colspan="3"></td>
        </tr>
    </table>

</body>
</html>
