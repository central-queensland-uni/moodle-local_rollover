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

use backup_root_task;
use context_course;
use local_rollover\admin\settings_controller;
use local_rollover\backup\backup_worker;
use local_rollover\backup\restore_worker;
use local_rollover\form\form_activities_and_resources_selection;
use local_rollover\form\form_options_selection;
use local_rollover\form\form_source_course_selection;
use moodle_url;
use moodleform;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rollover_controller {
    /** Run backup/restore as admin (bypass normal capability check for courses). */
    const USERID = 2;
    const STEP_SELECT_SOURCE_COURSE = 'source_course';
    const STEP_SELECT_CONTENT_OPTIONS = 'content_options';
    const STEP_SELECT_ACTIVITIES_AND_RESOURCES = 'activities_and_resources';
    const STEP_ROLLOVER_COMPLETE = 'complete';

    public static function get_steps() {
        return [
            self::STEP_SELECT_SOURCE_COURSE,
            self::STEP_SELECT_CONTENT_OPTIONS,
            self::STEP_SELECT_ACTIVITIES_AND_RESOURCES,
            self::STEP_ROLLOVER_COMPLETE,
        ];
    }

    public static function get_step_index($step) {
        return array_search($step, static::get_steps(), true);
    }

    /** @var int */
    private $currentstep;

    /** @var stdClass */
    private $destinationcourse;

    /** @var moodleform */
    private $form;

    /** @var backup_worker */
    private $backupworker = null;

    private function get_backup_worker() {
        if (is_null($this->backupworker)) {
            $backupid = optional_param(rollover_parameters::PARAM_BACKUP_ID, null, PARAM_ALPHANUM);
            if (is_null($backupid)) {
                $sourceid = required_param(rollover_parameters::PARAM_SOURCE_COURSE_ID, PARAM_INT);
                $this->backupworker = backup_worker::create($sourceid);
            } else {
                $this->backupworker = backup_worker::load($backupid);
            }
        }
        return $this->backupworker;
    }

    public function __construct() {
        $this->destinationcourse = get_course(required_param(rollover_parameters::PARAM_DESTINATION_COURSE_ID, PARAM_INT));
        $this->currentstep = (int)optional_param(rollover_parameters::PARAM_CURRENT_STEP, 0, PARAM_INT);
    }

    public function index() {
        global $PAGE;

        require_login($this->destinationcourse);
        $PAGE->set_context(context_course::instance($this->destinationcourse->id));
        $PAGE->set_url('/local/rollover/index.php',
                       [rollover_parameters::PARAM_DESTINATION_COURSE_ID => $this->destinationcourse->id]);
        $PAGE->set_heading($this->destinationcourse->fullname);
        $PAGE->add_body_class('path-backup');

        $this->process();

        if (!is_null(($this->form))) {
            $this->show_header();
            $this->form->set_data([rollover_parameters::PARAM_CURRENT_STEP => $this->currentstep]);
            $this->form->display();
            $this->show_footer();
        }
    }

    private function process() {
        $this->form = $this->create_form();

        $data = $this->form->get_data();
        if (empty($data)) {
            $this->form->set_data([rollover_parameters::PARAM_DESTINATION_COURSE_ID => $this->destinationcourse->id]);
            return;
        }

        $this->process_form_data($data);
        $this->currentstep++;

        if ($this->process_rollover_completed($data)) {
            return;
        }

        $this->form = $this->create_form();
        unset($data->submitbutton);
        $this->form->set_data($data);
    }

    private function process_rollover_completed($data) {
        if ($this->get_current_step_name() != self::STEP_ROLLOVER_COMPLETE) {
            return false;
        }

        $this->rollover($data);
        $this->show_rollover_complete($this->get_backup_worker()->get_source_course_id(),
                                      $data->{rollover_parameters::PARAM_DESTINATION_COURSE_ID});
        $this->form = null;
        return true;
    }

    private function process_form_data($data) {
        $step = $this->get_current_step_name();
        switch ($step) {
            case self::STEP_SELECT_SOURCE_COURSE:
                return $this->process_form_data_step_select_source_course($data);
            case  self::STEP_SELECT_CONTENT_OPTIONS:
                return $this->process_form_data_step_select_content_options($data);
            case self::STEP_SELECT_ACTIVITIES_AND_RESOURCES:
                $this->process_form_data_select_activities_and_resources($data);
                return true;
            default:
                return false;
        }
    }

    private function process_form_data_step_select_source_course($data) {
        $data->rollover_backup_id = $this->get_backup_worker()->get_backup_id();
        $this->backupworker->save();
        return true;
    }

    private function process_form_data_step_select_content_options($data) {
        $settings = $this->get_backup_worker()->get_backup_root_settings();
        foreach ($settings as $setting) {
            $name = $setting->get_ui_name();
            $value = isset($data->$name) ? $data->$name : 0;
            if ($value != $setting->get_value()) {
                $setting->set_value($value);
            }
        }
        $this->backupworker->save();
        return true;
    }

    public function rollover($parameters) {
        $backupworker = $this->get_backup_worker();
        $backupworker->backup();

        $destination = $parameters->{rollover_parameters::PARAM_DESTINATION_COURSE_ID};
        $restoreworker = new restore_worker($destination);
        $restoreworker->restore($backupworker->get_backup_id());
    }

    public function show_rollover_complete($from, $destination) {
        global $OUTPUT;

        $this->show_header();

        $from = get_course($from);
        $destination = get_course($destination);

        echo get_string('rolloversuccessfulmessage', 'local_rollover', [
            'from' => htmlentities($from->shortname),
            'into' => htmlentities($destination->shortname),
        ]);
        echo '<br /><br />';

        $url = new moodle_url('/course/view.php', ['id' => $destination->id]);
        echo $OUTPUT->single_button($url, get_string('proceed', 'local_rollover'), 'get');

        $this->show_footer();
    }

    /**
     * @return form_source_course_selection
     */
    public function create_form_source_course_selection() {
        $mycourses = $this->get_user_courses();
        $pastinstances = $this->get_past_instances();

        return new form_source_course_selection($pastinstances, $mycourses);
    }

    private function show_header() {
        global $OUTPUT;

        $stepname = $this->get_current_step_name();
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string("step_{$stepname}", 'local_rollover'));
    }

    private function show_footer() {
        global $OUTPUT;

        echo $OUTPUT->footer();
    }

    private function create_form() {
        $stepname = $this->get_current_step_name();
        switch ($stepname) {
            case self::STEP_SELECT_SOURCE_COURSE:
                return $this->create_form_source_course_selection();
            case self::STEP_SELECT_CONTENT_OPTIONS:
                return new form_options_selection($this->get_backup_worker()->get_backup_root_settings());
            case self::STEP_SELECT_ACTIVITIES_AND_RESOURCES:
                return new form_activities_and_resources_selection($this->get_backup_worker()->get_backup_tasks());
            default:
                debugging("Invalid step: {$this->currentstep}");
                return null;
        }
    }

    private function get_current_step_name() {
        return self::get_steps()[$this->currentstep];
    }

    private function process_form_data_select_activities_and_resources($data) {
        $tasks = $this->get_backup_worker()->get_backup_tasks();
        foreach ($tasks as &$task) {
            if ($task instanceof backup_root_task) {
                continue;
            }
            $settings = $task->get_settings();
            foreach ($settings as &$setting) {
                $name = $setting->get_ui_name();
                $value = isset($data->$name) ? $data->$name : 0;
                if ($value != $setting->get_value()) {
                    $setting->set_value($value);
                }
            }
        }
        return true;
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
        unset($courses[$this->destinationcourse->id]);

        return $courses;
    }

    private function get_past_instances() {
        global $DB;

        $regex = get_config('local_rollover', settings_controller::SETTING_PAST_INSTANCES_REGEX);
        if (empty($regex)) {
            return [];
        }

        $group = $this->past_instance_match($regex, $this->destinationcourse->shortname);
        if (is_null($group)) {
            return [];
        }

        $found = [];
        $courses = $DB->get_records('course', null, 'shortname ASC', 'id, shortname, fullname');
        foreach ($courses as $course) {
            $match = $this->past_instance_match($regex, $course->shortname);
            if ($match === $group) {
                $found[$course->id] = $course;
            }
        }

        // Remove site-level and destionation course.
        unset($found[$this->destinationcourse->id]);

        return $found;
    }

    private function past_instance_match($regex, $shortname) {
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
}
