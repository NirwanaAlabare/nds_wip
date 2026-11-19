/* User Guide marketing: tombol [data-guide-open="ID"] membuka modal #ID (marketing/partials/user_guide.blade.php).
   Navigasi: klik daftar langkah, tombol Sebelumnya / Berikutnya, panah kiri/kanan; Esc / klik latar menutup. */
(function () {
    function setup(box) {
        var steps = Array.prototype.slice.call(box.querySelectorAll('[data-guide-step]'));
        var navs = Array.prototype.slice.call(box.querySelectorAll('[data-guide-go]'));
        var prev = box.querySelector('[data-guide-prev]'), next = box.querySelector('[data-guide-next]');
        var count = box.querySelector('[data-guide-count]'), body = box.querySelector('.mkt-guide-body');
        var cur = 0;
        function show(i) {
            cur = Math.max(0, Math.min(steps.length - 1, i));
            steps.forEach(function (s, k) { s.hidden = k !== cur; });
            navs.forEach(function (b, k) { b.classList.toggle('on', k === cur); b.classList.toggle('done', k < cur); });
            prev.disabled = cur === 0;
            next.innerHTML = cur === steps.length - 1 ? 'Selesai &#10003;' : 'Berikutnya &rarr;';
            count.textContent = 'Langkah ' + (cur + 1) + ' dari ' + steps.length;
            body.scrollTop = 0;
        }
        navs.forEach(function (b) { b.addEventListener('click', function () { show(+b.dataset.guideGo); }); });
        prev.addEventListener('click', function () { show(cur - 1); });
        next.addEventListener('click', function () { if (cur === steps.length - 1) close(); else show(cur + 1); });
        // Tema modal ikut pilihan user (localStorage nds-theme, sama dengan navbar), walau halamannya belum mendukung tema gelap.
        function theme() {
            var t = document.documentElement.getAttribute('data-theme');
            if (t !== 'dark' && t !== 'light') { try { t = localStorage.getItem('nds-theme'); } catch (e) {} }
            if (t !== 'dark' && t !== 'light') t = window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            box.setAttribute('data-g-theme', t);
        }
        function open() { box.hidden = false; theme(); document.body.style.overflow = 'hidden'; show(0); next.focus(); }
        function close() { box.hidden = true; document.body.style.overflow = ''; }
        box.addEventListener('click', function (e) { if (e.target === box || e.target.closest('[data-guide-close]')) close(); });
        document.addEventListener('keydown', function (e) {
            if (box.hidden) return;
            if (e.key === 'Escape') close();
            else if (e.key === 'ArrowRight') show(cur + 1);
            else if (e.key === 'ArrowLeft') show(cur - 1);
        });
        box.mktGuideOpen = open;
    }
    function init() {
        Array.prototype.forEach.call(document.querySelectorAll('.mkt-guide'), setup);
        document.addEventListener('click', function (e) {
            var b = e.target.closest('[data-guide-open]');
            if (!b) return;
            var box = document.getElementById(b.dataset.guideOpen);
            if (box && box.mktGuideOpen) { e.preventDefault(); box.mktGuideOpen(); }
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
