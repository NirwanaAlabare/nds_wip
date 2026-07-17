@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

<style>
    .table-custom th {
        background-color: var(--sb-color) !important;
        color: white;
        white-space: nowrap;
        text-align: center;
        vertical-align: middle !important;
        font-size: 13px;
    }
    .table-custom td {
        vertical-align: middle !important;
        font-size: 13px;
    }
    .select2-container--bootstrap4 .select2-selection--multiple {
        min-height: calc(1.5em + .5rem + 2px) !important;
    }
    .table-custom tr.row-success-custom td {
        background-color: rgba(40, 167, 69, 0.12) !important;
    }

    /* Modal Status Periode */
    #modal-status-periode .modal-header {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        color: white;
        border-radius: 8px 8px 0 0;
    }
    #modal-status-periode .modal-header .close { color: white; opacity: 1; }
    #modal-status-periode .modal-title { font-size: 16px; font-weight: 700; }
    .accordion-doc-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 8px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,.07);
    }
    .accordion-doc-header {
        background: #f8f9fa;
        padding: 10px 14px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background .2s;
        user-select: none;
    }
    .accordion-doc-header:hover { background: #e9ecef; }
    .accordion-doc-header.open { background: #dbeafe; }
    .accordion-doc-body {
        display: none;
        padding: 12px 14px;
        background: #fff;
        border-top: 1px solid #dee2e6;
        font-size: 13px;
    }
    .badge-status-dot {
        display: inline-block;
        width: 8px; height: 8px;
        border-radius: 50%;
        margin-right: 5px;
    }
</style>
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">
            <i class="fas fa-file-invoice"></i> List Dokumen Pabean
        </h5>
    </div>
    <div class="card-body">

        <div class="row align-items-end mb-4">
            <div class="col-md-2">
                <label class="small fw-bold">Jenis Transaksi</label>
                <select id="jenis" class="form-control form-control-sm select2bs4">
                    <option value="Pemasukan" {{ $jenis == 'Pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                    <option value="Pengeluaran" {{ $jenis == 'Pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="small fw-bold">Tipe BC</label>
                <select id="jenis_bc" class="form-control form-control-sm select2bs4">
                    <option value="" {{ $jenis_bc == '' ? 'selected' : '' }}>Semua Tipe</option>
                    <option value="BC 4.0" {{ $jenis_bc == 'BC 4.0' ? 'selected' : '' }}>BC 4.0</option>
                    <option value="BC 4.1" {{ $jenis_bc == 'BC 4.1' ? 'selected' : '' }}>BC 4.1</option>
                    <option value="BC 3.0" {{ $jenis_bc == 'BC 3.0' ? 'selected' : '' }}>BC 3.0</option>
                    <option value="BC 3.3" {{ $jenis_bc == 'BC 3.3' ? 'selected' : '' }}>BC 3.3</option>
                    <option value="BC 2.7" {{ $jenis_bc == 'BC 2.7' ? 'selected' : '' }}>BC 2.7</option>
                    <option value="BC 2.6.1" {{ $jenis_bc == 'BC 2.6.1' ? 'selected' : '' }}>BC 2.6.1</option>
                    <option value="BC 2.6.2" {{ $jenis_bc == 'BC 2.6.2' ? 'selected' : '' }}>BC 2.6.2</option>
                    <option value="BC 2.5" {{ $jenis_bc == 'BC 2.5' ? 'selected' : '' }}>BC 2.5</option>
                    <option value="BC 2.3" {{ $jenis_bc == 'BC 2.3' ? 'selected' : '' }}>BC 2.3</option>
                    <option value="INHOUSE" {{ $jenis_bc == 'INHOUSE' ? 'selected' : '' }}>INHOUSE</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="small fw-bold">Status Kirim</label>
                <select id="status_ceisa" class="form-control form-control-sm select2bs4">
                    <option value="" {{ $status_ceisa == '' ? 'selected' : '' }}>Semua</option>
                    <option value="sent" {{ $status_ceisa == 'sent' ? 'selected' : '' }}>Sudah Kirim</option>
                    <option value="unsent" {{ $status_ceisa == 'unsent' ? 'selected' : '' }}>Belum Kirim</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="small fw-bold">Dari Tanggal</label>
                <input type="date" id="filter_tanggal_awal" class="form-control form-control-sm" value="{{ $tgl_awal }}">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Sampai Tanggal</label>
                <input type="date" id="filter_tanggal_akhir" class="form-control form-control-sm" value="{{ $tgl_akhir }}">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-sm w-100" id="btn-filter" onclick="refreshTable()">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <button class="btn btn-sm btn-outline-info" id="btn-status-periode">
                    Cek Status CEISA Periode Ini
                </button>
                <button id="btn-send" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Batch</button>
            </div>
        </div>

        <hr>

        <div class="table-responsive mb-3">
            <table class="table table-bordered table-sm table-custom table-hover w-100" id="table-dokumen">
                <thead>
                    <tr>
                        {{-- untuk checklist --}}
                        <th></th>
                        <th>Nomor Trans</th>
                        <th>PO #</th>
                        <th>Tanggal Trans</th>
                        <th>Pemasok</th>
                        <th>No. Invoice</th>
                        <th>Jenis BC</th>
                        <th>No. Daftar</th>
                        <th>Tgl. Daftar</th>
                        <th>No. Aju</th>
                        <th>Tgl. Aju</th>
                        <th>Act</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>
</div>

{{-- Modal Status Periode CEISA --}}
<div class="modal fade" id="modal-status-periode" tabindex="-1" role="dialog" aria-labelledby="modalStatusPeriodeLabel">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalStatusPeriodeLabel">
                    <i class="fas fa-satellite-dish mr-2"></i>
                    Status Dokumen CEISA &mdash; <span id="modal-periode-label"></span>
                </h5>
                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="modal-status-periode-body" style="min-height: 200px;">
                <div class="text-center py-5" id="modal-status-loading">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted">Mengambil data dari server CEISA...</div>
                </div>
                <div id="modal-status-content" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <small class="text-muted mr-auto" id="modal-status-total"></small>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('custom-script')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('plugins/sweetalert/dist/sweetalert2.all.min.js') }}"></script>

<script>
    let tableDokumen;

    $(document).ready(function() {
        $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

        tableDokumen = $('#table-dokumen').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("dokumen-pabean-index") }}',
                data: function (d) {
                    d.jenis         = $('#jenis').val();
                    d.jenis_bc      = $('#jenis_bc').val();
                    d.tanggal_awal  = $('#filter_tanggal_awal').val();
                    d.tanggal_akhir = $('#filter_tanggal_akhir').val();
                    d.status_ceisa  = $('#status_ceisa').val();
                }
            },
            columns: [
                {
                    data: null,
                    name: 'checkbox',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function (data, type, row, meta) {
                        let jenisBc = $('#jenis_bc').val();
                        let jenis = $('#jenis').val();

                        if(jenisBc == "BC 4.0" || jenisBc == "BC 4.1"){
                            if(jenis == 'Pemasukan'){
                                return '<input type="checkbox" class="select-checkbox" value="' + row.bpbno + '" data-supplier="' + row.supplier + '" data-no-aju="' + row.nomor_aju_ceisa + '" data-id="'+row.id+'">';
                            }else{
                                return '<input type="checkbox" class="select-checkbox" value="' + row.bppbno + '" data-supplier="' + row.supplier + '" data-no-aju="' + row.nomor_aju_ceisa + '" data-id="'+row.id+'">';
                            }
                        }else{
                            return '';
                        }
                    }
                },
                { data: 'trx_no',      name: 'trx_no',        searchable: true },
                { data: 'pono',        name: 'pono',          searchable: true},
                { data: 'tanggal',     name: 'tanggal',       searchable: false },
                { data: 'supplier',    name: 'ms.supplier',   searchable: true },
                { data: 'invno',       name: 'invno',         defaultContent: '-' },
                { data: 'jenis_dok',   name: 'jenis_dok',     defaultContent: '-' },
                { data: 'bcno',        name: 'bcno',          defaultContent: '-' },
                { data: 'bcdate',      name: 'bcdate',        searchable: false },
                { data: 'nomor_aju_ceisa',   name: 'nomor_aju_ceisa',     defaultContent: '-' },
                { data: 'tanggal_aju_ceisa', name: 'tanggal_aju_ceisa',   searchable: false },
                { data: 'action',      name: 'action',        orderable: false, searchable: false, className: 'text-center' }
            ],
            createdRow: function(row, data, dataIndex) {
                if (data.ceisa_status == 1) {
                    $(row).addClass('row-success-custom');
                }
            },
            drawCallback: function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });

        toggleKolomPO();
    });

    function refreshTable() {
        let jenisTrx = $('#jenis').val();
        $('#title-jen-trx').text(jenisTrx);
        toggleKolomPO();
        tableDokumen.ajax.reload(null, false);
    }

    function toggleKolomPO() {
        let jenisTrx = $('#jenis').val();
        let column = tableDokumen.column(1);

        if (jenisTrx === 'Pengeluaran') {
            column.visible(false);
        } else {
            column.visible(true);
        }
    }

    $(document).on('click', '.btn-kirim', function() {
        let trxNo = $(this).data('id');
        let jenisBc = $(this).data('jenis_bc');
        let actionUrl = '';
        if(jenisBc === 'BC 2.3') {
            actionUrl = '{{ route("dokumen-pabean-send-bc23", ":id") }}';
        }

        if(jenisBc === 'BC 2.5' || jenisBc === '25' || jenisBc === '2.5') {
            actionUrl = '{{ route("dokumen-pabean-send-bc25", ":id") }}';
        }

        if(jenisBc === 'BC 4.0') {
             actionUrl = '{{ route("dokumen-pabean-send", ":id") }}';
        }

        if(jenisBc === 'BC 3.0') {
             actionUrl = '{{ route("dokumen-pabean-send-bc30", ":id") }}';
        }

        if(jenisBc === 'BC 2.7') {
             actionUrl = '{{ route("dokumen-pabean-send-bc27", ":id") }}';
        }

        if(jenisBc === 'BC 3.3') {
             actionUrl = '{{ route("dokumen-pabean-send-bc33", ":id") }}';
        }

        if(jenisBc === 'BC 4.1') {
             actionUrl = '{{ route("dokumen-pabean-send-bc41", ":id") }}';
        }

        if(jenisBc === 'BC 2.6.1') {
             actionUrl = '{{ route("dokumen-pabean-send-bc261", ":id") }}';
        }

        if(jenisBc === 'BC 2.6.2') {
             actionUrl = '{{ route("dokumen-pabean-send-bc262", ":id") }}';
        }

        actionUrl = actionUrl.replace(':id', trxNo);

        Swal.fire({
            title: 'Kirim ke CEISA?',
            text: "Dokumen " + trxNo + " akan diproses dan dikirim ke server Bea Cukai.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-paper-plane"></i> Ya, Kirim!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Memproses ke CEISA...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: actionUrl,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if(res.status === 200) {
                            Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success' });
                            console.log("Response CEISA:", res.ceisa_response);

                            refreshTable();
                        } else {
                            showErrorSwal(res);
                        }
                    },
                    error: function(xhr) {
                        let res = xhr.responseJSON || { message: 'Terjadi Kesalahan Sistem' };
                        showErrorSwal(res);
                    }
                });
            }
        });
    });


    function showErrorSwal(res) {
        let errorHtml = res.message || 'Terjadi Kesalahan Sistem';

        if (res.ceisa_error) {
            if (Array.isArray(res.ceisa_error)) {
                let listError = res.ceisa_error.map(err => `<li>${err}</li>`).join('');
                errorHtml = `<div style="text-align: left; font-size: 14px;">
                                <ul style="padding-left: 20px; margin-top: 5px; color: #d33;">${listError}</ul>
                             </div>`;
            } else if (typeof res.ceisa_error === 'object') {
                if (res.ceisa_error.message) {
                    if (Array.isArray(res.ceisa_error.message)) {
                        let listError = res.ceisa_error.message.map(err => `<li>${err}</li>`).join('');
                        errorHtml = `<div style="text-align: left; font-size: 14px;">
                                        <ul style="padding-left: 20px; margin-top: 5px; color: #d33;">${listError}</ul>
                                     </div>`;
                    } else {
                        errorHtml = `<div style="color: #d33; font-weight: bold; font-size: 15px;">
                                        ${res.ceisa_error.message}
                                     </div>`;
                    }
                } else {
                    errorHtml = `<pre style="text-align: left; font-size: 13px; color: #d33;">${JSON.stringify(res.ceisa_error, null, 2)}</pre>`;
                }
            } else {
                errorHtml = `<div style="color: #d33; font-weight: bold; font-size: 15px;">${res.ceisa_error}</div>`;
            }
        }

        Swal.fire({ title: 'gagal mengirim ke CEISA!', html: errorHtml, icon: 'error' });
    }

    $(document).on('click', '.btn-delete-draft', function() {
        let noAju = $(this).data('noaju');
        let actionUrl = '{{ route("dokumen-pabean-delete-draft", ":noAju") }}';
        actionUrl = actionUrl.replace(':noAju', noAju);

        Swal.fire({
            title: 'Hapus Draft di CEISA?',
            text: "Draft dengan Nomor Aju " + noAju + " akan dihapus dari server Bea Cukai. Aksi ini tidak dapat dibatalkan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus...',
                    text: 'Mohon tunggu',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: actionUrl,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if(res.success) {
                            Swal.fire('Berhasil!', res.message, 'success');
                            refreshTable();
                        } else {
                            Swal.fire('Gagal!', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        let errMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
                        Swal.fire('Error!', errMsg, 'error');
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-status', function() {
        let noAju = $(this).data('noaju');
        let actionUrl = '{{ route("dokumen-pabean-status", ":noAju") }}';
        actionUrl = actionUrl.replace(':noAju', noAju);

        Swal.fire({
            title: 'Memeriksa status ke CEISA...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: actionUrl,
            type: 'GET',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                if(res.status === 200) {
                    let data = res.ceisa_response;
                    let htmlContent = '';

                    if (data && ((data.dataStatus && data.dataStatus.length > 0) || (data.dataRespon && data.dataRespon.length > 0))) {
                        let allStatuses = [];

                        if (data.dataStatus) {
                            data.dataStatus.forEach(s => allStatuses.push({
                                waktu: s.waktuStatus,
                                keterangan: s.keterangan,
                                kodeProses: s.kodeProses,
                                nomorDaftar: s.nomorDaftar,
                                tanggalDaftar: s.tanggalDaftar,
                                nomorAju: s.nomorAju,
                                isRespon: false
                            }));
                        }

                        if (data.dataRespon) {
                            data.dataRespon.forEach(r => allStatuses.push({
                                waktu: r.waktuRespon,
                                keterangan: r.keterangan,
                                kodeProses: r.kodeRespon,
                                nomorDaftar: r.nomorDaftar,
                                tanggalDaftar: r.tanggalDaftar,
                                nomorAju: r.nomorAju,
                                isRespon: true,
                                pesan: r.pesan
                            }));
                        }

                        allStatuses.sort((a, b) => new Date(b.waktu) - new Date(a.waktu));
                        let latest = allStatuses[0];

                        let validDaftar = allStatuses.find(s => s.nomorDaftar != null);
                        let textDaftar = validDaftar ? `<b>${validDaftar.nomorDaftar}</b> tanggal <b>${validDaftar.tanggalDaftar}</b>` : '<span class="badge badge-warning">Belum Terdaftar</span>';
                        let noDaftarStr = `${textDaftar}
                            <button type="button" class="btn btn-xs btn-primary ml-2 btn-sync-bcno"
                                data-noaju="${validDaftar ? validDaftar.nomorAju : noAju}"
                                data-nodaftar="${validDaftar ? validDaftar.nomorDaftar : ''}"
                                data-tgldaftar="${validDaftar ? validDaftar.tanggalDaftar : ''}">
                                <i class="fas fa-save"></i> Simpan ke No Daftar
                            </button>`;

                        let badgeColor = (latest.keterangan && latest.keterangan.toUpperCase().includes('REJECT')) ? 'badge-danger' : 'badge-success';

                        let reasonHtml = '';
                        if (latest.pesan && Array.isArray(latest.pesan) && latest.pesan.length > 0) {
                            reasonHtml = `<div class="alert alert-danger p-2 mt-2 mb-0" style="font-size:12px;"><b>Catatan/Alasan:</b><br><ul class="mb-0 pl-3">` + latest.pesan.map(p => `<li>${p}</li>`).join('') + `</ul></div>`;
                        }

                        htmlContent = `
                            <div style="text-align: left; font-size: 14px; margin-top: 10px;">
                                <table class="table table-sm table-bordered">
                                    <tr>
                                        <th width="35%" class="bg-light">Nomor Aju</th>
                                        <td>${latest.nomorAju || noAju}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">No. Pabean</th>
                                        <td>${noDaftarStr}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Status Terakhir</th>
                                        <td><span class="badge ${badgeColor}" style="font-size: 13px;">${latest.keterangan || '-'}</span>${reasonHtml}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Waktu Update</th>
                                        <td>${latest.waktu || '-'}</td>
                                    </tr>
                                </table>

                                <h5 class="mt-4 mb-2" style="font-size: 16px; font-weight: bold;"><i class="fas fa-history text-primary"></i> Riwayat Status</h5>
                                <div style="max-height: 250px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 5px;">
                                    <table class="table table-sm table-striped mb-0" style="font-size: 12px;">
                                        <thead>
                                            <tr class="bg-light">
                                                <th>Waktu</th>
                                                <th>Status/Keterangan</th>
                                                <th>Proses / Respon</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                        `;

                        allStatuses.forEach(st => {
                            let textClass = (st.keterangan && st.keterangan.toUpperCase().includes('REJECT')) ? 'text-danger' : '';
                            htmlContent += `
                                            <tr>
                                                <td nowrap>${st.waktu || '-'}</td>
                                                <td class="${textClass}"><b>${st.keterangan || '-'}</b></td>
                                                <td><span class="badge ${st.isRespon ? 'badge-primary' : 'badge-secondary'}">${st.kodeProses || '-'}</span></td>
                                            </tr>
                            `;
                        });

                        htmlContent += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        `;
                    } else {
                        htmlContent = `<div class="alert alert-info">Status berhasil diambil, tetapi tidak ada riwayat status yang ditemukan.</div>`;
                    }

                    Swal.fire({
                        title: 'Status Dokumen CEISA',
                        html: htmlContent,
                        icon: 'info',
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#3085d6',
                        width: '600px'
                    });

                    console.log("Response CEISA:", res.ceisa_response);
                } else {
                    showErrorSwal(res);
                }
            },
            error: function(xhr) {
                let res = xhr.responseJSON || { message: 'Terjadi Kesalahan Sistem' };
                showErrorSwal(res);
            }
        });
    });

    $(document).on('click', '.btn-rollback', function() {
        let trxNo = $(this).data('id');
        let noAju = $(this).data('noaju');
        let actionUrl = '{{ route("dokumen-pabean-rollback", ":id") }}';
        actionUrl = actionUrl.replace(':id', trxNo);

        Swal.fire({
            title: 'Rollback Status CEISA?',
            text: "Status pengiriman dokumen " + trxNo + " (No. Aju: " + noAju + ") akan di-reset sehingga Anda dapat melakukan pengiriman ulang ke CEISA.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f0ad4e',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-undo"></i> Ya, Rollback!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Rollback...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: actionUrl,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if(res.status === 200) {
                            Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success' });
                            refreshTable();
                        } else {
                            showErrorSwal(res);
                        }
                    },
                    error: function(xhr) {
                        let res = xhr.responseJSON || { message: 'Terjadi Kesalahan Sistem' };
                        showErrorSwal(res);
                    }
                });
            }
        });
    });

    // ============================================================
    // CEISA STATUS PERIODE
    // ============================================================
    $('#btn-status-periode').on('click', function() {
        let tglAwal  = $('#filter_tanggal_awal').val();
        let tglAkhir = $('#filter_tanggal_akhir').val();

        if (!tglAwal || !tglAkhir) {
            Swal.fire('Perhatian', 'Silakan isi filter tanggal terlebih dahulu.', 'warning');
            return;
        }

        // Tampilkan label periode di header modal
        let fmt = d => {
            let p = d.split('-'); return p[2]+'/'+p[1]+'/'+p[0];
        };
        $('#modal-periode-label').text(fmt(tglAwal) + ' s/d ' + fmt(tglAkhir));
        $('#modal-status-loading').show();
        $('#modal-status-content').hide().html('');
        $('#modal-status-total').text('');
        $('#modal-status-periode').modal('show');

        $.ajax({
            url: '{{ route("dokumen-pabean-status-periode") }}',
            type: 'GET',
            data: {
                tgl_awal:  tglAwal,
                tgl_akhir: tglAkhir,
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                $('#modal-status-loading').hide();

                if (res.status !== 200) {
                    $('#modal-status-content').html(
                        `<div class="alert alert-danger">${res.message || 'Terjadi kesalahan.'}</div>`
                    ).show();
                    return;
                }

                let data = res.data || [];
                $('#modal-status-total').text('Total dokumen ditemukan: ' + res.total);

                if (data.length === 0) {
                    $('#modal-status-content').html(
                        `<div class="alert alert-info text-center">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            Tidak ada dokumen dengan status pada periode <b>${fmt(tglAwal)}</b> s/d <b>${fmt(tglAkhir)}</b>
                        </div>`
                    ).show();
                    return;
                }

                let html = '';

                data.forEach(function(doc, idx) {
                    let latestStatus = doc.statusList && doc.statusList.length > 0 ? doc.statusList[0] : null;
                    // Fallback ke responList jika tidak ada statusList
                    let latestRespon = doc.responList && doc.responList.length > 0 ? doc.responList[0] : null;
                    let keterangan   = latestStatus ? (latestStatus.keterangan || '-') : (latestRespon ? (latestRespon.keterangan || '-') : '-');
                    let waktu        = latestStatus ? (latestStatus.waktuStatus || '-') : (latestRespon ? (latestRespon.waktuRespon || '-') : '-');
                    let noDaftar     = doc.nomorDaftar ? `<span class="badge badge-primary ml-1">${doc.nomorDaftar}</span><small class="text-muted ml-1">${doc.tanggalDaftar || ''}</small>` : `<span class="badge badge-warning ml-1">Belum Daftar</span>`;
                    let dotColor     = doc.nomorDaftar ? '#28a745' : '#ffc107';

                    let statusRows = '';
                    if (doc.statusList && doc.statusList.length > 0) {
                        statusRows += `
                            <div class="mb-2">
                                <b><i class="fas fa-history text-primary mr-1"></i> Riwayat Status</b>
                            </div>
                            <table class="table table-sm table-bordered table-striped mb-3" style="font-size:12px;">
                                <thead class="bg-light">
                                    <tr><th>Waktu Status</th><th>Keterangan</th><th>Kode Status</th><th>No. Daftar</th><th>Tgl. Daftar</th></tr>
                                </thead>
                                <tbody>`;
                        doc.statusList.forEach(function(st) {
                            statusRows += `<tr>
                                <td nowrap>${st.waktuStatus || '-'}</td>
                                <td><b>${st.keterangan || '-'}</b></td>
                                <td><span class="badge badge-secondary">${st.kodeStatus || '-'}</span></td>
                                <td>${st.nomorDaftar ? '<span class="badge badge-success">'+st.nomorDaftar+'</span>' : '-'}</td>
                                <td>${st.tanggalDaftar || '-'}</td>
                            </tr>`;
                        });
                        statusRows += `</tbody></table>`;
                    }

                    let responRows = '';
                    if (doc.responList && doc.responList.length > 0) {
                        responRows += `
                            <div class="mb-2">
                                <b><i class="fas fa-reply text-success mr-1"></i> Respon Dokumen</b>
                            </div>
                            <table class="table table-sm table-bordered table-striped" style="font-size:12px;">
                                <thead class="bg-light">
                                    <tr><th>Waktu Respon</th><th>Keterangan</th><th>Kode Respon</th><th>No. Respon</th><th>Tgl. Respon</th><th>Pesan</th><th>PDF</th></tr>
                                </thead>
                                <tbody>`;
                        doc.responList.forEach(function(rp) {
                            // Bangun string pesan dari array [{uraian1, uraian2, ...}]
                            let pesanHtml = '-';
                            if (rp.pesan && rp.pesan.length > 0) {
                                let lines = [];
                                rp.pesan.forEach(function(p) {
                                    Object.values(p).forEach(function(v) { if(v) lines.push(v); });
                                });
                                pesanHtml = lines.length > 0
                                    ? `<ul class="mb-0 pl-3" style="font-size:11px;">${lines.map(l=>'<li>'+l+'</li>').join('')}</ul>`
                                    : '-';
                            }

                            // Tombol PDF jika ada
                            let pdfBtn = '-';
                            if (rp.Pdf) {
                                pdfBtn = `<button class="btn btn-xs btn-outline-danger" onclick="openPdfBase64('${rp.nomorRespon || idx}')"
                                    data-pdf="${rp.Pdf}"><i class="fas fa-file-pdf"></i> Lihat</button>`;
                            }

                            responRows += `<tr>
                                <td nowrap>${rp.waktuRespon || '-'}</td>
                                <td><b>${rp.keterangan || '-'}</b></td>
                                <td><span class="badge badge-info">${rp.kodeRespon || '-'}</span></td>
                                <td>${rp.nomorRespon || '-'}</td>
                                <td nowrap>${rp.tanggalRespon || '-'}</td>
                                <td>${pesanHtml}</td>
                                <td>${pdfBtn}</td>
                            </tr>`;
                        });
                        responRows += `</tbody></table>`;
                    }

                    html += `
                    <div class="accordion-doc-card">
                        <div class="accordion-doc-header" onclick="toggleAccordion(this, 'acc-body-${idx}')">
                            <div>
                                <span class="badge-status-dot" style="background:${dotColor};"></span>
                                <b>${doc.nomorAju}</b>
                                ${noDaftar}
                            </div>
                            <div style="text-align:right; font-size:12px; color:#555;">
                                <span class="badge badge-light border">${keterangan}</span>
                                &nbsp;<span class="text-muted">${waktu}</span>
                                &nbsp;<i class="fas fa-chevron-down text-primary ml-2 acc-arrow"></i>
                            </div>
                        </div>
                        <div class="accordion-doc-body" id="acc-body-${idx}">
                            ${statusRows || '<div class="text-muted">Tidak ada riwayat status.</div>'}
                            ${responRows}
                        </div>
                    </div>`;
                });

                $('#modal-status-content').html(html).show();
            },
            error: function(xhr) {
                $('#modal-status-loading').hide();
                let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                $('#modal-status-content').html(`<div class="alert alert-danger">${msg}</div>`).show();
            }
        });
    });

    function toggleAccordion(headerEl, bodyId) {
        let body = document.getElementById(bodyId);
        let arrow = headerEl.querySelector('.acc-arrow');
        let isOpen = body.style.display === 'block';
        body.style.display = isOpen ? 'none' : 'block';
        headerEl.classList.toggle('open', !isOpen);
        if (arrow) {
            arrow.style.transform = isOpen ? '' : 'rotate(180deg)';
            arrow.style.transition = 'transform .2s';
        }
    }

    function openPdfBase64(btnEl) {
        let pdfBase64 = typeof btnEl === 'string'
            ? document.querySelector(`[data-pdf][onclick*="${btnEl}"]`)?.getAttribute('data-pdf')
            : btnEl.getAttribute('data-pdf');

        if (!pdfBase64) {
            Swal.fire('Error', 'Data PDF tidak tersedia.', 'error');
            return;
        }
        let byteChars = atob(pdfBase64);
        let byteArr   = new Uint8Array(byteChars.length);
        for (let i = 0; i < byteChars.length; i++) byteArr[i] = byteChars.charCodeAt(i);
        let blob = new Blob([byteArr], { type: 'application/pdf' });
        let url  = URL.createObjectURL(blob);
        window.open(url, '_blank');
    }

    $(document).on('click', '.btn-sync-bcno', function() {
        let btn = $(this);
        let noAju = btn.data('noaju');
        let noDaftar = btn.data('nodaftar');
        let tglDaftar = btn.data('tgldaftar');
        let actionUrl = '{{ route("dokumen-pabean-sync-bcno", ":noAju") }}'.replace(':noAju', noAju);

        let infoHtml = !noDaftar ? '<div class="alert alert-warning p-2" style="font-size:13px;"><i class="fas fa-exclamation-triangle"></i> Nomor pabean tidak ditemukan di CEISA. Silakan isi secara manual.</div>' : '';

        Swal.fire({
            title: 'Input Nomor Pabean',
            html: infoHtml + `
                <div class="form-group text-left mb-0">
                    <label>Nomor Pabean</label>
                    <input type="text" id="swal-no-daftar" class="form-control" value="${noDaftar}">
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                let inputNoDaftar = document.getElementById('swal-no-daftar').value;
                if (!inputNoDaftar) {
                    Swal.showValidationMessage('Nomor Pabean harus diisi');
                }
                return { noDaftar: inputNoDaftar };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Mohon tunggu',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: actionUrl,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        nomor_daftar: result.value.noDaftar
                    },
                    success: function(res) {
                        if (res.status === 200) {
                            Swal.fire('Berhasil!', res.message, 'success');
                            btn.data('nodaftar', result.value.noDaftar);
                            refreshTable();
                        } else {
                            Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
                        }
                    },
                    error: function(xhr) {
                        let errMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi Kesalahan Sistem';
                        Swal.fire('Gagal', errMsg, 'error');
                    }
                });
            }
        });
    });

    $('#table-dokumen').on('change', '.select-checkbox', function() {
        let selectedSuppliers = [];

        $('.select-checkbox:checked').each(function() {
            selectedSuppliers.push($(this).data('supplier'));
        });

        let uniqueSuppliers = [...new Set(selectedSuppliers)];

        let data_aju = $(this).data('no-aju');
        if (data_aju == '' || data_aju == null) {
            Swal.fire('Gagal', 'Dokumen ini belum di Isi.', 'error');
            $(this).prop('checked', false);
            return;
        }

        if (uniqueSuppliers.length > 1) {
            Swal.fire('Gagal', 'Pemasok tidak sama. Anda hanya bisa memilih satu pemasok yang sama.', 'error');
            $(this).prop('checked', false);
        }
    });

    $('#btn-send').on('click', function() {
        let checkedBoxes = $('.select-checkbox:checked');

        let selectedBpb = checkedBoxes.map(function() {
            return $(this).val();
        }).get();
        if (selectedBpb.length === 0) {
            Swal.fire('Perhatian', 'Silakan pilih satu data terlebih dahulu!', 'warning');
            return;
        }

        console.log('ID:', selectedBpb);

        sendBatch(selectedBpb);
    });

    function sendBatch(bpbs) {

        let bpbListHtml = bpbs.map(bpb => `<li>${bpb}</li>`).join('');

        bpbListHtml = bpbListHtml.replace(/<li>/g, '').replace(/<\/li>/g, '\n');

        let jenisBc = $('#jenis_bc').val();

        if(jenisBc === 'BC 4.0'){
            Swal.fire({ title: 'Peringatan!', text: 'Kirim Batch 4.0 belum dapat di kirim', icon: 'warning' });
            return;
        }

        Swal.fire({
            title: 'Kirim ke CEISA?',
            text: "Dokumen " + bpbListHtml + " akan diproses dan dikirim ke server Bea Cukai.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-paper-plane"></i> Ya, Kirim!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Memproses ke CEISA...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });


                let actionUrl = '{{ route("dokumen-pabean-send-batch-ceisa") }}';
                $.ajax({
                    url: actionUrl,
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        bpbs: bpbs,
                        jenis_bc: jenisBc
                    },
                    success: function(res) {
                        if(res.status === 200) {
                            Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success' });
                            console.log("Response CEISA:", res.ceisa_response);

                            refreshTable();
                        } else {
                            showErrorSwal(res);
                        }
                    },
                    error: function(xhr) {
                        let res = xhr.responseJSON || { message: 'Terjadi Kesalahan Sistem' };
                        showErrorSwal(res);
                    }
                });
            }
        });
    }
</script>
@endsection
