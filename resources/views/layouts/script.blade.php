<!-- jQuery -->
<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 5 -->
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- jQuery UI -->
<script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('plugins/jquery-ui/jquery-ui-timepicker-addon.js') }}"></script>
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
<!-- HTML5 QR Code -->
<script src="{{ asset('plugins/inputmask/inputmask.min.js') }}"></script>
<script src="{{ asset('plugins/inputmask/jquery.inputmask.min.js') }}"></script>
<!-- Livewire Scripts -->
@livewireScripts

<script type="text/javascript">
	function getmodalwarehouse(){
		$('#modal-pilih-gudang').modal('show');
	}

    function wsColorSizeChange(wsId, styleId, colorId, sizeId, tableReload) {
        // Sync WS ↔ Style (same value = act_costing.id)
        $("#" + wsId).on("change", function () {
            if ($("#" + styleId).val() !== $(this).val()) {
                $("#" + styleId).val($(this).val()).trigger("change");
            }

            tableReload();
        });

        $("#" + styleId).on("change", function () {
            if ($("#" + wsId).val() !== $(this).val()) {
                $("#" + wsId).val($(this).val()).trigger("change");
            }
            updateColorList();

            tableReload();
        });

        $("#" + colorId).on("change", function () {
            updateSizeList();

            tableReload();
        });

        $("#" + sizeId).on("change", function () {
            tableReload();
        });

        function updateColorList() {
            $('#' + colorId).html('<option value=""></option>').trigger('change');
            $('#' + sizeId).html('').trigger('change');

            let actCostingId = $('#' + wsId).val();
            if (!actCostingId) return;

            $.ajax({
                url: '{{ route("get-colors") }}',
                type: 'get',
                data: { act_costing_id: actCostingId },
                success: function (res) {
                    let select = document.getElementById(colorId);
                    select.innerHTML = '<option value="">ALL</option>';
                    (res || []).forEach(function (r) {
                        let o = document.createElement('option');
                        o.value = r.color;
                        o.text  = r.color;
                        select.appendChild(o);
                    });
                    $('#' + colorId).trigger('change.select2');
                    if (res && res.length > 0) {
                        $('#' + colorId).val(res[0].color).trigger('change');
                    }
                },
            });
        }

        function updateSizeList() {
            $('#' + sizeId).html('').trigger('change');

            let actCostingId = $('#' + wsId).val();
            let color        = $('#' + colorId).val();
            if (!actCostingId || !color) return;

            $.ajax({
                url: '{{ route("get-sizes") }}',
                type: 'get',
                data: { act_costing_id: actCostingId, color: color },
                success: function (res) {
                    let select = document.getElementById(sizeId);
                    select.innerHTML = '';
                    (res || []).forEach(function (r) {
                        let o = document.createElement('option');
                        o.value = r.so_det_id;
                        o.text  = r.size;
                        select.appendChild(o);
                    });
                    // Select all sizes by default
                    let allIds = (res || []).map(r => r.so_det_id);
                    $('#' + sizeId).val(allIds).trigger('change.select2');
                },
            });
        }
    }
</script>

@yield('custom-script')
