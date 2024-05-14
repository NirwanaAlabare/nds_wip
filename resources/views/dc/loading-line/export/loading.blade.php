<!DOCTYPE html>
<html lang="en">

<table>
    <tr>
        <th style="text-align: center;" colspan="7">Laporan Loading</th>
    </tr>
    <tr>
        <th style="text-align: center;" colspan="7">Tanggal : {{ $tanggal ? $tanggal : "Tanpa Filter" }}</th>
    </tr>
    <tr>
        <th style="text-align: center;font-weight: 800;">Tanggal Loading</th>
        <th style="text-align: center;font-weight: 800;">Line</th>
        <th style="text-align: center;font-weight: 800;">No. WS</th>
        <th style="text-align: center;font-weight: 800;">Style</th>
        <th style="text-align: center;font-weight: 800;">Color</th>
        <th style="text-align: center;font-weight: 800;">Size</th>
        <th style="text-align: center;font-weight: 800;">Loading Qty</th>
    </tr>
        @php
            $currentDate = "";
            $currentLine = "";
        @endphp
        @foreach ($data as $d)
            <tr>
                <?php
                    if ($currentDate != $d->tanggal_loading) {
                        ?>
                            <td data-format="date" style="text-align: center;vertical-align: middle;" rowspan="{{ $data->where("tanggal_loading", $d->tanggal_loading)->count() }}">{{ $d->tanggal_loading }}</td>
                        <?php

                        $currentDate = $d->tanggal_loading;
                        $currentLine = "";
                    }
                ?>

                <?php
                    if ($currentLine != $d->line_id) {
                        ?>
                            <td style="text-align: center;vertical-align: middle;" rowspan="{{ $data->where("line_id", $d->line_id)->where("tanggal_loading", $currentDate)->count() }}">{{ strtoupper(str_replace("_", " ", $lineData->where("line_id", $d->line_id)->first()->username)) }}</td>
                        <?php

                        $currentLine = $d->line_id;
                    }
                ?>

                <td>{{ $d->act_costing_ws }}</td>
                <td>{{ $d->style }}</td>
                <td>{{ $d->color }}</td>
                <td>{{ $d->size }}</td>
                <td data-format='0'>{{ $d->loading_qty }}</td>
            </tr>
        @endforeach
</table>

</html>
