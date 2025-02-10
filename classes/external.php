<?php

/**
 * Local invites external functions.
 *
 * @author    2025 Josemaria Bolanos <admin@mako.digital>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_invites;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * The API to check emails and send invites.
 *
 * @package    local_invites
 * @author     2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {
    /**
     * Returns description of parameters.
     *
     * @return external_function_parameters
     */
    public static function validate_email_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID.'),
            'emails' => new external_value(PARAM_RAW, 'Comma or semicolon separated list of emails.')
        ]);
    }

    /**
     * Returns an array with results of email validation, id, email and first and last name if user already exists.
     *
     * @param string $emails Comma or semicolon separated list of emails.
     * @param int $courseid Course ID.
     * @return array
     */
    public static function validate_email(string $emails, int $courseid) {
        global $DB;

        $results = [
            'valid' => [],
            'invalid' => []
        ];

        // Separate emails by comma or semicolon.
        $emails = preg_split('/[,;]+/', $emails);

        foreach ($emails as $email) {
            $email = trim($email);
            $email = strtolower($email);

            $result = new \stdClass();
            $result->email = $email;
            $result->valid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
            $result->userid = null;
            $result->name = null;

            // Invalid because he has already been invited.
            if ($exists = $DB->get_record('local_invites', ['email' => $email, 'courseid' => $courseid])) {
                $result->valid = false;
            }

            if ($result->valid) {
                if ($user = $DB->get_record('user', array('email' => $email))) {
                    $result->userid = $user->id;
                    $result->name = fullname($user);
                }
                $results['valid'][] = $result;
            } else {
                $results['invalid'][] = $result;
            }
        }

        return $results;
    }

    /**
     * Returns an array with results of email validation, id, email and first and last name if user already exists.
     *
     * @return external_single_structure
     */
    public static function validate_email_returns() {
        $result = new external_single_structure([
            'email'  => new external_value(PARAM_NOTAGS, 'Cleaned email'),
            'valid' => new external_value(PARAM_BOOL, 'Email is valid'),
            'userid' => new external_value(PARAM_INT, 'ID of user if exists'),
            'name'   => new external_value(PARAM_TEXT, 'Full name of user if exists')
        ]);

        return new external_single_structure([
            'valid' => new external_multiple_structure(
                $result
            ),
            'invalid' => new external_multiple_structure(
                $result
            )
        ]);
    }

    /**
     * Returns description of parameters.
     *
     * @return external_function_parameters
     */
    public static function send_invites_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID.'),
            'invitations' => new external_multiple_structure(
                new external_single_structure([
                    'email'  => new external_value(PARAM_NOTAGS, 'User email'),
                    'roleid' => new external_value(PARAM_INT, 'ID of role to assign'),
                ]),
            ),
            'message' => new external_value(PARAM_TEXT, 'Message to send.')
        ]);
    }

    /**
     * Returns an array with results of email sending and email.
     *
     * @param int $courseid Course ID.
     * @param array $invitations Objects with email and role
     * @param string $message Message body.
     * @return object
     */
    public static function send_invites(int $courseid, array $invitations, string $message) {
        global $DB, $USER, $PAGE;

        $PAGE->set_context(\context_system::instance());

        $transaction = $DB->start_delegated_transaction();

        foreach ($invitations as &$invitation) {
            $invitation['email'] = trim($invitation['email']);
            $invitation['courseid'] = $courseid;
            $invitation['userid'] = null;
            $invitation['timecreated'] = time();
            $invitation['token'] = hash('sha256', $invitation['email'] . $courseid . $invitation['timecreated']);

            if (filter_var($invitation['email'], FILTER_VALIDATE_EMAIL) !== false) {
                if ($user = $DB->get_record('user', array('email' => $invitation['email']))) {
                    $invitation['userid'] = $user->id;
                }
            } else {
                $error = new \Exception('Invalid email. Somebody changed it in the client side.');
                $DB->rollback_delegated_transaction($transaction, $error);
                return (object) ['success' => false, 'message' => get_string('failuretosend', 'local_invites')];
            }

            $DB->insert_record('local_invites', $invitation);
        }

        $transaction->allow_commit();

        // Generate invitations
        foreach ($invitations as $invitation) {
            // To and From
            $tempuser = $DB->get_record('user', array('id' => $USER->id), '*', MUST_EXIST);
            $tempuser->email = $invitation['email'];
            $noreplyuser = \core_user::get_noreply_user();

            // Subject and Body
            $subject = get_string('invitesubject', 'local_invites');
            $url = new \moodle_url('/local/invites/accept.php', ['token' => $invitation['token']]);
            $footer = get_string('invitefooter', 'local_invites', ['url' => $url]);
            $messagetext = $message . $footer;
            $messagehtml = text_to_html($messagetext, false, false, true);
            
            email_to_user($tempuser, $noreplyuser, $subject, $messagetext, $messagehtml);
        }

        return (object) ['success' => true];
    }

    /**
     * Return succes or failure
     *
     * @return external_single_structure
     */
    public static function send_invites_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Email were sent'),
            'message' => new external_value(PARAM_TEXT, 'Error Message', VALUE_OPTIONAL)
        ]);
    }
}
