@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style type="text/css">
        input[type=file]::file-selector-button {
  margin-right: 20px;
  border: none;
  background: #084cdf;
  padding: 10px 20px;
  border-radius: 10px;
  color: #fff;
  cursor: pointer;
  transition: background .2s ease-in-out;
}

input[type=file]::file-selector-button:hover {
  background: #0d45a5;
}

        .drop-container {
  position: relative;
  display: flex;
  gap: 10px;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 200px;
  padding: 20px;
  border-radius: 10px;
  border: 2px dashed #555;
  color: #444;
  cursor: pointer;
  transition: background .2s ease-in-out, border .2s ease-in-out;
}

.drop-container:hover {
  background: #eee;
  border-color: #111;
}

.drop-container:hover .drop-title {
  color: #222;
}

.drop-title {
  color: #444;
  font-size: 20px;
  font-weight: bold;
  text-align: center;
  transition: color .2s ease-in-out;
}

/*.loading {
    z-index: 20;
    position: absolute;
    top: 0;
    left:-5px;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
}
.loading-content {
    position: absolute;
    border: 16px solid #f3f3f3; 
    border-top: 16px solid #3498db; 
    border-radius: 50%;
    width: 50px;
    height: 50px;
    top: 40%;
    left:35%;
    animation: spin 2s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
*/
    </style>
@endsection

@section('content')
<form action="{{ route('save-upload-lokasi-retur') }}" method="post" id="store-inmaterial" onsubmit="submitForm(this, event)">
    @method('POST')
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
@foreach ($data_head as $dhead)
    <div class="card-body">
    <div class="form-group row">
    <div class="col-md-4">
        <div class="row">
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No BPB</small></label>
                <input type="text" class="form-control " id="txt_gr_dok" name="txt_gr_dok" value="{{ $dhead->no_dok }}" readonly>
                <input type="hidden" class="form-control " id="txt_idgr" name="txt_idgr" value="{{ $dhead->id_dok }}" readonly>
                <input type="hidden" class="form-control " id="txt_iddet" name="txt_iddet" value="{{ $dhead->id }}" readonly>
                <input type="hidden" class="form-control " id="txt_idjo" name="txt_idjo" value="{{ $dhead->id_jo }}" readonly>
                <input type="hidden" class="form-control " id="txt_iditem" name="txt_iditem" value="{{ $dhead->id_item }}" readonly>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Kode barang</small></label>
                <input type="text" class="form-control " id="m_kode_item" name="m_kode_item" value="{{ $dhead->kode_item }}" readonly>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                 <button type="button" class="btn btn-primary mr-5" data-toggle="modal" data-target="#importExcel" onclick="OpenModal()">
                    <i class="fa-solid fa-file-arrow-up"></i> IMPORT EXCEL
                </button>
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
                <label><small>No WS</small></label>
                <input type="text" class="form-control " id="m_no_ws" name="m_no_ws" value="{{ $dhead->no_ws }}" readonly>
                </div>
            </div>
            </div>

            <div class="col-md-6">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Qty Balance</small></label>
                    <input type="text" class="form-control" id="qty_bal" name="qty_bal" value="{{ $dhead->qty_sisa }}" readonly>
                </div>
            </div>
            </div>

            <div class="col-md-6">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Qty Upload</small></label>
                @foreach ($sum_data as $sdata)
                    @if ($sdata->qty == "")
                    <input style="background-color: white;" type="text" class="form-control" id="qty_upload" name="qty_upload" value="" readonly>
                    @endif
                    @if ($sdata->qty != "")
                    <input style="background-color: white;" type="text" class="form-control" id="qty_upload" name="qty_upload" value="{{ $sdata->qty }}" readonly>
                    @endif
                @endforeach
                </div>
            </div>
            </div>

        </div>
    </div>

    <div class="col-md-5">
        <div class="row">

            <div class="col-md-6">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Qty BPB</small></label>
                <input type="text" class="form-control" id="qty_bpb" name="qty_bpb" value="{{ $dhead->qty }}" readonly>
                </div>
            </div>
            </div>

            <div class="col-md-6">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Unit Detail</small></label>
                <input type="text" class="form-control " id="txt_unit" name="txt_unit" value="{{ $dhead->unit }}" readonly>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Deskripsi</small></label>
                <input type="text" class="form-control " id="txt_desc" name="txt_desc" value="{{ $dhead->desc_item }}" readonly>
                @foreach ($count_data as $cdata)
                    @if ($cdata->qty == "")
                   <input type="hidden" class="form-control" id="jumlah_data" name="jumlah_data" readonly>
                    @endif
                    @if ($cdata->qty != "")
                    <input type="hidden" class="form-control" id="jumlah_data" name="jumlah_data" value="{{ $cdata->qty }}" readonly>
                    @endif
                @endforeach
                </div>
            </div>
            </div>

        </div>
    </div>
    </div>
</div>   
</div>
@endforeach
<!-- 
<section id="loading">
    <div id="loading-content"></div>
  </section> -->
    <div class="card card-sb card-outline">
    <div class="card-body">
    <div class="form-group row">
    <div class="table-responsive" style="max-height: 300px">
            <table id="datatable" class="table table-bordered table-head-fixed table-striped table-sm w-100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">No Lot</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">No Roll</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">No Roll Buyer</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Qty BPB</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">QTY Actual</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Lokasi</th>
                        <th class="text-center" style="display: none;">Keterangan</th>
                        <th class="text-center" style="display: none;">Keterangan</th>
                        <th class="text-center" style="display: none;">Keterangan</th>
                        <th class="text-center" style="display: none;">Keterangan</th>
                        <th class="text-center" style="display: none;">Keterangan</th>
                        <th class="text-center" style="display: none;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
            <div class="mb-1">
                <div class="form-group">
                    <button class="btn btn-sb float-end mt-2 ml-2"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                    <a class="btn btn-warning float-end mt-2 ml-2" onclick="deleteupload()">
                    <i class="fa-solid fa-arrow-rotate-left"></i> Reset</a>
                    <a class="btn btn-danger float-end mt-2" onclick="submitback()">
                    <i class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>
        </div>
    </div>
</form>

        <!-- Import Excel -->
        <div class="modal fade" id="importExcel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form method="post" action="{{ route('import-excel-material') }}" enctype="multipart/form-data" onsubmit="submitLokasiForm(this, event)">
                    <div class="modal-content">
                        <div class="modal-header bg-sb text-light">
                            <h5 class="modal-title" id="exampleModalLabel">Import Excel</h5>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                        </div>
                        <div class="modal-body">
 
                            {{ csrf_field() }}
 
                           <!--  <label>Pilih file excel</label>
                            <div class="form-group">
                                <input type="file" name="file" required="required">
                            </div> -->

                            <label for="images" class="drop-container" id="dropcontainer">
                                <span class="drop-title">Drop files here</span>
                                or
                                <input type="file" name="file" required="required">
                            </label>

 
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Close</button>
                            <button type="submit" class="btn btn-primary toastsDefaultDanger"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Import</button>
                        </div>
                    </div>
                </form>
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

        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        //Initialize Select2 Elements
        $('.select2').select2()

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });

        $('.select2roll').select2({
            theme: 'bootstrap4'
        });

        $('.select2supp').select2({
            theme: 'bootstrap4'
        });

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

        function deleteupload(){
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("delete-upload") }}',
                type: 'get',
                data: {
                    id_det: $('#txt_iddet').val(),
                },
                success: function (res) {
                    $('#qty_upload').val('');
                    getlistdata();
                }
            });
        }

        function getqtyUpload() {
           return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-qty-upload") }}',
                type: 'get',
                data: {
                },
                success: function (res) {
                    if (res) {
                        // console.log(res[0].jml)
                        $('#qty_upload').val(res[0].qty);
                    }
                },
            });
        }

        async function getlistdata() {
            return datatable.ajax.reload(() => {
                document.getElementById('jumlah_data').value = datatable.data().count();
            });
        }

        function getMarkerCount() {
            document.getElementById('no_urut_marker').value = "";
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-marker-count") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color').val(),
                    panel: $('#panel').val()
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('no_urut_marker').value = res;
                    }
                }
            });
        }

        function getNumber() {
            document.getElementById('cons_ws').value = null;
            document.getElementById('order_qty').value = null;
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: ' {{ route("get-marker-number") }}',
                type: 'get',
                dataType: 'json',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color').val(),
                    panel: $('#panel').val()
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('cons_ws').value = res.cons_ws;
                        document.getElementById('order_qty').value = res.order_qty;
                    }
                }
            });

        }

        function calculateRatio(id) {
            let ratio = document.getElementById('ratio-'+id).value;
            let gelarQty = document.getElementById('gelar_marker_qty').value;
            document.getElementById('cut-qty-'+id).value = ratio * gelarQty;
        }

        function calculateAllRatio(element) {
            let gelarQty = element.value;

            for (let i = 0; i < datatable.data().count(); i++) {
                let ratio = document.getElementById('ratio-'+i).value;
                document.getElementById('cut-qty-'+i).value = ratio * gelarQty;
            }
        }

        // document.getElementById("store-marker").onkeypress = function(e) {
        //     var key = e.charCode || e.keyCode || 0;
        //     if (key == 13) {
        //         e.preventDefault();
        //     }
        // }


        function submitLokasiForm(e, evt) {
            // document.querySelector('#loading').classList.add('loading');
            // document.querySelector('#loading-content').classList.add('loading-content');
            evt.preventDefault();

            clearModified();

            $.ajax({
                url: e.getAttribute('action'),
                type: e.getAttribute('method'),
                data: new FormData(e),
                processData: false,
                contentType: false,
                success: async function(res) {
                    // document.querySelector('#loading').classList.remove('loading');
                    // document.querySelector('#loading-content').classList.remove('loading-content');
                    if (res.status == 200) {
                        console.log(res);

                        e.reset();

                        // $('#cbows').val("").trigger("change");
                        // $("#cbomarker").prop("disabled", true);

                        Swal.fire({
                            icon: 'success',
                            title: 'Data Lokasi berhasil diupload',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        })
                         $('#importExcel').modal('hide'); 

                        getlistdata();
                        getqtyUpload();
                    }
                },

            });
        }


        function submitback() {
            let iddok = $('#txt_idgr').val();
            window.location = '/laravel_develop/public/index.php/retur-inmaterial/lokasi-retur-material/'+iddok;
        }


        let datatable = $("#datatable").DataTable({
        ordering: false,
        processing: true,
        serverSide: true,
        paging: false,
        searching: false,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('data-upload-lokasi') }}',
            dataType: 'json',
            dataSrc: 'data',
            data: function(d) {
            },
        },
        columns: [{
                data: 'no_lot'
            },
            {
                data: 'no_roll'
            },
            {
                data: 'no_roll_buyer'
            },
            {
                data: 'qty_bpb'
            },
            {
                data: 'qty_aktual'
            },
            {
                data: 'kode_lok'
            },
            {
                data: 'no_lot'
            },
            {
                data: 'no_roll'
            },
            {
                data: 'no_roll_buyer'
            },
            {
                data: 'qty_bpb'
            },
            {
                data: 'qty_aktual'
            },
            {
                data: 'kode_lok'
            }
            
        ],
        columnDefs: [
                {
                    targets: [6],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="no_lot' + meta.row + '" name="no_lot['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [7],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="no_roll' + meta.row + '" name="no_roll['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [8],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="no_roll_buyer' + meta.row + '" name="no_roll_buyer['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [9],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="qty_bpb' + meta.row + '" name="qty_bpb['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [10],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="qty_aktual' + meta.row + '" name="qty_aktual['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [11],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="kode_lok' + meta.row + '" name="kode_lok['+meta.row+']" value="' + data + '" readonly />'
                }
        ]
    });



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
    <script type="text/javascript">
    function addlocation($ws,$id_jo, $id_item,$kode_item, $qty, $unit, $balance,$desc){
        let ws = $ws; 
        let id_jo = $id_jo; 
        let id_item = $id_item;
        let kode_item = $kode_item;
        let qty = $qty; 
        let unit = $unit;
        let balance = $balance;
        let desc = $desc;
        let no_dok = $('#txt_gr_dok').val();
        // alert(id_item);
    $('#m_gr_dok').val(no_dok);
    $('#m_no_ws').val(ws);
    $('#m_kode_item').val(kode_item);
    $('#m_qty').val(qty);
    $('#m_desc').val(desc);
    $('#m_balance').val(balance);
    $('#m_unit').val(unit);
    $('#m_idjo').val(id_jo);
    $('#m_iditem').val(id_item);
    $('#modal-add-lokasi').modal('show');  
    }

    function OpenModal(){
       $('#importExcel').modal('show'); 
    }

    function getlist_addlokasi(){
        let lokasi = $('#m_location').val();
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-detail-addlok") }}',
                type: 'get',
                data: {
                    lokasi: $('#m_location').val(),
                    jml_baris: $('#m_qty_det').val(),
                    lot: $('#m_lot').val()
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('detail_addlok').innerHTML = res;
                        $('.select2lok').select2({
                            theme: 'bootstrap4'
                        });
                    }
                }
            });
    }


    function showlocation($ws,$id_jo, $id_item,$kode_item, $qty, $unit, $balance,$desc){
        let ws = $ws; 
        let id_jo = $id_jo; 
        let id_item = $id_item;
        let kode_item = $kode_item;
        let qty = $qty; 
        let unit = $unit;
        let balance = $balance;
        let desc = $desc;
        let no_dok = $('#txt_gr_dok').val();
        // alert(id_item);
    $('#m_gr_dok2').val(no_dok);
    $('#m_no_ws2').val(ws);
    $('#m_kode_item2').val(kode_item);
    $('#m_qty2').val(qty);
    $('#m_desc2').val(desc);
    $('#m_balance2').val(balance);
    $('#m_unit2').val(unit);
    $('#m_idjo2').val(id_jo);
    $('#m_iditem2').val(id_item);
    $('#modal-show-lokasi').modal('show');  
    }

    function getlist_showlokasi($ws,$id_jo, $id_item){
        let ws = $ws;
        let id_jo = $id_jo; 
        let id_item = $id_item;
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-detail-showlok") }}',
                type: 'get',
                data: {
                    no_dok: $('#txt_gr_dok').val(),
                    no_ws: ws,
                    id_jo: id_jo,
                    id_item: id_item
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('detail_showlok').innerHTML = res;
                        $('#tableshow').dataTable({
                            "bFilter": false,
                        });
                    }
                }
            });
    }



</script>
@endsection
