@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    @include('asset_management.partials.tab_style')
@endsection

@section('content')
    <div class="tt-wrap">
        <div class="tt-card">
            <div class="tt-hero">
                <div class="tt-hero-top">
                    <div class="tt-hero-icon"><i class="fa-solid fa-tablet-screen-button"></i></div>
                    <div>
                        <h5 class="tt-hero-title">Transaksi Tab</h5>
                        <p class="tt-hero-sub">Ambil &amp; kembalikan tablet dengan scan RFID</p>
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

                <div class="tt-stats" id="locationStats">
                    <div class="tt-stat">
                        <div class="tt-stat-icon"><i class="fa-solid fa-warehouse"></i></div>
                        <div>
                            <div class="tt-stat-value" id="statIdle">{{ $idleCount }}</div>
                            <div class="tt-stat-label">Di ruangan</div>
                        </div>
                    </div>
                    <div class="tt-stat">
                        <div class="tt-stat-icon"><i class="fa-solid fa-person-walking-luggage"></i></div>
                        <div>
                            <div class="tt-stat-value" id="statTaken">{{ $takenCount }}</div>
                            <div class="tt-stat-label">Di lapangan</div>
                        </div>
                    </div>
                </div>

                @if ($overdueCount > 0)
                    <a href="{{ route('asset_monitoring_tab') }}?overdue=1" class="tt-alert">
                        <span class="tt-alert-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
                        <span><b>{{ $overdueCount }} tab</b> belum kembali lebih dari {{ $overdueHours }} jam</span>
                        <i class="fa-solid fa-chevron-right ms-auto"></i>
                    </a>
                @endif
            </div>

            <div class="tt-body">
                <div class="tt-section">
                    <div class="tt-section-label"><i class="fa-solid fa-sliders"></i> Mode</div>
                    <div class="tt-seg" role="group" aria-label="Mode">
                        <input type="radio" class="btn-check" name="ttMode" id="btnModeSingle" autocomplete="off" checked>
                        <label class="tt-seg-btn" for="btnModeSingle" onclick="setMode('single')">
                            <i class="fa-solid fa-user"></i> Single
                        </label>
                        <input type="radio" class="btn-check" name="ttMode" id="btnModeBulk" autocomplete="off">
                        <label class="tt-seg-btn" for="btnModeBulk" onclick="setMode('bulk')">
                            <i class="fa-solid fa-layer-group"></i> Bulk
                        </label>
                    </div>
                </div>

                <div class="tt-section">
                    <div class="tt-section-label"><i class="fa-solid fa-right-left"></i> Aksi</div>
                    <div class="tt-seg" role="group" aria-label="Aksi">
                        <input type="radio" class="btn-check" name="ttAction" id="btnActionAmbil" autocomplete="off" checked>
                        <label class="tt-seg-btn is-ambil" for="btnActionAmbil" onclick="setAction('ambil')">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Ambil
                        </label>
                        <input type="radio" class="btn-check" name="ttAction" id="btnActionKembalikan" autocomplete="off">
                        <label class="tt-seg-btn is-kembali" for="btnActionKembalikan" onclick="setAction('kembalikan')">
                            <i class="fa-solid fa-arrow-rotate-left"></i> Kembalikan
                        </label>
                    </div>
                </div>

                <div class="tt-banner" id="ttBanner" role="status">
                    <i class="fa-solid fa-circle-info" id="ttBannerIcon"></i>
                    <span id="ttBannerText"></span>
                </div>

                <div class="tt-section" id="nikBox">
                    <label class="tt-section-label" for="txtnik"><i class="fa-solid fa-id-badge"></i> Enroll ID Karyawan</label>
                    <div class="tt-input">
                        <i class="fa-solid fa-magnifying-glass tt-input-icon"></i>
                        <input type="text" id="txtnik" class="form-control" placeholder="Ketik Enroll ID" autocomplete="off">
                        <div id="nikSuggestList"></div>
                    </div>
                    <span id="nikEmployeeName"></span>
                </div>

                <div class="tt-section">
                    {{-- div, bukan label: tombol di dalam label ikut terpicu saat judulnya diklik --}}
                    <div class="tt-box-title">
                        <div class="tt-section-label mb-0">
                            <i class="fa-solid fa-tower-broadcast"></i> <span id="scanBoxTitle">Scan tag yang dibawa</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <button type="button" class="tt-clear d-none" id="btnClearTags" onclick="clearAllTags()">
                                <i class="fa-solid fa-trash-can"></i> Clear All
                            </button>
                            <span class="tt-count" id="tagCountLabel">0 tag</span>
                        </div>
                    </div>
                    <div class="tt-input tt-scan mt-2">
                        <i class="fa-solid fa-rss tt-input-icon"></i>
                        <input type="text" id="txtscan" class="form-control" placeholder="Tembak tag di sini"
                            autocomplete="off" onkeydown="handleScanEnter(event)">
                        <span class="tt-scan-ready"><span class="tt-pulse"></span> Siap scan</span>
                    </div>
                    <div id="scannedTagList"></div>
                    <div id="tagSummary"></div>
                </div>

                <div class="tt-section" id="tujuanBox">
                    <label class="tt-section-label" for="cbotujuan"><i class="fa-solid fa-location-dot"></i> Tujuan</label>
                    <select id="cbotujuan" class="form-control form-control-sm select2bs4" style="width: 100%;"
                        onchange="refreshSubmitState()">
                        <option value="">-- Pilih Tujuan --</option>
                        @foreach ($mainLokasiList as $row)
                            <option value="{{ $row->id }}">{{ $row->main_lokasi }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="tt-footer">
                    <button type="button" class="btn btn-secondary w-100" id="btnSubmit" onclick="submitTransTab()" disabled></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    @include('asset_management.partials.tab_sound')
    <script>
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: 'resolve'
        });
        $('.select2-container--bootstrap4 .select2-selection--single').css({
            'height': '42px',
            'font-size': '14px',
            'line-height': '40px'
        });

        let state = {
            mode: 'single', // single | bulk
            action: 'ambil', // ambil | kembalikan
            tags: [], // tag yang sudah discan: { code, status: pending|ok|error, message, tabInfo, holder, checked }
            dupCount: 0, // jumlah pembacaan tag yang sama (scanner RFID sering membaca 1 tag berkali-kali)
            ignored: new Set(), // kode yang tidak ada di Master Tab: tidak ditampilkan & tidak dicek lagi
            checkVersion: 0, // naik setiap aksi berubah, supaya hasil cek untuk aksi lama diabaikan
            nikEmployeeName: null
        };
        let nikSuggestTimer = null;
        let checkTimer = null;
        let checking = false;
        let checkFailCount = 0;

        const CHECK_DELAY_MS = 400; // cek dikirim setelah scan berhenti sejenak
        const CHECK_MAX_BATCH = 50; // atau langsung dikirim kalau sudah terkumpul sebanyak ini

        const esc = (s) => $('<div>').text(s == null ? '' : String(s)).html();

        const toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2200,
            timerProgressBar: true,
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        // NIK diketik manual (bukan select2) supaya cepat dipakai scanner/keyboard,
        // suggestion diambil dari HRIS via AJAX dengan debounce
        $('#txtnik').on('input', function () {
            state.nikEmployeeName = null;
            $('#nikEmployeeName').text('');

            let term = $(this).val().trim();
            clearTimeout(nikSuggestTimer);

            if (!term) {
                $('#nikSuggestList').hide().empty();
                renderScannedTagList();
                return;
            }

            nikSuggestTimer = setTimeout(function () {
                $.get('{{ route('nik_suggest_trans_tab') }}', { q: term }, function (data) {
                    renderNikSuggestList(data);
                });
            }, 300);

            // Dirender ulang karena tanda "bukan NIK ini" di daftar tag tergantung NIK yang diisi
            renderScannedTagList();
        });

        function renderNikSuggestList(data) {
            let $list = $('#nikSuggestList').empty();

            if (!data || !data.length) {
                $list.hide();
                return;
            }

            data.forEach(function (row) {
                $list.append(`
                    <div class="nik-suggest-item" data-enroll-id="${row.enroll_id}" data-name="${row.employee_name}">
                        <b>${row.enroll_id}</b>${row.nik} - ${row.employee_name}
                    </div>
                `);
            });

            $list.show();
        }

        $(document).on('click', '.nik-suggest-item', function () {
            let enrollId = $(this).data('enroll-id');
            let name = $(this).data('name');

            $('#txtnik').val(enrollId);
            state.nikEmployeeName = name;
            $('#nikEmployeeName').text(name);
            $('#nikSuggestList').hide().empty();

            renderScannedTagList();
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('#nikBox').length) {
                $('#nikSuggestList').hide();
            }
        });

        function setMode(mode) {
            state.mode = mode;

            $('#nikBox').toggleClass('d-none', mode === 'bulk');
            $('#scanBoxTitle').text(mode === 'single' ? 'Scan tag yang dibawa' : 'Sapu semua tag');

            updateBanner();
            renderScannedTagList();
        }

        function setAction(action) {
            state.action = action;

            $('#tujuanBox').toggleClass('d-none', action === 'kembalikan');
            if (action === 'kembalikan') {
                $('#cbotujuan').val('').trigger('change');
            }

            // Boleh-tidaknya tag diproses tergantung aksi, jadi semua tag yang sudah discan dicek ulang
            state.checkVersion++;
            state.tags.forEach((tag) => { tag.status = 'pending'; tag.message = ''; });
            renderScannedTagList();
            scheduleCheck();

            updateBanner();
            refreshSubmitState();
        }

        function updateBanner() {
            let text = '';

            if (state.mode === 'single' && state.action === 'ambil') {
                text = 'Single · Ambil — 1 NIK bisa bawa beberapa tag sekaligus';
            } else if (state.mode === 'single' && state.action === 'kembalikan') {
                text = 'Single · Kembalikan — 1 NIK mengembalikan tag yang pernah dibawa';
            } else if (state.mode === 'bulk' && state.action === 'ambil') {
                text = 'Bulk · Ambil — checkout cepat tanpa NIK, tercatat sebagai "keluar" saja';
            } else {
                text = 'Bulk · Kembalikan — checkin cepat tanpa NIK, seluruh tag disapu masuk';
            }

            let ambil = state.action === 'ambil';
            $('#ttBannerText').text(text);
            $('#ttBannerIcon').attr('class', 'fa-solid ' + (ambil ? 'fa-arrow-right-from-bracket' : 'fa-arrow-rotate-left'));
            $('#ttBanner').toggleClass('is-kembali', !ambil);
        }

        // Scanner RFID mengirim kode lalu Enter otomatis (keyboard wedge). Saat continuous scan kode datang
        // sangat cepat, jadi kotak scan TIDAK boleh dinonaktifkan/menunggu server: setiap Enter langsung
        // ditampung ke daftar, lalu dicek ke server secara berkelompok (lihat flushCheck).
        function handleScanEnter(e) {
            if (e.key !== 'Enter') return;

            e.preventDefault();

            let code = $('#txtscan').val().trim().toUpperCase();
            $('#txtscan').val('');

            if (code) addScannedTag(code);
        }

        function addScannedTag(code) {
            // Sudah diketahui tidak terdaftar: abaikan tanpa cek ulang ke server
            if (state.ignored.has(code)) return;

            if (state.tags.some((tag) => tag.code === code)) {
                state.dupCount++;
                renderTagSummary();
                return;
            }

            state.tags.push({ code: code, status: 'pending', message: '' });
            renderScannedTagList();
            // Tag terbaru ada di paling bawah, jadi daftar ikut digulir supaya tetap terlihat saat sweeping
            $('#scannedTagList').scrollTop(1e9);
            scheduleCheck();
        }

        function removeScannedTag(index) {
            state.tags.splice(index, 1);
            renderScannedTagList();
            $('#txtscan').focus();
        }

        function removeFailedTags() {
            state.tags = state.tags.filter((tag) => tag.status !== 'error');
            renderScannedTagList();
            $('#txtscan').focus();
        }

        function clearAllTags() {
            if (!state.tags.length) return;

            Swal.fire({
                icon: 'warning',
                title: 'Hapus semua tag?',
                text: state.tags.length + ' tag yang sudah discan akan dihapus dari daftar.',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus semua',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
                returnFocus: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    // Hasil cek yang masih berjalan diabaikan, supaya tidak mengubah tag yang discan sesudah ini
                    state.checkVersion++;
                    clearTimeout(checkTimer);
                    state.tags = [];
                    state.dupCount = 0;
                    state.ignored.clear();
                    renderScannedTagList();
                }
                $('#txtscan').focus();
            });
        }

        function scheduleCheck() {
            clearTimeout(checkTimer);
            let pending = state.tags.filter((tag) => tag.status === 'pending').length;
            if (pending) checkTimer = setTimeout(flushCheck, pending >= CHECK_MAX_BATCH ? 0 : CHECK_DELAY_MS);
        }

        // Kirim semua tag yang belum dicek dalam 1 request. Tag yang discan selama request berjalan
        // akan dikirim di putaran berikutnya.
        function flushCheck() {
            if (checking) return;

            let codes = state.tags.filter((tag) => tag.status === 'pending').map((tag) => tag.code);
            if (!codes.length) return;

            checking = true;
            let version = state.checkVersion;

            $.ajax({
                url: '{{ route('check_rfid_batch_trans_tab') }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', action: state.action, codes: codes },
            }).done(function (res) {
                checkFailCount = 0;
                if (version !== state.checkVersion) return; // aksi sudah berganti, hasil ini tidak berlaku

                let results = {};
                res.results.forEach((r) => { results[r.code] = r; });
                let newOk = 0;
                let newError = 0;

                state.tags = state.tags.filter((tag) => {
                    let r = results[tag.code];
                    if (!r || tag.status !== 'pending') return true;

                    // Tidak ada di Master Tab: dibuang dari daftar, hanya dihitung
                    if (!r.registered) {
                        state.ignored.add(tag.code);
                        return false;
                    }

                    tag.status = r.ok ? 'ok' : 'error';
                    tag.message = r.ok ? '' : r.message;
                    tag.tabInfo = [r.tab_code, r.line_code].filter(Boolean).join(' · ');
                    tag.holder = r.holder || null;

                    // Bunyi hanya untuk tag yang baru pertama kali dicek, bukan cek ulang saat aksi diganti
                    if (!tag.checked) {
                        tag.checked = true;
                        r.ok ? newOk++ : newError++;
                    }
                    return true;
                });

                if (newError) tabSound.error();
                else if (newOk) tabSound.ok();
            }).fail(function () {
                // Gagal koneksi: coba lagi beberapa kali, setelah itu tandai gagal
                checkFailCount++;
                if (checkFailCount >= 3 && version === state.checkVersion) {
                    state.tags.forEach((tag) => {
                        if (tag.status === 'pending' && codes.includes(tag.code)) {
                            tag.status = 'error';
                            tag.message = 'Gagal dicek (koneksi), hapus lalu scan ulang';
                        }
                    });
                    checkFailCount = 0;
                    tabSound.error();
                }
            }).always(function () {
                checking = false;
                renderScannedTagList();
                if (state.tags.some((tag) => tag.status === 'pending')) {
                    clearTimeout(checkTimer);
                    checkTimer = setTimeout(flushCheck, checkFailCount ? 2000 : 0);
                }
            });
        }

        function tagCounts() {
            let counts = { ok: 0, pending: 0, error: 0 };
            state.tags.forEach((tag) => counts[tag.status]++);
            return counts;
        }

        function renderScannedTagList() {
            let icons = {
                pending: '<i class="fa-solid fa-spinner fa-spin text-secondary"></i>',
                ok: '<i class="fa-solid fa-circle-check text-success"></i>',
                error: '<i class="fa-solid fa-circle-xmark text-danger"></i>',
            };

            // Dirender sekaligus sebagai 1 string supaya tetap ringan walau tag sudah ratusan
            let html = state.tags.map((tag, i) => {
                let info = tagInfo(tag);
                return `
                <div class="scanned-tag-row tag-${tag.status}">
                    <span class="tag-no">${i + 1}</span>
                    <span class="tag-status">${icons[tag.status]}</span>
                    <span class="tag-code">${esc(tag.code)}
                        <span class="tag-info ${info.cls}">${esc(info.text)}</span>
                    </span>
                    <button type="button" class="tt-remove" onclick="removeScannedTag(${i})" title="Hapus tag">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>`;
            }).join('');

            $('#scannedTagList').html(html);
            $('#tagCountLabel').text(state.tags.length + ' tag');
            $('#btnClearTags').toggleClass('d-none', !state.tags.length);
            renderTagSummary();
            refreshSubmitState();
        }

        // Keterangan di bawah kode tag: alasan gagal, pemegang tab (saat Kembalikan), atau kode tab & line
        function tagInfo(tag) {
            if (tag.status === 'pending') return { text: 'mengecek...', cls: 'text-muted' };

            let holder = tag.holder ? holderText(tag.holder) : '';

            if (tag.status === 'error') {
                return { text: tag.message + (holder ? ' — ' + holder : ''), cls: 'text-danger' };
            }

            if (state.action === 'kembalikan' && tag.holder) {
                let other = isOtherHolder(tag);
                return {
                    text: [tag.tabInfo, holder].filter(Boolean).join(' · ') + (other ? ' (bukan NIK ini)' : ''),
                    cls: other ? 'is-warn' : 'text-muted',
                };
            }

            return { text: tag.tabInfo || '', cls: 'text-muted' };
        }

        function holderText(h) {
            let who = h.name || (h.enroll_id ? 'ID ' + h.enroll_id : 'tanpa NIK (bulk)');
            return 'Dibawa ' + who + (h.tujuan ? ' · ' + h.tujuan : '') + (h.menit != null ? ' · ' + agoText(h.menit) : '');
        }

        function agoText(minutes) {
            if (minutes < 60) return minutes + ' mnt lalu';
            if (minutes < 1440) return Math.floor(minutes / 60) + ' jam lalu';
            return Math.floor(minutes / 1440) + ' hari lalu';
        }

        // Mode Single · Kembalikan: tag tercatat dibawa karyawan lain (bukan NIK yang diisi)
        function isOtherHolder(tag) {
            let nik = $('#txtnik').val().trim();
            return state.mode === 'single' && nik !== '' && !!tag.holder && tag.holder.enroll_id != null &&
                String(tag.holder.enroll_id) !== nik;
        }

        function renderTagSummary() {
            if (!state.tags.length && !state.dupCount && !state.ignored.size) {
                $('#tagSummary').empty();
                return;
            }

            let c = tagCounts();
            let parts = [`<span class="text-success">${c.ok} valid</span>`];
            if (c.pending) parts.push(`<span class="text-secondary">${c.pending} dicek</span>`);
            if (c.error) {
                parts.push(`<span class="text-danger">${c.error} gagal</span> ` +
                    `<a href="javascript:void(0)" onclick="removeFailedTags()">(hapus yang gagal)</a>`);
            }
            if (state.dupCount) parts.push(`<span class="text-muted">${state.dupCount} scan dobel diabaikan</span>`);
            if (state.ignored.size) parts.push(`<span class="text-muted">${state.ignored.size} tag tidak terdaftar diabaikan</span>`);

            $('#tagSummary').html(parts.join(' · '));
        }

        function refreshSubmitState() {
            let nik = $('#txtnik').val();
            let tujuan = $('#cbotujuan').val();
            let c = tagCounts();
            let hasTag = c.ok >= 1;

            let requirements = [];
            if (state.mode === 'single' && !nik) requirements.push('Isi NIK');
            if (c.pending) requirements.push('tunggu pengecekan tag');
            else if (c.error) requirements.push('hapus tag yang gagal');
            else if (!hasTag) requirements.push('scan minimal 1 tag');
            if (state.action === 'ambil' && !tujuan) requirements.push('pilih tujuan');

            let $btn = $('#btnSubmit').removeClass('btn-secondary btn-success btn-warning');

            if (requirements.length) {
                let text = requirements.join(' & ').replace(/^./, c => c.toUpperCase());
                $btn.prop('disabled', true).addClass('btn-secondary')
                    .html('<i class="fa-solid fa-circle-info"></i> ' + esc(text));
            } else {
                let ambil = state.action === 'ambil';
                let label = (ambil ? 'Proses Ambil Barang' : 'Proses Pengembalian') + ` (${c.ok} tag)`;
                $btn.prop('disabled', false).addClass(ambil ? 'btn-success' : 'btn-warning')
                    .html(`<i class="fa-solid ${ambil ? 'fa-arrow-right-from-bracket' : 'fa-arrow-rotate-left'}"></i> ` + esc(label));
            }
        }

        function updateLocationStats(action, tagCount) {
            let $idle = $('#statIdle');
            let $taken = $('#statTaken');

            let idle = parseInt($idle.text(), 10) || 0;
            let taken = parseInt($taken.text(), 10) || 0;

            if (action === 'ambil') {
                idle = Math.max(0, idle - tagCount);
                taken += tagCount;
            } else {
                taken = Math.max(0, taken - tagCount);
                idle += tagCount;
            }

            $idle.text(idle);
            $taken.text(taken);
        }

        updateBanner();
        refreshSubmitState();

        function submitTransTab() {
            let okTags = state.tags.filter((tag) => tag.status === 'ok');
            let others = state.action === 'kembalikan' ? okTags.filter(isOtherHolder) : [];

            if (!others.length) {
                sendTransTab(okTags.map((tag) => tag.code));
                return;
            }

            // Tidak diblok (bisa saja dititipkan), tapi dikonfirmasi dulu
            let list = others.slice(0, 8).map((tag) =>
                `<li>${esc(tag.tabInfo || tag.code)} — ${esc(tag.holder.name || 'ID ' + tag.holder.enroll_id)}</li>`).join('');
            if (others.length > 8) list += `<li>dan ${others.length - 8} tag lainnya</li>`;

            Swal.fire({
                icon: 'warning',
                title: 'Dikembalikan oleh orang lain?',
                html: `${others.length} tag tercatat dibawa oleh karyawan lain, bukan NIK ${esc($('#txtnik').val().trim())}:` +
                    `<ul class="text-start mt-2 mb-0">${list}</ul>`,
                showCancelButton: true,
                confirmButtonText: 'Tetap proses',
                cancelButtonText: 'Batal',
                returnFocus: false,
            }).then((result) => {
                if (result.isConfirmed) sendTransTab(okTags.map((tag) => tag.code));
                else $('#txtscan').focus();
            });
        }

        function sendTransTab(codes) {
            let $btn = $('#btnSubmit');

            $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Memproses...');

            $.ajax({
                url: '{{ route('store_trans_tab') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    mode: state.mode,
                    action: state.action,
                    enroll_id: $('#txtnik').val().trim(),
                    tags: codes,
                    id_tujuan: $('#cbotujuan').val()
                },
                success: function (res) {
                    tabSound.done();
                    toast.fire({
                        icon: 'success',
                        title: state.action === 'ambil' ? 'Barang berhasil diambil' : 'Barang berhasil dikembalikan',
                        text: res.message,
                    });

                    updateLocationStats(state.action, res.count || codes.length);

                    state.tags = [];
                    state.dupCount = 0;
                    state.ignored.clear();
                    $('#txtnik').val('');
                    $('#nikEmployeeName').text('');
                    state.nikEmployeeName = null;
                    $('#cbotujuan').val('').trigger('change');
                    renderScannedTagList();
                },
                error: function (xhr) {
                    let res = xhr.responseJSON || {};

                    // Status tag berubah sejak discan (mis. diambil dari perangkat lain): tandai tag-nya di daftar
                    (res.invalid || []).forEach((item) => {
                        let tag = state.tags.find((t) => t.code === item.code);
                        if (tag) { tag.status = 'error'; tag.message = item.message; }
                    });
                    renderScannedTagList();

                    tabSound.error();
                    toast.fire({ icon: 'error', title: res.message || 'Gagal menyimpan transaksi.' });
                },
                complete: function () {
                    refreshSubmitState();
                    $('#txtscan').focus();
                }
            });
        }
    </script>
@endsection
