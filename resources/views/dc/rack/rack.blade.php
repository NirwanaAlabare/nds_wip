@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card">
        <div class="card-header bg-sb text-light">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-plus-square fa-sm"></i> Master Rak</h5>
        </div>
        <div class="card-body">
            <div class="d-flex gap-2 mb-3">
                <a href="{{ route('create-rack') }}" type="button" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i>
                    Baru
                </a>
                <button type="button" class="btn btn-secondary btn-sm" onclick="printAllRack()">
                    <i class="fas fa-print"></i>
                    Print Selected
                </button>
            </div>
            {{-- <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Awal</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Akhir</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <button class="btn btn-primary btn-sm" onclick="filterTable()">Tampilkan</button>
                </div>
            </div> --}}
            <div class="table-responsive">
                <table id="datatable-rack" class="table table-bordered table w-100">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="check-all-rack"></th>
                            <th>Kode Rak</th>
                            <th>Nama Rak</th>
                            <th>Nama Detail Rak</th>
                            <th>Jumlah Ruang</th>
                            <th class="text-center align-bottom">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editMasterRackModal" tabindex="-1" aria-labelledby="editMasterRackLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('update-rack') }}" method="post" onsubmit="submitForm(this, event)">
                    @method('PUT')
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5" id="editMasterRackLabel">Ubah Data Master Rack</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Kode Rak</label>
                            <input type="text" class="form-control" name="edit_kode" id="edit_kode" value="" readonly>
                            <div class="form-text text-danger d-none" id="edit_kode_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Rak</label>
                            <input type="text" class="form-control" name="edit_nama_rak" id="edit_nama_rak" value="">
                            <div class="form-text text-danger d-none" id="edit_nama_rak_error"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah Ruang</label>
                            <input type="number" class="form-control" name="edit_total_ruang" id="edit_total_ruang" value="">
                            <div class="form-text text-danger d-none" id="edit_total_ruang_error"></div>
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
        let checkedRackIds = [];

        let datatableRak = $("#datatable-rack").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('rack') }}',
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'kode',
                },
                {
                    data: 'nama_rak',
                },
                {
                    data: 'nama_detail_rak',
                },
                {
                    data: 'total_ruang',
                    searchable: false,
                },
                {
                    data: 'id'
                },
            ],
            drawCallback: function () {
                $('#datatable-rack tbody .rack-check').each(function () {
                    if (checkedRackIds.includes(this.value)) {
                        $(this).prop('checked', true);
                    }
                });
            },
            columnDefs: [
                {
                    targets: [0],
                    className: "align-middle text-center",
                    render: (data, type, row, meta) => {
                        return `<input type="checkbox" class="rack-check" value="${row['id']}">`;
                    }
                },
                {
                    targets: [5],
                    className: "align-middle",
                    render: (data, type, row, meta) => {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#editMasterRackModal" onclick='editData(` + JSON.stringify(row) + `, "editMasterRackModal", [{"function" : "datatableRakReload()"}]);'>
                                    <i class='fa fa-edit'></i>
                                </a>
                                <a class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route("destroy-rack") }}/`+row['id']+`' onclick='deleteData(this);'>
                                    <i class='fa fa-trash'></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-secondary" data='`+JSON.stringify(row)+`' data-url='{{ route("print-rack") }}/`+row['id']+`' onclick="printRack(this);">
                                    <i class="fa fa-print fa-s"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
        });

        function checkAllRack(checked) {
            if (checked) {
                $.get('{{ route('get-all-rack-ids') }}', function (ids) {
                    checkedRackIds = ids.map(String);
                    $('#datatable-rack tbody .rack-check').prop('checked', true);
                });
            } else {
                checkedRackIds = [];
                $('#datatable-rack tbody .rack-check').prop('checked', false);
            }
        }

        $('#check-all-rack').on('change', function () {
            checkAllRack(this.checked);
        });

        $('#datatable-rack tbody').on('change', '.rack-check', function () {
            const val = this.value;
            if (this.checked) {
                if (!checkedRackIds.includes(val)) checkedRackIds.push(val);
            } else {
                checkedRackIds = checkedRackIds.filter(v => v !== val);
                $('#check-all-rack').prop('checked', false);
            }
        });

        function printAllRack() {
            if (checkedRackIds.length === 0) {
                Swal.fire('Perhatian', 'Pilih minimal satu rak terlebih dahulu.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => { Swal.showLoading(); },
                allowOutsideClick: false,
            });

            $.ajax({
                url: '{{ route('print-all-rack') }}',
                type: 'post',
                data: { ids: checkedRackIds, _token: '{{ csrf_token() }}' },
                xhrFields: { responseType: 'blob' },
                success: function (res) {
                    var blob = new Blob([res], { type: 'application/pdf' });
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'rack-selected.pdf';
                    link.click();
                    Swal.close();
                },
                error: function () {
                    Swal.fire('Error', 'Gagal mengunduh PDF.', 'error');
                }
            });
        }

        function datatableRakReload() {
            datatableRak.ajax.reload()
        }

        function printRack(e) {
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
                        link.download = data.nama_rak+".pdf";
                        link.click();

                        swal.close();

                        // window.location.reload();
                    }
                }
            });
        }
    </script>
@endsection
