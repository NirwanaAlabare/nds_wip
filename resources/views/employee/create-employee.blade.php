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
    <form action="{{ route('store-mut-karyawan') }}" method="post" id="store-mut-karyawan" name='form'
        onsubmit="submitMutKaryawanForm(this, event)">
        @csrf
        <div class="card card-info">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0">Perpindahan Karyawan</h5>
            </div>
            <div class="card-body">
                <div class="row justify-content-center align-items-end">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label label-input"><small><b>Line</b></small></label>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-sm border-input" name="txtline"
                                    id="txtline" autocomplete="off" enterkeyhint="go"
                                    onkeyup="if (event.keyCode == 13)
                                    document.getElementById('scan_line').click()"
                                    autofocus>
                                <button class="btn btn-sm btn-primary" type="button" id="scan_line"
                                    onclick="scanline()">Scan</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <div id="reader"></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-6">
                        <div class="mb-3">
                            <label class="form-label label-scan"><small><b>Nama Line</b></small></label>
                            <input type="text" class="form-control form-control-sm border-scan" name="nm_line"
                                id="nm_line" readonly>
                            <input type="hidden" class="form-control form-control-sm border-scan" name="nik"
                                id="nik" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-6">
                        <div class="mb-3">
                            <label class="form-label label-scan"><small><b>Jumlah Orang</b></small></label>
                            <input type="text" class="form-control form-control-sm border-scan" name="jml_org"
                                id="jml_org" readonly>
                            <input type="hidden" class="form-control form-control-sm border-scan" name="nm_karyawan"
                                id="nm_karyawan" readonly>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label label-input"><small><b>Scan NIK</b></small></label>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-sm border-input" name="txtnik"
                                    id="txtnik" autocomplete="off" enterkeyhint="go"
                                    onkeyup="if (event.keyCode == 13)
                                    document.getElementById('scan_nik').click()
                                ">
                                <button class="btn btn-sm btn-warning" type="button" id="scan_nik"
                                    onclick="scannik();">Scan</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <div id="reader_nik"></div>
                        </div>
                    </div>
                </div>
            </div>
    </form>
    <div class="card card-primary">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">List Karyawan</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-sm w-100">
                    <thead>
                        <tr>
                            <th>Tgl</th>
                            <th>Line</th>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Line Asal</th>
                            <th>Last Update</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
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
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        $('.select2').select2()

        $('.select2bs4').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#editMejaModal")
        })

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

                    document.getElementById('txtline').value = decodedText;

                    scanline();

                    html5QrcodeScanner.clear();

                    initScan1();
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


         // Scan QR Module :
        // Variable List :
        var html5QrcodeScanner1 = null;

        // Function List :
        // -Initialize Scanner-
        async function initScan1() {
            if (document.getElementById("reader_nik")) {
                if (html5QrcodeScanner1) {
                    await html5QrcodeScanner1.clear();
                }

                function onScanSuccess(decodedText, decodedResult) {
                    // handle the scanned code as you like, for example:
                    console.log(`Code matched = ${decodedText}`, decodedResult);

                    // store to input text
                    // let breakDecodedText = decodedText.split('-');

                    document.getElementById('txtnik').value = decodedText;

                    scannik();

                    // html5QrcodeScanner1.clear();
                }

                function onScanFailure(error) {
                    // handle scan failure, usually better to ignore and keep scanning.
                    // for example:
                    console.warn(`Code scan error = ${error}`);
                }

                html5QrcodeScanner1 = new Html5QrcodeScanner(
                    "reader_nik", {
                        fps: 10,
                        qrbox: {
                            width: 250,
                            height: 250
                        }
                    },
                    /* verbose= */
                    false);

                    html5QrcodeScanner1.render(onScanSuccess, onScanFailure);
            }
        }



        // // -Trigger Next Lap Button on Key Up 'Enter'-
        // scan_nik.addEventListener("keyup", function(evt) {
        //     if (evt.key === "Enter") {
        //         // Cancel the default action, if needed
        //         event.preventDefault();
        //         // Trigger the button element with a click
        //         document.getElementById('scan_nik').click();
        //     }
        // });

        // var x = document.getElementById("txtline");
        // x.addEventListener('keyup', function(e) {
        //     if (e.keyCode === 13) {
        //         document.getElementById('scan_line').click();
        //     }
        // }, false);

        // $('#inSku').keyup(function(e) {
        //     alert('Key pressed: ' + e.keyCode);
        // });
    </script>

    <script>
        $(document).ready(function() {
            $("#nm_line").val('');
            $("#jml_org").val('');
            initScan();

        })

        $(document).ready(function() {
            if (window.location.reload) {
                dataTableReload();
            }
        });


        window.addEventListener("focus", () => {
            dataTableReload();
        });

        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            info: false,
            paging: false,
            ajax: {
                url: '{{ route('getdatalinekaryawan') }}',
                data: function(d) {
                    d.nm_line = $('#nm_line').val();
                },
            },
            columns: [{
                    data: 'tgl_pindah'
                },
                {
                    data: 'line'
                },
                {
                    data: 'nik'
                },
                {
                    data: 'nm_karyawan'
                },
                {
                    data: 'line_asal'
                },
                {
                    data: 'updated_at'
                }
            ]
        });

        function scanline() {
            let txtline = document.form.txtline.value;
            let html = $.ajax({
                type: "get",
                url: '{{ route('getdataline') }}',
                data: {
                    txtline: txtline
                },
                dataType: 'json',
                success: function(response) {
                    document.getElementById('nm_line').value = response.nm_line;
                    // document.getElementById('jml_org').value = response.urutan;
                    $("#txtline").prop("readonly", true);
                    document.getElementById('txtnik').focus();
                    gettotal();
                    // updatelist();
                    // Reload Order Qty Datatable
                    datatable.ajax.reload();
                },
                error: function(request, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Data Line Tidak Terdaftar',
                        showCancelButton: false,
                        timer: 1000,
                        timerProgressBar: true
                    })
                    // alert(request.responseText);
                },
            });
        };

        function gettotal() {
            let nm_line = $("#nm_line").val();
            let html = $.ajax({
                type: "get",
                url: '{{ route('gettotal') }}',
                data: {
                    nm_line: nm_line
                },
                dataType: 'json',
                success: function(response) {
                    document.getElementById('jml_org').value = response.total;
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        };


        function scannik() {
            let nm_line = $("#nm_line").val();
            let txtnik = document.form.txtnik.value;
            let html = $.ajax({
                type: "get",
                url: '{{ route('getdatanik') }}',
                data: {
                    txtnik: txtnik
                },
                dataType: 'json',
                success: function(response) {
                    document.getElementById('nik').value = response.employee_id;
                    document.getElementById('nm_karyawan').value = response.employee_name;
                    let nm_karyawan = $("#nm_karyawan").val();
                    $.ajax({
                        type: "post",
                        url: '{{ route('store-mut-karyawan') }}',
                        data: {
                            txtnik: txtnik,
                            nm_line: nm_line,
                            nm_karyawan: nm_karyawan
                        },
                        success: async function(res) {
                            await Swal.fire({
                                icon: res.icon,
                                title: res.msg,
                                html: "NIK : " + txtnik + "<br/>" +
                                    "Nama :" + response.employee_name,
                                // html: "NIK :" + $("#txtnik").val(),
                                showCancelButton: false,
                                showConfirmButton: true,
                                timer: res.timer,
                                timerProgressBar: res.prog
                            })

                            document.getElementById('txtnik').focus();
                            datatable.ajax.reload();
                            gettotal();
                            $("#nik").val('');
                            $("#nm_karyawan").val('');
                            $("#txtnik").val('');

                        }
                    });
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        };

        function dataTableReload() {
            datatable.ajax.reload();
        }
    </script>
@endsection
