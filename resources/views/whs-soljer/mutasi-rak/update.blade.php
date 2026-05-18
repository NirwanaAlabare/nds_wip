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
    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold text-sb"><i class="fa fa-plus fa-sm"></i> Edit Mutasi Rak (FABRIC)</h5>
        <a href="{{ route('mutasi-rak') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali List Mutasi Rak (FABRIC)</a>
    </div>
    <form action="{{ route('update-mutasi-rak', $data->id) }}" method="post" id="store-mutasi-rak" onsubmit="setItemsBeforeSubmit(this, event)">
        @csrf
        @method('PUT')
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold">
                    Mutasi Rak
                </h5>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="row">
                        <div class="col-3 col-md-3">
                            <div class="mb-1">
                                <label class="form-label"><small>No. Mutasi</small></label>
                                <input type="text" class="form-control" id="no_mutasi" name="no_mutasi" value="{{ $data->no_mutasi }}" readonly>
                            </div>
                        </div>
                        <div class="col-3 col-md-3">
                            <div class="mb-1">
                                <label class="form-label"><small>Tgl Mutasi</small></label>
                                <input type="text" class="form-control" id="tgl_mutasi" name="tgl_mutasi" value="{{ $data->tgl_mutasi }}" readonly>
                            </div>
                        </div>

                        <div class="col-3 col-md-3">
                            <div class="mb-1">
                                <label class="form-label">
                                    <small>Lokasi Tujuan</small>
                                </label>

                                <input type="text" class="form-control" id="lokasi_tujuan" name="lokasi_tujuan" list="list_lokasi" autocomplete="off" value="{{ $data->lokasi_tujuan }}">
                                <datalist id="list_lokasi">
                                    @foreach($lokasi as $row)
                                        <option value="{{ $row->lokasi }}">
                                    @endforeach
                                </datalist>
                            </div>
                        </div>

                        <div class="col-3 col-md-3">
                            <div class="mb-1">
                                <label class="form-label"><small>Keterangan</small></label>
                                <input type="text" class="form-control" id="keterangan" name="keterangan" value="{{ $data->keterangan }}">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-2 col-md-2">
                            <div class="mb-1">
                                <label class="form-label"><small>Total Roll</small></label>
                                <input type="text" class="form-control text-end" id="total_roll" name="total_roll" value="" readonly>
                            </div>
                        </div>
                        <div class="col-2 col-md-2">
                            <div class="mb-1">
                                <label class="form-label"><small>Total Qty</small></label>
                                <input type="text" class="form-control text-end" id="total_qty" name="total_qty" value="" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold">
                    Detail Item
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-end mb-2">
                    <button type="button" class="btn btn-danger btn-sm" id="btnDeleteSelected" style="display:none;">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>
                <div class="row align-items-end">
                    <div class="col-md-12 table-responsive">
                        <table class="table table-bordered w-100 table" id="datatable">
                            <thead>
                                <tr>
                                    <th>Barcode</th>
                                    <th>Buyer</th>
                                    <th>Keterangan</th>
                                    <th>Jenis Item</th>
                                    <th>Warna</th>
                                    <th>Lot</th>
                                    <th>No Roll</th>
                                    <th>Qty</th>
                                    <th>Satuan</th>
                                    <th>Lokasi Barcode</th>
                                    <th>Lokasi Tujuan</th>
                                    <th class="text-center">
                                        <input type="checkbox" id="check_all">
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data_detail as $row)
                                    <tr data-id="{{ $row->id }}">
                                        <td>{{ $row->barcode }}</td>
                                        <td>{{ $row->buyer }}</td>
                                        <td>{{ $row->keterangan }}</td>
                                        <td>{{ $row->jenis_item }}</td>
                                        <td>{{ $row->warna }}</td>
                                        <td>{{ $row->lot }}</td>
                                        <td>{{ $row->no_roll }}</td>
                                        <td class="text-end qty">{{ number_format($row->qty, 2) }}</td>
                                        <td>{{ $row->satuan }}</td>
                                        <td>{{ $row->lokasi_asal }}</td>
                                        <td class="lokasi_tujuan">{{ $row->lokasi_tujuan }}</td>
                                        <td class="text-center">
                                            <input type="checkbox" class="row-check">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <input type="hidden" name="items" id="items">
                </div>
                <div class="col-12 col-md-6 offset-md-3 my-2 text-center">
                    <button type="submit" class="btn btn-success w-100 mb-1 fw-bold" id="btnSimpan">
                        <i class="fa fa-save"></i> SIMPAN
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Page specific script -->
    <script>
        let table_detail_item;

        $(document).ready(function () {

            table_detail_item = $('#datatable').DataTable({
                processing: true,
                serverSide: false,
                columnDefs: [
                    {
                        targets: -1,
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            updateTotalQty();

            $('#lokasi_tujuan').on('input', function () {

                let lokasi = $(this).val();

                $('#datatable tbody tr').each(function () {
                    $(this).find('.lokasi_tujuan').text(lokasi);
                });

            });

        });

        // $(document).on('input', '.qty_out', function () {
        //     let input = $(this);
        //     let val = parseFloat(input.val()) || 0;

        //     let row = input.closest('tr');
        //     let qty_act = parseFloat(row.find('td:eq(8)').text().replace(/,/g, '')) || 0;

        //     if (val > qty_act) {
        //         Swal.fire('Warning', 'Qty Out tidak boleh lebih dari Qty!', 'warning');
        //         input.val("0"); 
        //     }

        //     updateTotalQty();
        // });

        // $(document).on('blur', '.qty_out', function () {
        //     let val = parseFloat($(this).val());

        //     if (!isNaN(val)) {
        //         $(this).val(val.toFixed(2));
        //     } else {
        //         $(this).val('0.00');
        //     }
        // });

        function setItemsBeforeSubmit(form, e) {
            e.preventDefault();

            let data = [];
            let table = $('#datatable').DataTable();

            table.rows().every(function () {

                let row = $(this.node());

                data.push({
                    id: row.attr('data-id'),
                    lokasi_tujuan: row.find('.lokasi_tujuan').text().trim(),
                });

            });

            if (data.length === 0) {
                Swal.fire('Warning', 'Data masih kosong!', 'warning');
                return;
            }

            // let invalid = data.some(item => !item.qty_out || item.qty_out <= 0);

            // if (invalid) {
            //     Swal.fire('Warning', 'Qty Out tidak boleh kosong atau 0!', 'warning');
            //     return;
            // }

            $('#items').val(JSON.stringify(data));

            submitForm(form, e);
        }

        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field')?.focus();
        });

        // Select2 init
        $('.select2').select2();

        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });

        function updateTotalQty() {
            let totalQty = 0;
            let totalRoll = 0;

            table_detail_item.rows().every(function () {

                let row = $(this.node());
                let qty = parseFloat(row.find('.qty').text().replace(/,/g, '')) || 0;

                totalQty += qty;
                totalRoll++;
            });

            $('#total_qty').val(totalQty.toFixed(2));
            $('#total_roll').val(totalRoll);
        }

        $('#check_all').on('change', function () {
            $('.row-check').prop('checked', $(this).prop('checked'));
            toggleDeleteButton();
        });

        $('#btnDeleteSelected').on('click', function () {
            let table = $('#datatable').DataTable();
            let checked = $('.row-check:checked');

            if (checked.length === 0) {
                Swal.fire('Warning', 'Tidak ada data yang dipilih!', 'warning');
                return;
            }

            Swal.fire({
                title: 'Yakin?',
                text: 'Data yang dicentang akan dihapus!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!'
            }).then((result) => {
                if (result.isConfirmed) {

                    checked.each(function () {
                        table.row($(this).closest('tr')).remove().draw();
                    });

                    $('#check_all').prop('checked', false);
                    toggleDeleteButton();

                    updateTotalQty();

                    Swal.fire('Success', 'Data berhasil dihapus!', 'success');
                }
            });

        });

        $('#datatable').on('change', '.row-check', function () {
            toggleDeleteButton();
        });

        function toggleDeleteButton() {
            let checked = $('.row-check:checked').length;

            if (checked > 0) {
                $('#btnDeleteSelected').show();
            } else {
                $('#btnDeleteSelected').hide();
            }
        }
    </script>
@endsection
