@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold">
                    <i class="fa fa-circle-info"></i> Loading Line
                </h5>
                <a href="{{ route('loading-line') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-reply"></i> Kembali ke Loading Line
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="mb-1">
                        <label><small>Dari</small></label>
                        <input type="date" class="form-control" id="dateFrom" name="dateFrom" value="{{ $dateFrom }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-1">
                        <label><small>Sampai</small></label>
                        <input type="date" class="form-control" id="dateTo" name="dateTo" value="{{ $dateTo }}" readonly>
                    </div>
                </div>
                <div class="col-md-3"></div>
                <div class="col-md-3"></div>
                <div class="col-md-3">
                    <div class="mb-1">
                        <div class="form-group">
                            <label><small>Line</small></label>
                            <input class="form-control" type="text" id="line" name="line" value="{{ strtoupper(str_replace('_', ' ', $loadingLinePlan->userLine->username)) }}" readonly>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-1">
                        <div class="form-group">
                            <label><small>No. WS</small></label>
                            <input class="form-control" type="hidden" id="ws_id" name="ws_id" value="{{ $loadingLinePlan->act_costing_id }}" readonly>
                            <input class="form-control" type="text" id="ws" name="ws" value="{{ $loadingLinePlan->act_costing_ws }}" readonly>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-1">
                        <div class="form-group">
                            <label><small>Buyer</small></label>
                            <input class="form-control" type="text" id="buyer" name="buyer" value="{{ $loadingLinePlan->buyer }}" readonly>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-1">
                        <div class="form-group">
                            <label><small>Style</small></label>
                            <input class="form-control" type="text" id="style" name="style" value="{{ $loadingLinePlan->style }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-primary btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#filterDetailLoadingModal"><i class="fa fa-filter"></i> Filter</button>
            </div>
            <table class="table table-bordered table" id="datatable-stocker">
                <thead>
                    <tr>
                        <th>No. WS</th>
                        <th>Color</th>
                        <th>No. Cut</th>
                        <th>No. Form</th>
                        <th>Size</th>
                        <th>Group</th>
                        <th>Group</th>
                        <th>Range</th>
                        <th>Range</th>
                        <th>No. Stocker</th>
                        <th>Stock</th>
                        <th>No. Bon</th>
                        <th>Qty</th>
                        <th>Waktu Loading</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $currentForm = null;
                        $currentSize = null;
                        $currentGroup = null;
                        $currentRange = null;
                        $currentQty = null;

                        $latestUpdate = null;

                        $totalQty = 0;
                    @endphp
                    @foreach ($loadingLines as $loadingLine)
                        @php
                            $qty = $loadingLine->qty;

                            if ($currentSize != $loadingLine->size || $currentRange != $loadingLine->range_awal || (str_contains($currentForm, "GR") && $currentForm != $loadingLine->no_form)) {
                                $currentForm = $loadingLine->no_form;
                                $currentSize = $loadingLine->size;
                                $currentGroup = $loadingLine->group_stocker ? $loadingLine->group_stocker : $loadingLine->shade;
                                $currentRange = $loadingLine->range_awal;

                                $currentUpdate = $loadingLine->tanggal_loading;
                                $currentUpdate > $latestUpdate && $latestUpdate = $currentUpdate;

                                $totalQty += $qty;

                                $currentQty = $qty;
                            } else {
                                $currentQty > $qty ? $totalQty = $totalQty - $currentQty + $qty : $totalQty = $totalQty;

                                $currentQty = $qty;
                            }
                        @endphp
                        <tr>
                            <td class="align-middle">{{ $loadingLine->act_costing_ws }}</td>
                            <td class="align-middle">{{ $loadingLine->color }}</td>
                            <td class="align-middle">{{ $loadingLine->no_form." / ".$loadingLine->no_cut }}</td>
                            <td class="align-middle">{{ $loadingLine->no_form }}</td>
                            <td class="align-middle">{{ $loadingLine->size }}</td>
                            <td class="align-middle">{{ $loadingLine->group_stocker }}</td>
                            <td class="align-middle">{{ $loadingLine->shade }}</td>
                            <td class="align-middle">{{ $loadingLine->range_awal }}</td>
                            <td class="align-middle">{{ ($loadingLine->range_awal)." - ".($loadingLine->range_akhir) }}</td>
                            <td class="align-middle">{{ $loadingLine->id_qr_stocker }}</td>
                            <td class="align-middle">{{ $loadingLine->tipe }}</td>
                            <td class="align-middle">{{ $loadingLine->no_bon }}</td>
                            <td class="align-middle">{{ num($qty) }}</td>
                            <td class="align-middle">{{ $loadingLine->tanggal_loading }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-end" colspan="12">TOTAL</th>
                        <th id="total-qty">{{ num($totalQty) }}</th>
                        <th id="latest-update">{{ $latestUpdate }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterDetailLoadingModal" tabindex="-1" aria-labelledby="filterDetailLoadingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="filterDetailLoadingModalLabel"><i class="fa fa-filter"></i> Filter</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Stock</label>
                                <select class="form-select select2bs4filter" name="filter_tipe[]" id="filter_tipe" multiple="multiple">
                                    @foreach ($loadingLines->groupBy("tipe")->keys() as $item)
                                        <option value="{{ $item }}">{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Size</label>
                                <select class="form-select select2bs4filter" name="filter_size[]" id="filter_size" multiple="multiple">
                                    @foreach ($loadingLines->groupBy("size")->keys() as $item)
                                        <option value="{{ $item }}">{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Group</label>
                                <select class="form-select select2bs4filter" name="filter_group[]" id="filter_group" multiple="multiple">
                                    @foreach ($loadingLines->groupBy("shade")->keys() as $item)
                                        <option value="{{ $item }}">{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">No. Form</label>
                                <select class="form-select select2bs4filter" name="filter_no_form[]" id="filter_no_form" multiple="multiple">
                                    @foreach ($loadingLines->groupBy("no_form")->keys() as $item)
                                        <option value="{{ $item }}">{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">No. Cut</label>
                                <select class="form-select select2bs4filter" name="filter_no_cut[]" id="filter_no_cut" multiple="multiple">
                                    @foreach ($loadingLines->groupBy("no_cut")->keys() as $item)
                                        <option value="{{ $item }}">{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">No. Bon</label>
                                <select class="form-select select2bs4filter" name="filter_no_bon[]" id="filter_no_bon" multiple="multiple">
                                    @foreach ($loadingLines->groupBy("no_bon")->keys() as $item)
                                        <option value="{{ $item }}">{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sb" data-bs-dismiss="modal" id="apply-filter">Simpan <i class="fa fa-check"></i></button>
                </div>
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
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        $('.select2bs4filter').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#filterDetailLoadingModal")
        });

        $(document).ready(() => {
            var stockerDatatable = $("#datatable-stocker").DataTable({
                ordering: false,
                pageLength: 25,
                rowsGroup: [
                    0,
                    1,
                    2,
                    4,
                    6,
                    7,
                    8,
                ],
                columnDefs: [
                    {
                        targets: [3, 5, 7],
                        visible: false
                    },
                ]
            });

            stockerDatatable.on('search.dt', async function () {
                var total = 0;

                var currentForm = "";
                var currentSize = "";
                var currentGroup = "";
                var currentRange = "";
                var currentUpdate = "";
                var currentQty = 0;

                var latestUpdate = "";
                var totalQty = 0;

                await stockerDatatable.rows({"search":"applied" }).every( function () {
                    var data = this.data();

                    if (currentSize != data[4] || currentRange != data[7]) {
                        currentForm = data[3];
                        currentSize = data[4];
                        currentGroup = data[5];
                        currentRange = data[7];

                        currentUpdate = data[13];
                        currentUpdate > latestUpdate ? latestUpdate = currentUpdate : latestUpdate = latestUpdate;

                        currentQty = Number(data[12]);

                        totalQty += Number(currentQty);
                    } else {
                        currentQty > Number(data[12]) ? totalQty = totalQty - currentQty + Number(data[12]) : totalQty = totalQty;

                        currentQty = Number(data[12]);
                    }
                });

                document.getElementById('total-qty').innerHTML = Number(totalQty).toLocaleString('ID-id');
                document.getElementById('latest-update').innerHTML = latestUpdate;
            });

            // Clear any existing custom filters
            $.fn.dataTable.ext.search = [];

            // Custom filter with multiple select
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                const filter_no_form = $('#filter_no_form').val();
                const filter_size = $('#filter_size').val();
                const filter_group = $('#filter_group').val();
                const filter_tipe = $('#filter_tipe').val();
                const filter_no_bon = $('#filter_no_bon').val();

                const no_form = data[3];
                const size = data[4];
                const group = data[6];
                const tipe = data[10];
                const no_bon = data[11];

                const match_no_form = !filter_no_form || filter_no_form.length === 0 || filter_no_form.includes(no_form);
                const match_size = !filter_size || filter_size.length === 0 || filter_size.includes(size);
                const match_group = !filter_group || filter_group.length === 0 || filter_group.includes(group);
                const match_tipe = !filter_tipe || filter_tipe.length === 0 || filter_tipe.includes(tipe);
                const match_no_bon = !filter_no_bon || filter_no_bon.length === 0 || filter_no_bon.includes(no_bon);

                return match_no_form && match_size && match_group && match_tipe && match_no_bon;
            });

            $("#apply-filter").on("click", function() {
                stockerDatatable.draw();
            });
        });
    </script>
@endsection
