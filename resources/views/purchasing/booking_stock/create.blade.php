@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        .table-custom th {
            background-color: var(--sb-color) !important;
            color: white;
            white-space: nowrap;
            text-align: center;
            vertical-align: middle !important;
            font-size: 13px;
        }
        .table-custom td {
            vertical-align: middle !important;
            font-size: 13px;
        }
    </style>
@endsection

@section('content')
<form id="form-save-booking">
    @csrf
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-edit"></i> Form Booking Stock</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small fw-bold">No Booking</label>
                        <input type="text" name="no_booking" id="no_booking" class="form-control form-control-sm fw-bold" value="{{ $no_booking }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small fw-bold">Tgl Booking</label>
                        <input type="date" name="tgl_booking" id="tgl_booking" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small fw-bold">Jenis</label>
                        <select name="jenis" id="jenis" class="form-control form-control-sm select2bs4">
                            <option value="">-- Pilih Jenis --</option>
                            <option value="Accessories">Accessories</option>
                            <option value="Fabric">Fabric</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row bg-light p-3 border rounded shadow-sm mb-4">
                <div class="col-md-5 mb-3">
                    <label class="small fw-bold">Nama Barang</label>
                    <select id="id_item" name="id_item" class="form-control select2bs4">
                        <option value="">-- Pilih Item --</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="small fw-bold">Qty</label>
                    <input type="text" id="qty" name="qty" class="form-control form-control-sm text-right" placeholder="0" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="small fw-bold">Satuan</label>
                    <select id="satuan" name="satuan" class="form-control select2bs4">
                        <option value="">-- Pilih Satuan --</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->nama_pilihan }}">{{ $unit->nama_pilihan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="small fw-bold">WS</label>
                    <select id="ws" name="ws" class="form-control select2bs4">
                        <option value="">-- Pilih WS --</option>
                        @foreach($ws_list as $ws)
                            <option value="{{ $ws->kpno }}">{{ $ws->kpno }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-8">
                    <label class="small fw-bold">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" class="form-control form-control-sm" rows="2" placeholder="Masukkan keterangan booking..."></textarea>
                </div>
                <div class="col-md-4 d-flex align-items-end justify-content-end">
                    <button type="button" class="btn btn-success btn-sm px-4" onclick="addToTable()"><i class="fas fa-plus"></i> Add</button>
                </div>
            </div>

            <h6 class="mt-4 fw-bold text-primary">List Item</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-custom table-hover w-100" id="table-detail">
                    <thead>
                        <tr>
                            <th width="5%">No.</th>
                            <th width="10%">ID Item</th>
                            <th width="35%">Nama Barang</th>
                            <th width="10%">Qty</th>
                            <th width="10%">Satuan</th>
                            <th width="15%">WS</th>
                            <th width="5%">Act</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-detail">
                    </tbody>
                    <tfoot class="bg-light fw-bold">
                        <tr>
                            <td colspan="3" class="text-right">Total</td>
                            <td class="text-center" id="total_qty">0</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer text-right">
            <button type="button" class="btn btn-primary px-3 fw-bold" onclick="saveAll()"><i class="fas fa-save"></i> Simpan</button>
        </div>
    </div>
</form>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

        if(localStorage.getItem('booking_draft')) {
            $('#tbody-detail').html(localStorage.getItem('booking_draft'));
            calculateTotal();
        }

        $('#jenis').on('change', function() {
            let jenis = $(this).val();
            let selectItem = $('#id_item');

            selectItem.empty().append('<option value="">-- Pilih Item --</option>');
            $('#satuan').val('');
            $('#qty').val('');

            if(jenis) {
                Swal.fire({
                    title: 'Mencari Barang...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                let url = '{{ route("booking-stock-get-items", ":jenis") }}';
                url = url.replace(':jenis', jenis);

                $.ajax({
                    url: url,
                    type: "GET",
                    success: function(res) {
                        Swal.close();
                        if(res.length > 0) {
                            $.each(res, function(index, item) {
                                selectItem.append(`<option value="${item.id}" data-unit="${item.satuan}">${item.itemdesc}</option>`);
                            });
                            selectItem.trigger('change');
                        } else {
                            Swal.fire({toast: true, position: 'top-end', icon: 'info', title: 'Data barang tidak ditemukan!', showConfirmButton: false, timer: 3000});
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Error', 'Gagal menarik data barang dari server.', 'error');
                    }
                });
            }
        });

        $('#id_item').on('change', function() {
            let id_item = $(this).val();
            let unit = $(this).find(':selected').data('unit');

            if(unit) {
                $('#satuan').val(unit);
            } else {
                $('#satuan').val('');
            }

            if(id_item) {
                $.ajax({
                    url: "{{ url('booking-stock/get-stock') }}/" + id_item,
                    type: "GET",
                    success: function(res) {
                        $('#qty').val(res.stok);
                    },
                    error: function() {
                        console.log('Gagal mengambil data stok gudang.');
                    }
                });
            } else {
                $('#qty').val('');
            }
        });
    });

    function addToTable() {
        let id_item = $('#id_item').val();
        let name_item = $('#id_item option:selected').text();
        let qty = $('#qty').val();
        let satuan = $('#satuan').val();
        let ws = $('#ws').val();

        if(!id_item || !qty || !ws) {
            Swal.fire('Error', 'Lengkapi data item, qty, dan WS sebelum klik Add!', 'error');
            return;
        }

        let rowCount = $('#tbody-detail tr').length + 1;
        let tr = `
            <tr>
                <td class="text-center row-number">${rowCount}</td>
                <td class="text-center">${id_item} <input type="hidden" name="id_item[]" value="${id_item}"></td>
                <td>${name_item} <input type="hidden" name="nama_barang[]" value="${name_item}"></td>
                <td class="text-center">${qty} <input type="hidden" name="qty_det[]" value="${qty}"></td>
                <td class="text-center">${satuan} <input type="hidden" name="satuan_det[]" value="${satuan}"></td>
                <td class="text-center">${ws} <input type="hidden" name="ws_det[]" value="${ws}"></td>
                <td class="text-center">
                    <button type="button" class="btn btn-xs btn-danger" onclick="removeRow(this)"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;

        $('#tbody-detail').append(tr);
        calculateTotal();

        localStorage.setItem('booking_draft', $('#tbody-detail').html());

        clearInput();
    }

    function clearInput() {
        $('#id_item, #ws').val('').trigger('change');
        $('#qty, #satuan').val('');
    }

    function removeRow(btn) {
        $(btn).closest('tr').remove();

        $('#tbody-detail tr').each(function(index) {
            $(this).find('.row-number').text(index + 1);
        });

        calculateTotal();

        localStorage.setItem('booking_draft', $('#tbody-detail').html());
    }

    function calculateTotal() {
        let total = 0;
        $('input[name="qty_det[]"]').each(function() {
            total += parseFloat($(this).val()) || 0;
        });
        $('#total_qty').text(total);
    }

    function saveAll() {
        if($('#tbody-detail tr').length == 0) {
            Swal.fire('Error', 'List item masih kosong! Silakan isi minimal 1 barang.', 'error');
            return;
        }

        Swal.fire({
            title: 'Simpan data booking?',
            text: "Pastikan data item dan Qty sudah benar.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Simpan!'
        }).then((result) => {
            if (result.isConfirmed) {

                Swal.fire({ title: 'Menyimpan Data...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});

                $.ajax({
                    url: "{{ route('booking-stock-store') }}",
                    type: "POST",
                    data: $('#form-save-booking').serialize(),
                    success: function(res) {
                        Swal.close();
                        if(res.status == 200) {

                            localStorage.removeItem('booking_draft');

                            Swal.fire('Success', res.message, 'success').then(() => {
                                window.location.href = "{{ route('booking-stock') }}";
                            });
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Error', 'Terjadi kesalahan sistem saat menyimpan data.', 'error');
                    }
                });
            }
        });
    }
</script>
@endsection
