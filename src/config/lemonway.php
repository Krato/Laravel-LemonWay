<?php

return [

    /*
     * Default api url to make callbacks
     * @default https://sandbox-api.lemonway.fr/mb/demo/dev/directkitjson2/Service.asmx
     */
    'api_url' => env('LEMONWAY_APIURL', 'https://sandbox-api.lemonway.fr/mb/demo/dev/directkitjson2/Service.asmx'),

    /*
     * Your lemonway user login
     */
    'login' => env('LEMONWAY_LOGIN', ''),

    /*
     * Your lemonway password
     */
    'password' => env('LEMONWAY_PASSWORD', ''),

    /*
     * Lemonway language
     * @default en
     */
    'language' => env('LEMONWAY_LANGUAGE', 'en'),

    /*
     * Lemonway api versionç
     * @default 1.8
     */
    'version' => env('LEMONWAY_VERSION', '1.8'),

    /*
     * Lemonway api versionç
     * @default false
     */
    'ssl' => env('LEMONWAY_SSL', false),

];
