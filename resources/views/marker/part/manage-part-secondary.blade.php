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
    <div class="card card-sb">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold">
                    <i class="fa fa-circle-plus fa-sm"></i> Tambah Part Secondary
                </h5>
                <a href="{{ route('part') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-reply"></i> Kembali ke Part
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="#" method="post">
                <div class="row">
                    <input type="hidden" class="form-control form-control-sm" name="id" id="id" value="{{ $part->id }}" readonly>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Kode Part</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="kode" id="kode" value="{{ $part->kode }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>No. WS</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="ws" id="ws" value="{{ $part->act_costing_ws }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Buyer</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="buyer" id="buyer" value="{{ $part->buyer }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Style</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="style" id="style" value="{{ $part->style }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Color</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="color" id="color" value="{{ $part->color }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Panel</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="panel" id="panel" value="{{ $part->panel }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><small><b>Parts</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="part_details" id="part_details" value="{{ $part->part_details }}" readonly>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-12 mb-3">
            <div class="card card-primary h-100">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <h5 class="card-title fw-bold">
                                <i class="fa fa-list fa-sm"></i> Tambah Part Secondary
                            </h5>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="post" id="store-secondary" name='form'>
                        <div class="row">
                            <div class="col-6 col-md-3">
                                <div class="mb-4">
                                    <label><small><b>Part</b></small></label>
                                    <select class="form-control select2bs4" id="txtpart" name="txtpart" style="width: 100%;">
                                        <option selected="selected" value="">Pilih Part</option>
                                        @foreach ($data_part as $datapart)
                                            <option value="{{ $datapart->id }}">
                                                {{ $datapart->nama_part . ' - ' . $datapart->bag }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="mb-4">
                                    <label><small><b>Cons</b></small></label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" name="txtcons" id="txtcons">
                                        <div class="input-group-prepend">
                                            <select class="form-select" style="border-radius: 0 3px 3px 0;" name="txtconsunit" id="txtconsunit">
                                                <option value="meter">METER</option>
                                                <option value="yard">YARD</option>
                                                <option value="kgm">KGM</option>
                                                <option value="pcs">PCS</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="mb-4">
                                    <label><small><b>Tujuan</b></small></label>
                                    <select class="form-control select2bs4" id="cbotuj" name="cbotuj" style="width: 100%;" onchange="getproses();">
                                        <option selected="selected" value="">Pilih Tujuan</option>
                                        @foreach ($data_tujuan as $datatujuan)
                                            <option value="{{ $datatujuan->isi }}">
                                                {{ $datatujuan->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="row align-items-end">
                                    <div class="col-9">
                                        <div class="mb-4">
                                            <label><small><b>Proses</b></small></label>
                                            <select class="form-control select2bs4 w-100" id="cboproses" name="cboproses" style="width: 100%;">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="mb-4">
                                            <label><small><b>&nbsp</b></small></label>
                                            <button type="button" class="btn btn-block bg-primary" name="simpan" id="simpan" onclick="simpan_data();"><i class="fa fa-save"></i></button>
                                            {{-- <input type="button" class="btn bg-primary w-100" name="simpan" id="simpan" value="Simpan" onclick="simpan_data();"> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table id="datatable_list_part" class="table table-bordered table w-100">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Part</th>
                                    <th>Cons.</th>
                                    <th>Satuan</th>
                                    <th>Tujuan</th>
                                    <th>Proses</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Edit Modal -->
    <form action="{{ route('update-part-secondary') }}" method="post" id="update_part_secondary_form" onsubmit="submitForm(this, event)">
        @method("PUT")
        <div class="modal fade" id="editPartSecondaryModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="editPartSecondaryModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-sb">
                        <h1 class="modal-title fs-5" id="editPartSecondaryModalLabel"><i class="fa fa-edit"></i> Edit Part Detail</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <input type="hidden" class="form-control" readonly id="edit_id" name="edit_id">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Part</label>
                            <input type="text" class="form-control" id="edit_nama_part" name="edit_nama_part" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cons</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="edit_cons" name="edit_cons" readonly>
                                <input type="text" class="form-control" id="edit_unit" name="edit_unit" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tujuan</label>
                            <select class="form-select select2bs4" name="edit_tujuan" id="edit_tujuan" onchange="getproses(document.getElementById('edit_proses'), this);">
                                @foreach ($data_tujuan as $datatujuan)
                                    <option value="{{ $datatujuan->isi }}">
                                        {{ $datatujuan->tampil }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Proses</label>
                            <select class="form-select select2bs4" name="edit_proses" id="edit_proses"></select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sb-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-sb">Simpan</button>
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
        //Initialize Select2 Elements
        $('.select2').select2()

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        cleardata();
        dataTableReload();

        function cleardata() {
            $("#cboproses").val('').trigger('change');
            $("#cbotuj").val('').trigger('change');
            $("#txtpart").val('').trigger('change');
            $("#txtcons").val('').trigger('change');
            $("#txtconsunit").val('METER').trigger('change');
        }

        async function getproses(element, basedOn) {
            let cbotuj = basedOn ? basedOn.value : document.form.cbotuj.value;
            let html = $.ajax({
                type: "GET",
                url: '{{ route('get_proses') }}',
                data: {
                    cbotuj: cbotuj
                },
                async: false
            }).responseText;

            if (html != "") {
                if (element) {
                    element.innerHTML = html;
                    console.log(element.innerHTML, html);
                } else {
                    $("#cboproses").html(html);
                }
            }
        };

        function dataTableReload() {
            let datatable = $("#datatable_list_part").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                paging: false,
                destroy: true,
                ajax: {
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('datatable_list_part') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: function(d) {
                        d.id = $('#id').val();
                    },
                },
                columns: [
                    {
                        data: 'id',
                    },
                    {
                        data: 'nama_part',
                    },
                    {
                        data: 'cons',
                    },
                    {
                        data: 'unit',
                    },
                    {
                        data: 'tujuan',
                    },
                    {
                        data: 'proses',
                    },
                ],
                columnDefs: [
                    {
                        targets: [0],
                        className: "text-center",
                        render: (data, type, row, meta) => {
                            let disableDelete = (row.total_stocker > 0 ? 'disabled' : '');
                            return `
                                <button class='btn btn-primary btn-sm' onclick='editData(`+JSON.stringify(row)+`, "editPartSecondaryModal")'>
                                    <i class='fa fa-edit'></i>
                                </button>
                                <button class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route('destroy-part-detail') }}/`+row['id']+`' onclick='deleteData(this)' {{ Auth::user()->roles->whereIn("nama_role", ["admin", "superadmin"])->count() > 0 ? '' : '`+(disableDelete)+`'}}>
                                    <i class='fa fa-trash'></i>
                                </button>
                            `;
                        }
                    },
                ]
            });
        }

        function simpan_data() {
            let id = document.getElementById("id").value;
            let cbotuj = document.form.cbotuj.value;
            let txtpart = document.form.txtpart.value;
            let txtcons = document.form.txtcons.value;
            let txtconsunit = document.form.txtconsunit.value;
            let cboproses = document.form.cboproses.value;
            $.ajax({
                type: "post",
                url: '{{ route('store_part_secondary') }}',
                data: {
                    id: id,
                    cbotuj: cbotuj,
                    txtpart: txtpart,
                    txtcons: txtcons,
                    txtconsunit: txtconsunit,
                    cboproses: cboproses
                },
                success: function(response) {
                    if (response.icon == 'salah') {
                        iziToast.warning({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    } else {
                        iziToast.success({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    }
                    dataTableReload();
                    cleardata();
                },
                // error: function(request, status, error) {
                //     alert(request.responseText);
                // },
            });
        };
    </script>
@endsection
