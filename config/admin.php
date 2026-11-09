<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Session Idle Timeout
    |--------------------------------------------------------------------------
    |
    | Administrators are signed out after this many minutes without activity
    | in the admin area.
    |
    */

    'idle_timeout_minutes' => (int) env('ADMIN_IDLE_TIMEOUT_MINUTES', 30),

    /*
    |--------------------------------------------------------------------------
    | Account Lockout
    |--------------------------------------------------------------------------
    |
    | After this many failed admin sign-in attempts for one email address
    | within the lockout window, further attempts are refused.
    |
    */

    'lockout_attempts' => (int) env('ADMIN_LOCKOUT_ATTEMPTS', 5),

    'lockout_minutes' => (int) env('ADMIN_LOCKOUT_MINUTES', 15),

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Setup Skipping
    |--------------------------------------------------------------------------
    |
    | When enabled, administrators without two-factor authentication may skip
    | setup and use the admin area. Admins who have enabled two-factor must
    | always pass the challenge. Set to false in production to require it.
    |
    */

    'two_factor_skippable' => (bool) env('ADMIN_TWO_FACTOR_SKIPPABLE', true),

];
