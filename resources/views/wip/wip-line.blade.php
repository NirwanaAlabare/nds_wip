<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NDS - WIP</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('dist/img/tabicon.png') }}">
    {{-- dashboard TV: hanya muat yang dipakai, bukan layouts.link (jquery-ui, sweetalert, livewire, dll) --}}
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free-6.4.2/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/style.css') }}">

</head>

<style>
    .card {
        background-color: #072c66;
    }

    .card p {
        color: #FFFFFF;
    }

    .card-title {
        font-size: 25px;
        font-weight: bold;
        letter-spacing: -1px;
    }

    .card-text {
        font-size: 20px;
        font-weight: 400;
        color: #d1d5db !important;
        letter-spacing: -1px;
    }

    .card-body {
        line-height: 25px;
    }

    .table th {
        background-color: #072c66;
        color: #FFFFFF;
    }

    .table tr {
        background-color: rgb(255, 255, 255);
        color: #FFFFFF;
    }

    .table td {
        background-color: #072c66;
        color: #FFFFFF;
    }

    .thead-custom {
        background-color: #FFFFFF !important;

    }

    .thead-custom th {
        border-color: lightgray !important;
        background-color: #FFFFFF !important;
        font-size: 20px !important;
    }

    .tbody-custom td {
        font-size: 19px !important;
        font-weight: bold !important;
    }

    /* tabel per jam mengisi penuh tinggi card, sisa tinggi dibagi rata ke tiap baris */
    .hourly-table {
        flex: 1 1 auto;
        height: 100%;
    }

    .hourly-table td {
        vertical-align: middle !important;
    }

    /* baris jam berjalan */
    .hourly-table tr.hourly-current td {
        background-color: #1a56b8 !important;
        border-top: 3px solid #ffc107 !important;
        border-bottom: 3px solid #ffc107 !important;
    }

    .hourly-table tr.hourly-current td:first-child {
        border-left: 3px solid #ffc107 !important;
    }

    .hourly-table tr.hourly-current td:last-child {
        border-right: 3px solid #ffc107 !important;
    }

    .hourly-variation {
        position: relative;
    }

    .hourly-variation i {
        position: absolute;
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
        margin-left: 6px;
        line-height: 1;
    }


    .show-defect-area {
        z-index: 9999;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
    }

    .show-defect-area .defect-area-img-container {
        position: relative;
        display: inline-block;
        justify-content: center;
        align-items: center;
    }

    .show-defect-area .defect-area-img-container .defect-area-img {
        width: auto;
        height: 500px;
    }

    .show-defect-area .defect-area-img-container .defect-area-img-point {
        position: absolute;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        opacity: 80%;
        z-index: 99999;
    }


    /* SWIPPER */

    swiper-container {
        width: 100%;
        height: 100%;
        background-color: #ffffff !important;
    }

    swiper-slide {
        text-align: left;
        font-size: 18px;
        background: #ffffff;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    swiper-slide img {
        display: block;
        width: 100%;
        height: 100%;
        /* object-fit: cover; */
    }

    /* AUTO ZOOM: konten didesain di lebar 1300px lalu di-zoom mengikuti layar */
    .wip-scale {
        width: 1300px;
        flex: 0 0 auto;
        transform-origin: top center;
    }

    #chartdiv-efficiency {
        width: 100%;
        height: 220px;
    }

    #chartdiv-rft {
        width: 100%;
        height: 210px;
        margin-top: 15px;
    }

    #chartdiv-deffect {
        width: 100%;
        height: 210px;
        margin-top: 15px;
    }

    #defno1text::after {
        content: '';
        display: inline-block;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        background: #ec3032;
        top: 10px;
        margin: 0 10px;
    }

    #defno2text::after {
        content: '';
        display: inline-block;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        background: #fd8024;
        top: 10px;
        margin: 0 10px;
    }

    #defno3text::after {
        content: '';
        display: inline-block;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        background: #fffb45;
        top: 10px;
        margin: 0 10px;
    }

    #defno4text::after {
        content: '';
        display: inline-block;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        background: #2bff6b;
        top: 10px;
        margin: 0 10px;
    }

    #defno5text::after {
        content: '';
        display: inline-block;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        background: whitesmoke;
        top: 10px;
        margin: 0 10px;
    }

    /* Container to hide overflow text */
    .marquee-container {
        overflow: hidden;
        position: relative;
        width: 100%; /* Adjust to the width of the parent container */
        height: 25px; /* Adjust based on text size */
    }

    /* Moving text */
    .marquee {
        display: inline-block;
        white-space: nowrap; /* Prevent text wrapping */
        position: absolute;
        left: 10%;
        animation: marquee-infinite 30s linear infinite; /* Continuous scrolling without pause */
    }

    /* Keyframes for smooth continuous scrolling */
    @keyframes marquee-infinite {
        0% {
            transform: translateX(0%); /* Start outside of the container (right) */
        }
        100% {
            transform: translateX(-100%); /* End outside of the container (left) */
        }
    }

</style>

<body class=" d-flex justify-content-center align-items-center"
    style="min-height: 100vh; background-color: #ffffff; font-family: 'Inter', sans-serif;">
    <swiper-container class="mySwiper" autoplay-delay="15000" autoplay-disable-on-interaction="false" space-between="30"
        centered-slides="true">
        <swiper-slide>
            <div class="p-1 d-flex justify-content-center align-items-start"
                style="width: 100%; height: 100vh; background-color: #ffffff;">
                <div class="row wip-scale">
                    <div class="col-md-1">
                        <div class="card"
                            style="height: 100px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center;">
                            <img src="/nds_wip/public/assets/dist/img/logo-nds4.png" alt="AdminLTE Logo" class=""
                                style="height: 60px; width: 60px;">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card" style="height: 100px;">
                            <div class="card-body"
                                style="display: flex; text-align: start; justify-content: center; flex-direction: column;">
                                <p class="card-title mt-2" id="current-time"></p>
                                <p class="card-text" id="user-name">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card" style="height: 100px; overflow: hidden;">
                            <div class="card-body"
                                style="display: flex; text-align: start; justify-content: center; flex-direction: column;">
                                <div id="buyer-text">
                                    <p class="card-title" id="buyer-name">-</p>
                                </div>
                                <div id="ws-text">
                                    <p class="card-text" id="buyer-id">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card d-flex justify-content-center align-items-center" style="height: 100px;">
                            <div class="card-body"
                                style="display: flex; text-align: start; justify-content: center; flex-direction: column;">
                                <p class="card-title" id="realtime-diff" style="font-size: 27px; line-height: 30px;">
                                    0 Hours 0 Minutes
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-9">
                        <div class="h-100 card">
                            <div class="card-body d-flex flex-column">
                                <table class="table border-collapse hourly-table mb-0"
                                    style="background-color: #072c66; text-align: center;border-radius: 10px;">
                                    <thead class="thead-custom">
                                        <tr>
                                            <th scope="col"
                                                style="border-color: lightgray; border-top-left-radius: 10px; color: #072c66;">
                                                Hours</th>
                                            <th scope="col" style="border-color: lightgray; color: #072c66;">Target
                                            </th>
                                            <th scope="col" style="border-color: lightgray; color: #072c66;">Output
                                            </th>
                                            <th scope="col" style="border-color: lightgray; color: #072c66;">
                                                Variation</th>
                                            <th scope="col" style="border-color: lightgray; color: #072c66;">
                                                Efficiency</th>
                                            <th scope="col"
                                                style="border-color: lightgray; border-top-right-radius: 10px; color: #072c66;">
                                                Defect Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody class="tbody-custom" id="hourly-table-body">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="h-100 card">
                            <div class="card-body">
                                <div>
                                    <div class=""
                                        style="height: 40.7px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                        <h5 style="font-size: 18px; color:#072c66; font-weight: bold">
                                            Target
                                        </h5>
                                    </div>
                                    <div class=""
                                        style="height: 40.7px; background-color: #072c66; display: flex; align-items: center; justify-content: center; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; border-top: 0px; border-bottom: 3px solid #FFFFFF; border-left: 3px solid #FFFFFF; border-right: 3px solid #FFFFFF; color:#FFFFFF;">
                                        <h5 id="day_target1"
                                            style="font-size: 20px; color:#FFFFFF; font-weight: bold">

                                        </h5>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class=""
                                        style="height: 40.7px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                        <h5 style="font-size: 18px; color:#072c66; font-weight: bold">
                                            Output
                                        </h5>
                                    </div>
                                    <div class=""
                                        style="height: 40.7px; background-color: #072c66; display: flex; align-items: center; justify-content: center; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; border-top: 0px; border-bottom: 3px solid #FFFFFF; border-left: 3px solid #FFFFFF; border-right: 3px solid #FFFFFF; color:#FFFFFF;">
                                        <h5 id="actuall1" style="font-size: 20px; color:#FFFFFF; font-weight: bold">

                                        </h5>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class=""
                                        style="height: 40.7px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                        <h5 style="font-size: 18px; color:#072c66; font-weight: bold">
                                            Variation
                                        </h5>
                                    </div>
                                    <div class=""
                                        style="height: 40.7px; background-color: #072c66; display: flex; align-items: center; justify-content: center; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; border-top: 0px; border-bottom: 3px solid #FFFFFF; border-left: 3px solid #FFFFFF; border-right: 3px solid #FFFFFF; color:#FFFFFF;">
                                        <h5 id="variation1" style="font-size: 20px; color:#FFFFFF; font-weight: bold">

                                        </h5>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class=""
                                        style="height: 40.7px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                        <h5 style="font-size: 18px; color:#072c66; font-weight: bold">
                                            Efficiency
                                        </h5>
                                    </div>
                                    <div class=""
                                        style="height: 40.7px; background-color: #072c66; display: flex; align-items: center; justify-content: center; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; border-top: 0px; border-bottom: 3px solid #FFFFFF; border-left: 3px solid #FFFFFF; border-right: 3px solid #FFFFFF; color:#FFFFFF;">
                                        <h5 id="efficiency1"
                                            style="font-size: 20px; color:#FFFFFF; font-weight: bold">
                                        </h5>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class=""
                                        style="height: 40.7px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                        <h5 style="font-size: 18px; color:#072c66; font-weight: bold">
                                            Defect Rate
                                        </h5>
                                    </div>
                                    <div class=""
                                        style="height: 40.7px; background-color: #072c66; display: flex; align-items: center; justify-content: center; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; border-top: 0px; border-bottom: 3px solid #FFFFFF; border-left: 3px solid #FFFFFF; border-right: 3px solid #FFFFFF; color:#FFFFFF;">
                                        <h5 id="deffect1" style="font-size: 20px; color:#FFFFFF; font-weight: bold">
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </swiper-slide>
        <swiper-slide>
            <div class="p-1 d-flex justify-content-center align-items-start"
                style="width: 100%; height: 100vh; background-color: #ffffff;">
                <div class="row wip-scale">
                    <div class="col-md-1">
                        <div class="card"
                            style="height: 100px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center;">
                            <img src="/nds_wip/public/assets/dist/img/logo-nds4.png" alt="AdminLTE Logo"
                                class="" style="height: 60px; width: 60px;">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card" style="height: 100px;">
                            <div class="card-body"
                                style="display: flex; text-align: start; justify-content: center; flex-direction: column;">
                                <p class="card-title" style="font-size: 23px" id="current-time-2"></p>
                                <p class="card-text" id="user-name-2"></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card" style="height: 100px;">
                            <div class="card-body"
                                style="display: flex; text-align: start; justify-content: center; flex-direction: column;">
                                <div id="buyer-text-2">
                                    <p class="card-title" id="buyer-name-2">-</p>
                                </div>
                                <div id="ws-text-2">
                                    <p class="card-text" id="buyer-id-2">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card d-flex justify-content-center align-items-center" style="height: 100px;">
                            <div class="card-body"
                                style="display: flex; text-align: start; justify-content: center; flex-direction: column;">
                                <p class="card-title" id="realtime-diff-2"
                                    style="font-size: 27px; line-height: 30px;">
                                    0 Hours 0 Minutes
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card px-4 py-3"
                            style="height: 220px; background-color: #FFFFFF; flex-direction: column; gap: 1px; justify-content: center;">
                            <div style="display: flex; align-items: center; justify-content: center;">
                                <div
                                    style="height: 80px; width: 100px; background-color: #DA3EBF; display: flex; align-items: center; justify-content: center; border-radius: 20%;">
                                    <img src="/nds_wip/public/assets/dist/img/icon/checked.png" alt="AdminLTE Logo"
                                        class="" style="height: 40px; width: 40px;">
                                </div>
                                <div
                                    style="display: flex; flex-direction: column; width: 100%; margin-left:20px; margin-vertical:0px; padding-vertical:0px">
                                    <div>
                                        <div style="display: flex; width: 100%; justify-content: space-between">
                                            <p class="card-title mt-3"
                                                style="font-size: 50px; color: #282828; line-height: 40px;"
                                                id="actual-2"></p>
                                            <div class="shadow" id="variation-box"
                                                style="display: flex; justify-content: center; align-items: center; width: auto; height: 40px; border-top-left-radius: 25px; border-bottom-left-radius: 25px; border-top-right-radius: 25px; border-bottom-right-radius: 25px; padding:10px; border: 1px solid #3ee706">
                                                <p class="card-title"
                                                    style="font-size: 28px; color: #282828; line-height: 0px;"
                                                    id="variation-page-2"></p>
                                            </div>
                                        </div>
                                    </div>
                                    <p style="color: #64748b; font-size: 19px;">ACTUAL</p>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: center;">
                                <div
                                    style="height: 80px; width: 100px; background-color: #1AD87F; display: flex; align-items: center; justify-content: center; border-radius: 20%;">
                                    <img src="/nds_wip/public/assets/dist/img/icon/target.png" alt="AdminLTE Logo"
                                        class="" style="height: 40px; width: 40px;">
                                </div>
                                <div
                                    style="display: flex; flex-direction: column; width: 40%; margin-left:20px; margin-vertical:0px; padding-vertical:0px">
                                    <p class="card-title mt-3"
                                        style="font-size: 40px; color: #282828; line-height: 40px;" id="day-target-2">
                                    </p>
                                    <p style="color: #64748b; font-size: 16px;">DAY TARGET</p>
                                </div>
                                <div
                                    style="display: flex; flex-direction: column; width: 60%; margin-left:20px; margin-vertical:0px; padding-vertical:0px">
                                    <p class="card-title mt-3"
                                        style="font-size: 40px; color: #282828; line-height: 40px;"
                                        id="cumulative-target-2"></p>
                                    <p style="color: #64748b; font-size: 16px;">REALTIME TARGET</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card px-4 py-3"
                            style="height: 220px; background-color: #FFFFFF; flex-direction: column; gap: 1px; justify-content: center;">
                            <div style="display: flex; align-items: center; justify-content: center;">
                                <div
                                    style="height: 80px; width: 100px; background-color: #1A5CD8; display: flex; align-items: center; justify-content: center; border-radius: 20%;">
                                    <img src="/nds_wip/public/assets/dist/img/icon/checked.png" alt="AdminLTE Logo"
                                        class="" style="height: 40px; width: 40px;">
                                </div>
                                <div
                                    style="display: flex; flex-direction: column; width: 100%; margin-left:20px; margin-vertical:0px; padding-vertical:0px">
                                    <div>
                                        <div style="display: flex; width: 100%; justify-content: space-between">
                                            <p class="card-title mt-3"
                                                style="font-size: 50px; color: #282828; line-height: 40px;"
                                                id="actual-hour-now"></p>
                                            <div class="shadow" id="variation-box2"
                                                style="display: flex; justify-content: center; align-items: center; width: auto; height: 40px; border-top-left-radius: 25px; border-bottom-left-radius: 25px; border-top-right-radius: 25px; border-bottom-right-radius: 25px; padding:10px; border: 1px solid #3ee706">
                                                <p class="card-title"
                                                    style="font-size: 28px; color: #282828; line-height: 0px;"
                                                    id="variation-hour-now"></p>
                                            </div>
                                        </div>
                                        <p style="color: #64748b; font-size: 19px;">HOUR OUTPUT</p>
                                    </div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: center;">
                                <div
                                    style="height: 80px; width: 100px; background-color: #1AD87F; display: flex; align-items: center; justify-content: center; border-radius: 20%;">
                                    <img src="/nds_wip/public/assets/dist/img/icon/target.png" alt="AdminLTE Logo"
                                        class="" style="height: 40px; width: 40px;">
                                </div>
                                <div
                                    style="display: flex; flex-direction: column; width: 100%; margin-left:20px; margin-vertical:0px; padding-vertical:0px">
                                    <p class="card-title mt-3"
                                        style="font-size: 50px; color: #282828; line-height: 40px;"
                                        id="req-hour-target-2"></p>
                                    <p style="color: #64748b; font-size: 19px;">REQ HOUR TARGET</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">

                        <div class="card px-4 py-3"
                            style="height: 220px; background-color: #FFFFFF; flex-direction: column; gap: 1px; justify-content: center;">
                            <div style="display: flex; align-items: center; justify-content: center;">
                                <div
                                    style="height: 80px; width: 100px; background-color: #F3B03B; display: flex; align-items: center; justify-content: center; border-radius: 20%;">
                                    <img src="/nds_wip/public/assets/dist/img/icon/checked.png" alt="AdminLTE Logo"
                                        class="" style="height: 40px; width: 40px;">
                                </div>
                                <div
                                    style="display: flex; flex-direction: column; width: 100%; margin-left:20px; margin-vertical:0px; padding-vertical:0px">
                                    <p class="card-title mt-3"
                                        style="font-size: 50px; color: #282828; line-height: 40px;"
                                        id="deffect-garment-qty-2"></p>
                                    <p style="color: #64748b; font-size: 16px;">DEFECT GARMENT QTY</p>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: center;">
                                <div
                                    style="height: 80px; width: 100px; background-color: #1AD87F; display: flex; align-items: center; justify-content: center; border-radius: 20%;">
                                    <img src="/nds_wip/public/assets/dist/img/icon/target.png" alt="AdminLTE Logo"
                                        class="" style="height: 40px; width: 40px;">
                                </div>
                                <div
                                    style="display: flex; flex-direction: column; width: 100%; margin-left:20px; margin-vertical:0px; padding-vertical:0px">
                                    <p class="card-title mt-3"
                                        style="font-size: 50px; color: #282828; line-height: 40px;"
                                        id="rework-balance-2"></p>
                                    <p style="color: #64748b; font-size: 19px;">REWORK OUTPUT</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card px-4" style="height: 250px; background-color: #FFFFFF; position: relative;">
                            <div
                                style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                <div id="chartdiv-efficiency"></div>
                                <div
                                    style="position: absolute; bottom: -18%; left: 49%;  transform: translate(-50%, -50%);">
                                    <p style="color: #000000; font-weight: bold; font-size: 20px; margin-top:20px;">
                                        EFFICIENCY</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card px-4" style="height: 250px; background-color: #FFFFFF; position: relative;">
                            <div
                                style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                <div id="chartdiv-rft"></div>
                                <div
                                    style="position: absolute; bottom: -18%; left: 50%;  transform: translate(-50%, -50%);">
                                    <p style="color: #000000; font-weight: bold; font-size: 20px; margin-top:20px;">RFT
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card px-4" style="height: 250px; background-color: #FFFFFF; position: relative;">
                            <div
                                style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                <div id="chartdiv-deffect"></div>
                                <div
                                    style="position: absolute; bottom: -18%; left: 50%;  transform: translate(-50%, -50%);">
                                    <p style="color: #000000; font-weight: bold; font-size: 20px; margin-top:20px;">
                                        DEFECT RATE</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </swiper-slide>
        <swiper-slide>
            <div class="p-1 d-flex justify-content-center align-items-start"
                style="width: 100%; height: 100vh; background-color: #ffffff;">
                <div class="row wip-scale">
                    <div class="col-md-1">
                        <div class="card"
                            style="height: 100px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center;">
                            <img src="/nds_wip/public/assets/dist/img/logo-nds4.png" alt="AdminLTE Logo"
                                class="" style="height: 60px; width: 60px;">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card" style="height: 100px;">
                            <div class="card-body"
                                style="display: flex; text-align: start; justify-content: center; flex-direction: column;">
                                <p class="card-title" style="font-size: 23px" id="current-time-3"></p>
                                <p class="card-text" id="user-name-3"></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card" style="height: 100px;">
                            <div class="card-body"
                                style="display: flex; text-align: start; justify-content: center; flex-direction: column;">
                                <div id="buyer-text-3">
                                    <p class="card-title" id="buyer-name-3">-</p>
                                </div>
                                <div id="ws-text-3">
                                    <p class="card-text" id="buyer-id-3">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card d-flex justify-content-center align-items-center" style="height: 100px;">
                            <div class="card-body"
                                style="display: flex; text-align: start; justify-content: center; flex-direction: column;">
                                <p class="card-title" id="realtime-diff-3"
                                    style="font-size: 27px; line-height: 30px;">
                                    0 Hours 0 Minutes
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="h-100 card">
                            <div class="card-body d-flex justify-content-center align-items-center">
                                <div class="show-defect-area" id="show-defect-area">
                                    <div
                                        class="position-relative d-flex flex-column justify-content-center align-items-center">
                                        <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
                                            <div class="carousel-inner" id="carousel-inner">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="h-100 card">
                            <div class="card-body">
                                <div class="p-0 m-0"
                                    style="height: 40px;  background-color: #FFFFFF; display: flex; align-items: center; justify-content: center; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                    <h5 style="font-size: 18px; color:#072c66; font-weight: bold;">
                                        LIST DEFECT
                                    </h5>
                                </div>
                                <div id="defect-list" class="px-0 w-100"
                                    style="text-align: left; background-color: #072c66; display: flex; flex-direction: column; align-items: center; justify-content: start; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; border-top: 0px; border-bottom: 3px solid #FFFFFF; border-left: 3px solid #FFFFFF; border-right: 3px solid #FFFFFF; color:#FFFFFF;">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </swiper-slide>
    </swiper-container>

    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>

<script src="{{ asset('plugins/swiper/js/swiper-element-bundle.min.js') }}"></script>

<!-- JSC CHART-->
<script src="{{ asset('plugins/jscharting/JSC/jscharting.js') }}"></script>

<script>
    // instance gauge dibuat sekali lalu nilainya di-update; destroy + buat ulang tiap refresh bikin memori bocor
    const gaugeCharts = {};

    // signature data carousel defect terakhir
    let lastCarouselSignature = null;

    function renderGauge(key, elementId, value, label, buildOptions) {
        const chart = gaugeCharts[key];
        if (chart) {
            if (chart.lastValue === value && chart.lastLabel === label) return;
            chart.series(0).points(0).options({ y: [0, value] });
            if (label !== chart.lastLabel) {
                chart.options({ defaultSeries: { shape: { label: { text: label } } } });
            }
        } else {
            const options = buildOptions();
            // ukuran eksplisit dari layout asli; tanpa ini JSC mengukur ukuran yang sudah di-scale (auto zoom)
            const el = document.getElementById(elementId);
            options.width = el.offsetWidth;
            options.height = el.offsetHeight;
            options.defaultSeries.shape.label.text = label;
            options.series[0].points = [['x', [0, value]]];
            gaugeCharts[key] = JSC.chart(elementId, options);
        }
        gaugeCharts[key].lastValue = value;
        gaugeCharts[key].lastLabel = label;
    }

    function hourlyTable1(data) {
        const rows = data.hourly || [];
        // jam istirahat = slot tanpa menit kerja yang masih diikuti jam kerja berikutnya
        const lastWorkIndex = rows.map(r => Number(r.menit_kerja) > 0).lastIndexOf(true);
        const cell = 'border-color: lightgray;font-size: 13px;';
        const breakCell = cell + ' background-color: #ff0000 !important;';

        // jam berjalan ikut periode_jam summary (istirahat -> jam kerja berikutnya, setelah shift -> 1 jam setelah shift);
        // kalau baris setelah shift tidak tampil karena belum ada output, tandai baris terakhir
        const currentStart = ((data.summary || {}).periode_jam || '').slice(0, 5);
        let currentIndex = currentStart ? rows.findIndex(r => r.hours.slice(0, 5) === currentStart) : -1;
        if (currentStart && currentIndex < 0) currentIndex = rows.length - 1;

        const html = rows.map((row, index) => {
            const isBreak = Number(row.menit_kerja) === 0 && index < lastWorkIndex;
            const rowClass = isBreak ? 'bg-danger' : (index === currentIndex ? 'hourly-current' : '');
            const style = isBreak ? breakCell : cell;
            const variation = Number(row.variation || 0);
            // angka tetap di tengah kolom & sejajar kolom lain, ikon menggantung di kanan tanpa menambah tinggi baris
            const variationHtml = isBreak ? '0' :
                `<span class="hourly-variation">${Math.abs(variation)}<i class="fa ${variation < 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variation < 0 ? 'red' : 'green'};"></i></span>`;

            return `
                <tr style="line-height: 10px;" class="${rowClass}">
                    <td scope="row" style="${style}">${row.hours}</td>
                    <td style="${style}">${row.target || 0}</td>
                    <td style="${style}">${row.output || 0}</td>
                    <td style="${style}">${variationHtml}</td>
                    <td style="${style}">${Number(row.efficiency || 0).toFixed(2)} %</td>
                    <td style="${style}">${Number(row.defect_rate || 0).toFixed(2)} %</td>
                </tr>`;
        }).join('');

        $("#hourly-table-body").html(html);
    }

    function summaryCard1(data) {
        const summary = data.summary || {};

        $("#day_target1").text(summary.target || 0);
        $("#actuall1").text(summary.tot_output || 0);

        // Menentukan ikon berdasarkan nilai variation
        const variation = Number(summary.variation || 0);
        const icon = variation > 0 ?
            '<i class="fa fa-caret-down" style="color: red;"></i>' :
            '<i class="fa fa-caret-up" style="color: green;"></i>';

        $("#variation1").html(`
            ${Math.abs(variation)} ${icon}
        `);
        $("#efficiency1").html(`
            ${summary.eff || 0}%
        `);
        $("#deffect1").html(`
            ${summary.def_rate || 0}%
        `);
    }

    function summaryCard2(data) {
        const summary = data.summary || {};

        $("#actual-2").text(summary.tot_output || 0);
        $("#day-target-2").text(summary.target || 0);
        $("#cumulative-target-2").text(summary.target_skrg || 0);
        $("#actual-hour-now").text(summary.output_jam_ini || 0);
        $("#req-hour-target-2").text(summary.req_hour_target || 0);
        $("#deffect-garment-qty-2").text(summary.tot_defect || 0);
        $("#rework-balance-2").text(summary.tot_reworked || 0);

        const variance = Number(summary.variation || 0);
        const variation_hour_now = Number(summary.req_hour_target || 0) - Number(summary.output_jam_ini || 0);
        const varianceDisplay = Math.abs(variance);
        const varianceDisplayHour = Math.abs(variation_hour_now);
        $("#variation-page-2").html(`
            ${varianceDisplay}
           <i class="fa ${variance > 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variance > 0 ? 'red' : '#3ee706'};"></i>
        `);

        $("#variation-box").css('border', variance > 0 ? '1px solid red' : '1px solid #3ee706');

        $("#variation-hour-now").html(`
            ${varianceDisplayHour}
           <i class="fa ${variation_hour_now > 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variation_hour_now > 0 ? 'red' : '#3ee706'};"></i>
        `);

        $("#variation-box2").css('border', variation_hour_now > 0 ? '1px solid red' : '1px solid #3ee706');
    }

    function showingalldata(data) {
        if (data.no_ws.length > 30) {
            $("#buyer-text").addClass("marquee-container");
            $("#buyer-text-2").addClass("marquee-container");
            $("#buyer-text-3").addClass("marquee-container");
            $("#ws-text").addClass("marquee-container");
            $("#ws-text-2").addClass("marquee-container");
            $("#ws-text-3").addClass("marquee-container");
            $("#buyer-name").addClass("marquee");
            $("#buyer-name-2").addClass("marquee");
            $("#buyer-name-3").addClass("marquee");
            $("#buyer-id").addClass("marquee");
            $("#buyer-id-2").addClass("marquee");
            $("#buyer-id-3").addClass("marquee");
        }

        $("#user-name").text((data.user || "Line").replace(/_/g, " ").replace(/\b\w/g, (char) => char.toUpperCase()));
        $("#buyer-name").text(data.buyer || "0");
        $("#buyer-id").text(data.no_ws || "0");
        hourlyTable1(data);
        summaryCard1(data);

        // PAGES 2
        $("#user-name-2").text((data.user || "Line").replace(/_/g, " ").replace(/\b\w/g, (char) => char.toUpperCase()));
        $("#buyer-name-2").text(data.buyer || "0");
        $("#buyer-id-2").text(data.no_ws || "0");
        summaryCard2(data);

        // PAGES 3
        $("#user-name-3").text((data.user || "Line").replace(/_/g, " ").replace(/\b\w/g, (char) => char.toUpperCase()));
        $("#buyer-name-3").text(data.buyer || "0");
        $("#buyer-id-3").text(data.no_ws || "0");
        const defects = data.list_defect;

        $('#defect-list').empty();

        if (defects.length === 0) {
            const noDataHtml = `
                                    <h5 style="font-size: 20px; color: #FFFFFF; font-weight: bold; text-align: center; padding: 20px; width: 100%;">
                                        Belum ada defect
                                    </h5>
                                `;
            $('#defect-list').append(noDataHtml);
        } else {
            $.each(defects, function(index, defect) {
                const defectHtml = `
                                         <div class="defect-item" id="defno${index+1}text" style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #ccc; font-size: 14px; text-align: left; width:100%;">
                                            <div style="display: flex; align-items: center; justify-content-center; width:85%;">
                                                <span style="font-weight: bold; font-size:18px;">${index + 1}. ${defect.defect_type}</span>
                                            </div>
                                            <div style="display: flex; align-items: center; justify-content-center;">
                                                <input type="hidden" id="defno${index+1}" value="${defect.defect_type_id}">
                                                <span style="color: #FFF; font-weight: bold;">(${defect.jml})</span>
                                            </div>
                                        </div>
                                    `;
                $('#defect-list').append(defectHtml);
            });
        }

        const carouselContainer = $('#carousel-inner');
        const linkGambar1 = data.link_gambar1;
        const positionDefect = data.positiondefect || [];

        // bangun ulang carousel hanya kalau gambar / titik defect berubah
        const carouselSignature = JSON.stringify([linkGambar1, positionDefect]);
        if (carouselSignature !== lastCarouselSignature) {
            lastCarouselSignature = carouselSignature;
            carouselContainer.empty();
            linkGambar1.forEach((gambar1, index) => {
                const isActive = index === 0 ? 'active' : ''; // Set item pertama sebagai active
                const defectPoints = positionDefect
                    .filter(posdef => posdef.image === gambar1.image)
                    .map(posdef => `
                                            <div class="defect-area-img-point"
                                                data-defect-type="${posdef.defect_type_id}"
                                                data-x="${parseFloat(posdef.defect_area_x)}"
                                                data-y="${parseFloat(posdef.defect_area_y)}">
                                            </div>`).join('');
                let link = location.protocol !== 'https:' ? 'http://10.10.5.62:8080/erp/pages/prod_new/upload_files/' : 'https://10.10.5.62:8143/erp/pages/prod_new/upload_files/';
                // Add carousel item
                const carouselItem = `
                                        <div class="carousel-item ${isActive}" data-bs-interval="4500">
                                            <div class="defect-area-img-container mx-auto overflow-hidden" style="height: 420px; padding-bottom: 20px;">
                                                ${defectPoints}
                                                <img style="opacity: .8; width: auto; height: 420px;"
                                                    src="${link}${gambar1.image}"
                                                    class="img-fluid defect-area-img">
                                            </div>
                                        </div>`;

                carouselContainer.append(carouselItem);
            });
        }

        let defectAreaImage = document.getElementsByClassName('defect-area-img');
        let defectAreaImagePoint = document.getElementsByClassName('defect-area-img-point');

        const defNumberOne = document.getElementById('defno1') ? document.getElementById('defno1').value : '';
        const defNumberTwo = document.getElementById('defno2') ? document.getElementById('defno2').value : '';
        const defNumberThree = document.getElementById('defno3') ? document.getElementById('defno3').value : '';
        const defNumberFour = document.getElementById('defno4') ? document.getElementById('defno4').value : '';
        const defNumberFive = document.getElementById('defno5') ? document.getElementById('defno5').value : '';

        for (let i = 0; i < defectAreaImage.length; i++) {
            let img = new Image();

            img.src = defectAreaImage[i].src;

            img.onload = function() {
                var imgPercent = 470 / this.height * 100;
                var imgWidth = (imgPercent / 100) * this.width;

                var defectAreaImagePointArr = [...defectAreaImagePoint];

                for (let j = 0; j < defectAreaImagePoint.length; j++) {
                    let color = "whitesmoke";
                    let border = "gray";
                    switch (defectAreaImagePoint[j].getAttribute('data-defect-type')) {
                        case defNumberOne:
                            color = "#ec3032";
                            border = "#b50204";
                            break;
                        case defNumberTwo:
                            color = "#fd8024";
                            border = "#da5d02";
                            break;
                        case defNumberThree:
                            color = "#fffb45";
                            border = "#d0cb02";
                            break;
                        case defNumberFour:
                            color = "#2bff6b";
                            border = "#00c43b";
                            break;
                        case defNumberFive:
                            color = "whitesmoke";
                            border = "gray";
                            break;
                    }

                    defectAreaImagePoint[j].style.backgroundColor = color;
                    defectAreaImagePoint[j].style.border = "1px solid " + border;
                    defectAreaImagePoint[j].style.width = 0.03 * imgWidth + 'px';
                    defectAreaImagePoint[j].style.height = defectAreaImagePoint[j].style.width;
                    defectAreaImagePoint[j].style.left = 'calc(' + (defectAreaImagePoint[j].getAttribute(
                        'data-x')) + '% - ' + 0.02 * imgWidth + 'px)';
                    defectAreaImagePoint[j].style.top = 'calc(' + (defectAreaImagePoint[j].getAttribute('data-y')) +
                        '% - ' + 0.02 * imgWidth + 'px)';
                }
            }
        }

        showChartEfficiency(data);
        showChartRFT(data);
        showChartDeffect(data);

        // konten (tabel, nama buyer, dll) bisa berubah setelah data masuk
        fitDashboard();
    }

    $(document).ready(function() {
        getData();
    });

    var intervalData = setInterval(() => {
        getData();
    }, 60000);

    const lineId = @json($id);
    const testJam = new URLSearchParams(location.search).get('jam'); // TEST JAM: hapus setelah selesai cek
    let isFetching = false;

    async function getData() {
        // jangan tumpuk request kalau server/jaringan lambat
        if (isFetching) return;
        isFetching = true;

        // tanggal lokal, bukan toISOString() (UTC): sebelum jam 07:00 WIB toISOString() masih tanggal kemarin
        const today = new Date();
        const formattedDate = [
            today.getFullYear(),
            String(today.getMonth() + 1).padStart(2, '0'),
            String(today.getDate()).padStart(2, '0'),
        ].join('-');

        try {
            const response = await $.ajax({
                url: '/nds_wip/public/index.php/api/trigger-wip-line/dashboard-line/wip-line-sign',
                type: 'POST',
                contentType: 'application/json',
                timeout: 30000,
                data: JSON.stringify({
                    "tanggal": formattedDate,
                    "line_id": lineId,
                    "jam": testJam // TEST JAM
                }),
            });

            showingalldata(response.data.data);
        } catch (error) {
            console.error('Request failed:', error);
        } finally {
            isFetching = false;
        }
    }

    // Format waktu ke zona waktu Jakarta (Asia/Jakarta), dibuat sekali saja
    const jakartaFormatter = new Intl.DateTimeFormat("id-ID", {
        timeZone: "Asia/Jakarta",
        year: "numeric",
        month: "short",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    });

    function updateJakartaTime() {
        // Format waktu menjadi array parts
        const parts = jakartaFormatter.formatToParts(new Date());

        // Ambil bagian tanggal dengan format: 25 Jan 2025
        const day = parts.find(part => part.type === "day").value;
        const month = parts.find(part => part.type === "month").value;
        const year = parts.find(part => part.type === "year").value;

        const formattedDate = `${day} ${month} ${year}`;

        // Ambil bagian waktu
        const hour = parts.find(part => part.type === "hour").value;
        const minute = parts.find(part => part.type === "minute").value;
        const second = parts.find(part => part.type === "second").value;

        const formattedTime = `${hour}:${minute}:${second}`;

        // Gabungkan dan tampilkan
        document.getElementById("current-time").textContent = `${formattedDate} - ${formattedTime}`;
        document.getElementById("current-time-2").textContent = `${formattedDate} - ${formattedTime}`;
        document.getElementById("current-time-3").textContent = `${formattedDate} - ${formattedTime}`;
    }

    updateJakartaTime();

    var time = setInterval(function() {
        updateJakartaTime();
    }, 1000); // 1000 milliseconds = 1 second


    function updateRealtimeDiff() {
        // Waktu awal (07:00:00 hari ini)
        const datelog = new Date();
        datelog.setHours(7, 0, 0, 0); // Set jam 07:00:00

        // Waktu sekarang
        const now = new Date();

        // Perbedaan waktu dalam milidetik
        let diff = now - datelog;

        // Jika sekarang sebelum jam 07:00:00, set ke 0
        if (diff < 0) diff = 0;

        // Hitung total menit
        let totalMinutes = Math.floor(diff / (1000 * 60));

        // Jika waktu sekarang melewati jam 12:00, kurangi waktu istirahat dari total menit
        const restStart = new Date();
        restStart.setHours(12, 0, 0, 0); // 12:00:00
        const restEnd = new Date();
        restEnd.setHours(13, 0, 0, 0); // 13:00:00

        if (now >= restEnd) {
            // Jika sekarang sudah melewati jam 13:00, kurangi 60 menit (durasi istirahat)
            totalMinutes -= 60;
        } else if (now >= restStart && now < restEnd) {
            // Jika sekarang berada di antara 12:00 - 13:00, set total menit hingga jam istirahat dimulai
            totalMinutes -= Math.floor((now - restStart) / (1000 * 60));
        }

        const hours = Math.floor(totalMinutes / 60);
        const minutes = totalMinutes % 60;

        document.getElementById("realtime-diff").textContent = `${hours} Hours ${minutes} Minutes`;
        document.getElementById("realtime-diff-2").textContent = `${hours} Hours ${minutes} Minutes`;
        document.getElementById("realtime-diff-3").textContent = `${hours} Hours ${minutes} Minutes`;
    }

    updateRealtimeDiff();

    var timeDiff = setInterval(function() {
        updateRealtimeDiff();
    }, 60000);


    function showChartEfficiency(data) {
    // Ambil nilai asli dari data
    const eff_data = Number((data.summary || {}).eff || 0);

    // Batasi nilai bar hanya sampai 100 (rentang 0–100)
    const eff_data_clamped = Math.min(Math.max(eff_data, 0), 100);

    // label tetap tampilkan nilai asli (contoh: 383%), bar dibatasi 0-100
    renderGauge('efficiency', 'chartdiv-efficiency', eff_data_clamped, `${eff_data}`, () => ({
        debug: false,
        legend_visible: false,
        defaultTooltip_enabled: false,
        xAxis_spacingPercentage: 0.4,
        yAxis: [{
            id: 'ax1',
            defaultTick: {
                padding: 10,
                enabled: false
            },
            customTicks: [0, 50, 75, 85, 100],
            line: {
                width: 8,
                breaks: {},
                color: 'smartPalette:pal1'
            },
            scale_range: [0, 100]
        }],
        defaultSeries: {
            type: 'gauge column roundcaps',
            shape: {
                label: {
                    align: 'center',
                    verticalAlign: 'middle',
                    style_fontSize: 28
                }
            }
        },
        series: [{
            type: 'column roundcaps',
            name: 'Temperatures',
            yAxis: 'ax1',
            palette: {
                id: 'pal1',
                pointValue: '%yValue',
                ranges: [{
                        value: 0,
                        color: '#FF5353'
                    },
                    {
                        value: 50,
                        color: '#FFD221'
                    },
                    {
                        value: 75,
                        color: '#77E6B4'
                    },
                    {
                        value: [85, 100],
                        color: '#21D683'
                    }
                ]
            }
        }]
    }));
}


    function showChartRFT(data) {
        const per_rft = Number((data.summary || {}).rft_pct || 0);
        renderGauge('rft', 'chartdiv-rft', per_rft, '%max', () => ({
            debug: false,
            legend_visible: false,
            defaultTooltip_enabled: false,
            xAxis_spacingPercentage: 0.4,
            margin: '7px',
            yAxis: [{
                id: 'ax1',
                defaultTick: {
                    padding: 2,
                    enabled: false
                },
                customTicks: [0, 97, 98, 100],
                line: {
                    width: 8,

                    /*Defining the option will enable it.*/
                    breaks: {},

                    /*Palette is defined at series level with an ID referenced here.*/
                    color: 'smartPalette:pal1'
                },
                scale_range: [0, 100]
            }],
            defaultSeries: {
                type: 'gauge column roundcaps',
                shape: {
                    label: {
                        text: '%max',
                        align: 'center',
                        verticalAlign: 'middle',
                        style_fontSize: 28
                    }
                }
            },
            series: [{
                type: 'column roundcaps',
                name: 'Temperatures',
                yAxis: 'ax1',
                palette: {
                    id: 'pal1',
                    pointValue: '%yValue',
                    ranges: [{
                            value: 0,
                            color: '#FF5353'
                        },
                        {
                            value: 97,
                            color: '#FFD221'
                        },
                        {
                            value: 98,
                            color: '#77E6B4'
                        },
                        {
                            value: 100,
                            color: '#21D683'
                        }
                    ]
                }
            }, ]
        }));
    }

    function showChartDeffect(data) {
        const per_defect = Number((data.summary || {}).def_rate || 0);
        renderGauge('deffect', 'chartdiv-deffect', per_defect, '%max', () => ({
            debug: false,
            legend_visible: false,
            defaultTooltip_enabled: false,
            xAxis_spacingPercentage: 0.4,
            margin: '7px',
            yAxis: [{
                id: 'ax1',
                defaultTick: {
                    padding: 5,
                    enabled: false
                },
                customTicks: [0, 2, 3, 100],
                line: {
                    width: 8,

                    /*Defining the option will enable it.*/
                    breaks: {},

                    /*Palette is defined at series level with an ID referenced here.*/
                    color: 'smartPalette:pal1'
                },
                scale_range: [0, 100]
            }],
            defaultSeries: {
                type: 'gauge column roundcaps',
                shape: {
                    label: {
                        text: '%max',
                        align: 'center',
                        verticalAlign: 'middle',
                        style_fontSize: 28
                    }
                }
            },
            series: [{
                type: 'column roundcaps',
                name: 'Temperatures',
                yAxis: 'ax1',
                palette: {
                    id: 'pal1',
                    pointValue: '%yValue',
                    ranges: [{
                            value: 0,
                            color: '#21D683'
                        },
                        {
                            value: 2,
                            color: '#FFD221'
                        },
                        {
                            value: 3,
                            color: '#FF5353'
                        },
                        {
                            value: 100,
                            color: '#FF5353'
                        }
                    ]
                }
            }, ]
        }));
    }

    // AUTO ZOOM: skala konten agar pas lebar & tinggi layar (PC, laptop, TV)
    const DESIGN_WIDTH = 1300;

    function fitDashboard() {
        const rows = document.querySelectorAll('.wip-scale');
        if (!rows.length) return;

        // tinggi asli konten (offsetHeight tidak terpengaruh transform)
        let designHeight = 0;
        rows.forEach(row => designHeight = Math.max(designHeight, row.offsetHeight));
        if (!designHeight) return;

        const availWidth = window.innerWidth - 8; // padding p-1 kiri-kanan
        const availHeight = window.innerHeight - 8; // padding p-1 atas-bawah
        const scale = Math.min(availWidth / DESIGN_WIDTH, availHeight / designHeight);

        // pakai transform, bukan zoom: chart JSC salah ukur di dalam elemen yang di-zoom
        rows.forEach(row => row.style.transform = `scale(${scale})`);
    }

    let fitTimer;
    window.addEventListener('resize', function() {
        clearTimeout(fitTimer);
        fitTimer = setTimeout(fitDashboard, 150);
    });
    window.addEventListener('load', fitDashboard);
</script>
