@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
    {{-- <h5 class="text-sb">Trolley</h5> --}}
    {{-- <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title">Scan Qr</h5>
        </div>
        <div class="card-body">
            <div id="reader"></div>
        </div>
    </div> --}}
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold"> <i class="fas fa-plus-square fa-sm"></i> Master Trolley</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a href="{{ route('create-trolley') }}" class="btn btn-success btn-sm">
                    <i class="fa fa-plus"></i> Baru
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered w-100" id="datatable-trolley">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Trolley</th>
                            <th>Line</th>
                            <th>Act</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editMasterTrolleyModal" tabindex="-1" aria-labelledby="editMasterTrolleyLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('update-trolley') }}" method="post" onsubmit="submitForm(this, event)">
                    @method('PUT')
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5" id="editMasterTrolleyModal">Ubah Data Master Trolley</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Kode Trolley</label>
                            <input type="text" class="form-control" name="edit_kode" id="edit_kode" value="" readonly>
                            <div class="form-text text-danger d-none" id="edit_kode_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Trolley</label>
                            <input type="text" class="form-control" name="edit_nama_trolley" id="edit_nama_trolley" value="">
                            <div class="form-text text-danger d-none" id="edit_nama_trolley_error"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
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

    <script>
        let datatableTrolley = $("#datatable-trolley").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('trolley') }}',
            },
            columns: [
                {
                    data: 'kode',
                },
                {
                    data: 'nama_trolley',
                },
                {
                    data: 'line',
                },
                {
                    data: 'id'
                },
            ],
            columnDefs: [
                {
                    targets: [3],
                    className: "align-middle",
                    render: (data, type, row, meta) => {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#editMasterTrolleyModal" onclick='editData(` + JSON.stringify(row) + `, "editMasterTrolleyModal", [{"function" : "datatableTrolleyReload()"}]);'>
                                    <i class='fa fa-edit'></i>
                                </a>
                                <a class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route("destroy-trolley") }}/`+row['id']+`' onclick='deleteData(this);'>
                                    <i class='fa fa-trash'></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-secondary" data='`+JSON.stringify(row)+`' data-url='{{ route("print-trolley") }}/`+row['id']+`' onclick="printTrolley(this);">
                                    <i class="fa fa-print fa-s"></i>
                                </button>
                            </div>
                        `;
                    }
                },
            ]
        });

        function datatableTrolleyReload() {
            datatableTrolley.ajax.reload();
        }

        function printTrolley(e) {
            let data = JSON.parse(e.getAttribute('data'));

            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: e.getAttribute('data-url'),
                type: 'post',
                processData: false,
                contentType: false,
                data: data,
                xhrFields:
                {
                    responseType: 'blob'
                },
                success: function(res) {
                    if (res) {
                        console.log(res);

                        var blob = new Blob([res], {type: 'application/pdf'});
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = data.nama_trolley+".pdf";
                        link.click();

                        swal.close();

                        // window.location.reload();
                    }
                }
            });
        }
    </script>
@endsection
