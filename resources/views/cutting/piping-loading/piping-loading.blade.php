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
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-right-to-bracket"></i> Piping Loading</h5>
        </div>
        <div class="card-body">
            <a href="{{ route('create-piping-loading') }}" class="btn btn-success btn-sm mb-3"><i class="fa fa-plus"></i> New</a>
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
                            <th>Tanggal</th>
                            <th>No. Transaksi</th>
                            <th>Line</th>
                            <th>Kode Piping</th>
                            <th>Buyer</th>
                            <th>No. WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Part</th>
                            <th>Lebar Roll</th>
                            <th>Qty Roll</th>
                            <th>Output Roll</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="9"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
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

        var listFilter = [
            "tanggal_filter",
            "kode_filter",
            "line_name_filter",
            "kode_piping_filter",
            "buyer_filter",
            "act_costing_ws_filter",
            "style_filter",
            "color_filter",
            "part_filter",
            "lebar_filter",
            "qty_filter",
            "output_filter",
            "user_filter"
        ];

        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm" id="'+listFilter[i]+'"/>');

            $('input', this).on('keyup change', function() {
                if (datatable.column(i).search() !== this.value) {
                    datatable
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        let datatable = $("#datatable").DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            pageLength: 50,
            ajax: {
                url: '{{ route('piping-loading') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [
                {
                    data: 'tanggal'
                },
                {
                    data: 'kode'
                },
                {
                    data: 'line_name'
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
                    data: 'lebar_roll_piping'
                },
                {
                    data: 'qty_roll'
                },
                {
                    data: 'estimasi_output_total'
                },
                {
                    data: 'created_by_username'
                },
            ],
            columnDefs: [
                {
                    targets: '_all',
                    className: 'text-nowrap'
                }
            ],
            footerCallback: async function(row, data, start, end, display) {
                var api = this.api(),data;

                $(api.column(0).footer()).html('Total');
                $(api.column(9).footer()).html("...");
                $(api.column(10).footer()).html("...");
                $(api.column(11).footer()).html("...");

                $.ajax({
                    url: "{{ route("total-piping-loading") }}",
                    data: {
                        'dateFrom' : $('#tgl-awal').val(),
                        'dateTo' : $('#tgl-akhir').val(),
                        "tanggal": $("#tanggal_filter").val(),
                        "kode": $("#kode_filter").val(),
                        "line_name": $("#line_name_filter").val(),
                        "kode_piping": $("#kode_piping_filter").val(),
                        "buyer": $("#buyer_filter").val(),
                        "act_costing_ws": $("#act_costing_ws_filter").val(),
                        "style": $("#style_filter").val(),
                        "color": $("#color_filter").val(),
                        "part": $("#part_filter").val(),
                        "lebar": $("#lebar_filter").val(),
                        "qty": $("#qty_filter").val(),
                        "output": $("#output_filter").val(),
                        "user": $("#user_filter").val()
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log(response);

                        if (response) {
                            // Update footer by showing the total with the reference of the column index
                            $(api.column(0).footer()).html('Total');
                            $(api.column(9).footer()).html((response['total_lebar'] ?? "")+" "+(response['total_lebar_unit'] ?? ""));
                            $(api.column(10).footer()).html((response['total_qty'] ?? "")+" "+(response['total_qty_unit'] ?? ""));
                            $(api.column(11).footer()).html((response['total_output'] ?? "")+" "+(response['total_output_unit'] ?? ""));
                        }
                    },
                    error: function(jqXHR) {
                        console.log(jqXHR);
                    },
                })
            },
        });

        function dataTableReload() {
            datatable.ajax.reload();
        }
    </script>
@endsection
