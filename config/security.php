<?php
return [
    'authentication' => [
        'token_expiration' => [
            'access_token' => 3600,     // 1 hour
            'refresh_token' => 604800,  // 7 days
        ],
        'password_policy' => [
            'min_length' => 12,
            'max_length' => 128,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_special_chars' => true,
            'forbidden_passwords' => [
                'password', '123456', 'qwerty', 'admin'
            ]
        ],
        'brute_force_protection' => [
            'max_login_attempts' => 5,
            'lockout_duration' => 1800,  // 30 minutes
            'reset_attempts_after' => 3600  // 1 hour
        ]
    ],

    'session' => [
        'timeout' => 3600,  // 1 hour
        'regenerate_interval' => 300,  // 5 minutes
        'secure_cookie' => true,
        'http_only' => true,
        'same_site' => 'Strict'
    ],

    'cors' => [
        'allowed_origins' => [
            'http://localhost:8000',
            'https://birth-certificate-system.local'
        ],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => [
            'Content-Type', 
            'Authorization', 
            'X-Requested-With', 
            'X-CSRF-Token'
        ],
        'max_age' => 86400  // 1 day
    ],

    'rate_limiting' => [
        'global_limit' => 100,  // requests per minute
        'endpoints' => [
            '/login' => 10,
            '/register' => 5,
            '/reset-password' => 3
        ]
    ],

    'encryption' => [
        'algorithm' => 'AES-256-CBC',
        'key_length' => 32,
        'iv_length' => 16
    ],

    'audit_logging' => [
        'enabled' => true,
        'log_levels' => [
            'authentication' => true,
            'authorization' => true,
            'password_changes' => true,
            'profile_updates' => true
        ],
        'retention_days' => 90
    ],

    'two_factor_authentication' => [
        'enabled' => false,
        'methods' => [
            'totp' => true,
            'sms' => false,
            'email' => true
        ],
        'backup_codes' => [
            'count' => 5,
            'one_time_use' => true
        ]
    ]
]; 