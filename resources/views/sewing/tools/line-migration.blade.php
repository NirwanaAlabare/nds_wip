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
            <h5 class="card-title"><i class="fa-solid fa-shuffle"></i> Line Migration</h5>
        </div>
        <div class="card-body">
            <div class="row justify-content-center align-items-center g-3 mt-3">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="tanggal">Tanggal</label>
                                <input type="date" class="form-control" name="tanggal_from" id="tanggal_from" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="form-group">
                                <label for="line">Line</label>
                                <select class="form-select select2bs4" name="line_from" id="line_from">
                                    <option value="">Pilih Line</option>
                                    @foreach ($lines as $line)
                                        <option value="{{ $line->username }}">{{ strtoupper(str_replace("_", " ", $line->username)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="line">Master Plan</label>
                                <select class="form-select select2bs4" name="master_plan_from" id="master_plan_from">
                                    <option value="">Pilih Master Plan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="d-flex flex-column justify-content-center align-items-center mb-3">
                        <i class="text-sb-secondary fa fa-arrow-right fa-xl"></i>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="line">Line</label>
                                <select class="form-select select2bs4" name="line_to" id="line_to">
                                    <option value="">Pilih Line</option>
                                    @foreach ($lines as $line)
                                        <option value="{{ $line->username }}">{{ strtoupper(str_replace("_", " ", $line->username)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tanggal_plan">Tanggal Plan</label>
                        <input type="date" class="form-control" id="tanggal_plan" value="" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="no_ws">No. WS</label>
                        <input type="text" class="form-control" id="no_ws" value="" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="style">Style</label>
                        <input type="text" class="form-control" id="style" value="" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="text" class="form-control" id="color" value="" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="style">Jam Kerja</label>
                        <input type="text" class="form-control" id="jam_kerja" value="" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="style">SMV</label>
                        <input type="text" class="form-control" id="smv" value="" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="style">Man Power</label>
                        <input type="text" class="form-control" id="man_power" value="" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="style">Target</label>
                        <input type="text" class="form-control" id="target" value="" readonly>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered w-100" id="datatable">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Kode Numbering</th>
                            <th>Size</th>
                            <th>Status</th>
                            <th>Defect</th>
                            <th>Allocation</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <div class="card-footer border-1">
            <button class="btn btn-sb-secondary btn-block fw-bold" onclick="migrateLine()"><i class="fa-solid fa-shuffle"></i> MIGRATE</button>
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
        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        })
    </script>

    <script>
        $(document).ready(function () {
            getMasterPlan();
        });

        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-master-plan-output") }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.id = $('#master_plan_from').val();
                },
            },
            columns: [
                {
                    data: 'updated_at'
                },
                {
                    data: 'kode_numbering'
                },
                {
                    data: 'size'
                },
                {
                    data: 'status'
                },
                {
                    data: 'defect'
                },
                {
                    data: 'allocation'
                },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap"
                },
                {
                    targets: [3, 4, 5],
                    render: (data, type, row, meta) => data ? data.toUpperCase() : "-"
                }
            ]
        });

        function dataTableReload() {
            datatable.ajax.reload();
        }

        function migrateLine() {
            Swal.fire({
                icon: 'question',
                title: 'Migrate Line?',
                showCancelButton: true,
                showConfirmButton: true,
                confirmButtonText: 'Migrate',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#238380',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("loading").classList.remove("d-none");

                    $.ajax({
                        type: "post",
                        url: "{{ route('line-migration-submit') }}",
                        data: {
                            tanggal_from: document.getElementById("tanggal_from").value,
                            line_from: document.getElementById("line_from").value,
                            master_plan_from: document.getElementById("master_plan_from").value,
                            line_to: document.getElementById("line_to").value,
                        },
                        dataType: "json",
                        success: function (response) {
                            document.getElementById("loading").classList.add("d-none");

                            if (response.status == 200) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    html: response.message,
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    clearForm();
                                    dataTableReload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message,
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            document.getElementById("loading").classList.add("d-none");

                            let res = xhr.responseJSON;
                            let message = '';
                            for (let key in res.errors) {
                                message += '<b>'+res.errors[key]+'</b> <br>';

                                iziToast.error({
                                    title: 'Error',
                                    message: '<b>'+res.errors[key]+'</b> <br>',
                                    position: 'topCenter',
                                    timeout: 5000,
                                });
                            };
                        }
                    });
                } else {
                    document.getElementById("loading").classList.remove("d-none");
                }
            });
        }

        $('#tanggal_from').on('change', function() {
            getMasterPlan();
        });

        $('#line_from').on('change', function() {
            getMasterPlan();
        });

        function getMasterPlan() {
            document.getElementById("loading").classList.remove("d-none");

            let tanggal = $('#tanggal_from').val();
            let line = $('#line_from').val();

            if (tanggal && line) {
                $.ajax({
                    url: "{{ route('get-master-plan') }}",
                    type: 'GET',
                    data: {
                        tanggal: tanggal,
                        line: line
                    },
                    success: function(data) {
                        document.getElementById("loading").classList.add("d-none");

                        console.log(data);
                        $('#master_plan_from').empty().append('<option value="">Pilih Master Plan</option>');
                        $.each(data, function(index, item) {
                            $('#master_plan_from').append('<option value="' + item.id + '">' + item.no_ws + ' - ' + item.style + ' - ' + item.color + ' ' + (item.cancel == 'Y' ? '<b>CANCELLED</b>' : '') + '</option>');
                        });
                    }, error: function(xhr, status, error) {
                        document.getElementById("loading").classList.add("d-none");

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal memuat Master Plan.',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            } else {
                document.getElementById("loading").classList.add("d-none");
            }
        }

        $('#master_plan_from').on('change', function() {
            getMasterPlanDetail($(this).val());

            dataTableReload();
        });

        function getMasterPlanDetail(id) {
            if (id) {
                document.getElementById("loading").classList.remove("d-none");

                $.ajax({
                    url: "{{ route('get-master-plan-detail') }}/" + id,
                    type: 'GET',
                    success: function(data) {
                        document.getElementById("loading").classList.add("d-none");

                        $('#tanggal_plan').val(data.tanggal);
                        $('#no_ws').val(data.no_ws);
                        $('#style').val(data.style);
                        $('#color').val(data.color);
                        $('#jam_kerja').val(data.jam_kerja);
                        $('#smv').val(data.smv);
                        $('#man_power').val(data.man_power)
                        $('#target').val(data.plan_target)
                    },
                    error: function(xhr, status, error) {
                        document.getElementById("loading").classList.add("d-none");

                        console.error('Error fetching master plan detail:', error);
                    }
                });
            }
        }

        function clearForm() {
            $('#tanggal_from').val('').trigger('change');
            $('#line_from').val('').trigger('change');
            $('#master_plan_from').empty().append('<option value="">Pilih Master Plan</option>').trigger('change');
            $('#line_to').val('').trigger('change');
            $('#tanggal_plan').val('');
            $('#no_ws').val('');
            $('#style').val('');
            $('#color').val('');
            $('#jam_kerja').val('');
            $('#smv').val('');
            $('#man_power').val('');
            $('#target').val('');
        }
    </script>
@endsection
