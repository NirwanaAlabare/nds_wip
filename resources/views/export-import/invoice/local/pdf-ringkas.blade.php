{{-- ===========================================================================
     PDF Invoice Local EXIM - bentuk RINGKAS (Summary), gaya CARING.

     Isinya sama dengan versi sebelumnya: baris digabung per produk, harga
     satuan tanpa Rp, kolom TOTAL, total qty, rekap Subtotal / potongan yang
     terpakai / PPN / TOTAL, lalu Approved By - Yus Yulius / Exim Manager.
     Tampilannya disamakan dengan PDF Debit Note di AR (reportdebitnote_v2):
     kop dengan blok nilai C-A-R-I-N-G, judul merah, tabel berkepala abu,
     TOTAL berlatar merah muda, pita merah + "Caring -" di kaki halaman.

     Blok rekap & tanda tangan tetap sama lebar (38% halaman, rata kanan),
     sesuai permintaan sebelumnya.

     Tampilan lama disimpan di pdf-ringkas-lama.blade.php dan masih bisa
     dibuka dengan &gaya=lama untuk pembanding.
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

        /* ---- Keterangan ---- */
        .info { width: 100%; font-size: 12px; margin-top: 20px; }
        .info td { padding: 2px 0; vertical-align: top; }
        .info .label { width: 104px; }
        .info .titik { width: 11px; }

        /* ---- Tabel produk & rekap ---- */
        .rincian { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 18px; }
        .rincian th { background: #f5f6f7; border: 1px solid #dcdcdc; padding: 7px 6px; font-weight: bold; text-align: center; }
        .rincian td { border: 1px solid #dcdcdc; padding: 7px 6px; vertical-align: top; }
        .angka { text-align: right; }
        .tengah { text-align: center; }
        .tebal { font-weight: bold; }
        .mata-uang { width: 7%; text-align: center; }

        /* Satu tabel, bukan tabel di dalam tabel: mPDF mengecilkan & menggeser
           tabel yang bersarang. Kolom kosong 62% di kiri membuat rekap tepat
           selebar blok tanda tangan (38%). */
        .rekap { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 10px; }
        .rekap td { border: 1px solid #dcdcdc; padding: 7px 6px; }
        .rekap td.kosong { border: none; }
        .rekap .label-rekap { text-align: right; background: #f5f6f7; }
        .rekap .total-nilai { font-weight: bold; background: #fdeef0; }

        /* ---- Tanda tangan ---- */
        .ttd { width: 100%; margin-top: 30px; font-size: 11.5px; }
        .ttd td { vertical-align: top; }
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

    {{-- ---------------- Keterangan ---------------- --}}
    <table class="info">
        <tr>
            <td class="label">Date</td>
            <td class="titik">:</td>
            <td>{{ $tgl_cetak }}</td>
        </tr>
        <tr>
            <td class="label">To</td>
            <td class="titik">:</td>
            <td>{{ $tujuan_npwp }}</td>
        </tr>
        <tr>
            <td class="label">Address</td>
            <td class="titik">:</td>
            <td>
                {{ $data_invoice['customer'] }}
                @foreach ($alamat_baris as $baris)
                    <br>{{ $baris }}
                @endforeach
            </td>
        </tr>
    </table>

    {{-- ---------------- Rincian produk ---------------- --}}
    <table class="rincian">
        <thead>
            <tr>
                <th width="45%">Product</th>
                <th width="15%">Quantity</th>
                {{-- Rp cuma dipasang di kolom Total; harga satuan cukup angkanya. --}}
                <th width="15%">Unit Price</th>
                <th width="25%" colspan="2">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($baris_produk as $b)
                <tr>
                    <td>{{ $b['nama'] }}</td>
                    <td class="angka">{{ $b['qty'] }}</td>
                    <td class="angka">{{ $b['unit_price'] }}</td>
                    <td class="mata-uang">{{ $simbol }}</td>
                    <td class="angka">{{ $b['total'] }}</td>
                </tr>
            @endforeach
            {{-- Jumlah qty persis di bawah kolom Quantity. --}}
            <tr>
                <td class="angka tebal">Total Qty</td>
                <td class="angka tebal">{{ $total_qty }}</td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>

    {{-- ---------------- Rekap ----------------
         Tabel sendiri, 38% lebar halaman dan rata kanan - sama lebar dengan
         blok tanda tangan di bawahnya. Potongan hanya muncul kalau terpakai. --}}
    <table class="rekap">
        @foreach ($baris_rekap as $r)
            <tr>
                <td class="kosong" width="62%">&nbsp;</td>
                <td class="label-rekap {{ $r['tebal'] ? 'tebal' : '' }}" width="17%">{{ $r['label'] }}</td>
                <td class="angka {{ $r['tebal'] ? 'tebal' : '' }}" width="21%">{{ $r['nilai'] }}</td>
            </tr>
        @endforeach
        @if ($pakai_grand)
            {{-- TOTAL berlatar merah muda, sama dengan Grand Total Debit Note. --}}
            <tr>
                <td class="kosong">&nbsp;</td>
                <td class="label-rekap tebal">TOTAL</td>
                <td class="angka total-nilai">{{ $grand_total_teks }}</td>
            </tr>
        @endif
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
