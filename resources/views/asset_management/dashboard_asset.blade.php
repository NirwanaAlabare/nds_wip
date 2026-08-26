@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .dashboard-asset .small-box {
            border-radius: .5rem;
        }

        .dashboard-asset .small-box .icon>i {
            font-size: 60px;
        }

        .dashboard-asset .card {
            border-radius: .5rem;
        }

        .dashboard-asset .card-title {
            font-weight: 600;
        }

        .dashboard-asset .table td,
        .dashboard-asset .table th {
            vertical-align: middle;
        }
    </style>
@endsection

@section('content')
    <div class="dashboard-asset">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <div>
                <h5 class="mb-0 font-weight-bold">Dashboard Asset Management</h5>
                <span class="text-muted text-sm">Ringkasan mesin, kontrak sewa, dan penerimaan asset</span>
            </div>
            <span class="text-muted text-sm">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
        </div>

        <!-- RINGKASAN -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ number_format($totalMesinMilik, 0, ',', '.') }}</h3>
                        <p class="mb-0">Mesin Milik</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <a href="{{ route('asset_mesin_master') }}" class="small-box-footer">
                        Lihat Master Mesin <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ number_format($totalMesinSewa, 0, ',', '.') }}</h3>
                        <p class="mb-0">Mesin Sewa</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <a href="{{ route('asset_mesin_sewa') }}" class="small-box-footer">
                        Lihat Mesin Sewa <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ number_format($totalMesinBreakdown, 0, ',', '.') }}</h3>
                        <p class="mb-0">Breakdown / Service</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <a href="{{ route('asset_mesin_report_stok_jenis_area') }}" class="small-box-footer">
                        Lihat Report Mesin <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ number_format($totalKontrakSegeraBerakhir, 0, ',', '.') }}</h3>
                        <p class="mb-0">Kontrak Segera Berakhir</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <a href="{{ route('asset_mesin_sewa') }}" class="small-box-footer">
                        Lihat Semua Mesin Sewa <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- NOTIFIKASI KONTRAK MESIN SEWA -->
        <div class="card card-outline card-warning d-none" id="cardNotifMesinSewa">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                        <span id="cardNotifMesinSewaTitle">Kontrak Sewa Mesin Akan Berakhir</span>
                    </h3>
                    <a href="{{ route('asset_mesin_sewa') }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-list mr-1"></i> Lihat Semua Mesin Sewa
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered w-100" id="tableNotifMesinSewa">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>No. BPB</th>
                                <th>Mesin</th>
                                <th>Tipe</th>
                                <th>Serial Number</th>
                                <th>Lokasi</th>
                                <th>Akhir Kontrak</th>
                                <th class="text-center" style="width: 130px;">Sisa</th>
                                <th class="text-center" style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- CHART -->
        <div class="row">
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Komposisi Status Mesin</h3>
                    </div>
                    <div class="card-body">
                        @if (count($mesinPerStatus) > 0)
                            <div id="chartStatusMesin"></div>
                        @else
                            <p class="text-muted text-center mb-0 py-5">Belum ada data mesin</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <h3 class="card-title mb-0">Top 10 Jenis Mesin</h3>
                            <span class="text-muted text-sm">Mesin milik + sewa</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (count($mesinPerJenis) > 0)
                            <div id="chartJenisMesin"></div>
                        @else
                            <p class="text-muted text-center mb-0 py-5">Belum ada data jenis mesin</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <h3 class="card-title mb-0">Penerimaan Mesin 6 Bulan Terakhir</h3>
                            <span class="text-muted text-sm">Berdasarkan tanggal transaksi penerimaan</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (count($penerimaanPerBulan) > 0)
                            <div id="chartPenerimaanMesin"></div>
                        @else
                            <p class="text-muted text-center mb-0 py-5">Belum ada data penerimaan</p>
                        @endif
                    </div>
                </div>
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
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        const statusMesinData = @json($mesinPerStatus);
        const jenisMesinData = @json($mesinPerJenis);
        const penerimaanMesinData = @json($penerimaanPerBulan);

        const statusColorMap = {
            'ACTIVE': '#28a745',
            'IDLE': '#17a2b8',
            'BREAKDOWN': '#dc3545',
            'SERVICE': '#fd7e14',
            'TANPA STATUS': '#adb5bd'
        };

        // ==== DataTable Notifikasi Kontrak Mesin Sewa ====
        let dtNotifMesinSewa = null;

        function initDtNotifMesinSewa() {
            dtNotifMesinSewa = $('#tableNotifMesinSewa').DataTable({
                data: [],
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'Semua']
                ],
                order: [
                    [7, 'asc']
                ],
                responsive: true,
                autoWidth: false,
                language: {
                    emptyTable: 'Tidak ada kontrak yang akan berakhir',
                    zeroRecords: 'Data tidak ditemukan',
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ kontrak',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(difilter dari _MAX_ kontrak)',
                    paginate: {
                        first: 'Awal',
                        last: 'Akhir',
                        next: 'Selanjutnya',
                        previous: 'Sebelumnya'
                    }
                },
                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
                    },
                    {
                        data: 'bpbno_int',
                        defaultContent: '-'
                    },
                    {
                        data: null,
                        render: row => ((row.nm_jenis || '-') + ' ' + (row.nm_merk || '')).trim()
                    },
                    {
                        data: 'tipe',
                        defaultContent: '-'
                    },
                    {
                        data: 'serial_number',
                        defaultContent: '-'
                    },
                    {
                        data: 'lokasi',
                        defaultContent: '-'
                    },
                    {
                        data: 'tgl_akhir_kontrak',
                        defaultContent: '-'
                    },
                    {
                        data: 'sisa_hari',
                        className: 'text-center',
                        render: (data, type, row) => {
                            if (type !== 'display') {
                                return data;
                            }
                            let badgeClass = data <= 0 ? 'badge-danger' : 'badge-warning';
                            let label = data <= 0 ? 'Berakhir hari ini' : ('H-' + data);
                            return '<span class="badge ' + badgeClass + '">' + label + '</span>';
                        }
                    },
                    {
                        data: 'idx',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: idx => '<button type="button" class="btn btn-xs btn-outline-secondary" ' +
                            'onclick="showDetailNotifMesinSewa(' + idx + ')">' +
                            '<i class="fas fa-eye"></i> Detail</button>'
                    }
                ]
            });
        }

        window.renderNotifMesinSewaTable = function(items, total) {
            let $card = $('#cardNotifMesinSewa');
            if (!$card.length) {
                return;
            }

            if (!total || !items || items.length === 0) {
                $card.addClass('d-none');
                if (dtNotifMesinSewa) {
                    dtNotifMesinSewa.clear().draw();
                }
                return;
            }

            $('#cardNotifMesinSewaTitle').text('Kontrak Sewa Mesin Akan Berakhir (' + total + ')');
            $card.removeClass('d-none');

            if (!dtNotifMesinSewa) {
                initDtNotifMesinSewa();
            }

            let rows = items.map((item, idx) => $.extend({}, item, {
                idx: idx
            }));

            dtNotifMesinSewa.clear().rows.add(rows).draw();
            dtNotifMesinSewa.columns.adjust();
        };

        function formatPeriode(periode) {
            const bulanList = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const parts = (periode || '').split('-');
            if (parts.length !== 2) return periode;
            return bulanList[parseInt(parts[1], 10) - 1] + ' ' + parts[0];
        }

        $(document).ready(() => {
            if (statusMesinData.length > 0) {
                new ApexCharts(document.querySelector('#chartStatusMesin'), {
                    chart: {
                        type: 'donut',
                        height: 320
                    },
                    labels: statusMesinData.map(d => d.status),
                    series: statusMesinData.map(d => parseInt(d.total)),
                    colors: statusMesinData.map(d => statusColorMap[d.status] || '#6c757d'),
                    legend: {
                        position: 'bottom'
                    },
                    dataLabels: {
                        formatter: (val, opt) => opt.w.config.series[opt.seriesIndex]
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Total Mesin'
                                    }
                                }
                            }
                        }
                    }
                }).render();
            }

            if (jenisMesinData.length > 0) {
                new ApexCharts(document.querySelector('#chartJenisMesin'), {
                    chart: {
                        type: 'bar',
                        height: 320,
                        toolbar: {
                            show: false
                        }
                    },
                    series: [{
                        name: 'Jumlah Mesin',
                        data: jenisMesinData.map(d => parseInt(d.total))
                    }],
                    xaxis: {
                        categories: jenisMesinData.map(d => d.nm_jenis)
                    },
                    colors: ['#007bff'],
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            borderRadius: 4,
                            barHeight: '65%'
                        }
                    },
                    dataLabels: {
                        enabled: true
                    },
                    grid: {
                        borderColor: '#f1f1f1'
                    }
                }).render();
            }

            if (penerimaanMesinData.length > 0) {
                new ApexCharts(document.querySelector('#chartPenerimaanMesin'), {
                    chart: {
                        type: 'bar',
                        height: 300,
                        stacked: false,
                        toolbar: {
                            show: false
                        }
                    },
                    series: [{
                            name: 'Mesin Milik',
                            data: penerimaanMesinData.map(d => parseInt(d.milik))
                        },
                        {
                            name: 'Mesin Sewa',
                            data: penerimaanMesinData.map(d => parseInt(d.sewa))
                        }
                    ],
                    xaxis: {
                        categories: penerimaanMesinData.map(d => formatPeriode(d.periode))
                    },
                    colors: ['#17a2b8', '#28a745'],
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            columnWidth: '45%'
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right'
                    },
                    grid: {
                        borderColor: '#f1f1f1'
                    }
                }).render();
            }
        });
    </script>
@endsection
