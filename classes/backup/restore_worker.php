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
class restore_worker {
    /** @var int */
    private $destinationcourseid;

    public function __construct($destinationcourseid) {
        $this->destinationcourseid = (int)$destinationcourseid;
    }

    public function restore($backupid, $options) {
        $restore = new restore_controller($backupid,
                                          $this->destinationcourseid,
                                          backup::INTERACTIVE_NO,
                                          backup::MODE_GENERAL,
                                          rollover_worker::USERID,
                                          backup::TARGET_EXISTING_ADDING);

        $settings = $restore->get_plan()->get_settings();

        foreach (['activities'] as $option) {
            $settings[$option]->set_value($options[$option] ? 1 : 0);
        }

        $restore->execute_precheck();
        $restore->execute_plan();
        $restore->destroy();
    }
}
