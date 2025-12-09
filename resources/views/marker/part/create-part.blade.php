@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .draggable-list {
            list-style: none;
            padding: 0;
            margin: 10px 0;
        }

        .draggable-list li {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 8px 12px;
            margin-bottom: 5px;
            cursor: grab;
            border-radius: 8px;
            transition: background 0.2s ease;
        }

        .draggable-list li.dragging {
            opacity: 0.5;
            background: #e9ecef;
        }
    </style>
@endsection

@section('content')
    <form action="{{ route('store-part') }}" method="post" id="store-part" onsubmit="submitPartForm(this, event)">
        @csrf
        <div class="card">
            <div class="card-header bg-sb">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold">
                        <i class="fa fa-plus fa-sm"></i> Tambah Data Part Group
                    </h5>
                    <a href="{{ route('part') }}" class="btn btn-sm btn-primary">
                        <i class="fa fa-reply"></i> Kembali ke Part
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>No. WS</small></label>
                                <select class="form-control select2bs4" id="ws_id" name="ws_id" style="width: 100%;">
                                    <option selected="selected" value="">Pilih WS</option>
                                    @foreach ($orders as $order)
                                        <option value="{{ $order->id }}">
                                            {{ $order->kpno }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Buyer</small></label>
                            <input type="text" class="form-control" id="buyer" name="buyer" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Style</small></label>
                            <input type="text" class="form-control" id="style" name="style" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Panel</small></label>
                                <select class="form-control select2bs4" id="panel_id" name="panel_id" style="width: 100%;">
                                    <option selected="selected" value="">Pilih Panel</option>
                                    {{-- select 2 option --}}
                                </select>
                                <input type="hidden" class="form-control" id="panel" name="panel" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <input type="hidden" class="form-control" id="ws" name="ws" readonly>
                    <div class="col-9 col-md-9">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Color</small></label>
                                <input type="text" class="form-control" name="color" id="color" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-3 col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Status</small></label>
                                <select class="form-control select2bs4" name="panel_status" id="panel_status" onchange="switchPanelStatus(this)">
                                    <option value="">Pilih Status</option>
                                    <option value="main">MAIN</option>
                                    <option value="complement">COMPLEMENT</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-12 mb-3" id="parts-section">
                        <hr class="border-dark">
                        <h5 class="text-sb">Parts</h5>
                        <div class="card">
                            <div class="card-header bg-sb-secondary">
                                <h6 class="mb-0">Part 1</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4">
                                        <label class="form-label"><small>Part</small></label>
                                        <select class="form-control select2bs4" name="part_details[0]" id="part_details_0">
                                            <option value="">Pilih Part</option>
                                            @foreach ($masterParts as $masterPart)
                                                <option value="{{ $masterPart->id }}" data-index="0">{{ $masterPart->nama_part }} - {{ $masterPart->bag }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label"><small>Cons</small></label>
                                        <div class="d-flex mb-3">
                                            <div style="width: 50%;">
                                                <input type="number" class="form-control" style="border-radius: 3px 0 0 3px;" name="cons[0]" id="cons_0" step="0.001">
                                            </div>
                                            <div style="width: 50%;">
                                                <select class="form-select" style="border-radius: 0 3px 3px 0;" name="cons_unit[0]" id="cons_unit_0">
                                                    <option value="meter">METER</option>
                                                    <option value="yard">YARD</option>
                                                    <option value="kgm">KGM</option>
                                                    <option value="pcs">PCS</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label"><small>Tujuan</small></label>
                                        <select class="form-control select2bs4" style="border-radius: 0 3px 3px 0;" name="tujuan[0]" id="tujuan_0" onchange="switchTujuan(this, 0)">
                                            <option value="">Pilih Tujuan</option>
                                            <option value="NON SECONDARY">NON SECONDARY</option>
                                            <option value="SECONDARY">SECONDARY</option>
                                        </select>
                                    </div>
                                    <div class="col-3 d-none" id="non_secondary_container_0">
                                        <label class="form-label"><small>Proses</small></label>
                                        <select class="form-control select2bs4" style="border-radius: 0 3px 3px 0;" name="proses[0]" id="proses_0" data-index="0" onchange="orderNonSecondary(this, 0)">
                                            <option value="">Pilih Proses</option>
                                            @foreach ($masterSecondary->where("tujuan", "NON SECONDARY") as $secondary)
                                                <option value="{{ $secondary->id }}" data-tujuan="{{ $secondary->id_tujuan }}">{{ $secondary->proses." / ".$secondary->tujuan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col d-none" id="secondary_container_0">
                                        <label class="form-label"><small>Proses</small></label>
                                        <div class="d-flex gap-1">
                                            <select class="form-control select2bs4" id="secondaries_0" name="secondaries[0][]" data-width="100%" multiple onchange="orderSecondary(this, 0)">
                                                @foreach ($masterSecondary->where("tujuan", "!=", "NON SECONDARY") as $secondary)
                                                    <option value="{{ $secondary->id }}" data-tujuan="{{ $secondary->id_tujuan }}">{{ $secondary->proses." / ".$secondary->tujuan }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-sm btn-dark ps-1 ms-2" onclick="clearSelectOptions(0)">
                                                <i class="fa fa-rotate-left"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col d-none" id="urutan_container_0">
                                        <label class="form-label"><small>Urutan</small></label>
                                        <ul class="list-group" id="urutan_show_0">
                                        </ul>
                                        <input type="text" class="form-control d-none" id="urutan_0" name="urutan[0]" readonly>
                                    </div>
                                    <div class="col d-none">
                                        <label class="form-label"><small>Proses Secondary Luar</small></label>
                                        <select class="form-control select2bs4" style="border-radius: 0 3px 3px 0;" name="proses_secondary_luar[0]" id="proses_secondary_luar_0" data-index="0">
                                            <option value="">Pilih Proses</option>
                                            @foreach ($masterSecondary as $secondary)
                                                <option value="{{ $secondary->id }}" data-tujuan="{{ $secondary->id_tujuan }}">{{ $secondary->proses." / ".$secondary->tujuan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label class="form-label"><small>Main Part</small></label>
                                        <br>
                                        <div class="form-check">
                                            <input class="form-check-input is-main-part" type="checkbox" value="true" id="main_part_0" name="main_part[0]" onchange="uncheckOtherMainPart(0)" checked="true">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12" id="buttonAddNewPart">
                        <button type="button" class="btn btn-sm btn-sb-secondary float-end my-3" onclick="addNewPart()">
                            <i class="far fa-plus-square"></i>
                        </button>
                    </div>
                    <div class="col-12 col-md-12 mb-3 align-items-end d-none" id="complement-parts-section">
                        <hr class="border-dark">
                        <h5 class="text-sb-secondary">Complement Part</h5>
                        <div class="row">
                            <div class="col">
                                <label class="form-label"><small>Part</small></label>
                                <select class="form-control select2bs4" name="com_part_details[0]" id="com_part_details_0">
                                    <option value="">Pilih Part</option>
                                    @foreach ($masterParts as $masterPart)
                                        <option value="{{ $masterPart->id }}" data-index="0">{{ $masterPart->nama_part }} - {{ $masterPart->bag }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label"><small>From Panel</small></label>
                                <select class="form-control select2bs4 com_from_panel_id" name="com_from_panel_id[0]" id="com_from_panel_id_0" onchange="updateComplementPanelPartList(0)">
                                    <option value="">Pilih Panel</option>
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label"><small>From Part</small></label>
                                <select class="form-control select2bs4" name="com_from_part_id[0]" id="com_from_part_id_0">
                                    <option value="">Pilih Part</option>
                                </select>
                            </div>
                            <div class="col d-none">
                                <label class="form-label"><small>Tujuan</small></label>
                                <select class="form-control select2bs4" style="border-radius: 0 3px 3px 0;" name="com_tujuan[0]" id="com_tujuan_0">
                                    <option value="">Pilih Tujuan</option>
                                    @foreach ($masterTujuan as $tujuan)
                                        <option value="{{ $tujuan->id }}">{{ $tujuan->tujuan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col d-none">
                                <label class="form-label"><small>Proses</small></label>
                                <select class="form-control select2bs4" style="border-radius: 0 3px 3px 0;" name="com_proses[0]" id="com_proses_0" data-index="0" onchange="changeTujuan(this)">
                                    <option value="">Pilih Proses</option>
                                    @foreach ($masterSecondary as $secondary)
                                        <option value="{{ $secondary->id }}" data-tujuan="{{ $secondary->id_tujuan }}">{{ $secondary->proses." / ".$secondary->tujuan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col d-none">
                                <label class="form-label"><small>Urutan</small></label>
                                <input type="text" class="form-control" id="com_urutan_0" name="com_urutan[0]">
                            </div>
                            <div class="col d-none">
                                <label class="form-label"><small>Proses Secondary Luar</small></label>
                                <select class="form-control select2bs4" style="border-radius: 0 3px 3px 0;" name="com_proses_secondary_luar[0]" id="com_proses_secondary_luar_0" data-index="0" onchange="changeTujuan(this)">
                                    <option value="">Pilih Proses</option>
                                    @foreach ($masterSecondary->where("tujuan", "SECONDARY LUAR") as $secondary)
                                        <option value="{{ $secondary->id }}" data-tujuan="{{ $secondary->id_tujuan }}">{{ $secondary->proses." / ".$secondary->tujuan }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-12" id="buttonAddNewPartComplement">
                        <button type="button" class="btn btn-sm btn-sb-secondary float-end my-3" onclick="addNewPartComplement()">
                            <i class="far fa-plus-square"></i>
                        </button>
                    </div>
                    <input type="hidden" class="form-control" id="jumlah_part_detail" name="jumlah_part_detail" value="1" readonly>
                    <input type="hidden" class="form-control" id="jumlah_complement_part_detail" name="jumlah_complement_part_detail" value="1" readonly>
                </div>
                <div class="row justify-content-between">
                    <div class="col-3">
                        <a href="{{ route('part') }}" class="btn btn-danger btn-sm btn-block fw-bold mt-3" id="cancel-button"><i class="fa fa-close"></i> BATAL</a>
                    </div>
                    <div class="col-3">
                        <button type="submit" class="btn btn-success btn-sm btn-block fw-bold mt-3" id="submit-button"><i class="fa fa-save"></i> SIMPAN</button>
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
    <script>
        // Global Variable
        var sumCutQty = null;
        var totalRatio = null;

        var partSection = null;
        var partOptions = null;
        var tujuanOptions = null;
        var prosesNonSecondaryOptions = null;
        var prosesSecondaryOptions = null;
        var selectedPartArray = [];

        var complementPanelOptions = null;
        var complementPartOptions = null;

        var jumlahPartDetail = null;
        var jumlahComplementPartDetail = null;

        document.getElementById('loading').classList.remove("d-none");

        // Initial Window On Load Event
        $(document).ready(async function () {
            //Reset Form
            if (document.getElementById('store-part')) {
                document.getElementById('store-part').reset();

                $(".select2").val('').trigger('change');
                $(".select2bs4").val('').trigger('change');
                $(".select2bs4custom").val('').trigger('change');

                $("#ws_id").val(null).trigger("change");
                $('#part_details').val(null).trigger('change');

                await getMasterParts();
                await getTujuan();
                await getProsesNonSecondary();
                await getProsesSecondary();

                partSection = document.getElementById('parts-section');

                jumlahPartDetail = document.getElementById('jumlah_part_detail');
                jumlahPartDetail.value = 1;

                jumlahComplementPartDetail = document.getElementById('jumlah_complement_part_detail');
                jumlahComplementPartDetail.value = 1;
            }

            // Select2 Prevent Step-Jump Input ( Step = WS -> Panel )
            $("#panel_id").prop("disabled", true);

            document.getElementById('loading').classList.add("d-none");
        });

        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });

        // Step One (WS) on change event
        $('#ws_id').on('change', async function(e) {
            document.getElementById("loading").classList.remove("d-none");

            if (this.value) {
                await updateOrderInfo();
                await updatePanelList();
            }

            document.getElementById("loading").classList.add("d-none");
        });

        // Step Two (Panel) on change event
        $('#panel_id').on('change', async function(e) {
            document.getElementById("panel").value = $('#panel_id option:selected').text();

            updateComplementPanelList();
        });

        // Update Order Information Based on Order WS and Order Color
        function updateOrderInfo() {
            return $.ajax({
                url: '{{ route("get-part-order") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                },
                dataType: 'json',
                success: function (res) {
                    if (res) {
                        document.getElementById('ws').value = res.kpno;
                        document.getElementById('buyer').value = res.buyer;
                        document.getElementById('style').value = res.styleno;
                        document.getElementById('color').value = res.colors;
                    }
                },
            });
        }

        // Update Panel Select Option Based on Order WS and Color WS
        function updatePanelList() {
            document.getElementById("loading").classList.remove("d-none");

            document.getElementById('panel_id').value = null;
            return $.ajax({
                url: '{{ route("get-part-panels") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color').val(),
                },
                success: function (res) {
                    document.getElementById("loading").classList.add("d-none");

                    if (res) {
                        // Update this step
                        document.getElementById('panel_id').innerHTML = res;

                        // Open this step
                        $("#panel_id").prop("disabled", false);
                    }
                },
                error: function (jqXHR) {
                    console.error(jqXHR);

                    document.getElementById("loading").classList.add("d-none");
                }
            });
        }

        function getMasterParts() {
            document.getElementById("loading").classList.remove("d-none");

            return $.ajax({
                url: '{{ route("get-master-parts") }}',
                type: 'get',
                success: function (res) {
                    document.getElementById("loading").classList.add("d-none");

                    if (res) {
                        partOptions = res;
                    }
                },
                error: function (jqXHR) {
                    document.getElementById("loading").classList.add("d-none");

                    console.log(jqXHR);
                }
            });
        }

        function getTujuan() {
            document.getElementById("loading").classList.remove("d-none");

            return $.ajax({
                url: '{{ route("get-master-tujuan") }}',
                type: 'get',
                success: function (res) {
                    document.getElementById("loading").classList.add("d-none");

                    if (res) {
                        tujuanOptions = res;
                    }
                },
                error: function (jqXHR) {
                    document.getElementById("loading").classList.add("d-none");

                    console.log(jqXHR);
                }
            });
        }

        function getProsesNonSecondary() {
            document.getElementById("loading").classList.remove("d-none");

            return $.ajax({
                url: '{{ route("get-master-secondary") }}',
                data: {
                    type: "non secondary"
                },
                type: 'get',
                success: function (res) {
                    document.getElementById("loading").classList.add("d-none");

                    if (res) {
                        prosesNonSecondaryOptions = res;
                    }
                },
                error: function (jqXHR) {
                    console.log(jqXHR);

                    document.getElementById("loading").classList.add("d-none");
                }
            });
        }

        function getProsesSecondary() {
            document.getElementById("loading").classList.remove("d-none");

            return $.ajax({
                url: '{{ route("get-master-secondary") }}',
                data: {
                    type: "secondary"
                },
                type: 'get',
                success: function (res) {
                    document.getElementById("loading").classList.add("d-none");

                    if (res) {
                        prosesSecondaryOptions = res;
                    }
                },
                error: function (jqXHR) {
                    console.log(jqXHR);

                    document.getElementById("loading").classList.add("d-none");
                }
            });
        }

        // Update Complement Panel Select Option Based on Order WS and Color WS
        function updateComplementPanelList(index = null) {
            document.getElementById("loading").classList.remove("d-none");

            // Empty Complement Panel Options
            let complementPanels = document.querySelectorAll('.com_from_panel_id');
            if (complementPanels) {
                if (index !== undefined && index !== null) {
                    complementPanels[index].innerHTML = null;
                } else {
                    for(let i = 0; i < complementPanels.length; i++) {
                        complementPanels[i].innerHTML = null;
                    }
                }
            }

            return $.ajax({
                url: '{{ route("get-part-complement-panels") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color').val(),
                    panel: $('#panel').val(),
                },
                success: function (res) {
                    document.getElementById("loading").classList.add("d-none");

                    if (res) {
                        // Update this step
                        let complementPanels = document.querySelectorAll('.com_from_panel_id');

                        if (complementPanels) {
                            if (index !== undefined && index !== null) {
                                complementPanels[index].innerHTML = res;

                                complementPanels[index].disabled = false;
                            } else {
                                for(let i = 0; i < complementPanels.length; i++) {
                                    complementPanels[i].innerHTML = res;

                                    complementPanels[i].disabled = false;
                                }
                            }
                        }
                    }
                },
                error: function (jqXHR) {
                    console.error(jqXHR);

                    document.getElementById("loading").classList.add("d-none");
                }
            });
        }

        // Update Complement Panel Part Select Option Based on Order WS and Color WS
        function updateComplementPanelPartList(index) {
            if (index != null) {
                document.getElementById("loading").classList.remove("d-none");

                document.getElementById('com_from_part_id_'+index).innerHTML = null;
                return $.ajax({
                    url: '{{ route("get-part-complement-panel-parts") }}',
                    type: 'get',
                    data: {
                        part_id: $('#com_from_panel_id_'+index).val(),
                    },
                    success: function (res) {
                        document.getElementById("loading").classList.add("d-none");

                        if (res) {
                            // Update this step
                            let complementPanelParts = document.getElementById('com_from_part_id_'+index);

                            complementPanelParts.innerHTML = res;

                            complementPanelParts.disabled = false;
                        }
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }
        }

        // Switch Panel Status
        function switchPanelStatus(element) {
            if (element.value == "main") {
                showComplementSection();
            } else {
                hideComplementSection();
            }
        }

        // Show Complement Section
        function showComplementSection() {
            let complementPartSection = document.getElementById("complement-parts-section");
            let complementPartButton = document.getElementById("buttonAddNewPartComplement")

            if (complementPartSection) {
                complementPartSection.classList.remove("d-none");
            }

            if (complementPartButton) {
                complementPartButton.classList.remove("d-none");
            }

            let isMainPartElements = document.querySelectorAll(".is-main-part");
            if (isMainPartElements && isMainPartElements.length > 0) {
                isMainPartElements[0].checked = true;
                for (let i = 0; i < isMainPartElements.length; i++) {
                    isMainPartElements[i].classList.add("d-none");
                }
            }
        }

        // Hide Complement Section
        function hideComplementSection() {
            let complementPartSection = document.getElementById("complement-parts-section");
            let complementPartButton = document.getElementById("buttonAddNewPartComplement")

            if (complementPartSection) {
                complementPartSection.classList.add("d-none");
            }

            if (complementPartButton) {
                complementPartButton.classList.add("d-none");
            }

            // hide main part check
            let isMainPartElements = document.querySelectorAll(".is-main-part");
            if (isMainPartElements && isMainPartElements.length > 0) {
                for (let i = 0; i < isMainPartElements.length; i++) {
                    isMainPartElements[i].classList.add("d-none");
                    isMainPartElements[i].checked = false;
                }
            }
        }

        function uncheckOtherMainPart(index) {
            let isMainPartElements = document.querySelectorAll(".is-main-part");

            for (let i = 0; i < isMainPartElements.length; i++) {
                if (i != index) {
                    isMainPartElements[i].checked = false;
                }
            }
        }

        function switchTujuan(element, index) {
            let nonSecondaryContainer = document.getElementById("non_secondary_container_"+index);
            let secondaryContainer = document.getElementById("secondary_container_"+index);
            let urutanContainer = document.getElementById("urutan_container_"+index);
            let prosesElement = null;

            if (nonSecondaryContainer && secondaryContainer && urutanContainer) {
                if (element.value == "NON SECONDARY") {
                    nonSecondaryContainer.classList.remove("d-none");
                    secondaryContainer.classList.add("d-none");
                    urutanContainer.classList.add("d-none");

                    // Clear Non Secondary
                    prosesElement = document.getElementById("proses_"+index);
                    orderNonSecondary(prosesElement, index);
                } else if (element.value == "SECONDARY") {
                    nonSecondaryContainer.classList.add("d-none");
                    secondaryContainer.classList.remove("d-none");
                    urutanContainer.classList.remove("d-none");

                    // Clear Secondary
                    prosesElement = document.getElementById("secondaries_"+index);
                    clearSelectOptions(index);
                    orderSecondary(prosesElement, index);
                } else {
                    nonSecondaryContainer.classList.remove("d-none");
                    secondaryContainer.classList.remove("d-none");
                    urutanContainer.classList.remove("d-none");

                    nonSecondaryContainer.classList.add("d-none");
                    secondaryContainer.classList.add("d-none");
                    urutanContainer.classList.add("d-none");

                    clearSelectOptions(index);
                }
            }
        }

        function changeTujuan(element) {
            let thisIndex = element.getAttribute('data-index');
            let thisSelected = element.options[element.selectedIndex];
            let thisTujuan = document.getElementById('tujuan_'+thisIndex);

            if (thisTujuan && thisTujuan.value != thisSelected.getAttribute('data-tujuan')) {
                $('#tujuan_'+thisIndex).val(thisSelected.getAttribute('data-tujuan')).trigger("change");
            }
        }

        function changeComplementTujuan(element) {
            let thisIndex = element.getAttribute('data-index');
            let thisSelected = element.options[element.selectedIndex];
            let thisTujuan = document.getElementById('com_tujuan_'+thisIndex);

            if (thisTujuan.value != thisSelected.getAttribute('data-tujuan')) {
                $('#com_tujuan_'+thisIndex).val(thisSelected.getAttribute('data-tujuan')).trigger("change");
            }
        }

        function addNewPart() {
            if (!jumlahPartDetail) {
                Swal.fire({
                    title: "Warning",
                    text: "Harap pilih No. WS",
                    icon: "warning"
                });
                return;
            }

            let index = parseInt(jumlahPartDetail.value);

            // Create card container
            let card = document.createElement('div');
            card.classList.add('card', 'mt-3');

            // --- Card Header ---
            let cardHeader = document.createElement('div');
            cardHeader.classList.add('card-header', 'bg-sb-secondary');
            cardHeader.innerHTML = `<h6 class="mb-0">Part ${index + 1}</h6>`; // 1-based numbering

            // Card body
            let cardBody = document.createElement('div');
            cardBody.classList.add('card-body');

            let row = document.createElement('div');
            row.classList.add('row');

            // --- 1. Part ---
            let colPart = document.createElement('div');
            colPart.classList.add('col-4');
            colPart.innerHTML = `
                <label class="form-label"><small>Part</small></label>
                <select class="form-control select2bs4" name="part_details[${index}]" id="part_details_${index}">
                    ${partOptions}
                </select>
            `;

            // --- 2. Cons + Unit ---
            let colCons = document.createElement('div');
            colCons.classList.add('col-4');
            colCons.innerHTML = `
                <label class="form-label"><small>Cons</small></label>
                <div class="d-flex mb-3">
                    <div style="width: 50%;">
                        <input type="number" class="form-control" style="border-radius: 3px 0 0 3px;" name="cons[${index}]" id="cons_${index}" step="0.001">
                    </div>
                    <div style="width: 50%;">
                        <select class="form-select" style="border-radius: 0 3px 3px 0;" name="cons_unit[${index}]" id="cons_unit_${index}">
                            <option value="meter">METER</option>
                            <option value="yard">YARD</option>
                            <option value="kgm">KGM</option>
                            <option value="pcs">PCS</option>
                        </select>
                    </div>
                </div>
            `;

            // --- 3. Tujuan ---
            let colTujuan = document.createElement('div');
            colTujuan.classList.add('col-4');
            colTujuan.innerHTML = `
                <label class="form-label"><small>Tujuan</small></label>
                <select class="form-control select2bs4" name="tujuan[${index}]" id="tujuan_${index}" onchange="switchTujuan(this, ${index})">
                    <option value="">Pilih Tujuan</option>
                    <option value="NON SECONDARY">NON SECONDARY</option>
                    <option value="SECONDARY">SECONDARY</option>
                </select>
            `;

            // --- 4. Non-Secondary Proses ---
            let colNonSecondary = document.createElement('div');
            colNonSecondary.classList.add('col-3', 'd-none');
            colNonSecondary.id = `non_secondary_container_${index}`;
            colNonSecondary.innerHTML = `
                <label class="form-label"><small>Proses</small></label>
                <select class="form-control select2bs4" name="proses[${index}]" id="proses_${index}" data-index="${index}" onchange="orderNonSecondary(this, ${index})">
                    <option value="">Pilih Proses</option>
                    ${prosesNonSecondaryOptions}
                </select>
            `;

            // --- 5. Secondary Proses (multi-select + clear) ---
            let colSecondary = document.createElement('div');
            colSecondary.classList.add('col', 'd-none');
            colSecondary.id = `secondary_container_${index}`;
            colSecondary.innerHTML = `
                <label class="form-label"><small>Proses</small></label>
                <div class="d-flex gap-1">
                    <select class="form-control select2bs4" id="secondaries_${index}" name="secondaries[${index}][]" data-width="100%" multiple onchange="orderSecondary(this, ${index})">
                        ${prosesSecondaryOptions}
                    </select>
                    <button type="button" class="btn btn-sm btn-dark ps-1 ms-2" onclick="clearSelectOptions(${index})">
                        <i class="fa fa-rotate-left"></i>
                    </button>
                </div>
            `;

            // --- 6. Urutan container ---
            let colUrutan = document.createElement('div');
            colUrutan.classList.add('col', 'd-none');
            colUrutan.id = `urutan_container_${index}`;
            colUrutan.innerHTML = `
                <label class="form-label"><small>Urutan</small></label>
                <ul class="list-group" id="urutan_show_${index}"></ul>
                <input type="text" class="form-control d-none" id="urutan_${index}" name="urutan[${index}]" readonly>
            `;

            // --- 7. Proses Secondary Luar ---
            let colProsesLuar = document.createElement('div');
            colProsesLuar.classList.add('col', 'd-none');
            colProsesLuar.innerHTML = `
                <label class="form-label"><small>Proses Secondary Luar</small></label>
                <select class="form-control select2bs4" name="proses_secondary_luar[${index}]" id="proses_secondary_luar_${index}" data-index="${index}">
                    <option value="">Pilih Proses</option>
                    ${prosesSecondaryOptions}
                </select>
            `;

            // --- 8. Main Part checkbox ---
            let colMain = document.createElement('div');
            colMain.classList.add('col');
            colMain.innerHTML = `
                <label class="form-label"><small>Main Part</small></label><br>
                <div class="form-check">
                    <input class="form-check-input is-main-part" type="checkbox" value="true" id="main_part_${index}" name="main_part[${index}]" onchange="uncheckOtherMainPart(${index})">
                </div>
            `;

            // Append all columns to row
            row.appendChild(colPart);
            row.appendChild(colCons);
            row.appendChild(colTujuan);
            row.appendChild(colNonSecondary);
            row.appendChild(colSecondary);
            row.appendChild(colUrutan);
            row.appendChild(colProsesLuar);
            row.appendChild(colMain);

            cardBody.appendChild(row);

            // Append header and body to card
            card.appendChild(cardHeader);
            card.appendChild(cardBody);

            // Append card to parts section
            document.getElementById('parts-section').appendChild(card);

            // Reinitialize Select2
            $(`#part_details_${index}`).select2({ theme: 'bootstrap4' });
            $(`#tujuan_${index}`).select2({ theme: 'bootstrap4' });
            $(`#proses_${index}`).select2({ theme: 'bootstrap4' });
            $(`#secondaries_${index}`).select2({ theme: 'bootstrap4', width: '100%' });
            $(`#proses_secondary_luar_${index}`).select2({ theme: 'bootstrap4' });

            // Increment counter
            jumlahPartDetail.value++;
        }


        function addNewPartComplement() {
            if (jumlahComplementPartDetail) {
                let index = jumlahComplementPartDetail.value;

                // Create new row
                let divRow = document.createElement('div');
                divRow.setAttribute('class', 'row mt-2');

                // --- 1. Part ---
                let divColPart = document.createElement('div');
                divColPart.setAttribute('class', 'col');
                divColPart.innerHTML = `
                    <label class="form-label"><small>Part</small></label>
                    <select class="form-control select2bs4" name="com_part_details[${index}]" id="com_part_details_${index}">
                        ${partOptions}
                    </select>
                `;

                // --- 2. From Panel ---
                let divColFromPanel = document.createElement('div');
                divColFromPanel.setAttribute('class', 'col');
                divColFromPanel.innerHTML = `
                    <label class="form-label"><small>From Panel</small></label>
                    <select class="form-control select2bs4 com_from_panel_id" name="com_from_panel_id[${index}]" id="com_from_panel_id_${index}" onchange="updateComplementPanelPartList(${index})">
                        <option value="">Pilih Panel</option>
                    </select>
                `;

                // --- 3. From Part ---
                let divColFromPart = document.createElement('div');
                divColFromPart.setAttribute('class', 'col');
                divColFromPart.innerHTML = `
                    <label class="form-label"><small>From Part</small></label>
                    <select class="form-control select2bs4" name="com_from_part_id[${index}]" id="com_from_part_id_${index}">
                        <option value="">Pilih Part</option>
                    </select>
                `;

                // --- 4. Tujuan ---
                let divColTujuan = document.createElement('div');
                divColTujuan.setAttribute('class', 'col');
                divColTujuan.setAttribute('class', 'd-none');
                divColTujuan.innerHTML = `
                    <label class="form-label"><small>Tujuan</small></label>
                    <select class="form-control select2bs4" name="com_tujuan[${index}]" id="com_tujuan_${index}">
                        ${tujuanOptions}
                    </select>
                `;

                // --- 5. Proses ---
                let divColProses = document.createElement('div');
                divColProses.setAttribute('class', 'col');
                divColProses.setAttribute('class', 'd-none');
                divColProses.innerHTML = `
                    <label class="form-label"><small>Proses</small></label>
                    <select class="form-control select2bs4" name="com_proses[${index}]" id="com_proses_${index}"
                        data-index="${index}" onchange="changeComplementTujuan(this)">
                        ${prosesNonSecondaryOptions}
                    </select>
                `;

                // --- 6. Urutan ---
                let divColUrutan = document.createElement('div');
                divColUrutan.setAttribute('class', 'col');
                divColUrutan.classList.add('d-none');
                divColUrutan.innerHTML = `
                    <label class="form-label"><small>Urutan</small></label>
                    <input type="text" class="form-control" id="com_urutan_${index}" name="com_urutan[${index}]">
                `;

                // --- 7. Proses Secondary Luar ---
                let divColProsesLuar = document.createElement('div');
                divColProsesLuar.setAttribute('class', 'col');
                divColProsesLuar.classList.add('d-none');
                divColProsesLuar.innerHTML = `
                    <label class="form-label"><small>Proses Secondary Luar</small></label>
                    <select class="form-control select2bs4" name="com_proses_secondary_luar[${index}]"
                        id="com_proses_secondary_luar_${index}" data-index="${index}" onchange="changeComplementTujuan(this)">
                        ${prosesSecondaryOptions}
                    </select>
                `;

                // Append all to the row
                divRow.appendChild(divColPart);
                divRow.appendChild(divColFromPanel);
                divRow.appendChild(divColFromPart);
                divRow.appendChild(divColTujuan);
                divRow.appendChild(divColProses);
                divRow.appendChild(divColUrutan);
                divRow.appendChild(divColProsesLuar);

                // Append row to complement part section
                document.getElementById('complement-parts-section').appendChild(divRow);

                // Initialize select2
                $(`#com_part_details_${index}`).select2({ theme: 'bootstrap4' });
                $(`#com_from_panel_id_${index}`).select2({ theme: 'bootstrap4' });
                $(`#com_from_part_id_${index}`).select2({ theme: 'bootstrap4' });
                $(`#com_tujuan_${index}`).select2({ theme: 'bootstrap4' });
                $(`#com_proses_${index}`).select2({ theme: 'bootstrap4' });
                $(`#com_proses_secondary_luar_${index}`).select2({ theme: 'bootstrap4' });

                // Increment counter
                jumlahComplementPartDetail.value++;

                updateComplementPanelList(index);
            } else {
                Swal.fire({
                    title: "Warning",
                    text: "Harap pilih No. WS",
                    icon: "warning"
                });
            }
        }

        function orderNonSecondary(element, index) {
            const orderShow = document.getElementById(`urutan_show_${index}`);
            const order = document.getElementById(`urutan_${index}`);

            if (order && orderShow) {
                orderShow.innerHTML = '';

                const selectedValue = element.value;

                // Add to list
                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.dataset.value = selectedValue;
                li.textContent = selectedValue;
                li.draggable = true;
                orderShow.appendChild(li);

                order.value = selectedValue;
                console.log(`Select ${index} current values:`, selectedValue);
            }
        }

        function orderSecondary(element, index) {
            const orderShow = document.getElementById(`urutan_show_${index}`);
            const order = document.getElementById(`urutan_${index}`);

            // remove the default placeholder if it exists
            const placeholder = orderShow.querySelector('li');
            if (placeholder && placeholder.textContent.trim() === '-') {
                placeholder.remove();
            }

            let currentValues = order.value ? order.value.split(',') : [];

            const selectedValues = Array.from(element.selectedOptions).map(opt => opt.value);
            const selectedTexts = Array.from(element.selectedOptions).map(opt => opt.textContent);

            const newlySelected = selectedValues.filter(v => !currentValues.includes(v));
            const deselected = currentValues.filter(v => !selectedValues.includes(v));

            // Add new selections
            newlySelected.forEach(value => {
                const optionText = selectedTexts[selectedValues.indexOf(value)];
                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.dataset.value = value;
                li.textContent = optionText;
                li.draggable = true;
                orderShow.appendChild(li);
                currentValues.push(value);
            });

            // Remove deselected items
            deselected.forEach(value => {
                currentValues = currentValues.filter(v => v !== value);
                const liToRemove = orderShow.querySelector(`li[data-value="${value}"]`);
                if (liToRemove) liToRemove.remove();
            });

            order.value = currentValues.join(',');
            console.log(`Select ${index} current values:`, currentValues);
        }

        function clearSelectOptions(index) {
            // get select, list, hidden input based on index
            const select = document.getElementById(`secondaries_${index}`);
            const list = document.getElementById(`urutan_show_${index}`);
            const hidden = document.getElementById(`urutan_${index}`);

            if (!select || !list || !hidden) return;

            // 1️⃣ Deselect all options
            Array.from(select.options).forEach(option => option.selected = false);

            // 2️⃣ Clear the list
            list.innerHTML = '';

            // 3️⃣ Reset hidden input
            hidden.value = '';

            // 4️⃣ Restore placeholder
            const li = document.createElement('li');
            li.className = 'list-group-item';
            li.textContent = '-';
            list.appendChild(li);

            $(`#secondaries_${index}`).val(null).trigger("change");

            console.log(`Cleared select and list for index ${index}`);
        }

        // Prevent Form Submit When Pressing Enter
        document.getElementById("store-part").onkeypress = function(e) {
            var key = e.charCode || e.keyCode || 0;
            if (key == 13) {
                e.preventDefault();
            }
        }

        // Submit Part Form
        function submitPartForm(e, evt) {
            document.getElementById("loading").classList.remove("d-none");

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
                    document.getElementById("loading").classList.add("d-none");
                    document.getElementById('submit-button').removeAttribute('disabled');

                    // Success Response

                    if (res.status == 200) {
                        // When Actually Success :

                        // Prepare a placeholder tab only if needed
                        let newTab = null;
                        if (res.redirect && res.redirect !== '' && res.redirect !== 'reload') {
                            newTab = window.open('', '_blank'); // open synchronously
                        }

                        // Reset This Form
                        e.reset();

                        // Success Alert
                        Swal.fire({
                            icon: 'success',
                            title: 'Data Part Berhasil disimpan',
                            text: res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        }).then(() => {
                            if (res.redirect) {
                                if (res.redirect === 'reload') {
                                    location.reload();
                                } else if (newTab) {
                                    // if a new tab was opened earlier, fill it
                                    newTab.location.href = res.redirect;
                                    // then reload the current page
                                    location.reload();
                                } else {
                                    // fallback: just redirect in the same tab
                                    location.href = res.redirect;
                                }
                            }
                        });
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
                    document.getElementById("loading").classList.add("d-none");
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

        // Reset Step
        async function resetStep() {
            $('#part_details').val(null).trigger('change');
            await $("#ws_id").val(null).trigger("change");
            await $("#panel_id").val(null).trigger("change");
            await $("#panel_id").prop("disabled", true);
        }
    </script>
@endsection
