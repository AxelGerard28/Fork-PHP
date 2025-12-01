<?php

require_once 'inc/page.inc.php';

$errorMessage = filter_input(INPUT_GET, 'message', FILTER_SANITIZE_SPECIAL_CHARS);
if (empty($errorMessage)) {
    $errorMessage = "Une erreur inconnue est survenue.";
}

$page = new HTMLPage('Lowify - Erreur');

$htmlContent = <<<HTML
    <style>
        body {
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 30%, #5c6c60 70%, #4aff85 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #ffffff;
            margin: 0;
            padding: 0;
            background-size: cover;
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .error-container {
            max-width: 500px;
            width: 90%;
            padding: 40px;
            background-color: #111111; 
            
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.7); 
            border: 1px solid #282828; 
        }
        
        .error-container h1 {
            color: #ff4d4d;
            font-size: 3rem;
            margin-bottom: 15px;
        }
        .error-container p {
            font-size: 1.1rem;
            color: #b3b3b3;
            margin-bottom: 40px;
        }
        .error-container .button {
            padding: 12px 25px;
            background-color: #1ed760;
            color: #000000;
            text-decoration: none;
            border: none;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: background-color 0.2s, transform 0.2s;
            display: inline-block;
        }
        .error-container .button:hover {
            background-color: #4aff85;
            transform: translateY(-2px);
        }
    </style>
    <div class="error-container">
        <h1>Erreur</h1>
        <p>{$errorMessage}</p>
        <a href="index.php" class="button">RETOUR À L'ACCUEIL</a>
    </div>
HTML;

$page->addContent($htmlContent);
echo $page->render();