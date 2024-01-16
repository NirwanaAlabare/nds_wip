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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-sb fw-bold">{{ $trolley->nama_trolley }}</h5>
        <a href="{{ route('trolley') }}" class="btn btn-success btn-sm">
            <i class="fas fa-reply"></i> Kembali ke Stok Trolley
        </a>
    </div>
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">Kirim ke Line</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body" style="display: block" id="scan-stocker">
            <form action="{{ route('store-allocate-this-trolley') }}" method="post" onsubmit="submitForm(this, event)">
                <div id="line-reader" onclick="clearLineScan()"></div>
                <input type="hidden" name="trolley_id" id="trolley_id" value="{{ $trolley->id }}">
                <div class="mb-3">
                    <label class="form-label">Line</label>
                    <div class="input-group">
                        <select class="form-control form-control-sm select2bs4" name="line_id" id="line_id">
                            @foreach ($lines as $line)
                                <option value="{{ $line->id }}">{{ $line->nama_line }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-outline-success" type="button" onclick="getLineDataInput()">Get</button>
                        <button class="btn btn-sm btn-outline-primary" type="button" onclick="initLineScan()">Scan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card card-primary" id="stock-trolley">
        <div class="card-header">
            <h5 class="card-title fw-bold">Stock Trolley</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body" style="display: block">
            <table class="table table-bordered" id="datatable-trolley-stock">
                <thead>
                    <tr>
                        <th>Act</th>
                        <th>No. Stocker</th>
                        <th>No. WS</th>
                        <th>No. Cut</th>
                        <th>Style</th>
                        <th>Color</th>
                        <th>Part</th>
                        <th>Size</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
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
            $('#trolley').val("").trigger("change");
            $('#kode_stocker').val("").trigger("change");

            await initLineScan();
        });

        var trolleyId = document.getElementById('trolley_id').value;

        let datatableTrolleyStock = $("#datatable-trolley-stock").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('allocate-this-trolley') }}/'+trolleyId+' }}',
            },
            columns: [
                {
                    data: 'id'
                },
                {
                    data: 'id_qr_stocker',
                },
                {
                    data: 'act_costing_ws',
                },
                {
                    data: 'no_cut',
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
            ],
            columnDefs: [
                {
                    targets: [0],
                    className: "align-middle",
                    render: (data, type, row, meta) => {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <a class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route("destroy-trolley-stock") }}/`+row['id']+`' onclick='deleteData(this);'>
                                    <i class='fa fa-trash'></i>
                                </a>
                            </div>
                        `;
                    }
                },
            ]
        });

        function datatableTrolleyStockReload() {
            datatableTrolleyStock.ajax.reload();
        }

        // Scan QR Module :
            // Variable List :
                var lineScanner = new Html5Qrcode("line-reader");
                var lineScannerInitialized = false;

            // Function List :
                // -Initialize Line Scanner-
                    async function initLineScan() {
                        if (document.getElementById("line-reader")) {
                            if (lineScannerInitialized == false) {
                                if (lineScanner == null || (lineScanner && (lineScanner.getState() && lineScanner.getState() != 2))) {
                                    const stockerScanSuccessCallback = (decodedText, decodedResult) => {
                                            // handle the scanned code as you like, for example:
                                        console.log(`Code matched = ${decodedText}`, decodedResult);

                                        // store to input text
                                        let breakDecodedText = decodedText.split('-');

                                        $('#kode_stocker').val(breakDecodedText[0]).trigger('change');

                                        getLineData(breakDecodedText[0]);

                                        clearLineScan();
                                    };
                                    const stockerScanConfig = { fps: 10, qrbox: { width: 250, height: 250 } };

                                    // If you want to prefer front camera
                                    await lineScanner.start({ facingMode: "environment" }, stockerScanConfig, stockerScanSuccessCallback);

                                    lineScannerInitialized = true;
                                }
                            }
                        }
                    }

                    async function clearLineScan() {
                        if (lineScannerInitialized) {
                            if (lineScanner && (lineScanner.getState() && lineScanner.getState() != 1)) {
                                await lineScanner.stop();
                                await lineScanner.clear();
                            }

                            lineScannerInitialized = false;
                        }
                    }

                    async function refreshStockerScan() {
                        await clearLineScan();
                        await initLineScan();
                    }

                    function getLineDataInput() {
                        let id = document.getElementById('kode_stocker').value;

                        getLineData(id);
                    }

                    function getLineData(id) {
                        if (checkIfNull(id)) {
                            return $.ajax({
                                url: '{{ route('get-line-data-trolley-stock') }}/' + id,
                                type: 'get',
                                dataType: 'json',
                                success: function(res) {
                                    if (res) {
                                        setLineData(res);
                                    }
                                },
                                error: function(jqXHR) {
                                    console.log(jqXHR);
                                }
                            });
                        }
                    }

                    function setLineData(data) {
                        if (data) {
                            for (let key in data) {
                                console.log(document.getElementById('line_'+key));
                                if (document.getElementById('line_'+key)) {
                                    document.getElementById('line_'+key).value = data[key];
                                    document.getElementById('line_'+key).setAttribute('value', data[key]);

                                    if (document.getElementById('line_'+key).classList.contains('select2bs4') || document.getElementById('line_'+key).classList.contains('select2') || document.getElementById('line_'+key).classList.contains('select2bs4stat')) {
                                        $('#line_'+key).val(data[key]).trigger('change.select2');
                                    }
                                }
                            }
                        }
                    }

        function addToLine(element) {
            let date = new Date();
            let day = date.getDate();
            let month = date.getMonth() + 1;
            let year = date.getFullYear();

            let tanggalAlokasi = `${day}-${month}-${year}`;

            let selectedForm = $('#datatable-select').DataTable().rows('.selected').data();
            let formCutPlan = [];
            for (let key in selectedForm) {
                if (!isNaN(key)) {
                    formCutPlan.push({
                        no_form: selectedForm[key]['no_form']
                    });
                }
            }

            if (tglPlan && formCutPlan.length > 0) {
                element.setAttribute('disabled', true);

                $.ajax({
                    type: "POST",
                    url: '{!! route('store-cut-plan') !!}',
                    data: {
                        tanggal_alokasi: tanggalAlokasi,
                        formCutPlan: formCutPlan
                    },
                    success: function(res) {
                        element.removeAttribute('disabled');

                        if (res.status == 200) {
                            iziToast.success({
                                title: 'Success',
                                message: res.message,
                                position: 'topCenter'
                            });
                        } else {
                            iziToast.error({
                                title: 'Error',
                                message: res.message,
                                position: 'topCenter'
                            });
                        }

                        if (res.table != '') {
                            $('#' + res.table).DataTable().ajax.reload(() => {
                                document.getElementById('selected-row-count-2').innerText = $('#' + res.table).DataTable().rows('.selected').data().length;
                            });

                            $('#datatable-select').DataTable().ajax.reload(() => {
                                document.getElementById('selected-row-count-1').innerText = $('#datatable-select').DataTable().rows('.selected').data().length;
                            });
                        }

                        if (res.additional) {
                            let message = "";

                            if (res.additional['success'].length > 0) {
                                res.additional['success'].forEach(element => {
                                    message += element['no_form'] + " - Berhasil <br>";
                                });
                            }

                            if (res.additional['fail'].length > 0) {
                                res.additional['fail'].forEach(element => {
                                    message += element['no_form'] + " - Gagal <br>";
                                });
                            }

                            if (res.additional['exist'].length > 0) {
                                res.additional['exist'].forEach(element => {
                                    message += element['no_form'] + " - Sudah Ada <br>";
                                });
                            }

                            if ((res.additional['success'].length + res.additional['fail'].length + res
                                    .additional['exist'].length) > 1) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Hasil Transfer',
                                    html: message,
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                });
                            }
                        }
                    },
                    error: function(jqXHR) {
                        element.removeAttribute('disabled');

                        let res = jqXHR.responseJSON;
                        let message = '';

                        for (let key in res.errors) {
                            message = res.errors[key];
                        }

                        iziToast.error({
                            title: 'Error',
                            message: 'Terjadi kesalahan. ' + message,
                            position: 'topCenter'
                        });
                    }
                })
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    html: "Harap isi tanggal plan dan tentukan form cut nya",
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                });
            }
        }
    </script>
@endsection
