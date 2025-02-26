<?php

/**
 * @package    local_invites
 * @author     2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Invita al corso';

$string['invite'] = 'Invita al corso';
$string['invites:inviteusers'] = "Consente a un utente di accedere all'interfaccia degli inviti";

// Finestra modale
$string['remaininginvites'] = 'Inviti rimanenti: {$a}';
$string['useremail'] = 'Indirizzi email separati da , o ;';
$string['adduser'] = 'Aggiungi';
$string['userstoinvite'] = 'Inviti da inviare';
$string['removeuser'] = 'Rimuovi';
$string['message'] = 'Messaggio';
$string['send'] = 'Invia inviti';
// Feedback
$string['validmessage'] = '{$a} email aggiunte alla lista.';
$string['invalidmessage'] = 'I seguenti indirizzi sono non validi o sono già stati invitati: {$a}';
$string['exceededlimit'] = 'Hai superato il limite di inviti che puoi inviare.';
$string['failuretosend'] = 'Invio degli inviti fallito. Riprova più tardi o contatta l\'amministratore.';
$string['invitationssent'] = 'Inviti inviati con successo!';

// Notifica email
$string['invitesubject'] = 'Sei invitato!';
$string['invitebody'] = 'Sei stato invitato a partecipare al corso "{$a->course}" da {$a->inviter}.';
$string['invitefooter'] = '<br>Fai clic sul seguente link per accettare l\'invito:<br><a href="{$a->url}">{$a->url}</a>';

// Accettazione dell'invito
$string['invalid_token'] = 'Questo token è scaduto o non è valido.';
$string['error_login'] = 'Qualcosa è andato storto. Errore durante l\'accesso.';

