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

use html_writer;
use local_rollover\local\protection;
use local_rollover\local\rollover\rollover_parameters;
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
class form_precheck extends moodleform {
    /** @var string[] */
    private $warnings;

    /** @var string[] */
    private $errors;

    public function __construct($warnings, $errors) {
        $this->warnings = $warnings;
        $this->errors = $errors;
        parent::__construct();
    }

    /**
     * Form definition.
     */
    public function definition() {
        global $OUTPUT;

        $mform = $this->_form;

        $mform->addElement('hidden', rollover_parameters::PARAM_CURRENT_STEP);
        $mform->setType(rollover_parameters::PARAM_CURRENT_STEP, PARAM_INT);

        $mform->addElement('hidden', rollover_parameters::PARAM_DESTINATION_COURSE_ID);
        $mform->setType(rollover_parameters::PARAM_DESTINATION_COURSE_ID, PARAM_INT);

        foreach ($this->warnings as $warning) {
            $warning = html_writer::tag('strong', get_string('warning')) . ': ' .
                       protection::get_config_text($warning);
            $mform->addElement('html', $OUTPUT->notification($warning, 'notifywarning'));
        }

        foreach ($this->errors as $error) {
            $error = html_writer::tag('strong', get_string('error')) . ': ' .
                     protection::get_config_text($error);
            $mform->addElement('html', $OUTPUT->notification($error, 'notifyproblem'));
        }

        if (count($this->errors) == 0) {
            $this->add_action_buttons(false, get_string('continue'));
        }
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
