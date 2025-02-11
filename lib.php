<?php

/**
 * @package    local_invites
 * @author     2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/enrol/locallib.php");

function local_invites_extend_sharecourse(stdClass $course, $context) {
    global $PAGE, $USER;

    // Get list of assignable roles.
    $roles = array();
    $assignableroles = get_assignable_roles($context, ROLENAME_ALIAS, false);
    $studentroles = get_archetype_roles('student');
    foreach ($assignableroles as $roleid => $role) {
        $roles[] = (object) array('id' => $roleid, 'name' => $role, 'isstudent' => in_array($roleid, array_keys($studentroles)));
    }

    // Call init js script.
    $PAGE->requires->js_call_amd('local_invites/main', 'init', [
        $course->id,
        get_string('invitebody', 'local_invites', ['course' => $course->fullname, 'inviter' => fullname($USER)]),
        'roles' => $roles
    ]);

    return html_writer::link(
        '#',
        '<i class="fa fa-user-plus"></i>',
        [
            'id' => 'openInviteUsers',
            'target' => '_blank',
            'title' => get_string('invite', 'local_invites'),
            'class' => 'btn btn-primary mx-1 mr-5',
        ]
    );
}
