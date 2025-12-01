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

$query = filter_input(INPUT_GET, 'query', FILTER_SANITIZE_SPECIAL_CHARS);

if (empty($query)) {
    redirect_to_error('Veuillez entrer un terme de recherche.');
}

$searchParam = '%' . $query . '%';
$params = ['search' => $searchParam];

$sqlArtists = "SELECT id, name, cover FROM artist WHERE name LIKE :search";
$artists = $Database->executeQuery($sqlArtists, $params);

$sqlAlbums = "
    SELECT 
        al.id, 
        al.name AS album_name, 
        al.cover, 
        YEAR(al.release_date) as release_year,
        ar.name AS artist_name
    FROM album al
    JOIN artist ar ON al.artist_id = ar.id
    WHERE al.name LIKE :search
";
$albums = $Database->executeQuery($sqlAlbums, $params);

$sqlSongs = "
    SELECT 
        s.id,
        s.name AS song_name, 
        s.duration, 
        s.note,
        al.id AS album_id,
        al.name AS album_name,
        ar.id AS artist_id,
        ar.name AS artist_name
    FROM song s
    JOIN album al ON s.album_id = al.id
    JOIN artist ar ON al.artist_id = ar.id
    WHERE s.name LIKE :search
";
$songs = $Database->executeQuery($sqlSongs, $params);

$page = new HTMLPage(title: "Lowify - Résultats pour \"{$query}\"");

$html = "<h1>Résultats de recherche pour \"{$query}\"</h1>";
$hasResults = false;

$html .= '<h2>Artistes</h2>';
if (!empty($artists)) {
    $hasResults = true;
    $html .= '<div class="results-grid artists-grid">';
    foreach ($artists as $artist) {
        $html .= <<<HTML
        <div class="card">
            <a href="artist.php?id={$artist['id']}">
                <img src="{$artist['cover']}" alt="Cover de {$artist['name']}">
                <h4>{$artist['name']}</h4>
            </a>
        </div>
HTML;
    }
    $html .= '</div>';
} else {
    $html .= '<p class="no-results">Aucun artiste trouvé.</p>';
}

$html .= '<h2>Albums</h2>';
if (!empty($albums)) {
    $hasResults = true;
    $html .= '<div class="results-grid albums-grid">';
    foreach ($albums as $album) {
        $html .= <<<HTML
        <div class="card">
            <a href="album.php?id={$album['id']}">
                <img src="{$album['cover']}" alt="Cover de {$album['album_name']}">
                <h4>{$album['album_name']}</h4>
                <p class="card-subtitle">Par {$album['artist_name']} ({$album['release_year']})</p>
            </a>
        </div>
HTML;
    }
    $html .= '</div>';
} else {
    $html .= '<p class="no-results">Aucun album trouvé.</p>';
}

$html .= '<h2>Chansons</h2>';
if (!empty($songs)) {
    $hasResults = true;
    $html .= '<table class="songs-table"><thead><tr><th>Titre</th><th>Durée</th><th>Note</th><th>Album</th><th>Artiste</th></tr></thead><tbody>';
    foreach ($songs as $song) {
        $durationFormatted = formatDuration($song['duration']);
        $albumLink = "album.php?id={$song['album_id']}";
        $artistLink = "artist.php?id={$song['artist_id']}";

        $html .= <<<HTML
            <tr>
                <td>{$song['song_name']}</td>
                <td>{$durationFormatted}</td>
                <td>{$song['note']} / 10</td>
                <td><a href="{$albumLink}">{$song['album_name']}</a></td>
                <td><a href="{$artistLink}">{$song['artist_name']}</a></td>
            </tr>
        HTML;
    }
    $html .= '</tbody></table>';
} else {
    $html .= '<p class="no-results">Aucune chanson trouvée.</p>';
}

if (!$hasResults) {
    $html .= "<p>Désolé, aucun résultat n'a été trouvé pour votre recherche.</p>";
}

$html_content = <<<HTML
    <style>
        body {
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 30%, #5c6c60 70%, #4aff85 100%);
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
        h1 {
            font-size: 3rem;
            border-bottom: 1px solid #282828;
            padding-bottom: 15px;
            margin-bottom: 30px;
            color: #ffffff;
        }
        h2 {
            font-size: 2rem;
            margin-top: 40px;
            margin-bottom: 20px;
            color: #ffffff;
        }
        .no-results {
            color: #b3b3b3;
            font-style: italic;
        }
        .results-grid {
            display: flex;
            flex-wrap: wrap; 
            gap: 20px;
            margin-bottom: 40px;
        }
        .card {
            flex: 0 0 calc(20% - 16px); 
            max-width: calc(20% - 16px);
            background-color: #1a1a1a;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
            transition: transform 0.2s, background-color 0.2s;
            text-align: center;
        }
        .card:hover {
            transform: translateY(-5px);
            background-color: #282828;
        }
        .card a {
            color: #ffffff;
            display: block;
        }
        .card img {
            width: 100%;
            height: auto;
            object-fit: cover; 
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .card h4 {
            font-size: 1.1rem;
            margin: 0 0 5px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .card-subtitle {
            font-size: 0.9rem;
            color: #b3b3b3;
            margin: 0;
        }
        @media (max-width: 1200px) {
            .card {
                flex: 0 0 calc(25% - 15px);
                max-width: calc(25% - 15px);
            }
        }
        @media (max-width: 992px) {
            .card {
                flex: 0 0 calc(33.333% - 13.33px);
                max-width: calc(33.333% - 13.33px);
            }
            .songs-table th, .songs-table td {
                padding: 8px 10px;
            }
        }
        @media (max-width: 576px) {
            .card {
                flex: 0 0 100%;
                max-width: 100%;
            }
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
        
    </style>
    <div class="top">
        <a href="index.php">← Retour à l'accueil</a>
        
        {$html}

    </div>
HTML;

$page->addContent($html_content);
echo $page->render();