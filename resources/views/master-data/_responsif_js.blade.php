{{-- JS bersama daftar Master Data untuk layar sempit (HP). Disisipkan di dalam <script> halaman daftar.
     Kolom yang tidak muat disembunyikan DataTables Responsive dan dibuka lewat tombol + / - di
     kolom pertama; urutan kolom yang dipertahankan diatur responsivePriority di definisi kolom. --}}
    function mdResponsif() {
        return {
            details: {
                type: 'inline',
                target: 0,
                // Isi baris detail: label kecil di atas nilainya (tampilan sel aslinya, termasuk pil status)
                renderer: function (api, rowIdx, columns) {
                    var isi = $.map(columns, function (c) {
                        if (!c.hidden) { return null; }
                        return '<div class="md-dtr-butir"><span class="md-dtr-label">' + $('<div>').text(c.title).html() + '</span>'
                            + '<div class="md-dtr-nilai">' + (c.data === '' || c.data == null ? '-' : c.data) + '</div></div>';
                    }).join('');
                    return isi ? '<div class="md-dtr">' + isi + '</div>' : false;
                }
            }
        };
    }

    // Tombol aksi per baris: tiga tombol berjajar di layar lebar, satu tombol menu (titik tiga) di HP.
    // Keduanya memakai kelas yang sama (md-btn-lihat, md-btn-status, md-ke-form), jadi handler-nya sama.
    // b = { id, aktif, urlEdit }
    function mdTombolAksi(b) {
        var id = $('<div>').text(String(b.id)).html();
        var edit = b.urlEdit + '?id=' + encodeURIComponent(b.id);
        var status = ' data-id="' + id + '" data-aktif="' + (b.aktif ? '1' : '0') + '"';
        return '<div class="dn-aksi md-aksi-lebar">'
            + '<button type="button" class="btn dn-ikon md-ikon-lihat md-btn-lihat" data-id="' + id + '" title="Show detail"><i class="fas fa-eye"></i></button>'
            + '<button type="button" class="btn dn-ikon ' + (b.aktif ? 'md-ikon-aktif' : 'md-ikon-nonaktif') + ' md-btn-status"' + status
            + ' title="' + (b.aktif ? 'Active - click to set Inactive' : 'Inactive - click to set Active') + '">'
            + '<i class="fas ' + (b.aktif ? 'fa-toggle-on' : 'fa-toggle-off') + '"></i></button>'
            + '<a class="btn dn-ikon dn-ikon-ubah md-ke-form" href="' + edit + '" data-dasar="' + b.urlEdit + '" data-id="' + id + '" title="Edit"><i class="fas fa-pen"></i></a>'
            + '</div>'
            + '<div class="dropdown md-aksi-hp">'
            + '<button type="button" class="btn dn-ikon md-ikon-menu" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">'
            + '<i class="fas fa-ellipsis-vertical"></i></button>'
            + '<ul class="dropdown-menu dropdown-menu-end md-menu-aksi">'
            + '<li><button type="button" class="dropdown-item md-btn-lihat" data-id="' + id + '"><i class="fas fa-eye"></i> Show detail</button></li>'
            + '<li><button type="button" class="dropdown-item md-btn-status"' + status + '>'
            + '<i class="fas ' + (b.aktif ? 'fa-toggle-off' : 'fa-toggle-on') + '"></i> ' + (b.aktif ? 'Set Inactive' : 'Set Active') + '</button></li>'
            + '<li><a class="dropdown-item md-ke-form" href="' + edit + '" data-dasar="' + b.urlEdit + '" data-id="' + id + '"><i class="fas fa-pen"></i> Edit</a></li>'
            + '</ul></div>';
    }

    // Data baris dari tombol di dalamnya - juga kalau tombolnya sedang ada di baris detail (mode HP)
    function mdBarisDari(tabel, el) {
        var $tr = $(el).closest('tr');
        if ($tr.hasClass('child')) { $tr = $tr.prev(); }
        return tabel.row($tr).data() || {};
    }
