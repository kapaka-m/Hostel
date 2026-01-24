<?php

return [
    'cache_ttl' => env('FEATURE_FLAGS_CACHE_TTL', 30),
    'defaults' => [
        'audit_logs' => false,
        'activity_feed' => false,
        'permissions' => false,
        'correlation_ids' => false,
        'response_time_logging' => false,
        'user_freeze' => false,
        'strong_passwords' => false,
        'admin_ip_allowlist' => false,
        'suspicious_login_alerts' => false,
    ],
];
