@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style type="text/css">
        .form-control {
            border: 1.5px solid #ced4da;
            border-radius: 8px;
            padding: 6px 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.25);
        }

        .dataTables_length select {
            width: auto;
            min-width: 65px;
            padding-right: 24px;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list-alt"></i> Master Rak Sparepart</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3 align-items-end">
                <div class="col-md-3">
                    <label for="txtnm_rak"><small><b>Nama Rak :</b></small></label>
                    <input type="text" id="txtnm_rak" name="txtnm_rak" class="form-control form-control-sm"
                        style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();">
                </div>
                <div class="col-md-3">
                    <label for="txtno_rak"><small><b>No Rak :</b></small></label>
                    <input type="text" id="txtno_rak" name="txtno_rak" class="form-control form-control-sm"
                        style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();">
                </div>
                <div class="col-md-4">
                    <label for="txtdesc"><small><b>Deskripsi :</b></small></label>
                    <input type="text" id="txtdesc" name="txtdesc" class="form-control form-control-sm"
                        style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-success btn-sm" id="saveRakButton"
                        onclick="save_rak_sparepart();">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </div>

            <div class="mb-3">
                <button type="button" class="btn btn-success btn-sm" onclick="export_excel_stok_global();">
                    <i class="fas fa-file-excel"></i> Export Stok Global (Semua Item)
                </button>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">Nama Rak</th>
                            <th scope="col" class="text-center align-middle">No Rak</th>
                            <th scope="col" class="text-center align-middle">Deskripsi</th>
                            <th scope="col" class="text-center align-middle">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Edit Rak Sparepart -->
    <div class="modal fade" id="EditRakModal" tabindex="-1" aria-labelledby="EditRakModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="EditRakModalLabel">Edit Rak Sparepart</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="txted_id_rak" name="txted_id_rak" value="">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="txted_nm_rak"><small><b>Nama Rak :</b></small></label>
                            <input type="text" id="txted_nm_rak" name="txted_nm_rak" class="form-control form-control-sm"
                                style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();">
                        </div>
                        <div class="col-md-4">
                            <label for="txted_no_rak"><small><b>No Rak :</b></small></label>
                            <input type="text" id="txted_no_rak" name="txted_no_rak" class="form-control form-control-sm"
                                style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();">
                        </div>
                        <div class="col-md-4">
                            <label for="txted_desc"><small><b>Deskripsi :</b></small></label>
                            <input type="text" id="txted_desc" name="txted_desc" class="form-control form-control-sm"
                                style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning btn-sm" id="editRakButton"
                        onclick="update_rak_sparepart();">Edit</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal History Rak Sparepart -->
    <div class="modal fade" id="HistoryRakModal" tabindex="-1" aria-labelledby="HistoryRakModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl" style="max-width: 95vw;">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="HistoryRakModalLabel">History Rak Sparepart</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <span class="badge bg-primary">Total Jenis Item : <span id="totalItemRak">0</span></span>
                    </div>

                    <ul class="nav nav-tabs" id="historyRakTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-stok-btn" data-bs-toggle="tab"
                                data-bs-target="#tab-stok" type="button" role="tab">
                                <i class="fas fa-boxes"></i> Stok Saat Ini
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-riwayat-btn" data-bs-toggle="tab"
                                data-bs-target="#tab-riwayat" type="button" role="tab">
                                <i class="fas fa-history"></i> Riwayat Transaksi
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content border border-top-0 p-2" id="historyRakTabsContent">
                        <div class="tab-pane fade show active" id="tab-stok" role="tabpanel">
                            <div class="table-responsive">
                                <table id="stokRakTable"
                                    class="table table-bordered table-hover table-sm align-middle text-nowrap w-100">
                                    <thead class="bg-sb">
                                        <tr>
                                            <th scope="col" class="text-center align-middle">Nama Barang</th>
                                            <th scope="col" class="text-center align-middle">Goods Code</th>
                                            <th scope="col" class="text-center align-middle">Total Masuk</th>
                                            <th scope="col" class="text-center align-middle">Total Keluar</th>
                                            <th scope="col" class="text-center align-middle">Stok Saat Ini</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-riwayat" role="tabpanel">
                            <div class="table-responsive">
                                <table id="historyRakTable"
                                    class="table table-bordered table-hover table-sm align-middle text-nowrap w-100">
                                    <thead class="bg-sb">
                                        <tr>
                                            <th scope="col" class="text-center align-middle">Tanggal</th>
                                            <th scope="col" class="text-center align-middle">Jenis</th>
                                            <th scope="col" class="text-center align-middle">Nama Barang</th>
                                            <th scope="col" class="text-center align-middle">Qty</th>
                                            <th scope="col" class="text-center align-middle">Keterangan</th>
                                            <th scope="col" class="text-center align-middle">Dibuat Oleh</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-sm" onclick="export_excel_rak_sparepart();">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
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

    <script>
        // Modul Asset: senyapkan alert bawaan DataTables saat ajax gagal, cukup dicatat di console
        $.fn.dataTable.ext.errMode = function (settings, techNote, message) {
            console.error('DataTable ajax error:', message);
        };
    </script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        // Reset input form to default state on every page load/refresh
        $('#txtnm_rak').val('');
        $('#txtno_rak').val('');
        $('#txtdesc').val('');

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        function save_rak_sparepart() {
            let nm_rak = $('#txtnm_rak').val();
            let no_rak = $('#txtno_rak').val();
            let desc = $('#txtdesc').val();

            if (!nm_rak || !no_rak) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nama Rak dan No Rak wajib diisi',
                });
                return;
            }

            let $btn = $('#saveRakButton');
            $btn.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: '{{ route('store_rak_sparepart') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    nm_rak: nm_rak,
                    no_rak: no_rak,
                    desc: desc
                },
                success: function(response) {
                    $('#txtnm_rak').val('');
                    $('#txtno_rak').val('');
                    $('#txtdesc').val('');
                    dataTableReload();

                    Swal.fire({
                        icon: 'success',
                        title: 'Rak Sparepart Ditambahkan',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan.',
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        }

        function editData(id_c) {
            jQuery.ajax({
                url: '{{ route('show_rak_sparepart') }}',
                method: 'GET',
                data: {
                    id: id_c
                },
                dataType: 'json',
                success: function(res) {
                    $('#txted_id_rak').val(res.id_rak);
                    $('#txted_nm_rak').val(res.nm_rak);
                    $('#txted_no_rak').val(res.no_rak);
                    $('#txted_desc').val(res.desc);
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        }

        function update_rak_sparepart() {
            let id_rak = $('#txted_id_rak').val();
            let nm_rak = $('#txted_nm_rak').val();
            let no_rak = $('#txted_no_rak').val();
            let desc = $('#txted_desc').val();

            if (!nm_rak || !no_rak) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nama Rak dan No Rak wajib diisi',
                });
                return;
            }

            let $btn = $('#editRakButton');
            $btn.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: '{{ route('update_rak_sparepart') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    id_rak: id_rak,
                    nm_rak: nm_rak,
                    no_rak: no_rak,
                    desc: desc
                },
                success: function(response) {
                    $('#EditRakModal').modal('hide');
                    dataTableReload();

                    Swal.fire({
                        icon: 'success',
                        title: 'Rak Sparepart Diupdate',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat mengupdate.',
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        }

        function deleteData(id_c) {
            Swal.fire({
                icon: 'warning',
                title: 'Hapus Rak Sparepart?',
                text: 'Data yang sudah dihapus tidak dapat dikembalikan.',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: '{{ route('delete_rak_sparepart') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id_c
                    },
                    success: function(response) {
                        dataTableReload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Rak Sparepart Dihapus',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ||
                                'Terjadi kesalahan saat menghapus.',
                        });
                    }
                });
            });
        }
    </script>
    <script>
        function dataTableReload() {
            datatable.ajax.reload();
        }

        let datatable = $("#datatable").DataTable({
            ordering: true,
            responsive: true,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: true,
            scrollX: true,
            scrollCollapse: false,
            ajax: {
                url: '{{ route('asset_master_rak_sparepart') }}',
            },
            columns: [{
                    data: 'nm_rak'
                }, // Nama Rak
                {
                    data: 'no_rak'
                }, // No Rak
                {
                    data: 'desc'
                }, // Deskripsi
                {
                    data: 'id_rak',
                    render: function(data, type, row) {
                        let rakLabel = (row.nm_rak ?? '') + ' - ' + (row.no_rak ?? '');
                        return `
                    <div class="text-center">
                        <button class="btn btn-sm btn-info" onclick="showHistoryRak(${data}, '${rakLabel.replace(/'/g, "\\'")}')">
                            <i class="fa fa-history"></i>
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="editData(${data})"
                                data-bs-toggle="modal" data-bs-target="#EditRakModal">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteData(${data})">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>`;
                    },
                    orderable: false,
                    searchable: false
                }
            ],
        });
    </script>
    <script>
        // Modal History Rak Sparepart: stok saat ini per item + riwayat transaksi masuk/keluar
        let currentRakIdForHistory = null;

        let stokRakTable = $("#stokRakTable").DataTable({
            ordering: false,
            responsive: true,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollX: true,
            ajax: function(data, callback) {
                if (!currentRakIdForHistory) {
                    callback({
                        data: [],
                        recordsTotal: 0,
                        recordsFiltered: 0
                    });
                    return;
                }
                $.ajax({
                    url: '{{ route('get_stok_rak') }}',
                    data: {
                        id_rak: currentRakIdForHistory
                    },
                    success: callback
                });
            },
            columns: [{
                    data: 'itemdesc',
                    render: function(data) {
                        return data ?? '-';
                    }
                },
                {
                    data: 'goods_code',
                    render: function(data) {
                        return data ?? '-';
                    }
                },
                {
                    data: 'tot_masuk'
                },
                {
                    data: 'tot_keluar'
                },
                {
                    data: 'stok',
                    render: function(data) {
                        let cls = data > 0 ? 'bg-success' : 'bg-danger';
                        return `<span class="badge ${cls}">${data}</span>`;
                    }
                },
            ],
        });

        stokRakTable.on('xhr.dt', function(e, settings, json) {
            $('#totalItemRak').text(json?.recordsTotal ?? 0);
        });

        let historyRakTable = $("#historyRakTable").DataTable({
            ordering: false,
            responsive: true,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollX: true,
            ajax: function(data, callback) {
                if (!currentRakIdForHistory) {
                    callback({
                        data: [],
                        recordsTotal: 0,
                        recordsFiltered: 0
                    });
                    return;
                }
                $.ajax({
                    url: '{{ route('get_history_rak') }}',
                    data: {
                        id_rak: currentRakIdForHistory
                    },
                    success: callback
                });
            },
            columns: [{
                    data: 'tgl_trans_fix'
                },
                {
                    data: 'jenis',
                    render: function(data) {
                        let cls = data === 'MASUK' ? 'bg-success' : 'bg-danger';
                        return `<span class="badge ${cls}">${data}</span>`;
                    }
                },
                {
                    data: 'itemdesc',
                    render: function(data) {
                        return data ?? '-';
                    }
                },
                {
                    data: 'qty'
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        if (row.jenis === 'MASUK') {
                            return 'BPB: ' + (row.bpbno_int ?? '-');
                        }
                        let ket = [];
                        if (row.bpbno_int) ket.push('BPB: ' + row.bpbno_int);
                        if (row.mekanik_name) ket.push('Mekanik: ' + row.mekanik_name);
                        if (row.serial_number) ket.push('SN: ' + row.serial_number);
                        return ket.length ? ket.join(' | ') : '-';
                    }
                },
                {
                    data: 'created_by',
                    render: function(data) {
                        return data ?? '-';
                    }
                },
            ],
        });

        function showHistoryRak(id_rak, rakLabel) {
            currentRakIdForHistory = id_rak;
            $('#HistoryRakModalLabel').text('History Rak Sparepart - ' + rakLabel);

            stokRakTable.ajax.reload();
            historyRakTable.ajax.reload();

            $('#HistoryRakModal').modal('show');
        }

        // Tab Riwayat Transaksi tersembunyi saat modal dibuka, jadi lebar kolomnya perlu dihitung ulang saat ditampilkan
        $('#tab-riwayat-btn').on('shown.bs.tab', function() {
            historyRakTable.columns.adjust();
        });

        function downloadExcelBlob(response, defaultFilename) {
            Swal.close();
            let blob = new Blob([response]);
            let link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = defaultFilename;
            link.click();
        }

        // Export Excel untuk rak yang sedang dibuka di modal History (sheet Stok Saat Ini + Riwayat Transaksi)
        function export_excel_rak_sparepart() {
            if (!currentRakIdForHistory) return;

            Swal.fire({
                title: 'Please Wait,',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: 'get',
                url: '{{ route('export_excel_rak_sparepart') }}',
                data: {
                    id_rak: currentRakIdForHistory
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    downloadExcelBlob(response, 'Laporan Rak Sparepart.xlsx');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal export data ke Excel.',
                    });
                }
            });
        }

        // Export Excel stok sparepart global (per id_item, dijumlah dari semua rak)
        function export_excel_stok_global() {
            Swal.fire({
                title: 'Please Wait,',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: 'get',
                url: '{{ route('export_excel_stok_global_sparepart') }}',
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    downloadExcelBlob(response, 'Laporan Stok Sparepart Global.xlsx');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal export data ke Excel.',
                    });
                }
            });
        }
    </script>
@endsection
