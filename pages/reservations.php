<?php
// 1. Sécurisation de la page
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: connexion.php");
    exit();
}

require_once '../config/db.php';

$message = "";
$status = "";

// 2. Traitement du changement de statut d'une réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_reservation'])) {
    $id_reservation = intval($_POST['id_reservation']);
    $statut_res = $_POST['statut_res'];
    
    $statuts_valides = ['en_attente', 'confirmee', 'annulee'];
    
    if (in_array($statut_res, $statuts_valides)) {
        try {
            $stmt = $pdo->prepare("UPDATE reservations SET statut = ? WHERE id_reservation = ?");
            $stmt->execute([$statut_res, $id_reservation]);
            
            $message = "Le statut de la réservation a été mis à jour avec succès.";
            $status = "success";
        } catch (\PDOException $e) {
            $message = "Erreur lors de la modification : " . $e->getMessage();
            $status = "danger";
        }
    }
}

// 3. CORRECTION : Utilisation de LEFT JOIN pour éviter de masquer les réservations sans table assignée
$query_res = "SELECT r.*, t.numero AS numero_table 
              FROM reservations r
              LEFT JOIN tables_restaurant t ON r.id_table = t.id_table
              ORDER BY r.date_heure DESC";
$reservations = $pdo->query($query_res)->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <h3 class="mb-0"><i class="bi bi-calendar-check text-primary"></i> Gestion des Réservations de Tables</h3>
        </div>
    </div>
    
    <div class="app-content">
        <div class="container-fluid">
            
            <!-- Alertes système -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $status; ?> alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i> <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card card-outline card-dark shadow-sm">
                <div class="card-header">
                    <h3 class="card-title fw-bold">Demandes de réservation</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Client</th>
                                    <th>Contact</th>
                                    <th>Table assignée</th>
                                    <th>Date & Heure</th>
                                    <th>Couverts</th>
                                    <th>Commentaire</th>
                                    <th>Statut</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($reservations)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>
                                            Aucune réservation pour le moment.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($reservations as $res): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($res['nom_client']); ?></strong></td>
                                            <td>
                                                <div class="small">
                                                    <i class="bi bi-telephone text-secondary"></i> <?php echo htmlspecialchars($res['telephone_client']); ?><br>
                                                    <span class="text-muted"><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($res['email']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if(!empty($res['numero_table'])): ?>
                                                    <span class="badge bg-primary">Table n°<?php echo $res['numero_table']; ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Non assignée</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="fw-semibold"><?php echo date('d/m/Y à H:i', strtotime($res['date_heure'])); ?></span></td>
                                            <td><span class="badge bg-light text-dark border"><i class="bi bi-people-fill text-primary"></i> <?php echo $res['nb_couverts']; ?> pers.</span></td>
                                            <td>
                                                <small class="text-wrap d-block text-muted" style="max-width: 200px;">
                                                    <?php echo !empty($res['commentaire']) ? htmlspecialchars($res['commentaire']) : '<em>Aucun</em>'; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php 
                                                if($res['statut'] === 'en_attente') {
                                                    echo '<span class="badge bg-warning text-dark shadow-sm"><i class="bi bi-hourglass-split"></i> En attente</span>';
                                                } elseif($res['statut'] === 'confirmee') {
                                                    echo '<span class="badge bg-success shadow-sm"><i class="bi bi-check-circle-fill"></i> Confirmée</span>';
                                                } else {
                                                    echo '<span class="badge bg-danger shadow-sm"><i class="bi bi-x-circle-fill"></i> Annulée</span>';
                                                }
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <!-- CORRECTION : action="" renvoie la modification sur la même page, peu importe son nom -->
                                                <form action="" method="POST" class="d-inline-flex gap-1">
                                                    <input type="hidden" name="id_reservation" value="<?php echo $res['id_reservation']; ?>">
                                                    <select name="statut_res" class="form-select form-select-sm" style="width: 125px;">
                                                        <option value="en_attente" <?php if($res['statut']=='en_attente') echo 'selected'; ?>>En attente</option>
                                                        <option value="confirmee" <?php if($res['statut']=='confirmee') echo 'selected'; ?>>Confirmer</option>
                                                        <option value="annulee" <?php if($res['statut']=='annulee') echo 'selected'; ?>>Annuler</option>
                                                    </select>
                                                    <button type="submit" name="action_reservation" class="btn btn-sm btn-dark" title="Sauvegarder">
                                                        <i class="bi bi-save"></i>
                                                    </button>
                                                </form>
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

<?php include '../includes/footer.php'; ?>