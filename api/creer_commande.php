<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(["success" => false, "message" => "Méthode non autorisée"]); exit(); }

require_once '../config/db.php';
require_once '../config/mail_config.php'; // Inclusion de PHPMailer

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) { http_response_code(400); echo json_encode(["success" => false, "message" => "Données JSON invalides"]); exit(); }

if (empty($data['nom_client']) || empty($data['type']) || empty($data['panier'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Champs manquants"]);
    exit();
}

if (count($data['panier']) === 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Le panier est vide"]);
    exit();
}

try {
    $pdo->beginTransaction();
    
    $type = $data['type'];
    $id_table = isset($data['id_table']) && !empty($data['id_table']) ? $data['id_table'] : null;
    $nom_client = $data['nom_client'];
    $telephone_client = isset($data['telephone_client']) ? $data['telephone_client'] : null;
    $email = isset($data['email']) && !empty($data['email']) ? $data['email'] : null;
    $adresse_livraison = isset($data['adresse_livraison']) && !empty($data['adresse_livraison']) ? $data['adresse_livraison'] : null;
    $montant_total = isset($data['montant_total']) ? $data['montant_total'] : 0;
    
    if ($type === 'sur_place' && $id_table) {
        $checkTable = $pdo->prepare("SELECT id_table FROM tables_restaurant WHERE id_table = ?");
        $checkTable->execute([$id_table]);
        if (!$checkTable->fetch()) { $id_table = null; }
    }
    
    $sql = "INSERT INTO commandes (type, id_table, nom_client, telephone_client, email, adresse_livraison, montant_total, statut, date_heure) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$type, $id_table, $nom_client, $telephone_client, $email, $adresse_livraison, $montant_total]);
    $id_commande = $pdo->lastInsertId();
    
    $sqlLigne = "INSERT INTO lignes_commande (id_commande, id_plat, quantite, prix_unitaire, commentaire_client) VALUES (?, ?, ?, ?, ?)";
    $stmtLigne = $pdo->prepare($sqlLigne);
    
    foreach ($data['panier'] as $item) {
        $id_plat = isset($item['id']) ? $item['id'] : (isset($item['id_plat']) ? $item['id_plat'] : null);
        $quantite = isset($item['quantite']) ? $item['quantite'] : 1;
        $prix_unitaire = isset($item['prix']) ? $item['prix'] : 0;
        $commentaire = isset($item['personnalisation']) && $item['personnalisation'] !== 'Standard' ? $item['personnalisation'] : null;
        
        if ($id_plat) {
            $stmtLigne->execute([$id_commande, $id_plat, $quantite, $prix_unitaire, $commentaire]);
        }
    }
    
    $pdo->commit();
    
    // === ENVOI D'EMAIL AVEC PHPMailer ===
    $emailEnvoye = false;
    if ($email) {
        $emailEnvoye = envoyerEmailConfirmation($email, $nom_client, $id_commande, $data['panier'], $montant_total, $type, $adresse_livraison);
    }
    
    echo json_encode([
        "success" => true,
        "id_commande" => $id_commande,
        "email_envoye" => $emailEnvoye,
        "message" => "Commande créée avec succès !"
    ]);
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
?>