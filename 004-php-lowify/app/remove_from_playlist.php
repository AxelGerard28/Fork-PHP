<?php

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

$playlist_id = filter_input(INPUT_GET, 'playlist_id', FILTER_VALIDATE_INT);
$song_id = filter_input(INPUT_GET, 'song_id', FILTER_VALIDATE_INT);

if (!$playlist_id || !$song_id) {
    redirect_to_error('Identifiants manquants ou invalides.');
}

$sql_song_duration = "SELECT duration FROM song WHERE id = :id";
$song_data = $Database->executeQuery($sql_song_duration, ['id' => $song_id]);

if (empty($song_data)) {
    redirect_to_error('Chanson non trouvée.');
}
$song_duration = $song_data[0]['duration'];

try {
    $sql_delete = "
        DELETE FROM x_playlist_song 
        WHERE playlist_id = :playlist_id AND song_id = :song_id
    ";
    $Database->executeQuery($sql_delete, [
        'playlist_id' => $playlist_id,
        'song_id' => $song_id
    ]);

    $sql_update_playlist = "
        UPDATE playlist 
        SET 
            nb_song = nb_song - 1, 
            duration = duration - :duration 
        WHERE id = :playlist_id
    ";
    $Database->executeQuery($sql_update_playlist, [
        'duration' => $song_duration,
        'playlist_id' => $playlist_id
    ]);
    header('Location: playlist.php?id=' . $playlist_id);
    exit;

} catch (PDOException $e) {
    redirect_to_error("Erreur SQL lors du retrait de la playlist : " . $e->getMessage());
}