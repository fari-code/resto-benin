<?php
require_once '../includes/verification_droits.php';
verifierRoleAutorise(['cuisinier', 'manager']);

require_once '../config/db.php';

// Traitement AJAX pour changer le statut en arrière-plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_statut'])) {
    header('Content-Type: application/json');
    $id_commande = intval($_POST['id_commande']);
    $nouveau_statut = $_POST['nouveau_statut'];

    // Validation des statuts selon ton ENUM SQL : 'en_attente','en_cuisine','prete','livree','annulee'
    if (in_array($nouveau_statut, ['en_cuisine', 'prete'])) {
        try {
            // CORRECTION : La colonne s'appelle 'statut'
            $stmt = $pdo->prepare("UPDATE commandes SET statut = ? WHERE id_commande = ?");
            $stmt->execute([$nouveau_statut, $id_commande]);
            echo json_encode(["succes" => true]);
            exit();
        } catch (\PDOException $e) {
            echo json_encode(["succes" => false, "erreur" => $e->getMessage()]);
            exit();
        }
    }
    echo json_encode(["succes" => false, "erreur" => "Statut invalide"]);
    exit();
}

// CORRECTION REQUÊTE : 
// 1. statut_commande devient c.statut
// 2. On filtre sur 'en_attente' et 'en_cuisine'
// 3. On utilise LEFT JOIN pour lister la commande même si la cuisine n'a pas encore reçu le détail des lignes
$sql = "SELECT c.id_commande, c.id_table, c.statut, c.date_heure, 
               t.numero AS numero_table,
               l.quantite, p.nom AS nom_plat
        FROM commandes c
        LEFT JOIN tables_restaurant t ON c.id_table = t.id_table
        LEFT JOIN lignes_commande l ON c.id_commande = l.id_commande
        LEFT JOIN plats p ON l.id_plat = p.id_plat
        WHERE c.statut IN ('en_attente', 'en_cuisine')
        ORDER BY c.date_heure ASC";

$stmt = $pdo->query($sql);
$commandes_brutes = $stmt->fetchAll();

// On regroupe les plats par commande
$tickets = [];
foreach ($commandes_brutes as $ligne) {
    $id_cmd = $ligne['id_commande'];
    if (!isset($tickets[$id_cmd])) {
        $tickets[$id_cmd] = [
            'id_commande' => $ligne['id_commande'],
            'numero_table' => $ligne['numero_table'] ?? "Emporter/Livraison",
            'statut' => $ligne['statut'],
            'date_heure' => $ligne['date_heure'],
            'plats' => []
        ];
    }
    // On n'ajoute le plat que s'il y en a un de rattaché
    if (!empty($ligne['nom_plat'])) {
        $tickets[$id_cmd]['plats'][] = [
            'nom' => $ligne['nom_plat'],
            'quantite' => $ligne['quantite']
        ];
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0"><i class="bi bi-fire text-danger"></i> Écran de Contrôle Cuisine</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <button onclick="window.location.reload();" class="btn btn-outline-primary shadow-sm btn-sm">
                        <i class="bi bi-arrow-clockwise"></i> Actualiser les commandes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            <?php if (empty($tickets)): ?>
                <div class="text-center py-5">
                    <div class="display-1 text-muted"><i class="bi bi-check2-circle text-success"></i></div>
                    <h4 class="text-secondary mt-3">Tout est propre !</h4>
                    <p class="text-muted">Aucune commande en attente de préparation pour le moment.</p>
                </div>
            <?php else: ?>

                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                    <?php foreach ($tickets as $ticket):
                        $estEnAttente = ($ticket['statut'] === 'en_attente');
                        $cardClass = $estEnAttente ? 'card-danger' : 'card-warning';
                        $badgeClass = $estEnAttente ? 'bg-danger' : 'bg-warning text-dark';
                        $badgeTxt = $estEnAttente ? 'En Attente' : 'En Cuisine';

                        $heure_commande = new DateTime($ticket['date_heure']);
                        $maintenant = new DateTime();
                        $intervalle = $heure_commande->diff($maintenant);
                        $minutes_ecoulees = ($intervalle->days * 24 * 60) + ($intervalle->h * 60) + $intervalle->i;
                    ?>
                        <div class="col" id="ticket-<?php echo $ticket['id_commande']; ?>">
                            <div class="card <?php echo $cardClass; ?> card-outline shadow-lg h-100">

                                <div class="card-header d-flex justify-content-between align-items-center py-3">
                                    <h4 class="card-title fw-bold mb-0 text-dark">
                                        <i class="bi bi-egg-fried"></i> Table : <?php echo htmlspecialchars($ticket['numero_table']); ?>
                                    </h4>
                                    <span class="badge <?php echo $badgeClass; ?> fw-bold px-2 py-1">
                                        <?php echo $badgeTxt; ?>
                                    </span>
                                </div>

                                <div class="card-body">
                                    <p class="small text-muted mb-3">
                                        <i class="bi bi-clock"></i> Reçu à : <?php echo $heure_commande->format('H:i'); ?>
                                        <span class="fw-bold text-danger">(Il y a <?php echo $minutes_ecoulees; ?> min)</span>
                                    </p>

                                    <ul class="list-group list-group-flush mb-0 border-top">
                                        <?php if (empty($ticket['plats'])): ?>
                                            <li class="list-group-item text-muted italic py-2">
                                                Aucun article spécifié (Commande vide)
                                            </li>
                                        <?php else: ?>
                                            <?php foreach ($ticket['plats'] as $plat): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 fs-5">
                                                    <span class="text-dark fw-semibold"><?php echo htmlspecialchars($plat['nom']); ?></span>
                                                    <span class="badge bg-secondary rounded-pill px-3 py-2">x <?php echo $plat['quantite']; ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                </div>

                                <div class="card-footer bg-light py-3 text-center">
                                    <?php if ($estEnAttente): ?>
                                        <button class="btn btn-danger w-100 fw-bold fs-5 shadow-sm py-2"
                                            onclick="changerStatut(<?php echo $ticket['id_commande']; ?>, 'en_cuisine')">
                                            <i class="bi bi-play-fill"></i> Commencer la préparation
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-success w-100 fw-bold fs-5 shadow-sm py-2"
                                            onclick="changerStatut(<?php echo $ticket['id_commande']; ?>, 'prete')">
                                            <i class="bi bi-check-lg"></i> Commande Prête !
                                        </button>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>

        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function changerStatut(idCommande, nouveauStatut) {
        const formData = new FormData();
        formData.append('action_statut', '1');
        formData.append('id_commande', idCommande);
        formData.append('nouveau_statut', nouveauStatut);

        fetch('cuisine.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.succes) {
                    if (nouveauStatut === 'prete') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Commande prête !',
                            text: 'Le statut a été mis à jour.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        document.getElementById(`ticket-${idCommande}`).remove();

                        if (document.querySelectorAll('.col').length === 0) {
                            window.location.reload();
                        }
                    } else {
                        window.location.reload();
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: data.erreur
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur réseau',
                    text: 'Impossible de joindre le serveur.'
                });
            });
    }

    setInterval(function() {
        window.location.reload();
    }, 30000);
</script>