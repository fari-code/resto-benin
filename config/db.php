<?php
// config/db.php

$host = '127.0.0.1';
$db   = 'restobenin'; // Le nom de ta base de données
$user = 'root';       // Identifiant par défaut sur XAMPP
$pass = '';           // Mot de passe vide par défaut sur XAMPP
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Active les erreurs claires
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Récupère les données sous forme de tableau associatif
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Désactive l'émulation pour plus de sécurité
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Si la connexion échoue, on affiche l'erreur
     die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>