<?php
// On démarre la session PHP
session_start();
require_once '../config/db.php';

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Dans ta table, l'identifiant de connexion est la colonne 'email'
    $email = trim($_POST['identifiant']);
    $mot_de_passe = trim($_POST['mot_de_passe']);

    if (!empty($email) && !empty($mot_de_passe)) {
        try {
            // CORRECTION : Recherche de l'utilisateur dans la table 'employes' via son email et actif
            $stmt = $pdo->prepare("SELECT * FROM employes WHERE email = ? AND statut = 'actif' LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // CORRECTION : double vérification (password_verify pour le haché OU comparaison stricte pour le texte clair '123456')
            if ($user && (password_verify($mot_de_passe, $user['mot_de_passe']) || $mot_de_passe === $user['mot_de_passe'])) {
                
                // On stocke les informations essentielles demandées par tes fichiers de session
                $_SESSION['admin_id'] = $user['id_employe'];
                $_SESSION['admin_nom'] = $user['nom'];
                $_SESSION['admin_prenom'] = $user['prenom'];
                $_SESSION['admin_role'] = $user['role']; // serveur, cuisinier, manager, admin

                // Optionnel : s'aligner sur la variable $_SESSION['admin_id'] utilisée par supprimer_plat.php
                $_SESSION['admin_id'] = $user['id_employe'];

                // Redirection directe vers le tableau de bord
                header("Location: dashboard.php");
                exit();
            } else {
                $erreur = "Identifiant (Email) ou mot de passe incorrect.";
            }
        } catch (\PDOException $e) {
            $erreur = "Erreur système : " . $e->getMessage();
        }
    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RestoBénin | Connexion Administrative</title>
    
    <link rel="stylesheet" href="../public/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="login-page bg-body-secondary d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="login-box" style="width: 400px;">
        <div class="card card-outline card-primary shadow-lg">
            <div class="card-header text-center py-4">
                <h1 class="h3 mb-0 fw-bold text-primary">RestoBénin</h1>
                <small class="text-muted">Espace d'Administration</small>
            </div>
            <div class="card-body login-card-body p-4">
                <p class="login-box-msg text-center mb-4">Connectez-vous pour ouvrir votre session</p>

                <?php if (!empty($erreur)): ?>
                    <div class="alert alert-danger p-2 small" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $erreur; ?>
                    </div>
                <?php endif; ?>

                <form action="connexion.php" method="POST">
                    <div class="input-group mb-3">
                        <input type="email" name="identifiant" class="form-control" placeholder="Adresse Email (ex: farid@restobenin.bj)" required autocomplete="email">
                        <div class="input-group-text">
                            <i class="bi bi-envelope-fill"></i>
                        </div>
                    </div>
                    
                    <div class="input-group mb-4">
                        <input type="password" name="mot_de_passe" class="form-control" placeholder="Mot de passe" required autocomplete="current-password">
                        <div class="input-group-text">
                            <i class="bi bi-lock-fill"></i>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100 fw-bold py-2">
                                <i class="bi bi-box-arrow-in-right"></i> Se connecter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../public/dist/js/adminlte.min.js"></script>
</body>
</html>