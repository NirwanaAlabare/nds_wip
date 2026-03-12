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

        table.dataTable thead th { padding-right: 8px !important; }
        .table-po-wrapper { max-height: 250px; overflow-y: auto; }
        .table-po-wrapper thead th { position: sticky; top: 0; background: #f8f9fa; z-index: 1; }
    </style>
@endsection

@section('content')
<div class="card card-primary">
    <div class="card-header bg-sb">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold">
                Katalog BOM Additional
            </h5>
            <a href="{{ route('master-bom-additional') }}" class="btn btn-sm btn-primary">
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
                <label class="form-label"><small class="fw-bold">No SO</small></label>
                <input type="text" class="form-control" value="{{ $bom->so_no }}" readonly>
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

        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0"><small class="fw-bold text-info"><i class="fas fa-check-square"></i> Checklist PO untuk BOM Additional</small></label>
                </div>
                <div class="table-responsive border rounded table-po-wrapper">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center"><input type="checkbox" id="check-all-po" style="transform: scale(1.2);"></th>
                                <th>No PO</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th class="text-right">Total Qty Gmt</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-po">
                            <tr><td colspan="5" class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Memuat data PO...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <label class="form-label mb-0"></label>
                    <button type="button" class="btn btn-warning btn-sm shadow-sm fw-bold" onclick="syncPO()">
                        <i class="fas fa-sync"></i> Simpan Perubahan PO
                    </button>
                </div>
            </div>
        </div>

        <div class="row mt-3" style="display: none;">
            <div class="col-md-6"><select id="colorList" name="colors[]" class="form-control select2bs4" multiple></select></div>
            <div class="col-md-6"><select id="sizeList" name="sizes[]" class="form-control select2bs4" multiple></select></div>
        </div>
    </div>
</div>

<div class="card mt-3 card-success">
    <div class="card-header bg-sb">
        <h5 class="card-title fw-bold mb-0">  Tambah Item</h5>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <form id="form-bom" action="{{ route('store-bom-additional-item') }}" method="POST">
            @csrf
            <input type="hidden" name="id_bom_additional" value="{{ $bom->id }}">
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
                            @foreach ($masterUnits as $data)
                                <option value="{{ $data->id }}">{{ $data->nama_pilihan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><small class="fw-bold">Shell</small></label>
                        <select name="shell" id="shell" class="form-control select2bs4">
                            <option value="">Pilih Shell</option>
                            <option value="A">A</option><option value="B">B</option><option value="C">C</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><small class="fw-bold">Notes</small></label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Input catatan..."></textarea>
                    </div>
                </div>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-bordered table-sm w-100" id="itemTable">
                    <thead class="bg-light text-center">
                        <tr>
                            <th width="25%">Color | Size</th>
                            <th>Item Material</th>
                            <th width="15%">Cons</th>
                            <th width="15%">Price</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-success btn-md"><i class="fas fa-save"></i> Simpan Item</button>
            </div>
        </form>
    </div>
</div>

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
            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-list"></i> List Item</h6>
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
                        <th width="15%">Category</th>
                        <th width="20%">Content</th>
                        <th width="20%">Item Description</th>
                        <th width="10%">Color</th>
                        <th width="5%">Size</th>
                        <th width="5%">Cons</th>
                        <th width="5%">Price</th>
                        <th width="10%">Unit</th>
                        <th width="5%">Shell</th>
                        <th width="5%">Action</th>
                    </tr>
                    <tr class="filter-row">
                        <th></th><th></th>
                        <th><input type="text" class="form-control form-control-sm column-search" data-column="2"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" data-column="3"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" data-column="4"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" data-column="5"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" data-column="6"></th>
                        <th></th>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm column-search" data-column="9"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" data-column="10"></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
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
                            <label><small class="fw-bold">Qty / Cons</small></label>
                            <input type="number" step="0.0001" name="qty" id="edit_qty" class="form-control form-control-sm text-center qty_input" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label><small class="fw-bold">Unit</small></label>
                            <select name="id_unit" id="edit_id_unit" class="form-control select2bs4-edit" style="width: 100%;">
                                <option value="">Pilih Unit</option>
                                @foreach ($masterUnits as $data)
                                    <option value="{{ $data->id }}">{{ $data->nama_pilihan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label><small class="fw-bold">Shell</small></label>
                            <select name="shell" id="edit_shell" class="form-control select2bs4-edit" style="width: 100%;">
                                <option value="">Pilih Shell</option>
                                <option value="A">A</option><option value="B">B</option><option value="C">C</option>
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
    let id_so = "{{ $bom->id_so }}";
    let saved_pos = @json($saved_pos ?? []); // Array PO dari controller
    let table_add_item = null;

    // Helper Global untuk format decimal yang aman dari null/NaN
    function formatDecimal(nilai, presisi = 2) {
        let n = parseFloat(nilai);
        return isNaN(n) ? (0).toFixed(presisi) : n.toFixed(presisi);
    }

    $(document).ready(function() {
        $('.select2bs4').select2({ theme: 'bootstrap4' });

        // Load data saat halaman dibuka
        loadPOList();
        load_items(bom_id);

        // Fix select2 autofocus di modal
        $(document).on('select2:open', () => {
            document.querySelector('.select2-container--open .select2-search__field').focus();
        });

        // Event Handling PO Checklist
        $('#check-all-po').on('change', function() {
            $('.check-po').prop('checked', this.checked).trigger('change');
        });
        $(document).on('change', '.check-po', function() {
            updateColorSizeDropdowns();
        });

        // Trigger Generate Form Table Input Item
        $('#item_contents, #rule_bom, #colorList, #sizeList').on('change', function() {
            get_list_data();
        });

        // Event Form Utama Simpan Item
        $('#form-bom').on('submit', function(e) {
            e.preventDefault();
            submitMainForm(this);
        });

        // Format angka desimal saat user mengetik Qty
        $(document).on('change', '.qty_input', function() {
            let val = parseFloat(this.value);
            if (isNaN(val) || val <= 0) {
                this.value = '';
            } else {
                this.value = val;
            }
        });
        $(document).on('keypress', '.qty_input', function(e) {
            if (e.which == 45) return false; // Mencegah minus
        });
    });

    function loadPOList() {
        if (!id_so) return;
        $.ajax({
            url: "{{ route('get-po-by-so') }}", type: "GET", data: { id_so: id_so },
            success: function(res) {
                let htmlPO = '';
                if(res.po_list.length > 0) {
                    res.po_list.forEach(function(po) {
                        let uniqueVal = `${po.no_po}_${po.id_color}_${po.id_size}`;
                        let isChecked = saved_pos.includes(uniqueVal) ? 'checked' : '';
                        htmlPO += `<tr>
                            <td class="text-center">
                                <input type="checkbox" name="checked_po[]" value="${uniqueVal}" class="check-po"
                                    data-color-id="${po.id_color}" data-color-name="${po.color_name}"
                                    data-size-id="${po.id_size}" data-size-name="${po.size_name}" ${isChecked}>
                            </td>
                            <td>${po.no_po}</td><td>${po.color_name}</td><td>${po.size_name}</td>
                            <td class="text-right">${po.qty}</td>
                        </tr>`;
                    });
                } else {
                    htmlPO = `<tr><td colspan="5" class="text-center text-danger">Tidak ada PO ditemukan.</td></tr>`;
                }
                $('#tbody-po').html(htmlPO);
                updateColorSizeDropdowns();
            }
        });
    }
    function updateColorSizeDropdowns() {
        let selColors = new Map(), selSizes = new Map();
        $('.check-po:checked').each(function() {
            let cId = $(this).data('color-id'), cName = $(this).data('color-name');
            let sId = $(this).data('size-id'), sName = $(this).data('size-name');
            if(cId) selColors.set(cId, cName);
            if(sId) selSizes.set(sId, sName);
        });

        let $col = $('#colorList').empty(), $siz = $('#sizeList').empty();
        selColors.forEach((n, i) => $col.append(new Option(n, i, true, true)));
        selSizes.forEach((n, i) => $siz.append(new Option(n, i, true, true)));
        $col.trigger('change'); $siz.trigger('change');
    }

    function syncPO() {
        let po_array = [];
        $('.check-po:checked').each(function() { po_array.push($(this).val()); });

        if (po_array.length === 0) return Swal.fire('Peringatan', 'Minimal pilih 1 PO!', 'warning');

        Swal.fire({
            title: 'Update PO?',
            text: "Daftar PO pada sistem akan diperbarui.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Update'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
                $.post("{{ route('bom-add.update-po') }}", {
                    _token: "{{ csrf_token() }}", id_bom_additional: bom_id, po_list: po_array
                }, function(res) {
                    if (res.status == 200) {
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                        saved_pos = po_array;

                        // OTOMATIS REFRESH TABEL INPUT ITEM (JIKA ITEM & RULE SUDAH DIPILIH)
                        if ($('#item_contents').val() && $('#rule_bom').val()) {
                            get_list_data();
                        }

                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                });
            }
        });
    }

    function get_item_contents() {
        let kategori = $('#category').val();
        let itemContentsSelect = $('#item_contents');

        if (!kategori) {
            itemContentsSelect.html('<option value="">Pilih Kategori Terlebih Dahulu</option>').prop('disabled', true);
            $('#rule_bom').html('<option value="">Pilih Rule</option>');
            $("#itemTable tbody").empty();
            return;
        }

        itemContentsSelect.html('<option value="">Memuat data...</option>').prop('disabled', true);

        $.post("{{ route('get-item-contents-bom') }}", {
            _token: "{{ csrf_token() }}",
            kategori: kategori
        }, function(res) {
            itemContentsSelect.html(res).prop('disabled', false).trigger('change');
            $('#rule_bom').html('<option value="">Pilih Rule</option>');
            $("#itemTable tbody").empty();
        }).fail(function() {
            Swal.fire('Error', 'Gagal mengambil data item contents', 'error');
            itemContentsSelect.html('<option value="">Pilih Kategori Terlebih Dahulu</option>').prop('disabled', true);
        });
    }

    function get_rule() {
        let id_contents = $('#item_contents').val();
        if (!id_contents) return $('#rule_bom').html('<option value="">Pilih Rule</option>');

        $.post("{{ route('get-rule-bom') }}", {
            _token: "{{ csrf_token() }}", id_contents: id_contents
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

        if (!id_contents || !rule) { $("#itemTable tbody").empty(); return; }

        // PERBAIKAN: Ambil kombinasi (Color + Size) asli langsung dari PO yang dicentang
        let actual_combinations = [];
        $('.check-po:checked').each(function() {
            let cId = $(this).data('color-id');
            let cName = $(this).data('color-name');
            let sId = $(this).data('size-id');
            let sName = $(this).data('size-name');

            // Buat key unik agar tidak dobel jika ada PO beda tapi warna & size sama
            let comboKey = cId + '_' + sId;
            let exists = actual_combinations.find(combo => combo.key === comboKey);
            if(!exists) {
                actual_combinations.push({ key: comboKey, cId: cId, cName: cName, sId: sId, sName: sName });
            }
        });

        let proses = false;
        if (rule === "All Color All Size") proses = true;
        else if (rule === "All Color Range Size") proses = (selected_sizes.length > 0);
        else if (rule === "Per Color All Size") proses = (selected_colors.length > 0);
        else proses = (actual_combinations.length > 0); // Validasi pakai kombinasi asli

        if (!proses) { $("#itemTable tbody").empty(); return; }

        $.post("{{ route('get-list-data-bom') }}", {
            _token: "{{ csrf_token() }}", id_contents: id_contents, category: category
        }, function(res) {
            let tbody = $("#itemTable tbody");
            tbody.empty();
            let rows = [];

            if (rule === "All Color All Size") {
                rows.push({ label: "All Color | All Size", cId: null, sId: null });
            } else if (rule === "All Color Range Size") {
                selected_sizes.forEach(s => rows.push({ label: `All Color | ${s.text}`, cId: null, sId: s.id }));
            } else if (rule === "Per Color All Size") {
                selected_colors.forEach(c => rows.push({ label: `${c.text} | All Size`, cId: c.id, sId: null }));
            } else {
                // PERBAIKAN: Gunakan actual_combinations, BUKAN hasil perkalian silang
                actual_combinations.forEach(combo => {
                    rows.push({ label: `${combo.cName} | ${combo.sName}`, cId: combo.cId, sId: combo.sId });
                });
            }

            let itemOptions = '<option value="">Pilih Item</option>';
            res.items.forEach(i => itemOptions += `<option value="${i.isi}">${i.tampil}</option>`);

            rows.forEach((data, index) => {
                let idx = index + 1;
                let row = `<tr>
                    <td class="align-middle">
                        <input type="hidden" name="id_color[${idx}]" value="${data.cId ?? ''}">
                        <input type="hidden" name="id_size[${idx}]" value="${data.sId ?? ''}">
                        <small class="fw-bold">${data.label}</small>
                    </td>
                    <td><select name="id_item[${idx}]" class="form-control select2-item">${itemOptions}</select></td>
                    <td><input type="number" step="0.0001" name="qty_input[${idx}]" class="form-control form-control-sm text-center qty_input" placeholder="0.0000"></td>
                    <td><input type="number" step="0.0001" name="price_input[${idx}]" class="form-control form-control-sm text-center price_input" placeholder="0.0000"></td>
                </tr>`;
                tbody.append(row);
            });
            $('.select2-item').select2({ theme: 'bootstrap4', width: '100%' });
        });
    }

    function submitMainForm(form) {
        let content = $('#item_contents').val();
        let rule = $('#rule_bom').val();
        let supplier = $('select[name="id_supplier"]').val();

        if (!content || !rule || !supplier) {
            return Swal.fire('Data Belum Lengkap!', 'Content, Rule BOM, dan Supplier wajib diisi.', 'warning');
        }

        if ($("#itemTable tbody tr").length === 0) {
            return Swal.fire('Item Kosong!', 'Silakan tambahkan minimal satu item ke dalam list.', 'warning');
        }

        let isTableValid = true;
        $("#itemTable tbody tr").each(function() {
            let itemId = $(this).find('[name^="id_item"]').val();
            let qty = $(this).find('[name^="qty_input"]').val();
            if (!itemId || itemId === "" || !qty || parseFloat(qty) <= 0) {
                isTableValid = false;
                $(this).addClass('table-danger');
            } else {
                $(this).removeClass('table-danger');
            }
        });

        if (!isTableValid) {
            return Swal.fire('Data Tidak Valid', 'Terdapat item yang belum dipilih atau quantity kosong/0.', 'warning');
        }

        Swal.fire({
            title: 'Simpan Item Material?', icon: 'question',
            showCancelButton: true, confirmButtonColor: '#28a745', confirmButtonText: 'Ya, Simpan'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});

                let formData = new FormData(form);
                let colors = $('#colorList').val();
                let sizes = $('#sizeList').val();

                if(colors) colors.forEach(id => formData.append('colors[]', id));
                if(sizes) sizes.forEach(id => formData.append('sizes[]', id));

                $.ajax({
                    url: $(form).attr('action'), type: 'POST', data: formData, processData: false, contentType: false,
                    success: function(res) {
                        if (res.status == 200) {
                            Swal.fire({ icon: 'success', title: 'Berhasil!', timer: 1500, showConfirmButton: false });
                            $('#category, #item_contents, #rule_bom, #unit, #shell, select[name="id_supplier"]').val('').trigger('change');
                            $(form).find('textarea[name="notes"]').val('');
                            $("#itemTable tbody").empty();
                            table_add_item.ajax.reload(null, false);
                        } else Swal.fire('Gagal!', res.message, 'error');
                    },
                    error: function() { Swal.fire('Error!', 'Terjadi kesalahan sistem', 'error'); }
                });
            }
        });
    }

    function load_items(id_header) {
        let url = "{{ route('get-items-additional', ':id') }}".replace(':id', id_header);
        table_add_item = $('#table-detail-bom').DataTable({
            processing: true, serverSide: true, destroy: true,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]], pageLength: 10,
            ajax: url,
            columns: [
                { data: 'id', orderable: false, searchable: false, className: 'text-center align-middle', render: data => `<input type="checkbox" class="row-checkbox" value="${data}">` },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'category', name: 'category', className: 'text-center' },
                { data: 'content_name', name: 'content_name', render: data => data ? data : '-' },
                { data: 'item_name', name: 'i.itemdesc' },
                { data: 'color_name', name: 'color_name', className: 'text-center' },
                { data: 'size_name', name: 'size_name', className: 'text-center' },
                { data: 'qty', name: 'qty', className: 'text-center', searchable: false, render: data => formatDecimal(data) },
                { data: 'price', name: 'price', className: 'text-center', searchable: false, render: data => formatDecimal(data) },
                { data: 'unit_name', name: 'unit_name', className: 'text-center' },
                { data: 'shell', name: 'shell', className: 'text-center' },
                { data: 'id', orderable: false, searchable: false, className: 'text-center align-middle',
                  render: data => `<button type="button" class="btn btn-sm btn-info py-1 px-2" onclick="edit_item_row(${data})"><i class="fas fa-edit"></i></button>` }
            ],
            drawCallback: function() {
                $('#check-all').prop('checked', false);
                handle_delete_button();
            },
            autoWidth: false, responsive: true, orderCellsTop: true
        });

        $('#table-detail-bom thead').on('keyup change clear', '.column-search', function() {
            table_add_item.column($(this).data('column')).search(this.value).draw();
        });
    }

    function edit_item_row(id) {
        $.get("{{ route('get-item-row-bom-additional', ':id') }}".replace(':id', id), function(res) {
            $('#edit_id_detail').val(res.id);
            $('#edit_item_desc').val(res.item_name);
            $('#edit_id_color').val(res.id_color).trigger('change');
            $('#edit_id_size').val(res.id_size).trigger('change');

            let qtyValue = parseFloat(res.qty);
            $('#edit_qty').val(isNaN(qtyValue) ? 0 : qtyValue);

            let priceValue = parseFloat(res.price);
            $('#edit_price').val(isNaN(priceValue) ? 0 : priceValue);

            $('#edit_id_unit').val(res.id_unit).trigger('change');
            $('#edit_shell').val(res.shell).trigger('change');

            $('.select2bs4-edit').select2({ theme: 'bootstrap4', dropdownParent: $('#modalEdit') });
            $('#modalEdit').modal('show');
        });
    }

    function submit_edit_item(form, evt) {
        evt.preventDefault();
        Swal.fire({
            title: 'Konfirmasi Update', html: `Apakah Anda yakin ingin mengubah data item ini?`, icon: 'question',
            showCancelButton: true, confirmButtonColor: '#28a745', confirmButtonText: '<i class="fa fa-save"></i> Ya'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Sedang memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
                $.ajax({
                    url: "{{ route('update-item-row-bom-additional', ':id') }}".replace(':id', $('#edit_id_detail').val()),
                    type: 'POST', data: $(form).serialize(),
                    success: function(res) {
                        if (res.status == 200) {
                            $('#modalEdit').modal('hide');
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'item berhasil diedit.', timer: 1500, showConfirmButton: false });
                            table_add_item.ajax.reload(null, false);
                        } else Swal.fire('Gagal', res.message, 'error');
                    },
                    error: function() { Swal.fire('Error', 'Terjadi kesalahan sistem saat menghubungi server.', 'error'); }
                });
            }
        });
    }

    function handle_delete_button() {
        let checked_count = $('.row-checkbox:checked').length;
        if (checked_count > 0) {
            $('#count-selected').text(checked_count);
            $('#delete-batch-container').fadeIn(200);
        } else {
            $('#delete-batch-container').fadeOut(200);
            $('#check-all').prop('checked', false);
        }
    }

    function delete_batch() {
        let ids = [];
        $('.row-checkbox:checked').each(function() { ids.push($(this).val()); });
        if (ids.length === 0) return;

        Swal.fire({
            title: 'Hapus Batch?', text: `Hapus ${ids.length} item permanen?`, icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus', reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                $.post("{{ route('delete-batch-bom-additional') }}", { _token: "{{ csrf_token() }}", ids: ids }, function(res) {
                    if (res.status == 200) {
                        Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.message, timer: 1500, showConfirmButton: false });
                        $('#check-all').prop('checked', false);
                        handle_delete_button();
                        table_add_item.ajax.reload(null, false);
                    } else Swal.fire('Gagal!', res.message, 'error');
                });
            }
        });
    }

    async function export_excel() {
        Swal.fire({ title: "Exporting", html: "Mohon Tunggu...", timerProgressBar: true, didOpen: () => { Swal.showLoading(); }});
        try {
            const res = await $.ajax({
                url: '{{ route('export-excel-bom-additional') }}', type: "GET", data: { id : bom_id }, xhrFields: { responseType: 'blob' }
            });
            Swal.close();
            const link = document.createElement('a');
            link.href = window.URL.createObjectURL(new Blob([res]));
            link.download = "Laporan BOM Additional.xlsx";
            link.click();
        } catch (err) {
            Swal.fire('Error', 'Export gagal', 'error');
        }
    }
</script>
@endsection
