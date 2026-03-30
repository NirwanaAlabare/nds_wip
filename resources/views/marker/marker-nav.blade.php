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
