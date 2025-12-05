<div>
    <div class="loading-container-fullscreen" wire:loading wire:target='search, date, group, filter'>
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <div>
        <div class="d-flex justify-content-between align-items-end">
            <div class="mb-3">
                <label class="mb-1">Tanggal</label>
                <input type="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" wire:model='date' id="date">
            </div>
            <div class="mb-3">
                <button class="btn btn-primary btn-sm" onclick="openImport()"><i class="fa fa-upload"></i> Upload Master Plan</button>
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importExcelTmp"><i class="fa fa-upload"></i> Upload Master Line</a>
            </div>
        </div>
        <div class="d-flex justify-content-end">
            <div class="mb-3">
                <div class="d-flex align-items-center gap-1">
                    <label class="mb-0">Search: </label>
                    <input type="text" class="form-control form-control-sm" wire:model.lazy="search" id="search">
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered w-auto" id="datatable">
                <thead>
                    <tr>
                        <td class="bg-sb text-light fw-bold text-nowrap">Line</td>
                        <td class="bg-sb text-light fw-bold text-nowrap">WS Number</td>
                        <td class="bg-sb text-light fw-bold text-nowrap">Style</td>
                        <td class="bg-sb text-light fw-bold text-nowrap">Style Production</td>
                        <td class="bg-sb text-light fw-bold text-nowrap">Color</td>
                        <td class="bg-sb text-light fw-bold text-nowrap">SMV</td>
                        <td class="bg-sb text-light fw-bold text-nowrap">Jam Kerja</td>
                        <td class="bg-sb text-light fw-bold text-nowrap">Man Power</td>
                        <td class="bg-sb text-light fw-bold text-nowrap">Plan Target</td>
                        <td class="bg-sb text-light fw-bold text-nowrap">Target Efficiency</td>
                        <td class="bg-sb text-light fw-bold text-nowrap">Total Jam</td>
                        <td class="bg-sb text-light fw-bold text-nowrap">Total Target Plan</td>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $currentLine = "";
                    @endphp
                    @if ($masterPlan->count() > 0)
                        @foreach ($masterPlan as $mp)
                            @php
                                $thisLineRow = $lineRow->where("sewing_line", $mp->sewing_line)->first();
                            @endphp
                            <tr>
                                {{-- @if ($currentLine != $mp->sewing_line)
                                @endif --}}
                                <td class="text-nowrap text-center align-middle">
                                    <a href="{{ route('master-plan-detail')."/".$mp->sewing_line."/".$date }}" target="_blank">{{ ucfirst(str_replace("_", " ", $mp->sewing_line)) }}</a>
                                </td>
                                <td class="text-nowrap">{{ $mp->no_ws }}</td>
                                <td class="text-nowrap">{{ $mp->style }}</td>
                                <td class="text-nowrap">{{ $mp->style_production }}</td>
                                <td class="text-nowrap">{{ $mp->color }}</td>
                                <td class="text-nowrap">{{ num($mp->smv,3) }}</td>
                                <td class="text-nowrap">{{ curr($mp->jam_kerja) }}</td>
                                <td class="text-nowrap">{{ num($mp->man_power) }}</td>
                                <td class="text-nowrap">{{ num($mp->plan_target) }}</td>
                                <td class="text-nowrap">{{ curr($mp->target_effy) }} %</td>

                                <td class="text-nowrap text-center align-middle fw-bold {{ round($thisLineRow->total_jam) > 8 || round($thisLineRow->total_jam) < 8 ? "text-danger" : "text-success" }}">{{ $thisLineRow->total_jam }}</td>
                                <td class="text-center align-middle fw-bold text-sb" >{{ $thisLineRow->total_target }}</td>
                                {{-- @if ($currentLine != $mp->sewing_line)
                                @endif --}}
                            </tr>
                            @php
                                $currentLine = $mp->sewing_line;
                            @endphp
                        @endforeach
                    @else
                        <tr>
                            <td class="text-nowrap text-center align-middle"></td>
                            <td class="text-nowrap"></td>
                            <td class="text-nowrap"></td>
                            <td class="text-nowrap"></td>
                            <td class="text-nowrap"></td>
                            <td class="text-nowrap"></td>
                            <td class="text-nowrap"></td>
                            <td class="text-nowrap"></td>
                            <td class="text-nowrap"></td>
                            <td class="text-nowrap"></td>
                            <td class="text-nowrap text-center align-middle fw-bold"></td>
                            <td class="text-center align-middle fw-bold text-sb"></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal fade" id="importExcel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post" action="{{ route('import-master-plan') }}" enctype="multipart/form-data" onsubmit="submitImport(this, event)">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h5 class="modal-title" id="exampleModalLabel">Upload Master Plan</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {{ csrf_field() }}
                        <label class="drop-container" id="dropcontainer">
                            <input type="file" name="file" required="required">
                        </label>
                        <a href="{{ asset('example/contoh-import-master-plan.xlsx') }}" download class="btn btn-sb-secondary btn-sm"><i class="fa fa-solid fa-download"></i> Contoh Excel</a>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Close</button>
                        <button type="submit" class="btn btn-sb toastsDefaultDanger"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="importExcelTmp" tabindex="-1" role="dialog" aria-labelledby="importExcelTmpLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <form method="post" action="{{ route('import-master-line') }}" enctype="multipart/form-data" id="form-import-master-line">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h5 class="modal-title" id="importExcelTmpLabel">Upload Master Line</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {{ csrf_field() }}
                        <label class="drop-container mx-3 my-3" id="dropcontainer">
                            <input type="file" name="file" required="required" id="file-input" onchange="submitImportTmp()">
                        </label>
                        <a href="{{ asset('example/contoh-import-master-line.xlsx') }}" download class="btn btn-sb-secondary btn-sm"><i class="fa fa-solid fa-download"></i> Contoh Excel</a>
                        <div class="table-responsive">
                        <table class="table table-bordered w-100" id="datatable-tmp">
                            <thead>
                                <th>Tanggal Plan</th>
                                <th>Sewing Line</th>
                                <th>WS</th>
                                <th>Color</th>
                                <th>Jam Kerja</th>
                                <th>Man Power</th>
                                <th>Plan Target</th>
                                <th>Target Efficiency</th>
                                <th>Tanggal Input</th>
                                <th>Jam Kerja Awal</th>
                                <th>Chief</th>
                                <th>Leader</th>
                                <th>IE</th>
                                <th>Leader QC</th>
                                <th>Mechanic</th>
                                <th>Technical</th>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Close</button>
                        <button type="button" class="btn btn-sb toastsDefaultDanger" onclick="submitImportedMasterLine()"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            setFixedColumn();
        });

        $("#search").on("change", function () {
            clearFixedColumn();
        });

         $("#date").on("change", function () {
            clearFixedColumn();
        });

        function clearFixedColumn() {
            $('#datatable').DataTable().destroy();

            console.log("clearFixedColumn");
        }

        async function setFixedColumn() {
            setTimeout(function () {
                // Initialize DataTable again
                var datatable = $('#datatable').DataTable({
                    fixedColumns: {
                        start: 1,
                        end: 2
                    },
                    paging: false,
                    ordering: false,
                    searching: false,
                    scrollX: true,
                    serverSide: false,
                    rowsGroup: [
                        0,
                        10,
                        11
                    ]
                });
            }, 500);

            setTimeout(function () {
                $('#datatable').DataTable().columns.adjust();
            }, 1000);
        }

        Livewire.on("clearFixedColumn", () => {
            clearFixedColumn();
        });

        Livewire.on("initFixedColumn", () => {
            setFixedColumn();

            setTimeout(function () {
                $('#datatable').DataTable().columns.adjust();
            }, 1000);
        });

        // Run after any Livewire update
        Livewire.hook('message.processed', () => {
            setTimeout(() => {
                setFixedColumn();
            }, 0);
        });

        function openImport(){
            $('#importExcel').modal('show');
        }

        function submitImport(e, evt) {
            document.getElementById("loading").classList.remove("d-none");

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

                    if (res.status == 200) {
                        console.log(res);

                        e.reset();


                        Swal.fire({
                            icon: 'success',
                            title: 'Data Master Plan berhasil diupload',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        }).then(() => {
                            location.reload();
                        });

                        $('#importExcel').modal('hide');
                    }
                },
                error: function (jqXHR) {
                    document.getElementById("loading").classList.add("d-none");

                    console.error(jqXHR);
                }
            });
        }

        let datatableTmp = $("#datatable-tmp").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            scrollX: true,
            scrollY: "500px",
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('tmp-master-line') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.from = $('#from').val();
                    d.to = $('#to').val();
                },
            },
            columns: [
                {
                    data: "tanggal",
                },
                {
                    data: "sewing_line",
                },
                {
                    data: "ws",
                },
                {
                    data: "color",
                },
                {
                    data: "jam_kerja",
                },
                {
                    data: "man_power",
                },
                {
                    data: "plan_target",
                },
                {
                    data: "target_effy",
                },
                {
                    data: "created_at",
                },
                {
                    data: "jam_kerja_awal",
                },
                {
                    data: "chief_name",
                },
                {
                    data: "leader_name",
                },
                {
                    data: "ie_name",
                },
                {
                    data: "leaderqc_name",
                },
                {
                    data: "mechanic_name",
                },
                {
                    data: "technical_name",
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    className: 'align-middle',
                    render: (data, type, row, meta) => {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <a class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route('destroy-tmp-master-line') }}/`+row['id']+`' onclick='deleteData(this)'>
                                    <i class='fa fa-trash'></i>
                                </a>
                            </div>
                        `;
                    }
                },
                {
                    targets: [8],
                    className: 'align-middle',
                    render: (data, type, row, meta) => {
                        return formatDateTime(data);
                    }
                },
                {
                    targets: "_all",
                    className: "text-nowrap"
                }
            ]
        });

        function datatableTmpReload() {
            datatableTmp.ajax.reload();
        }

        function submitImportTmp() {
            document.getElementById("loading").classList.remove("d-none");

            var formImportMasterLine = document.getElementById("form-import-master-line");

            $.ajax({
                url: formImportMasterLine.getAttribute('action'),
                type: formImportMasterLine.getAttribute('method'),
                data: new FormData(formImportMasterLine),
                processData: false,
                contentType: false,
                success: async function(res) {
                    document.getElementById("loading").classList.add("d-none");

                    if (res.status == 200) {
                        console.log(res);

                        formImportMasterLine.reset();

                        datatableTmpReload();
                    }
                },
                error: function (jqXHR) {
                    document.getElementById("loading").classList.add("d-none");

                    console.error(jqXHR);
                }
            });
        }

        $('#importExcel').on('shown.bs.modal', function () {
            datatableTmp.columns.adjust().draw();
        });

        function submitImportedMasterLine() {
            document.getElementById("loading").classList.remove("d-none");

            var formImportMasterLine = document.getElementById("form-import-master-line");

            $.ajax({
                url: "{{ route('submit-imported-master-line') }}",
                type: "post",
                dataType: "json",
                success: function (response) {
                    document.getElementById("loading").classList.add("d-none");

                    if (response.status == 200) {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil",
                            message: response.message
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Gagal",
                            message: response.message
                        });
                    }

                    datatableReload();
                    datatableTmpReload();
                },
                error: function (jqXHR) {
                    document.getElementById("loading").classList.remove("d-none");

                    console.error(jqXHR);

                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        message: "Terjadi Kesalahan"
                    });

                    datatableReload();
                    datatableTmpReload();
                }
            });
        }
    </script>
@endpush
