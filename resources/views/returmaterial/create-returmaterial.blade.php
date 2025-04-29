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
<form action="{{ route('store-returmaterial-fabric') }}" method="post" id="store-outmaterial" onsubmit="submitForm(this, event)">
    @csrf
    <div class="card card-sb card-outline">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                Data Header
            </h5>
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
                <label><small>No RO</small></label>
                @foreach ($kode_gr as $kodegr)
                <input type="text" class="form-control " id="txt_noro" name="txt_noro" value="{{ $kodegr->kode }}" readonly>
                @endforeach
                </div>
            </div>
            </div>

            <div class="col-md-6">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl RO</small></label>
                <input type="date" class="form-control form-control" id="txt_tgl_ro" name="txt_tgl_ro"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div>

            <div class="col-md-6">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl BPB</small></label>
                <input type="date" class="form-control form-control" id="txt_tgl_bpb" name="txt_tgl_bpb"
                        value="" onchange="get_nobpb(this.value)">
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No BPB</small></label>
                <select class="form-control select2req" id="txt_nobpb" name="txt_nobpb" style="width: 100%;" onchange="getlistdata();getSupp()">
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Jenis Defect</small></label>
                <select class="form-control select2bs4" id="txt_jns_def" name="txt_jns_def" style="width: 100%;">
                    <option selected="selected" value="">Pilih Defect</option>
                        @foreach ($def_type as $def)
                    <option value="{{ $def->nama_defect }}">
                                {{ $def->nama_defect }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Jenis Pengeluaran</small></label>
                <select class="form-control select2bs4" id="txt_jns_klr" name="txt_jns_klr" style="width: 100%;">
                    <option selected="selected" value="">Pilih Pengeluaran</option>
                        @foreach ($jns_klr as $jnsklr)
                    <option value="{{ $jnsklr->isi }}">
                                {{ $jnsklr->tampil }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

        </div>
    </div>

    <div class="col-md-3">
        <div class="row">

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No PO</small></label>
                <input type="text" class="form-control " id="txt_nopo" name="txt_nopo" value="" readonly>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Dikirim Ke</small></label>
                <input type="text" class="form-control " id="txt_dikirim" name="txt_dikirim" value="" readonly>
                <input type="hidden" class="form-control " id="txt_idsupp" name="txt_idsupp" value="" readonly>
                <input type="hidden" class="form-control " id="txt_nows" name="txt_nows" value="" readonly>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tipe BC</small></label>
                <select class="form-control select2bs4" id="txt_type_bc" name="txt_type_bc" style="width: 100%;" onchange="get_tujuan(this.value)">
                    <option selected="selected" value="">Pilih Tipe</option>
                        @foreach ($mtypebc as $bc)
                    <option value="{{ $bc->nama_pilihan }}">
                                {{ $bc->nama_pilihan }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tujuan Pemasukan</small></label>
                <select class="form-control select2bs4" id="txt_tujuan" name="txt_tujuan" style="width: 100%;">
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Jenis Return</small></label>
                <select class="form-control select2bs4" id="txt_stat_rtn" name="txt_stat_rtn" style="width: 100%;">
                    <option selected="selected" value="">Pilih Status Retur</option>
                        @foreach ($status_replac as $replac)
                    <option value="{{ $replac->nama_pilihan }}">
                                {{ $replac->nama_pilihan }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

        </div>
    </div>

    <div class="col-md-5">
        <div class="row">

            <div class="col-md-7">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No Aju</small></label>
                <input type="text" class="form-control " id="txt_no_aju" name="txt_no_aju" value="" >
                </div>
            </div>
            </div>

            <div class="col-md-5">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl Aju</small></label>
                <input type="date" class="form-control form-control" id="txt_tgl_aju" name="txt_tgl_aju"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div>

            <div class="col-md-7">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No Daftar</small></label>
                <input type="text" class="form-control " id="txt_no_daftar" name="txt_no_daftar" value="" >
                </div>
            </div>
            </div>

            <div class="col-md-5">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl Daftar</small></label>
                <input type="date" class="form-control form-control" id="txt_tgl_daftar" name="txt_tgl_daftar"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Catatan</small></label>
                <textarea type="text" rows="4" class="form-control " id="txt_notes" name="txt_notes" value="" > </textarea>
                <input type="hidden" class="form-control" id="jumlah_data" name="jumlah_data" readonly>
                <input type="hidden" class="form-control" id="jumlah_qty" name="jumlah_qty" readonly>
                </div>
            </div>
            </div>
        </div>
    </div>
    </div>
</div>
</div>

    <div class="card card-sb card-outline">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                Data Detail
            </h5>
        </div>
    <div class="card-body">
    <div class="form-group row">
        <div class="d-flex justify-content-between">
            <div class="ml-auto">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
                <input type="text"  id="cari_item" name="cari_item" autocomplete="off" placeholder="Search Item..." onkeyup="cariitem()">
        </div>
    <div class="table-responsive"style="max-height: 500px">
            <table id="datatable" class="table table-bordered table-head-fixed table-striped 100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">No JO</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">No WS</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Kode Barang</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Deskripsi</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Qty BPB</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Satuan</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Stok</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Qty RO</th>
                        <th class="text-center" style="display: none;">Keterangan</th>
                        <th class="text-center" style="display: none;">Keterangan</th>
                        <th class="text-center" style="display: none;">Keterangan</th>
                        <th class="text-center" style="display: none;">Keterangan</th>
                       <!--  <th class="text-center" style="display: none;">Lokasi</th>
                        <th class="text-center" style="display: none;">Lokasi</th>
                        <th class="text-center" style="display: none;">Lokasi</th> -->
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
            <div class="mb-1">
                <div class="form-group">
                    <button class="btn btn-sb float-end mt-2 ml-2"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
                    <a href="{{ route('out-material') }}" class="btn btn-danger float-end mt-2">
                    <i class="fas fa-arrow-circle-left"></i> Kembali</a>
                </div>
            </div>
        </div>
        </div>
    </div>
</form>

<div class="modal fade" id="modal-out-manual">
    <form action="{{ route('save-out-manual-ro') }}" method="post" onsubmit="submitFormScan(this, event)">
         @method('POST')
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h4 class="modal-title">List Item</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <div class="modal-body">
                <div class="form-group row">

                    <div class="col-md-12">
                        <input type="hidden" class="form-control " id="m_no_bppb" name="m_no_bppb" value="" readonly>
                        <input type="hidden" class="form-control " id="m_tgl_bppb" name="m_tgl_bppb" value="" readonly>
                        <input type="hidden" class="form-control " id="t_roll" name="t_roll" value="" readonly>
                    <div class="row">
                        <div class="col-4 col-md-4">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Qty Request</small></label>
                                <input type="text" class="form-control " id="m_qty_req" name="m_qty_req" value="" readonly>
                                <input type="hidden" class="form-control " id="m_qty_req_h" name="m_qty_req_h" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-4 col-md-4">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Qty Out</small></label>
                                <input type="text" class="form-control " id="m_qty_out" name="m_qty_out" value="" readonly>
                                <input type="hidden" class="form-control " id="m_qty_out_h" name="m_qty_out_h" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-4 col-md-4">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Qty Balance</small></label>
                                <input type="text" class="form-control " id="m_qty_bal" name="m_qty_bal" value="" readonly>
                                <input type="hidden" class="form-control " id="m_qty_bal_h" name="m_qty_bal_h" value="" readonly>
                        </div>
                        </div>
                        </div>
                    </div>
                    </div>

                    <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12" id="detail_showitem">
                        </div>
                    </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Tutup</button>
                    <button type="submit" class="btn btn-primary toastsDefaultDanger"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Simpan</button>
                </div>

            </div>
        </div>
    </form>
</div>


<div class="modal fade" id="modal-out-barcode">
    <form action="{{ route('save-out-scan-ro') }}" method="post" onsubmit="submitFormScan(this, event)">
         @method('POST')
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h4 class="modal-title">List Item</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <div class="modal-body">
                <div class="form-group row">
                    <div class="row">
                        <div class="col-4 col-md-4">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Qty Request</small></label>
                                <input type="text" class="form-control " id="m_qty_req2" name="m_qty_req2" value="" readonly>
                                <input type="hidden" class="form-control " id="m_qty_req_h2" name="m_qty_req_h2" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-4 col-md-4">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Qty Out</small></label>
                                <input type="text" class="form-control " id="m_qty_out2" name="m_qty_out2" value="" readonly>
                                <input type="hidden" class="form-control " id="m_qty_out_h2" name="m_qty_out_h2" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-4 col-md-4">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Qty Balance</small></label>
                                <input type="text" class="form-control " id="m_qty_bal2" name="m_qty_bal2" value="" readonly>
                                <input type="hidden" class="form-control " id="m_qty_bal_h2" name="m_qty_bal_h2" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-md-12">
                            <input type="hidden" class="form-control " id="m_no_bppb2" name="m_no_bppb2" value="" readonly>
                            <input type="hidden" class="form-control " id="m_tgl_bppb2" name="m_tgl_bppb2" value="" readonly>
                        <input type="hidden" class="form-control " id="t_roll2" name="t_roll2" value="" readonly>
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Scan Barcode</small></label>
                            <select class='form-control select2barcode' multiple='multiple' style='width: 100%;height: 20px;' name='txt_barcode' id='txt_barcode' onchange='getdatabarcode(this.value)'>
                            </select>
                        </div>
                        </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12" id="detail_showbarcode">
                        </div>
                    </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Tutup</button>
                    <button type="submit" class="btn btn-primary toastsDefaultDanger"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Simpan</button>
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
    <script>

        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        $(document).on('select2req:open', () => {
            document.querySelector('.select2req-search__field').focus();
        });

        //Initialize Select2 Elements
        $('.select2').select2()

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        $('.select2barcode').select2({
            theme: 'bootstrap4'
        })

        $('.select2req').select2({
            theme: 'bootstrap4'
        })

        $("#color").prop("disabled", true);
        $("#panel").prop("disabled", true);
        $('#p_unit').val("yard").trigger('change');

        //Reset Form
        if (document.getElementById('store-outmaterial')) {
            document.getElementById('store-outmaterial').reset();
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


        function get_tujuan(val) {
           return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-tujuan-pemasukan-ro") }}',
                type: 'get',
                data: {
                    type_bc: $('#txt_type_bc').val(),
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('txt_tujuan').innerHTML = res;
                    }
                },
            });
        }


        function get_nobpb(val) {
           return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-no-bpb") }}',
                type: 'get',
                data: {
                    tgl_bpb: $('#txt_tgl_bpb').val(),
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('txt_nobpb').innerHTML = res;
                    }
                },
            });
        }


        function getSupp() {
           return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-supplier-ro") }}',
                type: 'get',
                data: {
                    no_bpb: $('#txt_nobpb').val(),
                },
                success: function (res) {
                    if (res) {
                        // console.log(res[0].jml)
                        $('#txt_nopo').val(res[0].pono);
                        $('#txt_dikirim').val(res[0].supplier);
                        $('#txt_idsupp').val(res[0].id_supplier);
                        $('#txt_nows').val(res[0].kpno);
                    }
                },
            });
        }

        // Form Submit
function submitFormScan(e, evt) {
    $("input[type=submit][clicked=true]").attr('disabled', true);

    evt.preventDefault();

    clearModified();

    $.ajax({
        url: e.getAttribute('action'),
        type: e.getAttribute('method'),
        data: new FormData(e),
        processData: false,
        contentType: false,
        success: function(res) {
            $("input[type=submit][clicked=true]").removeAttr('disabled');

            if (res.status == 200) {
                $('.modal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: res.message,
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                    timer: 5000,
                    timerProgressBar: true
                }).then(() => {
                    if (res.redirect != '') {
                        if (res.redirect != 'reload') {
                            location.href = res.redirect;
                        } else {
                            location.reload();
                        }
                    }
                    getlistdata();
                });

                e.reset();

                if (res.callback != '') {
                    eval(res.callback);
                }

                if (document.getElementsByClassName('select2')) {
                    $(".select2").val('').trigger('change');
                }
            }
          else if (res.status == 300) {
            $('.modal').modal('hide');

            iziToast.success({
                title: 'success',
                message: res.message,
                position: 'topCenter'
            });

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
            $("input[type=submit][clicked=true]").removeAttr('disabled');

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



        function sum_qty_item(val){
            var table = document.getElementById("tableshow");
            var qty_stok = 0;
            var satuan = '';
            var qty_out = 0;
            var qty = 0;
            var sisa_qty = 0;
            var nol = 0;
            var qty_req = $('#m_qty_req_h').val();
            var h_qty_out = '';
            var h_sum_bal = '';
            var sum_bal = 0;
            var sum_out = 0;

            for (let i = 1; i < (table.rows.length); i++) {
                var cek =  document.getElementById("pil_item"+i);
                satuan = document.getElementById("tableshow").rows[i].cells[5].children[0].value;
                qty_stok = document.getElementById("qty_stok"+i).value || 0;
                qty_out = document.getElementById("qty_out"+i).value || 0;
                sisa_qty = parseFloat(qty_stok) - parseFloat(qty_out) ;

                if (cek.checked == true && qty_out > 0) {
                    if (parseFloat(qty_out) > parseFloat(qty_stok)) {
                        $('#qty_out'+i).val(qty_stok);
                        $('#qty_sisa'+i).val(nol);
                    }else{
                        $('#qty_out'+i).val(qty_out);
                        $('#qty_sisa'+i).val(sisa_qty.round(2) || 0);
                    }
                    sum_out += parseFloat(qty_out);
                }
            }
                h_qty_out = sum_out + ' ' + satuan;
                sum_bal = parseFloat(qty_req) - parseFloat(sum_out);
                h_sum_bal = sum_bal + ' ' + satuan;
                $('#m_qty_out').val(h_qty_out.toFixed(2));
                $('#m_qty_out_h').val(sum_out.toFixed(2));
                $('#m_qty_bal').val(h_sum_bal.toFixed(2));
                $('#m_qty_bal_h').val(sum_bal.toFixed(2));

        }

        function sum_qty_barcode(val){
            var table = document.getElementById("tableshow");
            var qty_stok = 0;
            var satuan = '';
            var qty_out = 0;
            var qty = 0;
            var sisa_qty = 0;
            var nol = 0;
            var qty_req = $('#m_qty_req_h2').val();
            var h_qty_out = '';
            var h_sum_bal = '';
            var sum_bal = 0;
            var sum_out = 0;

            for (let i = 1; i < (table.rows.length); i++) {
                satuan = document.getElementById("tableshow").rows[i].cells[6].children[0].value;
                qty_stok = document.getElementById("qty_stok"+i).value || 0;
                qty_out = document.getElementById("qty_out"+i).value || 0;
                sisa_qty = parseFloat(qty_stok) - parseFloat(qty_out) ;
                // alert(sisa_qty);

                if ( qty_out > 0) {
                    if (parseFloat(qty_out) > parseFloat(qty_stok)) {
                        $('#qty_out'+i).val(qty_stok);
                        $('#qty_sisa'+i).val(nol);
                    }else{
                        $('#qty_out'+i).val(qty_out);
                        $('#qty_sisa'+i).val(sisa_qty.round(2) || 0);
                    }
                    sum_out += parseFloat(qty_out);
                }
            }

                h_qty_out = sum_out + ' ' + satuan;
                sum_bal = parseFloat(qty_req) - parseFloat(sum_out);
                h_sum_bal = sum_bal + ' ' + satuan;
                $('#m_qty_out2').val(h_qty_out.toFixed(2));
                $('#m_qty_out_h2').val(sum_out.toFixed(2));
                $('#m_qty_bal2').val(h_sum_bal.toFixed(2));
                $('#m_qty_bal_h2').val(sum_bal.toFixed(2));

        }




        async function getlistdata() {
            return datatable.ajax.reload(() => {
                document.getElementById('jumlah_data').value = datatable.data().count();
            });
        }

        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: false,
            serverSide: false,
            paging: false,
            searching: false,
            ajax: {
                url: '{{ route("get-detail-bpb") }}',
                data: function (d) {
                    d.no_bpb = $('#txt_nobpb').val();

                },
            },
            columns: [
                {
                    data: 'jo_no'
                },
                {
                    data: 'no_ws'
                },
                {
                    data: 'goods_code'
                } ,
                {
                    data: 'itemdesc'
                },
                {
                    data: 'qty'
                },
                {
                    data: 'unit'
                },
                {
                    data: 'qty_stok'
                },
                {
                    data: 'qty_input'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'id_jo'
                },
                {
                    data: 'id_bpb'
                },
                {
                    data: 'no_ws'
                }
            ],
            columnDefs: [
                {
                    targets: [7],
                    // className: "d-none",
                    render: (data, type, row, meta) => '<input style="width:150px;text-align:right;" type="text" id="input_qty' + meta.row + '" name="input_qty['+meta.row+']" value="" />'
                },
                {
                    targets: [8],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="id_item' + meta.row + '" name="id_item['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [9],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="id_jo' + meta.row + '" name="id_jo['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [10],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="id_bpb' + meta.row + '" name="id_bpb['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [11],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="no_ws' + meta.row + '" name="no_ws['+meta.row+']" value="' + data + '" readonly />'
                }
                // {
                //     targets: [10],
                //     className: "d-none",
                //     render: (data, type, row, meta) => '<input style="width:80px;text-align:center;" type="text" id="id_item' + meta.row + '" name="id_item['+meta.row+']" value="' + data + '" readonly />'
                // },
                // {
                //     targets: [11],
                //     className: "d-none",
                //     render: (data, type, row, meta) => '<input style="width:80px;text-align:center;" type="text" id="qty_sdh_out' + meta.row + '" name="qty_sdh_out['+meta.row+']" value="' + data + '" readonly />'
                // },
                // {
                //     targets: [12],
                //     className: "d-none",
                //     render: (data, type, row, meta) => '<input style="width:80px;text-align:center;" type="text" id="unit' + meta.row + '" name="unit['+meta.row+']" value="' + data + '" readonly />'
                // }
                ]
        });

    function getdatabarcode(val){
        let id_barcode = $('#txt_barcode').val();
        let text1 = "'";
        let kodenya = text1.concat(id_barcode, "'");
        let kodebarcode = kodenya.toString();
        let barcode = kodebarcode.replace(/,/g,"','");
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-data-barcode-ro") }}',
                type: 'get',
                data: {
                    id_barcode: barcode,
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('detail_showbarcode').innerHTML = res;
                         sum_qty_barcode('1');
                    }
                }
            });

    }

    function out_scan($id_item,$id_jo,$qty_req,$unit,$noreq){
        let id_item = $id_item;
        let id_jo = $id_jo;
        let qty_req = $qty_req;
        let unit = $unit;
        let no_bppb = $('#txt_nobppb').val();
        let tgl_bppb = $('#txt_tgl_bppb').val();
        let noreq = $noreq;

        getlist_barcode(id_item,id_jo);

        // $('#m_qty_req').val(qty_req + ' ' + unit);
        document.getElementById('txt_barcode').innerHTML = '';
        document.getElementById('detail_showbarcode').innerHTML = '';
        $('#m_qty_req2').val(qty_req + ' ' + unit);
        $('#m_qty_req_h2').val(qty_req);
        $('#m_no_bppb2').val(no_bppb);
        $('#m_tgl_bppb2').val(tgl_bppb);
        $('#m_qty_out2').val('');
        $('#m_qty_out_h2').val('');
        $('#m_qty_bal2').val('');
        $('#m_qty_bal_h2').val('');

        $('#modal-out-barcode').modal('show');
    }

    function getlist_barcode($id_item,$id_jo){
        let iditem = $id_item;
        let idjo = $id_jo;
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-list-barcode-ro") }}',
                type: 'get',
                data: {
                    id_item: iditem,
                    id_jo: idjo,
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('txt_barcode').innerHTML = res;
                    }
                }
            });
    }

    function out_manual($id_item,$id_jo,$qty_req,$unit){
        let id_item = $id_item;
        let id_jo = $id_jo;
        let qty_req = $qty_req;
        let unit = $unit;
        let no_bppb = $('#txt_nobppb').val();
        let tgl_bppb = $('#txt_tgl_bppb').val();

        getlist_showitem(id_item,id_jo);

        $('#m_qty_req').val(qty_req + ' ' + unit);
        $('#m_qty_req_h').val(qty_req);
        $('#m_no_bppb').val(no_bppb);
        $('#m_tgl_bppb').val(tgl_bppb);
        $('#m_qty_out').val('');
        $('#m_qty_out_h').val('');
        $('#m_qty_bal').val('');
        $('#m_qty_bal_h').val('');

        $('#modal-out-manual').modal('show');
    }

    function getlist_showitem($id_item,$id_jo){
        let iditem = $id_item;
        let idjo = $id_jo;
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-detail-item-ro") }}',
                type: 'get',
                data: {
                    id_item: iditem,
                    id_jo: idjo,
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('detail_showitem').innerHTML = res;
                        $('#tableshow').dataTable({
                            "bFilter": false,
                        });
                    }
                }
            });
    }

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


        function enableinput(){
            var table = document.getElementById("tableshow");
            var t_roll = 0;

            for (let i = 1; i < (table.rows.length); i++) {
                var cek =  document.getElementById("pil_item"+i);

                if (cek.checked == true){
                t_roll += parseFloat(cek.value);
                $("#qty_out"+i).prop("disabled", false);
                }
            }
            $('#t_roll').val(t_roll);
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
