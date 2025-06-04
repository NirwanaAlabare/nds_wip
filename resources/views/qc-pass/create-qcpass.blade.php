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
<form action="{{ route('store-qcdet-temp') }}" method="post" id="store-qcdet-temp" onsubmit="submitForm2(this, event)">
    @csrf
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                Inspection Form - @foreach ($kode_insp as $kodeinsp) {{ $kodeinsp->no_insp }} <input type="hidden" class="form-control " id="txt_noinsp" name="txt_noinsp" value="{{ $kodeinsp->no_insp }}" readonly> @endforeach
            </h5>
            <br>
            <span class=" ml-12">Form Ke - @foreach ($formke as $fk) {{ $fk->ttl_form }} @endforeach</span>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
    <div class="card-body">
    <div class="form-group row">
    <div class="col-md-4">
        <div class="row">
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Form Number</small></label>
                @foreach ($no_form as $noform)
                <input type="text" class="form-control " id="txt_no_form" name="txt_no_form" value="{{ $noform->kode }}" readonly>
                @endforeach
                </div>
            </div>
            </div>

            <div class="col-md-6">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Date</small></label>
                <input type="date" class="form-control form-control" id="txt_tgl_form" name="txt_tgl_form"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div>

            <div class="col-md-6">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Roll Number</small></label>
                <div class="d-flex justify-content-between">
                    <div class="ml-auto">
                       <!--  <span class="input-group-text " style="font-size:24px;" onclick="initScan()"><i class="fa-solid fa-camera"></i></span> -->
                    </div>
                        <input type="text" class="form-control " id="txt_no_roll" name="txt_no_roll" value="" onkeyup="getroll(this.value)">
                </div>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div id="reader" class="form-group">
                </div>
            </div>
            </div>

        </div>
    </div>

    <div class="col-md-4">
        <div class="row">

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Weight</small></label>
               <!--  <input type="text" class="form-control " id="txt_berat" name="txt_berat" value="" > -->
                <div class="d-flex justify-content-between">
                        <input type="text" class="form-control " id="txt_berat" name="txt_berat" value="" style="text-align: right;">
                    <div class="ml-auto">
                        <span class="form-control input-group-text ">KG</span>
                    </div>
                    <div class="ml-2">
                        <select class="form-control select2bs4" id="txt_unit" name="txt_unit" style="width: 100%;" onchange="getconvalue(this.value);">
                            <option selected="selected" value="">Convert Unit</option>
                            <option value="lbs">lbs</option>
                        </select>
                    </div>
                    <div class="ml-auto">
                        <input type="text" class="form-control " id="txt_berat_2" name="txt_berat_2" value="" style="text-align: right;" readonly>
                    </div>
                </div>
                </div>
            </div>
            </div>

   <!--          <div class="col-md-5">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Width</small></label>
               <div class="d-flex justify-content-between">
                        <input type="text" class="form-control " id="txt_lebar" name="txt_lebar" value="" style="text-align: right;">
                    <div class="ml-auto">
                        <span class="form-control input-group-text ">Inch</span>
                    </div>
                </div>
                </div>
            </div>
            </div> -->

            <div class="col-md-8">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Width</small></label>
                <div class="d-flex justify-content-between">
                        <!-- <input type="text" class="form-control " id="txt_lebar1" name="txt_lebar1" value="" > -->
                        <div class="d-flex justify-content-between">
                            <input type="text" class="form-control " id="lebar_txt" name="lebar_txt" value="" style="text-align: right;" onkeyup="getwidth3(this.value)">
                            <div class="ml-auto">
                            <select class="form-control select2bs4" id="txt_unit_lebar" name="txt_unit_lebar" style="width: 100%;" onchange="getconvaluewidth3(this.value);">
                            <option value="Inch">Inch</option>
                            <option value="Cm">Cm</option>
                        </select>
                            </div>
                        </div>&nbsp&nbsp&nbsp&nbsp
                        <!-- <input type="text" class="form-control " id="txt_lebar2" name="txt_lebar2" value="" >  -->
                        <div class="d-flex justify-content-between">
                            <input type="text" class="form-control " id="txt_lebar" name="txt_lebar" style="text-align: right;" readonly>
                            <div class="ml-auto">
                            <span class="form-control input-group-text ">Inch</span>
                            </div>
                        </div>
                </div>
                </div>
            </div>
            </div>

            <div class="col-md-4">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Fabric Supplier</small></label>
                <input type="text" class="form-control " id="txt_fab_supp" name="txt_fab_supp" value="" >
                </div>
            </div>
            </div>

        </div>
    </div>

    <div class="col-md-4">
        <div class="row">

            <div class="col-md-6">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Inspector</small></label>
                <input type="text" class="form-control " id="txt_inspektor" name="txt_inspektor" value="" >
                </div>
            </div>
            </div>

            <div class="col-md-6">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Machine No</small></label>
                <input type="text" class="form-control " id="txt_no_mesin" name="txt_no_mesin" value="" >
                </div>
            </div>
            </div>

            <div class="col-md-6">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Actual Weight</small></label>
                <!-- <input type="text" class="form-control " id="txt_aktual" name="txt_aktual" value="" > -->
                <div class="d-flex justify-content-between">
                        <input type="text" class="form-control " id="txt_aktual" name="txt_aktual" value="" style="text-align: right;">
                    <div class="ml-auto">
                        <span class="form-control input-group-text ">KG</span>
                    </div>
                </div>
                </div>
            </div>
            </div>

            <div class="col-md-6">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Gramage</small></label>
                <input type="text" class="form-control " id="txt_gramasi" name="txt_gramasi" value="" >
                </div>
            </div>
            </div>

        </div>
    </div>
    </div>
</div>
</div>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                Data Detail
            </h5>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
    <div class="card-body">
    <div class="row">
        <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Lenght</small></label>
                <select class="form-control select2bs4" id="txt_panjang" name="txt_panjang" style="width: 100%;">
                    <option selected="selected" value="">Select lenght</option>
                        @foreach ($lenght_qc as $lqc)
                    <option value="{{ $lqc->nama_pilihan }}">
                                {{ $lqc->nama_pilihan }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Code</small></label>
                <div class="d-flex justify-content-between">
                        <input type="text" class="form-control " id="txt_kode_qc" name="txt_kode_qc" value="" readonly>
                    <div class="ml-auto">
                        <span class="input-group-text " style="font-size:24px;" onclick="getTypeqc()"><i class="fa-solid fa-clipboard-list"></i></span>
                    </div>
                </div>
                </div>
            </div>
            </div>

            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Up To 3"</small></label>
                <input type="number" class="form-control " id="txt_upto3" name="txt_upto3" value="" >
                </div>
            </div>
            </div>

            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Over 3" - 6"</small></label>
                <input type="number" class="form-control " id="txt_over3" name="txt_over3" value="" >
                </div>
            </div>
            </div>

            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Over 6" - 9"</small></label>
                <input type="number" class="form-control " id="txt_over6" name="txt_over6" value="" >
                </div>
            </div>
            </div>

            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Over 9"</small></label>
                <input type="number" class="form-control " id="txt_over9" name="txt_over9" value="" >
                </div>
            </div>
            </div>

            <div class="col-md-4">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Full Width</small></label>
                <div class="d-flex justify-content-between">
                        <!-- <input type="text" class="form-control " id="txt_lebar1" name="txt_lebar1" value="" > -->
                        <div class="d-flex justify-content-between">
                            <input type="text" class="form-control " id="txt_lebar_" name="txt_lebar_" value="" style="text-align: right;" onkeyup="getwidth1(this.value)">
                            <div class="ml-auto">
                            <select class="form-control select2bs4" id="txt_unitleb1" name="txt_unitleb1" style="width: 100%;" onchange="getconvaluewidth(this.value);">
                            <option value="Inch">Inch</option>
                            <option value="Cm">Cm</option>
                        </select>
                            </div>
                        </div>&nbsp&nbsp&nbsp&nbsp
                        <!-- <input type="text" class="form-control " id="txt_lebar2" name="txt_lebar2" value="" >  -->
                        <div class="d-flex justify-content-between">
                            <input type="text" class="form-control " id="txt_lebar1" name="txt_lebar1"  style="text-align: right;" readonly>
                            <div class="ml-auto">
                            <span class="form-control input-group-text ">Inch</span>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                </div>

            <div class="col-md-4">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Cutable Width</small></label>
                <div class="d-flex justify-content-between">
                        <!-- <input type="text" class="form-control " id="txt_lebar1" name="txt_lebar1" value="" > -->
                        <div class="d-flex justify-content-between">
                            <input type="text" class="form-control " id="txt_lebar__" name="txt_lebar__" value="" style="text-align: right;" onkeyup="getwidth2(this.value)">
                            <div class="ml-auto">
                            <select class="form-control select2bs4" id="txt_unitleb2" name="txt_unitleb2" style="width: 100%;" onchange="getconvaluewidth2(this.value);">
                            <option value="Inch">Inch</option>
                            <option value="Cm">Cm</option>
                        </select>
                            </div>
                        </div>&nbsp&nbsp&nbsp&nbsp
                        <!-- <input type="text" class="form-control " id="txt_lebar2" name="txt_lebar2" value="" >  -->
                        <div class="d-flex justify-content-between">
                            <input type="text" class="form-control " id="txt_lebar2" name="txt_lebar2" style="text-align: right;" readonly>
                            <div class="ml-auto">
                            <span class="form-control input-group-text ">Inch</span>
                            </div>
                        </div>
                </div>
                </div>
            </div>
            </div>

            <div class="col-md-2">
            <div class="mt-4">
                <!-- onclick="savetempdata()" -->
                <button class="btn btn-sb mt-2"><i class="fa-solid fa-plus"></i> Add</button>
            </div>
            </div>

    </div>
    <br>
    <div class="form-group row">
        <div class="d-flex justify-content-between">
            <div class="ml-auto">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
                <input type="text"  id="cari_item" name="cari_item" autocomplete="off" placeholder="Search Item..." onkeyup="cariitem()">
        </div>
    <div class="table-responsive"style="max-height: 500px">
            <table id="datatable" class="table table-bordered table-head-fixed table-striped w-100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Length</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Code</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Up To 3"</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Over 3" - 6"</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Over 6" - 9"</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Over 9"</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Width Front Middle Back</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Act</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
        </div>
        </div>
</form>
<form action="{{ route('store-qcdet-save') }}" method="post" id="store-qcdet-save" onsubmit="submitForm4(this, event)">
<div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                Data Detail
            </h5>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
    <div class="card-body">
    <div class="row">

        <!-- <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Barcode Length</small></label>
                <input type="text" class="form-control " id="txt_barcode" name="txt_barcode" value="">
                <div class="d-flex justify-content-between">
                    <input type="text" class="form-control " id="txt_barcode" name="txt_barcode" value="" style="text-align: right;">
                    <div class="ml-auto">
                        <span class="form-control input-group-text ">Inch</span>
                    </div>
                </div>
                </div>
            </div>
            </div> -->

            <div class="col-md-4">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Barcode Length</small></label>
                <div class="d-flex justify-content-between">
                        <!-- <input type="text" class="form-control " id="txt_lebar1" name="txt_lebar1" value="" > -->
                        <div class="d-flex justify-content-between">
                            <input type="text" class="form-control " id="txt_barcode_" name="txt_barcode_" value="" style="text-align: right;" onkeyup="getwidthbar(this.value)">
                            <div class="ml-auto">
                            <select class="form-control select2bs4" id="txt_unitbar" name="txt_unitbar" style="width: 100%;" onchange="getconvaluebar(this.value);">
                            <option value="yard">Yard</option>
                            <option value="meter">Meter</option>
                        </select>
                            </div>
                        </div>&nbsp&nbsp&nbsp&nbsp
                        <!-- <input type="text" class="form-control " id="txt_lebar2" name="txt_lebar2" value="" >  -->
                        <div class="d-flex justify-content-between">
                            <input type="text" class="form-control " id="txt_barcode" name="txt_barcode" style="text-align: right;" readonly>
                            <div class="ml-auto">
                            <span class="form-control input-group-text ">Yard</span>
                            </div>
                        </div>
                </div>
                </div>
            </div>
            </div>

           <!--  <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Actual Length</small></label>
                <input type="text" class="form-control " id="txt_akt_lenght" name="txt_akt_lenght" value="">
                <div class="d-flex justify-content-between">
                    <input type="text" class="form-control " id="txt_akt_lenght" name="txt_akt_lenght" value="" style="text-align: right;">
                    <div class="ml-auto">
                        <span class="form-control input-group-text ">Inch</span>
                    </div>
                </div>
                </div>
            </div>
            </div> -->

            <div class="col-md-4">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Actual Length</small></label>
                <div class="d-flex justify-content-between">
                        <!-- <input type="text" class="form-control " id="txt_lebar1" name="txt_lebar1" value="" > -->
                        <div class="d-flex justify-content-between">
                            <input type="text" class="form-control " id="txt_akt_lenght_" name="txt_akt_lenght_" value="" style="text-align: right;" onkeyup="getwidthlength(this.value)">
                            <div class="ml-auto">
                            <select class="form-control select2bs4" id="txt_unitakt_lenght" name="txt_unitakt_lenght" style="width: 100%;" onchange="getconvaluelength(this.value);">
                            <option value="yard">Yard</option>
                            <option value="meter">Meter</option>
                        </select>
                            </div>
                        </div>&nbsp&nbsp&nbsp&nbsp
                        <!-- <input type="text" class="form-control " id="txt_lebar2" name="txt_lebar2" value="" >  -->
                        <div class="d-flex justify-content-between">
                            <input type="text" class="form-control " id="txt_akt_lenght" name="txt_akt_lenght" style="text-align: right;" readonly>
                            <div class="ml-auto">
                            <span class="form-control input-group-text ">Yard</span>
                            </div>
                        </div>
                </div>
                </div>
            </div>
            </div>

            <div class="col-md-5">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Remark</small></label>
                <input type="text" class="form-control " id="txt_remark" name="txt_remark" value="" >
                </div>
            </div>
            </div>
            @foreach ($no_form as $noform2)
                <input type="hidden" class="form-control " id="txt_no_form2" name="txt_no_form2" value="{{ $noform2->kode }}" readonly>
                @endforeach
                <input type="hidden" class="form-control " id="txt_no_roll2" name="txt_no_roll2">

            <div class="col-md-3">
            <div class="mt-4">
                <input type="button" class="btn btn-sb mt-2" value="Calculate" onclick="getavgpoin();getpoin();getlistdsum()">
                <input type="hidden" id="avgpoin" name="avgpoin">
            </div>
            </div>

    </div>
    <br>
    <div class="form-group row">
        <div class="d-flex justify-content-between">
            <div class="ml-auto">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
                <input type="text"  id="cari_item" name="cari_item" autocomplete="off" placeholder="Search Item..." onkeyup="cariitem()">
        </div>
    <div class="table-responsive"style="max-height: 500px">
            <table id="datatable2" class="table table-bordered table-head-fixed table-striped w-100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">No Roll</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Up To 3"</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Over 3" - 6"</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Over 6" - 9"</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Over 9"</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Width</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Total Point</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Actual Point Defect</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Status</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Act</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
     <div id="avg_poin"></div>
            <div class="mb-1">
                <div class="form-group">
                    <input type="button" class="btn btn-success float-end mt-2 ml-2" value="Finish" onclick="finishdata()">
                    <button class="btn btn-sb float-end mt-2 ml-2"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                    <a href="{{ route('qc-pass') }}" class="btn btn-danger float-end mt-2">
                    <i class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>
        </div>
        </form>
    </div>


<div class="modal fade" id="modal-type-qc">
    <form action="{{ route('store-defect') }}" method="post" onsubmit="submitForm3(this, event)">
         @method('POST')
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h4 class="modal-title">Confirm Dialog</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" class="form-control " id="def_noform" name="def_noform" readonly>
                    <input type="hidden" class="form-control " id="def_lenght" name="def_lenght" readonly>
                    <table id="datatable_def" class="table table-bordered table-head-fixed table-striped w-100 text-nowrap" width="100%">
                        <thead>
                            <tr>
                                <th width="10%">check</th>
                                <th width="15%">Code</th>
                                <th width="75%">Critical Defect</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            @foreach ($defect as $dfc)
                                <tr>
                                    <td style="text-align:center"><input type="checkbox" id="pilih_def<?= $i; ?>" name="pilih_def[<?= $i; ?>]" class="flat" value="1"></td>
                                    <td><input type="text" class="form-control" id="kode_def<?= $i; ?>" name="kode_def[<?= $i; ?>]" value="{{ $dfc->kode }}" readonly></td>
                                    <td><input type="text" class="form-control" id="nama_def<?= $i; ?>" name="nama_def[<?= $i; ?>]" value="{{ $dfc->nama_defect }}" readonly></td>
                                </tr>
                            <?php $i++; ?>
                            @endforeach
                                <tr>
                                    <td style="text-align:center"><input type="checkbox" id="pilih_def19" name="pilih_def[19]" class="flat" value="1"></td>
                                    <td><input type="text" class="form-control" id="kode_def19" name="kode_def[19]" value="S" readonly></td>
                                    <td><input type="text" class="form-control" id="nama_def19" name="nama_def[19]" value="" ></td>
                                </tr>
                                <tr>
                                    <td style="text-align:center"><input type="checkbox" id="pilih_def20" name="pilih_def[20]" class="flat" value="1"></td>
                                    <td><input type="text" class="form-control" id="kode_def20" name="kode_def[20]" value="T" readonly></td>
                                    <td><input type="text" class="form-control" id="nama_def20" name="nama_def[20]" value="" ></td>
                                </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Close</button>
                    <button type="submit" class="btn btn-primary toastsDefaultDanger"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Add Defect</button>
                </div>
            </div>
        </div>
    </form>
</div>


<div class="modal fade" id="modal-finis-data">
    <form action="{{ route('finish-data-modal') }}" method="post" onsubmit="submitModal(this, event)">
         @method('GET')
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h4 class="modal-title">Confirm Dialog</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @foreach ($kode_insp as $kodeinsp2)
                <input type="hidden" class="form-control " id="mdl_no_insp" name="mdl_no_insp" value="{{ $kodeinsp2->no_insp }}" readonly>
                @endforeach
                    <label><small>Status Inspection</small></label>
                <select class="form-control select2bs4" id="mdl_status" name="mdl_status" style="width: 100%;">
                    <option selected="selected" value="">Select Status</option>
                        @foreach ($status_insp as $sts)
                    <option value="{{ $sts->nama_pilihan }}">
                                {{ $sts->nama_pilihan }}
                    </option>
                        @endforeach
                </select>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Close</button>
                    <button type="submit" class="btn btn-primary toastsDefaultDanger"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Save</button>
                </div>
            </div>
        </div>
    </form>
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

    <script type="text/javascript">
        function getroll(val){
            let roll = val;
            $('#txt_no_roll2').val(roll);
        }

        function getconvalue(val){
            let unit = val;
            var berat = $('#txt_berat').val();
            var berat2 = 0;

            if (unit == 'lbs') {
                berat2 = berat * 2.20462;
            }else{
                berat2 = 0;
            }

            $('#txt_berat_2').val(berat2.round(2));

        }

        function getconvaluewidth(val){
            let unit = val;
            var berat = $('#txt_lebar_').val();
            var berat2 = 0;

            if (unit == 'Cm') {
                berat2 = berat * 0.393701;
            }else{
                berat2 = berat * 1;
            }

            $('#txt_lebar1').val(berat2.round(2));

        }

        function getconvaluewidth2(val){
            let unit = val;
            var berat = $('#txt_lebar__').val();
            var berat2 = 0;

            if (unit == 'Cm') {
                berat2 = berat * 0.393701;
            }else{
                berat2 = berat * 1;
            }

            $('#txt_lebar2').val(berat2.round(2));

        }

        function getwidth1(val){
            let unit =$('#txt_unitleb1').val();
            var berat = $('#txt_lebar_').val();
            var berat2 = 0;

            if (unit == 'Cm') {
                berat2 = berat * 0.393701;
            }else{
                berat2 = berat * 1;
            }

            $('#txt_lebar1').val(berat2.round(2));

        }

        function getwidth2(val){
            let unit =$('#txt_unitleb2').val();
            var berat = $('#txt_lebar__').val();
            var berat2 = 0;

            if (unit == 'Cm') {
                berat2 = berat * 0.393701;
            }else{
                berat2 = berat * 1;
            }

            $('#txt_lebar2').val(berat2.round(2));

        }

        function getconvaluewidth3(val){
            let unit = val;
            var berat = $('#lebar_txt').val();
            var berat2 = 0;

            if (unit == 'Cm') {
                berat2 = berat * 0.393701;
            }else{
                berat2 = berat * 1;
            }

            $('#txt_lebar').val(berat2.round(2));

        }

        function getwidth3(val){
            let unit =$('#txt_unit_lebar').val();
            var berat = $('#lebar_txt').val();
            var berat2 = 0;

            if (unit == 'Cm') {
                berat2 = berat * 0.393701;
            }else{
                berat2 = berat * 1;
            }

            $('#txt_lebar').val(berat2.round(2));

        }

        function getconvaluebar(val){
            let unit = val;
            var berat = $('#txt_barcode_').val();
            var berat2 = 0;

            if (unit == 'meter') {
                berat2 = berat * 1.09361;
            }else{
                berat2 = berat * 1;
            }

            $('#txt_barcode').val(berat2.round(2));

        }

        function getwidthbar(val){
            let unit =$('#txt_unitbar').val();
            var berat = $('#txt_barcode_').val();
            var berat2 = 0;

            if (unit == 'meter') {
                berat2 = berat * 1.09361;
            }else{
                berat2 = berat * 1;
            }

            $('#txt_barcode').val(berat2.round(2));

        }

        function getconvaluelength(val){
            let unit = val;
            var berat = $('#txt_akt_lenght_').val();
            var berat2 = 0;

            if (unit == 'meter') {
                berat2 = berat * 1.09361;
            }else{
                berat2 = berat * 1;
            }

            $('#txt_akt_lenght').val(berat2.round(2));

        }

        function getwidthlength(val){
            let unit =$('#txt_unitakt_lenght').val();
            var berat = $('#txt_akt_lenght_').val();
            var berat2 = 0;

            if (unit == 'meter') {
                berat2 = berat * 1.09361;
            }else{
                berat2 = berat * 1;
            }

            $('#txt_akt_lenght').val(berat2.round(2));

        }

    </script>

    <script type="text/javascript">
    function submitForm2(e, evt) {
    evt.preventDefault();

    clearModified();

    $.ajax({
        url: e.getAttribute('action'),
        type: e.getAttribute('method'),
        data: new FormData(e),
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.status == 200) {
                $('.modal').modal('hide');
                getlistdata();
                $('#txt_kode_qc').val('');
                $('#txt_upto3').val('');
                $('#txt_over3').val('');
                $('#txt_over6').val('');
                $('#txt_over9').val('');
                $('#txt_lebar1').val('');
                $('#txt_lebar2').val('');

                $('#txt_lebar_').val('');
                // $('#txt_unitleb1').val('inch');
                $('#txt_lebar__').val('');
                // $('#txt_unitleb2').val('inch');
                // $('#txt_unitleb1').text('Inch');
                // $('#txt_unitleb2').text('Inch');

                // if (res.redirect != '') {
                //     if (res.redirect != 'reload') {
                //         location.href = res.redirect;
                //     } else {
                //         location.reload();
                //     }
                // }

                // Swal.fire({
                //     icon: 'success',
                //     title: res.message2,
                //     text: res.message,
                //     showCancelButton: false,
                //     showConfirmButton: true,
                //     confirmButtonText: 'Oke',
                //     timer: 500,
                //     timerProgressBar: true
                // })

                // e.reset();

                if (document.getElementsByClassName('select2')) {
                    $(".select2").val('').trigger('change');
                }
            } else {
                for(let i = 0;i < res.errors; i++) {
                    document.getElementById(res.errors[i]).classList.add('is-invalid');
                    modified.push([res.errors[i], 'classList', 'remove(', "'is-invalid')"])
                }

                iziToast.error({
                    title: 'Error',
                    message: res.message,
                    position: 'topCenter'
                });
            }

            if (res.table != '') {
                $('#'+res.table).DataTable().ajax.reload();
            }

            if (Object.keys(res.additional).length > 0 ) {
                for (let key in res.additional) {
                    if (document.getElementById(key)) {
                        document.getElementById(key).classList.add('is-invalid');

                        if (res.additional[key].hasOwnProperty('message')) {
                            document.getElementById(key+'_error').classList.remove('d-none');
                            document.getElementById(key+'_error').innerHTML = res.additional[key]['message'];
                        }

                        if (res.additional[key].hasOwnProperty('value')) {
                            document.getElementById(key).value = res.additional[key]['value'];
                        }

                        modified.push(
                            [key, '.classList', '.remove(', "'is-invalid')"],
                            [key+'_error', '.classList', '.add(', "'d-none')"],
                            [key+'_error', '.innerHTML = ', "''"],
                        )
                    }
                }
            }
        }, error: function (jqXHR) {
            let res = jqXHR.responseJSON;
            let message = '';

            for (let key in res.errors) {
                message = res.errors[key];
                document.getElementById(key).classList.add('is-invalid');
                document.getElementById(key+'_error').classList.remove('d-none');
                document.getElementById(key+'_error').innerHTML = res.errors[key];

                modified.push(
                    [key, '.classList', '.remove(', "'is-invalid')"],
                    [key+'_error', '.classList', '.add(', "'d-none')"],
                    [key+'_error', '.innerHTML = ', "''"],
                )
            };

            iziToast.error({
                title: 'Error',
                message: 'Terjadi kesalahan.',
                position: 'topCenter'
            });
        }
    });
}

</script>

<script type="text/javascript">
    function submitForm3(e, evt) {
    evt.preventDefault();

    clearModified();

    $.ajax({
        url: e.getAttribute('action'),
        type: e.getAttribute('method'),
        data: new FormData(e),
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.status == 200) {
                $('.modal').modal('hide');
                Closemodaldef();

                // if (res.redirect != '') {
                //     if (res.redirect != 'reload') {
                //         location.href = res.redirect;
                //     } else {
                //         location.reload();
                //     }
                // }

                // Swal.fire({
                //     icon: 'success',
                //     title: res.message2,
                //     text: res.message,
                //     showCancelButton: false,
                //     showConfirmButton: true,
                //     confirmButtonText: 'Oke',
                //     timer: 500,
                //     timerProgressBar: true
                // })

                // e.reset();

                if (document.getElementsByClassName('select2')) {
                    $(".select2").val('').trigger('change');
                }
            } else {
                for(let i = 0;i < res.errors; i++) {
                    document.getElementById(res.errors[i]).classList.add('is-invalid');
                    modified.push([res.errors[i], 'classList', 'remove(', "'is-invalid')"])
                }

                iziToast.error({
                    title: 'Error',
                    message: res.message,
                    position: 'topCenter'
                });
            }

            if (res.table != '') {
                $('#'+res.table).DataTable().ajax.reload();
            }

            if (Object.keys(res.additional).length > 0 ) {
                for (let key in res.additional) {
                    if (document.getElementById(key)) {
                        document.getElementById(key).classList.add('is-invalid');

                        if (res.additional[key].hasOwnProperty('message')) {
                            document.getElementById(key+'_error').classList.remove('d-none');
                            document.getElementById(key+'_error').innerHTML = res.additional[key]['message'];
                        }

                        if (res.additional[key].hasOwnProperty('value')) {
                            document.getElementById(key).value = res.additional[key]['value'];
                        }

                        modified.push(
                            [key, '.classList', '.remove(', "'is-invalid')"],
                            [key+'_error', '.classList', '.add(', "'d-none')"],
                            [key+'_error', '.innerHTML = ', "''"],
                        )
                    }
                }
            }
        }, error: function (jqXHR) {
            let res = jqXHR.responseJSON;
            let message = '';

            for (let key in res.errors) {
                message = res.errors[key];
                document.getElementById(key).classList.add('is-invalid');
                document.getElementById(key+'_error').classList.remove('d-none');
                document.getElementById(key+'_error').innerHTML = res.errors[key];

                modified.push(
                    [key, '.classList', '.remove(', "'is-invalid')"],
                    [key+'_error', '.classList', '.add(', "'d-none')"],
                    [key+'_error', '.innerHTML = ', "''"],
                )
            };

            iziToast.error({
                title: 'Error',
                message: 'Terjadi kesalahan.',
                position: 'topCenter'
            });
        }
    });
}

</script>

<script type="text/javascript">
    function submitForm4(e, evt) {
    evt.preventDefault();

    clearModified();

    $.ajax({
        url: e.getAttribute('action'),
        type: e.getAttribute('method'),
        data: new FormData(e),
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.status == 200) {
                $('.modal').modal('hide');
                getlistdata();
                getlistdsum();
                $('#txt_kode_qc').val('');
                $('#txt_upto3').val('');
                $('#txt_over3').val('');
                $('#txt_over6').val('');
                $('#txt_over9').val('');
                $('#txt_lebar1').val('');
                $('#txt_lebar2').val('');
                $('#txt_barcode_').val('');
                // $('#txt_unitbar').val('yard');
                $('#txt_akt_lenght_').val('');
                // $('#txt_unitakt_lenght').val('yard');
                // $('#txt_unitbar').text('Yard');
                // $('#txt_unitakt_lenght').text('Yard');


                $('#txt_no_roll').val('');
                $('#txt_berat').val('');
                $('#txt_unit').val('');
                $('#txt_berat_2').val('');
                $('#txt_lebar').val('');
                $('#txt_fab_supp').val('');
                $('#txt_inspektor').val('');
                $('#txt_no_mesin').val('');
                $('#txt_aktual').val('');
                $('#txt_gramasi').val('');
                $('#txt_barcode').val('');
                $('#txt_akt_lenght').val('');
                $('#txt_remark').val('');
                $('#txt_no_roll2').val('');

                // location.reload();
                getavgpoin();
                getpoin();
                getnoform();

                // if (res.redirect != '') {
                //     if (res.redirect != 'reload') {
                //         location.href = res.redirect;
                //     } else {
                //         location.reload();
                //     }
                // }

                // Swal.fire({
                //     icon: 'success',
                //     title: res.message2,
                //     text: res.message,
                //     showCancelButton: false,
                //     showConfirmButton: true,
                //     confirmButtonText: 'Oke',
                //     timer: 500,
                //     timerProgressBar: true
                // })

                // e.reset();

                if (document.getElementsByClassName('select2')) {
                    $(".select2").val('').trigger('change');
                }
            } else {
                for(let i = 0;i < res.errors; i++) {
                    document.getElementById(res.errors[i]).classList.add('is-invalid');
                    modified.push([res.errors[i], 'classList', 'remove(', "'is-invalid')"])
                }

                iziToast.error({
                    title: 'Error',
                    message: res.message,
                    position: 'topCenter'
                });
            }

            if (res.table != '') {
                $('#'+res.table).DataTable().ajax.reload();
            }

            if (Object.keys(res.additional).length > 0 ) {
                for (let key in res.additional) {
                    if (document.getElementById(key)) {
                        document.getElementById(key).classList.add('is-invalid');

                        if (res.additional[key].hasOwnProperty('message')) {
                            document.getElementById(key+'_error').classList.remove('d-none');
                            document.getElementById(key+'_error').innerHTML = res.additional[key]['message'];
                        }

                        if (res.additional[key].hasOwnProperty('value')) {
                            document.getElementById(key).value = res.additional[key]['value'];
                        }

                        modified.push(
                            [key, '.classList', '.remove(', "'is-invalid')"],
                            [key+'_error', '.classList', '.add(', "'d-none')"],
                            [key+'_error', '.innerHTML = ', "''"],
                        )
                    }
                }
            }
        }, error: function (jqXHR) {
            let res = jqXHR.responseJSON;
            let message = '';

            for (let key in res.errors) {
                message = res.errors[key];
                document.getElementById(key).classList.add('is-invalid');
                document.getElementById(key+'_error').classList.remove('d-none');
                document.getElementById(key+'_error').innerHTML = res.errors[key];

                modified.push(
                    [key, '.classList', '.remove(', "'is-invalid')"],
                    [key+'_error', '.classList', '.add(', "'d-none')"],
                    [key+'_error', '.innerHTML = ', "''"],
                )
            };

            iziToast.error({
                title: 'Error',
                message: 'Terjadi kesalahan.',
                position: 'topCenter'
            });
        }
    });
}

</script>

    <script type="text/javascript">
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
                    let breakDecodedText = decodedText.split('-');

                    document.getElementById('txt_id_item').value = breakDecodedText[0];
                    getdataitem(breakDecodedText[0]);

                    // getScannedItem(breakDecodedText[0]);

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
                            width: 250,
                            height: 250
                        }
                    },
                    /* verbose= */
                    false);

                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            }
        }
</script>
<script type="text/javascript">
    function getTypeqc(){
        let noform = $('#txt_no_form').val();
        let panjang = $('#txt_panjang').val();

        $('#def_noform').val(noform);
        $('#def_lenght').val(panjang);
        document.getElementById("pilih_def1").checked = false;
        document.getElementById("pilih_def2").checked = false;
        document.getElementById("pilih_def3").checked = false;
        document.getElementById("pilih_def4").checked = false;
        document.getElementById("pilih_def5").checked = false;
        document.getElementById("pilih_def6").checked = false;
        document.getElementById("pilih_def7").checked = false;
        document.getElementById("pilih_def8").checked = false;
        document.getElementById("pilih_def9").checked = false;
        document.getElementById("pilih_def10").checked = false;
        document.getElementById("pilih_def11").checked = false;
        document.getElementById("pilih_def12").checked = false;
        document.getElementById("pilih_def13").checked = false;
        document.getElementById("pilih_def14").checked = false;
        document.getElementById("pilih_def15").checked = false;
        document.getElementById("pilih_def16").checked = false;
        document.getElementById("pilih_def17").checked = false;
        document.getElementById("pilih_def18").checked = false;
        document.getElementById("pilih_def19").checked = false;
        document.getElementById("pilih_def20").checked = false;

        $('#modal-type-qc').modal('show');
    }

    function Closemodaldef(){
        $('#modal-type-qc').modal('hide');
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-defect") }}',
                type: 'get',
                data: {
                },
                success: function (res) {
                    if (res) {
                        $('#txt_kode_qc').val(res[0].kode_def);
                    }
                },
            });
    }


    function getnoform(){
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-no-form") }}',
                type: 'get',
                data: {
                },
                success: function (res) {
                    if (res) {
                        $('#txt_no_form').val(res[0].kode);
                    }
                },
            });
    }
</script>
<script type="text/javascript">
    function submitModal(e, evt) {
    evt.preventDefault();

    clearModified();

    $.ajax({
        url: e.getAttribute('action'),
        type: e.getAttribute('method'),
        data: new FormData(e),
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.status == 200) {
                $('.modal').modal('hide');

                // if (res.redirect != '') {
                //     if (res.redirect != 'reload') {
                //         location.href = res.redirect;
                //     } else {
                //         location.reload();
                //     }
                // }

                Swal.fire({
                    icon: 'success',
                    title: res.message2,
                    text: res.message,
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                    timer: 5000,
                    timerProgressBar: true
                }).then((result)=>{
                    if (res.redirect != '') {
                    if (res.redirect != 'reload') {
                        location.href = res.redirect;
                    } else {
                        location.reload();
                    }
                }
                })

                e.reset();

                if (document.getElementsByClassName('select2')) {
                    $(".select2").val('').trigger('change');
                }
            } else {
                for(let i = 0;i < res.errors; i++) {
                    document.getElementById(res.errors[i]).classList.add('is-invalid');
                    modified.push([res.errors[i], 'classList', 'remove(', "'is-invalid')"])
                }

                iziToast.error({
                    title: 'Error',
                    message: res.message,
                    position: 'topCenter'
                });
            }

            if (res.table != '') {
                $('#'+res.table).DataTable().ajax.reload();
            }

            if (Object.keys(res.additional).length > 0 ) {
                for (let key in res.additional) {
                    if (document.getElementById(key)) {
                        document.getElementById(key).classList.add('is-invalid');

                        if (res.additional[key].hasOwnProperty('message')) {
                            document.getElementById(key+'_error').classList.remove('d-none');
                            document.getElementById(key+'_error').innerHTML = res.additional[key]['message'];
                        }

                        if (res.additional[key].hasOwnProperty('value')) {
                            document.getElementById(key).value = res.additional[key]['value'];
                        }

                        modified.push(
                            [key, '.classList', '.remove(', "'is-invalid')"],
                            [key+'_error', '.classList', '.add(', "'d-none')"],
                            [key+'_error', '.innerHTML = ', "''"],
                        )
                    }
                }
            }
        }, error: function (jqXHR) {
            let res = jqXHR.responseJSON;
            let message = '';

            for (let key in res.errors) {
                message = res.errors[key];
                document.getElementById(key).classList.add('is-invalid');
                document.getElementById(key+'_error').classList.remove('d-none');
                document.getElementById(key+'_error').innerHTML = res.errors[key];

                modified.push(
                    [key, '.classList', '.remove(', "'is-invalid')"],
                    [key+'_error', '.classList', '.add(', "'d-none')"],
                    [key+'_error', '.innerHTML = ', "''"],
                )
            };

            iziToast.error({
                title: 'Error',
                message: 'Terjadi kesalahan.',
                position: 'topCenter'
            });
        }
    });
}
</script>
<script type="text/javascript">
    function finishdata(){
        var poin = $('#avgpoin').val();
        if (poin > 15) {
            $('#modal-finis-data').modal('show');
        }else{
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("finish-data") }}',
                type: 'get',
                data: {
                    no_insp: $('#txt_noinsp').val(),
                },
                dataType: 'json',
                success: function (res) {
                    if (res.status == 200) {
                         Swal.fire({
                    icon: 'success',
                    title: res.message2,
                    text: res.message,
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                    timer: 5000,
                    timerProgressBar: true
                }).then((result)=>{
                    if (res.redirect != '') {
                    if (res.redirect != 'reload') {
                        location.href = res.redirect;
                    } else {
                        location.reload();
                    }
                }
                })

                    }
                },
            });
        }
    }
</script>
    <script>

        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        //Initialize Select2 Elements
        $('.select2').select2()

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        $('.select2roll').select2({
            theme: 'bootstrap4'
        })

        $('.select2supp').select2({
            theme: 'bootstrap4'
        })

        $("#color").prop("disabled", true);
        $("#panel").prop("disabled", true);
        $('#p_unit').val("yard").trigger('change');

        //Reset Form
        if (document.getElementById('store-inmaterial')) {
            document.getElementById('store-inmaterial').reset();
        }

        $('#ws_id').on('change', async function(e) {
            await updateColorList();
            await updateOrderInfo();
        });

        $('#color').on('change', async function(e) {
            await updatePanelList();
            await updateSizeList();
        });

        $('#panel').on('change', async function(e) {
            await getMarkerCount();
            await getNumber();
            await updateSizeList();
        });

        $('#p_unit').on('change', async function(e) {
            let unit = $('#p_unit').val();
            if (unit == 'yard') {
                $('#comma_unit').val('INCH');
                $('#l_unit').val('inch').trigger("change");
            } else if (unit == 'meter') {
                $('#comma_unit').val('CM');
                $('#l_unit').val('cm').trigger("change");
            }
        });

        function updateOrderInfo() {
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-marker-order") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color').val(),
                },
                dataType: 'json',
                success: function (res) {
                    if (res) {
                        document.getElementById('ws').value = res.kpno;
                        document.getElementById('buyer').value = res.buyer;
                        document.getElementById('style').value = res.styleno;
                    }
                },
            });
        }

        function savetempdata(){
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("store-qcdet-temp") }}',
                type: 'post',
                data: {
                    txt_noinsp: $('#txt_noinsp').val(),
                    txt_no_form: $('#txt_no_form').val(),
                    txt_tgl_form: $('#txt_tgl_form').val(),
                    txt_no_roll: $('#txt_no_roll').val(),
                    txt_berat: $('#txt_berat').val(),
                    txt_lebar: $('#txt_lebar').val(),
                    txt_fab_supp: $('#txt_fab_supp').val(),
                    txt_inspektor: $('#txt_inspektor').val(),
                    txt_no_mesin: $('#txt_no_mesin').val(),
                    txt_aktual: $('#txt_aktual').val(),
                    txt_gramasi: $('#txt_gramasi').val(),
                    txt_panjang: $('#txt_panjang').val(),
                    txt_kode_qc: $('#txt_kode_qc').val(),
                    txt_upto3: $('#txt_upto3').val(),
                    txt_over3: $('#txt_over3').val(),
                    txt_over6: $('#txt_over6').val(),
                    txt_over9: $('#txt_over9').val(),
                    txt_lebar1: $('#txt_lebar1').val(),
                    txt_lebar2: $('#txt_lebar2').val(),
                },
                dataType: 'json',
                success: function (res) {
                    if (res) {
                        // document.getElementById('ws').value = res.kpno;
                        // document.getElementById('buyer').value = res.buyer;
                        // document.getElementById('style').value = res.styleno;
                    }
                },
            });
        }

        function updateColorList() {
            document.getElementById('color').value = null;
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-marker-colors") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('color').innerHTML = res;
                        document.getElementById('panel').innerHTML = null;
                        document.getElementById('panel').value = null;

                        $("#color").prop("disabled", false);
                        $("#panel").prop("disabled", true);

                        // input text
                        document.getElementById('no_urut_marker').value = null;
                        document.getElementById('cons_ws').value = null;
                        document.getElementById('order_qty').value = null;
                    }
                },
            });
        }

        function updatePanelList() {
            document.getElementById('panel').value = null;
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-marker-panels") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color').val(),
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('panel').innerHTML = res;
                        $("#panel").prop("disabled", false);

                        // input text
                        document.getElementById('no_urut_marker').value = null;
                        document.getElementById('cons_ws').value = null;
                        document.getElementById('order_qty').value = null;
                    }
                },
            });
        }


        function settype(){
            let type = $('#txt_type_gr').val();
            $("#txt_wsglobal").prop("disabled", false);
            $("#txt_po").prop("disabled", false);
            if (type == 'FOB') {

                $("#txt_wsglobal").prop("disabled", true);
                $("#txt_wsglobal").val('');
                $("#txt_wsglobal").text('');
                getPO();

            }else if(type == 'CMT'){
                // $("#txt_po").prop("disabled", true);
                // $("#txt_po").val('');
                // $("#txt_po").text('');
                getWS();
                getPO();
            }else{
            }
        }


        function getPO() {
           return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-po-list") }}',
                type: 'get',
                data: {
                    txt_supp: $('#txt_supp').val(),
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('txt_po').innerHTML = res;
                    }
                },
            });
        }


        function getWS() {
           return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-ws-list") }}',
                type: 'get',
                data: {
                    txt_supp: $('#txt_supp').val(),
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('txt_wsglobal').innerHTML = res;
                    }
                },
            });
        }

        function getavgpoin() {
           return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-avg-poin") }}',
                type: 'get',
                data: {
                    no_insp: $('#txt_noinsp').val(),
                    akt_lenght: $('#txt_akt_lenght').val(),
                },
                success: function (res) {
                    if (res) {
                        console.log(res);
                        document.getElementById('avg_poin').innerHTML = res;
                        document.getElementById('avgpoin').val = res[0].poin;
                    }
                },
            });
        }

        function getpoin() {
           return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-poin") }}',
                type: 'get',
                data: {
                    no_insp: $('#txt_noinsp').val(),
                },
                success: function (res) {
                    if (res) {
                        console.log(res[0].poin);
                         $('#avgpoin').val(res[0].poin);
                    }
                },
            });
        }

        function deleteqc(id) {
           return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("delete-qc-temp") }}',
                type: 'get',
                data: {
                    id_temp: id,
                },
                success: function (res) {
                    if (res.status = '200') {
                        return datatable.ajax.reload(() => {
            });
                    }
                },
            });
        }

        function deleteqcdet(id) {
           return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("delete-qc-det") }}',
                type: 'get',
                data: {
                    id_temp: id,
                },
                success: function (res) {
                    if (res.status = '200') {
                        getavgpoin();
                        getpoin();
                        getlistdsum();
                        return datatable2.ajax.reload(() => {
            });
                    }
                },
            });
        }


        // function getlistdata(val){
        //     datatable.ajax.reload();
        // }

        async function getlistdata() {
            return datatable.ajax.reload(() => {
            });
        }
        async function getlistdsum() {
            // getavgpoin();
            // getpoin();
            return datatable2.ajax.reload(() => {
            });
        }


        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: false,
            ajax: {
                url: '{{ route("get-detail-defect") }}',
                data: function (d) {
                    // alert(d.name_fill);
                },
            },
            columns: [
                {
                    data: 'lenght_fabric'
                },
                {
                    data: 'kode_def'
                } ,
                {
                    data: 'upto3'
                },
                {
                    data: 'over3'
                },
                {
                    data: 'over6'
                },
                {
                    data: 'over9'
                },
                {
                    data: 'width_det'
                },
                {
                    data: 'id'
                }
            ],
            columnDefs: [
            {
                targets: [7],
                render: (data, type, row, meta) => {
                    console.log(data);
                    return `<div class='d-flex gap-1 justify-content-center'>
                    <button type='button' class='btn btn-sm btn-danger' onclick='deleteqc("` + row.id + `")'><i class='fa fa-trash'></i></button>
                     </div>`;
                }
            }

            ]
        });

        let datatable2 = $("#datatable2").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: false,
            ajax: {
                url: '{{ route("get-sum-data") }}',
                data: function (d) {
                    d.akt_lenght = $('#txt_akt_lenght').val();
                    d.no_insp = $('#txt_noinsp').val();
                    // d.txt_fill = $('#txt_po').val() ? $('#txt_po').val() : $('#txt_wsglobal').val();
                    // d.name_fill = $('#txt_po').val() ? 'PO' : 'WS';
                    // // alert(d.name_fill);
                },
            },
            columns: [
                {
                    data: 'no_roll'
                },
                {
                    data: 'upto3'
                },
                {
                    data: 'over3'
                } ,
                {
                    data: 'over6'
                },
                {
                    data: 'over9'
                },
                {
                    data: 'width_fabric'
                },
                {
                    data: 'ttl_poin'
                },
                {
                    data: 'akt_poin'
                },
                {
                    data: 'status'
                },
                {
                    data: 'no_form'
                }
            ],
            columnDefs: [
            {
                targets: [7],
                render: (data, type, row, meta) => data ? data.round(2) : "-"
            },
            {
                targets: [9],
                render: (data, type, row, meta) => {
                    console.log(row.stat_save);
                    if (row.stat_save == 'save') {
                    return `<div class='d-flex gap-1 justify-content-center'>
                    <button type='button' class='btn btn-sm btn-danger' onclick='deleteqcdet("` + row.no_form + `")'><i class='fa fa-trash'></i></button>
                     </div>`;
                    }else{
                        return `<div class='d-flex gap-1 justify-content-center'>
                     </div>`;

                    }
                }
            }

            ]
        });

        function tambahqty($val){
            var table = document.getElementById("datatable");
            var qty = 0;
            var jml_qty = 0;

            for (var i = 1; i < (table.rows.length); i++) {
                qty = document.getElementById("datatable").rows[i].cells[9].children[0].value || 0;
                jml_qty += parseFloat(qty) ;
            }

            $('#jumlah_qty').val(jml_qty);

        }

        // function calculateRatio(id) {
        //     let ratio = document.getElementById('ratio-'+id).value;
        //     let gelarQty = document.getElementById('gelar_marker_qty').value;
        //     document.getElementById('cut-qty-'+id).value = ratio * gelarQty;
        // }

        // function calculateAllRatio(element) {
        //     let gelarQty = element.value;

        //     for (let i = 0; i < datatable.data().count(); i++) {
        //         let ratio = document.getElementById('ratio-'+i).value;
        //         document.getElementById('cut-qty-'+i).value = ratio * gelarQty;
        //     }
        // }

        // document.getElementById("store-marker").onkeypress = function(e) {
        //     var key = e.charCode || e.keyCode || 0;
        //     if (key == 13) {
        //         e.preventDefault();
        //     }
        // }

        function submitLokasiForm(e, evt) {
            evt.preventDefault();

            clearModified();

            $.ajax({
                url: e.getAttribute('action'),
                type: e.getAttribute('method'),
                data: new FormData(e),
                processData: false,
                contentType: false,
                success: async function(res) {
                    if (res.status == 200) {
                        console.log(res);

                        e.reset();

                        // $('#cbows').val("").trigger("change");
                        // $("#cbomarker").prop("disabled", true);

                        Swal.fire({
                            icon: 'success',
                            title: 'Data Spreading berhasil disimpan',
                            html: "No. Form Cut : <br>" + res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        })

                        datatable.ajax.reload();
                    }
                },

            });
        }

        function cariitem() {
        // Declare variables
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("cari_item");
        filter = input.value.toUpperCase();
        table = document.getElementById("datatable");
        tr = table.getElementsByTagName("tr");

        // Loop through all table rows, and hide those who don't match the search query
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[5]; //kolom ke berapa
            if (td) {
                txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
    </script>
@endsection
