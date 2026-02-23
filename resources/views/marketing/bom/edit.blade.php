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
    </style>
@endsection

@section('content')
<form id="form-bom" action="{{ route('store-bom') }}" method="POST">
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
                <div class="col-md-4">
                    <label class="form-label"><small class="fw-bold">Buyer</small></label>
                    <select name="buyer" class="form-control select2bs4" disabled>
                        <option value="">Pilih Buyer</option>
                        @foreach ($buyers as $buyer)
                            <option value="{{ $buyer->Id_Supplier }}" {{ $bom->buyer == $buyer->Id_Supplier ? 'selected' : '' }}>
                                {{ $buyer->Supplier }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><small class="fw-bold">Style</small></label>
                    <input type="text" class="form-control" name="style" id="style" value="{{ $bom->style }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><small class="fw-bold">Market</small></label>
                    <input type="text" class="form-control" name="market" id="market" value="{{ $bom->market }}" readonly>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="form-label d-flex justify-content-between">
                        <small class="fw-bold">Master Color</small>
                    </div>
                    <select id="colorList" name="colors[]" class="form-control select2bs4" multiple disabled>
                        @foreach ($master_colors as $color)
                            <option value="{{ $color->id }}" {{ is_array($selectedColors) && in_array($color->id, $selectedColors) ? 'selected' : '' }}>
                                {{ $color->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <div class="form-label d-flex justify-content-between">
                        <small class="fw-bold">Master Size</small>
                    </div>
                    <select id="sizeList" name="sizes[]" class="form-control select2bs4" multiple disabled>
                        @foreach ($master_sizes as $size)
                            <option value="{{ $size->id }}" {{ is_array($selectedSizes) && in_array($size->id, $selectedSizes) ? 'selected' : '' }}>
                                {{ $size->size }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3 card-success" id="section-list-item">
        <div class="card-header bg-sb text-light">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-plus-circle"></i> Tambah Item Baru</h5>
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
                        <label><small class="fw-bold">Item Contents *</small></label>
                        <select name="item_contents" id="item_contents" class="form-control select2bs4" onchange="getRule()">
                            <option value="">Pilih Item Contents</option>
                            @foreach ($itemContents as $item)
                                <option value="{{ $item->isi }}">{{ $item->tampil }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><small class="fw-bold">Rule BOM *</small></label>
                        <select name="rule_bom" id="rule_bom" class="form-control select2bs4" onchange="getListData()">
                            <option value="">Pilih Rule</option>
                        </select>
                    </div>
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
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><small class="fw-bold">Notes</small></label>
                        <textarea name="notes" class="form-control" rows="7" placeholder="Input catatan..."></textarea>
                    </div>
                </div>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-bordered table-sm w-100" id="itemTable">
                    <thead class="bg-light text-center">
                        <tr>
                            <th width="25%">Color | Size</th>
                            <th>Item</th>
                            <th width="15%">Cons</th>
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

    <div class="card card-outline card-info mt-3" id="section_table_list_item">
        <div class="card-header">
            <h5 class="card-title fw-bold text-info"><i class="fas fa-clipboard-list"></i> BOM Detail</h5>
        </div>
        <div class="card-body">
            <table class="table table-sm table-striped table-bordered mb-0" id="table-detail-bom" style="width: 100%;">
                <thead>
                    <tr class="text-center bg-light">
                        <th width="5%">No</th>
                        <th width="20%">Content</th>
                        <th>Item Description</th>
                        <th width="15%">Color</th>
                        <th width="15%">Size</th>
                        <th width="10%">Cons</th>
                        <th width="10%">Unit</th>
                    </tr>
                    <tr class="bg-light filter-row">
                        <th></th> <th><input type="text" class="form-control form-control-sm column-search" data-column="1"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" data-column="2" ></th>
                        <th><input type="text" class="form-control form-control-sm column-search" data-column="3" ></th>
                        <th><input type="text" class="form-control form-control-sm column-search" data-column="4" ></th>
                        <th></th> <th><input type="text" class="form-control form-control-sm column-search" data-column="6"></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</form>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    let currentBomId = {{ $bom->id }};
    let tableAddedItems = null;

    $(document).ready(function() {
        $('.select2bs4').select2({ theme: 'bootstrap4' });

        loadSubmittedItems(currentBomId);

        $('#form-bom').on('submit', function(e) {
            e.preventDefault();
            submitMainForm(this);
        });

        $(document).on('select2:open', () => {
            document.querySelector('.select2-container--open .select2-search__field').focus();
        });

        $('#item_contents, #rule_bom, #colorList, #sizeList').on('change', function() {
            getListData();
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
            if (e.which == 45) {
                return false;
            }
        });
    });

    function getRule() {
        let id_contents = $('#item_contents').val();
        if (!id_contents) return $('#rule_bom').html('<option value="">Pilih Rule</option>');

        $.post("{{ route('get-rule-bom') }}", {
            _token: "{{ csrf_token() }}",
            id_contents: id_contents
        }, function(res) {
            $("#rule_bom").html(res).trigger('change');
            getListData();
        });
    }

    function getListData() {
        let id_contents = $('#item_contents').val();
        let rule = $('#rule_bom').val();

        let selected_colors = $('#colorList').select2('data');
        let selected_sizes = $('#sizeList').select2('data');

        if (!id_contents || !rule) {
            $("#itemTable tbody").empty();
            return;
        }

        let proses = false;
        if (rule === "All Color All Size") proses = true;
        else if (rule === "All Color Range Size") proses = (selected_sizes.length > 0);
        else if (rule === "Per Color All Size") proses = (selected_colors.length > 0);
        else proses = (selected_colors.length > 0 && selected_sizes.length > 0);

        if (!proses) {
            $("#itemTable tbody").empty();
            return;
        }

        $.post("{{ route('get-list-data-bom') }}", {
            _token: "{{ csrf_token() }}",
            id_contents: id_contents
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
                selected_colors.forEach(c => {
                    selected_sizes.forEach(s => rows.push({ label: `${c.text} | ${s.text}`, cId: c.id, sId: s.id }));
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
                    <td><input type="number" step="0.0001" name="qty_input[${idx}]" class="form-control form-control-sm text-right qty_input" placeholder="0.0000"></td>
                </tr>`;
                tbody.append(row);
            });

            $('.select2-item').select2({ theme: 'bootstrap4', width: '100%' });
        });
    }

    function submitMainForm(form) {
        Swal.fire({
            title: 'Simpan Item BOM?',
            html: "Item ini akan ditambahkan ke list bawah.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Ya, Simpan'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                let formData = new FormData(form);
                formData.append('id_bom_marketing', currentBomId);

                let colors = $('#colorList').val();
                let sizes = $('#sizeList').val();

                if(colors) colors.forEach(id => formData.append('colors[]', id));
                if(sizes) sizes.forEach(id => formData.append('sizes[]', id));

                $.ajax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.status == 200) {
                            Swal.fire({ icon: 'success', title: 'Berhasil!', timer: 1500, showConfirmButton: false });

                            $('#item_contents, #rule_bom, #unit, #shell, select[name="id_supplier"]').val('').trigger('change');
                            $(form).find('textarea[name="notes"]').val('');
                            $("#itemTable tbody").empty();

                            loadSubmittedItems(currentBomId);
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

    function loadSubmittedItems(idHeader) {
        if (!idHeader) return;

        let url = "{{ route('get-items', ':id') }}";
        url = url.replace(':id', idHeader);

        if ($.fn.DataTable.isDataTable('#table-detail-bom')) {
            tableAddedItems.ajax.url(url).load();
        } else {
            tableAddedItems = $('#table-detail-bom').DataTable({
                processing: true,
                serverSide: true,
                orderCellsTop: true,
                ajax: url,
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    {
                        data: 'content_name',
                        name: 'content_name',
                        render: function(data) {
                            return `${data ? data : '-'}`;
                        }
                    },
                    { data: 'item_name', name: 'i.itemdesc' },
                    { data: 'color_name', name: 'color_name', className: 'text-center' },
                    { data: 'size_name', name: 'size_name', className: 'text-center' },
                    {
                        data: 'qty',
                        name: 'qty',
                        className: 'text-center',
                        searchable: false,
                        render: function(data) {
                            return parseInt(data);
                        }
                    },
                    { data: 'unit_name', name: 'unit_name', className: 'text-center' }
                ],
                language: { emptyTable: "Belum ada item yang ditambahkan." },
                autoWidth: false,
                responsive: true
            });

            $('.column-search').on('keyup change clear', function() {
                let colIdx = $(this).data('column');
                if (tableAddedItems.column(colIdx).search() !== this.value) {
                    tableAddedItems.column(colIdx).search(this.value).draw();
                }
            });
        }
    }
</script>
@endsection
