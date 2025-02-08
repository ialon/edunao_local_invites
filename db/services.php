<?php

/**
 * Local invites external functions and services definitions.
 *
 * @author    2025 Josemaria Bolanos <admin@mako.digital>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$functions = [
    'local_invites_check_email' => [
        'classname'     => 'local_invites\external',
        'methodname'  => 'validate_email',
        'classpath'     => '',
        'description'   => 'Validate if emails are valid',
        'type'          => 'read',
        'ajax'          => true,
        'capabilities'  => 'local/invites:inviteusers',
        'loginrequired' => true,
    ],
    'local_invites_send_invites' => [
        'classname'     => 'local_invites\external',
        'methodname'  => 'send_invites',
        'classpath'     => '',
        'description'   => 'Send invites',
        'type'          => 'write',
        'ajax'          => true,
        'capabilities'  => 'local/invites:inviteusers',
        'loginrequired' => true,
    ]
];
