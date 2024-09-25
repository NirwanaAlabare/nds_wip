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
    <div class="modal fade" id="exampleModalMasterKendaraan" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalMasterKendaraanLabel" aria-hidden="true">
        <form action="{{ route('store-ga-master-kendaraan') }}" method="post" onsubmit="submitForm(this, event)"
            name='form_m_kendaraan' id='form_m_kendaraan'>
            @method('POST')
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-info">
                        <h3 class="modal-title fs-5">Tambah Master Kendaraan</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Plat No :</label>
                                    <input type='text' class='form-control form-control-sm' id="txtplat_no"
                                        name="txtplat_no" style="text-transform: uppercase" value = ''
                                        autocomplete="off">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Th. Pembuatan :</label>
                                    <input type='number' class='form-control form-control-sm' id="txtthn_pembuatan"
                                        name="txtthn_pembuatan" style="text-transform: uppercase" value = ''
                                        autocomplete="off">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">No. Mesin :</label>
                                    <input type='text' class='form-control form-control-sm' id="txtno_mesin"
                                        name="txtno_mesin" style="text-transform: uppercase" value = ''
                                        autocomplete="off">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">No Rangka :</label>
                                    <input type='text' class='form-control form-control-sm' id="txtno_rangka"
                                        name="txtno_rangka" style="text-transform: uppercase" value = ''
                                        autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-3">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Merk :</label>
                                    <input type='text' class='form-control form-control-sm' id="txtmerk" name="txtmerk"
                                        style="text-transform: uppercase" value = '' autocomplete="off">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Tipe :</label>
                                    <input type='text' class='form-control form-control-sm' id="txttipe" name="txttipe"
                                        style="text-transform: uppercase" value = '' autocomplete="off">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Warna :</label>
                                    <input type='text' class='form-control form-control-sm' id="txtwarna"
                                        name="txtwarna" style="text-transform: uppercase" value = ''
                                        autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Jenis Bahan Bakar :</label>
                                    <select class='form-control select2bs4' style='width: 100%;' name='cbojns_bhn_bakar'
                                        id='cbojns_bhn_bakar' required>
                                        <option selected="selected" value="" disabled="true">Pilih Bahan Bakar
                                        </option>
                                        @foreach ($data_jns_bhn_bakar as $datajnsbhnbakar)
                                            <option value="{{ $datajnsbhnbakar->isi }}">
                                                {{ $datajnsbhnbakar->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Isi Sillinder :</label>
                                    <input type='text' class='form-control form-control-sm' id="txtisi_silinder"
                                        name="txtisi_silinder" style="text-transform: uppercase" value = ''
                                        autocomplete="off">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Status
                                        (Inventaris, Operasional) :</label>
                                    <select class='form-control select2bs4' style='width: 100%;' name='cbostat'
                                        id='cbostat' required>
                                        <option selected="selected" value="" disabled="true">Pilih Status</option>
                                        @foreach ($data_status as $datastatus)
                                            <option value="{{ $datastatus->isi }}">
                                                {{ $datastatus->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-outline-success btn-sm"><i class="fas fa-check"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>


    <div class="modal fade" id="exampleModalMasterBensin" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalMasterBensinLabel" aria-hidden="true">
        <form action="{{ route('store-ga-master-bahan-bakar') }}" method="post" onsubmit="submitForm(this, event)"
            name='form' id='form'>
            @method('POST')
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-info">
                        <h3 class="modal-title fs-5">Tambah Master Bensin</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Jenis Bahan Bakar :</label>
                            <select class='form-control select2bs4' style='width: 100%;' name='cbo_bhn_bakar'
                                id='cbo_bhn_bakar' required>
                                <option selected="selected" value="" disabled="true">Pilih Jenis</option>
                                @foreach ($data_jns_bhn_bakar as $datajnsbhnbakar)
                                    <option value="{{ $datajnsbhnbakar->isi }}">
                                        {{ $datajnsbhnbakar->tampil }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Nama Bahan Bakar :</label>
                            <input type='text' class='form-control form-control-sm' id="txtnm_bhn_bakar"
                                name="txtnm_bhn_bakar" style="text-transform: uppercase" value = ''
                                autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Harga :</label>
                            <input type='text' class='form-control form-control-sm' id='txtharga' name='txtharga'
                                autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-outline-success btn-sm"><i class="fas fa-check"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="modal fade" id="exampleModalUpdateBensin" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalUpdateBensinLabel" aria-hidden="true">
        <form action="{{ route('update-ga-master-bahan-bakar') }}" method="post" onsubmit="submitForm(this, event)"
            name='form' id='form'>
            @method('POST')
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h3 class="modal-title fs-5">Update Harga Bahan Bakar</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Jenis Bahan Bakar :</label>
                            <input type='text' class='form-control form-control-sm' id='txtedjns' name='txtedjns'
                                autocomplete="off" readonly>
                            <input type='hidden' class='form-control form-control-sm' id='txtedid_bhn_bakar'
                                name='txtedid_bhn_bakar' autocomplete="off" readonly>
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Nama Bahan Bakar :</label>
                            <input type='text' class='form-control form-control-sm' id='txtednm' name='txtednm'
                                autocomplete="off" readonly>
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Harga :</label>
                            <input type='text' class='form-control form-control-sm' id='txtedharga' name='txtedharga'
                                autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-outline-success btn-sm"><i class="fas fa-check"></i> Simpan
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <div class="modal fade" id="exampleModalTransaksi" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalMasterKendaraanLabel" aria-hidden="true">
        <form action="{{ route('store-ga-trans') }}" method="post" onsubmit="submitForm(this, event)"
            name='form_trans' id='form_trans'>
            @method('POST')
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <h3 class="modal-title fs-5">Tambah Transaksi</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Tgl. Transaksi :</label>
                                    <input type='date' class='form-control form-control-sm' id="txttgl_trans"
                                        name="txttgl_trans" style="text-transform: uppercase"
                                        value="{{ date('Y-m-d') }}" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Nama Driver :</label>
                                    <select class='form-control select2bs4' style='width: 100%;' name='cbo_nm_driver'
                                        id='cbo_nm_driver' onchange='getnip();' required>
                                        <option selected="selected" value="" disabled="true">Pilih Nama Driver
                                        </option>
                                        @foreach ($data_driver as $datadriver)
                                            <option value="{{ $datadriver->isi }}">
                                                {{ $datadriver->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">NIP :</label>
                                    <input type='text' class='form-control form-control-sm' id="txtnip"
                                        name="txtnip" style="text-transform: uppercase" value = ''
                                        autocomplete="off" readonly>
                                    <input type='hidden' class='form-control form-control-sm' id="txtnm_driver"
                                        name="txtnm_driver" style="text-transform: uppercase" value = ''
                                        autocomplete="off" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">No. Kendaraan :</label>
                                    <select class='form-control select2bs4' style='width: 100%;' name='cbo_no_kendaraan'
                                        id='cbo_no_kendaraan' onchange='getjns();' required>
                                        <option selected="selected" value="" disabled="true">Pilih Kendaraan
                                        </option>
                                        @foreach ($data_no_kendaraan as $datano_kendaraan)
                                            <option value="{{ $datano_kendaraan->isi }}">
                                                {{ $datano_kendaraan->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Jenis Kendaraan :</label>
                                    <input type='text' class='form-control form-control-sm' id="txtjns_kendaraan"
                                        name="txtjns_kendaraan" style="text-transform: uppercase" value = ''
                                        autocomplete="off" readonly>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Oddometer :</label>
                                    <input type='text' class='form-control form-control-sm' id="txtodoometer"
                                        name="txtodoometer" style="text-transform: uppercase" value = ''
                                        autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Jenis Bahan bakar :</label>
                                    <input type='text' class='form-control form-control-sm' id="txtjns_bhn_bakar"
                                        name="txtjns_bhn_bakar" style="text-transform: uppercase" value=""
                                        autocomplete="off" readonly>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Bahan Bakar :</label>
                                    <select class='form-control select2bs4 form-control-sm' style='width: 100%;'
                                        name='cbobhn_bakar' id='cbobhn_bakar' onchange="getharga()"></select>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Jumlah :</label>
                                    <input type='text' class='form-control form-control-sm' id="txtjml"
                                        name="txtjml" style="text-transform: uppercase" value = ''
                                        oninput="hitungbayar()" autocomplete="off">
                                    <input type='hidden' class='form-control form-control-sm' id="txtharga_bensin"
                                        name="txtharga_bensin">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Total Bayar :</label>
                                    <input type='text' class='form-control form-control-sm' id="txttot_bayar"
                                        name="txttot_bayar" style="text-transform: uppercase" value = ''
                                        autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-outline-success btn-sm"><i class="fas fa-check"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="modal fade" id="exampleModalUpdateRealisasi" tabindex="-1" role="dialog" aria-hidden="true">
        <form action="{{ route('update_ga_realisasi') }}" method="post" onsubmit="submitForm(this, event)"
            name='form_update_trans' id='form_update_trans'>
            @method('POST')
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <h3 class="modal-title fs-5">Update Realisasi</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">No. Form :</label>
                                    <input type='text' class='form-control form-control-sm' id="edtxtno_form"
                                        name="edtxtno_form" style="text-transform: uppercase" value=""
                                        autocomplete="off" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Tgl. Transaksi :</label>
                                    <input type='date' class='form-control form-control-sm' id="edtxttgl_trans"
                                        name="edtxttgl_trans" style="text-transform: uppercase" value=""
                                        autocomplete="off" readonly>
                                    <input type='hidden' class='form-control form-control-sm' id="edtxt_id"
                                        name="edtxt_id" style="text-transform: uppercase" value=""
                                        autocomplete="off" readonly>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Nama Driver :</label>
                                    <select class='form-control select2bs4' style='width: 100%;' name='edcbo_nm_driver'
                                        id='edcbo_nm_driver' onchange='getnip_ed();' disabled>
                                        <option selected="selected" value="" disabled="true">Pilih Nama Driver
                                        </option>
                                        @foreach ($data_driver as $datadriver)
                                            <option value="{{ $datadriver->isi }}">
                                                {{ $datadriver->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">NIP :</label>
                                    <input type='text' class='form-control form-control-sm' id="edtxtnip"
                                        name="edtxtnip" style="text-transform: uppercase" value = ''
                                        autocomplete="off" readonly>
                                    <input type='hidden' class='form-control form-control-sm' id="edtxtnm_driver"
                                        name="edtxtnm_driver" style="text-transform: uppercase" value = ''
                                        autocomplete="off" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">No. Kendaraan :</label>
                                    <select class='form-control select2bs4' style='width: 100%;'
                                        name='edcbo_no_kendaraan' id='edcbo_no_kendaraan' onchange='getjns_ed();'
                                        disabled required>
                                        <option selected="selected" value="" disabled="true">Pilih Kendaraan
                                        </option>
                                        @foreach ($data_no_kendaraan as $datano_kendaraan)
                                            <option value="{{ $datano_kendaraan->isi }}">
                                                {{ $datano_kendaraan->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Jenis Kendaraan :</label>
                                    <input type='text' class='form-control form-control-sm' id="edtxtjns_kendaraan"
                                        name="edtxtjns_kendaraan" style="text-transform: uppercase" value = ''
                                        autocomplete="off" readonly>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Oddometer :</label>
                                    <input type='text' class='form-control form-control-sm' id="edtxtodoometer"
                                        name="edtxtodoometer" style="text-transform: uppercase" value = ''
                                        autocomplete="off" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Jenis Bahan bakar :</label>
                                    <input type='text' class='form-control form-control-sm' id="edtxtjns_bhn_bakar"
                                        name="edtxtjns_bhn_bakar" style="text-transform: uppercase" value=""
                                        autocomplete="off" readonly>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Bahan Bakar :</label>
                                    <input type='text' class='form-control form-control-sm' id="edcbobhn_bakar"
                                        name="edcbobhn_bakar" style="text-transform: uppercase" value=""
                                        autocomplete="off" readonly>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Jumlah :</label>
                                    <input type='text' class='form-control form-control-sm' id="edtxtjml"
                                        name="edtxtjml" style="text-transform: uppercase" value = ''
                                        oninput="hitungbayar()" autocomplete="off" readonly>
                                    <input type='hidden' class='form-control form-control-sm' id="edtxtharga_bensin"
                                        name="edtxtharga_bensin">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Total Bayar :</label>
                                    <input type='text' class='form-control form-control-sm' id="edtxttot_bayar"
                                        name="edtxttot_bayar" style="text-transform: uppercase" value = ''
                                        autocomplete="off" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Jumlah Realisasi:</label>
                                    <input type='text' class='form-control form-control-sm' id="txtjml_realisasi"
                                        name="txtjml_realisasi" style="text-transform: uppercase"
                                        value = ''autocomplete="off">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Realisasi Bayar :</label>
                                    <input type='text' class='form-control form-control-sm' id="txtbayar_realisasi"
                                        name="txtbayar_realisasi" style="text-transform: uppercase"
                                        value = ''autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-outline-success btn-sm"><i class="fas fa-check"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>


    <div class="modal fade" id="exampleModalEditTrans" tabindex="-1" role="dialog" aria-hidden="true">
        <form action="{{ route('update_ga_trans') }}" method="post" onsubmit="submitForm(this, event)"
            name='form_edit_trans' id='form_update_trans'>
            @method('POST')
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h3 class="modal-title fs-5">Edit Transaksi</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">No. Form :</label>
                                    <input type='text' class='form-control form-control-sm' id="edit_txtno_form"
                                        name="edit_txtno_form" style="text-transform: uppercase" value=""
                                        autocomplete="off" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Tgl. Transaksi :</label>
                                    <input type='date' class='form-control form-control-sm' id="edit_txttgl_trans"
                                        name="edit_txttgl_trans" style="text-transform: uppercase" value=""
                                        autocomplete="off" readonly>
                                    <input type='hidden' class='form-control form-control-sm' id="edit_txt_id"
                                        name="edit_txt_id" style="text-transform: uppercase" value=""
                                        autocomplete="off" readonly>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Nama Driver :</label>
                                    <select class='form-control select2bs4' style='width: 100%;'
                                        name='edit_cbo_nm_driver' id='edit_cbo_nm_driver' onchange='getnip_edit();'>
                                        <option selected="selected" value="" disabled="true">Pilih Nama Driver
                                        </option>
                                        @foreach ($data_driver as $datadriver)
                                            <option value="{{ $datadriver->isi }}">
                                                {{ $datadriver->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">NIP :</label>
                                    <input type='text' class='form-control form-control-sm' id="edit_txtnip"
                                        name="edit_txtnip" style="text-transform: uppercase" value = ''
                                        autocomplete="off" readonly>
                                    <input type='hidden' class='form-control form-control-sm' id="edit_txtnm_driver"
                                        name="edit_txtnm_driver" style="text-transform: uppercase" value = ''
                                        autocomplete="off" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">No. Kendaraan :</label>
                                    <select class='form-control select2bs4' style='width: 100%;'
                                        name='edit_cbo_no_kendaraan' id='edit_cbo_no_kendaraan' onchange='getjns_edit();'
                                        required autocomplete="off">
                                        <option selected="selected" value="" disabled="true">Pilih Kendaraan
                                        </option>
                                        @foreach ($data_no_kendaraan as $datano_kendaraan)
                                            <option value="{{ $datano_kendaraan->isi }}">
                                                {{ $datano_kendaraan->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Jenis Kendaraan :</label>
                                    <input type='text' class='form-control form-control-sm' id="edit_txtjns_kendaraan"
                                        name="edit_txtjns_kendaraan" style="text-transform: uppercase" value = ''
                                        autocomplete="off" readonly>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Oddometer :</label>
                                    <input type='text' class='form-control form-control-sm' id="edit_txtodoometer"
                                        name="edit_txtodoometer" style="text-transform: uppercase" value = ''
                                        autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Jenis Bahan bakar :</label>
                                    <input type='text' class='form-control form-control-sm' id="edit_txtjns_bhn_bakar"
                                        name="edit_txtjns_bhn_bakar" style="text-transform: uppercase" value=""
                                        readonly autocomplete="off">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Bahan Bakar :</label>
                                    <select class='form-control select2bs4 form-control-sm' style='width: 100%;'
                                        name='edit_cbobhn_bakar' id='edit_cbobhn_bakar'></select>
                                    <input type='hidden' class='form-control form-control-sm' id="edit_id_bhn_bakar"
                                        name="edit_id_bhn_bakar">
                                    {{-- <select class='form-control select2bs4' style='width: 100%;' name='edit_cbobhn_bakar'
                                        id='edit_cbobhn_bakar'>
                                        <option selected="selected" value="" disabled="true">Pilih Nama Driver
                                        </option>
                                        @foreach ($data_bensin as $databensin)
                                            <option value="{{ $databensin->isi }}">
                                                {{ $databensin->tampil }}
                                            </option>
                                        @endforeach
                                    </select> --}}
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Jumlah :</label>
                                    <input type='text' class='form-control form-control-sm' id="edit_txtjml"
                                        name="edit_txtjml" style="text-transform: uppercase"
                                        value = ''autocomplete="off">
                                    <input type='hidden' class='form-control form-control-sm' id="edit_txtharga_bensin"
                                        name="edit_txtharga_bensin">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Total Bayar :</label>
                                    <input type='text' class='form-control form-control-sm' id="edit_txttot_bayar"
                                        name="edit_txttot_bayar" style="text-transform: uppercase" value = ''
                                        autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-outline-success btn-sm"><i class="fas fa-check"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>



    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold text-dark">Pengajuan Bahan Bakar <i class="fas fa-gas-pump"></i></h5>
    </div>
    <div class='row'>
        <div class="col-md-7">
            <div class="card card-dark collapsed-card" id = "master-kendaraan-card">
                <div class="card-header">
                    <h5 class="card-title fw-bold mb-0"><i class="fas fa-car-side"></i> Master Kendaraan</h5>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-end gap-3 mb-3">
                        <div class="mb-3">
                            <button class="btn btn-outline-info position-relative btn-sm" data-bs-toggle="modal"
                                data-bs-target="#exampleModalMasterKendaraan" onclick="reset_m_kendaraan()"><i
                                    class="fas fa-plus fa-sm"></i>
                                Baru</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="datatable-master-kendaraan" class="table table-bordered table-sm w-100  nowrap">
                            <thead class="table-info">
                                <tr style='text-align:center; vertical-align:middle'>
                                    <th>Plat No</th>
                                    <th>Th Pembuatan</th>
                                    <th>No Mesin</th>
                                    <th>No Rangka</th>
                                    <th>Merk</th>
                                    <th>Tipe</th>
                                    <th>Warna</th>
                                    <th>Jenis Bahan Bakar</th>
                                    <th>Isi Sillinder</th>
                                    <th>Status</th>
                                    <th>Act</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card card-dark  collapsed-card ">
                <div class="card-header">
                    <h5 class="card-title fw-bold mb-0"><i class="fas fa-gas-pump"></i> Master Bensin</h5>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-end gap-3 mb-3">
                        <div class="mb-3">
                            <button class="btn btn-outline-info position-relative btn-sm" data-bs-toggle="modal"
                                data-bs-target="#exampleModalMasterBensin" onclick="reset()"><i
                                    class="fas fa-plus fa-sm"></i>
                                Baru</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="datatable-master-bensin" class="table table-bordered table-sm w-100  nowrap">
                            <thead class="table-info">
                                <tr style='text-align:center; vertical-align:middle'>
                                    <th>Jenis</th>
                                    <th>Nama</th>
                                    <th>Harga Per Liter</th>
                                    <th>Act</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="card card-success">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> List Transaksi</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Transaksi Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        oninput="dataTableTransReload();" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Transaksi Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        oninput="dataTableTransReload();" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <button class="btn btn-outline-info position-relative btn-sm" data-bs-toggle="modal"
                        data-bs-target="#exampleModalTransaksi" onclick="reset_trans()"><i class="fas fa-plus fa-sm"></i>
                        Baru</button>
                </div>
                <div class="mb-3">
                    <a onclick="export_excel_data_bahan_bakar()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable-trans" class="table table-bordered table-sm w-100 table-hover display nowrap">
                    <thead class="table-primary">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>No. Form</th>
                            <th>Tgl. Trans</th>
                            <th>Plat No</th>
                            <th>Driver</th>
                            <th>Kendaraan</th>
                            <th>Oddometer</th>
                            <th>Bahan Bakar</th>
                            <th>Jumlah</th>
                            <th>Total Pembayaran</th>
                            <th>Jumlah Realisasi</th>
                            <th>Realisasi Bayar</th>
                            <th>Tgl. Realisasi</th>
                            <th>Status</th>
                            <th>Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            containerCssClass: 'form-control-sm rounded'
        });
    </script>
    <script>
        $(document).ready(function() {
            dataTableTransReload();
            dataTableMasterBahanBakarReload();
            $('#master-kendaraan-card').on('expanded.lte.cardwidget', () => {
                dataTableMasterKendaraanReload();
            });
        })

        var rupiah = document.getElementById("txtharga");
        rupiah.addEventListener("keyup", function(e) {
            // tambahkan 'Rp.' pada saat form di ketik
            // gunakan fungsi formatRupiah() untuk mengubah angka yang di ketik menjadi format angka
            rupiah.value = formatRupiah(this.value, "Rp. ");
        });

        /* Fungsi formatRupiah */
        function formatRupiah(angka, prefix) {
            var number_string = angka.replace(/[^,\d]/g, "").toString(),
                split = number_string.split(","),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            // tambahkan titik jika yang di input sudah menjadi angka ribuan
            if (ribuan) {
                separator = sisa ? "." : "";
                rupiah += separator + ribuan.join(".");
            }

            rupiah = split[1] != undefined ? rupiah + "," + split[1] : rupiah;
            return prefix == undefined ? rupiah : rupiah ? "Rp. " + rupiah : "";
        }

        function reset() {
            $("#form").trigger("reset");
        }

        function reset_m_kendaraan() {
            $("#form_m_kendaraan").trigger("reset");
        }

        function reset_trans() {
            $("#form_trans").trigger("reset");
            $("#cbo_nm_driver").val('').trigger('change');
            $("#txtnip").val('');
            $("#txtnm_driver").val('');
            $("#cbo_no_kendaraan").val('').trigger('change');
            $("#txtjns_kendaraan").val('').trigger('change');
            $("#txtodoometer").val('');
            $("#txtjns_bhn_bakar").val('').trigger('change');
            $("#cbobhn_bakar").val('').trigger('change');
            $("#txtjml").val('');
            $("#txtharga_bensin").val('').trigger('change');
            $("#txttot_bayar").val('');
        }

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>

    <script>
        function dataTableMasterBahanBakarReload() {
            let datatable_m_bhn_bakar = $("#datatable-master-bensin").DataTable({
                ordering: true,
                processing: true,
                serverSide: true,
                paging: true,
                searching: true,
                info: false,
                ordering: false,
                ajax: {
                    url: '{{ route('show-master-bahan-bakar') }}',
                },
                columns: [{
                        data: 'jns_bhn_bakar'

                    },
                    {
                        data: 'nm_bhn_bakar'

                    },
                    {
                        data: 'harga_barang'
                    },
                    {
                        data: 'id'
                    },
                ],
                columnDefs: [{
                        targets: [3],
                        render: (data, type, row, meta) => {
                            return `
                <div
                class='d-flex gap-1 justify-content-center'>
                <a class='btn btn-warning btn-sm' data-bs-toggle="modal"
                        data-bs-target="#exampleModalUpdateBensin" onclick="UpdateHargaBensin(` + row.id + `);" ><i class='fas fa-edit'></i></a>
                </div>
                    `;
                        }
                    },
                    {
                        "className": "dt-center",
                        "targets": "_all"
                    },



                ]
            });
        }

        function dataTableMasterKendaraanReload() {
            let datatable_m_kendaraan = $("#datatable-master-kendaraan").DataTable({
                processing: true,
                serverSide: true,
                paging: true,
                searching: true,
                info: false,
                ordering: false,
                responsive: true,
                destroy: true,
                ajax: {
                    url: '{{ route('show-master-kendaraan') }}',
                },
                columns: [{
                        data: 'plat_no'

                    },
                    {
                        data: 'thn_pembuatan'

                    },
                    {
                        data: 'no_mesin'
                    },
                    {
                        data: 'no_rangka'
                    },
                    {
                        data: 'merk'
                    },
                    {
                        data: 'tipe'
                    },
                    {
                        data: 'warna'
                    },
                    {
                        data: 'jns_bhn_bakar'
                    },
                    {
                        data: 'isi_silinder_fix'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'id'
                    },
                ],
                columnDefs: [{
                        targets: [10],
                        render: (data, type, row, meta) => {
                            return `
                <div
                class='d-flex gap-1 justify-content-center'>
                <a class='btn btn-warning btn-sm' data-bs-toggle='tooltip' onclick='notif()'><i class='fas fa-edit'></i></a>
                </div>
                    `;
                        }
                    },
                    // <a class='btn btn-warning btn-sm' href='{{ route('create-dc-in') }}/` +
                //             row.id +
                //             `' data-bs-toggle='tooltip'><i class='fas fa-edit'></i></a>
                    {
                        "className": "dt-center",
                        "targets": "_all"
                    },



                ]
            });
        }

        function dataTableTransReload() {
            let datatable_m_kendaraan = $("#datatable-trans").DataTable({
                processing: true,
                serverSide: true,
                paging: true,
                searching: true,
                info: false,
                ordering: false,
                destroy: true,
                ajax: {
                    url: '{{ route('pengajuan-bahan-bakar') }}',
                    data: function(d) {
                        d.dateFrom = $('#tgl-awal').val();
                        d.dateTo = $('#tgl-akhir').val();
                    },
                },
                columns: [{
                        data: 'no_trans'

                    },
                    {
                        data: 'tgl_trans_fix'

                    },
                    {
                        data: 'plat_no'
                    },
                    {
                        data: 'nm_driver'
                    },
                    {
                        data: 'jns_kendaraan'
                    },
                    {
                        data: 'oddometer'
                    },
                    {
                        data: 'nm_bhn_bakar'
                    },
                    {
                        data: 'jml_fix'
                    },
                    {
                        data: 'tot_biaya_fix'
                    },
                    {
                        data: 'realisasi_jml_fix'
                    },
                    {
                        data: 'tot_biaya_realisasi_fix'
                    },
                    {
                        data: 'realisasi_tgl_fix'
                    },
                    {
                        data: 'status_fix'
                    },
                    {
                        data: 'id'
                    },
                ],
                columnDefs: [{
                        targets: [13],
                        render: (data, type, row, meta) => {
                            if (row.status == 'APPROVE') {
                                return `
                <div
                class='d-flex gap-1 justify-content-center'>
                <a class='btn btn-secondary btn-sm' data-bs-toggle='tooltip'
                onclick="export_pdf(` + row.id + `)"><i class='fas fa-print'></i></a>
                </div>
                    `;

                            } else if (row.status == 'CANCEL') {

                                return `
                <div
                <a class='btn btn-secondary btn-sm' data-bs-toggle='tooltip'
                onclick="export_pdf(` + row.id + `)"><i class='fas fa-print'></i></a>
                </div>
                    `;
                            } else if (row.status == 'PENDING APPROVE') {
                                return `
                <div
                class='d-flex gap-1 justify-content-center'>
                <a class='btn btn-success btn-sm' data-bs-toggle="modal"
                        data-bs-target="#exampleModalUpdateRealisasi"
                onclick="edit_realisasi(` + row.id + `)"><i class='fas fa-thumbs-up'></i></a>
                <a class='btn btn-secondary btn-sm' data-bs-toggle='tooltip'
                onclick="export_pdf(` + row.id + `)"><i class='fas fa-print'></i></a>
                </div>
                    `;
                            } else if (row.status == 'WAITING') {
                                return `
                <div
                class='d-flex gap-1 justify-content-center'>
                <a class='btn btn-warning btn-sm' data-bs-toggle="modal"
                        data-bs-target="#exampleModalEditTrans"
                onclick="edit_trans(` + row.id + `)"><i class='fas fa-edit'></i></a>
                <a class='btn btn-success btn-sm' data-bs-toggle="modal"
                        data-bs-target="#exampleModalUpdateRealisasi"
                onclick="edit_realisasi(` + row.id + `)"><i class='fas fa-thumbs-up'></i></a>
                <a class='btn btn-secondary btn-sm' data-bs-toggle='tooltip'
                onclick="export_pdf(` + row.id + `)"><i class='fas fa-print'></i></a>
                </div>
                    `;
                            }

                        }
                    },
                    {
                        "className": "align-middle",
                        "targets": "_all"
                    },
                    {
                        targets: '_all',
                        render: (data, type, row, meta) => {
                            var color = '#000000';
                            if (row.status == 'WAITING') {
                                color = '#7b7d7d';
                            } else if (row.status == 'PENDING APPROVE') {
                                color = '#0000FF';
                            } else if (row.status == 'APPROVE') {
                                color = '#008000';
                            } else if (row.status == 'CANCEL') {
                                color = '#FF0000';
                            }
                            return '<span style="font-weight: 600; color:' + color + '">' + data +
                                '</span>';
                        }
                    }



                ]
            });
        }

        function export_pdf(id_n) {
            let id = id_n;
            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "get",
                url: '{{ route('export_pdf_pengajuan_bhn_bakar') }}',
                data: {
                    id: id
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    {
                        swal.close();
                        Swal.fire({
                            title: 'Data Sudah Di Export!',
                            icon: "success",
                            showConfirmButton: true,
                            allowOutsideClick: false
                        });
                        var blob = new Blob([response]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "a.pdf";
                        link.click();

                    }
                },
            });
        }

        function getnip() {
            let cbo_nm_driver = document.form_trans.cbo_nm_driver.value;
            jQuery.ajax({
                url: '{{ route('show-ga-get-nip') }}',
                method: 'get',
                data: {
                    cbo_nm_driver: $('#cbo_nm_driver').val()
                },
                dataType: 'json',
                success: function(response) {
                    document.getElementById('txtnip').value = response.nik;
                    document.getElementById('txtnm_driver').value = response.employee_name;
                },
            });
        };

        function getnip_ed() {
            let cbo_nm_driver = document.form_update_trans.edcbo_nm_driver.value;
            jQuery.ajax({
                url: '{{ route('show-ga-get-nip') }}',
                method: 'get',
                data: {
                    cbo_nm_driver: $('#edcbo_nm_driver').val()
                },
                dataType: 'json',
                success: function(response) {
                    document.getElementById('edtxtnip').value = response.nik;
                    document.getElementById('edtxtnm_driver').value = response.employee_name;
                },
            });
        };

        function getnip_edit() {
            let cbo_nm_driver = document.form_edit_trans.edit_cbo_nm_driver.value;
            jQuery.ajax({
                url: '{{ route('show-ga-get-nip') }}',
                method: 'get',
                data: {
                    cbo_nm_driver: cbo_nm_driver
                },
                dataType: 'json',
                success: function(response) {
                    document.getElementById('edit_txtnip').value = response.nik;
                    document.getElementById('edit_txtnm_driver').value = response.employee_name;
                },
            });
        };


        function getjns() {
            let cbo_no_kendaraan = document.form_trans.cbo_no_kendaraan.value;
            jQuery.ajax({
                url: '{{ route('show-ga-get-jns') }}',
                method: 'get',
                data: {
                    cbo_no_kendaraan: cbo_no_kendaraan
                },
                dataType: 'json',
                success: function(response) {
                    document.getElementById('txtjns_kendaraan').value = response.jns_kendaraan;
                    document.getElementById('txtjns_bhn_bakar').value = response.jns_bhn_bakar;
                    if (response.jns_bhn_bakar != '') {
                        let html = $.ajax({
                            type: "GET",
                            url: '{{ route('show-ga-get-bhn-bakar') }}',
                            data: {
                                txtjns_bhn_bakar: response.jns_bhn_bakar
                            },
                            async: false
                        }).responseText;
                        if (html != "") {
                            $("#cbobhn_bakar").html(html);
                        }
                    }

                },
            });
        };

        function getjns_ed() {
            let cbo_no_kendaraan = document.form_update_trans.edcbo_no_kendaraan.value;
            jQuery.ajax({
                url: '{{ route('show-ga-get-jns') }}',
                method: 'get',
                data: {
                    cbo_no_kendaraan: cbo_no_kendaraan
                },
                dataType: 'json',
                success: function(response) {
                    document.getElementById('edtxtjns_kendaraan').value = response.jns_kendaraan;
                    document.getElementById('edtxtjns_bhn_bakar').value = response.jns_bhn_bakar;
                    if (response.jns_bhn_bakar != '') {
                        let html = $.ajax({
                            type: "GET",
                            url: '{{ route('show-ga-get-bhn-bakar') }}',
                            data: {
                                txtjns_bhn_bakar: response.jns_bhn_bakar
                            },
                            async: false
                        }).responseText;
                    }

                },
            });
        };

        function getjns_edit() {
            let cbo_no_kendaraan = document.form_edit_trans.edit_cbo_no_kendaraan.value;
            jQuery.ajax({
                url: '{{ route('show_ga_get_jns_edit') }}',
                method: 'get',
                data: {
                    cbo_no_kendaraan: cbo_no_kendaraan
                },
                dataType: 'json',
                success: function(response) {
                    document.getElementById('edit_txtjns_bhn_bakar').value = response.jns_bhn_bakar;
                    document.getElementById('edit_txtjns_kendaraan').value = response.jns_kendaraan;
                    getbhn_bakar_edit();
                },
            });
        };

        // function getbhn_bakar_edit() {
        //     let edit_txtjns_bhn_bakar = document.form_edit_trans.edit_txtjns_bhn_bakar.value;
        //     jQuery.ajax({
        //         url: '{{ route('show_ga_get_bhn_bakar_edit') }}',
        //         method: 'get',
        //         data: {
        //             edit_txtjns_bhn_bakar: edit_txtjns_bhn_bakar
        //         },
        //         dataType: 'json',
        //         success: function(response) {
        //             document.getElementById('edit_cbobhn_bakar').value = response.nm_bhn_bakar;
        //         },
        //     });
        // };



        function getbhn_bakar_edit() {
            let edit_txtjns_bhn_bakar = document.form_edit_trans.edit_txtjns_bhn_bakar.value;
            let edit_id_bhn_bakar = document.form_edit_trans.edit_id_bhn_bakar.value;
            console.log(edit_txtjns_bhn_bakar);
            let html = $.ajax({
                type: "GET",
                url: '{{ route('show_ga_get_bhn_bakar_edit') }}',
                data: {
                    edit_txtjns_bhn_bakar: edit_txtjns_bhn_bakar,
                    edit_id_bhn_bakar: edit_id_bhn_bakar
                },
                async: false
            }).responseText;
            // console.log(html != "");
            if (html != "") {
                $("#edit_cbobhn_bakar").html(html);
                $("#edit_cbobhn_bakar").val(edit_id_bhn_bakar);
                // $("#cbomarker").prop("disabled", false);
                // $("#txtqtyply").prop("readonly", false);
            }
        };

        function getharga() {
            let cbobhn_bakar = document.form_trans.cbobhn_bakar.value;
            jQuery.ajax({
                url: '{{ route('show-ga-get-harga') }}',
                method: 'get',
                data: {
                    cbobhn_bakar: cbobhn_bakar
                },
                dataType: 'json',
                success: function(response) {
                    document.getElementById('txtharga_bensin').value = response.harga;
                },
            });
        };

        function getharga_ed() {
            let cbobhn_bakar = document.form_update_trans.cbobhn_bakar.value;
            jQuery.ajax({
                url: '{{ route('show-ga-get-harga') }}',
                method: 'get',
                data: {
                    cbobhn_bakar: cbobhn_bakar
                },
                dataType: 'json',
                success: function(response) {
                    document.getElementById('edtxtharga_bensin').value = response.harga;
                },
            });
        };

        function hitungbayar() {
            let harga_trans = document.form_trans.txtharga_bensin.value;
            let jumlah_trans = document.form_trans.txtjml.value;
            totbayar = harga_trans * jumlah_trans;
            angkab = totbayar;
            document.getElementById('txttot_bayar').value = angkab;

            // function formatRupiahb(angkab, "Rp. ") {
            //     var number_stringb = angkab.replace(/[^,\d]/g, "").toString(),
            //         splitb = number_stringb.split(","),
            //         sisab = splitb[0].length % 3,
            //         rupiahb = splitb[0].substr(0, sisab),
            //         ribuanb = splitb[0].substr(sisab).match(/\d{3}/gi);

            //     // tambahkan titik jika yang di input sudah menjadi angka ribuan
            //     if (ribuanb) {
            //         separatorb = sisab ? "." : "";
            //         rupiahb += separatorb + ribuanb.join(".");
            //     }

            //     rupiahb = splitb[1] != undefined ? rupiahb + "," + splitb[1] : rupiahb;
            //     return prefixb == undefined ? rupiahb : rupiahb ? "Rp. " + rupiahb : "";
            // }

        }

        function UpdateHargaBensin(id_c) {
            jQuery.ajax({
                url: '{{ route('show_data_bahan_bakar') }}',
                method: 'GET',
                data: {
                    id_c: id_c
                },
                dataType: 'json',
                success: async function(response) {
                    document.getElementById('txtedjns').value = response.jns_bhn_bakar;
                    document.getElementById('txtednm').value = response.nm_bhn_bakar;
                    document.getElementById('txtedharga').value = response.harga;
                    document.getElementById('txtedid_bhn_bakar').value = id_c;
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        }

        function edit_trans(id_c) {
            jQuery.ajax({
                url: '{{ route('show_data_transaksi_edit') }}',
                method: 'GET',
                data: {
                    id_c: id_c
                },
                dataType: 'json',
                success: async function(response) {
                    document.getElementById('edit_txt_id').value = id_c;
                    document.getElementById('edit_txtno_form').value = response.no_trans;
                    document.getElementById('edit_txttgl_trans').value = response.tgl_trans;
                    $("#edit_cbo_nm_driver").val(response.nip).trigger('change');
                    $("#edit_cbo_no_kendaraan").val(response.plat_no).trigger('change');
                    document.getElementById('edit_id_bhn_bakar').value = response.id_bhn_bakar;
                    // console.log(response.id_bhn_bakar);
                    $("#edit_cbobhn_bakar").val(response.id_bhn_bakar).trigger('change');
                    // if (response.jns_bhn_bakar == 'BENSIN') {
                    //     $cek_bhn_bakar = data_bensin;
                    // } else {
                    //     $cek_bhn_bakar = data_solar;
                    // }
                    document.getElementById('edit_txtodoometer').value = response.oddometer;
                    document.getElementById('edit_txtjml').value = response.jml;
                    document.getElementById('edit_txttot_bayar').value = response.tot_biaya;
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        }




        function edit_realisasi(id_c) {
            jQuery.ajax({
                url: '{{ route('show_data_transaksi') }}',
                method: 'GET',
                data: {
                    id_c: id_c
                },
                dataType: 'json',
                success: async function(response) {
                    document.getElementById('edtxt_id').value = id_c;
                    document.getElementById('edtxtno_form').value = response.no_trans;
                    document.getElementById('edtxttgl_trans').value = response.tgl_trans;
                    $("#edcbo_nm_driver").val(response.nip).trigger('change');
                    $("#edcbo_no_kendaraan").val(response.plat_no).trigger('change');
                    document.getElementById('edcbobhn_bakar').value = response.nm_bhn_bakar;
                    document.getElementById('edtxtodoometer').value = response.oddometer;
                    document.getElementById('edtxtjml').value = response.jml;
                    document.getElementById('edtxttot_bayar').value = response.tot_biaya;
                    document.getElementById('txtjml_realisasi').value = response.realisasi_jml;
                    document.getElementById('txtbayar_realisasi').value = response.realisasi_biaya;
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        }

        function export_excel_data_bahan_bakar() {
            let from = document.getElementById("tgl-awal").value;
            let to = document.getElementById("tgl-akhir").value;

            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "get",
                url: '{{ route('export_excel_data_bahan_bakar') }}',
                data: {
                    from: from,
                    to: to
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    {
                        swal.close();
                        Swal.fire({
                            title: 'Data Sudah Di Export!',
                            icon: "success",
                            showConfirmButton: true,
                            allowOutsideClick: false
                        });
                        var blob = new Blob([response]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Laporan Pengajuan Bahan Bakar " +
                            from + " sampai " +
                            to + ".xlsx";
                        link.click();

                    }
                },
            });
        }
    </script>
@endsection
