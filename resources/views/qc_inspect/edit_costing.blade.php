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

        .select2-selection__rendered {
            overflow: auto !important;
            text-overflow: ellipsis;
            white-space: nowrap;
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
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><small><b>Buyer :</b></small></label>
                            <input type="text" class="form-control form-control-sm" id="buyer" name="buyer"
                                value="{{ $buyer }}"readonly>
                        </div>
                    </div>
                    <div class="col-md-2">
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
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><small><b>Tipe WS :</b></small></label>
                            <input type="text" class="form-control form-control-sm" id="txttipe_ws" name="txttipe_ws"
                                value="{{ $type_ws }}" readonly>
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
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><small><b>Brand :</b></small></label>
                                <input type="text" class="form-control form-control-sm" id="txtbrand" name="txtbrand"
                                    value="{{ $brand }}">
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
                                <label><small><b>Main Destination :</b></small></label>
                                <input type="text" class="form-control form-control-sm" id="txtmain_dest"
                                    name="txtmain_dest" value="{{ $main_dest }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><small><b>Delivery Date :</b></small></label>
                                <input type="date" class="form-control form-control-sm" id="txtdel_date"
                                    name="txtdel_date" value="{{ $deldate }}">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-2">
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
                                <label><small><b>Rate :</b></small></label>
                                <input type="text" class="form-control form-control-sm" id="txtrate" name="txtrate"
                                    value="{{ $txtrate }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><small><b>Confirm Price :</b></small></label>
                                <input type="text" class="form-control form-control-sm" id="txtcfm_price"
                                    oninput="calculateFinalPrice()" name="txtcfm_price" value="{{ $cfm_price }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><small><b>VAT (%) :</b></small></label>
                                <select class="select2bs4" id="cbo_vat" name="cbo_vat" style="width: 100%;"required
                                    onchange="calculateFinalPrice()">
                                    <option selected="selected" value="" disabled="true"></option>
                                    @foreach ($data_vat as $dv)
                                        <option value="{{ $dv->isi }}" {{ $dv->isi == $vat ? 'selected' : '' }}>
                                            {{ $dv->tampil }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><small><b>Final Confirm Price :</b></small></label>
                                <input type="text" class="form-control form-control-sm" id="txtfinal_cfm_price"
                                    name="txtfinal_cfm_price" value="{{ $cfm_price }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><small><b>Qty Order (PCS) :</b></small></label>
                                <input type="text" class="form-control form-control-sm" id="txtqty_order"
                                    name="txtqty_order" oninput="calculateMinutes()" value="{{ $qty_order }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><small><b>SMV :</b></small></label>
                                <input type="text" class="form-control form-control-sm" id="txtsmv" name="txtsmv"
                                    oninput="calculateMinutes()" value="{{ $txtsmv }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><small><b>Total Minute Production :</b></small></label>
                                <input type="text" class="form-control form-control-sm" id="txtmin_prod"
                                    name="txtmin_prod" value="{{ $txtmin_prod }}" readonly>
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


    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">
                <i class="fas fa-list"></i> List Item Costing
            </h5>
        </div>

        <div class="card-body pb-0">
            <div class="row mb-3">
                <div class="col-md-2">
                    <div class="form-group">
                        <label><small><b>Category :</b></small></label>
                        <select class="form-control select2bs4" id="cbo_cat" name="cbo_cat"
                            onchange="show_jns();show_item();">
                            <option value="all">All</option>
                            <option value="material">Material</option>
                            <option value="manufacturing">Manufacturing - Complexity</option>
                            <option value="other">Other Cost</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2" id="jns_wrapper" style="display:none;">
                    <div class="form-group">
                        <label><small><b>Jenis :</b></small></label>
                        <select class="form-control select2bs4" id="cbo_jns" name="cbo_jns" onchange="show_item();">
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label><small><b>Pilih Item :</b></small></label>
                        <select class="form-control select2bs4" id="cbo_item" name="cbo_item">
                        </select>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label><small><b>Price :</b></small></label>
                        <input type="text" class="form-control form-control-sm" id="txtprice" name="txtprice">
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label><small><b>Cons :</b></small></label>
                        <input type="text" class="form-control form-control-sm" id="txtcons" name="txtcons">
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label><small><b>UOM :</b></small></label>
                        <select class="form-control form-control-sm select2bs4" id="cbounit" name="cbounit"
                            style="width: 100%; font-size: 0.875rem;" required>
                            <option selected="selected" value="" disabled="true"><small>Pilih
                                    Unit</small>
                            </option>
                            @foreach ($data_unit as $du)
                                <option value="{{ $du->isi }}">
                                    {{ $du->tampil }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label><small><b>Allow (%) :</b></small></label>
                        <input type="text" class="form-control form-control-sm" id="txtallow" name="txtallow">
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label>&nbsp;</label> <!-- Keeps vertical alignment -->
                        <button type="button" class="btn btn-sm btn-success btn-block"
                            onclick="addSomething()">Add</button>
                    </div>
                </div>

            </div>
        </div>

        <div class="table-responsive">
            <table id="datatable" class="table table-bordered table-striped w-100 text-nowrap">
                <thead class="bg-sb">
                    <tr style="text-align:center; vertical-align:middle">
                        <th scope="col">ID Contents</th>
                        <th scope="col">Kode Barang</th>
                        <th scope="col">Description</th>
                        <th scope="col">Price (USD)</th>
                        <th scope="col">Price (IDR)</th>
                        <th scope="col">Cons</th>
                        <th scope="col">UOM</th>
                        <th scope="col">Allow (%)</th>
                        <th scope="col">Value (USD)</th>
                        <th scope="col">Value (IDR)</th>
                        <th scope="col">Percent</th>
                        <th scope="col">Material Source</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
            </table>
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
            txtrate: @json($txtrate),
            txtsmv: @json($txtsmv),
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
            $('#cbo_vat').val(defaultData.vat).trigger('change');
            $('#txtrate').val(defaultData.txtrate);
            $('#txtsmv').val(defaultData.txtsmv);
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

        function calculateFinalPrice() {
            var price = parseFloat(document.getElementById('txtcfm_price').value) || 0;
            var vat = parseFloat(document.getElementById('cbo_vat').value) || 0;

            var finalPrice = price / (1 + (vat / 100));
            document.getElementById('txtfinal_cfm_price').value = finalPrice.toFixed(2);
        }


        function calculateFinalPrice() {
            var price = parseFloat(document.getElementById('txtcfm_price').value) || 0;
            var vat = parseFloat(document.getElementById('cbo_vat').value) || 0;

            var finalPrice = price / (1 + (vat / 100));
            document.getElementById('txtfinal_cfm_price').value = finalPrice.toFixed(2);
        }

        function calculateMinutes() {
            var qty = parseFloat(document.getElementById('txtqty_order').value) || 0;
            var smv = parseFloat(document.getElementById('txtsmv').value) || 0;

            var totalMinutes = qty * smv;
            document.getElementById('txtmin_prod').value = totalMinutes.toFixed(2);
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
                            title: 'Updated successfully!',
                            text: `Style : ${data.data.style} `, // ✅ Custom success message
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


        function show_item() {
            let cbo_cat = $('#cbo_cat').val();
            let cbo_jns = cbo_cat === 'material' ? $('#cbo_jns').val() : null;

            $.ajax({
                type: "GET",
                url: '{{ route('get_material_costing') }}',
                data: {
                    cbo_cat: cbo_cat,
                    cbo_jns: cbo_jns // Pass null if not required
                },
                success: function(data) {
                    let html = '<option value="">Pilih Product Item</option>';
                    data.forEach(function(item) {
                        html +=
                            `<option value="${item.isi}" title="${item.tampil}">${item.tampil}</option>`;
                    });
                    $("#cbo_item").html(html);
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        }


        function show_jns() {
            const catValue = document.getElementById("cbo_cat").value;
            const jnsWrapper = document.getElementById("jns_wrapper");

            if (catValue === "material") {
                jnsWrapper.style.display = "block";

                // Load Jenis options
                $.ajax({
                    type: "GET",
                    url: '{{ route('get_jns_costing_material') }}',
                    success: function(data) {
                        let html = '<option value="">Pilih Jenis</option>';
                        data.forEach(function(item) {
                            html += `<option value="${item.isi}">${item.tampil}</option>`;
                        });
                        $("#cbo_jns").html(html);
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                    }
                });

            } else {
                jnsWrapper.style.display = "none";
                $("#cbo_jns").html(""); // Clear Jenis dropdown

                // 🔽 Clear and reset cbo_item to default
                $("#cbo_item").html('<option value="">Pilih Product Item</option>');

                // Load item list for non-costing categories (if needed)
                show_item();
            }
        }


        // Run it once on page load in case default value is "material"
        document.addEventListener("DOMContentLoaded", function() {
            show_jns();
        });
    </script>
@endsection
