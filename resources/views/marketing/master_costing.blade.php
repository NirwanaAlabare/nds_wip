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
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> List Data</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal_new_costing">
                    <i class="fas fa-plus"></i>
                    Baru
                </a>
            </div>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Costing Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        value="{{ $tgl_skrg_min_sebulan }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Costing Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <a class="btn btn-outline-primary position-relative btn-sm" onclick="dataTableReload()">
                        <i class="fas fa-search"></i>
                        Cari
                    </a>
                </div>
                <div class="mb-3">
                    <a onclick="notif()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped w-100 text-nowrap">
                    <thead class="bg-sb">
                        <tr style="text-align:center; vertical-align:middle">
                            <th scope="col">Costing</th>
                            <th scope="col">Tgl. Costing</th>
                            <th scope="col">Buyer</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Style</th>
                            <th scope="col">WS</th>
                            <th scope="col">Product Group</th>
                            <th scope="col">Product Item</th>
                            <th scope="col">Main Dest</th>
                            <th scope="col">Created By</th>
                            <th scope="col">Status Order</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>



    <!-- Modal -->
    <div class="modal fade" id="modal_new_costing" tabindex="-1" aria-labelledby="modalCostingLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <form action="{{ route('upload-packing-list') }}" enctype="multipart/form-data" method="post"
                    onsubmit="submitForm(this, event)" name='form_new_costing' id='form_new_costing'>
                    @csrf
                    @method('POST')
                    <div class="modal-header bg-sb text-white">
                        <h5 class="modal-title" id="modalCostingLabel"><i class="fas fa-plus"></i> New Costing</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class='row g-3'>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><small><b>Buyer :</b></small></label>
                                    <select class="form-control form-control-sm select2bs4" id="cbobuyer" name="cbobuyer"
                                        style="width: 100%; font-size: 0.875rem;" required>
                                        <option selected="selected" value="" disabled="true"><small>Pilih Buyer
                                        </option>
                                        @foreach ($data_buyer as $databuyer)
                                            <option value="{{ $databuyer->isi }}">
                                                {{ $databuyer->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><small><b>Curr :</b></small></label>
                                    <select class="form-control select2bs4" id="cbocurr" name="cbocurr"
                                        style="width: 100%; font-size: 0.875rem;" required>
                                        <option selected="selected" value="" disabled="true">Pilih Currency
                                        </option>
                                        @foreach ($data_curr as $datacurr)
                                            <option value="{{ $datacurr->isi }}">
                                                {{ $datacurr->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label><small><b>Notes :</b></small></label>
                                <input type="text" name="txtnotes" id="txtnotes" class="form-control"
                                    style="height: calc(2.15rem + 2px); padding: 0.375rem 0.75rem; font-size: 0.875rem;"
                                    placeholder="Masukan Notes" required>
                            </div>
                        </div>
                        <div class='row g-3'>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><small><b>Product Group :</b></small></label>
                                    <select class="form-control form-control-sm select2bs4" id="cbop_group"
                                        name="cbop_group" style="width: 100%; font-size: 0.875rem;"
                                        onchange="getprod_item();" required>
                                        <option selected="selected" value="" disabled="true">Pilih Product Group
                                        </option>
                                        @foreach ($data_pgroup as $datapgroup)
                                            <option value="{{ $datapgroup->isi }}">
                                                {{ $datapgroup->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label><small><b>Confirm Price :</b></small></label>
                                <input type="text" name="txtcfm_price" id="txtcfm_price" class="form-control"
                                    style="height: calc(2.15rem + 2px); padding: 0.375rem 0.75rem; font-size: 0.875rem;"
                                    placeholder="Masukan Confirm Price" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><small><b>Delivery Date</b></small></label>
                                <input type="date" class="form-control"
                                    style="height: calc(2.15rem + 2px); padding: 0.375rem 0.75rem; font-size: 0.875rem;"
                                    id="txtdel_date" name="txtdel_date" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class='row g-3'>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><small><b>Product Item :</b></small></label>
                                    <select class='form-control select2bs4 form-control-sm'
                                        style="width: 100%; font-size: 0.875rem;" name='cbop_item'
                                        id='cbop_item'></select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label><small><b>Ship Mode :</b></small></label>
                                <select class="form-control form-control-sm select2bs4" id="cbo_ship" name="cbo_ship"
                                    style="width: 100%; font-size: 0.875rem;" required>
                                    <option selected="selected" value="" disabled="true">Pilih Ship Mode
                                    </option>
                                    @foreach ($data_ship as $dataship)
                                        <option value="{{ $dataship->isi }}">
                                            {{ $dataship->tampil }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><small><b>Status</b></small></label>
                                <select class="form-control form-control-sm select2bs4" id="cbo_stat" name="cbo_stat"
                                    style="width: 100%; font-size: 0.875rem;" required>
                                    <option selected="selected" value="" disabled="true">Pilih Status
                                    </option>
                                    @foreach ($data_status as $datastatus)
                                        <option value="{{ $datastatus->isi }}">
                                            {{ $datastatus->tampil }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class='row g-3'>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><small><b>Style :</b></small></label>
                                    <input type="text" name="txtstyle" id="txtstyle" class="form-control"
                                        style="height: calc(2.15rem + 2px); padding: 0.375rem 0.75rem; font-size: 0.875rem;"
                                        placeholder="Masukan Style" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><small><b>Qty :</b></small></label>
                                    <input type="text" name="txtqty" id="txtqty" class="form-control"
                                        style="height: calc(2.15rem + 2px); padding: 0.375rem 0.75rem; font-size: 0.875rem;"
                                        placeholder="Masukan Qty" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><small><b>Main Destination :</b></small></label>
                                    <input type="text" name="txtdest" id="txtdest" class="form-control"
                                        style="height: calc(2.15rem + 2px); padding: 0.375rem 0.75rem; font-size: 0.875rem;"
                                        placeholder="Masukan Main Destination" required>
                                </div>
                            </div>
                        </div>
                        <div class='row g-3'>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><small><b>Brand :</b></small></label>
                                    <input type="text" name="txtbrand" id="txtbrand" class="form-control"
                                        style="height: calc(2.15rem + 2px); padding: 0.375rem 0.75rem; font-size: 0.875rem;"
                                        placeholder="Masukan Brand" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><small><b>VAT (%):</b></small></label>
                                    <input type="text" name="txtvat" id="txtvat" class="form-control"
                                        style="height: calc(2.15rem + 2px); padding: 0.375rem 0.75rem; font-size: 0.875rem;"
                                        placeholder="Masukan VAT" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><small><b>Tipe WS</b></small></label>
                                <select class="form-control form-control-sm select2bs4" id="cbo_tipe" name="cbo_tipe"
                                    style="width: 100%; font-size: 0.875rem;" required>
                                    <option value="standard">Standard</option>
                                    <option value="global">Global</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-dismiss="modal"><i
                                class="fas fa-times-circle"></i> Tutup</button>
                        <a class="btn btn-outline-success btn-sm" onclick="simpan()">
                            <i class="fas fa-check"></i>
                            Simpan
                        </a>
                    </div>
                </form>
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

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        function dataTableReload() {
            datatable.ajax.reload();
        }

        $(document).ready(function() {
            dataTableReload();
        })

        function capitalizeWords(str) {
            return str.replace(/\b\w/g, char => char.toUpperCase());
        }

        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            scrollY: '500px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route('master-costing') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'cost_no'

                },
                {
                    data: 'cost_date'

                }, {
                    data: 'buyer'
                },
                {
                    data: 'brand'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'kpno'
                },
                {
                    data: 'product_group'
                },
                {
                    data: 'product_item'
                },
                {
                    data: 'main_dest'
                },
                {
                    data: null, // Combine buyer + cost_date
                    render: function(data, type, row) {
                        return `${capitalizeWords(row.username)}<br><small>${row.dateinput}</small>`;
                    },
                    name: 'buyer' // Optional: use one of the fields as reference
                },
                {
                    data: 'status_order'
                },
            ],
        });

        $('#modal_new_costing').on('show.bs.modal', function(e) {
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                dropdownParent: $("#modal_new_costing")
            });

            $('#form_new_costing')[0].reset(); // reset all fields
            $('#cbobuyer').val('').trigger('change'); // reset select2 field
            $('#cbocurr').val('').trigger('change'); // reset select2 field
            $('#cbop_group').val('').trigger('change'); // reset select2 field
            $('#cbop_item').html('<option value="">Pilih Product Item</option>'); // reset item list
            $('#cbo_ship').val('').trigger('change'); // reset select2 field
            $('#cbo_stat').val('').trigger('change'); // reset select2 field
            $('#cbo_tipe').val('').trigger('change'); // reset select2 field

        });

        function getprod_item() {
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
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        }
    </script>
@endsection
