<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => 'debug',
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        'deletePartDetail' => [
            'driver' => 'daily', // or 'daily' if you want rotation
            'path' => storage_path('logs/deletePartDetail/deletePartDetail.log'),
            'level' => 'debug', // or 'info', 'warning', 'error' etc.
        ],

        'fixRollQty' => [
            'driver' => 'daily', // or 'daily' if you want rotation
            'path' => storage_path('logs/fixRollQty/fixRollQty.log'),
            'level' => 'debug', // or 'info', 'warning', 'error' etc.
        ],

        'resetStockerForm' => [
            'driver' => 'daily', // or 'daily' if you want rotation
            'path' => storage_path('logs/resetStockerForm/resetStockerForm.log'),
            'level' => 'debug', // or 'info', 'warning', 'error' etc.
        ],

        'transferOutput' => [
            'driver' => 'daily', // or 'daily' if you want rotation
            'path' => storage_path('logs/transferOutput/transferOutput.log'),
            'level' => 'debug', // or 'info', 'warning', 'error' etc.
        ],

        'missReworkOutput' => [
            'driver' => 'daily', // or 'daily' if you want rotation
            'path' => storage_path('logs/missReworkOutput/missReworkOutput.log'),
            'level' => 'debug', // or 'info', 'warning', 'error' etc.
        ],

        'missRejectOutput' => [
            'driver' => 'daily', // or 'daily' if you want rotation
            'path' => storage_path('logs/missRejectOutput/missRejectOutput.log'),
            'level' => 'debug', // or 'info', 'warning', 'error' etc.
        ],

        'missMasterPlanOutput' => [
            'driver' => 'daily', // or 'daily' if you want rotation
            'path' => storage_path('logs/missMasterPlanOutput/missMasterPlanOutput.log'),
            'level' => 'debug', // or 'info', 'warning', 'error' etc.
        ],

        'missUserOutput' => [
            'driver' => 'daily', // or 'daily' if you want rotation
            'path' => storage_path('logs/missUserOutput/missUserOutput.log'),
            'level' => 'debug', // or 'info', 'warning', 'error' etc.
        ],

        'missPackingPo' => [
            'driver' => 'daily', // or 'daily' if you want rotation
            'path' => storage_path('logs/missPackingPo/missPackingPo.log'),
            'level' => 'debug', // or 'info', 'warning', 'error' etc.
        ],

        'deleteStockerAbout' => [
            'driver' => 'daily', // or 'daily' if you want rotation
            'path' => storage_path('logs/stockerAbout/deleteStockerAbout.log'),
            'level' => 'debug', // or 'info', 'warning', 'error' etc.
        ],

        'updateHrisLabor' => [
            'driver' => 'daily', // or 'daily' if you want rotation
            'path' => storage_path('logs/hris/updateHrisLabor.log'),
            'level' => 'debug', // or 'info', 'warning', 'error' etc.
        ],
    ],
];
