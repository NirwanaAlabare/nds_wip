{{-- ===========================================================================
     PDF Invoice Local EXIM - bentuk KNITTING, gaya CARING.

     Khusus invoice knitting (NAK). Isinya sama dengan versi Classic
     (pdf-knitting-lama.blade.php): blok CONSIGNOR / CONSIGNEE / BILL TO, tabel
     tujuh kolom, lalu keterangan pengapalan & bank. Tampilannya disamakan
     dengan PDF Debit Note di AR (reportdebitnote_v2): kop dengan blok nilai
     C-A-R-I-N-G, judul merah, judul blok bergaris merah di kiri, tabel
     berkepala abu, dan pita merah + "Caring -" di kaki halaman.

     Alamat CONSIGNOR sengaja ditulis tetap di sini - itu alamat pabrik yang
     dipakai di dokumen ekspor, bukan data yang berubah per invoice.
     =========================================================================== --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $data_invoice['no_invoice'] }}</title>
    <style>
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

        /* ---- Blok pihak & pengapalan ----
           Garis merah di kiri judul dibuat sebagai kolom sendiri (td.garis) yang
           cuma berwarna di baris judul. Isi di bawahnya ada di kolom sebelahnya
           dengan jarak kiri yang sama dengan tulisan judul, jadi isinya sejajar
           dengan judul - tidak menempel ke garis merah. */
        .blok { width: 100%; border-collapse: collapse; font-size: 11.5px; }
        .blok td { vertical-align: top; padding: 0; }
        .blok td.garis { width: 3px; }
        .blok td.garis.merah { background: #e2231a; }
        .blok td.judul-blok { font-size: 12.5px; font-weight: bold; padding: 1px 0 1px 8px; }
        .blok td.nama { font-weight: bold; padding: 6px 0 0 8px; }
        /* Alamat satu baris per sel; baris pertama diberi jarak dari nama. */
        .blok td.alamat1 { padding: 5px 0 0 8px; }
        .blok td.alamat { padding: 2px 0 0 8px; }
        .blok td.antara { height: 12px; }
        /* Pemisah tipis di tengah, di antara CONSIGNOR dan CONSIGNEE. */
        .blok td.sekat { width: 16px; border-right: 1px solid #e6e6e6; }
        .blok td.sela { width: 16px; }
        .pihak { margin-top: 20px; }

        /* ---- Tabel rincian ---- */
        .rincian { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 20px; }
        .rincian th { background: #f5f6f7; border: 1px solid #dcdcdc; padding: 7px 6px; font-weight: bold; text-align: center; }
        .rincian td { border: 1px solid #dcdcdc; padding: 7px 6px; vertical-align: top; }
        .rincian .angka { text-align: right; }

        /* ---- Pengapalan & bank ---- */
        .kirim { margin-top: 26px; }
        .kirim td.lbl { width: 150px; font-weight: bold; padding: 3px 0 0 8px; }
        .kirim td.titik { width: 14px; padding-top: 3px; }
        .kirim td.nilai { padding-top: 3px; }

        /* ---- Kaki halaman ---- */
        .kaki { width: 100%; font-size: 8.5px; border-top: 1px solid #f0cbcf; }
        .kaki .kiri { color: #8a8a8a; letter-spacing: 1.6px; padding-top: 4px; }
        .kaki .hal { color: #8a8a8a; text-align: center; font-size: 9px; padding-top: 4px; }
        .kaki .kanan { text-align: right; color: #e2231a; font-family: dancingscript; font-size: 22px; }
    </style>
</head>

<body>

    <htmlpagefooter name="kaki">
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
    <div class="judul">{{ trim($data_invoice['type'] . ' INVOICE') }}</div>
    <div class="nomor">{{ $data_invoice['no_invoice'] }}</div>

    {{-- ---------------- Pengirim & penerima ---------------- --}}
    {{-- Kolom: [garis][CONSIGNOR][sekat][sela][garis][CONSIGNEE / BILL TO].
         Kiri & kanan disusun sebagai dua daftar baris lalu dipasangkan per
         baris - tinggi alamat CONSIGNOR tidak mendorong BILL TO ke bawah.
         Garis merah cuma di baris judul; isi di bawahnya sejajar dengan
         tulisan judul. Garis pemisah tengah dipasang per baris (bukan rowspan
         - rowspan di mPDF sering bermasalah), menyambung jadi satu garis. --}}
    @php
        // Baris yang panjang dipotong dulu (per kata, +-58 huruf) supaya tidak
        // terlipat di dalam selnya: kalau terlipat, baris itu jadi lebih tinggi
        // dan sisi seberangnya ikut renggang.
        $potong = function ($teks) {
            return explode("\n", wordwrap(trim((string) $teks), 58, "\n", true));
        };
        $susun = function ($judul, $nama, $alamat) use ($potong) {
            $isi = array(array('judul-blok', $judul));
            foreach ($potong($nama) as $baris) {
                $isi[] = array('nama', $baris);
            }
            $ke = 0;
            foreach ($alamat as $b) {
                foreach ($potong($b) as $baris) {
                    $isi[] = array($ke++ === 0 ? 'alamat1' : 'alamat', $baris);
                }
            }
            return $isi;
        };
        $kiri = $susun('CONSIGNOR', 'PT Nirwana Alabare Garment', array(
            'Jl. Raya Rancaekek - Majalaya No. 289', 'Solokan Jeruk, Majalaya',
            'Kab. Bandung, West Java, Indonesia', 'Zip code : 40376',
            'Phone No. : +62 22 85962076 / +62 22 85962081',
        ));
        $kanan = $susun('CONSIGNEE', $consignee['nama'], $consignee['baris']);
        if ($bill_to['nama'] !== '') {
            $kanan[] = array('antara', '');
            $kanan = array_merge($kanan, $susun('BILL TO', $bill_to['nama'], $bill_to['baris']));
        }
        $jmlBaris = max(count($kiri), count($kanan));
    @endphp
    <table class="blok pihak">
        @for ($i = 0; $i < $jmlBaris; $i++)
            @php
                $l = isset($kiri[$i]) ? $kiri[$i] : array('', '');
                $r = isset($kanan[$i]) ? $kanan[$i] : array('', '');
            @endphp
            <tr>
                <td class="garis {{ $l[0] === 'judul-blok' ? 'merah' : '' }}"></td>
                <td class="{{ $l[0] }}" @if ($i === 0) width="44%" @endif>{{ $l[1] }}</td>
                <td class="sekat"></td>
                <td class="sela"></td>
                <td class="garis {{ $r[0] === 'judul-blok' ? 'merah' : '' }}"></td>
                <td class="{{ $r[0] }}">{{ $r[1] }}</td>
            </tr>
        @endfor
    </table>

    {{-- ---------------- Rincian ---------------- --}}
    <table class="rincian">
        <thead>
            <tr>
                <th width="12%">Invoice Date</th>
                <th width="25%">Product Item</th>
                <th width="16%">Style / Color</th>
                <th width="13%">PO</th>
                <th width="11%">Qty ({{ $kol_uom }})</th>
                <th width="11%">Price ({{ $kol_curr }})</th>
                <th width="12%">Total ({{ $kol_curr }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($baris_knit as $b)
                <tr>
                    <td>{{ $tgl_knit }}</td>
                    <td>{{ $b['produk'] }}</td>
                    <td>{{ $b['warna'] }}</td>
                    <td>{{ $b['po'] }}</td>
                    <td class="angka">{{ $b['qty'] }}</td>
                    <td class="angka">{{ $b['unit_price'] }}</td>
                    <td class="angka">{{ $b['total'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ---------------- Pengapalan & bank ---------------- --}}
    {{-- Kolom: [garis][label][:][isi] - label & nama sejajar dengan tulisan judul. --}}
    <table class="blok kirim" style="page-break-inside: avoid">
        <tr>
            <td class="garis merah"></td>
            <td class="judul-blok" colspan="3">Shipping on behalf of</td>
        </tr>
        <tr>
            <td class="garis"></td>
            <td class="nama" colspan="3">{{ $kirim_atas }}</td>
        </tr>
        <tr>
            <td class="garis"></td>
            <td class="antara" colspan="3"></td>
        </tr>
        @if ($bayar['top'] !== '')
            <tr>
                <td class="garis"></td>
                <td class="lbl">Payment Terms</td>
                <td class="titik">:</td>
                <td class="nilai">{{ $bayar['top'] }}</td>
            </tr>
        @endif
        @foreach (array('bank' => 'Name of the bank', 'no_rek' => 'Bank Account Number',
                        'swift' => 'SWIFT Code', 'curr' => 'Bank account currency') as $k => $label)
            @if ($bayar[$k] !== '')
                <tr>
                    <td class="garis"></td>
                    <td class="lbl">{{ $label }}</td>
                    <td class="titik">:</td>
                    <td class="nilai">{{ $bayar[$k] }}</td>
                </tr>
            @endif
        @endforeach
    </table>

</body>

</html>
