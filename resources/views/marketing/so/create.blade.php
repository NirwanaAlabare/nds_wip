@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
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
                    <label>Buyer</label>
                    <select name="id_buyer" class="form-control select2bs4">
                        <option value="">Pilih Buyer</option>
                        @foreach ($buyers as $buyer)
                            <option value="{{ $buyer->Id_Supplier }}">{{ $buyer->Supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Product Group</label>
                    <select name="product_group" id="product_group" class="form-control select2bs4">
                        <option value="">Pilih Product Group</option>
                        @foreach($product_groups as $group)
                            <option value="{{ $group->product_group }}">{{ $group->product_group }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Product Item</label>
                    <select name="id_product_item" id="id_product_item" class="form-control select2bs4">
                        <option value="">Pilih Product Item</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Brand</label>
                    <input type="text" name="brand" id="brand" class="form-control">
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Style</label>
                    <input type="text" name="style" id="style" class="form-control" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Market</label>
                    <input type="text" name="market" id="market" class="form-control">
                </div>
                <div class="col-md-3 form-group">
                    <label>Marketing Order</label>
                    <input type="text" name="marketing_order" id="marketing_order" class="form-control">
                </div>
                <div class="col-md-3 form-group">
                    <label>SMV</label>
                    <input type="text" name="smv" id="smv" class="form-control input-decimal">
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Currency</label>
                   <select name="id_currency" id="id_currency" class="form-control select2bs4">
                        <option value="">Pilih Currency</option>
                        @foreach($currency as $curr)
                            <option value="{{ $curr->id }}">{{ $curr->nama_pilihan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Confirm Price</label>
                    <input type="text" name="confirm_price" id="confirm_price" class="form-control input-decimal">
                </div>
                <div class="col-md-3 form-group">
                    <label>Total Qty</label>
                    <input type="text" name="total_qty" id="total_qty" class="form-control bg-light input-decimal" readonly>
                </div>
                <div class="col-md-3 form-group">
                    <label>Notes</label>
                    <input type="text" name="notes" id="notes" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Jumlah PO</label>
                    <input type="text" name="jumlah_po" id="jumlah_po" class="form-control bg-light input-decimal" readonly>
                </div>
                <div class="col-md-3 form-group">
                    <label>No Katalog BOM</label>
                    <select name="id_bom" id="id_bom" class="form-control select2bs4">
                        <option value="">Pilih No Katalog BOM</option>
                        @foreach($bom_catalog as $bom)
                            <option value="{{ $bom->id }}">{{ $bom->no_katalog_bom }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>VAT (%)</label>
                    <input type="text" name="vat" id="vat" class="form-control input-decimal">
                </div>
                <div class="col-md-3 form-group">
                    <label>GA Cost (%)</label>
                    <input type="text" name="ga_cost" id="ga_cost" class="form-control input-decimal">
                </div>
                <div class="col-md-3 form-group">
                    <label>Commission Fee (%)</label>
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
                    <label>Upload Gambar</label>
                    <input type="file" name="images" id="images" class="form-control-file mb-2" accept="image/*">
                    <div class="border rounded d-flex justify-content-center align-items-center bg-light" style="height: 150px; overflow: hidden;">
                        <img id="preview_images" src="" alt="Preview Gambar" style="max-height: 100%; display: none;">
                        <span id="text_preview" class="text-muted">Preview Gambar</span>
                    </div>
                </div>

                <div class="col-md-4 form-group">
                    <label>Upload File (Excel)</label>
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
                        <tbody>
                            </tbody>
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
   $(document).ready(function() {
    $('.select2bs4').select2({
        theme: 'bootstrap4',
        width: '100%',
    });

    $(document).on('select2:open', () => {
        document.querySelector('.select2-container--open .select2-search__field').focus();
    });

    $('#product_group').on('change', function() {
        let selected_grup = $(this).val();
        let select_item = $('#id_product_item');

        if(selected_grup) {
            $.ajax({
                url: "{{ route('get-product-items') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    product_group: selected_grup
                },
                success: function(res) {
                    select_item.empty().append('<option value="">Pilih Product Item</option>');
                    $.each(res, function(key, value) {
                        select_item.append(`<option value="${value.id}">${value.product_item}</option>`);
                    });
                    select_item.trigger('change');
                }
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
                $(this).val('');
                return;
            }
            reader.onload = function(e) {
                $('#preview_images').attr('src', e.target.result).show();
                $('#text_preview').hide();
            }
            reader.readAsDataURL(file);
        }
    });

    let previewTable = null;

    function loadPreviewUpload() {
        $('#table-preview-po tbody').html('<tr><td colspan="15" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading data...</td></tr>');

        $.ajax({
            url: "{{ route('so-get-temp-data') }}",
            type: 'GET',
            success: function(res) {
                if ($.fn.DataTable.isDataTable('#table-preview-po')) {
                    $('#table-preview-po').DataTable().destroy();
                }

                let headerHtml = `
                    <tr>
                        <th>Style</th>
                        <th>Desc</th>
                        <th>PO</th>
                        <th>Market</th>
                        <th>Ex Fty</th>
                        <th>Color</th>`;

                res.available_sizes.forEach(size => {
                    headerHtml += `<th class="bg-warning text-dark text-center">${size}</th>`;
                });
                headerHtml += `</tr>`;

                $('#table-preview-po thead').html(headerHtml);

                let bodyHtml = '';
                if(res.data && res.data.length > 0) {
                    res.data.forEach(row => {
                        bodyHtml += `<tr>
                            <td>${row.style || '-'}</td>
                            <td>${row.desc || '-'}</td>
                            <td>${row.po || '-'}</td>
                            <td>${row.market || '-'}</td>
                            <td>${row.ex_fty || '-'}</td>
                            <td>${row.color || '-'}</td>`;

                        res.available_sizes.forEach(size => {
                            let qty = row[size] ? Number(row[size]).toLocaleString('id-ID') : '-';
                            bodyHtml += `<td class="text-right">${qty}</td>`;
                        });

                        bodyHtml += `</tr>`;
                    });
                } else {
                    bodyHtml = `<tr><td colspan="${6 + res.available_sizes.length}" class="text-center">Belum ada data di-upload.</td></tr>`;
                }

                $('#table-preview-po tbody').html(bodyHtml);

                $('#total_qty').val(res.total_qty || 0);
                $('#jumlah_po').val(res.jumlah_po || 0);

                if (res.data && res.data.length > 0) {
                    previewTable = $('#table-preview-po').DataTable({
                        "paging": true,
                        "lengthChange": true,
                        "searching": true,
                        "ordering": false,
                        "info": true,
                        "autoWidth": false,
                        "responsive": false,
                        "scrollX": true
                    });
                }
            },
            error: function(xhr) {
                console.error("Gagal load preview pivot", xhr.responseText);
                $('#table-preview-po tbody').html('<tr><td colspan="10" class="text-center text-danger">Gagal memuat data preview.</td></tr>');
            }
        });
    }

    loadPreviewUpload();

    $('#file_so').off('change').on('change', function() {
        let file = this.files[0];
        if (!file) return;

        let inputExcel = $(this);
        inputExcel.prop('disabled', true);

        let formData = new FormData();
        formData.append('file_so', file);
        formData.append('_token', '{{ csrf_token() }}');

        Swal.fire({
            title: 'Memproses Excel...',
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: "{{ route('so-upload-excel') }}",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                if (res.status === 200) {
                    Swal.fire('Berhasil!', res.message, 'success');

                    loadPreviewUpload();

                    inputExcel.val('');
                }
            },
            error: function(xhr) {
                let res = xhr.responseJSON;
                if (res && res.errors) {
                    let errHtml = res.errors.map(e => `<li>${e}</li>`).join('');
                    Swal.fire('Gagal!', `<ul class="text-left">${errHtml}</ul>`, 'error');
                } else {
                    Swal.fire('Error!', 'Gagal memproses file.', 'error');
                }
            },
            complete: function() {
                inputExcel.prop('disabled', false);
            }
        });
    });

    $('#form-create-so').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        Swal.fire({
            title: 'Simpan Sales Order?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Sedang Proses...', didOpen: () => { Swal.showLoading(); } });

                $.ajax({
                    url: "{{ route('so-store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message })
                        .then(() => { window.location.href = "{{ route('master-marketing-so') }}"; });
                    },
                    error: function(xhr) {
                        let err = xhr.responseJSON;
                        Swal.fire({ icon: 'error', title: 'Gagal', text: err.message || 'Error Server' });
                    }
                });
            }
        });
    });

    $('#btn-back').on('click', function(e) {
        window.location.href = "{{ route('master-marketing-so') }}";
    });
});
</script>
@endsection
