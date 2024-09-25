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
                        <h5 class="mb-0">Data Produksi</h5>
                    </div>
                    <div>
                        <button class="btn btn-dark position-relative" id="transfer-data-produksi">
                            <i class="fa fa-download"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <h6 class="mb-3 text-muted float-end">Terakhir input : {{ $lastUpdate->last_update }}</h6>
                <div class="table-responsive">
                    <table class="table table-sm w-100" id="produksi-table">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Tanggal</th>
                                <th>Order Info</th>
                                <th>Quantity Info</th>
                                <th>Production Info</th>
                                <th>Data Info</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateProduksiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form action="{{ route('dataProduksi.updateData') }}" method="POST" onsubmit="submitForm(this, event)">
                    @method('PUT')
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-6">Ubah Data Produksi</h1>
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
                                <label>Tanggal Order</label>
                                <input type="text" class="form-control" name="edit_tanggal_order" id="edit_tanggal_order" readonly>
                                <small class="text-danger d-none" id="edit_tanggal_order_error"></small>
                            </div>
                            <div class="col mb-3">
                                <label>Tanggal Delivery</label>
                                <input type="text" class="form-control" name="edit_tanggal_delivery" id="edit_tanggal_delivery" readonly>
                                <small class="text-danger d-none" id="edit_tanggal_delivery_error"></small>
                            </div>
                        </div>
                        <div class="row gap-1">
                            <div class="col mb-3">
                                <label>Buyer</label>
                                <input type="text" class="form-control" name="edit_nama_buyer" id="edit_nama_buyer" readonly>
                                <small class="text-danger d-none" id="edit_nama_buyer_error"></small>
                            </div>
                            <div class="col mb-3">
                                <label>No. WS</label>
                                <input type="text" class="form-control" name="edit_no_ws" id="edit_no_ws" readonly>
                                <small class="text-danger d-none" id="edit_no_ws_error"></small>
                            </div>
                        </div>
                        <div class="row gap-1">
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
                            <div class="col mb-3">
                                <label>Satuan</label>
                                <input type="text" class="form-control" name="edit_satuan" id="edit_order_satuan" readonly>
                                <small class="text-danger d-none" id="edit_order_satuan_error"></small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <label>Mata Uang</label>
                                <input type="text" class="form-control" name="edit_kode_mata_uang" id="edit_kode_mata_uang" readonly>
                                <small class="text-danger d-none" id="edit_kode_mata_uang_error"></small>
                            </div>
                            <div class="col mb-3">
                                <label>Price</label>
                                <input type="text" class="form-control" name="edit_order_cfm_price" id="edit_order_cfm_price">
                                <small class="text-danger d-none" id="edit_order_cfm_price_error"></small>
                            </div>
                        </div>
                        <div class="row gap-1">
                            <div class="col mb-3">
                                <label>Order Qty</label>
                                <input type="text" class="form-control" name="edit_order_qty" id="edit_order_qty" readonly>
                                <small class="text-danger d-none" id="edit_order_qty_error"></small>
                            </div>
                            <div class="col mb-3">
                                <label>Cutting Qty</label>
                                <input type="text" class="form-control" name="edit_order_qty_cutting" id="edit_order_qty_cutting">
                                <small class="text-danger d-none" id="edit_order_qty_cutting_error"></small>
                            </div>
                            <div class="col mb-3">
                                <label>Output Qty</label>
                                <input type="text" class="form-control" name="edit_order_qty_output" id="edit_order_qty_output" readonly>
                                <small class="text-danger d-none" id="edit_order_qty_output_error"></small>
                            </div>
                            <div class="col mb-3">
                                <label>Balance Qty</label>
                                <input type="text" class="form-control" name="edit_order_qty_balance" id="edit_order_qty_balance" readonly>
                                <small class="text-danger d-none" id="edit_order_qty_balance_error"></small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
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
            $('#produksi-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{!! route('dataProduksi') !!}',
                columns: [
                    {data: 'action',name: 'action', orderable: false, searchable: false},
                    {data: 'tanggal',name: 'tanggal'},
                    {data: 'order_info',name: 'order_info'},
                    {data: 'quantity_info',name: 'quantity_info'},
                    {data: 'production_info',name: 'production_info'},
                    {data: 'data_info',name: 'data_info'},
                ],
            });

            $('#edit_order_qty_cutting').on('keyup', () => {
                let orderQty = $('#edit_order_qty').val();
                let orderQtyCutting = $('#edit_order_qty_cutting').val();
                let orderQtyOutput = $('#edit_order_qty_output').val();
                let orderQtyBalance = $('#edit_order_qty_balance').val();

                if ($('#edit_order_qty_cutting').val() > 0) {
                    let orderQtyBalance = orderQtyCutting - orderQtyOutput;
                    $('#edit_order_qty_balance').val(orderQtyBalance);
                } else {
                    let orderQtyBalance = orderQty - orderQtyOutput;
                    $('#edit_order_qty_balance').val(orderQtyBalance);
                }
            });

            $('#transfer-data-produksi').on('click', () => {
                const date = new Date();

                let day = date.getDate();
                let month = ('0'+(date.getMonth() + 1)).slice(-2);
                let year = date.getFullYear();

                Swal.fire({
                    icon: 'info',
                    title: 'Salin data dari SIGNAL BIT?',
                    showCancelButton: true,
                    showConfirmButton: true,
                    confirmButtonText: 'Salin Data',
                    cancelButtonText: 'Batal',
                }).then((result) => {
                    if (result.isConfirmed) {
                        iziToast.info({
                            title: 'Info',
                            message: 'Transfer data sedang diproses, mohon tunggu...',
                            position: 'topCenter'
                        });

                        $('#transfer-data-produksi').prop("disabled", true);
                        $('#transfer-data-produksi').append( "<div class='loading'></div>" );

                        Swal.fire({
                            title: 'Please Wait...',
                            html: 'Calculating Data...',
                            didOpen: () => {
                                Swal.showLoading()
                            },
                            allowOutsideClick: false,
                        });

                        $.ajax({
                            url: '{!! route('dataProduksi.transferData') !!}',
                            type: 'post',
                            data: year+'-'+month+'-'+day,
                            processData: false,
                            contentType: false,
                            success: function(res) {
                                console.log(res);

                                $('#transfer-data-produksi').prop("disabled", false);
                                $( "div" ).remove( ".loading" );

                                swal.close();

                                iziToast.success({
                                    title: 'Success',
                                    message: 'Transfer berhasil',
                                    position: 'topCenter'
                                });

                                if (res.table != '') {
                                    $('#'+res.table).DataTable().ajax.reload();
                                }

                                if (res.additional) {
                                    let message = "";

                                    if (res.additional['success'].length > 0) {
                                        res.additional['success'].forEach(element => {
                                            message += element+" - Berhasil <br>";
                                        });
                                    }

                                    if (res.additional['fail'].length > 0) {
                                        res.additional['fail'].forEach(element => {
                                            message += element+" - Gagal <br>";
                                        });
                                    }

                                    if (res.additional['exist'].length > 0) {
                                        res.additional['exist'].forEach(element => {
                                            message += element+" - Sudah Ada <br>";
                                        });
                                    }

                                    if (res.additional['success'].length+res.additional['fail'].length+res.additional['exist'].length > 1) {
                                        Swal.fire({
                                            icon: 'info',
                                            title: 'Hasil Transfer',
                                            html: message,
                                            showCancelButton: false,
                                            showConfirmButton: true,
                                            confirmButtonText: 'Oke',
                                        }).then(function() {
                                            location.reload();
                                        });
                                    }
                                }
                            }, error: function (jqXHR) {
                                let res = jqXHR.responseJSON;
                                let message = '';

                                for (let key in res.errors) {
                                    message = res.errors[key];
                                }

                                $('#transfer-data-produksi').prop("disabled", false);
                                $( "div" ).remove( ".loading" );

                                iziToast.error({
                                    title: 'Error',
                                    message: 'Terjadi kesalahan. '+message,
                                    position: 'topCenter'
                                });

                                swal.close();

                                if (res.table != '') {
                                    $('#'+res.table).DataTable().ajax.reload();
                                }
                            }
                        });
                    }
                })
            });
        });
    </script>
@endsection
