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
<form action="{{ route('store-update-ceisa') }}" method="post" id="store-update-ceisa" onsubmit="submitForm(this, event)">
    @csrf
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                Data Header
            </h5>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
          </div>
      </div>
      <div class="card-body">
        <div class="form-group row">
            <div class="row">
                <div class="col-4 col-md-3">
                    <div class="mb-1">
                        <div class="form-group">
                            <label><small>No Dokumen</small></label>
                            @foreach ($kode_gr as $kodegr)
                            <input type="text" class="form-control " id="txt_no_dok" name="txt_no_dok" value="{{ $kodegr->kode }}" readonly>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-4 col-md-2">
                    <div class="mb-1">
                        <div class="form-group">
                            <label><small>Tgl Update</small></label>
                            <input type="date" class="form-control form-control" id="txt_tgl_update" name="txt_tgl_update"
                            value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>


                <div class="col-4 col-md-3">
                    <div class="mb-1">
                        <div class="form-group">
                            <label><small>Jenis Dok</small></label>
                            <select class="form-control select2supp" id="jenis_dok" name="jenis_dok" style="width: 100%;">
                                <option selected="selected" value="" disabled>ALL</option>
                                @foreach ($jenisdok as $jdok)
                                <option value="{{ $jdok->nama_pilihan }}">
                                    {{ $jdok->nama_pilihan }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-4 col-md-4">
                </div>

                <div class="col-4 col-md-2">
                    <div class="mb-1">
                        <div class="form-group">
                            <label><small>Dari: </small></label>
                            <input type="date" class="form-control form-control" id="tgl_dari" name="tgl_dari"
                            value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <div class="col-4 col-md-2">
                    <div class="mb-1">
                        <div class="form-group">
                            <label><small>Sampai: </small></label>
                            <input type="date" class="form-control form-control" id="tgl_sampai" name="tgl_sampai"
                            value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <div class="col-4 col-md-2">
                    <div class="mb-1">
                        <label class="invisible d-block"><small>Cari</small></label>
                        <button type="button" class="btn btn-primary w-50" onclick="loadData()">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>

                <div class="col-12 col-md-12">
                    <div class="form-group row">
                        <!-- <div class="d-flex justify-content-between">
                            <div class="ml-auto">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text"  id="cari_item" name="cari_item" autocomplete="off" placeholder="Search Item..." onkeyup="cariitem()">
                        </div> -->
                        <div class="table-responsive"style="max-height: 300px">
                            <table id="datatable" class="table table-bordered table-head-fixed table-striped 100 text-nowrap" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-center">check</th>
                                        <th class="text-center">Jenis Dokumen</th>
                                        <th class="text-center">No Aju</th>
                                        <th class="text-center">Tanggal Aju</th>
                                        <th class="text-center">No Daftar</th>
                                        <th class="text-center">Tanggal Daftar</th>
                                        <th class="text-center">Update Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody id="data">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mb-1">
                        <div class="form-group">
                            <button class="btn btn-sb float-end mt-2 ml-2"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
                            <a href="{{ route('update-data-ceisa') }}" class="btn btn-danger float-end mt-2">
                                <i class="fas fa-arrow-circle-left"></i> Kembali</a>
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

    //     $(document).ready(function () {
    //         $('#datatable').DataTable({
    //     searching: false, // nonaktifkan pencarian bawaan kalau pakai custom
    //     paging: true,
    //     ordering: true,
    //     autoWidth: false
    // });
    //     });

        function loadData() {
    const tgl_dari = $('#tgl_dari').val();
    const tgl_sampai = $('#tgl_sampai').val();
    const jenis_dok = $('#jenis_dok').val();

    $.ajax({
        url: '{{ route("get-data-ceisa") }}',
        method: 'GET',
        data: {
            jenis_dok: jenis_dok,
            tgl_dari: tgl_dari,
            tgl_sampai: tgl_sampai
        },
        success: function (response) {
            let html = '';
            response.forEach((item, index) => {
                html += `
                <tr>
                    <td class="text-center">
                        <input type="checkbox" name="rows[${index}][checked]" value="1" onchange="toggleKeterangan(this)">
                    </td>
                    <td class="text-center">${item.jenis_dok}</td>
                    <td class="text-center">${item.no_aju}</td>
                    <td class="text-center">${item.tanggal_aju}</td>
                    <td class="text-center">${item.nomor_daftar}</td>
                    <td class="text-center">${item.tanggal_daftar}</td>
                    <td class="text-center">
                        <input type="text" name="rows[${index}][keterangan]" class="form-control" disabled>
                        <input type="hidden" name="rows[${index}][no_aju]" value="${item.no_aju}">
                        <input type="hidden" name="rows[${index}][tanggal_aju]" value="${item.tanggal_aju}">
                        <input type="hidden" name="rows[${index}][no_daftar]" value="${item.nomor_daftar}">
                        <input type="hidden" name="rows[${index}][tanggal_daftar]" value="${item.tanggal_daftar}">
                    </td>
                </tr>`;
            });

            $('#data').html(html);

            // Pastikan hanya inisialisasi DataTable jika belum ada
            if (!$.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable({
                    searching: true,
                    paging: false,
                    ordering: true,
                    autoWidth: false
                });
            }
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            alert('Gagal memuat data.');
        }
    });
}

$(document).ready(function () {
    loadData();
});

        function toggleKeterangan(checkbox) {
            const row = checkbox.closest('tr');
            const input = row.querySelector('input[name*="[keterangan]"]');
            if (checkbox.checked) {
                input.removeAttribute('disabled');
            } else {
                input.setAttribute('disabled', 'disabled');
        input.value = ''; // Kosongkan jika tidak diceklis
    }
}




function submitForm(e, evt) {
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
                            title: 'Data Ceisa berhasil diupdate',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.href = '{{ route("update-data-ceisa") }}';
                        });

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
            td = tr[i].getElementsByTagName("td")[12]; //kolom ke berapa
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
