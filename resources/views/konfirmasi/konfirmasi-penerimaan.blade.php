@extends('layouts.index', ['containerFluid' => true])

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
    <style type="text/css">
        .marginnya{
            margin-left: 350px;
            margin-right: 350px;
            margin-top: 10px;
        }
    </style>
    @endsection

    @section('content')
    <div class="marginnya">
    <form action="{{ route('approve-material-all') }}" method="post" onsubmit="submitappForm(this, event)">
        @method('GET')
        <div class="card card-sb">
            <div class="card-header d-flex align-items-center gap-2">
                <h5 class="card-title fw-bold mb-0">Konfirmasi Penerimaan Bahan Baku</h5>
                <span class="badge badge-danger d-inline-flex align-items-center gap-1" style="font-size: 0.75rem; padding: 0.35em 0.55em; border-radius: 999px;" title="Dokumen belum di-approve">
                    <i class="fas fa-bell"></i> <span id="jml_pending">0</span>
                </span>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <button type="submit" class="btn btn-success btn-sm toastsDefaultDanger"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Approve</button>
                </div>
                <div>
                    <table id="datatable" class="table table-bordered table-striped table-head-fixed table w-100" style="table-layout: fixed;">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 12%;">No BPB</th>
                                <th class="text-center" style="width: 7%;">Tgl BPB</th>
                                <th class="text-center" style="width: 6%;">Tipe BPB</th>
                                <th class="text-center" style="width: 17%;">No PO</th>
                                <th class="text-center" style="width: 21%;">Supplier</th>
                                <th class="text-center" style="width: 10%;">No Invoice</th>
                                <th class="text-center" style="width: 6%;">Qty</th>
                                <th class="text-center" style="width: 5%;">Satuan</th>
                                <th class="text-center" style="width: 5%;">Lokasi</th>
                                <th class="text-center" style="width: 5%;"><input type="checkbox" id="check_all" title="Check semua data yang lokasinya sudah lengkap (hijau)"></th>
                                <th style="display:none;">Check</th>
                                <th class="text-center" style="width: 6%;">Detail</th>
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

    </script>

    <script type="text/javascript">
        $('.select2pchtype').select2({
            theme: 'bootstrap4'
        })
    </script>
    <script type="text/javascript">
        // checkedApprove menyimpan status checklist per No BPB, independen dari
        // halaman/paging atau filter DataTables yang sedang aktif saat ini.
        $(document).on('change', 'input[type="checkbox"][name^="chek_id"]', function() {
            let idBpb = $(this).data('idbpb');
            if (!idBpb) return;
            if (this.checked) {
                checkedApprove[idBpb] = true;
            } else {
                delete checkedApprove[idBpb];
            }
        });

        // Check all hanya menandai baris yang lokasinya sudah lengkap (qty_balance == 0 / ikon hijau).
        // Dengan deferRender:false (default), DataTables sudah membangun node DOM semua baris
        // sejak awal (bukan cuma halaman yang sedang tampil), jadi pindah halaman saja tidak akan
        // memicu render ulang. invalidate() memaksa semua baris (semua halaman) dirender ulang
        // supaya checkbox-nya ikut ter-update, bukan cuma yang sedang tampil.
        $(document).on('change', '#check_all', function() {
            let checked = this.checked;

            datatable.rows().every(function() {
                let row = this.data();
                if (row.qty_balance == 0) {
                    if (checked) {
                        checkedApprove[row.no_dok] = true;
                    } else {
                        delete checkedApprove[row.no_dok];
                    }
                }
            });

            datatable.rows().invalidate().draw(false);
        });

        function collectCheckedIds() {
            return Object.keys(checkedApprove);
        }

        function doApprove(idBpbList) {
            if (!idBpbList.length) return;

            let payload = {};
            idBpbList.forEach((id, idx) => {
                payload['id_bpb[' + idx + ']'] = id;
                payload['chek_id[' + idx + ']'] = '1';
            });

            Swal.fire({
                title: 'Memproses...',
                html: 'Meng-approve ' + idBpbList.length + ' data, mohon tunggu.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("approve-material-all") }}',
                type: 'get',
                data: payload,
                success: function(res) {
                    showApproveSummary(res, idBpbList.length);
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat menghubungi server.'
                    });
                }
            });
        }

        function showApproveSummary(res, totalChecked) {
            let approvedCount = res.approved_count ?? (res.approved ? res.approved.length : 0);
            let failed = res.failed || [];

            (res.approved || []).forEach(id => delete checkedApprove[id]);

            if (failed.length === 0) {
                Swal.fire({
                    icon: 'success',
                    title: 'Semua Berhasil Diapprove',
                    text: approvedCount + ' dari ' + totalChecked + ' data berhasil diapprove.',
                    confirmButtonText: 'Oke',
                }).then(() => {
                    dataTableReload();
                });
            } else {
                let listHtml = failed.map(f => '<li>' + f.id_bpb + '</li>').join('');
                Swal.fire({
                    icon: 'warning',
                    title: 'Sebagian Data Gagal Diapprove',
                    html: 'Berhasil: <b>' + approvedCount + '</b> dari <b>' + totalChecked + '</b> data.<br>' +
                        'Gagal: <b>' + failed.length + '</b><ul style="text-align:left;">' + listHtml + '</ul>',
                    showCancelButton: true,
                    confirmButtonText: 'Coba Lagi',
                    cancelButtonText: 'Tutup',
                }).then((result) => {
                    dataTableReload();
                    if (result.isConfirmed) {
                        let retryIds = failed.map(f => f.id_bpb);
                        doApprove(retryIds);
                    }
                });
            }
        }

        function submitappForm(e, evt) {
            evt.preventDefault();

            clearModified();

            let ids = collectCheckedIds();
            if (ids.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Pilih minimal 1 data untuk di-approve.'
                });
                return;
            }

            Swal.fire({
                icon: 'question',
                title: 'Konfirmasi Approve',
                text: 'Approve ' + ids.length + ' dokumen?',
                showCancelButton: true,
                confirmButtonText: 'Oke',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    doApprove(ids);
                }
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
        let checkedApprove = {};

        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: false,
            serverSide: false,
            paging: true,
            pageLength: 10,
            searching: true,
            autoWidth: false,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('konfirmasi-pemasukan') }}',
                dataType: 'json',
                dataSrc: 'data',
            },
            drawCallback: function() {
                $('#jml_pending').text(this.api().rows().count());
            },
            columns: [{
                data: 'no_dok'
            },
            {
                data: 'tgl_dok'
            },
            {
                data: 'type_dok'
            },
            {
                data: 'no_po'
            },
            {
                data: 'supplier'
            },
            {
                data: 'no_invoice'
            },
            {
                data: 'qty'
            },
            {
                data: 'unit'
            },
            {
                data: 'id'
            },
            {
                data: 'id'
            },
            {
                data: 'no_dok'
            },
            {
                data: 'no_dok'
            }

            ],
            columnDefs: [{
                targets: [4],
                render: (data, type, row, meta) => data ? data.toUpperCase() : "-"
            },
            {
                targets: [2],
                render: (data, type, row, meta) => data ? data : "-"
            },
            {
                targets: [3],
                render: (data, type, row, meta) => data ? data : "-"
            },
            {
                targets: [5],
                render: (data, type, row, meta) => data ? data : "-"
            },
            {
                targets: [7],
                render: (data, type, row, meta) => data ? data : "-"
            },
            {
                targets: [8],
                render: (data, type, row, meta) => {
                   if (row.qty_balance == 0) {
                    return `<div class='d-flex gap-1 justify-content-center'>
                    <a ><i class="fas fa-check-circle fa-lg" style='color:green;'></i></a>
                    </div>`;
                }else{
                    return `<div class='d-flex gap-1 justify-content-center'>
                    <a ><i class="fas fa-times-circle fa-lg" style='color:red;'></i></a>
                    </div>`;
                }
            }
        },
        {
            targets: [9],
            render: (data, type, row, meta) => {
                // if (row.no_po != '') {
                    if (row.qty_balance == 0) {
                        let isChecked = checkedApprove[row.no_dok] ? 'checked' : '';
                        return '<div class="d-flex gap-1 justify-content-center" style="padding-top:5px;"><input type="checkbox" id="chek_id' + meta.row +
                        '" name="chek_id[' + meta.row + ']" class="flat" data-idbpb="' + row.no_dok + '" value="1" ' + isChecked + '></div>';
                    }else{
                        return '';
                    }

                // }else{
                //     return '';
                // }
            }
        },
        {
            targets: [10],
            className: "d-none",
            render: (data, type, row, meta) => {
                return '<div class="d-flex gap-1 justify-content-center"><input type="text" id="id_bpb' + meta.row +
                '" name="id_bpb[' + meta.row + ']" class="flat" value="' + data + '" ></td></div>';
            }
        },
        {
            targets: [11],
            render: (data, type, row, meta) => {
                return `<div class="d-flex gap-1 justify-content-center"><a ><i class="fa-solid fa-circle-info fa-lg" style="color:DarkCyan;" onclick='showdata("` + data + `")'></i></a></div>`;
            }
        }
        ]
    });

async function dataTableReload() {
    return datatable.ajax.reload();
}
</script>
<script type="text/javascript">
    function approve_inmaterial($nodok){
        // alert($id);
        let nodok  = $nodok;

        $('#txt_nodok').val(nodok);
        $('#modal-appv-material').modal('show');
    }


    function nonactive_lokasi($id,$status,$kode_lok){
        // alert($id);
        let id  = $id;
        let status  = $status;
        let kode  = $kode_lok;
        let idnya  = $id;

        $('#txt_kode_lok').val(kode);
        $('#id_lok').val(idnya);
        $('#status_lok').val(status);
        $('#modal-active-lokasi').modal('show');
    }


    function editdata($id,$kapasitas,$inisial_lok,$baris,$level,$nomor,$area,$unit,$u_roll,$u_bundle,$u_box,$u_pack){
        // alert($id);
        $("#ROLL_edit").prop("checked", false);
        $("#BUNDLE_edit").prop("checked", false);
        $("#BOX_edit").prop("checked", false);
        $("#PACK_edit").prop("checked", false);
        let kapasitas  = $kapasitas;
        let inisial_lok  = $inisial_lok;
        let idnya  = $id;
        let baris  = $baris;
        let level  = $level;
        let nomor  = $nomor;
        let area  = $area;
        let unit  = $unit;
        let u_roll  = $u_roll;
        let u_bundle  = $u_bundle;
        let u_box  = $u_box;
        let u_pack  = $u_pack;

        console.log(u_roll);

        if (u_roll == 'ROLL') {
            $("#ROLL_edit").prop("checked", true);
        }

        if (u_bundle == 'BUNDLE') {
            $("#BUNDLE_edit").prop("checked", true);
        }

        if (u_box == 'BOX') {
            $("#BOX_edit").prop("checked", true);
        }

        if (u_pack == 'PACK') {
            $("#PACK_edit").prop("checked", true);
        }

        $('#txt_id').val(idnya);
        $('#txt_inisial').val(inisial_lok);
        $('#txt_capacity').val(kapasitas);
        $('#txt_baris').val(baris);
        $('#txt_level').val(level);
        $('#txt_num').val(nomor);
        $('#txt_area').val(area);
    // document.getElementById('txt_area').value=area;
    // document.getElementById('txt_area').selected=true;
    $('#modal-edit-lokasi').modal('show');
}

function tambahdata(){
    $('#modal-tambah-lokasi').modal('show');
}
</script>

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
