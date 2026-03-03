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
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-file-alt fa-sm"></i> Laporan Monitoring Subcont Packing</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><small>Supplier</small></label>
                        <select class="form-control select2bs4" id="txt_supp" name="txt_supp" style="width: 100%;">
                        <option selected="selected" value="">Pilih Supplier</option>
                        @foreach ($msupplier as $msupp)
                        <option value="{{ $msupp->id_supplier }}">
                            {{ $msupp->Supplier }}
                        </option>
                        @endforeach
                    </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label"><small>No PO</small></label>
                <select class="form-control select2bs4" id="txt_no_po" name="txt_no_po" style="width: 100%;" >
                    <option selected="selected" value="">Pilih PO</option>
                        @foreach ($no_po as $po)
                    <option value="{{ $po->pono }}">
                                {{ $po->pono }}
                    </option>
                        @endforeach
                </select>
                    </div>
                    <div class="mb-3">
                        {{-- <button class="btn btn-primary" onclick="export_excel()">Search</button> --}}
                        <input type='button' class='btn btn-primary' onclick="dataTableReload();" value="Tampilkan">
                        <!-- <button type='submit' name='submit' class='btn btn-success btn-sm'>
                            <i class="fas fa-file-excel"></i> Export</button> -->
                            <a onclick="export_excel()" class="btn btn-success position-relative">
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
                    <th>No PO</th>
                    <th>Supplier</th>
                    <th>buyer</th>
                    <th>WS</th>
                    <th>Style</th>
                    <th>Color</th>
                    <th>Size</th>
                    <th>Qty Out</th>
                    <th>Qty In</th>
                    <th>Qty In Reject</th>
                    <th>Balance</th>
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
        $(document).ready(function() {
            $(document).on('select2:open', function() {
        setTimeout(() => {
            let searchField = document.querySelector('.select2-container--open .select2-search__field');
            if (searchField) searchField.focus();
        }, 0);
    });

    $('.select2bs4').select2({
            theme: 'bootstrap4'
        });
});
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
        url: '{{ route('report-packing-monitoring-subcont') }}',
        data: function(d) {
            d.id_supplier = $('#txt_supp').val();
            d.no_po = $('#txt_no_po').val();
        },
    },
    columns: [
        { data: 'no_po' },
        { data: 'supplier' },
        { data: 'buyer' },
        { data: 'kpno' },
        { data: 'styleno' },
        { data: 'color' },
        { data: 'size' },
        { data: 'qty_out' },
        { data: 'qty_in' },
        { data: 'qty_in_reject' },
        { data: 'qty_sisa' }
    ],

    rowCallback: function(row, data) {

        let qty_in   = parseFloat(data.qty_in)   || 0;
        let qty_sisa = parseFloat(data.qty_sisa) || 0;

        // RESET dulu (penting kalau redraw)
        $(row).css('background-color', '');

        if (qty_in === 0) {
            // Putih / default
            $(row).css('background-color', '#ffffff');

        } else if (qty_sisa === 0) {
            // Hijau pucat
            $(row).css('background-color', '#d4edda');

        } else if (qty_in > 0 && qty_sisa > 0) {
            // Orange pucat
            $(row).css('background-color', '#fff3cd');
        }
    }
});


        function dataTableReload() {
            datatable.ajax.reload();
        }

        function export_excel() {
    let id_supplier = document.getElementById("txt_supp").value;
    let no_po = document.getElementById("txt_no_po").value;

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
        url: '{{ route('export-excel-packing-subcont-monitoring') }}',
        data: {
            id_supplier: id_supplier,
            no_po: no_po
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
                link.download = "Laporan Monitoring Subcont Packing.xlsx";
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
