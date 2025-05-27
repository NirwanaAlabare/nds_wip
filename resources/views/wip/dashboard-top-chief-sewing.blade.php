@extends('layouts.index', ["navbar" => false, "footer" => false, "containerFluid" => true])

@section('custom-link')
    {{-- Style SCSS --}}
    <link rel="stylesheet" href="{{ asset('dist/css/style.scss') }}">

    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <!-- Apex Charts -->
    <link rel="stylesheet" href="{{ asset('plugins/apexcharts/apexcharts.css') }}">

    <!-- Swiper -->
    <link rel="stylesheet" href="{{ asset('plugins/swiper/css/swiper-bundle.min.css') }}" />

    {{-- Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap" rel="stylesheet">

    <style>
        .dataTables_wrapper .dataTables_processing {
            position: absolute;
            top: 50px !important;
            /* background: #FFFFCC;
            border: 1px solid black; */
            border-radius: 3px;
            font-weight: bold;
        }

        /* SWIPPER */
        swiper-container {
            width: 100%;
            height: 100%;
            background-color: transparent !important;
        }

        swiper-slide {
            background: transparent;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        swiper-slide img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        swiper-container {
            width: 100%;
            height: 100%;
            background-color: inherit;
        }

        swiper-slide {
            display: flex;
            justify-content: center;
            align-items: start;
            border-radius: 10px;
            min-height: 95vh;
        }

        swiper-slide img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-frame {
            border-radius: 50%;
            overflow: hidden;
            border: 1px solid #cfcfcf;
            /* box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15); */
            margin-top: auto;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            justify-content: flex-end;
            align-items: flex-end;
        }

        .profile-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .crimson-text-regular {
            font-family: "Crimson Text", serif;
            font-weight: 400;
            font-style: normal;
        }

        .crimson-text-semibold {
            font-family: "Crimson Text", serif;
            font-weight: 600;
            font-style: normal;
        }

        .crimson-text-bold {
            font-family: "Crimson Text", serif;
            font-weight: 700;
            font-style: normal;
        }

        .crimson-text-regular-italic {
            font-family: "Crimson Text", serif;
            font-weight: 400;
            font-style: italic;
        }

        .crimson-text-semibold-italic {
            font-family: "Crimson Text", serif;
            font-weight: 600;
            font-style: italic;
        }

        .crimson-text-bold-italic {
            font-family: "Crimson Text", serif;
            font-weight: 700;
            font-style: italic;
        }

        #fireworks-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        overflow: hidden;
        pointer-events: none;
        z-index: 9999;
        }

        .firework-rocket {
        position: absolute;
        bottom: 0;
        width: 8px;
        height: 8px;
        transform: translateX(0);
        pointer-events: none;
        }

        .firework-rocket-inner {
        width: 100%;
        height: 100%;
        border-radius: 100%;
        margin-left: 2px;
        }

        .firework-spark {
        position: absolute;
        width: 4px;
        height: 4px;
        border-radius: 2px;
        pointer-events: none;
        opacity: 0.8;
        }

        .firework-fragment {
        position: absolute;
        width: 6px;
        height: 6px;
        border-radius: 3px;
        opacity: 0.9;
        pointer-events: none;
        }
    </style>
@endsection

@section('content')
    <input type="hidden" id="year" value="{{ $year ? $year : date("Y") }}">
    <input type="hidden" id="month" value="{{ $month ? $month : date("m") }}">
    <input type="hidden" id="month-name" value="{{ $monthName ? $monthName : $months[num(date("m"))-1] }}">
    {{-- <swiper-container class="mySwiper" id="top-chief-carousel" autoplay-delay="30000" autoplay-disable-on-interaction="true" space-between="30" centered-slides="true">
        <swiper-slide id="carousel-1">
            <div class="swiper-no-swiping mt-5" id="chief-daily-efficiency-table" style="max-height: 100vh;">

            </div>
        </swiper-slide>
    </swiper-container> --}}
    <div style="width: 100vw; height: 100vh; background: #fbfbfb;" class="d-flex flex-column justify-content-center align-items-center overflow-hidden">
        <h1 class="text-center text-warning crimson-text-bold mt-3 py-1 px-3">CHIEF OF THE MONTH</h1>
        <h5 class="text-center">{{ strtoupper($monthName)." ".$year }}</h5>

        <div class="profile-frame text-center" style="min-width: 200px !important; width: 200px !important; min-height: 200px !important; height: 200px !important;">
            <img src="{{ asset('dist/img/person.png') }}" alt="person" class="img-fluid" id='top-chief-img'>
        </div>
        <h3 class="text-center text-sb mt-3" id="top-chief-name">EMPLOYEE</h3>
        <div class="icon d-none" id="number-one-icon">
            <svg class="numeroUno" width="50" height="10" viewBox="0 0 53 53" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g class="championIcon">
                <g class="sparks">
                    <path class="spark" d="M42 4C43.1046 4 44 3.10457 44 2C44 0.89543 43.1046 0 42 0C40.8954 0 40 0.89543 40 2C40 3.10457 40.8954 4 42 4Z" fill="#F9BD18" />
                    <path class="spark" d="M36 23C36.5523 23 37 22.5523 37 22C37 21.4477 36.5523 21 36 21C35.4477 21 35 21.4477 35 22C35 22.5523 35.4477 23 36 23Z" fill="#F9BD18" />
                    <path class="spark" d="M35.5 8C35.7761 8 36 7.77614 36 7.5C36 7.22386 35.7761 7 35.5 7C35.2239 7 35 7.22386 35 7.5C35 7.77614 35.2239 8 35.5 8Z" fill="#F9BD18" />
                    <path class="spark" d="M42.5 17C42.7761 17 43 16.7761 43 16.5C43 16.2239 42.7761 16 42.5 16C42.2239 16 42 16.2239 42 16.5C42 16.7761 42.2239 17 42.5 17Z" fill="#F9BD18" />
                    <path class="spark" d="M27.5 2C27.7761 2 28 1.77614 28 1.5C28 1.22386 27.7761 1 27.5 1C27.2239 1 27 1.22386 27 1.5C27 1.77614 27.2239 2 27.5 2Z" fill="#F9BD18" />
                    <path class="spark" d="M30.5 33C30.7761 33 31 32.7761 31 32.5C31 32.2239 30.7761 32 30.5 32C30.2239 32 30 32.2239 30 32.5C30 32.7761 30.2239 33 30.5 33Z" fill="#F9BD18" />
                    <path class="spark" d="M10.5 49C10.7761 49 11 48.7761 11 48.5C11 48.2239 10.7761 48 10.5 48C10.2239 48 10 48.2239 10 48.5C10 48.7761 10.2239 49 10.5 49Z" fill="#F9BD18" />
                    <path class="spark" d="M46 12C46.5523 12 47 11.5523 47 11C47 10.4477 46.5523 10 46 10C45.4477 10 45 10.4477 45 11C45 11.5523 45.4477 12 46 12Z" fill="#F9BD18" />
                    <path class="spark" d="M14 21C14.5523 21 15 20.5523 15 20C15 19.4477 14.5523 19 14 19C13.4477 19 13 19.4477 13 20C13 20.5523 13.4477 21 14 21Z" fill="#F9BD18" />
                    <path class="spark" d="M4 13C4.55228 13 5 12.5523 5 12C5 11.4477 4.55228 11 4 11C3.44772 11 3 11.4477 3 12C3 12.5523 3.44772 13 4 13Z" fill="#F9BD18" />
                    <path class="spark" d="M3 39C3.55228 39 4 38.5523 4 38C4 37.4477 3.55228 37 3 37C2.44772 37 2 37.4477 2 38C2 38.5523 2.44772 39 3 39Z" fill="#F9BD18" />
                    <path class="spark" d="M39 52C39.5523 52 40 51.5523 40 51C40 50.4477 39.5523 50 39 50C38.4477 50 38 50.4477 38 51C38 51.5523 38.4477 52 39 52Z" fill="#F9BD18" />
                    <path class="spark" d="M48.5 45C49.3284 45 50 44.3284 50 43.5C50 42.6716 49.3284 42 48.5 42C47.6716 42 47 42.6716 47 43.5C47 44.3284 47.6716 45 48.5 45Z" fill="#F9BD18" />
                    <path class="spark" d="M14.5 9C15.3284 9 16 8.32843 16 7.5C16 6.67157 15.3284 6 14.5 6C13.6716 6 13 6.67157 13 7.5C13 8.32843 13.6716 9 14.5 9Z" fill="#F9BD18" />
                    <path class="spark" d="M19 28C20.1046 28 21 27.1046 21 26C21 24.8954 20.1046 24 19 24C17.8954 24 17 24.8954 17 26C17 27.1046 17.8954 28 19 28Z" fill="#F9BD18" />
                </g>
                <g class="left_branch">
                    <g class="stem_rapper">
                    <g class="top_leaf">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M5.30008 18L5.80008 16C6.10008 17 6.20008 18.1 6.20008 19.1V19.4C6.00008 20 5.40008 20.4 4.80008 20.3C4.20008 20.2 3.80008 19.6 3.90008 19C4.20008 17.8 4.90008 16.8 5.80008 16L5.30008 18Z" fill="#082149" />
                    </g>
                    <g class="left">
                        <g class="leaf_rapper">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M3.3002 23.9004C2.0002 22.8004 0.900195 21.4004 0.200195 19.9004L2.7002 21.7004L0.200195 19.9004C1.7002 20.2004 3.2002 20.7004 4.6002 21.3004C4.7002 21.4004 4.8002 21.4004 5.0002 21.5004C5.7002 21.8004 5.9002 22.6004 5.6002 23.3004C5.5002 23.4004 5.5002 23.5004 5.4002 23.6004C4.9002 24.2004 4.0002 24.3004 3.3002 23.9004Z" fill="#082149" />
                        </g>
                        <g class="leaf_rapper">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M0.600098 24.6992C2.1001 24.6992 3.6001 24.9992 5.1001 25.3992C5.3001 25.3992 5.4001 25.4992 5.5001 25.5992C6.3001 25.8992 6.6001 26.7992 6.3001 27.4992C6.3001 27.4992 6.3001 27.4992 6.3001 27.5992C5.8001 28.1992 5.0001 28.4992 4.2001 28.1992C2.8001 27.1992 1.6001 25.9992 0.600098 24.6992Z" fill="#082149" />
                        </g>
                        <g class="leaf_rapper">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M7.7002 30.1008C8.7002 30.4008 9.3002 31.4008 9.0002 32.4008C8.6002 33.4008 7.5002 33.9008 6.5002 33.5008H6.4002C6.3002 33.5008 6.2002 33.4008 6.1002 33.4008C4.3002 32.5008 2.6002 31.3008 1.2002 29.9008C3.2002 29.6008 5.2002 29.6008 7.2002 29.9008C7.4002 29.9008 7.5002 29.9008 7.7002 30.1008C7.6002 30.1008 7.6002 30.1008 7.7002 30.1008Z" fill="#082149" />
                        </g>
                        <g class="leaf_rapper">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M11.5004 35.0009C12.6004 35.2009 13.3004 36.2009 13.1004 37.3009C12.8004 38.3009 11.7004 38.9009 10.7004 38.6009H10.5004C10.4004 38.6009 10.2004 38.5009 10.1004 38.5009C8.20039 37.7009 6.50039 36.7009 4.90039 35.4009C6.90039 34.9009 9.00039 34.8009 11.0004 35.0009H11.5004Z" fill="#082149" />
                        </g>
                    </g>
                    <g class="right">
                        <g class="leaf_rapper">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M6.40022 21.9008L8.70022 20.3008C8.10022 21.7008 7.20022 23.0008 6.00022 24.0008C5.30022 24.4008 4.40022 24.3008 3.90022 23.7008C3.40022 23.1008 3.50022 22.3008 4.10022 21.8008L4.20022 21.7008C4.30022 21.6008 4.40022 21.6008 4.50022 21.5008C5.80022 20.9008 7.20022 20.5008 8.60022 20.3008L6.40022 21.9008Z" fill="#082149" />
                        </g>
                        <g class="leaf_rapper">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M5.10039 25.5996L4.90039 25.7996C4.40039 26.3996 4.40039 27.1996 4.90039 27.7996C5.60039 28.3996 6.60039 28.2996 7.20039 27.6996L7.50039 27.3996C8.30039 26.1996 9.00039 24.9996 9.40039 23.5996C7.90039 23.9996 6.40039 24.6996 5.10039 25.5996Z" fill="#082149" />
                        </g>
                        <g class="leaf_rapper">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M7.00031 29.4996L6.9003 29.5996C6.4003 30.2996 6.6003 31.2996 7.3003 31.7996C8.1003 32.2996 9.1003 32.0996 9.7003 31.3996C9.8003 31.2996 9.9003 31.1996 9.9003 30.9996C10.6003 29.5996 11.0003 28.0996 11.2003 26.5996C9.7003 27.2996 8.20031 28.1996 7.00031 29.2996C7.10031 29.2996 7.10031 29.3996 7.00031 29.4996Z" fill="#082149" />
                        </g>
                        <g class="leaf_rapper">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M11.8003 29.9004C11.1003 31.2004 10.6003 32.7004 10.4003 34.2004C10.4003 34.4004 10.3003 34.5004 10.3003 34.7004C10.3003 35.6004 11.1003 36.4004 12.0003 36.4004H12.1003C13.0003 36.4004 13.7003 35.7004 13.7003 34.8004V34.7004C13.7003 34.6004 13.7003 34.4004 13.6003 34.3004C13.3003 32.7004 12.7003 31.2004 11.8003 29.9004Z" fill="#082149" />
                        </g>
                    </g>
                    <path class="stem" d="M19.3 40.5004C16.9 40.2004 14.5 39.4004 12.5 38.1004C10.4 36.8004 8.60001 35.1004 7.20001 33.0004C5.80001 30.9004 4.90001 28.6004 4.50001 26.1004C4.10001 23.7004 4.40001 21.2004 5.30001 18.9004C4.90001 21.2004 5.10001 23.6004 5.60001 25.9004C6.20001 28.1004 7.10001 30.3004 8.40001 32.2004C9.70001 34.1004 11.3 35.8004 13.2 37.2004C15 38.5004 17.1 39.6004 19.3 40.5004Z" fill="#082149" />
                    </g>
                    <g class="bottom_leafs">
                    <g class="leaf_rapper">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M15.8004 32.6992C15.7004 34.3992 15.8004 38.3992 16.9004 40.6992C17.6004 41.8992 19.0004 42.3992 20.3004 41.9992C21.5004 41.4992 22.1004 39.9992 21.6004 38.7992C21.6004 38.6992 21.5004 38.6992 21.5004 38.5992C21.4004 38.3992 21.3004 38.1992 21.1004 37.9992C19.6004 35.9992 17.8004 34.1992 15.8004 32.6992L17.9004 37.1992L15.8004 32.6992Z" fill="#082149" />
                    </g>
                    <g class="leaf_rapper">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M17.8003 39.4009H18.0003C19.4003 39.2009 20.6003 40.1009 21.0003 41.4009C21.1003 42.7009 20.2003 43.9009 18.9003 44.1009H18.1003C15.4003 44.0009 12.8003 43.5009 10.3003 42.7009C12.4003 41.2009 14.8003 40.1009 17.3003 39.4009H17.8003Z" fill="#082149" />
                    </g>
                    </g>
                </g>
                <g class="right_branch">
                    <g class="stem_rapper">
                    <g class="top_leaf">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M46.9003 18.6992V18.3992C46.8003 17.2992 46.9003 16.1992 47.2003 15.1992L47.7003 17.1992L47.2003 15.1992C48.1003 15.9992 48.7003 17.0992 49.2003 18.1992C49.3003 18.7992 49.0003 19.2992 48.4003 19.4992C47.8003 19.5992 47.1003 19.2992 46.9003 18.6992Z" fill="#082149" />
                    </g>
                    <g class="left">
                        <g class="leaf_rapper">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M46.7003 21.1992L44.4003 19.6992C45.0003 21.0992 46.0003 22.2992 47.2003 23.2992C47.9003 23.6992 48.7003 23.5992 49.2003 22.9992C49.7003 22.3992 49.5003 21.4992 48.9003 21.0992C48.9003 21.0992 48.8003 21.0992 48.8003 20.9992C48.7003 20.8992 48.6003 20.8992 48.5003 20.7992C47.2003 20.1992 45.7003 19.8992 44.3003 19.6992L46.7003 21.1992Z" fill="#082149" />
                        </g>
                        <g class="leaf_rapper">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M48.2003 24.9C48.3003 24.9 48.3003 25 48.4003 25C49.0003 25.5 49.0003 26.3 48.5003 26.9L48.4003 27C47.8003 27.6 46.7003 27.6 46.1003 27L45.8003 26.7C44.9003 25.6 44.2003 24.3 43.8003 23C45.3003 23.3 46.8003 23.9 48.1003 24.8C48.2003 24.8 48.2003 24.8 48.2003 24.9Z" fill="#082149" />
                        </g>
                        <g class="leaf_rapper">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M47.0002 29.3996L47.1002 29.4996C47.7002 30.2996 47.5002 31.4996 46.7002 32.0996C45.7002 32.7996 44.4002 32.5996 43.7002 31.5996C43.6002 31.4996 43.5002 31.2996 43.5002 31.1996C42.6002 29.5996 42.0002 27.8996 41.7002 26.0996C43.5002 26.7996 45.2002 27.8996 46.7002 29.0996C46.8002 29.2996 46.9002 29.2996 47.0002 29.3996Z" fill="#082149" />
                        </g>
                        <g class="leaf_rapper">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M41.3003 29.3008C42.2003 30.9008 42.8003 32.5008 43.2003 34.3008C43.2003 34.5008 43.3003 34.7008 43.3003 34.9008C43.3003 36.0008 42.4003 37.0008 41.3003 37.0008C40.2003 37.0008 39.3003 36.2008 39.3003 35.1008V35.0008C39.3003 34.8008 39.3003 34.7008 39.4003 34.5008C39.7003 32.6008 40.4003 30.9008 41.3003 29.3008Z" fill="#082149" />
                        </g>
                    </g>
                    <g class="right">
                        <g class="leaf_rapper">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M50.2004 22.3008C51.3004 21.3008 52.1004 20.1008 52.7004 18.8008L50.6004 20.4008L52.6004 18.8008C51.3004 19.1008 50.1004 19.6008 48.9004 20.2008C48.8004 20.2008 48.7004 20.3008 48.6004 20.4008L48.5004 20.5008H48.4004C47.9004 20.9008 47.8004 21.7008 48.2004 22.2008C48.8004 22.6008 49.6004 22.6008 50.2004 22.3008Z" fill="#082149" />
                        </g>
                        <g class="leaf_rapper">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M52.6002 23.8008C51.1002 23.8008 49.5002 24.2008 48.1002 24.7008C47.9002 24.7008 47.8002 24.8008 47.7002 24.9008C46.9002 25.2008 46.6002 26.1008 46.9002 26.9008C47.4002 27.6008 48.3002 27.8008 49.0002 27.4008C50.4002 26.4008 51.6002 25.2008 52.6002 23.8008Z" fill="#082149" />
                        </g>
                        <g class="leaf_rapper">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M52.3002 28.7994C51.1002 30.0994 49.7002 31.0994 48.2002 31.8994C48.1002 31.9994 48.0002 31.9994 47.9002 31.9994H47.7002C46.9002 32.2994 45.9002 31.8994 45.6002 31.0994C45.3002 30.2994 45.8002 29.3994 46.6002 29.0994C46.7002 29.0994 46.7002 29.0994 46.8002 29.0994C46.9002 29.0994 47.1002 28.9994 47.2002 28.9994C48.9002 28.5994 50.6002 28.4994 52.3002 28.7994Z" fill="#082149" />
                        </g>
                        <g class="leaf_rapper">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M42.1001 34.4993C41.1001 34.6993 40.4001 35.7993 40.6001 36.7993C41.0001 37.7993 42.0001 38.2993 43.0001 37.9993H43.2001C43.3001 37.9993 43.4001 37.8993 43.5001 37.8993C45.4001 36.9993 47.1001 35.8993 48.7001 34.5993L44.8001 35.5993L48.6001 34.4993C46.6001 34.0993 44.6001 34.0993 42.6001 34.2993C42.5001 34.2993 42.3001 34.2993 42.2001 34.3993L42.1001 34.4993Z" fill="#082149" />
                        </g>
                    </g>
                    <path class="stem" d="M34.5 40.1992C36.7 39.2992 38.7 38.0992 40.5 36.5992C42.3 35.1992 43.8 33.4992 45.1 31.4992C46.3 29.5992 47.2 27.3992 47.7 25.1992C48.2 22.8992 48.2 20.4992 47.9 18.1992C48.9 20.4992 49.2 22.8992 48.9 25.3992C48.6 27.8992 47.7 30.1992 46.4 32.2992C45 34.3992 43.3 36.0992 41.2 37.4992C39.2 38.8992 36.9 39.7992 34.5 40.1992Z" fill="#082149" />
                    </g>
                    <g class="bottom_leafs">
                    <g class="leaf_rapper">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M38 32.4004C38.1 33.9004 38.2 37.3004 37.3 39.3004C36.8 40.4004 35.5 40.9004 34.4 40.5004C33.4 40.1004 32.8 39.0004 33.2 37.9004C33.2 37.8004 33.3 37.7004 33.3 37.7004C33.4 37.5004 33.5 37.4004 33.6 37.2004C34.8 35.4004 36.3 33.8004 38 32.4004L36.3 36.3004L38 32.4004Z" fill="#082149" />
                    </g>
                    <g class="leaf_rapper">
                        <path class="leaf" fill-rule="evenodd" clip-rule="evenodd" d="M35.9004 38.9991H35.7004C34.3004 38.8991 33.1004 39.8991 32.8004 41.1991C32.7004 42.4991 33.7004 43.6991 35.0004 43.7991H35.8004C38.5004 43.5991 41.1004 43.0991 43.6004 42.0991L38.5004 41.5991L43.6004 42.0991C41.4004 40.6991 39.0004 39.6991 36.5004 39.0991C36.2004 39.0991 36.1004 38.9991 35.9004 38.9991Z" fill="#082149" />
                    </g>
                    </g>
                </g>
                <g class="glory_rays_rapper">
                    <g class="glory_rays">
                    <rect x="26.5" y="33.5" width="1" height="4" rx="0.5" fill="#FBD111" />
                    <rect x="32.8286" y="35.5" width="1" height="4" rx="0.5" transform="rotate(45 32.8286 35.5)" fill="#F9C316" />
                    <rect x="36.7002" y="42.5" width="1" height="4" rx="0.5" transform="rotate(90 36.7002 42.5)" fill="#F9BC18" />
                    <rect x="26.5" y="48.5" width="1" height="4" rx="0.5" fill="#FBD111" />
                    <rect x="33.7495" y="49.4941" width="1" height="4" rx="0.5" transform="rotate(135 33.7495 49.4941)" fill="#F9C415" />
                    <rect width="1" height="4" rx="0.5" transform="matrix(-0.707107 0.707107 0.707107 0.707107 20.3716 35.5)" fill="#FDE30C" />
                    <rect width="1" height="4" rx="0.5" transform="matrix(4.37114e-08 1 1 -4.37114e-08 17.5 42.5)" fill="#FDEC08" />
                    <rect width="1" height="4" rx="0.5" transform="matrix(0.707107 0.707107 0.707107 -0.707107 20.5146 49.4941)" fill="#FCE10C" />
                    </g>
                </g>
                <path class="medalion" d="M27 47C29.2091 47 31 45.2091 31 43C31 40.7909 29.2091 39 27 39C24.7909 39 23 40.7909 23 43C23 45.2091 24.7909 47 27 47Z" fill="url(#paint0_linear)" />
                <path class="one" d="M24.7002 13.7004L22.9002 15.5004L19.7002 12.3004L25.6002 6.40039H29.6002V28.8004H24.7002V13.7004Z" fill="#082149" />
                </g>
                <defs>
                <linearGradient id="paint0_linear" x1="22.99" y1="42.96" x2="31.29" y2="42.96" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#FDED07" />
                    <stop offset="0.2034" stop-color="#FCE00C" />
                    <stop offset="0.7095" stop-color="#F9C315" />
                    <stop offset="1" stop-color="#F8B819" />
                </linearGradient>
                </defs>
            </svg>
        </div>
        <div style="width: 100px; height: 100px; background: #082149;" class="mt-3">
            &nbsp;
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- Apex Charts -->
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <!-- Swiper  -->
    <script src="{{ asset('plugins/swiper/js/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('plugins/swiper/js/swiper-element-bundle.min.js') }}"></script>

    <script>
        // Set Custom Dashboard View
        document.body.style.maxHeight = "100vh";
        document.body.style.overflow = "hidden";

        document.querySelector(".content-wrapper").classList.remove("pt-3");
        document.querySelector(".content-wrapper").classList.add("pt-1");

        // Slide
        const swiper = new Swiper('.swiper', {
            direction: 'vertical',
            loop: true,

            pagination: {
                el: '.swiper-pagination',
            },

            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },

            scrollbar: {
                el: '.swiper-scrollbar',
            },
        });

        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        document.getElementById("loading-bg").classList.remove("d-none");

        $('document').ready(async () => {
            await updateData();

            document.getElementById("loading-bg").classList.add("d-none");
            document.getElementById("number-one-icon").classList.remove("d-none");
        });

        var intervalData = setInterval(() => {
            updateData();
        }, 60000);

        var currentDayOne = "";
        var currentDayTwo = "";
        var currentDayThree = "";

        async function updateTopChief(data) {
            // Image
            let imageElement = document.getElementById("top-chief-img");
            imageElement.src = "{{ asset('../storage/employee_profile') }}/"+data.chief_nik+"%20"+data.chief_name+".png";
            imageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
            imageElement.setAttribute("alt", "person")
            imageElement.classList.add("img-fluid")
            imageElement.style.marginRight = "auto";

            // Name
            document.getElementById("top-chief-name").innerHTML = data.chief_name;
        }

        // Colorize Efficiency
        function colorizeEfficiency(element, efficiency) {
            if (isElement(element)) {
                switch (true) {
                    case efficiency < 75 :
                        element.style.color = '#dc3545';
                        break;
                    case efficiency >= 75 && efficiency <= 85 :
                        element.style.color = 'rgb(240, 153, 0)';
                        break;
                    case efficiency > 85 :
                        element.style.color = '#28a745';
                        break;
                }
            }
        }

        // Colorize RFT
        function colorizeRft(element, rft) {
            if (isElement(element)) {
                switch (true) {
                    case rft < 97 :
                        element.style.color = '#dc3545';
                        break;
                    case rft >= 97 && rft < 98 :
                        element.style.color = 'rgb(240, 153, 0)';
                        break;
                    case rft >= 98 :
                        element.style.color = '#28a745';
                        break;
                }
            }
        }

        async function updateData() {
            await $.ajax({
                url: "{{ route("dashboard-top-chief-sewing-data") }}",
                type: "get",
                data: {
                    year: $("#year").val(),
                    month: $("#month").val()
                },
                dataType: "json",
                success: async function (response) {
                    // Sort Chief Daily by Efficiency
                    let sortedChiefDailyEfficiency = response.sort(function(a,b){
                        if (a.currentEff+a.currentRft < b.currentEff+b.currentRft) {
                            return 1;
                        }
                        if (a.currentEff+a.currentRft  > b.currentEff+b.currentRft) {
                            return -1;
                        }
                        return 0;
                    });

                    // Show Chief Daily Data
                    if (sortedChiefDailyEfficiency.length > 0) {
                        updateTopChief(sortedChiefDailyEfficiency[0]);
                    }
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });
        }

        // ----------------------------------------
        // Nothing special but still something :)
        // Hope you like it <3
        // ----------------------------------------

        const fireworksData = [
            //
            // T = 0 ms (Big opener - multiple launches)
            //
            {
                left: '15%',
                color: '#FF4C4C', // Bright Red
                explosionType: 'circle',
                size: 'large',
                launchTime: 0,
            },
            {
                left: '70%',
                color: '#FFD24C', // Bright Gold
                explosionType: 'star',
                size: 'medium',
                launchTime: 0,
            },

            //
            // T = 3,000 ms
            //
            {
                left: '30%',
                color: '#5ECFFF', // Vibrant Light Blue
                explosionType: 'double-spiral',
                size: 'small',
                launchTime: 3000,
            },
            {
                left: '80%',
                color: '#7DFF5E', // Bright Green
                explosionType: 'wave',
                size: 'large',
                launchTime: 3000,
            },

            //
            // T = 6,000 ms (Triple launch)
            //
            {
                left: '25%',
                color: '#C15EFF', // Vivid Purple
                explosionType: 'heart',
                size: 'medium',
                launchTime: 6000,
            },
            {
                left: '50%',
                color: '#FF4CF6', // Magenta
                explosionType: 'swirl',
                size: 'medium',
                launchTime: 6000,
            },
            {
                left: '75%',
                color: '#FFF44C', // Bright Yellow
                explosionType: 'heart',
                size: 'small',
                launchTime: 6000,
            },

            //
            // T = 10,000 ms
            //
            {
                left: '20%',
                color: '#FF964C', // Vivid Orange
                explosionType: 'flower',
                size: 'large',
                launchTime: 10000,
            },
            {
                left: '70%',
                color: '#4CFFB7', // Neon Mint
                explosionType: 'random-burst',
                size: 'small',
                launchTime: 10000,
            },

            //
            // T = 13,000 ms (Triple launch)
            //
            {
                left: '40%',
                color: '#4CDAFF', // Bright Sky Blue
                explosionType: 'random-burst',
                size: 'medium',
                launchTime: 13000,
            },
            {
                left: '60%',
                color: '#F64CFF', // Hot Pink
                explosionType: 'spiral',
                size: 'large',
                launchTime: 13000,
            },
            {
                left: '85%',
                color: '#FF4C7D', // Hot Pinkish Red
                explosionType: 'star',
                size: 'small',
                launchTime: 13000,
            },

            //
            // T = 16,000 ms (Triple launch)
            //
            {
                left: '15%',
                color: '#4CFF4C', // Neon Green
                explosionType: 'triple-star',
                size: 'large',
                launchTime: 16000,
            },
            {
                left: '30%',
                color: '#A14CFF', // Neon Purple
                explosionType: 'random-burst',
                size: 'medium',
                launchTime: 16000,
            },
            {
                left: '80%',
                color: '#FFB74C', // Light Orange
                explosionType: 'circle',
                size: 'small',
                launchTime: 16000,
            },

            //
            // T = 20,000 ms (Triple launch)
            //
            {
                left: '25%',
                color: '#FF4C4C', // Bright Red
                explosionType: 'flower',
                size: 'small',
                launchTime: 20000,
            },
            {
                left: '50%',
                color: '#FFD24C', // Bright Gold
                explosionType: 'heart',
                size: 'medium',
                launchTime: 20000,
            },
            {
                left: '75%',
                color: '#5ECFFF', // Vibrant Light Blue
                explosionType: 'ring-of-rings',
                size: 'large',
                launchTime: 20000,
            },

            //
            // T = 23,000 ms (Triple launch)
            //
            {
                left: '10%',
                color: '#7DFF5E', // Bright Green
                explosionType: 'random-burst',
                size: 'small',
                launchTime: 23000,
            },
            {
                left: '40%',
                color: '#C15EFF', // Vivid Purple
                explosionType: 'spiral',
                size: 'medium',
                launchTime: 23000,
            },
            {
                left: '90%',
                color: '#FF4CF6', // Magenta
                explosionType: 'swirl',
                size: 'large',
                launchTime: 23000,
            },

            //
            // T = 26,000 ms
            //
            {
                left: '20%',
                color: '#FFF44C', // Bright Yellow
                explosionType: 'flurry',
                size: 'large',
                launchTime: 26000,
            },
            {
                left: '75%',
                color: '#FF964C', // Vivid Orange
                explosionType: 'random-burst',
                size: 'medium',
                launchTime: 26000,
            },

            //
            // T = 30,000 ms (Triple launch)
            //
            {
                left: '15%',
                color: '#4CFFB7', // Neon Mint
                explosionType: 'wave',
                size: 'small',
                launchTime: 30000,
            },
            {
                left: '60%',
                color: '#4CDAFF', // Bright Sky Blue
                explosionType: 'triple-star',
                size: 'large',
                launchTime: 30000,
            },
            {
                left: '80%',
                color: '#F64CFF', // Hot Pink
                explosionType: 'heart',
                size: 'medium',
                launchTime: 30000,
            },

            //
            // T = 33,000 ms (Triple launch)
            //
            {
                left: '25%',
                color: '#FF4C7D', // Hot Pinkish Red
                explosionType: 'random-burst',
                size: 'medium',
                launchTime: 33000,
            },
            {
                left: '50%',
                color: '#4CFF4C', // Neon Green
                explosionType: 'star',
                size: 'small',
                launchTime: 33000,
            },
            {
                left: '70%',
                color: '#A14CFF', // Neon Purple
                explosionType: 'double-spiral',
                size: 'large',
                launchTime: 33000,
            },

            //
            // T = 37,000 ms (Triple launch)
            //
            {
                left: '15%',
                color: '#FFB74C', // Light Orange
                explosionType: 'flower',
                size: 'medium',
                launchTime: 37000,
            },
            {
                left: '40%',
                color: '#FF4C4C', // Bright Red
                explosionType: 'random-burst',
                size: 'small',
                launchTime: 37000,
            },
            {
                left: '85%',
                color: '#FFD24C', // Bright Gold
                explosionType: 'heart',
                size: 'large',
                launchTime: 37000,
            },

            //
            // T = 40,000 ms (Triple launch)
            //
            {
                left: '10%',
                color: '#5ECFFF', // Vibrant Light Blue
                explosionType: 'ring-of-rings',
                size: 'medium',
                launchTime: 40000,
            },
            {
                left: '45%',
                color: '#7DFF5E', // Bright Green
                explosionType: 'wave',
                size: 'small',
                launchTime: 40000,
            },
            {
                left: '80%',
                color: '#C15EFF', // Vivid Purple
                explosionType: 'spiral',
                size: 'large',
                launchTime: 40000,
            },

            //
            // T = 43,000 ms
            //
            {
                left: '20%',
                color: '#FF4CF6', // Magenta
                explosionType: 'swirl',
                size: 'medium',
                launchTime: 43000,
            },
            {
                left: '75%',
                color: '#FFF44C', // Bright Yellow
                explosionType: 'flurry',
                size: 'large',
                launchTime: 43000,
            },

            //
            // T = 47,000 ms (Triple launch)
            //
            {
                left: '30%',
                color: '#FF964C', // Vivid Orange
                explosionType: 'double-spiral',
                size: 'large',
                launchTime: 47000,
            },
            {
                left: '70%',
                color: '#4CFFB7', // Neon Mint
                explosionType: 'wave',
                size: 'medium',
                launchTime: 47000,
            },
            {
                left: '50%',
                color: '#4CDAFF', // Bright Sky Blue
                explosionType: 'triple-star',
                size: 'small',
                launchTime: 47000,
            },

            //
            // T = 50,000 ms (Triple launch)
            //
            {
                left: '25%',
                color: '#F64CFF', // Hot Pink
                explosionType: 'heart',
                size: 'medium',
                launchTime: 50000,
            },
            {
                left: '60%',
                color: '#FF4C7D', // Hot Pinkish Red
                explosionType: 'random-burst',
                size: 'large',
                launchTime: 50000,
            },
            {
                left: '85%',
                color: '#4CFF4C', // Neon Green
                explosionType: 'random-burst',
                size: 'small',
                launchTime: 50000,
            },

            //
            // T = 53,000 ms
            //
            {
                left: '10%',
                color: '#A14CFF', // Neon Purple
                explosionType: 'ring-of-rings',
                size: 'large',
                launchTime: 53000,
            },
            {
                left: '40%',
                color: '#FFB74C', // Light Orange
                explosionType: 'circle',
                size: 'small',
                launchTime: 53000,
            },

            //
            // T = 56,000 ms
            //
            {
                left: '15%',
                color: '#FF4C4C', // Bright Red
                explosionType: 'spiral',
                size: 'small',
                launchTime: 56000,
            },
            {
                left: '75%',
                color: '#FFD24C', // Bright Gold
                explosionType: 'flurry',
                size: 'medium',
                launchTime: 56000,
            },

            //
            // T = 59,000 ms (Triple launch)
            //
            {
                left: '20%',
                color: '#5ECFFF', // Vibrant Light Blue
                explosionType: 'triple-star',
                size: 'medium',
                launchTime: 59000,
            },
            {
                left: '50%',
                color: '#7DFF5E', // Bright Green
                explosionType: 'wave',
                size: 'large',
                launchTime: 59000,
            },
            {
                left: '80%',
                color: '#C15EFF', // Vivid Purple
                explosionType: 'double-spiral',
                size: 'small',
                launchTime: 59000,
            },

            //
            // T = 62,000 ms (Grand Finale: a huge barrage of large effects!)
            //
            {
                left: '10%',
                color: '#FF4CF6', // Magenta
                explosionType: 'random-burst',
                size: 'large',
                launchTime: 62000,
            },
            {
                left: '25%',
                color: '#FFF44C', // Bright Yellow
                explosionType: 'ring-of-rings',
                size: 'large',
                launchTime: 62000,
            },
            {
                left: '40%',
                color: '#FF964C', // Vivid Orange
                explosionType: 'triple-star',
                size: 'large',
                launchTime: 62000,
            },
            {
                left: '55%',
                color: '#4CFFB7', // Neon Mint
                explosionType: 'heart',
                size: 'large',
                launchTime: 62000,
            },
            {
                left: '70%',
                color: '#4CDAFF', // Bright Sky Blue
                explosionType: 'double-spiral',
                size: 'large',
                launchTime: 62000,
            },
            {
                left: '85%',
                color: '#F64CFF', // Hot Pink
                explosionType: 'star',
                size: 'large',
                launchTime: 62000,
            },
            {
                left: '95%',
                color: '#FF4C7D', // Hot Pinkish Red
                explosionType: 'flurry',
                size: 'large',
                launchTime: 62000,
            },
            // add more if you want
        ];

        // -------------------------
        // MAIN LOGIC
        // -------------------------
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.createElement('div');
            container.id = 'fireworks-container';
            document.body.appendChild(container);

            let fireworksTimeouts = [];
            let showTimeout = null;
            let isPaused = false;

            function clearFireworks() {
                fireworksTimeouts.forEach(timeout => clearTimeout(timeout));
                fireworksTimeouts = [];
                clearTimeout(showTimeout);
            }

            function startFireworksShow() {
                if (document.hidden) return; // Don't start if tab is not active

                container.innerHTML = '';

                // Schedule rockets
                fireworksData.forEach((data) => {
                    const t = setTimeout(() => {
                        if (!document.hidden) {
                            launchRocket(container, data);
                        }
                    }, data.launchTime);
                    fireworksTimeouts.push(t);
                });

                const maxLaunchTime = Math.max(...fireworksData.map((d) => d.launchTime));
                const finaleTime = maxLaunchTime + 4000;

                const finale = setTimeout(() => {
                    if (!document.hidden) {
                        launchGrandFinaleRocket(container);
                    }

                    // Restart the show after a short pause, if still in focus
                    showTimeout = setTimeout(startFireworksShow, 10000);
                }, finaleTime);

                fireworksTimeouts.push(finale);
            }

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    isPaused = true;
                    clearFireworks(); // Pause scheduled events
                } else if (isPaused) {
                    isPaused = false;
                    startFireworksShow(); // Resume
                }
            });

            startFireworksShow(); // Start first show
        });

        // -------------------------
        // 3) Normal Rocket + Explosion
        // -------------------------
        function launchRocket(container, { left, color, explosionType, size }) {
            // Create rocket element
            const rocketEl = document.createElement('div');
            rocketEl.className = 'firework-rocket';
            rocketEl.style.left = left;

            const rocketInner = document.createElement('div');
            rocketInner.className = 'firework-rocket-inner';
            rocketInner.style.backgroundColor = color;
            rocketEl.appendChild(rocketInner);
            container.appendChild(rocketEl);

            // Random apex between 40vh and 80vh
            const apex = 40 + Math.random() * 40;
            // Random travel time between 1500 and 2000 ms
            const travelTime = 1500 + Math.random() * 500;

            // Animate rocket going up
            rocketEl.animate(
                [
                    { transform: 'translate(-50%, 0)' },
                    { transform: `translate(-50%, -${apex}vh)` },
                ],
                {
                    duration: travelTime,
                    easing: 'ease-out',
                    fill: 'forwards',
                }
            );

            // Create rocket sparks in an interval
            const trailInterval = setInterval(() => {
                createSpark(container, rocketEl, color);
            }, 60);

            // Explode after reaching apex
            setTimeout(() => {
                clearInterval(trailInterval);
                explode(container, rocketEl, color, explosionType, size);
            }, travelTime);
        }

        function explode(container, rocketEl, color, explosionType, size) {
            // Get rocket position
            const rect = rocketEl.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;
            rocketEl.remove();

            // Determine how many fragments
            let fragmentCount;
            if (size === 'small') fragmentCount = 30;
            else if (size === 'large') fragmentCount = 80;
            else fragmentCount = 50;

            // Get pattern
            const pattern = getExplosionPattern(explosionType);

            // Create explosion fragments
            for (let i = 0; i < fragmentCount; i++) {
                const angle = pattern.angles && pattern.angles.length
                ? pattern.angles[i % pattern.angles.length]
                : Math.random() * 2 * Math.PI;

                const magnitude = pattern.magnitude && pattern.magnitude.length
                ? pattern.magnitude[i % pattern.magnitude.length]
                : 1;

                createFragment(container, centerX, centerY, color, angle, size, magnitude);
            }
        }

        function createFragment(container, x, y, color, angle, size, magnitude) {
        const fragment = document.createElement('div');
        fragment.className = 'firework-fragment';
        fragment.style.backgroundColor = color;
        fragment.style.left = `${x}px`;
        fragment.style.top = `${y}px`;
        container.appendChild(fragment);

        // Speed based on size
        const baseVelocity = size === 'small' ? 2 : size === 'large' ? 4 : 3;
        const velocity = baseVelocity * magnitude;
        const offsetX = Math.cos(angle) * velocity * 100;
        const offsetY = Math.sin(angle) * velocity * 100;
        const duration = 2000 + Math.random() * 800;

        fragment.animate(
            [
                { transform: 'translate(0,0) scale(1)', opacity: 1 },
                { transform: `translate(${offsetX}px, ${offsetY}px) scale(0.3)`, opacity: 0 },
            ],
            {
                duration,
                easing: 'ease-out',
                fill: 'forwards',
            }
        );

        setTimeout(() => {
                fragment.remove();
            }, duration + 100);
        }

        // --------------------------------------------
        // 4) Explosion Patterns (including 15 new ones)
        // --------------------------------------------
        function getExplosionPattern(type) {
        // Original "circle" pattern
        if (type === 'circle') {
            const angles = Array.from({ length: 30 }, (_, i) => (i / 30) * 2 * Math.PI);
            return { angles };
        }

        // Original "star" pattern
        if (type === 'star') {
            const angles = [];
            for (let i = 0; i < 15; i++) {
            angles.push((i / 15) * 2 * Math.PI);
            angles.push(((i + 0.2) / 15) * 2 * Math.PI);
            }
            return { angles };
        }

        // -------------------------------------------------------------------
        // 15 NEW PATTERNS:
        // -------------------------------------------------------------------
        if (type === 'double-spiral') {
            const angles = [];
            for (let i = 0; i < 40; i++) {
            angles.push((i / 10) * Math.PI);
            }
            const magnitude = angles.map((val, idx) => (idx % 2 === 0 ? 1 : 2));
            return { angles, magnitude };
        }

        if (type === 'cross') {
            const baseAngles = [0, Math.PI / 2, Math.PI, (3 * Math.PI) / 2];
            const repeated = [];
            const repeats = 10;
            for (let r = 0; r < repeats; r++) {
            repeated.push(...baseAngles);
            }
            return { angles: repeated };
        }

        if (type === 'swirl') {
            const angles = [];
            for (let i = 0; i < 60; i++) {
            angles.push(i * 0.2);
            }
            const magnitude = angles.map((_, i) => 0.5 + i * 0.05);
            return { angles, magnitude };
        }

        if (type === 'flower') {
            const angles = [];
            const magnitude = [];
            for (let i = 0; i < 36; i++) {
            angles.push((2 * Math.PI * i) / 36);
            magnitude.push(i % 2 === 0 ? 1.2 : 0.7);
            }
            return { angles, magnitude };
        }

        if (type === 'heart') {
            const angles = [];
            for (let i = 0; i < 50; i++) {
            // parametric approximation for a heart shape
            const t = (i / 25) * Math.PI;
            angles.push(t);
            }
            const magnitude = angles.map(() => 1 + Math.random() * 1);
            return { angles, magnitude };
        }

        if (type === 'ring-of-rings') {
            const angles = [];
            const magnitude = [];
            for (let ring = 1; ring <= 3; ring++) {
            for (let i = 0; i < 20; i++) {
                angles.push((2 * Math.PI * i) / 20);
                magnitude.push(ring);
            }
            }
            return { angles, magnitude };
        }

        if (type === 'diamond') {
            const baseAngles = [
            Math.PI / 4,
            (3 * Math.PI) / 4,
            (5 * Math.PI) / 4,
            (7 * Math.PI) / 4,
            ];
            const angles = [];
            for (let i = 0; i < 10; i++) {
            angles.push(...baseAngles);
            }
            return { angles };
        }

        if (type === 'hexagon') {
            const angles = [];
            const baseAngles = [
            0,
            Math.PI / 3,
            (2 * Math.PI) / 3,
            Math.PI,
            (4 * Math.PI) / 3,
            (5 * Math.PI) / 3,
            ];
            for (let i = 0; i < 10; i++) {
            angles.push(...baseAngles);
            }
            return { angles };
        }

        if (type === 'spiral') {
            const angles = [];
            for (let i = 0; i < 50; i++) {
            angles.push(i * 0.3);
            }
            const magnitude = angles.map((_, i) => 0.4 + i * 0.1);
            return { angles, magnitude };
        }

        if (type === 'flurry') {
            const angles = Array.from({ length: 60 }, () => Math.random() * 2 * Math.PI);
            const magnitude = angles.map(() => 0.5 + Math.random() * 1.5);
            return { angles, magnitude };
        }

        if (type === 'triple-star') {
            const angles = [];
            for (let s = 0; s < 3; s++) {
            for (let i = 0; i < 15; i++) {
                angles.push((i / 15) * 2 * Math.PI);
                angles.push(((i + 0.2) / 15) * 2 * Math.PI);
            }
            }
            return { angles };
        }

        if (type === 'random-burst') {
            const angles = Array.from({ length: 50 }, () => Math.random() * 2 * Math.PI);
            const magnitude = Array.from({ length: 50 }, () => 0.5 + Math.random() * 2);
            return { angles, magnitude };
        }

        if (type === 'wave') {
            const angles = [];
            const magnitude = [];
            for (let i = 0; i < 40; i++) {
            const a = (i / 40) * 2 * Math.PI;
            angles.push(a);
            magnitude.push(1 + Math.sin(a * 4));
            }
            return { angles, magnitude };
        }

        // Default random scatter if none is recognized
        const angles = Array.from({ length: 30 }, () => Math.random() * 2 * Math.PI);
        const magnitude = Array.from({ length: 30 }, () => 0.5 + Math.random() * 1.5);
        return { angles, magnitude };
        }

        // -------------------------
        // 5) Finale Rocket + Explosion
        // -------------------------
        function launchGrandFinaleRocket(container) {
            const left = '50%';
            const color = '#ddd';

            const rocketEl = document.createElement('div');
            rocketEl.className = 'firework-rocket';
            rocketEl.style.left = left;

            const rocketInner = document.createElement('div');
            rocketInner.className = 'firework-rocket-inner';
            rocketInner.style.backgroundColor = color;
            rocketEl.appendChild(rocketInner);
            container.appendChild(rocketEl);

            // Slight arc
            const driftX = (Math.random() - 0.5) * 40;
            const travelTime = 2200;
            rocketEl.animate(
                [
                { offset: 0,   transform: 'translate(-50%, 0)' },
                { offset: 0.3, transform: `translate(calc(-50% + ${driftX / 2}px), -20vh)` },
                { offset: 0.6, transform: `translate(calc(-50% + ${driftX}px), -45vh)` },
                { offset: 1,   transform: 'translate(-50%, -70vh)' },
                ],
                {
                duration: travelTime,
                easing: 'cubic-bezier(0.25, 0.45, 0.45, 0.95)',
                fill: 'forwards',
                }
            );

            const trailInterval = setInterval(() => {
                createSpark(container, rocketEl, color);
            }, 60);

            setTimeout(() => {
                clearInterval(trailInterval);
                bigSlowExplosion(container, rocketEl);
            }, travelTime);
        }

        function createSpark(container, rocketEl, color) {
            const rect = rocketEl.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;

            const spark = document.createElement('div');
            spark.className = 'firework-spark';
            spark.style.backgroundColor = color;
            spark.style.left = `${centerX}px`;
            spark.style.top = `${centerY}px`;
            container.appendChild(spark);

            spark.animate(
                [
                    { transform: 'translate(0,0)', opacity: 1 },
                    { transform: 'translate(0, 15px)', opacity: 0 },
                ],
                {
                    duration: 500,
                    easing: 'ease-out',
                    fill: 'forwards',
                }
            );

            setTimeout(() => {
                spark.remove();
            }, 600);
        }

        // -------------------------
        // 6) "Slow" & Long Finale Boom
        // -------------------------
        function bigSlowExplosion(container, rocketEl) {
            const rect = rocketEl.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;
            rocketEl.remove();

            // Increase fragment count + bigger radius
            const fragmentCount = 500;

            for (let i = 0; i < fragmentCount; i++) {
                const angle = Math.random() * 2 * Math.PI;
                // Large radius: 300 - 700
                const radius = 300 + Math.random() * 400;
                // Big downward drift: 700 - 1200
                const rainDistance = 700 + Math.random() * 500;

                const targetX = Math.cos(angle) * radius;
                const targetY = Math.sin(angle) * radius;

                const fragment = document.createElement('div');
                fragment.className = 'firework-fragment';
                fragment.style.backgroundColor = '#ddd';
                fragment.style.left = `${centerX}px`;
                fragment.style.top = `${centerY}px`;
                fragment.style.width = '3px';
                fragment.style.height = '3px';
                fragment.style.borderRadius = '50%';
                container.appendChild(fragment);

                // Very slow: 12 - 18 seconds
                const animDuration = 12000 + Math.random() * 6000;

                // Optional rotations (set to 0 for simplicity)
                const rotateStart = 0;
                const rotateEnd = 0;

                fragment.animate(
                [
                    // 0%: start
                    {
                    offset: 0,
                    transform: `translate(0,0) scale(0) rotate(${rotateStart}deg)`,
                    opacity: 0,
                    },
                    // 10%: big "boom"
                    {
                    offset: 0.1,
                    transform: `translate(${targetX * 0.8}px, ${targetY * 0.8}px)
                                scale(2) rotate(${rotateStart}deg)`,
                    opacity: 1,
                    },
                    // 20%: full radius
                    {
                    offset: 0.2,
                    transform: `translate(${targetX}px, ${targetY}px)
                                scale(1.7) rotate(${rotateEnd}deg)`,
                    opacity: 1,
                    },
                    // 35%: drifting downward
                    {
                    offset: 0.35,
                    transform: `translate(${targetX}px, ${targetY + rainDistance * 0.1}px)
                                scale(1.3) rotate(${rotateEnd}deg)`,
                    opacity: 0.95,
                    },
                    // 50%
                    {
                    offset: 0.5,
                    transform: `translate(${targetX}px, ${targetY + rainDistance * 0.3}px)
                                scale(1.1) rotate(${rotateEnd}deg)`,
                    opacity: 0.8,
                    },
                    // 65%
                    {
                    offset: 0.65,
                    transform: `translate(${targetX}px, ${targetY + rainDistance * 0.55}px)
                                scale(0.9) rotate(${rotateEnd}deg)`,
                    opacity: 0.6,
                    },
                    // 80%
                    {
                    offset: 0.8,
                    transform: `translate(${targetX}px, ${targetY + rainDistance * 0.8}px)
                                scale(0.8) rotate(${rotateEnd}deg)`,
                    opacity: 0.3,
                    },
                    // 100%: end
                    {
                    offset: 1,
                    transform: `translate(${targetX}px, ${targetY + rainDistance}px)
                                scale(0.6) rotate(${rotateEnd}deg)`,
                    opacity: 0,
                    },
                ],
                {
                    duration: animDuration,
                    easing: 'cubic-bezier(0.25, 0.5, 0.25, 1)',
                    fill: 'forwards',
                }
                );

                setTimeout(() => {
                    fragment.remove();
                }, animDuration + 500);
            }
        }
    </script>
@endsection
