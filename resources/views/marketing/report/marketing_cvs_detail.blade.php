@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <style type="text/css">
        .form-control {
            border: 1.5px solid #ced4da;
            border-radius: 8px;
            padding: 6px 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.25);
        }

        .dataTables_length select {
            width: auto;
            min-width: 65px;
            padding-right: 24px;
        }

        .td-truncate {
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Costing Vs Sales Order Detail
                (SB)</h5>
        </div>
        <div class="card-body">
            <div class="mb-3 d-flex align-items-end gap-2 flex-wrap">
                <div>
                    <label for="txttgl_awal" class="col-form-label"><small><b>Tgl Awal :</b></small></label>
                    <input type="date" id="txttgl_awal" class="form-control form-control-sm">
                </div>
                <div>
                    <label for="txttgl_akhir" class="col-form-label"><small><b>Tgl Akhir :</b></small></label>
                    <input type="date" id="txttgl_akhir" class="form-control form-control-sm">
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="dataTableReload();">
                    <i class="fas fa-search"></i> Search
                </button>
                <button type="button" class="btn btn-success btn-sm" onclick="export_excel_marketing_cvs_detail();">
                    <i class="fas fa-file-excel"></i> Export
                </button>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th rowspan="2" class="text-center align-middle">No</th>
                            <th rowspan="2" class="text-center align-middle">WS</th>
                            <th rowspan="2" class="text-center align-middle">Buyer</th>
                            <th rowspan="2" class="text-center align-middle">Style</th>
                            <th rowspan="2" class="text-center align-middle">Product Type</th>
                            <th rowspan="2" class="text-center align-middle">Season</th>
                            <th rowspan="2" class="text-center align-middle">Marketing Order</th>
                            <th rowspan="2" class="text-center align-middle">Curr</th>
                            <th colspan="4" class="text-center align-middle">Sales Order</th>
                            <th colspan="31" class="text-center align-middle">Costing Breakdown</th>
                            <th rowspan="2" class="text-center align-middle">Sales Price - Total Cost</th>
                        </tr>
                        <tr>
                            <th class="text-center align-middle">SO Date</th>
                            <th class="text-center align-middle">Status SO</th>
                            <th class="text-center align-middle">Qty SO</th>
                            <th class="text-center align-middle">Sales Price</th>
                            <th class="text-center align-middle">CT Date</th>
                            <th class="text-center align-middle">Status</th>
                            <th class="text-center align-middle">Qty</th>
                            <th class="text-center align-middle">Fabric</th>
                            <th class="text-center align-middle">Acc Sewing</th>
                            <th class="text-center align-middle">Acc Packing</th>
                            <th class="text-center align-middle">Total Material</th>
                            <th class="text-center align-middle">CMT</th>
                            <th class="text-center align-middle">Embrodery</th>
                            <th class="text-center align-middle">Washing</th>
                            <th class="text-center align-middle">Printing</th>
                            <th class="text-center align-middle">Wrapped Button</th>
                            <th class="text-center align-middle">Complexity Makloon Button</th>
                            <th class="text-center align-middle">Label Print</th>
                            <th class="text-center align-middle">Laser Cutting</th>
                            <th class="text-center align-middle">Total Manufaturing</th>
                            <th class="text-center align-middle">Development</th>
                            <th class="text-center align-middle">Overhead</th>
                            <th class="text-center align-middle">Marketing</th>
                            <th class="text-center align-middle">Shipping</th>
                            <th class="text-center align-middle">Import</th>
                            <th class="text-center align-middle">Handing</th>
                            <th class="text-center align-middle">Testing</th>
                            <th class="text-center align-middle">Fabric Handling</th>
                            <th class="text-center align-middle">Service Charge</th>
                            <th class="text-center align-middle">Clearance Cost</th>
                            <th class="text-center align-middle">Unexpected Cost</th>
                            <th class="text-center align-middle">Management Fee</th>
                            <th class="text-center align-middle">Profit</th>
                            <th class="text-center align-middle">Total Others</th>
                            <th class="text-center align-middle">Total Cost</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        // Modul Asset: senyapkan alert bawaan DataTables saat ajax gagal, cukup dicatat di console
        $.fn.dataTable.ext.errMode = function(settings, techNote, message) {
            console.error('DataTable ajax error:', message);
        };

        // Default filter tanggal awal & akhir ke tanggal hari ini
        let todayStr = new Date().toISOString().slice(0, 10);
        $('#txttgl_awal').val(todayStr);
        $('#txttgl_akhir').val(todayStr);

        function dataTableReload() {
            datatable.ajax.reload();
        }

        // Format tanggal dari 'YYYY-MM-DD' (atau 'YYYY-MM-DD HH:mm:ss') ke 'DD-MM-YYYY'
        function formatDateDMY(data) {
            if (!data) return '-';
            let parts = String(data).split(' ')[0].split('-');
            if (parts.length !== 3) return data;
            return `${parts[2]}-${parts[1]}-${parts[0]}`;
        }

        function export_excel_marketing_cvs_detail() {
            let tgl_awal = $('#txttgl_awal').val();
            let tgl_akhir = $('#txttgl_akhir').val();

            Swal.fire({
                title: 'Please Wait,',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: 'get',
                url: '{{ route('export_excel_marketing_cvs_detail') }}',
                data: {
                    tgl_awal: tgl_awal,
                    tgl_akhir: tgl_akhir
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    Swal.close();
                    Swal.fire({
                        title: 'Data Berhasil Di Export!',
                        icon: 'success',
                        showConfirmButton: true,
                        allowOutsideClick: false
                    });
                    let blob = new Blob([response]);
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'Marketing Report CVS Detail ' + tgl_awal + ' sd ' + tgl_akhir + '.xlsx';
                    link.click();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal export data ke Excel.',
                    });
                }
            });
        }

        let datatable = $("#datatable").DataTable({
            ordering: false,
            responsive: false,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: true,
            scrollX: true,
            scrollCollapse: false,
            ajax: {
                url: '{{ route('marketing_report_cvs_detail') }}',
                data: function(d) {
                    d.tgl_awal = $('#txttgl_awal').val();
                    d.tgl_akhir = $('#txttgl_akhir').val();
                }
            },
            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    data: 'kpno'
                },
                {
                    data: 'supplier'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'product_item',
                    className: 'td-truncate',
                    render: function(data) {
                        return `<span title="${data ?? ''}">${data ?? '-'}</span>`;
                    }
                },
                {
                    data: 'season_desc'
                },
                {
                    data: 'mkt_order'
                },
                {
                    data: 'curr'
                },
                {
                    data: 'so_date',
                    render: function(data) {
                        return formatDateDMY(data);
                    }
                },
                {
                    data: 'status'
                },
                {
                    data: 'qty_so'
                },
                {
                    data: 'price_so'
                },
                {
                    data: 'cost_date',
                    render: function(data) {
                        return formatDateDMY(data);
                    }
                },
                {
                    data: 'status_cost'
                },
                {
                    data: 'qty_cost'
                },
                {
                    data: 'ttl_fabric'
                },
                {
                    data: 'ttl_accsew'
                },
                {
                    data: 'ttl_accpack'
                },
                {
                    data: 'ttl_material'
                },
                {
                    data: 'ttl_cmt'
                },
                {
                    data: 'ttl_embro'
                },
                {
                    data: 'ttl_wash'
                },
                {
                    data: 'ttl_print'
                },
                {
                    data: 'ttl_wrapbut'
                },
                {
                    data: 'ttl_compbut'
                },
                {
                    data: 'ttl_label'
                },
                {
                    data: 'ttl_laser'
                },
                {
                    data: 'ttl_manufacturing'
                },
                {
                    data: 'ttl_develop'
                },
                {
                    data: 'ttl_overhead'
                },
                {
                    data: 'ttl_market'
                },
                {
                    data: 'ttl_shipp'
                },
                {
                    data: 'ttl_import'
                },
                {
                    data: 'ttl_handl'
                },
                {
                    data: 'ttl_test'
                },
                {
                    data: 'ttl_fabhandl'
                },
                {
                    data: 'ttl_service'
                },
                {
                    data: 'ttl_clearcost'
                },
                {
                    data: 'ttl_unexcost'
                },
                {
                    data: 'ttl_managementfee'
                },
                {
                    data: 'ttl_profit'
                },
                {
                    data: 'ttl_others'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        let totalCost = (parseFloat(row.ttl_material) || 0) + (parseFloat(row
                            .ttl_manufacturing) || 0) + (parseFloat(row.ttl_others) || 0);
                        return totalCost;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        let totalCost = (parseFloat(row.ttl_material) || 0) + (parseFloat(row
                            .ttl_manufacturing) || 0) + (parseFloat(row.ttl_others) || 0);
                        let priceSo = parseFloat(row.price_so) || 0;
                        return (priceSo - totalCost).toFixed(4);
                    }
                },
            ],
        });
    </script>
@endsection
