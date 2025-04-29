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
    <form action="{{ route('update-cutting-reject') }}" method="post" id="update-cutting-form-reject" onsubmit="submitForm(this, event)">
        @method("PUT")
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fa fa-edit"></i> Edit Reject Form
                </h5>
            </div>
            <div class="card-body">
                <div class="row row-gap-3">
                    <div class="col-md-12 d-none">
                        <label class="form-label">ID</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="id" name="id" value="{{ $form->id }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">No. Form</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="no_form" name="no_form" value="{{ $form->no_form }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ $form->tanggal }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Worksheet</label>
                        <input type="hidden" class="form-control" id="act_costing_id" name="act_costing_id" value="{{ $form->act_costing_id }}" readonly>
                        <input type="text" class="form-control" id="act_costing_ws" name="act_costing_ws" value="{{ $form->act_costing_ws}}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Buyer</label>
                        <input type="hidden" class="form-control" id="buyer_id" name="buyer_id" value="{{ $form->buyer_id }}" readonly>
                        <input type="text" class="form-control" id="buyer" name="buyer" value="{{ $form->buyer }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Style</label>
                        <input type="text" class="form-control" id="style" name="style" value="{{ $form->style }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Color</label>
                        <input type="text" class="form-control" id="color" name="color" value="{{ $form->color }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Panel</label>
                        <input type="text" class="form-control" id="panel" name="panel" value="{{ $form->panel }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Group</label>
                        <input type="text" class="form-control" id="group" name="group" value="{{ $form->group }}" readonly>
                    </div>
                    <div class="col-md-12 table-responsive">
                        <table class="table table-bordered table mt-3" id="cutting-reject-table">
                            <thead>
                                <tr>
                                    <th>So Det ID</th>
                                    <th>Size</th>
                                    <th>Dest</th>
                                    <th>Qty Output</th>
                                </tr>
                            </thead>
                            <tbody>
                                <td colspan="3" class="text-center">Data tidak ditemukan</td>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th>Total</th>
                                    <th id="total-detail-qty">...</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card-footer border-top">
                <div class="d-flex justify-content-end gap-1">
                    <a href="{{ route('cutting-reject') }}" class="btn btn-sm btn-danger"><i class="fa fa-times"></i> BATAL</a>
                    <button type="submit" class="btn btn-sm btn-sb-secondary"><i class="fa fa-check"></i> SIMPAN</button>
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
    <script>
        document.getElementById("loading").classList.remove("d-none");

        // Initial Window On Load Event
        $(document).ready(async function () {
            //Reset Form
            if (document.getElementById('store-cutting-form-reject')) {
                document.getElementById('store-cutting-form-reject').reset();

                $("#act_costing_id").val(null).trigger("change");
            }

            // Select2 Prevent Step-Jump Input ( Step = WS -> Color -> Panel )
            // $("#color").prop("disabled", true);
            // $("#panel").prop("disabled", true);

            // cuttingRejectCode();

            document.getElementById("loading").classList.add("d-none");
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

        // Step One (WS) on change event
        $('#act_costing_id').on('change', function(e) {
            if (this.value) {
                updateColorList();
                updateOrderInfo();
            }
        });

        // Step Two (Color) on change event
        $('#color').on('change', function(e) {
            if (this.value) {
                updatePanelList();
                cuttingRejectTableReload();
            }
        });

        // Update Order Information Based on Order WS and Order Color
        function updateOrderInfo() {
            return $.ajax({
                url: '{{ route("get-general-order") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#act_costing_id').val(),
                    color: $('#color').val(),
                },
                dataType: 'json',
                success: function (res) {
                    if (res) {
                        document.getElementById('act_costing_ws').value = res.kpno;
                        document.getElementById('buyer_id').value = res.id_buyer;
                        document.getElementById('buyer').value = res.buyer;
                        document.getElementById('style').value = res.styleno;
                    }
                },
            });
        }

        // Update Color Select Option Based on Order WS
        function updateColorList() {
            document.getElementById('color').value = null;

            return $.ajax({
                url: '{{ route("get-colors") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#act_costing_id').val(),
                },
                success: function (res) {
                    if (res) {
                        // Update this step
                        let select = document.getElementById("color");

                        select.innerHTML = "";

                        for (let i=0; i < res.length; i++) {
                            let newOption = document.createElement("option");
                            newOption.value = res[i].color;
                            newOption.innerHTML = res[i].color;

                            select.appendChild(newOption);
                        }

                        // select.removeAttribute("disabled");

                        $("#color").val(res[0].color).trigger("change");
                    }
                },
            });
        }

        // Update Panel Select Option Based on Order WS and Color WS
        function updatePanelList() {
            document.getElementById('panel').value = null;
            return $.ajax({
                url: '{{ route("get-panels") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#act_costing_id').val(),
                    color: $('#color').val(),
                },
                success: function (res) {
                    if (res) {
                        // Update this step
                        let select = document.getElementById("panel");

                        select.innerHTML = "";

                        for (let i=0; i < res.length; i++) {
                            let newOption = document.createElement("option");
                            newOption.value = res[i].panel;
                            newOption.innerHTML = res[i].panel;

                            select.appendChild(newOption);
                        }

                        // select.removeAttribute("disabled");

                        $("#panel").val(res[0].color).trigger("change");
                    }
                },
            });
        }

        let cuttingRejectTable = $("#cutting-reject-table").DataTable({
            processing: true,
            ordering: false,
            serverSide: true,
            paging: false,
            ajax: {
                url: '{{ route('get-form-reject-sizes') }}',
                data: function(d) {
                    d.id = $("#id").val();
                    d.act_costing_id = $("#act_costing_id").val();
                    d.color = $("#color").val();
                },
            },
            columns: [
                {
                    data: 'so_det_id',
                },
                {
                    data: 'size',
                },
                {
                    data: 'dest',
                },
                {
                    data: 'qty',
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    className: "d-none",
                    render: (data, type, row, meta) => {
                        let input = `<input type='text' class='form-control form-control-sm' id='so_det_id_`+meta.row+`' name='so_det_id[`+meta.row+`]' value='`+data+`' readonly>`

                        return input;
                    }
                },
                {
                    targets: [1,2],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        return data;
                    }
                },
                {
                    targets: [3],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        let input = `<input type='number' class='form-control form-control-sm detail-qty' id='qty_`+meta.row+`' name='qty[`+meta.row+`]' value="`+data+`" onkeyup="calculateTotalDetailQty()" onchange="calculateTotalDetailQty()">`

                        return input;
                    }
                },
            ],
            drawCallback: async function (settings) {
                await calculateTotalDetailQty();
            }
        });

        function cuttingRejectTableReload() {
            $("#cutting-reject-table").DataTable().ajax.reload();
        }

        function cuttingRejectCode() {
            $.ajax({
                url: "{{ route("generate-code-cutting-reject") }}",
                type: "get",
                success: function (response) {
                    document.getElementById("no_form").value = response
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });
        }

        function calculateTotalDetailQty() {
            let detailQtyElements = document.getElementsByClassName("detail-qty");

            console.log(detailQtyElements.length);

            let totalQty = 0;
            for (let i = 0; i < detailQtyElements.length; i++) {
                console.log(i, detailQtyElements[i].value, detailQtyElements[i]);
                totalQty += Number(detailQtyElements[i].value);
            }

            document.getElementById("total-detail-qty").innerHTML = totalQty;
        }
    </script>
@endsection
