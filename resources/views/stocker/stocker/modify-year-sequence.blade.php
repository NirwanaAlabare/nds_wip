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
    <div class="card card-sb">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-middle">
                <h5 class="card-title fw-bold"><i class="fa-solid fa-pen-to-square"></i> Modify Year Sequence</h5>
            </div>
        </div>
        <div class="card-body">
            <h5 class="fw-bold text-sb">QR Range :</h5>
            <div class="row justify-content-center align-items-end g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label"><small><b>Year</b></small></label>
                    <select class="form-select" name="year" id="year">
                        @foreach ($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><small><b>Sequence</b></small></label>
                    <select class="form-select" name="sequence" id="sequence">

                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label"><small><b>Range Awal</b></small></label>
                    <input type="number" class="form-control" id="range_awal" name="range_awal">
                </div>
                <div class="col-md-5">
                    <label class="form-label"><small><b>Range Akhir</b></small></label>
                    <input type="number" class="form-control" id="range_akhir" name="range_akhir">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-block btn-primary" onclick="currentRangeTableReload()"><i class="fa fa-search"></i> Check</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered w-100'" id="current-range-table">
                    <thead>
                        <tr>
                            <th>QR</th>
                            <th>No. WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Destination</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <h5 class="fw-bold text-sb my-3">Update To : </h5>
            <div class="row justify-content-center align-items-end g-3">
                <div class="col-md-3">
                    <label class="form-label"><small><b>No. WS</b></small></label>
                    <select class="form-select select2bs4" name="id_ws" id="id_ws">
                        <option value="">Pilih WS</option>
                        @foreach ($orders as $order)
                            <option value="{{ $order->id }}">{{ $order->kpno }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><small><b>Style</b></small></label>
                    <select class="form-select select2bs4" name="style" id="style">
                        <option value="">Pilih Style</option>
                        @foreach ($orders as $style)
                            <option value="{{ $style->id }}">{{ $style->styleno }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><small><b>Color</b></small></label>
                    <select class="form-select select2bs4" name="color" id="color">
                        <option value="">Pilih Color</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><small><b>Size</b></small></label>
                    <select class="form-select select2bs4" name="size" id="size">
                        <option value="">Pilih Size</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><small><b>Range Awal</b></small></label>
                    <input type="number" class="form-control form-control-sm" value="" readonly id="range_awal_info">
                </div>
                <div class="col-md-4">
                    <label class="form-label"><small><b>Range Akhir</b></small></label>
                    <input type="number" class="form-control form-control-sm" value="" readonly id="range_akhir_info">
                </div>
                <div class="col-md-4">
                    <label class="form-label"><small><b>Total Update</b></small></label>
                    <input type="number" class="form-control form-control-sm" value="" readonly id="total_update">
                </div>
                <div class="col-md-12">
                    <div class="d-flex justify-content-between mt-3">
                        <button class="btn btn-sm btn-primary fw-bold"><i class="fa fa-reply"></i> Kembali</button>
                        <button class="btn btn-sm btn-success fw-bold" onclick="updateYearSequence()"><i class="fa fa-save"></i> UPDATE</button>
                    </div>
                </div>
            </div>
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

    <script>
        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        });
    </script>

    <script>
        $( document ).ready(async function() {
            let today = new Date();
            let todayDate = ("0" + today.getDate()).slice(-2);
            let todayMonth = ("0" + (today.getMonth() + 1)).slice(-2);
            let todayYear = today.getFullYear();
            let todayFull = todayYear + '-' + todayMonth + '-' + todayDate;

            getSequence();

            $("#id_ws").val("").trigger("change");
        });

        // Get Sequence
        function getSequence() {
            document.getElementById('loading').classList.remove('d-none');

            $.ajax({
                url: '{{ route('get-sequence-year-sequence') }}',
                type: 'get',
                data: {
                    year: $("#year").val()
                },
                dataType: 'json',
                success: async function(res)
                {
                    document.getElementById('loading').classList.add('d-none');

                    if (res) {
                        if (res.status != "400") {
                            let select = document.getElementById('sequence');
                            select.innerHTML = "";

                            let latestVal = null;
                            for(let i = 0; i < res.length; i++) {
                                let option = document.createElement("option");
                                option.setAttribute("value", res[i].year_sequence);
                                option.innerHTML = res[i].year_sequence;
                                select.appendChild(option);
                            }

                            $("#sequence").val(res[0].year_sequence).trigger("change");
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                html: res.message,
                            });
                        }
                    }
                },
                error: function(jqXHR)
                {
                    document.getElementById('loading').classList.add('d-none');
                    console.error(jqXHR)
                }
            })
        }

        $("#id_ws").on("change", () => {
            updateWs("ws");
            updateColorList()
        })

        $("#style").on("change", () => {
            updateWs("style");
            updateColorList()
        })

        // Update WS Select Option
        function updateWs(currentVal) {
            console.log(currentVal, $("#style").val(), $("#id_ws").val());
            if (currentVal && ($("#style").val() != $("#id_ws").val())) {
                if (currentVal == "ws") {
                    $("#style").val($("#id_ws").val()).trigger("change");
                }

                if (currentVal == "style") {
                    $("#id_ws").val($("#style").val()).trigger("change");
                }
            }
        }

        // Update Color Select Option
        function updateColorList() {
            document.getElementById('color').value = null;

            return $.ajax({
                url: '{{ route("get-colors") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#id_ws').val(),
                },
                success: function (res) {
                    if (res) {
                        let select = document.getElementById('color');
                        select.innerHTML = "";

                        let latestVal = null;
                        for(let i = 0; i < res.length; i++) {
                            let option = document.createElement("option");
                            option.setAttribute("value", res[i].color);
                            option.innerHTML = res[i].color;
                            select.appendChild(option);
                        }

                        $("#color").val(res[0].color).trigger("change");

                        // Open this step
                        $("#color").prop("disabled", false);
                    }
                },
            });
        }

        $("#color").on("change", () => {
            updateSizeList()
        })

        // Update Color Select Option
        function updateSizeList() {
            document.getElementById('size').value = null;

            return $.ajax({
                url: '{{ route("get-sizes") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#id_ws').val(),
                    color: $('#color').val(),
                },
                success: function (res) {
                    if (res) {
                        console.log(res, res[0]);
                        let select = document.getElementById('size');
                        select.innerHTML = "";

                        let latestVal = null;
                        for(let i = 0; i < res.length; i++) {
                            let option = document.createElement("option");
                            option.setAttribute("value", res[i].so_det_id);
                            option.innerHTML = res[i].size+(res[i].dest && res[i].dest != '-' ? ' - '+res[i].dest : '');
                            option.setAttribute("size", res[i].size);
                            select.appendChild(option);
                        }

                        $("#size").val(res[0].so_det_id).trigger("change");

                        // Open this step
                        $("#size").prop("disabled", false);
                    }
                },
            });
        }

        // Modify Year Sequence List
        function currentRangeTableReload() {
            $("#current-range-table").DataTable().ajax.reload(() => {
                $("#range_awal_info").val($("#range_awal").val());
                $("#range_akhir_info").val($("#range_akhir").val());
                $("#total_update").val(currentRangeTable.page.info().recordsTotal);
            });
        }

        let currentRangeTable = $("#current-range-table").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("modify-year-sequence-list") }}',
                data: function (d) {
                    d.year = $('#year').val();
                    d.sequence = $('#sequence').val();
                    d.range_awal = $('#range_awal').val();
                    d.range_akhir = $('#range_akhir').val();
                },
            },
            columns: [
                {
                    data: 'id_year_sequence'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'dest'
                }
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap"
                },
            ],
        });

        function updateYearSequence() {
            Swal.fire({
                icon: "info",
                title: "Konfirmasi",
                html: "Range <b>"+$('#range_awal').val()+" - "+$('#range_akhir').val()+"</b> dengan Total QTY <b>"+((Number($('#range_akhir').val()) - Number($('#range_awal').val())) + 1)+"</b>",
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: "Lanjut",
                denyButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("loading").classList.remove("d-none");

                    $.ajax({
                        url: '{{ route('update-year-sequence') }}',
                        method: "POST",
                        data: {
                            "year": $("#year").val(),
                            "sequence": $("#sequence").val(),
                            "range_awal": $("#range_awal").val(),
                            "range_akhir": $("#range_akhir").val(),
                            "size": $("#size").val(),
                            "size_text": $("#size").find(":selected").attr("size"),
                        },
                        success: function (res) {
                            document.getElementById("loading").classList.add("d-none");

                            if (res.status == 200) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    html: res.message,
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    html: res.message,
                                });
                            }
                        },
                        error: function (jqXHR) {
                            document.getElementById("loading").classList.add("d-none");

                            let res = jqXHR.responseJSON;
                            let message = '';

                            for (let key in res.errors) {
                                message = res.errors[key];
                                document.getElementById(key).classList.add('is-invalid');
                                document.getElementById(key + '_error').classList.remove('d-none');
                                document.getElementById(key + '_error').innerHTML = res.errors[key];

                                modified.push(
                                    [key, '.classList', '.remove(', "'is-invalid')"],
                                    [key + '_error', '.classList', '.add(', "'d-none')"],
                                    [key + '_error', '.innerHTML = ', "''"],
                                )
                            };

                            iziToast.error({
                                title: 'Error',
                                message: 'Terjadi kesalahan.',
                                position: 'topCenter'
                            });
                        }
                    })
                } else {
                    Swal.fire("Update dibatalkan", "", "info");
                }
            });
        }
    </script>
@endsection
