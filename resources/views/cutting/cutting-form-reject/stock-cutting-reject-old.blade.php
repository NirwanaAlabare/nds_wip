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
            <h5 class="card-title fw-bold"><i class="fa-solid fa-receipt"></i> Stock Ganti Reject</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end mb-3">
                <div class="d-flex justify-content-start align-items-end gap-3">
                    <div>
                        <label class="form-label">Dari</label>
                        <input type="date" class="form-control" value="{{ date("Y-m-d") }}" id="date-from" name="dateFrom" onchange="stockCuttingRejectTableReload()">
                    </div>
                    <div>
                        <label class="form-label">Sampai</label>
                        <input type="date" class="form-control" value="{{ date("Y-m-d") }}" id="date-to" name="dateTo" onchange="stockCuttingRejectTableReload()">
                    </div>
                    <div>
                        <button class="btn btn-sb" onclick="stockCuttingRejectTableReload()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div class="d-flex gap-1">
                    {{-- <a href="{{ route('create-cutting-reject') }}" class="btn btn-sb"><i class="fa fa-plus"></i> Baru</a> --}}
                    {{-- <button class="btn btn-success" onclick="exportExcel()"><i class="fa fa-file-excel"></i> Export</a> --}}
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered" id="stock-cutting-reject-table">
                    <thead>
                        <th>Action</th>
                        <th>Tanggal</th>
                        <th>No. Stocker</th>
                        <th>No. Form</th>
                        <th>No. WS</th>
                        <th>Style</th>
                        <th>Color</th>
                        <th>Panel</th>
                        <th>Group</th>
                        <th>Size</th>
                        <th>Part</th>
                        <th>Qty</th>
                        <th>Note</th>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
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
        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#editMejaModal")
        })

        let stockcuttingRejectTable = $("#stock-cutting-reject-table").DataTable({
            processing: true,
            ordering: false,
            serverSide: true,
            ajax: {
                url: '{{ route('stock-cutting-reject') }}',
                data: function(d) {
                    d.dateFrom = $('#date-from').val();
                    d.dateTo = $('#date-to').val();
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
                    data: 'id_qr_stocker'
                },
                {
                    data: 'no_form'
                },
                {
                    data: 'act_costing_ws'
                },
                {
                    data: 'style'
                },
                {
                    data: 'color'
                },
                {
                    data: 'panel'
                },
                {
                    data: 'group_reject'
                },
                {
                    data: 'size'
                },
                {
                    data: 'part'
                },
                {
                    data: 'qty'
                },
                {
                    data: 'notes'
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        let buttonPrint = `<button class="btn btn-sb btn-sm" onclick="printStocker(`+data+`)"><i class="fa fa-print"></i></buttton>`;

                        return buttonPrint;
                    }
                },
                {
                    targets: [3],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        let buttonDetail = `<a href="{{ route('show-cutting-reject') }}/`+row.form_reject_id+`" class="fw-bold" target="_blank">`+data+`</i></a>`;

                        return buttonDetail;
                    }
                },
                {
                    targets: "_all",
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        return data;
                    }
                }
            ]
        });

        function stockCuttingRejectTableReload() {
            $("#stock-cutting-reject-table").DataTable().ajax.reload();
        }

        function exportExcel() {
            console.log("js");
        }

        function printStocker(id) {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: '{{ route('print-stocker-reject') }}/'+id,
                type: 'post',
                processData: false,
                contentType: false,
                xhrFields:
                {
                    responseType: 'blob'
                },
                success: function(res) {
                    if (res) {
                        console.log(res);

                        var blob = new Blob([res], {type: 'application/pdf'});
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Stocker-"+id+".pdf";
                        link.click();

                        swal.close();

                        window.location.reload();
                    }
                    generating = false;
                },
                error: function(jqXHR) {
                    console.log(jqXHR);

                    generating = false;
                }
            });
        }
    </script>
@endsection
