<?php

/**
 * @package    local_invites
 * @author     2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Invite to course';

$string['invite'] = 'Invite to course';
$string['invites:inviteusers'] = "Allows a user to access the invites interface";

// Modal window
$string['remaininginvites'] = 'Remaining invites: {$a}';
$string['useremail'] = 'Email addresses separated by , or ;';
$string['adduser'] = 'Add';
$string['userstoinvite'] = 'Invitations to be sent';
$string['removeuser'] = 'Remove';
$string['message'] = 'Message';
$string['send'] = 'Send invites';
// Feedback
$string['validmessage'] = '{$a} email(s) added to the list.';
$string['invalidmessage'] = 'The following addresses are invalid or have already been invited: {$a}';
$string['exceededlimit'] = 'You have exceeded the limit of invites you can send.';
$string['failuretosend'] = 'Failure to send invites. Try again later or contact the administrator.';
$string['invitationssent'] = 'Invitations sent successfully!';

// Email notification
$string['invitesubject'] = 'You are invited!';
$string['invitebody'] = 'You have been invited to join the course "{$a->course}" by {$a->inviter}.';
$string['invitefooter'] = '<br>Click on the following link to accept the invitation:<br><a href="{$a->url}">{$a->url}</a>';

// Accepting the invitation
$string['invalid_token'] = 'This token has expired or is invalid.';
$string['error_login'] = 'Something went wrong. Error logging in.';
