@extends('layouts.index')

@section('custom-link')
    <style>
        .stocker-card {
            border-top: 4px solid #082149;
            border-radius: 8px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stocker-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
        }
        .info-card-box {
            background-color: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 8px 12px;
            height: 100%;
        }
        .info-label {
            font-size: 10px;
            color: #888888;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .info-value {
            font-size: 13px;
            font-weight: 700;
            color: #2b2b2b;
        }
        .section-badge {
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 700;
        }
        .bg-dc { background-color: #e3f2fd; color: #0d47a1; }
        .bg-sec-inhouse-in { background-color: #f3e5f5; color: #4a148c; }
        .bg-sec-inhouse { background-color: #fff3e0; color: #e65100; }
        .bg-sec-in { background-color: #e8f5e9; color: #1b5e20; }
        .bg-sec-update { background-color: #ffebee; color: #b71c1c; }
        
        .stat-box {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 6px;
            text-align: center;
        }
        .stat-title { font-size: 10px; color: #6c757d; font-weight: bold; }
        .stat-val { font-size: 12px; font-weight: bold; }

        /* Styling Box Header Modal */
        .modal-info-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 6px 10px;
            height: 100%;
        }

        /* Modal Table Styling */
        .modal-table th, .modal-table td {
            text-align: center;
            vertical-align: middle !important;
            font-size: 12px;
        }
    </style>
@endsection

@section('content')
    <h5 class="text-sb fw-bold mb-3"><i class="fa fa-cogs"></i> Cek Proses Stocker</h5>
    <!-- Filter Bar -->
    <div class="card card-sb mb-3">
        <div class="card-body py-3">
            <div class="row align-items-end">
                <div class="col-md-9">
                    <label class="form-label font-weight-bold">ID QR Stocker</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="id_qr_stocker" placeholder="Ketik ID QR Stocker lalu tekan Enter atau Cari (Contoh: STK-2605284)">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button" onclick="loadStockerCards()">
                                <i class="fas fa-search"></i> Cari Data
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end pt-2">
                    <button class="btn btn-secondary btn-block" onclick="resetFilter()">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicator Loading -->
    <div id="loading-spinner" class="text-center my-4" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Mencari data transaksi stocker...</p>
    </div>

    <!-- Container Grid Cards Stocker (Layout Full Width / col-12) -->
    <div class="row" id="stocker-cards-container">
        <!-- Default State saat belum ada filter -->
        <div class="col-12 text-center my-5 text-muted">
            <i class="fas fa-search fa-3x mb-3 text-secondary"></i>
            <h5>Silakan masukkan ID QR Stocker</h5>
            <p class="small">Ketik ID QR Stocker pada kolom pencarian di atas untuk menampilkan rincian transaksi.</p>
        </div>
    </div>

    <!-- Modal Detail Riwayat Transaksi per Tanggal -->
    <div class="modal fade" id="modalDetailTransaksi" tabindex="-1" role="dialog" aria-labelledby="modalDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-sb-secondary text-white py-2">
                    <h5 class="modal-title font-weight-bold" id="modalDetailLabel">
                        <i class="fas fa-history mr-2"></i> Rincian Riwayat Transaksi per Tanggal
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Header Info Stocker di Dalam Modal -->
                    <div class="card border mb-3 shadow-sm bg-white">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-lg-3 col-md-4 col-6 mb-2">
                                    <div class="modal-info-box">
                                        <div class="info-label"><i class="fas fa-qrcode mr-1 text-sb-secondary"></i> ID Stocker</div>
                                        <div class="info-value text-sb-secondary" id="modal-id-stocker">-</div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-4 col-6 mb-2">
                                    <div class="modal-info-box">
                                        <div class="info-label"><i class="fas fa-file-alt mr-1"></i> Work Order / WS</div>
                                        <div class="info-value text-truncate" id="modal-ws">-</div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-4 col-6 mb-2">
                                    <div class="modal-info-box">
                                        <div class="info-label"><i class="fas fa-palette mr-1"></i> Color</div>
                                        <div class="info-value text-truncate" id="modal-color">-</div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-4 col-6 mb-2">
                                    <div class="modal-info-box">
                                        <div class="info-label"><i class="fas fa-ruler mr-1"></i> Size</div>
                                        <div class="info-value" id="modal-size">-</div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-6 mb-2">
                                    <div class="modal-info-box">
                                        <div class="info-label"><i class="fas fa-th-large mr-1"></i> Panel</div>
                                        <div class="info-value text-truncate" id="modal-panel">-</div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-12 mb-2">
                                    <div class="modal-info-box">
                                        <div class="info-label"><i class="fas fa-puzzle-piece mr-1"></i> Nama Part</div>
                                        <div class="info-value text-truncate" id="modal-nama-part">-</div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-6 mb-2">
                                    <div class="modal-info-box">
                                        <div class="info-label"><i class="fas fa-info-circle mr-1"></i> Part Status</div>
                                        <div class="info-value text-truncate" id="modal-part-status">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nav Tabs Per Tahapan Proses -->
                    <ul class="nav nav-tabs" id="transaksiTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold text-primary" id="dc-tab" data-toggle="tab" data-bs-toggle="tab" href="#dc-content" role="tab">DC IN</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold text-purple" id="sec-inhouse-in-tab" data-toggle="tab" data-bs-toggle="tab" href="#sec-inhouse-in-content" role="tab">Sec. Dalam IN</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold text-orange" id="sec-inhouse-tab" data-toggle="tab" data-bs-toggle="tab" href="#sec-inhouse-content" role="tab">Sec. Dalam OUT</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold text-success" id="sec-in-tab" data-toggle="tab" data-bs-toggle="tab" href="#sec-in-content" role="tab">Terima Secondary</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold text-danger" id="sec-update-tab" data-toggle="tab" data-bs-toggle="tab" href="#sec-update-content" role="tab">Secondary Update</a>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content pt-3" id="transaksiTabContent">
                        <!-- 1. DC IN -->
                        <div class="tab-pane fade show active" id="dc-content" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped modal-table w-100">
                                    <thead class="bg-dc">
                                        <tr>
                                            <th>No</th>
                                            <th>Tgl Transaksi</th>
                                            <th>Created At</th>
                                            <th>Qty Awal</th>
                                            <th>Qty Reject</th>
                                            <th>Qty Replace</th>
                                            <th>Qty Hasil Net</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-dc"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 2. SEC INHOUSE IN -->
                        <div class="tab-pane fade" id="sec-inhouse-in-content" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped modal-table w-100">
                                    <thead class="bg-sec-inhouse-in">
                                        <tr>
                                            <th>No</th>
                                            <th>Tgl Transaksi</th>
                                            <th>Created At</th>
                                            <th>Tujuan</th>
                                            <th>Proses</th>
                                            <th>Qty IN</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-sec-inhouse-in"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 3. SEC INHOUSE -->
                        <div class="tab-pane fade" id="sec-inhouse-content" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped modal-table w-100">
                                    <thead class="bg-sec-inhouse">
                                        <tr>
                                            <th>No</th>
                                            <th>Tgl Transaksi</th>
                                            <th>Created At</th>
                                            <th>Tujuan</th>
                                            <th>Proses</th>
                                            <th>Qty Awal</th>
                                            <th>Qty Reject</th>
                                            <th>Qty Replace</th>
                                            <th>Qty Hasil Net</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-sec-inhouse"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 4. SEC IN -->
                        <div class="tab-pane fade" id="sec-in-content" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped modal-table w-100">
                                    <thead class="bg-sec-in">
                                        <tr>
                                            <th>No</th>
                                            <th>Tgl Transaksi</th>
                                            <th>Created At</th>
                                            <th>Tujuan Asal</th>
                                            <th>Proses</th>
                                            <th>Qty Awal</th>
                                            <th>Qty Reject</th>
                                            <th>Qty Replace</th>
                                            <th>Qty Hasil Net</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-sec-in"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 5. SEC UPDATE -->
                        <div class="tab-pane fade" id="sec-update-content" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped modal-table w-100">
                                    <thead class="bg-sec-update">
                                        <tr>
                                            <th>No</th>
                                            <th>Tgl Transaksi</th>
                                            <th>Created At</th>
                                            <th>Tujuan</th>
                                            <th>Proses</th>
                                            <th>Reject</th>
                                            <th>Replace</th>
                                            <th>Qty Hasil Net</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-sec-update"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal" data-bs-dismiss="modal"><i class=""></i> Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script>
        window.currentStockerData = [];

        document.addEventListener("DOMContentLoaded", () => {
            $('#id_qr_stocker').on('keyup', function (e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    loadStockerCards();
                }
            });

            $('#transaksiTab a').on('click', function (e) {
                e.preventDefault();
                $(this).tab('show');
            });

            $('[data-dismiss="modal"], [data-bs-dismiss="modal"]').on('click', function () {
                $('#modalDetailTransaksi').modal('hide');
            });
        });

        function loadStockerCards() {
            let filterQuery = $('#id_qr_stocker').val().trim();

            if (!filterQuery) {
                showEmptyState();
                return;
            }

            $('#loading-spinner').show();
            $('#stocker-cards-container').html('');

            $.ajax({
                url: "{{ route('get-stocker-process') }}",
                type: "GET",
                data: { id_qr_stocker: filterQuery },
                dataType: "json",
                success: function (response) {
                    $('#loading-spinner').hide();

                    window.currentStockerData = response.data || [];

                    if (window.currentStockerData.length === 0) {
                        $('#stocker-cards-container').html(`
                            <div class="col-12 text-center my-5 text-muted">
                                <i class="fas fa-box-open fa-3x mb-2"></i>
                                <p>Tidak ada data transaksi stocker ditemukan untuk ID: <strong>"${filterQuery}"</strong></p>
                            </div>
                        `);
                        return;
                    }

                    let html = '';
                    window.currentStockerData.forEach((item, index) => {
                        html += renderCardHtml(item, index);
                    });

                    $('#stocker-cards-container').html(html);
                },
                error: function (xhr, status, error) {
                    $('#loading-spinner').hide();
                    console.error("AJAX Error:", error);
                    $('#stocker-cards-container').html(`
                        <div class="col-12 text-center my-5 text-danger">
                            <i class="fas fa-exclamation-triangle fa-3x mb-2"></i>
                            <p>Gagal memuat data dari server. Silakan coba lagi.</p>
                        </div>
                    `);
                }
            });
        }

        function showEmptyState() {
            $('#stocker-cards-container').html(`
                <div class="col-12 text-center my-5 text-muted">
                    <i class="fas fa-search fa-3x mb-3 text-secondary"></i>
                    <h5>Silakan masukkan ID QR Stocker</h5>
                    <p class="small">Ketik ID QR Stocker pada kolom pencarian di atas untuk menampilkan rincian transaksi.</p>
                </div>
            `);
        }

        function renderCardHtml(item, index) {
            // Tentukan Qty Terakhir untuk Secondary Inhouse: 
            // jika sec_inhouse_qty_hasil bernilai > 0 / ada transaksi maka pakai sec_inhouse_qty_hasil, 
            // jika tidak ada maka gunakan total qty_sec_inhouse_in
            let secInhouseLastQty = (item.sec_inhouse_qty_hasil && item.sec_inhouse_qty_hasil !== 0) 
                ? item.sec_inhouse_qty_hasil 
                : (item.qty_sec_inhouse_in || 0);

            return `
                <div class="col-12 mb-4">
                    <div class="card stocker-card h-100 shadow-sm">
                        <!-- Header Kartu Stocker -->
                        <div class="card-header bg-white border-bottom">
                            <div class="row justify-content-between align-items-center py-2">
                                <div class="col-6">
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                        <span class="badge bg-sb font-weight-bold px-3 py-2" style="font-size: 14px;">
                                            <i class="fas fa-qrcode mr-1"></i> ${item.id_qr_stocker || '-'}
                                        </span>
                                        <span class="badge badge-light border text-dark font-weight-bold px-2 py-2" style="font-size: 12px;">
                                            Qty Ply: ${item.qty_stocker || 0}
                                        </span>
                                        ${item.part_status ? `<span class="badge badge-info font-weight-bold px-2 py-2" style="font-size: 12px;"><i class="fas fa-info-circle mr-1"></i>${item.part_status.toUpperCase()}</span>` : ''}
                                    </div>
                                </div>
                                <div class="col-6">
                                    <button class="btn btn-sm btn-sb-secondary font-weight-bold float-end" onclick="openDetailModal(${index})">
                                        <i class="fas fa-list-alt mr-1"></i> Detail Riwayat Transaksi
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body py-3">
                            <!-- Kelompok Informasi Master Stocker -->
                            <div class="row mb-3">
                                <div class="col-lg-2 col-md-3 col-6 mb-2">
                                    <div class="info-card-box">
                                        <div class="info-label"><i class="fas fa-file-alt mr-1"></i> Work Order / WS</div>
                                        <div class="info-value text-truncate" title="${item.ws || '-'}">${item.ws || '-'}</div>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-3 col-6 mb-2">
                                    <div class="info-card-box">
                                        <div class="info-label"><i class="fas fa-palette mr-1"></i> Color</div>
                                        <div class="info-value text-truncate" title="${item.color || '-'}">${item.color || '-'}</div>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-3 col-6 mb-2">
                                    <div class="info-card-box">
                                        <div class="info-label"><i class="fas fa-ruler mr-1"></i> Size</div>
                                        <div class="info-value">${item.size || '-'}</div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-3 mb-2">
                                    <div class="info-card-box">
                                        <div class="info-label"><i class="fas fa-th-large mr-1"></i> Panel</div>
                                        <div class="info-value text-truncate" title="${item.panel || '-'}">${item.panel || '-'}</div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-3 mb-2">
                                    <div class="info-card-box">
                                        <div class="info-label"><i class="fas fa-puzzle-piece mr-1"></i> Nama Part</div>
                                        <div class="info-value text-truncate" title="${item.nama_part || '-'}">${item.nama_part || '-'}</div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-3 col-3 mb-2">
                                    <div class="info-card-box">
                                        <div class="info-label"><i class="fas fa-map-marker-alt mr-1"></i> Tujuan</div>
                                        <div class="info-value text-truncate" title="${item.tujuan || '-'}">${item.tujuan || '-'}</div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-3 col-3 mb-2">
                                    <div class="info-card-box">
                                        <div class="info-label"><i class="fas fa-cogs mr-1"></i> Proses</div>
                                        <div class="info-value text-truncate" title="${item.proses || '-'}">${item.proses || '-'}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Detail Akumulasi Ringkasan Transaksi (Diubah ke Qty Terakhir) -->
                            <div class="row">
                                <!-- DC IN -->
                                <div class="col-xl-3 col-md-6 mb-2">
                                    <div class="p-2 border rounded h-100 bg-white">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="section-badge bg-dc">DC IN</span>
                                            <span class="font-weight-bold text-primary">Qty Terakhir: ${item.dc_qty_hasil || 0}</span>
                                        </div>
                                        <div class="row no-gutters">
                                            <div class="col-4 px-1"><div class="stat-box"><div class="stat-title">AWAL</div><div class="stat-val">${item.dc_qty_awal || 0}</div></div></div>
                                            <div class="col-4 px-1"><div class="stat-box"><div class="stat-title text-danger">REJ</div><div class="stat-val text-danger">${item.dc_qty_reject || 0}</div></div></div>
                                            <div class="col-4 px-1"><div class="stat-box"><div class="stat-title text-success">REP</div><div class="stat-val text-success">${item.dc_qty_replace || 0}</div></div></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SECONDARY INHOUSE -->
                                <div class="col-xl-3 col-md-6 mb-2">
                                    <div class="p-2 border rounded h-100 bg-white">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="section-badge bg-sec-inhouse">SEC. DALAM</span>
                                            <span class="font-weight-bold text-orange">Qty Terakhir: ${secInhouseLastQty}</span>
                                        </div>
                                        <div class="row no-gutters">
                                            <div class="col-3 px-1"><div class="stat-box" style="background:#f3e5f5;"><div class="stat-title">IN</div><div class="stat-val">${item.qty_sec_inhouse_in || 0}</div></div></div>
                                            <div class="col-3 px-1"><div class="stat-box"><div class="stat-title">AWAL</div><div class="stat-val">${item.sec_inhouse_qty_awal || 0}</div></div></div>
                                            <div class="col-3 px-1"><div class="stat-box"><div class="stat-title text-danger">REJ</div><div class="stat-val text-danger">${item.sec_inhouse_qty_reject || 0}</div></div></div>
                                            <div class="col-3 px-1"><div class="stat-box"><div class="stat-title text-success">REP</div><div class="stat-val text-success">${item.sec_inhouse_qty_replace || 0}</div></div></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SECONDARY IN -->
                                <div class="col-xl-3 col-md-6 mb-2">
                                    <div class="p-2 border rounded h-100 bg-white">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="section-badge bg-sec-in">SEC. MASUK</span>
                                            <span class="font-weight-bold text-success">Qty Terakhir: ${item.sec_in_qty_hasil || 0}</span>
                                        </div>
                                        <div class="row no-gutters">
                                            <div class="col-4 px-1"><div class="stat-box"><div class="stat-title">AWAL</div><div class="stat-val">${item.sec_in_qty_awal || 0}</div></div></div>
                                            <div class="col-4 px-1"><div class="stat-box"><div class="stat-title text-danger">REJ</div><div class="stat-val text-danger">${item.sec_in_qty_reject || 0}</div></div></div>
                                            <div class="col-4 px-1"><div class="stat-box"><div class="stat-title text-success">REP</div><div class="stat-val text-success">${item.sec_in_qty_replace || 0}</div></div></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SECONDARY UPDATE -->
                                <div class="col-xl-3 col-md-6 mb-2">
                                    <div class="p-2 border rounded h-100 bg-white">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="section-badge bg-sec-update">SEC. UPDATE</span>
                                            <span class="font-weight-bold text-danger">Qty Terakhir: ${item.update_qty_hasil || 0}</span>
                                        </div>
                                        <div class="row no-gutters">
                                            <div class="col-6 px-1"><div class="stat-box"><div class="stat-title text-danger">REJ</div><div class="stat-val text-danger">${item.update_reject || 0}</div></div></div>
                                            <div class="col-6 px-1"><div class="stat-box"><div class="stat-title text-success">REP</div><div class="stat-val text-success">${item.update_replace || 0}</div></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function openDetailModal(index) {
            let item = window.currentStockerData[index];

            // Populasi Header Info Modal
            $('#modal-id-stocker').text(item.id_qr_stocker || '-');
            $('#modal-ws').text(item.ws || '-');
            $('#modal-color').text(item.color || '-');
            $('#modal-size').text(item.size || '-');
            $('#modal-nama-part').text(item.nama_part || '-');
            $('#modal-panel').text(item.panel || '-');
            $('#modal-part-status').html(item.part_status ? `<span class="badge badge-primary">${item.part_status.toUpperCase()}</span>` : '-');

            // 1. DC IN
            let htmlDc = '';
            (item.history_dc || []).forEach((row, i) => {
                htmlDc += `<tr>
                    <td>${i+1}</td>
                    <td>${row.tgl_trans || '-'}</td>
                    <td>${row.created_at || '-'}</td>
                    <td>${row.qty_awal ?? 0}</td>
                    <td>${row.qty_reject ?? 0}</td>
                    <td>${row.qty_replace ?? 0}</td>
                    <td class="font-weight-bold">${row.hasil ?? 0}</td>
                </tr>`;
            });
            $('#tbody-dc').html(htmlDc || '<tr><td colspan="7" class="text-muted">Tidak ada transaksi DC IN</td></tr>');

            // 2. SEC INHOUSE IN
            let htmlSecInhouseIn = '';
            (item.history_sec_inhouse_in || []).forEach((row, i) => {
                htmlSecInhouseIn += `<tr>
                    <td>${i+1}</td>
                    <td>${row.tgl_trans || '-'}</td>
                    <td>${row.created_at || '-'}</td>
                    <td>${row.tujuan || '-'}</td>
                    <td>${row.proses || '-'}</td>
                    <td class="font-weight-bold">${row.qty_in ?? 0}</td>
                </tr>`;
            });
            $('#tbody-sec-inhouse-in').html(htmlSecInhouseIn || '<tr><td colspan="6" class="text-muted">Tidak ada transaksi Sec. Dalam IN</td></tr>');

            // 3. SEC INHOUSE
            let htmlSecInhouse = '';
            (item.history_sec_inhouse || []).forEach((row, i) => {
                htmlSecInhouse += `<tr>
                    <td>${i+1}</td>
                    <td>${row.tgl_trans || '-'}</td>
                    <td>${row.created_at || '-'}</td>
                    <td>${row.tujuan || '-'}</td>
                    <td>${row.proses || '-'}</td>
                    <td>${row.qty_awal ?? 0}</td>
                    <td>${row.qty_reject ?? 0}</td>
                    <td>${row.qty_replace ?? 0}</td>
                    <td class="font-weight-bold">${row.hasil ?? 0}</td>
                </tr>`;
            });
            $('#tbody-sec-inhouse').html(htmlSecInhouse || '<tr><td colspan="9" class="text-muted">Tidak ada transaksi Sec. Dalam OUT</td></tr>');

            // 4. SEC IN
            let htmlSecIn = '';
            (item.history_sec_in || []).forEach((row, i) => {
                htmlSecIn += `<tr>
                    <td>${i+1}</td>
                    <td>${row.tgl_trans || '-'}</td>
                    <td>${row.created_at || '-'}</td>
                    <td>${row.tujuan || '-'}</td>
                    <td>${row.proses || '-'}</td>
                    <td>${row.qty_awal ?? 0}</td>
                    <td>${row.qty_reject ?? 0}</td>
                    <td>${row.qty_replace ?? 0}</td>
                    <td class="font-weight-bold">${row.hasil ?? 0}</td>
                </tr>`;
            });
            $('#tbody-sec-in').html(htmlSecIn || '<tr><td colspan="9" class="text-muted">Tidak ada transaksi Secondary IN</td></tr>');

            // 5. SEC UPDATE
            let htmlSecUpdate = '';
            (item.history_sec_update || []).forEach((row, i) => {
                htmlSecUpdate += `<tr>
                    <td>${i+1}</td>
                    <td>${row.tgl_trans || '-'}</td>
                    <td>${row.created_at || '-'}</td>
                    <td>${row.tujuan || '-'}</td>
                    <td>${row.proses || '-'}</td>
                    <td>${row.reject ?? 0}</td>
                    <td>${row.replace ?? 0}</td>
                    <td class="font-weight-bold">${row.hasil ?? 0}</td>
                </tr>`;
            });
            $('#tbody-sec-update').html(htmlSecUpdate || '<tr><td colspan="8" class="text-muted">Tidak ada data Secondary Update</td></tr>');

            // Tab Reset & Show
            $('#transaksiTab a:first').tab('show');
            $('#modalDetailTransaksi').modal('show');
        }

        function resetFilter() {
            $('#id_qr_stocker').val('');
            showEmptyState();
        }
    </script>
@endsection