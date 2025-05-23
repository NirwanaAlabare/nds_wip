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
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <form action="{{ route('store-lokasi-fg-stock') }}" method="post" onsubmit="submitForm(this, event)" name='form'
            id='form'>
            @method('POST')
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h3 class="modal-title fs-5">Tambah Lokasi FG Stock</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Kode Lokasi :</label>
                            <input type='text' class='form-control form-control-sm' id="txtkode_lok" name="txtkode_lok"
                                value="" readonly>
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Lokasi :</label>
                            <input type='text' class='form-control form-control-sm' id="txtlok" name="txtlok"
                                style="text-transform: uppercase" oninput="setinisial()" value = '' autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Tingkat :</label>
                            <input type='number' class='form-control form-control-sm' id='txttingkat' name='txttingkat'
                                oninput="setinisial()" value = '' autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Baris :</label>
                            <input type='number' class='form-control form-control-sm' id='txtbaris' name='txtbaris'
                                oninput="setinisial()" value = '' autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal"><i
                                class="fas fa-times-circle"></i> Tutup</button>
                        <button type="submit" class="btn btn-outline-success"><i class="fas fa-check"></i> Simpan </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-box"></i> Penerimaan Barang Jadi Stok</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a href="{{ route('create-bpb-fg-stock') }}" class="btn btn-outline-primary position-relative">
                    <i class="fas fa-plus"></i>
                    Baru
                    @if ($cek_temp == null)
                    @else
                        <span
                            class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light rounded-circle">
                            <span class="visually-hidden">New alerts</span>
                        </span>
                    @endif
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
                    <a onclick="export_excel_bpb()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered 100 table-hover display nowrap">
                    <thead class="table-primary">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>No. Trans</th>
                            <th>Tgl. Trans</th>
                            <th>Lokasi</th>
                            <th>No. Karton</th>
                            <th>Buyer</th>
                            <th>Brand</th>
                            <th>Style</th>
                            <th>Grade</th>
                            <th>WS</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Qty</th>
                            <th>Sumber</th>
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
        $(document).ready(() => {
            dataTableReload();
        });

        function dataTableReload() {
            datatable.ajax.reload();
        }

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
                url: '{{ route('bpb-fg-stock') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'no_trans'

                }, {
                    data: 'tgl_terima_fix'
                },
                {
                    data: 'lokasi'
                },
                {
                    data: 'no_carton'
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
                    data: 'qty'
                },
                {
                    data: 'sumber_pemasukan'
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

        function export_excel_bpb() {
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
                url: '{{ route('export_excel_bpb_fg_stok') }}',
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
                            to + "Laporan Penerimaan FG Stock.xlsx";
                        link.click();

                    }
                },
            });
        }
    </script>
@endsection
