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
        <h5 class="fw-bold text-sb"><i class="fa fa-plus fa-sm"></i> Edit Pengeluaran Gudang Inputan (FABRIC)</h5>
        <a href="{{ route('pengeluaran-gudang-inputan') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali List Pengeluaran Gudang Inputan (FABRIC)</a>
    </div>
    <form action="{{ route('update-pengeluaran-gudang-inputan', $data->id) }}" method="post" id="store-pengeluaran-gudang-inputan" onsubmit="setItemsBeforeSubmit(this, event)">
        @csrf
        @method('PUT')
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold">
                    Header Penginputan
                </h5>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="row">
                        <div class="col-3 col-md-3">
                            <div class="mb-1">
                                <label class="form-label"><small>No. BPB</small></label>
                                <input type="text" class="form-control" id="no_bpb" name="no_bpb" value="{{ $data->no_bpb }}" readonly>
                            </div>
                        </div>
                        <div class="col-3 col-md-3">
                            <div class="mb-1">
                                <label class="form-label"><small>Tgl BPB</small></label>
                                <input type="text" class="form-control" id="tgl_bpb" name="tgl_bpb" value="{{ $data->tgl_bpb }}" readonly>
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
                <div class="row align-items-end">
                    <div class="col-md-12 table-responsive">
                        <table class="table table-bordered w-100 table" id="datatable">
                            <thead>
                                <tr>
                                    <th>Barcode</th>
                                    <th>Lokasi</th>
                                    <th>Buyer</th>
                                    <th>Keterangan</th>
                                    <th>Jenis Item</th>
                                    <th>Warna</th>
                                    <th>Lot</th>
                                    <th>No Roll</th>
                                    <th>Qty</th>
                                    <th>Satuan</th>
                                    <th>Qty Out</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalQtyAct = 0;
                                @endphp
                                @foreach($data_detail as $row)
                                    @php
                                        $totalQtyAct += $row->qty_act;
                                    @endphp
                                    <tr data-id="{{ $row->id }}">
                                        <td>{{ $row->barcode }}</td>
                                        <td>{{ $row->lokasi }}</td>
                                        <td>{{ $row->buyer }}</td>
                                        <td>{{ $row->keterangan }}</td>
                                        <td>{{ $row->jenis_item }}</td>
                                        <td>{{ $row->warna }}</td>
                                        <td>{{ $row->lot }}</td>
                                        <td>{{ $row->no_roll }}</td>
                                        <td class="text-end">{{ number_format($row->qty_act, 2) }}</td>
                                        <td>{{ $row->satuan }}</td>
                                        <td>
                                            <input type="number" step="any" class="form-control form-control-sm text-end qty_out" value="{{ number_format($row->qty_out, 2) }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="8" class="text-center">TOTAL</th>
                                    <th id="total_qty_act" class="text-end">{{ number_format($totalQtyAct, 2) }}</th>
                                    <th></th>
                                    <th id="total_qty_out" class="text-end">0</th>
                                </tr>
                            </tfoot>
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
        $(document).ready(function () {

            $('#datatable').DataTable({
                processing: true,
                serverSide: false
            });

            updateTotalQty();

        });

        $(document).on('input', '.qty_out', function () {
            let input = $(this);
            let val = parseFloat(input.val()) || 0;

            let row = input.closest('tr');
            let qty_act = parseFloat(row.find('td:eq(8)').text().replace(/,/g, '')) || 0;

            if (val > qty_act) {
                Swal.fire('Warning', 'Qty Out tidak boleh lebih dari Qty!', 'warning');
                input.val("0"); 
            }

            updateTotalQty();
        });

        $(document).on('blur', '.qty_out', function () {
            let val = parseFloat($(this).val());

            if (!isNaN(val)) {
                $(this).val(val.toFixed(2));
            } else {
                $(this).val('0.00');
            }
        });

        function setItemsBeforeSubmit(form, e) {
            e.preventDefault();

            let data = [];

            $('#datatable tbody tr').each(function () {

                let row = $(this);

                data.push({
                    id: row.attr('data-id'),
                    qty_out: row.find('.qty_out').val()
                });

            });

            if (data.length === 0) {
                Swal.fire('Warning', 'Data masih kosong!', 'warning');
                return;
            }

            let invalid = data.some(item => !item.qty_out || item.qty_out <= 0);

            if (invalid) {
                Swal.fire('Warning', 'Qty Out tidak boleh kosong atau 0!', 'warning');
                return;
            }

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
            let total_qty_out = 0;

            $('#datatable tbody .qty_out').each(function () {
                total_qty_out += parseFloat($(this).val() || 0);
            });

            $('#total_qty_out').text(total_qty_out.toFixed(2));
        }
    </script>
@endsection
