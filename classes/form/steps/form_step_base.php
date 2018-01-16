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

namespace local_rollover\form\steps;

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
abstract class form_step_base extends moodleform {
    /**
     * Step-specific form definition.
     */
    public abstract function step_definition();

    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', rollover_parameters::PARAM_CURRENT_STEP);
        $mform->setType(rollover_parameters::PARAM_CURRENT_STEP, PARAM_INT);

        $mform->addElement('hidden', rollover_parameters::PARAM_DESTINATION_COURSE_ID);
        $mform->setType(rollover_parameters::PARAM_DESTINATION_COURSE_ID, PARAM_INT);

        $mform->addElement('hidden', rollover_parameters::PARAM_BACKUP_ID);
        $mform->setType(rollover_parameters::PARAM_BACKUP_ID, PARAM_ALPHANUM);

        $this->step_definition();
    }

    /**
     * @param bool   $showback
     * @param string $nextlabel
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function add_action_buttons($showback = true, $nextlabel = null) {
        if (is_null($nextlabel)) {
            $nextlabel = get_string('next');
        }

        $mform = $this->_form;
        $buttonarray = [];
        if ($showback) {
            $buttonarray[] = $mform->createElement('submit', 'back', get_string('back'));
        }
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', $nextlabel);
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
    }
}
