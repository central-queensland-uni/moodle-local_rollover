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

namespace local_rollover\form;

use backup_generic_setting;
use local_rollover\local\rollover\rollover_parameters;
use moodleform;
use stdClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form_options_selection extends moodleform {
    /** @var stdClass */
    private $config;

    /** @var backup_generic_setting */
    private $settings;

    public function __construct($settings) {
        $this->config = get_config('local_rollover');
        $this->settings = $settings;
        parent::__construct();
    }

    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', rollover_parameters::PARAM_CURRENT_STEP);
        $mform->setType(rollover_parameters::PARAM_CURRENT_STEP, PARAM_INT);

        $mform->addElement('hidden', rollover_parameters::PARAM_DESTINATION_COURSE_ID);
        $mform->setType(rollover_parameters::PARAM_DESTINATION_COURSE_ID, PARAM_INT);

        $mform->addElement('hidden', rollover_parameters::PARAM_BACKUP_ID);
        $mform->setType(rollover_parameters::PARAM_BACKUP_ID, PARAM_ALPHANUM);

        foreach ($this->settings as $setting) {
            $this->definition_add_setting($setting);
        }

        foreach ($this->settings as $setting) {
            foreach ($setting->get_my_dependency_properties() as $dependency) {
                call_user_func_array([$this->_form, 'disabledIf'], $dependency);
            }
        }

        $this->add_action_buttons(false, get_string('next'));
    }

    private function definition_add_setting($setting) {
        $name = $setting->get_name();
        list($default, $locked) = $this->definition_get_default_and_locked($name);

        $hidden = ($locked && !$default);

        $attributes = $locked ? 'disabled' : null;
        $mform = $this->_form;

        $uiname = $setting->get_ui_name();
        if ($hidden) {
            $mform->addElement('hidden', $uiname);
            $mform->setType($uiname, PARAM_BOOL);
        } else {
            $mform->addElement('checkbox',
                               $uiname,
                               get_string($this->get_label_for_setting($name), 'backup'),
                               '',
                               $attributes);
        }
        $mform->setDefault($uiname, $default);
    }

    private function definition_get_default_and_locked($name) {
        $default = "option_{$name}";
        $default = isset($this->config->$default) ? $this->config->$default : 0;

        $locked = "option_{$name}_locked";
        $locked = isset($this->config->$locked) ? $this->config->$locked : 0;

        return [$default, $locked];
    }

    /**
     * Validate the parts of the request form for this module
     *
     * @param mixed[]  $data  An array of form data
     * @param string[] $files An array of form files
     * @return string[] of error messages
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }

    private function get_label_for_setting($name) {
        $name = str_replace('_', '', $name);
        return "rootsetting{$name}";
    }
}
