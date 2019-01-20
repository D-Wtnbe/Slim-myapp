<?php

use Respect\Validation\Validator as v;

session_start();

require __DIR__ . '/../vendor/autoload.php';

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
        'db' => [
            'driver' 	=> 'mysql',
            'host' 		=> 'localhost',
            'database' 	=> 'slim',
            'username' 	=> 'root',
            'password' 	=> '',
            'charset' 	=> 'utf8',
            'collation'	=> 'utf8_unicode_ci',
            'prefix'	=> '',
        ]
    ],

]);
$provider = new Kitchenu\Debugbar\ServiceProvider();
$provider->register($app);
$container = $app->getContainer();

// データベース
$capsule = new Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['validator'] = function ($container) {
    return new App\Validation\Validator;
};

// Illuminate package
$container['db'] = function ($container) use ($capsule) {
    return $capsule;
};

// Authクラス
$container['auth'] = function ($container) {
    return new \App\Auth\Auth;
};

// フラッシュメッセージ
$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

// view
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [
        'cache' => false,
    ]);

    $view->addExtension(new Slim\Views\TwigExtension(
        $container->router,
        $container->request->getUri()
    ));

    // viewがコントローラーにアクセスするため
    $view->getEnvironment()->addGlobal('auth', [
        'check' => $container->auth->check(),
        'user' => $container->auth->user()
    ]);

    // viewがフラッシュメッセージにアクセスするため
    $view->getEnvironment()->addGlobal('flash', $container->flash);

    return $view;
};

$container['HomeController'] = function ($container) {
    return new \App\Controllers\HomeController($container);
};

$container['AuthController'] = function ($container) {
    return new \App\Controllers\Auth\AuthController($container);
};


$container['PasswordController'] = function ($container) {
    return new \App\Controllers\Auth\PasswordController($container);
};

// Slim CSRF
$container['csrf'] = function ($container) {
    return new \Slim\Csrf\Guard;
};

// エラー
$app->add(new \App\Middelware\ValidationErrorsMiddelware($container));

// give back the old input
$app->add(new \App\Middelware\OldInputMiddelware($container));

// give back a csrf generated key
$app->add(new \App\Middelware\CsrfViewMiddelware($container));

// run the crsf check
$app->add($container->csrf);

// setup custom rules
v::with('App\\Validation\\Rules\\');

require  __DIR__ . '/../app/routes.php';
