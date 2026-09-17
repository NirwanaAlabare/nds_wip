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

        /* Pratinjau mesin hasil scan: dari lokasi mana ke lokasi mana */
        #panelPreview .arah-mutasi {
            font-size: 1.05rem;
            font-weight: 600;
        }

        /* ---- Kartu yang bisa dilipat ----
           Di HP ketiga section ini panjang sekali kalau terbuka semua, jadi
           headernya dibikin tombol supaya isinya bisa disembunyikan. */
        .kartu {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 14px;
            box-shadow: 0 1px 2px rgba(8, 33, 73, .04);
        }

        /* Semua header kartu pakai navy tema (--sb-color), seragam dari atas ke bawah */
        .kartu-head {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            border: 0;
            text-align: left;
            padding: 11px 14px;
            background: #082149;
        }

        .kartu-head:hover {
            background: #0c2c5e;
        }

        .kartu-ikon {
            flex: 0 0 auto;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            background: rgba(255, 255, 255, .14);
            color: #fff;
        }

        .kartu-judul {
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
        }

        .kartu-meta {
            flex: 1 1 auto;
            min-width: 0;
            font-size: .78rem;
            color: #9fb3d1;
            text-align: right;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kartu-angka {
            flex: 0 0 auto;
            font-size: .72rem;
            font-weight: 700;
            color: #fff;
            background: #238380;
            border-radius: 999px;
            padding: 2px 9px;
        }

        .kartu-chev {
            flex: 0 0 auto;
            font-size: .7rem;
            color: #9fb3d1;
            transition: transform .2s ease;
        }

        .kartu-head[aria-expanded="false"] .kartu-chev {
            transform: rotate(-90deg);
        }

        /* ---- Chip ringkasan isi lokasi ---- */
        .chip-baris {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 10px;
        }

        .chip {
            font-size: .74rem;
            border-radius: 8px;
            padding: 4px 10px;
            background: #f6f7f9;
            color: #495057;
        }

        .chip b {
            font-variant-numeric: tabular-nums;
        }

        /* ---- Keadaan kosong ---- */
        .kosong {
            text-align: center;
            color: #adb5bd;
            padding: 26px 10px;
            font-size: .82rem;
        }

        .kosong i {
            font-size: 1.6rem;
            display: block;
            margin-bottom: 8px;
            opacity: .5;
        }

        /* ---- Tabel mesin di lokasi ----
           Paging diganti scroll: operator lebih sering menyapu daftar daripada
           lompat halaman, dan headernya tetap kelihatan selama menggulir. */
        /* Saat scrollY aktif, DataTables memindah header ke tabel terpisah
           (.dataTables_scrollHead) - jadi dua-duanya perlu disebut */
        #tabelMesin thead th,
        .dataTables_scrollHead thead th {
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef !important;
            border-top: 0;
            font-size: .72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #6b7280;
            white-space: nowrap;
        }

        #tabelMesin tbody td {
            border-top: 1px solid #f4f5f7;
            padding: 9px 12px;
            vertical-align: middle;
        }

        #tabelMesin tbody tr:hover td {
            background: #f7fbfb;
        }

        #tabelMesin tbody tr td:first-child {
            font-weight: 600;
            color: #212529;
            letter-spacing: .3px;
        }

        /* Area scroll: header ikut tersangkut di atas & ada garis pemisah tipis */
        .dataTables_scrollBody {
            border-bottom: 1px solid #f1f3f5;
        }

        /* Pil lembut, bukan badge pekat - supaya tabel tidak ramai warna */
        .pil {
            display: inline-block;
            font-size: .68rem;
            font-weight: 600;
            letter-spacing: .3px;
            padding: 2px 8px;
            border-radius: 999px;
            white-space: nowrap;
        }

        .pil-beli {
            color: #1d4ed8;
            background: #e8eefc;
        }

        .pil-sewa {
            color: #8a5a00;
            background: #fbf0dc;
        }

        .pil-active {
            color: #1b7c50;
            background: #e4f4ec;
        }

        .pil-idle {
            color: #8a5a00;
            background: #fbf0dc;
        }

        .pil-breakdown {
            color: #b42318;
            background: #fce9e7;
        }

        .pil-lain {
            color: #6b7280;
            background: #f1f3f5;
        }

        /* ---- History: garis waktu ----
           Bulatan arah di kiri disambung garis vertikal, jadi kebaca sebagai
           urutan kejadian - bukan sekadar tumpukan baris. */
        .timeline {
            position: relative;
        }

        .history-item {
            position: relative;
            padding: 10px 2px 10px 38px;
        }

        .history-item::before {
            content: '';
            position: absolute;
            left: 13px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #f1f3f5;
        }

        .history-item:first-child::before {
            top: 16px;
        }

        .history-item:last-child::before {
            bottom: calc(100% - 16px);
        }

        .history-item .dot {
            position: absolute;
            left: 0;
            top: 8px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .65rem;
            background: #f1f3f5;
            color: #6b7280;
            border: 2px solid #fff;
        }

        .history-item .dot.masuk {
            background: #e4f4ec;
            color: #1b7c50;
        }

        .history-item .dot.keluar {
            background: #fbf0dc;
            color: #8a5a00;
        }

        .history-item .baris1 {
            display: flex;
            align-items: baseline;
            gap: 8px;
        }

        .history-item .qr {
            font-weight: 600;
            color: #212529;
        }

        .history-item .mesin {
            flex: 1 1 auto;
            min-width: 0;
            font-size: .8rem;
            color: #6c757d;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .history-item .waktu {
            flex: 0 0 auto;
            font-size: .75rem;
            color: #adb5bd;
            font-variant-numeric: tabular-nums;
        }

        .history-item .baris2 {
            font-size: .8rem;
            color: #495057;
            margin-top: 2px;
        }

        .history-item .lokasi-kosong {
            color: #adb5bd;
            font-style: italic;
        }

        /* Penanda arah cuma muncul saat satu lokasi sedang dilihat */
        .history-item .arah {
            font-size: .68rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .3px;
            padding: 1px 6px;
            border-radius: 4px;
        }

        .history-item .arah.masuk {
            color: #1b7c50;
            background: #e4f4ec;
        }

        .history-item .arah.keluar {
            color: #8a5a00;
            background: #fbf0dc;
        }

        .btn-muat-lagi {
            width: 100%;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            background: #fff;
            padding: 7px;
            margin-top: 10px;
            font-size: .8rem;
            color: #6c757d;
        }

        .btn-muat-lagi:hover {
            background: #f8f9fa;
            color: #212529;
        }

        /* Layar HP: tombol & input dibuat selebar layar supaya gampang dipencet
           sambil berdiri di depan mesin */
        @media (max-width: 575.98px) {
            #modeSwitch {
                display: flex;
                width: 100%;
            }

            #modeSwitch .btn {
                flex: 1 1 0;
            }

            #txtqr {
                font-size: 1.05rem;
                padding: 10px 12px;
            }

            #btnSimpan {
                padding-left: 16px;
                padding-right: 16px;
            }
        }

        /* ---- Tampilan HP (di bawah 768px) ---- */
        @media (max-width: 767.98px) {
            .kartu .card-body {
                padding: .75rem;
            }

            /* Tabel mesin di lokasi: tiap baris jadi satu kartu, tidak perlu geser ke samping.
               Scroll tabel DataTables dimatikan untuk HP (lihat isHp di script). */
            #tabelMesin,
            #tabelMesin tbody {
                display: block;
                width: 100% !important;
                border: 0;
            }

            #tabelMesin thead {
                display: none;
            }

            #tabelMesin tbody tr {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: .4rem .75rem;
                margin-bottom: .6rem;
                padding: .7rem .8rem;
                background: #fff;
                border: 1px solid #e9ecef;
                border-left: 4px solid #082149;
                border-radius: 10px;
                box-shadow: 0 1px 2px rgba(8, 33, 73, .05);
            }

            #tabelMesin tbody tr:hover td {
                background: transparent;
            }

            #tabelMesin tbody td {
                display: block;
                padding: 0;
                border: 0;
                white-space: normal;
                word-break: break-word;
                text-align: left !important;
                font-size: .85rem;
            }

            /* Label kecil di atas nilai */
            #tabelMesin tbody td.kolom-merk::before,
            #tabelMesin tbody td.kolom-tipe::before,
            #tabelMesin tbody td.kolom-sn::before {
                display: block;
                font-size: .68rem;
                font-weight: 400;
                color: #6c757d;
                text-transform: uppercase;
                letter-spacing: .3px;
            }

            #tabelMesin tbody td.kolom-merk::before { content: 'Merk'; }
            #tabelMesin tbody td.kolom-tipe::before { content: 'Tipe'; }
            #tabelMesin tbody td.kolom-sn::before { content: 'Serial Number'; }

            /* Urutan isi kartu: QR | Status, Jenis, Merk | Tipe, SN | Sumber */
            #tabelMesin tbody td.kolom-qr { order: 1; font-size: .95rem; }
            #tabelMesin tbody td.kolom-status { order: 2; text-align: right !important; }
            #tabelMesin tbody td.kolom-jenis {
                order: 3;
                grid-column: 1 / -1;
                font-weight: 600;
                padding-bottom: .35rem;
                border-bottom: 1px dashed #e9ecef;
            }
            #tabelMesin tbody td.kolom-merk { order: 4; }
            #tabelMesin tbody td.kolom-tipe { order: 5; }
            #tabelMesin tbody td.kolom-sn { order: 6; }
            #tabelMesin tbody td.kolom-sumber {
                order: 7;
                align-self: end;
                text-align: right !important;
            }

            #tabelMesin tbody td.dataTables_empty {
                grid-column: 1 / -1;
                text-align: center !important;
                color: #adb5bd;
            }

            /* Kotak cari DataTables selebar layar */
            #isiMesin .dataTables_filter {
                text-align: left;
                margin-bottom: .5rem;
            }

            #isiMesin .dataTables_filter label {
                display: flex;
                align-items: center;
                gap: .5rem;
                width: 100%;
                margin: 0;
            }

            #isiMesin .dataTables_filter input {
                flex: 1 1 auto;
                width: auto;
                margin-left: 0 !important;
            }

            /* History: nama mesin turun ke baris sendiri supaya tidak terpotong habis */
            .history-item .baris1 {
                flex-wrap: wrap;
                row-gap: 0;
            }

            .history-item .waktu {
                margin-left: auto;
            }

            .history-item .mesin {
                order: 3;
                flex-basis: 100%;
                white-space: normal;
            }

            .history-item .baris2 {
                line-height: 1.5;
            }
        }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-end mb-2">
        <a href="{{ route($routeDashboard) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="kartu">
        <button type="button" class="kartu-head" data-bs-toggle="collapse" data-bs-target="#isiInput"
            aria-expanded="true">
            <span class="kartu-ikon"><i class="fa-solid fa-right-left"></i></span>
            <span class="kartu-judul">Mutasi Mesin</span>
            <span class="kartu-meta">{{ date('d-m-Y') }}</span>
            <i class="fas fa-chevron-down kartu-chev"></i>
        </button>
        <div class="collapse show" id="isiInput">
        <div class="card-body">
            <div class="mb-3">
                <small class="text-muted">
                    Pilih <b>Lokasi Tujuan</b> lalu scan QR mesinnya. Lokasi asal terbaca otomatis dari data mesin,
                    jadi tidak perlu diisi.
                </small>
            </div>

            <div class="mb-3">
                <label for="cbotujuan" class="form-label"><small><b>Lokasi Tujuan</b></small></label>
                <select class="form-control form-control-sm select2bs4" id="cbotujuan" name="cbotujuan"
                    autocomplete="off">
                    <option value="" selected>-- Pilih Lokasi Tujuan --</option>
                    @foreach ($lokasiList as $row)
                        <option value="{{ $row->isi }}">{{ $row->tampil }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Semua mesin yang discan di bawah ini akan dipindah ke lokasi tujuan
                    tersebut.</small>
            </div>

            <div class="mb-3">
                <label class="form-label d-block"><small><b>Cara Input</b></small></label>
                <div class="btn-group btn-group-sm" role="group" id="modeSwitch">
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
                <small class="text-muted">Scanner gun &amp; ketik manual sama-sama tersimpan otomatis saat Enter.</small>
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

            <!-- Identitas mesin terakhir yang discan; penegas bahwa QR-nya benar -->
            <div id="panelPreview" class="alert alert-light border mt-3 mb-0 d-none">
                <div class="arah-mutasi mb-1">
                    <span id="prevAsal" class="badge bg-secondary">-</span>
                    <i class="fa-solid fa-arrow-right mx-1 text-muted"></i>
                    <span id="prevTujuan" class="badge bg-success">-</span>
                </div>
                <small class="text-muted" id="prevMesin">-</small>
            </div>
        </div>
        </div>
    </div>

    {{-- Isi lokasi tujuan saat ini. Bukan daftar hasil scan: ini cerminan master mesin,
         jadi operator bisa mencocokkan langsung dengan yang terlihat di lapangan. --}}
    <div class="kartu">
        <button type="button" class="kartu-head" data-bs-toggle="collapse" data-bs-target="#isiMesin"
            aria-expanded="true">
            <span class="kartu-ikon"><i class="fa-solid fa-gears"></i></span>
            <span class="kartu-judul">Mesin di Lokasi Ini</span>
            <span class="kartu-meta" id="lokasiAktif">belum ada lokasi dipilih</span>
            <span class="kartu-angka d-none" id="jumlahMesin">0</span>
            <i class="fas fa-chevron-down kartu-chev"></i>
        </button>
        <div class="collapse show" id="isiMesin">
        <div class="card-body">
            {{-- Ringkasan isi lokasi: yang paling sering ditanya orang lapangan
                 ("di sini ada berapa, yang nganggur berapa") tanpa harus menghitung baris --}}
            <div class="chip-baris" id="ringkasMesin"></div>

            <div class="table-responsive">
                <table id="tabelMesin" class="table table-hover align-middle text-nowrap w-100">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center align-middle">Kode QR</th>
                            <th scope="col" class="text-center align-middle">Sumber</th>
                            <th scope="col" class="text-center align-middle">Jenis</th>
                            <th scope="col" class="text-center align-middle">Merk</th>
                            <th scope="col" class="text-center align-middle">Tipe</th>
                            <th scope="col" class="text-center align-middle">Serial Number</th>
                            <th scope="col" class="text-center align-middle">Status</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        </div>
    </div>

    {{-- History sengaja bukan DataTables: isinya cuma 5 baris sekali muat, jadi list
         biasa lebih ringan & lebih enak dibaca di HP daripada tabel yang digeser-geser. --}}
    <div class="kartu">
        <button type="button" class="kartu-head" data-bs-toggle="collapse" data-bs-target="#isiHistory"
            aria-expanded="true">
            <span class="kartu-ikon"><i class="fa-solid fa-clock-rotate-left"></i></span>
            <span class="kartu-judul">History Mutasi</span>
            <span class="kartu-meta" id="historyLingkup">5 transaksi terakhir</span>
            <i class="fas fa-chevron-down kartu-chev"></i>
        </button>
        <div class="collapse show" id="isiHistory">
        <div class="card-body">
            <div id="daftarHistory" class="timeline"></div>

            <div id="historyKosong" class="kosong d-none">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <div>Belum ada mutasi tercatat.</div>
            </div>

            <button type="button" class="btn-muat-lagi d-none" id="btnMuatLagi">
                <i class="fas fa-chevron-down"></i> Muat 5 lagi
            </button>
        </div>
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

        function lokasiTujuan() {
            return $('#cbotujuan').val();
        }

        function modeAktif() {
            return $('input[name="modeInput"]:checked').val();
        }

        // ---- Notifikasi ----
        // Semua pakai toast, tidak ada lagi popup yang harus diklik OK: operator
        // memegang scanner gun / HP, tiap konfirmasi yang menahan layar memutus
        // ritme scan. Yang gagal diberi durasi lebih panjang supaya sempat dibaca,
        // tapi tetap tidak menahan scan berikutnya.
        function toast(jenis, judul, pesan, detik) {
            iziToast[jenis]({
                title: judul,
                message: pesan ?? '',
                position: 'topCenter',
                timeout: (detik ?? 2) * 1000,
                close: jenis === 'error' || jenis === 'warning',
                progressBar: false,
            });
        }

        // Detail dari server dikirim ber-HTML (<b>...</b>) supaya nama lokasi & kode QR
        // menonjol; iziToast merender HTML di message, jadi dipakai apa adanya.
        function pesanGagal(res, cadangan) {
            toast('error', res?.msg ?? cadangan, res?.detail ?? '', 4);
        }

        function badgeSumber(sumber) {
            if (!sumber) return '<span class="pil pil-lain">-</span>';
            return `<span class="pil ${sumber === 'SEWA' ? 'pil-sewa' : 'pil-beli'}">${sumber}</span>`;
        }

        function badgeStatus(status) {
            let kelas = {
                ACTIVE: 'pil-active',
                IDLE: 'pil-idle',
                BREAKDOWN: 'pil-breakdown',
            } [status] ?? 'pil-lain';
            return `<span class="pil ${kelas}">${status || '-'}</span>`;
        }

        // ---- Proses scan ----
        // onDone dipanggil setelah proses simpan selesai (sukses maupun gagal),
        // dipakai mode kamera untuk melanjutkan pembacaan QR berikutnya
        function simpanQr(kodeQr, onDone) {
            let done = typeof onDone === 'function' ? onDone : function() {};
            let idTujuan = lokasiTujuan();

            if (!kodeQr) {
                done();
                return;
            }

            if (!idTujuan) {
                toast('warning', 'Lokasi tujuan belum dipilih', 'Pilih lokasi dulu sebelum scan.', 2);
                $('#txtqr').val('');
                done();
                return;
            }

            $('#btnSimpan').prop('disabled', true);

            // Kode QR dicek dulu supaya user langsung melihat mesin & lokasi asalnya,
            // baru mutasinya disimpan
            $.ajax({
                type: 'GET',
                url: '{{ route('cek_qr_asset_mesin_mutasi') }}',
                data: {
                    kode_qr: kodeQr
                },
                success: function(res) {
                    tampilkanPreview(res.data);
                    kirimMutasi(kodeQr, idTujuan, done);
                },
                error: function(xhr) {
                    let res = xhr.responseJSON ?? {};
                    pesanGagal(res, 'Gagal membaca kode QR.');
                    resetInput();
                    done();
                }
            });
        }

        function kirimMutasi(kodeQr, idTujuan, done) {
            $.ajax({
                type: 'POST',
                url: '{{ route('store_asset_mesin_mutasi') }}',
                data: {
                    txtqr: kodeQr,
                    id_lokasi_tujuan: idTujuan,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    muatUlangTabel();

                    if (res.icon !== 'success') {
                        pesanGagal(res, 'Gagal menyimpan mutasi.');
                    } else if (res.pindah) {
                        toast('success', 'Termutasi', kodeQr, 0.8);
                    } else {
                        // Mesin memang sudah di lokasi ini: tidak dicatat sebagai mutasi,
                        // tapi tetap dikabari supaya operator tahu scan-nya terbaca
                        toast('info', 'Sudah di sini', kodeQr + ' &middot; ACTIVE', 1.2);
                    }

                    resetInput();
                    done();
                },
                error: function(xhr) {
                    console.error('Error saving mutasi:', xhr.responseText);
                    toast('error', 'Gagal menyimpan mutasi', 'Periksa koneksi lalu coba scan lagi.', 4);
                    resetInput();
                    done();
                }
            });
        }

        function tampilkanPreview(unit) {
            if (!unit) return;

            $('#prevAsal').text(unit.lokasi_asal ?? 'Belum ada lokasi');
            $('#prevTujuan').text($('#cbotujuan').find('option:selected').text());
            $('#prevMesin').html(
                `<b>${unit.kode_qr}</b> &middot; ${unit.sumber ?? '-'} &middot; ` +
                `${unit.nm_jenis ?? '-'} ${unit.nm_merk ?? ''} ${unit.tipe ?? ''} &middot; SN ${unit.serial_number ?? '-'}`
            );
            $('#panelPreview').removeClass('d-none');
        }

        function resetInput() {
            $('#txtqr').val('');
            if (modeAktif() === 'manual') $('#txtqr').focus();
            $('#btnSimpan').prop('disabled', false);
        }

        // ---- Tabel 1: isi lokasi tujuan ----
        // Catatan: jangan pakai variabel datatable di dalam callback DataTables (drawCallback dsb),
        // karena callback-nya jalan saat konstruktor masih berjalan & variabelnya belum ter-assign.
        // Pakai this.api() seperti di bawah.
        // Di HP baris tabel tampil sebagai kartu (lihat CSS), jadi scroll & header DataTables
        // tidak dipakai; kartunya cukup ikut scroll halaman.
        var isHp = window.matchMedia('(max-width: 767.98px)').matches;

        var tabelMesin = $('#tabelMesin').DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            searching: true,
            // Paging & "Show entries" dimatikan: isinya digulir saja, header tetap nempel.
            // info dimatikan juga karena ringkasannya sudah ditulis sendiri di bawah tabel.
            paging: false,
            lengthChange: false,
            info: false,
            scrollY: isHp ? '' : '420px',
            scrollX: !isHp,
            scrollCollapse: !isHp,
            // Tabelnya baru berarti setelah ada lokasi yang dipilih
            deferLoading: 0,
            ajax: {
                url: '{{ route('getdata_mesin_per_lokasi') }}',
                data: function(d) {
                    d.id_lokasi = lokasiTujuan();
                }
            },
            columns: [
                // className kolom-* dipakai CSS tampilan kartu di HP
                { data: 'kode_qr', className: 'kolom-qr', defaultContent: '-' }, // Kode QR
                { data: 'sumber', className: 'text-center kolom-sumber', defaultContent: '-', render: badgeSumber }, // Sumber
                { data: 'nm_jenis', className: 'kolom-jenis', defaultContent: '-' }, // Jenis
                { data: 'nm_merk', className: 'kolom-merk', defaultContent: '-' }, // Merk
                { data: 'tipe', className: 'kolom-tipe', defaultContent: '-' }, // Tipe
                { data: 'serial_number', className: 'kolom-sn', defaultContent: '-' }, // Serial Number
                { data: 'status', className: 'text-center kolom-status', defaultContent: '-', render: badgeStatus }, // Status
            ],
            language: {
                emptyTable: 'Pilih lokasi tujuan dulu untuk melihat isinya.',
                zeroRecords: 'Tidak ada mesin yang cocok dengan pencarian.',
            },
            drawCallback: function() {
                let rows = this.api().rows().data().toArray();
                let cacah = k => rows.filter(k).length;

                // Cukup tiga: status per unit sudah kelihatan di kolom tabelnya sendiri
                let ringkas = [
                    { label: 'Total', nilai: rows.length },
                    { label: 'Pembelian', nilai: cacah(r => r.sumber === 'PEMBELIAN') },
                    { label: 'Sewa', nilai: cacah(r => r.sumber === 'SEWA') },
                ];

                // Nilai 0 tetap ditampilkan: "Sewa 0" itu informasi, bukan kekosongan
                $('#ringkasMesin').html(
                    rows.length ?
                    ringkas.map(r => `<span class="chip">${r.label} <b>${r.nilai}</b></span>`).join('') :
                    ''
                );

                $('#jumlahMesin').text(rows.length).toggleClass('d-none', rows.length === 0);
            }
        });

        // ---- History mutasi ----
        // Bukan DataTables: dimuat 5 baris sekali jalan lewat tombol "Muat 5 lagi",
        // jadi paging-nya diurus sendiri dengan offset.
        let historyOffset = 0;

        function escapeHtml(teks) {
            return $('<div>').text(teks ?? '').html();
        }

        function labelLokasi(lokasi) {
            return lokasi ? escapeHtml(lokasi) : '<span class="lokasi-kosong">Belum ada lokasi</span>';
        }

        function barisHistory(r) {
            // Arah hanya dikirim server saat satu lokasi sedang dipilih
            let arah = r.arah ?
                `<span class="arah ${r.arah.toLowerCase()}">${r.arah}</span>` : '';

            // Bulatan di garis waktu: panah masuk / keluar, atau ikon netral
            // kalau tidak ada lokasi yang jadi acuan
            let kelasDot = r.arah ? r.arah.toLowerCase() : '';
            let ikonDot = {
                MASUK: 'fa-arrow-right-to-bracket',
                KELUAR: 'fa-arrow-right-from-bracket',
            } [r.arah] ?? 'fa-right-left';

            let mesin = [r.nm_jenis, r.nm_merk, r.tipe].filter(Boolean).join(' ');

            return `
                <div class="history-item">
                    <span class="dot ${kelasDot}"><i class="fa-solid ${ikonDot}"></i></span>
                    <div class="baris1">
                        <span class="qr">${escapeHtml(r.kode_qr)}</span>
                        ${arah}
                        <span class="mesin">${escapeHtml(mesin) || '-'}</span>
                        <span class="waktu">${escapeHtml(r.tgl_mutasi)} ${escapeHtml(r.jam)}</span>
                    </div>
                    <div class="baris2">
                        ${labelLokasi(r.lokasi_asal)}
                        <i class="fas fa-arrow-right mx-1 text-muted" style="font-size:.7rem"></i>
                        ${labelLokasi(r.lokasi_tujuan)}
                        <span class="text-muted"> &middot; ${escapeHtml(r.created_by) || '-'}</span>
                    </div>
                </div>`;
        }

        // reset = mulai dari awal (ganti lokasi / habis scan); selain itu menambah ke bawah
        function muatHistory(reset) {
            if (reset) historyOffset = 0;

            $('#btnMuatLagi').prop('disabled', true);

            $.ajax({
                type: 'GET',
                url: '{{ route('getdata_asset_mesin_mutasi') }}',
                data: {
                    id_lokasi: lokasiTujuan(),
                    offset: historyOffset
                },
                success: function(res) {
                    if (reset) $('#daftarHistory').empty();

                    res.data.forEach(r => $('#daftarHistory').append(barisHistory(r)));
                    historyOffset += res.data.length;

                    $('#historyKosong').toggleClass('d-none', historyOffset > 0);
                    $('#btnMuatLagi').toggleClass('d-none', !res.ada_lagi).prop('disabled', false);
                },
                error: function(xhr) {
                    console.error('Gagal memuat history:', xhr.responseText);
                    $('#btnMuatLagi').prop('disabled', false);
                }
            });
        }

        $('#btnMuatLagi').on('click', function() {
            muatHistory(false);
        });

        function muatUlangTabel() {
            tabelMesin.ajax.reload(null, false);
            muatHistory(true);
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

        // Ganti lokasi tujuan = kedua tabel ikut pindah ke lokasi tersebut
        $('#cbotujuan').on('change', function() {
            // value = id_lokasi, jadi nama lokasinya dibaca dari teks option yang terpilih
            let namaLokasi = $(this).find('option:selected').text();
            $('#lokasiAktif').text(this.value ? '- ' + namaLokasi : '- belum ada lokasi dipilih');
            $('#panelPreview').addClass('d-none');

            if (this.value) {
                tabelMesin.ajax.reload();
            } else {
                // Tanpa lokasi tidak ada yang bisa ditampilkan - dikosongkan, bukan
                // dibiarkan memegang isi lokasi sebelumnya
                tabelMesin.clear().draw();
            }

            $('#historyLingkup').text(this.value ? 'Keluar masuk lokasi ini' : '5 transaksi terakhir');
            muatHistory(true);
            if (modeAktif() === 'manual') $('#txtqr').focus();
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
                $('#cameraStatus').text('Arahkan kamera ke QR mesin, mutasinya tersimpan otomatis.');
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

        // ---- Kondisi awal halaman ----
        // Browser memulihkan isi form saat halaman di-reload (F5) & saat dibuka lagi lewat
        // tombol Back, jadi dropdown bisa balik ke lokasi sesi sebelumnya walaupun option
        // default-nya sudah diberi `selected`. Dikosongkan paksa di sini supaya scan tidak
        // pernah nyasar ke lokasi yang tidak sengaja tertinggal.
        // Dipanggil paling akhir: trigger('change') butuh handler #cbotujuan sudah terpasang,
        // dan handler itulah yang sekaligus mengosongkan tabel & memuat history awal.
        function resetLokasiTujuan() {
            $('#cbotujuan').val('').trigger('change');
        }

        resetLokasiTujuan();

        // pageshow dengan persisted = halaman diambil dari bfcache (mis. tombol Back),
        // yang tidak menjalankan ulang script di atas
        $(window).on('pageshow', function(e) {
            if (e.originalEvent?.persisted) resetLokasiTujuan();
        });

        // Lebar kolom DataTables dihitung dari elemen yang terlihat; kalau kartunya
        // sempat dilipat, header & isi tabel bisa jadi tidak sejajar saat dibuka lagi.
        $('#isiMesin').on('shown.bs.collapse', function() {
            tabelMesin.columns.adjust();
        });
    </script>
@endsection
