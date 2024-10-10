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
    <form id="form" name='form' method='post' action="{{ route('store-fg-in') }}"
        onsubmit="submitForm(this, event)">
        <div class="card card-primary ">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-user-check"></i> Input Penerimaan Finish Good</h5>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label class="form-label"><small><b>No. PO</b></small></label>
                            <select class="form-control select2bs4 form-control-sm" id="cbopo" name="cbopo"
                                style="width: 100%;" onchange="getno_carton();dataTablePreviewReload();">
                                <option selected="selected" value="" disabled="true">Pilih No. PO</option>
                                @foreach ($data_po as $datapo)
                                    <option value="{{ $datapo->isi }}">
                                        {{ $datapo->tampil }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label"><small><b>No. Carton</b></small></label>
                            <select class='form-control select2bs4 form-control-sm rounded' style='width: 100%;'
                                name='cbo_no_carton' id='cbo_no_carton' onchange="dataTablePreviewReload()"></select>
                        </div>
                    </div>
                </div>
                <label>Preview</label>
                <div class="table-responsive">
                    <table id="datatable_preview" class="table table-bordered table-striped table-sm w-100 text-nowrap">
                        <thead>
                            <tr>
                                <th>No. Carton</th>
                                <th>PO</th>
                                <th>Barcode</th>
                                <th>WS</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Qty Sisa</th>
                                <th>Input</th>
                                <th>Unit</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th colspan="6"></th>
                                <th></th>
                                <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                        id = 'total_qty_chk'> </th>
                                <th>PCS</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="d-flex justify-content-between">
                    <div class="p-2 bd-highlight">
                        <a class="btn btn-outline-warning" onclick="undo()">
                            <i class="fas fa-sync-alt
                fa-spin"></i>
                            Undo
                        </a>
                    </div>
                    <div class="p-2 bd-highlight">
                        <button type="submit" class="btn btn-outline-success"><i class="fas fa-check"></i> Simpan </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="card card-primary">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-people-carry"></i> Penerimaan Finish Good</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        oninput="dataTableReload()" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        oninput="dataTableReload()" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <a onclick="export_excel_fg_in_data()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel List
                    </a>
                </div>
                <div class="mb-3">
                    <a onclick="export_excel_fg_in_data()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel Summary
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-sm w-100 text-nowrap">
                    <thead class="table-success">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>No. SB</th>
                            <th>Tgl. Trans</th>
                            <th>PO</th>
                            <th>Buyer</th>
                            <th>WS</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Qty</th>
                            <th>No. Carton</th>
                            <th>Notes</th>
                            <th>User</th>
                            <th>Tgl. Input</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="7"></th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_qty_chk'> </th>
                            <th>PCS</th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
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
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
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
        });
    </script>
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        $(document).ready(() => {
            dataTableReload();
            $("#cbopo").val('').trigger('change');
            startCalc();
        });

        function getno_carton() {
            let cbopo = document.form.cbopo.value;
            let html = $.ajax({
                type: "GET",
                url: '{{ route('fg_in_getno_carton') }}',
                data: {
                    cbopo: cbopo
                },
                async: false
            }).responseText;
            if (html != "") {
                $("#cbo_no_carton").html(html);
            }
        };


        function dataTableReload() {
            datatable.ajax.reload();
        }

        function dataTablePreviewReload() {
            datatable_preview.ajax.reload();
        }

        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm"/>');
            $('input', this).on('keyup change', function() {
                if (datatable.column(i).search() !== this.value) {
                    datatable
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        let datatable = $("#datatable").DataTable({
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api(),
                    data;

                // converting to interger to find total
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };

                // computing column Total of the complete result
                var sumTotal = api
                    .column(7)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(7).footer()).html(sumTotal);
            },


            ordering: false,
            processing: true,
            serverSide: true,
            paging: true,
            lengthMenu: [
                [5, 50, 100, -1],
                [5, 50, 100, 'All']
            ],
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route('finish_good_penerimaan') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'no_sb'

                }, {
                    data: 'tgl_penerimaan_fix'
                },
                {
                    data: 'po'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'qty'
                },
                {
                    data: 'no_carton'
                },
                {
                    data: 'notes'
                },
                {
                    data: 'created_by'
                },
                {
                    data: 'created_at'
                },
            ],

            columnDefs: [{
                    "className": "align-left",
                    "targets": "_all"
                },
                // {
                //     targets: '_all',
                //     className: 'text-nowrap',
                //     render: (data, type, row, meta) => {
                //         if (row.tujuan == 'Temporary') {
                //             color = ' #d68910';
                //         } else if (row.status == 'Full' && row.tujuan != 'Temporary' && row.line !=
                //             'Temporary') {
                //             color = '#087521';
                //         } else if (row.status != 'Full' && row.tujuan != 'Temporary' && row.line !=
                //             'Temporary') {
                //             color = 'blue';
                //         } else if (row.status != 'Full' && row.tujuan != 'Temporary' && row.line !=
                //             'Temporary') {
                //             color = 'blue';
                //         } else if (row.status != 'Full' && row.tujuan != 'Temporary' && row.line !=
                //             'Temporary') {
                //             color = 'blue';
                //         } else if (row.status != 'Full' && row.line == 'Temporary') {
                //             color = 'purple';
                //         } else if (row.status == 'Full' && row.line == 'Temporary') {
                //             color = 'green';
                //         }
                //         return '<span style="font-weight: 600; color:' + color + '">' + data + '</span>';
                //     }
                // },

            ]

        });

        let datatable_preview = $("#datatable_preview").DataTable({
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api(),
                    data;

                // converting to interger to find total
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };

                // computing column Total of the complete result
                var sumTotal = api
                    .column(6)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(6).footer()).html(sumTotal);
            },
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            destroy: true,
            autoWidth: true,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('show_preview_fg_in') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.cbopo = $('#cbopo').val();
                    d.cbo_no_carton = $('#cbo_no_carton').val();
                },
            },
            columns: [{
                    data: 'no_carton',
                },
                {
                    data: 'po',
                },
                {
                    data: 'barcode',
                },
                {
                    data: 'ws',
                },
                {
                    data: 'color',
                },
                {
                    data: 'size',
                },
                {
                    data: 'qty',
                },
                {
                    data: 'id_so_det',
                },
                {
                    data: 'unit',
                },
            ],

            columnDefs: [{
                    "className": "align-middle",
                    "targets": "_all"
                },
                {
                    targets: [7],
                    render: (data, type, row, meta) => {
                        return `
                        <div>
                            <input type="number" class="form-control form-control-sm input" style="width:75px" id="txtqty[` +
                            row
                            .id_so_det + `]"
                            name="txtqty[` + row.id_so_det + `]" value = "` + row.qty +
                            `" autocomplete="off"  max = "` + row
                            .qty + `" min = "0" onFocus="startCalc();" onBlur="stopCalc();" />
                        </div>
                        <div>
                            <input type="hidden" size="4" id="id_so_det[` + row.id_so_det + `]"
                            name="id_so_det[` + row.id_so_det + `]" value = "` + row.id_so_det + `"/>
                            <input type="hidden" size="4" id="price[` + row.id_so_det + `]"
                            name="price[` + row.id_so_det + `]" value = "` + row.price + `"/>
                            <input type="hidden" size="4" id="curr[` + row.id_so_det + `]"
                            name="curr[` + row.id_so_det + `]" value = "` + row.curr + `"/>
                            <input type="hidden" size="4" id="id_ppic_master_so[` + row.id_so_det + `]"
                            name="id_ppic_master_so[` + row.id_so_det + `]" value = "` + row.id_ppic_master_so + `"/>
                            <input type="hidden" size="4" id="barcode[` + row.id_so_det + `]"
                            name="barcode[` + row.id_so_det + `]" value = "` + row.barcode + `"/>
                        </div>
                        `;
                    }
                },
            ]

        });

        function startCalc() {
            interval = setInterval('findTotal()', 1);
        }

        function findTotal() {
            var arr = document.getElementsByClassName('form-control form-control-sm input');
            var tot = 0;
            for (var i = 0; i < arr.length; i++) {
                if (parseInt(arr[i].value))
                    tot += parseInt(arr[i].value);
            }
            document.getElementById('total_qty_chk').value = tot;
        }

        function stopCalc() {
            clearInterval(interval);
        }


        function export_excel_fg_in_data() {
            let from = document.getElementById("tgl-awal").value;
            let to = document.getElementById("tgl-akhir").value;

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
                url: '{{ route('export_excel_fg_in_list') }}',
                data: {
                    from: from,
                    to: to
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
                        link.download = "Laporan FG IN " + from + " sampai " +
                            to + ".xlsx";
                        link.click();

                    }
                },
            });
        }
    </script>
@endsection
