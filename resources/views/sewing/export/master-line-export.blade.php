<!DOCTYPE html>
<html lang="en">

<table>
    <tr>
        <th colspan="8">PRODUCTION TEAM</th>
    </tr>
    <tr>
        <th colspan="4">{{ $from." / ".$to }}</th>
    </tr>
    <tr></tr>
    <tr>
        <th style="font-weight: 800;border: 1px solid #000;">Tanggal</th>
        <th style="font-weight: 800;border: 1px solid #000;">Line</th>
        <th style="font-weight: 800;border: 1px solid #000;">Chief</th>
        <th style="font-weight: 800;border: 1px solid #000;">Leader</th>
        <th style="font-weight: 800;border: 1px solid #000;">IE</th>
        <th style="font-weight: 800;border: 1px solid #000;">Leader QC</th>
        <th style="font-weight: 800;border: 1px solid #000;">Mechanic</th>
        <th style="font-weight: 800;border: 1px solid #000;">Technical</th>
    </tr>
    @foreach ($masterLines as $masterLine)
        <tr>
            <td style="border: 1px solid #000;">{{ $masterLine->tanggal }}</td>
            <td style="border: 1px solid #000;">{{ $masterLine->line_name }}</td>
            <td style="border: 1px solid #000;">{{ $masterLine->chief_name }}</td>
            <td style="border: 1px solid #000;">{{ $masterLine->leader_name }}</td>
            <td style="border: 1px solid #000;">{{ $masterLine->ie_name }}</td>
            <td style="border: 1px solid #000;">{{ $masterLine->leaderqc_name }}</td>
            <td style="border: 1px solid #000;">{{ $masterLine->mechanic_name }}</td>
            <td style="border: 1px solid #000;">{{ $masterLine->technical_name }}</td>
        </tr>
    @endforeach
</table>

</html>
