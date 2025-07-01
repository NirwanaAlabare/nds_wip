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
<form action="{{ route('update-mutlokasi') }}" method="post" id="store-mutlokasi" onsubmit="submitForm(this, event)">
   @method('GET')
    <div class="card card-sb">
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
        <div class="row">
            <div class="col-4 col-md-4">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No Mutasi</small></label>
                @foreach ($d_header as $dh)
                <input type="text" class="form-control " id="txt_no_mut" name="txt_no_mut" value="{{ $dh->kode }}" readonly>
                @endforeach
                </div>
            </div>
            </div>

            <div class="col-4 col-md-4">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl Mutasi</small></label>
                @foreach ($d_header as $dh)
                <input type="text date" class="form-control form-control" id="txt_tgl_mut" name="txt_tgl_mut" value="{{ $dh->tgl_mut }}" readonly>
                @endforeach
                </div>
            </div>
            </div>


            <div class="col-4 col-md-4">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Worksheet</small></label>
                @foreach ($d_header as $dh)
                <input type="text" class="form-control " id="txt_nows" name="txt_nows" value="{{ $dh->no_ws }}" readonly>
                @endforeach
                </div>
            </div>
            </div>

            <div class="col-4 col-md-4">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Rak Penyimpanan</small></label>
                 @foreach ($d_header as $dh)
                <input type="text" class="form-control " id="txt_rak" name="txt_rak" value="{{ $dh->rak_asal }}" readonly>
                @endforeach
                </div>
            </div>
            </div>

            <div class="col-8 col-md-8">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Note</small></label>
                 @foreach ($d_header as $dh)
                <input type="text" class="form-control " id="txt_note" name="txt_note" value="{{ $dh->deskripsi }}" readonly>
                @endforeach
                <input type="hidden" id="txt_jml_qty" name="txt_jml_qty" value="">
                @foreach ($sum_data as $sd)
                <input type="hidden" class="form-control " id="txt_sum_roll" name="txt_sum_roll" value="{{ $sd->jml }}" readonly>
                @endforeach
                </div>
            </div>
            </div>

            <div class="col-12 col-md-12">
            <div class="form-group row">
        <div class="d-flex justify-content-between">
            <div class="ml-auto">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
                <input type="text"  id="cari_item" name="cari_item" autocomplete="off" placeholder="Search Item..." onkeyup="cariitem()">
        </div>
    <div class="table-responsive"style="max-height: 300px">
            <table id="datatable" class="table table-bordered table-head-fixed table-striped w-100 text-nowrap" width="100%">
                <thead>
                    <tr>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">ID Item</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Kode Barang</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Deskripsi</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">WS</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">No BPB</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Lot</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Roll</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Qty</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Qty Mutasi</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Satuan</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Rak</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Rak Tujuan</th>
                        <th class="text-center" style="display: none;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i=1; ?>
                    @foreach ($det_data as $detdata)
                    <tr>
                        <td>{{$detdata->id_item}} <input type="hidden" id="id_item<?= $i; ?>" name="id_item[<?= $i; ?>]" value="{{$detdata->qty_mutasi}}" /></td>
                        <td>{{$detdata->kode_item}} <input type="hidden" id="kode_item<?= $i; ?>" name="kode_item[<?= $i; ?>]" value="{{$detdata->kode_item}}" /></td>
                        <td>{{$detdata->item_desc}} <input type="hidden" id="desk_item<?= $i; ?>" name="desk_item[<?= $i; ?>]" value="{{$detdata->item_desc}}" /></td>
                        <td>{{$detdata->no_ws}} <input type="hidden" id="nows<?= $i; ?>" name="nows[<?= $i; ?>]" value="{{$detdata->no_ws}}" /></td>
                        <td>{{$detdata->no_bpb}} <input type="hidden" id="no_bpb<?= $i; ?>" name="no_bpb[<?= $i; ?>]" value="{{$detdata->no_bpb}}" /></td>
                        <td>{{$detdata->no_lot}} <input type="hidden" id="lot_no<?= $i; ?>" name="lot_no[<?= $i; ?>]" value="{{$detdata->no_lot}}" /></td>
                        <td>{{$detdata->no_roll}} <input type="hidden" id="roll_no<?= $i; ?>" name="roll_no[<?= $i; ?>]" value="{{$detdata->no_roll}}" /></td>
                        <td>{{$detdata->qty_roll}} <input type="hidden" id="qty_roll<?= $i; ?>" name="qty_roll[<?= $i; ?>]" value="{{$detdata->qty_roll}}" /></td>
                        <td><input style="width:100px;" class="form-control-sm" type="text" min="0" max="{{$detdata->qty_roll}}" id="qty_mut<?= $i; ?>" name="qty_mut[<?= $i; ?>]" value="{{$detdata->qty_mutasi}}" onkeyup="sum_qty_mut(this.value)" /></td>
                        <td>{{$detdata->unit}} <input type="hidden" id="unit<?= $i; ?>" name="unit[<?= $i; ?>]" value="{{$detdata->unit}}" /></td>
                        <td>{{$detdata->rak_asal}} <input type="hidden" id="kode_rak<?= $i; ?>" name="kode_rak[<?= $i; ?>]" value="{{$detdata->rak_asal}}" /></td>
                        <td>
                            <select class="form-control select2rak" id="selectlok<?= $i; ?>" name="selectlok[<?= $i; ?>]" style="width: 150px;">
                            @foreach ($lokasi as $lok)
                            <?php $isSelected = '';
                                if($lok->kode_lok == $detdata->rak_tujuan ) {
                                $isSelected  = ' selected="selected"';
                                }else{
                                $isSelected = '';
                                }
                            ?>
                              <option value='{{$lok->kode_lok}}' <?= $isSelected; ?>>{{$lok->kode_lok}}</option>
                              @endforeach
                            </select>
                        </td>
                        <td hidden><input style="width:100px;" class="form-control-sm" type="text" id="id_det<?= $i; ?>" name="id_det[<?= $i; ?>]" value="{{$detdata->id}}" /></td>
                    </tr>
                    <?php $i++; ?>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
            <div class="mb-1">
                <div class="form-group">
                    <button class="btn btn-sb float-end mt-2 ml-2"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
                    <a href="{{ route('mutasi-lokasi') }}" class="btn btn-danger float-end mt-2">
                    <i class="fas fa-arrow-circle-left"></i> Kembali</a>
                </div>
            </div>
        </div>



    </div>
</div>
</div>

</form>
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

        //Initialize Select2 Elements
        $('.select2').select2()

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        $('.select2roll').select2({
            theme: 'bootstrap4'
        })

         $('.select2rak').select2({
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
                url: '{{ route("get-general-panels") }}',
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




        function getrak() {
            document.getElementById('txt_rak').innerHTML = '';
            document.getElementById('dataroll').innerHTML = '';
            document.getElementById('txt_sum_roll').value = 0;
           return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-rak-list") }}',
                type: 'get',
                data: {
                    no_ws: $('#txt_nows').val(),
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('txt_rak').innerHTML = res;
                    }
                },
            });
        }


        function getdataroll(val) {
            document.getElementById('dataroll').innerHTML = '';
            document.getElementById('txt_sum_roll').value =0;
            getsumdata(val);
           return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-list-roll") }}',
                type: 'get',
                data: {
                    rak: val,
                    no_ws: $('#txt_nows').val(),
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('dataroll').innerHTML = res;
                        $('.select2lok').select2({
                            theme: 'bootstrap4'
                        });
                    }
                },
            });
        }

        function getsumdata(val) {
           return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-sum-roll") }}',
                type: 'get',
                data: {
                    rak: val,
                    no_ws: $('#txt_nows').val(),
                },
                success: function (res) {
                    if (res) {
                        // console.log(res[0].jml)
                        $('#txt_sum_roll').val(res[0].jml);
                    }
                },
            });
        }

        function sum_qty_mut(val){
            var table = document.getElementById("datatable");
            var qty_roll = 0;
            var qty_mut = 0;
            var jum_qty = 0;
            var roll_num = '';

            for (let i = 1; i < (table.rows.length); i++) {
                qty_roll =  document.getElementById("qty_roll"+i).value || 0;
                qty_mut =  document.getElementById("qty_mut"+i).value || 0;
                jum_qty += parseFloat(qty_mut);


                if (qty_mut > 0) {
                    if (parseFloat(qty_mut) > parseFloat(qty_roll)) {
                        $('#qty_mut'+i).val(qty_roll);
                    }else{
                        $('#qty_mut'+i).val(qty_mut);
                    }
                }
            }
            $('#txt_jml_qty').val(jum_qty);

        }



        // function getlistdata(val){
        //     datatable.ajax.reload();
        // }

        async function getlistdata() {
            return datatable.ajax.reload(() => {
                document.getElementById('jumlah_data').value = datatable.data().count();
            });
        }

        $(document).ready(function() {
        $('#datatable').DataTable({ searching: false, paging: false, info: false, ordering: false});

        } );


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
            td = tr[i].getElementsByTagName("td")[12]; //kolom ke berapa
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
