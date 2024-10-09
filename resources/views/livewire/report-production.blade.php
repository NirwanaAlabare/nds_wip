<div>
    <div class="loading-container-fullscreen" wire:loading wire:target='selectedLine, date'>
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <div class="loading-container-fullscreen hidden" id="loadingReportProduction">
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <h3 class="my-3 text-center text-sb fw-bold">Production Report</h3>

    @if ($lines->count() < 1)
        <h5 class="text-center mt-5">
            Data line tidak ditemukan
        </h5>
    @else
        @if (!$loadingLine)
            <div class="d-flex justify-content-between flex-wrap gap-3" wire:poll.30000ms>
        @else
            <div class="d-flex justify-content-between flex-wrap gap-3">
        @endif
            <div class="d-flex justify-content-start align-items-center gap-3">
                <div wire:ignore>
                    <select class="select2 form-select form-select-sm" name="line" id="select-line">
                        @foreach ($lines as $line)
                            <option wire:key="{{ $loop->index }}" value="{{ $line->username }}">{{ $line->FullName }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="date" class="form-control form-control-sm" name="date" id='date' wire:model='date'>
                </div>
            </div>
            <div>
                <div class="d-flex justify-content-end gap-3">
                    <button class="btn btn-sm btn-success" onclick="exportExcel(this, 'production', '{{ $this->date }}', '{{ $this->selectedLine }}')">
                        Export <i class="fa-solid fa-file-excel"></i>
                    </button>
                    <button class="btn btn-sm btn-success" onclick="exportExcel(this, 'production-all', '{{ $this->date }}', '{{ $this->selectedLine }}')">
                        Export All <i class="fa-solid fa-file-excel"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="row table-responsive">
            <table class="table table-sm table-bordered align-middle mt-3">
                <thead>
                    <tr>
                        <th colspan="8" class="align-middle text-center">{{ ucfirst(str_replace("_", " ", $this->selectedLine != '' ? $this->selectedLine : '-')) }}</th>
                    </tr>
                    <tr>
                        <th class="text-center">Hour</th>
                        <th class="text-center">RFT</th>
                        <th class="text-center">Defect</th>
                        <th class="text-center">Rework</th>
                        <th class="text-center">Reject</th>
                        <th class="text-center">Actual</th>
                        <th class="text-center">Target</th>
                        <th class="text-center">Efficiency</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lineData = $selectedLine != '' ? $lines->firstWhere('username', $selectedLine)->masterPlans->where("cancel", 'N')->whereBetween('tgl_plan', [date('Y-m-d', strtotime('-1 days', strtotime($date))), $date]) : [];
                        $lineDataCurrent = $lineData->where('tgl_plan', $date);
                        // $manPower = count($lineData) > 0 ? $lineData->max('man_power') : 0;
                        // $jamKerja = count($lineData) > 0 ? round($lineData->sum('jam_kerja')) : 0;
                        $jamKerja = count($lineData) > 0 ? round(8) : 0;
                        $planTarget = count($lineData) > 0 ? $lineDataCurrent->sum('plan_target') : 0;
                        $hourTarget = count($lineData) > 0 ? ($lineDataCurrent->sum('jam_kerja') ? floor($planTarget/8) : 0) : 0;
                        $leftTarget = 0;
                        $summaryActual = 0;
                        $summaryTarget = 0;
                        $summaryMinsProd = 0;
                        $summaryMinsAvail = 0;

                        // Calculate realtime mins avail and real time target
                        if(strtotime(date('Y-m-d H:i:s')) <= strtotime($date.' 13:00:00')) {
                            $minsAvailNow = count($lineData) > 0 ? $lineDataCurrent->max('man_power') * floor(strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d').' 07:00:00'))/60 : 0;
                            $targetNow = count($lineData)  > 0 && $lineDataCurrent->avg('smv') > 0 ? floor($lineDataCurrent->max('man_power') * (floor(strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d').' 07:00:00'))/60) / $lineDataCurrent->avg('smv')) : 0;
                        } else {
                            $minsAvailNow = count($lineData) > 0 ? $lineDataCurrent->max('man_power') * floor(((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d').' 07:00:00'))/60)-60) : 0;
                            $targetNow = count($lineData)  > 0 && $lineDataCurrent->avg('smv') > 0 ? floor($lineDataCurrent->max('man_power') * floor(((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d').' 07:00:00'))/60)-60) / $lineDataCurrent->avg('smv')) : 0;
                        }
                    @endphp
                    @for ($i = 0; $i < count($hours); $i++)
                        @php
                            if ($i < 1) {
                                $timeFrom = $date.' 05:00:00';
                                $timeTo = $date.' '.$hours[$i];
                            }
                            else if ($i == count($hours)-1) {
                                $timeFrom = $date.' '.$hours[$i-1];
                                $timeTo = $date.' 23:59:59';
                            }
                            else {
                                $timeFrom = $date.' '.$hours[$i-1];
                                $timeTo = $date.' '.$hours[$i];
                            }

                            // Calculate output and mins prod
                            $jamKe = $i;
                            $totalActual = 0;
                            $totalRft = 0;
                            $totalDefect = 0;
                            $totalRework = 0;
                            $totalReject = 0;
                            $minsProd = 0;
                            $minsAvail = 0;

                            // Loop for calculating output data
                            foreach ($lineData as $line) {
                                $rft = $line->rfts->whereBetween('updated_at', [$timeFrom, $timeTo])->where('status', 'NORMAL')->count();
                                $totalRft += $rft;
                                $defect = $line->defects->whereBetween('updated_at', [$timeFrom, $timeTo])->where('defect_status', 'defect')->count();
                                $totalDefect += $defect;
                                $rework = $line->defects->whereBetween('updated_at', [$timeFrom, $timeTo])->where('defect_status', 'reworked')->count();
                                $totalRework += $rework;
                                $reject = $line->rejects->whereBetween('updated_at', [$timeFrom, $timeTo])->count();
                                $totalReject += $reject;
                                $totalActualThis = $rft + $rework;
                                $totalActual += $totalActualThis;
                                $minsProd += $totalActualThis * $line->smv;
                                $minsAvail += ($line->tgl_plan == $date ? ($line->man_power) : 0) * ($line->tgl_plan == $date ? ($line->jam_kerja) : 0) * 60;
                            }

                            // Sum output and mins prod
                            $summaryActual += $totalActual;
                            $summaryMinsProd += $minsProd;
                            $summaryTarget = $planTarget;

                            // Calculate mins avail summary and target summary
                            if (strtotime(date('Y-m-d H:i:s')) >= strtotime($date.' 16:00:00')) {
                                $summaryMinsAvail = $minsAvail;
                                $summaryTarget = $planTarget;
                            } else {
                                $summaryMinsAvail = $minsAvailNow;
                                $summaryTarget = $targetNow;
                            }

                            // Calculate efficiency
                            $cumulativeEfficiency = $summaryMinsAvail > 0 ? round((($summaryMinsProd/$summaryMinsAvail) * 100), 2) : 0 ;

                            // Calculate Hour Target
                            if ($date >= date('Y-m-d')) {
                                if ($jamKe > 0 && $hours[$jamKe-1] < date('H:i')) {
                                    if ($jamKe > 0 && $jamKe < $jamKerja) {
                                        $hourTarget = $jamKerja > $jamKe ? floor(($summaryTarget - ($summaryActual - $totalActual))/(round($jamKerja-$jamKe))) : 0;
                                        $leftTarget = $jamKerja > $jamKe ? ($summaryTarget - ($summaryActual - $totalActual))%(round(($jamKerja-$jamKe))) : 0;
                                    }
                                }
                            } else {
                                if ($jamKe > 0) {
                                    $hourTarget = $jamKerja > $jamKe ? floor(($summaryTarget - ($summaryActual - $totalActual))/(round($jamKerja-$jamKe))) : $hourTarget;
                                    $leftTarget = $jamKerja > $jamKe ? ($summaryTarget - ($summaryActual - $totalActual))%(round(($jamKerja-$jamKe))) : 0;
                                }
                            }
                        @endphp
                        <tr wire:key="{{ $i }}" class="{{ $date == date('Y-m-d') ? (date('H', strtotime($hours[$jamKe]." -1 hour")) == date('H') ? 'active' : '') : ''}}">
                            <td class="text-center">
                                {{ sprintf("%02d", (intval(substr($hours[$i],0,2))-1)).":00 - ".$hours[$i] }}
                            </td>
                            <td class="text-center fw-bold text-success">{{ num($totalRft) }}</td>
                            <td class="text-center fw-bold text-defect">{{ num($totalDefect) }}</td>
                            <td class="text-center fw-bold text-rework">{{ num($totalRework) }}</td>
                            <td class="text-center fw-bold text-reject">{{ num($totalReject) }}</td>
                            <td class="text-center fs-5 fw-bold text-sb">{{ num($totalActual) }}</td>
                            <td class="text-center fs-5 fw-bold text-sb">
                                @if($i == count($hours)-1)
                                    {{ 0 }}
                                @else
                                    @php
                                        if ($hourTarget < 0) {
                                            echo 0;
                                        } else {
                                            if ($leftTarget > 0) {
                                                echo num($hourTarget+1);
                                                $leftTarget--;
                                            } else {
                                                echo num($hourTarget);
                                            }
                                        }
                                    @endphp
                                @endif
                            </td>
                            <td class="text-center fs-5 fw-bold text-sb">
                                @if ($i == count($hours)-1)
                                    {{ $hourTarget > 0 ? ($totalActual > 0 ? '+'.(round(($totalActual/$hourTarget) * 100, 2)) : 0) : 0 }} %
                                @elseif ($i == count($hours)-2)
                                    {{ $hourTarget > 0 ? ($totalActual > 0 ? (round(($totalActual/$hourTarget+$leftTarget) * 100, 2)) : 0) : 0 }} %
                                @else
                                    {{ $hourTarget > 0 ? round(($totalActual/$hourTarget) * 100, 2) : 0 }} %
                                @endif
                            </td>
                        </tr>
                    @endfor
                </tbody>
                <tfoot>
                    <tr>
                        @php
                            $targetFromEfficiency = $summaryActual > 0 ? (($cumulativeEfficiency) > 0 ? floor($summaryActual / ($cumulativeEfficiency/100)) : 0) : 0;
                        @endphp
                        <td colspan="5" class="fs-5 fw-bold text-center">Summary</td>
                        <td class="fs-5 fw-bold text-center">{{ num($summaryActual) }}</td>
                        <td class="fs-5 fw-bold text-center">{{ num($summaryTarget) }}</td>
                        <td class="fs-5 fw-bold text-center">{{ num($cumulativeEfficiency) }} %</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="d-flex justify-content-between mt-3">
            <h5 class="text-center text-sb fw-bold">Top 5 Defects</h5>
            <button class="btn btn-sm btn-success" onclick="exportExcelDefect(this, '{{ $this->date }}', '{{ $this->selectedLine }}')">
                Export <i class="fa-solid fa-file-excel"></i>
            </button>
        </div>
        <div class="row table-responsive">
            <table class="table table-sm table-bordered mt-3">
                <thead>
                    <tr>
                        <th colspan="6" class="text-center">{{ ucfirst(str_replace("_"," ",$this->selectedLine != '' ? $this->selectedLine : '-')) }}</th>
                    </tr>
                    <tr>
                        <th class="text-center">No.</th>
                        <th colspan="3">Defect Types</th>
                        <th colspan="2">Defect Areas</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($defectTypes->count() < 1)
                        <tr>
                            <td colspan="6" class="text-center">Data not found</td>
                        </tr>
                    @else
                        @foreach ($defectTypes as $type)
                            @php
                                $defectAreasFiltered = $defectAreas->where("defect_type_id", $type->defect_type_id)->take(5);
                                $firstDefectAreasFiltered = $defectAreasFiltered->first();
                                $typeRowspan = $defectAreasFiltered->count();
                            @endphp
                            <tr>
                                <td {{ $typeRowspan > 1 ? 'rowspan='.$typeRowspan : '' }} class="text-center align-middle">{{ $loop->iteration }}</td>
                                <td {{ $typeRowspan > 1 ? 'rowspan='.$typeRowspan : '' }} class="align-middle">
                                    {{ $type->defectType->defect_type }}
                                </td>
                                <td {{ $typeRowspan > 1 ? 'rowspan='.$typeRowspan : '' }} class="text-center align-middle fw-bold">
                                    {{ num($type->defect_type_count) }}
                                </td>
                                <td {{ $typeRowspan > 1 ? 'rowspan='.$typeRowspan : '' }} class="text-center align-middle fw-bold">
                                    {{ num(($type->defect_type_count/$summaryActual)*100) }} %
                                </td>
                                <td class="align-middle">
                                    {{ $defectAreasFiltered->first()->defectArea->defect_area }}
                                </td>
                                <td class="text-center align-middle fw-bold">
                                    {{ num($defectAreasFiltered->first()->defect_area_count) }}
                                </td>
                            </tr>
                            @if ($defectAreasFiltered->count() > 1)
                                @foreach ($defectAreasFiltered as $area)
                                    @if ($loop->index > 0)
                                        <tr>
                                            <td class="align-middle">
                                                {{ $area->defectArea->defect_area }}
                                            </td>
                                            <td class="text-center align-middle fw-bold">
                                                {{ num($area->defect_area_count) }}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endif

                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        // $('#select-line').select2({
        //     theme: "bootstrap-5",
        //     width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
        //     placeholder: $( this ).data( 'placeholder' ),
        // });

        $('#select-line').on('change', function (e) {
            @this.set('loadingLine', true);

            var selectedLine = $('#select-line').select2("val");

            @this.set('selectedLine', selectedLine);

            Livewire.emit('loadingStart');
        });

        function exportExcel(elm, type, date, line) {
            @this.set('loadingLine', true);

            elm.setAttribute('disabled', 'true');
            elm.innerText = "";
            let loading = document.createElement('div');
            loading.classList.add('loading-small');
            elm.appendChild(loading);

            iziToast.info({
                title: 'Exporting...',
                message: 'Data sedang di export. Mohon tunggu...',
                position: 'topCenter'
            });

            if (type == 'production') {
                $.ajax({
                    url: "{{ url("/report/production/export") }}",
                    type: 'post',
                    data: { date : date, line : line },
                    xhrFields: { responseType : 'blob' },
                    success: function(res) {
                        @this.set('loadingLine', false);

                        elm.removeAttribute('disabled');
                        elm.innerText = "Export ";
                        let icon = document.createElement('i');
                        icon.classList.add('fa-solid');
                        icon.classList.add('fa-file-excel');
                        elm.appendChild(icon);

                        iziToast.success({
                            title: 'Success',
                            message: 'Success',
                            position: 'topCenter'
                        });

                        var blob = new Blob([res]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = line.replace("_", "-")+" "+date+" Production Report.xlsx";
                        link.click();
                    }, error: function (jqXHR) {
                        @this.set('loadingLine', false);

                        let res = jqXHR.responseJSON;
                        let message = '';
                        console.log(res.message);
                        for (let key in res.errors) {
                            message += res.errors[key]+' ';
                            document.getElementById(key).classList.add('is-invalid');
                        };
                        iziToast.error({
                            title: 'Error',
                            message: message,
                            position: 'topCenter'
                        });
                    }
                });
            } else if (type == 'production-all') {
                @this.set('loadingLine', true);

                $.ajax({
                    url: "{{ url("/report/production-all/export") }}",
                    type: 'post',
                    data: { date : date },
                    xhrFields: { responseType : 'blob' },
                    success: function(res) {
                        @this.set('loadingLine', false);

                        elm.removeAttribute('disabled');
                        elm.innerText = "Export All ";
                        let icon = document.createElement('i');
                        icon.classList.add('fa-solid');
                        icon.classList.add('fa-file-excel');
                        elm.appendChild(icon);

                        iziToast.success({
                            title: 'Success',
                            message: 'Success',
                            position: 'topCenter'
                        });

                        var blob = new Blob([res]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = date+" Production Report.xlsx";
                        link.click();
                    }, error: function (jqXHR) {
                        @this.set('loadingLine', false);

                        let res = jqXHR.responseJSON;
                        let message = '';
                        console.log(res.message);
                        for (let key in res.errors) {
                            message += res.errors[key]+' ';
                            document.getElementById(key).classList.add('is-invalid');
                        };
                        iziToast.error({
                            title: 'Error',
                            message: message,
                            position: 'topCenter'
                        });
                    }
                });
            }
        }

        function exportExcelDefect(elm, date, line) {
            console.log(date, line);

            @this.set('loadingLine', true);

            elm.setAttribute('disabled', 'true');
            elm.innerText = "";
            let loading = document.createElement('div');
            loading.classList.add('loading-small');
            elm.appendChild(loading);

            iziToast.info({
                title: 'Exporting...',
                message: 'Data sedang di export. Mohon tunggu...',
                position: 'topCenter'
            });

            $.ajax({
                url: "{{ url("/report/production/defect/export") }}",
                type: 'post',
                data: { date : date, line : line },
                xhrFields: { responseType : 'blob' },
                success: function(res) {
                    @this.set('loadingLine', false);

                    elm.removeAttribute('disabled');
                    elm.innerText = "Export ";
                    let icon = document.createElement('i');
                    icon.classList.add('fa-solid');
                    icon.classList.add('fa-file-excel');
                    elm.appendChild(icon);

                    iziToast.success({
                        title: 'Success',
                        message: 'Success',
                        position: 'topCenter'
                    });

                    var blob = new Blob([res]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = line.replace("_", "-")+" "+date+" Production Defect Report.xlsx";
                    link.click();
                }, error: function (jqXHR) {
                    @this.set('loadingLine', false);

                    let res = jqXHR.responseJSON;
                    let message = '';
                    console.log(res.message);
                    for (let key in res.errors) {
                        message += res.errors[key]+' ';
                        document.getElementById(key).classList.add('is-invalid');
                    };
                    iziToast.error({
                        title: 'Error',
                        message: message,
                        position: 'topCenter'
                    });
                }
            });
        }
    </script>
@endpush
