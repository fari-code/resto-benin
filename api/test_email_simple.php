<?php
require_once './config/mail_config.php';

$resultat = envoyerEmailConfirmation(
    'dossafarid1@gmail.com',  // Remplacez par votre email
    'Test',
    999,
    [['titre' => 'Plat test', 'prix' => 1000, 'personnalisation' => 'Standard']],
    1000,
    'sur_place',
    null
);

if ($resultat) {
    echo "✅ Email envoyé ! Vérifiez votre boîte (y compris les spams)";
} else {
    echo "❌ Échec de l'envoi";
}
?>