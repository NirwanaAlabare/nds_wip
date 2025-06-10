@extends('layouts.index', ["navbar" => false, "footer" => false, "containerFluid" => true])

@section('custom-link')
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

    <style>
        .content {
            padding: 0 !important;
        }

        .container-fluid {
            padding: 0 !important;
        }

        .content-wrapper.pt-3 {
            padding: 0 !important;
            overflow: hidden !important;
        }

        /* SWIPER */
        swiper-container {
            width: 100vw;
            height: 100vh;
            background-color: inherit;
            position: relative;
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
        .custom-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            min-width: 100px;
            width: 10vw;
            height: 100%;
            background: transparent;
            cursor: pointer;
        }

        .custom-prev {
            left: 0;
        }

        .custom-next {
            right: 0;
        }
    </style>
@endsection

@section('content')
    <swiper-container
        class="mySwiper"
        direction="horizontal"
        autoplay-delay="30000"
        autoplay-disable-on-interaction="false"
    >

        <swiper-slide id="carousel-1">
            <iframe src="{{ route("dashboard-factory-daily-sewing") }}/{{ $year }}/{{ $month }}" frameborder="0" class="iframe-el" style="width: 100%; height: 100%; border: none;"></iframe>
        </swiper-slide>
        <swiper-slide id="carousel-2">
            <iframe src="{{ route("dashboard-chief-sewing") }}/{{ $year }}/{{ $month }}" frameborder="0" class="iframe-el" style="width: 100%; height: 100%; border: none;"></iframe>
        </swiper-slide>
        <swiper-slide id="carousel-3">
            <iframe src="{{ route("dashboard-top-chief-sewing") }}/{{ $year }}/{{ $month }}" frameborder="0" class="iframe-el" style="width: 100%; height: 100%; border: none;"></iframe>
        </swiper-slide>
        <swiper-slide id="carousel-4">
            <iframe src="{{ route("dashboard-top-leader-sewing") }}/{{ $year }}/{{ $month }}" frameborder="0" class="iframe-el" style="width: 100%; height: 100%; border: none;"></iframe>
        </swiper-slide>

        <!-- Custom buttons -->
        <div class="custom-prev custom-nav" slot="container-start"></div>
        <div class="custom-next custom-nav" slot="container-start"></div>
    </swiper-container>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <!-- Apex Charts -->
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>

    <!-- Swiper  -->
    <script src="{{ asset('plugins/swiper/js/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('plugins/swiper/js/swiper-element-bundle.min.js') }}"></script>

    <script>
        $('.select2').select2();
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });

        document.getElementById("loading").classList.remove("d-none");

        $('document').ready(async () => {
            const swiperEl = document.querySelector('.mySwiper');
            const swiper = swiperEl.swiper;

            document.querySelector('.custom-next').addEventListener('click', () => {
                console.log("swiper next", swiper.isEnd)
                if (swiper.isEnd) {
                    swiper.slideTo(0); // Go to first slide
                } else {
                    swiper.slideNext();
                }
            });

            document.querySelector('.custom-prev').addEventListener('click', () => {
                console.log("swiper prev", swiper.isBeginning)
                if (swiper.isBeginning) {
                    swiper.slideTo(3); // Go to last slide
                } else {
                    swiper.slidePrev();
                }
            });

            // on ready
            document.getElementById("loading").classList.add("d-none");

            // Set Custom Dashboard View
            document.body.style.maxHeight = "100vh";
            document.body.style.overflow = "hidden";
        });
    </script>
@endsection
