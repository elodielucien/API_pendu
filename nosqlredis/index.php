<?php
//On lance la session
session_start();

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

//-------- Création de joueurs --------
$nbPlayers = 4;
//On test si on lance le jeu pour la première fois
if(!isset($_SESSION['gameStarted'])){

    //On crée les différents joueur

    $redis->HMSET("player1", array(
        "name" => "Joueur 1",
        "points" => 0,
        "IsPlaying" => "false",
        "ProposedWord" => "false"
    ));
    
    $redis->HMSET("player2", array(
        "name" => "Joueur 2",
        "points" => 0,
        "IsPlaying" => "false",
        "ProposedWord" => "false"
    ));

    $redis->HMSET("player3", array(
        "name" => "Joueur 3",
        "points" => 0,
        "IsPlaying" => "false",
        "ProposedWord" => "false"
    ));

    $redis->HMSET("player4", array(
        "name" => "Joueur 4",
        "points" => 0,
        "IsPlaying" => "false",
        "ProposedWord" => "false"
    ));
    //On crée une variable de session pour dire que le jeu est lancé
    $_SESSION['nbTries'] = 10;
    $_SESSION['playerChoosingWord'] = rand(1, $nbPlayers);
    if($_SESSION['playerChoosingWord'] != 1){
        $_SESSION['IsProposingLetter'] = 1;
    }
    else {
        $_SESSION['IsProposingLetter'] = 2;
    }

    $_SESSION['gameStarted'] = true; 
}


//On affiche les infos joueur1
//var_dump($redis->hgetall("player1"));

//On ajoute un point au joueur1
//$redis->HSET("player1", "points", "1");

// -------------------------------------


// mise à jour de la valeur
$redis->set('message', 'Coucou');

// recuperation de la valeur
$value = $redis->get('message');

// affichage de la valeur
//print($value);
//echo ($redis->exists('message')) ? "Oui" : "Non";

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
            <?php

            //On affiche dynamiquement une liste la liste des joueurs
                for($i=1; $i<=$nbPlayers; $i++ ){
                    echo("<li>");
                    $playerName = $redis->HGET("player".$i."", "name");
                    echo($playerName);
                    echo("</li>");
                }
            ?>
            <!--
                <li>Bob</li>
                <li>Jo</li>
                <li>Lili78</li>
            -->
            </ul>
        </div>
        <div class="col-sm-6">
            <h2>Mot à trouver</h2>

        <?php

        if (isset( $_POST['WORD'])){
            $redis->set('WordToFind', $_POST['WORD']);
            $redis->expire('WordToFind',60);  //TTL à 60 secondes
            $mot = $redis->get('WordToFind');
            $longueurmot = strlen($mot);

            $wordToDisplay ="";
        
            for($i = 1 ; $i <= $longueurmot ; $i++)
                 {
                    
                    $wordToDisplay = $wordToDisplay . " _ " ;
                  }

            $redis->set('WordToDisplay', $wordToDisplay);
            $wtd = $redis->get('WordToDisplay');
            echo($wtd);
            
         }
         if (isset($_POST['LETTER'])){
            $letterValue = $redis -> get('newLetter');
          /*  if (letterBelongsToWord($letterValue)) {
                //remplacer la lettres dans le mot aux endroits correspondants
              //  echo(replaceInWord($letterValue));
                $wtd = $redis->get('WordToDisplay');
                echo($wtd);    
                
            }
            else {
                $wtd = $redis->get('WordToDisplay');
                echo($wtd);
                
            }*/

         }

        
         
           // <span>_ &nbsp; _ &nbsp _ &nbsp E &nbsp _ &nbsp _ &nbsp _ &nbsp E &nbsp _ </span>

        ?>
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
            <span>
                <form method="post" action="index.php">
                    <input type="text" size="3" name="LETTER"/>
                        <input type="submit"/> 
                </form>
            </span>
        </div>

        <div class="col-sm-6">
            <h2>Proposer un mot</h2>
            <span>
                <form method="post" action="index.php">
                    <input type="text" name="WORD" />
                        <input type="submit" /> 
                </form> 
            </span>
        </div>
    </div>
</div>
</body>
</html>

<?php
// Création de la liste de lettres déjà proposée ---------------------------------

//$redis->sadd('proposedLetters', 'A'); //de type Set
//var_dump($redis->sgetmembers('proposedLetters'));

//---------------------------------------------------------------------------------
            //Si on cliqué pour proposer une lettre : code exécuté au clic sur Valider sous "proposer une lettre"
            if (isset($_POST['LETTER'])){
                if (isset($_POST['LETTER'])){

                    //On vas tester tous les cas pour définir qui est le suivant
                    /*$_SESSION['nbTries']--;
                    if($_SESSION['nbTries'] == 0){
                        $_SESSION['playerChoosingWord']++;
                        if ($_SESSION['playerChoosingWord'] > $nbPlayers){
                            $_SESSION['playerChoosingWord'] = 1;
                        }
                        $_SESSION['nbTries'] = 10;
                    }
                    echo("Il vous reste ".$_SESSION['nbTries']." essais !");
                    
                    $_SESSION['IsProposingLetter']++;
                    if($_SESSION['IsProposingLetter'] == $_SESSION['playerChoosingWord']){
                        $_SESSION['IsProposingLetter']++;
                    }
                    if($_SESSION['IsProposingLetter'] > $nbPlayers){
                        if($_SESSION['playerChoosingWord'] != 1){
                            $_SESSION['IsProposingLetter'] = 1;
                        }
                        else {
                            $_SESSION['IsProposingLetter'] = 2;
                        }
                    }*/

                $redis -> set('newLetter', $_POST['LETTER']);
                $letterValue = $redis -> get('newLetter');
                print($letterValue);
                //on vérifie si la lettre appartient au mot
                if (letterBelongsToWord($letterValue)) {
                    //remplacer la lettres dans le mot aux endroits correspondants
                    $updatedWord = replaceInWord($letterValue);
                    //afficher le mot mis à jour 
                    //....
                }
                else {
                    print("Cette lettre n'est pas dans le mot recherché");
                }

            //-------------------------- DEBUG pour afficher qui joue -----------------------------
            echo("<br />");
            $playerChoosingWord = $redis->HGET("player".$_SESSION['playerChoosingWord'], "name");
            echo("Is choosing word");
            echo("<br />");
            echo($playerChoosingWord);
            echo("<br />");
            echo("Are submittign letters");
            for($i=1; $i<=$nbPlayers; $i++ ){
                if($i != $_SESSION['playerChoosingWord'])
                {
                    echo("<li>");
                    $playerName = $redis->HGET("player".$i."", "name");
                    echo($playerName);
                    echo("</li>");
                }
            }
            echo("<br />");
            echo("Is submitting letter now");
            echo("<br />");
            $playerName = $redis->HGET("player".$_SESSION['IsProposingLetter']."", "name");
            echo($playerName);
            //------------------------------------------------------------------------------------
        }
    }
            //Si on a cliqué pour proposer un mot
            if (isset( $_POST['WORD'])){
                $redis->set('WordToFind', $_POST['WORD']);
                $value = $redis->get('WordToFind');
                print($value);
            }


// FONCTIONS OPERANT SUR LA BDD AVEC REDIS --------------------------------------//
            
         
            //teste si la lettre proposée appartient au mot 
            function letterBelongsToWord($letter) {
                $word = $redis->get('WordToFind');   //PB : REVIENT A NULL A CHAQUE ENVOI D'UNE LETTRE
                $len = strlen($word);
                for($i=0 ; $i<$len ; $i++) {
                    if(strcmp($letter,$redis->getrange('WordToFind', $i, $i))==0) {
                        return true;
                    break;
                    }
                }
                return false;
            }
            
            //teste s'il reste des lettres à trouver dans le mot affiché
            function isLettersLasting($displayedWord) {
                $len = strlen($displayedWord);
                for($i=0 ; $i<$len ; $i++) {
                    if(strcmp($redis->getrange('WordToFind', $i, $i),'_')==0) {
                        return true;
                    break;
                    }
                }
                return false;
            }

            //teste si la lettre proposée a déjà été proposée
            function letterAlreadyIn($newLetter) {
                if($redis->sismember('proposedLetters', $newLetter)) {
                    return true;
                }
                else {
                    return false;
                }

            }

            //effectue le remplacement de la lettre proposée dans le mot affiché. Retourne le mot 
            //mis à jour
           function replaceInWord($newLetter) {
                 if (isset( $_POST['WORD'])){
                $wordToFind = $redis->get('WordToFind');
                $longueurMot =strlen($wordToFind);
                $wordToDisplay=  $redis->get('WordToDisplay');
                
                for($i = 1 ; $i <= $longueurMot ; $i++) {
                    if ($wordToFind[i] == $newLetter) {
                                $wordToDisplay[i] = $newLetter;
                    }

                }
                $redis->set('WordToDisplay',$wordToDisplay);
                return $wordToDisplay;

            }
        }


          
?>