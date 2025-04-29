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
    <h3 class="my-3 text-center text-sb fw-bold">Defect Report</h3>

    @if ($defectTypes->count() < 1)
        <h5 class="text-center mt-5">
            Data defect tidak ditemukan
        </h5>
    @else
        @if (!$loadingLine)
            <div class="d-flex justify-content-between flex-wrap gap-3" wire:poll.30000ms>
        @else
            <div class="d-flex justify-content-between flex-wrap gap-3">
        @endif
            <div class="d-flex justify-content-start align-items-center gap-3">
                <div wire:ignore>
                    <select class="select2 form-select form-select-sm" name="defect" id="select-defect">
                        @foreach ($defectTypes as $defect)
                            <option wire:key="{{ $loop->index }}" value="{{ $defect->username }}">{{ $defect->FullName }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="date" class="form-control form-control-sm" name="date" id='date' wire:model='date'>
                </div>
            </div>
            <div>
                <div class="d-flex justify-content-end gap-3">
                    <button class="btn btn-sm btn-success" onclick="exportExcel(this, 'defect', '{{ $this->date }}', '{{ $this->selectedDefectType }}')">
                        Export <i class="fa-solid fa-file-excel"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="row table-responsive">
            <table class="table ble-bordered align-middle mt-3">
                <thead>
                    <tr>
                        <th colspan="9">{{ $this->selectedDefectType }}</th>
                    </tr>
                    <tr>
                        <th class="text-center">DATE IN</th>
                        <th class="text-center">LINE</th>
                        <th class="text-center">DEPARTMENT</th>
                        <th class="text-center">WS</th>
                        <th class="text-center">STYLE</th>
                        <th class="text-center">COLOR</th>
                        <th class="text-center">SIZE</th>
                        <th class="text-center">TYPE</th>
                        <th class="text-center">QTY</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($defectTypes->where("Groupp", $this->selectedDefectType)->defects as $defect)
                        <tr>
                            <td>{{ $defect->updated_at }}</td>
                            <td>{{ $defect->line }}</td>
                            <td>{{ $defect->type }}</td>
                            <td>{{ $defect->act_costing_ws }}</td>
                            <td>{{ $defect->style }}</td>
                            <td>{{ $defect->color }}</td>
                            <td>{{ $defect->size }}</td>
                            <td>{{ $defect->type }}</td>
                            <td>{{ $defect->qty }}</td>
                        </tr>
                    @endforeach
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
            <table class="table ble-bordered mt-3">
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
