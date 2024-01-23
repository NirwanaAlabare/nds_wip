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
        <form action="{{ route('store-secondary-inhouse') }}" method="post" onsubmit="submitForm(this, event)"
            name='form' id='form'>
            @method('POST')
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5">Scan DC IN</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row ">
                            <div class="col-sm-12">
                                <div class="mb-3">
                                    <label class="form-label label-input">Scan QR Stocker</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm border-input"
                                            name="txtqrstocker" id="txtqrstocker" autocomplete="off" enterkeyhint="go"
                                            onkeyup="if (event.keyCode == 13)
                                        document.getElementById('scanqr').click()"
                                            autofocus>
                                        {{-- <input type="button" class="btn btn-sm btn-primary" value="Scan Line" /> --}}
                                        {{-- style="display: none;" --}}
                                        <button class="btn btn-sm btn-primary" type="button" id="scanqr"
                                            onclick="scan_qr()">Scan</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div></div>
                            </div>
                            <div class="col-6">
                                <div id="reader"></div>
                            </div>
                            <div class="col-3">
                            </div>
                        </div>

                        <div class="row">
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>No Stocker</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtno_stocker'
                                        name='txtno_stocker' value = '' readonly>
                                    <input type='hidden' class='form-control form-control-sm' id='txtno_form'
                                        name='txtno_form' value = '' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>WS</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtws' name='txtws'
                                        value = '' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Buyer</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtbuyer'
                                        name='txtbuyer' value = '' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>No Cut</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtno_cut'
                                        name='txtno_cut' value = '' readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class='col-sm-12'>
                                <div class="table-responsive">
                                    <table id="datatable-scan" class="table table-bordered  table-sm w-100">
                                        <thead>
                                            <tr>
                                                <th>No Stocker</th>
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
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Simpan </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card card-sb card-outline">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">DC IN <i class="fas fa-podcast"></i></h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                {{-- <button class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Baru</button> --}}
                <a href="{{ route('create-dc-in') }}" class="btn btn-primary btn-sm">
                    Baru
                    <i class="fas fa-plus"></i>
                </a>
            </div>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Awal</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Akhir</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            <h5 class="card-title fw-bold mb-0">List Transaksi DC IN</h5>
            <br>
            <br>
            <div class="table-responsive">
                <table id="datatable-input" class="table table-bordered table-striped table-sm w-100">
                    <thead>
                        <tr>
                            <th>Tgl Transaksi</th>
                            <th>ID QR</th>
                            <th>WS</th>
                            <th>Buyer</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Tujuan</th>
                            <th>Tempat</th>
                            <th>Lokasi</th>
                            <th>Qty Awal</th>
                            <th>Qty Reject</th>
                            <th>Qty Replace</th>
                            <th>Qty In</th>
                            <th>User</th>
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
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        let datatable = $("#datatable-input").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: true,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('dc-in') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'tgl_trans_fix',
                },
                {
                    data: 'id_qr_stocker',
                },
                {
                    data: 'act_costing_ws',
                },
                {
                    data: 'buyer',
                },
                {
                    data: 'style',
                },
                {
                    data: 'color',
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
                    data: 'qty_awal',
                },
                {
                    data: 'qty_reject',
                },
                {
                    data: 'qty_replace',
                },
                {
                    data: 'qty_in',
                },
                {
                    data: 'user',
                },
            ],
        });
    </script>




    <script>
        // $('.select2bs4').select2({
        //     theme: 'bootstrap4',
        //     dropdownParent: $("#editMejaModal")
        // })

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

                    document.getElementById('txtqrstocker').value = decodedText;

                    scan_qr();

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
                            width: 200,
                            height: 200
                        }
                    },
                    /* verbose= */
                    false);


                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            }
        }
    </script>
    <script>
        $(document).ready(function() {
            reset();

        })

        $('#exampleModal').on('show.bs.modal', function(e) {
            initScan();
            // $(document).on('select2:open', () => {
            //     document.querySelector('.select2-search__field').focus();
            // });
            // $('.select2').select2()
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                dropdownParent: $("#exampleModal")
            })
            $('#cbotuj').val('').trigger('change');

        })
    </script>
    <script>
        function reset() {
            $("#form").trigger("reset");
            getdatatmp();
            // initScan();

        }


        function scan_qr() {
            let txtqrstocker = document.form.txtqrstocker.value;
            let html = $.ajax({
                type: "post",
                url: '{{ route('insert_tmp_dc') }}',
                data: {
                    txtqrstocker: txtqrstocker
                },
                success: function(response) {
                    $("#txtqrstocker").val('');
                    getdatatmp();
                },
                error: function(request, status, error) {
                    alert('cek');
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
                        data: 'id_qr_stocker',
                    },
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
                        data: 'id_qr_stocker',
                    },
                    {
                        data: 'id_qr_stocker',
                    },
                ],
            });
        };


        function sum() {
            let txtqty = document.getElementById('txtqtyawal').value;
            let txtqtyreject = document.getElementById('txtqtyreject').value;
            let txtqtyreplace = document.getElementById('txtqtyreplace').value;
            document.getElementById("txtqtyin").value = +txtqty;
            let result = parseFloat(txtqty) - parseFloat(txtqtyreject) + parseFloat(txtqtyreplace);
            let result_fix = Math.ceil(result)
            if (!isNaN(result_fix)) {
                document.getElementById("txtqtyin").value = result_fix;
            }
        }
    </script>
@endsection
