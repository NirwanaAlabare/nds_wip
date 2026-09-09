@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
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

        #txtqr {
            font-weight: 600;
            letter-spacing: .5px;
        }

        #reader {
            max-width: 420px;
            border-radius: 10px;
            overflow: hidden;
        }

        .dataTables_length select {
            width: auto;
            min-width: 65px;
            padding-right: 24px;
        }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-end mb-2">
        <a href="{{ route('asset_mesin_opname') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">
                <i class="fas fa-clipboard-check"></i> Opname Mesin
                <span class="badge bg-primary ms-1">{{ $header->no_so }}</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <small class="text-muted">
                    Periode
                    <b>{{ date('d-m-Y', strtotime($header->periode_tgl_awal)) }}</b>
                    s/d
                    <b>{{ date('d-m-Y', strtotime($header->periode_tgl_akhir)) }}</b>
                    @if ($header->ket)
                        &middot; {{ $header->ket }}
                    @endif
                </small>
            </div>

            <div class="mb-3">
                <label for="cbolok" class="form-label"><small><b>Lokasi</b></small></label>
                <select class="form-control form-control-sm select2bs4" id="cbolok" name="cbolok">
                    <option value="" selected>-- Semua Lokasi (lihat saja) --</option>
                    @foreach ($lokasiList as $row)
                        <option value="{{ $row->isi }}">{{ $row->tampil }}</option>
                    @endforeach
                </select>
                <small class="text-muted">
                    "Semua Lokasi" hanya untuk melihat seluruh isi No SO ini - untuk scan, pilih lokasinya dulu.
                </small>
            </div>

            <div class="mb-3">
                <label class="form-label d-block"><small><b>Cara Input</b></small></label>
                <div class="btn-group btn-group-sm" role="group">
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
                <label for="txtqr" class="form-label"><small><b>QR Code</b></small></label>
                <div class="input-group input-group-sm mb-1">
                    <input type="text" class="form-control form-control-sm" id="txtqr" name="txtqr" autocomplete="off"
                        enterkeyhint="go" placeholder="Scan / ketik kode QR lalu Enter..." autofocus>
                    <button class="btn btn-primary btn-sm" type="button" id="btnSimpan">
                        <i class="fas fa-check"></i> Simpan
                    </button>
                </div>
                <small class="text-muted">Scanner gun & ketik manual sama-sama tersimpan otomatis saat Enter.</small>
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

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">
                <i class="fas fa-list"></i> List Transaksi Mesin
                <small class="text-muted">{{ $header->no_so }} <span id="lokasiAktif"></span></small>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">Tgl. Opname</th>
                            <th scope="col" class="text-center align-middle">Sumber</th>
                            <th scope="col" class="text-center align-middle">Kode QR</th>
                            <th scope="col" class="text-center align-middle">Jenis</th>
                            <th scope="col" class="text-center align-middle">Merk</th>
                            <th scope="col" class="text-center align-middle">Tipe</th>
                            <th scope="col" class="text-center align-middle">Serial Number</th>
                            <th scope="col" class="text-center align-middle">Lokasi</th>
                            <th scope="col" class="text-center align-middle">User</th>
                            <th scope="col" class="text-center align-middle">Waktu Scan</th>
                            <th scope="col" class="text-center align-middle">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="mt-2"><small id="totalRows">Total : 0 mesin</small></div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        // Modul Asset: senyapkan alert bawaan DataTables saat ajax gagal, cukup dicatat di console
        $.fn.dataTable.ext.errMode = function(settings, techNote, message) {
            console.error('DataTable ajax error:', message);
        };
    </script>

    <script>
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: 'resolve'
        });

        // Semua scan di halaman ini masuk ke header (No SO) yang sedang dibuka
        const idSo = @json($header->id);

        // onDone dipanggil setelah proses simpan selesai (sukses maupun gagal),
        // dipakai mode kamera untuk melanjutkan pembacaan QR berikutnya
        function simpanQr(kodeQr, onDone) {
            let done = typeof onDone === 'function' ? onDone : function() {};
            let lokasi = $('#cbolok').val();

            if (!kodeQr) {
                done();
                return;
            }

            if (!lokasi) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Lokasi belum dipilih!',
                    text: 'Pilih lokasi dulu sebelum scan - "Semua Lokasi" hanya untuk melihat daftar.',
                });
                $('#txtqr').val('');
                done();
                return;
            }

            $('#btnSimpan').prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: '{{ route('store_asset_mesin_opname') }}',
                data: {
                    txtqr: kodeQr,
                    cbolok: lokasi,
                    id_so: idSo,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    dataTableReload();

                    if (res.icon === 'success') {
                        // Sukses cukup lewat toast singkat di pojok: tidak menutupi kamera
                        // & tidak menahan scan berikutnya
                        iziToast.success({
                            title: 'Tersimpan',
                            message: kodeQr,
                            position: 'topCenter',
                            timeout: 800,
                            close: false,
                            progressBar: false
                        });
                    } else {
                        // Penolakan (mis. QR dobel) tetap menunggu OK supaya pesannya sempat dibaca
                        Swal.fire({
                            icon: res.icon,
                            title: res.msg,
                            html: res.detail ?? '',
                            showConfirmButton: true
                        });
                    }
                    $('#txtqr').val('');
                    if (modeAktif() === 'manual') $('#txtqr').focus();
                    $('#btnSimpan').prop('disabled', false);
                    done();
                },
                error: function(xhr) {
                    console.error('Error saving scan:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal menyimpan hasil scan.',
                    });
                    $('#btnSimpan').prop('disabled', false);
                    done();
                }
            });
        }

        function modeAktif() {
            return $('input[name="modeInput"]:checked').val();
        }

        // ---- List transaksi hari ini per lokasi ----
        // Baru dimuat setelah lokasi dipilih; sebelum itu tabel dibiarkan kosong.
        // Catatan: jangan pakai variabel datatable di dalam callback DataTables (drawCallback dsb),
        // karena callback-nya jalan saat konstruktor masih berjalan & variabelnya belum ter-assign.
        // Pakai this.api() seperti di bawah.
        var datatable = $('#datatable').DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollX: true,
            ajax: {
                url: '{{ route('getdata_asset_mesin_opname') }}',
                data: function(d) {
                    d.id_so = idSo;
                    d.cbolok = $('#cbolok').val();
                }
            },
            columns: [
                { data: 'tgl_opname' }, // Tgl. Opname
                {
                    data: 'sumber',
                    className: 'text-center',
                    defaultContent: '-',
                    // Bedakan mesin pembelian & sewa secara visual
                    render: function(sumber) {
                        if (!sumber) return '<span class="badge bg-secondary">-</span>';
                        let warna = sumber === 'SEWA' ? 'bg-warning text-dark' : 'bg-primary';
                        return `<span class="badge ${warna}">${sumber}</span>`;
                    }
                }, // Sumber
                { data: 'kode_qr' }, // Kode QR
                { data: 'nm_jenis', defaultContent: '-' }, // Jenis
                { data: 'nm_merk', defaultContent: '-' }, // Merk
                { data: 'tipe', defaultContent: '-' }, // Tipe
                { data: 'serial_number', defaultContent: '-' }, // Serial Number
                { data: 'lokasi' }, // Lokasi
                { data: 'created_by', defaultContent: '-' }, // User
                { data: 'created_at' }, // Waktu Scan
                {
                    data: 'id',
                    className: 'text-center',
                    render: function(id) {
                        return `<button type="button" class="btn btn-sm btn-danger btn-hapus" data-id="${id}"><i class="fas fa-trash"></i></button>`;
                    }
                }, // Act
            ],
            drawCallback: function() {
                let api = this.api();
                let total = api.rows().count();
                let sewa = api.rows().data().toArray().filter(r => r.sumber === 'SEWA').length;

                $('#totalRows').text(`Total : ${total} mesin (Pembelian : ${total - sewa}, Sewa : ${sewa})`);
            }
        });

        function dataTableReload() {
            datatable.ajax.reload(null, false);
        }

        $('#btnSimpan').on('click', function() {
            simpanQr($('#txtqr').val());
        });

        // Scanner gun mengirim Enter setelah kodenya diketik, jadi jalurnya sama dengan ketik manual
        $('#txtqr').on('keyup', function(e) {
            if (e.keyCode === 13) {
                e.preventDefault();
                simpanQr($(this).val());
            }
        });

        // Ganti lokasi = list transaksi ikut menampilkan isi lokasi tersebut pada No SO ini.
        // Kosong berarti "Semua Lokasi": tabel menampilkan seluruh mesin di No SO ini.
        $('#cbolok').on('change', function() {
            $('#lokasiAktif').text(this.value ? '- ' + this.value : '- Semua Lokasi');
            datatable.ajax.reload();
            if (modeAktif() === 'manual') $('#txtqr').focus();
        });

        // Hapus baris scan yang salah
        $(document).on('click', '.btn-hapus', function() {
            let id = $(this).data('id');

            Swal.fire({
                icon: 'question',
                title: 'Hapus data scan ini?',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
            }).then(function(res) {
                if (!res.isConfirmed) return;

                $.ajax({
                    type: 'DELETE',
                    url: '{{ route('delete_asset_mesin_opname') }}',
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function() {
                        dataTableReload();
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal menghapus data.',
                        });
                    }
                });
            });
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
                    qrbox: { width: 250, height: 250 }
                }, function(decodedText) {
                    // Pembacaan dihentikan sementara supaya QR yang sama tidak terbaca
                    // berkali-kali selama proses simpan berjalan
                    html5Qrcode.pause(true);
                    simpanQr(decodedText, function() {
                        if (cameraOn) html5Qrcode.resume();
                    });
                });

                cameraOn = true;
                $('#cameraStatus').text('Arahkan kamera ke QR mesin, hasil scan tersimpan otomatis.');
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

        // Mode Kamera sengaja tetap bisa dipilih walau kondisinya tidak mendukung:
        // panelnya yang akan menjelaskan alasannya, bukan tombol yang diam saja.
    </script>
@endsection
