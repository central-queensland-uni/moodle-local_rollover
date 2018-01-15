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

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form_source_course_selection extends form_step_base {
    /** @var string[] */
    private $mycourses;

    public function get_my_courses() {
        return $this->mycourses;
    }

    /** @var string[] */
    private $pastinstances;

    public function get_past_instances() {
        return $this->pastinstances;
    }

    /**
     * @param string[] $pastinstances Array of "Past instances" to display.
     * @param string[] $mycourses     Array of "My courses" to display.
     */
    public function __construct($pastinstances = [], $mycourses = []) {
        global $PAGE;
        $PAGE->requires->js_amd_inline('require(["local_rollover/source-course-search"]);');

        $this->mycourses = $mycourses;
        $this->pastinstances = $pastinstances;
        parent::__construct();
    }

    private function prepare_options() {
        $pastinstances = [];
        foreach ($this->pastinstances as $id => $course) {
            $pastinstances[$id] = "{$course->shortname}: {$course->fullname}";
        }

        $mycourses = [];
        foreach ($this->mycourses as $id => $course) {
            $mycourses[$id] = "{$course->shortname}: {$course->fullname}";
        }

        return [
            get_string('originalcourse_pastinstances', 'local_rollover') => $pastinstances,
            get_string('originalcourse_mycourses', 'local_rollover')     => $mycourses,
        ];
    }

    /**
     * Step-specific form definition.
     */
    public function step_definition() {
        $mform = $this->_form;

        $mform->addElement('selectgroups',
                           rollover_parameters::PARAM_SOURCE_COURSE_ID,
                           get_string('originalcourse', 'local_rollover'),
                           $this->prepare_options(),
                           ['id' => 'local_rollover-your_units', 'size' => 10]);
        $mform->setType(rollover_parameters::PARAM_SOURCE_COURSE_ID, PARAM_INT);
        $mform->addHelpButton(rollover_parameters::PARAM_SOURCE_COURSE_ID, 'originalcourse', 'local_rollover');

        $mform->addElement('text',
                           'search',
                           get_string('originalcourse_search', 'local_rollover'));
        $mform->setType('search', PARAM_TEXT);

        $this->add_action_buttons(false);
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
