// Template tabel kosong halaman marketing: ilustrasi folder + kaca pembesar dan teks "No Data Available In Table".
// Gaya .mkt-empty-state ada di marketing-buttons.css. Muat setelah DataTables supaya default emptyTable/zeroRecords ikut terpasang.
(function (window, $) {
    'use strict';

    var SVG = '<svg viewBox="0 0 120 80" width="120" height="80" aria-hidden="true">'
        + '<ellipse cx="60" cy="74" rx="44" ry="3" fill="#e2e8f0"/>'
        + '<path d="M20 22h26l6 7h38a4 4 0 0 1 4 4v37H20z" fill="#9ec3ea"/>'
        + '<path d="M16 36h80l-6 36H22z" fill="#2f6fc6"/>'
        + '<circle cx="70" cy="18" r="11" fill="#fff" stroke="#9aa9bb" stroke-width="3"/>'
        + '<path d="M78 26l10 10" stroke="#475569" stroke-width="4" stroke-linecap="round"/>'
        + '<circle cx="92" cy="30" r="4" fill="#334155"/>'
        + '<path d="M92 34l-2 22M92 40l-8-6M90 56l-4 16M90 56l6 16" stroke="#334155" stroke-width="3" stroke-linecap="round"/>'
        + '</svg>';

    function emptyHtml() {
        return '<div class="mkt-empty-state">' + SVG + '<span>No Data Available In Table</span></div>';
    }

    window.mktEmptyHtml = emptyHtml;
    window.mktEmptyRow = function (colspan) {
        return '<tr class="mkt-empty-row"><td colspan="' + colspan + '">' + emptyHtml() + '</td></tr>';
    };

    if ($ && $.fn && $.fn.dataTable) {
        $.extend(true, $.fn.dataTable.defaults, { language: { emptyTable: emptyHtml(), zeroRecords: emptyHtml() } });
    }
})(window, window.jQuery);
