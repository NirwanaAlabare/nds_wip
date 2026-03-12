@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        #itemTable { table-layout: fixed; width: 100%; }
        #itemTable td { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .select2-container { width: 100% !important; }
        .select2-selection__rendered { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 400px; }
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            white-space: normal !important; word-wrap: break-word !important; height: auto !important;
            line-height: 1.5 !important; padding-top: 5px !important; padding-bottom: 5px !important;
        }
        .select2-results__option { white-space: normal !important; word-wrap: break-word !important; }
        .select2-container .select2-selection--single { height: auto !important; }

        table.dataTable thead .sorting::after,
        table.dataTable thead .sorting::before,
        table.dataTable thead .sorting_asc::after,
        table.dataTable thead .sorting_asc::before,
        table.dataTable thead .sorting_desc::after,
        table.dataTable thead .sorting_desc::before {
            display: none !important;
        }

        table.dataTable thead th {
            padding-right: 8px !important;
        }
    </style>
@endsection

@section('content')
<form id="form-edit-bom" class="form-add-material" action="{{ route('store-bom-detail-edit') }}" method="POST">
    @csrf

    <div class="card card-primary">
        <div class="card-header bg-sb">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold">
                    Katalog BOM
                </h5>
                <a href="{{ route('master-bom') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-reply"></i> Kembali ke List
                </a>
            </div>
        </div>
        <div class="card-body bg-light">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label"><small class="fw-bold">No Katalog BOM</small></label>
                    <input type="text" class="form-control" value="{{ $bom->no_katalog_bom }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><small class="fw-bold">Buyer</small></label>
                    <select class="form-control select2bs4" disabled>
                        <option value="">Pilih Buyer</option>
                        @foreach ($buyers as $buyer)
                            <option value="{{ $buyer->Id_Supplier }}" {{ $bom->id_buyer == $buyer->Id_Supplier ? 'selected' : '' }}>{{ $buyer->Supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><small class="fw-bold">Style</small></label>
                    <input type="text" class="form-control" value="{{ $bom->style }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><small class="fw-bold">Market</small></label>
                    <input type="text" class="form-control" value="{{ $bom->market }}" readonly>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-5">
                    <div class="form-label d-flex justify-content-between">
                        <small class="fw-bold">Master Color</small>
                    </div>
                    <select id="colorList" name="colors[]" class="form-control select2bs4" multiple>
                        @foreach ($master_colors as $color)
                            <option value="{{ $color->id }}" {{ is_array($selectedColors) && in_array($color->id, $selectedColors) ? 'selected' : '' }}>{{ $color->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <div class="form-label d-flex justify-content-between">
                        <small class="fw-bold">Master Size</small>
                    </div>
                    <select id="sizeList" name="sizes[]" class="form-control select2bs4" multiple>
                        @foreach ($master_sizes as $size)
                            <option value="{{ $size->id }}" {{ is_array($selectedSizes) && in_array($size->id, $selectedSizes) ? 'selected' : '' }}>{{ $size->size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-warning w-100 fw-bold" onclick="updateHeader()"><i class="fas fa-sync-alt"></i> Update Header</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3 card-success" id="section-list-item">
        <div class="card-header bg-sb">
            <h5 class="card-title fw-bold mb-0">  Tambah Item Baru</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><small class="fw-bold">Supplier</small></label>
                        <select name="id_supplier" class="form-control select2bs4">
                            <option value="">Pilih Supplier</option>
                            @foreach ($suppliers as $sup)
                                <option value="{{ $sup->Id_Supplier }}">{{ $sup->Supplier }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><small class="fw-bold">Category</small></label>
                        <select name="category" id="category" class="form-control select2bs4" onchange="get_item_contents()">
                            <option value="">Pilih Category</option>
                            <option value="Material">Material</option>
                            <option value="Manufacturing">Manufacturing</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><small class="fw-bold">Item Contents *</small></label>
                        <select name="item_contents" id="item_contents" class="form-control select2bs4" onchange="get_rule()" disabled>
                            <option value="">Pilih Kategori Terlebih Dahulu</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><small class="fw-bold">Rule BOM *</small></label>
                        <select name="rule_bom" id="rule_bom" class="form-control select2bs4" onchange="get_list_data()">
                            <option value="">Pilih Rule</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><small class="fw-bold">Unit</small></label>
                        <select name="unit" id="unit" class="form-control select2bs4">
                            <option value="">Pilih Unit</option>
                            @foreach ($master_units as $data)
                                <option value="{{ $data->nama_pilihan }}">{{ $data->nama_pilihan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><small class="fw-bold">Shell</small></label>
                        <select name="shell" id="shell" class="form-control select2bs4">
                            <option value="">Pilih Shell</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><small class="fw-bold">Currency</small></label>
                        <select name="currency" id="currency" class="form-control select2bs4">
                            <option value="">Pilih Currency</option>
                            @foreach ($master_currency as $data)
                                <option value="{{ $data->id }}">{{ $data->nama_pilihan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><small class="fw-bold">Notes</small></label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Input catatan..."></textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-end mt-4 mb-2">
                <label class="mb-0 fw-bold"><i class="fas fa-list"></i> Preview List Item</label>
                <button type="button" class="btn btn-sm btn-outline-danger" id="btn-hapus-batch-input" style="display:none;">
                    <i class="fas fa-trash-alt"></i> Hapus Terpilih (<span id="count-input-selected">0</span>)
                </button>
            </div>

            <div class="table-responsive border rounded">
                <table class="table table-bordered table-sm w-100 mb-0" id="itemTable">
                    <thead class="bg-light text-center">
                        <tr>
                            <th width="3%"><input type="checkbox" id="check-all-input"></th>
                            <th width="22%">Color | Size</th>
                            <th>Item</th>
                            <th width="15%">Cons</th>
                            <th width="15%">Price</th>
                            <th width="5%">Act</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-success btn-md">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</form>

<div class="card card-sb mt-3" id="section_table_list_item">
    <div class="card-header bg-sb">
        <h5 class="card-title fw-bold mb-0">BOM Detail Material & Manufacturing</h5>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-list"></i> Item BOM</h6>
            <div class="d-flex align-items-center">
                <button type="button" class="btn btn-info btn-sm shadow-sm mr-2" onclick="export_excel()">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
                <div id="delete-batch-container" style="display:none;">
                    <button type="button" class="btn btn-danger btn-sm shadow-sm" onclick="delete_batch()">
                        <i class="fas fa-trash"></i> Hapus Batch (<span id="count-selected">0</span>)
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered w-100" id="table-detail-bom">
                <thead class="bg-light">
                    <tr class="text-center">
                        <th width="2%"><input type="checkbox" id="check-all"></th>
                        <th width="5%">No</th>
                        <th width="10%">Category</th>
                        <th width="20%">Content</th>
                        <th width="20%">Item Description</th>
                        <th width="10%">Color</th>
                        <th width="5%">Size</th>
                        <th width="5%">Cons</th>
                        <th width="5%">Price</th>
                        <th width="5%">Currency</th>
                        <th width="10%">Unit</th>
                        <th width="5%">Shell</th>
                        <th width="10%">Action</th>
                    </tr>
                    <tr class="filter-row">
                        <th></th>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm column-search" data-column="2"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" data-column="3"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" data-column="4"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" data-column="5"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" data-column="6"></th>
                        <th></th>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm column-search" data-column="9"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" data-column="10"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" data-column="11"></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="card card-sb mt-3" id="section_table_other">
    <div class="card-header bg-sb">
        <h5 class="card-title fw-bold mb-0">  BOM Detail Other</h5>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-4 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-bold small">Pilih Item</label>
                <select name="id_item_others" id="id_item_others" class="form-control form-control-sm select2bs4">
                    <option value="">Pilih Item</option>
                    @foreach ($master_items_other as $other)
                        <option value="{{ $other->isi }}">{{ $other->tampil }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small">Price USD</label>
                <input type="number" name="price_usd" id="price_usd" class="form-control form-control-sm" placeholder="0.00" step="0.01">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small">Price IDR</label>
                <input type="number" name="price_idr" id="price_idr" class="form-control form-control-sm" placeholder="0">
            </div>
            <div class="col-md-auto">
                <button type="button" class="btn btn-sm btn-primary" id="btn-tambah-item" title="Tambah Item">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered w-100" id="table-detail-other">
                <thead class="table-light text-center">
                    <tr class="small">
                        <th width="40%">Description</th>
                        <th width="20%">Price USD</th>
                        <th width="20%">Price IDR</th>
                        <th width="5%">Action</th>
                    </tr>
                </thead>
                <tbody class="small">
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-edit-item" onsubmit="submit_edit_item(this, event)">
                @csrf
                <input type="hidden" name="id_detail" id="edit_id_detail">

                <div class="modal-header bg-sb text-light py-2">
                    <h6 class="modal-title"><i class="fas fa-edit"></i> Edit Item BOM</h6>
                    <button type="button" class="btn-close text-light" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <label><small class="fw-bold">Item Description</small></label>
                            <input type="text" id="edit_item_desc" class="form-control form-control-sm bg-light" readonly>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label><small class="fw-bold">Color</small></label>
                            <select name="id_color" id="edit_id_color" class="form-control select2bs4-edit" style="width: 100%;">
                                <option value="">Pilih Color</option>
                                @foreach ($master_colors as $color)
                                    <option value="{{ $color->id }}">{{ $color->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label><small class="fw-bold">Size</small></label>
                            <select name="id_size" id="edit_id_size" class="form-control select2bs4-edit" style="width: 100%;">
                                <option value="">Pilih Size</option>
                                @foreach ($master_sizes as $size)
                                    <option value="{{ $size->id }}">{{ $size->size }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label><small class="fw-bold">Qty</small></label>
                            <input type="number" step="0.0001" name="qty" id="edit_qty" class="form-control form-control-sm text-center qty_input" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label><small class="fw-bold">Unit</small></label>
                            <select name="unit" id="edit_unit" class="form-control select2bs4-edit" style="width: 100%;">
                                <option value="">Pilih Unit</option>
                                @foreach ($master_units as $data)
                                    <option value="{{ $data->nama_pilihan }}">{{ $data->nama_pilihan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                           <label><small class="fw-bold">Shell</small></label>
                            <select name="shell" id="edit_shell" class="form-control select2bs4-edit">
                                <option value="">Pilih Shell</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label><small class="fw-bold">Currency</small></label>
                            <select name="id_currency" id="edit_id_currency" class="form-control select2bs4-edit" style="width: 100%;">
                                <option value="">Pilih Currency</option>
                                @foreach ($master_currency as $data)
                                    <option value="{{ $data->id }}">{{ $data->nama_pilihan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label><small class="fw-bold">Price</small></label>
                            <input type="number" step="0.0001" name="price" id="edit_price" class="form-control form-control-sm text-center price_input" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    let bom_id = {{ $bom->id }};
    let table_add_item = null;

    $(document).ready(function() {
        $('.select2bs4').select2({ theme: 'bootstrap4' });

        load_items(bom_id);
        loadDataOther();

        $('#form-edit-bom').on('submit', function(e) {
            e.preventDefault();
            submit_form(this);
        });

        $(document).on('select2:open', () => {
            document.querySelector('.select2-container--open .select2-search__field').focus();
        });

        $('#item_contents, #rule_bom, #colorList, #sizeList').on('change', function() {
            get_list_data();
        });

        $(document).on('change', '.qty_input', function() {
            let val = parseFloat(this.value);
            if (isNaN(val) || val <= 0) {
                this.value = '';
                iziToast.warning({
                    title: 'Peringatan',
                    message: 'Qty tidak boleh 0 atau minus',
                    position: 'topRight'
                });
            } else {
                this.value = Math.round(val * 10000) / 10000;
            }
        });

        $(document).on('keypress', '.qty_input', function(e) {
            if (e.which == 45) { return false; }
        });

        $(document).on('click', '.btn-hapus-row', function() {
            $(this).closest('tr').remove();
        });

        $('#btn-tambah-item').on('click', function() {
            let id_item_others = $('#id_item_others').val();

            if (!id_item_others) {
                Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Item wajib dipilih terlebih dahulu!' });
                return;
            }

            let data = {
                _token: "{{ csrf_token() }}",
                id_bom_marketing: bom_id,
                id_item_others: id_item_others,
                price_usd: $('#price_usd').val(),
                price_idr: $('#price_idr').val(),
            };

            Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            $.ajax({
                url: "{{ route('bom.store_other') }}",
                type: "POST",
                data: data,
                success: function(res) {
                    $('#id_item_others').val('').trigger('change');
                    $('#price_usd').val('');
                    $('#price_idr').val('');

                    loadDataOther();

                    Swal.fire({
                        icon: 'success', title: 'Berhasil!',
                        text: res.message || 'Item berhasil ditambahkan.',
                        timer: 1500, showConfirmButton: false
                    });
                },
                error: function(err) {
                    let errorMsg = err.responseJSON ? err.responseJSON.message : "Terjadi kesalahan pada server.";
                    Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', text: errorMsg });
                }
            });
        });
    });

    function get_rule() {
        let id_contents = $('#item_contents').val();
        if (!id_contents) return $('#rule_bom').html('<option value="">Pilih Rule</option>');

        $.post("{{ route('get-rule-bom') }}", {
            _token: "{{ csrf_token() }}",
            id_contents: id_contents
        }, function(res) {
            $("#rule_bom").html(res).trigger('change');
            get_list_data();
        });
    }

    function get_list_data() {
        let id_contents = $('#item_contents').val();
        let category = $('#category').val();
        let rule = $('#rule_bom').val();

        let selected_colors = $('#colorList').select2('data');
        let selected_sizes = $('#sizeList').select2('data');

        if (!id_contents || !rule) {
            $("#itemTable tbody").empty();
            $('#check-all-input').prop('checked', false);
            $('#btn-hapus-batch-input').hide();
            return;
        }

        let proses = false;
        if (rule === "All Color All Size") proses = true;
        else if (rule === "All Color Range Size") proses = (selected_sizes.length > 0);
        else if (rule === "Per Color All Size") proses = (selected_colors.length > 0);
        else proses = (selected_colors.length > 0 && selected_sizes.length > 0);

        if (!proses) {
            $("#itemTable tbody").empty();
            $('#check-all-input').prop('checked', false);
            $('#btn-hapus-batch-input').hide();
            return;
        }

        $.post("{{ route('get-list-data-bom') }}", {
            _token: "{{ csrf_token() }}",
            id_contents: id_contents,
            category: category,
            id_bom: bom_id
        }, function(res) {
            let tbody = $("#itemTable tbody");
            tbody.empty();
            $('#check-all-input').prop('checked', false);
            $('#btn-hapus-batch-input').hide();

            let rows = [];
            if (rule === "All Color All Size") {
                rows.push({ label: "All Color | All Size", color_id: null, size_id: null });
            } else if (rule === "All Color Range Size") {
                selected_sizes.forEach(s => rows.push({ label: `All Color | ${s.text}`, color_id: null, size_id: s.id }));
            } else if (rule === "Per Color All Size") {
                selected_colors.forEach(c => rows.push({ label: `${c.text} | All Size`, color_id: c.id, size_id: null }));
            } else {
                selected_colors.forEach(c => {
                    selected_sizes.forEach(s => rows.push({ label: `${c.text} | ${s.text}`, color_id: c.id, size_id: s.id }));
                });
            }

            let existingMap = res.existing || {};

            rows.forEach((data, index) => {
                let idx = index + 1;

                let cIdStr = data.color_id !== null ? data.color_id : 'null';
                let sIdStr = data.size_id !== null ? data.size_id : 'null';
                let lookupKey = `${cIdStr}_${sIdStr}`;

                let savedData = existingMap[lookupKey];

                let selectedItemId = savedData ? savedData.id_item : null;
                let savedQty   = (savedData && savedData.qty > 0) ? parseFloat(savedData.qty).toFixed(4) : '';
                let savedPrice = (savedData && savedData.price > 0) ? parseFloat(savedData.price).toFixed(4) : '';

                let itemOptionsHtml = '<option value="">Pilih Item</option>';
                res.items.forEach(i => {
                    let isSelected = (i.isi == selectedItemId) ? 'selected' : '';
                    itemOptionsHtml += `<option value="${i.isi}" ${isSelected}>${i.tampil}</option>`;
                });

                // PENAMBAHAN CHECKBOX DAN TOMBOL HAPUS DI SINI
                let row = `<tr>
                    <td class="text-center align-middle">
                        <input type="checkbox" class="check-input-row">
                    </td>
                    <td class="align-middle">
                        <input type="hidden" name="id_color[${idx}]" value="${data.color_id ?? ''}">
                        <input type="hidden" name="id_size[${idx}]" value="${data.size_id ?? ''}">
                        <small class="fw-bold">${data.label}</small>
                    </td>
                    <td><select name="id_item[${idx}]" class="form-control select2-item">${itemOptionsHtml}</select></td>
                    <td><input type="number" step="0.0001" name="qty_input[${idx}]" class="form-control form-control-sm text-center qty_input" placeholder="0.0000" value="${savedQty}"></td>
                    <td><input type="number" step="0.0001" name="price_input[${idx}]" class="form-control form-control-sm text-center price_input" placeholder="0.0000" value="${savedPrice}"></td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm btn-danger btn-hapus-row" title="Hapus Baris Ini">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>`;
                tbody.append(row);
            });

            $('.select2-item').select2({ theme: 'bootstrap4', width: '100%' });
        });
    }


    function toggleBatchDeleteInput() {
        let checkedCount = $('.check-input-row:checked').length;
        if (checkedCount > 0) {
            $('#count-input-selected').text(checkedCount);
            $('#btn-hapus-batch-input').fadeIn(200);
        } else {
            $('#btn-hapus-batch-input').fadeOut(200);
        }
    }

    $('#check-all-input').on('click', function() {
        let isChecked = $(this).is(':checked');
        $('.check-input-row').prop('checked', isChecked);
        toggleBatchDeleteInput();
    });

    $('#itemTable').on('change', '.check-input-row', function() {
        let total = $('.check-input-row').length;
        let checked = $('.check-input-row:checked').length;
        $('#check-all-input').prop('checked', total === checked && total > 0);
        toggleBatchDeleteInput();
    });

    $('#btn-hapus-batch-input').on('click', function() {
        $('.check-input-row:checked').closest('tr').fadeOut(300, function() {
            $(this).remove();

            $('#check-all-input').prop('checked', false);
            toggleBatchDeleteInput();
        });
    });

    $(document).on('click', '.btn-hapus-row', function() {
        $(this).closest('tr').remove();
        toggleBatchDeleteInput();
    });

    function submit_form(form) {
        let content = $('#item_contents').val();
        let rule = $('#rule_bom').val();
        let supplier = $('select[name="id_supplier"]').val();

        if (!content || !rule || !supplier) {
            Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap!', text: 'Content, Rule BOM, dan Supplier wajib diisi.' });
            return false;
        }

        let rowCount = $("#itemTable tbody tr").length;
        if (rowCount === 0) {
            Swal.fire({ icon: 'warning', title: 'Item Kosong!', text: 'Silakan tambahkan minimal satu item ke dalam list.' });
            return false;
        }

        let isTableValid = true;
        $("#itemTable tbody tr").each(function(index) {
            let itemId = $(this).find('[name^="id_item"]').val();
            let qty    = $(this).find('[name^="qty_input"]').val();

            if (qty > 0 && (!itemId || itemId === "")) {
                isTableValid = false;
                $(this).addClass('table-danger');
            } else {
                $(this).removeClass('table-danger');
            }
        });

        if (!isTableValid) {
            Swal.fire({ icon: 'warning', title: 'Data Tidak Valid', text: 'Terdapat item yang belum dipilih padahal Qty terisi.' });
            return false;
        }

        Swal.fire({
            title: 'Simpan Item BOM?',
            html: "Item ini akan ditambahkan/diperbarui ke list bawah.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Ya, Simpan'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                let formData = new FormData(form);
                formData.append('id_bom_marketing', bom_id);

                $.ajax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false, contentType: false,
                    success: function(res) {
                        if (res.status == 200) {
                            Swal.fire({ icon: 'success', title: 'Berhasil!', timer: 1500, showConfirmButton: false });

                            $('#item_contents, #rule_bom, #unit, #shell, select[name="id_supplier"]').val('').trigger('change');
                            $(form).find('textarea[name="notes"]').val('');
                            $("#itemTable tbody").empty();

                            load_items(bom_id);
                        } else {
                            Swal.fire('Gagal!', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', 'Terjadi kesalahan sistem', 'error');
                    }
                });
            }
        });
    }

    function load_items(id_header) {
        if (!id_header) return;

        let url = "{{ route('get-items', ':id') }}";
        url = url.replace(':id', id_header);

        if ($.fn.DataTable.isDataTable('#table-detail-bom')) {
            table_add_item.ajax.url(url).load(null, false);
            return;
        }

        table_add_item = $('#table-detail-bom').DataTable({
            processing: true, serverSide: true, destroy: true,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            pageLength: 10, ajax: url,
            columns: [
                {
                    data: 'id', orderable: false, searchable: false, className: 'text-center align-middle',
                    render: data => `<input type="checkbox" class="row-checkbox" value="${data}">`
                },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'category', name: 'category', className: 'text-center' },
                { data: 'content_name', name: 'content_name', render: data => data ? data : '-' },
                { data: 'item_name', name: 'item_name' },
                { data: 'color_name', name: 'color_name', className: 'text-center' },
                { data: 'size_name', name: 'size_name', className: 'text-center' },
                {
                    data: 'qty', name: 'qty', className: 'text-center', searchable: false,
                    render: function(data) {
                        let nilai = parseFloat(data);
                        return isNaN(nilai) ? '0.00' : nilai.toFixed(2);
                    }
                },
                {
                    data: 'price', name: 'price', className: 'text-center', searchable: false,
                    render: function(data) {
                        let nilai = parseFloat(data);
                        return isNaN(nilai) ? '0.00' : nilai.toFixed(2);
                    }
                },
                { data: 'currency', name: 'currency', className: 'text-center' },
                { data: 'unit', name: 'unit', className: 'text-center' },
                { data: 'shell', name: 'shell', className: 'text-center' },
                {
                    data: 'id', orderable: false, searchable: false, className: 'text-center align-middle',
                    render: data => `
                        <div class="d-flex justify-content-center" style="gap: 5px;">
                            <button type="button" class="btn btn-sm btn-info py-1 px-2"
                                    onclick="edit_item_row(${data})" title="Edit Item">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>`
                }
            ],
            drawCallback: function() {
                $('#check-all').prop('checked', false);
                if (typeof handle_delete_button === "function") { handle_delete_button(); }
            },
            language: {
                emptyTable: "Belum ada item yang ditambahkan.",
                processing: '<i class="fa fa-spinner fa-spin fa-fw"></i> Menampilkan data...'
            },
            autoWidth: false, responsive: true, orderCellsTop: true
        });

        $('#table-detail-bom thead').on('keyup change clear', '.column-search', function() {
            let colIdx = $(this).data('column');
            table_add_item.column(colIdx).search(this.value).draw();
        });
    }

    function edit_item_row(id) {
        let url = "{{ route('get-item-row-bom', ':id') }}";
        url = url.replace(':id', id);

        $.get(url, function(res) {
            $('#edit_id_detail').val(res.id);
            $('#edit_item_desc').val(res.item_name);
            $('#edit_id_color').val(res.id_color).trigger('change');
            $('#edit_id_size').val(res.id_size).trigger('change');
            $('#edit_qty').val(res.qty || 0);
            $('#edit_price').val(res.price || 0);
            $('#edit_id_currency').val(res.id_currency).trigger('change');
            $('#edit_unit').val(res.unit).trigger('change');
            $('#edit_shell').val(res.shell).trigger('change');

            $('.select2bs4-edit').select2({ theme: 'bootstrap4', dropdownParent: $('#modalEdit') });
            $('#modalEdit').modal('show');
        });
    }

    function submit_edit_item(form, evt) {
        evt.preventDefault();
        let id = $('#edit_id_detail').val();
        if(!id) return;

        Swal.fire({
            title: 'Konfirmasi Update',
            html: `Apakah Anda yakin ingin mengubah data item ini?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="fa fa-save"></i> Ya',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Sedang memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                let url = "{{ route('update-item-row-bom', ':id') }}";
                url = url.replace(':id', id);

                $.ajax({
                    url: url, type: 'POST', data: $(form).serialize(),
                    success: function(res) {
                        if (res.status == 200) {
                            $('#modalEdit').modal('hide');
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'item berhasil diedit.', timer: 1500, showConfirmButton: false });
                            table_add_item.ajax.reload(null, false);
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Terjadi kesalahan sistem saat menghubungi server.', 'error');
                    }
                });
            }
        });
    }

    $('#check-all').on('click', function() {
        let isChecked = $(this).is(':checked');
        $('#table-detail-bom tbody').find('.row-checkbox').prop('checked', isChecked);
        handle_delete_button();
    });

    $('#table-detail-bom').on('change', '.row-checkbox', function() {
        let total_row = $('#table-detail-bom tbody').find('.row-checkbox').length;
        let total_checked = $('#table-detail-bom tbody').find('.row-checkbox:checked').length;
        $('#check-all').prop('checked', (total_checked === total_row && total_row > 0));
        handle_delete_button();
    });

    function handle_delete_button() {
        let checked_count = $('.row-checkbox:checked').length;
        let container = $('#delete-batch-container');
        if (checked_count > 0) {
            $('#count-selected').text(checked_count);
            container.fadeIn(200);
        } else {
            container.fadeOut(200);
            $('#check-all').prop('checked', false);
        }
    }

    function delete_batch() {
        let ids = [];
        $('.row-checkbox:checked').each(function() { ids.push($(this).val()); });
        if (ids.length === 0) return;

        Swal.fire({
            title: 'Hapus Batch?',
            text: `Anda akan menghapus ${ids.length} item secara permanen`,
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus', reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                $.ajax({
                    url: "{{ route('delete-batch-bom') }}", type: 'POST',
                    data: { _token: "{{ csrf_token() }}", ids: ids },
                    success: function(res) {
                        if (res.status == 200) {
                            Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.message, timer: 1500, showConfirmButton: false });
                            $('#check-all').prop('checked', false);
                            handle_delete_button();
                            if ($.fn.DataTable.isDataTable('#table-detail-bom')) {
                                $('#table-detail-bom').DataTable().ajax.reload(null, false);
                            }
                        } else { Swal.fire('Gagal!', res.message, 'error'); }
                    },
                    error: function() { Swal.fire('Error!', 'Terjadi kesalahan pada server.', 'error'); }
                });
            }
        });
    }

    async function export_excel() {
        Swal.fire({ title: "Exporting", html: "Mohon Tunggu...", timerProgressBar: true, didOpen: () => { Swal.showLoading(); } });
        try {
            const res = await $.ajax({
                url: '{{ route('export-excel-bom') }}', type: "GET", data: { id : bom_id }, xhrFields: { responseType: 'blob' }
            });

            Swal.close();
            iziToast.success({ title: 'Success', message: 'Success', position: 'topCenter' });

            const blob = new Blob([res]);
            const link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = "Laporan Marketing BOM Item.xlsx";
            link.click();
        } catch (err) {
            Swal.close();
            iziToast.error({ title: 'Error', message: 'Export gagal', position: 'topCenter' });
        }
    }

    function get_item_contents() {
        let kategori = $('#category').val();

        if (!kategori) {
            $('#item_contents').html('<option value="">Pilih Kategori Terlebih Dahulu</option>').prop('disabled', true);
            $('#rule_bom').html('<option value="">Pilih Rule</option>');
            $("#itemTable tbody").empty();
            return;
        }

        $('#item_contents').html('<option value="">Memuat data...</option>').prop('disabled', true);

        $.post("{{ route('get-item-contents-bom') }}", {
            _token: "{{ csrf_token() }}", kategori: kategori
        }, function(res) {
            $('#item_contents').html(res).prop('disabled', false).trigger('change');
            $('#rule_bom').html('<option value="">Pilih Rule</option>');
            $("#itemTable tbody").empty();
        }).fail(function() {
            Swal.fire('Error', 'Gagal mengambil data item contents', 'error');
            $('#item_contents').html('<option value="">Pilih Kategori Terlebih Dahulu</option>').prop('disabled', true);
        });
    }

    function loadDataOther() {
        let id_bom = bom_id;
        let url = "{{ route('bom.get_other', ':id') }}";
        url = url.replace(':id', id_bom);

        $.ajax({
            url: url,
            type: "GET", dataType: "json",
            success: function(data) {
                let row = '';
                $('#table-detail-other tbody').empty();

                $.each(data, function(key, val) {
                    row += `<tr>
                        <td>${val.tampil}</td>
                        <td class="text-end">${parseFloat(val.price_usd).toFixed(4)}</td>
                        <td class="text-end">${parseInt(val.price_idr).toLocaleString('id-ID')}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-xs btn-danger" onclick="deleteOther(${val.id})" title="Hapus Item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                });
                $('#table-detail-other tbody').append(row);
            }
        });
    }

    function deleteOther(id) {
        Swal.fire({
            title: 'Hapus Item?', text: "Data yang dihapus tidak dapat dikembalikan!", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus', cancelButtonText: 'Batal', reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                let url = "{{ route('bom.destroy_other', ':id') }}";
                url = url.replace(':id', id);

                $.ajax({
                    url: url, type: "DELETE",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        loadDataOther();
                        Swal.fire({ icon: 'success', title: 'Terhapus!', text: 'Item berhasil dihapus.', timer: 1500, showConfirmButton: false });
                    },
                    error: function() {
                        Swal.fire({ icon: 'error', title: 'Oops...', text: 'Terjadi kesalahan saat menghapus data.' });
                    }
                });
            }
        });
    }

   function updateHeader() {
        Swal.fire({ title: 'Memperbarui Master BOM...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        let data = {
            _token: "{{ csrf_token() }}",
            id_bom_marketing: bom_id,
            colors: $('#colorList').val(),
            sizes: $('#sizeList').val(),
        };

        console.log(data);

        $.ajax({
            url: "{{ route('update-bom-header') }}",
            type: "POST",
            data: data,
            success: function(res) {
                if (res.status == 200) {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false });
                    get_list_data();
                } else {
                    Swal.fire('Gagal!', res.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error!', 'Terjadi kesalahan sistem', 'error');
            }
        });
    }
</script>
@endsection
