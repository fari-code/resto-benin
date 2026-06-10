<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["succes" => false, "erreur" => "Non autorisé"]);
    exit();
}

require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_categorie'])) {
    $id_cat = intval($_POST['id_categorie']);

    try {
        $stmt = $pdo->prepare("DELETE FROM categories_plats WHERE id_categorie = ?");
        $stmt->execute([$id_cat]);

        echo json_encode(["succes" => true]);
        exit();
    } catch (\PDOException $e) {
        echo json_encode(["succes" => false, "erreur" => "Erreur de base de données : " . $e->getMessage()]);
        exit();
    }
} else {
    echo json_encode(["succes" => false, "erreur" => "Requête invalide"]);
    exit();
}