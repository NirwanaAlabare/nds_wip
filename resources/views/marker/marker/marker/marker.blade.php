@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-marker fa-sm"></i> Marker</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div class="d-flex justify-content-start align-items-end gap-3 mb-3">
                    <div class="mb-3">
                        <label class="form-label"><small>Dari</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" value="{{ date('Y-m-d') }}" onchange="filterTable()">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><small>Sampai</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}" onchange="filterTable()">
                    </div>
                    <div class="mb-3">
                        <button class="btn btn-primary btn-sm" onclick="filterTable()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div class="d-flex justify-content-end align-items-end gap-1 mb-3">
                    <a href="{{ route('create-marker') }}" class="btn btn-success btn-sm mb-3">
                        <i class="fas fa-plus"></i>
                        Buat
                    </a>
                    <button class="btn btn-info btn-sm mb-3 fw-bold" onclick="fixMarkerBalanceQty()">
                        <i class="fa-solid fa-screwdriver-wrench fa-sm"></i>
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table w-100">
                    <thead>
                        <tr>
                            <th class="align-bottom">Action</th>
                            <th>Tanggal</th>
                            <th>No. Marker</th>
                            <th>No. WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Panel</th>
                            <th>Urutan</th>
                            <th>Panjang</th>
                            <th>Lebar</th>
                            <th>Lebar WS</th>
                            <th>Gramasi</th>
                            <th>Gelar QTYs</th>
                            <th>Total Form</th>
                            <th>PO</th>
                            <th>Ket.</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Show Detail Marker Modal --}}
    <div class="modal fade" id="showMarkerModal" tabindex="-1" role="dialog" aria-labelledby="showMarkerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="showMarkerModalLabel"></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detail">
                    {{-- for ajax html response --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Marker Modal --}}
    <div class="modal fade" id="editMarkerModal" tabindex="-1" role="dialog" aria-labelledby="editMarkerModalLabel" aria-hidden="true">
        <form action="{{ route('update_marker') }}" method="post" onsubmit="submitForm(this, event)">
            @method('PUT')
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5" id="editMarkerModalLabel"></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class='row'>
                            <div class='col-sm-12'>
                                <div class='form-group'>
                                    <label class='form-label'><small class="fw-bold">Kode</small></label>
                                    <input type='text' class='form-control' id='txtkode_marker_edit' name='txtkode_marker_edit' value = '' readonly>
                                </div>
                            </div>
                            <div class='col-sm-12'>
                                <div class='form-group'>
                                    <label class='form-label'><small class="fw-bold">Gramasi</small></label>
                                    <input type='number' class='form-control' id='txt_gramasi' name='txt_gramasi' value = ''>
                                    <input type='hidden' class='form-control' id='id_c' name='id_c' value = ''>
                                </div>
                            </div>
                            {{-- Pilot Section --}}
                            <div class='col-sm-12' id="marker_pilot">
                                <div class='form-group'>
                                    <label class='form-label'><small class="fw-bold">Status Pilot</small></label>
                                    <div class="d-flex gap-3 ms-1">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="pilot_status" id="idle" value="idle">
                                            <label class="form-check-label" for="idle">
                                                <small class="fw-bold"><i class="fa fa-minus fa-sm"></i> Pilot Idle</small>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="pilot_status" id="active" value="active">
                                            <label class="form-check-label text-success" for="active">
                                                <small class="fw-bold"><i class="fa fa-check fa-sm"></i> Pilot Approve</small>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="pilot_status" id="not_active" value="not active">
                                            <label class="form-check-label text-danger" for="not_active">
                                                <small class="fw-bold"><i class="fa fa-times fa-sm"></i> Pilot Disapprove</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 d-none" id="advanced-edit-section">
                                <a href="" class="btn btn-primary btn-sm btn-block" id="advanced-edit-link"><i class="fas fa-edit"></i> Detail Edit</a>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('custom-script')
    @include('marker.marker.marker.marker-script')
@endsection
