@extends('layouts.index')

@php
    $fmtDate = fn ($value, $withTime = true) => $value ? date($withTime ? 'd-m-Y H:i' : 'd-m-Y', strtotime($value)) : '-';
    $fmtNum = fn ($value) => is_numeric($value) ? rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',') : '0';

    $currentTerima = $data
        ? date('Y-m-d', strtotime($data->tanggal_terima)) . 'T' . date('H:i', strtotime($data->created_at))
        : null;

    // Batas maksimal tanggal terima: hanya berlaku jika tidak ada penerimaan lain sebelum pemakaian pertama
    $batasTerima = null;
    if (!empty($firstPemakaian)) {
        if (empty($firstPenerimaanLain) || strtotime($firstPenerimaanLain) > strtotime($firstPemakaian)) {
            $batasTerima = date('Y-m-d\TH:i', strtotime($firstPemakaian));
        }
    }
@endphp

@section('custom-link')
    <style>
        .pc-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .pc-card .card-header {
            background: #fff;
            border-bottom: 1px solid #f1f3f5;
            padding: 0.85rem 1.25rem;
        }

        .pc-section-title {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            margin-bottom: 0;
        }

        .pc-hero {
            background: linear-gradient(135deg, #0c2556 0%, #1a3f86 100%);
            color: #fff;
            border-radius: 10px;
            padding: 1.1rem 1.35rem;
        }

        .pc-hero .pc-hero-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            opacity: 0.75;
        }

        .pc-hero .pc-hero-value {
            font-size: 1.05rem;
            font-weight: 700;
            word-break: break-word;
        }

        .pc-hero .pc-barcode {
            font-size: 1.4rem;
            font-family: SFMono-Regular, Menlo, Consolas, monospace;
            letter-spacing: 0.5px;
        }

        .pc-dl {
            display: grid;
            grid-template-columns: minmax(110px, 38%) 1fr;
            row-gap: 0.55rem;
            column-gap: 0.75rem;
            margin: 0;
        }

        .pc-dl dt {
            font-size: 0.8rem;
            font-weight: 500;
            color: #6c757d;
        }

        .pc-dl dd {
            font-size: 0.875rem;
            font-weight: 600;
            color: #212529;
            margin: 0;
            word-break: break-word;
        }

        .pc-unit {
            font-size: 0.72rem;
            font-weight: 600;
            background: #eef1f5;
            color: #495057;
            padding: 1px 6px;
            border-radius: 4px;
            margin-left: 4px;
        }

        .pc-edit-card {
            border: 1px solid #b6d4fe;
            border-left: 4px solid #0d6efd;
        }

        .pc-ref {
            font-size: 0.8rem;
            background: #f8f9fa;
            border-radius: 6px;
            padding: 0.6rem 0.75rem;
        }

        .pc-ref .pc-ref-row {
            display: flex;
            justify-content: space-between;
            gap: 0.5rem;
        }

        .pc-ref .pc-ref-row + .pc-ref-row {
            margin-top: 0.3rem;
        }

        .pc-change {
            font-size: 0.8rem;
        }

        .pc-actions {
            position: sticky;
            bottom: 0;
            background: #fff;
            border-top: 1px solid #e9ecef;
            padding: 0.75rem 0;
            z-index: 5;
            margin-bottom: 15px;
        }
    </style>
@endsection

@section('content')
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="fw-bold text-dark mb-0 fs-5">
                <i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Ubah Penerimaan Fabric Cutting
            </h4>
            <small class="text-muted">Hanya tanggal &amp; waktu terima yang dapat diubah. Data lain mengikuti BPPB.</small>
        </div>
        <div class="d-flex gap-2">
            @if ($data)
                <button type="button" class="btn btn-outline-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalPenerimaanRoll" title="Lihat penerimaan lain dengan barcode yang sama">
                    <i class="fa-solid fa-layer-group me-1"></i> Penerimaan Roll Ini
                    <span class="badge {{ $penerimaanRoll->count() > 1 ? 'bg-primary' : 'bg-secondary' }} ms-1">{{ $penerimaanRoll->count() }}</span>
                    {{-- <kbd class="ms-1 d-none d-md-inline">Alt+P</kbd> --}}
                </button>
            @endif
            <a href="{{ route('penerimaan-cutting') }}" class="btn btn-outline-secondary btn-sm px-3">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    @if (!$data)
        <div class="alert alert-warning d-flex align-items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>Data penerimaan tidak ditemukan. Silakan kembali ke daftar dan pilih data lain.</span>
        </div>
    @else
        <form action="{{ route('update-penerimaan-cutting') }}" method="post" id="update-penerimaan-cutting" onsubmit="confirmUpdate(this, event)">
            @csrf
            @method('PUT')

            <input type="hidden" id="id" name="id" value="{{ $data->id }}">
            <input type="hidden" name="whs_bppb_det_id" value="{{ $data->whs_bppb_det_id ?? '' }}">
            <input type="hidden" name="barcode" value="{{ $data->barcode ?? '' }}">

            <!-- Ringkasan Roll -->
            <div class="pc-hero mb-3">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-4">
                        <div class="pc-hero-label">Barcode</div>
                        <div class="pc-hero-value pc-barcode">{{ $data->barcode ?? '-' }}</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="pc-hero-label">Qty Konv</div>
                        <div class="pc-hero-value">{{ $fmtNum($data->qty_konv) }} <small class="opacity-75">{{ $data->unit_konv ?? '' }}</small></div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="pc-hero-label">No. Lot / Roll</div>
                        <div class="pc-hero-value">{{ $data->no_lot ?? '-' }} / {{ $data->no_roll ?? '-' }}</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="pc-hero-label">Diterima Saat Ini</div>
                        <div class="pc-hero-value"><i class="fa-regular fa-clock me-1 opacity-75"></i>{{ $fmtDate($currentTerima) }}</div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <!-- Kolom Kiri: Form Edit -->
                <div class="col-12 col-lg-4">
                    <div class="card pc-card pc-edit-card h-100 mb-0">
                        <div class="card-header">
                            <h6 class="pc-section-title text-primary"><i class="fa-solid fa-calendar-days me-1"></i> Tanggal Terima</h6>
                        </div>
                        <div class="card-body">
                            <label for="tgl_terima" class="form-label fw-semibold small mb-1">
                                Tanggal &amp; Waktu Terima <span class="text-danger">*</span>
                            </label>
                            <input type="datetime-local"
                                class="form-control"
                                id="tgl_terima"
                                name="tgl_terima"
                                value="{{ old('tgl_terima', $currentTerima) }}"
                                data-original="{{ $currentTerima }}"
                                @if ($batasTerima) max="{{ $batasTerima }}" @endif
                                required>
                            <div class="invalid-feedback d-none" id="tgl_terima_error"></div>

                            <div class="pc-change mt-2 d-none" id="tgl_terima_change">
                                <span class="badge bg-warning text-dark me-1">Diubah</span>
                                <span class="text-muted">{{ $fmtDate($currentTerima) }}</span>
                                <i class="fa-solid fa-arrow-right mx-1 text-muted"></i>
                                <span class="fw-semibold" id="tgl_terima_new"></span>
                                <a href="javascript:void(0)" class="ms-1 small" onclick="resetTglTerima()">Batalkan</a>
                            </div>

                            <div class="alert alert-danger py-2 px-3 small mt-2 mb-0 d-none" id="tgl_terima_warning">
                                <i class="fa-solid fa-circle-exclamation me-1"></i>
                                Tanggal terima tidak boleh melewati pemakaian pertama (<b>{{ $fmtDate($firstPemakaian) }}</b>).
                            </div>

                            <!-- Acuan tanggal -->
                            <div class="pc-ref mt-3">
                                <div class="fw-semibold text-secondary mb-1"><i class="fa-solid fa-circle-info me-1"></i> Acuan</div>
                                <div class="pc-ref-row">
                                    <span class="text-muted">Tgl. BPPB</span>
                                    <span class="fw-semibold">{{ $fmtDate($data->tanggal_bppb, false) }}</span>
                                </div>
                                <div class="pc-ref-row">
                                    <span class="text-muted">Pemakaian pertama</span>
                                    <span class="fw-semibold">{{ $firstPemakaian ? $fmtDate($firstPemakaian) : 'Belum dipakai' }}</span>
                                </div>
                                @if ($firstPenerimaanLain)
                                    <div class="pc-ref-row">
                                        <span class="text-muted">Penerimaan lain (terawal)</span>
                                        <span class="fw-semibold">{{ $fmtDate($firstPenerimaanLain) }}</span>
                                    </div>
                                @endif
                                @if ($penerimaanRoll->count() > 1)
                                    <div class="pc-ref-row">
                                        <span class="text-muted">Penerimaan roll ini</span>
                                        <a href="javascript:void(0)" class="fw-semibold" data-bs-toggle="modal" data-bs-target="#modalPenerimaanRoll">
                                            {{ $penerimaanRoll->count() }} data <i class="fa-solid fa-arrow-up-right-from-square ms-1 small"></i>
                                        </a>
                                    </div>
                                @endif
                            </div>

                            @if ($firstPemakaian)
                                <small class="text-muted d-block mt-2">
                                    Setelah disimpan, qty roll pada pemakaian akan dihitung ulang otomatis.
                                </small>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Detail Readonly -->
                <div class="col-12 col-lg-8">
                    <div class="card pc-card mb-3">
                        <div class="card-header">
                            <h6 class="pc-section-title"><i class="fa-solid fa-file-lines me-1"></i> Dokumen BPPB</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <dl class="pc-dl">
                                        <dt>No. BPPB</dt>
                                        <dd>{{ $data->no_bppb ?? '-' }}</dd>
                                        <dt>Tgl. BPPB</dt>
                                        <dd>{{ $fmtDate($data->tanggal_bppb, false) }}</dd>
                                        <dt>No. Req</dt>
                                        <dd>{{ $data->no_req ?? '-' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-12 col-md-6">
                                    <dl class="pc-dl">
                                        <dt>Tujuan</dt>
                                        <dd>{{ $data->tujuan ?? '-' }}</dd>
                                        <dt>No. WS</dt>
                                        <dd>{{ $data->no_ws ?? '-' }}</dd>
                                        <dt>No. WS Act</dt>
                                        <dd>{{ $data->no_ws_act ?? '-' }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card pc-card mb-0">
                        <div class="card-header">
                            <h6 class="pc-section-title"><i class="fa-solid fa-scroll me-1"></i> Fabric</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <dl class="pc-dl">
                                        <dt>ID Item</dt>
                                        <dd>{{ $data->id_item ?? '-' }}</dd>
                                        <dt>Nama Barang</dt>
                                        <dd>{{ $data->nama_barang ?? '-' }}</dd>
                                        <dt>Style</dt>
                                        <dd>{{ $data->style ?? '-' }}</dd>
                                        <dt>Warna</dt>
                                        <dd>{{ $data->warna ?? '-' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-12 col-md-6">
                                    <dl class="pc-dl">
                                        <dt>No. Lot</dt>
                                        <dd>{{ $data->no_lot ?? '-' }}</dd>
                                        <dt>No. Roll</dt>
                                        <dd>{{ $data->no_roll ?? '-' }}</dd>
                                        <dt>No. Roll Buyer</dt>
                                        <dd>{{ $data->no_roll_buyer ?? '-' }}</dd>
                                        <dt>Qty Out</dt>
                                        <dd>{{ $fmtNum($data->qty_out) }}<span class="pc-unit">{{ $data->unit ?? '' }}</span></dd>
                                        <dt>Qty Konv</dt>
                                        <dd>{{ $fmtNum($data->qty_konv) }}<span class="pc-unit">{{ $data->unit_konv ?? '' }}</span></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="pc-actions d-flex justify-content-end gap-2 mt-3">
                <a class="btn btn-outline-secondary px-4" id="btnCancel" href="{{ route('penerimaan-cutting') }}">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary px-4 fw-semibold" id="btnSimpan" disabled>
                    <i class="fa fa-save me-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>

        <!-- Modal: Penerimaan dengan barcode/id_roll yang sama -->
        <div class="modal fade" id="modalPenerimaanRoll" tabindex="-1" aria-labelledby="modalPenerimaanRollLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h6 class="modal-title fw-bold mb-0" id="modalPenerimaanRollLabel">
                                <i class="fa-solid fa-layer-group me-1 text-primary"></i> Penerimaan Roll {{ $data->barcode }}
                            </h6>
                            <small class="text-muted">{{ $penerimaanRoll->count() }} penerimaan, urut dari yang paling awal</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">#</th>
                                        <th>Tgl. Terima</th>
                                        <th>No. BPPB</th>
                                        <th class="text-end">Qty Konv</th>
                                        <th>Keterangan</th>
                                        <th class="text-end pe-3">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($penerimaanRoll as $i => $p)
                                        @php
                                            $isCurrent = $p->id == $data->id;
                                            $setelahPemakaian = $firstPemakaian && strtotime($p->created_at) > strtotime($firstPemakaian);
                                        @endphp
                                        <tr class="{{ $isCurrent ? 'table-primary' : '' }}">
                                            <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                                            <td class="text-nowrap fw-semibold">{{ $fmtDate($p->created_at) }}</td>
                                            <td class="text-nowrap">{{ $p->no_bppb ?? '-' }}</td>
                                            <td class="text-end text-nowrap">{{ $fmtNum($p->qty_konv) }} <small class="text-muted">{{ $p->unit_konv }}</small></td>
                                            <td>
                                                @if ($isCurrent)
                                                    <span class="badge bg-primary">Sedang diedit</span>
                                                @endif
                                                @if ($setelahPemakaian)
                                                    <span class="badge bg-light text-dark border" title="Diterima setelah pemakaian pertama ({{ $fmtDate($firstPemakaian) }})">Setelah pemakaian</span>
                                                @endif
                                                @if ($p->edited_notes)
                                                    <div class="small text-muted">{{ $p->edited_notes }}</div>
                                                @endif
                                            </td>
                                            <td class="text-end pe-3">
                                                @if (!$isCurrent)
                                                    <a href="{{ route('edit-penerimaan-cutting', ['id' => $p->id]) }}" class="btn btn-outline-primary btn-sm py-0">
                                                        <i class="fa-solid fa-pen me-1"></i> Ubah
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($firstPemakaian)
                        <div class="modal-footer justify-content-start small text-muted">
                            <i class="fa-solid fa-circle-info me-1"></i> Pemakaian pertama roll ini: <b class="ms-1">{{ $fmtDate($firstPemakaian) }}</b>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endsection

@section('custom-script')
    <script>
        (function () {
            const form = document.getElementById("update-penerimaan-cutting");
            if (!form) return;

            const input = document.getElementById("tgl_terima");
            const btnSimpan = document.getElementById("btnSimpan");
            const changeBox = document.getElementById("tgl_terima_change");
            const changeNew = document.getElementById("tgl_terima_new");
            const warning = document.getElementById("tgl_terima_warning");
            const batas = input.getAttribute("max");

            function formatDisplay(value) {
                if (!value) return "-";
                const [tgl, jam] = value.split("T");
                const [y, m, d] = tgl.split("-");
                return `${d}-${m}-${y} ${jam || ""}`.trim();
            }

            function refreshState() {
                const value = input.value;
                const changed = value && value !== input.dataset.original;
                const melewatiBatas = batas && value && value > batas;

                changeBox.classList.toggle("d-none", !changed);
                changeNew.textContent = formatDisplay(value);

                warning.classList.toggle("d-none", !melewatiBatas);
                input.classList.toggle("is-invalid", !!melewatiBatas);

                btnSimpan.disabled = !changed || !!melewatiBatas;
            }

            window.resetTglTerima = function () {
                input.value = input.dataset.original;
                refreshState();
            };

            window.confirmUpdate = function (el, evt) {
                evt.preventDefault();

                if (btnSimpan.disabled) return;

                Swal.fire({
                    icon: "question",
                    title: "Simpan perubahan?",
                    html: `Tanggal terima diubah dari<br><b>${formatDisplay(input.dataset.original)}</b> menjadi <b>${formatDisplay(input.value)}</b>`,
                    showCancelButton: true,
                    confirmButtonText: "Ya, simpan",
                    cancelButtonText: "Periksa lagi",
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitForm(el, evt);
                    }
                });
            };

            input.addEventListener("input", refreshState);
            input.addEventListener("change", refreshState);

            // Cegah submit tidak sengaja saat menekan Enter
            form.addEventListener("keypress", function (e) {
                if ((e.charCode || e.keyCode) === 13) {
                    e.preventDefault();
                }
            });

            // Shortcut Alt+P: buka/tutup daftar penerimaan dengan barcode yang sama
            // (sementara dinonaktifkan)
            // const modalRoll = document.getElementById("modalPenerimaanRoll");
            // document.addEventListener("keydown", function (e) {
            //     if (modalRoll && e.altKey && !e.ctrlKey && !e.metaKey && (e.key || "").toLowerCase() === "p") {
            //         e.preventDefault();
            //         bootstrap.Modal.getOrCreateInstance(modalRoll).toggle();
            //     }
            // });

            refreshState();
        })();
    </script>
@endsection
