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



try {
    $Database = new DatabaseManager(
        dsn: 'mysql:host=mysql;dbname=lowify;charset=utf8mb4',
        username: "lowify",
        password: "lowifypassword"
    );
} catch (PDOException $e) {
    header('Location: error.php?message=' . urlencode('Erreur de connexion à la base de données.'));
    exit;
}


$artist_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$params = ['id' => $artist_id];


if (!$artist_id) {
    header('Location: error.php?message=' . urlencode('Identifiant d\'artiste manquant ou invalide.'));
    exit;
}



$sql_artist = "SELECT * FROM artist WHERE id = :id";
$artist_data = $Database->executeQuery($sql_artist, $params);


if (empty($artist_data)) {
    header('Location: error.php?message=' . urlencode('Artiste non trouvé.'));
    exit;
}
$artist = $artist_data[0];


$sql_top_songs = "
    SELECT s.name, s.duration, s.note, a.cover
    FROM song s
    JOIN album a ON s.album_id = a.id
    WHERE s.artist_id = :id
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


// Détail Artiste
$monthly_listeners_formatted = formatMonthlyListeners($artist['monthly_listeners']);

$html_artist_detail = <<<HTML
    <div class="artist-header">
        <img src="{$artist['cover']}" alt="Cover de {$artist['name']}" class="artist-cover">
        <div>
            <h1>{$artist['name']}</h1>
            <p><strong>Auditeurs mensuels :</strong> {$monthly_listeners_formatted}</p>
            <p>{$artist['biography']}</p>
        </div>
    </div>
HTML;

// Top Titres
$html_top_songs = '<h2>Top Titres</h2>';
if (!empty($top_songs)) {
    $html_top_songs .= '<table><thead><tr><th>Titre</th><th>Durée</th><th>Note</th></tr></thead><tbody>';
    foreach ($top_songs as $song) {
        $duration_formatted = formatDuration($song['duration']);
        $html_top_songs .= <<<HTML
            <tr>
                <td><img src="{$song['cover']}" alt="Album cover" class="song-cover-small"> {$song['name']}</td>
                <td>{$duration_formatted}</td>
                <td>{$song['note']} / 5</td>
            </tr>
        HTML;
    }
    $html_top_songs .= '</tbody></table>';
} else {
    $html_top_songs .= '<p>Aucun top titre trouvé.</p>';
}


// Albums
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
                    <p>{$release_year}</p>
                </a>
            </div>
        HTML;
    }
    $html_albums .= '</div>';
} else {
    $html_albums .= '<p>Aucun album trouvé.</p>';
}

$html_content = $html_artist_detail . $html_top_songs . $html_albums;

$page->addContent($html_content);
echo $page->render();