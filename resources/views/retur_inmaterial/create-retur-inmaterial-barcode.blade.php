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
@include('retur_inmaterial._tab-create-ri')
<form action="{{ route('store-retur-inmaterial-fabric-new') }}" method="post" id="store-inmaterial" onsubmit="validateAndSubmitRiForm(this, event)">
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
        <div class="form-group row">
            <div class="col-6 col-md-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>No RI</small></label>
                                @foreach ($kode_gr as $kodegr)
                                <input type="text" class="form-control " id="txt_no_ri" name="txt_no_ri" value="{{ $kodegr->kode }}" readonly>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Supplier</small></label>
                <input type="text" class="form-control " id="txt_supp" name="txt_supp" value="" readonly>
                <input type="hidden" class="form-control " id="txt_idsupp" name="txt_idsupp" value="" readonly>
                </div>
            </div>
            </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="row">

                    <div class="col-md-12">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Tgl RI</small></label>
                                <input type="date" class="form-control form-control" id="txt_tgl_ri" name="txt_tgl_ri"
                                value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                   <!--  <div class="col-md-12">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Jenis Retur</small></label>
                                <select class="form-control select2bs4" id="txt_jns_rtr" name="txt_jns_rtr" style="width: 100%;" >
                                </select>
                            </div>
                        </div>
                    </div> -->

                    <div class="col-md-12">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Tipe BC <span class="text-danger">*</span></small></label>
                                <select class="form-control select2bs4" id="txt_type_bc" name="txt_type_bc" style="width: 100%;" onchange="get_tujuan(this.value)">
                                    <option selected="selected" value="">Pilih Tipe</option>
                                    @foreach ($mtypebc as $bc)
                                    <option value="{{ $bc->nama_pilihan }}">
                                        {{ $bc->nama_pilihan }}
                                    </option>
                                    @endforeach
                                </select>

                                <select class="form-control hidden" id="txt_type_pch" name="txt_type_pch" style="width: 100%;">
                                    <option selected="selected" value="Pengembalian dari Produksi">Pengembalian dari Produksi</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    

                </div>
            </div>

            <div class="col-12 col-md-5">
                <div class="row">

                    <input type="hidden" class="form-control " id="txt_aju_num" name="txt_aju_num" value="" >
                    <input type="hidden" class="form-control form-control" id="txt_tgl_aju" name="txt_tgl_aju"
                                                    value="{{ date('Y-m-d') }}">
                     <input type="hidden" class="form-control " id="txt_faktur" name="txt_faktur" value="" >
                    <input type="hidden" class="form-control form-control" id="txt_tgl_faktur" name="txt_tgl_faktur"
                                                    value="{{ date('Y-m-d') }}">
                    <input type="hidden" class="form-control " id="txt_reg" name="txt_reg" value="" >
                    <input type="hidden" class="form-control form-control" id="txt_tgl_reg" name="txt_tgl_reg"
                                                    value="{{ date('Y-m-d') }}">
                    <input type="hidden" class="form-control " id="txt_no_kk" name="txt_no_kk" value="" >

                    <!-- <div class="col-md-7">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>No Aju</small></label>
                                <input type="text" class="form-control " id="txt_aju_num" name="txt_aju_num" value="" >
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Tgl Aju</small></label>
                                <input type="date" class="form-control form-control" id="txt_tgl_aju" name="txt_tgl_aju"
                                value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>No Faktur Pajak</small></label>
                                <input type="text" class="form-control " id="txt_faktur" name="txt_faktur" value="" >
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Tgl Faktur Pajak</small></label>
                                <input type="date" class="form-control form-control" id="txt_tgl_faktur" name="txt_tgl_faktur"
                                value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>No Daftar</small></label>
                                <input type="text" class="form-control " id="txt_reg" name="txt_reg" value="" >
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Tgl Daftar</small></label>
                                <input type="date" class="form-control form-control" id="txt_tgl_reg" name="txt_tgl_reg"
                                value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div> -->

                    <div class="col-md-7">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>No Invoice/ No SJ</small></label>
                                <input type="text" class="form-control " id="txt_noinvoice" name="txt_noinvoice" value="" >
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Jenis Material</small></label>
                                <input type="text" class="form-control " id="txt_tom" name="txt_tom" value="Fabric" readonly>
                                <input type="hidden" class="form-control" id="jumlah_data" name="jumlah_data" value="0" readonly>
                                <input type="hidden" class="form-control" id="jumlah_qty" name="jumlah_qty" value="0" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Tujuan Pemasukan</small></label>
                                <select class="form-control select2bs4" id="txt_tujuan" name="txt_tujuan" style="width: 100%;">
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-sb card-outline">
    <div class="card-header">
        <h5 class="card-title fw-bold">
            Data Detail
        </h5>
    </div>
    <div class="card-body">
        <div class="form-group row">
            <div class="col-md-12">
                <ul class="nav nav-tabs" id="barcode-input-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-scan-barcode-link" data-bs-toggle="tab" href="#tab-scan-barcode" role="tab">
                            <i class="fa-solid fa-barcode"></i> Scan / Tempel Barcode
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-upload-excel-link" data-bs-toggle="tab" href="#tab-upload-excel" role="tab">
                            <i class="fa-solid fa-file-excel"></i> Upload Excel
                        </a>
                    </li>
                </ul>
                <div class="tab-content border border-top-0 rounded-bottom mb-3">
                    {{-- Tab: Scan Barcode --}}
                    <div class="tab-pane fade show active" id="tab-scan-barcode" role="tabpanel">
                        <div class="p-3" style="background:linear-gradient(135deg,#f0f8ff 0%,#e8f4fd 100%); border-radius:0 0 6px 6px;">
                            <div class="d-flex gap-3 align-items-end">
                                <div class="flex-grow-1">
                                    <label class="mb-1" style="font-size:0.8rem;font-weight:600;color:#555;">
                                        <i class="fa-solid fa-barcode text-info" style="margin-right:4px;"></i>
                                        Input Barcode
                                    </label>
                                    <textarea id="bulk_barcode_input_main" class="form-control"
                                        rows="4"
                                        placeholder="Scan atau tempel barcode di sini&#10;Pisahkan dengan Enter / spasi / koma..."
                                        style="font-family:monospace;font-size:0.9rem;resize:none;border-color:#90c4e8;background:#fff;"></textarea>
                                </div>
                                <button type="button" class="btn btn-info px-4 py-2" onclick="sendBarcodeList()"
                                    style="font-size:0.9rem;white-space:nowrap;border-radius:6px;">
                                    <i class="fa fa-paper-plane" style="margin-right:5px;"></i>Send
                                </button>
                            </div>
                        </div>
                    </div>
                    {{-- Tab: Upload Excel --}}
                    <div class="tab-pane fade" id="tab-upload-excel" role="tabpanel">
                        <div class="p-3" style="background:linear-gradient(135deg,#f2fff4 0%,#e8f8eb 100%); border-radius:0 0 6px 6px;">
                            <div class="d-flex gap-3 align-items-end flex-wrap">
                                <div class="flex-grow-1" style="min-width:220px;">
                                    <label class="mb-1" style="font-size:0.8rem;font-weight:600;color:#555;">
                                        <i class="fa-solid fa-file-excel text-success" style="margin-right:4px;"></i>
                                        Pilih File Excel (.xlsx / .xls / .csv)
                                    </label>
                                    <input type="file" id="excel_barcode_file" class="form-control" accept=".xlsx,.xls,.csv"
                                        style="border-color:#7dba84;background:#fff;">
                                </div>
                                <div class="d-flex gap-2" style="padding-bottom:1px;">
                                    <button type="button" class="btn btn-success px-4 py-2" onclick="uploadBarcodeExcel()"
                                        style="font-size:0.9rem;border-radius:6px;">
                                        <i class="fa fa-upload" style="margin-right:5px;"></i>Upload
                                    </button>
                                    <a href="{{ route('download-template-barcode-ri') }}"
                                        class="btn btn-outline-secondary px-3 py-2"
                                        style="font-size:0.9rem;border-radius:6px;">
                                        <i class="fa fa-download" style="margin-right:5px;"></i>Template
                                    </a>
                                </div>
                            </div>
                            <small class="d-block mt-2" style="color:#666;">
                                <i class="fa fa-circle-info text-info" style="margin-right:4px;"></i>
                                Kolom file: <strong>no_barcode</strong>, <strong>no_bppb</strong>, <strong>qty</strong>, <strong>kode_lok</strong> (sesuai template). Kolom <strong>kode_lok</strong> wajib diisi.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-head-fixed table-striped w-100 text-nowrap">
                    <thead>
                        <tr>
                            <th class="text-center" style="font-size: 0.6rem;width: 200px;">WS</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 200px;">No PO</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 200px;">ID Item</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 250px;">Deskripsi</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 100px;">Jml Barcode</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 120px;">Qty SJ</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 150px;">Qty Return</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 150px;">Qty Reject</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 100px;">Satuan</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 250px;">Keterangan</th>
                            <th class="text-center" style="display: none;">Hidden</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3 px-1 pb-1">
            <a href="{{ route('retur-inmaterial') }}" class="btn btn-danger px-3" style="border-radius:6px;">
                <i class="fas fa-arrow-circle-left" style="margin-right:5px;"></i>Kembali
            </a>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-warning px-3" onclick="clearBarcodeRiTemp()" style="border-radius:6px;">
                    <i class="fa-solid fa-trash-can" style="margin-right:5px;"></i>Clear
                </button>
                <button class="btn btn-sb px-4" style="border-radius:6px;">
                    <i class="fa-solid fa-floppy-disk" style="margin-right:5px;"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>
</form>

<div class="modal fade" id="modal-detail-barcode-ri-temp">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-sb text-light">
                <h4 class="modal-title">Detail Barcode</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="detail_barcode_ri_temp_content"></div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Tutup</button>
                <button type="button" class="btn btn-primary" onclick="saveAllDetailBarcodeRiTemp(this)"><i class="fa-solid fa-floppy-disk"></i> Simpan Semua</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-multi-gkout-barcode" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-sb text-light">
                <h4 class="modal-title">Pilih No GK/OUT</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Barcode berikut terdaftar di lebih dari satu No GK/OUT dengan qty yang berbeda. Pilih No GK/OUT yang sesuai untuk masing-masing barcode agar qty tersimpan dengan benar:</p>
                <div id="multi_gkout_barcode_content"></div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Batal</button>
                <button type="button" class="btn btn-primary" onclick="confirmMultiGkoutSelection()"><i class="fa fa-check" aria-hidden="true"></i>Simpan</button>
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

    //Reset Form
    if (document.getElementById('store-inmaterial')) {
        document.getElementById('store-inmaterial').reset();
    }

    function get_tujuan(val) {
       return $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: '{{ route("get-tujuan-pemasukan") }}',
        type: 'get',
        data: {
            type_bc: $('#txt_type_bc').val(),
        },
        success: function (res) {
            if (res) {
                document.getElementById('txt_tujuan').innerHTML = res;
            }
        },
    });
    }

    function escHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function fillSupplierFields(id_supplier, nama_supplier, no_invoice) {
        if (id_supplier) {
            $('#txt_idsupp').val(id_supplier);
            $('#txt_supp').val(nama_supplier || '');
        }
        if (no_invoice !== undefined && no_invoice !== null) {
            $('#txt_noinvoice').val(no_invoice);
        }
    }

    // fill supplier + no invoice fields from existing temp rows on page load
    $.get('{{ route("get-supplier-barcode-ri-temp") }}', function (res) {
        if (res) fillSupplierFields(res.id_supplier, res.nama_supplier, res.no_invoice);
    });

    function showSupplierConflict(groups) {
        let html = '<div class="text-start">';
        groups.forEach(function (g) {
            html += '<p class="mb-1"><strong>' + escHtml(g.nama_supplier || g.id_supplier) + '</strong></p>'
                + '<ul class="mb-2" style="font-size:0.85rem">';
            g.barcodes.forEach(function (b) { html += '<li>' + escHtml(b) + '</li>'; });
            html += '</ul>';
        });
        html += '</div>';

        Swal.fire({
            title: 'Supplier Berbeda!',
            html: html,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }

    let datatable = $("#datatable").DataTable({
        ordering: false,
        processing: true,
        serverSide: false,
        paging: false,
        searching: true,
        scrollY: '350px',
        scrollX: true,
        scrollCollapse: true,
        ajax: {
            url: '{{ route("get-grouped-barcode-ri-temp") }}',
        },
        columns: [
            { data: 'no_ws' },
            { data: 'pono' },
            { data: 'id_item' },
            { data: 'itemdesc' },
            { data: 'jml_barcode' },
            { data: 'qty_sj' },
            { data: 'qty_retur' },
            { data: 'qty_reject' },
            { data: 'unit' },
            { data: null },
            { data: 'id_item' },
            { data: null }
        ],
        columnDefs: [
            {
                targets: [6],
                render: (data, type, row, meta) => '<input style="width:100px;text-align:right;" class="form-control-sm" type="text" name="qty_retur[]" value="' + (parseFloat(data) || 0) + '" readonly />'
            },
            {
                targets: [7],
                render: (data, type, row, meta) => '<input style="width:100px;text-align:right;" class="form-control-sm" type="text" name="qty_reject[]" value="' + (parseFloat(data) || 0) + '" readonly />'
            },
            {
                targets: [9],
                render: (data, type, row, meta) => '<input style="width:250px;" class="form-control-sm" type="text" name="keterangan[]" />'
            },
            {
                targets: [10],
                className: 'd-none',
                render: (data, type, row, meta) =>
                    '<input type="hidden" name="id_item[]" value="' + escHtml(row.id_item) + '" readonly />'
                    + '<input type="hidden" name="id_jo[]" value="' + escHtml(row.id_jo) + '" readonly />'
                    + '<input type="hidden" name="id_bppb[]" value="' + escHtml(row.id_bppb) + '" readonly />'
                    + '<input type="hidden" name="no_bppb[]" value="' + escHtml(row.no_bppb) + '" readonly />'
                    + '<input type="hidden" name="no_ws[]" value="' + escHtml(row.no_ws) + '" readonly />'
            },
            {
                targets: [11],
                render: (data, type, row, meta) => {
                    return '<div class="d-flex gap-1 justify-content-center">'
                        + '<button type="button" class="btn btn-sm btn-info" onclick="viewDetailBarcodeRiTemp(' + row.id_item + ',' + row.id_jo + ',' + row.id_bppb + ')" title="Lihat Detail"><i class="fa-solid fa-eye"></i></button>'
                        + '<button type="button" class="btn btn-sm btn-danger" onclick="deleteGroupBarcodeRiTemp(' + row.id_item + ',' + row.id_jo + ',' + row.id_bppb + ')" title="Hapus"><i class="fa-solid fa-trash"></i></button>'
                        + '</div>';
                }
            }
        ],
        createdRow: function (row, data) {
            let all_filled = parseInt(data.jml_barcode || 0) > 0 && parseInt(data.jml_lok || 0) >= parseInt(data.jml_barcode || 0);
            $(row).css('background-color', all_filled ? '#d4edda' : '#f8d7da');
        }
    });

    datatable.on('xhr.dt', function (e, settings, json) {
        let data = (json && json.data) ? json.data : [];
        $('#jumlah_data').val(data.length);
    });

    datatable.on('draw.dt', function () {
        tambahqty();
    });

    function tambahqty() {
        let jml_qty = 0;

        $('#datatable tbody tr').each(function () {
            let qty = parseFloat($(this).find('input[name="qty_retur[]"]').val()) || 0;
            jml_qty += qty;
        });

        $('#jumlah_qty').val(jml_qty);
    }

    function sendBarcodeList() {
        let input = $('#bulk_barcode_input_main').val();
        if (!input) return;

        let barcodes = [...new Set(input.split(/[\s,;]+/).map(b => b.trim()).filter(b => b !== ''))];
        if (barcodes.length === 0) return;

        return $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route("save-barcode-ri-temp") }}',
            type: 'post',
            data: {
                id_barcode: barcodes,
            },
            success: function (res) {
                if (res) {
                    $('#bulk_barcode_input_main').val('');
                    datatable.ajax.reload();
                    fillSupplierFields(res.id_supplier, res.nama_supplier, res.no_invoice);

                    let warnings = [];

                    if (res.not_found && res.not_found.length > 0) {
                        warnings.push('Barcode tidak ditemukan: ' + res.not_found.join(', '));
                    }

                    if (res.duplicate && res.duplicate.length > 0) {
                        warnings.push('Barcode sudah ada di Data Detail: ' + res.duplicate.join(', '));
                    }

                    if (warnings.length > 0) {
                        iziToast.warning({
                            title: 'Perhatian',
                            message: warnings.join(' | '),
                            position: 'topCenter'
                        });
                    }

                    if (res.saved > 0) {
                        iziToast.success({
                            title: 'Berhasil',
                            message: res.saved + ' barcode berhasil ditambahkan.',
                            position: 'topCenter'
                        });
                    }

                    if (res.supplier_conflict && res.supplier_conflict.length > 0) {
                        showSupplierConflict(res.supplier_conflict);
                        return;
                    }

                    if (res.need_selection && res.need_selection.length > 0) {
                        showMultiGkoutSelection(res.need_selection);
                    }
                }
            },
            error: function () {
                iziToast.error({
                    title: 'Error',
                    message: 'Gagal menyimpan barcode.',
                    position: 'topCenter'
                });
            }
        });
    }

    function showMultiGkoutSelection(need_selection) {
        let html = '<div class="table-responsive"><table class="table table-bordered table-sm align-middle">'
            + '<thead><tr><th>No Barcode</th><th>Pilih No GK/OUT (qty akan dijumlahkan)</th></tr></thead><tbody>';

        need_selection.forEach(function (entry) {
            let checkboxes = entry.candidates.map(function (c, i) {
                let cbId = 'cb_gkout_' + entry.id_roll.replace(/[^a-zA-Z0-9]/g, '_') + '_' + i;
                return '<div class="form-check">'
                    + '<input class="form-check-input cb-multi-gkout" type="checkbox"'
                    + ' id="' + escHtml(cbId) + '"'
                    + ' value="' + escHtml(c.no_bppb) + '"'
                    + ' data-id-roll="' + escHtml(entry.id_roll) + '"'
                    + ' checked>'
                    + '<label class="form-check-label" for="' + escHtml(cbId) + '">'
                    + '<strong>' + escHtml(c.no_bppb) + '</strong>'
                    + ' &mdash; Qty: ' + escHtml(c.qty) + ' ' + escHtml(c.unit)
                    + '</label>'
                    + '</div>';
            }).join('');

            html += '<tr>'
                + '<td class="align-middle"><strong>' + escHtml(entry.id_roll) + '</strong></td>'
                + '<td>' + checkboxes + '</td>'
                + '</tr>';
        });

        html += '</tbody></table></div>';

        $('#multi_gkout_barcode_content').html(html);
        $('#modal-multi-gkout-barcode').modal('show');
    }

    function confirmMultiGkoutSelection() {
        let selections = {};

        $('#multi_gkout_barcode_content .cb-multi-gkout').each(function () {
            let roll = $(this).data('id-roll');
            if (!selections[roll]) selections[roll] = [];
            if ($(this).is(':checked')) selections[roll].push($(this).val());
        });

        // every barcode must have at least 1 checked
        let incomplete = Object.keys(selections).some(function (roll) {
            return selections[roll].length === 0;
        });

        if (incomplete) {
            iziToast.warning({
                title: 'Info',
                message: 'Pilih minimal satu No GK/OUT untuk setiap barcode.',
                position: 'topCenter'
            });
            return;
        }

        let id_barcode = Object.keys(selections);
        if (id_barcode.length === 0) return;

        return $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route("save-barcode-ri-temp") }}',
            type: 'post',
            data: {
                id_barcode: id_barcode,
                selections: selections,
            },
            success: function (res) {
                if (res) {
                    $('#modal-multi-gkout-barcode').modal('hide');
                    datatable.ajax.reload();
                    fillSupplierFields(res.id_supplier, res.nama_supplier, res.no_invoice);

                    let warnings = [];

                    if (res.supplier_conflict && res.supplier_conflict.length > 0) {
                        showSupplierConflict(res.supplier_conflict);
                        return;
                    }

                    if (res.duplicate && res.duplicate.length > 0) {
                        warnings.push('Barcode sudah ada di Data Detail: ' + res.duplicate.join(', '));
                    }

                    if (warnings.length > 0) {
                        iziToast.warning({
                            title: 'Perhatian',
                            message: warnings.join(' | '),
                            position: 'topCenter'
                        });
                    }

                    if (res.saved > 0) {
                        iziToast.success({
                            title: 'Berhasil',
                            message: res.saved + ' barcode berhasil ditambahkan.',
                            position: 'topCenter'
                        });
                    }
                }
            },
            error: function () {
                iziToast.error({
                    title: 'Error',
                    message: 'Gagal menyimpan barcode.',
                    position: 'topCenter'
                });
            }
        });
    }

    function uploadBarcodeExcel() {
        let fileInput = document.getElementById('excel_barcode_file');

        if (!fileInput.files || fileInput.files.length === 0) {
            iziToast.warning({
                title: 'Info',
                message: 'Pilih file Excel terlebih dahulu.',
                position: 'topCenter'
            });
            return;
        }

        let formData = new FormData();
        formData.append('file', fileInput.files[0]);

        return $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route("upload-barcode-ri-temp") }}',
            type: 'post',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res) {
                    fileInput.value = '';
                    datatable.ajax.reload();
                    fillSupplierFields(res.id_supplier, res.nama_supplier, res.no_invoice);

                    if (res.supplier_conflict && res.supplier_conflict.length > 0) {
                        showSupplierConflict(res.supplier_conflict);
                        return;
                    }

                    let warnings = [];

                    if (res.not_found && res.not_found.length > 0) {
                        warnings.push('Barcode tidak ditemukan: ' + res.not_found.join(', '));
                    }

                    if (res.mismatched && res.mismatched.length > 0) {
                        warnings.push('No BPPB tidak sesuai untuk barcode: ' + res.mismatched.join(', '));
                    }

                    if (res.duplicate && res.duplicate.length > 0) {
                        warnings.push('Barcode sudah ada di Data Detail: ' + res.duplicate.join(', '));
                    }

                    if (warnings.length > 0) {
                        iziToast.warning({
                            title: 'Perhatian',
                            message: warnings.join(' | '),
                            position: 'topCenter'
                        });
                    }

                    if (res.saved > 0) {
                        iziToast.success({
                            title: 'Berhasil',
                            message: res.saved + ' barcode berhasil ditambahkan.',
                            position: 'topCenter'
                        });
                    }
                }
            },
            error: function (jqXHR) {
                let res = jqXHR.responseJSON;

                iziToast.error({
                    title: 'Error',
                    message: (res && res.message) ? res.message : 'Gagal mengupload file.',
                    position: 'topCenter'
                });
            }
        });
    }

    function viewDetailBarcodeRiTemp(id_item, id_jo, id_bppb) {
        $('#detail_barcode_ri_temp_content').data('group', { id_item: id_item, id_jo: id_jo, id_bppb: id_bppb });
        $('#detail_barcode_ri_temp_content').html('');
        $('#modal-detail-barcode-ri-temp').modal('show');
        loadDetailBarcodeRiTemp(id_item, id_jo, id_bppb);
    }

    function loadDetailBarcodeRiTemp(id_item, id_jo, id_bppb) {
        return $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route("get-detail-group-barcode-ri-temp") }}',
            type: 'get',
            data: {
                id_item: id_item,
                id_jo: id_jo,
                id_bppb: id_bppb,
            },
            success: function (res) {
                if (res) {
                    $('#detail_barcode_ri_temp_content').html(res);

                    $('#detail_barcode_ri_temp_content #lokasi_all_select').select2({
                        theme: 'bootstrap4',
                        dropdownParent: $('#modal-detail-barcode-ri-temp'),
                        placeholder: '-- Pilih Lokasi --',
                        allowClear: true,
                        width: '100%',
                    }).on('change', function () {
                        applyLokasiAll();
                    });

                    $('#detail_barcode_ri_temp_content select[data-field="kode_lok"]').select2({
                        theme: 'bootstrap4',
                        dropdownParent: $('#modal-detail-barcode-ri-temp'),
                        placeholder: '-- Pilih --',
                        allowClear: true,
                        width: '160px',
                    });
                }
            }
        });
    }

    function saveDetailBarcodeRiTemp(id, btn) {
        let $row = $(btn).closest('tr');
        let qty_retur  = $row.find('input[data-field="qty_retur"]').val();
        let qty_reject = $row.find('input[data-field="qty_reject"]').val();
        let kode_lok   = $row.find('select[data-field="kode_lok"]').val();

        return $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route("update-barcode-ri-temp-qty") }}',
            type: 'post',
            data: { id: id, qty_retur: qty_retur, qty_reject: qty_reject, kode_lok: kode_lok },
            success: function (res) {
                $row.css('background-color', kode_lok ? '#d4edda' : '#f8d7da');
                iziToast.success({
                    title: 'Berhasil',
                    message: 'Data berhasil disimpan.',
                    position: 'topCenter'
                });
                datatable.ajax.reload();
            },
            error: function () {
                iziToast.error({
                    title: 'Error',
                    message: 'Gagal menyimpan data.',
                    position: 'topCenter'
                });
            }
        });
    }

    function saveAllDetailBarcodeRiTemp(btn) {
        let rows = [];

        $('#detail_barcode_ri_temp_content tbody tr').each(function () {
            let id         = $(this).data('id');
            let qty_retur  = $(this).find('input[data-field="qty_retur"]').val();
            let qty_reject = $(this).find('input[data-field="qty_reject"]').val();
            let kode_lok   = $(this).find('select[data-field="kode_lok"]').val();

            if (id) {
                rows.push({ id: id, qty_retur: qty_retur, qty_reject: qty_reject, kode_lok: kode_lok });
            }
        });

        if (rows.length === 0) return;

        $(btn).prop('disabled', true);

        return $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: '{{ route("update-barcode-ri-temp-qty-all") }}',
            type: 'post',
            data: { rows: rows },
            success: function () {
                $('#detail_barcode_ri_temp_content tbody tr').each(function () {
                    let kode_lok = $(this).find('select[data-field="kode_lok"]').val();
                    $(this).css('background-color', kode_lok ? '#d4edda' : '#f8d7da');
                });
                iziToast.success({
                    title: 'Berhasil',
                    message: rows.length + ' baris berhasil disimpan.',
                    position: 'topCenter'
                });
                datatable.ajax.reload();
            },
            error: function () {
                iziToast.error({
                    title: 'Error',
                    message: 'Gagal menyimpan data.',
                    position: 'topCenter'
                });
            },
            complete: function () {
                $(btn).prop('disabled', false);
            }
        });
    }

    function deleteDetailBarcodeRiTemp(id, btn) {
        return $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route("delete-barcode-ri-temp-row") }}',
            type: 'post',
            data: {
                id: id,
            },
            success: function (res) {
                $(btn).closest('tr').remove();
                datatable.ajax.reload();

                if ($('#detail_barcode_ri_temp_content tbody tr').length === 0) {
                    $('#modal-detail-barcode-ri-temp').modal('hide');
                }
            },
            error: function () {
                iziToast.error({
                    title: 'Error',
                    message: 'Gagal menghapus barcode.',
                    position: 'topCenter'
                });
            }
        });
    }

    function deleteGroupBarcodeRiTemp(id_item, id_jo, id_bppb) {
        Swal.fire({
            title: 'Hapus Data?',
            text: 'Semua barcode pada baris ini akan dihapus dari Data Detail.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route("delete-barcode-ri-temp-group") }}',
                    type: 'post',
                    data: {
                        id_item: id_item,
                        id_jo: id_jo,
                        id_bppb: id_bppb,
                    },
                    success: function (res) {
                        datatable.ajax.reload();
                    },
                    error: function () {
                        iziToast.error({
                            title: 'Error',
                            message: 'Gagal menghapus data.',
                            position: 'topCenter'
                        });
                    }
                });
            }
        });
    }

    function clearBarcodeRiTemp() {
        Swal.fire({
            title: 'Clear Semua Data Temporary?',
            text: 'Semua barcode yang sudah di-scan / di-upload akan dihapus. Tindakan ini tidak bisa dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus Semua',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    url: '{{ route("clear-barcode-ri-temp") }}',
                    type: 'post',
                    success: function (res) {
                        datatable.ajax.reload();
                        $('#txt_idsupp').val('');
                        $('#txt_supp').val('');
                        $('#txt_noinvoice').val('');
                        iziToast.success({
                            title: 'Berhasil',
                            message: 'Data temporary berhasil dihapus.',
                            position: 'topCenter'
                        });
                    },
                    error: function () {
                        iziToast.error({
                            title: 'Error',
                            message: 'Gagal menghapus data temporary.',
                            position: 'topCenter'
                        });
                    }
                });
            }
        });
    }

    function applyLokasiAll() {
        let val = $('#detail_barcode_ri_temp_content #lokasi_all_select').val();
        if (!val) return;
        $('#detail_barcode_ri_temp_content select[data-field="kode_lok"]').val(val).trigger('change');
        $('#detail_barcode_ri_temp_content tbody tr').each(function () {
            $(this).css('background-color', '#d4edda');
        });
    }

    function validateAndSubmitRiForm(e, evt) {
        evt.preventDefault();

        if (!$('#txt_type_bc').val()) {
            Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Tipe BC wajib dipilih sebelum menyimpan.' });
            $('#txt_type_bc').next('.select2-container').find('.select2-selection').addClass('border border-danger');
            return;
        }
        $('#txt_type_bc').next('.select2-container').find('.select2-selection').removeClass('border border-danger');

        let dtData = datatable.rows().data().toArray();
        if (dtData.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Tidak ada data barcode.' });
            return;
        }

        let missingLok = dtData.filter(function (d) {
            return parseInt(d.jml_lok || 0) < parseInt(d.jml_barcode || 0);
        });

        if (missingLok.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Lokasi Wajib Diisi',
                text: 'Terdapat ' + missingLok.length + ' kelompok barcode yang belum memiliki lokasi. Silakan isi lokasi di detail barcode terlebih dahulu.'
            });
            return;
        }

        submitForm(e, evt);
    }
</script>
@endsection
