<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NDS - WIP</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('dist/img/tabicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @include('layouts.link')

    @yield('custom-link')

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

    .bg-linear {
        background: linear-gradient(to bottom right, #1A5CD8, #007aff);
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
                style="max-width: 1300px; width: 100%;height: 100vh; background-color: #ffffff;">
                <div class="row">
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
                            <div class="card-body">
                                <table class="table border-collapse"
                                    style="background-color: #072c66; text-align: center;border-radius: 10px;"
                                    style="max-height: 10vh !important;">
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
                                    <tbody class="tbody-custom">
                                        <tr style="line-height: 10px;">
                                            <td scope="row" style="border-color: lightgray;font-size: 13px;"
                                                id="hour-1">07:00 - 08:00</td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="target-1"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="output-1"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="variation-1"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="efficiency-1"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="deffect-rate-1">
                                            </td>
                                        </tr>
                                        <tr style="line-height: 10px;">
                                            <td scope="row" style="border-color: lightgray;font-size: 13px;"
                                                id="hour-2">08:00 - 09:00</td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="target-2"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="output-2"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="variation-2"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="efficiency-2"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="deffect-rate-2">
                                            </td>
                                        </tr>
                                        <tr style="line-height: 10px;">
                                            <td scope="row" style="border-color: lightgray;font-size: 13px;"
                                                id="hour-3">09:00 - 10:00</td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="target-3"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="output-3"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="variation-3">
                                            </td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="efficiency-3">
                                            </td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="deffect-rate-3">
                                            </td>
                                        </tr>
                                        <tr style="line-height: 10px;">
                                            <td scope="row" style="border-color: lightgray;font-size: 13px;"
                                                id="hour-4">10:00 - 11:00</td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="target-4"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="output-4"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="variation-4">
                                            </td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="efficiency-4">
                                            </td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="deffect-rate-4">
                                            </td>
                                        </tr>
                                        <tr style="line-height: 10px;">
                                            <td scope="row" style="border-color: lightgray;font-size: 13px;"
                                                id="hour-5">11:00 - 12:00</td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="target-5"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="output-5"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="variation-5">
                                            </td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="efficiency-5">
                                            </td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="deffect-rate-5">
                                            </td>
                                        </tr>

                                        <tr style="line-height: 10px;" class="bg-danger">
                                            <td scope="row"
                                                style="border-color: lightgray;font-size: 13px; background-color: #ff0000 !important;">
                                                12:00 - 13:00</td>
                                            <td
                                                style="border-color: lightgray;font-size: 13px; background-color: #ff0000 !important;">
                                                0</td>
                                            <td
                                                style="border-color: lightgray;font-size: 13px; background-color: #ff0000 !important;">
                                                0</td>
                                            <td
                                                style="border-color: lightgray;font-size: 13px; background-color: #ff0000 !important;">
                                                0</td>
                                            <td
                                                style="border-color: lightgray;font-size: 13px; background-color: #ff0000 !important;">
                                                0%</td>
                                            <td
                                                style="border-color: lightgray;font-size: 13px; background-color: #ff0000 !important;">
                                                0%</td>
                                        </tr>
                                        <tr style="line-height: 10px;">
                                            <td scope="row" style="border-color: lightgray;font-size: 13px;"
                                                id="hour-7">13:00 - 14:00</td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="target-7"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="output-7"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="variation-7">
                                            </td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="efficiency-7">
                                            </td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="deffect-rate-7">
                                            </td>
                                        </tr>
                                        <tr style="line-height: 10px;">
                                            <td scope="row" style="border-color: lightgray;font-size: 13px;"
                                                id="hour-8">14:00 - 15:00</td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="target-8"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="output-8"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="variation-8">
                                            </td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="efficiency-8">
                                            </td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="deffect-rate-8">
                                            </td>
                                        </tr>
                                        <tr style="line-height: 10px;">
                                            <td scope="row" style="border-color: lightgray;font-size: 13px;"
                                                id="hour-9">15:00 - 16:00</td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="target-9"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="output-9"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="variation-9">
                                            </td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="efficiency-9">
                                            </td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="deffect-rate-9">
                                            </td>
                                        </tr>
                                        <tr style="line-height: 10px;">
                                            <td scope="row" style="border-color: lightgray;font-size: 13px;"
                                                id="hour-10">16:00 - 17:00</td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="target-10"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="output-10"></td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="variation-10">
                                            </td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="efficiency-10">
                                            </td>
                                            <td style="border-color: lightgray;font-size: 13px;" id="deffect-rate-10">
                                            </td>
                                        </tr>
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
                style="max-width: 1300px; width: 100%; height: 100vh; background-color: #ffffff;">
                <div class="row">
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
                style="max-width: 1300px; width: 100%; height: 100vh; background-color: #ffffff;">
                <div class="row">
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

    @include('layouts.script')

    @stack('scripts')
</body>

</html>

<link rel="stylesheet" href="{{ asset('plugins/swiper/css/swiper-bundle.min.css') }}" />

<script src="{{ asset('plugins/swiper/js/swiper-bundle.min.js') }}"></script>
<script src="{{ asset('plugins/swiper/js/swiper-element-bundle.min.js') }}"></script>

<!-- JSC CHART-->
<script src="https://code.jscharting.com/latest/jscharting.js"></script>

<!-- SOCKET.IO configuration -->
{{-- <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
    crossorigin="anonymous"></script>
<script>
    window.laravel_echo_port = '{{ env('LARAVEL_ECHO_PORT') }}';
</script>
<script src="http://{{ Request::getHost() }}:{{ config('redis.echo_port') }}/socket.io/socket.io.js"></script>
<script src="{{ config('redis.redis_url_public') }}/js/laravel-echo-setup.js" type="text/javascript"></script> --}}

<script>
    const currentDate = new Date().toISOString().split('T')[0];

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

    function updateTable1(data) {
        const data_table = data.datajam7[0];
        const jamKer = data_table.jam_kerja || 0;
        const output = data_table.output || 0;

        const target = jamKer < 1 ?
            0 :
            jamKer > 1 ?
            data_table.target1 :
            data_table.target2;


        const variance = jamKer < 1 ?
            0 :
            jamKer > 1 ?
            data_table.variation1 :
            data_table.variation2;


        const efficiency = target === 0 ? 0 : (output / target) * 100;

        const defectRate = jamKer < 1 ?
            0 :
            jamKer > 1 ?
            data_table.defect_rate1 :
            data_table.defect_rate2;

        $("#hour-1").text('07:00 - 08:00');
        $("#target-1").text(target);
        $("#output-1").text(output);
        $("#variation-1").html(`
            ${Math.abs(variance)}
            <i class="fa ${variance > 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variance > 0 ? 'red' : 'green'};"></ion-icon>
        `);
        $("#efficiency-1").text(`${efficiency.toFixed(2)} %`);
        $("#deffect-rate-1").text(`${defectRate || 0.00} %`);
    }

    function updateTable2(data) {
        const data_table = data.datajam8[0];
        const jamKer = data_table.jam_kerja || 0;
        const output = data_table.output || 0;

        const target = jamKer < 2 ?
            0 :
            jamKer > 2 ?
            data_table.target1 :
            data_table.target2;


        const variance = jamKer < 2 ?
            0 :
            jamKer > 2 ?
            data_table.variation1 :
            data_table.variation2;


        const efficiency = target === 0 ? 0 : (output / target) * 100;

        const defectRate = jamKer < 2 ?
            0 :
            jamKer > 2 ?
            data_table.defect_rate1 :
            data_table.defect_rate2;

        $("#hour-2").text("08:00 - 09:00");
        $("#target-2").text(target);
        $("#output-2").text(output);
        $("#variation-2").html(`
            ${Math.abs(variance)}
             <i class="fa ${variance > 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variance > 0 ? 'red' : 'green'};"></ion-icon>
        `);
        $("#efficiency-2").text(`${parseFloat(efficiency).toFixed(2)} %`);
        $("#deffect-rate-2").text(`${parseFloat(defectRate || 0).toFixed(2)} %`);
    }

    function updateTable3(data) {
        const data_table = data.datajam9[0];
        const jamKer = data_table.jam_kerja || 0;
        const output = data_table.output || 0;

        const target = jamKer < 3 ?
            0 :
            jamKer > 3 ?
            data_table.target1 :
            data_table.target2;


        const variance = jamKer < 3 ?
            0 :
            jamKer > 3 ?
            data_table.variation1 :
            data_table.variation2;


        const efficiency = target === 0 ? 0 : (output / target) * 100;

        const defectRate = jamKer < 3 ?
            0 :
            jamKer > 3 ?
            data_table.defect_rate1 :
            data_table.defect_rate2;

        $("#hour-3").text("09:00 - 10:00");
        $("#target-3").text(target);
        $("#output-3").text(output);
        $("#variation-3").html(`
            ${Math.abs(variance)}
            <i class="fa ${variance > 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variance > 0 ? 'red' : 'green'};"></ion-icon>
        `);
        $("#efficiency-3").text(`${parseFloat(efficiency).toFixed(2)} %`);
        $("#deffect-rate-3").text(`${parseFloat(defectRate || 0).toFixed(2)} %`);
    }

    function updateTable4(data) {
        const data_table = data.datajam10[0];
        const jamKer = data_table.jam_kerja || 0;
        const output = data_table.output || 0;

        const target = jamKer < 4 ?
            0 :
            jamKer > 4 ?
            data_table.target1 :
            data_table.target2;


        const variance = jamKer < 4 ?
            0 :
            jamKer > 4 ?
            data_table.variation1 :
            data_table.variation2;


        const efficiency = target === 0 ? 0 : (output / target) * 100;

        const defectRate = jamKer < 4 ?
            0 :
            jamKer > 4 ?
            data_table.defect_rate1 :
            data_table.defect_rate2;

        $("#hour-4").text("10:00 - 11:00");
        $("#target-4").text(target);
        $("#output-4").text(output);
        $("#variation-4").html(`
            ${Math.abs(variance)}
            <i class="fa ${variance > 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variance > 0 ? 'red' : 'green'};"></ion-icon>
        `);
        $("#efficiency-4").text(`${parseFloat(efficiency).toFixed(2)} %`);
        $("#deffect-rate-4").text(`${parseFloat(defectRate || 0).toFixed(2)} %`);
    }

    function updateTable5(data) {
        const data_table = data.datajam11[0];
        const jamKer = data_table.jam_kerja || 0;
        const output = data_table.output || 0;

        const target = jamKer < 5 ?
            0 :
            jamKer > 5 ?
            data_table.target1 :
            data_table.target2;


        const variance = jamKer < 5 ?
            0 :
            jamKer > 5 ?
            data_table.variation1 :
            data_table.variation2;


        const efficiency = target === 0 ? 0 : (output / target) * 100;

        const defectRate = jamKer < 5 ?
            0 :
            jamKer > 5 ?
            data_table.defect_rate1 :
            data_table.defect_rate2;

        $("#hour-5").text("11:00 - 12:00");
        $("#target-5").text(target);
        $("#output-5").text(output);
        $("#variation-5").html(`
            ${Math.abs(variance)}
            <i class="fa ${variance > 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variance > 0 ? 'red' : 'green'};"></ion-icon>
        `);
        $("#efficiency-5").text(`${parseFloat(efficiency).toFixed(2)} %`);
        $("#deffect-rate-5").text(`${parseFloat(defectRate || 0).toFixed(2)} %`);
    }

    function updateTable7(data) {
        const data_table = data.datajam13[0];
        const jamKer = data_table.jam_kerja || 0;
        const output = data_table.output || 0;

        const target = jamKer < 6 ?
            0 :
            jamKer > 6 ?
            data_table.target1 :
            data_table.target2;


        const variance = jamKer < 6 ?
            0 :
            jamKer > 6 ?
            data_table.variation1 :
            data_table.variation2;


        const efficiency = target === 0 ? 0 : (output / target) * 100;

        const defectRate = jamKer < 6 ?
            0 :
            jamKer > 6 ?
            data_table.defect_rate1 :
            data_table.defect_rate2;

        $("#hour-7").text("13:00 - 14:00");
        $("#target-7").text(target);
        $("#output-7").text(output);
        $("#variation-7").html(`
            ${Math.abs(variance)}
            <i class="fa ${variance > 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variance > 0 ? 'red' : 'green'};"></ion-icon>
        `);
        $("#efficiency-7").text(`${parseFloat(efficiency).toFixed(2)} %`);
        $("#deffect-rate-7").text(`${parseFloat(defectRate || 0).toFixed(2)} %`);
    }

    function updateTable8(data) {
        const data_table = data.datajam14[0];
        const jamKer = data_table.jam_kerja || 0;
        const output = data_table.output || 0;

        const target = jamKer < 7 ?
            0 :
            jamKer > 7 ?
            data_table.target1 :
            data_table.target2;


        const variance = jamKer < 7 ?
            0 :
            jamKer > 7 ?
            data_table.variation1 :
            data_table.variation2;


        const efficiency = target === 0 ? 0 : (output / target) * 100;

        const defectRate = jamKer < 7 ?
            0 :
            jamKer > 7 ?
            data_table.defect_rate1 :
            data_table.defect_rate2;

        $("#hour-8").text("14:00 - 15:00");
        $("#target-8").text(target);
        $("#output-8").text(output);
        $("#variation-8").html(`
            ${Math.abs(variance)}
            <i class="fa ${variance > 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variance > 0 ? 'red' : 'green'};"></ion-icon>
        `);
        $("#efficiency-8").text(`${parseFloat(efficiency).toFixed(2)} %`);
        $("#deffect-rate-8").text(`${parseFloat(defectRate || 0).toFixed(2)} %`);
    }

    function updateTable9(data) {
        const data_table = data.datajam15[0];
        const jamKer = data_table.jam_kerja || 0;
        const output = data_table.output || 0;

        const target = jamKer < 8 ?
            0 :
            jamKer > 8 ?
            data_table.target1 :
            data_table.target2;


        const variance = jamKer < 8 ?
            0 :
            jamKer > 8 ?
            data_table.variation1 :
            data_table.variation2;


        const efficiency = target === 0 ? 0 : (output / target) * 100;

        const defectRate = jamKer < 8 ?
            0 :
            jamKer > 8 ?
            data_table.defect_rate1 :
            data_table.defect_rate2;

        $("#hour-9").text("15:00 - 16:00");
        $("#target-9").text(target);
        $("#output-9").text(output);
        $("#variation-9").html(`
            ${Math.abs(variance)}
            <i class="fa ${variance > 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variance > 0 ? 'red' : 'green'};"></ion-icon>
        `);
        $("#efficiency-9").text(`${parseFloat(efficiency).toFixed(2)} %`);
        $("#deffect-rate-9").text(`${parseFloat(defectRate || 0).toFixed(2)} %`);
    }

    function updateTable10(data) {
        const jamKer = data.jamkerl1 || 0;
        const targetFloor = data.target_floor || 0;
        const targetFloorDom = data.target_floordom || 0;
        const output = data.output16 || 0;
        const defect = data.deffect16 || 0;

        const target = jamKer < 9 ?
            0 :
            jamKer > 9 ?
            targetFloor :
            targetFloorDom;

        const variance = target - output;
        const varianceDisplay = Math.abs(variance);

        const efficiency = target === 0 ? 0 : (output / target) * 100;

        const defectRate = target === 0 ? 0 : (defect / target) * 100;

        $("#hour-10").text("16:00 - 17:00");
        $("#target-10").text(target);
        $("#output-10").text(output);
        $("#variation-10").html(`
            ${varianceDisplay}
         <i class="fa ${variance > 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variance > 0 ? 'red' : 'green'};"></ion-icon>
        `);
        $("#efficiency-10").text(`${parseFloat(efficiency).toFixed(2)} %`);
        $("#deffect-rate-10").text(`${parseFloat(defectRate || 0).toFixed(2)} %`);
    }

    function variation1(data) {
        $("#day_target1").text(data.day_target1 || "");
        $("#actuall1").text(data.actuall1 || "");

        const dayTarget1 = data.day_target1 || 0;
        const actuall1 = data.actuall1 || 0;

        // Menghitung variance_sum dan varianc_sum
        const varianceSum = dayTarget1 - actuall1;
        const varianceDisplay = Math.abs(varianceSum);

        // Menentukan ikon berdasarkan nilai varianceSum
        const icon = varianceSum > 0 ?
            '<ion-icon name="caret-down-outline" style="color: red;"></ion-icon>' :
            '<ion-icon name="caret-up-outline" style="color: green;"></ion-icon>';

        // Menampilkan hasilnya pada elemen #variation1
        $("#variation1").html(`
            ${varianceDisplay} ${icon}
        `);
    }

    function efficiency1(data) {
        const dayTarget1 = data.day_target1 || 1; // Default ke 1 jika day_target1 0 atau undefined
        const actuall1 = data.actuall1 || 0;

        // Menghitung efficiency_sum
        const efficiencySum = (actuall1 / dayTarget1) * 100;
        const roundedEfficiency = efficiencySum.toFixed(2); // Membulatkan hasilnya ke 2 desimal
        // const eff_data = data.dashboard_indicators[0].effi || 0;
        const eff_data = data.dashboard_indicators2[0].effi || 0;

        // Menampilkan hasilnya pada elemen #efficiency1
        $("#efficiency1").html(`
            ${eff_data}%
        `);
    }

    function deffect1(data) {
        const dayTarget1 = data.day_target1 || 1; // Default ke 1 jika day_target1 0 atau undefined
        const deffectl1 = data.deffectl1 || 0;

        // Menghitung deffect_sum
        const deffectSum = (deffectl1 / dayTarget1) * 100;
        const roundedDeffect = deffectSum.toFixed(2); // Membulatkan hasilnya ke 2 desimal
        const deffect_data = data.dashboard_indicators[0].per_defect || 0;

        // Menampilkan hasilnya pada elemen #deffect1
        $("#deffect1").html(`
            ${deffect_data}%
        `);
    }

    function commulation2(data) {
        const target_menit = data.target_menit || 0;
        const actual_now = data.current_actual || 0;
        const targetnya = data.day_target1  || 0;
        const jamker = data.jamkerl1 || 0;

        const datelog = new Date();
        datelog.setHours(7, 0, 0, 0);
        const datenow = new Date();

        const diff = (datenow - datelog) / 1000;
        const minutes = Math.floor(diff / 60);
        const jam = Math.floor(minutes / 60);

        let min = minutes;

        if (jam >= 6) {
            min = minutes - 60;
        }

        const cumulative = Math.round(min * target_menit, 0);
        var val_cumulative = 0;
        if(cumulative > targetnya){
             val_cumulative = targetnya;
        }else{
             val_cumulative = cumulative;
        }
        document.getElementById("cumulative-target-2").textContent = val_cumulative;
        document.getElementById("actual-hour-now").textContent = actual_now;

    }


    function variance2(data) {
        const target_menit = data.target_menit || 0;
        const day_target = data.day_target1 || 0;
        const current_actual = data.current_actual || 0;
        const target_hour = data.target_floor || 0;
        const variation_hour_now = target_hour - current_actual;
        const actual = data.actuall1 || 0;
        const datelog = new Date();
        datelog.setHours(7, 0, 0, 0);
        const datenow = new Date();

        const diff = (datenow - datelog) / 1000;
        const minutes = Math.floor(diff / 60);
        const jam = Math.floor(minutes / 60);

        let min = minutes;

        if (jam >= 6) {
            min = minutes - 60;
        }

        const cumulative = Math.round(min * target_menit, 0);
        const variance = day_target - actual;
        const varianceDisplay = Math.abs(variance);
        const varianceDisplayHour = Math.abs(variation_hour_now);
        console.log(variance);
        $("#variation-page-2").html(`
            ${varianceDisplay}
           <i class="fa ${variance > 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variance > 0 ? 'red' : '#3ee706'};"></ion-icon>
        `);

        $("#variation-box").css('border', variance > 0 ? '1px solid red' : '1px solid #3ee706');

        $("#variation-hour-now").html(`
            ${varianceDisplayHour}
           <i class="fa ${variation_hour_now > 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variation_hour_now > 0 ? 'red' : '#3ee706'};"></ion-icon>
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
        updateTable1(data);
        updateTable2(data);
        updateTable3(data);
        updateTable4(data);
        updateTable5(data);
        updateTable7(data);
        updateTable8(data);
        updateTable9(data);
        updateTable10(data);
        variation1(data);
        efficiency1(data);
        deffect1(data);

        // PAGES 2
        $("#user-name-2").text((data.user || "Line").replace(/_/g, " ").replace(/\b\w/g, (char) => char.toUpperCase()));
        $("#buyer-name-2").text(data.buyer || "0");
        $("#buyer-id-2").text(data.no_ws || "0");
        $("#actual-2").text(data.actuall1 || "0");
        $("#day-target-2").text(data.day_target1 || "0");
        $("#req-hour-target-2").text(data.target_floor || "0");
        $("#deffect-garment-qty-2").text(data.deffectl1 - data.rework || "0");
        $("#rework-balance-2").text(data.rework || "0");
        commulation2(data);
        variance2(data);

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

        carouselContainer.empty();
        linkGambar1.forEach((gambar1, index) => {
            const isActive = index === 1 ? 'active' : ''; // Set item pertama sebagai active
            const defectPoints = positionDefect
                .filter(posdef => posdef.image === gambar1.image)
                .map(posdef => `
                                        <div class="defect-area-img-point"
                                            data-defect-type="${posdef.defect_type_id}"
                                            data-x="${parseFloat(posdef.defect_area_x)}"
                                            data-y="${parseFloat(posdef.defect_area_y)}">
                                        </div>`).join('');
            console.log(location.protocol);
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
    }

    $(document).ready(async function() {
        // window.Echo.channel("dashboard-wip-line-channel-" + lineId)
        //     .listen('.UpdatedDashboardWipLineEvent', (event) => {
        //         if (event && event.data) {
        //             const data = event.data;
        //             showingalldata(data);
        //         }
        //     });
        getData();
    });

    var intervalData = setInterval(() => {
        getData();
    }, 60000);

    // var intervalPage = setInterval(() => {
    //     location.reload()
    // }, 900000);

    async function getData() {
        const today = new Date();
        const formattedDate = today.toISOString().split('T')[0];
        var lineId = @json($id);
        const data = {
            "tanggal": formattedDate,
            "line_id": lineId
        };

        try {
            const response = await $.ajax({
                url: '/nds_wip/public/index.php/api/trigger-wip-line/dashboard-line/wip-line-sign',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(result) {},
                error: function(xhr, status, error) {}
            });

            showingalldata(response.data.data);
        } catch (error) {
            console.error('Request failed:', error);
        }
    }

    function updateJakartaTime() {
        // Buat objek waktu sekarang
        const now = new Date();

        // Format waktu ke zona waktu Jakarta (Asia/Jakarta)
        const formatter = new Intl.DateTimeFormat("id-ID", {
            timeZone: "Asia/Jakarta",
            year: "numeric",
            month: "short",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
        });

        // Format waktu menjadi array parts
        const parts = formatter.formatToParts(now);

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

        // Meminta frame berikutnya
        // requestAnimationFrame(updateJakartaTime);
    }

    updateJakartaTime();

    var time = setInterval(function() {
        updateJakartaTime();
        console.log("This runs every 1 second");
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

        // requestAnimationFrame(updateRealtimeDiff);
    }

    updateRealtimeDiff();

    var timeDiff = setInterval(function() {
        updateRealtimeDiff();

        console.log("This runs every 1 minute");
    }, 60000);


    function showChartEfficiency(data) {
    // Ambil nilai asli dari data
    const eff_data = data.dashboard_indicators2[0].effi || 0;

    // Batasi nilai bar hanya sampai 100 (rentang 0–100)
    const eff_data_clamped = Math.min(Math.max(eff_data, 0), 100);

    var chart = JSC.chart('chartdiv-efficiency', {
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
                    // Tetap tampilkan nilai asli (contoh: 383%)
                    text: `${eff_data}`,
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
            },
            points: [
                ['x', [0, eff_data_clamped]] // Nilai bar dibatasi dalam 0-100
            ]
        }]
    });
}


    function showChartRFT(data) {
        const per_rft = Number(data.dashboard_indicators[0].per_rft || 0, 10);
        var chart = JSC.chart('chartdiv-rft', {
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
                },
                points: [
                    ['x', [0, per_rft]]
                ]
            }, ]
        });
    }

    function showChartDeffect(data) {
        const per_defect = Number(data.dashboard_indicators[0].per_defect || 0, 10);
        var chart = JSC.chart('chartdiv-deffect', {
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
                },
                points: [
                    ['x', [0, per_defect]]
                ]
            }, ]
        });
    }
</script>
