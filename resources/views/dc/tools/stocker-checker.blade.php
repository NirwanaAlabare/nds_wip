@extends('layouts.index')

@section('custom-link')
    <style>
        .sc-section {
            border-radius: .5rem;
            padding: 1rem;
            height: 100%;
            border: 1px solid #dee2e6;
            background: #f8f9fa;
        }
        .sc-section h6 {
            font-weight: 600;
            border-bottom: 1px solid rgba(0, 0, 0, .1);
            padding-bottom: .5rem;
            margin-bottom: .75rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .sc-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .sc-row { display: flex; justify-content: space-between; gap: .75rem; font-size: .875rem; margin-bottom: .3rem; }
        .sc-row .sc-label { color: #6c757d; white-space: nowrap; }
        .sc-row .sc-value { font-weight: 500; text-align: right; word-break: break-word; }

        .sc-cutting   { background: #f8f9fa; border-color: #dee2e6; }
        .sc-dcin      { background: #eff6ff; border-color: #dbeafe; }
        .sc-inhouse-in  { background: #fffbeb; border-color: #fef3c7; }
        .sc-inhouse-out { background: #ecfdf5; border-color: #d1fae5; }
        .sc-secin     { background: #faf5ff; border-color: #f3e8ff; }
        .sc-trolley   { background: #ecfeff; border-color: #cffafe; }

        .sc-card-header {
            background: #1f2937;
            color: #fff;
            padding: 1rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: .5rem;
        }
        .sc-card-header .sc-qr { font-size: 1.25rem; font-weight: 700; color: #60a5fa; }
        .sc-card-header .sc-small { font-size: .75rem; color: #9ca3af; text-transform: uppercase; letter-spacing: .05em; display: block; }
        .sc-badge-proses { background: #1e3a8a; color: #bfdbfe; font-size: .75rem; padding: .25rem .6rem; border-radius: .375rem; font-weight: 600; }

        #sc-table th, #sc-table td { vertical-align: middle; font-size: .85rem; }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold text-sb"><i class="fa-solid fa-magnifying-glass"></i> Stocker Checker</h5>
        <a href="{{ route('dc-tools') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali ke Tools</a>
    </div>

    <div class="card card-sb mb-3">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">Pencarian</h5>
        </div>
        <div class="card-body">
            <form id="sc-form" class="row align-items-end g-3" onsubmit="scSearch(event)">
                <div class="col-md-3">
                    <label class="form-label">Pencarian Berdasarkan</label>
                    <select class="form-select" id="sc-search-type" onchange="scChangeType()">
                        <option value="stocker">BY Stocker (QR ID)</option>
                        <option value="form">BY Nomor Form</option>
                        <option value="ws">BY Nomor WS</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" id="sc-input-label">Masukkan ID QR Stocker</label>
                    <input type="text" class="form-control" id="sc-input" placeholder="Contoh: STK-123456" autocomplete="off">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-sb w-100" id="sc-btn-search">
                        <i class="fa fa-search"></i> Cari Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="alert alert-info d-none" id="sc-info"></div>
    <div class="alert alert-danger d-none" id="sc-error"></div>

    <div class="d-flex justify-content-between align-items-center mb-3 d-none" id="sc-toolbar">
        <div class="text-muted small">
            Total Data: <span class="fw-bold text-dark" id="sc-total">0</span> baris ditemukan
        </div>
        <button type="button" class="btn btn-success btn-sm" onclick="scExportExcel()">
            <i class="fa-solid fa-file-excel"></i> Export Excel (.xlsx)
        </button>
    </div>

    {{-- Tampilan BY Stocker: kartu per stocker --}}
    <div id="sc-cards"></div>

    {{-- Tampilan BY Form / WS: tabel --}}
    <div class="card card-sb d-none" id="sc-table-wrap">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm w-100" id="sc-table">
                    <thead class="table-light">
                        <tr>
                            <th>Tgl Trans</th>
                            <th>QR Stocker</th>
                            <th>WS / Style / Color / Size</th>
                            <th class="text-end">Qty Awal</th>
                            <th class="text-end">Reject</th>
                            <th class="text-end">Replace</th>
                            <th class="text-end fw-bold">Qty In</th>
                            <th>Tujuan / Lokasi</th>
                            <th>Sec Inhouse In / Out</th>
                            <th>Sec In</th>
                            <th>Trolley</th>
                            <th>Line</th>
                        </tr>
                    </thead>
                    <tbody id="sc-table-body"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card d-none" id="sc-empty">
        <div class="card-body text-center text-muted py-4" id="sc-empty-text">Tidak ada data yang ditampilkan.</div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/export_excel_js/exceljs.min.js') }}"></script>
    <script>
        let scResults = [];
        let scLastType = 'stocker';
        let scLastInput = '';

        const scLabels = {
            stocker: { label: 'Masukkan ID QR Stocker', placeholder: 'Contoh: STK-123456' },
            form:    { label: 'Masukkan Nomor Form',    placeholder: 'Contoh: 24/08/F-CUT/001' },
            ws:      { label: 'Masukkan Nomor WS',      placeholder: 'Contoh: SMT/0526/035' },
        };

        function scEsc(val) {
            if (val === null || val === undefined) return '';
            return String(val)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        function scOr(val, fallback = '-') {
            return (val === null || val === undefined || val === '') ? fallback : val;
        }

        // YYYY-MM-DD
        function scFormatDateOnly(dateStr) {
            if (!dateStr || dateStr === '-') return '-';
            const m = String(dateStr).match(/^(\d{4}-\d{2}-\d{2})/);
            return m ? m[1] : dateStr;
        }

        // YYYY-MM-DD HH:mm:ss (data dari MySQL sudah berformat ini)
        function scFormatDateTime(dateStr) {
            if (!dateStr || dateStr === '-') return '-';
            const m = String(dateStr).match(/^(\d{4}-\d{2}-\d{2})[ T](\d{2}:\d{2}:\d{2})/);
            return m ? (m[1] + ' ' + m[2]) : dateStr;
        }

        function scChangeType() {
            const type = $('#sc-search-type').val();
            $('#sc-input-label').text(scLabels[type].label);
            $('#sc-input').attr('placeholder', scLabels[type].placeholder).val('');
            scReset();
        }

        function scReset() {
            scResults = [];
            $('#sc-info').addClass('d-none').text('');
            $('#sc-error').addClass('d-none').text('');
            $('#sc-toolbar').addClass('d-none');
            $('#sc-cards').html('');
            $('#sc-table-body').html('');
            $('#sc-table-wrap').addClass('d-none');
            $('#sc-empty').addClass('d-none');
        }

        function scSetLoading(isLoading) {
            $('#sc-btn-search').prop('disabled', isLoading)
                .html(isLoading ? '<i class="fa fa-spinner fa-spin"></i> Memuat...' : '<i class="fa fa-search"></i> Cari Data');
            if (isLoading) {
                $('#sc-empty-text').text('Sedang mengambil data...');
                $('#sc-empty').removeClass('d-none');
            }
        }

        function scGet(url, data) {
            return new Promise((resolve, reject) => {
                $.ajax({ url: url, type: 'get', data: data, dataType: 'json' })
                    .done(res => {
                        if (!res || !res.success) return reject(new Error((res && res.error) || 'Gagal memuat data'));
                        resolve(res);
                    })
                    .fail(xhr => reject(new Error((xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) || 'Terjadi kesalahan sistem')));
            });
        }

        async function scSearch(e) {
            e.preventDefault();
            const type = $('#sc-search-type').val();
            const input = $('#sc-input').val().trim();
            if (!input) return;

            scReset();
            scSetLoading(true);
            scLastType = type;
            scLastInput = input;

            try {
                if (type === 'ws') {
                    const res = await scGet('{{ route('dc-tools-stocker-checker-by-ws') }}', { ws: input });
                    if (res.panelUsed) {
                        $('#sc-info').text('Menampilkan data berdasarkan panel utama: "' + res.panelUsed + '"').removeClass('d-none');
                    }
                    scResults = res.data || [];
                } else {
                    let targetQrs = '';

                    if (type === 'form') {
                        const resForm = await scGet('{{ route('dc-tools-stocker-checker-by-form') }}', { no_form: input });
                        if (!resForm.data || resForm.data.length === 0) {
                            scResults = [];
                            scRender();
                            return;
                        }
                        targetQrs = resForm.data.join(',');
                    } else {
                        targetQrs = input;
                    }

                    const resStocker = await scGet('{{ route('dc-tools-stocker-checker-data') }}', { id_qr_stocker: targetQrs });
                    scResults = resStocker.data || [];
                }

                scRender();
            } catch (err) {
                $('#sc-error').text(err.message || 'Terjadi kesalahan sistem').removeClass('d-none');
                scRender();
            } finally {
                scSetLoading(false);
            }
        }

        function scRowHtml(label, value, cls = '') {
            return '<div class="sc-row"><span class="sc-label">' + scEsc(label) + ':</span> <span class="sc-value ' + cls + '">' + scEsc(value) + '</span></div>';
        }

        function scSectionHtml(cls, dotColor, title, rows) {
            return '<div class="col-md-6 col-lg-4">' +
                '<div class="sc-section ' + cls + '">' +
                    '<h6><span class="sc-dot" style="background:' + dotColor + '"></span>' + scEsc(title) + '</h6>' +
                    rows.join('') +
                '</div>' +
            '</div>';
        }

        function scRender() {
            $('#sc-empty').addClass('d-none');

            if (scResults.length === 0) {
                $('#sc-empty-text').text('Tidak ada data yang ditampilkan.');
                $('#sc-empty').removeClass('d-none');
                return;
            }

            $('#sc-total').text(scResults.length);
            $('#sc-toolbar').removeClass('d-none');

            if (scLastType === 'stocker') {
                const html = scResults.map(item => {
                    return '<div class="card mb-3 overflow-hidden">' +
                        '<div class="sc-card-header">' +
                            '<div><span class="sc-small">ID QR Stocker</span><span class="sc-qr">' + scEsc(item.id_qr_stocker) + '</span></div>' +
                            '<div class="text-md-end">' +
                                '<span class="sc-badge-proses d-inline-block mb-1">Proses Terakhir: ' + scEsc(scOr(item.proses_terakhir)) + '</span>' +
                                '<div class="small text-light opacity-75">Waktu: ' + scEsc(scFormatDateTime(item.tanggal_proses_terakhir)) + '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="card-body"><div class="row g-3">' +
                            scSectionHtml('sc-cutting', '#6366f1', 'Informasi Cutting', [
                                scRowHtml('No Form', scOr(item.no_form)),
                                scRowHtml('No Cut', scOr(item.no_cut_form_cut)),
                                scRowHtml('Selesai Cut', scFormatDateTime(item.waktu_selesai_form_cut)),
                                scRowHtml('Panel', scOr(item.stocker_panel)),
                                scRowHtml('WS', scOr(item.ws)),
                                scRowHtml('Style / Buyer', scOr(item.styleno) + ' / ' + scOr(item.buyer)),
                                scRowHtml('Color / Size', scOr(item.color) + ' / ' + scOr(item.size)),
                                scRowHtml('Nama Part', scOr(item.nama_part)),
                                scRowHtml('Range Stocker', scOr(item.stocker_range)),
                                scRowHtml('Qty Awal Stocker', scOr(item.qty_awal_stocker, 0), 'fw-bold text-dark'),
                            ]) +
                            scSectionHtml('sc-dcin', '#3b82f6', 'Informasi DC IN', [
                                scRowHtml('Tgl Trans', scFormatDateOnly(item.tgl_trans)),
                                scRowHtml('Qty Awal', scOr(item.qty_awal, 0)),
                                scRowHtml('Qty Reject', scOr(item.qty_reject, 0), 'text-danger'),
                                scRowHtml('Qty Replace', scOr(item.qty_replace, 0), 'text-success'),
                                scRowHtml('Qty In', scOr(item.qty_in, 0), 'fw-bold text-primary'),
                                scRowHtml('Tujuan', scOr(item.tujuan)),
                                scRowHtml('Lokasi', scOr(item.lokasi)),
                            ]) +
                            scSectionHtml('sc-inhouse-in', '#f59e0b', 'Informasi Secondary Inhouse IN', [
                                scRowHtml('Waktu Inhouse IN', scFormatDateTime(item.sec_inhouse_in)),
                                scRowHtml('Qty Sec Inhouse IN', scOr(item.qty_sec_inhouse_in, 0), 'fw-bold text-warning'),
                            ]) +
                            scSectionHtml('sc-inhouse-out', '#10b981', 'Informasi Secondary Inhouse OUT', [
                                scRowHtml('Waktu Inhouse OUT', scFormatDateTime(item.sec_inhouse_out)),
                                scRowHtml('Qty Awal', scOr(item.qty_sec_inhouse_awal, 0)),
                                scRowHtml('Qty Reject', scOr(item.qty_sec_inhouse_reject, 0), 'text-danger'),
                                scRowHtml('Terima Replace', scOr(item.qty_sec_inhouse_replace, 0), 'text-success'),
                                scRowHtml('Terima Good (OUT)', scOr(item.qty_sec_inhouse_out, 0), 'fw-bold text-success'),
                            ]) +
                            scSectionHtml('sc-secin', '#a855f7', 'Informasi Secondary IN', [
                                scRowHtml('Waktu Sec IN', scFormatDateTime(item.sec_in)),
                                scRowHtml('Nama Secondary', scOr(item.nama_secondary)),
                                scRowHtml('Proses Secondary', scOr(item.proses_secondary)),
                                scRowHtml('QTY AWAL', scOr(item.qty_sec_in_awal, 0)),
                                scRowHtml('QTY Reject', scOr(item.qty_sec_in_reject, 0), 'text-danger'),
                                scRowHtml('Terima Replace', scOr(item.qty_sec_in_replace, 0), 'text-success'),
                                scRowHtml('Terima Good (OUT)', scOr(item.qty_sec_in_out, 0), 'fw-bold text-success'),
                            ]) +
                            scSectionHtml('sc-trolley', '#06b6d4', 'Informasi Trolley & Loading', [
                                scRowHtml('Trolley', scOr(item.trolley)),
                                scRowHtml('Loading Line', scOr(item.line)),
                            ]) +
                        '</div></div>' +
                    '</div>';
                }).join('');

                $('#sc-cards').html(html);
            } else {
                const html = scResults.map(item => {
                    return '<tr>' +
                        '<td class="text-nowrap">' + scEsc(scFormatDateOnly(item.tgl_trans)) + '</td>' +
                        '<td class="fw-semibold">' + scEsc(item.id_qr_stocker) + '</td>' +
                        '<td><div class="fw-semibold">' + scEsc(item.ws) + '</div>' +
                            '<div class="small text-muted">' + scEsc(item.styleno) + ' | ' + scEsc(item.color) + ' | ' + scEsc(item.size) + '</div></td>' +
                        '<td class="text-end">' + scEsc(item.qty_awal) + '</td>' +
                        '<td class="text-end text-danger">' + scEsc(item.qty_reject) + '</td>' +
                        '<td class="text-end text-success">' + scEsc(item.qty_replace) + '</td>' +
                        '<td class="text-end fw-bold text-primary">' + scEsc(item.qty_in) + '</td>' +
                        '<td><div>' + scEsc(scOr(item.tujuan)) + '</div><div class="small text-muted">' + scEsc(scOr(item.lokasi)) + '</div></td>' +
                        '<td class="small text-nowrap"><div>In: ' + scEsc(scFormatDateTime(item.sec_inhouse_in)) + '</div><div>Out: ' + scEsc(scFormatDateTime(item.sec_inhouse_out)) + '</div></td>' +
                        '<td class="small text-nowrap">' + scEsc(scFormatDateTime(item.sec_in)) + '</td>' +
                        '<td class="small">' + scEsc(item.trolley) + '</td>' +
                        '<td class="small">' + scEsc(item.line) + '</td>' +
                    '</tr>';
                }).join('');

                $('#sc-table-body').html(html);
                $('#sc-table-wrap').removeClass('d-none');
            }
        }

        // ================= EXCELJS EXPORT =================
        async function scExportExcel() {
            if (scResults.length === 0) return;

            const workbook = new ExcelJS.Workbook();
            const worksheet = workbook.addWorksheet('Stocker Report');

            worksheet.columns = [
                { header: 'WS', key: 'ws' },
                { header: 'Style', key: 'styleno' },
                { header: 'Color', key: 'color' },
                { header: 'Size', key: 'size' },
                { header: 'Nama Part', key: 'nama_part' },
                { header: 'No Form', key: 'no_form' },
                { header: 'Waktu Selesai Form Cut', key: 'waktu_selesai_form_cut' },
                { header: 'Tgl DC IN', key: 'tgl_trans' },
                { header: 'QR Stocker', key: 'id_qr_stocker' },
                { header: 'Qty Awal DC IN', key: 'qty_awal' },
                { header: 'Reject DC IN', key: 'qty_reject' },
                { header: 'Replace DC IN', key: 'qty_replace' },
                { header: 'Qty In DC IN', key: 'qty_in' },
                { header: 'Tujuan', key: 'tujuan' },
                { header: 'Lokasi', key: 'lokasi' },
                { header: 'Kirim Sec Dalam', key: 'qty_sec_inhouse_in' },
                { header: 'Qty Sec OUT Inhouse Awal', key: 'qty_sec_inhouse_awal' },
                { header: 'Qty Sec OUT Inhouse Reject', key: 'qty_sec_inhouse_reject' },
                { header: 'Terima Rep Sec Dalam', key: 'qty_sec_inhouse_replace' },
                { header: 'Terima Good Sec Dalam', key: 'qty_sec_inhouse_out' },
                { header: 'Sec Inhouse In', key: 'sec_inhouse_in' },
                { header: 'Sec Inhouse Out', key: 'sec_inhouse_out' },
                { header: 'Qty Sec IN Awal', key: 'qty_sec_in_awal' },
                { header: 'Qty Sec IN Reject', key: 'qty_sec_in_reject' },
                { header: 'Terima Rep Sec IN', key: 'qty_sec_in_replace' },
                { header: 'Terima Good Sec IN', key: 'qty_sec_in_out' },
                { header: 'Sec In', key: 'sec_in' },
                { header: 'Trolley', key: 'trolley' },
                { header: 'Line', key: 'line' },
                { header: 'Nama Secondary', key: 'nama_secondary' }
            ];

            worksheet.getRow(1).font = { bold: true };
            worksheet.getRow(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFEAEAEA' } };

            scResults.forEach(item => {
                worksheet.addRow({
                    ws: item.ws || '-',
                    styleno: item.styleno || '-',
                    color: item.color || '-',
                    size: item.size || '-',
                    nama_part: item.nama_part || '-',
                    no_form: item.no_form || '-',
                    waktu_selesai_form_cut: scFormatDateTime(item.waktu_selesai_form_cut),
                    tgl_trans: scFormatDateOnly(item.tgl_trans),
                    id_qr_stocker: item.id_qr_stocker || '-',
                    qty_awal: Number(item.qty_awal || 0),
                    qty_reject: Number(item.qty_reject || 0),
                    qty_replace: Number(item.qty_replace || 0),
                    qty_in: Number(item.qty_in || 0),
                    tujuan: item.tujuan || '-',
                    lokasi: item.lokasi || '-',
                    qty_sec_inhouse_in: Number(item.qty_sec_inhouse_in || 0),
                    qty_sec_inhouse_out: Number(item.qty_sec_inhouse_out || 0),
                    qty_sec_inhouse_awal: Number(item.qty_sec_inhouse_awal || 0),
                    qty_sec_inhouse_reject: Number(item.qty_sec_inhouse_reject || 0),
                    qty_sec_inhouse_replace: Number(item.qty_sec_inhouse_replace || 0),
                    qty_sec_in_awal: Number(item.qty_sec_in_awal || 0),
                    qty_sec_in_reject: Number(item.qty_sec_in_reject || 0),
                    qty_sec_in_replace: Number(item.qty_sec_in_replace || 0),
                    qty_sec_in_out: Number(item.qty_sec_in_out || 0),
                    sec_inhouse_in: scFormatDateTime(item.sec_inhouse_in),
                    sec_inhouse_out: scFormatDateTime(item.sec_inhouse_out),
                    sec_in: scFormatDateTime(item.sec_in),
                    trolley: item.trolley || '-',
                    line: item.line || '-',
                    nama_secondary: item.nama_secondary || '-'
                });
            });

            worksheet.columns.forEach(column => {
                let maxLen = column.header ? column.header.toString().length : 0;
                column.eachCell({ includeEmpty: true }, cell => {
                    const len = cell.value ? cell.value.toString().length : 0;
                    if (len > maxLen) maxLen = len;
                });
                column.width = maxLen < 10 ? 12 : maxLen + 4;
            });

            const buffer = await workbook.xlsx.writeBuffer();
            const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const url = URL.createObjectURL(blob);

            const link = document.createElement('a');
            link.href = url;
            const safeName = scLastInput.replace(/[\/\\?%*:|"<>\s]/g, '_');
            link.setAttribute('download', 'Stocker_Report_' + scLastType + '_' + safeName + '.xlsx');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }
    </script>
@endsection
