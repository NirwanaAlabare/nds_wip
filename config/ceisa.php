<?php

return [
    // === DEV ===
    'base_url_dev'      => env('CEISA_BASE_URL_DEV', 'https://apisdev-gw.beacukai.go.id'),
    'username_dev'      => env('CEISA_USERNAME_DEV'),
    'password_dev'      => env('CEISA_PASSWORD_DEV'),
    'id_platform_dev'   => env('CEISA_ID_PLATFORM_DEV', ''),
    'id_perusahaan_dev' => env('CEISA_ID_PERUSAHAAN_DEV', ''),
    'api_key_dev'       => env('CEISA_API_KEY_DEV', ''),

    // === LIVE / PRODUCTION ===
    'base_url_live'      => env('CEISA_BASE_URL', 'https://apis-gw.beacukai.go.id'),
    'username_live'      => env('CEISA_USERNAME'),
    'password_live'      => env('CEISA_PASSWORD'),
    'id_platform_live'   => env('CEISA_ID_PLATFORM', ''),
    'id_perusahaan_live' => env('CEISA_ID_PERUSAHAAN', ''),
    'api_key_live'       => env('CEISA_API_KEY', ''),
];
