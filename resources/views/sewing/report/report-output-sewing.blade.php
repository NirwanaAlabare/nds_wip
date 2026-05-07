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
            white-space: nowrap;
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
        <h5 class="card-title fw-bold mb-0"><i class="fas fa-list-alt"></i> Report Output Sewing</h5>
    </div>
    <div class="card-body">

        <div class="row align-items-end mb-4">
            <div class="col-md-3 mb-2">
                <label class="small fw-bold">Buyer</label>
                <select id="filter_buyer" class="form-control form-control-sm select2bs4">
                    <option value="">Semua Buyer</option>
                    @foreach($list_buyer as $b)
                        <option value="{{ $b->supplier }}">{{ $b->supplier }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="small fw-bold">Tgl Awal</label>
                <input type="date" id="start_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-2 mb-2">
                <label class="small fw-bold">Tgl Akhir</label>
                <input type="date" id="end_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-4 mb-2 d-flex gap-2">
                <button class="btn btn-primary btn-sm" onclick="refreshTable()">
                    <i class="fa fa-search"></i> Cari
                </button>
                <button class="btn btn-success btn-sm" onclick="exportExcel()">
                    <i class="fa fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>

        <hr>

        <div class="table-responsive">
            <table id="datatable" class="table table-bordered table-sm table-custom table-hover w-100">
                <thead>
                    <tr>
                        <th width="15%">Buyer</th>
                        <th width="10%">WS</th>
                        <th width="15%">Style</th>
                        <th width="20%">Color</th>
                        <th width="10%">Size</th>
                        <th width="15%">Jumlah Output Sewing</th>
                    </tr>
                </thead>
                <tbody>
                    </tbody>
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
    let table;

    $(document).ready(function() {

        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: '100%'
        });


        table = $('#datatable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: "{{ route('report-sewing.output.data') }}",
                data: function(d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.buyer = $('#filter_buyer').val();
                }
            },
            columns: [
                { data: 'buyer', render: function(data, type, row) {
                    return `<span class="text-center">${data}</span>`;
                }},
                { data: 'ws', render: function(data, type, row) {
                    return `<span class="text-center">${data}</span>`;
                }},
                { data: 'style', render: function(data, type, row) {
                    return `<span class="text-center">${data}</span>`;
                }},
                { data: 'color', render: function(data, type, row) {
                    return `<span class="text-center">${data}</span>`;
                }},
                { data: 'size', render: function(data, type, row) {
                    return `<span class="text-center">${data}</span>`;
                }},
                {
                    data: 'jumlah_output_sewing',
                    render: $.fn.dataTable.render.number(',', '.', 0),
                    className: 'text-center fw-bold'
                }
            ]
        });
    });

    function refreshTable() {
        table.ajax.reload(null, false);
    }

    function exportExcel() {
        let start = $('#start_date').val();
        let end = $('#end_date').val();
        let buyer = $('#filter_buyer').val();

        if(!start || !end) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Tgl Awal dan Tgl Akhir tidak boleh kosong!'
            });
            return;
        }

        Swal.fire({ title: 'Menyiapkan Excel...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});

        let url = `{{ route('report-sewing.output.export') }}?start_date=${start}&end_date=${end}&buyer=${encodeURIComponent(buyer)}`;
        window.location.href = url;

        setTimeout(() => { Swal.close(); }, 1500);
    }
</script>
@endsection
