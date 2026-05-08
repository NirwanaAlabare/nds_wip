@extends('layouts.index')

@section('custom-link')
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        /* Tema Warna */
        :root { --navy: #081e3f; --primary-hover: #030d1a; }

        /* --- Panel Alert & Filter --- */
        .alert-panel {
            background-color: #fff;
            border-radius: 8px;
            border-top: 5px solid var(--navy);
            padding: 20px;
            margin-bottom: 25px;
            position: relative;
        }

        /* Animasi Denyut pada Header Panel */
        .alert-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
            animation: headerPulse 3s infinite;
        }
        @keyframes headerPulse {
            0% { box-shadow: 0 0 0 0px rgba(220, 53, 69, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0px rgba(220, 53, 69, 0); }
        }

        /* Ikon Peringatan Berkedip */
        .blink-icon { animation: blink 2.5s infinite; }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Input Tanggal Elegan */
        .date-input-group { position: relative; }
        .date-input-group i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #ced4da;
            pointer-events: none;
        }
        .date-input-group .form-control {
            border: 1px solid #ced4da;
            border-bottom: 3px solid #ced4da;
            border-radius: 5px;
            padding-right: 40px;
            transition: border-bottom-color 0.3s ease;
        }
        .date-input-group .form-control:focus {
            border-color: #ced4da;
            border-bottom-color: var(--navy);
            box-shadow: none;
        }

        /* Tombol Custom */
        .btn-navy {
            background-color: var(--navy);
            color: #fff;
            border-color: var(--navy);
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .btn-navy:hover, .btn-navy:focus {
            background-color: var(--primary-hover);
            color: #fff;
            box-shadow: 0 0 10px rgba(8, 30, 63, 0.3);
        }
        .btn-outline-navy {
            color: var(--navy);
            border: 2px solid var(--navy);
            background-color: transparent;
            transition: all 0.3s ease;
        }
        .btn-outline-navy:hover {
            color: #ffffff;
            background-color: var(--navy);
            border-color: var(--navy);
            box-shadow: 0 0 10px rgba(8, 30, 63, 0.3);
        }

        /* --- Desain Card PO --- */
        .card-po {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .card-po:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .card-po .card-header { background-color: var(--navy); color: white; border-bottom: 0; }
        .card-po .card-body { background-color: #fafbfe; }
        .card-po .card-footer { background-color: #fff; border-top: 0; }

        /* Ikon Destinasi */
        .dest-icon { color: #5bc0de; font-size: 1.1rem; }
        .dest-icon-franchis { color: #31708f; }
        .dest-icon-us { color: #b52b27; }

        /* Garis Waktu (Timeline) */
        .timeline-box { display: flex; align-items: flex-start; }
        .timeline-dot {
            width: 10px; height: 10px;
            background-color: #dc3545;
            border-radius: 50%;
            margin-right: 8px;
        }
        .timeline-line {
            width: 2px; height: 20px;
            background-color: #ced4da;
            margin-left: 4px; /* Supaya sejajar dengan dot */
        }

        /* Skeleton Animasi */
        .skeleton-box {
            position: relative; overflow: hidden;
            background-color: #e2e5e7; border-radius: 4px;
        }
        .skeleton-box::after {
            position: absolute; top: 0; right: 0; bottom: 0; left: 0;
            transform: translateX(-100%);
            background-image: linear-gradient(90deg, rgba(255, 255, 255, 0) 0, rgba(255, 255, 255, 0.2) 20%, rgba(255, 255, 255, 0.5) 60%, rgba(255, 255, 255, 0));
            animation: shimmer 1.5s infinite;
            content: '';
        }
        @keyframes shimmer { 100% { transform: translateX(100%); } }
    </style>

    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="container-fluid mt-4">

        <div class="alert-panel shadow-sm">
            <div class="alert-panel-header">
                <h4 class="text-danger font-weight-bold mb-0 d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle mr-3 blink-icon fa-lg"></i> Alert Shipment
                </h4>
                <span class="badge badge-danger p-2" style="font-size: 15px;">
                    {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}
                </span>
            </div>

            <form id="filterForm" action="{{ route('dashboard-export-import') }}" method="GET" class="mt-4">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="font-weight-bold mb-2"><small>Dari Tanggal:</small></label>
                        <div class="date-input-group">
                            <input type="date" name="tgl_awal" class="form-control" value="{{ $tgl_awal ?? date('Y-m-d') }}">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                    <div class="col-md-3 mt-3 mt-md-0">
                        <label class="font-weight-bold mb-2"><small>Sampai Tanggal:</small></label>
                        <div class="date-input-group">
                            <input type="date" name="tgl_akhir" class="form-control" value="{{ $tgl_akhir ?? date('Y-m-d', strtotime('+7 days')) }}">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                    <div class="col-md-2 mt-4 mt-md-0">
                        <button type="submit" class="btn btn-navy w-100 font-weight-bold py-2">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2 mt-2 mt-md-0">
                        <a href="{{ route('dashboard-export-import') }}" class="btn btn-outline-secondary w-100 py-2">
                            <i class="fas fa-sync-alt mr-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="row" id="data-container">
            @forelse($data ?? [] as $item)
                <div class="col-md-4 mb-4">
                    <div class="card card-po shadow-sm h-100">
                        <div class="card-header font-weight-bold d-flex align-items-center">
                            <span style="font-size: 1.1rem;">PO: {{ $item->po }}</span>
                            <i class="fas fa-ship ml-auto fa-lg"></i>
                        </div>

                        <div class="card-body">
                            <h5 class="card-title font-weight-bold text-dark w-100 mb-1" style="font-size: 1.1rem;">{{ $item->desc }}</h5>
                            <h6 class="card-subtitle mb-3 "><i class="fas fa-map-marker-alt"></i> Dest: {{ $item->dest }}</h6>

                            <div class="timeline-box align-items-center mb-3">
                                <div class="col-auto p-0 mr-3">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-line"></div>
                                    <div class="timeline-dot"></div>
                                </div>
                                <div class="col p-0">
                                    <small class="d-block ">Tanggal Shipment:</small>
                                    <span style="font-size: 1.25rem;" class="font-weight-bold text-danger">
                                        {{ \Carbon\Carbon::parse($item->tgl_shipment)->locale('id')->translatedFormat('l, d F Y') }}
                                    </span>
                                </div>
                            </div>

                            <div class="mb-2">
                                <small class="d-block ">Total QTY PO:</small>
                                <span style="font-size: 1.25rem;" class="font-weight-bold text-dark">
                                    <i class="fas fa-list-ol mr-2" style="color: var(--navy);"></i>{{ number_format($item->total_qty, 0, ',', '.') }} PCS
                                </span>
                            </div>
                        </div>

                        <div class="card-footer pb-3 pt-0 text-center">
                            <button class="btn btn-outline-navy w-100 font-weight-bold py-2" onclick="cekDetail('{{ $item->po }}', '{{ $item->desc }}', '{{ $item->total_qty }}')">
                                <i class="fas fa-search-location mr-1"></i> Cek Detail
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-success shadow-sm d-flex align-items-center p-4" style="border-radius: 8px;">
                        <i class="fas fa-check-circle fa-2x mr-3"></i>
                        <div>
                            <strong>Aman Terkendali!</strong><br>
                            Belum ada PO yang mendekati jadwal Shipment pada rentang tanggal filter yang dipilih.
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="row d-none" id="skeleton-container">
            @for ($i = 0; $i < 3; $i++)
                <div class="col-md-4 mb-4">
                    <div class="card card-po shadow-sm h-100">
                        <div class="card-header d-flex align-items-center" style="background-color: #e2e5e7; height: 50px;">
                            <div class="skeleton-box" style="width: 130px; height: 20px;"></div>
                            <div class="skeleton-box ml-auto" style="width: 25px; height: 25px; border-radius: 50%;"></div>
                        </div>
                        <div class="card-body">
                            <div class="skeleton-box mb-2" style="width: 80%; height: 24px;"></div>
                            <div class="skeleton-box mb-4" style="width: 40%; height: 16px;"></div>

                            <div class="skeleton-box mb-1" style="width: 50%; height: 14px;"></div>
                            <div class="skeleton-box mb-3" style="width: 60%; height: 22px;"></div>

                            <div class="skeleton-box mb-1" style="width: 40%; height: 14px;"></div>
                            <div class="skeleton-box" style="width: 50%; height: 22px;"></div>
                        </div>
                        <div class="card-footer bg-white pb-3 pt-0 text-center">
                            <div class="skeleton-box w-100" style="height: 40px; border-radius: 5px;"></div>
                        </div>
                    </div>
                </div>
            @endfor
        </div>

    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border: 2px solid var(--navy);">
                <div class="modal-header text-white" style="background-color: var(--navy);">
                    <h5 class="modal-title font-weight-bold" id="detailModalLabel">Rincian PO</h5>
                    <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3 bg-light p-3 rounded mx-1 border shadow-sm">
                        <div class="col-md-6 border-right">
                            <small class=" d-block">Item Description:</small>
                            <strong id="modalDesc" style="color: var(--navy); font-size: 1.1rem;">-</strong>
                        </div>
                        <div class="col-md-6 text-md-right mt-2 mt-md-0">
                            <small class=" d-block">Total QTY PO:</small>
                            <strong id="modalTotal" class="text-dark" style="font-size: 1.1rem;">-</strong>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-bordered table-striped table-sm table-hover mb-0">
                            <thead class="thead-light sticky-top">
                                <tr>
                                    <th class="text-center" width="5%">No</th>
                                    <th>WS</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th class="text-center" width="15%">QTY</th>
                                </tr>
                            </thead>
                            <tbody id="detailTableBody">
                                </tbody>
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
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('#filterForm').on('submit', function() {
                $('#data-container').addClass('d-none');
                $('#skeleton-container').removeClass('d-none');

                let btnSubmit = $(this).find('button[type="submit"]');
                btnSubmit.html('<i class="fas fa-spinner fa-spin mr-1"></i> Loading...');
                btnSubmit.prop('disabled', true);
            });
        });


        function cekDetail(po, desc, total) {

            let formattedTotal = new Intl.NumberFormat('id-ID').format(total);

            $('#detailModalLabel').text('Rincian PO: ' + po);
            $('#modalDesc').text(desc);
            $('#modalTotal').text(formattedTotal + ' PCS');

            $('#detailTableBody').html('<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary spinner-border-sm" role="status"></div> Memuat data...</td></tr>');

            $('#detailModal').modal('show');

            $.ajax({
                url: '{{ url("/export-import/alert-detail") }}?po=' + encodeURIComponent(po),
                type: 'GET',
                success: function(response) {
                    let html = '';
                    if(response.data && response.data.length > 0) {
                        $.each(response.data, function(index, item) {
                            html += `<tr>
                                <td class="text-center align-middle">${index + 1}</td>
                                <td class="align-middle">${item.kpno}</td>
                                <td class="align-middle">${item.color}</td>
                                <td class="align-middle">${item.size}</td>
                                <td class="text-center align-middle font-weight-bold">${item.qty_po}</td>
                            </tr>`;
                        });
                    } else {
                        html = '<tr><td colspan="5" class="text-center py-4 font-weight-bold text-danger"><i class="fas fa-exclamation-circle mr-1"></i> Data rincian tidak ditemukan.</td></tr>';
                    }
                    $('#detailTableBody').html(html);
                },
                error: function(xhr) {
                    $('#detailTableBody').html('<tr><td colspan="5" class="text-center py-4 text-danger font-weight-bold"><i class="fas fa-times-circle mr-1"></i> Terjadi kesalahan saat memuat data.</td></tr>');
                    console.error(xhr.responseText);
                }
            });
        }
    </script>
@endsection
