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
        .scroll-to-bottom-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            background-color: var(--sb-secondary-color);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.3s ease, transform 0.3s ease;
            opacity: 0;
            pointer-events: none;
        }

        .scroll-to-bottom-btn.show {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0);
        }

        .scroll-to-bottom-btn:hover {
            background-color: var(--sb-secondary-color);
        }

    </style>
@endsection

@section('content')
    @php
        $currentCuttingPiece = null;

        if (isset($cuttingFormPiece)) {
            $currentCuttingPiece = $cuttingFormPiece;
        }
    @endphp

    <div class="d-flex justify-content-between mb-3">
        <h5 class="text-sb fw-bold">Create Cutting Fabric PCS</h5>
        <div class="d-flex gap-1">
            <a href="{{ route("create-new-cutting-piece") }}" class="btn btn-success btn-sm {{ $currentCuttingPiece && $currentCuttingPiece->process >= 1 ? "" : "d-none" }}" id="create-new-process-button"><i class="fa fa-plus"></i> Baru</a>
            <button class="btn btn-sb btn-sm {{ $currentCuttingPiece && $currentCuttingPiece->process >= 1 ? "d-none" : "" }}" onclick="startProcess()" id="start-process-button"><i class="fa fa-plus"></i> MULAI</button>
            <a href="{{ route("cutting-piece") }}" class="btn btn-primary btn-sm"><i class="fa fa-reply"></i> Kembali ke List Cutting</a>
        </div>
    </div>

    {{-- PROCESS --}}
        <input type="hidden" id="process" value="{{ $currentCuttingPiece ? $currentCuttingPiece->process : null }}"></input>

    {{-- PROCESS ONE --}}
        <form action="{{ route('store-cutting-piece') }}" method="POST" id="process-one-form" onsubmit="processOne(this, event)">
            <input type="hidden" id="process-one" name="process-one" value="1">
            <div class="card card-sb">
                <div class="card-header">
                    <h5 class="card-title">
                        Detail Form Fabric PCS
                    </h5>
                </div>
                <div class="card-body">
                    {{-- ID --}}
                    <input type="hidden" id="id" name="id" value="{{ $currentCuttingPiece ? $currentCuttingPiece->id : null }}" readonly>
                    <div class="row row-gap-3">
                        <div class="col-md-6">
                            <label class="form-label">No. Form</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="no_form" name="no_form" value="{{ $currentCuttingPiece ? $currentCuttingPiece->no_form : null }}" readonly>
                                <button type="button" class="btn btn-sb" id="generate-code-button" onclick="generateCode()" disabled><i class="fa fa-rotate"></i></button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ $currentCuttingPiece ? ($currentCuttingPiece->tanggal ? $currentCuttingPiece->tanggal : date("Y-m-d")) : date("Y-m-d") }}" disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Worksheet</label>
                            @if ($currentCuttingPiece && $currentCuttingPiece->process >= 1)
                                <input type="hidden" class="form-control" id="act_costing_id" name="act_costing_id" value="{{ $currentCuttingPiece ? $currentCuttingPiece->act_costing_id : null }}" readonly>
                                <input type="text" class="form-control" id="act_costing_id" name="act_costing_id" value="{{ $currentCuttingPiece ? $currentCuttingPiece->act_costing_ws : null }}" readonly>
                            @else
                                <select class="form-select select2bs4" id="act_costing_id" name="act_costing_id" value="{{ $currentCuttingPiece ? $currentCuttingPiece->act_costing_id : null }}" disabled>
                                    <option value="">Pilih Worksheet</option>
                                    @foreach ($orders as $order)
                                        <option value="{{ $order->id }}">{{ $order->kpno }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="form-control" id="act_costing_ws" name="act_costing_ws" readonly>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Buyer</label>
                            <input type="hidden" class="form-control" id="buyer_id" name="buyer_id" value="{{ $currentCuttingPiece ? $currentCuttingPiece->buyer_id : null }}" readonly>
                            <input type="text" class="form-control" id="buyer" name="buyer" value="{{ $currentCuttingPiece ? $currentCuttingPiece->buyer : null }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Style</label>
                            <input type="text" class="form-control" id="style" name="style" value="{{ $currentCuttingPiece ? $currentCuttingPiece->style : null }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Color</label>
                            @if ($currentCuttingPiece && $currentCuttingPiece->process >= 1)
                                <input type="text" class="form-control" id="color" name="color" value="{{ $currentCuttingPiece ? $currentCuttingPiece->color : null }}" readonly>
                            @else
                                <select class="form-select select2bs4" id="color" name="color" disabled>
                                    <option value="">Pilih Color</option>
                                </select>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Panel</label>
                            @if ($currentCuttingPiece && $currentCuttingPiece->process >= 1)
                                <input type="text" class="form-control" id="panel" name="panel" value="{{ $currentCuttingPiece ? $currentCuttingPiece->panel : null }}" readonly>
                            @else
                                <select class="form-select select2bs4" id="panel" name="panel" disabled>
                                    <option value="">Pilih Panel</option>
                                </select>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cons. WS</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="cons_ws" name="cons_ws" value="{{ $currentCuttingPiece ? $currentCuttingPiece->cons_ws : null }}" readonly>
                                <input type="text" class="form-control" id="unit_cons_ws" name="unit_cons_ws" value="{{ $currentCuttingPiece ? $currentCuttingPiece->unit_cons_ws : null }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <hr class="border-dark">
                        </div>
                        <div class="col-md-12">
                            <div id="reader-operator"></div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label label-input">Scan ID Operator</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="employee_id" id="employee_id" value="{{ $currentCuttingPiece ? $currentCuttingPiece->employee_id : null }}" disabled>
                                <button class="btn btn-sm btn-success" type="button" id="get-button-operator" onclick="fetchScanOperator()" disabled>Get</button>
                                <button class="btn btn-sm btn-primary" type="button" id="scan-button-operator" onclick="refreshScanOperator()" disabled>Scan</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIK</label>
                            <input type="text" class="form-control" name="employee_nik" id="employee_nik" value="{{ $currentCuttingPiece ? $currentCuttingPiece->employee_nik : null }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" name="employee_name" id="employee_name" value="{{ $currentCuttingPiece ? $currentCuttingPiece->employee_name : null }}" readonly>
                        </div>
                    </div>
                </div>
                <div class="card-footer border-1">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-sb" type="submit" id="submit_process_one" disabled>NEXT</button>
                    </div>
                </div>
            </div>
        </form>
    {{-- END OF PROCESS ONE --}}

    {{-- PROCESS TWO --}}
        @php
            $currentCuttingPieceDetail = $currentCuttingPiece && $currentCuttingPiece->status == "complete" && $currentCuttingPiece->formCutPieceDetails ? $currentCuttingPiece->formCutPieceDetails->sortByDesc("id")->first() : null;
        @endphp
        <form action="{{ route('store-cutting-piece') }}" method="POST" id="process-two-form" onsubmit="processTwo(this, event)" class="{{ ($currentCuttingPiece ? ($currentCuttingPiece->process >= 1 ? "" : "d-none") : "d-none") }}">
            <div class="card card-sb" id="item-card">
                <div class="card-header">
                    <h3 class="card-title">Scan QR</h3>
                </div>
                <div class="card-body" style="display: block;">
                    <div class="row justify-content-center align-items-end">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <div id="reader-item"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="d-flex justify-content-center mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="switch-method" checked onchange="switchMethod(this)">
                                    <label class="form-check-label" id="to-scan">Scan Roll</label>
                                    <label class="form-check-label d-none" id="to-select">Pilih Barang</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12" id="scan-method">
                            <div class="row align-items-end mb-3">
                                <div class="col-md-12">
                                    <label class="form-label label-input">ID Roll</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="kode_barang" id="kode_barang" value="{{ $currentCuttingPieceDetail ? $currentCuttingPieceDetail->id_roll : null }}" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();">
                                        <button class="btn btn-sm btn-success" type="button" id="get-button" onclick="fetchScanItem()">Get</button>
                                        <button class="btn btn-sm btn-primary" type="button" id="scan-button" onclick="refreshScanItem()">Scan</button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-sb btn-sm btn-block d-none" id="scan-item">START</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 d-none" id="select-method">
                            <div class="row align-items-end mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Pilih Barang</label>
                                    <select class="form-select select2bs4" name="barang" id="barang" onchange="clearItem()">
                                        <option value="">Pilih Barang</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-sb btn-block" id="select-item" onclick="setSelectedItem()">Get</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">ID Item</label>
                                <input type="text" class="form-control" name="id_item" id="id_item"  value="{{ $currentCuttingPieceDetail ? $currentCuttingPieceDetail->id_item : null }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Detail Item</label>
                                <input type="text" class="form-control" name="detail_item" id="detail_item"  value="{{ $currentCuttingPieceDetail ? $currentCuttingPieceDetail->detail_item : null }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Qty</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="qty_item" id="qty_item"  value="{{ $currentCuttingPieceDetail ? $currentCuttingPieceDetail->qty : null }}" readonly>
                                    <input type="text" class="form-control" name="unit_qty_item" id="unit_qty_item"  value="{{ $currentCuttingPieceDetail ? $currentCuttingPieceDetail->qty_unit : null }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-6 d-none">
                            <div class="mb-3">
                                <label class="form-label">So Det</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="so_det_item" id="so_det_item" value="{{ ($currentCuttingPieceDetail ? ($currentCuttingPieceDetail->scannedItem ? $currentCuttingPieceDetail->scannedItem->so_det_list : null) : null) }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Sizes</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="sizes_item" id="sizes_item" value="{{ ($currentCuttingPieceDetail ? ($currentCuttingPieceDetail->scannedItem ? $currentCuttingPieceDetail->scannedItem->size_list : null) : null) }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer border-1">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-sb" type="submit">NEXT</button>
                    </div>
                </div>
            </div>
        </form>
    {{-- END OF PROCESS TWO --}}

    {{-- PROCESS THREE --}}
        <div id="process-three-container">
            <form action="{{ route('store-cutting-piece') }}" method="POST" id="process-three-form" onsubmit="processThree(this, event)" class="{{ ($currentCuttingPiece ? ($currentCuttingPiece->process >= 2 ? "" : "d-none") : "d-none") }}">
                <div class="card card-sb" id="process-three-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            Process
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row row-gap-3">
                            <input type="hidden" class="form-control" id="id_detail" name="id_detail" value="{{ $currentCuttingPieceDetail ? $currentCuttingPieceDetail->id_detail : null }}" readonly>
                            <input type="hidden" class="form-control" id="id_roll" name="id_roll" value="{{ $currentCuttingPieceDetail ? $currentCuttingPieceDetail->id_roll : null }}" readonly>
                            <div class="col-md-4">
                                <label class="form-label">Group</label>
                                <input type="text" class="form-control" id="group_roll" name="group_roll" value="{{ $currentCuttingPieceDetail ? $currentCuttingPieceDetail->group_roll : null }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Lot</label>
                                <input type="text" class="form-control" id="lot" name="lot" value="{{ $currentCuttingPieceDetail ? $currentCuttingPieceDetail->lot : null }}" readonly>
                            </div>
                            <div class="col-md-4 {{ $currentCuttingPieceDetail && $currentCuttingPieceDetail->roll_buyer ? "d-none" : "" }}" id="roll_container">
                                <label class="form-label">No. Roll</label>
                                <input type="text" class="form-control" id="roll" name="roll" value="{{ $currentCuttingPieceDetail ? $currentCuttingPieceDetail->roll : null }}" readonly>
                            </div>
                            <div class="col-md-4 {{ $currentCuttingPieceDetail && $currentCuttingPieceDetail->roll_buyer ? "" : "d-none" }}" id="roll_buyer_container">
                                <label class="form-label">No. Roll</label>
                                <input type="text" class="form-control" id="roll_buyer" name="roll_buyer" value="{{ $currentCuttingPieceDetail ? $currentCuttingPieceDetail->roll_buyer : null }}" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Role BOM</label>
                                <input type="text" class="form-control" id="rule_bom" name="rule_bom" value="{{ $currentCuttingPieceDetail ? $currentCuttingPieceDetail->rule_bom : null }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">QTY Pengeluaran</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="qty_pengeluaran" name="qty_pengeluaran" value="{{ $currentCuttingPieceDetail ? $currentCuttingPieceDetail->qty_pengeluaran : null }}" readonly>
                                    <input type="text" class="form-control" id="qty_pengeluaran_unit" name="qty_pengeluaran_unit" value="PCS" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">QTY</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="qty" name="qty" value="{{ $currentCuttingPieceDetail ? $currentCuttingPieceDetail->qty : null }}" readonly>
                                    <input type="text" class="form-control" id="qty_unit" name="qty_unit" value="PCS" readonly>
                                </div>
                            </div>
                            <div class="col-md-12 table-responsive table-container">
                                @if ($currentCuttingPieceDetail)
                                    <table class="table table-bordered w-100 mt-3">
                                        <thead>
                                            <tr>
                                                <th class="d-none">So Det ID</th>
                                                <th class="d-none">Size</th>
                                                <th class="d-none">Dest</th>
                                                <th>Size</th>
                                                <th>Dest</th>
                                                <th>Qty Output</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($currentCuttingPieceDetail->formCutPieceDetailSizes->sortBy("so_det_id") as $size)
                                                <tr>
                                                    <td class="d-none"><input type="hidden" id="so_det_id_{{ $loop->index }}" name="so_det_id[{{ $loop->index }}]" value="{{ $size->so_det_id }}" readonly></td>
                                                    <td class="d-none"><input type="hidden" id="size_{{ $loop->index }}" name="size[{{ $loop->index }}]" value="{{ $size->size }}" readonly></td>
                                                    <td class="d-none"><input type="hidden" id="dest_{{ $loop->index }}" name="dest[{{ $loop->index }}]" value="{{ $size->dest }}" readonly></td>
                                                    <td>{{ $size->size }}</td>
                                                    <td>{{ $size->dest }}</td>
                                                    <td><input type="number" id="qty_detail_{{ $loop->index }}" name="qty_detail[{{ $loop->index }}]" value="{{ $size->qty }}"></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th class="d-none"></th>
                                                <th class="d-none"></th>
                                                <th class="d-none"></th>
                                                <th></th>
                                                <th>Total</th>
                                                <th id="total-detail-qty">{{ $currentCuttingPieceDetail->qty_pemakaian }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                @else
                                    <table class="table table-bordered w-100 mt-3" id="cutting-piece-table">
                                        <thead>
                                            <tr>
                                                <th>So Det ID</th>
                                                <th>Size</th>
                                                <th>Dest</th>
                                                <th>Size</th>
                                                <th>Dest</th>
                                                <th>Qty Output</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <td colspan="6" class="text-center">Data tidak ditemukan</td>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th>Total</th>
                                                <th id="total-detail-qty">...</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">QTY Digunakan</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="qty_pemakaian" name="qty_pemakaian" value="{{ $currentCuttingPieceDetail ? $currentCuttingPieceDetail->qty_pemakaian : null }}" readonly>
                                    <input type="text" class="form-control" id="qty_pemakaian_unit" name="qty_pemakaian_unit" value="{{ $currentCuttingPieceDetail ? $currentCuttingPieceDetail->qty_unit : null }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">QTY Sisa</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="qty_sisa" name="qty_sisa" value="{{ $currentCuttingPieceDetail ? $currentCuttingPieceDetail->qty_sisa : null }}" readonly>
                                    <input type="text" class="form-control" id="qty_sisa_unit" name="qty_sisa_unit" value="{{ $currentCuttingPieceDetail ? $currentCuttingPieceDetail->qty_unit : null }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer border-1">
                        <button class="btn btn-sb btn-block" type="submit">SUBMIT</button>
                    </div>
                </div>
            </form>
        </div>
    {{-- END OF PROCESS THREE --}}

    {{-- FINISHING PROCESS --}}
        <button type="button" class="btn btn-sb-secondary btn-block d-none mb-3" onclick="finishProcess()" id="finish-button" disabled><i class="fas fa-flag-checkered"></i> FINISH PROCESS</button>
    {{-- END OF FINISHING PROCESS    --}}

    {{-- FINISH --}}
        <div id="cutting-piece-finish" class="my-5 {{ ($currentCuttingPiece ? ($currentCuttingPiece->process >= 3 ? "" : "d-none") : "d-none") }}">
            <h3 class="text-center text-sb fw-bold">PROCESS FINISHED</h3>
            <h5 class="text-center">Last Update : <span id="last-update" class="fw-bold">{{ $currentCuttingPiece ? $currentCuttingPiece->updated_at : "-" }}</span></h5>
        </div>
    {{-- END OF THE LINE --}}

    {{-- GO TO BOTTOM --}}
        <button id="scroll-to-bottom" class="scroll-to-bottom-btn">
            <i class="fas fa-arrow-down"></i>
        </button>
    {{-- END --}}
@endsection

@section('custom-script')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        // Initial Window On Load Event
        $(document).ready(async function () {
            if ($("#id").val() >= 1) {
                checkProcess();
            }

            disableFormSubmit("#process-one-form");
            disableFormSubmit("#process-two-form");
            disableFormSubmit("#process-three-form");
        });

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

        function startProcess() {
            Swal.fire({
                icon: 'question',
                title: 'Mulai Proses?',
                confirmButtonText: 'Mulai',
                showCancelButton: true,
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    initial();

                    generateCode();
                }
            });
        }

        function showFinishButton() {
            if (document.getElementById("process").value <= 2) {
                let button = document.getElementById("finish-button");

                button.removeAttribute("disabled");
                button.classList.remove("d-none");
            }
        }

        // Init
            async function initial() {
                document.getElementById("loading").classList.remove("d-none");

                $("#act_costing_id").removeAttr("disabled");
                $("#tanggal").removeAttr("disabled");
                $("#employee_id").removeAttr("disabled");
                $("#submit_process_one").removeAttr("disabled");
                $("#generate-code-button").removeAttr("disabled");
                $("#get-button-operator").removeAttr("disabled");
                $("#scan-button-operator").removeAttr("disabled");

                $("#start-process-button").addClass("d-none");
                $("#create-new-process-button").removeClass("d-none");

                if (document.getElementById("process").value > 0) {
                    // Check Current Process
                    await checkProcess(document.getElementById("process").value);
                } else {
                    //Reset Form
                    if (document.getElementById('process-one-form')) {
                        document.getElementById('process-one-form').reset();

                        $("#act_costing_id").val(null).trigger("change");
                    }

                    // Select2 Prevent Step-Jump Input ( Step = WS -> Color -> Panel )
                    $("#color").prop("disabled", true);
                    $("#panel").prop("disabled", true);

                    // Open Scan Operator
                    refreshScanOperator();
                }

                document.getElementById("switch-method").checked = true;

                await document.getElementById("loading").classList.add("d-none");
            }

        // CHECK PROCESS
            async function checkProcess(process) {
                document.getElementById("loading").classList.remove("d-none");

                switch (process) {
                    case "1" :
                        await initProcessTwo();

                        break;
                    case "2" :
                        await initProcessThree();

                        break;
                    case "3" :
                        await initFinish();

                        break;
                    default :
                        await initial();

                        break;
                }

                getProcessThree();
            }

        // PROCESS ONE
            // Generate Code
            function generateCode() {
                $.ajax({
                    url: "{{ route("generate-code-cutting-piece") }}",
                    type: "get",
                    success: function (response) {
                        if (response) {
                            document.getElementById("id").value = response.id;
                            document.getElementById("no_form").value = response.no_form;
                        }
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);
                    }
                });
            }

            // Step One (WS) on change event
            $('#act_costing_id').on('change', async function(e) {
                if (this.value) {
                    document.getElementById('loading').classList.remove("d-none");

                    await updateColorList();
                    await updateOrderInfo();

                    document.getElementById('loading').classList.add("d-none");
                }
            });

            // Step Two (Color) on change event
            $('#color').on('change', async function(e) {
                if (this.value) {
                    document.getElementById('loading').classList.remove("d-none");

                    await updatePanelList();
                    await cuttingPieceTableReload();

                    document.getElementById('loading').classList.add("d-none");

                }
            });

            // Step Three (Panel) on change event
            $('#panel').on('change', async function(e) {
                if (this.value) {
                    document.getElementById('loading').classList.remove("d-none");

                    await getNumber();

                    document.getElementById('loading').classList.add("d-none");
                } else {
                    document.getElementById('cons_ws').value = null;
                    document.getElementById('unit_cons_ws').value = null;
                }
            });

            // Update Order Information
            function updateOrderInfo() {
                return $.ajax({
                    url: '{{ route("get-general-order") }}',
                    type: 'get',
                    data: {
                        act_costing_id: $('#act_costing_id').val(),
                        color: $('#color').val(),
                    },
                    dataType: 'json',
                    success: function (res) {
                        if (res) {
                            document.getElementById('act_costing_ws').value = res.kpno;
                            document.getElementById('buyer_id').value = res.id_buyer;
                            document.getElementById('buyer').value = res.buyer;
                            document.getElementById('style').value = res.styleno;
                        } else {
                            document.getElementById('act_costing_ws').value = null;
                            document.getElementById('buyer_id').value = null;
                            document.getElementById('buyer').value = null;
                            document.getElementById('style').value = null;
                        }
                    },
                });
            }

            // Update Color Select Option Based on Order WS
            function updateColorList() {
                document.getElementById('color').innerHTML = null;
                document.getElementById('color').value = null;

                return $.ajax({
                    url: '{{ route("get-colors") }}',
                    type: 'get',
                    data: {
                        act_costing_id: $('#act_costing_id').val(),
                    },
                    success: function (res) {
                        if (res && res.length > 0) {
                            // Update this step
                            let select = document.getElementById("color");

                            select.innerHTML = "";

                            for (let i=0; i < res.length; i++) {
                                let newOption = document.createElement("option");
                                newOption.value = res[i].color;
                                newOption.innerHTML = res[i].color;

                                select.appendChild(newOption);
                            }

                            select.removeAttribute("disabled");

                            $("#color").val(res[0].color).trigger("change");
                        } else {
                            $("#color").val(null).trigger("change");
                        }
                    },
                });
            }

            // Update Panel Select Option Based on Order WS and Color WS
            function updatePanelList() {
                document.getElementById('panel').innerHTML = null;
                document.getElementById('panel').value = null;

                return $.ajax({
                    url: '{{ route("get-panels") }}',
                    type: 'get',
                    data: {
                        act_costing_id: $('#act_costing_id').val(),
                        color: $('#color').val(),
                    },
                    success: function (res) {
                        if (res && res.length > 0) {
                            // Update this step
                            let select = document.getElementById("panel");

                            select.innerHTML = "";

                            for (let i=0; i < res.length; i++) {
                                let newOption = document.createElement("option");
                                newOption.value = res[i].panel;
                                newOption.innerHTML = res[i].panel;

                                select.appendChild(newOption);
                            }

                            select.removeAttribute("disabled");

                            $("#panel").val(res[0].color).trigger("change");
                        } else {
                            $("#panel").val(null).trigger("change");
                        }
                    },
                });
            }

            // Get & Set Order WS Cons and Order Qty Based on Order WS, Order Color and Order Panel
            function getNumber() {
                document.getElementById('cons_ws').value = null;
                document.getElementById('unit_cons_ws').value = null;
                return $.ajax({
                    url: ' {{ route("get-general-number") }}',
                    type: 'get',
                    dataType: 'json',
                    data: {
                        act_costing_id: $('#act_costing_id').val(),
                        color: $('#color').val(),
                        panel: $('#panel').val()
                    },
                    success: function (res) {
                        if (res) {
                            document.getElementById('cons_ws').value = res.cons_ws;
                            document.getElementById('unit_cons_ws').value = res.unit_cons_ws;
                        }
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);
                    }
                });
            }

            // Check employee ID
            $("#employee_id").on("keyup", function(e) {
                if (e.keyCode === 13) {
                    e.preventDefault();

                    fetchScanOperator();
                }
            });

            // Scan Operator
            var html5QrcodeScannerOperator = new Html5Qrcode("reader-operator");
            var scannerInitializedOperator = false;

            // Initialize Scan Operator
            async function initScanOperator() {
                if (document.getElementById("reader-operator")) {
                    if (html5QrcodeScannerOperator == null || (html5QrcodeScannerOperator && (html5QrcodeScannerOperator.isScanning == false))) {
                        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                                // handle the scanned code as you like, for example:
                            console.log(`Code matched = ${decodedText}`, decodedResult);

                            // store to input text
                            let breakDecodedText = decodedText.split('-');

                            document.getElementById('employee_id').value = breakDecodedText[0];

                            getScannedOperator(breakDecodedText[0]);

                            clearQrCodeScannerOperator();
                        };
                        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                        await html5QrcodeScannerOperator.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
                    }
                }
            }

            // Stop and Clear Scan Operator
            async function clearQrCodeScannerOperator() {
                if (html5QrcodeScannerOperator && (html5QrcodeScannerOperator.isScanning)) {
                    await html5QrcodeScannerOperator.stop();
                    await html5QrcodeScannerOperator.clear();
                }
            }

            // Refresh Scan Operator
            async function refreshScanOperator() {
                await clearQrCodeScannerOperator();
                await initScanOperator();
            }

            // Fetch Scanned ID Operator
            function fetchScanOperator() {
                let idOperator = document.getElementById('employee_id').value;

                getScannedOperator(idOperator);
            }

            function getScannedOperator(id) {
                document.getElementById("loading").classList.remove("d-none");

                document.getElementById("employee_nik").value = "";
                document.getElementById("employee_name").value = "";

                if (isNotNull(id)) {
                    return $.ajax({
                        url: '{{ route('get-scanned-employee') }}/' + id,
                        type: 'get',
                        dataType: 'json',
                        success: function(res) {
                            if (res) {
                                if (res.enroll_id) {
                                    document.getElementById("employee_id").value = res.enroll_id;
                                    document.getElementById("employee_nik").value = res.nik;
                                    document.getElementById("employee_name").value = res.employee_name;
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: 'Operator tidak ditemukan.',
                                        showCancelButton: false,
                                        showConfirmButton: true,
                                        confirmButtonText: 'Oke',
                                    });
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: 'Terjadi kesalahan.',
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                });
                            }

                            document.getElementById("loading").classList.add("d-none");
                        },
                        error: function(jqXHR) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: jqXHR.responseText ? jqXHR.responseText : 'Terjadi kesalahan.',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });

                            document.getElementById("loading").classList.add("d-none");
                        }
                    });
                }

                return Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Item tidak ditemukan',
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                });
            }

            // Submit Process One
            function processOne(e, event) {
                document.getElementById("loading").classList.remove("d-none");

                event.preventDefault();

                let form = new FormData(e);

                let dataObj = {
                    "process": 1,
                }

                form.forEach((value, key) => dataObj[key] = value);

                $.ajax({
                    url: '{{ route('store-cutting-piece') }}',
                    type: 'post',
                    data: dataObj,
                    dataType: "json",
                    success: function (response) {

                        console.log("something", response);
                        if (response.status == 200) {
                            console.log("success", response);

                            if (response.additional) {
                                document.getElementById("id").value = response.additional.id;
                            }

                            initProcessTwo();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message,
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });
                        }

                        document.getElementById("loading").classList.add("d-none");
                    },
                    error: function (jqXHR) {
                        handleError(jqXHR.responseJSON);

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }
        // END OF PROCESS ONE


        // PROCESS TWO
            // Init Process Two
            function initProcessTwo() {
                clearItem();

                // -Scan
                initScanItem();
                // -Select
                getItemList();

                document.getElementById("process-two-form").classList.remove("d-none");

                let form = document.getElementById("process-one-form");
                for (let i = 0; i < form.length; i++) {
                    form[i].setAttribute("disabled", "true");
                }

                let thisForm = document.getElementById("process-two-form");
                for (let i = 0; i < thisForm.length; i++) {
                    thisForm[i].removeAttribute("disabled");
                }

                let afterForm = document.getElementById("process-three-form");
                for (let i = 0; i < thisForm.length; i++) {
                    afterForm[i].setAttribute("disabled", "true");
                }

                focusProcessTwo();
            }

            // Switch Method
            var method = "scan";

            function switchMethod(element, scanner = true) {
                clearItem();

                if (element.checked) {
                    toScanMethod(scanner);
                } else {
                    toSelectMethod();
                }
            }

            function toScanMethod(scanner = true) {
                method = "scan";

                document.getElementById("select-method").classList.add('d-none');
                document.getElementById("to-select").classList.add('d-none');

                document.getElementById("scan-method").classList.remove('d-none');
                document.getElementById("to-scan").classList.remove('d-none');

                if (scanner) {
                    initScanItem();
                }

                location.href = "#item-card";
            }

            function toSelectMethod() {
                method = "select";

                document.getElementById("scan-method").classList.add('d-none');
                document.getElementById("to-scan").classList.add('d-none');

                document.getElementById("select-method").classList.remove('d-none');
                document.getElementById("to-select").classList.remove('d-none');
                // $("#barang").val("").trigger("change");

                clearQrCodeScannerItem();

                location.href = "#item-card";
            }

            function clearItem() {
                document.getElementById("kode_barang").value = "";

                document.getElementById("id_item").value = "";
                document.getElementById("id_item").value = "";
                document.getElementById("detail_item").value = "";
                document.getElementById("qty_item").value = "";
                document.getElementById("unit_qty_item").value = "";
                document.getElementById("so_det_item").value = "";
                document.getElementById("sizes_item").value = "";

                document.getElementById("qty_item").setAttribute("readonly", true);
            }

            // Scan QR Module :
                // Variable List :
                    var html5QrcodeScanner = new Html5Qrcode("reader-item");
                    var scannerInitialized = false;
                // Function List :
                    // -Initialize Scanner-
                    async function initScanItem() {
                        if (document.getElementById("reader-item")) {
                            if (html5QrcodeScanner == null || (html5QrcodeScanner && (html5QrcodeScanner.isScanning == false))) {
                                const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                                    // handle the scanned code as you like, for example:
                                    console.log(`Code matched = ${decodedText}`, decodedResult);

                                    // store to input text
                                    let breakDecodedText = decodedText.split('-');

                                    document.getElementById('kode_barang').value = breakDecodedText[0];

                                    getScannedItem(breakDecodedText[0]);

                                    clearQrCodeScannerItem();
                                };
                                const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                                await html5QrcodeScanner.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
                            }
                        }
                    }

                    async function clearQrCodeScannerItem() {
                        if (html5QrcodeScanner && (html5QrcodeScanner.isScanning)) {
                            await html5QrcodeScanner.stop();
                            await html5QrcodeScanner.clear();
                        }
                    }

                    async function refreshScanItem() {
                        await clearQrCodeScannerItem();
                        await initScanItem();
                    }

                    // Lock Scan Item Form then Clear Scanner
                    function lockScanItemForm() {
                        document.getElementById("kode_barang").setAttribute("readonly", true);
                        document.getElementById("get-button").setAttribute("disabled", true);
                        document.getElementById("scan-button").setAttribute("disabled", true);
                        document.getElementById("switch-method").setAttribute("disabled", true);
                        document.getElementById("reader-item").classList.add("d-none");

                        clearQrCodeScannerItem();
                    }

                    // Open Scan Item Form then Open Scanner
                    function openScanItemForm() {
                        if (status != "SELESAI PENGERJAAN") {

                            document.getElementById("kode_barang").removeAttribute("readonly");
                            document.getElementById("get-button").removeAttribute("disabled");
                            document.getElementById("scan-button").removeAttribute("disabled");
                            document.getElementById("switch-method").removeAttribute("disabled");
                            document.getElementById("reader-item").classList.remove("d-none");

                            initScanItem();
                        }
                    }

                // Fetch Scanned Item Data
                function fetchScanItem() {
                    let kodeBarang = document.getElementById('kode_barang').value;

                    getScannedItem(kodeBarang);
                }

                function getScannedItem(id) {
                    document.getElementById("loading").classList.remove("d-none");

                    document.getElementById("id_item").value = "";
                    document.getElementById("detail_item").value = "";
                    document.getElementById("qty_item").value = "";
                    document.getElementById("unit_qty_item").value = "";
                    document.getElementById("so_det_item").value = "";
                    document.getElementById("sizes_item").value = "";

                    if (isNotNull(id)) {
                        return $.ajax({
                            url: '{{ route('get-scanned-form-cut-input') }}/' + id,
                            type: 'get',
                            data: {
                                unit: "PCS",
                                act_costing_id: $("#act_costing_id").val(),
                                act_costing_ws: $("#act_costing_ws").val(),
                                color: $("#color").val(),
                            },
                            dataType: 'json',
                            success: function(res) {
                                if (res) {
                                    if (res.qty > 0) {
                                        console.log(res);

                                        currentScannedItem = res;

                                        document.getElementById("id_item").value = res.id_item;
                                        document.getElementById("detail_item").value = res.detail_item;
                                        document.getElementById("qty_item").value = res.qty;
                                        document.getElementById("unit_qty_item").value = res.unit;
                                        document.getElementById("so_det_item").value = res.so_det_list;
                                        document.getElementById("sizes_item").value = res.size_list;
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Gagal',
                                            text: 'Qty sudah habis.',
                                            showCancelButton: false,
                                            showConfirmButton: true,
                                            confirmButtonText: 'Oke',
                                        });
                                    }
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: res ? res : 'Roll tidak tersedia.',
                                        showCancelButton: false,
                                        showConfirmButton: true,
                                        confirmButtonText: 'Oke',
                                    });
                                }

                                document.getElementById("loading").classList.add("d-none");
                            },
                            error: function(jqXHR) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: jqXHR.responseText ? jqXHR.responseText : 'Roll tidak tersedia.',
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                });

                                document.getElementById("loading").classList.add("d-none");
                            }
                        });
                    }

                    return Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Item tidak ditemukan',
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                    });
                }

            // Select Item Module :
                async function getItemList() {
                    $("#barang").prop("disabled", true);

                    await $.ajax({
                        url: '{{ route('get-item-form-cut-input') }}',
                        type: 'get',
                        data: {
                            act_costing_id: $("#act_costing_id").val(),
                            unit: "PCS"
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res) {
                                res.forEach((item) => {
                                    let option = document.createElement("option");
                                    option.text = item.id_item+" | "+item.itemdesc;
                                    option.value = item.id_item;
                                    option.setAttribute("data-detail", item.itemdesc);
                                    option.setAttribute("data-unit-qty", item.unit);
                                    option.setAttribute("data-sizes", item.sizes);

                                    document.getElementById("barang").appendChild(option);
                                });
                            }
                        },
                    });

                    $("#barang").prop("disabled", false);
                }

                function setSelectedItem() {
                    let element = document.getElementById("barang");

                    currentScannedItem = null;

                    if (element.value && element.value != "") {
                        document.getElementById("kode_barang").value = "";

                        document.getElementById("id_item").value = element.value;
                        document.getElementById("detail_item").value = $("#barang option:selected").attr("data-detail");
                        document.getElementById("qty_item").removeAttribute("readonly");
                        document.getElementById("unit_qty_item").value = $("#barang option:selected").attr("data-unit-qty");
                        document.getElementById("sizes_item").value = $("#barang option:selected").attr("data-sizes");

                        currentScannedItem = {"id_item": element.value, "detail_item": $("#detail_item").val(), "qty": $("#qty_item").val(), "unit": $("#unit_qty_item").val(), "sizes": $("#sizes_item")};
                    }
                }

            // Submit process two
                function processTwo(e, event) {
                    document.getElementById("loading").classList.remove("d-none");

                    event.preventDefault();

                    let form = new FormData(e);

                    let dataObj = {
                        "id": document.getElementById("id").value,
                        "method": document.getElementById("switch-method").checked ? "scan" : "select",
                        "process": 2,
                    }

                    form.forEach((value, key) => dataObj[key] = value);

                    $.ajax({
                        url: '{{ route('store-cutting-piece') }}',
                        type: 'post',
                        data: dataObj,
                        dataType: "json",
                        success: function (response) {
                            if (response.status == 200) {
                                clearQrCodeScannerItem();

                                initProcessThree(response.additional ? response.additional : null);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: response.message,
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                });
                            }

                            document.getElementById("loading").classList.add("d-none");
                        },
                        error: function (jqXHR) {
                            handleError(jqXHR.responseJSON);

                            document.getElementById("loading").classList.add("d-none");
                        }
                    });
                }

                function focusProcessTwo() {
                    let kodeBarang = document.getElementById("kode_barang");
                    let barang = document.getElementById("barang");

                    if (!kodeBarang.classList.contains("d-none")) {
                        kodeBarang.focus();
                    }

                    if (!barang.classList.contains("d-none")) {
                        barang.focus();
                    }
                }
        // END OF PROCESS TWO

        // PROCESS THREE
            // Init Process Three
            async function initProcessThree(item = null) {
                document.getElementById("process-three-form").classList.remove("d-none");

                // Process One
                let form1 = document.getElementById("process-one-form");
                for (let i = 0; i < form1.length; i++) {
                    form1[i].setAttribute("disabled", "true");
                }

                // Process Two
                let form2 = document.getElementById("process-two-form");
                for (let i = 0; i < form2.length; i++) {
                    form2[i].setAttribute("disabled", "true");
                }

                // Process Three
                let form3 = document.getElementById("process-three-form");
                for (let i = 0; i < form3.length; i++) {
                    form3[i].removeAttribute("disabled");
                }

                if (item) {
                    await setProcessThree(item);
                } else {
                    await getProcessThree();
                }

                focusProcessThree();
            }

            // Get Incomplete Item
            var completedItem = [];
            async function getProcessThree() {
                document.getElementById("loading").classList.remove("d-none");

                let id = document.getElementById("id").value;

                if (id) {
                    $.ajax({
                        type: "get",
                        url: "{{ route('incomplete-item-cutting-piece') }}/"+id,
                        dataType: "json",
                        success: async function (response) {
                            if (response && response.length > 0) {
                                console.log("item", response);

                                for (let i = 0; i < response.length; i++) {
                                    if (response[i].status == 'incomplete') {
                                        await setProcessThree(response[i]);
                                    } else {
                                        if (!completedItem.includes(response[i].id)) {
                                            await appendProcessThree(response[i]);
                                        }

                                        completedItem.push(response[i].id);
                                    }
                                }

                                if (completedItem.length > 0) {
                                    showFinishButton();
                                }
                            }

                            document.getElementById("loading").classList.add("d-none");
                        },
                    });
                }
            }

            // Set Item
            async function setProcessThree(item) {
                if (item.method == "scan") {
                    document.getElementById("lot").setAttribute("readonly", true);
                    document.getElementById("roll").setAttribute("readonly", true);
                    document.getElementById("roll_buyer").setAttribute("readonly", true);
                    document.getElementById("rule_bom").setAttribute("readonly", true);

                    document.getElementById("switch-method").checked = true;

                    await switchMethod(document.getElementById("switch-method"), false);
                } else if (item.method == "select") {
                    document.getElementById("lot").removeAttribute("readonly");
                    document.getElementById("roll").removeAttribute("readonly");
                    document.getElementById("roll_buyer").removeAttribute("readonly");
                    document.getElementById("rule_bom").removeAttribute("readonly");

                    document.getElementById("switch-method").checked = false;

                    await switchMethod(document.getElementById("switch-method"), false);
                }

                document.getElementById("group_roll").removeAttribute("readonly");

                document.getElementById("kode_barang").value = item.id_roll ? item.id_roll : "";
                document.getElementById("id_detail").value = item.id ? item.id : "";
                document.getElementById("id_roll").value = item.id_roll ? item.id_roll : "";
                document.getElementById("id_item").value = item.id_item ? item.id_item : "";
                document.getElementById("detail_item").value = item.detail_item ? item.detail_item : "";
                document.getElementById("qty_item").value = item.qty ? item.qty : "";
                document.getElementById("unit_qty_item").value = item.qty_unit ? item.qty_unit : "";
                document.getElementById("so_det_item").value = item.scanned_item && item.scanned_item['so_det_list'] ? item.scanned_item['so_det_list'] : "";
                document.getElementById("sizes_item").value = item.roll_buyer && item.scanned_item['size_list'] ? item.scanned_item['size_list'] : "";

                document.getElementById("lot").value = item.lot ? item.lot : "";
                document.getElementById("roll").value = item.roll ? item.roll : "";
                document.getElementById("roll_buyer").value = item.roll_buyer ? item.roll_buyer : "";
                if (item.roll_buyer) {
                    document.getElementById("roll_container").classList.add("d-none");
                    document.getElementById("roll_buyer_container").classList.remove("d-none");
                } else if (!item.roll_buyer) {
                    document.getElementById("roll_container").classList.remove("d-none");
                    document.getElementById("roll_buyer_container").classList.add("d-none");
                }
                document.getElementById("rule_bom").value = item.scanned_item ? item.scanned_item.rule_bom : "";
                document.getElementById("qty_pengeluaran").value = item.qty_pengeluaran ? item.qty_pengeluaran : "";
                document.getElementById("qty_pengeluaran_unit").value = item.qty_unit ? item.qty_unit : "";
                document.getElementById("qty").value = item.qty ? item.qty : "";
                document.getElementById("qty_unit").value = item.qty_unit ? item.qty_unit : "";
                document.getElementById("qty_pemakaian_unit").value = item.qty_unit ? item.qty_unit : "";
                document.getElementById("qty_sisa_unit").value = item.qty_unit ? item.qty_unit : "";

                cuttingPieceTableReload();

                lockSizeInput();
            }

            function appendProcessThree(item) {
                const card = document.getElementById("process-three-card");
                if (!card) return console.error("Card not found");

                const container = document.getElementById("process-three-container");
                const cloneCard = card.cloneNode(true);
                const suffix = "_" + item.id_roll;
                cloneCard.id = `process-three-card${suffix}`;
                cloneCard.classList.add("collapsed-card");

                // 💡 Header with collapse button (prevent duplicate)
                const header = cloneCard.querySelector(".card-header");
                if (header && !header.querySelector(".card-tools")) {
                    const tools = document.createElement("div");
                    tools.className = "card-tools";
                    tools.innerHTML = `<button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>`;
                    header.appendChild(tools);
                }

                // 💡 Set title
                const title = cloneCard.querySelector(".card-title");
                if (title) title.textContent = `${item.id_roll}`;

                // ✅ Value map from item
                const valueMap = {
                    group_roll: item.group_roll,
                    lot: item.lot,
                    roll: item.roll,
                    roll_buyer: item.roll_buyer,
                    rule_bom: item.scanned_item?.rule_bom,
                    qty_pengeluaran: item.qty_pengeluaran,
                    qty_pengeluaran_unit: item.qty_unit,
                    qty: item.qty,
                    qty_unit: item.qty_unit,
                    qty_pemakaian: item.qty_pemakaian,
                    qty_pemakaian_unit: item.qty_unit,
                    qty_sisa: item.qty_sisa,
                    qty_sisa_unit: item.qty_unit,
                };

                // 💡 Update all element IDs and set values if mapped
                cloneCard.querySelectorAll("[id]").forEach((el) => {
                    const oldId = el.id;
                    const newId = `${oldId}${suffix}`;
                    el.id = newId;

                    if (valueMap.hasOwnProperty(oldId)) {
                        if ("value" in el) el.value = valueMap[oldId] ?? "";
                        el.setAttribute("readonly", true);
                    }
                });

                // ✅ Toggle visibility for roll vs roll_buyer
                const rollContainer = cloneCard.querySelector(`#roll_container${suffix}`);
                const rollBuyerContainer = cloneCard.querySelector(`#roll_buyer_container${suffix}`);
                if (item.roll_buyer) {
                    rollContainer?.classList.add("d-none");
                    rollBuyerContainer?.classList.remove("d-none");
                } else {
                    rollContainer?.classList.remove("d-none");
                    rollBuyerContainer?.classList.add("d-none");
                }

                // Clean up the cloned DataTable (important!)
                const clonedTable = cloneCard.querySelector("table");
                if (clonedTable) {
                    if ($.fn.DataTable.isDataTable(clonedTable)) {
                        $(clonedTable).DataTable().destroy(); // Remove DT behavior from the clone
                    }
                    clonedTable.remove(); // Remove the cloned table completely
                }

                // Create a new clean table
                const tableWrapper = document.createElement("div");
                tableWrapper.innerHTML = `
                    <table id="detail-table${suffix}" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Size</th>
                                <th>Destination</th>
                                <th>Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${buildTableRows(item.form_cut_piece_detail_sizes || [], suffix)}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-right font-weight-bold">Total</td>
                                <td id="total-detail-qty${suffix}">${(item.form_cut_piece_detail_sizes || []).reduce((sum, row) => sum + Number(row.qty || 0), 0)}</td>
                            </tr>
                        </tfoot>
                    </table>
                `;

                // Append clean table into card body
                const tableContainer = cloneCard.querySelector(".table-container");
                if (tableContainer) {
                    tableContainer.innerHTML = "";
                    tableContainer.appendChild(tableWrapper.firstElementChild);
                }

                // Remove card-footer if needed
                cloneCard.querySelector(".card-footer")?.remove();

                container.appendChild(cloneCard);
            }


            function buildTableRows(details, suffix) {
                return details.map((row, index) => `
                    <tr>
                        <td>
                            <input type="hidden" name="so_det_id[${index}]" id="so_det_id_${index}${suffix}" value="${row.so_det_id}" />
                            ${row.size}
                        </td>
                        <td>
                            <input type="hidden" name="dest[${index}]" id="dest_${index}${suffix}" value="${row.dest}" />
                            ${row.dest}
                        </td>
                        <td>
                            <input type="number" name="qty_detail[${index}]" id="qty_detail_${index}${suffix}" value="${row.qty}" class="form-control" readonly />
                        </td>
                    </tr>
                `).join("");
            }

            // Size List
            let cuttingPieceTable = $("#cutting-piece-table").DataTable({
                processing: true,
                ordering: false,
                filter: false,
                serverSide: true,
                paging: false,
                ajax: {
                    url: '{{ route('get-general-sizes') }}',
                    data: function(d) {
                        d.act_costing_id = $("#act_costing_id").val();
                        d.color = $("#color").val();
                        d.so_det_list = $("#so_det_item").val();
                        d.size_list = $("#sizes_item").val();
                    },
                },
                columns: [
                    {
                        data: 'so_det_id', // so_det input
                    },
                    {
                        data: 'size', // size input
                    },
                    {
                        data: 'dest', // dest input
                    },
                    {
                        data: 'size',
                    },
                    {
                        data: 'dest',
                    },
                    {
                        data: null, // qty
                    },
                ],
                columnDefs: [
                    {
                        targets: [0],
                        className: "d-none",
                        render: (data, type, row, meta) => {
                            let input = `<input type='text' class='form-control form-control-sm' id='so_det_id_`+meta.row+`' name='so_det_id[`+meta.row+`]' value='`+data+`' readonly>`

                            return input;
                        }
                    },
                    {
                        targets: [1],
                        className: "d-none",
                        render: (data, type, row, meta) => {
                            let input = `<input type='text' class='form-control form-control-sm' id='size_`+meta.row+`' name='size[`+meta.row+`]' value='`+data+`' readonly>`

                            return input;
                        }
                    },
                    {
                        targets: [2],
                        className: "d-none",
                        render: (data, type, row, meta) => {
                            let input = `<input type='text' class='form-control form-control-sm' id='dest_`+meta.row+`' name='dest[`+meta.row+`]' value='`+data+`' readonly>`

                            return input;
                        }
                    },
                    {
                        targets: [3,4],
                        className: "text-nowrap",
                        render: (data, type, row, meta) => {
                            return data;
                        }
                    },
                    {
                        targets: [5],
                        className: "text-nowrap",
                        render: (data, type, row, meta) => {
                            let input = `<input type='number' class='form-control form-control-sm detail-qty' id='qty_detail_`+meta.row+`' name='qty_detail[`+meta.row+`]' data-so-det='`+row.so_det_id+`' onkeyup="calculateTotalDetailQty()" onchange="calculateTotalDetailQty()">`

                            return input;
                        }
                    }
                ]
            });

            function cuttingPieceTableReload() {
                $("#cutting-piece-table").DataTable().ajax.reload();
            }

            function lockSizeInput() {
                let soDetList = document.getElementById("so_det_item").value;

                let detailQty = document.getElementsByClassName("detail-qty");

                if (detailQty.length > 0) {
                    for (let i = 0; i < detailQty.length; i++) {
                        if (soDetList.includes(detailQty[i].getAttribute('data-so-det'))) {
                            detailQty[i].removeAttribute("readonly");
                        } else {
                            detailQty[i].setAttribute("readonly", true);
                        }
                    }
                }
            }

            function calculateTotalDetailQty() {
                let detailQtyElements = document.getElementsByClassName("detail-qty");

                let totalQty = 0;
                for (let i = 0; i < detailQtyElements.length; i++) {
                    console.log(i, detailQtyElements[i].value, detailQtyElements[i]);
                    totalQty += Number(detailQtyElements[i].value);
                }

                document.getElementById("total-detail-qty").innerHTML = totalQty;

                // Sisa
                let qtyItemElement = document.getElementById("qty");
                let qtyUseElement = document.getElementById("qty_pemakaian");
                let qtySisaElement = document.getElementById("qty_sisa");

                qtyUseElement.value = totalQty;
                qtySisaElement.value = Number(qtyItemElement.value) - totalQty;
            }

            function processThree(e, event) {
                document.getElementById("loading").classList.remove("d-none");

                event.preventDefault();

                let form = new FormData(e);

                let dataObj = {
                    "id": document.getElementById("id").value,
                    "id_detail": document.getElementById("id_detail").value,
                    "process": 3,
                }

                form.forEach((value, key) => dataObj[key] = value);

                $.ajax({
                    url: '{{ route('store-cutting-piece') }}',
                    type: 'post',
                    data: dataObj,
                    dataType: "json",
                    success: async function (response) {
                        if (response.status == 200) {
                            await getProcessThree();

                            await clearProcessThree();

                            await initProcessTwo();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message,
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });
                        }

                        document.getElementById("loading").classList.add("d-none");
                    },
                    error: function (jqXHR) {
                        handleError(jqXHR.responseJSON);

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }

            function clearProcessThree() {
                let thisForm = document.getElementById("process-three-form");
                for (let i = 0; i < thisForm.length; i++) {
                    thisForm[i].value = "";
                }

                document.getElementById("total-detail-qty").innerHTML = "...";
            }
        // END OF PROCESS THREE

        // FINISH PROCESS
            function finishProcess() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Akhiri Proses?',
                    html: 'Proses yang <b>BELUM SELESAI</b> akan <b>HILANG</b>.',
                    confirmButtonText: 'Akhiri',
                    confirmButtonColor: '#238380',
                    showCancelButton: true,
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        finish();
                    }
                });
            }

            function finish() {
                document.getElementById("loading").classList.remove("d-none");

                event.preventDefault();

                let dataObj = {
                    "id": document.getElementById("id").value,
                    "id_detail": document.getElementById("id_detail").value,
                    "process": 4,
                }

                $.ajax({
                    url: '{{ route('store-cutting-piece') }}',
                    type: 'post',
                    data: dataObj,
                    dataType: "json",
                    success: function (response) {
                        if (response.status == 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Data Cutting PCS berhasil diselesaikan.',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });

                            initFinish();

                            if (response.additional) {
                                console.log(response.additional)
                                setFinish(response.additional);
                            };
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message,
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });
                        }

                        document.getElementById("loading").classList.add("d-none");
                    },
                    error: function (jqXHR) {
                        handleError(jqXHR.responseJSON);

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }
        // END OF FINISH PROCESS

        // FINISHED
            function initFinish() {
                document.getElementById("cutting-piece-finish").classList.remove("d-none");

                // Process One
                let form1 = document.getElementById("process-one-form");
                for (let i = 0; i < form1.length; i++) {
                    form1[i].setAttribute("disabled", "true");
                }

                // Process Two
                let form2 = document.getElementById("process-two-form");
                for (let i = 0; i < form2.length; i++) {
                    form2[i].setAttribute("disabled", "true");
                }

                // Process Three
                let form3 = document.getElementById("process-three-form");
                for (let i = 0; i < form3.length; i++) {
                    form3[i].setAttribute("disabled", "true");
                }

                // Finish
                document.getElementById("finish-button").classList.add("d-none");
                document.getElementById("finish-button").setAttribute("readonly", true);
            }

            function setFinish(data) {
                if (data && data.updated_at) {
                    document.getElementById("last-update").innerText = formatDateTime(data.updated_at);
                }
            }

            function focusProcessThree() {
                document.getElementById("group_roll").focus();
            }
        // END OF THE LINE

        // GO TO BOTTOM
            const scrollBtn = document.getElementById("scroll-to-bottom");

            function toggleScrollBtn() {
                const scrolled = window.scrollY;
                const nearBottom = (window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 200);

                if (scrolled > 300 && !nearBottom) {
                    scrollBtn.classList.add("show");
                } else {
                    scrollBtn.classList.remove("show");
                }
            }

            window.addEventListener("scroll", toggleScrollBtn);

            scrollBtn.addEventListener("click", () => {
                const footer = document.querySelector("#finish-button");
                if (footer) {
                    footer.scrollIntoView({ behavior: "smooth", block: "center" });

                    // Optional: highlight the target briefly
                    footer.style.transition = "background-color 0.5s";
                    footer.style.backgroundColor = "#fbfbfb"; // yellow-ish
                    setTimeout(() => {
                        footer.style.backgroundColor = "";
                    }, 500);
                } else {
                    console.warn("No button found.");
                }
            });
        // END
    </script>
@endsection
