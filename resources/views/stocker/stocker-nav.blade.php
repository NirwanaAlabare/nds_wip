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
                    Stocker <i class="fa-solid fa-ticket-simple"></i>
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
