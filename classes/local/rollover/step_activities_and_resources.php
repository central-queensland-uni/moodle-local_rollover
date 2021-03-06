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

use backup_root_task;
use local_rollover\form\steps\form_activities_and_resources_selection;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class step_activities_and_resources extends step {
    public function create_form() {
        return new form_activities_and_resources_selection($this->controller->get_backup_worker()->get_backup_tasks());
    }

    public function process_form_data($data) {
        $backupworker = $this->controller->get_backup_worker();
        $tasks = $backupworker->get_backup_tasks();
        foreach ($tasks as &$task) {
            if ($task instanceof backup_root_task) {
                continue;
            }
            $settings = $task->get_settings();
            foreach ($settings as &$setting) {
                $name = $setting->get_ui_name();
                $value = isset($data->$name) ? $data->$name : 0;
                if ($value != $setting->get_value()) {
                    $setting->set_value($value);
                }
            }
        }
        $backupworker->save();
    }
}
