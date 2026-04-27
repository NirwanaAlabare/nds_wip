@role('dc')
    <li class="nav-item dropdown">
        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
            class="nav-link dropdown-toggle {{ $subPageGroup == 'dcin-dc' ? 'active' : '' }}">DC</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li>
                <a href="{{ route('dc-in') }}"
                    class="dropdown-item {{ $routeName == 'dc-in' ? 'active' : '' }}">
                    DC IN <i class="fas fa-qrcode fa-sm"></i>
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
                    IN Secondary Dalam <i class="fas fa-house-user"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('secondary-inhouse') }}"
                    class="dropdown-item {{ $routeName == 'secondary-inhouse' ? 'active' : '' }}">
                    OUT Secondary Dalam <i class="fas fa-house-user"></i>
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
@endrole
@role('dc')
    <li class="nav-item dropdown">
        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
            class="nav-link dropdown-toggle {{ $subPageGroup == 'stocker-reject' ? 'active' : '' }}">Reject</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li>
                <a href="{{ route('stocker-reject') }}"
                    class="dropdown-item {{ $subPage == 'stocker-reject' ? 'active' : '' }}">
                Stocker Ganti Reject <i class="fa-solid fa-ticket"></i>
                </a>
            </li>
        </ul>
    </li>
@endrole
@role('dc')
    <li class="nav-item dropdown">
        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle {{ $subPageGroup == 'stok-dc' ? 'active' : '' }}">Stok</a>
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
        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"class="nav-link dropdown-toggle {{ $subPageGroup == 'stocker-number' ? 'active' : '' }}">Number</a>
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
                {{-- <li>
                    <a href="{{ route('month-count') }}" class="dropdown-item {{ $subPage == 'month-count' ? 'active' : '' }}"> Month Number <i class="fa-solid fa-hashtag"></i></a>
                </li>
                <li>
                    <a href="{{ route('stocker-balance') }}" class="dropdown-item {{ $subPage == 'stocker-balance' ? 'active' : '' }}"> Month Number <i class="fa-solid fa-hashtag"></i></a>
                </li> --}}
        </ul>
    </li>
@endrole
@role('admin')
    <li class="nav-item dropdown">
        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle {{ $subPageGroup == 'report' ? 'active' : '' }}">Report</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li>
                <a href="{{ route('dc-report') }}"
                    class="dropdown-item {{ $routeName == 'dc-report' ? 'active' : '' }}">
                    Report WIP DC <i class="fa-solid fa-file"></i>
                </a>
            </li>
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
