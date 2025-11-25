<?php

$Choices = ['Pierre', 'Feuille', 'Ciseaux'];
$Result = '';
$Player = '';
$Computer = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["choice"])) {
    $Player = $_POST["choice"];
    $Computer = $Choices[array_rand($Choices)];
    if ($Player === $Computer){
        $Result = 'Egalité !!';
    }elseif(
        ($Player === "Pierre" && $Computer === "Ciseaux") ||
        ($Player === "Feuille" && $Computer === "Pierre") ||
        ($Player === "Ciseaux" && $Computer === "Feuille")
    ){
        $Result = 'Gagné !!';
    }else{
        $Result = 'Perdu !!';
    }
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
    background: #f2f2f2;
}

h1 {
    font-size: 2.5rem;
    margin-bottom: 30px;
}

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

form button:hover {
    transform: scale(1.1);
    background: #ddd;
}

.result {
    margin-top: 30px;
    padding: 20px;
    background: white;
    display: inline-block;
    border-radius: 10px;
    box-shadow: 0 0 10px #aaa;
}

.result h2 {
    margin-top: 15px;
}
</style>
</head>
<body>
<h1>Pierre, Feuille Ciseaux</h1>

<form method="POST">
    <button type="submit" name="choice" value="Pierre">Pierre</button>
    <button type="submit" name="choice" value="Feuille">Feuille</button>
    <button type="submit" name="choice" value="Ciseaux">Ciseaux</button>
</form>
</body>
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