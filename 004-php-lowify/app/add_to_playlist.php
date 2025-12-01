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

$song_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
    ?? filter_input(INPUT_POST, 'song_id', FILTER_VALIDATE_INT);
$playlist_id = filter_input(INPUT_POST, 'playlist_id', FILTER_VALIDATE_INT);

if (!$song_id) {
    redirect_to_error('Identifiant de chanson manquant ou invalide.');
}

$sql_song_info = "SELECT name, duration FROM song WHERE id = :id";
$song_data = $Database->executeQuery($sql_song_info, ['id' => $song_id]);
if (empty($song_data)) {
    redirect_to_error('Chanson non trouvée.');
}
$song = $song_data[0];

$sql_playlists = "SELECT id, name FROM playlist ORDER BY name ASC";
$playlists = $Database->executeQuery($sql_playlists);

$message = '';
if (empty($playlists)) {
    $message = '<p class="alert">Vous n\'avez pas encore créé de playlist. <a href="create_playlist.php">Cliquez ici pour en créer une.</a></p>';
}
$disabled_attr = !empty($message) ? 'disabled' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $playlist_id) {
    $sql_check = "SELECT id FROM x_playlist_song WHERE song_id = :song_id AND playlist_id = :playlist_id";
    $exists = $Database->executeQuery($sql_check, ['song_id' => $song_id, 'playlist_id' => $playlist_id]);

    if (!empty($exists)) {
        redirect_to_error('Cette chanson est déjà dans la playlist sélectionnée.');
    }

    try {
        $sql_insert = "INSERT INTO x_playlist_song (song_id, playlist_id) VALUES (:song_id, :playlist_id)";
        $Database->executeQuery($sql_insert, ['song_id' => $song_id, 'playlist_id' => $playlist_id]);
        $sql_update_playlist = "
            UPDATE playlist 
            SET 
                nb_song = nb_song + 1, 
                duration = duration + :duration 
            WHERE id = :playlist_id
        ";
        $Database->executeQuery($sql_update_playlist, [
            'duration' => $song['duration'],
            'playlist_id' => $playlist_id
        ]);

        header('Location: playlist.php?id=' . $playlist_id);
        exit;

    } catch (PDOException $e) {
        redirect_to_error("Erreur SQL lors de l'ajout: " . $e->getMessage());
    }
}

$page = new HTMLPage(title: "Lowify - Ajouter {$song['name']}");

$options = '';
foreach ($playlists as $p) {
    $options .= "<option value=\"{$p['id']}\">{$p['name']}</option>";
}


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
        .form-container h2 {
            font-size: 1.2rem;
            color: #b3b3b3;
            margin-top: -15px;
            margin-bottom: 20px;
            font-weight: normal;
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
        .form-group select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #282828;
            background-color: #333333;
            color: #ffffff;
            box-sizing: border-box;
            height: 44px;
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
        .submit-button:hover:not(:disabled) {
            background-color: #4aff85;
        }
        .submit-button:disabled {
            background-color: #4a4a4a;
            cursor: not-allowed;
        }
        .alert {
            color: #ff4d4d;
            text-align: center;
        }
        .alert a {
            color: #1ed760;
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
        <h1>Ajouter à une Playlist</h1>
        <h2>Chanson : "{$song['name']}"</h2>
        {$message}
        
        <form method="POST">
            <input type="hidden" name="song_id" value="{$song_id}">
            <div class="form-group">
                <label for="playlist_id">Choisir une playlist</label>
                <select id="playlist_id" name="playlist_id" required {$disabled_attr}>
                    <option value="">-- Sélectionner --</option>
                    {$options}
                </select>
            </div>
            
            <button type="submit" class="submit-button" {$disabled_attr}>Ajouter</button>
            
        </form>
        <a href="javascript:history.back()" class="back-link">Annuler</a>
    </div>
HTML;

$page->addContent($html_content);
echo $page->render();