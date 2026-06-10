<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification 1 : L'utilisateur est-il au moins connecté ?
if (!isset($_SESSION['admin_id'])) {
    // Si pas connecté, redirection immédiate vers la page de connexion
    header("Location: ../pages/connexion.php"); 
    exit();
}

/**
 * Fonction de contrôle des rôles sur chaque page
 * @param array $rolesAutorises Liste des rôles ayant le droit d'accéder à la page
 */
function verifierRoleAutorise($rolesAutorises = []) {
    // Si l'utilisateur est l'admin suprême ('admin'), il passe partout sans restriction
    if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin') {
        return true;
    }

    // Vérifie si le rôle de l'utilisateur fait partie des rôles configurés pour la page
    if (isset($_SESSION['admin_role']) && in_array($_SESSION['admin_role'], $rolesAutorises)) {
        return true;
    }

    // Si le rôle n'est pas autorisé : redirection vers le tableau de bord avec un message
    header("Location: dashboard.php?erreur=acces_refuse");
    exit();
}