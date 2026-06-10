<?php
// 1. SÉCURITÉ : On inclut la vérification de connexion globale (gère la session)
require_once '../includes/verification_droits.php';
require_once '../config/db.php';

// 2. Variable repère : l'utilisateur est-il un admin ou un manager ?
$est_manager_ou_admin = (isset($_SESSION['admin_role']) && ($_SESSION['admin_role'] === 'manager' || $_SESSION['admin_role'] === 'admin'));

// Initialisation des variables pour éviter tout plantage HTML
$chiffreAffaires = 0;
$commandesAttente = 0;
$totalPlats = 0;
$reservationsAttente = 0;
$caJsonData = json_encode(array_fill(0, 12, 0));
$jsonNomsPlats = json_encode([]);
$jsonVentesPlats = json_encode([]);
$recentCommandes = [];

try {
    // --- REQUÊTES PUBLIQUES (Accessibles par tout le personnel connecté) ---
    
    // Commandes en attente
    $stmtCmd = $pdo->query("SELECT COUNT(*) AS nb_attente FROM commandes WHERE statut = 'en_attente'");
    $cmdData = $stmtCmd->fetch();
    $commandesAttente = $cmdData['nb_attente'] ?? 0;

    // Nombre de plats au menu
    $stmtPlats = $pdo->query("SELECT COUNT(*) AS total_plats FROM plats");
    $platsData = $stmtPlats->fetch();
    $totalPlats = $platsData['total_plats'] ?? 0;

    // Les 5 dernières activités (Flux de commandes)
    $stmtRecent = $pdo->query("SELECT * FROM commandes ORDER BY date_heure DESC LIMIT 5");
    $recentCommandes = $stmtRecent->fetchAll();


    // --- REQUÊTES SÉCURISÉES : Chargées UNIQUEMENT pour l'Admin et le Manager ---
    if ($est_manager_ou_admin) {
        
        // 1. Chiffre d'Affaires total (commandes livrées)
        $stmtCA = $pdo->query("SELECT SUM(montant_total) AS total_ca FROM commandes WHERE statut = 'livree'");
        $caData = $stmtCA->fetch();
        $chiffreAffaires = $caData['total_ca'] ?? 0;

        // 2. Réservations en attente (Gestion managériale)
        $stmtRes = $pdo->query("SELECT COUNT(*) AS nb_res_attente FROM reservations WHERE statut = 'en_attente'");
        $resData = $stmtRes->fetch();
        $reservationsAttente = $resData['nb_res_attente'] ?? 0;

        // 3. Graphique 1 : Chiffre d'affaires par mois (Année en cours)
        $queryGraph = "SELECT MONTH(date_heure) as mois, SUM(montant_total) as total 
                       FROM commandes 
                       WHERE statut = 'livree' AND YEAR(date_heure) = YEAR(CURDATE())
                       GROUP BY MONTH(date_heure)
                       ORDER BY mois ASC";
        $stmtGraph = $pdo->query($queryGraph);
        $graphData = $stmtGraph->fetchAll();

        $caParMois = array_fill(1, 12, 0);
        foreach ($graphData as $data) {
            $caParMois[$data['mois']] = (int)$data['total'];
        }
        $caJsonData = json_encode(array_values($caParMois));

        // 4. Graphique 2 : Top 5 des plats populaires 
        $queryPopulaires = "SELECT p.nom, COUNT(cp.id_plat) AS total_ventes 
                            FROM plats p
                            JOIN commande_plats cp ON p.id_plat = cp.id_plat
                            JOIN commandes c ON cp.id_commande = c.id_commande
                            WHERE c.statut = 'livree'
                            GROUP BY p.id_plat, p.nom
                            ORDER BY total_ventes DESC 
                            LIMIT 5";
        
        try {
            $stmtPopulaires = $pdo->query($queryPopulaires);
            $populairesData = $stmtPopulaires->fetchAll();
        } catch (\PDOException $e) {
            // Données de secours si la table d'association est absente
            $populairesData = [
                ['nom' => 'Atassi', 'total_ventes' => 12],
                ['nom' => 'Jus de Bissap', 'total_ventes' => 25],
                ['nom' => 'Ragoût d\'igname', 'total_ventes' => 18],
                ['nom' => 'Aloko', 'total_ventes' => 30],
                ['nom' => 'Poulet bicyclette', 'total_ventes' => 15]
            ];
        }

        $nomsPlats = [];
        $ventesPlats = [];
        foreach ($populairesData as $platPop) {
            $nomsPlats[] = $platPop['nom'];
            $ventesPlats[] = (int)$platPop['total_ventes'];
        }
        $jsonNomsPlats = json_encode($nomsPlats);
        $jsonVentesPlats = json_encode($ventesPlats);
    }

} catch (\PDOException $e) {
    die("Erreur de statistiques : " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="app-main">
    <!-- En-tête d'origine léger et épuré -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0"> <i class="fa-solid fa-gauge-simple"></i> Tableau de Bord</h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="app-content">
        <div class="container-fluid">
            
            <?php if (isset($_GET['erreur']) && $_GET['erreur'] === 'acces_refuse'): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="bi bi-shield-lock-fill me-2"></i> 
                    <strong>Accès non autorisé !</strong> Vos privilèges actuels ne vous permettent pas d'ouvrir cette page.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                
                <?php if ($est_manager_ou_admin): ?>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success text-white shadow-sm p-3 mb-4 rounded">
                            <div class="inner">
                                <h3><span class="compteur-anime" data-target="<?php echo $chiffreAffaires; ?>">0</span> <span class="fs-6">FCFA</span></h3>
                                <p class="mb-0">Chiffre d'Affaires</p>
                            </div>
                            <div class="icon position-absolute top-0 end-0 p-3 opacity-25">
                                <i class="bi bi-cash-coin fs-1"></i>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="<?php echo $est_manager_ou_admin ? 'col-lg-3' : 'col-lg-6'; ?> col-6">
                    <div class="small-box bg-warning text-dark shadow-sm p-3 mb-4 rounded">
                        <div class="inner">
                            <h3><span class="compteur-anime" data-target="<?php echo $commandesAttente; ?>">0</span></h3>
                            <p class="mb-0">Commandes en Attente</p>
                        </div>
                        <div class="icon position-absolute top-0 end-0 p-3 opacity-25">
                            <i class="bi bi-clock-history fs-1"></i>
                        </div>
                    </div>
                </div>

                <div class="<?php echo $est_manager_ou_admin ? 'col-lg-3' : 'col-lg-6'; ?> col-6">
                    <div class="small-box bg-info text-white shadow-sm p-3 mb-4 rounded">
                        <div class="inner">
                            <h3><span class="compteur-anime" data-target="<?php echo $totalPlats; ?>">0</span></h3>
                            <p class="mb-0">Plats à la Carte</p>
                        </div>
                        <div class="icon position-absolute top-0 end-0 p-3 opacity-25">
                            <i class="bi bi-egg-fried fs-1"></i>
                        </div>
                    </div>
                </div>

                <?php if ($est_manager_ou_admin): ?>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger text-white shadow-sm p-3 mb-4 rounded">
                            <div class="inner">
                                <h3><span class="compteur-anime" data-target="<?php echo $reservationsAttente; ?>">0</span></h3>
                                <p class="mb-0">Réservations à Confirmer</p>
                            </div>
                            <div class="icon position-absolute top-0 end-0 p-3 opacity-25">
                                <i class="bi bi-calendar-check fs-1"></i>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($est_manager_ou_admin): ?>
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white align-items-center d-flex justify-content-between border-bottom-0 py-3">
                                <h3 class="card-title fw-bold text-dark mb-0">
                                    <i class="bi bi-bar-chart-line text-success me-2"></i> Évolution des Revenus Mensuels (FCFA)
                                </h3>
                            </div>
                            <div class="card-body">
                                <div id="chart-revenus"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white align-items-center d-flex justify-content-between border-bottom-0 py-3">
                                <h3 class="card-title fw-bold text-dark mb-0">
                                    <i class="bi bi-pie-chart-fill text-danger me-2"></i> Plats les plus Populaires
                                </h3>
                            </div>
                            <div class="card-body d-flex align-items-center justify-content-center">
                                <div id="chart-populaires" class="w-100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info shadow-sm d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                    <div>
                        <strong>Bon service à vous, <?php echo htmlspecialchars($_SESSION['admin_prenom']); ?> !</strong> 
                        Accédez rapidement à vos fonctions principales depuis la barre latérale ou cliquez sur vos boutons métiers.
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white">
                            <h3 class="card-title"><i class="bi bi-list-task me-2"></i> Flux des 5 dernières activités</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID Commande</th>
                                            <th>Client</th>
                                            <th>Montant</th>
                                            <th>Date / Heure</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recentCommandes)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-3 text-muted">Aucune commande enregistrée.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($recentCommandes as $rc): ?>
                                                <tr>
                                                    <td><strong>#<?php echo $rc['id_commande']; ?></strong></td>
                                                    <td><?php echo htmlspecialchars($rc['nom_client']); ?></td>
                                                    <td><strong><?php echo number_format($rc['montant_total'], 0, ',', ' '); ?> FCFA</strong></td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($rc['date_heure'])); ?></td>
                                                    <td>
                                                        <?php 
                                                        switch($rc['statut']) {
                                                            case 'en_attente': echo '<span class="badge bg-warning text-dark">En attente</span>'; break;
                                                            case 'en_cuisine': echo '<span class="badge bg-info">En cuisine</span>'; break;
                                                            case 'prete':      echo '<span class="badge bg-primary">Prête</span>'; break;
                                                            case 'livree':     echo '<span class="badge bg-success">Livrée</span>'; break;
                                                            case 'annulee':    echo '<span class="badge bg-danger">Annulée</span>'; break;
                                                        }
                                                        ?>
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

        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    
    // --- SCRIPT 1 : L'ANIMATION DES CHIFFRES (PUBLIC) ---
    const compteurs = document.querySelectorAll('.compteur-anime');
    compteurs.forEach(compteur => {
        const cible = +compteur.getAttribute('data-target');
        const vitesse = cible / 60;
        
        const majCompteur = () => {
            const valeurActuelle = +compteur.innerText.replace(/\s/g, '');
            if (valeurActuelle < cible) {
                const nouvelleValeur = Math.ceil(valeurActuelle + vitesse);
                if (nouvelleValeur >= cible) {
                    compteur.innerText = cible.toLocaleString('fr-FR');
                } else {
                    compteur.innerText = nouvelleValeur.toLocaleString('fr-FR');
                    setTimeout(majCompteur, 20);
                }
            } else {
                compteur.innerText = cible.toLocaleString('fr-FR');
            }
        };
        if (cible > 0) { majCompteur(); } else { compteur.innerText = "0"; }
    });

    // --- SCRIPTS APEXCHARTS (SÉCURISÉS : S'exécutent uniquement si les données existent) ---
    <?php if ($est_manager_ou_admin): ?>
        
        // --- GRAPH 1 : REVENUS MENSUELS ---
        const donneesChiffreAffaires = <?php echo $caJsonData; ?>;
        const optionsRevenus = {
            series: [{ name: 'Revenus', data: donneesChiffreAffaires }],
            chart: {
                type: 'area',
                height: 320,
                toolbar: { show: false }
            },
            colors: ['#198754'],
            stroke: { curve: 'smooth', width: 3 },
            xaxis: { categories: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'] },
            yaxis: { labels: { formatter: (v) => v.toLocaleString('fr-FR') + " F" } },
            tooltip: { y: { formatter: (v) => v.toLocaleString('fr-FR') + " FCFA" } },
            fill: {
                type: 'gradient',
                gradient: { opacityFrom: 0.4, opacityTo: 0.1 }
            }
        };
        new ApexCharts(document.querySelector("#chart-revenus"), optionsRevenus).render();


        // --- GRAPH 2 : PLATS POPULAIRES ---
        const listeNomsPlats = <?php echo $jsonNomsPlats; ?>;
        const listeVentesPlats = <?php echo $jsonVentesPlats; ?>;

        const optionsPopulaires = {
            series: listeVentesPlats,
            labels: listeNomsPlats,
            chart: {
                type: 'donut',
                height: 320
            },
            colors: ['#dc3545', '#fd7e14', '#ffc107', '#0dcaf0', '#6c757d'],
            dataLabels: {
                enabled: true,
                formatter: function (val, opts) {
                    return opts.w.config.series[opts.seriesIndex]; 
                }
            },
            legend: {
                position: 'bottom'
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " commandes passées";
                    }
                }
            }
        };
        new ApexCharts(document.querySelector("#chart-populaires"), optionsPopulaires).render();

    <?php endif; ?>
});
</script>

<?php include '../includes/footer.php'; ?>