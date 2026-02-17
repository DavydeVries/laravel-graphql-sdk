<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Endpoint
    |--------------------------------------------------------------------------
    |
    | The Prepr GraphQL endpoint to consume.
    |
    */

    'endpoint' => env('PREPR_ENDPOINT', ''),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | The request timeout in seconds.
    |
    */

    'timeout' => (int) env('PREPR_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Connect Timeout
    |--------------------------------------------------------------------------
    |
    | The connection timeout in seconds.
    |
    */

    'connect_timeout' => (int) env('PREPR_CONNECT_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | HTTP Headers
    |--------------------------------------------------------------------------
    |
    | Default HTTP headers sent with every Prepr request.
    |
    */

    'headers' => [
        'Prepr-Resolve-Internal-Links-Prefix' => env('PREPR_RESOLVE_INTERNAL_LINKS_PREFIX', null),
    ],
];
