<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='21' style="font-size: 16px;"><b>Laporan Data Rekonsiliasi Ceisa vs Signalbit</b></td>
    </tr>
    <tr>
        <td colspan='21' style="font-size: 12px;">Periode {{ date('d-M-Y', strtotime($from)) }} s/d {{ date('d-M-Y', strtotime($to)) }}
        </td>
    </tr>
    <thead>
        <tr>
            <th>No</th>
            <th>Jenis Dok</th>
            <th>No Daftar</th>
            <th>Tgl Daftar</th>
            <th>No Aju</th>
            <th>Tgl Aju</th>
            <th>Supplier Ceisa</th>
            <th>Supplier SB</th> 
            <th>Satuan Ceisa</th>
            <th>Satuan SB</th> 
            <th>Qty Ceisa</th>
            <th>Qty SB</th> 
            <th>Diff Qty</th>
            <th>Total Ceisa</th>
            <th>Total IDR</th>
            <th>Total SB</th>
            <th>Total SB IDR</th>
            <th>Diff Total</th>
            <th>Status</th>
            <th>Issue List</th>
            <th>Description Update</th>
        </tr>
    </thead>
    <tbody>
     @php
     $no = 1;
     @endphp
     @foreach ($data as $item)
     <tr>
        <td>{{ $no++ }}.</td> 
        <td>{{ $item->kode_dokumen }}</td>
        <td>{{ $item->no_daftar }}</td>
        <td>{{ $item->tgl_daftar }}</td>
        <td>{{ $item->no_aju }}</td>
        <td>{{ $item->tgl_aju }}</td>
        <td>{{ $item->nama_entitas }}</td>
        <td>{{ $item->supplier }}</td>

        <td>{!! str_replace(' ', '<br>', $item->satuan_ciesa_tampil) !!}</td>
        <td>{!! str_replace(' ', '<br>', $item->satuan_sb) !!}</td>

        {{-- Format angka 2 desimal, ribuan koma --}}
        <td>{{ number_format($item->qty, 2, '.', ',') }}</td>
        <td>{{ number_format($item->qty_sb, 2, '.', ',') }}</td>
        <td>{{ number_format($item->diff_qty, 2, '.', ',') }}</td>
        <td>{{ number_format($item->total, 2, '.', ',') }}</td>
        <td>{{ number_format($item->total_idr, 2, '.', ',') }}</td>
        <td>{{ number_format($item->total_sb, 2, '.', ',') }}</td>
        <td>{{ number_format($item->total_sb_idr, 2, '.', ',') }}</td>
        <td>{{ number_format($item->diff_total, 2, '.', ',') }}</td>

        <td>{{ $item->status }}</td>
        <td>{{ $item->status_kesesuaian }}</td>
        <td>{{ $item->keterangan_update }}</td>
    </tr>
    @endforeach

</tbody>

</table>

</html>
