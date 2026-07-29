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
                <th colspan="17" class="header-judul">
                    PT NIRWANA ALABARE GARMENT <br>
                    LAPORAN {{ strtoupper($jenis) }} - {{ strtoupper(str_replace('-', ' ', $kategori)) }}<br>
                    PERIODE: {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} S/D {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}<br>
                    FILTER BERDASARKAN : {{ strtoupper($kategoriBarang) }} | TANGGAL {{strtoupper(str_replace('-', ' ', $filterBy))}}
                </th>
            </tr>
            <tr class="header-kolom">
                <th>No</th>
                <th>Kode Kantor</th>
                <th>Jenis Dokumen</th>
                <th>Kategori Barang</th>
                <th>Nomor Daftar</th>
                <th>Tanggal Daftar</th>
                <th>Nama {{ $jenis == 'pemasukan' ? 'Pengirim' : 'Penerima' }}</th>
                <th>Nomor BPB</th>
                <th>Tanggal BPB</th>
                <th>ID Item</th>
                <th>Uraian Barang</th>
                <th>Jenis Satuan</th>
                <th>Jumlah Satuan</th>
                <th>Kode Valuta</th>
                <th>Nilai Barang</th>
                <th>Kurs</th>
                <th>Nilai Barang IDR</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->kode_kantor ?? '-' }}</td>
                    <td>{{ strtoupper(str_replace('-', ' ', $kategori)) }}</td>
                    <td>{{ $row->kategori_barang ?? '-' }}</td>
                    <td>{{ $row->nomor_daftar ?? '-' }}</td>
                    <td>{{ $row->tanggal_daftar ?? '-' }}</td>
                    <td>{{ $row->nama_pengirim ?? '-' }}</td>
                    <td>{{ $row->nomor_bpb ?? '-' }}</td>
                    <td>{{ $row->tanggal_bpb ?? '-' }}</td>
                    <td>{{ $row->id_item ?? '-' }}</td>
                    <td>{{ $row->uraian_barang ?? '-' }}</td>
                    <td>{{ $row->jenis_satuan ?? '-' }}</td>
                    <td class="text-right">{{ $row->jumlah_satuan ?? 0 }}</td>
                    <td>{{ $row->kode_valuta ?? '-' }}</td>
                    <td class="text-right">{{ $row->nilai_barang ?? 0 }}</td>
                    <td class="text-right">{{ $row->kurs ?? 0 }}</td>
                    <td class="text-right">{{ $row->nilai_barang_idr ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="17" class="text-center">Tidak ada data untuk laporan ini pada rentang tanggal tersebut.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
