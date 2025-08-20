<div>
    <div class="loading-container-fullscreen" wire:loading wire:target='search, date, group, filter'>
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <div wire:poll>
        <div class="d-flex">
            <div class="mb-3">
                <label class="mb-1">Tanggal</label>
                <input type="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" wire:model='date'>
            </div>
        </div>
        <table class="table table-bordered" wire:poll>
            <thead>
                <tr>
                    <th>Line</th>
                    <th>WS Number</th>
                    <th>Color</th>
                    <th>SMV</th>
                    <th>Jam Kerja</th>
                    <th>Man Power</th>
                    <th>Plan Target</th>
                    <th>Target Efficiency</th>
                    <th>Total Jam</th>
                    <th>Total Target</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $currentLine = "";
                @endphp
                @if ($masterPlan->count() > 0)
                    @foreach ($masterPlan as $mp)
                        @php
                            $thisLineRow = $lineRow->where("sewing_line", $mp->sewing_line)->first();
                        @endphp
                        <tr>
                            @if ($currentLine != $mp->sewing_line)
                                <td class="text-center align-middle" rowspan="{{ $thisLineRow->total_row }}">
                                    <a href="{{ route('master-plan-detail')."/".$mp->sewing_line."/".$date }}" target="_blank">{{ ucfirst(str_replace("_", " ", $mp->sewing_line)) }}</a>
                                </td>
                            @endif
                            <td>{{ $mp->actCosting->kpno }}</td>
                            <td>{{ $mp->color }}</td>
                            <td>{{ curr($mp->smv) }}</td>
                            <td>{{ curr($mp->jam_kerja) }}</td>
                            <td>{{ num($mp->man_power) }}</td>
                            <td>{{ num($mp->plan_target) }}</td>
                            <td>{{ curr($mp->target_effy) }} %</td>

                            @if ($currentLine != $mp->sewing_line)
                                <td class="text-center align-middle fw-bold {{ round($thisLineRow->total_jam) > 8 || round($thisLineRow->total_jam) < 8 ? "text-danger" : "text-success" }}" rowspan="{{ $thisLineRow->total_row }}">{{ $thisLineRow->total_jam }}</td>
                                <td class="text-center align-middle fw-bold text-sb" rowspan="{{ $thisLineRow->total_row }}">{{ $thisLineRow->total_target }}</td>
                            @endif
                        </tr>
                        @php
                            $currentLine = $mp->sewing_line;
                        @endphp
                    @endforeach
                @else
                    <tr>
                        <td colspan="9" class="text-center">Data tidak ditemukan</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
