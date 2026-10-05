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
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-file fa-sm"></i> Output Cutting</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end gap-3">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div class="mb-3">
                        <label class="form-label"><small>Tanggal</small></label>
                        <div class="d-flex justify-content-start align-items-end gap-3">
                            <input type="date" class="form-control form-control-sm" id="from" name="date-from"
                                onchange="datatableReload()">
                            <input type="date" class="form-control form-control-sm" id="to" name="date-to"
                                value="{{ date('Y-m-d') }}" onchange="datatableReload()">
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm mb-3" onclick="datatableReload()"><i
                            class="fa fa-search fa-sm"></i></button>
                </div>
                <div class="d-flex align-items-end gap-1 mb-3">
                    <div class="mb-3">
                        <button class="btn btn-success btn-sm" onclick="exportExcel(this)"><i class="fa fa-file-excel"></i>
                            Export</button>
                    </div>
                    <div class="mb-3">
                        <button class="btn btn-outline-success btn-sm" onclick="exportExcel(this, true)"><i class="fa fa-file-excel"></i>
                            Export Detail</button>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover table w-100">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Meja</th>
                            <th>Worksheet</th>
                            <th>Buyer</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Destination</th>
                            <th>Group</th>
                            <th>Lot</th>
                            <th>Cut Number</th>
                            <th>No Form</th>
                            <th>No Marker</th>
                            <th>Panel</th>
                            <th>Qty Form</th>
                            <th>Qty Additional</th>
                            <th>Qty Modify Size</th>
                            <th>Switching Out</th>
                            <th>Switching In</th>
                            <th>Qty Aktual</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="14">Total</th>
                            <th>Qty Form</th>
                            <th>Qty Additional</th>
                            <th>Qty Modify Size</th>
                            <th>Switching Out</th>
                            <th>Switching In</th>
                            <th>Qty Aktual</th>
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
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            $("#from").val(oneWeeksBeforeFull).trigger("change");

            // window.addEventListener("focus", () => {
            //     $('#datatable').DataTable().ajax.reload(null, false);
            // });
        });

        var listFilter = [
            'tanggal_filter',
            'meja_filter',
            'ws_filter',
            'buyer_filter',
            'style_filter',
            'color_filter',
            'size_filter',
            'dest_filter',
            'group_roll_filter',
            'lot_filter',
            'no_cut_filter',
            'no_form_filter',
            'no_marker_filter',
            'panel_filter'
        ];

        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            if (i <= 13) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" id="' + listFilter[i] +
                '"/>');

                $('input', this).on('keyup change', function() {
                    if ($("#datatable").DataTable().column(i).search() !== this.value) {
                        $("#datatable").DataTable()
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
            scrollX: "500px",
            scrollY: "500px",
            pageLength: 50,
            ajax: {
                url: '{{ route('report-cutting') }}',
                data: function(d) {
                    d.dateFrom = $('#from').val();
                    d.dateTo = $('#to').val();
                },
            },
            columns: [
                { data: 'tanggal' },
                { data: 'meja' },
                { data: 'worksheet' },
                { data: 'buyer' },
                { data: 'style' },
                { data: 'color' },
                { data: 'size' },
                { data: 'dest' },
                { data: 'group_roll' },
                { data: 'lot' },
                { data: 'no_cut' },
                { data: 'no_form' },
                { data: 'no_marker' },
                { data: 'panel' },
                { data: 'qty_awal' },
                { data: 'qty_additional' },
                { data: 'qty_modify_size' },
                { data: 'qty_switching_out' },
                { data: 'qty_switching_in' },
                { data: 'qty' },
            ],
            columnDefs: [{
                    targets: [14, 15, 16, 17, 18, 19],
                    className: "text-nowrap text-end",
                    render: (data) => Number(data ?? 0),
                },
                {
                    targets: "_all",
                    defaultContent: "-",
                    className: "text-nowrap"
                }
            ],
            footerCallback: async function(row, data, start, end, display) {
                var api = this.api(),
                    data;

                const totalColumns = {
                    14: 'qty_awal',
                    15: 'qty_additional',
                    16: 'qty_modify_size',
                    17: 'qty_switching_out',
                    18: 'qty_switching_in',
                    19: 'qty',
                };

                $(api.column(0).footer()).html('Total');
                Object.keys(totalColumns).forEach((index) => {
                    $(api.column(Number(index)).footer()).html("...");
                });

                $.ajax({
                    url: 'total-cutting',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: {
                        'dateFrom': $('#from').val(),
                        'dateTo': $('#to').val(),
                        'tanggal': $('#tanggal_filter').val(),
                        'meja': $('#meja_filter').val(),
                        'ws': $('#ws_filter').val(),
                        'buyer': $('#buyer_filter').val(),
                        'style': $('#style_filter').val(),
                        'color': $('#color_filter').val(),
                        'size': $('#size_filter').val(),
                        'dest': $('#dest_filter').val(),
                        'group_roll': $('#group_roll_filter').val(),
                        'lot': $('#lot_filter').val(),
                        'no_cut': $('#no_cut_filter').val(),
                        'no_form': $('#no_form_filter').val(),
                        'no_marker': $('#no_marker_filter').val(),
                        'panel': $('#panel_filter').val()
                    },
                    success: function(response) {
                        if (response && response[0]) {
                            // Update footer by showing the total with the reference of the column index
                            $(api.column(0).footer()).html('Total');
                            Object.entries(totalColumns).forEach(([index, key]) => {
                                $(api.column(Number(index)).footer()).html(Number(response[0][key] ?? 0));
                            });
                        }
                    },
                    error: function(jqXHR) {
                        console.log(jqXHR);
                    },
                })
            },
        });

        function datatableReload() {
            $("#datatable").DataTable().ajax.reload();
        }

        // Legacy Export Excel
        // function exportExcel (elm) {
        //     elm.setAttribute('disabled', 'true');
        //     elm.innerText = "";
        //     let loading = document.createElement('div');
        //     loading.classList.add('loading-small');
        //     elm.appendChild(loading);

        //     iziToast.info({
        //         title: 'Exporting...',
        //         message: 'Data sedang di export. Mohon tunggu...',
        //         position: 'topCenter'
        //     });

        //     let date = new Date();

        //     let day = date.getDate();
        //     let month = date.getMonth() + 1;
        //     let year = date.getFullYear();

        //     // This arrangement can be altered based on how we want the date's format to appear.
        //     let currentDate = `${day}-${month}-${year}`;

        //     $.ajax({
        //         url: "{{ route('report-cutting-export') }}",
        //         type: 'post',
        //         data: {
        //             dateFrom : $('#from').val(),
        //             dateTo : $('#to').val()
        //         },
        //         xhrFields: { responseType : 'blob' },
        //         success: function(res) {
        //             elm.removeChild(loading);
        //             elm.removeAttribute('disabled');
        //             elm.innerHTML += "<i class='fa fa-file-excel'></i> Export";

        //             iziToast.success({
        //                 title: 'Success',
        //                 message: 'Success',
        //                 position: 'topCenter'
        //             });

        //             var blob = new Blob([res]);
        //             var link = document.createElement('a');
        //             link.href = window.URL.createObjectURL(blob);
        //             link.download = "Output Cutting - "+$('#from').val()+" - "+$('#to').val()+".xlsx";
        //             link.click();
        //         }, error: function (jqXHR) {
        //             elm.removeChild(loading);
        //             elm.removeAttribute('disabled');
        //             elm.innerHTML += "<i class='fa fa-file-excel'></i> Export";

        //             let res = jqXHR.responseJSON;
        //             let message = '';
        //             console.log(res.message);
        //             for (let key in res.errors) {
        //                 message += res.errors[key]+' ';
        //                 document.getElementById(key).classList.add('is-invalid');
        //             };
        //             iziToast.error({
        //                 title: 'Error',
        //                 message: message,
        //                 position: 'topCenter'
        //             });
        //         }
        //     });
        // }

        function exportExcel(elm, detail = false) {
            let textBefore = elm.innerText;

            elm.setAttribute('disabled', 'true');
            elm.innerText = "";
            let loading = document.createElement('div');
            loading.classList.add('loading-small');
            elm.appendChild(loading);

            iziToast.info({
                title: 'Exporting...',
                message: 'Data sedang di export. Mohon tunggu...',
                position: 'topCenter'
            });

            let date = new Date();

            let day = date.getDate();
            let month = date.getMonth() + 1;
            let year = date.getFullYear();

            // This arrangement can be altered based on how we want the date's format to appear.
            let currentDate = `${day}-${month}-${year}`;

            let currentRoute = "{{ route('export-cutting-form') }}";
            if (detail) {
                currentRoute = "{{ route('export-cutting-form-part-detail') }}";
            }

            $.ajax({
                url: currentRoute,
                type: 'post',
                data: {
                    dateFrom: $("#from").val(),
                    dateTo: $("#to").val()
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(res) {
                    elm.removeChild(loading);
                    elm.removeAttribute('disabled');
                    let icon = document.createElement('i');
                    icon.classList.add('fa-solid');
                    icon.classList.add('fa', 'fa-file-excel');
                    elm.appendChild(icon);
                    elm.innerHTML += textBefore;

                    iziToast.success({
                        title: 'Success',
                        message: 'Success',
                        position: 'topCenter'
                    });

                    var blob = new Blob([res]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Form Cutting " + $("#from").val() + " - " + $("#to").val() + ".xlsx";
                    link.click();
                },
                error: function(jqXHR) {
                    elm.removeChild(loading);
                    elm.removeAttribute('disabled');
                    let icon = document.createElement('i');
                    icon.classList.add('fa', 'fa-file-excel');
                    elm.appendChild(icon);
                    elm.innerHTML += textBefore;

                    let res = jqXHR.responseJSON;
                    let message = '';
                    console.log(res.message);
                    for (let key in res.errors) {
                        message += res.errors[key] + ' ';
                        document.getElementById(key).classList.add('is-invalid');
                    };
                    iziToast.error({
                        title: 'Error',
                        message: message,
                        position: 'topCenter'
                    });
                }
            });
        }
    </script>
@endsection
