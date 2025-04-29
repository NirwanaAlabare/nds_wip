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
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-file fa-sm"></i> Output Cutting Daily</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end gap-3">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div class="mb-3">
                        <label class="form-label"><small>Tanggal</small></label>
                        <div class="d-flex justify-content-start align-items-end gap-3">
                            <input type="date" class="form-control form-control-sm" id="from" name="date-from" value="{{ date('Y-m-d') }}" onchange="datatableReload()">
                            <input type="date" class="form-control form-control-sm" id="to" name="date-to" value="{{ date('Y-m-d') }}" onchange="datatableReload()">
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm mb-3" onclick="datatableReload()"><i class="fa fa-search fa-sm"></i></button>
                </div>
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div class="mb-3">
                        <button class='btn btn-success btn-sm' onclick="exportExcel(this)">
                            <i class="fas fa-file-excel"></i> Export
                        </button>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover table w-100">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Meja</th>
                            <th>Buyer</th>
                            <th>No. WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Panel</th>
                            <th>No. Form</th>
                            <th>Output</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7" class="fw-bold">Total</td>
                            <td colspan="2" class="fw-bold"></td>
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
            // let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            // let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            // let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            // let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            // let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            // $("#from").val(oneWeeksBeforeFull).trigger("change");

            // window.addEventListener("focus", () => {
            //     $('#datatable').DataTable().ajax.reload(null, false);
            // });
        });

        var listFilter = [
            "tanggal_filter",
            "no_meja_filter",
            "buyer_filter",
            "ws_filter",
            "style_filter",
            "color_filter",
            "panel_filter",
            "no_form_filter",
            "output_filter"
        ];

        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            if (i <= 7) {
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
                url: '{{ route('report-cutting-daily') }}',
                data: function(d) {
                    d.dateFrom = $('#from').val();
                    d.dateTo = $('#to').val();
                },
            },
            columns: [
                {
                    data: 'tgl_form_cut'
                },
                {
                    data: 'meja'
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
                    data: 'panel'
                },
                {
                    data: 'no_form'
                },
                {
                    data: 'qty'
                },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap"
                }
            ],
            footerCallback: async function(row, data, start, end, display) {
                var api = this.api(),data;

                $(api.column(0).footer()).html('Total');
                $(api.column(7).footer()).html("...");

                $.ajax({
                    url: 'total-cutting-daily',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: {
                        'dateFrom' : $('#from').val(),
                        'dateTo' : $('#to').val(),
                        'tanggal': $('#tanggal_filter').val(),
                        'noMeja': $('#no_meja_filter').val(),
                        'noForm': $('#no_form_filter').val(),
                        'buyer': $('#buyer_filter').val(),
                        'ws': $('#ws_filter').val(),
                        'style': $('#style_filter').val(),
                        'color': $('#color_filter').val(),
                        'panel': $('#panel_filter').val(),
                        'output': $('#output_filter').val(),
                    },
                    success: function(response) {
                        console.log(response);

                        if (response) {
                            // Update footer by showing the total with the reference of the column index
                            $(api.column(0).footer()).html('Total');
                            $(api.column(7).footer()).html(response['totalCuttingDaily']);
                        }
                    },
                    error: function(jqXHR) {
                        console.log(jqXHR);
                    },
                })
            },
        });

        function datatableReload() {
            datatable.ajax.reload();
        }

        function exportExcel (elm) {
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

            $.ajax({
                url: "{{ route("report-cutting-daily-export") }}",
                type: 'post',
                data: {
                    dateFrom : $('#from').val(),
                    dateTo : $('#to').val()
                },
                xhrFields: { responseType : 'blob' },
                success: function(res) {
                    elm.removeChild(loading);
                    elm.removeAttribute('disabled');
                    elm.innerHTML += "<i class='fa fa-file-excel'></i> Export";

                    iziToast.success({
                        title: 'Success',
                        message: 'Success',
                        position: 'topCenter'
                    });

                    var blob = new Blob([res]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Daily Output Cutting - "+$('#from').val()+" - "+$('#to').val()+".xlsx";
                    link.click();
                }, error: function (jqXHR) {
                    elm.removeChild(loading);
                    elm.removeAttribute('disabled');
                    elm.innerHTML += "<i class='fa fa-file-excel'></i> Export";

                    let res = jqXHR.responseJSON;
                    let message = '';
                    console.log(res.message);
                    for (let key in res.errors) {
                        message += res.errors[key]+' ';
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
