{{--
    Script bersama tabel stok trolley (Kirim & Alokasi) :
    - Warna dari nama color (colorFromName, colorDotHtml, paintColorDot)
    - Render sel gabungan (renderStockerList, renderWsc, renderSizeQty)
    - Filter modal (initStockFilter, applyStockFilter, resetStockFilter, refreshStockFilterOptions)

    Butuh : jQuery, DataTables, Select2, Bootstrap 5, partial 'stock-filter-modal'.
--}}
<script>
    // ===== Util =====
    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function escapeRegex(text) {
        return String(text).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    // ===== Warna dari nama color (nama warna garmen, dicocokkan lewat kata kunci) =====
    const COLOR_KEYWORDS = [
        // Frasa lebih panjang / spesifik di atas
        ['OFF WHITE', '#f3eee0'], ['BROKEN WHITE', '#f3eee0'], ['IVORY', '#f3eee0'], ['CREAM', '#f1e6c8'],
        ['NAVY', '#1f3a68'], ['MAROON', '#7b1e3a'], ['BURGUNDY', '#7b1e3a'], ['WINE', '#722f37'],
        ['CHARCOAL', '#36454f'], ['HEATHER', '#9aa0a6'], ['SILVER', '#adb5bd'],
        ['KHAKI', '#a39264'], ['ARMY', '#4b5320'], ['OLIVE', '#6b8e23'], ['MINT', '#3eb489'], ['TEAL', '#20c997'], ['TOSCA', '#20c997'],
        ['LAVENDER', '#b19cd9'], ['LILAC', '#b19cd9'], ['PURPLE', '#6f42c1'], ['UNGU', '#6f42c1'], ['VIOLET', '#6f42c1'],
        ['FUCHSIA', '#d63384'], ['MAGENTA', '#d63384'], ['PINK', '#e83e8c'], ['ROSE', '#e05780'], ['PEACH', '#ffb38a'], ['CORAL', '#ff7f50'],
        ['ORANGE', '#fd7e14'], ['ORANYE', '#fd7e14'], ['MUSTARD', '#d4a017'], ['GOLD', '#d4a017'], ['YELLOW', '#ffc107'], ['KUNING', '#ffc107'],
        ['BEIGE', '#d2b48c'], ['SAND', '#d2b48c'], ['TAN', '#d2b48c'], ['CAMEL', '#c19a6b'], ['BROWN', '#795548'], ['COKLAT', '#795548'], ['MOCCA', '#8b6f47'],
        ['SKY', '#6ec1e4'], ['TURQUOISE', '#30c5d2'], ['DENIM', '#3b5b92'], ['INDIGO', '#3f51b5'], ['BLUE', '#0d6efd'], ['BIRU', '#0d6efd'],
        ['GREEN', '#198754'], ['HIJAU', '#198754'], ['RED', '#dc3545'], ['MERAH', '#dc3545'],
        ['GREY', '#6c757d'], ['GRAY', '#6c757d'], ['ABU', '#6c757d'],
        ['BLACK', '#212529'], ['HITAM', '#212529'], ['WHITE', '#ffffff'], ['PUTIH', '#ffffff'],
    ];

    function hexToRgb(hex) {
        let int = parseInt(hex.replace('#', ''), 16);
        return [(int >> 16) & 255, (int >> 8) & 255, int & 255];
    }

    // amount > 0 : lebih terang, amount < 0 : lebih gelap
    function shadeRgb(rgb, amount) {
        return rgb.map((c) => Math.round(amount >= 0 ? c + (255 - c) * amount : c * (1 + amount)));
    }

    function colorFromName(name) {
        let text = ' ' + String(name || '').toUpperCase().replace(/[^A-Z]+/g, ' ') + ' ';
        let match = COLOR_KEYWORDS.find(([keyword]) => text.includes(' ' + keyword + ' '));

        if (!match) {
            return null;
        }

        let rgb = hexToRgb(match[1]);

        if (/ (DARK|DK|DEEP|TUA) /.test(text)) {
            rgb = shadeRgb(rgb, -0.35);
        } else if (/ (LIGHT|LT|PALE|SOFT|MUDA) /.test(text)) {
            rgb = shadeRgb(rgb, 0.4);
        }

        return rgb;
    }

    // Titik warna (HTML) untuk render datatable
    function colorDotHtml(name) {
        let rgb = colorFromName(name);

        return rgb
            ? `<span class="color-dot" style="background-color: rgb(${rgb.join(',')})"></span>`
            : `<span class="color-dot unknown"></span>`;
    }

    // Titik warna untuk elemen yang sudah ada di DOM
    function paintColorDot(dot, name) {
        let rgb = colorFromName(name);

        if (dot && rgb) {
            dot.style.backgroundColor = `rgb(${rgb.join(',')})`;
            dot.classList.remove('unknown');
        }
    }

    // ===== Render sel gabungan =====
    // No. Stocker : setiap stocker bisa diklik untuk lihat detail transaksi (partials/stocker-detail-modal)
    function renderStockerList(value) {
        let items = String(value || '').split(',').map((item) => item.trim()).filter((item) => item);

        return `<div class="stocker-list">` + items.map((item) => stockerLinkHtml(item)).join('') + `</div>`;
    }

    function stockerLinkHtml(idQrStocker) {
        return `<button type="button" class="stocker-chip stocker-detail-link" data-stocker="${escapeHtml(idQrStocker)}" title="Lihat detail transaksi ${escapeHtml(idQrStocker)}">${escapeHtml(idQrStocker)}</button>`;
    }

    // ===== Detail transaksi stocker (DC IN s/d Secondary) =====
    var stockerDetailData = [];
    var stockerDetailRequest = null;

    // Klik No. Stocker : buka detail (tidak ikut memilih / men-toggle baris)
    $(document).on('click', '.stocker-detail-link', function (e) {
        e.preventDefault();
        e.stopPropagation();

        openStockerDetail(this.dataset.stocker);
    });

    function stockerDetailShow(state, message = '') {
        document.getElementById('sd-loading').classList.toggle('d-none', state !== 'loading');
        document.getElementById('sd-content').classList.toggle('d-none', state !== 'content');

        let messageEl = document.getElementById('sd-message');
        messageEl.classList.toggle('d-none', state !== 'message');
        messageEl.innerHTML = message;
    }

    function openStockerDetail(idQrStocker) {
        if (!idQrStocker || !document.getElementById('stockerDetailModal')) {
            return;
        }

        document.getElementById('stockerDetailModalLabel').innerHTML = `<i class="fas fa-history fa-sm"></i> Detail Transaksi Stocker <span class="ms-1">${escapeHtml(idQrStocker)}</span>`;
        stockerDetailShow('loading');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('stockerDetailModal')).show();

        if (stockerDetailRequest) {
            stockerDetailRequest.abort();
        }

        stockerDetailRequest = $.ajax({
            url: '{{ route('get-stocker-process') }}',
            type: 'GET',
            data: { id_qr_stocker: idQrStocker },
            dataType: 'json',
            success: function (response) {
                stockerDetailData = (response && response.data) ? response.data : [];

                if (stockerDetailData.length < 1) {
                    stockerDetailShow('message', `<i class="fas fa-box-open fa-2x mb-2"></i><p class="mb-0">Tidak ada data transaksi untuk stocker <b>${escapeHtml(idQrStocker)}</b></p>`);
                    return;
                }

                let index = stockerDetailData.findIndex((item) => item.id_qr_stocker === idQrStocker);
                renderStockerDetail(index > -1 ? index : 0);
            },
            error: function (jqXHR, status) {
                if (status === 'abort') {
                    return;
                }

                stockerDetailShow('message', `<i class="fas fa-exclamation-triangle fa-2x mb-2 text-danger"></i><p class="mb-0 text-danger">Gagal memuat data dari server. Silakan coba lagi.</p>`);
            },
        });
    }

    function renderStockerDetail(index) {
        let item = stockerDetailData[index];

        if (!item) {
            return;
        }

        document.getElementById('stockerDetailModalLabel').innerHTML = `<i class="fas fa-history fa-sm"></i> Detail Transaksi Stocker <span class="ms-1">${escapeHtml(item.id_qr_stocker)}</span>`;

        // Pilihan stocker dalam satu grup + qty terakhir
        // Qty terkecil di grup (= qty grup yang dialokasikan) ditandai merah kalau ada selisih
        let groupQtys = stockerDetailData.map((stocker) => stocker.current_qty).filter((qty) => qty !== null && qty !== undefined).map(Number);
        let minGroupQty = groupQtys.length ? Math.min(...groupQtys) : null;
        let hasQtyDiff = groupQtys.length > 0 && Math.max(...groupQtys) !== minGroupQty;

        document.getElementById('sd-stocker-tabs').innerHTML = stockerDetailData.map((stocker, i) => {
            let hasQty = stocker.current_qty !== null && stocker.current_qty !== undefined;
            let isMin = hasQty && hasQtyDiff && Number(stocker.current_qty) === minGroupQty;

            return `
                <button type="button" class="sd-stocker-tab ${i === index ? 'active' : ''}" onclick="renderStockerDetail(${i})" title="Qty terakhir : ${hasQty ? escapeHtml(stocker.current_qty) : '-'}">
                    ${escapeHtml(stocker.id_qr_stocker)}
                    <span class="sd-stocker-tab-part">${escapeHtml(stocker.nama_part || '')}</span>
                    <span class="sd-stocker-tab-qty ${isMin ? 'is-min' : ''}">${hasQty ? escapeHtml(stocker.current_qty) : '-'}</span>
                </button>
            `;
        }).join('');

        // Badge
        document.getElementById('sd-badges').innerHTML = `
            <span class="badge bg-sb px-3 py-2" style="font-size: 14px;"><i class="fas fa-qrcode me-1"></i> ${escapeHtml(item.id_qr_stocker || '-')}</span>
            <span class="badge bg-light text-dark border px-2 py-2" style="font-size: 12px;">Qty Ply: ${escapeHtml(item.qty_stocker || 0)}</span>
            ${item.part_status ? `<span class="badge bg-info px-2 py-2" style="font-size: 12px;">${escapeHtml(String(item.part_status).toUpperCase())}</span>` : ''}
            ${item.stocker_reject > 0 ? `<span class="badge bg-danger px-2 py-2" style="font-size: 12px;"><i class="fas fa-exclamation-triangle me-1"></i>STOCKER REJECT</span>` : ''}
            ${item.stocker_source_qr ? `<span class="badge bg-warning text-dark px-2 py-2" style="font-size: 12px;">Sumber : ${escapeHtml(item.stocker_source_qr)}</span>` : ''}
        `;

        // Info
        document.getElementById('sd-ws').innerText = item.ws || '-';
        document.getElementById('sd-color').innerHTML = item.color ? `<span class="d-inline-flex align-items-center gap-1">${colorDotHtml(item.color)}${escapeHtml(item.color)}</span>` : '-';
        document.getElementById('sd-size').innerText = item.size || '-';
        document.getElementById('sd-panel').innerText = item.panel || '-';
        document.getElementById('sd-nama-part').innerText = item.nama_part || '-';
        document.getElementById('sd-tujuan').innerText = item.tujuan || '-';
        document.getElementById('sd-proses').innerText = item.proses || '-';

        // Ringkasan per tahap
        let stat = (title, value, cls = '') => `<div class="col px-1"><div class="sd-stat"><div class="sd-stat-title ${cls}">${title}</div><div class="sd-stat-val ${cls}">${escapeHtml(value || 0)}</div></div></div>`;
        let stage = (badgeClass, label, lastQty, lastClass, stats) => `
            <div class="col-xl-3 col-md-6">
                <div class="sd-stage">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="sd-stage-badge ${badgeClass}">${label}</span>
                        <span class="fw-bold ${lastClass}">Qty Terakhir: ${escapeHtml(lastQty || 0)}</span>
                    </div>
                    <div class="row g-0">${stats}</div>
                </div>
            </div>
        `;

        let secInhouseLastQty = (item.sec_inhouse_qty_hasil && item.sec_inhouse_qty_hasil !== 0) ? item.sec_inhouse_qty_hasil : (item.qty_sec_inhouse_in || 0);

        document.getElementById('sd-stages').innerHTML =
            stage('sd-bg-dc', 'DC IN', item.dc_qty_hasil, 'text-primary',
                stat('AWAL', item.dc_qty_awal) + stat('REJ', item.dc_qty_reject, 'text-danger') + stat('REP', item.dc_qty_replace, 'text-success')) +
            stage('sd-bg-sec-inhouse', 'SEC. DALAM', secInhouseLastQty, 'text-orange',
                stat('IN', item.qty_sec_inhouse_in) + stat('AWAL', item.sec_inhouse_qty_awal) + stat('REJ', item.sec_inhouse_qty_reject, 'text-danger') + stat('REP', item.sec_inhouse_qty_replace, 'text-success')) +
            stage('sd-bg-sec-in', 'SEC. MASUK', item.sec_in_qty_hasil, 'text-success',
                stat('AWAL', item.sec_in_qty_awal) + stat('REJ', item.sec_in_qty_reject, 'text-danger') + stat('REP', item.sec_in_qty_replace, 'text-success')) +
            stage('sd-bg-sec-update', 'SEC. UPDATE', item.update_qty_hasil, 'text-danger',
                stat('REJ', item.update_reject, 'text-danger') + stat('REP', item.update_replace, 'text-success'));

        // Riwayat per tahap
        let cell = (value) => `<td>${escapeHtml(value ?? '-')}</td>`;
        let num = (value) => `<td>${escapeHtml(value ?? 0)}</td>`;
        let net = (value) => `<td class="fw-bold">${escapeHtml(value ?? 0)}</td>`;

        let fillHistory = (key, tbodyId, countId, colspan, emptyText, rowFn) => {
            let rows = item[key] || [];
            document.getElementById(countId).innerText = rows.length;
            document.getElementById(tbodyId).innerHTML = rows.length
                ? rows.map((row, i) => `<tr><td>${i + 1}</td>${rowFn(row)}</tr>`).join('')
                : `<tr><td colspan="${colspan}" class="text-muted">${emptyText}</td></tr>`;
        };

        fillHistory('history_dc', 'sd-tbody-dc', 'sd-count-dc', 7, 'Tidak ada transaksi DC IN',
            (row) => cell(row.tgl_trans) + cell(row.created_at) + num(row.qty_awal) + num(row.qty_reject) + num(row.qty_replace) + net(row.hasil));
        fillHistory('history_sec_inhouse_in', 'sd-tbody-sec-inhouse-in', 'sd-count-sec-inhouse-in', 6, 'Tidak ada transaksi Sec. Dalam IN',
            (row) => cell(row.tgl_trans) + cell(row.created_at) + cell(row.tujuan) + cell(row.proses) + net(row.qty_in));
        fillHistory('history_sec_inhouse', 'sd-tbody-sec-inhouse', 'sd-count-sec-inhouse', 9, 'Tidak ada transaksi Sec. Dalam OUT',
            (row) => cell(row.tgl_trans) + cell(row.created_at) + cell(row.tujuan) + cell(row.proses) + num(row.qty_awal) + num(row.qty_reject) + num(row.qty_replace) + net(row.hasil));
        fillHistory('history_sec_in', 'sd-tbody-sec-in', 'sd-count-sec-in', 9, 'Tidak ada transaksi Terima Secondary',
            (row) => cell(row.tgl_trans) + cell(row.created_at) + cell(row.tujuan) + cell(row.proses) + num(row.qty_awal) + num(row.qty_reject) + num(row.qty_replace) + net(row.hasil));
        fillHistory('history_sec_update', 'sd-tbody-sec-update', 'sd-count-sec-update', 8, 'Tidak ada data Secondary Update',
            (row) => cell(row.tgl_trans) + cell(row.created_at) + cell(row.tujuan) + cell(row.proses) + num(row.reject) + num(row.replace) + net(row.hasil));

        // Kembali ke tab pertama
        let firstTab = document.querySelector('#stockerDetailTab .nav-link');
        if (firstTab) {
            bootstrap.Tab.getOrCreateInstance(firstTab).show();
        }

        stockerDetailShow('content');
    }

    function renderWsc(ws, style, color) {
        return `
            <div class="wsc-ws">${escapeHtml(ws)}</div>
            <div class="wsc-style">${escapeHtml(style)}</div>
            <div class="wsc-color">${colorDotHtml(color)}${escapeHtml(color)}</div>
        `;
    }

    function renderSizeQty(size, qty) {
        return `
            <div class="sq-size">${escapeHtml(size)}</div>
            <div class="sq-qty">${escapeHtml(qty)}</div>
        `;
    }

    function renderMultiline(value) {
        return String(value || '').split(',').map((item) => escapeHtml(item.trim())).filter((item) => item).join('<br>');
    }

    // ===== Filter modal =====
    var stockFilterTable = null;
    var stockFilterOnApply = null;

    function initStockFilter(table, options = {}) {
        stockFilterTable = table;
        stockFilterOnApply = options.onApply || null;

        $('#filter-stock-fields .filter-select').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#filterStockModal'),
            placeholder: 'Semua',
            allowClear: true,
            width: '100%',
        });

        // Pencarian No. Stocker (di luar modal) : langsung menyaring saat mengetik
        let searchTimer = null;
        $(document).on('input', '.stock-filter-input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => applyStockSearch(), 250);
        });

        $(document).on('keydown', '.stock-filter-input', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // jangan submit form
                clearTimeout(searchTimer);
                applyStockSearch();
            }
        });
    }

    // Pencarian teks No. Stocker (tidak dihitung di badge / chip filter, nilainya sudah terlihat di input)
    function applyStockSearch() {
        if (!stockFilterTable) {
            return;
        }

        document.querySelectorAll('.stock-filter-input[data-column]').forEach((input) => {
            stockFilterTable.column(input.dataset.column).search(input.value.trim());
        });

        stockFilterTable.draw();

        if (typeof stockFilterOnApply === 'function') {
            stockFilterOnApply();
        }
    }

    // Label field filter (teks <label> di atas input / select)
    function stockFilterLabel(field) {
        let label = field.closest('div') ? field.closest('div').querySelector('label') : null;
        return label ? label.innerText.trim() : '';
    }

    // Chip filter aktif di toolbar (#filter-active-chips)
    function renderActiveFilterChips(activeFilters) {
        let container = document.getElementById('filter-active-chips');

        if (!container) {
            return;
        }

        container.innerHTML = activeFilters.map((filter, index) => `
            <span class="filter-chip" title="${escapeHtml(filter.label)} : ${escapeHtml(filter.value)}">
                <span class="filter-chip-label">${escapeHtml(filter.label)}:</span>
                <span class="filter-chip-value">${escapeHtml(filter.value)}</span>
                <button type="button" class="filter-chip-remove" data-index="${index}" aria-label="Hapus filter ${escapeHtml(filter.label)}">&times;</button>
            </span>
        `).join('');

        // Hapus satu filter
        container.querySelectorAll('.filter-chip-remove').forEach((button) => {
            button.addEventListener('click', () => {
                let field = activeFilters[button.dataset.index].field;

                $(field).val('').trigger('change');

                applyStockFilter();
            });
        });
    }

    function applyStockFilter() {
        if (!stockFilterTable) {
            return;
        }

        let activeCount = 0;
        let activeFilters = [];

        // No. Stocker (di luar modal) : pencarian teks biasa
        document.querySelectorAll('.stock-filter-input[data-column]').forEach((input) => {
            stockFilterTable.column(input.dataset.column).search(input.value.trim());
        });

        // Select : exact = harus sama persis, contains = salah satu nilai di dalam sel (Panel, Part)
        document.querySelectorAll('#filter-stock-fields select[data-column]').forEach((select) => {
            let value = ($(select).val() || '').trim();
            let pattern = '';

            if (value) {
                pattern = select.dataset.match === 'exact'
                    ? '^\\s*' + escapeRegex(value) + '\\s*$'
                    : escapeRegex(value);

                activeCount++;
                activeFilters.push({ label: stockFilterLabel(select), value: value, field: select });
            }

            stockFilterTable.column(select.dataset.column).search(pattern, true, false);
        });

        stockFilterTable.draw();

        let filterCount = document.getElementById('filter-count');
        if (filterCount) {
            filterCount.innerText = activeCount;
            filterCount.classList.toggle('d-none', activeCount < 1);
        }

        let resetInline = document.getElementById('filter-reset-inline');
        if (resetInline) {
            resetInline.classList.toggle('d-none', activeCount < 1);
        }

        renderActiveFilterChips(activeFilters);

        if (typeof stockFilterOnApply === 'function') {
            stockFilterOnApply();
        }

        bootstrap.Modal.getOrCreateInstance(document.getElementById('filterStockModal')).hide();
    }

    // Reset filter modal (pencarian No. Stocker tidak ikut dikosongkan)
    function resetStockFilter() {
        $('#filter-stock-fields .filter-select').val('').trigger('change');

        applyStockFilter();
    }

    // ===== Tabel halaman Alokasi (partials/stock-table-allocate) =====
    // Data diambil sekali lewat ajax (tanpa server side), filter & paging di browser.
    function createAllocateStockTable(selector, options) {
        let table = $(selector).DataTable({
            ordering: false,
            processing: true,
            ajax: Object.assign({ dataSrc: 'data' }, options.ajax),
            // Selisih qty minus : highlight baris
            createdRow: (row, data) => {
                row.classList.toggle('has-qty-diff', Number(data.diff_qty || 0) < 0);
            },
            columns: [
                { data: 'id' },              // 0 Act
                { data: 'id_qr_stocker' },   // 1 No. Stocker
                { data: 'act_costing_ws' },  // 2 WS / Style / Color
                { data: 'qty' },             // 3 Size / Qty
                { data: 'no_cut' },          // 4 No. Cut
                { data: 'panel' },           // 5 Panel
                { data: 'nama_part' },       // 6 Part
                { data: 'rangeAwalAkhir' },  // 7 Range
                { data: 'user' },            // 8 By
                { data: 'act_costing_ws' },  // 9  No. WS (filter)
                { data: 'style' },           // 10 Style (filter)
                { data: 'color' },           // 11 Color (filter)
                { data: 'size' },            // 12 Size (filter)
            ],
            columnDefs: [
                {
                    targets: [0],
                    render: (data, type, row) => {
                        if (type !== 'display') {
                            return data;
                        }

                        return `
                            <div class="d-flex gap-1 justify-content-center">
                                <a class="btn btn-danger btn-sm" data="${escapeHtml(JSON.stringify(row))}" data-url="${options.deleteUrl}/${row.id}" onclick="deleteData(this);">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        `;
                    }
                },
                {
                    targets: [1],
                    className: 'col-stocker',
                    render: (data, type) => type === 'display' ? renderStockerList(data) : data,
                },
                {
                    targets: [2],
                    className: 'col-wsc',
                    render: (data, type, row) => type === 'display' ? renderWsc(row.act_costing_ws, row.style, row.color) : data,
                },
                {
                    targets: [3],
                    className: 'col-sq',
                    render: (data, type, row) => type === 'display' ? renderSizeQty(row.size, row.qty) : data,
                },
                {
                    targets: [5, 6],
                    render: (data, type) => type === 'display' ? renderMultiline(data) : data,
                },
                {
                    targets: [7],
                    render: (data, type, row) => {
                        let diff = Number(row.diff_qty || 0);
                        return escapeHtml(data) + (diff !== 0 ? ` <span class="qty-diff">(${diff})</span>` : '');
                    }
                },
                {
                    targets: [9, 10, 11, 12],
                    visible: false,
                },
                {
                    targets: '_all',
                    className: 'text-nowrap',
                },
            ],
        });

        initStockFilter(table, {
            onApply: () => updateStockSummary(table),
        });

        // Setelah data baru di-load : perbarui opsi filter
        let reloadOptions = false;
        table.on('xhr.dt', () => { reloadOptions = true; });
        table.on('draw.dt', () => {
            if (reloadOptions) {
                reloadOptions = false;
                refreshStockFilterOptions();
            }

            updateStockSummary(table);
        });

        return table;
    }

    // Ringkasan jumlah & total qty dari baris yang tampil (sesuai filter)
    function updateStockSummary(table) {
        let rows = table.rows({ search: 'applied' }).data().toArray();
        let totalQty = rows.reduce((acc, row) => acc + Number(row.qty || 0), 0);

        let count = document.getElementById('stock-summary-count');
        let qty = document.getElementById('stock-summary-qty');

        if (count) {
            count.innerText = rows.length.toLocaleString('id-ID');
        }

        if (qty) {
            qty.innerText = totalQty.toLocaleString('id-ID');
        }

        updateAllocateActionBar();
    }

    // Action bar bawah halaman Alokasi : tampil saat ada filter aktif,
    // ringkasan (elemen yang sama) ikut pindah ke bar supaya hasil filter langsung terlihat
    function updateAllocateActionBar() {
        let bar = document.getElementById('stock-action-bar');
        let chips = document.getElementById('filter-active-chips');
        let summary = document.getElementById('stock-summary');

        if (!bar || !chips || !summary) {
            return;
        }

        let hasFilter = chips.children.length > 0;
        let target = document.getElementById(hasFilter ? 'stock-action-summary' : 'stock-summary-slot');

        bar.classList.toggle('d-none', !hasFilter);

        if (target && summary.parentElement !== target) {
            target.appendChild(summary);
        }
    }

    // Isi ulang opsi select dari data tabel (untuk tabel yang datanya di-load lewat ajax)
    // Nilai yang sedang dipilih tetap dipertahankan.
    function refreshStockFilterOptions() {
        if (!stockFilterTable) {
            return;
        }

        document.querySelectorAll('#filter-stock-fields select[data-column]').forEach((select) => {
            let values = [];

            stockFilterTable.column(select.dataset.column).data().each((value) => {
                let items = select.dataset.match === 'contains' ? String(value ?? '').split(',') : [value];

                items.forEach((item) => {
                    let text = String(item ?? '').trim();

                    if (text && !values.includes(text)) {
                        values.push(text);
                    }
                });
            });

            values.sort((a, b) => a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' }));

            let current = $(select).val();

            $(select).empty().append(new Option('', '', false, false));
            values.forEach((value) => {
                $(select).append(new Option(value, value, false, value === current));
            });

            // Nilai lama sudah tidak ada di data : tetap tampilkan supaya filter tidak hilang diam-diam
            if (current && !values.includes(current)) {
                $(select).append(new Option(current, current, true, true));
            }

            $(select).trigger('change.select2');
        });
    }
</script>
