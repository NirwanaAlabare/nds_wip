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
    {{-- Hidden fields for JS --}}
    <input type="hidden" id="form_cut_id"  value="{{ $stockerList->form_cut_id }}">
    <input type="hidden" id="group_stocker" value="{{ $stockerList->group_stocker }}">
    <input type="hidden" id="ratio"         value="{{ $stockerList->ratio }}">
    <input type="hidden" id="so_det_id"     value="{{ $stockerList->so_det_id }}">
    <input type="hidden" id="tipe"          value="{{ $stockerList->tipe }}">

    {{-- Info Card --}}
    <div class="card">
        <div class="card-header bg-sb text-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0">
                    <i class="fa-solid fa-note-sticky"></i> Stocker List Detail
                </h5>
                <a href="{{ route('stocker-list') }}" class="btn btn-primary btn-sm"><i class="fa fa-reply"></i> Kembali ke Stocker List</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">No. WS</label>
                        <input type="text" class="form-control" id="act_costing_ws" value="{{ $stockerList->act_costing_ws }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="text" class="form-control" id="color" value="{{ $stockerList->color }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Panel</label>
                        <input type="text" class="form-control" id="panel" value="{{ $stockerList->panel }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Size</label>
                        <input type="text" class="form-control" id="size" value="{{ $stockerList->size }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">No. Form / No. Cut</label>
                        <input type="text" class="form-control" id="no_form" value="{{ $stockerList->no_form.' / '.$stockerList->no_cut }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Qty</label>
                        <input type="text" class="form-control" id="qty" value="{{ $stockerListNumber ? $stockerListNumber->count() : $stockerList->range_akhir - $stockerList->range_awal + 1 }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Range Awal</label>
                        <input type="text" class="form-control" id="range_awal_stocker" value="{{ $stockerList->range_awal }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Range Akhir</label>
                        <input type="text" class="form-control" id="range_akhir_stocker" value="{{ $stockerList->range_akhir }}" readonly>
                    </div>
                </div>
            </div>

            @php $stockers = explode(',', $stockerList->id_qr_stocker); @endphp
            <div class="accordion my-2" id="accordionExample">
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

            <div class="my-3 float-end">
                <button class="btn btn-sb w-100" data-bs-toggle="modal" data-bs-target="#setYearSequenceModal">
                    <i class="fas fa-external-link-alt fa-sm"></i> Set Year Sequence
                </button>
            </div>
        </div>
    </div>

    {{-- Number List Card --}}
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0 text-sb">
                    <i class="fa-solid fa-hashtag"></i> Number List
                </h5>
                <button type="button" class="btn btn-success btn-sm" onclick="exportExcel('year_sequence')">
                    <i class="fa fa-file-excel"></i> Export
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <form id="month-count-form">
                    <table class="table table-bordered table-sm w-100" id="datatable">
                        <thead>
                            <tr>
                                <th>Number</th>
                                <th>Year Sequence</th>
                                <th>Year Sequence Number</th>
                                <th>Size</th>
                                <th>Dest</th>
                                <th>Sewing Line</th>
                                <th>Sewing Input</th>
                                <th>Packing Line</th>
                                <th>Packing Input</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stockerListNumber as $number)
                                @php $thisOutput = $output->where('kode_numbering', $number->id_year_sequence)->first(); @endphp
                                <tr>
                                    <td>{{ $number->number }}</td>
                                    <td>{{ $number->year.'_'.$number->year_sequence }}</td>
                                    <td>{{ $number->year_sequence_number }}</td>
                                    <td>{{ $number->size }}</td>
                                    <td>{{ $number->dest }}</td>
                                    <td>{{ $thisOutput->sewing_line ?? '-' }}</td>
                                    <td>{{ $thisOutput->sewing_update ?? '-' }}</td>
                                    <td>{{ $thisOutput->packing_line ?? '-' }}</td>
                                    <td>{{ $thisOutput->packing_update ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Data tidak ada</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>

    {{-- Set Year Sequence Modal --}}
    <div class="modal fade" id="setYearSequenceModal" tabindex="-1" aria-labelledby="setYearSequenceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5 fw-bold" id="setYearSequenceModalLabel">
                        <i class="fa-regular fa-file-lines fa-sm"></i> Set Year Sequence
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row align-items-end mb-3">
                        <div class="col-6">
                            <label class="form-label">Tahun</label>
                            <select class="form-select select2bs4" name="year" id="year" onchange="getSequenceYearSequence()">
                                @foreach ($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Sequence</label>
                            <select class="form-select select2bs4" name="sequence" id="sequence" onchange="getRangeYearSequence()"></select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Qty</label>
                        <input type="number" class="form-control" id="set_qty" name="set_qty" onkeyup="calculateRange()" onchange="calculateRange()">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Range</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="number" class="form-control" id="range_awal" name="range_awal" onkeyup="calculateRange()" onchange="calculateRange()">
                            <span>-</span>
                            <input type="number" class="form-control" id="range_akhir" name="range_akhir" onkeyup="calculateQty()" onchange="calculateQty()">
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
    <!-- DataTables -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('#year').select2({ theme: 'bootstrap4', dropdownParent: $('#setYearSequenceModal') });
        $('#sequence').select2({ theme: 'bootstrap4', dropdownParent: $('#setYearSequenceModal') });

        $(document).ready(function () {
            let today = new Date();
            $('#year').val(today.getFullYear()).trigger('change');
            $('#set_qty').val($('#qty').val()).trigger('change');
            $('#datatable').DataTable();
        });

        function validateYearSequence() {
            return Number($('#range_awal').val()) > 0
                && Number($('#range_awal').val()) <= Number($('#range_akhir').val());
        }

        function setYearSequenceNumber() {
            if (!validateYearSequence()) {
                Swal.fire({ icon: 'error', title: 'Gagal', html: 'Qty/Range tidak valid.', allowOutsideClick: false });
                return;
            }

            $.ajax({
                url: '{{ route('set-year-sequence-number') }}',
                type: 'post',
                data: {
                    year:                     $('#year').val(),
                    year_sequence:            $('#sequence').val(),
                    form_cut_id:              $('#form_cut_id').val(),
                    so_det_id:                $('#so_det_id').val(),
                    size:                     $('#size').val(),
                    range_awal_stocker:       Number($('#range_awal_stocker').val()),
                    range_akhir_stocker:      Number($('#range_akhir_stocker').val()),
                    range_awal_year_sequence: Number($('#range_awal').val()),
                    range_akhir_year_sequence: Number($('#range_akhir').val()),
                    replace: true,
                },
                success: function (res) {
                    if (res.status == 200) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            html: 'Data berhasil di setting<br><b>' + $('#no_form').val() + '</b><br><b>' + $('#year').val() + '_' + $('#sequence').val() + '</b><br><b>' + $('#range_awal').val() + ' - ' + $('#range_akhir').val() + '</b>',
                            allowOutsideClick: false,
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', html: 'Data sudah mencapai ' + $('#set_qty').val(), allowOutsideClick: false });
                    }
                },
                error: function () {
                    Swal.fire('Nomor stocker sudah mencapai ' + $('#set_qty').val() + '.', '', 'info');
                }
            });
        }

        function getSequenceYearSequence() {
            $.ajax({
                url: '{{ route('get-sequence-year-sequence') }}',
                type: 'get',
                data: { year: $('#year').val() },
                dataType: 'json',
                success: function (res) {
                    if (!res || res.status == '400') {
                        Swal.fire({ icon: 'error', title: 'Gagal', html: res?.message ?? '' });
                        return;
                    }
                    let select = document.getElementById('sequence');
                    select.innerHTML = '';
                    let latestVal = null;
                    res.forEach(function (val) {
                        let opt = document.createElement('option');
                        opt.value = val;
                        opt.textContent = val;
                        select.appendChild(opt);
                        latestVal = val;
                    });
                    $('#sequence').val(latestVal).trigger('change');
                },
                error: function (jqXHR) { console.error(jqXHR); }
            });
        }

        function getRangeYearSequence() {
            $.ajax({
                url: '{{ route('get-range-year-sequence') }}',
                type: 'get',
                data: { year: $('#year').val(), sequence: $('#sequence').val() },
                dataType: 'json',
                success: function (res) {
                    if (!res || res.status == '400') {
                        Swal.fire({ icon: 'error', title: 'Gagal', html: res?.message ?? '' });
                        return;
                    }
                    $('#range_awal').val(res.year_sequence_number + 1).trigger('change');
                },
                error: function (jqXHR) { console.error(jqXHR); }
            });
        }

        function calculateRange() {
            let setQty  = Number($('#set_qty').val());
            let rangeAwal = Number($('#range_awal').val());
            if (setQty > 0 && rangeAwal > 0) {
                $('#range_akhir').val(rangeAwal + setQty - 1);
            }
        }

        function calculateQty() {
            let rangeAwal  = Number($('#range_awal').val());
            let rangeAkhir = Number($('#range_akhir').val());
            if (rangeAwal > 0 && rangeAkhir > rangeAwal) {
                $('#set_qty').val(rangeAkhir - rangeAwal + 1);
            }
        }

        async function exportExcel(type) {
            if (type !== 'year_sequence') return;

            document.getElementById('loading').classList.remove('d-none');

            await $.ajax({
                url: '{{ route("stocker-list-detail-export") }}/'
                    + ($('#form_cut_id').val()  || '0') + '/'
                    + ($('#group_stocker').val() || '0') + '/'
                    + ($('#ratio').val()         || '0') + '/'
                    + ($('#so_det_id').val()     || '0') + '/'
                    + ($('#tipe').val() == 'REJECT' ? 0 : 1),
                type: 'get',
                xhrFields: { responseType: 'blob' },
                success: function (res) {
                    document.getElementById('loading').classList.add('d-none');
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(new Blob([res]));
                    link.download = 'Stocker List.xlsx';
                    link.click();
                    iziToast.success({ title: 'Success', message: 'Data berhasil di export.', position: 'topCenter' });
                },
                error: function (jqXHR) {
                    document.getElementById('loading').classList.add('d-none');
                    iziToast.error({ title: 'Error', message: 'Data gagal di export.', position: 'topCenter' });
                    console.error(jqXHR);
                }
            });
        }
    </script>
@endsection
