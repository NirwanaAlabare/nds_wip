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

function unique(value, index, array) {
    return array.indexOf(value) === index;
}

function arrayUnique(array) {
    return array.filter(unique)
}

function disableFormSubmit(formId) {
    // Target the form and its submit button
    const form = document.querySelector(formId);

    // Add an event listener for the 'keypress' event
    form.addEventListener('keypress', function(event) {
        // Check if the pressed key is 'Enter' (key code 13)
        if (event.key === 'Enter') {
            // Prevent the form from submitting
            event.preventDefault();
        }
    });
}

function isImage(i) {
    return i instanceof HTMLImageElement;
}

function isElement(obj) {
    try {
      //Using W3 DOM2 (works for FF, Opera and Chrome)
      return obj instanceof HTMLElement;
    }
    catch(e){
      //Browsers not supporting W3 DOM2 don't have HTMLElement and
      //an exception is thrown and we end up here. Testing some
      //properties that all elements have (works on IE7)
      return (typeof obj==="object") &&
        (obj.nodeType===1) && (typeof obj.style === "object") &&
        (typeof obj.ownerDocument ==="object");
    }
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

// Format Decimal Number
function formatDecimalNumber(number) {
    if (number) {
        if (Math.round(number) !== number) {
            return formatNumber(number.toFixed(1));
        }
    }

    return formatNumber(number);
}

// Format Number
function formatNumber(val) {
    // remove sign if negative
    var sign = 1;
    if (val < 0) {
        sign = -1;
        val = -val;
    }

    if (val) {
        // trim the number decimal point if it exists
        let num = val.toString().includes('.') ? val.toString().split('.')[0] : val.toString();
        let len = num.toString().length;
        let result = '';
        let count = 1;

        for (let i = len - 1; i >= 0; i--) {
            result = num.toString()[i] + result;
            if (count % 3 === 0 && count !== 0 && i !== 0) {
            result = '.' + result;
            }
            count++;
        }

        // add number after decimal point
        if (val.toString().includes('.')) {
            result = result + ',' + val.toString().split('.')[1];
        }

        // return result with - sign if negative
        return sign < 0 ? '-' + result : result;
    }

    return 0;
}

// Format date to YYYY-MM-DD
function formatDate(date) {
    var dateObj = new Date(date);

    return [
        dateObj.getFullYear(),
        pad(dateObj.getMonth() + 1),
        pad(dateObj.getDate()),
    ].join('-');
}

function formatDateLocal(date) {
    let months = [{'angka' : 1, 'nama' : 'Januari'}, {'angka' : 2, 'nama' : 'Februari'}, {'angka' : 3, 'nama' : 'Maret'}, {'angka' : 4, 'nama' : 'April'}, {'angka' : 5, 'nama' : 'Mei'}, {'angka' : 6, 'nama' : 'Juni'}, {'angka' : 7, 'nama' : 'Juli'}, {'angka' : 8, 'nama' : 'Agustus'}, {'angka' : 9, 'nama' : 'September'}, {'angka' : 10, 'nama' : 'Oktober'}, {'angka' : 11, 'nama' : 'November'}, {'angka' : 12, 'nama' : 'Desember'}];

    var dateObj = new Date(date);

    return [
        pad(dateObj.getDate()),
        months[dateObj.getMonth()]['nama'],
        dateObj.getFullYear(),
    ].join(' ');
}

function formatDateTime(date) {
    var dateObj = new Date(date);

    var date = "0" + dateObj.getDate();
    var month = "0" + (dateObj.getMonth()+1);
    var year = dateObj.getFullYear();

    var dateMonthYear = year+"-"+month.substr(-2)+"-"+date.substr(-2);

    // Hours part from the timestamp
    var hours = "0" + dateObj.getHours();

    // Minutes part from the timestamp
    var minutes = "0" + dateObj.getMinutes();

    // Seconds part from the timestamp
    var seconds = "0" + dateObj.getSeconds();

    // Will display time in 10:30:23 format
    var time = hours.substr(-2) + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);

    return dateMonthYear + " " + time;
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
            } else if (res.status == 300) {
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
            } else if (res.status == 202) {
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
            } else if (res.status == 203) {
                // $('.modal').modal('hide');

                let successMessage = "";
                res.additional.success.forEach((item) => {
                    console.log("item", item);
                    successMessage += '<span class="text-success fw-bold">'+(item)+' Berhasil</span><br>';
                });

                let failMessage = "";
                res.additional.fail.forEach((item) => {
                    failMessage += '<span class="text-danger fw-bold">'+(item)+' Tidak Valid</span><br>';
                });

                Swal.fire({
                    icon: 'success',
                    title: res.message,
                    html: failMessage + successMessage,
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
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
                    title: res.message,
                    position: 'topCenter'
                });

                Swal.fire({
                    icon: 'error',
                    title: "Gagal",
                    html: res.message,
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
async function editData(e, modal, addons = []) {
    let data = e;

    for (let key in data) {
        if (document.getElementById('edit_' + key)) {
            console.log("img", isImage(document.getElementById('edit_' + key)));
            if (isImage(document.getElementById('edit_' + key))) {
                document.getElementById('edit_' + key).src = data[key];
            }

            document.getElementById('edit_' + key).value = data[key];
            document.getElementById('edit_' + key).setAttribute('value', data[key]);

            if ([...document.getElementById('edit_' + key).classList].some(className => className.includes('select2'))) {
                $('#edit_' + key).val(data[key]).trigger('change.select2');
            }
        }
    }

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

    $('#' + modal).modal('show');
}

// Delete data confirmation
function deleteData(e) {
    console.log(e, e.getAttribute('data'));

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

                            // $('.modal').modal('hide');
                        } else {
                            iziToast.error({
                                title: 'Error',
                                message: res.message,
                                position: 'topCenter'
                            });
                        }

                        if (res.table) {
                            $('#' + res.table).DataTable().ajax.reload();
                        }

                        if (res.callback != '') {
                            eval(res.callback);
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

// popup notification
function showNotification(type, message) {
    switch (type) {
        case 'info' :
            iziToast.info({
                title: 'Information',
                message: message,
                position: 'topCenter'
            });
            break;
        case 'success' :
            iziToast.success({
                title: 'Success',
                message: message,
                position: 'topCenter'
            });
            break;
        case 'warning' :
            iziToast.warning({
                title: 'Warning',
                message: message,
                position: 'topCenter'
            });
            break;
        case 'error' :
            iziToast.error({
                title: 'Error',
                message: message,
                position: 'topCenter'
            });
            break;
    }
}

//Returns true if it is a Node
function isNode(o){
    return (
      typeof Node === "object" ? o instanceof Node :
      o && typeof o === "object" && typeof o.nodeType === "number" && typeof o.nodeName==="string"
    );
}

//Returns true if it is a DOM element
function isElement(o){
    return (
        typeof HTMLElement === "object" ? o instanceof HTMLElement : //DOM2
        o && typeof o === "object" && o !== null && o.nodeType === 1 && typeof o.nodeName==="string"
    );
}

function objectGroupBy(array, keyFn) {
    return array.reduce((acc, item) => {
      const key = keyFn(item);
      (acc[key] ||= []).push(item);
      return acc;
    }, {});
}

function objectValues(obj) {
  var values = [];
  for (var key in obj) {
    if (Object.prototype.hasOwnProperty.call(obj, key)) {
      values.push(obj[key]);
    }
  }
  return values;
}
