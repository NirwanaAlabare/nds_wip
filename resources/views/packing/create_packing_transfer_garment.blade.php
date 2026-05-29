@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        /* ── Stepper ── */
        .step-row {
            display: flex;
            gap: 16px;
            margin-bottom: 18px;
        }

        .step-aside {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex-shrink: 0;
        }

        .step-badge {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 15px;
            transition: background .25s, box-shadow .25s;
            flex-shrink: 0;
        }

        .step-badge.st-locked {
            background: #ced4da;
            color: #fff;
        }

        .step-badge.st-active {
            background: #007bff;
            color: #fff;
            box-shadow: 0 0 0 4px rgba(0, 123, 255, .18);
        }

        .step-badge.st-done {
            background: #28a745;
            color: #fff;
        }

        .step-connector {
            width: 2px;
            flex: 1;
            min-height: 14px;
            background: #dee2e6;
            margin: 4px 0;
            transition: background .25s;
        }

        .step-connector.done {
            background: #28a745;
        }

        .step-body {
            flex: 1;
            padding-bottom: 4px;
            transition: opacity .2s;
        }

        .step-body.locked {
            opacity: .38;
            pointer-events: none;
            user-select: none;
        }

        .step-title {
            font-weight: 700;
            font-size: .9rem;
            margin-bottom: 6px;
            color: #343a40;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ── Filter box ── */
        .filter-box {
            border: 2px dashed #007bff;
            border-radius: 8px;
            background: #f4f8ff;
            padding: 14px 16px 10px;
            transition: border-color .25s, background .25s;
        }

        .filter-box.done {
            border-style: solid;
            border-color: #28a745;
            background: #f2fff5;
        }

        .or-pill {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            padding-bottom: 6px;
        }

        .or-pill span {
            border: 2px solid #ced4da;
            border-radius: 20px;
            padding: 2px 9px;
            font-size: 10px;
            font-weight: 700;
            color: #6c757d;
            background: #fff;
            letter-spacing: 1px;
        }

        /* ── Garment select ── */
        .garment-wrap .select2-container--bootstrap4 .select2-selection {
            min-height: 38px;
        }

        /* ── Qty row ── */
        .qty-input {
            font-size: 1.25rem;
            font-weight: 600;
            text-align: center;
        }

        /* ── Select2: teks sekunder tetap terbaca saat item di-highlight (bg biru) ── */
        .select2-results__option--highlighted .opt-secondary {
            color: rgba(255, 255, 255, 0.82) !important;
        }
        .select2-results__option--highlighted .opt-badge-green {
            background: rgba(255,255,255,0.25) !important;
            color: #fff !important;
        }
        .select2-results__option--highlighted .opt-badge-red {
            background: rgba(255,255,255,0.25) !important;
            color: #fff !important;
        }
        .select2-results__option--highlighted .opt-primary {
            color: #fff !important;
        }

        /* ── Table ── */
        #datatable_tmp thead th {
            background: #f1f3f5;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        #datatable_tmp tfoot th {
            background: #f8f9fa;
            font-weight: 700;
        }
    </style>
@endsection

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0">
                    <i class="fas fa-shirt"></i> Input Transfer Garment Dari Sewing
                </h5>
                <a href="{{ route('transfer-garment') }}" class="btn btn-sm btn-light">
                    <i class="fa fa-reply"></i> Kembali
                </a>
            </div>
        </div>

        <form id="form_h" name="form_h" method="post" autocomplete="off">
            <input type="hidden" name="user" id="user" value="{{ $user }}">
            <div class="card-body pt-3 pb-2">

                {{-- Step 1: Tujuan --}}
                <div class="step-row">
                    <div class="step-aside">
                        <div class="step-badge st-active" id="badge1">1</div>
                        <div class="step-connector" id="line1"></div>
                    </div>
                    <div class="step-body" id="body1">
                        <div class="step-title"><i class="fas fa-map-marker-alt text-primary"></i> Tujuan</div>
                        <select class="form-control select2bs4" id="cbotuj" name="cbotuj" style="width:100%">
                            <option value="" disabled selected>-- Pilih Tujuan --</option>
                            @foreach ($data_tujuan as $d)
                                <option value="{{ $d->isi }}">{{ $d->tampil }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Step 2: Filter Line / PO --}}
                <div class="step-row">
                    <div class="step-aside">
                        <div class="step-badge st-locked" id="badge2">2</div>
                        <div class="step-connector" id="line2"></div>
                    </div>
                    <div class="step-body locked" id="body2">
                        <div class="step-title">
                            <i class="fas fa-filter text-primary"></i> Filter Barang
                            <small class="text-muted font-weight-normal" style="font-size:.78rem">
                                &mdash; mulai dari LINE atau PO, bebas
                            </small>
                        </div>
                        <div class="filter-box" id="filter-box">
                            <div class="row align-items-end">
                                <div class="col-5">
                                    <label class="small font-weight-bold text-primary mb-1">
                                        <i class="fas fa-layer-group mr-1"></i>LINE
                                    </label>
                                    <select class="form-control select2bs4" id="cboline" name="cboline"
                                        style="width:100%">
                                        <option value="">-- Pilih Line --</option>
                                        @foreach ($data_line as $d)
                                            <option value="{{ $d->isi }}">{{ $d->tampil }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2 or-pill"><span>DAN</span></div>
                                <div class="col-5">
                                    <label class="small font-weight-bold text-success mb-1">
                                        <i class="fas fa-file-invoice mr-1"></i>NO. PO
                                    </label>
                                    <select class="form-control select2bs4" id="cbopo" name="cbopo"
                                        style="width:100%">
                                        <option value="">-- Pilih PO --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small id="filter-hint" class="text-muted"></small>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetFilter()">
                                    <i class="fas fa-times"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 3: Garment --}}
                <div class="step-row">
                    <div class="step-aside">
                        <div class="step-badge st-locked" id="badge3">3</div>
                        <div class="step-connector" id="line3"></div>
                    </div>
                    <div class="step-body locked garment-wrap" id="body3">
                        <div class="step-title">
                            <i class="fas fa-tshirt text-primary"></i> Garment
                            <small class="text-muted font-weight-normal ml-1" style="font-size:.75rem">
                                — filter opsional
                            </small>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6">
                                <select class="form-control form-control-sm select2bs4" id="cbo_filter_color"
                                    style="width:100%" disabled>
                                    <option value="">Semua Color</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <select class="form-control form-control-sm select2bs4" id="cbo_filter_size"
                                    style="width:100%" disabled>
                                    <option value="">Semua Size</option>
                                </select>
                            </div>
                        </div>
                        <select class="form-control select2bs4" id="cbogarment" name="cbogarment" style="width:100%"
                            disabled>
                            <option value="">-- Pilih Line &amp; PO dulu --</option>
                        </select>
                    </div>
                </div>

                {{-- Step 4: Qty + Tambah --}}
                <div class="step-row" style="margin-bottom:4px">
                    <div class="step-aside">
                        <div class="step-badge st-locked" id="badge4">4</div>
                    </div>
                    <div class="step-body locked" id="body4">
                        <div class="step-title"><i class="fas fa-sort-numeric-up text-primary"></i> Qty</div>
                        <div class="row align-items-center">
                            <div class="col-7 col-md-8">
                                <div class="input-group">
                                    <input type="number" class="form-control qty-input" id="txtqty" name="txtqty"
                                        min="0" autocomplete="off" placeholder="0" disabled>
                                    <div class="input-group-append">
                                        <span class="input-group-text font-weight-bold px-3">PCS</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-5 col-md-4">
                                <button type="button" id="btn-tambah" class="btn btn-success btn-block font-weight-bold"
                                    style="height:38px" onclick="tambah_data()" disabled>
                                    <i class="fas fa-plus mr-1"></i>Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>

        {{-- List Garment --}}
        <div class="row mx-0">
            <div class="col-12">
                <div class="card card-primary card-outline mb-0">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-list"></i> List Garment</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable_tmp" class="table table-bordered text-nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Line</th>
                                        <th>PO</th>
                                        <th>WS</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Qty</th>
                                        <th>Act</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th colspan="5"></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <a class="btn btn-outline-warning" onclick="undo()">
                                <i class="fas fa-sync-alt"></i> Undo
                            </a>
                            <a class="btn btn-outline-success" onclick="simpan()">
                                <i class="fas fa-check"></i> Simpan
                            </a>
                        </div>
                    </div>
                </div>
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
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Semua select pakai bootstrap4 theme (kecuali yang punya template custom)
        $('.select2bs4').not('#cbogarment').not('#cbopo').not('#cbo_filter_color').not('#cbo_filter_size')
            .select2({ theme: 'bootstrap4' });
        $('#cbo_filter_color, #cbo_filter_size').select2({ theme: 'bootstrap4' });

        // ── Template PO dropdown ──
        function poTemplate(option) {
            if (!option.id) return option.text;
            const el          = $(option.element);
            const styleno     = el.data('styleno')     || '';
            const stylenoProd = el.data('stylenoprod') || '';
            return $(`<div style="padding:2px 0;line-height:1.4">
                <div class="opt-primary" style="font-weight:700;font-size:.88rem;color:#212529">${option.id}</div>
                <div class="opt-secondary" style="font-size:.75rem;color:#6c757d">${styleno}${stylenoProd ? ' · ' + stylenoProd : ''}</div>
            </div>`);
        }
        function poSelection(option) {
            if (!option.id) return option.text;
            const el      = $(option.element);
            const styleno = el.data('styleno') || '';
            return $(`<span><strong>${option.id}</strong>${styleno ? ' <small style="color:#6c757d">— ' + styleno + '</small>' : ''}</span>`);
        }
        function initPoSelect2() {
            $('#cbopo').select2({
                theme: 'bootstrap4',
                templateResult:    poTemplate,
                templateSelection: poSelection,
            });
        }
        initPoSelect2();

        // Template dropdown list garment — 2 baris, mobile friendly
        function garmentTemplate(option) {
            if (!option.id) return option.text;
            const el      = $(option.element);
            const selisih = parseInt(el.data('selisih') ?? 0);
            const isMinus = selisih < 0;
            const ws      = el.data('ws')    || '-';
            const color   = el.data('color') || '-';
            const size    = el.data('size')  || '-';
            const dest    = el.data('dest')  || '-';
            const qty     = el.data('qty')   !== undefined ? el.data('qty') : selisih;
            const badge   = isMinus
                ? `<span class="opt-badge-red" style="background:#dc3545;color:#fff;border-radius:4px;padding:1px 6px;font-size:.7rem;font-weight:700">${selisih} PCS</span>`
                : `<span class="opt-badge-green" style="background:#d1fae5;color:#065f46;border-radius:4px;padding:1px 6px;font-size:.7rem;font-weight:700">${qty} PCS</span>`;
            return $(`<div style="padding:2px 0;line-height:1.5">
                <div class="opt-primary" style="font-weight:700;font-size:.88rem;color:${isMinus ? '#dc3545' : '#212529'}">
                    ${ws} <span class="opt-secondary" style="color:#6c757d;font-weight:400;font-size:.8rem">· ${color} / ${size}</span>
                </div>
                <div class="opt-secondary" style="font-size:.75rem;color:#6c757d;display:flex;justify-content:space-between;align-items:center;margin-top:1px">
                    <span>Dest: ${dest}</span>${badge}
                </div>
            </div>`);
        }

        // Template teks di kotak select setelah item dipilih
        function garmentSelection(option) {
            if (!option.id) return option.text;
            const el      = $(option.element);
            const selisih = parseInt(el.data('selisih') ?? 0);
            const ws      = el.data('ws')    || '';
            const color   = el.data('color') || '';
            const size    = el.data('size')  || '';
            const qty     = el.data('qty')   !== undefined ? el.data('qty') : selisih;
            const css     = selisih < 0 ? 'color:#dc3545;font-weight:700' : '';
            return $(`<span style="${css}">${ws} · ${color} / ${size} — ${qty} PCS</span>`);
        }

        $('#cbogarment').select2({
            theme: 'bootstrap4',
            templateResult: garmentTemplate,
            templateSelection: garmentSelection,
        });
    </script>
    <script>
        // Snapshot of all lines from server (used to restore dropdown)
        const allLines = @json($data_line);

        // Guard to prevent change-event loops when updating selects programmatically
        let updating = false;

        // Cache semua garment dari server untuk filter clientside
        let allGarments = [];

        $(document).ready(function() {
            reset();
            loadAllPo();
            restoreAllLines();
            clear_h();
            dataTableTmpReload();

            // ---- TUJUAN changed ----
            $('#cbotuj').on('change', function() {
                updateStep2();
            });

            // ---- LINE changed ----
            $('#cboline').on('change', function() {
                if (updating) return;
                const line = $(this).val();
                const po = $('#cbopo').val();
                if (line && !po) {
                    // Belum ada PO → filter PO berdasarkan line
                    filterPoByLine(line);
                    setFilterHint('Line <strong>' + line + '</strong> dipilih &mdash; sekarang pilih PO');
                } else if (line && po) {
                    // Sudah ada PO → jangan reset PO, hanya update hint
                    setFilterHint('<i class="fas fa-check-circle text-success"></i> Line: <strong>' + line +
                        '</strong> &amp; PO: <strong>' + po + '</strong>');
                } else if (!line && po) {
                    // Line dikosongkan, PO masih ada
                    setFilterHint('PO <strong>' + po + '</strong> dipilih &mdash; sekarang pilih Line');
                } else {
                    // Dua-duanya kosong
                    loadAllPo();
                    setFilterHint('');
                }
                updateStep3();
                reloadGarment();
            });

            // ---- PO changed ----
            $('#cbopo').on('change', function() {
                if (updating) return;
                const po = $(this).val();
                const line = $('#cboline').val();
                if (po && !line) {
                    // Belum ada Line → filter Line berdasarkan PO
                    filterLineByPo(po);
                    setFilterHint('PO <strong>' + po + '</strong> dipilih &mdash; sekarang pilih Line');
                } else if (po && line) {
                    // Sudah ada Line → jangan reset Line, hanya update hint
                    setFilterHint('<i class="fas fa-check-circle text-success"></i> Line: <strong>' + line +
                        '</strong> &amp; PO: <strong>' + po + '</strong>');
                } else if (!po && line) {
                    // PO dikosongkan, Line masih ada
                    setFilterHint('Line <strong>' + line + '</strong> dipilih &mdash; sekarang pilih PO');
                } else {
                    // Dua-duanya kosong
                    restoreAllLines();
                    setFilterHint('');
                }
                updateStep3();
                reloadGarment();
            });

            // Garment dipilih: kelola state step 3→4 + block jika minus
            $('#cbogarment').on('change', function() {
                const val = $(this).val();
                const selisih = parseInt($('#cbogarment option:selected').data('selisih') ?? 0);
                const isMinus = val && selisih < 0;
                const canInput = val && !isMinus;

                $('#txtqty').prop('disabled', !canInput);
                $('#btn-tambah').prop('disabled', !canInput);
                $('#body4').toggleClass('locked', !canInput);

                if (val) {
                    $('#badge3').removeClass('st-active st-locked').addClass('st-done')
                        .html('<i class="fas fa-check" style="font-size:13px"></i>');
                    $('#line3').addClass('done');
                    $('#badge4').removeClass('st-locked').addClass(canInput ? 'st-active' : 'st-locked')
                        .html('4');
                } else {
                    $('#badge3').removeClass('st-done').addClass('st-active').html('3');
                    $('#line3').removeClass('done');
                    $('#badge4').removeClass('st-active st-done').addClass('st-locked').html('4');
                }

                if (isMinus) {
                    document.getElementById('txtqty').value = '';
                    iziToast.error({
                        title: 'Tidak Bisa Input!',
                        message: 'Qty sisa garment ini MINUS (' + selisih +
                            ' PCS). Input diblokir.',
                        position: 'topCenter',
                        timeout: 5000,
                    });
                } else if (canInput) {
                    // Set max sesuai sisa stok garment yang dipilih
                    $('#txtqty').attr('max', selisih).focus().select();
                }
            });

            // Enter on qty = Tambah
            $('#txtqty').on('keydown', function(e) {
                if (e.key === 'Enter') tambah_data();
            });

            // ---- Filter Color / Size (opsional) ----
            $('#cbo_filter_color').on('change', function() {
                applyGarmentFilter();
            });
            $('#cbo_filter_size').on('change', function() {
                applyGarmentFilter();
            });
        });

        // ---- Filter helpers ----

        // Helper: set badge state
        function setBadge(id, state) {
            // state: 'locked' | 'active' | 'done'
            $('#' + id).removeClass('st-locked st-active st-done').addClass('st-' + state);
            if (state === 'done') $('#' + id).html('<i class="fas fa-check" style="font-size:13px"></i>');
            else $('#' + id).html($('#' + id).data('num'));
        }

        // Lock/unlock step 2 berdasarkan apakah Tujuan sudah dipilih
        function updateStep2() {
            const tujuan = $('#cbotuj').val();
            const ready = !!tujuan;

            $('#body2').toggleClass('locked', !ready);
            if (ready) {
                $('#badge1').removeClass('st-active').addClass('st-done')
                    .html('<i class="fas fa-check" style="font-size:13px"></i>');
                $('#line1').addClass('done');
                $('#badge2').removeClass('st-locked').addClass('st-active').html('2');
            } else {
                $('#badge1').removeClass('st-done').addClass('st-active').html('1');
                $('#line1').removeClass('done');
                $('#badge2').removeClass('st-active').addClass('st-locked').html('2');
                // Reset step 2 ke awal
                updating = true;
                $('#cboline').val('').trigger('change.select2');
                $('#cbopo').val('').trigger('change.select2');
                updating = false;
                setFilterHint('');
                updateStep3();
            }
        }

        // Enable/disable step 3 & 4 berdasarkan apakah Line & PO sudah terisi
        function updateStep3() {
            const line = $('#cboline').val();
            const po = $('#cbopo').val();
            const ready = !!(line && po);

            $('#cbogarment').prop('disabled', !ready).trigger('change.select2');
            $('#body3').toggleClass('locked', !ready);

            if (ready) {
                $('#badge2').removeClass('st-active').addClass('st-done')
                    .html('<i class="fas fa-check" style="font-size:13px"></i>');
                $('#line2').addClass('done');
                $('#badge3').removeClass('st-locked').addClass('st-active').html('3');
                $('#filter-box').addClass('done');
            } else {
                const tujuan = $('#cbotuj').val();
                $('#badge2').removeClass('st-done').addClass(tujuan ? 'st-active' : 'st-locked').html('2');
                $('#line2').removeClass('done');
                $('#badge3').removeClass('st-active st-done').addClass('st-locked').html('3');
                $('#filter-box').removeClass('done');
                updating = true;
                $('#cbogarment').html('<option value="">-- Pilih Line &amp; PO dulu --</option>').trigger('change.select2');
                updating = false;
                allGarments = [];
                $('#cbo_filter_color').html('<option value="">Semua Color</option>').val('').prop('disabled', true).trigger(
                    'change.select2');
                $('#cbo_filter_size').html('<option value="">Semua Size</option>').val('').prop('disabled', true).trigger(
                    'change.select2');
                // Pastikan step 4 juga terkunci
                $('#body4').addClass('locked');
                $('#txtqty').prop('disabled', true);
                $('#btn-tambah').prop('disabled', true);
                $('#badge4').removeClass('st-active st-done').addClass('st-locked').html('4');
                $('#line3').removeClass('done');
            }
        }

        function setFilterHint(msg) {
            $('#filter-hint').html(msg || '');
        }

        function loadAllPo() {
            updating = true;
            const html = $.ajax({
                type: 'GET',
                url: '{{ route('get_po') }}',
                data: { cbo_line: '' },
                async: false,
            }).responseText;
            $('#cbopo').html(html).val('');
            initPoSelect2();
            updating = false;
        }

        function filterPoByLine(line) {
            updating = true;
            const html = $.ajax({
                type: 'GET',
                url: '{{ route('get_po') }}',
                data: { cbo_line: line },
                async: false,
            }).responseText;
            $('#cbopo').html(html).val('');
            initPoSelect2();
            updating = false;
        }

        function filterLineByPo(po) {
            updating = true;
            const html = $.ajax({
                type: 'GET',
                url: '{{ route('get_line_by_po') }}',
                data: {
                    cbo_po: po
                },
                async: false,
            }).responseText;
            $('#cboline').html(html).val('').trigger('change.select2');
            updating = false;
        }

        function restoreAllLines() {
            updating = true;
            let html = '<option value="">-- Pilih Line --</option>';
            allLines.forEach(l => {
                html += `<option value="${l.isi}">${l.tampil}</option>`;
            });
            $('#cboline').html(html).val('').trigger('change.select2');
            updating = false;
        }

        function resetFilter() {
            updating = true;
            restoreAllLines();
            loadAllPo();
            updating = false;
            setFilterHint('');
            updateStep3();
        }

        function reloadGarment() {
            const line = $('#cboline').val();
            const po = $('#cbopo').val();

            if (!line || !po) return;

            const html = $.ajax({
                type: 'GET',
                url: '{{ route('get_garment') }}',
                data: {
                    cbo_line: line,
                    cbo_po: po
                },
                async: false,
            }).responseText;

            if (!html) return;

            // Parse semua garment ke array cache
            const $tmp = $('<select>').html(html);
            allGarments = [];
            $tmp.find('option[value!=""]').each(function() {
                allGarments.push({
                    isi:     $(this).val(),
                    tampil:  $(this).text(),
                    selisih: parseInt($(this).data('selisih') ?? 0),
                    ws:      $(this).data('ws')    ?? '',
                    color:   $(this).data('color') ?? '',
                    size:    $(this).data('size')  ?? '',
                    dest:    $(this).data('dest')  ?? '',
                    qty:     $(this).data('qty')   ?? 0,
                });
            });

            // Isi dropdown Color (unique, sorted)
            const colors = [...new Set(allGarments.map(g => g.color))].filter(Boolean).sort();
            let cHtml = '<option value="">Semua Color</option>';
            colors.forEach(c => cHtml += `<option value="${c}">${c}</option>`);
            $('#cbo_filter_color').html(cHtml).val('').trigger('change.select2');

            // Isi dropdown Size (unique, sorted)
            const sizes = [...new Set(allGarments.map(g => g.size))].filter(Boolean);
            let sHtml = '<option value="">Semua Size</option>';
            sizes.forEach(s => sHtml += `<option value="${s}">${s}</option>`);
            $('#cbo_filter_size').html(sHtml).val('').trigger('change.select2');

            // Enable filter selects
            $('#cbo_filter_color, #cbo_filter_size').prop('disabled', false);

            applyGarmentFilter();
        }

        function applyGarmentFilter() {
            const filterColor = $('#cbo_filter_color').val();
            const filterSize = $('#cbo_filter_size').val();

            let filtered = allGarments;
            if (filterColor) filtered = filtered.filter(g => g.color === filterColor);
            if (filterSize) filtered = filtered.filter(g => g.size === filterSize);

            let html = '<option value="">-- Pilih Garment --</option>';
            filtered.forEach(g => {
                html += `<option value="${g.isi}"`
                      + ` data-selisih="${g.selisih}"`
                      + ` data-ws="${g.ws}"`
                      + ` data-color="${g.color}"`
                      + ` data-size="${g.size}"`
                      + ` data-dest="${g.dest}"`
                      + ` data-qty="${g.qty}">`
                      + `${g.ws} / ${g.color} / ${g.size}</option>`;
            });

            $('#cbogarment').html(html).val('').select2({
                theme: 'bootstrap4',
                templateResult: garmentTemplate,
                templateSelection: garmentTemplate,
            });
        }

        // ---- CRUD ----

        function tambah_data() {
            const cboline = document.form_h.cboline.value;
            const cbopo = document.form_h.cbopo.value;
            const cbogarment = document.form_h.cbogarment.value;
            const txtqty = document.form_h.txtqty.value;

            if (!cboline) {
                iziToast.warning({
                    message: 'Line belum dipilih',
                    position: 'topCenter'
                });
                return;
            }
            if (!cbopo) {
                iziToast.warning({
                    message: 'No. PO belum dipilih',
                    position: 'topCenter'
                });
                return;
            }
            if (!cbogarment) {
                iziToast.warning({
                    message: 'Garment belum dipilih',
                    position: 'topCenter'
                });
                return;
            }
            if (!txtqty || txtqty <= 0) {
                iziToast.warning({ message: 'Qty harus diisi', position: 'topCenter' });
                return;
            }
            const selisihGarment = parseInt($('#cbogarment option:selected').data('selisih') ?? 0);
            if (parseInt(txtqty) > selisihGarment) {
                iziToast.warning({
                    title: 'Qty Melebihi Sisa!',
                    message: 'Maksimal ' + selisihGarment + ' PCS untuk garment ini.',
                    position: 'topCenter',
                    timeout: 4000,
                });
                $('#txtqty').focus().select();
                return;
            }

            $.ajax({
                type: 'post',
                url: '{{ route('store_tmp_trf_garment') }}',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    cboline,
                    cbopo,
                    cbogarment,
                    txtqty
                },
                success: function(res) {
                    if (res.icon === 'salah') {
                        iziToast.warning({
                            message: res.msg,
                            position: 'topCenter'
                        });
                    } else {
                        iziToast.success({
                            message: res.msg,
                            position: 'topCenter'
                        });
                        document.getElementById('txtqty').value = '';
                        reloadGarment(); // reload dulu (reinit Select2 + isi ulang options)
                        $('#cbogarment').val('').trigger('change.select2'); // baru kosongkan pilihan
                    }
                    dataTableTmpReload();
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message ?? 'Gagal menyimpan, coba lagi';
                    iziToast.error({
                        message: msg,
                        position: 'topCenter'
                    });
                },
            });
        }

        function dataTableTmpReload() {
            $('#datatable_tmp').DataTable({
                footerCallback: function(row, data, start, end, display) {
                    const api = this.api();
                    const intVal = i => (typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i ===
                        'number' ? i : 0);
                    const total = api.column(5).data().reduce((a, b) => intVal(a) + intVal(b), 0);
                    $(api.column(0).footer()).html('Total');
                    $(api.column(5).footer()).html(total);
                },
                ordering: false,
                processing: true,
                serverSide: true,
                paging: false,
                destroy: true,
                ajax: {
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('show_tmp_trf_garment') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: d => {
                        d.id = $('#id').val();
                    },
                },
                columns: [{
                        data: 'line'
                    },
                    {
                        data: 'po'
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
                        data: 'qty_tmp_trf_garment'
                    },
                ],
                columnDefs: [{
                    targets: [6],
                    render: (data, type, row) => `
                        <div class='d-flex justify-content-center'>
                            <a class='btn btn-sm' onclick="hapus('${row.id_tmp_trf_garment}')">
                                <i class='fas fa-minus-square fa-lg text-danger'></i>
                            </a>
                        </div>`,
                }],
            });
        }

        function clear_h() {
            updating = true;
            $('#cbotuj').val('').trigger('change.select2');
            $('#cboline').val('').trigger('change.select2');
            $('#cbopo').val('').trigger('change.select2');
            $('#cbogarment').html('<option value="">-- Pilih Line &amp; PO dulu --</option>').val('').trigger(
                'change.select2');
            document.getElementById('txtqty').value = '';
            setFilterHint('');
            updating = false;
            updateStep2();
            updateStep3();
        }

        function hapus(id) {
            const cbopo = document.form_h.cbopo.value;
            $.ajax({
                type: 'post',
                url: '{{ route('hapus_tmp_trf_garment') }}',
                data: {
                    id
                },
                success: function() {
                    iziToast.error({
                        message: 'Data Berhasil Dihapus',
                        position: 'topCenter'
                    });
                    dataTableTmpReload();
                    document.getElementById('txtqty').value = '';
                    $('#cbogarment').val('').trigger('change.select2');
                    reloadGarment();
                },
            });
        }

        function simpan() {
            const cbotuj = document.form_h.cbotuj.value;
            if (!cbotuj) {
                iziToast.error({
                    message: 'Tujuan Kosong Harap Diisi',
                    position: 'topCenter'
                });
                return;
            }
            $.ajax({
                type: 'post',
                url: '{{ route('store_trf_garment') }}',
                data: {
                    cbotuj
                },
                success: function(res) {
                    if (res.icon === 'salah') {
                        iziToast.warning({
                            message: res.msg,
                            position: 'topCenter'
                        });
                    } else {
                        Swal.fire({
                            text: res.msg,
                            icon: 'success',
                            title: res.title
                        });
                    }
                    dataTableTmpReload();
                    clear_h();
                },
                error: function() {
                    iziToast.warning({
                        message: 'Data Temporary Kosong, cek lagi',
                        position: 'topCenter'
                    });
                },
            });
        }

        function undo() {
            const user = document.form_h.user.value;
            $.ajax({
                type: 'post',
                url: '{{ route('undo-trf-garment') }}',
                data: {
                    user
                },
                success: function(res) {
                    if (res.icon === 'salah') {
                        iziToast.warning({
                            message: res.msg,
                            position: 'topCenter'
                        });
                    } else {
                        iziToast.success({
                            message: res.msg,
                            position: 'topCenter'
                        });
                    }
                    dataTableTmpReload();
                },
            });
        }

        function reset() {
            const user = document.form_h.user.value;
            $.ajax({
                type: 'post',
                url: '{{ route('reset-trf-garment') }}',
                data: {
                    user
                },
            });
        }
    </script>
@endsection
