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
    <!-- Card Container -->
    <div class="card card-sb">
        <div class="card-header">
            <h3 class="card-title fw-bold"><i class="fa-solid fa-ticket-simple"></i> Bon Loading</h3>
        </div>
        <div class="card-body">
            <form action="{{ route("store-bon-loading-line") }}" method="post" onsubmit="storeBonLoading(this, event)" id="bon-loading-form">
                <div class="d-flex align-items-end justify-content-between gap-3">
                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <input type="date" class="form-control" name="tanggal_loading" id="tanggal_loading" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <select class="form-select select2bs4" name="year" id="year">
                                @foreach ($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <select class="form-select select2bs4" name="sequence" id="sequence">
                            </select>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <div class="d-flex gap-3">
                        <input type="text" class="form-control" id="no_bon_loading" name="no_bon_loading" placeholder="Nomor...">
                        <select class="form-select select2bs4" name="line_id" id="line_id">
                            <option value="">Pilih Line</option>
                            @foreach ($lines as $line)
                                <option value="{{ $line->line_id }}">{{ $line->FullName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-save"></i> SIMPAN</button>
                </div>

                <!-- Table Inside Card -->
                <div class="table-responsive">
                    <table class="table table-bordered" id="dynamicTable">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>No.WS</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Stocker</th>
                                <th>Qty</th>
                                <th>Range</th>
                            </tr>
                        </thead>
                        <tbody id="stocker-list">
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm deleteBtn"><i class="fa fa-trash"></i></button>
                                </td>
                                <td class="text-nowrap" id="no_ws_1">-</td>
                                <td class="text-nowrap" id="color_1">-</td>
                                <td class="text-nowrap" id="size_1">-</td>
                                <td>
                                    <div class="d-flex">
                                        <input type="text" class="form-control form-control-sm w-auto" data-row='1' id="stocker_1" style="border-radius: 3px 0 0 3px" name="stocker[1]" onkeydown="stockerKeyup(this, event)">
                                        <button class="btn btn-sm btn-outline-success" type="button" data-row='1' id="get_stocker_1" style="border-radius: 0 3px 3px 0" onclick="stockerChange(this)">Get</button>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm w-auto qty" data-row='1' id="qty_1" name="qty[1]" onchange="calculateRange(this, 'qty')" onkeyup="calculateRange(this, 'qty')">
                                </td>
                                <td>
                                    <div class="d-flex gap-3 align-items-end">
                                        <input type="number" class="form-control form-control-sm w-auto" data-row='1' id="range_awal_1" onchange="calculateRange(this, 'range_awal')" onkeyup="calculateRange(this, 'range_awal')" name="range_awal[1]">
                                        <span class="mb-1"> - </span>
                                        <input type="number" class="form-control form-control-sm w-auto" data-row='1' id="range_akhir_1" onchange="calculateRange(this, 'range_akhir')" onkeyup="calculateRange(this, 'range_akhir')" name="range_akhir[1]">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm deleteBtn"><i class="fa fa-trash"></i></button>
                                </td>
                                <td class="text-nowrap" id="no_ws_2">-</td>
                                <td class="text-nowrap" id="color_2">-</td>
                                <td class="text-nowrap" id="size_2">-</td>
                                <td>
                                    <div class="d-flex">
                                        <input type="text" class="form-control form-control-sm w-auto" data-row='2' id="stocker_2" style="border-radius: 3px 0 0 3px" name="stocker[2]" onkeydown="stockerKeyup(this, event)">
                                        <button class="btn btn-sm btn-outline-success" type="button" data-row='2' id="get_stocker_2" style="border-radius: 0 3px 3px 0" onclick="stockerChange(this)">Get</button>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm w-auto qty" data-row='2' id="qty_2" name="qty[2]" onchange="calculateRange(this, 'qty')" onkeyup="calculateRange(this, 'qty')">
                                </td>
                                <td>
                                    <div class="d-flex gap-3 align-items-end">
                                        <input type="number" class="form-control form-control-sm w-auto" data-row='2' id="range_awal_2" onchange="calculateRange(this, 'range_awal')" onkeyup="calculateRange(this, 'range_awal')" name="range_awal[2]">
                                        <span class="mb-1"> - </span>
                                        <input type="number" class="form-control form-control-sm w-auto" data-row='2' id="range_akhir_2" onchange="calculateRange(this, 'range_akhir')" onkeyup="calculateRange(this, 'range_akhir')" name="range_akhir[2]">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm deleteBtn"><i class="fa fa-trash"></i></button>
                                </td>
                                <td class="text-nowrap" id="no_ws_3">-</td>
                                <td class="text-nowrap" id="color_3">-</td>
                                <td class="text-nowrap" id="size_3">-</td>
                                <td>
                                    <div class="d-flex">
                                        <input type="text" class="form-control form-control-sm w-auto" data-row='3' id="stocker_3" style="border-radius: 3px 0 0 3px" name="stocker[3]" onkeydown="stockerKeyup(this, event)">
                                        <button class="btn btn-sm btn-outline-success" type="button" data-row='3' id="get_stocker_3" style="border-radius: 0 3px 3px 0" onclick="stockerChange(this)">Get</button>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm w-auto qty" data-row='3' id="qty_3" name="qty[3]" onchange="calculateRange(this, 'qty')" onkeyup="calculateRange(this, 'qty')">
                                </td>
                                <td>
                                    <div class="d-flex gap-3 align-items-end">
                                        <input type="number" class="form-control form-control-sm w-auto" data-row='3' id="range_awal_3" onchange="calculateRange(this, 'range_awal')" onkeyup="calculateRange(this, 'range_awal')" name="range_awal[3]">
                                        <span class="mb-1"> - </span>
                                        <input type="number" class="form-control form-control-sm w-auto" data-row='3' id="range_akhir_3" onchange="calculateRange(this, 'range_akhir')" onkeyup="calculateRange(this, 'range_akhir')" name="range_akhir[3]">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm deleteBtn"><i class="fa fa-trash"></i></button>
                                </td>
                                <td class="text-nowrap" id="no_ws_4">-</td>
                                <td class="text-nowrap" id="color_4">-</td>
                                <td class="text-nowrap" id="size_4">-</td>
                                <td>
                                    <div class="d-flex">
                                        <input type="text" class="form-control form-control-sm w-auto" data-row='4' id="stocker_4" style="border-radius: 3px 0 0 3px" name="stocker[4]" onkeydown="stockerKeyup(this, event)">
                                        <button class="btn btn-sm btn-outline-success" type="button" data-row='4' id="get_stocker_4" style="border-radius: 0 3px 3px 0" onclick="stockerChange(this)">Get</button>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm w-auto qty" data-row='4' id="qty_4" name="qty[4]" onchange="calculateRange(this, 'qty')" onkeyup="calculateRange(this, 'qty')">
                                </td>
                                <td>
                                    <div class="d-flex gap-3 align-items-end">
                                        <input type="number" class="form-control form-control-sm w-auto" data-row='4' id="range_awal_4" onchange="calculateRange(this, 'range_awal')" onkeyup="calculateRange(this, 'range_awal')" name="range_awal[4]">
                                        <span class="mb-1"> - </span>
                                        <input type="number" class="form-control form-control-sm w-auto" data-row='4' id="range_akhir_4" onchange="calculateRange(this, 'range_akhir')" onkeyup="calculateRange(this, 'range_akhir')" name="range_akhir[4]">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm deleteBtn"><i class="fa fa-trash"></i></button>
                                </td>
                                <td class="text-nowrap" id="no_ws_5">-</td>
                                <td class="text-nowrap" id="color_5">-</td>
                                <td class="text-nowrap" id="size_5">-</td>
                                <td>
                                    <div class="d-flex">
                                        <input type="text" class="form-control form-control-sm w-auto" data-row='5' id="stocker_5" style="border-radius: 3px 0 0 3px" name="stocker[5]" onkeydown="stockerKeyup(this, event)">
                                        <button class="btn btn-sm btn-outline-success" type="button" data-row='5' id="get_stocker_5" style="border-radius: 0 3px 3px 0" onclick="stockerChange(this)">Get</button>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm w-auto qty" data-row='5' id="qty_5" name="qty[5]" onchange="calculateRange(this, 'qty')" onkeyup="calculateRange(this, 'qty')">
                                </td>
                                <td>
                                    <div class="d-flex gap-3 align-items-end">
                                        <input type="number" class="form-control form-control-sm w-auto" data-row='5' id="range_awal_5" onchange="calculateRange(this, 'range_awal')" onkeyup="calculateRange(this, 'range_awal')" name="range_awal[5]">
                                        <span class="mb-1"> - </span>
                                        <input type="number" class="form-control form-control-sm w-auto" data-row='5' id="range_akhir_5" onchange="calculateRange(this, 'range_akhir')" onkeyup="calculateRange(this, 'range_akhir')" name="range_akhir[5]">
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5">Total</td>
                                <td class="fw-bold" id="total_qty"></td>
                                <td><button type="button" class="btn btn-primary btn-sm w-100" id="addRow"><i class="fa fa-plus"></i> Tambah Stocker</button></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold">
                    <i class="fa-solid fa-clock-rotate-left"></i> Loading Line History
                </h5>
                <input type="date" class="form-control form-control-sm w-25" id="tanggal_history" value="{{ date('Y-m-d') }}">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table table-bordered" id="loading-line-history">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nomor</th>
                            <th>Line</th>
                            <th>No. WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Dest</th>
                            <th>Stocker</th>
                            <th>Qty</th>
                            <th>Year</th>
                            <th>Year Sequence</th>
                            <th>Range</th>
                            <th>Timestamp</th>
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
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        // Initial Window On Load Event
        $(document).ready(async function () {
            let today = new Date();
            let todayDate = ("0" + today.getDate()).slice(-2);
            let todayMonth = ("0" + (today.getMonth() + 1)).slice(-2);
            let todayYear = today.getFullYear();
            let todayFull = todayYear + '-' + todayMonth + '-' + todayDate;

            $("#year").val(todayYear).trigger("change");
        });

        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        });

        // Add Row functionality
        document.getElementById('addRow').addEventListener('click', function() {
            const tableBody = document.querySelector('#dynamicTable tbody');
            const newRow = document.createElement('tr');

            // Create editable cells for the new row
            newRow.innerHTML = `
                <tr>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm deleteBtn"><i class="fa fa-trash"></i></button>
                    </td>
                    <td class="text-nowrap" id="no_ws_`+(tableBody.rows.length + 1)+`">-</td>
                    <td class="text-nowrap" id="color_`+(tableBody.rows.length + 1)+`">-</td>
                    <td class="text-nowrap" id="size_`+(tableBody.rows.length + 1)+`">-</td>
                    <td>
                        <div class="d-flex">
                            <input type="text" class="form-control form-control-sm w-auto" data-row='`+(tableBody.rows.length + 1)+`' id="stocker_`+(tableBody.rows.length + 1)+`" style="border-radius: 3px 0 0 3px" name="stocker[`+(tableBody.rows.length + 1)+`]" onkeydown="stockerKeyup(this, event)">
                            <button class="btn btn-sm btn-outline-success" type="button" data-row='`+(tableBody.rows.length + 1)+`' id="get_stocker_`+(tableBody.rows.length + 1)+`" style="border-radius: 0 3px 3px 0" onclick="stockerChange(this)">Get</button>
                        </div>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm w-auto qty" data-row='`+(tableBody.rows.length + 1)+`' id="qty_`+(tableBody.rows.length + 1)+`" name="qty[`+(tableBody.rows.length + 1)+`]" onchange="calculateRange(this, 'qty')" onkeyup="calculateRange(this, 'qty')">
                    </td>
                    <td>
                        <div class="d-flex gap-3 align-items-end">
                            <input type="number" class="form-control form-control-sm w-auto" data-row='`+(tableBody.rows.length + 1)+`' id="range_awal_`+(tableBody.rows.length + 1)+`" onchange="calculateRange(this, 'range_awal')" onkeyup="calculateRange(this, 'range_akhir')" name="range_awal[`+(tableBody.rows.length + 1)+`]">
                            <span class="mb-1"> - </span>
                            <input type="number" class="form-control form-control-sm w-auto" data-row='`+(tableBody.rows.length + 1)+`' id="range_akhir_`+(tableBody.rows.length + 1)+`" onchange="calculateRange(this, 'range_akhir')" onkeyup="calculateRange(this, 'range_akhir')" name="range_akhir[`+(tableBody.rows.length + 1)+`]">
                        </div>
                    </td>
                </tr>
            `;

            // Append the new row to the table
            tableBody.appendChild(newRow);

            // Reattach the event handler for the delete button of the new row
            newRow.querySelector('.deleteBtn').addEventListener('click', function() {
                newRow.remove();
            });
        });

        // Delete Row functionality (for existing rows)
        document.querySelectorAll('.deleteBtn').forEach(function(button) {
            button.addEventListener('click', function() {
                const row = button.closest('tr');
                row.remove();
            });
        });

        $("#year").on("change", () => {
            getSequence();
        });

        $("#sequence").on("change", () => {
            getRangeYearSequence();
        });

        // Get Sequence
        function getSequence() {
            document.getElementById('loading').classList.remove('d-none');

            $.ajax({
                url: '{{ route('get-sequence-year-sequence') }}',
                type: 'get',
                data: {
                    year: $("#year").val()
                },
                dataType: 'json',
                success: async function(res)
                {
                    document.getElementById('loading').classList.add('d-none');

                    if (res) {
                        let select = document.getElementById('sequence');
                        select.innerHTML = "";

                        let latestVal = null;
                        for(let i = res.length-1; i >= 0; i--) {
                            let option = document.createElement("option");
                            option.setAttribute("value", res[i]);
                            option.innerHTML = res[i];
                            select.appendChild(option);
                        }

                        $("#sequence").val(res[res.length-1]).trigger("change");
                    }
                },
                error: function(jqXHR)
                {
                    document.getElementById('loading').classList.add('d-none');
                    console.error(jqXHR)
                }
            })
        }

        function getRangeYearSequence() {
            document.getElementById("loading").classList.remove("d-none");

            $.ajax({
                url: '{{ route('get-range-year-sequence') }}',
                type: 'get',
                data: {
                    year: $("#year").val(),
                    sequence: $("#sequence").val()
                },
                dataType: 'json',
                success: function(res)
                {
                    document.getElementById("loading").classList.add("d-none");

                    console.log("range",res);

                    if (res) {
                        if (res.status != "400") {
                            $("#range_awal_1").val(res.year_sequence_number+1).trigger("change");
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

        function stockerKeyup(element, event) {
            if (event.keyCode === 13) {
                event.preventDefault();

                document.getElementById('loading').classList.remove("d-none");

                getStocker(element);
            }
        }

        function stockerChange(element) {
            document.getElementById('loading').classList.remove("d-none");

            let stocker = document.getElementById("stocker_"+element.getAttribute("data-row"));

            getStocker(stocker);
        }

        function getStocker(stocker) {
            $.ajax({
                url: '{{ route('get-stocker') }}',
                type: 'get',
                data: {
                    stocker: stocker.value
                },
                dataType: 'json',
                success: function(res)
                {
                    document.getElementById('loading').classList.add("d-none");

                    if (res) {
                        document.getElementById("no_ws_"+stocker.getAttribute("data-row")).innerHTML = res.act_costing_ws;
                        document.getElementById("color_"+stocker.getAttribute("data-row")).innerHTML = res.color;
                        document.getElementById("size_"+stocker.getAttribute("data-row")).innerHTML = res.size;
                        $("#qty_"+stocker.getAttribute("data-row")).val(res.qty).trigger("change");

                        $("#stocker_"+(Number(stocker.getAttribute("data-row"))+1)).focus();
                    }
                },
                error: function (jqXHR) {
                    document.getElementById('loading').classList.add("d-none");
                }
            });
        }

        async function calculateRange(element, type) {
            let qty = document.getElementById("qty_"+element.getAttribute('data-row'));
            let rangeAwal = document.getElementById("range_awal_"+element.getAttribute('data-row'));
            let rangeAkhir = document.getElementById("range_akhir_"+element.getAttribute('data-row'));

            if (type == 'qty') {
                if (qty.value && qty.value > 0) {
                    if (rangeAwal.value) {
                        rangeAkhir.value = (Number(rangeAwal.value) + Number(qty.value)) - 1;
                    }
                } else {
                    rangeAwal.value = '';
                    rangeAkhir.value = '';
                }
            } else if (type == 'range_awal') {
                if (rangeAwal.value && rangeAwal.value > 0) {
                    if (qty.value) {
                        rangeAkhir.value = (Number(rangeAwal.value) + Number(qty.value)) - 1;
                    } else if (rangeAkhir.value) {
                        qty.value = (Number(rangeAkhir.value) - Number(rangeAwal.value)) + 1;
                    }
                } else {
                    qty.value = '';
                    rangeAkhir.value = '';
                }
            } else if (type == 'rangeAkhir') {
                if (rangeAkhir.value && rangeAkhir.value > 0) {
                    if (qty.value) {
                        rangeAwal.value = (Number(rangeAkhir.value) - Number(qty.value)) + 1;
                    } else if (rangeAwal.value) {
                        qty.value = (Number(rangeAkhir.value) - Number(rangeAwal.value)) + 1;
                    }
                } else {
                    qty.value = '';
                    rangeAwal.value = '';
                }
            }

            if (rangeAkhir.value) {
                $("#range_awal_"+(Number(element.getAttribute('data-row'))+1)).val(Number(rangeAkhir.value)+1).trigger("change");
            } else {
                $("#range_awal_"+(Number(element.getAttribute('data-row'))+1)).val("").trigger("change");
            }

            sumQty();
        }

        function sumQty() {
            let totalQty = 0;

            document.querySelectorAll('.qty').forEach((qty) => {
                totalQty += Number(qty.value);
            });

            document.getElementById('total_qty').innerHTML = totalQty;
        }

        function storeBonLoading(e, event) {
            if (document.getElementById("line_id").value && document.getElementById("no_bon_loading").value) {
                Swal.fire({
                    icon: "info",
                    title: "Konfirmasi",
                    html: "Simpan Bon Loading?",
                    showDenyButton: true,
                    showCancelButton: false,
                    confirmButtonText: "Lanjut",
                    denyButtonText: "Batal"
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitForm(e, event);
                    } else {
                        Swal.fire("Dibatalkan", "", "info");
                    }
                })
            } else {
                if (!document.getElementById("line_id").value) {
                    Swal.fire("Harap Pilih Line", "", "error");
                }

                if (!document.getElementById("no_bon_loading").value) {
                    Swal.fire("Harap Isi Nomor", "", "error");
                }
            }
        }

        // const filters = ["line_filter", "tanggal_filter", "ws_filter", "style_filter", "color_filter", "size_filter", "dest_filter"];
        $('#loading-line-history thead tr').clone(true).appendTo('#loading-line-history thead');
        $('#loading-line-history thead tr:eq(1) th').each(function(i) {
            // if (i == 0 || i == 1 || i == 2 || i == 3 || i == 4 || i == 5 || i == 6) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" style="width:100%" />');

                $('input', this).on('keyup change', function() {
                    if (loadingLineHistory.column(i).search() !== this.value) {
                        loadingLineHistory
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            // } else {
            //     $(this).empty();
            // }
        });

        var loadingLineHistory = $("#loading-line-history").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            pageLength: 100,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('bon-loading-line-history') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.tanggal = $('#tanggal_history').val();
                },
            },
            columns: [
                {
                    data: 'tanggal'
                },
                {
                    data: 'no_bon_loading'
                },
                {
                    data: 'line_id'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'styleno'
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
                    data: 'id_qr_stocker'
                },
                {
                    data: 'qty'
                },
                {
                    data: 'year'
                },
                {
                    data: 'year_sequence'
                },
                {
                    data: 'year_sequence_range'
                },
                {
                    data: 'updated_at'
                }
            ],
            columnDefs: [
                // Text No Wrap
                {
                    targets: "_all",
                    className: "text-nowrap"
                },
                {
                    target: [13],
                    render: (data, type, row, meta) => {
                        console.log(data);
                        return moment(data).format("MM-DD-YYYY HH:mm");
                    }
                }
            ],
        });
    </script>
@endsection
