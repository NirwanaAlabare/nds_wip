<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='9'>EFISIENSI TOTAL</td>
    </tr>
    <tr>
        {{-- <td colspan='12'>{{ date('d-M-Y', strtotime($from)) }} - {{ date('d-M-Y', strtotime($to)) }}
        </td> --}}
    </tr>
    <tr>
        <td></td>
    </tr>
    <thead>
        <tr>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Tgl. Plan</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Tgl. Trans</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Line</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">WS</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Buyer</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Style</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">SMV</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">MP</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Mins Avail</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Target</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Output</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Currency</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">CM Price</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Earning</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Mins. Prod</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Efficiency</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">RFT</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Jam Kerja Aktual</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $item)
            <tr>
                <td>{{ $item->tgl_plan_fix }}</td>
                <td>{{ $item->tgl_trans_fix }}</td>
                <td>{{ $item->sewing_line }}</td>
                <td>{{ $item->kpno }}</td>
                <td>{{ $item->buyer }}</td>
                <td>{{ $item->styleno }}</td>
                <td>{{ $item->smv }}</td>
                <td>{{ $item->man_power }}</td>
                <td>{{ $item->mins_avail }}</td>
                <td>{{ $item->target }}</td>
                <td>{{ $item->tot_output }}</td>
                <td>{{ $item->curr }}</td>
                <td>{{ $item->cm_price }}</td>
                <td>{{ $item->earning }}</td>
                <td>{{ $item->mins_prod }}</td>
                <td>{{ $item->eff_line }}</td>
                <td>{{ $item->rfts }}</td>
                <td>{{ $item->jam_kerja_act }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
