<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NDS - WIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
}
.card-text{
    font-size: 25px;
    font-weight: 600;
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
    color: #333333;
    border-color: lightgray !important;
    font-size: 20px;
    background-color: #FFFFFF !important;
}

.bg-linear{
    background: linear-gradient(to bottom right, #1A5CD8, #007aff);
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
</style>
<!-- autoplay-delay="15000"  -->
<!-- autoplay-disable-on-interaction="false" -->

<body class=" d-flex justify-content-center align-items-center" style="min-height: 100vh; background-color: #072c66;">
    <swiper-container class="mySwiper" space-between="30" centered-slides="true" 
    >
        <swiper-slide>
            <div class="shadow rounded p-4 d-flex justify-content-center align-items-center" style="max-width: 1300px; width: 100%; height: 100vh; background-color: #e3e5e8;">
                <div class="row g-3">
                        <div class="col-md-1">
                            <div class="card" style="height: 100px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center;">
                                <img src="http://localhost/nds_wip/public/assets/dist/img/logo-nds4.png" alt="AdminLTE Logo" class="" style="height: 60px; width: 60px;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card" style="height: 100px;">
                                <div class="card-body">
                                    <p class="card-title">2024-12-26 13:46:30</p>
                                    <div class="mb-2" style="height:2px; background-color: #FFFFFF; width: 100%;"></div>
                                    <p class="card-text">Line 01 | End Line.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card" style="height: 100px;">
                                <div class="card-body">
                                    <p class="card-title">KANMAX ENTERPRISES LTD</p>
                                    <p class="card-text">KNM/1224/086</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card d-flex justify-content-center align-items-center" style="height: 100px;">
                                <div class="card-body text-center">
                                    <p class="card-title" style="font-size: 30px; margin-top:1px;  line-height: 36px;">5 Hours 47 Minutes</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-9">
                            <div class="card">
                                <div class="card-body">
                                    <table class="table border-collapse" style="background-color: #072c66; font-size: 12px;text-align: center;border-radius: 10px;">
                                        <thead class="thead-custom">
                                            <tr>
                                            <th scope="col" style="border-color: lightgray;font-size: 20px; border-top-left-radius: 10px; color: #072c66;">Hours</th>
                                            <th scope="col" style="border-color: lightgray;font-size: 20px; color: #072c66;">Target</th>
                                            <th scope="col" style="border-color: lightgray;font-size: 20px; color: #072c66;">Output</th>
                                            <th scope="col" style="border-color: lightgray;font-size: 20px; color: #072c66;">Variation</th>
                                            <th scope="col" style="border-color: lightgray;font-size: 20px; color: #072c66;">Efficiency</th>
                                            <th scope="col" style="border-color: lightgray;font-size: 20px; border-top-right-radius: 10px; color: #072c66;">Defect Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr style="line-height: 22px;">
                                                <th scope="row" style="border-color: lightgray;font-size: 18px;">07:00 - 08:00</th>
                                                <td style="border-color: lightgray;font-size: 18px;">189</td>
                                                <td style="border-color: lightgray;font-size: 18px;">89</td>
                                                <td style="border-color: lightgray;font-size: 18px;">100</td>
                                                <td style="border-color: lightgray;font-size: 18px;">46.87 %</td>
                                                <td style="border-color: lightgray;font-size: 18px;">5.82 %</td>
                                            </tr>
                                            <tr style="line-height: 22px;">
                                                <th scope="row" style="border-color: lightgray;font-size: 18px;">08:00 - 09:00</th>
                                                <td style="border-color: lightgray;font-size: 18px;">291</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0</td>
                                                <td style="border-color: lightgray;font-size: 18px;">291</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0.00 %</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0.00 %</td>
                                            </tr>
                                            <tr style="line-height: 22px;">
                                                <th scope="row" style="border-color: lightgray;font-size: 18px;">09:00 - 10:00</th>
                                                <td style="border-color: lightgray;font-size: 18px;">291</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0</td>
                                                <td style="border-color: lightgray;font-size: 18px;">291</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0.00 %</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0.00 %</td>
                                            </tr>
                                            <tr style="line-height: 22px;">
                                                <th scope="row" style="border-color: lightgray;font-size: 18px;">10:00 - 11:00</th>
                                                <td style="border-color: lightgray;font-size: 18px;">189</td>
                                                <td style="border-color: lightgray;font-size: 18px;">89</td>
                                                <td style="border-color: lightgray;font-size: 18px;">100</td>
                                                <td style="border-color: lightgray;font-size: 18px;">46.87 %</td>
                                                <td style="border-color: lightgray;font-size: 18px;">5.82 %</td>
                                            </tr>
                                            <tr style="line-height: 22px;">
                                            <th scope="row" style="border-color: lightgray;font-size: 18px;">11:00 - 12:00</th>
                                                <td style="border-color: lightgray;font-size: 18px;">291</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0</td>
                                                <td style="border-color: lightgray;font-size: 18px;">291</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0.00 %</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0.00 %</td>
                                            </tr>
                                            <tr style="line-height: 22px;" class="bg-danger">
                                                <th scope="row" style="border-color: lightgray;font-size: 18px;">12:00 - 13:00</th>
                                                <td style="border-color: lightgray;font-size: 18px;">0</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0%</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0%</td>
                                            </tr>
                                            <tr style="line-height: 22px;">
                                                <th scope="row" style="border-color: lightgray;font-size: 18px;">13:00 - 14:00</th>
                                                <td style="border-color: lightgray;font-size: 18px;">291</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0</td>
                                                <td style="border-color: lightgray;font-size: 18px;">291</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0.00 %</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0.00 %</td>
                                            </tr>
                                            <tr style="line-height: 22px;">
                                                <th scope="row" style="border-color: lightgray;font-size: 18px;">14:00 - 15:00</th>
                                                <td style="border-color: lightgray;font-size: 18px;">291</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0</td>
                                                <td style="border-color: lightgray;font-size: 18px;">291</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0.00 %</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0.00 %</td>
                                            </tr>
                                            <tr style="line-height: 22px;">
                                            <th scope="row" style="border-color: lightgray;font-size: 18px;">15:00 - 16:00</th>
                                                <td style="border-color: lightgray;font-size: 18px;">291</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0</td>
                                                <td style="border-color: lightgray;font-size: 18px;">291</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0.00 %</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0.00 %</td>
                                            </tr>
                                            <tr style="line-height: 22px;">
                                            <th scope="row" style="border-color: lightgray;font-size: 18px;">16:00 - 17:00</th>
                                                <td style="border-color: lightgray;font-size: 18px;">291</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0</td>
                                                <td style="border-color: lightgray;font-size: 18px;">291</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0.00 %</td>
                                                <td style="border-color: lightgray;font-size: 18px;">0.00 %</td>
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
                                        <div class="" style="height: 38px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                        <h5 style="font-size: 18px; color:#072c66; font-weight: bold">
                                            Target
                                        </h5>
                                        </div>
                                        <div class="" style="height: 38px; background-color: #072c66; display: flex; align-items: center; justify-content: center; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; border-top: 0px; border-bottom: 3px solid #FFFFFF; border-left: 3px solid #FFFFFF; border-right: 3px solid #FFFFFF; color:#FFFFFF;">
                                        <h5 style="font-size: 20px; color:#FFFFFF; font-weight: bold">
                                            1519
                                        </h5>
                                        </div>
                                </div>
                                <div class="mt-3">
                                        <div class="" style="height: 38px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                        <h5 style="font-size: 18px; color:#072c66; font-weight: bold">
                                            Output
                                        </h5>
                                        </div>
                                        <div class="" style="height: 38px; background-color: #072c66; display: flex; align-items: center; justify-content: center; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; border-top: 0px; border-bottom: 3px solid #FFFFFF; border-left: 3px solid #FFFFFF; border-right: 3px solid #FFFFFF; color:#FFFFFF;">
                                        <h5 style="font-size: 20px; color:#FFFFFF; font-weight: bold">
                                            1519
                                        </h5>
                                        </div>
                                </div>
                                <div class="mt-3">
                                        <div class="" style="height: 38px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                        <h5 style="font-size: 18px; color:#072c66; font-weight: bold">
                                            Variation
                                        </h5>
                                        </div>
                                        <div class="" style="height: 38px; background-color: #072c66; display: flex; align-items: center; justify-content: center; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; border-top: 0px; border-bottom: 3px solid #FFFFFF; border-left: 3px solid #FFFFFF; border-right: 3px solid #FFFFFF; color:#FFFFFF;">
                                        <h5 style="font-size: 20px; color:#FFFFFF; font-weight: bold">
                                            1519
                                        </h5>
                                        </div>
                                </div>
                                <div class="mt-3">
                                        <div class="" style="height: 38px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                        <h5 style="font-size: 18px; color:#072c66; font-weight: bold">
                                            Efficiency
                                        </h5>
                                        </div>
                                        <div class="" style="height: 38px; background-color: #072c66; display: flex; align-items: center; justify-content: center; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; border-top: 0px; border-bottom: 3px solid #FFFFFF; border-left: 3px solid #FFFFFF; border-right: 3px solid #FFFFFF; color:#FFFFFF;">
                                        <h5 style="font-size: 20px; color:#FFFFFF; font-weight: bold">
                                            1519
                                        </h5>
                                        </div>
                                </div>
                                <div class="mt-3">
                                        <div class="" style="height: 38px; background-color: #FFFFFF; display: flex; align-items: center; justify-content: center; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                        <h5 style="font-size: 18px; color:#072c66; font-weight: bold">
                                            Defect Rate
                                        </h5>
                                        </div>
                                        <div class="" style="height: 38px; background-color: #072c66; display: flex; align-items: center; justify-content: center; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; border-top: 0px; border-bottom: 3px solid #FFFFFF; border-left: 3px solid #FFFFFF; border-right: 3px solid #FFFFFF; color:#FFFFFF;">
                                        <h5 style="font-size: 20px; color:#FFFFFF; font-weight: bold">
                                            1519
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
                                <img src="http://localhost/nds_wip/public/assets/dist/img/logo-nds4.png" alt="AdminLTE Logo" class="" style="height: 60px; width: 60px;">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card" style="height: 100px;">
                                <div class="card-body">
                                    <p class="card-title">2024-12-26 13:46:30</p>
                                    <div class="mb-2" style="height:2px; background-color: #FFFFFF; width: 100%;"></div>
                                    <p class="card-text">Line 01 | End Line.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card" style="height: 100px;">
                                <div class="card-body">
                                    <p class="card-title">KANMAX ENTERPRISES LTD</p>
                                    <p class="card-text">KNM/1224/086</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card d-flex justify-content-center align-items-center" style="height: 100px;">
                                <div class="card-body text-center">
                                    <p class="card-title" style="font-size: 30px; margin-top:10px;  line-height: 36px;">5 Hours 47 Minutes</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card px-4 py-3" style="height: 220px; background-color: #FFFFFF;">
                                <div style="display: flex; align-items: center; justify-content: center;">
                                    <div style="height: 80px; width: 100px; background-color: #DA3EBF; display: flex; align-items: center; justify-content: center; border-radius: 20%;">
                                        <img src="http://localhost/nds_wip/public/assets/dist/img/icon/checked.png" alt="AdminLTE Logo" class="" style="height: 40px; width: 40px;">
                                    </div>
                                    <div class="card-body" style="width:100%">
                                        <p class="card-title" style="font-size: 50px; color: #282828;">205</p>
                                        <p class="card-text" style="color: #505050; font-size: 21px; margin-top: 16px;">ACTUAL</p>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: center;">
                                    <div style="height: 80px; width: 100px; background-color: #1AD87F; display: flex; align-items: center; justify-content: center; border-radius: 20%;">
                                        <img src="http://localhost/nds_wip/public/assets/dist/img/icon/target.png" alt="AdminLTE Logo" class="" style="height: 40px; width: 40px;">
                                    </div>
                                    <div class="card-body" style="width:100%">
                                        <p class="card-title" style="font-size: 50px; color: #282828;">1519</p>
                                        <p class="card-text" style="color: #505050; font-size: 21px; margin-top: 16px;">DAY TARGET</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card px-4 py-3" style="height: 220px; background-color: #FFFFFF;">
                                <div style="display: flex; align-items: center; justify-content: center;">
                                    <div style="height: 80px; width: 100px; background-color: #1A5CD8; display: flex; align-items: center; justify-content: center; border-radius: 20%;">
                                        <img src="http://localhost/nds_wip/public/assets/dist/img/icon/checked.png" alt="AdminLTE Logo" class="" style="height: 40px; width: 40px;">
                                    </div>
                                    <div class="card-body" style="width:100%">
                                        <p class="card-title" style="font-size: 50px; color: #282828;">203</p>
                                        <p class="card-text" style="color: #505050; font-size: 21px; margin-top: 16px;">REALTIME TARGET</p>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: center;">
                                    <div style="height: 80px; width: 100px; background-color: #1AD87F; display: flex; align-items: center; justify-content: center; border-radius: 20%;">
                                        <img src="http://localhost/nds_wip/public/assets/dist/img/icon/target.png" alt="AdminLTE Logo" class="" style="height: 40px; width: 40px;">
                                    </div>
                                    <div class="card-body" style="width:100%">
                                        <p class="card-title" style="font-size: 50px; color: #282828;">189</p>
                                        <p class="card-text" style="color: #505050; font-size: 21px; margin-top: 16px;">REQ HOUR TARGET</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card px-4 py-3" style="height: 220px; background-color: #FFFFFF;">
                                <div style="display: flex; align-items: center; justify-content: center;">
                                    <div style="height: 80px; width: 100px; background-color: #F3B03B; display: flex; align-items: center; justify-content: center; border-radius: 20%;">
                                        <img src="http://localhost/nds_wip/public/assets/dist/img/icon/checked.png" alt="AdminLTE Logo" class="" style="height: 40px; width: 40px;">
                                    </div>
                                    <div class="card-body" style="width:100%">
                                        <p class="card-title" style="font-size: 50px; color: #282828;">5</p>
                                        <p class="card-text" style="color: #505050; font-size: 21px; margin-top: 16px;">DEFFECT GARMENT QTY</p>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: center;">
                                    <div style="height: 80px; width: 100px; background-color: #1AD87F; display: flex; align-items: center; justify-content: center; border-radius: 20%;">
                                        <img src="http://localhost/nds_wip/public/assets/dist/img/icon/target.png" alt="AdminLTE Logo" class="" style="height: 40px; width: 40px;">
                                    </div>
                                    <div class="card-body" style="width:100%">
                                        <p class="card-title" style="font-size: 50px; color: #282828;">5</p>
                                        <p class="card-text" style="color: #505050; font-size: 21px; margin-top: 16px;">REWORK</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card px-4" style="height: 220px; background-color: #FFFFFF;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative;">
                                    <div id="chartdiv-efficiency"></div>
                                    <div style="position: absolute; top: 8%; left: 12%; transform: translate(-50%, -50%);">
                                        <p class="card-text" style="color: #505050; font-size: 21px;">EFFICIENCY</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card px-4" style="height: 220px; background-color: #FFFFFF;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative;">
                                    <div id="chartdiv-rft"></div>
                                    <div style="position: absolute; top: 8%; left: 1%; transform: translate(-50%, -50%);">
                                        <p class="card-text" style="color: #505050; font-size: 21px;">RFT</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card px-4" style="height: 220px; background-color: #FFFFFF;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative;">
                                <div id="chartdiv-deffect"></div>
                                    <div style="position: absolute; top: 8%; left: 15%; transform: translate(-50%, -50%);">
                                        <p class="card-text" style="color: #505050; font-size: 21px;">DEFFECT RATE</p>
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
                                <img src="http://localhost/nds_wip/public/assets/dist/img/logo-nds4.png" alt="AdminLTE Logo" class="" style="height: 60px; width: 60px;">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card" style="height: 100px;">
                                <div class="card-body">
                                    <p class="card-title">2024-12-26 13:46:30</p>
                                    <div class="mb-2" style="height:2px; background-color: #FFFFFF; width: 100%;"></div>
                                    <p class="card-text">Line 01 | End Line.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card" style="height: 100px;">
                                <div class="card-body">
                                    <p class="card-title">KANMAX ENTERPRISES LTD</p>
                                    <p class="card-text">KNM/1224/086</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card d-flex justify-content-center align-items-center" style="height: 100px;">
                                <div class="card-body text-center">
                                    <p class="card-title" style="font-size: 30px; margin-top:10px;  line-height: 36px;">5 Hours 47 Minutes</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-body d-flex justify-content-center align-items-center">
                                    <img src="http://10.10.5.60/dashboard-wip/assets/dist/img/upload_files/poloshirt.PNG" alt="AdminLTE Logo" class="" style="width: 85%;"/>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="">
                                <div class="card-body">
                                    <div>
                                            <div class="" style="height: 38px;  background-color: #FFFFFF; display: flex; align-items: center; justify-content: center; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                                <h5 style="font-size: 18px; color:#072c66; font-weight: bold">
                                                    List Deffect
                                                </h5>
                                            </div>
                                            <div class="px-4" style="padding-top: 10px; background-color: #072c66; display: flex; flex-direction: column; align-items: center; justify-content: center; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; border-top: 0px; border-bottom: 3px solid #FFFFFF; border-left: 3px solid #FFFFFF; border-right: 3px solid #FFFFFF; color:#FFFFFF;">
                                                <h5 style="font-size: 20px; color:#FFFFFF; font-weight: bold">
                                                    1. Posisi miring / Bentuk tidak bagus (r02)
                                                </h5>
                                                <h5 style="font-size: 20px; color:#FFFFFF; font-weight: bold">
                                                    1. Posisi miring / Bentuk tidak bagus (r02)
                                                </h5>
                                            </div>
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

    // const swiper = new Swiper('.swiper', {
    // direction: 'vertical',
    // loop: true,

    // pagination: {
    //     el: '.swiper-pagination',
    // },

    // navigation: {
    //     nextEl: '.swiper-button-next',
    //     prevEl: '.swiper-button-prev',
    // },

    // scrollbar: {
    //     el: '.swiper-scrollbar',
    // },
    // });

    $(document).ready(async function () {
        console.log("HALLO",currentDate);
        window.Echo.channel("dashboard-wip-line-channel")
            .listen('.UpdatedDashboardWipLineEvent', (data) => {
                console.log("Data received:", data);
            });
    });

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