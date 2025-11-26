<?php

function generateSelectOptions(int $selected = 12): string
{
    $html = "";
    $options = range(8, 42);

    foreach ($options as $value) {
        $attribute = ((int)$value === $selected) ? "selected" : "";
        $html .= "<option value=\"$value\" $attribute>$value</option>";
    }

    return $html;
}

function generatePassword($Length, $UseLower, $UseUpper, $UseNumber, $UseSymbols)
{
    $Upper   = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $Lower   = "abcdefghijklmnopqrstuvwxyz";
    $Number  = "0123456789";
    $Symbols = "!@#$%^&*()_+-={}[]";

    $Chars = "";
    if ($UseLower)   $Chars .= $Lower;
    if ($UseUpper)   $Chars .= $Upper;
    if ($UseNumber)  $Chars .= $Number;
    if ($UseSymbols) $Chars .= $Symbols;

    if ($Chars === "") return "Veuillez sélectionner une case.";

    $Password = "";
    for ($i = 0; $i < $Length; $i++) {
        $Password .= $Chars[random_int(0, strlen($Chars) - 1)];
    }
    return $Password;
}

$generatedPassword = "";
$selectedLength = 12;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $selectedLength = intval($_POST["length"]);
    $UseLower   = isset($_POST["lower"]);
    $UseUpper   = isset($_POST["upper"]);
    $UseNumber  = isset($_POST["number"]);
    $UseSymbols = isset($_POST["symbols"]);

    $generatedPassword = generatePassword($selectedLength, $UseLower, $UseUpper, $UseNumber, $UseSymbols);
}

$optionsHTML = generateSelectOptions($selectedLength);


$html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Générateur de mot de passe</title>

<style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f4f4;
        display: flex;
        justify-content: center;
        margin-top: 50px;
    }
    .container {
        background: white;
        padding: 25px;
        width: 380px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    h2 {
        text-align: center;
        margin-bottom: 20px;
    }
    .Password-Box {
        width: 100%;
        padding: 12px;
        border: 1px solid #333;
        border-radius: 6px;
        font-weight: bold;
        text-align: center;
        background: #eee;
        margin-bottom: 20px;
        font-size: 1.1em;
    }
    form label {
        display: block;
        margin-top: 10px;
        font-weight: bold;
    }
    form input[type="checkbox"] {
        margin-right: 8px;
    }
    select {
        padding: 6px;
        width: 100%;
        border-radius: 5px;
        margin-top: 5px;
    }
    button {
        display: block;
        width: 100%;
        padding: 10px;
        background: #007bff;
        color: white;
        border: none;
        font-size: 1em;
        border-radius: 6px;
        margin-top: 15px;
        cursor: pointer;
    }
    button:hover {
        background: #0056b3;
    }
</style>

</head>
<body>

<div class="container">
    <h2>Générateur de mot de passe</h2>

    <div class="Password-Box">$generatedPassword</div>

    <form method="POST">

        <label>Longueur du mot de passe :</label>
        <select name="length">
            $optionsHTML
        </select>

        <label><input type="checkbox" name="lower" checked> Lettres minuscules</label>
        <label><input type="checkbox" name="upper"> Lettres majuscules</label>
        <label><input type="checkbox" name="number"> Chiffres</label>
        <label><input type="checkbox" name="symbols"> Symboles</label>

        <button type="submit">Générer</button>
    </form>
</div>

</body>
</html>
HTML;

echo $html;
