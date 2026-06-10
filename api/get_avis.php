<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/db.php';

try {
    $sql = "SELECT id_avis, nom_client, note, commentaire, date_creation 
            FROM avis 
            ORDER BY date_creation DESC 
            LIMIT 20";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $avis = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($avis);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => true, "message" => $e->getMessage()]);
}
?>