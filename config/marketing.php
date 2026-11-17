<?php

return [
    /*
    | Percobaan DB baru marketing. true = Costing yang disimpan ikut ditulis ke koneksi mysql_mkt
    | dan listing Costing dibaca dari sana. Signalbit tetap ditulis supaya produksi berjalan.
    */
    'nds_trial' => env('MARKETING_NDS_TRIAL', false),
];
