@extends('layouts.index')

@section('custom-link')
    <style type="text/css">
        .t2-container {
            --t2-muted: #6b7a90;
            --t2-line: #d9e3ef;
            color: #172033;
            font-size: 13px;
        }

        .t2-muted {
            color: var(--t2-muted);
        }

        .t2-card {
            background: #fff;
            border: 1px solid var(--t2-line);
            border-radius: 10px;
            padding: 17px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(29, 61, 101, 0.06);
        }

        /* Info PO */
        .t2-order {
            background: linear-gradient(110deg, #f4f8ff, #fff);
            border-color: #cbdcf5;
        }

        .t2-ordergrid {
            display: grid;
            grid-template-columns: 1.05fr 1fr 1.2fr 1.1fr 1.1fr .9fr;
        }

        .t2-oi {
            padding: 0 15px;
            border-right: 1px solid #dce5f0;
        }

        .t2-oi:first-child {
            padding-left: 0;
        }

        .t2-oi:last-child {
            border: 0;
        }

        .t2-lbl {
            font-size: 10px;
            font-weight: 800;
            color: #718096;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .t2-val {
            font-weight: 700;
            line-height: 1.35;
        }

        .t2-tag {
            display: inline-block;
            background: #e8f1ff;
            color: #2563eb;
            border-radius: 12px;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: 800;
            margin-top: 6px;
        }

        /* Layout atas: Tentukan Carton + Progress */
        .t2-top-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 300px;
            gap: 16px;
            margin-bottom: 16px;
            align-items: stretch;
        }

        .t2-top-layout .t2-card {
            margin-bottom: 0;
            height: 100%;
        }

        .t2-title {
            font-size: 15px;
            font-weight: 800;
            margin-bottom: 13px;
        }

        .t2-toolbar {
            display: grid;
            grid-template-columns: 110px 110px 120px 150px minmax(160px, 1fr);
            gap: 12px;
            align-items: end;
        }

        .t2-field label {
            display: block;
            font-size: 11px;
            font-weight: 800;
            color: #52637e;
            margin-bottom: 5px;
        }

        .t2-container .t2-input {
            height: 38px;
            border: 1px solid #cdd9e8;
            border-radius: 7px;
            padding: 0 10px;
            width: 100%;
            background: #fff;
            color: #253956;
        }

        .t2-container .t2-input:focus {
            outline: 0;
            border-color: #5b91ef;
            box-shadow: 0 0 0 3px #eaf2ff;
        }

        .t2-btn {
            height: 38px;
            border-radius: 7px;
            border: 1px solid #cad8ea;
            background: #fff;
            color: #2563eb;
            font-weight: 800;
            padding: 0 13px;
            cursor: pointer;
        }

        .t2-btn.primary {
            background: #2563eb;
            color: #fff;
            border-color: #2563eb;
        }

        .t2-btn.soft {
            background: #eef5ff;
            border-color: #d2e1fb;
        }

        .t2-btn.danger {
            color: #d83c43;
            background: #fff5f5;
            border-color: #ffd9dc;
        }

        .t2-help {
            font-size: 13px;
            color: #7c8ba1;
            margin-top: 8px;
        }

        .t2-chips {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .t2-chip {
            background: #e8f1ff;
            color: #2563eb;
            border-radius: 14px;
            padding: 5px 8px;
            font-size: 10px;
            font-weight: 800;
        }

        /* Progress */
        .t2-sidecard .t2-stat {
            display: flex;
            justify-content: space-between;
            padding: 7px 0;
        }

        .t2-miniProgress {
            height: 9px;
            background: #e9eef5;
            border-radius: 10px;
            overflow: hidden;
            margin: 8px 0 14px;
        }

        .t2-miniBar {
            height: 100%;
            width: 0;
            background: #20ae74;
            transition: width .3s;
        }

        /* 2. Isi Packing per SKU */
        .t2-info {
            font-size: 11px;
            line-height: 1.5;
            color: #64748b;
            background: #f7f9fc;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 12px;
        }

        .t2-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin: 14px 0 9px;
        }

        .t2-tab {
            border: 1px solid #d4dfec;
            background: #fff;
            padding: 7px 11px;
            border-radius: 7px;
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
        }

        .t2-tab.active {
            background: #eaf2ff;
            color: #2563eb;
            border-color: #c7dcfb;
        }

        .t2-tablewrap {
            border: 1px solid #dce5ef;
            border-radius: 8px;
            overflow: auto;
        }

        .t2-matrix {
            width: 100%;
            border-collapse: collapse;
        }

        .t2-matrix th {
            background: #f3f7fc;
            color: #304666;
            text-align: left;
            font-size: 10px;
            padding: 9px;
            border-bottom: 1px solid #dce5ef;
            white-space: nowrap;
        }

        .t2-matrix td {
            padding: 7px 8px;
            border-bottom: 1px solid #edf1f5;
            font-size: 11px;
            vertical-align: middle;
        }

        .t2-matrix tr:last-child td {
            border: 0;
        }

        .t2-auto {
            background: #f8fbff;
        }

        .t2-color-badge {
            display: inline-block;
            background: #e6f8ee;
            color: #188057;
            border-radius: 12px;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: 700;
        }

        .t2-ws {
            font-weight: 800;
            color: #40587c;
        }

        .t2-sizeMatrix .t2-sizeHead {
            background: #eaf2ff;
            color: #245da9;
            font-size: 10px;
            padding: 8px 3px;
            text-align: center;
            min-width: 125px;
        }

        .t2-sizeCell {
            text-align: center;
            background: #fbfdff;
            vertical-align: top !important;
            padding: 6px 4px !important;
        }

        .t2-sizeInfo {
            font-size: 12px;
            line-height: 1.45;
            text-align: left;
            margin-bottom: 4px;
        }

        .t2-miniLabel {
            display: inline-block;
            width: 36px;
            color: #8090a5;
            font-size: 12px;
        }

        .t2-container .t2-qtyInput {
            width: 100%;
            height: 30px;
            padding: 0 5px;
            font-size: 12px;
            border: 1px solid #cdd9e8;
            border-radius: 7px;
        }

        .t2-container .t2-qtyInput:disabled {
            background: #eef1f5;
            color: #a1adbd;
            cursor: not-allowed;
        }

        .t2-container .t2-over {
            border-color: #e5484d;
            background: #fff3f3;
        }

        .t2-barcode {
            font-size: 11px;
            color: #8290a4;
            margin-top: 3px;
        }

        .t2-sizeMatrix td:first-child,
        .t2-sizeMatrix th:first-child {
            position: sticky;
            left: 0;
            background: #fff;
            z-index: 2;
            min-width: 100px;
        }

        .t2-sizeMatrix th:first-child {
            background: #f3f7fc;
        }

        .t2-footerActions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 13px;
        }

        .t2-total b {
            font-size: 17px;
            margin-left: 6px;
        }

        /* 3. Hasil Packing List */
        .t2-savedTable {
            width: 100%;
            border-collapse: collapse;
        }

        .t2-savedTable th {
            background: #f3f7fc;
            text-align: left;
            padding: 9px;
            font-size: 10px;
        }

        .t2-savedTable td {
            padding: 8px;
            border-bottom: 1px solid #edf1f5;
            font-size: 11px;
        }

        /* Modal Preview */
        .t2-cartonPreviewGrid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 16px;
            margin-top: 14px;
        }

        .t2-cartonCard {
            border: 1px solid #d8e2ee;
            border-radius: 12px;
            background: #fff;
            padding: 12px;
            box-shadow: 0 2px 8px rgba(29, 61, 101, 0.06);
        }

        .t2-cartonHead {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .t2-cartonNo {
            font-size: 14px;
            font-weight: 800;
            color: #172b4d;
        }

        .t2-cartonTotal {
            font-size: 10px;
            font-weight: 800;
            color: #2563eb;
            background: #edf4ff;
            padding: 5px 8px;
            border-radius: 12px;
        }

        .t2-cartonBox {
            position: relative;
            background: #d9a86c;
            border: 3px solid #b98245;
            border-radius: 7px;
            padding: 17px 10px 11px;
            min-height: 145px;
            box-shadow: inset 0 0 0 3px #e7bd83;
        }

        .t2-cartonBox::before {
            content: "";
            position: absolute;
            left: 10%;
            right: 10%;
            top: 7px;
            height: 12px;
            border: 2px solid #b98245;
            border-bottom: 0;
            border-radius: 5px 5px 0 0;
            background: #e8bd80;
        }

        .t2-cartonContents {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 7px;
            position: relative;
            z-index: 1;
        }

        .t2-itemSymbol {
            background: #fff8e9;
            border: 1px solid #c99450;
            border-radius: 7px;
            padding: 7px 3px 5px;
            text-align: center;
            min-height: 65px;
            min-width: 0;
        }

        .t2-itemIcon {
            font-size: 20px;
            line-height: 20px;
        }

        .t2-itemSize {
            font-size: 10px;
            font-weight: 800;
            color: #172b4d;
            margin-top: 3px;
        }

        .t2-itemQty {
            font-size: 10px;
            font-weight: 800;
            color: #188057;
        }

        .t2-itemMeta {
            font-size: 8px;
            line-height: 1.3;
            color: #64748b;
            overflow-wrap: anywhere;
            margin-top: 2px;
        }

        .t2-cartonStyle {
            font-size: 10px;
            color: #66758b;
            margin: 8px 0 0;
            line-height: 1.35;
        }

        .t2-cartonStyle b {
            color: #314767;
        }

        .t2-previewSummary {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin: 8px 0 14px;
        }

        .t2-summaryPill {
            background: #f2f6fb;
            border: 1px solid #dbe5ef;
            border-radius: 8px;
            padding: 7px 10px;
            font-size: 10px;
            color: #52637e;
        }

        .t2-summaryPill b {
            color: #172b4d;
        }

        @media (max-width: 1050px) {
            .t2-top-layout {
                grid-template-columns: 1fr;
            }

            .t2-ordergrid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .t2-oi {
                border: 0;
                padding: 0;
            }
        }

        @media (max-width: 700px) {
            .t2-ordergrid {
                grid-template-columns: 1fr;
            }

            .t2-toolbar {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header bg-sb d-flex align-items-center">
            <h5 class="card-title fw-bold mb-0">
                <i class="fas fa-box-open"></i> Packing List — Tambah
            </h5>
            <a class="btn btn-light btn-sm ms-auto" style="margin-block: -.5rem;" href="{{ route('packing-list-karton') }}">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="t2-container">
        {{-- Info PO --}}
        <div class="t2-card t2-order">
            <div class="t2-ordergrid">
                <div class="t2-oi">
                    <div class="t2-lbl">No. PO</div>
                    <div class="t2-val">{{ $po }}</div>
                </div>
                <div class="t2-oi">
                    <div class="t2-lbl">Tgl. Shipment</div>
                    <div class="t2-val" id="poShipment">-</div>
                </div>
                <div class="t2-oi">
                    <div class="t2-lbl">Buyer</div>
                    <div class="t2-val" id="poBuyer">-</div>
                </div>
                <div class="t2-oi">
                    <div class="t2-lbl">Worksheet</div>
                    <div class="t2-val" id="poWorksheet">-</div>                
                </div>
                <div class="t2-oi">
                    <div class="t2-lbl">Style</div>
                    <div class="t2-val" id="poStyle">-</div>
                </div>
                <div class="t2-oi">
                    <div class="t2-lbl">Dest</div>
                    <div class="t2-val" id="poDest">-</div>
                </div>
            </div>
        </div>

        {{-- 1. Tentukan Carton + Progress Packing --}}
        <div class="t2-top-layout">
            <div class="t2-card">
                <div class="t2-title">1. Tentukan Carton</div>
                <div class="t2-toolbar">
                    <div class="t2-field">
                        <label>Carton Mulai <span class="text-danger">*</span></label>
                        <input class="t2-input" id="start" type="number" min="1" step="1" value="1" placeholder="Contoh: 1" onkeydown="onlyInteger(event)">
                    </div>
                    <div class="t2-field">
                        <label>Carton Sampai <span class="text-danger">*</span></label>
                        <input class="t2-input" id="end" type="number" min="1" step="1" value="5" placeholder="Contoh: 5" onkeydown="onlyInteger(event)">
                    </div>
                    <button type="button" class="t2-btn soft" onclick="setRange()">Pilih Carton</button>
                    <div class="t2-field">
                        <label>Tipe Packing <span class="text-danger">*</span></label>
                        <input class="t2-input" id="tipePack" type="text" maxlength="255" placeholder="Contoh: SOLID">
                    </div>
                    <div class="t2-field">
                        <label>Keterangan</label>
                        <input class="t2-input" id="keterangan" type="text" maxlength="255" placeholder="Opsional">
                    </div>
                </div>
                <div class="t2-help">Bisa memilih banyak carton sekaligus. Data qty yang diinput akan diterapkan ke seluruh carton yang dipilih.</div>
                <div class="t2-help" id="usedCartons"></div>
                <div class="t2-chips" id="chips"></div>
            </div>

            <div class="t2-card t2-sidecard">
                <div class="t2-title">Progress Packing</div>
                <div class="t2-stat"><span>Total Qty PO</span><b id="totalPoVal">0 pcs</b></div>
                <div class="t2-stat"><span>Sudah Packing</span><b id="packed">0 pcs</b></div>
                <div class="t2-stat"><span>Balance</span><b id="balance">0 pcs</b></div>
                <div class="t2-miniProgress">
                    <div class="t2-miniBar" id="miniBar"></div>
                </div>
                <div class="t2-stat"><span>Progress</span><b id="pct">0%</b></div>
            </div>
        </div>

        {{-- 2. Isi Packing per Barcode --}}
        <div class="t2-card">
            <div class="t2-title">2. Isi Packing per Barcode</div>
            <div class="t2-info">
                Semua detail dari Sales Order sudah ditampilkan otomatis. <b>Semua size ditampilkan horizontal</b>.
                User cukup isi Qty Packing pada size yang masuk ke carton.
            </div>
            <div class="t2-tabs" id="wsTabs"></div>
            <div class="t2-tablewrap">
                <table class="t2-matrix t2-sizeMatrix">
                    <thead id="masterHead"></thead>
                    <tbody id="masterRows"></tbody>
                </table>
            </div>
            <div class="t2-footerActions">
                <div class="t2-total">Total Qty per Carton <b id="cartonTotal">0</b></div>
                <button type="button" class="t2-btn primary" onclick="saveSelected()">
                    <i class="fas fa-save"></i> Simpan ke Carton Terpilih
                </button>
            </div>
        </div>

        {{-- 3. Hasil Packing List --}}
        <div class="t2-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="t2-title mb-0">3. Hasil Packing List</div>
                <div class="d-flex gap-2 align-items-center">
                    <div class="t2-summaryPill" style="font-size:13px">Jumlah Carton <b id="savedCartonCount">0</b></div>
                    <div class="t2-summaryPill" style="font-size:13px">Total Qty <b id="savedQtyTotal">0 pcs</b></div>
                    <button type="button" class="t2-btn soft" onclick="preview()">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                    <button type="button" class="t2-btn danger" onclick="clearPacking()">Hapus Semua</button>
                    <button type="button" class="t2-btn primary" onclick="storePacking()">
                        <i class="fas fa-database"></i> Simpan Packing List
                    </button>
                </div>
            </div>
            <div id="saved"></div>
        </div>
    </div>

    {{-- Modal Preview Packing List --}}
    <div class="modal fade" id="modalPreviewPacking" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h3 class="modal-title fs-5"><i class="fas fa-box-open"></i> Preview Packing List</h3>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body t2-container" id="previewBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script>
        $('#tipePack, #keterangan').on('input', function() {
            const start = this.selectionStart;
            const end = this.selectionEnd;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(start, end);
        });

        // Detail SKU PO terpilih (dari ppic_master_so + master_sb_ws)
        let master = [];
        let poInfo = {
            worksheets: []
        };

        let selected = [];
        let packing = {};
        let filter = "ALL";
        let inputQty = {};
        let activePo = null;

        // po & dest terkunci, carton yang sudah ada tidak bisa dipilih lagi
        const poRepeat = @json($po);
        const destRepeat = @json($dest);
        let usedCartons = [];

        function toast(msg, type = 'info') {
            iziToast[type]({
                message: msg,
                position: 'topCenter'
            });
        }

        function fmt(n) {
            return Number(n).toLocaleString("id-ID");
        }

        function escHtml(v) {
            return $('<div>').text(v ?? '').html();
        }

        function key(x) {
            return String(x.id);
        }

        function currentMaster() {
            return master;
        }

        function totalPo() {
            return currentMaster().reduce((a, x) => a + Number(x.po), 0);
        }

        function uniq(arr) {
            return [...new Set(arr.filter(v => v !== null && v !== ''))];
        }

        function resetPoInfo() {
            $('#poShipment, #poBuyer, #poWorksheet, #poStyle, #poDest').text('-');
        }

        function selectPO(po) {
            if (!po) return;

            activePo = po;
            master = [];
            poInfo = {
                worksheets: []
            };
            filter = "ALL";
            inputQty = {};
            packing = {};
            resetPoInfo();
            renderTabs();
            renderAll();

            $.ajax({
                url: '{{ route('get-po-detail-packing-list-karton') }}',
                method: 'GET',
                data: {
                    po: po,
                    dest: destRepeat
                },
                beforeSend: function() {
                    showLoading();
                },
                success: function(rows) {
                    master = rows.map(r => ({
                        id: r.id_ppic_master_so,
                        id_so_det: r.id_so_det,
                        dest: r.dest,
                        ws: r.ws,
                        style: r.styleno,
                        color: r.color,
                        size: r.size,
                        urutan: r.urutan == null ? 9999 : +r.urutan,
                        sku: r.barcode,
                        po: parseInt(r.qty_po) || 0,
                        existing: parseInt(r.qty_packing) || 0
                    }));

                    const worksheets = uniq(rows.map(r => r.ws));
                    const styles = uniq(rows.map(r => r.styleno));
                    const buyers = uniq(rows.map(r => r.buyer));
                    poInfo = {
                        worksheets
                    };

                    $('#poShipment').text(uniq(rows.map(r => r.tgl_shipment)).join(', ') || '-');
                    $('#poBuyer').text(buyers.join(', ') || '-');
                    $('#poWorksheet').html(worksheets.map(escHtml).join('<br>') || '-');
                    $('#poStyle').html(styles.map(escHtml).join('<br>') || '-');
                    $('#poDest').html(uniq(rows.map(r => r.dest)).map(escHtml).join('<br>') || '-');

                    renderTabs();
                    renderAll();
                },
                error: function() {
                    toast('Gagal memuat data PO', 'error');
                },
                complete: function() {
                    hideLoading();
                }
            });

            loadUsedCartons();
        }

        function loadUsedCartons() {
            $.ajax({
                url: '{{ route('get-used-carton-packing-list-karton') }}',
                method: 'GET',
                data: {
                    po: poRepeat,
                    dest: destRepeat
                },
                success: function(rows) {
                    usedCartons = rows.map(Number).filter(c => !isNaN(c)).sort((a, b) => a - b);
                    selected = selected.filter(c => !usedCartons.includes(c));

                    // carton berikutnya yang masih kosong
                    const next = usedCartons.length ? usedCartons[usedCartons.length - 1] + 1 : 1;
                    $('#start, #end').val(next);
                    renderUsedCartons();
                    renderChips();
                },
                error: function() {
                    toast('Gagal memuat data carton', 'error');
                }
            });
        }

        // carton yang sudah ada di DB + yang baru masuk Hasil Packing List
        function allUsedCartons() {
            return [...new Set([...usedCartons, ...Object.keys(packing).map(Number)])].sort((a, b) => a - b);
        }

        function renderUsedCartons() {
            $('#usedCartons').html(`Carton yang sudah ada (tidak bisa diinput lagi): <b>${cartonRanges(allUsedCartons()) || '-'}</b>`);
        }

        // [1, 2, 3, 7] => "1–3, 7"
        function cartonRanges(list) {
            const ranges = [];
            list.forEach(c => {
                const last = ranges[ranges.length - 1];
                if (last && last[1] === c - 1) last[1] = c;
                else ranges.push([c, c]);
            });
            return ranges.map(([a, b]) => a === b ? a : `${a}–${b}`).join(', ');
        }

        // no carton & qty hanya bilangan bulat, type number masih menerima . , e - +
        function onlyInteger(e) {
            if (['.', ',', 'e', 'E', '-', '+'].includes(e.key)) e.preventDefault();
        }

        function setRange() {
            if (!$('#start').val()) return toast("Isi Carton Mulai terlebih dahulu", 'warning');
            let a = Math.max(1, +$('#start').val() || 1);
            let b = +$('#end').val() || a;
            if (!Number.isInteger(a) || !Number.isInteger(b)) return toast("No carton tidak boleh desimal", 'warning');
            if (b < a) return toast("Carton Sampai tidak boleh lebih kecil dari Carton Mulai", 'warning');
            const bentrok = allUsedCartons().filter(c => c >= a && c <= b);
            if (bentrok.length) return toast(`Carton ${cartonRanges(bentrok)} sudah ada di packing list`, 'warning');
            selected = [];
            for (let i = a; i <= b; i++) selected.push(i);
            renderAll();
            toast(`Carton ${a}–${b} dipilih`, 'success');
        }

        function renderAll() {
            renderUsedCartons();
            renderChips();
            renderMaster();
            renderSaved();
            updateSummary();
        }

        function renderChips() {
            $('#chips').html(selected.length ?
                selected.map(c => `<span class="t2-chip">Carton ${c}</span>`).join("") :
                '<span class="t2-muted">Belum ada carton.</span>');
        }

        function renderTabs() {
            const ws = poInfo.worksheets;
            let html = `<button type="button" class="t2-tab ${filter === 'ALL' ? 'active' : ''}" onclick="filterWs('ALL')">Semua Worksheet</button>`;
            html += ws.map(w =>
                `<button type="button" class="t2-tab ${filter === w ? 'active' : ''}" onclick="filterWs('${w}')">${escHtml(w)}</button>`
            ).join("");
            $('#wsTabs').html(html);
        }

        function filterWs(ws) {
            filter = ws;
            renderTabs();
            renderMaster();
        }

        // qty sudah packing = yang sudah tersimpan di DB + yang baru diisi di form
        function packedFor(x) {
            return (x.existing || 0) + Object.values(packing).flat()
                .filter(r => r.id === x.id)
                .reduce((a, r) => a + Number(r.qty), 0);
        }

        function renderMaster() {
            const list = currentMaster();
            const arr = filter === "ALL" ? list : list.filter(x => x.ws === filter);
            const groups = [];
            const seen = new Set();
            arr.forEach(x => {
                const g = x.ws + "|" + x.style + "|" + x.color + "|" + x.dest;
                if (!seen.has(g)) {
                    seen.add(g);
                    groups.push({
                        ws: x.ws,
                        style: x.style,
                        color: x.color,
                        dest: x.dest
                    });
                }
            });
            const sizes = [...new Set(list.map(x => x.size))];

            $('#masterHead').html(`<tr>
                <th>Worksheet</th>
                <th style="min-width:150px">Style</th>
                <th style="min-width:115px">Color</th>
                <th style="min-width:100px">Dest</th>
                ${sizes.map(s => `<th class="t2-sizeHead">${s}</th>`).join("")}
            </tr>`);

            if (!groups.length) {
                $('#masterRows').html(
                    `<tr><td colspan="${4 + sizes.length}" class="text-center t2-muted py-3">Pilih PO terlebih dahulu.</td></tr>`
                );
            } else {
                $('#masterRows').html(groups.map(g => `<tr>
                    <td class="t2-ws">${g.ws}</td>
                    <td class="t2-auto">${g.style}</td>
                    <td class="t2-auto"><span class="t2-color-badge">${g.color}</span></td>
                    <td class="t2-auto">${g.dest ?? '-'}</td>
                    ${sizes.map(size => {
                        const x = list.find(m => m.ws === g.ws && m.style === g.style && m.color === g.color && m.dest === g.dest && m.size === size);
                        if (!x) return `<td class="t2-auto text-center" style="color:#a1adbd">—</td>`;
                        const k = key(x),
                            done = packedFor(x),
                            bal = Math.max(0, x.po - done),
                            val = inputQty[k] ?? "";
                        return `<td class="t2-sizeCell">
                            <div class="t2-sizeInfo">
                                <div><span class="t2-miniLabel">PO</span> <b>${fmt(x.po)}</b></div>
                                <div><span class="t2-miniLabel">Pack</span> ${fmt(done)}</div>
                                <div style="color:#000"><span class="t2-miniLabel">Bal</span> ${fmt(bal)}</div>
                            </div>
                            ${x.sku
                                ? `<input class="t2-qtyInput ${+val > bal ? 't2-over' : ''}" type="number" min="0" step="1" max="${bal}" onkeydown="onlyInteger(event)"
                                    value="${val}" placeholder="Qty" onchange="setQty('${k}', this.value, this)">`
                                : `<input class="t2-qtyInput" type="number" placeholder="Qty" disabled>`}
                            <div class="t2-barcode">${x.sku ? `Barcode ${escHtml(x.sku)}` : 'Barcode Kosong'}</div>
                        </td>`;
                    }).join("")}
                </tr>`).join(""));
            }

            $('#cartonTotal').text(fmt(Object.values(inputQty).reduce((a, b) => a + (+b || 0), 0)));
        }

        // tidak render ulang tabel, supaya klik ke input lain tidak hilang fokusnya
        function setQty(k, v, el) {
            const x = currentMaster().find(m => key(m) === k);
            if (!x) return;
            const bal = x.po - packedFor(x);
            const n = Math.max(0, +v || 0);
            if (!Number.isInteger(n)) {
                $(el).val(inputQty[k] ?? '');
                return toast("Qty tidak boleh desimal", 'warning');
            }
            inputQty[k] = n;
            $(el).toggleClass('t2-over', n > bal);
            $('#cartonTotal').text(fmt(Object.values(inputQty).reduce((a, b) => a + (+b || 0), 0)));
            if (n > bal) toast(`Qty ${x.sku} melebihi balance PO (${fmt(bal)})`, 'warning');
        }

        function saveSelected() {
            if (!selected.length) return toast("Pilih carton terlebih dahulu", 'warning');
            const entries = Object.entries(inputQty).filter(([k, v]) => +v > 0);
            if (!entries.length) return toast("Isi minimal satu Qty Packing", 'warning');

            if (!$('#tipePack').val().trim()) {
                $('#tipePack').focus();
                return toast("Tipe Packing wajib diisi", 'warning');
            }

            const list = currentMaster();
            for (const [k, v] of entries) {
                const x = list.find(m => key(m) === k);
                const bal = x.po - packedFor(x);
                if (+v * selected.length > bal) {
                    return toast(`Qty ${x.ws} / ${x.sku}: ${fmt(v)} x ${selected.length} carton melebihi balance (${fmt(bal)})`, 'warning');
                }
            }

            const keterangan = $('#keterangan').val().trim();
            const tipe_pack = $('#tipePack').val().trim().toUpperCase();
            const carton_awal = Math.min(...selected);
            const carton_akhir = Math.max(...selected);
            const jumlahCarton = selected.length;
            selected.forEach(c => {
                packing[c] = entries.map(([k, v]) => ({
                    ...list.find(m => key(m) === k),
                    qty: +v,
                    tipe_pack,
                    keterangan,
                    carton_awal,
                    carton_akhir
                }));
            });
            // carton yang sudah masuk hasil tidak boleh terpilih lagi
            selected = [];
            inputQty = {};
            $('#start, #end, #keterangan, #tipePack').val('');
            renderAll();
            toast(`Komposisi disimpan ke ${jumlahCarton} carton`, 'success');
        }

        // urutan isi carton: ws, color, master_size_new.urutan
        function byWsColorSize(a, b) {
            return String(a.ws).localeCompare(String(b.ws)) ||
                String(a.color).localeCompare(String(b.color)) ||
                a.urutan - b.urutan;
        }

        function renderSaved() {
            const all = [];
            Object.keys(packing).sort((a, b) => a - b)
                .forEach(c => [...packing[c]].sort(byWsColorSize).forEach(r => all.push({
                    carton: c,
                    ...r
                })));

            $('#savedCartonCount').text(fmt(Object.keys(packing).length));
            $('#savedQtyTotal').text(fmt(all.reduce((a, r) => a + r.qty, 0)) + " pcs");

            if (!all.length) {
                $('#saved').html('<div class="t2-muted text-center" style="padding:25px">Belum ada carton yang disimpan.</div>');
                return;
            }

            $('#saved').html(`<div class="t2-tablewrap"><table class="t2-savedTable">
                <thead><tr><th>Carton</th><th>WS</th><th>Style</th><th>Color</th><th>Dest</th><th>Size</th><th>Barcode</th><th>Qty</th><th>Tipe Packing</th><th>Keterangan</th><th>Aksi</th></tr></thead>
                <tbody>
                ${all.map(r => `<tr>
                    <td><b>Carton ${r.carton}</b></td>
                    <td>${r.ws}</td>
                    <td>${r.style}</td>
                    <td>${r.color}</td>
                    <td>${r.dest ?? '-'}</td>
                    <td>${r.size}</td>
                    <td>${r.sku ? escHtml(r.sku) : 'Kosong'}</td>
                    <td><b>${fmt(r.qty)}</b></td>
                    <td>${r.tipe_pack ? escHtml(r.tipe_pack) : '-'}</td>
                    <td>${escHtml(r.keterangan)}</td>
                    <td><button type="button" class="t2-btn danger" style="height:29px;padding:0 9px" onclick="deleteCarton(${r.carton})">Hapus</button></td>
                </tr>`).join("")}
                </tbody></table></div>`);
        }

        function deleteCarton(c) {
            delete packing[c];
            renderAll();
            toast(`Carton ${c} dihapus`, 'success');
        }

        function updateSummary() {
            // termasuk qty yang sudah tersimpan di DB
            const packed = currentMaster().reduce((a, x) => a + packedFor(x), 0);
            const po = totalPo();
            const bal = Math.max(0, po - packed);
            const pct = po ? (packed / po) * 100 : 0;

            $('#totalPoVal').text(fmt(po) + " pcs");
            $('#packed').text(fmt(packed) + " pcs");
            $('#balance').text(fmt(bal) + " pcs");
            $('#pct').text(pct.toFixed(1) + "%");
            $('#miniBar').css('width', Math.min(100, pct) + "%");
        }

        function clearPacking() {
            if (!Object.keys(packing).length) return;
            Swal.fire({
                title: 'Hapus semua hasil packing?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
            }).then(result => {
                if (!result.isConfirmed) return;
                packing = {};
                renderAll();
                toast("Semua packing dihapus", 'success');
            });
        }

        function storePacking() {
            const cartons = Object.keys(packing).sort((a, b) => a - b);
            if (!activePo) return toast("Pilih PO terlebih dahulu", 'warning');
            if (!cartons.length) return toast("Belum ada data packing", 'warning');

            const rows = cartons.flatMap(c => packing[c].map(r => ({
                carton: +c,
                carton_awal: r.carton_awal,
                carton_akhir: r.carton_akhir,
                id: r.id,
                qty: r.qty,
                tipe_pack: r.tipe_pack,
                keterangan: r.keterangan || null
            })));

            Swal.fire({
                title: 'Simpan packing list?',
                html: `PO <b>${escHtml(activePo)}</b><br>${cartons.length} carton akan disimpan`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, simpan',
                cancelButtonText: 'Batal',
            }).then(result => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: '{{ route('store-packing-list-karton') }}',
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        po: activePo,
                        rows: rows
                    },
                    beforeSend: function() {
                        showLoading();
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message
                        });
                        selectPO(activePo);
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: xhr.responseJSON?.message || 'Gagal menyimpan packing list'
                        });
                    },
                    complete: function() {
                        hideLoading();
                    }
                });
            });
        }

        function preview() {
            const cartons = Object.keys(packing).sort((a, b) => a - b);
            if (!cartons.length) return toast("Belum ada data packing", 'warning');

            let grandTotal = 0;
            const cards = cartons.map(c => {
                const rows = [...(packing[c] || [])].sort(byWsColorSize);
                const total = rows.reduce((a, r) => a + r.qty, 0);
                grandTotal += total;

                const items = rows.map(r => `
                    <div class="t2-itemSymbol" title="${r.style} • ${r.ws}">
                        <div class="t2-itemIcon"><i class="fas fa-tshirt"></i></div>
                        <div class="t2-itemSize">${r.size}</div>
                        <div class="t2-itemQty">${fmt(r.qty)} pcs</div>
                        <div class="t2-itemMeta">${r.sku ? escHtml(r.sku) : 'Barcode Kosong'}<br>${escHtml(r.ws)}</div>
                    </div>
                `).join("");

                const styles = [...new Set(rows.map(r => r.style))];
                return `
                    <div class="t2-cartonCard">
                        <div class="t2-cartonHead">
                            <div class="t2-cartonNo">Carton ${c}</div>
                            <div class="t2-cartonTotal">${fmt(total)} pcs</div>
                        </div>
                        <div class="t2-cartonBox">
                            <div class="t2-cartonContents">${items}</div>
                        </div>
                        <div class="t2-cartonStyle">${styles.map(s => `<div><b>${s}</b></div>`).join("")}</div>
                        ${rows[0]?.tipe_pack ? `<div class="t2-muted" style="font-size:11px">Tipe: ${escHtml(rows[0].tipe_pack)}</div>` : ''}
                        ${rows[0]?.keterangan ? `<div class="t2-muted" style="font-size:11px">Ket: ${escHtml(rows[0].keterangan)}</div>` : ''}
                    </div>
                `;
            }).join("");

            $('#previewBody').html(`
                <div class="t2-muted mb-1">PO <b>${activePo}</b></div>
                <div class="t2-previewSummary">
                    <div class="t2-summaryPill">Jumlah Carton <b>${cartons.length}</b></div>
                    <div class="t2-summaryPill">Total Isi <b>${fmt(grandTotal)} pcs</b></div>
                    <div class="t2-summaryPill">Visualisasi Isi Carton</div>
                </div>
                <div class="t2-cartonPreviewGrid">${cards}</div>
            `);
            $('#modalPreviewPacking').modal('show');
        }

        // Init
        renderTabs();
        renderAll();
        selectPO(poRepeat);
    </script>
@endsection
