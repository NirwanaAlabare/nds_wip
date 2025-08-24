@extends('layouts.index')

@section('custom-link')
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
<!-- <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script> -->

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    @endsection

    @section('content')
    <form method="post">
        @method('GET')
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0">Data Item</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div class="col-md-12">
                        <div class="form-group row">

                            <div class="col-md-3">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label>Tipe Item</label>
                                        <select class="form-control select2supp" id="item_so" name="item_so" style="width: 100%;" onchange="getdatanomor()">
                                            <option selected="selected" value="">Select item</option>
                                            @foreach ($item_so as $item)
                                            <option value="{{ $item->nama_pilihan }}">{{ $item->nama_pilihan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label class="form-label">Date Filter</label>
                                        <input type="date" class="form-control form-control" id="tgl_filter" name="tgl_filter"
                                        value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-5" style="padding-top: 0.5rem;">
                                <div class="mt-4 ">
                                    <input type='button' class='btn btn-primary btn' onclick="dataTableReload();" value="Search">
                                    <button type="button" class="btn btn-success btn toastsDefaultDanger" id="btncopy" name="btncopy" onclick="copysaldo()" disabled><i class="fa fa-clone" aria-hidden="true"></i> Copy Saldo</button>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label>No Dokumen</label>
                                        <select class="form-control select2supp" id="no_dok_cs" name="no_dok_cs" style="width: 100%;" onchange="ubahtombol()">

                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-5" style="padding-top: 0.5rem;">
                                <div class="mt-4 ">
                                    <button type="button" class="btn btn-warning btn toastsDefaultDanger" id="btnreplace" name="btnreplace" onclick="replacesaldo()" disabled><i class="fa fa-clone" aria-hidden="true"></i> Replace</button>
                                </div>
                            </div>

                            <div id="divpartial" class="col-md-8" style="padding-top: 0.5rem;display:none">
                                <div class="mb-1 ">
                                    <label>Partial Copy OR Replace Saldo</label>
                                    <select class='form-control select2barcode' multiple='multiple' style='width: 100%;height: 20px;' name='partial_data' id='partial_data'>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped table-head-fixed text-nowrap">
                        <thead>
                            <tr>
                                <th class="text-center">Lokasi</th>
                                <th class="text-center">ID JO</th>
                                <th class="text-center">WS</th>
                                <th class="text-center">Style</th>
                                <th class="text-center">Buyer</th>
                                <th class="text-center">ID Item</th>
                                <th class="text-center">Item Desc</th>
                                <th class="text-center">Total Roll</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>

    <div class="modal fade " id="modal_det" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-light">
                <h4 class="modal-title" id="modal_title1">11</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <!-- style="height: 400px" -->
                        <div class="table-responsive" id="table_modal">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('custom-script')
<!-- DataTables & Plugins -->
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<!-- <script src="{{ asset('plugins/ionicons/js/ionicons.esm.js') }}"></script>
    <script src="{{ asset('plugins/ionicons/js/ionicons.js') }}"></script> -->

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2master').select2({
            theme: 'bootstrap4'
        })
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        $('.select2roll').select2({
            theme: 'bootstrap4'
        })
        $('.select2supp').select2({
            theme: 'bootstrap4'
        })
        $('.select2type').select2({
            theme: 'bootstrap4'
        })
        $('.select2barcode').select2({
            theme: 'bootstrap4'
        })

    </script>

    <script type="text/javascript">
        $('.select2pchtype').select2({
            theme: 'bootstrap4'
        })
    </script>
    <script type="text/javascript">
        function ubahtombol(){
            let no_dok_cs = document.getElementById("no_dok_cs").value;

            if (no_dok_cs != '') {
                $("#btncopy").prop("disabled", true);
                $("#btnreplace").prop("disabled", false);

            }else{
                $("#btncopy").prop("disabled", false);
                $("#btnreplace").prop("disabled", true);

            }

            // var element = document.getElementById("divpartial");
            // element.style.display = 'block';

            // let tgl_filter = document.getElementById("tgl_filter").value;
            // let item_so = document.getElementById("item_so").value;
            // return $.ajax({
            //     headers: {
            //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            //     },
            //     url: '{{ route("get-list-partial-so-replace") }}',
            //     type: 'get',
            //     data: {
            //         tgl_filter: tgl_filter,
            //         item_so: item_so,
            //         no_dok_cs: no_dok_cs,
            //     },
            //     success: function (res) {
            //         if (res) {
            //             document.getElementById('partial_data').innerHTML = res;
            //             // $("#txt_barcode").focus();
            //         }
            //     }
            // });

        }

        function getdatanomor() {
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('get-nomor-so') }}',
                type: 'get',
                data: {
                    item_so: $('#item_so').val(),
                },
                success: function(res) {
                    if (res) {
                        document.getElementById('no_dok_cs').innerHTML = res;
                    }
                },
            });
        }

    </script>
    <script type="text/javascript">
        function submitappForm(e, evt) {
            evt.preventDefault();

            clearModified();

            $.ajax({
                url: e.getAttribute('action'),
                type: e.getAttribute('method'),
                data: new FormData(e),
                processData: false,
                contentType: false,
                success: async function(res) {
                    if (res.status == 200) {
                        console.log(res);

                        // e.reset();

                        // $('#cbows').val("").trigger("change");
                        // $("#cbomarker").prop("disabled", true);

                        Swal.fire({
                            icon: 'success',
                            title: 'Data Berhasil Dikonfirmasi',
                            // html: "No. Form Cut : <br>" + res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 2000,
                            timerProgressBar: true
                        })

                        dataTableReload();
                    }
                },

            });
        }
    </script>

    <script type="text/javascript">
        function showdata(data) {
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-data-penerimaan") }}',
                type: 'get',
                data: {
                    no_bpb: data,
                },
                success: function (res) {
                    if (res) {
                        $('#modal_det').modal('show');
                        $('#modal_title1').html(data);
                        document.getElementById('table_modal').innerHTML = res;
                        $("#tableshow").DataTable({
                            "responsive": true,
                            "autoWidth": false,
                        })
                    }
                }
            });
        }
    </script>

    <script>

        let datatable = $("#datatable").DataTable({
         serverSide: true,
         processing: true,
         ordering: false,
         scrollX: true,
         autoWidth: false,
         scrollY: true,
         pageLength: 10,
         ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('data-rak') }}',
            dataType: 'json',
            dataSrc: 'data',
            data: function(d) {
                d.tgl_filter = $('#tgl_filter').val();
                d.item_so = $('#item_so').val();
            },
        },
        columns: [{
            data: 'kode_lok'
        },
        {
            data: 'id_jo'
        },
        {
            data: 'kpno'
        },
        {
            data: 'styleno'
        },
        {
            data: 'buyer'
        },
        {
            data: 'id_item'
        },
        {
            data: 'itemdesc'
        },
        {
            data: 'ttl_roll'
        },
        {
            data: 'qty'
        },
        {
            data: 'unit'
        }

        ],
        columnDefs: [
        {
            targets: [4],
            className: "d-none",
        },
        {
            targets: [0],
            render: (data, type, row, meta) => data ? data : "-"
        },
        {
            targets: [1],
            className: "d-none",
        },
        {
            targets: [2],
            className: "d-none",
        },
        {
            targets: [3],
            className: "d-none",
        }
        // {
        //     targets: [3],
        //     render: (data, type, row, meta) => data ? data : "-"
        // },
        // {
        //     targets: [5],
        //     render: (data, type, row, meta) => data ? data : "-"
        // },
        // {
        //     targets: [7],
        //     render: (data, type, row, meta) => data ? data : "-"
        // }
        ]
    });

        async function dataTableReload() {
            datatable.ajax.reload();
            $("#btncopy").prop("disabled", false);
            var element = document.getElementById("divpartial");
            element.style.display = 'block';

            let tgl_filter = document.getElementById("tgl_filter").value;
            let item_so = document.getElementById("item_so").value;
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-list-partial-so") }}',
                type: 'get',
                data: {
                    tgl_filter: tgl_filter,
                    item_so: item_so,
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('partial_data').innerHTML = res;
                        // $("#txt_barcode").focus();
                    }
                }
            });
        }
    </script>
    <script type="text/javascript">
        function copysaldo(){
            // let partial_data = document.getElementById("partial_data").value;
            let partial_data = $('#partial_data').val();
            let text1 = "'";
            let kodenya = text1.concat(partial_data, "'");
            let kodebarcode = kodenya.toString();
            let data_partial = kodebarcode.replace(/,/g,"','");

            if (data_partial == "''") {
                let tgl_filter = document.getElementById("tgl_filter").value;
                let item_so = document.getElementById("item_so").value;

                Swal.fire({
                    title: 'Please Wait...',
                    html: 'Copy Saldo...',
                    didOpen: () => {
                        Swal.showLoading()
                    },
                    allowOutsideClick: false,
                });

                $.ajax({
                    type: "get",
                    url: '{{ route('copy-saldo-stokopname') }}',
                    data: {
                        tgl_filter: tgl_filter,
                        item_so: item_so
                    },
                    success: function(response) {
                        {
                            swal.close();
                            Swal.fire({
                                title: response.message,
                                icon: "success",
                                showConfirmButton: true,
                                allowOutsideClick: false
                            }).then(async (result) => {
                                window.location.reload()
                            });

                        }
                    },
                });
            }else{
                let tgl_filter = document.getElementById("tgl_filter").value;
                let item_so = document.getElementById("item_so").value;

                Swal.fire({
                    title: 'Please Wait...',
                    html: 'Copy Saldo...',
                    didOpen: () => {
                        Swal.showLoading()
                    },
                    allowOutsideClick: false,
                });

                $.ajax({
                    type: "get",
                    url: '{{ route('copy-saldo-stokopname-partial') }}',
                    data: {
                        tgl_filter: tgl_filter,
                        item_so: item_so,
                        data_partial: data_partial
                    },
                    success: function(response) {
                        {
                            swal.close();
                            Swal.fire({
                                title: response.message,
                                icon: "success",
                                showConfirmButton: true,
                                allowOutsideClick: false
                            }).then(async (result) => {
                                window.location.reload()
                            });

                        }
                    },
                });
            }


        }

        function replacesaldo(){
            let tgl_filter = document.getElementById("tgl_filter").value;
            let item_so = document.getElementById("item_so").value;
            let no_dok_cs = document.getElementById("no_dok_cs").value;

            Swal.fire({
                title: 'Please Wait...',
                html: 'Replace Saldo...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "get",
                url: '{{ route('replace-saldo-stokopname') }}',
                data: {
                    tgl_filter: tgl_filter,
                    item_so: item_so,
                    no_dok_cs: no_dok_cs
                },
                success: function(response) {
                    {
                        swal.close();
                        Swal.fire({
                            title: response.message,
                            icon: "success",
                            showConfirmButton: true,
                            allowOutsideClick: false
                        }).then(async (result) => {
                            window.location.reload()
                        });

                    }
                },
            });

        }
    </script>
<!--
        <script type="text/javascript">
        function copysaldo(data) {
            let tgl_filter = document.getElementById("tgl_filter").value;
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("copy-saldo-stokopname") }}',
                type: 'get',
                data: {
                    tgl_filter: tgl_filter,
                },
                success: function (res) {
                    alert(res);
                }
            });
        }
    </script> -->

    <script type="text/javascript">

        function carigrdok() {
        // Declare variables
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("cari_grdok");
        filter = input.value.toUpperCase();
        table = document.getElementById("datatable");
        tr = table.getElementsByTagName("tr");

        // Loop through all table rows, and hide those who don't match the search query
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[0]; //kolom ke berapa
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

<script type="text/javascript">
    function printbarcode(id) {

        $.ajax({
            url: '{{ route('print-barcode-inmaterial') }}/'+id,
            type: 'post',
            processData: false,
            contentType: false,
            xhrFields:
            {
                responseType: 'blob'
            },
            success: function(res) {
                if (res) {
                    console.log(res);

                    var blob = new Blob([res], {type: 'application/pdf'});
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = id+".pdf";
                    link.click();
                }
            }
        });
    }

    function printpdf(id) {

        $.ajax({
            url: '{{ route('print-pdf-inmaterial') }}/'+id,
            type: 'post',
            processData: false,
            contentType: false,
            xhrFields:
            {
                responseType: 'blob'
            },
            success: function(res) {
                if (res) {
                    console.log(res);

                    var blob = new Blob([res], {type: 'application/pdf'});
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = id+".pdf";
                    link.click();
                }
            }
        });
    }
</script>
@endsection
