<?php
require 'vendor/autoload.php';
require '/Slim/Slim.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use src\model\User;

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->view(new \JsonApiView());
$app->add(new \JsonApiMiddleware());

require 'config/config.php';
require 'routes/routes.php';

$app->db = function () {
    return New Capsule;
};

$app->get('/users/:username', function($username) use ($app) {

    //$user = $app->db->table('recipients')->join('wishes', 'recipients.wishes_id', '=', 'wishes.id')->where('id', $username)->first();

    $wishes = $app->db->table('recipients')
        ->leftJoin('wishes', 'recipients.wishes_id', '=', 'wishes.id')
        ->get();

    //$user = User::where('id', $username)->first();

    $app->render(200, array(
        'user' => $wishes
    ));
});

$app->post('/wishes', function () use ($app) {


    try {

        $email = $app->request->post('email');

        $app->db->table('recipients')
            ->insert(
                ['email' => $email]
            );
    }catch(\Exception $e){
        throw new Exception("Nie udało się dodać życzeń :(");
    }

    $app->render(200, array(
        'msg' => 'Pomyślnie dodano usera ' . $email
    ));
});

$app->get('/wishes', function () use ($app) {
    /*$posts = $app->db->query("
        SELECT *
        FROM recipients
        LEFT JOIN wishes
        ON recipients.wishes_id = wishes.id
    ")->fetchAll(PDO::FETCH_ASSOC);*/

    $posts = $app->db->table('recipients')
        ->leftJoin('wishes', 'recipients.wishes_id', '=', 'wishes.id')
        ->get();

    $app->render(200, array(
        'posts' => $posts
    ));
})->name('wishes');

$app->get('/wishes/:id', function ($id) use ($app) {
    $post = $app->db->prepare("
        SELECT *
        FROM recipients
        LEFT JOIN wishes
        ON recipients.wishes_id = wishes.id
        WHERE wishes.id = :id
    ");

    $post->execute(['id' => $id]);

    $post = $post->fetch(PDO::FETCH_ASSOC);

    if(!$post){
        throw new Exception("Nie ma takiej karki!");
    }

    $app->render(200, array(
        'post' => $post
    ));
});