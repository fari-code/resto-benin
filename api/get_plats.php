<?php
// Permettre l'accès depuis le Frontend (CORS) si les dossiers sont séparés
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Inclusion de ton fichier de configuration de la base de données
require_once '../config/db.php';

try {
    // Requête SQL pour récupérer les plats actifs/disponibles avec leur catégorie
    $query = "SELECT p.id_plat AS id, 
                     p.nom AS titre, 
                     p.prix, 
                     p.description AS `desc`, 
                     p.image AS img, 
                     c.nom AS categorie
              FROM plats p
              INNER JOIN categories_plats c ON p.id_categorie = c.id_categorie
              WHERE p.disponible = 1
              ORDER BY c.nom, p.nom ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $plats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Forcer les types pour correspondre au JavaScript (prix en nombre)
    // Définir le chemin vers le dossier d'images du back-office
    // Si ton frontend et ton backend partagent le même domaine, un chemin relatif remontant au dossier parent fonctionne :
    $chemin_backend_images = '../public/dist/assets/img/';

    foreach ($plats as &$plat) {
        $plat['id'] = (int)$plat['id'];
        $plat['prix'] = (float)$plat['prix'];

        // Si le plat a une image enregistrée par le back-office
        if (!empty($plat['img'])) {
            $plat['img'] = $chemin_backend_images . trim($plat['img']);
        } else {
            // Image de secours dans le dossier img du backend ou fallback sur le dossier frontend
            $plat['img'] = $chemin_backend_images . 'food.png';
        }
    }

    // On renvoie le tableau encodé en JSON
    echo json_encode($plats, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (\PDOException $e) {
    // En cas d'erreur, renvoyer un code HTTP 500 et le message
    http_response_code(500);
    echo json_encode([
        "error" => true,
        "message" => "Erreur lors de la récupération du menu : " . $e->getMessage()
    ]);
}
