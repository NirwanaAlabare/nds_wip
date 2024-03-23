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
    <div class="modal fade" id="updateTmpDcModal" tabindex="-1" role="dialog" aria-labelledby="updateTmpDcModalLabel"
        aria-hidden="true">
        <form action="{{ route('update_tmp_dc_in') }}" method="post" name='form_modal' onsubmit="submitForm(this, event)">
            @method('PUT')
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5" id="updateTmpDcModalLabel"></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class='row'>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Qty In</small></label>
                                    <input type='hidden' class='form-control' id='txtqtyply' name='txtqtyply' value='' readonly>
                                    <input type='text' class='form-control' id='txtqty' name='txtqty' value='' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Reject</small></label>
                                    <input type='number' class='form-control' id='txtqtyreject' name='txtqtyreject' oninput='sum();' autocomplete='off'>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Replacement</small></label>
                                    <input type='number' class='form-control' id='txtqtyreplace' name='txtqtyreplace' oninput='sum();' autocomplete='off'>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Keterangan</small></label>
                                    <input type='text' class='form-control' id='txtket' name='txtket' value=''>
                                    <input type='hidden' class='form-control' id='id_c' name='id_c' value=''>
                                    <input type='hidden' class='form-control' id='id_kode' name='id_kode' value=''>
                                </div>
                            </div>
                        </div>
                        <div class='row'>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Tujuan</small></label>
                                    <input type='text' class='form-control' id='txttuj' name='txttuj' value='' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Tempat</small></label>
                                    <select class='form-control select2bs4' style='width: 100%;' name='cbotempat' id='cbotempat' onchange='getlokasi();'></select>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Lokasi</small></label>
                                    <select class='form-control select2bs4' style='width: 100%;' name='cbolokasi' id='cbolokasi'></select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-sb">Simpan </button>
                    </div>
                </div>
            </div>
        </form>
    </div>




    <form action="{{ route('store-dc-in') }}" method="post" id="store-dc-in" name='form' onsubmit="submitForm(this, event)">
        @csrf
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0">Scan DC IN</h5>
            </div>
            <div class="card-body">
                <div class="row justify-content-center align-items-end">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label label-input"><small><b>Stocker</b></small></label>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-sm border-input" name="txtqr" id="txtqr" autocomplete="off" enterkeyhint="go" onkeyup="if (event.keyCode == 13) document.getElementById('scan_qr').click()" autofocus>
                                <button class="btn btn-sm btn-primary" type="button" id="scan_qr" onclick="scanqr()">Scan</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-2">
                        <div></div>
                    </div>
                    <div class="col-8">
                        <div id="reader"></div>
                    </div>
                    <div class="col-2">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label label-input"><small><b>WS</b></small></label>
                        <div class="input-group">
                            <input type='text' class='form-control form-control-sm border-input' id='txtws' name='txtws' value='' readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label label-input"><small><b>Buyer</b></small></label>
                        <div class="input-group">
                            <input type='text' class='form-control form-control-sm border-input' id='txtbuyer' name='txtbuyer' value='' readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label label-input"><small><b>Style</b></small></label>
                        <div class="input-group">
                            <input type='text' class='form-control form-control-sm border-input' id='txtstyle' name='txtstyle' value='' readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label label-input"><small><b>Color</b></small></label>
                        <div class="input-group">
                            <input type='text' class='form-control form-control-sm border-input' id='txtcolor' name='txtcolor' value='' readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label label-input"><small><b>Shell</b></small></label>
                        <div class="input-group">
                            <input type='text' class='form-control form-control-sm border-input' id='txtshell' name='txtshell' value='' readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label label-input"><small><b>No Cut</b></small></label>
                        <div class="input-group">
                            <input type='text' class='form-control form-control-sm border-input' id='txtnocut' name='txtnocut' value='' readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label label-input"><small><b>Size</b></small></label>
                        <div class="input-group">
                            <input type='text' class='form-control form-control-sm border-input' id='txtsize' name='txtsize' value='' readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label label-input"><small><b>Group/Lot</b></small></label>
                        <div class="input-group">
                            <input type='text' class='form-control form-control-sm border-input' id='txtgroup' name='txtgroup' value='' readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label label-input"><small><b>Qty</b></small></label>
                        <div class="input-group">
                            <input type='text' class='form-control form-control-sm border-input' id='txtqtyply_h' name='txtqtyply_h' value='' readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label label-input"><small><b>Range Awal</b></small></label>
                        <div class="input-group">
                            <input type='text' class='form-control form-control-sm border-input' id='txtrange_awal' name='txtrange_awal' value='' readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label label-input"><small><b>Range Akhir</b></small></label>
                        <div class="input-group">
                            <input type='text' class='form-control form-control-sm border-input' id='txtrange_akhir' name='txtrange_akhir' value='' readonly>
                        </div>
                        <input type='hidden' class='form-control form-control-sm border-input' id='txtkode' name='txtkode' value='' readonly>
                        <input type='hidden' class='form-control form-control-sm border-input' id='txttuj_h' name='txttuj_h' value='' readonly>
                        <input type='hidden' class='form-control form-control-sm border-input' id='txtlok_h' name='txtlok_h' value='' readonly>
                        <input type='hidden' class='form-control form-control-sm border-input' id='txttempat_h' name='txttempat_h' value='' readonly>
                    </div>
                </div>

            </div>
        </div>

        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0">List Data</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable-scan" class="table table-bordered table-sm w-100 display nowrap">
                        <thead>
                            <tr>
                                <th>Stocker</th>
                                <th>Part</th>
                                <th>Tujuan</th>
                                <th>Tempat</th>
                                <th>Proses / Lokasi</th>
                                <th>Qty In</th>
                                <th>Act</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-sb btn-block">Simpan </button>
            </div>
        </div>
    </form>
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
        $(document).ready(function() {
            initScan();
            clearh();
            cleard();
            getdatatmp();
        })
    </script>
    <script>
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        $('.select2').select2();

        $('.select2bs4').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#updateTmpDcModal")
        });

        // Scan QR Module :
        // Variable List :
        var html5QrcodeScanner = null;

        // Function List :
        // -Initialize Scanner-
        async function initScan() {
            if (document.getElementById("reader")) {
                if (html5QrcodeScanner) {
                    await html5QrcodeScanner.clear();
                }

                function onScanSuccess(decodedText, decodedResult) {
                    // handle the scanned code as you like, for example:
                    console.log(`Code matched = ${decodedText}`, decodedResult);

                    // store to input text
                    // let breakDecodedText = decodedText.split('-');

                    document.getElementById('txtqr').value = decodedText;

                    scanqr();

                    html5QrcodeScanner.clear();
                }

                function onScanFailure(error) {
                    // handle scan failure, usually better to ignore and keep scanning.
                    // for example:
                    console.warn(`Code scan error = ${error}`);
                }

                html5QrcodeScanner = new Html5QrcodeScanner(
                    "reader", {
                        fps: 10,
                        qrbox: {
                            width: 250,
                            height: 250
                        }
                    },
                    /* verbose= */
                    false);

                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            }
        }

        function clearh() {
            $("#txtws").val('');
            $("#txtbuyer").val('');
            $("#txtstyle").val('');
            $("#txtcolor").val('');
            $("#txtshell").val('');
            $("#txtnocut").val('');
            $("#txtsize").val('');
            $("#txtgroup").val('');
            $("#txtqtyply_h").val('');
            $("#txtrange_awal").val('');
            $("#txtrange_akhir").val('');
            $("#txtkode").val('');
            $("#txttuj_h").val('');
            $("#txtlok_h").val('');
            $("#txttempat_h").val('');
        }

        function scanqr() {
            let txtqrstocker = document.form.txtqr.value;
            jQuery.ajax({
                url: '{{ route('show_data_header') }}',
                method: 'get',
                data: {
                    txtqrstocker: txtqrstocker
                },
                dataType: 'json',
                success: async function(response) {
                    document.getElementById('txtws').value = response.act_costing_ws;
                    document.getElementById('txtbuyer').value = response.buyer;
                    document.getElementById('txtstyle').value = response.styleno;
                    document.getElementById('txtcolor').value = response.color;
                    document.getElementById('txtshell').value = response.panel;
                    document.getElementById('txtnocut').value = response.no_cut;
                    document.getElementById('txtsize').value = response.size;
                    document.getElementById('txtgroup').value = response.grouplot;
                    document.getElementById('txtqtyply_h').value = response.qty_ply;
                    document.getElementById('txtrange_awal').value = response.range_awal;
                    document.getElementById('txtrange_akhir').value = response.range_akhir;
                    document.getElementById('txtkode').value = response.kode;
                    document.getElementById('txttuj_h').value = response.tujuan;
                    document.getElementById('txtlok_h').value = response.lokasi;
                    document.getElementById('txttempat_h').value = response.tempat;
                    let html = $.ajax({
                        type: "post",
                        url: '{{ route('insert_tmp_dc') }}',
                        data: {
                            txtqrstocker: txtqrstocker,
                            txttuj_h: document.form.txttuj_h.value,
                            txtlok_h: response.lokasi,
                            txttempat_h: response.tempat
                        },
                        success: function(response) {
                            $("#txtqr").val('');
                            getdatatmp();
                            initScan();
                        },
                        error: function(request, status, error) {
                            alert('cek');
                        },
                    });

                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        };

        function getdatatmp() {
            let datatable = $("#datatable-scan").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                destroy: true,
                paging: false,
                ajax: {
                    url: '{{ route('get_data_tmp') }}',
                    dataType: 'json',
                    dataSrc: 'data',

                },
                columns: [{
                        data: 'id_qr_stocker',
                    },
                    {
                        data: 'nama_part',
                    },
                    {
                        data: 'tujuan',
                    },
                    {
                        data: 'tempat',
                    },
                    {
                        data: 'lokasi',
                    },
                    {
                        data: 'qty_in',
                    },
                    {
                        data: 'id_qr_stocker',
                    },
                ],
                columnDefs: [{
                        targets: [6],
                        render: (data, type, row, meta) => {

                            if (row.cek_stat != 'x') {
                                return `
                            <div class='d-flex gap-1 justify-content-center'>
                                    <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#updateTmpDcModal"
                                    onclick="getdetail('` + row.id_qr_stocker + `','` + row.kode_stocker + `');">
                                        <i class='fa fa-search'></i>
                                    </a>
                                </div>
                                `;
                            } else {
                                return `
                            <div class='d-flex gap-1 justify-content-center'>
                                </div>
                                `;
                            }
                        }
                    },
                    {
                        targets: '_all',
                        render: (data, type, row, meta) => {
                            var color = '#000000';
                            if (row.cek_stat != 'x') {
                                color = '#000000';
                            } else {
                                color = '#7b7d7d';
                            }
                            return '<span style="font-weight: 600; color:' + color + '">' + data +
                                '</span>';
                        }
                    }

                ]

            });
        };

        function getdetail(id_c, id_b) {
            if (document.getElementById("loading")) {
                document.getElementById("loading").classList.remove("d-none");
            }

            jQuery.ajax({
                url: '{{ route('show_tmp_dc_in') }}',
                method: 'get',
                data: {
                    id_c: id_c
                },
                dataType: 'json',
                success: async function(response) {
                    if (document.getElementById("loading")) {
                        document.getElementById("loading").classList.add("d-none");
                    }

                    $("#updateTmpDcModalLabel").html(id_b);
                    document.getElementById('txtqty').value = response.qty_in;
                    document.getElementById('id_c').value = response.id_qr_stocker;
                    document.getElementById('txtqtyreject').value = response.qty_reject;
                    document.getElementById('txtqtyreplace').value = response.qty_replace;
                    document.getElementById('txttuj').value = response.tujuan;
                    document.getElementById('txtqtyply').value = response.qty_ply;
                    document.getElementById('txtket').value = response.ket;
                    document.getElementById('id_kode').value = response.kode;
                    gettempat();
                    $("#cbotempat").val(response.tempat).trigger('change');
                    $("#cbolokasi").val(response.lokasi).trigger('change');
                    // getlokasi();
                    // $("#updateTmpDcModalLabel").html(response.nama_stocker);
                    // // document.getElementById('cbotuj').value = response.tujuan;
                    // $("#cbotuj").val(response.tujuan).trigger('change');
                    // $("#cboalokasi").val(response.alokasi).trigger('change');
                    // // document.getElementById('cboalokasi').value = response.alokasi;
                    // $("#cbodetalokasi").val(response.det_alokasi).trigger('change');
                },
                error: function(request, status, error) {
                    if (document.getElementById("loading")) {
                        document.getElementById("loading").classList.add("d-none");
                    }

                    alert(request.responseText);
                },
            });
        };


        function gettempat() {
            let tujuan = document.form_modal.txttuj.value;
            let html = $.ajax({
                type: "get",
                url: '{{ route('get_tempat') }}',
                data: {
                    tujuan: tujuan
                },
                async: false
            }).responseText;
            if (html != "") {
                $("#cbotempat").html(html);
            }
        };

        function getlokasi() {
            let tujuan = document.form_modal.txttuj.value;
            let tempat = document.form_modal.cbotempat.value;
            let html = $.ajax({
                type: "get",
                url: '{{ route('get_lokasi') }}',
                data: {
                    tujuan: tujuan,
                    tempat: tempat
                },
                async: false
            }).responseText;
            console.log(tujuan);
            console.log(tempat);
            console.log(html != "");
            if (html != "") {
                $("#cbolokasi").html(html);
            }
        };

        function cleard() {
            let html = $.ajax({
                type: "delete",
                url: '{{ route('destroy') }}',
                async: false
            }).responseText;
        }


        function sum() {
            let txtqtyply = document.getElementById('txtqty').value;
            let txtqtyreject = document.getElementById('txtqtyreject').value;
            let txtqtyreplace = document.getElementById('txtqtyreplace').value;
            let result = parseFloat(txtqty) - parseFloat(txtqtyreject) + parseFloat(txtqtyreplace);
            let result_fix = Math.ceil(result)
            if (!isNaN(result_fix)) {
                document.getElementById("txtqtyin").value = result_fix;
            }
        }
    </script>
@endsection
