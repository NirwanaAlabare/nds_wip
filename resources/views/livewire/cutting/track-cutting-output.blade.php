<div>
    <div class="loading-container-fullscreen" wire:loading wire:target='selectedSupplier, groupBy, colorFilter, panelFilter, mejaFilter, sizeFilter, clearFilter'>
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <div class="loading-container-fullscreen hidden" id="loadingOrderOutput">
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    @if ($aWeekForce)
        <div class="alert alert-warning alert-dismissible fade show" role="alert" data-bs-theme="light">
            <strong>Range terlalu luas untuk filter global.</strong> otomatis diset ke 7 hari terakhir.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="row justify-content-between align-items-center">
        <div class="col-12 col-lg-6 col-xl-4">
            <div class="d-flex align-items-center justify-content-start gap-3 mb-3">
                <div wire:ignore>
                    <input type="date" class="form-control form-control-sm" id="dateFrom" wire:model="dateFromFilter" value="{{ $dateFromFilter }}">
                </div>
                <div> - </div>
                <div wire:ignore>
                    <input type="date" class="form-control form-control-sm" id="dateTo" wire:model="dateToFilter" value="{{ $dateToFilter }}">
                </div>
                <span class="badge bg-sb text-light">CUT</span>
            </div>
        </div>
        <div class="col-12 col-lg-6 col-xl-8">
            <div class="d-flex align-items-center justify-content-end gap-3 mb-3">
                @if ($onFilter)
                    <span class="badge text-bg-success">ON FILTER</span>
                @endif
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
        @if ($dailyOrderGroup)
            <table class="table table-bordered w-auto" id="trackdatatable">
                <thead>
                    <tr>
                        <td class="bg-sb text-light">No. WS</td>
                        <td class="bg-sb text-light">Style</td>
                        <td class="bg-sb text-light">Color</td>
                        <td class="bg-sb text-light">Meja</td>
                        <td class="bg-sb text-light">Panel</td>

                        @if ($groupBy === 'size')
                            <td class="bg-sb text-light">Size</td>
                        @endif

                        @foreach ($dates as $tanggal)
                            <th class="bg-sb text-light">
                                {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}
                            </th>
                        @endforeach

                        <th class="bg-sb text-light text-center">TOTAL</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $dateOutputs = collect();
                        $totalOutput = 0;
                    @endphp

                    @foreach ($dailyOrderGroup as $dailyGroup)
                        @php
                            $thisRowOutput = 0;
                        @endphp

                        <tr wire:key="row-{{ $dailyGroup->ws }}-{{ $dailyGroup->style }}-{{ $dailyGroup->color }}-{{ $dailyGroup->panel }}-{{ $dailyGroup->id_meja }}-{{ $dailyGroup->size ?? 'all' }}">
                            <td class="text-nowrap">
                                <span class="bg-light text-dark sticky-span">{{ $dailyGroup->ws }}</span>
                            </td>
                            <td class="text-nowrap">
                                <span class="bg-light text-dark sticky-span">{{ $dailyGroup->style }}</span>
                            </td>
                            <td class="text-nowrap">
                                <span class="bg-light text-dark sticky-span">{{ $dailyGroup->color }}</span>
                            </td>
                            <td class="text-nowrap">
                                <span class="bg-light text-dark sticky-span">
                                    {{ strtoupper(str_replace('_', ' ', $dailyGroup->meja)) }}
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <span class="bg-light text-dark sticky-span">{{ $dailyGroup->panel }}</span>
                            </td>

                            @if ($groupBy === 'size')
                                <td class="text-nowrap">{{ $dailyGroup->size }}</td>
                            @endif

                            @foreach ($dates as $tanggal)
                                @php
                                    $key = implode('|', [
                                        $dailyGroup->ws,
                                        $dailyGroup->style,
                                        $dailyGroup->color,
                                        $dailyGroup->panel,
                                        $dailyGroup->id_meja,
                                        $groupBy === 'size' ? $dailyGroup->size : 'all',
                                        $tanggal,
                                    ]);

                                    $thisOutput = $outputSums[$key] ?? 0;
                                    $thisRowOutput += $thisOutput;

                                    $dateOutputs[$tanggal] = ($dateOutputs[$tanggal] ?? 0) + $thisOutput;
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
                    @endforeach
                </tbody>

                <tfoot>
                    <tr>
                        <th colspan="{{ $groupBy === 'size' ? 6 : 5 }}" class="bg-sb text-light text-end">
                            TOTAL
                        </th>

                        @foreach ($dates as $tanggal)
                            <td class="fw-bold text-end text-nowrap fs-5 bg-sb text-light">
                                {{ num($dateOutputs[$tanggal] ?? 0) }}
                            </td>
                        @endforeach

                        <td class="fw-bold text-end text-nowrap fs-5 bg-sb text-light">
                            {{ num($totalOutput) }}
                        </td>
                    </tr>
                </tfoot>
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
                                        @foreach ($orderFilter->sortBy('color')->groupBy('color') as $color)
                                            <option value="{{ $color->first()->color }}">{{ $color->first()->color }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Panel</label>
                                <select class="form-select form-select-sm" name="panel" id="panel" wire:model="panelFilter">
                                    <option value="">Pilih Panel</option>
                                    @if ($orderFilter)
                                        @foreach ($orderFilter->sortBy('panel')->groupBy('panel') as $panel)
                                            <option value="{{ $panel->first()->panel }}">{{ $panel->first()->panel }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Meja</label>
                                <select class="form-select form-select-sm" name="meja" id="meja" wire:model="mejaFilter">
                                    <option value="">Pilih Meja</option>
                                    @if ($orderFilter)
                                        @foreach ($orderFilter->sortBy('id_meja')->groupBy('id_meja') as $meja)
                                            <option value="{{ $meja->first()->id_meja }}">{{ $meja->first()->meja }}</option>
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
                                            @foreach ($orderFilter->sortBy('size')->groupBy('size') as $size)
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
                    <button type="button" class="btn btn-sb" onclick="clearFilter()"><i class="fa-solid fa-broom"></i> Bersihkan</button>
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
                // await clearFixedColumn();

                // @this.set('dateFromFilter', this.value);

                // updateSupplierList($('#dateFrom').val(), $('#dateTo').val());

                reloadPage();
            });

            $('#dateTo').on('change', async function (e) {
                // await clearFixedColumn();

                // @this.set('dateToFilter', this.value);

                // updateSupplierList($('#dateFrom').val(), $('#dateTo').val());

                reloadPage();
            });

            $('#supplier').on('change', async function (e) {
                // await clearFixedColumn();

                // @this.set('selectedSupplier', this.value);

                updateWsList($('#dateFrom').val(), $('#dateTo').val(), this.value);
            });

            $('#order').on('change', async function (e) {
                // await clearFixedColumn();

                // @this.set('loadingOrderOutput', true);

                // @this.set('selectedOrder', this.value);

                // Livewire.emit('loadingStart');

                // Reload Page for Better Performance
                reloadPage();
            });

            $('#color').on('change', async function (e) {
                // await clearFixedColumn();

                // @this.set('loadingOrderOutput', true);

                // Livewire.emit('loadingStart');
                reloadPage();
            });

            $('#panel').on('change', async function (e) {
                // await clearFixedColumn();

                // @this.set('loadingOrderOutput', true);

                // Livewire.emit('loadingStart');
                reloadPage();
            });

            $('#meja').on('change', async function (e) {
                // await clearFixedColumn();

                // @this.set('loadingOrderOutput', true);

                // Livewire.emit('loadingStart');
                reloadPage();
            });

            $('#size').on('change', async function (e) {
                // await clearFixedColumn();

                // @this.set('loadingOrderOutput', true);

                // Livewire.emit('loadingStart');
                reloadPage();
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

            clearFixedColumn();
            setFixedColumn();

            $("#order").val(@js($selectedOrder)).trigger("change.select2");
            $("#supplier").val(@js($selectedSupplier)).trigger("change.select2");
        });

        function clearFixedColumn() {
            $('#trackdatatable').DataTable().destroy();

            console.log("clearFixedColumn");
        }

        // Use Reload Page for better performance
        function reloadPage() {
            let dateFromFilter = "&dateFromFilter="+$("#dateFrom").val();
            let dateToFilter = "&dateToFilter="+$("#dateTo").val();
            let selectedSupplier = "&selectedSupplier="+$("#supplier").val();
            let selectedOrder = "&selectedOrder="+$("#order").val();
            let colorFilter = "&colorFilter="+$("#color").val();
            let panelFilter = "&panelFilter="+$("#panel").val();
            let mejaFilter = "&mejaFilter="+$("#meja").val();
            let sizeFilter = "&sizeFilter="+$("#size").val();
            let groupBy = "&groupBy="+($("#group-by").val() ? $("#group-by").val() : "size");

            let params = dateFromFilter+dateToFilter+selectedSupplier+selectedOrder+colorFilter+panelFilter+mejaFilter+sizeFilter+groupBy

            window.location.href = `{{ route('track-cutting-output') }}?${params}`;
        }

        function clearFilter() {
            $("#color").val("").trigger("change");
            $("#panel").val("").trigger("change");
            $("#meja").val("").trigger("change");
            $("#size").val("").trigger("change");

            // @this.set('colorFilter', null);
            // @this.set('panelFilter', null);
            // @this.set('mejaFilter', null);
            // @this.set('sizeFilter', null);

            // Livewire.emit('loadingStart');

            // Reload Page for Better Performance
            reloadPage();
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

            console.log("initFixedColumn");
        }

        Livewire.on("reloadPage", () => {
            reloadPage();
        });

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
        Livewire.hook('message.processed', () => {
            setTimeout(() => {
                setFixedColumn();
            }, 0);
        });

        async function updateSupplierList(dateFrom, dateTo) {
            document.getElementById("loadingOrderOutput").classList.remove("hidden");

            let currentValue = await $("#supplier").val();

            return $.ajax({
                url: 'track-cutting-output',
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
                url: 'track-cutting-output',
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

                        // if ($('#order').find("option[value='"+currentValue+"']").length) {
                        //     await $('#order').val(currentValue).trigger("change");
                        // } else {
                        //     $('#order').val("").trigger("change");
                        // }

                        $('#order').val("").trigger("change.select2");
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
                url: "{{ url("/report-cutting/track-cutting-output/export") }}",
                type: 'get',
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
                    link.download = currentDate+" - "+($('#supplier').val() ? "'"+$('#supplier').find(":selected").text()+"'" : " All Supplier ")+" - "+($('#order').val() ? "'"+$('#order').find(":selected").text()+"'" : " All WS ")+" - Cutting Output.xlsx";
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
