@extends('layouts.index')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title text-sb text-center fw-bold my-3">Line Dashboard List</h3>
                @livewire('sewing.line-dashboard-list')
            </div>
        </div>
    </div>
@endsection
