<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the connections below you wish
    | to use as your default connection for all reverb work.
    |
    */

    'default' => env('REVERB_CONNECTION', 'reverb'),

    /*
    |--------------------------------------------------------------------------
    | Reverb Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the connections setup for your application.
    |
    */

    'connections' => [
        'main' => [
            'host' => env('REVERB_SERVER_HOST', '0.0.0.0'),
            'port' => env('REVERB_SERVER_PORT', 8080),
            'hostname' => env('REVERB_HOST', '127.0.0.1'),
            'options' => [
                'tls' => [
                    'cert' => env('REVERB_TLS_CERT_PATH', null),
                    'key' => env('REVERB_TLS_KEY_PATH', null),
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reverb Apps
    |--------------------------------------------------------------------------
    |
    | Here you can configure the apps that can connect to your Reverb server.
    | By default, the "id", "key", and "secret" will use the values from your
    | broadcasting config, but you're free to add more apps as needed.
    |
    */

    'apps' => [
        [
            'id' => env('REVERB_APP_ID'),
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'app_host' => env('APP_URL', 'http://localhost'),
            'allowed_origins' => explode(',', env('REVERB_ALLOWED_ORIGINS', '*')),
            'ping_interval' => env('REVERB_PING_INTERVAL', 60),
            'max_message_size' => env('REVERB_MAX_SIZE', 10000),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Presence Channel Cleanup
    |--------------------------------------------------------------------------
    |
    | When a user connects to a presence channel, a record is stored in your
    | database to maintain a list of all connected users. You can choose
    | to have the record removed when user disconnects.
    |
    */

    'presence_channel_cleanup' => env('REVERB_PRESENCE_CHANNEL_CLEANUP', true),

    /*
    |--------------------------------------------------------------------------
    | Application Manager
    |--------------------------------------------------------------------------
    |
    | This key enables the processing of WebSocket events on queue workers.
    | It's crucial for effectively handling WebSocket messages when running
    | multiple server instances, akin to broadcasting in a clustered setup.
    |
    | While this approach introduces a slight delay due to utilizing the queue system,
    | it guarantees message delivery across multiple server instances. You should
    | enable this for production environments with multiple server instances.
    |
    */

    'manager' => env('REVERB_MANAGER', \Laravel\Reverb\ApplicationManagers\DatabaseApplicationManager::class),

    /*
    |--------------------------------------------------------------------------
    | Adapters
    |--------------------------------------------------------------------------
    |
    | Specify which adapters the server will use.
    |
    | The server adapter handles the HTTP server, the webhook adapter handles
    | the webhook server, and the database adapter handles database operations.
    |
    */

    'adapters' => [
        'server' => env('REVERB_SERVER_ADAPTER', \Laravel\Reverb\Adapters\Server\SwoolServerAdapter::class),
        'webhook' => env('REVERB_WEBHOOK_ADAPTER', \Laravel\Reverb\Adapters\Webhook\GuzzleWebhookAdapter::class),
        'database' => env('REVERB_DATABASE_ADAPTER', \Laravel\Reverb\Adapters\Database\EloquentDatabaseAdapter::class),
    ],

    /*
    |--------------------------------------------------------------------------
    | Server Modes
    |--------------------------------------------------------------------------
    |
    | Reverb supports different server modes to better suit your needs.
    |
    | `full`: Runs the WebSocket server and also process WebSocket events.
    | `server`: Only runs the WebSocket server but doesn't process events.
    | `worker`: Process WebSocket events through the queue.
    |
    */

    'server_mode' => env('REVERB_SERVER_MODE', 'full'),

    /*
    |--------------------------------------------------------------------------
    | CORS Allowed Origins
    |--------------------------------------------------------------------------
    |
    | If the 'allowed_origins' is set to '*', it will allow all origins. If set
    | to 'null', it will process the 'Origin' header from the HTTP request.
    | Otherwise, it should be an array of allowed origins.
    |
    */

    'allowed_origins' => explode(',', env('REVERB_ALLOWED_ORIGINS', '*')),

    /*
    |--------------------------------------------------------------------------
    | Backend
    |--------------------------------------------------------------------------
    |
    | Backend is how Reverb will store connection information and channel
    | information. By default, it will use the database, but you can
    | set it to 'memory' for an in-memory backend instead.
    |
    */

    'backend' => env('REVERB_BACKEND', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Maximum Connection Lifetime
    |--------------------------------------------------------------------------
    |
    | This value determines the maximum lifetime of a WebSocket connection in
    | seconds. This allows you to configure how long a connection should last
    | before it is closed, regardless of client or server activity.
    |
    */

    'max_connection_lifetime' => env('REVERB_MAX_CONNECTION_LIFETIME', null),

    /*
    |--------------------------------------------------------------------------
    | Maximum Request Size
    |--------------------------------------------------------------------------
    |
    | This value represents the maximum allowed size for WebSocket messages
    | in bytes. Requests larger than this will be rejected.
    |
    */

    'max_request_size' => env('REVERB_MAX_REQUEST_SIZE', 1024 * 1024), // 1MB

    /*
    |--------------------------------------------------------------------------
    | Connection Metrics
    |--------------------------------------------------------------------------
    |
    | Enable connection metrics to track the number of connections per channel.
    | This is useful for monitoring and debugging. It incurs a performance
    | penalty, so it's disabled by default in production.
    |
    */

    'connection_metrics' => env('REVERB_CONNECTION_METRICS', false),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Statistics
    |--------------------------------------------------------------------------
    |
    | Whether or not to collect statistics about the broadcasts through the
    | server. This includes the number of messages, connections, etc.
    |
    */

    'broadcast_statistics' => env('REVERB_BROADCAST_STATISTICS', false),
];
