@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                <i class="fa-solid fa-right-left"></i> Switch Transaction SO Det
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-warning py-2">
                <small>
                    Memindahkan <b>seluruh</b> transaksi so_det asal ke so_det tujuan:
                    marker &rarr; form cut (form baru tanpa pemakaian) &rarr; part &rarr; stocker &rarr; year sequence &rarr; loading &rarr; output sewing / finishing / packing (termasuk master plan).
                    Ditolak bila sudah ada output packing PO / packing NDS.
                </small>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label"><small class="fw-bold">SO Det Asal</small></label>
                    <select class="form-select select2-so-det" id="so_det_asal"></select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><small class="fw-bold">SO Det Tujuan</small></label>
                    <select class="form-select select2-so-det" id="so_det_tujuan"></select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label"><small class="fw-bold">Catatan</small></label>
                    <input type="text" class="form-control" id="notes" placeholder="Alasan switch...">
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-sb-secondary btn-sm fw-bold" id="btnPreview">
                    <i class="fa fa-search"></i> PREVIEW
                </button>
                <button type="button" class="btn btn-success btn-sm fw-bold" id="btnExecute" disabled>
                    <i class="fa fa-right-left"></i> EKSEKUSI
                </button>
            </div>

            <div id="previewResult" class="mt-3 d-none">
                <div id="previewBlocks"></div>
                <div id="previewWarnings"></div>

                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr><th></th><th>Asal</th><th>Tujuan</th></tr>
                            </thead>
                            <tbody id="previewInfo"></tbody>
                        </table>

                        <h6 class="fw-bold mt-3">Jumlah Data</h6>
                        <table class="table table-bordered table-sm">
                            <thead><tr><th>Tabel</th><th class="text-end">Baris</th></tr></thead>
                            <tbody id="previewCounts"></tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Form</h6>
                        <table class="table table-bordered table-sm">
                            <thead><tr><th>No. Form</th><th>Marker</th><th>Panel</th><th>Status</th><th>Selesai</th></tr></thead>
                            <tbody id="previewForms"></tbody>
                        </table>

                        <h6 class="fw-bold mt-3">Part</h6>
                        <table class="table table-bordered table-sm">
                            <thead><tr><th>Part Asal</th><th>Panel</th><th>Part Tujuan</th><th>Aksi</th></tr></thead>
                            <tbody id="previewParts"></tbody>
                        </table>

                        <h6 class="fw-bold mt-3">Master Plan</h6>
                        <table class="table table-bordered table-sm">
                            <thead><tr><th>Plan Asal</th><th>Line</th><th>Tanggal</th><th>Plan Tujuan</th><th>Aksi</th></tr></thead>
                            <tbody id="previewPlans"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-sb-secondary">
            <h5 class="card-title"><i class="fas fa-list fa-sm"></i> Riwayat Switch</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm w-100" id="list-table">
                    <thead>
                        <tr>
                            <th>Aksi</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>SO Det Asal</th>
                            <th>WS Asal</th>
                            <th>Color / Size Asal</th>
                            <th>SO Det Tujuan</th>
                            <th>WS Tujuan</th>
                            <th>Color / Size Tujuan</th>
                            <th>Catatan</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        const csrf = $('meta[name="csrf-token"]').attr('content');

        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        $('.select2-so-det').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Cari WS / Style / ID SO Det...',
            minimumInputLength: 3,
            ajax: {
                url: '{{ route('get-so-det-switch-so-det') }}',
                dataType: 'json',
                delay: 400,
                data: params => ({ q: params.term }),
                processResults: data => data,
            },
        });

        // Setiap pilihan berubah, preview harus diulang sebelum eksekusi
        $('.select2-so-det').on('change', function () {
            $('#btnExecute').prop('disabled', true);
            $('#previewResult').addClass('d-none');
        });

        let listTable = $('#list-table').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
                url: '{{ route('get-data-switch-so-det') }}',
            },
            columns: [
                {
                    data: 'id',
                    render: (data, type, row) => row.status == 'success'
                        ? `<button type="button" class="btn btn-danger btn-sm btn-undo" data-id="${data}"><i class="fa fa-rotate-left"></i> Undo</button>`
                        : ''
                },
                { data: 'created_at' },
                {
                    data: 'status',
                    render: (data, type, row) => {
                        if (data == 'success') {
                            return '<span class="badge bg-success">success</span>';
                        }
                        if (data == 'undone') {
                            return `<span class="badge bg-secondary" title="${escapeHtml(row.undone_by_username)} ${escapeHtml(row.undone_at)} ${escapeHtml(row.undo_notes)}">undone</span>`;
                        }
                        return `<span class="badge bg-danger" title="${escapeHtml(row.error)}">failed</span>`;
                    }
                },
                { data: 'so_det_id_asal' },
                { data: 'ws_asal' },
                { data: 'color_asal', render: (data, type, row) => escapeHtml(data) + ' / ' + escapeHtml(row.size_asal) },
                { data: 'so_det_id_tujuan' },
                { data: 'ws_tujuan' },
                { data: 'color_tujuan', render: (data, type, row) => escapeHtml(data) + ' / ' + escapeHtml(row.size_tujuan) },
                { data: 'notes', render: data => escapeHtml(data) },
                { data: 'created_by_username' },
            ],
            columnDefs: [{ targets: '_all', className: 'text-nowrap' }],
        });

        function payload() {
            return {
                so_det_asal: $('#so_det_asal').val(),
                so_det_tujuan: $('#so_det_tujuan').val(),
                notes: $('#notes').val(),
            };
        }

        function renderPreview(res) {
            const blocks = res.blocks || [];
            const warnings = res.warnings || [];

            $('#previewBlocks').html(blocks.length
                ? `<div class="alert alert-danger py-2"><b>Tidak bisa dieksekusi:</b><ul class="mb-0">${blocks.map(b => `<li>${escapeHtml(b)}</li>`).join('')}</ul></div>`
                : '<div class="alert alert-success py-2">Lolos pengecekan. Periksa ringkasan di bawah sebelum eksekusi.</div>');

            $('#previewWarnings').html(warnings.length
                ? `<div class="alert alert-warning py-2"><ul class="mb-0">${warnings.map(w => `<li>${escapeHtml(w)}</li>`).join('')}</ul></div>`
                : '');

            const a = res.asal || {};
            const t = res.tujuan || {};
            $('#previewInfo').html(['id_so_det', 'ws', 'styleno', 'buyer', 'color', 'size', 'dest'].map(key =>
                `<tr><th>${key}</th><td>${escapeHtml(a[key])}</td><td>${escapeHtml(t[key])}</td></tr>`
            ).join(''));

            const counts = res.counts || {};
            $('#previewCounts').html(Object.keys(counts).map(key =>
                `<tr><td>${escapeHtml(key)}</td><td class="text-end">${Number(counts[key]).toLocaleString('id-ID')}</td></tr>`
            ).join('') || '<tr><td colspan="2" class="text-center">-</td></tr>');

            $('#previewForms').html((res.forms || []).map(f =>
                `<tr><td>${escapeHtml(f.no_form)}</td><td>${escapeHtml(f.marker_kode)}</td><td>${escapeHtml(f.panel)}</td><td>${escapeHtml(f.status)}</td><td>${escapeHtml(f.waktu_selesai)}</td></tr>`
            ).join('') || '<tr><td colspan="5" class="text-center">-</td></tr>');

            $('#previewParts').html((res.parts || []).map(p =>
                `<tr><td>${escapeHtml(p.old_kode)}</td><td>${escapeHtml(p.panel)}</td><td>${escapeHtml(p.new_kode ?? '-')}</td><td>${escapeHtml(p.action)}</td></tr>`
            ).join('') || '<tr><td colspan="4" class="text-center">-</td></tr>');

            $('#previewPlans').html((res.master_plans || []).map(p =>
                `<tr><td>${escapeHtml(p.old_id)}</td><td>${escapeHtml(p.sewing_line)}</td><td>${escapeHtml(p.tgl_plan)}</td><td>${escapeHtml(p.new_id ?? '-')}</td><td>${escapeHtml(p.action)}</td></tr>`
            ).join('') || '<tr><td colspan="5" class="text-center">-</td></tr>');

            $('#previewResult').removeClass('d-none');
            $('#btnExecute').prop('disabled', blocks.length > 0);
        }

        $('#btnPreview').on('click', function () {
            const data = payload();
            if (!data.so_det_asal || !data.so_det_tujuan) {
                iziToast.warning({ title: 'Warning', message: 'Pilih so_det asal dan tujuan.', position: 'topCenter' });
                return;
            }

            Swal.fire({ title: 'Memeriksa data...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: '{{ route('preview-switch-so-det') }}',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
                data: data,
                success: function (res) {
                    Swal.close();
                    renderPreview(res);
                },
                error: function (xhr) {
                    Swal.close();
                    iziToast.error({ title: 'Error', message: xhr.responseJSON?.message || 'Gagal preview', position: 'topCenter' });
                }
            });
        });

        function countList(obj) {
            const keys = Object.keys(obj || {});
            return keys.length
                ? '<ul class="mb-0">' + keys.map(k => `<li>${escapeHtml(k)}: ${Number(obj[k]).toLocaleString('id-ID')}</li>`).join('') + '</ul>'
                : '-';
        }

        $(document).on('click', '.btn-undo', function () {
            const id = $(this).data('id');

            Swal.fire({ title: 'Memeriksa undo...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: '{{ route('undo-preview-switch-so-det') }}',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
                data: { id: id },
                success: function (res) {
                    const blocks = res.blocks || [];
                    const log = res.log || {};
                    const html = `
                        <div class="text-start small">
                            <p class="mb-2">So_det <b>${escapeHtml(log.so_det_id_tujuan)}</b> (${escapeHtml(log.ws_tujuan)}) dikembalikan ke <b>${escapeHtml(log.so_det_id_asal)}</b> (${escapeHtml(log.ws_asal)}).</p>
                            ${blocks.length ? `<div class="alert alert-danger py-2"><b>Tidak bisa undo:</b><ul class="mb-0">${blocks.map(b => `<li>${escapeHtml(b)}</li>`).join('')}</ul></div>` : ''}
                            <b>Dikembalikan:</b>${countList(res.revert)}
                            <b>Dihapus:</b>${countList(res.delete)}
                            <small class="text-muted">Part, loading plan & master plan baru hanya dihapus bila sudah tidak dipakai.</small>
                            ${blocks.length ? '' : '<input type="text" id="undoNotes" class="form-control form-control-sm mt-2" placeholder="Alasan undo...">'}
                        </div>`;

                    Swal.fire({
                        icon: blocks.length ? 'error' : 'warning',
                        title: 'Undo Switch #' + escapeHtml(id),
                        html: html,
                        width: 700,
                        showConfirmButton: blocks.length == 0,
                        showCancelButton: true,
                        confirmButtonText: 'Ya, undo',
                        cancelButtonText: 'Tutup',
                        confirmButtonColor: '#dc3545',
                        preConfirm: () => $('#undoNotes').val(),
                    }).then(result => {
                        if (!result.isConfirmed) {
                            return;
                        }

                        Swal.fire({ title: 'Memproses undo...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                        $.ajax({
                            url: '{{ route('undo-switch-so-det') }}',
                            type: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf },
                            data: { id: id, notes: result.value },
                            success: function (res) {
                                Swal.fire({ icon: 'success', title: 'Berhasil', html: escapeHtml(res.message) });
                                listTable.ajax.reload();
                            },
                            error: function (xhr) {
                                Swal.fire({ icon: 'error', title: 'Gagal', html: xhr.responseJSON?.message || 'Terjadi kesalahan' });
                                listTable.ajax.reload();
                            }
                        });
                    });
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal', html: xhr.responseJSON?.message || 'Gagal memeriksa undo' });
                }
            });
        });

        $('#btnExecute').on('click', function () {
            const data = payload();

            Swal.fire({
                icon: 'warning',
                title: 'Eksekusi Switch?',
                html: `Semua transaksi so_det <b>${escapeHtml(data.so_det_asal)}</b> akan dipindah ke so_det <b>${escapeHtml(data.so_det_tujuan)}</b>.`,
                showCancelButton: true,
                confirmButtonText: 'Ya, eksekusi',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#198754',
            }).then(result => {
                if (!result.isConfirmed) {
                    return;
                }

                Swal.fire({ title: 'Memproses switch...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $('#btnExecute').prop('disabled', true);

                $.ajax({
                    url: '{{ route('store-switch-so-det') }}',
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf },
                    data: data,
                    success: function (res) {
                        Swal.fire({ icon: 'success', title: 'Berhasil', html: escapeHtml(res.message) });
                        $('#previewResult').addClass('d-none');
                        listTable.ajax.reload();
                    },
                    error: function (xhr) {
                        Swal.fire({ icon: 'error', title: 'Gagal', html: xhr.responseJSON?.message || 'Terjadi kesalahan' });
                        listTable.ajax.reload();
                    }
                });
            });
        });
    </script>
@endsection
