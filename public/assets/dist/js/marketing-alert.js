/* Alert modul Marketing (acuan: Alert Marketing NDS.html, gaya "N").
   - Katalog teks: window.MKT_ALERTS[kode]. Teks persis katalog; bagian {nama} diisi data asli.
   - mktAlert(kode, data, opsi)  -> tampilkan alert sesuai bentuknya (toast / dialog). Dialog mengembalikan Promise Swal.
   - mktToast(jenis, judul, pesan, opsi) -> toast bebas (jenis: ok | info | warn | err).
   - Semua Swal.fire di halaman marketing otomatis memakai gaya ini: ikon bulat gradien, judul Poppins navy,
     tombol pill, Cancel / Back di kiri dan aksi di kanan. */
(function (window, document) {
    'use strict';

    var IC = {
        ok: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path pathLength="1" d="M5 12.5l4.5 4.5L19 7.5"/></svg>',
        info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path pathLength="1" d="M12 11v7"/><path pathLength="1" d="M12 6.5v.01"/></svg>',
        warn: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path pathLength="1" d="M12 5.5v8"/><path pathLength="1" d="M12 17.8v.01"/></svg>',
        err: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path pathLength="1" d="M7 7l10 10"/><path pathLength="1" d="M17 7 7 17"/></svg>'
    };
    // Ikon SweetAlert -> jenis katalog. question = konfirmasi = info.
    var KIND = { success: 'ok', info: 'info', question: 'info', warning: 'warn', error: 'err' };

    /* Katalog pesan. j: ok | info | warn | err. f: toast | banner | field | dialog.
       btn: [[teks, gaya]] untuk dialog, tombol pertama = kiri (Cancel / Back / Tutup), terakhir = aksi. a: aksi tunggal toast. */
    var A = {
        'CST-01': { j: 'ok', f: 'toast', h: 'Costing tersimpan', m: '{costing} sudah diajukan ke Marketing Manager.', a: 'Lihat' },
        'CST-02': { j: 'ok', f: 'toast', h: 'Costing disetujui', m: '{costing} disetujui Marketing Manager. SO sudah bisa dibuat.', a: 'Buat SO' },
        'CST-03': { j: 'warn', f: 'banner', h: 'Costing dikembalikan', m: '{costing} belum disetujui. Catatan Manager: {note}. Perbaiki lalu ajukan lagi.' },
        'CST-04': { j: 'info', f: 'toast', h: 'Costing belum disetujui', m: 'SO baru bisa dibuat setelah Marketing Manager menyetujui {costing}.' },
        'SO-01': { j: 'info', f: 'banner', h: 'Costing ini sudah punya {count} SO', m: 'SO baru akan jadi SO ke-{next} dari {costing}.' },
        'SO-02': { j: 'ok', f: 'toast', h: 'Sales Order tersimpan', m: '{so} otomatis disetujui dan No WS {ws} dibuat sistem. BOM sudah bisa dibuat.', a: 'Buat BOM' },
        'SO-03': { j: 'err', f: 'banner', h: '{count} baris belum valid', m: '{detail} Perbaiki dulu, baru SO bisa disimpan.' },
        'SO-04': { j: 'info', f: 'toast', h: 'SO sampel tersimpan', m: 'Sampel barang jadi selesai di SO, tanpa BOM dan tanpa PO material.' },
        'BOM-01': { j: 'ok', f: 'toast', h: 'BOM berhasil disalin', m: '{count} baris material dari {from} masuk ke {to}. Warna dan size mengikuti SO ini, cek lagi sebelum diajukan.' },
        'BOM-02': { j: 'err', f: 'dialog', h: 'No WS ini sudah punya BOM', m: '{ws} hanya boleh punya satu BOM. Buka BOM yang ada untuk mengubahnya.', btn: [['Tutup', ''], ['Buka BOM', 'pri']] },
        'BOM-03': { j: 'err', f: 'field', h: 'Warna tidak ada di SO', m: '{color} tidak ada di {so}. Pilih warna dari SO: {colors}.' },
        'BOM-04': { j: 'info', f: 'toast', h: 'BOM menunggu persetujuan', m: 'Worksheet bisa dibuat setelah Marketing Manager menyetujui BOM ini.' },
        'BOM-05': { j: 'ok', f: 'toast', h: 'BOM disetujui', m: 'Worksheet {ws} sudah bisa dibuat. No WS jadi acuan Purchasing untuk PO material.', a: 'Buat Worksheet' },
        'BOM-06': { j: 'warn', f: 'banner', h: 'BOM dikembalikan', m: 'BOM {ws} belum disetujui. Perbaiki datanya lalu ajukan lagi.' },
        'WS-01': { j: 'ok', f: 'toast', h: 'Worksheet tersimpan', m: '{ws} tersimpan. Semua tahap untuk {so} sudah lengkap.', a: 'Unduh PDF' },
        'WS-02': { j: 'info', f: 'toast', h: 'BOM belum disetujui', m: 'Worksheet baru bisa dibuat setelah BOM untuk {so} disetujui.' },
        'REV-01': { j: 'info', f: 'dialog', h: 'Simpan perubahan ini?', m: 'Costing dan SO langsung berlaku tanpa approval. BOM {ws} perlu disetujui ulang oleh Manager.', btn: [['Cancel', ''], ['Save', 'pri']] },
        'REV-02': { j: 'err', f: 'dialog', h: 'Perubahan ditolak', m: '{detail}', btn: [['Tutup', 'pri']] },
        'REV-03': { j: 'warn', f: 'dialog', h: 'Perubahan ini perlu persetujuan Manager', m: '{so} sudah dipakai Cutting dan PO material. Produksi tetap memakai data lama sampai revisi disetujui.', btn: [['Cancel', ''], ['Save', 'pri']] },
        'REV-04': { j: 'ok', f: 'toast', h: 'Revisi berlaku', m: 'Revisi {so} disetujui, BOM ikut disetujui. PPIC, Purchasing, Gudang, dan Accounting sudah dapat notifikasi.' },
        'REV-05': { j: 'info', f: 'toast', h: 'Revisi dibatalkan', m: 'Revisi {so} tidak disetujui. Data lama tetap dipakai.' },
        'UM-01': { j: 'info', f: 'toast', h: 'Hanya Marketing Manager yang bisa menyetujui', m: 'Kamu bisa melihat dokumen ini, tapi tombol Approve tidak aktif untuk peranmu.' },
        'SV-01': { j: 'info', f: 'dialog', h: 'Simpan data ini?', m: '{subject} akan disimpan{detail}.', btn: [['Cancel', ''], ['Save', 'pri']] },
        'SV-02': { j: 'info', f: 'dialog', h: 'Simpan dan ajukan costing?', m: '{costing} akan dikirim ke Marketing Manager untuk disetujui.', btn: [['Cancel', ''], ['Save', 'pri']] },
        'SV-03': { j: 'info', f: 'dialog', h: 'Simpan Sales Order ini?', m: 'SO untuk {costing}: {dest} destination, {po} PO, total {qty} pcs. SO langsung disetujui sistem dan No WS dibuat otomatis.', btn: [['Cancel', ''], ['Save', 'pri']] },
        'SV-04': { j: 'info', f: 'dialog', h: 'Ajukan BOM ke Manager?', m: '{count} baris material untuk {ws}. Worksheet bisa dibuat setelah Manager menyetujui.', btn: [['Cancel', ''], ['Save', 'pri']] },
        'SV-05': { j: 'info', f: 'dialog', h: 'Simpan Worksheet ini?', m: '{ws} adalah tahap terakhir untuk {so}.', btn: [['Cancel', ''], ['Save', 'pri']] },
        'SV-06': { j: 'ok', f: 'toast', h: 'Data tersimpan', m: '{subject} berhasil disimpan.' },
        'UPD-01': { j: 'info', f: 'dialog', h: 'Simpan perubahan ini?', m: '{subject}: {detail} diubah.', btn: [['Cancel', ''], ['Save', 'pri']] },
        'UPD-02': { j: 'ok', f: 'toast', h: 'Perubahan tersimpan', m: 'Data {subject} sudah diperbarui.' },
        'DEL-01': { j: 'warn', f: 'dialog', h: 'Hapus item ini?', m: 'Item {item} tidak akan muncul lagi di pilihan BOM baru. BOM lama yang sudah memakainya tidak berubah.', btn: [['Cancel', ''], ['Delete', 'danger']] },
        'DEL-02': { j: 'ok', f: 'toast', h: 'Item dihapus', m: '{item} sudah dihapus dari master item.' },
        'DEL-03': { j: 'warn', f: 'dialog', h: 'Hapus baris ini?', m: '{row} akan dihapus dari {doc}.', btn: [['Cancel', ''], ['Delete', 'danger']] },
        'DEL-04': { j: 'err', f: 'dialog', h: 'Tidak bisa dihapus', m: '{detail}', btn: [['Tutup', 'pri']] },
        'APR-01': { j: 'info', f: 'toast', h: 'Belum ada yang dipilih', m: 'Centang minimal satu baris dulu, baru klik Approve, Return to Staff, atau Cancel.' },
        'APR-02': { j: 'info', f: 'dialog', h: 'Approve {count} costing?', m: '{docs}. Setelah disetujui, Sales Order untuk {them} bisa dibuat.', btn: [['Back', ''], ['Approve', 'go']] },
        'APR-03': { j: 'info', f: 'dialog', h: 'Approve {count} BOM?', m: '{docs}. Setelah disetujui, Worksheet bisa dibuat dan No WS jadi acuan Purchasing.', btn: [['Back', ''], ['Approve', 'go']] },
        'APR-04': { j: 'warn', f: 'dialog', h: '{skipped} dari {total} baris dilewati', m: 'Hanya baris berstatus Awaiting Approval yang bisa diproses. {count} {type} akan di-approve, {skipped} dilewati karena sudah Approved atau Cancel.', btn: [['Back', ''], ['Approve {count}', 'go']] },
        'APR-05': { j: 'ok', f: 'toast', h: '{count} {type} disetujui', m: 'Admin Marketing sudah bisa membuat Sales Order untuk {them}.' },
        'APR-06': { j: 'warn', f: 'dialog', h: 'Return {count} {type} ke Staff?', m: '{Them} dikembalikan ke Admin Marketing bersama catatan kamu, supaya jelas apa yang harus diperbaiki.', btn: [['Back', ''], ['Return to Staff', 'pri']] },
        'APR-07': { j: 'err', f: 'field', h: 'Catatan belum diisi', m: 'Tulis alasan kenapa dokumen ini dikembalikan.' },
        'APR-08': { j: 'info', f: 'toast', h: '{count} {type} dikembalikan ke Staff', m: 'Statusnya sekarang Return to Staff. Admin Marketing bisa melihat catatan kamu.' },
        'APR-09': { j: 'err', f: 'dialog', h: 'Cancel {count} {type}?', m: 'Status {docs} berubah jadi Cancel.', btn: [['Back', ''], ['Cancel', 'danger']] },
        'APR-10': { j: 'info', f: 'toast', h: '{count} {type} di-cancel', m: '{Them} pindah ke tab Cancel.' },
        'UNL-01': { j: 'info', f: 'dialog', h: 'Unlock {count} costing?', m: '{Them} costing bisa diedit lagi oleh Admin Marketing. Setelah diubah, costing otomatis approved.', btn: [['Back', ''], ['Unlock', 'go']] },
        'UNL-02': { j: 'info', f: 'dialog', h: 'Unlock {count} SO?', m: '{Them} SO bisa diedit lagi. Setelah diubah, SO otomatis approved, tapi BOM-nya perlu disetujui ulang.', btn: [['Back', ''], ['Unlock', 'go']] },
        'UNL-03': { j: 'info', f: 'dialog', h: 'Unlock {count} BOM?', m: '{Them} BOM bisa diedit lagi. Setelah diubah, BOM harus diajukan dan disetujui ulang.', btn: [['Back', ''], ['Unlock', 'go']] },
        'UNL-04': { j: 'ok', f: 'toast', h: '{count} dokumen di-unlock', m: 'Admin Marketing sudah bisa mengedit {them}.' },
        'UNL-05': { j: 'warn', f: 'dialog', h: 'Return {count} permintaan unlock?', m: '{Them} dokumen tetap terkunci. Catatan kamu dikirim ke Admin Marketing.', btn: [['Back', ''], ['Return to Staff', 'pri']] },
        'UNL-06': { j: 'info', f: 'toast', h: '{count} permintaan unlock dikembalikan', m: 'Dokumennya tetap terkunci dan tidak berubah.' }
    };

    // "ketiganya", "keduanya", ... sesuai katalog; 1 dokumen = "dokumen ini".
    var KATA = { 1: 'dokumen ini', 2: 'keduanya', 3: 'ketiganya', 4: 'keempatnya', 5: 'kelimanya', 6: 'keenamnya', 7: 'ketujuhnya', 8: 'kedelapannya', 9: 'kesembilannya', 10: 'kesepuluhnya' };
    var KATA_DOK = { 1: 'Dokumen ini', 2: 'Kedua', 3: 'Ketiga', 4: 'Keempat', 5: 'Kelima', 6: 'Keenam', 7: 'Ketujuh', 8: 'Kedelapan', 9: 'Kesembilan', 10: 'Kesepuluh' };
    function them(n) { return KATA[n] || ('semua ' + n); }

    function esc(v) {
        return String(v === null || v === undefined ? '' : v).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    // Daftar nomor dokumen gaya katalog: "A, B, dan C" / "A dan B".
    function list(items) {
        items = (items || []).filter(Boolean);
        if (items.length <= 1) return items.join('');
        if (items.length === 2) return items[0] + ' dan ' + items[1];
        return items.slice(0, -1).join(', ') + ', dan ' + items[items.length - 1];
    }
    function fill(text, data) {
        data = data || {};
        var n = Number(data.count);
        var ctx = Object.assign({}, data);
        if (ctx.them === undefined && n) ctx.them = them(n);
        // "{Them} costing" -> "Ketiga costing", "{Them} dikembalikan" -> "Ketiganya dikembalikan"
        return String(text).replace(/\{Them\}(\s+(costing|SO|BOM|dokumen))?/g, function (all, sp, noun) {
            if (noun) return n === 1 ? noun.charAt(0).toUpperCase() + noun.slice(1) + ' ini' : (KATA_DOK[n] || ('Semua ' + n)) + ' ' + noun;
            var w = ctx.them || them(n);
            return w.charAt(0).toUpperCase() + w.slice(1);
        }).replace(/\{(\w+)\}/g, function (all, k) {
            var v = ctx[k];
            if (Array.isArray(v)) v = list(v);
            return v === undefined || v === null ? '' : v;
        });
    }

    /* ---------- toast ---------- */
    function toastBox() {
        var box = document.getElementById('mkt-toasts');
        if (!box) {
            box = document.createElement('div');
            box.id = 'mkt-toasts';
            box.className = 'mkt-toasts';
            box.setAttribute('aria-live', 'polite');
            document.body.appendChild(box);
        }
        return box;
    }
    function mktToast(kind, title, message, opts) {
        opts = opts || {};
        kind = KIND[kind] || kind || 'info';
        var el = document.createElement('div');
        el.className = 'mkt-toast mkt-al-' + kind;
        el.setAttribute('role', kind === 'err' ? 'alert' : 'status');
        el.innerHTML = '<span class="mkt-toast-ic">' + IC[kind] + '</span><div class="mkt-toast-tx"><b>' + esc(title) + '</b>'
            + (message ? '<span>' + esc(message) + '</span>' : '')
            + (opts.action ? '<div class="mkt-toast-act"><button class="mkt-al-btn" type="button">' + esc(opts.action) + '</button></div>' : '')
            + '</div><button class="mkt-toast-x" type="button" aria-label="Tutup">×</button>';
        var kill = function () {
            if (el.classList.contains('out')) return;
            el.classList.add('out');
            setTimeout(function () { el.remove(); }, 170);
        };
        el.querySelector('.mkt-toast-x').addEventListener('click', kill);
        var act = el.querySelector('.mkt-toast-act button');
        if (act) act.addEventListener('click', function () { kill(); if (opts.onAction) opts.onAction(); });
        var box = toastBox();
        box.insertBefore(el, box.firstChild);
        // Sukses & info hilang sendiri (4 detik, 7 detik kalau ada aksi); peringatan & gagal menunggu ditutup,
        // kecuali diberi opts.duration (ms), mis. toast cons BOM 5 detik.
        if (kind === 'ok' || kind === 'info' || opts.duration) {
            var left = opts.duration || (opts.action ? 7000 : 4000), t0 = Date.now(), tm = setTimeout(kill, left);
            el.classList.add('auto');
            el.style.setProperty('--t', left + 'ms');
            el.addEventListener('mouseenter', function () { clearTimeout(tm); left -= Date.now() - t0; });
            el.addEventListener('mouseleave', function () { t0 = Date.now(); tm = setTimeout(kill, left); });
        }
        while (box.children.length > 3) box.lastChild.remove();
        return el;
    }

    /* ---------- banner: menempel di atas form / tabel, tidak hilang sendiri ----------
       mktBanner(target, jenis, judul, pesan) -> target = elemen / selector tempat banner dipasang (paling atas).
       Tanpa target -> kembalikan HTML-nya (untuk disisipkan ke dalam modal). Hapus dengan mktBanner.clear(target). */
    function bannerHtml(kind, title, message, opts) {
        return '<div class="mkt-banner mkt-al-' + kind + '" role="' + (kind === 'err' ? 'alert' : 'status') + '">'
            + '<span class="mkt-toast-ic">' + IC[kind] + '</span><div class="mkt-toast-tx"><b>' + esc(title) + '</b>'
            + (message ? '<span>' + (opts && opts.html ? message : esc(message)) + '</span>' : '')
            + (opts && opts.action ? '<div class="mkt-toast-act"><button class="mkt-al-btn" type="button">' + esc(opts.action) + '</button></div>' : '')
            + '</div></div>';
    }
    function mktBanner(target, kind, title, message, opts) {
        opts = opts || {};
        kind = KIND[kind] || kind || 'info';
        var html = bannerHtml(kind, title, message, opts);
        var host = typeof target === 'string' ? document.querySelector(target) : (target && target.jquery ? target[0] : target);
        if (!host) return html;
        mktBanner.clear(host);
        host.insertAdjacentHTML('afterbegin', html);
        var el = host.firstElementChild;
        var act = el.querySelector('.mkt-toast-act button');
        if (act && opts.onAction) act.addEventListener('click', opts.onAction);
        return el;
    }
    mktBanner.clear = function (target) {
        var host = typeof target === 'string' ? document.querySelector(target) : (target && target.jquery ? target[0] : target);
        if (host) Array.prototype.forEach.call(host.querySelectorAll(':scope > .mkt-banner'), function (b) { b.remove(); });
    };

    /* ---------- dialog: pembungkus Swal.fire ---------- */
    function btnClass(text, kind) {
        if (/^(delete|hapus|cancel)\b/i.test(text) && kind !== 'info') return 'is-danger';
        if (/^(approve|unlock)\b/i.test(text)) return 'is-go';
        return 'is-pri';
    }
    function cls(v) { return v ? String(v) : ''; }

    function style(opts) {
        if (!opts || typeof opts !== 'object') return opts;
        if (opts.toast) return opts;
        var o = Object.assign({}, opts);
        var cc = Object.assign({}, o.customClass || {});
        // Kelas gaya lama (mkt-plain-*) diganti gaya ini.
        Object.keys(cc).forEach(function (k) { cc[k] = cls(cc[k]).replace(/\bmkt-plain-[\w-]+/g, '').trim(); });
        var kind = KIND[o.icon];
        if (kind) {
            if (!o.iconHtml) o.iconHtml = IC[kind];
            cc.icon = (cls(cc.icon) + ' mkt-al-ic mkt-al-' + kind).trim();
        }
        cc.popup = (cls(cc.popup) + ' mkt-al').trim();
        cc.container = (cls(cc.container) + ' mkt-al-back').trim();
        var confirmText = o.confirmButtonText || (o.showCancelButton ? 'Save' : 'Tutup');
        o.confirmButtonText = confirmText;
        cc.confirmButton = (cls(cc.confirmButton) + ' mkt-al-btn ' + btnClass(String(confirmText), kind)).trim();
        cc.denyButton = (cls(cc.denyButton) + ' mkt-al-btn').trim();
        cc.cancelButton = (cls(cc.cancelButton) + ' mkt-al-btn').trim();
        if (o.showCancelButton && !o.cancelButtonText) o.cancelButtonText = 'Cancel';
        cc.actions = cls(cc.actions);
        o.customClass = cc;
        o.buttonsStyling = false;
        o.reverseButtons = true; // Cancel / Back di kiri, aksi di kanan.
        if (!o.showClass) o.showClass = { popup: 'mkt-al-in', backdrop: 'swal2-backdrop-show' };
        if (!o.hideClass) o.hideClass = { popup: 'mkt-al-out', backdrop: 'swal2-backdrop-hide' };
        return o;
    }

    function wrapSwal() {
        var Swal = window.Swal;
        if (!Swal || Swal.__mktAlert) return;
        var fire = Swal.fire;
        Swal.fire = function (a, b, c) {
            var opts = typeof a === 'object' && a !== null ? a : { title: a, html: b, icon: c };
            return fire.call(this, style(opts));
        };
        Swal.__mktAlert = true;
    }

    /* mktAlert('SV-02', {costing: 'CST/1126/00238'}, {preConfirm: ...}) */
    function mktAlert(code, data, opts) {
        var a = A[code];
        if (!a) throw new Error('Kode alert tidak dikenal: ' + code);
        opts = opts || {};
        var title = fill(a.h, data), message = fill(a.m, data);
        if (a.f === 'banner') {
            return mktBanner(opts.target, a.j, title, message, { action: opts.action || (opts.onAction ? a.a : null), onAction: opts.onAction, html: opts.html });
        }
        if (a.f === 'toast') {
            return mktToast(a.j, title, message, { action: opts.action === false ? null : (opts.action || (opts.onAction ? a.a : null)), onAction: opts.onAction });
        }
        if (a.f === 'field') {
            return { title: title, message: message };
        }
        var btn = (a.btn || [['Tutup', 'pri']]).map(function (b) { return [fill(b[0], data), b[1]]; });
        var two = btn.length > 1;
        var icon = { ok: 'success', info: 'info', warn: 'warning', err: 'error' }[a.j];
        return window.Swal.fire(Object.assign({
            icon: icon,
            title: esc(title),
            text: message,
            showCancelButton: two,
            cancelButtonText: two ? btn[0][0] : undefined,
            confirmButtonText: btn[btn.length - 1][0],
            focusCancel: two
        }, opts));
    }

    /* Pesan di field (bentuk "Di field"): tampil di bawah input, input ditandai merah, kursor pindah ke sana. */
    function mktFieldError(input, code, data) {
        var $ = window.jQuery;
        var el = $ ? $(input)[0] : input;
        if (!el) return;
        var a = A[code];
        var text = a ? fill(a.m, data) : String(code);
        var id = (el.id || 'mkt-field') + '-msg';
        var msg = document.getElementById(id);
        if (!msg) {
            msg = document.createElement('small');
            msg.id = id;
            msg.className = 'mkt-field-msg';
            var next = el.nextElementSibling;
            (next && next.classList.contains('select2') ? next : el).insertAdjacentElement('afterend', msg);
        }
        msg.textContent = text;
        el.classList.add('is-invalid');
        el.setAttribute('aria-invalid', 'true');
        el.setAttribute('aria-describedby', id);
        el.focus();
        var clear = function () { el.classList.remove('is-invalid'); el.removeAttribute('aria-invalid'); msg.remove(); el.removeEventListener('input', clear); };
        el.addEventListener('input', clear);
    }

    // iziToast lama di halaman marketing ikut tampil sebagai toast gaya ini.
    function wrapIzi() {
        var izi = window.iziToast;
        if (!izi || izi.__mktAlert) return;
        var map = { success: 'ok', info: 'info', warning: 'warn', error: 'err' };
        Object.keys(map).forEach(function (k) {
            izi[k] = function (o) { o = o || {}; mktToast(map[k], o.title || o.message || '', o.title ? o.message : ''); };
        });
        izi.__mktAlert = true;
    }

    wrapSwal();
    wrapIzi();
    window.MKT_ALERTS = A;
    window.mktAlert = mktAlert;
    window.mktToast = mktToast;
    window.mktBanner = mktBanner;
    window.mktFieldError = mktFieldError;
    window.mktAlertText = function (code, data) { var a = A[code]; return a ? { title: fill(a.h, data), message: fill(a.m, data) } : null; };
})(window, document);
