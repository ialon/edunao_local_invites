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
            'emails' => new external_value(PARAM_RAW, 'Comma or semicolon separated list of emails.')
        ]);
    }

    /**
     * Returns an array with results of email validation, id, email and first and last name if user already exists.
     *
     * @param string $emails Comma or semicolon separated list of emails.
     * @return array
     */
    public static function validate_email(string $emails) {
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
            'emails' => new external_value(PARAM_RAW, 'Comma or semicolon separated list of emails.')
        ]);
    }

    /**
     * Returns an array with results of email sending and email.
     *
     * @param string $emails Comma or semicolon separated list of emails.
     * @return array
     */
    public static function send_invites(string $emails) {
        global $DB;

        $results = [];

        // Separate emails by comma or semicolon.
        $emails = preg_split('/[,;]+/', $emails);

        foreach ($emails as $email) {
            $email = trim($email);

            $result = new \stdClass();
            $result->email = $email;
            $result->sent = false;

            if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
                // TOOD: Send email here.
                $result->sent = true;
            }

            $results[] = $result;
        }

        return $results;
    }

    /**
     * Returns an array with results of email validation, id, email and first and last name if user already exists.
     *
     * @return external_multiple_structure
     */
    public static function send_invites_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'email'  => new external_value(PARAM_NOTAGS, 'Email'),
                'sent' => new external_value(PARAM_BOOL, 'Email is sent')
            ])
        );
    }
}
