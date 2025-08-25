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
                <button class="btn btn-primary btn-sm" onclick="openImport()"><i class="fa fa-upload"></i> Upload</button>
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
                                <td class="text-nowrap">{{ curr($mp->smv) }}</td>
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
    </script>
@endpush
