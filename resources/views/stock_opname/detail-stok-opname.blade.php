
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
<form action="{{ route('export_excel_pemasukan') }}" method="get">
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">
                <i class="fas fa-file-alt fa-sm"></i> Detail Stock Opname
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">

                <!-- Tipe Item -->
                <div class="col-12 col-md-3">
                    <label for="item_so" class="form-label">Tipe Item</label>
                    <select class="form-control select2supp" id="item_so" name="item_so" style="width: 100%;">
                        <option value="" selected>Select item</option>
                        @foreach ($item_so as $item)
                        <option value="{{ $item->nama_pilihan }}">{{ $item->nama_pilihan }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- From Date -->
                <div class="col-6 col-md-2">
                    <label for="from" class="form-label">From</label>
                    <input type="date" class="form-control" id="from" name="from" value="{{ date('Y-m-d') }}">
                </div>

                <!-- To Date -->
                <div class="col-6 col-md-2">
                    <label for="to" class="form-label">To</label>
                    <input type="date" class="form-control" id="to" name="to" value="{{ date('Y-m-d') }}">
                </div>

                <!-- Buttons -->
                <div class="col-12 col-md-5 d-flex gap-2">
                    <input type="button" class="btn btn-primary" onclick="dataTableReload();" value="Search">
                    <a href="javascript:void(0);" onclick="export_excel()" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Export
                    </a>
                </div>

            </div>
        

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
                            <th>Tipe Item</th>
                            <th>No Transaksi</th>
                            <th>Tgl Transaksi</th>
                            <th>No Barcode</th>
                            <th>Lokasi Scan</th>
                            <th>Lokasi Aktual</th>
                            <th>ID JO</th>
                            <th>ID Item</th>
                            <th>Kode Item</th>
                            <th>Deskripsi Item</th>
                            <th>No Lot</th>
                            <th>No Roll</th>
                            <th>Qty Saldo</th>
                            <th>Qty SO</th>
                            <th>Unit</th>
                            <th>Created by</th>
                            <th>Created date</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="12" style="text-align:right; font-size: 14pz;">TOTAL :</th>
                            <th></th> <!-- qty_so -->
                            <th></th> <!-- qty -->
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                </table>
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
    <script type="text/javascript">
        $('.select2supp').select2({
            theme: 'bootstrap4'
        })
    </script>
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
            ajax: {
                url: '{{ route('detail-stok-opname') }}',
                data: function(d) {
                    d.itemSO = $('#item_so').val();
                    d.dateFrom = $('#from').val();
                    d.dateTo = $('#to').val();
                },
            },
            columns: [
            { data: 'tipe_item' },
            { data: 'no_dokumen' },
            { data: 'tgl_dokumen' },
            { data: 'no_barcode' },
            { data: 'lokasi_scan' },
            { data: 'lokasi_aktual' },
            { data: 'id_jo' },
            { data: 'id_item' },
            { data: 'goods_code' },
            { data: 'itemdesc' },
            { data: 'no_lot' },
            { data: 'no_roll' },
            { data: 'qty_so' },
            { data: 'qty' },
            { data: 'unit' },
            { data: 'created_by' },
            { data: 'created_at' }
            ],
            columnDefs: [
            {
                targets: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16],
                render: (data, type, row, meta) => data ? data : "-"
            },
            {
            targets: [12,13], // qty_so dan qty
            render: $.fn.dataTable.render.number(',', '.', 0, '') // number format
        }
        ],
        footerCallback: function(row, data, start, end, display) {
            let api = this.api();

        // Total kolom qty_so (index 12) dan qty (index 13)
        let intVal = function(i) {
            return typeof i === 'string' ? i.replace(/[\$,]/g, '')*1 : typeof i === 'number' ? i : 0;
        };

        let totalQtySO = api.column(12).data().reduce((a, b) => intVal(a) + intVal(b), 0);
        let totalQty = api.column(13).data().reduce((a, b) => intVal(a) + intVal(b), 0);

        // Tampilkan di footer
        $(api.column(12).footer()).html(totalQtySO.toLocaleString());
        $(api.column(13).footer()).html(totalQty.toLocaleString());
    }
});


        function dataTableReload() {
            datatable.ajax.reload();
        }

        function export_excel() {
            let itemso = document.getElementById("item_so").value;
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
                url: '{{ route('export_excel_detail_so') }}',
                data: {
                    itemso: itemso,
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
                        link.download = "Detail Stock Opname  " + itemso + ".xlsx";
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
            td = tr[i].getElementsByTagName("td")[25]; //kolom ke berapa
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
