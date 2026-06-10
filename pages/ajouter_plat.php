<?php
// 1. Sécurisation de la page avec la session de l'administrateur
require_once '../includes/verification_droits.php';
verifierRoleAutorise(['manager']);

// Connexion à la base de données
require_once '../config/db.php';

// Récupérer les catégories pour alimenter la liste déroulante du formulaire
$categories = $pdo->query("SELECT * FROM categories_plats ORDER BY ordre_affichage ASC, nom ASC")->fetchAll();

$message = "";
$status = "";

// 2. Traitement du formulaire lors de la soumission (méthode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $prix = floatval($_POST['prix']);
    $temps_preparation = intval($_POST['temps_preparation']);
    $id_categorie = intval($_POST['id_categorie']);
    $disponible = isset($_POST['disponible']) ? 1 : 0;

    // Trouver le nom de la catégorie correspondante pour remplir le champ 'categorie' textuel de ta table
    $nom_categorie = "";
    foreach ($categories as $cat) {
        if ($cat['id_categorie'] == $id_categorie) {
            $nom_categorie = $cat['nom'];
            break;
        }
    }

    // Gestion de l'image
    $nom_image_final = "default-plat.jpg"; // Image par défaut si aucune n'est choisie
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Extensions autorisées
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileExtension, $allowedExtensions)) {
            // Pour éviter les doublons de noms, on génère un nom unique (ex: plat_171829382.png)
            $nom_image_final = "plat_" . time() . "." . $fileExtension;
            
            // Dossier de destination (dans ton dossier public/dist/assets/img/)
            $uploadFileDir = '../public/dist/assets/img/';
            
            // Créer le dossier s'il n'existe pas encore
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            
            $dest_path = $uploadFileDir . $nom_image_final;

            // Déplacement physique du fichier temporaire vers ton dossier de projet
            move_uploaded_file($fileTmpPath, $dest_path);
        } else {
            $message = "Format d'image non valide (uniquement JPG, PNG, WEBP).";
            $status = "danger";
        }
    }

    // Si pas d'erreur sur l'image, on insère en BDD
    if ($status !== "danger" && !empty($nom) && $prix > 0) {
        try {
            $sql = "INSERT INTO plats (nom, description, prix, image, disponible, temps_preparation, categorie, id_categorie) 
                    VALUES (:nom, :description, :prix, :image, :disponible, :temps_preparation, :categorie, :id_categorie)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nom'               => $nom,
                ':description'       => $description,
                ':prix'              => $prix,
                ':image'             => $nom_image_final,
                ':disponible'        => $disponible,
                ':temps_preparation' => $temps_preparation,
                ':categorie'         => $nom_categorie,
                ':id_categorie'      => $id_categorie
            ]);

            $message = "Le plat <strong>$nom</strong> a bien été ajouté au menu !";
            $status = "success";
        } catch (\PDOException $e) {
            $message = "Erreur lors de l'ajout en BDD : " . $e->getMessage();
            $status = "danger";
        }
    }
}

// 3. Inclusion du template AdminLTE 4
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <h3 class="mb-0">Ajouter une Spécialité Culinaire</h3>
        </div>
    </div>
    
    <div class="app-content">
        <div class="container-fluid">
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $status; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Détails du nouveau plat</h3>
                </div>
                
                <form action="ajouter_plat.php" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Nom du plat <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nom" name="nom" placeholder="Ex: Brochettes de pintade" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="id_categorie" class="form-label">Catégorie <span class="text-danger">*</span></label>
                                <select class="form-select" id="id_categorie" name="id_categorie" required>
                                    <option value="">-- Sélectionner une catégorie --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id_categorie']; ?>"><?php echo htmlspecialchars($cat['nom']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="prix" class="form-label">Prix (FCFA) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="prix" name="prix" min="0" placeholder="Ex: 4500" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="temps_preparation" class="form-label">Temps de préparation (minutes)</label>
                                <input type="number" class="form-control" id="temps_preparation" name="temps_preparation" min="0" placeholder="Ex: 25">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description ou Ingrédients</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Description du plat béninois..."></textarea>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-md-6 mb-3">
                                <label for="image" class="form-label">Photo du plat</label>
                                <input class="form-control" type="file" id="image" name="image">
                            </div>
                            <div class="col-md-6 mb-3 form-check form-switch ps-5">
                                <input class="form-check-input" type="checkbox" id="disponible" name="disponible" value="1" checked>
                                <label class="form-check-label fw-bold" for="disponible">Rendre ce plat disponible immédiatement</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-light text-end">
                        <a href="plats.php" class="btn btn-secondary">Retour à la liste</a>
                        <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Enregistrer le Plat</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>