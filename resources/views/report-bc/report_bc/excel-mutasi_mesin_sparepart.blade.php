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
        <style>
        /* Tambahkan font modern agar tidak terlihat jadul */
        body {
            font-family: 'Segoe UI', Calibri, Arial, sans-serif;
            font-size: 11pt;
        }
        .tabel-excel {
            border-collapse: collapse;
            width: 100%;
        }
        .tabel-excel th, .tabel-excel td {
            border: 1px solid #000000;
            padding: 6px 8px;
        }
        .header-judul {
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            padding: 12px;
            border: 1px solid #000000;
            background-color: #f8fafc;
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
    </style>
</head>
<body>
    <table class="tabel-excel">
        <thead>
            <tr>
                <th colspan="9" class="header-judul">
                    PT NIRWANA ALABARE GARMENT <br>
                    LAPORAN MUTASI {{ strtoupper($kategoriBarang == 'sparepart' ? 'SPAREPART' : 'MESIN & PERALATAN KANTOR') }} <br>
                    PERIODE: {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} S/D {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}
                </th>
            </tr>
            <tr class="header-kolom">
                <th>No</th>
                <th>Id Item</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Saldo Awal</th>
                <th>Penerimaan</th>
                <th>Pengeluaran</th>
                <th>Saldo Akhir</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->id_item ?? '-' }}</td>
                    <td>{{ $row->kode_brg ?? '-' }}</td>
                    <td>{{ $row->nama_brg ?? '-' }}</td>
                    <td class="text-right">{{ $row->saldo_awal ?? 0 }}</td>
                    <td class="text-right">{{ $row->qtyrcv ?? 0 }}</td>
                    <td class="text-right">{{ $row->qtyout ?? 0 }}</td>
                    <td class="text-right">{{ $row->qty_akhir ?? 0 }}</td>
                    <td class="text-center">{{ $row->unit ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data mutasi untuk kategori ini pada rentang tanggal tersebut.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
