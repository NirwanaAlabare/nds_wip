<script>

    document.addEventListener("DOMContentLoaded", () => {
        let oneWeekBefore = getOneWeekBefore();
        let today = getCurrentDate();

        $("#tgl-awal").val(oneWeekBefore).trigger("change");
        $("#tgl-akhir").val(today).trigger("change");

        window.addEventListener("focus", () => {
            $('#datatable').DataTable().ajax.reload(null, false);
        });
    });

    $('#datatable thead tr').clone(true).appendTo('#datatable thead');
    $('#datatable thead tr:eq(1) th').each(function(i) {
        if (i != 0) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm" />');

            $('input', this).on('keyup change', function() {
                if (datatable.column(i).search() !== this.value) {
                    datatable
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        } else {
            $(this).empty();
        }
    });

    let datatable = $("#datatable").DataTable({
        ordering: false,
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('marker') }}',
            data: function(d) {
                d.tgl_awal = $('#tgl-awal').val();
                d.tgl_akhir = $('#tgl-akhir').val();
            },
        },
        columns: [
            {
                data: 'id'
            },
            {
                data: 'tgl_cut_fix',
            },
            {
                data: 'kode'
            },
            {
                data: 'act_costing_ws'
            },
            {
                data: 'style'
            },
            {
                data: 'color'
            },
            {
                data: 'panel'
            },
            {
                data: 'urutan_marker'
            },
            {
                data: 'panjang_marker_fix',
                searchable: false
            },
            {
                data: 'lebar_marker',
                searchable: false
            },
            {
                data: 'lebar_ws',
                searchable: false
            },
            {
                data: 'gramasi',
                searchable: false
            },
            {
                data: undefined
            },
            {
                data: 'total_form',
                searchable: false
            },
            {
                data: 'po_marker',
            },
            {
                data: 'notes',
            },
        ],
        columnDefs: [
            {
                targets: [0],
                render: (data, type, row, meta) => {
                    let exportBtn = `
                        <button type="button" class="btn btn-sm btn-success" onclick="printMarker('` + row.kode + `');">
                            <i class="fa fa-print"></i>
                        </button>
                    `;

                    // Case when there is form
                    if (row.cancel != 'Y' && row.total_form > 0 /* && row.tipe_marker != "pilot marker" */) {
                        return `
                            <div class='d-flex gap-1 justify-content-start mb-1'>
                                <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#showMarkerModal" onclick='getdetail(` + row.id + `);'>
                                    <i class='fa fa-search'></i>
                                </a>
                                <button class='btn btn-info btn-sm' data-bs-toggle="modal" data-bs-target="#editMarkerModal" onclick='edit(` + row.id + `);'>
                                    <i class='fa fa-edit'></i>
                                </button>
                                ` + exportBtn + `
                                <button class='btn btn-danger btn-sm' onclick='cancel(` + row.id + `);' disabled>
                                    <i class='fa fa-ban'></i>
                                </button>
                            </div>
                        `;
                    }
                    // When there ain't no form
                    else if ((row.cancel != 'Y' && row.total_form < 1) /*|| (row.cancel != 'Y' && row.gelar_qty_balance > 0  && row.tipe_marker == "pilot marker" )*/) {
                        return `
                            <div class='d-flex gap-1 justify-content-start mb-1'>
                                <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#showMarkerModal" onclick='getdetail(` + row.id + `);'>
                                    <i class='fa fa-search'></i>
                                </a>
                                <button class='btn btn-info btn-sm' data-bs-toggle="modal" data-bs-target="#editMarkerModal" onclick='edit(` + row.id + `);'>
                                    <i class='fa fa-edit'></i>
                                </button>
                                ` + exportBtn + `
                                <button class='btn btn-danger btn-sm' onclick='cancel(` + row.id + `);'>
                                    <i class='fa fa-ban'></i>
                                </button>
                            </div>
                        `;
                    }
                    // When the form is cancelled
                    else if (row.cancel == 'Y') {
                        return `
                            <div class='d-flex gap-1 justify-content-start'>
                                <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#showMarkerModal" onclick='getdetail(` + row.id + `);'>
                                    <i class='fa fa-search'></i>
                                </a>
                                <button class='btn btn-info btn-sm' data-bs-toggle="modal" data-bs-target="#editMarkerModal" onclick='edit(` + row.id + `);' disabled>
                                    <i class='fa fa-edit'></i>
                                </button>
                                ` + exportBtn + `
                                <button class='btn btn-danger btn-sm' onclick='cancel(` + row.id + `);' disabled>
                                    <i class='fa fa-ban'></i>
                                </button>
                            </div>
                        `;
                    }
                    // Default
                    else {
                        return `
                            <div class='d-flex gap-1 justify-content-start'>
                                <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#showMarkerModal" onclick='getdetail(` + row.id + `);'>
                                    <i class='fa fa-search'></i>
                                </a>
                                <button class='btn btn-info btn-sm' data-bs-toggle="modal" data-bs-target="#editMarkerModal" onclick='edit(` + row.id + `);'>
                                    <i class='fa fa-edit'></i>
                                </button>
                                ` + exportBtn + `
                                <button class='btn btn-danger btn-sm' onclick='cancel(` + row.id + `);' disabled>
                                    <i class='fa fa-ban'></i>
                                </button>
                            </div>
                        `;
                    }
                }
            },
            {
                targets: [12],
                render: (data, type, row, meta) => {
                    // Marker Progress Bar
                    return `
                        <div class="progress border border-sb position-relative" style="height: 21px; width: 100px;">
                            <p class="position-absolute" style="top: 50%;left: 50%;transform: translate(-50%, -50%);">` + row.total_lembar + `/` + row.gelar_qty + `</p>
                            <div class="progress-bar" style="background-color: #75baeb;width: ` + ((row.total_lembar / row.gelar_qty) * 100) + `%" role="progressbar"></div>
                        </div>
                    `;
                }
            },
            {
                targets: '_all',
                className: 'text-nowrap',
                render: (data, type, row, meta) => {
                    var color = '#2b2f3a';
                    if (row.total_form != '0' && row.cancel == 'N') {
                        color = '#087521';
                    } else if (row.total_form == '0' && row.cancel == 'N') {
                        color = '#2243d6';
                    } else if (row.cancel == 'Y') {
                        color = '#d33141';
                    }
                    return '<span style="font-weight: 600; color:' + color + '">' + data + '</span>';
                }
            },
        ],
        rowCallback: function( row, data, index ) {
            // Case when it's a special marker
            if (data['tipe_marker'] == 'special marker') {
                $('td', row).css('background-color', '#e7dcf7');
                $('td', row).css('border', '0.15px solid #d0d0d0');
            }
            // When it's a pilot
            else if (data['tipe_marker'] == 'pilot marker' || data['tipe_marker'] == 'bulk marker') {
                $('td', row).css('background-color', '#c5e0fa');
                $('td', row).css('border', '0.15px solid #d0d0d0');
            }
        }
    });

    function filterTable() {
        datatable.ajax.reload();
    }

    // Get Marker Detail
    function getdetail(id_c) {

        // Modal content html
        $("#showMarkerModalLabel").html('<i class="fa-solid fa-magnifying-glass fa-sm"></i> Marker Detail');
        let html = $.ajax({
            type: "POST",
            url: '{{ route('show-marker') }}',
            data: {
                id_c: id_c
            },
            async: false
        }).responseText;
        $("#detail").html(html);

        // Set content table to datatable
        $("#detail-marker-ratio").DataTable({
            ordering: false
        });
        $("#detail-marker-form").DataTable({
            ordering: false
        });
    };

    // Get Marker Type and Gramasi
    function edit(id_c) {
        document.getElementById("loading").classList.remove('d-none');

        $("#editMarkerModalLabel").html('<i class="fa fa-edit fa-sm"></i> Marker Edit');

        // Get Gramasi with Marker Type
        $.ajax({
            url: '{{ route('show_gramasi') }}',
            method: 'POST',
            data: {
                id_c: id_c
            },
            dataType: 'json',
            success: function(response) {
                console.log(response);

                document.getElementById('id_c').value = response.id;
                document.getElementById('txtkode_marker_edit').value = response.kode;
                document.getElementById('txt_gramasi').value = response.gramasi;

                if (response.tipe_marker == "pilot marker") {
                    // Show Pilot Section
                    document.getElementById('marker_pilot').classList.remove('d-none');

                    // Pilot to bulk
                    if (response.status_marker) {
                        document.getElementById(response.status_marker).checked = true;
                    } else {
                        document.getElementById("idle").checked = true;
                    }
                } else {
                    document.getElementById('marker_pilot').classList.add('d-none');
                }

                // Edit gramasi on form availibility
                if (response.jumlah_form > 0) {
                    document.getElementById('txt_gramasi').setAttribute('readonly', true);
                } else {
                    document.getElementById('txt_gramasi').removeAttribute('readonly');
                }

                // Go to detail edit
                document.getElementById('advanced-edit-link').setAttribute('target','_blank');
                document.getElementById('advanced-edit-link').setAttribute('href','{{ route('edit-marker') }}/' + response.id);
                document.getElementById('advanced-edit-section').classList.remove('d-none');

                document.getElementById("loading").classList.add('d-none');
            },
            error: function(request, status, error) {
                alert(request.responseText);

                document.getElementById("loading").classList.add('d-none');
            },
        });
    };

    function cancel(id_c) {
        Swal.fire({
            icon: 'error',
            title: 'Hapus Data',
            showConfirmButton: true,
            confirmButtonText: "Hapus",
            confirmButtonColor: "red",
            showCancelButton: true
        }).then((result) => {
            if (result.isConfirmed) {
                let html = $.ajax({
                    type: "POST",
                    url: '{{ route('update_status') }}',
                    data: {
                        id_c: id_c
                    },
                    async: false
                }).responseText;

                swal.fire({
                    position: 'mid-end',
                    icon: 'info',
                    title: 'Data Sudah Di Ubah',
                    showConfirmButton: false,
                    timer: 5000
                });

                datatable.ajax.reload();
            }
        });
    };

    function printMarker(kodeMarker) {
        let fileName = kodeMarker;

        // Show Loading
        Swal.fire({
            title: 'Please Wait...',
            html: 'Exporting Data...',
            didOpen: () => {
                Swal.showLoading()
            },
            allowOutsideClick: false,
        });

        $.ajax({
            url: '{{ route('print-marker') }}/' + kodeMarker.replace(/\//g, '_'),
            type: 'post',
            processData: false,
            contentType: false,
            xhrFields: {
                responseType: 'blob'
            },
            success: function(res) {
                if (res) {
                    var blob = new Blob([res], {
                        type: 'application/pdf'
                    });

                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = fileName + ".pdf";
                    link.click();

                    swal.close();
                }
            }
        });
    }

    function fixMarkerBalanceQty() {
        Swal.fire({
            title: 'Please Wait...',
            html: 'Fixing Data...',
            didOpen: () => {
                Swal.showLoading()
            },
            allowOutsideClick: false,
        });

        $.ajax({
            url: '{{ route('fix-marker-balance-qty') }}',
            method: 'POST',
            dataType: 'json',
            success: async function(res) {
                console.log(res);

                await swal.close();

                Swal.fire({
                    icon: 'success',
                    title: res.message,
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
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
            },
            error: function(jqXHR) {
                console.log(jqXHR);
            },
        });
    }
</script>
