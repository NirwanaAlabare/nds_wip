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
        div.dataTables_wrapper div.dataTables_processing {
            top: 15%;
        }
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header bg-sb text-light">
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-file"></i> REPORT TERIMA SECONDARY LUAR </h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div>
                        <label class="form-label"><small>Tanggal Awal</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" value="">
                    </div>
                    <div>
                        <label class="form-label"><small>Tanggal Akhir</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="">
                    </div>
                    <div>
                        <button class="btn btn-primary btn-sm" onclick="datatableReportDC()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <button class="btn btn-sm btn-success mb-3" onclick="exportExcel()"><i class="fa fa-file-excel"></i> Export</button>
            </div>
            <div class="table-responsive">
                <table id="datatable-dc-report" class="table table-bordered table w-100">
                    <thead>
                        <tr>
                            <th class="text-center">Tanggal</th>
                            <th class="text-center">No. WS</th>
                            <th class="text-center">Buyer</th>
                            <th class="text-center">Style</th>
                            <th class="text-center">Color</th>
                            <th class="text-center">Size</th>
                            <th class="text-center">Panel</th>
                            <th class="text-center">Part</th>
                            <th class="text-center">Qty</th>
                        </tr>
                    </thead>
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
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // let today = new Date(new Date().setDate(new Date().getDate()));
            // let todayDate = ("0" + today.getDate()).slice(-2);
            // let todayMonth = ("0" + (today.getMonth() + 1)).slice(-2);
            // let todayYear = today.getFullYear();
            // let todayFull = todayYear + '-' + todayMonth + '-' + todayDate;

            // $("#tgl-awal").val(todayFull).trigger("change");

            window.addEventListener("focus", () => {
                // $('#datatable-dc-report').DataTable().ajax.reload(null, false);
            });
        });


        let datatableDcReport = $("#datatable-dc-report").DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            scrollX: true,
            scrollY: "60vh",
            scrollCollapse: true,
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            ajax: {
                url: '{{ route('dc-report-terima-secondary-luar') }}',
                data: function (d) {
                    d.dateFrom = $("#tgl-awal").val();
                    d.dateTo = $("#tgl-akhir").val();
                }
            },
            columns: [
                { data: 'tanggal' },
                { data: 'no_ws' },
                { data: 'buyer' },
                { data: 'style' },
                { data: 'color' },
                { data: 'size' },
                { data: 'panel' },
                { data: 'part' },
                { data: 'qty' },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: 'align-middle text-nowrap'
                },
            ],
        });


        function datatableReportDC() {
            let start_date = $('#tgl-awal').val();
            let end_date = $('#tgl-akhir').val();

            if (start_date && end_date) {
                Swal.fire({
                    title: 'Loading...',
                    text: 'Please wait while data is loading.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            }

            datatableDcReport.ajax.reload(function () {
                Swal.close();
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
                url: "{{ route("export_excel_report_terima_secondary_luar") }}",
                type: "post",
                data: {
                    from : $("#tgl-awal").val(),
                    to : $("#tgl-akhir").val(),
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
                    link.download = "Report Terima Secondary Luar "+$("#tgl-awal").val()+" - "+$("#tgl-akhir").val()+".xlsx";
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
