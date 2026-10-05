@extends('layouts.index', ['containerFluid' => true])

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
<style>
    .rekon-wrap { max-width: 1500px; margin: 10px auto; }
    .rekon-num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .rekon-diff-pos { color: #b02a37; font-weight: 600; }
    .rekon-diff-neg { color: #0a58ca; font-weight: 600; }
    .rekon-ok { color: #198754; font-weight: 600; }
    .rekon-summary td, .rekon-summary th { vertical-align: middle; }
    .rekon-summary tr.rekon-ref td { background: #f1f5ff; }
    .rekon-tabs .nav-link { font-size: .8rem; padding: .4rem .7rem; }
    .rekon-tabs .badge { font-size: .7rem; }
    .rekon-edge-info { font-size: .85rem; }
    #rekon-progress td, #rekon-progress th { font-size: .85rem; vertical-align: middle; }
    table.dataTable td, table.dataTable th { font-size: .8rem; }
</style>
@endsection

@section('content')
<div class="rekon-wrap">
    <div class="card card-sb">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-balance-scale fa-sm"></i> Rekonsiliasi Mutasi Fabric</h5>
            <span class="badge bg-secondary">Akses terbatas</span>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-end gap-3">
                <div>
                    <label class="form-label mb-1"><small>Periode (bulan)</small></label>
                    <input type="month" class="form-control form-control-sm" id="periode" value="{{ date('Y-m') }}">
                </div>
                <div>
                    <label class="form-label mb-1"><small>Dari</small></label>
                    <input type="date" class="form-control form-control-sm" id="from" value="{{ date('Y-m-01') }}">
                </div>
                <div>
                    <label class="form-label mb-1"><small>Sampai</small></label>
                    <input type="date" class="form-control form-control-sm" id="to" value="{{ date('Y-m-t') }}">
                </div>
                <div>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-proses" onclick="proses(false)">
                        <i class="fas fa-play"></i> Proses
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-ulang" onclick="proses(true)">
                        <i class="fas fa-sync-alt"></i> Hitung Ulang
                    </button>
                </div>
            </div>
            <p class="text-muted small mt-2 mb-3">
                Angka tiap laporan diambil dari <b>query export Excel</b> laporannya, jadi sama dengan file export.
                Hasil disimpan 6 jam; klik <b>Hitung Ulang</b> setelah ada perbaikan data.
            </p>

            <table class="table table-sm table-bordered mb-0" id="rekon-progress">
                <thead class="table-light">
                    <tr>
                        <th>Laporan</th>
                        <th style="width:170px">Status</th>
                        <th class="rekon-num" style="width:110px">Baris</th>
                        <th class="rekon-num" style="width:110px">Waktu hitung</th>
                        <th style="width:170px">Dihitung pada</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $report)
                        <tr data-report="{{ $report['key'] }}">
                            <td>{{ $report['label'] }}</td>
                            <td class="rekon-status"><span class="badge bg-light text-dark">Belum diproses</span></td>
                            <td class="rekon-num rekon-rows">-</td>
                            <td class="rekon-num rekon-sec">-</td>
                            <td class="rekon-at">-</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div id="rekon-hasil" class="d-none">
        <div class="card mt-3">
            <div class="card-header"><h6 class="fw-bold mb-0">Ringkasan <span id="rekon-periode-label" class="text-muted fw-normal"></span></h6></div>
            <div class="card-body">
                <div id="rekon-status-line" class="mb-2"></div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered rekon-summary mb-2">
                        <thead class="table-light">
                            <tr>
                                <th>Laporan</th>
                                <th class="rekon-num">Qty IN</th>
                                <th class="rekon-num">Selisih IN vs Detail Item</th>
                                <th class="rekon-num">Qty OUT</th>
                                <th class="rekon-num">Selisih OUT vs Detail Item</th>
                                <th class="rekon-num">Saldo Awal</th>
                                <th class="rekon-num">Saldo Akhir</th>
                            </tr>
                        </thead>
                        <tbody id="rekon-summary-body"></tbody>
                    </table>
                </div>
                <small class="text-muted">Baris biru = acuan (Pemasukan/Pengeluaran Detail Item). Selisih = laporan − acuan.</small>

                <div class="row mt-3">
                    <div class="col-lg-6">
                        <h6 class="fw-bold">IN per satuan</h6>
                        <div class="table-responsive"><table class="table table-sm table-bordered" id="rekon-unit-in"></table></div>
                    </div>
                    <div class="col-lg-6">
                        <h6 class="fw-bold">OUT per satuan</h6>
                        <div class="table-responsive"><table class="table table-sm table-bordered" id="rekon-unit-out"></table></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3 mb-4">
            <div class="card-header"><h6 class="fw-bold mb-0">Penyebab Selisih & Data Bermasalah</h6></div>
            <div class="card-body">
                <ul class="nav nav-tabs rekon-tabs flex-wrap" id="rekon-tabs" role="tablist"></ul>
                <div class="tab-content pt-3" id="rekon-tab-content"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script>
    const REPORTS = @json($reports);
    const URL_HITUNG = @json(route('rekonsiliasi-mutasi-hitung'));
    const URL_STATUS = @json(route('rekonsiliasi-mutasi-status'));
    const URL_HASIL = @json(route('rekonsiliasi-mutasi-hasil'));
    const CONCURRENCY = 3;
    const POLL_MS = 10000;

    let runId = 0;
    const timers = {};

    const fmt = (v) => Number(v || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const esc = (s) => $('<div>').text(s == null ? '' : String(s)).html();

    function diffHtml(v) {
        v = Math.round((Number(v) || 0) * 100) / 100;
        if (v === 0) return '<span class="rekon-ok">0,00</span>';
        return '<span class="' + (v > 0 ? 'rekon-diff-pos' : 'rekon-diff-neg') + '">' + (v > 0 ? '+' : '') + fmt(v) + '</span>';
    }

    $('#periode').on('change', function () {
        const [y, m] = this.value.split('-').map(Number);
        if (!y || !m) return;
        const last = new Date(y, m, 0).getDate();
        $('#from').val(`${y}-${String(m).padStart(2, '0')}-01`);
        $('#to').val(`${y}-${String(m).padStart(2, '0')}-${String(last).padStart(2, '0')}`);
    });

    function row(key) { return $(`#rekon-progress tr[data-report="${key}"]`); }

    function setStatus(key, html) { row(key).find('.rekon-status').html(html); }

    function startTimer(key, label) {
        stopTimer(key);
        const start = Date.now();
        const tick = () => {
            const s = Math.floor((Date.now() - start) / 1000);
            setStatus(key, `<span class="badge bg-warning text-dark"><i class="fas fa-spinner fa-spin"></i> ${label} ${Math.floor(s / 60)}:${String(s % 60).padStart(2, '0')}</span>`);
        };
        tick();
        timers[key] = setInterval(tick, 1000);
    }

    function stopTimer(key) {
        if (timers[key]) { clearInterval(timers[key]); delete timers[key]; }
    }

    function markDone(key, res) {
        stopTimer(key);
        setStatus(key, '<span class="badge bg-success"><i class="fas fa-check"></i> Selesai</span>');
        row(key).find('.rekon-rows').text(Number(res.rows).toLocaleString('id-ID'));
        row(key).find('.rekon-sec').text(res.seconds + ' dtk');
        row(key).find('.rekon-at').text(res.computed_at);
    }

    function markError(key, msg) {
        stopTimer(key);
        setStatus(key, `<span class="badge bg-danger" title="${esc(msg)}"><i class="fas fa-times"></i> Gagal</span>`);
    }

    function params(extra) {
        return Object.assign({ from: $('#from').val(), to: $('#to').val() }, extra || {});
    }

    // Pantau laporan yang masih dihitung (atau koneksinya putus karena timeout) sampai hasilnya ada di cache.
    function poll(key, myRun) {
        return new Promise((resolve) => {
            const check = () => {
                if (myRun !== runId) return resolve(false);
                $.get(URL_STATUS, params({ report: key })).done((res) => {
                    if (res.status === 'done') { markDone(key, res); resolve(true); }
                    else if (res.status === 'running') setTimeout(check, POLL_MS);
                    else { markError(key, 'Proses berhenti sebelum selesai'); resolve(false); }
                }).fail((xhr) => { markError(key, xhr.statusText); resolve(false); });
            };
            startTimer(key, 'Menghitung');
            setTimeout(check, POLL_MS);
        });
    }

    function hitung(key, refresh, myRun) {
        return new Promise((resolve) => {
            startTimer(key, 'Menghitung');
            $.ajax({ url: URL_HITUNG, data: params({ report: key, refresh: refresh ? 1 : 0 }), timeout: 0 })
                .done((res) => {
                    if (res.status === 'done') { markDone(key, res); resolve(true); }
                    else poll(key, myRun).then(resolve);
                })
                .fail((xhr) => {
                    // Timeout Apache: proses di server tetap jalan, jadi pantau hasilnya
                    if (xhr.status === 422) { markError(key, 'Parameter tidak valid'); resolve(false); }
                    else poll(key, myRun).then(resolve);
                });
        });
    }

    async function proses(refresh) {
        const from = $('#from').val(), to = $('#to').val();
        if (!from || !to || from > to) {
            Swal.fire({ icon: 'warning', title: 'Periode tidak valid', text: 'Isi tanggal Dari dan Sampai dengan benar.' });
            return;
        }

        const myRun = ++runId;
        $('#btn-proses, #btn-ulang').prop('disabled', true);
        $('#rekon-hasil').addClass('d-none');
        REPORTS.forEach((r) => {
            stopTimer(r.key);
            setStatus(r.key, '<span class="badge bg-light text-dark">Menunggu</span>');
            row(r.key).find('.rekon-rows, .rekon-sec, .rekon-at').text('-');
        });

        const queue = REPORTS.map((r) => r.key);
        const results = {};
        const worker = async () => {
            while (queue.length && myRun === runId) {
                const key = queue.shift();
                results[key] = await hitung(key, refresh, myRun);
            }
        };
        await Promise.all(Array.from({ length: CONCURRENCY }, worker));

        if (myRun !== runId) return;
        $('#btn-proses, #btn-ulang').prop('disabled', false);

        if (Object.values(results).every(Boolean)) {
            loadHasil();
        } else {
            Swal.fire({ icon: 'error', title: 'Ada laporan yang gagal dihitung', text: 'Klik Proses lagi untuk mencoba ulang laporan yang gagal.' });
        }
    }

    function loadHasil() {
        $.get(URL_HASIL, params()).done((res) => {
            if (res.status !== 'done') {
                Swal.fire({ icon: 'warning', title: 'Hasil belum lengkap', text: 'Laporan belum dihitung: ' + (res.missing || []).join(', ') });
                return;
            }
            renderSummary(res);
            renderUnits(res);
            renderTabs(res);
            $('#rekon-periode-label').text(`(${res.from} s/d ${res.to})`);
            $('#rekon-hasil').removeClass('d-none');
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        }).fail((xhr) => Swal.fire({ icon: 'error', title: 'Gagal memuat hasil', text: xhr.statusText }));
    }

    function renderSummary(res) {
        const byKey = Object.fromEntries(res.summary.map((s) => [s.key, s]));
        const refIn = byKey.pdi.total.in, refOut = byKey.kdi.total.out;
        const ins = res.summary.filter((s) => s.has_in).map((s) => s.total.in.toFixed(2));
        const outs = res.summary.filter((s) => s.has_out).map((s) => s.total.out.toFixed(2));
        const sameIn = new Set(ins).size === 1, sameOut = new Set(outs).size === 1;

        $('#rekon-status-line').html(
            (sameIn ? '<span class="badge bg-success me-2"><i class="fas fa-check"></i> IN semua laporan sama</span>'
                    : '<span class="badge bg-danger me-2"><i class="fas fa-times"></i> IN tidak sama</span>') +
            (sameOut ? '<span class="badge bg-success"><i class="fas fa-check"></i> OUT semua laporan sama</span>'
                     : '<span class="badge bg-danger"><i class="fas fa-times"></i> OUT tidak sama</span>')
        );

        $('#rekon-summary-body').html(res.summary.map((s) => `
            <tr class="${s.key === 'pdi' || s.key === 'kdi' ? 'rekon-ref' : ''}">
                <td>${esc(s.label)}</td>
                <td class="rekon-num">${s.has_in ? fmt(s.total.in) : '-'}</td>
                <td class="rekon-num">${s.has_in && s.key !== 'pdi' ? diffHtml(s.total.in - refIn) : '-'}</td>
                <td class="rekon-num">${s.has_out ? fmt(s.total.out) : '-'}</td>
                <td class="rekon-num">${s.has_out && s.key !== 'kdi' ? diffHtml(s.total.out - refOut) : '-'}</td>
                <td class="rekon-num">${s.has_saldo ? fmt(s.total.sal_awal) : '-'}</td>
                <td class="rekon-num">${s.has_saldo ? fmt(s.total.sal_akhir) : '-'}</td>
            </tr>`).join(''));
    }

    function renderUnits(res) {
        const build = (jenis, flag) => {
            const reps = res.summary.filter((s) => s[flag]);
            const units = [...new Set(reps.flatMap((s) => Object.keys(s.per_unit)))].sort();
            let html = '<thead class="table-light"><tr><th>Satuan</th>' + reps.map((s) => `<th class="rekon-num">${esc(s.label)}</th>`).join('') + '</tr></thead><tbody>';
            units.forEach((u) => {
                const vals = reps.map((s) => ((s.per_unit[u] || {})[jenis] || 0));
                const same = new Set(vals.map((v) => v.toFixed(2))).size === 1;
                html += `<tr><td>${esc(u || '(kosong)')} ${same ? '' : '<i class="fas fa-exclamation-triangle text-danger" title="Tidak sama"></i>'}</td>` +
                    vals.map((v) => `<td class="rekon-num">${fmt(v)}</td>`).join('') + '</tr>';
            });
            return html + '</tbody>';
        };
        $('#rekon-unit-in').html(build('in', 'has_in'));
        $('#rekon-unit-out').html(build('out', 'has_out'));
    }

    const textCol = (data, title, opt) => Object.assign({ data, title, render: $.fn.dataTable.render.text(), defaultContent: '' }, opt || {});
    const numCol = (data, title) => ({ data, title, className: 'rekon-num', render: (v, type) => type === 'display' ? fmt(v) : v });
    const diffCol = (data, title) => ({ data, title, className: 'rekon-num', render: (v, type) => type === 'display' ? diffHtml(v) : v });
    const itemCol = { data: null, title: 'Item', render: (r, type) => type === 'display'
        ? `<b>${esc(r.id_item)}</b> ${esc(r.kode_item)}<br><small class="text-muted">${esc(r.nama_item)}</small>`
        : `${r.id_item} ${r.kode_item} ${r.nama_item}` };
    const docCol = { data: 'doc', title: 'Dokumen', render: (v, type, r) => type === 'display' && r.link
        ? `<a href="${esc(r.link)}" target="_blank">${esc(v)} <i class="fas fa-external-link-alt fa-xs"></i></a>` : esc(v) };

    // Daftar dokumen penyusun selisih. Tampil maks. 5, tapi pencarian & Excel memakai daftar lengkap.
    const dokCol = (title) => ({ data: 'dokumen', title, orderable: false, render: (list, type) => {
        list = list || [];
        if (type !== 'display') return list.map((d) => `${d.doc} (${d.qty})`).join('; ');
        if (!list.length) return '<span class="text-muted">-</span>';
        const MAX = 5;
        const item = (d) => (d.link ? `<a href="${esc(d.link)}" target="_blank">${esc(d.doc)}</a>` : esc(d.doc)) +
            ` <small class="text-muted">(${d.tgl ? esc(d.tgl) + ', ' : ''}${fmt(d.qty)})</small>`;
        return list.slice(0, MAX).map(item).join('<br>') +
            (list.length > MAX ? `<br><small class="text-muted">+${list.length - MAX} dokumen lain</small>` : '');
    } });

    function edgeColumns(edge) {
        const cols = [];
        if (edge.group === 'doc') cols.push(docCol);
        if (edge.group === 'barcode') cols.push(textCol('barcode', 'Barcode'));
        cols.push(itemCol);
        if (edge.group === 'doc' || edge.group === 'jo') cols.push(textCol('id_jo', 'JO'));
        cols.push(textCol('unit', 'Satuan'));
        cols.push(numCol('qty_a', edge.label_a), numCol('qty_b', edge.label_b), diffCol('selisih', 'Selisih'));
        if (edge.group === 'doc') cols.push({ data: null, title: 'Baris / Roll', className: 'rekon-num', render: (r) => `${r.n_a} / ${r.n_b}` });
        if (edge.group === 'item' || edge.group === 'barcode') cols.push(dokCol(edge.jenis === 'out' ? 'Dokumen OUT' : 'Dokumen IN'));
        cols.push(textCol('keterangan', 'Keterangan'));
        return cols;
    }

    const PROBLEMS = [
        { id: 'saldo_minus', judul: 'Roll saldo minus', penjelasan: 'Barcode dengan saldo akhir minus di Mutasi Barcode: qty keluar melebihi qty masuk (kemungkinan OUT dobel di dokumen berbeda, atau IN belum tercatat).',
          columns: [textCol('barcode', 'Barcode'), textCol('kode_lok', 'Lokasi'), textCol('no_dok', 'Dok IN'), itemCol, textCol('unit', 'Satuan'),
                    numCol('sal_awal', 'Saldo Awal'), numCol('qty_in', 'IN'), numCol('qty_out', 'OUT'), diffCol('sal_akhir', 'Saldo Akhir'),
                    dokCol('Dokumen OUT dari lokasi ini (semua periode)')] },
        { id: 'dobel', judul: 'Roll dobel di 1 dokumen', penjelasan: 'Barcode yang tercatat lebih dari sekali dalam satu dokumen pengeluaran. Periksa rinciannya, bisa jadi scan/input dobel.',
          columns: [docCol, textCol('tgl', 'Tgl'), textCol('barcode', 'Barcode'), itemCol, textCol('unit', 'Satuan'),
                    { data: 'n', title: 'Jumlah Baris', className: 'rekon-num' }, textCol('rincian', 'Rincian Qty'), numCol('total', 'Total')] },
    ];

    function renderTabs(res) {
        $.fn.dataTable.tables({ api: true }).destroy();
        const $tabs = $('#rekon-tabs').empty(), $content = $('#rekon-tab-content').empty();

        const panes = res.edges.map((e) => ({ id: e.id, judul: e.judul, rows: e.rows, columns: edgeColumns(e),
            info: `${esc(e.penjelasan)}<br><b>${esc(e.label_a)}</b>: ${fmt(e.total_a)} &nbsp;|&nbsp; <b>${esc(e.label_b)}</b>: ${fmt(e.total_b)} &nbsp;|&nbsp; Selisih: ${diffHtml(e.selisih)}` }))
            .concat(PROBLEMS.map((p) => ({ id: p.id, judul: p.judul, rows: res[p.id], columns: p.columns, info: esc(p.penjelasan) })));

        const first = panes.find((p) => p.rows.length) || panes[0];
        panes.forEach((p) => {
            const active = p === first;
            $tabs.append(`<li class="nav-item" role="presentation">
                <button class="nav-link ${active ? 'active' : ''}" data-bs-toggle="tab" data-bs-target="#pane-${p.id}" type="button" role="tab">
                    ${esc(p.judul)} <span class="badge ${p.rows.length ? 'bg-danger' : 'bg-success'}">${p.rows.length}</span>
                </button></li>`);
            $content.append(`<div class="tab-pane fade ${active ? 'show active' : ''}" id="pane-${p.id}" role="tabpanel">
                <div class="rekon-edge-info alert alert-light border py-2">${p.info}</div>
                ${p.rows.length ? `<table class="table table-sm table-bordered table-striped w-100" id="tbl-${p.id}"></table>`
                               : '<div class="text-success"><i class="fas fa-check-circle"></i> Tidak ada selisih.</div>'}
            </div>`);

            if (p.rows.length) {
                $(`#tbl-${p.id}`).DataTable({
                    data: p.rows, columns: p.columns, order: [], pageLength: 10, autoWidth: false,
                    dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>rt<'row'<'col-sm-5'i><'col-sm-7'p>>",
                    buttons: [{ extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-success btn-sm',
                                title: `Rekonsiliasi ${p.judul} ${res.from} sd ${res.to}`, exportOptions: { orthogonal: 'export' } }],
                    language: { search: 'Cari:', info: '_START_-_END_ dari _TOTAL_', infoEmpty: '0 data', paginate: { previous: '‹', next: '›' }, lengthMenu: '_MENU_' },
                });
            }
        });

        $('#rekon-tabs button[data-bs-toggle="tab"]').on('shown.bs.tab', () => $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust());
    }
</script>
@endsection
