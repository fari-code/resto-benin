<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Chargement de Composer
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Envoie un email de confirmation de commande
 */
function envoyerEmailConfirmation(
    string $email,
    string $nom,
    int $id_commande,
    array $panier,
    float $total,
    string $type,
    ?string $adresse = null
): bool {

    $mail = new PHPMailer(true);

    try {

        // Configuration SMTP Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        // Remplace par tes informations
        $mail->Username = 'dossafarid1@gmail.com';
        $mail->Password = 't n r q e z s x v p x w c e i x';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->CharSet = 'UTF-8';

        // Expéditeur
        $mail->setFrom('dossafarid1@gmail.com', 'RestoBénin');

        // Destinataire
        $mail->addAddress($email, $nom);

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = "Confirmation de votre commande #{$id_commande}";

        $message = "
        <!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body{
                    font-family: Arial, sans-serif;
                    background:#f8fafc;
                    color:#333;
                    margin:0;
                    padding:0;
                }

                .container{
                    max-width:600px;
                    margin:20px auto;
                    background:#fff;
                    border-radius:10px;
                    overflow:hidden;
                    box-shadow:0 2px 10px rgba(0,0,0,0.1);
                }

                .header{
                    background:#dc2626;
                    color:white;
                    text-align:center;
                    padding:20px;
                }

                .content{
                    padding:20px;
                }

                .card{
                    background:#f9fafb;
                    border:1px solid #e5e7eb;
                    border-radius:8px;
                    padding:15px;
                    margin-bottom:20px;
                }

                .item{
                    border-bottom:1px solid #ddd;
                    padding:10px 0;
                }

                .item:last-child{
                    border-bottom:none;
                }

                .total{
                    text-align:right;
                    font-size:20px;
                    font-weight:bold;
                    color:#dc2626;
                    margin-top:15px;
                }

                .footer{
                    background:#f3f4f6;
                    text-align:center;
                    padding:15px;
                    font-size:12px;
                    color:#666;
                }

                .badge{
                    display:inline-block;
                    padding:6px 12px;
                    border-radius:20px;
                    background:#fef3c7;
                    color:#92400e;
                    font-size:12px;
                }
            </style>
        </head>

        <body>

            <div class='container'>

                <div class='header'>
                    <h1>RestoBénin</h1>
                    <p>Confirmation de commande</p>
                </div>

                <div class='content'>

                    <p>Bonjour <strong>" . htmlspecialchars($nom) . "</strong>,</p>

                    <p>
                        Nous avons bien reçu votre commande.
                        Notre équipe est en train de la préparer.
                    </p>

                    <div class='card'>
                        <h3>Informations de la commande</h3>

                        <p><strong>Numéro :</strong> #{$id_commande}</p>

                        <p><strong>Date :</strong> " . date('d/m/Y H:i') . "</p>

                        <p><strong>Type :</strong> " . getTypeTexte($type) . "</p>";

        if (!empty($adresse)) {
            $message .= "
                <p><strong>Adresse :</strong>
                " . htmlspecialchars($adresse) . "</p>";
        }

        $message .= "
                        <p>
                            <strong>Statut :</strong>
                            <span class='badge'>En attente de préparation</span>
                        </p>
                    </div>

                    <div class='card'>
                        <h3>Détail de votre commande</h3>";

        foreach ($panier as $item) {

            $message .= "
                <div class='item'>
                    <strong>" . htmlspecialchars($item['titre']) . "</strong><br>";

            if (
                isset($item['personnalisation']) &&
                $item['personnalisation'] !== 'Standard'
            ) {
                $message .= "
                    <small>
                        " . htmlspecialchars($item['personnalisation']) . "
                    </small><br>";
            }

            $message .= "
                    " . number_format($item['prix'], 0, ',', ' ') . " FCFA
                </div>";
        }

        $message .= "

                        <div class='total'>
                            Total : " . number_format($total, 0, ',', ' ') . " FCFA
                        </div>

                    </div>

                    <p>
                        Vous pouvez suivre votre commande
                        directement sur notre plateforme.
                    </p>

                    <p>
                        Temps estimé de préparation :
                        <strong>20 à 30 minutes</strong>
                    </p>

                    <p>
                        Merci pour votre confiance.<br>
                        L'équipe RestoBénin.
                    </p>

                </div>

                <div class='footer'>
                    RestoBénin<br>
                    Tél : +229 01 98 12 80 96
                </div>

            </div>

        </body>
        </html>";

        $mail->Body = $message;
        $mail->AltBody = strip_tags($message);

        $mail->send();

        return true;
    } catch (Exception $e) {

        error_log("Erreur email : " . $mail->ErrorInfo);

        return false;
    }
}

/**
 * Convertit le type de commande en texte lisible
 */
function getTypeTexte(string $type): string
{
    switch ($type) {

        case 'sur_place':
            return 'Sur place';

        case 'emporter':
            return 'À emporter';

        case 'livraison':
            return 'Livraison à domicile';

        default:
            return $type;
    }
}
