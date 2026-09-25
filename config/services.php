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

    'analytics_ai' => [
        'provider' => env('ANALYTICS_AI_PROVIDER', 'rules'),
        'url' => env('OLLAMA_URL', 'http://127.0.0.1:11434'),
        'model' => env('OLLAMA_MODEL', 'llama3.2:1b'),
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY', ''),
        'model' => env('GROQ_MODEL', 'qwen/qwen3.8-27b'),
    ],

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

    // ─── SMS Provider Switch ──────────────────────────────────────────
    'sms_provider' => env('SMS_PROVIDER', 'movider'), // 'movider' or 'textbee'

    // ─── Movider SMS Gateway ──────────────────────────────────────────
    'movider' => [
        'api_key'    => env('MOVIDER_API_KEY'),
        'api_secret' => env('MOVIDER_API_SECRET'),
        'api_url'    => env('MOVIDER_API_URL', 'https://api.movider.co/v1/sms'),
        'mock'       => env('MOVIDER_MOCK', false),
    ],

    // ─── TextBee SMS Gateway (Android phone gateway) ──────────────────
    'textbee' => [
        'api_key'   => env('TEXTBEE_API_KEY'),
        'device_id' => env('TEXTBEE_DEVICE_ID'),
        'api_url'   => 'https://api.textbee.dev/api/v1/gateway/devices',
    ],

    // ─── Google Gemini AI ─────────────────────────────────────────────
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model'   => env('GEMINI_MODEL', 'gemini-1.5-flash'),
        'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models',
    ],

    'google_maps' => [
        'api_key' => in_array(env('GOOGLE_MAPS_API_KEY', ''), ['', 'your_key_here'], true)
            ? ''
            : env('GOOGLE_MAPS_API_KEY'),
    ],

];
