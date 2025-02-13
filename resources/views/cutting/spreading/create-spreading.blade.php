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
    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold text-sb">Tambah Data Spreading</h5>
        <a href="{{ route('spreading') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali ke Spreading</a>
    </div>
    <form action="{{ route('store-spreading') }}" method="post" id="store-spreading" name='form' onsubmit="submitSpreadingForm(this, event)">
        @csrf
            <div class='row'>
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="fw-bold card-title">Filter Data :</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-6 col-md-6">
                                    <div class="form-group">
                                        <label>No. WS</label>
                                        <select class="form-control select2bs4" id="cbows" name="cbows" onchange='getno_marker();' style="width: 100%;">
                                            <option selected="selected" value="">Pilih WS</option>
                                            @foreach ($data_ws as $dataws)
                                                <option value="{{ $dataws->act_costing_id }}">
                                                    {{ $dataws->ws }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <input type='hidden' class='form-control' id='txtid_marker' name='txtid_marker'>
                                </div>
                                <div class="col-6 col-md-6">
                                    <div class="form-group">
                                        <label>No. Marker</label>
                                        <select class='form-control select2bs4' style='width: 100%;' name='cbomarker' id='cbomarker' onchange='getdata_marker();'></select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="form-group">
                                        <label>Tipe Form</label>
                                        <input type='text' class='form-control' id='tipe_form' name='tipe_form' autocomplete='off' readonly>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="form-group">
                                        <label>Qty Ply Cutting</label>
                                        <input type='number' class='form-control' id='txtqty_ply_cut' name='txtqty_ply_cut' oninput='sum();' autocomplete='off'>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="form-group">
                                        <label>Total Form</label>
                                        <input type='number' class='form-control' id='jumlah_form' name='jumlah_form' oninput='customSum();' autocomplete='off'>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Keterangan</label>
                                        <textarea class='form-control' id='notes' name='notes' rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="fw-bold card-title">Hasil Data</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>Qty Ply Marker</label>
                                        <input type='text' class='form-control' id='hitungmarker' name='hitungmarker' readonly>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>Qty Ply Cutting</label>
                                        <input type='text' class='form-control' id='hitungcut' name='hitungcut' readonly>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>Total Form</label>
                                        <input type='text' class='form-control' id='hitungform' name='hitungform' readonly>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>Qty Ply Form</label>
                                        <input type='text' class='form-control' id='qty_ply_form' name='qty_ply_form' readonly>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>Sisa Ply Marker</label>
                                        <input type='text' class='form-control' id='sisa' name='sisa' readonly>
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="tarik_sisa" id="tarik_sisa" value="tarik">
                                            <label>Tarik Sisa Ply</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        {{-- <label>&emsp;&emsp;&emsp;</label> --}}
                                        <button type='submit' name='submit' class='btn btn-block btn-success'>Simpan</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class='row'>
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="fw-bold card-title">Detail Data :</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Panel</label>
                                        <input type='text' class='form-control' id='txtpanel' name='txtpanel' readonly>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Color</label>
                                        <input type='text' class='form-control' id='txtcolor' name='txtcolor' readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Buyer</label>
                                        <input type='text' class='form-control' id='txtbuyer' name='txtbuyer' readonly>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Style</label>
                                        <input type='text' class='form-control' id='txtstyle' name='txtstyle' readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>P. Marker</label>
                                        <input type='text' class='form-control' id='txt_p_marker' name='txt_p_marker' readonly>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>Unit</label>
                                        <input type='text' class='form-control' id='txt_unit_p_marker' name='txt_unit_p_marker' readonly>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>Comma</label>
                                        <input type='text' class='form-control' id='txt_comma_p_marker' name='txt_comma_p_marker' readonly>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>Unit</label>
                                        <input type='text' class='form-control' id='txt_unit_comma_p_marker' name='txt_unit_comma_p_marker' readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>PO</label>
                                        <input type='text' class='form-control' id='txt_po_marker' name='txt_po_marker' readonly>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>L. Marker</label>
                                        <input type='text' class='form-control' id='txt_l_marker' name='txt_l_marker' readonly>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>Unit</label>
                                        <input type='text' class='form-control' id='txt_unit_l_marker' name='txt_unit_l_marker' readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Qty Gelar Marker</label>
                                        <input type='text' class='form-control' id='txt_qty_gelar' name='txt_qty_gelar' readonly>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>WS</label>
                                        <input type='text' class='form-control' id='txt_ws' name='txt_ws' readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Cons WS</label>
                                        <input type='text' class='form-control' id='txt_cons_ws' name='txt_cons_ws'
                                            readonly>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Cons Marker</label>
                                        <input type='text' class='form-control' id='txt_cons_marker'
                                            name='txt_cons_marker' readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="fw-bold card-title">Ratio</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="datatable" class="table table-bordered table-striped table-sm w-100">
                                <thead>
                                    <tr>
                                        <th style='width:30%;text-align: center;'>Size</th>
                                        <th style='width:35%;text-align: center;'>Ratio</th>
                                        <th style='width:35%;text-align: center;'>Qty Cut Marker</th>
                                    </tr>
                                </thead>
                            </table>
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
    <script>
        $(document).ready(() => {
            document.getElementById("tarik_sisa").checked = false;
        });

        $("#tarik_sisa").on("change", () => {
            console.log($("#tarik_sisa").val());
        });

        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
        $('#cbows').val("").trigger("change");
        $("#cbomarker").prop("disabled", true);
        $("#txtqtyply").prop("readonly", true);

        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            info: false,
            ajax: {
                url: '{{ route('getdata_ratio') }}',
                data: function(d) {
                    d.cbomarker = $('#cbomarker').val();
                },
            },
            columns: [
                {
                    data: 'size'
                },
                {
                    data: 'ratio'
                },
                {
                    data: 'cut_qty'
                }
            ]
        });

        function getno_marker() {
            clearForm();

            let cbows = document.form.cbows.value;
            let html = $.ajax({
                type: "POST",
                url: '{{ route('getno_marker') }}',
                data: {
                    cbows: cbows
                },
                async: false
            }).responseText;

            console.log(html != "");

            if (html != "") {
                $("#cbomarker").html(html);

                $("#cbomarker").prop("disabled", false);
                $("#txtqtyply").prop("readonly", false);
            }
        };

        function getdata_marker() {
            clearForm();

            let cbomarker = document.form.cbomarker.value;
            jQuery.ajax({
                url: '{{ route('getdata_marker') }}',
                method: 'get',
                data: {
                    cri_item: $('#cbomarker').val()
                },
                dataType: 'json',
                success: function(response) {
                    document.getElementById('txtpanel').value = response.panel;
                    document.getElementById('txtcolor').value = response.color;
                    document.getElementById('txtbuyer').value = response.buyer;
                    document.getElementById('txtstyle').value = response.style;
                    document.getElementById('txt_p_marker').value = response.panjang_marker;
                    document.getElementById('txt_unit_p_marker').value = response.unit_panjang_marker;
                    document.getElementById('txt_comma_p_marker').value = response.comma_marker;
                    document.getElementById('txt_unit_comma_p_marker').value = response.unit_comma_marker;
                    document.getElementById('txt_po_marker').value = response.po_marker;
                    document.getElementById('txt_l_marker').value = response.lebar_marker;
                    document.getElementById('txt_unit_l_marker').value = response.unit_lebar_marker;
                    document.getElementById('txt_qty_gelar').value = response.gelar_qty_balance ? response.gelar_qty_balance : response.gelar_qty;
                    document.getElementById('txt_ws').value = response.act_costing_ws;
                    document.getElementById('txt_cons_ws').value = response.cons_ws;
                    document.getElementById('txt_cons_marker').value = response.cons_marker;
                    document.getElementById('hitungmarker').value = response.gelar_qty_balance ? response.gelar_qty_balance : response.gelar_qty;
                    document.getElementById('txtid_marker').value = response.kode;
                    document.getElementById('tipe_form').value = response.tipe_marker == "bulk marker" && response.status_marker == "active" ? "Pilot to Bulk" : capitalizeFirstLetter((response.tipe_marker).replace(' marker', ""));
                    document.getElementById('notes').value = response.notes ? response.notes : (response.tipe_marker == "bulk marker" && response.status_marker == "active" ? "Pilot to Bulk" : capitalizeFirstLetter((response.tipe_marker).replace(' marker', "")));
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });

            datatable.ajax.reload();
        };

        function sum() {
            let hitungmarker = document.getElementById('txt_qty_gelar').value;
            let hitungcut = document.getElementById('txtqty_ply_cut').value;
            document.getElementById("hitungcut").value = +hitungcut;
            let result = parseFloat(hitungmarker) / parseFloat(hitungcut);
            let modulus = Math.ceil(parseFloat(hitungmarker) % parseFloat(hitungcut))
            let result_fix = Math.ceil(result)
            let jumlah_form = document.getElementById("jumlah_form").value;

            if (!isNaN(result_fix)) {
                document.getElementById("hitungform").value = result_fix;
                document.getElementById("sisa").value = modulus;
                document.getElementById("jumlah_form").value = result_fix;
                document.getElementById("qty_ply_form").value = (jumlah_form * hitungcut) > hitungmarker ? hitungmarker : (jumlah_form * hitungcut);
            }
        }

        function customSum() {
            let qtyPlyMarker = document.getElementById("hitungmarker").value;

            let qtyPly = document.getElementById("txtqty_ply_cut").value;
            let jumlahForm = document.getElementById("jumlah_form").value;

            let qtyPlyForm = qtyPly * jumlahForm;
            let modulus = qtyPlyMarker % qtyPly;
            let maxForm = Math.floor(qtyPlyMarker/qtyPly) + (modulus > 0 ? 1 : 0);

            console.log(jumlahForm, maxForm);

            if (jumlahForm >= maxForm) {
                sum();
            } else {
                console.log(qtyPly, maxForm);
                document.getElementById("hitungform").value = jumlahForm;
                document.getElementById("sisa").value = qtyPlyMarker - qtyPlyForm;
                document.getElementById("qty_ply_form").value = (qtyPlyForm > qtyPlyMarker ? qtyPlyMarker : qtyPlyForm);
            }
        }

        function clearForm() {
            document.getElementById('txtqty_ply_cut').value = "";
            document.getElementById('jumlah_form').value = "";
            document.getElementById('hitungmarker').value = "";
            document.getElementById('hitungcut').value = "";
            document.getElementById('hitungform').value = "";
            document.getElementById('qty_ply_form').value = "";
            document.getElementById('sisa').value = "";
            document.getElementById('notes').value = "";
        }

        document.getElementById("store-spreading").onkeypress = function(e) {
            var key = e.charCode || e.keyCode || 0;
            if (key == 13) {
                e.preventDefault();
                console.log('enter key prevented');
            }
        }

        function submitSpreadingForm(e, evt) {
            if (document.getElementById("loading")) {
                document.getElementById("loading").classList.remove("d-none");
            }

            evt.preventDefault();

            $("input[type=submit][clicked=true]").attr('disabled', true);

            clearModified();

            $.ajax({
                url: e.getAttribute('action'),
                type: e.getAttribute('method'),
                data: new FormData(e),
                processData: false,
                contentType: false,
                success: async function(res) {
                    if (document.getElementById("loading")) {
                        document.getElementById("loading").classList.add("d-none");
                    }

                    $("input[type=submit][clicked=true]").removeAttr('disabled');

                    if (res.status == 200) {
                        console.log(res);

                        e.reset();

                        $('#cbows').val("").trigger("change");
                        $("#cbomarker").prop("disabled", true);

                        Swal.fire({
                            icon: 'success',
                            title: 'Data Spreading berhasil disimpan',
                            html: "No. Form Cut : <br>" + res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        }).then((result) => {
                            location.reload();
                        })

                        datatable.ajax.reload();
                    }
                }, error: function(jqXHR) {
                    if (document.getElementById("loading")) {
                        document.getElementById("loading").classList.add("d-none");
                    }
                }

            });
        }
    </script>
@endsection
