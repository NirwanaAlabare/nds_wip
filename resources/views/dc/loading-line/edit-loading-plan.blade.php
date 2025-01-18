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
    <form action="{{ route('update-loading-plan', ['id' => $loadingLinePlan->id]) }}" method="post" id="update-loading-plan" onsubmit="submitLoadingPlan(this, event)">
        @method('PUT')
        <div class="card card-sb">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold">
                        <i class="fa fa-edit"></i> Edit Loading Plan
                    </h5>
                    <a href="{{ route('loading-line') }}" class="btn btn-sm btn-primary">
                        <i class="fa fa-reply"></i> Kembali ke Loading Line
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 col-md-3 mb-3">
                        <label><small>Tanggal</small></label>
                        <input type="date" class="form-control" value="{{ date('Y-m-d') }}" name="tanggal" id="tanggal" readonly>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <label><small>Kode</small></label>
                        <input type="text" class="form-control" value="{{ $loadingLinePlan->kode }}" name="kode" id="kode" readonly>
                    </div>
                    <div class="col-6 col-md-6">
                        <div class="mb-1">
                            <div class="form-group">
                                @php
                                    $lineData = $lines->where("line_id", $loadingLinePlan->line_id)->first();
                                @endphp
                                <label><small>Line</small></label>
                                <input class="form-control" type="hidden" id="line_id" name="line_id" value="{{ $lineData->line_id }}" readonly>
                                <input class="form-control" type="text" id="line" name="line" value="{{ strtoupper(str_replace("_", " ", $lineData->username)) }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>No. WS</small></label>
                                <input class="form-control" type="hidden" id="ws_id" name="ws_id" value="{{ $loadingLinePlan->act_costing_id }}" readonly>
                                <input class="form-control" type="text" id="ws" name="ws" value="{{ $loadingLinePlan->act_costing_ws }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="mb-1">
                            <label class="form-label"><small>Buyer</small></label>
                            <input type="text" class="form-control" id="buyer" name="buyer" value="{{ $loadingLinePlan->buyer }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="mb-1">
                            <label class="form-label"><small>Style</small></label>
                            <input type="text" class="form-control" id="style" name="style" value="{{ $loadingLinePlan->style }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Target Sewing</small></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="target_sewing" name="target_sewing" value="{{ $loadingLinePlan->target_sewing }}">
                                    <span class="input-group-text">PCS</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Target Loading</small></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="target_loading" name="target_loading" value="{{ $loadingLinePlan->target_loading }}">
                                    <span class="input-group-text">PCS</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-6">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Color</small></label>
                                <input type="text" class="form-control" name="color" id="color" readonly value="{{ $loadingLinePlan->color }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <a type="button" href="{{ route('loading-line') }}" class="btn btn-danger fw-bold mt-3"><i class="fa fa-times"></i> Batal</a>
                    <button type="submit" class="btn btn-success fw-bold mt-3" id="submit-button"><i class="fa fa-save"></i> Simpan</button>
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
    <script>
        // Initial Window On Load Event
        $(document).ready(async function () {
            //Reset Form
            if (document.getElementById('update-loading-plan')) {
                document.getElementById('update-loading-plan').reset();
            }
        });

        // Prevent Form Submit When Pressing Enter
        document.getElementById("update-loading-plan").onkeypress = function(e) {
            var key = e.charCode || e.keyCode || 0;
            if (key == 13) {
                e.preventDefault();
            }
        }

        // Submit Part Form
        function submitLoadingPlan(e, evt) {
            document.getElementById('submit-button').setAttribute('disabled', true);

            evt.preventDefault();

            clearModified();

            $.ajax({
                url: e.getAttribute('action'),
                type: e.getAttribute('method'),
                data: new FormData(e),
                processData: false,
                contentType: false,
                success: async function(res) {
                    document.getElementById('submit-button').removeAttribute('disabled');

                    // Success Response

                    if (res.status == 200) {
                        // When Actually Success :

                        // Reset This Form
                        e.reset();

                        // Success Alert
                        Swal.fire({
                            icon: 'success',
                            title: 'Data Loading Plan Berhasil disimpan',
                            text: res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        }).then(() => {
                            if (res.redirect != '') {
                                if (res.redirect != 'reload') {
                                    location.href = res.redirect;
                                } else {
                                    location.reload();
                                }
                            }
                        })
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
                    document.getElementById('submit-button').removeAttribute('disabled');

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
