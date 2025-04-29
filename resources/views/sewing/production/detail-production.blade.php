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
    <div class="container-fluid mt-3 pt-3">
        <div class="card">
            <div class="card-header bg-sb text-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Data Detail Produksi</h5>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table w-100" id="detail-produksi-table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Order Info</th>
                            <th>Production Info</th>
                            <th>Data Info</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Update Detail Production --}}
    <div class="modal fade" id="updateDataDetailProduksiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form action="{{ route('dataDetailProduksi.updateData') }}" method="POST" onsubmit="submitForm(this, event)">
                    @method('PUT')
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-6">Ubah Data Detail Produksi</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 d-none">
                            <label>ID</label>
                            <input type="hidden" class="form-control" name="edit_id" id="edit_id" readonly>
                            <small class="text-danger d-none" id="edit_id_error"></small>
                        </div>
                        <div class="row gap-1">
                            <div class="col mb-3">
                                <label>Line</label>
                                <input type="text" class="form-control" name="edit_sewing_line" id="edit_sewing_line" readonly>
                                <small class="text-danger d-none" id="edit_sewing_line_error"></small>
                            </div>
                            <div class="col mb-3">
                                <label>No. WS</label>
                                <input type="text" class="form-control" name="edit_no_ws" id="edit_no_ws" readonly>
                                <small class="text-danger d-none" id="edit_no_ws_error"></small>
                            </div>
                        </div>
                        <div class="row gap-1">
                            <div class="col mb-3">
                                <label>Style</label>
                                <input type="text" class="form-control" name="edit_no_style" id="edit_no_style" readonly>
                                <small class="text-danger d-none" id="edit_no_style_error"></small>
                            </div>
                            <div class="col mb-3">
                                <label>Product Group</label>
                                <input type="text" class="form-control" name="edit_product_group" id="edit_product_group" readonly>
                                <small class="text-danger d-none" id="edit_product_group_error"></small>
                            </div>
                            <div class="col mb-3">
                                <label>Product Item</label>
                                <input type="text" class="form-control" name="edit_product_item" id="edit_product_item" readonly>
                                <small class="text-danger d-none" id="edit_product_item_error"></small>
                            </div>
                        </div>
                        <div class="row gap-1">
                            <div class="col mb-3">
                                <label>Qty Alokasi</label>
                                <input type="text" class="form-control" name="edit_order_qty_loading" id="edit_order_qty_loading">
                                <small class="text-danger d-none" id="edit_order_qty_loading_error"></small>
                            </div>
                            <div class="col mb-3">
                                <label>Qty Output</label>
                                <input type="text" class="form-control" name="edit_order_qty_output" id="edit_order_qty_output" readonly>
                                <small class="text-danger d-none" id="edit_order_qty_output_error"></small>
                            </div>
                            <div class="col mb-3">
                                <label>Qty Balance</label>
                                <input type="text" class="form-control" name="edit_order_qty_balance" id="edit_order_qty_balance" readonly>
                                <small class="text-danger d-none" id="edit_order_qty_balance_error"></small>
                            </div>
                        </div>
                        <div class="row gap-1">
                            <div class="col mb-3">
                                <label>Mata Uang</label>
                                <input type="text" class="form-control" name="edit_kode_mata_uang" id="edit_kode_mata_uang" readonly>
                                <small class="text-danger d-none" id="edit_kode_mata_uang_error"></small>
                            </div>
                            <div class="col mb-3">
                                <label>Price</label>
                                <input type="text" class="form-control" name="edit_order_cfm_price" id="edit_order_cfm_price" readonly>
                                <small class="text-danger d-none" id="edit_order_cfm_price_error"></small>
                            </div>
                            <div class="col mb-3">
                                <label>Earning</label>
                                <input type="text" class="form-control" name="edit_earning" id="edit_earning" readonly>
                                <small class="text-danger d-none" id="edit_earning_error"></small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-dark">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2').select2({
            theme: 'bootstrap4'
        })
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            $('#detail-produksi-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{!! route('dataDetailProduksi') !!}',
                columns: [
                    {data: 'action',name: 'action', orderable: false, searchable: false},
                    {data: 'order_info',name: 'order_info'},
                    {data: 'production_info',name: 'production_info'},
                    {data: 'data_info',name: 'data_info'},
                ],
            });

            $('#edit_order_qty_loading').on('keyup', () => {
                let orderQty = $('#edit_order_qty').val();
                let orderQtyLoading = $('#edit_order_qty_loading').val();
                let orderQtyOutput = $('#edit_order_qty_output').val();
                let orderQtyBalance = $('#edit_order_qty_balance').val();

                if ($('#edit_order_qty_loading').val() > 0) {
                    let orderQtyBalance = orderQtyLoading - orderQtyOutput;
                    $('#edit_order_qty_balance').val(orderQtyBalance);
                } else {
                    let orderQtyBalance = orderQty - orderQtyOutput;
                    $('#edit_order_qty_balance').val(orderQtyBalance);
                }
            });
        });
    </script>
@endsection
