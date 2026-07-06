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
</style>
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0"><i class="fas fa-history"></i> History Item Purchasing</h5>
    </div>
    <div class="card-body">

        <div class="row align-items-end mb-4 bg-light p-3 rounded border">
            <div class="col-md-8">
                <label class="small fw-bold">Cari Item</label>
                <select id="item_search" class="form-control form-control-sm" style="width: 100%"></select>
            </div>
            <div class="col-md-4 mt-2 mt-md-0">
                <button class="btn btn-success btn-sm fw-bold px-3" onclick="exportExcel()">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>

        <hr>

        <div id="empty-state" class="text-center text-muted py-5">
            <i class="fas fa-search fa-2x mb-2"></i>
            <p class="mb-0">Silakan cari dan pilih item untuk melihat history transaksi PO-nya.</p>
        </div>

        <div class="table-responsive d-none" id="wrap-table-report">
            <table class="table table-bordered table-sm table-custom table-hover w-100 text-nowrap" id="table-report">
                <thead>
                    <tr>
                        <th>Tanggal PO</th>
                        <th>No PO</th>
                        <th>Jenis</th>
                        <th>Supplier</th>
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
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

<script>
    let tableReport;
    let currentIdItem = null;

    $(document).ready(function() {
        $('#item_search').select2({
            theme: 'bootstrap4',
            placeholder: 'Ketik nama item atau kode barang...',
            minimumInputLength: 2,
            ajax: {
                url: '{{ route("history-item-purchasing-search-item") }}',
                dataType: 'json',
                delay: 300,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return { results: data.results };
                }
            }
        });

        $(document).on('select2:open', function () {
            setTimeout(function () {
                document.querySelector('.select2-container--open .select2-search__field').focus();
            }, 50);
        });

        $('#item_search').on('select2:select', function (e) {
            currentIdItem = e.params.data.id;

            $('#empty-state').addClass('d-none');
            $('#wrap-table-report').removeClass('d-none');

            if (!tableReport) {
                tableReport = $('#table-report').DataTable({
                    processing: true,
                    serverSide: true,
                    scrollY: "500px",
                    scrollX: true,
                    scrollCollapse: true,
                    ajax: {
                        url: '{{ route("history-item-purchasing") }}',
                        data: function (d) {
                            d.id_item = currentIdItem;
                        }
                    },
                    columns: [
                        {data: 'podate', name: 'podate', className: 'text-center'},
                        {data: 'pono', name: 'pono'},
                        {data: 'jenis', name: 'jenis', className: 'text-center'},
                        {data: 'nama_supplier', name: 'nama_supplier'},
                        {data: 'qty', name: 'qty', className: 'text-right fw-bold'},
                        {data: 'unit', name: 'unit', className: 'text-center fw-bold'},
                        {data: 'price', name: 'price', className: 'text-right'},
                        {data: 'total_price', name: 'total_price', className: 'text-right', orderable: false, searchable: false},
                    ]
                });
            } else {
                tableReport.ajax.reload();
            }
        });

        $('#item_search').on('select2:clear', function () {
            currentIdItem = null;
            $('#wrap-table-report').addClass('d-none');
            $('#empty-state').removeClass('d-none');
        });
    });

    function exportExcel() {
        if (!currentIdItem) {
            return Swal.fire('Perhatian!', 'Silakan pilih item terlebih dahulu sebelum export.', 'warning');
        }

        let url = '{{ route("history-item-purchasing-export") }}';
        window.location.href = url + '?id_item=' + currentIdItem;
    }
</script>
@endsection
