<?php
// On charge PHPMailer (assure-toi que le dossier vendor est bien accessible via ce chemin)
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Envoie un email au format HTML stylisé pour notifier le client du statut de sa réservation.
 *
 * @param string $email Adresse email du client
 * @param string $nom Nom du client
 * @param int $id_reservation Identifiant unique de la réservation
 * @param int $id_table Numéro ou ID de la table affectée
 * @param string $date_heure Date et heure au format SQL (AAAA-MM-JJ HH:MM:SS)
 * @param int $nb_couverts Nombre de personnes
 * @param string $statut Statut actuel ('en_attente', 'confirmee', 'annulee')
 * @param string|null $commentaire Commentaire ou note du client
 * @return bool True en cas de succès, False si l'envoi échoue
 */
function envoyerEmailReservation(
    string $email,
    string $nom,
    int $id_reservation,
    int $id_table,
    string $date_heure,
    int $nb_couverts,
    string $statut,
    ?string $commentaire = null
): bool {

    $mail = new PHPMailer(true);

    try {
        // --- CONFIGURATION SERVEUR SMTP GMAIL ---
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'dossafarid1@gmail.com';
        // /!\ RAPPEL : Mets ton mot de passe d'application à 16 caractères ici (sans espaces)
        $mail->Password   = 't n r q e z s x v p x w c e i x'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // --- EXPÉDITEUR ET DESTINATAIRE ---
        $mail->setFrom('dossafarid1@gmail.com', 'RestoBénin');
        $mail->addAddress($email, $nom);

        // --- ADAPTATION VISUELLE ET TEXTUELLE DU TEMPLATE SELON LE STATUT ---
        switch ($statut) {
            case 'confirmee':
                $sujet = "Votre réservation #{$id_reservation} est CONFIRMÉE !";
                $titre_mail = "Réservation Confirmée";
                $badge_texte = "Confirmée";
                $badge_style = "background-color: #d1e7dd; color: #0f5132;"; // Vert émeraude
                $phrase_intro = "Bonne nouvelle ! Votre réservation a été validée par notre équipe. Nous avons hâte de vous recevoir.";
                break;
                
            case 'annulee':
                $sujet = "Annulation de votre réservation #{$id_reservation}";
                $titre_mail = "Réservation Annulée";
                $badge_texte = "Annulée";
                $badge_style = "background-color: #f8d7da; color: #842029;"; // Rouge rubis
                $phrase_intro = "Nous vous informons que votre réservation a malheureusement été annulée. Si vous n'êtes pas à l'origine de cette action, n'hésitez pas à nous recontacter.";
                break;
                
            default:
                $sujet = "Votre réservation #{$id_reservation} est en attente de validation";
                $titre_mail = "Demande de Réservation";
                $badge_texte = "En attente";
                $badge_style = "background-color: #fff3cd; color: #664d03;"; // Jaune/Orange ambré
                $phrase_intro = "Votre demande de réservation a bien été enregistrée. Notre équipe va la traiter dans les plus brefs délais pour vous attribuer une table.";
                break;
        }

        $mail->isHTML(true);
        $mail->Subject = $sujet;

        // --- DESIGN ET STRUCURE DE L'EMAIL HTML ---
        $message = "
        <!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; background: #f8fafc; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { background: #dc2626; color: white; text-align: center; padding: 20px; }
                .header h1 { margin: 0; font-size: 24px; }
                .header p { margin: 5px 0 0 0; opacity: 0.9; }
                .content { padding: 20px; line-height: 1.6; }
                .card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; margin-top: 15px; margin-bottom: 20px; }
                .card h3 { margin-top: 0; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; color: #111827; }
                .card p { margin: 8px 0; font-size: 14px; }
                .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-weight: bold; font-size: 12px; text-transform: uppercase; }
                .footer { background: #f3f4f6; text-align: center; padding: 15px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>RestoBénin</h1>
                    <p>{$titre_mail}</p>
                </div>
                <div class='content'>
                    <p>Bonjour <strong>" . htmlspecialchars($nom) . "</strong>,</p>
                    <p>{$phrase_intro}</p>
                    
                    <div class='card'>
                        <h3>Détails de la réservation</h3>
                        <p><strong>Numéro :</strong> #{$id_reservation}</p>
                        <p><strong>Table assignée :</strong> " . ($id_table > 0 ? "Table N° ".$id_table : "En cours d'attribution") . "</p>
                        <p><strong>Date et heure :</strong> " . date('d/m/Y à H:i', strtotime($date_heure)) . "</p>
                        <p><strong>Nombre de couverts :</strong> {$nb_couverts} personnes</p>
                        <p><strong>Statut :</strong> <span class='badge' style='{$badge_style}'>{$badge_texte}</span></p>";

        // Ajout conditionnel du commentaire s'il existe
        if (!empty($commentaire)) {
            $message .= "
                        <p style='margin-top:12px; padding-top:8px; border-top:1px dashed #e5e7eb;'>
                            <strong>Votre note / commentaire :</strong><br>
                            <span style='font-style: italic; color: #555;'>" . htmlspecialchars($commentaire) . "</span>
                        </p>";
        }

        $message .= "
                    </div>
                    
                    <p>Merci d'avoir choisi RestoBénin !</p>
                    <p>Cordialement,<br><strong>L'équipe RestoBénin</strong></p>
                </div>
                <div class='footer'>
                    <strong>RestoBénin</strong><br>
                    Cotonou, Bénin<br>
                    Tél : +229 01 98 12 80 96
                </div>
            </div>
        </body>
        </html>";

        $mail->Body = $message;
        $mail->AltBody = strip_tags($message); // Version brute pour les clients mail obsolètes

        $mail->send();
        return true;

    } catch (Exception $e) {
        // Enregistre l'erreur en arrière-plan sans bloquer l'application
        error_log("Erreur PHPMailer lors de l'envoi de la réservation : " . $mail->ErrorInfo);
        return false;
    }
}