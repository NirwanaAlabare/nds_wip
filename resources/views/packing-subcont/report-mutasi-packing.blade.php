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
    <form action="{{ route('export_excel_pemasukanroll') }}" method="get">
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-file-alt fa-sm"></i> Laporan Mutasi Subcont Packing</h5>
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
                        <input type='button' class='btn btn-primary btn-sm' onclick="dataTableReload();" value="Tampilkan">
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
                    <th>Item desc</th>
                    <th>No PO</th>
                    <th>Supplier</th>
                    <th>buyer</th>
                    <th>WS</th>
                    <th>Style</th>
                    <th>Color</th>
                    <th>Size</th>
                    <th>Saldo Awal</th>
                    <th>Qty Out</th>
                    <th>Qty In</th>
                    <th>Saldo Akhir</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
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
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route('report-packing-mutasi-subcont') }}',
                data: function(d) {
                    d.dateFrom = $('#from').val();
                    d.dateTo = $('#to').val();
                },
            },
            columns: [{
                    data: 'id_item'
                },
                {
                    data: 'itemdesc'
                },
                {
                    data: 'no_po'
                },
                {
                    data: 'supplier'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'kpno'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'saldo_awal'
                },
                {
                    data: 'qty_out'
                },
                {
                    data: 'qty_in'
                },
                {
                    data: 'saldo_akhir'
                }
            ],
            columnDefs: [
            // {
            //     targets: [21],
            //     className: "d-none",
            //     render: (data, type, row, meta) => data ? data : "-"
            // },
            ]
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
        url: '{{ route('export-excel-packing-subcont-mutasi') }}',
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
                link.download = "Laporan Mutasi Subcont Packing Dari  " + from + " sampai " +
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
            td = tr[i].getElementsByTagName("td")[21]; //kolom ke berapa
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
