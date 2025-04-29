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
        <form action="{{ route('store_finish_good_master_lokasi') }}" method="post" onsubmit="submitForm(this, event)"
            name='form' id='form'>
            @method('POST')
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h3 class="modal-title fs-5">Tambah Master Lokasi</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Kode Lokasi :</label>
                            <input type='text' class='form-control form-control-sm' id="txtkode_lokasi"
                                name="txtkode_lokasi" style="text-transform: uppercase" value="" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Lokasi :</label>
                            <input type='text' class='form-control form-control-sm' id="txtlokasi" name="txtlokasi"
                                style="text-transform: uppercase" value="" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Keterangan :</label>
                            <input type='text' class='form-control form-control-sm' id="txtket" name="txtket"
                                style="text-transform: uppercase" value = '' autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal"><i
                                class="fas fa-times-circle"></i> Tutup</button>
                        <button type="submit" class="btn btn-outline-success"><i class="fas fa-check"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="modal fade" id="exampleModalEdit" tabindex="-1" role="dialog" aria-labelledby="exampleModalEditLabel"
        aria-hidden="true">
        <form action="{{ route('edit_finish_good_master_lokasi') }}" method="post" onsubmit="submitForm(this, event)"
            name='form' id='form' enctype="multipart/form-data">
            @method('POST')
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h3 class="modal-title fs-5">Edit Master Lokasi</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Kode Lokasi :</label>
                            <input type='hidden' class='form-control form-control-sm' id="txtid_lokasi_edit"
                                name="txtid_lokasi_edit" style="text-transform: uppercase" value=""
                                autocomplete="off">
                            <input type='text' class='form-control form-control-sm' id="txtkode_lokasi_edit"
                                name="txtkode_lokasi_edit" style="text-transform: uppercase" value=""
                                autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Lokasi :</label>
                            <input type='text' class='form-control form-control-sm' id="txtlok_edit"
                                name="txtlok_edit" style="text-transform: uppercase" value = '' autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Keterangan :</label>
                            <input type='text' class='form-control form-control-sm' id="txtket_edit"
                                name="txtket_edit" style="text-transform: uppercase" value = '' autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal"><i
                                class="fas fa-times-circle"></i> Tutup</button>
                        <button type="submit" class="btn btn-outline-success"><i class="fas fa-check"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>


    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-map-marker"></i> Master Lokasi</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#exampleModal"
                        onclick="reset()"><i class="fas fa-plus"></i> Baru</button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped 100 text-nowrap">
                    <thead class="table-success">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>Kode Lokasi</th>
                            <th>Lokasi</th>
                            <th>Keterangan</th>
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

        function reset() {
            $("#form").trigger("reset");
        }

        function dataTableReload() {
            datatable.ajax.reload();
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
            paging: true,
            lengthMenu: [
                [5, 50, 100, -1],
                [5, 50, 100, 'All']
            ],
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route('finish_good_master_lokasi') }}',
            },
            columns: [{
                    data: 'kode_lok'

                },
                {
                    data: 'lokasi'

                }, {
                    data: 'ket'

                }, {
                    data: 'id'
                },
            ],

            columnDefs: [{
                    "className": "align-left",
                    "targets": "_all"
                },
                {
                    targets: [3],
                    render: (data, type, row, meta) => {
                        return `
                    <div
                    class='d-flex gap-1 justify-content-center'>
                <a class='btn btn-warning btn-sm'  data-bs-toggle="modal"
                        data-bs-target="#exampleModalEdit"
                onclick="show_data_edit_h('` + row.id + `');"><i class='fas fa-edit'></i></a>                    </div>
                        `;
                    }
                },

            ]

        });

        function show_data_edit_h(id) {
            let id_e = id;
            jQuery.ajax({
                url: '{{ route('getdata_finish_good_master_lokasi') }}',
                method: 'GET',
                data: {
                    id_e: id_e
                },
                dataType: 'json',
                success: async function(response) {
                    document.getElementById('txtkode_lokasi_edit').value = response.kode_lok;
                    document.getElementById('txtket_edit').value = response.ket;
                    document.getElementById('txtlok_edit').value = response.lokasi;
                    document.getElementById('txtid_lokasi_edit').value = response.id;
                    if (response.status_lokasi == 'Y') {
                        document.getElementById('txtkode_lokasi_edit').readOnly = true;
                    } else {
                        document.getElementById('txtkode_lokasi_edit').readOnly = false;
                    }
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        }
    </script>
@endsection
