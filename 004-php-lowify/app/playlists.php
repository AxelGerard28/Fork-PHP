<?php

require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

function formatDurationHhMmSs(int $seconds): string {
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    $s = $seconds % 60;
    return sprintf('%02d:%02d:%02d', $h, $m, $s);
}

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

$sql_playlists = "SELECT id, name, duration, nb_song FROM playlist ORDER BY name ASC";
$playlists = $Database->executeQuery($sql_playlists);
$page = new HTMLPage(title: "Lowify - Mes Playlists");

$html = '<h1>Mes Playlists</h1>';
$html .= '<p class="action-link"><a href="index.php" class="button-create">Retour au menu</a></p>';
$html .= '<p class="action-link"><a href="create_playlist.php" class="button-create">Créer une nouvelle playlist</a></p>';

if (!empty($playlists)) {
    $html .= '<div class="playlist-grid">';
    foreach ($playlists as $playlist) {
        $duration_formatted = formatDurationHhMmSs($playlist['duration']);
        $delete_link = "delete_playlist.php?id={$playlist['id']}";
        $detail_link = "playlist.php?id={$playlist['id']}";

        $html .= <<<HTML
        <div class="playlist-card">
            <a href="{$detail_link}" class="playlist-link">
                <h3>{$playlist['name']}</h3>
                <p>{$playlist['nb_song']} chansons</p>
                <p>Durée totale : {$duration_formatted}</p>
            </a>
            <a href="{$delete_link}" class="delete-button" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette playlist ?');">Supprimer</a>
        </div>
HTML;
    }
    $html .= '</div>';
} else {
    $html .= '<p class="no-results">Vous n\'avez aucune playlist pour le moment.</p>';
}

$html_content = <<<HTML
    <style>
        html { height: 100%; }
        body {
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 40%, #5c6c60 80%, #4aff85 100%) fixed; 
            background-size: cover;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #ffffff;
            margin: 0;
            padding: 0;
        }
        .top {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            font-size: 3rem;
            border-bottom: 1px solid #282828;
            padding-bottom: 15px;
            margin-bottom: 30px;
            color: #ffffff;
        }
        a {
            text-decoration: none;
            color: #1ed760;
            transition: color 0.2s;
        }
        a:hover {
            color: #4aff85;
        }
        .action-link {
            margin-bottom: 30px;
        }
        .button-create {
            display: inline-block;
            padding: 10px 20px;
            background-color: #1ed760;
            color: #000000;
            border-radius: 50px;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        .button-create:hover {
            background-color: #4aff85;
        }

        .playlist-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
        }
        .playlist-card {
            flex: 0 0 calc(33.333% - 17px);
            background-color: #1a1a1a;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.5);
            transition: transform 0.2s, background-color 0.2s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .playlist-card:hover {
            transform: translateY(-5px);
            background-color: #282828;
        }
        .playlist-link {
            color: #ffffff;
        }
        .playlist-link h3 {
            margin-top: 0;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        .playlist-link p {
            color: #b3b3b3;
            margin: 5px 0;
        }
        .delete-button {
            margin-top: 15px;
            color: #ff4d4d;
            font-weight: bold;
            text-align: right;
            border-top: 1px solid #282828;
            padding-top: 10px;
            display: block;
        }
        .delete-button:hover {
            color: #ff7777;
        }
        .no-results {
            color: #b3b3b3;
        }
    </style>
    <div class="top">
        {$html}
    </div>
HTML;

$page->addContent($html_content);
echo $page->render();