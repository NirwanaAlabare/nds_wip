@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style type="text/css">
        .form-control {
            border: 1.5px solid #ced4da;
            border-radius: 8px;
            padding: 6px 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.25);
        }

        /* Toggle mode/aksi: tombol besar biar enak dipencet di layar HP */
        .tt-toggle-group .btn {
            padding: 10px;
            font-weight: 600;
        }

        .tt-box-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        #btnSubmit {
            padding: 12px;
            font-weight: 700;
            font-size: 15px;
        }

        .tt-stat-value {
            font-size: 26px;
            font-weight: 700;
        }

        .tt-stat-label {
            font-size: 12px;
            color: #6c757d;
        }

        #scannedTagList {
            max-height: 220px;
            overflow-y: auto;
            margin-top: 10px;
        }

        #scannedTagList:empty {
            display: none;
        }

        .scanned-tag-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 6px 10px;
            margin-bottom: 6px;
            font-size: 13px;
        }

        .scanned-tag-row .tag-no {
            color: #6c757d;
            margin-right: 8px;
        }

        .scanned-tag-row .tag-code {
            flex: 1;
            font-weight: 600;
            word-break: break-all;
        }

        @media (max-width: 575.98px) {
            .tt-toggle-group .btn {
                font-size: 13px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb mx-auto" style="max-width: 520px;">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-satellite-dish"></i> Transaksi Tab</h5>
        </div>
        <div class="card-body">
            <label class="d-block"><small><b>Pilih Mode :</b></small></label>
            <div class="btn-group btn-group-sm w-100 mb-2 tt-toggle-group" role="group">
                <input type="radio" class="btn-check" name="ttMode" id="btnModeSingle" autocomplete="off" checked>
                <label class="btn btn-outline-primary" for="btnModeSingle" onclick="setMode('single')">Single</label>
                <input type="radio" class="btn-check" name="ttMode" id="btnModeBulk" autocomplete="off">
                <label class="btn btn-outline-primary" for="btnModeBulk" onclick="setMode('bulk')">Bulk</label>
            </div>
            <div class="btn-group btn-group-sm w-100 mb-3 tt-toggle-group" role="group">
                <input type="radio" class="btn-check" name="ttAction" id="btnActionAmbil" autocomplete="off" checked>
                <label class="btn btn-outline-success" for="btnActionAmbil" onclick="setAction('ambil')">Ambil
                    barang</label>
                <input type="radio" class="btn-check" name="ttAction" id="btnActionKembalikan" autocomplete="off">
                <label class="btn btn-outline-warning" for="btnActionKembalikan"
                    onclick="setAction('kembalikan')">Kembalikan</label>
            </div>

            <div class="alert alert-info py-2 px-3 mb-3" id="ttBanner" role="alert"></div>

            <div class="mb-3" id="nikBox">
                <label for="txtnik"><small><b>Ketik NIK :</b></small></label>
                <input type="text" id="txtnik" class="form-control" placeholder="Masukkan NIK" autocomplete="off"
                    oninput="refreshSubmitState()">
            </div>

            <div class="mb-3">
                <label class="tt-box-title">
                    <small><b id="scanBoxTitle">Scan tag yang dibawa :</b></small>
                    <span class="badge bg-secondary" id="tagCountLabel">0 tag</span>
                </label>
                <input type="text" id="txtscan" class="form-control" placeholder="Tembak tag di sini"
                    autocomplete="off" onkeydown="handleScanEnter(event)">
                <button type="button" class="btn btn-outline-secondary btn-sm w-100 mt-2" onclick="simulateScan()">
                    <i class="fas fa-plus"></i> Simulasikan scan (demo)
                </button>
                <div id="scannedTagList"></div>
            </div>

            <div class="mb-3">
                <label for="cbotujuan"><small><b>Tujuan :</b></small></label>
                <select id="cbotujuan" class="form-control form-control-sm select2bs4" style="width: 100%;"
                    onchange="refreshSubmitState()">
                    <option value="">-- Pilih Tujuan --</option>
                    @foreach ($mainLokasiList as $row)
                        <option value="{{ $row->id }}">{{ $row->main_lokasi }}</option>
                    @endforeach
                </select>
            </div>

            <button type="button" class="btn btn-secondary w-100" id="btnSubmit" onclick="submitTransTab()" disabled></button>

            <div class="row mt-4 pt-3 border-top text-center" id="locationStats">
                <div class="col-6">
                    <div class="tt-stat-value">128</div>
                    <div class="tt-stat-label">Di ruangan</div>
                </div>
                <div class="col-6">
                    <div class="tt-stat-value">37</div>
                    <div class="tt-stat-label">Di lapangan</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: 'resolve'
        });
        $('.select2-container--bootstrap4 .select2-selection--single').css({
            'height': '30px',
            'font-size': '12px',
            'line-height': '30px'
        });

        let state = {
            mode: 'single', // single | bulk
            action: 'ambil', // ambil | kembalikan
            tags: [] // daftar kode tag yang sudah discan
        };
        let scanSeq = 0;

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        function setMode(mode) {
            state.mode = mode;

            $('#nikBox').toggleClass('d-none', mode === 'bulk');
            $('#scanBoxTitle').text(mode === 'single' ? 'Scan tag yang dibawa :' :
                'Sapu semua tag yang dibawa keluar :');

            updateBanner();
            refreshSubmitState();
        }

        function setAction(action) {
            state.action = action;
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

            $('#ttBanner').text(text)
                .toggleClass('alert-info', state.action === 'ambil')
                .toggleClass('alert-warning', state.action === 'kembalikan');
        }

        // Scanner RFID biasanya kirim karakter kode lalu diakhiri Enter otomatis (keyboard wedge),
        // jadi setiap Enter di input scan langsung dianggap 1 tag baru dan list-nya nambah ke bawah
        function handleScanEnter(e) {
            if (e.key !== 'Enter') return;

            e.preventDefault();

            let code = $('#txtscan').val().trim();
            $('#txtscan').val('');

            if (!code) return;

            addScannedTag(code);
        }

        function simulateScan() {
            scanSeq++;
            addScannedTag('DEMO-' + String(scanSeq).padStart(3, '0'));
        }

        function addScannedTag(code) {
            state.tags.push(code);
            renderScannedTagList();
            $('#txtscan').focus();
        }

        function removeScannedTag(index) {
            state.tags.splice(index, 1);
            renderScannedTagList();
        }

        function renderScannedTagList() {
            let $list = $('#scannedTagList').empty();

            state.tags.forEach((code, i) => {
                $list.append(`
                    <div class="scanned-tag-row">
                        <span class="tag-no">${i + 1}.</span>
                        <span class="tag-code">${code}</span>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0"
                            onclick="removeScannedTag(${i})">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                `);
            });

            $('#tagCountLabel').text(state.tags.length + ' tag');
            refreshSubmitState();
        }

        function refreshSubmitState() {
            let nik = $('#txtnik').val();
            let tujuan = $('#cbotujuan').val();
            let hasTag = state.tags.length >= 1;

            let requirements = [];
            if (state.mode === 'single' && !nik) requirements.push('Isi NIK');
            if (!hasTag) requirements.push('scan minimal 1 tag');
            if (!tujuan) requirements.push('pilih tujuan');

            let $btn = $('#btnSubmit');

            if (requirements.length) {
                $btn.prop('disabled', true).removeClass('btn-success').addClass('btn-secondary')
                    .text(requirements.join(' & ').replace(/^./, c => c.toUpperCase()));
            } else {
                let label = state.action === 'ambil' ? 'Proses Ambil Barang' : 'Proses Pengembalian';
                $btn.prop('disabled', false).removeClass('btn-secondary').addClass('btn-success').text(label);
            }
        }

        updateBanner();
        refreshSubmitState();

        function submitTransTab() {
            notif();
        }
    </script>
@endsection
