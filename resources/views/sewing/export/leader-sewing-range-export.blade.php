<table>
    @php
        function colorizeEfficiency($efficiency) {
            $color = "";
            switch (true) {
                case $efficiency < 75 :
                    $color = '#dc3545';
                    break;
                case $efficiency >= 75 && $efficiency <= 85 :
                    $color = '#f09900';
                    break;
                case $efficiency > 85 :
                    $color = '#28a745';
                    break;
            }

            return $color;
        }

        function colorizeRft($rft) {
            $color = "";
            switch (true) {
                case $rft < 97 :
                    $color = '#dc3545';
                    break;
                case $rft >= 97 && $rft < 98 :
                    $color = '#f09900';
                    break;
                case $rft >= 98 :
                    $color = '#28a745';
                    break;
            }

            return $color;
        }

        $lineGroups = $leaderPerformance->groupBy("sewing_line");

        $leaderGroup = $leaderGroups->map(function ($group) {
            return [
                'sewing_line' => $group->first()->sewing_line,// opposition_id is constant inside the same group, so just take the first or whatever.
                'data' => $group,
                'total_rft' => $group->sum('rft'),
                'total_output' => $group->sum('output'),
                'total_mins_prod' => $group->sum('mins_prod'),
                'total_mins_avail' => $group->sum('mins_avail'),
            ];
        });
    @endphp
    <tr>
        @foreach ($leaderGroup as $leader)
            <tr>
                <td></td>
            </tr>
        @endforeach
    </tr>
</table>
