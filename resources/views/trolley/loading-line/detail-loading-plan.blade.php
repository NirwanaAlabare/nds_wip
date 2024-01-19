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
                        <th>Color</th>
                        <th>No. Stocker</th>
                        <th>No. Cut</th>
                        <th>Size</th>
                        <th>Qty</th>
                        <th>Waktu Loading</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($loadingLinePlan->loadingLines as $loadingLine)
                        <tr>
                            <td>{{ strtoupper(str_replace("_", " ", $loadingLine->stocker->color)) }}</td>
                            <td>{{ strtoupper(str_replace("_", " ", $loadingLine->stocker->id_qr_stocker)) }}</td>
                            <td>{{ strtoupper(str_replace("_", " ", $loadingLine->stocker->formCut->no_cut)) }}</td>
                            <td>{{ strtoupper(str_replace("_", " ", $loadingLine->stocker->size)) }}</td>
                            <td>{{ strtoupper(str_replace("_", " ", $loadingLine->stocker->qty_ply)) }}</td>
                            <td>{{ strtoupper(str_replace("_", " ", $loadingLine->updated_at ? $loadingLine->updated_at : $loadingLine->tanggal_loading)) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4"></th>
                        <th></th>
                        <th></th>
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
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        $(document).ready(() => {
            let stockerDatatable = $("#datatable-stocker").DataTable({
                footerCallback: function(row, data, start, end, display) {
                    // This datatable api
                    let api = this.api();

                    // Remove the formatting to get integer data for summation
                    let intVal = function(i) {
                        return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
                    };

                    // Total stocker qty
                    let stockerQty = api
                        .cells( null, 4 )
                        .render( 'display' )
                        .reduce(function(a, b) {
                            let result = intVal(a) + intVal(b);
                            return result;
                        }, 0);

                    let latestUpdate = api
                        .cells( null, 5 )
                        .render( 'display' )
                        .reduce(function (a, b) {
                            if (a < b) {
                                return a;
                            } else {
                                return b;
                            }
                        });

                    // Update footer
                    $(api.column(1).footer()).html("Total");
                    $(api.column(4).footer()).html(Number(stockerQty).toLocaleString('id-ID'));
                    $(api.column(5).footer()).html(latestUpdate);
                }
            });
        });
    </script>
@endsection
