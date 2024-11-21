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
    <div class="card">
        <div class="card-header bg-sb text-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold">
                    <i class="fa-solid fa-note-sticky"></i>
                    Stocker List Detail
                </h5>
                <a href="{{ route('stocker-list') }}" class="btn btn-primary btn-sm"><i class="fa fa-reply"></i> Kembali ke Stocker List</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-3 d-none">
                    <div class="mb-3">
                        <label class="form-label">Form Cut ID</label>
                        <input type="text" class="form-control" name="form_cut_id" id="form_cut_id" value="{{ $stockerList->form_cut_id }}" readonly>
                    </div>
                </div>
                <div class="col-md-3 d-none">
                    <div class="mb-3">
                        <label class="form-label">SO Detail ID</label>
                        <input type="text" class="form-control" name="so_det_id" id="so_det_id" value="{{ $stockerList->so_det_id }}" readonly>
                    </div>
                </div>
                <div class="col-md-3 ">
                    <div class="mb-3">
                        <label class="form-label">No. WS</label>
                        <input type="text" class="form-control" name="act_costing_ws" id="act_costing_ws" value="{{ $stockerList->act_costing_ws }}" readonly>
                    </div>
                </div>
                <div class="col-md-3 ">
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="text" class="form-control" name="color" id="color" value="{{ $stockerList->color }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Size</label>
                        <input type="text" class="form-control" name="size" id="size" value="{{ $stockerList->size }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">No. Form</label>
                        <input type="text" class="form-control" name="no_form" id="no_form" value="{{ $stockerList->no_form." / ".$stockerList->no_cut }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Qty</label>
                        <input type="text" class="form-control" name="qty" id="qty" value="{{ $stockerList->range_akhir - $stockerList->range_awal + 1 }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Range Awal</label>
                        <input type="text" class="form-control" name="range_awal_stocker" id="range_awal_stocker" value="{{ $stockerList->range_awal }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Range Akhir</label>
                        <input type="text" class="form-control" name="range_akhir_stocker" id="range_akhir_stocker" value="{{ $stockerList->range_akhir }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        {{-- <button type="button" class="btn btn-success btn-block" data-bs-toggle="modal" data-bs-target="#setYearSequenceModal"><i class="fa fa-print"></i> Set Month Count Number</button> --}}
                        <button class="btn btn-sb w-100" data-bs-toggle="modal" data-bs-target="#setYearSequenceModal"><i class="fa-regular fa-file-lines fa-sm"></i> Set Year Sequence</button>
                    </div>
                </div>
            </div>
            @php
                $stockers = explode(",",$stockerList->id_qr_stocker);
            @endphp

            <div class="accordion mt-3" id="accordionExample">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button bg-sb-secondary text-light fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            Stocker List
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <ul class="list-group">
                                @foreach ($stockers as $stk)
                                    <li class="list-group-item">{{ $stk }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header bg-sb text-light">
            <h5 class="card-title fw-bold">
                <i class="fa-solid fa-hashtag"></i> Number List
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <form action="#" method="post" id="month-count-form">
                    <table class="table table-bordered table-sm w-100" id="datatable">
                        <thead>
                            <th>Number</th>
                            <th>Year Sequence</th>
                            <th>Year Sequence Number</th>
                            <th>Size</th>
                            <th>Dest</th>
                            <th>Sewing Line</th>
                            <th>Sewing Input</th>
                            <th>Packing Line</th>
                            <th>Packing Input</th>
                            <th class="d-none">
                                Print
                            </th>
                        </thead>
                        <tbody>
                            @if ($stockerListNumber->count() > 0)
                                @foreach ($stockerListNumber as $number)
                                    @php
                                        $thisOutput = $output->where("kode_numbering", $number->id_year_sequence)->first();
                                    @endphp
                                    <tr>
                                        <td>{{ $number->number }}</td>
                                        <td>{{ $number->year."_".$number->year_sequence }}</td>
                                        <td>{{ $number->year_sequence_number }}</td>
                                        <td>{{ $number->size }}</td>
                                        <td>{{ $number->dest }}</td>
                                        <td>{{ $thisOutput ? ($thisOutput->sewing_line ? $thisOutput->sewing_line : '-') : '-' }}</td>
                                        <td>{{ $thisOutput ? ($thisOutput->sewing_update ? $thisOutput->sewing_update : '-') : '-' }}</td>
                                        <td>{{ $thisOutput ? ($thisOutput->packing_line ? $thisOutput->packing_line : '-') : '-' }}</td>
                                        <td>{{ $thisOutput ? ($thisOutput->packing_update ? $thisOutput->packing_update : '-') : '-' }}</td>
                                        <td class="d-none">
                                            <div class="d-flex gap-3">
                                                <button type="button" class="btn btn-sm btn-danger" onclick="printYearSequence({{ $loop->index }});">
                                                    <i class="fa fa-print fa-s"></i>
                                                </button>
                                                <div class="form-check mt-1 mb-0">
                                                    <input class="form-check-input generate-num-check" type="checkbox" name="generate_num[{{ $loop->index }}]" id="generate_num_{{ $loop->index }}" value="{{ $number->id_month_year }}">
                                                    <label class="form-check-label" for="flexCheckDefault">
                                                        Select
                                                    </label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center">Data tidak ada</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    {{-- <button class="btn btn-success" id="generate-checked-month-count" onclick="generateCheckedMonthCount()"><i class="fa fa-print"></i> Generate Checked Month</button> --}}
                </form>
            </div>
        </div>
    </div>

    {{-- <div class="modal fade" id="setYearSequenceModal" tabindex="-1" aria-labelledby="setYearSequenceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="setYearSequenceModalLabel">Set Month Count</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bulan</label>
                            <select class="form-select select2bs4" name="year" id="year">
                                @foreach ($months as $month)
                                    <option value="{{ $month['angka'] }}">{{ $month['nama'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tahun</label>
                            <select class="form-select select2bs4" name="year_sequence" id="year_sequence">
                                @foreach ($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Range</label>
                        <div class="d-flex align-items-center">
                            <input type="number" class="form-control" id="range_awal_year_sequence" name="range_awal_year_sequence" value="">
                            <span class="mx-3">-</span>
                            <input type="number" class="form-control" id="range_akhir_year_sequence" name="range_akhir_year_sequence" value="">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times fa-sm"></i> Tutup</button>
                    <button type="button" class="btn btn-success" onclick="setYearSequenceNumber()"><i class="fa fa-print fa-sm"></i> Generate</button>
                </div>
            </div>
        </div>
    </div> --}}
    <div class="modal fade" id="setYearSequenceModal" tabindex="-1" aria-labelledby="setYearSequenceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5 fw-bold" id="setYearSequenceModalLabel"><i class="fa-regular fa-file-lines fa-sm"></i> Set Year Sequence</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row align-items-end mb-3">
                        <div class="col-sm-6 col-md-6">
                            <label class="form-label">Tahun</label>
                            <select class="form-select select2bs4" name="year" id="year" onchange="getSequenceYearSequence()">
                                @foreach ($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6 col-md-6">
                            <label class="form-label">Sequence</label>
                            <select class="form-select select2bs4" name="sequence" id="sequence" onchange="getRangeYearSequence()">
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Qty</label>
                        <input type="number" class="form-control" id="set_qty" name="set_qty" onkeyup="calculateRange()" onchange="calculateRange()">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Range</label>
                        <div class="d-flex align-items-center">
                            <input type="number" class="form-control" id="range_awal" name="range_awal" value="" onkeyup="calculateRange()" onchange="calculateRange()">
                            <span class="mx-3">-</span>
                            <input type="number" class="form-control" id="range_akhir" name="range_akhir" value="" onkeyup="calculateQty()" onchange="calculateQty()">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times fa-sm"></i> Tutup</button>
                    <button type="button" class="btn btn-success" onclick="setYearSequenceNumber()"><i class="fa fa-check fa-sm"></i> Selesai</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
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
        })

        $('#year').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#setYearSequenceModal")
        })

        $('#year_sequence').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#setYearSequenceModal")
        })
    </script>

    <script>
        // Initial Function
        $(document).ready(() => {
            // Set Filter to 1 Week Ago
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            $("#check-all-month-count").prop("checked", false);

            $("#tgl-awal").val(oneWeeksBeforeFull).trigger("change");

            let today = new Date();
            let todayDate = ("0" + today.getDate()).slice(-2);
            let todayMonth = ("0" + (today.getMonth() + 1)).slice(-2);
            let todayYear = today.getFullYear();
            let todayFull = todayYear + '-' + todayMonth + '-' + todayDate;

            $("#tgl-akhir").val(todayFull).trigger("change");

            $('#year').val(todayYear).trigger("change");
            // $('#year_sequence').val("").trigger("change");

            $('#set_qty').val($('#qty').val()).trigger("change");
        });

        var generating = false;

        $("#datatable").DataTable();

        function checkAllYearSequence(element) {
            let generateNumberingCheck = document.getElementsByClassName('generate-num-check');

            for (let i = 0; i < generateNumberingCheck.length; i++) {
                generateNumberingCheck[i].checked = element.checked;
            }
        }

        function validateYearSequence() {
            if (Number($('#range_awal').val()) > 0 && Number($('#range_awal').val()) <= Number($('#range_akhir').val())) {
                return true;
            }

            return false
        }

        function setYearSequenceNumber() {
            if (validateYearSequence()) {
                $.ajax({
                    url: '{{ route('set-year-sequence-number') }}',
                    type: 'post',
                    data: {
                        "year": $('#year').val(),
                        "year_sequence": $('#sequence').val(),
                        "form_cut_id": $('#form_cut_id').val(),
                        "so_det_id": $('#so_det_id').val(),
                        "size": $('#size').val(),
                        "range_awal_stocker": Number($('#range_awal_stocker').val()),
                        "range_akhir_stocker": Number($('#range_akhir_stocker').val()),
                        "range_awal_year_sequence": Number($('#range_awal').val()),
                        "range_akhir_year_sequence": Number($('#range_akhir').val()),
                        "replace": true,
                    },
                    // xhrFields:
                    // {
                    //     responseType: 'blob'
                    // },
                    success: function(res) {
                        if (res.status == 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                html: 'Data berhasil di setting <br> <b>'+$("#no_form").val()+'</b> <br> <b>'+$("#year").val()+'_'+$("#sequence").val()+'</b> <br> <b>'+$("#range_awal").val()+' - '+$("#range_akhir").val()+'</b>',
                                allowOutsideClick: false,
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                html: 'Data sudah mencapai '+$("#set_qty").val(),
                                allowOutsideClick: false,
                            });
                        }

                        // window.location.reload();

                        generating = false;
                    },
                    error: function(jqXHR) {
                        Swal.fire("Nomor stocker sudah mencapai "+$("#set_qty").val()+".", "", "info");

                        generating = false;
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    html: 'Qty/Range tidak valid.',
                    allowOutsideClick: false,
                });
            }
        }

        function generateCheckedMonthCount() {
            generating = true;

            let generateNumberingCheck = document.getElementsByClassName('generate-num-check');

            let checkedCount = 0;
            for (let i = 0; i < generateNumberingCheck.length; i++) {
                if (generateNumberingCheck[i].checked) {
                    checkedCount++;
                }
            }

            if (checkedCount > 0) {
                let monthCountForm = new FormData(document.getElementById("month-count-form"));

                Swal.fire({
                    title: 'Please Wait...',
                    html: 'Exporting Data...',
                    didOpen: () => {
                        Swal.showLoading()
                    },
                    allowOutsideClick: false,
                });

                $.ajax({
                    url: '{{ route('print-month-count-checked') }}',
                    type: 'post',
                    processData: false,
                    contentType: false,
                    data: monthCountForm,
                    xhrFields:
                    {
                        responseType: 'blob'
                    },
                    success: function(res) {
                        if (res) {
                            console.log(res);

                            var blob = new Blob([res], {type: 'application/pdf'});
                            var link = document.createElement('a');
                            link.href = window.URL.createObjectURL(blob);
                            link.download = "Numbers.pdf";
                            link.click();
                        }

                        window.location.reload();

                        generating = false;
                    },
                    error: function(jqXHR) {
                        console.error(jqXHR);

                        swal.close();

                        generating = false;
                    }
                });
            } else {
                Swal.fire({
                    icon:'warning',
                    title: 'Warning',
                    html: 'Harap ceklis number yang akan di print',
                });
            }
        }

        function getSequenceYearSequence() {
            $.ajax({
                url: '{{ route('get-sequence-year-sequence') }}',
                type: 'get',
                data: {
                    year: $("#year").val()
                },
                dataType: 'json',
                success: async function(res)
                {
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

                                latestVal = res[i].year_sequence;
                            }

                            $("#sequence").val(latestVal).trigger("change");
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
                    console.error(jqXHR)
                }
            })
        }

        function getRangeYearSequence() {
            $.ajax({
                url: '{{ route('get-range-year-sequence') }}',
                type: 'get',
                data: {
                    year: $("#year").val(),
                    sequence: $("#sequence").val()
                },
                dataType: 'json',
                success: function(res)
                {
                    console.log("range",res);

                    if (res) {
                        if (res.status != "400") {
                            $("#range_awal").val(res.year_sequence_number+1).trigger("change");
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
                    console.error(jqXHR)
                }
            })
        }

        function calculateRange() {
            let setQty = Number($("#set_qty").val());
            let rangeAwalStocker = Number($("#range_awal_stocker").val());
            let rangeAkhirStocker = Number($("#range_akhir_stocker").val());
            let rangeAwal = Number($("#range_awal").val());
            let rangeAkhir = Number($("#range_akhir").val());

            if (setQty > 0 && rangeAwal > 0) {
                $("#range_akhir").val(rangeAwal + setQty - 1);
            }
        }

        function calculateQty() {
            let setQty = Number($("#set_qty").val());
            let rangeAwalStocker = Number($("#range_awal_stocker").val());
            let rangeAkhirStocker = Number($("#range_akhir_stocker").val());
            let rangeAwal = Number($("#range_awal").val());
            let rangeAkhir = Number($("#range_akhir").val());

            if (rangeAwal > 0 && rangeAkhir > rangeAwal) {
                $("#set_qty").val(rangeAkhir - rangeAwal + 1);
            }
        }

        function printYearSequence() {
            let setQty = Number($("#set_qty").val());
            let rangeAwalStocker = Number($("#range_awal_stocker").val());
            let rangeAkhirStocker = Number($("#range_akhir_stocker").val());
            let rangeAwal = Number($("#range_awal").val());
            let rangeAkhir = Number($("#range_akhir").val());

            if (rangeAwal > 0 && rangeAkhir > rangeAwal) {
                $("#set_qty").val(rangeAkhir - rangeAwal + 1);
            }
        }
    </script>
@endsection
