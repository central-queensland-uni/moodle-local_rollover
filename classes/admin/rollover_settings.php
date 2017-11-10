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
use admin_externalpage;
use admin_root;
use admin_setting_configcheckbox_with_lock;
use admin_setting_configduration;
use admin_setting_configselect;
use admin_setting_configtext;
use admin_settingpage;
use lang_string;
use local_rollover\local\backup_history;
use local_rollover\local\protection;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class rollover_settings {
    const DEFAULT_PROTECTION_LEVEL = protection::ACTION_WARN;

    /**
     * Returns an array where the key is the option name (used in settings)
     * and the value is the language name (used in language strings).
     *
     * They are not the same because we reuse names from core.
     *
     * @return array
     */
    public static function get_rollover_options_defaults() {
        return [
            'users'            => false,
            'anonymize'        => false,
            'role_assignments' => false,
            'activities'       => true,
            'blocks'           => true,
            'filters'          => true,
            'comments'         => false,
            'badges'           => false,
            'userscompletion'  => false,
            'logs'             => false,
            'histories'        => false,
            'questionbank'     => true,
            'groups'           => false,
        ];
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
        $this->create_filter($admin);
        $this->create_activities($admin);
        $this->create_protection($admin);
        $this->create_backup_history($admin);
    }

    private function create_options($admin) {
        $options = new admin_settingpage('local_rollover_options',
                                         new lang_string('settings-options', 'local_rollover')
        );

        foreach (self::get_rollover_options_defaults() as $option => $default) {
            $langname = str_replace('_', '', $option);
            $key = 'local_rollover/option_' . $option;
            $title = new lang_string("general{$langname}", 'backup');
            $description = new lang_string("option_{$langname}", 'local_rollover');
            $defaults = ['value' => $default, 'locked' => 0];
            $options->add(
                new admin_setting_configcheckbox_with_lock($key, $title, $description, $defaults)
            );
        }

        $admin->add('local_rollover', $options);
    }

    private function create_filter($admin) {
        $page = new admin_externalpage('local_rollover_filter',
                                       new lang_string('settings-filter', 'local_rollover'),
                                       new moodle_url('/local/rollover/past-instances-filter.php')
        );
        $admin->add('local_rollover', $page);
    }

    private function create_activities($admin) {
        $page = new admin_externalpage('local_rollover_activities',
                                       new lang_string('settings-activities', 'local_rollover'),
                                       new moodle_url('/local/rollover/activities-rules.php')
        );
        $admin->add('local_rollover', $page);
    }

    private function create_protection($admin) {
        $protectionsetting = new admin_settingpage('local_rollover_protection',
                                                   new lang_string('settings-protection', 'local_rollover')
        );

        $options = [];
        foreach (protection::get_actions() as $option) {
            $options[$option] = new lang_string("settings-protection-option-{$option}", 'local_rollover');
        }

        foreach (protection::get_protections() as $protection) {
            $title = new lang_string("settings-protection-{$protection}", 'local_rollover');
            $description = new lang_string("settings-protection-{$protection}-description", 'local_rollover');
            $key = 'local_rollover/' . protection::get_config_key($protection);
            $protectionsetting->add(
                new admin_setting_configselect($key, $title, $description, self::DEFAULT_PROTECTION_LEVEL, $options)
            );
        }

        $admin->add('local_rollover', $protectionsetting);
    }

    private function create_backup_history($admin) {
        $backupsettings = new admin_settingpage('local_rollover_backup_history',
                                                new lang_string('settings-backup-history', 'local_rollover')
        );

        $backupsettings->add(new admin_setting_configtext(
                                 'local_rollover/' . backup_history::SETTING_BACKUP_LOCATION,
                                 new lang_string('settings-backup-location', 'local_rollover'),
                                 new lang_string('settings-backup-location-description', 'local_rollover'),
                                 backup_history::get_default_location()
                             ));

        $backupsettings->add(new admin_setting_configduration(
                                 'local_rollover/' . backup_history::SETTING_BACKUP_DURATION,
                                 new lang_string('settings-backup-duration', 'local_rollover'),
                                 new lang_string('settings-backup-duration-description', 'local_rollover'),
                                 backup_history::get_default_duration()
                             ));

        $admin->add('local_rollover', $backupsettings);
    }
}
