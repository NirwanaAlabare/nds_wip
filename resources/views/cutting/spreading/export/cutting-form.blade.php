<!DOCTYPE html>
<html lang="en">

<table>
    <tr></tr>
    <tr>
        <th></th>
        <th>Dari : {{ $dateFrom }}</th>
        <th>Sampai : {{ $dateFrom }}</th>
    </tr>
    <tr></tr>
    <tr>
        <th style="background: #505154; color: #FBFBFB;padding:10px;font-weight:bold;">TANGGAL</th>
        <th style="background: #505154; color: #FBFBFB;padding:10px;font-weight:bold;">MEJA</th>
        <th style="background: #505154; color: #FBFBFB;padding:10px;font-weight:bold;">WORKSHEET</th>
        <th style="background: #505154; color: #FBFBFB;padding:10px;font-weight:bold;">BUYER</th>
        <th style="background: #505154; color: #FBFBFB;padding:10px;font-weight:bold;">STYLE</th>
        <th style="background: #505154; color: #FBFBFB;padding:10px;font-weight:bold;">COLOR</th>
        <th style="background: #505154; color: #FBFBFB;padding:10px;font-weight:bold;">SIZE</th>
        <th style="background: #505154; color: #FBFBFB;padding:10px;font-weight:bold;">GROUP</th>
        <th style="background: #505154; color: #FBFBFB;padding:10px;font-weight:bold;">LOT</th>
        <th style="background: #505154; color: #FBFBFB;padding:10px;font-weight:bold;">CUT NUMBER</th>
        <th style="background: #505154; color: #FBFBFB;padding:10px;font-weight:bold;">NO FORM</th>
        <th style="background: #505154; color: #FBFBFB;padding:10px;font-weight:bold;">NO MARKER</th>
        <th style="background: #505154; color: #FBFBFB;padding:10px;font-weight:bold;">PANEL</th>
        <th style="background: #505154; color: #FBFBFB;padding:10px;font-weight:bold;">PART</th>
        <th style="background: #505154; color: #FBFBFB;padding:10px;font-weight:bold;">QTY</th>
    </tr>
    @foreach ($data as $d)
        <tr>
            <td style="text-align: left;">{{ $d->tanggal }}</td>
            <td style="text-align: left;">{{ $d->meja }}</td>
            <td style="text-align: left;">{{ $d->worksheet }}</td>
            <td style="text-align: left;">{{ $d->buyer }}</td>
            <td style="text-align: left;">{{ $d->style }}</td>
            <td style="text-align: left;">{{ $d->color }}</td>
            <td style="text-align: left;">{{ $d->size }}</td>
            <td style="text-align: left;">{{ $d->group_roll }}</td>
            <td style="text-align: left;">{{ $d->lot }}</td>
            <td style="text-align: left;">{{ $d->no_cut }}</td>
            <td style="text-align: left;">{{ $d->no_form }}</td>
            <td style="text-align: left;">{{ $d->no_marker }}</td>
            <td style="text-align: left;">{{ $d->panel }}</td>
            <td style="text-align: left;">{{ $d->part }}</td>
            <td>{{ $d->qty }}</td>
        </tr>
    @endforeach
</table>

</html>
