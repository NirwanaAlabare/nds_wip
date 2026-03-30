@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        #card-preview .card-body {
            max-height: 500px;
            overflow-y: auto;
            overflow-x: auto;
            padding: 0;
        }
        #table-preview-po {
            margin-bottom: 0 !important;
        }
        #table-preview-po thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: var(--sb-color) !important;
        }
    </style>
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <h3 class="card-title fw-bold">Form Create Sales Order</h3>
    </div>
    <form id="form-create-so" action="{{ route('so-store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Buyer <span class="text-danger">*</span></label>
                    <select name="id_buyer" class="form-control select2bs4">
                        <option value="">Pilih Buyer</option>
                        @foreach ($buyers as $buyer)
                            <option value="{{ $buyer->Id_Supplier }}">{{ $buyer->Supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Product Group <span class="text-danger">*</span></label>
                    <select name="product_group" id="product_group" class="form-control select2bs4">
                        <option value="">Pilih Product Group</option>
                        @foreach($product_groups as $group)
                            <option value="{{ $group->product_group }}">{{ $group->product_group }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Product Item <span class="text-danger">*</span></label>
                    <select name="id_product_item" id="id_product_item" class="form-control select2bs4">
                        <option value="">Pilih Product Item</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Brand <span class="text-danger">*</span></label>
                    <input type="text" name="brand" id="brand" class="form-control">
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Style <span class="text-danger">*</span></label>
                    <input type="text" name="style" id="style" class="form-control">
                </div>
                <div class="col-md-3 form-group">
                    <label>Market <span class="text-danger">*</span></label>
                    <input type="text" name="market" id="market" class="form-control">
                </div>
                <div class="col-md-3 form-group">
                    <label>Marketing Order <span class="text-danger">*</span></label>
                    <select name="marketing_order" id="marketing_order" class="form-control select2bs4">
                        <option value="">Pilih Marketing Order</option>
                        <option value="BANDUNG">Bandung</option>
                        <option value="JAKARTA">Jakarta</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>SMV <span class="text-danger">*</span></label>
                    <input type="text" name="smv" id="smv" class="form-control input-decimal">
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Currency <span class="text-danger">*</span></label>
                   <select name="id_currency" id="id_currency" class="form-control select2bs4">
                        <option value="">Pilih Currency</option>
                        @foreach($currency as $curr)
                            <option value="{{ $curr->id }}">{{ $curr->nama_pilihan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Confirm Price <span class="text-danger">*</span></label>
                    <input type="text" name="confirm_price" id="confirm_price" class="form-control input-decimal">
                </div>
                <div class="col-md-3 form-group">
                    <label>Total Qty</label>
                    <input type="text" name="total_qty" id="total_qty" class="form-control bg-light input-decimal" readonly>
                </div>
                <div class="col-md-3 form-group">
                    <label>Notes <span class="text-danger">*</span></label>
                    <input type="text" name="notes" id="notes" class="form-control">
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Jumlah PO</label>
                    <input type="text" name="jumlah_po" id="jumlah_po" class="form-control bg-light input-decimal" readonly>
                </div>
                <div class="col-md-3 form-group">
                    <label>No Katalog BOM <span class="text-danger">*</span></label>
                    <div class="d-flex align-items-center" style="gap: 5px;">
                        <select name="id_bom" id="id_bom" class="form-control select2bs4">
                            <option value="">Pilih No Katalog BOM</option>
                            @foreach($bom_catalog as $bom)
                                <option value="{{ $bom->id }}">{{ "{$bom->no_katalog_bom} - {$bom->style}" }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-sm btn-info" style="font-size: 12px; white-space: nowrap;" onclick="goToEditBOM()">
                            <i class="fas fa-edit"></i> Edit BOM
                        </button>
                    </div>
                </div>
                 <div class="col-md-3 form-group">
                    <label>Price Costing</label>
                    <input type="text" name="price_costing" id="price_costing" class="form-control input-decimal">
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-3 form-group">
                    <label>VAT (%) <span class="text-danger">*</span></label>
                    <input type="text" name="vat" id="vat" class="form-control input-decimal">
                </div>
                <div class="col-md-3 form-group">
                    <label>GA Cost (%) <span class="text-danger">*</span></label>
                    <input type="text" name="ga_cost" id="ga_cost" class="form-control input-decimal">
                </div>
                <div class="col-md-3 form-group">
                    <label>Commission Fee (%) <span class="text-danger">*</span></label>
                    <input type="text" name="commission_fee" id="commission_fee" class="form-control input-decimal">
                </div>
                <div class="col-md-3 form-group">
                    <label>Profit (%)</label>
                    <input type="text" name="profit" id="profit" class="form-control input-decimal" readonly>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Upload Gambar <span class="text-danger">*</span></label>
                    <input type="file" name="images" id="images" class="form-control-file mb-2" accept="image/*">
                    <div class="border rounded d-flex justify-content-center align-items-center bg-light" style="height: 150px; overflow: hidden;">
                        <img id="preview_images" src="" alt="Preview Gambar" style="max-height: 100%; display: none;">
                        <span id="text_preview" class="text-muted">Preview Gambar</span>
                    </div>
                </div>

                <div class="col-md-4 form-group">
                    <label>Upload File (Excel) <span class="text-danger">*</span></label>
                    <div class="d-flex mb-2">
                        <a href="{{ asset('template/template_upload_so.xlsx') }}" class="btn btn-outline-info btn-sm mr-2">
                            <i class="fas fa-download"></i> Download Template Excel
                        </a>
                    </div>
                    <input type="file" name="file_so" id="file_so" class="form-control-file" accept=".xls,.xlsx">
                </div>
            </div>
        </div>

        <hr>

        <div class="card card-sb" id="card-preview">
            <div class="card-header bg-warning">
                <h3 class="card-title fw-bold"><i class="fas fa-table"></i> Preview Data Upload</h3>
            </div>
            <div class="card-body">
                <button type="button" id="btn-reload-preview" class="btn btn-info mb-2" onclick="loadPreviewUpload()"><i class="fas fa-refresh"></i> Reload Table</button>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-striped text-nowrap w-100" id="table-preview-po">
                        <thead class="bg-sb text-white text-center">
                            <tr>
                                <th>Style</th>
                                <th>Desc</th>
                                <th>PO</th>
                                <th>Market</th>
                                <th>Ex Fty</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Qty</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card-footer text-right">
            <button type="button" id="btn-back" class="btn btn-secondary">
                <i class="fas fa-times"></i> Batal
            </button>
            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
        </div>
    </form>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    let previewTable = null;

  function goToEditBOM() {
        let id_bom = $('#id_bom').val();

        if (!id_bom) {
            Swal.fire('Peringatan!', 'Silakan pilih No Katalog BOM terlebih dahulu.', 'warning');
            return;
        }

        let baseUrl = "{{ url('master-bom/edit') }}";
        let urlEditBom = baseUrl + '/' + id_bom;

        window.open(urlEditBom, '_blank');
    }

    // function loadPreviewUpload() {
    //     let id_bom = $('#id_bom').val();
    //     if (!id_bom) return;

    //     $('#table-preview-po tbody').html('<tr><td colspan="15" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading data...</td></tr>');

    //     $.ajax({
    //         url: "{{ route('so-get-temp-data') }}",
    //         type: 'GET',
    //         data: { id_bom: id_bom },
    //         success: function(res) {
    //             if ($.fn.DataTable.isDataTable('#table-preview-po')) {
    //                 $('#table-preview-po').DataTable().destroy();
    //             }

    //             let headerHtml = `<tr><th>Style</th><th>Desc</th><th>PO</th><th>Market</th><th>Ex Fty</th><th>Color</th>`;
    //             res.available_sizes.forEach(size => {
    //                 headerHtml += `<th class="bg-warning text-dark text-center">${size}</th>`;
    //             });
    //             headerHtml += `</tr>`;
    //             $('#table-preview-po thead').html(headerHtml);

    //             let bodyHtml = '';
    //             if(res.data && res.data.length > 0) {
    //                 res.data.forEach(row => {
    //                     let colorErrorClass = row.color_error ? 'bg-danger text-white font-weight-bold' : '';

    //                     bodyHtml += `<tr>
    //                         <td>${row.style || '-'}</td>
    //                         <td>${row.desc || '-'}</td>
    //                         <td>${row.po || '-'}</td>
    //                         <td>${row.market || '-'}</td>
    //                         <td>${row.ex_fty || '-'}</td>
    //                         <td class="${colorErrorClass}">${row.color || '-'}</td>`;

    //                     res.available_sizes.forEach(size => {
    //                         let val = row[size];
    //                         let qty = val ? Number(val).toLocaleString('id-ID') : '-';
    //                         let isError = (row.errors && row.errors[size]) ? 'bg-danger text-white font-weight-bold' : '';
    //                         bodyHtml += `<td class="text-right ${isError}">${qty}</td>`;
    //                     });
    //                     bodyHtml += `</tr>`;
    //                 });
    //             } else {
    //                 let colCount = 6 + (res.available_sizes ? res.available_sizes.length : 0);
    //                 bodyHtml = `<tr><td colspan="${colCount}" class="text-center">Belum ada data di-upload.</td></tr>`;
    //             }
    //             $('#table-preview-po tbody').html(bodyHtml);

    //             if ($('#table-preview-po tfoot').length === 0) $('#table-preview-po').append('<tfoot></tfoot>');

    //             let footerHtml = `<tr>
    //                     <th class="bg-light"></th><th class="bg-light"></th><th class="bg-light"></th>
    //                     <th class="bg-light"></th><th class="bg-light"></th>
    //                     <th class="text-right align-middle bg-light text-dark fw-bold">TOTAL</th>`;
    //             res.available_sizes.forEach(size => {
    //                 footerHtml += `<th class="text-right bg-light text-dark fw-bold">0</th>`;
    //             });
    //             footerHtml += `</tr>`;
    //             $('#table-preview-po tfoot').html(footerHtml);

    //             $('#total_qty').val(res.total_qty || 0);
    //             $('#jumlah_po').val(res.jumlah_po || 0);

    //             if (res.data && res.data.length > 0) {
    //                 previewTable = $('#table-preview-po').DataTable({
    //                     "paging": false, "info": true, "searching": true, "ordering": false,
    //                     "autoWidth": false, "responsive": false, "scrollX": true,
    //                     "footerCallback": function (row, data, start, end, display) {
    //                         let api = this.api();
    //                         let intVal = function (i) {
    //                             if (typeof i === 'string') {
    //                                 let val = i.replace(/\./g, '');
    //                                 return val === '-' ? 0 : val * 1;
    //                             }
    //                             return typeof i === 'number' ? i : 0;
    //                         };

    //                         let startColIndex = 6;
    //                         let numSizes = res.available_sizes.length;
    //                         for (let i = 0; i < numSizes; i++) {
    //                             let colIndex = startColIndex + i;
    //                             let colTotal = api.column(colIndex, { search: 'applied' }).data().reduce((a, b) => intVal(a) + intVal(b), 0);
    //                             $(api.column(colIndex).footer()).html(colTotal.toLocaleString('id-ID'));
    //                         }
    //                     },
    //                     "drawCallback": function(settings) {
    //                         let api = this.api();
    //                         setTimeout(function() { api.columns.adjust(); }, 10);
    //                     }
    //                 });
    //             }

    //             let btnSubmit = $('#form-create-so button[type="submit"]');

    //             if (res.has_bom_error) {
    //                 btnSubmit.prop('disabled', true);

    //                 // if ($('#btn-fix-bom').length === 0) {
    //                 //     $('#table-preview-po').before(`<button type="button" id="btn-reload-preview" class="btn btn-info mb-2" onclick="loadPreviewUpload()"><i class="fas fa-refresh"></i> Reload Table</button>`);
    //                 // }

    //                 Swal.fire({
    //                     toast: true, position: 'top-end', icon: 'error',
    //                     title: 'Warna/Size tidak terdaftar di BOM!',
    //                     text: 'Silakan klik Edit BOM untuk mengubah Material.',
    //                     showConfirmButton: false, timer: 5000
    //                 });
    //             } else {
    //                 btnSubmit.prop('disabled', false);
    //                 $('#btn-fix-bom').remove();
    //             }
    //         },
    //         error: function(xhr) {
    //             $('#table-preview-po tbody').html('<tr><td colspan="10" class="text-center text-danger">Gagal memuat data preview.</td></tr>');
    //         }
    //     });
    // }

    function loadPreviewUpload() {
        let id_bom = $('#id_bom').val();
        if (!id_bom) return;

        $('#table-preview-po tbody').html('<tr><td colspan="15" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading data...</td></tr>');

        $.ajax({
            url: "{{ route('so-get-temp-data') }}",
            type: 'GET',
            data: { id_bom: id_bom },
            success: function(res) {
                if ($.fn.DataTable.isDataTable('#table-preview-po')) {
                    $('#table-preview-po').DataTable().destroy();
                }

                let headerHtml = `<tr><th>Style</th><th>Desc</th><th>PO</th><th>Market</th><th>Ex Fty</th><th>Color</th>`;
                res.available_sizes.forEach(size => {
                    headerHtml += `<th class="bg-warning text-dark text-center">${size}</th>`;
                });
                headerHtml += `</tr>`;
                $('#table-preview-po thead').html(headerHtml);

                let bodyHtml = '';
                if(res.data && res.data.length > 0) {
                    res.data.forEach(row => {
                        let colorErrorClass = row.color_error ? 'bg-danger text-white font-weight-bold' : '';

                        bodyHtml += `<tr>
                            <td>${row.style || '-'}</td>
                            <td>${row.desc || '-'}</td>
                            <td>${row.po || '-'}</td>
                            <td>${row.market || '-'}</td>
                            <td>${row.ex_fty || '-'}</td>
                            <td class="${colorErrorClass}">${row.color || '-'}</td>`;

                        res.available_sizes.forEach(size => {
                            let val = row[size];
                            let qty = val ? Number(val).toLocaleString('id-ID') : '-';
                            let isError = (row.errors && row.errors[size]) ? 'bg-danger text-white font-weight-bold' : '';
                            bodyHtml += `<td class="text-right ${isError}">${qty}</td>`;
                        });
                        bodyHtml += `</tr>`;
                    });
                } else {
                    let colCount = 6 + (res.available_sizes ? res.available_sizes.length : 0);
                    bodyHtml = `<tr><td colspan="${colCount}" class="text-center">Belum ada data di-upload.</td></tr>`;
                }
                $('#table-preview-po tbody').html(bodyHtml);

                if ($('#table-preview-po tfoot').length === 0) $('#table-preview-po').append('<tfoot></tfoot>');

                let footerHtml = `<tr>
                        <th class="bg-light"></th><th class="bg-light"></th><th class="bg-light"></th>
                        <th class="bg-light"></th><th class="bg-light"></th>
                        <th class="text-right align-middle bg-light text-dark fw-bold">TOTAL</th>`;
                res.available_sizes.forEach(size => {
                    footerHtml += `<th class="text-right bg-light text-dark fw-bold">0</th>`;
                });
                footerHtml += `</tr>`;
                $('#table-preview-po tfoot').html(footerHtml);

                $('#total_qty').val(res.total_qty || 0);
                $('#jumlah_po').val(res.jumlah_po || 0);

                if (res.data && res.data.length > 0) {
                    previewTable = $('#table-preview-po').DataTable({
                        "paging": false, "info": true, "searching": true, "ordering": false,
                        "autoWidth": false, "responsive": false, "scrollX": true,
                        "footerCallback": function (row, data, start, end, display) {
                            let api = this.api();
                            let intVal = function (i) {
                                if (typeof i === 'string') {
                                    let val = i.replace(/\./g, '');
                                    return val === '-' ? 0 : val * 1;
                                }
                                return typeof i === 'number' ? i : 0;
                            };

                            let startColIndex = 6;
                            let numSizes = res.available_sizes.length;
                            for (let i = 0; i < numSizes; i++) {
                                let colIndex = startColIndex + i;
                                let colTotal = api.column(colIndex, { search: 'applied' }).data().reduce((a, b) => intVal(a) + intVal(b), 0);
                                $(api.column(colIndex).footer()).html(colTotal.toLocaleString('id-ID'));
                            }
                        },
                        "drawCallback": function(settings) {
                            let api = this.api();
                            setTimeout(function() { api.columns.adjust(); }, 10);
                        }
                    });
                }

                let btnSubmit = $('#form-create-so button[type="submit"]');

                if (res.has_bom_error) {
                    btnSubmit.prop('disabled', true);

                    let htmlError = '';

                    if (res.missing_colors && res.missing_colors.length > 0) {
                        htmlError += `<p class="text-danger mb-1" style="font-size:14px;"><b>Warna di BOM tapi TIDAK ADA di Excel:</b></p>
                                      <p class="mb-3 text-dark">` + res.missing_colors.join(', ') + `</p>`;
                    }
                    if (res.missing_sizes && res.missing_sizes.length > 0) {
                        htmlError += `<p class="text-danger mb-1" style="font-size:14px;"><b>Size di BOM tapi TIDAK ADA di Excel:</b></p>
                                      <p class="mb-3 text-dark">` + res.missing_sizes.join(', ') + `</p>`;
                    }

                    if (htmlError !== '') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Data Excel Kurang Lengkap!',
                            html: htmlError + '<br><small><b>Solusi:</b> Update File Excel Anda <b>ATAU</b> hapus warna/size tersebut dari BOM.</small>',
                            confirmButtonText: 'Tutup'
                        });
                    } else {
                        // if ($('#btn-fix-bom').length === 0) {
                        //     $('#table-preview-po').before(`<button type="button" id="btn-fix-bom" class="btn btn-danger mb-2" onclick="goToEditBOM()"><i class="fas fa-tools"></i> Terdapat Material Belum Terdaftar (Klik untuk Fix di Tab Baru)</button>`);
                        // }

                        Swal.fire({
                            toast: true, position: 'top-end', icon: 'error',
                            title: 'Warna/Size tidak terdaftar di BOM!',
                            text: 'Silakan klik Edit BOM untuk menambah material.',
                            showConfirmButton: false, timer: 5000
                        });
                    }
                } else {
                    btnSubmit.prop('disabled', false);
                    $('#btn-fix-bom').remove();
                }
            },
            error: function(xhr) {
                $('#table-preview-po tbody').html('<tr><td colspan="10" class="text-center text-danger">Gagal memuat data preview.</td></tr>');
            }
        });
    }

    $(document).ready(function() {

        $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });
        $(document).on('select2:open', () => { document.querySelector('.select2-container--open .select2-search__field').focus(); });

        $('#product_group').on('change', function() {
            let selected_grup = $(this).val();
            let select_item = $('#id_product_item');
            if(selected_grup) {
                $.post("{{ route('get-product-items') }}", { _token: "{{ csrf_token() }}", product_group: selected_grup }, function(res) {
                    select_item.empty().append('<option value="">Pilih Product Item</option>');
                    $.each(res, function(key, value) { select_item.append(`<option value="${value.id}">${value.product_item}</option>`); });
                    select_item.trigger('change');
                });
            } else {
                select_item.empty().append('<option value="">Pilih Product Item</option>').trigger('change');
            }
        });

        $(document).on('input', '.input-decimal', function() {
            let val = $(this).val().replace(/[^0-9.]/g, '');
            let parts = val.split('.');
            if (parts.length > 2) val = parts[0] + '.' + parts.slice(1).join('');
            $(this).val(val);
        });

        $('#images').on('change', function(e) {
            let file = e.target.files[0];
            let reader = new FileReader();
            if (file) {
                if (!file.type.match('image.*')) {
                    Swal.fire('Peringatan!', 'File harus berupa gambar.', 'warning');
                    $(this).val(''); return;
                }
                reader.onload = function(e) {
                    $('#preview_images').attr('src', e.target.result).show();
                    $('#text_preview').hide();
                }
                reader.readAsDataURL(file);
            }
        });

        $('#id_bom').on('change', function() {
            let id_bom = $(this).val();
            let hasData = $('#table-preview-po tbody tr td').length > 1;

            if (id_bom && hasData) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Memvalidasi ulang data...', showConfirmButton: false, timer: 2000 });
                loadPreviewUpload();
            } else if (!id_bom && hasData) {
                $('#form-create-so button[type="submit"]').prop('disabled', true);
                Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Pilih BOM!', text: 'Silakan pilih Katalog BOM untuk memvalidasi data.', showConfirmButton: false, timer: 3000 });
            }
        });

        $('#file_so').off('change').on('change', function() {
            let id_bom = $('#id_bom').val();
            if (!id_bom) {
                Swal.fire('Peringatan!', 'Silakan pilih No Katalog BOM terlebih dahulu.', 'warning');
                $(this).val('');
                return;
            }

            let file = this.files[0];
            if (!file) return;

            let inputExcel = $(this);
            inputExcel.prop('disabled', true);
            let formData = new FormData();
            formData.append('file_so', file);
            formData.append('id_bom', id_bom);
            formData.append('_token', '{{ csrf_token() }}');

            Swal.fire({ title: 'Memproses Excel...', didOpen: () => { Swal.showLoading(); } });

            $.ajax({
                url: "{{ route('so-upload-excel') }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    if (res.status === 200) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false });
                        loadPreviewUpload();
                    }
                },
                error: function(xhr) {
                    let res = xhr.responseJSON;
                    if (res && res.errors) {
                        let errorList = Array.isArray(res.errors) ? res.errors : Object.values(res.errors).flat();
                        let errHtml = errorList.map(e => `<li>${e}</li>`).join('');
                        Swal.fire({ icon: 'error', title: res.message || 'Gagal!', html: `<ul class="text-left" style="font-size: 13px;">${errHtml}</ul>` });
                    } else {
                        Swal.fire('Error!', res.message || 'Gagal memproses file.', 'error');
                    }
                },
                complete: function() {
                    inputExcel.prop('disabled', false);
                    inputExcel.val('');
                }
            });
        });

        $('#form-create-so').on('submit', function(e) {
            e.preventDefault();

            let empty_input = [];

            const checkEmpty = (selector, name) => {
                let val = $(selector).val();
                if (!val || val.trim() === '') {
                    empty_input.push(name);
                }
            };

            checkEmpty('select[name="id_buyer"]', 'Buyer');
            checkEmpty('#product_group', 'Product Group');
            checkEmpty('#id_product_item', 'Product Item');
            checkEmpty('#brand', 'Brand');
            checkEmpty('#style', 'Style');
            checkEmpty('#market', 'Market');
            checkEmpty('#marketing_order', 'Marketing Order');
            checkEmpty('#smv', 'SMV');
            checkEmpty('#id_currency', 'Currency');
            checkEmpty('#confirm_price', 'Confirm Price');
            checkEmpty('#notes', 'Notes');
            checkEmpty('#id_bom', 'No Katalog BOM');
            checkEmpty('#vat', 'VAT (%)');
            checkEmpty('#ga_cost', 'GA Cost (%)');
            checkEmpty('#commission_fee', 'Commission Fee (%)');

            if ($('#images').get(0).files.length === 0) {
                empty_input.push('Upload Gambar');
            }

            let totalQty = $('#total_qty').val();
            if (!totalQty || totalQty == 0) {
                empty_input.push('Upload File Excel (Data Preview masih kosong)');
            }

            if (empty_input.length > 0) {
                let errHtml = empty_input.map(e => `<li>${e}</li>`).join('');
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Belum Lengkap!',
                    html: `Silakan isi kolom berikut sebelum menyimpan:<br><br><ul class="text-left" style="font-size: 14px;">${errHtml}</ul>`,
                    confirmButtonText: 'Mengerti'
                });
                return;
            }

            let formData = new FormData(this);
            Swal.fire({
                title: 'Simpan Sales Order?', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, Simpan', cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Sedang Proses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    $.ajax({
                        url: "{{ route('so-store') }}", type: "POST", data: formData, processData: false, contentType: false,
                        success: function(res) {
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message })
                            .then(() => { window.location.href = "{{ route('master-marketing-so') }}"; });
                        },
                        error: function(xhr) {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON.message || 'Error Server' });
                        }
                    });
                }
            });
        });

        $('#btn-back').on('click', function(e) { window.location.href = "{{ route('master-marketing-so') }}"; });
    });
</script>
@endsection
