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
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold"><i class="fa-solid fa-hashtag"></i> Set Year Sequence</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <div id="reader" onclick="clearQrCodeScanner()"></div>
            </div>
            <div class="mb-3">
                <label  class="form-label">Stocker</label>
                <div class="input-group">
                    <input type="text" class="form-control border-input" name="id_qr_stocker" id="id_qr_stocker" data-prevent-submit="true" autocomplete="off" enterkeyhint="go" autofocus>
                    <button class="btn btn-sm btn-primary" type="button" id="scan_qr" onclick="initScan()">Scan</button>
                </div>
            </div>
            <div class="row align-items-end">
                <div class="col-md-6 d-none">
                    <div class="mb-3">
                        <label class="form-label">Form Cut ID</label>
                        <input type="text" class="form-control" name="form_cut_id" id="form_cut_id" value="" onchange="monthCountTableReload()" readonly>
                    </div>
                </div>
                <div class="col-md-6 d-none">
                    <div class="mb-3">
                        <label class="form-label">SO Detail ID</label>
                        <input type="text" class="form-control" name="so_det_id" id="so_det_id" value="" onchange="monthCountTableReload()" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Stocker</label>
                        <input type="text" class="form-control" name="stocker" id="stocker" value="" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">No. WS</label>
                        <input type="text" class="form-control" name="act_costing_ws" id="act_costing_ws" value="" readonly>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="text" class="form-control" name="color" id="color" value="" readonly>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Size</label>
                        <input type="text" class="form-control" name="size" id="size" value="" readonly>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="mb-3">
                        <label class="form-label">No. Form</label>
                        <input type="text" class="form-control" name="no_form" id="no_form" value="" readonly>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 ">
                    <div class="mb-3">
                        <label class="form-label">Part</label>
                        <input type="text" class="form-control" name="part" id="part" value="" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Qty</label>
                        <input type="text" class="form-control" name="qty" id="qty" value="" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <div class="d-flex align-items-end gap-3">
                            <div class="w-100">
                                <label class="form-label">Range Awal</label>
                                <input class="form-control" type="number" name="range_awal_stocker" id="range_awal_stocker" readonly     />
                            </div>
                            <span class="mb-1"> - </span>
                            <div class="w-100">
                                <label for="form-label">Range Akhir</label>
                                <input class="form-control" type="number" name="range_akhir_stocker" id="range_akhir_stocker" readonly   />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <div class="row align-items-end">
                            <div class="col-sm-6 col-md-6">
                                <label class="form-label">Tahun</label>
                                <select class="form-select select2bs4" name="year" id="year" onchange="getRangeYearSequence()">
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 col-md-6">
                                <label class="form-label">Sequence</label>
                                <select class="form-select select2bs4" name="sequence" id="sequence" onchange="getRangeYearSequence()">
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Print Qty</label>
                        <input class="form-control" type="number" name="print_qty" id="print_qty" onkeyup="calculateRange()" onchange="calculateRange()" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <div class="d-flex align-items-end gap-3">
                            <div class="w-100">
                                <label class="form-label">Range Awal</label>
                                <input class="form-control" type="number" name="range_awal" id="range_awal" onkeyup="calculateRange()" onchange="calculateRange()" />
                            </div>
                            <span class="mb-1"> - </span>
                            <div class="w-100">
                                <label for="form-label">Range Akhir</label>
                                <input class="form-control" type="number" name="range_akhir" id="range_akhir" onkeyup="calculateQty()" onchange="calculateQty()" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <button class="btn btn-success btn-block mt-3" onclick="setYearSequenceNumber()"><i class="fa fa-print"></i> Set Month Number</button>
                    </div>
                </div>
            </div>
            <div class="my-3">
                <div>
                    <form action="#" method="post" id="year-sequence-form">
                        <table class="table table-bordered table-sm w-100" id="year-sequence-table">
                            <thead>
                                <th>Number</th>
                                <th>Year Month</th>
                                <th>Year Month Number</th>
                                <th>Size</th>
                                <th>Dest</th>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </form>
                </div>
                <button class="btn btn-success d-none my-3" id="generate-checked-month-count" onclick="generateCheckedMonthCount()"><i class="fa fa-print"></i> Generate Checked Month</button>
            </div>
        </div>
    </div>
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
        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        })
    </script>

    <script>
        $(document).ready(async () => {
            let today = new Date();
            let todayDate = ("0" + today.getDate()).slice(-2);
            let todayMonth = ("0" + (today.getMonth() + 1)).slice(-2);
            let todayYear = today.getFullYear();
            let todayFull = todayYear + '-' + todayMonth + '-' + todayDate;

            $('#month_year_month').val(todayMonth).trigger("change");
            $('#month_year_year').val(todayYear).trigger("change");

            clearScanItemForm();

            await initScan();
        });

        // Scan QR Module :

        // Variable List :
            var html5QrcodeScanner = null;

        // Function List :

        // -Initialize Scanner-
        // Scan QR Module :
            // Variable List :
            var html5QrcodeScanner = new Html5Qrcode("reader");
            var scannerInitialized = false;

        // Function List :
            // -Initialize Scanner-
            async function initScan() {
                if (document.getElementById("reader")) {
                    console.log(html5QrcodeScanner, html5QrcodeScanner.getState());
                    if (html5QrcodeScanner == null || (html5QrcodeScanner && (html5QrcodeScanner.isScanning == false))) {
                        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                            // handle the scanned code as you like, for example:
                            console.log(`Code matched = ${decodedText}`, decodedResult);

                            // store to input text
                            document.getElementById('id_qr_stocker').value = decodedText;

                            getScannedItem(decodedText);

                            clearQrCodeScanner();
                        };
                        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                        // If you want to prefer front camera
                        await html5QrcodeScanner.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);

                        // function onScanSuccess(decodedText, decodedResult) {
                        //     // handle the scanned code as you like, for example:
                        //     console.log(`Code matched = ${decodedText}`, decodedResult);

                        //     // store to input text
                        //     let breakDecodedText = decodedText.split('-');

                        //     document.getElementById('kode_barang').value = breakDecodedText[0];

                        //     getScannedItem(breakDecodedText[0]);

                        //     clearQrCodeScanner();
                        // }

                        // function onScanFailure(error) {
                        //     // handle scan failure, usually better to ignore and keep scanning.
                        //     // for example:
                        //     console.warn(`Code scan error = ${error}`);
                        // }

                        // html5QrcodeScanner = new Html5QrcodeScanner(
                        //     "reader",
                        //     {
                        //         fps: 10,
                        //         qrbox: {
                        //             width: 250,
                        //             height: 250
                        //         }
                        //     }
                        // );

                        // html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                        // html5QrCode.start({ facingMode: { exact: "environment"}}, config, onScanSuccess, onScanFailure);
                    }
                }
            }

            async function clearQrCodeScanner() {
                console.log(html5QrcodeScanner, html5QrcodeScanner.getState());
                if (html5QrcodeScanner && (html5QrcodeScanner.isScanning)) {
                    await html5QrcodeScanner.stop();
                    await html5QrcodeScanner.clear();
                }
            }

            async function refreshScan() {
                await clearQrCodeScanner();
                await initScan();
            }

            // --Clear Scan Item Form--
            function clearScanItemForm() {
                $("#stocker").val("");
                $("#form_cut_id").val("").trigger("change");
                $("#so_det_id").val("").trigger("change");
                $("#act_costing_ws").val("");
                $("#color").val("");
                $("#size").val("");
                $("#no_form").val("");
                $("#part").val("");
                $("#qty").val("");
                $("#range_awal_stocker").val("");
                $("#range_akhir_stocker").val("");

                $("#print_qty").val("");
                $("#range_awal").val("");
                $("#range_akhir").val("");
            }

            // --Lock Scan Item Form then Clear Scanner--
            function lockScanItemForm() {
                document.getElementById("reader").classList.add("d-none");

                clearQrCodeScanner();
            }

            // --Open Scan Item Form then Open Scanner--
            function openScanItemForm() {
                document.getElementById("reader").classList.remove("d-none");

                initScan();
            }

        document.getElementById("id_qr_stocker").addEventListener("keyup", function (event) {
            console.log(event.keyCode, this.value);

            if (event.keyCode == 13) {
                getScannedItem(this.value)
            }
        })

        function getScannedItem(stocker) {
            $.ajax({
                url: '{{ route('get-stocker') }}',
                type: 'get',
                data: {
                    stocker: $("#id_qr_stocker").val()
                },
                dataType: 'json',
                success: function(res)
                {
                    console.log(res);

                    if (res) {
                        if (res.status != "400") {
                            document.getElementById("stocker").value = res.id_qr_stocker ? res.id_qr_stocker : null;
                            $("#form_cut_id").val(res.form_cut_id ? res.form_cut_id : null).trigger("change");
                            $("#so_det_id").val(res.so_det_id ? res.so_det_id : null).trigger("change");
                            document.getElementById("act_costing_ws").value = res.act_costing_ws ? res.act_costing_ws : null;
                            document.getElementById("color").value = res.color ? res.color : null;
                            document.getElementById("size").value = res.size ? res.size : null;
                            document.getElementById("no_form").value = res.no_form ? res.no_form : null;
                            document.getElementById("part").value = res.part ? res.part : null;
                            document.getElementById("qty").value = res.qty ? res.qty : null;
                            document.getElementById("range_awal_stocker").value = res.range_awal ? res.range_awal : null;
                            document.getElementById("range_akhir_stocker").value = res.range_akhir ? res.range_akhir : null;
                            $("#print_qty").val(res.qty).trigger("change");
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                html: res.message,
                            });
                        }
                    }
                },
                error: function(jqXHR)
                {
                    console.error(jqXHR)
                }
            })
        }

        function getRangeYearSequence() {
            $.ajax({
                url: '{{ route('get-range-year-sequence') }}',
                type: 'get',
                data: {
                    year: $("#year").val(),
                    sequence: $("#sequence").val()
                },
                dataType: 'json',
                success: function(res)
                {
                    console.log(res);

                    if (res) {
                        if (res.status != "400") {
                            $("#range_awal").val(res.year_sequence_number).trigger("change");
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                html: res.message,
                            });
                        }
                    }
                },
                error: function(jqXHR)
                {
                    console.error(jqXHR)
                }
            })
        }

        function calculateRange() {
            let printQty = Number($("#print_qty").val());
            let rangeAwalStocker = Number($("#range_awal_stocker").val());
            let rangeAkhirStocker = Number($("#range_akhir_stocker").val());
            let rangeAwal = Number($("#range_awal").val());
            let rangeAkhir = Number($("#range_akhir").val());

            if (printQty > 0 && rangeAwal > 0) {
                $("#range_akhir").val(rangeAwal + printQty - 1);
            }
        }

        function calculateQty() {
            let printQty = Number($("#print_qty").val());
            let rangeAwalStocker = Number($("#range_awal_stocker").val());
            let rangeAkhirStocker = Number($("#range_akhir_stocker").val());
            let rangeAwal = Number($("#range_awal").val());
            let rangeAkhir = Number($("#range_akhir").val());

            if (rangeAwal > 0 && rangeAkhir > rangeAwal) {
                $("#print_qty").val(rangeAkhir - rangeAwal + 1);
            }
        }

        function validatePrintYearSequence() {
            if (Number($('#range_awal').val()) > 0 && Number($('#range_awal').val()) <= Number($('#range_akhir').val())) {
                return true;
            }

            return false
        }

        function setYearSequenceNumber() {
            if (Number($('#print_qty').val()) > Number($('#qty').val())) {
                Swal.fire({
                    icon: "info",
                    title: "Konfirmasi",
                    html: "Qty Number melebihi Qty Stocker. Lanjut ?",
                    showDenyButton: true,
                    showCancelButton: false,
                    confirmButtonText: "Lanjut",
                    denyButtonText: "Batal"
                }).then((result) => {
                    if (result.isConfirmed) {
                        setYearSequenceNumberAct();
                    } else if (result.isDenied) {
                        Swal.fire("Input dibatalkan", "", "info");
                    }
                });
            } else {
                setYearSequenceNumberAct();
            }
        }

        function setYearSequenceNumberAct() {
            /* Read more about isConfirmed, isDenied below */
            if (validatePrintMonthCount()) {
                generating = true;

                Swal.fire({
                    title: 'Please Wait...',
                    html: 'Exporting Data...',
                    didOpen: () => {
                        Swal.showLoading()
                    },
                    allowOutsideClick: false,
                });

                $.ajax({
                    url: '{{ route('set-year-sequence-number') }}',
                    type: 'post',
                    data: {
                        "year": $('#year').val(),
                        "sequence": $('#sequence').val(),
                        "form_cut_id": $('#form_cut_id').val(),
                        "so_det_id": $('#so_det_id').val(),
                        "size": $('#size').val(),
                        "range_awal_stocker": Number($('#range_awal_stocker').val()),
                        "range_akhir_stocker": Number($('#range_akhir_stocker').val()),
                        "range_awal_year_sequence": Number($('#range_awal').val()),
                        "range_akhir_year_sequence": Number($('#range_akhir').val()),
                    },
                    xhrFields:
                    {
                        responseType: 'blob'
                    },
                    success: function(res) {
                        if (res) {
                            console.log("123",res);

                            var blob = new Blob([res], {type: 'application/pdf'});
                            var link = document.createElement('a');
                            link.href = window.URL.createObjectURL(blob);
                            link.download = "Numbers.pdf";
                            link.click();
                        }

                        window.location.reload();

                        generating = false;
                    },
                    error: function(jqXHR) {
                        Swal.fire("Nomor stocker sudah mencapai "+$("#print_qty").val()+".", "", "info");

                        generating = false;
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    html: 'Qty/Range tidak valid.',
                    allowOutsideClick: false,
                });
            }
        }

        let yearSequenceTable = $("#year-sequence-table").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            scrollX: '500px',
            scrollY: '400px',
            pageLength: 100,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('get-stocker-year-sequence') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.form_cut_id = $('#form_cut_id').val();
                    d.so_det_id = $('#so_det_id').val();
                },
            },
            columns: [
                {
                    data: 'number',
                },
                {
                    data: 'month_year'
                },
                {
                    data: 'month_year_number'
                },
                {
                    data: 'size'
                },
                {
                    data: 'dest',
                },
            ],
            columnDefs: [
                // Text No Wrap
                {
                    targets: "_all",
                    className: "text-nowrap"
                }
            ],
            "rowCallback": function( row, data, index ) {
                let yearSequence = data['year_sequence'], //data numbering month
                    $node = this.api().row(row).nodes().to$();

                if (yearSequence && yearSequence != "-") {
                    $node.addClass('red');
                }
            }
        });

        $('#year-sequence-table thead tr').clone(true).appendTo('#year-sequence-table thead');
        $('#year-sequence-table thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm" style="width:100%"/>');

            $('input', this).on('keyup change', function() {
                if (yearSequenceTable.column(i).search() !== this.value) {
                    yearSequenceTable
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        function yearSequenceTableReload() {
            $('#year-sequence-table').DataTable().ajax.reload();
        }
    </script>
@endsection
