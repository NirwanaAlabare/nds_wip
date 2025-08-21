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
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-list-ul"></i> Registration List</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div class="mb-3">
                        <label class="form-label"><small>Tanggal Awal</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><small>Tanggal Akhir</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <button class="btn btn-primary btn-sm" onclick="dataTableReload()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div class="d-flex justify-content-end align-items-end gap-3">
                    {{-- <button class="btn btn-sb btn-sm" data-bs-toggle="modal" data-bs-target="#printModal"><i class="fa-regular fa-file-lines fa-sm"></i> Print Month Count</button> --}}
                    <button class="btn btn-success btn-sm" onclick="exportExcel()"><i class="fa-regular fa-file-excel"></i></button>
                    <button class="btn btn-sb btn-sm" id="print-stock-number" onclick="printStockNumber()"><i class="fa-solid fa-print"></i> Print Number Stock</button>
                    <button class="btn btn-sb-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#printYearModal"><i class="fa-solid fa-print"></i> Print Label</button>
                    <button class="btn btn-primary btn-sm d-none" data-bs-toggle="modal" data-bs-target="#printNewYearModal"><i class="fa-solid fa-print"></i> Print New Label</button>
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
                            <th>Tanggal</th>
                            <th>No. Form</th>
                            <th>No. Cut</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Dest</th>
                            <th>Qty</th>
                            <th>Year Sequence</th>
                            <th>Year Sequence Range</th>
                            <th>Buyer</th>
                            <th>No. WS</th>
                            <th>Style</th>
                            <th>Stocker</th>
                            <th>Stock</th>
                            <th>Part</th>
                            <th>Group</th>
                            <th>Shade</th>
                            <th>Ratio</th>
                            <th>Stocker Range</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6">TOTAL</th>
                            <th></th>
                            <th colspan="3"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
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
                    <div class="mb-3 d-none" id="range-method">
                        <label class="form-label">Print Range : </label>
                        <div class="d-flex gap-3">
                            <input class="form-control form-control-sm" type="number" name="number" id="print-range-awal" />
                            <span> - </span>
                            <input class="form-control form-control-sm" type="number" name="number" id="print-range-akhir" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa-solid fa-times fa-sm"></i> Batal</button>
                    <button class="btn btn-success" onclick="printMonthCount()"><i class="fa-solid fa-file-export fa-sm"></i> Export</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Print Year Numbers Modal --}}
    <div class="modal fade" id="printYearModal" tabindex="-1" aria-labelledby="printYearModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="printYearModalLabel">Print Year Sequence</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-center mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="switch-method-year" onchange="switchMethodYear(this)">
                                <label class="form-check-label" id="to-qty-year">Qty</label>
                                <label class="form-check-label d-none" id="to-range-year">Range</label>
                            </div>
                        </div>
                    </div>
                    <label class="form-label">Tahun : </label>
                    <div class="d-flex gap-3 mb-3">
                        <select class="form-select select2bs4yearseq" name="year-sequence-year" id="year-sequence-year" onchange="getSequenceYearSequence();">
                            @foreach ($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                        <select class="form-select select2bs4yearseq" name="year-sequence-sequence" id="year-sequence-sequence" onchange="getRangeYearSequence();">
                        </select>
                    </div>
                    <div class="mb-3" id="qty-method-year">
                        <label class="form-label">Print Qty : </label>
                        <input class="form-control form-control-sm" type="number" name="number" id="print-qty-year" />
                    </div>
                    <div class="mb-3 d-none" id="range-method-year">
                        <label class="form-label">Print Range : </label>
                        <div class="d-flex gap-3 mb-3">
                            <input class="form-control form-control-sm" type="number" name="number" id="print-range-awal-year" />
                            <span> - </span>
                            <input class="form-control form-control-sm" type="number" name="number" id="print-range-akhir-year" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa-solid fa-times fa-sm"></i> Batal</button>
                    <button class="btn btn-success" onclick="printYearSequence()"><i class="fa-solid fa-file-export fa-sm"></i> Export</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Print New Year Numbers Modal --}}
    <div class="modal fade" id="printNewYearModal" tabindex="-1" aria-labelledby="printNewYearModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="printNewYearModalLabel">Print New Year Sequence</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Tahun : </label>
                    <div class="d-flex gap-3 mb-3">
                        <select class="form-select select2bs4newyearseq" name="new-year-sequence-year" id="new-year-sequence-year" onchange="getSequenceYearSequence('new');">
                            @foreach ($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                        <select class="form-select select2bs4newyearseq" name="new-year-sequence-sequence" id="new-year-sequence-sequence" onchange="getRangeYearSequence('new');">
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Print Range : </label>
                        <div class="d-flex gap-3 mb-3">
                            <input class="form-control form-control-sm" type="number" name="number" id="new-print-range-awal-year" />
                            <span> - </span>
                            <input class="form-control form-control-sm" type="number" name="number" id="new-print-range-akhir-year" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa-solid fa-times fa-sm"></i> Batal</button>
                    <button class="btn btn-success" onclick="printNewYearSequence()"><i class="fa-solid fa-file-export fa-sm"></i> Export</button>
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
        })
        $('.select2bs4yearseq').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#printYearModal")
        })
        $('.select2bs4newyearseq').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#printNewYearModal")
        })
    </script>

    <script>
        var bypassBackward = false;

        // Initial Function
        $(document).ready(() => {
            // Set Filter to 1 Week Ago
            // let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            // let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            // let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            // let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            // let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            $("#switch-method").prop("checked", false);
            $("#switch-method-year").prop("checked", false);

            // $("#tgl-awal").val(oneWeeksBeforeFull).trigger("change");

            $("#year-sequence-year").val(new Date().getFullYear()).trigger("change");
            $("#new-year-sequence-year").val(new Date().getFullYear()).trigger("change");

            bypassBackward = true;
        });

        var method = 'qty';
        var methodYear = 'qty';

        $("#printModal").on('hide.bs.modal', event => {
            toQtyMethod();

            $("#switch-method").prop("checked", false);
        });

        var stockListFilter = ['action', 'tanggal_filter', 'no_form_filter', 'no_cut_filter', 'color_filter', 'size_filter', 'dest_filter', 'qty_filter', 'year_sequence_filter', 'numbering_range_filter', 'buyer_filter', 'ws_filter', 'style_filter', 'stocker_filter', 'tipe_filter', 'part_filter', 'group_filter', 'shade_filter', 'ratio_filter', 'stocker_range_filter'];

        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            if (i != 0) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" style="width:100%" id="'+stockListFilter[i]+'" />');

                $('input', this).on('keyup change', function() {
                    if (datatable.column(i).search() !== this.value) {
                        datatable
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                if (i == 0) {
                    $(this).html(`
                        <div class="form-check" style="scale: 1.5;translate: 50%;">
                            <input class="form-check-input" type="checkbox" value="" id="checkAllStockNumber">
                        </div>
                    `);
                } else {
                    $(this).empty();
                }
            }
        });

        // Stocker Datatable
        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            scrollX: "500px",
            scrollY: "400px",
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
                    data: 'updated_at'
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
                    data: 'qty'
                },
                {
                    data: 'year_sequence'
                },
                {
                    data: 'numbering_range'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'act_costing_ws'
                },
                {
                    data: 'style',
                },
                {
                    data: 'id_qr_stocker'
                },
                {
                    data: 'tipe'
                },
                {
                    data: 'part'
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
            ],
            columnDefs: [
                // Act Column
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <a class='btn btn-primary btn-sm' href='{{ route("stocker-list-detail") }}/`+row.form_cut_id+`/`+row.group_stocker+`/`+row.ratio+`/`+row.so_det_id+`/`+(row.tipe == 'PIECE' ? 3 : (row.tipe == 'REJECT' ? 2 : 1))+`'     target='_blank'><i class='fa fa-search-plus'></i></a>
                                <div class="form-check" style="scale: 1.5;translate: 50%;">
                                    <input class="form-check-input check-stock-number" type="checkbox" onchange="checkStockNumber(this)" id="stock_number_`+meta.row+`">
                                </div>
                            </div>
                        `;
                    }
                },
                // Form Hyperlink
                {
                    targets: [2],
                    render: (data, type, row, meta) => {
                        return data ? ( row.tipe == 'REJECT' ? `<a class='fw-bold' href='{{ route("show-cutting-reject") }}/`+row.form_cut_id+`' target='_blank'><u>`+data+`</u></a>` : `<a class='fw-bold' href='{{ route("show-stocker") }}/`+row.form_cut_id+`' target='_blank'><u>`+data+`</u></a>` ) : "-";
                    }
                },
                // Stocker List
                {
                    targets: [13],
                    render: (data, type, row, meta) => {
                        return `<div style='width: 200px; overflow-x: auto;'>`+data+`</div>`;
                    }
                },
                // Text No Wrap
                {
                    targets: "_all",
                    className: "text-nowrap"
                }
            ],
            footerCallback: async function(row, data, start, end, display) {
                var api = this.api(),data;

                $(api.column(0).footer()).html('Total');
                $(api.column(6).footer()).html("...");
                $(api.column(7).footer()).html("...");

                $.ajax({
                    url: '{{ route('stocker-list-total') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: {
                        'dateFrom': $('#tgl-awal').val(),
                        'dateTo': $('#tgl-akhir').val(),
                        'tanggal_filter': $('#tanggal_filter').val(),
                        'no_form_filter': $('#no_form_filter').val(),
                        'no_cut_filter': $('#no_cut_filter').val(),
                        'color_filter': $('#color_filter').val(),
                        'size_filter': $('#size_filter').val(),
                        'dest_filter': $('#dest_filter').val(),
                        'qty_filter': $('#qty_filter').val(),
                        'year_sequence_filter': $('#year_sequence_filter').val(),
                        'numbering_range_filter': $('#numbering_range_filter').val(),
                        'buyer_filter': $('#buyer_filter').val(),
                        'ws_filter': $('#ws_filter').val(),
                        'style_filter': $('#style_filter').val(),
                        'stocker_filter': $('#stocker_filter').val(),
                        'tipe_filter': $('#tipe_filter').val(),
                        'part_filter': $('#part_filter').val(),
                        'group_filter': $('#group_filter').val(),
                        'shade_filter': $('#shade_filter').val(),
                        'ratio_filter': $('#ratio_filter').val(),
                        'stocker_range_filter': $('#stocker_range_filter').val()
                    },
                    success: function(response) {
                        if (response && response[0]) {
                            // Update footer by showing the total with the reference of the column index
                            $(api.column(0).footer()).html('Total');
                            $(api.column(6).footer()).html(response[0]['total_row']);
                            $(api.column(7).footer()).html(response[0]['total_qty']);
                        }
                    },
                    error: function(request, status, error) {
                        // alert('cek');
                        console.error(error);
                    },
                })
            },
            rowCallback: function( row, data, index ) {
                let numberingMonth = data['numbering_month'], //data numbering month
                    $node = this.api().row(row).nodes().to$();

                if (numberingMonth && numberingMonth != "-") {
                    $node.addClass('red');
                }

                if (stockNumberArr.filter(item => item.updated_at == data['updated_at'] && item.id_qr_stocker == data['id_qr_stocker']).length > 0) {
                    currentPageCheck++;

                    $(row).find('input[type="checkbox"]').prop('checked', true);
                }
            },
            drawCallback: function (settings) {
                if (currentPageCheck == 0) {
                    $('#checkAllStockNumber').prop("checked", false);
                } else {
                    $('#checkAllStockNumber').prop("checked", true);
                }

                currentPageCheck = 0;
            },
        });

        async function dataTableReload() {
            await limitDateRange();

            $('#datatable').DataTable().ajax.reload(function () {
                document.getElementById("loading").classList.add("d-none");
            }, false);
        }

        async function limitDateRange() {
            const from = document.getElementById('tgl-awal').value;
            const to = document.getElementById('tgl-akhir').value;

            if (!from || !to) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Harap isi tanggal',
                    // html: 'Harap isi tanggal awal dan tanggal akhir.',
                    allowOutsideClick: false,
                });

                return;
            }

            const fromDate = new Date(from);
            const toDate = new Date(to);

            // Create a new date that's 1 months after fromDate
            const maxToDate = new Date(fromDate);
            maxToDate.setMonth(maxToDate.getMonth() + 1);

            if (toDate > maxToDate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Maks. 1 Bulan',
                    // html: 'Harap isi tanggal awal dan tanggal akhir.',
                    allowOutsideClick: false,
                });

                // Format maxToDate as YYYY-MM-DD
                const y = maxToDate.getFullYear();
                const m = String(maxToDate.getMonth() + 1).padStart(2, '0');
                const d = String(maxToDate.getDate()).padStart(2, '0');
                const formatted = `${y}-${m}-${d}`;

                // Set it to #dateto
                document.getElementById('tgl-akhir').value = formatted;
            } else {
                // alert('Date range is valid.');
            }
        }

        function exportExcel() {
            document.getElementById("loading").classList.remove("d-none");

            $.ajax({
                url: "{{ route("stocker-list-export") }}",
                type: "get",
                data: {
                    'dateFrom': $('#tgl-awal').val(),
                    'dateTo': $('#tgl-akhir').val(),
                    'tanggal_filter': $('#tanggal_filter').val(),
                    'no_form_filter': $('#no_form_filter').val(),
                    'no_cut_filter': $('#no_cut_filter').val(),
                    'color_filter': $('#color_filter').val(),
                    'size_filter': $('#size_filter').val(),
                    'dest_filter': $('#dest_filter').val(),
                    'qty_filter': $('#qty_filter').val(),
                    'year_sequence_filter': $('#year_sequence_filter').val(),
                    'numbering_range_filter': $('#numbering_range_filter').val(),
                    'buyer_filter': $('#buyer_filter').val(),
                    'ws_filter': $('#ws_filter').val(),
                    'style_filter': $('#style_filter').val(),
                    'stocker_filter': $('#stocker_filter').val(),
                    'tipe_filter': $('#tipe_filter').val(),
                    'part_filter': $('#part_filter').val(),
                    'group_filter': $('#group_filter').val(),
                    'shade_filter': $('#shade_filter').val(),
                    'ratio_filter': $('#ratio_filter').val(),
                    'stocker_range_filter': $('#stocker_range_filter').val()
                },
                xhrFields:{
                    responseType: 'blob'
                },
                success: function (res) {
                    document.getElementById("loading").classList.add("d-none");

                    if (res) {
                        console.log(res);

                        var blob = new Blob([res]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Stocker List.xlsx";
                        link.click();

                        iziToast.success({
                            title: 'Success',
                            message: 'Data Stocker Number List berhasil di export.',
                            position: 'topCenter'
                        });
                    }
                },
                error: function (jqXHR) {
                    document.getElementById("loading").classList.add("d-none");

                    iziToast.error({
                        title: 'Error',
                        message: 'Data Stocker Number List gagal di export.',
                        position: 'topCenter'
                    });

                    console.error(jqXHR);
                }
            });
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
            $('#to-qty').removeClass('d-none');
            $('#to-range').addClass('d-none');

            $('#qty-method').removeClass('d-none');
            $('#range-method').addClass('d-none');

            method = 'qty';
        }

        function toRangeMethod() {
            $('#to-range').removeClass('d-none');
            $('#to-qty').addClass('d-none');

            $('#range-method').removeClass('d-none');
            $('#qty-method').addClass('d-none');

            method = 'range';
        }

        function validatePrintMonthCount() {
            if (method == 'qty' && $('#print-qty').val() > 0) {
                return true;
            } else if (method == 'range' && $('#print-range-awal').val() > 0 && $('#print-range-awal').val() <= $('#print-range-akhir').val()) {
                return true;
            }

            return false;
        }

        function printMonthCount() {
            if (validatePrintMonthCount()) {
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
                        method: method,
                        qty: $("#print-qty").val(),
                        rangeAwal: $("#print-range-awal").val(),
                        rangeAkhir: $("#print-range-akhir").val(),
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
                        swal.close();

                        generating = false;
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    html: 'Qty/Range tidak valid.',
                    allowOutsideClick: false,
                });
            }
        }

        function getSequenceYearSequence(type) {
            $.ajax({
                url: '{{ route('get-sequence-year-sequence') }}',
                type: 'get',
                data: {
                    year: type == 'new' ? $("#new-year-sequence-year").val() : $("#year-sequence-year").val()
                },
                dataType: 'json',
                success: async function(res)
                {
                    console.log("sequence", res);

                    if (res) {
                        if (res.status != "400") {
                            if (type == 'new') {
                                let select = document.getElementById('new-year-sequence-sequence');
                                select.innerHTML = "";

                                for(let i = 0; i < res.length; i++) {
                                    let option = document.createElement("option");
                                    option.setAttribute("value", res[i]);
                                    option.innerHTML = res[i];
                                    select.appendChild(option);
                                }

                                $("#new-year-sequence-sequence").val(res[res.length-1]).trigger("change");
                            } else {
                                let select = document.getElementById('year-sequence-sequence');
                                select.innerHTML = "";

                                for(let i = 0; i < res.length; i++) {
                                    let option = document.createElement("option");
                                    option.setAttribute("value", res[i]);
                                    option.innerHTML = res[i];
                                    select.appendChild(option);
                                }

                                $("#year-sequence-sequence").val(res[res.length-1]).trigger("change");
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                html: res.message,
                            });
                        }
                    }
                },
                error: function(jqXHR)
                {
                    console.error(jqXHR)
                }
            })
        }

        function getRangeYearSequence(type) {
            document.getElementById("loading").classList.remove("d-none");
            $.ajax({
                url: '{{ route('get-range-year-sequence') }}',
                type: 'get',
                data: {
                    year: type == 'new' ? $("#new-year-sequence-year").val() : $("#year-sequence-year").val(),
                    sequence: type == 'new' ? $("#new-year-sequence-sequence").val() : $("#year-sequence-sequence").val()
                },
                dataType: 'json',
                success: function(res)
                {
                    document.getElementById("loading").classList.add("d-none");

                    console.log("range", res);

                    if (res) {
                        if (res.status != "400") {
                            if (type == 'new') {
                                $('#new-print-range-awal-year').val(res.year_sequence_number > 1 ? res.year_sequence_number+1 : res.year_sequence_number).trigger("change");

                                if (Number(res.year_sequence_number) >= 999999) {
                                    let newSelect = document.getElementById('new-year-sequence-sequence');

                                    if ($('#new-year-sequence-sequence > option[value="'+(Number(newSelect.value)+1)+'"]').length < 1) {
                                        let newOption = document.createElement("option");
                                        newOption.setAttribute("value", Number(newSelect.value)+1);
                                        newOption.innerHTML = Number(newSelect.value)+1;
                                        newSelect.appendChild(newOption);
                                    }

                                    if (!bypassBackward) {
                                        $("#new-year-sequence-sequence").val(Number(newSelect.value)+1).trigger("change");

                                        bypassBackward = true;
                                    }
                                }
                            } else {
                                $('#print-range-awal-year').val(res.year_sequence_number > 1 ? res.year_sequence_number+1 : res.year_sequence_number).trigger("change");

                                if (Number(res.year_sequence_number) >= 999999) {
                                    let select = document.getElementById('year-sequence-sequence');

                                    if ($('#year-sequence-sequence > option[value="'+(Number(select.value)+1)+'"]').length < 1) {
                                        let option = document.createElement("option");
                                        option.setAttribute("value", Number(select.value)+1);
                                        option.innerHTML = Number(select.value)+1;
                                        select.appendChild(option);
                                    }

                                    if (!bypassBackward) {
                                        $("#year-sequence-sequence").val(Number(select.value)+1).trigger("change");

                                        bypassBackward = true;
                                    }
                                }
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                html: res.message,
                            });
                        }
                    }
                },
                error: function(jqXHR)
                {
                    document.getElementById("loading").classList.add("d-none");

                    console.error(jqXHR)
                }
            })
        }

        function switchMethodYear(element) {
            console.log(element.checked);

            if (element.checked) {
                toRangeMethodYear();
            } else {
                toQtyMethodYear();
            }
        }

        function toQtyMethodYear() {
            $('#to-qty-year').removeClass('d-none');
            $('#to-range-year').addClass('d-none');

            $('#qty-method-year').removeClass('d-none');
            $('#range-method-year').addClass('d-none');

            methodYear = 'qty';
        }

        function toRangeMethodYear() {
            $('#to-range-year').removeClass('d-none');
            $('#to-qty-year').addClass('d-none');

            $('#range-method-year').removeClass('d-none');
            $('#qty-method-year').addClass('d-none');

            methodYear = 'range';
        }

        function validatePrintYearSequence() {
            if (methodYear == 'qty' && $('#print-qty-year').val() > 0) {
                return true;
            } else if (methodYear == 'range' && Number($('#print-range-awal-year').val()) > 0 && Number($('#print-range-awal-year').val()) <= Number($('#print-range-akhir-year').val()) &&  Number($('#print-range-akhir-year').val()) <= 999999) {
                return true;
            }

            return false
        }

        async function printYearSequence() {
            if (validatePrintYearSequence()) {
                generating = true;

                Swal.fire({
                    title: 'Please Wait...',
                    html: 'Exporting Data...  <br><br> <b>0</b>s elapsed...',
                    didOpen: () => {
                        Swal.showLoading();

                        let estimatedTime = 0;
                        const estimatedTimeElement = Swal.getPopup().querySelector("b");
                        estimatedTimeInterval = setInterval(() => {
                            estimatedTime++;
                            estimatedTimeElement.textContent = estimatedTime;
                        }, 1000);
                    },
                    allowOutsideClick: false,
                });

                let totalPrint = methodYear == 'range' ? Number($("#print-range-akhir-year").val()) - Number($("#print-range-awal-year").val()) + 1 : Number($("#print-qty-year").val());

                let i = 0;
                let qtyI = 0;
                let rangeI = Number($("#print-range-awal-year").val());
                while (i < totalPrint) {
                    if ((i + 1000) > totalPrint) {
                        qtyI = totalPrint - i;
                        i += qtyI;
                    } else {
                        qtyI = 1000;
                        i += qtyI;
                    }

                    console.log(i, qtyI, rangeI, totalPrint);

                    await $.ajax({
                        url: '{{ route('print-year-sequence') }}',
                        type: 'post',
                        data: {
                            method: methodYear,
                            qty: qtyI,
                            year: $("#year-sequence-year").val(),
                            yearSequence: $("#year-sequence-sequence").val(),
                            rangeAwal: rangeI,
                            rangeAkhir: rangeI + qtyI - 1,
                        },
                        xhrFields:
                        {
                            responseType: 'blob'
                        },
                        success: function(res) {
                            if (res) {
                                var blob = new Blob([res], {type: 'application/pdf'});
                                var link = document.createElement('a');
                                link.href = window.URL.createObjectURL(blob);
                                link.download = methodYear == "range" ? "Numbers_"+rangeI+"-"+(rangeI+qtyI-1)+".pdf" : "Numbers.pdf";
                                link.click();
                            }

                            generating = false;
                        },
                        error: function(jqXHR) {
                            console.error(jqXHR)

                            generating = false;

                            clearInterval(estimatedTimeInterval);
                        }
                    });

                    rangeI += qtyI;
                }

                // window.location.reload();

                swal.close();
            } else {
                if (Number($('#print-range-akhir-year').val()) > 999999) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: 'Range Akhir tidak dapat melebihi 999999.',
                        allowOutsideClick: false,
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: 'Qty/Range tidak valid.',
                        allowOutsideClick: false,
                    });
                }
            }
        }

        function validatePrintNewYearSequence() {
            if (Number($('#new-print-range-awal-year').val()) > 0 && Number($('#new-print-range-awal-year').val()) <= Number($('#new-print-range-akhir-year').val())) {
                return true;
            }

            return false
        }

        async function printNewYearSequence() {
            if (validatePrintNewYearSequence()) {
                generating = true;

                Swal.fire({
                    title: 'Please Wait...',
                    html: 'Exporting Data...  <br><br> <b>0</b>s elapsed...',
                    didOpen: () => {
                        Swal.showLoading();

                        let estimatedTime = 0;
                        const estimatedTimeElement = Swal.getPopup().querySelector("b");
                        estimatedTimeInterval = setInterval(() => {
                            estimatedTime++;
                            estimatedTimeElement.textContent = estimatedTime;
                        }, 1000);
                    },
                    allowOutsideClick: false,
                });

                let totalPrint = Number($("#new-print-range-akhir-year").val()) - Number($("#new-print-range-awal-year").val()) + 1;

                let i = 0;
                let qtyI = 0;
                let rangeI = Number($("#new-print-range-awal-year").val());
                while (i < totalPrint) {
                    if ((i + 1000) > totalPrint) {
                        qtyI = totalPrint - i;
                        i += qtyI;
                    } else {
                        qtyI = 1000;
                        i += qtyI;
                    }

                    console.log(i, qtyI, rangeI, totalPrint);

                    await $.ajax({
                        url: '{{ route('print-year-sequence-new-format') }}',
                        type: 'post',
                        data: {
                            method: methodYear,
                            qty: qtyI,
                            year: $("#new-year-sequence-year").val(),
                            yearSequence: $("#new-year-sequence-sequence").val(),
                            rangeAwal: rangeI,
                            rangeAkhir: rangeI + qtyI - 1,
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
                                link.download = methodYear == "range" ? "Numbers_"+rangeI+"-"+(rangeI+qtyI-1)+".pdf" : "Numbers.pdf";
                                link.click();
                            }

                            generating = false;
                        },
                        error: function(jqXHR) {
                            console.error(jqXHR)

                            generating = false;

                            clearInterval(estimatedTimeInterval);
                        }
                    });

                    rangeI += qtyI;
                }

                // window.location.reload();

                swal.close();
            } else {
                if (Number($('#print-range-akhir-year').val()) > 999999) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: 'Range Akhir tidak dapat melebihi 999999.',
                        allowOutsideClick: false,
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: 'Qty/Range tidak valid.',
                        allowOutsideClick: false,
                    });
                }
            }
        }

        async function printYearSequenceAjax(method, qty, year, yearSequence, rangeAwal, rangeAkhir) {
            return $.ajax({
                        url: '{{ route('print-year-sequence') }}',
                        type: 'post',
                        data: {
                            method: method ? method : methodYear,
                            qty: qty ? qty : $("#print-qty-year").val(),
                            year: year ? year : $("#year-sequence-year").val(),
                            yearSequence: yearSequence ? yearSequence : ("#year-sequence-sequence").val(),
                            rangeAwal: rangeAwal ? rangeAwal : $("#print-range-awal-year"),
                            rangeAkhir: rangeAkhir ? rangeAkhir : $("#print-range-akhir-year"),
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
                            console.error(jqXHR)

                            swal.close();

                            generating = false;
                        }
                    });
        }

        var currentPageCheck = 0;
        var stockNumberArr = [];

        $("#checkAllStockNumber").on("change", function () {
            document.getElementById("loading").classList.remove("d-none");

            if (this.checked) {
                $.ajax({
                    url: '{{ route('check-all-stock-number') }}',
                    method: 'post',
                    data: {
                        dateFrom: $("#tgl-awal").val(),
                        dateTo: $("#tgl-akhir").val(),
                        tanggal_filter: $('#tanggal_filter').val(),
                        stocker_filter: $('#stocker_filter').val(),
                        tipe_filter: $('#tipe_filter').val(),
                        part_filter: $('#part_filter').val(),
                        buyer_filter: $('#buyer_filter').val(),
                        ws_filter: $('#ws_filter').val(),
                        style_filter: $('#style_filter').val(),
                        no_form_filter: $('#no_form_filter').val(),
                        no_cut_filter: $('#no_cut_filter').val(),
                        color_filter: $('#color_filter').val(),
                        size_filter: $('#size_filter').val(),
                        dest_filter: $('#dest_filter').val(),
                        group_filter: $('#group_filter').val(),
                        shade_filter: $('#shade_filter').val(),
                        ratio_filter: $('#ratio_filter').val(),
                        stocker_range_filter: $('#stocker_range_filter').val(),
                        qty_filter: $('#qty_filter').val(),
                        year_sequence_filter: $('#year_sequence_filter').val(),
                        numbering_range_filter: $('#numbering_range_filter').val()
                    },
                    success: function (res) {
                        if (res) {
                            stockNumberArr = res;

                            dataTableReload();
                        } else {
                            document.getElementById("loading").classList.add("d-none");
                        }
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);

                        document.getElementById("loading").classList.add("d-none");
                    }
                })
            } else {
                stockNumberArr = [];

                dataTableReload();

                document.getElementById("loading").classList.add("d-none");
            }
        })

        function checkStockNumber(element) {
            let data = $('#datatable').DataTable().row(element.closest('tr')).data();

            if (data) {
                if (element.checked) {
                    stockNumberArr.push(data);
                } else {
                    stockNumberArr = stockNumberArr.filter(item => item.updated_at != data.updated_at && item.id_qr_stocker != data.id_qr_stocker);
                }
            }
        }

        async function printStockNumber() {
            document.getElementById('loading').classList.remove('d-none');

            if (stockNumberArr.length > 0) {
                let chunkSize = 50;

                let i = 0;
                do {
                    let stockNumberArrChunk = stockNumberArr.slice(i, i + chunkSize);

                    if (stockNumberArrChunk) {
                        await $.ajax({
                            url: '{{ route('print-stock-number') }}',
                            method: 'post',
                            data: {
                                stockNumbers: stockNumberArrChunk,
                            },
                            xhrFields:
                            {
                                responseType: 'blob'
                            },
                            success: function (res) {
                                if (res) {
                                    var blob = new Blob([res], {type: 'application/pdf'});
                                    var link = document.createElement('a');
                                    link.href = window.URL.createObjectURL(blob);
                                    link.download = "Stock Numbers.pdf";
                                    link.click();
                                }
                            },
                            error: function (jqXHR) {
                                console.log(jqXHR);
                            }
                        })

                        i += chunkSize;

                        if (i >= stockNumberArr.length) {
                            document.getElementById('loading').classList.add('d-none');
                        }
                    }
                }
                while (i < stockNumberArr.length);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    html: 'Harap pilih stocker',
                    allowOutsideClick: false,
                });

                document.getElementById('loading').classList.add('d-none');
            }
        }
    </script>
@endsection
