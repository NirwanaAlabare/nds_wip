<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Koreksi Data - {{ $bap->no_form }}</title>
    <style>
        @page { margin: 25px 35px; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 11px; color: #000; }

        table { border-collapse: collapse; }
        .w-100 { width: 100%; }

        .header-table td { vertical-align: middle; }
        .company-name { margin: 0; font-size: 16px; font-weight: bold; text-align: center; }
        .doc-title { margin: 2px 0 0; font-size: 14px; font-weight: bold; text-align: center; text-decoration: underline; }
        .header-line { border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 10px; }

        .meta-table td { padding: 1px 0; font-size: 11px; }

        .body-text { margin: 12px 0 6px; }

        .section-table { margin-top: 8px; }
        .section-table th, .section-table td { border: 1px solid #000; padding: 5px 6px; font-size: 11px; }
        .section-header { background-color: #dbe4ee; color: #1f3a5f; text-align: center; font-weight: bold; }
        .label-col { width: 33%; vertical-align: top; }
        .sep-col { width: 2%; text-align: center; vertical-align: top; }
        .value-col { vertical-align: top; }

        .sign-table td { border: 1px solid #000; padding: 6px; vertical-align: top; text-align: center; font-size: 11px; }
        .sign-header { background-color: #dbe4ee; color: #1f3a5f; font-weight: bold; }
        .sign-space { height: 55px; }
        .sign-name { border-top: 1px solid #000; margin-top: 2px; padding-top: 2px; }

        .note-box { border: 1px solid #000; border-top: none; padding: 6px; min-height: 70px; font-size: 11px; }
        .note-label { font-weight: bold; }

        .it-table { margin-top: 12px; }
        .it-table th, .it-table td { border: 1px solid #000; padding: 5px 6px; font-size: 11px; }
        .it-header { background-color: #dbe4ee; color: #1f3a5f; text-align: center; font-weight: bold; }
        .it-label { width: 30%; }
        .it-value { height: 22px; }
    </style>
</head>
<body>

    <table class="w-100 header-line">
        <tr>
            <td style="width: 15%;">
                <img src="{{ public_path('assets/dist/img/nag-logo.png') }}" style="max-width: 90px; max-height: 50px; object-fit: contain;">
            </td>
            <td style="width: 70%;">
                <p class="company-name">PT. NIRWANA ALABARE GARMENT</p>
                <p class="doc-title">BERITA ACARA KOREKSI DATA</p>
            </td>
            <td style="width: 15%; text-align: right; font-size: 10px;">
                FM-SYS-001
            </td>
        </tr>
    </table>

    <table class="w-100 meta-table">
        <tr>
            <td style="width: 50%;">No. : {{ $bap->no_form }}</td>
            <td style="width: 50%; text-align: right;">Tanggal : {{ $bap->tgl_form ? date('d/m/Y', strtotime($bap->tgl_form)) : '-' }}</td>
        </tr>
    </table>

    <p class="body-text">
        Kepada Yth.<br>
        Bpk/Ibu Kepala Bagian IT<br>
        di tempat
    </p>

    <p class="body-text">Dengan Hormat,</p>
    <p class="body-text">Bersama ini kami sampaikan permintaan koreksi data sebagai berikut :</p>

    <table class="w-100 section-table">
        <tr>
            <td colspan="3" class="section-header">Diisi Oleh Pengguna (User)</td>
        </tr>
        <tr>
            <td class="label-col">Department</td>
            <td class="sep-col">:</td>
            <td class="value-col">{{ $bap->department ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Modul</td>
            <td class="sep-col">:</td>
            <td class="value-col">{{ $bap->modul ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">No Dokumen yang akan dikoreksi</td>
            <td class="sep-col">:</td>
            <td class="value-col">{{ $bap->no_dokumen ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Masalah yang terjadi</td>
            <td class="sep-col">:</td>
            <td class="value-col">{{ $bap->masalah ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Masalah ditemukan tanggal</td>
            <td class="sep-col">:</td>
            <td class="value-col">{{ $bap->tgl_masalah ? date('d/m/Y', strtotime($bap->tgl_masalah)) : '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Penyebab masalah tersebut</td>
            <td class="sep-col">:</td>
            <td class="value-col">{{ $bap->penyebab ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Usulan Penyelesaian masalah</td>
            <td class="sep-col">:</td>
            <td class="value-col">{{ $bap->usulan ?: '-' }}</td>
        </tr>
    </table>

    <table class="w-100 sign-table">
        <tr>
            <td style="width: 33.33%;" class="sign-header">Pengguna</td>
            <td style="width: 33.33%;" class="sign-header">Menyetujui</td>
            <td style="width: 33.34%;" class="sign-header">Mengetahui</td>
        </tr>
        <tr>
            <td class="sign-space"></td>
            <td class="sign-space"></td>
            <td class="sign-space"></td>
        </tr>
        <tr>
            <td class="sign-name">(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
            <td class="sign-name">(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
            <td class="sign-name">(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
        </tr>
    </table>

    <div class="note-box">
        <span class="note-label">Note :</span><br>
        {{ $bap->keterangan ?: '-' }}
    </div>

    <table class="w-100 it-table">
        <tr>
            <td colspan="2" class="it-header">Disetujui oleh,*</td>
        </tr>
        <tr>
            <td colspan="2" class="it-header">Diisi Oleh IT Dept.</td>
        </tr>
        <tr>
            <td class="it-label">Dikoreksi oleh</td>
            <td class="it-value"></td>
        </tr>
        <tr>
            <td class="it-label">Tanggal Koreksi</td>
            <td class="it-value"></td>
        </tr>
        <tr>
            <td class="it-label">Catatan</td>
            <td class="it-value" style="height: 45px;"></td>
        </tr>
    </table>

</body>
</html>
