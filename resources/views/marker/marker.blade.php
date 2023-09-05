@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="/assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="/assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
@endsection

@section('content')
    <div class="card card-sb card-outline">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">Data Marker</h5>
        </div>
        <div class="card-body">
            <a href="/marker/create" class="btn btn-sb btn-sm mb-3">
                <i class="fas fa-plus"></i>
                Baru
            </a>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Awal</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Akhir</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir">
                </div>
                <div class="mb-3">
                    <button class="btn btn-sb btn-sm">Tampilkan</button>
                </div>
            </div>
            <div>
                <table id="datatable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>WS</th>
                            <th>Color</th>
                            <th>Panel</th>
                            <th>Panjang Marker</th>
                            <th>Comma Marker</th>
                            <th>Lebar Marker</th>
                            <th>Gelar QTYs</th>
                            <th>PO</th>
                            <th>Urutan</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables  & Plugins -->
    <script src="/assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="/assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="/assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <script>
        let datatable = $("#datatable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '/marker',
                dataType: 'json',
                dataSrc: 'data',
                data: function (d) {
                    d.tgl_Awal = $('#tgl-awal').val();
                    d.tgl_Akhir = $('#tgl-akhir').val();
                },
            },
            columns: [
                {
                    data: 'no_ws'
                },
                {
                    data: 'color'
                },
                {
                    data: 'panel'
                },
                {
                    data: 'panjang_marker'
                },
                {
                    data: 'comma_marker'
                },
                {
                    data: 'lebar_marker'
                },
                {
                    data: 'gelar_qty'
                }
                {
                    data: 'po'
                }
                {
                    data: 'urutan'
                }
            ],
        });
    </script>
@endsection
