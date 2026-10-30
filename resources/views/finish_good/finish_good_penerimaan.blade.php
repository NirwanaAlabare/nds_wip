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
    {{-- Simpan lewat confirmAndSave() (cek FG IN dulu), jadi form tidak boleh ter-submit langsung, mis. Enter di kotak search --}}
    <form id="form" name='form' method='post' action="{{ route('store-fg-in') }}"
        onsubmit="event.preventDefault()">
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
                                    <th> <input type = 'text' class="form-control form-control-sm total-qty-preview" style="width:75px"
                                            readonly> </th>
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

        /* Swal konfirmasi & hasil simpan FG IN */
        .fgin-swal {
            width: 780px !important;
            max-width: calc(100vw - 32px);
        }

        .fgin-swal .swal2-title {
            font-size: 1.35rem;
        }

        .fgin-swal .swal2-html-container {
            margin: .75rem 1.25rem 0;
            text-align: left;
            font-size: .875rem;
            color: #343a40;
        }

        .fgin-info {
            display: grid;
            grid-template-columns: minmax(0, .9fr) minmax(0, 1fr) minmax(0, 1.7fr) minmax(0, 1fr);
            gap: .5rem 1rem;
            padding: .65rem .85rem;
            margin-bottom: .75rem;
            border-radius: .5rem;
            background: #f4f8ff;
            border: 1px solid #dbe7ff;
        }

        .fgin-info b {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .fgin-label {
            display: block;
            font-size: .7rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .fgin-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .5rem;
            margin-bottom: .75rem;
        }

        .fgin-stat {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .6rem .8rem;
            border-radius: .5rem;
            border: 1px solid #e3e8ef;
            border-left-width: 4px;
            background: #fff;
        }

        .fgin-stat i {
            font-size: 1.25rem;
        }

        .fgin-stat-val {
            font-size: 1.35rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .fgin-stat-lbl {
            font-size: .72rem;
            color: #6c757d;
        }

        .fgin-stat-primary {
            border-left-color: #0d6efd;
        }

        .fgin-stat-primary i {
            color: #0d6efd;
        }

        .fgin-stat-success {
            border-left-color: #198754;
        }

        .fgin-stat-success i {
            color: #198754;
        }

        .fgin-stat-warning {
            border-left-color: #ffc107;
        }

        .fgin-stat-warning i {
            color: #d39e00;
        }

        .fgin-stat-muted {
            border-left-color: #ced4da;
        }

        .fgin-stat-muted i {
            color: #adb5bd;
        }

        .fgin-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            margin: .25rem 0 .4rem;
        }

        .fgin-section-title {
            font-weight: 600;
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #495057;
        }

        .fgin-size-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(66px, 1fr));
            gap: .35rem;
            margin-bottom: .75rem;
        }

        .fgin-size-cell {
            padding: .25rem .35rem;
            text-align: center;
            border-radius: .4rem;
            background: #f4f8ff;
            border: 1px solid #dbe7ff;
        }

        .fgin-size-cell span {
            display: block;
            font-size: .68rem;
            color: #6c757d;
            white-space: nowrap;
        }

        .fgin-size-cell b {
            display: block;
            font-size: .9rem;
            line-height: 1.2;
            font-variant-numeric: tabular-nums;
        }

        .fgin-carton-sizes {
            display: flex;
            flex-wrap: wrap;
            gap: .25rem;
        }

        .fgin-chip {
            display: inline-block;
            padding: .05rem .45rem;
            border-radius: 1rem;
            background: #eef2f7;
            border: 1px solid #e1e6ee;
            font-size: .72rem;
            white-space: nowrap;
        }

        .fgin-search {
            width: 230px;
            max-width: 55%;
        }

        .fgin-table-wrap {
            max-height: 240px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            background: #fff;
        }

        .fgin-table {
            width: 100%;
            margin: 0;
            font-size: .8rem;
            border-collapse: separate;
            border-spacing: 0;
        }

        .fgin-table th {
            position: sticky;
            top: 0;
            z-index: 1;
            padding: .4rem .6rem;
            background: #e9f1ff;
            border-bottom: 1px solid #cfe2ff;
            font-weight: 600;
            white-space: nowrap;
        }

        .fgin-table td {
            padding: .4rem .6rem;
            border-bottom: 1px solid #f1f3f5;
            vertical-align: middle;
        }

        .fgin-table tbody tr:hover td {
            background: #f8fbff;
        }

        .fgin-table .col-no {
            width: 44px;
        }

        .fgin-table td.col-no {
            color: #adb5bd;
        }

        .fgin-table .col-carton {
            width: 100px;
            font-weight: 700;
            white-space: nowrap;
        }

        .fgin-table .col-qty {
            width: 84px;
            text-align: right;
            font-weight: 700;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .fgin-table tfoot td {
            position: sticky;
            bottom: 0;
            background: #f1f5fb;
            border-top: 1px solid #cfe2ff;
            border-bottom: 0;
            font-weight: 700;
        }

        .fgin-alert {
            padding: .65rem .85rem;
            margin-bottom: .75rem;
            border-radius: .5rem;
            background: #fff8e1;
            border: 1px solid #ffe08a;
        }

        .fgin-alert-title {
            font-weight: 600;
            color: #8a6100;
        }

        .fgin-alert-sub {
            margin-bottom: .5rem;
            font-size: .78rem;
            color: #6c5a1f;
        }

        .fgin-alert .fgin-table-wrap {
            max-height: 150px;
        }

        .fgin-alert .fgin-table th {
            background: #fff3cd;
            border-bottom-color: #ffe08a;
        }

        .fgin-safe {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .5rem .85rem;
            margin-bottom: .75rem;
            border-radius: .5rem;
            background: #e9f7ef;
            border: 1px solid #b7e4c7;
            color: #146c43;
            font-size: .8rem;
        }

        .fgin-trans {
            padding: .75rem;
            margin-bottom: .75rem;
            text-align: center;
            border-radius: .5rem;
            background: #e9f7ef;
            border: 1px dashed #75b798;
        }

        .fgin-trans-no {
            font-size: 1.5rem;
            font-weight: 700;
            color: #146c43;
            letter-spacing: .03em;
        }

        @media (max-width: 576px) {
            .fgin-info {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .fgin-stats {
                grid-template-columns: 1fr;
            }

            .fgin-section {
                flex-wrap: wrap;
            }

            .fgin-search {
                width: 100%;
                max-width: 100%;
            }
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
            templateResult: function(data) {
                if (!data.id) {
                    return data.text;
                }

                var result_value = $('<span></span>').text(data.text);

                if (data.close_order === 'Y') {
                    result_value.append(' <span style="font-weight:bold;">(Close Order)</span>');
                    result_value.css({
                        'color': '#dc3545',
                        'cursor': 'not-allowed'
                    });
                }

                return result_value;
            }
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

        // Hanya karton yang sudah full (patokan Packing List: semua barcode qty = qty scan) yang boleh dicentang
        function isSelectable(row) {
            return row.qty != 0 && row.carton_full == 1;
        }

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
                findTotal(this.api());
            },
            // Data diambil sekali per PO, paging & filter dikerjakan di browser.
            // deferRender: baris baru dibuat saat halamannya ditampilkan, jadi ribuan baris tetap ringan.
            ordering: false,
            processing: true,
            serverSide: false,
            paging: true,
            pageLength: 50,
            lengthMenu: [
                [10, 50, 100, 500],
                [10, 50, 100, 500]
            ],
            deferRender: true,
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
                dataSrc: function(json) {
                    // Status centang disimpan di data (bukan di DOM) supaya tetap ada saat pindah halaman / filter
                    json.data.forEach(function(row) {
                        row.checked = isSelectable(row);
                    });
                    return json.data;
                },
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
                if (isSelectable(data)) {
                    $(row).css('background-color', '#cfe2ff');
                } else if (data.carton_full != 1) {
                    $(row).css('background-color', '#fff3cd');
                }
            },
            columnDefs: [{
                    "className": "align-middle",
                    "targets": "_all"
                },
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        return `
                        <div class="form-check checkbox-xl" style="text-align:center">
                            <input class="form-check-input row-check" type="checkbox"
                                ${isSelectable(row) ? '' : 'disabled'} ${row.checked ? 'checked' : ''}>
                        </div>`;
                    }
                },
                {
                    targets: [1],
                    render: (data, type, row, meta) => {
                        if (row.carton_full == 1) {
                            return data;
                        }

                        return `${data} <span class="badge bg-warning text-dark">Belum Full ${row.carton_scan}/${row.carton_qty}</span>`;
                    }
                },
                {
                    targets: [9],
                    render: (data, type, row, meta) => {
                        // Tanpa name: yang dikirim saat simpan cuma selected_keys, detailnya diambil ulang di server
                        return `
                        <div>
                            <input type="number" class="form-control form-control-sm input" style="width:75px"
                            value="${row.qty}" autocomplete="off" readonly ${row.checked ? '' : 'disabled'}/>
                        </div>
                        `;
                    }
                },
            ]

        });

        // Berlaku untuk semua baris hasil filter, termasuk yang ada di halaman lain
        function togglePreview(source) {
            datatable_preview.rows({
                search: 'applied'
            }).every(function() {
                let row = this.data();
                if (isSelectable(row)) {
                    row.checked = source.checked;
                    this.invalidate('data');
                }
            });
            datatable_preview.draw(false);
        }

        $(document).on('change', '#datatable_preview .row-check', function() {
            let tr = $(this).closest('tr');
            datatable_preview.row(tr).data().checked = this.checked;
            tr.find('input.input').prop('disabled', !this.checked);
            findTotal();
        });

        // Hitung dari data DataTables (semua halaman), bukan dari input yang sedang tampil
        function findTotal(api = datatable_preview) {
            let tot = 0;
            let cartons = new Set();

            api.rows().data().each(function(row) {
                if (row.checked) {
                    tot += parseInt(row.qty) || 0;
                    cartons.add(String(row.no_carton));
                }
            });

            $('.total-qty-preview').val(tot);
            $('#total_qty_selected').val(tot);
            $('#total_carton_selected').val(cartons.size);
        }

        // Alur simpan: cek dulu ke server (bandingkan dengan data FG IN) -> swal konfirmasi -> simpan -> swal hasil.
        // Key dikirim sebagai 1 field JSON supaya tidak kena batas max_input_vars PHP walau ribuan baris.
        function confirmAndSave() {
            let selectedKeys = [];

            datatable_preview.rows().data().each(function(row) {
                if (row.checked) {
                    selectedKeys.push(row.no_carton + '__' + row.id_so_det);
                }
            });

            if (selectedKeys.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak ada karton dipilih!',
                    text: 'Pilih minimal satu karton untuk disimpan.'
                });
                return;
            }

            Swal.fire({
                title: 'Memeriksa data...',
                html: 'Membandingkan karton terpilih dengan data FG IN',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            $.ajax({
                type: 'POST',
                url: '{{ route('check-fg-in') }}',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    cbopo: $('#cbopo').val(),
                    selected_keys: JSON.stringify(selectedKeys),
                },
                success: showConfirmSave,
                error: (xhr) => Swal.fire({
                    icon: 'error',
                    title: 'Gagal memeriksa data FG IN',
                    text: ajaxErrorMessage(xhr),
                }),
            });
        }

        function showConfirmSave(check) {
            let skipped = check.skipped || [];

            if (check.total_carton === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak ada karton yang bisa disimpan',
                    html: skippedHtml(skipped),
                    customClass: {
                        popup: 'fgin-swal'
                    },
                    confirmButtonText: '<i class="fas fa-sync-alt"></i> Muat Ulang Preview',
                }).then(() => dataTablePreviewReload());
                return;
            }

            let po = getPoInfo();
            let today = new Date().toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });

            let cartonRows = check.cartons.map((carton, idx) => {
                let search = (carton.no_carton + ' ' + carton.sizes.map(s => s.size).join(' ')).toLowerCase();

                return `
                    <tr data-search="${escapeHtml(search)}">
                        <td class="col-no">${idx + 1}</td>
                        <td class="col-carton">${escapeHtml(carton.no_carton)}</td>
                        <td><div class="fgin-carton-sizes">${carton.sizes.map(s => sizeChip(s)).join('')}</div></td>
                        <td class="col-qty">${formatNumber(carton.qty)}</td>
                    </tr>`;
            }).join('');

            let safeOrSkipped = skipped.length > 0 ? skippedHtml(skipped) : `
                <div class="fgin-safe">
                    <i class="fas fa-shield-alt"></i>
                    Sudah dicek dengan data FG IN, tidak ada karton yang dobel.
                </div>`;

            let html = `
                <div class="fgin-info">
                    <div><span class="fgin-label">PO</span><b>${escapeHtml(po.po)}</b></div>
                    <div><span class="fgin-label">Dest</span><b>${escapeHtml(po.dest)}</b></div>
                    <div><span class="fgin-label">Buyer</span><b title="${escapeHtml(po.buyer)}">${escapeHtml(po.buyer)}</b></div>
                    <div><span class="fgin-label">Tgl Penerimaan</span><b>${today}</b></div>
                </div>
                <div class="fgin-stats">
                    ${statCard('primary', 'fa-box', check.total_carton, 'Karton akan disimpan')}
                    ${statCard('success', 'fa-tshirt', check.total_qty, 'Total PCS')}
                    ${statCard(skipped.length > 0 ? 'warning' : 'muted', 'fa-ban', skipped.length, 'Karton dilewati')}
                </div>
                ${safeOrSkipped}
                <div class="fgin-section">
                    <span class="fgin-section-title">Total per Size (${check.sizes.length} size)</span>
                </div>
                ${sizeGrid(check.sizes)}
                <div class="fgin-section">
                    <span class="fgin-section-title">Daftar Karton</span>
                    <input type="search" id="fgin-search" class="form-control form-control-sm fgin-search"
                        placeholder="Cari no. karton / size..." autocomplete="off">
                </div>
                <div class="fgin-table-wrap">
                    <table class="fgin-table">
                        <thead>
                            <tr>
                                <th class="col-no">No</th>
                                <th class="col-carton">No. Carton</th>
                                <th>Rincian Size</th>
                                <th class="col-qty">Qty (pcs)</th>
                            </tr>
                        </thead>
                        <tbody id="fgin-carton-body">${cartonRows}</tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3">Total ${formatNumber(check.total_carton)} karton</td>
                                <td class="col-qty">${formatNumber(check.total_qty)}</td>
                            </tr>
                        </tfoot>
                    </table>
                    <div id="fgin-search-empty" class="text-center text-muted py-3 d-none">Karton tidak ditemukan</div>
                </div>`;

            Swal.fire({
                title: '<i class="fas fa-boxes text-primary"></i> Konfirmasi Penerimaan FG',
                html: html,
                customClass: {
                    popup: 'fgin-swal'
                },
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `<i class="fas fa-check"></i> Ya, Simpan ${formatNumber(check.total_carton)} Karton`,
                cancelButtonText: '<i class="fas fa-times"></i> Batal',
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                didOpen: () => {
                    let container = Swal.getHtmlContainer();

                    container.querySelector('#fgin-search').addEventListener('input', function() {
                        let term = this.value.trim().toLowerCase();
                        let visible = 0;

                        container.querySelectorAll('#fgin-carton-body tr').forEach((tr) => {
                            let show = tr.dataset.search.includes(term);
                            tr.style.display = show ? '' : 'none';
                            visible += show ? 1 : 0;
                        });

                        container.querySelector('#fgin-search-empty').classList.toggle('d-none', visible > 0);
                    });
                },
                // Simpan hanya key yang lolos pengecekan. Server mengecek ulang (dengan lock per PO),
                // jadi kalau ada yang keburu disimpan user lain, karton itu tetap dilewati.
                preConfirm: () => {
                    return $.ajax({
                        type: 'POST',
                        url: '{{ route('store-fg-in') }}',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            cbopo: $('#cbopo').val(),
                            selected_keys: JSON.stringify(check.keys),
                        },
                    }).then((res) => res, (xhr) => {
                        Swal.showValidationMessage('Gagal menyimpan: ' + ajaxErrorMessage(xhr));
                        return false;
                    });
                },
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    showSaveResult(result.value);
                } else if (skipped.length > 0) {
                    // Preview sudah tidak sesuai data terbaru
                    dataTablePreviewReload();
                }
            });
        }

        function showSaveResult(res) {
            let skipped = res.skipped || [];

            if (res.status == 201) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Disimpan',
                    html: `
                        <div class="fgin-trans">
                            <span class="fgin-label">No. Transaksi</span>
                            <div class="fgin-trans-no">${escapeHtml(res.no_transaksi)}</div>
                        </div>
                        <div class="fgin-stats">
                            ${statCard('primary', 'fa-box', res.total_carton, 'Karton tersimpan')}
                            ${statCard('success', 'fa-tshirt', res.total_qty, 'PCS tersimpan')}
                            ${statCard(skipped.length > 0 ? 'warning' : 'muted', 'fa-ban', skipped.length, 'Karton dilewati')}
                        </div>
                        ${sizeGrid(res.sizes || [])}
                        ${skippedHtml(skipped)}`,
                    customClass: {
                        popup: 'fgin-swal'
                    },
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Oke',
                });
            } else if (res.status == 200) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak ada data yang disimpan',
                    html: skippedHtml(skipped),
                    customClass: {
                        popup: 'fgin-swal'
                    },
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: res.message,
                });
            }

            dataTablePreviewReload();
            dataTableReload();
        }

        function skippedHtml(skipped) {
            if (skipped.length === 0) {
                return '';
            }

            let rows = skipped.map((item) => `
                <tr>
                    <td class="fw-bold">${escapeHtml(item.no_carton)}</td>
                    <td>
                        <span class="badge ${item.reason.includes('FG IN') ? 'bg-danger' : 'bg-warning text-dark'}">
                            ${escapeHtml(item.reason)}
                        </span>
                    </td>
                    <td>${escapeHtml(item.no_sb || '-')}</td>
                    <td>${escapeHtml(item.tgl || '-')}</td>
                    <td>${escapeHtml(item.user || '-')}</td>
                </tr>`).join('');

            return `
                <div class="fgin-alert">
                    <div class="fgin-alert-title">
                        <i class="fas fa-exclamation-triangle"></i> ${formatNumber(skipped.length)} karton tidak ikut disimpan
                    </div>
                    <div class="fgin-alert-sub">
                        Karton berikut sudah pernah di-input FG IN atau tidak memenuhi syarat, jadi otomatis dilewati supaya tidak dobel.
                    </div>
                    <div class="fgin-table-wrap">
                        <table class="fgin-table">
                            <thead>
                                <tr>
                                    <th>No. Carton</th>
                                    <th>Keterangan</th>
                                    <th>No. SB</th>
                                    <th>Tgl FG IN</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>
                </div>`;
        }

        function statCard(variant, icon, value, label) {
            return `
                <div class="fgin-stat fgin-stat-${variant}">
                    <i class="fas ${icon}"></i>
                    <div>
                        <div class="fgin-stat-val">${formatNumber(value)}</div>
                        <div class="fgin-stat-lbl">${label}</div>
                    </div>
                </div>`;
        }

        function sizeChip(size) {
            return `<span class="fgin-chip">${escapeHtml(size.size)} <b>${formatNumber(size.qty)}</b></span>`;
        }

        function sizeGrid(sizes) {
            let cells = sizes.map((size) => `
                <div class="fgin-size-cell">
                    <span>${escapeHtml(size.size)}</span>
                    <b>${formatNumber(size.qty)}</b>
                </div>`).join('');

            return `<div class="fgin-size-grid">${cells}</div>`;
        }

        // Teks pilihan PO: "PO - DEST - BUYER"
        function getPoInfo() {
            let selected = $('#cbopo').select2('data')[0];
            let parts = (selected ? selected.text : '').split(' - ');

            return {
                po: parts[0] || '-',
                dest: parts[1] || '-',
                buyer: parts.slice(2).join(' - ') || '-',
            };
        }

        function formatNumber(value) {
            return Number(value || 0).toLocaleString('id-ID');
        }

        function escapeHtml(value) {
            return String(value === null || value === undefined ? '' : value).replace(/[&<>"']/g, (c) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));
        }

        function ajaxErrorMessage(xhr) {
            return (xhr.responseJSON && xhr.responseJSON.message) || 'Terjadi kesalahan pada server.';
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
