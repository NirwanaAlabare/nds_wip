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
            <h5 class="card-title">
                <i class="fa fa-search fa-sm"></i> Detail Cutting Plan Output
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>No. Meja</small></label>
                    <input type="text" class="form-control" value="{{ $cutPlanData->meja->name }}" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>No. WS</small></label>
                    <input type="text" class="form-control" id="ws" name="ws" value="{{ $cutPlanData->ws }}" readonly>
                    <input type="hidden" class="form-control" id="id_ws" name="id_ws">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Style</small></label>
                    <input type="text" class="form-control" id="style" name="style" value="" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Color</small></label>
                    <select class="form-select select2bs4" id="color" name="color">
                        <option value="" selected>Pilih Color</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Panel</small></label>
                    <select class="form-select select2bs4" id="panel" name="panel">
                        <option value="" selected>Pilih Panel</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Order Qty</small></label>
                    <input type="number" class="form-control" id="order_qty" name="order_qty" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Target Shift 1</small></label>
                    <input type="number" class="form-control" onchange="calculateTarget()" onkeyup="calculateTarget()" id="target_1" name="target_1">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Pending Shift 1</small></label>
                    <input type="number" class="form-control" onchange="calculateTarget()" onkeyup="calculateTarget()" id="pending_1" name="pending_1">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Target Shift 2</small></label>
                    <input type="number" class="form-control" onchange="calculateTarget()" onkeyup="calculateTarget()" id="target_2" name="target_2">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Pending Shift 2</small></label>
                    <input type="number" class="form-control" onchange="calculateTarget()" onkeyup="calculateTarget()" id="pending_2" name="pending_2">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Total Target</small></label>
                    <input type="number" class="form-control" id="total_target" name="total_target" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Material Cons.</small></label>
                    <input type="number" class="form-control" onchange="calculateTarget()" onkeyup="calculateTarget()" id="cons" name="cons" step="0.001">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Material Need</small></label>
                    <input type="number" class="form-control" id="need" name="need" step="0.0001">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Material Unit</small></label>
                    <select class="form-select select2bs4" id="unit" name="unit">
                        <option value="meter" selected>METER</option>
                        <option value="yard">YARD</option>
                        <option value="kgm">KGM</option>
                    </select>
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
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Page specific script -->
    <script>
        //Initialize Select2 Elements
        $('.select2').select2();

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });

        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        document.addEventListener("DOMContentLoaded", () => {
            $("#date").val(oneWeeksBeforeFull).trigger("change");
        });
    </script>
@endsection
