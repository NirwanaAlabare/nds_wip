@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .dataTables_wrapper .dataTables_processing {
            position: absolute;
            top: 15% !important;
            /* background: #FFFFCC;
            border: 1px solid black; */
            border-radius: 3px;
            font-weight: bold;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <h3 class="my-3 text-sb text-start fw-bold">Sewing Line WIP</h3>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-end">
                    <div class="d-flex gap-3 align-items-end mb-3">
                        <div>
                            <label class="form-label">Tanggal Awal</label>
                            <input type="date" class="form-control form-control-sm" id="tanggal_awal" value="{{ date('Y-m-d') }}" onchange="lineWipTableReload()">
                        </div>
                        <span> - </span>
                        <div>
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control form-control-sm" id="tanggal_akhir" value="{{ date('Y-m-d') }}" onchange="lineWipTableReload()">
                        </div>
                        <button class="btn btn-sm btn-primary"><i class="fa fa-search"></i></button>
                    </div>
                    <div class="mb-3">
                        <select class="form-select select2bs4" name="line_id" id="line_id">
                            <option value="">Pilih Line</option>
                            @foreach ($lines as $line)
                                <option value="{{ $line->line_id }}" data-line="{{ $line->username }}">{{ $line->FullName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered" id="line_wip_table">
                        <thead>
                            <tr>
                                <th>Line</th>
                                <th>Last Shipment Date</th>
                                <th>No. WS</th>
                                <th>Style</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Dest</th>
                                <th>Qty Loading</th>
                                <th>WIP Sewing Line</th>
                                <th>Reject</th>
                                <th>Defect</th>
                                <th>Qty Output</th>
                                <th>WIP Steam</th>
                                <th>Qty Packing Line</th>
                                <th>WIP Packing</th>
                                <th>Qty Transfer Garment</th>
                            </tr>
                        </thead>
                    </table>
                </div>
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

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
    </script>

    <script>
        // Initial Function
        $(document).ready(() => {
            // Set Filter to 1 Week Ago
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            $("#tgl-awal").val(oneWeeksBeforeFull).trigger("change");
        });

        $("#line_id").on("change", () => {
            lineWipTableReload();
        });

        function lineWipTableReload() {
            $("#line_wip_table").DataTable().ajax.reload();
        }

        $('#line_wip_table thead tr').clone(true).appendTo('#line_wip_table thead');
        $('#line_wip_table thead tr:eq(1) th').each(function(i) {
            if (i == 0 || i == 1 || i == 2 || i == 3 || i == 4 || i == 5 || i == 6 || i == 7) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" style="width:100%"/>');

                $('input', this).on('keyup change', function() {
                    if (lineWipTable.column(i).search() !== this.value) {
                        lineWipTable
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        let lineWipTable = $("#line_wip_table").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            pageLength: 100,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('line-wip') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.tanggal_awal = $('#tanggal_awal').val();
                    d.tanggal_akhir = $('#tanggal_akhir').val();
                    d.line_id = $('#line_id').val();
                    d.line = $('#line_id').find(":selected").attr("data-line");
                },
            },
            columns: [
                {
                    data: 'nama_line'
                },
                {
                    data: 'tanggal',
                },
                {
                    data: 'ws'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'dest'
                },
                {
                    data: 'loading_qty'
                },
                {
                    data: null
                },
                {
                    data: 'reject'
                },
                {
                    data: 'defect'
                },
                {
                    data: 'output'
                },
                {
                    data: null
                },
                {
                    data: 'output_packing'
                },
                {
                    data: null
                },
                {
                    data: 'total_transfer_garment'
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        return data ? data.replace("_", " ").toUpperCase() : '-';
                    }
                },
                {
                    targets: [7, 9, 10, 11, 13, 15],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        return data ? data : 0;
                    }
                },
                {
                    targets: [8],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        return (row.loading_qty ? row.loading_qty : 0) -  ((row.reject ? row.reject : 0) + (row.defect ? row.defect : 0) + (row.output));
                    }
                },
                {
                    targets: [12],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        return (row.output ? row.output : 0) -  (row.output_packing ? row.output_packing : 0);
                    }
                },
                {
                    targets: [14],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        return (row.output_packing ? row.output_packing : 0) - (row.total_transfer_garment ? row.total_transfer_garment : 0);
                    }
                },
                // Text No Wrap
                {
                    targets: "_all",
                    className: "text-nowrap"
                }
            ],
        });
    </script>
@endsection
