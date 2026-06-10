<style>
@keyframes pulse-green {
    0% { transform: scale(0.95); opacity: 1; box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7); }
    70% { transform: scale(1.1); opacity: 1; box-shadow: 0 0 0 5px rgba(25, 135, 84, 0); }
    100% { transform: scale(0.95); opacity: 1; box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); }
}
.online-indicator {
    width: 11px;
    height: 11px;
    background-color: #28a745;
    border: 2px solid #fff;
    border-radius: 50%;
    position: absolute;
    bottom: -1px;
    right: -1px;
    animation: pulse-green 2s infinite ease-in-out;
}
.avatar-nav {
    width: 36px;
    height: 36px;
    font-size: 15px;
    user-select: none;
    cursor: pointer;
    transition: transform 0.2s ease;
}
.avatar-nav:hover {
    transform: scale(1.05);
}
</style>

<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        <!-- Côté gauche : Bouton Menu Burger -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list"></i>
                </a>
            </li >
        </ul>
        
        <!-- Côté droit : Profil Utilisateur & Déconnexion -->
        <ul class="navbar-nav ms-auto align-items-center">
            
            <?php 
            // Sécurité : Récupération des informations de la session active
            $prenom_user = htmlspecialchars($_SESSION['admin_prenom'] ?? 'Utilisateur');
            $role_user = htmlspecialchars(ucfirst($_SESSION['admin_role'] ?? 'Personnel'));
            $initiale = strtoupper(substr($prenom_user, 0, 1));
            
            // Assignation d'une couleur dynamique à l'avatar selon les privilèges
            $avatarBg = 'bg-primary';
            if (isset($_SESSION['admin_role'])) {
                if ($_SESSION['admin_role'] === 'admin') $avatarBg = 'bg-danger';
                elseif ($_SESSION['admin_role'] === 'manager') $avatarBg = 'bg-success';
                elseif ($_SESSION['admin_role'] === 'cuisinier') $avatarBg = 'bg-warning text-dark';
                elseif ($_SESSION['admin_role'] === 'serveur') $avatarBg = 'bg-info text-white';
            }
            ?>
            
            <!-- Élément Avatar avec Tooltip Bootstrap au survol -->
            <li class="nav-item me-3">
                <div class="position-relative" 
                     data-bs-toggle="tooltip" 
                     data-bs-placement="bottom" 
                     data-bs-html="true"
                     title="<div class='text-start py-1'><strong><?php echo $prenom_user; ?></strong><br><span class='badge bg-light text-dark border mt-1'><?php echo $role_user; ?></span></div>">
                    
                    <!-- Le cercle avec l'initiale -->
                    <div class="<?php echo $avatarBg; ?> text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm avatar-nav">
                        <?php echo $initiale; ?>
                    </div>
                    
                    <!-- Le point clignotant vert "En ligne" -->
                    <div class="online-indicator"></div>
                </div>
            </li>

            <!-- Séparateur vertical visuel -->
            <li class="nav-item border-start me-2" style="height: 22px; opacity: 0.2;"></li>
            
            <!-- Bouton Déconnexion -->
            <li class="nav-item">
                <a href="../logout.php" class="nav-link text-danger fw-semibold d-flex align-items-center gap-1">
                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                </a>
            </li>
        </ul>
    </div>
</nav>