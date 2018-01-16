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

namespace local_rollover\form\steps;

use backup_activity_task;
use html_writer;
use local_rollover\form\steps\helpers\activities_and_resources_helper;
use local_rollover\local\select_allnone;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form_activities_and_resources_selection extends form_step_base {
    /** @var activities_and_resources_helper */
    private $helper;

    public function __construct($tasks) {
        global $PAGE;
        $PAGE->requires->js_call_amd('local_rollover/activity-resources-selector', 'initialise');

        $this->helper = new activities_and_resources_helper($tasks);
        parent::__construct();
    }

    /**
     * Step-specific form definition.
     */
    public function step_definition() {
        $mform = $this->_form;
        $this->helper->set_form($mform);

        $mform->addElement('header', 'coursesettings', get_string('includeactivities', 'backup'));
        if (select_allnone::should_display()) {
            $this->create_select_all_none_section();
        }
        $this->helper->create_tasks();

        $this->add_action_buttons();
    }

    private function create_select_all_none_section() {
        $html = $this->create_select_all_none_html();
        $html .= $this->create_select_all_none_activities_html();

        $html = html_writer::div($html, 'grouped_settings section_level');

        $this->_form->addElement('html', $html);
    }

    private function create_select_all_none_html() {
        $select = get_string('select', 'local_rollover');
        $all = get_string('select_all', 'local_rollover');
        $none = get_string('select_none', 'local_rollover');

        $links = html_writer::link('#', $all, ['id' => 'rollover-all-included']) .
                 ' / ' .
                 html_writer::link('#', $none, ['id' => 'rollover-none-included']);

        $html = html_writer::div($select, 'fitemtitle') .
                html_writer::div($links, 'felement');
        $html = html_writer::div($html, 'fitem fitem_fcheckbox backup_selector local_rollover_select_allnone');

        $html = html_writer::div($html, 'include_setting section_level');

        return $html;
    }

    private function create_select_all_none_activities_html() {
        $all = get_string('select_all', 'local_rollover');
        $none = get_string('select_none', 'local_rollover');
        $modules = $this->get_rollover_modules();

        $html = '';
        foreach ($modules as $key => $name) {
            $links = html_writer::link('#', $all, ['id' => "rollover-{$key}-all"]) .
                     ' / ' .
                     html_writer::link('#', $none, ['id' => "rollover-{$key}-none"]);
            $modulehtml = html_writer::div($name, 'fitemtitle') .
                          html_writer::div($links, 'felement');
            $html .= html_writer::div($modulehtml, 'fitem');
        }

        $html = html_writer::div($html, 'include_setting activity_level local_rollover_partial_select');
        $html = html_writer::div($html, 'grouped_settings activity_level');

        return $html;
    }

    private function get_rollover_modules() {
        $modules = [];

        foreach ($this->helper->get_tasks() as $task) {
            if (!$task instanceof backup_activity_task) {
                continue;
            }

            $module = $task->get_modulename();
            if (array_key_exists($module, $modules)) {
                continue;
            }

            $modules[$module] = get_string('modulenameplural', $module);
        }

        asort($modules);
        return $modules;
    }
}
