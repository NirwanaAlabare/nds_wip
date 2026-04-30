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
    .select2-container--bootstrap4 .select2-selection--multiple {
        min-height: calc(1.5em + .5rem + 2px) !important;
    }
</style>
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Listing PO & Draft</h5>
    </div>
    <div class="card-body">

        <div class="mb-3">
            <a href="{{ route('create-purchase-order') }}" class="btn btn-outline-primary">
                <i class="fas fa-plus"></i> Create PO
            </a>
        </div>

        <div class="row align-items-end mb-4">
            <div class="col-md-2">
                <label class="small fw-bold">Tahun</label>
                <select id="filter_tahun" class="form-control form-control-sm select2bs4">
                    <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                    <option value="{{ date('Y', strtotime('-1 year')) }}">{{ date('Y', strtotime('-1 year')) }}</option>
                    <option value="{{ date('Y', strtotime('-2 year')) }}">{{ date('Y', strtotime('-2 year')) }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold">Supplier</label>
                <select id="filter_supplier" class="form-control form-control-sm select2bs4">
                    <option value="">Semua Supplier</option>
                    @foreach ($suppliers as $s)
                        <option value="{{ $s->id_supplier ?? $s->Id_Supplier }}">{{ $s->supplier ?? $s->Supplier }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Jenis PO</label>
                <select id="filter_jenis" class="form-control select2bs4" multiple="multiple" data-placeholder="Pilih Jenis">
                    <option value="draft" selected>Draft</option>
                    <option value="po" selected>PO</option>
                </select>
            </div>
            <div class="col-md-4">
                <div class="d-flex justify-content-between">
                    <div class="w-100 mr-2">
                        <label class="small fw-bold">Jumlah Draft</label>
                        <input type="text" id="count_draft" class="form-control form-control-sm text-center fw-bold bg-white" readonly value="0">
                    </div>
                    <div class="w-100">
                        <label class="small fw-bold">Jumlah PO</label>
                        <input type="text" id="count_po" class="form-control form-control-sm text-center fw-bold bg-white" readonly value="0">
                    </div>
                </div>
            </div>
            <div class="col-md-1">
                <button class="btn btn-primary btn-sm w-100" onclick="refreshTable()">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
        </div>

        <hr>

        <div id="container-draft" class="mb-5">
            <h6 class="fw-bold mb-3"> List Draft</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-custom table-hover w-100" id="table-draft">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>No PO</th>
                            <th>Jenis</th>
                            <th>Supplier</th>
                            <th>Style</th>
                            <th>Notes</th>
                            <th>User</th>
                            <th>P Terms</th>
                            <th>Status</th>
                            <th>Approved by</th>
                            <th>Tanggal App</th>
                            <th>Act</th>

                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div id="container-po">
            <h6 class="fw-bold mb-3"> List PO</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-custom table-hover w-100" id="table-po">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>No PO</th>
                            <th>ETD</th>
                            <th>ETA</th>
                            <th>Jenis</th>
                            <th>Supplier</th>
                            <th>Style</th>
                            <th>Notes</th>
                            <th>User</th>
                            <th>P Terms</th>
                            <th>Status</th>
                            <th>Approved by</th>
                            <th>Tanggal App</th>
                            <th>Act</th>

                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="modalViewPO" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-sb text-white py-2">
                <h6 class="modal-title fw-bold"><i class="fas fa-search"></i> Detail Purchase Order <span id="v_pono"></span></h6>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body bg-light">

                <h6 class="fw-bold border-bottom pb-2"><i class="fas fa-info-circle"></i> Info Header PO</h6>
                <div class="row mb-4">
                    <div class="col-md-3 mb-2"><label class="small fw-bold mb-0">No PO:</label><div id="v_pono_text" class="text-dark border px-2 py-1 rounded bg-white">-</div></div>
                    <div class="col-md-3 mb-2"><label class="small fw-bold mb-0">Tanggal PO:</label><div id="v_podate" class="text-dark border px-2 py-1 rounded bg-white">-</div></div>
                    <div class="col-md-3 mb-2"><label class="small fw-bold mb-0">Supplier:</label><div id="v_supplier" class="text-dark border px-2 py-1 rounded bg-white">-</div></div>
                    <div class="col-md-3 mb-2"><label class="small fw-bold mb-0">Jenis Item:</label><div id="v_jenis" class="text-dark border px-2 py-1 rounded bg-white">-</div></div>
                    <div class="col-md-3 mb-2"><label class="small fw-bold mb-0">P Terms (Days):</label><div id="v_terms" class="text-dark border px-2 py-1 rounded bg-white">-</div></div>
                    <div class="col-md-3 mb-2"><label class="small fw-bold mb-0">Tax:</label><div id="v_tax" class="text-dark border px-2 py-1 rounded bg-white">-</div></div>
                    <div class="col-md-3 mb-2"><label class="small fw-bold mb-0">Kurs:</label><div id="v_kurs" class="text-dark border px-2 py-1 rounded bg-white">-</div></div>
                    <div class="col-md-3 mb-2"><label class="small fw-bold mb-0">Tipe Comm:</label><div id="v_tipe" class="text-dark border px-2 py-1 rounded bg-white">-</div></div>
                    <div class="col-md-12"><label class="small fw-bold mb-0">Notes:</label><div id="v_notes" class="text-dark border px-2 py-1 rounded bg-white" style="min-height:35px;">-</div></div>
                </div>

                <h6 class="fw-bold border-bottom pb-2"><i class="fas fa-boxes"></i> Rincian Item Material / Manufacturing</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm table-custom text-nowrap" id="table-view-items">
                        <thead>
                            <tr>
                                <th>Style</th>
                                <th>Deskripsi Item</th>
                                <th>Prod Set</th>
                                <th>Qty</th>
                                <th>Unit</th>
                                <th>Convert</th>
                                <th>Qty Conv</th>
                                <th>Unit Conv</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <h6 class="fw-bold border-bottom pb-2"><i class="fas fa-money-bill-wave"></i> Rincian Biaya Lain-Lain</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-custom" id="table-view-biaya">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Keterangan</th>
                                <th>Subtotal</th>
                                <th>PPN</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot class="bg-sb text-white fw-bold">
                            <tr>
                                <td colspan="2" class="text-right">GRAND TOTAL BIAYA:</td>
                                <td id="v_biaya_subtotal" class="text-right">0</td>
                                <td id="v_biaya_ppn" class="text-right">0</td>
                                <td id="v_biaya_total" class="text-right">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm fw-bold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalEditDate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form id="form-edit-date">
                @csrf
                <div class="modal-header bg-sb text-white py-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-search"></i> Update ETD & ETA</h6>
                    <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light">
                    <input type="hidden" id="edit_date_id" name="id">

                    <div class="form-group">
                        <label class="fw-bold"><small>ETD</small></label>
                        <input type="date" class="form-control form-control-sm" id="edit_etd" name="etd">
                    </div>

                    <div class="form-group mb-0">
                        <label class="fw-bold"><small>ETA</small></label>
                        <input type="date" class="form-control form-control-sm" id="edit_eta" name="eta">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

<script>
    let tableDraft, tablePo;

    $(document).ready(function() {
        $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

    tableDraft = $('#table-draft').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("index-purchase-order") }}',
            data: function (d) {
                d.jenis = 'draft';
                d.tahun = $('#filter_tahun').val();
                d.supplier = $('#filter_supplier').val();
            }
        },
        columns: [
            {data: 'podate', name: 'podate'},
            {data: 'pono', name: 'pono'},
            {data: 'jenis', name: 'jenis'},
            {data: 'nama_supplier', name: 'nama_supplier'},
            {
                data: 'style',
                name: 'style',
                defaultContent: '-',
                render: function(data, type, row) {
                    if (data && type === 'display') {
                        return data.split(',').map(item => '- ' + $.trim(item)).join('<br>');
                    }
                    return data || '-';
                }
            },
            {data: 'notes', name: 'notes'},
            {data: 'username', name: 'username'},
            {data: 'nama_terms', name: 'nama_terms', defaultContent: '-'},
            {
                data: 'app',
                name: 'app',
                render: function(data, type, row) {
                    let badgeClass = '';
                    let badgeLabel = '';
                    if (data === 'A') {
                        badgeClass = 'success';
                        badgeLabel = 'Approved';
                    } else if (data === 'W') {
                        badgeClass = 'warning';
                        badgeLabel = 'Draft';
                    }
                    return `<span class="badge badge-${badgeClass}">${badgeLabel}</span>`;
                }
            },
            {data: 'app_by', name: 'app_by', defaultContent: '-'},
            {data: 'app_date', name: 'app_date', defaultContent: '-'},
            {data: 'action', name: 'action', orderable: false, searchable: false},

        ]
    });

    tablePo = $('#table-po').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("index-purchase-order") }}',
            data: function (d) {
                d.jenis = 'po';
                d.tahun = $('#filter_tahun').val();
                d.supplier = $('#filter_supplier').val();
            }
        },
        columns: [
            {data: 'podate', name: 'podate'},
            {data: 'pono', name: 'pono'},
            {data: 'etd', name: 'etd', defaultContent: '-'},
            {data: 'eta', name: 'eta', defaultContent: '-'},
            {data: 'jenis', name: 'jenis'},
            {data: 'nama_supplier', name: 'nama_supplier'},
            {
                data: 'style',
                name: 'style',
                defaultContent: '-',
                render: function(data, type, row) {
                    if (data && type === 'display') {
                        return data.split(',').map(item => '- ' + $.trim(item)).join('<br>');
                    }
                    return data || '-';
                }
            },
            {data: 'notes', name: 'notes'},
            {data: 'username', name: 'username'},
            {data: 'nama_terms', name: 'nama_terms', defaultContent: '-'},
            {
                data: 'app',
                name: 'app',
                render: function(data, type, row) {
                    let badgeClass = '';
                    let badgeLabel = '';
                    if (data === 'A') {
                        badgeClass = 'success';
                        badgeLabel = 'Approved';
                    } else if (data === 'W') {
                        badgeClass = 'warning';
                        badgeLabel = 'Waiting';
                    }
                    return `<span class="badge badge-${badgeClass}">${badgeLabel}</span>`;
                }
            },
            {data: 'app_by', name: 'app_by', defaultContent: '-'},
            {data: 'app_date', name: 'app_date', defaultContent: '-'},
            {data: 'action', name: 'action', orderable: false, searchable: false},

        ]
    });

        refreshTable();
    });

    function refreshTable() {
        let jenisFilter = $('#filter_jenis').val() || [];

        if (jenisFilter.includes('draft')) {
            $('#container-draft').show();
            tableDraft.ajax.reload(null, false);
        } else {
            $('#container-draft').hide();
        }

        if (jenisFilter.includes('po')) {
            $('#container-po').show();
            tablePo.ajax.reload(null, false);
        } else {
            $('#container-po').hide();
        }

        $.ajax({
            url: '{{ route("count-purchase-order") }}',
            type: 'GET',
            data: {
                tahun: $('#filter_tahun').val(),
                supplier: $('#filter_supplier').val()
            },
            success: function(res) {
                $('#count_draft').val(res.draft);
                $('#count_po').val(res.po);
            }
        });
    }

    const formatIDR = (num) => parseFloat(num || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    $(document).on('click', '.btn-view', function() {
        let id = $(this).data('id');

        let actionUrl = '{{ route("show-purchase-order", ":id") }}';
        actionUrl = actionUrl.replace(':id', id);

        Swal.fire({ title: 'Loading...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});

        $.ajax({
            url: actionUrl,
            type: 'GET',
            success: function(res) {
                Swal.close();

                let h = res.header;
                $('#v_pono').text('(' + (h.po_no || h.pono) + ')');
                $('#v_pono_text').text(h.po_no || h.pono);
                $('#v_podate').text(h.podate);
                $('#v_supplier').text(h.nama_supplier);
                $('#v_jenis').text(h.jenis === 'M' ? 'Manufacturing' : 'Material');
                $('#v_terms').text((h.jml_pterms || 0) + ' Days - ' + (h.nama_terms || '-'));
                $('#v_tax').text(h.tax || '-');
                $('#v_kurs').text(formatIDR(h.n_kurs));
                $('#v_tipe').text(h.tipe_commercial || '-');
                $('#v_notes').text(h.notes || '-');

                let tbodyItems = $('#table-view-items tbody');
                tbodyItems.empty();
                if(res.items.length > 0) {
                    $.each(res.items, function(i, val) {
                        let totalCost = parseFloat(val.qty) * parseFloat(val.price);
                        tbodyItems.append(`
                            <tr>
                                <td>${val.nama_style || '-'}</td>
                                <td>${val.itemdesc || '-'}</td>
                                <td class="text-center">${val.product_set || '-'}</td>
                                <td class="text-right">${parseFloat(val.qty_pr_awal || 0)}</td>
                                <td class="text-center">${val.unit_pr_awal || '-'}</td>
                                <td class="text-right">${parseFloat(val.convert_val || 0)}</td>
                                <td class="text-right text-dark fw-bold">${parseFloat(val.qty || 0)}</td>
                                <td class="text-center text-dark fw-bold">${val.unit || '-'}</td>
                                <td class="text-right">${formatIDR(val.price)}</td>
                            </tr>
                        `);
                    });
                } else {
                    tbodyItems.html('<tr><td colspan="10" class="text-center font-italic">Tidak ada rincian item.</td></tr>');
                }


                let tbodyBiaya = $('#table-view-biaya tbody');
                tbodyBiaya.empty();
                let sumSub = 0, sumPpn = 0, sumTot = 0;

                if(res.biaya.length > 0) {
                    $.each(res.biaya, function(i, val) {
                        sumSub += parseFloat(val.total || 0);
                        sumPpn += parseFloat(val.ppn || 0);
                        let tot = parseFloat(val.total || 0) + parseFloat(val.ppn || 0);
                        sumTot += tot;

                        tbodyBiaya.append(`
                            <tr>
                                <td>${val.nama_kategori || '-'}</td>
                                <td>${val.keterangan || '-'}</td>
                                <td class="text-right">${formatIDR(val.total)}</td>
                                <td class="text-right">${formatIDR(val.ppn)}</td>
                                <td class="text-right fw-bold">${formatIDR(tot)}</td>
                            </tr>
                        `);
                    });
                } else {
                    tbodyBiaya.html('<tr><td colspan="5" class="text-center font-italic">Tidak ada biaya lain-lain.</td></tr>');
                }

                $('#v_biaya_subtotal').text(formatIDR(sumSub));
                $('#v_biaya_ppn').text(formatIDR(sumPpn));
                $('#v_biaya_total').text(formatIDR(sumTot));

                $('#modalViewPO').modal('show');
            },
            error: function() {
                Swal.fire('Error!', 'Gagal mengambil data detail PO.', 'error');
            }
        });
    });

    $(document).on('click', '.btn-edit-date', function() {
        let id = $(this).data('id');
        let etd = $(this).data('etd');
        let eta = $(this).data('eta');

        $('#edit_date_id').val(id);
        $('#edit_etd').val(etd !== '-' ? etd : '');
        $('#edit_eta').val(eta !== '-' ? eta : '');

        $('#modalEditDate').modal('show');
    });

    $('#form-edit-date').on('submit', function(e) {
        e.preventDefault();

        let id = $('#edit_date_id').val();
        let actionUrl = '{{ route("update-date-purchase-order", ":id") }}';
        actionUrl = actionUrl.replace(':id', id);

        let formData = $(this).serialize();

        Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});

        $.ajax({
            url: actionUrl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if(response.status === 200) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: response.message, showConfirmButton: false, timer: 1500 });
                    $('#modalEditDate').modal('hide');
                    refreshTable();
                } else {
                    Swal.fire('Gagal!', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
            }
        });
    });
</script>
@endsection
