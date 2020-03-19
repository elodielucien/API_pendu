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

//création de joueurs 



// mise à jour de la valeur
$redis->set('message', 'Coucou');

// recuperation de la valeur
$value = $redis->get('message');

// affichage de la valeur
print($value);
echo ($redis->exists('message')) ? "Oui" : "Non";

//suppression de la clé
$redis->del('message');

?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Le PeNdU</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
</head>
<body>

<?php
echo("bonjour")
?>

<nav class="navbar navbar-light bg-light">
    <span class="navbar-brand mb-0 h1">Le PeNdU</span>
    <span class="navbar-text">
      Bonjour Joe, ton score est 10293 points !
    </span>
</nav>

<div class="container">

    <div class="row">
        <div class="col-sm-3">
            <h2>Liste des joueurs</h2>
            <ul>
                <li>Mick</li>
                <li>Jo</li>
                <li>Lili78</li>
            </ul>
        </div>
        <div class="col-sm-6">
            <h2>Mot à trouver</h2>
            <span>_ &nbsp; _ &nbsp _ &nbsp E &nbsp _ &nbsp _ &nbsp _ &nbsp E &nbsp _ </span>
        </div>
        <div class="col-sm-3">
            <h2>Propositions</h2>
            <ul>
                <li>A</li>
                <li>E</li>
                <li>C</li>
                <li>J A M B O N</li>
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <h2>Temps restant</h2>
            <span>45 secondes</span>
        </div>
        <div class="col-sm-6">
            <h2>Nombre d'essais restant</h2>
            <span>3 essais</span>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <h2>Proposer une lettre</h2>
            <span><input type="text" size="3"/><button>Valider</button></span>
        </div>
        <div class="col-sm-6">
            <h2>Proposer un mot</h2>
            <span>
            


            <form method="post" action="index.php">
                <input type="text" name="WORD" />

                 <input type="submit"> </span>
            
            </form> 
                
      <?php
    
            if (isset( $_POST['WORD'])){
            $redis->set('WordToFind', $_POST['WORD']);
            $value = $redis->get('WordToFind');
             print($value);
            }
            
        ?>

                
                     
                    



        </div>
    </div>
</div>
</body>
</html>