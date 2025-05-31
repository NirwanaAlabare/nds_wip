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
                <i class="fa-solid fa-screwdriver-wrench"></i> Cutting Tools
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <a type="button" class="home-item" data-bs-toggle="modal" data-bs-target="#fixRollQty">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Fix Roll Qty</h5>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Stocker -->
    <div class="modal fade" id="fixRollQty" tabindex="-1" aria-labelledby="fixRollQtyLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h1 class="modal-title fs-5" id="fixRollQtyLabel">Fix Roll Qty</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ID Roll</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="fix_roll_id">
                            <button class="btn btn-sb" onclick="fetchScan(document.getElementById('fix_roll_id'))">Get</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ID Item</label>
                        <input type="text" class="form-control" id="fix_roll_id_item" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" id="fix_roll_detail_item" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="text" class="form-control" id="fix_roll_color" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Buyer</label>
                        <input type="text" class="form-control" id="fix_roll_buyer" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. WS</label>
                        <input type="text" class="form-control" id="fix_roll_no_ws" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Style</label>
                        <input type="text" class="form-control" id="fix_roll_style" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lot</label>
                        <input type="text" class="form-control" id="fix_roll_lot" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Roll</label>
                        <input type="text" class="form-control" id="fix_roll_no_roll" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Qty</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="fix_roll_qty">
                            <input type="text" class="form-control" id="fix_roll_unit" readonly>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sb btn-block" onclick="fixRollQty()">Fix Roll Qty</button>
                    <hr class="border-sb">
                    <h5 class="text-center">OR</h5>
                    <hr class="border-sb">
                    <button type="button" class="btn btn-sb-secondary btn-block" onclick="fixRollQtyUpdate()">Fix All Roll Qty</button>
                </div>
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
        $('.select2bs4form').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#fixRollQty')
        });

        $(document).ready(function () {

        });

        document.getElementById("fix_roll_id").addEventListener("onkeydown", (event) => {
            if (event.keyCode == 13) {
                fetchScan(this);
            }
        })

        function fetchScan(element) {
            let idRoll = element.value;

            getScannedItem(idRoll, "fix_roll_");
        }

        async function getScannedItem(id, prefix) {

            document.getElementById("loading").classList.remove("d-none");

            document.getElementById(prefix+"id_item").value = "";
            document.getElementById(prefix+"detail_item").value = "";
            document.getElementById(prefix+"color").value = "";
            document.getElementById(prefix+"buyer").value = "";
            document.getElementById(prefix+"no_ws").value = "";
            document.getElementById(prefix+"lot").value = "";
            document.getElementById(prefix+"no_roll").value = "";
            document.getElementById(prefix+"qty").value = "";
            document.getElementById(prefix+"unit").value = "";

            if (isNotNull(id)) {
                return $.ajax({
                    url: '{{ route('get-roll-qty') }}',
                    type: 'get',
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (typeof res === 'object' && res !== null) {
                            document.getElementById(prefix+"id_item").value = res.id_item;
                            document.getElementById(prefix+"detail_item").value = res.detail_item;
                            document.getElementById(prefix+"buyer").value = res.buyer;
                            document.getElementById(prefix+"no_ws").value = res.no_ws;
                            document.getElementById(prefix+"style").value = res.style;
                            document.getElementById(prefix+"color").value = res.color;
                            document.getElementById(prefix+"lot").value = res.lot;
                            document.getElementById(prefix+"no_roll").value = res.no_roll;
                            document.getElementById(prefix+"qty").value = res.qty;
                            document.getElementById(prefix+"unit").value = res.unit;

                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res ? res : 'Roll tidak tersedia.',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });
                        }

                        document.getElementById("loading").classList.add("d-none");
                    },
                    error: function(jqXHR) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Roll tidak tersedia.',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }

            document.getElementById("loading").classList.add("d-none");

            return Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Item tidak ditemukan',
                showCancelButton: false,
                showConfirmButton: true,
                confirmButtonText: 'Oke',
            });
        }

        async function fixRollQty() {
            let idRoll = document.getElementById("fix_roll_id").value;
            let qtyRoll = document.getElementById("fix_roll_qty").value;

            if (!idRoll || !qtyRoll || isNaN(qtyRoll)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Input',
                    text: 'Please enter a valid Roll ID and Quantity.',
                });
                return;
            }

            await fixRollQtyUpdate(idRoll, qtyRoll);
        }

        async function fixRollQtyUpdate(idRoll = null, qtyRoll = null) {
            document.getElementById("loading").classList.remove("d-none");

            $.ajax({
                type: "post",
                url: "{{ route('fix-roll-qty') }}",
                data: {
                    id_roll: idRoll,
                    qty: qtyRoll,
                },
                dataType: "json",
                success: function (response) {
                    if (response && response.status == 200) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });

                        if (idRoll) {
                            getScannedItem(idRoll, "fix_roll_");
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Roll yang tidak sesuai tidak ditemukan',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });
                    }

                    document.getElementById("loading").classList.add("d-none");
                }
            });
        }
    </script>
@endsection

