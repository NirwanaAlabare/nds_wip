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
    {{-- <form action="{{ route('export_excel') }}" method="get"> --}}
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-toilet-paper fa-sm"></i> Manajemen Roll</h5>
            </div>
            <div class="card-body">
                <div class="row justify-content-between align-items-end">
                    <div class="col-12 col-md-6">
                        <div class="d-flex align-items-end gap-3 mb-3">
                            <div class="mb-3">
                                <label class="form-label"><small>Tanggal Awal</small></label>
                                <input type="date" class="form-control form-control-sm" id="from" name="from" value="{{ date('Y-m-d') }}" onchange="datatableReload()">
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><small>Tanggal Akhir</small></label>
                                <input type="date" class="form-control form-control-sm" id="to" name="to" value="{{ date('Y-m-d') }}" onchange="datatableReload()">
                            </div>
                            <button type="button" class="btn btn-primary btn-sm mb-3"><i class="fa fa-search fa-sm"></i></button>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="d-flex justify-content-end align-items-end gap-3 mb-3">
                            <div class="mb-3">
                                <select class="form-select form-select-sm select2bs4" style="min-width:200px;" name="supplier" id="supplier">
                                    <option value="">Pilih Buyer</option>
                                </select>

                            </div>
                            <div class="mb-3">
                                <select class="form-select form-select-sm select2bs4" style="min-width:150px;" name="order" id="order">
                                    <option value="">Pilih WS</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <button type='button' id="export_excel" name='export_excel' class='btn btn-success' onclick="exportExcel(this)">
                                    <i class="fas fa-file-excel"></i> Export
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-hover table-sm w-100">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th>Tanggal Input</th>
                                <th>No. Form</th>
                                <th>Meja</th>
                                <th>No. WS</th>
                                <th>Buyer</th>
                                <th>Style</th>
                                <th>Color</th>
                                <th>Color Actual</th>
                                <th>Panel</th>
                                <th>Qty Order</th>
                                <th>Cons. WS</th>
                                <th>Cons. Marker</th>
                                <th>Cons. Ampar</th>
                                <th>Cons. Actual</th>
                                <th>Cons. Piping</th>
                                <th>Panjang Marker</th>
                                <th>Unit Panjang Marker</th>
                                <th>Comma Marker</th>
                                <th>Unit Comma Marker</th>
                                <th>Lebar Marker</th>
                                <th>Unit Lebar Marker</th>
                                <th>Panjang Actual</th>
                                <th>Unit Panjang Actual</th>
                                <th>Comma Actual</th>
                                <th>Unit Comma Actual</th>
                                <th>Lebar Actual</th>
                                <th>Unit Lebar Actual</th>
                                <th>ID Roll</th>
                                <th>ID Item</th>
                                <th>Detail Item</th>
                                <th>No. Roll</th>
                                <th>Lot</th>
                                <th>Group</th>
                                <th>Qty Roll</th>
                                <th>Unit Roll</th>
                                <th>Berat Amparan (KGM)</th>
                                <th>Estimasi Amparan</th>
                                <th>Lembar Amparan</th>
                                <th>Ratio</th>
                                <th>Qty Cut</th>
                                <th>Average Time</th>
                                <th>Sisa Gelaran</th>
                                <th>Sambungan</th>
                                <th>Sambungan Roll</th>
                                <th>Kepala Kain</th>
                                <th>Sisa Tidak Bisa</th>
                                <th>Reject</th>
                                <th>Piping</th>
                                <th>Sisa Kain</th>
                                <th>Pemakaian Gelar</th>
                                <th>Total Pemakaian Roll</th>
                                <th>Short Roll</th>
                                <th>Short Roll (%)</th>
                                <th>Operator</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    {{-- </form> --}}
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
        document.addEventListener("DOMContentLoaded", async function () {
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            // $("#from").val(oneWeeksBeforeFull).trigger("change");

            // window.addEventListener("focus", () => {
            //     datatableReload();
            // });
            await getSupplierList();

            await getOrderList();
        });

        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        $('.select2').select2();

        $('.select2bs4').select2({
            theme: 'bootstrap4',
        });

        // Select Supplier
        $('#supplier').on('change', function (e) {
            getOrderList();

            datatableReload();
        });

        // Select Order
        $('#order').on('change', function (e) {
            datatableReload();
        });

        // Option Supplier
        function getSupplierList() {
            return $.ajax({
                url: '{{ route('roll-get-supplier') }}',
                method: 'GET',
                dataType: 'json',
                success: function (res) {
                    let select = document.getElementById("supplier");

                    select.innerHTML = null;
                    let initOpt = document.createElement('option');
                    initOpt.setAttribute("value", "");
                    initOpt.innerHTML = "Pilih Buyer";
                    select.appendChild(initOpt);

                    if (res && res.length > 0) {
                        for (let i = 0; i < res.length; i++) {
                            let opt = document.createElement('option');
                            opt.setAttribute("value", res[i]['id']);
                            opt.innerHTML = res[i]['name'];

                            select.appendChild(opt);
                        }
                    }
                },
                error: function (jqXHR) {
                    console.log(res);
                }
            });
        }

        // Order Option
        function getOrderList() {
            return $.ajax({
                url: '{{ route('roll-get-order') }}',
                method: 'GET',
                dataType: 'json',
                data: {
                    supplier: $("#supplier").val()
                },
                success: function (res) {
                    let select = document.getElementById("order");

                    select.innerHTML = null;
                    let initOpt = document.createElement('option');
                    initOpt.setAttribute("value", "");
                    initOpt.innerHTML = "Pilih WS";
                    select.appendChild(initOpt);

                    if (res && res.length > 0) {
                        for (let i = 0; i < res.length; i++) {
                            let opt = document.createElement('option');
                            opt.setAttribute("value", res[i]['id_ws']);
                            opt.innerHTML = res[i]['no_ws'];

                            select.appendChild(opt);
                        }
                    }
                },
                error: function (jqXHR) {
                    console.log(res);
                }
            });
        }

        function exportExcel(elm, order, buyer) {
            if ((!$("#from").val() || !$("#to").val()) && (!$("#order").val() && !$("#supplier").val())) {
                elm.removeAttribute('disabled');
                elm.innerText = "Export ";
                let icon = document.createElement('i');
                icon.classList.add('fa-solid');
                icon.classList.add('fa-file-excel');
                elm.appendChild(icon);

                return Swal.fire({
                    icon: 'warning',
                    title: 'Gagal',
                    text: 'Harap tentukan tanggal awal/akhir atau pilih ws/buyer.',
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                });
            }

            order ? order : order = $("#order").val();
            buyer ? buyer : buyer = $("#supplier").val();

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
                url: "{{ route("export_excel_manajemen_roll") }}",
                type: 'post',
                data: {
                    dateFrom: $("#from").val(),
                    dateTo: $("#to").val(),
                    id_ws:order,
                    buyer:buyer,
                },
                xhrFields: { responseType : 'blob' },
                success: function(res) {
                    elm.removeAttribute('disabled');
                    elm.innerText = "Export ";
                    let icon = document.createElement('i');
                    icon.classList.add('fa-solid');
                    icon.classList.add('fa-file-excel');
                    elm.appendChild(icon);

                    iziToast.success({
                        title: 'Success',
                        message: 'Success',
                        position: 'topCenter'
                    });

                    var blob = new Blob([res]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = currentDate+" - "+($('#supplier').val() ? "'"+$('#supplier').find(":selected").text()+"'" : "All Supplier")+" - "+($('#order').val() ? "'"+$('#order').find(":selected").text()+"'" : "All WS")+" - '"+"' Manajemen Roll.xlsx";
                    link.click();
                }, error: function (jqXHR) {
                    elm.removeAttribute('disabled');
                    elm.innerText = "Export ";
                    let icon = document.createElement('i');
                    icon.classList.add('fa-solid');
                    icon.classList.add('fa-file-excel');
                    elm.appendChild(icon);

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

        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            if (i <= 9 || i == 17 || i == 21 || i == 23 || i == 25 || i == 26 || i == 27 || i == 28 || i == 29 || i == 30 || i == 31) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" />');

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
                url: '{{ route('lap_pemakaian_data') }}',
                method: "POST",
                data: function(d) {
                    d.supplier = $('#supplier').val() ? $('#supplier').find(":selected").text() : null;
                    d.id_ws = $('#order').val();
                    d.dateFrom = $('#from').val();
                    d.dateTo = $('#to').val();
                },
            },
            columns: [
                {
                    data: "bulan"
                },
                {
                    data: "tgl_input"
                },
                {
                    data: "no_form_cut_input"
                },
                {
                    data: "nama_meja"
                },
                {
                    data: "act_costing_ws"
                },
                {
                    data: "buyer"
                },
                {
                    data: "style"
                },
                {
                    data: "color"
                },
                {
                    data: "color_act"
                },
                {
                    data: "panel"
                },
                {
                    data: "qty"
                },
                {
                    data: "cons_ws"
                },
                {
                    data: "cons_marker"
                },
                {
                    data: "cons_ampar"
                },
                {
                    data: "cons_act"
                },
                {
                    data: "cons_piping"
                },
                {
                    data: "panjang_marker"
                },
                {
                    data: "unit_panjang_marker"
                },
                {
                    data: "comma_marker"
                },
                {
                    data: "unit_comma_marker"
                },
                {
                    data: "lebar_marker"
                },
                {
                    data: "unit_lebar_marker"
                },
                {
                    data: "panjang_actual"
                },
                {
                    data: "unit_panjang_actual"
                },
                {
                    data: "comma_actual"
                },
                {
                    data: "unit_comma_actual"
                },
                {
                    data: "lebar_actual"
                },
                {
                    data: "unit_lebar_actual"
                },
                {
                    data: "id_roll"
                },
                {
                    data: "id_item"
                },
                {
                    data: "detail_item"
                },
                {
                    data: "roll"
                },
                {
                    data: "lot"
                },
                {
                    data: "group_roll"
                },
                {
                    data: "qty_roll"
                },
                {
                    data: "unit_roll"
                },
                {
                    data: "berat_amparan"
                },
                {
                    data: "est_amparan"
                },
                {
                    data: "lembar_gelaran"
                },
                {
                    data: "total_ratio"
                },
                {
                    data: "qty_cut"
                },
                {
                    data: "average_time"
                },
                {
                    data: "sisa_gelaran"
                },
                {
                    data: "sambungan"
                },
                {
                    data: "sambungan_roll"
                },
                {
                    data: "kepala_kain"
                },
                {
                    data: "sisa_tidak_bisa"
                },
                {
                    data: "reject"
                },
                {
                    data: "piping"
                },
                {
                    data: "sisa_kain"
                },
                {
                    data: "pemakaian_lembar"
                },
                {
                    data: "total_pemakaian_roll"
                },
                {
                    data: "short_roll"
                },
                {
                    data: "short_roll_percentage"
                },
                {
                    data: "operator"
                }
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap"
                }
            ],
        });

        function datatableReload() {
            datatable.ajax.reload();
        }
    </script>
@endsection
