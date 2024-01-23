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
                                                <th class="{{ ($rackDetail->rackDetailStockers && $rackDetail->rackDetailStockers->count() > 0) ? 'bg-warning align-top h-100' : '' }} w-50 p-3">
                                                    <div class="row row-cols-1 row-cols-sm-2 g-3">
                                                        @if ($rackDetail->rackDetailStockers && $rackDetail->rackDetailStockers->count() > 0)
                                                            @php
                                                                $thisRackStocker = collect([]);
                                                            @endphp

                                                            @foreach ($rackDetail->rackDetailStockers->sortBy('stocker_id') as $rackDetailStocker)
                                                                @php
                                                                    if ($rackDetailStocker->stocker) {
                                                                        $thisRackStocker->push($rackDetailStocker->stocker);
                                                                    }
                                                                @endphp
                                                            @endforeach

                                                            @if ($thisRackStocker)
                                                                @foreach ($thisRackStocker->groupBy(["form_cut_id", "so_det_id", "group_stocker", "ratio"], $preserveKeys = true) as $stockerGroup)
                                                                    @foreach ($stockerGroup as $soDetId => $item)
                                                                        @php
                                                                            $data = [];

                                                                            // SO Det
                                                                            if (count($stockerGroup[$soDetId]) > 0) {
                                                                                foreach ($stockerGroup[$soDetId] as $groupStocker => $item1) {
                                                                                    // Group Stocker
                                                                                    if (count($stockerGroup[$soDetId][$groupStocker]) > 0) {
                                                                                        foreach ($stockerGroup[$soDetId][$groupStocker] as $ratio => $item2) {
                                                                                            // Ratio
                                                                                            if (count($stockerGroup[$soDetId][$groupStocker][$ratio]) > 0) {
                                                                                                foreach ($stockerGroup[$soDetId][$groupStocker][$ratio] as $key => $item3) {
                                                                                                    if (isset($data['act_costing_ws']) && $data['act_costing_ws'] == $item3->act_costing_ws) {
                                                                                                        array_push($data, ['act_costing_ws' => $item3->act_costing_ws]);
                                                                                                    } else {
                                                                                                        $data['act_costing_ws'] .= $item3->act_costing_ws;
                                                                                                    }

                                                                                                    if (isset($data['size']) && $data['size'] == $item3->size) {
                                                                                                        array_push($data, ['size' => $item3->size]);
                                                                                                    } else {
                                                                                                        $data['size'] .= $item3->size;
                                                                                                    }

                                                                                                    if (isset($data['shade']) && $data['shade'] == $item3->shade) {
                                                                                                        array_push($data, ['shade' => $item3->shade]);
                                                                                                    } else {
                                                                                                        $data['shade'] .= $item3->shade;
                                                                                                    }

                                                                                                    if (isset($data['ratio']) && $data['ratio'] == $item3->ratio) {
                                                                                                        array_push($data, ['ratio' => $item3->ratio]);
                                                                                                    } else {
                                                                                                        $data['ratio'] .= $item3->ratio;
                                                                                                    }

                                                                                                    if (isset($data['id_qr_stocker']) && $data['id_qr_stocker'] == $item3->id_qr_stocker) {
                                                                                                        array_push($data, ['id_qr_stocker' => $item3->id_qr_stocker]);
                                                                                                    } else {
                                                                                                        $data['id_qr_stocker'] .= $item3->id_qr_stocker;
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        @endphp

                                                                        <div class="col">
                                                                            <div class="card h-100"
                                                                                data-bs-toggle="tooltip"
                                                                                data-bs-placement="right"
                                                                                data-bs-custom-class="custom-tooltip"
                                                                                data-bs-html="true"
                                                                                data-bs-title="
                                                                                    WS : <strong>{{ ( $data['act_costing_ws'] ) }}</strong><br>
                                                                                    Color : <strong>{{ ( $data['color'] ) }}</strong><br>
                                                                                    No. Cut : <strong>{{ "" }}</strong><br>
                                                                                    Shade : <strong>{{ ( $data['shade'] ) }}</strong><br>
                                                                                    Size : <strong>{{ $data['ratio'] }}</strong><br>
                                                                                    Part : <strong>{{ "" }}</strong><br>
                                                                                    Range : <strong>{{ "" }}</strong><br>
                                                                                "
                                                                            >
                                                                                <div class="card-body">
                                                                                    {{ $data['id_qr_stocker'] }}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                @endforeach
                                                            @endif
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
