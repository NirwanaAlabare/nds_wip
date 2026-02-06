<!DOCTYPE html>
<html lang="en">

<table border="1" cellpadding="5">
    <tr>
        <th colspan="16" style="text-align:left;font-size:16px;font-weight:bold;">
            REPORT DC
        </th>
    </tr>
    <tr>
        <th colspan="16" style="text-align:left;">
            Periode : {{ $from }} s/d {{ $to }}
        </th>
    </tr>
    <tr><th colspan="16"></th></tr>

    <tr style="background:#ddd;font-weight:bold;">
        <th>No. WS</th>
        <th>Buyer</th>
        <th>Style</th>
        <th>Color</th>
        <th>Size</th>
        <th>Part</th>
        <th>Saldo Awal</th>
        <th>Masuk</th>
        <th>Kirim Sec Dalam</th>
        <th>Terima Rep Sec Dalam</th>
        <th>Terima Good Sec Dalam</th>
        <th>Kirim Sec Luar</th>
        <th>Terima Rep Sec Luar</th>
        <th>Terima Good Sec Luar</th>
        <th>Loading</th>
        <th>Saldo Akhir</th>
    </tr>

    @php
        $totalSaldoAwal = $totalMasuk = $totalKirimSecDalam = 0;
        $totalTerimaRepairedSecDalam = $totalTerimaGoodSecDalam = 0;
        $totalKirimSecLuar = $totalTerimaRepairedSecLuar = 0;
        $totalTerimaGoodSecLuar = $totalLoading = $totalSaldoAkhir = 0;
    @endphp

    @foreach ($dataReport as $data)
    @php
        $saldoAwal = $data->saldo_awal ?? 0;
        $masuk = $data->qty_in ?? 0;
        $kirimDalam = $data->kirim_secondary_dalam ?? 0;
        $repDalam = $data->terima_repaired_secondary_dalam ?? 0;
        $goodDalam = $data->terima_good_secondary_dalam ?? 0;
        $kirimLuar = $data->kirim_secondary_luar ?? 0;
        $repLuar = $data->terima_repaired_secondary_luar ?? 0;
        $goodLuar = $data->terima_good_secondary_luar ?? 0;
        $loading = $data->loading ?? 0;

        $saldoAkhir = $saldoAwal + $masuk - $kirimDalam + $repDalam + $goodDalam - $kirimLuar + $repLuar + $goodLuar - $loading;

        $totalSaldoAwal += $saldoAwal;
        $totalMasuk += $masuk;
        $totalKirimSecDalam += $kirimDalam;
        $totalTerimaRepairedSecDalam += $repDalam;
        $totalTerimaGoodSecDalam += $goodDalam;
        $totalKirimSecLuar += $kirimLuar;
        $totalTerimaRepairedSecLuar += $repLuar;
        $totalTerimaGoodSecLuar += $goodLuar;
        $totalLoading += $loading;
        $totalSaldoAkhir += $saldoAkhir;
    @endphp

    <tr>
        <td>{{ $data->ws }}</td>
        <td>{{ $data->buyer }}</td>
        <td>{{ $data->style }}</td>
        <td>{{ $data->color }}</td>
        <td>{{ $data->size }}</td>
        <td>{{ $data->nama_part }}</td>
        <td>{{ $saldoAwal }}</td>
        <td>{{ $masuk }}</td>
        <td>{{ $kirimDalam }}</td>
        <td>{{ $repDalam }}</td>
        <td>{{ $goodDalam }}</td>
        <td>{{ $kirimLuar }}</td>
        <td>{{ $repLuar }}</td>
        <td>{{ $goodLuar }}</td>
        <td>{{ $loading }}</td>
        <td>{{ $saldoAkhir }}</td>
    </tr>
    @endforeach

    <tr style="font-weight:bold;background:#eee;">
        <td colspan="6" style="text-align:right;">TOTAL</td>
        <td>{{ $totalSaldoAwal }}</td>
        <td>{{ $totalMasuk }}</td>
        <td>{{ $totalKirimSecDalam }}</td>
        <td>{{ $totalTerimaRepairedSecDalam }}</td>
        <td>{{ $totalTerimaGoodSecDalam }}</td>
        <td>{{ $totalKirimSecLuar }}</td>
        <td>{{ $totalTerimaRepairedSecLuar }}</td>
        <td>{{ $totalTerimaGoodSecLuar }}</td>
        <td>{{ $totalLoading }}</td>
        <td>{{ $totalSaldoAkhir }}</td>
    </tr>
</table>
</html>
