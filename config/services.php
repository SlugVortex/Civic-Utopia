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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'azure' => [
        'openai' => [
            'endpoint' => env('AZURE_AI_PROJECT_ENDPOINT'),
            'api_key' => env('AZURE_AI_API_KEY'),
            'deployment' => env('AZURE_AI_MODEL_DEPLOYMENT_NAME'),
            'api_version' => env('AZURE_API_VERSION', '2024-05-01-preview'),
        ],
        'vision' => [
            'endpoint' => env('AZURE_AI_VISION_ENDPOINT'),
            'api_key' => env('AZURE_AI_VISION_API_KEY'),
        ],
        'speech' => [
            'key' => env('AZURE_AI_SPEECH_KEY'),
            'region' => env('AZURE_AI_SPEECH_REGION'),
        ],
        'document' => [
            'endpoint' => env('AZURE_AI_DOCUMENT_INTELLIGENCE_ENDPOINT'),
            'key' => env('AZURE_AI_DOCUMENT_INTELLIGENCE_API_KEY'),
        ],
    ],

     // --- ADDED FOR CIVIC UTOPIA ---
    'rapidapi' => [
        'key' => env('RAPIDAPI_KEY'),
        'host' => env('RAPIDAPI_HOST'),
    ],

    'azure_dalle' => [
        'endpoint' => env('AZURE_DALLE_ENDPOINT'),
        'key' => env('AZURE_DALLE_API_KEY'),
    ],

];
