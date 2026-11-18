<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Credenciales VAPID
    |--------------------------------------------------------------------------
    |
    | Publica y privada se usan para firmar y cifrar las notificaciones push.
    | Genera tus propias claves una sola vez:
    |
    |   docker exec laravel_bar_app php -r 'require "vendor/autoload.php";
    |       print_r(Minishlink\WebPush\VAPID::createVapidKeys());'
    |
    */

    'public_key' => env('VAPID_PUBLIC_KEY'),

    'private_key' => env('VAPID_PRIVATE_KEY'),

    'subject' => env('VAPID_SUBJECT', 'mailto:admin@casualred.com'),

    /*
    |--------------------------------------------------------------------------
    | Timeout (segundos) para los envios HTTP a FCM/Apple/Mozilla
    |--------------------------------------------------------------------------
    */

    'timeout' => (int) env('VAPID_TIMEOUT', 10),

];
