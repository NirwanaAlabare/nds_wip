@if (!isset($page))
    @php
        $page = '';
    @endphp
@endif

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>NDS</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('dist/img/tabicon.png') }}">

    @include('layouts.link')

    @yield('custom-link')
</head>

<body class="hold-transition layout-top-nav">
    <div class="wrapper">
        @if (!isset($navbar))
            @php
                $navbar = true;
            @endphp
        @endif

        @if ($navbar)
            @include('layouts.navbar', ['page' => $page])
        @endif

        @include('layouts.offcanvas')

        <div class="loading-container-fullscreen d-none" id="loading">
            <div class="loading-container">
                <div class="loading"></div>
            </div>
        </div>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper pt-3">
            <!-- Content Header (Page header) -->
            @if (isset($title))
                <div class="content-header">
                    <div class="container">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1 class="m-0">{{ ucfirst($title) }}</h1>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Main content -->
            <div class="content">
                <div class="container">
                    @yield('content')
                </div>
            </div>
        </div>

        @if (!isset($footer))
            @php
                $footer = true;
            @endphp
        @endif

        @if ($footer)
            <footer class="main-footer">
                <strong>
                    <a href="https://nirwanagroup.co.id/en/service/nirwana-alabare-santosa/" class="text-dark"
                        target="_blank">
                        Nirwana Digital Solution
                    </a> &copy; {{ date('Y') }}
                </strong>
            </footer>
        @endif
    </div>

    @include('layouts.script')
</body>

</html>
