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
    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold text-sb"><i class="fa fa-edit fa-sm"></i> Edit Data Marker - {{ $marker->kode }}</h5>
        <a href="{{ route('marker') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali ke Marker</a>
    </div>
    @php
        $totalForm = $marker->formCutInputs->count();
    @endphp
    <form action="{{ route('update-marker')."/".$marker->id }}" method="post" id="store-marker" onsubmit="submitMarkerForm(this, event)">
        @method('PUT')
        <div class="card card-sb">
            <input type="hidden" id="id" name="id" value="{{ $marker->id }}">
            <div class="card-header">
                <h5 class="card-title fw-bold">
                    List Data
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Tanggal</small></label>
                            <input type="date" class="form-control" id="tgl_cutting" name="tgl_cutting" value="{{ $marker->tgl_cutting }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>No. WS</small></label>
                                <input type="text" class="form-control" id="no_ws" name="no_ws" value="{{ $marker->act_costing_ws }}" readonly>
                                <input type="hidden" class="form-control" id="ws_id" name="ws_id" value="{{ $marker->act_costing_id }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Color</small></label>
                                <input type="hidden" class="form-control" id="color" name="color" value="{{ $marker->color }}">
                                <select class="form-control select2bs4" id="color_select2" name="color_select2" style="width: 100%;">
                                    <option selected="selected" value="">Pilih Color</option>
                                    {{-- select 2 option --}}
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Panel</small></label>
                                <input type="hidden" class="form-control" id="panel_default" name="panel_default" value="{{ $marker->panel }}">
                                <select class="form-control select2bs4" id="panel" name="panel" style="width: 100%;">
                                    <option selected="selected" value="">Pilih Panel</option>
                                    {{-- select 2 option --}}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="d-flex flex-column">
                            <input type="hidden" class="form-control" id="ws" name="ws" value="{{ $marker->act_costing_ws }}" readonly>
                            <div class="mb-1">
                                <label class="form-label"><small>Buyer</small></label>
                                <input type="text" class="form-control" id="buyer" name="buyer" value="{{ $marker->buyer }}" readonly>
                            </div>
                            <div class="mb-1">
                                <label class="form-label"><small>Style</small></label>
                                <input type="text" class="form-control" id="style" name="style" value="{{ $marker->style }}" readonly>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <label class="form-label"><small>Cons WS</small></label>
                                        <input type="text" class="form-control" id="cons_ws" name="cons_ws" value="{{ $marker->cons_ws }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <label class="form-label"><small>Qty Order</small></label>
                                        <input type="text" class="form-control" id="order_qty" name="order_qty" value="" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <label class="form-label"><small>P. Marker</small></label>
                                <div class="input-group mb-1">
                                    <input type="number" class="form-control" id="p_marker" name="p_marker" step=".001" value="{{ $marker->panjang_marker }}" {{ $totalForm > 0 ? "readonly" : "" }}>
                                    <span class="input-group-text">METER</span>
                                </div>
                                <input type="hidden" class="form-control" id="p_unit" name="p_unit" value="METER" readonly>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label"><small>Comma</small></label>
                                <div class="input-group mb-1">
                                    <input type="number" class="form-control" id="comma_marker" name="comma_marker" step=".001" value="{{ $marker->comma_marker }}" {{ $totalForm > 0 ? "readonly" : "" }}>
                                    <span class="input-group-text">CM</span>
                                </div>
                                <input type="hidden" class="form-control" id="comma_unit" name="comma_unit" value="CM" readonly>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label"><small>L. Marker</small></label>
                                <div class="input-group mb-1">
                                    <input type="number" class="form-control" id="l_marker" name="l_marker" step=".001" value="{{ $marker->lebar_marker }}" {{ $totalForm > 0 ? "readonly" : "" }}>
                                    <span class="input-group-text">CM</span>
                                </div>
                                <input type="hidden" class="form-control" id="l_unit" name="l_unit" value="CM" readonly>
                            </div>
                            <div class="col-6 col-md-6">
                                <div class="mb-1">
                                    <label class="form-label"><small>Cons Marker</small></label>
                                    <input type="number" class="form-control" id="cons_marker" name="cons_marker" step=".001" value="{{ $marker->cons_marker }}" {{ $totalForm > 0 ? "readonly" : "" }}>
                                </div>
                            </div>
                            <div class="col-4 col-md-4">
                                <div class="mb-1">
                                    <label class="form-label"><small>Cons Piping</small></label>
                                    <input type="number" class="form-control" id="cons_piping" name="cons_piping" step=".001" value="{{ $marker->cons_piping }}" {{-- $totalForm > 0 ? "readonly" : "" --}}>
                                </div>
                            </div>
                            <div class="col-4 col-md-4">
                                <div class="mb-1">
                                    <label class="form-label"><small>Gramasi</small></label>
                                    <input type="number" class="form-control" id="gramasi" name="gramasi" step=".001" value="{{ $marker->gramasi }}" {{-- $totalForm > 0 ? "readonly" : "" --}}>
                                </div>
                            </div>
                            <div class="col-4 col-md-4">
                                <div class="mb-1">
                                    <label class="form-label"><small>Qty Gelar Marker</small></label>
                                    <input type="number" class="form-control" id="gelar_marker_qty" name="gelar_marker_qty" onchange="calculateAllRatio(this)" onkeyup="calculateAllRatio(this)" value="{{ $marker->gelar_qty }}" {{-- {{ $totalForm > 0 ? "readonly" : "" }} --}}>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>No. Urut Marker</small></label>
                            <input type="text" class="form-control" id="no_urut_marker" name="no_urut_marker" value="{{ $marker->urutan_marker }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>PO</small></label>
                            <input type="text" class="form-control" id="po" name="po" value="{{ $marker->po_marker }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Tipe Marker</small></label>
                            <select class="form-select select2bs4" id="tipe_marker" name="tipe_marker" style="width: 100%;" {{ $totalForm > 0 ? "disabled" : "" }}>
                                <option value="regular marker" {{ $marker->tipe_marker == "regular marker" ? "selected" : "" }}>Regular Marker</option>
                                <option value="special marker" {{ $marker->tipe_marker == "special marker" ? "selected" : "" }}>Special Marker</option>
                                <option value="pilot marker" {{ $marker->tipe_marker == "pilot marker" ? "selected" : "" }}>Pilot Marker</option>
                                <option value="bulk marker" {{ $marker->tipe_marker == "bulk marker" ? "selected" : "" }}>Bulk Marker</option>
                            </select>
                        </div>
                        @if ($totalForm > 0)
                            <input type="hidden" id="tipe_marker" name="tipe_marker" value="{{ $marker->tipe_marker }}">
                        @endif
                    </div>
                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Catatan</small></label>
                            <textarea class="form-control" id="notes" name="notes"></textarea>
                        </div>
                    </div>
                    <input type="hidden" class="form-control" id="jumlah_so_det" name="jumlah_so_det" readonly>
                </div>
            </div>
        </div>
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold">
                    Data Ratio
                </h5>
            </div>
            <div class="card-body table-responsive">
                <table id="orderQtyDatatable" class="table table-bordered table-striped table-sm w-100">
                    <thead>
                        <tr>
                            <th>WS</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Size Input</th>
                            <th>QTY Order</th>
                            <th>Sisa</th>
                            <th>Persentase</th>
                            <th>So Det Id</th>
                            <th>Ratio</th>
                            <th>Cut Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th id="total_ratio"></th>
                            <th id="total_cut_qty"></th>
                        </tr>
                    </tfoot>
                </table>
                <button class="btn btn-sb float-end mt-3">Simpan</button>
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
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Page specific script -->
    <script>
        // Global Variable
        var sumCutQty = null;
        var totalRatio = null;

        // Initial Window On Load Event
        $(document).ready(async function () {
            document.getElementById("loading").classList.remove("d-none");

            // Call Get Total Cut Qty ( set sum cut qty variable )
            await getTotalCutQty($("#ws_id").val(), $("#color").val(), $("#panel").val());

            await getNumber();

            await updateColorList();
            await $('#color_select2').val($('#color').val()).trigger('change');

            await updatePanelList();
            await $('#panel').val($('#panel_default').val()).trigger('change');

            await updateSizeList();

            document.getElementById("loading").classList.add("d-none");
        });

        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2()

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        })

        // Step Two (Color) on change event
        $('#color_select2').on('change', function(e) {
            if (this.value) {
                $('#color').val(this.value);
                updatePanelList();
                updateSizeList();
            }
        });

        // Step Three (Panel) on change event
        $('#panel').on('change', function(e) {
            if (this.value) {
                updateSizeList();
                getNumber();
            }
        });

        // Update Color Select Option Based on Order WS
        function updateColorList() {
            document.getElementById('color_select2').value = null;

            return $.ajax({
                url: '{{ route("get-marker-colors") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                },
                success: function (res) {
                    if (res) {
                        // Update this step
                        document.getElementById('color_select2').innerHTML = res;

                        // Reset next step
                        document.getElementById('panel').innerHTML = null;
                        document.getElementById('panel').value = null;

                        // Open this step
                        $("#color_select2").prop("disabled", false);

                        // Close next step
                        $("#panel").prop("disabled", true);
                    }
                },
            });
        }

        // Update Panel Select Option Based on Order WS and Color WS
        function updatePanelList() {
            document.getElementById('panel').value = null;
            return $.ajax({
                url: '{{ route("get-marker-panels") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color_select2').val(),
                },
                success: function (res) {
                    if (res) {
                        // Update this step
                        document.getElementById('panel').innerHTML = res;

                        // Open this step
                        $("#panel").prop("disabled", false);

                        // Close step before
                        $("#color_select2").prop("disabled", true);
                    }
                },
            });
        }

        // Get & Set Total Cut Qty Based on Order WS and Order Color ( to know remaining cut qty )
        async function getTotalCutQty(wsId, color, panel) {
            sumCutQty = await $.ajax({
                url: '{{ route("create-marker") }}',
                type: 'get',
                data: {
                    act_costing_id: wsId,
                    color: color,
                    panel: panel,
                },
                dataType: 'json',
            });
        }

        // Calculate Remaining Cut Qty
        function remainingCutQty(orderQty, soDetId) {
            // Get Total Cut Qty Based on Order WS, Order Color and Order Panel ( to know remaining cut qty )
            let sumCutQtyData = sumCutQty.find(o => o.so_det_id == soDetId && o.panel == $("#panel").val());

            // Calculate Remaining Cut Qty
            let remain = orderQty - (sumCutQtyData ? sumCutQtyData.total_cut_qty : 0);

            return remain;
        }

        // Order Qty Datatable (Size|Ratio|Cut Qty)
        let orderQtyDatatable = $("#orderQtyDatatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            ajax: {
                url: '{{ route("get-general-sizes") }}',
                data: function (d) {
                    d.marker_id = '{{ $marker->id }}';
                    d.act_costing_id = $('#ws_id').val();
                    d.color = $('#color_select2').val();
                },
            },
            columns: [
                {
                    data: 'no_ws'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size_dest'
                },
                {
                    data: 'size' // size input
                },
                {
                    data: 'order_qty'
                },
                {
                    data: null // remaining cut qty
                },
                {
                    data: null // percentage
                },
                {
                    data: 'so_det_id' // detail so input
                },
                {
                    data: 'so_det_id' // ratio input
                },
                {
                    data: 'so_det_id' // cut qty input
                }
            ],
            columnDefs: [
                {
                    // Size Input
                    targets: [3],
                    className: "d-none",
                    render: (data, type, row, meta) => {
                        // Hidden Size Input
                        return '<input type="hidden" class="form-control" id="size-' + meta.row + '" name="size['+meta.row+']" value="' + data + '" readonly />'
                    }
                },
                {
                    // Remaining Cut Qty
                    targets: [5],
                    render: (data, type, row, meta) => {
                        // Calculate Remaining Cut Qty
                        let remain = remainingCutQty(row.order_qty, row.so_det_id);

                        return remain;
                    }
                },
                {
                    // Percentage
                    targets: [6],
                    render: (data, type, row, meta) => {
                        // Calculate Remaining Cut Qty
                        let remain = remainingCutQty(row.order_qty, row.so_det_id);

                        // Calculate Percentage
                        let percentage = Number(row.order_qty) > 0 ? ((Number(row.order_qty)-Number(remain))/Number(row.order_qty)*100) : 0;

                        return `
                            <div class="position-relative">
                                <div class="progress border border-sb" style="height: 27px">
                                    <p class="position-absolute" style="top: 55%;left: 50%;transform: translate(-50%, -50%);" id="current_ply_progress_txt">`+ percentage.round(2) +`%</p>
                                    <div class="progress-bar" style="background-color: #75baeb; width: `+ percentage.round(2) +`%" role="progressbar" id="current_ply_progress"></div>
                                </div>
                            </div>
                        `;
                    }
                },
                {
                    // SO Detail Input
                    targets: [7],
                    className: "d-none",
                    render: (data, type, row, meta) => {
                        // Hidden Detail SO Input
                        return '<input type="hidden" id="so-det-id-' + meta.row + '" name="so_det_id['+meta.row+']" value="' + data + '" readonly />'
                    }
                },
                {
                    // Ratio Input
                    targets: [8],
                    render: (data, type, row, meta) => {
                        // Calculate Remaining Cut Qty
                        let remain = remainingCutQty(row.order_qty, row.so_det_id);

                        // Conditional Based on Remaining Cut Qty
                        // let readonly = remain < 1 ? "readonly" : "";
                        let readonly = remain < 1 ? "" : "";

                        // Hidden Ratio Input
                        return '<input type="number" id="ratio-' + meta.row + '" name="ratio[' + meta.row + ']" onchange="calculateRatio(' + meta.row + ');" onkeyup="calculateRatio(' + meta.row + ');" value="' + row.ratio + '" '+readonly+' />';
                    }
                },
                {
                    // Cut Qty Input
                    targets: [9],
                    render: (data, type, row, meta) => {
                        // Hidden Cut Qty Input
                        return '<input type="number" id="cut-qty-' + meta.row + '" name="cut_qty['+meta.row+']" value="' + row.cut_qty + '" readonly />'
                    }
                }
            ],
            footerCallback: function(row, data, start, end, display) {
                // This datatable api
                let api = this.api();

                // Remove the formatting to get integer data for summation
                let intVal = function(i) {
                    return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
                };

                // Total order qty
                let orderQtyTotal = api
                    .column(4, {
                        page: 'current'
                    })
                    .data()
                    .reduce(function(a, b) {
                        let result = intVal(a) + intVal(b);
                        return result;
                    }, 0);

                // Total remain qty
                let remainQtyTotal = orderQtyDatatable
                    .cells( null, 5 )
                    .render( 'display' )
                    .reduce(function(a, b) {
                        let result = intVal(a) + intVal(b);
                        return result;
                    }, 0);

                // Update footer
                $(api.column(1).footer()).html("Total");
                $(api.column(4).footer()).html(Number(orderQtyTotal).toLocaleString('id-ID'));
                $(api.column(5).footer()).html(Number(remainQtyTotal).toLocaleString('id-ID'));
                $(api.column(8).footer()).html(0); // Total ratio
                $(api.column(9).footer()).html(0); // Total cut qty
            },
        });

        // Update Order Qty Datatable
        async function updateSizeList() {
            await orderQtyDatatable.ajax.reload(() => {
                // Get Sizes Count ( for looping over sizes input )
                document.getElementById('jumlah_so_det').value = orderQtyDatatable.data().count();
            });
        }

        // Get & Set Order WS Cons and Order Qty Based on Order WS, Order Color and Order Panel
        function getNumber() {
            document.getElementById('order_qty').value = null;
            return $.ajax({
                url: ' {{ route("get-marker-number") }}',
                type: 'get',
                dataType: 'json',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color_select2').val(),
                    panel: $('#panel').val()
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('order_qty').value = res.order_qty;
                    }
                }
            });
        }

        // Calculate Cut Qty Based on Ratio and Spread Qty ( Ratio * Spread Qty )
        function calculateRatio(id) {
            let ratio = document.getElementById('ratio-'+id).value;
            let gelarQty = document.getElementById('gelar_marker_qty').value;

            // Cut Qty Formula
            document.getElementById('cut-qty-'+id).value = ratio * gelarQty;

            // Call Calculate Total Ratio Function ( for order qty datatable summary )
            calculateTotalRatio();
        }

        // Calculate Total Ratio
        function calculateTotalRatio() {
            // Get Sizes Count
            let totalSize = document.getElementById('jumlah_so_det').value;

            let totalRatio = 0;
            let totalCutQty = 0;

            // Looping Over Sizes Input
            for (let i = 0; i < totalSize; i++) {
                // Sum Ratio and Cut Qty
                totalRatio += Number(document.getElementById('ratio-'+i).value);
                totalCutQty += Number(document.getElementById('cut-qty-'+i).value);
            }

            // Set Ratio and Cut Qty ( order qty datatable summary )
            document.querySelector("table#orderQtyDatatable tfoot tr th:nth-child(7)").innerText = totalRatio;
            document.querySelector("table#orderQtyDatatable tfoot tr th:nth-child(8)").innerText = totalCutQty;
        }

        // Calculate All Cut Qty at Once Based on Spread Qty
        function calculateAllRatio(element) {
            // Get Sizes Count
            let totalSize = document.getElementById('jumlah_so_det').value;

            let gelarQty = element.value;

            // Looping Over Sizes Input
            for (let i = 0; i < totalSize; i++) {
                // Calculate Cut Qty
                let ratio = document.getElementById('ratio-'+i).value;

                // Cut Qty Formula
                document.getElementById('cut-qty-'+i).value = ratio * gelarQty;
            }

            // Call Calculate Total Ratio Function ( for order qty datatable summary )
            calculateTotalRatio();
        }

        // Prevent Form Submit When Pressing Enter
        document.getElementById("store-marker").onkeypress = function(e) {
            var key = e.charCode || e.keyCode || 0;
            if (key == 13) {
                e.preventDefault();
            }
        }

        document.getElementById("tipe_marker").onchange = function(e) {
            if (this.value == "pilot marker") {
                document.getElementById("notes").value = "PILOT MARKER";
            } else {
                document.getElementById("notes").value = "";
            }
        }

        // Submit Marker Form
        function submitMarkerForm(e, evt) {
            document.getElementById("loading").classList.remove("d-none");
            $("input[type=submit][clicked=true]").attr('disabled', true);

            evt.preventDefault();

            clearModified();

            $.ajax({
                url: e.getAttribute('action'),
                type: e.getAttribute('method'),
                data: new FormData(e),
                processData: false,
                contentType: false,
                success: async function(res) {
                    document.getElementById("loading").classList.add("d-none");
                    $("input[type=submit][clicked=true]").removeAttr('disabled');

                    // Success Response
                    if (res.status == 200) {
                        // When Actually Success :

                        // Reset This Form
                        e.reset();

                        // Success Alert
                        Swal.fire({
                            icon: 'success',
                            title: 'Data Marker berhasil disimpan',
                            html: res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        }).then(() => {
                            if (res.redirect != '') {
                                if (res.redirect != 'reload') {
                                    location.href = res.redirect;
                                } else {
                                    location.reload();
                                }
                            }
                        });
                    } else {
                        // When Actually Error :

                        // Error Alert
                        iziToast.error({
                            title: 'Error',
                            message: res.message,
                            position: 'topCenter'
                        });
                    }

                    // Reload Order Qty Datatable
                    orderQtyDatatable.ajax.reload();

                    // If There Are Some Additional Error
                    if (Object.keys(res.additional).length > 0 ) {
                        for (let key in res.additional) {
                            if (document.getElementById(key)) {
                                document.getElementById(key).classList.add('is-invalid');

                                if (res.additional[key].hasOwnProperty('message')) {
                                    document.getElementById(key+'_error').classList.remove('d-none');
                                    document.getElementById(key+'_error').innerHTML = res.additional[key]['message'];
                                }

                                if (res.additional[key].hasOwnProperty('value')) {
                                    document.getElementById(key).value = res.additional[key]['value'];
                                }

                                modified.push(
                                    [key, '.classList', '.remove(', "'is-invalid')"],
                                    [key+'_error', '.classList', '.add(', "'d-none')"],
                                    [key+'_error', '.innerHTML = ', "''"],
                                )
                            }
                        }
                    }
                }, error: function (jqXHR) {
                    // Error Response

                    let res = jqXHR.responseJSON;
                    let message = '';
                    let i = 0;

                    for (let key in res.errors) {
                        message = res.errors[key];
                        document.getElementById(key).classList.add('is-invalid');
                        modified.push(
                            [key, '.classList', '.remove(', "'is-invalid')"],
                        )

                        if (i == 0) {
                            document.getElementById(key).focus();
                            i++;
                        }
                    };
                }
            });
        }
    </script>
@endsection
