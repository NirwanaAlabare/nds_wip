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
            <h5 class="card-title fw-bold mb-0"> Laporan Mutasi Per Kategori</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div>
                        <label class="form-label"><small>Tanggal Awal</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" onchange="dataTableReload()">
                    </div>
                    <div>
                        <label class="form-label"><small>Tanggal Akhir</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}" onchange="dataTableReload()">
                    </div>
                    <div>
                        <label class='form-label'><small>Kategori</small></label>
                        <select class="form-select form-select-sm" id="kategori" name="kategori" style="width: 100%;"  onchange="dataTableReload()">
                            <option selected="selected" value="" disabled="true">- Pilih Kategori -</option>
                            <option value="FABRIC">FABRIC</option>
                            <option value="ACCESORIES">ACCESORIES</option>
                            <option value="FG">FG</option>
                        </select>
                    </div>
                    <div>
                        <button class="btn btn-primary btn-sm" onclick="dataTableReload()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div class="mb-3">
                        <button class="btn btn-success btn-sm" id="exportExcel" data-bs-toggle="tooltip" data-bs-title="Export Excel" onclick="exportExcel()" disabled><i class="fa fa-file-excel"></i></button>
                    </div>
                </div>
            </div>
            <div id="table-fabric" class="table-responsive">
                <table id="datatable-fabric" class="table table-bordered table-striped table-hover table w-100">
                    <thead>
                        <tr>
                            <th>Barcode</th>
                            <th>Buyer</th>
                            <th>Jenis Item</th>
                            <th>Warna</th>
                            <th>Satuan</th>
                            <th>Saldo Awal</th>
                            <th>Pemasukan</th>
                            <th>Pengeluaran</th>
                            <th>Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-center"> TOTAL</th>
                            <th class="text-end"></th>
                            <th class="text-end"></th>
                            <th class="text-end"></th>
                            <th class="text-end"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div id="table-accesories" class="table-responsive d-none">
                <table id="datatable-accesories" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Barcode</th>
                            <th>Buyer</th>
                            <th>Nama Barang</th>
                            <th>Warna</th>
                            <th>Satuan</th>
                            <th>Saldo Awal</th>
                            <th>Pemasukan</th>
                            <th>Pengeluaran</th>
                            <th>Saldo Akhir</th>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-center"> TOTAL</th>
                            <th class="text-end"></th>
                            <th class="text-end"></th>
                            <th class="text-end"></th>
                            <th class="text-end"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div id="table-fg" class="table-responsive d-none">
                <table id="datatable-fg" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Barcode</th>
                            <th>Buyer</th>
                            <th>Product Item</th>
                            <th>Warna</th>
                            <th>Satuan</th>
                            <th>Saldo Awal</th>
                            <th>Pemasukan</th>
                            <th>Pengeluaran</th>
                            <th>Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-center"> TOTAL</th>
                            <th class="text-end"></th>
                            <th class="text-end"></th>
                            <th class="text-end"></th>
                            <th class="text-end"></th>
                        </tr>
                    </tfoot>
                </table>
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
            dropdownParent: $("#editMejaModal")
        })
    </script>

    <script>

        let tableFabric, tableAccesories, tableFg;

        document.addEventListener("DOMContentLoaded", () => {

            // set tanggal default TANPA trigger
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeFull = oneWeeksBefore.toISOString().slice(0,10);

            $("#tgl-awal").val(oneWeeksBeforeFull);
            $("#tgl-akhir").val(new Date().toISOString().slice(0,10));

            // ================= INIT DATATABLE =================
            tableFabric = $("#datatable-fabric").DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: {
                    url: '{{ route('laporan-mutasi-per-kategori') }}',
                    data: d => {
                        d.dateFrom = $('#tgl-awal').val();
                        d.dateTo = $('#tgl-akhir').val();
                        d.kategori = $("#kategori").val();
                    }
                },
                columns: [
                    {
                        data: 'barcode'
                    },
                    {
                        data: 'buyer'
                    },
                    {
                        data: 'jenis_item'
                    },
                    {
                        data: 'warna'
                    },
                    {
                        data: 'satuan'
                    },
                    {
                        data: 'saldo_awal',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'pemasukan',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'pengeluaran',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'saldo_akhir',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                ],
                footerCallback: function(row, data, start, end, display) {
                    let api = this.api();

                    function sum(colIndex) {
                        return api
                            .column(colIndex, { page: 'current' })
                            .data()
                            .reduce(function(a, b) {
                                return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                            }, 0);
                    }

                    let totalSaldoAwal   = sum(5);
                    let totalMasuk       = sum(6);
                    let totalKeluar      = sum(7);
                    let totalSaldoAkhir  = sum(8);

                    $(api.column(5).footer()).html(totalSaldoAwal.toFixed(2));
                    $(api.column(6).footer()).html(totalMasuk.toFixed(2));
                    $(api.column(7).footer()).html(totalKeluar.toFixed(2));
                    $(api.column(8).footer()).html(totalSaldoAkhir.toFixed(2));
                },
            });

            tableAccesories = $("#datatable-accesories").DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: {
                    url: '{{ route('laporan-mutasi-per-kategori') }}',
                    data: d => {
                        d.dateFrom = $('#tgl-awal').val();
                        d.dateTo = $('#tgl-akhir').val();
                        d.kategori = $("#kategori").val();
                    }
                },
                columns: [
                    {
                        data: 'barcode'
                    },
                    {
                        data: 'buyer'
                    },
                    {
                        data: 'nama_barang'
                    },
                    {
                        data: 'warna'
                    },
                    {
                        data: 'satuan'
                    },
                    {
                        data: 'saldo_awal',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'pemasukan',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'pengeluaran',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'saldo_akhir',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                ],
                footerCallback: function(row, data, start, end, display) {
                    let api = this.api();

                    function sum(colIndex) {
                        return api
                            .column(colIndex, { page: 'current' })
                            .data()
                            .reduce(function(a, b) {
                                return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                            }, 0);
                    }

                    let totalSaldoAwal   = sum(5);
                    let totalMasuk       = sum(6);
                    let totalKeluar      = sum(7);
                    let totalSaldoAkhir  = sum(8);

                    $(api.column(5).footer()).html(totalSaldoAwal.toFixed(2));
                    $(api.column(6).footer()).html(totalMasuk.toFixed(2));
                    $(api.column(7).footer()).html(totalKeluar.toFixed(2));
                    $(api.column(8).footer()).html(totalSaldoAkhir.toFixed(2));
                },
            });

            tableFg = $("#datatable-fg").DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: {
                    url: '{{ route('laporan-mutasi-per-kategori') }}',
                    data: d => {
                        d.dateFrom = $('#tgl-awal').val();
                        d.dateTo = $('#tgl-akhir').val();
                        d.kategori = $("#kategori").val();
                    }
                },
                columns: [
                    {
                        data: 'barcode'
                    },
                    {
                        data: 'buyer'
                    },
                    {
                        data: 'product_item'
                    },
                    {
                        data: 'warna'
                    },
                    {
                        data: 'satuan'
                    },
                    {
                        data: 'saldo_awal',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'pemasukan',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'pengeluaran',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'saldo_akhir',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                ],
                footerCallback: function(row, data, start, end, display) {
                    let api = this.api();

                    function sum(colIndex) {
                        return api
                            .column(colIndex, { page: 'current' })
                            .data()
                            .reduce(function(a, b) {
                                return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                            }, 0);
                    }

                    let totalSaldoAwal   = sum(5);
                    let totalMasuk       = sum(6);
                    let totalKeluar      = sum(7);
                    let totalSaldoAkhir  = sum(8);

                    $(api.column(5).footer()).html(totalSaldoAwal.toFixed(2));
                    $(api.column(6).footer()).html(totalMasuk.toFixed(2));
                    $(api.column(7).footer()).html(totalKeluar.toFixed(2));
                    $(api.column(8).footer()).html(totalSaldoAkhir.toFixed(2));
                },
            });

        });

        function dataTableReload() {
            let kategori = $('#kategori').val();

            $('#table-fabric, #table-accesories, #table-fg').addClass('d-none');
            $('#exportExcel').prop('disabled', false);

            if (kategori === 'FABRIC') {
                $('#table-fabric').removeClass('d-none');
                if (tableFabric) tableFabric.ajax.reload();
            } else if (kategori === 'ACCESORIES') {
                $('#table-accesories').removeClass('d-none');
                if (tableAccesories) tableAccesories.ajax.reload();
            } else if (kategori === 'FG') {
                $('#table-fg').removeClass('d-none');
                if (tableFg) tableFg.ajax.reload();
            }
        }

        async function exportExcel() {
            Swal.fire({
                title: "Exporting",
                html: "Please Wait...",
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            await $.ajax({
                url: "{{ route("export-laporan-mutasi-per-kategori") }}",
                type: "post",
                data: {
                    from : $("#tgl-awal").val(),
                    to : $("#tgl-akhir").val(),
                    kategori : $("#kategori").val()
                },
                xhrFields: { responseType : 'blob' },
                success: function (res) {
                    Swal.close();

                    iziToast.success({
                        title: 'Success',
                        message: 'Success',
                        position: 'topCenter'
                    });

                    var blob = new Blob([res]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Laporan Mutasi Per Kategori "+$("#tgl-awal").val()+" - "+$("#tgl-akhir").val()+".xlsx";
                    link.click();
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });

            Swal.close();
        }
    </script>
@endsection
