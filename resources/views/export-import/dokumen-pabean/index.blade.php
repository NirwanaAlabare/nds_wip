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
            <div class="col-md-3">
                <label class="small fw-bold">Jenis Transaksi</label>
                <select id="jenis" class="form-control form-control-sm select2bs4">
                    <option value="Pemasukan" {{ $jenis == 'Pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                    <option value="Pengeluaran" {{ $jenis == 'Pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold">Dari Tanggal</label>
                <input type="date" id="filter_tanggal_awal" class="form-control form-control-sm" value="{{ $tgl_awal }}">
            </div>
            <div class="col-md-3">
                <label class="small fw-bold">Sampai Tanggal</label>
                <input type="date" id="filter_tanggal_akhir" class="form-control form-control-sm" value="{{ $tgl_akhir }}">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-sm w-100" id="btn-filter" onclick="refreshTable()">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
        </div>

        <hr>

        <div class="table-responsive mb-3">
            <table class="table table-bordered table-sm table-custom table-hover w-100" id="table-dokumen">
                <thead>
                    <tr>
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
@endsection

@section('custom-script')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                    d.jenis = $('#jenis').val();
                    d.tanggal_awal = $('#filter_tanggal_awal').val();
                    d.tanggal_akhir = $('#filter_tanggal_akhir').val();
                }
            },
            columns: [
                { data: 'trx_no', name: 'trx_no' },
                { data: 'pono', name: 'pono' },
                { data: 'tanggal', name: 'tanggal' },
                { data: 'supplier', name: 'ms.supplier' },
                { data: 'invno', name: 'invno', defaultContent: '-' },
                { data: 'jenis_dok', name: 'jenis_dok', defaultContent: '-' },
                { data: 'bcno', name: 'bcno', defaultContent: '-' },
                { data: 'bcdate', name: 'bcdate' },
                { data: 'nomor_aju', name: 'nomor_aju', defaultContent: '-' },
                { data: 'tanggal_aju', name: 'tanggal_aju' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
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
        let actionUrl = '{{ route("dokumen-pabean-send", ":id") }}';
        actionUrl = actionUrl.replace(':id', trxNo);

        Swal.fire({
            title: 'Kirim ke CEISA?',
            text: "Dokumen " + trxNo + " akan diproses dan dikirim ke server Bea Cukai. Nomor Aju akan dibuat otomatis.",
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
                errorHtml = `<div style="text-align: left; font-size: 14px;"><ul style="padding-left: 20px; margin-top: 5px; color: #d33;">${listError}</ul></div>`;
            } else if (typeof res.ceisa_error === 'object') {
                if (res.ceisa_error.message) {
                    if (Array.isArray(res.ceisa_error.message)) {
                        let listError = res.ceisa_error.message.map(err => `<li>${err}</li>`).join('');
                        errorHtml = `<div style="text-align: left; font-size: 14px;"><ul style="padding-left: 20px; margin-top: 5px; color: #d33;">${listError}</ul></div>`;
                    } else {
                        errorHtml = `<div style="color: #d33; font-weight: bold; font-size: 15px;">${res.ceisa_error.message}</div>`;
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
</script>
@endsection
