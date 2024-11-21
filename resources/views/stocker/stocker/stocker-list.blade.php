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
                <div class="d-flex justify-content-end align-items-end gap-3">
                    {{-- <button class="btn btn-sb btn-sm" data-bs-toggle="modal" data-bs-target="#printModal"><i class="fa-regular fa-file-lines fa-sm"></i> Print Month Count</button> --}}
                    <button class="btn btn-sb btn-sm" id="print-stock-number" onclick="printStockNumber()"><i class="fa-regular fa-file-lines fa-sm"></i> Print Stock Number</button>
                    <button class="btn btn-sb-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#printYearModal"><i class="fa-regular fa-file-lines fa-sm"></i> Print Year Sequence</button>
                    <button class="btn btn-primary btn-sm d-none" data-bs-toggle="modal" data-bs-target="#printNewYearModal"><i class="fa-regular fa-file-lines fa-sm"></i> Print New Year Sequence</button>
                </div>
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
                            <th>Part</th>
                            <th>Group</th>
                            <th>Shade</th>
                            <th>Ratio</th>
                            <th>Stocker Range</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
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
        });

        var method = 'qty';
        var methodYear = 'qty';

        $("#printModal").on('hide.bs.modal', event => {
            toQtyMethod();

            $("#switch-method").prop("checked", false);
        });

        var stockListFilter = ['action', 'tanggal_filter', 'no_form_filter', 'no_cut_filter', 'color_filter', 'size_filter', 'dest_filter', 'qty_filter', 'year_sequence_filter', 'numbering_range_filter', 'buyer_filter', 'ws_filter', 'style_filter', 'stocker_filter', 'part_filter', 'group_filter', 'shade_filter', 'ratio_filter', 'stocker_range_filter'];

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
                                <a class='btn btn-primary btn-sm' href='{{ route("stocker-list-detail") }}/`+row.form_cut_id+`/`+row.so_det_id+`' target='_blank'><i class='fa fa-search-plus'></i></a>
                                <div class="form-check">
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
                        return data ? `<a class='fw-bold' href='{{ route("show-stocker") }}/`+row.form_cut_id+`' target='_blank'><u>`+data+`</u></a>` : "-";
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
            }
        });

        function dataTableReload() {
            $('#datatable').DataTable().ajax.reload(function () {
                document.getElementById("loading").classList.add("d-none");
            }, false);
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

                                let latestVal = null;
                                for(let i = 0; i < res.length; i++) {
                                    let option = document.createElement("option");
                                    option.setAttribute("value", res[i].year_sequence);
                                    option.innerHTML = res[i].year_sequence;
                                    select.appendChild(option);
                                }

                                $("#new-year-sequence-sequence").val(res[0].year_sequence).trigger("change");
                            } else {
                                let select = document.getElementById('year-sequence-sequence');
                                select.innerHTML = "";

                                let latestVal = null;
                                for(let i = 0; i < res.length; i++) {
                                    let option = document.createElement("option");
                                    option.setAttribute("value", res[i].year_sequence);
                                    option.innerHTML = res[i].year_sequence;
                                    select.appendChild(option);
                                }

                                $("#year-sequence-sequence").val(res[0].year_sequence).trigger("change");
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
                                $('#new-print-range-awal-year').val(res.year_sequence_number);
                            } else {
                                $('#print-range-awal-year').val(res.year_sequence_number);
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
            } else if (methodYear == 'range' && Number($('#print-range-awal-year').val()) > 0 && Number($('#print-range-awal-year').val()) <= Number($('#print-range-akhir-year').val())) {
                return true;
            }

            return false
        }

        async function printYearSequence() {
            if (validatePrintYearSequence()) {
                generating = true;

                Swal.fire({
                    title: 'Please Wait...',
                    html: 'Exporting Data... <br><br> Est. <b>0</b>s...',
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
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    html: 'Qty/Range tidak valid.',
                    allowOutsideClick: false,
                });
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
                    html: 'Exporting Data... <br><br> Est. <b>0</b>s...',
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
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    html: 'Qty/Range tidak valid.',
                    allowOutsideClick: false,
                });
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
                        tanggalFilter: $('#tanggal_filter').val(),
                        stockerFilter: $('#stocker_filter').val(),
                        partFilter: $('#part_filter').val(),
                        buyerFilter: $('#buyer_filter').val(),
                        wsFilter: $('#ws_filter').val(),
                        styleFilter: $('#style_filter').val(),
                        noFormFilter: $('#no_form_filter').val(),
                        noCutFilter: $('#no_cut_filter').val(),
                        colorFilter: $('#color_filter').val(),
                        sizeFilter: $('#size_filter').val(),
                        destFilter: $('#dest_filter').val(),
                        groupFilter: $('#group_filter').val(),
                        shadeFilter: $('#shade_filter').val(),
                        ratioFilter: $('#ratio_filter').val(),
                        stockerRangeFilter: $('#stocker_range_filter').val(),
                        qtyFilter: $('#qty_filter').val(),
                        yearSequenceFilter: $('#year_sequence_filter').val(),
                        numberingRangeFilter: $('#numbering_range_filter').val()
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
