<?php
// logout.php
session_start();

// On détruit toutes les variables de session
$_SESSION = array();

// On détruit la session proprement
session_destroy();

// Redirection immédiate vers l'écran de connexion
header("Location: pages/connexion.php");
exit();