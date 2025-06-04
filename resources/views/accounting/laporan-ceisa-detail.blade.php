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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-file-alt fa-sm"></i> Laporan Data Ceisa Detail</h5>
        </div>
        <div class="card-body">
         <div class="d-flex flex-wrap align-items-end gap-2 mb-3">
            <div class="col-12 col-md-3 mb-2">
                <label class="form-label mb-1"><small>Jenis Dok</small></label>
                <select class="form-control form-control-sm select2supp" id="jenis_dok" name="jenis_dok" style="width: 100%;">
                    <option selected value="ALL">ALL</option>
                    @foreach ($jenisdok as $jdok)
                    <option value="{{ $jdok->nama_pilihan }}">{{ $jdok->nama_pilihan }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-2 mb-2" style="display: none;">
                <label class="form-label mb-1"><small>Status</small></label>
                <select class="form-control form-control-sm select2supp" id="status" name="status" style="width: 100%;">
                    <option selected value="ALL">ALL</option>
                    @foreach ($statusdok as $sdok)
                    <option value="{{ $sdok->isi }}">{{ $sdok->tampil }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2 mb-2">
                <label class="form-label mb-1"><small>From</small></label>
                <input type="date" class="form-control form-control" id="from" name="from" value="{{ date('Y-m-d') }}">
            </div>

            <div class="col-6 col-md-2 mb-2">
                <label class="form-label mb-1"><small>To</small></label>
                <input type="date" class="form-control form-control" id="to" name="to" value="{{ date('Y-m-d') }}">
            </div>

            <div class="col-12 col-md-auto mb-2 d-flex gap-2">
                <button type="button" class="btn btn-primary" onclick="dataTableReload();"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                <a onclick="export_excel()" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Export
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
                            <th>No Upload</th>
                            <th>Jenis Dokumen</th>
                            <th>Supplier</th>
                            <th>No Daftar</th>
                            <th>Tgl Daftar</th>
                            <th>No Aju</th>
                            <th>Tgl Aju</th> 
                            <th>Kode Barang</th> 
                            <th>Nama Barang</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>Curr</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Rate</th>
                            <th>Total IDR</th>
                            <th>Uploaded By</th>
                            <th>Uploaded Date</th>
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
        $('.select2supp').select2({
            theme: 'bootstrap4'
        })

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
                url: '{{ route('report-ceisa-detail') }}',
                data: function(d) {
                    d.dateFrom = $('#from').val();
                    d.dateTo = $('#to').val();
                    d.jenis_dok = $('#jenis_dok').val();
                    d.status = $('#status').val();
                },
            },
            columns: [{
                data: 'no_dokumen'
            },
            {
                data: 'kode_dokumen_format'
            },
            {
                data: 'nama_entitas'
            },
            {
                data: 'no_daftar'
            },
            {
                data: 'tgl_daftar'
            },
            {
                data: 'no_aju'
            },
            {
                data: 'tgl_aju'
            },
            {
                data: 'kode_barang'
            },
            {
                data: 'uraian'
            },
            {
                data: 'qty'
            },
            {
                data: 'unit'
            },
            {
                data: 'curr'
            },
            {
                data: 'price'
            },
            {
                data: 'cif'
            },
            {
                data: 'rates'
            },
            {
                data: 'cif_rupiah'
            },
            {
                data: 'created_by'
            },
            {
                data: 'created_date'
            },
            
            ],
            columnDefs: [
            {
                targets: [9,12,13,14,15],
            className: "text-right",  // kanan biar rapi
            render: function(data) {
                if (data == null || data === '') return data;
                // pastikan data angka, kalau string parse dulu
                let num = Number(data.toString().replace(/,/g, '')); 
                if (isNaN(num)) return data;

                // format angka dengan ribuan koma dan desimal titik (2 digit)
                return num.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2});
            }
        }
        ]

    });

        function dataTableReload() {
            datatable.ajax.reload();
        }

        function export_excel() {
            let from = document.getElementById("from").value;
            let to = document.getElementById("to").value;
            let jenis_dok = document.getElementById("jenis_dok").value;
            let status = document.getElementById("status").value;

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
                url: '{{ route('export-ceisa-detail') }}',
                data: {
                    from: from,
                    to: to,
                    jenis_dok: jenis_dok,
                    status: status
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
                        link.download = "Laporan Data Ceisa Detail Dari  " + from + " sampai " +
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
