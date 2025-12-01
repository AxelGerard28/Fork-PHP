<?php

require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

function redirect_to_error(string $message): void {
    header('Location: error.php?message=' . urlencode($message));
    exit;
}

try {
    $Database = new DatabaseManager(
        dsn: 'mysql:host=mysql;dbname=lowify;charset=utf8mb4',
        username: "lowify",
        password: "lowifypassword"
    );
} catch (PDOException $e) {
    redirect_to_error('Erreur de connexion à la base de données.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $playlist_name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($playlist_name)) {
        redirect_to_error('Le nom de la playlist ne peut pas être vide.');
    }
    $sql_insert = "INSERT INTO playlist (name) VALUES (:name)";
    $Database->executeQuery($sql_insert, ['name' => $playlist_name]);

    header('Location: playlists.php');
    exit;
}
$page = new HTMLPage(title: "Lowify - Créer une Playlist");

$html_content = <<<HTML
    <style>
        body {
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 30%, #5c6c60 70%, #4aff85 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #ffffff;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .form-container {
            max-width: 450px;
            width: 90%;
            padding: 40px;
            background-color: #1a1a1a;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
        }
        .form-container h1 {
            color: #1ed760;
            font-size: 2rem;
            margin-top: 0;
            margin-bottom: 25px;
            border-bottom: 1px solid #282828;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #ffffff;
        }
        .form-group input[type="text"] {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #282828;
            background-color: #333333;
            color: #ffffff;
            box-sizing: border-box;
        }
        .form-group input[type="text"]:focus {
            outline: none;
            border-color: #1ed760;
            box-shadow: 0 0 5px rgba(30, 215, 96, 0.5);
        }
        .submit-button {
            width: 100%;
            padding: 12px;
            background-color: #1ed760;
            color: #000000;
            border: none;
            border-radius: 50px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s;
            text-transform: uppercase;
        }
        .submit-button:hover {
            background-color: #4aff85;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #b3b3b3;
            text-decoration: none;
        }
    </style>
    <div class="form-container">
        <h1>Créer une Playlist</h1>
        <form method="POST">
            <div class="form-group">
                <label for="name">Nom de la playlist</label>
                <input type="text" id="name" name="name" required>
            </div>
            <button type="submit" class="submit-button">Créer</button>
        </form>
        <a href="playlists.php" class="back-link">Annuler</a>
    </div>
HTML;

$page->addContent($html_content);
echo $page->render();