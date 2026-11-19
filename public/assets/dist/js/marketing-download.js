/* Export PDF / Excel marketing dengan loading: klik link export -> pop-up loading di tengah layar (halaman diblok), klik berulang
   diabaikan sampai selesai. Setelah siap loading ditutup: Excel langsung terunduh, PDF dibuka di tab baru (diunduh kalau tab diblokir).
   Dimuat layouts.index untuk halaman marketing. */
(function () {
    if (window.mktDownload) return; // sudah dimuat (jangan pasang handler klik dua kali)
    // Route export: Costing (print-pdf, print-pdf-batch, print-excel-costing), SO & BOM nds (pdf, pdf-batch, excel).
    var PATTERN = /\/(master-costing\/(print-pdf|print-pdf-batch|print-excel-costing)|master-marketing-(so|bom)-nds\/(pdf|pdf-batch|excel))(\/|$)/;
    var busy = false;

    function css() {
        if (document.getElementById('mkt-dl-style')) return;
        var s = document.createElement('style');
        s.id = 'mkt-dl-style';
        s.textContent = ''
            // lapisan penuh: memblok halaman selama loading / viewer terbuka
            + '.mkt-dl-overlay{position:fixed;inset:0;z-index:3000;display:grid;place-items:center;padding:16px;background:rgba(15,23,42,.45)}'
            + '.mkt-dl-overlay.is-loading{cursor:progress}'
            + '.mkt-dl-box{display:flex;flex-direction:column;align-items:center;gap:14px;min-width:240px;padding:26px 34px;border-radius:16px;'
            + 'background:#fff;color:#16202b;font:600 15px Inter,system-ui,sans-serif;box-shadow:0 24px 60px -20px rgba(0,0,0,.5)}'
            + '.mkt-dl-spin{width:34px;height:34px;border-radius:50%;border:4px solid rgba(40,74,99,.2);border-top-color:#284a63;animation:mkt-dl-spin .8s linear infinite}'
            + '@keyframes mkt-dl-spin{to{transform:rotate(360deg)}}'
            + 'html[data-theme="dark"] .mkt-dl-box{background:#111c31;color:#eef3fb}html[data-theme="dark"] .mkt-dl-spin{border-color:rgba(255,255,255,.2);border-top-color:#3d8bff}'
            + '.mkt-dl-busy{opacity:.55;pointer-events:none;cursor:progress}';
        document.head.appendChild(s);
    }

    var overlay = null, objectUrl = null;
    function openOverlay() {
        css();
        close();
        overlay = document.createElement('div');
        overlay.className = 'mkt-dl-overlay';
        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';
        return overlay;
    }
    function close() {
        if (overlay) { overlay.remove(); overlay = null; }
        if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = null; }
        document.body.style.overflow = '';
    }
    function showLoading(text) {
        var o = openOverlay();
        o.classList.add('is-loading');
        o.setAttribute('role', 'status');
        o.innerHTML = '<div class="mkt-dl-box"><span class="mkt-dl-spin"></span><span class="t"></span></div>';
        o.querySelector('.t').textContent = text;
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay && !overlay.classList.contains('is-loading')) close();
    });

    function fileName(res, url) {
        var cd = res.headers.get('content-disposition') || '';
        var m = cd.match(/filename\*=UTF-8''([^;]+)/i) || cd.match(/filename="?([^";]+)"?/i);
        if (m) { try { return decodeURIComponent(m[1]); } catch (e) { return m[1]; } }
        return url.split('/').pop().split('?')[0] || 'download';
    }

    function fail(message) {
        if (typeof window.mktToast === 'function') window.mktToast('error', 'File gagal dibuat', message);
        else if (window.Swal) window.Swal.fire({ icon: 'error', title: 'File gagal dibuat', text: message });
        else alert(message);
    }

    /** Unduh (Excel) / tampilkan (PDF) file export. el = tombol yang diklik (dinonaktifkan sementara). */
    function run(url, asPdf, el) {
        if (busy) return; // satu export sekaligus; halaman diblok selama loading
        busy = true;
        if (el) el.classList.add('mkt-dl-busy');
        showLoading(asPdf ? 'Menyiapkan PDF...' : 'Menyiapkan Excel...');
        fetch(url, { credentials: 'same-origin' })
            .then(function (res) {
                var type = res.headers.get('content-type') || '';
                if (!res.ok || type.indexOf('text/html') !== -1) {
                    throw new Error(res.status === 404 ? 'Dokumen tidak ditemukan.' : 'Server tidak mengembalikan file. Coba lagi; kalau masih gagal, hubungi IT.');
                }
                return res.blob().then(function (blob) { return { blob: blob, name: fileName(res, url) }; });
            })
            .then(function (file) {
                var href = URL.createObjectURL(file.blob);
                close();
                // PDF dibuka di tab baru setelah siap (loading hanya selama menyiapkan). Kalau tab diblokir browser, PDF diunduh.
                if (asPdf && window.open(href, '_blank')) {
                    setTimeout(function () { URL.revokeObjectURL(href); }, 60000);
                    return;
                }
                var a = document.createElement('a');
                a.href = href;
                a.download = file.name;
                document.body.appendChild(a);
                a.click();
                a.remove();
                setTimeout(function () { URL.revokeObjectURL(href); }, 60000);
            })
            .catch(function (err) {
                close();
                fail(err && err.message ? err.message : 'Koneksi terputus. Coba lagi.');
            })
            .then(function () {
                busy = false;
                if (el) el.classList.remove('mkt-dl-busy');
            });
    }

    function isExport(href) {
        try {
            var u = new URL(href, location.href);
            return u.origin === location.origin && PATTERN.test(u.pathname) ? u : null;
        } catch (e) {
            return null;
        }
    }

    document.addEventListener('click', function (e) {
        var a = e.target.closest ? e.target.closest('a[href]') : null;
        if (!a || e.defaultPrevented || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
        if (overlay && overlay.contains(a)) return; // tombol Download / Buka di tab baru di viewer
        var u = isExport(a.getAttribute('href'));
        if (!u) return;
        e.preventDefault();
        run(u.href, !/excel/.test(u.pathname), a);
    }, true);

    window.mktDownload = function (url, el) {
        var u = isExport(url);
        if (!u) { window.location.href = url; return; }
        run(u.href, !/excel/.test(u.pathname), el || null);
    };
})();
