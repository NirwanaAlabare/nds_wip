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
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-note-sticky"></i> Stocker</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div class="mb-3">
                        <label class="form-label"><small>Tanggal Awal</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" onchange="dataTableReload()" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><small>Tanggal Akhir</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" onchange="dataTableReload()" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <button class="btn btn-primary btn-sm" onclick="dataTableReload()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                {{-- <div class="d-none">
                    <div class="d-flex gap-1">
                        <button class="btn btn-success btn-sm" onclick="fixRedundantStocker()"><i class="fa fa-cog"></i> Stocker Redundant</button>
                        <button class="btn btn-primary btn-sm" onclick="fixRedundantNumbering()"><i class="fa fa-cog"></i> Numbering Redundant</button>
                    </div>
                </div> --}}
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table w-100">
                    <thead>
                        <tr>
                            <th>Act</th>
                            <th>Tgl Spreading</th>
                            <th>No. Form</th>
                            <th>Meja</th>
                            <th>No. Cut</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Part</th>
                            <th>Total Lembar</th>
                            <th>Size Ratio</th>
                            <th>No. Marker</th>
                            <th>WS</th>
                            <th>Buyer</th>
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

        // Stocker Datatable
        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('stocker') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [
                {
                    data: null,
                    searchable: false
                },
                {
                    data: 'tanggal_selesai',
                    searchable: false
                },
                {
                    data: 'no_form'
                },
                {
                    data: 'nama_meja'
                },
                {
                    data: 'no_cut',
                },
                {
                    data: 'style'
                },
                {
                    data: 'color'
                },
                {
                    data: 'part_details',
                    searchable: false
                },
                {
                    data: 'total_lembar',
                    searchable: false
                },
                {
                    data: 'marker_details',
                    searchable: false
                },
                {
                    data: 'id_marker'
                },
                {
                    data: 'act_costing_ws'
                },
                {
                    data: 'buyer'
                },
            ],
            columnDefs: [
                // Act Column
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        return `<div class='d-flex gap-1 justify-content-center'> <a class='btn btn-primary btn-sm' href='{{ route("show-stocker") }}/`+row.form_cut_id+`' data-bs-toggle='tooltip'><i class='fa fa-search-plus'></i></a> </div>`;
                    }
                },
                // No. Meja Column
                {
                    targets: [3],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => data ? data.toUpperCase() : "-"
                },
                // Marker Hyperlink
                {
                    targets: [10],
                    render: (data, type, row, meta) => {
                        return data ? `<a class='fw-bold' href='{{ route('edit-marker') }}/ `+row.marker_id+`' target='_blank'><u>`+data+`</u></a>` : "-";
                    }
                },
                // Text No Wrap
                {
                    targets: "_all",
                    className: "text-nowrap"
                }
            ]
        });

        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            if (i == 1 || i == 2 || i == 3 || i == 4 || i == 5 || i == 6 || i == 7 || i == 8 || i == 10 || i == 11 || i == 12) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" style="width:100%"/>');

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

        function dataTableReload() {
            datatable.ajax.reload();
        }

        function fixRedundantStocker() {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Fixing Stocker Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: '{{ route('fix-redundant-stocker') }}',
                type: 'post',
                success: function (res) {
                    console.log(res);

                    swal.close();
                },
                error: function (jqXHR) {
                    console.log(jqXHR);
                }
            });
        }

        function fixRedundantNumbering() {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Fixing Numbering Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: '{{ route('fix-redundant-numbering') }}',
                type: 'post',
                success: function (res) {
                    console.log(res);

                    swal.close();
                },
                error: function (jqXHR) {
                    console.log(jqXHR);
                }
            });
        }

    </script>
@endsection
