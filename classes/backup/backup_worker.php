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

namespace local_rollover\backup;

use backup;
use backup_controller;
use backup_root_task;
use local_rollover\rollover_controller;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/backup/moodle2/backup_plan_builder.class.php');

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_worker {
    public static function create($sourcecourseid) {
        $backupcontroller = new backup_controller(backup::TYPE_1COURSE,
                                                  $sourcecourseid,
                                                  backup::FORMAT_MOODLE,
                                                  backup::INTERACTIVE_YES,
                                                  backup::MODE_IMPORT,
                                                  rollover_controller::USERID);
        return new static($backupcontroller);
    }

    public static function load($backupid) {
        $backupcontroller = backup_controller::load_controller($backupid);
        return new static($backupcontroller);
    }

    /** @var backup_controller */
    private $backupcontroller;

    public function get_source_course_id() {
        return $this->backupcontroller->get_courseid();
    }

    public function get_backup_id() {
        return $this->backupcontroller->get_backupid();
    }

    public function get_path() {
        global $CFG;
        return $CFG->tempdir . '/backup/' . $this->get_backup_id();
    }

    public function backup() {
        $this->backupcontroller->finish_ui();
        $this->backupcontroller->execute_plan();
        $this->backupcontroller->destroy();
    }

    protected function __construct(backup_controller $backupcontroller) {
        $this->backupcontroller = $backupcontroller;
    }

    public function get_backup_root_task() {
        $plan = $this->backupcontroller->get_plan();
        $tasks = $plan->get_tasks();

        foreach ($tasks as $task) {
            if ($task instanceof backup_root_task) {
                return $task;
            }
        }

        debugging('backup_root_task not found');
        return null;
    }

    public function get_backup_root_settings() {
        /** @var backup_root_task $task */
        $task = $this->get_backup_root_task();
        $indexed = $task->get_settings();
        $settings = [];

        foreach ($indexed as $setting) {
            $settings[$setting->get_name()] = $setting;
        }

        unset($settings['filename']);

        return $settings;
    }

    public function save() {
        $this->backupcontroller->save_controller();

        // It cannot be reused, need to be reloaded.
        $this->backupcontroller = backup_controller::load_controller($this->get_backup_id());
    }
}
