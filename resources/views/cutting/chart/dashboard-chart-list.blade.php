@extends('layouts.index', ["navbar" => false, "containerFluid" => true, "footer" => false])

@section('custom-link')
    <style>
        .meja-list h5 {
            color: var(--sb-color);
            font-weight: 700;
            text-align: end;
        }

        .meja-list .card {
            transition: 0.5s;
        }

        .meja-list:hover .card {
            scale: 1.05;
        }
    </style>
@endsection

@section('content')
    <div class="row row-cols-4">
        @foreach ($listMeja as $meja)
            <div class="col">
                <a href="http://10.10.5.62:8000/nds_wip/public/index.php/dashboard-chart/{{ $meja->username."?tgl_plan=".date("Y-m-d") }}" target="_blank" class="meja-list">
                    <div class="card">
                        <div class="card-body">
                            Dashboard
                            <h5>{{ strtoupper($meja->name) }}</h5>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
        <div class="col">
            <a href="http://10.10.5.62:8000/nds_wip/public/index.php/dashboard-chart" target="_blank" class="meja-list">
                <div class="card">
                    <div class="card-body">
                        Dashboard
                        <h5>SUMMARY</h5>
                    </div>
                </div>
            </a>
        </div>
    </div>
@endsection

@section('custom-script')
@endsection
