<?php


require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

function formatDurationMmSs(int $seconds): string {
    return gmdate("i:s", $seconds);
}

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

$playlist_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$params = ['id' => $playlist_id];

if (!$playlist_id) {
    redirect_to_error('Identifiant de playlist manquant ou invalide.');
}

$sql_playlist = "SELECT name, duration, nb_song FROM playlist WHERE id = :id";
$playlist_data = $Database->executeQuery($sql_playlist, $params);

if (empty($playlist_data)) {
    redirect_to_error('Playlist non trouvée.');
}
$playlist = $playlist_data[0];
$duration_formatted_hhmmss = formatDurationHhMmSs($playlist['duration']);

$sql_songs = "
    SELECT 
        s.id AS song_id,
        s.name AS song_name,
        s.duration,
        s.note,
        al.id AS album_id,
        al.name AS album_name,
        ar.id AS artist_id,
        ar.name AS artist_name
    FROM x_playlist_song xps
    JOIN song s ON xps.song_id = s.id
    JOIN album al ON s.album_id = al.id
    JOIN artist ar ON al.artist_id = ar.id
    WHERE xps.playlist_id = :id
    ORDER BY s.name ASC
";
$songs = $Database->executeQuery($sql_songs, $params);

$page = new HTMLPage(title: "Lowify - {$playlist['name']}");

$html = <<<HTML
    <a href="playlists.php">← Retour aux playlists</a>
    <div class="playlist-header">
        <h1>{$playlist['name']}</h1>
        <p>🎵 {$playlist['nb_song']} chansons</p>
        <p>⏳ Durée totale : {$duration_formatted_hhmmss}</p>
    </div>
HTML;

$html .= '<h2>Liste des titres</h2>';

if (!empty($songs)) {
    $html .= '<table class="songs-table"><thead><tr><th>Titre</th><th>Durée</th><th>Note</th><th>Album</th><th>Artiste</th><th>Action</th></tr></thead><tbody>';
    foreach ($songs as $song) {
        $duration_formatted_mmss = formatDurationMmSs($song['duration']);
        $album_link = "album.php?id={$song['album_id']}";
        $artist_link = "artist.php?id={$song['artist_id']}";
        $remove_link = "remove_from_playlist.php?playlist_id={$playlist_id}&song_id={$song['song_id']}";

        $html .= <<<HTML
            <tr>
                <td>{$song['song_name']}</td>
                <td>{$duration_formatted_mmss}</td>
                <td>{$song['note']} / 10</td>
                <td><a href="{$album_link}">{$song['album_name']}</a></td>
                <td><a href="{$artist_link}">{$song['artist_name']}</a></td>
                <td><a href="{$remove_link}" class="remove-button">Retirer</a></td>
            </tr>
        HTML;
    }
    $html .= '</tbody></table>';
} else {
    $html .= '<p>Cette playlist est vide.</p>';
}

$html_content = <<<HTML
    <style>
        html {
            height: 100%;
        }
        body {
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 40%, #5c6c60 80%, #4aff85 100%) fixed;
            background-size: cover;
            min-height: 100%;
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
        a {
            text-decoration: none;
            color: #1ed760;
            transition: color 0.2s;
        }
        a:hover {
            color: #4aff85;
        }
        .playlist-header {
            border-bottom: 1px solid #282828;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .playlist-header h1 {
            font-size: 3rem;
            margin-bottom: 5px;
        }
        .playlist-header p {
            font-size: 1.1rem;
            color: #b3b3b3;
            margin: 5px 0;
        }
        h2 {
            font-size: 2rem;
            margin-top: 40px;
            margin-bottom: 20px;
            color: #ffffff;
        }
        .songs-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .songs-table th, .songs-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #282828;
            color: #ffffff;
        }
        .songs-table th {
            color: #1ed760;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        .songs-table tr:hover {
            background-color: #282828;
        }
        .songs-table td a {
            color: #b3b3b3;
        }
        .songs-table td a:hover {
            color: #1ed760;
        }
        .remove-button {
            color: #ff4d4d;
            font-weight: bold;
        }
        .remove-button:hover {
            color: #ff7777;
        }
    </style>
    <div class="top">
        {$html}
    </div>
HTML;

$page->addContent($html_content);
echo $page->render();