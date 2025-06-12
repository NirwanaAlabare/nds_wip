@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        .row-selected {
            background-color: #d1ecf1 !important;
            /* Checkbox highlight */
        }
    </style>
@endsection

@section('content')
    <form action="{{ route('store_so_mesin') }}" method="post" id="store_so_mesin" name='form' onsubmit="return false;">
        @csrf
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0">Stock Opname Mesin</h5>
            </div>
            <div class="card-body">
                <div class="row justify-content-center align-items-end">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label label-input"><small><b>Lokasi</b></small></label>
                            <select class="form-control form-control-sm select2bs4" id="cbolok" name="cbolok"
                                onchange="dataTableReload()">
                                <option value="" selected disabled>-- Pilih Lokasi --</option>
                                @foreach ($data_lokasi as $dl)
                                    <option value="{{ $dl->isi }}">
                                        {{ $dl->tampil }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label label-input"><small><b>QR Code</b></small></label>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-sm border-input" name="txtqr"
                                    id="txtqr" autocomplete="off" enterkeyhint="go"
                                    onkeyup="if (event.keyCode == 13) { event.preventDefault(); document.getElementById('scan_qr_c').click(); }"
                                    autofocus>
                                {{-- <input type="button" class="btn btn-sm btn-primary" value="Scan Line" /> --}}
                                {{-- style="display: none;" --}}
                                <button class="btn btn-sm btn-primary" type="button" id="scan_qr_c"
                                    onclick="scan_qr();">Scan</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-2">
                        <div></div>
                    </div>
                    <div class="col-8">
                        <div id="reader"></div>
                    </div>
                    <div class="col-2">
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">List Mesin</h5>
        </div>
        <div class="card-body">
            <button id="openMultiUpdateModal" class="btn btn-sb mb-3">
                Update Keterangan (Multiple)
            </button>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered  display nowrap" style="width: 100%">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>ID</th>
                            <th>ID QR</th>
                            <th>Keterangan</th>
                            <th>Tgl SO</th>
                            <th>Lokasi</th>
                            <th>Jenis Mesin</th>
                            <th>Brand</th>
                            <th>Tipe</th>
                            <th>Serial No</th>
                            <th>User</th>
                            <th>Waktu Scan</th>
                        </tr>
                    </thead>
                </table>
                <div id="totalRows" class="mt-2">Total rows: 0</div> <!-- place for count -->
            </div>
        </div>
    </div>

    <div class="modal fade" id="multiUpdateModal" tabindex="-1" role="dialog" aria-labelledby="multiUpdateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Keterangan for Selected Rows</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="selectedList"></div>
                    <div class="form-group mt-3">
                        <label for="multiKeteranganSelect">Keterangan</label>
                        <select id="multiKeteranganSelect" class="form-control select2bs4" style="width: 100%;">
                            <option value="BAIK/GOOD">BAIK/GOOD</option>
                            <option value="DALAM PERBAIKAN/REPAIRING">DALAM PERBAIKAN/REPAIRING</option>
                            <option value="RUSAK DAPAT DIPERBAIKI/REPAIRABLE">RUSAK DAPAT DIPERBAIKI/REPAIRABLE</option>
                            <option value="RUSAK MATI TOTAL/NON REPAIRABLE">RUSAK MATI TOTAL/NON REPAIRABLE</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="confirmMultiUpdate" class="btn btn-success">Update All</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>

                </div>
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
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        });
        $(document).ready(function() {
            initScan();
        })

        function dataTableReload() {
            datatable.ajax.reload();
        }
        // When a row checkbox is toggled
        $('#datatable').on('change', '.row-checkbox', function() {
            const row = $(this).closest('tr');
            row.toggleClass('row-selected', this.checked);
        });

        // Optional: "Select All" checkbox also applies the style
        $('#select-all').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.row-checkbox').each(function() {
                this.checked = isChecked;
                const row = $(this).closest('tr');
                row.toggleClass('row-selected', isChecked);
            });
        });

        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            info: false,
            paging: false,
            scrollX: true,
            ajax: {
                url: '{{ route('getdata_so_mesin') }}',
                data: function(d) {
                    d.cbolok = $('#cbolok').val();
                },
                dataSrc: function(json) {
                    return json.data;
                }
            },
            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `<input type="checkbox" class="row-checkbox" data-id="${row.id_so}">`;
                    }
                },
                {
                    data: 'id_so',
                    visible: false
                },
                {
                    data: 'id_qr'
                },
                {
                    data: 'ket'
                },
                {
                    data: 'tgl_so'
                },
                {
                    data: 'lokasi'
                },
                {
                    data: 'jenis_mesin'
                },
                {
                    data: 'brand'
                },
                {
                    data: 'tipe_mesin'
                },
                {
                    data: 'serial_no'
                },
                {
                    data: 'created_by'
                },
                {
                    data: 'created_at'
                }
            ]
        });
        datatable.on('draw', function() {
            // recordsTotal comes from the server in the response
            let info = datatable.page.info();
            // info.recordsTotal = total records from server
            $('#totalRows').text('Total rows: ' + info.recordsTotal);
        });

        $('#openMultiUpdateModal').on('click', function() {
            let selectedRows = [];
            $('#selectedList').empty();

            $('.row-checkbox:checked').each(function() {
                const row = datatable.row($(this).closest('tr')).data();
                selectedRows.push(row.id_so); // Save only the ID
                $('#selectedList').append(`
            <p><strong>${row.jenis_mesin}</strong> - ${row.serial_no} (${row.lokasi})</p>
        `);
            });

            if (selectedRows.length === 0) {
                Swal.fire('No rows selected!', 'Please check at least one row.', 'warning');
                return;
            }

            // Store for later use
            window.selectedMultiIds = selectedRows;

            $('#multiKeteranganSelect').val('').trigger('change');
            $('#multiUpdateModal').modal('show');
        });

        $('#confirmMultiUpdate').on('click', function() {
            const keterangan = $('#multiKeteranganSelect').val();
            const ids = window.selectedMultiIds || [];

            if (!keterangan) {
                Swal.fire('Please select Keterangan.', '', 'warning');
                return;
            }

            $.ajax({
                url: '{{ route('update_ket_so_mesin') }}',
                method: 'POST',
                data: {
                    id_so_list: ids,
                    keterangan: keterangan,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire('Success', response.message, 'success');
                    $('#multiUpdateModal').modal('hide');
                    datatable.ajax.reload(null, false);
                },
                error: function() {
                    Swal.fire('Error', 'Update failed.', 'error');
                }
            });
        });

        $('#multiKeteranganSelect').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#multiUpdateModal')
        });

        $('#multiUpdateModal .btn-secondary').on('click', function() {
            $('#multiUpdateModal').modal('hide');
        });



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

                    document.getElementById('txtqr').value = decodedText;

                    scan_qr();

                    html5QrcodeScanner.clear();
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


        function scan_qr() {
            let btn = $("#scan_qr_c");
            btn.prop('disabled', true);

            let txtqr = $("#txtqr").val();
            let cbolok = $("#cbolok").val();

            if (!cbolok) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Lokasi belum dipilih!',
                    text: 'Silakan pilih lokasi terlebih dahulu sebelum scan.',
                    showConfirmButton: true
                });
                $("#txtqr").val('');
                btn.prop('disabled', false);
                return;
            }

            $.ajax({
                type: "POST",
                url: '{{ route('store_so_mesin') }}',
                data: {
                    txtqr: txtqr,
                    cbolok: cbolok,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    dataTableReload();
                    Swal.fire({
                        icon: res.icon,
                        title: res.msg,
                        showCancelButton: false,
                        showConfirmButton: true,
                        timer: res.timer,
                        timerProgressBar: res.prog
                    })
                    $("#txtqr").val('');
                    btn.prop('disabled', false);
                    initScan();
                },
                error: function(xhr, status, error) {
                    console.error("Error saving scan:", error);
                    btn.prop('disabled', false);
                }
            });
        }
    </script>
@endsection
