@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style type="text/css">
        .template-modal {
            border: none;
            border-radius: 14px;
        }

        .template-type-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 10px;
        }

        .template-type-btn {
            padding: 12px 10px;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            background: #fff;
            font-weight: 600;
            color: #212529;
            transition: all .15s ease-in-out;
        }

        .template-type-btn:hover {
            border-color: #86a8ff;
        }

        .template-type-btn.active {
            background: #eef3ff;
            border-color: #4a7dff;
            color: #2f5bea;
        }

        #list-table th,
        #list-table td {
            white-space: nowrap;
            vertical-align: middle;
        }

        th.search-cell {
            padding-left: .5rem !important;
            padding-right: .5rem !important;
        }

        .col-search {
            width: 100%;
            min-width: 100px;
        }

        .badge-soft {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-soft-success {
            background: #d1f2dc;
            color: #198754;
        }

        .badge-soft-danger {
            background: #f8d7da;
            color: #b02a37;
        }

        .import-type-desc {
            font-size: 12px;
            font-weight: 400;
            color: #6c757d;
        }

        .import-type-btn.active .import-type-desc {
            color: #2f5bea;
        }

        .import-step {
            padding: 5px 12px;
            border-radius: 20px;
            background: #f1f3f5;
            color: #6c757d;
            font-size: 12px;
        }

        .import-step.active {
            background: #e7eeff;
            color: #2f5bea;
            font-weight: 600;
        }

        .import-drop-zone {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            padding: 32px 20px;
            border: 1px dashed #c3c8d0;
            border-radius: 10px;
            background: #f8f9fb;
            text-align: center;
            cursor: pointer;
            transition: all .15s ease-in-out;
        }

        .import-drop-zone:hover,
        .import-drop-zone.dragover {
            background: #f5f8ff;
            border-color: #4a7dff;
        }

        .import-note {
            padding: 10px 14px;
            border-radius: 8px;
            background: #f8f9fb;
            color: #6c757d;
        }

        .template-column-box {
            padding: 14px 16px;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            background: #f8f9fb;
        }

        .template-column-chip {
            padding: 4px 8px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            background: #fff;
            font-size: 12px;
        }
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header bg-sb">
            <h5 class="card-title fw-bold mb-0">
                <i class="fas fa-list"></i> WIP Adjustment
            </h5>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-end gap-3 mb-3">
                <div>
                    <label class="form-label"><small>Tanggal Awal</small></label>
                    <input type="date"
                        class="form-control form-control-sm"
                        id="tgl-awal"
                        name="tgl_awal"
                        value="{{ date('Y-m-d') }}">
                </div>

                <div>
                    <label class="form-label"><small>Tanggal Akhir</small></label>
                    <input type="date"
                        class="form-control form-control-sm"
                        id="tgl-akhir"
                        name="tgl_akhir"
                        value="{{ date('Y-m-d') }}">
                </div>

                <div>
                    <label class="form-label"><small>Jenis Report</small></label>
                    <select name="type_report" id="type_report_filter" class="form-control form-control-sm select2bs4" style="width: 170px;">
                        <option value="CUTTING_FABRIC" selected>Cutting Fabric</option>
                        <option value="CUTTING_PCS">Cutting PCS</option>
                        <option value="DC">DC</option>
                        <option value="SEWING">Sewing</option>
                        <option value="PACKING">Packing</option>
                    </select>
                </div>

                <div>
                    <button class="btn btn-primary btn-sm" onclick="listTableReload()">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
                <div>
                    <a onclick="export_excel()" class="btn btn-outline-success position-relative btn-sm" id="but_export"
                        name="but_export">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>

                <!-- Button kanan -->
                <div class="ms-auto d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="OpenTemplateModal()">
                        <i class="fa fa-file-excel"></i> Template Report
                    </button>

                    <button type="button" class="btn btn-success btn-sm" onclick="OpenModal()">
                        <i class="fa fa-upload"></i> Import Adjustment
                    </button>
                </div>
            </div>
            <div>
                <table class="table table-bordered table-striped w-100 text-nowrap" id="list-table">
                    {{-- Header & body dibangun lewat JS sesuai jenis report (buildListTable) --}}
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="importExcel" tabindex="-1" aria-labelledby="importExcelLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <form class="w-100" method="post" action="{{ route('import-data-wip-adjustment') }}" enctype="multipart/form-data"
                onsubmit="submitUploadForm(this, event)">
                <div class="modal-content template-modal">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="importExcelLabel">Import Data Adjustment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        {{ csrf_field() }}
                        <input type="hidden" name="type_report" id="importTypeReport" value="CUTTING_FABRIC">

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="import-step active" data-step="1">1. Pilih Report</span>
                            <span class="import-step" data-step="2">2. Upload File</span>
                            <span class="import-step" data-step="3">3. Validasi</span>
                            <span class="import-step" data-step="4">4. Import</span>
                        </div>

                        <div class="fw-bold text-uppercase mb-2"><small>Jenis Report</small></div>
                        <div class="template-type-list mb-3">
                            <button type="button" class="template-type-btn import-type-btn active" data-type="CUTTING_FABRIC">
                                <i class="fa fa-scroll"></i> Cutting Fabric
                                <div class="import-type-desc">Mutasi Fabric</div>
                            </button>
                            <button type="button" class="template-type-btn import-type-btn" data-type="CUTTING_PCS">
                                <i class="fa fa-cut"></i> Cutting PCS
                                <div class="import-type-desc">Mutasi PCS</div>
                            </button>
                            <button type="button" class="template-type-btn import-type-btn" data-type="DC">
                                <i class="fa fa-truck"></i> DC
                                <div class="import-type-desc">Mutasi DC</div>
                            </button>
                            <button type="button" class="template-type-btn import-type-btn" data-type="SEWING">
                                <i class="fa fa-tshirt"></i> Sewing
                                <div class="import-type-desc">Mutasi Sewing</div>
                            </button>
                            <button type="button" class="template-type-btn import-type-btn" data-type="PACKING">
                                <i class="fa fa-box"></i> Packing
                                <div class="import-type-desc">Mutasi Packing</div>
                            </button>
                        </div>

                        <label class="import-drop-zone mb-3" id="importDropZone">
                            <i class="fa fa-upload fa-2x mb-3"></i>
                            <div class="fw-bold fs-5 mb-1">Upload File Adjustment</div>
                            <div class="text-muted mb-3">Gunakan template sesuai jenis report yang dipilih. Format .xlsx / .xls / .csv</div>
                            <span class="btn btn-primary px-4 rounded-3">Pilih File</span>
                            <div class="text-muted mt-3"><small id="importFileName">Belum ada file dipilih</small></div>
                            <input type="file" name="file" id="importFile" accept=".xlsx,.xls,.csv" hidden>
                        </label>

                        <div class="import-note">
                            <small>Tanggal saldo adjustment akan dibaca dari data file dan digunakan sebagai dasar filter pada halaman utama.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            Import &amp; Validasi
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="templateReportModal" tabindex="-1" aria-labelledby="templateReportLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content template-modal">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="templateReportLabel">Template Report Adjustment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-2"><small>Pilih jenis report untuk melihat kolom yang tersedia pada template.</small></p>

                    <div class="template-type-list mb-3">
                        <button type="button" class="template-type-btn active" data-type="CUTTING_FABRIC">
                            <i class="fa fa-scroll"></i> Cutting Fabric
                        </button>
                        <button type="button" class="template-type-btn" data-type="CUTTING_PCS">
                            <i class="fa fa-cut"></i> Cutting PCS
                        </button>
                        <button type="button" class="template-type-btn" data-type="DC">
                            <i class="fa fa-truck"></i> DC
                        </button>
                        <button type="button" class="template-type-btn" data-type="SEWING">
                            <i class="fa fa-tshirt"></i> Sewing
                        </button>
                        <button type="button" class="template-type-btn" data-type="PACKING">
                            <i class="fa fa-box"></i> Packing
                        </button>
                    </div>

                    <div class="template-column-box">
                        <div class="fw-bold mb-2" id="templateColumnTitle"></div>
                        <div class="d-flex flex-wrap gap-2" id="templateColumnList"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="btnDownloadTemplate">
                        <i class="fa fa-download"></i> Download Template
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: 'resolve'
        })

        $('.select2-container--bootstrap4 .select2-selection--single').css({
            'height': '30px',
            'font-size': '12px',
            'line-height': '30px'
        });

        // Kolom datatable per jenis report: [judul, field, angka?]
        const listColumns = {
            PACKING: [
                ['WS', 'no_ws'], ['Buyer', 'buyer'], ['Style', 'style'], ['Color', 'color'], ['Size', 'size'],
                ['Transit Terima Packing Line', 'transit_terima_packing_line', true],
                ['Packing Line', 'packing_line', true],
                ['Packing Temporary', 'packing_temporary', true],
                ['Packing Central', 'packing_central', true],
            ],
            SEWING: [
                ['WS', 'no_ws'], ['Buyer', 'buyer'], ['Style', 'style'], ['Color', 'color'], ['Size', 'size'],
                ['Sewing', 'sewing', true],
                ['QC Finishing', 'qc_finishing', true],
                ['Finishing Pasang Kancing', 'finishing_pasang_kancing', true],
                ['Finishing Bartack', 'finishing_bartack', true],
                ['Finishing Heatseal', 'finishing_heatseal', true],
                ['Finishing Snap', 'finishing_snap', true],
                ['Finishing Embro', 'finishing_embro', true],
                ['Defect Sewing', 'defect_sewing', true],
                ['Defect Spotcleaning', 'defect_spotcleaning', true],
                ['Defect Mending', 'defect_mending', true],
                ['Transit Terima QC Reject', 'transit_terima_qc_reject', true],
                ['QC Reject', 'qc_reject', true],
            ],
            DC: [
                ['WS', 'no_ws'], ['Buyer', 'buyer'], ['Style', 'style'], ['Color', 'color'], ['Size', 'size'],
                ['Panel', 'panel'], ['Part', 'part'],
                ['Mutasi DC', 'mutasi_dc', true],
                ['Mutasi Secondary Dalam', 'mutasi_secondary_dalam', true],
                ['Mutasi Secondary Luar', 'mutasi_secondary_luar', true],
                ['Terima Transit Secondary Luar', 'terima_transit_secondary_luar', true],
            ],
            CUTTING_PCS: [
                ['WS', 'no_ws'], ['Buyer', 'buyer'], ['Style', 'style'], ['Color', 'color'], ['Size', 'size'],
                ['Panel', 'panel'], ['Part', 'part'],
                ['Cutting', 'cutting', true],
            ],
            CUTTING_FABRIC: [
                ['WS', 'ws'], ['ID Roll', 'id_roll'], ['ID Item', 'id_item'], ['Satuan', 'satuan'],
                ['Fabric', 'fabric', true],
            ],
        };

        let listTable;
        // Tanggal closing terakhir, tgl saldo <= tanggal ini tidak bisa di-cancel
        const lastClosing = @json($lastClosing ? date('Y-m-d', strtotime($lastClosing)) : null);
        // Jenis report yang sedang tampil di tabel (bukan yang sedang dipilih di dropdown)
        let currentListType;

        function buildListTable(type) {
            currentListType = type;

            const columns = [
                { title: 'Tanggal Saldo', data: 'tgl_saldo' },
                { title: 'Jenis Report', data: 'type_report' },
                ...listColumns[type].map(([title, data, isNumber]) => ({
                    title, data, className: isNumber ? 'text-end' : ''
                })),
                { title: 'Waktu Import', data: 'created_at' },
                { title: 'User Import', data: 'created_by_username' },
                {
                    title: 'Status',
                    data: 'status',
                    className: 'text-center',
                    render: function (data) {
                        return data === 'N'
                            ? '<span class="badge-soft badge-soft-danger">Tidak Aktif</span>'
                            : '<span class="badge-soft badge-soft-success">Aktif</span>';
                    }
                },
                {
                    title: 'Aksi',
                    data: null,
                    className: 'text-center',
                    searchable: false,
                    render: function (data, type, row) {
                        const isClosing = lastClosing && row.tgl_saldo_raw <= lastClosing;
                        const disabled = row.status === 'N' || isClosing;

                        return `
                            <button type="button" class="btn btn-danger btn-sm btn-cancel-adjustment" ${disabled ? 'disabled' : ''}
                                ${isClosing ? 'title="Periode sudah closing"' : ''}>
                                <i class="fa fa-times"></i> Cancel
                            </button>
                        `;
                    }
                },
            ];

            if (listTable) {
                listTable.destroy();
            }

            // Header 2 baris: judul + search per kolom
            const titleRow = columns.map(col => `<th class="text-center align-middle">${col.title}</th>`).join('');
            const searchRow = columns.map(col => col.searchable === false
                ? '<th class="search-cell"></th>'
                : '<th class="search-cell"><input type="text" class="form-control form-control-sm col-search"/></th>'
            ).join('');

            $('#list-table').html(`
                <thead class="table-primary">
                    <tr>${titleRow}</tr>
                    <tr>${searchRow}</tr>
                </thead>
                <tbody></tbody>
            `);

            $('#list-table thead tr:eq(1) th').each(function (i) {
                $('input', this).on('keyup change', function () {
                    if (listTable.column(i).search() !== this.value) {
                        listTable.column(i).search(this.value).draw();
                    }
                });
            });

            listTable = $('#list-table').DataTable({
                orderCellsTop: true,
                ordering: false,
                processing: true,
                serverSide: true,
                paging: false,
                searching: true,
                scrollY: '300px',
                scrollX: true,
                scrollCollapse: true,
                drawCallback: function () {
                    this.api().columns.adjust();
                },
                ajax: {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('get-data-wip-adjustment') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: function(d) {
                        d.dateFrom = $('#tgl-awal').val();
                        d.dateTo = $('#tgl-akhir').val();
                        d.type_report = currentListType;
                    },
                },
                columns: columns,
                columnDefs: [
                    {
                        targets: "_all",
                        defaultContent: "-"
                    },
                ]
            });
        }

        buildListTable($('#type_report_filter').val());

        // Cancel 1 baris gabungan (semua type_report di baris itu jadi status N)
        $('#list-table').on('click', '.btn-cancel-adjustment', function () {
            const row = listTable.row($(this).closest('tr')).data();

            Swal.fire({
                title: 'Cancel Adjustment?',
                html: currentListType === 'CUTTING_FABRIC'
                    ? `WS <b>${row.ws}</b> — Roll ${row.id_roll}<br>Tanggal saldo ${row.tgl_saldo}`
                    : `WS <b>${row.no_ws}</b> — ${row.color} / ${row.size}<br>Tanggal saldo ${row.tgl_saldo}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, cancel',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
            }).then(result => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route('cancel-wip-adjustment') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        type_report: currentListType,
                        id: row.id,
                        tgl_saldo: row.tgl_saldo_raw,
                        no_ws: row.no_ws,
                        buyer: row.buyer,
                        style: row.style,
                        color: row.color,
                        size: row.size,
                        panel: row.panel,
                        part: row.part,
                    },
                    beforeSend: function () {
                        showLoading();
                    },
                    success: function (res) {
                        iziToast.success({ title: 'Berhasil', message: res.message, position: 'topCenter' });
                        listTableReload();
                    },
                    error: function (xhr) {
                        iziToast.error({
                            title: 'Gagal',
                            message: xhr.responseJSON?.message ?? 'Cancel gagal',
                            position: 'topCenter'
                        });
                    },
                    complete: function () {
                        hideLoading();
                    }
                });
            });
        });

        function listTableReload() {
            const type = $('#type_report_filter').val();

            // Jenis report berubah: bangun ulang kolom tabel
            if (type !== currentListType) {
                buildListTable(type);
                return;
            }

            showLoading();

            listTable.ajax.reload(function () {
                hideLoading();
            });
        }

        async function export_excel() {
            const type = $('#type_report_filter').val();

            Swal.fire({
                title: "Exporting",
                html: "Please Wait...",
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            try {
                const res = await $.ajax({
                    url: '{{ route('export-excel-wip-adjustment') }}',
                    type: "GET",
                    data: {
                        dateFrom: $("#tgl-awal").val(),
                        dateTo: $("#tgl-akhir").val(),
                        type_report: type,
                    },
                    xhrFields: {
                        responseType: 'blob'
                    }
                });

                Swal.close();

                iziToast.success({
                    title: 'Success',
                    message: 'Success',
                    position: 'topCenter'
                });

                const blob = new Blob([res]);
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download =
                    "WIP Adjustment " +
                    $('#type_report_filter option:selected').text() + " " +
                    $("#tgl-awal").val() +
                    " - " +
                    $("#tgl-akhir").val() +
                    ".xlsx";
                link.click();

            } catch (err) {
                Swal.close();
                console.error(err);

                iziToast.error({
                    title: 'Error',
                    message: 'Export gagal',
                    position: 'topCenter'
                });
            }
        }

        function setImportStep(step) {
            $('.import-step').each(function () {
                $(this).toggleClass('active', $(this).data('step') <= step);
            });
        }

        function OpenModal() {
            const form = $('#importExcel form')[0];

            form.reset();
            $('.import-type-btn').removeClass('active').first().addClass('active');
            $('#importTypeReport').val('CUTTING_FABRIC');
            $('#importFileName').text('Belum ada file dipilih');
            setImportStep(1);
            $('#importExcel').modal('show');
        }

        $('.import-type-btn').on('click', function () {
            $('.import-type-btn').removeClass('active');
            $(this).addClass('active');
            $('#importTypeReport').val($(this).data('type'));
            setImportStep($('#importFile')[0].files.length ? 2 : 1);
        });

        $('#importFile').on('change', function () {
            const file = this.files[0];

            $('#importFileName').text(file ? file.name : 'Belum ada file dipilih');
            setImportStep(file ? 2 : 1);
        });

        $('#importDropZone')
            .on('dragover', function (e) {
                e.preventDefault();
                $(this).addClass('dragover');
            })
            .on('dragleave drop', function () {
                $(this).removeClass('dragover');
            })
            .on('drop', function (e) {
                e.preventDefault();
                const input = document.getElementById('importFile');

                input.files = e.originalEvent.dataTransfer.files;
                $(input).trigger('change');
            });

        // Struktur kolom template per jenis report
        const templateColumns = {
            PACKING: {
                label: 'Packing',
                file: "{{ asset('example/template_wip_adjustment_packing.xlsx') }}",
                columns: ['tgl_saldo', 'ws', 'buyer', 'style', 'color', 'size', 'qty transit terima packing line', 'qty packing line', 'qty packing temporary', 'qty packing central']
            },
            SEWING: {
                label: 'Sewing',
                file: "{{ asset('example/template_wip_adjustment_sewing.xlsx') }}",
                columns: ['tgl_saldo', 'ws', 'buyer', 'style', 'color', 'size', 'qty sewing', 'qty qc finishing', 'qty finishing pasang kancing', 'qty finishing bartack', 'qty finishing heatseal', 'qty finishing snap', 'qty finishing embro', 'qty defect sewing', 'qty defect spotcleaning', 'qty defect mending', 'qty transit terima qc reject', 'qty qc reject']
            },
            DC: {
                label: 'DC',
                file: "{{ asset('example/template_wip_adjustment_dc.xlsx') }}",
                columns: ['tgl_saldo', 'ws', 'buyer', 'style', 'color', 'size', 'panel', 'part', 'qty mutasi dc', 'qty mutasi secondary dalam', 'qty mutasi secondary luar', 'qty terima transit secondary luar']
            },
            CUTTING_PCS: {
                label: 'Cutting PCS',
                file: "{{ asset('example/template_wip_adjustment_cutting_pcs.xlsx') }}",
                columns: ['tgl_saldo', 'ws', 'buyer', 'style', 'color', 'size', 'panel', 'part', 'qty cutting']
            },
            CUTTING_FABRIC: {
                label: 'Cutting Fabric',
                file: "{{ asset('example/template_wip_adjustment_cutting_fabric.xlsx') }}",
                columns: ['tgl_saldo', 'ws', 'id_roll', 'id_item', 'satuan', 'qty fabric']
            }
        };

        function renderTemplateColumns(type) {
            const template = templateColumns[type];

            $('#templateColumnTitle').text(template.label + ' — Struktur Kolom');
            $('#templateColumnList').html(
                template.columns.map(col => `<span class="template-column-chip">${col}</span>`).join('')
            );
        }

        function OpenTemplateModal() {
            $('#templateReportModal .template-type-btn').removeClass('active').first().addClass('active');
            renderTemplateColumns('CUTTING_FABRIC');
            $('#templateReportModal').modal('show');
        }

        $('#templateReportModal .template-type-btn').on('click', function () {
            $('#templateReportModal .template-type-btn').removeClass('active');
            $(this).addClass('active');
            renderTemplateColumns($(this).data('type'));
        });

        $('#btnDownloadTemplate').on('click', function () {
            const type = $('#templateReportModal .template-type-btn.active').data('type');

            window.location.href = templateColumns[type].file;
        });

        function submitUploadForm(form, event) {
            event.preventDefault();

            if (!$('#importFile')[0].files.length) {
                iziToast.warning({
                    title: 'Perhatian',
                    message: 'Silahkan masukan file terlebih dahulu.',
                    position: 'topCenter'
                });
                return;
            }

            let formData = new FormData(form);

            $.ajax({
                url: form.action,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    showLoading();
                    setImportStep(3);
                },
                success: function(res) {
                    if (res.status === 200) {
                        setImportStep(4);
                        $('#importExcel').modal('hide');
                        listTableReload();

                        iziToast.success({
                            title: 'Berhasil',
                            message: res.message,
                            position: 'topCenter'
                        });
                    }
                },
                error: function(xhr) {
                    const res = xhr.responseJSON ?? {};
                    let message = res.message ?? 'Import gagal';

                    // Error per baris (maks 5 ditampilkan)
                    if (Array.isArray(res.errors) && res.errors.length) {
                        message = res.errors.slice(0, 5).join('<br>');

                        if (res.errors.length > 5) {
                            message += `<br>dan ${res.errors.length - 5} error lainnya`;
                        }
                    }

                    setImportStep(2);

                    iziToast.error({
                        title: 'Gagal',
                        message: message,
                        position: 'topCenter',
                        timeout: 8000
                    });
                },
                complete: function () {
                    hideLoading();
                }
            });
        }
    </script>
@endsection
