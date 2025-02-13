@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style type="text/css">
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            /* display: none; <- Crashes Chrome on hover */
            -webkit-appearance: none;
            margin: 0;
            /* <-- Apparently some margin are still there even though it's hidden */
        }

        input[type=number] {
            -moz-appearance: textfield;
            /* Firefox */
        }
    </style>
@endsection

@section('content')
    <form id="form" name='form' method='post' action="{{ route('store-packing-packing-in') }}"
        onsubmit="submitForm(this, event)">
        <div class="card card-secondary ">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-people-carry"></i> Input Penerimaan Packing</h5>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><small><b>No. Transaksi</b></small></label>
                    <select class="form-control select2bs4" id="cbono" name="cbono" style="width: 100%;"
                        onchange="dataTablePreviewReload()">
                        <option selected="selected" value="" disabled="true">Pilih No. Transaksi</option>
                        @foreach ($data_no_trans as $datanotrans)
                            <option value="{{ $datanotrans->isi }}">
                                {{ $datanotrans->tampil }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <label>Preview</label>
                <div class="table-responsive">
                    <table id="datatable_preview" class="table table-bordered table-striped table-sm w-100 text-nowrap">
                        <thead>
                            <tr>
                                <th>Line</th>
                                <th>PO</th>
                                <th>Barcode</th>
                                <th>WS</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Qty</th>
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
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> List Transaksi</h5>
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
                    <a onclick="export_excel_packing_in()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-sm w-100 text-nowrap">
                    <thead class="table-primary">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>No. Trans</th>
                            <th>Tgl. Trans</th>
                            <th>No. Trans Garment</th>
                            <th>Line</th>
                            <th>Barcode</th>
                            <th>PO</th>
                            <th>WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Dest</th>
                            <th>Qty</th>
                            <th>User</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="11"></th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_qty_chk'> </th>
                            <th>PCS</th>
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
            $("#cbono").val('').trigger('change');
            dataTablePreviewReload();
            startCalc();
        });

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
                    .column(11)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(11).footer()).html(sumTotal);
            },
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route('packing-in') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'no_trans'

                }, {
                    data: 'tgl_penerimaan_fix'
                },
                {
                    data: 'no_trf_garment'
                },
                {
                    data: 'line'
                },
                {
                    data: 'barcode'
                },
                {
                    data: 'po'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'dest'
                },
                {
                    data: 'qty'
                },
                {
                    data: 'created_by'
                },
                {
                    data: 'created_at'
                },
            ],
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
                url: '{{ route('show_preview_packing_in') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.cbono = $('#cbono').val();
                },
            },
            columns: [{
                    data: 'line',
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
                    data: 'id',
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
                        // return '<input type="text" id="txtqty' + meta.row + '" name="txtqty[' + meta
                        //     .row + ']" value = "0"  />'
                        // return '<input type="number" size="10" id="txtqty[' + row.kode +
                        //     ']" name="txtqty[' + row
                        //     .kode + ']" autocomplete="off" />'
                        return `
                        <div>
                            <input type="number" class="form-control form-control-sm input" style="width:75px" id="txtqty[` +
                            row
                            .id + `]"
                            name="txtqty[` + row.id + `]" value = "` + row.qty + `" autocomplete="off"  max = "` + row
                            .qty + `" min = "0" onFocus="startCalc();" onBlur="stopCalc();" />
                        </div>
                        <div>
                            <input type="hidden" size="4" id="id_trf_garment[` + row.id + `]"
                            name="id_trf_garment[` + row.id + `]" value = "` + row.id + `"/>
                            <input type="hidden" size="4" id="status[` + row.id + `]"
                            name="status[` + row.id + `]" value = "` + row.line + `"/>
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

        function undo() {
            $("#cbono").val('').trigger('change');
            dataTablePreviewReload();
            startCalc();
        }

        function export_excel_packing_in() {
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
                url: '{{ route('export_excel_packing_in') }}',
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
                        link.download = "Laporan Packing In " +
                            from + " sampai " +
                            to + ".xlsx";
                        link.click();

                    }
                },
            });
        }
    </script>
@endsection
