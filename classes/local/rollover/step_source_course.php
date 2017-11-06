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

namespace local_rollover\local\rollover;

use local_rollover\admin\settings_controller;
use local_rollover\form\form_source_course_selection;
use local_rollover\regex_validator;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class step_source_course extends step {
    public function create_form() {
        $mycourses = $this->get_user_courses();
        $pastinstances = $this->get_past_instances();

        return new form_source_course_selection($pastinstances, $mycourses);
    }

    private function get_user_courses() {
        global $DB;

        $courses = get_user_capability_course('moodle/course:update');
        if ($courses === false) {
            $courses = [];
        }

        foreach ($courses as &$course) {
            $course = $course->id;
        }

        $courses = $DB->get_records_list('course',
                                         'id',
                                         $courses,
                                         'shortname ASC',
                                         'id,shortname,fullname');

        // Remove site-level and destionation course.
        unset($courses[1]);
        unset($courses[$this->controller->get_destination_course()->id]);

        return $courses;
    }

    public function get_past_instances() {
        global $DB;

        $regex = get_config('local_rollover', settings_controller::SETTING_PAST_INSTANCES_REGEX);
        if (empty($regex)) {
            return [];
        }

        $group = $this->past_instance_match($regex, $this->controller->get_destination_course()->shortname);
        if (is_null($group)) {
            return [];
        }

        $found = [];
        $courses = $DB->get_records('course', ['visible' => 1], 'shortname ASC', 'id, shortname, fullname');
        foreach ($courses as $course) {
            $match = $this->past_instance_match($regex, $course->shortname);
            if ($match === $group) {
                $found[$course->id] = $course;
            }
        }

        // Remove site-level and destionation course.
        unset($found[$this->controller->get_destination_course()->id]);

        return $found;
    }

    private function past_instance_match($regex, $shortname) {
        $validator = new regex_validator($regex);
        if (!$validator->is_valid()) {
            $error = $validator->get_error();
            debugging("Invalid regex ({$error}): {$regex}");
            return null;
        }

        if (!preg_match($regex, $shortname, $matches)) {
            return null;
        }

        // We are interested in the first capture group.
        if (count($matches) < 2) {
            return null;
        }

        $match = $matches[1];
        if (empty($match)) {
            return null;
        }

        return $match;
    }

    public function process_form_data($data) {
        $backupworker = $this->controller->get_backup_worker();
        $data->rollover_backup_id = $backupworker->get_backup_id();
        $backupworker->save();
    }
}
