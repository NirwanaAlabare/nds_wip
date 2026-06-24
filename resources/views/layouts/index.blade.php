{{-- Page Configurations --}}
    @if (!isset($page))
        @php
            $page = '';
        @endphp
    @endif

    @if (!isset($subPage))
        @php
            $subPage = '';
        @endphp
    @endif

    @if (!isset($subPageGroup))
        @php
            $subPageGroup = '';
        @endphp
    @endif

    @if (!isset($head))
        @php
            $head = '';
        @endphp
    @endif

    @if (!isset($navbar))
        @php
            $navbar = true;
        @endphp
    @endif

    @if (!isset($containerFluid))
        @php
            $containerFluid = false;
        @endphp
    @endif

    @if (!isset($footer))
        @php
            $footer = true;
        @endphp
    @endif
{{-- End of Page Configurations --}}

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('dist/img/tabicon.png') }}">
    <title>{{ preg_match("/generate/i", strtolower(Route::current()->getName())) < 1 ? (ucwords(preg_replace("/-|_/", " ", Route::current()->getName())) ? ucwords(preg_replace("/-|_/", " ", Route::current()->getName())) : "NDS") : "NDS" }}</title>

    @include('layouts.link')

    @yield('custom-link')
</head>

<body class="hold-transition layout-top-nav">
    <div class="wrapper">
        <!-- Navbar -->
        @if ($navbar)
            @include('layouts.navbar', ['page' => $page, 'subPage' => $subPage, 'subPageGroup' => $subPageGroup, 'routeName' => Route::current()->getName()])
        @endif

        <!-- Notifikasi Marquee: Kontrak Mesin Sewa Akan Berakhir (H-2) - khusus modul Asset -->
        @if ($page == 'dashboard-asset')
            <style>
                .notif-marquee-wrap {
                    overflow: hidden;
                    white-space: nowrap;
                    background: #ffc107;
                    color: #000;
                }

                .notif-marquee-text {
                    display: inline-block;
                    padding-left: 100%;
                    animation: notif-marquee-scroll 20s linear infinite;
                    font-weight: bold;
                    padding-top: 6px;
                    padding-bottom: 6px;
                }

                @keyframes notif-marquee-scroll {
                    0% {
                        transform: translateX(0);
                    }

                    100% {
                        transform: translateX(-100%);
                    }
                }
            </style>
            <div class="notif-marquee-wrap d-none" id="marqueeNotifMesinSewa">
                <span class="notif-marquee-text" id="marqueeNotifMesinSewaText"></span>
            </div>
        @endif

        <!-- Offcanvas -->
        @include('layouts.offcanvas')

        <!-- Loading -->
        <div class="loading-container-fullscreen d-none" id="loading">
            <div class="loading-container">
                <div class="loading"></div>
            </div>
        </div>

        <!-- Loading with Background -->
        <div class="loading-container-fullscreen-background d-none" id="loading-bg">
            <div class="loading-container">
                <div class="loading"></div>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper pt-3">
            <!-- Header -->
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
                <div class="{{ $containerFluid ? "container-fluid" : "container" }}">
                    @yield('content')
                </div>
            </div>
        </div>

        <!-- Footer -->
        @if ($footer)
            <footer class="main-footer">
                <strong>
                    <a href="https://nirwanagroup.co.id/en/service/nirwana-alabare-santosa/" class="text-dark" target="_blank">
                        Nirwana Digital Solution
                    </a> &copy; {{ date('Y') }}
                </strong>
            </footer>
        @endif
    </div>

    @include('layouts.script')

    @stack('scripts')
</body>

</html>
