<?php

require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

$Page = new HTMLPage(title: "Lowify - Artistes");

try {

    $Database = new DatabaseManager(
        dsn: 'mysql:host=mysql;dbname=lowify;charset=utf8mb4',
        username: "lowify",
        password: "lowifypassword"
    );
} catch (PDOException $e) {
    echo "Erreur lors de la connexion à la DataBase : " . $e->getMessage();
    exit;
}

$ListArtists = [];
try {
    $ListArtists = $Database->executeQuery("SELECT * FROM artist;");
} catch (PDOException $e) {
    echo "Erreur lors de la requête en base de données : " . $e->getMessage();
    exit;
}



$HTMLArtists = "";




foreach ($ListArtists as $Artist) {
    $ArtistName = $Artist['name'];
    $ArtistCover = $Artist['cover'];
    $ArtistID = $Artist['id'];
    $ArtistBio = $Artist['biography'];


    $HTMLArtists .=<<<HTML
    <div class="cadre-conteneur-colonne">
        <a href="artist.php?id=$ArtistID" class="lien-artiste">
            <div class="cadre-artiste">
                <img src="$ArtistCover" class="image-couverture" alt="$ArtistName">
                <div class="info-details">
                    <h5 class="nom-artiste">$ArtistName</h5>
                    <p class="bio-apercu">$ArtistBio</p>
                </div>
            </div>
        </a>
    </div>
HTML;

}

$html = <<<HTML
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
        }
        
        .Titre {
            color: #ffffff;
            font-size: 2.5rem;
            border-bottom: 1px solid #282828;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        
        .Liste {
            display: flex;
            flex-wrap: wrap; 
            gap: 20px;
        }
        
        .cadre-conteneur-colonne { 
            flex: 0 0 calc(33.333% - 20px); 
            max-width: calc(33.333% - 20px);
            margin-bottom: 20px;
        }
        
        @media (max-width: 992px) {
            .cadre-conteneur-colonne {
                flex: 0 0 calc(50% - 20px);
                max-width: calc(50% - 20px);
            }
        }
        @media (max-width: 576px) {
            .cadre-conteneur-colonne {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
        
        .lien-artiste {
            text-decoration: none;
            color: #ffffff;
            display: block;
        }

        .cadre-artiste {
            background-color: #1a1a1a;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
            transition: transform 0.2s, background-color 0.2s;
            display: flex;
            flex-direction: row; 
            align-items: flex-start;
            height: 100%;
        }

        .cadre-artiste:hover {
            transform: translateY(-3px);
            background-color: #282828;
        }

        .image-couverture {
            width: 80px; 
            height: 80px;
            object-fit: cover; 
            border-radius: 50%;
            border: 3px solid #1ed760; 
            margin-right: 15px;
            flex-shrink: 0;
        }

        .info-details {
            flex-grow: 1; 
            text-align: left;
        }

        .nom-artiste {
            font-size: 1.1rem;
            margin: 0 0 5px 0;
            color: #ffffff;
            font-weight: bold;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .bio-apercu {
            font-size: 0.9rem;
            color: #b3b3b3;
            margin: 0;
            
            display: -webkit-box;
            -webkit-line-clamp: 2; 
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
        <div class="top">
            <a href="index.php">Retour à l'accueil</a>

            <h1 class="Titre">Artistes</h1>
    
            <div class="Liste">
                {$HTMLArtists}
            </div>
        </div>
    HTML;




$Page->addContent($html);
echo $Page->render();