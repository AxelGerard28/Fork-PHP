<?php
session_start(); // Nécessaire pour conserver les compteurs entre les coups

// Initialisation des compteurs si pas encore définis
if (!isset($_SESSION['win']))  $_SESSION['win'] = 0;
if (!isset($_SESSION['lose'])) $_SESSION['lose'] = 0;
if (!isset($_SESSION['draw'])) $_SESSION['draw'] = 0;
if (!isset($_SESSION['total'])) $_SESSION['total'] = 0;

$Choices = ['Pierre', 'Feuille', 'Ciseaux', 'Lézard', 'Spock'];
$Result = '';
$Player = '';
$Computer = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["choice"])) {
    $Player = $_POST["choice"];
    $Computer = $Choices[array_rand($Choices)];

    if ($Player === $Computer) {
        $Result = 'Egalité !!';
        $_SESSION['draw']++;
        $_SESSION['total']++;
    } elseif (
        ($Player === "Pierre" && $Computer === "Ciseaux") ||
        ($Player === "Pierre" && $Computer === "Lézard") ||
        ($Player === "Feuille" && $Computer === "Pierre") ||
        ($Player === "Feuille" && $Computer === "Spock") ||
        ($Player === "Ciseaux" && $Computer === "Feuille") ||
        ($Player === "Ciseaux" && $Computer === "Lézard") ||
        ($Player === "Lézard" && $Computer === "Feuille") ||
        ($Player === "Lézard" && $Computer === "Spock") ||
        ($Player === "Spock" && $Computer === "Pierre") ||
        ($Player === "Spock" && $Computer === "Ciseaux")
    ) {
        $Result = 'Gagné !!';
        $_SESSION['win']++;
        $_SESSION['total']++;
    } else {
        $Result = 'Perdu !!';
        $_SESSION['lose']++;
        $_SESSION['total']++;
    }
}

// Reset du score si demandé
if (isset($_POST["reset"])) {
    $_SESSION['win'] = $_SESSION['lose'] = $_SESSION['draw'] = $_SESSION['total'] = 0;
}

$html = <<< HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {
    font-family: Arial, sans-serif;
    text-align: center;
    margin-top: 50px;
    background: grey;
}
h1 { font-size: 2.5rem; margin-bottom: 30px; }
form button {
    padding: 15px 25px;
    font-size: 20px;
    margin: 10px;
    cursor: pointer;
    border: none;
    border-radius: 8px;
    transition: 0.2s;
    background: beige;
    box-shadow: 0 0 10px #aaa;
}
form button:hover { transform: scale(1.1); background: #ddd; }

.result {
    margin-top: 30px;
    padding: 20px;
    background: white;
    display: inline-block;
    border-radius: 10px;
    box-shadow: 0 0 10px #aaa;
}

.score {
    margin-top: 25px;
    padding: 15px;
    background: white;
    display: inline-block;
    border-radius: 10px;
    box-shadow: 0 0 10px #aaa;
    font-size: 1.2rem;
}
</style>
</head>
<body>
<h1>Pierre, Feuille Ciseaux</h1>

<form method="POST">
    <button type="submit" name="choice" value="Pierre">Pierre</button>
    <button type="submit" name="choice" value="Feuille">Feuille</button>
    <button type="submit" name="choice" value="Ciseaux">Ciseaux</button>
    <button type="submit" name="choice" value="Lézard">Lézard</button>
    <button type="submit" name="choice" value="Spock">Spock</button>
</form>

<div class="score">
    <p>Total partie : <strong>{$_SESSION['total']}</strong></p>
    <p>Victoires : <strong>{$_SESSION['win']}</strong></p>
    <p>Défaites : <strong>{$_SESSION['lose']}</strong></p>
    <p>Égalités : <strong>{$_SESSION['draw']}</strong></p>
    
    <form method="POST">
        <button type="submit" name="reset" style="background:#ffb3b3;">Reinitialiser Le Score</button>
    </form>
</div>
HTML;

if ($Result !== "") {
    $html .= "
    <div class='result'>
        <p> Tu as choisi : <strong>" . htmlspecialchars($Player) . "</strong></p>
        <p> L'ordinateur a choisi : <strong>$Computer</strong></p>
        <h2>$Result</h2>
    </div>
    ";
}

$html .= "</body></html>";

echo $html;
