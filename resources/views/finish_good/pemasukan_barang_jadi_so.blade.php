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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-box"></i> Pemasukan Barang Jadi SO</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label d-block"><small>&nbsp;</small></label>
                    <a onclick="dataTableReload()" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-search fa-sm"></i>
                        Cari
                    </a>
                </div>
                <div class="mb-3">
                    <label class="form-label d-block"><small>&nbsp;</small></label>
                    <a onclick="export_excel()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
                <div class="mb-3 ms-auto">
                    <label class="form-label d-block"><small>&nbsp;</small></label>
                    <a href="{{ route('create_pemasukan_barang_jadi_so') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-plus fa-sm"></i>
                        Tambah
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped w-100 text-nowrap">
                    <thead class="table-primary">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>No. BPB</th>
                            <th>No. Trans</th>
                            <th>Tgl. BPB</th>
                            <th>Supplier</th>
                            <th>Jenis Pemasukan</th>
                            <th>Jenis Dok</th>
                            <th>Invoice</th>
                            <th>No. Reff</th>
                            <th>PO</th>
                            <th>WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th>Grade</th>
                            <th>User</th>
                            <th>Tgl. Input</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="13"></th>
                            <th></th>
                            <th>PCS</th>
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                </table>
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
    <script>
        function dataTableReload() {
            datatable.ajax.reload();
        }

        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            $(this).html('<input type="text" class="form-control form-control-sm"/>');
            $('input', this).on('keyup change', function() {
                if (datatable.column(i).search() !== this.value) {
                    datatable.column(i).search(this.value).draw();
                }
            });
        });

        let datatable = $("#datatable").DataTable({
            footerCallback: function(row, data, start, end, display) {
                let api = this.api();
                // Total qty dari semua baris hasil filter (bukan cuma halaman yang tampil)
                let total = api.column(13, {search: 'applied'}).data()
                    .reduce((a, b) => a + (parseInt(b) || 0), 0);
                $(api.column(0).footer()).html('Total');
                $(api.column(13).footer()).html(total.toLocaleString('id-ID'));
            },
            ordering: false,
            paging: true,
            lengthMenu: [
                [10, 50, 100, -1],
                [10, 50, 100, 'All']
            ],
            searching: true,
            scrollY: '400px',
            scrollX: true,
            scrollCollapse: true,
            processing: true,
            // Data diambil sekali per rentang tanggal, filter & paging di browser
            ajax: {
                url: '{{ route('pemasukan_barang_jadi_so') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [
                {data: 'bpbno_int'},
                {data: 'bpbno'},
                {data: 'bpbdate'},
                {data: 'supplier'},
                {data: 'jenis_trans'},
                {data: 'jenis_dok'},
                {data: 'invno'},
                {data: 'reffno'},
                {data: 'po'},
                {data: 'ws'},
                {data: 'styleno'},
                {data: 'color'},
                {data: 'size'},
                {data: 'qty'},
                {data: 'unit'},
                {data: 'grade'},
                {data: 'username'},
                {data: 'dateinput'},
            ],
            columnDefs: [{
                className: "align-left",
                targets: "_all"
            }, {
                className: "text-end",
                targets: [13]
            }]
        });

        // Export sesuai Tgl Awal & Tgl Akhir (data sama dengan tabel)
        function export_excel() {
            let from = $('#tgl-awal').val();
            let to = $('#tgl-akhir').val();

            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => Swal.showLoading(),
                allowOutsideClick: false,
            });

            $.ajax({
                type: 'get',
                url: '{{ route('pbj_so_export_excel') }}',
                data: {from: from, to: to},
                xhrFields: {responseType: 'blob'},
                success: function(response) {
                    Swal.fire({
                        title: 'Data Sudah Di Export!',
                        icon: 'success',
                        showConfirmButton: true,
                        allowOutsideClick: false
                    });
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(new Blob([response]));
                    link.download = 'Laporan Pemasukan Barang Jadi SO ' + from + ' sampai ' + to + '.xlsx';
                    link.click();
                },
                error: function() {
                    Swal.fire({icon: 'error', title: 'Gagal export', text: 'Terjadi kesalahan pada server.'});
                },
            });
        }
    </script>
@endsection
