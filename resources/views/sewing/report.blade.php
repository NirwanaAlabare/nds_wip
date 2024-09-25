@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                @if ($type == "output")
                    @livewire('report-output')
                @elseif ($type == "production")
                    @livewire('report-production')
                @elseif ($type == "line-user")
                    @livewire('report-line-user')
                @endif
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2').select2({
            theme: 'bootstrap4',
        })

        $('.select2bs4').select2({
            theme: 'bootstrap4',
        })
    </script>
    <script>
        Livewire.on('loadingStart', () => {
            if (document.getElementById('loadingReportProduction')) {
                $('#loadingReportProduction').removeClass('hidden');
            }
        });
    </script>
@endsection
