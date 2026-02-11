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
    <span class="text-light">Search : {{ $isSearch ? "true" : "false" }}</span>
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
        @if ($dailyOrderGroup && is_object($dailyOrderGroup))
            <table class="table table-bordered w-100" id="trackdatatable">
                <thead>
                    <tr>
                        <th class="bg-sb fw-bold">No. WS</th>
                        <th class="bg-sb fw-bold">Style</th>
                        <th class="bg-sb fw-bold">Color</th>
                        <th class="bg-sb fw-bold">Line</th>
                        <th class="bg-sb fw-bold">Po</th>
                        <th class="bg-sb fw-bold">Quality</th>

                        @if ($groupBy === 'size')
                            <th class="bg-sb fw-bold">Size</th>
                        @endif

                        @foreach ($dates as $date)
                            <th class="bg-sb fw-bold text-center">
                                {{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}
                            </th>
                        @endforeach

                        <th class="bg-sb fw-bold text-center">TOTAL</th>
                    </tr>
                </thead>

                <tbody>
                    @php $useSize = $groupBy === 'size'; @endphp

                    @foreach ($dailyOrderGroup as $row)
                        @if (is_object($row))
                            @php
                                $key = implode('|', [
                                    $row->ws,
                                    $row->style,
                                    $row->color,
                                    $row->sewing_line,
                                    $row->po,
                                    $row->type,
                                    $useSize ? $row->size : '_',
                                ]);
                            @endphp

                            <tr>
                                <td class="text-nowrap">
                                    <span class="sticky-span">{{ $row->ws }}</span>
                                </td>
                                <td class="text-nowrap">
                                    <span class="sticky-span">{{ $row->style }}</span>
                                </td>
                                <td class="text-nowrap">
                                    <span class="sticky-span">{{ $row->color }}</span>
                                </td>
                                <td class="text-nowrap">
                                    <span class="sticky-span">
                                        {{ strtoupper(str_replace('_', ' ', $row->sewing_line)) }}
                                    </span>
                                </td>
                                <td class="text-nowrap">
                                    <span class="sticky-span">{{ $row->po }}</span>
                                </td>
                                <td class="text-nowrap">
                                    <span class="sticky-span">{{ strtoupper($row->type) }}</span>
                                </td>

                                @if ($useSize)
                                    <td class="text-nowrap">{{ $row->size }}</td>
                                @endif

                                @foreach ($dates as $date)
                                    <td class="text-end text-nowrap">
                                        {{ num($outputMap[$key][$date] ?? 0) }}
                                    </td>
                                @endforeach

                                <td class="fw-bold text-end text-nowrap fs-5">
                                    {{ num($rowTotals[$key] ?? 0) }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>

                <tfoot>
                    <tr>
                        <td
                            colspan="{{ $useSize ? 7 : 6 }}"
                            class="fixed-column fw-bold text-end"
                        >
                            TOTAL
                        </td>

                        @foreach ($dates as $date)
                            <td class="fw-bold text-end text-nowrap fs-5">
                                {{ num($dateTotals[$date] ?? 0) }}
                            </td>
                        @endforeach

                        <td class="fw-bold text-end text-nowrap fs-5">
                            {{ num($grandTotal) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        @else
            <table class="table table-bordered w-100">
                <thead>
                    <tr>
                        <th class="bg-sb fw-bold">No. WS</th>
                        <th class="bg-sb fw-bold">Style</th>
                        <th class="bg-sb fw-bold">Color</th>
                        <th class="bg-sb fw-bold">Line</th>

                        @if ($groupBy === 'size')
                            <th class="bg-sb fw-bold">Size</th>
                        @endif

                        @foreach ($dates as $date)
                            <th class="bg-sb fw-bold text-center">
                                {{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}
                            </th>
                        @endforeach

                        <th class="bg-sb fw-bold text-center">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="{{ count($dates) + ($groupBy == 'size' ? 6 : 5) }}">Data tidak ada</td>
                    </tr>
                </tbody>
            </table>
        @endif
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

            if (!$.fn.dataTable.isDataTable('#trackdatatable')) {
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
                        5,
                    ]
                });
            }
        });

        function clearFixedColumn() {
            if ($.fn.dataTable.isDataTable('#trackdatatable')) {
                $('#trackdatatable').DataTable().destroy();

                console.log("clearFixedColumn");
            }
        }

        async function setFixedColumn() {
            if ($.fn.dataTable.isDataTable('#trackdatatable')) {
                console.log("datatable initialized");
            } else {
                // Initialize DataTable again
                var datatable = $('#trackdatatable').DataTable({
                    fixedColumns: {
                        start: 7,
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
                        5,
                        6
                    ]
                });

                setTimeout(function () {
                    $('#trackdatatable').DataTable().columns.adjust();
                }, 1000);
            }
        }

        Livewire.on("clearFixedColumn", async function () {
            await clearFixedColumn();
        });

        Livewire.on("initFixedColumn", async function () {
            await clearFixedColumn();

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
