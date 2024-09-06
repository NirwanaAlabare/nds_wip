// CSRF token for ajax
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

document.addEventListener('DOMContentLoaded', () => {
    // Bootstrap modal configuration
    $.fn.modal.Constructor.prototype.enforceFocus = function () { };

    // Enable bootstrap tooltip
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

});

function isImage(i) {
    return i instanceof HTMLImageElement;
}

// Capitalize
function capitalizeFirstLetter(string) {
    if (string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    return "-";
}

// Round
Number.prototype.round = function (places) {
    return +(Math.round(this + "e+" + places) + "e-" + places);
}

// Pad 2 Digits
function pad(n) {
    return n < 10 ? '0' + n : n
}

// Check if value is null
function isNotNull(value) {
    if (typeof value != "number") {
        if (value == "" || value == null) {
            return false
        }
    }

    return true;
}

// Format date to YYYY-MM-DD
function formatDate(date) {
    return [
        date.getFullYear(),
        pad(date.getMonth() + 1),
        pad(date.getDate()),
    ].join('-');
}

// Clear modified
var modified = [];
function clearModified() {
    if (modified.length > 0) {
        modified.forEach(element => {
            let strFunction = '';
            element.forEach((ele, idx) => {
                if (idx == 0) {
                    strFunction += 'document.getElementById("' + ele + '")';
                } else {
                    strFunction += ele
                }
            });
            eval(strFunction);
        });
    }
}

// Form Submit
function submitForm(e, evt) {
    if (document.getElementById("loading")) {
        document.getElementById("loading").classList.remove("d-none");
    }

    $("input[type=submit][clicked=true]").attr('disabled', true);

    evt.preventDefault();

    clearModified();

    $.ajax({
        url: e.getAttribute('action'),
        type: e.getAttribute('method'),
        data: new FormData(e),
        processData: false,
        contentType: false,
        success: function (res) {
            if (document.getElementById("loading")) {
                document.getElementById("loading").classList.add("d-none");
            }

            $("input[type=submit][clicked=true]").removeAttr('disabled');
            if (res.status == 200 || res.status == 999) {
                $('.modal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: res.message,
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                    timer: (res.status == 200 ? 5000 : 3000),
                    timerProgressBar: true
                }).then(() => {
                    if (isNotNull(res.redirect)) {
                        if (res.redirect != 'reload') {
                            location.href = res.redirect;
                        } else {
                            location.reload();
                        }
                    } else {
                        location.reload();
                    }
                });

                e.reset();
                if (document.getElementsByClassName('select2')) {
                    $(".select2").val('').trigger('change');
                    $(".select2bs4").val('').trigger('change');
                }

                if (res.callback != '') {
                    eval(res.callback);
                }
            }
            // else if (res.status == 201) {
            //     $('.modal').modal('hide');

            //     Swal.fire({
            //         icon: 'warning',
            //         title: res.message,
            //         showCancelButton: false,
            //         showConfirmButton: true,
            //         confirmButtonText: 'Oke',
            //         timer: (res.status == 201 ? 5000 : 3000),
            //         timerProgressBar: true
            //     }).then(() => {
            //         if (isNotNull(res.redirect)) {
            //             if (res.redirect != 'reload') {
            //                 location.href = res.redirect;
            //             } else {
            //                 location.reload();
            //             }
            //         } else {
            //             location.reload();
            //         }
            //     });

            //     e.reset();
            //     if (document.getElementsByClassName('select2')) {
            //         $(".select2").val('').trigger('change');
            //         $(".select2bs4").val('').trigger('change');
            //     }

            //     if (res.callback != '') {
            //         eval(res.callback);
            //     }
            // }
            else if (res.status == 300) {
                $('.modal').modal('hide');

                iziToast.success({
                    title: 'success',
                    message: res.message,
                    position: 'topCenter'
                });

                e.reset();
                if (document.getElementsByClassName('select2')) {
                    $(".select2").val('').trigger('change');
                    $(".select2bs4").val('').trigger('change');
                }
            } else if (res.status == 900) {
                Swal.fire({
                    icon: 'success',
                    title: res.message,
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke'
                }).then(() => {
                    if (isNotNull(res.redirect)) {
                        if (res.redirect != 'reload') {
                            location.href = res.redirect;
                        } else {
                            location.reload();
                        }
                    } else {
                        location.reload();
                    }
                });

                e.reset();
                if (document.getElementsByClassName('select2')) {
                    $(".select2").val('').trigger('change');
                    $(".select2bs4").val('').trigger('change');
                }

                if (res.callback != '') {
                    eval(res.callback);
                }
            } else if (res.status == 201) {
                // $('.modal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: res.message,
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    if (isNotNull(res.redirect)) {
                        if (res.redirect != 'reload') {
                            location.href = res.redirect;
                        } else {
                            location.reload();
                        }
                    } else {
                        // location.reload();
                    }
                });

                // e.reset();
                // if (document.getElementsByClassName('select2')) {
                //     $(".select2").val('').trigger('change');
                //     $(".select2bs4").val('').trigger('change');
                // }

                if (res.callback != '') {
                    eval(res.callback);
                }
            } else {
                for (let i = 0; i < res.errors; i++) {
                    document.getElementById(res.errors[i]).classList.add('is-invalid');
                    modified.push([res.errors[i], 'classList', 'remove(', "'is-invalid')"])
                }

                iziToast.error({
                    title: 'Error',
                    message: res.message,
                    position: 'topCenter'
                });
            }

            if (res.table != '') {
                $('#' + res.table).DataTable().ajax.reload();
            }

            if (Object.keys(res.additional).length > 0) {
                for (let key in res.additional) {
                    if (document.getElementById(key)) {
                        document.getElementById(key).classList.add('is-invalid');

                        if (res.additional[key].hasOwnProperty('message')) {
                            document.getElementById(key + '_error').classList.remove('d-none');
                            document.getElementById(key + '_error').innerHTML = res.additional[key]['message'];
                        }

                        if (res.additional[key].hasOwnProperty('value')) {
                            document.getElementById(key).value = res.additional[key]['value'];
                        }

                        modified.push(
                            [key, '.classList', '.remove(', "'is-invalid')"],
                            [key + '_error', '.classList', '.add(', "'d-none')"],
                            [key + '_error', '.innerHTML = ', "''"],
                        )
                    }
                }
            }
        }, error: function (jqXHR) {
            if (document.getElementById("loading")) {
                document.getElementById("loading").classList.add("d-none");
            }

            $("input[type=submit][clicked=true]").removeAttr('disabled');

            let res = jqXHR.responseJSON;
            let message = '';

            for (let key in res.errors) {
                message = res.errors[key];
                document.getElementById(key).classList.add('is-invalid');
                document.getElementById(key + '_error').classList.remove('d-none');
                document.getElementById(key + '_error').innerHTML = res.errors[key];

                modified.push(
                    [key, '.classList', '.remove(', "'is-invalid')"],
                    [key + '_error', '.classList', '.add(', "'d-none')"],
                    [key + '_error', '.innerHTML = ', "''"],
                )
            };

            iziToast.error({
                title: 'Error',
                message: 'Terjadi kesalahan.',
                position: 'topCenter'
            });
        }
    });
}

// Edit data modal
function editData(e, modal, addons = []) {
    let data = e;

    for (let key in data) {
        if (document.getElementById('edit_' + key)) {
            console.log("img", isImage(document.getElementById('edit_' + key)));
            if (isImage(document.getElementById('edit_' + key))) {
                document.getElementById('edit_' + key).src = data[key];
            }

            document.getElementById('edit_' + key).value = data[key];
            document.getElementById('edit_' + key).setAttribute('value', data[key]);

            if (document.getElementById('edit_' + key).classList.contains('select2') || document.getElementById('edit_' + key).classList.contains('select2bs4') || document.getElementById('edit_' + key).classList.contains('select2bs4stat') || document.getElementById('edit_' + key).classList.contains('select2custom')) {
                $('#edit_' + key).val(data[key]).trigger('change.select2');
            }
        } else {
            if (addons.length > 0) {
                for (let i = 0; i < addons.length; i++) {
                    if (typeof addons == "object") {
                        for (let addonsKey in addons[i]) {
                            if (addonsKey == "function") {
                                eval(addons[i][addonsKey]);
                            }
                        }
                    }
                }
            }
        }
    }

    $('#' + modal).modal('show');
}

// Delete data confirmation
function deleteData(e) {
    let data = JSON.parse(e.getAttribute('data'));

    if (data.hasOwnProperty('id')) {
        Swal.fire({
            icon: 'error',
            title: 'Hapus data?',
            showCancelButton: true,
            showConfirmButton: true,
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#fa4456',
        }).then((result) => {
            if (result.isConfirmed) {
                if (document.getElementById("loading")) {
                    document.getElementById("loading").classList.remove("d-none");
                }

                $.ajax({
                    url: e.getAttribute('data-url'),
                    type: 'POST',
                    data: {
                        _method: 'DELETE'
                    },
                    success: function (res) {
                        if (document.getElementById("loading")) {
                            document.getElementById("loading").classList.add("d-none");
                        }

                        if (res.status == 200) {
                            iziToast.success({
                                title: 'Success',
                                message: res.message,
                                position: 'topCenter'
                            });

                            $('.modal').modal('hide');
                        } else {
                            iziToast.error({
                                title: 'Error',
                                message: res.message,
                                position: 'topCenter'
                            });
                        }

                        if (res.table != '') {
                            $('#' + res.table).DataTable().ajax.reload();
                        } else {
                            location.reload();
                        }
                    }, error: function (jqXHR) {
                        if (document.getElementById("loading")) {
                            document.getElementById("loading").classList.add("d-none");
                        }

                        let res = jqXHR.responseJSON;
                        let message = '';

                        for (let key in res.errors) {
                            message = res.errors[key];
                        }

                        iziToast.error({
                            title: 'Error',
                            message: 'Terjadi kesalahan. ' + message,
                            position: 'topCenter'
                        });
                    }
                })
            }
        })
    }
}

function generateToken(id, route) {
    $.ajax({
        url: route,
        method: 'post',
        data: {
            id: id
        },
        success: function (res) {
            if (res) {
                document.getElementById("unlock_token").value = res;
            }
        }
    });
}
