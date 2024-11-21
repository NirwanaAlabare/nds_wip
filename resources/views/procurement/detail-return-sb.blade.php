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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-file-alt fa-sm"></i> List Return</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-1 mb-3">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label>Tipe Item</label>
                        <select class="form-control select2supp" id="item_so" name="item_so" style="width: 100%;">
                            <option selected="selected" value="">ALL</option>
                            @foreach ($item_so as $item)
                            <option value="{{ $item->kode_pilihan }}">{{ $item->nama_pilihan }}</option>
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
                <table id="datatable" class="table table-bordered table-striped table-head-fixed table-sm w-100 text-nowrap">
                    <thead>
                        <tr>
                            <th>No Return</th>
                            <th>Tgl Return</th>
                            <th>Tipe Return</th>
                            <th>Style</th>
                            <th>No WS</th>
                            <th>Penerima</th>
                            <th>No Invoice</th>
                            <th>Jenis BC</th>
                            <th>No Dokumen</th>
                            <th>Tgl Dokumen</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-edit-lokasi">
        <form action="{{ route('simpan-edit-returnsb') }}" method="post" onsubmit="submitForm(this, event)">
           @method('GET')
           <div class="modal-dialog modal-md modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h4 class="modal-title">Edit Tipe Return</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!--  -->
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <div class="form-group">
                                            <label>ID</label>
                                            <input type="text" class="form-control " id="txt_id" name="txt_id" value="" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <div class="form-group">
                                            <label>Item Type</label>
                                            <input class="form-control" list="txtarea" id="txt_area" name="txt_area" value="">
                                            <datalist id="txtarea">
                                                @foreach ($arealok as $alok)
                                                <option value="{{ $alok->nama_pilihan }}">
                                                    {{ $alok->nama_pilihan }}
                                                </option>
                                                @endforeach
                                            </datalist>
                                        </div>
                                    </div>
                                </div>

                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Tutup</button>
                    <button type="submit" class="btn btn-sb toastsDefaultDanger"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
                </div>
            </div>
        </div>
    </form>
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
            url: '{{ route('detail-return-sb') }}',
            data: function(d) {
                d.itemSO = $('#item_so').val();
                d.dateFrom = $('#from').val();
                d.dateTo = $('#to').val();
            },
        },
        columns: [{
            data: 'bppbno_int'
        },
        {
            data: 'bppbdate'
        },
        {
            data: 'status_return'
        },
        {
            data: 'stylenya'
        },
        {
            data: 'wsno'
        },
        {
            data: 'supplier'
        },
        {
            data: 'invno'
        },
        {
            data: 'jenis_dok'
        },
        {
            data: 'nomor_aju'
        },
        {
            data: 'tanggal_aju'
        },
        {
            data: 'status'
        },
        {
            data: 'created_by'
        },
        {
            data: 'bppbno_int'
        }
        ],
        columnDefs: [
            // {
            //     targets: [25],
            //     className: "d-none",
            //     render: (data, type, row, meta) => data ? data : "-"
            // },
            {
                targets: [0,1,2,3,4,5,6,7,8,9,10,11],
                render: (data, type, row, meta) => data ? data : "-"
            },
            {
                targets: [12],
                render: (data, type, row, meta) => {
                    console.log(row);
                    // if (row.status == 'Active') {
                        return `<div class='d-flex gap-1 justify-content-center'>
                        <button type='button' class='btn btn-sm btn-warning' href='javascript:void(0)' onclick='editdata("` + row.bppbno_int + `","` + row.status_return + `")'><i class="fa-solid fa-pen-to-square"></i></button>
                        </div>`;
                    // }
                }
            }
            ]
        });

    function dataTableReload() {
        datatable.ajax.reload();
    }

    function editdata($bppbno_int,$status_return){
        // alert($id);
        let bppbno_int  = $bppbno_int;
        let status_return  = $status_return;


        $('#txt_id').val(bppbno_int);
        $('#txt_area').val(status_return);
        $('#modal-edit-lokasi').modal('show');
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
            url: '{{ route('export_excel_detail_return_sb') }}',
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
                    link.download = "List Return Detail" + ".xlsx";
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
