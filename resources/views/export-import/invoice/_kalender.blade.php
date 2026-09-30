{{-- ===========================================================================
     Kotak tanggal + kalender, dipakai semua halaman invoice (daftar & form).

     Kalender bawaan <input type="date"> digambar browser: besar, polos, dan
     beda-beda di tiap browser - tidak bisa diberi gaya. Jadi dipakai datepicker
     jQuery UI (sudah dimuat layout) yang tampilannya diatur di _skin.

     Isi yang tampil '18 Sep 2026' - sama dengan kolom tanggal di tabel, dan
     nama bulan tidak bisa tertukar dengan tanggalnya. Yang dikirim ke server
     tetap yyyy-mm-dd lewat
     tglIso(). Kotaknya ditandai kelas .dn-tgl.

     Cara pakai: di dalam <script> halaman, sesudah select2 disiapkan,
         @include('export-import.invoice._kalender')
     lalu baca/tulis tanggalnya dengan tglIso('#id') & tglSet('#id', 'yyyy-mm-dd').
     =========================================================================== --}}
    var FORMAT_TGL = 'd M yy';

    /** Tanggal yang dipilih dalam bentuk yyyy-mm-dd; '' kalau kosong. */
    function tglIso(sel) {
        var d = $(sel).datepicker('getDate');
        return d ? $.datepicker.formatDate('yy-mm-dd', d) : '';
    }

    /** Isi kotak tanggal dari nilai yyyy-mm-dd. */
    function tglSet(sel, iso) {
        try {
            $(sel).datepicker('setDate', $.datepicker.parseDate('yy-mm-dd', iso));
            $(sel).data('tglSah', $(sel).val());
        } catch (e) { /* bukan yyyy-mm-dd: biarkan nilai lama */ }
    }

    $('.dn-tgl').each(function () {
        var $i = $(this);
        $i.data('tglSah', $i.val());
        $i.datepicker({
            dateFormat: FORMAT_TGL,
            // Kotak readonly (mis. Invoice Date yang ikut tanggal SJ) tetap
            // dipasangi datepicker supaya tglIso()/tglSet() jalan, tapi
            // kalendernya tidak pernah terbuka sendiri.
            showOn: $i.prop('readonly') ? 'off' : 'focus',
            showOtherMonths: true,
            selectOtherMonths: true,
            dayNamesMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
            prevText: '«',
            nextText: '»',
            showAnim: '',
            beforeShow: function (input, inst) {
                // Di dalam modal, popup ditaruh di modal itu: kalau di <body>,
                // focus trap Bootstrap merebut fokus setiap kali tanggal diklik.
                var $modal = $(input).closest('.modal');
                inst.dpDiv.addClass('dn-kalender').appendTo($modal.length ? $modal : 'body');
            },
            onSelect: function () { $i.data('tglSah', $i.val()).trigger('change'); }
        });
    }).on('blur', function () {
        // Ketikan yang bukan tanggal sah (31 Feb 2026, 17 Sep 20) dikembalikan
        // ke tanggal terakhir yang benar.
        var $i = $(this);
        try {
            $.datepicker.parseDate(FORMAT_TGL, $i.val());
            $i.data('tglSah', $i.val());
        } catch (e) {
            $i.val($i.data('tglSah'));
        }
    });
