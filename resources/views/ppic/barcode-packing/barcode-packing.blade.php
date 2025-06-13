@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .box {
            width: 100%;
            min-height: 300px;
            border: 1.5px dashed #ccc;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            background-color: #f9f9f9;
            padding: 10px;
        }
    </style>
@endsection

@section('content')
    <form action="">
        <div class="card">
            <div class="card-header bg-sb text-light">
                <h3 class="card-title">
                    <i class="fa fa-barcode"></i> Barcode Packing
                </h3>
            </div>
            <div class="card-body">
                <div class="row align-items-end g-3">
                    <div class="col-md-3">
                        <label class="form-label">No. WS</label>
                        <select class="form-select select2bs4" name="order" id="order" onchange="orderChange(this)">
                            <option value="">Pilih WS</option>
                            @foreach ($orders as $order)
                                <option value="{{ $order->id }}">{{ $order->kpno }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Style</label>
                        <select class="form-select select2bs4" name="style" id="style" onchange="orderChange(this)">
                            <option value="">Pilih Style</option>
                            @foreach ($orders as $order)
                                <option value="{{ $order->id }}">{{ $order->styleno }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Color</label>
                        <select class="form-select select2bs4" name="color" id="color" onchange="getSizes()">
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Size</label>
                        <select class="form-select select2bs4" name="size" id="size" onchange="getBarcode()">
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Barcode</label>
                        <input type="text" class="form-control" id="barcode" name="barcode" readonly>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-sb" onclick="generateBarcode()">Generate</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="text-sb d-none" id="current"></h5>
                    <button id="button-download" type="button" class="btn btn-sb-secondary btn-sm d-none" onclick="downloadBarcode()">Download</button>
                </div>
                <div class="box">
                    <div class="d-flex justify-content-center align-items-center w-100">
                        <h5 id="preview-title">Preview</h5>
                        <iframe id="barcode-pdf" width="100%" height="300px" class="d-none"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
            // containerCssClass: 'form-control-sm rounded'
        });

        document.addEventListener('DOMContentLoaded', function () {
            $("#order").val('').trigger("change");
            $("#barcode").val('').trigger("change");
        });

        function orderChange(element) {
            if (element.id == "order") {
                if ($("#style").val() != element.value) {
                    $("#style").val(element.value).trigger("change");
                }
            } else if (element.id == "style") {
                if ($("#order").val() != element.value) {
                    $("#order").val(element.value).trigger("change");
                }
            }

            let colorElement = document.getElementById('color');
            let sizeElement = document.getElementById('size');

            // Clear previous options
            colorElement.innerHTML = '<option value="">Pilih Color</option>';
            sizeElement.innerHTML = '<option value="">Pilih Size</option>';

            getColors(element);
        }

        function getColors(element) {
            let order = element.value;

            // if (order) {
                document.getElementById("loading").classList.remove("d-none");

                $.ajax({
                    url: "{{ route('get-colors') }}",
                    type: "GET",
                    data: { act_costing_id: order },
                    success: function (response) {
                        document.getElementById("loading").classList.add("d-none");

                        if (response) {
                            $('#color').empty().append('<option value="">Pilih Color</option>');
                            response.forEach(function (color) {
                                $('#color').append(`<option value="${color.color}">${color.color}</option>`);
                            });
                            $('#color').val('').trigger('change');
                        }
                    },
                    error: function (jqXHR) {
                        document.getElementById("loading").classList.add("d-none");

                        console.error(jqXHR);
                    }
                });
            // }
        }

        function getSizes() {
            let order = $("#order").val();
            let color = $("#color").val();

            // if (order && color) {
                document.getElementById("loading").classList.remove("d-none");

                $.ajax({
                    url: "{{ route('get-sizes') }}",
                    type: "GET",
                    data: {
                        act_costing_id: order,
                        color: color,
                    },
                    success: function (response) {
                        document.getElementById("loading").classList.add("d-none");

                        if (response) {
                            $('#size').empty().append('<option value="">Pilih Size</option>');
                            response.forEach(function (size) {
                                $('#size').append(`<option value="${size.so_det_id}">${size.size}${(size.dest && size.dest != "-" ? " / "+size.dest : "")}</option>`);
                            });
                            $('#size').val('').trigger('change');
                        }
                    },
                    error: function (jqXHR) {
                        document.getElementById("loading").classList.add("d-none");

                        console.error(jqXHR);
                    }
                });
            // }
        }

        function getBarcode() {
            let size = $("#size").val();

            if (size) {
                document.getElementById("loading").classList.remove("d-none");

                $.ajax({
                    url: "{{ route('get-barcode-packing') }}",
                    type: "GET",
                    data: {
                        so_det_id: size,
                    },
                    success: function (response) {
                        document.getElementById("loading").classList.add("d-none");

                        if (response) {
                            $('#barcode').val(response.barcode);
                        } else {
                            $('#barcode').val('');

                            Swal.fire({
                                icon: 'error',
                                title: 'Tidak ditemukan',
                                text: 'Barcode tidak ditemukan',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function (jqXHR) {
                        document.getElementById("loading").classList.add("d-none");

                        console.error(jqXHR);
                    }
                });
            } else {
                $('#barcode').val('');
            }
        }

        function generateBarcode() {
            let barcode = $("#barcode").val();

            if (barcode) {
                const pdfUrl = `{{ route('generate-barcode-packing') }}/${barcode}`;

                document.getElementById('preview-title').classList.add("d-none");

                document.getElementById('current').classList.remove("d-none");
                document.getElementById('button-download').classList.remove("d-none");
                document.getElementById('barcode-pdf').classList.remove("d-none");

                document.getElementById('current').innerHTML = "Barcode Packing : <b>"+barcode+"</b>";
                document.getElementById('barcode-pdf').src = pdfUrl;
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Harap pilih size.',
                    confirmButtonText: 'OK'
                });
            }
        }

        function downloadBarcode() {
            let barcode = $("#barcode").val();

            if (barcode) {
                Swal.fire({
                    title: 'Please Wait...',
                    html: 'Exporting Data...',
                    didOpen: () => {
                        Swal.showLoading()
                    },
                    allowOutsideClick: false,
                });

                const formData = new FormData();
                formData.append('barcode', barcode);

                $.ajax({
                    url: '{{ route('download-barcode-packing') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhrFields: {
                        responseType: 'blob' // Expect binary (PDF) response
                    },
                    success: function(res) {
                        const blob = new Blob([res], { type: 'application/pdf' });
                        const link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Barcode Packing " + barcode + ".pdf";
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        Swal.close();
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to export barcode.',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Harap pilih size.',
                    confirmButtonText: 'OK'
                });
            }
        }
    </script>
@endsection
