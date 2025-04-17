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
            <h5 class="card-title"><i class="fa-solid fa-sliders"></i> Modify Loading Line</h5>
        </div>
        <div class="card-body">
            <div class="row justify-content-between align-items-start g-3 mb-3">
                <div class="col-md-12">
                    <label class="form-label">Stocker</label>
                    <textarea class="form-control" name="text" id="stocker_ids" rows="5"></textarea>
                </div>
                <div class="col-md-6">
                    <div class="form-text">Contoh : <br>&nbsp;&nbsp;&nbsp;<b> STK-12332</b><br>&nbsp;&nbsp;&nbsp;<b> STK-12433</b><br>&nbsp;&nbsp;&nbsp;<b> STK-12651</b></div>
                </div>
                <div class="col-md-6">
                    <button class="btn btn-block btn-primary mt-1" onclick="currentStockerTableReload()"><i class="fa fa-search"></i> Check</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered w-100'" id="current-stocker-table">
                    <thead>
                        <tr>
                            <th>Stocker IDs</th>
                            <th>Tanggal Loading</th>
                            <th>Line</th>
                            <th>No. WS</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Lokasi</th>
                            <th>Trolley</th>
                            <th>Qty</th>
                            <th>DC Qty</th>
                            <th>Secondary In Qty</th>
                            <th>Secondary Inhouse Qty</th>
                            <th>Loading Qty</th>
                            <th>Range Awal Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <label class="form-label">Total</label>
                <input type="number" class="form-control" readonly>
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
    <script>
        let currentStockerTable = $("#current-stocker-table").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("modify-loading-line") }}',
                data: function (d) {
                    d.stocker_ids = $('#stocker_ids').val();
                },
            },
            columns: [
                {
                    data:'stocker_ids'
                },
                {
                    data:'tanggal_loading'
                },
                {
                    data:'nama_line'
                },
                {
                    data:'act_costing_ws'
                },
                {
                    data:'color'
                },
                {
                    data:'size'
                },
                {
                    data:'lokasi'
                },
                {
                    data:'trolley'
                },
                {
                    data:'qty'
                },
                {
                    data:'dc_qty'
                },
                {
                    data:'secondary_in_qty'
                },
                {
                    data:'secondary_inhouse_qty'
                },
                {
                    data:'loading_qty'
                },
                {
                    data:'range_awal_akhir'
                }
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap"
                },
            ],
        });

        function currentStockerTableReload() {
            $("#current-stocker-table").DataTable().ajax.reload(() => {
                $("#total_update").val(currentStockerTable.page.info().recordsTotal);
            });
        }
    </script>
@endsection
