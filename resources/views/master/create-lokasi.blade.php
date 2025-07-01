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
<form action="{{ route('store-lokasi') }}" method="post" id="store-lokasi" onsubmit="submitForm(this, event)">
    @csrf
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                Add Data
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
            <div class="col-md-3">
            <div class="mb-1">
                <div class="form-group">
                <label>Location Area</label>
                <select class="form-control select2bs4" id="txt_area" name="txt_area" style="width: 100%;">
                    <option selected="selected" value="">Select Area</option>
                        @foreach ($arealok as $alok)
                    <option value="{{ $alok->area }}">
                                {{ $alok->area }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>
            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label>Storage Initial</label>
                <input type="text" class="form-control " id="txt_inisial" name="txt_inisial" value="">
                </div>
            </div>
            </div>
            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label>Row</label>
                <input type="number" class="form-control " id="txt_baris" name="txt_baris" value="" min="0">
                </div>
            </div>
            </div>
            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label>Level</label>
                <input type="number" class="form-control " id="txt_level" name="txt_level" value="" min="0">
                </div>
            </div>
            </div>
            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label>Number</label>
                <input type="text" class="form-control " id="txt_num" name="txt_num" value="">
                </div>
            </div>
            </div>
            <div class="col-md-3">
            <div class="mb-1">
                <div class="form-group">
                <label>Unit</label>
                <select class="form-control select2roll" id="txt_unit" name="txt_unit" style="width: 100%;">
                    <option selected="selected" value="">Select Unit</option>
                        @foreach ($unit as $un)
                    <option value="{{ $un->nama_unit }}">
                                {{ $un->nama_unit }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>
            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label>Capacity</label>
                <input type="number" class="form-control " id="txt_capacity" name="txt_capacity" value="" min="0">
                </div>
            </div>
            </div>
            <div class="mb-1">
                <div class="form-group">
                    <button class="btn btn-sb float-end mt-2 ml-2"><i class="fa fa-plus" aria-hidden="true"></i> Save</button>
                    <a href="{{ route('master-lokasi') }}" class="btn btn-warning float-end mt-2">
                    <i class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
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

        //Initialize Select2 Elements
        $('.select2').select2()

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        $('.select2roll').select2({
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
                url: '{{ route("get-general-panels") }}',
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

        function submitLokasiForm(e, evt) {
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
                        console.log(res);

                        e.reset();

                        // $('#cbows').val("").trigger("change");
                        // $("#cbomarker").prop("disabled", true);

                        Swal.fire({
                            icon: 'success',
                            title: 'Data Spreading berhasil disimpan',
                            html: "No. Form Cut : <br>" + res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        })

                        datatable.ajax.reload();
                    }
                },

            });
        }
    </script>
@endsection
