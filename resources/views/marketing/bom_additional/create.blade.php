@extends('layouts.index')

@section('content')
<div class="card card-primary">
    <div class="card-header bg-sb">
        <h5 class="card-title fw-bold mb-0">Buat Katalog BOM Additional Baru</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label"><small class="fw-bold">Pilih Sales Order (SO)</small></label>
                <select name="id_so" id="id_so" class="form-control select2bs4">
                    <option value="">Pilih SO</option>
                    @foreach ($list_so as $so)
                        <option value="{{ $so->id }}">{{ $so->so_no }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label"><small class="fw-bold">Buyer</small></label>
                <input type="text" class="form-control bg-light" id="buyer" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label"><small class="fw-bold">Style</small></label>
                <input type="text" class="form-control bg-light" id="style" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label"><small class="fw-bold">Market</small></label>
                <input type="text" class="form-control bg-light" id="market" readonly>
            </div>
        </div>

        <div class="row mt-4" id="section-po-list" style="display: none;">
            <div class="col-12">
                <label class="form-label"><small class="fw-bold text-info"><i class="fas fa-check-square"></i> Pilih PO yang Akan Digunakan</small></label>
                <div class="table-responsive border rounded" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="bg-light" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th width="5%" class="text-center"><input type="checkbox" id="check-all-po" style="transform: scale(1.2);"></th>
                                <th>No PO</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th class="text-right">Total Qty</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-po"></tbody>
                    </table>
                </div>
            </div>
            <div class="col-12 text-right mt-3">
                <button type="button" class="btn btn-primary btn-md" onclick="createHeader()">
                    <i class="fas fa-save"></i> Buat Header BOM
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.select2bs4').select2({ theme: 'bootstrap4' });

        $('#id_so').on('change', function() {
            let id_so = $(this).val();
            if(!id_so) {
                $('#buyer, #style, #market').val('');
                $('#section-po-list').hide();
                return;
            }
            Swal.fire({ title: 'Menarik Data SO...', didOpen: () => { Swal.showLoading(); }});

            $.get("{{ route('get-po-by-so') }}", { id_so: id_so }, function(res) {
                Swal.close();
                $('#buyer').val(res.so_data.buyer);
                $('#style').val(res.so_data.style);
                $('#market').val(res.so_data.market);

                let htmlPO = '';
                if(res.po_list.length > 0) {
                    res.po_list.forEach(function(po) {
                        let uniqueVal = `${po.no_po}_${po.id_color}_${po.id_size}`;

                        htmlPO += `<tr>
                            <td class="text-center"><input type="checkbox" class="check-po" value="${uniqueVal}"></td>
                            <td>${po.no_po}</td>
                            <td>${po.color_name}</td>
                            <td>${po.size_name}</td>
                            <td class="text-right">${po.qty}</td>
                        </tr>`;
                    });
                }
                $('#tbody-po').html(htmlPO);
                $('#section-po-list').slideDown();
            });
        });

        $('#check-all-po').on('change', function() {
            $('.check-po').prop('checked', this.checked);
        });
    });

    function createHeader() {
        let id_so = $('#id_so').val();
        let po_array = [];
        $('.check-po:checked').each(function() { po_array.push($(this).val()); });

        if (po_array.length === 0) {
            return Swal.fire('Peringatan', 'Centang minimal 1 No PO!', 'warning');
        }

        Swal.fire({
            title: 'Buat BOM Additional?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Buat'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Memproses...', didOpen: () => { Swal.showLoading(); } });
                $.post("{{ route('store-bom-additional-header') }}", {
                    _token: "{{ csrf_token() }}", id_so: id_so, po_list: po_array
                }, function(res) {
                    if(res.status == 200) {
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Beralih ke halaman detail...', timer: 1500, showConfirmButton: false })
                        .then(() => {
                            let editUrl = "{{ route('edit-bom-additional', ':id') }}";
                            window.location.href = editUrl.replace(':id', res.id);
                        });
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                });
            }
        });
    }
</script>
@endsection
