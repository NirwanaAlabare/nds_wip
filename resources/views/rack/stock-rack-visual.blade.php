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
                                                                $stockerData = $stockers->where('detail_rack_id', $rackDetail->id);
                                                            @endphp

                                                            @if ($stockerData)
                                                                @foreach ($stockerData as $data)
                                                                    <div class="col">
                                                                        <div class="card h-100"
                                                                            data-bs-toggle="tooltip"
                                                                            data-bs-placement="right"
                                                                            data-bs-custom-class="custom-tooltip"
                                                                            data-bs-html="true"
                                                                            data-bs-title="
                                                                                WS : <strong>{{ $data->act_costing_ws }}</strong><br>
                                                                                Buyer : <strong>{{ $data->buyer }}</strong><br>
                                                                                Style : <strong>{{ $data->style }}</strong><br>
                                                                                Color : <strong>{{ $data->color }}</strong><br>
                                                                                Size : <strong>{{ $data->size }}</strong><br>
                                                                                No. Cut : <strong>{{ $data->no_cut }}</strong><br>
                                                                                Shade : <strong>{{ $data->shade }}</strong><br>
                                                                                Qty : <strong>{{ $data->qty_ply }}</strong><br>
                                                                                Range : <strong>{{ $data->full_range }}</strong><br>
                                                                            "
                                                                        >
                                                                            <div class="card-body" onclick="openModal({{ json_encode($data) }})"  data-bs-toggle="modal" data-bs-target="#detailModal">
                                                                                {{ $data->style }}
                                                                                <br>
                                                                                <small>{{ $data->color }}</small>
                                                                            </div>
                                                                        </div>
                                                                    </div>
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
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb-secondary">
                    <h1 class="modal-title fs-5" id="detailModalLabel">Detail</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeModal()"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-sm" id="detail-table">
                        <tbody>
                        </tbody>
                    </table>
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
        var detailTable = document.getElementById('detail-table');
        var detailTableTbody = detailTable.getElementsByTagName("tbody")[0];

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

        function openModal(data) {
            for (let key in data) {
                if (key != "detail_rack_id" && key != "form_cut_id" && key != 'group_stocker' && key != 'so_det_id') {
                    let tr = document.createElement("tr");

                    let th = document.createElement("th");
                    th.innerText = key.replace(/_/g, " ").toUpperCase();

                    let td = document.createElement("td");
                    td.innerText = data[key];

                    tr.appendChild(th);
                    tr.appendChild(td);

                    detailTableTbody.appendChild(tr);
                }
            }

            $.ajax({
                url: '{{ route('stock-rack-visual-detail') }}',
                type: 'get',
                data: {
                    'form_cut_id' : data.form_cut_id,
                    'so_det_id' : data.so_det_id,
                    'group_stocker' : data.group_stocker,
                    'ratio' : data.ratio,
                },
                success: async function(res) {
                    if (res) {
                        let tr = document.createElement("tr");
                        let th = document.createElement("th");
                        th.innerText = "PART";
                        th.classList.add("align-top");
                        th.setAttribute("rowspan", res.length + 1);

                        tr.appendChild(th);

                        detailTableTbody.appendChild(tr);

                        await res.forEach(item => {
                            let tr = document.createElement("tr");

                            let td = document.createElement("td");
                            td.innerText = item.stocker;

                            tr.appendChild(td);

                            detailTableTbody.appendChild(tr);
                        });
                    }
                }
            });

            $('#detailModal').show();
        }

        function closeModal() {
            detailTableTbody.innerHtml = "";
            detailTableTbody.innerText = "";

            $('#detailModal').hide();
        }
    </script>
@endsection
