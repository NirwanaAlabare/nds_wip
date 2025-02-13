<div>
    <div class="loading-container-fullscreen" wire:loading wire:target='search, date, group, filter'>
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <h3 class="my-3 text-center text-sb fw-bold">Report Line User</h3>
    <div class="d-flex justify-content-between gap-3">
        <div class="d-flex justify-content-start align-items-center gap-3">
            <div>
                <input type="text" class="form-control form-control-sm" name="search" id='search' wire:model.lazy='search' placeholder="Search...">
            </div>
            <div>
                <input type="date" class="form-control form-control-sm" name="date" id='date' wire:model.lazy='date'>
            </div>
        </div>
    </div>

    <div class="row table-responsive">
        <table class="table table-sm table-bordered mt-3">
            <thead>
                <tr>
                    <th class="text-center">Line</th>
                    <th class="text-center">User</th>
                    <th class="text-center">Output</th>
                    <th class="text-center">Last Input</th>
                    <th class="text-center">Total Output</th>
                    <th class="text-center">Latest Input</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $thisLine = "";
                    $thisLineActual = 0;
                    $thisLastInput = 0;
                    $summaryActual = 0;
                    $lastInput = 0;
                @endphp
                @if ($lines->count() < 1)
                    <tr>
                        <td class="text-center" colspan="3">
                            Data not found
                        </td>
                    </tr>
                @else
                    {{-- Total Line Loop --}}
                    @foreach ($lines as $line)
                        @php
                            $thisLineData =  $lines->where('line_id', $line->line_id);
                            $thisLastInput < $line->latest_output && $thisLastInput = $line->latest_output;

                            $lastInput < $line->latest_output && $lastInput = $line->latest_output;

                            $summaryActual += $line->total_actual;
                        @endphp
                        <tr wire:key="{{ $loop->index }}">
                            @if ($line->line_id != $thisLine)
                                <td rowspan="{{ $thisLineData->count() }}" class="text-center align-middle">
                                    <a class="text-sb" href="http://10.10.5.62:8000/dashboard-wip/line/dashboard1/{{ $line->username }}" target="_blank">
                                        {{ ucfirst(str_replace("_", " ", $line->username)) }}
                                    </a>
                                </td>
                            @endif

                            <td>
                                {{ str_replace("_", " ", $line->name) }}
                            </td>
                            <td class="fs-5 text-center text-sb fw-bold">
                                {{ $line->total_actual }}
                            </td>
                            <td class="text-center">
                                {{ $line->latest_output }}
                            </td>

                            @if ($line->line_id != $thisLine)
                                <td rowspan="{{ $thisLineData->count() }}" class="fs-5 fw-bold text-sb text-center align-middle">
                                    {{ $thisLineData->sum('total_actual') }}
                                </td>
                                <td rowspan="{{ $thisLineData->count() }}" class="text-center align-middle">
                                    {{ $thisLastInput }}
                                </td>
                            @endif
                        </tr>
                        @php
                            $thisLine = $line->line_id;
                            $thisLastInput = null;
                        @endphp
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-center">Summary</th>
                    <th class="fs-5 text-center">{{ num($summaryActual) }}</th>
                    <th class="text-center">{{ $lastInput }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
