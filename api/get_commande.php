<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/db.php';

if (!isset($_GET['id_commande'])) {
    echo json_encode(["success" => false, "message" => "ID commande manquant"]);
    exit();
}

$id_commande = intval($_GET['id_commande']);

try {
    $sql = "SELECT id_commande, statut, date_heure, montant_total, nom_client FROM commandes WHERE id_commande = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_commande]);
    $commande = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($commande) {
        echo json_encode(["success" => true, "commande" => $commande]);
    } else {
        echo json_encode(["success" => false, "message" => "Commande non trouvée"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>