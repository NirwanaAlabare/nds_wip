@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card">
        <div class="card-header bg-sb text-light">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-table fa-sm"></i> Rak</h5>
        </div>

        <div class="card-body">
            <div class="accordion" id="accordionPanelsStayOpenExample">
                @foreach ($racks as $rack)
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button {{ $loop->index % 2 != 0 ? 'accordion-blue' : 'accordion-blue-sec' }}" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen{{ $loop->index }}" aria-expanded="true" aria-controls="panelsStayOpen{{ $loop->index }}">
                                <h5 class="fw-bold ps-1">{{ $rack->nama_rak }}</h5>
                            </button>
                        </h2>
                        <div id="panelsStayOpen{{ $loop->index }}" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm w-100">
                                        <thead>
                                            <tr>
                                                <th>Rak</th>
                                                @foreach ($rack->rackDetails as $rackDetail)
                                                    <th colspan="{{ $rackDetail->rackDetailStockers->count() > 0 ? $rackDetail->rackDetailStockers->count() : 1 }}">{{ $rackDetail->nama_detail_rak }}</th>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>No. Stocker</th>
                                                @foreach ($rack->rackDetails as $rackDetail)
                                                    @if ($rackDetail->rackDetailStockers && $rackDetail->rackDetailStockers->count() > 0)
                                                        @foreach ($rackDetail->rackDetailStockers as $rackDetailStocker)
                                                            <td>{{ $rackDetailStocker->stocker->id_qr_stocker }}</td>
                                                        @endforeach
                                                    @else
                                                        <td>&nbsp;</td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>No. WS</th>
                                                @foreach ($rack->rackDetails as $rackDetail)
                                                    @if ($rackDetail->rackDetailStockers && $rackDetail->rackDetailStockers->count() > 0)
                                                        @foreach ($rackDetail->rackDetailStockers as $rackDetailStocker)
                                                            <td>{{ $rackDetailStocker->stocker->act_costing_ws }}</td>
                                                        @endforeach
                                                    @else
                                                        <td>&nbsp;</td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>No. Cut</th>
                                                @foreach ($rack->rackDetails as $rackDetail)
                                                    @if ($rackDetail->rackDetailStockers && $rackDetail->rackDetailStockers->count() > 0)
                                                        @foreach ($rackDetail->rackDetailStockers as $rackDetailStocker)
                                                            <td>{{ $rackDetailStocker->stocker->formCut->no_cut }}</td>
                                                        @endforeach
                                                    @else
                                                        <td>&nbsp;</td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>Style</th>
                                                @foreach ($rack->rackDetails as $rackDetail)
                                                    @if ($rackDetail->rackDetailStockers && $rackDetail->rackDetailStockers->count() > 0)
                                                        @foreach ($rackDetail->rackDetailStockers as $rackDetailStocker)
                                                            <td>{{ $rackDetailStocker->stocker->formCut->marker->style }}</td>
                                                        @endforeach
                                                    @else
                                                        <td>&nbsp;</td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>Color</th>
                                                @foreach ($rack->rackDetails as $rackDetail)
                                                    @if ($rackDetail->rackDetailStockers && $rackDetail->rackDetailStockers->count() > 0)
                                                        @foreach ($rackDetail->rackDetailStockers as $rackDetailStocker)
                                                            <td>{{ $rackDetailStocker->stocker->formCut->marker->color }}</td>
                                                        @endforeach
                                                    @else
                                                        <td>&nbsp;</td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>Size</th>
                                                @foreach ($rack->rackDetails as $rackDetail)
                                                    @if ($rackDetail->rackDetailStockers && $rackDetail->rackDetailStockers->count() > 0)
                                                        @foreach ($rackDetail->rackDetailStockers as $rackDetailStocker)
                                                            <td>{{ $rackDetailStocker->stocker->size }}</td>
                                                        @endforeach
                                                    @else
                                                        <td>&nbsp;</td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>Qty</th>
                                                @foreach ($rack->rackDetails as $rackDetail)
                                                    @if ($rackDetail->rackDetailStockers && $rackDetail->rackDetailStockers->count() > 0)
                                                        @foreach ($rackDetail->rackDetailStockers as $rackDetailStocker)
                                                            <td>{{ $rackDetailStocker->stocker->cut_qty }}</td>
                                                        @endforeach
                                                    @else
                                                        <td>&nbsp;</td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>Part Tersedia</th>
                                                @foreach ($rack->rackDetails as $rackDetail)
                                                    @if ($rackDetail->rackDetailStockers && $rackDetail->rackDetailStockers->count() > 0)
                                                        @foreach ($rackDetail->rackDetailStockers as $rackDetailStocker)
                                                            <td>-</td>
                                                        @endforeach
                                                    @else
                                                        <td>&nbsp;</td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th>Part Belum Lengkap</th>
                                                @foreach ($rack->rackDetails as $rackDetail)
                                                    @if ($rackDetail->rackDetailStockers && $rackDetail->rackDetailStockers->count() > 0)
                                                        @foreach ($rackDetail->rackDetailStockers as $rackDetailStocker)
                                                            <td>-</td>
                                                        @endforeach
                                                    @else
                                                        <td>&nbsp;</td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered bg-white">
                @foreach ($racks as $rack)
                    <tr>
                        @foreach ($rack->rackDetails as $rackDetail)
                            <th class="text-center bg-sb-secondary">{{ $rackDetail->nama_detail_rak }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($rack->rackDetails as $rackDetail)
                            <th class="{{ $loop->index > 1 ? 'bg-light' : 'bg-warning' }} p-3" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-custom-class="custom-tooltip" data-bs-title="This top tooltip is themed via CSS variables.">
                                <a href="youtube.com" class="h-100 w-100">&nbsp;</a>
                            </th>
                        @endforeach
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        // let datatableRack = $("#datatable-rack").DataTable({
        //     ordering: false,
        //     processing: true,
        //     serverSide: true,
        //     ajax: {
        //         url: '{{ route('rack-detail') }}',
        //     },
        //     columns: [
        //         {
        //             data: 'kode',
        //         },
        //         {
        //             data: 'act_costing_ws',
        //         },
        //         {
        //             data: 'style'
        //         },
        //         {
        //             data: 'color'
        //         },
        //         {
        //             data: 'size'
        //         },
        //         {
        //             data: 'no_cut'
        //         },
        //         {
        //             data: 'part_details'
        //         },
        //         {
        //             data: 'qty_cut'
        //         },
        //         {
        //             data: 'part_details_unavailable'
        //         },
        //     ],
        // });

        // function datatableRackReload() {
        //     datatableRack.ajax.reload()
        // }
    </script>
@endsection
