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
@endsection

@section('content')
    <!-- Add Critical Defect Modal -->
    <div class="modal fade" id="addFoundingIssueModal" tabindex="-1" aria-labelledby="addFoundingIssueModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="addFoundingIssueForm">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header bg-sb">
                        <h5 class="modal-title" id="addFoundingIssueModalLabel">
                            <i class="fas fa-plus-circle"></i> New Founding Issue
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="foundingissue" class="form-label">Founding Issue</label>
                            <input type="text" class="form-control" id="foundingissue" required>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="saveButton"
                            onclick="addFoundingIssue();">Save</button>

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>

                </div>
            </form>
        </div>
    </div>


    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Master Founding Issue</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-start mb-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFoundingIssueModal">
                    <i class="fas fa-plus"></i> Add Founding Issue
                </button>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered w-100 text-nowrap">
                    <thead class="bg-sb">
                        <tr style="text-align:center; vertical-align:middle">
                            <th scope="col">Founding Issue</th>
                            <th scope="col">Created At</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    {{-- <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script> --}}
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
        function dataTableReload() {
            datatable.ajax.reload();
        }

        $(document).ready(function() {
            dataTableReload();
            $('#addFoundingIssueModal').on('show.bs.modal', function() {
                // Clear the critical defect input
                $('#foundingissue').val('');
            });


        })


        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            paging: false,
            searching: true,
            scrollY: true,
            scrollX: true,
            scrollCollapse: false,
            ajax: {
                url: '{{ route('qc_inspect_master_founding_issue_show') }}',
            },
            columns: [{
                    data: 'founding_issue'
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `
      <span style="font-weight:600; vertical-align: middle;">${row.created_by}</span>
      <span style="color: #666; font-size: 0.9em; margin-left: 6px; vertical-align: middle;">&bull; ${row.created_at}</span>
    `;
                    }
                }

            ],
        });


        function addFoundingIssue() {
            let foundingissue = $('#foundingissue').val();

            // Trim values to remove whitespace
            if (!foundingissue || foundingissue.trim() === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Fields',
                    text: 'fields are required and cannot be empty or whitespace.',
                });
                return;
            }

            // Disable the Save button to prevent multiple clicks
            let $btn = $('#saveButton'); // Add id="saveButton" to your Save button
            $btn.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: '{{ route('qc_inspect_master_founding_issue_add') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    foundingissue: foundingissue
                },
                success: function(response) {
                    // Reset form and hide modal
                    $('#addFoundingIssueForm')[0].reset();
                    $('#addFoundingIssueModal').modal('hide');
                    dataTableReload();
                    // SweetAlert success notification
                    Swal.fire({
                        icon: 'success',
                        title: 'Founding Issue Added',
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
