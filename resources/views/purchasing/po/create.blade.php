@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .table-custom th {
            background-color: var(--sb-color) !important;
            color: white;
            white-space: nowrap;
            text-align: center;
            vertical-align: middle !important;
            font-size: 13px;
        }
        .table-custom td {
            vertical-align: middle !important;
            font-size: 13px;
        }
        .input-table {
            width: 100%;
            min-width: 80px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 13px;
        }
        .input-table:focus {
            outline: none;
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        .input-table[readonly] {
            background-color: #e9ecef;
            pointer-events: none;
        }
        .select2-container--bootstrap4 .select2-selection--single {
            height: calc(1.5em + .5rem + 2px) !important;
        }
        .table-scroll-wrapper {
            max-height: 75vh;
            overflow-y: auto;
        }
        .table-scroll-wrapper thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: var(--sb-color);
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
    </style>
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header bg-sb">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold">Purchase Order</h5>
            <a href="{{ route('purchasing') }}" class="btn btn-sm btn-primary">
                <i class="fa fa-reply"></i> Kembali ke List
            </a>
        </div>
    </div>

    <form id="form-create-po" action="#" method="POST">
        @csrf
        <div class="card-body">

            <div class="row">
                <div class="col-md-3 form-group">
                    <label><small class="fw-bold">Draft PO Date</small></label>
                    <input type="date" name="po_date" id="po_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3 form-group">
                    <label><small class="fw-bold">Tax</small></label>
                    <select name="tax" id="tax" class="form-control select2bs4">
                        <option value="">Pilih Tax</option>
                        @foreach ($tax as $t)
                            <option value="{{ $t->id ?? $t->Id }}">{{ $t->tampil ?? $t->Tampil }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label><small class="fw-bold">Payment Terms</small></label>
                   <select name="payment_term" id="payment_term" class="form-control select2bs4">
                        <option value="">Pilih Payment Terms</option>
                        @foreach ($payment_term as $pt)
                            <option value="{{ $pt->id ?? $pt->Id }}">{{ $pt->tampil ?? $pt->Tampil }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label><small class="fw-bold">Tipe Commercial</small></label>
                    <select name="tipe_commercial" id="tipe_commercial" class="form-control select2bs4">
                        <option value="">--- Pilih Tipe Commersial PO ---</option>
                        <option value="REGULAR">REGULAR</option>
                        <option value="FOC">FOC</option>
                        <option value="BUYER">BUYER</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 form-group">
                    <label><small class="fw-bold">Jenis Item</small></label>
                    <select name="jenis_item" id="jenis_item" class="form-control select2bs4">
                        <option value="">Pilih Jenis Item</option>
                        <option value="Material">Material</option>
                        <option value="Manufacturing">Manufacturing</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <div class="row">
                        <div class="col-6">
                            <label><small class="fw-bold">Days</small></label>
                            <input type="text" name="days" id="days" class="form-control form-control-sm text-end numbers-only" placeholder="0">
                        </div>
                        <div class="col-6">
                            <label><small class="fw-bold">Day Terms</small></label>
                            <select name="day_terms" id="day_terms" class="form-control select2bs4">
                                <option value="">Pilih Day Terms</option>
                                @foreach ($day_terms as $dt)
                                    <option value="{{ $dt->id ?? $dt->Id }}">{{ $dt->tampil ?? $dt->Tampil }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 form-group">
                    <label><small class="fw-bold">Currency</small></label>
                    <select name="currency" id="currency" class="form-control select2bs4">
                        <option value="">Pilih Currency</option>
                        @foreach ($currency as $c)
                            <option value="{{ $c->id ?? $c->Id }}">{{ $c->nama_pilihan ?? $c->Nama_Pilihan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label><small class="fw-bold">Kurs</small></label>
                    <input type="text" name="kurs" id="kurs" class="form-control form-control-sm input-decimal text-end" placeholder="0.00">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label><small class="fw-bold">Supplier</small></label>
                    <select name="id_supplier" id="id_supplier" class="form-control select2bs4">
                        <option value="">Pilih Supplier</option>
                        @foreach ($suppliers as $s)
                            <option value="{{ $s->id_supplier ?? $s->Id_Supplier }}">{{ $s->supplier ?? $s->Supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label><small class="fw-bold">Notes</small></label>
                    <textarea name="notes_po" id="notes_po" class="form-control" rows="2" placeholder="Input notes..."></textarea>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between align-items-center mb-2 bg-light p-2 rounded">
                <h6 class="fw-bold text-sb mb-0"><i class="fas fa-boxes"></i> Rincian Item PO</h6>

                <div class="d-flex">
                    <div class="input-group input-group-sm mr-2" style="width: 250px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" id="search_po_table" class="form-control" placeholder="Cari Style / Item...">
                    </div>
                    <button type="button" class="btn btn-sm btn-danger fw-bold mr-2" id="btn-delete-batch" style="display: none;">
                        <i class="fas fa-trash-alt"></i> Hapus Terpilih
                    </button>
                    <button type="button" class="btn btn-sm btn-info fw-bold" data-bs-toggle="modal" data-bs-target="#modalPilihItem">
                        <i class="fas fa-search-plus"></i> Pilih Item
                    </button>
                </div>
            </div>

            <div class="table-responsive table-scroll-wrapper">
                <table class="table table-bordered table-custom" id="table-po-items">
                    <thead>
                        <tr>
                            <th width="3%" class="text-center"><input type="checkbox" id="check-all-po-items"></th>
                            <th style="min-width: 150px;">Style</th>
                            <th style="min-width: 300px;">Item</th>
                            <th>Stok Item</th>
                            <th>Qty BOM</th>
                            <th>Qty Need</th>
                            <th>Blc PR</th>
                            <th>Qty PR</th>
                            <th>Unit</th>
                            <th>Convert</th>
                            <th>Unit Convert</th>
                            <th>Qty PR (Conv)</th>
                            <th>Unit (Conv)</th>
                            <th>Price Costing</th>
                            <th>Price Costing Conv</th>
                            <th>Price PR/Unit</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="empty-row-item">
                            <td colspan="17" class="text-center font-italic text-muted">Tidak ada Item. Silakan klik "Pilih Item".</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <hr class="mt-4">

            <div class="mb-2 mt-4">
                <h6 class="fw-bold text-sb mb-0"><i class="fas fa-money-bill-wave"></i> Rincian Biaya Lain - Lain</h6>
            </div>

            <div class="row align-items-end mb-3 bg-light p-3 rounded border mx-0">
                <div class="col-md-3 form-group mb-0">
                    <label><small class="fw-bold">Kategori :</small></label>
                     <select name="kategori_biaya" id="kategori_biaya" class="form-control select2bs4">
                        <option value="">Pilih Kategori</option>
                        @foreach ($kategori_biaya as $kb)
                            <option value="{{ $kb->id }}">{{ $kb->tampil }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group mb-0">
                    <label><small class="fw-bold">Value *:</small></label>
                    <input type="text" id="value_biaya" class="form-control input-decimal text-end" placeholder="0">
                </div>
                <div class="col-md-1 form-group mb-0 text-center">
                    <label><small class="fw-bold">PPN 11% :</small></label>
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" id="ppn_biaya" value="1" style="transform: scale(1.5);">
                    </div>
                </div>
                <div class="col-md-4 form-group mb-0">
                    <label><small class="fw-bold">Description :</small></label>
                    <textarea id="desc_biaya" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-md-1 form-group mb-0 text-right">
                    <button type="button" class="btn btn-primary fw-bold w-100" id="btn-tambah-biaya">Tambah</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm" id="table-biaya-lain">
                    <thead class="bg-white text-center">
                        <tr style="border-bottom: 2px solid #333;">
                            <th width="5%" class="border-top-0 border-left-0 border-right-0">No #</th>
                            <th width="20%" class="border-top-0 border-left-0 border-right-0">Kategori</th>
                            <th width="15%" class="border-top-0 border-left-0 border-right-0">Subtotal</th>
                            <th width="15%" class="border-top-0 border-left-0 border-right-0">PPN</th>
                            <th width="15%" class="border-top-0 border-left-0 border-right-0">Total</th>
                            <th width="20%" class="border-top-0 border-left-0 border-right-0">Description</th>
                            <th width="10%" class="border-top-0 border-left-0 border-right-0">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot class="bg-sb text-white">
                        <tr style="border-top: 2px solid #333;">
                            <td colspan="2" class="text-right align-middle border-0">Total :</td>
                            <td class="text-right align-middle border-0" id="footer-subtotal">0.00</td>
                            <td class="text-right align-middle border-0" id="footer-ppn">0.00</td>
                            <td class="text-right align-middle border-0" id="footer-total">0.00</td>
                            <td colspan="2" class="border-0"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>

        <div class="card-footer text-right">
            <button type="submit" class="btn btn-success fw-bold"><i class="fas fa-save"></i> Simpan Data PO</button>
        </div>
    </form>
</div>

<div class="modal fade" id="modalPilihItem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-sb text-white py-2">
                <h6 class="modal-title fw-bold"><i class="fas fa-search"></i> Cari Item</h6>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body bg-light">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label><small class="fw-bold">Pilih Style</small></label>
                        <select id="search_style" class="form-control select2bs4-modal">
                            <option value="">Pilih Style</option>
                            @foreach ($style as $s)
                                <option value="{{ $s->id_bom }}">{{ $s->style }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label><small class="fw-bold">Cari Item (Filter)</small></label>
                        <input type="text" id="search_item_filter" class="form-control" placeholder="Ketik nama material untuk mencari...">
                    </div>
                </div>

                <div class="table-responsive mt-2">
                    <table class="table table-bordered table-sm table-hover w-100" id="table-modal-items">
                        <thead class="bg-sb text-white text-center">
                            <tr>
                                <th width="5%"><input type="checkbox" id="check-all-items"></th>
                                <th width="7%">ID Item</th>
                                <th width="40%">Item Description</th>
                                <th width="10%">Product Set</th>
                                <th width="10%">Cons BOM</th>
                                <th width="10%">Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center font-italic text-muted">Silakan pilih Style terlebih dahulu</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary btn-sm fw-bold" id="btn-add-item-to-table">Simpan</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('custom-script')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

<script>
    $(document).ready(function() {

        const parseNum = (val) => parseFloat(val) || 0;
        const formatIDR = (num) => num.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });
        $('.select2bs4-modal').select2({
            theme: 'bootstrap4',
            width: '100%',
            dropdownParent: $('#modalPilihItem')
        });

        $('.numbers-only').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        $(document).on('input', '.input-decimal', function() {
            let val = $(this).val().replace(/[^0-9.]/g, '');
            let parts = val.split('.');
            if (parts.length > 2) val = parts[0] + '.' + parts.slice(1).join('');
            $(this).val(val);
        });

        function hitungTotalBiaya() {
            let sumSubtotal = 0, sumPpn = 0, sumTotal = 0;

            $('#table-biaya-lain tbody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
                sumSubtotal += parseNum($(this).find('.val-subtotal').val());
                sumPpn += parseNum($(this).find('.val-ppn').val());
                sumTotal += parseNum($(this).find('.val-total').val());
            });

            $('#footer-subtotal').text(formatIDR(sumSubtotal));
            $('#footer-ppn').text(formatIDR(sumPpn));
            $('#footer-total').text(formatIDR(sumTotal));
        }

        $('#btn-tambah-biaya').on('click', function() {
            let kategoriId = $('#kategori_biaya').val();
            let kategoriText = $('#kategori_biaya option:selected').text();
            let value = parseNum($('#value_biaya').val());
            let desc = $('#desc_biaya').val();
            let isPpn = $('#ppn_biaya').is(':checked');

            if (value <= 0) return Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Value harus diisi!', showConfirmButton: false, timer: 2000 });
            if (!kategoriId) return Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Kategori harus dipilih!', showConfirmButton: false, timer: 2000 });

            let ppnValue = isPpn ? (value * 0.11) : 0;
            let totalValue = value + ppnValue;

            let newRow = `
                <tr class="bg-light">
                    <td class="text-center align-middle"></td>
                    <td class="align-middle">
                        <input type="hidden" name="kategori_biaya[]" value="${kategoriId}">
                        ${kategoriText || '-'}
                    </td>
                    <td class="text-right align-middle">
                        <input type="hidden" name="value_biaya[]" class="val-subtotal" value="${value}">
                        ${formatIDR(value)}
                    </td>
                    <td class="text-right align-middle">
                        <input type="hidden" name="is_ppn_biaya[]" value="${isPpn ? '1' : '0'}">
                        <input type="hidden" name="ppn_biaya[]" class="val-ppn" value="${ppnValue}">
                        ${formatIDR(ppnValue)}
                    </td>
                    <td class="text-right align-middle">
                        <input type="hidden" name="total_biaya[]" class="val-total" value="${totalValue}">
                        ${formatIDR(totalValue)}
                    </td>
                    <td class="text-center align-middle">
                        <input type="hidden" name="desc_biaya[]" value="${desc}">
                        ${desc}
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm btn-danger btn-remove-biaya px-3"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
            $('#table-biaya-lain tbody').append(newRow);

            $('#kategori_biaya').val('').trigger('change');
            $('#value_biaya, #desc_biaya').val('');
            $('#ppn_biaya').prop('checked', false);
            hitungTotalBiaya();
        });

        $(document).on('click', '.btn-remove-biaya', function() {
            $(this).closest('tr').remove();
            hitungTotalBiaya();
        });


        $('#search_style').on('change', function() {
            let idBom = $(this).val();
            let jenisItem = $('#jenis_item').val();

            let tbody = $('#table-modal-items tbody');
            $('#check-all-items').prop('checked', false);


            if (idBom && !jenisItem) {
                Swal.fire('Peringatan!', 'Silakan pilih Jenis Item (Material / Manufacturing) di form utama terlebih dahulu.', 'warning');
                $(this).val('').trigger('change.select2');
                return;
            }

            if (!idBom) {
                tbody.html('<tr><td colspan="6" class="text-center font-italic text-muted">Silakan pilih Style terlebih dahulu</td></tr>');
                return;
            }

            tbody.html('<tr><td colspan="6" class="text-center font-italic">Mencari material... <i class="fas fa-spinner fa-spin"></i></td></tr>');

            $.ajax({
                url: '{{ route("get-items-by-bom") }}',
                type: 'GET',
                data: {
                    id_bom: idBom,
                    jenis_item: jenisItem
                },
                success: function(response) {
                    tbody.empty();
                    if(response && response.length > 0) {
                        $.each(response, function(index, data) {
                            let prodSet = data.product_set || '';
                            tbody.append(`
                                <tr class="item-row">
                                    <td class="text-center align-middle">
                                        <input type="checkbox" class="check-item"
                                            data-id="${data.id_item}"
                                            data-desc="${data.itemdesc}"
                                            data-cons="${data.cons_bom}"
                                            data-cons-asli="${data.cons_asli || 1}"
                                            data-unit="${data.unit}"
                                            data-set="${prodSet}">
                                    </td>
                                    <td class="align-middle text-center item-id">${data.id_item}</td>
                                    <td class="align-middle item-desc fw-bold">${data.itemdesc}</td>
                                    <td class="align-middle text-center fw-bold">${prodSet}</td>
                                    <td class="text-center align-middle">${data.cons_bom}</td>
                                    <td class="text-center align-middle">${data.unit}</td>
                                </tr>
                            `);
                        });
                    } else {
                        tbody.html('<tr><td colspan="6" class="text-center font-italic text-danger">Item tidak ditemukan untuk jenis item ini.</td></tr>');
                    }
                }
            });
        });

        $('#search_item_filter').on('keyup', function() {
            let value = $(this).val().toLowerCase();
            $('#table-modal-items tbody .item-row').filter(function() {
                let textToMatch = $(this).find('.item-id').text().toLowerCase() + " " + $(this).find('.item-desc').text().toLowerCase();
                $(this).toggle(textToMatch.indexOf(value) > -1);
            });
        });

        $('#check-all-items').on('click', function() {
            $('.item-row:visible .check-item').prop('checked', $(this).prop('checked'));
        });


        let optionUnits = ``;
        @if(isset($units))
            @foreach($units as $u)
                optionUnits += `<option value="{{ $u->nama_pilihan }}">{{ $u->nama_pilihan }}</option>`;
            @endforeach
        @endif

        $('#btn-add-item-to-table').on('click', function() {
            let styleVal = $('#search_style').val();
            let styleText = $('#search_style option:selected').text().split(' - ')[0].trim();
            let checkedItems = $('.check-item:checked');

            if(!styleVal){
                return Swal.fire('Peringatan!', 'Silakan pilih Style terlebih dahulu.', 'warning');
            }

            if(checkedItems.length === 0){
                    return Swal.fire('Peringatan!', 'Silakan pilih minimal 1 item.', 'warning');
                }
            $('#empty-row-item').remove();

            checkedItems.each(function() {
                let idItem = $(this).attr('data-id');
                let itemDesc = $(this).attr('data-desc');
                let consBom = parseNum($(this).attr('data-cons'));
                let consAsli = parseNum($(this).attr('data-cons-asli')) || 1;
                let unit = $(this).attr('data-unit');
                let prodSet = $(this).attr('data-set') || '';


                let isDuplicate = false;
                $('#table-po-items tbody .po-item-row').each(function() {
                    if ($(this).attr('data-id') === idItem && $(this).attr('data-set') === prodSet) {
                        isDuplicate = true;
                    }
                });

                if(isDuplicate) return true;

                let stokItem = 0;
                let qtyNeed = Math.max(0, consBom - stokItem);
                let displaySet = prodSet ? `<br><span class="badge badge-danger" style="font-size: 11px;"> ${prodSet}</span>` : '';

                let newRow = `
                    <tr class="po-item-row" data-id="${idItem}" data-set="${prodSet}" data-cons-asli="${consAsli}">
                        <td class="text-center align-middle">
                            <input type="checkbox" class="check-po-item">
                        </td>
                        <td class="align-middle"><input type="hidden" name="style_item[]" value="${styleVal}">${styleText}</td>
                        <td class="align-middle">
                            <input type="hidden" name="id_item[]" value="${idItem}">
                            <input type="hidden" name="product_set[]" value="${prodSet}">
                            <span class="item-name-text font-weight-bold">${itemDesc}</span> ${displaySet}
                        </td>
                        <td class="align-middle"><input type="text" name="stok_item[]" class="input-table val-stok input-decimal text-center" value="${stokItem}"></td>
                        <td class="align-middle"><input type="text" name="qty_bom[]" class="input-table val-bom text-center" value="${consBom}" readonly></td>
                        <td class="align-middle"><input type="text" name="qty_need[]" class="input-table val-need text-center" value="${qtyNeed}" readonly></td>
                        <td class="align-middle"><input type="text" name="blc_pr[]" class="input-table val-blc text-center" value="${qtyNeed}" readonly></td>
                        <td class="align-middle"><input type="text" name="qty_pr[]" class="input-table input-decimal text-center" value="0"></td>
                        <td class="align-middle"><input type="text" name="unit_pr[]" class="input-table text-center" value="${unit}" readonly></td>
                        <td class="align-middle"><input type="text" name="convert[]" class="input-table input-decimal text-center" value="0"></td>
                        <td class="align-middle">
                            <select name="unit_convert[]" class="input-table select-unit-convert">
                                <option value="${unit}">${unit} (BOM)</option>
                                ${optionUnits}
                            </select>
                        </td>
                        <td class="align-middle"><input type="text" name="qty_pr_conv[]" class="input-table text-center" value="0" readonly></td>
                        <td class="align-middle"><input type="text" name="unit_pr_conv[]" class="input-table text-center" value="${unit}" readonly></td>
                        <td class="align-middle"><input type="text" name="price_costing[]" class="input-table text-right" value="0"></td>
                        <td class="align-middle"><input type="text" name="price_costing_conv[]" class="input-table text-right" value="0" readonly></td>
                        <td class="align-middle"><input type="text" name="price_pr[]" class="input-table input-decimal text-right" value="0"></td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-sm btn-danger btn-remove-item"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                $('#table-po-items tbody').append(newRow);
            });

            $('#modalPilihItem').modal('hide');
            $('.check-item, #check-all-items').prop('checked', false);
            $('#search_item_filter').val('').trigger('keyup');
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Item berhasil ditambahkan.', showConfirmButton: false, timer: 1500 });
        });

        $('#search_po_table').on('keyup', function() {
            let value = $(this).val().toLowerCase();
            $('#table-po-items tbody .po-item-row').filter(function() {
                let textMatch = $(this).find('td:eq(1)').text().toLowerCase() + " " + $(this).find('td:eq(2)').text().toLowerCase();
                $(this).toggle(textMatch.indexOf(value) > -1);
            });
        });

        function toggleBatchDeleteBtn() {
            let checkedCount = $('.check-po-item:checked').length;
            if (checkedCount > 0) {
                $('#btn-delete-batch').fadeIn(200);
            } else {
                $('#btn-delete-batch').fadeOut(200);
            }
        }

        $('#check-all-po-items').on('change', function() {
            $('.check-po-item').prop('checked', $(this).prop('checked'));
            toggleBatchDeleteBtn();
        });

        $(document).on('change', '.check-po-item', function() {
            toggleBatchDeleteBtn();
            if (!$(this).prop('checked')) {
                $('#check-all-po-items').prop('checked', false);
            }
        });

        $('#btn-delete-batch').on('click', function() {
            Swal.fire({
                title: 'Hapus Item Terpilih?',
                text: "Semua item yang dicentang akan dihapus dari tabel.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('.check-po-item:checked').closest('tr').remove();

                    $('#check-all-po-items').prop('checked', false);
                    toggleBatchDeleteBtn();

                    if ($('#table-po-items tbody tr').length === 0) {
                        $('#table-po-items tbody').append(`<tr id="empty-row-item"><td colspan="17" class="text-center font-italic text-muted">Belum ada item yang dipilih. Silakan klik "Pilih Item".</td></tr>`);
                    }

                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Item berhasil dihapus.', showConfirmButton: false, timer: 1500 });
                }
            });
        });

        $(document).on('click', '.btn-remove-item', function() {
            $(this).closest('tr').remove();
            toggleBatchDeleteBtn();

            if ($('#table-po-items tbody tr').length === 0) {
                $('#table-po-items tbody').append(`<tr id="empty-row-item"><td colspan="17" class="text-center font-italic text-muted">Belum ada item yang dipilih. Silakan klik "Pilih Item".</td></tr>`);
                $('#check-all-po-items').prop('checked', false);
            }
        });


        $(document).on('keyup input', '.val-stok', function() {
            let tr = $(this).closest('tr');
            let stok = parseNum($(this).val());
            let bom = parseNum(tr.find('.val-bom').val());

            let need = Math.max(0, bom - stok);
            tr.find('.val-need').val(need);
            tr.find('.val-blc').val(need);
        });

        function calculatePoRow(tr) {
            let qtyPr = parseNum(tr.find('input[name="qty_pr[]"]').val());
            let convert = parseNum(tr.find('input[name="convert[]"]').val());
            let priceCosting = parseNum(tr.find('input[name="price_costing[]"]').val());
            let consAsli = parseNum(tr.attr('data-cons-asli')) || 1;

            let unitAsli = tr.find('input[name="unit_pr[]"]').val();
            let unitConvert = tr.find('select[name="unit_convert[]"]').val();

            let isConverted = convert > 0;

            let qtyPrConv = isConverted ? (qtyPr * convert) : qtyPr;
            let finalUnit = isConverted ? unitConvert : unitAsli;
            let priceCostingConv = isConverted ? ((priceCosting / consAsli) * convert) : priceCosting;

            tr.find('input[name="qty_pr_conv[]"]').val(qtyPrConv);
            tr.find('input[name="unit_pr_conv[]"]').val(finalUnit);
            tr.find('input[name="price_costing_conv[]"]').val(priceCostingConv);
        }

        $(document).on('keyup change', 'input[name="qty_pr[]"], input[name="convert[]"], select[name="unit_convert[]"], input[name="price_costing[]"]', function() {
            calculatePoRow($(this).closest('tr'));
        });


        $('#form-create-po').on('submit', function(e) {
            e.preventDefault();

            let actionUrl = '{{ route("store-purchase-order") }}';
            let formData = $(this).serialize();

            Swal.fire({
                title: 'Simpan Data PO?',
                text: "Pastikan semua item, stok, dan harga sudah diisi dengan benar.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});

                    $.ajax({
                        url: actionUrl,
                        type: "POST",
                        data: formData,
                        success: function(response) {
                            if(response.status === 200) {
                                Swal.fire('Berhasil!', response.message, 'success').then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('Gagal!', response.message || 'Terjadi kesalahan.', 'error');
                            }
                        },
                        error: function(xhr) {
                            let msg = 'Gagal menyimpan data!';
                            if(xhr.responseJSON && xhr.responseJSON.errors) {
                                msg = Object.values(xhr.responseJSON.errors)[0][0];
                            }
                            Swal.fire('Error Validasi!', msg, 'error');
                        }
                    });
                }
            });
        });

        $('#modalPilihItem').on('hidden.bs.modal', function () {
            $('#search_style').val('').trigger('change.select2');
            $('#table-modal-items tbody').html('<tr><td colspan="6" class="text-center font-italic text-muted">Silakan pilih Style terlebih dahulu</td></tr>');
            $('#search_item_filter').val('');
            $('#check-all-items').prop('checked', false);

            console.log('Modal clear');
        });

    });
</script>
@endsection
