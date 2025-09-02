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
                <i class="fa-solid fa-screwdriver-wrench"></i> Sewing Tools
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <a href="{{ route('sewing-transfer-output') }}" type="button" class="home-item">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"> <i class="fa-solid fa-arrow-right-arrow-left"></i> Transfer Output
                                </h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" onclick="missUser()">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-users-line"></i> Fix Output User Line</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" onclick="missMasterPlan()">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Fix Output Master Plan</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" onclick="missRework()">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-circle-exclamation"></i> Fix Defect-Rework-RFT</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" onclick="missReject()">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-circle-exclamation"></i> Fix Defect-Reject</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('check-output-detail') }}" class="home-item">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-circle-info"></i> Check Output Detail</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('undo-output') }}" class="home-item">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-arrows-rotate"></i> Undo Output</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('line-migration') }}" class="home-item">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-shuffle"></i> Line Migration</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('modify-output') }}" class="home-item">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-shirt"></i> Modify Output</h5>
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
        $('.select2bs4merge').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#mergeDefectTypeModal')
        });

        function missUser() {
            Swal.fire({
                title: 'Fix Miss User Output',
                html: '<span class="text-danger"><b>Critical</b></span> <br> Yakin akan mengubah data output user line?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'UBAH',
                cancelButtonText: 'BATAL',
                confirmButtonColor: "#dc3545"
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Please Wait...',
                        html: 'Fixing Miss User Output Data...  <br><br> <b>0</b>s elapsed...',
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
                        url: "{{ route('sewing-miss-user') }}",
                        dataType: "json",
                        success: function(response) {
                            if (response.status == 200) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
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
                                    html: response && response.message ? response.message :
                                        "Terjadi Kesalahan",
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                    confirmButtonColor: "#082149",
                                });
                            }
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'Info',
                        html: "Proses dibatalkan",
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                        confirmButtonColor: "#082149",
                    });
                }
            });
        }

        function missMasterPlan() {
            Swal.fire({
                title: 'Fix Miss Master Plan Output',
                html: '<span class="text-danger"><b>Critical</b></span> <br> Yakin akan mengubah data output master plan?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'UBAH',
                cancelButtonText: 'BATAL',
                confirmButtonColor: "#dc3545"
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Please Wait...',
                        html: 'Fixing Miss Master Plan Output Data...  <br><br> <b>0</b>s elapsed...',
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
                        url: "{{ route('sewing-miss-masterplan') }}",
                        dataType: "json",
                        success: function(response) {
                            if (response.status == 200) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
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
                                    html: response && response.message ? response.message :
                                        "Terjadi Kesalahan",
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                    confirmButtonColor: "#082149",
                                });
                            }
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'Info',
                        html: "Proses dibatalkan",
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                        confirmButtonColor: "#082149",
                    });
                }
            })
        }

        function missRework() {
            Swal.fire({
                title: 'Fix Miss Rework Output',
                html: '<span class="text-danger"><span class="text-danger"><b>Critical</b></span></span> <br> Yakin akan mengubah data output rework?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'UBAH',
                cancelButtonText: 'BATAL',
                confirmButtonColor: "#dc3545"
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Please Wait...',
                        html: 'Fixing Rework Output Data...  <br><br> <b>0</b>s elapsed...',
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
                        url: "{{ route('sewing-miss-rework') }}",
                        dataType: "json",
                        success: function(response) {
                            if (response.status == 200) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
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
                                    html: response && response.message ? response.message :
                                        "Terjadi Kesalahan",
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                    confirmButtonColor: "#082149",
                                });
                            }
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'Info',
                        html: "Proses dibatalkan",
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                        confirmButtonColor: "#082149",
                    });
                }
            });
        }

        function missReject() {
            Swal.fire({
                title: 'Fix Miss Reject Output',
                html: '<span class="text-danger"><span class="text-danger"><b>Critical</b></span></span> <br> Yakin akan mengubah data output reject?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'UBAH',
                cancelButtonText: 'BATAL',
                confirmButtonColor: "#dc3545"
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Please Wait...',
                        html: 'Fixing Reject Output Data...  <br><br> <b>0</b>s elapsed...',
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
                        url: "{{ route('sewing-miss-reject') }}",
                        dataType: "json",
                        success: function(response) {
                            if (response.status == 200) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
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
                                    html: response && response.message ? response.message :
                                        "Terjadi Kesalahan",
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                    confirmButtonColor: "#082149",
                                });
                            }
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'Info',
                        html: "Proses dibatalkan",
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                        confirmButtonColor: "#082149",
                    });
                }
            });
        }
    </script>
@endsection
