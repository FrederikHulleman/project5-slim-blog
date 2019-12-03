<?php
return [
    'settings' => [
        'displayErrorDetails' => true,
        //'determineRouteBeforeAppMiddleware' => false,
        //'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        //twig view settings
        'view' => [
          'template_path' => __DIR__ . '/../templates/',
          'cache' => false,
          'auto_reload' => true,
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
            //'level' => \Monolog\Logger::DEBUG,
        ],

        //db settings
        'db' => [
            'driver' => 'sqlite',
            'host' => 'localhost',
            'database' => __DIR__ . '/../src/blog.db',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ],
    ],
];
