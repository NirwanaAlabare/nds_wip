<div>
    <div class="loading-container-fullscreen" wire:loading wire:target='selectedDefectType, dateFrom, dateTo'>
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <div class="loading-container-fullscreen hidden" id="loadingReportDefectInOut">
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    {{-- <h3 class="my-3 text-center text-sb fw-bold">Defect Report</h3> --}}

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
                <div class="d-flex justify-content-start align-items-center gap-3">
                    <input type="date" class="form-control form-control-sm" name="dateFrom" id='dateFrom' wire:model='dateFrom'>
                    <span>-</span>
                    <input type="date" class="form-control form-control-sm" name="dateTo" id='dateTo' wire:model='dateTo'>
                </div>
                <div wire:ignore>
                    <select class="select2 form-select" name="defect" id="select-defect">
                        @foreach ($defectTypes as $defect)
                            <option wire:key="{{ $loop->index }}" value="{{ $defect->Groupp }}">{{ $defect->FullName }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select class="form-select" name="type" id="select-type" wire:model="selectedOutputType">
                        <option value="qc">QC</option>
                        <option value="packing">Packing</option>
                    </select>
                </div>
            </div>
            <div>
                <div class="d-flex justify-content-end gap-3">
                    <select class="form-select form-select-sm w-75" name="pagination" id="pagination" wire:model="defectInOutShowPage">
                        <option value="10">Show 10</option>
                        <option value="25">Show 25</option>
                        <option value="50">Show 50</option>
                        <option value="100">Show 100</option>
                    </select>
                    <button class="w-25 btn btn-sm btn-success" onclick="exportExcel(this, '{{ $this->selectedDefectType }}', '{{ $this->selectedOutputType }}', '{{ $this->dateFrom }}', '{{ $this->dateTo }}')">
                        <i class="fa-solid fa-file-excel"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="row table-responsive">
            <table class="table table-sm table-bordered align-middle mt-3">
                <thead>
                    <tr>
                        <th colspan="10" class="text-center">{{ strtoupper(str_replace("_", "", $this->selectedDefectType)) }}</th>
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
                        <th class="text-center">RATE (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($defectInOutList->count() > 0)
                        @foreach ($defectInOutList as $defect)
                            <tr>
                                <td>{{ $defect->updated_at }}</td>
                                <td>{{ $defect->FullName }}</td>
                                <td>{{ strtoupper($defect->output_type) }}</td>
                                <td>{{ $defect->kpno }}</td>
                                <td>{{ $defect->styleno }}</td>
                                <td>{{ $defect->color }}</td>
                                <td>{{ $defect->size }}</td>
                                <td>{{ $defect->defect_type }}</td>
                                <td>{{ $defect->defect_qty }}</td>
                                <td>{{ round(($defect->defect_qty/($defectInOutTotalQty > 0 ? $defectInOutTotalQty : 1)) * 100, 2) }} %</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="10">Data Tidak ditemukan</td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="8" class="fs-5 fw-bold text-center">Summary</td>
                        <td class="fs-5 fw-bold text-center">{{ num($defectInOutTotalQty) }}</td>
                        <td class="fs-5 fw-bold text-center"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        {{ $defectInOutList->links() }}
    @endif
</div>

@push('scripts')
    <script>
        // $('#select-line').select2({
        //     theme: "bootstrap-5",
        //     width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
        //     placeholder: $( this ).data( 'placeholder' ),
        // });

        $('#select-defect').on('change', function (e) {
            @this.set('loading', true);

            var selectedDefectType = $('#select-defect').select2("val");

            @this.set('selectedDefectType', selectedDefectType);

            Livewire.emit('loadingStart');
        });

        function exportExcel(elm, type, outputType, dateFrom, dateTo) {
            @this.set('loading', true);

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
                url: "{{ url("/report/defect-in-out/export") }}",
                type: 'post',
                data: { dateFrom : dateFrom, dateTo : dateTo, type : type, outputType : outputType },
                xhrFields: { responseType : 'blob' },
                success: function(res) {
                    @this.set('loading', false);

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
                    link.download = dateFrom+" - "+dateTo+" "+type+" Defect Report.xlsx";
                    link.click();
                }, error: function (jqXHR) {
                    @this.set('loading', false);

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

        // function exportExcelDefect(elm, date, line) {
        //     console.log(date, line);

        //     @this.set('loadingLine', true);

        //     elm.setAttribute('disabled', 'true');
        //     elm.innerText = "";
        //     let loading = document.createElement('div');
        //     loading.classList.add('loading-small');
        //     elm.appendChild(loading);

        //     iziToast.info({
        //         title: 'Exporting...',
        //         message: 'Data sedang di export. Mohon tunggu...',
        //         position: 'topCenter'
        //     });

        //     $.ajax({
        //         url: "{{ url("/report/production/defect/export") }}",
        //         type: 'post',
        //         data: { date : date, line : line },
        //         xhrFields: { responseType : 'blob' },
        //         success: function(res) {
        //             @this.set('loadingLine', false);

        //             elm.removeAttribute('disabled');
        //             elm.innerText = "Export ";
        //             let icon = document.createElement('i');
        //             icon.classList.add('fa-solid');
        //             icon.classList.add('fa-file-excel');
        //             elm.appendChild(icon);

        //             iziToast.success({
        //                 title: 'Success',
        //                 message: 'Success',
        //                 position: 'topCenter'
        //             });

        //             var blob = new Blob([res]);
        //             var link = document.createElement('a');
        //             link.href = window.URL.createObjectURL(blob);
        //             link.download = line.replace("_", "-")+" "+date+" Production Defect Report.xlsx";
        //             link.click();
        //         }, error: function (jqXHR) {
        //             @this.set('loadingLine', false);

        //             let res = jqXHR.responseJSON;
        //             let message = '';
        //             console.log(res.message);
        //             for (let key in res.errors) {
        //                 message += res.errors[key]+' ';
        //                 document.getElementById(key).classList.add('is-invalid');
        //             };
        //             iziToast.error({
        //                 title: 'Error',
        //                 message: message,
        //                 position: 'topCenter'
        //             });
        //         }
        //     });
        // }
    </script>
@endpush
