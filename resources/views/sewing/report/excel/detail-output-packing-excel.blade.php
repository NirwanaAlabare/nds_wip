<!DOCTYPE html>
<html lang="en">
    <table>
        <tr>
        </tr>
        <tr>
            <th rowspan="2">Tanggal</th>
            <th rowspan="2">Line</th>
            <th rowspan="2">No. WS</th>
            <th rowspan="2">Buyer</th>
            <th rowspan="2">Style</th>
            <th colspan="6" style="background: #bce4ff;text-align: center;">Output Finishing</th>
        </tr>
        <tr>
            <th style="background: #bce4ff;">Color</th>
            <th style="background: #bce4ff;">Size</th>
            <th style="background: #bce4ff;">No. Bon</th>
            <th style="background: #bce4ff;">Total PCS</th>
            <th style="background: #bce4ff;">Man Power</th>
            <th style="background: #bce4ff;">Jam Kerja</th>
        </tr>
        @foreach ($dataDetailProduksiDay as $day)
            <tr>
                <td>{{ $day->tgl_plan }}</td>
                <td data-format="@">{{ $day->sewing_line }}</td>
                <td>{{ $day->no_ws }}</td>
                <td>{{ $day->nama_buyer }}</td>
                <td data-format="@">{{ $day->no_style }}</td>
                <td data-format="@">{{ $day->color }}</td>
                <td>{{ $day->size }}</td>
                <td>-</td>
                <td data-format="0">{{ $day->output }}</td>
                <td>-</td>
                <td>-</td>
            </tr>
        @endforeach
    </table>
</html>
