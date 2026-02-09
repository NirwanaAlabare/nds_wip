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
<form action="{{ route('export_excel_mut_global') }}" method="get">
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-file-alt fa-sm"></i> Laporan Mutasi Global</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small>From</small></label>
                    <input type="date" class="form-control form-control-sm" id="from" name="from"
                    value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small>To</small></label>
                    <input type="date" class="form-control form-control-sm" id="to" name="to"
                    value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    {{-- <button class="btn btn-primary btn-sm" onclick="export_excel()">Search</button> --}}
                    <input type='button' class='btn btn-primary btn-sm' onclick="dataTableReload();" value="Search">
                        <!-- <button type='submit' name='submit' class='btn btn-success btn-sm'>
                            <i class="fas fa-file-excel"></i> Export</button> -->
                            <a onclick="export_excel()" class="btn btn-success position-relative btn-sm">
                                <i class="fas fa-file-excel"></i>
                                Export
                            </a>
                        </div>
                    </div>
                </form>
    <!-- <div class="d-flex justify-content-between">
            <div class="ml-auto">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
                <input type="text"  id="cari_item" name="cari_item" autocomplete="off" placeholder="Search Data..." onkeyup="caridata()">
            </div> -->
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-head-fixed 100 text-nowrap">
                    <thead>
                        <tr>
                            <th>Id Item</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Warna</th>
                            <th>Ukuran</th>
                            <th>satuan</th>
                            <th>Saldo Awal</th>
                            <th>Pemasukan</th>
                            <th>Pengeluaran</th>
                            <th>Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" style="text-align:center">TOTAL</th>
                            <th></th> <!-- sal_awal -->
                            <th></th> <!-- qty_in -->
                            <th></th> <!-- qty_out -->
                            <th></th> <!-- sal_akhir -->
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
        let datatable = $("#datatable").DataTable({
            ordering: true,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            deferLoading: 0,
            deferRender: true,     // SUPER PENTING
            scroller: true,
            ajax: {
                url: '{{ route('lap-mutasi-global') }}',
                data: function(d) {
                    d.dateFrom = $('#from').val();
                    d.dateTo = $('#to').val();
                },
            },
            columns: [
            { data: 'id_item' },
            { data: 'goods_code' },
            { data: 'itemdesc' },
            { data: 'color' },
            { data: 'size' },
            { data: 'unit' },
            { data: 'sal_awal' },
            { data: 'qty_in' },
            { data: 'qty_out' },
            { data: 'sal_akhir' }
            ],
            columnDefs: [
            {
                targets: [6, 7, 8, 9],
                render: function(data, type, row, meta) {
                    let val = parseFloat(data);
                    return isNaN(val) ? "0" : val.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            }
            ],
            footerCallback: function (row, data, start, end, display) {
                var api = this.api();

        // fungsi untuk hitung total kolom
        var intVal = function (i) {
            return typeof i === 'string'
            ? i.replace(/[\.,]/g, '').replace(',', '.')*1
            : typeof i === 'number'
            ? i : 0;
        };

        // hitung total per kolom
        // let totalAwal = api.column(4, { page: 'current' }).data().reduce((a, b) => intVal(a) + intVal(b), 0);
        // let totalIn   = api.column(5, { page: 'current' }).data().reduce((a, b) => intVal(a) + intVal(b), 0);
        // let totalOut  = api.column(6, { page: 'current' }).data().reduce((a, b) => intVal(a) + intVal(b), 0);
        // let totalAkhir= api.column(7, { page: 'current' }).data().reduce((a, b) => intVal(a) + intVal(b), 0);
        let totalAwal  = api.column(6).data().reduce((a, b) => intVal(a) + intVal(b), 0);
        let totalIn    = api.column(7).data().reduce((a, b) => intVal(a) + intVal(b), 0);
        let totalOut   = api.column(8).data().reduce((a, b) => intVal(a) + intVal(b), 0);
        let totalAkhir = api.column(9).data().reduce((a, b) => intVal(a) + intVal(b), 0);

        // update ke tfoot
        $(api.column(6).footer()).html(totalAwal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $(api.column(7).footer()).html(totalIn.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $(api.column(8).footer()).html(totalOut.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $(api.column(9).footer()).html(totalAkhir.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    }
});


        function dataTableReload() {
            datatable.ajax.reload();
        }

        function export_excel() {
            let from = document.getElementById("from").value;
            let to = document.getElementById("to").value;

            Swal.fire({
                title: 'Please Wait,',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "get",
                url: '{{ route('export_excel_mut_global') }}',
                data: {
                    from: from,
                    to: to
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    {
                        swal.close();
                        Swal.fire({
                            title: 'Data Berhasil Di Export!',
                            icon: "success",
                            showConfirmButton: true,
                            allowOutsideClick: false
                        });
                        var blob = new Blob([response]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Laporan Mutasi Global Dari  " + from + " sampai " +
                        to + ".xlsx";
                        link.click();

                    }
                },
            });
        }

        function caridata() {
        // Declare variables
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("cari_item");
        filter = input.value.toUpperCase();
        table = document.getElementById("datatable");
        tr = table.getElementsByTagName("tr");

        // Loop through all table rows, and hide those who don't match the search query
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[8]; //kolom ke berapa
            if (td) {
                txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
</script>
@endsection
