<?php

/**
 * @package    local_invites
 * @author     2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . "/user/lib.php");

global $DB, $SESSION;

$token = required_param('token', PARAM_TEXT);

// Find invite in database
$column = $DB->sql_compare_text('token', 255);
$invite = $DB->get_record_sql(sprintf('SELECT * FROM {local_invites} WHERE %s = ?', $column), array($token));

if (!$invite) {
    \core\notification::add(get_string('invalid_token', 'local_invites'), \core\notification::ERROR);
    redirect(get_login_url());
}

// Try to find an existing user
if ($invite->userid) {
    $user = $DB->get_record('user', ['id' => $invite->userid], '*', MUST_EXIST);
} else {
    $user = $DB->get_record('user', ['email' => $invite->email]);
}

// No user, let's create one
if (!$user) {
    $user = new stdClass();
    $user->auth = 'manual';
    $user->confirmed = 1;
    $user->deleted = 0;
    $user->password = random_string(length: 8) . '1!';
    $user->mnethostid = $CFG->mnet_localhost_id;
    $user->timecreated = time();

    // Email and username from invite
    $user->email = $invite->email;
    $user->username = $invite->email;

    $user->id = user_create_user($user, true, false);

    // Reload from DB.
    $user = $DB->get_record('user', array('id' => $user->id));
}

$transaction = $DB->start_delegated_transaction();

// Try to log in
try {
    $auth = authenticate_user_login($user->email, $user->password);
    complete_user_login($user);
    $DB->delete_records('local_invites', ['id' => $invite->id]);
} catch (Exception $e) {
    $transaction->rollback($e);
    \core\notification::add(get_string('error_login', 'local_invites'), \core\notification::ERROR);
    redirect(get_login_url());
}

$transaction->allow_commit();

// Course and Role must exist
$course = $DB->get_record('course', ['id' => $invite->courseid], '*', MUST_EXIST);
$role = $DB->get_record('role', ['id' => $invite->roleid], '*', MUST_EXIST);

// Enrol user in course
$enrol = enrol_get_plugin('manual');
$manual = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'manual'), '*', MUST_EXIST);
$enrol->enrol_user($manual, $user->id, $role->id);

// Redirect to courseurl
$courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);
$SESSION->wantsurl = $courseurl->out();
redirect($courseurl);
