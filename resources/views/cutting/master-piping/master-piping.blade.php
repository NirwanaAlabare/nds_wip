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
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-cog"></i> Master Piping</h5>
        </div>
        <div class="card-body">
            <a href="{{ route('create-master-piping') }}" class="btn btn-success btn-sm mb-3"><i class="fa fa-plus"></i> New</a>
            <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div>
                        <label class="form-label"><small>Tanggal Awal</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" onchange="dataTableReload()">
                    </div>
                    <div>
                        <label class="form-label"><small>Tanggal Akhir</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}" onchange="dataTableReload()">
                    </div>
                    <div>
                        <button class="btn btn-primary btn-sm" onclick="dataTableReload()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover table w-100">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Buyer</th>
                            <th>No. WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Part</th>
                            <th>Panjang</th>
                            <th>Created By</th>
                            <th>Latest Update</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <form action="{{ route('update-master-piping') }}" method="post" id="update-master-piping" onsubmit="submitForm(this, event)">
        @method('PUT')
        <div class="modal fade" id="editMasterPipingModal" tabindex="-1" aria-labelledby="editMasterPipingModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-sb">
                        <h1 class="modal-title fs-5" id="editMasterPipingModalLabel"><i class="fa fa-edit"></i> Edit Master Piping</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 d-none">
                            <label class="form-label">ID</label>
                            <input type="text" class="form-control" id="edit_id" name="id" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Buyer</label>
                            <input type="text" class="form-control" id="edit_buyer" name="buyer" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. WS</label>
                            <input type="text" class="form-control" id="edit_act_costing_ws" name="act_costing_ws" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Style</label>
                            <input type="text" class="form-control" id="edit_style" name="style" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Color</label>
                            <input type="text" class="form-control" id="edit_color" name="color" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Part</label>
                            <input type="text" class="form-control" id="edit_part" name="part" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Panjang</label>
                            <div class="input-group flex-nowrap">
                                <input type="text" class="form-control" id="edit_panjang" name="panjang">
                                <input type="text" class="form-control" id="edit_unit" name="unit">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> BATAL</button>
                        <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> SIMPAN</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

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
            if (i != 0) {
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
            scrollX: "500px",
            scrollY: "500px",
            pageLength: 50,
            ajax: {
                url: '{{ route('master-piping') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [
                {
                    data: 'id'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'act_costing_ws'
                },
                {
                    data: 'style'
                },
                {
                    data: 'color'
                },
                {
                    data: 'part'
                },
                {
                    data: 'panjang'
                },
                {
                    data: 'created_by_username'
                },
                {
                    data: 'updated_at'
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        let btnEdit = "<button class='btn btn-primary btn-sm' onclick='editData(" + JSON.stringify(row) + ", \"editMasterPipingModal\");'><i class='fa fa-edit'></i></button>";

                        return `<div class='d-flex gap-1 justify-content-center'>` + btnEdit + `</div>`;
                    }
                },
                {
                    targets: [6],
                    render: (data, type, row, meta) => {
                        return row.panjang+" "+row.unit;
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
    </script>
@endsection
