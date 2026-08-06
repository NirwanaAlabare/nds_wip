@extends('layouts.index', ['containerFluid' => true, 'navbar' => false, 'footer' => false])

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        body {
            background-color: #f4f6fb;
        }

        .content-wrapper {
            padding: 24px 30px !important;
        }

        .opn-panel {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
            margin-bottom: 1.25rem;
        }

        .opn-chart-card {
            height: 380px;
            display: flex;
            flex-direction: column;
        }

        .opn-chart-card>div:last-child {
            flex: 1;
            min-height: 0;
            display: flex;
            align-items: center;
        }

        .opn-chart-card>div:last-child>div {
            width: 100%;
        }

        .opn-stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 16px 18px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
            height: 100%;
        }

        .opn-stat-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 600;
        }

        .opn-stat-value {
            font-size: 24px;
            font-weight: 700;
            line-height: 1.2;
            margin: 6px 0 2px;
        }

        .opn-stat-sub {
            font-size: 11px;
            color: #9ca3af;
        }

        .opn-legend-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .badge-status-open {
            background-color: #e7f6ec;
            color: #1f8f4d;
            border: 1px solid #c7ecd3;
        }

        .badge-status-closed {
            background-color: #fdeaea;
            color: #c0392b;
            border: 1px solid #f5c6c6;
        }

        .detail-info-chip {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: #eef4ff;
            border: 1px solid #d7e3ff;
            color: var(--sb-color);
            font-weight: 600;
            font-size: .85rem;
            padding: .4rem .85rem;
            border-radius: 30px;
        }

        .detail-total-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: #e7f6ec;
            color: #1f8f4d;
            font-weight: 700;
            font-size: .85rem;
            padding: .4rem .85rem;
            border-radius: 30px;
        }

        #detail_item_table thead th {
            background: #f4f6fb;
            border-bottom: 2px solid #e3e7f1;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #5a5f73;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .detail-table-scroll {
            max-height: 400px;
            overflow-y: auto;
        }

        .warehouse-zone-box {
            flex: 0 0 calc(25% - 1.5rem);
            max-width: calc(25% - 1.5rem);
        }

        .warehouse-zone-box .table-responsive {
            max-width: 100%;
        }

        @media (max-width: 1400px) {
            .warehouse-zone-box {
                flex: 0 0 calc(33.333% - 1.5rem);
                max-width: calc(33.333% - 1.5rem);
            }
        }

        @media (max-width: 992px) {
            .warehouse-zone-box {
                flex: 0 0 calc(50% - 1.5rem);
                max-width: calc(50% - 1.5rem);
            }
        }

        @media (max-width: 576px) {
            .warehouse-zone-box {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }

        .qty-pill {
            display: inline-block;
            min-width: 2.2rem;
            padding: .2rem .55rem;
            border-radius: 20px;
            background: #e7f6ec;
            color: #1f8f4d;
            font-weight: 700;
            font-size: .8rem;
        }
    </style>
@endsection

@section('content')
    <!-- Modal Detail Item -->
    <div class="modal fade" id="modalDetailItem" tabindex="-1" role="dialog" aria-labelledby="modalDetailItemLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none;">
                <div class="modal-header bg-sb text-light">
                    <h3 class="modal-title fs-5 mb-0"><i class="fas fa-list"></i> Detail Item Opname</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <span class="detail-info-chip" id="detail_item_header"></span>
                        <span class="detail-total-badge">
                            <i class="fas fa-cubes"></i> Total Qty: <span id="detail_total_qty">0</span>
                        </span>
                    </div>
                    <div class="form-group mb-2">
                        <input type="text" class="form-control form-control-sm" id="inp_search_detail_item"
                            placeholder="Cari Buyer / WS / Style / Dest / Color / Size / Grade..." autocomplete="off">
                    </div>
                    <div class="table-responsive detail-table-scroll">
                        <table class="table table-borderless table-sm table-hover align-middle" id="detail_item_table">
                            <thead>
                                <tr style="text-align:center; vertical-align:middle">
                                    <th style="width: 5%;">No</th>
                                    <th>Buyer</th>
                                    <th>WS</th>
                                    <th>Style</th>
                                    <th>Dest</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th>Grade</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody id="detail_item_body">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <div>
            <a href="{{ route('opname-fg-stock') }}" class="text-muted small d-inline-block mb-1">
                <i class="fas fa-arrow-left"></i> Kembali ke List Opname
            </a>
            <h5 class="fw-bold mb-0"><i class="fas fa-chart-pie"></i> Stock Opname Analytics</h5>
            <small class="text-muted">Sistem Pemantauan Stok &amp; Pengelolaan Lokasi Gudang</small>
        </div>
        <div style="min-width: 260px;">
            <label class="form-label mb-1"><small><i class="fas fa-clipboard-list"></i> <b>No. Opname:</b></small></label>
            <select class="form-control form-control-sm select2bs4" id="filter-opname" style="width: 100%;">
            </select>
            <div class="mt-1" id="filter-opname-ket-wrap" style="display:none;">
                <small class="text-muted"><i class="fas fa-info-circle"></i> Keterangan: <span id="filter-opname-ket">-</span></small>
            </div>
        </div>
    </div>

    <div class="row mb-1">
        <div class="col-md col-6 mb-3">
            <div class="opn-stat-card">
                <div class="opn-stat-label">Total Quantity</div>
                <div class="opn-stat-value text-primary" id="stat-total-qty">0 Pcs</div>
                <div class="opn-stat-sub">Total Stok Opname</div>
            </div>
        </div>
        <div class="col-md col-6 mb-3">
            <div class="opn-stat-card">
                <div class="opn-stat-label">Total Pallet</div>
                <div class="opn-stat-value" id="stat-total-pallet">0</div>
                <div class="opn-stat-sub">Pallet Terisi</div>
            </div>
        </div>
        <div class="col-md col-6 mb-3">
            <div class="opn-stat-card">
                <div class="opn-stat-label">Total Karton</div>
                <div class="opn-stat-value" id="stat-total-karton">0</div>
                <div class="opn-stat-sub">Kartons Scanned</div>
            </div>
        </div>
        <div class="col-md col-6 mb-3">
            <div class="opn-stat-card">
                <div class="opn-stat-label">Kualitas Grade A</div>
                <div class="opn-stat-value text-primary" id="stat-grade-a">0 Pcs</div>
                <div class="opn-stat-sub"><span id="stat-grade-a-pct">0</span>% dari Total</div>
            </div>
        </div>
        <div class="col-md col-6 mb-3">
            <div class="opn-stat-card">
                <div class="opn-stat-label">Top Buyer</div>
                <div class="opn-stat-value" style="font-size: 15px;" id="stat-top-buyer">-</div>
                <div class="opn-stat-sub"><span id="stat-top-buyer-qty">0</span> Pcs</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="opn-panel opn-chart-card">
                <h6 class="fw-bold mb-3"><i class="fas fa-chart-pie"></i> Kuantitas Produk per Grade</h6>
                <div id="chart-grade"></div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="opn-panel opn-chart-card">
                <h6 class="fw-bold mb-3"><i class="fas fa-chart-bar"></i> Top Buyer (Total Qty)</h6>
                <div id="chart-buyer"></div>
            </div>
        </div>
    </div>

    <div class="card card-sb mb-3">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-th"></i> Peta Lokasi Gudang (No. Pallet)</h5>
            <small class="text-muted">Format No. Pallet: <b>Zona.Baris.Kolom</b> (contoh: A.1.1). Klik sel untuk
                melihat karton di pallet tsb</small>
        </div>
        <div class="card-body">
            <div class="row align-items-end mb-3">
                <div class="col-md-3 form-group mb-2">
                    <input type="text" class="form-control form-control-sm" id="inp_search_warehouse_map"
                        placeholder="Cari No. Pallet / No. Karton...">
                </div>
                <div class="col-md-2 form-group mb-2">
                    <select class="form-control form-control-sm select2bs4" id="filter_warehouse_buyer"
                        style="width: 100%;" data-placeholder="Semua Buyer">
                        <option value="">- Semua Buyer -</option>
                    </select>
                </div>
                <div class="col-md-2 form-group mb-2">
                    <select class="form-control form-control-sm select2bs4" id="filter_warehouse_ws"
                        style="width: 100%;" data-placeholder="Semua WS">
                        <option value="">- Semua WS -</option>
                    </select>
                </div>
                <div class="col-md-2 form-group mb-2">
                    <select class="form-control form-control-sm select2bs4" id="filter_warehouse_style"
                        style="width: 100%;" data-placeholder="Semua Style">
                        <option value="">- Semua Style -</option>
                    </select>
                </div>
                <div class="col-md-1 form-group mb-2">
                    <select class="form-control form-control-sm select2bs4" id="filter_warehouse_color"
                        style="width: 100%;" data-placeholder="Semua Color">
                        <option value="">- Semua Color -</option>
                    </select>
                </div>
                <div class="col-md-2 form-group mb-2">
                    <select class="form-control form-control-sm select2bs4" id="filter_warehouse_product_item"
                        style="width: 100%;" data-placeholder="Semua Product Item">
                        <option value="">- Semua Product Item -</option>
                    </select>
                </div>
                <div class="col-md-1 form-group mb-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="btn_reset_warehouse_filter"
                        title="Reset Filter">
                        <i class="fas fa-rotate-left fa-sm"></i> Reset
                    </button>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap mb-3" style="font-size: 12px;">
                <span><span class="opn-legend-dot" style="background:#e5e7eb;"></span>Kosong</span>
                <span><span class="opn-legend-dot" style="background:#dbeafe;"></span>Terisi</span>
            </div>
            <div id="warehouse-map-wrap">
                <div class="text-muted text-center py-4">Memuat peta lokasi...</div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPalletDetail" tabindex="-1" role="dialog" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none;">
                <div class="modal-header bg-sb text-light">
                    <h3 class="modal-title fs-5 mb-0"><i class="fas fa-pallet"></i> Detail Pallet <span
                            id="pallet_detail_title"></span></h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-end mb-2">
                        <span class="detail-total-badge">
                            <i class="fas fa-cubes"></i> Total Qty: <span id="pallet_detail_total_qty">0</span>
                        </span>
                    </div>
                    <div class="table-responsive detail-table-scroll">
                        <table class="table table-borderless table-sm table-hover align-middle">
                            <thead>
                                <tr style="text-align:center; vertical-align:middle">
                                    <th>No. Karton</th>
                                    <th>Buyer</th>
                                    <th>WS</th>
                                    <th>Barang (Style)</th>
                                    <th>Product Item</th>
                                    <th>Grade</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody id="pallet_detail_body">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-database"></i> Master Data Opname</h5>
            <small class="text-muted">Klik tombol <b>Rincian</b> pada baris untuk melihat detail spesifikasi item &amp;
                posisi karton</small>
        </div>
        <div class="card-body">
            <div class="row align-items-end mb-2">
                <div class="col-md-4 form-group mb-2">
                    <input type="text" class="form-control form-control-sm" id="inp_search_master"
                        placeholder="Cari Item, Buyer, Pallet, Karton...">
                </div>
                <div class="col-md-2 form-group mb-2 ms-md-auto">
                    <select class="form-control form-control-sm select2bs4" id="inp_page_length">
                        <option value="10">10 / halaman</option>
                        <option value="25">25 / halaman</option>
                        <option value="50">50 / halaman</option>
                        <option value="100">100 / halaman</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable-master" class="table table-bordered table-hover display nowrap"
                    style="width: 100%;">
                    <thead class="table-primary">
                        <tr style="text-align:center; vertical-align:middle">
                            <th>No. Opname</th>
                            <th>No. Pallet</th>
                            <th>No. Karton</th>
                            <th>Buyer</th>
                            <th>Barang (Style)</th>
                            <th>Product Item</th>
                            <th>Grade</th>
                            <th>Qty</th>
                            <th style="width: 8%;">Aksi / Rincian</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        const gradePalette = ['#2563eb', '#f59e0b', '#ef4444', '#10b981', '#8b5cf6', '#14b8a6'];
        const buyerPalette = ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#14b8a6', '#f472b6', '#60a5fa',
            '#fb923c', '#94a3b8'
        ];

        let chartGrade, chartBuyer, tableMaster;

        function initChartGrade() {
            chartGrade = new ApexCharts(document.querySelector("#chart-grade"), {
                series: [],
                labels: [],
                chart: {
                    type: 'donut',
                    height: 300
                },
                colors: gradePalette,
                legend: {
                    position: 'bottom',
                    formatter: (seriesName, opts) => {
                        let val = opts.w.globals.series[opts.seriesIndex];
                        let total = opts.w.globals.series.reduce((a, b) => a + b, 0);
                        let pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                        return `${seriesName}: ${pct}%`;
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: (val) => val.toFixed(1) + '%'
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%'
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: (val) => val + ' Pcs'
                    }
                }
            });
            chartGrade.render();
        }

        function initChartBuyer() {
            chartBuyer = new ApexCharts(document.querySelector("#chart-buyer"), {
                series: [{
                    name: 'Total Qty',
                    data: []
                }],
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '65%',
                        borderRadius: 3,
                        distributed: true
                    }
                },
                colors: buyerPalette,
                legend: {
                    show: false
                },
                dataLabels: {
                    enabled: true,
                    style: {
                        fontSize: '11px',
                        colors: ['#333']
                    },
                    offsetX: 14
                },
                xaxis: {
                    categories: [],
                    labels: {
                        style: {
                            fontSize: '11px'
                        },
                        formatter: (val) => (val && val.length > 16) ? val.substring(0, 16) + '…' : val
                    }
                },
                grid: {
                    padding: {
                        left: 10
                    }
                },
                tooltip: {
                    y: {
                        formatter: (val) => val + ' Pcs'
                    }
                }
            });
            chartBuyer.render();
        }

        let opnameMap = {};

        function loadOpnameList() {
            $.get('{{ route('get-opname-list-opname-fg-stock') }}', function(response) {
                let $cbo = $('#filter-opname');
                $cbo.empty();
                opnameMap = {};

                if (!response || response.length === 0) {
                    $cbo.append(new Option('- Tidak ada data -', ''));
                    $cbo.trigger('change');
                    updateOpnameKetInfo();
                    return;
                }

                response.forEach(function(row) {
                    opnameMap[row.no_opname] = row;
                    let label = row.no_opname + ' - ' + row.tgl_opname_fix;
                    $cbo.append(new Option(label, row.no_opname));
                });

                $cbo.trigger('change');
            });
        }

        function updateOpnameKetInfo() {
            let noOpname = $('#filter-opname').val();
            let row = opnameMap[noOpname];

            if (row && row.ket) {
                $('#filter-opname-ket').text(row.ket);
                $('#filter-opname-ket-wrap').show();
            } else {
                $('#filter-opname-ket-wrap').hide();
            }
        }

        function loadSummary(noOpname) {
            $.get('{{ route('get-summary-opname-fg-stock') }}', {
                no_opname: noOpname
            }, function(response) {
                $('#stat-total-qty').text((response.total_qty || 0) + ' Pcs');
                $('#stat-total-pallet').text(response.total_pallet || 0);
                $('#stat-total-karton').text(response.total_karton || 0);
                $('#stat-grade-a').text((response.grade_a_qty || 0) + ' Pcs');
                $('#stat-grade-a-pct').text(response.grade_a_pct || 0);
                $('#stat-top-buyer').text(response.top_buyer || '-');
                $('#stat-top-buyer-qty').text(response.top_buyer_qty || 0);
            });
        }

        function loadChartGrade(noOpname) {
            $.get('{{ route('get-chart-grade-opname-fg-stock') }}', {
                no_opname: noOpname
            }, function(response) {
                let labels = response.map(item => 'Grade ' + item.grade);
                let series = response.map(item => parseFloat(item.qty));

                chartGrade.updateOptions({
                    labels: labels
                }, false, true);
                chartGrade.updateSeries(series);
            });
        }

        function loadChartBuyer(noOpname) {
            $.get('{{ route('get-chart-buyer-opname-fg-stock') }}', {
                no_opname: noOpname
            }, function(response) {
                let categories = response.map(item => item.buyer);
                let data = response.map(item => parseFloat(item.qty));

                chartBuyer.updateOptions({
                    xaxis: {
                        categories: categories
                    }
                }, false, true);
                chartBuyer.updateSeries([{
                    name: 'Total Qty',
                    data: data
                }]);
            });
        }

        function loadMasterTable(noOpname) {
            tableMaster = $("#datatable-master").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                paging: true,
                searching: true,
                destroy: true,
                scrollX: true,
                pageLength: parseInt($('#inp_page_length').val()) || 10,
                dom: 'rt<"d-flex justify-content-between align-items-center"ip>',
                ajax: {
                    url: '{{ route('get-master-data-opname-fg-stock') }}',
                    data: function(d) {
                        d.no_opname = noOpname;
                        d.search.value = $('#inp_search_master').val();
                    },
                },
                columns: [{
                        data: 'no_opname'
                    },
                    {
                        data: 'no_pallet',
                        render: (data) => data || '-'
                    },
                    {
                        data: 'no_carton'
                    },
                    {
                        data: 'buyer'
                    },
                    {
                        data: 'styleno'
                    },
                    {
                        data: 'product_item',
                        render: (data) => data || '-'
                    },
                    {
                        data: 'grade',
                        render: (data) => `<span class="badge bg-secondary">Grade ${data}</span>`
                    },
                    {
                        data: 'qty',
                        render: (data) => `<span class="qty-pill">${data} Pcs</span>`
                    },
                    {
                        data: null,
                        render: (data, type, row) => `
                            <div class="d-flex gap-1 justify-content-center">
                                <a class="btn btn-outline-secondary btn-sm" href="javascript:void(0)"
                                    onclick='viewDetailItem(${JSON.stringify(row)})' title="Rincian">
                                    <i class="fas fa-eye fa-sm"></i> Rincian
                                </a>
                            </div>`
                    },
                ],
                columnDefs: [{
                    "className": "dt-center",
                    "targets": "_all"
                }, ]
            });
        }

        function fillWarehouseFilterOptions($cbo, values) {
            let currentVal = $cbo.val();
            $cbo.find('option[value!=""]').remove();

            (values || []).forEach(function(val) {
                $cbo.append(new Option(val, val));
            });

            if (values && values.includes(currentVal)) {
                $cbo.val(currentVal);
            } else {
                $cbo.val('');
            }

            $cbo.trigger('change.select2');
        }

        function loadWarehouseFilterOptions(noOpname) {
            $.get('{{ route('get-warehouse-filter-options-opname-fg-stock') }}', {
                no_opname: noOpname
            }, function(response) {
                fillWarehouseFilterOptions($('#filter_warehouse_buyer'), response.buyer);
                fillWarehouseFilterOptions($('#filter_warehouse_ws'), response.ws);
                fillWarehouseFilterOptions($('#filter_warehouse_style'), response.styleno);
                fillWarehouseFilterOptions($('#filter_warehouse_color'), response.color);
                fillWarehouseFilterOptions($('#filter_warehouse_product_item'), response.product_item);
            });
        }

        function loadWarehouseMap(noOpname) {
            $('#warehouse-map-wrap').html('<div class="text-muted text-center py-4">Memuat peta lokasi...</div>');

            $.get('{{ route('get-warehouse-map-opname-fg-stock') }}', {
                no_opname: noOpname
            }, function(response) {
                renderWarehouseMap(response);
                filterWarehouseMap();
            }).fail(function() {
                $('#warehouse-map-wrap').html(
                    '<div class="text-danger text-center py-4">Gagal memuat peta lokasi</div>');
            });
        }

        function renderWarehouseMap(zones) {
            if (!zones || zones.length === 0) {
                $('#warehouse-map-wrap').html(
                    '<div class="text-muted text-center py-4">Belum ada No. Pallet pada opname ini</div>');
                return;
            }

            let html = '<div class="d-flex flex-wrap align-items-start gap-4">';

            zones.forEach(function(zone) {
                let cellMap = {};
                (zone.cells || []).forEach(function(cell) {
                    cellMap[cell.baris + '-' + cell.kolom] = cell;
                });

                html += '<div class="warehouse-zone-box mb-2">';
                html += `<h6 class="fw-bold mb-2">Zona ${zone.zona}</h6>`;
                html += '<div class="table-responsive">';
                html += '<table class="table table-bordered text-center align-middle mb-0" style="width:auto;">';
                html += '<tbody>';

                for (let b = 1; b <= zone.max_baris; b++) {
                    html += '<tr>';
                    html += `<th class="bg-light" style="min-width:50px;">${b}</th>`;

                    for (let k = 1; k <= zone.max_kolom; k++) {
                        let cell = cellMap[b + '-' + k];

                        if (cell) {
                            let searchText = `${cell.no_pallet} ${cell.no_carton_list}`.toLowerCase();
                            let buyerText = (cell.buyer_list || '').toLowerCase();
                            let wsText = (cell.ws_list || '').toLowerCase();
                            let styleText = (cell.styleno_list || '').toLowerCase();
                            let colorText = (cell.color_list || '').toLowerCase();
                            let productItemText = (cell.product_item_list || '').toLowerCase();
                            html += `
                                <td class="warehouse-cell" data-search="${searchText}" data-buyer="${buyerText}"
                                    data-ws="${wsText}" data-style="${styleText}" data-color="${colorText}"
                                    data-product-item="${productItemText}" style="cursor:pointer; min-width:110px;"
                                    onclick='openPalletDetail(${JSON.stringify(cell)})'>
                                    <div class="fw-bold" style="font-size:12px;">${cell.no_pallet}</div>
                                    <div class="text-muted" style="font-size:11px;">${cell.total_karton} Karton</div>
                                    <div style="font-size:11px;">${cell.total_qty} Pcs</div>
                                </td>`;
                        } else {
                            html += '<td style="background:#e5e7eb; min-width:110px;">&nbsp;</td>';
                        }
                    }

                    html += '</tr>';
                }

                html += '</tbody></table></div>';
                html += '</div>';
            });

            html += '</div>';

            $('#warehouse-map-wrap').html(html);
        }

        function filterWarehouseMap() {
            let keyword = $('#inp_search_warehouse_map').val().toLowerCase().trim();
            let buyer = ($('#filter_warehouse_buyer').val() || '').toLowerCase().trim();
            let ws = ($('#filter_warehouse_ws').val() || '').toLowerCase().trim();
            let style = ($('#filter_warehouse_style').val() || '').toLowerCase().trim();
            let color = ($('#filter_warehouse_color').val() || '').toLowerCase().trim();
            let productItem = ($('#filter_warehouse_product_item').val() || '').toLowerCase().trim();
            let hasFilter = !!keyword || !!buyer || !!ws || !!style || !!color || !!productItem;

            $('.warehouse-cell').each(function() {
                let $cell = $(this);
                let match = (!keyword || $cell.data('search').toString().includes(keyword)) &&
                    (!buyer || $cell.data('buyer').toString().includes(buyer)) &&
                    (!ws || $cell.data('ws').toString().includes(ws)) &&
                    (!style || $cell.data('style').toString().includes(style)) &&
                    (!color || $cell.data('color').toString().includes(color)) &&
                    (!productItem || $cell.data('product-item').toString().includes(productItem));

                $cell.css({
                    'background': match ? '#dbeafe' : '#f3f4f6',
                    'opacity': match ? 1 : .35
                });

                $cell.toggleClass('border-warning border-3', hasFilter && match);
            });
        }

        function openPalletDetail(cell) {
            let noOpname = $('#filter-opname').val();

            $('#pallet_detail_title').text('- ' + cell.no_pallet);
            $('#pallet_detail_total_qty').text(0);
            $('#pallet_detail_body').html(
                '<tr><td colspan="7" class="text-center">Memuat data...</td></tr>');

            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPalletDetail')).show();

            $.get('{{ route('get-pallet-detail-opname-fg-stock') }}', {
                no_opname: noOpname,
                no_pallet: cell.no_pallet
            }, function(response) {
                $('#pallet_detail_body').empty();

                if (!response || response.length === 0) {
                    $('#pallet_detail_body').html(
                        '<tr><td colspan="7" class="text-center">Belum ada item</td></tr>');
                    updatePalletDetailTotalQty();
                    return;
                }

                response.forEach(function(item) {
                    $('#pallet_detail_body').append(`
                        <tr data-qty="${item.qty}">
                            <td class="text-center">${item.no_carton}</td>
                            <td class="text-center">${item.buyer}</td>
                            <td class="text-center">${item.ws}</td>
                            <td class="text-center">${item.styleno}</td>
                            <td class="text-center">${item.product_item || '-'}</td>
                            <td class="text-center">${item.grade}</td>
                            <td class="text-center"><span class="qty-pill">${item.qty}</span></td>
                        </tr>`);
                });

                updatePalletDetailTotalQty();
            }).fail(function() {
                $('#pallet_detail_body').html(
                    '<tr><td colspan="7" class="text-center text-danger">Gagal memuat data</td></tr>');
                updatePalletDetailTotalQty();
            });
        }

        function updatePalletDetailTotalQty() {
            let total = 0;
            $('#pallet_detail_body tr[data-qty]').each(function() {
                total += parseFloat($(this).data('qty')) || 0;
            });
            $('#pallet_detail_total_qty').text(total);
        }

        function reloadAll() {
            let noOpname = $('#filter-opname').val();

            updateOpnameKetInfo();

            if (!noOpname) {
                return;
            }

            loadSummary(noOpname);
            loadChartGrade(noOpname);
            loadChartBuyer(noOpname);
            loadMasterTable(noOpname);
            loadWarehouseMap(noOpname);
            loadWarehouseFilterOptions(noOpname);
        }

        function viewDetailItem(row) {
            $('#inp_search_detail_item').val('');
            $('#detail_total_qty').text(0);
            $('#detail_item_body').html(
                '<tr><td colspan="9" class="text-center">Memuat data...</td></tr>');

            $('#detail_item_header').html(
                `<i class="fas fa-box-open"></i> No. Opname: <b>${row.no_opname}</b> &nbsp;|&nbsp; No. Carton: <b>${row.no_carton}</b> &nbsp;|&nbsp; No. Pallet: <b>${row.no_pallet || '-'}</b>`
            );

            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetailItem')).show();

            $.get('{{ route('get-opname-items-fg-stock') }}', {
                no_opname: row.no_opname
            }, function(response) {
                $('#detail_item_body').empty();

                let items = (response.items || []).filter(function(item) {
                    return item.no_carton === row.no_carton && item.no_pallet === row.no_pallet;
                });

                if (items.length === 0) {
                    $('#detail_item_body').html(
                        '<tr><td colspan="9" class="text-center">Belum ada item</td></tr>');
                    updateDetailTotalQty();
                    return;
                }

                items.forEach(function(item, i) {
                    let destLabel = item.dest ? item.dest : '-';
                    let searchText =
                        `${item.buyer} ${item.ws} ${item.styleno} ${item.dest} ${item.color} ${item.size} ${item.grade}`
                        .toLowerCase();
                    $('#detail_item_body').append(`
                        <tr data-qty="${item.qty}" data-search="${searchText}">
                            <td class="text-center">${i + 1}</td>
                            <td class="text-center">${item.buyer}</td>
                            <td class="text-center">${item.ws}</td>
                            <td class="text-center">${item.styleno}</td>
                            <td class="text-center">${destLabel}</td>
                            <td class="text-center">${item.color}</td>
                            <td class="text-center">${item.size}</td>
                            <td class="text-center">${item.grade}</td>
                            <td class="text-center"><span class="qty-pill">${item.qty}</span></td>
                        </tr>`);
                });

                updateDetailTotalQty();
            }).fail(function() {
                $('#detail_item_body').html(
                    '<tr><td colspan="9" class="text-center text-danger">Gagal memuat data</td></tr>');
                updateDetailTotalQty();
            });
        }

        function updateDetailTotalQty() {
            let total = 0;
            $('#detail_item_body tr[data-qty]:visible').each(function() {
                total += parseFloat($(this).data('qty')) || 0;
            });
            $('#detail_total_qty').text(total);
        }

        $('#inp_search_detail_item').on('keyup', function() {
            let keyword = $(this).val().toLowerCase().trim();

            $('#detail_item_body tr[data-search]').each(function() {
                let match = $(this).data('search').toString().includes(keyword);
                $(this).toggle(match);
            });

            updateDetailTotalQty();
        });

        $(document).ready(function() {
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: 'resolve',
                minimumResultsForSearch: -1
            });
            $('.select2-container--bootstrap4 .select2-selection--single').css({
                'height': '31px',
                'font-size': '12px',
                'line-height': '30px'
            });

            $('#inp_page_length').val('10').trigger('change');

            initChartGrade();
            initChartBuyer();
            loadOpnameList();

            $('#filter-opname').on('change', reloadAll);
            $('#inp_search_warehouse_map').on('keyup', filterWarehouseMap);
            $('#filter_warehouse_buyer, #filter_warehouse_ws, #filter_warehouse_style, #filter_warehouse_color, #filter_warehouse_product_item')
                .on('change', filterWarehouseMap);

            $('#btn_reset_warehouse_filter').on('click', function() {
                $('#inp_search_warehouse_map').val('');
                $('#filter_warehouse_buyer, #filter_warehouse_ws, #filter_warehouse_style, #filter_warehouse_color, #filter_warehouse_product_item')
                    .val('').trigger('change.select2');
                filterWarehouseMap();
            });

            $('#inp_page_length').on('change', function() {
                if (tableMaster) {
                    tableMaster.page.len(parseInt($(this).val())).draw();
                }
            });

            let searchTimer;
            $('#inp_search_master').on('keyup', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    if (tableMaster) {
                        tableMaster.draw();
                    }
                }, 400);
            });
        });
    </script>
@endsection
