<?php
// 1. On démarre la session et on sécurise l'accès
require_once '../includes/verification_droits.php';
verifierRoleAutorise(['manager']);

// 2. Connexion à la base de données
require_once '../config/db.php';

// 3. Vérification de la présence de l'ID dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['msg_erreur'] = "Catégorie introuvable.";
    header("Location: categories.php");
    exit();
}

$id_categorie = intval($_GET['id']);

// 4. Traitement de la mise à jour (Formulaire soumis)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_categorie'])) {
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $ordre = intval($_POST['ordre_affichage']);

    if (!empty($nom)) {
        try {
            $stmtUpdate = $pdo->prepare("UPDATE categories_plats SET nom = ?, description = ?, ordre_affichage = ? WHERE id_categorie = ?");
            $stmtUpdate->execute([$nom, $description, $ordre, $id_categorie]);
            
            $_SESSION['msg_succes'] = "La catégorie a été modifiée avec succès !";
            header("Location: categories.php");
            exit();
        } catch (\PDOException $e) {
            $erreur = "Erreur lors de la modification : " . $e->getMessage();
        }
    } else {
        $erreur = "Le nom de la catégorie est obligatoire.";
    }
}

// 5. Récupérer les données actuelles de la catégorie à modifier
$stmt = $pdo->prepare("SELECT * FROM categories_plats WHERE id_categorie = ?");
$stmt->execute([$id_categorie]);
$categorie = $stmt->fetch();

// Si la catégorie n'existe pas en BDD
if (!$categorie) {
    $_SESSION['msg_erreur'] = "Cette catégorie n'existe pas.";
    header("Location: categories.php");
    exit();
}

// 6. Inclusion du template AdminLTE 4
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Modifier la Catégorie</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <a href="categories.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-short"></i> Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card shadow-sm max-width-600 mx-auto">
                <div class="card-header bg-warning text-dark">
                    <h3 class="card-title fw-bold">
                        <i class="bi bi-pencil-square"></i> Édition : <?php echo htmlspecialchars($categorie['nom']); ?>
                    </h3>
                </div>

                <form action="modifier_categorie.php?id=<?php echo $id_categorie; ?>" method="POST">
                    <div class="card-body">
                        
                        <?php if (isset($erreur)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $erreur; ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="nom" class="form-label fw-bold">Nom de la catégorie *</label>
                            <input type="text" class="form-control" id="nom" name="nom" 
                                   value="<?php echo htmlspecialchars($categorie['nom']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($categorie['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="ordre_affichage" class="form-label fw-bold">Ordre d'affichage</label>
                            <input type="number" class="form-control" id="ordre_affichage" name="ordre_affichage" 
                                   value="<?php echo intval($categorie['ordre_affichage']); ?>" min="0">
                            <small class="text-muted">Définit la priorité d'apparition dans les menus.</small>
                        </div>

                    </div>
                    
                    <div class="card-footer bg-light text-end">
                        <a href="categories.php" class="btn btn-secondary">Annuler</a>
                        <button type="submit" name="modifier_categorie" class="btn btn-warning fw-bold text-dark">
                            <i class="bi bi-check-circle-fill"></i> Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>