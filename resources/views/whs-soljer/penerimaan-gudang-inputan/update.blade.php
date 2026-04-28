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
        <h5 class="fw-bold text-sb"><i class="fa fa-plus fa-sm"></i> Edit Penerimaan Gudang Inputan (FABRIC)</h5>
        <a href="{{ route('penerimaan-gudang-inputan') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali List Penerimaan Gudang Inputan (FABRIC)</a>
    </div>
    <form action="{{ route('update-penerimaan-gudang-inputan', $data->id) }}" method="post" id="store-penerimaan-gudang-inputan" onsubmit="setItemsBeforeSubmit(this, event)">
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
                                    <th>No Roll</th>
                                    <th>Buyer</th>
                                    <th>Jenis Item</th>
                                    <th>Warna</th>
                                    <th>Lot</th>
                                    <th>Qty</th>
                                    <th>Satuan</th>
                                    <th>Keterangan</th>
                                    <th>Lokasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data_detail as $row)
                                <tr data-id="{{ $row->id }}">
                                    <td>{{ $row->no_roll }}</td>
                                    <td>{{ $row->buyer }}</td>
                                    <td>{{ $row->jenis_item }}</td>
                                    <td>{{ $row->warna }}</td>
                                    <td>{{ $row->lot }}</td>
                                    <td>
                                        {{-- <input type="number" class="form-control form-control-sm text-end qty" value="{{ $row->qty }}"> --}}
                                        <input type="number" step="any" class="form-control form-control-sm text-end qty" value="{{ $row->qty }}">
                                    </td>
                                    <td>{{ $row->satuan }}</td>
                                    <td>{{ $row->keterangan }}</td>
                                    <td>{{ $row->lokasi }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-center">TOTAL</th>
                                    <th id="total_qty" class="text-end">0</th>
                                    <th colspan="3"></th>
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

        $(document).on('input', '.qty', function () {
            updateTotalQty();
        });

        function setItemsBeforeSubmit(form, e) {
            e.preventDefault();

            let data = [];

            $('#datatable tbody tr').each(function () {

                let row = $(this);

                data.push({
                    id: row.attr('data-id'),
                    qty: row.find('.qty').val()
                });

            });

            if (data.length === 0) {
                Swal.fire('Warning', 'Data masih kosong!', 'warning');
                return;
            }

            let invalid = data.some(item => !item.qty || item.qty <= 0);

            if (invalid) {
                Swal.fire('Warning', 'Qty tidak boleh kosong atau 0!', 'warning');
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
            let total = 0;

            $('#datatable tbody .qty').each(function () {
                total += parseFloat($(this).val() || 0);
            });

            $('#total_qty').text(total);
        }
    </script>
@endsection
