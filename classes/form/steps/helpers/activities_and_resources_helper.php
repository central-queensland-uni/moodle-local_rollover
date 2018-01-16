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

use backup_root_task;
use backup_setting;
use backup_task;
use base_task;
use html_writer;
use local_rollover\backup\activities_and_resources_rules_applier;
use MoodleQuickForm;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activities_and_resources_helper {
    /** @var MoodleQuickForm */
    private $form = null;

    public function set_form($form) {
        $this->form = $form;
    }

    /** @var backup_task[] */
    private $tasks;

    public function get_tasks() {
        return $this->tasks;
    }

    /** @var array */
    private $modules;

    /** @var bool[] */
    private $formatting = [
        'activity' => false,
        'section'  => false,
        'course'   => false,
    ];

    public function __construct($tasks) {
        global $DB;

        $this->tasks = $tasks;
        $this->modules = $DB->get_records('modules');
    }

    public function create_tasks() {
        $applier = new activities_and_resources_rules_applier();
        $applier->apply($this->tasks);

        foreach ($this->tasks as $task) {
            if ($task instanceof backup_root_task) {
                continue;
            }
            foreach ($task->get_settings() as $setting) {
                $changeable = $setting->get_ui()->is_changeable();
                $visible = ($setting->get_visibility() == backup_setting::VISIBLE);
                if ($changeable && $visible) {
                    $this->create_task_setting_unlocked($setting, $task);
                } else {
                    $this->create_task_setting_locked($setting, $task);
                }
            }
        }
    }

    private function create_task_setting_unlocked(backup_setting $setting, base_task $task) {
        global $OUTPUT;

        $this->add_html_formatting($setting);

        call_user_func_array([$this->form, 'addElement'], $setting->get_ui()->get_element_properties($task, $OUTPUT));
        $this->form->setType($setting->get_ui_name(), $setting->get_param_validation());
        $this->form->setDefault($setting->get_ui_name(), $setting->get_value());

        if ($setting->has_help()) {
            list($identifier, $component) = $setting->get_help();
            $this->form->addHelpButton($setting->get_ui_name(), $identifier, $component);
        }

        $this->close_div();
    }

    private function create_task_setting_locked(backup_setting $setting, base_task $task) {
        global $OUTPUT;
        $settingui = $setting->get_ui();

        if ($setting->get_visibility() == backup_setting::VISIBLE) {
            $this->add_html_formatting($setting);

            $icon = $this->get_fixed_setting_locked_icon($setting);
            $label = $settingui->get_label($task);
            $labelicon = $settingui->get_icon();
            if (!empty($labelicon)) {
                $label .= '&nbsp;' . $OUTPUT->render($labelicon);
            }
            $this->form->addElement('static', 'static_' . $settingui->get_name(), $label, $settingui->get_static_value() . $icon);

            $this->close_div();
        }

        $this->form->addElement('hidden', $settingui->get_name(), $settingui->get_value());
        $this->form->setType($settingui->get_name(), $settingui->get_param_validation());
    }

    private function get_fixed_setting_locked_icon(backup_setting $setting) {
        global $OUTPUT;

        switch ($setting->get_status()) {
            case backup_setting::LOCKED_BY_PERMISSION:
                $icon = ' ' . $OUTPUT->pix_icon('i/permissionlock', get_string('lockedbypermission', 'backup'), 'moodle',
                                                ['class' => 'smallicon lockedicon permissionlock']);
                break;
            case backup_setting::LOCKED_BY_CONFIG:
                $icon = ' ' . $OUTPUT->pix_icon('i/configlock', get_string('lockedbyconfig', 'backup'), 'moodle',
                                                ['class' => 'smallicon lockedicon configlock']);
                break;
            case backup_setting::LOCKED_BY_HIERARCHY:
                $icon = ' ' . $OUTPUT->pix_icon('i/hierarchylock', get_string('lockedbyhierarchy', 'backup'), 'moodle',
                                                ['class' => 'smallicon lockedicon configlock']);
                break;
            default:
                $icon = '';
                break;
        }
        return $icon;
    }

    private function add_html_formatting(backup_setting $setting) {
        $isincludesetting = (strpos($setting->get_name(), '_include') !== false);
        $isrootlevel = ($setting->get_level() == backup_setting::ROOT_LEVEL);

        if ($isincludesetting && !$isrootlevel) {
            switch ($setting->get_level()) {
                case backup_setting::COURSE_LEVEL:
                    $this->add_html_formatting_course_level();
                    break;
                case backup_setting::SECTION_LEVEL:
                    $this->add_html_formatting_section_level();
                    break;
                case backup_setting::ACTIVITY_LEVEL:
                    $this->add_html_formatting_activity_level();
                    break;
                default:
                    $this->open_div('normal_setting');
                    break;
            }
        } else if ($setting->get_level() == backup_setting::ROOT_LEVEL) {
            $this->open_div('root_setting');
        } else {
            $this->open_div('normal_setting');
        }
    }

    private function add_html_formatting_course_level() {
        if ($this->formatting['activity']) {
            $this->close_div();
            $this->formatting['activity'] = false;
        }

        if ($this->formatting['section']) {
            $this->close_div();
            $this->formatting['section'] = false;
        }

        if ($this->formatting['course']) {
            $this->close_div();
        }

        $this->open_div('grouped_settings course_level');
        $this->open_div('include_setting course_level');
        $this->formatting['course'] = true;
    }

    private function add_html_formatting_section_level() {
        if ($this->formatting['activity']) {
            $this->close_div();
            $this->formatting['activity'] = false;
        }

        if ($this->formatting['section']) {
            $this->close_div();
        }

        $this->open_div('grouped_settings section_level');
        $this->open_div('include_setting section_level');
        $this->formatting['section'] = true;
    }

    private function add_html_formatting_activity_level() {
        if ($this->formatting['activity']) {
            $this->close_div();
            $this->formatting['activity'] = false;
        }

        $this->open_div('grouped_settings activity_level');
        $this->open_div('include_setting activity_level');
        $this->formatting['activity'] = true;
    }

    private function close_div() {
        $this->form->addElement('html', html_writer::end_tag('div'));
    }

    private function open_div($class) {
        $this->form->addElement('html', html_writer::start_tag('div', ['class' => $class]));
    }
}
