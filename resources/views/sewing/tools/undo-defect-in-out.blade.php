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
                <i class="fa fa-solid fa-rotate fa-sm"></i> Undo Defect In Out
            </h5>
        </div>
        <div class="card-body">
            <div class="form-group mt-3">
                <label class='form-label'>Department</label>
                <select class="form-select" name="department" id="department">
                    <option value="qc">QC</option>
                    <option value="packing">FINISHING</option>
                </select>
            </div>
            <label class="form-label">Kode Numbering :</label>
            <textarea class="form-control" name="kode_numbering" id="kode_numbering" cols="30" rows="10"></textarea>
            <div class="form-text">Contoh : <br>&nbsp;&nbsp;&nbsp;<b> {{ date("Y") }}_1_1</b><br>&nbsp;&nbsp;&nbsp;<b> {{ date("Y") }}_1_2</b><br>&nbsp;&nbsp;&nbsp;<b> {{ date("Y") }}_1_3</b></div>
        </div>

        <div class="card-footer border-1">
            <button class="btn btn-sb btn-block" onclick="listTableReload()"><i class="fa fa-solid fa-search"></i> Search</button>
        </div>
    </div>
    <div class="card">
        <div class="card-header bg-sb-secondary">
            <h5 class="card-title">
                <i class="fas fa-list fa-sm"></i> List Data
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm" id="list-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Type</th>
                            <th>WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Status</th>
                            <th>Line</th>
                            <th>Waktu</th>
                            <th>Defect Type</th>
                            <th>Defect Area</th>
                            <th>Alokasi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer border-1">
            <button type="button" class="btn btn-sb-secondary btn-block" onclick="undoDefectSubmit()"><i class="fa fa-rotate"></i> UNDO</button>
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
            theme: 'bootstrap4'
        })

        let listTable = $("#list-table").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('get-defect-in-out') }}',
                dataType: 'json',
                dataSrc: 'data',
                scrollY: '400px',
                data: function(d) {
                    d.kode_numbering = $('#kode_numbering').val();
                    d.department = $('#department').val();
                },
            },
            columns: [
                { data: "kode_numbering" },
                { data: "output_type" },
                { data: "ws" },
                { data: "style" },
                { data: "color" },
                { data: "size" },
                { data: "status" },
                { data: "sewing_line" },
                { data: "time_out" },
                { data: "defect_type" },
                { data: "defect_area" },
                { data: "allocation" }
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap",
                    render: (data, type, row, meta) => data
                },
            ]
        });

        function listTableReload() {
            listTable.ajax.reload();
        }

        function undoDefectSubmit() {
            Swal.fire({
                title: 'UNDO Defect IN/OUT',
                html: '<span class="text-danger"><b>Critical</b></span> <br> Yakin akan UNDO DEFECT IN/OUT ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'UNDO',
                cancelButtonText: 'BATAL',
                confirmButtonColor: "#dc3545"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("loading").classList.remove("d-none");

                    $.ajax({
                        url: "{{ route('undo-defect-in-out-submit') }}",
                        type: "post",
                        data: {
                            kode_numbering: $("#kode_numbering").val(),
                            department: $("#department").val()
                        },
                        dataType: "json",
                        success: function (res) {
                            document.getElementById("loading").classList.add("d-none");

                            console.log(res);

                            if (res.length > 0) {
                                let message = res.join("");

                                Swal.fire({
                                    icon: 'info',
                                    title: 'Info',
                                    html: message,
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                    confirmButtonColor: "#082149",
                                });

                                listTableReload();
                            }
                        }
                    });
                }
            });
        }
    </script>
@endsection
