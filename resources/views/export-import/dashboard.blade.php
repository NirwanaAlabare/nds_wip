@extends('layouts.index')

@section('custom-link')
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        /* Tema Warna */
        :root { --navy: #081e3f; --primary-hover: #030d1a; }

        /* --- Panel Alert & Filter (STICKY) --- */
        .alert-panel {
            background-color: #fff;
            border-radius: 8px;
            border-top: 5px solid var(--navy);
            padding: 20px;
            margin-bottom: 25px;
            position: sticky;
            top: 60px;
            z-index: 999;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .alert-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .blink-icon { animation: blink 2.5s infinite; }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .date-input-group { position: relative; }
        .date-input-group i {
            position: absolute; right: 15px; top: 50%; transform: translateY(-50%);
            color: #ced4da; pointer-events: none;
        }
        .date-input-group .form-control {
            border: 1px solid #ced4da; border-bottom: 3px solid #ced4da; border-radius: 5px; padding-right: 40px;
        }
        .date-input-group .form-control:focus { border-color: #ced4da; border-bottom-color: var(--navy); box-shadow: none; }

        .btn-navy { background-color: var(--navy); color: #fff; border-color: var(--navy); transition: all 0.3s ease; }
        .btn-navy:hover { background-color: var(--primary-hover); color: #fff; }

        .btn-outline-navy { color: var(--navy); border: 2px solid var(--navy); background-color: transparent; transition: all 0.3s ease; }
        .btn-outline-navy:hover { color: #ffffff; background-color: var(--navy); }

        /* --- Desain Grouping Tanggal --- */
        .date-group-header {
            background: #e9ecef;
            padding: 12px 20px;
            border-radius: 5px;
            border-left: 5px solid #dc3545;
            margin-top: 15px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .date-group-header:hover { background-color: #dde2e6; }

        /* Animasi Panah Putar */
        .toggle-icon {
            transition: transform 0.3s ease;
            font-size: 1.2rem;
            color: var(--navy);
        }
        .rotate-180 {
            transform: rotate(180deg);
        }

        /* --- Desain Card PO --- */
        .card-po { border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; transition: transform 0.2s ease-in-out; }
        .card-po:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .card-po .card-header { background-color: var(--navy); color: white; border-bottom: 0; }

        .timeline-box { display: flex; align-items: center; }
        .timeline-dot { width: 10px; height: 10px; background-color: #dc3545; border-radius: 50%; margin-right: 8px; }

        .skeleton-box { position: relative; overflow: hidden; background-color: #e2e5e7; border-radius: 4px; }
        .skeleton-box::after {
            position: absolute; top: 0; right: 0; bottom: 0; left: 0; transform: translateX(-100%);
            background-image: linear-gradient(90deg, rgba(255, 255, 255, 0) 0, rgba(255, 255, 255, 0.2) 20%, rgba(255, 255, 255, 0.5) 60%, rgba(255, 255, 255, 0));
            animation: shimmer 1.5s infinite; content: '';
        }
        @keyframes shimmer { 100% { transform: translateX(100%); } }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-4">

        <div class="alert-panel shadow-sm">
            <div class="alert-panel-header">
                <h4 class="text-danger font-weight-bold mb-0">
                    <i class="fas fa-exclamation-triangle mr-2 blink-icon"></i> Alert Shipment
                </h4>
                <span class="badge badge-danger p-2">
                    {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}
                </span>
            </div>

            <form id="filterForm" action="{{ route('dashboard-export-import') }}" method="GET" class="mt-3">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="font-weight-bold mb-1"><small>Dari Tanggal:</small></label>
                        <div class="date-input-group">
                            <input type="date" name="tgl_awal" class="form-control" value="{{ $tgl_awal ?? date('Y-m-d') }}">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold mb-1"><small>Sampai Tanggal:</small></label>
                        <div class="date-input-group">
                            <input type="date" name="tgl_akhir" class="form-control" value="{{ $tgl_akhir ?? date('Y-m-d', strtotime('+7 days')) }}">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                    <div class="col-md-2 mt-2 mt-md-0">
                        <button type="submit" class="btn btn-navy w-100 font-weight-bold">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2 mt-2 mt-md-0">
                        <a href="{{ route('dashboard-export-import') }}" class="btn btn-outline-secondary w-100 text-dark">
                            <i class="fas fa-sync-alt"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card shadow border-0 mb-5">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="font-weight-bold text-dark mb-0">
                    <i class="fas fa-clipboard-list mr-2 text-navy"></i> Daftar PO Shipment
                </h5>
            </div>

            <div class="card-body" id="main-content-area">

                @php
                    $groupedData = collect($data ?? [])->groupBy('tgl_shipment')->sortKeys();
                @endphp

                <div id="data-container">
                    @forelse($groupedData as $tanggal => $items)

                        <div class="date-group-header shadow-sm btn-custom-collapse" data-target="#collapseDate{{ $loop->index }}">
                            <div class="d-flex align-items-center">
                                <span class="font-weight-bold text-dark" style="font-size: 1.1rem;">
                                    <i class="fas fa-calendar-day mr-2 text-danger"></i>
                                    {{ \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('l, d F Y') }}
                                </span>
                                <span class="badge badge-danger px-3 py-2 ml-3" style="font-size: 0.9rem;">
                                    {{ count($items) }} PO
                                </span>
                            </div>
                            <i class="fas fa-chevron-down toggle-icon"></i>
                        </div>

                        <div id="collapseDate{{ $loop->index }}" style="display: none;">
                            <div class="row px-2 pt-3 pb-2 border-bottom">
                                @foreach($items as $item)
                                    <div class="col-12 col-md-6 col-lg-3 mb-4">
                                        <div class="card card-po shadow-sm h-100 bg-white">
                                            <div class="card-header font-weight-bold d-flex align-items-center">
                                                <span>PO: {{ $item->po }}</span>
                                                <i class="fas fa-ship ml-auto"></i>
                                            </div>
                                            <div class="card-body">
                                                <h6 class="font-weight-bold text-dark mb-1">{{ $item->desc }}</h6>
                                                <p class="small mb-3">
                                                    <i class="fas fa-map-marker-alt mr-1"></i> Dest: {{ $item->dest }}
                                                </p>

                                                <div class="mb-2">
                                                    <small class="d-block">Total QTY:</small>
                                                    <span class="font-weight-bold text-dark" style="font-size: 1.1rem;">
                                                        <i class="fas fa-shirt mr-1 text-navy"></i> {{ number_format($item->total_qty, 0, ',', '.') }} PCS
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="card-footer pb-3 pt-0 text-center">
                                                <button class="btn btn-outline-navy w-100 font-weight-bold py-2 btn-sm"
                                                    onclick="cekDetail('{{ $item->po }}', '{{ $item->desc }}', '{{ $item->total_qty }}')">
                                                    <i class="fas fa-search mr-1"></i> Cek Detail
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    @empty
                        <div class="alert alert-success shadow-sm d-flex align-items-center p-4">
                            <i class="fas fa-check-circle fa-2x mr-3"></i>
                            <div> Belum ada PO yang mendekati jadwal Shipment.</div>
                        </div>
                    @endforelse
                </div>

                <div class="row d-none" id="skeleton-container">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="col-12 col-md-6 col-lg-3 mb-4">
                            <div class="card card-po shadow-sm h-100">
                                <div class="card-header d-flex align-items-center" style="background-color: #e2e5e7; height: 45px;">
                                    <div class="skeleton-box" style="width: 100px; height: 15px;"></div>
                                </div>
                                <div class="card-body">
                                    <div class="skeleton-box mb-2" style="width: 80%; height: 20px;"></div>
                                    <div class="skeleton-box mb-4" style="width: 40%; height: 15px;"></div>
                                    <div class="skeleton-box" style="width: 60%; height: 20px;"></div>
                                </div>
                                <div class="card-footer bg-white pb-3 pt-0 text-center">
                                    <div class="skeleton-box w-100" style="height: 35px; border-radius: 5px;"></div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border: 2px solid var(--navy);">
                <div class="modal-header text-white" style="background-color: var(--navy);">
                    <h5 class="modal-title font-weight-bold" id="detailModalLabel">Rincian PO</h5>
                    <button type="button" class="close text-white" data-bs-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3 bg-light p-3 rounded mx-1 border shadow-sm text-center">
                        <div class="col-md-6 border-right">
                            <small class="d-block">Item:</small>
                            <strong id="modalDesc" class="text-navy">-</strong>
                        </div>
                        <div class="col-md-6">
                            <small class="d-block">Total QTY:</small>
                            <strong id="modalTotal" class="text-dark">-</strong>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-bordered table-striped table-sm table-hover mb-0">
                            <thead class="text-center">
                                <tr>
                                    <th>No</th>
                                    <th>WS</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th>QTY</th>
                                </tr>
                            </thead>
                            <tbody id="detailTableBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary font-weight-bold px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script>
        $(document).ready(function() {


            $('.btn-custom-collapse').on('click', function() {
                let targetArea = $(this).data('target');
                $(targetArea).slideToggle(300);
                $(this).find('.toggle-icon').toggleClass('rotate-180');
            });


            $('#filterForm').on('submit', function() {
                $('#data-container').addClass('d-none');
                $('#skeleton-container').removeClass('d-none');
                let btn = $(this).find('button[type="submit"]');
                btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
            });
        });

        function cekDetail(po, desc, total) {
            let formattedTotal = new Intl.NumberFormat('id-ID').format(total) + ' PCS';
            $('#detailModalLabel').text('Rincian PO: ' + po);
            $('#modalDesc').text(desc);
            $('#modalTotal').text(formattedTotal);
            $('#detailTableBody').html('<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary spinner-border-sm"></div> Loading...</td></tr>');
            $('#detailModal').modal('show');

            $.ajax({
                url: '{{ url("/export-import/alert-detail") }}?po=' + encodeURIComponent(po),
                type: 'GET',
                success: function(res) {
                    let html = '';
                    if(res.data && res.data.length > 0) {
                        $.each(res.data, function(i, item) {
                            html += `<tr class="text-center">
                                <td>${i + 1}</td>
                                <td>${item.kpno}</td>
                                <td>${item.color}</td>
                                <td>${item.size}</td>
                                <td class="font-weight-bold">${item.qty_po}</td>
                            </tr>`;
                        });
                    } else {
                        html = '<tr><td colspan="5" class="text-center py-3">Data tidak ditemukan.</td></tr>';
                    }
                    $('#detailTableBody').html(html);
                },
                error: function() {
                    $('#detailTableBody').html('<tr><td colspan="5" class="text-center py-3 text-danger">Gagal memuat data.</td></tr>');
                }
            });
        }
    </script>
@endsection
