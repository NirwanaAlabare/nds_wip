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
            <h5 class="card-title fw-bold mb-0"> Laporan Pengeluaran Per Kategori</h5>
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
                            <th>No. BPB</th>
                            <th>Tgl BPB</th>
                            <th>Barcode</th>
                            <th>Lokasi</th>
                            <th>Buyer</th>
                            <th>Keterangan</th>
                            <th>Jenis Item</th>
                            <th>Warna</th>
                            <th>Lot</th>
                            <th>No Roll</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>Qty Out</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div id="table-accesories" class="table-responsive d-none">
                <table id="datatable-accesories" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No. BPB</th>
                            <th>Tgl BPB</th>
                            <th>Barcode</th>
                            <th>No Box/Koli</th>
                            <th>Buyer</th>
                            <th>Worksheet</th>
                            <th>Nama Barang</th>
                            <th>Kode</th>
                            <th>Warna</th>
                            <th>Size</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>Qty KGM</th>
                            <th>Keterangan</th>
                            <th>Lokasi</th>
                            <th>Qty Out</th>
                            <th>Qty KGM Out</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div id="table-fg" class="table-responsive d-none">
                <table id="datatable-fg" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No. BPB</th>
                            <th>Tgl BPB</th>
                            <th>Barcode</th>
                            <th>No Koli</th>
                            <th>Buyer</th>
                            <th>No WS</th>
                            <th>Style</th>
                            <th>Product Item</th>
                            <th>Warna</th>
                            <th>Size</th>
                            <th>Grade</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>Keterangan</th>
                            <th>Lokasi</th>
                            <th>Qty Out</th>
                        </tr>
                    </thead>
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
        document.addEventListener("DOMContentLoaded", () => {
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            $("#tgl-awal").val(oneWeeksBeforeFull).trigger("change");

            window.addEventListener("focus", () => {
                $('#datatable').DataTable().ajax.reload(null, false);
            });
        });

        // $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        // $('#datatable thead tr:eq(1) th').each(function(i) {
        //     if (i != 5) {
        //         var title = $(this).text();
        //         $(this).html('<input type="text" class="form-control form-control-sm"/>');

        //         $('input', this).on('keyup change', function() {
        //             if (datatable.column(i).search() !== this.value) {
        //                 datatable
        //                     .column(i)
        //                     .search(this.value)
        //                     .draw();
        //             }
        //         });
        //     } else {
        //         $(this).empty();
        //     }
        // });

        let tableFabric = $("#datatable-fabric").DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            pageLength: 10,
            ajax: {
                url: '{{ route('laporan-pengeluaran-per-kategori') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                    d.kategori = $("#kategori").val();
                },
            },
            columns: [
                {
                    data: 'no_bpb'
                },
                {
                    data: 'tgl_bpb'
                },
                {
                    data: 'barcode'
                },
                {
                    data: 'lokasi'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'keterangan'
                },
                {
                    data: 'jenis_item'
                },
                {
                    data: 'warna'
                },
                {
                    data: 'lot'
                },
                {
                    data: 'no_roll'
                },
                {
                    data: 'qty',
                    className: 'text-end',
                    render: function(data, type, row) {
                        return parseFloat(data).toFixed(2);
                    }
                },
                {
                    data: 'satuan'
                },
                {
                    data: 'qty_out',
                    className: 'text-end',
                    render: function(data, type, row) {
                        return parseFloat(data).toFixed(2);
                    }
                },
            ],
        });

        let tableAccesories = $("#datatable-accesories").DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            pageLength: 10,
            ajax: {
                url: '{{ route('laporan-pengeluaran-per-kategori') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                    d.kategori = $("#kategori").val();
                },
            },
            columns: [
                { data: 'no_bpb' },
                { data: 'tgl_bpb' },
                { data: 'barcode' },
                { data: 'no_box' },
                { data: 'buyer' },
                { data: 'worksheet' },
                { data: 'nama_barang' },
                { data: 'kode' },
                { data: 'warna' },
                { data: 'size' },
                {
                    data: 'qty',
                    className: 'text-end',
                    render: function(data) {
                        return parseFloat(data).toFixed(2);
                    }
                },
                { data: 'satuan' },
                {
                    data: 'qty_kgm',
                    className: 'text-end',
                    render: function(data) {
                        return parseFloat(data).toFixed(2);
                    }
                },
                { data: 'keterangan' },
                { data: 'lokasi' },
                {
                    data: 'qty_out',
                    className: 'text-end',
                    render: function(data, type, row) {
                        return parseFloat(data).toFixed(2);
                    }
                },
                {
                    data: 'qty_kgm_out',
                    className: 'text-end',
                    render: function(data, type, row) {
                        return parseFloat(data).toFixed(2);
                    }
                },
            ],
        });

        let tableFg = $("#datatable-fg").DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            pageLength: 10,
            ajax: {
                url: '{{ route('laporan-pengeluaran-per-kategori') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                    d.kategori = $("#kategori").val();
                },
            },
            columns: [
                { data: 'no_bpb' },
                { data: 'tgl_bpb' },
                { data: 'barcode' },
                { data: 'no_koli' },
                { data: 'buyer' },
                { data: 'no_ws' },
                { data: 'style' },
                { data: 'product_item' },
                { data: 'warna' },
                { data: 'size' },
                { data: 'grade' },
                {
                    data: 'qty',
                    className: 'text-end',
                    render: function(data) {
                        return parseFloat(data).toFixed(2);
                    }
                },
                { data: 'satuan' },
                { data: 'keterangan' },
                { data: 'lokasi' },
                {
                    data: 'qty_out',
                    className: 'text-end',
                    render: function(data, type, row) {
                        return parseFloat(data).toFixed(2);
                    }
                },
            ],
        });

        function dataTableReload() {
            let kategori = $('#kategori').val();

            $('#table-fabric').addClass('d-none');
            $('#table-accesories').addClass('d-none');
            $('#table-fg').addClass('d-none');

            if (kategori === 'FABRIC') {
                $('#exportExcel').prop('disabled', false);
                $('#table-fabric').removeClass('d-none');
                tableFabric.ajax.reload();
            }else if (kategori === 'ACCESORIES') {
                $('#exportExcel').prop('disabled', false);
                $('#table-accesories').removeClass('d-none');
                tableAccesories.ajax.reload();
            }else if (kategori === 'FG') {
                $('#exportExcel').prop('disabled', false);
                $('#table-fg').removeClass('d-none');
                tableFg.ajax.reload();
            }else{
                $('#table-fabric').removeClass('d-none');
                tableFabric.ajax.reload();
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
                url: "{{ route("export-laporan-pengeluaran-per-kategori") }}",
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
                    link.download = "Laporan Pengeluaran Per Kategori "+$("#tgl-awal").val()+" - "+$("#tgl-akhir").val()+".xlsx";
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
