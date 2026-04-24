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
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-tape"></i> Penerimaan Fabric Cutting</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div>
                        <label class="form-label small">Tanggal Awal</label>
                        <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" onchange="dataTableReload()">
                    </div>
                    <div>
                        <label class="form-label small">Tanggal Akhir</label>
                        <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}" onchange="dataTableReload()">
                    </div>
                    <div>
                        <button class="btn btn-primary btn-sm" onclick="dataTableReload()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div class="d-flex align-items-end gap-1 mb-3">
                    <a href="{{ route('create-penerimaan-cutting') }}" data-bs-toggle="tooltip" data-bs-title="Buat Penerimaan Cutting" class="btn btn-sb btn-sm"><i class="fa fa-plus"></i></a>
                    <button class="btn btn-sb-secondary btn-sm" data-bs-toggle="tooltip" data-bs-title="Refresh Data" onclick="dataTableReload()"><i class="fa fa-rotate"></i></button>
                    <button class="btn btn-success btn-sm" data-bs-toggle="tooltip" data-bs-title="Export Excel" onclick="exportExcel()"><i class="fa fa-file-excel"></i></button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover table w-100">
                    <thead>
                        <tr>
                            {{-- <th>Action</th> --}}
                            <th>Tanggal Terima</th>
                            <th>Barcode</th>
                            <th>Qty Out</th>
                            <th>Unit</th>
                            <th>Qty Konv</th>
                            <th>Unit Konv</th>
                            <th>No. Req</th>
                            <th>No. BPPB</th>
                            <th>Tgl. BPPB</th>
                            <th>Tujuan</th>
                            <th>No. WS</th>
                            <th>No. WS Act</th>
                            <th>ID Item</th>
                            <th>Style</th>
                            <th>Warna</th>
                            <th>No. Lot</th>
                            <th>No. Roll</th>
                            <th>No. Roll Buyer</th>
                            <th>Created By</th>
                            <th>Created At</th>
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
            // if (i != 0) {
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
            // } else {
            //     $(this).empty();
            // }
        });

        let datatable = $("#datatable").DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            scrollX: "500px",
            scrollY: "500px",
            pageLength: 50,
            ajax: {
                url: '{{ route('penerimaan-cutting') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [
                // {
                //     data: 'id'
                // },
                {
                    data: 'tanggal_terima'
                },
                {
                    data: 'barcode'
                },
                {
                    data: 'qty_out'
                },
                {
                    data: 'unit'
                },
                {
                    data: 'qty_konv'
                },
                {
                    data: 'unit_konv'
                },
                {
                    data: 'no_req'
                },
                {
                    data: 'no_bppb'
                },
                {
                    data: 'tanggal_bppb'
                },
                {
                    data: 'tujuan'
                },
                {
                    data: 'no_ws'
                },
                {
                    data: 'no_ws_act'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'style'
                },
                {
                    data: 'warna'
                },
                {
                    data: 'no_lot'
                },
                {
                    data: 'no_roll'
                },
                {
                    data: 'no_roll_buyer'
                },
                {
                    data: 'created_by_username'
                },
                {
                    data: 'created_at_format'
                }
            ],
            columnDefs: [
                // {
                //     targets: [0],
                //     render: (data, type, row, meta) => {
                //         let btnEdit = "<a href='{{ route('edit-penerimaan-cutting') }}/"+data+"' class='btn btn-primary btn-sm'><i class='fa fa-edit'></i></button>";
                //         let btnDelete = `<a class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route('destroy-penerimaan-cutting') }}/`+data+`' onclick='deleteData(this)'><i class='fa fa-trash'></i></a>`;

                //         // return `<div class='d-flex gap-1 justify-content-center'>` + btnEdit + btnDelete + `</div>`;
                //         return `<div class='d-flex gap-1 justify-content-center'>` + btnDelete + `</div>`;
                //     }
                // },
                {
                    targets: '_all',
                    className: 'text-nowrap'
                }
            ]
        });

        function dataTableReload() {
            datatable.ajax.reload();
        }

        async function exportExcel() {
            Swal.fire({
                title: "Exporting",
                html: "Please Wait...",
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            await $.ajax({
                url: "{{ route("export-penerimaan-cutting") }}",
                type: "post",
                data: {
                    from : $("#tgl-awal").val(),
                    to : $("#tgl-akhir").val()
                },
                xhrFields: { responseType : 'blob' },
                success: function (res) {
                    Swal.close();

                    iziToast.success({
                        title: 'Success',
                        message: 'Success',
                        position: 'topCenter'
                    });

                    var blob = new Blob([res]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Penerimaan Fabric Cutting "+$("#tgl-awal").val()+" - "+$("#tgl-akhir").val()+".xlsx";
                    link.click();
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });

            Swal.close();
        }
    </script>
@endsection
