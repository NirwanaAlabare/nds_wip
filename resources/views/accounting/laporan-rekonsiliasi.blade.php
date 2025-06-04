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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-file-alt fa-sm"></i> Laporan Rekonsiliasi Ceisa - Signalbit</h5>
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

            <div class="col-12 col-md-2 mb-2">
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
                            <th>Jenis Dok</th>
                            <th>No Daftar</th>
                            <th>Tgl Daftar</th>
                            <th>No Aju</th>
                            <th>Tgl Aju</th>
                            <th>Supplier Ceisa</th>
                            <th>Supplier SB</th> 
                            <th>Satuan Ceisa</th>
                            <th>Satuan SB</th> 
                            <th>Qty Ceisa</th>
                            <th>Qty SB</th> 
                            <th>Diff Qty</th>
                            <th>Total Ceisa</th>
                            <th>Total IDR</th>
                            <th>Total SB</th>
                            <th>Total SB IDR</th>
                            <th>Diff Total</th>
                            <th>Status</th>
                            <th>Issue List</th>
                            <th>Description Update</th>
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
                url: '{{ route('report-rekonsiliasi-ceisa') }}',
                data: function(d) {
                    d.dateFrom = $('#from').val();
                    d.dateTo = $('#to').val();
                    d.jenis_dok = $('#jenis_dok').val();
                    d.status = $('#status').val();
                },
            },
            columns: [{
                data: 'kode_dokumen'
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
                data: 'nama_entitas'
            },
            {
                data: 'supplier'
            },
            {
                data: 'satuan_ciesa_tampil'
            },
            {
                data: 'satuan_sb'
            },
            {
                data: 'qty'
            },
            {
                data: 'qty_sb'
            },
            {
                data: 'diff_qty'
            },
            {
                data: 'total'
            },
            {
                data: 'total_idr'
            },
            {
                data: 'total_sb'
            },
            {
                data: 'total_sb_idr'
            },
            {
                data: 'diff_total'
            },
            {
                data: 'status'
            },
            {
                data: 'status_kesesuaian'
            },
            {
                data: 'keterangan_update'
            },
            
            ],
            columnDefs: [
            {
                targets: 7,
                className: "d-left",
                render: function(data) {
                    if (data) {
                        return data.replace(/,/g, '<br>');
                    } else {
                        return data;
                    }
                }
            },
            {
                targets: 8,
                className: "d-left",
                render: function(data) {
                    if (data) {
                        return data.replace(/,/g, '<br>');
                    } else {
                        return data;
                    }
                }
            },
            {
                targets: [9,10,11,12,13,14,15,16],
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
                url: '{{ route('export-rekonsiliasi-ceisa') }}',
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
                        link.download = "Laporan Rekonsiliasi Data Ceisa Dari  " + from + " sampai " +
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
