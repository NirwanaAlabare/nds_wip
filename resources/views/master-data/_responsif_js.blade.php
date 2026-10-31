{{-- JS bersama daftar Master Data (layar sempit / HP, tombol aksi, export Excel). Disisipkan di dalam <script> halaman daftar.
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
    // b = { id, aktif, urlEdit, status } - status (ACTIVE / INACTIVE / BLOCKED) diisi kalau status baru dipilih
    // lewat swal (supplier); tanpa status, tombolnya bolak-balik Active / Inactive.
    function mdTombolAksi(b) {
        var id = $('<div>').text(String(b.id)).html();
        var edit = b.urlEdit + '?id=' + encodeURIComponent(b.id);
        var status = ' data-id="' + id + '" data-aktif="' + (b.aktif ? '1' : '0') + '"';
        var tiga = { ACTIVE: ['md-ikon-aktif', 'fa-toggle-on', 'Active'], INACTIVE: ['md-ikon-nonaktif', 'fa-toggle-off', 'Inactive'],
                     BLOCKED: ['md-ikon-blokir', 'fa-ban', 'Blocked'] }[b.status];
        var tombol = tiga
            ? { kelas: tiga[0], ikon: tiga[1], judul: tiga[2] + ' - click to change status', menuIkon: 'fa-arrows-rotate', menu: 'Change Status' }
            : { kelas: b.aktif ? 'md-ikon-aktif' : 'md-ikon-nonaktif', ikon: b.aktif ? 'fa-toggle-on' : 'fa-toggle-off',
                judul: b.aktif ? 'Active - click to set Inactive' : 'Inactive - click to set Active',
                menuIkon: b.aktif ? 'fa-toggle-off' : 'fa-toggle-on', menu: b.aktif ? 'Set Inactive' : 'Set Active' };
        return '<div class="dn-aksi md-aksi-lebar">'
            + '<button type="button" class="btn dn-ikon md-ikon-lihat md-btn-lihat" data-id="' + id + '" title="Show detail"><i class="fas fa-eye"></i></button>'
            + '<button type="button" class="btn dn-ikon ' + tombol.kelas + ' md-btn-status"' + status
            + ' title="' + tombol.judul + '">'
            + '<i class="fas ' + tombol.ikon + '"></i></button>'
            + '<a class="btn dn-ikon dn-ikon-ubah md-ke-form" href="' + edit + '" data-dasar="' + b.urlEdit + '" data-id="' + id + '" title="Edit"><i class="fas fa-pen"></i></a>'
            + '</div>'
            + '<div class="dropdown md-aksi-hp">'
            + '<button type="button" class="btn dn-ikon md-ikon-menu" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">'
            + '<i class="fas fa-ellipsis-vertical"></i></button>'
            + '<ul class="dropdown-menu dropdown-menu-end md-menu-aksi">'
            + '<li><button type="button" class="dropdown-item md-btn-lihat" data-id="' + id + '"><i class="fas fa-eye"></i> Show detail</button></li>'
            + '<li><button type="button" class="dropdown-item md-btn-status"' + status + '>'
            + '<i class="fas ' + tombol.menuIkon + '"></i> ' + tombol.menu + '</button></li>'
            + '<li><a class="dropdown-item md-ke-form" href="' + edit + '" data-dasar="' + b.urlEdit + '" data-id="' + id + '"><i class="fas fa-pen"></i> Edit</a></li>'
            + '</ul></div>';
    }

    // Data baris dari tombol di dalamnya - juga kalau tombolnya sedang ada di baris detail (mode HP)
    function mdBarisDari(tabel, el) {
        var $tr = $(el).closest('tr');
        if ($tr.hasClass('child')) { $tr = $tr.prev(); }
        return tabel.row($tr).data() || {};
    }

    // Export Excel lewat XHR supaya swal loading tampil selama file dibuat, lalu diunduh dengan nama dari server.
    // Respons yang bukan file Excel (mis. sesi habis -> halaman login) dianggap gagal.
    function mdExportExcel(url) {
        Swal.fire({
            title: 'Exporting...', text: 'Preparing the Excel file, please wait.',
            allowOutsideClick: false, allowEscapeKey: false,
            showConfirmButton: false, customClass: { popup: 'dn-swal' },
            didOpen: function () { Swal.showLoading(); }
        });
        var gagal = function () {
            Swal.fire({ icon: 'error', title: 'Export failed', text: 'Please try again.', customClass: { popup: 'dn-swal' } });
        };
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url);
        xhr.responseType = 'blob';
        xhr.onload = function () {
            if (xhr.status !== 200 || !/spreadsheetml/.test(xhr.getResponseHeader('Content-Type') || '')) { gagal(); return; }
            var nama = /filename\*=UTF-8''([^;]+)|filename="?([^";]+)"?/i.exec(xhr.getResponseHeader('Content-Disposition') || '');
            var a = document.createElement('a');
            a.href = URL.createObjectURL(xhr.response);
            a.download = nama ? decodeURIComponent(nama[1] || nama[2]) : 'export.xlsx';
            document.body.appendChild(a);
            a.click();
            a.remove();
            setTimeout(function () { URL.revokeObjectURL(a.href); }, 10000);
            Swal.close();
        };
        xhr.onerror = gagal;
        xhr.send();
    }
