<?php
//On lance la session
session_start();

require "predis/autoload.php";
Predis\Autoloader::register();

// Connexion à Redis
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

//-------- Création de joueurs --------
$nbPlayers = 4;
//On test si on lance le jeu pour la première fois
if (!isset($_SESSION['gameStarted'])) {

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
    $redis->set('nbTries', "Le jeu n'a pas commencé");

    $_SESSION['playerChoosingWord'] = rand(1, $nbPlayers);
    if ($_SESSION['playerChoosingWord'] != 1) {
        $_SESSION['IsProposingLetter'] = 1;
    } else {
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
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Le PeNdU</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
</head>

<body>

    <nav class="navbar navbar-light bg-light">
        <span class="navbar-brand mb-0 h1">Le PeNdU</span>
        <span class="navbar-text">
            <?php
            $name = $redis->HGET("player" . $i . "", "name");
            print("Bonjour " . $name . " ton score est 10 points !"); ?>
        </span>
    </nav>

    <div class="container">

        <div class="row">
            <div class="col-sm-3">
                <h2>Liste des joueurs</h2>
                <ul>
                    <?php

                    //On affiche dynamiquement une liste la liste des joueurs
                    for ($i = 1; $i <= $nbPlayers; $i++) {
                        echo ("<li>");
                        $playerName = $redis->HGET("player" . $i . "", "name");
                        echo ($playerName);
                        echo ("</li>");
                    }
                    ?>
                </ul>
            </div>
            <div class="col-sm-6">
                <h2>Mot à trouver</h2>

                <?php

                if (isset($_POST['WORD'])) {
                    //on vide d'abord la liste de lettres déjà proposées du mot précédent
                    $proposedLetters = $redis->sMembers('letters');
                    $lenSet = $redis->sCard('letters');
                    for ($i = 0; $i < $lenSet; $i++) {
                        $redis->sRem('letters', $proposedLetters[$i]);
                    }
                    if (!goodWord($_POST['WORD'])) {
                        print("Le mot n'est pas valide, réessayez");
                    } else {
                        $redis->set('WordToFind', $_POST['WORD']);
                        //TTL à 60 secondes
                        $redis->expire('WordToFind', 60);
                        $mot = $redis->get('WordToFind');
                        $longueurmot = strlen($mot);

                        $wordToDisplay = "";

                        for ($i = 1; $i <= $longueurmot; $i++) {

                            $wordToDisplay = $wordToDisplay . "_";
                        }

                        $redis->set('WordToDisplay', $wordToDisplay);
                        $wtd = $redis->get('WordToDisplay');
                        showWordToDisplay($wtd);
                        $redis->set('nbTries', 10);
                    }
                }

                if (isset($_POST['LETTER'])) {

                    if (!goodCharacter($_POST['LETTER'])) {

                        showWordToDisplay(replaceInWord(".", $redis));
                        echo '<br/>';
                        print("La lettre entrée n'est pas valide");
                    } else {

                        $redis->set('newLetter', $_POST['LETTER']);
                        $letterValue = $redis->get('newLetter');


                        //On vérifie que le TTL du mot n'a pas été dépassé 
                        if ($redis->TTL('WordToFind') > 0) {

                            //on vérifie que la lettre n'a pas déjà été proposée
                            if (letterAlreadyIn($letterValue, $redis) == false) {
                                //On vas tester tous les cas pour définir qui est le suivant

                                $redis->decrby('nbTries', 1);
                                $nbTries = $redis->get('nbTries');
                                if ($nbTries == 0) {
                                    $_SESSION['playerChoosingWord']++;
                                    if ($_SESSION['playerChoosingWord'] > $nbPlayers) {
                                        $_SESSION['playerChoosingWord'] = 1;
                                    }
                                    $redis->set('nbTries', 10);
                                }

                                //création + ajout à la base de données dans un Set : lettres proposées
                                $redis->sAdd('letters', $letterValue); //de type Set           
                                if (letterBelongsToWord($letterValue, $redis)) {

                                    //remplacer la lettres dans le mot aux endroits correspondants et afficher
                                    showWordToDisplay(replaceInWord($letterValue, $redis));
                                } else {
                                    showWordToDisplay(replaceInWord(".", $redis));
                                    echo '<br/>';
                                    print("la lettre n'est pas dans le mot");
                                }

                                $_SESSION['IsProposingLetter']++;
                                if ($_SESSION['IsProposingLetter'] == $_SESSION['playerChoosingWord']) {
                                    $_SESSION['IsProposingLetter']++;
                                }
                                if ($_SESSION['IsProposingLetter'] > $nbPlayers) {
                                    if ($_SESSION['playerChoosingWord'] != 1) {
                                        $_SESSION['IsProposingLetter'] = 1;
                                    } else {
                                        $_SESSION['IsProposingLetter'] = 2;
                                    }
                                }
                            } else {
                                showWordToDisplay(replaceInWord(".", $redis));
                                echo '<br/>';
                                echo ("Cette lettre a déjà été proposée");
                            }
                        } else {
                            print("Le temps est écoulé !");
                        }
                    }
                }


                ?>
            </div>
            <div class="col-sm-3">
                <h2>Propositions</h2>
                <ul>
                    <?php foreach ($redis->sMembers('letters') as $letter) {  ?>
                        <li> <?php print($letter) ?> </li>
                    <?php
                    }
                    ?>
                </ul>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <h2>Temps restant</h2>
                <span> <?php print($redis->TTL('WordToFind')); ?> </span>
            </div>
            <div class="col-sm-6">
                <h2>Nombre d'essais restant</h2>
                <span><?php $nbTries = $redis->get('nbTries');
                        if ($nbTries == "Le jeu n'a pas commencé") {
                            print($nbTries);
                        } else {
                            print($nbTries . " essais");
                        }
                        ?></span>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <h2>Proposer une lettre</h2>
                <span>
                    <form method="post" action="index.php">
                        <input type="text" size="3" name="LETTER" />
                        <input type="submit" />
                    </form>
                </span>
            </div>

            <div class="col-sm-6">
                <h2>Proposer un mot</h2>
                <?php
                $playerChoosingWord = $redis->HGET("player" . $_SESSION['playerChoosingWord'] . "", "name");
                echo ("C'est au tour de " . $playerChoosingWord . " de proposer un mot");
                ?>
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

//-------------------------- DEBUG pour afficher qui joue -----------------------------
echo ("<br />");
$playerChoosingWord = $redis->HGET("player" . $_SESSION['playerChoosingWord'], "name");
echo ("Is choosing word");
echo ("<br />");
echo ($playerChoosingWord);
echo ("<br />");
echo ("Are submittign letters");
for ($i = 1; $i <= $nbPlayers; $i++) {
    if ($i != $_SESSION['playerChoosingWord']) {
        echo ("<li>");
        $playerName = $redis->HGET("player" . $i . "", "name");
        echo ($playerName);
        echo ("</li>");
    }
}
echo ("<br />");
echo ("Is submitting letter now");
echo ("<br />");
$playerName = $redis->HGET("player" . $_SESSION['IsProposingLetter'] . "", "name");
echo ($playerName);
//------------------------------------------------------------------------------------


// FONCTIONS OPERANT SUR LA BDD AVEC REDIS --------------------------------------//


//teste si la lettre proposée appartient au mot 
function letterBelongsToWord($letter, $redis)
{
    $word = $redis->get('WordToFind'); //$_SESSION['WORD'];  
    $len = strlen($word);
    for ($i = 0; $i < $len; $i++) {
        if (strcmp($word[$i], $letter) == 0) {
            return true;
            break;
        }
    }
    return false;
}

//teste s'il reste des lettres à trouver dans le mot affiché
function isLettersLasting($displayedWord, $redis)
{
    $len = strlen($displayedWord);
    for ($i = 0; $i < $len; $i++) {
        if (strcmp($redis->getrange('WordToFind', $i, $i), '_') == 0) {
            return true;
            break;
        }
    }
    return false;
}

//teste si la lettre proposée a déjà été proposée
function letterAlreadyIn($newLetter, $redis)
{
    if ($redis->sismember('letters', $newLetter)) {
        return true;
    } else {
        return false;
    }
}

//effectue le remplacement de la lettre proposée dans le mot affiché. Retourne le mot 
//mis à jour
function replaceInWord($newLetter, $redis)
{

    $wordToFind = $redis->get('WordToFind');
    $longueurMot = strlen($wordToFind);

    $wordToDisplay = $redis->get('WordToDisplay');


    for ($i = 0; $i < $longueurMot; $i++) {
        if ((strcmp($wordToFind[$i], $newLetter)) == 0) {
            $wordToDisplay[$i] = $newLetter;
        }
    }
    $redis->set('WordToDisplay', $wordToDisplay);

    return $wordToDisplay;
}

//affiche le mot avec des espaces entre chaque lettre

function showWordToDisplay($wordToDisplay)
{
    for ($i = 0; $i < strlen($wordToDisplay); $i++) {
        print($wordToDisplay[$i] . " ");
    }
}

//verifie si un caractère est valide, retourne un boolean
function goodCharacter($character)
{

    if (preg_match("#[a-zA-Z]#", $character)) {

        return TRUE;
    } else {
        return FALSE;
    }
}

// vérifie si le mot est valide, retourne un boolean
function goodWord($word)
{
    $bool = true;
    for ($i = 0; $i < strlen($word); $i++) {
        if (preg_match("#[a-zA-Z]#", $word)) {
            $bool = true;
        } else {
            $bool = false;
        }
    }
    return $bool;
}

?>