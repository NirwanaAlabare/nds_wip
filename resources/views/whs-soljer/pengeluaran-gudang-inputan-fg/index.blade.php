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
            <h5 class="card-title fw-bold mb-0"> Pengeluaran Gudang Inputan (FG)</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div>
                        <label class="form-label"><small>Tanggal Awal</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal">
                    </div>
                    <div>
                        <label class="form-label"><small>Tanggal Akhir</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}">
                    </div>
                    <div>
                        <button class="btn btn-primary btn-sm" onclick="dataTableReload()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div class="d-flex align-items-end gap-3 mb-3">
                    <a href="{{ route('create-pengeluaran-gudang-inputan-fg') }}" class="btn btn-success btn-sm mb-3"><i class="fa fa-plus"></i> New</a>
                    {{-- <div class="mb-3">
                        <button class="btn btn-success btn-sm" onclick="exportExcel()"><i class="fa fa-file-excel"></i></button>
                    </div> --}}
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-hover table w-100">
                    <thead>
                        <tr>
                            <th>No. BPB</th>
                            <th>Tgl BPB</th>
                            <th>Qty Out</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
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
            dropdownParent: $("#editMejaModal")
        })
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            $("#tgl-awal").val(oneWeeksBeforeFull).trigger("change");

            window.addEventListener("focus", () => {
                $('#datatable').DataTable().ajax.reload(null, false);
            });
        });

        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            if (i != 5) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm"/>');

                $('input', this).on('keyup change', function() {
                    if (datatable.column(i).search() !== this.value) {
                        datatable
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        let datatable = $("#datatable").DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            pageLength: 10,
            ajax: {
                url: '{{ route('pengeluaran-gudang-inputan-fg') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [
                {
                    data: 'no_bpb'
                },
                {
                    data: 'tgl_bpb'
                },
                {
                    data: 'total_qty',
                    className: 'text-end',
                    render: function(data, type, row) {
                        return parseFloat(data).toFixed(2);
                    }
                },
                {
                    data: 'status'
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
                    targets: [5],
                    render: (data, type, row, meta) => {

                        let btnEdit = '';
                        let btnDelete = '';
                        let btnPrint = '';
                        let btnBarcode = '';

                        if (row.cancel != 1) {
                            btnEdit = `
                                <a href="{{ url('pengeluaran-gudang-inputan-fg/edit') }}/${row.id}">
                                    <button type="button" class="btn btn-sm btn-primary">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </a>
                            `;

                            btnDelete = `
                                <button type="button" class="btn btn-sm btn-danger btn-delete"
                                    data-id="${row.id}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            `;

                            btnPrint = `
                                <a 
                                    href="{{ url('pengeluaran-gudang-inputan-fg/print-sj') }}/${row.id}" 
                                    target="_blank"
                                    class="btn btn-warning btn-sm"
                                >
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            `;
    
                            btnBarcode = `
                                <a 
                                    href="{{ url('pengeluaran-gudang-inputan-fg/print-barcode') }}/${row.id}" 
                                    target="_blank"
                                    class="btn btn-success btn-sm"
                                >
                                    <i class="fa-solid fa-barcode"></i>
                                </a>
                            `;
                        }

                        return `
                            <div class="d-flex gap-1 justify-content-center">
                                ${btnEdit}
                                ${btnPrint}
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
            let url = "{{ url('pengeluaran-gudang-inputan-fg/cancel') }}/" + id;

            Swal.fire({
                title: 'Yakin cancel data ini?',
                text: "Data yang dicancel tidak bisa dikembalikan!",
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
                                text: 'Data berhasil dicancel',
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
