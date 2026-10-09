<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudflare Turnstile
    |--------------------------------------------------------------------------
    |
    | Isi kedua kunci di .env (buat site di dash.cloudflare.com → Turnstile):
    | TURNSTILE_SITE_KEY dipakai widget di form, TURNSTILE_SECRET_KEY dipakai
    | verifikasi server. Verifikasi aktif hanya bila secret key terisi.
    |
    */

    'site_key' => env('TURNSTILE_SITE_KEY'),

    'secret_key' => env('TURNSTILE_SECRET_KEY'),

];
