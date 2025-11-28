<?php

require_once('inc/page.inc.php');
require_once('inc/database.inc.php');

try {
    $Database = new DatabaseManager(
        dsn: 'mysql:host=mysql;dbname=lowify;charset=utf8mb4',
        username: "lowify",
        password: "lowifypassword"
    );
} catch (PDOException $e) {
    echo "erreur lors de la connexion à la DataBase" . $e->getMessage();
    exit;
}

$ListArtists = [];
try {
    $ListArtists = $Database->executeQuery("SELECT * FROM artist;");
} catch (PDOException $e) {
    echo "Erreur lors de la requête en base de données : " . $e->getMessage();
    exit;
}


$HTMLArtists = [];


foreach ($ListArtists as $Artist) {
    $ArtistName = $Artist['name'];
    $ArtistCover = $Artist['cover'];
    $ArtistID = $Artist['id'];

    $HTMLArtists .=<<<HTML
    <div class="col-lg-3 col-md-6 mb-4">
                <a href="artist.php?id=$ArtistID" class="text-decoration-none text-white">
                    <div class="card h-100 bg-dark text-white border-dark shadow">
                        <img src="$ArtistCover" class="card-img-top rounded-circle" alt="Image 1">
                        <div class="card-body bg-secondary-subtle  text-white">
                            <h5 class="card-title">$ArtistName</h5>
                        </div>
                    </div>
                </a>
            </div>
    HTML;

}

$html = <<<HTML
        <div class="container bg-dark text-white p-4">
            <a href="index.php" class="link text-white"> < Retour à l'accueil</a>

            <h1 class="mb-4">Artistes</h1>
    
            <div>
            {$HTMLArtists}
            </div>
        </div>
    HTML;




$Page = new HTMLPage(title: "Lowify - Artistes");
$Page->addContent($html);
echo $Page->render();

