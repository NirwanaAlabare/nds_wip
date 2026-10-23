@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-fixedcolumns/css/fixedColumns.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style type="text/css">
        #list-table th,
        #list-table td {
            white-space: nowrap;
            vertical-align: middle;
        }

        /* Border "separate" supaya tidak ada celah di kolom freeze saat scroll */
        .dataTables_scroll table.table-bordered {
            border-collapse: separate;
            border-spacing: 0;
            border-width: 1px 0 0 1px;
        }

        .dataTables_scroll table.table-bordered th,
        .dataTables_scroll table.table-bordered td {
            border-width: 0 1px 1px 0;
        }

        /* Header bayangan di dalam body scroll (dipakai DataTables untuk lebar kolom): dibuat setipis mungkin,
           warnanya tetap ikut warna panel (inline style) */
        .dataTables_scrollBody thead th {
            height: 0 !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            border-top-width: 0 !important;
            border-bottom-width: 0 !important;
        }

        /* Bagian header bayangan di bawah Buyer - Size ikut freeze (class & left diisi fixFrozenLeft) */
        .dataTables_scrollBody thead th.frozen-clone {
            position: sticky;
            z-index: 2;
            background-color: #b8daff;
        }

        /* thead.table-primary ikut ter-clone ke body, latarnya bikin garis biru di bawah header */
        .dataTables_scrollBody thead,
        .dataTables_scrollBody thead tr {
            background: transparent !important;
        }

        /* Kolom freeze (Buyer - Size) */
        #list-table tbody td.dtfc-fixed-left {
            z-index: 1;
        }

        #list-table thead th.dtfc-fixed-left,
        .dataTables_scrollHead th.dtfc-fixed-left {
            background-color: #b8daff;
        }

        #list-table tfoot th.dtfc-fixed-left,
        .dataTables_scrollFoot th.dtfc-fixed-left {
            background-color: #fff;
        }

    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header bg-sb">
            <h5 class="card-title fw-bold mb-0">
                <i class="fas fa-list"></i> Proses Output
            </h5>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-end gap-3 mb-3">
                <div>
                    <label class="form-label"><small>Buyer</small></label>
                    <select id="buyer_filter" class="form-control form-control-sm select2bs4" style="width: 220px;">
                        <option value="">Pilih Buyer</option>
                        @foreach ($buyers as $buyer)
                            <option value="{{ $buyer }}">{{ $buyer }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label"><small>WS</small></label>
                    <select id="ws_filter" class="form-control form-control-sm select2bs4" style="width: 170px;">
                        <option value="">Semua WS</option>
                    </select>
                </div>

                <div>
                    <label class="form-label"><small>Style</small></label>
                    <select id="style_filter" class="form-control form-control-sm select2bs4" style="width: 170px;">
                        <option value="">Semua Style</option>
                    </select>
                </div>

                <div>
                    <label class="form-label"><small>Color</small></label>
                    <select id="color_filter" class="form-control form-control-sm select2bs4" style="width: 170px;">
                        <option value="">Semua Color</option>
                    </select>
                </div>

                <div>
                    <label class="form-label"><small>Size</small></label>
                    <select id="size_filter" class="form-control form-control-sm select2bs4" style="width: 170px;">
                        <option value="">Semua Size</option>
                    </select>
                </div>

                <div>
                    <button class="btn btn-primary btn-sm" id="btnSearch" onclick="listTableReload()">
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
            </div>
            {{-- Grafik output per panel (ikut hasil search tabel) --}}
            {{-- Tanpa overflow-hidden supaya tooltip grafik tidak terpotong --}}
            <div id="panel-chart-card" class="mb-3 border rounded shadow-sm d-none">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-3 py-2 border-bottom bg-light rounded-top">
                    <div class="fw-bold"><i class="fas fa-chart-bar text-primary me-1"></i> Output Per Panel</div>
                    <div id="panel-chart-summary" class="d-flex flex-wrap gap-2"></div>
                </div>
                <div id="panel-chart" class="px-2 pt-2"></div>
            </div>
            <div>
                <table class="table table-bordered table-striped w-100 text-nowrap" id="list-table">
                    {{-- Header & body dibangun lewat JS sesuai panel yang ada di data (buildListTable) --}}
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-fixedcolumns/js/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-fixedcolumns/js/fixedColumns.bootstrap4.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <!-- ApexCharts -->
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>
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

        const filterLabels = {
            ws: 'Semua WS',
            style: 'Semua Style',
            color: 'Semua Color',
            size: 'Semua Size',
        };

        // Isi ulang pilihan filter dari master_sb_ws
        function loadFilter(type, params) {
            const $select = $(`#${type}_filter`);

            $select.html(`<option value="">${filterLabels[type]}</option>`).trigger('change.select2');

            if (!params.buyer && !params.ws) return;

            $.get('{{ route('get-filter-proses-output') }}', { type, ...params }, function (res) {
                $select.append(res.map(value => $('<option>').val(value).text(value)));
            });
        }

        // Buyer berubah: daftar WS ikut buyer, style / color / size dikosongkan
        $('#buyer_filter').on('change', function () {
            loadFilter('ws', { buyer: this.value });
            ['style', 'color', 'size'].forEach(type => loadFilter(type, {}));
        });

        // WS berubah: style / color / size ikut WS
        $('#ws_filter').on('change', function () {
            ['style', 'color', 'size'].forEach(type => loadFilter(type, { ws: this.value }));
        });

        // Kolom teks (1 baris per buyer / ws / style / color / size)
        const textColumns = [
            ['Buyer', 'buyer'], ['WS', 'ws'], ['Style', 'style'], ['Color', 'color'], ['Size', 'size'],
        ];

        // Sub kolom tiap panel: [judul, field dari server]
        const qtyColumns = [
            ['Form', 'qty_form'],
            ['Scrap', 'qty_scrap'],
            ['Switching Out', 'qty_switching_out'],
            ['Switching In', 'qty_switching_in'],
            ['Adjustment', 'qty_adjustment'],
        ];

        // Kolom ujung setelah semua panel: [judul, field]
        const endColumns = [
            ['Output Set', 'output_set'],
            ['Qty Order', 'qty_order'],
            ['Balance', 'balance'],
        ];

        // Output per panel = form + scrap - switching out + switching in + adjustment
        function hitungOutput(qty) {
            return qty.qty_form + qty.qty_scrap - qty.qty_switching_out + qty.qty_switching_in + qty.qty_adjustment;
        }

        // Data server (1 baris per panel) jadi 1 baris per buyer / ws / color / size, panel jadi kolom
        function pivotData(data, qtyOrder = {}) {
            const panels = [...new Set(data.map(item => item.panel ?? '-'))].sort();
            const rows = new Map();

            data.forEach(item => {
                const key = [item.ws, item.color, item.size].join('|');

                if (!rows.has(key)) {
                    rows.set(key, {
                        buyer: item.buyer, ws: item.ws, style: item.style, color: item.color, size: item.size,
                        size_order: Number(item.size_order) || 0, panels: {},
                    });
                }

                const row = rows.get(key);
                const panel = row.panels[item.panel ?? '-'] ??= {};

                qtyColumns.forEach(([, field]) => panel[field] = (panel[field] ?? 0) + Number(item[field] ?? 0));
                row.size_order = Math.min(row.size_order, Number(item.size_order) || 0);
            });

            rows.forEach(row => {
                panels.forEach(name => {
                    const panel = row.panels[name] ??= {};

                    qtyColumns.forEach(([, field]) => panel[field] ??= 0);
                    panel.output = hitungOutput(panel);
                });

                // Output set = output panel terkecil
                row.output_set = panels.length ? Math.min(...panels.map(name => row.panels[name].output)) : 0;

                // Balance = output set - qty order
                row.qty_order = Number(qtyOrder[[row.ws, row.color, row.size].join('|')] ?? 0);
                row.balance = row.output_set - row.qty_order;
            });

            const sorted = [...rows.values()].sort((a, b) =>
                String(a.buyer).localeCompare(String(b.buyer)) || String(a.ws).localeCompare(String(b.ws)) || String(a.style).localeCompare(String(b.style)) || String(a.color).localeCompare(String(b.color)) || a.size_order - b.size_order
            );

            return { panels, rows: sorted };
        }

        const formatQty = value => Number(value ?? 0).toLocaleString('id-ID');

        // Warna header per panel: [latar, garis lebih gelap dari latar] (berulang jika panel lebih banyak dari daftar warna)
        const panelColors = [
            ['#d1e7dd', '#a3cfbb'], // hijau
            ['#fff3cd', '#ffe69c'], // kuning
            ['#f8d7da', '#f1aeb5'], // merah
            ['#e2d9f3', '#c5b3e6'], // ungu
            ['#ffe5d0', '#fecba1'], // oranye
            ['#d2f4ea', '#a6e9d5'], // tosca
            ['#f7d6e6', '#efadce'], // pink
            ['#e2e3e5', '#c4c8cb'], // abu-abu
        ];

        const panelStyle = p => {
            const [background, border] = panelColors[p % panelColors.length];

            return `background-color: ${background}; border-color: ${border} !important;`;
        };

        let listTable;
        let panelChart;

        // Grafik batang: total output per panel, garis putus-putus = output set
        function renderPanelChart(panels, rows) {
            $('#panel-chart-card').toggleClass('d-none', !panels.length);

            if (!panels.length) return;

            // Total qty per panel (untuk batang & rincian tooltip)
            const totals = panels.map(panel => {
                const total = { output: 0 };

                rows.forEach(row => {
                    total.output += row.panels[panel].output;
                    qtyColumns.forEach(([, field]) => total[field] = (total[field] ?? 0) + row.panels[panel][field]);
                });

                return total;
            });
            const outputSet = rows.reduce((sum, row) => sum + row.output_set, 0);
            const qtyOrder = rows.reduce((sum, row) => sum + row.qty_order, 0);

            // Ringkasan di header grafik: output set, qty order, total output & panel dengan output terendah
            const lowest = totals.reduce((min, total, p) => total.output < totals[min].output ? p : min, 0);
            const chip = (icon, title, value, style) =>
                `<span class="badge rounded-pill border fw-normal px-3 py-2" style="font-size: 12px; ${style}">
                    <i class="fas ${icon} me-1"></i>${title}: <b>${value}</b>
                </span>`;

            $('#panel-chart-summary').html(
                chip('fa-layer-group', 'Output Set', formatQty(outputSet), 'background-color: #cfe2ff; border-color: #9ec5fe !important; color: #084298;')
                + chip('fa-clipboard-list', 'Qty Order', formatQty(qtyOrder), 'background-color: #f8d7da; border-color: #f1aeb5 !important; color: #842029;')
                + chip('fa-boxes', 'Total Output', formatQty(totals.reduce((sum, total) => sum + total.output, 0)), 'background-color: #fff; color: #495057;')
                + chip('fa-arrow-down', 'Terendah', `${panels[lowest]} (${formatQty(totals[lowest].output)})`, `background-color: ${panelColors[lowest % panelColors.length][0]}; border-color: ${panelColors[lowest % panelColors.length][1]} !important; color: #212529;`)
            );

            const options = {
                series: [{ name: 'Output', data: totals.map(total => total.output) }],
                // Horizontal: categories = panel di kiri, qty di bawah
                xaxis: {
                    categories: panels,
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { formatter: value => formatQty(Math.round(value)), style: { colors: '#6c757d' } },
                },
                // Sumbu qty minimal sampai qty order supaya garis batasnya selalu terlihat
                yaxis: {
                    max: max => Math.max(max, qtyOrder * 1.05),
                    labels: { style: { fontSize: '12px', fontWeight: 700, colors: '#343a40' } },
                },
                // Tinggi grafik menyesuaikan jumlah panel (±48px per panel, jadi tebal batang selalu sama)
                chart: { height: panels.length * 48 + 90 },
                // Warna batang = warna header panel di tabel, garis tepi = garis header
                colors: panels.map((panel, p) => panelColors[p % panelColors.length][0]),
                stroke: { show: true, width: 2, colors: panels.map((panel, p) => panelColors[p % panelColors.length][1]) },
                annotations: {
                    xaxis: [{
                        x: outputSet,
                        borderColor: '#0d6efd',
                        strokeDashArray: 5,
                        label: {
                            text: 'Output Set: ' + formatQty(outputSet),
                            orientation: 'horizontal',
                            position: 'top',
                            offsetY: -8,
                            borderColor: '#0d6efd',
                            style: { background: '#0d6efd', color: '#fff', fontSize: '11px', fontWeight: 600 },
                        },
                    }, {
                        // Batas qty order (label di bawah supaya tidak tabrakan dengan label output set)
                        x: qtyOrder,
                        borderColor: '#dc3545',
                        strokeDashArray: 5,
                        label: {
                            text: 'Qty Order: ' + formatQty(qtyOrder),
                            orientation: 'horizontal',
                            position: 'bottom',
                            offsetY: 8,
                            borderColor: '#dc3545',
                            style: { background: '#dc3545', color: '#fff', fontSize: '11px', fontWeight: 600 },
                        },
                    }],
                },
                tooltip: {
                    custom: ({ dataPointIndex }) => {
                        const total = totals[dataPointIndex];
                        const [background, border] = panelColors[dataPointIndex % panelColors.length];
                        const line = (title, value, bold) =>
                            `<div class="d-flex justify-content-between gap-4${bold ? ' fw-bold border-top pt-1 mt-1' : ''}"><span>${title}</span><span>${formatQty(value)}</span></div>`;

                        return `<div class="px-3 py-2" style="font-size: 12px; min-width: 190px;">
                            <div class="fw-bold mb-1 px-2 rounded" style="background-color: ${background}; border: 1px solid ${border};">${panels[dataPointIndex]}</div>
                            ${qtyColumns.map(([title, field]) => line(title, total[field])).join('')}
                            ${line('Output', total.output, true)}
                        </div>`;
                    },
                },
            };

            if (panelChart) {
                panelChart.updateOptions(options, true, false);
                return;
            }

            panelChart = new ApexCharts(document.querySelector('#panel-chart'), {
                ...options,
                chart: {
                    ...options.chart,
                    type: 'bar',
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                    animations: { enabled: true, speed: 500 },
                    // Bayangan halus supaya batang tidak flat
                    dropShadow: { enabled: true, top: 3, left: 2, blur: 4, color: '#000', opacity: 0.12 },
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        distributed: true,
                        barHeight: '60%',
                        borderRadius: 8,
                        borderRadiusApplication: 'end',
                        dataLabels: { position: 'top' },
                    },
                },
                fill: { type: 'solid', opacity: 1 },
                dataLabels: {
                    enabled: true,
                    offsetX: 34,
                    formatter: formatQty,
                    background: {
                        enabled: true, foreColor: '#212529', borderRadius: 10, padding: 6,
                        borderWidth: 1, borderColor: '#dee2e6', opacity: 1,
                        dropShadow: { enabled: true, top: 1, left: 1, blur: 2, color: '#000', opacity: 0.15 },
                    },
                    style: { fontSize: '11px', fontWeight: 700, colors: ['#fff'] },
                },
                legend: { show: false },
                grid: {
                    borderColor: '#e9ecef',
                    strokeDashArray: 4,
                    xaxis: { lines: { show: true } },
                    yaxis: { lines: { show: false } },
                    // Latar baris selang-seling per panel
                    row: { colors: ['#f8f9fa', 'transparent'], opacity: 1 },
                    padding: { top: 10, right: 60 },
                },
                states: {
                    hover: { filter: { type: 'darken', value: 0.92 } },
                    active: { filter: { type: 'none' } },
                },
            });

            panelChart.render();
        }

        function buildListTable(panels, rows) {
            const panelColumns = [...qtyColumns, ['Output', 'output']];

            const columns = [
                ...textColumns.map(([title, data]) => ({ title, data })),
                ...panels.flatMap((panel, p) => panelColumns.map(([title, field]) => ({
                    title,
                    data: null,
                    className: 'text-end' + (field === 'output' ? ' col-output' : ''),
                    render: function (data, type, row) {
                        const value = row.panels[panel][field];

                        if (type !== 'display') return value;

                        // Output panel yang sama dengan output set ditulis biru
                        return field === 'output' && value === row.output_set
                            ? `<span class="text-primary fw-bold">${formatQty(value)}</span>`
                            : formatQty(value);
                    }
                }))),
                // Kolom paling ujung, hanya jika ada panel
                ...(panels.length ? endColumns.map(([title, data]) => ({
                    title,
                    data,
                    className: 'text-end col-output',
                    render: function (value, type) {
                        if (type !== 'display') return value;

                        // Balance: plus hijau, minus merah
                        if (data === 'balance' && value !== 0) {
                            return `<span class="${value > 0 ? 'text-success' : 'text-danger'} fw-bold">${formatQty(value)}</span>`;
                        }

                        return formatQty(value);
                    }
                })) : []),
            ];

            if (listTable) {
                listTable.destroy();
            }

            // Header 2 baris: panel (colspan) & sub kolom qty
            const panelRow = textColumns.map(([title]) => `<th rowspan="${panels.length ? 2 : 1}" class="text-center align-middle">${title}</th>`).join('')
                + panels.map((panel, p) => `<th colspan="${panelColumns.length}" class="text-center align-middle" style="${panelStyle(p)}">${panel}</th>`).join('')
                + (panels.length ? endColumns.map(([title]) => `<th rowspan="2" class="text-center align-middle col-output">${title}</th>`).join('') : '');
            const subRow = panels.map((panel, p) => panelColumns.map(([title, field]) =>
                `<th class="text-center align-middle${field === 'output' ? ' col-output' : ''}" style="${panelStyle(p)}">${title}</th>`
            ).join('')).join('');
            // Tanpa colspan supaya footer kolom freeze tetap sejajar
            const footRow = textColumns.map((col, i) => `<th class="text-end">${i === textColumns.length - 1 ? 'Total' : ''}</th>`).join('')
                + columns.slice(textColumns.length).map(col => `<th class="${col.className}">0</th>`).join('');

            $('#list-table').html(`
                <thead class="table-primary">
                    <tr>${panelRow}</tr>
                    ${subRow ? `<tr>${subRow}</tr>` : ''}
                </thead>
                <tbody></tbody>
                <tfoot><tr>${footRow}</tr></tfoot>
            `);

            listTable = $('#list-table').DataTable({
                // Kolom teks pakai sel header teratas (rowspan)
                orderCellsTop: true,
                ordering: false,
                processing: true,
                paging: true,
                pageLength: 50,
                lengthMenu: [
                    [25, 50, 100, 500, -1],
                    [25, 50, 100, 500, 'All']
                ],
                searching: true,
                scrollY: '300px',
                scrollX: true,
                scrollCollapse: false,
                // Freeze Buyer, WS, Style, Color, Size
                fixedColumns: {
                    left: textColumns.length
                },
                data: rows,
                columns: columns,
                columnDefs: [
                    {
                        targets: "_all",
                        defaultContent: "-"
                    },
                ],
                drawCallback: function () {
                    // Grafik dulu (di atas tabel) supaya tinggi tabel dihitung dari posisi akhirnya
                    renderPanelChart(panels, this.api().rows({ search: 'applied' }).data().toArray());

                    fitTableHeight();
                    this.api().columns.adjust();

                    // Total per kolom qty mengikuti hasil search
                    const api = this.api();

                    api.columns().every(function (i) {
                        if (i < textColumns.length) return;

                        const total = api.cells(null, i, { search: 'applied' }).render('sort')
                            .reduce((sum, value) => sum + Number(value ?? 0), 0);

                        $(api.column(i).footer()).text(formatQty(total));
                    });
                },
            });

            // Jalan setelah FixedColumns (handler-nya terdaftar lebih dulu saat init)
            listTable.on('draw.dt column-sizing.dt', fixFrozenLeft);
            fixFrozenLeft();
        }

        // FixedColumns menghitung posisi "left" dari outerWidth() yang dibulatkan, sehingga ada celah 1px
        // antar kolom freeze. Hitung ulang pakai lebar pecahan dari getBoundingClientRect.
        // Header, body & footer dihitung dari lebar selnya masing-masing supaya tidak ada sela.
        function fixFrozenLeft() {
            const columns = [...Array(textColumns.length).keys()].map(i => listTable.column(i));
            const groups = [
                columns.map(col => col.nodes().toArray()),
                columns.map(col => [col.header()]),
                columns.map(col => [col.footer()]),
                // Header bayangan di body scroll: baris pertama, sel Buyer - Size (rowspan)
                $('.dataTables_scrollBody thead tr:first-child th').slice(0, columns.length).addClass('frozen-clone')
                    .toArray().map(th => [th]),
            ];

            groups.forEach(cells => {
                let left = 0;

                cells.forEach(nodes => {
                    if (!nodes.length || !nodes[0]) return;

                    $(nodes).css('left', left + 'px');
                    left += nodes[0].getBoundingClientRect().width;
                });
            });
        }

        buildListTable([], []);

        // Tinggi area scroll tabel mengikuti sisa layar sampai bawah (minimal 300px)
        function fitTableHeight() {
            const $body = $('#list-table').closest('.dataTables_scrollBody');

            if (!$body.length) return;

            const reserved = 170; // total + info "Showing ..." + pagination + padding card + footer
            const height = Math.max(300, window.innerHeight - $body.offset().top - reserved);

            $body.css({ 'height': height + 'px', 'max-height': height + 'px' });
        }

        $(window).on('resize', function () {
            fitTableHeight();
            listTable?.columns.adjust();
        });

        // Filter yang dipilih; null jika buyer & ws belum dipilih
        function filterParams() {
            const params = {
                buyer: $('#buyer_filter').val(),
                ws: $('#ws_filter').val(),
                style: $('#style_filter').val(),
                color: $('#color_filter').val(),
                size: $('#size_filter').val(),
            };

            if (!params.buyer && !params.ws) {
                iziToast.warning({
                    title: 'Perhatian',
                    message: 'Pilih buyer atau WS terlebih dahulu.',
                    position: 'topCenter'
                });
                return null;
            }

            return params;
        }

        async function export_excel() {
            const params = filterParams();

            if (!params) return;

            Swal.fire({
                title: "Exporting",
                html: "Please Wait...",
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            try {
                const res = await fetch('{{ route('export-excel-proses-output') }}?' + new URLSearchParams(params));

                Swal.close();

                if (!res.ok) {
                    const json = await res.json().catch(() => ({}));

                    iziToast.warning({
                        title: 'Perhatian',
                        message: json.message ?? 'Export gagal',
                        position: 'topCenter'
                    });
                    return;
                }

                iziToast.success({
                    title: 'Success',
                    message: 'Success',
                    position: 'topCenter'
                });

                const blob = await res.blob();
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = "Proses Output " + (params.ws || params.buyer).replace(/\//g, '-') + ".xlsx";
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

        function listTableReload() {
            const params = filterParams();

            if (!params) return;

            $.ajax({
                url: '{{ route('get-data-proses-output') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: params,
                beforeSend: function () {
                    showLoading();
                    $('#btnSearch').prop('disabled', true);
                },
                success: function (res) {
                    const { panels, rows } = pivotData(res.data, res.qty_order);

                    buildListTable(panels, rows);
                },
                error: function (xhr) {
                    iziToast.error({
                        title: 'Gagal',
                        message: xhr.responseJSON?.message ?? 'Gagal mengambil data',
                        position: 'topCenter'
                    });
                },
                complete: function () {
                    hideLoading();
                    $('#btnSearch').prop('disabled', false);
                }
            });
        }
    </script>
@endsection
