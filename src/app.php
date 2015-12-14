<?php
require 'vendor/autoload.php';
require '/Slim/Slim.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use src\model\User;
use PHPMailer\PHPMailer\PHPMailer;

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
$app->view(new \JsonApiView());
$app->add(new \JsonApiMiddleware());

require 'config/config.php';

$app->db = function () {
    return New Capsule;
};

require 'routes/routes.php';

function emailsSend($recipients)
{
    $app = new \Slim\Slim();

    $app->db = function () {
        return New Capsule;
    };

    try {
        foreach ($recipients as $recipient) {

            $body = '<div style="display:none; white-space:nowrap; font:15px courier; line-height:0;">
    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
</div>
<table width="100%" align="center" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td align="center" valign="top" style="margin: 0; padding: 0;">
            <table width="600" align="center" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0"
                   style="font-family:Arial, Helvetica, sans-serif;">
                <tr>
                    <td align="center" valign="top" style="margin: 0; padding: 0;">
                        <img style="display: block;" src="http://prp.dev.plej.pl/mailing/mailing_header_top.gif" alt="" width="600" height="240">
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center;">

                        <h1 style="font-weight: bold; font-size: 26px; color: #D1AC50; line-height: 38px;">'.$recipient['fullName'].'</h1>

                        <p style="font-size: 20px; color: #162C53; line-height: 28px;">Wysłaliśmy, specjalnie dla Ciebie zrobioną<br>
                        Kartkę Świąteczną<br>
                        Kliknij poniżej aby ją zobaczyć</p>
                    </td>
                </tr>
                <tr>
                    <td align="center" width="271" style="padding: 15px 0; height: 50px;">
                        <a href="/'.$recipient['token'].'" style="width: 271px; display: block; height: 50px;"><img style="display: block;" align="center" src="http://prp.dev.plej.pl/mailing/open-pl.jpg" alt="" width="271" height="50"></a>
                    </td>
                </tr>
                <tr>
                    <td align="center" valign="top" style="margin: 0; padding: 0;">
                        <img style="display: block;" src="http://prp.dev.plej.pl/mailing/mailing_header_bottom.gif" alt="" width="600" height="210">
                    </td>
                </tr>


            </table>
        </td>
    </tr>
</table>';


            $mail = new PHPMailer;

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'jaroslaw.frydrych@plej.pl';                 // SMTP username
            $mail->Password = 'PaulinA2121';                           // SMTP password
            $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;                                    // TCP port to connect to
            $mail->CharSet = 'UTF-8';
            $mail->setFrom('jaroslaw.frydrych@plej.pl', 'Mailer');
            $mail->addReplyTo('jaroslaw.frydrych@plej.pl', 'Information');
            $mail->isHTML(true);

            $mail->Subject = 'Hej, ' . $recipient['fullName'] . ' mamy życzenia dla Ciebie';
            $mail->Body = $body;
            //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
            $mail->addAddress($recipient['email'], $recipient['fullName']);

            if (!$mail->send()) {
                return false;
            } else {
                $app->db->table('recipients')->where('token', $recipient['token'])->update(array('sendEmail' => 1, 'sendDate' => date("Y-m-d H:i:s")));
            }
        }
    } catch (\Exception $e) {
        throw new Exception("Nie udało się wysłać wiadomości :( " . $mail->ErrorInfo);
    }
    return true;
}

$app->get('/', function () use ($app) {

    $app->render(404, array(
        'msg' => 'Zamiast przegladac API moze napijesz sie wodki?',
        'error' => true
    ));

});


$app->post('/wishes', function () use ($app) {
    /*PRZYKLADOWY JSON*/

    /*{
        "fullName":"Moje Imie i nazwisko",
        "q1":1,
        "q1custom":"dasdsadas",
        "q2":2,
        "q3":3,
        "language":"pl",
        "recipients":[
            {"fullName":"Imie i nazwisko", "email":"adasdas@dasdas.pl"},
            {"fullName":"Inne imie i nazwisko", "email":"test@test.pl"},
            {"fullName":"Jeszcze inne dane ąśęśźć", "email":"aaa@ach.pl"}
        ]
    }*/

    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $insert = array();

    if (empty($data['q1custom']) || $data['q1custom'] == null) {
        $q1custom = null;
    } else {
        $q1custom = $data['q1custom'];
    }

    if (empty($data['q1']) || !$data['q1']) {
        $q1 = null;
    } else {
        $q1 = $data['q1'];
    }

    try {
        if (!($data['fullName'] && ($data['q1custom'] || $q1) && $data['q2'] && $data['q3'] && $data['language'])) {
            throw new Exception("Nie udało się dodać życzeń :(");
        }
        $wishId = $app->db->table('wishes')
            ->insertGetId(array('fullName' => $data['fullName'],
                'q1' => $q1,
                'q1custom' => $q1custom,
                'q2' => $data['q2'],
                'q3' => $data['q3'],
                'language' => $data['language'],
                'dateCreated' => date("Y-m-d H:i:s"))
            );

        foreach ($data['recipients'] as $recipient) {
            if (!$recipient['fullName'] || !$recipient['email']) {
                throw new Exception("Nie udało się dodać życzeń :(");
            }
            array_push($insert,array('fullName' => $recipient['fullName'],
                'email' => $recipient['email'],
                'dateAdd' => date("Y-m-d H:i:s"),
                'sendEmail' => false,
                'sendDate' => null,
                'wishesId' => $wishId,
                'token' => md5(uniqid($recipient['email'], true)),
                'opened' => false,
                'dateOpen' => null)
            );
        }

        $app->db->table('recipients')
            ->insert($insert);

    } catch (\Exception $e) {
        throw new Exception("Nie udało się dodać życzeń :(");
    }

    if (emailsSend($insert)) {
        $app->render(200, array(
            'msg' => 'Pomyslnie wysłano kartki'
        ));
    }

    $app->render(400, array(
        'msg' => 'Wystąpił błąd przy wysyłce'
    ));

});

$app->get('/wishes/:token', function ($token) use ($app) {
    try {

        $wish = $app->db
            ->table('recipients')
            ->select('recipients.id', 'wishes.fullName', 'wishes.q1', 'wishes.q1custom', 'wishes.q2', 'wishes.q3', 'wishes.language')
            ->join('wishes', 'recipients.wishesId', '=', 'wishes.id')
            ->where('recipients.token', $token)
            ->first();

        if($wish->q1) {
            $wish->q1 = $app->db
                ->table('questiononetext')
                ->select('textEn', 'textPl')
                ->where('id', $wish->q1)
                ->first();
            if($wish->language == 'en') {
                $wish->q1 = $wish->q1->textEn;
            } else {
                $wish->q1 = $wish->q1->textPl;
            }
        } else {
            $wish->q1 = $wish->q1custom;
        }
    } catch (\Exception $e) {
        throw new Exception("Wystąpił problem z pobieraniem życzeń :(");
    }

    if (!$wish) {
        $app->render(404, array(
            'msg' => 'Nie ma takiej kartki'
        ));
    }

    try {
        $app->db->table('recipients')->where('id', $wish->id)->update(array('opened' => 1, 'dateOpen' => date("Y-m-d H:i:s")));
    } catch (\Exception $e) {
        throw new Exception("Wystąpił problem z pobieraniem życzeń :(");
    }

    unset($wish->id, $wish->q1custom);

    $app->render(200, array(
        'wish' => $wish
    ));
});

$app->put('/wishes/:token', function ($token) use ($app) {
    $json = $app->request->getBody();
    $data = json_decode($json, true);

    if($data['hang']){
        try {
            $user = $app->db
                ->table('recipients')
                ->where('token', $token)
                ->first();


        } catch (\Exception $e) {
            throw new Exception("Wystąpił problem :(");
        }


        if(!$user) {
            throw new Exception("Nie ma takiej kartki :(");
        }

        if($user->dateHang !== null) {
            throw new Exception("Twoja bombka już jest zawieszona");
        }

        try {
            $app->db
                ->table('recipients')
                ->where('id', $user->id)
                ->update(array('dateHang' => date("Y-m-d H:i:s")));

        } catch (\Exception $e) {
            throw new Exception("Wystąpił problem :(");
        }

        $app->render(200, array(
            'success' => true
        ));
    }

    $app->render(200, array(
        'success' => false
    ));
});