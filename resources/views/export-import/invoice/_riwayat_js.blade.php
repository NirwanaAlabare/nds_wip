    /* =====================================================================
       Riwayat invoice - dipakai modal rincian Local & Export.

       Baris detail invoice ditulis ulang tiap kali di-update, jadi keadaan
       sebelumnya cuma tersimpan di tabel riwayat. Server sudah mengirim tiap
       revisi beserta bedanya dari revisi sebelumnya, jadi di sini tinggal
       menampilkan - bentuk potretnya tidak perlu diketahui layar.

       Dimuat sekali per invoice, saat panelnya pertama dibuka. Memakai teks()
       milik layar masing-masing untuk mengamankan isi dari database.
       ===================================================================== */
    var RUT_RIWAYAT = @json(route('invoice-exim-riwayat'));

    var riwId = 0;             // invoice yang riwayatnya sedang tampil
    var riwSedang = false;     // sedang memuat - klik berulang tidak menumpuk

    /** Dipanggil waktu modal rincian dibuka untuk invoice lain. */
    function riwSetel() {
        riwId = 0;
        riwSedang = false;
        $('#riw-alur').empty();
        $('#riw-muat').prop('hidden', true);
        $('#riw-kosong').prop('hidden', true);
    }

    /** Muat riwayat satu invoice - yang sudah termuat tidak diminta lagi. */
    function riwMuat(id) {
        id = parseInt(id, 10) || 0;
        if (!id || riwId === id || riwSedang) { return; }

        riwSedang = true;
        $('#riw-muat').prop('hidden', false);
        $('#riw-kosong').prop('hidden', true);
        $('#riw-alur').empty();

        $.getJSON(RUT_RIWAYAT, { id: id }).done(function (res) {
            riwId = id;
            riwGambar((res && res.baris) ? res.baris : []);
        }).fail(function (x) {
            // Riwayat gagal dimuat bukan alasan menutup rincian invoice-nya -
            // alasannya ditulis di tempat panelnya saja.
            var p = (x.responseJSON && x.responseJSON.pesan)
                ? x.responseJSON.pesan : 'History could not be loaded.';
            $('#riw-kosong').prop('hidden', false);
            $('#riw-kosong-teks').text(p);
        }).always(function () {
            riwSedang = false;
            $('#riw-muat').prop('hidden', true);
        });
    }

    function riwGambar(baris) {
        if (!baris.length) {
            $('#riw-kosong').prop('hidden', false);
            $('#riw-kosong-teks').text('No history recorded for this invoice yet.');
            return;
        }

        $('#riw-alur').html(baris.map(function (r, i) {
            return '<div class="dn-riw-butir' + (r.aksi === 'CANCEL' ? ' dn-riw-batal' : '') + '">'
                +    '<div class="dn-riw-titik">' + (r.revisi + 1) + '</div>'
                +    '<div class="dn-riw-kartu">'
                +      '<div class="dn-riw-kepala">'
                +        '<b class="dn-riw-judul">' + teks(r.judul) + '</b>'
                +        '<span class="dn-riw-oleh"><i class="fas fa-user"></i> '
                +          teks(r.oleh || '-') + '</span>'
                +        '<span class="dn-riw-waktu"><i class="fas fa-clock"></i> '
                +          teks(r.waktu) + '</span>'
                +      '</div>'
                +      (r.ringkas ? '<div class="dn-riw-ringkas">' + teks(r.ringkas) + '</div>' : '')
                +      riwBeda(r)
                +      '<button type="button" class="btn btn-dn-lipat dn-riw-lipat" data-riw="' + i + '"'
                +        ' aria-expanded="false" aria-controls="riw-potret-' + i + '">'
                +        '<span class="dn-riw-lipat-teks">Show what was saved</span>'
                +        '<i class="fas fa-chevron-down dn-lipat-panah"></i>'
                +      '</button>'
                +      '<div class="dn-riw-potret" id="riw-potret-' + i + '" hidden>'
                +        riwPotret(r.isi) + '</div>'
                +    '</div>'
                +  '</div>';
        }).join(''));
    }

    /** Apa yang berubah dari revisi sebelumnya, dikelompokkan per bagian. */
    function riwBeda(r) {
        var beda = r.beda || [];
        if (!beda.length) {
            return '<div class="dn-riw-catatan">'
                + (r.revisi === 0 ? 'This is the earliest recorded state.'
                                  : 'Saved again, but nothing in the data changed.')
                + '</div>';
        }

        var urut = [], peta = {};
        beda.forEach(function (b) {
            if (!peta[b.bagian]) { peta[b.bagian] = []; urut.push(b.bagian); }
            peta[b.bagian].push(b);
        });

        return '<div class="dn-riw-beda">' + urut.map(function (nama) {
            return '<div class="dn-riw-beda-judul">' + teks(nama)
                + ' <span class="dn-riw-cacah">' + peta[nama].length + '</span></div>'
                + '<ul class="dn-riw-beda-daftar">'
                + peta[nama].map(riwBarisBeda).join('')
                + '</ul>';
        }).join('') + '</div>';
    }

    /**
     * Satu baris perubahan.
     *
     * Jenisnya dibaca dari server, bukan ditebak dari nilainya - isi kolom bisa
     * saja memang berbunyi "added".
     */
    function riwBarisBeda(b) {
        if (b.jenis === 'tambah') {
            return '<li><span class="dn-riw-tanda dn-riw-tambah">added</span> '
                + teks(b.apa) + riwEkor(b.ke) + '</li>';
        }
        if (b.jenis === 'buang') {
            return '<li><span class="dn-riw-tanda dn-riw-buang">removed</span> '
                + teks(b.apa) + riwEkor(b.dari) + '</li>';
        }
        return '<li>' + teks(b.apa) + ': ' + riwNilai(b.dari)
            + ' <i class="fas fa-arrow-right dn-riw-panah"></i> ' + riwNilai(b.ke) + '</li>';
    }

    /** Angka penting baris yang ditambah/dibuang, kalau ada. */
    function riwEkor(v) {
        v = (v === null || v === undefined) ? '' : String(v);
        return v === '' ? '' : ' <span class="dn-riw-ekor">(' + teks(v) + ')</span>';
    }

    /** Nilai kosong ditulis sebagai tanda, bukan dibiarkan kosong - biar bedanya terbaca. */
    function riwNilai(v) {
        v = (v === null || v === undefined) ? '' : String(v);
        return v === '' ? '<i class="dn-riw-hampa">empty</i>' : '<b>' + teks(v) + '</b>';
    }

    /** Potret lengkap satu revisi - keadaan invoice waktu itu. */
    function riwPotret(isi) {
        isi = isi || {};
        var h = riwPasangan(isi.header, 'dn-riw-info');

        (isi.grup || []).forEach(function (g) {
            if (!g.baris || !g.baris.length) { return; }
            h += '<div class="dn-riw-grup-judul">' + teks(g.nama)
               +   ' <span class="dn-riw-cacah">' + g.baris.length + '</span></div>'
               + '<ul class="dn-riw-grup">'
               + g.baris.map(function (r) {
                     return '<li><b>' + teks(r.label || '-') + '</b>'
                          + '<span class="dn-riw-nilai">' + riwNilaiBaris(r.nilai) + '</span></li>';
                 }).join('')
               + '</ul>';
        });

        if (isi.uang && isi.uang.length) {
            h += '<div class="dn-riw-grup-judul">Amounts</div>'
               + riwPasangan(isi.uang, 'dn-riw-info dn-riw-uang');
        }
        return h || '<div class="dn-riw-catatan">Nothing was recorded in this snapshot.</div>';
    }

    /** Isi kosong dilewati - potretnya sudah panjang tanpa baris yang tidak berisi. */
    function riwNilaiBaris(nilai) {
        return (nilai || []).filter(function (p) {
            return p && String(p[1] === null || p[1] === undefined ? '' : p[1]) !== '';
        }).map(function (p) {
            return teks(p[0]) + ' <b>' + teks(p[1]) + '</b>';
        }).join(' <span class="dn-lipat-titik">&middot;</span> ');
    }

    function riwPasangan(pasangan, kelas) {
        var isi = (pasangan || []).filter(function (p) {
            return p && String(p[1] === null || p[1] === undefined ? '' : p[1]) !== '';
        });
        if (!isi.length) { return ''; }
        return '<dl class="' + kelas + '">' + isi.map(function (p) {
            return '<div><dt>' + teks(p[0]) + '</dt><dd>' + teks(p[1]) + '</dd></div>';
        }).join('') + '</dl>';
    }

    // Kartunya digambar ulang tiap kali riwayat dimuat, jadi tombolnya diikat
    // lewat document - bukan ke tombol yang sudah ada.
    $(document).on('click', '.dn-riw-lipat', function () {
        var $p = $('#riw-potret-' + $(this).data('riw'));
        var buka = $p.prop('hidden');
        $p.prop('hidden', !buka);
        $(this).attr('aria-expanded', buka ? 'true' : 'false')
            .find('.dn-riw-lipat-teks').text(buka ? 'Hide what was saved' : 'Show what was saved');
    });
