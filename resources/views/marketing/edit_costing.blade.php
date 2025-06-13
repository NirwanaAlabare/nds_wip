@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .rotate-icon {
            transition: transform 0.3s ease;
        }

        .rotate-icon.rotated {
            transform: rotate(180deg);
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <!-- Collapsible Header -->
        <div class="card-header text-start">
            <h5 class="card-title fw-bold mb-1">
                <i class="fas fa-edit"></i> Edit Costing Header
            </h5>
        </div>
        <form action="{{ route('update_header_master_costing') }}" enctype="multipart/form-data" method="post"
            name='form_update_header_cost' id='form_update_header_cost'>
            @csrf
            @method('POST')
            <!-- Always visible fields (NOT collapsible) -->
            <div class="card-body pb-0">
                <div class="row mb-1">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><small><b>No. Costing :</b></small></label>
                            <input type="text" class="form-control form-control-sm" id="no_costing" name="no_costing"
                                value="{{ $no_cost }}" readonly>
                            <input type="hidden" class="form-control form-control-sm" id="id_cost" name="id_cost"
                                value="{{ $id }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><small><b>Buyer :</b></small></label>
                            <input type="text" class="form-control form-control-sm" id="buyer" name="buyer"
                                value="{{ $buyer }}"readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><small><b>Style :</b></small></label>
                            <input type="text" class="form-control form-control-sm" id="txtstyle" name="txtstyle"
                                value="{{ $style }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><small><b>Tgl. Costing :</b></small></label>
                            <input type="text" class="form-control form-control-sm" id="tgl_costing" name="tgl_costing"
                                value="{{ $tgl_input }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><small><b>Worksheet :</b></small></label>
                            <input type="text" class="form-control form-control-sm" id="txtws" name="txtws"
                                value="{{ $ws }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Collapsible content (only this hides) -->
            <div id="collapseBody" class="collapse">
                <div class="card-body pt-0">
                    <div class="row mb-1">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><small><b>Product Group :</b></small></label>
                                <select class="select2bs4" id="cbop_group" name="cbop_group" style="width: 100%;"
                                    onchange="getprod_item();" required>
                                    <option selected="selected" value="" disabled="true">Pilih Product Group
                                    </option>
                                    @foreach ($data_pgroup as $pg)
                                        <option value="{{ $pg->isi }}"
                                            {{ $pg->isi == $product_group ? 'selected' : '' }}>
                                            {{ $pg->tampil }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label><small><b>Curr :</b></small></label>
                                <select class="select2bs4" id="cbocurr" name="cbocurr" style="width: 100%;"required>
                                    <option selected="selected" value="" disabled="true"></option>
                                    @foreach ($data_curr as $dc)
                                        <option value="{{ $dc->isi }}" {{ $dc->isi == $curr ? 'selected' : '' }}>
                                            {{ $dc->tampil }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><small><b>Confirm Price :</b></small></label>
                                <input type="text" class="form-control form-control-sm" id="txtcfm_price"
                                    name="txtcfm_price" value="{{ $cfm_price }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><small><b>Delivery Date :</b></small></label>
                                <input type="date" class="form-control form-control-sm" id="txtdel_date"
                                    name="txtdel_date" value="{{ $deldate }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><small><b>Ship Mode :</b></small></label>
                                <select class="select2bs4" id="cbo_ship" name="cbo_ship" style="width: 100%;"required>
                                    <option selected="selected" value="" disabled="true"></option>
                                    @foreach ($data_ship as $ds)
                                        <option value="{{ $ds->isi }}"
                                            {{ $ds->isi == $id_smode ? 'selected' : '' }}>
                                            {{ $ds->tampil }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><small><b>Tipe WS :</b></small></label>
                                <input type="text" class="form-control form-control-sm" id="txttipe_ws"
                                    name="txttipe_ws" value="{{ $type_ws }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><small><b>Product Item :</b></small></label>
                                <select class="select2bs4" id="cbop_item" name="cbop_item" style="width: 100%;"required>
                                    <option selected="selected" value="" disabled="true">Pilih Product Group
                                    </option>
                                    @foreach ($data_pitem as $pi)
                                        <option value="{{ $pi->isi }}"
                                            {{ $pi->isi == $id_product ? 'selected' : '' }}>
                                            {{ $pi->tampil }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><small><b>Main Destination :</b></small></label>
                                <input type="text" class="form-control form-control-sm" id="txtmain_dest"
                                    name="txtmain_dest" value="{{ $main_dest }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><small><b>Brand :</b></small></label>
                                <input type="text" class="form-control form-control-sm" id="txtbrand"
                                    name="txtbrand" value="{{ $brand }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><small><b>Qty Order :</b></small></label>
                                <input type="text" class="form-control form-control-sm" id="txtqty_order"
                                    name="txtqty_order" value="{{ $qty_order }}">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label><small><b>VAT :</b></small></label>
                                <input type="text" class="form-control form-control-sm" id="txtvat" name="txtvat"
                                    value="{{ $vat }}">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label><small><b>Rate :</b></small></label>
                                <input type="text" class="form-control form-control-sm" id="txtvat" name="txtvat"
                                    value="{{ $vat }}">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-1">
                            <div class="form-group">
                                <label><small><b>SMV (Mins):</b></small></label>
                                <input type="text" class="form-control form-control-sm" id="txtmain_dest"
                                    name="txtmain_dest" value="{{ $main_dest }}">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label><small><b>Book (Mins) :</b></small></label>
                                <input type="text" class="form-control form-control-sm" id="txtbrand"
                                    name="txtbrand" value="{{ $brand }}">
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDefault()">
                            <i class="fas fa-undo"></i> Default
                        </button>
                        <button type="submit" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-check"></i>
                            Edit
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Chevron at bottom (toggle button) -->
        <div class="text-center py-2" data-bs-toggle="collapse" data-bs-target="#collapseBody" style="cursor: pointer;">
            <div class="d-flex flex-column align-items-center">
                <small class="text-primary fw-semibold">See More</small>
                <i class="fas fa-chevron-down transition rotate-icon" id="collapseIcon"></i>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    {{-- <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script> --}}
    <script>
        const collapseBody = document.getElementById('collapseBody');
        const collapseIcon = document.getElementById('collapseIcon');

        collapseBody.addEventListener('show.bs.collapse', function() {
            collapseIcon.classList.add('rotated');
        });

        collapseBody.addEventListener('hide.bs.collapse', function() {
            collapseIcon.classList.remove('rotated');
        });
    </script>

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


        const defaultData = {
            style: @json($style),
            brand: @json($brand),
            curr: @json($curr),
            product_group: @json($product_group),
            product_item: @json($product_item),
            cfm_price: @json($cfm_price),
            deldate: @json($deldate),
            id_smode: @json($id_smode),
            main_dest: @json($main_dest),
            qty_order: @json($qty_order),
            vat: @json($vat),
            // Add more fields as needed
        };

        function setDefault() {
            $('#txtbrand').val(defaultData.brand);
            $('#txtstyle').val(defaultData.style);
            $('#cbocurr').val(defaultData.curr).trigger('change');
            $('#txtcfm_price').val(defaultData.cfm_price);
            $('#txtdel_date').val(defaultData.deldate);
            $('#cbo_ship').val(defaultData.id_smode).trigger('change');
            $('#txtmain_dest').val(defaultData.main_dest);
            $('#txtqty_order').val(defaultData.qty_order);
            $('#txtvat').val(defaultData.vat);
            // $('#cbop_group').val(defaultData.product_group);
            // getprod_item(function() {
            //     $('#cbop_item').val(defaultData.product_item).trigger('change');
            // });
        }

        function getprod_item(callback) {
            let prod_group = $('#cbop_group').val();

            $.ajax({
                type: "GET",
                url: '{{ route('getprod_item_costing') }}',
                data: {
                    prod_group: prod_group
                },
                success: function(data) {
                    let html = '<option value="">Pilih Product Item</option>';
                    data.forEach(function(item) {
                        html += `<option value="${item.isi}">${item.tampil}</option>`;
                    });
                    $("#cbop_item").html(html);

                    if (typeof callback === 'function') {
                        callback(); // Call the function after populating options
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        }
    </script>

    <script>
        document.getElementById('form_update_header_cost').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            const form = this;
            const formData = new FormData(form);
            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message,
                            confirmButtonColor: '#28a745'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong!'
                        });
                    }
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Server error occurred'
                    });
                });
        });
    </script>
@endsection
