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
            <h5 class="card-title"><i class="fa-solid fa-people-group"></i> Master Line</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end mb-3">
                <div class="d-flex align-items-end gap-3">
                    <div>
                        <label class="form-label">Dari</label>
                        <input type="date" class="form-control" name="from" id="from" value="{{ date('Y-m-d') }}" onchange="datatableReload()">
                    </div>
                    <span class="mb-2">
                        -
                    </span>
                    <div>
                        <label class="form-label">Sampai</label>
                        <input type="date" class="form-control" name="to" id="to" value="{{ date('Y-m-d') }}" onchange="datatableReload()">
                    </div>
                </div>
                <div class="d-flex gap-1">
                    <button class="btn btn-sb fw-bold" onclick="updateImage()"><i class="fa-solid fa-images"></i> UPDATE</a>
                    <button class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#storeMasterLineModal"><i class="fa fa-plus"></i> NEW</a>
                    <button class="btn btn-rft fw-bold" onclick="exportExcel()"><i class="fa fa-file-excel"></i></a>
                </div>
            </div>
            <table id="datatable" class="table table-bordered w-100">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Tanggal</th>
                        <th>Line</th>
                        <th>Chief</th>
                        <th>Leader</th>
                        <th>IE</th>
                        <th>Leader QC</th>
                        <th>Mechanic</th>
                        <th>Technical</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Modal -->
    <form method="post" action="{{ route("store-master-line") }}" id="store-master-line" onsubmit="submitForm(this, event)">
        <div class="modal fade" id="storeMasterLineModal" tabindex="-1" aria-labelledby="storeMasterLineModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-sb">
                        <h1 class="modal-title fs-5" id="storeMasterLineModalLabel"><i class="fa fa-plus"></i> New Master Line</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Tanggal</label>
                                <input type="date" class="form-control" name="tanggal" id="tanggal" value="{{ date("Y-m-d") }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Line</label>
                                <select class="form-select select2bs4-create-modal" name="line_id" id="line_id">
                                    <option value="">Pilih Line</option>
                                    @foreach ($lines as $line)
                                        <option value="{{ $line->line_id }}">{{ strtoupper(str_replace("_", " ", $line->username)) }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="form-control" name="line_name" id="line_name" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Chief</label>
                                <select class="form-select select2bs4-create-modal" name="chief_id" id="chief_id">
                                    <option value="">Pilih Chief</option>
                                    @foreach ($employeesChief as $employeeChief)
                                        <option value="{{ $employeeChief->enroll_id }}" data-nik="{{ $employeeChief->nik ? $employeeChief->nik : '' }}" data-name="{{ $employeeChief->employee_name ? $employeeChief->employee_name : '' }}">{{ $employeeChief->nik." - ".$employeeChief->employee_name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="form-control" name="chief_nik" id="chief_nik" readonly>
                                <input type="hidden" class="form-control" name="chief_name" id="chief_name" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Leader</label>
                                <select class="form-select select2bs4-create-modal" name="leader_id" id="leader_id">
                                    <option value="">Pilih Leader</option>
                                    @foreach ($employeesLeader as $employeeLeader)
                                        <option value="{{ $employeeLeader->enroll_id }}" data-nik="{{ $employeeLeader->nik ? $employeeLeader->nik : '' }}" data-name="{{ $employeeLeader->employee_name ? $employeeLeader->employee_name : '' }}">{{ $employeeLeader->nik." - ".$employeeLeader->employee_name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="form-control" name="leader_nik" id="leader_nik" readonly>
                                <input type="hidden" class="form-control" name="leader_name" id="leader_name" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">IE</label>
                                <select class="form-select select2bs4-create-modal" name="ie_id" id="ie_id">
                                    <option value="">Pilih IE</option>
                                    @foreach ($employeesIe as $employeeIe)
                                        <option value="{{ $employeeIe->enroll_id }}" data-nik="{{ $employeeIe->nik ? $employeeIe->nik : '' }}" data-name="{{ $employeeIe->employee_name ? $employeeIe->employee_name : '' }}">{{ $employeeIe->nik." - ".$employeeIe->employee_name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="form-control" name="ie_nik" id="ie_nik" readonly>
                                <input type="hidden" class="form-control" name="ie_name" id="ie_name" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Leader QC</label>
                                <select class="form-select select2bs4-create-modal" name="leaderqc_id" id="leaderqc_id">
                                    <option value="">Pilih Leader QC</option>
                                    @foreach ($employeesLeaderQc as $employeeQc)
                                        <option value="{{ $employeeQc->enroll_id }}" data-nik="{{ $employeeQc->nik ? $employeeQc->nik : '' }}" data-name="{{ $employeeQc->employee_name ? $employeeQc->employee_name : '' }}">{{ $employeeQc->nik." - ".$employeeQc->employee_name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="form-control" name="leaderqc_nik" id="leaderqc_nik" readonly>
                                <input type="hidden" class="form-control" name="leaderqc_name" id="leaderqc_name" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Mechanic</label>
                                <select class="form-select select2bs4-create-modal" name="mechanic_id" id="mechanic_id">
                                    <option value="">Pilih Mechanic</option>
                                    @foreach ($employeesMechanic as $employeeMech)
                                        <option value="{{ $employeeMech->enroll_id }}" data-nik="{{ $employeeMech->nik ? $employeeMech->nik : '' }}" data-name="{{ $employeeMech->employee_name ? $employeeMech->employee_name : '' }}">{{ $employeeMech->nik." - ".$employeeMech->employee_name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="form-control" name="mechanic_nik" id="mechanic_nik" readonly>
                                <input type="hidden" class="form-control" name="mechanic_name" id="mechanic_name" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Technical</label>
                                <select class="form-select select2bs4-create-modal" name="technical_id" id="technical_id">
                                    <option value="">Pilih Technical</option>
                                    @foreach ($employeesTechnical as $employeeTech)
                                        <option value="{{ $employeeTech->enroll_id }}" data-nik="{{ $employeeTech->nik ? $employeeTech->nik : '' }}" data-name="{{ $employeeTech->employee_name ? $employeeTech->employee_name : '' }}">{{ $employeeTech->nik." - ".$employeeTech->employee_name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="form-control" name="technical_nik" id="technical_nik" readonly>
                                <input type="hidden" class="form-control" name="technical_name" id="technical_name" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> BATAL</button>
                        <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> SIMPAN</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Edit Modal -->
    <form method="post" action="{{ route("update-master-line") }}" id="update-master-line" onsubmit="submitForm(this, event)">
        @method("PUT")
        <div class="modal fade" id="updateMasterLineModal" tabindex="-1" aria-labelledby="updateMasterLineModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-sb">
                        <h1 class="modal-title fs-5" id="updateMasterLineModalLabel"><i class="fa fa-edit"></i> Edit Master Line</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-12 d-none">
                                <label class="form-label">ID</label>
                                <input type="tex" class="form-control" name="edit_id" id="edit_id" value="" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Tanggal</label>
                                <input type="date" class="form-control" name="edit_tanggal" id="edit_tanggal">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Line</label>
                                <select class="form-select select2bs4-edit-modal" name="edit_line_id" id="edit_line_id">
                                    <option value="">Pilih Line</option>
                                    @foreach ($lines as $line)
                                        <option value="{{ $line->line_id }}">{{ strtoupper(str_replace("_", " ", $line->username)) }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="form-control" name="edit_line_name" id="edit_line_name" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Chief</label>
                                <select class="form-select select2bs4-edit-modal" name="edit_chief_id" id="edit_chief_id">
                                    <option value="">Pilih Chief</option>
                                    @foreach ($employeesChief as $employee)
                                        <option value="{{ $employee->enroll_id }}" data-nik="{{ $employee->nik }}" data-name="{{ $employee->employee_name }}">{{ $employee->nik." - ".$employee->employee_name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="form-control" name="edit_chief_nik" id="edit_chief_nik" readonly>
                                <input type="hidden" class="form-control" name="edit_chief_name" id="edit_chief_name" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Leader</label>
                                <select class="form-select select2bs4-edit-modal" name="edit_leader_id" id="edit_leader_id">
                                    <option value="">Pilih Leader</option>
                                    @foreach ($employeesLeader as $employee)
                                        <option value="{{ $employee->enroll_id }}" data-nik="{{ $employee->nik }}" data-name="{{ $employee->employee_name }}">{{ $employee->nik." - ".$employee->employee_name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="form-control" name="edit_leader_nik" id="edit_leader_nik" readonly>
                                <input type="hidden" class="form-control" name="edit_leader_name" id="edit_leader_name" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">IE</label>
                                <select class="form-select select2bs4-edit-modal" name="edit_ie_id" id="edit_ie_id">
                                    <option value="">Pilih IE</option>
                                    @foreach ($employeesIe as $employee)
                                        <option value="{{ $employee->enroll_id }}" data-nik="{{ $employee->nik }}" data-name="{{ $employee->employee_name }}">{{ $employee->nik." - ".$employee->employee_name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="form-control" name="edit_ie_nik" id="edit_ie_nik" readonly>
                                <input type="hidden" class="form-control" name="edit_ie_name" id="edit_ie_name" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Leader QC</label>
                                <select class="form-select select2bs4-edit-modal" name="edit_leaderqc_id" id="edit_leaderqc_id">
                                    <option value="">Pilih Leader QC</option>
                                    @foreach ($employeesLeaderQc as $employee)
                                        <option value="{{ $employee->enroll_id }}" data-nik="{{ $employee->nik }}" data-name="{{ $employee->employee_name }}">{{ $employee->nik." - ".$employee->employee_name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="form-control" name="edit_leaderqc_nik" id="edit_leaderqc_nik" readonly>
                                <input type="hidden" class="form-control" name="edit_leaderqc_name" id="edit_leaderqc_name" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Mechanic</label>
                                <select class="form-select select2bs4-edit-modal" name="edit_mechanic_id" id="edit_mechanic_id">
                                    <option value="">Pilih Mechanic</option>
                                    @foreach ($employeesMechanic as $employee)
                                        <option value="{{ $employee->enroll_id }}" data-nik="{{ $employee->nik }}" data-name="{{ $employee->employee_name }}">{{ $employee->nik." - ".$employee->employee_name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="form-control" name="edit_mechanic_nik" id="edit_mechanic_nik" readonly>
                                <input type="hidden" class="form-control" name="edit_mechanic_name" id="edit_mechanic_name" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Technical</label>
                                <select class="form-select select2bs4-edit-modal" name="edit_technical_id" id="edit_technical_id">
                                    <option value="">Pilih Technical</option>
                                    @foreach ($employeesTechnical as $employee)
                                        <option value="{{ $employee->enroll_id }}" data-nik="{{ $employee->nik }}" data-name="{{ $employee->employee_name }}">{{ $employee->nik." - ".$employee->employee_name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="form-control" name="edit_technical_nik" id="edit_technical_nik" readonly>
                                <input type="hidden" class="form-control" name="edit_technical_name" id="edit_technical_name" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> BATAL</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-edit"></i> UBAH</button>
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
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
        $('.select2bs4-create-modal').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#storeMasterLineModal')
        })
        $('.select2bs4-edit-modal').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#updateMasterLineModal')
        })
    </script>

    {{-- General --}}
    <script>
        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            scrollX: true,
            scrollY: "500px",
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('master-line') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.from = $('#from').val();
                    d.to = $('#to').val();
                },
            },
            columns: [
                {
                    data: 'id'
                },
                {
                    data: 'tanggal'
                },
                {
                    data: 'line_name'
                },
                {
                    data: 'chief_name'
                },
                {
                    data: 'leader_name'
                },
                {
                    data: 'ie_name'
                },
                {
                    data: 'leaderqc_name'
                },
                {
                    data: 'mechanic_name'
                },
                {
                    data: 'technical_name'
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    className: 'align-middle',
                    render: (data, type, row, meta) => {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#updateMasterLineModal" onclick='editData(` + JSON.stringify(row) + `, "updateMasterLineModal");'>
                                    <i class='fa fa-edit'></i>
                                </a>
                                <a class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route('destroy-master-line') }}/`+row['id']+`' onclick='deleteData(this)'>
                                    <i class='fa fa-trash'></i>
                                </a>
                            </div>
                        `;
                    }
                },
                {
                    targets: "_all",
                    className: "text-nowrap"
                }
            ]
        });

        function datatableReload() {
            datatable.ajax.reload();
        }

        // CREATE SELECT2
        $("#line_id").on("change", function () {
            $("#line_name").val($("#line_id").find(":selected").text());
        })

        $("#chief_id").on("change", function () {
            $("#chief_nik").val($("#chief_id").find(":selected").attr("data-nik"));
            $("#chief_name").val($("#chief_id").find(":selected").attr("data-name"));
        })

        $("#leader_id").on("change", function () {
            $("#leader_nik").val($("#leader_id").find(":selected").attr("data-nik"));
            $("#leader_name").val($("#leader_id").find(":selected").attr("data-name"));
        })


        $("#ie_id").on("change", function () {
            $("#ie_nik").val($("#ie_id").find(":selected").attr("data-nik"));
            $("#ie_name").val($("#ie_id").find(":selected").attr("data-name"));
        })

        $("#leaderqc_id").on("change", function () {
            $("#leaderqc_nik").val($("#leaderqc_id").find(":selected").attr("data-nik"));
            $("#leaderqc_name").val($("#leaderqc_id").find(":selected").attr("data-name"));
        })

        $("#mechanic_id").on("change", function () {
            $("#mechanic_nik").val($("#mechanic_id").find(":selected").attr("data-nik"));
            $("#mechanic_name").val($("#mechanic_id").find(":selected").attr("data-name"));
        })

        $("#technical_id").on("change", function () {
            $("#technical_nik").val($("#technical_id").find(":selected").attr("data-nik"));
            $("#technical_name").val($("#technical_id").find(":selected").attr("data-name"));
        })

        // EDIT SELECT2
        $("#edit_line_id").on("change", function () {
            $("#edit_line_name").val($("#edit_line_id").find(":selected").text());
        })

        $("#edit_chief_id").on("change", function () {
            $("#edit_chief_nik").val($("#edit_chief_id").find(":selected").attr("data-nik"));
            $("#edit_chief_name").val($("#edit_chief_id").find(":selected").attr("data-name"));
        })

        $("#edit_leader_id").on("change", function () {
            $("#edit_leader_nik").val($("#edit_leader_id").find(":selected").attr("data-nik"));
            $("#edit_leader_name").val($("#edit_leader_id").find(":selected").attr("data-name"));
        })

        $("#edit_ie_id").on("change", function () {
            $("#edit_ie_nik").val($("#edit_ie_id").find(":selected").attr("data-nik"));
            $("#edit_ie_name").val($("#edit_ie_id").find(":selected").attr("data-name"));
        })

        $("#edit_leaderqc_id").on("change", function () {
            $("#edit_leaderqc_nik").val($("#edit_leaderqc_id").find(":selected").attr("data-nik"));
            $("#edit_leaderqc_name").val($("#edit_leaderqc_id").find(":selected").attr("data-name"));
        })

        $("#edit_mechanic_id").on("change", function () {
            $("#edit_mechanic_nik").val($("#edit_mechanic_id").find(":selected").attr("data-nik"));
            $("#edit_mechanic_name").val($("#edit_mechanic_id").find(":selected").attr("data-name"));
        })

        $("#edit_technical_id").on("change", function () {
            $("#edit_technical_nik").val($("#edit_technical_id").find(":selected").attr("data-nik"));
            $("#edit_technical_name").val($("#edit_technical_id").find(":selected").attr("data-name"));
        })

        function updateImage() {
            Swal.fire({
                icon: 'info',
                title: 'Konfirmasi',
                html: 'Update Gambar Karyawan?',
                confirmButtonText: "Update",
                showCancelButton: true,
            }).
            then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("loading").classList.remove("d-none");

                    $.ajax({
                        url: "{{ route("update-master-line-image") }}",
                        type: "post",
                        dataType: "json",
                        success: function (response) {
                            if (response.status == 200) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Berhasil",
                                    html: response.message
                                });
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Gagal",
                                    html: response.message
                                });
                            }

                            document.getElementById("loading").classList.add("d-none");
                        },
                        error: function(jqXHR) {
                            console.error(jqXHR);

                            document.getElementById("loading").classList.add("d-none");
                        }
                    });
                }
            });
        }

        async function exportExcel() {
            Swal.fire({
                title: "Exporting",
                html: "Please Wait...",
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            await $.ajax({
                url: "{{ route("export-master-line") }}",
                type: "post",
                data: {
                    from : $("#from").val(),
                    to : $("#to").val()
                },
                xhrFields: { responseType : 'blob' },
                success: function (res) {
                    Swal.close();

                    iziToast.success({
                        title: 'Success',
                        message: 'Success',
                        position: 'topCenter'
                    });

                    var blob = new Blob([res]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Production Team List "+$("#from").val()+" - "+$("#to").val()+".xlsx";
                    link.click();
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });

            Swal.close();
        }
    </script>
@endsection
