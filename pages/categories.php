<?php
require_once '../includes/verification_droits.php';
verifierRoleAutorise(['manager']);

// 2. Connexion à la base de données
require_once '../config/db.php';

// 3. Traitement de l'ajout d'une catégorie (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_categorie'])) {
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $ordre = intval($_POST['ordre_affichage']);

    if (!empty($nom)) {
        $stmt = $pdo->prepare("INSERT INTO categories_plats (nom, description, ordre_affichage) VALUES (?, ?, ?)");
        $stmt->execute([$nom, $description, $ordre]);
        $_SESSION['msg_succes'] = "La catégorie a été ajoutée avec succès !";
    } else {
        $_SESSION['msg_erreur'] = "Le nom de la catégorie est obligatoire.";
    }
    header("Location: categories.php");
    exit();
}

// 4. Récupérer toutes les catégories triées par ordre d'affichage
$stmt = $pdo->query("SELECT * FROM categories_plats ORDER BY ordre_affichage ASC, id_categorie DESC");
$categories = $stmt->fetchAll();

// 5. Inclusion du template AdminLTE 4
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Gestion des Catégories</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <a href="./ajouter_categorie.php" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddCategorie">
                        <i class="bi bi-plus-circle"></i> Ajouter une catégorie
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h3 class="card-title">Liste des catégories disponibles</h3>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Ordre</th>
                                    <th>Nom de la catégorie</th>
                                    <th>Description</th>
                                    <th class="text-center" style="width: 200px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($categories)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4">Aucune catégorie trouvée.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <tr id="ligne-cat-<?php echo $cat['id_categorie']; ?>">
                                            <td>
                                                <span class="badge bg-info text-dark fw-bold"><?php echo $cat['ordre_affichage']; ?></span>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($cat['nom']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="text-muted"><?php echo htmlspecialchars($cat['description'] ?? 'Aucune description'); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <a href="modifier_categorie.php?id=<?php echo $cat['id_categorie']; ?>" class="btn btn-sm btn-warning me-1">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="supprimerCategorie(<?php echo $cat['id_categorie']; ?>, '<?php echo addslashes(htmlspecialchars($cat['nom'], ENT_QUOTES)); ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div> 
            </div> 
        </div> 
    </div> 
</main>

<div class="modal fade" id="modalAddCategorie" tabindex="-1" aria-labelledby="modalAddCategorieLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="categories.php" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalAddCategorieLabel"><i class="bi bi-folder-plus"></i> Nouvelle Catégorie</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nom" class="form-label fw-bold">Nom de la catégorie *</label>
                        <input type="text" class="form-control" id="nom" name="nom" required placeholder="Ex: Entrées, Grillades, Local...">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Brève description..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="ordre_affichage" class="form-label fw-bold">Ordre d'affichage</label>
                        <input type="number" class="form-control" id="ordre_affichage" name="ordre_affichage" value="0" min="0">
                        <small class="text-muted">Les plus petits numéros s'affichent en premier.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" name="ajouter_categorie" class="btn btn-primary fw-bold">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Notification Flash de succès ou d'erreur PHP
document.addEventListener("DOMContentLoaded", function() {
    <?php if (isset($_SESSION['msg_succes'])): ?>
        Swal.fire({ icon: 'success', title: 'Succès', text: '<?php echo $_SESSION['msg_succes']; ?>', timer: 2500, showConfirmButton: false });
        <?php unset($_SESSION['msg_succes']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['msg_erreur'])): ?>
        Swal.fire({ icon: 'error', title: 'Erreur', text: '<?php echo $_SESSION['msg_erreur']; ?>' });
        <?php unset($_SESSION['msg_erreur']); ?>
    <?php endif; ?>
});

// Suppression asynchrone (AJAX Fetch) d'une catégorie
function supprimerCategorie(id, nom) {
    Swal.fire({
        title: 'Supprimer la catégorie ?',
        text: `Voulez-vous vraiment supprimer "${nom}" ? Attention, cela peut impacter les plats liés !`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id_categorie', id);

            fetch('supprimer_categorie.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.succes) {
                    Swal.fire({ icon: 'success', title: 'Supprimé !', text: 'La catégorie a été retirée.', timer: 1500, showConfirmButton: false });
                    const ligne = document.getElementById(`ligne-cat-${id}`);
                    if (ligne) ligne.remove();
                } else {
                    Swal.fire({ icon: 'error', title: 'Erreur', text: data.erreur || 'Impossible de supprimer.' });
                }
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Erreur', text: 'Erreur de connexion réseau.' });
            });
        }
    });
}
</script>