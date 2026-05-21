@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0"><i class="fas fa-file-alt fa-sm"></i> Laporan Sewing Out</h5>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-end gap-3 mb-3">
            <div class="mb-3">
                <label class="form-label"><small>From</small></label>
                <input type="date" class="form-control form-control-sm" id="from" name="from" value="{{ date('Y-m-d') }}">
            </div>
            <div class="mb-3">
                <label class="form-label"><small>To</small></label>
                <input type="date" class="form-control form-control-sm" id="to" name="to" value="{{ date('Y-m-d') }}">
            </div>
            <div class="mb-3">
                <input type='button' class='btn btn-primary btn-sm' onclick="dataTableReload();" value="Tampilkan">
                <a onclick="export_excel()" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Export
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table id="datatable" class="table table-bordered table-striped table-head-fixed w-100 text-nowrap">
                <thead>
                    <tr>
                        <th>No BPB</th>
                        <th>Tgl BPB</th>
                        <th>No PO</th>
                        <th>Supplier</th>
                        <th>Buyer</th>
                        <th>Jenis Pengeluaran</th>
                        <th>Jenis Dok</th>
                        <th>WS</th>
                        <th>Style</th>
                        <th>ID JO</th>
                        <th>ID Item</th>
                        <th>Item Desc</th>
                        <th>Color</th>
                        <th>Size</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Berat Garment</th>
                        <th>Berat Karton</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th>Created User</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

<script>
let datatable = $("#datatable").DataTable({
    ordering: false,
    processing: true,
    serverSide: true,
    paging: true,
    searching: true,
    scrollY: '400px',
    scrollX: true,
    scrollCollapse: true,
    ajax: {
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        url: '{{ route("report-sewing-out") }}',
        dataType: 'json',
        dataSrc: 'data',
        data: function(d) {
            d.dateFrom = $('#from').val();
            d.dateTo   = $('#to').val();
        },
    },
    columns: [
        { data: 'no_bppb' }, { data: 'tgl_bppb' }, { data: 'no_po' },
        { data: 'supplier' }, { data: 'buyer' },
        { data: 'jenis_pengeluaran' }, { data: 'jenis_dok' },
        { data: 'kpno' }, { data: 'styleno' },
        { data: 'id_jo' }, { data: 'id_item' }, { data: 'itemdesc' },
        { data: 'color' }, { data: 'size' }, { data: 'qty' }, { data: 'unit' },
        { data: 'berat_garment' }, { data: 'berat_karton' },
        { data: 'status' }, { data: 'keterangan' }, { data: 'created_by' },
    ],
    columnDefs: [
        { targets: [14,16,17], className: 'text-end', render: (d) => parseFloat(d || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) },
        { targets: [18], render: (d) => d ? d.toUpperCase() : '-' },
    ]
});

function dataTableReload() { datatable.ajax.reload(); }

function export_excel() {
    let from = $('#from').val();
    let to   = $('#to').val();
    let url  = "{{ route('export-excel-sewing-out') }}?from=" + from + "&to=" + to;
    window.location.href = url;
}
</script>
@endsection
