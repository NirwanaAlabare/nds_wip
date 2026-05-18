@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-sb fw-bold">Alokasi Rak</h5>
        <a href="{{ route('stock-rack') }}" class="btn btn-sb-secondary btn-sm">
            <i class="fas fa-reply"></i> Kembali ke Stok Rak
        </a>
    </div>
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">Scan Rak</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body" style="display: block" id="scan-rack">
            <div id="rack-reader" onclick="clearRackScan()"></div>
            <div class="my-3">
                <label class="form-label">List Rak</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="rack" name="rack" list="list_lokasi" autocomplete="off">
                    <datalist id="list_lokasi">
                        @foreach ($racks as $rack)
                            @foreach ($rack->rackDetails as $rackDetail)
                                <option
                                    value="{{ $rackDetail->nama_detail_rak }}"
                                    data-id="{{ $rackDetail->id }}">
                                </option>
                            @endforeach
                        @endforeach
                    </datalist>

                    <input type="hidden" id="rack_id">

                    {{-- <select class="form-select select2bs4" name="rack" id="rack">
                        <option value="">Pilih Rak</option>
                        @foreach ($racks as $rack)
                            @foreach ($rack->rackDetails as $rackDetail)
                                <option value="{{ $rackDetail->id }}">{{ $rackDetail->nama_detail_rak }}</option>
                            @endforeach
                        @endforeach
                    </select> --}}
                    <button class="btn btn-sm btn-outline-primary" type="button" onclick="refreshRackScan()">Scan</button>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-primary" id="scan-stocker">
        <div class="card-header">
            <h5 class="card-title fw-bold">Scan Stocker</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="stocker-reader" onclick="clearStockerScan()"></div>
            <div class="mb-3">
                <label class="form-label">Stocker</label>
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm" name="kode_stocker" id="kode_stocker">
                    <button class="btn btn-sm btn-outline-success" type="button" onclick="addRackStockerScan()">Get</button>
                    <button class="btn btn-sm btn-outline-primary" type="button" onclick="initStockerScan()">Scan</button>
                </div>
            </div>
            <div class="row g-1 align-items-end">
                <input type="hidden" id="stocker_id" name="stocker_id" readonly>
                <div class="col-md-3">
                    <label class="form-label"><small>Stocker</small></label>
                    <input type="text" class="form-control form-control-sm" id="stocker_kode" name="stocker_kode" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><small>WS</small></label>
                    <input type="text" class="form-control form-control-sm" id="stocker_ws" name="stocker_ws" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><small>Style</small></label>
                    <input type="text" class="form-control form-control-sm" id="stocker_style" name="stocker_style" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><small>Color</small></label>
                    <input type="text" class="form-control form-control-sm" id="stocker_color" name="stocker_color" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><small>Size</small></label>
                    <input type="text" class="form-control form-control-sm" id="stocker_size" name="stocker_size" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><small>Qty</small></label>
                    <input type="text" class="form-control form-control-sm" id="stocker_qty" name="stocker_qty" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><small>Range</small></label>
                    <input type="text" class="form-control form-control-sm" id="stocker_range" name="stocker_range" readonly>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-sm btn-success btn-block" onclick="addRackStocker()"><i class="fa fa-save"></i> Simpan</button>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-success" id="stock-rack">
        <div class="card-header">
            <h5 class="card-title fw-bold">Stock Rak</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table table-bordered" id="rack-stock-datatable">
                    <thead>
                        <tr>
                            {{-- <th>Act</th> --}}
                            <th>No. Rak</th>
                            <th>No. Stocker</th>
                            <th>No. WS</th>
                            <th>No. Cut</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Part</th>
                            <th>Size</th>
                            <th>Tgl Scan</th>
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
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2()

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        });

        $(document).ready(async () => {
            // $('#rack').val("").trigger("change");
            // $('#kode_stocker').val("").trigger("change");
            $('#rack').focus();

            await initRackScan();
            // await initStockerScan();

            // $('#scan-stocker').CardWidget('collapse');
            // $('#stock-rack').CardWidget('collapse');
        });

        var step = "";
        var currentRack = "";
        var currentStocker = "";

        // Scan QR Module :
            // Variable List :
                var rackScanner = new Html5Qrcode("rack-reader");
                var rackScannerInitialized = false;

                var stockerScanner = new Html5Qrcode("stocker-reader");
                var stockerScannerInitialized = false;

            // Function List :
                // -Initialize Rack Scanner-
                    async function initRackScan() {
                        if (document.getElementById("rack-reader")) {
                            if (rackScannerInitialized == false && stockerScannerInitialized == false) {
                                if (rackScanner == null || (rackScanner && (rackScanner.getState() && rackScanner.getState() != 2))) {
                                    const rackScanSuccessCallback = (decodedText, decodedResult) => {
                                            // handle the scanned code as you like, for example:
                                        console.log(`Code matched = ${decodedText}`, decodedResult);

                                        // store to input text
                                        let breakDecodedText = decodedText.split('-');

                                        $('#rack').val(breakDecodedText[0]).trigger('change');

                                        getScannedRack(breakDecodedText[0]);

                                        clearRackScan();

                                        // $('#scan-stocker').CardWidget('expand');
                                        // $('#stock-rack').CardWidget('expand');

                                        setTimeout(() => {
                                            $('#kode_stocker').val('').focus();

                                            initStockerScan();
                                        }, 500);
                                    };
                                    const rackScanConfig = { fps: 10, qrbox: { width: 250, height: 250 } };

                                    // If you want to prefer front camera
                                    await rackScanner.start({ facingMode: "environment" }, rackScanConfig, rackScanSuccessCallback);

                                    rackScannerInitialized = true;
                                }
                            }
                        }
                    }

                    async function clearRackScan() {
                        if (rackScannerInitialized) {
                            if (rackScanner && (rackScanner.getState() && rackScanner.getState() != 1)) {
                                await rackScanner.stop();
                                await rackScanner.clear();
                            }

                            rackScannerInitialized = false;
                        }
                    }

                    async function refreshRackScan() {
                        await clearRackScan();
                        await initRackScan();
                    }

                // -Initialize Stocker Scanner-
                    async function initStockerScan() {
                        if (document.getElementById("stocker-reader")) {
                            if (stockerScannerInitialized == false && rackScannerInitialized == false) {
                                if (stockerScanner == null || (stockerScanner && (stockerScanner.getState() && stockerScanner.getState() != 2))) {
                                    const stockerScanSuccessCallback = (decodedText, decodedResult) => {
                                            // handle the scanned code as you like, for example:
                                        console.log(`Code matched = ${decodedText}`, decodedResult);

                                        // store to input text
                                        let breakDecodedText = decodedText.split('-');

                                        $('#kode_stocker').val(decodedText).trigger('change');

                                        // getScannedStocker(decodedText);
                                        addRackStockerScan(decodedText);

                                        clearStockerScan();

                                        setTimeout(() => {
                                            $('#kode_stocker').val('').focus();
                                            initStockerScan();
                                        }, 500);
                                    };
                                    const stockerScanConfig = { fps: 10, qrbox: { width: 250, height: 250 } };

                                    // If you want to prefer front camera
                                    await stockerScanner.start({ facingMode: "environment" }, stockerScanConfig, stockerScanSuccessCallback);

                                    stockerScannerInitialized = true;
                                }
                            }
                        }
                    }

                    async function clearStockerScan() {
                        if (stockerScannerInitialized) {
                            if (stockerScanner && (stockerScanner.getState() && stockerScanner.getState() != 1)) {
                                await stockerScanner.stop();
                                await stockerScanner.clear();
                            }

                            stockerScannerInitialized = false;
                        }
                    }

                    async function refreshStockerScan() {
                        await clearStockerScan();
                        await initStockerScan();
                    }

            // Get Scanned Rack
            function getScannedRack(id) {
                if (id) {
                    $.ajax({
                        url: "{{ route("get-scanned-rack-detail") }}",
                        type: "get",
                        data: {
                            id : id
                        },
                        dataType: "json",
                        success: function (response) {
                            console.log(response);

                            $('#rack').val(response.id).trigger("change");
                        },
                        error: function (jqXHR) {
                            console.error(jqXHR);
                        }
                    });
                }
            }

            // Get Scanned Stocker
            function getScannedStocker(id) {
                if (!id) {
                    id = $("#kode_stocker").val();
                }

                if (id) {
                    $.ajax({
                        url: "{{ route("get-stocker") }}",
                        type: "get",
                        data: {
                            stocker : id
                        },
                        dataType: "json",
                        success: function (response) {
                            console.log(response);

                            $('#kode_stocker').val(response.id_qr_stocker).trigger("change");

                            $('#stocker_kode').val(response.id_qr_stocker);
                            $('#stocker_ws').val(response.act_costing_ws);
                            $('#stocker_style').val(response.style);
                            $('#stocker_color').val(response.color);
                            $('#stocker_size').val(response.size);
                            $('#stocker_qty').val(response.qty);
                            $('#stocker_range').val(response.range_awal+" - "+response.range_akhir);
                        },
                        error: function (jqXHR) {
                            console.error(jqXHR);
                        }
                    });
                }
            }

        // Rack Stocker Transaction
        function addRackStocker() {
            if ($("#stocker_kode").val() && $("#rack").val()) {
                Swal.fire({
                    icon: 'info',
                    title: "Konfirmasi",
                    html: "Stocker "+$("#stocker_kode").val()+" ke "+$("#rack").val()+"",
                    showCancelButton: true,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route("store-rack-stock") }}",
                            type: "post",
                            data: {
                                rack_detail_id: $("#rack_id").val(),
                                stocker_kode: $("#stocker_kode").val(),
                                qty_in: $("#stocker_qty").val(),
                            },
                            dataType: "json",
                            success: function (response) {
                                if (response) {
                                    if (response.status == 200) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: response.message,
                                            showCancelButton: false,
                                            showConfirmButton: true,
                                            confirmButtonText: 'Oke',
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: response.message,
                                            showCancelButton: false,
                                            showConfirmButton: true,
                                            confirmButtonText: 'Oke',
                                        });
                                    }
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: response.message,
                                        showCancelButton: false,
                                        showConfirmButton: true,
                                        confirmButtonText: 'Oke',
                                    });
                                }

                                if (response.table != '') {
                                    $('#' + response.table).DataTable().ajax.reload();
                                }
                            },
                            error: function (jqXHR) {
                                console.error(jqXHR);
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: "Alokasi stocker dibatalkan.",
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: "Harap scan rak dan stocker",
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                });
            }
        }

        // function addRackStockerScan(kode_stocker = null) {

        //     if (!kode_stocker) {
        //         kode_stocker = $('#kode_stocker').val();
        //     }

        //     if (!kode_stocker) {
        //         Swal.fire({
        //             icon: 'error',
        //             title: 'Stocker kosong',
        //             showConfirmButton: true,
        //         });

        //         return;
        //     }

        //     $.ajax({
        //         url: "{{ route('get-stocker') }}",
        //         type: "get",
        //         data: {
        //             stocker: kode_stocker
        //         },
        //         dataType: "json",
        //         success: function(stockerResponse) {

        //             $.ajax({
        //                 url: "{{ route('store-rack-stock') }}",
        //                 type: "post",
        //                 data: {
        //                     rack_detail_id: $("#rack_id").val(),
        //                     stocker_kode: stockerResponse.id_qr_stocker,
        //                     qty_in: stockerResponse.qty,
        //                 },
        //                 dataType: "json",
        //                 success: function(response) {
        //                     console.log(response)

        //                     if (response.status == 200) {

        //                         Swal.fire({
        //                             icon: 'success',
        //                             title: response.message,
        //                             timer: 1000,
        //                             showCancelButton: false,
        //                             showConfirmButton: true,
        //                             confirmButtonText: 'Oke',
        //                         }).then(() => {
        //                             $('#kode_stocker').val('').focus();
        //                         });

        //                         $('#kode_stocker').val('').focus();

        //                     } else {

        //                         Swal.fire({
        //                             icon: 'error',
        //                             title: response.message,
        //                             timer: 1000,
        //                             showCancelButton: false,
        //                             showConfirmButton: true,
        //                         });
        //                     }

        //                     if (response.table != '') {
        //                         $('#' + response.table).DataTable().ajax.reload();
        //                     }

        //                     rackStockTableReload();
        //                     $('#loading').addClass('d-none');
        //                 },
        //                 error: function(jqXHR) {
        //                     console.error(jqXHR);
        //                     Swal.fire({
        //                             icon: 'error',
        //                             title: 'Stocker ' + kode_stocker +' tidak valid',
        //                             showConfirmButton: true,
        //                         });

        //                     $('#loading').addClass('d-none');
        //                 }
        //             });

        //         },
        //         error: function(jqXHR) {
        //             console.error(jqXHR);

        //             $('#loading').addClass('d-none');
        //         }
        //     });
        // }

        // function addRackStockerScan(kode_stocker = null) {

        //     if (!kode_stocker) {
        //         kode_stocker = $('#kode_stocker').val().trim();
        //     }

        //     if (!kode_stocker) {
        //         Swal.fire({
        //             icon: 'error',
        //             title: 'Stocker kosong',
        //             showConfirmButton: true,
        //         });

        //         return;
        //     }

        //     $('#loading').removeClass('d-none');

        //     $.ajax({
        //         url: "{{ route('store-rack-stock') }}",
        //         type: "POST",
        //         data: {
        //             rack_detail_id: $('#rack_id').val(),
        //             stocker_kode: kode_stocker,
        //         },
        //         dataType: "json",
        //         success: function(response) {

        //             if (response.status == 200) {

        //                 $('#kode_stocker').val('').focus();

        //                 Swal.fire({
        //                     icon: 'success',
        //                     title: response.message,
        //                     timer: 800,
        //                     showConfirmButton: false,
        //                 });

        //             } else {

        //                 Swal.fire({
        //                     icon: 'error',
        //                     title: response.message,
        //                     showConfirmButton: true,
        //                 });
        //             }

        //             if (response.table) {
        //                 $('#' + response.table)
        //                     .DataTable()
        //                     .ajax.reload(null, false);
        //             }

        //             rackStockTableReload();
        //         },
        //         error: function(jqXHR) {
        //             let message = 'Terjadi kesalahan';

        //             if (jqXHR.responseJSON?.errors) {
        //                 const errors = jqXHR.responseJSON.errors;
        //                 message = Object.values(errors)[0][0];
        //             }

        //             Swal.fire({
        //                 icon: 'error',
        //                 title: message,
        //                 timer: 800,
        //                 showConfirmButton: true,
        //             });
        //         },
        //         complete: function() {
        //             $('#loading').addClass('d-none');
        //         }
        //     });
        // }

        async function addRackStockerScan(kodeStocker = '') {

            kodeStocker = kodeStocker || $('#kode_stocker').val().trim();

            if (!kodeStocker) {
                return Swal.fire({
                    icon: 'error',
                    title: 'Stocker kosong',
                });
            }

            const rackId = $('#rack_id').val();

            if (!rackId) {
                return Swal.fire({
                    icon: 'error',
                    title: 'Rack belum dipilih',
                });
            }

            $('#loading').removeClass('d-none');

            try {

                const response = await $.ajax({
                    url: "{{ route('store-rack-stock') }}",
                    type: "POST",
                    dataType: "json",
                    data: {
                        rack_detail_id: rackId,
                        stocker_kode: kodeStocker,
                    }
                });

                if (response.status === 200) {

                    $('#kode_stocker')
                        .val('')
                        .focus();

                    Swal.fire({
                        icon: 'success',
                        title: response.message,
                        timer: 800,
                        showConfirmButton: false,
                    });

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: response.message,
                    });
                }

                if (response.table) {
                    $('#' + response.table)
                        .DataTable()
                        .ajax.reload(null, false);
                }

                rackStockTableReload();

            } catch (jqXHR) {

                let message = 'Terjadi kesalahan';

                if (jqXHR.responseJSON?.message) {
                    message = jqXHR.responseJSON.message;
                }

                if (jqXHR.responseJSON?.errors) {
                    message = Object.values(jqXHR.responseJSON.errors)[0][0];
                }

                Swal.fire({
                    icon: 'error',
                    title: message,
                });

            } finally {

                $('#loading').addClass('d-none');
            }
        }

        function deleteRackStocker(id) {
            Swal.fire({
                icon: 'info',
                title: "Konfirmasi",
                html: "Hapus Stok Rak?",
                showCancelButton: true,
                showConfirmButton: true,
                confirmButtonText: 'Oke',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route("destroy-rack-stock") }}/"+id,
                        type: "delete",
                        dataType: "json",
                        success: function (response) {
                            if (response && response.status == '200') {
                                Swal.fire({
                                    icon: 'success',
                                    title: "Stok Rak berhasil dihapus.",
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: "Stok Rak gagal dihapus.",
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                });
                            }

                            if (response.table != '') {
                                $('#' + response.table).DataTable().ajax.reload();
                            }
                        },
                        error: function (jqXHR) {
                            console.error(jqXHR);
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: "Hapus stok rak dibatalkan.",
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                    });
                }
            })
        }

        // Datatable
            // $('#rack').on('change', function () {
            //     rackStockTableReload();
            //     console.log($("#rack").val())
            // });

            // $('#kode_stocker').on('keypress', function(e) {
            //     if (e.key === 'Enter') {
            //         e.preventDefault();

            //         let value = $(this).val().trim();

            //         if (!value) return;

            //         $('#loading').removeClass('d-none');
            //         $('#kode_stocker').val('').focus();

            //         addRackStockerScan(value);
            //         rackStockTableReload();
            //     }
            // });

            document.getElementById('kode_stocker').addEventListener('keydown', function(evt) {
                if (evt.keyCode === 13) {
                    evt.preventDefault();

                    let value = this.value.trim();

                    if (!value) return;

                    document.getElementById('loading').classList.remove('d-none');

                    this.value = '';
                    this.focus();

                    addRackStockerScan(value);
                    // rackStockTableReload();
                }
            });

            function setRackId() {
                let value = $('#rack').val();
                let id = '';

                $('#list_lokasi option').each(function () {
                    if ($(this).val() == value) {
                        id = $(this).data('id');
                    }
                });

                $('#rack_id').val(id);

                return id;
            }

            // $('#rack').on('keypress', function(e) {
            //     if (e.key === 'Enter') {
            //         e.preventDefault();

            //         let value = $(this).val().trim();

            //         if (!value) return;

            //         let rackId = setRackId();

            //         if (!rackId) {

            //             Swal.fire({
            //                 icon: 'error',
            //                 title: 'Rak tidak ditemukan',
            //                 text: 'Rak tidak ada di daftar',
            //                 showConfirmButton: true,
            //             });

            //             $('#rack').val('').focus();

            //             return;
            //         }

            //         $('#kode_stocker').focus();

            //         rackStockTableReload();
            //     }
            // });

            document.getElementById('rack').addEventListener('keydown', function(evt) {
                if (evt.keyCode === 13) {
                    evt.preventDefault();

                    let value = this.value.trim();

                    if (!value) return;

                    let rackId = setRackId();

                    if (!rackId) {

                        Swal.fire({
                            icon: 'error',
                            title: 'Rak '+ value + ' Tidak ditemukan',
                            timer: 1000,
                            showCancelButton: false,
                            showConfirmButton: true,
                        });

                        this.value = '';
                        this.focus();

                        return;
                    }

                    document.getElementById('kode_stocker').focus();

                    rackStockTableReload();
                }
            });

            $('#rack').on('change', function () {

                $('#kode_stocker').focus();

                setRackId();
                rackStockTableReload();
            });

            function rackStockTableReload() {
                $("#rack-stock-datatable").DataTable().ajax.reload();
            }

            var rackStockTable = $("#rack-stock-datatable").DataTable({
                ordering: false,
                processing: true,
                serverSide: false,
                deferRender: true,
                ajax: {
                    url: '{{ route('current-rack-stock') }}',
                    data: function (d) {
                        d.id = $("#rack_id").val();
                    },
                },
                columns: [
                    // {
                    //     data: 'id'
                    // },
                    {
                        data: 'no_rak',
                    },
                    {
                        data: 'no_stocker',
                    },
                    {
                        data: 'no_ws'
                    },
                    {
                        data: 'no_cut'
                    },
                    {
                        data: 'style'
                    },
                    {
                        data: 'color'
                    },
                    {
                        data: 'part'
                    },
                    {
                        data: 'size'
                    },
                    {
                        data: 'updated_at',
                        render: function (data, type, row) {
                            if (!data) return '-';

                            let date = new Date(data);

                            let day = String(date.getDate()).padStart(2, '0');
                            let month = String(date.getMonth() + 1).padStart(2, '0');
                            let year = date.getFullYear();

                            let hours = String(date.getHours()).padStart(2, '0');
                            let minutes = String(date.getMinutes()).padStart(2, '0');
                            let seconds = String(date.getSeconds()).padStart(2, '0');

                            return `${day}-${month}-${year} ${hours}:${minutes}:${seconds}`;
                        }
                    }
                ],
                rowCallback: function(row, data) {
                    let updatedAt = new Date(data.updated_at);

                    let today = new Date();
                    let yesterday = new Date();

                    yesterday.setDate(today.getDate() - 1);

                    today.setHours(23,59,59,999);
                    yesterday.setHours(0,0,0,0);

                    if (updatedAt >= yesterday && updatedAt <= today) {
                        $(row).css('background-color', '#ffeeba');
                    }
                },
                // columnDefs: [
                //     {
                //         targets: "_all",
                //         className: "text-nowrap",
                //     },
                //     {
                //         targets: [0],
                //         render: (data, type, row, meta) => {
                //             return `
                //                 <button class="btn btn-danger btn-sm" onclick="deleteRackStocker('`+data+`')">
                //                     <i class="fa fa-trash"></i>
                //                 </button>
                //             `;
                //         }
                //     },
                // ]
            });
    </script>
@endsection
