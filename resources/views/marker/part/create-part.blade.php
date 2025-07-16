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
    <form action="{{ route('store-part') }}" method="post" id="store-part" onsubmit="submitPartForm(this, event)">
        @csrf
        <div class="card card-sb">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold">
                        <i class="fa fa-plus fa-sm"></i> Tambah Data Part Group
                    </h5>
                    <a href="{{ route('part') }}" class="btn btn-sm btn-primary">
                        <i class="fa fa-reply"></i> Kembali ke Part
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>No. WS</small></label>
                                <select class="form-control select2bs4" id="ws_id" name="ws_id" style="width: 100%;">
                                    <option selected="selected" value="">Pilih WS</option>
                                    @foreach ($orders as $order)
                                        <option value="{{ $order->id }}">
                                            {{ $order->kpno }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Buyer</small></label>
                            <input type="text" class="form-control" id="buyer" name="buyer" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Style</small></label>
                            <input type="text" class="form-control" id="style" name="style" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Panel</small></label>
                                <select class="form-control select2bs4" id="panel_id" name="panel_id" style="width: 100%;" >
                                    <option selected="selected" value="">Pilih Panel</option>
                                    {{-- select 2 option --}}
                                </select>
                                <input type="hidden" class="form-control" id="panel" name="panel" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <input type="hidden" class="form-control" id="ws" name="ws" readonly>
                    <div class="col-12 col-md-12">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Color</small></label>
                                <input type="text" class="form-control" name="color" id="color" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-12" id="parts-section">
                        <div class="row">
                            <div class="col-3">
                                <label class="form-label"><small>Part</small></label>
                                <select class="form-control select2bs4" name="part_details[0]" id="part_details_0">
                                    <option value="">Pilih Part</option>
                                    @foreach ($masterParts as $masterPart)
                                        <option value="{{ $masterPart->id }}" data-index="0">{{ $masterPart->nama_part }} - {{ $masterPart->bag }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <label class="form-label"><small>Cons</small></label>
                                <div class="d-flex mb-3">
                                    <div style="width: 50%;">
                                        <input type="number" class="form-control" style="border-radius: 3px 0 0 3px;" name="cons[0]" id="cons_0" step="0.001">
                                    </div>
                                    <div style="width: 50%;">
                                        <select class="form-select" style="border-radius: 0 3px 3px 0;" name="cons_unit[0]" id="cons_unit_0">
                                            <option value="meter">METER</option>
                                            <option value="yard">YARD</option>
                                            <option value="kgm">KGM</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <label class="form-label"><small>Tujuan</small></label>
                                <select class="form-control select2bs4" style="border-radius: 0 3px 3px 0;" name="tujuan[0]" id="tujuan_0">
                                    <option value="">Pilih Tujuan</option>
                                    @foreach ($masterTujuan as $tujuan)
                                        <option value="{{ $tujuan->id }}">{{ $tujuan->tujuan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <label class="form-label"><small>Proses</small></label>
                                <select class="form-control select2bs4" style="border-radius: 0 3px 3px 0;" name="proses[0]" id="proses_0" data-index="0" onchange="changeTujuan(this)">
                                    <option value="">Pilih Proses</option>
                                    @foreach ($masterSecondary as $secondary)
                                        <option value="{{ $secondary->id }}" data-tujuan="{{ $secondary->id_tujuan }}">{{ $secondary->proses." / ".$secondary->tujuan }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="button" class="btn btn-sm btn-sb-secondary float-end my-3" onclick="addNewPart()">
                            <i class="far fa-plus-square"></i>
                        </button>
                    </div>
                    <input type="hidden" class="form-control" id="jumlah_part_detail" name="jumlah_part_detail" value="1" readonly>
                </div>
                <div class="row justify-content-between">
                    <div class="col-3">
                        <a href="{{ route('part') }}" class="btn btn-danger btn-sm btn-block fw-bold mt-3" id="cancel-button"><i class="fa fa-close"></i> BATAL</a>
                    </div>
                    <div class="col-3">
                        <button type="submit" class="btn btn-success btn-sm btn-block fw-bold mt-3" id="submit-button"><i class="fa fa-save"></i> SIMPAN</button>
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
        // Global Variable
        var sumCutQty = null;
        var totalRatio = null;

        var partSection = null;
        var partOptions = null;
        var tujuanOptions = null;
        var prosesOptions = null;
        var selectedPartArray = [];

        var jumlahPartDetail = null;

        document.getElementById('loading').classList.remove("d-none");

        // Initial Window On Load Event
        $(document).ready(async function () {
            //Reset Form
            if (document.getElementById('store-part')) {
                document.getElementById('store-part').reset();

                $(".select2").val('').trigger('change');
                $(".select2bs4").val('').trigger('change');
                $(".select2bs4custom").val('').trigger('change');

                $("#ws_id").val(null).trigger("change");
                $('#part_details').val(null).trigger('change');

                await getMasterParts();
                await getTujuan();
                await getProses();

                partSection = document.getElementById('parts-section');

                jumlahPartDetail = document.getElementById('jumlah_part_detail');
                jumlahPartDetail.value = 1;
            }

            // Select2 Prevent Step-Jump Input ( Step = WS -> Panel )
            $("#panel_id").prop("disabled", true);

            document.getElementById('loading').classList.add("d-none");
        });

        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        });

        // Step One (WS) on change event
        $('#ws_id').on('change', async function(e) {
            document.getElementById("loading").classList.remove("d-none");

            if (this.value) {
                await updateOrderInfo();
                await updatePanelList();
            }

            document.getElementById("loading").classList.add("d-none");
        });

        // Step Two (Panel) on change event
        $('#panel_id').on('change', async function(e) {
            document.getElementById("panel").value = $('#panel_id option:selected').text();
        });

        // Update Order Information Based on Order WS and Order Color
        function updateOrderInfo() {
            return $.ajax({
                url: '{{ route("get-part-order") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                },
                dataType: 'json',
                success: function (res) {
                    if (res) {
                        document.getElementById('ws').value = res.kpno;
                        document.getElementById('buyer').value = res.buyer;
                        document.getElementById('style').value = res.styleno;
                        document.getElementById('color').value = res.colors;
                    }
                },
            });
        }

        // Update Panel Select Option Based on Order WS and Color WS
        function updatePanelList() {
            document.getElementById('panel_id').value = null;
            return $.ajax({
                url: '{{ route("get-part-panels") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color').val(),
                },
                success: function (res) {
                    if (res) {
                        // Update this step
                        document.getElementById('panel_id').innerHTML = res;

                        // Open this step
                        $("#panel_id").prop("disabled", false);
                    }
                },
            });
        }

        function getMasterParts() {
            return $.ajax({
                url: '{{ route("get-master-parts") }}',
                type: 'get',
                success: function (res) {
                    if (res) {
                        partOptions = res;
                    }
                },
                error: function (jqXHR) {
                    console.log(jqXHR);
                }
            });
        }

        function getTujuan() {
            return $.ajax({
                url: '{{ route("get-master-tujuan") }}',
                type: 'get',
                success: function (res) {
                    if (res) {
                        tujuanOptions = res;
                    }
                },
                error: function (jqXHR) {
                    console.log(jqXHR);
                }
            });
        }

        function getProses() {
            return $.ajax({
                url: '{{ route("get-master-secondary") }}',
                type: 'get',
                success: function (res) {
                    if (res) {
                        prosesOptions = res;
                    }
                },
                error: function (jqXHR) {
                    console.log(jqXHR);
                }
            });
        }

        function changeTujuan(element) {
            let thisIndex = element.getAttribute('data-index');
            let thisSelected = element.options[element.selectedIndex];
            let thisTujuan = document.getElementById('tujuan_'+thisIndex);

            console.log(thisTujuan);

            if (thisTujuan.value != thisSelected.getAttribute('data-tujuan')) {
                $('#tujuan_'+thisIndex).val(thisSelected.getAttribute('data-tujuan')).trigger("change");
            }
        }

        function addNewPart() {
            if (jumlahPartDetail) {
                // row
                let divRow = document.createElement('div');
                divRow.setAttribute('class', 'row');

                // 1
                let divCol1 = document.createElement('div');
                divCol1.setAttribute('class', 'col-3');

                let label1 = document.createElement('label');
                label1.setAttribute('class', 'form-label');
                label1.innerHTML = '<small>Part</small>';

                let partDetail = document.createElement("select");
                partDetail.setAttribute('class', 'form-select select2bs4custom');
                partDetail.setAttribute('name', 'part_details['+jumlahPartDetail.value+']');
                partDetail.setAttribute('id', 'part_details_'+jumlahPartDetail.value);
                partDetail.innerHTML = partOptions;

                divCol1.appendChild(label1);
                divCol1.appendChild(partDetail);

                // 2
                let divCol2 = document.createElement('div');
                divCol2.setAttribute('class', 'col-3');

                divCol2.innerHTML= `
                    <label class="form-label"><small>Cons</small></label>
                    <div class="d-flex mb-3">
                        <div style="width: 50%;">
                            <input type="number" class="form-control" style="border-radius: 3px 0 0 3px;" name="cons[`+jumlahPartDetail.value+`]" id="cons_`+jumlahPartDetail.value+`" step="0.001">
                        </div>
                        <div style="width: 50%;">
                            <select class="form-select" style="border-radius: 0 3px 3px 0;" name="cons_unit[`+jumlahPartDetail.value+`]" id="cons_unit_`+jumlahPartDetail.value+`">
                                <option value="meter">METER</option>
                                <option value="yard">YARD</option>
                                <option value="kgm">KGM</option>
                            </select>
                        </div>
                    </div>
                `;

                // 3
                let divCol3 = document.createElement('div');
                divCol3.setAttribute('class', 'col-3');

                let label3 = document.createElement('label');
                label3.setAttribute('class', 'form-label');
                label3.innerHTML = '<small>Tujuan</small>';

                let tujuan = document.createElement("select");
                tujuan.setAttribute('class', 'form-select select2bs4custom');
                tujuan.setAttribute('name', 'tujuan['+jumlahPartDetail.value+']');
                tujuan.setAttribute('id', 'tujuan_'+jumlahPartDetail.value);
                tujuan.innerHTML = tujuanOptions;

                divCol3.appendChild(label3);
                divCol3.appendChild(tujuan);

                // 4
                let divCol4 = document.createElement('div');
                divCol4.setAttribute('class', 'col-3');

                let label4 = document.createElement('label');
                label4.setAttribute('class', 'form-label');
                label4.innerHTML = '<small>Proses</small>';

                let proses = document.createElement("select");
                proses.setAttribute('class', 'form-select select2bs4custom');
                proses.setAttribute('name', 'proses['+jumlahPartDetail.value+']');
                proses.setAttribute('id', 'proses_'+jumlahPartDetail.value);
                proses.setAttribute('data-index', jumlahPartDetail.value);
                proses.setAttribute('onchange', 'changeTujuan(this)');
                proses.innerHTML = prosesOptions;

                divCol4.appendChild(label4);
                divCol4.appendChild(proses);

                // row
                divRow.appendChild(divCol1);
                divRow.appendChild(divCol2);
                divRow.appendChild(divCol3);
                divRow.appendChild(divCol4);

                partSection.appendChild(divRow);

                $('#part_details_'+jumlahPartDetail.value).select2({
                    theme: 'bootstrap4',
                });
                $('#tujuan_'+jumlahPartDetail.value).select2({
                    theme: 'bootstrap4',
                });
                $('#proses_'+jumlahPartDetail.value).select2({
                    theme: 'bootstrap4',
                });

                jumlahPartDetail.value++;
            } else {
                Swal.fire({
                    title: "Warning",
                    text: "Harap pilih No. WS",
                    icon: "warning"
                });
            }
        }

        // Prevent Form Submit When Pressing Enter
        document.getElementById("store-part").onkeypress = function(e) {
            var key = e.charCode || e.keyCode || 0;
            if (key == 13) {
                e.preventDefault();
            }
        }

        // Submit Part Form
        function submitPartForm(e, evt) {
            document.getElementById('submit-button').setAttribute('disabled', true);

            evt.preventDefault();

            clearModified();

            $.ajax({
                url: e.getAttribute('action'),
                type: e.getAttribute('method'),
                data: new FormData(e),
                processData: false,
                contentType: false,
                success: async function(res) {
                    document.getElementById('submit-button').removeAttribute('disabled');

                    // Success Response

                    if (res.status == 200) {
                        // When Actually Success :

                        // Reset This Form
                        e.reset();

                        // Success Alert
                        Swal.fire({
                            icon: 'success',
                            title: 'Data Part Berhasil disimpan',
                            text: res.message,
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
                        })
                    } else {
                        // When Actually Error :

                        // Error Alert
                        iziToast.error({
                            title: 'Error',
                            message: res.message,
                            position: 'topCenter'
                        });
                    }

                    // If There Are Some Additional Error
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
                    document.getElementById('submit-button').removeAttribute('disabled');

                    // Error Response

                    let res = jqXHR.responseJSON;
                    let message = '';
                    let i = 0;

                    for (let key in res.errors) {
                        message = res.errors[key];
                        document.getElementById(key).classList.add('is-invalid');
                        modified.push(
                            [key, '.classList', '.remove(', "'is-invalid')"],
                        )

                        if (i == 0) {
                            document.getElementById(key).focus();
                            i++;
                        }
                    };
                }
            });
        }

        // Reset Step
        async function resetStep() {
            $('#part_details').val(null).trigger('change');
            await $("#ws_id").val(null).trigger("change");
            await $("#panel_id").val(null).trigger("change");
            await $("#panel_id").prop("disabled", true);
        }
    </script>
@endsection
