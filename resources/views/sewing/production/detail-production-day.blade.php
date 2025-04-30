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
    <div class="container-fluid mt-3 pt-3">
        <div class="card">
            <div class="card-header bg-sb text-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Data Produksi Harian</h5>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <input type="date" class="form-control form-control-sm" id="date-detail-produksi-day">
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table w-100" id="detail-produksi-day-table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Tanggal Produksi</th>
                            <th>Tim Produksi</th>
                            <th>Plan Info</th>
                            <th>Production Info</th>
                            <th>Earning Info</th>
                            <th>Data Info</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Update Data Detail Produksi Day --}}
    <div class="modal fade" id="updateDataDetailProduksiDayModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form action="{{ route('dataDetailProduksiDay.updateData') }}" method="POST" onsubmit="submitForm(this, event)">
                    @method('PUT')
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-6">Ubah Data Hari Produksi</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 d-none">
                            <label>ID</label>
                            <input type="hidden" class="form-control" name="edit_id" id="edit_id" readonly>
                            <small class="text-danger d-none" id="edit_id_error"></small>
                        </div>
                        <div class="row gap-1">
                            <div class="col mb-3">
                                <label>Tanggal Produksi</label>
                                <input type="text" class="form-control" name="edit_tgl_produksi" id="edit_tgl_produksi" readonly>
                                <small class="text-danger d-none" id="edit_tgl_produksi_error"></small>
                            </div>
                            <div class="col mb-3">
                                <label>Line</label>
                                <input type="text" class="form-control" name="edit_sewing_line" id="edit_sewing_line" readonly>
                                <small class="text-danger d-none" id="edit_sewing_line_error"></small>
                            </div>
                            <div class="col mb-3">
                                <label>No. WS</label>
                                <input type="text" class="form-control" name="edit_no_ws" id="edit_no_ws" readonly>
                                <small class="text-danger d-none" id="edit_no_ws_error"></small>
                            </div>
                        </div>
                        <div class="row gap-1">
                            <div class="col mb-3">
                                <label>SMV</label>
                                <input type="text" class="form-control" name="edit_smv" id="edit_smv" readonly>
                                <small class="text-danger d-none" id="edit_smv_error"></small>
                            </div>
                            <div class="col mb-3">
                                <label>Man Power</label>
                                <input type="text" class="form-control" name="edit_man_power" id="edit_man_power" readonly>
                                <small class="text-danger d-none" id="edit_man_power_error"></small>
                            </div>
                            <div class="col mb-3">
                                <label>Jam Aktual</label>
                                <input type="text" class="form-control" name="edit_jam_aktual" id="edit_jam_aktual" readonly>
                                <small class="text-danger d-none" id="edit_jam_aktual_error"></small>
                            </div>
                            <div class="col mb-3">
                                <label>Target</label>
                                <input type="text" class="form-control" name="edit_target" id="edit_target" readonly>
                                <small class="text-danger d-none" id="edit_target_error"></small>
                            </div>
                        </div>
                        <div class="row gap-1">
                            <div class="col mb-3">
                                <label>Mins. Avail</label>
                                <input type="text" class="form-control" name="edit_mins_avail" id="edit_mins_avail" readonly>
                                <small class="text-danger d-none" id="edit_mins_avail_error"></small>
                            </div>
                            <div class="col mb-3">
                                <label>Mins. Prod</label>
                                <input type="text" class="form-control" name="edit_mins_prod" id="edit_mins_prod" readonly>
                                <small class="text-danger d-none" id="edit_mins_prod_error"></small>
                            </div>
                            <div class="col mb-3">
                                <label>Output</label>
                                <input type="text" class="form-control" name="edit_output" id="edit_output" readonly>
                                <small class="text-danger d-none" id="edit_output_error"></small>
                            </div>
                            <div class="col mb-3">
                                <label>Efficiency</label>
                                <input type="text" class="form-control" name="edit_efficiency" id="edit_efficiency" readonly>
                                <small class="text-danger d-none" id="edit_efficiency_error"></small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4 mb-3">
                                <label>Chief</label>
                                <select type="text" class="form-select select2" name="edit_chief_enroll_id" id="edit_chief_enroll_id">
                                    <option value="">Pilih chief</option>
                                    @foreach ($chiefs as $chief)
                                        <option value="{{ $chief->id }}">{{ $chief->nik.' - '.$chief->nama }}</option>
                                    @endforeach
                                </select>
                                <small class="text-danger d-none" id="edit_chief_enroll_id_error"></small>
                            </div>
                            <div class="col-4 mb-3">
                                <label>Leader</label>
                                <select type="text" class="form-select select2" name="edit_leader_enroll_id" id="edit_leader_enroll_id">
                                    <option value="">Pilih leader</option>
                                    @foreach ($leaders as $leader)
                                        <option value="{{ $leader->id }}">{{ $leader->nik.' - '.$leader->nama }}</option>
                                    @endforeach
                                </select>
                                <small class="text-danger d-none" id="edit_leader_enroll_id_error"></small>
                            </div>
                            <div class="col-4 mb-3">
                                <label>Administrator</label>
                                <select type="text" class="form-select select2" name="edit_adm_enroll_id" id="edit_adm_enroll_id">
                                    <option value="">Pilih adm</option>
                                    @foreach ($administrators as $administrator)
                                        <option value="{{ $administrator->id }}">{{ $administrator->nik.' - '.$administrator->nama }}</option>
                                    @endforeach
                                </select>
                                <small class="text-danger d-none" id="edit_adm_enroll_id_error"></small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-dark">Submit</button>
                    </div>
                </form>
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
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2').select2({
            theme: 'bootstrap4'
        })
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            $('#detail-produksi-day-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route('dataDetailProduksiDay') !!}',
                    data: function (d) {
                        d.date = $('#date-detail-produksi-day').val();
                    }
                },
                columns: [
                    {data: 'action',name: 'action', orderable: false, searchable: false},
                    {data: 'tgl_produksi',name: 'tgl_produksi', visible: false},
                    {data: 'tim_produksi',name: 'tim_produksi'},
                    {data: 'plan_info',name: 'plan_info'},
                    {data: 'production_info',name: 'production_info'},
                    {data: 'earning_info',name: 'earning_info'},
                    {data: 'data_info',name: 'data_info'},
                ],
            });

            $('#date-detail-produksi-day').on('change', () => {
                $('#detail-produksi-day-table').DataTable().ajax.reload();
            });

            $('#edit_chief_enroll_id').select2({
                theme: "bootstrap-5",
                dropdownParent: $("#updateDataDetailProduksiDayModal"),
            });

            $('#edit_leader_enroll_id').select2({
                theme: "bootstrap-5",
                dropdownParent: $("#updateDataDetailProduksiDayModal"),
            });

            $('#edit_adm_enroll_id').select2({
                theme: "bootstrap-5",
                dropdownParent: $("#updateDataDetailProduksiDayModal"),
            });
        });
    </script>
@endsection
