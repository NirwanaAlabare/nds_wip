@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        /* ===== Listing ===== */
        #datatable td.col-jenis {
            white-space: normal;
            min-width: 260px;
        }

        .badge-jenis {
            background: #e7f1ff;
            color: #084298;
            border: 1px solid #b6d4fe;
            font-weight: 600;
            margin: 1px 0;
        }

        .badge-jenis-qty {
            background: #084298;
            color: #fff;
            border-radius: 3px;
            padding: 1px 5px;
            margin-left: 2px;
        }

        /* ===== Preview Machine Requirement ===== */
        @media (min-width: 1200px) {
            .modal-preview {
                max-width: 96%;
            }
        }

        .preview-card {
            border: 0;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
        }

        .preview-card .card-header {
            background: #fff;
            border-bottom: 2px solid #e9ecef;
        }

        .preview-card .card-footer {
            background: #fff;
        }

        /* Tabel panjang (stok beli 40+ jenis) di-scroll di dalam card, header & total tetap kelihatan */
        .card-scroll {
            max-height: 420px;
            overflow-y: auto;
        }

        .card-scroll thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background-color: var(--sb-color);
        }

        .card-scroll tfoot th {
            position: sticky;
            bottom: 0;
            z-index: 1;
        }

        .table-preview th,
        .table-preview td {
            vertical-align: middle;
        }

        .table-preview thead th {
            text-align: center;
            white-space: nowrap;
        }

        .table-preview tr.row-kurang td {
            background: #fdf1f2;
        }

        .table-preview td.cell-kurang {
            background: #f8d7da;
            color: #b02a37;
            font-weight: 700;
        }

        .table-preview thead th.th-kurang {
            background: #dc3545;
            color: #fff;
        }

        .table-preview tfoot .row-total th {
            background: #e9ecef;
            border-top: 2px solid #212529;
        }

        /* KPI */
        .kpi-card {
            border-left: 4px solid #0d6efd;
        }

        .kpi-card.kpi-ok {
            border-left-color: #198754;
        }

        .kpi-card.kpi-bad {
            border-left-color: #dc3545;
        }

        .kpi-card.kpi-bad .kpi-value {
            color: #dc3545;
        }

        .kpi-card.kpi-ok .kpi-value {
            color: #198754;
        }

        .kpi-label {
            font-size: .75rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .kpi-value {
            font-size: 1.6rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .kpi-icon {
            font-size: 2rem;
            opacity: .2;
        }

    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-list-check"></i> Machine Requirement</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#AddRequirementModal">
                    <i class="fas fa-plus"></i> New
                </button>
                <button type="button" class="btn btn-info btn-sm text-white" onclick="previewRequirement();">
                    <i class="fa-solid fa-table-cells"></i> Preview
                </button>
            </div>
            <div class="mb-3 d-flex align-items-end gap-2 flex-wrap">
                <div>
                    <label for="txtfilter_awal" class="col-form-label"><small><b>Tgl Awal :</b></small></label>
                    <input type="date" id="txtfilter_awal" class="form-control form-control-sm"
                        value="{{ date('Y-m-01') }}">
                </div>
                <div>
                    <label for="txtfilter_akhir" class="col-form-label"><small><b>Tgl Akhir :</b></small></label>
                    <input type="date" id="txtfilter_akhir" class="form-control form-control-sm"
                        value="{{ date('Y-m-t') }}">
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="dataTableReload();">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th class="text-center align-middle">Periode</th>
                            <th class="text-center align-middle">Status</th>
                            <th class="text-center align-middle">Style</th>
                            <th class="text-center align-middle">Line</th>
                            <th class="text-center align-middle">Jenis Mesin</th>
                            <th class="text-center align-middle">Total Qty</th>
                            <th class="text-center align-middle">Dibuat</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Machine Requirement -->
    <div class="modal fade" id="AddRequirementModal" tabindex="-1" aria-labelledby="AddRequirementModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="AddRequirementModalLabel">
                        <i class="fa-solid fa-list-check"></i> New Machine Requirement
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label><small><b>Tgl. Plan :</b></small></label>
                            <div class="input-group input-group-sm">
                                <input type="date" id="txttgl_awal" name="tgl_awal" class="form-control form-control-sm"
                                    value="{{ date('Y-m-d') }}">
                                <span class="input-group-text">s/d</span>
                                <input type="date" id="txttgl_akhir" name="tgl_akhir" class="form-control form-control-sm"
                                    value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="txttotal_days"><small><b>Total Days :</b></small></label>
                            <input type="text" id="txttotal_days" name="total_days" class="form-control form-control-sm"
                                value="1" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="cbostyle"><small><b>Style :</b></small></label>
                            <select id="cbostyle" name="style" class="form-control form-control-sm select2bs4">
                            </select>

                        </div>
                        <div class="col-md-3">
                            <label for="cboline"><small><b>Line :</b></small></label>
                            <select id="cboline" name="line" class="form-control form-control-sm select2bs4">
                                <option value="">-- Pilih Line --</option>
                                @foreach ($lineList as $row)
                                    <option value="{{ $row->id }}">{{ $row->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small><b>Kebutuhan Mesin :</b></small>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="tambahBarisJenis();">
                            <i class="fas fa-plus"></i> Tambah Jenis
                        </button>
                    </div>
                    <table class="table table-bordered table-sm mb-0" id="tableJenis">
                        <thead>
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th>Jenis Mesin</th>
                                <th style="width: 150px;">Qty</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-end">Total Qty</th>
                                <th id="totalQty">0</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-sm" id="saveRequirementButton"
                        onclick="simpanRequirement();">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Preview: kebutuhan per style & jenis mesin vs stok (tersedia / kurang) -->
    <div class="modal fade" id="PreviewRequirementModal" tabindex="-1" aria-labelledby="PreviewRequirementModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-preview">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="PreviewRequirementModalLabel">
                        <i class="fa-solid fa-table-cells"></i> Preview Machine Requirement
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light">
                    <!-- Periode -->
                    <div class="card preview-card mb-3">
                        <div class="card-body py-2 d-flex align-items-end gap-2 flex-wrap">
                            <div>
                                <label for="txtpreview_dari" class="col-form-label"><small><b>Dari :</b></small></label>
                                <input type="date" id="txtpreview_dari" class="form-control form-control-sm"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <div>
                                <label for="txtpreview_sampai" class="col-form-label"><small><b>Sampai :</b></small></label>
                                <input type="date" id="txtpreview_sampai" class="form-control form-control-sm"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" onclick="loadPreview();">
                                <i class="fas fa-search"></i> Tampilkan
                            </button>
                            <div class="btn-group btn-group-sm ms-md-2">
                                <button type="button" class="btn btn-outline-secondary" onclick="setPeriodePreview('hari');">Hari Ini</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="setPeriodePreview('minggu');">Minggu Ini</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="setPeriodePreview('bulan');">Bulan Ini</button>
                            </div>
                            <div class="form-check form-switch ms-auto mb-1">
                                <input class="form-check-input" type="checkbox" id="chkhanya_kurang" onchange="renderSemua();">
                                <label class="form-check-label small fw-bold text-danger" for="chkhanya_kurang">
                                    Tampilkan yang kurang saja
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="previewContent">
                        <!-- KPI -->
                        <div class="row g-3 mb-3" id="kpiRow"></div>

                        <!-- Requirement per Style -->
                        <div class="card preview-card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <span class="fw-bold"><i class="fa-solid fa-table"></i> Requirement per Style</span>
                                <div style="min-width: 300px;">
                                    <select id="cbofilter_style" class="form-control form-control-sm" multiple></select>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="alert py-2 mb-2" id="pivotAlert"></div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0 table-preview" id="tablePivotStyle">
                                        <thead class="bg-sb"></thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Stok semua jenis mesin: beli & sewa dipisah, dibanding pemakaian di periode ini -->
                        <div class="card preview-card">
                            <div class="card-header fw-bold">
                                <i class="fa-solid fa-gears"></i> Stok Mesin
                            </div>
                            <div class="card-body">
                                <div class="alert py-2 mb-2" id="previewStatus"></div>
                                <div class="table-responsive card-scroll">
                                    <table class="table table-bordered table-sm mb-0 table-preview" id="tableStokMesin">
                                        <thead class="bg-sb">
                                            <tr>
                                                <th>Jenis Mesin</th>
                                                <th>Beli</th>
                                                <th>Sewa</th>
                                                <th>Total Stok</th>
                                                <th>Pemakaian</th>
                                                <th>Sisa</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer small text-muted">
                                Pemakaian = jumlah mesin di hari paling sibuk dalam periode ini (plan yang tidak berjalan
                                bersamaan tidak dijumlah). Mesin sewa terhitung kalau nama jenisnya sama dengan kode jenis.
                                <span id="infoSewaBelum"></span>
                            </div>
                        </div>
                    </div>

                    <div id="previewEmpty" class="card preview-card text-center text-muted py-5 d-none">
                        <i class="fa-regular fa-folder-open fa-2x mb-2"></i>
                        <div>Tidak ada requirement di periode ini</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Template baris jenis mesin, di-clone tiap klik "Tambah Jenis" -->
    <template id="templateBarisJenis">
        <tr>
            <td class="text-center align-middle nomor-baris"></td>
            <td>
                <select class="form-control form-control-sm cbo-jenis">
                    <option value="">-- Pilih Jenis --</option>
                    @foreach ($jenisList as $row)
                        <option value="{{ $row->id_jenis }}">{{ $row->kd_jenis }} - {{ $row->nm_jenis }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm txt-qty" min="1" value="1">
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-danger btn-sm" onclick="hapusBarisJenis(this);">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    </template>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        $(function () {
            // dropdownParent wajib, kalau tidak kotak pencarian select2 tidak bisa diketik di dalam modal
            $('#cboline').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#AddRequirementModal'),
            });
            // Kosong = semua style tampil
            $('#cbofilter_style').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#PreviewRequirementModal'),
                placeholder: 'Semua style',
                allowClear: true,
            }).on('change', function () {
                if (dataPreview) renderSemua();
            });
            // Style ribuan data, jadi dicari lewat ajax sambil mengetik (distinct dari master_sb_ws)
            $('#cbostyle').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#AddRequirementModal'),
                placeholder: 'Ketik style...',
                allowClear: true,
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route('asset_mesin_requirement_style') }}',
                    dataType: 'json',
                    delay: 300,
                    data: params => ({ q: params.term }),
                },
            });

            // Tgl akhir tidak boleh sebelum tgl awal
            $('#txttgl_awal').on('change', function () {
                $('#txttgl_akhir').attr('min', this.value);
                if ($('#txttgl_akhir').val() && $('#txttgl_akhir').val() < this.value) {
                    $('#txttgl_akhir').val(this.value);
                }
                hitungTotalDays();
            });
            $('#txttgl_akhir').on('change', hitungTotalDays);

            // Mulai dengan satu baris supaya user tidak perlu klik "Tambah Jenis" dulu
            tambahBarisJenis();

            $('#datatable').DataTable({
                ordering: true,
                processing: true,
                serverSide: false,
                paging: true,
                searching: true,
                scrollX: true,
                order: [[0, 'desc']],
                ajax: {
                    url: '{{ route('getdata_asset_mesin_requirement') }}',
                    data: function (d) {
                        d.tgl_awal = $('#txtfilter_awal').val();
                        d.tgl_akhir = $('#txtfilter_akhir').val();
                    },
                },
                columns: [
                    // Sorting & pencarian periode tetap pakai tgl_awal asli (yyyy-mm-dd)
                    { data: 'tgl_awal', className: 'text-center', render: renderPeriode },
                    { data: 'tgl_awal', className: 'text-center', orderable: false, render: renderStatusPlan },
                    { data: 'style', className: 'fw-semibold' },
                    { data: 'nama_line', render: renderLine },
                    { data: 'jenis', orderable: false, className: 'col-jenis', render: renderJenisBadge },
                    { data: 'total_qty', className: 'text-center fw-bold' },
                    { data: 'created_by', render: renderDibuat },
                ],
            });
        });

        // Satu tanggal kalau plan cuma sehari, rentang kalau lebih; jumlah hari kecil di bawahnya
        function renderPeriode(data, type, row) {
            if (type !== 'display') return data;
            const periode = row.tgl_awal === row.tgl_akhir
                ? formatTanggal(row.tgl_awal, 'display')
                : `${formatTanggal(row.tgl_awal, 'display')} s/d ${formatTanggal(row.tgl_akhir, 'display')}`;
            return `<div class="text-nowrap">${periode}</div><small class="text-muted">${row.total_days} hari</small>`;
        }

        // Status dihitung dari tanggal hari ini: sudah lewat, sedang berjalan, atau belum mulai
        function renderStatusPlan(data, type, row) {
            const today = '{{ date('Y-m-d') }}';
            const status = row.tgl_akhir < today ? ['Selesai', 'bg-secondary']
                : (row.tgl_awal > today ? ['Akan Datang', 'bg-info text-dark'] : ['Berjalan', 'bg-success']);
            return type === 'display' ? `<span class="badge ${status[1]}">${status[0]}</span>` : status[0];
        }

        function renderLine(data, type, row) {
            if (type !== 'display') return `${row.nama_line ?? ''} ${row.nama_gedung ?? ''}`;
            return `<div class="fw-semibold">${escapeHtml(row.nama_line ?? '-')}</div>
                <small class="text-muted">${escapeHtml(row.nama_gedung ?? '')}</small>`;
        }

        // Jenis mesin jadi badge "KODE qty"; nama lengkap muncul saat kursor diarahkan
        function renderJenisBadge(data, type) {
            if (type !== 'display') return data.map(j => `${j.kd_jenis} ${j.nm_jenis}`).join(' ');
            return data.map(j => `<span class="badge badge-jenis" title="${escapeHtml(j.nm_jenis)}">
                    ${escapeHtml(j.kd_jenis)} <span class="badge-jenis-qty">${j.qty}</span></span>`).join(' ');
        }

        function renderDibuat(data, type, row) {
            if (type !== 'display') return data;
            const waktu = row.created_at
                ? `${formatTanggal(row.created_at.slice(0, 10), 'display')} ${row.created_at.slice(11, 16)}`
                : '';
            return `<div>${escapeHtml(data ?? '-')}</div><small class="text-muted text-nowrap">${waktu}</small>`;
        }

        // Tampil dd-mm-yyyy, tapi sorting tetap pakai nilai asli yyyy-mm-dd supaya urutannya benar
        function formatTanggal(data, type) {
            if (type === 'sort' || type === 'type' || !data) {
                return data;
            }
            const [y, m, d] = data.split('-');
            return `${d}-${m}-${y}`;
        }

        const escapeHtml = text => $('<div>').text(text ?? '').html();

        // Tanggal diolah sebagai string yyyy-mm-dd lewat UTC supaya tidak geser karena zona waktu
        function toIsoDate(date) {
            return date.toISOString().slice(0, 10);
        }

        function daftarTanggal(dari, sampai) {
            const hasil = [];
            const cursor = new Date(dari + 'T00:00:00Z');
            const akhir = new Date(sampai + 'T00:00:00Z');
            while (cursor <= akhir) {
                hasil.push(toIsoDate(cursor));
                cursor.setUTCDate(cursor.getUTCDate() + 1);
            }
            return hasil;
        }

        function previewRequirement() {
            $('#PreviewRequirementModal').modal('show');
            loadPreview();
        }

        function setPeriodePreview(jenis) {
            const now = new Date();
            const today = new Date(Date.UTC(now.getFullYear(), now.getMonth(), now.getDate()));
            let dari = new Date(today);
            let sampai = new Date(today);

            if (jenis === 'minggu') {
                // Senin s/d Minggu di minggu berjalan
                const offset = (today.getUTCDay() + 6) % 7;
                dari.setUTCDate(today.getUTCDate() - offset);
                sampai = new Date(dari);
                sampai.setUTCDate(dari.getUTCDate() + 6);
            } else if (jenis === 'bulan') {
                dari = new Date(Date.UTC(today.getUTCFullYear(), today.getUTCMonth(), 1));
                sampai = new Date(Date.UTC(today.getUTCFullYear(), today.getUTCMonth() + 1, 0));
            }

            $('#txtpreview_dari').val(toIsoDate(dari));
            $('#txtpreview_sampai').val(toIsoDate(sampai));
            loadPreview();
        }

        // Semua data preview yang sedang tampil. Filter style & toggle "kurang saja" cuma mengubah
        // tampilan dari data ini, tidak request ulang ke server.
        let dataPreview = null;

        function loadPreview() {
            const dari = $('#txtpreview_dari').val();
            const sampai = $('#txtpreview_sampai').val();
            if (!dari || !sampai) {
                Swal.fire({ icon: 'warning', title: 'Isi tanggal Dari dan Sampai' });
                return;
            }

            $.ajax({
                type: 'GET',
                url: '{{ route('preview_asset_mesin_requirement') }}',
                data: { tgl_dari: dari, tgl_sampai: sampai },
                success: function (response) {
                    const tanggal = daftarTanggal(dari, sampai);
                    dataPreview = {
                        ...response,
                        tanggal,
                        ...hitungAlokasi(response.jenis, response.rows, tanggal),
                    };
                    isiFilterStyle(response.rows);
                    renderSemua();
                },
                error: function (xhr) {
                    const errors = xhr.responseJSON?.errors;
                    const message = errors ? Object.values(errors)[0][0] : (xhr.responseJSON?.message || 'Gagal memuat preview');
                    Swal.fire({ icon: 'error', title: message });
                },
            });
        }

        // Stok satu jenis mesin dipakai bersama oleh semua style. Tiap hari, stok dibagi ke plan yang mulai
        // lebih awal dulu (kalau sama, yang diinput lebih dulu). Hasil:
        // - kurangPerRow[index][kd]: kekurangan terbesar baris requirement itu di sepanjang harinya
        // - kebutuhan[kd]: total kebutuhan di hari paling sibuk (plan beda hari tidak dijumlah)
        function hitungAlokasi(jenis, rows, tanggal) {
            const urut = rows
                .map((row, index) => index)
                .sort((a, b) => rows[a].tgl_awal.localeCompare(rows[b].tgl_awal) || rows[a].urutan - rows[b].urutan);
            const kurangPerRow = rows.map(() => ({}));
            const kebutuhan = {};

            jenis.forEach(j => {
                const stok = stokJenis(j);
                kebutuhan[j.kd_jenis] = 0;

                tanggal.forEach(t => {
                    let sisa = stok;
                    let total = 0;

                    urut.forEach(index => {
                        const row = rows[index];
                        const qty = row.qty[j.kd_jenis];
                        if (!qty || row.tgl_awal > t || row.tgl_akhir < t) return;

                        const dapat = Math.min(qty, Math.max(sisa, 0));
                        sisa -= dapat;
                        total += qty;
                        kurangPerRow[index][j.kd_jenis] = Math.max(kurangPerRow[index][j.kd_jenis] || 0, qty - dapat);
                    });

                    kebutuhan[j.kd_jenis] = Math.max(kebutuhan[j.kd_jenis], total);
                });
            });

            return { kurangPerRow, kebutuhan };
        }

        // Pilihan filter diisi dari style yang ada di periode ini; pilihan sebelumnya dipertahankan kalau masih ada
        function isiFilterStyle(rows) {
            const $filter = $('#cbofilter_style');
            const terpilih = $filter.val() || [];
            const daftar = [...new Set(rows.map(row => row.style))].sort();

            $filter.empty();
            daftar.forEach(style => $filter.append(new Option(style, style, false, terpilih.includes(style))));
            $filter.trigger('change.select2');
        }

        const hanyaKurang = () => $('#chkhanya_kurang').is(':checked');

        // Stok satu jenis = mesin beli + mesin sewa yang nm_jenis-nya sudah sama dengan kode jenis
        const stokJenis = j => Number(j.stok_beli) + Number(j.stok_sewa);

        function renderSemua() {
            if (!dataPreview) return;

            if (!dataPreview.rows.length) {
                $('#previewContent').addClass('d-none');
                $('#previewEmpty').removeClass('d-none');
                return;
            }
            $('#previewContent').removeClass('d-none');
            $('#previewEmpty').addClass('d-none');

            renderKpi();
            renderPivotStyle();
            renderStokMesin();
        }

        function kartuKpi(label, nilai, keterangan, icon, warna) {
            return `<div class="col-6 col-xl-3">
                <div class="card preview-card kpi-card kpi-${warna} h-100">
                    <div class="card-body py-2 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="kpi-label">${label}</div>
                            <div class="kpi-value">${nilai}</div>
                            <small class="text-muted">${keterangan}</small>
                        </div>
                        <i class="fa-solid ${icon} kpi-icon"></i>
                    </div>
                </div>
            </div>`;
        }

        function renderKpi() {
            const { jenis, rows, kebutuhan, kurangPerRow } = dataPreview;

            const totalKebutuhan = jenis.reduce((sum, j) => sum + kebutuhan[j.kd_jenis], 0);
            const totalStok = jenis.reduce((sum, j) => sum + stokJenis(j), 0);
            const jenisKurang = jenis.filter(j => stokJenis(j) < kebutuhan[j.kd_jenis]).length;

            const semuaStyle = new Set(rows.map(row => row.style));
            const styleMasalah = new Set(rows
                .filter((row, index) => Object.values(kurangPerRow[index]).some(k => k > 0))
                .map(row => row.style));

            $('#kpiRow').html(
                kartuKpi('Total Kebutuhan', totalKebutuhan, 'unit mesin di hari tersibuk', 'fa-gears', 'info') +
                kartuKpi('Stok Tersedia', totalStok, `untuk ${jenis.length} jenis yang dibutuhkan`, 'fa-warehouse', 'info') +
                kartuKpi('Jenis Kurang', `${jenisKurang} / ${jenis.length}`,
                    jenisKurang ? 'jenis mesin stoknya tidak cukup' : 'semua jenis tercukupi',
                    'fa-triangle-exclamation', jenisKurang ? 'bad' : 'ok') +
                kartuKpi('Style Bermasalah', `${styleMasalah.size} / ${semuaStyle.size}`,
                    styleMasalah.size ? 'style tidak dapat mesin cukup' : 'semua style aman',
                    'fa-shirt', styleMasalah.size ? 'bad' : 'ok')
            );
        }

        // Tabel ala Excel: kolom = style (tanggal awal plan di bawahnya), baris = line lalu jenis mesin.
        // Style yang sama di beberapa periode digabung jadi satu kolom. Kalau di line yang sama, qty per jenis
        // diambil yang terbesar (bukan dijumlah) karena mesinnya dipakai ulang, bukan ditambah.
        // Filter style / kurang saja hanya menyaring tampilan; alokasi tetap dihitung dari semua style.
        function renderPivotStyle() {
            const { jenis, rows, kurangPerRow } = dataPreview;
            const filter = $('#cbofilter_style').val() || [];
            const kurangSaja = hanyaKurang();
            const styles = {};
            const lines = {};

            rows.forEach((row, index) => {
                if (filter.length && !filter.includes(row.style)) return;

                Object.entries(row.qty).forEach(([kdJenis, qty]) => {
                    const kurang = kurangPerRow[index][kdJenis] || 0;
                    if (kurangSaja && !kurang) return;

                    const infoStyle = (styles[row.style] ??= { tgl: row.tgl_awal, kurang: false });
                    if (row.tgl_awal < infoStyle.tgl) infoStyle.tgl = row.tgl_awal;
                    if (kurang) infoStyle.kurang = true;

                    const line = (lines[row.nama_lokasi] ??= {});
                    const sel = ((line[kdJenis] ??= {})[row.style] ??= { qty: 0, kurang: 0 });
                    sel.qty = Math.max(sel.qty, qty);
                    sel.kurang = Math.max(sel.kurang, kurang);
                });
            });

            const daftarStyle = Object.keys(styles).sort((a, b) =>
                styles[a].tgl.localeCompare(styles[b].tgl) || a.localeCompare(b)
            );

            if (!daftarStyle.length) {
                $('#tablePivotStyle thead, #tablePivotStyle tfoot').html('');
                $('#tablePivotStyle tbody').html(`<tr><td class="text-center text-muted py-3">
                    ${kurangSaja ? 'Tidak ada kekurangan mesin' : 'Tidak ada data sesuai filter'}</td></tr>`);
                renderAlertPivot([]);
                return;
            }

            const urutanJenis = jenis.map(j => j.kd_jenis);

            let head = `<tr>
                <th rowspan="2" class="align-middle">Line</th>
                <th rowspan="2" class="align-middle">Jenis Mesin</th>`;
            daftarStyle.forEach(style => head += styles[style].kurang
                ? `<th class="th-kurang"><i class="fa-solid fa-triangle-exclamation"></i> ${escapeHtml(style)}</th>`
                : `<th>${escapeHtml(style)}</th>`);
            head += '</tr><tr>';
            daftarStyle.forEach(style => head += `<th class="fw-normal">${formatTanggal(styles[style].tgl, 'display')}</th>`);
            head += '</tr>';

            const totalPerStyle = {};
            const pesanKurang = [];
            let body = '';
            Object.entries(lines).forEach(([namaLine, perJenis]) => {
                const jenisLine = urutanJenis.filter(kd => perJenis[kd]);
                jenisLine.forEach((kdJenis, i) => {
                    body += '<tr>';
                    if (i === 0) {
                        body += `<td rowspan="${jenisLine.length}" class="fw-bold">${escapeHtml(namaLine)}</td>`;
                    }
                    body += `<td>${escapeHtml(kdJenis)}</td>`;
                    daftarStyle.forEach(style => {
                        const sel = perJenis[kdJenis][style];
                        if (!sel) {
                            body += '<td></td>';
                            return;
                        }

                        totalPerStyle[style] = (totalPerStyle[style] || 0) + sel.qty;
                        if (!sel.kurang) {
                            body += `<td class="text-end">${sel.qty}</td>`;
                            return;
                        }

                        const dapat = sel.qty - sel.kurang;
                        pesanKurang.push({ style, namaLine, kdJenis, qty: sel.qty, dapat, kurang: sel.kurang });
                        body += `<td class="text-end cell-kurang" title="Butuh ${sel.qty}, dapat ${dapat}, kurang ${sel.kurang}">
                            ${sel.qty} <small>(-${sel.kurang})</small>
                        </td>`;
                    });
                    body += '</tr>';
                });
            });

            let foot = '<tr class="row-total"><th colspan="2">TOTAL</th>';
            daftarStyle.forEach(style => foot += `<th class="text-end">${totalPerStyle[style] || 0}</th>`);
            foot += '</tr>';

            $('#tablePivotStyle thead').html(head);
            $('#tablePivotStyle tbody').html(body);
            $('#tablePivotStyle tfoot').html(foot);

            renderAlertPivot(pesanKurang);
        }

        function renderAlertPivot(pesanKurang) {
            $('#pivotAlert')
                .removeClass('alert-success alert-danger')
                .addClass(pesanKurang.length ? 'alert-danger' : 'alert-success')
                .html(pesanKurang.length
                    ? `<i class="fa-solid fa-triangle-exclamation"></i> <b>Stok mesin tidak cukup untuk ${pesanKurang.length} kebutuhan</b>
                       <small class="d-block">Stok dibagi ke plan yang mulai lebih awal dulu.</small>
                       <ul class="mb-0 mt-1">${pesanKurang.map(p => `<li>
                            <b>${escapeHtml(p.style)}</b> - ${escapeHtml(p.namaLine)} - <b>${escapeHtml(p.kdJenis)}</b>:
                            butuh ${p.qty}, dapat ${p.dapat}, <b>kurang ${p.kurang}</b></li>`).join('')}</ul>`
                    : '<i class="fa-solid fa-circle-check"></i> Semua style mendapat mesin yang cukup');
        }

        // Satu tabel untuk semua jenis yang punya mesin: stok beli & sewa dipisah, lalu dibanding pemakaian
        // (kebutuhan hari tersibuk di periode ini). Status di atas tabel hanya untuk jenis yang dibutuhkan.
        function renderStokMesin() {
            const { stok: daftarStok, kebutuhan, sewa } = dataPreview;
            const total = { beli: 0, sewa: 0, pakai: 0 };
            const kurang = [];
            let body = '';

            daftarStok.forEach(j => {
                const beli = Number(j.stok_beli);
                const sewaJenis = Number(j.stok_sewa);
                const stok = beli + sewaJenis;
                const pakai = kebutuhan[j.kd_jenis] || 0;
                const sisa = stok - pakai;
                total.beli += beli;
                total.sewa += sewaJenis;
                total.pakai += pakai;
                if (sisa < 0) kurang.push(`${j.kd_jenis} (kurang ${-sisa})`);
                if (hanyaKurang() && sisa >= 0) return;

                body += `<tr class="${sisa < 0 ? 'row-kurang' : ''}">
                    <td><b>${escapeHtml(j.kd_jenis)}</b> - ${escapeHtml(j.nm_jenis)}</td>
                    <td class="text-end">${beli}</td>
                    <td class="text-end">${sewaJenis || ''}</td>
                    <td class="text-end fw-bold">${stok}</td>
                    <td class="text-end">${pakai || ''}</td>
                    <td class="text-end fw-bold ${sisa < 0 ? 'text-danger' : ''}">${sisa}</td>
                </tr>`;
            });

            const totalStok = total.beli + total.sewa;
            $('#tableStokMesin tbody').html(body || `<tr><td colspan="6" class="text-center text-muted">
                ${hanyaKurang() ? 'Tidak ada jenis yang kurang' : 'Tidak ada mesin aktif'}</td></tr>`);
            $('#tableStokMesin tfoot').html(`<tr class="row-total">
                <th>TOTAL</th>
                <th class="text-end">${total.beli}</th>
                <th class="text-end">${total.sewa}</th>
                <th class="text-end">${totalStok}</th>
                <th class="text-end">${total.pakai}</th>
                <th class="text-end">${totalStok - total.pakai}</th>
            </tr>`);

            $('#previewStatus')
                .removeClass('alert-success alert-danger')
                .addClass(kurang.length ? 'alert-danger' : 'alert-success')
                .html(kurang.length
                    ? `<i class="fa-solid fa-circle-xmark"></i> <b>Stok tidak cukup</b> untuk ${kurang.length} jenis mesin: ${escapeHtml(kurang.join(', '))}`
                    : '<i class="fa-solid fa-circle-check"></i> <b>Semua stok tersedia</b> untuk requirement di periode ini');

            // Mesin sewa yang nm_jenis-nya belum sama dengan kode jenis tidak masuk tabel, cukup diinfokan jumlahnya
            const belumTerpetakan = sewa.filter(s => !s.kd_jenis).reduce((sum, s) => sum + Number(s.total), 0);
            $('#infoSewaBelum').html(belumTerpetakan
                ? `<b class="text-danger">${belumTerpetakan} unit mesin sewa belum terhitung</b> karena nama jenisnya belum diisi kode jenis.`
                : '');
        }

        function dataTableReload() {
            $('#datatable').DataTable().ajax.reload();
        }

        function tambahBarisJenis() {
            const baris = $($('#templateBarisJenis').html().trim());
            $('#tableJenis tbody').append(baris);

            baris.find('.cbo-jenis').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#AddRequirementModal'),
            });

            nomoriBarisJenis();
        }

        // Minimal harus tersisa satu baris
        function hapusBarisJenis(btn) {
            if ($('#tableJenis tbody tr').length <= 1) {
                return;
            }

            $(btn).closest('tr').remove();
            nomoriBarisJenis();
        }

        function nomoriBarisJenis() {
            $('#tableJenis tbody tr').each(function (i) {
                $(this).find('.nomor-baris').text(i + 1);
            });
            hitungTotalQty();
        }

        function hitungTotalQty() {
            let total = 0;
            $('#tableJenis .txt-qty').each(function () {
                total += parseInt(this.value) || 0;
            });
            $('#totalQty').text(total);
        }

        $(document).on('input', '#tableJenis .txt-qty', hitungTotalQty);

        function simpanRequirement() {
            const items = $('#tableJenis tbody tr').map(function () {
                return {
                    id_kd_jenis: $(this).find('.cbo-jenis').val(),
                    qty: $(this).find('.txt-qty').val(),
                };
            }).get();

            if (!$('#txttgl_awal').val() || !$('#txttgl_akhir').val() || !$('#cbostyle').val() || !$('#cboline').val()) {
                Swal.fire({ icon: 'warning', title: 'Tgl. Plan, Style dan Line wajib diisi' });
                return;
            }
            if (items.some(item => !item.id_kd_jenis || !(parseInt(item.qty) > 0))) {
                Swal.fire({ icon: 'warning', title: 'Jenis mesin & Qty (minimal 1) wajib diisi di semua baris' });
                return;
            }

            const data = {
                _token: '{{ csrf_token() }}',
                tgl_awal: $('#txttgl_awal').val(),
                tgl_akhir: $('#txttgl_akhir').val(),
                style: $('#cbostyle').val(),
                id_lokasi: $('#cboline').val(),
                items: items,
            };

            const $btn = $('#saveRequirementButton');
            $btn.prop('disabled', true);

            // Cek stok dulu. Kalau requirement ini bikin mesin kurang di suatu hari, user diminta konfirmasi;
            // kalau cek stoknya sendiri gagal, simpan tetap jalan supaya input tidak tertahan.
            $.ajax({
                type: 'POST',
                url: '{{ route('cek_stok_asset_mesin_requirement') }}',
                data: data,
            }).then(function (response) {
                if (!response.kurang.length) {
                    return true;
                }

                const daftar = response.kurang.map(k => `<li><b>${escapeHtml(k.kd_jenis)}</b>: butuh ${k.kebutuhan}, stok ${k.stok},
                    <b class="text-danger">kurang ${k.kurang}</b>
                    <small class="text-muted">(${formatTanggal(k.tgl_dari, 'display')} s/d ${formatTanggal(k.tgl_sampai, 'display')})</small></li>`).join('');

                return Swal.fire({
                    icon: 'warning',
                    title: 'Stok mesin tidak cukup',
                    html: `<div class="text-start">Total kebutuhan semua style di tanggal tersebut melebihi stok:
                        <ul class="mt-2 mb-0">${daftar}</ul></div>`,
                    showCancelButton: true,
                    confirmButtonText: 'Tetap Simpan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc3545',
                }).then(result => result.isConfirmed);
            }, function () {
                return true;
            }).then(function (lanjut) {
                if (!lanjut) {
                    $btn.prop('disabled', false);
                    return;
                }
                kirimRequirement(data, $btn);
            });
        }

        function kirimRequirement(data, $btn) {
            $.ajax({
                type: 'POST',
                url: '{{ route('store_asset_mesin_requirement') }}',
                data: data,
                success: function (response) {
                    Swal.fire({ icon: 'success', title: response.message, timer: 1500, showConfirmButton: false });
                    $('#AddRequirementModal').modal('hide');
                    resetFormRequirement();
                    dataTableReload();
                },
                error: function (xhr) {
                    // Pesan validasi Laravel diambil yang pertama saja
                    const errors = xhr.responseJSON?.errors;
                    const message = errors ? Object.values(errors)[0][0] : (xhr.responseJSON?.message || 'Gagal menyimpan data');
                    Swal.fire({ icon: 'error', title: message });
                },
                complete: function () {
                    $btn.prop('disabled', false);
                },
            });
        }

        function resetFormRequirement() {
            const today = '{{ date('Y-m-d') }}';
            $('#txttgl_awal').val(today);
            $('#txttgl_akhir').val(today).attr('min', today);
            hitungTotalDays();
            $('#cbostyle').val(null).trigger('change');
            $('#cboline').val('').trigger('change');

            $('#tableJenis tbody').empty();
            tambahBarisJenis();
        }

        // Tgl awal & akhir ikut dihitung, jadi plan 1 hari (awal = akhir) tetap 1 hari
        function hitungTotalDays() {
            const awal = $('#txttgl_awal').val();
            const akhir = $('#txttgl_akhir').val();

            if (!awal || !akhir) {
                $('#txttotal_days').val('');
                return;
            }

            const selisih = (new Date(akhir) - new Date(awal)) / 86400000;
            $('#txttotal_days').val(selisih >= 0 ? selisih + 1 : '');
        }
    </script>
@endsection
