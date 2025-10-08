<table>
    <tr style='text-align:center; vertical-align:middle'>
        <th style="font-weight: bold;border: 1px solid black">Line</th>
        <th style="font-weight: bold;border: 1px solid black">Chief</th>
        <th style="font-weight: bold;border: 1px solid black">Leader</th>
        <th style="font-weight: bold;border: 1px solid black">Style</th>
        <th style="font-weight: bold;border: 1px solid black">SMV</th>
        <th style="font-weight: bold;border: 1px solid black">MP</th>
        <th style="font-weight: bold;border: 1px solid black">Jml Hr</th>
        <th style="font-weight: bold;border: 1px solid black">Eff H-2</th>
        <th style="font-weight: bold;border: 1px solid black">Eff H-1</th>
        <th style="font-weight: bold;border: 1px solid black">JK</th>
        <th style="font-weight: bold;border: 1px solid black">Eff 100 %</th>
        <th style="font-weight: bold;border: 1px solid black">Eff</th>
        <th style="font-weight: bold;border: 1px solid black">Target</th>
        <th style="font-weight: bold;border: 1px solid black">Target/H</th>
        <th style="font-weight: bold;border: 1px solid black">Target/J</th>
        <th style="font-weight: bold;border: 1px solid black">JK Akt</th>
        <th style="font-weight: bold;border: 1px solid black">1</th>
        <th style="font-weight: bold;border: 1px solid black">2</th>
        <th style="font-weight: bold;border: 1px solid black">3</th>
        <th style="font-weight: bold;border: 1px solid black">4</th>
        <th style="font-weight: bold;border: 1px solid black">5</th>
        <th style="font-weight: bold;border: 1px solid black">6</th>
        <th style="font-weight: bold;border: 1px solid black">7</th>
        <th style="font-weight: bold;border: 1px solid black">8</th>
        <th style="font-weight: bold;border: 1px solid black">9</th>
        <th style="font-weight: bold;border: 1px solid black">10</th>
        <th style="font-weight: bold;border: 1px solid black">11</th>
        <th style="font-weight: bold;border: 1px solid black">12</th>
        <th style="font-weight: bold;border: 1px solid black">13</th>
        <th style="font-weight: bold;border: 1px solid black">Output</th>
        <th style="font-weight: bold;border: 1px solid black">Eff</th>
        <th style="font-weight: bold;border: 1px solid black">Eff Line</th>
    </tr>
    @php
        $uniqueManPower = [];
        $uniqueEfficiency = [];

        $totalTarget100 = 0;
        $totalTargetOutputEff = 0;
        $totalTargetPerHari = 0;
        $currentLine = "";

        $totalOutputJam1 = 0;
        $totalOutputJam2 = 0;
        $totalOutputJam3 = 0;
        $totalOutputJam4 = 0;
        $totalOutputJam5 = 0;
        $totalOutputJam6 = 0;
        $totalOutputJam7 = 0;
        $totalOutputJam8 = 0;
        $totalOutputJam9 = 0;
        $totalOutputJam10 = 0;
        $totalOutputJam11 = 0;
        $totalOutputJam12 = 0;
        $totalOutputJam13 = 0;

        $totalOutputAll = 0;
    @endphp
    @foreach ($data as $d)
        @php
            $line = $d->sewing_line;
            $power = intval($d->man_power);

            if (!isset($uniqueManPower[$line])) {
                $uniqueManPower[$line] = $power;
            }

            $totalTarget100 += $d->target_100;
            $totalTargetOutputEff += $d->target_output_eff;
            $totalTargetPerHari += $d->set_target_perhari;

            $totalOutputJam1 += $d->o_jam_1;
            $totalOutputJam2 += $d->o_jam_2;
            $totalOutputJam3 += $d->o_jam_3;
            $totalOutputJam4 += $d->o_jam_4;
            $totalOutputJam5 += $d->o_jam_5;
            $totalOutputJam6 += $d->o_jam_6;
            $totalOutputJam7 += $d->o_jam_7;
            $totalOutputJam8 += $d->o_jam_8;
            $totalOutputJam9 += $d->o_jam_9;
            $totalOutputJam10 += $d->o_jam_10;
            $totalOutputJam11 += $d->o_jam_11;
            $totalOutputJam12 += $d->o_jam_12;
            $totalOutputJam13 += $d->o_jam_13;

            $totalOutputAll += $d->tot_output;
        @endphp
        <tr>
            <td style="border: 1px solid black;">{{ $d->sewing_line }}</td>
            <td style="border: 1px solid black;">{{ $d->nm_chief }}</td>
            <td style="border: 1px solid black;">{{ $d->nm_leader }}</td>
            <td style="border: 1px solid black;">{{ $d->styleno_prod }}</td>
            <td style="border: 1px solid black;">{{ $d->smv }}</td>
            <td style="border: 1px solid black;">{{ $d->man_power }}</td>
            <td style="border: 1px solid black;">{{ $d->tot_days }}</td>
            <td style="border: 1px solid black;{{ $d->kemarin_2 < 85 ? 'color:#ff0000;' : 'color:#008000;' }}">{{ $d->kemarin_2 }}</td>
            <td style="border: 1px solid black;{{ $d->kemarin_1 < 85 ? 'color:#ff0000;' : 'color:#008000;' }}">{{ $d->kemarin_1 }}</td>
            <td style="border: 1px solid black;">{{ $d->jam_kerja }}</td>
            <td style="border: 1px solid black;">{{ $d->target_100 }}</td>
            <td style="border: 1px solid black;">{{ $d->target_effy }}</td>
            <td style="border: 1px solid black;">{{ $d->target_output_eff }}</td>
            <td style="border: 1px solid black;">{{ $d->set_target_perhari }}</td>
            <td style="border: 1px solid black;">{{ $d->plan_target_perjam }}</td>
            <td style="border: 1px solid black;">{{ $d->jam_kerja_act }}</td>
            <td style="border: 1px solid black;">{{ $d->o_jam_1 }}</td>
            <td style="border: 1px solid black;">{{ $d->o_jam_2 }}</td>
            <td style="border: 1px solid black;">{{ $d->o_jam_3 }}</td>
            <td style="border: 1px solid black;">{{ $d->o_jam_4 }}</td>
            <td style="border: 1px solid black;">{{ $d->o_jam_5 }}</td>
            <td style="border: 1px solid black;">{{ $d->o_jam_6 }}</td>
            <td style="border: 1px solid black;">{{ $d->o_jam_7 }}</td>
            <td style="border: 1px solid black;">{{ $d->o_jam_8 }}</td>
            <td style="border: 1px solid black;">{{ $d->o_jam_9 }}</td>
            <td style="border: 1px solid black;">{{ $d->o_jam_10 }}</td>
            <td style="border: 1px solid black;">{{ $d->o_jam_11 }}</td>
            <td style="border: 1px solid black;">{{ $d->o_jam_12 }}</td>
            <td style="border: 1px solid black;">{{ $d->o_jam_13 }}</td>
            <td style="border: 1px solid black;">{{ $d->tot_output }}</td>
            <td style="border: 1px solid black;{{ $d->eff_line < 85 ? 'color:#ff0000;' : 'color:#008000;' }}">{{ $d->eff_line }}</td>
            @if ($currentLine != $d->sewing_line)
                <td style="border: 1px solid black;{{ $d->eff_skrg < 85 ? 'color:#ff0000;' : 'color:#008000;' }}" rowspan="{{ $data->where("sewing_line", $d->sewing_line)->count() }}">{{ $d->eff_skrg }}</td>

                @php
                    $currentLine = $d->sewing_line;
                @endphp
            @endif
        </tr>
    @endforeach
    <tr>
        <th colspan="4" style="font-weight: bold;border: 1px solid black"> Total </th>
        <th style="font-weight: bold;border: 1px solid black"></th>
        <th style="font-weight: bold;border: 1px solid black">{{ array_sum($uniqueManPower) }}</th>
        <th colspan="4" style="font-weight: bold;border: 1px solid black"></th>
        <th style="font-weight: bold;border: 1px solid black">{{ $totalTarget100 }}</th>
        <th style="font-weight: bold;border: 1px solid black"></th>
        <th style="font-weight: bold;border: 1px solid black">{{ $totalTargetOutputEff }}</th>
        <th style="font-weight: bold;border: 1px solid black">{{ $totalTargetPerHari }}</th>
        <th style="font-weight: bold;border: 1px solid black"></th>
        <th style="font-weight: bold;border: 1px solid black"></th>
        <th style="font-weight: bold;border: 1px solid black">{{ $totalOutputJam1 }}</th>
        <th style="font-weight: bold;border: 1px solid black">{{ $totalOutputJam2 }}</th>
        <th style="font-weight: bold;border: 1px solid black">{{ $totalOutputJam3 }}</th>
        <th style="font-weight: bold;border: 1px solid black">{{ $totalOutputJam4 }}</th>
        <th style="font-weight: bold;border: 1px solid black">{{ $totalOutputJam5 }}</th>
        <th style="font-weight: bold;border: 1px solid black">{{ $totalOutputJam6 }}</th>
        <th style="font-weight: bold;border: 1px solid black">{{ $totalOutputJam7 }}</th>
        <th style="font-weight: bold;border: 1px solid black">{{ $totalOutputJam8 }}</th>
        <th style="font-weight: bold;border: 1px solid black">{{ $totalOutputJam9 }}</th>
        <th style="font-weight: bold;border: 1px solid black">{{ $totalOutputJam10 }}</th>
        <th style="font-weight: bold;border: 1px solid black">{{ $totalOutputJam11 }}</th>
        <th style="font-weight: bold;border: 1px solid black">{{ $totalOutputJam12 }}</th>
        <th style="font-weight: bold;border: 1px solid black">{{ $totalOutputJam13 }}</th>
        <th style="font-weight: bold;border: 1px solid black">{{ $totalOutputAll }}</th>
        <th colspan='2' style='border: 1px solid black;font-weight: bold;{{ $tot_eff_percent < 85 ? 'color:#ff0000;' : 'color:#008000;' }}'>{{ $tot_eff_percent }}</th>
    </tr>
</table>
