@extends('layouts.index', ['title' => 'kanban'])

@section('custom-link')
    <!-- Ekko Lightbox -->
    <link rel="stylesheet" href="/assets/plugins/ekko-lightbox/ekko-lightbox.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="/assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-6">
            <h1>Kanban Board</h1>
        </div>
        <div class="col-sm-6 d-none d-sm-block">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Kanban Board</li>
            </ol>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- overlayScrollbars -->
    <script src="/assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
    <!-- Filterizr-->
    <script src="/assets/plugins/filterizr/jquery.filterizr.min.js"></script>
    <!-- Page specific script -->
    <script>
        $(function () {

        })
    </script>
@endsection
