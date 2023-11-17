@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card">
        <div class="card-header bg-sb text-light">
            <h5 class="card-title">Part</h5>
        </div>
        <div class="card-body">
            <button type="button" class="btn btn-success btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#createMasterPartModal">
                <i class="fas fa-plus"></i>
                Baru
            </button>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Awal</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Akhir</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <button class="btn btn-primary btn-sm" onclick="filterTable()">Tampilkan</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable-master-part" class="table table-bordered table-sm w-100">
                    <thead>
                        <tr>
                            <th>Kode Part</th>
                            <th>Nama Part</th>
                            <th>Bag</th>
                            <th class="align-bottom">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailPartModal" tabindex="-1" aria-labelledby="detailPartLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="detailPartLabel">Detail Part</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Nama Part</label>
                        <input type="text" class="form-control" name="edit_nama_part" id="edit_nama_part" value="">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bag</label>
                        <input type="text" class="form-control" name="edit_bag" id="edit_bag" value="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Simpan</button>
                </div>
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

    <script>
        // let datatableMasterPart = $("#datatable-master-part").DataTable({
        //     ordering: false,
        //     processing: true,
        //     serverSide: true,
        //     ajax: {
        //         url: '{{ route('master-part') }}',
        //     },
        //     columns: [
        //         {
        //             data: 'kode_part',
        //         },
        //         {
        //             data: 'nama_part',
        //         },
        //         {
        //             data: 'bag'
        //         },
        //         {
        //             data: 'id'
        //         },
        //     ],
        //     columnDefs: [
        //         {
        //             targets: [3],
        //             className: "align-middle",
        //             render: (data, type, row, meta) => {
        //                 return `
        //                     <div class='d-flex gap-1 justify-content-center'>
        //                         <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#editMasterPartModal" onclick='editData(` + JSON.stringify(row) + `, "editMasterPartModal", [{"function" : "datatableMasterPartReload()"}]);'>
        //                             <i class='fa fa-edit'></i>
        //                         </a>
        //                         <a class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route('destroy-master-part') }}/`+row['id']+`' onclick='deleteData(this)'>
        //                             <i class='fa fa-trash'></i>
        //                         </a>
        //                     </div>
        //                 `;
        //             }
        //         }
        //     ],
        // });

        // function datatableMasterPartReload() {
        //     datatableMasterPart.ajax.reload()
        // }

        // // Submit Marker Form
        // function submitMasterPartForm(e, evt) {
        //     evt.preventDefault();

        //     clearModified();

        //     $.ajax({
        //         url: e.getAttribute('action'),
        //         type: e.getAttribute('method'),
        //         data: new FormData(e),
        //         processData: false,
        //         contentType: false,
        //         success: async function(res) {
        //             // Success Response

        //             if (res.status == 200) {
        //                 // When Actually Success :

        //                 // Hide Modal
        //                 $('.modal').modal('hide');

        //                 // Reset This Form
        //                 e.reset();

        //                 // Success Alert
        //                 Swal.fire({
        //                     icon: 'success',
        //                     title: 'Data Master Part berhasil disimpan',
        //                     text: res.message,
        //                     showCancelButton: false,
        //                     showConfirmButton: true,
        //                     confirmButtonText: 'Oke',
        //                     timer: 5000,
        //                     timerProgressBar: true
        //                 })

        //                 datatableMasterPartReload();
        //             } else {
        //                 // When Actually Error :

        //                 // Error Alert
        //                 iziToast.error({
        //                     title: 'Error',
        //                     message: res.message,
        //                     position: 'topCenter'
        //                 });
        //             }

        //             // If There Are Some Additional Error
        //             if (Object.keys(res.additional).length > 0 ) {
        //                 for (let key in res.additional) {
        //                     if (document.getElementById(key)) {
        //                         document.getElementById(key).classList.add('is-invalid');

        //                         if (res.additional[key].hasOwnProperty('message')) {
        //                             document.getElementById(key+'_error').classList.remove('d-none');
        //                             document.getElementById(key+'_error').innerHTML = res.additional[key]['message'];
        //                         }

        //                         if (res.additional[key].hasOwnProperty('value')) {
        //                             document.getElementById(key).value = res.additional[key]['value'];
        //                         }

        //                         modified.push(
        //                             [key, '.classList', '.remove(', "'is-invalid')"],
        //                             [key+'_error', '.classList', '.add(', "'d-none')"],
        //                             [key+'_error', '.innerHTML = ', "''"],
        //                         )
        //                     }
        //                 }
        //             }
        //         }, error: function (jqXHR) {
        //             // Error Response

        //             let res = jqXHR.responseJSON;
        //             let message = '';
        //             let i = 0;

        //             for (let key in res.errors) {
        //                 message = res.errors[key];
        //                 document.getElementById(key).classList.add('is-invalid');
        //                 modified.push(
        //                     [key, '.classList', '.remove(', "'is-invalid')"],
        //                 )

        //                 if (i == 0) {
        //                     document.getElementById(key).focus();
        //                     i++;
        //                 }
        //             };
        //         }
        //     });
        // }
    </script>
@endsection
