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
            text-align: center;
            vertical-align: middle !important;
            font-size: 13px;
            white-space: nowrap;
        }
        .table-custom td {
            vertical-align: middle !important;
            font-size: 13px;
            white-space: nowrap;
        }
    </style>
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0"><i class="fas fa-file-invoice"></i> Report Sewing Sentral</h5>
    </div>
    <div class="card-body">

        <div class="row align-items-end mb-4">
            <div class="col-md-2 mb-2">
                <label class="small fw-bold">Tipe Report</label>
                <select id="filter_tipe" class="form-control form-control-sm select2bs4">
                    @foreach($list_tipe as $key => $val)
                        <option value="{{ $key }}">{{ $val }}</option>
                    @endforeach
                </select>
            </div>
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
            <div class="col-md-3 mb-2 d-flex gap-2">
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
                        <th>Buyer</th>
                        <th>WS</th>
                        <th>Style</th>
                        <th>Color</th>
                        <th>Size</th>
                        <th id="label_jumlah">Jumlah</th>
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


    const routeMap = {
        'output': {
            data: "{{ route('report.output.data') }}",
            export: "{{ route('report.output.export') }}"
        },
        'defect': {
            data: "{{ route('report.defect.data') }}",
            export: "{{ route('report.defect.export') }}"
        },
        'mending': {
            data: "{{ route('report.mending.data') }}",
            export: "{{ route('report.mending.export') }}"
        },
        'spotcleaning': {
            data: "{{ route('report.spotcleaning.data') }}",
            export: "{{ route('report.spotcleaning.export') }}"
        },
        'rework': {
            data: "{{ route('report.rework.data') }}",
            export: "{{ route('report.rework.export') }}"
        },
        'rework_mending': {
            data: "{{ route('report.rework_mending.data') }}",
            export: "{{ route('report.rework_mending.export') }}"
        },
        'rework_spotcleaning': {
            data: "{{ route('report.rework_spotcleaning.data') }}",
            export: "{{ route('report.rework_spotcleaning.export') }}"
        },
        'reject': {
            data: "{{ route('report.reject.data') }}",
            export: "{{ route('report.reject.export') }}"
        }
    };

    $(document).ready(function() {
        $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

        initDatatable('output');
    });

    function initDatatable(tipe) {
        if ($.fn.DataTable.isDataTable('#datatable')) {
            $('#datatable').DataTable().destroy();
        }
        console.log("Initializing datatable with tipe:", tipe);
        console.log("Using AJAX URL:", routeMap[tipe].data);
        table = $('#datatable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: routeMap[tipe].data,
                data: function(d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.buyer = $('#filter_buyer').val();
                },
                error: function() {
                    Swal.fire('Error', 'Gagal memuat data dari controller ' + tipe, 'error');
                }
            },
            columns: [
                { data: 'buyer' },
                { data: 'ws' },
                { data: 'style' },
                { data: 'color' },
                { data: 'size' },
                {
                    data: null,
                    className: 'text-center fw-bold',
                    render: function(data, type, row) {

                        let keys = Object.keys(row);
                        let lastKey = keys[keys.length - 1];
                        let val = row[lastKey];
                        return parseFloat(val || 0).toLocaleString('id-ID');
                    }
                }
            ]
        });
    }

    function refreshTable() {
        let tipe = $('#filter_tipe').val();
        let tipeText = $("#filter_tipe option:selected").text();

        $("#label_jumlah").text("Jumlah " + tipeText);
        console.log("Refreshing table with tipe:", tipe);
        initDatatable(tipe);
    }

    function exportExcel() {
        let tipe = $('#filter_tipe').val();
        let start = $('#start_date').val();
        let end = $('#end_date').val();
        let buyer = $('#filter_buyer').val();

        if(!start || !end) {
            Swal.fire('Perhatian', 'Rentang tanggal harus diisi!', 'warning');
            return;
        }

        Swal.fire({
            title: 'Exporting...',
            text: 'Sedang menyiapkan file Excel',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        let url = `${routeMap[tipe].export}?start_date=${start}&end_date=${end}&buyer=${encodeURIComponent(buyer)}`;
        window.location.href = url;

        setTimeout(() => { Swal.close(); }, 2000);
    }
</script>
@endsection
