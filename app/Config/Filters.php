<?php
// app/Config/Filters.php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public array $aliases = [
        'csrf'          => \CodeIgniter\Filters\CSRF::class,
        'toolbar'       => \CodeIgniter\Filters\DebugToolbar::class,
        'honeypot'      => \CodeIgniter\Filters\Honeypot::class,
        'invalidchars'  => \CodeIgniter\Filters\InvalidChars::class,
        'secureheaders' => \CodeIgniter\Filters\SecureHeaders::class,
        
        // Custom filters
        'tenant'        => \App\Filters\TenantFilter::class,
        'auth'          => \App\Filters\AuthFilter::class,
        'admin'         => \App\Filters\AdminFilter::class,
        'api'           => \App\Filters\ApiFilter::class,
        'role'          => \App\Filters\RoleFilter::class,
    ];

    public array $globals = [
        'before' => [
            'honeypot',
            'csrf' => ['except' => [
                'webhook/*',
                'api/*',
            ]],
            'invalidchars',
        ],
        'after' => [
            'toolbar',
            'secureheaders',
        ],
    ];

    public array $methods = [];

    public array $filters = [
        'tenant' => ['before' => ['restaurant/*']],
        // 'auth' => ['before' => [
        //     'restaurant/*/pos',
        //     'restaurant/*/kitchen',
        //     'restaurant/*/orders',
        //     'restaurant/*/menu',
        //     'restaurant/*/staff',
        //     'restaurant/*/reports',
        //     'restaurant/*/settings',
        // ]],
    ];
}
