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

use backup;
use backup_controller;
use restore_controller;

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
class rollover_worker {
    /** Run backup/restore as admin (bypass normal capability check for courses). */
    const USERID = 2;

    /** @var int */
    private $sourcecourseid;

    /** @var int */
    private $destinationcourseid;

    /** @var string|null */
    private $backupid = null;

    public function set_backup_id($backupid) {
        $this->backupid = $backupid;
    }

    public function __construct($parameters) {
        $this->sourcecourseid = (int)$parameters['from'];
        $this->destinationcourseid = (int)$parameters['into'];
    }

    public function get_backup_path() {
        global $CFG;
        return $CFG->tempdir . '/backup/' . $this->backupid;
    }

    public function backup() {
        $backup = new backup_controller(backup::TYPE_1COURSE,
                                        $this->sourcecourseid,
                                        backup::FORMAT_MOODLE,
                                        backup::INTERACTIVE_NO,
                                        backup::MODE_IMPORT,
                                        self::USERID);

        $this->backupid = $backup->get_backupid();

        $backup->execute_plan();
        $backup->destroy();
    }

    public function restore() {
        $restore = new restore_controller($this->backupid,
                                          $this->destinationcourseid,
                                          backup::INTERACTIVE_NO,
                                          backup::MODE_GENERAL,
                                          self::USERID,
                                          backup::TARGET_EXISTING_ADDING);

        $restore->execute_precheck();
        $restore->execute_plan();
        $restore->destroy();
    }

    public function rollover() {
        $this->backup();
        $this->restore();
    }
}
