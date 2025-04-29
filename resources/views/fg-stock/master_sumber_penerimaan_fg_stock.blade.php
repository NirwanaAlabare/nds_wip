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
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <form action="{{ route('store-master-sumber-penerimaan') }}" method="post" onsubmit="submitForm(this, event)"
            name='form' id='form'>
            @method('POST')
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h3 class="modal-title fs-5">Tambah Sumber Penerimaan</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Sumber Penerimaan :</label>
                            <input type='text' class='form-control form-control-sm' id="txtsumber" name="txtsumber"
                                value="" autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal"><i
                                class="fas fa-times-circle"></i> Tutup</button>
                        <button type="submit" class="btn btn-outline-success"><i class="fas fa-check"></i> Simpan </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"> <i class="fas fa-indent"></i> Master Sumber Penerimaan</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#exampleModal"
                    onclick="reset()"><i class="fas fa-plus"></i> Baru</button>
            </div>


            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped 100">
                    <thead>
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>No</th>
                            <th>Sumber</th>
                            <th>Act</th>
                        </tr>
                    </thead>
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
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        function reset() {
            $("#form").trigger("reset");
            $("#txtsumber").focus();
        }
    </script>
    <script>
        let datatable = $("#datatable").DataTable({
            ordering: true,
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            ajax: {
                url: '{{ route('master-sumber-penerimaan') }}',
            },
            "fnCreatedRow": function(row, data, index) {
                $('td', row).eq(0).html(index + 1);
            },
            columns: [{
                    data: 'sumber'
                },
                {
                    data: 'sumber'

                },
            ],
            columnDefs: [{
                    targets: [2],
                    render: (data, type, row, meta) => {
                        return `
                    <div
                    class='d-flex gap-1 justify-content-center'>
                    <a class='btn btn-warning btn-sm' data-bs-toggle='tooltip' onclick='notif()'><i class='fas fa-edit'></i></a>
                    <a class='btn btn-success btn-sm' data-bs-toggle='tooltip' onclick='notif()'><i class='fas fa-lock'></i></a>
                    </div>
                        `;
                    }
                },
                // <a class='btn btn-warning btn-sm' href='{{ route('create-dc-in') }}/` +
            //             row.id +
            //             `' data-bs-toggle='tooltip'><i class='fas fa-edit'></i></a>
                {
                    "className": "dt-left",
                    "targets": "_all"
                },



            ]
        });
    </script>
@endsection
