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
                @foreach ($kode_insp as $kodeinsp) {{ $kodeinsp->no_insp }} <input type="hidden" class="form-control " id="txt_noinsp" name="txt_noinsp" value="{{ $kodeinsp->no_insp }}" readonly> @endforeach
            </h5>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
    <div class="card-body">
    <div class="form-group row">
    <div class="col-md-12 mb-3">
        <div class="table-responsive">
        @foreach ($data_header as $dheader)
        <table width="100%" class="text-nowrap">
            <tr>
                <td><b>Inspection Number :</b> {{ $dheader->no_insp }} &nbsp&nbsp&nbsp&nbsp&nbsp</td>
                <td><b>ID Item :</b> {{ $dheader->id_item }} &nbsp&nbsp&nbsp&nbsp&nbsp</td>
                <td><b>Style :</b> {{ $dheader->no_style }} &nbsp&nbsp&nbsp&nbsp&nbsp</td>
            </tr>
            <tr>
                <td><b>Inspection Date :</b> <?= date("d F Y",strtotime($dheader->tgl_insp)) ?> &nbsp&nbsp&nbsp&nbsp&nbsp</td>
                <td><b>Fabric Name :</b> {{ $dheader->fabricname }} &nbsp&nbsp&nbsp&nbsp&nbsp</td>
                <td><b>Lot :</b> {{ $dheader->no_lot }} &nbsp&nbsp&nbsp&nbsp&nbsp</td>
            </tr>
            <tr>
                <td><b>Color:</b> {{ $dheader->color }}</td>
                 @foreach ($avg_poin as $avgpoin)
                <td><b>Average Actual Point: {{ $avgpoin->avg_poin }}</b></td>
                @endforeach
                <td><b>Status: {{ $dheader->status }}</b></td>
            </tr>
        </table>
        @endforeach
        </div>
    </div>

        @foreach ($data_detail as $ddetail)
    <div class="col-md-12 mb-3" style="border: solid 1px;">
        <div class="table-responsive">
        <table width="100%" class="text-nowrap">
            <tr>
                <td><b>Form Number:</b> {{ $ddetail->no_form }} &nbsp&nbsp&nbsp&nbsp&nbsp</td>
                <td><b>Width:</b> {{ $ddetail->width_fabric }} Inch &nbsp&nbsp&nbsp&nbsp&nbsp</td>
                <td><b>Weight:</b> {{ $ddetail->weight_fabric }} Kg </td>
            </tr>
            <tr>
                <td><b>Date:</b> {{ $ddetail->tgl_form }} &nbsp&nbsp&nbsp&nbsp&nbsp</td>
                <td><b>Gramage:</b> {{ $ddetail->gramage }} &nbsp&nbsp&nbsp&nbsp&nbsp</td>
                <td><b>Inspector:</b> {{ $ddetail->inspektor }} &nbsp&nbsp&nbsp&nbsp&nbsp</td>
            </tr>
            <tr>
                <td><b>Fabric Supplier:</b> {{ $ddetail->fabric_supp }} &nbsp&nbsp&nbsp&nbsp&nbsp</td>
                <td><b>Roll:</b> {{ $ddetail->no_roll }}</td>
                <td><b>Machine No:</b> {{ $ddetail->no_mesin }}</td>
            </tr>
        </table>
        <br>
        <table class="table table-bordered table-striped 100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center">Length</th>
                        <th class="text-center">Defect Name</th>
                        <th class="text-center">Code</th>
                        <th class="text-center">Up To 3"</th>
                        <th class="text-center">Over 3" - 6"</th>
                        <th class="text-center">Over 6" - 9"</th>
                        <th class="text-center">Over 9"</th>
                        <th class="text-center">Width</th>
                    </tr>
                </thead>
                <tbody>
        @foreach ($data_temuan as $dtemuan)
        @if( $dtemuan->no_form == $ddetail->no_form)
                    <tr>
                        <td>{{ $dtemuan->lenght_fabric }}</td>
                        <td><?= $dtemuan->nama_defect ?></td>
                        <td><?= str_replace(',', '/',$dtemuan->kode_def) ?></td>
                        <td><?= str_replace(',', '/',$dtemuan->upto3) ?></td>
                        <td><?= str_replace(',', '/',$dtemuan->over3) ?></td>
                        <td><?= str_replace(',', '/',$dtemuan->over6) ?></td>
                        <td><?= str_replace(',', '/',$dtemuan->over9) ?></td>
                        <td>{{ $dtemuan->width_det }}</td>
                    </tr>
        @endif
        @endforeach
                </tbody>
            </table>
            <br>
        <table width="100%" class="text-nowrap">
            <tr>
                <td><b>Barcode Length:</b> {{ $ddetail->lenght_barcode }} Yard &nbsp&nbsp&nbsp&nbsp&nbsp</td>
                <td><b>Remark:</b> {{ $ddetail->catatan }} &nbsp&nbsp&nbsp&nbsp&nbsp</td>
            </tr>
            <tr>
                <td><b>Actual Length:</b> {{ $ddetail->lenght_actual }} Yard</td>
                @foreach ($data_sum as $dsum2)
                @if( $dsum2->no_form == $ddetail->no_form)
                @if( $dsum2->akt_poin > 20)
                <td><b>Status: REJECT</b></td>
                @else
                <td><b>Status: PASS</b> </td>
                @endif
                @endif
                @endforeach
            </tr>
        </table>
        <br>
        <table class="table table-bordered table-striped 100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center">Up To 3"</th>
                        <th class="text-center">Over 3" - 6"</th>
                        <th class="text-center">Over 6" - 9"</th>
                        <th class="text-center">Over 9"</th>
                        <th class="text-center">Width</th>
                        <th class="text-center">Total Point</th>
                        <th class="text-center">Actual Point</th>
                    </tr>
                </thead>
                <tbody>
        @foreach ($data_sum as $dsum)
        @if( $dsum->no_form == $ddetail->no_form)
                    <tr>
                        <td>{{ $dsum->upto3 }}</td>
                        <td>{{ $dsum->over3 }}</td>
                        <td>{{ $dsum->over6 }}</td>
                        <td>{{ $dsum->over9 }}</td>
                        <td>{{ $dsum->width_fabric }}</td>
                        <td>{{ $dsum->ttl_poin }}</td>
                        <td>{{ $dsum->akt_poin }}</td>
                    </tr>
        @endif
        @endforeach
                </tbody>
            </table>
        </div>
    </div>
        @endforeach



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

                $('#txt_no_roll').val('');
                $('#txt_berat').val('');
                $('#txt_unit').val('');
                $('#txt_unit').text('Convert Unit');
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

                location.reload();

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
                    <a href="{{ route('create-qcpass') }}/`+data+`"><button type='button' class='btn btn-sm btn-warning'><i class="fa-solid fa-pen-to-square"></i></button></a>
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
                }
            ],
            columnDefs: [
            {
                targets: [6],
                render: (data, type, row, meta) => data ? data.round(2) : "-"
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
