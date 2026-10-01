@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">

    @include('asset_management.partials.tab_style')

    <style type="text/css">
        .op-progress {
            height: 8px;
            margin-top: 14px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }

        .op-progress-bar {
            height: 100%;
            width: 0;
            border-radius: 8px;
            background: #fff;
            transition: width 0.3s ease;
        }

        .op-progress-text {
            margin-top: 6px;
            font-size: 12px;
            opacity: 0.9;
        }

        .tt-stats.is-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        #opSummary {
            font-size: 12px;
            margin-top: 8px;
            color: #6c757d;
        }

        /* ---------- Kelompok hasil ---------- */
        .op-group {
            border: 1px solid #edf0f2;
            border-radius: 14px;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .op-group-head {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 11px 14px;
            border: 0;
            background: #fff;
            text-align: left;
            font-size: 14px;
            font-weight: 700;
        }

        .op-group-icon {
            width: 30px;
            height: 30px;
            flex-shrink: 0;
            border-radius: 9px;
            display: grid;
            place-items: center;
            font-size: 14px;
        }

        .op-group-sub {
            display: block;
            font-size: 11.5px;
            font-weight: 400;
            color: #6c757d;
        }

        .op-group-count {
            margin-left: auto;
            min-width: 34px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            text-align: center;
            color: #fff;
        }

        .op-group-caret {
            color: #adb5bd;
            transition: transform 0.2s ease;
        }

        .op-group.is-open .op-group-caret {
            transform: rotate(180deg);
        }

        .op-group-body {
            display: none;
            border-top: 1px solid #edf0f2;
            background: #fbfcfd;
            padding: 8px 10px;
        }

        .op-group.is-open .op-group-body {
            display: block;
        }

        .op-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .op-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 10px;
            margin-bottom: 5px;
            background: #fff;
            border: 1px solid #edf0f2;
            border-radius: 10px;
            font-size: 13px;
        }

        .op-item-main {
            flex: 1;
            min-width: 0;
        }

        .op-item-code {
            display: block;
            font-family: SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 11px;
            color: #8a939b;
            word-break: break-all;
        }

        .op-item-extra {
            display: block;
            font-size: 11.5px;
            color: #a45a00;
        }

        .op-empty {
            padding: 10px;
            font-size: 13px;
            color: #6c757d;
            text-align: center;
        }

        .op-group-action {
            padding: 4px 0 6px;
        }

        #btnKembalikan {
            background: #d97706;
            border-color: #d97706;
            color: #fff;
        }

        #btnKembalikan:hover {
            background: #b45309;
            border-color: #b45309;
        }

        .op-missing .op-group-icon { background: #f8d7da; color: #b02a37; }
        .op-missing .op-group-count { background: #dc3545; }
        .op-notreturned .op-group-icon { background: #fff3e0; color: #a45a00; }
        .op-notreturned .op-group-count { background: #d97706; }
        .op-repair .op-group-icon { background: #e7f1ff; color: #0a58ca; }
        .op-repair .op-group-count { background: #0d6efd; }
        .op-found .op-group-icon { background: #e9f7ef; color: #146c43; }
        .op-found .op-group-count { background: #198754; }

        #btnSaveOpname {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 52px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
        }

        /* ---------- Riwayat opname ---------- */
        .op-history {
            margin-top: 18px;
            padding: 18px 20px;
        }

        .op-history-title {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .op-table {
            width: 100% !important;
            font-size: 13px;
        }

        .op-table thead th {
            background: #f8f9fa;
            color: #6c757d;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .op-table td {
            vertical-align: middle;
        }

        .op-num-bad {
            color: #dc3545;
            font-weight: 700;
        }

        .op-badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .op-badge.hasil-TIDAK_ADA { background: #f8d7da; color: #b02a37; }
        .op-badge.hasil-BELUM_KEMBALI { background: #fff3e0; color: #a45a00; }
        .op-badge.hasil-REPAIR_ADA { background: #e7f1ff; color: #0a58ca; }
        .op-badge.hasil-ADA { background: #e9f7ef; color: #146c43; }
        .op-badge.hasil-DI_LAPANGAN,
        .op-badge.hasil-REPAIR { background: #f1f3f5; color: #6c757d; }

        .op-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 12px;
        }

        @media (max-width: 575.98px) {
            .tt-stats.is-3 {
                gap: 6px;
            }

            .tt-stats.is-3 .tt-stat {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
                padding: 8px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="tt-wrap is-medium">
        <div class="tt-card">
            <div class="tt-hero">
                <div class="tt-hero-top">
                    <div class="tt-hero-icon"><i class="fa-solid fa-clipboard-check"></i></div>
                    <div>
                        <h5 class="tt-hero-title">Opname Tab</h5>
                        <p class="tt-hero-sub">Sapu semua tag di ruangan IT, lalu cocokkan dengan sistem</p>
                    </div>
                    <div class="tt-hero-actions">
                        <button type="button" class="tt-icon-btn" id="btnSound" onclick="toggleSound()" title="Bunyi scan">
                            <i class="fa-solid fa-volume-high"></i>
                        </button>
                        <a href="{{ route('asset_monitoring_tab') }}" class="tt-icon-btn" title="Monitoring Tab">
                            <i class="fa-solid fa-display"></i>
                        </a>
                    </div>
                </div>

                <div class="tt-stats is-3">
                    <div class="tt-stat">
                        <div class="tt-stat-icon"><i class="fa-solid fa-warehouse"></i></div>
                        <div>
                            <div class="tt-stat-value" id="statExpected">-</div>
                            <div class="tt-stat-label">Harus ada di ruangan</div>
                        </div>
                    </div>
                    <div class="tt-stat">
                        <div class="tt-stat-icon"><i class="fa-solid fa-circle-check"></i></div>
                        <div>
                            <div class="tt-stat-value" id="statFound">0</div>
                            <div class="tt-stat-label">Sudah terbaca</div>
                        </div>
                    </div>
                    <div class="tt-stat is-danger">
                        <div class="tt-stat-icon"><i class="fa-solid fa-circle-question"></i></div>
                        <div>
                            <div class="tt-stat-value" id="statMissing">-</div>
                            <div class="tt-stat-label">Belum terbaca</div>
                        </div>
                    </div>
                </div>

                <div class="op-progress"><div class="op-progress-bar" id="opProgressBar"></div></div>
                <div class="op-progress-text" id="opProgressText">Memuat data master tab...</div>
            </div>

            <div class="tt-body">
                <div class="tt-section">
                    <div class="tt-box-title">
                        <div class="tt-section-label mb-0">
                            <i class="fa-solid fa-tower-broadcast"></i> Sapu tag di ruangan
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <button type="button" class="tt-clear d-none" id="btnResetScan" onclick="resetScan()">
                                <i class="fa-solid fa-rotate-left"></i> Ulang
                            </button>
                            <span class="tt-count" id="scanCountLabel">0 tag</span>
                        </div>
                    </div>
                    <div class="tt-input tt-scan mt-2">
                        <i class="fa-solid fa-rss tt-input-icon"></i>
                        <input type="text" id="txtscan" class="form-control" placeholder="Tembak tag di sini"
                            autocomplete="off" onkeydown="handleScanEnter(event)" autofocus>
                        <span class="tt-scan-ready"><span class="tt-pulse"></span> Siap scan</span>
                    </div>
                    <div id="opSummary"></div>
                </div>

                <div class="op-group op-missing is-open" id="groupMissing">
                    <button type="button" class="op-group-head" onclick="toggleGroup('groupMissing')">
                        <span class="op-group-icon"><i class="fa-solid fa-circle-question"></i></span>
                        <span>Belum terbaca
                            <span class="op-group-sub">Seharusnya ada di ruangan (status IDLE)</span>
                        </span>
                        <span class="op-group-count">0</span>
                        <i class="fa-solid fa-chevron-down op-group-caret"></i>
                    </button>
                    <div class="op-group-body"><div class="op-list"></div></div>
                </div>

                <div class="op-group op-notreturned is-open d-none" id="groupNotReturned">
                    <button type="button" class="op-group-head" onclick="toggleGroup('groupNotReturned')">
                        <span class="op-group-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
                        <span>Ada di ruangan, tapi tercatat dibawa
                            <span class="op-group-sub">Kemungkinan lupa di-scan Kembalikan</span>
                        </span>
                        <span class="op-group-count">0</span>
                        <i class="fa-solid fa-chevron-down op-group-caret"></i>
                    </button>
                    <div class="op-group-body">
                        <div class="op-group-action">
                            <button type="button" class="btn btn-sm fw-bold w-100" id="btnKembalikan"
                                onclick="kembalikanTabs()">
                                <i class="fa-solid fa-arrow-rotate-left"></i> Catat sudah kembali
                            </button>
                        </div>
                        <div class="op-list"></div>
                    </div>
                </div>

                <div class="op-group op-repair d-none" id="groupRepair">
                    <button type="button" class="op-group-head" onclick="toggleGroup('groupRepair')">
                        <span class="op-group-icon"><i class="fa-solid fa-screwdriver-wrench"></i></span>
                        <span>Status repair, ada di ruangan
                            <span class="op-group-sub">Tandai selesai repair di Master Tab kalau sudah bisa dipakai</span>
                        </span>
                        <span class="op-group-count">0</span>
                        <i class="fa-solid fa-chevron-down op-group-caret"></i>
                    </button>
                    <div class="op-group-body"><div class="op-list"></div></div>
                </div>

                <div class="op-group op-found" id="groupFound">
                    <button type="button" class="op-group-head" onclick="toggleGroup('groupFound')">
                        <span class="op-group-icon"><i class="fa-solid fa-circle-check"></i></span>
                        <span>Ada di ruangan
                            <span class="op-group-sub">Terbaca dan sesuai sistem</span>
                        </span>
                        <span class="op-group-count">0</span>
                        <i class="fa-solid fa-chevron-down op-group-caret"></i>
                    </button>
                    <div class="op-group-body"><div class="op-list"></div></div>
                </div>

                <div class="tt-footer">
                    <button type="button" class="btn btn-secondary w-100" id="btnSaveOpname" onclick="saveOpname()" disabled>
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Hasil Opname
                    </button>
                </div>
            </div>
        </div>

        <div class="tt-card op-history">
            <div class="op-history-title"><i class="fa-solid fa-clock-rotate-left text-secondary"></i> Riwayat Opname</div>
            <div class="table-responsive">
                <table id="tableOpname" class="table table-hover op-table">
                    <thead>
                        <tr>
                            <th>No Opname</th>
                            <th>Tanggal</th>
                            <th class="text-center">Harus ada</th>
                            <th class="text-center">Ada</th>
                            <th class="text-center">Tidak terbaca</th>
                            <th class="text-center">Tercatat dibawa</th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal detail opname -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="detailModalLabel">Detail Opname</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="op-chips" id="detailChips"></div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted" id="detailInfo"></small>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="detailShowAll" onchange="renderDetailRows()">
                            <label class="form-check-label small" for="detailShowAll">Tampilkan semua tab</label>
                        </div>
                    </div>
                    <table class="table table-sm op-table mb-0">
                        <thead>
                            <tr>
                                <th>Hasil</th>
                                <th>Tab</th>
                                <th>RFID</th>
                                <th>Status sistem</th>
                            </tr>
                        </thead>
                        <tbody id="detailRows"></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-outline-success btn-sm" id="detailExport">
                        <i class="fa-solid fa-file-excel"></i> Export Excel
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    @include('asset_management.partials.tab_sound')

    <script>
        $.fn.dataTable.ext.errMode = function (settings, techNote, message) {
            console.error('DataTable ajax error:', message);
        };

        const HASIL_LABELS = @json($hasilLabels);
        const PROBLEM_HASIL = ['TIDAK_ADA', 'BELUM_KEMBALI', 'REPAIR_ADA'];
        const RENDER_DELAY_MS = 120; // saat scan beruntun, tampilan diperbarui berkala, bukan per tag

        let state = {
            tabs: null, // { KODE: {rfid_code, line_code, tab_code, status, lokasi, holder} } dari server
            loadedAt: '',
            codes: new Set(), // semua kode unik yang terbaca (terdaftar maupun tidak)
            dupCount: 0,
            pendingCodes: [], // terbaca sebelum data master selesai dimuat
            lastFoundCount: 0,
            lastNotReturnedCount: 0,
        };
        let renderTimer = null;
        let detailData = null;

        const esc = (s) => $('<div>').text(s == null ? '' : String(s)).html();

        const toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
        });

        function loadMaster() {
            return $.get('{{ route('master_opname_tab') }}').done(function (res) {
                state.tabs = {};
                res.tabs.forEach((tab) => { state.tabs[tab.rfid_code] = tab; });
                state.loadedAt = res.loaded_at;

                // Kode yang terbaca sebelum data master siap diproses sekarang
                state.pendingCodes.splice(0).forEach(addCode);
                renderNow(true);
            }).fail(function () {
                $('#opProgressText').text('Gagal memuat data master tab. Muat ulang halaman.');
                tabSound.error();
            });
        }

        // Scanner RFID mengirim kode lalu Enter otomatis (keyboard wedge)
        function handleScanEnter(e) {
            if (e.key !== 'Enter') return;

            e.preventDefault();

            let code = $('#txtscan').val().trim().toUpperCase();
            $('#txtscan').val('');

            if (!code) return;
            if (!state.tabs) {
                state.pendingCodes.push(code);
                return;
            }
            addCode(code);
        }

        function addCode(code) {
            if (state.codes.has(code)) state.dupCount++;
            else state.codes.add(code);

            scheduleRender();
        }

        function scheduleRender() {
            if (!renderTimer) renderTimer = setTimeout(renderNow, RENDER_DELAY_MS);
        }

        // Kelompokkan seluruh tab berdasarkan status di sistem & terbaca atau tidak
        function groupTabs() {
            let groups = { missing: [], notReturned: [], repair: [], found: [], unregistered: 0, expected: 0 };

            Object.values(state.tabs || {}).forEach((tab) => {
                let read = state.codes.has(tab.rfid_code);

                if (tab.status === 'TAKEN') {
                    if (read) groups.notReturned.push(tab);
                } else if (tab.status === 'REPAIR') {
                    if (read) groups.repair.push(tab);
                } else {
                    groups.expected++;
                    (read ? groups.found : groups.missing).push(tab);
                }
            });

            state.codes.forEach((code) => {
                if (state.tabs && !state.tabs[code]) groups.unregistered++;
            });

            return groups;
        }

        // silent = tanpa bunyi, dipakai saat data master dimuat ulang (bukan karena scan baru)
        function renderNow(silent) {
            clearTimeout(renderTimer);
            renderTimer = null;

            if (!state.tabs) return;

            let g = groupTabs();

            // Bunyi: tab bermasalah (tercatat dibawa) lebih penting daripada tab yang sesuai
            if (silent !== true) {
                if (g.notReturned.length > state.lastNotReturnedCount) tabSound.error();
                else if (g.found.length + g.repair.length > state.lastFoundCount) tabSound.ok();
            }
            state.lastNotReturnedCount = g.notReturned.length;
            state.lastFoundCount = g.found.length + g.repair.length;

            let percent = g.expected ? Math.round(g.found.length / g.expected * 100) : 0;
            $('#statExpected').text(g.expected);
            $('#statFound').text(g.found.length);
            $('#statMissing').text(g.missing.length);
            $('#opProgressBar').css('width', percent + '%');
            $('#opProgressText').text(g.expected ?
                `${percent}% tab di ruangan sudah terbaca · data master jam ${state.loadedAt}` :
                `Tidak ada tab berstatus IDLE · data master jam ${state.loadedAt}`);

            renderGroup('groupMissing', g.missing, (tab) => '',
                g.expected ? 'Semua tab di ruangan sudah terbaca' : 'Tidak ada tab yang harus ada di ruangan');
            renderGroup('groupNotReturned', g.notReturned, (tab) => tab.holder ? holderText(tab.holder) : '');
            renderGroup('groupRepair', g.repair, (tab) => '');
            renderGroup('groupFound', g.found, (tab) => '', 'Belum ada tab yang terbaca');

            $('#groupNotReturned, #groupRepair').each(function () {
                $(this).toggleClass('d-none', $(this).find('.op-item').length === 0);
            });
            $('#btnKembalikan').html(`<i class="fa-solid fa-arrow-rotate-left"></i> Catat sudah kembali (${g.notReturned.length} tab)`);

            let parts = [`${g.found.length + g.notReturned.length + g.repair.length} tab terbaca`];
            if (state.dupCount) parts.push(`${state.dupCount} scan dobel diabaikan`);
            if (g.unregistered) parts.push(`${g.unregistered} tag tidak terdaftar diabaikan`);
            $('#opSummary').text(state.codes.size ? parts.join(' · ') : '');

            $('#scanCountLabel').text(state.codes.size + ' tag');
            $('#btnResetScan').toggleClass('d-none', !state.codes.size);

            let $save = $('#btnSaveOpname');
            $save.prop('disabled', !state.codes.size).toggleClass('btn-success', !!state.codes.size)
                .toggleClass('btn-secondary', !state.codes.size);
        }

        function renderGroup(id, tabs, extraFn, emptyText) {
            let $group = $('#' + id);
            $group.find('.op-group-count').text(tabs.length);

            let html = tabs.map((tab) => {
                let extra = extraFn(tab);
                return `
                    <div class="op-item">
                        <div class="op-item-main">
                            <b>${esc(tab.tab_code || '-')}</b>${tab.line_code ? ' <span class="text-muted">· ' + esc(tab.line_code) + '</span>' : ''}
                            <span class="op-item-code">${esc(tab.rfid_code)}</span>
                            ${extra ? `<span class="op-item-extra">${esc(extra)}</span>` : ''}
                        </div>
                    </div>`;
            }).join('');

            $group.find('.op-list').html(html || `<div class="op-empty">${esc(emptyText || '')}</div>`);
        }

        function holderText(h) {
            let who = h.name || (h.enroll_id ? 'ID ' + h.enroll_id : 'tanpa NIK (bulk)');
            let ago = h.menit == null ? '' : h.menit < 60 ? h.menit + ' mnt lalu' :
                h.menit < 1440 ? Math.floor(h.menit / 60) + ' jam lalu' : Math.floor(h.menit / 1440) + ' hari lalu';
            return 'Dibawa ' + who + (h.tujuan ? ' · ' + h.tujuan : '') + (ago ? ' · ' + ago : '');
        }

        function toggleGroup(id) {
            $('#' + id).toggleClass('is-open');
        }

        function resetScan() {
            if (!state.codes.size) return;

            Swal.fire({
                icon: 'warning',
                title: 'Ulang opname?',
                text: state.codes.size + ' tag yang sudah terbaca akan dihapus dari daftar.',
                showCancelButton: true,
                confirmButtonText: 'Ya, ulang',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
                returnFocus: false,
            }).then((result) => {
                if (result.isConfirmed) clearScan();
                $('#txtscan').focus();
            });
        }

        function clearScan() {
            state.codes.clear();
            state.dupCount = 0;
            state.lastFoundCount = 0;
            state.lastNotReturnedCount = 0;
            renderNow();
        }

        // Tab yang fisiknya ada tapi masih tercatat dibawa: dicatat KEMBALI (tipe OPNAME) supaya status sesuai
        function kembalikanTabs() {
            let codes = groupTabs().notReturned.map((tab) => tab.rfid_code);
            if (!codes.length) return;

            Swal.fire({
                icon: 'question',
                title: 'Catat sudah kembali?',
                text: `${codes.length} tab akan dicatat KEMBALI ke ruangan IT (tercatat sebagai koreksi opname di riwayat).`,
                showCancelButton: true,
                confirmButtonText: 'Ya, catat kembali',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d97706',
                returnFocus: false,
            }).then((result) => {
                if (!result.isConfirmed) return $('#txtscan').focus();

                $('#btnKembalikan').prop('disabled', true);
                $.ajax({
                    url: '{{ route('kembalikan_opname_tab') }}',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', codes: codes },
                }).done(function (res) {
                    tabSound.done();
                    toast.fire({ icon: 'success', title: res.message });
                    state.lastNotReturnedCount = 0;
                    loadMaster();
                }).fail(function (xhr) {
                    tabSound.error();
                    toast.fire({ icon: 'error', title: (xhr.responseJSON || {}).message || 'Gagal menyimpan.' });
                }).always(function () {
                    $('#btnKembalikan').prop('disabled', false);
                    $('#txtscan').focus();
                });
            });
        }

        function saveOpname() {
            let g = groupTabs();

            Swal.fire({
                icon: g.missing.length ? 'warning' : 'question',
                title: 'Simpan hasil opname?',
                html: `<div class="text-start small">
                        <div><b>${g.found.length}</b> dari <b>${g.expected}</b> tab di ruangan terbaca</div>
                        <div class="${g.missing.length ? 'text-danger fw-bold' : ''}"><b>${g.missing.length}</b> tab belum terbaca</div>
                        ${g.notReturned.length ? `<div class="text-warning fw-bold"><b>${g.notReturned.length}</b> tab ada di ruangan tapi tercatat dibawa</div>` : ''}
                    </div>`,
                input: 'text',
                inputPlaceholder: 'Keterangan (opsional)',
                inputAttributes: { maxlength: 255 },
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#198754',
                returnFocus: false,
            }).then((result) => {
                if (!result.isConfirmed) return $('#txtscan').focus();

                let $btn = $('#btnSaveOpname');
                $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...');

                $.ajax({
                    url: '{{ route('store_opname_tab') }}',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', codes: Array.from(state.codes), keterangan: result.value },
                }).done(function (res) {
                    tabSound.done();
                    clearScan();
                    tableOpname.ajax.reload();
                    loadMaster();

                    Swal.fire({
                        icon: 'success',
                        title: 'Opname tersimpan',
                        text: res.no_opname,
                        showCancelButton: true,
                        confirmButtonText: 'Lihat detail',
                        cancelButtonText: 'Tutup',
                    }).then((r) => { if (r.isConfirmed) showDetail(res.id); });
                }).fail(function (xhr) {
                    tabSound.error();
                    toast.fire({ icon: 'error', title: (xhr.responseJSON || {}).message || 'Gagal menyimpan opname.' });
                }).always(function () {
                    $btn.html('<i class="fa-solid fa-floppy-disk"></i> Simpan Hasil Opname');
                    renderNow();
                });
            });
        }

        // Cegah hasil scan hilang karena halaman tertutup sebelum disimpan
        window.addEventListener('beforeunload', function (e) {
            if (state.codes.size) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // ---------- Riwayat opname ----------
        let tableOpname = $('#tableOpname').DataTable({
            ordering: false,
            processing: true,
            pageLength: 10,
            dom: 'rtip',
            language: {
                emptyTable: 'Belum ada opname',
                info: '_START_–_END_ dari _TOTAL_',
                infoEmpty: '',
                processing: '<i class="fa-solid fa-spinner fa-spin"></i> Memuat...',
                paginate: { previous: '‹', next: '›' },
            },
            ajax: { url: '{{ route('list_opname_tab') }}' },
            columns: [
                {
                    data: 'no_opname',
                    render: (d, t, row) => `<b class="text-nowrap">${esc(d)}</b><div class="small text-muted">oleh ${esc(row.created_by)}` +
                        (row.keterangan ? ` · ${esc(row.keterangan)}` : '') + '</div>',
                },
                { data: 'tgl_opname', className: 'text-nowrap', render: (d) => esc(String(d).substring(0, 16)) },
                { data: 'total_idle', className: 'text-center' },
                { data: 'total_ada', className: 'text-center' },
                { data: 'total_tidak_ada', className: 'text-center', render: (d) => d > 0 ? `<span class="op-num-bad">${d}</span>` : d },
                { data: 'total_belum_kembali', className: 'text-center', render: (d) => d > 0 ? `<span class="op-num-bad">${d}</span>` : d },
                {
                    data: 'id',
                    className: 'text-end text-nowrap',
                    render: (d) => `
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="showDetail(${d})" title="Detail">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        <a href="{{ route('export_opname_tab') }}?id=${d}" class="btn btn-sm btn-outline-success" title="Export Excel">
                            <i class="fa-solid fa-file-excel"></i>
                        </a>`,
                },
            ],
        });

        function showDetail(id) {
            $.get('{{ route('detail_opname_tab') }}', { id: id }).done(function (res) {
                detailData = res;
                let h = res.header;

                $('#detailModalLabel').text('Detail Opname ' + h.no_opname);
                $('#detailInfo').text(`${String(h.tgl_opname).substring(0, 16)} · oleh ${h.created_by}` +
                    (h.keterangan ? ' · ' + h.keterangan : ''));
                $('#detailExport').attr('href', '{{ route('export_opname_tab') }}?id=' + h.id);

                let counts = {};
                res.details.forEach((row) => { counts[row.hasil] = (counts[row.hasil] || 0) + 1; });
                $('#detailChips').html(Object.keys(HASIL_LABELS).filter((k) => counts[k]).map((k) =>
                    `<span class="op-badge hasil-${k}">${esc(HASIL_LABELS[k])}: ${counts[k]}</span>`).join('') +
                    (h.total_tidak_terdaftar ? `<span class="op-badge hasil-REPAIR">Tag tidak terdaftar: ${h.total_tidak_terdaftar}</span>` : ''));

                $('#detailShowAll').prop('checked', false);
                renderDetailRows();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('detailModal')).show();
            }).fail(function () {
                toast.fire({ icon: 'error', title: 'Gagal memuat detail opname.' });
            });
        }

        function renderDetailRows() {
            if (!detailData) return;

            let showAll = $('#detailShowAll').is(':checked');
            let rows = detailData.details.filter((row) => showAll || PROBLEM_HASIL.includes(row.hasil));

            $('#detailRows').html(rows.length ? rows.map((row) => `
                <tr>
                    <td><span class="op-badge hasil-${esc(row.hasil)}">${esc(HASIL_LABELS[row.hasil] || row.hasil)}</span></td>
                    <td><b>${esc(row.tab_code || '-')}</b>${row.line_code ? ' <span class="text-muted">· ' + esc(row.line_code) + '</span>' : ''}</td>
                    <td class="font-monospace small">${esc(row.rfid_code)}</td>
                    <td>${esc(row.status_sistem)}</td>
                </tr>`).join('') :
                '<tr><td colspan="4" class="text-center text-muted py-3">Tidak ada tab bermasalah</td></tr>');
        }

        loadMaster();
    </script>
@endsection
