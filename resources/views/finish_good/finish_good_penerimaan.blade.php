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
                            <select class="form-control form-control-sm" id="cbopo" name="cbopo"
                                style="width: 100%;"></select>
                        </div>
                    </div>

                </div>
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label class="mb-0">Preview</label>
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold">Total Karton Dipilih :</span>
                            <input type="text" id="total_carton_selected"
                                class="form-control form-control-sm text-center fw-bold" style="width:70px;" readonly
                                value="0">
                            <span class="fw-bold">Karton</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold">Total Qty Dipilih :</span>
                            <input type="text" id="total_qty_selected"
                                class="form-control form-control-sm text-center fw-bold" style="width:90px;" readonly
                                value="0">
                            <span class="fw-bold">PCS</span>
                        </div>
                    </div>
                </div>
                <div class="position-relative">
                    <div id="preview-loading"
                        class="d-none position-absolute w-100 h-100 d-flex align-items-center justify-content-center"
                        style="top:0;left:0;background:rgba(255,255,255,0.75);z-index:10;">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                            <div class="mt-1 small fw-bold text-primary">Memuat data...</div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="datatable_preview" class="table table-bordered w-100 text-nowrap">
                            <thead>
                                <tr>
                                    <th>
                                        <input class="form-check checkbox-xl" type="checkbox" onclick="togglePreview(this);"
                                            checked>
                                    </th>
                                    <th>No. Carton</th>
                                    <th>PO</th>
                                    <th>Barcode</th>
                                    <th>WS</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th>Dest</th>
                                    <th>Qty Sisa</th>
                                    <th>Input</th>
                                    <th>Unit</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th colspan="8"></th>
                                    <th></th>
                                    <th> <input type = 'text' class="form-control form-control-sm" style="width:75px"
                                            readonly id = 'total_qty_chk'> </th>
                                    <th>PCS</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <div class="p-2 bd-highlight">
                        <a class="btn btn-outline-warning" onclick="undo()">
                            <i class="fas fa-sync-alt fa-spin"></i>
                            Undo
                        </a>
                    </div>
                    <div class="p-2 bd-highlight">
                        <button type="button" onclick="confirmAndSave()" class="btn btn-outline-success"><i
                                class="fas fa-check"></i> Simpan </button>
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
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label d-block"><small>&nbsp;</small></label>
                    <a onclick="dataTableReload()" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-search fa-sm"></i>
                        Cari
                    </a>
                </div>
                <div class="mb-3">
                    <label class="form-label d-block"><small>&nbsp;</small></label>
                    <a onclick="export_excel_list()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel List
                    </a>
                </div>
                <div class="mb-3">
                    <label class="form-label d-block"><small>&nbsp;</small></label>
                    <a onclick="export_excel_summary()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel Summary
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped w-100 text-nowrap">
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
                            <th>Dest</th>
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
    <style>
        .checkbox-xl .form-check-input {
            scale: 1.5;
        }
    </style>
    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // PO dropdown — AJAX search, tidak load semua data sekaligus
        $('#cbopo').select2({
            theme: 'bootstrap4',
            placeholder: 'Ketik untuk cari No. PO...',
            allowClear: true,
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('fg_in_search_po') }}',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return {
                        q: params.term || ''
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.results
                    };
                },
                cache: true,
            },
        }).on('change', function() {
            dataTablePreviewReload();
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
            startCalc();
        });

        function dataTableReload() {
            datatable.ajax.reload();
        }

        function dataTablePreviewReload() {
            $('#preview-loading').removeClass('d-none').addClass('d-flex');
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
                    data: 'dest'
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

                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };

                var sumTotal = api
                    .column(8)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                $(api.column(1).footer()).html('Total');
                $(api.column(8).footer()).html(sumTotal);
            },
            drawCallback: function() {
                $('#preview-loading').removeClass('d-flex').addClass('d-none');
                findTotal();
            },
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            destroy: true,
            autoWidth: false,
            scrollY: '400px',
            scrollX: true,
            scrollCollapse: true,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('show_preview_fg_in') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.cbopo = $('#cbopo').val();
                },
            },
            columns: [{
                    data: null,
                    orderable: false,
                },
                {
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
                    data: 'dest',
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

            createdRow: function(row, data, dataIndex) {
                if (parseInt(data.qty) > 0) {
                    $(row).css('background-color', '#cfe2ff');
                }
            },
            columnDefs: [{
                    "className": "align-middle",
                    "targets": "_all"
                },
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        let ck = row.no_carton + '__' + row.id_so_det;
                        let isZero = row.qty == 0;
                        return `
                        <div class="form-check checkbox-xl" style="text-align:center">
                            <input class="form-check-input row-check" type="checkbox"
                                data-ck="${ck}" ${isZero ? 'disabled' : 'checked'}>
                        </div>`;
                    }
                },
                {
                    targets: [9],
                    render: (data, type, row, meta) => {
                        let ck = row.no_carton + '__' + row.id_so_det;
                        let isZero = row.qty == 0;
                        let dis = isZero ? 'disabled' : '';
                        return `
                        <div>
                            <input type="number" class="form-control form-control-sm input" style="width:75px"
                            id="txtqty[${ck}]" name="txtqty[${ck}]"
                            value="${row.qty}" autocomplete="off" readonly ${dis}/>
                        </div>
                        <div>
                            <input type="hidden" name="id_so_det[${ck}]" value="${row.id_so_det}" ${dis}/>
                            <input type="hidden" name="price[${ck}]" value="${row.price}" ${dis}/>
                            <input type="hidden" name="curr[${ck}]" value="${row.curr}" ${dis}/>
                            <input type="hidden" name="id_ppic_master_so[${ck}]" value="${row.id_ppic_master_so}" ${dis}/>
                            <input type="hidden" name="barcode[${ck}]" value="${row.barcode}" ${dis}/>
                            <input type="hidden" name="no_carton[${ck}]" value="${row.no_carton}" ${dis}/>
                        </div>
                        `;
                    }
                },
            ]

        });

        function togglePreview(source) {
            $('#datatable_preview .row-check:not(:disabled)').each(function() {
                $(this).prop('checked', source.checked).trigger('change');
            });
        }

        $(document).on('change', '#datatable_preview .row-check', function() {
            let ck = $(this).data('ck');
            let disabled = !$(this).is(':checked');
            $(`[name="txtqty[${ck}]"]`).prop('disabled', disabled);
            $(`[name="id_so_det[${ck}]"]`).prop('disabled', disabled);
            $(`[name="price[${ck}]"]`).prop('disabled', disabled);
            $(`[name="curr[${ck}]"]`).prop('disabled', disabled);
            $(`[name="id_ppic_master_so[${ck}]"]`).prop('disabled', disabled);
            $(`[name="barcode[${ck}]"]`).prop('disabled', disabled);
            $(`[name="no_carton[${ck}]"]`).prop('disabled', disabled);
            findTotal();
        });

        function startCalc() {
            interval = setInterval('findTotal()', 1);
        }

        function findTotal() {
            var arr = document.getElementsByClassName('form-control form-control-sm input');
            var tot = 0;
            for (var i = 0; i < arr.length; i++) {
                if (!arr[i].disabled && parseInt(arr[i].value))
                    tot += parseInt(arr[i].value);
            }
            document.getElementById('total_qty_chk').value = tot;
            document.getElementById('total_qty_selected').value = tot;

            var cartons = new Set();
            $('#datatable_preview .row-check:checked:not(:disabled)').each(function() {
                var ck = $(this).data('ck');
                if (ck) cartons.add(ck.toString().split('__')[0]);
            });
            document.getElementById('total_carton_selected').value = cartons.size;
        }

        function stopCalc() {
            clearInterval(interval);
        }

        function confirmAndSave() {
            let cartonMap = {};
            let totalQty = 0;

            $('#datatable_preview .row-check:checked:not(:disabled)').each(function() {
                let ck = $(this).data('ck');
                let noCarton = ck.toString().split('__')[0];
                let qty = parseInt($(`[name="txtqty[${ck}]"]`).val()) || 0;

                if (cartonMap[noCarton] === undefined) {
                    cartonMap[noCarton] = 0;
                }
                cartonMap[noCarton] += qty;
                totalQty += qty;
            });

            let cartonList = Object.keys(cartonMap);

            if (cartonList.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak ada karton dipilih!',
                    text: 'Pilih minimal satu karton untuk disimpan.'
                });
                return;
            }

            let tableRows = cartonList.map((noCarton, idx) =>
                `<tr>
                    <td class="text-center">${idx + 1}</td>
                    <td class="fw-bold">${noCarton}</td>
                    <td class="text-center">${cartonMap[noCarton].toLocaleString()}</td>
                </tr>`
            ).join('');

            let html = `
                <div style="max-height:350px; overflow-y:auto;">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-primary">
                            <tr>
                                <th class="text-center" style="width:40px">No</th>
                                <th class="text-center">No. Carton</th>
                                <th class="text-center" style="width:80px">Qty</th>
                            </tr>
                        </thead>
                        <tbody>${tableRows}</tbody>
                        <tfoot>
                            <tr class="table-success fw-bold">
                                <td colspan="2" class="text-end">Total (${cartonList.length} Karton)</td>
                                <td class="text-center">${totalQty.toLocaleString()} PCS</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>`;

            Swal.fire({
                title: '<i class="fas fa-boxes"></i> Konfirmasi Simpan',
                html: html,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check"></i> Ya, Simpan',
                cancelButtonText: '<i class="fas fa-times"></i> Batal',
                width: '500px',
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#form').off('submit').submit();
                }
            });
        }

        function export_excel_list() {
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
                        link.download = "Laporan List FG IN " + from + " sampai " +
                            to + ".xlsx";
                        link.click();

                    }
                },
            });
        }

        function export_excel_summary() {
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
                url: '{{ route('export_excel_fg_in_summary') }}',
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
                        link.download = "Laporan Summary FG IN " + from + " sampai " +
                            to + ".xlsx";
                        link.click();

                    }
                },
            });
        }
    </script>
@endsection
