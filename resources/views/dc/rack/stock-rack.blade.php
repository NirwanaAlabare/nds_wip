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
            <a href="{{ route('allocate-rack') }}" class="btn btn-success btn-sm mb-3"><i class="fa fa-plus"></i> Alokasi</a>
            <div class="accordion" id="accordionPanelsStayOpenExample">
                @foreach ($racks as $rack)
                    @php
                        $rackDetails = $rack->rackDetails;
                    @endphp
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button {{ $loop->index % 2 != 0 ? 'accordion-blue' : 'accordion-blue-sec' }}" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen{{ $loop->index }}" aria-expanded="true" aria-controls="panelsStayOpen{{ $loop->index }}">
                                <h5 class="fw-bold ps-1">{{ $rack->nama_rak }}</h5>
                            </button>
                        </h2>
                        <div id="panelsStayOpen{{ $loop->index }}" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table w-100">
                                        <thead>
                                            <tr>
                                                <th>Rak</th>
                                                <th>No. Stocker</th>
                                                <th>No. WS</th>
                                                <th>No. Cut</th>
                                                <th>Shade</th>
                                                <th>Style</th>
                                                <th>Color</th>
                                                <th>Size</th>
                                                <th>Qty</th>
                                                <th>Part Tersedia</th>
                                                <th>Part Belum Lengkap</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($rackDetails as $rackDetail)
                                                @php
                                                    $stockerData = $stockers->where('detail_rack_id', $rackDetail->id);
                                                @endphp

                                                @if ($stockerData)
                                                    <tr>
                                                        <td class="fw-bold" {{ $stockerData->where('detail_rack_id', $rackDetail->id)->count() > 0 ? 'rowspan='.$stockerData->where('detail_rack_id', $rackDetail->id)->count() .'' : '' }}>{{ $rackDetail->nama_detail_rak }}</td>
                                                        @foreach ($stockerData as $stocker)
                                                            @if ($loop->index != 0)
                                                                <tr>
                                                            @endif

                                                            <td class="text-nowrap">{{ $stocker->stockers }}</td>
                                                            <td class="text-nowrap">{{ $stocker->act_costing_ws }}</td>
                                                            <td class="text-nowrap">{{ $stocker->no_cut }}</td>
                                                            <td class="text-nowrap">{{ $stocker->shade }}</td>
                                                            <td class="text-nowrap">{{ $stocker->style }}</td>
                                                            <td class="text-nowrap">{{ $stocker->color }}</td>
                                                            <td class="text-nowrap">{{ $stocker->act_costing_ws }}</td>
                                                            <td class="text-nowrap">{{ $stocker->qty_ply }}</td>
                                                            <td class="text-nowrap">{{ '-' }}</td>
                                                            <td class="text-nowrap">{{ '-' }}</td>

                                                            @if ($loop->index != 0)
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    <tr>
                                                @endif
                                            @endforeach
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
@endsection

@section('custom-script')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        let datatableRack = $("#datatable-rack").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('allocate-rack') }}',
            },
            columns: [
                {
                    data: 'kode',
                },
                {
                    data: 'act_costing_ws',
                },
                {
                    data: 'style'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'no_cut'
                },
                {
                    data: 'part_details'
                },
                {
                    data: 'qty_cut'
                },
                {
                    data: 'part_details_unavailable'
                },
            ],
        });

        function datatableRackReload() {
            datatableRack.ajax.reload()
        }
    </script>
@endsection
