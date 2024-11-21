@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
    {{-- Complete Stocker Data --}}
    <div class="card">
        <div class="card-header bg-sb text-light">
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-spinner"></i> Stock DC Incomplete</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div>
                    <label class="form-label"><small>Tanggal Awal</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" onchange="datatableIncompleteStockerReload()">
                </div>
                <div>
                    <label class="form-label"><small>Tanggal Akhir</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}" onchange="datatableIncompleteStockerReload()">
                </div>
                <div>
                    <button class="btn btn-primary btn-sm" onclick="datatableIncompleteStockerReload()"><i class="fa fa-search"></i></button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable-incomplete-stocker" class="table table-bordered table-sm w-100">
                    <thead>
                        <tr>
                            <th class="align-bottom">Action</th>
                            <th>No. WS</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Bundle</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
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

    <script>
        // Initial Function
        document.addEventListener("DOMContentLoaded", () => {
            // Set Filter to 1 Week Ago
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            $("#tgl-awal").val(oneWeeksBeforeFull).trigger("change");
        });

        // Complete Stocker Datatable
        let datatableIncompleteStocker = $("#datatable-incomplete-stocker").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('stock-dc-incomplete') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function (d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                }
            },
            columns: [
                {
                    data: 'act_costing_ws'
                },
                {
                    data: 'act_costing_ws'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'bundle'
                },
                {
                    data: 'qty'
                },
            ],
            columnDefs: [
                {
                    // Act Column
                    targets: [0],
                    render: (data, type, row, meta) => {
                        return  `
                            <div class='d-flex gap-1 justify-content-center'>
                                <a href='{{ route('stock-dc-incomplete-detail') }}/`+row.part_id+`/`+row.color+`/`+row.size+`/' class='btn btn-primary btn-sm'>
                                    <i class='fa fa-search-plus'></i>
                                </a>
                            </div>
                        `;
                    }
                },
                {
                    // All Column Colorization
                    targets: '_all',
                    className: 'text-nowrap',
                    render: (data, type, row, meta) => {
                        var color = '#2b2f3a';
                        if (row.sisa == '0') {
                            color = '#087521';
                        } else {
                            color = '#2b2f3a';
                        }
                        return '<span style="font-weight: 600; color:' + color + '">' + data + '</span>';
                    }
                },
            ],
        });

        // Complete Stocker Datatable Reload
        function datatableIncompleteStockerReload() {
            datatableIncompleteStocker.ajax.reload()
        }

        // Complete Stocker Datatable Header Column Filter
        $('#datatable-incomplete-stocker thead tr').clone(true).appendTo('#datatable-incomplete-stocker thead');
        $('#datatable-incomplete-stocker thead tr:eq(1) th').each(function(i) {
            if (i == 1 || i == 2 || i == 3 || i == 4 || i == 5 || i == 6 || i == 7 || i == 8) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" />');

                $('input', this).on('keyup change', function() {
                    if (datatableIncompleteStocker.column(i).search() !== this.value) {
                        datatableIncompleteStocker
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });
    </script>
@endsection
