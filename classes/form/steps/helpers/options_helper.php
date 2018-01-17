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

namespace local_rollover\form\steps\helpers;

use backup_generic_setting;
use MoodleQuickForm;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class options_helper extends setting_helper {
    /** @var MoodleQuickForm */
    private $form = null;

    public function set_form($form) {
        $this->form = $form;
    }

    /** @var stdClass */
    private $config;

    /** @var backup_generic_setting */
    private $settings;

    public function __construct($settings) {
        $this->config = get_config('local_rollover');
        $this->settings = $settings;
    }

    public function create_options() {
        foreach ($this->settings as $setting) {
            $this->definition_add_setting($setting);
        }

        foreach ($this->settings as $setting) {
            foreach ($setting->get_my_dependency_properties() as $dependency) {
                call_user_func_array([$this->form, 'disabledIf'], $dependency);
            }
        }
    }

    private function definition_add_setting($setting) {
        $name = $setting->get_name();
        $default = $this->get_default($name);
        $locked = $this->is_locked($name);
        $value = $locked ? $default : $this->definition_get_value($name);
        $hidden = ($locked && !$default);

        $ui = $setting->get_ui();

        if ($locked) {
            $ui->disable();
        }
        $attributes = $ui->get_attributes();

        $label = get_string($this->get_label_for_setting($name), 'backup');
        $uiname = $ui->get_name();

        if ($hidden) {
            $this->form->addElement('hidden', $uiname);
            $this->form->setType($uiname, PARAM_BOOL);
        } else if ($this->is_readonly()) {
            $this->form->addElement('static',
                                    'static_' . $uiname,
                                    $label,
                                    $ui->get_static_value());
        } else {
            $this->form->addElement('checkbox',
                                    $uiname,
                                    $label,
                                    '',
                                    $attributes);
        }

        $this->form->setDefault($uiname, $value);
    }

    public function get_default($name) {
        $default = "option_{$name}";
        $default = isset($this->config->$default) ? $this->config->$default : 0;
        return $default;
    }

    public function is_locked($name) {
        $locked = "option_{$name}_locked";
        $locked = isset($this->config->$locked) ? $this->config->$locked : 0;
        return $locked;
    }

    private function get_label_for_setting($name) {
        $name = str_replace('_', '', $name);
        return "rootsetting{$name}";
    }

    private function definition_get_value($name) {
        if (!array_key_exists($name, $this->settings)) {
            return null;
        }
        $setting = $this->settings[$name];
        $value = $setting->get_value();
        return $value;
    }
}
