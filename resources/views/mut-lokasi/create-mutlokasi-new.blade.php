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
<form method="post" id="store-opname" onsubmit="submitForm(this, event)" >
    @method('GET')
    @csrf
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">
               <i class="fas fa-dolly"></i> Mutasi Lokasi
            </h5>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
          </div>
      </div>

      <div class="card-body">
        <div class="form-group row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-6 col-md-4">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>No Mutasi</small></label>
                                @foreach ($kode_gr as $kodegr)
                                <input type="text" class="form-control" id="txt_no_mut" name="txt_no_mut" value="{{ $kodegr->kode }}" readonly>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Tgl Mutasi</small></label>
                                <input type="date" class="form-control form-control" id="txt_tgl_mut" name="txt_tgl_mut"
                                value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Lokasi Tujuan</small></label>
                                <input list="list_lokasi" id="txt_lokasi_tujuan" name="txt_lokasi_tujuan" class="form-control" placeholder="Pilih Lokasi Tujuan" onchange="GantilokasiTujuan();">
                                    <datalist id="list_lokasi">
                                        @foreach ($lokasi as $lok)
                                        <option value="{{ $lok->kode_lok }}"></option>
                                        @endforeach
                                    </datalist>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-7">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Keterangan</small></label>
                                <div class="d-flex align-items-start">
                                    <textarea id="txt_keterangan" class="form-control me-2" rows="1"
                                    placeholder="Keterangan..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5"></div>

                    <div class="col-6 col-md-2">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Total Roll</small></label>
                <input type="text" class="form-control text-right" id="txt_total_roll" name="txt_total_roll"
                        value="" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-2">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Total Qty</small></label>
                <input type="text" class="form-control text-right" id="txt_total_qty" name="txt_total_qty"
                        value="" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Scan</small></label>
                                <input type="text" class="form-control" id="txt_barcode" name="txt_barcode" 
                                value="" autofocus onchange="getdatabarcode()">
                                <input type="hidden" id="txt_qty_barcode" name="txt_qty_barcode">
                                <input type="hidden" id="txt_item_barcode" name="txt_item_barcode" readonly>
                                <input type="hidden" id="txt_iditem_barcode" name="txt_iditem_barcode" readonly>
                                <input type="hidden" id="txt_jo_barcode" name="txt_jo_barcode" readonly>
                                <input type="hidden" id="txt_lok_barcode" name="txt_lok_barcode" readonly>
                                <input type="hidden" id="txt_lot_barcode" name="txt_lot_barcode" readonly>
                                <input type="hidden" id="txt_roll_barcode" name="txt_roll_barcode" readonly>
                                <input type="hidden" id="txt_unit_barcode" name="txt_unit_barcode" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                    </div>

                    <div class="col-12 col-md-5">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Multi Barcode</small></label>
                                <div class="d-flex align-items-start">
                                    <textarea id="txt_barcodes" class="form-control me-2" rows="5"
                                    placeholder="Scan/masukkan banyak barcode (1 baris 1 barcode)"></textarea>
                                    <button type="button" id="btnSendBarcode" class="btn btn-primary d-flex align-items-center">
                                        <i class="fas fa-paper-plane me-1"></i> Send
                                    </button>
                                </div>
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
            Data Detail Roll
        </h5>
    </div>
    <div class="card-body">
        <div class="form-group row">

            <div class="table-responsive" >
                <table id="datatable" class="table table-bordered table-striped w-100 text-nowrap">
                    <thead>
                        <tr>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">No Barcode</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">ID JO</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">No WS</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">ID Item</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Item Desc</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Qty</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Unit</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Lokasi Barcode</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Lokasi Tujuan</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
        <div class="mb-1">
            <div class="form-group">
                <input type="button"   class="btn btn-sb float-end mt-2 ml-2" value="Save" onclick="savedataopname()" />
                <input type="button"   class="btn btn-danger float-end mt-2 ml-2" value="Delete All" onclick="delete_scan_all()" />
                <a href="{{ route('mutasi-lokasi') }}" class="btn btn-warning float-end mt-2">
                    <i class="fas fa-arrow-circle-left"></i> Back</a>
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
const inputLokasi = document.getElementById('txt_lokasi_tujuan');
const listLokasi = document.getElementById('list_lokasi');
const lokasiOptions = Array.from(listLokasi.options).map(opt => opt.value);

// Cek juga saat blur (keluar dari input)
inputLokasi.addEventListener('blur', function() {
    const val = this.value.trim();
    if (val !== '' && !lokasiOptions.includes(val)) {
        this.value = ''; // kosongkan jika tidak cocok
    }
});
</script>

<script type="text/javascript">


// === ambil data barcode
function getdatabarcode(no_barcode = null) {
    let barcode = no_barcode ?? $('#txt_barcode').val();

    if (!barcode) return Promise.resolve({ status: 'skip' });

    return $.ajax({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        url: '{{ route("get-data-barcodemutasi") }}',
        type: 'get',
        data: { no_barcode: barcode },
        dataType: 'json'
    }).then(function (res) {
        if (!res || res.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Data Barcode ' + barcode + ' tidak ditemukan!'
            });
            resetBarcodeInputs();
            return { status: 'notfound', barcode };
        }

        res = res[0];
        return { status: 'ok', barcode, rawData: res };
    }).catch(function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal koneksi ke server!' });
        resetBarcodeInputs();
        return { status: 'error', barcode, msg: error };
    });
}


// === simpan data barcode
function savedatabarcode(data) {
    return $.ajax({
        url: '{{ route("simpan-scan-barcode-mutasi") }}',
        type: 'GET',
        dataType: 'json',
        data: data,
        success: function(res) {
            if (res?.status == 200) {
                dataTableReload();
                resetBarcodeInputs();
            }
        }
    });
}


// === reset input
function resetBarcodeInputs() {
    $('#txt_qty_barcode, #txt_item_barcode, #txt_iditem_barcode, #txt_jo_barcode, #txt_lok_barcode, #txt_lot_barcode, #txt_roll_barcode, #txt_unit_barcode').val('');
    $('#txt_barcode').val('').focus();
}

// === scan satu-satu
$('#txt_barcode').on('keydown', function(e) {
    if (["Tab", "Enter"].includes(e.key) || [9, 13].includes(e.keyCode)) {
        e.preventDefault();
        let code = $(this).val().trim();
        let lokasi_tujuan = $('#txt_lokasi_tujuan').val().trim();

        // ✅ Cek apakah lokasi tujuan kosong
        if (!lokasi_tujuan) {
            Swal.fire({
                icon: 'warning',
                title: 'Lokasi Tujuan Kosong!',
                text: 'Silakan pilih lokasi tujuan terlebih dahulu sebelum scan barcode.',
                confirmButtonText: 'OK'
            }).then(() => {
                $('#txt_barcode').val('');
                $(document.activeElement).blur();
                setTimeout(() => {
                    $('#txt_lokasi_tujuan').focus();
                }, 200);
            });
            return; // hentikan proses
        }

        if (!code) return; // tidak ada barcode, abaikan

        // ✅ lanjut proses barcode
        getdatabarcode(code).then(res => {
            if (res.status === 'ok' && res.rawData) {
                let d = res.rawData;
                savedatabarcode({
                    lokasi_tujuan: lokasi_tujuan,
                    no_barcode: d.no_barcode,
                    qty: d.qty,
                    id_item: d.id_item,
                    id_jo: d.id_jo,
                    lokasi_barcode: d.kode_lok,
                    no_lot: d.no_lot,
                    no_roll: d.no_roll,
                    unit: d.satuan,
                    supplier: d.supplier,
                    buyer: d.buyer,
                    itemdesc: d.itemdesc,
                    no_ws: d.no_ws,
                    no_dok: d.no_dok
                });
            }
        });
    }
});



// === batch via textarea
$('#btnSendBarcode').on('click', function () {
    let lokasi_tujuan = $('#txt_lokasi_tujuan').val().trim();
    let barcodes = $('#txt_barcodes').val().trim().split("\n").map(b => b.trim()).filter(b => b.length > 0);

    // ✅ Cek lokasi tujuan
    if (!lokasi_tujuan) {
        Swal.fire({
            icon: 'warning',
            title: 'Lokasi Tujuan Kosong!',
            text: 'Silakan pilih lokasi tujuan terlebih dahulu sebelum mengirim barcode.',
            confirmButtonText: 'OK'
        }).then(() => {
            $(document.activeElement).blur();
            setTimeout(() => {
                $('#txt_lokasi_tujuan').focus();
            }, 300);
        });
        return; // hentikan proses
    }

    if (!barcodes.length) {
        Swal.fire({ icon: 'warning', title: 'Kosong', text: 'Tidak ada barcode untuk diproses!' });
        return;
    }

    (async () => {
        Swal.fire({
            title: 'Sedang memproses...',
            html: 'Tunggu sebentar, jangan tutup halaman.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        let results = [];

        for (let bc of barcodes) {
            let res = await getdatabarcode(bc);
            if (res.status === 'ok' && res.rawData) {
                let d = res.rawData;
                await savedatabarcode({
                    lokasi_tujuan: lokasi_tujuan,
                    no_barcode: d.no_barcode,
                    qty: d.qty,
                    id_item: d.id_item,
                    id_jo: d.id_jo,
                    lokasi_barcode: d.kode_lok,
                    no_lot: d.no_lot,
                    no_roll: d.no_roll,
                    unit: d.satuan,
                    supplier: d.supplier,
                    buyer: d.buyer,
                    itemdesc: d.itemdesc,
                    no_ws: d.no_ws,
                    no_dok: d.no_dok
                });
                results.push({ status: 'success', barcode: bc });
            } else {
                results.push(res);
            }
        }

        Swal.close();

        let success = results.filter(r => r.status === 'success').map(r => r.barcode);
        let notfound = results.filter(r => r.status === 'notfound').map(r => r.barcode);

        let msg = "";
        if (success.length) msg += "✅ Berhasil: " + success.join(", ") + "<br>";
        if (notfound.length) msg += "❌ Tidak ditemukan: " + notfound.join(", ") + "<br>";

        Swal.fire({ icon: 'info', title: 'Hasil Proses', html: msg });
        $('#txt_barcodes').val('');
    })();
});

function GantilokasiTujuan(){
    let lokasi_tujuan = $('#txt_lokasi_tujuan').val().trim();
    return $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route("update_lokasi-mut-temp") }}',
                    type: 'get',
                    data: {
                        lokasi_tujuan: lokasi_tujuan,
                    },
                    success: function (res) {
                        dataTableReload();
                    }
                });
}


</script>
<script type="text/javascript">
    function savedataopname() {
        var txt_no_mut = document.getElementById('txt_no_mut').value;
        var txt_tgl_mut = document.getElementById('txt_tgl_mut').value;
        var txt_lokasi_tujuan = document.getElementById('txt_lokasi_tujuan').value;
        var txt_keterangan = document.getElementById('txt_keterangan').value;
        var txt_total_roll = document.getElementById('txt_total_roll').value;
        console.log("txt_no_mut:" + txt_no_mut);
        console.log("txt_tgl_mut:" + txt_tgl_mut);
        console.log("txt_lokasi_tujuan:" + txt_lokasi_tujuan);
        console.log("txt_keterangan:" + txt_keterangan);
        console.log("txt_total_roll:" + txt_total_roll);

        if (!txt_lokasi_tujuan) {
            Swal.fire({
                icon: 'warning',
                title: 'Lokasi Tujuan Kosong!',
                text: 'Silakan pilih lokasi tujuan',
                confirmButtonText: 'OK'
            }).then(() => {
                $('#txt_lokasi_tujuan').focus();
            });
            return;
        }

        if (txt_total_roll <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Belum ada data yang dipilih!',
                text: 'Silakan isi data terlebih dahulu',
                confirmButtonText: 'OK'
            }).then(() => {
            });
            return;
        }


                return $.ajax({
                    url: '{{ route('save-mutasi-rak-fabric') }}',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        txt_no_mut: txt_no_mut,
                        txt_tgl_mut: txt_tgl_mut,
                        txt_lokasi_tujuan: txt_lokasi_tujuan,
                        txt_keterangan: txt_keterangan,
                        txt_total_roll: txt_total_roll,
                    },
                    success: function(res) {
                        if (res) {
                            if (res.status == 200) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: 'Data Berhasil Disimpan ' + res.message,
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                    timer: 1500,
                                    timerProgressBar: true
                                }).then(async (result) => {
                                    dataTableReload();
                                    window.location.href = res.redirect;

                                });

                            }
                        }
                    }, error: function(jqXHR){
                        Swal.fire({
                            icon: 'warning',
                            title: 'Gagal!',
                            text: 'Silahkan Coba Lagi',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke'
                        }).then(async (result) => {
                            if (result.isConfirmed) {
                                savedataopname();
                            }
                        });
                    }
                });
            }
        </script>
        <script>
            let datatable = $("#datatable").DataTable({
                serverSide: true,
                processing: true,
                ordering: false,
                scrollX: '400px',
                scrollY: true,
                pageLength: 10,
                ajax: {
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('list-scan-barcode-mutasi') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: function(d) {
                        d.kode_lok = $('#txt_lokasi_h').val();

                    },
                },
                
                columns: [{
                    data: 'idbpb_det'
                },
                {
                    data: 'id_jo'
                },
                {
                    data: 'no_ws'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'item_desc'
                },
                {
                    data: 'qty'
                },

                {
                    data: 'unit'
                },
                {
                    data: 'rak_asal'
                },
                {
                    data: 'rak_tujuan'
                },

                {
                    data: 'idbpb_det'
                }

                ],
                columnDefs: [{
                    targets: [9],
                    render: (data, type, row, meta) => {
                   // if (row.qty_balance == 0) {
                    return `<div class='d-flex gap-1 justify-content-center'>
                    <button type='button' class='btn btn-sm btn-danger' href='javascript:void(0)' onclick='delete_scan("` + row.idbpb_det + `")'><i class="fa-solid fa-trash"></i></i></button>
                    </div>`;
                // }
            }
        }

        ]
    });

            function dataTableReload() {
                datatable.ajax.reload();
            }

function formatNumber(num, decimals = 0) {
    if (isNaN(num) || num === null) return '0';
    const options = {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    };
    return Number(num).toLocaleString('en-US', options);
}

datatable.on('xhr.dt', function(e, settings, json, xhr) {
    if (json && json.data) {
        let totalRoll = json.data.length;

        let totalQty = json.data.reduce((sum, row) => {
            let val = parseFloat(row.qty);
            if (isNaN(val)) val = 0;
            return sum + val;
        }, 0);

        const totalRollRounded = Math.round(totalRoll);         // 0 decimal
        const totalQtyRounded = Math.round((totalQty + Number.EPSILON) * 100) / 100; // 2 decimal

        $('#txt_total_roll').val(formatNumber(totalRollRounded, 0));
        $('#txt_total_qty').val(formatNumber(totalQtyRounded, 2));
    }
});


            function delete_scan($no_barcode){
                let no_barcode = $no_barcode;
                return $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route("delete-mut-temp") }}',
                    type: 'get',
                    data: {
                        no_barcode: no_barcode,
                    },
                    success: function (res) {
                        dataTableReload();
                    }
                });

            }

            function delete_scan_all(){
                let lokasi_h = '';
                return $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route("delete-mut-temp-all") }}',
                    type: 'get',
                    data: {
                        lokasi_h: lokasi_h,
                    },
                    success: function (res) {
                        dataTableReload();
                    }
                });

            }


        $( document ).ready(function() {
            dataTableReload();
            GantilokasiTujuan();
        });
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
