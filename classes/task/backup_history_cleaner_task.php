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

namespace local_rollover\task;

use core\task\scheduled_task;
use local_rollover\local\backup_history;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_history_cleaner_task extends scheduled_task {
    public static function get_filename_timestamp($filename) {
        $pattern = '/^(\d{4})-(\d{2})-(\d{2})_(\d{2})-(\d{2})-(\d{2})_[a-z0-9\-]+\.mbz/';
        if (!preg_match($pattern, $filename, $matches)) {
            return null;
        }
        list(, $year, $month, $day, $hour, $minute, $second) = $matches;
        $time = mktime($hour, $minute, $second, $month, $day, $year);
        return $time;
    }

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('backup_history_cleaner_task', 'local_rollover');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        $now = time();
        $duration = backup_history::get_setting_duration();
        $deletebefore = $now - $duration;
        $deletedcount = 0;
        echo "Deleting rollover history files before: " . date('Y-m-d H:i:s', $deletebefore) . "\n";
        echo "Current timestamp: {$now} - History Duration: {$duration}\n";

        $path = backup_history::get_setting_location();
        $files = is_dir($path) ? scandir($path) : [];
        foreach ($files as $file) {
            if (($file == '.') || ($file == '..')) {
                continue; // Do not log it.
            }

            $filetime = self::get_filename_timestamp($file);
            if (is_null($filetime)) {
                printf("%15s: %s (%d)\n", "Unrecognized", $file, $filetime);
                continue;
            }

            if ($filetime < $deletebefore) {
                printf("%15s: %s (%d)\n", "Deleted", $file, $filetime);
                unlink("{$path}/{$file}");
                $deletedcount++;
            } else {
                printf("%15s: %s (%d)\n", "Kept", $file, $filetime);
            }
        }

        echo "Files deleted: {$deletedcount}\n";
    }
}
