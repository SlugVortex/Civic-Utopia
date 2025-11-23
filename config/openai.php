<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key and Organization
    |--------------------------------------------------------------------------
    |
    | Here you may specify your OpenAI API Key and organization. This will be
    | used to authenticate with the OpenAI API - you can find your API key
    | and organization on your OpenAI dashboard, at https://openai.com.
    */

    'api_key' => env('AZURE_AI_API_KEY'), // Use our Azure Key
    'organization' => env('OPENAI_ORGANIZATION'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout may be used to specify the maximum number of seconds to wait
    | for a response. By default, the client will time out after 30 seconds.
    */

    'request_timeout' => env('OPENAI_REQUEST_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | (Azure) API Type
    |--------------------------------------------------------------------------
    |
    | You may specify the API type used. By default, the client will use
    | the 'open_ai' API type. You can switch to 'azure' if you're using the
    | Azure OpenAI Service.
    */

    'api_type' => env('OPENAI_API_TYPE', 'azure'), // Tell the client to use Azure

    /*
    |--------------------------------------------------------------------------
    | (Azure) API Version
    |--------------------------------------------------------------------------
    |
    | You may specify the API version used. By default, the client will use
    | the '2022-12-01' API version.
    */

    'api_version' => env('AZURE_API_VERSION'), // Use our Azure Version

    /*
    |--------------------------------------------------------------------------
    | (Azure) API Base URI
    |--------------------------------------------------------------------------
    |
    | You may specify the base URI used. By default, the client will use
    | the 'api.openai.com/v1' base URI.
    |
    | For Azure, this should be the endpoint of your Azure OpenAI resource.
    */

    'base_uri' => env('AZURE_AI_PROJECT_ENDPOINT'), // Use our Azure Endpoint

    /*
    |--------------------------------------------------------------------------
    | Default Query Parameters
    |--------------------------------------------------------------------------
    |
    | You may specify default query parameters that will be sent with every
    | request to the Azure OpenAI API.
    */

    'default_query_params' => [
        //
    ],
];
