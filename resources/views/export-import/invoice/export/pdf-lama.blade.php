{{-- ===========================================================================
     CADANGAN tampilan CLASSIC dari export/pdf.blade.php (sebelum gaya CARING).
     Dibuka lewat tombol "PDF Classic" (&gaya=lama). Isinya sama persis dengan
     versi sebelum gaya CARING dipasang.
     =========================================================================== --}}
{{-- ===========================================================================
     PDF Invoice Export - dua versi dari data yang sama:
       CM  ($versi = 'cm')  untuk penagihan tim AR
       FOB ($versi = 'fob') untuk perizinan barang keluar BC
     Yang berbeda cuma harga di Invoice Summary & rekapnya. Judulnya sama -
     versinya sengaja tidak ditulis di dokumen.

     Susunannya mengikuti contoh cetakan dari tim EXIM (baris 1-35 di lembar
     spesifikasi): kop, pihak-pihak, blok pengiriman, Invoice Summary, penutup.
     Nomor FG/OUT sengaja tidak dicetak - itu cuma asal angka Qty Invoiced.

     Data disiapkan InvoiceEximController::dataCetakExport(). Mesin PDF: mPDF,
     jadi CSS-nya dibuat sederhana (tabel, tanpa flex/grid).
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
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $noCetak }}</title>
    <style>
        body { font-family: dejavusanscondensed; font-size: 8.5pt; color: #000; }
        table { border-collapse: collapse; width: 100%; }
        td, th { vertical-align: top; padding: 2px 4px; }

        .bingkai { border: 1px solid #000; }

        /* Kop surat */
        .kop td { vertical-align: middle; padding: 6px 8px; }
        .kop-nama { font-size: 17pt; font-weight: bold; text-align: center; letter-spacing: .3px; }
        .kop-alamat { font-size: 7.5pt; font-style: italic; text-align: center; line-height: 1.35; }
        .kop-telp { font-size: 7.5pt; font-style: italic; font-weight: bold; text-align: center; }
        .kop-garis { border-bottom: 3px double #000; }

        .judul { font-size: 11pt; font-weight: bold; text-align: center; padding: 10px 0 8px; }
        .tebal { font-weight: bold; }
        .tengah { text-align: center; }
        .kanan { text-align: right; }

        /* Grid bergaris: blok pengiriman & Invoice Summary */
        .grid td, .grid th { border: 1px solid #000; vertical-align: middle; }
        .grid th { font-weight: bold; text-align: center; font-size: 7.5pt; }
        .grid td { text-align: center; }
        .grid .kiri { text-align: left; }

        .ringkas th { font-weight: normal; }
        .ringkas .simbol { border-right: none; text-align: left; padding-right: 0; }
        .ringkas .nilai { border-left: none; text-align: right; }

        .jarak { height: 10px; }
        .garis-atas { border-top: 1px solid #000; }
        .garis-bawah { border-bottom: 1px solid #000; }
    </style>
</head>
<body>
<div class="bingkai">

    {{-- ===== Kop surat ===== --}}
    <table class="kop kop-garis">
        <tr>
            <td style="width:20%"><img src="{{ $logo }}" style="width:118px"></td>
            <td style="width:80%">
                <div class="kop-nama">{{ $kop['nama'] }}</div>
                @foreach ($kop['alamat'] as $b)
                    <div class="kop-alamat">{{ $b }}</div>
                @endforeach
                <div class="kop-telp">{{ $kop['telp'] }}</div>
            </td>
        </tr>
    </table>

    <div class="judul">{{ $judul }}</div>

    {{-- ===== Nomor & tanggal =====
         Satu nomor saja: Invoice Number #2 kalau diisi, selain itu nomor sistem.
         Kolomnya sama dengan blok pihak di bawah (50% | 10% | 40%), jadi DATE
         sejajar label pihak di kanan dan tanggalnya sejajar isinya. --}}
    <table>
        <tr>
            <td style="width:15%">INVOICE NO :</td>
            <td style="width:35%">{{ $noCetak }}</td>
            <td style="width:10%">DATE :</td>
            <td style="width:40%">{{ $tanggal }} &nbsp;|&nbsp; {{ $labelVersi }}</td>
        </tr>
    </table>

    {{-- ===== Pihak-pihak =====
         Kiri: label di atas, isinya di bawah. Kanan: isinya sejajar label -
         sama dengan contoh cetakan. --}}
    {{-- Labelnya saja yang berbeda dari sebelumnya; isinya tetap dari kolom
         yang sama (seller_* & purchaser_*). --}}
    @foreach ([['SHIP FROM', $baris['shipper'], 'PURCHASER / INVOICE TO', $baris['seller']],
               ['ULTIMATE CONSIGNEE', $baris['purchaser'], 'SHIP TO :', $baris['receiver']]] as $p)
        <div class="jarak"></div>
        <table>
            <tr>
                <td style="width:50%">
                    <span class="tebal">{{ $p[0] }}</span>
                    @foreach ($p[1] as $b)
                        <br>{{ $b }}
                    @endforeach
                </td>
                <td style="width:10%" class="tebal">{{ $p[2] }}</td>
                <td style="width:40%">
                    @foreach ($p[3] as $i => $b)
                        {!! $i ? '<br>' : '' !!}{{ $b }}
                    @endforeach
                </td>
            </tr>
        </table>
    @endforeach

    {{-- ===== Blok pengiriman (baris 8-24) - satu blok per baris Shipment =====
         Lebar kolom dibagi supaya judul berat (KGS) & JAKARTA,ID tidak terpotong. --}}
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
            <tr>
                <td colspan="8" style="font-weight:normal">Product Description</td>
            </tr>
            <tr>
                <td colspan="8">{{ $k['product_description'] }}</td>
            </tr>
        </table>
    @endforeach

    {{-- ===== Invoice Summary (baris 25-31) ===== --}}
    <div class="jarak"></div>
    <div style="padding:2px 4px 4px">Invoice Summary</div>
    <table class="grid ringkas">
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

        @php
            // Baris yang di contoh cetakan memang kosong (Total Dozens, Hard Tag
            // Cost) ikut dicetak kosong. DP, DP/CBD, Return & VAT hanya muncul
            // kalau ada nilainya - invoice export biasanya tidak memakainya.
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
            $rekapBaris[] = ['Net Invoice Total', null, $rekap['grand'], true];
        @endphp
        @foreach ($rekapBaris as $r)
            <tr>
                {{-- Label rekap memakai dua kolom - kolom Color Code terlalu sempit. --}}
                <td class="kiri" colspan="2">{{ $r[0] }}</td>
                @if ($adaSet)
                    <td></td>
                @endif
                <td>{{ $r[1] === null ? '' : $bulat($r[1]) }}</td>
                <td></td>
                <td class="simbol {{ $r[3] ? 'tebal' : '' }}">{{ $r[2] === null ? '' : $simbol }}</td>
                <td class="nilai {{ $r[3] ? 'tebal' : '' }}">{{ $r[2] === null ? '' : $uang($r[2]) }}</td>
            </tr>
        @endforeach
    </table>

    {{-- ===== Penutup (baris 32-34) ===== --}}
    <div class="jarak"></div>
    <table>
        <tr><td class="garis-atas">Invoice Notes :</td></tr>
        <tr><td class="garis-bawah">{!! nl2br(e((string) $inv['invoice_notes'])) !!}&nbsp;</td></tr>
    </table>

    <div class="jarak"></div>
    <table>
        <tr><td colspan="2">Manufacturer Information</td></tr>
        <tr>
            <td style="width:45%;border-top:1px solid #000;border-bottom:1px solid #000;border-right:1px solid #000" class="tengah">
                {{ $inv['manufacturer_nama'] }}
            </td>
            <td style="width:55%;border-top:1px solid #000;border-bottom:1px solid #000">
                {!! nl2br(e(trim((string) $inv['manufacturer_alamat']))) !!}
            </td>
        </tr>
    </table>

    <div class="jarak"></div>
    {{-- Nama & jabatan menempati kolom 25% dengan jarak 3% dari bingkai kiri,
         jadi garis di bawah nama selebar kolom itu saja dan tidak menempel ke
         bingkai halaman. --}}
    <table style="page-break-inside: avoid">
        <tr>
            <td colspan="5">I hereby certify that all information provided is true and correct</td>
        </tr>
        <tr>
            <td colspan="5">Prepare's Name</td>
        </tr>
        <tr>
            <td style="width:3%;height:34px"></td>
            <td style="width:25%"></td>
            <td style="width:30%"></td>
            <td style="width:10%;vertical-align:bottom">REFF</td>
            <td style="width:32%;vertical-align:bottom">{{ $inv['reference'] }}</td>
        </tr>
        <tr>
            <td colspan="5" style="height:46px"></td>
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
        <tr><td colspan="5" style="height:10px"></td></tr>
    </table>

</div>
</body>
</html>
