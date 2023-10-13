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
<form action="{{ route('store-marker') }}" method="post" id="store-marker" onsubmit="submitMarkerForm(this, event)">
    @csrf
    <div class="card card-sb card-outline">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                List Data
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-6 col-md-3">
                    <div class="mb-1">
                        <label class="form-label"><small>Tgl Cutting</small></label>
                        <input type="date" class="form-control" id="tgl_cutting" name="tgl_cutting" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
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
                <div class="col-6 col-md-3">
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
                <div class="col-6 col-md-3">
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
                <div class="col-12 col-md-6">
                    <div class="d-flex flex-column">
                        <input type="hidden" class="form-control" id="ws" name="ws" readonly>
                        <div class="mb-1">
                            <label class="form-label"><small>Buyer</small></label>
                            <input type="text" class="form-control" id="buyer" name="buyer" readonly>
                        </div>
                        <div class="mb-1">
                            <label class="form-label"><small>Style</small></label>
                            <input type="text" class="form-control" id="style" name="style" readonly>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label"><small>Cons WS</small></label>
                                    <input type="text" class="form-control" id="cons_ws" name="cons_ws" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label"><small>Qty Order</small></label>
                                    <input type="text" class="form-control" id="order_qty" name="order_qty" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="row">
                        <div class="col-6 col-md-3">
                            <div class="mb-1">
                                <label class="form-label"><small>P. Marker</small></label>
                                <input type="number" class="form-control" id="p_marker" name="p_marker" step=".001">
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-1">
                                <label class="form-label"><small>Unit</small></label>
                                <select class="form-control input-sm select2bs4" id="p_unit" name="p_unit" style="width: 100%;">
                                    <option selected="selected" value="yard">YARD</option>
                                    <option value="meter">METER</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-1">
                                <label class="form-label"><small>Comma</small></label>
                                <input type="number" class="form-control" id="comma_marker" name="comma_marker" step=".001">
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-1">
                                <label class="form-label"><small>Unit</small></label>
                                <input type="text" class="form-control" id="comma_unit" name="comma_unit"
                                    value="INCH" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-1">
                                <label class="form-label"><small>L. Marker</small></label>
                                <input type="number" class="form-control" id="l_marker" name="l_marker" step=".001">
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-1">
                                <label class="form-label"><small>Unit</small></label>
                                <select class="form-control input-sm select2bs4" id="l_unit" name="l_unit"
                                    style="width: 100%;">
                                    <option selected="selected" value="inch">INCH</option>
                                    <option value="cm">CM</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="mb-1">
                                <label class="form-label"><small>Gramasi</small></label>
                                <input type="number" class="form-control" id="gramasi" name="gramasi" step=".001">
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="mb-1">
                                <label class="form-label"><small>Cons Marker</small></label>
                                <input type="number" class="form-control" id="cons_marker" name="cons_marker" step=".001">
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="mb-1">
                                <label class="form-label"><small>Qty Gelar Marker</small></label>
                                <input type="number" class="form-control" id="gelar_marker_qty"
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
                        <input type="text" class="form-control" id="no_urut_marker" name="no_urut_marker"
                            readonly>
                    </div>
                </div>
                <input type="hidden" class="form-control" id="jumlah_so_det" name="jumlah_so_det" readonly>
            </div>
        </div>
    </div>
    <div class="card card-sb card-outline">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                Data Ratio :
            </h5>
        </div>
        <div class="card-body table-responsive">
            <table id="datatable" class="table table-bordered table-striped table-sm w-100">
                <thead>
                    <tr>
                        <th>WS</th>
                        <th>Color</th>
                        <th>Size</th>
                        <th>Size Input</th>
                        <th>QTY Order</th>
                        <th>Sisa</th>
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
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Page specific script -->
    <script>
        var sumCutQty = null;
        $(document).ready(async function () {
            sumCutQty = await $.ajax({
                url: '{{ route("create-marker") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color').val(),
                },
                dataType: 'json',
            });
        });

        //Initialize Select2 Elements
        $('.select2').select2()

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        $("#color").prop("disabled", true);
        $("#panel").prop("disabled", true);
        $('#p_unit').val("yard").trigger('change');

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
            await updateSizeList();
        });

        $('#p_unit').on('change', async function(e) {
            let unit = $('#p_unit').val();
            if (unit == 'yard') {
                $('#comma_unit').val('INCH');
                $('#l_unit').val('inch').trigger("change");
            } else if (unit == 'meter') {
                $('#comma_unit').val('CM');
                $('#l_unit').val('cm').trigger("change");
            }
        });

        function updateOrderInfo() {
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-marker-order") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color').val(),
                },
                dataType: 'json',
                success: function (res) {
                    if (res) {
                        document.getElementById('ws').value = res.kpno;
                        document.getElementById('buyer').value = res.buyer;
                        document.getElementById('style').value = res.styleno;
                    }
                },
            });
        }

        function updateColorList() {
            document.getElementById('color').value = null;
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-marker-colors") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('color').innerHTML = res;
                        document.getElementById('panel').innerHTML = null;
                        document.getElementById('panel').value = null;

                        $("#color").prop("disabled", false);
                        $("#panel").prop("disabled", true);

                        // input text
                        document.getElementById('no_urut_marker').value = null;
                        document.getElementById('cons_ws').value = null;
                        document.getElementById('order_qty').value = null;
                    }
                },
            });
        }

        function updatePanelList() {
            document.getElementById('panel').value = null;
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-marker-panels") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color').val(),
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('panel').innerHTML = res;
                        $("#panel").prop("disabled", false);

                        // input text
                        document.getElementById('no_urut_marker').value = null;
                        document.getElementById('cons_ws').value = null;
                        document.getElementById('order_qty').value = null;
                    }
                },
            });
        }

        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("get-marker-sizes") }}',
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
                    data: 'size'
                },
                {
                    data: 'size'
                },
                {
                    data: 'order_qty'
                },
                {
                    data: null
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
                    targets: [3],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="size-' + meta.row + '" name="size['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [5],
                    render: (data, type, row, meta) => {
                        let sumCutQtyData = sumCutQty.find(o => o.so_det_id == row.id && o.panel == $('#panel').val())  ;
                        let left = row.order_qty - (sumCutQtyData ? sumCutQtyData.total_cut_qty : 0);

                        return left < 0 ? 0 : left;
                    }
                },
                {
                    targets: [6],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="so-det-id-' + meta.row + '" name="so_det_id['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [7],
                    render: (data, type, row, meta) => {
                        let sumCutQtyData = sumCutQty.find(o => o.so_det_id == row.id &&  o.panel == $('#panel').val())  ;
                        let left = row.order_qty - (sumCutQtyData ? sumCutQtyData.total_cut_qty : 0);
                        let readonly = left < 1 ? "readonly" : "";

                        return '<input type="number" id="ratio-' + meta.row + '" name="ratio[' + meta.row + ']" onchange="calculateRatio(' + meta.row + ')" onkeyup="calculateRatio(' + meta.row + ')" '+readonly+' />';
                    }
                },
                {
                    targets: [8],
                    render: (data, type, row, meta) => '<input type="number" id="cut-qty-' + meta.row + '" name="cut_qty['+meta.row+']" readonly />'
                }
            ]
        });

        async function updateSizeList() {
            return datatable.ajax.reload(() => {
                document.getElementById('jumlah_so_det').value = datatable.data().count();
            });
        }

        function getMarkerCount() {
            document.getElementById('no_urut_marker').value = "";
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-marker-count") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color').val(),
                    panel: $('#panel').val()
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('no_urut_marker').value = res;
                    }
                }
            });
        }

        function getNumber() {
            document.getElementById('cons_ws').value = null;
            document.getElementById('order_qty').value = null;
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: ' {{ route("get-marker-number") }}',
                type: 'get',
                dataType: 'json',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color').val(),
                    panel: $('#panel').val()
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('cons_ws').value = res.cons_ws;
                        document.getElementById('order_qty').value = res.order_qty;
                    }
                }
            });

        }

        function calculateRatio(id) {
            let ratio = document.getElementById('ratio-'+id).value;
            let gelarQty = document.getElementById('gelar_marker_qty').value;
            document.getElementById('cut-qty-'+id).value = ratio * gelarQty;
        }

        function calculateAllRatio(element) {
            let gelarQty = element.value;

            for (let i = 0; i < datatable.data().count(); i++) {
                let ratio = document.getElementById('ratio-'+i).value;
                document.getElementById('cut-qty-'+i).value = ratio * gelarQty;
            }
        }

        document.getElementById("store-marker").onkeypress = function(e) {
            var key = e.charCode || e.keyCode || 0;
            if (key == 13) {
                e.preventDefault();
            }
        }

        function submitMarkerForm(e, evt) {
            evt.preventDefault();

            clearModified();


            $.ajax({
                url: e.getAttribute('action'),
                type: e.getAttribute('method'),
                data: new FormData(e),
                processData: false,
                contentType: false,
                success: async function(res) {
                    if (res.status == 200) {
                        e.reset();

                        Swal.fire({
                            icon: 'success',
                            title: 'Data Marker berhasil disimpan',
                            text: res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        })

                        await $("#ws_id").val(null).trigger("change");
                        await $("#panel").val(null).trigger("change");
                        await $("#color").val(null).trigger("change");
                        await $('#p_unit').val("yard").trigger('change');
                        await $("#panel").prop("disabled", true);
                        await $("#color").prop("disabled", true);

                        sumCutQty = await $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            url: '{{ route("create-marker") }}',
                            type: 'get',
                            data: {
                                act_costing_id: $('#ws_id').val(),
                                color: $('#color').val(),
                            },
                            dataType: 'json',
                        });
                    } else {
                        iziToast.error({
                            title: 'Error',
                            message: res.message,
                            position: 'topCenter'
                        });
                    }

                    $('#datatable').DataTable().ajax.reload();

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
