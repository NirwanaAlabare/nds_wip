@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        .barcode-input {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            letter-spacing: 1px;
        }
        .barcode-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
            background-color: #fffde7;
        }
        .row-filled { background-color: #f0fff4 !important; }
        .row-empty  { background-color: #fffde7 !important; }

        .table-container {
            max-height: calc(100vh - 360px);
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        .sticky-header thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #343a40;
            color: white;
            box-shadow: 0 2px 2px rgba(0,0,0,0.2);
        }
        .badge-count { font-size: 13px; padding: 5px 10px; }
        .select2-container { width: 100% !important; }
    </style>
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">
            <i class="fas fa-barcode"></i> Input Barcode Sales Order
        </h5>
    </div>
    <div class="card-body">

        {{-- Pilih SO --}}
        <div class="card border mb-3">
            <div class="card-body py-3">
                <div class="row align-items-end">
                    <div class="col-md-9">
                        <label class="font-weight-bold mb-1">Pilih SO <span class="text-danger">*</span>
                            <small class="text-muted font-weight-normal">(bisa pilih lebih dari 1)</small>
                        </label>
                        <select id="select-so" multiple class="form-control select2-so" style="width:100%"
                            placeholder="Cari SO No, WS, PO, Buyer...">
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary btn-block" onclick="loadDetails()">
                            <i class="fas fa-search"></i> Load Detail SO
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter & Counter (hidden dulu) --}}
        <div id="section-filter" style="display:none;">
            <div class="row align-items-center mb-2">
                <div class="col-md-5 d-flex gap-2 align-items-center flex-wrap">
                    <span class="badge badge-secondary badge-count">Total: <strong id="count-total">0</strong></span>
                    <span class="badge badge-success badge-count">Terisi: <strong id="count-filled">0</strong></span>
                    <span class="badge badge-warning text-dark badge-count">Kosong: <strong id="count-empty">0</strong></span>
                </div>
                <div class="col-md-7">
                    <div class="input-group input-group-sm">
                        <select id="filter-so" class="form-control">
                            <option value="">-- Semua SO --</option>
                        </select>
                        <select id="filter-color" class="form-control ml-1">
                            <option value="">-- Semua Color --</option>
                        </select>
                        <select id="filter-size" class="form-control ml-1">
                            <option value="">-- Semua Size --</option>
                        </select>
                        <select id="filter-status" class="form-control ml-1">
                            <option value="">-- Semua Status --</option>
                            <option value="empty">Belum Diisi</option>
                            <option value="filled">Sudah Diisi</option>
                        </select>
                        <div class="input-group-append ml-1">
                            <button class="btn btn-outline-secondary btn-sm" onclick="resetFilter()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel --}}
            <div class="table-container">
                <table class="table table-bordered table-sm mb-0 sticky-header" id="table-barcode">
                    <thead>
                        <tr class="text-center">
                            <th width="4%">No</th>
                            <th width="10%">No SO</th>
                            <th width="8%">WS</th>
                            <th width="9%">Color</th>
                            <th width="7%">Size</th>
                            <th width="10%">Product Set</th>
                            <th width="11%">Style</th>
                            <th width="6%">Qty</th>
                            <th>Barcode</th>
                        </tr>
                    </thead>
                    <tbody id="barcode-table-body">
                    </tbody>
                </table>
            </div>

            {{-- Simpan --}}
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i>
                    Tekan <kbd>Enter</kbd> setelah scan → pindah ke baris berikutnya.
                    <span class="text-success">■</span> Hijau = terisi &nbsp;
                    <span class="text-warning">■</span> Kuning = kosong
                </small>
                <button class="btn btn-primary px-4" onclick="saveAllBarcode()">
                    <i class="fas fa-save"></i> Simpan Semua Barcode
                </button>
            </div>
        </div>

        {{-- Empty state --}}
        <div id="section-empty" class="text-center py-5 text-muted">
            <i class="fas fa-barcode fa-4x mb-3 d-block"></i>
            Pilih SO di atas lalu klik <strong>Load Detail SO</strong>
        </div>

    </div>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>

    // ── Select2 AJAX untuk memilih SO ──────────────
    $(document).ready(function() {
        $('#select-so').select2({
            theme: 'bootstrap4',
            placeholder: 'Cari SO No, WS, PO, Buyer...',
            minimumInputLength: 0,
            ajax: {
                url: "{{ route('so-barcode-get-so-list') }}",
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return { search: params.term };
                },
                processResults: function(data) {
                    return { results: data.results };
                },
                cache: true
            }
        });
    });

    // ── Load Detail dari SO yang dipilih ──────────
    function loadDetails() {
        let so_ids = $('#select-so').val();
        if (!so_ids || so_ids.length === 0) {
            Swal.fire('Perhatian', 'Pilih minimal 1 SO terlebih dahulu.', 'warning');
            return;
        }

        Swal.fire({ title: 'Memuat...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        $.ajax({
            url : "{{ route('so-barcode-get-details') }}",
            type: 'GET',
            data: { so_ids: so_ids },
            success: function(res) {
                Swal.close();
                if (res.status !== 200) {
                    Swal.fire('Gagal', res.message, 'error');
                    return;
                }
                renderTable(res.data, res.total, res.filled);
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Gagal memuat', 'error');
            }
        });
    }

    // ── Render tabel ─────────────────────────────
    function renderTable(data, total, filled) {
        let tbody     = '';
        let soSet     = new Set();
        let colorSet  = new Set();
        let sizeSet   = new Set();

        data.forEach(function(d, i) {
            let isFilled  = d.barcode && d.barcode.trim() !== '';
            let rowClass  = isFilled ? 'row-filled' : 'row-empty';
            let soNo      = d.so_no || '-';
            let color     = d.color || '-';
            let size      = d.size  || '-';

            soSet.add(soNo);
            colorSet.add(color);
            sizeSet.add(size);

            tbody += `
                <tr class="${rowClass}"
                    data-sono="${soNo.toLowerCase()}"
                    data-color="${color.toLowerCase()}"
                    data-size="${size.toLowerCase()}"
                    data-filled="${isFilled ? 'filled' : 'empty'}">
                    <td class="text-center align-middle">${i + 1}</td>
                    <td class="align-middle" style="font-size:11px;">${soNo}</td>
                    <td class="align-middle" style="font-size:11px;">${d.kpno || '-'}</td>
                    <td class="align-middle">${color}</td>
                    <td class="text-center align-middle">${size}</td>
                    <td class="text-center align-middle">${d.product_set || '-'}</td>
                    <td class="align-middle" style="font-size:11px;">${d.styleno_prod || '-'}</td>
                    <td class="text-right align-middle">${Number(d.qty).toLocaleString()}</td>
                    <td class="align-middle">
                        <input type="text"
                            class="form-control form-control-sm barcode-input"
                            data-id="${d.id}"
                            value="${d.barcode || ''}"
                            placeholder="Scan / ketik..."
                            autocomplete="off">
                    </td>
                </tr>`;
        });

        $('#barcode-table-body').html(tbody);

        // Populate filter dropdowns
        let soOpts = '<option value="">-- Semua SO --</option>';
        soSet.forEach(s => soOpts += `<option value="${s.toLowerCase()}">${s}</option>`);
        $('#filter-so').html(soOpts);

        let colorOpts = '<option value="">-- Semua Color --</option>';
        colorSet.forEach(c => colorOpts += `<option value="${c.toLowerCase()}">${c}</option>`);
        $('#filter-color').html(colorOpts);

        let sizeOpts = '<option value="">-- Semua Size --</option>';
        sizeSet.forEach(s => sizeOpts += `<option value="${s.toLowerCase()}">${s}</option>`);
        $('#filter-size').html(sizeOpts);

        // Update counter
        let empty = total - filled;
        $('#count-total').text(total);
        $('#count-filled').text(filled);
        $('#count-empty').text(empty);

        $('#section-empty').hide();
        $('#section-filter').show();

        // Fokus ke input kosong pertama
        $('.barcode-input').filter(function() { return !$(this).val().trim(); }).first().focus();
    }

    // ── Filter ───────────────────────────────────
    function applyFilter() {
        let fSo     = $('#filter-so').val().toLowerCase();
        let fColor  = $('#filter-color').val().toLowerCase();
        let fSize   = $('#filter-size').val().toLowerCase();
        let fStatus = $('#filter-status').val();

        $('#barcode-table-body tr').each(function() {
            let match = (!fSo     || $(this).data('sono')   === fSo)
                     && (!fColor  || $(this).data('color')  === fColor)
                     && (!fSize   || $(this).data('size')   === fSize)
                     && (!fStatus || $(this).data('filled') === fStatus);
            $(this).toggle(match);
        });
    }
    $('#filter-so, #filter-color, #filter-size, #filter-status').on('change', applyFilter);

    function resetFilter() {
        $('#filter-so, #filter-color, #filter-size, #filter-status').val('');
        $('#barcode-table-body tr').show();
    }

    // ── Auto-advance Enter ────────────────────────
    $(document).on('keydown', '.barcode-input', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            updateRowStatus($(this));
            let visible = $('.barcode-input').filter(function() {
                return $(this).closest('tr').is(':visible');
            });
            let next = visible.eq(visible.index(this) + 1);
            if (next.length) next.focus().select();
        }
    });

    $(document).on('input', '.barcode-input', function() {
        updateRowStatus($(this));
        updateCounter();
    });

    function updateRowStatus($input) {
        let $row = $input.closest('tr');
        let val  = $input.val().trim();
        if (val) {
            $row.removeClass('row-empty').addClass('row-filled').attr('data-filled', 'filled');
        } else {
            $row.removeClass('row-filled').addClass('row-empty').attr('data-filled', 'empty');
        }
    }

    function updateCounter() {
        let filled = 0, empty = 0;
        $('.barcode-input').each(function() {
            $(this).val().trim() ? filled++ : empty++;
        });
        let total = filled + empty;
        $('#count-total').text(total);
        $('#count-filled').text(filled);
        $('#count-empty').text(empty);
    }

    // ── Simpan Semua ─────────────────────────────
    function saveAllBarcode() {
        let barcodes = [];
        $('.barcode-input').each(function() {
            barcodes.push({ id: $(this).data('id'), barcode: $(this).val().trim() });
        });

        if (barcodes.length === 0) {
            Swal.fire('Perhatian', 'Tidak ada data untuk disimpan.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Simpan Semua Barcode?',
            html: `Menyimpan barcode untuk <b>${barcodes.length}</b> baris so_det.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url  : "{{ route('so-barcode-save') }}",
                type : 'POST',
                data : { _token: "{{ csrf_token() }}", barcodes: barcodes },
                success: function(res) {
                    if (res.status === 200) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, showConfirmButton: false });
                        updateCounter();
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Gagal menyimpan', 'error');
                }
            });
        });
    }

</script>
@endsection
