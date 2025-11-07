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
    <div class="card card-sb">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold">
                    <i class="fa fa-circle-plus fa-sm"></i> Atur Part Detail
                </h5>
                <a href="{{ route('part') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-reply"></i> Kembali ke Part
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="#" method="post">
                <div class="row">
                    <input type="hidden" class="form-control form-control-sm" name="id" id="id" value="{{ $part->id }}" readonly>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Kode Part</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="kode" id="kode" value="{{ $part->kode }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>No. WS</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="ws" id="ws" value="{{ $part->act_costing_ws }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Buyer</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="buyer" id="buyer" value="{{ $part->buyer }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Style</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="style" id="style" value="{{ $part->style }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Color</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="color" id="color" value="{{ $part->color }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Panel</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="panel" id="panel" value="{{ $part->panel }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Panel Status</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="panel_status" id="panel_status" value="{{ strtoupper($part->panel_status) }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Parts</b></small></label>
                            @php
                                $partDetails = explode(",", $part->part_details);
                            @endphp
                            <ul class="list-group">
                                @if ($partDetails)
                                    @for ($i = 0; $i < count($partDetails); $i++)
                                        <li class="list-group-item">{{ $partDetails[$i] }}</li>
                                    @endfor
                                @endif
                            </ul>
                            {{-- <input type="text" class="form-control form-control-sm" name="part_details" id="part_details" value="{{ $part->part_details }}" readonly> --}}
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- SECONDARY --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card h-100">
                <div class="card-header bg-sb-secondary">
                    <h5 class="card-title fw-bold">
                        <i class="fa fa-list fa-sm"></i> Tambah Part Secondary
                    </h5>
                    <div class='card-tools'>
                        <button type='button' class='btn btn-tool' data-card-widget='collapse'><i class='fas fa-plus'></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="post" id="store-secondary" name='form'>
                        <div class="row mb-3">
                            <div class="col-4">
                                <label><small><b>Part</b></small></label>
                                <select class="form-control select2bs4" id="txtpart" name="txtpart" style="width: 100%;">
                                    <option selected="selected" value="">Pilih Part</option>
                                    @foreach ($data_part as $datapart)
                                        <option value="{{ $datapart->id }}">
                                            {{ $datapart->nama_part . ' - ' . $datapart->bag }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-4">
                                <label><small><b>Cons</b></small></label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" name="txtcons" id="txtcons">
                                    <div class="input-group-prepend">
                                        <select class="form-select" style="border-radius: 0 3px 3px 0;" name="txtconsunit" id="txtconsunit">
                                            <option value="meter">METER</option>
                                            <option value="yard">YARD</option>
                                            <option value="kgm">KGM</option>
                                            <option value="pcs">PCS</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <label class="form-label"><small>Tujuan</small></label>
                                <select class="form-control select2bs4" style="border-radius: 0 3px 3px 0;" name="tujuan" id="tujuan" onchange="switchTujuan(this)">
                                    <option value="">Pilih Tujuan</option>
                                    <option value="NON SECONDARY">NON SECONDARY</option>
                                    <option value="SECONDARY">SECONDARY</option>
                                </select>
                            </div>
                            <div class="col-6 d-none" id="non_secondary_container">
                                <label class="form-label"><small>Proses</small></label>
                                <select class="form-control select2bs4" style="border-radius: 0 3px 3px 0;" name="proses" id="proses" onchange="orderNonSecondary(this)">
                                    <option value="">Pilih Proses</option>
                                    @foreach ($data_secondary->where("tujuan", "NON SECONDARY") as $secondary)
                                        <option value="{{ $secondary->id }}" data-tujuan="{{ $secondary->id_tujuan }}">{{ $secondary->proses." / ".$secondary->tujuan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 d-none" id="secondary_container">
                                <label class="form-label"><small>Proses</small></label>
                                <div class="d-flex gap-1">
                                    <select class="form-control select2bs4" id="secondaries" name="secondaries[]" data-width="100%" multiple onchange="orderSecondary(this)">
                                        @foreach ($data_secondary->where("tujuan", "!=", "NON SECONDARY") as $secondary)
                                            <option value="{{ $secondary->id }}" data-tujuan="{{ $secondary->id_tujuan }}">{{ $secondary->proses." / ".$secondary->tujuan }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-sm btn-dark ps-1 ms-2" onclick="clearSelectOptions(this)">
                                        <i class="fa fa-rotate-left"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-6 d-none" id="urutan_container">
                                <label class="form-label"><small>Urutan</small></label>
                                <ul class="list-group" id="urutan_show">
                                </ul>
                                <input type="text" class="form-control d-none" id="urutan" name="urutan[]" readonly>
                            </div>
                            <div class="col-12 col-md-12 mt-3">
                                <button type="button" class="btn btn-block btn-sb-secondary btn-sm" name="simpan" id="simpan" onclick="simpan_data();">SIMPAN <i class="fa fa-save"></i></button>
                            </div>
                            {{-- <div class="col-6 col-md-3">
                                <div class="mb-4">
                                    <label><small><b>Tujuan</b></small></label>
                                    <select class="form-control select2bs4" id="cbotuj" name="cbotuj" style="width: 100%;" onchange="getproses();">
                                        <option selected="selected" value="">Pilih Tujuan</option>
                                        @foreach ($data_tujuan as $datatujuan)
                                            <option value="{{ $datatujuan->isi }}">
                                                {{ $datatujuan->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> --}}
                            {{-- <div class="col-6 col-md-3">
                                <div class="row align-items-end">
                                    <div class="col-9">
                                        <div class="mb-4">
                                            <label><small><b>Proses</b></small></label>
                                            <select class="form-control select2bs4 w-100" id="cboproses" name="cboproses" style="width: 100%;">
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table id="datatable_list_part" class="table table-bordered table w-100">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Part</th>
                                    <th>Cons.</th>
                                    <th>Satuan</th>
                                    <th>Tujuan</th>
                                    <th>Proses</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tambah Part Secondary Complement --}}
    <div class="row mb-3 {{ $part->panel_status != 'main' ? 'd-none' : '' }}">
        <div class="col-12">
            <div class="card h-100">
                <div class="card-header bg-sb">
                    <h5 class="card-title fw-bold">
                        <i class="fa fa-list fa-sm"></i> Tambah Complement Part Secondary
                    </h5>
                    <div class='card-tools'>
                        <button type='button' class='btn btn-tool' data-card-widget='collapse'><i class='fas fa-plus'></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="post" id="store-complement-secondary" name='form'>
                        <div class="row mb-3">
                            <div class="col-6 col-md-3">
                                <label><small><b>Part</b></small></label>
                                <select class="form-control select2bs4" id="com_txtpart" name="com_txtpart" style="width: 100%;">
                                    <option selected="selected" value="">Pilih Part</option>
                                    @foreach ($data_part as $datapart)
                                        <option value="{{ $datapart->id }}">
                                            {{ $datapart->nama_part . ' - ' . $datapart->bag }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-3">
                                <label><small><b>Cons</b></small></label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" name="com_txtcons" id="com_txtcons">
                                    <div class="input-group-prepend">
                                        <select class="form-select" style="border-radius: 0 3px 3px 0;" name="com_txtconsunit" id="com_txtconsunit">
                                            <option value="meter">METER</option>
                                            <option value="yard">YARD</option>
                                            <option value="kgm">KGM</option>
                                            <option value="pcs">PCS</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <label class="form-label"><small>From Panel</small></label>
                                <select class="form-control select2bs4 com_from_panel_id" name="com_from_panel_id" id="com_from_panel_id" onchange="updateComplementPanelPartList()">
                                    <option value="">Pilih Panel</option>
                                    @foreach ($complementPanels as $panels)
                                        <option value="{{ $panels->id }}">{{ $panels->panel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label"><small>From Part</small></label>
                                <select class="form-control select2bs4" name="com_from_part_id" id="com_from_part_id">
                                    <option value="">Pilih Part</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-12 mt-3">
                                <button type="button" class="btn btn-block btn-sb btn-sm" name="com_simpan" id="com_simpan" onclick="simpan_data_com();">SIMPAN <i class="fa fa-save"></i></button>
                                {{-- <input type="button" class="btn bg-primary w-100" name="simpan" id="simpan" value="Simpan" onclick="simpan_data();"> --}}
                            </div>
                            {{-- <div class="col-6 col-md-3">
                                <div class="mb-4">
                                    <label><small><b>Tujuan</b></small></label>
                                    <select class="form-control select2bs4" id="cbotuj" name="cbotuj" style="width: 100%;" onchange="getproses();">
                                        <option selected="selected" value="">Pilih Tujuan</option>
                                        @foreach ($data_tujuan as $datatujuan)
                                            <option value="{{ $datatujuan->isi }}">
                                                {{ $datatujuan->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> --}}
                            {{-- <div class="col-6 col-md-3">
                                <div class="row align-items-end">
                                    <div class="col-9">
                                        <div class="mb-4">
                                            <label><small><b>Proses</b></small></label>
                                            <select class="form-control select2bs4 w-100" id="cboproses" name="cboproses" style="width: 100%;">
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table id="datatable_list_part_complement" class="table table-bordered table w-100">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Part</th>
                                    <th>Cons.</th>
                                    <th>Satuan</th>
                                    <th>Tujuan</th>
                                    <th>Proses</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <form action="{{ route('update-part-secondary') }}" method="post" id="update_part_secondary_form" onsubmit="submitForm(this, event)">
        @method("PUT")
        <div class="modal fade" id="editPartSecondaryModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="editPartSecondaryModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-sb">
                        <h1 class="modal-title fs-5" id="editPartSecondaryModalLabel"><i class="fa fa-edit"></i> Edit Part Detail</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <input type="hidden" class="form-control" readonly id="edit_id" name="edit_id">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Part</label>
                            <input type="text" class="form-control" id="edit_nama_part" name="edit_nama_part" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ubah Part</label>
                            <select class="form-select select2bs4edit" name="edit_master_part_id" id="edit_master_part_id">
                                <option selected="selected" value="">Pilih Part</option>
                                @foreach ($data_part as $datapart)
                                    <option value="{{ $datapart->id }}">
                                        {{ $datapart->nama_part . ' - ' . $datapart->bag }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cons</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="edit_cons" name="edit_cons" step="0.001">
                                <input type="text" class="form-control" id="edit_unit" name="edit_unit" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tujuan</label>
                            <select class="form-control select2bs4" style="border-radius: 0 3px 3px 0;" name="edit_tujuan" id="edit_tujuan" onchange="switchTujuan(this, 'edit_')">
                                <option value="">Pilih Tujuan</option>
                                <option value="NON SECONDARY">NON SECONDARY</option>
                                <option value="SECONDARY">SECONDARY</option>
                            </select>
                        </div>
                        <div class="mb-3 d-none" id="edit_non_secondary_container">
                            <label class="form-label">Proses</label>
                            <select class="form-control select2bs4" style="border-radius: 0 3px 3px 0;" name="edit_proses" id="edit_proses" onchange="orderNonSecondary(this, 'edit_')">
                                <option value="">Pilih Proses</option>
                                @foreach ($data_secondary->where("tujuan", "NON SECONDARY") as $secondary)
                                    <option value="{{ $secondary->id }}" data-tujuan="{{ $secondary->id_tujuan }}">{{ $secondary->proses." / ".$secondary->tujuan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 d-none" id="edit_secondary_container">
                            <label class="form-label">Proses</label>
                            <div class="d-flex gap-1">
                                <select class="form-control select2bs4" id="edit_secondaries" name="edit_secondaries[]" data-width="100%" multiple onchange="orderSecondary(this, 'edit_')">
                                    @foreach ($data_secondary->where("tujuan", "!=", "NON SECONDARY") as $secondary)
                                        <option value="{{ $secondary->id }}" data-tujuan="{{ $secondary->id_tujuan }}">{{ $secondary->proses." / ".$secondary->tujuan }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-sm btn-dark ps-1 ms-2" onclick="clearSelectOptions(this,'edit_')">
                                    <i class="fa fa-rotate-left"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3 d-none" id="edit_urutan_container">
                            <label class="form-label">Urutan</label>
                            <ul class="list-group" id="edit_urutan_show">
                            </ul>
                            <input type="text" class="form-control d-none" id="edit_urutan" name="edit_urutan" readonly>
                        </div>
                        {{-- <div class="mb-3">
                            <label class="form-label">Tujuan</label>
                            <select class="form-select select2bs4" name="edit_tujuan" id="edit_tujuan" onchange="getproses(document.getElementById('edit_proses'), this);">
                                @foreach ($data_tujuan as $datatujuan)
                                    <option value="{{ $datatujuan->isi }}">
                                        {{ $datatujuan->tampil }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Proses</label>
                            <select class="form-select select2bs4" name="edit_proses" id="edit_proses"></select>
                        </div> --}}
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sb-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-sb">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- FORM --}}
    <div class="row mb-3">
        <div class="col-12 mb-3">
            <div class="card collapsed-card card-primary h-100">
                <div class="card-header">
                    <h5 class="card-title fw-bold">
                        <i class="fa-regular fa-hourglass-half"></i> Form Cut Pending :
                    </h5>
                    <div class='card-tools'>
                        <button type='button' class='btn btn-tool' data-card-widget='collapse'><i class='fas fa-plus'></i></button>
                    </div>
                </div>
                <div class="card-body">
                    {{-- <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <h5 class="mb-1">Harap cek kembali data <strong>Form</strong> jika <strong>Form</strong> tidak muncul.</h5>
                        <hr>
                        <p>Mungkin <a href="{{ route('marker-panel') }}"><strong>Panel Marker</strong></a> dari <strong>Form</strong> yang tidak muncul tersebut tidak sesuai dengan <strong>Panel</strong> dari <strong>Part</strong> yang telah dibuat. Jika kesulitan bisa hubungi IT.</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" onclick="updatePartFormInfoReaded()"></button>
                    </div> --}}
                    {{-- <div class="d-flex gap-3">
                        <div class="mb-3">
                            <label><small><b>Tgl Awal</b></small></label>
                            <input type="date" class="form-control form-control-sm w-auto" id="tgl_awal" name="tgl_awal" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label><small><b>Tgl Akhir</b></small></label>
                            <input type="date" class="form-control form-control-sm w-auto" id="tgl_akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}">
                        </div>
                    </div> --}}
                    <div class="row justify-content-between mb-3 align-items-center">
                        <div class="col-6">
                            <p class="mb-0">Form yang dipilih : <span class="fw-bold" id="selected-row-count-2">0</span></p>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-success btn-sm float-end fw-bold" onclick="addToPartForm(this)">
                                <i class="fa fa-arrow-right fa-sm"></i> FORM IN
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="datatable-select" class="table table-bordered table w-100">
                            <thead>
                                <tr>
                                    <th>Tgl Spreading</th>
                                    <th>No. Form</th>
                                    <th>No. Meja</th>
                                    <th>No. Cut</th>
                                    <th>Style</th>
                                    <th>Color</th>
                                    <th>Panel</th>
                                    <th>Qty Ply</th>
                                    <th>Size Ratio</th>
                                    <th>Marker</th>
                                    <th>WS</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 mb-3">
            <div class="card collapsed-card card-success h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0 fw-bold" style="padding-bottom: 2px">
                        <i class="fa fa-check"></i> Form Cut In :
                    </h5>
                    <div class='card-tools'>
                        <button type='button' class='btn btn-tool' data-card-widget='collapse'><i class='fas fa-plus'></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row justify-content-between align-items-center mb-3">
                        <div class="col-6">
                            <p class="mb-0">Form yang dipilih : <span class="fw-bold" id="selected-row-count-1">0</span></p>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-primary btn-sm float-end fw-bold" onclick="removePartForm()">
                                <i class="fa fa-arrow-left fa-sm"></i> FORM OUT
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="datatable-selected" class="table table-bordered table w-100">
                            <thead>
                                <tr>
                                    <th>Tgl Spreading</th>
                                    <th>No. Form</th>
                                    <th>No. Meja</th>
                                    <th>No. Cut</th>
                                    <th>Style</th>
                                    <th>Color</th>
                                    <th>Panel</th>
                                    <th>Qty Ply</th>
                                    <th>Size Ratio</th>
                                    <th>Marker</th>
                                    <th>WS</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Page specific script -->
    <script>
        //Initialize Select2 Elements
        $('.select2').select2()

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        //Initialize Select2 Edit Elements
        $('.select2bs4edit').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#editPartSecondaryModal')
        })

        cleardata();
        dataTableReload();
        dataTableComplementReload();

        function cleardata() {
            $("#cboproses").val('').trigger('change');
            $("#cbotuj").val('').trigger('change');
            $("#txtpart").val('').trigger('change');
            $("#txtcons").val('').trigger('change');
            $("#txtconsunit").val('METER').trigger('change');
        }

        async function getproses(element, basedOn) {
            let cbotuj = basedOn ? basedOn.value : document.form.cbotuj.value;
            let html = $.ajax({
                type: "GET",
                url: '{{ route('get_proses') }}',
                data: {
                    cbotuj: cbotuj
                },
                async: false
            }).responseText;

            if (html != "") {
                if (element) {
                    element.innerHTML = html;
                    console.log(element.innerHTML, html);
                } else {
                    $("#cboproses").html(html);
                }
            }
        };

        function dataTableReload() {
            let datatable = $("#datatable_list_part").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                paging: false,
                destroy: true,
                ajax: {
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('datatable_list_part') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: function(d) {
                        d.id = $('#id').val();
                    },
                },
                columns: [
                    {
                        data: 'id',
                    },
                    {
                        data: 'nama_part',
                    },
                    {
                        data: 'cons',
                    },
                    {
                        data: 'unit',
                    },
                    {
                        data: 'tujuan',
                    },
                    {
                        data: 'proses',
                    },
                    {
                        data: 'part_status',
                    },
                ],
                columnDefs: [
                    {
                        targets: [0],
                        className: "text-center",
                        render: (data, type, row, meta) => {
                            let disableDelete = (row.total_stocker > 0 ? 'disabled' : '');
                            return `
                                <button class='btn btn-primary btn-sm' onclick='editData(`+JSON.stringify(row)+`, "editPartSecondaryModal")'>
                                    <i class='fa fa-edit'></i>
                                </button>
                                <button class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route('destroy-part-detail') }}/`+row['id']+`' onclick='deleteData(this)' {{ Auth::user()->roles->whereIn("nama_role", ["admin", "superadmin"])->count() > 0 ? '' : '`+(disableDelete)+`'}}>
                                    <i class='fa fa-trash'></i>
                                </button>
                            `;
                        }
                    },
                    {
                        targets: [6],
                        render: (data, type, row, meta) => {
                            return data ? data.toUpperCase() : '-';
                        }
                    }
                ]
            });
        }

        function simpan_data() {
            let id = document.getElementById("id").value;
            // let cbotuj = document.getElementById('cbotuj') ? document.getElementById('cbotuj').value : '';
            // let cboproses = document.getElementById('cboproses') ? document.getElementById('cboproses').value : '';
            let txtpart = document.getElementById('txtpart') ? document.getElementById('txtpart').value : '';
            let txtcons = document.getElementById('txtcons') ? document.getElementById('txtcons').value : '';
            let txtconsunit = document.getElementById('txtconsunit') ? document.getElementById('txtconsunit').value : '';
            // New
            let tujuan = document.getElementById('tujuan') ? document.getElementById('tujuan').value : '';
            let urutan = document.getElementById('urutan') ? document.getElementById('urutan').value : '';

            // Loading
            document.getElementById("loading").classList.remove("d-none");

            $.ajax({
                type: "post",
                url: '{{ route('store_part_secondary') }}',
                data: {
                    id: id,
                    txtpart: txtpart,
                    txtcons: txtcons,
                    txtconsunit: txtconsunit,
                    tujuan: tujuan,
                    urutan: urutan
                },
                success: function(response) {
                    document.getElementById("loading").classList.add("d-none");

                    if (response.icon == 'salah') {
                        iziToast.warning({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    } else {
                        iziToast.success({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    }
                    dataTableReload();
                    cleardata();
                },
                error: function(request, status, error) {
                    document.getElementById("loading").classList.add("d-none");

                    alert(request.responseText);
                },
            });
        };

        function simpan_data_com() {
            let id = document.getElementById("id").value;
            let txtpart = document.getElementById('com_txtpart') ? document.getElementById('com_txtpart').value : '';
            let txtcons = document.getElementById('com_txtcons') ? document.getElementById('com_txtcons').value : '';
            let txtconsunit = document.getElementById('com_txtpart') ? document.getElementById('com_txtpart').value : '';
            // New
            let partSource = document.getElementById('com_from_part_id') ? document.getElementById('com_from_part_id').value : '';
            // let cboproses = document.getElementById('cboproses') ? document.getElementById('cboproses').value : '';

            // Loading
            document.getElementById("loading").classList.remove("d-none");

            $.ajax({
                type: "post",
                url: '{{ route('store_part_secondary') }}',
                data: {
                    id: id,
                    txtpart: txtpart,
                    txtcons: txtcons,
                    txtconsunit: txtconsunit,
                    partSource: partSource,
                    is_complement: 1
                },
                success: function(response) {
                    document.getElementById("loading").classList.add("d-none");

                    if (response.icon == 'salah') {
                        iziToast.warning({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    } else {
                        iziToast.success({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    }
                    dataTableReload();
                    cleardata();
                },
                error: function(request, status, error) {
                    document.getElementById("loading").classList.add("d-none");

                    alert(request.responseText);
                },
            });
        };

        function update_data() {
            let id = document.getElementById("id").value;
            let cbotuj = document.getElementById('cbotuj') ? document.getElementById('cbotuj').value : '';
            let txtpart = document.getElementById('txtpart') ? document.getElementById('txtpart').value : '';
            let txtcons = document.getElementById('txtcons') ? document.getElementById('txtcons').value : '';
            let txtconsunit = document.getElementById('txtconsunit') ? document.getElementById('txtconsunit').value : '';
            // New
            let tujuan = document.getElementById('tujuan') ? document.getElementById('tujuan').value : '';
            let urutan = document.getElementById('urutan') ? document.getElementById('urutan').value : '';
            // let cboproses = document.getElementById('cboproses') ? document.getElementById('cboproses').value : '';

            // Loading
            document.getElementById("loading").classList.remove("d-none");

            $.ajax({
                type: "post",
                url: '{{ route('store_part_secondary') }}',
                data: {
                    id: id,
                    cbotuj: cbotuj,
                    txtpart: txtpart,
                    txtcons: txtcons,
                    txtconsunit: txtconsunit,
                    tujuan: tujuan,
                    urutan: urutan
                },
                success: function(response) {
                    document.getElementById("loading").classList.add("d-none");

                    if (response.icon == 'salah') {
                        iziToast.warning({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    } else {
                        iziToast.success({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    }
                    dataTableReload();
                    cleardata();
                },
                error: function(request, status, error) {
                    document.getElementById("loading").classList.add("d-none");

                    alert(request.responseText);
                },
            });
        };

        function dataTableComplementReload() {
            let datatable = $("#datatable_list_part_complement").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                paging: false,
                destroy: true,
                ajax: {
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('datatable_list_part_complement') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: function(d) {
                        d.id = $('#id').val();
                    },
                },
                columns: [
                    {
                        data: 'id',
                    },
                    {
                        data: 'nama_part',
                    },
                    {
                        data: 'cons',
                    },
                    {
                        data: 'unit',
                    },
                    {
                        data: 'tujuan',
                    },
                    {
                        data: 'proses',
                    },
                    {
                        data: 'part_status',
                    },
                ],
                columnDefs: [
                    {
                        targets: [0],
                        className: "text-center",
                        render: (data, type, row, meta) => {
                            let disableDelete = (row.total_stocker > 0 ? 'disabled' : '');
                            return `
                                <button class='btn btn-primary btn-sm' onclick='editData(`+JSON.stringify(row)+`, "editPartSecondaryModal")'>
                                    <i class='fa fa-edit'></i>
                                </button>
                                <button class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route('destroy-part-detail') }}/`+row['id']+`' onclick='deleteData(this)' {{ Auth::user()->roles->whereIn("nama_role", ["admin", "superadmin"])->count() > 0 ? '' : '`+(disableDelete)+`'}}>
                                    <i class='fa fa-trash'></i>
                                </button>
                            `;
                        }
                    },
                    {
                        targets: [6],
                        render: (data, type, row, meta) => {
                            return data ? data.toUpperCase() : '-';
                        }
                    }
                ]
            });
        }

         function simpan_data_complement() {
            let id = document.getElementById("id").value;
            let cbotuj = document.getElementById('cbotuj') ? document.getElementById('cbotuj').value : '';
            let txtpart = document.getElementById('txtpart') ? document.getElementById('txtpart').value : '';
            let txtcons = document.getElementById('txtcons') ? document.getElementById('txtcons').value : '';
            let txtconsunit = document.getElementById('txtconsunit') ? document.getElementById('txtconsunit').value : '';
            // New
            let tujuan = document.getElementById('tujuan') ? document.getElementById('tujuan').value : '';
            let urutan = document.getElementById('urutan') ? document.getElementById('urutan').value : '';
            // let cboproses = document.getElementById('cboproses') ? document.getElementById('cboproses').value : '';

            // Loading
            document.getElementById("loading").classList.remove("d-none");

            $.ajax({
                type: "post",
                url: '{{ route('store_part_secondary') }}',
                data: {
                    id: id,
                    cbotuj: cbotuj,
                    txtpart: txtpart,
                    txtcons: txtcons,
                    txtconsunit: txtconsunit,
                    tujuan: tujuan,
                    urutan: urutan
                },
                success: function(response) {
                    document.getElementById("loading").classList.add("d-none");

                    if (response.icon == 'salah') {
                        iziToast.warning({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    } else {
                        iziToast.success({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    }
                    dataTableReload();
                    cleardata();
                },
                error: function(request, status, error) {
                    document.getElementById("loading").classList.add("d-none");

                    alert(request.responseText);
                },
            });
        };
    </script>

    {{-- FORM --}}
    <script>
        var id = document.getElementById("id").value;
        var ws = document.getElementById("ws").value;
        var panel = document.getElementById("panel").value;

        //Form Part Datatable
        let datatableSelected = $("#datatable-selected").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            ajax: {
                url: '{{ route('manage-part-form') }}/'+id,
                data: function(d) {
                    d.act_costing_ws = $('#ws').val();
                    d.panel = $('#panel').val();
                },
            },
            columns: [
                {
                    data: 'tgl_mulai_form'
                },
                {
                    data: 'no_form'
                },
                {
                    data: 'nama_meja'
                },
                {
                    data: 'no_cut'
                },
                {
                    data: 'style'
                },
                {
                    data: 'color'
                },
                {
                    data: 'panel'
                },
                {
                    data: 'qty_ply'
                },
                {
                    data: 'marker_details',
                    searchable: false
                },
                {
                    data: 'id_marker'
                },
                {
                    data: 'act_costing_ws'
                },
                {
                    data: 'type'
                },
            ],
            columnDefs: [
                {
                    targets: [2],
                    render: (data, type, row, meta) => {
                        if (data) {
                            if (row.type == 'PIECE') {
                                return `
                                    <a href='{{ route('process-cutting-piece') }}/ `+row.id+`' target='_blank'>`+data+`</a>
                                `;
                            }
                        }

                        return data ? data : '-';
                    }
                },
                {
                    targets: [2],
                    render: (data, type, row, meta) => {
                        let color = "";

                        if (row.status == 'SELESAI PENGERJAAN') {
                            color = '#087521';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING DETAIL') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING SPREAD') {
                            color = '#2243d6';
                        }

                        return data ? "<span style='color: " + color + "' >" + data.toUpperCase() + "</span>" : "<span style=' color: " + color + "'>-</span>"
                    }
                },
                {
                    targets: [9],
                    render: (data, type, row, meta) => {
                        if (data) {
                            return `
                                <a href='{{ route('edit-marker') }}/ `+row.marker_id+`' target='_blank'>`+data+`</a>
                            `;
                        }

                        return data ? data : '-';
                    }
                },
                {
                    targets: '_all',
                    className: 'text-nowrap',
                    render: (data, type, row, meta) => {
                        let color = "";

                        if (row.status == 'SELESAI PENGERJAAN') {
                            color = '#087521';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING DETAIL') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING SPREAD') {
                            color = '#2243d6';
                        }

                        return data ? "<span style='color: " + color + "' >" + data + "</span>" : "<span style=' color: " + color + "'>-</span>"
                    }
                }
            ]
        });

        // Datatable row selection
        datatableSelected.on('click', 'tbody tr', function(e) {
            e.currentTarget.classList.toggle('selected');
            document.getElementById('selected-row-count-1').innerText = $('#datatable-selected').DataTable().rows('.selected').data().length;
        });

        $('#datatable-selected thead tr').clone(true).appendTo('#datatable-selected thead');
        $('#datatable-selected thead tr:eq(1) th').each(function(i) {
            if (i == 0 || i == 1 || i == 2 || i == 3 || i == 4 || i == 5 || i == 6 || i == 7 || i == 9 || i == 10) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" />');

                $('input', this).on('keyup change', function() {
                    if (datatableSelected.column(i).search() !== this.value) {
                        datatableSelected
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        function addToPartForm(element) {
            element.setAttribute('disabled', true);

            let selectedForm = $('#datatable-select').DataTable().rows('.selected').data();
            let partForms = [];
            for (let key in selectedForm) {
                if (!isNaN(key)) {
                    partForms.push({
                        form_id: selectedForm[key]['id'],
                        no_form: selectedForm[key]['no_form'],
                        type: selectedForm[key]['type']
                    });
                }
            }

            if (partForms.length > 0) {
                $.ajax({
                    type: "POST",
                    url: '{!! route('store-part-form') !!}',
                    data: {
                        part_id: id,
                        partForms: partForms
                    },
                    success: function(res) {
                        element.removeAttribute('disabled');

                        if (res.status == 200) {
                            iziToast.success({
                                title: 'Success',
                                message: res.message,
                                position: 'topCenter'
                            });
                        } else {
                            iziToast.error({
                                title: 'Error',
                                message: res.message,
                                position: 'topCenter'
                            });
                        }

                        if (res.table != '') {
                            $('#' + res.table).DataTable().ajax.reload(() => {
                                document.getElementById('selected-row-count-2').innerText = $('#' + res.table).DataTable().rows('.selected').data().length;
                            });

                            $('#datatable-select').DataTable().ajax.reload(() => {
                                document.getElementById('selected-row-count-1').innerText = $('#datatable-select').DataTable().rows('.selected').data().length;
                            });
                        }

                        if (res.additional) {
                            let message = "";

                            if (res.additional['success'].length > 0) {
                                res.additional['success'].forEach(element => {
                                    message += element['no_form'] + " - Berhasil <br>";
                                });
                            }

                            if (res.additional['fail'].length > 0) {
                                res.additional['fail'].forEach(element => {
                                    message += element['no_form'] + " - Gagal <br>";
                                });
                            }

                            if (res.additional['exist'].length > 0) {
                                res.additional['exist'].forEach(element => {
                                    message += element['no_form'] + " - Sudah Ada <br>";
                                });
                            }

                            if ((res.additional['success'].length + res.additional['fail'].length + res.additional['exist'].length) > 0) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Hasil Transfer',
                                    html: message,
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                });
                            }
                        }
                    },
                    error: function(jqXHR) {
                        element.removeAttribute('disabled');

                        let res = jqXHR.responseJSON;
                        let message = '';

                        for (let key in res.errors) {
                            message = res.errors[key];
                        }

                        iziToast.error({
                            title: 'Error',
                            message: 'Terjadi kesalahan. ' + message,
                            position: 'topCenter'
                        });
                    }
                })
            } else {
                element.removeAttribute('disabled');

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    html: "Harap pilih form cut yang ingin ditambahkan",
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                });
            }
        }

        //Form Cut Datatable
        let datatableSelect = $("#datatable-select").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            ajax: {
                url: '{{ route('get-part-form-cut') }}/',
                data: function(d) {
                    d.act_costing_ws = $('#ws').val();
                    d.panel = $('#panel').val();
                },
            },
            columns: [
                {
                    data: 'tgl_mulai_form'
                },
                {
                    data: 'no_form'
                },
                {
                    data: 'nama_meja'
                },
                {
                    data: 'no_cut'
                },
                {
                    data: 'style'
                },
                {
                    data: 'color'
                },
                {
                    data: 'panel'
                },
                {
                    data: 'qty_ply',
                },
                {
                    data: 'marker_details',
                    searchable: false
                },
                {
                    data: 'id_marker'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'type'
                },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap"
                },
                {
                    targets: [1],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        if (data) {
                            if (row.type == 'PIECE') {
                                return `
                                    <a href='{{ route('process-cutting-piece') }}/ `+row.id+`' target='_blank'>`+data+`</a>
                                `;
                            }
                        }

                        return data ? data : '-';
                    }
                },
                {
                    targets: [2],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        return data ? data.toUpperCase() : "-";
                    }
                },
                {
                    targets: [9],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        if (data) {
                            return `
                                <a href='{{ route('edit-marker') }}/ `+row.marker_id+`' target='_blank'>`+data+`</a>
                            `;
                        }

                        return data ? data : '-';
                    }
                },
            ]
        });

        // Datatable row selection
        datatableSelect.on('click', 'tbody tr', function(e) {
            e.currentTarget.classList.toggle('selected');
            document.getElementById('selected-row-count-2').innerText = $('#datatable-select').DataTable().rows('.selected').data().length;
        });

        $('#datatable-select thead tr').clone(true).appendTo('#datatable-select thead');
        $('#datatable-select thead tr:eq(1) th').each(function(i) {
            if (i == 0 || i == 1 || i == 2 || i == 3 || i == 4 || i == 5 || i == 6 || i == 7 || i == 9 || i == 10) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" />');

                $('input', this).on('keyup change', function() {
                    if (datatableSelect.column(i).search() !== this.value) {
                        datatableSelect
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        function removePartForm() {
            let tglPlan = $("#tgl_plan").val();
            let selectedForm = $('#datatable-selected').DataTable().rows('.selected').data();
            let partForms = [];
            for (let key in selectedForm) {
                if (!isNaN(key)) {
                    partForms.push({
                        form_id: selectedForm[key]['id'],
                        no_form: selectedForm[key]['no_form'],
                        type: selectedForm[key]['type']
                    });
                }
            }

            if (partForms.length > 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Singkirkan Form yang dipilih?',
                    text: 'Yakin akan menyingkirkan form?',
                    showCancelButton: true,
                    showConfirmButton: true,
                    confirmButtonText: 'Singkirkan',
                    confirmButtonColor: "#d33141",
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "DELETE",
                            url: '{!! route('destroy-part-form') !!}',
                            data: {
                                part_id: id,
                                partForms: partForms
                            },
                            success: function(res) {
                                if (res.status == 200) {
                                    iziToast.success({
                                        title: 'Success',
                                        message: res.message,
                                        position: 'topCenter'
                                    });
                                } else {
                                    iziToast.error({
                                        title: 'Error',
                                        message: res.message,
                                        position: 'topCenter'
                                    });
                                }

                                if (res.table != '') {
                                    $('#' + res.table).DataTable().ajax.reload(() => {document.getElementById('selected-row-count-2').innerText = $('#' + res.table).DataTable().rows('.selected').data().length;
                                    });

                                    $('#datatable-select').DataTable().ajax.reload(() => {
                                        document.getElementById('selected-row-count-1').innerText = $('#datatable-select').DataTable().rows('.selected').data().length;
                                    });
                                }

                                if (res.additional) {
                                    let message = "";

                                    if (res.additional['success'].length > 0) {
                                        res.additional['success'].forEach(element => {
                                            message += element['no_form'] +
                                                " - Berhasil <br>";
                                        });
                                    }

                                    if (res.additional['fail'].length > 0) {
                                        res.additional['fail'].forEach(element => {
                                            message += element['no_form'] + " - Gagal <br>";
                                        });
                                    }

                                    if (res.additional['success'].length + res.additional['fail'].length > 1) {
                                        Swal.fire({
                                            icon: 'info',
                                            title: 'Form berhasil disingkirkan',
                                            html: message,
                                            showCancelButton: false,
                                            showConfirmButton: true,
                                            confirmButtonText: 'Oke',
                                        });
                                    }
                                }
                            },
                            error: function(jqXHR) {
                                let res = jqXHR.responseJSON;
                                let message = '';

                                for (let key in res.errors) {
                                    message = res.errors[key];
                                }

                                iziToast.error({
                                    title: 'Error',
                                    message: 'Terjadi kesalahan. ' + message,
                                    position: 'topCenter'
                                });
                            }
                        })
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    html: "Harap pilih form cut yang akan disingkirkan",
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                });
            }
        }

        function orderSecondary(element, prefix = '') {
            console.log(element, prefix, document.getElementById(`${prefix}urutan_show`), `${prefix}urutan_show`);
            const orderShow = document.getElementById(`${prefix}urutan_show`);
            const order = document.getElementById(`${prefix}urutan`);

            if (orderShow && order) {
                // remove the default placeholder if it exists
                const placeholder = orderShow.querySelector('li');
                if (placeholder && placeholder.textContent.trim() === '-') {
                    placeholder.remove();
                }

                let currentValues = order.value ? order.value.split(',') : [];

                const selectedValues = Array.from(element.selectedOptions).map(opt => opt.value);
                const selectedTexts = Array.from(element.selectedOptions).map(opt => opt.textContent);

                const newlySelected = selectedValues.filter(v => !currentValues.includes(v));
                const deselected = currentValues.filter(v => !selectedValues.includes(v));

                // Add new selections
                newlySelected.forEach(value => {
                    const optionText = selectedTexts[selectedValues.indexOf(value)];
                    const li = document.createElement('li');
                    li.className = 'list-group-item';
                    li.dataset.value = value;
                    li.textContent = optionText;
                    li.draggable = true;
                    orderShow.appendChild(li);
                    currentValues.push(value);
                });

                // Remove deselected items
                deselected.forEach(value => {
                    currentValues = currentValues.filter(v => v !== value);
                    const liToRemove = orderShow.querySelector(`li[data-value="${value}"]`);
                    if (liToRemove) liToRemove.remove();
                });

                order.value = currentValues.join(',');
                console.log(`Select ${prefix} current values:`, currentValues);
            }
        }

        function orderNonSecondary(element, prefix='') {
            const orderShow = document.getElementById(`${prefix}urutan_show`);
            const order = document.getElementById(`${prefix}urutan`);

            if (order && orderShow) {
                orderShow.innerHTML = '';

                const selectedValue = element.value;

                // Add to list
                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.dataset.value = selectedValue;
                li.textContent = selectedValue;
                li.draggable = true;
                orderShow.appendChild(li);

                order.value = selectedValue;
                console.log(`Select current values:`, selectedValue);
            }
        }

        function clearSelectOptions(button, prefix = '') {
            // get select, list, hidden input based on index
            const select = document.getElementById(`${prefix}secondaries`);
            const list = document.getElementById(`${prefix}urutan_show`);
            const hidden = document.getElementById(`${prefix}urutan`);

            if (!select || !list || !hidden) return;

            // Deselect all options
            Array.from(select.options).forEach(option => option.selected = false);

            // Clear the list
            list.innerHTML = '';

            // Reset hidden input
            hidden.value = '';

            // Restore placeholder
            const li = document.createElement('li');
            li.className = 'list-group-item';
            li.textContent = '-';
            list.appendChild(li);

            $(`#${prefix}secondaries`).val(null).trigger("change");

            console.log(`Cleared select and list for ${prefix} index`);
        }

        function switchTujuan(element, prefix='') {
            let nonSecondaryContainer = document.getElementById(prefix+"non_secondary_container");
            let secondaryContainer = document.getElementById(prefix+"secondary_container");
            let urutanContainer = document.getElementById(prefix+"urutan_container");
            let prosesElement = null;

            if (nonSecondaryContainer && secondaryContainer && urutanContainer) {
                if (element.value == "NON SECONDARY") {
                    nonSecondaryContainer.classList.remove("d-none");
                    secondaryContainer.classList.add("d-none");
                    urutanContainer.classList.add("d-none");

                    // Clear Non Secondary
                    prosesElement = document.getElementById(prefix+"proses");
                    orderNonSecondary(prosesElement);
                } else if (element.value == "SECONDARY") {
                    nonSecondaryContainer.classList.add("d-none");
                    secondaryContainer.classList.remove("d-none");
                    urutanContainer.classList.remove("d-none");

                    // Clear Secondary
                    prosesElement = document.getElementById(prefix+"secondaries");
                    clearSelectOptions(prefix);
                    orderSecondary(prosesElement, prefix);
                } else {
                    nonSecondaryContainer.classList.remove("d-none");
                    secondaryContainer.classList.remove("d-none");
                    urutanContainer.classList.remove("d-none");

                    nonSecondaryContainer.classList.add("d-none");
                    secondaryContainer.classList.add("d-none");
                    urutanContainer.classList.add("d-none");

                    clearSelectOptions(prefix);
                }
            }
        }

        function updateComplementPanelPartList() {
            document.getElementById("loading").classList.remove("d-none");

            document.getElementById('com_from_part_id').innerHTML = null;
            return $.ajax({
                url: '{{ route("get-part-complement-panel-parts") }}',
                type: 'get',
                data: {
                    part_id: $('#com_from_panel_id').val(),
                },
                success: function (res) {
                    document.getElementById("loading").classList.add("d-none");

                    if (res) {
                        // Update this step
                        let complementPanelParts = document.getElementById('com_from_part_id');

                        complementPanelParts.innerHTML = res;

                        complementPanelParts.disabled = false;
                    }
                },
                error: function (jqXHR) {
                    console.error(jqXHR);

                    document.getElementById("loading").classList.add("d-none");
                }
            });
        }
    </script>
@endsection
