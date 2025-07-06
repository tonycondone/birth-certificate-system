<?php
return [
    /**
     * Logging Configuration
     * 
     * Defines system-wide logging behavior and retention policies
     */
    'logging' => [
        // Log levels in order of severity (lowest to highest)
        'levels' => [
            'debug'     => 100,
            'info'      => 200,
            'warning'   => 300,
            'error'     => 400,
            'critical'  => 500
        ],

        // Minimum log level to record
        'min_level' => 'info',

        // Log rotation and retention settings
        'rotation' => [
            // Number of days to retain logs in the main system_logs table
            'retention_days' => 90,

            // Maximum log file size before rotation (in MB)
            'max_file_size' => 50,

            // Number of backup log files to keep
            'max_files' => 5
        ],

        // Categories to log
        'categories' => [
            'authentication' => true,
            'security'       => true,
            'system'         => true,
            'database'       => true,
            'performance'    => false
        ],

        // Sensitive data masking
        'sensitive_fields' => [
            'password',
            'confirm_password',
            'token',
            'reset_token',
            'credit_card',
            'ssn'
        ],

        // External logging integrations
        'external_logging' => [
            'enabled' => false,
            'service' => null, // e.g., 'sentry', 'datadog'
            'config' => []
        ],

        // Performance logging thresholds
        'performance_thresholds' => [
            'database_query' => 0.5,  // Log queries taking more than 0.5 seconds
            'http_request'   => 2.0,  // Log requests taking more than 2 seconds
            'memory_usage'   => 128   // Log memory usage exceeding 128MB
        ]
    ]
]; 