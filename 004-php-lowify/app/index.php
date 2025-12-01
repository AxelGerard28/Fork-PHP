<?php

require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

function formatMonthlyListeners(int $listeners): string {
    if ($listeners >= 1000000) {
        $value = number_format($listeners / 1000000, 1, '.', '');
        return rtrim($value, '0.') . 'M';
    }
    if ($listeners >= 1000) {
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

$page = new HTMLPage(title: "Lowify - Accueil");

$sqlTrending = "
    SELECT id, name, cover, monthly_listeners 
    FROM artist 
    ORDER BY monthly_listeners DESC 
    LIMIT 5
";
$trendingArtists = $Database->executeQuery($sqlTrending);

$sqlNewReleases = "
    SELECT 
        id, 
        name, 
        cover, 
        YEAR(release_date) as release_year 
    FROM album 
    ORDER BY release_date DESC 
    LIMIT 5
";
$newReleases = $Database->executeQuery($sqlNewReleases);

$sqlTopAlbums = "
    SELECT 
        al.id, 
        al.name, 
        al.cover, 
        AVG(s.note) AS avg_note,
        YEAR(al.release_date) AS release_year
    FROM album al
    JOIN song s ON al.id = s.album_id
    GROUP BY al.id, al.name, al.cover, al.release_date
    ORDER BY avg_note DESC
    LIMIT 5
";
$topAlbums = $Database->executeQuery($sqlTopAlbums);

$playlistLink = '<a href="playlists.php" class="playlist-button">🎵 Mes Playlists</a>';

$searchForm = <<<HTML
<div class="search-section">
    <form action="search.php" method="GET">
        <input type="text" name="query" placeholder="Rechercher artistes, albums ou chansons..." required>
        <button type="submit" class="search-button">Rechercher</button>
    </form>
</div>
HTML;

$trendingHtml = '<h2>Top Trending (Artistes)</h2>';
if (!empty($trendingArtists)) {
    $trendingHtml .= '<div class="card-grid">';
    foreach ($trendingArtists as $artist) {
        $formattedListeners = formatMonthlyListeners($artist['monthly_listeners']);
        $trendingHtml .= <<<HTML
        <div class="card">
            <a href="artist.php?id={$artist['id']}">
                <img src="{$artist['cover']}" alt="Cover de {$artist['name']}">
                <h4>{$artist['name']}</h4>
                <p class="card-subtitle">{$formattedListeners} auditeurs</p>
            </a>
        </div>
HTML;
    }
    $trendingHtml .= '</div>';
}

$newReleasesHtml = '<h2>Top Sorties (Albums)</h2>';
if (!empty($newReleases)) {
    $newReleasesHtml .= '<div class="card-grid">';
    foreach ($newReleases as $album) {
        $newReleasesHtml .= <<<HTML
        <div class="card">
            <a href="album.php?id={$album['id']}">
                <img src="{$album['cover']}" alt="Cover de {$album['name']}">
                <h4>{$album['name']}</h4>
                <p class="card-subtitle">Sortie: {$album['release_year']}</p>
            </a>
        </div>
HTML;
    }
    $newReleasesHtml .= '</div>';
}

$topAlbumsHtml = '<h2>Top Albums</h2>';
if (!empty($topAlbums)) {
    $topAlbumsHtml .= '<div class="card-grid">';
    foreach ($topAlbums as $album) {
        $avgNote = number_format($album['avg_note'], 1);
        $topAlbumsHtml .= <<<HTML
        <div class="card">
            <a href="album.php?id={$album['id']}">
                <img src="{$album['cover']}" alt="Cover de {$album['name']}">
                <h4>{$album['name']}</h4>
                <p class="card-subtitle">Note: {$avgNote} / 10</p>
            </a>
        </div>
HTML;
    }
    $topAlbumsHtml .= '</div>';
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
        
        .playlist-button {
            display: inline-block;
            float: right;
            padding: 10px 20px;
            background-color: #1ed760;
            color: #000000 !important;
            border-radius: 50px;
            font-weight: bold;
            transition: background-color 0.2s;
            margin-top: 25px;
        }
        .playlist-button:hover {
            background-color: #4aff85;
        }
        
        .search-section {
            padding: 30px;
            background-color: #1a1a1a;
            border-radius: 8px;
            margin-bottom: 40px;
            clear: both;
        }
        .search-section form {
            display: flex;
            gap: 10px;
        }
        .search-section input[type="text"] {
            flex-grow: 1;
            padding: 12px 15px;
            border-radius: 50px;
            border: 2px solid #282828;
            background-color: #333333;
            color: #ffffff;
            font-size: 1rem;
        }
        .search-section input[type="text"]::placeholder {
            color: #b3b3b3;
        }
        .search-button {
            padding: 12px 25px;
            background-color: #1ed760;
            color: #000000;
            border: none;
            border-radius: 50px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .search-button:hover {
            background-color: #4aff85;
        }
       
        .card-grid {
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
        }
        @media (max-width: 576px) {
            .card {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }

    </style>
    <div class="top">
        {$playlistLink}
        
        <h1>Lowify - Accueil</h1>
        
        {$searchForm}
        
        {$trendingHtml}
        
        {$newReleasesHtml}
        
        {$topAlbumsHtml}

    </div>
HTML;

$page->addContent($html_content);
echo $page->render();