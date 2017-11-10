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

namespace local_rollover\local;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_history {
    const SETTING_BACKUP_LOCATION = 'backup_location';

    const SETTING_BACKUP_DURATION = 'backup_duration';

    public static function get_default_location() {
        global $CFG;
        return "{$CFG->dataroot}/local_rollover/backup_history";
    }

    public static function get_default_duration() {
        return WEEKSECS;
    }

    public static function get_setting_location($filename = '') {
        $location = get_config('local_rollover', self::SETTING_BACKUP_LOCATION);
        if (empty($location)) {
            $location = self::get_default_location();
        }

        if (!empty($filename)) {
            $location = "{$location}/{$filename}";
        }

        return $location;
    }
}
