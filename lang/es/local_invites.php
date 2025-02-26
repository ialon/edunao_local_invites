<?php

/**
 * @package    local_invites
 * @author     2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Invitar al curso';

$string['invite'] = 'Invitar al curso';
$string['invites:inviteusers'] = "Permite a un usuario acceder a la interfaz de invitaciones";

// Ventana modal
$string['remaininginvites'] = 'Invitaciones restantes: {$a}';
$string['useremail'] = 'Direcciones de correo electrónico separadas por , o ;';
$string['adduser'] = 'Agregar';
$string['userstoinvite'] = 'Invitaciones a enviar';
$string['removeuser'] = 'Eliminar';
$string['message'] = 'Mensaje';
$string['send'] = 'Enviar invitaciones';
// Retroalimentación
$string['validmessage'] = '{$a} correo(s) añadido(s) a la lista.';
$string['invalidmessage'] = 'Las siguientes direcciones son inválidas o ya han sido invitadas: {$a}';
$string['exceededlimit'] = 'Has excedido el límite de invitaciones que puedes enviar.';
$string['failuretosend'] = 'Error al enviar las invitaciones. Inténtalo de nuevo más tarde o contacta al administrador.';
$string['invitationssent'] = '¡Invitaciones enviadas exitosamente!';

// Notificación por correo electrónico
$string['invitesubject'] = '¡Estás invitado!';
$string['invitebody'] = 'Has sido invitado a unirte al curso "{$a->course}" por {$a->inviter}.';
$string['invitefooter'] = '<br>Haz clic en el siguiente enlace para aceptar la invitación:<br><a href="{$a->url}">{$a->url}</a>';

// Aceptando la invitación
$string['invalid_token'] = 'Este token ha expirado o es inválido.';
$string['error_login'] = 'Algo salió mal. Error al iniciar sesión.';
