<?php

require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

function formatDuration(int $seconds): string {
    return gmdate("i:s", $seconds);
}

function formatMonthlyListeners(int $listeners): string {
    if ($listeners >= 1000000) {
        $value = number_format($listeners / 1000000, 1, '.', '');
        return rtrim($value, '0.') . 'M';
    }
    elseif ($listeners >= 1000) {
        $value = number_format($listeners / 1000, 1, '.', '');
        return rtrim($value, '0.') . 'k';
    }
    return (string) $listeners;
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

$artist_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$params = ['id' => $artist_id];


if (!$artist_id) {
    redirect_to_error('Identifiant d\'artiste manquant ou invalide.');
}

$sql_artist = "SELECT * FROM artist WHERE id = :id";
$artist_data = $Database->executeQuery($sql_artist, $params);

if (empty($artist_data)) {
    redirect_to_error('Artiste non trouvé.');
}

$artist = $artist_data[0];
$sql_top_songs = "
    SELECT 
        s.name, 
        s.duration, 
        s.note, 
        a.cover 
    FROM song s
    JOIN album a ON s.album_id = a.id
    WHERE a.artist_id = :id
    ORDER BY s.note DESC
    LIMIT 5
";

$top_songs = $Database->executeQuery($sql_top_songs, $params);

$sql_albums = "
    SELECT id, name, cover, release_date
    FROM album
    WHERE artist_id = :id
    ORDER BY release_date DESC
";

$albums = $Database->executeQuery($sql_albums, $params);
$page = new HTMLPage("Lowify - {$artist['name']}");


$monthly_listeners = formatMonthlyListeners($artist['monthly_listeners']);
$html_artist_detail = <<<HTML
    <div class="artist-header-section">
        <img src="{$artist['cover']}" alt="Cover de {$artist['name']}" class="artist-cover">
        <div class="artist-info-details">
            <h1 class="Titre-header">{$artist['name']}</h1>
            <p class="listeners-count">Auditeurs mensuels : $monthly_listeners</p>
            <p class="biography">{$artist['biography']}</p>
        </div>
    </div>
HTML;

$html_top_songs = '<h2>Top Titres</h2>';
if (!empty($top_songs)) {
    $html_top_songs .= '<table><thead><tr><th>Titre</th><th>Durée</th><th>Note</th></tr></thead><tbody>';
    foreach ($top_songs as $song) {
        $duration_formatted = formatDuration($song['duration']);
        $html_top_songs .= <<<HTML
            <tr>
                <td><img src="{$song['cover']}" alt="Album cover" class="song-cover-small"> {$song['name']}</td>
                <td>$duration_formatted</td>
                <td>{$song['note']} / 5</td>
            </tr>
        HTML;
    }
    $html_top_songs .= '</tbody></table>';
} else {
    $html_top_songs .= '<p>Aucun top titre trouvé.</p>';
}


$html_albums = '<h2>Albums</h2>';
if (!empty($albums)) {
    $html_albums .= '<div class="albums-grid">';


    foreach ($albums as $album) {
        $release_year = date('Y', strtotime($album['release_date']));
        $html_albums .= <<<HTML
            <div class="album-card">
                <a href="album.php?id={$album['id']}">
                    <img src="{$album['cover']}" alt="Cover de {$album['name']}">
                    <h3>{$album['name']}</h3>
                    <p>$release_year</p>
                </a>
            </div>
        HTML;
    }
    $html_albums .= '</div>';
} else {
    $html_albums .= '<p>Aucun album trouvé.</p>';
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
        h1, h2, h3 {
            color: #ffffff;
        }

        .Titre-header, h2 {
            font-size: 2.5rem;
            border-bottom: 1px solid #282828;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .artist-header-section {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 40px;
            background-color: #0d0d0d;
            padding: 30px;
            border-radius: 12px;
        }
        .artist-cover {
            width: 250px;
            height: 250px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 0 20px rgba(30, 215, 96, 0.4);
            flex-shrink: 0;
        }
        .artist-info-details .Titre-header {
            font-size: 3rem;
            margin: 0 0 10px 0;
            border-bottom: none;
            padding-bottom: 0;
        }
        .listeners-count {
            font-size: 1.2rem;
            color: #1ed760;
            margin: 0 0 15px 0;
            font-weight: bold;
        }
        .biography {
            font-size: 1rem;
            color: #b3b3b3;
            line-height: 1.5;
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
        }
        .songs-table th {
            color: #1ed760;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        .songs-table tr:hover {
            background-color: #282828;
        }
        .song-cover-small {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
            vertical-align: middle;
        }
        .song-name-td {
            font-weight: bold;
        }

        .Liste-albums {
            display: flex;
            flex-wrap: wrap; 
            gap: 20px;
        }
        .album-card {
            flex: 0 0 calc(20% - 16px); 
            max-width: calc(20% - 16px);
            margin-bottom: 20px;
            background-color: #1a1a1a;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
            transition: transform 0.2s, background-color 0.2s;
            text-align: center;
        }
        .album-card:hover {
            transform: translateY(-3px);
            background-color: #282828;
        }
        .album-card img {
            width: 100%;
            height: auto;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .album-card h3 {
            font-size: 1rem;
            margin: 0 0 5px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .album-card p {
            font-size: 0.9rem;
            color: #b3b3b3;
            margin: 0;
        }
        @media (max-width: 1200px) {
            .album-card {
                flex: 0 0 calc(25% - 15px);
                max-width: calc(25% - 15px);
            }
        }
        @media (max-width: 992px) {
            .album-card {
                flex: 0 0 calc(33.333% - 13.33px);
                max-width: calc(33.333% - 13.33px);
            }
        }
        @media (max-width: 576px) {
            .album-card {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }

    </style>
    <div class="top">
        <a href="artists.php">← Retour aux artistes</a>
        
        {$html_artist_detail}

        <section class="top-songs-section">
            {$html_top_songs}
        </section>

        <section class="albums-section">
            {$html_albums}
        </section>
    </div>
HTML;

$page->addContent($html_content);
echo $page->render();