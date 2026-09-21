<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Anthropic Claude (Clinical Decision Support AI)
    |--------------------------------------------------------------------------
    | Powers the live Clinical AI Advisor. When no API key is configured the
    | system gracefully falls back to the built-in offline knowledge base so
    | the feature keeps working in air-gapped / on-premise deployments.
    */
    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-opus-5'),
        'version' => env('ANTHROPIC_VERSION', '2023-06-01'),
        'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
        'max_tokens' => (int) env('ANTHROPIC_MAX_TOKENS', 8000),
        // Reasoning effort: low | medium | high | xhigh | max
        'effort' => env('ANTHROPIC_EFFORT', 'high'),
        // Request timeout (seconds) — extended thinking + web search can take time.
        'timeout' => (int) env('ANTHROPIC_TIMEOUT', 120),
        // Real-time web search for current guidelines/outbreaks/recalls.
        'web_search' => env('ANTHROPIC_WEB_SEARCH', true),
        'web_search_max_uses' => (int) env('ANTHROPIC_WEB_SEARCH_MAX_USES', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | NIS ID Card Portal (Officer verification & auto-population)
    |--------------------------------------------------------------------------
    | When "url" is set, officer registration verifies the Service Number
    | against the official NIS ID Card Portal and auto-populates the officer's
    | bio-data from it. When it is not set, the system falls back to the local
    | officer_directory table (and existing staff/patient records), so officer
    | verification keeps working on-premise / before the integration is live.
    */
    'nis_portal' => [
        'url' => env('NIS_IDCARD_PORTAL_URL'),
        'key' => env('NIS_IDCARD_PORTAL_KEY'),
        'timeout' => (int) env('NIS_IDCARD_PORTAL_TIMEOUT', 8),
    ],

];
