<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => '02115564_0000018',
    'username'  => '02115564_0000018',
    'password'  => 'N*YVbTlqe_KH',
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


// PHPMailer config

$app->phpMailer = array(
    'Host' => 'smtp.gmail.com',
    'SMTPAuth' => true,
    'Username' => 'jaroslaw.frydrych@plej.pl',
    'Password' => 'PaulinA2121',
    'SMTPSecure' => 'tls',
    'Port' => 587,
    'setFrom' => ['email' => 'jaroslaw.frydrych@plej.pl', 'name' => 'Mailer'],
    'addReplyTo' => ['email' => 'jaroslaw.frydrych@plej.pl', 'name' => 'Information']
);

