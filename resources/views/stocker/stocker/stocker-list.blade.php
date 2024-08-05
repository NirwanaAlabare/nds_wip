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
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-note-sticky"></i> Stocker List</h5>
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
                <button class="btn btn-sb btn-sm" data-bs-toggle="modal" data-bs-target="#printModal"><i class="fa-regular fa-file-lines fa-sm"></i> Print Month Count</button>
                {{-- <div class="d-none">
                    <div class="d-flex gap-1">
                        <button class="btn btn-success btn-sm" onclick="fixRedundantStocker()"><i class="fa fa-cog"></i> Stocker Redundant</button>
                        <button class="btn btn-primary btn-sm" onclick="fixRedundantNumbering()"><i class="fa fa-cog"></i> Numbering Redundant</button>
                    </div>
                </div> --}}
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-sm w-100">
                    <thead>
                        <tr>
                            <th>Act</th>
                            <th>Stocker</th>
                            <th>Part</th>
                            <th>No. WS</th>
                            <th>Style</th>
                            <th>No. Form</th>
                            <th>No. Cut</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Dest</th>
                            <th>Group</th>
                            <th>Shade</th>
                            <th>Ratio</th>
                            <th>Stocker Range</th>
                            <th>Numbering Month</th>
                            <th>Numbering Range</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Stocker List Modal --}}
    <div class="modal fade" id="stockerListModal" tabindex="-1" aria-labelledby="stockerListModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Stocker List</h5>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <form action="#" method="post" id="stocker-form">
                            <table class="table table-sm">
                                <thead>
                                    <th>No. WS</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th>Dest</th>
                                    <th>Group</th>
                                    <th>Shade</th>
                                    <th>Ratio</th>
                                    <th>Number</th>
                                    <th>Month</th>
                                    <th>Month Number</th>
                                    <th>Print</th>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Numbers Modal -->
    <div class="modal fade" id="printModal" tabindex="-1" aria-labelledby="printModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="printModalLabel">Print Number</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-center mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="switch-method" onchange="switchMethod(this)">
                                <label class="form-check-label" id="to-qty">Qty</label>
                                <label class="form-check-label d-none" id="to-range">Range</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="qty-method">
                        <label class="form-label">Print Qty : </label>
                        <input class="form-control form-control-sm" type="number" name="number" id="print-qty" />
                    </div>
                    <div class="mb-3" id="range-method" class="d-none">
                        <label class="form-label">Print Range : </label>
                        <input class="form-control form-control-sm" type="number" name="number" id="range-awal" />
                        <input class="form-control form-control-sm" type="number" name="number" id="range-akhir" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa-solid fa-times fa-sm"></i> Batal</button>
                    <button class="btn btn-success" onclick="printMonthCount()"><i class="fa-solid fa-file-export fa-sm"></i> Export</button>
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

        // Stocker Datatable
        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('stocker-list') }}',
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
                    data: 'id_qr_stocker'
                },
                {
                    data: 'part'
                },
                {
                    data: 'act_costing_ws'
                },
                {
                    data: 'style',
                },
                {
                    data: 'no_form'
                },
                {
                    data: 'no_cut',
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
                    data: 'group_stocker',
                },
                {
                    data: 'shade',
                },
                {
                    data: 'ratio',
                },
                {
                    data: 'stocker_range',
                },
                {
                    data: 'numbering_month'
                },
                {
                    data: 'numbering_range'
                },
            ],
            columnDefs: [
                // Act Column
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        return `<div class='d-flex gap-1 justify-content-center'><button type='button' class='btn btn-primary btn-sm' onclick='stockerListModal(`+JSON.stringify(row)+`)'><i class='fa fa-search-plus'></i></a></div>`;
                    }
                },
                // Form Hyperlink
                {
                    targets: [5],
                    render: (data, type, row, meta) => {
                        return data ? `<a class='fw-bold' href='{{ route("show-stocker") }}/`+row.form_cut_id+`' target='_blank'><u>`+data+`</u></a>` : "-";
                    }
                },
                // Text No Wrap
                {
                    targets: "_all",
                    className: "text-nowrap"
                }
            ],
            "rowCallback": function( row, data, index ) {
                let numberingMonth = data['numbering_month'], //data numbering month
                    $node = this.api().row(row).nodes().to$();

                if (numberingMonth && numberingMonth != "-") {
                    $node.addClass('red');
                }
            }
        });

        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            if (i != 0) {
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
            $('#datatable').DataTable().ajax.reload();
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

        function switchMethod(element) {
            if (element.checked) {
                toRangeMethod();
            } else {
                toQtyMethod();
            }
        }

        function toQtyMethod() {
            $('to-qty').removeClass('d-none');
            $('to-range').addClass('d-none');

            $('qty-method').removeClass('d-none');
            $('range-method').addClass('d-none');
        }

        function toRangeMethod() {
            $('to-range').removeClass('d-none');
            $('to-qty').addClass('d-none');

            $('range-method').removeClass('d-none');
            $('qty-method').addClass('d-none');
        }

        function printMonthCount() {
            generating = true;

            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: '{{ route('print-month-count') }}',
                type: 'post',
                data: {
                    qty: $("#print-qty").val()
                },
                xhrFields:
                {
                    responseType: 'blob'
                },
                success: function(res) {
                    if (res) {
                        console.log(res);

                        var blob = new Blob([res], {type: 'application/pdf'});
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Numbers.pdf";
                        link.click();
                    }

                    window.location.reload();

                    generating = false;
                },
                error: function(jqXHR) {
                    console.log(jqXHR);

                    generating = false;
                }
            });
        }
    </script>
@endsection
