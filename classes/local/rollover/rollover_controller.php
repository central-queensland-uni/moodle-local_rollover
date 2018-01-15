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

use context_course;
use local_rollover\backup\backup_worker;
use local_rollover\backup\restore_worker;
use local_rollover\capabilities;
use local_rollover\event\rollover_completed;
use local_rollover\event\rollover_started;
use local_rollover\local\protection;
use moodle_exception;
use moodle_url;
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

    const STEP_PRECHECK = 'precheck';

    const STEP_SELECT_SOURCE_COURSE = 'source_course';

    const STEP_SELECT_CONTENT_OPTIONS = 'content_options';

    const STEP_SELECT_ACTIVITIES_AND_RESOURCES = 'activities_and_resources';

    const STEP_CONFIRMATION = 'confirmation';

    const STEP_COMPLETE = 'complete';

    public static function get_steps() {
        return [
            self::STEP_PRECHECK,
            self::STEP_SELECT_SOURCE_COURSE,
            self::STEP_SELECT_CONTENT_OPTIONS,
            self::STEP_SELECT_ACTIVITIES_AND_RESOURCES,
            self::STEP_CONFIRMATION,
            self::STEP_COMPLETE,
        ];
    }

    public static function get_step_index($step) {
        return array_search($step, static::get_steps(), true);
    }

    /** @var int */
    private $currentstep;

    /** @var int */
    private $sourcecourseid = null;

    public function set_source_course_id($sourcecourseid) {
        $this->sourcecourseid = $sourcecourseid;
    }

    /** @var stdClass */
    private $destinationcourse;

    public function get_destination_course() {
        return $this->destinationcourse;
    }

    /** @var backup_worker */
    private $backupworker = null;

    /** @var rollover_progress */
    private $progressbackup = null;

    /** @var rollover_progress */
    private $progressrestore = null;

    public function get_backup_worker() {
        if (is_null($this->backupworker)) {
            $backupid = optional_param(rollover_parameters::PARAM_BACKUP_ID, null, PARAM_ALPHANUM);
            if (empty($backupid)) {
                if (empty($this->sourcecourseid)) {
                    throw new moodle_exception('Missing source course id.');
                }
                $this->backupworker = backup_worker::create($this->sourcecourseid);
            } else {
                $this->backupworker = backup_worker::load($backupid);
            }
        }
        return $this->backupworker;
    }

    public function __construct() {
        if (!defined('NO_OUTPUT_BUFFERING') || !NO_OUTPUT_BUFFERING) {
            debugging('Missing NO_OUTPUT_BUFFERING');
        }

        $this->destinationcourse = get_course(required_param(rollover_parameters::PARAM_DESTINATION_COURSE_ID, PARAM_INT));
        $this->currentstep = (int)optional_param(rollover_parameters::PARAM_CURRENT_STEP, 0, PARAM_INT);
    }

    public function index() {
        global $PAGE, $USER;

        $context = context_course::instance($this->destinationcourse->id);
        require_capability(capabilities::CAPABILITY_PERFORM_ROLLOVER, $context, $USER);

        require_login($this->destinationcourse);

        $PAGE->set_context(context_course::instance($this->destinationcourse->id));
        $PAGE->set_url('/local/rollover/index.php',
                       [rollover_parameters::PARAM_DESTINATION_COURSE_ID => $this->destinationcourse->id]);
        $PAGE->set_heading($this->destinationcourse->fullname);
        $PAGE->add_body_class('path-backup');

        $form = $this->process();

        if (!is_null(($form))) {
            $this->show_header();
            $form->set_data([rollover_parameters::PARAM_CURRENT_STEP => $this->currentstep]);
            $form->display();
            $this->show_footer();
        }
    }

    private function process() {
        $form = $this->get_step()->create_form();
        if ($form->is_cancelled()) {
            redirect(course_get_url($this->destinationcourse));
        }

        $data = $form->get_data();
        if (empty($data)) {
            $form->set_data([rollover_parameters::PARAM_DESTINATION_COURSE_ID => $this->destinationcourse->id]);
            return $form;
        }
        if (!empty($data->{rollover_parameters::PARAM_SOURCE_COURSE_ID})) {
            $this->sourcecourseid = $data->{rollover_parameters::PARAM_SOURCE_COURSE_ID};
        }

        $this->get_step()->process_form_data($data);

        $this->currentstep += empty($data->back) ? 1 : -1;

        if ($this->complete_rollover()) {
            return null;
        }

        $form = $this->get_step()->create_form();
        unset($data->submitbutton);

        $form->set_data($data);

        return $form;
    }

    private function complete_rollover() {
        if ($this->get_current_step_name() != self::STEP_COMPLETE) {
            return false;
        }

        $from = $this->get_backup_worker()->get_source_course_id();
        $from = get_course($from);

        $destination = $this->destinationcourse->id;
        $destination = get_course($destination);

        $this->start_rollover_ui($from, $destination);
        $this->rollover();
        $this->finish_rollover_ui($from, $destination);

        return true;
    }

    public function rollover() {
        $this->fire_event(rollover_started::class);

        $backupworker = $this->get_backup_worker();
        $backupworker->backup($this->progressbackup);

        $destination = $this->destinationcourse->id;
        $restoreworker = new restore_worker($destination);
        $restoreworker->restore($backupworker->get_backup_id(), $this->progressrestore);

        $this->fire_event(rollover_completed::class);
    }

    public function start_rollover_ui($from, $destination) {
        $this->show_header();
        echo '<hr />';

        $loading = get_string('progress_loading', 'local_rollover', htmlentities($from->shortname));
        $this->progressbackup = new rollover_progress($loading);

        $saving = get_string('progress_saving', 'local_rollover', htmlentities($destination->shortname));
        $this->progressrestore = new rollover_progress($saving);
    }

    public function finish_rollover_ui($from, $destination) {
        global $OUTPUT;

        $warnings = $this->get_backup_worker()->get_warnings();
        $sucessorwarn = 'rolloversuccessful' . (count($warnings) == 0 ? '' : 'warn');

        echo $OUTPUT->heading(get_string($sucessorwarn, 'local_rollover'));

        foreach ($warnings as $warning) {
            echo $OUTPUT->notification(get_string($warning, 'backup'), 'notifyproblem');
        }

        echo get_string("{$sucessorwarn}message", 'local_rollover', [
            'from' => htmlentities($from->shortname),
            'into' => htmlentities($destination->shortname),
        ]);
        echo '<hr />';

        $url = new moodle_url('/course/view.php', ['id' => $this->destinationcourse->id]);
        echo $OUTPUT->single_button($url, get_string('proceed', 'local_rollover'), 'get');

        $this->show_footer();
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

    public function get_step() {
        $this->enforce_protection();

        $class = step::class . '_' . $this->get_current_step_name();
        if (!class_exists($class)) {
            throw new moodle_exception("Invalid class '{$class}' for step: {$this->currentstep}");
        }

        /** @var step $step */
        $step = new $class($this);
        if (!($step instanceof step)) {
            throw new moodle_exception("Class '{$class}' must extend: " . step::class);
        }

        if ($step->skipped()) {
            $this->currentstep++;
            return $this->get_step();
        }

        return $step;
    }

    private function get_current_step_name() {
        return self::get_steps()[$this->currentstep];
    }

    private function enforce_protection() {
        $protector = new protection($this->get_destination_course());
        if ($protector->has_errors()) {
            $this->currentstep = self::get_step_index(self::STEP_PRECHECK);
        }
    }

    public function fire_event($class) {
        $backupworker = $this->get_backup_worker();
        $destinationcourseid = $this->get_destination_course()->id;

        $data = [
            'context'  => context_course::instance($destinationcourseid),
            'objectid' => $destinationcourseid,
            'other'    => [
                'sourceid' => $backupworker->get_source_course_id(),
                'backupid' => $backupworker->get_backup_id(),
                'filename' => $backupworker->get_history_filename(),
            ],
        ];

        $event = $class::create($data);
        $event->trigger();
    }
}
