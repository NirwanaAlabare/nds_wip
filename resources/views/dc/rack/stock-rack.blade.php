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
            <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div>
                        <label class="form-label"><small>Tanggal Awal</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" value="{{ date('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="form-label"><small>Tanggal Akhir</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}">
                    </div>
                    {{-- <div>
                        <button class="btn btn-primary btn-sm" onclick="dataTableReload()"><i class="fa fa-search"></i></button>
                    </div> --}}
                    <a href="{{ route('allocate-rack') }}" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Alokasi</a>

                </div>
                <div class="d-flex align-items-end gap-2">
                    <div class="mb-3">
                        <button class="btn btn-success btn-sm" id="exportExcelStockOpname" data-bs-toggle="tooltip" data-bs-title="Export Excel" onclick="exportExcelStockOpname()"><i class="fa fa-file-excel"></i> Export Excel Stock Opname</button>
                    </div>
                    <div class="mb-3">
                        <button class="btn btn-success btn-sm" id="exportExcel" data-bs-toggle="tooltip" data-bs-title="Export Excel" onclick="exportExcel()"><i class="fa fa-file-excel"></i> Export Excel</button>
                    </div>
                </div>
            </div>

            <div class="container mt-4">
                <div class="row g-4">
                    @foreach ($racks as $rack)
                        @php
                            $rackDetails = $rack->rackDetails;
                        @endphp

                        <div class="col-12 col-md-6">
                            <div class="card shadow border-0 rounded-4 h-100">
                                <div class="card-header bg-sb text-white rounded-top-4">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <div>
                                            <h5 class="mb-0 fw-bold">
                                                {{ $rack->nama_rak }}
                                            </h5>
                                            <small>
                                                Total Detail Rak : {{ $rackDetails->count() }}
                                            </small>
                                        </div>

                                        <div>
                                            <i class="fas fa-warehouse fs-4 fs-md-3 opacity-75"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body bg-light rounded-bottom-4">
                                    <div class="row g-3">
                                        @foreach ($rackDetails as $rackDetail)
                                            @php
                                                $stockerData = $stockers->where('detail_rack_id', $rackDetail->id);

                                                $totalQty = $stockerData->sum('qty_ply');
                                            @endphp

                                            <div class="col-12 col-sm-6">
                                                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <div>
                                                                <small class="text-muted">
                                                                    Nama Rak
                                                                </small>

                                                                <h6 class="fw-bold mb-0">
                                                                    {{ $rackDetail->nama_detail_rak }}
                                                                </h6>
                                                            </div>

                                                            <span class="badge bg-sb px-3 py-2">
                                                                {{ $stockerData->count() }}
                                                            </span>
                                                        </div>

                                                        <div class="mt-3">
                                                            <small class="text-muted">
                                                                Total Qty
                                                            </small>
                                                            <h2 class="fw-bold text-sb mb-0">
                                                                {{ number_format($totalQty) }}
                                                            </h2>
                                                        </div>

                                                        <div class="mt-3 border-top pt-2">
                                                            <small class="text-muted">
                                                                {{ $stockerData->count() }} Stocker Tersimpan
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- <div class="accordion mt-5" id="accordionPanelsStayOpenExample">
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
            </div> --}}
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

        async function exportExcel() {
            Swal.fire({
                title: "Exporting",
                html: "Please Wait...",
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            await $.ajax({
                url: "{{ route("export-data-rack") }}",
                type: "post",
                data: {
                    from : $("#tgl-awal").val(),
                    to : $("#tgl-akhir").val(),
                },
                xhrFields: { responseType : 'blob' },
                success: function (res) {
                    Swal.close();

                    iziToast.success({
                        title: 'Success',
                        message: 'Success',
                        position: 'topCenter'
                    });

                    var blob = new Blob([res]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Laporan Data Rack "+$("#tgl-awal").val()+" - "+$("#tgl-akhir").val()+".xlsx";
                    link.click();
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });

            Swal.close();
        }

        async function exportExcelStockOpname() {
            Swal.fire({
                title: "Exporting",
                html: "Please Wait...",
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            await $.ajax({
                url: "{{ route("export-data-rack-stock-opname") }}",
                type: "post",
                data: {
                    from : $("#tgl-awal").val(),
                    to : $("#tgl-akhir").val(),
                },
                xhrFields: { responseType : 'blob' },
                success: function (res) {
                    Swal.close();

                    iziToast.success({
                        title: 'Success',
                        message: 'Success',
                        position: 'topCenter'
                    });

                    var blob = new Blob([res]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Laporan Data Rack "+$("#tgl-awal").val()+" - "+$("#tgl-akhir").val()+".xlsx";
                    link.click();
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });

            Swal.close();
        }
    </script>
@endsection
