<?php

return [
    /*
    | Gambar Master Item General yang di-upload sebelum pindah ke NDS masih ada di folder upload
    | Signalbit. Isi dengan alamat folder itu (diakhiri '/') supaya gambar lama tetap bisa dilihat
    | dari NDS, mis. http://host-signalbit/signalbit_erp/pages/others/upload_files/
    | Kosong = gambar lama ditampilkan sebagai "Image file not found". Gambar baru selalu di storage NDS.
    */
    'item_gambar_lama_url' => env('MASTER_ITEM_GAMBAR_LAMA_URL'),
];
