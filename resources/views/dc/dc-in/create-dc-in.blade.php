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
    {{-- Mass Update Lokasi --}}
    <div class="modal fade" id="updateMassTmpDcModal" tabindex="-1" role="dialog" aria-labelledby="updateMassTmpDcModalLabel" aria-hidden="true">
        <form action="{{ route('update_mass_tmp_dc_in') }}" method="post" name='mass_form_modal' onsubmit="submitForm(this, event)">
            @method('PUT')
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <div style="max-width: 80%; max-height: 100%; overflow: auto;">
                            <h1 class="modal-title fs-5" id="updateMassTmpDcModalLabel"></h1>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class='row'>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Tujuan</small></label>
                                    <input type='text' class='form-control' name='mass_txttuj' id='mass_txttuj' value='' readonly>
                                    <input type='hidden' class='form-control' id='mass_id_c' name='mass_id_c' value=''>
                                    <input type='hidden' class='form-control' id='mass_id_kode' name='mass_id_kode' value=''>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Tempat</small></label>
                                    <select class='form-control select2bs4' style='width: 100%;' name='mass_cbotempat' id='mass_cbotempat' onchange='getmasslokasi();'></select>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Lokasi</small></label>
                                    <select class='form-control select2bs4' style='width: 100%;' name='mass_cbolokasi' id='mass_cbolokasi'></select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-success mt-3">Simpan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Update Lokasi --}}
    <div class="modal fade" id="updateTmpDcModal" tabindex="-1" role="dialog" aria-labelledby="updateTmpDcModalLabel" aria-hidden="true">
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

    {{-- Store DC In --}}
    <form action="{{ route('store-dc-in') }}" method="post" id="store-dc-in" name='form' onsubmit="submitForm(this, event)">
        @csrf
        <div class="card card-sb">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0"><i class="fa fa-qrcode fa-sm"></i> Scan DC IN</h5>
                    <a href="{{ route('dc-in') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-reply fa-sm"></i> Kembali ke DC IN
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row justify-content-center align-items-end">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label label-input"><small><b>Stocker</b></small></label>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-sm border-input" name="txtqr" id="txtqr" data-prevent-submit="true" autocomplete="off" enterkeyhint="go" autofocus>
                                <button class="btn btn-sm btn-primary" type="button" id="scan_qr" onclick="scanqr()">Scan</button>
                                <button class="btn btn-sm btn-success d-none" type="button" id="mass_scan_qr" onclick="massscanqr()">Mass Scan</button>
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
                    <div class="col-3">
                        <label class="form-label label-input"><small><b>WS</b></small></label>
                        <div class="input-group">
                            <input type='text' class='form-control form-control-sm border-input' id='txtws' name='txtws' value='' readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <label class="form-label label-input"><small><b>Buyer</b></small></label>
                        <div class="input-group">
                            <input type='text' class='form-control form-control-sm border-input' id='txtbuyer' name='txtbuyer' value='' readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <label class="form-label label-input"><small><b>Style</b></small></label>
                        <div class="input-group">
                            <input type='text' class='form-control form-control-sm border-input' id='txtstyle' name='txtstyle' value='' readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <label class="form-label label-input"><small><b>Color</b></small></label>
                        <div class="input-group">
                            <input type='text' class='form-control form-control-sm border-input' id='txtcolor' name='txtcolor' value='' readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-3">
                        <label class="form-label label-input"><small><b>Shell</b></small></label>
                        <div class="input-group">
                            <input type='text' class='form-control form-control-sm border-input' id='txtshell' name='txtshell' value='' readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <label class="form-label label-input"><small><b>No Cut</b></small></label>
                        <div class="input-group">
                            <input type='text' class='form-control form-control-sm border-input' id='txtnocut' name='txtnocut' value='' readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <label class="form-label label-input"><small><b>Size</b></small></label>
                        <div class="input-group">
                            <input type='text' class='form-control form-control-sm border-input' id='txtsize' name='txtsize' value='' readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <label class="form-label label-input"><small><b>Group/Lot</b></small></label>
                        <div class="input-group">
                            <input type='text' class='form-control form-control-sm border-input' id='txtgroup' name='txtgroup' value='' readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-3">
                        <label class="form-label label-input"><small><b>Qty</b></small></label>
                        <div class="input-group">
                            <input type='text' class='form-control form-control-sm border-input' id='txtqtyply_h' name='txtqtyply_h' value='' readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <label class="form-label label-input"><small><b>Range Awal</b></small></label>
                        <div class="input-group">
                            <input type='text' class='form-control form-control-sm border-input' id='txtrange_awal' name='txtrange_awal' value='' readonly>
                        </div>
                    </div>
                    <div class="col-3">
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
                                <th>Action</th>
                                <th>
                                    <div class="d-flex justify-content-center ms-3">
                                        <div class="form-check mt-1 mb-0">
                                            <input class="form-check-input" type="checkbox" id="check-all-stocker" onchange="checkAll(this)" style="scale: 1.5;">
                                        </div>
                                    </div>
                                </th>
                                <th>Stocker</th>
                                <th>Size</th>
                                <th>Part</th>
                                <th>Tujuan</th>
                                <th>Tempat</th>
                                <th>Proses / Lokasi</th>
                                <th>Qty In</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between">
                        <p class="my-3">
                            Selected : <span id="checked-stocker-count" class="fw-bold">0</span>
                        </p>
                        <div class="d-flex gap-3">
                            <button type="button" data-bs-toggle="modal" data-bs-target="#updateMassTmpDcModal" class="btn btn-primary btn-sm my-3" onclick="editCheckedTmpDcIn()">
                                <i class="fa fa-edit"></i> Edit Selected Stocker
                            </button>
                            <button type="button"  class="btn btn-danger btn-sm my-3" onclick="deleteCheckedTmpDcIn()">
                                <i class="fa fa-trash"></i> Delete Selected Stocker
                            </button>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-success btn-block"><i class="fa fa-save"></i> Simpan DC IN</button>
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
        if (document.getElementById("loading")) {
            document.getElementById("loading").classList.remove("d-none");
        }

        $(document).ready(async function() {
            await initScan();
            await clearh();
            // await cleard();
            await getdatatmp();

            if (document.getElementById("loading")) {
                document.getElementById("loading").classList.add("d-none");
            }
        })

        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        $('.select2').select2();

        $('#cbotempat').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#updateTmpDcModal")
        });

        $('#cbolokasi').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#updateTmpDcModal")
        });

        $('#mass_cbotempat').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#updateMassTmpDcModal")
        });

        $('#mass_cbolokasi').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#updateMassTmpDcModal")
        });

        document.getElementById("store-dc-in").onkeypress = function(e) {
            var key = e.charCode || e.keyCode || 0;
            if (key == 13) {
                e.preventDefault();

                if (e.target.getAttribute('data-prevent-submit') == "true") {
                    scanqr();
                }
            }
        }

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

        function resetCheckedStocker() {
            document.getElementById("checked-stocker-count").innerText = 0;
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
            if (document.getElementById("loading")) {
                document.getElementById("loading").classList.remove("d-none");
            }

            let txtqrstocker = document.form.txtqr.value;
            $.ajax({
                url: '{{ route('show_data_header') }}',
                method: 'get',
                data: {
                    txtqrstocker: txtqrstocker
                },
                dataType: 'json',
                success: function(response) {
                    if (response) {
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
                            url: '{{ route('insert_tmp_dc_in') }}',
                            data: {
                                txtqrstocker: txtqrstocker,
                                txttuj_h: document.form.txttuj_h.value,
                                txtlok_h: response.lokasi,
                                txttempat_h: response.tempat
                            },
                            success: function(response) {
                                if (document.getElementById("loading")) {
                                    document.getElementById("loading").classList.add("d-none");
                                }

                                $("#txtqr").val('');
                                getdatatmp();
                                initScan();
                            },
                            error: function(request, status, error) {
                                if (document.getElementById("loading")) {
                                    document.getElementById("loading").classList.add("d-none");
                                }

                                alert(request.responseText);
                            },
                        });
                    } else {
                        if (document.getElementById("loading")) {
                            document.getElementById("loading").classList.add("d-none");
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Stocker Tidak Ditemukan',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timerProgressBar: true
                        })
                    }
                },
                error: function(request, status, error) {
                    if (document.getElementById("loading")) {
                        document.getElementById("loading").classList.add("d-none");
                    }

                    alert(request.responseText);
                },
            });
        };

        function massscanqr() {
            if (document.getElementById("loading")) {
                document.getElementById("loading").classList.remove("d-none");
            }

            let txtqrstocker = document.form.txtqr.value;
            $.ajax({
                url: '{{ route('show_data_header') }}',
                method: 'get',
                data: {
                    txtqrstocker: txtqrstocker
                },
                dataType: 'json',
                success: function(response) {
                    if (response) {
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
                            url: '{{ route('mass_insert_tmp_dc_in') }}',
                            data: {
                                txtqrstocker: txtqrstocker,
                                txttuj_h: document.form.txttuj_h.value,
                                txtlok_h: response.lokasi,
                                txttempat_h: response.tempat
                            },
                            success: function(response) {
                                if (document.getElementById("loading")) {
                                    document.getElementById("loading").classList.add("d-none");
                                }

                                $("#txtqr").val('');
                                getdatatmp();
                                initScan();
                            },
                            error: function(request, status, error) {
                                if (document.getElementById("loading")) {
                                    document.getElementById("loading").classList.add("d-none");
                                }

                                alert(request.responseText);
                            },
                        });
                    } else {
                        if (document.getElementById("loading")) {
                            document.getElementById("loading").classList.add("d-none");
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Stocker Tidak Ditemukan',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timerProgressBar: true
                        })
                    }
                },
                error: function(request, status, error) {
                    if (document.getElementById("loading")) {
                        document.getElementById("loading").classList.add("d-none");
                    }

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
                info: false,
                ajax: {
                    url: '{{ route('get_tmp_dc_in') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                },
                columns: [
                    {
                        data: 'id_qr_stocker',
                    },
                    {
                        data: 'id_qr_stocker',
                    },
                    {
                        data: 'id_qr_stocker',
                    },
                    {
                        data: 'size',
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
                    }
                ],
                columnDefs: [
                    {
                        targets: [0],
                        render: (data, type, row, meta) => {

                            if (row.cek_stat != 'x') {
                                return `
                                    <div class='d-flex gap-1 justify-content-center'>
                                        <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#updateTmpDcModal" onclick="getdetail('` + row.id_qr_stocker + `','` + row.kode_stocker + `');">
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
                        targets: [1],
                        className: "text-center",
                        render: (data, type, row, meta) => {

                            if (row.cek_stat != 'x') {
                                return `
                                    <div class="d-flex justify-content-center">
                                        <div class="form-check mt-1 mb-0">
                                            <input class="form-check-input tmp_dc_stock_check" type="checkbox" data-tujuan-tempat-proses="`+row.tujuan+`-`+row.tempat+`-`+row.lokasi+`" name="tmp_dc_stock[`+meta.row+`]" id="tmp_dc_stock_`+meta.row+`" value="`+data+`" onchange="filterCheck(this)" style="scale: 1.5;">
                                        </div>
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
                            return '<span style="font-weight: 600; color:' + color + '">' + data + '</span>';
                        }
                    }
                ]
            });
        };

        $('#datatable-scan thead tr').clone(true).appendTo('#datatable-scan thead');
        $('#datatable-scan thead tr:eq(1) th').each(function(i) {
            if (i != 0 && i != 1) {
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

        function getdetail(id_c) {
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

                    $("#updateTmpDcModalLabel").html(response.id_qr_stocker);
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

        function getmasstempat() {
            let tujuan = document.mass_form_modal.mass_txttuj.value;
            let html = $.ajax({
                type: "get",
                url: '{{ route('get_tempat') }}',
                data: {
                    tujuan: tujuan
                },
                async: false
            }).responseText;
            if (html != "") {
                $("#mass_cbotempat").html(html);
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

        function getmasslokasi() {
            let tujuan = document.mass_form_modal.mass_txttuj.value;
            let tempat = document.mass_form_modal.mass_cbotempat.value;
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
                $("#mass_cbolokasi").html(html);
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

        async function checkAll(element) {
            let tmpDcStockCheck = document.getElementsByClassName('tmp_dc_stock_check');

            if (tmpDcStockCheck) {
                let checkedTmpDcStock = 0;

                if (element.checked) {
                    for (let i = 0; i < tmpDcStockCheck.length; i++) {
                        if (tmpDcStockCheck[i].getAttribute('data-tujuan-tempat-proses') == tmpDcStockCheck[0].getAttribute('data-tujuan-tempat-proses')) {
                            tmpDcStockCheck[i].checked = true;
                            tmpDcStockCheck[i].removeAttribute('disabled');

                            checkedTmpDcStock++;
                        } else {
                            tmpDcStockCheck[i].checked = false;
                            tmpDcStockCheck[i].setAttribute('disabled', true);
                        }
                    }
                } else {
                    for (let i = 0; i < tmpDcStockCheck.length; i++) {
                        tmpDcStockCheck[i].checked = false;
                        tmpDcStockCheck[i].removeAttribute('disabled');
                    }
                }

                document.getElementById('checked-stocker-count').innerText = checkedTmpDcStock;
            }
        }

        async function filterCheck(element) {
            let tmpDcStockCheck = document.getElementsByClassName('tmp_dc_stock_check');

            let checkedTmpDcStock = 0;
            for (let i = 0; i < tmpDcStockCheck.length; i++) {
                if (tmpDcStockCheck[i].checked) {
                    checkedTmpDcStock++;
                }
            }

            if (element.checked) {
                for (let i = 0; i < tmpDcStockCheck.length; i++) {
                    if (tmpDcStockCheck[i].getAttribute('data-tujuan-tempat-proses') != element.getAttribute('data-tujuan-tempat-proses')) {
                        tmpDcStockCheck[i].setAttribute('disabled', true);
                    } else {
                        tmpDcStockCheck[i].removeAttribute('disabled');
                    }
                }
            } else {
                if (checkedTmpDcStock < 1) {
                    for (let i = 0; i < tmpDcStockCheck.length; i++) {
                        tmpDcStockCheck[i].removeAttribute('disabled');
                    }
                }
            }

            document.getElementById('checked-stocker-count').innerText = checkedTmpDcStock;
        }

        function editCheckedTmpDcIn() {
            let tmpDcStockCheck = document.getElementsByClassName('tmp_dc_stock_check');

            let checkedTmpDcStock = [];
            for (let i = 0; i < tmpDcStockCheck.length; i++) {
                if (tmpDcStockCheck[i].checked) {
                    checkedTmpDcStock.push(tmpDcStockCheck[i].value);
                }
            }

            if (checkedTmpDcStock.length > 0) {
                if (document.getElementById("loading")) {
                    document.getElementById("loading").classList.remove("d-none");
                }

                $.ajax({
                    url: '{{ route('show_tmp_dc_in') }}',
                    method: 'get',
                    data: {
                        id_c: checkedTmpDcStock[0]
                    },
                    dataType: 'json',
                    success: async function(response) {
                        if (document.getElementById("loading")) {
                            document.getElementById("loading").classList.add("d-none");
                        }

                        $("#updateMassTmpDcModalLabel").html(checkedTmpDcStock.toString());
                        document.getElementById('mass_id_c').value = checkedTmpDcStock.toString();
                        document.getElementById('mass_txttuj').value = response.tujuan;
                        getmasstempat();
                        $("#mass_cbotempat").val(response.tempat).trigger('change');
                        $("#mass_cbolokasi").val(response.lokasi).trigger('change');
                    },
                    error: function(request, status, error) {
                        if (document.getElementById("loading")) {
                            document.getElementById("loading").classList.add("d-none");
                        }

                        alert(request.responseText);
                    },
                });
            } else {
                Swal.fire({
                    icon:'warning',
                    title: 'Warning',
                    html: 'Harap ceklis stocker yang akan di ubahs',
                }).then(() => {
                    $('#updateMassTmpDcModal').modal('hide');
                });
            }
        }

        function deleteCheckedTmpDcIn() {
            let tmpDcStockCheck = document.getElementsByClassName('tmp_dc_stock_check');

            let checkedTmpDcStock = [];
            for (let i = 0; i < tmpDcStockCheck.length; i++) {
                if (tmpDcStockCheck[i].checked) {
                    checkedTmpDcStock.push(tmpDcStockCheck[i].value);
                }
            }

            if (checkedTmpDcStock.length > 0) {
                Swal.fire({
                    icon:'error',
                    title: 'Hapus Data?',
                    html: 'Hapus stock dc yang dipilih',
                    showConfirmButton: true,
                    confirmButtonText: 'Hapus',
                    confirmButtonColor: 'red',
                    showCancelButton: true
                }).then(async (result) => {
                    if (document.getElementById("loading")) {
                        document.getElementById("loading").classList.remove("d-none");
                    }

                    await $.ajax({
                        url: '{{ route('delete_mass_tmp_dc_in') }}',
                        type: "delete",
                        data: {
                            ids: checkedTmpDcStock.toString()
                        },
                        dataType: "json",
                        success: function (res) {
                            Swal.fire({
                                icon: (res.status == 200 ? 'success' : 'error'),
                                title: (res.status == 200 ? 'Berhasil' : 'Gagal'),
                                html: res.message,
                            }).then((result) => {
                                getdatatmp();
                            });
                        },
                        error: function (jqXHR) {
                            console.error(jqXHR);
                        }
                    });

                    if (document.getElementById("loading")) {
                        document.getElementById("loading").classList.add("d-none");
                    }
                })
            } else {
                Swal.fire({
                    icon:'warning',
                    title: 'Warning',
                    html: 'Harap ceklis stocker yang akan di hapus',
                })
            }
        }
    </script>
@endsection
