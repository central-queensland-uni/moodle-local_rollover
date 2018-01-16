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

namespace local_rollover\test;

use backup_controller;
use backup_plan;
use backup_setting;
use ReflectionClass;
use setting_dependency;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_rollover
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2017 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_controller_debugger {
    private static $lastdebug = null;

    /**
     * This will create/append data to a temporary file'.
     *
     * You need to set: $CFG->local_rollover_backup_controller_debug = true;
     *
     * @param backup_controller $backupcontroller
     */
    public static function debug(backup_controller $backupcontroller, $header) {
        global $CFG;

        if (empty($CFG->local_rollover_backup_controller_debug)) {
            return;
        }

        $data = (new backup_controller_debugger($backupcontroller))->create_debug_info();
        if ($data === self::$lastdebug) {
            $data = "*same as before*\n";
        } else {
            self::$lastdebug = $data;
        }

        $data = "###########################\n### " . date("Y-m-d H:i:s") .
                " ###\n###########################\n### {$header}\n{$data}\n\n";
        file_put_contents('/tmp/local_rollover_backup_controller_debug', $data, FILE_APPEND);
    }

    private static function get_level($level) {
        switch ($level) {
            case backup_setting::ROOT_LEVEL:
                return 'ROOT';
            case backup_setting::COURSE_LEVEL:
                return 'COURSE';
            case backup_setting::SECTION_LEVEL:
                return 'SECTION';
            case backup_setting::ACTIVITY_LEVEL:
                return 'ACTIVITY';
            default:
                return $level;
        }
    }

    private static function get_status($status) {
        switch ($status) {
            case backup_setting::NOT_LOCKED:
                return 'NOT';
            case backup_setting::LOCKED_BY_CONFIG:
                return 'CONFIG';
            case backup_setting::LOCKED_BY_PERMISSION:
                return 'PERMISSION';
            case backup_setting::LOCKED_BY_HIERARCHY:
                return 'HIERARCHY';
            default:
                return $status;
        }
    }

    private static function get_ui_type($type) {
        switch ($type) {
            case backup_setting::UI_NONE:
                return 'NONE';
            case backup_setting::UI_HTML_CHECKBOX:
                return 'CHECK';
            case backup_setting::UI_HTML_RADIOBUTTON:
                return 'RADIO';
            case backup_setting::UI_HTML_DROPDOWN:
                return 'DROP';
            case backup_setting::UI_HTML_TEXTFIELD:
                return 'TEXT';
            default:
                return $type;
        }
    }

    /** @var backup_controller */
    private $controller;

    private function __construct($controller) {
        $this->controller = $controller;
    }

    public function create_debug_info() {
        $result = '';

        /** @var backup_plan $plan */
        $plan = $this->controller->get_plan();
        $settings = $plan->get_settings();

        $format = "%-30s %-8s %-10s %-3s %-5s %-4s %-s\n";
        $result .= sprintf($format, 'Name', 'Level', 'Lock', 'Vis', 'UI', 'Type', 'Value');
        $result .= sprintf($format, '----', '-----', '----', '---', '--', '----', '-----');

        /** @var backup_setting[] $settings */
        foreach ($settings as $setting) {
            $result .= sprintf(
                $format,
                $setting->get_name(),
                self::get_level($setting->get_level()),
                self::get_status($setting->get_status()),
                $setting->get_visibility() ? 'Yes' : 'No',
                self::get_ui_type($setting->get_ui_type()),
                $setting->get_param_validation(),
                $setting->get_value()
            );

            /** @var setting_dependency $dependency */
            foreach ($setting->get_dependencies() as $dependency) {
                $reflection = new ReflectionClass($dependency);
                $name = str_replace('setting_dependency_disabledif_', '', $reflection->getShortName());
                $name = str_replace('_', ' ', $name);
                $result .= sprintf("          > Disables if %-11s (%-8s): %s\n",
                                   $name,
                                   $dependency->is_locked() ? 'locked' : 'unlocked',
                                   $dependency->get_dependent_setting()->get_name()
                );
            }

            /** @var setting_dependency $dependency */
            foreach ($setting->get_settings_depended_on() as $dependency) {
                $reflection = new ReflectionClass($dependency);
                $name = str_replace('setting_dependency_disabledif_', '', $reflection->getShortName());
                $name = str_replace('_', ' ', $name);
                $result .= sprintf("          < Disabled if %-11s (%-8s): %s\n",
                                   $name,
                                   $dependency->is_locked() ? 'locked' : 'unlocked',
                                   $dependency->get_dependent_setting()->get_name()
                );
            }
        }

        return $result;
    }
}
