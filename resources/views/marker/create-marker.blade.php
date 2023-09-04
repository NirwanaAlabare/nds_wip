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
<form action="{{ route('store-marker') }}" method="post" id="store-marker" onsubmit="submitMarkerForm(this, event)">
    <div class="card card-sb card-outline">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                List Data
            </h5>
        </div>
        <div class="card-body">
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
                            <select class="form-control select2bs4" id="panel" name="panel" style="width: 100%;" >
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
                                    value="INCH" disabled>
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
                                    name="gelar_marker_qty" onchange="calculateAllRatio(this)" onkeyup="calculateAllRatio(this)">
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
                        <th>So Det Id</th>
                        <th>Ratio</th>
                        <th>Cut Qty</th>
                    </tr>
                </thead>
            </table>
            <button class="btn btn-sb float-end mt-3">Simpan</button>
        </div>
    </div>
</form>
@endsection

@section('custom-script')
    <!-- DataTables  & Plugins -->
    <script src="/assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="/assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="/assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <!-- Select2 -->
    <script src="/assets/plugins/select2/js/select2.full.min.js"></script>
    <!-- Page specific script -->
    <script>
        //Initialize Select2 Elements
        $('.select2').select2()

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        //Reset Form
        if (document.getElementById('store-marker')) {
            document.getElementById('store-marker').reset();
        }

        $('#ws_id').on('change', async function(e) {
            await updateColorList();
            await updateOrderInfo();
        });

        $('#color').on('change', async function(e) {
            await updatePanelList();
            await updateSizeList();
        });

        $('#panel').on('change', async function(e) {
            await getMarkerCount();
            await getNumber();
        });

        function updateOrderInfo() {
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '/marker/get-order',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color').val(),
                },
                dataType: 'json',
                success: function (res) {
                    document.getElementById('buyer').value = res.buyer;
                    document.getElementById('style').value = res.styleno;
                },
            });
        }

        function updateColorList() {
            document.getElementById('color').value = null;
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '/marker/get-colors',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                },
                success: function (res) {
                    document.getElementById('color').innerHTML = res;
                    document.getElementById('panel').innerHTML = null;
                    document.getElementById('panel').value = null;
                    document.getElementById('no-urut-marker').value = null;
                    document.getElementById('cons-ws').value = null;
                    document.getElementById('order-qty').value = null;
                },
            });
        }

        function updatePanelList() {
            document.getElementById('panel').value = null;
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '/marker/get-panels',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color').val(),
                },
                success: function (res) {
                    document.getElementById('panel').innerHTML = res;
                    document.getElementById('no-urut-marker').value = null;
                    document.getElementById('cons-ws').value = null;
                    document.getElementById('order-qty').value = null;
                },
            });
        }

        $("#datatable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '/marker/get-sizes',
                dataType: 'json',
                dataSrc: 'data',
                data: function (d) {
                    d.act_costing_id = $('#ws_id').val();
                    d.color = $('#color').val();
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
                },
                {
                    data: 'id'
                }
            ],
            columnDefs: [
                {
                    targets: [4],
                    visible: false,
                    render: (data, type, row, meta) => '<input type="text" id="so-det-id-' + meta.row + '" name="so_det_id['+meta.row+']" value="' + data + '">'
                },
                {
                    targets: [5],
                    render: (data, type, row, meta) => '<input type="number" id="ratio-' + meta.row + '" name="ratio['+meta.row+']" onchange="calculateRatio(' + meta.row + ')" onkeyup="calculateRatio('+meta.row+')">'
                },
                {
                    targets: [6],
                    render: (data, type, row, meta) => '<input type="number" id="cut-qty-' + meta.row + '" name="cut_qty['+meta.row+']" disabled>'
                }
            ]
        });

        function updateSizeList() {
            return $('#datatable').DataTable().ajax.reload();
        }

        function getMarkerCount() {
            document.getElementById('no-urut-marker').value = "";
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '/marker/get-count',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color').val(),
                    panel: $('#panel').val()
                },
                success: function (res) {
                    document.getElementById('no-urut-marker').value = res;
                }
            });
        }

        function getNumber() {
            document.getElementById('cons-ws').value = null;
            document.getElementById('order-qty').value = null;
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '/marker/get-number',
                type: 'get',
                dataType: 'json',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color').val(),
                    panel: $('#panel').val()
                },
                success: function (res) {
                    document.getElementById('cons-ws').value = res.cons_ws;
                    document.getElementById('order-qty').value = res.order_qty;
                }
            });
        }

        function calculateRatio(id) {
            let ratio = document.getElementById('ratio-'+id).value;
            let gelarQty = document.getElementById('gelar-marker-qty').value;
            document.getElementById('cut-qty-'+id).value = ratio * gelarQty;
        }

        function calculateAllRatio(element) {
            let gelarQty = element.value;
            console.log(document.getElementsByName('ratio[]'));
        }

        function submitMarkerForm(e, event) {
            evt.preventDefault();

            $.ajax({
                url: e.getAttribute('action'),
                type: e.getAttribute('method'),
                data: new FormData(e),
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.status == 200) {
                        console.log(res.message);

                        if (res.redirect != '') {
                            location.href = res.redirect;
                        }

                        iziToast.success({
                            title: 'Success',
                            message: res.message,
                            position: 'topCenter'
                        });

                        e.reset();

                        if (document.getElementsByClassName('select2')) {
                            $(".select2").val(null).trigger('change');
                        }

                        if (document.getElementsByClassName('select2bs4')) {
                            $(".select2bs4").val(null).trigger('change');
                        }
                    } else {
                        console.log(res.message);

                        for(let i = 0;i < res.errors; i++) {
                            document.getElementById(res.errors[i]).classList.add('is-invalid');
                            modified.push([res.errors[i], 'classList', 'remove(', "'is-invalid')"])
                        }

                        iziToast.error({
                            title: 'Error',
                            message: res.message,
                            position: 'topCenter'
                        });
                    }

                    if (res.table != '') {
                        $('#'+res.table).DataTable().ajax.reload();
                    }

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
                    let res = jqXHR.responseJSON;
                    let message = '';

                    for (let key in res.errors) {
                        message = res.errors[key];
                        document.getElementById(key).classList.add('is-invalid');
                        document.getElementById(key+'_error').classList.remove('d-none');
                        document.getElementById(key+'_error').innerHTML = res.errors[key];

                        modified.push(
                            [key, '.classList', '.remove(', "'is-invalid')"],
                            [key+'_error', '.classList', '.add(', "'d-none')"],
                            [key+'_error', '.innerHTML = ', "''"],
                        )
                    };

                    iziToast.error({
                        title: 'Error',
                        message: 'Terjadi kesalahan.',
                        position: 'topCenter'
                    });
                }
            });
        }
    </script>
@endsection
