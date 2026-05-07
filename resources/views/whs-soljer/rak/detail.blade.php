<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard RAK</title>
    <link rel="icon" type="image/png" href="/nds_wip/public/assets/dist/img/logo-nds4.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Poppins', sans-serif;
        }

        /* HEADER */
        .header-rak {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .header-rak h2 {
            margin: 0; /* ⬅️ ini yang paling penting */
        }

        .header-rak img {
            height: 55px;
            width: 55px;
        }

        .header-rak h2 {
            margin-left: 15px;
            font-weight: 700;
            color: #082149;
        }

        .badge-rak {
            background: #082149;
            color: #fff;
            font-weight: 500;
            font-size: 32px;
            padding: 0px 14px;
            border-radius: 8px;
        }

        /* CARD */
        .card-custom {
            border: none;
            border-radius: 18px;
            color: white;
            overflow: hidden;
            position: relative;
            transition: all 0.3s ease;
            min-height: 140px;
        }

        .card-custom:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .card-body {
            padding: 28px;
            position: relative;
            z-index: 2;
        }

        .card-title {
            font-size: 20px;
            font-weight: 600;
        }

        .card-value {
            font-size: 34px;
            font-weight: bold;
            margin-top: 10px;
        }

        .card-icon {
            font-size: 30px;
            position: absolute;
            right: 25px;
            top: 25px;
            opacity: 0.7;
        }

        /* Bubble */
        .card-custom::before,
        .card-custom::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
        }

        .card-custom::before {
            width: 140px;
            height: 140px;
            top: -40px;
            right: -40px;
        }

        .card-custom::after {
            width: 100px;
            height: 100px;
            bottom: -30px;
            right: 20px;
        }

        .bg-kapasitas {
            background: linear-gradient(135deg, #ff6a88, #ff99ac);
        }

        .bg-terisi {
            background: linear-gradient(135deg, #1e90ff, #74b9ff);
        }

        .bg-kosong {
            background: linear-gradient(135deg, #20c997, #7bed9f);
        }

        #list-barang {
            font-weight: 700;
            color: #082149;
        }

        .bg-sb th {
            background-color: #082149;
            color: #fff;
            border-color: #082149;
            font-weight: 600;
        }
    </style>
</head>

<body>

<div class="container-fluid my-4 px-5">

    <!-- HEADER -->
    <div class="header-rak">
        <img src="/nds_wip/public/assets/dist/img/logo-nds4.png">
        <h2>RAK</h2>
        <span class="badge-rak ms-2">{{ $data->kode_lok }}</span>
        <input type="hidden" id="kode_lok" value="{{ $data->kode_lok }}">
    </div>

    <!-- CARDS -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card card-custom bg-kapasitas">
                <div class="card-body">
                    <div class="card-title">Kapasitas</div>
                    <div class="card-value">{{ $jumlah_barang }}</div>
                    <i class="mdi mdi-view-dashboard card-icon"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-custom bg-terisi">
                <div class="card-body">
                    <div class="card-title">Jumlah Barang</div>
                    <div class="card-value">{{ $jumlah_barang }}</div>
                    <i class="mdi mdi-cube-outline card-icon"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-custom bg-kosong">
                <div class="card-body">
                    <div class="card-title">Ruang Kosong</div>
                    <div class="card-value"> 0</div>
                    <i class="mdi mdi-package-variant-closed card-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <h3 id="list-barang" class="pt-3">LIST BARANG</h3>

    <!-- TABLE -->
    <div class="table-responsive">
        <table id="datatable" class="table table-bordered table-striped w-100">
            <thead class="bg-sb">
                <tr>
                    <th>Barcode</th>
                    <th>Lokasi</th>
                    <th>Buyer</th>
                    <th>Keterangan</th>
                    <th>Jenis Item</th>
                    <th>Warna</th>
                    <th>Lot</th>
                    <th>No Roll</th>
                    <th>Qty</th>
                    <th>Satuan</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

</div>

<!-- jQuery (WAJIB) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {

    // TEST MODE (tanpa server dulu)
    $('#datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("dashboard-rak-detail-get-data-rak") }}',
            data: function (d) {
                d.lokasi = $("#kode_lok").val();
            }
        },
        columns: [
            { data: 'barcode' },
            { data: 'lokasi' },
            { data: 'buyer' },
            { data: 'keterangan' },
            { data: 'jenis_item' },
            { data: 'warna' },
            { data: 'lot' },
            { data: 'no_roll' },
            {
                data: 'qty_saat_ini',
                className: 'text-end',
                render: function(data, type, row) {
                    return parseFloat(data).toFixed(2);
                }
            },
            { data: 'satuan' },
        ]
    });
});
</script>

</body>
</html>