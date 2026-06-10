<?php
// On démarre la session et on sécurise la page
require_once '../includes/verification_droits.php';
verifierRoleAutorise(['manager']);
require_once '../config/db.php';

$message = "";
$erreur = "";

// 1. Vérifier si l'ID du plat est bien fourni dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: plats.php");
    exit();
}

$id_plat = intval($_GET['id']);

// 2. Récupérer les informations actuelles du plat
try {
    $stmt = $pdo->prepare("SELECT * FROM plats WHERE id_plat = ?");
    $stmt->execute([$id_plat]);
    $plat = $stmt->fetch();

    if (!$plat) {
        // Si le plat n'existe pas en BDD
        header("Location: plats.php");
        exit();
    }
} catch (\PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}

// 3. Traitement de la soumission du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $prix = floatval($_POST['prix']);
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    
    // Par défaut, on garde l'ancienne image
    $image_destination = $plat['image']; 

    if (!empty($nom) && $prix > 0) {
        
        // Gestion de l'upload d'une nouvelle image (si fournie)
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = $_FILES['image']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $extensions_autorisees = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($fileExtension, $extensions_autorisees)) {
                // On génère un nom unique pour éviter les doublons
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $uploadFileDir = '../public/uploads/';
                
                // Créer le dossier s'il n'existe pas
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }
                
                $dest_path = $uploadFileDir . $newFileName;
                
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Supprimer l'ancienne image du serveur si elle existe et que ce n'est pas une image par défaut
                    if (!empty($plat['image_url']) && file_exists($plat['image_url']) && strpos($plat['image_url'], 'default') === false) {
                        unlink($plat['image_url']);
                    }
                    $image_destination = $dest_path;
                } else {
                    $erreur = "Erreur lors du déplacement du fichier image.";
                }
            } else {
                $erreur = "Format d'image non valide (uniquement JPG, JPEG, PNG, WEBP).";
            }
        }

        // Si aucune erreur sur l'image, on met à jour la BDD
        if (empty($erreur)) {
            try {
                $stmtUpdate = $pdo->prepare("UPDATE plats SET nom = ?, description = ?, prix = ?, image = ?, disponible = ? WHERE id_plat = ?");
                $stmtUpdate->execute([$nom, $description, $prix, $image_destination, $disponible, $id_plat]);
                
                // Rafraîchir les données locales du plat pour l'affichage à jour
                $plat['nom'] = $nom;
                $plat['description'] = $description;
                $plat['prix'] = $prix;
                $plat['image_url'] = $image_destination;
                $plat['disponible'] = $disponible;

                $message = "Le plat a été modifié avec succès !";
            } catch (\PDOException $e) {
                $erreur = "Erreur lors de la mise à jour : " . $e->getMessage();
            }
        }
    } else {
        $erreur = "Veuillez remplir correctement les champs obligatoires (Nom et Prix).";
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Modifier une spécialité</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <a href="plats.php" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 mx-auto">
                    
                    <!-- Alertes de succès ou d'erreur -->
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-success shadow-sm">
                            <i class="bi bi-check-circle-fill"></i> <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($erreur)): ?>
                        <div class="alert alert-danger shadow-sm">
                            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $erreur; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Formulaire de modification dans une Card AdminLTE 4 -->
                    <div class="card card-outline card-warning shadow-sm mb-4">
                        <div class="card-header">
                            <h3 class="card-title fw-bold">Formulaire d'édition : <?php echo htmlspecialchars($plat['nom']); ?></h3>
                        </div>
                        
                        <form action="modifier_plat.php?id=<?php echo $id_plat; ?>" method="POST" enctype="multipart/form-data">
                            <div class="card-body">
                                
                                <!-- Nom du Plat -->
                                <div class="mb-3">
                                    <label for="nom" class="form-label fw-semibold">Nom du plat <span class="text-danger">*</span></label>
                                    <input type="text" name="nom" id="nom" class="form-control" value="<?php echo htmlspecialchars($plat['nom']); ?>" required>
                                </div>
                                
                                <!-- Prix -->
                                <div class="mb-3">
                                    <label for="prix" class="form-label fw-semibold">Prix (FCFA) <span class="text-danger">*</span></label>
                                    <input type="number" name="prix" id="prix" class="form-control" value="<?php echo $plat['prix']; ?>" required min="0">
                                </div>
                                
                                <!-- Description -->
                                <div class="mb-3">
                                    <label for="description" class="form-label fw-semibold">Description / Ingrédients</label>
                                    <textarea name="description" id="description" class="form-control" rows="4"><?php echo htmlspecialchars($plat['description']); ?></textarea>
                                </div>
                                
                                <!-- Image Actuelle & Upload -->
                                <div class="mb-3 row align-items-center">
                                    <div class="col-sm-3 text-center">
                                        <label class="form-label d-block fw-semibold">Image actuelle</label>
                                        <?php if (!empty($plat['image_url']) && file_exists($plat['image_url'])): ?>
                                            <img src="<?php echo $plat['image_url']; ?>" alt="Plat" class="img-thumbnail rounded shadow-sm" style="max-height: 100px;">
                                        <?php else: ?>
                                            <span class="text-muted small">Aucune image</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-sm-9">
                                        <label for="image" class="form-label fw-semibold">Remplacer l'image (optionnel)</label>
                                        <input type="file" name="image" id="image" class="form-control" accept="image/*">
                                        <div class="form-text">Formats acceptés : JPG, PNG, WEBP.</div>
                                    </div>
                                </div>
                                
                                <!-- Statut de Disponibilité -->
                                <div class="mb-3 form-check form-switch pt-2">
                                    <input class="form-check-input" type="checkbox" name="disponible" id="disponible" value="1" <?php echo $plat['disponible'] ? 'checked' : ''; ?>>
                                    <label class="form-check-input-label fw-semibold ms-2" for="disponible">Plat disponible immédiatement pour les clients</label>
                                </div>

                            </div>
                            
                            <!-- Boutons d'action -->
                            <div class="card-footer bg-light text-end">
                                <button type="submit" class="btn btn-warning fw-bold">
                                    <i class="bi bi-save"></i> Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>