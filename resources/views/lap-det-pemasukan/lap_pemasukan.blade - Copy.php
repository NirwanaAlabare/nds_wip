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
    <form action="{{ route('export_excel_pemasukan') }}" method="get">
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-file-alt fa-sm"></i> Laporan Pemakaian</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div class="mb-3">
                        <label class="form-label"><small>Tanggal Awal</small></label>
                        <input type="date" class="form-control form-control-sm" id="from" name="from"
                            value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><small>Tanggal Akhir</small></label>
                        <input type="date" class="form-control form-control-sm" id="to" name="to"
                            value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        {{-- <button class="btn btn-primary btn-sm" onclick="export_excel()">Tampilkan</button> --}}
                        <input type='button' class='btn btn-primary btn-sm' onclick="dataTableReload();" value="Tampilkan">
                        <button type='submit' name='submit' class='btn btn-success btn-sm'>
                            <i class="fas fa-file-excel"></i> Export</button>
                    </div>
                </div>
    </form>
    <div class="table-responsive">
        <table id="datatable" class="table table-bordered table-striped w-100 text-nowrap">
            <thead>
                <tr>
                    <th>No BPB</th>
                    <th>Tgl BPB</th>
                    <th>No Inv</th>
                    <th>Jenis Dok</th>
                    <th>No Aju</th>
                    <th>Tgl AJu</th>
                    <th>No Daftar</th>
                    <th>Tgl Daftar</th>
                    <th>Supplier</th>
                    <th>No PO</th>
                    <th>Type</th>
                    <th>No Inv/SJ</th>
                    <th>Id Item</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Warna</th>
                    <th>Ukuran</th>
                    <th>Qty BPB</th>
                    <th>Qty Good</th>
                    <th>Qty Reject</th>
                    <th>Satuan</th>
                    <th>Berat Bersih</th>
                    <th>Keterangan</th>
                    <th>Nama User</th>
                    <th>Approve By</th>
                    <th>WS</th>
                    <th>Style</th>
                    <th>Curr</th>
                    <th>Price</th>
                    <th>Price Act</th>
                    <th>Jenis Trans</th>
                    <th>Reff No</th>
                    <th>No Rak</th>
                    <th>Panel</th>
                    <th>Color Garment</th>
                </tr>
            </thead>
            <tbody>
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

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('lap-det-pemasukan') }}',
                data: function(d) {
                    d.dateFrom = $('#from').val();
                    d.dateTo = $('#to').val();
                },
            },
            columns: [{
                    data: 'bpbno'
                },
                {
                    data: 'bpbdate'
                },
                {
                    data: 'invno'
                },
                {
                    data: 'jenis_dok'
                },
                {
                    data: 'no_aju'
                },
                {
                    data: 'tgl_aju'
                },
                {
                    data: 'bcno'
                },
                {
                    data: 'bcdate'
                },
                {
                    data: 'supplier'
                },
                {
                    data: 'pono'
                },
                {
                    data: 'tipe_com'
                },
                {
                    data: 'invno'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'goods_code'
                },
                {
                    data: 'itemdesc'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'qty'
                },
                {
                    data: 'qty_good'
                },
                {
                    data: 'qty_reject'
                },
                {
                    data: 'unit'
                },
                {
                    data: 'berat_bersih'
                },
                {
                    data: 'remark'
                },
                {
                    data: 'username'
                },
                {
                    data: 'confirm_by'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'curr'
                },
                {
                    data: 'price'
                },
                {
                    data: 'price'
                },
                {
                    data: 'jenis_trans'
                },
                {
                    data: 'reffno'
                },
                {
                    data: 'rak'
                },
                {
                    data: 'nama_panel'
                },
                {
                    data: 'color_gmt'
                },
            ],
        });

        function dataTableReload() {
            datatable.ajax.reload();
        }
    </script>
@endsection
