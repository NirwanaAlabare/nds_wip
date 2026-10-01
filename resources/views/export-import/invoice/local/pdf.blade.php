{{-- ===========================================================================
     PDF Invoice Local EXIM - bentuk DETAIL, gaya CARING.

     Isinya sama dengan versi sebelumnya (per style / warna / size, lengkap
     dengan semua potongan), tampilannya disamakan dengan PDF Debit Note di AR
     (application/views/arnag/reportdebitnote_v2.php): kop dengan blok nilai
     C-A-R-I-N-G di kanan, judul merah, tabel berkepala abu, Grand Total
     berlatar merah muda, dan pita merah + tulisan "Caring -" di kaki halaman.

     Tampilan lama disimpan di pdf-lama.blade.php dan masih bisa dibuka dengan
     &gaya=lama untuk pembanding.

     Dirender mPDF (lihat mpdfCaring() di controller): tata letaknya pakai
     tabel, lingkaran CARING & pita memakai SVG.
     =========================================================================== --}}
@php
    $inv = $data_invoice;
    $gabung = function ($daftar, $kolom) {
        return implode(', ', array_filter(array_map(function ($r) use ($kolom) {
            return trim((string) $r[$kolom]);
        }, $daftar), function ($v) { return $v !== ''; }));
    };
    $mataUang = '';
    $totalQty = 0;
    foreach ($data_invoice_detail as $r) {
        $totalQty += (float) $r['qty'];
        if ($mataUang === '') { $mataUang = (string) $r['curr']; }
    }
    // Baris rekap: yang menentukan (Total) ditebalkan; Grand Total ditulis
    // terpisah di bawah karena berlatar merah muda.
    $rekap = array(
        array('Total', $data_invoice_pot['total'], true),
        array('Discount', $data_invoice_pot['discount'], false),
        array('Down Payment', $data_invoice_pot['dp'], false),
        array('Return', $data_invoice_pot['retur'], false),
        array('Total Before Value Added Tax', $data_invoice_pot['twot'], false),
        array('Value Added Tax', $data_invoice_pot['vat'], false),
    );
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $inv['no_invoice'] }}</title>
    <style>
        /* Kaki halaman didaftarkan lewat @page supaya ikut di setiap halaman. */
        @page {
            margin: 7mm 8mm 30mm 8mm;
            odd-footer-name: html_kaki;
            even-footer-name: html_kaki;
        }

        body { font-family: sans-serif; font-size: 11px; color: #222222; }

        /* ---- Kop surat ---- */
        .kepala td { vertical-align: top; padding: 0; }
        .kepala .tengah { text-align: center; }
        .nama-pt { font-size: 27px; font-weight: bold; }
        .alamat-pt { font-size: 14px; line-height: 1.5; margin-top: 4px; }

        /* ---- Judul ---- */
        /* Sedikit lebih kecil dari judul Debit Note (30px) - teks "COMMERCIAL INVOICE" lebih panjang. */
        .judul { text-align: center; font-size: 24px; font-weight: bold; color: #e2231a; margin-top: 18px; }
        .nomor { text-align: center; font-size: 16px; font-weight: bold; margin-top: 2px; }

        /* ---- Keterangan ---- */
        .info { width: 100%; font-size: 12px; margin-top: 20px; }
        .info td { padding: 2px 0; vertical-align: top; }
        .info .label { width: 104px; }
        .info .titik { width: 11px; }

        /* ---- Tabel rincian ---- */
        .rincian { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 18px; }
        .rincian th { background: #f5f6f7; border: 1px solid #dcdcdc; padding: 7px 6px; font-weight: bold; text-align: center; }
        .rincian td { border: 1px solid #dcdcdc; padding: 7px 6px; vertical-align: top; }
        .rincian .angka { text-align: right; }
        .rincian .tengah { text-align: center; }
        .rincian .label-rekap { text-align: right; }
        .rincian .tebal { font-weight: bold; }
        .rincian .total-nilai { font-weight: bold; background: #fdeef0; }
        /* Rekap menempel langsung di bawah tabel rincian. */
        .rincian.rekap-lanjut { margin-top: 0; }
        .rincian td.tanpa-atas { border-top: none; }

        /* ---- Tanda tangan ---- */
        .ttd { width: 100%; margin-top: 30px; font-size: 11.5px; }
        .ttd td { vertical-align: top; }
        .ttd .tengah { text-align: center; }
        .ttd .garis { border-bottom: 1px solid #222222; }

        /* ---- Kaki halaman ---- */
        .kaki { width: 100%; font-size: 8.5px; border-top: 1px solid #f0cbcf; }
        .kaki .kiri { color: #8a8a8a; letter-spacing: 1.6px; padding-top: 4px; }
        .kaki .hal { color: #8a8a8a; text-align: center; font-size: 9px; padding-top: 4px; }
        .kaki .kanan { text-align: right; color: #e2231a; font-family: dancingscript; font-size: 22px; }
    </style>
</head>

<body>

    <htmlpagefooter name="kaki">
        {{-- Pita merah melengkung, sama dengan PDF Debit Note. --}}
        <img src="{{ $gbr_pita }}" style="width: 194mm; height: 16mm;">
        <table class="kaki">
            <tr>
                <td class="kiri" width="40%">PT NIRWANA ALABARE GARMENT</td>
                <td class="hal" width="20%">{PAGENO} / {nbpg}</td>
                <td class="kanan" width="40%">Caring &#8212;</td>
            </tr>
        </table>
    </htmlpagefooter>

    {{-- ---------------- Kop surat ---------------- --}}
    <table class="kepala" width="100%">
        <tr>
            {{-- 32 + 130 + 32 = 194mm = lebar isi, jadi nama perusahaan tepat
                 di tengah halaman. --}}
            <td width="32mm"><img src="{{ $logo }}" width="112"></td>
            <td width="130mm" class="tengah">
                <div class="nama-pt">PT. NIRWANA ALABARE GARMENT</div>
                <div class="alamat-pt">
                    Jl. Raya Rancaekek &#8211; Majalaya No. 289 Desa Solokan Jeruk<br>
                    Kecamatan Solokan Jeruk, Kabupaten Bandung 40382<br>
                    Telp. 022-85962081
                </div>
            </td>
            <td width="32mm" align="right"><img src="{{ $gbr_caring }}" width="105"></td>
        </tr>
    </table>

    {{-- ---------------- Judul ---------------- --}}
    <div class="judul">{{ trim($inv['type'] . ' INVOICE') }}</div>
    <div class="nomor">{{ $inv['no_invoice'] }}</div>

    {{-- ---------------- Keterangan ---------------- --}}
    <table class="info">
        <tr>
            <td class="label">Date</td>
            <td class="titik">:</td>
            <td>{{ $inv['tgl_inv'] }}</td>
        </tr>
        <tr>
            <td class="label">To</td>
            <td class="titik">:</td>
            <td>{{ $inv['customer'] }}</td>
        </tr>
        <tr>
            <td class="label">Address</td>
            <td class="titik">:</td>
            <td>{!! nl2br(e((string) $inv['alamat'])) !!}</td>
        </tr>
        <tr>
            <td class="label">Telp.</td>
            <td class="titik">:</td>
            <td>{{ $inv['phone'] }}</td>
        </tr>
        <tr>
            <td class="label">BPPB#</td>
            <td class="titik">:</td>
            <td>{{ $gabung($group_bppb_number, 'shipp_number') }}</td>
        </tr>
        <tr>
            <td class="label">Sales Order#</td>
            <td class="titik">:</td>
            <td>{{ $gabung($group_so_number, 'so_number') }}</td>
        </tr>
    </table>

    {{-- ---------------- Rincian ---------------- --}}
    <table class="rincian">
        <thead>
            {{-- Style dibuat paling lebar supaya kode style yang panjang (mis.
                 "OS-DANBOWL RIB (OTTER)") tetap satu baris seperti PDF Classic;
                 ruangnya diambil dari Quantity & Unit Price yang masih longgar. --}}
            <tr>
                <th width="21%">Style</th>
                <th width="14%">Color</th>
                <th width="22%">Product Item</th>
                <th width="12%" colspan="2">Quantity</th>
                <th width="13%">Unit Price</th>
                <th width="18%" colspan="2">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data_invoice_detail as $r)
                <tr>
                    <td>{{ $r['styleno'] }}</td>
                    <td>{{ $r['color'] }}</td>
                    <td>{{ trim($r['product_item'] . ' (' . $r['size'] . ')') }}</td>
                    <td class="angka">{{ $r['qty'] }}</td>
                    <td class="tengah">{{ $r['uom'] }}</td>
                    <td class="angka">{{ $r['curr'] }} {{ $r['unit_price'] }}</td>
                    <td class="tengah" width="5%">{{ $r['curr'] }}</td>
                    <td class="angka">{{ $r['total_price'] }}</td>
                </tr>
            @endforeach

            {{-- Jumlah qty persis di bawah kolom Quantity. --}}
            <tr>
                <td class="label-rekap tebal" colspan="3">Total Qty</td>
                <td class="angka tebal">{{ 0 + $totalQty }}</td>
                <td colspan="4"></td>
            </tr>

        </tbody>
    </table>

    {{-- ---------------- Rekap ----------------
         Tabel sendiri tanpa kepala kolom: kalau rekapnya jatuh ke halaman baru,
         kepala tabel rincian tidak ikut terulang di atasnya. Lebar kolomnya
         sama dengan tabel rincian (Style s/d Unit Price = 82%, mata uang 5%,
         nilai 13%), jadi di satu halaman tetap terlihat menyambung. Baris
         pertamanya tanpa garis atas supaya garisnya tidak dobel. --}}
    {{-- autosize="1": mPDF tidak boleh mengecilkan tabel ini supaya muat - kalau
         tidak muat, pindah halaman utuh dalam ukuran normal. --}}
    <table class="rincian rekap-lanjut" style="page-break-inside: avoid" autosize="1">
        @foreach ($rekap as $i => $x)
            <tr>
                <td class="label-rekap {{ $x[2] ? 'tebal' : '' }} {{ $i === 0 ? 'tanpa-atas' : '' }}" width="82%">{{ $x[0] }}</td>
                <td class="tengah {{ $i === 0 ? 'tanpa-atas' : '' }}" width="5%">{{ $mataUang }}</td>
                <td class="angka {{ $x[2] ? 'tebal' : '' }} {{ $i === 0 ? 'tanpa-atas' : '' }}" width="13%">{{ $x[1] }}</td>
            </tr>
        @endforeach

        {{-- Grand Total berlatar merah muda, sama dengan PDF Debit Note. --}}
        <tr>
            <td class="label-rekap tebal">Grand Total</td>
            <td class="tengah total-nilai">{{ $mataUang }}</td>
            <td class="angka total-nilai">{{ $data_invoice_pot['grand_total'] }}</td>
        </tr>
    </table>

    {{-- ---------------- Tanda tangan ---------------- --}}
    <table class="ttd" style="page-break-inside: avoid">
        <tr>
            <td width="62%">&nbsp;</td>
            <td class="tengah">Approved By</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td style="height:70px">&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td class="tengah garis">Yus Yulius</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td class="tengah">Exim Manager</td>
        </tr>
    </table>

</body>

</html>
