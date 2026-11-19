{{--
    Mesin panduan halaman : modal panduan + sorotan elemen asli + tur langkah demi langkah.
    Pola sama dengan panduan di cutting/penerimaan-cutting/create-penerimaan-cutting.

    Pemakaian :
        const guide = initPageGuide({
            modal: '#pgGuide',                  // modal panduan
            button: '#pg-guide-btn',            // tombol "Panduan"
            tourKey: 'kunci_unik_{{ Auth::id() }}', // tur otomatis sekali per user per browser
            steps: [ { sel, sec, lbl, d, missing? }, ... ],
        });
        guide.autoTour();   // panggil setelah halaman siap (data tabel sudah tampil)

    Step :
        sel     = selector elemen yang disorot (elemen pertama yang terlihat)
        sec     = id section di modal panduan (tanpa #)
        lbl     = judul langkah
        d       = penjelasan (HTML)
        missing = (opsional) penjelasan bila elemennya belum tampil, misal "Muncul setelah memilih stocker"
--}}
<script>
    function initPageGuide(cfg) {
        const gEl = document.querySelector(cfg.modal);

        if (!gEl) {
            return { autoTour: () => {}, open: () => {} };
        }

        const gBs = bootstrap.Modal.getOrCreateInstance(gEl);
        const STEPS = cfg.steps || [];
        const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        const seenTour = () => { try { return localStorage.getItem(cfg.tourKey) === '1'; } catch (e) { return true; } };
        const markSeen = () => { try { localStorage.setItem(cfg.tourKey, '1'); } catch (e) {} };

        // ===== Tur otomatis : tahan ketikan / scan sampai sorotan pertama muncul =====
        const waitId = 'pg-tour-wait';
        const tourWaiting = () => !!document.getElementById(waitId);
        const hideTourWait = () => { let w = document.getElementById(waitId); if (w) w.remove(); };

        if (!seenTour()) {
            let w = document.createElement('div');
            w.id = waitId;
            w.className = 'pg-tour-wait';
            w.innerHTML = '<div><span class="spinner-border spinner-border-sm"></span> Menyiapkan panduan halaman…</div>';
            document.body.appendChild(w);

            if (document.activeElement) {
                document.activeElement.blur();
            }

            window.addEventListener('keydown', (e) => { if (tourWaiting()) { e.preventDefault(); e.stopImmediatePropagation(); } }, true);
            setTimeout(hideTourWait, 15000); // pengaman bila ada yang macet
        }

        // ===== Modal panduan =====
        function gGo(id, smooth) {
            let t = gEl.querySelector(id);
            if (t) {
                t.scrollIntoView({ behavior: smooth ? 'smooth' : 'auto', block: 'start' });
            }
        }

        if (cfg.button && document.querySelector(cfg.button)) {
            document.querySelector(cfg.button).addEventListener('click', () => gBs.show());
        }

        // ===== Sorotan & tur =====
        let spotTok = 0, spotIdx = -1, tourFirst = false, autoTried = false;

        function visibleEl(sel) {
            return [...document.querySelectorAll(sel)].find((x) => x.offsetParent !== null && x.getBoundingClientRect().height > 0);
        }

        function spotClear() {
            spotTok++;
            document.querySelectorAll('.pg-spot, .pg-spot-dim, .pg-spot-l, .pg-spot-cv').forEach((x) => x.remove());
            removeEventListener('scroll', spotOff, true);
            removeEventListener('resize', spotOff);
        }

        // Tutup tur & kembali ke panduan di bagian langkah terakhir yang dilihat
        function spotOff() {
            hideTourWait();

            let st = STEPS[spotIdx], first = tourFirst;

            spotClear();
            spotIdx = -1;

            if (first) {
                tourFirst = false;
                markSeen();
            }

            gEl.addEventListener('shown.bs.modal', () => { if (first) gGo('#' + gEl.querySelector('section').id); else if (st) gGo('#' + st.sec); }, { once: true });
            gBs.show();
        }

        // Lewati tur : tutup tur tanpa membuka panduan (tur otomatis tetap ditandai sudah dilihat)
        function spotSkip() {
            hideTourWait();
            spotClear();
            spotIdx = -1;

            if (tourFirst) {
                tourFirst = false;
                markSeen();
            }
        }

        function spotGo(i) {
            if (i < 0 || i >= STEPS.length) {
                return;
            }

            spotClear();
            spotIdx = i;

            let st = STEPS[i], n = STEPS.length, tok = spotTok;
            let el = visibleEl(st.sel);

            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            // Tunggu scroll berhenti (posisi elemen stabil beberapa frame), baru pasang sorotan
            let last = null, still = 0, t0 = performance.now();
            (function wait() {
                if (tok !== spotTok) {
                    return;
                }

                if (!el) {
                    draw();
                    return;
                }

                let top = el.getBoundingClientRect().top;
                still = last !== null && Math.abs(top - last) < 0.5 ? still + 1 : 0;
                last = top;

                if (still >= 6 || performance.now() - t0 > 2000) {
                    draw();
                } else {
                    requestAnimationFrame(wait);
                }
            })();

            function draw() {
                let cv = document.createElement('div');
                cv.className = 'pg-spot-cv';
                cv.onclick = spotOff;

                let l = document.createElement('div');
                l.className = 'pg-spot-l';
                l.innerHTML = `
                    <div class="st"><span>Langkah ${i + 1} dari ${n}</span><span class="dots">${STEPS.map((_, k) => `<i class="${k === i ? 'on' : k < i ? 'done' : ''}"></i>`).join('')}</span></div>
                    <b>${esc(st.lbl)}</b><span class="d">${st.d}</span>
                    ${!el ? `<span class="m"><i class="fas fa-info-circle"></i> ${st.missing || 'Bagian ini belum tampil di halaman.'}</span>` : ''}
                    <div class="nav">
                        <button type="button" data-nav="prev" ${i ? '' : 'disabled'}>‹ Sebelumnya</button>
                        ${i < n - 1 ? `<button type="button" data-nav="skip" class="skip" title="Tutup tur tanpa membuka panduan">Lewati tur</button>` : ''}
                        <button type="button" data-nav="next" class="pri">${i === n - 1 ? 'Selesai ✓' : 'Berikutnya ›'}</button>
                    </div>
                    <small><kbd>←</kbd><kbd>→</kbd> pindah langkah · <kbd>Esc</kbd>, scroll, atau klik area gelap untuk kembali ke panduan</small>
                `;
                l.onclick = (e) => {
                    let b = e.target.closest('[data-nav]');
                    if (!b) {
                        return;
                    }

                    if (b.dataset.nav === 'skip') {
                        spotSkip();
                    } else if (b.dataset.nav === 'prev') {
                        spotGo(i - 1);
                    } else if (i === n - 1) {
                        spotOff();
                    } else {
                        spotGo(i + 1);
                    }
                };

                if (el) {
                    let r = el.getBoundingClientRect(), pad = 6;
                    let s = document.createElement('div');
                    s.className = 'pg-spot';
                    Object.assign(s.style, { left: r.left - pad + 'px', top: r.top - pad + 'px', width: r.width + pad * 2 + 'px', height: r.height + pad * 2 + 'px' });
                    document.body.append(cv, s, l);

                    let lh = l.offsetHeight, below = r.bottom + 16 + lh < innerHeight;
                    l.style.left = Math.max(8, Math.min(innerWidth - l.offsetWidth - 8, r.left)) + 'px';
                    l.style.top = (below ? r.bottom + 16 : Math.max(8, r.top - lh - 16)) + 'px';
                } else {
                    // Elemen belum tampil : tampilkan penjelasan di tengah layar
                    let dim = document.createElement('div');
                    dim.className = 'pg-spot-dim';
                    document.body.append(cv, dim, l);

                    l.style.left = Math.max(8, (innerWidth - l.offsetWidth) / 2) + 'px';
                    l.style.top = Math.max(8, (innerHeight - l.offsetHeight) / 2) + 'px';
                }

                hideTourWait();

                let nx = l.querySelector('[data-nav="next"]');
                if (nx) {
                    nx.focus({ preventScroll: true });
                }

                // Mulai dengarkan scroll user setelah sorotan tampil
                setTimeout(() => {
                    if (tok !== spotTok || !document.querySelector('.pg-spot-l')) {
                        return;
                    }

                    addEventListener('scroll', spotOff, true);
                    addEventListener('resize', spotOff);
                }, 250);
            }
        }

        // Klik di dalam panduan : nomor peta / navigasi = loncat ke bagian, kotak peta / tombol = sorot di halaman
        gEl.addEventListener('click', (e) => {
            let a = e.target.closest('.pg-map-n, .pg-g-nav a');
            if (a) {
                e.preventDefault();
                e.stopPropagation();
                gGo(a.getAttribute('href'), true);
                return;
            }

            let b = e.target.closest('[data-spot], [data-tour]');
            if (!b) {
                return;
            }

            let idx = b.dataset.tour !== undefined ? 0 : Math.max(0, STEPS.findIndex((x) => x.sec === b.dataset.spot));
            gEl.addEventListener('hidden.bs.modal', () => spotGo(idx), { once: true });
            gBs.hide();
        });

        document.addEventListener('keydown', (e) => {
            if (spotIdx < 0) {
                return;
            }

            if (e.key === 'Escape') {
                e.preventDefault();
                spotOff();
                return;
            }

            if (!document.querySelector('.pg-spot-l')) {
                return;
            }

            if (e.key === 'ArrowRight') {
                e.preventDefault();
                spotIdx === STEPS.length - 1 ? spotOff() : spotGo(spotIdx + 1);
            } else if (e.key === 'ArrowLeft') {
                e.preventDefault();
                spotGo(spotIdx - 1);
            }
        }, true);

        // Tur otomatis saat halaman pertama kali dibuka oleh user ini (per user per browser),
        // setelah tur selesai modal panduan dibuka
        function autoTour() {
            if (autoTried) {
                return;
            }

            autoTried = true;

            if (seenTour()) {
                hideTourWait();
                return;
            }

            setTimeout(() => {
                if (spotIdx < 0 && !document.querySelector('.modal.show')) {
                    tourFirst = true;
                    spotGo(0);
                } else {
                    hideTourWait();
                }
            }, 600);
        }

        return {
            autoTour: autoTour,
            open: () => gBs.show(),
            start: () => spotGo(0),
        };
    }
</script>
