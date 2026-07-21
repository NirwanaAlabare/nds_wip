@extends('layouts.index', ['containerFluid' => true])

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style type="text/css">
        .marginnya{
            margin-left: 350px;
            margin-right: 350px;
            margin-top: 10px;
        }
    </style>
@endsection

@section('content')
<form action="{{ route('update-retur-inmaterial-fabric') }}" method="post" id="store-inmaterial" onsubmit="validateAndSubmitRoForm(this, event)">
    @csrf
    <input type="hidden" name="txt_id" id="txt_id" value="{{ $kode_gr->id }}">
    <input type="hidden" name="txt_source_form" value="{{ $kode_gr->source_form ?? 'Supplier' }}">
    <div class="card card-sb card-outline marginnya">
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

            <div class="col-md-3">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No RI</small></label>
                <input type="text" class="form-control " id="txt_no_ri" name="txt_no_ri" value="{{ $kode_gr->no_dok }}" readonly>
                </div>
            </div>
            </div>

            <div class="col-md-3">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl RI</small></label>
                <div class="input-group">
                    <input type="text" class="form-control" id="txt_tgl_ri" name="txt_tgl_ri" autocomplete="off" readonly
                            value="{{ $kode_gr->tgl_dok }}">
                    <span class="input-group-text" id="txt_tgl_ri_icon" style="cursor: pointer;"><i class="fas fa-calendar-alt"></i></span>
                </div>
                </div>
            </div>
            </div>

            <div class="col-md-3">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tujuan Pemasukan</small></label>
                <select class="form-control select2bs4" id="txt_tujuan" name="txt_tujuan" style="width: 100%;">
                    @if ($tujuan_existing)
                    <option selected="selected" value="{{ $tujuan_existing }}">{{ $tujuan_existing }}</option>
                    @endif
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-3">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Supplier</small></label>
                <input type="text" class="form-control " id="txt_supp" name="txt_supp" value="{{ $kode_gr->supplier }}" readonly>
                <input type="hidden" class="form-control " id="txt_idsupp" name="txt_idsupp" value="{{ $id_supplier_existing }}" readonly>
                </div>
            </div>
            </div>

            <div class="col-md-3">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl SJ</small></label>
                <input type="date" class="form-control form-control" id="txt_tgl_sj" name="txt_tgl_sj" value="{{ $kode_gr->tgl_shipp }}"
                         onchange="get_nobppb(this.value)">
                </div>
            </div>
            </div>

            <div class="col-md-3">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Surat jalan Asal</small></label>
                <select class="form-control select2bs4" id="txt_sj_asal" name="txt_sj_asal" style="width: 100%;" onchange="get_supplier();getlistdata();">
                    <option selected="selected" value="{{ $kode_gr->ori_dok }}">{{ $kode_gr->ori_dok }}</option>
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-3">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Jenis Retur</small></label>
                <select class="form-control select2bs4" id="txt_jns_rtr" name="txt_jns_rtr" style="width: 100%;" >
                    @if ($kode_gr->jns_retur)
                    <option selected="selected" value="{{ $kode_gr->jns_retur }}">{{ $kode_gr->jns_retur }}</option>
                    @endif
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-3">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No PO</small></label>
                <input type="text" class="form-control " id="txt_no_po" name="txt_no_po" value="{{ $kode_gr->no_po }}" readonly>
                </div>
            </div>
            </div>

            <div class="col-md-3">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tipe BC <span class="text-danger">*</span></small></label>
                <select class="form-control select2bs4" id="txt_type_bc" name="txt_type_bc" style="width: 100%;" onchange="get_tujuan(this.value)">
                    <option value="" {{ !$kode_gr->type_bc ? 'selected' : '' }}>Pilih Tipe</option>
                        @foreach ($mtypebc as $bc)
                    <option value="{{ $bc->nama_pilihan }}" {{ $kode_gr->type_bc == $bc->nama_pilihan ? 'selected' : '' }}>
                                {{ $bc->nama_pilihan }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-3">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tipe Pembelian <span class="text-danger">*</span></small></label>
                <select class="form-control select2bs4" id="txt_type_pch" name="txt_type_pch"
                style="width: 100%;">
                <option value="" {{ !$kode_gr->type_pch ? 'selected' : '' }}>Pilih Tipe</option>
                @foreach ($pch_type as $pch)
                <option value="{{ $pch->nama_pilihan }}" {{ $kode_gr->type_pch == $pch->nama_pilihan ? 'selected' : '' }}>
                    {{ $pch->nama_pilihan }}
                </option>
                @endforeach
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-3">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No Invoice/ No SJ <span class="text-danger">*</span></small></label>
                <input type="text" class="form-control " id="txt_noinvoice" name="txt_noinvoice" value="{{ $kode_gr->no_invoice }}" required>
                </div>
            </div>
            </div>

            <div class="col-md-3">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Jenis Material</small></label>
                    <input type="text" class="form-control " id="txt_tom" name="txt_tom" value="Fabric" readonly>
                    <input type="hidden" class="form-control" id="jumlah_data" name="jumlah_data" readonly>
                    <input type="hidden" class="form-control" id="jumlah_qty" name="jumlah_qty" readonly>
               </div>
            </div>
            </div>

            <div class="col-md-6">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Keterangan</small></label>
                <input type="text" class="form-control " id="txt_keterangan" name="txt_keterangan" value="{{ $kode_gr->deskripsi }}" >
                </div>
            </div>
            </div>

    <div style="display: none;">
            <input type="text" class="form-control " id="txt_no_kk" name="txt_no_kk" value="{{ $kode_gr->no_kontrak }}" >
            <input type="text" class="form-control " id="txt_aju_num" name="txt_aju_num" value="{{ $kode_gr->no_aju }}" >
            <input type="date" class="form-control form-control" id="txt_tgl_aju" name="txt_tgl_aju"
                    value="{{ $kode_gr->tgl_aju }}">
            <input type="text" class="form-control " id="txt_faktur" name="txt_faktur" value="{{ $kode_gr->no_faktur }}" >
            <input type="date" class="form-control form-control" id="txt_tgl_faktur" name="txt_tgl_faktur"
                    value="{{ $kode_gr->tgl_faktur }}">
            <input type="text" class="form-control " id="txt_reg" name="txt_reg" value="{{ $kode_gr->no_daftar }}" >
            <input type="date" class="form-control form-control" id="txt_tgl_reg" name="txt_tgl_reg"
                    value="{{ $kode_gr->tgl_daftar }}">

        </div>
    </div>
</div>
</div>

    <div class="card card-sb card-outline marginnya">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                Data Detail
            </h5>
        </div>
    <div class="card-body">
    <div class="form-group row">
    <div>
            <table id="datatable" class="table table-bordered table-striped table-head-fixed table w-100" style="table-layout: fixed;">
                <thead>
                    <tr>
                        <th class="text-center" style="font-size: 0.75rem;">WS</th>
                        <th class="text-center" style="font-size: 0.75rem;">ID Item</th>
                        <th class="text-center" style="font-size: 0.75rem;">Kode Barang</th>
                        <th class="text-center" style="font-size: 0.75rem;">Deskripsi</th>
                        <th class="text-center" style="font-size: 0.75rem;">Qty SJ</th>
                        <th class="text-center" style="font-size: 0.75rem;">Satuan</th>
                        <th class="text-center" style="font-size: 0.75rem;">Qty Return</th>
                        <th class="text-center" style="font-size: 0.75rem;">Qty Reject</th>
                        <th class="text-center" style="display: none;">Berat Kotor</th>
                        <th class="text-center" style="display: none;">Berat Bersih</th>
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
                    <button class="btn btn-sb float-end mt-2 ml-2"><i class="fa-solid fa-floppy-disk"></i> Update</button>
                    <a href="{{ route('retur-inmaterial') }}" class="btn btn-danger float-end mt-2">
                    <i class="fas fa-arrow-circle-left"></i> Kembali</a>
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

        // ─── Tgl RI datepicker (batasi periode closed) ─────────────────────────

        let minTglRo = @json($min_tgl_ro ?? '');
        let closedPeriods = {!! json_encode($closed_periods ?? []) !!};
        let existingOriDok = @json($kode_gr->ori_dok);
        let existingTujuan = @json($tujuan_existing);

        function formatDateYmd(date) {
            let m = date.getMonth() + 1;
            let d = date.getDate();
            return date.getFullYear() + '-' + (m < 10 ? '0' : '') + m + '-' + (d < 10 ? '0' : '') + d;
        }

        $('#txt_tgl_ri').datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: minTglRo ? minTglRo : null,
            beforeShowDay: function (date) {
                let ymd = formatDateYmd(date);
                for (let p of closedPeriods) {
                    if (ymd >= p.tgl_awal && ymd <= p.tgl_akhir) {
                        return [false, '', 'Periode sudah closed'];
                    }
                }
                return [true, ''];
            }
        });

        $('#txt_tgl_ri_icon').on('click', function () {
            $('#txt_tgl_ri').datepicker('show');
        });

        function validateAndSubmitRoForm(e, evt) {
            let tglRo = $('#txt_tgl_ri').val();

            if (minTglRo && tglRo < minTglRo) {
                evt.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Tgl RI tidak boleh sebelum ' + minTglRo + ' (periode sudah closed).' });
                return;
            }

            for (let p of closedPeriods) {
                if (tglRo >= p.tgl_awal && tglRo <= p.tgl_akhir) {
                    evt.preventDefault();
                    Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Tgl RI tidak boleh pada periode ' + p.tgl_awal + ' s/d ' + p.tgl_akhir + ' (sudah closed).' });
                    return;
                }
            }

            let requiredFields = [
                { id: 'txt_type_bc', label: 'Tipe BC' },
                { id: 'txt_type_pch', label: 'Tipe Pembelian' },
                { id: 'txt_noinvoice', label: 'No Invoice/ No SJ' },
            ];
            for (let f of requiredFields) {
                if (!$('#' + f.id).val()) {
                    evt.preventDefault();
                    Swal.fire({ icon: 'warning', title: 'Peringatan', text: f.label + ' wajib diisi.' });
                    return;
                }
            }

            if (datatable.search()) {
                evt.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Data Detail sedang difilter/dicari. Kosongkan pencarian sebelum menyimpan.' });
                return;
            }

            submitForm(e, evt);
        }

        //Reset saat pertama load tidak diperlukan di halaman edit (form sudah terisi data lama)

        function get_nobppb(val) {
           return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-no-bppb") }}',
                type: 'get',
                data: {
                    tgl_ri: $('#txt_tgl_sj').val(),
                    no_dok: $('#txt_no_ri').val(),
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('txt_sj_asal').innerHTML = res;
                        $('#txt_sj_asal').val(existingOriDok).trigger('change');
                    }
                },
            });
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
                        if (existingTujuan) {
                            $('#txt_tujuan').val(existingTujuan).trigger('change');
                            existingTujuan = null;
                        }
                    }
                },
            });
        }

        function get_supplier() {
           return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-supplier-ri") }}',
                type: 'get',
                data: {
                    no_bppb: $('#txt_sj_asal').val(),
                },
                success: function (res) {
                    if (res) {
                        // console.log(res[0].jml)
                        $('#txt_supp').val(res[0].supplier);
                        $('#txt_no_po').val(res[0].no_po);
                        $('#txt_idsupp').val(res[0].id_supplier);
                    }
                },
            });
        }


        async function getlistdata() {
            return datatable.ajax.reload(() => {
                document.getElementById('jumlah_data').value = datatable.data().count();
            });
        }

        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            paging: false,
            searching: true,
            autoWidth: false,
            ajax: {
                url: '{{ route("get-list-bppb-edit") }}',
                data: function (d) {
                    d.sj_asal = $('#txt_sj_asal').val();
                    d.no_dok = $('#txt_no_ri').val();
                },
            },
            columns: [
                {
                    data: 'kpno'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'goods_code'
                } ,
                {
                    data: 'itemdesc'
                },
                {
                    data: 'qty'
                },
                {
                    data: 'unit'
                },
                {
                    data: 'qty'
                },
                {
                    data: 'qty'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'id_jo'
                },
                {
                    data: 'id_bppb'
                },
                {
                    data: 'kpno'
                }
            ],
            columnDefs: [
                {
                    targets: [2],
                    visible: false
                },
                {
                    targets: [0],
                    width: '9%'
                },
                {
                    targets: [1],
                    width: '9%'
                },
                {
                    targets: [3],
                    width: '27%',
                    className: 'text-wrap'
                },
                {
                    targets: [4],
                    width: '8%'
                },
                {
                    targets: [5],
                    width: '8%'
                },
                {
                    targets: [6],
                    width: '10%',
                    render: (data, type, row, meta) => '<input style="width:100%;text-align:right;" class="form-control-sm" type="text" min="0" max="' + data + '" id="qty_retur' + meta.row + '" name="qty_retur['+meta.row+']" value="' + (row.qty_retur_saved || '') + '" onkeyup="tambahqty(this.value)" />'
                },
                {
                    targets: [7],
                    width: '10%',
                    render: (data, type, row, meta) => '<input style="width:100%;text-align:right;" class="form-control-sm" type="text" min="0" max="' + data + '" id="qty_reject' + meta.row + '" name="qty_reject['+meta.row+']" value="' + (row.qty_reject_saved || '') + '" />'
                },
                {
                    targets: [8],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input style="width:100%;text-align:right;" class="form-control-sm" type="text" id="bruto' + meta.row + '" name="bruto['+meta.row+']" value="' + (row.bruto_saved || '') + '" />'
                },
                {
                    targets: [9],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input style="width:100%;text-align:right;" class="form-control-sm" type="text" id="neto' + meta.row + '" name="neto['+meta.row+']" value="' + (row.neto_saved || '') + '" />'
                },
                {
                    targets: [10],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="id_item' + meta.row + '" name="id_item['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [11],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="id_jo' + meta.row + '" name="id_jo['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [12],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="id_bppb' + meta.row + '" name="id_bppb['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [13],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="no_ws' + meta.row + '" name="no_ws['+meta.row+']" value="' + data + '" readonly />'
                }

            ]
        });

        function tambahqty($val){
            var table = document.getElementById("datatable");
            var qty = 0;
            var jml_qty = 0;

            for (var i = 1; i < (table.rows.length); i++) {
                qty = document.getElementById("datatable").rows[i].cells[6].children[0].value || 0;
                jml_qty += parseFloat(qty) ;
            }

            $('#jumlah_qty').val(jml_qty);

        }

        // Load ulang daftar Surat Jalan Asal & Tujuan Pemasukan berdasarkan data tersimpan
        $(document).ready(function () {
            get_nobppb();
            if ($('#txt_type_bc').val()) {
                get_tujuan();
            }
        });
    </script>
@endsection
