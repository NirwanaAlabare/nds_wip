<!DOCTYPE html>
<html lang="en">

<table >
    @php
        $currentPanel = "";

        $totalTargetShift1 = 0;
        $totalPendingShift1 = 0;
        $totalTargetShift2 = 0;
        $totalPendingShift2 = 0;
        $totalTotalTarget = 0;
        $totalBalanceToTarget = 0;
        $totalTotalTargetSpr = 0;
        $totalOutputSpr = 0;
        $totalBalanceSpr = 0;
        $totalBalanceToSpr = 0;
        $totalTotalTargetCut = 0;
        $totalOutputCut = 0;
        $totalBalanceCut = 0;
        $totalConsWs = 0;
        $totalNeed = 0;
        $totalSisaKemarin = 0;
        $totalIn = 0;
        $totalTotalIn = 0;
        $totalBalance = 0;
        $totalUseAct = 0;
        $totalSisa = 0;
        $totalUnit = 0;
    @endphp
    @foreach ($reportCutting as $cutting)
        @if ($currentPanel != $cutting->panel)
            @if ($loop->index != 0)
                <tr></tr>
                <tr></tr>
            @endif
            <tr>
                <td colspan="2" style="font-weight: 800;background: #ffd966;">PLANNING</td>
                <td colspan="2" style="font-weight: 800;background: #ffd966;">{{ $cutting->panel }}</td>
                <td colspan="7" style="font-weight: 800;background: #ffd966;">{{ localeDateFormat($date) }}</td>
                <td colspan="4" style="font-weight: 800;background: #f8cbad;text-align: center;">CUTTING</td>
                {{-- <td colspan="4" style="font-weight: 800;background: #bdd7ee;text-align: center;">SPREADING</td> --}}
                <td colspan="9" style="font-weight: 800;background: #ccccff;text-align: center;">MATERIAL</td>
            </tr>
            <tr>
                <th style="font-weight: 800;background: #ffd966;">Meja</th>
                <th style="font-weight: 800;background: #ffd966;">Buyer</th>
                <th style="font-weight: 800;background: #ffd966;">No. WS</th>
                <th style="font-weight: 800;background: #ffd966;">Style</th>
                <th style="font-weight: 800;background: #ffd966;">Color</th>
                <th style="font-weight: 800;background: #ffd966;">Ket.</th>
                <th style="font-weight: 800;background: #ffd966;">Target Shift 1</th>
                <th style="font-weight: 800;background: #ffd966;">Pending Shift 1</th>
                <th style="font-weight: 800;background: #ffd966;">Target Shift 2</th>
                <th style="font-weight: 800;background: #ffd966;">Pending Shift 2</th>
                <th style="font-weight: 800;background: #ffd966;">Total Target</th>
                {{-- <th style="font-weight: 800;background: #bdd7ee;">Balance To Target</th>
                <th style="font-weight: 800;background: #bdd7ee;">Total Target</th>
                <th style="font-weight: 800;background: #bdd7ee;">Output Spreading</th>
                <th style="font-weight: 800;background: #bdd7ee;">Balance</th> --}}
                <th style="font-weight: 800;background: #f8cbad;">Balance To Spreading</th>
                <th style="font-weight: 800;background: #f8cbad;">Total Target</th>
                <th style="font-weight: 800;background: #f8cbad;">Output Cutting</th>
                <th style="font-weight: 800;background: #f8cbad;">Balance</th>
                <th style="font-weight: 800;background: #ccccff;">Cons</th>
                <th style="font-weight: 800;background: #ccccff;">Need</th>
                <th style="font-weight: 800;background: #ccccff;">Sisa Kemarin</th>
                <th style="font-weight: 800;background: #ccccff;">In</th>
                <th style="font-weight: 800;background: #ccccff;">Total In</th>
                <th style="font-weight: 800;background: #ccccff;">Balance</th>
                <th style="font-weight: 800;background: #ccccff;">Use Act</th>
                <th style="font-weight: 800;background: #ccccff;">Sisa</th>
                <th style="font-weight: 800;background: #ccccff;">Unit</th>
            </tr>

            @php
                $currentPanel = $cutting->panel;

                $totalTargetShift1 = 0;
                $totalPendingShift1 = 0;
                $totalTargetShift2 = 0;
                $totalPendingShift2 = 0;
                $totalTotalTarget = 0;
                $totalBalanceToTarget = 0;
                $totalTotalTargetSpr = 0;
                $totalOutputSpr = 0;
                $totalBalanceSpr = 0;
                $totalBalanceToSpr = 0;
                $totalTotalTargetCut = 0;
                $totalOutputCut = 0;
                $totalBalanceCut = 0;
                $totalConsWs = 0;
                $totalNeed = 0;
                $totalSisaKemarin = 0;
                $totalIn = 0;
                $totalTotalIn = 0;
                $totalBalance = 0;
                $totalUseAct = 0;
                $totalSisa = 0;
                $totalUnit = 0;
            @endphp
        @endif
        <tr>
            <td data-format="@" style="vertical-align: top;">{{ $cutting->meja ? str_replace('meja ', '', $cutting->meja) : '-'  }}</td>
            <td style="vertical-align: top;">{{ $cutting->buyer ? $cutting->buyer : '-'  }}</td>
            <td style="vertical-align: top;">{{ $cutting->act_costing_ws ? $cutting->act_costing_ws : '-'  }}</td>
            <td data-format="@" style="vertical-align: top;">{{ $cutting->style ? $cutting->style : '-'  }}</td>
            <td style="vertical-align: top;">{{ $cutting->color ? $cutting->color : '-'  }}</td>
            <td style="vertical-align: top;">{{ $cutting->notes ? $cutting->notes : '-'  }}</td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;">{{ $cutting->marker_gelar ? $cutting->marker_gelar : '-'  }}</td>
            <td style="vertical-align: top;"></td>
            {{-- <td style="vertical-align: top;">{{ $cutting->marker_gelar ? $cutting->marker_gelar : '-'  }}</td>
            <td style="vertical-align: top;">{{ $cutting->spreading_gelar ? $cutting->spreading_gelar : '-'  }}</td>
            <td style="vertical-align: top;">{{ ($cutting->spreading_gelar ? $cutting->spreading_gelar : 0) - ($cutting->marker_gelar ? $cutting->marker_gelar : 0) }}</td>
            <td style="vertical-align: top;"></td> --}}
            <td style="vertical-align: top;">{{ $cutting->marker_gelar ? $cutting->marker_gelar : '-'  }}</td>
            <td style="vertical-align: top;">{{ $cutting->form_gelar ? $cutting->form_gelar : '-'  }}</td>
            <td style="vertical-align: top;">{{ ($cutting->form_gelar ? $cutting->form_gelar : 0) - ($cutting->marker_gelar ? $cutting->marker_gelar : 0) }}</td>
            <td style="vertical-align: top;">{{ ($cutting->cons_ws ? $cutting->cons_ws : 0) }}</td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;">{{ ($cutting->form_gelar ? $cutting->form_gelar : 0) * ($cutting->cons_ws ? $cutting->cons_ws : 0) }}</td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;">{{ ($cutting->unit ? $cutting->unit : '-') }}</td>
        </tr>
        @php
            $totalTargetShift1 += 0;
            $totalPendingShift1 += 0;
            $totalTargetShift2 += 0;
            $totalPendingShift2 += 0;
            $totalTotalTarget +=  $cutting->marker_gelar ? $cutting->marker_gelar : 0;
            $totalBalanceToTarget += 0;
            $totalTotalTargetSpr +=  $cutting->marker_gelar ? $cutting->marker_gelar : 0;
            $totalOutputSpr += $cutting->spreading_gelar ? $cutting->spreading_gelar : 0;
            $totalBalanceSpr += ($cutting->spreading_gelar ? $cutting->spreading_gelar : 0) - ($cutting->marker_gelar ? $cutting->marker_gelar : 0);
            $totalBalanceToSpr += 0;
            $totalTotalTargetCut += $cutting->spreading_gelar ? $cutting->spreading_gelar : 0;
            $totalOutputCut += $cutting->form_gelar ? $cutting->form_gelar : 0;
            $totalBalanceCut += ($cutting->form_gelar ? $cutting->form_gelar : 0) - ($cutting->spreading_gelar ? $cutting->spreading_gelar : 0);
            $totalConsWs += $cutting->cons_ws ? $cutting->cons_ws : 0;
            $totalNeed += 0;
            $totalSisaKemarin += 0;
            $totalIn += 0;
            $totalTotalIn += 0;
            $totalBalance += 0;
            $totalUseAct += ($cutting->spreading_gelar ? $cutting->spreading_gelar : 0) * ($cutting->cons_ws ? $cutting->cons_ws : 0);
            $totalSisa += 0;
            $totalUnit = ($cutting->unit ? $cutting->unit : '-');
        @endphp
        @if (($loop->index == ($reportCutting->count()-1)) || ($loop->index <= ($reportCutting->count()-1) && $currentPanel != $reportCutting->slice($loop->index+1, 1)->first()->panel))
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>TOTAL</td>
                <td>{{ $totalTargetShift1 }}</td>
                <td>{{ $totalPendingShift1 }}</td>
                <td>{{ $totalTargetShift2 }}</td>
                <td>{{ $totalPendingShift2 }}</td>
                <td>{{ $totalTotalTarget }}</td>
                <td>{{ $totalBalanceToTarget }}</td>
                {{-- <td>{{ $totalTotalTargetSpr }}</td>
                <td>{{ $totalOutputSpr }}</td>
                <td style="{{ $totalBalanceSpr > 0 ? 'background: #c4edc2; color: #199e08;' : ($totalBalanceSpr == 0 ? 'background: #dde7f7;color: #2683ed;' : 'background: #f7dedd;color: #ed2626;') }}">{{ $totalBalanceSpr }}</td>
                <td>{{ $totalBalanceToSpr }}</td> --}}
                <td>{{ $totalTotalTargetCut }}</td>
                <td>{{ $totalOutputCut }}</td>
                <td style="{{ $totalBalanceCut > 0 ? 'background: #c4edc2; color: #199e08;' : ($totalBalanceCut == 0 ? 'background: #dde7f7;color: #2683ed;' : 'background: #f7dedd;color: #ed2626;') }}">{{ $totalBalanceCut }}</td>
                <td>{{ $totalConsWs }}</td>
                <td>{{ $totalNeed }}</td>
                <td>{{ $totalSisaKemarin }}</td>
                <td>{{ $totalIn }}</td>
                <td>{{ $totalTotalIn }}</td>
                <td>{{ $totalBalance }}</td>
                <td>{{ $totalUseAct }}</td>
                <td>{{ $totalSisa }}</td>
                <td>{{ $totalUnit }}</td>
            </tr>
        @endif
    @endforeach
</table>

</html>
