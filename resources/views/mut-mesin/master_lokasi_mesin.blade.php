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
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <form action="{{ route('store_master_lokasi_mesin') }}" method="post" onsubmit="submitForm(this, event)"
            name='form' id='form'>
            @method('POST')
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h3 class="modal-title fs-5">Tambah Master Lokasi</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Lokasi :</label>
                            <input type='text' class='form-control form-control-sm' id="txtlok" name="txtlok"
                                style="text-transform: uppercase" value="" autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal"><i
                                class="fas fa-times-circle"></i> Tutup</button>
                        <button type="submit" class="btn btn-outline-success"><i class="fas fa-check"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-location-arrow"></i> Master Lokasi </h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#exampleModal"
                        onclick="reset()"><i class="fas fa-plus"></i> Baru</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered w-100 text-nowrap">
                    <thead class="bg-sb">
                        <tr style='text-align:left; vertical-align:middle'>
                            <th>Lokasi</th>
                        </tr>
                    </thead>
                </table>
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

    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        function reset() {
            $("#form").trigger("reset");
        }
    </script>
    <script>
        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'All']
            ],
            searching: true,
            ajax: {
                url: '{{ route('master_mesin_lokasi') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                data: 'lokasi'

            }],
            columnDefs: [{
                "className": "dt-left",
                "targets": "_all"
            }, ]
        });

        function hapus(id_qr) {
            $.ajax({
                type: "post",
                url: '{{ route('hapus-data-mesin') }}',
                data: {
                    id_qr: id_qr
                },
                success: async function(res) {
                    iziToast.success({
                        message: 'Data Berhasil Dihapus',
                        position: 'topCenter'
                    });
                    $('#datatable').DataTable().ajax.reload();
                }
            });

        }
    </script>
@endsection
