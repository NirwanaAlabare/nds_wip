@extends('layouts.index')

<style>
    table.dataTable tbody tr.tr-closed {
        background-color: #f0f0f0 !important;
    }
    .tr-closed {
        background-color: #f0f0f0 !important; /* lebih gelap dari #f5f5f5 */
        color: #888 !important;
    }

    .tr-closed input,
    .tr-closed select,
    .tr-closed textarea,
    .tr-closed button {
        pointer-events: none;
        background-color: #eaeaea !important;
        color: #aaa !important;
        border-color: #ccc !important;
    }

</style>


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
<form action="{{ route('save-maintainbpb') }}" method="post" id="save-transferbpb" onsubmit="submitForm(this, event)">
    @csrf
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                FORM REVERSE BPB
            </h5>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
          </div>
      </div>
      <div class="card-body">
        <div class="form-group row">
            <div class="row">
                <div class="col-3 col-md-3">
                    <div class="mb-1">
                        <div class="form-group">
                            <label><small>No Reverse</small></label>
                            @foreach ($kode_gr as $kodegr)
                            <input type="text" class="form-control " id="txt_no_trf" name="txt_no_trf" value="{{ $kodegr->kode }}" readonly>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-2 col-md-2">
                    <div class="mb-1">
                        <div class="form-group">
                            <label><small>Tgl Reverse</small></label>
                            <input type="date" class="form-control form-control" id="txt_tgl_trf" name="txt_tgl_trf"
                            value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>


                <div class="col-5 col-md-5">
                    <div class="mb-1">
                        <div class="form-group">
                            <label><small>Supplier</small></label>
                            <select class="form-control  select2supp" id="nama_supp" name="nama_supp" style="width: 100%;font-size: 14px;">
                                <option selected="selected" value="ALL">ALL</option>
                                @foreach ($nama_supp as $namasupp)
                                <option value="{{ $namasupp->supplier }}">
                                    {{ $namasupp->supplier }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-2 col-md-2">
                </div>

                <div class="col-5 col-md-5">
                    <div class="mb-1">
                        <div class="form-group">
                            <label><small>Keterangan</small></label>
                            <textarea class="form-control" id="txt_keterangan" name="txt_keterangan" rows="3" placeholder="Isi Description..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-2 col-md-2">
                    <div class="mb-1">
                        <div class="form-group">
                            <label><small>Search From:</small></label>
                            <input type="date" class="form-control form-control" id="txt_tgl_awal" name="txt_tgl_awal"
                            value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <div class="col-2 col-md-2">
                    <div class="mb-1">
                        <div class="form-group">
                            <label><small>Search To:</small></label>
                            <input type="date" class="form-control form-control" id="txt_tgl_akhir" name="txt_tgl_akhir"
                            value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mt-3" style="padding-top: 0.5rem;">
                    <input type="button" class="btn btn-primary " value="search" onclick="getlistdata()">
                </div>
                <input type="hidden" id="jumlah_data" name="jumlah_data">

                <div class="col-12 col-md-12">
                    <div class="form-group row">
        <!-- <div class="d-flex justify-content-between">
            <div class="ml-auto">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
                <input type="text"  id="cari_item" name="cari_item" autocomplete="off" placeholder="Search Item..." onkeyup="cariitem()">
            </div> -->
            <!--     <div class="table-responsive"style="max-height: 300px"> -->            
                <table id="datatable" class="table table-bordered table-head-fixed w-100 text-nowrap" width="100%">
                    <thead>
                        <tr>
                            <th class="text-center" style="font-size: 0.6rem;width: 30px;">Check</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">No BPB</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Tgl BPB</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">No PO</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Supplier</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">TOP</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Payment Terms</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Descriptions</th>
                            <th class="text-center" style="display: none;"></th>
                            <th class="text-center" style="display: none;"></th>
                            <th class="text-center" style="display: none;"></th>
                            <th class="text-center" style="display: none;"></th>
                            <th class="text-center" style="display: none;"></th>
                            <th class="text-center" style="display: none;"></th>
                            <th class="text-center" style="display: none;"></th>
                            <th class="text-center" style="display: none;"></th>
                            <th class="text-center" style="display: none;"></th>
                            <th class="text-center" style="display: none;"></th>
                            <th class="text-center" style="display: none;"></th>
                            <th class="text-center" style="display: none;"></th>
                            <th class="text-center" style="display: none;"></th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Status</th>
                            <!-- <th class="text-center" style="display: none;"></th> -->
                        </tr>
                    </thead>
                    <tbody id="dataroll">
                    </tbody>
                </table>
<!--         </div>
-->    </div>
<div class="mb-1">
    <div class="form-group">
        <button class="btn btn-sb float-end mt-2 ml-2"><i class="fa-solid fa-floppy-disk"></i> Save</button>
        <a href="{{ route('maintain-bpb') }}" class="btn btn-danger float-end mt-2">
            <i class="fas fa-arrow-circle-left"></i> Back</a>
        </div>
    </div>
</div>



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
<!-- Page specific script -->
<script>

    $(document).on('select2:open', () => {
        document.querySelector('.select2-search__field').focus();
    });

        //Initialize Select2 Elements
        $('.select2').select2()

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        $('.select2roll').select2({
            theme: 'bootstrap4'
        })

        $('.select2supp').select2({
            theme: 'bootstrap4'
        })

        $("#color").prop("disabled", true);
        $("#panel").prop("disabled", true);
        $('#p_unit').val("yard").trigger('change');

        //Reset Form
        if (document.getElementById('store-mutlokasi')) {
            document.getElementById('store-mutlokasi').reset();
        }

        async function getlistdata() {
            return datatable.ajax.reload(() => {
                document.getElementById('jumlah_data').value = datatable.data().count();
            });
        }



        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollX: true,
            scrollY: 350,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('create-maintain-bpb') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.nama_supp = $('#nama_supp').val();
                    d.tgl_awal = $('#txt_tgl_awal').val();
                    d.tgl_akhir = $('#txt_tgl_akhir').val();
                },
            },
            columns: [
        { data: 'id' },              // 0: checkbox
        { data: 'bpbno_int' },       // 1
        { data: 'bpbdate' },         // 2
        { data: 'pono' },            // 3
        { data: 'Supplier' },        // 4
        { data: 'jml_pterms' },      // 5
        { data: 'kode_pterms' },     // 6
        { data: 'id' },              // 7: keterangan
        { data: 'bpbno_int' },       // 8
        { data: 'pono' },            // 9
        { data: 'bpbdate' },         // 10
        { data: 'Supplier' },        // 11
        { data: 'jml_pterms' },      // 12
        { data: 'kode_pterms' },     // 13
        { data: 'curr' },            // 14
        { data: 'total' },           // 15
        { data: 'confirm_by' },      // 16
        { data: 'confirm_date' },    // 17
        { data: 'dateinput' },       // 18
        { data: 'qty' },             // 19
        { data: 'price' },           // 20
        { data: 'status_closing' }   // 21
        ],
        columnDefs: [
        {
            targets: [3, 5, 6],
            render: (data) => data ? data : "-"
        },
        {
            targets: [0],
            className: "center",
            render: (data, type, row, meta) => {
                const disabled = row.status_closing === 'Open' ? '' : 'disabled';
                return `<input type="checkbox" id="chek_id${meta.row}" name="chek_id[${meta.row}]" class="flat" value="1" onchange="enableinput()" ${disabled}>`;
            }
        },
        {
            targets: [7],
            render: (data, type, row, meta) => {
                const disabled = row.status_closing === 'Open' ? '' : 'disabled';
                return '<input class="form-control form-control-sm" style="width:300px;text-align:left;" type="text" id="keterangan' + meta.row + '" name="keterangan['+meta.row+']" value="" autocomplete="off" / disabled>';
            }
        },
        {
            targets: [8],
            className: "d-none",
            render: (data, type, row, meta) => `<input type="text" id="bpbno_int${meta.row}" name="bpbno_int[${meta.row}]" value="${data}" readonly style="width:80px;text-align:center;" />`
        },
        {
            targets: [9],
            className: "d-none",
            render: (data, type, row, meta) => `<input type="text" id="pono${meta.row}" name="pono[${meta.row}]" value="${data}" readonly style="width:80px;text-align:center;" />`
        },
        {
            targets: [10],
            className: "d-none",
            render: (data, type, row, meta) => `<input type="text" id="bpbdate${meta.row}" name="bpbdate[${meta.row}]" value="${data}" readonly style="width:80px;text-align:center;" />`
        },
        {
            targets: [11],
            className: "d-none",
            render: (data, type, row, meta) => `<input type="text" id="Supplier${meta.row}" name="Supplier[${meta.row}]" value="${data}" readonly style="width:80px;text-align:center;" />`
        },
        {
            targets: [12],
            className: "d-none",
            render: (data, type, row, meta) => `<input type="text" id="jml_pterms${meta.row}" name="jml_pterms[${meta.row}]" value="${data}" readonly style="width:80px;text-align:center;" />`
        },
        {
            targets: [13],
            className: "d-none",
            render: (data, type, row, meta) => `<input type="text" id="kode_pterms${meta.row}" name="kode_pterms[${meta.row}]" value="${data}" readonly style="width:80px;text-align:center;" />`
        },
        {
            targets: [14],
            className: "d-none",
            render: (data, type, row, meta) => `<input type="text" id="curr${meta.row}" name="curr[${meta.row}]" value="${data}" readonly style="width:80px;text-align:center;" />`
        },
        {
            targets: [15],
            className: "d-none",
            render: (data, type, row, meta) => `<input type="text" id="total${meta.row}" name="total[${meta.row}]" value="${data}" readonly style="width:80px;text-align:center;" />`
        },
        {
            targets: [16],
            className: "d-none",
            render: (data, type, row, meta) => `<input type="text" id="confirm_by${meta.row}" name="confirm_by[${meta.row}]" value="${data}" readonly style="width:80px;text-align:center;" />`
        },
        {
            targets: [17],
            className: "d-none",
            render: (data, type, row, meta) => `<input type="text" id="confirm_date${meta.row}" name="confirm_date[${meta.row}]" value="${data}" readonly style="width:80px;text-align:center;" />`
        },
        {
            targets: [18],
            className: "d-none",
            render: (data, type, row, meta) => `<input type="text" id="dateinput_bpb${meta.row}" name="dateinput_bpb[${meta.row}]" value="${data}" readonly style="width:80px;text-align:center;" />`
        },
        {
            targets: [19],
            className: "d-none",
            render: (data, type, row, meta) => `<input type="text" id="txt_qty${meta.row}" name="txt_qty[${meta.row}]" value="${data}" readonly style="width:80px;text-align:center;" />`
        },
        {
            targets: [20],
            className: "d-none",
            render: (data, type, row, meta) => `<input type="text" id="txt_price${meta.row}" name="txt_price[${meta.row}]" value="${data}" readonly style="width:80px;text-align:center;" />`
        }
        ],
        createdRow: function(row, data, dataIndex) {
            if (data.status_closing === 'Closed') {
                $(row).addClass('tr-closed');
            }
        }
    });



        // $(document).ready(function() {
        // $('#datatable').DataTable({ searching: false, paging: false, info: false, ordering: false});

        // } );


        function tambahqty($val){
            var table = document.getElementById("datatable");
            var qty = 0;
            var jml_qty = 0;

            for (var i = 1; i < (table.rows.length); i++) {
                qty = document.getElementById("datatable").rows[i].cells[9].children[0].value || 0;
                jml_qty += parseFloat(qty) ;
            }

            $('#jumlah_qty').val(jml_qty);

        }

        // enableinput
        function enableinput(){
            var table = document.getElementById("datatable");
            for (let i = 0; i < (table.rows.length); i++) {
                var cek =  document.getElementById("chek_id"+i);

                if (cek.checked == true){
                    $("#keterangan"+i).prop("disabled", false);
                }else if(cek.checked == false){
                    $("#keterangan"+i).val('');
                    $("#keterangan"+i).prop("disabled", true);
                }
            }
        }


        function submitLokasiForm(e, evt) {
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

                        e.reset();

                        // $('#cbows').val("").trigger("change");
                        // $("#cbomarker").prop("disabled", true);

                        Swal.fire({
                            icon: 'success',
                            title: 'Data Spreading berhasil disimpan',
                            html: "No. Form Cut : <br>" + res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        })

                        datatable.ajax.reload();
                    }
                },

            });
        }

        function cariitem() {
        // Declare variables
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("cari_item");
        filter = input.value.toUpperCase();
        table = document.getElementById("datatable");
        tr = table.getElementsByTagName("tr");

        // Loop through all table rows, and hide those who don't match the search query
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[1]; //kolom ke berapa
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
