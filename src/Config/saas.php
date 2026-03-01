<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SaaS Domain Configuration
    |--------------------------------------------------------------------------
    | These values are read by Middleware and Controllers.
    | Using config() instead of env() ensures they work correctly
    | even when config:cache is enabled.
    */

    'tenant_base_domain' => env('TENANT_BASE_DOMAIN', 'athkahr.com'),

    'central_domain' => env('CENTRAL_DOMAIN', env('TENANT_BASE_DOMAIN', 'athkahr.com')),
];
