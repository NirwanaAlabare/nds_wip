<script>
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
