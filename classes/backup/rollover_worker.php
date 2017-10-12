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

use local_rollover\admin\rollover_settings;

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
    /** @var backup_worker */
    private $backupworker = null;

    public function get_backup_worker() {
        return $this->backupworker;
    }

    /** @var bool[] */
    private $options;

    public function get_options() {
        return $this->options;
    }

    public function __construct($parameters) {
        if (!empty($parameters['from'])) {
            $this->backupworker = new backup_worker($parameters['from']);
        }

        $this->options = [];
        foreach (array_keys(rollover_settings::get_rollover_options()) as $option) {
            $this->options[$option] = isset($parameters['option'][$option]) ? (bool)$parameters['option'][$option] : false;
        }
    }

    public function rollover($into) {
        $this->backupworker->backup();
        $worket = new restore_worker($into);
        $worket->restore($this->backupworker->get_backup_id(), $this->options);
    }
}
