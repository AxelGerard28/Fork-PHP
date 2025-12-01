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

$playlist_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$playlist_id) {
    redirect_to_error('Identifiant de playlist manquant ou invalide.');
}

try {
    $sql_delete_associations = "DELETE FROM x_playlist_song WHERE playlist_id = :id";
    $Database->executeQuery($sql_delete_associations, ['id' => $playlist_id]);
    $sql_delete_playlist = "DELETE FROM playlist WHERE id = :id";
    $Database->executeQuery($sql_delete_playlist, ['id' => $playlist_id]);
    header('Location: playlists.php');
    exit;

} catch (PDOException $e) {
    redirect_to_error("Erreur lors de la suppression de la playlist : " . $e->getMessage());
}