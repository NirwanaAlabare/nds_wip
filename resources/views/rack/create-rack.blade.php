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
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0">Tambahkan Rak</h5>
                <a href="{{ route('rack') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-reply"></i> Kembali ke Master Rak
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('store-rack') }}" method="post" onsubmit="submitRackForm(this, event)">
                <div class="row">
                    <div class="col-9">
                        <div class="mb-3">
                            <label>Nama Rak</label>
                            <input type="text" class="form-control" name="nama_rak" id="nama_rak" onchange="buildRackTable()" onkeyup="buildRackTable()">
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="mb-3">
                            <label>Jumlah Ruang</label>
                            <input type="number" class="form-control" name="jumlah_ruang" id="jumlah_ruang" onchange="buildRackTable()" onkeyup="buildRackTable()">
                        </div>
                    </div>
                </div>
                <table class="table table-bordered" id="rack-table">
                    <tbody>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-sb btn-block">Simpan</button>
            </form>
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
        $(document).ready(() => {
            document.getElementById('nama_rak').value = "";
            document.getElementById('jumlah_ruang').value = 0;
        });

        function clearRackTable() {
            let rackTable = document.getElementById('rack-table');
            let rackTableTbody = rackTable.getElementsByTagName("tbody")[0];

            rackTableTbody.innerHTML = "";
        }

        function buildRackTable() {
            let rackName = document.getElementById('nama_rak').value;
            let rackNumber = document.getElementById('jumlah_ruang').value;
            let rackTable = document.getElementById('rack-table');
            let rackTableTbody = rackTable.getElementsByTagName("tbody")[0];

            rackTableTbody.innerHTML = "";

            if (rackNumber > 0) {
                let tr1 = document.createElement('tr');
                let tr2 = document.createElement('tr');

                for (let i = 0; i < rackNumber; i++) {
                    let th1 = document.createElement('th');
                    let th2 = document.createElement('th');

                    th1.innerHTML = rackName+'.'+(i+1);
                    th2.innerHTML = "&nbsp;";

                    tr1.appendChild(th1);
                    tr2.appendChild(th2);

                    rackTableTbody.appendChild(tr1);
                    rackTableTbody.appendChild(tr2);
                }
            }
        }

        // Submit Rak Form
        function submitRackForm(e, evt) {
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

                        // Reset This Form
                        e.reset();

                        // Success Alert
                        Swal.fire({
                            icon: 'success',
                            title: 'Data Master Rak berhasil disimpan',
                            text: res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        })

                        clearRackTable();
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
                    if (Object.keys(res.additional).length > 0 ) {
                        for (let key in res.additional) {
                            if (document.getElementById(key)) {
                                document.getElementById(key).classList.add('is-invalid');

                                if (res.additional[key].hasOwnProperty('message')) {
                                    document.getElementById(key+'_error').classList.remove('d-none');
                                    document.getElementById(key+'_error').innerHTML = res.additional[key]['message'];
                                }

                                if (res.additional[key].hasOwnProperty('value')) {
                                    document.getElementById(key).value = res.additional[key]['value'];
                                }

                                modified.push(
                                    [key, '.classList', '.remove(', "'is-invalid')"],
                                    [key+'_error', '.classList', '.add(', "'d-none')"],
                                    [key+'_error', '.innerHTML = ', "''"],
                                )
                            }
                        }
                    }
                }, error: function (jqXHR) {
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
