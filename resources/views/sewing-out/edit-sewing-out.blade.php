@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
<form action="{{ route('update-sewing-out', $header->id) }}" method="post" id="form-edit-sewing-out" onsubmit="validateAndSubmit(this, event)">
    @csrf
    @method('PUT')

    {{-- Header --}}
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">Edit Sewing Out - {{ $header->no_bppb }}</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <div class="form-group row">
                <div class="col-md-4">
                    <div class="row">
                        <div class="col-md-12 mb-1">
                            <label><small>Transaction No</small></label>
                            <input type="text" class="form-control" value="{{ $header->no_bppb }}" readonly>
                        </div>
                        <div class="col-md-12 mb-1">
                            <label><small>Transaction Date</small></label>
                            <input type="date" class="form-control" id="txt_tgl_bppb" name="txt_tgl_bppb" value="{{ $header->tgl_bppb }}">
                        </div>
                        <div class="col-md-12 mb-1">
                            <label><small>No PO</small></label>
                            <select class="form-control select2bs4" id="txt_no_po" name="txt_no_po" style="width:100%;" onchange="detail_po()">
                                <option value="">Select PO</option>
                                @foreach($no_po as $po)
                                <option value="{{ $po->pono }}" {{ $header->no_po == $po->pono ? 'selected' : '' }}>{{ $po->pono }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="row">
                        <div class="col-md-12 mb-1">
                            <label><small>Send To</small></label>
                            <select class="form-control select2bs4" id="txt_supp" name="txt_supp" style="width:100%;">
                                <option value="">Select Supplier</option>
                                @foreach($msupplier as $s)
                                <option value="{{ $s->id_supplier }}" {{ $header->id_supplier == $s->id_supplier ? 'selected' : '' }}>{{ $s->Supplier }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-1">
                            <label><small>Notes</small></label>
                            <textarea rows="5" class="form-control" name="txt_notes">{{ $header->keterangan }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Saved Items --}}
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">Saved Items</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm" id="tbl_existing">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">No</th>
                            <th>WS #</th>
                            <th>Style</th>
                            <th>Job Order</th>
                            <th>Item Desc</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th class="text-center">Qty</th>
                            <th>Unit</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="existing_tbody">
                        @foreach($detail as $i => $d)
                        @php $maxQty = max(0, (float)$d->max_qty); @endphp
                        <tr id="row_det_{{ $d->id }}">
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $d->kpno }}</td>
                            <td>{{ $d->styleno }}</td>
                            <td>{{ $d->jo_no }}</td>
                            <td>{{ $d->itemdesc }}</td>
                            <td>{{ $d->color }}</td>
                            <td>{{ $d->size }}</td>
                            <td class="text-center">
                                <input type="hidden" name="det_id[{{ $i }}]" value="{{ $d->id }}">
                                <input type="text" inputmode="decimal" autocomplete="off"
                                       class="form-control form-control-sm text-right"
                                       name="det_qty[{{ $i }}]"
                                       value="{{ $d->qty }}"
                                       style="width:90px;display:inline-block;"
                                       data-max="{{ $maxQty }}"
                                       oninput="validateExistingQty(this)">
                                <small class="text-muted d-block" style="font-size:0.65rem;">max: {{ number_format($maxQty, 2) }}</small>
                            </td>
                            <td>{{ $d->unit }}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteDet({{ $d->id }})">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add New Item --}}
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">Add New Item</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-head-fixed w-100 text-nowrap">
                    <thead>
                        <tr>
                            <th class="text-center" style="font-size:0.6rem;">WS #</th>
                            <th class="text-center" style="font-size:0.6rem;">Style No</th>
                            <th class="text-center" style="font-size:0.6rem;">Job Order</th>
                            <th class="text-center" style="font-size:0.6rem;">ID Item</th>
                            <th class="text-center" style="font-size:0.6rem;">Item Desc</th>
                            <th class="text-center" style="font-size:0.6rem;">Unit</th>
                            <th class="text-center" style="font-size:0.6rem;">Qty In</th>
                            <th class="text-center" style="font-size:0.6rem;">Qty Out</th>
                            <th class="text-center" style="font-size:0.6rem;">Qty Input</th>
                            <th class="text-center" style="font-size:0.6rem;">Balance</th>
                            <th class="text-center" style="font-size:0.6rem;">Add Data</th>
                            <th style="display:none;"></th>
                            <th style="display:none;"></th>
                            <th style="display:none;"></th>
                            <th style="display:none;"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="mt-2">
                <a href="{{ route('sewing-out') }}" class="btn btn-danger float-end mt-2" onclick="clearTemp()">
                    <i class="fas fa-arrow-circle-left"></i> Back
                </a>
                <button class="btn btn-sb float-end mt-2 me-2">
                    <i class="fa-solid fa-floppy-disk"></i> Update
                </button>
            </div>
        </div>
    </div>
</form>

{{-- Modal Add Detail --}}
<div class="modal fade" id="modal_add_detail">
    <form action="{{ route('save-out-detail-temp-sewing') }}" method="post" onsubmit="submitFormScan(this, event)">
        @method('POST')
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h4 class="modal-title">List Item</h4>
                    <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-12">
                            <label><small>Buyer</small></label>
                            <input type="text" class="form-control" id="mdl_buyer" name="mdl_buyer" readonly>
                        </div>
                        <div class="col-6 mt-1">
                            <label><small>WS #</small></label>
                            <input type="text" class="form-control" id="mdl_ws" name="mdl_ws" readonly>
                        </div>
                        <div class="col-6 mt-1">
                            <label><small>Qty</small></label>
                            <div class="input-group">
                                <input type="text" class="form-control" style="text-align:right;" id="mdl_qty" name="mdl_qty" readonly>
                                <span class="input-group-text bg-success text-white">PCS</span>
                            </div>
                            <input type="hidden" id="mdl_qty_h" name="mdl_qty_h">
                        </div>
                    </div>
                    <div id="detail_showitem"></div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close"></i> Close</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-thumbs-up"></i> Save</button>
                </div>
            </div>
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
    $(document).on('select2:open', function() {
        setTimeout(() => {
            let sf = document.querySelector('.select2-container--open .select2-search__field');
            if (sf) sf.focus();
        }, 0);
    });
    $('.select2bs4').select2({ theme: 'bootstrap4' });
    detail_po();
});

function validateAndSubmit(form, evt) {
    evt.preventDefault();
    let errors = [];
    let noPo = $('#txt_no_po').val();
    let supp = $('#txt_supp').val();

    if (!noPo) {
        errors.push('No PO');
        $('#txt_no_po').next('.select2-container').find('.select2-selection').css('border-color','#dc3545');
    } else {
        $('#txt_no_po').next('.select2-container').find('.select2-selection').css('border-color','');
    }
    if (!supp) {
        errors.push('Send To');
        $('#txt_supp').next('.select2-container').find('.select2-selection').css('border-color','#dc3545');
    } else {
        $('#txt_supp').next('.select2-container').find('.select2-selection').css('border-color','');
    }
    if (errors.length > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Required fields!',
            html: '<b>' + errors.join('</b> and <b>') + '</b> must be filled.',
            confirmButtonText: 'OK',
        });
        return;
    }
    submitForm(form, evt);
}

// Validation for existing item qty (cannot exceed max_qty)
function validateExistingQty(el) {
    let max   = parseFloat(el.dataset.max) || 0;
    let input = parseFloat(el.value) || 0;
    if (input > max) {
        el.classList.add('is-invalid');
        el.value = max;
    } else if (input < 0) {
        el.classList.add('is-invalid');
        el.value = 0;
    } else {
        el.classList.remove('is-invalid');
    }
}

function submitFormScan(e, evt) {
    evt.preventDefault();
    $.ajax({
        url: e.getAttribute('action'),
        type: e.getAttribute('method'),
        data: new FormData(e),
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.status == 200) {
                $('.modal').modal('hide');
                Swal.fire({ icon: 'success', title: res.message, confirmButtonText: 'OK', timer: 3000, timerProgressBar: true })
                    .then(() => { detail_po(); });
                e.reset();
            } else {
                iziToast.error({ title: 'Error', message: res.message, position: 'topCenter' });
            }
        }
    });
}

async function detail_po() {
    return datatable.ajax.reload();
}

let datatable = $("#datatable").DataTable({
    ordering: false,
    processing: true,
    serverSide: false,
    paging: false,
    searching: true,
    scrollY: '300px',
    scrollX: '300px',
    scrollCollapse: true,
    ajax: {
        url: '{{ route("get-detail-item-sewing-out") }}',
        data: function(d) { d.pono = $('#txt_no_po').val(); }
    },
    columns: [
        { data: 'kpno' }, { data: 'styleno' }, { data: 'jo_no' },
        { data: 'id_item' }, { data: 'itemdesc' }, { data: 'unit' },
        { data: 'qty' }, { data: 'qty_out' }, { data: 'qty_input' },
        { data: 'qty_balance' }, { data: 'id_po' },
        { data: 'id_jo' }, { data: 'id_item' }, { data: 'unit' }, { data: 'id_po' }
    ],
    columnDefs: [
        { targets: [6,7,9], render: (data) => data ? data : '0' },
        { targets: [8], render: (data, type, row, meta) =>
            `<input style="width:80px;text-align:center;" type="text" id="input_qty${meta.row}" name="input_qty[${meta.row}]" value="${data}" readonly />`
        },
        { targets: [10], render: (data, type, row) =>
            `<div class='d-flex gap-1 justify-content-center'>
                <button type='button' class='btn btn-sm btn-info' onclick='get_data_detail("${row.id_po}","${row.id_jo}","${row.id_item}","${row.buyer}","${row.kpno}")'><i class="fas fa-plus-square"></i> Add</button>
                <button type='button' class='btn btn-sm btn-danger' onclick='delete_temp("${row.id_po}","${row.id_jo}","${row.id_item}")'><i class="fa-solid fa-undo"></i> Undo</button>
            </div>`
        },
        { targets: [11], className: 'd-none', render: (data, type, row, meta) =>
            `<input type="text" id="id_jo${meta.row}" name="id_jo[${meta.row}]" value="${data}" readonly />`
        },
        { targets: [12], className: 'd-none', render: (data, type, row, meta) =>
            `<input type="text" id="id_item${meta.row}" name="id_item[${meta.row}]" value="${data}" readonly />`
        },
        { targets: [13], className: 'd-none', render: (data, type, row, meta) =>
            `<input type="text" id="unit${meta.row}" name="unit[${meta.row}]" value="${data}" readonly />`
        },
        { targets: [14], className: 'd-none', render: (data, type, row, meta) =>
            `<input type="text" id="id_po${meta.row}" name="id_po[${meta.row}]" value="${data}" readonly />`
        },
    ]
});

function get_data_detail(id_po, id_jo, id_item, buyer, ws) {
    getlist_showitem(id_po, id_item, id_jo);
    $('#mdl_buyer').val(buyer);
    $('#mdl_ws').val(ws);
    $('#mdl_qty').val('');
    $('#mdl_qty_h').val('');
    $('#modal_add_detail').modal('show');
}

function getlist_showitem(id_po, id_item, id_jo) {
    let no_po = $('#txt_no_po').val();
    $.ajax({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        url: '{{ route("show-detail-so-sewing-out") }}',
        type: 'get',
        data: { id_po, id_item, id_jo, no_po },
        success: function(res) {
            if (res) {
                document.getElementById('detail_showitem').innerHTML = res;
                $('#tableshow').DataTable({ paging: false, searching: true, scrollY: '300px', scrollX: true, scrollCollapse: true });
            }
        }
    });
}

function validate_qty(el) {
    let stok  = parseFloat(el.dataset.stok) || 0;
    let input = parseFloat(el.value) || 0;
    if (input > stok) {
        el.classList.add('is-invalid');
        el.value = stok;
    } else if (input < 0) {
        el.classList.add('is-invalid');
        el.value = 0;
    } else {
        el.classList.remove('is-invalid');
    }
    sum_qty_item();
}

function sum_qty_item() {
    let table = document.getElementById("tableshow");
    if (!table) return;
    let sum_out = 0;
    for (let i = 1; i < table.rows.length; i++) {
        sum_out += parseFloat($("#det_qty" + i).val()) || 0;
    }
    let rounded = Math.round(sum_out * 100) / 100;
    $('#mdl_qty').val(new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(rounded));
    $('#mdl_qty_h').val(rounded);
}

function delete_temp(id_po, id_jo, id_item) {
    $.ajax({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        url: '{{ route("delete-out-detail-temp-sewing") }}',
        type: 'get',
        data: { id_po, id_jo, id_item },
        success: function() { detail_po(); }
    });
}

function deleteDet(id) {
    Swal.fire({
        title: 'Delete this item?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.get("{{ route('delete-sewing-out-det', ':id') }}".replace(':id', id), function(res) {
                if (res.status === 200) {
                    $('#row_det_' + id).fadeOut(300, function() { $(this).remove(); renumberRows(); });
                    detail_po();
                }
            });
        }
    });
}

function renumberRows() {
    $('#existing_tbody tr:visible').each(function(i) {
        $(this).find('td:first').text(i + 1);
    });
}

function clearTemp() {
    $.ajax({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        url: '{{ route("delete-all-temp") }}',
        type: 'get',
        data: { no_bppb: '' }
    });
}
</script>
@endsection
