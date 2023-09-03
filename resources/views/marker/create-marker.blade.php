@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="/assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="/assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="/assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endsection

@section('content')
    <div class="card card-sb card-outline">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                List Data
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('store-marker') }}" method="post" id="store-marker" onsubmit="submitForm(this, event)">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Tgl Cutting</small></label>
                            <input type="date" class="form-control" id="tgl-cutting" name="tgl_cutting">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>No. WS</small></label>
                                <select class="form-control select2bs4" id="ws_id" name="ws_id" style="width: 100%;">
                                    <option selected="selected" value="">Pilih WS</option>
                                    @foreach ($orders as $order)
                                        <option value="{{ $order->id }}">
                                            {{ $order->kpno }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Color</small></label>
                                <select class="form-control select2bs4" id="color" name="color" style="width: 100%;">
                                    <option selected="selected" value="">Pilih Color</option>
                                    {{-- select 2 option --}}
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Panel</small></label>
                                <select class="form-control select2bs4" id="panel" name="panel" style="width: 100%;">
                                    <option selected="selected" value="">Pilih Panel</option>
                                    {{-- select 2 option --}}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex flex-column">
                            <input type="hidden" class="form-control" id="ws" name="ws" disabled>
                            <div class="mb-1">
                                <label class="form-label"><small>Buyer</small></label>
                                <input type="text" class="form-control" id="buyer" name="buyer" disabled>
                            </div>
                            <div class="mb-1">
                                <label class="form-label"><small>Style</small></label>
                                <input type="text" class="form-control" id="style" name="style" disabled>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <label class="form-label"><small>Cons WS</small></label>
                                        <input type="text" class="form-control" id="cons-ws" name="cons_ws" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <label class="form-label"><small>Qty Order</small></label>
                                        <input type="text" class="form-control" id="order-qty" name="order_qty" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label"><small>P. Marker</small></label>
                                    <input type="number" class="form-control" id="p-marker" name="p_marker">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label"><small>Unit</small></label>
                                    <select class="form-control input-sm select2bs4" id="p-unit" name="p_unit"
                                        style="width: 100%;">
                                        <option selected="selected" value="yard">YARD</option>
                                        <option value="meter">METER</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label"><small>Comma</small></label>
                                    <input type="number" class="form-control" id="comma" name="comma">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label"><small>Unit</small></label>
                                    <input type="text" class="form-control" id="comma-unit" name="comma_unit"
                                        value="INCH">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label"><small>L. Marker</small></label>
                                    <input type="number" class="form-control" id="l-marker" name="l_marker">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label"><small>Unit</small></label>
                                    <select class="form-control input-sm select2bs4" id="l-unit" name="l_unit"
                                        style="width: 100%;">
                                        <option selected="selected" value="inch">INCH</option>
                                        <option value="cm">CM</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label"><small>Cons Marker</small></label>
                                    <input type="number" class="form-control" id="cons-marker" name="cons_marker">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label"><small>Qty Gelar Marker</small></label>
                                    <input type="number" class="form-control" id="gelar-marker-qty"
                                        name="gelar_marker_qty">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-1">
                            <label class="form-label"><small>PO</small></label>
                            <input type="text" class="form-control" id="po" name="po">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-1">
                            <label class="form-label"><small>No. Urut Marker</small></label>
                            <input type="text" class="form-control" id="no-urut-marker" name="no_urut_marker"
                                disabled>
                        </div>
                    </div>
                </div>
                <button class="btn btn-sb mt-3 float-end" type="submit">SIMPAN</button>
            </form>
        </div>
    </div>
    <div class="card card-sb card-outline">
        <div class="card-body">
            <h5 class="fw-bold mb-3">
                Data Ratio :
            </h5>
            <table id="datatable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>WS</th>
                        <th>Color</th>
                        <th>QTY Order</th>
                        <th>Size</th>
                        <th>Ratio</th>
                        <th>Cut Qty</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables  & Plugins -->
    <script src="/assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="/assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="/assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <script src="/assets/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
    <script src="/assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
    <script src="/assets/plugins/jszip/jszip.min.js"></script>
    <script src="/assets/plugins/pdfmake/pdfmake.min.js"></script>
    <script src="/assets/plugins/pdfmake/vfs_fonts.js"></script>
    <script src="/assets/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
    <script src="/assets/plugins/datatables-buttons/js/buttons.print.min.js"></script>
    <script src="/assets/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
    <!-- Select2 -->
    <script src="/assets/plugins/select2/js/select2.full.min.js"></script>
    <!-- Page specific script -->
    <script>
        function showAlert(message) {
            alert(message);
        }

        $("#datatable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{!! route('get-marker-sizes') !!}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.act_costing_id = $('#ws_id').val(),
                        d.color = $('#color').val()
                },
            },
            columns: [{
                    data: 'no_ws'
                },
                {
                    data: 'color'
                },
                {
                    data: 'order_qty'
                },
                {
                    data: 'size'
                },
                {
                    data: 'id'
                },
                {
                    data: 'id'
                }
            ],
            columnDefs: [{
                    targets: [4],
                    render: (data, type, row) => '<input type="number" id="ratio-' + data + '">'
                },
                {
                    targets: [5],
                    render: (data, type, row) => '<input type="number" id="cut-qty-' + data + '" disabled>'
                }
            ]
        });

        $(function() {
            //Initialize Select2 Elements
            $('.select2').select2()

            //Initialize Select2 Elements
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            })

            if (document.getElementById('store-marker')) {
                document.getElementById('store-marker').reset();
            }

            $('#ws_id').val(null).trigger('change');

            function updateOrderInfo() {
                return $.ajax({
                    url: '{!! route('get-marker-order') !!}',
                    type: 'get',
                    dataType: 'json',
                    data: {
                        act_costing_id: $('#ws_id').val(),
                    },
                    success: function(res) {
                        console.log(res);
                        if (res) {
                            $('#buyer').val(res.buyer);
                            $('#style').val(res.styleno);
                        }
                    },
                    error: function(jqXHR) {
                        let res = jqXHR.responseJSON;
                        console.error(res.message);
                        iziToast.error({
                            title: 'Error',
                            message: res.message,
                            position: 'topCenter'
                        });
                    }
                });
            }

            function updateColorList() {
                return $.ajax({
                    url: '{!! route('get-marker-colors') !!}',
                    type: 'get',
                    dataType: 'json',
                    data: {
                        act_costing_id: $('#ws_id').val(),
                    },
                    success: async function(res) {
                        // Clear options
                        $("#color").html("");

                        let colorElm;

                        if (document.getElementById('color')) {
                            colorElm = document.getElementById('color');
                        }

                        res.forEach((element, index) => {
                            if ($('#color').find("option[value='" + element.color + "']")
                                .length) {
                                $('#color').val(element.color);
                            } else {
                                let option = ""

                                if (index == 0) {
                                    option += "<option value=''>Pilih Color</option>"
                                }

                                option += "<option value='" + element.color + "'>" + element
                                    .color + "</option>";

                                colorElm.innerHTML += option;
                            }
                        });
                    },
                    error: function(jqXHR) {
                        let res = jqXHR.responseJSON;
                        console.error(res.message);
                        iziToast.error({
                            title: 'Error',
                            message: res.message,
                            position: 'topCenter'
                        });
                    }
                });
            }

            $('#ws_id').on('change', async function(e) {
                await $("#color").val(null).trigger('change');
                await $("#panel").val(null).trigger('change');
                await updateColorList();
                await updateOrderInfo();
                await getMarkerCount();
            });

            function updatePanelList() {
                return $.ajax({
                    url: '{!! route('get-marker-panels') !!}',
                    type: 'get',
                    dataType: 'json',
                    data: {
                        act_costing_id: $('#ws_id').val(),
                        color: $('#color').val(),
                    },
                    success: function(res) {
                        // Clear options
                        $("#panel").html("");

                        let panelElm;

                        if (document.getElementById('panel')) {
                            panelElm = document.getElementById('panel');
                        }

                        res.forEach((element, index) => {
                            if ($('#panel').find("option[value='" + element.panel + "']")
                                .length) {
                                $('#panel').val(element.panel);
                            } else {
                                let option = ""

                                if (index == 0) {
                                    option += "<option value=''>Pilih Panel</option>"
                                }

                                option += "<option value='" + element.panel + "'>" + element
                                    .panel + "</option>";

                                panelElm.innerHTML += option;
                            }
                        });
                    },
                    error: function(jqXHR) {
                        let res = jqXHR.responseJSON;
                        console.error(res.message);
                        iziToast.error({
                            title: 'Error',
                            message: res.message,
                            position: 'topCenter'
                        });
                    }
                });
            }

            function updateSizeList() {
                return $('#datatable').DataTable().ajax.reload();
            }

            function getMarkerCount() {
                return $.ajax({
                    url: '{!! route('get-marker-count') !!}',
                    type: 'get',
                    dataType: 'json',
                    data: {
                        act_costing_id: $('#ws_id').val(),
                        color: $('#color').val(),
                        panel: $('#panel').val()
                    },
                    success: async function(res) {
                        if (res) {
                            $('#no-urut-marker').val(res);
                        }
                    },
                    error: function(jqXHR) {
                        let res = jqXHR.responseJSON;
                        console.error(res.message);
                        iziToast.error({
                            title: 'Error',
                            message: res.message,
                            position: 'topCenter'
                        });
                    }
                });
            }

            $('#color').on('change', async function(e) {
                await updatePanelList();
                await updateSizeList();
                await getMarkerCount();
            });

            $('#panel').on('change', async function(e) {
                await getMarkerCount();
            });
        })
    </script>
@endsection
