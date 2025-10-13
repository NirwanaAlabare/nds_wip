<div>
    <div class="loading-container-fullscreen" wire:loading wire:target='setSearch, selectedSupplier, groupBy, colorFilter, poFilter, lineFilter, sizeFilter, clearFilter'>
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <div class="loading-container-fullscreen hidden" id="loadingOrderOutput">
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <span class="d-none">Search : {{ $isSearch ? "true" : "false" }}</span>
    <div class="row justify-content-between align-items-end flex-wrap">
        <div class="col">
            <div class="d-flex flex-wrap align-items-center justify-content-start gap-3 mb-3">
                <div wire:ignore>
                    <input type="date" class="form-control form-control-sm" id="dateFrom" wire:model="dateFromFilter" value="{{ $dateFromFilter }}">
                </div>
                <div> - </div>
                <div wire:ignore>
                    <input type="date" class="form-control form-control-sm" id="dateTo" wire:model="dateToFilter" value="{{ $dateToFilter }}">
                </div>
                <button class="btn btn-sb-secondary btn-sm" id="search"><i class="fa fa-search"></i></button>
                <span class="badge bg-sb text-light">PACKING</span>
            </div>
        </div>
        <div class="col">
            <div class="d-flex flex-wrap align-items-end justify-content-end gap-3 mb-3">
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
                    <button class="btn btn-success" onclick="exportExcel(this)" wire:ignore>
                        <i class="fa fa-file-excel fa-sm"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered w-auto" id="trackdatatable">
            <thead>
                <tr>
                    <td class="bg-sb text-light">No. WS</td>
                    <td class="bg-sb text-light">Style</td>
                    <td class="bg-sb text-light">Color</td>
                    <td class="bg-sb text-light">Line</td>
                    <td class="bg-sb text-light">PO</td>
                    @if ($groupBy == 'size')
                        <td class="bg-sb text-light">Size</td>
                    @endif
                    <?php
                        if ( $dailyOrderOutputs && $dailyOrderOutputs->count() > 0 && $dailyOrderOutputs->sortBy("tanggal")->groupBy("tanggal") && $dailyOrderOutputs->sortBy("tanggal")->groupBy("tanggal")->count() > 0) {
                            foreach ($dailyOrderOutputs->sortBy("tanggal")->groupBy("tanggal") as $dailyDate) {
                                if ($dailyDate && is_object($dailyDate->first())) {
                                ?>
                                    <th class="bg-sb text-light">{{ date_format(date_create($dailyDate->first()->tanggal), "d-m-Y") }}</th>
                                <?php
                                }
                            }
                    ?>
                    <th class="bg-sb text-light text-center">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php
                        $currentWs = null;
                        $currentStyle = null;
                        $currentColor = null;
                        $currentLine = null;
                        $currentPo = null;
                        $currentSize = null;

                        $dateOutputs = collect();
                        $totalOutput = null;

                        if ($dailyOrderGroup && $dailyOrderGroup->count() > 0) {
                            foreach ($dailyOrderGroup as $dailyGroup) {
                                if ($dailyGroup && is_object($dailyGroup)) {
                                ?>
                                    <tr>
                                        <td class="text-nowrap"><span class="bg-light text-dark">{{ $dailyGroup->ws }}</span></td>
                                        <td class="text-nowrap"><span class="bg-light text-dark">{{ $dailyGroup->style }}</span></td>
                                        <td class="text-nowrap"><span class="bg-light text-dark">{{ $dailyGroup->color }}</span></td>
                                        <td class="text-nowrap"><span class="bg-light text-dark">{{ strtoupper(str_replace('_', ' ', $dailyGroup->sewing_line)) }}</span></td>
                                        <td class="text-wrap"><span class="bg-light text-dark">{{ $dailyGroup->po }}</span></td>
                                        <td class="text-nowrap">{{ $dailyGroup->size }}</td>
                                        @php
                                            $thisRowOutput = 0;
                                        @endphp
                                        @foreach ($dailyOrderOutputs->sortBy("tanggal")->groupBy("tanggal") as $dailyDate)
                                            @php
                                                $thisOutput = 0;

                                                if ($groupBy == 'size') {
                                                    $thisOutput = $dailyOrderOutputs->where('ws', $dailyGroup->ws)->where('style', $dailyGroup->style)->where('color', $dailyGroup->color)->where('po', $dailyGroup->po)->where('sewing_line', $dailyGroup->sewing_line)->where('tanggal', $dailyDate->first()->tanggal)->where('size', $dailyGroup->size)->sum("output");
                                                } else {
                                                    $thisOutput = $dailyOrderOutputs->where('ws', $dailyGroup->ws)->where('style', $dailyGroup->style)->where('color', $dailyGroup->color)->where('po', $dailyGroup->po)->where('sewing_line', $dailyGroup->sewing_line)->where('tanggal', $dailyDate->first()->tanggal)->sum("output");
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
                            }
                        }
                    }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="{{ $groupBy == "size" ? '6' : '5' }}" class="bg-sb text-light text-end">
                        TOTAL
                    </th>
                    @if ($dailyOrderOutputs && $dailyOrderOutputs->count() > 0)
                        @foreach ($dateOutputs as $dateOutput)
                            <td class="fw-bold text-end text-nowrap fs-5 bg-sb text-light">
                                {{ num($dateOutput) }}
                            </td>
                        @endforeach
                        <td class="fw-bold text-end text-nowrap fs-5 bg-sb text-light">{{ num($totalOutput) }}</td>
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
                                            @if ($color && is_object($color->first()))
                                                <option value="{{ $color->first()->color }}">{{ $color->first()->color }}</option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>PO</label>
                                <select class="form-select form-select-sm" name="po" id="po" wire:model="poFilter">
                                    <option value="">Pilih PO</option>
                                    @if ($orderFilter)
                                        @foreach ($orderFilter->groupBy('po') as $po)
                                            @if ($po && is_object($po->first()))
                                                <option value="{{ $po->first()->po }}">{{ $po->first()->po }}</option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Line</label>
                                <select class="form-select form-select-sm" name="line" id="line" wire:model="lineFilter">
                                    <option value="">Pilih Line</option>
                                    @if ($orderFilter)
                                        @foreach ($orderFilter->groupBy('sewing_line') as $line)
                                            @if ($line && is_object($line->first()))
                                                <option value="{{ $line->first()->sewing_line }}">{{ $line->first()->sewing_line }}</option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            @if ($groupBy == "size")
                                <div class="mb-3">
                                    <label>Size</label>
                                    <select class="form-select form-select-sm" name="size" id="size" wire:model="sizeFilter">
                                        <option value="">Pilih Size</option>
                                        @if ($orderFilter)
                                            @foreach ($orderFilter->groupBy('size') as $size)
                                                @if ($size && is_object($size->first()))
                                                    <option value="{{ $size->first()->size }}">{{ $size->first()->size }}</option>
                                                @endif
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
        // async function setFixedColumn() {
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

        //         for (var j = 0; j < tds[i].length; j++) {
        //             tds[i][j].style.left = (column - currentWidth) + "px";

        //             if (j != tds[i].length - 1) {
        //                 if (currentWidth < 1) {
        //                     column += tds[i][j].offsetWidth;
        //                 }
        //             } else {
        //                 tds[i][j].style.left = column + "px";
        //             }

        //             if (i > 0 && Number(tds[i][j].getAttribute("rowspan")) >= 1) {
        //                 if (currentRowSpan < Number(tds[i][j].getAttribute("rowspan"))) {
        //                     currentRowSpan = Number(tds[i][j].getAttribute("rowspan"));
        //                 } else if (currentRowSpan >= Number(tds[i][j].getAttribute("rowspan"))) {
        //                     if (j == tds[i].length - 1 || j == tds[i].length - 2 || j == tds[i].length - 3) {
        //                         currentWidth = tds[i][j].offsetWidth;
        //                         currentCells = trs[i].cells.length;
        //                     } else {
        //                         if (currentCells > 0) {

        //                             let currentRowFilter = currentRow.filter((item) => item.currentCells >= trs[i].cells.length);
        //                             let currentRowFilterWidth = currentRowFilter[currentRowFilter.length-1] ? (Math.round(Number(currentRowFilter[currentRowFilter.length-1].currentWidth))) : 0;

        //                             tds[i][j].style.left = ((column-currentWidth)-currentRowFilterWidth)+"px";
        //                         }

        //                         console.log(tds[i][j], column, currentWidth, currentCells, j, tds[i].length-2);

        //                         currentRow.push({"currentWidth" : tds[i][j].offsetWidth, "currentCells": trs[i].cells.length });
        //                     }
        //                 }
        //             }
        //         }
        //     }
        // }

        document.addEventListener("DOMContentLoaded", () => {
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

            $('#search').on('click', async function (e) {
                await clearFixedColumn();

                @this.setSearch();

                Livewire.emit('loadingStart');
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

            $('#po').on('change', async function (e) {
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
                    4,
                    5
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
                        start: 6,
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
                        4,
                        5
                    ]
                });
            }, 500);

            setTimeout(function () {
                $('#trackdatatable').DataTable().columns.adjust();
            }, 1000);
        }

        Livewire.on("clearFixedColumn", () => {
            clearFixedColumn();
        });

        Livewire.on("initFixedColumn", () => {
            clearFixedColumn();

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
                url: 'track-packing-output',
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
                url: 'track-packing-output',
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
                url: "{{ url("/packing-line/track-packing-output/export") }}",
                type: 'post',
                data: {
                    dateFrom:$("#dateFrom").val(),
                    dateTo:$("#dateTo").val(),
                    groupBy:$("#group-by").val(),
                    order:$("#order").val(),
                    buyer:$("#supplier").val(),
                },
                xhrFields: { responseType : 'blob' },
                success: function(res) {
                    elm.removeAttribute('disabled');
                    elm.innerHTML = "";
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
                    link.download = currentDate+" - "+($('#supplier').val() ? "'"+$('#supplier').find(":selected").text()+"'" : " All Supplier ")+" - "+($('#order').val() ? "'"+$('#order').find(":selected").text()+"'" : " All WS ")+" - Packing Output.xlsx";
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
