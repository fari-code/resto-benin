<?php
// On démarre la session et on sécurise la page
// session_start();
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: connexion.php");
//     exit();
// }

require_once '../config/db.php';

// Sécurité : On accepte uniquement les requêtes en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_plat'])) {
    $id_plat = intval($_POST['id_plat']);

    try {
        // 1. Récupérer le nom de l'image (colonne 'image') pour la supprimer du serveur
        $stmtImage = $pdo->prepare("SELECT image FROM plats WHERE id_plat = ?");
        $stmtImage->execute([$id_plat]);
        $plat = $stmtImage->fetch();

        if ($plat) {
            // Reconstitution du chemin vers ton dossier d'images
            $chemin_image = "../public/dist/assets/img/" . $plat['image'];

            // Supprimer le fichier image s'il existe et que ce n'est pas une image par défaut
            if (!empty($plat['image']) && file_exists($chemin_image) && strpos($plat['image'], 'default') === false) {
                unlink($chemin_image);
            }

            // 2. Supprimer définitivement le plat de la base de données
            $stmtDelete = $pdo->prepare("DELETE FROM plats WHERE id_plat = ?");
            $stmtDelete->execute([$id_plat]);

            // On stocke un message de succès en session
            $_SESSION['message_succes'] = "Le plat a été supprimé avec succès.";
        } else {
            $_SESSION['message_erreur'] = "Le plat n'existe pas ou a déjà été supprimé.";
        }
    } catch (\PDOException $e) {
        // En cas de clé étrangère liée (ex: le plat est déjà dans une commande passée)
        $_SESSION['message_erreur'] = "Impossible de supprimer ce plat car il est lié à des commandes existantes. Désactivez plutôt sa disponibilité.";
    }
} else {
    $_SESSION['message_erreur'] = "Requête non valide.";
}

// Redirection immédiate vers la liste des plats
header("Location: plats.php");
exit();