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
                    <i class="fa fa-circle-info"></i> Detail Loading Line
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
                        <th>Size</th>
                        <th>Group</th>
                        <th>Range</th>
                        <th>No. Stocker</th>
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

                        $latestUpdate = null;

                        $totalQty = 0;
                    @endphp
                    @foreach ($loadingLinePlan->loadingLines as $loadingLine)
                        @php
                            if ($currentForm != $loadingLine->stocker->formCut->no_form || $currentSize != $loadingLine->stocker->size || $currentGroup != $loadingLine->stocker->shade || $currentRange != ($loadingLine->stocker->range_awal)." - ".($loadingLine->stocker->range_akhir)) {
                                $currentForm = $loadingLine->stocker->formCut->no_form;
                                $currentSize = $loadingLine->stocker->size;
                                $currentGroup = $loadingLine->stocker->shade;
                                $currentRange = ($loadingLine->stocker->range_awal)." - ".($loadingLine->stocker->range_akhir);

                                $currentUpdate = $loadingLine->updated_at ? $loadingLine->updated_at : $loadingLine->tanggal_loading;
                                $currentUpdate > $latestUpdate && $latestUpdate = $currentUpdate;

                                $totalQty += $loadingLine->qty;
                            }
                        @endphp
                        <tr>
                            <td class="align-middle">{{ $loadingLine->stocker->act_costing_ws }}</td>
                            <td class="align-middle">{{ $loadingLine->stocker->color }}</td>
                            <td class="align-middle">{{ $loadingLine->stocker->formCut->no_form." / ".$loadingLine->stocker->formCut->no_cut }}</td>
                            <td class="align-middle">{{ $loadingLine->stocker->size }}</td>
                            <td class="align-middle">{{ $loadingLine->stocker->shade }}</td>
                            <td class="align-middle">{{ ($loadingLine->stocker->range_awal)." - ".($loadingLine->stocker->range_akhir)." (".($loadingLine->qty - $loadingLine->stocker->qty_ply).")" }}</td>
                            <td class="align-middle">{{ $loadingLine->stocker->id_qr_stocker }}</td>
                            <td class="align-middle">{{ num($loadingLine->qty) }}</td>
                            <td class="align-middle">{{ $loadingLine->updated_at ? $loadingLine->updated_at : $loadingLine->tanggal_loading }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-end" colspan="7">TOTAL</th>
                        <th>{{ num($totalQty) }}</th>
                        <th>{{ $latestUpdate }}</th>
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
                    3,
                    4,
                    5,
                    7
                ],
            });
        });
    </script>
@endsection
