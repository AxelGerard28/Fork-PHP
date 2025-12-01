<?php

require_once 'inc/database.inc.php';
require_once 'inc/page.inc.php';
function redirect_to_error(string $message): void {
    header('Location: error.php?message=' . urlencode($message));
    exit;
}

$referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';

try {
    $Database = new DatabaseManager(
        dsn: 'mysql:host=mysql;dbname=lowify;charset=utf8mb4',
        username: "lowify",
        password: "lowifypassword"
    );
} catch (PDOException $e) {
    redirect_to_error('Erreur de connexion à la base de données.');
}

$song_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$song_id) {
    redirect_to_error('Identifiant de chanson manquant ou invalide.');
}

$sql_select = "SELECT is_liked FROM song WHERE id = :id";
$song_data = $Database->executeQuery($sql_select, ['id' => $song_id]);

if (empty($song_data)) {
    redirect_to_error('Chanson non trouvée.');
}

$current_liked_status = $song_data[0]['is_liked'];

$new_liked_status = $current_liked_status == 1 ? 0 : 1;

$sql_update = "UPDATE song SET is_liked = :new_status WHERE id = :id";
$Database->executeQuery($sql_update, [
    'new_status' => $new_liked_status,
    'id' => $song_id
]);

header('Location: ' . $referer);
exit;
