@extends('layouts.index')

@section('custom-link')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <link href="https://cdn.datatables.net/2.0.3/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/fixedcolumns/5.0.0/css/fixedColumns.bootstrap4.min.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.3.1.js"></script>

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        /* Custom styles for the table */

        .table-bordered {

            border: 1px solid black;
            /* Change thickness of the outer border */

        }

        .table-bordered th,
        .table-bordered td {

            border: 1px solid black;
            /* Change thickness of inner borders */

        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-chart-area"></i> Hourly Output</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Filter</b></small></label>
                    <input type="date" class="form-control form-control " id="tgl_filter" name="tgl_filter"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <a onclick="dataTableReload()" class="btn btn-outline-primary position-relative">
                        <i class="fas fa-search fa-sm"></i>
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-sm w-100 text-nowrap">
                    <thead class="table-primary">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>Tgl. Input</th>
                            <th>Line</th>
                            <th>WS</th>
                            <th>Buyer</th>
                            <th>Style</th>
                            <th>SMV</th>
                            <th>MP</th>
                            <th>Jumlah Hari</th>
                            <th>Eff Kemarin (1)</th>
                            <th>Eff Kemarin (2)</th>
                            <th>Jam Kerja</th>
                            <th>Target Eff 100 %</th>
                            <th>Target Eff</th>
                            <th>Target Output Eff</th>
                            <th>Target PerHari</th>
                            <th>Target Perjam</th>
                            <th>Jam Kerja Act</th>
                            <th>1</th>
                            <th>2</th>
                            <th>3</th>
                            <th>4</th>
                            <th>5</th>
                            <th>6</th>
                            <th>7</th>
                            <th>8</th>
                            <th>9</th>
                            <th>10</th>
                            <th>11</th>
                            <th>12</th>
                            <th>13</th>
                            <th>Total Output</th>
                            <th>Eff</th>
                            <th>Eff Line</th>
                        </tr>
                    </thead>
                    <tbody>

                        <!-- Data will be populated here by DataTables -->

                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="https://cdn.datatables.net/2.0.3/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.3/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/5.0.0/js/dataTables.fixedColumns.min.js"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>

    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            containerCssClass: 'form-control-sm rounded'
        });
    </script>
    <script>
        $(document).ready(() => {
            dataTableReload();
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        function dataTableReload() {
            // Check if DataTable is already initialized
            if ($.fn.DataTable.isDataTable('#datatable')) {
                // Destroy the existing DataTable
                $('#datatable').DataTable().destroy();
            }

            // Re-initialize the DataTable
            datatable = $("#datatable").DataTable({
                scrollY: "450px",
                scrollX: true,
                scrollCollapse: true,
                paging: false,
                ordering: false,
                fixedColumns: {
                    leftColumns: 3 // Fix the first three columns
                },
                ajax: {
                    url: '{{ route('report-hourly') }}',
                    data: function(d) {
                        d.tgl_filter = $('#tgl_filter').val(); // Send the selected date to the server
                        console.log(d.tgl_filter); // Debugging: log the filter date
                    },
                },
                columns: [{
                        data: 'tgl_trans_fix'
                    },
                    {
                        data: 'sewing_line'
                    },
                    {
                        data: 'kpno'
                    },
                    {
                        data: 'buyer'
                    },
                    {
                        data: 'styleno'
                    },
                    {
                        data: 'man_power'
                    },
                    {
                        data: 'smv'
                    },
                    {
                        data: 'tot_days'
                    },
                    {
                        data: 'kemarin_1'
                    },
                    {
                        data: 'kemarin_2'
                    },
                    {
                        data: 'jam_kerja'
                    },
                    {
                        data: 'target_100'
                    },
                    {
                        data: 'target_effy'
                    },
                    {
                        data: 'target_output_eff'
                    },
                    {
                        data: 'set_target_perhari'
                    },
                    {
                        data: 'plan_target_perjam'
                    },
                    {
                        data: 'jam_kerja_act'
                    },
                    {
                        data: 'o_jam_1'
                    },
                    {
                        data: 'o_jam_2'
                    },
                    {
                        data: 'o_jam_3'
                    },
                    {
                        data: 'o_jam_4'
                    },
                    {
                        data: 'o_jam_5'
                    },
                    {
                        data: 'o_jam_6'
                    },
                    {
                        data: 'o_jam_7'
                    },
                    {
                        data: 'o_jam_8'
                    },
                    {
                        data: 'o_jam_9'
                    },
                    {
                        data: 'o_jam_10'
                    },
                    {
                        data: 'o_jam_11'
                    },
                    {
                        data: 'o_jam_12'
                    },
                    {
                        data: 'o_jam_13'
                    },
                    {
                        data: 'tot_output'
                    },
                    {
                        data: 'eff_line'
                    },
                    {
                        data: 'eff_skrg'
                    }
                ],
                columnDefs: [{
                    "className": "align-middle",
                    "targets": "_all"
                }],
                rowsGroup: [
                    32 // Adjust this index to the correct column (zero-based)
                ]
            });
        }



        function export_excel_tracking() {
            let buyer = document.getElementById("cbobuyer").value;
            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "get",
                url: '{{ route('export_excel_tracking') }}',
                data: {
                    buyer: buyer
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    {
                        swal.close();
                        Swal.fire({
                            title: 'Data Sudah Di Export!',
                            icon: "success",
                            showConfirmButton: true,
                            allowOutsideClick: false
                        });
                        var blob = new Blob([response]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Laporan Tracking " + buyer + ".xlsx";
                        link.click();

                    }
                },
            });
        }
    </script>
@endsection
