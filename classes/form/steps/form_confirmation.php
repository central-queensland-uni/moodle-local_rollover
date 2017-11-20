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

use html_writer;
use local_rollover\backup\backup_worker;
use local_rollover\form\steps\helpers\activities_and_resources_helper;
use local_rollover\form\steps\helpers\options_helper;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form_confirmation extends form_step_base {
    /** @var backup_worker */
    private $worker;

    /** @var options_helper */
    private $optionshelper;

    /** @var activities_and_resources_helper */
    private $activitieshelper;

    public function __construct(backup_worker $worker) {
        $worker->block_modifications();
        $this->worker = $worker;
        $this->optionshelper = new options_helper($worker->get_backup_root_settings());
        $this->activitieshelper = new activities_and_resources_helper($worker->get_backup_tasks());
        parent::__construct();
    }

    /**
     * Step-specific form definition.
     */
    public function step_definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'coursesettings', get_string('courses', 'local_rollover'));
        $mform->addElement('static',
                           'originalcourse',
                           get_string('originalcourse', 'local_rollover'),
                           $this->get_original_course_link());
        $mform->addElement('static',
                           'destinationcourse',
                           get_string('destinationcourse', 'local_rollover'),
                           $this->get_destination_course_link());

        $mform->addElement('header', 'coursesettings', get_string('options', 'local_rollover'));
        $this->optionshelper->set_form($mform);
        $this->optionshelper->create_options();

        $mform->addElement('header', 'coursesettings', get_string('includeactivities', 'backup'));
        $this->activitieshelper->set_form($mform);
        $this->activitieshelper->create_tasks();

        $this->add_action_buttons(false, get_string('performrollover', 'local_rollover'));
    }

    private function get_original_course_link() {
        $course = get_course($this->worker->get_source_course_id());
        $url = new moodle_url('/course/view.php', ['name' => $course->shortname]);
        return html_writer::link($url, $course->fullname);
    }

    private function get_destination_course_link() {
        global $COURSE;
        $url = new moodle_url('/course/view.php', ['name' => $COURSE->shortname]);
        return html_writer::link($url, $COURSE->fullname);
    }
}
