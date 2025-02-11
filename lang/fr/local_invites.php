<?php

/**
 * @package    local_invites
 * @author     2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Inviter au cours';

$string['invite'] = 'Inviter au cours';
$string['invites:inviteusers'] = "Permet à un utilisateur d'accéder à l'interface des invitations";

// Modal window
$string['remaininginvites'] = 'Invitations restantes : {$a}';
$string['useremail'] = 'Adresses e-mail séparées par , ou ;';
$string['adduser'] = 'Ajouter';
$string['userstoinvite'] = 'Invitations à envoyer';
$string['removeuser'] = 'Supprimer';
$string['message'] = 'Message';
$string['send'] = 'Envoyer les invitations';
// Feedback
$string['validmessage'] = '{$a} e-mail(s) ajouté(s) à la liste.';
$string['invalidmessage'] = 'Les adresses suivantes sont invalides ou ont déjà été invitées : {$a}';
$string['exceededlimit'] = 'Vous avez dépassé la limite d\'invitations que vous pouvez envoyer.';
$string['failuretosend'] = 'Échec de l\'envoi des invitations. Réessayez plus tard ou contactez l\'administrateur.';
$string['invitationssent'] = 'Invitations envoyées avec succès !';

// Email notification
$string['invitesubject'] = 'Vous êtes invité !';
$string['invitebody'] = 'Vous avez été invité à rejoindre le cours "{$a->course}" par {$a->inviter}.';
$string['invitefooter'] = '<br>Cliquez sur le lien suivant pour accepter l\'invitation :<br><a href="{$a->url}">{$a->url}</a>';

// Accepting the invitation
$string['invalid_token'] = 'Ce jeton a expiré ou est invalide.';
$string['error_login'] = 'Une erreur s\'est produite. Erreur de connexion.';
