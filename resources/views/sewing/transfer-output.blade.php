@extends('layouts.index')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="mx-3 my-3">
                <h5 class="card-title fw-bold text-sb text-center">Transfer Output</h5>
            </div>
            <div class="card-body">
                @livewire('transfer-output')
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script>
        Livewire.on('alert', (type, message) => {
            showNotification(type, message);
        })

        Livewire.on('loadingStart', () => {
            if (document.getElementById('loadingMasterPlan')) {
                $('#loadingMasterPlan').removeClass('hidden');
            }
        });
    </script>
@endsection
