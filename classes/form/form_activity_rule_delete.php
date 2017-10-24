<?php
// This file is part of Moodle Course Rollover Plugin
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rollover\form;

use moodleform;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form_activity_rule_delete extends moodleform {
    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', form_activity_rule::PARAM_ACTION, 'delete');
        $mform->setType(form_activity_rule::PARAM_ACTION, PARAM_ALPHANUM);

        $mform->addElement('hidden',
                           form_activity_rule::PARAM_RULEID,
                           required_param(form_activity_rule::PARAM_RULEID, PARAM_INT));
        $mform->setType(form_activity_rule::PARAM_RULEID, PARAM_INT);

        $this->add_action_buttons(true, get_string('remove_rule', 'local_rollover'));
    }

    /**
     * Validate the parts of the request form for this module
     *
     * @param mixed[]  $data  An array of form data
     * @param string[] $files An array of form files
     * @return string[] of error messages
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
