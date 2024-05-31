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
                        <h1 class="modal-title fs-5">Scan QR Secondary Dalam</h1>
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
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Style</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtstyle'
                                        name='txtstyle' value = '' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Color</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtcolor'
                                        name='txtcolor' value = '' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Size</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtsize'
                                        name='txtsize' value = '' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Part</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtpart'
                                        name='txtpart' value = '' readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class='col-sm-6'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Tujuan Asal</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txttujuan'
                                        name='txttujuan' value = '' readonly>
                                </div>
                            </div>
                            <div class='col-sm-6'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Lokasi Asal</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtalokasi'
                                        name='txtalokasi' value = '' readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Qty Awal</small></label>
                                    <input type='number' class='form-control form-control-sm' id='txtqtyawal'
                                        name='txtqtyawal' value = '' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Reject</small></label>
                                    <input type='number' class='form-control form-control-sm' id='txtqtyreject'
                                        name='txtqtyreject' value = '' oninput='sum();'
                                        style = 'border-color:blue;'>
                                </div>
                            </div>

                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Replacement</small></label>
                                    <input type='number' class='form-control form-control-sm' id='txtqtyreplace'
                                        name='txtqtyreplace' value = '0' oninput='sum();'
                                        style = 'border-color:blue;'>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Qty In</small></label>
                                    <input type='number' class='form-control form-control-sm' id='txtqtyin'
                                        name='txtqtyin' value = '' readonly style = 'border-color:green;'>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            {{-- <div class='col-md-6'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Rak</small></label>
                                    <select class="form-control select2bs4" name="cborak" id="cborak"
                                        style="width: 100%;">
                                        <option selected="selected" value="">Pilih Rak Tujuan</option>
                                        @foreach ($data_rak as $datarak)
                                            <option value="{{ $datarak->isi }}">
                                                {{ $datarak->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> --}}
                            <div class='col-md-12'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Keterangan</small></label>
                                    <textarea class="form-control" rows="2" id='txtket' name='txtket' style = 'border-color:blue;'
                                        autocomplete="off"></textarea>
                                    {{-- <input type='text' class='form-control' id='txtket' name='txtket'
                                        value = '' style = 'border-color:blue;' autocomplete="off"> --}}
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

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">Secondary Dalam <i class="fas fa-house-user"></i></h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Awal</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" onchange="datatableReload()">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Akhir</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}" onchange="datatableReload()">
                </div>
                <div class="mb-3">
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="reset();"><i class="fas fa-plus"></i> Baru</button>
                </div>
            </div>

            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <button class="btn btn-info btn-sm" onclick="list();" id="list" name="list"><i class="fas fa-list"></i> List</button>
                </div>
                <div class="mb-3">
                    <button class="btn btn-secondary btn-sm" onclick="detail();" id="detail" name="detail"><i class="fas fa-list"></i> Detail</button>
                </div>
            </div>

            <h5 class="card-title fw-bold mb-0" id="judul" name="judul">List Transaksi Inhouse / Dalam</h5>
            <br>
            <br>
            <div class="table-responsive" id = "show_datatable_input">
                <table id="datatable-input" class="table table-bordered table-striped table-sm w-100">
                    <thead>
                        <tr>
                            <th>Tgl Transaksi</th>
                            <th>ID QR</th>
                            <th>WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Part</th>
                            <th>Size</th>
                            <th>No. Cut</th>
                            <th>Tujuan Asal</th>
                            <th>Lokasi Asal</th>
                            <th>Qty Awal</th>
                            <th>Qty Reject</th>
                            <th>Qty Replace</th>
                            <th>Qty In</th>
                            <th>Buyer</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

            <div class="table-responsive" id = "show_datatable_detail">
                <table id="datatable-detail" class="table table-bordered table-striped table-sm w-100">
                    <thead>
                        <tr>
                            <th>WS</th>
                            <th>Buyer</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Out</th>
                            <th>In</th>
                            <th>Balance</th>
                            <th>Proses</th>
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
            destroy: true,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('secondary-inhouse') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [
                {
                    data: 'tgl_trans_fix',
                },
                {
                    data: 'id_qr_stocker',
                },
                {
                    data: 'act_costing_ws',
                },
                {
                    data: 'style',
                },
                {
                    data: 'color',
                },
                {
                    data: 'nama_part',
                },
                {
                    data: 'size',
                },
                {
                    data: 'no_cut',
                },
                {
                    data: 'tujuan',
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
                    data: 'buyer',
                },
                {
                    data: 'user',
                },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap"
                }
            ]
        });

        $('#datatable-input thead tr').clone(true).appendTo('#datatable-input thead');
        $('#datatable-input thead tr:eq(1) th').each(function(i) {
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

        function datatableReload() {
            $('#datatable-input').DataTable().ajax.reload();
        }

        let datatable_detail = $("#datatable-detail").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: true,
            destroy: true,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('detail_stocker_inhouse') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [
                {
                    data: 'act_costing_ws',
                },
                {
                    data: 'buyer',
                },
                {
                    data: 'styleno',
                },
                {
                    data: 'color',
                },
                {
                    data: 'qty_out',
                },
                {
                    data: 'qty_in',
                },
                {
                    data: 'balance',
                },
                {
                    data: 'lokasi',
                },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap"
                }
            ]
        });

        $('#datatable-detail thead tr').clone(true).appendTo('#datatable-detail thead');
        $('#datatable-detail thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm"/>');

            $('input', this).on('keyup change', function() {
                if (datatable_detail.column(i).search() !== this.value) {
                    datatable_detail
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
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
            list();
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

        $('#exampleModal').on('shown.bs.modal', function(e) {
            $('#txtqrstocker').focus();
        })
    </script>
    <script>
        function reset() {
            $("#form").trigger("reset");
            // initScan();

        }

        function scan_qr() {
            let txtqrstocker = document.form.txtqrstocker.value;
            let html = $.ajax({
                type: "get",
                url: '{{ route('cek_data_stocker_inhouse') }}',
                data: {
                    txtqrstocker: txtqrstocker
                },
                dataType: 'json',
                success: function(response) {
                    document.getElementById('txtno_stocker').value = response.id_qr_stocker;
                    document.getElementById('txtno_form').value = response.no_form;
                    document.getElementById('txtws').value = response.act_costing_ws;
                    document.getElementById('txtbuyer').value = response.buyer;
                    document.getElementById('txtno_cut').value = response.no_cut;
                    document.getElementById('txtstyle').value = response.style;
                    document.getElementById('txtcolor').value = response.color;
                    document.getElementById('txtsize').value = response.size;
                    document.getElementById('txtpart').value = response.nama_part;
                    document.getElementById('txttujuan').value = response.tujuan;
                    document.getElementById('txtalokasi').value = response.lokasi;
                    document.getElementById('txtqtyawal').value = response.qty_awal;
                    // let txtqtyreject = $("#txtqtyreject").val();
                    // let txtqtyreplace = $("#txtqtyreplace").val();
                    // let txtqtyin = $("#txtqtyin").val();
                    // let cborak = $("#cborak").val();
                    // let ket = $("#txtket").val();
                    // $.ajax({
                    //     type: "post",
                    //     url: '{{-- route('store-mut-karyawan') --}}',
                    //     data: {
                    //         txtenroll_id: txtenroll_id,
                    //         nm_line: nm_line,
                    //         nik: nik,
                    //         nm_karyawan: nm_karyawan
                    //     },
                    //     success: async function(res) {
                    //         await Swal.fire({
                    //             icon: res.icon,
                    //             title: res.msg,
                    //             html: "NIK : " + response.nik + "<br/>" +
                    //                 "Nama :" + response.employee_name,
                    //             // html: "NIK :" + $("#txtnik").val(),
                    //             showCancelButton: false,
                    //             showConfirmButton: true,
                    //             timer: res.timer,
                    //             timerProgressBar: res.prog
                    //         })
                    //         document.getElementById('txtenroll_id').focus();
                    //         datatable.ajax.reload();
                    //         gettotal();
                    //         $("#nik").val('');
                    //         $("#nm_karyawan").val('');
                    //         $("#txtenroll_id").val('');
                    //         initScan1();
                    //     }
                    // });

                },
                error: function(request, status, error) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Tidak Ada',
                        showConfirmButton: true,
                    })
                },
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


        function list() {
            document.getElementById("judul").textContent = "List Transaksi Inhouse / Dalam";
            document.getElementById("show_datatable_input").style.display = 'block';
            document.getElementById("show_datatable_detail").style.display = 'none';
        }

        function detail() {
            document.getElementById("judul").textContent = "Detail Transaksi Inhouse / Dalam";
            document.getElementById("show_datatable_input").style.display = 'none';
            document.getElementById("show_datatable_detail").style.display = 'block';
        }
    </script>
@endsection
