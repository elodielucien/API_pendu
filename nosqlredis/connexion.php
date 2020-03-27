<?php

require "predis/autoload.php";
Predis\Autoloader::register();

try {
    $redis = new Predis\Client(array(
        "scheme" => "tcp",
        "host" => "SG-Pendu-32039.servers.mongodirector.com:6379", //changer le nom de la base
        "port" => 6379,
        "password" => "THpCYGub1Hlz1mjSF34scGgWWaoyBYr5" //changer le mot de passe de la base
    ));
} catch (Exception $e) {
    die($e->getMessage());
}

if(isset($_POST['KEY_PLAYER_LEAVING'])){
    //session_destroy();
    $redis->zRem('AreCurrentlyPlaying', $_POST['KEY_PLAYER_LEAVING']);
    echo($_POST['PLAYER_LEAVING']);
}
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Connexion Le PeNdU</title>
</head>
<h2>Choisisez un nom</h2>
<span>
    <form method="post" action="index.php">
        <input type="text" name="PLAYER_NAME" />

        <input type="submit" />
    </form>
</span>

</html>