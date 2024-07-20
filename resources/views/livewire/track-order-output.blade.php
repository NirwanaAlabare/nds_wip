<div>
    <div class="loading-container-fullscreen" wire:loading wire:target='selectedSupplier, selectedOrder, groupBy, colorFilter, lineFilter, sizeFilter, clearFilter, outputType'>
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <div class="loading-container-fullscreen hidden" id="loadingOrderOutput">
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <div class="row justify-content-between align-items-end">
        <div class="col-12 col-lg-6 col-xl-4">
            <div class="d-flex align-items-center justify-content-start gap-3 mb-3">
                <div wire:ignore>
                    <input type="date" class="form-control form-control-sm" id="dateFrom">
                </div>
                <div> - </div>
                <div wire:ignore>
                    <input type="date" class="form-control form-control-sm" id="dateTo">
                </div>
                <span class="badge bg-sb text-light">{{ strtoupper(str_replace("_", "", ($outputType ? $outputType : "SEWING"))) }}</span>
            </div>
        </div>
        <div class="col-12 col-lg-6 col-xl-8">
            <div class="d-flex align-items-end justify-content-end gap-3 mb-3">
                <button class="btn btn-sb btn-sm" data-bs-toggle="modal" data-bs-target="#filter-modal"><i class="fa fa-filter"></i></button>
                <div wire:ignore>
                    <select class="form-select form-select-sm" name="supplier" id="supplier">
                        <option value="">Pilih Supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div wire:ignore>
                    <select class="form-select form-select-sm" name="order" id="order">
                        <option value="">Pilih WS</option>
                        @foreach ($orders as $order)
                            <option value="{{ $order->id_ws }}">{{ $order->no_ws }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button class="btn btn-success" onclick="exportExcel(this, {{ $selectedOrder }})">
                        <i class="fa fa-file-excel fa-sm"></i>
                        Export
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        @if (!$loadingOrderOutput)
            <table class="table table-bordered">
        @else
            <table class="table table-bordered" wire:poll.30000ms>
        @endif
            <tbody>
                <tr>
                    <th>No. WS</th>
                    <th>Style</th>
                    <th>Color</th>
                    <th>Line</th>
                    @if ($groupBy == 'size')
                        <th>Size</th>
                    @endif
                    <?php
                        if ( $selectedOrder && $dailyOrderOutputs && $dailyOrderOutputs->count() > 0 ) {
                            foreach ($dailyOrderOutputs->sortBy("tanggal")->groupBy("tanggal") as $dailyDate) {
                                ?>
                                    <th>{{ date_format(date_create($dailyDate->first()->tanggal), "d-m-Y") }}</th>
                                <?php
                            }

                    ?>
                            <th class="text-center">TOTAL</th>
                        </tr>
                    <?php

                            $currentWs = null;
                            $currentStyle = null;
                            $currentColor = null;
                            $currentLine = null;
                            $currentSize = null;

                            $dateOutputs = collect();
                            $totalOutput = null;

                            foreach ($dailyOrderGroup as $dailyGroup) {
                                ?>
                                    <tr>
                                        @if ($dailyGroup->ws != $currentWs)
                                            <td class="text-nowrap" rowspan="{{ $dailyOrderGroup->where('ws', $dailyGroup->ws)->count(); }}">{{ $dailyGroup->ws }}</td>

                                            @php
                                                $currentWs = $dailyGroup->ws;
                                                $currentStyle = null;
                                                $currentColor = null;
                                                $currentLine = null;
                                            @endphp
                                        @endif
                                        @if ($dailyGroup->ws == $currentWs && $dailyGroup->style != $currentStyle)
                                            <td class="text-nowrap" rowspan="{{ $dailyOrderGroup->where('ws', $dailyGroup->ws)->where('style', $dailyGroup->style)->count(); }}">{{ $dailyGroup->style }}</td>

                                            @php
                                                $currentStyle = $dailyGroup->style;
                                                $currentColor = null;
                                                $currentLine = null;
                                            @endphp
                                        @endif
                                        @if ($dailyGroup->ws == $currentWs && $dailyGroup->style == $currentStyle && $dailyGroup->color != $currentColor)
                                            <td class="text-nowrap" rowspan="{{ $dailyOrderGroup->where('ws', $dailyGroup->ws)->where('style', $dailyGroup->style)->where('color', $dailyGroup->color)->count(); }}">{{ $dailyGroup->color }}</td>

                                            @php
                                                $currentColor = $dailyGroup->color;
                                                $currentLine = null;
                                            @endphp
                                        @endif
                                        @if ($dailyGroup->ws == $currentWs && $dailyGroup->style == $currentStyle && $dailyGroup->color == $currentColor && $dailyGroup->sewing_line != $currentLine)
                                            <td class="text-nowrap" rowspan="{{ $dailyOrderGroup->where('ws', $dailyGroup->ws)->where('style', $dailyGroup->style)->where('color', $dailyGroup->color)->where('sewing_line', $dailyGroup->sewing_line)->count(); }}">{{ strtoupper(str_replace('_', ' ', $dailyGroup->sewing_line)) }}</td>

                                            @php
                                                $currentLine = $dailyGroup->sewing_line;
                                            @endphp
                                        @endif
                                        @if ($groupBy == "size")
                                            <td class="text-nowrap">{{ $dailyGroup->size }}</td>
                                        @endif

                                        @php
                                            $thisRowOutput = 0;
                                        @endphp
                                        @foreach ($dailyOrderOutputs->sortBy("tanggal")->groupBy("tanggal") as $dailyDate)
                                            @php
                                                $thisOutput = 0;

                                                if ($groupBy == 'size') {
                                                    $thisOutput = $dailyOrderOutputs->where('ws', $dailyGroup->ws)->where('style', $dailyGroup->style)->where('color', $dailyGroup->color)->where('sewing_line', $dailyGroup->sewing_line)->where('tanggal', $dailyDate->first()->tanggal)->where('size', $dailyGroup->size)->sum("output");
                                                } else {
                                                    $thisOutput = $dailyOrderOutputs->where('ws', $dailyGroup->ws)->where('style', $dailyGroup->style)->where('color', $dailyGroup->color)->where('sewing_line', $dailyGroup->sewing_line)->where('tanggal', $dailyDate->first()->tanggal)->sum("output");
                                                }

                                                if (isset($dateOutputs[$dailyDate->first()->tanggal])) {
                                                    $dateOutputs[$dailyDate->first()->tanggal] += $thisOutput;
                                                } else {
                                                    $dateOutputs->put($dailyDate->first()->tanggal, $thisOutput);
                                                }
                                                $thisRowOutput += $thisOutput;
                                            @endphp

                                            <td class="text-end text-nowrap">
                                                {{ num($thisOutput) }}
                                            </td>
                                        @endforeach
                                        <td class="fw-bold text-end text-nowrap fs-5">
                                            {{ num($thisRowOutput) }}
                                        </td>
                                        @php
                                            $totalOutput += $thisRowOutput;
                                        @endphp
                                    </tr>
                                <?php
                            }
                        } else {
                            ?>
                                <tr>
                                    <td colspan="{{ $groupBy == "size" ? '7' : '4' }}">Data tidak ditemukan</td>
                                </tr>
                            <?php
                        }
                    ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="{{ $groupBy == "size" ? '5' : '4' }}" class="text-end">
                        TOTAL
                    </th>
                    @if ($selectedOrder && $dailyOrderOutputs && $dailyOrderOutputs->count() > 0)
                        @foreach ($dateOutputs as $dateOutput)
                            <td class="fw-bold text-end text-nowrap fs-5">
                                {{ num($dateOutput) }}
                            </td>
                        @endforeach
                        <td class="fw-bold text-end text-nowrap fs-5">{{ num($totalOutput) }}</td>
                    @endif
                </tr>
            </tfoot>
        </table>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="filter-modal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="filterModalLabel">Filter Modal</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label>Tipe Output</label>
                                <select class="form-select form-select-sm" name="output-type" id="output-type" wire:model="outputType">
                                    <option value="">QC</option>
                                    <option value="_finish">Finish</option>
                                    <option value="_packing">Packing</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Group By</label>
                                <select class="form-select form-select-sm" name="group-by" id="group-by" wire:model="groupBy">
                                    <option value="">-</option>
                                    <option value="size">Size</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Color</label>
                                <select class="form-select form-select-sm" name="color" id="color" wire:model="colorFilter">
                                    <option value="">Pilih Color</option>
                                    @if ($dailyOrderGroup)
                                        @foreach ($dailyOrderGroup->groupBy('color') as $color)
                                            <option value="{{ $color->first()->color }}">{{ $color->first()->color }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Line</label>
                                <select class="form-select form-select-sm" name="line" id="line" wire:model="lineFilter">
                                    <option value="">Pilih Line</option>
                                    @if ($dailyOrderGroup)
                                        @foreach ($dailyOrderGroup->groupBy('sewing_line') as $color)
                                            <option value="{{ $color->first()->sewing_line }}">{{ $color->first()->sewing_line }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            @if ($groupBy == "size")
                                <div class="mb-3">
                                    <label>Size</label>
                                    <select class="form-select form-select-sm" name="size" id="size" wire:model="sizeFilter">
                                        <option value="">Pilih Size</option>
                                        @if ($dailyOrderGroup)
                                            @foreach ($dailyOrderGroup->groupBy('size') as $size)
                                                <option value="{{ $size->first()->size }}">{{ $size->first()->size }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa-solid fa-times"></i> Tutup</button>
                    <button type="button" class="btn btn-sb" wire:click='clearFilter()'><i class="fa-solid fa-broom"></i> Bersihkan</button>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // new DataTable('#trackdatatable', {
            //     fixedColumns: {
            //         start: 1,
            //         end: 1
            //     },
            //     paging: true,
            //     scrollX: true
            // });

            // select2
            $('#supplier').select2({
                theme: "bootstrap4",
            });

            $('#order').select2({
                theme: "bootstrap4",
            });

            $('#dateFrom').on('change', async function (e) {
                @this.set('dateFromFilter', this.value);

                updateSupplierList($('#dateFrom').val(), $('#dateTo').val());
            });

            $('#dateTo').on('change', async function (e) {
                @this.set('dateToFilter', this.value);

                updateSupplierList($('#dateFrom').val(), $('#dateTo').val());
            });

            $('#supplier').on('change', async function (e) {
                @this.set('selectedSupplier', this.value);

                updateWsList($('#dateFrom').val(), $('#dateTo').val(), this.value);
            });

            $('#order').on('change', async function (e) {
                @this.set('loadingOrderOutput', true);

                @this.set('selectedOrder', this.value);

                Livewire.emit('loadingStart');
            });
        });

        async function updateSupplierList(dateFrom, dateTo) {
            document.getElementById("loadingOrderOutput").classList.remove("hidden");

            let currentValue = await $("#supplier").val();

            return $.ajax({
                url: 'track-order-output',
                type: 'get',
                data: {
                    type : "supplier",
                    dateFrom : dateFrom,
                    dateTo : dateTo,
                },
                success: async function(res) {
                    document.getElementById("loadingOrderOutput").classList.add("hidden");

                    // Clear options
                    $("#supplier").html("");

                    $('#supplier').append(new Option("Pilih Value", "", true, true));

                    let count = 0;
                    if (res && res.length > 0) {
                        await new Promise( (resolve) => {
                            res.forEach((element, index) => {
                                try {
                                    // Create a DOM Option and pre-select by default
                                    var newOption = new Option(element.name, element.id, true, true);
                                    // Append it to the select

                                    $('#supplier').append(newOption);
                                } catch (e) {
                                    // error handling
                                    console.log(e)
                                } finally {
                                    // most important is here
                                    count += 1
                                    if (count == res.length) {
                                        resolve()
                                    }
                                }
                            });
                        })

                        if ($('#supplier').find("option[value='"+currentValue+"']").length) {
                            $('#supplier').val(currentValue).trigger("change");
                        } else {
                            $('#supplier').val("").trigger("change");
                        }
                    }
                },
                error: function (jqXHR) {
                    document.getElementById("loadingOrderOutput").classList.add("hidden");

                    let res = jqXHR.responseJSON;
                    console.error(res.message);
                    iziToast.error({
                        title: 'Error',
                        message: res.message,
                        position: 'topCenter'
                    });
                }
            });
        }

        async function updateWsList(dateFrom, dateTo, supplier) {
            document.getElementById("loadingOrderOutput").classList.remove("hidden");

            let currentValue = await $("#order").val();

            return $.ajax({
                url: 'track-order-output',
                type: 'get',
                data: {
                    type : "order",
                    dateFrom : dateFrom,
                    dateTo : dateTo,
                    supplier : supplier,
                },
                success: async function(res) {
                    document.getElementById("loadingOrderOutput").classList.add("hidden");

                    $("#order").html("");

                    $('#order').append(new Option("Pilih Order", "", true, true));

                    let count = 0;
                    if (res && res.length > 0) {
                        await new Promise( (resolve) => {
                            res.forEach((element, index) => {
                                try {
                                    // Create a DOM Option and pre-select by default
                                    var newOption = new Option(element.no_ws, element.id_ws, true, true);
                                    // Append it to the select
                                    $('#order').append(newOption);
                                } catch (e) {
                                    // error handling
                                    console.log(e)
                                } finally {
                                    // most important is here
                                    count += 1
                                    if (count == res.length) {
                                        resolve()
                                    }
                                }
                            });
                        })

                        if ($('#order').find("option[value='"+currentValue+"']").length) {
                            await $('#order').val(currentValue).trigger("change");
                        } else {
                            $('#order').val("").trigger("change");
                        }
                    }
                },
                error: function (jqXHR) {
                    document.getElementById("loadingOrderOutput").classList.add("hidden");

                    let res = jqXHR.responseJSON;
                    console.error(res.message);
                    iziToast.error({
                        title: 'Error',
                        message: res.message,
                        position: 'topCenter'
                    });
                }
            });
        }

        function exportExcel(elm, order) {
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

            let date = new Date();

            let day = date.getDate();
            let month = date.getMonth() + 1;
            let year = date.getFullYear();

            // This arrangement can be altered based on how we want the date's format to appear.
            let currentDate = `${day}-${month}-${year}`;

            $.ajax({
                url: "{{ url("/report/track-order-output/export") }}",
                type: 'post',
                data: {
                    dateFrom:$("#dateFrom").val(),
                    dateTo:$("#dateTo").val(),
                    outputType:$("#output-type").val(),
                    groupBy:$("#group-by").val(),
                    order:order,
                },
                xhrFields: { responseType : 'blob' },
                success: function(res) {
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
                    link.download = currentDate+" - '"+($('#order').find(":selected").text())+"' - '"+( $("#output-type").val() ? $("#output-type").val().replace(/_/g, "").toUpperCase() : "SEWING" )+"' Output.xlsx";
                    link.click();
                }, error: function (jqXHR) {
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
