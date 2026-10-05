<!-- Bootstrap -->
<link rel="stylesheet" href="{{ asset('plugins/bootstrap/css/bootstrap.min.css') }}">
<!-- jQuery UI -->
<link rel="stylesheet" href="{{ asset('plugins/jquery-ui/jquery-ui.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/jquery-ui/jquery-ui-timepicker-addon.css') }}">
<!-- Font Awesome -->
<link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/fontawesome-free-6.4.2/css/all.min.css') }}">
<!-- Theme Style -->
<link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
<!-- Izi Toast CSS -->
<link rel="stylesheet" href="{{ asset('plugins/izitoast/dist/css/iziToast.min.css') }}">
<!-- Sweet Alert CSS -->
<link rel="stylesheet" href="{{ asset('plugins/sweetalert/dist/sweetalert2.min.css') }}">
<!-- Custom Style -->
<link rel="stylesheet" href="{{ asset('dist/css/style.css') }}">
<!-- Livewire Styles -->
@livewireStyles
<style>
    .drop-zone {
        border: 2px dashed #7a7a7a;
        border-radius: 16px;
        padding: 20px;
        background-color: #ffffff;
        transition: all 0.2s ease-in-out;
    }

    .drop-zone.drag-over {
        background-color: #f0f4ff;
        border-color: #0d6efd;
    }

    .navbar-nav .dropdown-menu {
        max-height: 80vh !important;
        overflow-y: auto !important;
    }

    @media (max-width: 767.98px) {
        /* 1. Izinkan kontainer navbar di HP agar memiliki scroll jika menu melebihi layar */
        .navbar-collapse {
            max-height: 75vh !important;
            overflow-y: auto !important;
        }

        /* 2. Ubah dropdown dari posisi melayang (absolute) menjadi menyatu (static) */
        .navbar-nav .dropdown-menu {
            position: static !important;
            float: none !important;
            box-shadow: none !important;
            border: none !important;
            background-color: transparent !important;
            transform: none !important; /* Mencegah style bawaan Bootstrap Popper.js */
        }
    }
</style>

@yield('custom-link')
