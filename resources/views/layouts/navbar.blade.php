{{-- Nav Configuration --}}
@if (!isset($page))
    @php
        $page = '';
    @endphp
@endif

@if (!isset($subPageGroup))
    @php
        $subPageGroup = '';
    @endphp
@endif

@if (!isset($subPage))
    @php
        $subPage = '';
    @endphp
@endif
{{-- Nav Configuration --}}

<nav class="main-header navbar navbar-expand-md navbar-light navbar-white sticky-top">
    <div class="container">
        <!-- LEFT SECTION -->
        <a href="{{ $page != '' ? route($page) : '#' }}" class="navbar-brand">
            <img src="{{ asset('dist/img/logo-icon.png') }}" alt="nds Logo" class="brand-image">
        </a>

        <button class="navbar-toggler order-1" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
            aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse order-3" id="navbarCollapse">
            <ul class="navbar-nav">
                @if ($page == 'dashboard-track')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            class="nav-link dropdown-toggle {{ preg_match('/track-ws|track-stocker/', $routeName) ? 'active' : '' }}">Track</a>
                        <ul class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('track-ws') }}"
                                    class="dropdown-item {{ $routeName == 'track-ws' ? 'active' : '' }}">
                                    Worksheet <i class="fa-solid fa-file-invoice"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('track-stocker') }}"
                                    class="dropdown-item {{ $routeName == 'track-stocker' ? 'active' : '' }}">
                                    Stocker <i class="fa-solid fa-receipt"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard-marker')
                    @role('marker')
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                                class="nav-link dropdown-toggle {{ preg_match('/master-part|master-secondary/', $routeName) > 0 ? 'active' : '' }}">Master</a>
                            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('master-part') }}"
                                        class="dropdown-item {{ $routeName == 'master-part' ? 'active' : '' }}">
                                        Master Part <i class="fa-regular fa-square-plus"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('master-secondary') }}"
                                        class="dropdown-item {{ $routeName == 'master-secondary' ? 'active' : '' }}">
                                        Master Secondary <i class="fa-regular fa-square-plus"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                                class="nav-link dropdown-toggle {{ preg_match('/^(part|marker)$/', $routeName) > 0 ? 'active' : '' }}">Process</a>
                            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('part') }}"
                                        class="dropdown-item {{ $routeName == 'part' ? 'active' : '' }}">
                                        Part <i class="fas fa-th fa-sm"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('marker') }}"
                                        class="dropdown-item {{ $routeName == 'marker' ? 'active' : '' }}">
                                        Marker <i class="fas fa-marker fa-sm"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endrole
                @endif

                @if ($page == 'dashboard-cutting')
                    @role('cutting')
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPageGroup == 'proses-cutting' ? 'active' : '' }}">Process</a>
                            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('spreading') }}"
                                        class="dropdown-item {{ $routeName == 'spreading' ? 'active' : '' }}">
                                        Spreading <i class="fas fa-scroll fa-sm"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('form-cut-input') }}"
                                        class="appeared dropdown-item {{ $routeName == 'form-cut-input' ? 'active' : '' }}">
                                        Form Cutting <i class="fas fa-cut fa-sm"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('cutting-piece') }}"
                                        class="dropdown-item {{ $routeName == 'cutting-piece' ? 'active' : '' }}">
                                        Form Pieces <i class="fa-solid fa-shirt"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('form-cut-piping') }}"
                                        class="dropdown-item {{ $routeName == 'form-cut-piping' ? 'active' : '' }}">
                                        Piping <i class="fa-solid fa-paperclip"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        @strictmeja
                            <li class="nav-item dropdown">
                                <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                    class="nav-link dropdown-toggle {{ $subPageGroup == 'cuttingplan-cutting' ? 'active' : '' }}">
                                    Plan
                                </a>
                                <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                                    <li>
                                        <a href="{{ route('cut-plan') }}"
                                            class="dropdown-item {{ $routeName == 'cut-plan' ? 'active' : '' }}">
                                            Cutting Plan Date <i class="fas fa-map fa-sm"></i>
                                        </a>
                                    </li>
                                    {{-- Deprecated --}}
                                    {{--
                                        <li>
                                            <a href="{{ route('cut-plan-output') }}" class="dropdown-item {{ $subPage == 'cut-plan-output' ? 'active' : '' }}">
                                                Cutting Plan Output <i class="fa-solid fa-map-location"></i>
                                            </a>
                                        </li>
                                    --}}
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                    class="nav-link dropdown-toggle {{ $subPage == 'manage-cutting' ? 'active' : '' }}">Completed
                                </a>
                                <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                                    <li>
                                        <a href="{{ route('manage-cutting') }}"
                                            class="dropdown-item {{ $routeName == 'manage-cutting' ? 'active' : '' }}">
                                            Completed Form <i class="fa-solid fa-check-to-slot"></i>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                    class="nav-link dropdown-toggle {{ $subPageGroup == 'laporan-cutting' ? 'active' : '' }}">Roll</a>
                                <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                                    <li>
                                        <a href="{{ route('lap_pemakaian') }}"
                                            class="dropdown-item {{ $subPage == 'lap-pemakaian' ? 'active' : '' }}">
                                            Manajemen Roll <i class="fa-solid fa-toilet-paper fa-sm"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('sisa_kain_roll') }}"
                                            class="dropdown-item {{ $subPage == 'sisa-kain-roll' ? 'active' : '' }}">
                                            Bintex Sisa Kain <i class="fa-solid fa-toilet-paper-slash fa-sm"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('alokasi_fabric_gr_panel') }}"
                                            class="dropdown-item {{ $subPage == 'alokasi-fabric-gr-panel' ? 'active' : '' }}">
                                            Alokasi Fabric GR Panel <i class="fa-solid fa-toilet-paper fa-sm"></i>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                    class="nav-link dropdown-toggle {{ $subPageGroup == 'cutting-report' ? 'active' : '' }}">Report</a>
                                <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                                    <li>
                                        <a href="{{ route('report-cutting') }}"
                                            class="dropdown-item {{ $subPage == 'cutting' ? 'active' : '' }}">
                                            Output Cutting <i class="fa fa-file"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('report-cutting-daily') }}"
                                            class="dropdown-item {{ $subPage == 'cutting-daily' ? 'active' : '' }}">
                                            Output Cutting Daily <i class="fa fa-file"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('track-cutting-output') }}"
                                            class="dropdown-item {{ $subPage == 'cutting-track' ? 'active' : '' }}">
                                            Track Cutting Output <i class="fa fa-file"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('pemakaian-roll') }}"
                                            class="dropdown-item {{ $routeName == 'pemakaian-roll' ? 'active' : '' }}">
                                            Pemakaian Kain <i class="fa fa-file"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('cutting-dashboard-list') }}"
                                            class="dropdown-item {{ $subPage == 'form-cut-piping' ? 'active' : '' }}"
                                            target="_blank">
                                            Dashboard <i class="fa-solid fa-pager"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('report_cutting_mutasi_fabric') }}"
                                            class="dropdown-item {{ $routeName == 'report_cutting_mutasi_fabric' ? 'active' : '' }}">
                                            Mutasi Fabric <i class="fa fa-list"></i>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endstrictmeja
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPageGroup == 'cutting-piping' ? 'active' : '' }}">Piping</a>
                            <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('master-piping') }}"
                                        class="dropdown-item {{ $routeName == 'master-piping' ? 'active' : '' }}">
                                        Master <i class="fa-solid fa-gear"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('piping-process') }}"
                                        class="dropdown-item {{ $routeName == 'piping-process' ? 'active' : '' }}">
                                        Process <i class="fa-solid fa-ring"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('piping-loading') }}"
                                        class="dropdown-item {{ $routeName == 'piping-loading' ? 'active' : '' }}">
                                        Loading <i class="fa-solid fa-right-to-bracket"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('piping-stock') }}"
                                        class="dropdown-item {{ $routeName == 'piping-stock' ? 'active' : '' }}">
                                        Stock <i class="fa-solid fa-receipt"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPageGroup == 'cutting-reject' ? 'active' : '' }}">Reject</a>
                            <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('cutting-reject') }}"
                                        class="dropdown-item {{ $routeName == 'cutting-reject' ? 'active' : '' }}">
                                        Form <i class="fa-solid fa-file-circle-exclamation"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock-cutting-reject') }}"
                                        class="dropdown-item {{ $routeName == 'stock-cutting-reject' ? 'active' : '' }}">
                                        Stocker <i class="fa-solid fa-receipt"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('form_gr_panel') }}"
                                        class="dropdown-item {{ $routeName == 'form_gr_panel' ? 'active' : '' }}">
                                        Form GR Panel <i class="fa-solid fa-file"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endrole
                    @role('superadmin')
                        <li class="nav-item">
                            <a href="{{ route('cutting-tools') }}"
                                class="nav-link {{ $routeName == 'cutting-tools' ? 'active' : '' }}" target="_blank">
                                <i class="fa-solid fa-toolbox"></i>
                            </a>
                        </li>
                    @endrole
                @endif

                @if ($page == 'dashboard-stocker')
                    @role('stocker')
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPageGroup == 'master-stocker' ? 'active' : '' }}">Master</a>
                            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('master-part') }}"
                                        class="dropdown-item {{ $routeName == 'master-part' ? 'active' : '' }}">
                                        Master Part <i class="fa-regular fa-square-plus"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('master-secondary') }}"
                                        class="dropdown-item {{ $routeName == 'master-secondary' ? 'active' : '' }}">
                                        Master Secondary <i class="fa-regular fa-square-plus"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPageGroup == 'proses-stocker' ? 'active' : '' }}">Process</a>
                            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('stocker-part') }}"
                                        class="dropdown-item {{ $subPage == 'part' ? 'active' : '' }}">
                                        Part <i class="fas fa-th fa-sm"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stocker') }}"
                                        class="dropdown-item {{ $routeName == 'stocker' ? 'active' : '' }}">
                                        Stocker <i class="fa-solid fa-note-sticky"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endrole

                    @role('superadmin')
                        <li class="nav-item">
                            <a href="{{ route('stocker-tools') }}"
                                class="nav-link {{ $routeName == 'stocker-tools' ? 'active' : '' }}" target="_blank">
                                <i class="fa-solid fa-toolbox"></i>
                            </a>
                        </li>
                    @endrole
                @endif

                @if ($page == 'dashboard-dc')
                    @role('dc')
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPageGroup == 'dcin-dc' ? 'active' : '' }}">DC
                                In</a>
                            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('dc-in') }}"
                                        class="dropdown-item {{ $routeName == 'dc-in' ? 'active' : '' }}">
                                        DC In <i class="fas fa-qrcode fa-sm"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPageGroup == 'secondary-dc' ? 'active' : '' }}">Secondary</a>
                            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('secondary-inhouse-in') }}"
                                        class="dropdown-item {{ $routeName == 'secondary-inhouse-in' ? 'active' : '' }}">
                                        In Secondary Dalam <i class="fas fa-house-user"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('secondary-inhouse') }}"
                                        class="dropdown-item {{ $routeName == 'secondary-inhouse' ? 'active' : '' }}">
                                        Out Secondary Dalam <i class="fas fa-house-user"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('secondary-in') }}"
                                        class="dropdown-item {{ $routeName == 'secondary-in' ? 'active' : '' }}">
                                        Secondary In <i class="fas fa-sign-in-alt"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPageGroup == 'rak-dc' ? 'active' : '' }}">Rak</a>
                            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('rack') }}"
                                        class="dropdown-item {{ $routeName == 'rack' ? 'active' : '' }}">
                                        Master Rak <i class="fa-solid fa-plus-square fa-sm"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock-rack') }}"
                                        class="dropdown-item {{ $routeName == 'stock-rack' ? 'active' : '' }}">
                                        Rak <i class="fa-solid fa-table fa-sm"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock-rack-visual') }}"
                                        class="dropdown-item {{ $routeName == 'stock-rack-visual' ? 'active' : '' }}">
                                        Stok Rak <i class="fa-solid fa-th-list"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPageGroup == 'stok-dc' ? 'active' : '' }}">Stok
                                DC</a>
                            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('stock-dc-complete') }}"
                                        class="dropdown-item {{ $routeName == 'stok-dc-complete' ? 'active' : '' }}">
                                        Stocker Complete <i class="fa-solid fa-circle-check"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock-dc-incomplete') }}"
                                        class="dropdown-item {{ $routeName == 'stok-dc-incomplete' ? 'active' : '' }}">
                                        Stocker Incomplete <i class="fa-solid fa-spinner"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock-dc-wip') }}"
                                        class="dropdown-item {{ $routeName == 'stok-dc-wip' ? 'active' : '' }}">
                                        WIP <i class="fa-solid fa-shuffle"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPageGroup == 'trolley-dc' ? 'active' : '' }}">Trolley</a>
                            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('trolley') }}"
                                        class="dropdown-item {{ $routeName == 'trolley' ? 'active' : '' }}">
                                        Master Trolley <i class="fas fa-plus-square fa-sm"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock-trolley') }}"
                                        class="dropdown-item {{ $routeName == 'stock-trolley' ? 'active' : '' }}">
                                        Trolley <i class="fas fa-dolly-flatbed"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPageGroup == 'loading-dc' ? 'active' : '' }}">Loading</a>
                            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('loading-line') }}"
                                        class="dropdown-item {{ $routeName == 'loading-line' ? 'active' : '' }}">
                                        Loading Line <i class="fa-solid fa-users-line"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('summary-loading') }}"
                                        class="dropdown-item {{ $routeName == 'summary-loading' ? 'active' : '' }}">
                                        Summary Loading <i class="fa-solid fa-list-check"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('modify-loading-line') }}"
                                        class="dropdown-item {{ $routeName == 'modify-loading' ? 'active' : '' }}">
                                        Modify Loading <i class="fa-solid fa-sliders"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('loading_out') }}"
                                        class="dropdown-item {{ $subPage == 'loading_out' ? 'active' : '' }}">
                                        Loading Out / WIP Out <i class="fas fa-long-arrow-alt-right"></i>
                                    </a>
                                </li>
                                {{-- Deprecated --}}
                                {{-- <li>
                                    <a href="{{ route('bon-loading-line') }}" class="dropdown-item {{ $subPage == 'bon-loading-line' ? 'active' : '' }}">
                                        Bon Loading <i class="fa-solid fa-ticket-simple"></i>
                                    </a>
                                </li> --}}
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPageGroup == 'stocker-number' ? 'active' : '' }}">Number</a>
                            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('year-sequence') }}"
                                        class="dropdown-item {{ $routeName == 'year-sequence' ? 'active' : '' }}">
                                        Set Year Sequence <i class="fa-solid fa-list-ol"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stocker-list') }}"
                                        class="dropdown-item {{ $routeName == 'stocker-list' ? 'active' : '' }}">
                                        Registration List <i class="fa-solid fa-list-ul"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('modify-year-sequence') }}"
                                        class="dropdown-item {{ $routeName == 'modify-year-sequence' ? 'active' : '' }}">
                                        Modify Year Sequence <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                </li>
                                {{-- Deprecated --}}
                                {{--
                                    <li>
                                        <a href="{{ route('month-count') }}" class="dropdown-item {{ $subPage == 'month-count' ? 'active' : '' }}"> Month Number <i class="fa-solid fa-hashtag"></i></a>
                                    </li>
                                    <li>
                                        <a href="{{ route('stocker-balance') }}" class="dropdown-item {{ $subPage == 'stocker-balance' ? 'active' : '' }}"> Month Number <i class="fa-solid fa-hashtag"></i></a>
                                    </li>
                                 --}}
                            </ul>
                        </li>
                    @endrole
                    @role('superadmin')
                        <li class="nav-item">
                            <a href="{{ route('dc-tools') }}"
                                class="nav-link {{ $routeName == 'dc-tools' ? 'active' : '' }}" target="_blank">
                                <i class="fa-solid fa-toolbox"></i>
                            </a>
                        </li>
                    @endrole
                @endif

                @if ($page == 'dashboard-sewing-eff')
                    @role('sewing')
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPageGroup == 'sewing-master' ? 'active' : '' }}">Master</a>
                            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('master-line') }}"
                                        class="dropdown-item {{ $routeName == 'master-line' ? 'active' : '' }}">
                                        Master Line <i class="fa-solid fa-people-group"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('master-plan') }}"
                                        class="dropdown-item {{ $routeName == 'master-plan' ? 'active' : '' }}">
                                        Master Plan <i class="fa-solid fa-gears"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('master-defect') }}"
                                        class="dropdown-item {{ $routeName == 'master-defect' ? 'active' : '' }}">
                                        Master Defect <i class="fa-solid fa-circle-exclamation"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('sewing-secondary-master') }}"
                                        class="dropdown-item {{ $routeName == 'sewing-secondary-master' ? 'active' : '' }}">
                                        Secondary Sewing Master <i class="fa-solid fa-diagram-project"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPageGroup == 'sewing-sewing' ? 'active' : '' }}">Output</a>
                            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('daily-sewing', ['type' => 'output']) }}"
                                        class="dropdown-item {{ $subPage == 'sewing-output' ? 'active' : '' }}">
                                        Daily Sewing Output <i class="fa-solid fa-calendar-days"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('daily-sewing', ['type' => 'production']) }}"
                                        class="dropdown-item {{ $subPage == 'sewing-production' ? 'active' : '' }}">
                                        Daily Sewing Line Output <i class="fa-solid fa-people-line"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('report-hourly') }}"
                                        class="dropdown-item {{ $routeName == 'report-hourly' ? 'active' : '' }}">
                                        Hourly Output <i class="fa-solid fa-clock fa-sm"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('daily-sewing', ['type' => 'line-user']) }}"
                                        class="dropdown-item {{ $subPage == 'sewing-line-user' ? 'active' : '' }}">
                                        User Line Output <i class="fa-solid fa-user"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('sewing-track-order-output') }}"
                                        class="dropdown-item {{ $subPage == 'sewing-track' ? 'active' : '' }}">
                                        Track Output <i class="fa-solid fa-shuffle"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('undo-output-history') }}"
                                        class="dropdown-item {{ $routeName == 'undo-output-history' ? 'active' : '' }}">
                                        Undo Output History <i class="fa-solid fa-rotate-left"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPageGroup == 'sewing-defect' ? 'active' : '' }}">Defect</a>
                            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('order-defects') }}"
                                        class="dropdown-item {{ $subPage == 'sewing-pareto' ? 'active' : '' }}">
                                        Pareto Chart <i class="fa-solid fa-triangle-exclamation"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('report-defect-in-out') }}"
                                        class="dropdown-item {{ $routeName == 'report-defect-in-out' ? 'active' : '' }}">
                                        Report Defect In Out <i class="fa-solid fa-circle-exclamation"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('report-defect') }}"
                                        class="dropdown-item {{ $routeName == 'report-defect' ? 'active' : '' }}">
                                        Report Defect <i class="fa-solid fa-file-circle-exclamation"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('report-reject') }}"
                                        class="dropdown-item {{ $routeName == 'report-reject' ? 'active' : '' }}">
                                        Report Reject <i class="fa-solid fa-file-circle-xmark"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('defect-map') }}"
                                        class="dropdown-item {{ $routeName == 'defect-map' ? 'active' : '' }}">
                                        Defect Map <i class="fa-solid fa-map-pin"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPageGroup == 'sewing-report' ? 'active' : '' }}">Report</a>
                            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('reportEfficiencynew') }}"
                                        class="dropdown-item {{ $subPage == 'reportEfficiencynew' ? 'active' : '' }}">
                                        Report Efficiency <i class="fa-solid fa-file"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('report_mut_output') }}"
                                        class="dropdown-item {{ $subPage == 'report_mut_output' ? 'active' : '' }}">
                                        Report Mutasi Output <i class="fa-solid fa-file"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('dashboard-chief-sewing-range', [date('Y-m-d', strtotime(date('Y-m-d') . ' -14 days')), date('Y-m-d')]) }}"
                                        class="dropdown-item {{ $routeName == 'dashboard-chief-sewing-range' ? 'active' : '' }}">
                                        Chief Performance <i class="fa-solid fa-file"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('dashboard-leader-sewing', [date('Y-m-d', strtotime(date('Y-m-d') . ' -14 days')), date('Y-m-d')]) }}"
                                        class="dropdown-item {{ $routeName == 'dashboard-leader-sewing' ? 'active' : '' }}">
                                        Line Performance <i class="fa-solid fa-file"></i>
                                    </a>
                                </li>
                                {{-- Deprecated --}}
                                {{--
                                        <li>
                                            <a href="{{ route('reportOutput') }}" class="dropdown-item {{ $subPage == 'reportOutput' ? 'active' : '' }}">
                                                Report Output <i class="fa-solid fa-file"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('reportProduction') }}" class="dropdown-item {{ $subPage == 'reportProduction' ? 'active' : '' }}">
                                                Report Production <i class="fa-solid fa-file"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('reportEfficiency') }}" class="dropdown-item {{ $subPage == 'reportEfficiency' ? 'active' : '' }}">
                                                Report Efficiency <i class="fa-solid fa-file"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('reportDetailOutput') }}" class="dropdown-item {{ $subPage == 'reportDetailOutput' ? 'active' : '' }}">
                                                Report Detail Output <i class="fa-solid fa-file"></i>
                                            </a>
                                        </li>
                                    --}}
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPageGroup == 'sewing-wip' ? 'active' : '' }}">WIP</a>
                            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('line-wip') }}"
                                        class="dropdown-item {{ $subPage == 'line-wip' ? 'active' : '' }}">
                                        Line WIP <i class="fa-solid fa-bars-progress"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('dashboard-wip') }}"
                                class="nav-link {{ $routeName == 'dashboard-wip' ? 'active' : '' }}" target="_blank">
                                Dashboard
                            </a>
                        </li>
                    @endrole
                    @role('superadmin')
                        <li class="nav-item">
                            <a href="{{ route('sewing-tools') }}"
                                class="nav-link {{ $routeName == 'sewing-tools' ? 'active' : '' }}" target="_blank">
                                <i class="fa-solid fa-toolbox"></i>
                            </a>
                        </li>
                    @endrole
                @endif

                @if ($page == 'dashboard-warehouse')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            class="nav-link dropdown-toggle">Master</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('master-lokasi') }}" class="dropdown-item">
                                    Master Lokasi
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('qc-inspect-master-group-inspect') }}" class="dropdown-item">
                                    Master Group Inspect
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('qc-inspect-master-satuan') }}" class="dropdown-item">
                                    Master Satuan
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('qc-inspect-master-lenght') }}" class="dropdown-item">
                                    Master Lenght
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('qc-inspect-master-defect') }}" class="dropdown-item">
                                    Master Defect
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('qc-inspect-master-result') }}" class="dropdown-item">
                                    Master Result
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard-warehouse')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            class="nav-link dropdown-toggle">Penerimaan</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('in-material') }}" class="dropdown-item">
                                    Penerimaan Bahan Baku
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('retur-inmaterial') }}" class="dropdown-item">
                                    Penerimaan Retur Bahan Baku
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('mutasi-lokasi') }}" class="dropdown-item">
                                    Mutasi Lokasi
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard-warehouse')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            class="nav-link dropdown-toggle">Pengeluaran</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('req-material') }}" class="dropdown-item">
                                    Permintaan Bahan Baku
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('out-material') }}" class="dropdown-item">
                                    Pengeluaran Bahan Baku
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('retur-material') }}" class="dropdown-item">
                                    Retur Bahan Baku
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard-warehouse')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            class="nav-link dropdown-toggle">QC</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('qc-pass') }}" class="dropdown-item">
                                    QC Inspect
                                </a>
                                <a href="{{ route('qc-inspect-inmaterial') }}" class="dropdown-item">
                                    Data Penerimaan
                                </a>
                                <a href="{{ route('qc-inspect-inmaterial-header') }}" class="dropdown-item">
                                    QC Inspect 2
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard-warehouse')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            class="nav-link dropdown-toggle">Laporan</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('lap-det-pemasukan') }}" class="dropdown-item">
                                    Pemasukan Detail Item
                                </a>
                                <a href="{{ route('lap-det-pemasukanroll') }}" class="dropdown-item">
                                    Pemasukan Detail Roll
                                </a>
                                <a href="{{ route('lap-det-pengeluaran') }}" class="dropdown-item">
                                    Pengeluaran Detail Item
                                </a>
                                <a href="{{ route('lap-det-pengeluaranroll') }}" class="dropdown-item">
                                    Pengeluaran Detail Roll
                                </a>
                                <a href="{{ route('lap-mutasi-global') }}" class="dropdown-item">
                                    Mutasi Global
                                </a>
                                <a href="{{ route('lap-mutasi-detail') }}" class="dropdown-item">
                                    Mutasi Detail
                                </a>
                                <a href="{{ route('lap-mutasi-barcode') }}" class="dropdown-item">
                                    Mutasi Barcode
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard-warehouse')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            class="nav-link dropdown-toggle">Konfirmasi</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('konfirmasi-pemasukan') }}" class="dropdown-item">
                                    Konfirmasi Pemasukan
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('konfirmasi-pengeluaran') }}" class="dropdown-item">
                                    Konfirmasi Pengeluaran
                                </a>
                            </li>

                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard-warehouse')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            class="nav-link dropdown-toggle">Manage BPB</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('transfer-bpb') }}" class="dropdown-item">
                                    Transfer BPB
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('maintain-bpb') }}" class="dropdown-item">
                                    Reverse BPB
                                </a>
                            </li>

                        </ul>
                    </li>
                @endif

                @if ($page == 'procurement')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            class="nav-link dropdown-toggle">Proses</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('detail-return-sb') }}" class="dropdown-item">
                                    List Return
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if ($page == 'stock_opname')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            class="nav-link dropdown-toggle">Proses</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('list-data-stok') }}" class="dropdown-item">
                                    List Item
                                </a>
                            </li>
                            <!-- <li>
                                <a href="{{ route('data-rak') }}" class="dropdown-item">
                                    Data Item
                                </a>
                            </li> -->
                            <li>
                                <a href="{{ route('list-stok-opname') }}" class="dropdown-item">
                                    Stock Opname
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            class="nav-link dropdown-toggle">Report</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <?php if(auth()->user()->username != 'acc'){ ?>
                            <li>
                                <a href="{{ route('detail-stok-opname') }}" class="dropdown-item">
                                    List Stock Opname
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('laporan-stok-opname') }}" class="dropdown-item">
                                    Report Stock Opname
                                </a>
                            </li>
                            <?php }else{ ?>
                            <li>
                                <a href="{{ route('laporan-stok-opname') }}" class="dropdown-item">
                                    Report Stock Opname
                                </a>
                            </li>
                            <?php } ?>
                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard-fg-stock')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'fgstock-masterlokasi' ? 'active' : '' }}">Master</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('master-lokasi-fg-stock') }}"
                                    class="dropdown-item {{ $subPage == 'fg-stock' ? 'active' : '' }}">
                                    Master Lokasi <i class="fas fa-search-location fa-sm"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('master-sumber-penerimaan') }}"
                                    class="dropdown-item {{ $subPage == 'master-sumber-penerimaan' ? 'active' : '' }}">
                                    Master Sumber Penerimaan <i class="fas fa-indent fa-sm"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('master-tujuan-pengeluaran') }}"
                                    class="dropdown-item {{ $subPage == 'master-tujuan-pengeluaran' ? 'active' : '' }}">
                                    Master Tujuan Pengeluaran <i class="fas fa-outdent fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'fgstock-bpb' ? 'active' : '' }}">Penerimaan</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('bpb-fg-stock') }}"
                                    class="dropdown-item {{ $subPage == 'bpb-fg-stock' ? 'active' : '' }}">
                                    Penerimaan Barang Jadi Stok <i class="fas fa-box fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'fgstock-bppb' ? 'active' : '' }}">Pengeluaran</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('bppb-fg-stock') }}"
                                    class="dropdown-item {{ $subPage == 'bppb-fg-stock' ? 'active' : '' }}">
                                    Pengeluaran Barang Jadi Stok <i class="fas fa-box-open fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'fgstock-mutasi' ? 'active' : '' }}">Mutasi</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('mutasi-fg-stock') }}"
                                    class="dropdown-item {{ $subPage == 'mutasi-fg-stock' ? 'active' : '' }}">
                                    Mutasi Internal <i class="fas fa-exchange-alt"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'fgstock-laporan' ? 'active' : '' }}">Laporan</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('laporan-fg-stock') }}"
                                    class="dropdown-item {{ $subPage == 'laporan-fg-stock' ? 'active' : '' }}">
                                    List Laporan <i class="fas fa-list fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard-packing')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'packing-transfer-garment' ? 'active' : '' }}">Transfer
                            Garment</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('transfer-garment') }}"
                                    class="dropdown-item {{ $subPage == 'transfer-garment' ? 'active' : '' }}">
                                    Transfer Garment <i class="fa-solid fa-right-left fa-sm"></i>
                                </a>
                            </li>
                            {{-- <li>
                                <a href="{{ route('transfer-garment') }}" class="dropdown-item {{ $subPage == 'transfer-garment' ? 'active' : '' }}">
                                    Stok <i class="fa-solid fa-warehouse fa-sm"></i>
                                </a>
                            </li> --}}
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'packing-packing-in' ? 'active' : '' }}">Packing
                            In</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('packing-in') }}"
                                    class="dropdown-item {{ $subPage == 'packing-in' ? 'active' : '' }}">
                                    Packing In <i class="fa-solid fa-people-carry fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'packing-packing-out' ? 'active' : '' }}">Packing
                            Out</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('needle-check') }}"
                                    class="dropdown-item {{ $subPage == 'packing-needle-check' ? 'active' : '' }}">
                                    Needle Checking Scan (Optional)<i class="fa-solid fa-check-double fa-sm"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('packing-out') }}"
                                    class="dropdown-item {{ $subPage == 'packing-out' ? 'active' : '' }}">
                                    Packing Out <i class="fa-solid fa-compress fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'packing-master-karton' ? 'active' : '' }}">Master</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            {{-- <li>
                                <a href="{{ route('master-karton') }}" class="dropdown-item {{ $subPage == 'master-karton' ? 'active' : '' }}">
                                    Master Karton <i class="fa-solid fa-boxes fa-sm"></i>
                                </a>
                            </li> --}}
                            <li>
                                <a href="{{ route('packing-list') }}"
                                    class="dropdown-item {{ $subPage == 'packing-list' ? 'active' : '' }}">
                                    Upload Packing List <i class="fas fa-file-upload fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'packing-report' ? 'active' : '' }}">Report</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('packing_rep_packing_line_sum') }}"
                                    class="dropdown-item {{ $subPage == 'packing_rep_packing_line_sum' ? 'active' : '' }}">
                                    <i class="fas fa-file-upload fa-sm"></i> Summary Packing Line
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('packing_rep_packing_mutasi') }}"
                                    class="dropdown-item {{ $subPage == 'packing_rep_packing_mutasi' ? 'active' : '' }}">
                                    <i class="fas fa-file-upload fa-sm"></i> Mutasi Packing List
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('packing_rep_packing_mutasi_wip') }}"
                                    class="dropdown-item {{ $subPage == 'packing_rep_packing_mutasi_wip' ? 'active' : '' }}">
                                    <i class="fas fa-file-upload fa-sm"></i> Mutasi Packing (WIP)
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'packing-line' ? 'active' : '' }}">Packing
                            Line</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('track-packing-output') }}"
                                    class="dropdown-item {{ $subPage == 'track-packing-output' ? 'active' : '' }}">
                                    <i class="fa-solid fa-shuffle"></i> Track Packing Output
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard_finish_good')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'finish_good_master_lokasi' ? 'active' : '' }}">Master
                        </a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('finish_good_master_lokasi') }}"
                                    class="dropdown-item {{ $subPage == 'finish_good_master_lokasi' ? 'active' : '' }}">
                                    <i class="fas fa-map-marker fa-sm"></i> Master Lokasi
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'finish_good_penerimaan' ? 'active' : '' }}">Penerimaan</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('finish_good_penerimaan') }}"
                                    class="dropdown-item {{ $subPage == 'finish_good_penerimaan' ? 'active' : '' }}">
                                    <i class="fas fa-arrow-circle-left fa-sm" style="color: green;"></i>
                                    Penerimaan Finish Good
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('finish_good_alokasi_karton') }}"
                                    class="dropdown-item {{ $subPage == 'finish_good_alokasi_karton' ? 'active' : '' }}">
                                    <i class="fas fa-boxes-packing fa-sm"></i> Alokasi Karton
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'finish_good_pengeluaran' ? 'active' : '' }}">Pengeluaran</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('finish_good_pengeluaran') }}"
                                    class="dropdown-item {{ $subPage == 'finish_good_pengeluaran' ? 'active' : '' }}">
                                    <i class="fas fa-arrow-circle-right fa-sm" style="color: blue;"></i>
                                    Pengeluaran Finish Good
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('finish_good_retur') }}"
                                    class="dropdown-item {{ $subPage == 'finish_good_retur' ? 'active' : '' }}">
                                    <i class="fas fa-arrow-circle-left fa-sm" style="color:red;"></i>
                                    Retur Finish Good Ekspedisi
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- Document Report --}}
                @if ($page == 'dashboard-report-doc')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'report-doc-laporan' ? 'active' : '' }}">Laporan</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('report-doc-laporan-wip') }}"
                                    class="dropdown-item {{ $subPage == 'report-doc-laporan-wip' ? 'active' : '' }}">
                                    Laporan Saldo WIP
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif


                {{-- QC Inspect Kain --}}
                @if ($page == 'dashboard-qc-inspect')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'qc-inspect-master' ? 'active' : '' }}">Master</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('qc_inspect_master_critical_defect_show') }}"
                                    class="dropdown-item {{ $subPage == 'qc-inspect-master-critical-defect' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Critical Defect
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('qc_inspect_master_founding_issue_show') }}"
                                    class="dropdown-item {{ $subPage == 'qc-inspect-master-founding-issue' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Founding Issue
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'qc-inspect-proses' ? 'active' : '' }}">Proses</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('qc_inspect_proses_packing_list') }}"
                                    class="dropdown-item {{ $subPage == 'qc-inspect-proses-packing-list' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Packing List
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('qc_inspect_proses_form_inspect') }}"
                                    class="dropdown-item {{ $subPage == 'qc-inspect-proses-form-inspect' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Form Inspect
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('qc_inspect_proses_fabric_relaxation') }}"
                                    class="dropdown-item {{ $subPage == 'qc-inspect-proses-fabric-relaxation' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Fabric Relaxation
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('qc_inspect_print_bintex_shade_band') }}"
                                    class="dropdown-item {{ $subPage == 'qc-inspect-proses-print-bintex-shade-band' ? 'active' : '' }}">
                                    <i class="fas fa-print fa-sm"></i> Print Bintex Shade Band
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('qc_inspect_shade_band') }}"
                                    class="dropdown-item {{ $subPage == 'qc-inspect-proses-shade-band' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Shade Band
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'qc-inspect-laporan' ? 'active' : '' }}">Laporan</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('qc_inspect_laporan_roll') }}"
                                    class="dropdown-item {{ $subPage == 'qc-inspect-laporan-roll' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Report Inspection Roll
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('qc_inspect_laporan_lot') }}"
                                    class="dropdown-item {{ $subPage == 'qc-inspect-laporan-lot' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Report Inspection Lot
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('qc_inspect_report_shade_band') }}"
                                    class="dropdown-item {{ $subPage == 'qc-inspect-report-shade-band' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Report Shade Band
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif


                {{-- PPIC --}}
                @if ($page == 'dashboard-ppic')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'ppic-master' ? 'active' : '' }}">Master</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('master-so') }}"
                                    class="dropdown-item {{ $subPage == 'ppic-master-master-so' ? 'active' : '' }}">
                                    Master Sales Order <i class="fa-solid fa-list-ul fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'ppic-laporan' ? 'active' : '' }}">Laporan</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('ppic-laporan-tracking') }}"
                                    class="dropdown-item {{ $subPage == 'ppic-laporan-tracking' ? 'active' : '' }}">
                                    Laporan Tracking <i class="fa-solid fa-list-ul fa-sm"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('ppic_monitoring_order') }}"
                                    class="dropdown-item {{ $subPage == 'ppic_monitoring_order' ? 'active' : '' }}">
                                    Monitoring Order <i class="fa-solid fa-list-ul fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'ppic-monitoring' ? 'active' : '' }}">Monitoring</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('ppic_monitoring_material') }}"
                                    class="dropdown-item {{ $subPage == 'ppic_monitoring_material' ? 'active' : '' }}">
                                    Monitoring Material <i class="fa-solid fa-list-ul fa-sm"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('ppic_monitoring_material_det') }}"
                                    class="dropdown-item {{ $subPage == 'ppic_monitoring_material_det' ? 'active' : '' }}">
                                    Monitoring Material Detail <i class="fa-solid fa-list-ul fa-sm"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('ppic_monitoring_material_sum') }}"
                                    class="dropdown-item {{ $subPage == 'ppic_monitoring_material_sum' ? 'active' : '' }}">
                                    Monitoring Material Summary <i class="fa-solid fa-list-ul fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('barcode-packing') }}"
                            class="nav-link {{ $routeName == 'barcode-packing' ? 'active' : '' }}" target="_blank">
                            Barcode Packing
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'ppic_tools' ? 'active' : '' }}"><i
                                class="fa-solid fa-toolbox"></i></a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('ppic_tools_adj_mut_output') }}"
                                    class="dropdown-item {{ $subPage == 'ppic_tools_adj_mut_output' ? 'active' : '' }}">
                                    Adjustment Mutasi <i class="fa-solid fa-list-ul fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- Machine --}}
                @if ($page == 'dashboard-mut-mesin')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'proses-mut-mesin' ? 'active' : '' }}">Process</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('mut-mesin') }}"
                                    class="dropdown-item {{ $subPage == 'mut-mesin' ? 'active' : '' }}">
                                    <i class="fas fa-tools fa-sm"></i> Mutasi Mesin
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('so_mesin') }}"
                                    class="dropdown-item {{ $subPage == 'create_so_mesin' ? 'active' : '' }}">
                                    <i class="fas fa-list fa-sm"></i> SO Mesin
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'master-mut-mesin' ? 'active' : '' }}">Master</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('master-mut-mesin') }}"
                                    class="dropdown-item {{ $subPage == 'master-mut-mesin' ? 'active' : '' }}">
                                    <i class="fas fa-cogs fa-sm"></i> Master Mesin
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('master_mesin_lokasi') }}"
                                    class="dropdown-item {{ $subPage == 'master_mesin_lokasi' ? 'active' : '' }}">
                                    <i class="fas fa-location-arrow fa-sm"></i> Master Lokasi Mesin
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'lap-mut-mesin' ? 'active' : '' }}">Laporan</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('lap_stok_mesin') }}"
                                    class="dropdown-item {{ $subPage == 'lap_stok_mesin' ? 'active' : '' }}">
                                    <i class="fas fa-list fa-sm"></i> List Stok Mesin
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('lap_stok_detail_mesin') }}"
                                    class="dropdown-item {{ $subPage == 'lap_stok_detail_mesin' ? 'active' : '' }}">
                                    <i class="fas fa-list fa-sm"></i> List Detail Stok Mesin
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- G.A.I.S --}}
                @if ($page == 'dashboard-ga')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'ga-pengajuan' ? 'active' : '' }}">Pengajuan</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('pengajuan-bahan-bakar') }}"
                                    class="dropdown-item {{ $subPage == 'ga-pengajuan-bahan-bakar' ? 'active' : '' }}">
                                    Bahan Bakar <i class="fa-solid fa-gas-pump fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'ga-approval' ? 'active' : '' }}">Approval</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('approval-bahan-bakar') }}"
                                    class="dropdown-item {{ $subPage == 'ga-approval-bahan-bakar' ? 'active' : '' }}">
                                    Bahan Bakar <i class="fa-solid fa-gas-pump fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard-manage-user')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'manage-user' ? 'active' : '' }}">Manage</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('manage-user') }}"
                                    class="dropdown-item {{ $subPage == 'manage-user' ? 'active' : '' }}">
                                    User <i class="fa-solid fa-user-group"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('manage-role') }}"
                                    class="dropdown-item {{ $subPage == 'manage-role' ? 'active' : '' }}">
                                    Role <i class="fa-solid fa-user-gear"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('manage-access') }}"
                                    class="dropdown-item {{ $subPage == 'manage-access' ? 'active' : '' }}">
                                    Access <i class="fa-solid fa-user-gear"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('manage-user-line') }}"
                                    class="dropdown-item {{ $subPage == 'manage-user-line' ? 'active' : '' }}">
                                    Line <i class="fa-solid fa-users-line"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif


                {{-- Marketing --}}
                @if ($page == 'dashboard-marketing')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'marketing-master' ? 'active' : '' }}">Master</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('master-costing') }}"
                                    class="dropdown-item {{ $subPage == 'marketing-master-costing' ? 'active' : '' }}">
                                    Costing <i class="fa-solid fa-list-ul fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if ($page == 'accounting')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            class="nav-link dropdown-toggle">Proses</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('update-data-ceisa') }}" class="dropdown-item">
                                    Update Data Ceisa
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            class="nav-link dropdown-toggle">Laporan</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('report-rekonsiliasi-ceisa') }}" class="dropdown-item">
                                    Rekonsiliasi Ceisa
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('report-ceisa-detail') }}" class="dropdown-item">
                                    Ceisa Detail
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('report-signalbit-bc') }}" class="dropdown-item">
                                    Data BC Signalbit
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif


                {{-- Management Report --}}
                @if ($page == 'dashboard-mgt-report')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'mgt-report-proses' ? 'active' : '' }}">Proses</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('mgt_report_proses_daily_cost') }}"
                                    class="dropdown-item {{ $subPage == 'mgt-report-proses-daily-cost' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Daily Cost
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'mgt-report-laporan' ? 'active' : '' }}">Laporan</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('mgt_report_daily_cost') }}"
                                    class="dropdown-item {{ $subPage == 'mgt-report-laporan-daily-cost' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Daily Cost
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('mgt_report_earning') }}"
                                    class="dropdown-item {{ $subPage == 'mgt-report-laporan-earning' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Earning
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('mgt_report_sum_prod_earn') }}"
                                    class="dropdown-item {{ $subPage == 'mgt-report-laporan-sum-prod-earn' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Summary Production Earning
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('mgt_report_sum_full_earn') }}"
                                    class="dropdown-item {{ $subPage == 'mgt-report-laporan-sum-full-earn' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Summary Production Full Earning
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('mgt_report_sum_buyer') }}"
                                    class="dropdown-item {{ $subPage == 'mgt-report-laporan-sum-buyer' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Summary Buyer
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('mgt_report_daily_earn_buyer') }}"
                                    class="dropdown-item {{ $subPage == 'mgt-report-laporan-daily-earn-buyer' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Daily Earn Per Buyer
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('mgt_report_profit_line') }}"
                                    class="dropdown-item {{ $subPage == 'mgt-report-laporan-profit-line' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Profit Line
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- Industrial Engineering --}}
                @if ($page == 'dashboard-IE')
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'IE-master' ? 'active' : '' }}">Master</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('IE_master_process') }}"
                                    class="dropdown-item {{ $subPage == 'IE-master-process' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Master Process
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('IE_master_part_process') }}"
                                    class="dropdown-item {{ $subPage == 'IE-master-part-process' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Master Part Process
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'IE-proses' ? 'active' : '' }}">Proses</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('IE_proses_op_breakdown') }}"
                                    class="dropdown-item {{ $subPage == 'IE-proses-op-breakdown' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Operational Breakdown
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'IE-laporan' ? 'active' : '' }}">Laporan</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('IE_lap_recap_smv') }}"
                                    class="dropdown-item {{ $subPage == 'IE-laporan-recap-smv' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Recap SMV
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('IE_lap_recap_cm_price') }}"
                                    class="dropdown-item {{ $subPage == 'IE-laporan-recap-cm-price' ? 'active' : '' }}">
                                    <i class="fa-solid fa-list-ul fa-sm"></i> Recap CM Price
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif


                {{-- DIVIDER EXAMPLE --}}
                {{-- <li class="dropdown-divider"></li> --}}

                {{-- TWO LEVER DROPDOWN EXAMPLE --}}
                {{--
                        <li class="dropdown-submenu dropdown-hover">
                            <a href="#" role="button" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false" class="dropdown-item dropdown-toggle">Hover
                                for action</a>
                            <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a tabindex="-1" href="#" class="dropdown-item">level 2</a>
                                </li>

                                <!-- Level three dropdown-->
                                <li class="dropdown-submenu">
                                    <a id="dropdownSubMenu3" href="#" role="button" data-bs-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false" class="dropdown-item dropdown-toggle">level 2</a>
                                    <ul aria-labelledby="dropdownSubMenu3" class="dropdown-menu border-0 shadow">
                                        <li><a href="#" class="dropdown-item">3rd level</a></li>
                                        <li><a href="#" class="dropdown-item">3rd level</a></li>
                                    </ul>
                                </li>
                                <!-- End Level three -->

                                <li><a href="#" class="dropdown-item">level 2</a></li>
                                <li><a href="#" class="dropdown-item">level 2</a></li>
                            </ul>
                        </li>
                    --}}

                <li class="nav-item">
                    <a href="{{ route('home') }}/" class="nav-link">
                        <i class="fas fa-home"></i>
                    </a>
                </li>
            </ul>

            <!-- SEARCH FORM -->
            {{--
                <form class="form-inline ml-0 ml-md-3">
                    <div class="input-group input-group-sm">
                        <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                        <div class="input-group-append">
                        <button class="btn btn-navbar" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                        </div>
                    </div>
                </form>
            --}}
        </div>

        <!-- RIGHT SECTION -->
        <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
            <!-- MESSAGES EXAMPLE -->
            {{--
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
            --}}

            <!-- NOTIFICATION EXAMPLE -->
            {{--
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
                </li>
            --}}

            <!-- USER OFFCANVAS -->
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="offcanvas" href="#user-offcanvas" role="button"
                    aria-controls="user-offcanvas">
                    <i class="fas fa-user ps-1"></i>
                    {{ strlen(Auth::user()->username) > 10 ? substr(Auth::user()->username, 0, 10) . '...' : Auth::user()->username }}
                </a>
            </li>
        </ul>
    </div>
</nav>
