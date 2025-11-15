@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- DataTables CSS -->
    {{-- <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/fixedColumns.bootstrap4.min.css') }}"> --}}
    <!-- jQuery -->
    <script src="{{ asset('plugins/datatables 2.0/jquery-3.3.1.js') }}"></script>


    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Master Proses</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <button type="button" class="btn btn-outline-primary position-relative btn-sm" data-bs-toggle="modal"
                    data-bs-target="#CreateModal">
                    <i class="fas fa-plus"></i>
                    New
                </button>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">Process Name</th>
                            <th scope="col" class="text-center align-middle">Class</th>
                            <th scope="col" class="text-center align-middle">SMV</th>
                            <th scope="col" class="text-center align-middle">AMV</th>
                            <th scope="col" class="text-center align-middle">Machine Type</th>
                            <th scope="col" class="text-center align-middle">Remark</th>
                            <th scope="col" class="text-center align-middle">User</th>
                            <th scope="col" class="text-center align-middle">Update at</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="CreateModal" tabindex="-1" aria-labelledby="CreateModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-xl"> <!-- ubah modal-lg ke modal-sm/md sesuai kebutuhan -->
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="CreateModalLabel">New Master Process</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Isi form di sini -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="txtname"><small><b>Process Name :</b></small></label>
                            <input type="text" id="txtname" name="txtname" class="form-control form-control-sm"
                                value="">
                        </div>
                        <div class="col-md-4">
                            <label for="cboclass" class="form-label"><small><b>Class :</b></small></label>
                            <select id="cboclass" name="cboclass"
                                class="form-control form-control-sm select2bs4 border-primary" style="width: 100%;">
                                <option value="">-- Select Class --</option>
                                <option value="Operator">Operator</option>
                                <option value="Helper">Helper</option>
                                <option value="Steam">Steam</option>
                                <option value="QC">QC</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="cbotype"><small><b>Machine Type :</b></small></label>
                            <input type="text" id="cbotype" name="cbotype" class="form-control form-control-sm"
                                value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="txtsmv"><small><b>SMV:</b></small></label>
                            <input type="number" id="txtsmv" name="txtsmv" class="form-control form-control-sm"
                                value="">
                        </div>
                        <div class="col-md-4">
                            <label for="txtamv"><small><b>AMV :</b></small></label>
                            <input type="number" id="txtamv" name="txtamv" class="form-control form-control-sm"
                                value="">
                        </div>
                        <div class="col-md-4">
                            <label for="txtremark"><small><b>Remark :</b></small></label>
                            <input type="text" id="txtremark" name="txtremark" class="form-control form-control-sm"
                                value="">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="saveButton"
                        onclick="save_master_process();">Save</button>

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
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
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    {{-- <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script> --}}
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
            width: 'resolve' // Ensures it respects the 100% width from inline style or Bootstrap
        });
        // Now set height and font-size on the Select2 container after init
        $('.select2-container--bootstrap4 .select2-selection--single').css({
            'height': '30px', // your desired height
            'font-size': '12px', // your desired font size
            'line-height': '30px' // vertically center text
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        function dataTableReload() {
            datatable.ajax.reload();
        }


        $(document).ready(function() {
            dataTableReload();
            $('#CreateModal').on('show.bs.modal', function() {
                // Clear text
                $('#txtname').val('');
                $('#txtsmv').val('');
                $('#txtamv').val('');
                $('#txtremark').val('');
                $('#cbotype').val('');
                // Optional: Also clear select2 (if using select2)
                $('#cboclass').val(null).trigger('change');
            });
        })

        let datatable = $("#datatable").DataTable({
            ordering: false,
            responsive: true,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: true,
            scrollX: true,
            scrollCollapse: false,
            ajax: {
                url: '{{ route('IE_master_process') }}',
            },
            columns: [{
                    data: 'nm_process'
                }, // Process Name
                {
                    data: 'class'
                }, // Class
                {
                    data: 'smv'
                }, // SMV
                {
                    data: 'amv'
                }, // AMV
                {
                    data: 'machine_type'
                }, // Machine Type
                {
                    data: 'remark'
                }, // Remark
                {
                    data: 'created_by'
                }, // User
                {
                    data: 'updated_at'
                } // Update at
            ],


        });



        function save_master_process() {
            let process_name = $('#txtname').val();
            let class_name = $('#cboclass').val();
            let cbotype = $('#cbotype').val();
            let smv = $('#txtsmv').val();
            let amv = $('#txtamv').val();
            let remark = $('#txtremark').val();

            // Disable the Save button to prevent multiple clicks
            let $btn = $('#saveButton'); // Add id="saveButton" to your Save button
            $btn.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: '{{ route('IE_save_master_process') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    process_name: process_name,
                    class_name: class_name,
                    cbotype: cbotype,
                    smv: smv,
                    amv: amv,
                    remark: remark
                },
                success: function(response) {
                    // SweetAlert success notification
                    // Clear text
                    $('#txtname').val('');
                    $('#txtsmv').val('');
                    $('#txtamv').val('');
                    $('#txtremark').val('');
                    $('#cbotype').val('');
                    // Optional: Also clear select2 (if using select2)
                    $('#cboclass').val(null).trigger('change');


                    Swal.fire({
                        icon: 'success',
                        title: 'Data Added',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Something went wrong while saving.',
                    });
                    dataTableReload();
                },
                complete: function() {
                    // Re-enable the Save button after request completes
                    $btn.prop('disabled', false);
                }
            });
        }
    </script>
@endsection
