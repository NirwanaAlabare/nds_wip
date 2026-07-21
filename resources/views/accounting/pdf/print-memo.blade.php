<!DOCTYPE html>
<html>
<head>
    <title>Memo Permintaan Pembayaran</title>
    <style>
        @page { margin: 20px 25px; }
        body { margin: 0; font-family: Arial, sans-serif; font-size: 10pt; color: #000; }

        /* ── HEADER ── */
        .header-table { width:100%; border-collapse:collapse; border-bottom:3px solid #000; padding-bottom:6px; margin-bottom:10px; }
        .header-logo  { width:90px; vertical-align:middle; }
        .header-logo img { width:80px; }
        .header-company { vertical-align:middle; padding-left:8px; }
        .header-company .name { font-size:16pt; font-weight:bold; }
        .header-company .addr { font-size:7.5pt; color:#555; margin-top:2px; }

        /* ── TITLE ── */
        .title-area { text-align:center; margin:10px 0 6px; }
        .title-area h2 { font-size:13pt; font-weight:bold; text-decoration:underline; margin:0; letter-spacing:1px; }
        .nomor-area { text-align:center; margin-bottom:10px; font-size:10pt; text-decoration:underline; font-weight:bold; }

        /* ── INFO ── */
        .info-table { width:50%; border-collapse:collapse; margin-bottom:14px; font-size:10pt; }
        .info-table td { padding:1px 4px; vertical-align:top; }
        .info-label { font-weight:bold; width:90px; }

        /* ── BODY TEXT ── */
        .body-text { font-size:10pt; margin-bottom:6px; }
        .body-text-orange { font-size:10pt; color:#c0392b; margin-bottom:10px; }

        /* ── DETAIL TABLE ── */
        .detail-table { width:100%; border-collapse:collapse; margin-bottom:20px; font-size:10pt; }
        .detail-table td { border:1px solid #bbb; padding:5px 8px; vertical-align:top; }
        .detail-table .col-label { width:35%; font-weight:bold; background:#fafafa; }
        .detail-table .col-colon { width:3%; text-align:center; }

        /* ── SIGNATURE ── */
        .sign-table { width:100%; border-collapse:collapse; margin-top:20px; font-size:10pt; }
        .sign-table td { padding:4px 8px; vertical-align:top; }
        .sign-label { font-weight:bold; color:#c0392b; padding-bottom:50px; }
        .sign-name  { font-weight:bold; border-top:1px solid #000; padding-top:2px; }
        .sign-title { font-size:9pt; color:#555; }
    </style>
</head>
<body>

{{-- ── KOP SURAT ── --}}
<table class="header-table">
    <tr>
        <td class="header-logo">
            <img src="{{ public_path('nag-logo.png') }}" alt="Logo">
        </td>
        <td class="header-company">
            <div class="name">PT NIRWANA ALABARE GARMENT</div>
            <div class="addr">
                Jl. Raya Rancaekek – Majalaya No. 289<br>
                Desa Solokan Jeruk Kecamatan Solokan Jeruk, Kabupaten Bandung 40382<br>
                Telp. 022-5959049 &nbsp; Fax. 022-5959261
            </div>
        </td>
    </tr>
</table>

{{-- ── JUDUL ── --}}
<div class="title-area">
    <h2>MEMO PERMINTAAN PEMBAYARAN</h2>
</div>
<div class="nomor-area">{{ $memo->nomor }}</div>

{{-- ── INFO MEMO ── --}}
<table class="info-table">
    <tr>
        <td class="info-label">Nomor</td>
        <td>: <u>{{ $memo->nomor }}</u></td>
    </tr>
    <tr>
        <td class="info-label">Kepada</td>
        <td>: {{ $memo->kepada }}</td>
    </tr>
    <tr>
        <td class="info-label">Perihal</td>
        <td>: {{ $memo->perihal }}</td>
    </tr>
</table>

{{-- ── BODY TEXT ── --}}
<p class="body-text">Dengan hormat,</p>
<p class="body-text-orange">Mohon dapat dibayarkan biaya sesuai dengan keterangan di bawah ini :</p>

{{-- ── DETAIL TABLE ── --}}
<table class="detail-table">
    <tr>
        <td class="col-label">Kepada</td>
        <td class="col-colon">:</td>
        <td>{{ $memo->kepada_detail }}</td>
    </tr>
    <tr>
        <td class="col-label">Jumlah</td>
        <td class="col-colon">:</td>
        <td>{{ $memo->mata_uang }} {{ number_format($memo->jumlah, 2) }}</td>
    </tr>
    <tr>
        <td class="col-label">Peruntukan</td>
        <td class="col-colon">:</td>
        <td>{{ $memo->peruntukan }}</td>
    </tr>
    <tr>
        <td class="col-label">Tanggal Pembayaran</td>
        <td class="col-colon">:</td>
        <td>{{ $memo->tgl_pembayaran ?? '' }}</td>
    </tr>
    <tr>
        <td class="col-label">Bank &amp; No Rekening</td>
        <td class="col-colon">:</td>
        <td>{{ $memo->bank_rekening }}</td>
    </tr>
    <tr>
        <td class="col-label">Nama Penerima</td>
        <td class="col-colon">:</td>
        <td>{{ $memo->nama_penerima }}</td>
    </tr>
    <tr>
        <td class="col-label">Lampiran</td>
        <td class="col-colon">:</td>
        <td>{{ $memo->lampiran }}</td>
    </tr>
</table>

{{-- ── TANDA TANGAN ── --}}
<table class="sign-table">
    <tr>
        <td class="sign-label">Diajukan oleh</td>
        <td class="sign-label">Disetujui oleh</td>
        <td class="sign-label">&nbsp;</td>
    </tr>
    <tr style="height:60px;">
        <td></td><td></td><td></td>
    </tr>
    <tr>
        <td class="sign-name">{{ $memo->diajukan_nama }}</td>
        <td class="sign-name">{{ $memo->disetujui_nama }}</td>
        <td class="sign-name">{{ $memo->mengetahui_nama }}</td>
    </tr>
    <tr>
        <td class="sign-title">{{ $memo->diajukan_jabatan }}</td>
        <td class="sign-title">{{ $memo->disetujui_jabatan }}</td>
        <td class="sign-title">{{ $memo->mengetahui_jabatan }}</td>
    </tr>
</table>

</body>
</html>
