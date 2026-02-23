@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> List Data</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <a href="{{ route('create-bom') }}" class="btn btn-outline-primary">
                <i class="fas fa-plus"></i>
                Create BOM
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover w-100" id="table-bom">
                <thead>
                    <tr class="text-center">
                        <th>No</th>
                        <th>Buyer</th>
                        <th>Style</th>
                        <th>Market</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $key => $item)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $item->buyer }}</td>
                        <td>{{ $item->style }}</td>
                        <td>{{ $item->market }}</td>
                        <td>
                            <div class="d-flex justify-content-center align-items-center">
                                <button class="btn btn-sm btn-info mr-1 py-1 px-2" style="font-size: 12px;" onclick="viewDetail({{ $item->id }})">
                                    <i class="fas fa-eye"></i> Detail
                                </button>
                                <a href="{{ route('edit-bom', $item->id) }}" class="btn btn-sm btn-success py-1 px-2" style="font-size: 12px;">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-sb text-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-info-circle"></i> Detail BOM Marketing</h5>
                <button type="button" class="close text-light" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered w-100" id="table-detail">
                        <thead class="bg-light">
                            <tr class="text-center">
                                <th width="5%">No</th>
                                <th width="20%">Content</th>
                                <th>Item Description</th>
                                <th width="15%">Color</th>
                                <th width="15%">Size</th>
                                <th width="10%">Cons</th>
                                <th width="10%">Unit</th>
                            </tr>
                            <tr class="bg-light filter-row">
                                <th></th> <th><input type="text" class="form-control form-control-sm column-search" data-column="1"></th>
                                <th><input type="text" class="form-control form-control-sm column-search" data-column="2"></th>
                                <th><input type="text" class="form-control form-control-sm column-search" data-column="3"></th>
                                <th><input type="text" class="form-control form-control-sm column-search" data-column="4"></th>
                                <th></th> <th><input type="text" class="form-control form-control-sm column-search" data-column="6"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-dismiss="modal"><i class="fas fa-times-circle"></i> Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $(".close").click(function(){
                $("#modalDetail").modal("hide");
            });

            $('#modalDetail').on('shown.bs.modal', function () {
                if ($.fn.DataTable.isDataTable('#table-detail')) {
                    $('#table-detail').DataTable().columns.adjust().responsive.recalc();
                }
            });

            $('#table-bom').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "columnDefs": [
                    {
                        "targets": [0],
                        "width": "5%",
                        "className": "text-center align-middle"
                    },
                    {
                        "targets": [4],
                        "width": "15%",
                        "className": "text-center align-middle",
                        "orderable": false
                    },
                    {
                        "targets": "_all",
                        "className": "align-middle"
                    }
                ]
            });
        });

        let tableDetail = null;

        function viewDetail(id) {
            $('#modalDetail').modal('show');

            let url = "{{ route('show-detail-bom', ':id') }}";
            url = url.replace(':id', id);

            if ($.fn.DataTable.isDataTable('#table-detail')) {
                $('.column-search').val('');
                tableDetail.search('').columns().search('');

                tableDetail.ajax.url(url).load();
            } else {
                tableDetail = $('#table-detail').DataTable({
                    processing: true,
                    serverSide: true,
                    orderCellsTop: true,
                    ajax: url,
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                        {
                            data: 'content_name',
                            name: 'content_name',
                            className: 'align-middle',
                            render: function(data) {
                                return `${data ? data : '-'}`;
                            }
                        },
                        { data: 'item_name', name: 'i.itemdesc', className: 'align-middle' },
                        { data: 'color_name', name: 'color_name', className: 'text-center align-middle' },
                        { data: 'size_name', name: 'size_name', className: 'text-center align-middle' },
                        {
                            data: 'qty',
                            name: 'qty',
                            className: 'text-center align-middle',
                            searchable: false,
                            render: function(data) {
                                return parseInt(data);
                            }
                        },
                        { data: 'unit_name', name: 'unit_name', className: 'text-center align-middle' }
                    ],
                    language: { emptyTable: "Data tidak ditemukan" },
                    autoWidth: false,
                    responsive: true,
                    lengthMenu: [5, 10, 25, 50],
                    pageLength: 10
                });

                $('#table-detail .column-search').on('keyup change clear', function() {
                    let colIdx = $(this).data('column');
                    if (tableDetail.column(colIdx).search() !== this.value) {
                        tableDetail.column(colIdx).search(this.value).draw();
                    }
                });
            }
        }
    </script>
@endsection
