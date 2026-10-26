/* Title Case untuk teks tampilan Marketing (nama buyer, supplier, brand, item, dll).
   - Singkatan badan usaha / negara / dokumen tetap kapital: PT, CV, LTD, USA, ...
   - Kata berangka dibiarkan apa adanya: 100%, 150GSM, 70-72", 2PCS
   - "PT.EIGERINDO" -> "PT. Eigerindo"
   Hanya untuk tampilan; data asli (value, data-*, yang dikirim ke server) tidak diubah. */
(function (window) {
    'use strict';

    var KEEP_UPPER = [
        // badan usaha
        'PT', 'CV', 'UD', 'TBK', 'PD', 'LTD', 'CO', 'INC', 'LLC', 'LLP', 'PLC', 'CORP', 'PTE', 'SDN', 'BHD', 'GMBH', 'AG', 'SA', 'SRL', 'SPA', 'BV', 'NV', 'KK', 'JSC', 'JV',
        // negara / wilayah
        'USA', 'US', 'UK', 'UAE', 'EU', 'HK', 'PRC', 'RRC', 'RI', 'KSA',
        // dokumen / istilah sistem
        'PO', 'SO', 'WS', 'BOM', 'JO', 'QC', 'QA', 'FOB', 'CMT', 'CIF', 'EXW', 'DDP', 'IDR', 'USD', 'SMV',
        // kode musim
        'FW', 'SS', 'AW', 'SP', 'HO',
        // angka romawi (II ke atas; I/V/X tunggal dianggap kata biasa)
        'II', 'III', 'IV', 'VI', 'VII', 'VIII', 'IX', 'XI', 'XII'
    ];
    var KEEP = {};
    KEEP_UPPER.forEach(function (w) { KEEP[w] = true; });

    function isKept(part) {
        var core = part.replace(/[^A-Za-z]/g, '').toUpperCase();
        if (core === '') return false;
        // Daftar singkatan, atau kode 2-5 huruf tanpa vokal (NPD, SHP, PCS, KGM, YDS) -> tetap kapital
        return KEEP[core] === true || /^[BCDFGHJKLMNPQRSTVWXZ]{2,5}$/.test(core);
    }

    // Satu bagian kata (tanpa pemisah - / ( & .)
    function casePart(part) {
        if (part === '') return part;
        if (/\d/.test(part)) return part;          // 100%, 150GSM, 2PCS
        if (isKept(part)) return part.toUpperCase();
        var lower = part.toLowerCase();
        var i = lower.search(/[a-z]/);
        return i === -1 ? lower : lower.slice(0, i) + lower.charAt(i).toUpperCase() + lower.slice(i + 1);
    }

    function caseWord(word) {
        if (/\d/.test(word)) return word;
        // Pisah di - / ( ) & . supaya "T-SHIRT" -> "T-Shirt", "CO.,LTD" -> "CO.,LTD"; apostrof tidak (MEN'S -> Men's)
        return word.split(/([\-\/()&.,])/).map(function (part, index) {
            return index % 2 === 1 ? part : casePart(part);
        }).join('');
    }

    function titleCase(text) {
        if (text === null || text === undefined) return '';
        var s = String(text);
        if (s.trim() === '') return s;
        // "PT.EIGERINDO" / "CV.MODESALE" -> beri spasi setelah titik singkatan badan usaha
        s = s.replace(/\b(PT|CV|UD|PD)\.(?=[A-Za-z])/gi, '$1. ');
        return s.split(/(\s+)/).map(function (token, index) {
            return index % 2 === 1 ? token : caseWord(token);
        }).join('');
    }

    // Untuk sel DataTables yang datanya sudah di-escape server (H&amp;M): decode -> Title Case -> escape lagi.
    function titleCaseHtml(html) {
        var box = document.createElement('textarea');
        box.innerHTML = String(html === null || html === undefined ? '' : html);
        var div = document.createElement('div');
        div.textContent = titleCase(box.value);
        return div.innerHTML;
    }

    // Select2: hanya teks yang tampil di dropdown & pilihan yang di-Title Case; <option> asli tetap.
    function select2Options() {
        var render = function (item) {
            if (!item || item.loading || item.id === '' || item.id === undefined) return item ? item.text : '';
            return titleCase(item.text);
        };
        return { templateResult: render, templateSelection: render };
    }

    window.mktTitleCase = titleCase;
    window.mktTitleCaseHtml = titleCaseHtml;
    window.mktTitleCaseSelect2 = select2Options;
})(window);
