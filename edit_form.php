<?php

/**
 * Form for editing a users profile
 *
 * @package    local_invites
 * @author     2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page.
}

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/user/lib.php');

/**
 * Class local_invites_user_editadvanced_form.
 */
class local_invites_user_editadvanced_form extends moodleform {

    /**
     * Define the form.
     */
    public function definition() {
        global $USER, $CFG;

        $mform = $this->_form;

        if (!is_array($this->_customdata)) {
            throw new coding_exception('invalid custom data for user_edit_form');
        }
        $user = $this->_customdata['user'];
        $course = $this->_customdata['course'];
        $userid = $user->id;

        // Accessibility: "Required" is bad legend text.
        $strgeneral  = get_string('general');
        $strrequired = get_string('required');

        // Add some extra hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', core_user::get_property_type('id'));
        $mform->addElement('hidden', 'course', $course->id);
        $mform->setType('course', PARAM_INT);
        $mform->addElement('hidden', 'auth', $user->auth);
        $mform->setType('auth', core_user::get_property_type('auth'));

        // Print the required moodle fields first.
        $mform->addElement('header', 'moodle', $strgeneral);

        // Add the necessary names.
        $stringman = get_string_manager();
        foreach (useredit_get_required_name_fields() as $fullname) {
            $purpose = user_edit_map_field_purpose($user->id, $fullname);
            $mform->addElement('text', $fullname,  get_string($fullname),  'maxlength="100" size="30"' . $purpose);
            if ($stringman->string_exists('missing'.$fullname, 'core')) {
                $strmissingfield = get_string('missing'.$fullname, 'core');
            } else {
                $strmissingfield = $strrequired;
            }
            $mform->addRule($fullname, $strmissingfield, 'required', null, 'client');
            $mform->setType($fullname, PARAM_NOTAGS);
        }
        
        $enabledusernamefields = useredit_get_enabled_name_fields();
        // Add the enabled additional name fields.
        foreach ($enabledusernamefields as $addname) {
            $purpose = user_edit_map_field_purpose($user->id, $addname);
            $mform->addElement('text', $addname,  get_string($addname), 'maxlength="100" size="30"' . $purpose);
            $mform->setType($addname, PARAM_NOTAGS);
        }

        $purpose = user_edit_map_field_purpose($userid, 'username');
        $mform->addElement('text', 'username', get_string('username'), 'size="20"' . $purpose);
        $mform->addHelpButton('username', 'username', 'auth');
        $mform->setType('username', PARAM_RAW);

        if (!empty($CFG->passwordpolicy)) {
            $mform->addElement('static', 'passwordpolicyinfo', '', print_password_policy());
        }

        $purpose = user_edit_map_field_purpose($userid, 'password');
        $mform->addElement('passwordunmask', 'newpassword', get_string('newpassword'),
            'maxlength="'.MAX_PASSWORD_CHARACTERS.'" size="20"' . $purpose);
        $mform->addRule('newpassword', get_string('maximumchars', '', MAX_PASSWORD_CHARACTERS),
            'maxlength', MAX_PASSWORD_CHARACTERS, 'client');
        $mform->addHelpButton('newpassword', 'newpassword');
        $mform->setType('newpassword', core_user::get_property_type('password'));

        $btnstring = get_string('updatemyprofile');

        $this->add_action_buttons(false, $btnstring);

        $this->set_data($user);
    }

    /**
     * Extend the form definition after data has been parsed.
     */
    public function definition_after_data() {
        global $DB;

        $mform = $this->_form;

        // Trim required name fields.
        foreach (useredit_get_required_name_fields() as $field) {
            $mform->applyFilter($field, 'trim');
        }

        // Admin must choose some password and supply correct email.
        $mform->addRule('newpassword', get_string('required'), 'required', null, 'client');
    }

    /**
     * Validate the form data.
     * @param array $usernew
     * @param array $files
     * @return array|bool
     */
    public function validation($usernew, $files) {
        global $CFG, $DB;

        $usernew = (object)$usernew;
        $usernew->username = trim($usernew->username);

        $user = $DB->get_record('user', array('id' => $usernew->id));
        $err = array();

        if (!empty($usernew->newpassword)) {
            $errmsg = ''; // Prevent eclipse warning.
            if (!check_password_policy($usernew->newpassword, $errmsg, $usernew)) {
                $err['newpassword'] = $errmsg;
            }
        } else {
            $auth = get_auth_plugin($usernew->auth);
            if ($auth->is_internal()) {
                // Internal accounts require password!
                $err['newpassword'] = get_string('required');
            }
        }

        if (empty($usernew->username)) {
            // Might be only whitespace.
            $err['username'] = get_string('required');
        } else if (!$user or $user->username !== $usernew->username) {
            // Check new username does not exist.
            if ($DB->record_exists('user', array('username' => $usernew->username, 'mnethostid' => $CFG->mnet_localhost_id))) {
                $err['username'] = get_string('usernameexists');
            }
            // Check allowed characters.
            if ($usernew->username !== core_text::strtolower($usernew->username)) {
                $err['username'] = get_string('usernamelowercase');
            } else {
                if ($usernew->username !== core_user::clean_field($usernew->username, 'username')) {
                    $err['username'] = get_string('invalidusername');
                }
            }
        }

        // Next the customisable profile fields.
        $err += profile_validation($usernew, $files);

        if (count($err) == 0) {
            return true;
        } else {
            return $err;
        }
    }
}


