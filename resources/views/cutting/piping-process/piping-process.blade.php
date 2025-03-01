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
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-ring"></i> Piping</h5>
        </div>
        <div class="card-body">
            <a href="{{ route('create-piping-process') }}" class="btn btn-success btn-sm mb-3"><i class="fa fa-plus"></i> New</a>
            <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div>
                        <label class="form-label"><small>Dari</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" onchange="dataTableReload()">
                    </div>
                    <div>
                        <label class="form-label"><small>Sampai</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}" onchange="dataTableReload()">
                    </div>
                    <div>
                        <button class="btn btn-primary btn-sm" onclick="dataTableReload()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover table-sm w-100">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Tanggal</th>
                            <th>ID Piping</th>
                            <th>Buyer</th>
                            <th>No. WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Part</th>
                            <th>Panjang</th>
                            <th>Jumlah Roll</th>
                            <th>Lebar Roll</th>
                            <th>Panjang Roll</th>
                            <th>Output</th>
                            <th>Created By</th>
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
            pageLength: 50,
            ajax: {
                url: '{{ route('piping-process') }}',
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
                    data: 'tanggal'
                },
                {
                    data: 'kode_piping'
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
                    data: 'panjang_master_piping'
                },
                {
                    data: 'total_roll'
                },
                {
                    data: 'lebar_roll'
                },
                {
                    data: 'panjang_roll'
                },
                {
                    data: 'output_total_roll'
                },
                {
                    data: 'created_by_username'
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        let btnEdit = "<a href='{{ route('process-piping-process') }}/"+data+"' class='btn btn-primary btn-sm'><i class='fa fa-search-plus'></i></a>";
                        let btnDelete = `<a class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route('destroy-piping-process') }}/`+data+`' onclick='deleteData(this)'><i class='fa fa-trash'></i></a>`;
                        let btnPdf = "<a href='{{ route('pdf-piping-process') }}/"+data+"' class='btn btn-secondary btn-sm'><i class='fa-solid fa-print'></i></a>";

                        return `<div class='d-flex gap-1 justify-content-center'>` + btnEdit + btnDelete + btnPdf + `</div>`;
                    }
                },
                {
                    targets: [8],
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
