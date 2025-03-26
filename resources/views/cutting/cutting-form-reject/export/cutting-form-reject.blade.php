<!DOCTYPE html>
<html lang="en">

<table>
    <tr></tr>
    <tr>
        <th>Dari : {{ $dateFrom }}</th>
        <th>Sampai : {{ $dateTo }}</th>
    </tr>
    <tr></tr>
    <tr>
        <th style="background: #b5c7fc;padding:10px;font-weight:bold;">TANGGAL</th>
        <th style="background: #b5c7fc;padding:10px;font-weight:bold;">WORKSHEET</th>
        <th style="background: #b5c7fc;padding:10px;font-weight:bold;">BUYER</th>
        <th style="background: #b5c7fc;padding:10px;font-weight:bold;">STYLE</th>
        <th style="background: #b5c7fc;padding:10px;font-weight:bold;">COLOR</th>
        <th style="background: #b5c7fc;padding:10px;font-weight:bold;">PANEL</th>
        <th style="background: #b5c7fc;padding:10px;font-weight:bold;">PART</th>
        <th style="background: #b5c7fc;padding:10px;font-weight:bold;">NO. FORM</th>
        <th style="background: #b5c7fc;padding:10px;font-weight:bold;">SIZE</th>
        <th style="background: #b5c7fc;padding:10px;font-weight:bold;">GROUP</th>
        <th style="background: #b5c7fc;padding:10px;font-weight:bold;">QTY</th>
        <th style="background: #b5c7fc;padding:10px;font-weight:bold;">NOTE</th>
    </tr>
    @php
        $totalQty = 0;
    @endphp
    @foreach ($data as $d)
        @php
            $totalQty += $d->qty;
        @endphp
        <tr>
            <td style="text-align: left;">{{ $d->tanggal }}</td>
            <td style="text-align: left;">{{ $d->act_costing_ws }}</td>
            <td style="text-align: left;">{{ $d->buyer }}</td>
            <td style="text-align: left;">{{ $d->style }}</td>
            <td style="text-align: left;">{{ $d->color }}</td>
            <td style="text-align: left;">{{ $d->panel }}</td>
            <td style="text-align: left;">{{ $d->part }}</td>
            <td style="text-align: left;">{{ $d->no_form }}</td>
            <td style="text-align: left;">{{ $d->size }}</td>
            <td style="text-align: left;">{{ $d->group }}</td>
            <td style="text-align: right;">{{ $d->qty }}</td>
            <td style="text-align: left;">{{ $d->notes }}</td>
        </tr>
    @endforeach
    <tr>
        <td style="text-align: right;font-weight: bold;" colspan="9">TOTAL</td>
        <td style="font-weight: bold;">{{ $totalQty }}</td>
    </tr>
</table>

</html>
