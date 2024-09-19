@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <!-- Webcam -->
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> --}}
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.25/webcam.min.js"></script> --}}

    {{-- <style type="text/css">
        #results {
            width: 240;
            height: 320;
            border: 1px solid;
            background: #ccc;
        }
    </style> --}}
@endsection

@section('content')
    <div class="modal fade" id="exampleModalEdit" tabindex="-1" role="dialog" aria-labelledby="exampleModalEditLabel"
        aria-hidden="true">
        <form action="{{ route('edit-master-mut-mesin') }}" method="post" onsubmit="submitForm(this, event)" name='form'
            id='form' enctype="multipart/form-data">
            @method('POST')
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h3 class="modal-title fs-5">Edit Master Mesin</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Kode QR :</label>
                            <input type='text' class='form-control form-control-sm' id="txtedit_qr" name="txtedit_qr"
                                style="text-transform: uppercase" value="" readonly autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Jenis :</label>
                            <input type='text' class='form-control form-control-sm' id="txtedit_jenis"
                                name="txtedit_jenis" style="text-transform: uppercase" value = '' autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Brand :</label>
                            <input type='text' class='form-control form-control-sm' id='txtedit_brand'
                                name='txtedit_brand' style="text-transform: uppercase" value = '' autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Tipe Mesin :</label>
                            <input type='text' class='form-control form-control-sm' id='txtedit_tipe' name='txtedit_tipe'
                                style="text-transform: uppercase" value = '' autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Serial No :</label>
                            <input type='text' class='form-control form-control-sm' id='txtedit_serial'
                                name='txtedit_serial' style="text-transform: uppercase" value = '' autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Gambar :</label>
                            <input type="file" id="uploadphoto" name="uploadphoto" />
                            <input type="hidden" id="nm_gambar" name="nm_gambar" />
                        </div>
                        <div class="form-group">
                            <center>
                                <img id="preview" src="" width="300" height="350">
                            </center>
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

    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <form action="{{ route('store-master-mut-mesin') }}" method="post" onsubmit="submitForm(this, event)"
            name='form' id='form'>
            @method('POST')
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h3 class="modal-title fs-5">Tambah Master Mesin</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Kode QR :</label>
                            <input type='text' class='form-control form-control-sm' id="txtkode_qr" name="txtkode_qr"
                                style="text-transform: uppercase" value="" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Jenis :</label>
                            <input type='text' class='form-control form-control-sm' id="txtjenis" name="txtjenis"
                                style="text-transform: uppercase" value = '' autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Brand :</label>
                            <input type='text' class='form-control form-control-sm' id='txtbrand' name='txtbrand'
                                style="text-transform: uppercase" value = '' autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Tipe Mesin :</label>
                            <input type='text' class='form-control form-control-sm' id='txttipe' name='txttipe'
                                style="text-transform: uppercase" value = '' autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Serial No :</label>
                            <input type='text' class='form-control form-control-sm' id='txtserial_no'
                                name='txtserial_no' style="text-transform: uppercase" value = '' autocomplete="off">
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
            <h5 class="card-title fw-bold mb-0">Master Mesin <i class="fas fa-cogs"></i></h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#exampleModal"
                        onclick="reset()"><i class="fas fa-plus"></i> Baru</button>
                </div>
                <div class="mb-3">
                    <a onclick="export_excel_master_mesin()" class="btn btn-outline-success">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>
            {{-- <form method="POST" name="form_1" id="form_1" action="{{ route('webcam_capture') }}">
                @csrf
                <div class="row justify-content-center align-items-end">
                    <div class="col-md-12">
                        <div id="my_camera"></div>
                        <input type=button value="Take Snapshot" onClick="take_snapshot()">
                        <input type="text" name="image" id="image" class="image-tag">
                    </div>
                    <div class="col-md-12">
                        <center>
                            <div id="results">
                            </div>
                        </center>
                    </div>
                    <div class="col-2">
                        <button class="btn btn-success">Submit</button>
                    </div>
                </div>
            </form> --}}

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-sm w-100 text-nowrap">
                    <thead>
                        <tr style='text-align:left; vertical-align:middle'>
                            <th>Kode QR</th>
                            <th>Jenis</th>
                            <th>Brand</th>
                            <th>Tipe</th>
                            <th>Serial No</th>
                            <th>Juml. Transaksi</th>
                            <th>Act</th>
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

    {{-- <script language="JavaScript">
        Webcam.set({
            width: 240,
            height: 320,
            image_format: 'png',
            jpeg_quality: 100

        });
        Webcam.set('constraints', {
            facingMode: "environment"
        });
        Webcam.attach('#my_camera');

        function take_snapshot() {
            Webcam.snap(function(data_uri) {
                $(".image-tag").val(data_uri);
                document.getElementById('results').innerHTML = '<img src="' + data_uri + '"/>';
            });

        }
    </script> --}}

    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        function reset() {
            $("#form").trigger("reset");
        }
    </script>
    <script>
        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            if (i != 6) {
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
            } else {
                $(this).empty();
            }
        });

        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: true,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'All']
            ],
            searching: true,
            ajax: {
                url: '{{ route('master-mut-mesin') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'id_qr'

                }, {
                    data: 'jenis_mesin'
                },
                {
                    data: 'brand'
                },
                {
                    data: 'tipe_mesin'
                },
                {
                    data: 'serial_no'
                },
                {
                    data: 'jml'
                },
            ],
            columnDefs: [{
                    targets: [6],
                    render: (data, type, row, meta) => {

                        if (row.jml == '0') {
                            return `
                    <div
                    class='d-flex gap-1 justify-content-center'>
                <a class='btn btn-warning btn-sm'  data-bs-toggle="modal"
                        data-bs-target="#exampleModalEdit"
                onclick="show_data_edit_h('` + row.id_qr + `');"><i class='fas fa-edit'></i></a>
                    <a class='btn btn-danger btn-sm' data-bs-toggle='tooltip' onclick="hapus('` + row.id_qr + `')"><i class='fas fa-trash'></i></a>
                    </div>
                        `;
                        } else {
                            return `
                    <div
                    class='d-flex gap-1 justify-content-center'>
                <a class='btn btn-warning btn-sm'  data-bs-toggle="modal"
                        data-bs-target="#exampleModalEdit"
                onclick="show_data_edit_h('` + row.id_qr + `');"><i class='fas fa-edit'></i></a>                    </div>
                        `;
                        }


                    }
                },
                // <a class='btn btn-warning btn-sm' href='{{ route('create-dc-in') }}/` +
            //             row.id +
            //             `' data-bs-toggle='tooltip'><i class='fas fa-edit'></i></a>
                {
                    "className": "dt-left",
                    "targets": "_all"
                },
            ]
        });

        function export_excel_master_mesin() {
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
                url: '{{ route('export_excel_master_mesin') }}',
                data: {},
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
                        link.download = "Laporan Master Mesin.xlsx";
                        link.click();

                    }
                },
            });
        }

        function hapus(id_qr) {
            $.ajax({
                type: "post",
                url: '{{ route('hapus-data-mesin') }}',
                data: {
                    id_qr: id_qr
                },
                success: async function(res) {
                    iziToast.success({
                        message: 'Data Berhasil Dihapus',
                        position: 'topCenter'
                    });
                    $('#datatable').DataTable().ajax.reload();
                }
            });

        }

        function show_data_edit_h(id_qr) {
            $("#uploadphoto").val('');
            let id_qr_edit = id_qr;
            jQuery.ajax({
                url: '{{ route('getdata_mesin') }}',
                method: 'GET',
                data: {
                    id_qr_edit: id_qr_edit
                },
                dataType: 'json',
                success: async function(response) {
                    document.getElementById('txtedit_qr').value = response.id_qr;
                    document.getElementById('txtedit_jenis').value = response.jenis_mesin;
                    document.getElementById('txtedit_brand').value = response.brand;
                    document.getElementById('txtedit_tipe').value = response.tipe_mesin;
                    document.getElementById('txtedit_serial').value = response.serial_no;
                    document.getElementById('nm_gambar').value = response.gambar;
                    $("#preview").attr("src",
                        "https://nag.ddns.net/nds_wip/public/storage/gambar_mesin/" + response
                        .gambar);
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        }

        function readURL(input) {

            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('#preview').attr('src', e.target.result).show();
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#uploadphoto").change(function() {
            readURL(this);
            $('#preview').show();
        });

        // $('#preview').on("click", function() {
        //     $('#uploadphoto').replaceWith(selected_photo = $('#uploadphoto').clone(true));
        //     $('#preview').removeProp('src').hide();;
        // });
    </script>
@endsection
