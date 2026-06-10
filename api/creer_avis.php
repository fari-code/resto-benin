<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(["success" => false, "message" => "Méthode non autorisée"]); exit(); }

require_once '../config/db.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) { http_response_code(400); echo json_encode(["success" => false, "message" => "Données JSON invalides"]); exit(); }

if (empty($data['nom_client']) || empty($data['note']) || empty($data['commentaire'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Nom, note et commentaire sont requis"]);
    exit();
}

if ($data['note'] < 1 || $data['note'] > 5) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "La note doit être entre 1 et 5"]);
    exit();
}

try {
    // Vérifier si la colonne id_plat accepte NULL
    $checkColumn = $pdo->query("SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'avis' AND COLUMN_NAME = 'id_plat'")->fetch();
    
    if ($checkColumn && $checkColumn['IS_NULLABLE'] === 'YES') {
        // La colonne accepte NULL
        $sql = "INSERT INTO avis (id_plat, nom_client, telephone_client, note, commentaire, date_creation) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([null, $data['nom_client'], null, $data['note'], $data['commentaire']]);
    } else {
        // La colonne ne accepte pas NULL, prendre un plat par défaut
        $stmtDefault = $pdo->prepare("SELECT id_plat FROM plats WHERE disponible = 1 LIMIT 1");
        $stmtDefault->execute();
        $defaultPlat = $stmtDefault->fetch();
        $id_plat = $defaultPlat ? $defaultPlat['id_plat'] : 6;
        
        $sql = "INSERT INTO avis (id_plat, nom_client, telephone_client, note, commentaire, date_creation) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_plat, $data['nom_client'], null, $data['note'], $data['commentaire']]);
    }
    
    echo json_encode(["success" => true, "id_avis" => $pdo->lastInsertId(), "message" => "Merci pour votre avis !"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
?>