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

use local_rollover\form\steps\form_options_selection;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class step_content_options extends step {
    public function create_form() {
        return new form_options_selection($this->controller->get_backup_worker()->get_backup_root_settings());
    }

    public function process_form_data($data) {
        $backupworker = $this->controller->get_backup_worker();
        $settings = $backupworker->get_backup_root_settings();
        foreach ($settings as $setting) {
            $name = $setting->get_ui_name();
            $value = isset($data->$name) ? $data->$name : 0;
            if ($value != $setting->get_value()) {
                $setting->set_value($value);
            }
        }
        $backupworker->save();
    }
}
