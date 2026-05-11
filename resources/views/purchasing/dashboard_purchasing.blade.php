@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    /* Custom Styling Biar Nggak Bosenin */
    .dashboard-title { font-weight: 800; color: #343a40; letter-spacing: -0.5px; }

    /* Efek Hover untuk Card */
    .card-hover {
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    /* Warna Gradient Card Modern */
    .bg-grad-primary { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); color: white; }
    .bg-grad-success { background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); color: white; }
    .bg-grad-warning { background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%); color: white; }
    .bg-grad-danger  { background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%); color: white; }

    .icon-bg {
        position: absolute;
        right: 20px;
        top: 20px;
        font-size: 3rem;
        opacity: 0.2;
    }

    .table-modern th { background-color: #f8f9fc; color: #4e73df; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; border-top: none; }
    .table-modern td { vertical-align: middle; color: #5a5c69; font-size: 13px;}
</style>
@endsection

@section('content')
<div class="container-fluid py-2">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="dashboard-title"><i class="fas fa-chart-line mr-2"></i> Purchasing Dashboard</h5>

        <div class="bg-white px-2 py-1 rounded shadow-sm border d-flex align-items-center">
            <i class="fas fa-calendar-alt text-muted mr-2 ml-1"></i>
            <select id="filter_tahun" class="form-control form-control-sm border-0 font-weight-bold text-muted" style="box-shadow: none; cursor: pointer; min-width: 100px;" onchange="filterDashboard()">
                @php $selectedYear = request('tahun', date('Y')); @endphp
                @for($i = date('Y'); $i >= date('Y') - 3; $i--)
                    <option value="{{ $i }}" {{ $selectedYear == $i ? 'selected' : '' }}>Tahun {{ $i }}</option>
                @endfor
            </select>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-hover bg-grad-warning h-100 py-2 relative" style="cursor: pointer;" onclick="showListPO('W', 'DRAFT (WAITING)')">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">PO DRAFT (WAITING)</div>
                    <div class="h3 mb-0 font-weight-bold">{{ number_format($count_draft) }}</div>
                    <i class="fas fa-file-signature icon-bg"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-hover bg-grad-success h-100 py-2 relative" style="cursor: pointer;" onclick="showListPO('A', 'APPROVED')">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">PO APPROVED</div>
                    <div class="h3 mb-0 font-weight-bold">{{ number_format($count_approved) }}</div>
                    <i class="fas fa-check-double icon-bg"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-hover bg-grad-danger h-100 py-2 relative" style="cursor: pointer;" onclick="showListPO('C', 'CANCELED')">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">PO CANCELED</div>
                    <div class="h3 mb-0 font-weight-bold">{{ number_format($count_canceled) }}</div>
                    <i class="fas fa-times-circle icon-bg"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-hover bg-grad-primary h-100 py-2 relative" style="cursor: pointer;" onclick="showListPO('S', 'IN SHIPMENTS')">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">IN SHIPMENTS</div>
                    <div class="h3 mb-0 font-weight-bold">12</div>
                    <i class="fas fa-truck fa-flip-horizontal icon-bg"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-header bg-white pt-3 pb-2 border-bottom-0">
                    <h6 class="m-0 font-weight-bold">5 Transaksi PO Terakhir</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4">Tanggal</th>
                                    <th>No PO</th>
                                    <th>Supplier</th>
                                    <th>Jenis</th>
                                    <th class="pr-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_pos as $po)
                                <tr>
                                    <td class="pl-4">{{ $po->podate }}</td>
                                    <td class="font-weight-bold text-dark">{{ $po->pono }}</td>
                                    <td>{{ $po->nama_supplier }}</td>
                                    <td>
                                        <span class="badge badge-light border">{{ $po->jenis === 'M' ? 'Manufacturing' : 'Material' }}</span>
                                    </td>
                                    <td class="pr-4 text-center">
                                        @if($po->app === 'A')
                                            <span class="badge badge-success px-2 py-1"><i class="fas fa-check"></i> Approved</span>
                                        @elseif($po->app === 'W')
                                            <span class="badge badge-warning px-2 py-1"><i class="fas fa-clock"></i> Draft</span>
                                        @else
                                            <span class="badge badge-danger px-2 py-1"><i class="fas fa-times"></i> Canceled</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-4">Belum ada transaksi</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0 h-100 rounded-lg">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">Proporsi Jenis PO</h6>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <div id="chartJenis"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalListPO" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" style="border-radius: 12px;">
                <div class="modal-header border-bottom-0 bg-light">
                    <h5 class="modal-title fw-bold" id="list_title">List Purchase Order</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive p-3">
                        <table class="table table-bordered table-hover table-sm w-100" id="table-list-po">
                            <thead class="bg-sb text-white text-center">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>No PO</th>
                                    <th>Supplier</th>
                                    <th width="10%">Act</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>

<script>
    let tableList;

    $(document).ready(function() {
        var optionsSpend = {
            series: [{
                name: 'Pengeluaran (Juta)',
                data: {!! json_encode($chart_spend ?? []) !!}
            }],
            chart: {
                type: 'area',
                height: 320,
                toolbar: { show: false },
                fontFamily: 'Nunito, sans-serif'
            },
            colors: ['#4e73df'],
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            xaxis: {
                categories: {!! json_encode($chart_months ?? []) !!},
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { formatter: function (val) { return "Rp " + val + " Jt"; } }
            },
            tooltip: {
                y: { formatter: function (val) { return "Rp " + val + " Juta"; } }
            }
        };

        if(document.querySelector("#chartSpend")) {
            var chartSpend = new ApexCharts(document.querySelector("#chartSpend"), optionsSpend);
            chartSpend.render();
        }

        var optionsJenis = {
            series: {!! json_encode($chart_jenis_data ?? []) !!},
            chart: {
                type: 'donut',
                height: 300,
                fontFamily: 'Nunito, sans-serif'
            },
            labels: {!! json_encode($chart_jenis_label ?? []) !!},
            colors: ['#1cc88a', '#36b9cc'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            name: { show: true },
                            value: { show: true, fontSize: '24px', fontWeight: 'bold' },
                            total: { show: true, showAlways: false, label: 'Total Transaksi' }
                        }
                    }
                }
            },
            legend: { position: 'bottom' },
            dataLabels: { enabled: false },
            stroke: { show: true, width: 2, colors: ['#fff'] }
        };

        if(document.querySelector("#chartJenis")) {
            var chartJenis = new ApexCharts(document.querySelector("#chartJenis"), optionsJenis);
            chartJenis.render();
        }
    });

    function filterDashboard() {
        let tahun = document.getElementById('filter_tahun').value;
        let url = new URL(window.location.href);
        url.searchParams.set('tahun', tahun);
        window.location.href = url.href;
    }

    function showListPO(status, title) {
        $('#list_title').text('LIST PO - ' + title);
        let tahun = $('#filter_tahun').val();

        if ($.fn.DataTable.isDataTable('#table-list-po')) {
            tableList.ajax.url('{{ route("dashboard-list-po") }}?status=' + status + '&tahun=' + tahun).load();
        } else {
            tableList = $('#table-list-po').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("dashboard-list-po") }}?status=' + status + '&tahun=' + tahun,
                columns: [
                    {data: 'podate', name: 'po_header.podate', className: 'text-center'},
                    {data: 'pono', name: 'po_header.pono', className: 'fw-bold'},
                    {data: 'nama_supplier', name: 's.Supplier'},
                    {data: 'action', name:  'action', orderable: false, searchable: false, className: 'text-center'},
                ]
            });
        }
        $('#modalListPO').modal('show');
    }

   $(document).on('click', '.btn-view-detail', function() {
        let id = $(this).data('id');
        $('#modalListPO').modal('hide');

        let urlEdit = '{{ route("edit-purchase-order", ":id") }}'.replace(':id', id) + '?mode=view';
        window.open(urlEdit, '_blank');
    });
</script>
@endsection
