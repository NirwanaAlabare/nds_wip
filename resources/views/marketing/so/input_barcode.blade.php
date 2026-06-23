@extends('layouts.index')

@section('custom-link')
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
        .row-filled td {
            background-color: #f0fff4 !important;
        }
        .row-empty td {
            background-color: #fffde7 !important;
        }
        .sticky-header thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #343a40;
            color: white;
            box-shadow: 0 2px 2px rgba(0,0,0,0.2);
        }
        .table-container {
            max-height: calc(100vh - 280px);
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        .info-bar {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            border-radius: 8px;
            padding: 12px 20px;
            margin-bottom: 16px;
        }
        .badge-count {
            font-size: 14px;
            padding: 6px 12px;
        }
        .btn-save-sticky {
            position: sticky;
            bottom: 20px;
            z-index: 100;
        }
    </style>
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title fw-bold mb-0">
            <i class="fas fa-barcode"></i> Input Barcode SO
        </h5>
        <a href="{{ route('master-marketing-so') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">

        {{-- Info SO --}}
        <div class="info-bar">
            <div class="row">
                <div class="col-md-3">
                    <small class="text-white-50 d-block">No SO</small>
                    <strong>{{ $so->so_no ?? '-' }}</strong>
                </div>
                <div class="col-md-3">
                    <small class="text-white-50 d-block">WS / Kode</small>
                    <strong>{{ $so->kpno ?? '-' }}</strong>
                </div>
                <div class="col-md-3">
                    <small class="text-white-50 d-block">No PO</small>
                    <strong>{{ $so->no_po ?? '-' }}</strong>
                </div>
                <div class="col-md-3">
                    <small class="text-white-50 d-block">Style | Brand</small>
                    <strong>{{ $so->style ?? '-' }} | {{ $so->brand ?? '-' }}</strong>
                </div>
            </div>
        </div>

        {{-- Counter + Filter --}}
        <div class="row align-items-center mb-3">
            <div class="col-md-6 d-flex gap-2 align-items-center flex-wrap">
                <span class="badge badge-secondary badge-count">
                    Total: <strong id="count-total">{{ $details->count() }}</strong> baris
                </span>
                <span class="badge badge-success badge-count">
                    Terisi: <strong id="count-filled">{{ $details->whereNotNull('barcode')->where('barcode','!=','')->count() }}</strong>
                </span>
                <span class="badge badge-warning badge-count text-dark">
                    Kosong: <strong id="count-empty">{{ $details->filter(fn($d) => empty($d->barcode))->count() }}</strong>
                </span>
            </div>
            <div class="col-md-6">
                <div class="input-group input-group-sm">
                    <select id="filter-color" class="form-control">
                        <option value="">-- Semua Color --</option>
                        @foreach($colors as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                        @endforeach
                    </select>
                    <select id="filter-size" class="form-control ml-1">
                        <option value="">-- Semua Size --</option>
                        @foreach($sizes as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                    <select id="filter-status" class="form-control ml-1">
                        <option value="">-- Semua Status --</option>
                        <option value="empty">Belum Diisi</option>
                        <option value="filled">Sudah Diisi</option>
                    </select>
                    <div class="input-group-append ml-1">
                        <button class="btn btn-outline-secondary btn-sm" onclick="resetFilter()">
                            <i class="fas fa-times"></i> Reset
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
                        <th width="10%">Color</th>
                        <th width="8%">Size</th>
                        <th width="10%">Product Set</th>
                        <th width="12%">Style</th>
                        <th width="7%">Qty</th>
                        <th width="12%">Ex FTY</th>
                        <th>Barcode</th>
                    </tr>
                </thead>
                <tbody id="barcode-table-body">
                    @foreach($details as $i => $d)
                    @php $isFilled = !empty($d->barcode); @endphp
                    <tr class="{{ $isFilled ? 'row-filled' : 'row-empty' }}"
                        data-color="{{ strtolower($d->color) }}"
                        data-size="{{ strtolower($d->size) }}"
                        data-filled="{{ $isFilled ? 'filled' : 'empty' }}">
                        <td class="text-center align-middle">{{ $i + 1 }}</td>
                        <td class="align-middle">{{ $d->color ?? '-' }}</td>
                        <td class="text-center align-middle">{{ $d->size ?? '-' }}</td>
                        <td class="text-center align-middle">{{ $d->product_set ?? '-' }}</td>
                        <td class="align-middle" style="font-size:11px;">{{ $d->styleno_prod ?? '-' }}</td>
                        <td class="text-right align-middle">{{ number_format($d->qty, 0, ',', '.') }}</td>
                        <td class="text-center align-middle">{{ $d->deldate_det ?? '-' }}</td>
                        <td class="align-middle">
                            <input
                                type="text"
                                class="form-control form-control-sm barcode-input"
                                data-id="{{ $d->id }}"
                                value="{{ $d->barcode ?? '' }}"
                                placeholder="Scan / ketik barcode..."
                                autocomplete="off"
                            >
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Tombol Simpan --}}
        <div class="mt-3 d-flex justify-content-between align-items-center">
            <small class="text-muted">
                <i class="fas fa-info-circle"></i>
                Tekan <kbd>Enter</kbd> atau <kbd>Tab</kbd> setelah scan untuk pindah ke baris berikutnya.
                Baris <span class="text-success">hijau</span> = sudah terisi, <span class="text-warning">kuning</span> = belum.
            </small>
            <button class="btn btn-primary px-4" onclick="saveAllBarcode()">
                <i class="fas fa-save"></i> Simpan Semua Barcode
            </button>
        </div>

    </div>
</div>
@endsection

@section('custom-script')
<script>

    // ── Filter baris ──────────────────────────────
    function applyFilter() {
        const color  = $('#filter-color').val().toLowerCase();
        const size   = $('#filter-size').val().toLowerCase();
        const status = $('#filter-status').val();

        $('#barcode-table-body tr').each(function() {
            const rowColor  = $(this).data('color');
            const rowSize   = $(this).data('size');
            const rowFilled = $(this).data('filled');

            const matchColor  = !color  || rowColor  === color;
            const matchSize   = !size   || rowSize   === size;
            const matchStatus = !status || rowFilled === status;

            $(this).toggle(matchColor && matchSize && matchStatus);
        });
    }

    $('#filter-color, #filter-size, #filter-status').on('change', applyFilter);

    function resetFilter() {
        $('#filter-color, #filter-size, #filter-status').val('');
        $('#barcode-table-body tr').show();
    }

    // ── Auto-advance ke baris berikutnya saat Enter ──
    $(document).on('keydown', '.barcode-input', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();

            // Update status baris saat ini
            updateRowStatus($(this));

            // Cari input visible berikutnya
            let visibleInputs = $('.barcode-input').filter(function() {
                return $(this).closest('tr').is(':visible');
            });

            let currentIdx = visibleInputs.index(this);
            let next = visibleInputs.eq(currentIdx + 1);

            if (next.length) {
                next.focus().select();
            }
        }
    });

    // ── Update status baris & counter saat input berubah ──
    $(document).on('input', '.barcode-input', function() {
        updateRowStatus($(this));
        updateCounter();
    });

    function updateRowStatus($input) {
        let $row = $input.closest('tr');
        let val  = $input.val().trim();

        if (val) {
            $row.removeClass('row-empty').addClass('row-filled');
            $row.attr('data-filled', 'filled');
        } else {
            $row.removeClass('row-filled').addClass('row-empty');
            $row.attr('data-filled', 'empty');
        }
    }

    function updateCounter() {
        let filled = 0, empty = 0;
        $('.barcode-input').each(function() {
            if ($(this).val().trim()) filled++;
            else empty++;
        });
        $('#count-filled').text(filled);
        $('#count-empty').text(empty);
    }

    // ── Simpan Semua ─────────────────────────────
    function saveAllBarcode() {
        let barcodes = [];
        $('.barcode-input').each(function() {
            barcodes.push({
                id      : $(this).data('id'),
                barcode : $(this).val().trim()
            });
        });

        Swal.fire({
            title: 'Simpan Semua Barcode?',
            html: `Akan menyimpan barcode untuk <b>${barcodes.length}</b> baris so_det.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url  : "{{ route('so-save-barcode') }}",
                type : 'POST',
                data : {
                    _token   : "{{ csrf_token() }}",
                    barcodes : barcodes
                },
                success: function(res) {
                    if (res.status === 200) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, showConfirmButton: false });
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan';
                    Swal.fire('Error!', msg, 'error');
                }
            });
        });
    }

    // ── Fokus ke input pertama yang kosong saat halaman dibuka ──
    $(document).ready(function() {
        let firstEmpty = $('.barcode-input').filter(function() {
            return !$(this).val().trim();
        }).first();

        if (firstEmpty.length) {
            firstEmpty.focus();
        }
    });

</script>
@endsection
