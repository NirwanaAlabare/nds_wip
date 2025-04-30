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
    <div class="modal fade" id="exampleModalTambah" tabindex="-1" role="dialog" aria-labelledby="exampleModalTambahLabel"
        aria-hidden="true">
        <form action="{{ route('store_karton_alokasi') }}" method="post" onsubmit="submitForm(this, event)"
            name='form_modal_tambah' id='form_modal_tambah'>
            @method('POST')
            <div class="modal-dialog modal-dialog-scrollable modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h3 class="modal-title fs-5">Tambah Alokasi</h3>
                        {{-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> --}}
                    </div>
                    <div class="modal-body">
                        <div class="card-body">
                            <form id="form_h" name='form_h' method='post'
                                action="{{ route('hapus_master_karton_det') }}" onsubmit="submitForm(this, event)">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><small><b>Kode Lokasi</b></small></label>
                                            <input type="text" class="form-control form-control-sm" id="txtkode_lok"
                                                name="txtkode_lok" style="width: 100%;" readonly>
                                            <input type="hidden" class="form-control form-control-sm" id="txtuser"
                                                name="txtuser" style="width: 100%;" readonly value={{ $user }}>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><small><b>Lokasi</b></small></label>
                                            <input type="text" class="form-control form-control-sm" id="txtlok"
                                                name="txtlok" style="width: 100%;" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><small><b>PO</b></small></label>
                                            <select class="form-control select2bs4" id="cbopo" name="cbopo"
                                                style="width: 100%;" onchange="getno_karton()">
                                                <option selected="selected" value="" disabled="true">Pilih PO</option>
                                                @foreach ($data_po as $datapo)
                                                    <option value="{{ $datapo->isi }}">
                                                        {{ $datapo->tampil }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><small><b>No. Carton</b></small></label>
                                            <select class='form-control select2bs4 form-control-sm rounded'
                                                style='width: 100%;' name='cbono_carton' id='cbono_carton'></select>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div class="input-group">
                                                <a onclick="insert_tmp()"
                                                    class="btn btn-outline-primary position-relative btn-sm">
                                                    <i class="fas fa-plus fa-sm"></i>
                                                    Tambah
                                                </a>
                                            </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class="col-md-12 table-responsive">
                                        <table id="datatable_preview"
                                            class="table table-bordered 100 text-nowrap">
                                            <thead class="table-primary">
                                                <tr style='text-align:center; vertical-align:middle'>
                                                    <th>Buyer</th>
                                                    <th>PO</th>
                                                    <th>Tgl. Shipment</th>
                                                    <th>No. Carton</th>
                                                    <th>WS</th>
                                                    <th>Color</th>
                                                    <th>Size</th>
                                                    <th>Qty</th>
                                                    <th>Act</th>
                                                </tr>
                                            </thead>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="3"></th>
                                                    <th> <input type = 'text' class="form-control form-control-sm"
                                                            style="width:75px" readonly id = 'total_qty_chk'> </th>
                                                    <th colspan="3"></th>
                                                    <th> <input type = 'text' class="form-control form-control-sm"
                                                            style="width:75px" readonly id = 'total_qty_tot'> </th>
                                                    <th>PCS</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a onclick="delete_tmp()" class="btn btn-outline-danger position-relative" data-bs-dismiss="modal">
                            <i class="fas fa-times-circle"></i>
                            Tutup
                        </a>
                        {{-- <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal"><i
                                class="fas fa-times-circle"></i> Tutup</button> --}}
                        <button type="submit" class="btn btn-outline-success"><i class="fas fa-check"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>


    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-truck-loading"></i> Alokasi Karton</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <a onclick="export_excel()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover 100 text-nowrap">
                    <thead class="table-success">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>Kode Lokasi</th>
                            <th>Lokasi</th>
                            <th>Keterangan</th>
                            <th>Total Carton</th>
                            <th>Act</th>
                        </tr>
                    </thead>
                    {{-- <tfoot>
                        <tr>
                            <th colspan="7"></th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_qty_chk'> </th>
                            <th>PCS</th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot> --}}
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
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        });

        $('#exampleModalTambah').on('show.bs.modal', function(e) {
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                dropdownParent: $("#exampleModalTambah"),
                containerCssClass: 'form-control-sm rounded'
            })
        })
    </script>
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        $(document).ready(() => {
            dataTableReload();
        });

        function dataTableReload() {
            datatable.ajax.reload();
        }

        function reset() {
            $("#form_modal_tambah").trigger("reset");
            $("#cbopo").val('').trigger('change');
        }

        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm"/>');
            $('input', this).on('keyup change', function() {
                if (datatable.column(i).search() !== this.value) {
                    datatable
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        $('#datatable_preview thead tr').clone(true).appendTo('#datatable_preview thead');
        $('#datatable_preview thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm"/>');
            $('input', this).on('keyup change', function() {
                if (datatable_preview.column(i).search() !== this.value) {
                    datatable_preview
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        let datatable = $("#datatable").DataTable({
            // "footerCallback": function(row, data, start, end, display) {
            //     var api = this.api(),
            //         data;

            //     // converting to interger to find total
            //     var intVal = function(i) {
            //         return typeof i === 'string' ?
            //             i.replace(/[\$,]/g, '') * 1 :
            //             typeof i === 'number' ?
            //             i : 0;
            //     };

            //     // computing column Total of the complete result
            //     var sumTotal = api
            //         .column(7)
            //         .data()
            //         .reduce(function(a, b) {
            //             return intVal(a) + intVal(b);
            //         }, 0);

            //     // Update footer by showing the total with the reference of the column index
            //     $(api.column(0).footer()).html('Total');
            //     $(api.column(7).footer()).html(sumTotal);
            // },


            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route('finish_good_alokasi_karton') }}',
            },
            columns: [{
                    data: 'kode_lok'

                }, {
                    data: 'lokasi'
                },
                {
                    data: 'ket'
                },
                {
                    data: 'tot_karton'
                },
                {
                    data: 'id'
                },
            ],

            columnDefs: [{
                    "className": "align-left",
                    "targets": "_all"
                },
                {
                    targets: [4],
                    render: (data, type, row, meta) => {
                        return `
                <div class='d-flex gap-1 justify-content-center'>
                <a class='btn btn-primary btn-sm' data-bs-toggle="modal"
                data-bs-target="#exampleModalTambah"
                onclick="show_data_tambah('` + row.id + `');"><i class='fas fa-plus'></i></a>
                </div>
                        `;
                    }
                },

            ]

        });

        function show_data_tambah(id) {
            reset();
            let id_e = id;
            jQuery.ajax({
                url: '{{ route('getdata_lokasi_alokasi') }}',
                method: 'GET',
                data: {
                    id_e: id_e
                },
                dataType: 'json',
                success: async function(response) {
                    document.getElementById('txtkode_lok').value = response.kode_lok;
                    document.getElementById('txtlok').value = response.lokasi;
                    dataTablePreviewReload();
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        }

        function getno_karton() {
            let cbopo = document.form_modal_tambah.cbopo.value;
            let txtkode_lok = document.form_modal_tambah.txtkode_lok.value;
            let html = $.ajax({
                type: "GET",
                url: '{{ route('getno_carton_alokasi') }}',
                data: {
                    cbopo: cbopo,
                    txtkode_lok: txtkode_lok
                },
                async: false
            }).responseText;
            if (html != "") {
                $("#cbono_carton").html(html);
            }
        };

        function dataTablePreviewReload() {
            datatable_preview.ajax.reload();
        }

        let datatable_preview = $("#datatable_preview").DataTable({

            "footerCallback": function(row, data, start, end, display) {
                var api = this.api(),
                    data;

                // converting to interger to find total
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };

                var columnData1 = api.column(1).data();
                var columnData2 = api.column(3).data();

                var groups = [];


                for (var i = 0; i < columnData1.length; i++) {

                    var key = [columnData1[i], columnData2[i]].join(',');

                    if (!groups.includes(key)) {

                        groups.push(key);

                    }

                }
                // Count the number of unique combinations
                var rowCount = groups.length;
                var sumTotal_Isi = api
                    .column(7)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(3).footer()).html(rowCount);
                $(api.column(7).footer()).html(sumTotal_Isi);
            },


            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route('show_preview_detail_alokasi') }}',
                data: function(d) {
                    d.txtkode_lok = $('#txtkode_lok').val();
                },
            },
            columns: [{
                    data: 'buyer'

                }, {
                    data: 'po'
                },
                {
                    data: 'tgl_shipment_fix'
                },
                {
                    data: 'no_carton'
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
                    data: 'stat'
                },
            ],

            columnDefs: [{
                    "className": "align-left",
                    "targets": "_all"
                },
                {
                    targets: [8],
                    render: (data, type, row, meta) => {
                        if (row.stat == 'tmp') {
                            return `
                <div
                class='d-flex gap-1 justify-content-center'>
                <a class='btn btn-danger btn-sm'  data-bs-toggle="tooltip"
                onclick="hapus('` + row.po + `','` + row.no_carton + `','` + row.notes + `')"><i class='fas fa-trash'></i></a>
                </div>
                        `;
                        } else {

                            return ``;

                        }

                    }
                },

            ]

        });

        function insert_tmp() {
            let cbolok = $('#txtkode_lok').val();
            let cbopo = $('#cbopo').val();
            let cbono_carton = $('#cbono_carton').val();

            $.ajax({
                type: "post",
                url: '{{ route('insert_tmp_alokasi_karton') }}',
                data: {
                    cbolok: cbolok,
                    cbopo: cbopo,
                    cbono_carton: cbono_carton
                },
                success: function(response) {
                    dataTablePreviewReload();
                    getno_karton();
                },
            });
        }

        function hapus(id_po, id_no_carton, id_notes) {
            $.ajax({
                type: "post",
                url: '{{ route('alokasi_hapus_tmp') }}',
                data: {
                    id_po: id_po,
                    id_no_carton: id_no_carton,
                    id_notes: id_notes
                },
                success: async function(res) {
                    iziToast.success({
                        message: 'Data Berhasil Dihapus',
                        position: 'topCenter'
                    });
                    dataTablePreviewReload();
                    getno_karton();
                }
            });

        }

        function delete_tmp() {
            let user = $('#txtuser').val();
            let cbolok = $('#txtkode_lok').val();

            $.ajax({
                type: "post",
                url: '{{ route('delete_tmp_all_alokasi_karton') }}',
                data: {
                    user: user,
                    cbolok: cbolok
                },
                success: function(response) {
                    dataTableReload();
                    // dataTablePreviewReload();
                    // getno_karton();
                },
            });
        }

        function export_excel() {
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
                url: '{{ route('export_excel_fg_alokasi') }}',
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
                        link.download = "Laporan Alokasi .xlsx";
                        link.click();

                    }
                },
            });
        }
    </script>
@endsection
