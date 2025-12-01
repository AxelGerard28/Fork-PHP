<?php

require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

function formatDuration(int $seconds): string {
    return gmdate("i:s", $seconds);
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

$album_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$params = ['id' => $album_id];

if (!$album_id) {
    redirect_to_error('Identifiant d\'album manquant ou invalide.');
}

$sql_album_artist = "
    SELECT 
        al.id, 
        al.name AS album_name, 
        al.cover, 
        al.release_date,
        ar.id AS artist_id, 
        ar.name AS artist_name
    FROM album al
    JOIN artist ar ON al.artist_id = ar.id
    WHERE al.id = :id
";
$album_data = $Database->executeQuery($sql_album_artist, $params);

if (empty($album_data)) {
    redirect_to_error('Album non trouvé.');
}
$album = $album_data[0];
$release_year = date('Y', strtotime($album['release_date']));

$sql_songs = "
    SELECT 
        id, 
        name, 
        duration, 
        note,
        is_liked
    FROM song 
    WHERE album_id = :id 
    ORDER BY id ASC
";
$songs = $Database->executeQuery($sql_songs, $params);

$page = new HTMLPage(title: "Lowify - {$album['album_name']}");

$artist_link = "artist.php?id={$album['artist_id']}";
$html_album_detail = <<<HTML
    <div class="album-header-section">
        <img src="{$album['cover']}" alt="Cover de {$album['album_name']}" class="album-cover-lg">
        <div class="album-info-details">
            <p class="type-header">ALBUM</p>
            <h1 class="Titre-header">{$album['album_name']}</h1>
            <p class="artist-link">Par : <a href="{$artist_link}">{$album['artist_name']}</a></p>
            <p class="release-date">Date de sortie : {$album['release_date']} ({$release_year})</p>
        </div>
    </div>
HTML;

$html_songs = '<h2>Titres de l\'album</h2>';
if (!empty($songs)) {
    $html_songs .= '<table class="songs-table"><thead><tr><th>#</th><th>Titre</th><th>Durée</th><th>Note</th><th>Action</th></tr></thead><tbody>';
    $track_number = 1;
    foreach ($songs as $song) {
        $duration_formatted = formatDuration($song['duration']);
        $like_link = "like_song.php?id={$song['id']}";
        $add_to_playlist_link = "add_to_playlist.php?id={$song['id']}";
        $like_icon = $song['is_liked'] == 1 ? '💖' : '🤍';

        $html_songs .= <<<HTML
            <tr>
                <td>{$track_number}</td>
                <td>{$song['name']}</td>
                <td>{$duration_formatted}</td>
                <td>{$song['note']} / 10</td>
                <td>
                    <a href="{$like_link}" title="Liker/Délìker">{$like_icon}</a>
                    <a href="{$add_to_playlist_link}" title="Ajouter à une playlist">⊕</a>
                </td>
            </tr>
        HTML;
        $track_number++;
    }
    $html_songs .= '</tbody></table>';
} else {
    $html_songs .= '<p>Aucun titre trouvé dans cet album.</p>';
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
        a {
            text-decoration: none;
            color: #1ed760;
            transition: color 0.2s;
        }
        a:hover {
            color: #4aff85;
        }
        h1, h2, h3 {
            color: #ffffff;
        }
        .Titre-header, h2 {
            font-size: 2.5rem;
            border-bottom: 1px solid #282828;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .album-header-section {
            display: flex;
            align-items: flex-end;
            gap: 30px;
            margin-bottom: 40px;
            background: linear-gradient(to top, #1a1a1a 0%, #2c2c2c 100%);
            padding: 30px;
            border-radius: 12px;
        }
        .album-cover-lg {
            width: 250px;
            height: 250px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            flex-shrink: 0;
        }
        .album-info-details {
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }
        .album-info-details .Titre-header {
            font-size: 4rem;
            margin: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        .type-header {
            color: #1ed760;
            font-size: 1rem;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .artist-link, .release-date {
            font-size: 1.2rem;
            color: #b3b3b3;
            margin-top: 5px;
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
        .songs-table td:first-child {
             width: 50px;
             color: #b3b3b3;
        }
        .songs-table tr:hover {
            background-color: #282828;
        }
        .songs-table td a {
            color: #b3b3b3;
            text-decoration: none;
            margin-right: 10px;
            font-size: 1.2rem;
        }
        .songs-table td a[title*="Liker/Délìker"] {
            margin-right: 5px;
        }
        
    </style>
    <div class="top">
        <a href="{$artist_link}">← Retour à l'artiste</a>
        
        {$html_album_detail}

        <section class="songs-section">
            {$html_songs}
        </section>
    </div>
HTML;

$page->addContent($html_content);
echo $page->render();