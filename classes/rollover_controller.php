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

namespace local_rollover;

use context_course;
use local_rollover\admin\rollover_settings;
use local_rollover\backup\backup_worker;
use local_rollover\backup\restore_worker;
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
    const STEP_ROLLOVER_COMPLETE = 'complete';

    public static function get_steps() {
        return [
            self::STEP_SELECT_SOURCE_COURSE,
            self::STEP_SELECT_CONTENT_OPTIONS,
            self::STEP_ROLLOVER_COMPLETE,
        ];
    }

    /** @var int */
    private $currentstep;

    /** @var stdClass */
    private $destinationcourse;

    /** @var moodleform */
    private $form;

    public function __construct() {
        $this->destinationcourse = get_course(required_param(rollover_parameters::PARAM_DESTINATION_COURSE_ID, PARAM_INT));
        $this->currentstep = (int)optional_param(rollover_parameters::PARAM_STEP, 0, PARAM_INT);
    }

    public function index() {
        global $PAGE;

        require_login($this->destinationcourse);
        $PAGE->set_context(context_course::instance($this->destinationcourse->id));
        $PAGE->set_url('/local/rollover/index.php',
                       [rollover_parameters::PARAM_DESTINATION_COURSE_ID => $this->destinationcourse->id]);
        $PAGE->set_heading($this->destinationcourse->fullname);

        $this->process();

        $this->show_header();
        $this->form->set_data([rollover_parameters::PARAM_STEP => $this->currentstep]);
        $this->form->display();
        $this->show_footer();
    }

    private function process() {
        $this->form = $this->create_form();

        $data = $this->form->get_data();
        if (empty($data)) {
            $this->form->set_data([rollover_parameters::PARAM_DESTINATION_COURSE_ID => $this->destinationcourse->id]);
            return;
        }

        $this->currentstep++;
        if ($this->get_current_step_name() == self::STEP_ROLLOVER_COMPLETE) {
            $options = isset($data->option) ? $data->option : [];
            $this->rollover($data->rollover_source_course_id, $data->rollover_destination_course_id, $options);
            $this->show_rollover_complete($data->rollover_source_course_id, $data->rollover_destination_course_id);
            return;
        }

        $this->form = $this->create_form();
        unset($data->submitbutton);
        $this->form->set_data($data);
    }

    public function rollover($from, $destination, $parameters) {
        $options = rollover_settings::prepare_rollover_options($parameters);

        $backupworker = backup_worker::create($from);
        $backupworker->backup();

        $restoreworker = new restore_worker($destination);
        $restoreworker->restore($backupworker->get_backup_id(), $options);
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

        return new form_source_course_selection($courses);
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
                return new form_options_selection();
            default:
                debugging("Invalid step: {$this->currentstep}");
                return null;
        }
    }

    private function get_current_step_name() {
        return self::get_steps()[$this->currentstep];
    }
}
