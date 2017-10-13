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

namespace local_rollover\admin;

use admin_category;
use admin_root;
use admin_setting_configcheckbox_with_lock;
use admin_settingpage;
use lang_string;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class rollover_settings {
    /**
     * Returns an array where the key is the option name (used in settings)
     * and the value is the language name (used in language strings).
     *
     * They are not the same because we reuse names from core.
     *
     * @return array
     */
    public static function get_rollover_options() {
        $options = [];
        $rawoptions = [
            'activities',
            'blocks',
            'filters',
            'questionbank',
            'groups',
        ];
        foreach ($rawoptions as $option) {
            $options[$option] = str_replace('_', '', $option);
        }
        return $options;
    }

    public static function prepare_rollover_options($parameters) {
        $options = [];
        foreach (array_keys(static::get_rollover_options()) as $option) {
            $options[$option] = isset($parameters[$option]) ? (bool)$parameters[$option] : false;
        }
        return $options;
    }


    /**
     * @param admin_root $admin
     */
    public function create($admin) {
        global $CFG;
        require_once($CFG->libdir . '/adminlib.php');

        $category = new admin_category('local_rollover',
                                       new lang_string('settings', 'local_rollover')
        );
        $admin->add('courses', $category);

        $this->create_options($admin);
    }

    /**
     * @param admin_root $admin
     */
    private function create_options($admin) {
        $options = new admin_settingpage('local_rollover_options',
                                         new lang_string('settings-options', 'local_rollover')
        );
        $defaults = ['value' => 0, 'locked' => 0];

        foreach (self::get_rollover_options() as $option => $langname) {
            $key = 'local_rollover/option_' . $option;
            $title = new lang_string("general{$langname}", 'backup');
            $description = new lang_string("option_{$langname}", 'local_rollover');
            $options->add(
                new admin_setting_configcheckbox_with_lock($key, $title, $description, $defaults)
            );
        }

        $admin->add('local_rollover', $options);
    }
}
