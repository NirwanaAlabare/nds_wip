@extends('layouts.index')

@section('custom-link')
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

        /* 16px menahan Safari/Chrome HP me-zoom halaman saat input di-tap */
        #txtqr {
            font-weight: 600;
            letter-spacing: .5px;
            font-size: 16px;
        }

        #reader {
            width: 100%;
            max-width: 420px;
            border-radius: 10px;
            overflow: hidden;
        }

        .mode-group .btn {
            padding-top: .5rem;
            padding-bottom: .5rem;
        }

        .info-tile {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: .6rem .75rem;
            height: 100%;
        }

        .saldo-panel {
            border: 1px solid #cfe2ff;
            background: #f4f8ff;
            border-radius: 10px;
            padding: .75rem 1rem;
            height: 100%;
        }

        .saldo-box {
            font-size: 42px;
            font-weight: 700;
            line-height: 1;
        }

        /* --- Daftar isi carton versi HP: tabel 9 kolom diganti kartu per item --- */
        .item-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: .65rem .75rem;
            margin-bottom: .5rem;
        }

        .item-card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .5rem;
            border-bottom: 1px dashed #e9ecef;
            padding-bottom: .4rem;
            margin-bottom: .4rem;
        }

        .item-card-ws {
            font-weight: 700;
            word-break: break-word;
        }

        .item-card-qty {
            white-space: nowrap;
            font-weight: 700;
            font-size: 15px;
        }

        .item-card-tags {
            display: flex;
            flex-wrap: wrap;
            gap: .3rem;
            margin-bottom: .45rem;
        }

        .item-card-tag {
            background: #eef1f6;
            color: #35405a;
            border-radius: 999px;
            padding: .15rem .55rem;
            font-size: 12px;
            font-weight: 600;
        }

        .item-card-row {
            display: flex;
            justify-content: space-between;
            gap: .75rem;
            font-size: 13px;
            padding: .1rem 0;
        }

        .item-card-row span {
            color: #6c757d;
            flex: 0 0 auto;
        }

        .item-card-row b {
            text-align: right;
            word-break: break-word;
        }

        @media (max-width: 575.98px) {
            .saldo-box {
                font-size: 36px;
            }

            .card-sb .card-body {
                padding: .85rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">
                <i class="fas fa-qrcode"></i> Check QR Carton
                @if ($header)
                    <span class="badge bg-primary ms-1">{{ $header->no_opname }}</span>
                @endif
            </h5>
        </div>
        <div class="card-body">
            @if ($header)
                <div class="mb-3">
                    <small class="text-muted">
                        Opname terakhir
                        <b>{{ $header->no_opname }}</b>
                        &middot; {{ date('d-m-Y', strtotime($header->tgl_opname)) }}
                        &middot; Status <b>{{ $header->status }}</b>
                        @if ($header->ket)
                            &middot; {{ $header->ket }}
                        @endif
                    </small>
                </div>
            @else
                <div class="alert alert-warning py-2">
                    <small>Belum ada data opname sama sekali, jadi belum ada saldo yang bisa dicek.</small>
                </div>
            @endif

            <div class="mb-3">
                <label class="form-label d-block"><small><b>Cara Input</b></small></label>
                <div class="btn-group btn-group-sm mode-group w-100" style="max-width: 420px;" role="group">
                    <input type="radio" class="btn-check" name="modeInput" id="modeManual" value="manual"
                        autocomplete="off" checked>
                    <label class="btn btn-outline-primary" for="modeManual">
                        <i class="fas fa-keyboard"></i> Manual / Scanner
                    </label>
                    <input type="radio" class="btn-check" name="modeInput" id="modeKamera" value="kamera"
                        autocomplete="off">
                    <label class="btn btn-outline-primary" for="modeKamera">
                        <i class="fas fa-camera"></i> Kamera
                    </label>
                </div>
            </div>

            <div id="panelManual">
                <label for="txtqr" class="form-label"><small><b>No. Carton</b></small></label>
                <div class="input-group input-group-sm mb-1" style="max-width: 420px;">
                    <input type="text" class="form-control form-control-sm" id="txtqr" name="txtqr" autocomplete="off"
                        enterkeyhint="go" placeholder="Scan / ketik No. Carton lalu Enter..." autofocus>
                    <button class="btn btn-primary btn-sm" type="button" id="btnCek">
                        <i class="fas fa-search"></i> Cek
                    </button>
                </div>
                <small class="text-muted">Scanner gun & ketik manual sama-sama langsung dicek saat Enter.</small>
            </div>

            <div id="panelKamera" class="d-none">
                <div id="cameraWarning" class="alert alert-warning py-2 d-none">
                    <small></small>
                </div>
                <div id="reader" class="mx-auto"></div>
                <div class="text-center mt-2">
                    <small class="text-muted" id="cameraStatus">Mengaktifkan kamera...</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-sb d-none" id="cardHasil">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">
                <i class="fas fa-box"></i> Isi Carton <span id="hasilNoCarton"></span>
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-2 mb-3">
                <!-- Di HP saldo naik paling atas: itu yang dicari operator saat scan -->
                <div class="col-12 col-md-5 order-first order-md-last">
                    <div class="saldo-panel">
                        <small class="text-muted d-block">Saldo (Total Qty)</small>
                        <span class="saldo-box text-primary" id="infoQty">0</span>
                        <small class="text-muted">pcs</small>
                    </div>
                </div>
                <div class="col-12 col-md-7">
                    <div class="row g-2">
                        <div class="col-6 col-md-4">
                            <div class="info-tile">
                                <small class="text-muted d-block">No. Carton</small>
                                <b id="infoCarton">-</b>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="info-tile">
                                <small class="text-muted d-block">No. Pallet</small>
                                <b id="infoPallet">-</b>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="info-tile">
                                <small class="text-muted d-block">Status Carton</small>
                                <span id="infoStatus" class="badge bg-secondary">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Layar kecil: satu kartu per item. Layar md ke atas: tabel seperti halaman lain -->
            <div class="d-md-none" id="listIsi"></div>

            <div class="table-responsive d-none d-md-block">
                <table class="table table-bordered table-hover align-middle w-100" id="tabelIsi">
                    <thead class="bg-sb">
                        <tr>
                            <th class="text-center align-middle">Buyer</th>
                            <th class="text-center align-middle">WS</th>
                            <th class="text-center align-middle">Style</th>
                            <th class="text-center align-middle">Dest</th>
                            <th class="text-center align-middle">Color</th>
                            <th class="text-center align-middle">Size</th>
                            <th class="text-center align-middle">Product Item</th>
                            <th class="text-center align-middle">Grade</th>
                            <th class="text-center align-middle">Qty</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script>
        // Halaman ini hanya membaca: scan QR carton -> tampilkan isi & saldonya
        // pada opname terakhir. Tidak ada data yang disimpan.

        function modeAktif() {
            return $('input[name="modeInput"]:checked').val();
        }

        function escapeHtml(text) {
            return $('<div>').text(text ?? '-').html();
        }

        // onDone dipanggil setelah pengecekan selesai (sukses maupun gagal),
        // dipakai mode kamera untuk melanjutkan pembacaan QR berikutnya
        function cekCarton(noCarton, onDone) {
            let done = typeof onDone === 'function' ? onDone : function() {};
            noCarton = (noCarton ?? '').trim();

            if (!noCarton) {
                done();
                return;
            }

            $('#btnCek').prop('disabled', true);

            $.ajax({
                type: 'GET',
                url: '{{ route('get-cek-qr-carton-opname-fg-stock') }}',
                data: {
                    no_carton: noCarton
                },
                success: function(res) {
                    tampilkanHasil(res);
                    $('#txtqr').val('');
                    if (modeAktif() === 'manual') $('#txtqr').focus();
                    $('#btnCek').prop('disabled', false);
                    done();
                },
                error: function(xhr) {
                    $('#cardHasil').addClass('d-none');
                    let pesan = xhr.responseJSON?.message ?? 'Gagal mengambil data carton.';

                    Swal.fire({
                        icon: xhr.status === 404 ? 'warning' : 'error',
                        title: xhr.status === 404 ? 'Tidak Ditemukan' : 'Error',
                        text: pesan,
                    });

                    $('#txtqr').val('');
                    if (modeAktif() === 'manual') $('#txtqr').focus();
                    $('#btnCek').prop('disabled', false);
                    done();
                }
            });
        }

        function barisKartu(label, nilai) {
            return `<div class="item-card-row"><span>${label}</span><b>${escapeHtml(nilai)}</b></div>`;
        }

        function tampilkanHasil(res) {
            $('#hasilNoCarton').text(res.no_carton);
            $('#infoCarton').text(res.no_carton ?? '-');
            $('#infoPallet').text(res.no_pallet ?? '-');
            $('#infoStatus')
                .text(res.status ?? '-')
                .removeClass('bg-secondary bg-success bg-warning text-dark')
                .addClass(res.status === 'CLOSED' ? 'bg-success' : 'bg-warning text-dark');
            $('#infoQty').text(res.total_qty ?? 0);

            let body = $('#tabelIsi tbody').empty();
            let list = $('#listIsi').empty();

            if (!res.items || res.items.length === 0) {
                // Carton sudah terdaftar di opname tapi belum diisi item apa pun
                body.append('<tr><td colspan="9" class="text-center text-muted">Carton ini belum ada isinya (saldo 0).</td></tr>');
                list.append('<div class="text-center text-muted py-3"><small>Carton ini belum ada isinya (saldo 0).</small></div>');
            } else {
                res.items.forEach(function(item) {
                    body.append(`<tr>
                        <td>${escapeHtml(item.buyer)}</td>
                        <td>${escapeHtml(item.ws)}</td>
                        <td>${escapeHtml(item.styleno)}</td>
                        <td>${escapeHtml(item.dest)}</td>
                        <td>${escapeHtml(item.color)}</td>
                        <td class="text-center">${escapeHtml(item.size)}</td>
                        <td>${escapeHtml(item.product_item)}</td>
                        <td class="text-center">${escapeHtml(item.grade)}</td>
                        <td class="text-end">${item.qty ?? 0}</td>
                    </tr>`);

                    list.append(`<div class="item-card">
                        <div class="item-card-head">
                            <span class="item-card-ws">${escapeHtml(item.ws)}</span>
                            <span class="item-card-qty text-primary">${item.qty ?? 0} pcs</span>
                        </div>
                        <div class="item-card-tags">
                            <span class="item-card-tag">Size ${escapeHtml(item.size)}</span>
                            <span class="item-card-tag">Grade ${escapeHtml(item.grade)}</span>
                        </div>
                        ${barisKartu('Buyer', item.buyer)}
                        ${barisKartu('Style', item.styleno)}
                        ${barisKartu('Dest', item.dest)}
                        ${barisKartu('Color', item.color)}
                        ${barisKartu('Product Item', item.product_item)}
                    </div>`);
                });
            }

            $('#cardHasil').removeClass('d-none');

            // Di HP kartu hasil ada di bawah panel scan, jadi digeser sendiri supaya langsung terlihat
            $('html, body').animate({
                scrollTop: $('#cardHasil').offset().top - 70
            }, 250);

            iziToast.success({
                title: 'Ditemukan',
                message: res.no_carton,
                position: 'topCenter',
                timeout: 800,
                close: false,
                progressBar: false
            });
        }

        $('#btnCek').on('click', function() {
            cekCarton($('#txtqr').val());
        });

        // Scanner gun mengirim Enter setelah kodenya diketik, jadi jalurnya sama dengan ketik manual
        $('#txtqr').on('keyup', function(e) {
            if (e.keyCode === 13) {
                e.preventDefault();
                cekCarton($(this).val());
            }
        });

        // ---- Mode Kamera ----
        // Kamera hanya menyala selama mode Kamera dipilih, supaya di PC / pemakai scanner gun
        // halaman tidak pernah meminta izin kamera.
        let html5Qrcode = null;
        let cameraOn = false;

        // getUserMedia hanya tersedia di secure context: https atau localhost.
        // Kalau halaman dibuka lewat http://<ip-server>/..., browser tidak memberi akses kamera
        // sama sekali - itu batasan browser, bukan kode ini.
        function cameraSupported() {
            return window.isSecureContext && navigator.mediaDevices && navigator.mediaDevices.getUserMedia;
        }

        function httpsUrl() {
            return 'https://' + location.host + location.pathname + location.search;
        }

        function showCameraWarning(msg) {
            $('#cameraWarning').removeClass('d-none').find('small').html(msg);
            $('#cameraStatus').text('');
        }

        function insecureMessage() {
            return `Kamera diblokir browser karena halaman ini dibuka lewat <b>${location.protocol}//${location.host}</b>. ` +
                'Browser hanya mengizinkan kamera pada <b>https</b> atau <b>localhost</b>. ' +
                `<a href="${httpsUrl()}" class="alert-link">Buka lewat https</a> untuk memakai kamera.`;
        }

        // qrbox mengikuti lebar layar HP supaya kotak pemindai tidak lebih besar dari videonya
        function qrboxSize(viewfinderWidth, viewfinderHeight) {
            let sisi = Math.floor(Math.min(viewfinderWidth, viewfinderHeight) * 0.7);
            return { width: sisi, height: sisi };
        }

        async function startCamera() {
            $('#cameraWarning').addClass('d-none');
            $('#cameraStatus').text('Mengaktifkan kamera...');

            if (typeof Html5Qrcode === 'undefined') {
                showCameraWarning('Library scanner QR gagal dimuat. Muat ulang halaman ini (Ctrl+Shift+R).');
                return;
            }

            if (!cameraSupported()) {
                showCameraWarning(insecureMessage());
                return;
            }

            html5Qrcode = new Html5Qrcode('reader');

            try {
                // facingMode "environment" = kamera belakang HP, yang dipakai untuk scan
                await html5Qrcode.start({ facingMode: 'environment' }, {
                    fps: 10,
                    qrbox: qrboxSize
                }, function(decodedText) {
                    // Pembacaan dihentikan sementara supaya QR yang sama tidak terbaca
                    // berkali-kali selama proses cek berjalan
                    html5Qrcode.pause(true);
                    cekCarton(decodedText, function() {
                        if (cameraOn) html5Qrcode.resume();
                    });
                });

                cameraOn = true;
                $('#cameraStatus').text('Arahkan kamera ke QR carton, saldonya langsung tampil.');
            } catch (err) {
                console.error('Gagal membuka kamera:', err);

                // Terjemahkan error bawaan browser supaya user tahu harus berbuat apa
                let sebab = {
                    NotAllowedError: 'Izin kamera ditolak. Buka ikon gembok di address bar lalu izinkan Kamera untuk situs ini.',
                    NotFoundError: 'Tidak ada kamera yang terdeteksi di perangkat ini.',
                    NotReadableError: 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi tersebut lalu coba lagi.',
                    OverconstrainedError: 'Kamera belakang tidak tersedia di perangkat ini.',
                }[err?.name] ?? (err?.message ?? err);

                showCameraWarning('Gagal membuka kamera: ' + sebab);
            }
        }

        async function stopCamera() {
            if (html5Qrcode) {
                try {
                    await html5Qrcode.stop();
                    await html5Qrcode.clear();
                } catch (err) {
                    console.warn('Kamera sudah berhenti:', err);
                }
            }

            html5Qrcode = null;
            cameraOn = false;
        }

        $('input[name="modeInput"]').on('change', function() {
            if (modeAktif() === 'kamera') {
                $('#panelManual').addClass('d-none');
                $('#panelKamera').removeClass('d-none');
                startCamera();
            } else {
                $('#panelKamera').addClass('d-none');
                $('#panelManual').removeClass('d-none');
                stopCamera();
                $('#txtqr').focus();
            }
        });

        // Lepaskan kamera saat pindah halaman supaya lampu kamera HP tidak tetap menyala
        $(window).on('beforeunload', function() {
            if (cameraOn) stopCamera();
        });

        // Keyboard HP tidak perlu langsung muncul saat halaman dibuka:
        // autofocus hanya berguna untuk scanner gun di PC
        if (window.matchMedia('(pointer: coarse)').matches) {
            $('#txtqr').removeAttr('autofocus').trigger('blur');
        }
    </script>
@endsection
