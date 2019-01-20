<?php
return [
    /*
   |--------------------------------------------------------------------------
   | LaravelWordpress Settings
   |--------------------------------------------------------------------------
   |
   | LaravelWordpress is disabled by default, when enabled is set to true in app.php.
   | You can override the value by setting enable to true or false instead of null.
   |
   */
    'enabled' => env('VRKANSAGARA_LARAVEL_WORDPRESS_ENABLED', false),

    'debug' => env('VRKANSAGARA_LARAVEL_WORDPRESS_DEBUG', false),

    'database' => [
        'default' => env('DB_WOREDPRESS_CONNECTION', 'mysql'),
        'connections' => [
            'mysql' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_WORDPRESS_DATABASE', ''),
                'username' => env('DB_WORDPRESS_USERNAME', ''),
                'password' => env('DB_WORDPRESS_PASSWORD', ''),
                'unix_socket' => env('DB_WORDPRESS_SOCKET', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'prefix_indexes' => true,
                'strict' => true,
                'engine' => null,
            ],
        ]
    ]

];
