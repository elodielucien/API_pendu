<?php

require "predis/autoload.php";
Predis\Autoloader::register();


// Connexion à Redis
try {

    $redis = new Predis\Client(array(
        "scheme" => "tcp",
        "host" => "SG-Pendu-32039.servers.mongodirector.com:6379",//changer le nom de la base
        "port" => 6379,
        "password"=>"THpCYGub1Hlz1mjSF34scGgWWaoyBYr5"//changer le mot de passe de la base
    ));

}
catch (Exception $e) {
    die($e->getMessage());
}

// mise à jour de la valeur
$redis->set('message', 'Coucou');

// recuperation de la valeur
$value = $redis->get('message');

// affichage de la valeur
print($value);
echo ($redis->exists('message')) ? "Oui" : "Non";

//suppression de la clé
$redis->del('message');