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
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-search"></i> Piping Stock Detail</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Worksheet</label>
                    <input type="text" class="form-control" id="act_costing_ws" name="act_costing_ws" value="{{ $piping->act_costing_ws }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Style</label>
                    <input type="text" class="form-control" id="style" name="style" value="{{ $piping->style }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Color</label>
                    <input type="text" class="form-control" id="color" name="color" value="{{ $color }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Part</label>
                    <input type="text" class="form-control" id="part" name="part" value="{{ $piping->part }}" readonly>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover table w-100">
                    <thead>
                        <tr>
                            <th>ID Piping</th>
                            <th>ID Roll</th>
                            <th>Group</th>
                            <th>Lot</th>
                            <th>Lebar Roll</th>
                            <th>Panjang Roll</th>
                            <th>Qty Roll</th>
                            <th>Output PCS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $pipingProcesses = $piping->pipingProcesses->where("color", $color);

                            $totalLebar = 0;
                            $totalPanjang = 0;
                            $totalQty = 0;
                            $totalOutput = 0;
                        @endphp
                        @foreach ($pipingProcesses as $pipingProcess)
                            @php
                                $totalLebar += floatval($pipingProcess->lebar_roll_piping);
                                $totalPanjang += floatval($pipingProcess->panjang_roll_piping);
                                $totalQty += intval($pipingProcess->output_total_roll);
                                $totalOutput += intval($pipingProcess->output_total_roll * $pipingProcess->estimasi_output_roll);
                            @endphp
                            <tr>
                                <td>{{ $pipingProcess->kode_piping }}</td>
                                <td>{{ $pipingProcess->pipingProcessDetails->implode("id_roll", ", ") }}</td>
                                <td>{{ $pipingProcess->group }}</td>
                                <td>{{ $pipingProcess->lot }}</td>
                                <td>{{ $pipingProcess->lebar_roll_piping." ".$pipingProcess->lebar_roll_piping_unit }}</td>
                                <td>{{ $pipingProcess->panjang_roll_piping." ".$pipingProcess->panjang_roll_piping_unit }}</td>
                                <td>{{ $pipingProcess->output_total_roll." ".$pipingProcess->output_total_roll_unit }}</td>
                                <td>{{ ($pipingProcess->output_total_roll * $pipingProcess->estimasi_output_roll)." ".$pipingProcess->estimasi_output_roll_unit }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4">Total</th>
                            <th>{{ $totalLebar." ".($pipingProcesses->unique("lebar_roll_piping_unit")->implode("lebar_roll_piping_unit", ",")) }}</th>
                            <th>{{ $totalPanjang." ".($pipingProcesses->unique("panjang_roll_piping_unit")->implode("panjang_roll_piping_unit", ",")) }}</th>
                            <th>{{ $totalQty." ".($pipingProcesses->unique("output_total_roll_unit")->implode("output_total_roll_unit", ",")) }}</th>
                            <th>{{ $totalOutput." ".($pipingProcesses->unique("estimasi_output_roll_unit")->implode("estimasi_output_roll_unit", ",")) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
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
        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#editMejaModal")
        })

        $("#datatable").DataTable();
    </script>
@endsection
