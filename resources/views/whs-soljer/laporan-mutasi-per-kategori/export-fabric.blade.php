<!DOCTYPE html>
<html lang="en">

<table>
    <tr>
        <th colspan="9">Laporan Mutasi Per Kategori (FABRIC)</th>
    </tr>
    <tr>
        <th colspan="9">{{ date('d-m-Y', strtotime($from)) }} / {{ date('d-m-Y', strtotime($to)) }}</th>
    </tr>
    <tr></tr>
    <tr>
        <th style="font-weight: 800;border: 1px solid #000;">Barcode</th>
        <th style="font-weight: 800;border: 1px solid #000;">Buyer</th>
        <th style="font-weight: 800;border: 1px solid #000;">Jenis Item</th>
        <th style="font-weight: 800;border: 1px solid #000;">Warna</th>
        <th style="font-weight: 800;border: 1px solid #000;">Satuan</th>
        <th style="font-weight: 800;border: 1px solid #000;">Saldo Awal</th>
        <th style="font-weight: 800;border: 1px solid #000;">Pemasukan</th>
        <th style="font-weight: 800;border: 1px solid #000;">Pengeluaran</th>
        <th style="font-weight: 800;border: 1px solid #000;">Saldo Akhir</th>
    </tr>
    @php
        $total_saldo_awal = 0;
        $total_pemasukan = 0;
        $total_pengeluaran = 0;
        $total_saldo_akhir = 0;
    @endphp
    @foreach ($data as $d)
        @php
            $total_saldo_awal += $d->saldo_awal;
            $total_pemasukan += $d->pemasukan;
            $total_pengeluaran += $d->pengeluaran;
            $total_saldo_akhir += $d->saldo_akhir;
        @endphp
        <tr>
            <td style="border: 1px solid #000;">{{ $d->barcode }}</td>
            <td style="border: 1px solid #000;">{{ $d->buyer }} </td>
            <td style="border: 1px solid #000;">{{ $d->jenis_item }} </td>
            <td style="border: 1px solid #000;">{{ $d->warna }} </td>
            <td style="border: 1px solid #000;">{{ $d->satuan }} </td>
            <td style="border: 1px solid #000;">{{ $d->saldo_awal }} </td>
            <td style="border: 1px solid #000;">{{ $d->pemasukan }} </td>
            <td style="border: 1px solid #000;">{{ $d->pengeluaran }} </td>
            <td style="border: 1px solid #000;">{{ $d->saldo_akhir }} </td>
        </tr>
    @endforeach
    <tr>
        <td colspan="5" style="border: 1px solid #000; font-weight: bold; text-align: center;">TOTAL</td>
        <td style="border: 1px solid #000; font-weight: bold;">
            {{ $total_saldo_awal }}
        </td>
        <td style="border: 1px solid #000; font-weight: bold;">
            {{ $total_pemasukan }}
        </td>
        <td style="border: 1px solid #000; font-weight: bold;">
            {{ $total_pengeluaran }}
        </td>
        <td style="border: 1px solid #000; font-weight: bold;">
            {{ $total_saldo_akhir }}
        </td>
    </tr>
</table>

</html>