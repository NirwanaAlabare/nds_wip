@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> List Data Sales Order</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <a href="{{ route('create-so') }}" class="btn btn-outline-primary">
                <i class="fas fa-plus"></i> Create SO
            </a>
        </div>

        <div class="row align-items-end mb-4">
            <div class="col-md-2">
                <label class="small fw-bold">Tgl Awal</label>
                <input type="date" id="date_from" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Tgl Akhir</label>
                <input type="date" id="date_to" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-sm" onclick="refreshTable()">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover w-100" id="table-so">
                <thead>
                    <tr class="text-center">
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>No SO</th>
                        <th>No PO</th>
                        <th>WS</th>
                        <th>Buyer</th>
                        <th>Product</th>
                        <th>Item Name</th>
                        <th>Qty</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-detail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-sb text-white">
                <h5 class="modal-title"><i class="fas fa-info-circle"></i> Detail Sales Order</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col-md-4">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="30%">No SO</th>
                                <td>: <span id="so_no">-</span></td>
                            </tr>
                            <tr>
                                <th>WS</th>
                                <td>: <span id="kpno">-</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th>Style</th>
                                <td>: <span id="style">-</span></td>
                            </tr>
                            <tr>
                                <th width="30%">Buyer</th>
                                <td>: <span id="buyer">-</span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm w-100" id="table-detail-so">
                        <thead class="bg-light text-center">
                            <tr>
                                <th>Color</th>
                                <th width="20%">Size</th>
                                <th width="20%">Qty</th>
                            </tr>
                        </thead>
                        <tbody id="dtl_table_body">
                            </tbody>
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

    <script>

        $(".close").click(function(){
            $("#modal-detail").modal("hide");
        });

        let table;
        $(document).ready(function() {
            table = $('#table-so').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('master-marketing-so') }}",
                    data: function (d) {
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'd_insert', name: 'so.d_insert', className: 'text-center' },
                    { data: 'so_no', name: 'so.so_no' },
                    { data: 'no_po', name: 'so.no_po' },
                    { data: 'kpno', name: 'act.kpno' },
                    { data: 'buyer', name: 'ms.Supplier' },
                    { data: 'product_group', name: 'mp.product_group' },
                    { data: 'product_item', name: 'mp.product_item' },
                    { data: 'qty', name: 'so.qty', className: 'text-right' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ]
            });
        });

        function refreshTable() {
            table.ajax.reload();
        }

        let tableDetail;

        function showDetail(id) {
            $('#modal-detail').modal('show');

            if ($.fn.DataTable.isDataTable('#table-detail-so')) {
                $('#table-detail-so').DataTable().destroy();
            }

            $('#table-detail-so tbody').html('<tr><td colspan="3" class="text-center">Loading...</td></tr>');

            let url = "{{ route('get-detail-so', ':id') }}";
            url = url.replace(':id', id);

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json'
            })
            .done((res) => {
                const { so_no, kpno, buyer, styleno } = res.header || {};
                $('#so_no').text(so_no || '-');
                $('#kpno').text(kpno || '-');
                $('#buyer').text(buyer || '-');
                $('#style').text(styleno || '-');

                let rows = '';
                res.details.forEach(item => {
                    rows += `<tr>
                        <td>${item.color || '-'}</td>
                        <td class="text-center">${item.size || '-'}</td>
                        <td class="text-right">${Number(item.qty).toLocaleString('id-ID')}</td>
                    </tr>`;
                });

                $('#table-detail-so tbody').html(rows);

                tableDetail = $('#table-detail-so').DataTable({
                    "paging": true,
                    "ordering": true,
                    "info": true,
                    "searching": true,
                    "lengthMenu": [5, 10, 25],
                    "pageLength": 10
                });
            })
            .fail((xhr) => {
                console.error(xhr.responseText);
                alert('Gagal memuat data detail.');
            });
        }
    </script>
@endsection
