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
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-exchange"></i> Mutasi Barang Jadi Stok</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a href="{{ route('create-mutasi-fg-stock') }}" class="btn btn-outline-primary position-relative">
                    <i class="fas fa-plus"></i>
                    Baru
                </a>
            </div>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        oninput="dataTableReload()" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        oninput="dataTableReload()" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <a onclick="export_excel_mutasi_int()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-sm w-100 table-hover display nowrap">
                    <thead class="table-primary">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>No. Trans</th>
                            <th>Tgl. Trans</th>
                            <th>Buyer</th>
                            <th>Brand</th>
                            <th>Style</th>
                            <th>Grade</th>
                            <th>WS</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Qty</th>
                            <th>Lokasi Asal</th>
                            <th>No. Karton Asal</th>
                            <th>Lokasi Tujuan</th>
                            <th>No. Karton Tujuan</th>
                            <th>No. BPB</th>
                            <th>No. BPPB</th>
                        </tr>
                    </thead>
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
        $(document).ready(function() {
            dataTableReload();
        })

        function dataTableReload() {
            $('#datatable thead tr').clone(true).appendTo('#datatable thead');
            $('#datatable thead tr:eq(1) th').each(function(i) {
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

            });

            let datatable = $("#datatable").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                paging: true,
                searching: true,
                destroy: true,
                scrollX: true,
                ajax: {
                    url: '{{ route('mutasi-fg-stock') }}',
                    data: function(d) {
                        d.dateFrom = $('#tgl-awal').val();
                        d.dateTo = $('#tgl-akhir').val();
                    },
                },
                columns: [{
                        data: 'no_trans'

                    }, {
                        data: 'tgl_mut_fix'
                    },
                    {
                        data: 'buyer'
                    },
                    {
                        data: 'brand'
                    },
                    {
                        data: 'styleno'
                    },
                    {
                        data: 'grade'
                    },
                    {
                        data: 'ws'
                    },
                    {
                        data: 'color'
                    },
                    {
                        data: 'size'
                    },
                    {
                        data: 'qty_mut'
                    },
                    {
                        data: 'lokasi_asal'
                    },
                    {
                        data: 'no_carton_asal'
                    },
                    {
                        data: 'lokasi_tujuan'
                    },
                    {
                        data: 'no_carton_tujuan'
                    },
                    {
                        data: 'no_trans'
                    },
                    {
                        data: 'no_trans_out'
                    },
                ],
                columnDefs: [
                    // {
                    //     targets: [10],
                    //     render: (data, type, row, meta) => {
                    //         return `
                // <div
                // class='d-flex gap-1 justify-content-center'>
                // <a class='btn btn-warning btn-sm' href='{{ route('create-dc-in') }}/` +
                    //             row.id +
                    //             `' data-bs-toggle='tooltip'><i class='fas fa-edit'></i></a>
                //     <a class='btn btn-success btn-sm' href='{{ route('create-dc-in') }}/` +
                    //             row.id +
                    //             `' data-bs-toggle='tooltip'><i class='fas fa-lock'></i></a>
                // </div>
                //     `;
                    //     }
                    // },
                    {
                        "className": "dt-center",
                        "targets": "_all"
                    },
                ]
            });
        }

        function export_excel_mutasi_int() {
            let from = document.getElementById("tgl-awal").value;
            let to = document.getElementById("tgl-akhir").value;

            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "get",
                url: '{{ route('export_excel_mutasi_int_fg_stok') }}',
                data: {
                    from: from,
                    to: to
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    {
                        swal.close();
                        Swal.fire({
                            title: 'Data Sudah Di Export!',
                            icon: "success",
                            showConfirmButton: true,
                            allowOutsideClick: false
                        });
                        var blob = new Blob([response]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = from + " sampai " +
                            to + "Laporan Mutasi Internal FG Stock.xlsx";
                        link.click();

                    }
                },
            });
        }
    </script>
@endsection
