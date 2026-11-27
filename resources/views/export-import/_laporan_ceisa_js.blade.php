{{-- ===========================================================================
     Pembantu JavaScript laporan CEISA (Rekonsiliasi, Ceisa Detail, Data BC Signalbit).
     Disisipkan di dalam <script> halaman, sesudah @include invoice._kalender:
         @include('export-import._laporan_ceisa_js')
     Semua render DataTables mengembalikan nilai aslinya untuk pencarian, dan
     tampilan yang sudah dirapikan untuk layar.
     =========================================================================== --}}
    var BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    /** Isi teks apa adanya: sebagian data CEISA tersimpan dengan entity HTML
        (&quot; dll) - diterjemahkan dulu, sama seperti tampilan lama. Lewat
        textarea, jadi tag di dalamnya tidak pernah dijalankan. */
    function polos(v) { return $('<textarea>').html(String(v)).text(); }

    /** Teks aman untuk HTML; kosong (atau '-' dari server) ditulis '-' redup. */
    function teks(v) {
        if (v == null || String(v).trim() === '' || v === '-') { return '<span class="dn-lap-kosong">-</span>'; }
        return $('<div>').text(polos(v)).html();
    }

    /** Angka bergaya en-US dengan desMin-desMaks desimal. Nilai uang 2-2, qty 0-2, harga satuan 2-4. */
    function angka(v, desMin, desMaks) {
        if (v == null || v === '') { return teks(v); }
        var n = Number(String(v).replace(/,/g, ''));
        if (isNaN(n)) { return teks(v); }
        return n.toLocaleString('en-US', { minimumFractionDigits: desMin, maximumFractionDigits: desMaks });
    }

    function rTeks(v, t) { return t === 'display' ? teks(v) : (v == null ? '' : v); }

    /** Teks panjang (nama supplier, nama barang) - boleh turun baris. */
    function rLipat(v, t) { return t === 'display' ? '<div class="dn-lap-lipat">' + teks(v) + '</div>' : (v == null ? '' : v); }

    /** Nama barang: kolom lebar, maksimal 3 baris; teks lengkap di tooltip. */
    function rLipatLebar(v, t) {
        if (t !== 'display') { return v == null ? '' : v; }
        var judul = v == null ? '' : polos(v).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
        return '<div class="dn-lap-lipat dn-lap-lebar dn-lap-potong" title="' + judul + '">' + teks(v) + '</div>';
    }

    /** Nomor dokumen yang ditonjolkan. */
    function rKunci(v, t) { return t === 'display' ? '<span class="dn-lap-kunci">' + teks(v) + '</span>' : (v == null ? '' : v); }

    /** yyyy-mm-dd -> 19 Nov 2026, sama dengan tanggal di daftar Invoice EXIM. */
    function rTgl(v, t) {
        if (t !== 'display') { return v || ''; }
        var p = /^(\d{4})-(\d{2})-(\d{2})/.exec(v || '');
        return p ? p[3] + ' ' + BULAN[parseInt(p[2], 10) - 1] + ' ' + p[1] : teks(v);
    }

    /** yyyy-mm-dd hh:mm:ss -> 19 Nov 2026 07:40 (detiknya tidak dipakai di layar). */
    function rJam(v, t) {
        if (t !== 'display') { return v || ''; }
        var p = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/.exec(v || '');
        return p ? p[3] + ' ' + BULAN[parseInt(p[2], 10) - 1] + ' ' + p[1] + ' ' + p[4] + ':' + p[5] : rTgl(v, t);
    }

    /** Render angka dengan desMin-desMaks desimal. */
    function rAngka(desMin, desMaks) {
        return function (v, t) { return t === 'display' ? angka(v, desMin, desMaks) : v; };
    }

    /** Render selisih: merah kalau bukan nol, redup kalau nol. */
    function rSelisih(desMin, desMaks) {
        return function (v, t) {
            if (t !== 'display') { return v; }
            var n = Number(String(v == null ? 0 : v).replace(/,/g, ''));
            return '<span class="' + (n ? 'dn-lap-beda' : 'dn-lap-nol') + '">'
                + angka(v == null ? 0 : v, desMin, desMaks) + '</span>';
        };
    }

    /** Pil status bertitik - bentuknya sama dengan status di daftar Invoice EXIM. */
    function pil(isi, kelas) {
        return '<span class="dn-pil ' + kelas + '"><span class="dn-pil-titik"></span>' + teks(isi) + '</span>';
    }

    /**
     * Opsi DataTables bersama. Tabelnya dibungkus .dn-lap-gulir (geser ke samping
     * & kepala menempel); baris Show entries/Search dan nomor halaman di luarnya.
     * Tanda memuat memakai overlay skin, bukan tulisan "Processing" DataTables.
     */
    function opsiTabel(opsi) {
        return $.extend({
            ordering: false,
            processing: false,
            serverSide: false,
            paging: true,
            searching: true,
            autoWidth: false,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
                 "<'dn-table-scroll dn-lap-gulir'tr>" +
                 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            language: {
                emptyTable: '<div class="dn-kosong-isi"><i class="fas fa-folder-open"></i>'
                    + '<span>No data in this date range.</span></div>',
                zeroRecords: '<div class="dn-kosong-isi"><i class="fas fa-magnifying-glass"></i>'
                    + '<span>No data matches your search.</span></div>'
            }
        }, opsi);
    }

    /** Overlay "Loading..." selama data diminta. Dipasang SEBELUM DataTable dibuat
        supaya permintaan pertama ikut tertangkap. xhr.dt juga terpanggil kalau gagal. */
    function pasangLoader(tabel, area) {
        $(tabel).on('preXhr.dt', function () { $(area).addClass('is-memuat'); })
                .on('xhr.dt', function () { $(area).removeClass('is-memuat'); });
    }

    /**
     * Bekukan n kolom pertama (kolomnya diberi kelas dn-lap-beku, yang terakhir
     * juga dn-lap-beku-akhir) supaya tetap terlihat waktu tabel digeser ke samping.
     * Lebar kolom ikut isi, jadi posisi kirinya dihitung ulang tiap kali tabel
     * digambar ulang atau layar berubah ukuran. Yang dijumlah lebar kolom, bukan
     * offsetLeft: posisi sel yang sedang menempel ikut bergeser.
     */
    function pasangBeku(tabel, n) {
        var $gulir = $(tabel.table().container()).find('.dn-lap-gulir');
        function atur() {
            var kiri = 0;
            for (var i = 0; i < n; i++) {
                var th = tabel.column(i).header();
                $(th).add(tabel.column(i, { page: 'current' }).nodes()).css('left', kiri + 'px');
                kiri += th.getBoundingClientRect().width;
            }
        }
        tabel.on('draw.dt', atur);
        $(window).on('resize', atur);
        $gulir.on('scroll', function () { $gulir.toggleClass('is-geser', this.scrollLeft > 0); });
        atur();
    }

    /** Unduh Excel dari server dengan filter yang sedang dipakai. */
    function unduhExcel(url, data, namaFile) {
        Swal.fire({
            title: 'Please Wait,',
            html: 'Exporting Data...',
            allowOutsideClick: false,
            customClass: { popup: 'dn-swal' },
            didOpen: function () { Swal.showLoading(); }
        });

        $.ajax({
            type: 'get',
            url: url,
            data: data,
            xhrFields: { responseType: 'blob' }
        }).done(function (res) {
            Swal.fire({
                title: 'Data Berhasil Di Export!',
                icon: 'success',
                allowOutsideClick: false,
                customClass: { popup: 'dn-swal' }
            });
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(new Blob([res]));
            link.download = namaFile;
            link.click();
        }).fail(function () {
            Swal.fire({
                icon: 'error',
                title: 'Export Gagal',
                text: 'Silakan coba lagi.',
                customClass: { popup: 'dn-swal' }
            });
        });
    }
