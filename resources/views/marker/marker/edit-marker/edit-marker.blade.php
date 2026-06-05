@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold text-sb"><i class="fa fa-edit fa-sm"></i> Ubah Marker - {{ $marker->kode }}</h5>
        <a href="{{ route('marker') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali ke Marker</a>
    </div>
    @php
        $totalForm = $marker->formCutInputs->count();
    @endphp
    <form action="{{ route('update-marker')."/".$marker->id }}" method="post" id="store-marker" onsubmit="submitMarkerForm(this, event)">
        @method('PUT')
        <div class="card card-sb">
            <input type="hidden" id="id" name="id" value="{{ $marker->id }}">
            <div class="card-header">
                <h5 class="card-title fw-bold">
                    List Data
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label small">Tanggal</label>
                            <input type="date" class="form-control form-control-sm" id="tgl_cutting" name="tgl_cutting" value="{{ $marker->tgl_cutting }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label class="form-label small">No. WS</label>
                                <input type="text" class="form-control form-control-sm" id="no_ws" name="no_ws" value="{{ $marker->act_costing_ws }}" readonly>
                                <input type="hidden" class="form-control form-control-sm" id="ws_id" name="ws_id" value="{{ $marker->act_costing_id }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label class="form-label small">Color</label>
                                <input type="hidden" class="form-control form-control-sm" id="color" name="color" value="{{ strtoupper(trim($marker->color)) }}">
                                <select class="form-control select2bs4" id="color_select2" name="color_select2" style="width: 100%;">
                                    <option selected="selected" value="">Pilih Color</option>
                                    {{-- select 2 option --}}
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label class="form-label small">Panel</label>
                                <input type="hidden" class="form-control form-control-sm" id="panel_id_default" name="panel_id_default" value="{{ $marker->panel_id }}">
                                <select class="form-control select2bs4" id="panel_id" name="panel_id" style="width: 100%;">
                                    <option selected="selected" value="">Pilih Panel</option>
                                    {{-- select 2 option --}}
                                </select>
                                <input type="hidden" class="form-control d-none" id="panel_default" name="panel_default" value="{{ $marker->panel }}" readonly>
                                <input type="hidden" class="form-control d-none" id="panel" name="panel" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="d-flex flex-column">
                            <input type="hidden" class="form-control form-control-sm" id="ws" name="ws" value="{{ $marker->act_costing_ws }}" readonly>
                            <div class="mb-1">
                                <label class="form-label small">Buyer</label>
                                <input type="text" class="form-control form-control-sm" id="buyer" name="buyer" value="{{ $marker->buyer }}" readonly>
                            </div>
                            <div class="mb-1">
                                <label class="form-label small">Style</label>
                                <input type="text" class="form-control form-control-sm" id="style" name="style" value="{{ $marker->style }}" readonly>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="mb-1">
                                        <label class="form-label small">Cons. WS</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" id="cons_ws" name="cons_ws" value="{{ $marker->cons_ws ? round($marker->cons_ws, 3) : 0 }}" readonly>
                                            <input type="text" class="form-control" id="unit_cons_ws" name="unit_cons_ws" value="{{ $marker->unit_cons_ws ? $marker->unit_cons_ws : 'METER' }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-1">
                                        <label class="form-label small">Qty Order</label>
                                        <input type="text" class="form-control form-control-sm" id="order_qty" name="order_qty" value="" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-1">
                                        <label class="form-label small">No. Urut Marker</label>
                                        <input type="text" class="form-control form-control-sm" id="no_urut_marker" name="no_urut_marker" value="{{ $marker->urutan_marker }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <label class="form-label small">Panjang Marker</label>
                                <div class="input-group input-group-sm mb-1">
                                    <input type="number" class="form-control" id="p_marker" name="p_marker" step=".001" value="{{ $marker->panjang_marker }}" {{ $totalForm > 0 ? "readonly" : "" }}>
                                    <span class="input-group-text">METER</span>
                                </div>
                                <input type="hidden" class="form-control" id="p_unit" name="p_unit" value="METER" readonly>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small">Comma</label>
                                <div class="input-group input-group-sm mb-1">
                                    <input type="number" class="form-control" id="comma_marker" name="comma_marker" step=".001" value="{{ $marker->comma_marker }}" {{ $totalForm > 0 ? "readonly" : "" }}>
                                    <span class="input-group-text">CM</span>
                                </div>
                                <input type="hidden" class="form-control" id="comma_unit" name="comma_unit" value="CM" readonly>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small">L. Marker</label>
                                <div class="input-group input-group-sm mb-1">
                                    <input type="number" class="form-control" id="l_marker" name="l_marker" step=".001" value="{{ $marker->lebar_marker }}" {{ $totalForm > 0 ? "readonly" : "" }}>
                                    <span class="input-group-text">CM</span>
                                </div>
                                <input type="hidden" class="form-control" id="l_unit" name="l_unit" value="CM" readonly>
                            </div>
                            <div class="col-6 col-md-6">
                                <div class="mb-1">
                                    <label class="form-label small">Cons Marker</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" id="cons_marker" name="cons_marker" step=".001" value="{{ $marker->cons_marker ? $marker->cons_marker : 0 }}" {{ $totalForm > 0 ? "readonly" : "" }}>
                                        <input type="text" class="form-control" id="unit_cons_marker" name="unit_cons_marker" step=".001" value="{{ $marker->unit_cons_marker ? $marker->unit_cons_marker : 'METER' }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4 col-md-4">
                                <div class="mb-1">
                                    <label class="form-label small">Cons Piping</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" id="cons_piping" name="cons_piping" step=".001" value="{{ $marker->cons_piping ? $marker->cons_piping : 0 }}" {{-- $totalForm > 0 ? "readonly" : "" --}}>
                                        <input type="text" class="form-control" id="unit_cons_piping" name="unit_cons_piping" step=".001" value="{{ $marker->unit_cons_piping ? $marker->unit_cons_piping : 'METER' }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4 col-md-4">
                                <div class="mb-1">
                                    <label class="form-label small">Gramasi</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" id="gramasi" name="gramasi" step=".001" value="{{ $marker->gramasi ? $marker->gramasi : 0 }}" {{-- $totalForm > 0 ? "readonly" : "" --}}>
                                        <input type="text" class="form-control" id="unit_gramasi" name="unit_gramasi" step=".001" value="gr/cm²" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4 col-md-4">
                                <div class="mb-1">
                                    <label class="form-label small">Qty Gelar Marker</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" id="gelar_marker_qty" name="gelar_marker_qty" onchange="calculateAllRatio(this)" onkeyup="calculateAllRatio(this)" value="{{ $marker->gelar_qty ? $marker->gelar_qty : 0 }}" {{-- {{ $totalForm > 0 ? "readonly" : "" }} --}}>
                                        <input type="text" class="form-control" id="unit_gelar_marker_qty" name="unit_gelar_marker_qty" value="Ply" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label small">PO</label>
                            <input type="text" class="form-control form-control-sm" id="po" name="po" value="{{ $marker->po_marker }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Lebar WS</label>
                        <div class="input-group input-group-sm mb-1">
                            <input type="number" class="form-control" id="lebar_ws" name="lebar_ws" value="{{ $marker->lebar_ws ? $marker->lebar_ws : 0 }}" step=".001" >
                            <span class="input-group-text">CM</span>
                        </div>
                        <input type="hidden" class="form-control" id="lebar_ws_unit" name="lebar_ws_unit" value="CM">
                    </div>
                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label small">Tipe Marker</label>
                            <select class="form-select select2bs4" id="tipe_marker" name="tipe_marker" style="width: 100%;" {{ $totalForm > 0 ? "disabled" : "" }}>
                                <option value="regular marker" {{ $marker->tipe_marker == "regular marker" ? "selected" : "" }}>Regular Marker</option>
                                <option value="special marker" {{ $marker->tipe_marker == "special marker" ? "selected" : "" }}>Special Marker</option>
                                <option value="pilot marker" {{ $marker->tipe_marker == "pilot marker" ? "selected" : "" }}>Pilot Marker</option>
                                <option value="bulk marker" {{ $marker->tipe_marker == "bulk marker" ? "selected" : "" }}>Bulk Marker</option>
                            </select>
                        </div>
                        @if ($totalForm > 0)
                            <input type="hidden" id="tipe_marker" name="tipe_marker" value="{{ $marker->tipe_marker }}">
                        @endif
                    </div>
                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label small">Catatan</label>
                            <textarea class="form-control form-control-sm" id="notes" name="notes"></textarea>
                        </div>
                    </div>
                    <input type="hidden" class="form-control" id="jumlah_so_det" name="jumlah_so_det" readonly>
                </div>
            </div>
        </div>
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold">
                    Data Ratio
                </h5>
            </div>
            <div class="card-body table-responsive">
                <table id="orderQtyDatatable" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>WS</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Size Input</th>
                            <th>QTY Order</th>
                            <th>Sisa</th>
                            <th>Persentase</th>
                            <th>So Det Id</th>
                            <th>Ratio</th>
                            <th>Cut Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th id="total_ratio"></th>
                            <th id="total_cut_qty"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <button class="btn btn-sm btn-block btn-sb-secondary my-3 fw-bold"><i class="fas fa-save"></i> SIMPAN</button>
    </form>
@endsection

@section('custom-script')
    @include('marker.marker.edit-marker.edit-marker-script')
@endsection
