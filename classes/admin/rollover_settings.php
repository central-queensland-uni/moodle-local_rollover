<?php
/**
 * Created by PhpStorm.
 * User: danielroperto
 * Date: 11/10/17
 * Time: 9:18 AM
 */

namespace local_rollover\admin;

use admin_category;
use admin_root;
use admin_setting_configcheckbox_with_lock;
use admin_settingpage;
use lang_string;

class rollover_settings {
    /**
     * @return array
     */
    public static function get_rollover_options() {
        $options = [];
        $rawoptions = [
            'users',
            'anonymize',
            'role_assignments',
            'activities',
            'blocks',
            'filters',
            'comments',
            'badges',
            'userscompletion',
            'logs',
            'histories',
            'questionbank',
            'groups',
        ];
        foreach ($rawoptions as $option) {
            $options[$option] = str_replace('_', '', $option);
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
