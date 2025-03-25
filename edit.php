<?php

/**
 * Clone of user/edit.php with the desired redirection after completing the profile.
 * 
 * @package    local_invites
 * @author     2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/gdlib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/local/invites/edit_form.php');
require_once($CFG->dirroot.'/user/editlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/webservice/lib.php');

global $USER;

$course = required_param('course', PARAM_INT);  // Course id.

$PAGE->set_url('/local/invites/edit.php', array('course' => $course));

$course = $DB->get_record('course', array('id' => $course), '*', MUST_EXIST);

$returnurl = new \moodle_url('/course/view.php', array('id' => $course->id));

$PAGE->set_pagelayout('admin');
$PAGE->add_body_class('limitedwidth');

$coursecontext = context_course::instance($course->id);
$systemcontext = context_system::instance();

// Editing existing user.
$user = $DB->get_record('user', array('id' => $USER->id), '*', MUST_EXIST);
$PAGE->set_context(context_user::instance($user->id));
$PAGE->navbar->includesettingsbase = true;
if ($node = $PAGE->navigation->find('myprofile', navigation_node::TYPE_ROOTNODE)) {
    $node->force_open();
}

// Remote users cannot be edited.
if ($user->id != -1 and is_mnet_remote_user($user)) {
    redirect($CFG->wwwroot . "/user/view.php?id=$id&course={$course->id}");
}

if ($user->deleted) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('userdeleted'));
    echo $OUTPUT->footer();
    die;
}

// Create form.

$userform = new local_invites_user_editadvanced_form(new moodle_url($PAGE->url), array('user' => $user, 'course' => $course));

if ($usernew = $userform->get_data()) {
    $authplugin = get_auth_plugin($usernew->auth);

    $usernew->timemodified = time();

    // Pass a true old $user here.
    if (!$authplugin->user_update($user, $usernew)) {
        // Auth update failed.
        throw new \moodle_exception('cannotupdateuseronexauth', '', '', $user->auth);
    }
    user_update_user($usernew, false, false);

    // Set new password if specified.
    if (!empty($usernew->newpassword)) {
        if ($authplugin->can_change_password()) {
            if (!$authplugin->user_update_password($usernew, $usernew->newpassword)) {
                throw new \moodle_exception('cannotupdatepasswordonextauth', '', '', $usernew->auth);
            }
            unset_user_preference('create_password', $usernew); // Prevent cron from generating the password.
        }
    }

    $usercontext = context_user::instance($usernew->id);

    // Reload from db.
    $usernew = $DB->get_record('user', array('id' => $usernew->id));

    // Trigger update event, after all fields are stored.
    \core\event\user_updated::create_from_userid($usernew->id)->trigger();

    // Override old $USER session variable.
    foreach ((array)$usernew as $variable => $value) {
        if ($variable === 'description' or $variable === 'password') {
            // These are not set for security nad perf reasons.
            continue;
        }
        $USER->$variable = $value;
    }
    // Preload custom fields.
    profile_load_custom_fields($USER);

    redirect($returnurl, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
}

// Display page header.
$streditmyprofile = get_string('editmyprofile');
$strparticipants  = get_string('participants');
$strnewuser       = get_string('newuser');
$userfullname     = fullname($user, true);

$PAGE->set_title("$course->shortname: $streditmyprofile");
$PAGE->set_heading($userfullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($streditmyprofile);

// Finally display THE form.
$userform->display();

// And proper footer.
echo $OUTPUT->footer();
