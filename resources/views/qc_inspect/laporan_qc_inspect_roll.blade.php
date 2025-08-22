@extends('layouts.index')

@section('custom-link')
    <style>
        #pagination {
            text-align: right;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Report Inspection Roll</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('qc_inspect_laporan_roll') }}">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div class="mb-3">
                        <label class="form-label"><small><b>Tgl Selesai Awal</b></small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal"
                            value="{{ request('tgl_awal', $tgl_skrg_min_sebulan) }}">

                    </div>
                    <div class="mb-3">
                        <label class="form-label"><small><b>Tgl Selesai Akhir</b></small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                            value="{{ request('tgl_akhir', date('Y-m-d')) }}">
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                    <div class="mb-3">
                        <a onclick="export_excel()" class="btn btn-outline-success position-relative btn-sm">
                            <i class="fas fa-file-excel fa-sm"></i>
                            Export Excel
                        </a>
                    </div>
                </div>
            </form>
            <input type="text" id="tableSearch" class="form-control form-control-sm mb-2" placeholder="Cari...">
            <div class="table-responsive">
                <table id="myTable" class="table table-bordered w-100 text-nowrap">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle" rowspan="3">Tgl Bpb</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Tgl Form</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Tgl Mulai Inspect</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Tgl Selesai Inspect</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Machine</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Inspector</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">NIK</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">No PL</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">No Form</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Supplier</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Worksheet</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Buyer</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Style</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Id Item</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Fabric</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Color</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Lot</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Barcode</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">No Roll</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Inspect Ke</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">% Inspect</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Group Inspect</th>

                            <!-- Baris 1 untuk Weight -->
                            <th scope="col" colspan="4" class="text-center align-middle">Weight</th>

                            <!-- Baris 1 untuk Width -->
                            <th scope="col" colspan="13" class="text-center align-middle">Width</th>

                            <!-- Baris 1 untuk Length -->
                            <th scope="col" colspan="7" class="text-center align-middle">Length</th>

                            <!-- Baris 1 untuk Defect -->
                            {{-- Kolom Defect Type --}}
                            <th scope="col" colspan="{{ $totalDefectCols }}" class="text-center align-middle">
                                Defect Type
                            </th>

                            {{-- Kolom Point Defect --}}
                            <th scope="col" class="text-center align-middle" rowspan="3">Point Defect</th>
                            {{-- Kolom Point System --}}
                            <th scope="col" class="text-center align-middle" colspan="2">4 Point System</th>
                            {{-- Kolom Decision Visual Inspection --}}
                            <th scope="col" class="text-center align-middle" colspan="6">Decision Visual Inspection
                            </th>

                        </tr>

                        <tr>
                            <!-- Baris 2 untuk Weight -->
                            <th scope="col" colspan="2" class="text-center align-middle">Kg</th>
                            <th scope="col" colspan="2" class="text-center align-middle">Lbs</th>
                            <!-- Baris 2 untuk Width -->
                            <th scope="col" colspan="6" class="text-center align-middle">Inch</th>
                            <th scope="col" colspan="6" class="text-center align-middle">CM</th>
                            <th scope="col" rowspan = "2" class="text-center align-middle">Short Roll (%)
                                <!-- Baris 2 untuk Length -->
                            <th scope="col" colspan="3" class="text-center align-middle">Yard</th>
                            <th scope="col" colspan="3" class="text-center align-middle">Meter</th>
                            <th scope="col" rowspan = "2" class="text-center align-middle">Short Roll (%)

                                @foreach ($defects_group_kol as $group)
                            <th scope="col" colspan="{{ $group->tot_kolom }}" class="text-center align-middle">
                                {{ $group->point_defect }}</th>
                            @endforeach
                            </th>
                            {{-- Kolom Point System --}}
                            <th scope="col" rowspan="2" class="text-center align-middle">Point</th>
                            <th scope="col" rowspan="2" class="text-center align-middle">Standard</th>
                            {{-- Kolom Decision Visual Inspection --}}
                            <th scope="col" rowspan="2" class="text-center align-middle">Grade (Visual Defect
                                Point)</th>
                            <th scope="col" rowspan="2" class="text-center align-middle">Founding Issue</th>
                            <th scope="col" rowspan="2" class="text-center align-middle">Visual Defect Result</th>
                            <th scope="col" rowspan="2" class="text-center align-middle">Short Roll Result</th>
                            <th scope="col" rowspan="2" class="text-center align-middle">Founding Issue Result</th>
                            <th scope="col" rowspan="2" class="text-center align-middle">Final Result</th>
                        </tr>

                        <tr>
                            <!-- Baris 3 untuk sub-kolom masing-masing -->
                            <th scope="col" class="text-center align-middle">Bintex</th>
                            <th scope="col" class="text-center align-middle">Actual</th>
                            <th scope="col" class="text-center align-middle">Bintex</th>
                            <th scope="col" class="text-center align-middle">Actual</th>
                            <!-- Baris 3 untuk width inch -->
                            <th scope="col" class="text-center align-middle">Bintex</th>
                            <th scope="col" class="text-center align-middle">Front</th>
                            <th scope="col" class="text-center align-middle">Middle</th>
                            <th scope="col" class="text-center align-middle">Back</th>
                            <th scope="col" class="text-center align-middle">Average</th>
                            <th scope="col" class="text-center align-middle">Shortage</th>
                            <!-- Baris 3 untuk width cm -->
                            <th scope="col" class="text-center align-middle">Bintex</th>
                            <th scope="col" class="text-center align-middle">Front</th>
                            <th scope="col" class="text-center align-middle">Middle</th>
                            <th scope="col" class="text-center align-middle">Back</th>
                            <th scope="col" class="text-center align-middle">Average</th>
                            <th scope="col" class="text-center align-middle">Shortage</th>
                            <!-- Baris 3 untuk length -->
                            <th scope="col" class="text-center align-middle">Bintex</th>
                            <th scope="col" class="text-center align-middle">Actual</th>
                            <th scope="col" class="text-center align-middle">Shortage</th>
                            <th scope="col" class="text-center align-middle">Bintex</th>
                            <th scope="col" class="text-center align-middle">Actual</th>
                            <th scope="col" class="text-center align-middle">Shortage</th>


                            @foreach ($defects as $def)
                                <th scope="col" class="text-center align-middle" rowspan="3"
                                    data-defect-id="{{ $def->id }}">
                                    {{ $def->critical_defect }}
                                </th>
                            @endforeach

                        </tr>

                    </thead>

                    <tbody>
                        @foreach ($data_input as $row)
                            <tr>
                                <td>{{ $row->tgl_dok }}</td>
                                <td>{{ $row->tgl_form }}</td>
                                <td>{{ $row->tgl_start }}</td>
                                <td>{{ $row->tgl_finish }}</td>
                                <td>{{ $row->no_mesin }}</td>
                                <td>{{ $row->operator }}</td>
                                <td>{{ $row->nik }}</td>
                                <td>{{ $row->no_invoice }}</td>
                                <td>{{ $row->no_form }}</td>
                                <td>{{ $row->supplier }}</td>
                                <td>{{ $row->no_ws }}</td>
                                <td>{{ $row->buyer }}</td>
                                <td>{{ $row->styleno }}</td>
                                <td>{{ $row->id_item }}</td>
                                <td>{{ $row->itemdesc }}</td>
                                <td>{{ $row->color }}</td>
                                <td>{{ $row->no_lot }}</td>
                                <td>{{ $row->barcode }}</td>
                                <td>{{ $row->no_roll_buyer }}</td>
                                <td>{{ $row->proses }}</td>
                                <td>{{ $row->cek_inspect }}</td>
                                <td>{{ $row->group_inspect }}</td>

                                <!-- Weight -->
                                <td>{{ $row->w_bintex }}</td>
                                <td>{{ $row->w_act }}</td>
                                <td>{{ $row->w_bintex_lbs }}</td>
                                <td>{{ $row->w_act_lbs }}</td>

                                <!-- Width - Inch -->
                                <td>{{ $row->bintex_width }}</td>
                                <td>{{ $row->front }}</td>
                                <td>{{ $row->middle }}</td>
                                <td>{{ $row->back }}</td>
                                <td>{{ $row->avg_width }}</td>
                                <td>{{ $row->shortage_width }}</td>

                                <!-- Width - CM -->
                                <td>{{ $row->bintex_width_cm }}</td>
                                <td>{{ $row->front_cm }}</td>
                                <td>{{ $row->middle_cm }}</td>
                                <td>{{ $row->back_cm }}</td>
                                <td>{{ $row->avg_width_cm }}</td>
                                <td>{{ $row->shortage_width_cm }}</td>
                                <td>{{ $row->short_roll_percentage_width }}</td>

                                <!-- Length -->
                                <td>{{ $row->bintex_length_act }}</td>
                                <td>{{ $row->act_length_fix }}</td>
                                <td>{{ $row->shortage_length_yard }}</td>
                                <td>{{ $row->bintex_length_meter }}</td>
                                <td>{{ $row->bintex_act_length_meter }}</td>
                                <td>{{ $row->shortage_length_meter }}</td>
                                <td>{{ $row->short_roll_percentage_length }}</td>

                                @foreach ($defects as $def)
                                    <td>
                                        {{ $defectData[$row->no_form][$def->id] ?? 0 }}
                                    </td>
                                @endforeach
                                <!-- Kolom Point Defect -->
                                <td>{{ $row->sum_point_def }}</td>
                                <!-- Kolom Point System -->
                                <td>{{ $row->point_system }}</td>
                                <td>{{ $row->individu }}</td>
                                <!-- Kolom Decision Visual Inspection -->
                                <td>{{ $row->grade }}</td>
                                <td>{{ $row->founding_issue }}</td>
                                <td>{{ $row->result }}</td>
                                <td>{{ $row->short_roll_result }}</td>
                                <td>{{ $row->founding_issue_result }}</td>
                                <td>{{ $row->final_result }}</td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
            <div id="tableInfo" class="mt-2 text-muted"></div>
            <div id="pagination" class="mt-2"></div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        function export_excel() {
            let from = document.getElementById("tgl-awal").value;
            let to = document.getElementById("tgl-akhir").value;

            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "get",
                url: '{{ route('export_excel_qc_inspect_roll') }}',
                data: {
                    from: from,
                    to: to
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    {
                        swal.close();
                        Swal.fire({
                            title: 'Data Sudah Di Export!',
                            icon: "success",
                            showConfirmButton: true,
                            allowOutsideClick: false
                        });
                        var blob = new Blob([response]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Laporan QC Inspect Roll " + from + " sampai " +
                            to + ".xlsx";
                        link.click();

                    }
                },
            });
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const table = document.getElementById("myTable");
            const searchInput = document.getElementById("tableSearch");
            const pagination = document.getElementById("pagination");
            const rowsPerPage = 10;
            let currentPage = 1;

            function getAllRows() {
                return Array.from(table.tBodies[0].rows);
            }

            function getFilteredRows() {
                const query = searchInput.value.trim().toLowerCase();
                const allRows = getAllRows();

                if (query === "") return allRows;

                return allRows.filter(row =>
                    Array.from(row.cells).some(cell =>
                        cell.textContent.toLowerCase().includes(query)
                    )
                );
            }

            function renderTable() {
                const allRows = getAllRows();
                const filteredRows = getFilteredRows();

                // Make sure currentPage is not out of bounds
                const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
                if (currentPage > totalPages) currentPage = 1;

                // Hide all rows
                allRows.forEach(row => row.style.display = "none");

                const start = (currentPage - 1) * rowsPerPage;
                const end = start + rowsPerPage;

                filteredRows.slice(start, end).forEach(row => {
                    row.style.display = "";
                });

                renderPagination(filteredRows.length);
                renderTableInfo(filteredRows.length, start + 1, Math.min(end, filteredRows.length));
            }


            function renderPagination(totalRows) {
                pagination.innerHTML = "";
                const totalPages = Math.ceil(totalRows / rowsPerPage);

                if (totalPages <= 1) return;

                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement("button");
                    btn.textContent = i;
                    btn.className = "btn btn-sm mx-1 " + (i === currentPage ? "btn-primary" : "btn-light");
                    btn.addEventListener("click", () => {
                        currentPage = i;
                        renderTable();
                    });
                    pagination.appendChild(btn);
                }
            }

            function renderTableInfo(totalFiltered, startIndex, endIndex) {
                const infoDiv = document.getElementById("tableInfo");
                infoDiv.textContent = `Showing ${startIndex} to ${endIndex} of ${totalFiltered} entries`;
            }


            // ✅ When user types or deletes
            searchInput.addEventListener("input", () => {
                currentPage = 1; // Always go back to page 1
                renderTable();
            });

            // Initial load
            renderTable();
        });
    </script>
@endsection
