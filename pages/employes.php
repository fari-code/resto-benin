<?php
require_once '../includes/verification_droits.php';
// On autorise uniquement les managers (l'admin passe automatiquement grâce à la fonction)
verifierRoleAutorise(['manager']);
require_once '../config/db.php';

$message = "";
$status = "";

// 2. TRAITEMENT : Ajout d'un nouvel employé
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_employe'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    if (!empty($nom) && !empty($prenom) && !empty($email) && !empty($role) && !empty($password)) {
        // Hachage sécurisé du mot de passe
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare("INSERT INTO employes (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email, $password_hash, $role]);
            $message = "L'employé a été enregistré avec succès !";
            $status = "success";
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // Erreur clé dupliquée (email)
                $message = "Cette adresse email est déjà utilisée par un autre membre du personnel.";
            } else {
                $message = "Erreur système : " . $e->getMessage();
            }
            $status = "danger";
        }
    } else {
        $message = "Veuillez remplir correctement tous les champs obligatoires.";
        $status = "warning";
    }
}

// 3. TRAITEMENT : Basculer le statut (Actif/Inactif) ou révoquer un accès
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changer_statut'])) {
    $id_employe = intval($_POST['id_employe']);
    $nouveau_statut = $_POST['statut_actuel'] === 'actif' ? 'inactif' : 'actif';

    $stmt = $pdo->prepare("UPDATE employes SET statut = ? WHERE id_employe = ?");
    $stmt->execute([$nouveau_statut, $id_employe]);
    $message = "Le statut d'accès de l'employé a été modifié.";
    $status = "info";
}

// 4. Récupération de la liste complète des employés
$query = "SELECT id_employe, nom, prenom, email, role, statut, date_creation FROM employes ORDER BY role, nom ASC";
$employes = $pdo->query($query)->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<style>
    .animate-popup {
        animation: slideIn 0.5s ease-out;
    }
    @keyframes slideIn {
        from { transform: translateY(-50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    /* Classe violette sur-mesure compatible avec AdminLTE / Bootstrap */
    .bg-purple-custom {
        background-color: #6f42c1 !important;
        color: white !important;
    }
</style>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0"><i class="bi bi-people-fill text-dark"></i> Équipe RestoBénin</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <button type="button" class="btn btn-primary shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjoutEmploye">
                        <i class="bi bi-person-plus-fill text-white"></i> Recruter un employé
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $status; ?> alert-dismissible fade show shadow-sm animate-popup" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i> <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title fw-bold">Gestion des accès du personnel</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nom Complet</th>
                                    <th>Identifiant / Email</th>
                                    <th>Rôle & Permissions</th>
                                    <th>Statut de connexion</th>
                                    <th class="text-center">Action sur les accès</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($employes)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Aucun employé enregistré pour le moment.</td>
                                    </tr>
                                <?php index: else: ?>
                                    <?php foreach ($employes as $emp):
                                        // Attribution correcte des couleurs de badges (Y compris le Violet Manager)
                                        $roleBadge = 'bg-secondary text-white';
                                        if ($emp['role'] === 'manager') $roleBadge = 'bg-purple-custom';
                                        elseif ($emp['role'] === 'cuisinier') $roleBadge = 'bg-danger text-white';
                                        elseif ($emp['role'] === 'serveur') $roleBadge = 'bg-info text-dark';
                                        elseif ($emp['role'] === 'admin') $roleBadge = 'bg-dark text-white';
                                    ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($emp['prenom'] . ' ' . $emp['nom']); ?></strong></td>
                                            <td><i class="bi bi-envelope text-muted"></i> <?php echo htmlspecialchars($emp['email']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $roleBadge; ?> px-2 py-1 fs-6 shadow-sm">
                                                    <?php echo ucfirst($emp['role']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($emp['statut'] === 'actif'): ?>
                                                    <span class="badge bg-success shadow-sm"><i class="bi bi-shield-check"></i> Accès Autorisé</span>
                                                <?php else: ?>
                                                    <span class="badge bg-dark shadow-sm"><i class="bi bi-shield-x"></i> Accès Révoqué</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <form action="" method="POST" class="d-inline">
                                                    <input type="hidden" name="id_employe" value="<?php echo $emp['id_employe']; ?>">
                                                    <input type="hidden" name="statut_actuel" value="<?php echo $emp['statut']; ?>">

                                                    <?php if ($emp['statut'] === 'actif'): ?>
                                                        <button type="submit" name="changer_statut" class="btn btn-sm btn-outline-danger shadow-sm">
                                                            <i class="bi bi-lock-fill"></i> Bloquer les accès
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="submit" name="changer_statut" class="btn btn-sm btn-success text-white shadow-sm">
                                                            <i class="bi bi-unlock-fill"></i> Réactiver l'accès
                                                        </button>
                                                    <?php endif; ?>
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

<div class="modal fade" id="modalAjoutEmploye" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modalAjoutLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"> <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalAjoutLabel"><i class="bi bi-person-plus"></i> Créer un compte employé</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Prénom</label>
                            <input type="text" name="prenom" class="form-control" placeholder="Ex: Jean" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nom</label>
                            <input type="text" name="nom" class="form-control" placeholder="Ex: Chabi" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Adresse Email (Identifiant de connexion)</label>
                            <input type="email" name="email" class="form-control" placeholder="jean.chabi@restobenin.com" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Rôle de l'employé</label>
                            <select name="role" class="form-select" required>
                                <option value="" disabled selected>Choisir un rôle...</option>
                                <option value="serveur">Serveur (Prise de commande sur table)</option>
                                <option value="cuisinier">Cuisinier (Écran de contrôle cuisine)</option>
                                <option value="manager">Manager (Accès total aux rapports & outils)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Mot de passe initial</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                            <div class="form-text">L'employé utilisera cet email et ce mot de passe pour se connecter à son espace dédié.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" name="ajouter_employe" class="btn btn-primary text-white fw-bold">Valider l'embauche</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>