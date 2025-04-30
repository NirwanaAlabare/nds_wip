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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-file-alt fa-sm"></i> Detail Stock Opname</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-1 mb-3">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label>Tipe Item</label>
                        <select class="form-control select2supp" id="item_so" name="item_so" style="width: 100%;">
                            <option selected="selected" value="">Select item</option>
                            @foreach ($item_so as $item)
                            <option value="{{ $item->nama_pilihan }}">{{ $item->nama_pilihan }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">From</label>
                        <input type="date" class="form-control form-control" id="from" name="from"
                        value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">To</label>
                        <input type="date" class="form-control form-control" id="to" name="to"
                        value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        {{-- <button class="btn btn-primary btn" onclick="export_excel()">Search</button> --}}
                        <input type='button' class='btn btn-primary btn' onclick="dataTableReload();" value="Search">
                        <a onclick="export_excel()" class="btn btn-success position-relative btn">
                            <i class="fas fa-file-excel"></i>
                            Export
                        </a>
                        <!-- <button type='submit' name='submit' class='btn btn-success btn-sm'>
                            <i class="fas fa-file-excel"></i> Export</button> -->
                        </div>
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
    <script type="text/javascript">
        $('.select2supp').select2({
            theme: 'bootstrap4'
        })
    </script>
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
                url: '{{ route('detail-stok-opname') }}',
                data: function(d) {
                    d.itemSO = $('#item_so').val();
                    d.dateFrom = $('#from').val();
                    d.dateTo = $('#to').val();
                },
            },
            columns: [{
                data: 'tipe_item'
            },
            {
                data: 'no_dokumen'
            },
            {
                data: 'tgl_dokumen'
            },
            {
                data: 'no_barcode'
            },
            {
                data: 'lokasi_scan'
            },
            {
                data: 'lokasi_aktual'
            },
            {
                data: 'id_jo'
            },
            {
                data: 'id_item'
            },
            {
                data: 'goods_code'
            },
            {
                data: 'itemdesc'
            },
            {
                data: 'no_lot'
            },
            {
                data: 'no_roll'
            },
            {
                data: 'qty_so'
            },
            {
                data: 'qty'
            },
            {
                data: 'unit'
            },
            {
                data: 'created_by'
            },
            {
                data: 'created_at'
            }
            ],
            columnDefs: [
            // {
            //     targets: [25],
            //     className: "d-none",
            //     render: (data, type, row, meta) => data ? data : "-"
            // },
            {
                targets: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16],
                render: (data, type, row, meta) => data ? data : "-"
            }
            ]
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
