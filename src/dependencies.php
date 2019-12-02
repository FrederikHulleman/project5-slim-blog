<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// Register component on container
$container['view'] = function ($c) {
    $settings = $c->get('settings')['view'];
    $view = new \Slim\Views\Twig($settings['template_path'], [
        'cache' => $settings['cache'],
        'auto_reload' => $settings['auto_reload'],
    ]);

    // Instantiate and add Slim specific extension
    $router = $c->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));

    return $view;
};

//csrf
$container['csrf'] = function ($c) {
    return new \Slim\Csrf\Guard;
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
    return $logger;
};

// database
$container['db'] = function ($c) {
    try {
      //$db = $c['settings']['db'];
      $settings = $c->get('settings')['db'];
      $pdo = new PDO($settings['path']);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
      return $pdo;
    }
    catch (Exception $e) {
      echo "Unable to connect <br>" . $e->getMessage();
      exit;
    }
};
