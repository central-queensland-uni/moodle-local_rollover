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

use local_rollover\admin\rollover_settings;
use local_rollover\form\form_precheck;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class step_precheck extends step {
    public function skipped() {
        $protections = rollover_settings::get_rollover_protection_items();

        // If any protection is not skipped, do not skip.
        foreach ($protections as $protection) {
            $method = "check_skipped_{$protection}";
            if (!method_exists($this, $method)) {
                throw new moodle_exception("Missing protection check method: {$method}");
            }
            if (!$this->$method()) {
                return false;
            }
        }

        return true;
    }

    public function check_skipped_empty() {
        $level = rollover_settings::get_protection_config(rollover_settings::PROTECTION_NOT_EMPTY);
        if ($level == rollover_settings::LEVEL_IGNORE) {
            return true;
        }

        $course = $this->controller->get_destination_course();
        $info = get_fast_modinfo($course, -1);
        $instances = $info->get_instances();
        return (count($instances) == 0);
    }

    public function check_skipped_hidden() {
        return true;
    }

    public function check_skipped_user() {
        return true;
    }

    public function check_skipped_started() {
        return true;
    }

    public function create_form() {
        return new form_precheck();
    }

    public function process_form_data($data) {
    }
}
