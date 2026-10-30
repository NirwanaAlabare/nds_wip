<script>
    document.addEventListener("DOMContentLoaded", function() {
        function adjustStickyHeader() {
            // Cari elemen navbar utama
            let navbar = document.querySelector('.main-header') || document.querySelector('nav');
            let navbarHeight = navbar ? navbar.offsetHeight : 0;

            console.log('Navbar Height:', navbarHeight);

            // Applied top offset
            let stickyHeader = document.getElementById('stickyFormHeader');
            if (stickyHeader) {
                stickyHeader.style.top = navbarHeight + 'px';
            }
        }

        // Jalankan saat document ready
        adjustStickyHeader();

        // Jalankan saat window resize
        window.addEventListener('resize', adjustStickyHeader);
    });

    function handleUndo() {
        Swal.fire({
            title: 'Konfirmasi Undo',
            text: "Apakah Anda yakin ingin membatalkan atau menghapus pengerjaan/input terakhir?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Undo!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            let kodeBarang = $("#kode_barang").val();
            let selectItem = $("#select_item").val();

            console.log("scanned item", currentScannedItem);

            if (currentScannedItem) {
                location.reload(); // Refresh halaman jika ada inputan yang belum dikosongkan
                return; // Hentikan eksekusi jika ada inputan yang belum dikosongkan
            }

            if (result.isConfirmed) {
                showLoading();

                $.ajax({
                    url: '{{ route('form-cut-undo') }}',
                    method: 'POST',
                    data: {
                        id: $("#id").val()
                    },
                    success: function (res) {
                        hideLoading();

                        if (res && res.status == 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                html: res.message
                            }).then(() => {
                                // Refresh halaman atau lakukan tindakan lain sesuai kebutuhan
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                html: res.message
                            });
                        }
                    },
                    error: function (jqXHR) {
                        hideLoading();
                        
                        console.error(jqXHR);
                        let resJson = jqXHR.responseJSON;

                        let message = resJson && resJson.message ? resJson.message : 'Terjadi kesalahan saat membatalkan pengerjaan terakhir';
                        iziToast.error({
                            title: 'Gagal',
                            message: message,
                            position: 'topCenter'
                        });
                    }
                });
            }
        });
    }

    // --- FUNGSI REDO ---
    function handleRedo() {
        Swal.fire({
            title: 'Konfirmasi Redo',
            text: "Apakah Anda yakin ingin mengembalikan pengerjaan/input yang sebelumnya di-undo?",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Redo!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading();

                $.ajax({
                    url: '{{ route("form-cut-redo") }}', // Route khusus untuk Redo
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        id: $("#id").val()
                    },
                    success: function (res) {
                        hideLoading();

                        if (res && res.status == 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                html: res.message
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                html: res ? res.message : 'Terjadi kesalahan.'
                            });
                        }
                    },
                    error: function (jqXHR) {
                        hideLoading();

                        console.error(jqXHR);
                        let resJson = jqXHR.responseJSON;
                        let message = resJson && resJson.message ? resJson.message : 'Terjadi kesalahan saat mengembalikan pengerjaan terakhir';

                        iziToast.error({
                            title: 'Gagal',
                            message: message,
                            position: 'topCenter'
                        });
                    }
                });
            }
        });
    }

    async function unlockEnter(evt, isStoring = 0, locktype = 'shortroll') {
        if (evt.keyCode == 13) {
            unlockForm(isStoring, locktype);
        }
    }

    async function lockForm(locktype = "shortroll") {
        document.getElementById("loading").classList.remove('d-none');

        $.ajax({
            url: '{{ route('form-cut-lock') }}',
            method: 'POST',
            data: {
                id: $("#id").val(),
                locktype: locktype
            },
            success: function (res) {
                document.getElementById("loading").classList.add('d-none');

                if (res) {
                    iziToast.warning({
                        title: 'Form di Kunci',
                        message: 'Harap hubungi atasan untuk melanjutkan',
                        position: 'topCenter'
                    });
                }
            },
            error: function (jqXHR) {
                document.getElementById("loading").classList.add('d-none');

                console.error(jqXHR);
            }
        });
    }

    async function unlockForm(isStoring = 0, locktype = "shortroll") {
        document.getElementById("loading").classList.remove('d-none');

        if ($("#unlock_form_username").val() && $("#unlock_form_password").val()) {
            $.ajax({
                url: '{{ route('form-cut-unlock') }}',
                method: 'POST',
                data: {
                    id: $("#id").val(),
                    username: $("#unlock_form_username").val(),
                    password: $("#unlock_form_password").val(),
                    locktype: locktype
                },
                success: function (res) {
                    document.getElementById("loading").classList.add('d-none');

                    if (res) {
                        if (res.locked < 1 && res.cons_locked < 1) {
                            Swal.close();

                            iziToast.success({
                                title: 'Berhasil',
                                message: 'Form berhasil dibuka',
                                position: 'topCenter'
                            });

                            $("#locked").val(res.locked);
                            $("#unlocked_by").val(res.unlocked_by);
                            $("#cons_locked").val(res.cons_locked);
                            $("#cons_unlocked_by").val(res.cons_unlocked_by);

                            if (isStoring > 0) {
                                storeTimeRecord(1);
                            }
                        } else {
                            iziToast.error({
                                title: 'Form gagal dibuka',
                                message: 'Password salah',
                                position: 'topCenter'
                            });
                        }
                    }
                },
                error: function (jqXHR) {
                    document.getElementById("loading").classList.add('d-none');

                    let message = jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : 'Terjadi kesalahan saat membuka form';

                    iziToast.error({
                        title: 'Form gagal dibuka',
                        message: message,
                        position: 'topCenter'
                    });

                    console.error(jqXHR);
                }
            });
        } else {
            document.getElementById("loading").classList.add('d-none');

            iziToast.error({
                title: 'Form gagal dibuka',
                message: 'Harap isi kolom password',
                position: 'topCenter'
            });
        }
    }
</script>
