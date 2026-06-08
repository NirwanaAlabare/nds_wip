@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        input[type=number] { -moz-appearance: textfield; }
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

        /* ── FROM → TO Banner ── */
        .switch-banner {
            display: flex;
            align-items: stretch;
            gap: 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,.10);
            margin-bottom: 20px;
        }
        .switch-side {
            flex: 1;
            padding: 16px 20px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .switch-side.from { background: linear-gradient(135deg, #1a73e8, #0d47a1); }
        .switch-side.to   { background: linear-gradient(135deg, #2e7d32, #1b5e20); }
        .switch-side .side-label {
            font-size: 10px; font-weight: 800; letter-spacing: 1.5px;
            text-transform: uppercase; color: rgba(255,255,255,.65); margin-bottom: 2px;
        }
        .switch-side .side-value {
            font-size: 15px; font-weight: 700; color: #fff;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            min-height: 22px;
        }
        .switch-side .side-sub {
            font-size: 11px; color: rgba(255,255,255,.55);
        }
        .switch-arrow {
            display: flex; align-items: center; justify-content: center;
            background: #fff; padding: 0 14px;
            font-size: 22px; color: #555;
        }

        /* ── Qty badge di footer ── */
        .qty-badge {
            display: inline-block;
            background: #1a73e8; color: #fff;
            border-radius: 20px; padding: 3px 14px;
            font-size: 13px; font-weight: 700;
            min-width: 60px; text-align: center;
        }

        /* ── Row highlight on hover ── */
        #tbl-preview tbody tr:hover { background: #eef4ff !important; cursor: default; }
    </style>
@endsection

@section('content')

    {{-- ═══════════════ FORM SWITCHING ═══════════════ --}}
    <form id="form-switching" method="post" action="{{ route('store_packing_central_switching') }}">
        @csrf

        <div class="card card-secondary">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0">
                    <i class="fas fa-random"></i> Packing Central Switching
                </h5>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>

            <div class="card-body">

                {{-- ── Step 1 : Pilih Source & Tujuan ── --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label class="form-label"><small><b>Asal PO</b></small></label>
                        <select class="form-control select2bs4"
                            id="cbono"
                            name="cbono"
                            style="width:100%">
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end justify-content-center pb-1">
                        <span class="fs-4 text-secondary fw-bold">→</span>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label"><small><b>Tujuan PO</b></small></label>
                        <select class="form-control select2bs4"
                            id="cbo_tujuan"
                            name="tujuan"
                            style="width:100%">
                        </select>
                    </div>
                </div>

                {{-- ── Visual Banner FROM → TO ── --}}
                <div class="switch-banner" id="switch-banner" style="display:none!important">
                    <div class="switch-side from">
                        <span class="side-label">Asal</span>
                        <span class="side-value" id="banner-from-notrans">—</span>
                        <span class="side-sub" id="banner-from-info">No. Transaksi belum dipilih</span>
                    </div>
                    <div class="switch-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                    <div class="switch-side to">
                        <span class="side-label">Tujuan</span>
                        <span class="side-value" id="banner-to-line">—</span>
                        <span class="side-sub" id="banner-to-info">Tujuan belum dipilih</span>
                    </div>
                </div>

                {{-- ── Preview Table ── --}}
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <label class="form-label mb-0"><small><b>Preview Item</b></small></label>
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-muted">Total Switch:</small>
                        <span class="qty-badge" id="lbl-total-switch">0</span>
                        <small class="text-muted">PCS</small>
                    </div>
                </div>

                <div class="table-responsive mb-3">
                    <table id="tbl-preview" class="table table-bordered table-sm table-striped w-100 text-nowrap">
                        <thead class="table-secondary text-center">
                            <tr>
                                <th>#</th>
                                <th>Line Asal</th>
                                <th>Barcode</th>
                                <th>PO</th>
                                <th>WS</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Dest</th>
                                <th>Qty Packing In</th>
                                <th>Qty Switch</th>
                            </tr>
                        </thead>
                        <tbody id="tbl-preview-body">
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    Pilih No. Transaksi terlebih dahulu
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-bold text-center">
                                <td colspan="8" class="text-end">Total</td>
                                <td><span id="foot-qty-in">0</span></td>
                                <td><span id="foot-qty-switch">0</span></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <input type="hidden" name="packing_packing_in_id" id="packing_packing_in_id">
                <input type="hidden" name="asal_ppic_master_so_id" id="asal_ppic_master_so_id">
                <input type="hidden" name="asal_so_det_id" id="asal_so_det_id">

                <input type="hidden" name="tujuan_ppic_master_so_id" id="tujuan_ppic_master_so_id">
                <input type="hidden" name="tujuan_so_det_id" id="tujuan_so_det_id">
                <input type="hidden" name="tujuan_po" id="tujuan_po">
                <input type="hidden" name="tujuan_barcode" id="tujuan_barcode">
                <input type="hidden" name="tujuan_dest" id="tujuan_dest">

                {{-- ── Actions ── --}}
                <div class="d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-warning" onclick="resetForm()">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-success px-4" id="btn-simpan" disabled>
                        <i class="fas fa-check"></i> Simpan Switching
                    </button>
                </div>

            </div>
        </div>
    </form>

    {{-- ═══════════════ LIST TRANSAKSI ═══════════════ --}}
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">
                <i class="fas fa-list"></i> Riwayat Central Switching
            </h5>
        </div>
        <div class="card-body">

            <div class="d-flex align-items-end flex-wrap gap-3 mb-3">
                <div>
                    <label class="form-label"><small><b>Tgl Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal"
                        value="{{ date('Y-m-d') }}" oninput="listReload()">
                </div>
                <div>
                    <label class="form-label"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir"
                        value="{{ date('Y-m-d') }}" oninput="listReload()">
                </div>
                <div class="ms-auto">
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="exportExcel()">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="tbl-list" class="table table-bordered table-striped w-100 text-nowrap">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>No. Trans</th>
                            <th>Tgl. Trans</th>
                            <th>No. Trans Packing In</th>
                            <th>Line Asal</th>
                            <th>PO Asal</th>
                            <th>Tujuan</th>
                            <th>Barcode</th>
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
                        <tr class="fw-bold">
                            <th colspan="12"></th>
                            <th>
                                <input type="text" class="form-control form-control-sm text-center"
                                    style="width:80px" readonly id="foot-list-qty">
                            </th>
                            <th colspan="2">PCS</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>

@endsection

@section('custom-script')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        /* ── Select2 ── */
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });
        $('.select2bs4').select2({ theme: 'bootstrap4' });

        /* ── State ── */
        let sourceSelected = false;
        let tujuanSelected = false;

        /* ── Ketika No. Trans dipilih ── */
        $('#cbono').select2({
            theme: 'bootstrap4',
            placeholder: 'Pilih No. Transaksi',
            minimumInputLength: 3, // baru search setelah 3 karakter
            ajax: {
                url: '{{ route('getDataAsalPo_packing_central_switching') }}',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return {
                        search: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(item => ({
                            id: item.id_ppic_master_so,
                            text: item.po + ' | ' + item.color + ' | ' + item.size,
                            packing_packing_in_id: item.packing_packing_in_id,
                            so_det_id: item.so_det_id,
                            po: item.po,
                            ws: item.ws,
                            color: item.color,
                            size: item.size,
                            qty: item.qty_sisa
                        }))
                    };
                }
            }
        });

        $('#cbono').on('select2:select', function(e) {
            let data = e.params.data;

            $('#asal_ppic_master_so_id').val(data.id);
            $('#asal_so_det_id').val(data.so_det_id);
            $('#packing_packing_in_id').val(data.packing_packing_in_id);

            $('#banner-from-notrans').html(`
                <table style="font-size:12px">
                    <tr><td style="width:50px">PO</td><td style="width:10px">:</td><td>${data.po}</td></tr>
                    <tr><td>WS</td><td>:</td><td>${data.ws}</td></tr>
                    <tr><td>Color</td><td>:</td><td>${data.color}</td></tr>
                    <tr><td>Size</td><td>:</td><td>${data.size}</td></tr>
                    <tr><td>Qty</td><td>:</td><td>${data.qty}</td></tr>
                </table>
            `);

            $('#banner-from-info').text('Transaksi dipilih');

            sourceSelected = !!data.id;

            updateBanner();
            loadPreview(data.id);
            checkSubmitReady();
        });

        // function onSourceChange() {
        //     const val = $('#cbono').val();
        //     const selected = $('#cbono option:selected');

        //     $('#asal_ppic_master_so_id').val(val);
        //     $('#asal_so_det_id').val(selected.data('so-det-id'));
        //     $('#packing_packing_in_id').val(selected.data('packing-packing-in-id'));

        //     sourceSelected = !!val;

        //     if (val) {
        //         $('#banner-from-notrans').html(`
        //             <table style="font-size:12px">
        //                 <tr><td style="width:50px">PO</td><td style="width:10px">:</td><td>${selected.data('po')}</td></tr>
        //                 <tr><td>WS</td><td>:</td><td>${selected.data('ws')}</td></tr>
        //                 <tr><td>Color</td><td>:</td><td>${selected.data('color')}</td></tr>
        //                 <tr><td>Size</td><td>:</td><td>${selected.data('size')}</td></tr>
        //                 <tr><td>Qty</td><td>:</td><td>${selected.data('qty')}</td></tr>
        //             </table>
        //         `);

        //         $('#banner-from-info').text('Transaksi dipilih');
        //     } else {
        //         $('#banner-from-notrans').html('—');
        //         $('#banner-from-info').text('No. Transaksi belum dipilih');
        //     }

        //     updateBanner();
        //     loadPreview(val);
        //     checkSubmitReady();
        // }

        /* ── Ketika Tujuan dipilih ── */
        $('#cbo_tujuan').select2({
            theme: 'bootstrap4',
            placeholder: 'Pilih Tujuan',
            minimumInputLength: 3,
            ajax: {
                url: '{{ route('getDataTujuanPo_packing_central_switching') }}',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return {
                        search: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(item => ({
                            id: item.id,
                            so_det_id: item.id_so_det,
                            po: item.po,
                            barcode: item.barcode,
                            dest: item.dest,
                            ws: item.ws,
                            color: item.color,
                            size: item.size,
                            qty: item.qty_po,
                            text: item.po + ' | ' + item.color + ' | ' + item.size
                        }))
                    };
                }
            }
        });

        $('#cbo_tujuan').on('select2:select', function(e) {

            let data = e.params.data;

            $('#tujuan_ppic_master_so_id').val(data.id);
            $('#tujuan_so_det_id').val(data.so_det_id);
            $('#tujuan_po').val(data.po);
            $('#tujuan_barcode').val(data.barcode);
            $('#tujuan_dest').val(data.dest);

            tujuanSelected = !!data.id;

            $('#banner-to-line').html(`
                <table style="font-size:12px">
                    <tr><td style="width:50px">PO</td><td style="width:10px">:</td><td>${data.po}</td></tr>
                    <tr><td>WS</td><td>:</td><td>${data.ws}</td></tr>
                    <tr><td>Color</td><td>:</td><td>${data.color}</td></tr>
                    <tr><td>Size</td><td>:</td><td>${data.size}</td></tr>
                    <tr><td>Qty</td><td>:</td><td>${data.qty}</td></tr>
                </table>
            `);

            $('#banner-to-info').text('Tujuan dipilih');

            updateBanner();
            checkSubmitReady();
        });
        // function onTujuanChange() {
        //     const val = $('#cbo_tujuan').val();
        //     const selected = $('#cbo_tujuan option:selected');

        //     $('#tujuan_ppic_master_so_id').val(val);
        //     $('#tujuan_so_det_id').val(selected.data('so-det-id'));
        //     $('#tujuan_po').val(selected.data('po'));
        //     $('#tujuan_barcode').val(selected.data('barcode'));
        //     $('#tujuan_dest').val(selected.data('dest'));

        //     tujuanSelected = !!val;

        //     if (val) {
        //         $('#banner-to-line').html(`
        //             <table style="font-size:12px">
        //                 <tr><td style="width:50px">PO</td><td style="width:10px">:</td><td>${selected.data('po')}</td></tr>
        //                 <tr><td>WS</td><td>:</td><td>${selected.data('ws')}</td></tr>
        //                 <tr><td>Color</td><td>:</td><td>${selected.data('color')}</td></tr>
        //                 <tr><td>Size</td><td>:</td><td>${selected.data('size')}</td></tr>
        //                 <tr><td>Qty</td><td>:</td><td>${selected.data('qty')}</td></tr>
        //             </table>
        //         `);

        //         $('#banner-to-info').text('Tujuan dipilih');
        //     } else {
        //         $('#banner-to-line').html('—');
        //         $('#banner-to-info').text('No. Tujuan belum dipilih');
        //     }

        //     updateBanner();
        //     checkSubmitReady();
        // }

        function updateBanner() {
            if (sourceSelected || tujuanSelected) {
                $('#switch-banner').css('display', 'flex');
            } else {
                $('#switch-banner').hide();
            }
        }

        /* ── Load preview (simulasi — ganti dengan AJAX saat route siap) ── */
        function loadPreview(noTrans) {
            const $body = $('#tbl-preview-body');
            if (!noTrans) {
                $body.html(`
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            Pilih No. Transaksi terlebih dahulu
                        </td>
                    </tr>`);
                updateTotals();
                return;
            }

            $body.html(`
                <tr>
                    <td colspan="10" class="text-center text-muted py-3">
                        <i class="fas fa-spinner fa-spin"></i> Memuat data...
                    </td>
                </tr>`);

            $.ajax({
                url: "{{ route('preview_packing_central_switching') }}",
                type: "GET",
                data: {
                    id_ppic_master_so: noTrans
                },
                success: function(res) {
                    renderPreview(res);
                },
                error: function() {
                    $('#tbl-preview-body').html(`
                        <tr>
                            <td colspan="10" class="text-center text-danger py-3">
                                Gagal memuat data
                            </td>
                        </tr>
                    `);
                }
            });
        }

        function renderPreview(rows) {
            if (!rows || rows.length === 0) {
                $('#tbl-preview-body').html(`
                    <tr>
                        <td colspan="10" class="text-center text-muted py-3">Tidak ada data</td>
                    </tr>`);
                updateTotals();
                return;
            }

            let html = '';
            rows.forEach((r, i) => {
                html += `
                <tr>
                    <td class="text-center">${i + 1}</td>
                    <td>${r.line}</td>
                    <td>${r.barcode}</td>
                    <td>${r.po}</td>
                    <td>${r.ws}</td>
                    <td>${r.color}</td>
                    <td>${r.size}</td>
                    <td>${r.dest}</td>
                    <td class="text-center">${r.qty_sisa}</td>
                    <td class="text-center">
                        <input type="number"
                            class="form-control form-control-sm text-center input-switch"
                            style="width:75px; margin:auto"
                            name="qty_switch"
                            value="${r.qty_sisa}"
                            min="0"
                            max="${r.qty_sisa}"
                            oninput="updateTotals()"
                        >
                    </td>
                </tr>`;
            });

            $('#tbl-preview-body').html(html);
            updateTotals();
            checkSubmitReady();
        }

        function updateTotals() {
            let totalIn = 0, totalSwitch = 0;

            /* qty packing in — baca dari cell ke-9 (index 8) */
            $('#tbl-preview tbody tr').each(function () {
                const cellIn = $(this).find('td').eq(8).text().trim();
                totalIn += parseInt(cellIn) || 0;
            });

            /* qty switch — dari input */
            document.querySelectorAll('.input-switch').forEach(el => {
                totalSwitch += parseInt(el.value) || 0;
            });

            $('#foot-qty-in').text(totalIn);
            $('#foot-qty-switch').text(totalSwitch);
            $('#lbl-total-switch').text(totalSwitch);
        }

        function checkSubmitReady() {
            const hasRows = document.querySelectorAll('.input-switch').length > 0;
            $('#btn-simpan').prop('disabled', !(sourceSelected && tujuanSelected && hasRows));
        }

        /* ── Reset ── */
        function resetForm() {
            $('#cbono').val('').trigger('change');
            $('#cbo_tujuan').val('').trigger('change');
            sourceSelected = false;
            tujuanSelected = false;
            $('#switch-banner').hide();
            loadPreview(null);
            checkSubmitReady();
        }

        /* ── Submit ── */
        $('#form-switching').on('submit', function (e) {
            e.preventDefault();
            const form = this;
            
            Swal.fire({
                title: 'Simpan Switching?',
                html: `Pindahkan item ke <b>${$('#cbo_tujuan option:selected').text()}</b>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#198754',
            }).then(res => {
                if (res.isConfirmed) {
                    submitForm(form, e);
                }
            });
        });

        /* ── List DataTable ── */
        $('#tbl-list thead tr').clone(true).appendTo('#tbl-list thead');
        $('#tbl-list thead tr:eq(1) th').each(function (i) {
            $(this).html('<input type="text" class="form-control form-control-sm">');
            $('input', this).on('keyup change', function () {
                if (dtList.column(i).search() !== this.value) {
                    dtList.column(i).search(this.value).draw();
                }
            });
        });

        let dtList = $('#tbl-list').DataTable({
            footerCallback(row, data, start, end, display) {
                const api = this.api();
                const intVal = v => (typeof v === 'string' ? v.replace(/[\$,]/g, '') * 1 : typeof v === 'number' ? v : 0);
                const total  = api.column(12).data().reduce((a, b) => intVal(a) + intVal(b), 0);
                $(api.column(0).footer()).html('Total');
                $(api.column(12).footer()).html(total);
                $('#foot-list-qty').val(total);
            },
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollY: '320px',
            scrollX: true,
            scrollCollapse: true,
            ajax: {
                url: "{{ route('getData_packing_central_switching') }}",
                data: d => {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo   = $('#tgl-akhir').val();
                },
            },
            columns: [
                { data: 'no_trans' },
                { data: 'tgl_trans' },
                { data: 'no_trans_packing_in' },
                { data: 'line' },
                { data: 'po' },
                { data: 'tujuan' },
                { data: 'barcode' },
                { data: 'ws' },
                { data: 'styleno' },
                { data: 'color' },
                { data: 'size' },
                { data: 'dest' },
                { data: 'qty_switch' },
                { data: 'created_by_username' },
                { data: 'created_at' },
            ],
        });

        function listReload() { dtList.ajax.reload(); }

        /* ── Export Excel ── */
        function exportExcel() {
            const from = $('#tgl-awal').val();
            const to   = $('#tgl-akhir').val();
            Swal.fire({
                title: 'Please Wait...', html: 'Exporting Data...',
                didOpen: () => Swal.showLoading(), allowOutsideClick: false,
            });
            $.ajax({
                type: 'get', url: '{{ route('export_excel_packing_central_switching') }}', /* TODO: route export */
                data: { from, to },
                xhrFields: { responseType: 'blob' },
                success(res) {
                    swal.close();
                    Swal.fire({ title: 'Data Sudah Di Export!', icon: 'success', allowOutsideClick: false });
                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(new Blob([res]));
                    a.download = `Central Switching ${from} sd ${to}.xlsx`;
                    a.click();
                },
            });
        }
    </script>
@endsection
