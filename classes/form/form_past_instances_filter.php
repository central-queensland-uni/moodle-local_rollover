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

use local_rollover\admin\settings_controller;
use local_rollover\regex_validator;
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
class form_past_instances_filter extends moodleform {
    const FIELD_REGEX = 'regex';

    /** @var bool */
    private $saved;

    public function is_saved() {
        return $this->saved;
    }

    public function __construct() {
        parent::__construct();
        $this->saved = $this->process();
    }

    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;

        $regex = get_config('local_rollover', settings_controller::SETTING_PAST_INSTANCES_REGEX);
        $mform->addElement('text', self::FIELD_REGEX, get_string('regex', 'local_rollover'));
        $mform->setType(self::FIELD_REGEX, PARAM_TEXT);
        $mform->setDefault(self::FIELD_REGEX, $regex ? $regex : '');
        $mform->addHelpButton(self::FIELD_REGEX, 'regex', 'local_rollover');

        $this->add_action_buttons(false, get_string('save', 'local_rollover'));
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
        $regex = $data[self::FIELD_REGEX];

        $validator = new regex_validator($regex, [regex_validator::OPTION_REQUIRE_CAPTURE_GROUP]);
        if (!$validator->is_valid()) {
            $errors[self::FIELD_REGEX] = $validator->get_error();
        }

        return $errors;
    }

    private function process() {
        if ($this->is_cancelled() || !$this->is_submitted()) {
            return false;
        }

        require_sesskey();

        $data = $this->get_data();
        if (!$data) {
            return false;
        }

        set_config(settings_controller::SETTING_PAST_INSTANCES_REGEX, $data->regex, 'local_rollover');

        $this->saved = true;

        return true;
    }
}
