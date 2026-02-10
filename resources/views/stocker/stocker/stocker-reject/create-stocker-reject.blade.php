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
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">
                    <i class='fa fa-plus'></i> Create Stocker Reject
                </h5>
                <a type="button" href="{{ route('stocker-reject') }}" class="btn btn-sm btn-primary"><i class="fa fa-reply"></i> Kembali ke stocker reject</a>
            </div>
        </div>
        <div class="card-body">
            <div>
                <div class="mb-3">
                    <label class="form-label">Stocker</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="stocker">
                        <button class="btn btn-outline-success" type="button" id="get-stocker-button" onclick="getStocker()">Get</button>
                        <button class="btn btn-outline-primary" type="button" id="scan-stocker-button" onclick="initScan()">Scan</button>
                    </div>
                </div>
                <div class="d-flex justify-content-center my-3">
                    <div id="reader" class="w-75"></div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Stocker</label>
                            <input type="text" class="form-control" id="id_qr_stocker" value="-" readonly>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Bundle Stocker</label>
                            <input type="text" class="form-control" id="id_qr_stocker_bundle" value="-" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Worksheet</label>
                            <input type="text" class="form-control" id="act_costing_ws" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Style</label>
                            <input type="text" class="form-control" id="style" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Color</label>
                            <input type="text" class="form-control" id="color" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Panel</label>
                            <input type="text" class="form-control" id="panel" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Part</label>
                            <input type="text" class="form-control" id="part" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Proses</label>
                            <select class="form-select" id="process" onchange="stockerRejectTableReload()">
                                <option value="-">Pilih Proses</option>
                            </select>
                        </div>
                        <input type="hidden" class="form-control" id="dc_in_id">
                        <input type="hidden" class="form-control" id="secondary_inhouse_id">
                        <input type="hidden" class="form-control" id="secondary_in_id">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title">
                Stocker Process
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id='stocker-reject-table'>
                    <thead>
                        <th>Action</th>
                        <th>Tanggal</th>
                        <th>Stocker</th>
                        <th>Proses</th>
                        <th>Qty Reject</th>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            initScan();
        });

        $('.select2').select2();

        // Scan QR Module :
        // Variable List :
        var html5QrcodeScanner = null;

        // Function List :
        // -Initialize Scanner-
        async function initScan() {
            if (document.getElementById("reader")) {
                if (html5QrcodeScanner) {
                    await html5QrcodeScanner.clear();
                }

                function onScanSuccess(decodedText, decodedResult) {
                    // handle the scanned code as you like, for example:
                    console.log(`Code matched = ${decodedText}`, decodedResult);

                    // store to input text
                    // let breakDecodedText = decodedText.split('-');

                    document.getElementById('stocker').value = decodedText;

                    getStocker();

                    // html5QrcodeScanner.clear();
                    initScan();
                }

                function onScanFailure(error) {
                    // handle scan failure, usually better to ignore and keep scanning.
                    // for example:
                    console.warn(`Code scan error = ${error}`);
                }

                html5QrcodeScanner = new Html5QrcodeScanner(
                    "reader", {
                        fps: 10,
                        qrbox: {
                            width: 250,
                            height: 250
                        }
                    },
                    /* verbose= */
                    false);

                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            }
        }

        function getStocker()
        {
            clearForm();

            // Refresh process list
            let processListElement = document.getElementById("process");
            processListElement.innerHTML = '';
            // Initial option
            let initialOpt = document.createElement("option");
            initialOpt.text = 'Pilih Proses';
            initialOpt.value = '-';
            processListElement.append(initialOpt);

            // Get stocker reject
            $.ajax({
                type: "get",
                url: "{{ route('get-stocker-reject') }}",
                data: {
                    id_qr_stocker: $('#stocker').val()
                },
                dataType: "json",
                success: function (res) {
                    if (res.status == 200) {
                        if (res.data) {
                            let data = res.data;

                            $("#id_qr_stocker").val(data.id_qr_stocker);
                            $("#id_qr_stocker_bundle").val(data.id_qr_stocker_similar);
                            $("#act_costing_ws").val(data.ws);
                            $("#style").val(data.styleno);
                            $("#color").val(data.color);
                            $("#panel").val(data.panel);
                            $("#part").val(data.nama_part);

                            if (res.dataProcess) {
                                let dataProcess = res.dataProcess;

                                // populate select process list
                                let processList = objectGroupBy(dataProcess, ({process}) => process);
                                if (processList) {
                                    for (var key in processList) {
                                        if (processList[key].length > 0) {
                                            if (processList[key][0]['qty_reject'] > 0) {
                                                let opt = document.createElement("option");
                                                opt.text = key.replace('_', ' ').toUpperCase();
                                                opt.value = key;

                                                processListElement.append(opt);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: res.message
                        });
                    }
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });

            // reload stocker reject
            stockerRejectTableReload();
        }

        function clearForm()
        {
            $("#id_qr_stocker").val('-');
            $("#id_qr_stocker_bundle").val('-');
            $("#act_costing_ws").val('');
            $("#style").val('');
            $("#color").val('');
            $("#panel").val('');
            $("#part").val('');
            $("#process").val('-').trigger('change');
        }

        document.getElementById("stocker").addEventListener("keyup", function(event) {
            if (event.key === "Enter") {
                event.preventDefault();
                getStocker();
            }
        });

        // Stocker Reject Datatable
        let stockerRejectTable = $("#stocker-reject-table").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('get-stocker-reject-process') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.id_qr_stocker = $('#id_qr_stocker').val();
                    d.process = $('#process').val();
                },
            },
            columns: [
                {
                    data: 'id',
                    searchable: false
                },
                {
                    data: 'tanggal'
                },
                {
                    data: 'id_qr_stocker'
                },
                {
                    data: 'proses'
                },
                {
                    data: 'qty_reject',
                }
            ],
            columnDefs: [
                // Act Column
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        // conditional
                        return `<div class='d-flex gap-1 justify-content-center'> <a class='btn btn-primary btn-sm' href='{{ route("show-stocker-reject") }}/`+row.id+`/`+row.proses+`' data-bs-toggle='tooltip'><i class='fa fa-search-plus'></i></a> </div>`;
                    }
                },
                {
                    targets: [2],
                    render: (data, type, row, meta) => {
                        return `<span><b>`+row.id_qr_stocker+`</b>`+(row.id_qr_similar_stocker ? `, `+row.id_qr_similar_stocker : ``)+`</span>`
                    }
                },
                {
                    targets: '_all',
                    className: 'text-nowrap'
                }
            ]
        });

        // Stocker Reject Header Filter
        $('#stocker-reject-table thead tr').clone(true).appendTo('#stocker-reject-table thead');
        $('#stocker-reject-table thead tr:eq(1) th').each(function(i) {
            if (i != 0) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" style="width:100%"/>');

                $('input', this).on('keyup change', function() {
                    if (stockerRejectTable.column(i).search() !== this.value) {
                        stockerRejectTable
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        // Stocker Reject Table Reload
        function stockerRejectTableReload() {
            stockerRejectTable.ajax.reload();
        }
    </script>
@endsection
