<?php

/**
 * @package    local_invites
 * @author     2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/enrol/locallib.php");

function local_invites_extend_navigation_course(navigation_node $navigation, stdClass $course, $context) {
    global $PAGE, $CFG, $DB, $USER;

    if (has_capability('local/invites:inviteusers', $context)) {
        $url = new moodle_url('/local/invites/invite.php', ['id' => $course->id]);
        $node = navigation_node::create(
            get_string('invite', 'local_invites'),
            $url,
            navigation_node::TYPE_CUSTOM,
            null,
            'inviteusers',
            new pix_icon('i/assignroles', '', 'core')
        );
        $node->showinflatnavigation = true;

        // Get list of assignable roles.
        $roles = array();
        $assignableroles = get_assignable_roles($context, ROLENAME_ALIAS, false);
        $studentroles = get_archetype_roles('student');
        foreach ($assignableroles as $roleid => $role) {
            $roles[] = (object) array('id' => $roleid, 'name' => $role, 'isstudent' => in_array($roleid, array_keys($studentroles)));
        }

        // Add the node to the end of the navigation.
        $navigation->add_node($node);

        // Call init js script.
        $PAGE->requires->js_call_amd('local_invites/main', 'init', [
            $course->id,
            get_string('invitebody', 'local_invites', ['course' => $course->fullname, 'inviter' => fullname($USER)]),
            'roles' => $roles
        ]);
    }
}
