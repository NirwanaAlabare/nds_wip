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
    <div class="container-fluid">
        <h3 class="my-3 text-sb text-start fw-bold">Sewing Line WIP</h3>
        <div class="card">
            <div class="card-body">
                <div class="d-flex gap-3 align-items-end mb-3">
                    <div>
                        <label class="form-label">Tanggal Awal</label>
                        <input type="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                    </div>
                    <span> - </span>
                    <div>
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                    </div>
                    <button class="btn btn-sm btn-primary"><i class="fa fa-search"></i></button>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Line</th>
                                <th>Last Shipment Date</th>
                                <th>No. WS</th>
                                <th>Style</th>
                                <th>Color</th>
                                <th>Size</th>
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
            theme: 'bootstrap4',
            dropdownParent: $("#editMejaModal")
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

        let lineWipTable = $("#line-wip-table").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            scrollX: '500px',
            scrollY: '400px',
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
                },
            },
            columns: [
                {
                    data: 'number',
                },
                {
                    data: 'id_year_sequence'
                },
                {
                    data: 'year_sequence_number'
                },
                {
                    data: 'size'
                },
                {
                    data: 'dest',
                },
            ],
            columnDefs: [
                // Text No Wrap
                {
                    targets: "_all",
                    className: "text-nowrap"
                }
            ],
            "rowCallback": function( row, data, index ) {
                let yearSequence = data['year_sequence'], //data numbering month
                    $node = this.api().row(row).nodes().to$();

                if (yearSequence && yearSequence != "-") {
                    $node.addClass('red');
                }
            }
        });
    </script>
@endsection
