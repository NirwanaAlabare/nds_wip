@extends('layouts.index')

@section('custom-link')
    {{-- <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}"> --}}

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/fixedColumns.bootstrap4.min.css') }}">
    <!-- jQuery -->
    <script src="{{ asset('plugins/datatables 2.0/jquery-3.3.1.js') }}"></script>


    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .select2-container--bootstrap4 .select2-selection--single {
            height: 31px !important;
            padding: 0.25rem 0.5rem !important;
            font-size: 0.875rem !important;
        }

        .select2-container--bootstrap4 .select2-selection__rendered {
            line-height: 1.5 !important;
        }
    </style>

    <style>
        /* Responsive Scanner Box */
        .scanner-box {
            width: 100%;
            max-width: 320px;
            aspect-ratio: 1 / 1;
            margin: 0 auto;
        }

        @media (max-width: 400px) {
            .scanner-box {
                max-width: 260px;
            }
        }

        .scanner-box-fabric {
            width: 100%;
            max-width: 320px;
            aspect-ratio: 1 / 1;
            margin: 0 auto;
        }

        @media (max-width: 400px) {
            .scanner-box-fabric {
                max-width: 260px;
            }
        }
    </style>
@endsection

@section('content')
    <!-- SCAN OPERATOR MODAL -->
    <div class="modal fade" id="startFormModal" tabindex="-1" aria-labelledby="startFormModalLabel" aria-hidden="true">
        <div class="modal-dialog"> <!-- Smaller, scrollable modal -->
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="startFormModalLabel">
                        <i class="fas fa-qrcode"></i> Scan ID Operator
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">

                    <!-- Input Group -->
                    <div class="mb-3">
                        <label for="txtqr_operator" class="form-label label-input">
                            <small><b>QR Code</b></small>
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control border-input" name="txtqr_operator"
                                id="txtqr_operator" autocomplete="off" enterkeyhint="go" autofocus>
                            <span class="input-group-text bg-light text-muted py-0 px-2">
                                <small>Scan</small>
                            </span>
                            <span id="btnScan" onclick="check_operator()"
                                class="input-group-text bg-light text-muted py-0 px-2" role="button"
                                style="cursor: pointer;">
                                <small>Scan</small>
                            </span>
                        </div>
                    </div>

                    <!-- QR Scanner -->
                    <div class="d-flex justify-content-center">
                        <div id="reader" class="scanner-box"></div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                        onclick="stopScannerOperator()">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- SCAN FABRIC MODAL -->
    <div class="modal fade" id="startFabricModal" tabindex="-1" aria-labelledby="startFabricModalLabel" aria-hidden="true">
        <div class="modal-dialog"> <!-- Smaller, scrollable modal -->
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="startFabricModalLabel">
                        <i class="fas fa-qrcode"></i> Scan Barcode Fabric
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">

                    <!-- Input Group -->
                    <div class="mb-3">
                        <label for="txtbarcode_scan" class="form-label label-input">
                            <small><b>Barcode</b></small>
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control border-input" name="txtbarcode_scan"
                                id="txtbarcode_scan" autocomplete="off" enterkeyhint="go" autofocus>
                            <span class="input-group-text bg-light text-muted py-0 px-2">
                                <small>Scan</small>
                            </span>
                            <span id="btnScanFabric" onclick="check_barcode()"
                                class="input-group-text bg-light text-muted py-0 px-2" role="button"
                                style="cursor: pointer;">
                                <small>Scan</small>
                            </span>
                        </div>
                    </div>

                    <!-- QR Scanner -->
                    <div class="d-flex justify-content-center">
                        <div id="reader_fabric" class="scanner-box-fabric"></div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                        onclick="stopScannerFabric()">Close</button>
                </div>
            </div>
        </div>
    </div>



    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Input Fabric Relaxation</h5>
        </div>
        <div class="card-body pb-0">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="txtstart"><small><b>Start :</b></small></label>
                    <input type="hidden" id="txtid" name="txtid" value="{{ $id }}"
                        class="form-control form-control-sm border-primary"readonly>
                    <input type="text" id="txtstart" name="txtstart" value="{{ $start_form }}"
                        class="form-control form-control-sm border-primary"readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtfinish"><small><b>Finish :</b></small></label>
                    <input type="text" id="txtfinish" name="txtfinish" value="{{ $finish_form }}"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtnik"><small><b>NIK :</b></small></label>
                    <input type="hidden" id="txtenroll_id" name="txtenroll_id" value="{{ $enroll_id }}"
                        class="form-control form-control-sm border-primary" readonly>
                    <input type="text" id="txtnik" name="txtnik" value="{{ $nik }}"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtoperator"><small><b>Operator :</b></small></label>
                    <input type="text" id="txtoperator" name="txtoperator" value="{{ $operator }}"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="txtno_mesin"><small><b>No. Mesin :</b></small></label>
                    <input type="text" id="txtno_mesin" name="txtno_mesin" value ="{{ $no_mesin }}"
                        class="form-control form-control-sm border-primary"readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtno_form"><small><b>No. Form :</b></small></label>
                    <input type="text" id="txtno_form" name="txtno_form" value="{{ $no_form }}"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txttgl_bpb"><small><b>Tgl BPB :</b></small></label>
                    <input type="text" id="txttgl_bpb" name="txttgl_bpb" value="{{ $tgl_dok }}"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtno_pl"><small><b>No. PL :</b></small></label>
                    <input type="text" id="txtno_pl" name="txtno_pl" value="{{ $no_invoice }}"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
            </div>


            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="txtbuyer"><small><b>Buyer :</b></small></label>
                    <input type="text" id="txtbuyer" name="txtbuyer" value="{{ $buyer }}"
                        class="form-control form-control-sm border-primary"readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtws"><small><b>WS :</b></small></label>
                    <input type="text" id="txtws" name="txtws" value="{{ $kpno }}"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtstyle"><small><b>Style :</b></small></label>
                    <input type="text" id="txtstyle" name="txtstyle" value="{{ $styleno }}"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtcolor"><small><b>Color :</b></small></label>
                    <input type="text" id="txtcolor" name="txtcolor" value="{{ $color }}"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="txtno_roll"><small><b>No Roll :</b></small></label>
                    <input type="text" id="txtno_roll" name="txtno_roll" value="{{ $no_roll_buyer }}"
                        class="form-control form-control-sm border-primary"readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtno_lot"><small><b>No Lot :</b></small></label>
                    <input type="text" id="txtno_lot" name="txtno_lot" value="{{ $no_lot }}"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtbarcode"><small><b>Barcode :</b></small></label>
                    <input type="text" id="txtbarcode" name="txtbarcode" value="{{ $barcode }}"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtid_item"><small><b>Color :</b></small></label>
                    <input type="text" id="txtid_item" name="txtid_item" value="{{ $color }}"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-9">
                    <label for="txtitem_desc"><small><b>Detail Item :</b></small></label>
                    <input type="text" id="txtitem_desc" name="txtitem_desc" value="{{ $itemdesc }}"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtdurasi_relax"><small><b>Durasi Relax:</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="number" id="txtdurasi_relax" name="txtdurasi_relax" value="{{ $durasi_relax }}"
                            class="form-control border-primary" readonly>
                        <span class="input-group-text">jam</span>
                    </div>
                </div>
            </div>
            <div class="row mb-4" id="btnFinishContainer">
                <div class="col-md-12 d-flex justify-content-center">
                    <button type="button" id="btnFinishForm" onclick="finish_form()"
                        class="btn btn-success px-5 py-2 shadow rounded-pill">
                        <i class="fas fa-check-circle me-2"></i> Finish Form
                    </button>
                </div>
            </div>

            <div style="text-align: center; padding-top: 20px;">
                <div style="display: inline-block; max-width: 600px; width: 100%;">
                    <table class="table table-bordered table-striped table-sm" style="width: 100%; margin: 0 auto;">
                        <thead class="table">
                            <tr>
                                <th colspan="2">Start</th>
                                <th colspan="2">Finish</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>(Date)</strong></td>
                                <td><strong>(Time)</strong></td>
                                <td><strong>(Date)</strong></td>
                                <td><strong>(Time)</strong></td>
                            </tr>
                            <tr>
                                <td>{{ $finish_date }}</td>
                                <td>{{ $finish_time }}</td>
                                <td>{{ $finish_relax_date }}</td>
                                <td>{{ $finish_relax_time }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        $(document).ready(function() {
            let finishFormVal = $('#txtfinish').val();

            if (!finishFormVal || finishFormVal.trim() === "") {
                // Show button
                $('#btnFinishContainer').show();
            } else {
                // Hide button
                $('#btnFinishContainer').hide();
            }
        });


        function finish_form() {
            let id = $('#txtid').val();

            // Build summary before save
            Swal.fire({
                title: 'Perhatian!',
                html: `
        <p>Apabila Anda melanjutkan, data ini <b>tidak akan bisa diubah kembali</b>.</p>
        <hr>
        Apakah Anda yakin ingin menyelesaikan dan menyimpan data ini?
    `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Selesaikan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send data only if confirmed
                    $.ajax({
                        type: "POST",
                        url: '{{ route('finish_form_fabric_relaxation') }}',
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: id
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Form Disimpan!',
                                html: `
            <hr>
            <p>Form Telah Selesai.</p>
        `
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            console.error('Save Error:', xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Gagal menyimpan data.'
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
