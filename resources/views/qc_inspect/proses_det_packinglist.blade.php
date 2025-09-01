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
    <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="modalCostingLabel"><i class="fas fa-list"></i> List Form QC Inspect</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="datatable_modal" class="table table-bordered w-100 text-nowrap">
                            <thead class="bg-sb">
                                <tr>
                                    <th scope="col" class="text-center align-middle" style="color: white;">Act</th>
                                    <th scope="col" class="text-center align-middle">Tanggal</th>
                                    <th scope="col" class="text-center align-middle">No. Mesin</th>
                                    <th scope="col" class="text-center align-middle">No. Form</th>
                                    <th scope="col" class="text-center align-middle">No. PL</th>
                                    <th scope="col" class="text-center align-middle">Buyer</th>
                                    <th scope="col" class="text-center align-middle">WS</th>
                                    <th scope="col" class="text-center align-middle">Style</th>
                                    <th scope="col" class="text-center align-middle">Color</th>
                                    <th scope="col" class="text-center align-middle">ID Item</th>
                                    <th scope="col" class="text-center align-middle">Fabric</th>
                                    <th scope="col" class="text-center align-middle">Supplier</th>
                                    <th scope="col" class="text-center align-middle">Group Inspect</th>
                                    <th scope="col" class="text-center align-middle">Lot</th>
                                    <th scope="col" class="text-center align-middle">No. Roll</th>
                                    <th scope="col" class="text-center align-middle">Point / Max Point</th>
                                    <th scope="col" class="text-center align-middle">Visual Defect Result</th>
                                    <th scope="col" class="text-center align-middle">Short Roll Result</th>
                                    <th scope="col" class="text-center align-middle">Founding Issue Result</th>
                                    <th scope="col" class="text-center align-middle">Final Result</th>
                                    <th scope="col" class="text-center align-middle">Note</th>
                                    <th scope="col" class="text-center align-middle">Status</th>
                                    <th scope="col" class="text-center align-middle">Proses</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="ModalBlanket" tabindex="-1" aria-labelledby="modalBlanketLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="modalBlanketLabel"><i class="fas fa-list"></i> Blanket</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <form id="blanketUploadForm" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-body text-center" id="photoModalBody" style="max-height: 70vh; overflow-y: auto;">
                        <div id="photoPreview"></div>

                        <div class="mb-3">
                            <label class="form-label" for="photoInput">Upload / Capture Blanket Photo</label>
                            <input type="file" name="photo" accept="image/*" capture="environment"
                                class="form-control" id="photoInput">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="rateSelect">Rate</label>
                            <!-- Rate Dropdown -->
                            <select name="rateSelect" id="rateSelect" class="form-select" required>
                                <option value="" selected disabled>-- Select Rate --</option>
                                <option value="1">1</option>
                                <option value="0.5">1/2</option>
                                <option value="2">2</option>
                                <option value="2.5">2/3</option>
                                <option value="3">3</option>
                                <option value="3.5">3/4</option>
                                <option value="4">4</option>
                                <option value="4.5">4/5</option>
                                <option value="5">5</option>
                            </select>
                        </div>

                        <!-- Conditional Checkbox for REJECT Override -->
                        <div class="mb-3" id="passCheckboxContainer" style="display: none;">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="passCheckbox"
                                    name="pass">
                                <label class="form-check-label" for="passCheckbox">
                                    Pass With Condition
                                </label>
                            </div>
                        </div>



                        <!-- Result (PASS/REJECT) -->
                        <div class="mb-3">
                            <label class="form-label">Result</label>
                            <input type="text" class="form-control" id="rateResult" name="rateResult" readonly>
                        </div>


                        <!-- Hidden Fields -->
                        <input type="hidden" name="id_item" id="input_id_item">
                        <input type="hidden" name="id_jo" id="input_id_jo">
                        <input type="hidden" name="no_invoice" id="input_no_invoice">
                        <input type="hidden" name="no_lot" id="input_no_lot">
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Upload</button>
                    </div>
                </form>

            </div>
        </div>
    </div>



    <form method="POST" name='form_generate' id='form_generate'>
        @csrf
        <div class="card card-sb">
            <div class="card-header text-start">
                <h5 class="card-title fw-bold mb-1">
                    <i class="fas fa-list"></i> Detail Packing List
                </h5>
            </div>

            <div class="card-body pb-0">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="txttgl_dok"><small><b>Tgl Dok :</b></small></label>
                        <input type="text" id="txttgl_dok" name="txttgl_dok"
                            class="form-control form-control-sm border-primary" value="{{ $tgl_dok_fix }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label for="txtno_inv"><small><b>No. Packing List :</b></small></label>
                        <input type="text" id="txtno_inv" name="txtno_inv"
                            class="form-control form-control-sm border-primary" value="{{ $no_invoice }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label for="txtbuyer"><small><b>Buyer :</b></small></label>
                        <input type="text" id="txtbuyer" name="txtbuyer"
                            class="form-control form-control-sm border-primary" value="{{ $buyer }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label for="txtstyle"><small><b>Style :</b></small></label>
                        <input type="text" id="txtstyle" name="txtstyle"
                            class="form-control form-control-sm border-primary" value="{{ $styleno }}" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="txtcolor"><small><b>Color :</b></small></label>
                        <input type="text" id="txtcolor" name="txtcolor"
                            class="form-control form-control-sm border-primary" value="{{ $color }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label for="txtitemdesc"><small><b>Item Desc :</b></small></label>
                        <input type="text" id="txtitemdesc" name="txtitemdesc"
                            class="form-control form-control-sm border-primary" value="{{ $itemdesc }}" readonly>
                    </div>
                    <div class="col-md-1">
                        <label for="txtid_item"><small><b>ID Item :</b></small></label>
                        <input type="text" id="txtid_item" name="txtid_item"
                            class="form-control form-control-sm border-primary" value="{{ $id_item }}" readonly>
                        <input type="hidden" id="txtid_jo" name="txtid_jo" value="{{ $id_jo }}">
                    </div>
                    <div class="col-md-1">
                        <label for="txtjml_lot"><small><b>Jml Lot :</b></small></label>
                        <input type="text" id="txtjml_lot" name="txtjml_lot"
                            class="form-control form-control-sm border-primary" value="{{ $jml_lot }}" readonly>
                    </div>
                    <div class="col-md-1">
                        <label for="txtjml_roll"><small><b>Jml Roll :</b></small></label>
                        <input type="text" id="txtjml_roll" name="txtjml_roll"
                            class="form-control form-control-sm border-primary" value="{{ $jml_roll }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label for="txttype_pch"><small><b>Notes :</b></small></label>
                        <input type="text" id="txttype_pch" name="txttype_pch"
                            class="form-control form-control-sm border-primary" value="{{ $type_pch }}" readonly>
                        <input type="hidden" id="txtcount" name="txtcount"
                            class="form-control form-control-sm border-primary" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="cbo_group_def"><small><b>Group Inspect :</b></small></label>
                        <select id="cbo_group_def" name="cbo_group_def" class="form-control form-control-sm select2bs4"
                            style="width: 100%; font-size: 0.875rem;" required>
                            <option value="" disabled>Pilih Group Inspect</option>
                            @foreach ($data_group as $dg)
                                <option value="{{ $dg->isi }}" {{ $group_inspect == $dg->isi ? 'selected' : '' }}>
                                    {{ $dg->tampil }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="txtcek_inspect"><small><b>Cek Inspect :</b></small></label>
                        <div class="input-group input-group-sm">
                            <input type="number" id="txtcek_inspect" name="txtcek_inspect"
                                class="form-control border-primary" min="0" max="100"
                                value="{{ $cek_inspect }}">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <input type="button" class="btn btn-primary w-100" value="Calculate" onclick="calculate();">
            </div>

        </div>

        <div class="card card-sb">
            <div class="card-header text-start">
                <h5 class="card-title fw-bold mb-1">
                    <i class="fas fa-list"></i> Detail Roll Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered w-100 text-nowrap">
                        <thead class="bg-sb">
                            <tr style="text-align:center; vertical-align:middle">
                                <th scope="col">No. Lot</th>
                                <th scope="col">Jml Roll</th>
                                <th scope="col">Jml Roll (Cek)</th>
                                <th scope="col">Total Form</th>
                                <th scope="col">Form Done</th>
                                <th scope="col">Cek Inspect</th>
                                <th scope="col">Proses</th>
                                <th scope="col">Shipment Point</th>
                                <th scope="col">Max Shipment Point</th>
                                <th scope="col">Visual Defect Result</th>
                                <th scope="col">Max Width Short Roll</th>
                                <th scope="col">Max Length Short Roll</th>
                                <th scope="col">Founding Issue</th>
                                <th scope="col">Blanket Result</th>
                                <th scope="col">Final Inspect Result</th>
                                <th scope="col">Blanket</th>
                                <th scope="col">Act</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr class="text-end">
                                <th>Total</th>
                                <th></th> <!-- For Jml Roll total -->
                                <th></th> <!-- For Jml Roll (Cek) total -->
                                <th></th> <!-- For Total Form total -->
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
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
            <div class="card-footer">
                <div class="d-flex justify-content-end">
                    <input type="button" class="btn btn-primary" id="btn-generate" value="Generate"
                        onclick="generate_form();">
                </div>

            </div>
        </div>

        <div class="card card-sb" id="inspect_pertama" style="cursor: pointer;">
            <div class="card-header text-start">
                <h5 class="card-title fw-bold mb-1">
                    <i class="fas fa-list"></i> Inspect Pertama
                </h5>
            </div>
            <div id="inspect_pertama_collapse" class="collapse"> <!-- Removed 'show' -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="datatable_pertama" class="table table-bordered w-100 text-nowrap">
                            <thead class="bg-sb">
                                <tr style="text-align:center; vertical-align:middle">
                                    <th>No. Lot</th>
                                    <th>Jml Roll</th>
                                    <th>Jml Roll (Cek)</th>
                                    <th>Total Form</th>
                                    <th>Form Done</th>
                                    <th>Cek Inspect</th>
                                    <th>Proses</th>
                                    <th>Shipment Point</th>
                                    <th>Max Shipment Point</th>
                                    <th>Visual Defect Result</th>
                                    <th>Max Width Short Roll</th>
                                    <th>Max Length Short Roll</th>
                                    <th>Founding Issue</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr class="text-end">
                                    <th>Total</th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
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
        </div>

        <div class="card card-sb" id="inspect_kedua" style="cursor: pointer;">
            <div class="card-header text-start">
                <h5 class="card-title fw-bold mb-1">
                    <i class="fas fa-list"></i> Inspect Kedua
                </h5>
            </div>
            <div id="inspect_kedua_collapse" class="collapse"> <!-- Removed 'show' -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="datatable_kedua" class="table table-bordered w-100 text-nowrap">
                            <thead class="bg-sb">
                                <tr style="text-align:center; vertical-align:middle">
                                    <th>No. Lot</th>
                                    <th>Jml Roll</th>
                                    <th>Jml Roll (Cek)</th>
                                    <th>Total Form</th>
                                    <th>Form Done</th>
                                    <th>Cek Inspect</th>
                                    <th>Proses</th>
                                    <th>Shipment Point</th>
                                    <th>Max Shipment Point</th>
                                    <th>Visual Defect Result</th>
                                    <th>Max Width Short Roll</th>
                                    <th>Max Length Short Roll</th>
                                    <th>Founding Issue</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr class="text-end">
                                    <th>Total</th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
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
        </div>


    </form>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: 'resolve' // Ensures it respects the 100% width from inline style or Bootstrap
        });
        // Now set height and font-size on the Select2 container after init
        $('.select2-container--bootstrap4 .select2-selection--single').css({
            'height': '30px', // your desired height
            'font-size': '12px', // your desired font size
            'line-height': '30px' // vertically center text
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        $(document).ready(function() {
            calculate();
            const groupVal = '{{ $group_inspect }}';

            if (groupVal) {
                $('#cbo_group_def').val(groupVal).trigger('change');
            }

            $('#inspect_pertama .card-header').on('click', function(e) {
                $('#inspect_pertama_collapse').collapse('toggle');
                show_inspect_pertama();
            });

            $('#inspect_kedua .card-header').on('click', function(e) {
                $('#inspect_kedua_collapse').collapse('toggle');
                show_inspect_kedua();
            });

        })

        function calculate() {
            // Destroy existing DataTable instance if it exists
            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().clear().destroy();
            }

            // Now initialize it again
            let datatable = $("#datatable").DataTable({
                ordering: false,
                processing: true,
                serverSide: false,
                paging: false,
                searching: true,
                scrollY: true,
                scrollX: true,
                scrollCollapse: false,
                ajax: {
                    url: '{{ route('show_calculate_qc_inspect') }}',
                    data: function(d) {
                        d.id_item = $('#txtid_item').val();
                        d.id_jo = $('#txtid_jo').val();
                        d.no_inv = $('#txtno_inv').val();
                        d.cek_inspect = $('#txtcek_inspect').val();
                        d.cbo_group_def = $('#cbo_group_def').val();
                    },
                },
                columns: [{
                        data: 'no_lot'
                    },
                    {
                        data: 'jml_roll'
                    },
                    {
                        data: 'jml_roll_cek'
                    },
                    {
                        data: 'tot_form'
                    },
                    {
                        data: 'tot_form_done'
                    },
                    {
                        data: 'cek_inspect'
                    },
                    {
                        data: 'proses'
                    },
                    {
                        data: 'shipment_point'
                    },
                    {
                        data: 'max_shipment'
                    },
                    {
                        data: 'result',
                        className: 'text-center',
                    },
                    {
                        data: 'max_width_short_roll',
                        className: 'text-center',
                        render: function(data) {
                            return parseFloat(data).toFixed(2); // force 2 decimal digits
                        }
                    },
                    {
                        data: 'max_length_short_roll',
                        className: 'text-center',
                        render: function(data) {
                            return parseFloat(data).toFixed(2); // force 2 decimal digits
                        }
                    },
                    {
                        data: 'list_founding_issue'
                    },
                    {
                        data: 'result_blanket'
                    },
                    {
                        data: 'final_result'
                    },
                    {
                        data: 'photo'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            let html = `
                    <div class="text-center align-middle visual-input">
            <input
                type="button"
                class="btn btn-primary btn-sm"
                value="Show"
                onclick="show_list_form('${data.id_item}', '${data.id_jo}', '${data.no_invoice}', '${data.no_lot}')">`;

                            if (data.gen_more === 'Y') {
                                html +=
                                    `
            <input
                type="button"
                class="btn btn-success btn-sm ms-2"
                value="Generate"
                onclick="generate_kedua('${data.id_item}', '${data.id_jo}', '${data.no_invoice}', '${data.no_lot}', '${data.cek_inspect}', '${data.group_inspect}', '${data.tot_form}')">`;
                            }

                            if (data.stat_reject === 'Y') {
                                html +=
                                    `
            <input
                type="button"
                class="btn btn-danger btn-sm ms-2"
                value="Pass With Condition"
                onclick="pass_with_condition('${data.id_item}', '${data.id_jo}', '${data.no_invoice}', '${data.no_lot}')">`;
                            }

                            // ✅ Add "Blanket" button unconditionally or conditionally if needed
                            html +=
                                `
            <input
                type="button"
                class="btn btn-warning btn-sm ms-2"
                value="Blanket"
                onclick="show_list_blanket('${data.id_item}', '${data.id_jo}', '${data.no_invoice}', '${data.no_lot}')">`;

                            // ✅ Add your new "Print" button here
                            html +=
                                `
        <input
            type="button"
            class="btn btn-info btn-sm ms-2"
            value="Print Sticker"
onclick="print_inspection('${data.id_item}', '${data.id_jo}', '${data.no_invoice}', '${data.no_lot}', '${data.final_result}')">`;

                            html += `</div>`;
                            return html;
                        }
                    }


                ],

                createdRow: function(row, data, dataIndex) {
                    $('td', row).addClass('text-end'); // align only <td> to right
                    // Apply background color for full status
                    if (data.status_lot === 'Y') {
                        $(row).addClass('table-success');
                    }
                },

                footerCallback: function(row, data, start, end, display) {
                    let api = this.api();

                    // Helper function to parse float safely
                    let parseVal = function(i) {
                        return typeof i === 'string' ?
                            parseFloat(i.replace(/[\$,]/g, '')) || 0 : typeof i === 'number' ? i : 0;
                    };

                    // Total for Jml Roll (column index 1)
                    let totalJmlRoll = api.column(1).data().reduce((a, b) => parseVal(a) + parseVal(b), 0);

                    // Total for Jml Roll (Cek) (column index 2)
                    let totalJmlRollCek = api.column(2).data().reduce((a, b) => parseVal(a) + parseVal(b), 0);

                    // Total for Total Form (column index 3)
                    let totalForm = api.column(3).data().reduce((a, b) => parseVal(a) + parseVal(b), 0);

                    // Total for Total Form (column index 3)
                    let totalFormDone = api.column(4).data().reduce((a, b) => parseVal(a) + parseVal(b), 0);

                    // Update footer
                    $(api.column(1).footer()).html(totalJmlRoll.toLocaleString());
                    $(api.column(2).footer()).html(totalJmlRollCek.toLocaleString());
                    $(api.column(3).footer()).html(totalForm.toLocaleString());
                    $(api.column(4).footer()).html(totalFormDone.toLocaleString());
                }
            });

            // 👇 Add this block to update #txtcount after data is loaded
            $('#datatable').on('xhr.dt', function(e, settings, json, xhr) {
                if (json && json.status_lot_n_count !== undefined) {
                    $('#txtcount').val(json.status_lot_n_count);

                    if (parseInt(json.status_lot_n_count) === 0) {
                        $('#btn-generate').hide(); // 🔴 Hide if 0
                    } else {
                        $('#btn-generate').show(); // ✅ Show if > 0
                    }
                }

            });

        }

        function generate_form() {
            // Collect values from the input fields
            let id_item = $('#txtid_item').val();
            let id_jo = $('#txtid_jo').val();
            let no_inv = $('#txtno_inv').val();
            let cbo_group_def = $('#cbo_group_def').val();
            let cek_inspect = $('#txtcek_inspect').val();

            // Validate required input
            if (!cbo_group_def || cbo_group_def.trim() === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Field "Group Inspect" wajib diisi.',
                });
                return;
            }

            // Send AJAX POST request
            $.ajax({
                type: "POST",
                url: '{{ route('generate_qc_inspect') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    id_item: id_item,
                    id_jo: id_jo,
                    no_inv: no_inv,
                    cbo_group_def: cbo_group_def,
                    cek_inspect: cek_inspect
                },
                success: function(response) {
                    // Group the generated_forms by no_lot
                    let formMap = {};
                    response.generated_forms.forEach(form => {
                        if (!formMap[form.no_lot]) {
                            formMap[form.no_lot] = [];
                        }
                        formMap[form.no_lot].push(form.no_form);
                    });

                    // Prepare HTML for summary
                    let summaryHtml = `<ul>`;
                    response.summary.forEach(item => {
                        let forms = formMap[item.no_lot] || [];
                        let formList = forms.map(f => `<li style="margin-left:15px;">${f}</li>`).join(
                            '');
                        summaryHtml += `
                    <li>
                        <b>No. Lot:</b> ${item.no_lot} → Generate <b>${item.generated_forms}</b> form(s)
                        <ul>${formList}</ul>
                    </li>`;
                    });
                    summaryHtml += `</ul>`;

                    // Show SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Generate',
                        html: `
                    <p><b>ID Item:</b> ${response.data.id_item}</p>
                    <p><b>No. Invoice:</b> ${response.data.no_inv}</p>
                    <p><b>Total Forms Generated:</b> ${response.total_generated_forms}</p>
                    <hr>
                    <h4>Detail Per Lot & Form:</h4>
                    ${summaryHtml}
                `,
                        width: 700
                    });

                    calculate();
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal generate data. Periksa koneksi atau coba lagi.'
                    });
                }
            });
        }

        function show_list_form(id_item, id_jo, no_invoice, no_lot) {
            // Save the current filter parameters in global vars
            window.current_id_item = id_item;
            window.current_id_jo = id_jo;
            window.current_no_invoice = no_invoice;
            window.current_no_lot = no_lot;

            // Show modal
            const myModal = new bootstrap.Modal(document.getElementById('myModal'));
            myModal.show();

            // Reload DataTable with new params
            if (datatable_modal) {
                datatable_modal.ajax.reload();
            }
        }

        // Instant preview when selecting a photo
        document.getElementById('photoInput').addEventListener('change', function(event) {
            const file = event.target.files[0];

            if (!file || !file.type.startsWith('image/')) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.src = e.target.result;

                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    const MAX_WIDTH = 1024;
                    const MAX_HEIGHT = 1024;
                    let width = img.width;
                    let height = img.height;

                    if (width > height && width > MAX_WIDTH) {
                        height *= MAX_WIDTH / width;
                        width = MAX_WIDTH;
                    } else if (height > MAX_HEIGHT) {
                        width *= MAX_HEIGHT / height;
                        height = MAX_HEIGHT;
                    }

                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    // Convert to blob with quality compression (e.g. 0.7)
                    canvas.toBlob(function(blob) {
                        // Preview compressed image
                        document.getElementById('photoPreview').innerHTML = `
                    <img src="${URL.createObjectURL(blob)}" class="img-fluid rounded border mb-2" style="max-height: 400px;">
                `;

                        // Replace original file in form data
                        const compressedFile = new File([blob], file.name, {
                            type: 'image/jpeg'
                        });

                        // Store it for uploading later
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(compressedFile);
                        document.getElementById('photoInput').files = dataTransfer.files;
                    }, 'image/jpeg', 0.7);
                };
            };

            reader.readAsDataURL(file);
        });

        $('#ModalBlanket').on('show.bs.modal', function() {
            // Clear checkbox and hide it
            $('#passCheckbox').prop('checked', false);
            $('#passCheckboxContainer').hide();

            // Reset rate select and result
            $('#rateSelect').val('');
            $('#rateResult').val('');
        });


        $('#ModalBlanket').on('hidden.bs.modal', function() {
            $('#blanketUploadForm')[0].reset();
            $('#photoPreview').empty(); // clear image preview
        });


        function show_list_blanket(id_item, id_jo, no_invoice, no_lot) {
            const previewContainer = document.getElementById('photoPreview');
            const input = document.getElementById('photoInput');

            // Reset preview and input
            if (previewContainer) previewContainer.innerHTML = '<p class="text-muted">Loading photo...</p>';
            if (input) input.value = '';

            // Set hidden inputs
            $('#input_id_item').val(id_item);
            $('#input_id_jo').val(id_jo);
            $('#input_no_invoice').val(no_invoice);
            $('#input_no_lot').val(no_lot);

            // Open modal immediately
            const modal = new bootstrap.Modal(document.getElementById('ModalBlanket'));
            modal.show();

            // Fetch actual photo filename from backend
            $.ajax({
                url: '{{ route('get_blanket_photo') }}',
                method: 'GET',
                data: {
                    id_item: id_item,
                    id_jo: id_jo,
                    no_invoice: no_invoice,
                    no_lot: no_lot,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    previewContainer.innerHTML = ''; // Clear loading text

                    // Show photo
                    if (res.photo) {
                        const timestamp = new Date().getTime();
                        const imgPath = `/nds_wip/public/storage/gambar_blanket/${res.photo}?t=${timestamp}`;
                        previewContainer.innerHTML = `
            <img src="${imgPath}" alt="Blanket Photo"
                 class="img-fluid rounded border mb-2" style="max-height: 400px;">
        `;
                    } else {
                        previewContainer.innerHTML = `<p class="text-muted">No photo available yet.</p>`;
                    }

                    // Populate rate and result using helper
                    resetFormState(res.rate, res.result);
                },

                error: function(xhr) {
                    previewContainer.innerHTML = `<p class="text-danger">Error loading photo.</p>`;
                    console.error(xhr.responseText);
                }
            });
        }



        $('#blanketUploadForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const modalEl = document.getElementById('ModalBlanket');
            const modal = bootstrap.Modal.getInstance(modalEl);

            $.ajax({
                url: '{{ route('upload_blanket_photo') }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Uploaded!',
                        text: 'Photo uploaded successfully',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    calculate();
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Failed',
                        text: 'There was an error uploading the photo. Please try again.',
                    });
                    console.error(xhr.responseText);
                }
            });
        });


        let datatable_modal = $("#datatable_modal").DataTable({
            ordering: false,
            responsive: false,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: true,
            scrollX: true,
            scrollCollapse: false,

            ajax: {
                url: '{{ route('show_qc_inspect_form_modal') }}',
                data: function(d) {
                    d.id_item = window.current_id_item;
                    d.id_jo = window.current_id_jo;
                    d.no_invoice = window.current_no_invoice;
                    d.no_lot = window.current_no_lot;
                },
            },
            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                    <a class="btn btn-outline-primary position-relative btn-sm" href="{{ route('qc_inspect_proses_form_inspect_det') }}/` +
                            data.id + `" title="Detail" target="_blank">
                        Detail
                    </a>`;
                    }
                },
                {
                    data: 'tgl_form_fix'
                },
                {
                    data: 'no_mesin'
                },
                {
                    data: 'no_form'
                },
                {
                    data: 'no_invoice'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'kpno'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'color'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'itemdesc'
                },
                {
                    data: 'supplier'
                },
                {
                    data: 'group_inspect'
                },
                {
                    data: 'no_lot'
                },
                {
                    data: 'no_roll_buyer'
                },
                {
                    data: 'point_max_point',
                    className: 'text-end'
                },
                {
                    data: 'result',
                    className: 'text-center',
                    render: function(data) {
                        return data ? data.toUpperCase() : '';
                    }
                },
                {
                    data: 'short_roll_result',
                    render: function(data) {
                        return data ? data.toUpperCase() : '';
                    }
                },
                {
                    data: 'founding_issue_result',
                    render: function(data) {
                        return data ? data.toUpperCase() : '';
                    }
                },
                {
                    data: 'final_result',
                    render: function(data) {
                        return data ? data.toUpperCase() : '';
                    }
                },
                {
                    data: 'type_pch'
                },
                {
                    data: 'status_proses_form',
                    render: function(data) {
                        return data ? data.toUpperCase() : '';
                    }
                },
                {
                    data: 'proses'
                }
            ],

            // ✅ Add this block just after columns
            rowCallback: function(row, data) {
                const status = data.status_proses_form?.toLowerCase();

                // Remove any previous Bootstrap table-* color classes
                $(row).removeClass('table-success table-primary table-warning');

                // Apply new light row background color
                switch (status) {
                    case 'done':
                        $(row).addClass('table-success');
                        break;
                    case 'ongoing':
                        $(row).addClass('table-primary');
                        break;
                    case 'new':
                        $(row).addClass('table-warning');
                        break;
                        // draft or others = no color change
                }
            }

        });

        function generate_kedua(id_item, id_jo, no_invoice, no_lot, cek_inspect, group_inspect, tot_form) {
            Swal.fire({
                title: 'Konfirmasi',
                text: 'Kamu akan mengenerate form kedua. Apakah kamu yakin?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, generate!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Perform AJAX POST request here
                    $.ajax({
                        url: '{{ route('generate_form_kedua') }}', // <-- Adjust this route name
                        method: 'POST',
                        data: {
                            id_item: id_item,
                            id_jo: id_jo,
                            no_invoice: no_invoice,
                            no_lot: no_lot,
                            cek_inspect: cek_inspect,
                            group_inspect: group_inspect,
                            tot_form: tot_form,
                            _token: '{{ csrf_token() }}' // Laravel CSRF token
                        },
                        success: function(response) {
                            Swal.fire(
                                'Sukses!',
                                'Form kedua berhasil digenerate.',
                                'success'
                            );

                            // Optional: refresh datatable or part of page
                            $('#datatable').DataTable().ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Gagal!',
                                'Terjadi kesalahan saat generate form.',
                                'error'
                            );
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
        }



        function show_inspect_pertama() {
            // Destroy existing DataTable instance if it exists
            if ($.fn.DataTable.isDataTable('#datatable_pertama')) {
                $('#datatable_pertama').DataTable().clear().destroy();
            }

            // Now initialize it again
            let datatable = $("#datatable_pertama").DataTable({
                ordering: false,
                processing: true,
                serverSide: false,
                paging: false,
                searching: true,
                scrollY: true,
                scrollX: true,
                scrollCollapse: false,
                ajax: {
                    url: '{{ route('show_inspect_pertama') }}',
                    data: function(d) {
                        d.id_item = $('#txtid_item').val();
                        d.id_jo = $('#txtid_jo').val();
                        d.no_inv = $('#txtno_inv').val();
                        d.cek_inspect = $('#txtcek_inspect').val();
                        d.cbo_group_def = $('#cbo_group_def').val();
                    },
                },
                columns: [{
                        data: 'no_lot'
                    },
                    {
                        data: 'jml_roll'
                    },
                    {
                        data: 'jml_roll_cek'
                    },
                    {
                        data: 'tot_form'
                    },
                    {
                        data: 'tot_form_done'
                    },
                    {
                        data: 'cek_inspect'
                    },
                    {
                        data: 'proses'
                    },
                    {
                        data: 'shipment_point'
                    },
                    {
                        data: 'max_shipment'
                    },
                    {
                        data: 'result',
                        className: 'text-center',
                    },
                    {
                        data: 'max_width_short_roll',
                        className: 'text-center',
                        render: function(data) {
                            return parseFloat(data).toFixed(2); // force 2 decimal digits
                        }
                    },
                    {
                        data: 'max_length_short_roll',
                        className: 'text-center',
                        render: function(data) {
                            return parseFloat(data).toFixed(2); // force 2 decimal digits
                        }
                    },
                    {
                        data: 'list_founding_issue'
                    },
                ],

                createdRow: function(row, data, dataIndex) {
                    $('td', row).addClass('text-end'); // align only <td> to right
                    // Apply background color for full status
                    if (data.status_lot === 'Y') {
                        $(row).addClass('table-success');
                    }
                },

                footerCallback: function(row, data, start, end, display) {
                    let api = this.api();

                    // Helper function to parse float safely
                    let parseVal = function(i) {
                        return typeof i === 'string' ?
                            parseFloat(i.replace(/[\$,]/g, '')) || 0 : typeof i === 'number' ? i : 0;
                    };

                    // Total for Jml Roll (column index 1)
                    let totalJmlRoll = api.column(1).data().reduce((a, b) => parseVal(a) + parseVal(b), 0);

                    // Total for Jml Roll (Cek) (column index 2)
                    let totalJmlRollCek = api.column(2).data().reduce((a, b) => parseVal(a) + parseVal(b), 0);

                    // Total for Total Form (column index 3)
                    let totalForm = api.column(3).data().reduce((a, b) => parseVal(a) + parseVal(b), 0);

                    // Total for Total Form (column index 3)
                    let totalFormDone = api.column(4).data().reduce((a, b) => parseVal(a) + parseVal(b), 0);

                    // Update footer
                    $(api.column(1).footer()).html(totalJmlRoll.toLocaleString());
                    $(api.column(2).footer()).html(totalJmlRollCek.toLocaleString());
                    $(api.column(3).footer()).html(totalForm.toLocaleString());
                    $(api.column(4).footer()).html(totalFormDone.toLocaleString());
                }
            });
        }

        function show_inspect_kedua() {
            // Destroy existing DataTable instance if it exists
            if ($.fn.DataTable.isDataTable('#datatable_kedua')) {
                $('#datatable_kedua').DataTable().clear().destroy();
            }

            // Now initialize it again
            let datatable = $("#datatable_kedua").DataTable({
                ordering: false,
                processing: true,
                serverSide: false,
                paging: false,
                searching: true,
                scrollY: true,
                scrollX: true,
                scrollCollapse: false,
                ajax: {
                    url: '{{ route('show_inspect_kedua') }}',
                    data: function(d) {
                        d.id_item = $('#txtid_item').val();
                        d.id_jo = $('#txtid_jo').val();
                        d.no_inv = $('#txtno_inv').val();
                        d.cek_inspect = $('#txtcek_inspect').val();
                        d.cbo_group_def = $('#cbo_group_def').val();
                    },
                },
                columns: [{
                        data: 'no_lot'
                    },
                    {
                        data: 'jml_roll'
                    },
                    {
                        data: 'jml_roll_cek'
                    },
                    {
                        data: 'tot_form'
                    },
                    {
                        data: 'tot_form_done'
                    },
                    {
                        data: 'cek_inspect'
                    },
                    {
                        data: 'proses'
                    },
                    {
                        data: 'shipment_point'
                    },
                    {
                        data: 'max_shipment'
                    },
                    {
                        data: 'result',
                        className: 'text-center',
                    },
                    {
                        data: 'max_width_short_roll',
                        className: 'text-center',
                        render: function(data) {
                            return parseFloat(data).toFixed(2); // force 2 decimal digits
                        }
                    },
                    {
                        data: 'max_length_short_roll',
                        className: 'text-center',
                        render: function(data) {
                            return parseFloat(data).toFixed(2); // force 2 decimal digits
                        }
                    },
                    {
                        data: 'list_founding_issue'
                    },
                ],

                createdRow: function(row, data, dataIndex) {
                    $('td', row).addClass('text-end'); // align only <td> to right
                    // Apply background color for full status
                    if (data.status_lot === 'Y') {
                        $(row).addClass('table-success');
                    }
                },

                footerCallback: function(row, data, start, end, display) {
                    let api = this.api();

                    // Helper function to parse float safely
                    let parseVal = function(i) {
                        return typeof i === 'string' ?
                            parseFloat(i.replace(/[\$,]/g, '')) || 0 : typeof i === 'number' ? i : 0;
                    };

                    // Total for Jml Roll (column index 1)
                    let totalJmlRoll = api.column(1).data().reduce((a, b) => parseVal(a) + parseVal(b), 0);

                    // Total for Jml Roll (Cek) (column index 2)
                    let totalJmlRollCek = api.column(2).data().reduce((a, b) => parseVal(a) + parseVal(b), 0);

                    // Total for Total Form (column index 3)
                    let totalForm = api.column(3).data().reduce((a, b) => parseVal(a) + parseVal(b), 0);

                    // Total for Total Form (column index 3)
                    let totalFormDone = api.column(4).data().reduce((a, b) => parseVal(a) + parseVal(b), 0);

                    // Update footer
                    $(api.column(1).footer()).html(totalJmlRoll.toLocaleString());
                    $(api.column(2).footer()).html(totalJmlRollCek.toLocaleString());
                    $(api.column(3).footer()).html(totalForm.toLocaleString());
                    $(api.column(4).footer()).html(totalFormDone.toLocaleString());
                }
            });
        }

        function pass_with_condition(id_item, id_jo, no_invoice, no_lot) {
            Swal.fire({
                title: 'Konfirmasi',
                text: 'Kamu akan merubah status menjadi pass with condition?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, rubah!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Perform AJAX POST request here
                    $.ajax({
                        url: '{{ route('pass_with_condition') }}', // <-- Adjust this route name
                        method: 'POST',
                        data: {
                            id_item: id_item,
                            id_jo: id_jo,
                            no_invoice: no_invoice,
                            no_lot: no_lot,
                            _token: '{{ csrf_token() }}' // Laravel CSRF token
                        },
                        success: function(response) {
                            Swal.fire(
                                'Sukses!',
                                `${response.total_updated_forms} form berhasil diupdate.`,
                                'success'
                            );

                            // Optional: refresh datatable
                            location.reload();
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Gagal!',
                                'Terjadi kesalahan saat generate form.',
                                'error'
                            );
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
        }

        // Unified function to update the rate result
        function updateResult() {
            const rate = parseFloat($('#rateSelect').val());
            const isOverride = $('#passCheckbox').is(':checked');
            let result = '';

            if (!isNaN(rate)) {
                // Define base logic: PASS if >= 4
                result = rate >= 4 ? 'PASS' : 'REJECT';

                // If it's REJECT and user checked override, update to PASS WITH CONDITION
                if (result === 'REJECT' && isOverride) {
                    result = 'PASS WITH CONDITION';
                }

                // Set the result field
                $('#rateResult').val(result);

                // Show the override checkbox only if it's REJECT
                if (rate < 4) {
                    $('#passCheckboxContainer').show();
                } else {
                    $('#passCheckboxContainer').hide();
                    $('#passCheckbox').prop('checked', false);
                }
            } else {
                // Invalid rate or none selected
                $('#rateResult').val('');
                $('#passCheckboxContainer').hide();
                $('#passCheckbox').prop('checked', false);
            }
        }

        // Bind change events
        $('#rateSelect').on('change', updateResult);
        $('#passCheckbox').on('change', updateResult);

        // Optional: Call this function when modal opens to apply current state
        function resetFormState(rate = null, result = null) {
            if (rate !== null) $('#rateSelect').val(rate);
            if (result !== null) $('#rateResult').val(result);

            // Set checkbox based on result string
            const isPassWithCondition = result === 'PASS WITH CONDITION';
            $('#passCheckbox').prop('checked', isPassWithCondition);

            updateResult();
        }


        const printStickerPackingListUrl = "{{ route('print_sticker_packing_list') }}";

        function print_inspection(id_item, id_jo, no_invoice, no_lot, final_result) {
            const url =
                `${printStickerPackingListUrl}?id_item=${encodeURIComponent(id_item)}&id_jo=${encodeURIComponent(id_jo)}&no_invoice=${encodeURIComponent(no_invoice)}&no_lot=${encodeURIComponent(no_lot)}&final_result=${encodeURIComponent(final_result)}`;
            window.open(url, '_blank');
        }
    </script>
@endsection
