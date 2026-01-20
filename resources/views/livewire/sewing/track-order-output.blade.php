<div>
    <div class="loading-container-fullscreen" wire:loading wire:target='setSearch, selectedSupplier, selectedOrder, groupBy, colorFilter, lineFilter, sizeFilter, clearFilter, outputType'>
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <div class="loading-container-fullscreen hidden" id="loadingOrderOutput">
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <input type="hidden" wire:model="search">
    <div class="row justify-content-between align-items-end">
        <div class="col-12 col-lg-6 col-xl-5">
            <div class="d-flex align-items-center justify-content-start gap-3 mb-3">
                <div wire:ignore>
                    <input type="date" class="form-control form-control-sm" id="dateFrom" wire:model="dateFromFilter">
                </div>
                <div> - </div>
                <div wire:ignore>
                    <input type="date" class="form-control form-control-sm" id="dateTo" wire:model="dateToFilter" value="{{ $dateToFilter }}">
                </div>
                <button class="btn btn-sb-secondary btn-sm" wire:click="setSearch" onclick="clearFixedColumn()"><i class="fa fa-search"></i></button>
                <span class="badge bg-sb text-light">{{ strtoupper(str_replace("_", "", ($outputType ? ($outputType == "_packing_po" ? "PACKING" : ($outputType == "_packing" ? "FINISHING" : "SEWING")) : 'SEWING'))) }}</span>
            </div>
        </div>
        <div class="col-12 col-lg-6 col-xl-7">
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
                <div class="d-flex flex-wrap gap-1">
                    <button class="btn btn-success" onclick="exportExcel(this, '{{ $selectedOrder }}', '{{ $selectedSupplier }}')">
                        <i class="fa fa-file-excel fa-sm"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered w-100" id="trackdatatable">
            <thead>
                <tr>
                    <th class="bg-sb fw-bold">No. WS</th>
                    <th class="bg-sb fw-bold">Style</th>
                    <th class="bg-sb fw-bold">Color</th>
                    <th class="bg-sb fw-bold">Line</th>
                    @if ($groupBy == 'size')
                        <th class="bg-sb">Size</th>
                    @endif
                    <?php
                        if ( $dailyOrderOutputs && $dailyOrderOutputs->count() > 0 ) {
                            foreach ($dailyOrderOutputs->sortBy("tanggal")->groupBy("tanggal") as $dailyDate) {
                                if ($dailyDate && is_object($dailyDate->first())) {
                                ?>
                                    <th class="bg-sb">{{ date_format(date_create(($dailyDate->first()->tanggal)), "d-m-Y") }}</th>
                                <?php
                                }
                            }
                    ?>
                    <th class="bg-sb text-center">TOTAL</th>
                </tr>
            <thead>
            <tbody>
                <?php
                        $currentWs = null;
                        $currentStyle = null;
                        $currentColor = null;
                        $currentLine = null;
                        $currentSize = null;

                        $dateOutputs = collect();
                        $totalOutput = null;

                        foreach ($dailyOrderGroup as $dailyGroup) {
                            if ($dailyGroup && is_object($dailyGroup)) {
                            ?>
                                <tr>
                                    <td class="text-nowrap"><span class="sticky-span">{{ $dailyGroup->ws }}</span></td>
                                    <td class="text-nowrap"><span class="sticky-span">{{ $dailyGroup->style }}</span></td>
                                    <td class="text-nowrap"><span class="sticky-span">{{ $dailyGroup->color }}</span></td>
                                    <td class="text-nowrap"><span class="sticky-span">{{ strtoupper(str_replace('_', ' ', $dailyGroup->sewing_line)) }}</span></td>
                                    <td class="text-nowrap">{{ $dailyGroup->size }}</td>

                                    @php
                                        $thisRowOutput = 0;
                                    @endphp
                                    @foreach ($dailyOrderOutputs->sortBy("tanggal")->groupBy("tanggal") as $dailyDate)
                                        @php
                                            $thisOutput = 0;

                                            if ($dailyDate && is_object($dailyDate->first())) {
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
                                            }
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
                        }
                    }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="{{ $groupBy == "size" ? '5' : '4' }}" class="fixed-column text-end">
                        TOTAL
                    </td>
                    @if ($dailyOrderOutputs && $dailyOrderOutputs->count() > 0)
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
                                    <option value="_packing">Finishing</option>
                                    <option value="_packing_po">Packing</option>
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
                                    @if ($orderFilter)
                                        @foreach ($orderFilter->groupBy('color') as $color)
                                            <option value="{{ $color->first()->color }}">{{ $color->first()->color }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Line</label>
                                <select class="form-select form-select-sm" name="line" id="line" wire:model="lineFilter">
                                    <option value="">Pilih Line</option>
                                    @if ($orderFilter)
                                        @foreach ($orderFilter->groupBy('sewing_line') as $color)
                                            <option value="{{ $color->first()->sewing_line }}">{{ $color->first()->sewing_line }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            {{-- @if ($groupBy == "size")
                                <div class="mb-3">
                                    <label>Size</label>
                                    <select class="form-select form-select-sm" name="size" id="size" wire:model="sizeFilter">
                                        <option value="">Pilih Size</option>
                                        @if ($orderFilter)
                                            @foreach ($orderFilter->groupBy('size') as $size)
                                                <option value="{{ $size->first()->size }}">{{ $size->first()->size }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            @endif --}}
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
        // function setFixedColumn() {
        //     var table = document.querySelector('table.sticky-table');
        //     var trs = table.querySelectorAll('tr');
        //     var tds = [].map.call(trs, tr => tr.querySelectorAll('td.fixed-column'));

        //     let column = 0;
        //     let currentRowSpan = 0;
        //     let currentRow = [];
        //     let currentWidth = 0;
        //     let currentCells = 0;
        //     for (var i = 0; i < trs.length; i++) {
        //         if (currentRowSpan <= 1) {
        //             column = 0;
        //             currentRowSpan = 0;
        //             currentRow = [];
        //             currentWidth = 0;
        //             currentCells = 0;
        //         } else {
        //             currentRowSpan--;
        //         }

        //         console.log(trs[i].cells.length, tds[i].length, currentRow);

        //         for (var j = 0; j < tds[i].length; j++) {
        //             tds[i][j].style.left = (column - currentWidth) + "px";

        //             if (j != tds[i].length - 1) {
        //                 if (currentWidth < 1) {
        //                     column += tds[i][j].offsetWidth;
        //                 }
        //             } else {
        //                 tds[i][j].style.left = column + "px";
        //             }

        //             if (i > 0) {
        //                 if (currentRowSpan < Number(tds[i][j].getAttribute("rowspan"))) {
        //                     currentRowSpan = Number(tds[i][j].getAttribute("rowspan"));
        //                 } else if (currentRowSpan >= Number(tds[i][j].getAttribute("rowspan"))) {
        //                     if (j == tds[i].length - 2) {
        //                         currentWidth = tds[i][j].offsetWidth;
        //                         currentCells = trs[i].cells.length;
        //                     } else {
        //                         if (currentCells > 0) {

        //                             let currentRowFilter = currentRow.filter((item) => item.currentCells >= trs[i].cells.length);
        //                             let currentRowFilterWidth = currentRowFilter[currentRowFilter.length-1] ? (Math.round(Number(currentRowFilter[currentRowFilter.length-1].currentWidth))) : 0;

        //                             console.log(currentRowFilter, currentRowFilterWidth);

        //                             tds[i][j].style.left = ((column-currentWidth)-currentRowFilterWidth)+"px";
        //                         }

        //                         currentRow.push({"currentWidth" : tds[i][j].offsetWidth, "currentCells": trs[i].cells.length });
        //                     }
        //                 }
        //             }
        //         }
        //     }
        // }

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
                await clearFixedColumn();

                @this.set('dateFromFilter', this.value);

                updateSupplierList($('#dateFrom').val(), $('#dateTo').val());
            });

            $('#dateTo').on('change', async function (e) {
                await clearFixedColumn();

                @this.set('dateToFilter', this.value);

                updateSupplierList($('#dateFrom').val(), $('#dateTo').val());
            });

            $('#supplier').on('change', async function (e) {
                await clearFixedColumn();

                @this.set('selectedSupplier', this.value);

                updateWsList($('#dateFrom').val(), $('#dateTo').val(), this.value);
            });

            $('#order').on('change', async function (e) {
                await clearFixedColumn();

                @this.set('loadingOrderOutput', true);

                @this.set('selectedOrder', this.value);

                Livewire.emit('loadingStart');
            });

            $('#color').on('change', async function (e) {
                await clearFixedColumn();

                @this.set('loadingOrderOutput', true);

                Livewire.emit('loadingStart');
            });

            $('#line').on('change', async function (e) {
                await clearFixedColumn();

                @this.set('loadingOrderOutput', true);

                Livewire.emit('loadingStart');
            });

            $('#size').on('change', async function (e) {
                await clearFixedColumn();

                @this.set('loadingOrderOutput', true);

                Livewire.emit('loadingStart');
            });

            $('#output-type').on('change', async function (e) {
                await clearFixedColumn();

                @this.set('loadingOrderOutput', true);

                Livewire.emit('loadingStart');
            });

            $('#group-by').on('change', async function (e) {
                await clearFixedColumn();

                @this.set('loadingOrderOutput', true);

                Livewire.emit('loadingStart');
            });

            $('#color').on('change', async function (e) {
                await clearFixedColumn();

                @this.set('loadingOrderOutput', true);

                Livewire.emit('loadingStart');
            });

            $('#line').on('change', async function (e) {
                await clearFixedColumn();

                @this.set('loadingOrderOutput', true);

                Livewire.emit('loadingStart');
            });

            var datatable = $('#trackdatatable').DataTable({
                paging: false,
                ordering: false,
                searching: false,
                scrollX: true,
                scrollY: '400px',
                serverSide: false,
                rowsGroup: [
                    0,
                    1,
                    2,
                    3,
                    {{ $groupBy == 'size' ? 4 : null }}
                ]
            });
        });

        function clearFixedColumn() {
            $('#trackdatatable').DataTable().destroy();

            console.log("clearFixedColumn");
        }

        async function setFixedColumn() {
            setTimeout(function () {
                // Initialize DataTable again
                var datatable = $('#trackdatatable').DataTable({
                    fixedColumns: {
                        start: {{ $groupBy == 'size' ? 5 : 4 }},
                        end: 1
                    },
                    paging: false,
                    ordering: false,
                    searching: false,
                    scrollX: true,
                    scrollY: "400px",
                    serverSide: false,
                    rowsGroup: [
                        0,
                        1,
                        2,
                        3,
                        {{ $groupBy == 'size' ? 4 : null }}
                    ]
                });
            }, 500);

            setTimeout(function () {
                $('#trackdatatable').DataTable().columns.adjust();
            }, 1000);

            console.log("initFixedColumn");
        }

        Livewire.on("clearFixedColumn", () => {
            clearFixedColumn();
        });

        Livewire.on("initFixedColumn", () => {
            setFixedColumn();

            setTimeout(function () {
                $('#trackdatatable').DataTable().columns.adjust();
            }, 1000);
        });

        // Run after any Livewire update
        // Livewire.hook('message.processed', () => {
        //     setTimeout(() => {
        //         setFixedColumn();
        //     }, 0);
        // });

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

        function exportExcel(elm, order, buyer) {
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
                    buyer:buyer,
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
                    link.download = currentDate+" - "+($('#supplier').val() ? "'"+$('#supplier').find(":selected").text()+"'" : "All Supplier")+" - "+($('#order').val() ? "'"+$('#order').find(":selected").text()+"'" : "All WS")+" - '"+( $("#output-type").val() ? $("#output-type").val().replace(/_/g, "").toUpperCase() : "SEWING" )+"' Output.xlsx";
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
