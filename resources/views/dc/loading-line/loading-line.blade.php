@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <style>
        div.dataTables_wrapper div.dataTables_processing {
            top: 15%;
        }
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header bg-sb text-light">
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-users-line"></i> Stok Loading Line</h5>
        </div>
        <div class="card-body">
            <a href="{{ route('create-loading-plan') }}" class="btn btn-success btn-sm mb-3">
                <i class="fas fa-plus"></i>
                Baru
            </a>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div>
                    <label class="form-label"><small>Tanggal Awal</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" onchange="datatableLoadingLineReload()">
                </div>
                <div>
                    <label class="form-label"><small>Tanggal Akhir</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}" onchange="datatableLoadingLineReload()">
                </div>
                <div>
                    <button class="btn btn-primary btn-sm" onclick="datatableLoadingLineReload()"><i class="fa fa-search"></i></button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable-loading-line" class="table table-bordered table-sm w-100">
                    <thead>
                        <tr>
                            <th>Line</th>
                            <th>Act</th>
                            <th>No. WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Target Sewing</th>
                            <th>Target Loading</th>
                            <th>Loading</th>
                            <th>Balance Loading</th>
                            <th>Trolley</th>
                            <th>Stok Trolley</th>
                            <th>Color</th>
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
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            $("#tgl-awal").val(oneWeeksBeforeFull).trigger("change");

            window.addEventListener("focus", () => {
                // $('#datatable-loading-line').DataTable().ajax.reload(null, false);
            });
        });

        let datatableLoadingLine = $("#datatable-loading-line").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: {
                url: '{{ route('loading-line') }}',
                data: function (d) {
                    d.dateFrom = $("#tgl-awal").val();
                    d.dateTo = $("#tgl-akhir").val();
                }
            },
            columns: [
                {
                    data: 'nama_line'
                },
                {
                    data: 'id'
                },
                {
                    data: 'act_costing_ws',
                },
                {
                    data: 'style',
                },
                {
                    data: 'color'
                },
                {
                    data: 'target_sewing'
                },
                {
                    data: 'target_loading'
                },
                {
                    data: 'loading_qty'
                },
                {
                    data: 'loading_balance'
                },
                {
                    data: 'nama_trolley',
                },
                {
                    data: 'trolley_qty'
                },
                {
                    data: 'trolley_color'
                },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: 'align-middle text-nowrap'
                },
                {
                    targets: [1],
                    render: (data, type, row, meta) => {
                        console.log(row['id']);
                        return `
                            <div class='d-flex flex-column gap-1 justify-content-center align-items-center'>
                                <a href='{{ route('edit-loading-plan') }}/` + row['id'] + `' class='btn btn-primary btn-sm'>
                                    <i class='fa fa-edit'></i>
                                </a>
                                <a href='{{ route('detail-loading-plan') }}/` + row['id'] + `' class='btn btn-info btn-sm'>
                                    <i class='fa fa-search'></i>
                                </a>
                            </div>
                        `;
                    }
                },
                {
                    targets: [5,6,7,8,10],
                    render: (data, type, row, meta) => {
                        return Number(data).toLocaleString('id-ID')
                    }
                }
            ],
            rowsGroup: [
                // Always the array (!) of the column-selectors in specified order to which rows groupping is applied
                // (column-selector could be any of specified in https://datatables.net/reference/type/column-selector)
                0,
                1,
                2,
                3,
                4,
                5,
                6
            ],
        });

        function datatableLoadingLineReload() {
            datatableLoadingLine.ajax.reload()
        }

        $('#datatable-loading-line thead tr').clone(true).appendTo('#datatable-loading-line thead');
        $('#datatable-loading-line thead tr:eq(1) th').each(function(i) {
            if (i == 0 || i == 2 || i == 3 || i == 4 || i == 9 || i == 11) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" />');

                $('input', this).on('keyup change', function() {
                    if (datatableLoadingLine.column(i).search() !== this.value) {
                        datatableLoadingLine
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
