@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">

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
</style>
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0"><i class="fas fa-file-alt"></i> Laporan Item PO</h5>
    </div>
    <div class="card-body">

        <div class="row align-items-end mb-4 bg-light p-3 rounded border">
            <div class="col-md-2">
                <label class="small fw-bold">Dari Tanggal</label>
                <input type="date" id="tgl_awal" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Sampai Tanggal</label>
                <input type="date" id="tgl_akhir" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-8 text-left mt-2 mt-md-0">
                <button class="btn btn-primary btn-sm fw-bold px-3 mr-1" onclick="refreshTable()">
                    <i class="fas fa-search"></i> Tampilkan Data
                </button>
                <button class="btn btn-success btn-sm fw-bold px-3" onclick="exportExcel()">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>

        <hr>

        <div class="table-responsive">
            <table class="table table-bordered table-sm table-custom table-hover w-100 text-nowrap" id="table-report">
                <thead>
                    <tr>
                        <th>Tanggal PO</th>
                        <th>No PO</th>
                        <th>Jenis</th>
                        <th>Supplier</th>
                        <th>Item Description</th>
                        <th>Set</th>
                        <th>Qty Awal</th>
                        <th>Unit Awal</th>
                        <th>Convert</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Price</th>
                        <th>Total Price</th>
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

<script>
    let tableReport;

    $(document).ready(function() {
        tableReport = $('#table-report').DataTable({
            processing: true,
            serverSide: true,
            scrollY: "500px",
            scrollX: true,
            scrollCollapse: true,
            ajax: {
                url: '{{ route("item-report-purchasing") }}',
                data: function (d) {
                    d.tgl_awal = $('#tgl_awal').val();
                    d.tgl_akhir = $('#tgl_akhir').val();
                }
            },
            columns: [
                {data: 'podate', name: 'podate', className: 'text-center'},
                {data: 'pono', name: 'pono'},
                {data: 'jenis', name: 'jenis', className: 'text-center'},
                {data: 'nama_supplier', name: 'nama_supplier'},
                {data: 'itemdesc', name: 'itemdesc'},
                {data: 'product_set', name: 'product_set', defaultContent: '-', className: 'text-center'},
                {data: 'qty_pr_awal', name: 'qty_pr_awal', className: 'text-right'},
                {data: 'unit_pr_awal', name: 'unit_pr_awal', className: 'text-center'},
                {data: 'convert_val', name: 'convert_val', className: 'text-right'},
                {data: 'qty', name: 'qty', className: 'text-right fw-bold'},
                {data: 'unit', name: 'unit', className: 'text-center fw-bold'},
                {data: 'price', name: 'price', className: 'text-right'},
                {data: 'total_price', name: 'total_price', className: 'text-right'},
            ]
        });
    });

    function refreshTable() {
        tableReport.ajax.reload();
    }

    function exportExcel() {
        let tgl_awal = $('#tgl_awal').val();
        let tgl_akhir = $('#tgl_akhir').val();

        if (!tgl_awal || !tgl_akhir) {
            return Swal.fire('Perhatian!', 'Harap isi range tanggal terlebih dahulu.', 'warning');
        }

        let url = '{{ route("export-item-report-purchasing") }}';
        let fullUrl = url + '?tgl_awal=' + tgl_awal + '&tgl_akhir=' + tgl_akhir;

        window.location.href = fullUrl;
    }
</script>
@endsection
