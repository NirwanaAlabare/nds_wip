@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        /* Custom Styling khusus Edit Form Fabric */
        .edit-fabric-card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 8px;
            overflow: hidden;
        }

        .edit-fabric-card .card-header {
            background-color: #0c2556; /* Warna dark navy sesuai gambar */
            color: #ffffff;
            padding: 0.85rem 1.25rem;
        }

        .editable-box {
            background-color: #f8f9fa;
            border: 2px dashed #0d6efd;
            border-radius: 6px;
            padding: 12px;
        }

        .detail-item {
            background-color: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 8px 12px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .detail-item .detail-label {
            font-size: 0.725rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            margin-bottom: 2px;
        }

        .detail-item .detail-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #212529;
            word-break: break-word;
        }

        .unit-badge {
            font-size: 0.75rem;
            background-color: #e9ecef;
            color: #495057;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 6px;
        }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0 fs-5">
            <i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Ubah Data Penerimaan Fabric Cutting
        </h4>
        <a href="{{ route('penerimaan-cutting') }}" class="btn btn-primary btn-sm px-3 shadow-sm">
            <i class="fas fa-reply me-1"></i> Kembali
        </a>
    </div>

    <form action="{{ route('update-penerimaan-cutting') }}" method="post" id="update-penerimaan-cutting" onsubmit="submitForm(this, event)">
        @csrf
        @method('PUT')

        <input type="hidden" id="id" name="id" value="{{ $data->id }}">
        <input type="hidden" name="whs_bppb_det_id" value="{{ $data->whs_bppb_det_id ?? '' }}">
        <input type="hidden" name="barcode" value="{{ $data->barcode ?? '' }}">
        <div class="card edit-fabric-card mb-4">
            <div class="card-header">
                <h6 class="card-title fw-bold mb-0">Detail Barcode Fabric</h6>
            </div>
            <div class="card-body p-4">

                <!-- SECTION 1: EDITABLE FIELD (ROW ATAS) -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <div class="editable-box">
                            <label class="form-label small fw-bold text-primary mb-1">
                                Tanggal Terima <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control form-control-sm bg-white" id="tgl_terima" name="tgl_terima" value="{{ old('tgl_terima', $data->tanggal_terima ?? date('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="col-6 col-md-4">
                        <div class="detail-item">
                            <span class="detail-label">Barcode</span>
                            <span class="detail-value text-primary">{{ $data->barcode ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="col-6 col-md-4">
                        <div class="detail-item">
                            <span class="detail-label">No. Req</span>
                            <span class="detail-value">{{ $data->no_req ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <hr class="text-muted opacity-25 my-3">

                <!-- SECTION 2: READONLY DETAILS (GRID PROPOSIONAL) -->
                <div class="row g-3">
                    <!-- Baris 1: Informasi Dokumen BPPB -->
                    <div class="col-12 col-md-3">
                        <div class="detail-item">
                            <span class="detail-label">No. BPPB</span>
                            <span class="detail-value">{{ $data->no_bppb ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="detail-item">
                            <span class="detail-label">Tgl. BPPB</span>
                            <span class="detail-value">{{ $data->tanggal_bppb ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="detail-item">
                            <span class="detail-label">Tujuan</span>
                            <span class="detail-value">{{ $data->tujuan ?? '-' }}</span>
                        </div>
                    </div>

                    <!-- Baris 2: Work Order / Work Station -->
                    <div class="col-6 col-md-3">
                        <div class="detail-item">
                            <span class="detail-label">No. WS</span>
                            <span class="detail-value">{{ $data->no_ws ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="detail-item">
                            <span class="detail-label">No. WS Act</span>
                            <span class="detail-value">{{ $data->no_ws_act ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="detail-item">
                            <span class="detail-label">Qty Out</span>
                            <span class="detail-value">
                                {{ $data->qty_out ?? '0' }}
                                <span class="unit-badge">{{ $data->unit ?? '' }}</span>
                            </span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="detail-item">
                            <span class="detail-label">Qty Konv</span>
                            <span class="detail-value">
                                {{ $data->qty_konv ?? '0' }}
                                <span class="unit-badge">{{ $data->unit_konv ?? '' }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- Baris 3: Informasi Spesifikasi Fabric -->
                    <div class="col-12 col-md-3">
                        <div class="detail-item">
                            <span class="detail-label">Style</span>
                            <span class="detail-value">{{ $data->style ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="detail-item">
                            <span class="detail-label">Warna</span>
                            <span class="detail-value">{{ $data->warna ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="detail-item">
                            <span class="detail-label">No. Lot</span>
                            <span class="detail-value">{{ $data->no_lot ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="detail-item">
                            <span class="detail-label">No. Roll</span>
                            <span class="detail-value">{{ $data->no_roll ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-2">
                        <div class="detail-item">
                            <span class="detail-label">No. Roll Buyer</span>
                            <span class="detail-value">{{ $data->no_roll_buyer ?? '-' }}</span>
                        </div>
                    </div>

                    <!-- Baris 4: Informasi Master Item -->
                    <div class="col-12 col-md-2">
                        <div class="detail-item">
                            <span class="detail-label">ID Item</span>
                            <span class="detail-value">{{ $data->id_item ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-10">
                        <div class="detail-item">
                            <span class="detail-label">Nama Barang</span>
                            <span class="detail-value">{{ $data->nama_barang ?? '-' }}</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Tombol Submit Form -->
        <div class="d-flex justify-content-between mb-3">
            <a type="button" class="btn btn-danger px-5 py-2 fw-bold shadow-sm" id="btnCancel" href="{{ route('penerimaan-cutting') }}">
                <i class="fa fa-x me-1"></i> Batal
            </a>
            <button type="submit" class="btn btn-success px-5 py-2 fw-bold shadow-sm" id="btnSimpan">
                <i class="fa fa-save me-1"></i> Simpan Perubahan
            </button>
        </div>
    </form>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Page specific script -->
    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2()

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        })

        // Prevent Form Submit When Pressing Enter
        document.getElementById("update-penerimaan-cutting").onkeypress = function(e) {
            var key = e.charCode || e.keyCode || 0;
            if (key == 13) {
                e.preventDefault();
            }
        }

    </script>
@endsection
