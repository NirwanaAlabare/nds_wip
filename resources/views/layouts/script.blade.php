<!-- jQuery -->
<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 5 -->
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- jQuery UI -->
<script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<!-- Ekko Lightbox -->
<script src="{{ asset('plugins/ekko-lightbox/ekko-lightbox.min.js') }}"></script>
<!-- Izi Toast -->
<script src="{{ asset('plugins/izitoast/dist/js/iziToast.min.js') }}"></script>
<!-- Sweet Alert -->
<script src="{{ asset('plugins/sweetalert/dist/sweetalert2.all.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('dist/js/script.js') }}"></script>
<!-- HTML5 QR Code -->
<script src="{{ asset('plugins/html5-qrcode/html5-qrcode.min.js') }}"></script>

<script type="text/javascript">
	function getmodalwarehouse(){
		$('#modal-pilih-gudang').modal('show'); 
	}
</script>

@yield('custom-script')
