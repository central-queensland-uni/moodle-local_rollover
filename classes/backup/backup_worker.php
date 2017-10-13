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
use local_rollover\rollover_controller;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_worker {
    public static function create($sourcecourseid) {
        return new static($sourcecourseid);
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
        $this->backupcontroller->execute_plan();
        $this->backupcontroller->destroy();
    }

    protected function __construct($sourcecourseid) {
        $this->backupcontroller = new backup_controller(backup::TYPE_1COURSE,
                                                        $sourcecourseid,
                                                        backup::FORMAT_MOODLE,
                                                        backup::INTERACTIVE_NO,
                                                        backup::MODE_IMPORT,
                                                        rollover_controller::USERID);
    }
}
