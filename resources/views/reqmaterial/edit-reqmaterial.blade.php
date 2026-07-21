@extends('layouts.index', ['containerFluid' => true])

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style type="text/css">
        .marginnya{
            margin-left: 350px;
            margin-right: 350px;
            margin-top: 10px;
        }
    </style>
@endsection

@section('content')
<div class="marginnya">
<form action="{{ route('update-reqmaterial-fabric') }}" method="post" id="store-reqmaterial" onsubmit="validateAndSubmitEditReqForm(this, event)">
     @method('GET')
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
        <div class="row">
            <div class="col-md-3">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No Request</small></label>
                @foreach ($data_head as $dh)
                <input type="text" class="form-control " id="no_req" name="no_req" value="{{ $dh->bppbno }}" readonly>
                </div>
            </div>
            </div>

            <div class="col-md-3">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Request date</small></label>
                <div class="input-group">
                    <input type="text" class="form-control" id="req_date" name="req_date" autocomplete="off" readonly
                            value="{{ $dh->bppbdate }}">
                    <span class="input-group-text" id="req_date_icon" style="cursor: pointer;"><i class="fas fa-calendar-alt"></i></span>
                </div>
                </div>
            </div>
            </div>

            <div class="col-md-4">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Dikirim Ke</small></label>
                <select class="form-control select2supp" id="dikirim_ke" name="dikirim_ke" style="width: 100%;">
                        @foreach ($msupplier as $msupp)
                        <?php
                            $status = '';
                            if ($msupp->id_supplier == $dh->id_supplier) {
                                $status = 'selected="selected"';
                            }else{
                                $status = '';
                            }
                        ?>
                    <option <?= $status; ?> value="{{ $msupp->id_supplier }}">
                                {{ $msupp->Supplier }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-3">
                <div class="mb-1">
                    <div class="form-group">
                        <label><small>Job Order #/WS #</small></label>
                        <input type="text" class="form-control" id="job_order" name="job_order" value="{{ $dh->kpno }}" readonly>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="mb-1">
                    <div class="form-group">
                        <label><small>WS Actual #</small></label>
                         <select class="form-control select2supp" id="ws_act" name="ws_act" style="width: 100%;" onchange="getStyle_aktual(this.value);">
                        @foreach ($no_ws_act as $ws_act)
                        <?php
                            $status = '';
                            if ($ws_act->isi == $dh->idws_act) {
                                $status = 'selected="selected"';
                            }else{
                                $status = '';
                            }
                        ?>
                    <option <?= $status; ?> value="{{ $ws_act->isi }}">
                                {{ $ws_act->tampil }}
                    </option>
                        @endforeach
                </select>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="mb-1">
                    <div class="form-group">
                        <label><small>Style Actual #</small></label>
                        <input type="text" class="form-control" id="style_act" name="style_act" value="{{ $dh->style_act}}" readonly>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Notes</small></label>
                <textarea type="text" rows="2" class="form-control " id="txt_notes" name="txt_notes" value="">{{ $dh->remark}}</textarea>
                <input type="hidden" class="form-control" id="jumlah_data" name="jumlah_data" readonly>
                <input type="hidden" class="form-control" id="jumlah_qty" name="jumlah_qty" readonly>
                </div>
            </div>
            </div>

        </div>
    </div>
</div>
   @endforeach

   @foreach ($jml_det as $jmldet)
<input type="hidden" class="form-control " id="txt_jmldet" name="txt_jmldet" value="{{$jmldet->jml_dok}}" readonly>
@endforeach

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
    <div>
            <table id="datatable" class="table table-bordered table-head-fixed table-striped w-100" style="table-layout: fixed;">
                <thead>
                    <tr>
                        <th class="text-center" style="font-size: 0.6rem;width: 11%;">JO #</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 11%;">WS #</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 11%;">WS Act #</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 11%;">Style #</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 8%;">ID Item</th>
                        <th class="text-center d-none" style="font-size: 0.6rem;">Kode Barang</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 27%;">Nama Barang</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 9%;">Qty Req</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 6%;">Qty Out</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 6%;">Unit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i=1; ?>
                    @foreach ($det_data as $detdata)
                    <tr>
                        <td value="{{$detdata->jo_no}}">{{$detdata->jo_no}}</td>
                        <td value="{{$detdata->kpno}}">{{$detdata->kpno}}</td>
                        <td value="{{$detdata->idws_act}}">{{$detdata->idws_act}}</td>
                        <td value="{{$detdata->styleno}}">{{$detdata->styleno}}</td>
                        <td value="{{$detdata->id_item}}">{{$detdata->id_item}}</td>
                        <td value="{{$detdata->goods_code}}">{{$detdata->goods_code}}</td>
                        <td value="{{$detdata->itemdesc}}">{{$detdata->itemdesc}}</td>
                        <td value="{{$detdata->qty}}"><input style="width:100px;" class="form-control-sm" type="text" id="qty_input<?= $i; ?>" name="qty_input[<?= $i; ?>]" value="{{$detdata->qty}}" />
                        <input type="hidden" id="id_item<?= $i; ?>" name="id_item[<?= $i; ?>]" value="{{$detdata->id_item}}" />
                        <input type="hidden" id="id_jo<?= $i; ?>" name="id_jo[<?= $i; ?>]" value="{{$detdata->id_jo}}" />
                        </td>
                        <td value="{{$detdata->qty_out}}">{{$detdata->qty_out}}</td>
                        <td value="{{$detdata->unit}}">{{$detdata->unit}}</td>

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
                    <a href="{{ route('req-material') }}" class="btn btn-danger float-end mt-2">
                    <i class="fas fa-arrow-circle-left"></i> Kembali</a>
                </div>
            </div>
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

        // ─── Request date datepicker (batasi periode closed) ─────────────────────────

        let minTglRo = @json($min_tgl_ro ?? '');
        let closedPeriods = {!! json_encode($closed_periods ?? []) !!};

        function formatDateYmd(date) {
            let m = date.getMonth() + 1;
            let d = date.getDate();
            return date.getFullYear() + '-' + (m < 10 ? '0' : '') + m + '-' + (d < 10 ? '0' : '') + d;
        }

        $('#req_date').datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: minTglRo ? minTglRo : null,
            beforeShowDay: function (date) {
                let ymd = formatDateYmd(date);
                for (let p of closedPeriods) {
                    if (ymd >= p.tgl_awal && ymd <= p.tgl_akhir) {
                        return [false, '', 'Periode sudah closed'];
                    }
                }
                return [true, ''];
            }
        });

        $('#req_date_icon').on('click', function () {
            $('#req_date').datepicker('show');
        });

        function validateAndSubmitEditReqForm(e, evt) {
            let tglRo = $('#req_date').val();

            if (minTglRo && tglRo < minTglRo) {
                evt.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Request date tidak boleh sebelum ' + minTglRo + ' (periode sudah closed).' });
                return;
            }

            for (let p of closedPeriods) {
                if (tglRo >= p.tgl_awal && tglRo <= p.tgl_akhir) {
                    evt.preventDefault();
                    Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Request date tidak boleh pada periode ' + p.tgl_awal + ' s/d ' + p.tgl_akhir + ' (sudah closed).' });
                    return;
                }
            }

            let missing = [];
            if (!$('#dikirim_ke').val()) missing.push('Dikirim Ke');

            let dikirimKeText = $('#dikirim_ke option:selected').text().toUpperCase().replace(/\s*-\s*/g, ' ').trim();
            if (dikirimKeText.includes('PRODUCTION CUTTING') && !$('#ws_act').val()) {
                missing.push('WS Actual #');
            }

            if (missing.length > 0) {
                evt.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Belum Lengkap',
                    html: '<div style="text-align:left;">Mohon lengkapi data berikut:' +
                        '<ul style="margin-top:10px;">' +
                        missing.map(m => '<li>' + m + '</li>').join('') +
                        '</ul></div>'
                });
                return;
            }

            // DataTables membuang row yang tidak match/tidak di halaman aktif dari DOM
            // saat difilter atau di-paging, jadi filter & paging dikosongkan dulu supaya
            // semua row detail ikut terkirim saat submit.
            if ($.fn.DataTable.isDataTable('#datatable')) {
                let table = $('#datatable').DataTable();
                table.search('');
                table.column(6).search('');
                table.page.len(-1).draw(false);
            }

            submitForm(e, evt);
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

        $('#datatable').DataTable({
            ordering: false,
            paging: true,
            pageLength: 10,
            searching: true,
            info: false,
            autoWidth: false,
            dom: "<'d-none'f>rtip",
            columnDefs: [{
                targets: [5],
                visible: false
            }]
        });


        function getWS() {
           return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-ws-req") }}',
                type: 'get',
                data: {
                    tipe_ws: $('#tipe_ws').val(),
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('job_order').innerHTML = res;
                    }
                },
            });
        }

        function getWSAct() {
           return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-ws-act") }}',
                type: 'get',
                data: {
                    tipe_ws: $('#tipe_ws').val(),
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('ws_act').innerHTML = res;
                    }
                },
            });
        }

        function getlist_item($id_jo){
        let idjo = $id_jo;
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-detail-req") }}',
                type: 'get',
                data: {
                    id_jo: idjo,
                    tipe_ws: $('#tipe_ws').val(),
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('detail_item').innerHTML = res;
                    }
                }
            });
        }

        function getsum_item($id_jo){
        let idjo = $id_jo;
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-sum-req") }}',
                type: 'get',
                data: {
                    id_jo: idjo,
                    tipe_ws: $('#tipe_ws').val(),
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('jumlah_data').value = res[0].jml_item;
                    }
                }
            });
        }


        // function getlistdata(val){
        //     datatable.ajax.reload();
        // }

        async function getlistdata() {
            return datatable.ajax.reload(() => {
                document.getElementById('jumlah_data').value = datatable.data().count();
            });
        }

        // let datatable = $("#datatable").DataTable({
        //     ordering: false,
        //     processing: true,
        //     serverSide: true,
        //     paging: false,
        //     searching: false,
        //     ajax: {
        //         url: '{{ route("get-detail-list") }}',
        //         data: function (d) {
        //             d.txt_supp = $('#txt_supp').val();
        //             d.txt_fill = $('#txt_po').val() ? $('#txt_po').val() : $('#txt_wsglobal').val();
        //             d.name_fill = $('#txt_po').val() ? 'PO' : 'WS';
        //             // alert(d.name_fill);
        //         },
        //     },
        //     columns: [
        //         {
        //             data: 'kpno'
        //         },
        //         {
        //             data: 'id_jo'
        //         } ,
        //         {
        //             data: 'id_item'
        //         },
        //         {
        //             data: 'goods_code'
        //         },
        //         {
        //             data: 'produk'
        //         },
        //         {
        //             data: 'itemdesc'
        //         },
        //         {
        //             data: 'qty'
        //         },
        //         {
        //             data: 'unit'
        //         },
        //         {
        //             data: 'qty'
        //         },
        //         {
        //             data: 'qty'
        //         },
        //         {
        //             data: 'unit'
        //         },
        //         {
        //             data: 'qty'
        //         },
        //         {
        //             data: 'unit'
        //         },
        //         {
        //             data: 'kpno'
        //         },
        //         {
        //             data: 'id_jo'
        //         },
        //         {
        //             data: 'id_item'
        //         },
        //         {
        //             data: 'goods_code'
        //         },
        //         {
        //             data: 'produk'
        //         },
        //         {
        //             data: 'itemdesc'
        //         },
        //         {
        //             data: 'qty'
        //         },
        //         {
        //             data: 'unit'
        //         }
        //     ],
        //     columnDefs: [
        //         {
        //             targets: [13],
        //             className: "d-none",
        //             render: (data, type, row, meta) => '<input type="hidden" id="det_kpno' + meta.row + '" name="det_kpno['+meta.row+']" value="' + data + '" readonly />'
        //         },
        //         {
        //             targets: [14],
        //             className: "d-none",
        //             render: (data, type, row, meta) => '<input type="hidden" id="det_idjo' + meta.row + '" name="det_idjo['+meta.row+']" value="' + data + '" readonly />'
        //         },
        //         {
        //             targets: [15],
        //             className: "d-none",
        //             render: (data, type, row, meta) => '<input type="hidden" id="det_iditem' + meta.row + '" name="det_iditem['+meta.row+']" value="' + data + '" readonly />'
        //         },
        //         {
        //             targets: [16],
        //             className: "d-none",
        //             render: (data, type, row, meta) => '<input type="hidden" id="det_code' + meta.row + '" name="det_code['+meta.row+']" value="' + data + '" readonly />'
        //         },
        //         {
        //             targets: [17],
        //             className: "d-none",
        //             render: (data, type, row, meta) => '<input type="hidden" id="det_produk' + meta.row + '" name="det_produk['+meta.row+']" value="' + data + '" readonly />'
        //         },
        //         {
        //             targets: [18],
        //             className: "d-none",
        //             render: (data, type, row, meta) => '<input type="hidden" id="det_itemdesc' + meta.row + '" name="det_itemdesc['+meta.row+']" value="' + data + '" readonly />'
        //         },
        //         {
        //             targets: [19],
        //             className: "d-none",
        //             render: (data, type, row, meta) => '<input type="hidden" id="det_qty' + meta.row + '" name="det_qty['+meta.row+']" value="' + data + '" readonly />'
        //         },
        //         {
        //             targets: [20],
        //             className: "d-none",
        //             render: (data, type, row, meta) => '<input type="hidden" id="det_unit' + meta.row + '" name="det_unit['+meta.row+']" value="' + data + '" readonly />'
        //         },

        //         {
        //             targets: [9],
        //             render: (data, type, row, meta) => {
        //                 // alert(meta.row)
        //             return '<input style="width:100px;" class="form-control-sm" type="text" min="0" max="' + data + '" id="qty_good' + meta.row + '" name="qty_good['+meta.row+']" onkeyup="tambahqty(this.value)" />';
        //         }

        //         },
        //         {
        //             targets: [11],
        //             render: (data, type, row, meta) => '<input style="width:100px;" class="form-control-sm" type="text" min="0" max="' + data + '" id="qty_reject' + meta.row + '" name="qty_reject['+meta.row+']" />'
        //         }
        //     ]
        // });

        function tambahqty($val){
            var table = document.getElementById("datatable");
            var qty = 0;
            var jml_qty = 0;

            for (var i = 1; i < (table.rows.length); i++) {
                qty = document.getElementById("datatable").rows[i].cells[10].children[0].value || 0;
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
        let filter = document.getElementById("cari_item").value;
        $('#datatable').DataTable().column(6).search(filter).draw();
    }


    function getStyle_aktual($no_ws){
            let no_ws = $no_ws;
            // alert(no_ws);
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-style-actual") }}',
                type: 'get',
                data: {
                    no_ws: no_ws,
                },
                success: function (res) {
                    if (res) {
                        console.log(res);
                        document.getElementById('style_act').value = res[0].styleno;
                    }
                }
            });
        }
    </script>
@endsection
