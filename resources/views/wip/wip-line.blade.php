<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NDS - WIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @include('layouts.link')

    @yield('custom-link')

</head>

<style>
.card{
    background-color: #072c66;
}
.card p{
    color: #FFFFFF;
}
.card-title{
    font-size: 25px;
    font-weight: bold;
    letter-spacing: -1px;
}
.card-text{
    font-size: 20px;
    font-weight: 400;
    color: #d1d5db !important;
    letter-spacing: -1px;
}
.card-body{
  line-height: 25px;
}
.table th {
    background-color: #072c66;
    color: #FFFFFF;
}
.table tr {
    background-color:rgb(255, 255, 255);
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

.bg-linear{
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
      /* background-color: #FFA07A;
      border: 3px solid #d42a13; */
      border-radius: 50%;
      opacity: 80%;
      z-index: 99999;
    }


/* SWIPPER */

swiper-container {
      width: 100%;
      height: 100%;
      background-color: #FFFFFF !important;
    }

    swiper-slide {
      text-align: left;
      font-size: 18px;
      background: #fff;
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

#chartdiv-efficiency {
  width: 100%;
  height: 220px;
}
#chartdiv-rft {
  width: 100%;
  height: 220px;
}
#chartdiv-deffect {
  width: 100%;
  height: 220px;
}

#defno1text::after {
      content: '';
      display: inline-block;
      width:25px;
      height:25px;
      border-radius: 50%;
      background: #ec3032;
      top: 10px;
      margin: 0 10px;
      transform: translateY(4px);
    }

    #defno2text::after {
      content: '';
      display: inline-block;
      width:25px;
      height:25px;
      border-radius: 50%;
      background: #fd8024;
      top: 10px;
      margin: 0 10px;
      transform: translateY(4px);
    }

    #defno3text::after {
      content: '';
      display: inline-block;
      width:25px;
      height:25px;
      border-radius: 50%;
      background: #fffb45;
      top: 10px;
      margin: 0 10px;
      transform: translateY(4px);
    }

    #defno4text::after {
      content: '';
      display: inline-block;
      width:25px;
      height:25px;
      border-radius: 50%;
      background: #2bff6b;
      top: 10px;
      margin: 0 10px;
      transform: translateY(4px);
    }

    #defno5text::after {
      content: '';
      display: inline-block;
      width:25px;
      height:25px;
      border-radius: 50%;
      background: whitesmoke;
      top: 10px;
      margin: 0 10px;
      transform: translateY(4px);
    }
</style>
<!-- autoplay-delay="15000"  -->
<!-- autoplay-disable-on-interaction="false" -->

<body class=" d-flex justify-content-center align-items-center" style="min-height: 100vh; background-color: #072c66; font-family: 'Inter', sans-serif;">
    <swiper-container class="mySwiper" autoplay-delay="15000" autoplay-disable-on-interaction="false" space-between="30" centered-slides="true"
    >
        <swiper-slide>
            <div class="shadow rounded p-4 d-flex justify-content-center align-items-center" style="max-width: 1300px; width: 100%; background-color: #e3e5e8;">
                <div class="row g-1">
                        <div class="col-md-1">
                            <div class="card" style="height: 100px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center;">
                                <img src="/nds_wip/public/assets/dist/img/logo-nds4.png" alt="AdminLTE Logo" class="" style="height: 60px; width: 60px;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card" style="height: 100px;">
                                <div class="card-body" style="display: flex; text-align: start; justify-content: center; flex-direction: column;">
                                    <p class="card-title mt-2 " id="current-time"></p>
                                    <p class="card-text" id="user-name">-</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card" style="height: 100px;">
                                <div class="card-body" style="display: flex; text-align: start; justify-content: center; flex-direction: column;">
                                    <p class="card-title" id="buyer-name">-</p>
                                    <p class="card-text" id="buyer-id">-</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card d-flex justify-content-center align-items-center" style="height: 100px;">
                                <div class="card-body" style="display: flex; text-align: start; justify-content: center; flex-direction: column;">
                                    <p class="card-title" id="realtime-diff" style="font-size: 27px; line-height: 30px;">
                                         0 Hours 0 Minutes
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-9">
                            <div class="card">
                                <div class="card-body">
                                    <table class="table border-collapse" style="background-color: #072c66; text-align: center;border-radius: 10px;" style="max-height: 70vh !important;">
                                        <thead class="thead-custom">
                                            <tr>
                                                <th scope="col" style="border-color: lightgray; font-size: 600px; border-top-left-radius: 10px; color: #072c66;">Hours</th>
                                                <th scope="col" style="border-color: lightgray; font-size: 600px; color: #072c66;">Target</th>
                                                <th scope="col" style="border-color: lightgray; font-size: 600px; color: #072c66;">Output</th>
                                                <th scope="col" style="border-color: lightgray; font-size: 600px; color: #072c66;">Variation</th>
                                                <th scope="col" style="border-color: lightgray; font-size: 600px; color: #072c66;">Efficiency</th>
                                                <th scope="col" style="border-color: lightgray; font-size: 600px; border-top-right-radius: 10px; color: #072c66;">Defect Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody class="tbody-custom">
                                            <tr style="line-height: 10px;">
                                                <td scope="row" style="border-color: lightgray;font-size: 13px;" id="hour-1">07:00 - 08:00</td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="target-1"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="output-1"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="variation-1"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="efficiency-1"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="deffect-rate-1"></td>
                                            </tr>
                                            <tr style="line-height: 10px;">
                                                <td scope="row" style="border-color: lightgray;font-size: 13px;" id="hour-2">08:00 - 09:00</td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="target-2"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="output-2"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="variation-2"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="efficiency-2"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="deffect-rate-2"></td>
                                            </tr>
                                            <tr style="line-height: 10px;">
                                                <td scope="row" style="border-color: lightgray;font-size: 13px;" id="hour-3">09:00 - 10:00</td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="target-3"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="output-3"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="variation-3"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="efficiency-3"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="deffect-rate-3"></td>
                                            </tr>
                                            <tr style="line-height: 10px;">
                                                <td scope="row" style="border-color: lightgray;font-size: 13px;" id="hour-4">10:00 - 11:00</td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="target-4"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="output-4"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="variation-4"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="efficiency-4"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="deffect-rate-4"></td>
                                            </tr>
                                            <tr style="line-height: 10px;">
                                                <td scope="row" style="border-color: lightgray;font-size: 13px;" id="hour-5">11:00 - 12:00</td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="target-5"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="output-5"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="variation-5"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="efficiency-5"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="deffect-rate-5"></td>
                                            </tr>

                                            <tr style="line-height: 10px;" class="bg-danger">
                                                    <td scope="row" style="border-color: lightgray;font-size: 13px;">12:00 - 13:00</td>
                                                <td style="border-color: lightgray;font-size: 13px;">0</td>
                                                <td style="border-color: lightgray;font-size: 13px;">0</td>
                                                <td style="border-color: lightgray;font-size: 13px;">0</td>
                                                <td style="border-color: lightgray;font-size: 13px;">0%</td>
                                                <td style="border-color: lightgray;font-size: 13px;">0%</td>
                                            </tr>
                                            <tr style="line-height: 10px;">
                                                <td scope="row" style="border-color: lightgray;font-size: 13px;" id="hour-7">13:00 - 14:00</td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="target-7"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="output-7"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="variation-7"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="efficiency-7"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="deffect-rate-7"></td>
                                            </tr>
                                            <tr style="line-height: 10px;">
                                                <td scope="row" style="border-color: lightgray;font-size: 13px;" id="hour-8">14:00 - 15:00</td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="target-8"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="output-8"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="variation-8"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="efficiency-8"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="deffect-rate-8"></td>
                                            </tr>
                                            <tr style="line-height: 10px;">
                                                <td scope="row" style="border-color: lightgray;font-size: 13px;" id="hour-9">15:00 - 16:00</td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="target-9"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="output-9"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="variation-9"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="efficiency-9"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="deffect-rate-9"></td>
                                            </tr>
                                            <tr style="line-height: 10px;">
                                                <td scope="row" style="border-color: lightgray;font-size: 13px;" id="hour-10">16:00 - 17:00</td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="target-10"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="output-10"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="variation-10"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="efficiency-10"></td>
                                                <td style="border-color: lightgray;font-size: 13px;" id="deffect-rate-10"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="card">
                                <div class="card-body">
                                <div>
                                        <div class="" style="height: 45.7px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                        <h5 style="font-size: 18px; color:#072c66; font-weight: bold">
                                            Target
                                        </h5>
                                        </div>
                                        <div class="" style="height: 45.7px; background-color: #072c66; display: flex; align-items: center; justify-content: center; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; border-top: 0px; border-bottom: 3px solid #FFFFFF; border-left: 3px solid #FFFFFF; border-right: 3px solid #FFFFFF; color:#FFFFFF;">
                                        <h5 id="day_target1" style="font-size: 20px; color:#FFFFFF; font-weight: bold">

                                        </h5>
                                        </div>
                                </div>
                                <div class="mt-3">
                                        <div class="" style="height: 45.7px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                        <h5 style="font-size: 18px; color:#072c66; font-weight: bold">
                                            Output
                                        </h5>
                                        </div>
                                        <div class="" style="height: 45.7px; background-color: #072c66; display: flex; align-items: center; justify-content: center; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; border-top: 0px; border-bottom: 3px solid #FFFFFF; border-left: 3px solid #FFFFFF; border-right: 3px solid #FFFFFF; color:#FFFFFF;">
                                        <h5 id="actuall1" style="font-size: 20px; color:#FFFFFF; font-weight: bold">

                                        </h5>
                                        </div>
                                </div>
                                <div class="mt-3">
                                        <div class="" style="height: 45.7px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                        <h5 style="font-size: 18px; color:#072c66; font-weight: bold">
                                            Variation
                                        </h5>
                                        </div>
                                        <div class="" style="height: 45.7px; background-color: #072c66; display: flex; align-items: center; justify-content: center; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; border-top: 0px; border-bottom: 3px solid #FFFFFF; border-left: 3px solid #FFFFFF; border-right: 3px solid #FFFFFF; color:#FFFFFF;">
                                            <h5 id="variation1" style="font-size: 20px; color:#FFFFFF; font-weight: bold">

                                            </h5>
                                        </div>
                                </div>
                                <div class="mt-3">
                                        <div class="" style="height: 45.7px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                        <h5 style="font-size: 18px; color:#072c66; font-weight: bold">
                                            Efficiency
                                        </h5>
                                        </div>
                                        <div class="" style="height: 45.7px; background-color: #072c66; display: flex; align-items: center; justify-content: center; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; border-top: 0px; border-bottom: 3px solid #FFFFFF; border-left: 3px solid #FFFFFF; border-right: 3px solid #FFFFFF; color:#FFFFFF;">
                                            <h5 id="efficiency1" style="font-size: 20px; color:#FFFFFF; font-weight: bold">
                                            </h5>
                                        </div>
                                </div>
                                <div class="mt-3">
                                        <div class="" style="height: 45.7px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                        <h5 style="font-size: 18px; color:#072c66; font-weight: bold">
                                            Defect Rate
                                        </h5>
                                        </div>
                                        <div class="" style="height: 45.7px; background-color: #072c66; display: flex; align-items: center; justify-content: center; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; border-top: 0px; border-bottom: 3px solid #FFFFFF; border-left: 3px solid #FFFFFF; border-right: 3px solid #FFFFFF; color:#FFFFFF;">
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
            <div class="shadow rounded p-4 d-flex justify-content-center align-items-center" style="max-width: 1300px; width: 100%; height: 100vh; background-color: #e3e5e8;">
                <div class="row g-3">
                        <div class="col-md-1">
                            <div class="card" style="height: 100px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center;">
                                <img src="/nds_wip/public/assets/dist/img/logo-nds4.png" alt="AdminLTE Logo" class="" style="height: 60px; width: 60px;">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card" style="height: 100px;">
                               <div class="card-body" style="display: flex; text-align: start; justify-content: center; flex-direction: column;">
                                    <p class="card-title" id="current-time-2"></p>
                                    <p class="card-text" id="user-name-2"></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card" style="height: 100px;">
                               <div class="card-body" style="display: flex; text-align: start; justify-content: center; flex-direction: column;">
                                    <p class="card-title" id="buyer-name-2"></p>
                                    <p class="card-text" id="buyer-id-2"></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card d-flex justify-content-center align-items-center" style="height: 100px;">
                            <div class="card-body" style="display: flex; text-align: start; justify-content: center; flex-direction: column;">
                                    <p class="card-title" id="realtime-diff-2" style="font-size: 27px; line-height: 30px;">
                                         0 Hours 0 Minutes
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card px-4 py-3" style="height: 220px; background-color: #FFFFFF; flex-direction: column; gap: 15px; justify-content: center;">
                                <div style="display: flex; align-items: center; justify-content: center;">
                                    <div style="height: 80px; width: 100px; background-color: #DA3EBF; display: flex; align-items: center; justify-content: center; border-radius: 20%;">
                                        <img src="/nds_wip/public/assets/dist/img/icon/checked.png" alt="AdminLTE Logo" class="" style="height: 40px; width: 40px;">
                                    </div>
                                    <div style="display: flex; flex-direction: column; width: 100%; margin-left:20px; margin-vertical:0px; padding-vertical:0px">
                                        <p class="card-title" style="font-size: 50px; color: #282828; line-height: 40px;" id="actual-2"></p>
                                        <p  style="color: #64748b; font-size: 21px;">ACTUAL</p>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: center;">
                                    <div style="height: 80px; width: 100px; background-color: #1AD87F; display: flex; align-items: center; justify-content: center; border-radius: 20%;">
                                        <img src="/nds_wip/public/assets/dist/img/icon/target.png" alt="AdminLTE Logo" class="" style="height: 40px; width: 40px;">
                                    </div>
                                    <div style="display: flex; flex-direction: column; width: 100%; margin-left:20px; margin-vertical:0px; padding-vertical:0px">
                                        <p class="card-title" style="font-size: 50px; color: #282828; line-height: 40px;" id="day-target-2"></p>
                                        <p  style="color: #64748b; font-size: 21px;">DAY TARGET</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card px-4 py-3" style="height: 220px; background-color: #FFFFFF; flex-direction: column; gap: 15px; justify-content: center;">
                                <div style="display: flex; align-items: center; justify-content: center;">
                                    <div style="height: 80px; width: 100px; background-color: #1A5CD8; display: flex; align-items: center; justify-content: center; border-radius: 20%;">
                                        <img src="/nds_wip/public/assets/dist/img/icon/checked.png" alt="AdminLTE Logo" class="" style="height: 40px; width: 40px;">
                                    </div>
                                    <div style="display: flex; flex-direction: column; width: 100%; margin-left:20px; margin-vertical:0px; padding-vertical:0px">
                                        <p class="card-title" style="font-size: 50px; color: #282828; line-height: 40px;" id="cumulative-target-2"></p>
                                        <p  style="color: #64748b; font-size: 21px;">Cumulative TARGET</p>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: center;">
                                    <div style="height: 80px; width: 100px; background-color: #1AD87F; display: flex; align-items: center; justify-content: center; border-radius: 20%;">
                                        <img src="/nds_wip/public/assets/dist/img/icon/target.png" alt="AdminLTE Logo" class="" style="height: 40px; width: 40px;">
                                    </div>
                                    <div style="display: flex; flex-direction: column; width: 100%; margin-left:20px; margin-vertical:0px; padding-vertical:0px">
                                        <p class="card-title" style="font-size: 50px; color: #282828; line-height: 40px;" id="req-hour-target-2"></p>
                                        <p  style="color: #64748b; font-size: 21px;">REQ HOUR TARGET</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">

                        <div class="card px-4 py-3" style="height: 220px; background-color: #FFFFFF; flex-direction: column; gap: 15px; justify-content: center;">
                                <div style="display: flex; align-items: center; justify-content: center;">
                                    <div style="height: 80px; width: 100px; background-color: #F3B03B; display: flex; align-items: center; justify-content: center; border-radius: 20%;">
                                        <img src="/nds_wip/public/assets/dist/img/icon/checked.png" alt="AdminLTE Logo" class="" style="height: 40px; width: 40px;">
                                    </div>
                                    <div style="display: flex; flex-direction: column; width: 100%; margin-left:20px; margin-vertical:0px; padding-vertical:0px">
                                        <p class="card-title" style="font-size: 50px; color: #282828; line-height: 40px;" id="deffect-garment-qty-2"></p>
                                        <p  style="color: #64748b; font-size: 21px;">DEFFECT GARMENT QTY</p>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: center;">
                                    <div style="height: 80px; width: 100px; background-color: #1AD87F; display: flex; align-items: center; justify-content: center; border-radius: 20%;">
                                        <img src="/nds_wip/public/assets/dist/img/icon/target.png" alt="AdminLTE Logo" class="" style="height: 40px; width: 40px;">
                                    </div>
                                    <div style="display: flex; flex-direction: column; width: 100%; margin-left:20px; margin-vertical:0px; padding-vertical:0px">
                                        <p class="card-title" style="font-size: 50px; color: #282828; line-height: 40px;" id="rework-balance-2"></p>
                                        <p  style="color: #64748b; font-size: 21px;">REWORK BALANCE</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card px-4" style="height: 220px; background-color: #FFFFFF;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative;">
                                    <div id="chartdiv-efficiency"></div>
                                    <div style="position: absolute; top: 8%; left: 12%; transform: translate(-50%, -50%);">
                                        <p style="color: #64748b; font-size: 20px; margin-top:20px;">EFFICIENCY</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card px-4" style="height: 220px; background-color: #FFFFFF;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative;">
                                    <div id="chartdiv-rft"></div>
                                    <div style="position: absolute; top: 8%; left: 1%; transform: translate(-50%, -50%);">
                                        <p style="color: #64748b; font-size: 20px; margin-top:20px;">RFT</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card px-4" style="height: 220px; background-color: #FFFFFF;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative;">
                                <div id="chartdiv-deffect"></div>
                                    <div style="position: absolute; top: 8%; left: 15%; transform: translate(-50%, -50%);">
                                        <p style="color: #64748b; font-size: 20px; margin-top:20px;">DEFFECT RATE</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </swiper-slide>
        <swiper-slide>
            <div class="shadow rounded p-4 d-flex justify-content-center align-items-center" style="max-width: 1300px; width: 100%; height: 100vh; background-color: #e3e5e8;">
                <div class="row g-3">
                        <div class="col-md-1">
                            <div class="card" style="height: 100px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center;">
                                <img src="/nds_wip/public/assets/dist/img/logo-nds4.png" alt="AdminLTE Logo" class="" style="height: 60px; width: 60px;">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card" style="height: 100px;">
                                <div class="card-body" style="display: flex; text-align: start; justify-content: center; flex-direction: column;">
                                <p class="card-title" id="current-time-3"></p>
                                <p class="card-text" id="user-name-3"></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card" style="height: 100px;">
                                <div class="card-body" style="display: flex; text-align: start; justify-content: center; flex-direction: column;">
                                <p class="card-title" id="buyer-name-3"></p>
                                <p class="card-text" id="buyer-id-3"></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card d-flex justify-content-center align-items-center" style="height: 100px;">
                                <div class="card-body" style="display: flex; text-align: start; justify-content: center; flex-direction: column;">
                                    <p class="card-title" id="realtime-diff-3" style="font-size: 27px; line-height: 30px;">
                                         0 Hours 0 Minutes
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-body d-flex justify-content-center align-items-center">
                                    <div class="show-defect-area" id="show-defect-area">
                                        <div class="position-relative d-flex flex-column justify-content-center align-items-center">
                                            <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
                                                <div class="carousel-inner" id="carousel-inner">


                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body">
                                            <div class="p-0 m-0" style="height: 40px;  background-color: #FFFFFF; display: flex; align-items: center; justify-content: center; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                                <h5  style="font-size: 18px; color:#072c66; font-weight: bold;">
                                                    LIST DEFFECT
                                                </h5>
                                            </div>
                                            <div id="defect-list" class="px-4 w-100" style="text-align: left; background-color: #072c66; display: flex; flex-direction: column; align-items: center; justify-content: start; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; border-top: 0px; border-bottom: 3px solid #FFFFFF; border-left: 3px solid #FFFFFF; border-right: 3px solid #FFFFFF; color:#FFFFFF;">

                                            </div>
                                    </div>
                                </div>
                        </div>
                </div>
            </div>
        </swiper-slide>
    </swiper-container>

    {{-- <tr style="line-height: 18px; " <?php if ($datenow > '07:00' && $datenow <= '08:00') {
        echo 'class="bg-info"';
        } ?>>
        <th scope="row" style="border-color: lightgray;font-size: 18px;">07:00 - 08:00</th>
        <td style="border-color: lightgray;font-size: 18px;"><?php
        if ($jamker < 1) {
        echo '0';
        } elseif ($jamker > 1) {
        echo $datajam7['target1'];
        } else {
        echo $datajam7['target2'];
        } ?></td>
        <td style="border-color: lightgray;font-size: 18px;"><?= $datajam7['output']; ?></td>
        <td style="border-color: lightgray;font-size: 18px;">
        <?php
        if ($jamker < 1) {
        $variance7 = '0';
        } elseif ($jamker > 1) {
        $variance7 = $datajam7['variation1'];
        } else {
        $variance7 = $datajam7['variation2'];
        }

        $varianc7 = abs($variance7);
        echo $varianc7;
        if ($variance7 > 0) {
        echo '<ion-icon name="caret-down-outline" style= "color: red;"></ion-icon>';
        } else {
        echo '<ion-icon name="caret-up-outline" style= "color: green;"></ion-icon>';
        }

        if ($datenow > '07:00') {
        $cumulativeVariation += $variance7;
        }
        ?>
        </td>
        <td style="border-color: lightgray;font-size: 18px;">
        <?php
        if ($jamker < 1) {
        echo '0';
        } elseif ($jamker > 1) {
        echo $datajam7['efficiency1'];
        } else {
        echo $datajam7['efficiency2'];
        }
        ?> %
        </td>
        <td style="border-color: lightgray;font-size: 18px;">
        <?php
        if ($jamker < 1) {
        echo '0';
        } elseif ($jamker > 1) {
        echo $datajam7['defect_rate1'];
        } else {
        echo $datajam7['defect_rate2'];
        }
        ?> %
        </td>
        </tr> --}}

    @include('layouts.script')

    @stack('scripts')
</body>
</html>

<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"
/>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-element-bundle.min.js"></script>

<!-- JSC CHART-->
<script src="https://code.jscharting.com/latest/jscharting.js"></script>

 <!-- SOCKET.IO configuration -->
 <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script> window.laravel_echo_port='{{env("LARAVEL_ECHO_PORT")}}';</script>
    <script src="http://{{ Request::getHost() }}:{{ config('redis.echo_port') }}/socket.io/socket.io.js"></script>
    <script src="{{ config('redis.redis_url_public') }}/js/laravel-echo-setup.js" type="text/javascript"></script>

<script>

    // var lineId = @json($id);
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

        const target = jamKer < 1
            ? 0
            : jamKer > 1
            ? data_table.target1
            : data_table.target2;


        const variance = jamKer < 1
            ? 0
            : jamKer > 1
            ? data_table.variation1
            : data_table.variation2;


        const efficiency = target === 0 ? 0 : (output / target) * 100;

        const defectRate = jamKer < 1
            ? 0
            : jamKer > 1
            ? data_table.defect_rate1
            : data_table.defect_rate2;

        $("#hour-1").text('07:00 - 08:00');
        $("#target-1").text(target);
        $("#output-1").text(output);
        $("#variation-1").html(`
            ${variance}
            <i class="fa ${variance > 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variance > 0 ? 'red' : 'green'};"></ion-icon>
        `);
        $("#efficiency-1").text(`${efficiency.toFixed(2)} %`);
        $("#deffect-rate-1").text(`${defectRate.toFixed(2)} %`);
    }

    function updateTable2(data) {
        const data_table = data.datajam8[0];
        const jamKer = data_table.jam_kerja || 0;
        const output = data_table.output || 0;

        const target = jamKer < 2
            ? 0
            : jamKer > 2
            ? data_table.target1
            : data_table.target2;


        const variance = jamKer < 2
            ? 0
            : jamKer > 2
            ? data_table.variation1
            : data_table.variation2;


        const efficiency = target === 0 ? 0 : (output / target) * 100;

        const defectRate = jamKer < 2
            ? 0
            : jamKer > 2
            ? data_table.defect_rate1
            : data_table.defect_rate2;

        $("#hour-2").text("08:00 - 09:00");
        $("#target-2").text(target);
        $("#output-2").text(output);
        $("#variation-2").html(`
            ${variance}
             <i class="fa ${variance > 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variance > 0 ? 'red' : 'green'};"></ion-icon>
        `);
        $("#efficiency-2").text(`${efficiency.toFixed(2)} %`);
        $("#deffect-rate-2").text(`${defectRate.toFixed(2)} %`);
    }

    function updateTable3(data) {
        const data_table = data.datajam9[0];
        const jamKer = data_table.jam_kerja || 0;
        const output = data_table.output || 0;

        const target = jamKer < 3
            ? 0
            : jamKer > 3
            ? data_table.target1
            : data_table.target2;


        const variance = jamKer < 3
            ? 0
            : jamKer > 3
            ? data_table.variation1
            : data_table.variation2;


        const efficiency = target === 0 ? 0 : (output / target) * 100;

        const defectRate = jamKer < 3
            ? 0
            : jamKer > 3
            ? data_table.defect_rate1
            : data_table.defect_rate2;

        $("#hour-3").text("09:00 - 10:00");
        $("#target-3").text(target);
        $("#output-3").text(output);
        $("#variation-3").html(`
            ${variance}
            <i class="fa ${variance > 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variance > 0 ? 'red' : 'green'};"></ion-icon>
        `);
        $("#efficiency-3").text(`${efficiency.toFixed(2)} %`);
        $("#deffect-rate-3").text(`${defectRate.toFixed(2)} %`);
    }

    function updateTable4(data) {
        const data_table = data.datajam10[0];
        const jamKer = data_table.jam_kerja || 0;
        const output = data_table.output || 0;

        const target = jamKer < 4
            ? 0
            : jamKer > 4
            ? data_table.target1
            : data_table.target2;


        const variance = jamKer < 4
            ? 0
            : jamKer > 4
            ? data_table.variation1
            : data_table.variation2;


        const efficiency = target === 0 ? 0 : (output / target) * 100;

        const defectRate = jamKer < 4
            ? 0
            : jamKer > 4
            ? data_table.defect_rate1
            : data_table.defect_rate2;

        $("#hour-4").text("10:00 - 11:00");
        $("#target-4").text(target);
        $("#output-4").text(output);
        $("#variation-4").html(`
            ${variance}
            <i class="fa ${variance > 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variance > 0 ? 'red' : 'green'};"></ion-icon>
        `);
        $("#efficiency-4").text(`${efficiency.toFixed(2)} %`);
        $("#deffect-rate-4").text(`${defectRate.toFixed(2)} %`);
    }

    function updateTable5(data) {
        const data_table = data.datajam11[0];
        const jamKer = data_table.jam_kerja || 0;
        const output = data_table.output || 0;

        const target = jamKer < 5
            ? 0
            : jamKer > 5
            ? data_table.target1
            : data_table.target2;


        const variance = jamKer < 5
            ? 0
            : jamKer > 5
            ? data_table.variation1
            : data_table.variation2;


        const efficiency = target === 0 ? 0 : (output / target) * 100;

        const defectRate = jamKer < 5
            ? 0
            : jamKer > 5
            ? data_table.defect_rate1
            : data_table.defect_rate2;

        $("#hour-5").text("11:00 - 12:00");
        $("#target-5").text(target);
        $("#output-5").text(output);
        $("#variation-5").html(`
            ${variance}
            <i class="fa ${variance > 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variance > 0 ? 'red' : 'green'};"></ion-icon>
        `);
        $("#efficiency-5").text(`${efficiency.toFixed(2)} %`);
        $("#deffect-rate-5").text(`${defectRate.toFixed(2)} %`);
    }

    function updateTable7(data) {
        const data_table = data.datajam13[0];
        const jamKer = data_table.jam_kerja || 0;
        const output = data_table.output || 0;

        const target = jamKer < 6
            ? 0
            : jamKer > 6
            ? data_table.target1
            : data_table.target2;


        const variance = jamKer < 6
            ? 0
            : jamKer > 6
            ? data_table.variation1
            : data_table.variation2;


        const efficiency = target === 0 ? 0 : (output / target) * 100;

        const defectRate = jamKer < 6
            ? 0
            : jamKer > 6
            ? data_table.defect_rate1
            : data_table.defect_rate2;

        $("#hour-7").text("13:00 - 14:00");
        $("#target-7").text(target);
        $("#output-7").text(output);
        $("#variation-7").html(`
            ${variance}
            <i class="fa ${variance > 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variance > 0 ? 'red' : 'green'};"></ion-icon>
        `);
        $("#efficiency-7").text(`${efficiency.toFixed(2)} %`);
        $("#deffect-rate-7").text(`${defectRate.toFixed(2)} %`);
    }

    function updateTable8(data) {
        const data_table = data.datajam14[0];
        const jamKer = data_table.jam_kerja || 0;
        const output = data_table.output || 0;

        const target = jamKer < 7
            ? 0
            : jamKer > 7
            ? data_table.target1
            : data_table.target2;


        const variance = jamKer < 7
            ? 0
            : jamKer > 7
            ? data_table.variation1
            : data_table.variation2;


        const efficiency = target === 0 ? 0 : (output / target) * 100;

        const defectRate = jamKer < 7
            ? 0
            : jamKer > 7
            ? data_table.defect_rate1
            : data_table.defect_rate2;

        $("#hour-8").text("14:00 - 15:00");
        $("#target-8").text(target);
        $("#output-8").text(output);
        $("#variation-8").html(`
            ${variance}
            <i class="fa ${variance > 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variance > 0 ? 'red' : 'green'};"></ion-icon>
        `);
        $("#efficiency-8").text(`${efficiency.toFixed(2)} %`);
        $("#deffect-rate-8").text(`${defectRate.toFixed(2)} %`);
    }

    function updateTable9(data) {
        const data_table = data.datajam15[0];
        const jamKer = data_table.jam_kerja || 0;
        const output = data_table.output || 0;

        const target = jamKer < 8
            ? 0
            : jamKer > 8
            ? data_table.target1
            : data_table.target2;


        const variance = jamKer < 8
            ? 0
            : jamKer > 8
            ? data_table.variation1
            : data_table.variation2;


        const efficiency = target === 0 ? 0 : (output / target) * 100;

        const defectRate = jamKer < 8
            ? 0
            : jamKer > 8
            ? data_table.defect_rate1
            : data_table.defect_rate2;

        $("#hour-9").text("15:00 - 16:00");
        $("#target-9").text(target);
        $("#output-9").text(output);
        $("#variation-9").html(`
            ${variance}
            <i class="fa ${variance > 0 ? 'fa-caret-down' : 'fa-caret-up'}"
                    style="color: ${variance > 0 ? 'red' : 'green'};"></ion-icon>
        `);
        $("#efficiency-9").text(`${efficiency.toFixed(2)} %`);
        $("#deffect-rate-9").text(`${defectRate.toFixed(2)} %`);
    }

    function updateTable10(data) {
        // Mendefinisikan variabel untuk kolom tabel
        const jamKer = data.jamkerl1 || 0;
        const targetFloor = data.target_floor || 0;
        const targetFloorDom = data.target_floordom || 0;
        const output = data.output16 || 0;
        const defect = data.deffect16 || 0;

        // Hitung nilai Target
        const target = jamKer < 9
            ? 0
            : jamKer > 9
            ? targetFloor
            : targetFloorDom;

        // Hitung Variance
        const variance = target - output;
        const varianceDisplay = Math.abs(variance);

        // Hitung Efficiency
        const efficiency = target === 0 ? 0 : (output / target) * 100;

        // Hitung Defect Rate
        const defectRate = target === 0 ? 0 : (defect / target) * 100;

        // Update tabel
        $("#hour-10").text("16:00 - 17:00"); // Jam tetap
        $("#target-10").text(target); // Target
        $("#output-10").text(output); // Output
        $("#variation-10").html(`
            ${varianceDisplay}
            <ion-icon name="${variance > 0 ? 'caret-down-outline' : 'caret-up-outline'}"
                    style="color: ${variance > 0 ? 'red' : 'green'};"></ion-icon>
        `); // Variance dengan indikator panah
        $("#efficiency-10").text(`${efficiency.toFixed(2)} %`); // Efficiency
        $("#deffect-rate-10").text(`${defectRate.toFixed(2)} %`); // Defect Rate
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
        const icon = varianceSum > 0
            ? '<ion-icon name="caret-down-outline" style="color: red;"></ion-icon>'
            : '<ion-icon name="caret-up-outline" style="color: green;"></ion-icon>';

        // Menampilkan hasilnya pada elemen #variation1
        $("#variation1").html(`
            ${varianceDisplay} ${icon}
        `);
    }

    function efficiency1(data) {
        const dayTarget1 = data.day_target1 || 1;  // Default ke 1 jika day_target1 0 atau undefined
        const actuall1 = data.actuall1 || 0;

        // Menghitung efficiency_sum
        const efficiencySum = (actuall1 / dayTarget1) * 100;
        const roundedEfficiency = efficiencySum.toFixed(2); // Membulatkan hasilnya ke 2 desimal

        // Menampilkan hasilnya pada elemen #efficiency1
        $("#efficiency1").html(`
            ${roundedEfficiency}%
        `);
    }

    function deffect1(data) {
        const dayTarget1 = data.day_target1 || 1;  // Default ke 1 jika day_target1 0 atau undefined
        const deffectl1 = data.deffectl1 || 0;

        // Menghitung deffect_sum
        const deffectSum = (deffectl1 / dayTarget1) * 100;
        const roundedDeffect = deffectSum.toFixed(2); // Membulatkan hasilnya ke 2 desimal

        // Menampilkan hasilnya pada elemen #deffect1
        $("#deffect1").html(`
            ${roundedDeffect}%
        `);
    }

    function commulation2(data) {
        const target_menit = data.target_menit || 0;
        const datelog = new Date();
        const datenow = new Date();

        // Menghitung selisih waktu dalam menit
        const diff = (datenow - datelog) / 1000; // konversi ke detik
        const minutes = Math.floor(diff / 60);
        const jam = Math.floor(minutes / 60);
        let min = minutes;

        // Jika lebih dari 6 jam, kurangi menit dengan 60
        if (jam >= 6) {
            min = minutes - 60;
        }

        // Cumulative calculation
        const cumulative = Math.round(Math.min(min, target_menit) * target_menit, 0);

        // Menampilkan hasil ke elemen dengan id cumulative-target-2
        document.getElementById("cumulative-target-2").textContent = cumulative;
    }

    function showingalldata(data) {
                            $("#user-name").text((data.user || "Line").replace(/_/g, " ").replace(/\b\w/g, (char) => char.toUpperCase()));
                            $("#buyer-name").text(data.buyer || "Unknown -");
                            $("#buyer-id").text(data.no_ws || "Unknown WS");
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
                            $("#buyer-name-2").text(data.buyer || "Unknown -");
                            $("#buyer-id-2").text(data.no_ws || "Unknown WS");
                            $("#actual-2").text(data.actuall1 || "Unknown -");
                            $("#day-target-2").text(data.day_target1 || "Unknown -");
                            $("#req-hour-target-2").text(data.target_floor || "Unknown -");
                            $("#deffect-garment-qty-2").text(data.deffectl1 || "Unknown -");
                            $("#rework-balance-2").text(data.rework || "Unknown -");
                            commulation2(data);

                            // PAGES 3
                            $("#user-name-3").text((data.user || "Line").replace(/_/g, " ").replace(/\b\w/g, (char) => char.toUpperCase()));
                            $("#buyer-name-3").text(data.buyer || "Unknown -");
                            $("#buyer-id-3").text(data.no_ws || "Unknown WS");
                            const defects = data.list_defect;

                            // Clear previous list of defects
                            $('#defect-list').empty();

                            // Loop through each defect item and append to the container
                            $.each(defects, function(index, defect) {
                                const defectHtml = `
                                        <h5 id="defno${index+1}text" style="font-size: 20px; color:#FFFFFF; font-weight: bold; text-align: left; padding-top: 5px; padding-bottom: 5px; width: 100%;">
                                        <input type="hidden" id="defno${index+1}" value="${defect.defect_type_id}">
                                            ${index + 1}. ${defect.defect_type} (${defect.jml} items)
                                        </h5>
                                `;
                                $('#defect-list').append(defectHtml); // Append defect HTML to the list
                            });



                            const carouselContainer = $('#carousel-inner');
                            const linkGambar1 = data.link_gambar1;
                            const positionDefect = data.positiondefect || [];

                            carouselContainer.empty();
                            // Loop through link_gambar1
                            linkGambar1.forEach((gambar1, index) => {
                                console.log("gambar1:", gambar1);
                                const isActive = index === 0 ? 'active' : ''; // Set item pertama sebagai active
                                const defectPoints = positionDefect
                                    .filter(posdef => posdef.image === gambar1.image)
                                    .map(posdef => `
                                        <div class="defect-area-img-point"
                                            data-defect-type="${posdef.defect_type_id}"
                                            data-x="${parseFloat(posdef.defect_area_x)}"
                                            data-y="${parseFloat(posdef.defect_area_y)}">
                                        </div>`).join('');

                                // Add carousel item
                                const carouselItem = `
                                    <div class="${isActive}">
                                        <div class="defect-area-img-container mx-auto overflow-hidden" style="height: 470px; padding-bottom: 20px;">
                                            ${defectPoints}
                                            <img style="opacity: .8; width: auto; height: 470px;"
                                                src="http://10.10.5.60/dashboard-wip/assets/dist/img/upload_files/${gambar1.image}"
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
                                        defectAreaImagePoint[j].style.left = 'calc(' + (defectAreaImagePoint[j].getAttribute('data-x')) + '% - ' + 0.02 * imgWidth + 'px)';
                                        defectAreaImagePoint[j].style.top = 'calc(' + (defectAreaImagePoint[j].getAttribute('data-y')) + '% - ' + 0.02 * imgWidth + 'px)';
                                    }
                                }
                            }
    }

    $(document).ready(async function () {
        const today = new Date();
        const formattedDate = today.toISOString().split('T')[0];
        var lineId = @json($id);
        const data = {
            "tanggal": formattedDate,
            "line_id":lineId
        };

        window.Echo.channel("dashboard-wip-line-channel-" + lineId)
            .listen('.UpdatedDashboardWipLineEvent', (event) => {
                console.log("Data received:", event);
                    if (event && event.data) {
                        const data = event.data;
                        showingalldata(data);
                }
            });


            try {
                const response = await $.ajax({
                    url: '/nds_wip/public/index.php/api/trigger-wip-line/dashboard-line/wip-line-sign',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    success: function (result) {
                        // console.log('Response:', result);
                    },
                    error: function (xhr, status, error) {
                        // console.error('Error:', status, error);
                    }
                });

                showingalldata(response.data.data);
            } catch (error) {
                console.error('Request failed:', error);
            }
    });



    function updateJakartaTime() {
        // Buat objek waktu sekarang
        const now = new Date();

        // Format waktu ke zona waktu Jakarta (Asia/Jakarta)
        const formatter = new Intl.DateTimeFormat("id-ID", {
            timeZone: "Asia/Jakarta",
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
        });

        // Format waktu menjadi array [tanggal, waktu]
        const parts = formatter.formatToParts(now);

        // Ambil bagian tanggal dan waktu
        const formattedDate = `${parts[4].value}-${parts[2].value}-${parts[0].value}`;
        const formattedTime = `${parts[6].value}:${parts[8].value}:${parts[10].value}`;

        // Gabungkan dan tampilkan
        document.getElementById("current-time").textContent = `${formattedDate} ${formattedTime}`;
        document.getElementById("current-time-2").textContent = `${formattedDate} ${formattedTime}`;
        document.getElementById("current-time-3").textContent = `${formattedDate} ${formattedTime}`;

        // Meminta frame berikutnya
        requestAnimationFrame(updateJakartaTime);
    }

    // Memulai pembaruan waktu
    updateJakartaTime();

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

        // Hitung jam dan menit
        let totalMinutes = Math.floor(diff / (1000 * 60));
        const hours = Math.floor(totalMinutes / 60);
        let minutes = totalMinutes % 60;

        // Case berdasarkan waktu tertentu
        const currentTime = now.toTimeString().split(" ")[0]; // Format HH:MM:SS
        if (currentTime >= "12:00:00" && currentTime <= "13:00:00") {
            // Jam istirahat (12:00 - 13:00), tidak menghitung menit
            minutes = 0;
        }

        // Tampilkan hasil di elemen
        document.getElementById("realtime-diff").textContent = `${hours} Hours ${minutes} Minutes`;
        document.getElementById("realtime-diff-2").textContent = `${hours} Hours ${minutes} Minutes`;
        document.getElementById("realtime-diff-3").textContent = `${hours} Hours ${minutes} Minutes`;

        // Perbarui perhitungan setiap detik
        requestAnimationFrame(updateRealtimeDiff);
    }

    // Mulai perhitungan real-time
    updateRealtimeDiff();



 </script>


<!-- CHART EFFICIENCY -->
<script>
      var chart = JSC.chart('chartdiv-efficiency', {
        debug: false,
        legend_visible: false,
        defaultTooltip_enabled: false,
        xAxis_spacingPercentage: 0.4,
        yAxis: [
          {
            id: 'ax1',
            defaultTick: { padding: 10, enabled: false },
            customTicks: [350, 600, 700, 800, 850],
            line: {
              width: 10,

              /*Defining the option will enable it.*/
              breaks: {},

              /*Palette is defined at series level with an ID referenced here.*/
              color: 'smartPalette:pal1'
            },
            scale_range: [350, 850]
          }
        ],
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
        series: [
          {
            type: 'column roundcaps',
            name: 'Temperatures',
            yAxis: 'ax1',
            palette: {
              id: 'pal1',
              pointValue: '%yValue',
              ranges: [
                { value: 350, color: '#FF5353' },
                { value: 600, color: '#FFD221' },
                { value: 700, color: '#77E6B4' },
                { value: [800, 850], color: '#21D683' }
              ]
            },
            points: [['x', [350, 720]]]
          },
        ]
      });
</script>

<!-- CHART RFT -->
<script>
      var chart = JSC.chart('chartdiv-rft', {
        debug: false,
        legend_visible: false,
        defaultTooltip_enabled: false,
        xAxis_spacingPercentage: 0.4,
        yAxis: [
          {
            id: 'ax1',
            defaultTick: { padding: 10, enabled: false },
            customTicks: [350, 600, 700, 800, 850],
            line: {
              width: 10,

              /*Defining the option will enable it.*/
              breaks: {},

              /*Palette is defined at series level with an ID referenced here.*/
              color: 'smartPalette:pal1'
            },
            scale_range: [350, 850]
          }
        ],
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
        series: [
          {
            type: 'column roundcaps',
            name: 'Temperatures',
            yAxis: 'ax1',
            palette: {
              id: 'pal1',
              pointValue: '%yValue',
              ranges: [
                { value: 350, color: '#FF5353' },
                { value: 600, color: '#FFD221' },
                { value: 700, color: '#77E6B4' },
                { value: [800, 850], color: '#21D683' }
              ]
            },
            points: [['x', [350, 720]]]
          },
        ]
      });
</script>

<!-- CHART DEFFECT -->
<script>
      var chart = JSC.chart('chartdiv-deffect', {
        debug: false,
        legend_visible: false,
        defaultTooltip_enabled: false,
        xAxis_spacingPercentage: 0.4,
        yAxis: [
          {
            id: 'ax1',
            defaultTick: { padding: 10, enabled: false },
            customTicks: [350, 600, 700, 800, 850],
            line: {
              width: 10,

              /*Defining the option will enable it.*/
              breaks: {},

              /*Palette is defined at series level with an ID referenced here.*/
              color: 'smartPalette:pal1'
            },
            scale_range: [350, 850]
          }
        ],
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
        series: [
          {
            type: 'column roundcaps',
            name: 'Temperatures',
            yAxis: 'ax1',
            palette: {
              id: 'pal1',
              pointValue: '%yValue',
              ranges: [
                { value: 350, color: '#FF5353' },
                { value: 600, color: '#FFD221' },
                { value: 700, color: '#77E6B4' },
                { value: [800, 850], color: '#21D683' }
              ]
            },
            points: [['x', [350, 720]]]
          },
        ]
      });
</script>

{{-- FOOTER --}}

<script>
  const datelog = date("Y-m-d 07:00:00");
  const datenow = date("Y-m-d H:i:s");
  const timenow = date("H:i:s");
  const awal  = strtotime(datelog);
  const akhir = strtotime(datenow); // waktu sekarang
  const diff  = akhir - awal;
  const jam   = floor(diff / (60 * 60));
  const minutes   = floor(diff / 60);
  const menit = diff - jam * (60 * 60);
  const menitkerja = jamker * 60;
  let min = 0;
  let min_ = 0;
  if (jam >= 6) {
    min = minutes - 60;
  } else {
    min = minutes;
  }

  if (min > menitkerja) {
    min_ = menitkerja;
  } else {
    min_ = min;
  }

  const m_prod = actual * smv;
  const m_tersedia = menpower * min_;
  const percen_effi = m_tersedia > 0 ? m_prod / m_tersedia * 100 : 0;

  const per_effi = round(percen_effi, 2);


  var data = per_effi;
   if (per_effi >= 75) {
    var warna = "#49FF00";
   }else{
    var warna = "#ff0000";
    }
  var options1 = {
    chart: {
      height: 320,
      type: "radialBar",
      animations: {
        enabled: true,
        easing: 'easeinout',
        speed: 3000,
        animateGradually: {
          enabled: true,
          delay: 3500
        },
        dynamicAnimation: {
          enabled: true,
          speed: 3000
        }
      }
    },
    series: [data],
    colors: [warna],
    plotOptions: {
      radialBar: {
        startAngle: -135,
        endAngle: 135,
        track: {
          background: '#333',
          startAngle: -135,
          endAngle: 135,
        },
        dataLabels: {
          name: {
            show: false,
          },
          value: {
            color: "#fff",
            fontSize: "42px",
            show: true
          }
        }
      }
    },
    fill: {
      type: 'gradient',
      gradient: {
        shade: 'dark',
        type: "horizontal",
        shadeIntensity: 0.5,
        gradientToColors: undefined,
        inverseColors: true,
        opacityFrom: 1,
        opacityTo: 1,
        stops: [0, 100],
        colorStops: []
      }
    },
    stroke: {
      lineCap: "butt"
    },
    labels: ["Progress"]
  };

  new ApexCharts(document.querySelector("#chart_eff_line"), options1).render();
</script>


<script>
  if (actual == 0) {
    actual = 1;
  } else {
    actual = 200;
  }
  var percen_deffect = rfts / actual * 100;
  var per_deffect = round(percen_deffect, 2);
  var data = rfts;
  var options1 = {
    chart: {
      height: 320,
      type: "radialBar",
      animations: {
        enabled: true,
        easing: 'easeinout',
        speed: 3000,
        animateGradually: {
          enabled: true,
          delay: 3500
        },
        dynamicAnimation: {
          enabled: true,
          speed: 3000
        }
      }
    },
    series: [data],
    colors: ["#49FF00"],
    plotOptions: {
      radialBar: {
        startAngle: -135,
        endAngle: 135,
        track: {
          background: '#333',
          startAngle: -135,
          endAngle: 135,
        },
        dataLabels: {
          name: {
            show: false,
          },
          value: {
            color: "#fff",
            fontSize: "42px",
            show: true
          }
        }
      }
    },
    fill: {
      type: 'gradient',
      gradient: {
        shade: 'dark',
        type: "horizontal",
        shadeIntensity: 0.5,
        gradientToColors: undefined, // optional, if not defined - uses the shades of same color in series
        inverseColors: true,
        opacityFrom: 1,
        opacityTo: 1,
        stops: [0, 100],
        colorStops: []
      }
    },
    stroke: {
      lineCap: "butt"
    },
    labels: ["Progress"]
  };

  new ApexCharts(document.querySelector("#chart_rft_line"), options1).render();
</script>

<script>
  if (actual == 0) {
    actual = 1;
  } else {
    actual = actual;
  }
  const percen_deffect = deffect / actual * 100;
  const per_deffect = round(percen_deffect, 2);

  var data = defects;
  var options1 = {
    chart: {
      height: 320,
      type: "radialBar",
    },
    series: [data],
    colors: ["#FD0101"],
    plotOptions: {
      radialBar: {
        startAngle: -135,
        endAngle: 135,
        track: {
          background: '#333',
          startAngle: -135,
          endAngle: 135,
        },
        dataLabels: {
          name: {
            show: false,
          },
          value: {
            color: "#fff",
            fontSize: "42px",
            show: true
          }
        }
      }
    },
    fill: {
      type: "gradient",
      gradient: {
        shade: "dark",
        type: "horizontal",
        gradientToColors: undefined,
        stops: [0, 100]
      }
    },
    stroke: {
      lineCap: "butt"
    },
    labels: ["Progress"]
  };

  new ApexCharts(document.querySelector("#chart_def_line"), options1).render();
</script>
