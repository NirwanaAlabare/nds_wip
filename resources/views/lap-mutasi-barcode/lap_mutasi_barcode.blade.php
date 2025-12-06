@extends('layouts.index')

<style type="text/css">
    .dataTables_processing {
        z-index: 9999 !important;
        background-color: rgba(255, 255, 255, 0.8); /* opsional biar semi transparan */
    /*top: 50% !important;
    left: 50% !important;*/
    transform: translate(-50%, -50%);
    position: fixed !important; /* supaya nempel ke layar */
}
</style>

@section('custom-link')
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
<form action="{{ route('export_excel_mut_barcode') }}" method="get">
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-file-alt fa-sm"></i> Laporan Mutasi Barcode</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small>From</small></label>
                    <input type="date" class="form-control form-control-sm" id="from" name="from"
                    value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small>To</small></label>
                    <input type="date" class="form-control form-control-sm" id="to" name="to"
                    value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <input type='button' class='btn btn-primary btn-sm' onclick="dataTableReload();" value="Search">
                    <a onclick="export_excel()" class="btn btn-success position-relative btn-sm">
                        <i class="fas fa-file-excel"></i>
                        Export
                    </a>

                    <a onclick="copySaldo()" class="btn btn-warning position-relative btn-sm">
                        <i class="fas fa-copy"></i>
                        Copy Saldo
                    </a>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table id="datatable" class="table table-bordered table-striped table-head-fixed 100 text-nowrap">
                <thead>
                    <tr>
                        <th>No Barcode</th>
                        <th>No BPB</th>
                        <th>Tgl BPB</th>
                        <th>Supplier</th>
                        <th>Buyer</th>
                        <th>Lokasi</th>
                        <th>Id JO</th>
                        <th>No WS</th>
                        <th>Style</th>
                        <th>Id Item</th>
                        <th>Nama Barang</th>
                        <th>No Roll</th>
                        <th>No Lot</th>
                        <th>satuan</th>
                        <th>Saldo Awal</th>
                        <th>Pemasukan</th>
                        <th>Pengeluaran</th>
                        <th>Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="14" class="text-center">TOTAL</th>
                        <th></th> <!-- saldo awal -->
                        <th></th> <!-- qty in -->
                        <th></th> <!-- qty out -->
                        <th></th> <!-- saldo akhir -->
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
<!-- DataTables  & Plugins -->
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

<!-- Select2 -->
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>

   function copySaldo() {
    let from = document.getElementById('from').value;
    let to   = document.getElementById('to').value;

    if (!from || !to) {
        Swal.fire("Perhatian", "Tanggal awal dan akhir harus diisi!", "warning");
        return;
    }

    let fromDate = new Date(from);
    let toDate   = new Date(to);

    let tgl_periode = new Date(Date.UTC(fromDate.getFullYear(), fromDate.getMonth() + 1, 1));
    let tglPeriode = tgl_periode.toISOString().split('T')[0];
    console.log(from + ' ' + to + ' ' + tglPeriode);

    // ✅ cek tanggal awal harus tanggal 1
    if (fromDate.getDate() !== 1) {
        Swal.fire("Tidak valid", "Tanggal awal harus tanggal 1!", "error");
        return;
    }

    // ✅ cari tanggal terakhir bulan (contoh: 28, 30, 31)
    let lastDay = new Date(toDate.getFullYear(), toDate.getMonth() + 1, 0).getDate();

    // ✅ cek tanggal akhir harus akhir bulan
    if (toDate.getDate() !== lastDay) {
        Swal.fire("Tidak valid", "Tanggal akhir harus tanggal terakhir bulan!", "error");
        return;
    }

    // ✅ cek apakah dalam 1 bulan yang sama
    if (fromDate.getFullYear() === toDate.getFullYear() &&
        fromDate.getMonth() === toDate.getMonth()) {

        Swal.fire({
            title: "Yakin?",
            text: "Saldo bulan ini akan dicopy!",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Ya, Copy!",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route('copy-saldo-mutasi-barcode') }}',
                    method: "POST",
                    data: {
                        from: from,
                        to: to,
                        tgl_periode : tglPeriode,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        Swal.fire("Berhasil", "Saldo berhasil dicopy.", "success");
                        dataTableReload();
                    },
                    error: function(xhr) {
                        Swal.fire("Error", "Gagal copy saldo.", "error");
                        console.log(xhr);
                    }
                });
            }
        });

    } else {
        Swal.fire("Tidak valid", "Tanggal awal dan akhir harus dalam bulan yang sama!", "error");
    }
}



function number_format(number, decimals = 0, dec_point = '.', thousands_sep = ',') {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    let n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '';

    s = (prec ? n.toFixed(prec) : Math.round(n).toString()).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}


let datatable = $("#datatable").DataTable({
    ordering: true,
    processing: true,
    serverSide: false,
    paging: true,
    searching: true,
    scrollY: '300px',
    scrollX: '300px',
    scrollCollapse: true,
    deferLoading: 0,
    deferRender: true,     // SUPER PENTING
    scroller: true, 
    ajax: {
        url: '{{ route('lap-mutasi-barcode') }}',
        data: function(d) {
            d.dateFrom = $('#from').val();
            d.dateTo = $('#to').val();
        },
    },
    columns: [
    { data: 'no_barcode' },
    { data: 'no_dok' },
    { data: 'tgl_dok' },
    { data: 'supplier' },
    { data: 'buyer' },
    { data: 'kode_lok' },
    { data: 'id_jo' },
    { data: 'kpno' },
    { data: 'styleno' },
    { data: 'id_item' },
    { data: 'itemdesc' },
    { data: 'no_roll' },
    { data: 'no_lot' },
    { data: 'satuan' },
    { data: 'sal_awal' },
    { data: 'qty_in' },
    { data: 'qty_out' },
    { data: 'sal_akhir' }
    ],
    columnDefs: [
    {
        targets: 5, // kode_lok
        render: (data) => data ? data + ' FABRIC WAREHOUSE RACK' : '-',
    },
    {
        targets: [14,15,16,17],
        render: (data, type, row, meta) => data ? parseFloat(data).toFixed(2) : "0.00",
        className: "text-right"
    }
    ],

    // 🔹 Hitung total di footer
    footerCallback: function (row, data, start, end, display) {
    let api = this.api();

    // ambil semua baris yang saat ini "applied" (termasuk search/filter)
    let rows = api.rows({ search: 'applied' }).data();

    // helper untuk parse angka aman (hilangkan koma dan non-digit)
    const toNumber = v => {
        if (v === null || v === undefined || v === '') return 0;
        // jika sudah number
        if (typeof v === 'number') return v;
        // ubah ke string, hilangkan koma/space/currency, ganti tanda minus tetap
        let s = String(v).replace(/[^0-9\-\.\,]/g, '');
        // jika ada thousand separator koma, hilangkan koma
        s = s.replace(/,/g, '');
        let n = parseFloat(s);
        return isNaN(n) ? 0 : n;
    };

    // jumlahkan berdasarkan properti original dari row data
    let total_awal = 0, total_in = 0, total_out = 0, total_akhir = 0;
    for (let i = 0; i < rows.length; i++) {
        let r = rows[i];
        total_awal  += toNumber(r.sal_awal);
        total_in    += toNumber(r.qty_in);
        total_out   += toNumber(r.qty_out);
        total_akhir += toNumber(r.sal_akhir);
    }

    // fungsi formatting (ganti kalau kamu pakai library lain)
    function number_format(number, decimals, dec_point, thousands_sep) {
        // simple implementation
        number = Number(number) || 0;
        let fixed = number.toFixed(decimals);
        let parts = fixed.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousands_sep);
        return parts.join(dec_point);
    }

    // update footer (pastikan footer ada di HTML)
    $(api.column(14).footer()).html(number_format(total_awal, 2, '.', ','));
    $(api.column(15).footer()).html(number_format(total_in,   2, '.', ','));
    $(api.column(16).footer()).html(number_format(total_out,  2, '.', ','));
    $(api.column(17).footer()).html(number_format(total_akhir,2, '.', ','));
}

});


function dataTableReload() {
    datatable.ajax.reload();
}

function export_excel() {
    let from = document.getElementById("from").value;
    let to = document.getElementById("to").value;

    Swal.fire({
        title: 'Please Wait,',
        html: 'Exporting Data...',
        didOpen: () => {
            Swal.showLoading()
        },
        allowOutsideClick: false,
    });

    $.ajax({
        type: "get",
        url: '{{ route('export_excel_mut_barcode') }}',
        data: {
            from: from,
            to: to
        },
        xhrFields: {
            responseType: 'blob'
        },
        success: function(response) {
            {
                swal.close();
                Swal.fire({
                    title: 'Data Berhasil Di Export!',
                    icon: "success",
                    showConfirmButton: true,
                    allowOutsideClick: false
                });
                var blob = new Blob([response]);
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = "Laporan Mutasi Barcode Dari  " + from + " sampai " +
                to + ".xlsx";
                link.click();

            }
        },
    });
}

function caridata() {
        // Declare variables
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("cari_item");
        filter = input.value.toUpperCase();
        table = document.getElementById("datatable");
        tr = table.getElementsByTagName("tr");

        // Loop through all table rows, and hide those who don't match the search query
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[0]; //kolom ke berapa
            if (td) {
                txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
</script>
@endsection
