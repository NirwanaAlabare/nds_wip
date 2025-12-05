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
                <i class="fa-solid fa-screwdriver-wrench"></i> General Tools
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <a type="button" class="home-item" onclick="updateMasterSbWs()">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Update Master SB</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" data-bs-toggle="modal" data-bs-target="#updateGeneralOrderModal">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Update General Order Info</h5>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Update General Order Modal --}}
    <div class="modal fade" id="updateGeneralOrderModal" tabindex="-1" aria-labelledby="updateGeneralOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h1 class="modal-title fs-5" id="updateGeneralOrderModalLabel">Update General Order</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="order_number" class="form-label">No. WS</label>
                        <select class="form-control select2bs4updategeneralorder" name="act_costing_id[]" id="act_costing_id" multiple="multiple">
                            <option value="">Pilih WS</option>
                            @foreach ($orders as $order)
                                <option value="{{ $order->act_costing_id }}">{{ $order->act_costing_ws }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sb-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-sb" onclick="updateGeneralOrder()">UPDATE</button>
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
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            containerCssClass: 'form-control-sm rounded'
        });

        // Initialize Select2BS4 Elements Filter Modal
        $('.select2bs4updategeneralorder').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#updateGeneralOrderModal")
        });

        function updateGeneralOrder() {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Fixing Data...  <br><br> <b>0</b>s elapsed...',
                didOpen: () => {
                    Swal.showLoading();

                    let estimatedTime = 0;
                    const estimatedTimeElement = Swal.getPopup().querySelector("b");
                    estimatedTimeInterval = setInterval(() => {
                        estimatedTime++;
                        estimatedTimeElement.textContent = estimatedTime;
                    }, 1000);
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "post",
                url: "{{ route('update-general-order') }}",
                data: {
                    ids: $('#act_costing_id').val()
                },
                dataType: "json",
                success: function (response) {
                    if (response.status == 200) {
                        Swal.fire({
                            icon: 'info',
                            title: 'INFO',
                            html: response.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            confirmButtonColor: "#082149",
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: 'Terjadi kesalahan',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            confirmButtonColor: "#082149",
                        });
                    }
                }
            });
        }

        function updateMasterSbWs() {
            Swal.fire({
                title: 'Update Master SB',
                html: 'Yakin akan memperbaharui data master sb?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'PERBAHARUI',
                cancelButtonText: 'BATAL',
                confirmButtonColor: "#dc3545"
            }).then((result) => {
                document.getElementById("loading").classList.remove("d-none");

                $.ajax({
                    type: "post",
                    url: "{{ route('update-master-sb-ws') }}",
                    dataType: "json",
                    success: function (response) {
                        document.getElementById("loading").classList.add("d-none");

                        if (response) {
                            Swal.fire({
                                icon: 'info',
                                title: 'INFO',
                                html:
                                    "Deleted '"+response.deleted+"'' rows <br>"+
                                    "Inserted '"+response.inserted+"'' rows <br>"+
                                    "Updated '"+response.updated+"'' rows <br>",
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                                confirmButtonColor: "#082149",
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                html: 'Terjadi kesalahan',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                                confirmButtonColor: "#082149",
                            });
                        }

                        console.log(response);
                    },
                    error: function (jqXHR) {
                        document.getElementById("loading").classList.add("d-none");

                        console.error(jqXHR);
                    }
                });
            })
        }
    </script>
@endsection
