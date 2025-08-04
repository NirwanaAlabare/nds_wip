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
    <div class="d-flex justify-content-between align-items-end">
        <h5 class="text-sb text-center fw-bold">{{ strtoupper(str_replace("_", " ", $line)) }}, Master Plan</h5>
        <input type="hidden" id="line" value="{{ $line }}">
        <div class="d-flex justify-conten-end gap-3">
            <input type="date" class="form-control" value="{{ $date }}" id="date">
            <a href="{{ route('master-plan') }}" class="btn btn-sb-secondary"><i class="fa fa-reply "></i></a>
        </div>
    </div>
    <div class="row row-cols-4 g-3 my-3">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mx-1 my-3">
                        <h5 class="fw-bold">Master Plan</h5>
                        <h3 class="text-sb fw-bold">{{ num($masterPlan->count()) }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mx-1 my-3">
                        <h5 class="fw-bold">Jam Kerja</h5>
                        <h3 class="fw-bold {{ num($masterPlan->sum("jam_kerja")) != 8 ? "text-danger" : "text-success" }}">{{ num($masterPlan->sum("jam_kerja")) }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mx-1 my-3">
                        <h5 class="fw-bold">Man Power</h5>
                        <h3 class="text-primary fw-bold">{{ num($masterPlan->max("man_power")) }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    @php
                        $totalOutput = 0;

                        foreach ($masterPlan as $mp) {
                            $totalOutput += $mp->rfts->count();
                        }
                    @endphp
                    <div class="d-flex justify-content-between align-items-center mx-1 my-3">
                        <h5 class="fw-bold">Output</h5>
                        <h3 class="text-info fw-bold">{{ num($totalOutput) }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-sb collapsed collapsed-card" id="create-master-plan">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-plus fa-sm"></i> Tambah Master Plan - {{ strtoupper(str_replace("_", " ", $line)) }}, {{ $date }}</h5>
                <div class="d-flex justify-content-end align-items-center gap-1">
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form id="create-master-plan-form" method="post" action="{{ route('store-master-plan') }}" onsubmit="submitForm(this, event)">
                <div class="w-100 d-none" id="store-loading">
                    <div class="loading-container">
                        <div class="loading"></div>
                    </div>
                </div>
                <div id="store-form">
                    <div class="row g-3">
                        <input type="hidden" name="sewing_line" id="sewing_line" value="{{ $line }}">
                        <input type="hidden" name="tgl_plan" id="tgl_plan" value="{{ $date }}">
                        <div class="col-md-6">
                            <label>WS Number</label>
                            <select class="form-select form-select-sm select2bs4" name="id_ws" id="id_ws">
                                <option value="">Select WS</option>
                                @foreach ($actCosting as $ac)
                                    <option value="{{ $ac->id }}">{{ $ac->kpno }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger d-none" id="id_ws_error"></small>
                        </div>
                        <div class="col-md-6">
                            <input type="hidden" name="color" id="color">
                            <label>Color</label>
                            <select class="form-select form-select-sm select2bs4" name="color_select" id="color_select">
                                <option value="">Select Color</option>
                            </select>
                            <small class="text-danger d-none" id="color_select_error"></small>
                        </div>
                        <div class="col-md-4">
                            <label>SMV</label>
                            <input type="number" class="form-control form-control-sm" value="" step="0.001" name="smv" id="smv" onchange="calculateMasterPlanTarget()" onkeyup="calculateMasterPlanTarget()">
                            <small class="text-danger d-none" id="smv_error"></small>
                        </div>
                        <div class="col-md-4">
                            <label>Jam Kerja</label>
                            <input type="number" class="form-control form-control-sm" value="" step="0.001" name="jam_kerja" id="jam_kerja" onchange="calculateMasterPlanTarget()" onkeyup="calculateMasterPlanTarget()">
                            <small class="text-danger d-none" id="jam_kerja_error"></small>
                        </div>
                        <div class="col-md-4">
                            <label>Man Power</label>
                            <input type="number" class="form-control form-control-sm" value="" name="man_power" id="man_power" onchange="calculateMasterPlanTarget()" onkeyup="calculateMasterPlanTarget()">
                            <small class="text-danger d-none" id="man_power_error"></small>
                        </div>
                        <div class="col-md-4">
                            <label>Plan Target</label>
                            <input type="number" class="form-control form-control-sm" value="" name="plan_target" id="plan_target">
                            <small class="text-danger d-none" id="plan_target_error"></small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Target Efficiency</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control form-control-sm" value="" name="target_effy" id="target_effy">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-danger d-none" id="target_effy_error"></small>
                        </div>
                        <div class="col-md-4 d-none">
                            <label>Gambar</label>
                            <input accept="image/*"  type="file" class="form-control form-control-sm" name="gambar" id="gambar">
                            <img id="gambar-preview" src="#" alt="Preview Gambar" class="img-fluid" />
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button class="btn btn-success btn-sm fw-bold"><i class="fa fa-save"></i> SIMPAN</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title text-sb fw-bold"><i class="fa-solid fa-list"></i> Data Master Plan - {{ strtoupper(str_replace("_", " ", $line)) }}, {{ $date }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table table-bordered" id="datatable-master-plan">
                    <thead>
                        <tr>
                            <th>WS Number</th>
                            <th>Style</th>
                            <th>Style Production</th>
                            <th>Color</th>
                            <th>SMV</th>
                            <th>Jam Kerja</th>
                            <th>Man Power</th>
                            <th>Plan Target</th>
                            <th>Target Efficiency</th>
                            <th>Output</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($masterPlan->count() > 0)
                            @foreach ($masterPlan as $mp)
                                <tr>
                                    <td class="text-nowrap">{{ $mp->no_ws  }}</td>
                                    <td class="text-nowrap">{{ $mp->style }}</td>
                                    <td class="text-nowrap">{{ $mp->style_production }}</td>
                                    <td class="text-nowrap">{{ $mp->color }}</td>
                                    <td class="text-nowrap">{{ curr($mp->smv) }}</td>
                                    <td class="text-nowrap">{{ curr($mp->jam_kerja) }}</td>
                                    <td class="text-nowrap">{{ num($mp->man_power) }}</td>
                                    <td class="text-nowrap">{{ num($mp->plan_target) }}</td>
                                    <td class="text-nowrap">{{ curr($mp->target_effy) }} %</td>
                                    <td class="text-nowrap">{{ num($mp->rfts->count()) }}</td>
                                    <td class="text-nowrap">
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-primary btn-sm" onclick='editData({{ $mp->makeHidden(["rfts"]) }}, "editMasterPlanModal");'><i class="fa fa-edit"></i></button>
                                            <button class="btn btn-danger btn-sm" data='{{ $mp->makeHidden(["rfts"]) }}' data-url='{{ route('destroy-master-plan', ['id' => $mp->id]) }}' onclick='deleteData(this)'><i class="fa fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="11" class="text-center">Data tidak ditemukan</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal" tabindex="-1" id="editMasterPlanModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="edit-master-plan-form" method="post" action="{{ route('update-master-plan') }}" onsubmit="submitForm(this, event)">
                    @method('PUT')
                    <div class="modal-header bg-sb text-light">
                        <h5 class="modal-title"><i class="fa fa-edit"></i> Edit Master Plan - {{ strtoupper(str_replace("_", " ", $line)) }}, {{ $date }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="w-100" id="edit-loading">
                            <div class="loading-container">
                                <div class="loading"></div>
                            </div>
                        </div>
                        <div id="edit-form">
                            <div class="row g-3">
                                <input type="hidden" name="edit_id" id="edit_id">
                                <div class="col-md-6">
                                    <label>WS Number</label>
                                    <select class="form-select select2bs4" name="edit_id_ws" id="edit_id_ws">
                                        <option value="">Select WS</option>
                                        @foreach ($actCosting as $ac)
                                            <option value="{{ $ac->id }}">{{ $ac->kpno }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger d-none" id="edit_id_ws_error"></small>
                                </div>
                                <div class="col-md-6">
                                    <input type="hidden" name="edit_color" id="edit_color">
                                    <label>Color</label>
                                    <select class="form-select select2bs4" name="edit_color_select" id="edit_color_select">
                                        <option value="">Select Color</option>
                                    </select>
                                    <small class="text-danger d-none" id="edit_color_error"></small>
                                </div>
                                <div class="col-md-4">
                                    <label>SMV</label>
                                    <input type="number" class="form-control" value="" step="0.001" name="edit_smv" id="edit_smv" onchange="calculateMasterPlanTarget('edit_')" onkeyup="calculateMasterPlanTarget('edit_')">
                                    <small class="text-danger d-none" id="edit_smv_error"></small>
                                </div>
                                <div class="col-md-4">
                                    <label>Jam Kerja</label>
                                    <input type="number" class="form-control" value="" step="0.001" name="edit_jam_kerja" id="edit_jam_kerja" onchange="calculateMasterPlanTarget('edit_')" onkeyup="calculateMasterPlanTarget('edit_')">
                                    <small class="text-danger d-none" id="edit_jam_kerja_error"></small>
                                </div>
                                <div class="col-md-4">
                                    <label>Man Power</label>
                                    <input type="number" class="form-control" value="" name="edit_man_power" id="edit_man_power" onchange="calculateMasterPlanTarget('edit_')" onkeyup="calculateMasterPlanTarget('edit_')">
                                    <small class="text-danger d-none" id="edit_man_power_error"></small>
                                </div>
                                <div class="col-md-6">
                                    <label>Plan Target</label>
                                    <input type="number" class="form-control" value="" name="edit_plan_target" id="edit_plan_target">
                                    <small class="text-danger d-none" id="edit_plan_target_error"></small>
                                </div>
                                <div class="col-md-6">
                                    <label>Target Efficiency</label>
                                    <div class="input-group mb-3">
                                        <input type="number" class="form-control" value="" name="edit_target_effy" id="edit_target_effy">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <small class="text-danger d-none" id="edit_target_effy_error"></small>
                                </div>
                                <div class="col-md-6 d-none">
                                    <label>Gambar Baru</label>
                                    <input accept="image/*"  type="file" class="form-control form-control-sm" name="edit_gambar_new" id="edit_gambar_new">
                                    <img id="edit_gambar_new-preview" src="#" alt="Preview Gambar Baru" class="img-fluid" />
                                </div>
                                <div class="col-md-6 d-none">
                                    <label>Gambar Lama</label>
                                    <div>
                                        <img id="edit_gambar" src="#" alt="Gambar Lama" class="img-fluid" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger fw-bold" data-bs-dismiss="modal">BATAL</button>
                        <button type="submit" class="btn btn-primary fw-bold" id="update-master-plan-btn">SIMPAN</button>
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
    $('.select2master').select2({
        theme: 'bootstrap4'
    })
    $('.select2bs4').select2({
        theme: 'bootstrap4'
    })

    $('.select2roll').select2({
        theme: 'bootstrap4'
    })
</script>

<script>
    $( document ).ready(function() {
        $("#id_ws").val("").trigger("change");
    });

    $("#date").on("change", () => {
        location.href = '{{ route('master-plan-detail') }}/'+$("#line").val()+'/'+$("#date").val();
    });

    $("#id_ws").on("change.select2", async (element) => {
        storeLoadingStart();

        await updateColorList();

        storeLoadingStop();
    });

    $("#edit_id_ws").on("change.select2", async (element) => {
        editModalLoadingStart();

        await updateColorList("edit_");

        editModalLoadingStop();
    });

    $("#edit_color_select").on("change.select2", async (element) => {
        $("#edit_color").val($("#edit_color_select").val());
    });

    let datatableMasterPlan = $("#datatable-master-plan").DataTable({
        ordering: false,
        processing: true,
        paging: false,
    });

    function storeLoadingStart() {
        let loadingEl = document.getElementById("store-loading");
        let formEl = document.getElementById("store-form");

        loadingEl.classList.remove("d-none");
        formEl.classList.add("d-none");
    }

    function storeLoadingStop() {
        let loadingEl = document.getElementById("store-loading");
        let formEl = document.getElementById("store-form");

        loadingEl.classList.add("d-none");
        formEl.classList.remove("d-none");
    }

    function editModalLoadingStart() {
        let loadingEl = document.getElementById("edit-loading");
        let formEl = document.getElementById("edit-form");

        loadingEl.classList.remove("d-none");
        formEl.classList.add("d-none");
    }

    function editModalLoadingStop() {
        let loadingEl = document.getElementById("edit-loading");
        let formEl = document.getElementById("edit-form");

        loadingEl.classList.add("d-none");
        formEl.classList.remove("d-none");
    }

    function datatableMasterPlanReload() {
        location.reload();
    }

    function calculateMasterPlanTarget(type = "") {
        let manPower = $("#"+type+"man_power").val() ? $("#"+type+"man_power").val() : 0;
        let jamKerja = $("#"+type+"jam_kerja").val() ? $("#"+type+"jam_kerja").val() : 0;
        let smv = $("#"+type+"smv").val() ? $("#"+type+"smv").val() : 1;
        let planTarget = 0;

        if (smv > 0) {
            planTarget = Math.round((manPower * (jamKerja * 60)) / smv);
        }

        if (planTarget > 0) {
            $("#"+type+"plan_target").val(planTarget);
        }
    }

    async function updateColorList(type = "") {
        let colorElement = document.getElementById(type+'color_select');

        colorElement.innerHTML = "";

        return $.ajax({
            url: '{{ route("get-general-colors") }}',
            type: 'get',
            data: {
                act_costing_id: $('#'+type+'id_ws').val(),
            },
            success: async function (res) {
                if (res) {
                    await res.forEach(item => {
                        let option = document.createElement("option");
                        option.value = item.color;
                        option.innerText = item.color;
                        colorElement.appendChild(option);
                    });

                    console.log($("#"+type+"color").val());

                    $("#"+type+"color_select").val($("#"+type+"color").val()).trigger("change");
                }
            },
        });
    }

    var gambar = document.getElementById("gambar");
    var gambarPreview = document.getElementById("gambar-preview");
    gambar.onchange = (evt) => {
        const [file] = gambar.files;
        if (file) {
            gambarPreview.src = URL.createObjectURL(file)
        }
    }

    var editGambar = document.getElementById("edit_gambar_new");
    var editGambarPreview = document.getElementById("edit_gambar_new-preview");
    editGambar.onchange = (evt) => {
        const [file] = editGambar.files;
        if (file) {
            editGambarPreview.src = URL.createObjectURL(file)
        }
    }

    function reloadWindow() {
        window.location.reload();
    }
</script>
@endsection
