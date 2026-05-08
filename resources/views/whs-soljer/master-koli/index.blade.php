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
            <h5 class="card-title fw-bold mb-0"> Master Koli</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-end align-items-end gap-3 mb-3">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <button type="button" class="btn btn-success btn-sm mb-3" id="btnCreate">
                        <i class="fa fa-plus"></i> New
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-hover table w-100">
                    <thead>
                        <tr>
                            <th>No. Koli</th>
                            <th>Kode Koli</th>
                            <th>User</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCreate" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('store-master-koli-whs-soljer') }}" method="POST"  onsubmit="submitForm(this, event)">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h5 class="modal-title">Create Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label>No Koli</label>
                            <input type="number" name="no_koli" id="no_koli" class="form-control" required>
                            @error('no_koli')
                                <small class="text-danger">
                                    {{ $message }}
                                </small>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Kode Koli</label>
                            <input type="text" name="kode_koli" id="kode_koli" class="form-control" readonly required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
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
            dropdownParent: $("#editMejaModal")
        })

        $('#btnCreate').click(function () {
            $('#modalCreate').modal('show');
        });

        $('#no_koli').on('input', function () {
            let noKoli = $(this).val();

            if(noKoli !== '') {
                $('#kode_koli').val('WHSKOLI.' + noKoli);
            } else {
                $('#kode_koli').val('');
            }
        });

    </script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            $("#tgl-awal").val(oneWeeksBeforeFull).trigger("change");

            $('#datatable').DataTable().ajax.reload(null, false);

            window.addEventListener("focus", () => {
                $('#datatable').DataTable().ajax.reload(null, false);
            });
        });

        let datatable = $("#datatable").DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            pageLength: 10,
            ajax: {
                url: '{{ route('master-koli-whs-soljer') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [
                {
                    data: 'no_koli'
                },
                {
                    data: 'kode_koli'
                },
                {
                    data: 'created_by_username'
                },
                {
                    data: 'id'
                },
            ],
            columnDefs: [
                {
                    targets: [3],
                    render: (data, type, row, meta) => {

                        let btnBarcode = `
                            <a 
                                href="{{ url('master-koli-whs-soljer/print-barcode') }}/${row.id}" 
                                target="_blank"
                                class="btn btn-warning btn-sm"
                            >
                                <i class="fa-solid fa-barcode"></i>
                            </a>
                        `;

                        let btnDelete = `
                            <button type="button" class="btn btn-sm btn-danger btn-delete"
                                data-id="${row.id}">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        `;

                        return `
                            <div class="d-flex gap-1 justify-content-center">
                                ${btnBarcode}
                                ${btnDelete}
                            </div>
                        `;
                    }
                },
                {
                    targets: '_all',
                    className: 'text-nowrap'
                }
            ]
        });

        function dataTableReload() {
            datatable.ajax.reload();
        }

        $(document).on('click', '.btn-delete', function () {
            let id = $(this).data('id');
            let url = "{{ url('master-koli-whs-soljer/delete') }}/" + id;

            Swal.fire({
                title: 'Yakin delete data ini?',
                text: "Data yang didelete tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: 'PUT'
                        },
                        success: function () {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Data berhasil didelete',
                                timer: 1500,
                                showConfirmButton: false
                            });

                            // kalau pakai datatable
                            $('#datatable').DataTable().ajax.reload();
                        }
                    });
                }
            });
        });

        
    </script>
@endsection
