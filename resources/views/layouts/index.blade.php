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
        @if (!(isset($navbar)))
            @php
                $navbar = true;
            @endphp
        @endif

        @if ($navbar)
            @include('layouts.navbar')
        @endif

        @include('layouts.offcanvas')

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
                            {{-- <div class="col-sm-6">
                                <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                </ol>
                            </div> --}}
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

            @if (!(isset($footer)))
                @php
                    $footer = true;
                @endphp
            @endif

            @if ($footer)
                <footer class="main-footer mt-3">
                    <strong>
                        <a href="https://nirwanagroup.co.id/en/service/nirwana-alabare-santosa/" class="text-dark" target="_blank">
                            Nirwana Digital Solution
                        </a> &copy; {{ date('Y') }}
                    </strong>
                </footer>
            @endif
        </div>

    </div>

    @include('layouts.script')
</body>
</html>
