<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée"]);
    exit();
}

require_once '../config/db.php';

// ==============================================================================
// ÉTAPE 2 : INCLUSION DE LA FONCTION D'EMAIL
// Vérifie que le chemin ci-dessous correspond bien à l'emplacement de ton étape 1
// ==============================================================================
require_once '../includes/emails.php'; 

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Données JSON invalides"]);
    exit();
}

// Validation des champs
if (empty($data['nom_client']) || empty($data['telephone']) || empty($data['email']) || empty($data['date']) || empty($data['heure']) || empty($data['nb_personnes'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Tous les champs sont requis"]);
    exit();
}

try {
    // Formater la date et l'heure
    $date_heure = $data['date'] . ' ' . $data['heure'] . ':00';
    
    // Trouver une table disponible selon le nombre de personnes
    $sqlFindTable = "SELECT id_table, numero, capacite FROM tables_restaurant 
                     WHERE capacite >= ? AND statut = 'libre' 
                     ORDER BY capacite ASC LIMIT 1";
    $stmtFind = $pdo->prepare($sqlFindTable);
    $stmtFind->execute([$data['nb_personnes']]);
    $table = $stmtFind->fetch();
    
    if (!$table) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Désolé, aucune table disponible pour " . $data['nb_personnes'] . " personnes à cette heure"]);
        exit();
    }
    
    // Préparation des variables pour l'insertion et l'email
    $statut_reservation = 'confirmee';
    $commentaire = isset($data['commentaire']) ? $data['commentaire'] : null;
    
    // Créer la réservation
    $sql = "INSERT INTO reservations (nom_client, telephone_client, email, id_table, date_heure, nb_couverts, statut, commentaire) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['nom_client'],
        $data['telephone'],
        $data['email'],
        $table['id_table'],
        $date_heure,
        $data['nb_personnes'],
        $statut_reservation,
        $commentaire
    ]);
    
    $id_reservation = $pdo->lastInsertId();
    
    // Mettre à jour le statut de la table
    $sqlUpdateTable = "UPDATE tables_restaurant SET statut = 'reservee' WHERE id_table = ?";
    $stmtUpdate = $pdo->prepare($sqlUpdateTable);
    $stmtUpdate->execute([$table['id_table']]);
    
    // ==============================================================================
    // EXÉCUTION AUTOMATIQUE DE L'ENVOI D'EMAIL
    // ==============================================================================
    $email_envoye = envoyerEmailReservation(
        $data['email'],
        $data['nom_client'],
        (int)$id_reservation,
        (int)$table['id_table'],
        $date_heure,
        (int)$data['nb_personnes'],
        $statut_reservation,
        $commentaire
    );
    
    // Réponse JSON renvoyée au frontend de ton application
    echo json_encode([
        "success" => true,
        "id_reservation" => $id_reservation,
        "table_numero" => $table['numero'],
        "email_notif" => $email_envoye, 
        "message" => "Réservation confirmée ! Table n°" . $table['numero'] . " pour " . $data['nb_personnes'] . " personnes." . ($email_envoye ? " Un email de confirmation a été envoyé." : "")
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
?>