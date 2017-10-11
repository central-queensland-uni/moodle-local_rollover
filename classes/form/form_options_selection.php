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

use local_rollover\admin\rollover_settings;
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

    public function __construct() {
        $this->config = get_config('local_rollover');
        parent::__construct();
    }

    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'from');
        $mform->setType('from', PARAM_INT);

        $mform->addElement('hidden', 'into');
        $mform->setType('into', PARAM_INT);

        foreach (rollover_settings::get_rollover_options() as $option => $langname) {
            $this->definition_add_option($option, $langname);
        }

        $this->add_action_buttons(false, get_string('performrollover', 'local_rollover'));
    }

    private function definition_add_option($option, $langname) {
        list($default, $locked) = $this->definition_add_option_get_settings($option);

        if ($locked && !$default) {
            return;
        }

        $mform = $this->_form;
        $name = "option[{$option}]";

        $attributes = $locked ? 'disabled' : null;
        $mform->addElement('checkbox',
                           $name,
                           get_string("general{$langname}", 'backup'),
                           '',
                           $attributes);
        $mform->setDefault($name, $default);
    }

    private function definition_add_option_get_settings($option) {
        $default = "option_{$option}";
        $default = isset($this->config->$default) ? $this->config->$default : 0;

        $locked = "option_{$option}_locked";
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
}
