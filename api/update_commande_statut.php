<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(["success" => false, "message" => "Méthode non autorisée"]); exit(); }

require_once '../config/db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id_commande']) || !isset($data['statut'])) {
    echo json_encode(["success" => false, "message" => "ID commande et statut requis"]);
    exit();
}

$statutsValides = ['en_attente', 'en_cuisine', 'prete', 'livree', 'annulee'];
if (!in_array($data['statut'], $statutsValides)) {
    echo json_encode(["success" => false, "message" => "Statut invalide"]);
    exit();
}

try {
    $sql = "UPDATE commandes SET statut = ? WHERE id_commande = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data['statut'], $data['id_commande']]);
    
    echo json_encode(["success" => true, "message" => "Statut mis à jour"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>