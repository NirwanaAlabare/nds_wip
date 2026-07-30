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
        .tabel-excel { border-collapse: collapse; width: 100%; }
        .tabel-excel th, .tabel-excel td { border: 1px solid #000000; padding: 5px; }
        .header-judul { font-size: 16px; font-weight: bold; text-align: center; padding: 10px; border: 1px solid #000000; }
        .header-kolom { background-color: #d9edf7; font-weight: bold; text-align: center; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <table class="tabel-excel">
        <thead>
            <tr>
                <th colspan="13" class="header-judul">
                    PT NIRWANA ALABARE GARMENT <br>
                    LAPORAN MUTASI BARANG JADI <br>
                    KATEGORI: {{ strtoupper($kategoriBarang == 'all' ? 'SEMUA KATEGORI' : $kategoriBarang) }}<br>
                    PERIODE: {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} S/D {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}
                </th>
            </tr>
            <tr class="header-kolom">
                <th>No</th>
                <th>Id So Det</th>
                <th>Kode Barang</th>
                <th>Style</th>
                <th>No WS</th>
                <th>Color</th>
                <th>Size</th>
                <th>Dest / Country</th>
                <th>Unit</th>
                <th>Saldo Awal</th>
                <th>Penerimaan</th>
                <th>Pengeluaran</th>
                <th>Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->id_so_det ?? '-' }}</td>
                    <td>{{ $row->goods_code ?? '-' }}</td>
                    <td>{{ $row->styleno ?? '-' }}</td>
                    <td>{{ $row->kpno ?? '-' }}</td>
                    <td>{{ $row->color ?? '-' }}</td>
                    <td>{{ $row->size ?? '-' }}</td>
                    <td>{{ $row->country ?? '-' }}</td>
                    <td class="text-center">PCS</td>

                    <td class="text-right">{{ $row->saldoawal ?? 0 }}</td>
                    <td class="text-right">{{ $row->qtyterima ?? 0 }}</td>
                    <td class="text-right">{{ $row->qtykeluar ?? 0 }}</td>
                    <td class="text-right">{{ $row->saldoakhir ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="text-center">Tidak ada data mutasi barang jadi untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
