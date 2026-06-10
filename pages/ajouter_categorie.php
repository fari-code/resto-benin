<?php
require_once '../includes/verification_droits.php';
verifierRoleAutorise(['manager']);

// 2. Connexion à la base de données
require_once '../config/db.php';

$message = "";
$status = "";

// 3. Traitement du formulaire lors de la soumission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $ordre_affichage = intval($_POST['ordre_affichage']);

    if (!empty($nom)) {
        try {
            // Insertion dans la table categories_plats
            $sql = "INSERT INTO categories_plats (nom, description, ordre_affichage) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $description, $ordre_affichage]);

            // Message de succès mémorisé pour l'affichage
            $message = "La catégorie <strong>" . htmlspecialchars($nom) . "</strong> a été ajoutée avec succès !";
            $status = "success";
        } catch (\PDOException $e) {
            $message = "Erreur lors de l'ajout en base de données : " . $e->getMessage();
            $status = "danger";
        }
    } else {
        $message = "Le nom de la catégorie est obligatoire.";
        $status = "danger";
    }
}

// 4. Inclusion des éléments de structure du template
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Ajouter une Catégorie de Plat</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <a href="categories.php" class="btn btn-secondary shadow-sm">
                        <i class="bi bi-arrow-left-short"></i> Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $status; ?> alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi <?php echo ($status === 'success') ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i>
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card card-primary card-outline shadow-sm max-width-700 mx-auto">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title fw-bold">
                        <i class="bi bi-folder-plus"></i> Nouvelle Catégorie RestoBénin
                    </h3>
                </div>

                <form action="ajouter_categorie.php" method="POST">
                    <div class="card-body">

                        <div class="mb-3">
                            <label for="nom" class="form-label fw-bold">Nom de la catégorie <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="nom" name="nom"
                                placeholder="Ex: Spécialités Locales, Boissons, Grillades..." required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">Description / Détails</label>
                            <textarea class="form-control" id="description" name="description" rows="4"
                                placeholder="Petite description textuelle de ce que regroupe cette catégorie..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="ordre_affichage" class="form-label fw-bold">Ordre d'affichage dans le menu</label>
                            <div class="input-group" style="max-width: 200px;">
                                <input type="number" class="form-control" id="ordre_affichage" name="ordre_affichage" value="0" min="0">
                                <span class="input-group-text"><i class="bi bi-sort-numeric-down"></i></span>
                            </div>
                            <small class="text-muted d-block mt-1">
                                Les numéros les plus bas (ex: 0, 1, 2) s'afficheront en premier sur l'application.
                            </small>
                        </div>

                    </div>

                    <div class="card-footer bg-light text-end py-3">
                        <a href="categories.php" class="btn btn-secondary me-2">Annuler</a>
                        <button type="submit" class="btn btn-primary fw-bold">
                            <i class="bi bi-plus-circle"></i> Créer la Catégorie
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>