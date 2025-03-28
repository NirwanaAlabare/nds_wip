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
            <table class="table table-bordered table-sm" id="datatable-stocker">
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
                            <td class="align-middle">{{ num($qty) }}</td>
                            <td class="align-middle">{{ $loadingLine->tanggal_loading }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-end" colspan="11">TOTAL</th>
                        <th id="total-qty">{{ num($totalQty) }}</th>
                        <th id="latest-update">{{ $latestUpdate }}</th>
                    </tr>
                </tfoot>
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
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        $(document).ready(() => {
            let stockerDatatable = $("#datatable-stocker").DataTable({
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

                        currentUpdate = data[11];
                        currentUpdate > latestUpdate ? latestUpdate = currentUpdate : latestUpdate = latestUpdate;

                        totalQty += Number(data[10]);

                        currentQty = Number(data[10]);
                    }
                    // else {
                    //     currentQty > Number(data[10]) ? totalQty = totalQty - currentQty + Number(data[10]) : totalQty = totalQty;

                    //     currentQty = Number(data[10]);
                    // }
                });

                document.getElementById('total-qty').innerHTML = Number(totalQty).toLocaleString('ID-id');
                document.getElementById('latest-update').innerHTML = latestUpdate;
            });
        });
    </script>
@endsection
