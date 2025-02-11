<?php

/**
 * @package    local_invites
 * @author     2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/enrol/locallib.php");

function local_invites_extend_sharecourse(stdClass $course, $context) {
    if (!has_capability('local/invites:inviteusers', $context)) {
        return '';
    }

    global $DB, $PAGE, $USER;

    // Get list of assignable roles.
    $roles = array();
    $assignableroles = get_assignable_roles($context, ROLENAME_ALIAS, false);
    $studentroles = get_archetype_roles('student');
    foreach ($assignableroles as $roleid => $role) {
        $roles[] = (object) array('id' => $roleid, 'name' => $role, 'isstudent' => in_array($roleid, array_keys($studentroles)));
    }

    // Get count of enrolled users, pending invites and invites created in the last 24 hours.
    $enrolled = count(get_enrolled_users($context, '', 0, 'u.*', null, 0, 0, true));
    $pending = $DB->count_records('local_invites', ['courseid' => $course->id]);
    $recent = $DB->count_records_sql("SELECT COUNT(*) FROM {local_invites} WHERE courseid = ? AND timecreated > ?", [$course->id, time() - DAYSECS]);

    // Calculate remaining invites.
    $maxenrolled = 25;  // Maximum number of enrolled users.
    $maxrecent = 5;     // Maximum number of invites created in the last 24 hours.

    $remaining = $maxenrolled - $enrolled - $pending;

    if ($recent >= $maxrecent) {
        $remaining = 0;
    } else {
        $remaining = min($remaining, $maxrecent - $recent);
    }

    if (is_siteadmin()) {
        $remaining = 1000;
    }

    // Call init js script.
    $PAGE->requires->js_call_amd('local_invites/main', 'init', [
        $course->id,
        get_string('invitebody', 'local_invites', ['course' => $course->fullname, 'inviter' => fullname($USER)]),
        'roles' => $roles,
        'remaining' => $remaining
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
