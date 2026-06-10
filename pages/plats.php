<?php
// 1. On démarre la session (nécessaire pour la sécurité)
session_start();

// 2. On inclut la connexion à la base de données
require_once '../config/db.php';

// 3. On fait une requête SQL pour récupérer tous les plats
$stmt = $pdo->query("SELECT * FROM plats ORDER BY id_plat DESC");
$plats = $stmt->fetchAll();

// 4. On inclut les morceaux de notre template AdminLTE 4
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0">Gestion du Menu</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <a href="ajouter_plat.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Ajouter un plat local
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h3 class="card-title">Liste des plats enregistrés</h3>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Nom du plat</th>
                                    <th>Catégorie</th>
                                    <th>Prix</th>
                                    <th>Temps de Préparation</th>
                                    <th>Statut</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($plats)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">Aucun plat trouvé dans le menu.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($plats as $plat): ?>
                                        <tr id="ligne-plat-<?php echo $plat['id_plat']; ?>">
                                            <td>
                                                <img src="../public/dist/assets/img/<?php echo htmlspecialchars($plat['image']); ?>"
                                                     alt="<?php echo htmlspecialchars($plat['nom']); ?>"
                                                     class="img-thumbnail" style="width: 60px; height: 50px; object-fit: cover;">
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($plat['nom']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($plat['description']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($plat['categorie']); ?></span>
                                            </td>
                                            <td>
                                                <strong><?php echo number_format($plat['prix'], 0, ',', ' '); ?> FCFA</strong>
                                            </td>
                                            <td>
                                                <i class="bi bi-clock"></i> <?php echo htmlspecialchars($plat['temps_preparation']); ?> min
                                            </td>
                                            <td>
                                                <?php if ($plat['disponible'] == 1): ?>
                                                    <span class="badge bg-success">Disponible</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Épuisé</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="modifier_plat.php?id=<?php echo $plat['id_plat']; ?>" class="btn btn-sm btn-warning me-1">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="confirmerSuppression(<?php echo $plat['id_plat']; ?>, '<?php echo addslashes(htmlspecialchars($plat['nom'], ENT_QUOTES)); ?>')">
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

<?php
// 5. On inclut le pied de page
include '../includes/footer.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmerSuppression(id, nom) {
    // 1. Fenêtre de confirmation stylisée SweetAlert2
    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: `Le plat "${nom}" sera définitivement supprimé.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash-fill"></i> Oui, supprimer',
        cancelButtonText: 'Annuler',
        focusCancel: true
    }).then((result) => {
        
        // 2. Si l'utilisateur clique sur le bouton rouge "Oui, supprimer"
        if (result.isConfirmed) {
            
            // Affichage d'un indicateur de chargement pendant le traitement
            Swal.fire({
                title: 'Suppression en cours...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Préparation des données à envoyer en POST
            const formData = new FormData();
            formData.append('id_plat', id);

            // Envoi de la requête asynchrone (AJAX) à supprimer_plat.php
            fetch('supprimer_plat.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Si la requête s'est bien passée
                if (response.ok) {
                    // Pop-up moderne de succès automatique
                    Swal.fire({
                        icon: 'success',
                        title: 'Supprimé !',
                        text: `Le plat "${nom}" a été retiré avec succès.`,
                        confirmButtonColor: '#28a745',
                        timer: 2500,
                        timerProgressBar: true
                    });

                    // Animation fluide : On fait disparaître la ligne du tableau sans recharger
                    const ligne = document.getElementById(`ligne-plat-${id}`);
                    if (ligne) {
                        ligne.style.transition = "all 0.5s ease";
                        ligne.style.opacity = "0";
                        setTimeout(() => {
                            ligne.remove();
                        }, 500);
                    }
                } else {
                    throw new Error('Erreur serveur');
                }
            })
            .catch(error => {
                // En cas de problème technique ou réseau
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Une erreur est survenue lors de la suppression.',
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}
</script>