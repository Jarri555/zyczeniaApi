<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'zyczenia',
    'username'  => 'root',
    'password'  => '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();

$capsule->bootEloquent();

//PRODUCTION

//$app->config('debug', false);


// DATABASE

$app->container->singleton('db', function () {
    return new PDO('mysql:host=localhost;dbname=zyczenia', 'root', '');
});


