@role('export_import')
    <li class="nav-item dropdown">
        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
            class="nav-link dropdown-toggle {{ $subPageGroup == 'laporan-export-import' ? 'active' : '' }}">Laporan</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li>
                <a href="{{ route('report-rekonsiliasi-ceisa') }}"
                    class="dropdown-item {{ $routeName == 'report-rekonsiliasi-ceisa' ? 'active' : '' }}">
                    Rekonsiliasi Ceisa
                </a>
            </li>
            <li>
                <a href="{{ route('report-ceisa-detail') }}"
                    class="appeared dropdown-item {{ $routeName == 'report-ceisa-detail' ? 'active' : '' }}">
                    Ceisa Detail
                </a>
            </li>
            <li>
                <a href="{{ route('report-signalbit-bc') }}"
                    class="appeared dropdown-item {{ $routeName == 'report-signalbit-bc' ? 'active' : '' }}">
                    Data BC Signalbit
                </a>
            </li>
        </ul>
    </li>
@endrole