@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
<form action="{{ route('store-ro-barcode') }}" method="post" id="store-outmaterial" onsubmit="validateAndSubmitRoForm(this, event)">
    @csrf

    {{-- ─── Header ─────────────────────────────────────────────────────────── --}}
    <div class="card card-sb card-outline">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">Data Header</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
        <div class="card-body">
    <div class="form-group row">
    <div class="col-md-4">
        <div class="row">
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No RO</small></label>
                @foreach ($kode_gr as $kodegr)
                <input type="text" class="form-control " id="txt_noro" name="txt_noro" value="{{ $kodegr->kode }}" readonly>
                @endforeach
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No PO</small></label>
                <input type="text" class="form-control " id="txt_nopo" name="txt_nopo" value="" readonly>
                </div>
            </div>
            </div>

            

            <!-- <div class="col-md-6">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl BPB</small></label>
                <input type="date" class="form-control form-control" id="txt_tgl_bpb" name="txt_tgl_bpb"
                        value="" onchange="get_nobpb(this.value)">
                </div>
            </div>
            </div> -->

            <!-- <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No BPB</small></label>
                <select class="form-control select2req" id="txt_nobpb" name="txt_nobpb" style="width: 100%;" onchange="getlistdata();getSupp()">
                </select>
                </div>
            </div>
            </div> -->

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Jenis Defect</small></label>
                <select class="form-control select2bs4" id="txt_jns_def" name="txt_jns_def" style="width: 100%;">
                    <option selected="selected" value="">Pilih Defect</option>
                        @foreach ($def_type as $def)
                    <option value="{{ $def->nama_defect }}">
                                {{ $def->nama_defect }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Jenis Pengeluaran</small></label>
                <select class="form-control select2bs4" id="txt_jns_klr" name="txt_jns_klr" style="width: 100%;">
                    <option selected="selected" value="">Pilih Pengeluaran</option>
                        @foreach ($jns_klr as $jnsklr)
                    <option value="{{ $jnsklr->isi }}">
                                {{ $jnsklr->tampil }}
                    </option>
                        @endforeach
                </select>
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
                <label><small>Tgl RO</small></label>
                <input type="date" class="form-control form-control" id="txt_tgl_ro" name="txt_tgl_ro"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div>

            


            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tipe BC</small></label>
                <select class="form-control select2bs4" id="txt_type_bc" name="txt_type_bc" style="width: 100%;" onchange="get_tujuan(this.value)">
                    <option selected="selected" value="">Pilih Tipe</option>
                        @foreach ($mtypebc as $bc)
                    <option value="{{ $bc->nama_pilihan }}">
                                {{ $bc->nama_pilihan }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tujuan Pemasukan</small></label>
                <select class="form-control select2bs4" id="txt_tujuan" name="txt_tujuan" style="width: 100%;">
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Jenis Return</small></label>
                <select class="form-control select2bs4" id="txt_stat_rtn" name="txt_stat_rtn" style="width: 100%;">
                    <option selected="selected" value="">Pilih Status Retur</option>
                        @foreach ($status_replac as $replac)
                    <option value="{{ $replac->nama_pilihan }}">
                                {{ $replac->nama_pilihan }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

        </div>
    </div>

    <div class="col-md-5">
        <div class="row">

            <input type="hidden" class="form-control " id="txt_no_aju" name="txt_no_aju" value="" >
            <input type="hidden" class="form-control form-control" id="txt_tgl_aju" name="txt_tgl_aju"
                        value="{{ date('Y-m-d') }}">
            <input type="hidden" class="form-control " id="txt_no_daftar" name="txt_no_daftar" value="" >
            <input type="hidden" class="form-control form-control" id="txt_tgl_daftar" name="txt_tgl_daftar"
                        value="{{ date('Y-m-d') }}">

            <!-- <div class="col-md-7">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No Aju</small></label>
                <input type="hidden" class="form-control " id="txt_no_aju" name="txt_no_aju" value="" >
                </div>
            </div>
            </div>

            <div class="col-md-5">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl Aju</small></label>
                <input type="hidden" class="form-control form-control" id="txt_tgl_aju" name="txt_tgl_aju"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div>

            <div class="col-md-7">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No Daftar</small></label>
                <input type="hidden" class="form-control " id="txt_no_daftar" name="txt_no_daftar" value="" >
                </div>
            </div>
            </div>

            <div class="col-md-5">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl Daftar</small></label>
                <input type="hidden" class="form-control form-control" id="txt_tgl_daftar" name="txt_tgl_daftar"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div> -->

            <div class="col-md-12">
    <div class="mb-1">
        <div class="form-group">
            <label><small>Dikirim Ke</small></label>
            <select class="form-control select2bs4" id="txt_dikirim" name="txt_dikirim">
                <option value="">-- Pilih Supplier --</option>
                @foreach($msupplier as $supp)
                    <option value="{{ $supp->id_supplier }}">{{ $supp->Supplier }}</option>
                @endforeach
            </select>
            <input type="hidden" id="txt_idsupp" name="txt_idsupp" readonly>
            <input type="hidden" id="txt_nows" name="txt_nows" readonly>
        </div>
    </div>
</div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Catatan</small></label>
                <textarea type="text" rows="4" class="form-control " id="txt_notes" name="txt_notes" value="" > </textarea>
                <input type="hidden" class="form-control" id="jumlah_data" name="jumlah_data" readonly>
                <input type="hidden" class="form-control" id="jumlah_qty" name="jumlah_qty" readonly>
                </div>
            </div>
            </div>
        </div>
    </div>
    </div>
</div>
</div>

    {{-- ─── Detail / Barcode ───────────────────────────────────────────────── --}}
    <div class="card card-sb card-outline">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">Data Barcode</h5>
        </div>
        <div class="card-body">

            {{-- Tab Scan / Upload --}}
            <ul class="nav nav-tabs" id="barcode-input-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-scan-barcode" role="tab">
                        <i class="fa-solid fa-barcode"></i> Scan / Tempel Barcode
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-upload-excel" role="tab">
                        <i class="fa-solid fa-file-excel"></i> Upload Excel
                    </a>
                </li>
            </ul>
            <div class="tab-content border border-top-0 rounded-bottom mb-3">
                {{-- Scan --}}
                <div class="tab-pane fade show active" id="tab-scan-barcode" role="tabpanel">
                    <div class="p-3" style="background:linear-gradient(135deg,#f0f8ff 0%,#e8f4fd 100%);border-radius:0 0 6px 6px;">
                        <div class="d-flex gap-3 align-items-end">
                            <div class="flex-grow-1">
                                <label class="mb-1" style="font-size:.8rem;font-weight:600;color:#555;">
                                    <i class="fa-solid fa-barcode text-info me-1"></i>Input Barcode
                                </label>
                                <textarea id="bulk_barcode_input" class="form-control" rows="4"
                                    placeholder="Scan atau tempel barcode di sini&#10;Pisahkan dengan Enter / spasi / koma..."
                                    style="font-family:monospace;font-size:.9rem;resize:none;border-color:#90c4e8;background:#fff;"></textarea>
                            </div>
                            <button type="button" class="btn btn-info px-4 py-2" onclick="sendBarcodeList()"
                                style="font-size:.9rem;white-space:nowrap;border-radius:6px;">
                                <i class="fa fa-paper-plane me-1"></i>Send
                            </button>
                        </div>
                    </div>
                </div>
                {{-- Upload --}}
                <div class="tab-pane fade" id="tab-upload-excel" role="tabpanel">
                    <div class="p-3" style="background:linear-gradient(135deg,#f2fff4 0%,#e8f8eb 100%);border-radius:0 0 6px 6px;">
                        <div class="d-flex gap-3 align-items-end flex-wrap">
                            <div class="flex-grow-1" style="min-width:220px;">
                                <label class="mb-1" style="font-size:.8rem;font-weight:600;color:#555;">
                                    <i class="fa-solid fa-file-excel text-success me-1"></i>Pilih File Excel (.xlsx / .xls)
                                </label>
                                <input type="file" id="excel_barcode_file" class="form-control" accept=".xlsx,.xls"
                                    style="border-color:#7dba84;background:#fff;">
                            </div>
                            <div class="d-flex gap-2" style="padding-bottom:1px;">
                                <button type="button" class="btn btn-success px-4 py-2" onclick="uploadBarcodeExcel()"
                                    style="font-size:.9rem;border-radius:6px;">
                                    <i class="fa fa-upload me-1"></i>Upload
                                </button>
                                <a href="{{ route('download-template-ro-bc') }}"
                                    class="btn btn-outline-secondary px-3 py-2"
                                    style="font-size:.9rem;border-radius:6px;">
                                    <i class="fa fa-download me-1"></i>Template
                                </a>
                            </div>
                        </div>
                        <small class="d-block mt-2" style="color:#666;">
                            <i class="fa fa-circle-info text-info me-1"></i>
                            Kolom file: <strong>no_barcode</strong> (A), <strong>qty</strong> (B). Qty kosong = otomatis ambil dari lokasi.
                        </small>
                    </div>
                </div>
            </div>

            {{-- DataTable list barcode --}}
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-head-fixed w-100 text-nowrap" style="font-size:.82rem;">
                    <thead>
                        <tr>
                            <th class="text-center" style="font-size:0.6rem;width:150px;">WS</th>
                            <th class="text-center" style="font-size:0.6rem;width:150px;">No PO</th>
                            <th class="text-center" style="font-size:0.6rem;width:150px;">ID Item</th>
                            <th class="text-center" style="font-size:0.6rem;width:250px;">Deskripsi</th>
                            <th class="text-center" style="font-size:0.6rem;width:100px;">Jml Barcode</th>
                            <th class="text-center" style="font-size:0.6rem;width:120px;">Qty Aktual</th>
                            <th class="text-center" style="font-size:0.6rem;width:120px;">Qty RO</th>
                            <th class="text-center" style="font-size:0.6rem;width:100px;">Satuan</th>
                            <th class="text-center" style="font-size:0.6rem;width:100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            {{-- Bottom buttons --}}
            <div class="d-flex justify-content-between align-items-center mt-3 px-1 pb-1">
                <a href="{{ route('retur-material') }}" class="btn btn-danger px-3" style="border-radius:6px;">
                    <i class="fas fa-arrow-circle-left"></i> Kembali
                </a>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-warning px-3" onclick="clearRoBarcodeTemp()" style="border-radius:6px;">
                        <i class="fa-solid fa-trash-can"></i> Clear
                    </button>
                    <button class="btn btn-sb px-4" style="border-radius:6px;">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan
                    </button>
                </div>
            </div>

        </div>
    </div>

</form>

<div class="modal fade" id="modal-detail-ro-barcode-temp">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-sb text-light">
                <h4 class="modal-title">Detail Barcode</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="detail_ro_barcode_temp_content"></div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Tutup</button>
                <button type="button" class="btn btn-primary" onclick="saveAllDetailRoBarcodeTemp(this)"><i class="fa-solid fa-floppy-disk"></i> Simpan Semua</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        $('.select2bs4').select2({ theme: 'bootstrap4' });

        // ─── Helpers ──────────────────────────────────────────────────────────

        function escHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // ─── DataTable ────────────────────────────────────────────────────────

        let datatable = $('#datatable').DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            paging: false,
            searching: false,
            ajax: {
                url: '{{ route("get-grouped-ro-barcode-temp") }}',
            },
            columns: [
                { data: 'no_ws' },
                { data: 'no_po' },
                { data: 'id_item' },
                { data: 'itemdesc' },
                { data: 'jml_barcode' },
                { data: 'qty_aktual', className: 'text-end' },
                { data: 'qty_ro', className: 'text-end' },
                { data: 'unit' },
                { data: null },
            ],
            columnDefs: [
                {
                    targets: [8],
                    className: 'text-center',
                    render: (data, type, row) =>
                        '<div class="d-flex gap-1 justify-content-center">'
                        + '<button type="button" class="btn btn-sm btn-info" onclick="viewDetailRoBarcodeTemp(' + row.id_item + ', \'' + escHtml(row.no_ws) + '\')" title="Lihat Detail"><i class="fa-solid fa-eye"></i></button>'
                        + '<button type="button" class="btn btn-sm btn-danger" onclick="deleteGroupRoBarcodeTemp(' + row.id_item + ', \'' + escHtml(row.no_ws) + '\')" title="Hapus"><i class="fa-solid fa-trash"></i></button>'
                        + '</div>'
                },
            ],
        });

        // ─── Send barcode ─────────────────────────────────────────────────────

        function sendBarcodeList() {
            let raw = $('#bulk_barcode_input').val().trim();
            if (!raw) return;

            $.ajax({
                url: '{{ route("insert-ro-barcode-temp") }}',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { barcodes: raw },
                success: function (res) {
                    $('#bulk_barcode_input').val('');
                    datatable.ajax.reload();

                    let icon = (res.not_found.length > 0 || res.po_mismatch.length > 0) ? 'warning' : 'success';
                    iziToast[icon === 'success' ? 'success' : 'warning']({
                        title: icon === 'success' ? 'Berhasil' : 'Peringatan',
                        message: res.message,
                        position: 'topCenter',
                        timeout: 4000,
                    });
                },
                error: function () {
                    iziToast.error({ title: 'Error', message: 'Terjadi kesalahan.', position: 'topCenter' });
                }
            });
        }

        // ─── Upload Excel ─────────────────────────────────────────────────────

        function uploadBarcodeExcel() {
            let file = $('#excel_barcode_file')[0].files[0];
            if (!file) {
                iziToast.warning({ title: 'Peringatan', message: 'Pilih file terlebih dahulu.', position: 'topCenter' });
                return;
            }

            let fd = new FormData();
            fd.append('excel_file', file);
            fd.append('_token', $('meta[name="csrf-token"]').attr('content'));

            $.ajax({
                url: '{{ route("upload-ro-barcode-temp") }}',
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#excel_barcode_file').val('');
                    datatable.ajax.reload();

                    let icon = (res.not_found.length > 0 || res.po_mismatch.length > 0) ? 'warning' : 'success';
                    iziToast[icon === 'success' ? 'success' : 'warning']({
                        title: icon === 'success' ? 'Berhasil' : 'Peringatan',
                        message: res.message,
                        position: 'topCenter',
                        timeout: 5000,
                    });
                },
                error: function () {
                    iziToast.error({ title: 'Error', message: 'Gagal mengupload file.', position: 'topCenter' });
                }
            });
        }

        // ─── Detail modal ─────────────────────────────────────────────────────

        function viewDetailRoBarcodeTemp(id_item, no_ws) {
            $('#detail_ro_barcode_temp_content').html('');
            $('#modal-detail-ro-barcode-temp').modal('show');
            loadDetailRoBarcodeTemp(id_item, no_ws);
        }

        function loadDetailRoBarcodeTemp(id_item, no_ws) {
            $.ajax({
                url: '{{ route("get-detail-group-ro-barcode-temp") }}',
                type: 'GET',
                data: { id_item: id_item, no_ws: no_ws },
                success: function (res) {
                    $('#detail_ro_barcode_temp_content').html(res);
                }
            });
        }

        function saveDetailRoBarcodeTemp(id, btn) {
            let qty_ro = $(btn).closest('tr').find('input[data-field="qty_ro"]').val();

            $.ajax({
                url: '{{ route("update-ro-barcode-qty") }}',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { id: id, qty_ro: qty_ro },
                success: function () {
                    iziToast.success({ title: 'Berhasil', message: 'Qty berhasil disimpan.', position: 'topCenter' });
                    datatable.ajax.reload();
                },
                error: function () {
                    iziToast.error({ title: 'Error', message: 'Gagal menyimpan qty.', position: 'topCenter' });
                }
            });
        }

        function saveAllDetailRoBarcodeTemp(btn) {
            let rows = [];

            $('#detail_ro_barcode_temp_content tbody tr').each(function () {
                let id     = $(this).data('id');
                let qty_ro = $(this).find('input[data-field="qty_ro"]').val();

                if (id) {
                    rows.push({ id: id, qty_ro: qty_ro });
                }
            });

            if (rows.length === 0) return;

            $(btn).prop('disabled', true);

            $.ajax({
                url: '{{ route("update-ro-barcode-qty-all") }}',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { rows: rows },
                success: function () {
                    iziToast.success({ title: 'Berhasil', message: rows.length + ' baris berhasil disimpan.', position: 'topCenter' });
                    datatable.ajax.reload();
                },
                error: function () {
                    iziToast.error({ title: 'Error', message: 'Gagal menyimpan data.', position: 'topCenter' });
                },
                complete: function () {
                    $(btn).prop('disabled', false);
                }
            });
        }

        function deleteDetailRoBarcodeTemp(id, btn) {
            Swal.fire({
                icon: 'question',
                title: 'Hapus barcode ini?',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
            }).then(r => {
                if (!r.isConfirmed) return;
                $.post('{{ route("delete-ro-barcode-temp") }}', {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    id: id,
                }, function () {
                    $(btn).closest('tr').remove();
                    datatable.ajax.reload();

                    if ($('#detail_ro_barcode_temp_content tbody tr').length === 0) {
                        $('#modal-detail-ro-barcode-temp').modal('hide');
                    }
                });
            });
        }

        // ─── Delete group ─────────────────────────────────────────────────────

        function deleteGroupRoBarcodeTemp(id_item, no_ws) {
            Swal.fire({
                icon: 'warning',
                title: 'Hapus Data?',
                text: 'Semua barcode pada baris ini akan dihapus.',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
            }).then(r => {
                if (!r.isConfirmed) return;
                $.post('{{ route("delete-ro-barcode-temp-group") }}', {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    id_item: id_item,
                    no_ws: no_ws,
                }, function () {
                    datatable.ajax.reload();
                });
            });
        }

        // ─── Clear all temp ───────────────────────────────────────────────────

        function clearRoBarcodeTemp() {
            Swal.fire({
                icon: 'warning',
                title: 'Clear Temporary?',
                text: 'Semua barcode yang belum disimpan akan dihapus.',
                showCancelButton: true,
                confirmButtonText: 'Ya, clear',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#e09600',
            }).then(r => {
                if (!r.isConfirmed) return;
                $.post('{{ route("clear-ro-barcode-temp") }}', {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                }, function () {
                    datatable.ajax.reload();
                    iziToast.success({ title: 'Cleared', message: 'Data temporary dihapus.', position: 'topCenter' });
                });
            });
        }

        // ─── Tujuan dropdown ─────────────────────────────────────────────────

        function get_tujuan(val) {
            $.ajax({
                url: '{{ route("get-tujuan-pemasukan-ro") }}',
                type: 'GET',
                data: { type_bc: val },
                success: function (res) {
                    document.getElementById('txt_tujuan').innerHTML = res;
                    $('#txt_tujuan').trigger('change');
                }
            });
        }

        // ─── Validate & submit ───────────────────────────────────────────────

        function validateAndSubmitRoForm(e, evt) {
            evt.preventDefault();

            let required = [
                { id: 'txt_type_bc', label: 'Tipe BC' },
                { id: 'txt_jns_klr', label: 'Jenis Pengeluaran' },
                { id: 'txt_dikirim', label: 'Dikirim Ke' },
            ];

            for (let f of required) {
                let $el = $('#' + f.id);
                if (!$el.val()) {
                    Swal.fire({ icon: 'warning', title: 'Peringatan', text: f.label + ' wajib diisi sebelum menyimpan.' });
                    $el.next('.select2-container').find('.select2-selection').addClass('border border-danger');
                    return;
                }
                $el.next('.select2-container').find('.select2-selection').removeClass('border border-danger');
            }

            let dtData = datatable.rows().data().toArray();
            if (dtData.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Tidak ada data barcode.' });
                return;
            }

            submitForm(e, evt);
        }
    </script>
@endsection
