@extends('layouts.index')

@section('custom-link')
    <style>
        /* Wadah scroll setinggi layar, header & kolom kiri tetap */
        #cmWrap {
            height: calc(100vh - 230px);
            min-height: 300px;
            overflow: auto;
            border: 1px solid #dee2e6;
            position: relative;
        }

        #cmTable {
            border-collapse: separate;
            border-spacing: 0;
            font-size: 12px;
            margin: 0;
            width: max-content;
            min-width: 100%;
        }

        #cmTable th,
        #cmTable td {
            padding: 4px 8px;
            border-right: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
            white-space: nowrap;
            vertical-align: middle;
            background-color: #fff;
        }

        #cmTable thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background-color: #b6d7f2;
            color: #000;
            text-align: center;
            white-space: normal;
            line-height: 1.2;
        }

        #cmTable tbody tr:hover td {
            filter: brightness(0.95);
        }

        /* Kolom kiri yang dikunci: lebar tetap supaya posisi left akurat */
        #cmTable .fx {
            position: sticky;
            z-index: 1;
            overflow: hidden;
        }

        #cmTable thead th.fx {
            z-index: 3;
        }

        #cmTable .fx-1 { left: 0;     width: 150px; min-width: 150px; max-width: 150px; }
        #cmTable .fx-2 { left: 150px; width: 110px; min-width: 110px; max-width: 110px; }
        #cmTable .fx-3 { left: 260px; width: 200px; min-width: 200px; max-width: 200px; }
        #cmTable .fx-4 { left: 460px; width: 200px; min-width: 200px; max-width: 200px; }
        #cmTable .fx-5 { left: 660px; width: 90px;  min-width: 90px;  max-width: 90px; }
        #cmTable .fx-6 { left: 750px; width: 80px;  min-width: 80px;  max-width: 80px; }

        #cmTable td.fx-6,
        #cmTable thead th.fx-6 {
            border-right: 2px solid #6c757d;
        }

        #cmTable td.wrap-col {
            white-space: normal;
            word-break: break-word;
        }

        #cmEmpty,
        #cmMore {
            padding: 12px 0;
            text-align: center;
            color: #6c757d;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Recap CM Price</h5>
        </div>

        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <small class="text-muted" id="cmInfo"></small>
                <div class="d-flex align-items-center">
                    <button type="button" id="cmExport" class="btn btn-outline-success btn-sm mr-2 text-nowrap">
                        <i class="fas fa-file-excel fa-sm"></i> Export Excel
                    </button>
                    <input type="text" id="cmSearch" class="form-control form-control-sm" style="width: 280px;"
                        placeholder="Cari Buyer / WS / Style..." autocomplete="off">
                </div>
            </div>

            <div id="cmWrap">
                <table id="cmTable">
                    <thead></thead>
                    <tbody></tbody>
                </table>
                <div id="cmEmpty" class="d-none">Data tidak ditemukan</div>
                <div id="cmMore" class="d-none"><i class="fas fa-spinner fa-spin"></i> Scroll untuk memuat lagi...</div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/export_excel_js/exceljs.min.js') }}"></script>
    <script>
        // Data dikirim sebagai JSON (lebih ringan dari HTML), baris dirender bertahap saat di-scroll
        const cmData = @json($groupedData);

        $(document).ready(function() {
            const colors = ['#fff3cd', '#d1ecf1', '#f8d7da', '#d4edda']; // kuning, biru, merah muda, hijau muda
            const chunk = 100;
            const maxDetails = cmData.reduce((m, ws) => Math.max(m, ws.details.length), 0);

            let filtered = cmData;
            let rendered = 0;

            function esc(val) {
                if (val === null || val === undefined || val === '') return '-';
                return String(val).replace(/[&<>"']/g, c => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
                }[c]));
            }

            // Sama dengan number_format($x, 2, '.', ',')
            function num(val) {
                return Number(val || 0).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            // Teks pencarian per WS
            cmData.forEach(ws => {
                ws._key = [ws.buyer, ws.kpno, ws.styleno, ws.styleno_prod].join(' ').toLowerCase();
            });

            let head = '<tr>' +
                '<th class="fx fx-1">Buyer</th>' +
                '<th class="fx fx-2">WS</th>' +
                '<th class="fx fx-3">Style</th>' +
                '<th class="fx fx-4">Style Production</th>' +
                '<th class="fx fx-5">Price</th>' +
                '<th class="fx fx-6">Number of Changes</th>';
            for (let i = 1; i <= maxDetails; i++) {
                head += `<th>Date of Update ${i}</th><th>Price ${i}</th>`;
            }
            $('#cmTable thead').html(head + '</tr>');

            function rowHtml(ws) {
                let tr = '<tr>' +
                    `<td class="fx fx-1 wrap-col">${esc(ws.buyer)}</td>` +
                    `<td class="fx fx-2">${esc(ws.kpno)}</td>` +
                    `<td class="fx fx-3 wrap-col">${esc(ws.styleno)}</td>` +
                    `<td class="fx fx-4 wrap-col">${esc(ws.styleno_prod)}</td>` +
                    `<td class="fx fx-5 text-right">${num(ws.price_act)}</td>` +
                    `<td class="fx fx-6 text-center">${esc(ws.total_changes)}</td>`;

                for (let i = 0; i < maxDetails; i++) {
                    const d = ws.details[i];
                    const hasValue = d && (d.tgl_upd_fix || d.price_act_upd);
                    const style = hasValue ? ` style="background-color: ${colors[i % colors.length]}"` : '';
                    tr += `<td class="text-center"${style}>${esc(d ? d.tgl_upd_fix : null)}</td>` +
                        `<td class="text-right"${style}>${d && d.price_act_upd !== null ? num(d.price_act_upd) : '-'}</td>`;
                }
                return tr + '</tr>';
            }

            function renderMore() {
                if (rendered >= filtered.length) return;
                const html = filtered.slice(rendered, rendered + chunk).map(rowHtml).join('');
                document.querySelector('#cmTable tbody').insertAdjacentHTML('beforeend', html);
                rendered = Math.min(rendered + chunk, filtered.length);
                updateInfo();
            }

            function updateInfo() {
                $('#cmInfo').text(`Menampilkan ${rendered} dari ${filtered.length} WS` +
                    (filtered.length !== cmData.length ? ` (total ${cmData.length})` : ''));
                $('#cmEmpty').toggleClass('d-none', filtered.length > 0);
                $('#cmMore').toggleClass('d-none', rendered >= filtered.length);
            }

            function reset() {
                document.querySelector('#cmTable tbody').innerHTML = '';
                document.getElementById('cmWrap').scrollTop = 0;
                rendered = 0;
                renderMore();
            }

            // Muat baris berikutnya saat scroll mendekati bawah
            $('#cmWrap').on('scroll', function() {
                if (this.scrollTop + this.clientHeight >= this.scrollHeight - 200) {
                    renderMore();
                }
            });

            let timer;
            $('#cmSearch').on('input', function() {
                clearTimeout(timer);
                const q = this.value.trim().toLowerCase();
                timer = setTimeout(() => {
                    filtered = q ? cmData.filter(ws => ws._key.includes(q)) : cmData;
                    reset();
                }, 200);
            });

            // Export sesuai hasil pencarian saat ini
            $('#cmExport').on('click', async function() {
                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin fa-sm"></i> Exporting...');

                try {
                    const wb = new ExcelJS.Workbook();
                    const ws = wb.addWorksheet('Recap CM Price', {
                        views: [{ state: 'frozen', xSplit: 6, ySplit: 1 }]
                    });

                    const header = ['Buyer', 'WS', 'Style', 'Style Production', 'Price', 'Number of Changes'];
                    for (let i = 1; i <= maxDetails; i++) {
                        header.push(`Date of Update ${i}`, `Price ${i}`);
                    }
                    ws.addRow(header);

                    ws.columns = header.map((h, i) => ({
                        width: [22, 16, 30, 30, 12, 12][i] || (i % 2 === 0 ? 20 : 12)
                    }));

                    const border = {
                        top: { style: 'thin' }, left: { style: 'thin' },
                        bottom: { style: 'thin' }, right: { style: 'thin' }
                    };
                    const fill = color => ({ type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF' + color.replace('#', '') } });

                    ws.getRow(1).eachCell(cell => {
                        cell.font = { bold: true };
                        cell.fill = fill('#b6d7f2');
                        cell.border = border;
                        cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
                    });

                    filtered.forEach(d => {
                        const values = [d.buyer, d.kpno, d.styleno, d.styleno_prod,
                            d.price_act === null ? null : Number(d.price_act), Number(d.total_changes)];
                        for (let i = 0; i < maxDetails; i++) {
                            const det = d.details[i];
                            values.push(det && det.tgl_upd_fix ? det.tgl_upd_fix : '-',
                                det && det.price_act_upd !== null ? Number(det.price_act_upd) : '-');
                        }
                        const row = ws.addRow(values);

                        row.eachCell({ includeEmpty: true }, (cell, col) => {
                            cell.border = border;
                            const center = col === 6 || (col > 6 && col % 2 === 1);
                            cell.alignment = {
                                vertical: 'middle',
                                wrapText: col <= 4,
                                horizontal: center ? 'center' : undefined
                            };
                            if (col === 5 || (col > 6 && col % 2 === 0)) cell.numFmt = '#,##0.00';

                            if (col > 6) {
                                const det = d.details[Math.floor((col - 7) / 2)];
                                if (det && (det.tgl_upd_fix || det.price_act_upd)) {
                                    cell.fill = fill(colors[Math.floor((col - 7) / 2) % colors.length]);
                                }
                            }
                        });
                    });

                    const buffer = await wb.xlsx.writeBuffer();
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(new Blob([buffer], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    }));
                    link.download = `Recap CM Price ${new Date().toISOString().slice(0, 10)}.xlsx`;
                    link.click();
                    setTimeout(() => URL.revokeObjectURL(link.href), 1000);
                } catch (e) {
                    console.error(e);
                    alert('Gagal export Excel');
                } finally {
                    btn.prop('disabled', false).html('<i class="fas fa-file-excel fa-sm"></i> Export Excel');
                }
            });

            reset();
        });
    </script>
@endsection
