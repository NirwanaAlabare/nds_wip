<?php
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        .tabel-excel {
            border-collapse: collapse;
            width: 100%;
        }
        .tabel-excel th, .tabel-excel td {
            border: 1px solid #000000;
            padding: 5px;
        }
        .header-judul {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            border: 1px solid #000000;
        }
        .header-kolom {
            background-color: #d9edf7;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>

    <table class="tabel-excel">
        <thead>
            <tr>
                <th colspan="10" class="header-judul">
                    PT NIRWANA ALABARE GARMENT <br>
                    LAPORAN MUTASI BAHAN BAKU DAN PENOLONG <br>
                    KATEGORI: {{ strtoupper($kategoriBarang == 'all' ? 'SEMUA KATEGORI' : $kategoriBarang) }}<br>
                    PERIODE: {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} S/D {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}
                </th>
            </tr>
            <tr class="header-kolom">
                <th>No</th>
                <th>ID Item</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>No WS</th>
                <th>Saldo Awal</th>
                <th>Pemasukan</th>
                <th>Pengeluaran</th>
                <th>Saldo Akhir</th>
                <th>Satuan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->id_item ?? '-' }}</td>
                    <td>{{ $row->goods_code ?? '-' }}</td>
                    <td>{{ $row->itemdesc ?? '-' }}</td>
                    <td>{{ $row->kpno ?? '-' }}</td>
                    <td class="text-right">{{ $row->saldoawal ?? 0 }}</td>
                    <td class="text-right">{{ $row->qtyterima ?? 0 }}</td>
                    <td class="text-right">{{ $row->qtykeluar ?? 0 }}</td>
                    <td class="text-right">{{ $row->saldoakhir ?? 0 }}</td>
                    <td class="text-center">{{ $row->unit ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">Tidak ada data mutasi bahan baku untuk periode dan kategori ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
