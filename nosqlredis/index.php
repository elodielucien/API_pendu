<?php

require "predis/autoload.php";
Predis\Autoloader::register();


// Connexion à Redis
try {

    $redis = new Predis\Client(array(
        "scheme" => "tcp",
        "host" => "SG-TPNOSQL-31571.servers.mongodirector.com",//changer le nom de la base
        "port" => 6379,
        "password"=>"bsXiEYKjq3AfpKRjuuRheT5ig7nZyZuF"//changer le mot de passe de la base
    ));

}
catch (Exception $e) {
    die($e->getMessage());
}

// mise à jour de la valeur
$redis->set('message', 'Hello world');

// recuperation de la valeur
$value = $redis->get('message');

// affichage de la valeur
print($value);
echo ($redis->exists('message')) ? "Oui" : "Non";

//suppression de la clé
$redis->del('message');