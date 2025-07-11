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
            <h5 class="card-title fw-bold mb-0"><i class="fa-regular fa-square-plus"></i> Master Secondary</h5>
        </div>
        <div class="card-body">
            <button type="button" class="btn btn-success btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#createMasterSecondaryModal">
                <i class="fas fa-plus"></i>
                Baru
            </button>
            <div class="table-responsive">
                <table id="datatable-master-secondary" class="table table-bordered table w-100">
                    <thead>
                        <tr>
                            <th class="align-bottom">Action</th>
                            <th>Kode Secondary</th>
                            <th>Jenis Secondary</th>
                            <th>Proses Secondary</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createMasterSecondaryModal" tabindex="-1" aria-labelledby="createMasterSecondaryLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('store-master-secondary') }}" method="post" onsubmit="submitMasterSecondaryForm(this, event)" id="storemastersecondaryform">
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5" id="createMasterPartLabel"><i class="fa fa-plus-square"></i> Tambah Data Master Tujuan</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tujuan</label>
                            <select class="form-control select2bs4" id="cbotuj" name="cbotuj" style="width: 100%;" onchange="updateCboJns()">
                                <option selected="selected" value="">Pilih Tujuan</option>
                                @foreach ($data_tujuan as $datatujuan)
                                    <option value="{{ $datatujuan->isi }}">
                                        {{ $datatujuan->tampil }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" class="form-control" name="cbojns" id="cbojns" value="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Proses</label>
                            <input type="text" class="form-control" name="proses" id="proses" autocomplete="off" value="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editMasterSecondaryModal" tabindex="-1" aria-labelledby="editMasterSecondaryLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('update_master_secondary') }}" method="post" onsubmit="submitForm(this, event)">
                    @method('PUT')
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5" id="editMasterPartLabel"><i class="fa fa-edit"></i> Ubah Data Master Secondary</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Jenis Secondary</label>
                            <select class="form-control select2bs4" id="txttuj" name="txttuj" style="width: 100%;" onchange="updateTxtJns()">
                                <option selected="selected" value="">Pilih Tujuan</option>
                                @foreach ($data_tujuan as $datatujuan)
                                    <option value="{{ $datatujuan->isi }}">
                                        {{ $datatujuan->tampil }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" class="form-control" name="txtjns" id="txtjns" value="">
                            <input type="hidden" class="form-control" name="id_c" id="id_c" value="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Proses</label>
                            <input type="text" class="form-control" name="txtproses" id="txtproses" value="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
                    </div>
                </form>
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
        const modalElement = document.getElementById('createMasterSecondaryModal');

        modalElement.addEventListener('show.bs.modal', function (event) {
            document.getElementById("storemastersecondaryform").reset(); // Reset the form when the modal is opened
        });

        let datatableMasterPart = $("#datatable-master-secondary").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('master-secondary') }}',
            },
            columns: [
                {
                    data: 'id'
                },
                {
                    data: 'kode',
                },
                {
                    data: 'tujuan',
                },
                {
                    data: 'proses'
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    className: "align-middle",
                    render: (data, type, row, meta) => {
                        return `
                                <div class='d-flex gap-1 justify-content-center'>
                                    <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#editMasterSecondaryModal"
                                        onclick="getdetail('` + row.id + `');">
                                        <i class='fa fa-edit'></i>
                                    </a>
                                    <a class='btn btn-danger btn-sm' data='` + JSON.stringify(row) +
                            `' data-url='{{ route('destroy-master-secondary') }}/` + row['id'] + `' onclick='deleteData(this)'>
                                        <i class='fa fa-trash'></i>
                                    </a>
                                </div>
                            `;
                    }
                }
            ],
        });

        function datatableMasterPartReload() {
            datatableMasterPart.ajax.reload()
        }

        function getdetail(id_c) {
            jQuery.ajax({
                url: '{{ route('show_master_secondary') }}',
                method: 'GET',
                data: {
                    id_c: id_c
                },
                dataType: 'json',
                success: async function(response) {
                    $('#txttuj').val(response.id_tujuan).trigger('change'); // Trigger change to update txtjns
                    document.getElementById('txtjns').value = response.tujuan;
                    document.getElementById('txtproses').value = response.proses;
                    document.getElementById('id_c').value = id_c;
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        };

        function updateCboJns() {
            let cbotuj = document.getElementById('cbotuj');
            let cbojns = document.getElementById('cbojns');

            if (cbotuj.value != '') {
                cbojns.value = cbotuj.options[cbotuj.selectedIndex].text;
            } else {
                cbojns.value = '';
            }
        }

        function updateTxtJns() {
            let txttuj = document.getElementById('txttuj');
            let txtjns = document.getElementById('txtjns');

            if (txttuj.value != '') {
                txtjns.value = txttuj.options[txttuj.selectedIndex].text;
            } else {
                txtjns.value = '';
            }
        }

        // Submit Marker Form
        function submitMasterSecondaryForm(e, evt) {
            evt.preventDefault();

            clearModified();

            $.ajax({
                url: e.getAttribute('action'),
                type: e.getAttribute('method'),
                data: new FormData(e),
                processData: false,
                contentType: false,
                success: async function(res) {
                    // Success Response

                    if (res.status == 200) {
                        // When Actually Success :

                        // Hide Modal
                        $('.modal').modal('hide');

                        // Reset This Form
                        e.reset();

                        // Success Alert
                        Swal.fire({
                            icon: 'success',
                            title: 'Data Master Part berhasil disimpan',
                            text: res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        })

                        datatableMasterPartReload();
                    } else {
                        // When Actually Error :

                        // Error Alert
                        iziToast.error({
                            title: 'Error',
                            message: res.message,
                            position: 'topCenter'
                        });
                    }

                    // If There Are Some Additional Error
                    if (Object.keys(res.additional).length > 0) {
                        for (let key in res.additional) {
                            if (document.getElementById(key)) {
                                document.getElementById(key).classList.add('is-invalid');

                                if (res.additional[key].hasOwnProperty('message')) {
                                    document.getElementById(key + '_error').classList.remove('d-none');
                                    document.getElementById(key + '_error').innerHTML = res.additional[key][
                                        'message'
                                    ];
                                }

                                if (res.additional[key].hasOwnProperty('value')) {
                                    document.getElementById(key).value = res.additional[key]['value'];
                                }

                                modified.push(
                                    [key, '.classList', '.remove(', "'is-invalid')"],
                                    [key + '_error', '.classList', '.add(', "'d-none')"],
                                    [key + '_error', '.innerHTML = ', "''"],
                                )
                            }
                        }
                    }
                },
                error: function(jqXHR) {
                    // Error Response

                    let res = jqXHR.responseJSON;
                    let message = '';
                    let i = 0;

                    for (let key in res.errors) {
                        message = res.errors[key];
                        document.getElementById(key).classList.add('is-invalid');
                        modified.push(
                            [key, '.classList', '.remove(', "'is-invalid')"],
                        )

                        if (i == 0) {
                            document.getElementById(key).focus();
                            i++;
                        }
                    };
                }
            });
        }
    </script>
@endsection
