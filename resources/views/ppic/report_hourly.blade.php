@extends('layouts.index')

@section('custom-link')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/fixedColumns.bootstrap4.min.css') }}">
    <!-- jQuery -->
    <script src="{{ asset('plugins/datatables 2.0/jquery-3.3.1.js') }}"></script>

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
                <div class="mb-3">
                    <div id="last-updated" class="text-muted" style="font-size: small;"></div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-sm w-100 text-nowrap">
                    <thead class="table-primary">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>Line</th>
                            <th>Chief</th>
                            <th>Leader</th>
                            <th>Style</th>
                            <th>Product</th>
                            <th>SMV</th>
                            <th>MP</th>
                            <th>Jml Hr</th>
                            <th>Eff H-2</th>
                            <th>Eff H-1</th>
                            <th>JK</th>
                            <th>Eff 100 %</th>
                            <th>Eff</th>
                            <th>Target</th>
                            <th>Target/H</th>
                            <th>Target/J</th>
                            <th>JK Akt</th>
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
                            <th>Output</th>
                            <th>Eff</th>
                            <th>Eff Line</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="5"> Total </th>
                            <th></th>
                            <th></th>
                            <th colspan="4"></th>
                            <th>2</th>
                            <th></th>
                            <th>4</th>
                            <th>5</th>
                            <th></th>
                            <th></th>
                            <th>8</th>
                            <th>9</th>
                            <th>10</th>
                            <th>11</th>
                            <th>12</th>
                            <th>13</th>
                            <th>14</th>
                            <th>15</th>
                            <th>16</th>
                            <th>17</th>
                            <th>18</th>
                            <th>19</th>
                            <th>20</th>
                            <th>21</th>
                            <th colspan = '2'></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
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
            setInterval(dataTableReload, 300000);
            // 300000
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        function dataTableReload() {

            // Update the "Last Updated" message

            let now = new Date();

            let options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            };

            document.getElementById('last-updated').innerText = 'Last Updated: ' + now.toLocaleString('en-US', options);


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
                    leftColumns: 5 // Fix the first three columns
                },
                ajax: {
                    url: '{{ route('report-hourly') }}',
                    data: function(d) {
                        d.tgl_filter = $('#tgl_filter').val(); // Send the selected date to the server
                        console.log(d.tgl_filter); // Debugging: log the filter date
                    },
                },
                columns: [{
                        data: 'sewing_line'
                    },
                    {
                        data: 'nm_chief'
                    },
                    {
                        data: 'nm_leader'
                    },
                    {
                        data: 'styleno_prod'
                    },
                    {
                        data: 'product_group'
                    },
                    {
                        data: 'smv'
                    },
                    {
                        data: 'man_power'
                    },
                    {
                        data: 'tot_days'
                    },
                    {
                        data: 'kemarin_2'
                    },
                    {
                        data: 'kemarin_1'
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

                drawCallback: function(settings) {
                    var api = this.api();

                    var intVal = function(i) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                            i : 0;
                    };
                    // Compute column Total of the complete result
                    // Create an object to store unique man_power for each sewing_line
                    let uniqueManPower = {};
                    // Loop through the data to populate the uniqueManPower object
                    api.rows({
                        search: 'applied'
                    }).every(function() {
                        var data = this.data();
                        // Get the sewing_line and man_power
                        let line = data.sewing_line;
                        let power = intVal(data.man_power);
                        // If the line does not exist in the object, add it
                        if (!uniqueManPower[line]) {
                            uniqueManPower[line] = power;
                        }
                    });
                    // Calculate the total of unique man_power values
                    var totalUniqueManPower = Object.values(uniqueManPower).reduce((a, b) => a + b, 0);
                    // Update footer for the total unique man_power
                    $(api.column(6).footer()).html(
                        totalUniqueManPower); // Assuming man_power is in the 7th column (zero-based index 6)
                    // Your existing sum calculations...
                    var sumTotalA = api
                        .column(11, {
                            search: 'applied'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    // Update other footer calculations as needed...
                    $(api.column(13).footer()).html(sumTotalA);


                    var sumTotalB = api
                        .column(13, {
                            search: 'applied'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    var sumTotalC = api
                        .column(14, {
                            search: 'applied'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    var sumTotal_1 = api
                        .column(17, {
                            search: 'applied'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    var sumTotal_2 = api
                        .column(18, {
                            search: 'applied'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    var sumTotal_3 = api
                        .column(19, {
                            search: 'applied'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    var sumTotal_4 = api
                        .column(20, {
                            search: 'applied'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    var sumTotal_5 = api
                        .column(21, {
                            search: 'applied'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    var sumTotal_6 = api
                        .column(22, {
                            search: 'applied'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    var sumTotal_7 = api
                        .column(23, {
                            search: 'applied'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    var sumTotal_8 = api
                        .column(24, {
                            search: 'applied'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    var sumTotal_9 = api
                        .column(25, {
                            search: 'applied'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    var sumTotal_10 = api
                        .column(26, {
                            search: 'applied'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    var sumTotal_11 = api
                        .column(27, {
                            search: 'applied'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    var sumTotal_12 = api
                        .column(28, {
                            search: 'applied'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    var sumTotal_13 = api
                        .column(29, {
                            search: 'applied'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    var sumTotal_tot_out = api
                        .column(30, {
                            search: 'applied'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Update footer for the "MP" column

                    $(api.column(11).footer()).html(sumTotalA);
                    $(api.column(13).footer()).html(sumTotalB);
                    $(api.column(14).footer()).html(sumTotalC);
                    $(api.column(17).footer()).html(sumTotal_1);
                    $(api.column(18).footer()).html(sumTotal_2);
                    $(api.column(19).footer()).html(sumTotal_3);
                    $(api.column(20).footer()).html(sumTotal_4);
                    $(api.column(21).footer()).html(sumTotal_5);
                    $(api.column(22).footer()).html(sumTotal_6);
                    $(api.column(23).footer()).html(sumTotal_7);
                    $(api.column(24).footer()).html(sumTotal_8);
                    $(api.column(25).footer()).html(sumTotal_9);
                    $(api.column(26).footer()).html(sumTotal_10);
                    $(api.column(27).footer()).html(sumTotal_11);
                    $(api.column(28).footer()).html(sumTotal_12);
                    $(api.column(29).footer()).html(sumTotal_13);
                    $(api.column(30).footer()).html(sumTotal_tot_out);

                },


                createdRow: function(row, data, dataIndex) {

                    $(row).find('td').css('font-weight', 'bold');

                    // Check the value of eff_skrg

                    if (data.eff_skrg_angka < 85) {

                        // Apply a class to change the font color

                        $('td:eq(32)', row).css({

                            'color': 'red',

                            'font-weight': 'bold'

                        });

                    } else {
                        $('td:eq(32)', row).css({

                            'color': 'green',

                            'font-weight': 'bold'

                        });
                    }

                    if (data.eff_line_angka < 85) {

                        // Apply a class to change the font color

                        $('td:eq(31)', row).css({

                            'color': 'red',

                            'font-weight': 'bold'

                        });

                    } else {
                        $('td:eq(31)', row).css({

                            'color': 'green',

                            'font-weight': 'bold'

                        });
                    }

                    if (data.kemarin_2_angka < 85) {

                        // Apply a class to change the font color

                        $('td:eq(8)', row).css({

                            'color': 'red',

                            'font-weight': 'bold'

                        });

                    } else {
                        $('td:eq(8)', row).css({

                            'color': 'green',

                            'font-weight': 'bold'

                        });
                    }

                    if (data.kemarin_1_angka < 85) {

                        // Apply a class to change the font color

                        $('td:eq(9)', row).css({

                            'color': 'red',

                            'font-weight': 'bold'

                        });

                    } else {
                        $('td:eq(9)', row).css({

                            'color': 'green',

                            'font-weight': 'bold'

                        });
                    }

                },
                rowsGroup: [
                    31 // Adjust this index to the correct column (zero-based)
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
