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
            <h5 class="card-title fw-bold mb-0">Secondary In <i class="fas fa-sign-in-alt"></i></h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Awal</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" value="{{ date('Y-m-d') }}" onchange="datatableReload(); updateFilterSec(); updateFilterDetailSec();">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Akhir</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}" onchange="datatableReload(); updateFilterSec(); updateFilterDetailSec();">
                </div>
                <div class="mb-3">
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="reset();"><i class="fas fa-plus"></i> Baru</button>
                </div>
                <div class="mb-3">
                    <button class="btn btn-sb btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" onclick="reset();"><i class="fas fa-edit"></i> Ubah</button>
                </div>
            </div>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <button class="btn btn-info btn-sm" onclick="list();" id="list" name="list"><i class="fas fa-list"></i> List</button>
                </div>
                <div class="mb-3">
                    <button class="btn btn-secondary btn-sm" onclick="detail();" id="detail" name="detail"><i class="fas fa-list"></i>
                        Detail
                    </button>
                </div>
            </div>
            <h5 class="card-title fw-bold mb-0" id="judul" name="judul">List Transaksi Secondary In</h5>
            <br>
            <br>
            <div class="table-responsive" id="show_datatable_input">
                <div class="d-flex justify-content-end mb-3 gap-1">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#filterSecModal"><i class="fa fa-filter"></i></button>
                    <button class="btn btn-sm btn-success" onclick="exportExcel('list')"><i class="fa fa-file-excel"></i> Export</button>
                </div>
                <table id="datatable-input" class="table table-bordered table-striped table w-100 text-nowrap">
                    <thead>
                        <tr>
                            <th>Tgl Transaksi</th>
                            <th>ID QR</th>
                            <th>Stock</th>
                            <th>WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Part</th>
                            <th>Size</th>
                            <th>No. Cut</th>
                            <th>Tujuan Awal</th>
                            <th>Lokasi Awal</th>
                            <th>Lokasi Rak</th>
                            <th>Qty Awal</th>
                            <th>Qty Reject</th>
                            <th>Qty Replace</th>
                            <th>Qty In</th>
                            <th>Buyer</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="12"></th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly id='total_qty_awal'> </th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly id='total_qty_reject'> </th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly id='total_qty_replace'> </th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly id='total_qty_in'> </th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="table-responsive" id = "show_datatable_detail">
                <div class="d-flex justify-content-end mb-3 gap-1">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#filterDetailSecModal"><i class="fa fa-filter"></i></button>
                    <button class="btn btn-sm btn-success" onclick="exportExcel('detail')"><i class="fa fa-file-excel"></i> Export</button>
                </div>
                <table id="datatable-detail" class="table table-bordered table-striped table w-100">
                    <thead>
                        <tr>
                            <th>WS</th>
                            <th>Buyer</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>In</th>
                            <th>Reject</th>
                            <th>Replace</th>
                            <th>Out</th>
                            <th>Balance</th>
                            <th>Proses</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="4"></th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly id = 'total_qty_int'></th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly id = 'total_qty_reject_det'></th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly id = 'total_qty_replace_det'></th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly id = 'total_qty_out'></th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly id = 'total_qty_balance'></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterSecModal" tabindex="-1" aria-labelledby="filterSecModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="filterSecModalLabel"><i class="fa fa-filter"></i> Filter</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Stock</label>
                                <select class="form-select select2bs4filtersec" name="sec_filter_tipe[]" id="sec_filter_tipe" multiple="multiple">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Buyer</label>
                                <select class="form-select select2bs4filtersec" name="sec_filter_buyer[]" id="sec_filter_buyer" multiple="multiple">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Worksheet</label>
                                <select class="form-select select2bs4filtersec" name="sec_filter_ws[]" id="sec_filter_ws" multiple="multiple">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Style</label>
                                <select class="form-select select2bs4filtersec" name="sec_filter_style[]" id="sec_filter_style" multiple="multiple">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Color</label>
                                <select class="form-select select2bs4filtersec" name="sec_filter_color[]" id="sec_filter_color" multiple="multiple">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Part</label>
                                <select class="form-select select2bs4filtersec" name="sec_filter_part[]" id="sec_filter_part" multiple="multiple">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Size</label>
                                <select class="form-select select2bs4filtersec" name="sec_filter_size[]" id="sec_filter_size" multiple="multiple">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">No. Cut</label>
                                <select class="form-select select2bs4filtersec" name="sec_filter_no_cut[]" id="sec_filter_no_cut" multiple="multiple">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tujuan Awal</label>
                                <select class="form-select select2bs4filtersec" name="sec_filter_tujuan[]" id="sec_filter_tujuan" multiple="multiple">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Lokasi Awal</label>
                                <select class="form-select select2bs4filtersec" name="sec_filter_lokasi[]" id="sec_filter_lokasi" multiple="multiple">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Lokasi Rak</label>
                                <select class="form-select select2bs4filtersec" name="sec_filter_lokasi_rak[]" id="sec_filter_lokasi_rak" multiple="multiple">
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sb" onclick="datatableReload()" data-bs-dismiss="modal">Simpan <i class="fa fa-check"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterDetailSecModal" tabindex="-1" aria-labelledby="filterDetailSecModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="filterDetailSecModalLabel"><i class="fa fa-filter"></i> Filter</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Buyer</label>
                                <select class="form-select select2bs4filterdetailsec" name="detail_sec_filter_buyer[]" id="detail_sec_filter_buyer" multiple="multiple">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Worksheet</label>
                                <select class="form-select select2bs4filterdetailsec" name="detail_sec_filter_ws[]" id="detail_sec_filter_ws" multiple="multiple">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Style</label>
                                <select class="form-select select2bs4filterdetailsec" name="detail_sec_filter_style[]" id="detail_sec_filter_style" multiple="multiple">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Color</label>
                                <select class="form-select select2bs4filterdetailsec" name="detail_sec_filter_color[]" id="detail_sec_filter_color" multiple="multiple">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Proses</label>
                                <select class="form-select select2bs4filterdetailsec" name="detail_sec_filter_lokasi[]" id="detail_sec_filter_lokasi" multiple="multiple">
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sb" onclick="datatableReload()" data-bs-dismiss="modal">Simpan <i class="fa fa-check"></i></button>
                </div>
            </div>
        </div>
    </div>

    {{-- Baru Modal --}}
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <form action="{{ route('store-secondary-in') }}" method="post" onsubmit="submitForm(this, event)" name='form' id='form'>
            @method('POST')
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5">Scan QR Secondary In</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row ">
                            <div class="col-sm-12">
                                <div class="mb-3">
                                    <label class="form-label label-input">Scan QR Stocker</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm border-input" name="txtqrstocker" id="txtqrstocker" autocomplete="off" enterkeyhint="go" autofocus>
                                        {{-- <input type="button" class="btn btn-sm btn-primary" value="Scan Line" /> --}}
                                        {{-- style="display: none;" --}}
                                        <button class="btn btn-sm btn-primary" type="button" id="scanqr" onclick="scan_qr()">Scan</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div></div>
                            </div>
                            <div class="col-6">
                                <div id="reader"></div>
                            </div>
                            <div class="col-3">
                            </div>
                        </div>

                        <div class="row">
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>No Stocker</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtno_stocker' name='txtno_stocker' value='' readonly>
                                    <input type='hidden' class='form-control form-control-sm' id='txtno_form' name='txtno_form' value='' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>WS</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtws' name='txtws' value='' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Buyer</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtbuyer' name='txtbuyer' value='' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>No Cut</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtno_cut' name='txtno_cut' value='' readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Style</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtstyle' name='txtstyle' value='' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Color</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtcolor' name='txtcolor' value='' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Size</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtsize' name='txtsize' value='' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Part</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtpart' name='txtpart' value='' readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class='col-sm-6'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Tujuan Asal</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txttujuan' name='txttujuan' value='' readonly>
                                </div>
                            </div>
                            <div class='col-sm-6'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Lokasi Asal</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtalokasi' name='txtalokasi' value='' readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Qty Awal</small></label>
                                    <input type='number' class='form-control form-control-sm' id='txtqtyawal' name='txtqtyawal' value='' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Reject</small></label>
                                    <input type='number' class='form-control form-control-sm' id='txtqtyreject' name='txtqtyreject' value='' oninput='sum();'
                                        style = 'border-color:blue;'>
                                </div>
                            </div>

                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Replacement</small></label>
                                    <input type='number' class='form-control form-control-sm' id='txtqtyreplace' name='txtqtyreplace' value = '0' oninput='sum();'
                                        style = 'border-color:blue;'>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Qty In</small></label>
                                    <input type='number' class='form-control form-control-sm' id='txtqtyin' name='txtqtyin' value='' readonly style = 'border-color:green;'>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class='col-md-6' id="rak-input">
                                <div class='form-group'>
                                    <label class='form-label'><small>Rak</small></label>
                                    <select class="form-control select2bs4" name="cborak" id="cborak" style="width: 100%;">
                                        <option selected="selected" value="">Pilih Rak Tujuan</option>
                                        @foreach ($data_rak as $datarak)
                                            <option value="{{ $datarak->isi }}">
                                                {{ $datarak->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class='col-md-6' id="trolley-input">
                                <div class='form-group'>
                                    <label class='form-label'><small>Trolley</small></label>
                                    <select class="form-control select2bs4" name="cbotrolley" id="cbotrolley" style="width: 100%;">
                                        <option selected="selected" value="">Pilih Trolley Tujuan</option>
                                        @foreach ($data_trolley as $datatrolley)
                                            <option value="{{ $datatrolley->isi }}">
                                                {{ $datatrolley->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class='col-md-6'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Keterangan</small></label>
                                    <input type='text' class='form-control' id='txtket' name='txtket' value='' style = 'border-color:blue;' autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Simpan </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
        aria-hidden="true">
        <form action="{{ route('update-secondary-in') }}" method="post" onsubmit="submitForm(this, event)" name='editform' id='editform'>
            @method('POST')
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5">Update Secondary In</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row ">
                            <div class="col-sm-12">
                                <div class="mb-3">
                                    <label class="form-label label-input">Scan QR Stocker</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm border-input" name="editqrstocker" id="editqrstocker" autocomplete="off" enterkeyhint="go" autofocus>
                                        {{-- <input type="button" class="btn btn-sm btn-primary" value="Scan Line" /> --}}
                                        {{-- style="display: none;" --}}
                                        <button class="btn btn-sm btn-primary" type="button" id="editscanqr" onclick="edit_scan_qr()">Scan</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div></div>
                            </div>
                            <div class="col-6">
                                <div id="edit-reader"></div>
                            </div>
                            <div class="col-3">
                            </div>
                        </div>

                        <div class="row">
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>No Stocker</small></label>
                                    <input type='text' class='form-control form-control-sm' id='edit_no_stocker' name='edit_no_stocker' value='' readonly>
                                    <input type='hidden' class='form-control form-control-sm' id='edit_no_form' name='edit_no_form' value='' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>WS</small></label>
                                    <input type='text' class='form-control form-control-sm' id='edit_ws' name='edit_ws' value='' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Buyer</small></label>
                                    <input type='text' class='form-control form-control-sm' id='edit_buyer' name='edit_buyer' value='' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>No Cut</small></label>
                                    <input type='text' class='form-control form-control-sm' id='edit_no_cut' name='edit_no_cut' value='' readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Style</small></label>
                                    <input type='text' class='form-control form-control-sm' id='edit_style' name='edit_style' value='' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Color</small></label>
                                    <input type='text' class='form-control form-control-sm' id='edit_color' name='edit_color' value='' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Size</small></label>
                                    <input type='text' class='form-control form-control-sm' id='edit_size' name='edit_size' value='' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Part</small></label>
                                    <input type='text' class='form-control form-control-sm' id='edit_part' name='edit_part' value='' readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class='col-sm-6'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Tujuan Asal</small></label>
                                    <input type='text' class='form-control form-control-sm' id='edit_tujuan' name='edit_tujuan' value='' readonly>
                                </div>
                            </div>
                            <div class='col-sm-6'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Lokasi Asal</small></label>
                                    <input type='text' class='form-control form-control-sm' id='edit_alokasi' name='edit_alokasi' value='' readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Qty Awal</small></label>
                                    <input type='number' class='form-control form-control-sm' id='edit_qtyawal' name='edit_qtyawal' value='' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Reject</small></label>
                                    <input type='number' class='form-control form-control-sm' id='edit_qtyreject' name='edit_qtyreject' value='' oninput='editsum();'
                                        style = 'border-color:blue;'>
                                </div>
                            </div>

                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Replacement</small></label>
                                    <input type='number' class='form-control form-control-sm' id='edit_qtyreplace' name='edit_qtyreplace' value = '0' oninput='editsum();'
                                        style = 'border-color:blue;'>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Qty In</small></label>
                                    <input type='number' class='form-control form-control-sm' id='edit_qtyin' name='edit_qtyin' value='' readonly style = 'border-color:green;'>
                                </div>
                            </div>

                        </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Simpan </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2bs4filtersec').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#filterSecModal")
        });

        $('.select2bs4filterdetailsec').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#filterDetailSecModal")
        });

        $('#datatable-input thead tr').clone(true).appendTo('#datatable-input thead');
        $('#datatable-input thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();

            if (i == 7) {
                $(this).html('<select class="form-select" id="size_filter" multiple="multiple" style="min-width: 90px;"></select>');
            } else {
                $(this).html('<input type="text" class="form-control form-control-sm"/>');

                $('input', this).on('keyup change', function() {
                    if (datatable.column(i).search() !== this.value) {
                        datatable
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            }
        })

        let datatable = $("#datatable-input").DataTable({
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api(),
                    data;

                // converting to interger to find total
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };

                // computing column Total of the complete result
                var sumTotal = api
                    .column(12)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalAwal = api
                    .column(12)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalReject = api
                    .column(13)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalReplace = api
                    .column(14)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalIn = api
                    .column(15)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(12).footer()).html(sumTotalAwal);
                $(api.column(13).footer()).html(sumTotalReject);
                $(api.column(14).footer()).html(sumTotalReplace);
                $(api.column(15).footer()).html(sumTotalIn);

                $('#size_filter').select2({
                    theme: 'bootstrap4',
                });
            },
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('secondary-in') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                    d.sec_filter_tipe = $('#sec_filter_tipe').val();
                    d.sec_filter_buyer = $('#sec_filter_buyer').val();
                    d.sec_filter_ws = $('#sec_filter_ws').val();
                    d.sec_filter_style = $('#sec_filter_style').val();
                    d.sec_filter_color = $('#sec_filter_color').val();
                    d.sec_filter_part = $('#sec_filter_part').val();
                    d.sec_filter_size = $('#sec_filter_size').val();
                    d.sec_filter_no_cut = $('#sec_filter_no_cut').val();
                    d.sec_filter_tujuan = $('#sec_filter_tujuan').val();
                    d.sec_filter_lokasi = $('#sec_filter_lokasi').val();
                    d.sec_filter_lokasi_rak = $('#sec_filter_lokasi_rak').val();
                    d.size_filter = $('#size_filter').val();
                },
            },
            columns: [
                {
                    data: 'tgl_trans_fix',
                },
                {
                    data: 'id_qr_stocker',
                },
                {
                    data: 'tipe',
                },
                {
                    data: 'act_costing_ws',
                },
                {
                    data: 'style',
                },
                {
                    data: 'color',
                },
                {
                    data: 'nama_part',
                },
                {
                    data: 'size',
                },
                {
                    data: 'no_cut',
                },
                {
                    data: 'tujuan',
                },
                {
                    data: 'lokasi',
                },
                {
                    data: 'lokasi_rak',
                },
                {
                    data: 'qty_awal',
                },
                {
                    data: 'qty_reject',
                },
                {
                    data: 'qty_replace',
                },
                {
                    data: 'qty_in',
                },
                {
                    data: 'buyer',
                },
                {
                    data: 'user',
                },
            ],
            columnDefs: [{
                    targets: "_all",
                    className: "text-nowrap"
                }
                // {
                // targets: [10],
                // render: (data, type, row, meta) => {
                //     return `<div class='d-flex gap-1 justify-content-center'> <a class='btn btn-warning btn-sm' href='{{ route('create-dc-in') }}/` +
                //         row.no_form +
                //         `' data-bs-toggle='tooltip'><i class='fas fa-qrcode'></i></a> </div>`;
                // }
                // }
            ]
        });

        $('#datatable-detail thead tr').clone(true).appendTo('#datatable-detail thead');
        $('#datatable-detail thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm"/>');

            $('input', this).on('keyup change', function() {
                if (datatable_detail.column(i).search() !== this.value) {
                    datatable_detail
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        let datatable_detail = $("#datatable-detail").DataTable({
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api(),
                    data;

                // converting to interger to find total
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };

                var sumTotalIn = api
                    .column(4)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalReject = api
                    .column(5)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalReplace = api
                    .column(6)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalOut = api
                    .column(7)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalBalance = api
                    .column(8)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(4).footer()).html(sumTotalIn);
                $(api.column(5).footer()).html(sumTotalReject);
                $(api.column(6).footer()).html(sumTotalReplace);
                $(api.column(7).footer()).html(sumTotalOut);
                $(api.column(8).footer()).html(sumTotalBalance);
            },
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            destroy: true,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('detail_stocker_in') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                    d.detail_sec_filter_buyer = $('#detail_sec_filter_buyer').val();
                    d.detail_sec_filter_ws = $('#detail_sec_filter_ws').val();
                    d.detail_sec_filter_style = $('#detail_sec_filter_style').val();
                    d.detail_sec_filter_color = $('#detail_sec_filter_color').val();
                    d.detail_sec_filter_lokasi = $('#detail_sec_filter_lokasi').val();
                },
            },
            columns: [
                {
                    data: 'act_costing_ws',
                },
                {
                    data: 'buyer',
                },
                {
                    data: 'styleno',
                },
                {
                    data: 'color',
                },
                {
                    data: 'qty_in',
                },
                {
                    data: 'qty_reject',
                },
                {
                    data: 'qty_replace',
                },
                {
                    data: 'qty_out',
                },
                {
                    data: 'balance',
                },
                {
                    data: 'lokasi',
                },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap"
                },
                {
                    targets: [8],
                    visible: false
                }
            ]
        });

        function datatableReload() {
            $('#datatable-input').DataTable().ajax.reload();
            $('#datatable-detail').DataTable().ajax.reload();
        }
    </script>

    {{-- Baru Scan --}}
    <script>
        // $('.select2bs4').select2({
        //     theme: 'bootstrap4',
        //     dropdownParent: $("#editMejaModal")
        // })

        // Scan QR Module :
        // Variable List :
        var html5QrcodeScanner = null;

        // Function List :
        // -Initialize Scanner-
        async function initScan() {
            if (document.getElementById("reader")) {
                if (html5QrcodeScanner) {
                    await html5QrcodeScanner.clear();
                }

                function onScanSuccess(decodedText, decodedResult) {
                    // handle the scanned code as you like, for example:
                    console.log(`Code matched = ${decodedText}`, decodedResult);

                    // store to input text
                    // let breakDecodedText = decodedText.split('-');

                    document.getElementById('txtqrstocker').value = decodedText;


                    scan_qr();

                    html5QrcodeScanner.clear();

                }

                function onScanFailure(error) {
                    // handle scan failure, usually better to ignore and keep scanning.
                    // for example:
                    console.warn(`Code scan error = ${error}`);
                }

                html5QrcodeScanner = new Html5QrcodeScanner(
                    "reader", {
                        fps: 10,
                        qrbox: {
                            width: 200,
                            height: 200
                        }
                    },
                    /* verbose= */
                    false);


                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            }
        }
    </script>

    {{-- Ubah Scan --}}
    <script>
        // $('.select2bs4').select2({
        //     theme: 'bootstrap4',
        //     dropdownParent: $("#editMejaModal")
        // })

        // Scan QR Module :
        // Variable List :
        var edithtml5QrcodeScanner = null;

        // Function List :
        // -Initialize Scanner-
        async function initScanEdit() {
            if (document.getElementById("edit-reader")) {
                if (edithtml5QrcodeScanner) {
                    await edithtml5QrcodeScanner.clear();
                }

                function onScanSuccess(decodedText, decodedResult) {
                    // handle the scanned code as you like, for example:
                    console.log(`Code matched = ${decodedText}`, decodedResult);

                    // store to input text
                    // let breakDecodedText = decodedText.split('-');

                    document.getElementById('editqrstocker').value = decodedText;

                    scan_qr();

                    edithtml5QrcodeScanner.clear();

                }

                function onScanFailure(error) {
                    // handle scan failure, usually better to ignore and keep scanning.
                    // for example:
                    console.warn(`Code scan error = ${error}`);
                }

                edithtml5QrcodeScanner = new Html5QrcodeScanner(
                    "edit-reader", {
                        fps: 10,
                        qrbox: {
                            width: 200,
                            height: 200
                        }
                    },
                    /* verbose= */
                    false);


                edithtml5QrcodeScanner.render(onScanSuccess, onScanFailure);
            }
        }
    </script>

    <script>
        $(document).ready(function() {
            reset();
            list();
            updateFilterSec();
            updateFilterDetailSec();
        })

        // Baru modal
        $('#exampleModal').on('show.bs.modal', function(e) {
            initScan();
            // $(document).on('select2:open', () => {
            //     document.querySelector('.select2-search__field').focus();
            // });
            // $('.select2').select2()
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                dropdownParent: $("#exampleModal")
            })
            $('#cbotuj').val('').trigger('change');
            $('#rak-input').removeClass('d-none');
            $('#trolley-input').removeClass('d-none');
        })

        $('#exampleModal').on('shown.bs.modal', function(e) {
            $('#txtqrstocker').focus();
        })

        // Edit modal
        $('#editModal').on('show.bs.modal', function(e) {
            initScanEdit();
        })

        $('#editModal').on('shown.bs.modal', function(e) {
            $('#editqrstocker').focus();
        })
    </script>
    <script>
        function reset() {
            $("#form").trigger("reset");
            $("#editform").trigger("reset");
            // initScan();
        }

        function scan_qr() {
            let txtqrstocker = document.form.txtqrstocker.value;
            let html = $.ajax({
                type: "get",
                url: '{{ route('cek_data_stocker_in') }}',
                data: {
                    txtqrstocker: txtqrstocker
                },
                dataType: 'json',
                success: function(response) {
                    if (response[0] && response[0].status && response[0].status == 400) {
                        return Swal.fire({
                            icon: "error",
                            title: "Gagal",
                            html: response[0].message
                        });
                    }

                    document.getElementById('txtno_stocker').value = response.id_qr_stocker;
                    document.getElementById('txtws').value = response.act_costing_ws;
                    document.getElementById('txtbuyer').value = response.buyer;
                    document.getElementById('txtno_cut').value = response.no_cut;
                    document.getElementById('txtstyle').value = response.style;
                    document.getElementById('txtcolor').value = response.color;
                    document.getElementById('txtsize').value = response.size;
                    document.getElementById('txtpart').value = response.nama_part;
                    document.getElementById('txttujuan').value = response.tujuan;
                    document.getElementById('txtalokasi').value = response.lokasi;
                    document.getElementById('txtqtyawal').value =  response.qty_awal;

                    console.log(response.tempat_tujuan);

                    if (response.tempat_tujuan == "RAK") {
                        $("#rak-input").removeClass("d-none");
                        $("#trolley-input").addClass("d-none");
                        $("#cborak").val(response.lokasi_tujuan).trigger('change');
                    } else {
                        $("#trolley-input").removeClass("d-none");
                        $("#rak-input").addClass("d-none");
                        $("#cbotrolley").val(response.lokasi_tujuan).trigger('change');
                    }
                    // let txtqtyreject = $("#txtqtyreject").val();
                    // let txtqtyreplace = $("#txtqtyreplace").val();
                    // let txtqtyin = $("#txtqtyin").val();
                    // let cborak = $("#cborak").val();
                    // let ket = $("#txtket").val();
                    // $.ajax({
                    //     type: "post",
                    //     url: '{{-- route('store-mut-karyawan') --}}',
                    //     data: {
                    //         txtenroll_id: txtenroll_id,
                    //         nm_line: nm_line,
                    //         nik: nik,
                    //         nm_karyawan: nm_karyawan
                    //     },
                    //     success: async function(res) {
                    //         await Swal.fire({
                    //             icon: res.icon,
                    //             title: res.msg,
                    //             html: "NIK : " + response.nik + "<br/>" +
                    //                 "Nama :" + response.employee_name,
                    //             // html: "NIK :" + $("#txtnik").val(),
                    //             showCancelButton: false,
                    //             showConfirmButton: true,
                    //             timer: res.timer,
                    //             timerProgressBar: res.prog
                    //         })
                    //         document.getElementById('txtenroll_id').focus();
                    //         datatable.ajax.reload();
                    //         gettotal();
                    //         $("#nik").val('');
                    //         $("#nm_karyawan").val('');
                    //         $("#txtenroll_id").val('');
                    //         initScan1();
                    //     }
                    // });

                },
                error: function(request, status, error) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Tidak Ada',
                        showConfirmButton: true,
                    })
                },
            });
        };

        function edit_scan_qr() {
            let txtqrstocker = document.editform.editqrstocker.value;
            let html = $.ajax({
                type: "get",
                url: '{{ route('cek_data_stocker_in_edit') }}',
                data: {
                    txtqrstocker: txtqrstocker
                },
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                    document.getElementById('edit_no_stocker').value = response.id_qr_stocker;
                    document.getElementById('edit_ws').value = response.act_costing_ws;
                    document.getElementById('edit_buyer').value = response.buyer;
                    document.getElementById('edit_no_cut').value = response.no_cut;
                    document.getElementById('edit_style').value = response.style;
                    document.getElementById('edit_color').value = response.color;
                    document.getElementById('edit_size').value = response.size;
                    document.getElementById('edit_part').value = response.nama_part;
                    document.getElementById('edit_tujuan').value = response.tujuan;
                    document.getElementById('edit_alokasi').value = response.lokasi;
                    document.getElementById('edit_qtyawal').value = response.qty_awal;

                    console.log(response);
                    $("#edit_qtyreject").val(response.qty_reject);
                    $("#edit_qtyreplace").val(response.qty_replace);
                    $("#edit_qtyin").val(response.qty_in);
                },
                error: function(request, status, error) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Tidak Ada',
                        showConfirmButton: true,
                    })
                },
            });
        };

        function sum() {
            let txtqty = document.getElementById('txtqtyawal').value;
            let txtqtyreject = document.getElementById('txtqtyreject').value;
            let txtqtyreplace = document.getElementById('txtqtyreplace').value;
            document.getElementById("txtqtyin").value = +txtqty;
            let result = parseFloat(txtqty) - parseFloat(txtqtyreject) + parseFloat(txtqtyreplace);
            let result_fix = Math.ceil(result)
            if (!isNaN(result_fix)) {
                document.getElementById("txtqtyin").value = result_fix;
            }
        }

        function editsum() {
            let txtqty = document.getElementById('edit_qtyawal').value;
            let txtqtyreject = document.getElementById('edit_qtyreject').value;
            let txtqtyreplace = document.getElementById('edit_qtyreplace').value;
            document.getElementById("edit_qtyin").value = +txtqty;
            let result = parseFloat(txtqty) - parseFloat(txtqtyreject) + parseFloat(txtqtyreplace);
            let result_fix = Math.ceil(result)
            if (!isNaN(result_fix)) {
                document.getElementById("edit_qtyin").value = result_fix;
            }
        }

        function list() {
            document.getElementById("judul").textContent = "List Transaksi In";
            document.getElementById("show_datatable_input").style.display = 'block';
            document.getElementById("show_datatable_detail").style.display = 'none';
        }

        function detail() {
            document.getElementById("judul").textContent = "Detail Transaksi In";
            document.getElementById("show_datatable_input").style.display = 'none';
            document.getElementById("show_datatable_detail").style.display = 'block';
            $('#datatable-detail').DataTable().ajax.reload();
        }

        async function exportExcel(type) {
            Swal.fire({
                title: "Exporting",
                html: "Please Wait...",
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            if (type == 'list') {

                await $.ajax({
                    url: "{{ route("secondary-in-export-excel") }}",
                    type: "get",
                    data: {
                        from : $("#tgl-awal").val(),
                        to : $("#tgl-akhir").val(),
                    },
                    xhrFields: { responseType : 'blob' },
                    success: function (res) {
                        Swal.close();

                        iziToast.success({
                            title: 'Success',
                            message: 'Success',
                            position: 'topCenter'
                        });

                        var blob = new Blob([res]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Secondary IN List "+$("#tgl-awal").val()+" - "+$("#tgl-akhir").val()+".xlsx";
                        link.click();
                    }
                });
            } else if (type == 'detail') {

                await $.ajax({
                    url: "{{ route("secondary-in-detail-export-excel") }}",
                    type: "get",
                    data: {
                        from : $("#tgl-awal").val(),
                        to : $("#tgl-akhir").val(),
                    },
                    xhrFields: { responseType : 'blob' },
                    success: function (res) {
                        Swal.close();

                        iziToast.success({
                            title: 'Success',
                            message: 'Success',
                            position: 'topCenter'
                        });

                        var blob = new Blob([res]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Secondary IN Detail List "+$("#tgl-awal").val()+" - "+$("#tgl-akhir").val()+".xlsx";
                        link.click();
                    }
                });
            }

            Swal.close()
        }

        async function updateFilterSec() {
            document.getElementById('loading').classList.remove('d-none');

            $.ajax({
                url: '{{ route('filter-sec-in') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: {
                    dateFrom : $('#tgl-awal').val(),
                    dateTo : $('#tgl-akhir').val(),
                },
                success: function(response) {
                    document.getElementById('loading').classList.add('d-none');

                    if (response) {
                        if (response.tipe && response.tipe.length > 0) {
                            let tipe = response.tipe;
                            $("#sec_filter_tipe").empty();
                            $.each(tipe, function(index, value) {
                                $('#sec_filter_tipe').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }

                        if (response.buyer && response.buyer.length > 0) {
                            let buyer = response.buyer;
                            $("#sec_filter_buyer").empty();
                            $.each(buyer, function(index, value) {
                                $('#sec_filter_buyer').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }

                        if (response.ws && response.ws.length > 0) {
                            let ws = response.ws;
                            $("#sec_filter_ws").empty();
                            $.each(ws, function(index, value) {
                                $('#sec_filter_ws').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }

                        if (response.style && response.style.length > 0) {
                            let style = response.style;
                            $("#sec_filter_style").empty();
                            $.each(style, function(index, value) {
                                $('#sec_filter_style').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }

                        if (response.color && response.color.length > 0) {
                            let color = response.color;
                            $("#sec_filter_color").empty();
                            $.each(color, function(index, value) {
                                $('#sec_filter_color').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }

                        if (response.part && response.part.length > 0) {
                            let part = response.part;
                            $("#sec_filter_part").empty();
                            $.each(part, function(index, value) {
                                $('#sec_filter_part').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }

                        if (response.size && response.size.length > 0) {
                        let size = response.size;
                            $("#sec_filter_size").empty();
                            $('#size_filter').empty();
                            $.each(size, function(index, value) {
                                $('#sec_filter_size').append('<option value="'+value+'">'+value+'</option>');
                                $('#size_filter').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }

                        if (response.no_cut && response.no_cut.length > 0) {
                            let no_cut = response.no_cut;
                            $("#sec_filter_no_cut").empty();
                            $.each(no_cut, function(index, value) {
                                $('#sec_filter_no_cut').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }

                        if (response.tujuan && response.tujuan.length > 0) {
                            let tujuan = response.tujuan;
                            $("#sec_filter_tujuan").empty();
                            $.each(tujuan, function(index, value) {
                                $('#sec_filter_tujuan').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }

                        if (response.tempat && response.tempat.length > 0) {
                            let tempat = response.tempat;
                            $("#sec_filter_tempat").empty();
                            $.each(tempat, function(index, value) {
                                $('#sec_filter_tempat').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }

                        if (response.lokasi && response.lokasi.length > 0) {
                            let lokasi = response.lokasi;
                            $("#sec_filter_lokasi").empty();
                            $.each(lokasi, function(index, value) {
                                $('#sec_filter_lokasi').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }

                        if (response.lokasi_rak && response.lokasi_rak.length > 0) {
                            let lokasi_rak = response.lokasi_rak;
                            $("#sec_filter_lokasi_rak").empty();
                            $.each(lokasi_rak, function(index, value) {
                                $('#sec_filter_lokasi_rak').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }
                    }
                },
                error: function(jqXHR) {
                    document.getElementById('loading').classList.add('d-none');

                    console.error(jqXHR);
                },
            })
        }

        async function updateFilterDetailSec() {
            document.getElementById('loading').classList.remove('d-none');

            $.ajax({
                url: '{{ route('filter-detail-sec-in') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: {
                    dateFrom : $('#tgl-awal').val(),
                    dateTo : $('#tgl-akhir').val(),
                },
                success: function(response) {
                    document.getElementById('loading').classList.add('d-none');

                    if (response) {
                        if (response.buyer && response.buyer.length > 0) {
                            let buyer = response.buyer;
                            $("#detail_sec_filter_buyer").empty();
                            $.each(buyer, function(index, value) {
                                $('#detail_sec_filter_buyer').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }

                        if (response.ws && response.ws.length > 0) {
                            let ws = response.ws;
                            $("#detail_sec_filter_ws").empty();
                            $.each(ws, function(index, value) {
                                $('#detail_sec_filter_ws').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }

                        if (response.style && response.style.length > 0) {
                            let style = response.style;
                            $("#detail_sec_filter_style").empty();
                            $.each(style, function(index, value) {
                                $('#detail_sec_filter_style').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }

                        if (response.color && response.color.length > 0) {
                            let color = response.color;
                            $("#detail_sec_filter_color").empty();
                            $.each(color, function(index, value) {
                                $('#detail_sec_filter_color').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }

                        if (response.lokasi && response.lokasi.length > 0) {
                            let lokasi = response.lokasi;
                            $("#detail_sec_filter_lokasi").empty();
                            $.each(lokasi, function(index, value) {
                                $('#detail_sec_filter_lokasi').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }
                    }
                },
                error: function(jqXHR) {
                    document.getElementById('loading').classList.add('d-none');

                    console.error(jqXHR);
                },
            })
        }

        $('#size_filter').on("change", function() {
            datatableReload();
        });
    </script>
@endsection
