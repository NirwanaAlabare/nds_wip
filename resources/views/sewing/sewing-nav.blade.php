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
                    Master Secondary Sewing <i class="fa-solid fa-diagram-project"></i>
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
        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle {{ $subPageGroup == 'sewing-defect' ? 'active' : '' }}">Defect</a>
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
                    Report Mutasi Sewing <i class="fa-solid fa-file"></i>
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
            <li>
                <a href="{{ route('report-sewing-out-subcont') }}"
                    class="dropdown-item {{ $subPage == 'report-sewing-out-subcont' ? 'active' : '' }}">
                    Sewing Out Subcont <i class="fas fa-file-excel"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('report-sewing-in-subcont') }}"
                    class="dropdown-item {{ $subPage == 'report-sewing-in-subcont' ? 'active' : '' }}">
                    Sewing In Subcont <i class="fas fa-file-excel"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('report-sewing-monitoring-subcont') }}"
                    class="dropdown-item {{ $subPage == 'report-sewing-monitoring-subcont' ? 'active' : '' }}">
                    Monitoring Sewing Subcont <i class="fas fa-file-excel"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('report-sewing-mutasi-subcont') }}"
                    class="dropdown-item {{ $subPage == 'report-sewing-mutasi-subcont' ? 'active' : '' }}">
                    Mutasi Sewing Subcont <i class="fas fa-file-excel"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('reportDefectReject') }}"
                    class="dropdown-item {{ $subPage == 'reportDefectReject' ? 'active' : '' }}">
                    Defect & Reject <i class="fas fa-file-excel"></i>
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
    <li class="nav-item dropdown">
        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false" class="nav-link dropdown-toggle {{ $subPageGroup == 'sewing-packing-in' ? 'active' : '' }}">Sewing In</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li>
                <a href="{{ route('sewing-in-subcont') }}"
                    class="dropdown-item {{ $subPage == 'sewing-in-subcont' ? 'active' : '' }}">
                    Sewing In Subcont <i class="fas fa-truck-loading"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('approve-sewing-in-subcont') }}"
                    class="dropdown-item {{ $subPage == 'approve-sewing-in-subcont' ? 'active' : '' }}">
                    Approval Sewing In Subcont <i class="fas fa-thumbs-up"></i>
                </a>
            </li>
        </ul>
    </li>
    <li class="nav-item dropdown">
        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false" class="nav-link dropdown-toggle {{ $subPageGroup == 'sewing-packing-out' ? 'active' : '' }}">Sewing Out</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li>
                <a href="{{ route('sewing-out-subcont') }}"
                    class="dropdown-item {{ $subPage == 'sewing-out-subcont' ? 'active' : '' }}">
                    Sewing Out Subcont <i class="fas fa-shipping-fast"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('approve-sewing-out-subcont') }}"
                    class="dropdown-item {{ $subPage == 'approve-sewing-out-subcont' ? 'active' : '' }}">
                    Approval Sewing Out Subcont <i class="fas fa-thumbs-up"></i>
                </a>
            </li>
        </ul>
    </li>
    <li class="nav-item">
        <a href="{{ route('dashboard-wip') }}"
            class="nav-link {{ $routeName == 'dashboard-wip' ? 'active' : '' }}" target="_blank">
            <i class="fa fa-pager"></i>
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
