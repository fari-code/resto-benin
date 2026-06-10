<?php
require_once '../includes/verification_droits.php';
verifierRoleAutorise(['serveur', 'manager']);
require_once '../config/db.php';

// 1. Traitement de la mise à jour du statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_statut'])) {
    $id_commande = intval($_POST['id_commande']);
    $nouveau_statut = $_POST['statut'];
    $statuts_autorises = ['en_attente', 'en_cuisine', 'prete', 'livree', 'annulee'];

    if (in_array($nouveau_statut, $statuts_autorises)) {
        $stmt = $pdo->prepare("UPDATE commandes SET statut = ? WHERE id_commande = ?");
        $stmt->execute([$nouveau_statut, $id_commande]);
    }
}

// 2. Récupérer toutes les commandes
$query = "SELECT c.*, t.numero AS numero_table 
          FROM commandes c 
          LEFT JOIN tables_restaurant t ON c.id_table = t.id_table 
          ORDER BY c.date_heure DESC";
          
$commandes = $pdo->query($query)->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <h3 class="mb-0">Suivi & Traitement des Commandes</h3>
        </div>
    </div>
    
    <div class="app-content">
        <div class="container-fluid">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h3 class="card-title">Flux des commandes en temps réel</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Client / Contact</th>
                                    <th>Type</th>
                                    <th>Table</th>
                                    <th>Montant Total</th>
                                    <th>Date & Heure</th>
                                    <th>Statut Actuel</th>
                                    <th class="text-center">Changer le Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($commandes as $com): ?>
                                    <tr>
                                        <td><strong>#<?php echo $com['id_commande']; ?></strong></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($com['nom_client']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($com['telephone_client']); ?></small>
                                            <?php if($com['type'] === 'livraison'): ?>
                                                <br><small class="text-danger"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($com['adresse_livraison']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            if($com['type'] === 'sur_place') echo '<span class="badge bg-primary">Sur Place</span>';
                                            elseif($com['type'] === 'emporter') echo '<span class="badge bg-info text-dark">À Emporter</span>';
                                            else echo '<span class="badge bg-purple" style="background-color: #6f42c1; color:white;">Livraison</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo $com['id_table'] ? 'Table n°' . $com['numero_table'] : '<span class="text-muted">—</span>'; ?>
                                        </td>
                                        <td><strong><?php echo number_format($com['montant_total'], 0, ',', ' '); ?> FCFA</strong></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($com['date_heure'])); ?></td>
                                        <td>
                                            <?php 
                                            switch($com['statut']) {
                                                case 'en_attente': echo '<span class="badge bg-warning text-dark">En attente</span>'; break;
                                                case 'en_cuisine': echo '<span class="badge bg-info text-white">En cuisine</span>'; break;
                                                case 'prete':      echo '<span class="badge bg-primary">Prête</span>'; break;
                                                case 'livree':     echo '<span class="badge bg-success">Livrée</span>'; break;
                                                case 'annulee':    echo '<span class="badge bg-danger">Annulée</span>'; break;
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <form action="commandes.php" method="POST" class="d-inline-flex gap-1 align-items-center">
                                                <input type="hidden" name="id_commande" value="<?php echo $com['id_commande']; ?>">
                                                <select name="statut" class="form-select form-select-sm" style="width: 130px;">
                                                    <option value="en_attente" <?php if($com['statut']=='en_attente') echo 'selected'; ?>>En attente</option>
                                                    <option value="en_cuisine" <?php if($com['statut']=='en_cuisine') echo 'selected'; ?>>En cuisine</option>
                                                    <option value="prete" <?php if($com['statut']=='prete') echo 'selected'; ?>>Prête</option>
                                                    <option value="livree" <?php if($com['statut']=='livree') echo 'selected'; ?>>Livrée</option>
                                                    <option value="annulee" <?php if($com['statut']=='annulee') echo 'selected'; ?>>Annulée</option>
                                                </select>
                                                <button type="submit" name="action_statut" class="btn btn-sm btn-dark">
                                                    <i class="bi bi-arrow-right-short"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>