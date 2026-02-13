@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-sb fw-bold">{{ $trolley->nama_trolley }}</h5>
        <a href="{{ route('stock-trolley') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-reply"></i> Kembali ke Stok Trolley
        </a>
    </div>
    <form action="{{ route('store-allocate-this-trolley') }}" method="post" onsubmit="submitForm(this, event)">
        <div class="d-flex justify-content-center">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="switch-destination" checked onchange="switchDestination(this)">
                <label class="form-check-label" id="to-line">Line</label>
                <label class="form-check-label d-none" id="to-trolley">Trolley</label>
            </div>
        </div>
        <input type="hidden" name="trolley_id" id="trolley_id" value="{{ $trolley->id }}">
        <div class="card card-sb" id="to-line-card">
            <div class="card-header">
                <h5 class="card-title fw-bold">Kirim ke Line</h5>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" style="display: block" id="scan-line">
                <div id="line-reader" onclick="clearLineScan()"></div>
                <div class="mb-3">
                    <label class="form-label">Line</label>
                    <div class="input-group w-100">
                        <select class="form-control form-control-sm select2bs4" name="line_id" id="line_id">
                            @foreach ($lines as $line)
                                <option value="{{ $line->line_id }}" {{ $trolley->line_id == $line->line_id ? "selected" : "" }}>{{ $line->FullName }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-outline-success" type="button">Get</button>
                        <button class="btn btn-sm btn-outline-primary" type="button" onclick="initLineScan()">Scan</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card card-sb d-none" id="to-trolley-card">
            <div class="card-header">
                <h5 class="card-title fw-bold">Kirim ke Trolley</h5>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" style="display: block" id="scan-stocker">
                <div id="trolley-reader" onclick="clearTrolleyScan()"></div>
                <div class="mb-3">
                    <label class="form-label">Trolley</label>
                    <div class="input-group w-100">
                        <select class="form-control form-control-sm select2bs4" name="destination_trolley_id" id="destination_trolley_id">
                            @foreach ($trolleys as $trolley)
                                <option value="{{ $trolley->id }}">{{ $trolley->nama_trolley }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-outline-success" type="button">Get</button>
                        <button class="btn btn-sm btn-outline-primary" type="button" onclick="initTrolleyScan()">Scan</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card card-primary h-100">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-6">
                        <h5 class="card-title fw-bold">
                            Kirim Stocker :
                        </h5>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row justify-content-between mb-3">
                    <div class="col-6">
                        <p>Selected Stocker : <span class="fw-bold" id="selected-row-count-1">0</span></p>
                    </div>
                    <div class="col-6">
                        <div class="d-flex gap-3 justify-content-end">
                            <div>
                                <input type="text" class="form-control" id="no_bon" name="no_bon" value="" placeholder="No. BON">
                            </div>
                            <button type="button" class="btn btn-success btn-sm float-end" onclick="sendToLine(this)"><i class="fa fa-plus fa-sm"></i> Kirim</button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table table-bordered w-100" id="datatable-trolley-stock">
                        <thead>
                            <tr>
                                <th class="d-none">ID</th>
                                <th>Check</th>
                                <th>No. Stocker</th>
                                <th>No. WS</th>
                                <th>No. Cut</th>
                                <th>Style</th>
                                <th>Color</th>
                                <th>Panel</th>
                                <th>Part</th>
                                <th>Size</th>
                                <th>Qty</th>
                                <th>Range</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($trolleyStocks && $trolleyStocks->count() < 1)
                                {{-- No Data --}}
                            @else
                                @php
                                    $i = 0;
                                @endphp
                                @foreach ($trolleyStocks as $trolleyStock)
                                    <tr>
                                        <td class="d-none">
                                            {{ $trolleyStock->stocker_id }}
                                        </td>
                                        <td>
                                            <div class="form-check" style="scale: 1.5;translate: 50%;margin-top: 10px;">
                                                <input class="form-check-input check-stock" type="checkbox" value="" onchange="checkStock(this)" id="stock_{{ $i }}">
                                            </div>
                                        </div>
                                        </td>
                                        <td>{{ $trolleyStock->id_qr_stocker }}</p></td>
                                        <td>{{ $trolleyStock->act_costing_ws }}</td>
                                        <td>{{ $trolleyStock->no_cut }}</td>
                                        <td>{{ $trolleyStock->style }}</td>
                                        <td>{{ $trolleyStock->color }}</td>
                                        <td>{{ $trolleyStock->panel }}</td>
                                        <td>{{ $trolleyStock->nama_part }}</td>
                                        <td>{{ $trolleyStock->size }}</td>
                                        <td>{{ $trolleyStock->qty }}</td>
                                        <td>{{ $trolleyStock->rangeAwalAkhir }}</td>
                                        <td>{{ $trolleyStock->user }}</td>
                                    </tr>
                                    @php
                                        $i++;
                                    @endphp
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('custom-script')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2()

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        });

        $(document).ready(async () => {
            document.getElementById("loading").classList.remove("d-none");

            $('#trolley').val("").trigger("change");
            $('#kode_stocker').val("").trigger("change");

            initLineScan();

            await $('#switch-destination').prop('checked', true);

            initialSelected();

            document.getElementById("loading").classList.add("d-none");
        });

        // Trolley ID
        var trolleyId = document.getElementById('trolley_id').value;

        // Datatable
        let datatableTrolleyStock = $("#datatable-trolley-stock").DataTable({
            ordering: false,
            columnDefs: [
                {
                    targets: [2],
                    render: (data, type, row, meta) => {
                        return `<span class="text-nowrap">`+ data.replace(/,/g, ", <br>") +`</span>`;
                    }
                },
                {
                    targets: [8],
                    render: (data, type, row, meta) => {
                        return `<span class="text-nowrap">`+ data.replace(/,/g, ", <br>") +`</span>`;
                    }
                },
                {
                    targets: "_all",
                    className: "text-nowrap"
                }
            ]
        });

        // Datatable thead filter
        $('#datatable-trolley-stock thead tr').clone(true).appendTo('#datatable-trolley-stock thead');
        $('#datatable-trolley-stock thead tr:eq(1) th').each(function(i) {
            if (i != 0 && i != 1) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm"/>');

                $('input', this).on('keyup change', function() {
                    if (datatableTrolleyStock.column(i).search() !== this.value) {
                        datatableTrolleyStock
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                if (i == 1) {
                    $(this).html(`
                        <div class="form-check" style="scale: 1.5;translate: 50%;">
                            <input class="form-check-input" type="checkbox" value="" id="checkAllStock">
                        </div>
                    `);
                } else {
                    $(this).empty();
                }
            }
        });

        // Check Stocker
        var stockArr = [];

        // Check each
        function checkStock(element) {
            let data = $('#datatable-trolley-stock').DataTable().row(element.closest('tr')).data();

            if (data) {
                if (element.checked) {
                    stockArr.push(data);
                } else {
                    stockArr = stockArr.filter(item => item[0] != data[0]);
                }

                console.log(data, stockArr);

                updateSelectedSum();
            }
        }

        // Check all
        // $("#checkAllStock").on("change", function () {
        //     document.getElementById("loading").classList.remove("d-none");

        //     // get elements
        //     let checkStockElements = document.getElementsByClassName("check-stock");

        //     // reset stock arr
        //     stockArr = [];

        //     // check each element
        //     for (let i = 0; i < checkStockElements.length; i++) {
        //         if (this.checked) {
        //             checkStockElements[i].checked = true;

        //             // push data
        //             let data = $('#datatable-trolley-stock').DataTable().row(checkStockElements[i].closest('tr')).data();
        //             stockArr.push(data);
        //         } else {
        //             checkStockElements[i].checked = false;
        //         }
        //     }

        //     updateSelectedSum();
        // });
        $("#checkAllStock").on("change", function () {
            document.getElementById("loading").classList.remove("d-none");

            let table = $('#datatable-trolley-stock').DataTable();

            // reset stock arr
            stockArr = [];

            if (this.checked) {
                // check ALL checkboxes from filtered rows (across all pages)
                table.rows({ search: 'applied' }).nodes().to$()
                    .find('.check-stock')
                    .prop('checked', true)
                    .each(function () {
                        let data = table.row($(this).closest('tr')).data();
                        stockArr.push(data);
                    });
            } else {
                // uncheck ALL checkboxes
                table.rows({ search: 'applied' }).nodes().to$()
                    .find('.check-stock')
                    .prop('checked', false);
            }

            updateSelectedSum();
        });

        // Update checked summary
        function updateSelectedSum() {
            let stockArrSum = stockArr.reduce((acc, row) => acc + Number(row[10]), 0);

            document.getElementById('selected-row-count-1').innerText = stockArr.length+" (Total Qty : "+stockArrSum+")";

            document.getElementById("loading").classList.add("d-none");
        }

        // Reset checked
        function initialSelected() {
            stockArr = [];

            let checkStockElements = document.getElementsByClassName("check-stock");
            for (let i = 0; i < checkStockElements.length; i++) {
                checkStockElements[i].checked = false;
            }
        }

        // Datatable selected row selection
        // var totalQty = 0;
        // datatableTrolleyStock.on('click', 'tbody tr', async function(e) {
        //     await e.currentTarget.classList.toggle('selected');

        //     if (e.currentTarget.classList.contains('selected')) {
        //         totalQty += parseInt(e.currentTarget.cells[8].innerText);
        //     } else {
        //         totalQty -= parseInt(e.currentTarget.cells[8].innerText);
        //     }

        //     document.getElementById('selected-row-count-1').innerText = $('#datatable-trolley-stock').DataTable().rows('.selected').data().length+" (Total Qty : "+totalQty+")";
        // });

        // Submit Send to Line
        async function sendToLine(element) {
            // Validation
            if (!document.getElementById("no_bon").value) {
                return Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: 'Harap isi no. bon',
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                    }).then(() => {
                        if (isNotNull(res.redirect)) {
                            if (res.redirect != 'reload') {
                                location.href = res.redirect;
                            } else {
                                location.reload();
                            }
                        } else {
                            location.reload();
                        }
                    });
            } else {
                let date = new Date();

                let day = date.getDate();
                let month = date.getMonth() + 1;
                let year = date.getFullYear();

                let tanggalLoading = `${year}-${month}-${day}`

                let selectedStockerTable = stockArr;
                let selectedStocker = [];

                for (let key in selectedStockerTable) {
                    if (!isNaN(key)) {
                        console.log(selectedStockerTable[key]);
                        selectedStocker.push({
                            stocker_ids: selectedStockerTable[key][0]
                        });
                    }
                }

                // Destination Line/Trolley with Bon
                let lineId = document.getElementById('line_id').value;
                let destinationTrolleyId = document.getElementById('destination_trolley_id').value;
                let noBon = document.getElementById('no_bon').value;

                if (tanggalLoading && selectedStocker.length > 0) {
                    if (document.getElementById("loading")) {
                        document.getElementById("loading").classList.remove("d-none");
                    }

                    element.setAttribute('disabled', true);

                    $.ajax({
                        type: "POST",
                        url: '{!! route('submit-send-trolley-stock') !!}',
                        data: {
                            tanggal_loading: tanggalLoading,
                            selectedStocker: selectedStocker,
                            destination: destination,
                            line_id: lineId,
                            destination_trolley_id: destinationTrolleyId,
                            no_bon: noBon,
                        },
                        success: function(res) {
                            if (document.getElementById("loading")) {
                                document.getElementById("loading").classList.add("d-none");
                            }

                            element.removeAttribute('disabled');

                            if (res.status == 200) {
                                document.getElementById("no_bon").value = "";

                                iziToast.success({
                                    title: 'Success',
                                    message: res.message,
                                    position: 'topCenter'
                                });
                            } else {
                                iziToast.error({
                                    title: 'Error',
                                    message: res.message,
                                    position: 'topCenter'
                                });
                            }

                            if (res.additional) {
                                let message = "";

                                if (res.additional['success'].length > 0) {
                                    res.additional['success'].forEach(element => {
                                        message += element['stocker'] + " - Berhasil <br>";
                                    });
                                }

                                if (res.additional['fail'].length > 0) {
                                    res.additional['fail'].forEach(element => {
                                        message += element['stocker'] + " - Gagal <br>";
                                    });
                                }

                                if (res.additional['exist'].length > 0) {
                                    res.additional['exist'].forEach(element => {
                                        message += element['stocker'] + " - Sudah Ada <br>";
                                    });
                                }

                                Swal.fire({
                                    icon: 'info',
                                    title: 'Hasil Transfer',
                                    html: message,
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                }).then(() => {
                                    if (isNotNull(res.redirect)) {
                                        if (res.redirect != 'reload') {
                                            location.href = res.redirect;
                                        } else {
                                            location.reload();
                                        }
                                    } else {
                                        location.reload();
                                    }
                                });
                            }
                        },
                        error: function(jqXHR) {
                            if (document.getElementById("loading")) {
                                document.getElementById("loading").classList.add("d-none");
                            }

                            element.removeAttribute('disabled');

                            // let res = jqXHR.responseJSON;
                            // let message = '';

                            // for (let key in res.errors) {
                            //     message = res.errors[key];
                            // }

                            iziToast.error({
                                title: 'Error',
                                message: 'Terjadi kesalahan.',
                                position: 'topCenter'
                            });

                            console.log(jqXHR);
                        }
                    })
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: "Harap pilih line/trolley dan tentukan stocker nya",
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                    });
                }
            }
        }

        // Scan QR Module :
            // Variable List :
                var lineScanner = new Html5Qrcode("line-reader");
                var lineScannerInitialized = false;

                var trolleyScanner = new Html5Qrcode("trolley-reader");
                var trolleyScannerInitialized = false;

            // Function List :
                // -Initialize Line Scanner-
                    async function initLineScan() {
                        if (document.getElementById("line-reader")) {
                            if (lineScannerInitialized == false) {
                                if (lineScanner == null || (lineScanner && (lineScanner.getState() && lineScanner.getState() != 2))) {
                                    const lineScanSuccessCallback = (decodedText, decodedResult) => {
                                            // handle the scanned code as you like, for example:
                                        console.log(`Code matched = ${decodedText}`, decodedResult);

                                        // store to input text
                                        let breakDecodedText = decodedText.split('-');

                                        setLineInput(breakDecodedText[0])

                                        clearLineScan();
                                    };
                                    const lineScanConfig = { fps: 10, qrbox: { width: 250, height: 250 } };

                                    // If you want to prefer front camera
                                    await lineScanner.start({ facingMode: "environment" }, lineScanConfig, lineScanSuccessCallback);

                                    lineScannerInitialized = true;
                                }
                            }
                        }
                    }

                    async function clearLineScan() {
                        if (lineScannerInitialized) {
                            if (lineScanner && (lineScanner.getState() && lineScanner.getState() != 1)) {
                                await lineScanner.stop();
                                await lineScanner.clear();
                            }

                            lineScannerInitialized = false;
                        }
                    }

                    async function initTrolleyScan() {
                        if (document.getElementById("trolley-reader")) {
                            if (trolleyScannerInitialized == false) {
                                if (trolleyScanner == null || (trolleyScanner && (trolleyScanner.getState() && trolleyScanner.getState() != 2))) {
                                    const trolleyScanSuccessCallback = (decodedText, decodedResult) => {
                                            // handle the scanned code as you like, for example:
                                        console.log(`Code matched = ${decodedText}`, decodedResult);

                                        // store to input text
                                        let breakDecodedText = decodedText.split('-');

                                        setTrolleyInput(breakDecodedText[0])

                                        clearLineScan();
                                    };
                                    const trolleyScanConfig = { fps: 10, qrbox: { width: 250, height: 250 } };

                                    // If you want to prefer front camera
                                    await trolleyScanner.start({ facingMode: "environment" }, trolleyScanConfig, trolleyScanSuccessCallback);

                                    trolleyScannerInitialized = true;
                                }
                            }
                        }
                    }

                    async function clearTrolleyScan() {
                        if (trolleyScannerInitialized) {
                            if (trolleyScanner && (trolleyScanner.getState() && trolleyScanner.getState() != 1)) {
                                await trolleyScanner.stop();
                                await trolleyScanner.clear();
                            }

                            trolleyScannerInitialized = false;
                        }
                    }

                    async function refreshStockerScan() {
                        await clearLineScan();
                        await initLineScan();

                        await clearTrolleyScan();
                        await initTrolleyScan();
                    }

                    function setLineInput(val) {
                        $('line_id').val(val).trigger("change");
                    }

                    function setTrolleyInput(val) {
                        $('destination_trolley_id').val(val).trigger("change");
                    }

        // -Switch Destination-
        var destination = "line";

        function switchDestination(element) {
            if (element.checked) {
                toLine();
            } else {
                toTrolley();
            }
        }

        // When Destination = line
        async function toLine() {
            document.getElementById("loading").classList.remove("d-none");

            destination = "line";

            document.getElementById("to-trolley-card").classList.add('d-none');
            document.getElementById("to-trolley").classList.add('d-none');

            document.getElementById("to-line-card").classList.remove('d-none');
            document.getElementById("to-line").classList.remove('d-none');

            $("#line_id").val("").trigger("change");

            await clearTrolleyScan();

            document.getElementById("loading").classList.add("d-none");

            await initLineScan();
        }

        // When Destination = trolley
        async function toTrolley() {
            document.getElementById("loading").classList.remove("d-none");

            destination = "trolley";

            document.getElementById("to-line-card").classList.add('d-none');
            document.getElementById("to-line").classList.add('d-none');

            document.getElementById("to-trolley-card").classList.remove('d-none');
            document.getElementById("to-trolley").classList.remove('d-none');

            $("#destination_trolley_id").val("").trigger("change");

            await clearLineScan();

            document.getElementById("loading").classList.add("d-none");

            await initTrolleyScan();
        }
    </script>
@endsection
