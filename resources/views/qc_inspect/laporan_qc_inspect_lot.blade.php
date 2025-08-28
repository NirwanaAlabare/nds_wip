@extends('layouts.index')

@section('custom-link')
    <style>
        #pagination {
            text-align: right;
        }

        /* Container that allows horizontal scrolling */
        .table-container {
            width: 100%;
            overflow-x: auto;
            border: 1px solid #ccc;
        }

        /* Base table layout */
        table {
            border-collapse: collapse;
            border-spacing: 0;
            width: 100%;
            min-width: 900px;
            /* or larger depending on your content */
        }

        /* All table cells */
        th,
        td {
            border: 1px solid #ccc;
            padding: 8px 12px;
            white-space: nowrap;
            background: white;
            /* Prevent overlap visibility */
            box-sizing: border-box;
        }

        /* Shared sticky column styles */
        .sticky-col {
            position: sticky;
            background: white !important;
            /* Visually hides content behind */
            border-right: 2px solid #ddd;
            /* Optional separator */
            z-index: 2;
            /* Base z-index for body cells */
            white-space: nowrap;
            box-sizing: border-box;
        }

        /* Patch for border line flicker */
        .sticky-col::after {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 2px;
            /* match your border-right */
            height: 100%;
            background: white;
            z-index: 5;
        }

        /* Sticky header cells */
        th.sticky-col {
            z-index: 4;
            /* Make sure header sits on top */
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Report Inspection Lot</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('qc_inspect_laporan_lot') }}">
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
                            <th scope="col" class="text-center align-middle sticky-col sticky-col-1" rowspan="3"
                                style="color: black;">Tgl
                                Bpb</th>
                            <th scope="col" class="text-center align-middle sticky-col sticky-col-2" rowspan="3"
                                style="color: black;" rowspan="3">No PL</th>
                            <th scope="col" class="text-center align-middle sticky-col sticky-col-3" rowspan="3"
                                style="color: black;" rowspan="3">Supplier</th>
                            <th scope="col" class="text-center align-middle sticky-col sticky-col-4" rowspan="3"
                                style="color: black;" rowspan="3">Worksheet</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Buyer</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Style</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">ID Item</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Fabric</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Color</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Lot</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Jml Roll</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">% Inspect</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Jml Roll Inspect</th>
                            <th scope="col" class="text-center align-middle" rowspan="3">Group Inspect</th>

                            <!-- Baris 1 untuk Width -->
                            <th scope="col" colspan="2" class="text-center align-middle">Width</th>

                            <!-- Baris 1 untuk Length -->
                            <th scope="col" colspan="2" class="text-center align-middle">Length</th>

                            <!-- Baris 1 untuk 4 Point System -->
                            <th scope="col" colspan="2" rowspan="2" class="text-center align-middle">4 Point System
                            </th>

                            <!-- Baris 1 untuk Decision -->
                            <th scope="col" colspan="8" rowspan="2" class="text-center align-middle">Decision
                                Visual Inspection
                            </th>
                        </tr>
                        <tr>
                            <!-- Baris 2 untuk Width -->
                            <th scope="col" class="text-center align-middle">Inch</th>
                            <th scope="col" class="text-center align-middle">CM</th>
                            <!-- Baris 2 untuk Length -->
                            <th scope="col" class="text-center align-middle">Yard</th>
                            <th scope="col" class="text-center align-middle">Meter</th>
                        </tr>

                        <tr>
                            <!-- Baris 3 untuk Width -->
                            <th scope="col" class="text-center align-middle">Actual Average</th>
                            <th scope="col" class="text-center align-middle">Actual Average</th>
                            <!-- Baris 3 untuk Length -->
                            <th scope="col" class="text-center align-middle">Actual Average</th>
                            <th scope="col" class="text-center align-middle">Actual Average</th>
                            <!-- Baris 3 untuk Average Point -->
                            <th scope="col" class="text-center align-middle">Average Point</th>
                            <th scope="col" class="text-center align-middle">Standard</th>
                            <!-- Baris 3 untuk Decision -->
                            <th scope="col" class="text-center align-middle">Grade (Visual Defect Point)</th>
                            <th scope="col" class="text-center align-middle">Founding
                                Issue
                            </th>
                            <th scope="col" class="text-center align-middle">Rate
                                Blanket
                            </th>
                            <th scope="col" class="text-center align-middle">Visual
                                Defect Result
                            </th>
                            <th scope="col" class="text-center align-middle">Blanket Result
                            </th>
                            <th scope="col" class="text-center align-middle">Final Result
                            </th>
                            <th scope="col" class="text-center align-middle">Defect Found
                            </th>
                            <th scope="col" class="text-center align-middle">Est Reject Panel
                            </th>
                        </tr>

                    </thead>

                    <tbody>
                        @foreach ($data_input as $row)
                            <tr>
                                <td class="sticky-col sticky-col-1">{{ $row->tgl_dok }}</td>
                                <td class="sticky-col sticky-col-2">{{ $row->no_invoice }}</td>
                                <td class="sticky-col sticky-col-3">{{ $row->supplier }}</td>
                                <td class="sticky-col sticky-col-4">{{ $row->kpno }}</td>
                                <td>{{ $row->buyer }}</td>
                                <td>{{ $row->styleno }}</td>
                                <td>{{ $row->id_item }}</td>
                                <td>{{ $row->itemdesc }}</td>
                                <td>{{ $row->color }}</td>
                                <td>{{ $row->no_lot }}</td>
                                <td>{{ $row->jml_roll }}</td>
                                <td>{{ $row->cek_inspect }}</td>
                                <td>{{ $row->jml_form }}</td>
                                <td>{{ $row->group_inspect }}</td>
                                <td>{{ $row->avg_width_inch }}</td>
                                <td>{{ $row->avg_width_cm }}</td>
                                <td>{{ $row->avg_l }}</td>
                                <td>{{ $row->avg_l_meter }}</td>
                                <td>{{ $row->avg_point }}</td>
                                <td>{{ $row->shipment }}</td>
                                <td>{{ $row->grade_visual_defect }}</td>
                                <td>{{ $row->list_founding_issue }}</td>
                                <td>{{ $row->rate }}</td>
                                <td>{{ $row->visual_defect_result }}</td>
                                <td>{{ $row->blanket_result }}</td>
                                <td>{{ $row->final_result }}</td>
                                <td>{{ $row->list_defect }}</td>
                                <td>{{ $row->est_final_reject }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tr id="noDataRow" style="display: none;">
                        <td colspan="28" class="text-center text-muted">
                            No data available, please enter a search button.
                        </td>
                    </tr>
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
                    Swal.showLoading();
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "get",
                url: '{{ route('export_excel_qc_inspect_lot') }}',
                data: {
                    from: from,
                    to: to
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    Swal.close();
                    Swal.fire({
                        title: 'Data Sudah Di Export!',
                        icon: "success",
                        showConfirmButton: true,
                        allowOutsideClick: false
                    });

                    var blob = new Blob([response]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Laporan QC Inspect Lot " + from + " sampai " + to + ".xlsx";
                    link.click();
                },
                error: function(xhr, status, error) {
                    Swal.close();
                    Swal.fire({
                        title: 'Gagal Mengekspor Data',
                        text: 'Terjadi kesalahan saat mengekspor. Silakan coba lagi.',
                        icon: 'error',
                        showConfirmButton: true,
                        allowOutsideClick: false
                    });
                    console.error("Export error:", status, error); // For debugging
                }
            });
        }
    </script>
    <script>
        window.addEventListener('load', () => {
            const stickyCols = document.querySelectorAll('th.sticky-col, td.sticky-col');

            // Extract unique sticky column indexes
            const colIndexes = [...new Set(
                Array.from(stickyCols)
                .map(el => {
                    const className = Array.from(el.classList).find(c => c.startsWith('sticky-col-'));
                    if (className) {
                        return parseInt(className.split('-')[2]);
                    }
                    return null;
                })
                .filter(i => i !== null)
            )].sort((a, b) => a - b);

            const colLefts = {};
            let cumulativeWidth = 0;

            colIndexes.forEach(idx => {
                // Find first cell of this column (can be th or td)
                const firstCell = document.querySelector(`.sticky-col-${idx}`);
                if (firstCell) {
                    const width = firstCell.offsetWidth;

                    // Store cumulative left position
                    colLefts[idx] = cumulativeWidth;

                    // Set width for all cells in this column to ensure consistency
                    const allCells = document.querySelectorAll(`.sticky-col-${idx}`);
                    allCells.forEach(cell => {
                        cell.style.width = width + 'px';
                        cell.style.minWidth = width + 'px';
                        cell.style.maxWidth = width + 'px';
                        cell.style.boxSizing = 'border-box'; // Important
                    });

                    cumulativeWidth += width;
                }
            });

            // Apply calculated left positions
            stickyCols.forEach(el => {
                const className = Array.from(el.classList).find(c => c.startsWith('sticky-col-'));
                if (className) {
                    const idx = parseInt(className.split('-')[2]);
                    if (colLefts[idx] !== undefined) {
                        el.style.left = colLefts[idx] + 'px';
                    }
                }
            });
        });


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

                const noDataRow = document.getElementById("noDataRow");

                // Make sure currentPage is not out of bounds
                const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
                if (currentPage > totalPages) currentPage = 1;

                // Hide all rows except the noDataRow
                allRows.forEach(row => {
                    if (row.id !== "noDataRow") {
                        row.style.display = "none";
                    }
                });

                const start = (currentPage - 1) * rowsPerPage;
                const end = start + rowsPerPage;

                if (filteredRows.length === 0) {
                    // Show placeholder row if no data found
                    noDataRow.style.display = "";
                } else {
                    noDataRow.style.display = "none";

                    filteredRows.slice(start, end).forEach(row => {
                        row.style.display = "";
                    });
                }

                renderPagination(filteredRows.length);
                renderTableInfo(filteredRows.length, filteredRows.length === 0 ? 0 : start + 1, Math.min(end,
                    filteredRows.length));
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
