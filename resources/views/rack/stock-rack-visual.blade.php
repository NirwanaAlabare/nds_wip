@extends('layouts.index', ["page" => $page])

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Apex Charts -->
    <link rel="stylesheet" href="{{ asset('plugins/apexcharts/apexcharts.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .tooltip-inner {
            text-align: left !important;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <h5 class="text-sb text-center mb-3 fw-bold">
            <i class="fas fa-th-list fa-sm"></i> Stok Rak
        </h5>
        <div class="card card-sb">
            <div class="card-body">
                <div class="row justify-content-between align-items-center">
                    <div class="col-6 col-sm-3">
                        <select id="rack-group" class="form-select form-select-sm select2bs4">
                            @foreach ($racks->groupBy('grup') as $rackGroup )
                                <option value="{{ $loop->index }}" {{ $loop->first ? "selected" : "" }}>{{ $rackGroup[0]['grup'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="d-flex justify-content-end gap-1">
                            <button class="btn btn-sb btn-sm" type="button" data-bs-target="#carouselExample" data-bs-slide="prev" id="previous-button">
                                <i class="fas fa-angle-left fa-lg"></i>
                            </button>
                            <button class="btn btn-sb btn-sm" type="button" data-bs-target="#carouselExample" data-bs-slide="next" id="next-button">
                                <i class="fas fa-angle-right fa-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div id="carouselExample" class="carousel slide">
                    <div class="carousel-inner">
                        @foreach ($racks->groupBy('grup') as $rackGroup )
                            <div class="carousel-item {{ $loop->first ? 'active' : '' }} mt-3" id="carousel-{{ $loop->index }}" data-group="{{ $rackGroup[0]['grup'] }}">
                                <table class="table table-bordered table-sm bg-white">
                                    @foreach ($racks->where('grup', $rackGroup[0]['grup']) as $rack)
                                        <tr>
                                            @foreach ($rack->rackDetails as $rackDetail)
                                                <th class="text-center bg-sb-secondary">{{ $rackDetail->nama_detail_rak }}</th>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            @foreach ($rack->rackDetails as $rackDetail)
                                                <th class="{{ ($rackDetail->rackDetailStockers && $rackDetail->rackDetailStockers->count() > 0) ? 'bg-warning align-top' : '' }} w-50 p-3">
                                                    <div class="row row-cols-1 row-cols-sm-2">
                                                        @if ($rackDetail->rackDetailStockers && $rackDetail->rackDetailStockers->count() > 0)
                                                            @foreach ($rackDetail->rackDetailStockers->sortBy('id_qr_stocker') as $rackDetailStocker)
                                                                <div class="col">
                                                                    <div class="card"
                                                                        data-bs-toggle="tooltip"
                                                                        data-bs-placement="right"
                                                                        data-bs-custom-class="custom-tooltip"
                                                                        data-bs-html="true"
                                                                        data-bs-title="
                                                                            WS : <strong>{{ ($rackDetailStocker->stocker ? $rackDetailStocker->stocker->act_costing_ws : '-') }}</strong><br>
                                                                            Color : <strong>{{ ($rackDetailStocker->stocker ? $rackDetailStocker->stocker->color : '-') }}</strong><br>
                                                                            No. Cut : <strong>{{ ($rackDetailStocker->stocker ? $rackDetailStocker->stocker->formCut->no_cut : '-') }}</strong><br>
                                                                            Shade : <strong>{{ ($rackDetailStocker->stocker ? $rackDetailStocker->stocker->shade : '-') }}</strong><br>
                                                                            Size : <strong>{{ ($rackDetailStocker->stocker ? $rackDetailStocker->stocker->size : '-') }}</strong><br>
                                                                            Part : <strong>{{( $rackDetailStocker->stocker ? $rackDetailStocker->stocker->partDetail->masterPart->nama_part : '-') }}</strong><br>
                                                                            Range : <strong>{{ ($rackDetailStocker->stocker ? $rackDetailStocker->stocker->range_awal : '-')." - ".($rackDetailStocker->stocker ? $rackDetailStocker->stocker->range_akhir : '-') }}</strong><br>
                                                                        "
                                                                    >
                                                                        <div class="card-body">
                                                                            {{ $rackDetailStocker->stocker ? $rackDetailStocker->stocker->id_qr_stocker : '-' }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            &nbsp;
                                                        @endif
                                                    </div>
                                                </th>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        });

        // Carousel
        var carouselSlid = false;

        document.getElementById('carouselExample').addEventListener('slid.bs.carousel', event => {
            if (!(carouselSlid)) {
                carouselSlid = true;

                $('#rack-group').val(event.to).trigger('change');
            } else {
                carouselSlid = false;
            }
        });

        $('#rack-group').on("change", function(event) {
            if (!(carouselSlid)) {
                carouselSlid = true;

                let rackNumber = Number(this.value);
                $('#carouselExample').carousel(rackNumber);
            } else {
                carouselSlid = false;
            }
        });
    </script>
@endsection
