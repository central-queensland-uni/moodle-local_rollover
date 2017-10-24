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

use core_collator;
use local_rollover\dml\activity_rule_db;
use moodleform;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form_activity_rule extends moodleform {
    const PARAM_ACTION = 'action';

    const PARAM_RULEID = 'ruleid';

    const PARAM_RULE = 'rule';

    const PARAM_MODULE = 'module';

    const PARAM_REGEX = 'regex';

    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', self::PARAM_ACTION, 'add');
        $mform->setType(self::PARAM_ACTION, PARAM_ALPHANUM);

        $mform->addElement('hidden', self::PARAM_RULEID, '');
        $mform->setType(self::PARAM_RULEID, PARAM_INT);

        $ruleoptions = [
            activity_rule_db::RULE_FORBID      => get_string('rule-forbid', 'local_rollover'),
            activity_rule_db::RULE_ENFORCE     => get_string('rule-enforce', 'local_rollover'),
            activity_rule_db::RULE_NOT_DEFAULT => get_string('rule-not_default', 'local_rollover'),
        ];
        $mform->addElement('select',
                           self::PARAM_RULE,
                           get_string('add_rule_field_rule', 'local_rollover'),
                           $ruleoptions
        );
        $mform->addHelpButton(self::PARAM_RULE, 'add_rule_field_rule', 'local_rollover');

        $modules = ['' => ''] + $this->get_available_modules();
        $mform->addElement('select',
                           self::PARAM_MODULE,
                           get_string('add_rule_field_module', 'local_rollover'),
                           $modules);
        $mform->addHelpButton(self::PARAM_MODULE, 'add_rule_field_module', 'local_rollover');

        $mform->addElement('text',
                           self::PARAM_REGEX,
                           get_string('add_rule_field_regex', 'local_rollover'));
        $mform->setType(self::PARAM_REGEX, PARAM_TEXT);
        $mform->addHelpButton(self::PARAM_REGEX, 'add_rule_field_regex', 'local_rollover');

        $action = optional_param(self::PARAM_ACTION, 'add', PARAM_ALPHANUM);
        $submit = ($action == 'add') ? 'add_rule' : 'update_rule';
        $this->add_action_buttons(true, get_string($submit, 'local_rollover'));
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

    private function get_available_modules() {
        global $DB;

        $modules = [];
        $rows = $DB->get_records('modules', ['visible' => 1], 'name', 'id, name');
        foreach ($rows as $row) {
            $modules[$row->id] = get_string("modulename", $row->name);
        }
        core_collator::asort($modules);

        return $modules;
    }
}
