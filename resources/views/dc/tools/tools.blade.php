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
                <i class="fa-solid fa-screwdriver-wrench"></i> DC Tools
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <a type="button" class="home-item" onclick="emptyOrderLoading()">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Fix Empty Order Loading</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" onclick="redundantLoadingPlan()">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Fix Redundant Loading Plan</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route("modify-dc-qty") }}" class="home-item" target="_blank">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Modify DC Qty</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route("modify-loading-line") }}" class="home-item" target="_blank">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Modify Loading Line</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route("modify-year-sequence") }}" class="home-item" target="_blank">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Modify Year Sequence</h5>
                            </div>
                        </div>
                    </a>
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

        function emptyOrderLoading() {
            Swal.fire({
                title: 'Fix Empty Order Loading?',
                html: '<span class="text-danger"><b>Critical</b></span> <br> Yakin akan mengubah data loading?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'UBAH',
                cancelButtonText: 'BATAL',
                confirmButtonColor: "#dc3545"
            }).then((result) => {
                if (result.isConfirmed) {
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
                        url: "{{ route('empty-order-loading') }}",
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
            });
        }

        function redundantLoadingPlan() {
            Swal.fire({
                title: 'Fix Redundant Loading Plan?',
                html: '<span class="text-danger"><b>Critical</b></span> <br> Yakin akan mengubah data loading?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'UBAH',
                cancelButtonText: 'BATAL',
                confirmButtonColor: "#dc3545"
            }).then((result) => {
                if (result.isConfirmed) {
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
                        url: "{{ route('redundant-loading-plan') }}",
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
            });
        }
    </script>
@endsection
