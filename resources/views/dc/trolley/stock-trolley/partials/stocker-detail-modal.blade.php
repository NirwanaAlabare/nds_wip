{{--
    Modal detail transaksi stocker (DC IN s/d Secondary), data dari route 'get-stocker-process'.
    Dibuka lewat klik No. Stocker (.stocker-detail-link), lihat openStockerDetail() di partials/stock-table-script.
    Letakkan di luar <form>.
--}}
<style>
    /* Link No. Stocker di tabel */
    .stock-table .stocker-detail-link {
        padding: 0;
        border: 0;
        background: none;
        color: #0d6efd;
        font-weight: 700;
        text-align: left;
        text-decoration: underline dotted;
        text-underline-offset: 3px;
        cursor: pointer;
    }

    .stock-table .stocker-detail-link:hover {
        color: #0a58ca;
        text-decoration-style: solid;
    }

    /* Pilihan stocker dalam satu grup */
    .sd-stocker-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
    }

    .sd-stocker-tab {
        padding: .25rem .65rem;
        border: 1px solid #ced4da;
        border-radius: 999px;
        background-color: #fff;
        color: #495057;
        font-size: .8rem;
        font-weight: 600;
        cursor: pointer;
    }

    .sd-stocker-tab .sd-stocker-tab-part {
        font-weight: 400;
        opacity: .75;
    }

    .sd-stocker-tab {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
    }

    .sd-stocker-tab.active {
        border-color: #0d6efd;
        background-color: #0d6efd;
        color: #fff;
    }

    /* Qty terakhir di pill */
    .sd-stocker-tab-qty {
        min-width: 1.6rem;
        margin-left: .15rem;
        padding: .05rem .45rem;
        border-radius: 999px;
        background-color: #e9ecef;
        color: #212529;
        font-weight: 700;
        text-align: center;
    }

    .sd-stocker-tab.active .sd-stocker-tab-qty {
        background-color: #fff;
        color: #0d6efd;
    }

    /* Qty terkecil di grup (pembatas qty alokasi) */
    .sd-stocker-tab-qty.is-min,
    .sd-stocker-tab.active .sd-stocker-tab-qty.is-min {
        background-color: #dc3545;
        color: #fff;
    }

    /* Info stocker */
    .sd-info-box {
        height: 100%;
        padding: .4rem .65rem;
        border: 1px solid #e9ecef;
        border-radius: .375rem;
        background-color: #fff;
    }

    .sd-info-label {
        margin-bottom: 2px;
        color: #888;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
    }

    .sd-info-value {
        color: #2b2b2b;
        font-size: 13px;
        font-weight: 700;
        word-break: break-word;
    }

    /* Ringkasan per tahap */
    .sd-stage {
        height: 100%;
        padding: .5rem;
        border: 1px solid #dee2e6;
        border-radius: .375rem;
        background-color: #fff;
    }

    .sd-stage-badge {
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
    }

    .sd-stat {
        padding: 5px;
        border-radius: 6px;
        background-color: #f8f9fa;
        text-align: center;
    }

    .sd-stat-title {
        color: #6c757d;
        font-size: 10px;
        font-weight: 700;
    }

    .sd-stat-val {
        font-size: 13px;
        font-weight: 700;
    }

    .sd-bg-dc { background-color: #e3f2fd; color: #0d47a1; }
    .sd-bg-sec-inhouse-in { background-color: #f3e5f5; color: #4a148c; }
    .sd-bg-sec-inhouse { background-color: #fff3e0; color: #e65100; }
    .sd-bg-sec-in { background-color: #e8f5e9; color: #1b5e20; }
    .sd-bg-sec-update { background-color: #ffebee; color: #b71c1c; }

    /* Tabel riwayat */
    .sd-history-table th,
    .sd-history-table td {
        font-size: 12px;
        text-align: center;
        vertical-align: middle !important;
        white-space: nowrap;
    }

    #stockerDetailTab .nav-link {
        font-size: .85rem;
        font-weight: 700;
    }
</style>

<div class="modal fade" id="stockerDetailModal" tabindex="-1" aria-labelledby="stockerDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header bg-sb text-light py-2">
                <h5 class="modal-title fw-bold" id="stockerDetailModalLabel"><i class="fas fa-history fa-sm"></i> Detail Transaksi Stocker</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Loading / pesan --}}
                <div class="text-center text-muted py-5 d-none" id="sd-loading">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 mb-0">Memuat data transaksi stocker...</p>
                </div>
                <div class="text-center text-muted py-5 d-none" id="sd-message"></div>

                <div class="d-none" id="sd-content">
                    {{-- Stocker lain dalam grup yang sama --}}
                    <div class="mb-3">
                        <div class="sd-info-label mb-1">Stocker dalam grup ini</div>
                        <div class="sd-stocker-tabs" id="sd-stocker-tabs"></div>
                    </div>

                    {{-- Info stocker --}}
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2" id="sd-badges"></div>
                    <div class="row g-2 mb-3">
                        <div class="col-lg-3 col-md-4 col-6"><div class="sd-info-box"><div class="sd-info-label">Work Order / WS</div><div class="sd-info-value" id="sd-ws">-</div></div></div>
                        <div class="col-lg-3 col-md-4 col-6"><div class="sd-info-box"><div class="sd-info-label">Color</div><div class="sd-info-value" id="sd-color">-</div></div></div>
                        <div class="col-lg-2 col-md-4 col-6"><div class="sd-info-box"><div class="sd-info-label">Size</div><div class="sd-info-value" id="sd-size">-</div></div></div>
                        <div class="col-lg-2 col-md-6 col-6"><div class="sd-info-box"><div class="sd-info-label">Panel</div><div class="sd-info-value" id="sd-panel">-</div></div></div>
                        <div class="col-lg-2 col-md-6 col-12"><div class="sd-info-box"><div class="sd-info-label">Nama Part</div><div class="sd-info-value" id="sd-nama-part">-</div></div></div>
                        <div class="col-md-6 col-12"><div class="sd-info-box"><div class="sd-info-label">Tujuan</div><div class="sd-info-value" id="sd-tujuan">-</div></div></div>
                        <div class="col-md-6 col-12"><div class="sd-info-box"><div class="sd-info-label">Proses</div><div class="sd-info-value" id="sd-proses">-</div></div></div>
                    </div>

                    {{-- Ringkasan per tahap --}}
                    <div class="row g-2 mb-3" id="sd-stages"></div>

                    {{-- Riwayat per tahap --}}
                    <ul class="nav nav-tabs" id="stockerDetailTab" role="tablist">
                        <li class="nav-item" role="presentation"><button class="nav-link active text-primary" data-bs-toggle="tab" data-bs-target="#sd-tab-dc" type="button" role="tab">DC IN <span class="badge bg-secondary" id="sd-count-dc">0</span></button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link text-purple" data-bs-toggle="tab" data-bs-target="#sd-tab-sec-inhouse-in" type="button" role="tab">Sec. Dalam IN <span class="badge bg-secondary" id="sd-count-sec-inhouse-in">0</span></button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link text-orange" data-bs-toggle="tab" data-bs-target="#sd-tab-sec-inhouse" type="button" role="tab">Sec. Dalam OUT <span class="badge bg-secondary" id="sd-count-sec-inhouse">0</span></button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link text-success" data-bs-toggle="tab" data-bs-target="#sd-tab-sec-in" type="button" role="tab">Terima Secondary <span class="badge bg-secondary" id="sd-count-sec-in">0</span></button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link text-danger" data-bs-toggle="tab" data-bs-target="#sd-tab-sec-update" type="button" role="tab">Secondary Update <span class="badge bg-secondary" id="sd-count-sec-update">0</span></button></li>
                    </ul>
                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="sd-tab-dc" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped sd-history-table w-100">
                                    <thead class="sd-bg-dc"><tr><th>No</th><th>Tgl Transaksi</th><th>Created At</th><th>Qty Awal</th><th>Qty Reject</th><th>Qty Replace</th><th>Qty Hasil Net</th></tr></thead>
                                    <tbody id="sd-tbody-dc"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="sd-tab-sec-inhouse-in" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped sd-history-table w-100">
                                    <thead class="sd-bg-sec-inhouse-in"><tr><th>No</th><th>Tgl Transaksi</th><th>Created At</th><th>Tujuan</th><th>Proses</th><th>Qty IN</th></tr></thead>
                                    <tbody id="sd-tbody-sec-inhouse-in"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="sd-tab-sec-inhouse" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped sd-history-table w-100">
                                    <thead class="sd-bg-sec-inhouse"><tr><th>No</th><th>Tgl Transaksi</th><th>Created At</th><th>Tujuan</th><th>Proses</th><th>Qty Awal</th><th>Qty Reject</th><th>Qty Replace</th><th>Qty Hasil Net</th></tr></thead>
                                    <tbody id="sd-tbody-sec-inhouse"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="sd-tab-sec-in" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped sd-history-table w-100">
                                    <thead class="sd-bg-sec-in"><tr><th>No</th><th>Tgl Transaksi</th><th>Created At</th><th>Tujuan Asal</th><th>Proses</th><th>Qty Awal</th><th>Qty Reject</th><th>Qty Replace</th><th>Qty Hasil Net</th></tr></thead>
                                    <tbody id="sd-tbody-sec-in"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="sd-tab-sec-update" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped sd-history-table w-100">
                                    <thead class="sd-bg-sec-update"><tr><th>No</th><th>Tgl Transaksi</th><th>Created At</th><th>Tujuan</th><th>Proses</th><th>Reject</th><th>Replace</th><th>Qty Hasil Net</th></tr></thead>
                                    <tbody id="sd-tbody-sec-update"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
