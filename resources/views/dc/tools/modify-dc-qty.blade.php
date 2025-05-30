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
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title">
                <i class="fa-solid fa-screwdriver-wrench"></i> Modify DC Qty
            </h5>
        </div>
        <div class="card-body">
            <label class="form-label">Stocker</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" id="id_qr_stocker">
                <button class="btn btn-sb" type="button" id="get-stocker-button" onclick="getDcQty()">Get</button>
            </div>
            <hr>
            <input type="text" class="form-control mb-3" id="stocker" value="" readonly>
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">No. WS</label>
                        <input type="text" class="form-control mb-3" id="ws" value="" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Style</label>
                        <input type="text" class="form-control mb-3" id="style" value="" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="text" class="form-control mb-3" id="color" value="" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Size</label>
                        <input type="text" class="form-control mb-3" id="size" value="" readonly>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                DC IN
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="#" method="post">
                                <div class="mb-3">
                                    <label class="form-label">Qty Awal</label>
                                    <input type="text" class="form-control" name="dc_qty_awal" id="dc_qty_awal" onchange="calculateAll()" onkeyup="calculateAll()">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Qty Reject</label>
                                    <input type="text" class="form-control" name="dc_qty_reject" id="dc_qty_reject" onchange="calculateAll()" onkeyup="calculateAll()">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Qty Replace</label>
                                    <input type="text" class="form-control" name="dc_qty_replace" id="dc_qty_replace" onchange="calculateAll()" onkeyup="calculateAll()">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Qty In</label>
                                    <input type="text" class="form-control" name="dc_qty_in" id="dc_qty_in" readonly>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                Secondary Inhouse
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="#" method="post">
                                <div class="mb-3">
                                    <label class="form-label">Qty Awal</label>
                                    <input type="text" class="form-control" name="inhouse_qty_awal" id="inhouse_qty_awal" onchange="calculateAll()" onkeyup="calculateAll()">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Qty Reject</label>
                                    <input type="text" class="form-control" name="inhouse_qty_reject" id="inhouse_qty_reject" onchange="calculateAll()" onkeyup="calculateAll()">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Qty Replace</label>
                                    <input type="text" class="form-control" name="inhouse_qty_replace" id="inhouse_qty_replace" onchange="calculateAll()" onkeyup="calculateAll()">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Qty In</label>
                                    <input type="text" class="form-control" name="inhouse_qty_in" id="inhouse_qty_in" readonly>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                Secondary In
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="#" method="post">
                                <div class="mb-3">
                                    <label class="form-label">Qty Awal</label>
                                    <input type="text" class="form-control" name="in_qty_awal" id="in_qty_awal" onchange="calculateAll()" onkeyup="calculateAll()">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Qty Reject</label>
                                    <input type="text" class="form-control" name="in_qty_reject" id="in_qty_reject" onchange="calculateAll()" onkeyup="calculateAll()">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Qty Replace</label>
                                    <input type="text" class="form-control" name="in_qty_replace" id="in_qty_replace" onchange="calculateAll()" onkeyup="calculateAll()">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Qty In</label>
                                    <input type="text" class="form-control" name="in_qty_in" id="in_qty_in" readonly>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                Loading Line
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="#" method="post">
                                <div class="mb-3">
                                    <label class="form-label">Line</label>
                                    <select class="form-select select2bs4" name="line_id" id="line_id">
                                        <option value="">Pilih Line</option>
                                        @foreach ($lines as $line)
                                            <option value="{{ $line->line_id }}" data-name="{{ $line->username }}">{{ strtoupper(str_replace("_", " ", $line->username)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="hidden" class="form-control" name="line_name" id="line_name" readonly>
                                <div class="mb-3">
                                    <label class="form-label">Tanggal</label>
                                    <input type="date" class="form-control" name="line_tanggal" id="line_tanggal">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Qty</label>
                                    <input type="text" class="form-control" name="line_qty" id="line_qty">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <button class="btn btn-yes btn-block fw-bold" onclick="updateDcQty()">SIMPAN</button>
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
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2').select2({
            theme: 'bootstrap4'
        });
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });

        $('#id_qr_stocker').on("keydown", (evt) => {
            if (evt.keyCode == 13) {
                getDcQty();
            }
        });

        $('#line_id').on("change", () => {
            let lineName = $('#line_id option:selected').attr('data-name');

            $('#line_name').val(lineName);
        });

        function getDcQty() {
            document.getElementById("loading").classList.remove("d-none");

            $.ajax({
                type: "get",
                url: "{{ route('get-dc-qty') }}",
                data: {
                    id_qr_stocker: $("#id_qr_stocker").val()
                },
                dataType: "json",
                success: function (response) {
                    document.getElementById("loading").classList.add("d-none");

                    if (response.status == 200) {
                        let data = response.data;
                        document.getElementById("stocker").value = data.id_qr_stocker != null ? data.id_qr_stocker : "-";
                        document.getElementById("ws").value = data.ws != null ? data.ws : "-";
                        document.getElementById("style").value = data.styleno != null ? data.styleno : "-";
                        document.getElementById("color").value = data.color != null ? data.color : "-";
                        document.getElementById("size").value = data.size != null ? data.size : "-";
                        document.getElementById("dc_qty_awal").value = data.dc_qty_awal != null ? data.dc_qty_awal : "-";
                        document.getElementById("dc_qty_reject").value = data.dc_qty_reject != null ? data.dc_qty_reject : "-";
                        document.getElementById("dc_qty_replace").value = data.dc_qty_replace != null ? data.dc_qty_replace : "-";
                        document.getElementById("dc_qty_in").value = (data.dc_qty_awal)-(data.dc_qty_reject)+(data.dc_qty_replace);
                        document.getElementById("inhouse_qty_awal").value = data.inhouse_qty_awal != null ? data.inhouse_qty_awal : "-";
                        document.getElementById("inhouse_qty_reject").value = data.inhouse_qty_reject != null ? data.inhouse_qty_reject : "-";
                        document.getElementById("inhouse_qty_replace").value = data.inhouse_qty_replace != null ? data.inhouse_qty_replace : "-";
                        document.getElementById("inhouse_qty_in").value = data.inhouse_qty_in != null ? data.inhouse_qty_in : "-";
                        document.getElementById("in_qty_awal").value = data.in_qty_awal != null ? data.in_qty_awal : "-";
                        document.getElementById("in_qty_reject").value = data.in_qty_reject != null ? data.in_qty_reject : "-";
                        document.getElementById("in_qty_replace").value = data.in_qty_replace != null ? data.in_qty_replace : "-";
                        document.getElementById("in_qty_in").value = data.in_qty_in != null ? data.in_qty_in : "-";
                        $('#line_id').val(data.line_id).trigger('change');
                        document.getElementById("line_tanggal").value = data.line_tanggal != null ? data.line_tanggal : "-";
                        document.getElementById("line_qty").value = data.line_qty != null ? data.line_qty : "-";
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: 'Data tidak ditemukan',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            confirmButtonColor: "#082149",
                        });
                    }
                }
            });
        }

        function updateDcQty() {
            document.getElementById("loading").classList.remove("d-none");

            $.ajax({
                type: "post",
                url: "{{ route('update-dc-qty') }}",
                data: {
                    id_qr_stocker: $('#stocker').val(),
                    dc_qty_awal: $('#dc_qty_awal').val(),
                    dc_qty_reject: $('#dc_qty_reject').val(),
                    dc_qty_replace: $('#dc_qty_replace').val(),
                    dc_qty_in: $('#dc_qty_in').val(),
                    inhouse_qty_awal: $('#inhouse_qty_awal').val(),
                    inhouse_qty_reject: $('#inhouse_qty_reject').val(),
                    inhouse_qty_replace: $('#inhouse_qty_replace').val(),
                    inhouse_qty_in: $('#inhouse_qty_in').val(),
                    in_qty_awal: $('#in_qty_awal').val(),
                    in_qty_reject: $('#in_qty_reject').val(),
                    in_qty_replace: $('#in_qty_replace').val(),
                    in_qty_in: $('#in_qty_in').val(),
                    line_tanggal: $('#line_tanggal').val(),
                    line_id: $('#line_id').val(),
                    line_name: $('#line_name').val(),
                    line_qty: $('#line_qty').val()
                },
                dataType: "json",
                success: function (response) {
                    document.getElementById("loading").classList.add("d-none");

                    console.log(response);
                    let dc = response.dc ? "DC berhasil di update." : "DC tidak ada";
                    let secondaryInhouse = response.secondaryInhouse ? "Secondary Inhouse berhasil di update." : "Secondary Inhouse tidak ada";
                    let secondaryIn = response.secondaryIn ? "Secondary In berhasil di update." : "Secondary In tidak ada";
                    let loadingLine = response.loadingLine ? "Loading Line berhasil di update." : "Loading Line tidak ada";

                    Swal.fire({
                        icon: "info",
                        html: dc+"<br>"+secondaryInhouse+"<br>"+secondaryIn+"<br>"+loadingLine
                    })
                },
                error: function (jqXHR) {
                    document.getElementById("loading").classList.add("d-none");

                    console.error(jqXHR);
                }
            });
        }

        function calculateAll() {
            if (document.getElementById('dc_qty_awal').value && document.getElementById('dc_qty_reject').value && document.getElementById('dc_qty_replace').value) {
                document.getElementById('dc_qty_in').value = Number(document.getElementById('dc_qty_awal').value) - Number(document.getElementById('dc_qty_reject').value) + Number(document.getElementById('dc_qty_replace').value);

                if (document.getElementById('inhouse_qty_awal').value != "-") {
                    document.getElementById('inhouse_qty_awal').value = Number(document.getElementById('dc_qty_in').value);
                } else {
                    if (document.getElementById('in_qty_awal').value != '-') {
                        document.getElementById('in_qty_awal').value = Number(document.getElementById('dc_qty_in').value);
                    } else {
                        document.getElementById('line_qty').value = Number(document.getElementById('dc_qty_in').value);
                    }
                }
            }

            if (document.getElementById('inhouse_qty_awal').value != "-" && document.getElementById('inhouse_qty_reject').value != "-" && document.getElementById('inhouse_qty_replace').value != "-") {
                document.getElementById('inhouse_qty_in').value = Number(document.getElementById('inhouse_qty_awal').value) - Number(document.getElementById('inhouse_qty_reject').value) + Number(document.getElementById('inhouse_qty_replace').value);

                if (document.getElementById('in_qty_awal').value != '-') {
                    document.getElementById('in_qty_awal').value = Number(document.getElementById('inhouse_qty_in').value);
                } else {
                    document.getElementById('line_qty').value = Number(document.getElementById('inhouse_qty_in').value);
                }
            }

            if (document.getElementById('in_qty_awal').value != "-" && document.getElementById('in_qty_reject').value != "-" && document.getElementById('in_qty_replace').value != "-") {
                document.getElementById('in_qty_in').value = Number(document.getElementById('in_qty_awal').value) - Number(document.getElementById('in_qty_reject').value) + Number(document.getElementById('in_qty_replace').value);
                document.getElementById('line_qty').value = Number(document.getElementById('in_qty_in').value);
            }
        }
    </script>
@endsection
