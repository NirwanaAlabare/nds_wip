document.addEventListener('DOMContentLoaded', () => {
    // CSRF token for ajax
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Bootstrap modal configuration
    $.fn.modal.Constructor.prototype.enforceFocus = function() {};

    // Enable bootstrap tooltip
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

    if (document.getElementById('parent-produksi')) {
        let parentProduksi = document.getElementById('parent-produksi');
        if(parentProduksi.classList.contains('dropdown')) {
            $('#produksi-dropdown').collapse('show');
        }
    };

    if (document.getElementById('parent-master')) {
        let parentMaster = document.getElementById('parent-master');
        if(parentMaster.classList.contains('dropdown')) {
            $('#master-dropdown').collapse('show');
        }
    };

    if (document.getElementById('parent-report')) {
        let parentReport = document.getElementById('parent-report');
        if(parentReport.classList.contains('dropdown')) {
            $('#report-dropdown').collapse('show');
        }
    };
});

// Clear modified
var modified = [];
function clearModified() {
    if (modified.length > 0) {
        modified.forEach(element => {
            let strFunction = '';
            element.forEach((ele,idx) => {
                if (idx == 0) {
                    strFunction += 'document.getElementById("'+ele+'")';
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
    evt.preventDefault();

    clearModified();

    $.ajax({
        url: e.getAttribute('action'),
        type: e.getAttribute('method'),
        data: new FormData(e),
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.status == 200) {
                console.log(res.message);

                if (res.redirect != '') {
                    location.href = res.redirect;
                }

                iziToast.success({
                    title: 'Success',
                    message: res.message,
                    position: 'topCenter'
                });

                e.reset();

                if (document.getElementsByClassName('select2')) {
                    $(".select2").val('').trigger('change');
                }

                $('.modal').modal('hide');
            } else {
                console.log(res.message);

                for(let i = 0;i < res.errors; i++) {
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
                $('#'+res.table).DataTable().ajax.reload();
            }

            if (Object.keys(res.additional).length > 0 ) {
                for (let key in res.additional) {
                    if (document.getElementById(key)) {
                        document.getElementById(key).classList.add('is-invalid');

                        if (res.additional[key].hasOwnProperty('message')) {
                            document.getElementById(key+'_error').classList.remove('d-none');
                            document.getElementById(key+'_error').innerHTML = res.additional[key]['message'];
                        }

                        if (res.additional[key].hasOwnProperty('value')) {
                            document.getElementById(key).value = res.additional[key]['value'];
                        }

                        modified.push(
                            [key, '.classList', '.remove(', "'is-invalid')"],
                            [key+'_error', '.classList', '.add(', "'d-none')"],
                            [key+'_error', '.innerHTML = ', "''"],
                        )
                    }
                }
            }
        }, error: function (jqXHR) {
            let res = jqXHR.responseJSON;
            let message = '';

            for (let key in res.errors) {
                message = res.errors[key];
                document.getElementById(key).classList.add('is-invalid');
                document.getElementById(key+'_error').classList.remove('d-none');
                document.getElementById(key+'_error').innerHTML = res.errors[key];

                modified.push(
                    [key, '.classList', '.remove(', "'is-invalid')"],
                    [key+'_error', '.classList', '.add(', "'d-none')"],
                    [key+'_error', '.innerHTML = ', "''"],
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
    let data = JSON.parse(e.getAttribute('data'));

    for (let key in data) {
        console.log(data);
        if (document.getElementById('edit_'+key)) {
            document.getElementById('edit_'+key).value = data[key];
            document.getElementById('edit_'+key).setAttribute('value', data[key]);

            if (document.getElementById('edit_'+key).classList.contains('select2')) {
                $('#edit_'+key).val(data[key]).trigger('change.select2');
            }
        } else {
            if (addons.length > 0) {
                for (let i=0; i < addons.length; i++) {
                    for (let addonsKey in data[addons[i]]) {
                        if (!data.hasOwnProperty(addonsKey)) {
                            if (document.getElementById('edit_'+addonsKey)) {
                                document.getElementById('edit_'+addonsKey).value = data[addons[i]][addonsKey];
                                document.getElementById('edit_'+addonsKey).setAttribute('value', data[addons[i]][addonsKey]);

                                console.log(document.getElementById('edit_'+addonsKey));

                                if (document.getElementById('edit_'+addonsKey).classList.contains('select2')) {
                                    $('#edit_'+addonsKey).val(data[addons[i]][addonsKey]).trigger('change.select2');
                                }
                            } else {
                                if (typeof data[addons[i]][addonsKey] === 'object' && data[addons[i]][addonsKey] !== null) {
                                    for (let subAddonsKey in data[addons[i]][addonsKey]) {
                                        if (!data.hasOwnProperty(subAddonsKey) && !data[addons[i]].hasOwnProperty(subAddonsKey)) {
                                            if (document.getElementById('edit_'+subAddonsKey)) {
                                                document.getElementById('edit_'+subAddonsKey).value = data[addons[i]][addonsKey][subAddonsKey];
                                                document.getElementById('edit_'+subAddonsKey).setAttribute('value', data[addons[i]][addonsKey][subAddonsKey]);

                                                if (document.getElementById('edit_'+subAddonsKey).classList.contains('select2')) {
                                                    $('#edit_'+subAddonsKey).val(data[addons[i]][addonsKey][subAddonsKey]).trigger('change.select2');
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    $('#'+modal).modal('show');
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
                $.ajax({
                    url: e.getAttribute('data-url'),
                    type: 'POST',
                    data: {
                        _method: 'DELETE'
                    },
                    success: function(res) {
                        if (res.status == 200) {
                            iziToast.success({
                                title: 'Success',
                                message: res.message,
                                position: 'topCenter'
                            });

                            $('.modal').modal('hide');
                        } else {
                            iziToast.success({
                                title: 'Error',
                                message: res.message,
                                position: 'topCenter'
                            });
                        }

                        if (res.table != '') {
                            $('#'+res.table).DataTable().ajax.reload();
                        }
                    }, error: function (jqXHR) {
                        let res = jqXHR.responseJSON;
                        let message = '';

                        for (let key in res.errors) {
                            message = res.errors[key];
                        }

                        iziToast.error({
                            title: 'Error',
                            message: 'Terjadi kesalahan. '+message,
                            position: 'topCenter'
                        });
                    }
                })
            }
        })
    }
}
