<?php
return [
    'settings' => [
        'displayErrorDetails' => true,

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
        ],

        //db settings
        'db' => [
            'path' => 'sqlite:' . __DIR__.'/../src/blog.db',
        ],
    ],
];
