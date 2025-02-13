@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                <i class="fas fa-dolly-flatbed"></i> Trolley
            </h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a href="{{ route('allocate-trolley') }}" class="btn btn-success btn-sm">
                    <i class="fa fa-plus"></i> Alokasi
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered w-100" id="datatable">
                    <thead>
                        <tr>
                            <th class="text-center">Action</th>
                            <th>Trolley</th>
                            <th>WS Number</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Qty</th>
                            <th class="text-center">Send</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($trolleyStocks && $trolleyStocks->count() < 1)
                            <tr>
                                <td colspan="7">Data tidak ditemukan</td>
                            </tr>
                        @else
                            @foreach ($trolleyStocks as $trolleyStock)
                                <tr>
                                    <td class="align-middle">
                                        <div class='d-flex gap-1 justify-content-center'>
                                            <a class='btn btn-success btn-sm' href='{{ route('allocate-this-trolley', ['id' => $trolleyStock->id]) }}'>
                                                <i class='fa fa-plus'></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="align-middle">{{ $trolleyStock->nama_trolley }}</td>
                                    <td>{{ $trolleyStock->act_costing_ws }}</td>
                                    <td>{{ $trolleyStock->style }}</td>
                                    <td>{{ $trolleyStock->color }}</td>
                                    <td>{{ num($trolleyStock->qty) }}</td>
                                    <td class="align-middle">
                                        <div class='d-flex gap-1 justify-content-center'>
                                            <a href='{{ route('send-trolley-stock', ['id' => $trolleyStock->id]) }}/' class='btn btn-primary btn-sm'>
                                                <i class='fa fa-share'></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
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

    <script>
        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            if (i != 0 && i != 6) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" />');

                $('input', this).on('keyup change', function() {
                    if (datatable.column(i).search() !== this.value) {
                        datatable
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        let datatable = $("#datatable").DataTable({
            ordering: false,
            rowsGroup: [
                0,
                1,
                6
            ],
        });

        function datatableReload() {
            datatable.ajax.reload();
        }
    </script>
@endsection
