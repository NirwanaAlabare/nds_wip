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

<!-- Navbar -->
<nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
    <div class="container">
        <a href="{{ $page != '' ? route($page) : '#' }}" class="navbar-brand">
            <img src="{{ asset('dist/img/logo-icon.png') }}" alt="nds Logo" class="brand-image">
        </a>

        <button class="navbar-toggler order-1" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
            aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse order-3" id="navbarCollapse">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                {{-- <li class="nav-item">
                    <a href="/marker" class="nav-link {{ $page == 'marker' ? 'active' : '' }}">Marker</a>
                </li> --}}
                @if ($page == 'dashboard-cutting')
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'proses-cutting' ? 'active' : '' }}">Proses</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @marker
                                <li>
                                    <a href="{{ route('marker') }}"
                                        class="dropdown-item {{ $subPage == 'marker' ? 'active' : '' }}">
                                        Marker <i class="fas fa-marker fa-sm"></i>
                                    </a>
                                </li>
                            @endmarker

                            @spreading
                                <li>
                                    <a href="{{ route('spreading') }}"
                                        class="dropdown-item {{ $subPage == 'spreading' ? 'active' : '' }}">
                                        Spreading <i class="fas fa-scroll fa-sm"></i>
                                    </a>
                                </li>
                            @endspreading

                            @meja
                                <li>
                                    <a href="{{ route('form-cut-input') }}"
                                        class="dropdown-item {{ $subPage == 'form-cut-input' ? 'active' : '' }}">
                                        Form Cutting <i class="fas fa-cut fa-sm"></i>
                                    </a>
                                </li>
                            @endmeja

                            {{-- @admin
                                <li>
                                    <a href="{{ route('cut-plan') }}" class="dropdown-item">
                                        Cutting Plan
                                    </a>
                                </li>
                            @endadmin --}}
                        </ul>
                    </li>

                    @admin
                        <li class="nav-item dropdown">
                            <a id="dropdownSubMenu2" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPageGroup == 'cuttingplan-cutting' ? 'active' : '' }}">Cutting
                                Plan</a>
                            <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('cut-plan') }}"
                                        class="dropdown-item {{ $subPage == 'cut-plan' ? 'active' : '' }}">
                                        Cutting Plan <i class="fas fa-map fa-sm"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a id="dropdownSubMenu2" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPageGroup == 'laporan-cutting' ? 'active' : '' }}">Laporan</a>
                            <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('lap_pemakaian') }}"
                                        class="dropdown-item {{ $subPage == 'lap-pemakaian' ? 'active' : '' }}">
                                        Laporan Pemakaian <i class="fas fa-file-alt fa-sm"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endadmin

                    {{-- @manager
                        <li class="nav-item">
                            <a href="{{ route('manage-cutting') }}" class="nav-link">
                                Generate <i class="fas fa-file-archive fa-sm"></i>
                            </a>
                        </li>
                    @endmanager --}}

                    @admin
                        <li class="nav-item {{ $subPage == 'summary-cutting' ? 'active' : '' }}">
                            <a href="{{ route('summary') }}" class="nav-link">
                                Summary <i class="fas fa-tasks fa-sm"></i>
                            </a>
                        </li>
                    @endadmin
                @endif

                @if ($page == 'dashboard-stocker')
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'master-stocker' ? 'active' : '' }}">Master</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @stocker
                                {{-- <li>
                                    <a href="{{ route('part') }}" class="dropdown-item">
                                        Part
                                    </a>
                                </li> --}}
                                <li>
                                    <a href="{{ route('master-part') }}"
                                        class="dropdown-item {{ $subPage == 'master-part' ? 'active' : '' }}">
                                        Master Part <i class="fas fa-plus-square fa-sm"></i>
                                    </a>
                                </li>
                            @endstocker
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'proses-stocker' ? 'active' : '' }}">Proses</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @stocker
                                {{-- <li>
                                    <a href="{{ route('part') }}" class="dropdown-item">
                                        Part
                                    </a>
                                </li> --}}
                                <li>
                                    <a href="{{ route('part') }}"
                                        class="dropdown-item {{ $subPage == 'part' ? 'active' : '' }}">
                                        Part <i class="fas fa-th fa-sm"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stocker') }}"
                                        class="dropdown-item {{ $subPage == 'stocker' ? 'active' : '' }}">
                                        Stocker <i class="fas fa-ticket-alt"></i>
                                    </a>
                                </li>
                            @endstocker
                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard-dc')
                    {{-- <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false" class="nav-link dropdown-toggle">Proses</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @dc
                                <li>
                                    <a href="{{ route('stocker') }}" class="dropdown-item">
                                        Stocker <i class="fas fa-receipt fa-sm"></i>
                                    </a>
                                </li>
                            @enddc
                        </ul>
                    </li> --}}
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'dcin-dc' ? 'active' : '' }}">DC In</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @dc
                                <li>
                                    <a href="{{ route('dc-in') }}"
                                        class="dropdown-item {{ $subPage == 'dc-in' ? 'active' : '' }}">
                                        DC In <i class="fas fa-qrcode fa-sm"></i>
                                    </a>
                                </li>
                            @enddc
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'secondary-dc' ? 'active' : '' }}">Secondary</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @dc
                                <li>
                                    <a href="{{ route('secondary-inhouse') }}"
                                        class="dropdown-item {{ $subPage == 'secondary-inhouse' ? 'active' : '' }}">
                                        Secondary Inhouse <i class="fas fa-house-user"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('secondary-in') }}"
                                        class="dropdown-item {{ $subPage == 'secondary-in' ? 'active' : '' }}">
                                        Secondary In <i class="fas fa-receipt fa-sm"></i>
                                    </a>
                                </li>
                            @enddc
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false" class="nav-link dropdown-toggle {{ $subPageGroup == 'rak-dc' ? 'active' : '' }}">Rak</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @dc
                                <li>
                                    <a href="{{ route('rack') }}"
                                        class="dropdown-item {{ $subPage == 'rack' ? 'active' : '' }}">
                                        Master Rak <i class="fas fa-plus-square fa-sm"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock-rack') }}"
                                        class="dropdown-item {{ $subPage == 'stock-rack' ? 'active' : '' }}">
                                        Rak <i class="fas fa-table fa-sm"></i>
                                    </a>
                                </li>
                            @enddc
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'stok-dc' ? 'active' : '' }}">Stok
                            DC</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @dc
                                <li>
                                    <a href="{{ route('stocker') }}"
                                        class="dropdown-item {{ $subPage == '-' ? 'active' : '' }}">
                                        Stocker <i class="fas fa-receipt fa-sm"></i>
                                    </a>
                                </li>
                            @enddc
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'trolley-dc' ? 'active' : '' }}">Trolley</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @dc
                                <li>
                                    <a href="{{ route('trolley') }}" class="dropdown-item {{ $subPage == "trolley" ? 'active' : '' }}">
                                        Master Trolley <i class="fas fa-plus-square fa-sm"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock-trolley') }}" class="dropdown-item {{ $subPage == "stock-trolley" ? 'active' : '' }}">
                                        Trolley <i class="fas fa-dolly-flatbed"></i>
                                    </a>
                                </li>
                            @enddc
                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard-mut-karyawan')
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'proses-karyawan' ? 'active' : '' }}">Proses</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @hr
                                <li>
                                    <a href="{{ route('mut-karyawan') }}"
                                        class="dropdown-item {{ $subPage == 'mut-karyawan' ? 'active' : '' }}">
                                        Mutasi Karyawan <i class="fas fa-users-cog fa-sm"></i>
                                    </a>
                                </li>
                            @endhr
                        </ul>
                    </li>
                @endif

                {{-- <li class="dropdown-divider"></li> --}}
                <!-- Level two dropdown-->
                {{-- <li class="dropdown-submenu dropdown-hover">
                            <a id="dropdownSubMenu2" href="#" role="button" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false" class="dropdown-item dropdown-toggle">Hover
                                for action</a>
                            <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a tabindex="-1" href="#" class="dropdown-item">level 2</a>
                                </li>

                                <!-- Level three dropdown-->
                                <li class="dropdown-submenu">
                                    <a id="dropdownSubMenu3" href="#" role="button" data-bs-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false"
                                        class="dropdown-item dropdown-toggle">level 2</a>
                                    <ul aria-labelledby="dropdownSubMenu3" class="dropdown-menu border-0 shadow">
                                        <li><a href="#" class="dropdown-item">3rd level</a></li>
                                        <li><a href="#" class="dropdown-item">3rd level</a></li>
                                    </ul>
                                </li>
                                <!-- End Level three -->

                                <li><a href="#" class="dropdown-item">level 2</a></li>
                                <li><a href="#" class="dropdown-item">level 2</a></li>
                            </ul>
                        </li> --}}
                <!-- End Level two -->

                <li class="nav-item">
                    <a href="{{ route('home') }}/" class="nav-link">
                        <i class="fas fa-home"></i>
                    </a>
                </li>
            </ul>

            <!-- SEARCH FORM -->
            {{-- <form class="form-inline ml-0 ml-md-3">
            <div class="input-group input-group-sm">
                <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                    <i class="fas fa-search"></i>
                </button>
                </div>
            </div>
            </form> --}}
        </div>

        <!-- Right navbar links -->
        <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
            {{-- <!-- Messages Dropdown Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#">
                    <i class="fas fa-comments"></i>
                    <span class="badge badge-danger navbar-badge">3</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <a href="#" class="dropdown-item">
                        <!-- Message Start -->
                        <div class="media">
                            <img src="{{ asset('dist/img/user1-128x128.jpg') }}" alt="User Avatar" class="img-size-50 mr-3 img-circle">
                            <div class="media-body">
                                <h3 class="dropdown-item-title">
                                    Brad Diesel
                                    <span class="float-end text-sm text-danger"><i class="fas fa-star"></i></span>
                                </h3>
                                <p class="text-sm">Call me whenever you can...</p>
                                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                            </div>
                        </div>
                        <!-- Message End -->
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <!-- Message Start -->
                        <div class="media">
                            <img src="{{ asset('dist/img/user8-128x128.jpg') }}" alt="User Avatar" class="img-size-50 img-circle mr-3">
                            <div class="media-body">
                                <h3 class="dropdown-item-title">
                                    John Pierce
                                    <span class="float-end text-sm text-muted"><i class="fas fa-star"></i></span>
                                </h3>
                                <p class="text-sm">I got your message bro</p>
                                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                            </div>
                        </div>
                        <!-- Message End -->
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <!-- Message Start -->
                        <div class="media">
                            <img src="{{ asset('dist/img/user3-128x128.jpg') }}" alt="User Avatar" class="img-size-50 img-circle mr-3">
                            <div class="media-body">
                                <h3 class="dropdown-item-title">
                                    Nora Silvester
                                    <span class="float-end text-sm text-warning"><i class="fas fa-star"></i></span>
                                </h3>
                                <p class="text-sm">The subject goes here</p>
                                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                            </div>
                        </div>
                        <!-- Message End -->
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
                </div>
            </li>
            <!-- Notifications Dropdown Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#">
                    <i class="far fa-bell"></i>
                    <span class="badge badge-warning navbar-badge">15</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <span class="dropdown-header">15 Notifications</span>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-envelope mr-2"></i> 4 new messages
                        <span class="float-end text-muted text-sm">3 mins</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-users mr-2"></i> 8 friend requests
                        <span class="float-end text-muted text-sm">12 hours</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-file mr-2"></i> 3 new reports
                        <span class="float-end text-muted text-sm">2 days</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                </div>
            </li> --}}
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="offcanvas" href="#user-offcanvas" role="button"
                    aria-controls="user-offcanvas">
                    {{ strtoupper(auth()->user()->name) }}
                    <i class="fas fa-user ps-1"></i>
                </a>
            </li>
        </ul>
    </div>
</nav>
<!-- /.navbar -->
