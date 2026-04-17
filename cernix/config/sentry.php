<?php

use App\Monitoring\SentryScrubber;

return [

    // ── Connection ────────────────────────────────────────────────────────────
    // DSN is read exclusively from the environment — never hardcode it here.
    // Leave SENTRY_DSN unset (or empty) to disable Sentry entirely.
    'dsn' => env('SENTRY_DSN'),

    // ── Identity ──────────────────────────────────────────────────────────────
    'environment' => env('APP_ENV', 'production'),
    'release'     => env('SENTRY_RELEASE'),

    // ── Privacy ───────────────────────────────────────────────────────────────
    // Do not attach cookies or raw Authorization header values.
    'send_default_pii' => false,

    // ── Performance ───────────────────────────────────────────────────────────
    // Disabled by default — set SENTRY_TRACES_SAMPLE_RATE=0.1 in .env to
    // enable distributed tracing for 10 % of requests.
    'traces_sample_rate'   => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.0),
    'profiles_sample_rate' => 0.0,

    // ── Breadcrumbs ───────────────────────────────────────────────────────────
    'breadcrumbs' => [
        'logs'         => true,   // Laravel log entries (info/warning/error)
        'sql_queries'  => false,  // SQL could include student identifiers
        'sql_bindings' => false,
        'queue_info'   => false,
        'command_info' => false,
    ],

    // ── Payload scrubber ──────────────────────────────────────────────────────
    // Called for every event before it is transmitted. Strips QR payloads,
    // HMAC secrets, payment references, and other domain secrets.
    'before_send' => [SentryScrubber::class, 'beforeSend'],

];
